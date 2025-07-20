<?php
enum Privilege: string{
    case Normal = "Normal";
    case Manager = "Manager";
}

enum AccountSearch: string{
    case Username = "Username";
    case Password = "Password";
    case Token = "Token";
}

enum LeaveStatus: string{
    case Approved = "Approved";
    case Rejected = "Rejected";
    case Pending = "Pending";
}

enum Leave_Application_Columns: string{
    case LeaveApplicationID = "LeaveApplicationID";
    case EmployeeID = "EmployeeID";
    case LeaveCategory = "LeaveCategory";
    case StartDate = "StartDate";
    case EndDate = "EndDate";
    case LeaveStatus = "LeaveStatus";
    case ApprovedBy = "ApprovedBy";

    /**From Employee */
    case EmployeeName = "EmployeeName";
    case EmployeeGrade = "EmployeeGrade";
}

enum Employee_Allocation_Columns: string{
    case EmployeeID = "EmployeeID";
    case LeaveCategory = "LeaveCategory";
    case UsedAllocations = "UsedAllocations";

    /**From Leave_Application */
    case StartDate = "StartDate";
    case EndDate = "EndDate";
    case LeaveStatus = "LeaveStatus";

    /**From Employee */
    case EmployeeName = "EmployeeName";
    case EmployeeGrade = "EmployeeGrade";

    /**From Grade_Allocation */
    case Allocations = "Allocations";
}

class Employee_Allocation implements IteratorAggregate{
    private $employeeallocation = [];
    private mysqli $mysqli;

    private function __construct(mysqli $mysqli, $employeeallocation = [])
    {
        $this->mysqli = $mysqli;
        $this->employeeallocation = $employeeallocation;
    }

    /**Please fix this SQL Query later, the idea: all approved employee request leave but each row unique employee and their latest leave */
    static function queryAllEmployeeAllocations(mysqli $mysqli){
        $approved = LeaveStatus::Approved->value;
        $sql1 = <<< Q1
        SELECT Employee.EmployeeGrade, Employee.EmployeeName, 
        Leave_Application.LeaveCategory, Leave_Application.StartDate, 
        Grade_Allocation.Allocations, Employee_Allocation.UsedAllocations, 
        MAX(Leave_Application.EndDate) AS EndDate FROM Leave_Application 
        Q1;
        $sql2 = "INNER JOIN Employee ON Employee.EmployeeID = Leave_Application.EmployeeID ";
        $sql3 = <<< Q3
         INNER JOIN Grade_Allocation ON Grade_Allocation.EmployeeGrade = Employee.EmployeeGrade 
        AND Grade_Allocation.LeaveCategory = Leave_Application.LeaveCategory
        Q3;
        $sql4 = <<< Q4
         INNER JOIN Employee_Allocation ON Employee_Allocation.EmployeeID = Leave_Application.EmployeeID 
        AND Employee_Allocation.LeaveCategory = Leave_Application.LeaveCategory 
        Q4;
        $sql5 = " WHERE Leave_Application.LeaveStatus = '$approved' GROUP BY Leave_Application.EmployeeID";
        $stmt_EmployeeAllocation = mysqli_prepare($mysqli, $sql1 . $sql2 . $sql3 . $sql4 . $sql5);
        #mysqli_stmt_bind_param($stmt_EmployeeAllocation, "s", $approved);
        mysqli_stmt_execute($stmt_EmployeeAllocation);
        $result = mysqli_stmt_get_result($stmt_EmployeeAllocation);
        $_employeeallocation = [];
        while ($row = mysqli_fetch_assoc($result)){
            $_employeeallocation[] = $row;
        }
        return new Employee_Allocation($mysqli, $_employeeallocation);
    }

    static function incrementUsedAllocations(mysqli $mysqli, string $employeeID, string $leaveCategory){
        $sql1 = "INSERT INTO Employee_Allocation (EmployeeID, LeaveCategory) VALUES (?, ?) ON DUPLICATE KEY UPDATE UsedAllocations = UsedAllocations + 1";
        #throw new Exception("incrementUsedAllocations " . $employeeID ." " . $leaveCategory, 1);
        $stmt_EmployeeAllocation = mysqli_prepare($mysqli, $sql1);
        mysqli_stmt_bind_param($stmt_EmployeeAllocation, "ss", $employeeID, $leaveCategory);
        $status = mysqli_stmt_execute($stmt_EmployeeAllocation);
        return $status;
    }
    
    function getIterator(): Traversable {return new ArrayIterator($this->employeeallocation);}
}

class Leave_Application implements IteratorAggregate{
    private $leaveapplications = [];
    private mysqli $mysqli;

    private function __construct(mysqli $mysqli, $leaveapplications = [])
    {
        $this->mysqli = $mysqli;
        $this->leaveapplications = $leaveapplications;
    }

    static function queryAllLeaveApplication(mysqli $mysqli){
        $stmt_LeaveApplication = mysqli_prepare($mysqli, "SELECT * FROM Leave_Application ". 
        "INNER JOIN Employee ON Leave_Application.EmployeeID = Employee.EmployeeID WHERE Leave_Application.LeaveStatus = ?");
        $pending = LeaveStatus::Pending->value;
        mysqli_stmt_bind_param($stmt_LeaveApplication, "s", $pending);
        mysqli_stmt_execute($stmt_LeaveApplication);
        $result = mysqli_stmt_get_result($stmt_LeaveApplication);
        $_leaveapplications = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $_leaveapplications[] = $row;
        }
        return new Leave_Application($mysqli, $_leaveapplications);
    }

    static function querySpecificEmployeeLeaveApplication(mysqli $mysqli, string $EmployeeID){
        $stmt_LeaveApplication = mysqli_prepare($mysqli, "SELECT * FROM Leave_Application ". 
        "INNER JOIN Employee ON Leave_Application.EmployeeID = Employee.EmployeeID WHERE Leave_Application.". 
        Leave_Application_Columns::EmployeeID->value ." = ?");
        mysqli_stmt_bind_param($stmt_LeaveApplication, "s", $EmployeeID);
        mysqli_stmt_execute($stmt_LeaveApplication);
        $result = mysqli_stmt_get_result($stmt_LeaveApplication);
        $_leaveapplications = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $_leaveapplications[] = $row;
        }
        return new Leave_Application($mysqli, $_leaveapplications);
    }

    static function setStatusEmployeeLeaveApplication(mysqli $mysqli, string $LeaveApplicationID, LeaveStatus $approvalstatus){
        $stmt_LeaveApplication = mysqli_prepare($mysqli, "UPDATE Leave_Application SET LeaveStatus = ? " . 
        "WHERE Leave_Application." . Leave_Application_Columns::LeaveApplicationID->value . " = ?");
        $_approvalstatus = $approvalstatus->value;
        mysqli_stmt_bind_param($stmt_LeaveApplication, "ss", $_approvalstatus, $LeaveApplicationID);
        $status = mysqli_stmt_execute($stmt_LeaveApplication);
        return $status;
    }

    static function createEmployeeLeaveApplication(mysqli $mysqli, string $SentLeaveType, string $From, string $To, string $EmpID){
        $sql1 = "INSERT INTO Leave_Application (EmployeeID, LeaveCategory, StartDate, EndDate, LeaveStatus) ";
        $sql2 = "VALUES (?, ?, ?, ?, ?)";
        $pending = LeaveStatus::Pending->value;
        if(!$SentLeaveType or !$From or !$To or !$EmpID){
            throw new Exception("Creating Employee Leave Application failed, empty inputs!", 1);
        }
        $stmt_LeaveApplication = mysqli_prepare($mysqli, $sql1 . $sql2);
        mysqli_stmt_bind_param($stmt_LeaveApplication, "sssss", $EmpID, $SentLeaveType, $From, $To, $pending);
        $status = mysqli_stmt_execute($stmt_LeaveApplication);
        return $status;
    }

    function getIterator(): Traversable {return new ArrayIterator($this->leaveapplications);}
}

class Grade_Allocation implements IteratorAggregate{
    private $gradeallocations = [];
    private string $leavetype;
    private mysqli $mysqli;

    private function __construct(mysqli $mysqli, $gradeallocations, string $leavetype)
    {
        $this->mysqli = $mysqli;
        $this->gradeallocations = $gradeallocations;
        $this->leavetype = $leavetype;
    }

    static function queryGradeAllocation(mysqli $mysqli, string $leavetype){
        $GradeAllocation = null;
        $stmt_GradeAllocation = mysqli_prepare($mysqli, "SELECT * FROM Grade_Allocation WHERE LeaveCategory = ?");
        mysqli_stmt_bind_param($stmt_GradeAllocation, "s", $leavetype);
        mysqli_stmt_execute($stmt_GradeAllocation);
        $Result = mysqli_stmt_get_result($stmt_GradeAllocation);
        $_GradeAllocations = [];
        while ($gradeallocation = mysqli_fetch_assoc($Result)) {
            $_GradeAllocations[] = $gradeallocation;
        }
        if (mysqli_num_rows($Result) > 0)
        $GradeAllocation = new Grade_Allocation($mysqli, $_GradeAllocations, $leavetype);
        return $GradeAllocation;
    }

    static function initGradeAllocation(mysqli $mysqli, string $leavetype){
        $grades = Grade::queryGrades($mysqli);
        $_gradeallocations = "";
        $_tobeUnpacked = [];
        $_stringtypes = "";
        foreach ($grades as $grade) {
            $_gradeallocations .= "(" . "?" . "," . "?" . "," . "?" . ")"; 
            $_gradeallocations .= ",";
            
            $_tobeUnpacked[] = $grade;
            $_stringtypes .= "s"; 
            $_tobeUnpacked[] = 0;
            $_stringtypes .= "i"; 
            $_tobeUnpacked[] = $leavetype;
            $_stringtypes .= "s"; 
        }
        $_gradeallocations[strlen($_gradeallocations) - 1] = ";";

        #throw new Exception("INSERT INTO Grade_Allocation (EmployeeGrade, Allocations, LeaveCategory) VALUES " . $_gradeallocations, 1);
        
        $stmt_GradeAllocation = mysqli_prepare($mysqli, "INSERT INTO Grade_Allocation (EmployeeGrade, Allocations, LeaveCategory) VALUES " . $_gradeallocations);
        mysqli_stmt_bind_param($stmt_GradeAllocation, $_stringtypes, ...$_tobeUnpacked);
        $status = mysqli_stmt_execute($stmt_GradeAllocation);
        return $status;
    }

    static function addGradeAllocation(mysqli $mysqli, $leavetype){
        #Get list of Grade
        $grades = Grade::queryGrades($mysqli);
        #Get list of GradeAllocation
        $stmt_GradeAllocation = mysqli_prepare($mysqli, "SELECT EmployeeGrade FROM Grade_Allocation");
        mysqli_stmt_execute($stmt_GradeAllocation);
        $Result = mysqli_stmt_get_result($stmt_GradeAllocation);
        $_GradeAllocations = [];
        while ($gradeallocation = mysqli_fetch_assoc($Result)) {
            $_GradeAllocations[] = $gradeallocation["EmployeeGrade"];
        }
        
        #Filter out Grade in GradeAllocation based on leavetype from Grade array
        $grades = iterator_to_array($grades, false);
        foreach($_GradeAllocations as $GA){
            $grades = array_filter($grades, function ($_grade) use ($GA){
                return $_grade !== $GA;
            });
        }

        #throw new Exception(implode(", ", $_GradeAllocations), 1);

        #Finally add
        $_gradeallocations = "";
        $_tobeUnpacked = [];
        $_stringtypes = "";
        foreach ($grades as $grade) {
            $_gradeallocations .= "(" . "?" . "," . "?" . "," . "?" . ")"; 
            $_gradeallocations .= ",";
            
            $_tobeUnpacked[] = $grade;
            $_stringtypes .= "s"; 
            $_tobeUnpacked[] = 0;
            $_stringtypes .= "i"; 
            $_tobeUnpacked[] = $leavetype;
            $_stringtypes .= "s"; 
        }
        $_gradeallocations[strlen($_gradeallocations) - 1] = ";";

        #throw new Exception("INSERT INTO Grade_Allocation (EmployeeGrade, Allocations, LeaveCategory) VALUES " . $_gradeallocations, 1);
        $status = true;
        if(strlen($_gradeallocations) === 0){
            return $status;
        }
        
        $stmt_GradeAllocation = mysqli_prepare($mysqli, "INSERT INTO Grade_Allocation (EmployeeGrade, Allocations, LeaveCategory) VALUES " . $_gradeallocations);
        mysqli_stmt_bind_param($stmt_GradeAllocation, $_stringtypes, ...$_tobeUnpacked);
        $status = mysqli_stmt_execute($stmt_GradeAllocation);
        return $status;
    }

    static function setAllocation(mysqli $mysqli, string $grade ,string $leavetype, int $allocation){
        $stmt_GradeAllocation = mysqli_prepare($mysqli, "UPDATE Grade_Allocation SET Allocations = ? WHERE EmployeeGrade = ? AND LeaveCategory = ?");
        mysqli_stmt_bind_param($stmt_GradeAllocation, "iss", $allocation , $grade, $leavetype);
        $status = mysqli_stmt_execute($stmt_GradeAllocation);
        return $status;
    }

    function getLeaveTyoe(){return $this->leavetype;}

    function getIterator(): Traversable {return new ArrayIterator($this->gradeallocations);}
}

class LeaveType implements IteratorAggregate{
    private $LeaveCategory = [];
    private mysqli $mysqli;

    private function __construct(mysqli $mysqli, $LeaveCategory = []){
        $this->LeaveCategory = $LeaveCategory;
        $this->mysqli = $mysqli;
    }

    static function queryLeaveType(mysqli $mysqli){
        $_LeaveType = null;
        $stmt_LeaveType = mysqli_prepare($mysqli, "SELECT LeaveCategory FROM LeaveType");
        mysqli_stmt_execute($stmt_LeaveType);
        $Result = mysqli_stmt_get_result($stmt_LeaveType);
        $LeaveCategories = [];
        while ($leavecategory = mysqli_fetch_assoc($Result)) {
            $LeaveCategories[] = $leavecategory["LeaveCategory"];
        }
        $_LeaveType = new LeaveType($mysqli, $LeaveCategories);
        return $_LeaveType;
    }

    static function addLeaveType(mysqli $mysqli, string $LeaveCategory, ?LeaveType $LT){
        $stmt_LeaveType = mysqli_prepare($mysqli, "INSERT INTO LeaveType (LeaveCategory) VALUES (?)");
        mysqli_stmt_bind_param($stmt_LeaveType, "s", $LeaveCategory);
        $status = mysqli_stmt_execute($stmt_LeaveType);
        if(!$status){
            return $status;
        }
        if($LT){
            $LT->LeaveCategory[] = $LeaveCategory;
        }
        return $status;
    }

    static function deleteLeaveType(mysqli $mysqli, string $LeaveCategory, ?LeaveType $LT){
        $stmt_LeaveType = mysqli_prepare($mysqli, "DELETE FROM LeaveType WHERE LeaveCategory = ?");
        mysqli_stmt_bind_param($stmt_LeaveType, "s", $LeaveCategory);
        $status = mysqli_stmt_execute($stmt_LeaveType);
        if(!$status){
            return $status;
        }
        if($LT){
            $LT->LeaveCategory = array_filter($LT->LeaveCategory, function($_LeaveCategory) use($LeaveCategory){
                return $_LeaveCategory !== $LeaveCategory;
            });
        }
        return $status;
    }

    static function isExistLeaveType(mysqli $mysqli, string $LeaveCategory){
        $status = false;
        $stmt_LeaveType = mysqli_prepare($mysqli, "SELECT LeaveCategory FROM LeaveType WHERE LeaveCategory = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_LeaveType, "s", $LeaveCategory);
        mysqli_stmt_execute($stmt_LeaveType);
        mysqli_stmt_store_result($stmt_LeaveType); #Store results before calling bind
        if(mysqli_stmt_num_rows($stmt_LeaveType) > 0){
            $status = true;
        }

        return $status;
    }

    function getIterator(): Traversable {return new ArrayIterator($this->LeaveCategory);}
}

class Grade implements IteratorAggregate{
    private $Grades = [];

    private function __construct($Grades = []){
        $this->Grades = $Grades;
    }

    static function queryGrades(mysqli $mysqli){
        $_Grade = null;
        $stmt_Grades = mysqli_prepare($mysqli, "SELECT EmployeeGrade FROM Grade");
        mysqli_stmt_execute($stmt_Grades);
        $Result = mysqli_stmt_get_result($stmt_Grades);
        $Grades = [];
        while ($grade = mysqli_fetch_assoc($Result)) {
            $Grades[] = $grade["EmployeeGrade"];
        }
        $_Grade = new Grade($Grades);
        return $_Grade;
    }

    static function addGrade(mysqli $mysqli, string $grade){
        $stmt_Grades = mysqli_prepare($mysqli, "INSERT INTO Grade (EmployeeGrade) VALUES (?)");
        mysqli_stmt_bind_param($stmt_Grades, "s", $grade);
        $status = mysqli_stmt_execute($stmt_Grades);
        return $status;
    }

    static function removeGrade(mysqli $mysqli, string $grade){
        $stmt_Grades = mysqli_prepare($mysqli, "DELETE FROM Grade WHERE EmployeeGrade = ?");
        mysqli_stmt_bind_param($stmt_Grades, "s", $grade);
        $status = mysqli_stmt_execute($stmt_Grades);
        return $status;
    }

    function getIterator(): Traversable{return new ArrayIterator($this->Grades);}
}

class Employee{
    private string $EmployeeID;
    private string $EmployeeName;
    private string $EmployeeGrade;
    private mysqli $mysqli;

    private function __construct(mysqli $mysqli, string $EmployeeID, string $EmployeeName, string $EmployeeGrade)
    {
        $this->mysqli = $mysqli;
        $this->EmployeeID =  $EmployeeID;
        $this->EmployeeName = $EmployeeName;
        $this->EmployeeGrade = $EmployeeGrade;
    }

    function getEmployeeID(){return $this->EmployeeID;}

    static function createEmployee(mysqli $mysqli, string $EmployeeID, string $EmployeeName, string $EmployeeGrade){
        $Emp = null;

        $stmt_Employee = mysqli_prepare($mysqli, "INSERT INTO Employee (EmployeeID, EmployeeName, EmployeeGrade) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_Employee, "sss", $EmployeeID, $EmployeeName, $EmployeeGrade);
        
        if (mysqli_stmt_execute($stmt_Employee)){
            $Emp = new Employee($mysqli, $EmployeeID, $EmployeeName, $EmployeeGrade);
        }

        return $Emp;
    }
    static function searchEmployee(mysqli $mysqli, string $EmployeeID = "", string $EmployeeName = ""){
        $stmt = "";
        $input = "";
        if ($EmployeeID !== ""){
            $stmt = "SELECT * FROM Employee WHERE EmployeeID = ? LIMIT 1";
            $input = $EmployeeID;
        }else if ($EmployeeName !== ""){
            $stmt = "SELECT * FROM Employee WHERE EmployeeName = ? LIMIT 1";
            $input = $EmployeeName;
        }else{
            return null;
        }

        $stmt_Employee = mysqli_prepare($mysqli, $stmt);
        mysqli_stmt_bind_param($stmt_Employee, "s", $input);
        mysqli_stmt_execute($stmt_Employee);
        mysqli_stmt_store_result($stmt_Employee); #Store results before calling bind
        mysqli_stmt_bind_result($stmt_Employee, $EmployeeID, $EmployeeName, $EmployeeGrade); #Prepare variables for binding

        if(mysqli_stmt_num_rows($stmt_Employee) > 0){
            mysqli_stmt_fetch($stmt_Employee); #Fetch results into the variables declared for binding earlier
            return new Employee($mysqli, $EmployeeID, $EmployeeName, $EmployeeGrade);
        }

        return null;
    }
    
}

class Account{
    private string $Username;
    private string $Password;
    private string $Token;
    private Privilege $Privilege;
    private ?Employee $Emp;
    private mysqli $mysqli;

    /***
     * Create Account
     */
    private function __construct(mysqli $mysqli,string $Username, string $Password, string $Token, Privilege $Privilege, ?Employee $Emp)
    {
        $this->mysqli = $mysqli;
        $this->Token = $Token;
        $this->Privilege = $Privilege;
        $this->Username = $Username;
        $this->Password = $Password;
        $this->Emp = $Emp;   
    }
    

    function getToken(){return $this->Token;}
    function setToken(string $Token){
        $stmt_Account = mysqli_prepare($this->mysqli, "UPDATE Account SET Token = ? WHERE Username = ?");
        mysqli_stmt_bind_param($stmt_Account, "ss", $Token, $this->Username);
        $status = mysqli_stmt_execute($stmt_Account);
        if ($status){
            $this->Token = $Token;
        }

        return $status;
    }
    function getPrivilege(){return $this->Privilege;}
    function setPrivilege(Privilege $Privilege){
        $stmt_Account = mysqli_prepare($this->mysqli, "UPDATE Account SET Privilege = ? WHERE Username = ?");
        mysqli_stmt_bind_param($stmt_Account, "ss", $Privilege->value, $this->Username);
        $status = mysqli_stmt_execute($stmt_Account);
        if ($status){
            $this->Privilege = $Privilege;
        }

        return $status;
    }
    function getEmployee(){return $this->Emp;}
    function setEmployee(Employee $Emp){
        $stmt_Account = mysqli_prepare($this->mysqli, "UPDATE Account SET EmployeeID = ? WHERE Username = ?");
        mysqli_stmt_bind_param($stmt_Account, "ss", $Emp->getEmployeeID(), $this->Username);
        $status = mysqli_stmt_execute($stmt_Account);
        if ($status){
            $this->Emp = $Emp;
        }

        return $status;
    }

    function getUsername(){return $this->Username;}

    static function searchAccount_UsernamePassword(mysqli $mysqli,string $Username, string $Password){
        $stmt_Account = mysqli_prepare($mysqli, "SELECT * FROM Account WHERE Username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_Account, "s", $Username);
        mysqli_stmt_execute($stmt_Account);
        mysqli_stmt_store_result($stmt_Account); #Store results before calling bind
        mysqli_stmt_bind_result($stmt_Account,$Username, $Password2, $Token, $Privilege, $EmployeeID); #Prepare variables for binding

        if(mysqli_stmt_num_rows($stmt_Account) > 0){
            mysqli_stmt_fetch($stmt_Account); #Fetch results into the variables declared for binding earlier
            $emp = Employee::searchEmployee($mysqli, $EmployeeID, $Username);
            if (password_verify($Password, $Password2))
            return new Account($mysqli, $Username, $Password2, $Token ?? "", Privilege::tryFrom($Privilege) , $emp);
        }

        return null;
    }

    static function searchAccount_Token(mysqli $mysqli,string $Token){
        $stmt_Account = mysqli_prepare($mysqli, "SELECT * FROM Account WHERE Token = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_Account, "s", $Token);
        mysqli_stmt_execute($stmt_Account);
        mysqli_stmt_store_result($stmt_Account); #Store results before calling bind
        mysqli_stmt_bind_result($stmt_Account,$Username, $Password, $Token, $Privilege, $EmployeeID); #Prepare variables for binding

        if(mysqli_stmt_num_rows($stmt_Account) > 0){
            mysqli_stmt_fetch($stmt_Account); #Fetch results into the variables declared for binding earlier
            $emp = Employee::searchEmployee($mysqli, $EmployeeID, $Username);
            return new Account($mysqli, $Username, $Password, $Token, Privilege::tryFrom($Privilege), $emp);
        }

        return null;
    }

    static function createAccount(mysqli $mysqli,string $Username, string $Password, string $Token, Privilege $Privilege)
    {

        $Acc = null;
        $Password = password_hash($Password, PASSWORD_BCRYPT);
        $privilege = $Privilege->value;
        if($Token === ""){
            $stmt = "INSERT INTO Account (Username, `Password`, Privilege) VALUES (?, ?, ?)";
            $stmt_Account = mysqli_prepare($mysqli, $stmt);
            mysqli_stmt_bind_param($stmt_Account, "sss", $Username, $Password, $privilege);
            
            if (mysqli_stmt_execute($stmt_Account)){
                $Acc = new Account($mysqli, $Username, $Password, $Token, $Privilege, null);
            }
        }else{
            $stmt = "INSERT INTO Account (Username, `Password`, Token, Privilege) VALUES (?, ?, ?, ?)";
            $stmt_Account = mysqli_prepare($mysqli, $stmt);
            mysqli_stmt_bind_param($stmt_Account, "ssss", $Username, $Password, $Token, $privilege);
            
            if (mysqli_stmt_execute($stmt_Account)){
                $Acc = new Account($mysqli, $Username, $Password, $Token, $Privilege, null);
            }
        }

        return $Acc;
    }

    
}
?>