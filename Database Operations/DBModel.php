<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
use Random\Randomizer; //php 8.2+ 
$random = new Randomizer();
?>
<?php
/* Start the Session */
session_start();
?>
<?php
/* MYSQL */
$DB_Username = "root";
$DB_Name = "LeaveManagement";
$Host = "localhost";
$DB_Password = ""; 

$mysqli = mysqli_connect($Host, $DB_Username, $DB_Password, $DB_Name);

//Check connection status
if (mysqli_connect_errno()){
    die("Error connecting to DB, Error: " . mysqli_connect_error());
}
?>
<?php
/* Start DB operations */
class Privilege{
    public const Normal = "Normal";
    public const Manager = "Manager";
}
class LeaveStatus{
    public const Approved = "Approved";
    public const Rejected = "Rejected";
    public const Pending = "Pending";
}
class Account{
    public const col_Username = "Username";
    public const col_Password = "Password";
    public const col_Token = "Token";
    public const col_Privilege = "Privilege";
    public const col_EmployeeID = "EmployeeID";

    private mysqli $mysqli;
    private string $Username;
    private string $Password;
    private string $Token;
    private string $Privilege;
    private string $EmployeeID;

    private function __construct(
        mysqli $mysqli,
        string $Username, 
        string $Password, 
        string $Token, 
        string $Privilege, 
        string $EmployeeID)
    {
        $this->mysqli = $mysqli;
        $this->Token = $Token;
        $this->Privilege = $Privilege;
        $this->Username = $Username;
        $this->Password = $Password;
        $this->EmployeeID = $EmployeeID;
    }

    static function createAccount(
        mysqli $mysqli,
        string $Username, 
        string $Password, 
        string $Privilege, 
        string $EmployeeID
        ): ?Account{
        $Acc = null;
        $Password = password_hash($Password, PASSWORD_BCRYPT);

        $stmt = "INSERT INTO Account ("
        . Account::col_Username .", `". Account::col_Password 
        ."`, ". Account::col_Privilege . ",". Account::col_EmployeeID . ") VALUES (?, ?, ?, ?)";

        $stmt_Account = mysqli_prepare($mysqli, $stmt);
        mysqli_stmt_bind_param($stmt_Account, "ssss", $Username, $Password, $Privilege, $EmployeeID);
        if (mysqli_stmt_execute($stmt_Account)){
            $Acc = new Account($mysqli, $Username, $Password, "", $Privilege, $EmployeeID);
        }
        
        return $Acc;
    }

    static function getAccount(
        mysqli $mysqli, 
        string $Username, 
        string $Password
        ): ?Account{
        $stmt_Account = mysqli_prepare($mysqli, 
        "SELECT * FROM Account WHERE ". Account::col_Username ." = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_Account, "s", $Username);
        mysqli_stmt_execute($stmt_Account);
        #Store results before calling bind
        mysqli_stmt_store_result($stmt_Account); 
        #Prepare variables for binding
        mysqli_stmt_bind_result($stmt_Account,$Username, $Password2, $Token, $Privilege, $EmployeeID); 
        if(mysqli_stmt_num_rows($stmt_Account) > 0){
            #Fetch results into the variables declared for binding earlier
            mysqli_stmt_fetch($stmt_Account); 
            
            if (password_verify($Password, $Password2))
            return new Account($mysqli, $Username, $Password2, $Token ?? "", $Privilege , $EmployeeID);
        }

        return null;
    }

    static function getAccount_usingToken(
        mysqli $mysqli,
        string $Token
        ): ?Account{
        $stmt_Account = mysqli_prepare($mysqli, 
        "SELECT * FROM Account WHERE ". Account::col_Token ." = ? LIMIT 1");

        mysqli_stmt_bind_param($stmt_Account, "s", $Token);
        mysqli_stmt_execute($stmt_Account);
        /*Store results before calling bind*/
        mysqli_stmt_store_result($stmt_Account); 
        #Prepare variables for binding
        mysqli_stmt_bind_result($stmt_Account,$Username, $Password, $Token, $Privilege, $EmployeeID); 

        if(mysqli_stmt_num_rows($stmt_Account) > 0){
            /*Fetch results into the variables declared for binding earlier*/
            mysqli_stmt_fetch($stmt_Account); 
            
            return new Account($mysqli, $Username, $Password, $Token, $Privilege, $EmployeeID);
        }

        return null;
    }

    function setToken(string $Token): bool{
        $stmt_Account = mysqli_prepare($this->mysqli, 
        "UPDATE Account SET ". Account::col_Token . " = ? WHERE ". Account::col_Username ." = ?");
        mysqli_stmt_bind_param($stmt_Account, "ss", $Token, $this->Username);
        $status = mysqli_stmt_execute($stmt_Account);
        if ($status){
            $this->Token = $Token;
        }

        return $status;
    }

    function getUsername(): string{return $this->Username;}
    function getPassword(): string{return $this->Password;}
    function getToken(): string{return $this->Token;}
    function getPrivilege(): string{return $this->Privilege;}
    function getEmployeeID(): string{return $this->EmployeeID;}

    static function TryCatch(callable $func, ...$funcParams): Account|string|bool|null{
        try {
            return $func(...$funcParams);
        } catch(mysqli_sql_exception $err){
            die($err->getMessage());
        } catch (\Throwable $th) {
            die($th);
        }
    }

    
}

class Employee{
    public const col_EmployeeID = "EmployeeID";
    public const col_EmployeeName = "EmployeeName";
    public const col_EmployeeGrade = "EmployeeGrade";

    private mysqli $mysqli;
    private string $EmployeeID;
    private string $EmployeeName;
    private string $EmployeeGrade;

    private function __construct(
        mysqli $mysqli, 
        string $EmployeeID, 
        string $EmployeeName, 
        string $EmployeeGrade
        )
    {
        $this->mysqli = $mysqli;
        $this->EmployeeID =  $EmployeeID;
        $this->EmployeeName = $EmployeeName;
        $this->EmployeeGrade = $EmployeeGrade;
    }

    static function createEmployee(
        mysqli $mysqli, 
        string $EmployeeName, 
        string $EmployeeGrade
        ): ?Employee{
        $Emp = null;
        $random = new Randomizer();
        $EmployeeID = implode("", $random->shuffleArray(str_split("abcdefghijklmnopqrstuvwxyz0123456789")));
        
        $stmt_Employee = mysqli_prepare($mysqli, 
        "INSERT INTO Employee (".Employee::col_EmployeeID.", ".Employee::col_EmployeeName.", "
        .Employee::col_EmployeeGrade.") VALUES (?, ?, ?)");

        mysqli_stmt_bind_param($stmt_Employee, "sss", $EmployeeID, $EmployeeName, $EmployeeGrade);
        
        if (mysqli_stmt_execute($stmt_Employee)){
            $Emp = new Employee($mysqli, $EmployeeID, $EmployeeName, $EmployeeGrade);
        }

        return $Emp;
    }

    static function getEmployee_withEmployeeID(
        mysqli $mysqli, 
        string $EmployeeID
    ){

        $stmt_Employee = mysqli_prepare($mysqli, 
        "SELECT * FROM Employee WHERE ".Employee::col_EmployeeID." = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_Employee, "s", $EmployeeID);
        mysqli_stmt_execute($stmt_Employee);
        mysqli_stmt_store_result($stmt_Employee); #Store results before calling bind
        mysqli_stmt_bind_result($stmt_Employee, $EmployeeID, $EmployeeName, $EmployeeGrade); #Prepare variables for binding

        if(mysqli_stmt_num_rows($stmt_Employee) > 0){
            mysqli_stmt_fetch($stmt_Employee); #Fetch results into the variables declared for binding earlier
            return new Employee($mysqli, $EmployeeID, $EmployeeName, $EmployeeGrade);
        }

        return null;
    }

    static function getEmployee_withEmployeeName(
        mysqli $mysqli, 
        string $EmployeeName
    ){

        $stmt_Employee = mysqli_prepare($mysqli, 
        "SELECT * FROM Employee WHERE ".Employee::col_EmployeeName." = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_Employee, "s", $EmployeeName);
        mysqli_stmt_execute($stmt_Employee);
        mysqli_stmt_store_result($stmt_Employee); #Store results before calling bind
        mysqli_stmt_bind_result($stmt_Employee, $EmployeeID, $EmployeeName, $EmployeeGrade); #Prepare variables for binding

        if(mysqli_stmt_num_rows($stmt_Employee) > 0){
            mysqli_stmt_fetch($stmt_Employee); #Fetch results into the variables declared for binding earlier
            return new Employee($mysqli, $EmployeeID, $EmployeeName, $EmployeeGrade);
        }

        return null;
    }

    function getEmployeeID(): string{return $this->EmployeeID;}
    function getEmployeeName(): string{return $this->EmployeeName;}
    function getEmployeeGrade(): string{return $this->EmployeeGrade;}

    static function TryCatch(callable $func, ...$funcParams): Employee|string|bool|null{
        try {
            return $func(...$funcParams);
        } catch(mysqli_sql_exception $err){
            die($err->getMessage());
        } catch (\Throwable $th) {
            die($th);
        }
    }
}

class Grades implements IteratorAggregate{
    public const col_EmployeeGrade = "EmployeeGrade";

    private $grades = [];

    private function __construct($grades = []){
        $this->grades = $grades;
    }

    static function getGrades(mysqli $mysqli){
        $_Grade = null;

        $stmt_Grades = mysqli_prepare($mysqli, 
        "SELECT ".Grades::col_EmployeeGrade." FROM Grade");

        mysqli_stmt_execute($stmt_Grades);
        $Result = mysqli_stmt_get_result($stmt_Grades);
        $grades = [];
        while ($grade = mysqli_fetch_assoc($Result)) {
            $grades[] = $grade["EmployeeGrade"];
        }
        $_Grade = new Grades($grades);
        return $_Grade;
    }

    static function addGrade(mysqli $mysqli, string $grade){
        $stmt_Grades = mysqli_prepare($mysqli, 
        "INSERT INTO Grade (EmployeeGrade) VALUES (?)");
        mysqli_stmt_bind_param($stmt_Grades, "s", $grade);
        $status = mysqli_stmt_execute($stmt_Grades);
        return $status;
    }

    static function removeGrade(mysqli $mysqli, string $grade){
        $stmt_Grades = mysqli_prepare($mysqli, 
        "DELETE FROM Grade WHERE EmployeeGrade = ?");
        mysqli_stmt_bind_param($stmt_Grades, "s", $grade);
        $status = mysqli_stmt_execute($stmt_Grades);
        return $status;
    }

    function getIterator(): Traversable{return new ArrayIterator($this->grades);}

    static function TryCatch(callable $func, ...$funcParams): Grades|string|bool|null{
        try {
            return $func(...$funcParams);
        } catch(mysqli_sql_exception $err){
            die($err->getMessage());
        } catch (\Throwable $th) {
            die($th);
        }
    }
}

class GradeAllocation implements IteratorAggregate{
    public const col_EmployeeGrade = "EmployeeGrade";
    public const col_Allocations = "Allocations";
    public const col_LeaveCategory = "LeaveCategory";

    private $gradeallocations = [];
    private string $leavetype;

    private function __construct($gradeallocations, string $leavetype)
    {
        $this->gradeallocations = $gradeallocations;
        $this->leavetype = $leavetype;
    }

    static function queryGradeAllocation(mysqli $mysqli, string $leavetype): ?GradeAllocation{
        $GradeAllocation = null;
        $stmt_GradeAllocation = mysqli_prepare($mysqli, 
        "SELECT * FROM Grade_Allocation WHERE LeaveCategory = ?");
        mysqli_stmt_bind_param($stmt_GradeAllocation, "s", $leavetype);
        mysqli_stmt_execute($stmt_GradeAllocation);
        $Result = mysqli_stmt_get_result($stmt_GradeAllocation);
        $_GradeAllocations = [];
        while ($gradeallocation = mysqli_fetch_assoc($Result)) {
            $_GradeAllocations[] = $gradeallocation;
        }
        if (mysqli_num_rows($Result) > 0)
        $GradeAllocation = new GradeAllocation($_GradeAllocations, $leavetype);
        return $GradeAllocation;
    }

    static function setAllocation(
        mysqli $mysqli, string $grade,
        string $leavetype, int $allocation): bool{
        $stmt_GradeAllocation = mysqli_prepare($mysqli, 
        "UPDATE Grade_Allocation SET Allocations = ? WHERE EmployeeGrade = ? AND LeaveCategory = ?");
        mysqli_stmt_bind_param($stmt_GradeAllocation, "iss", $allocation , $grade, $leavetype);
        $status = mysqli_stmt_execute($stmt_GradeAllocation);
        return $status;
    }

    static function getAllocation(mysqli $mysqli, string $grade, string $leavetype): int{
        $allocation = 0;
        $stmt_GradeAllocation = mysqli_prepare($mysqli, 
        "SELECT COALESCE(SUM(Allocations), 0) AS Allocated FROM Grade_Allocation WHERE EmployeeGrade = ? AND LeaveCategory = ?");

        mysqli_stmt_bind_param($stmt_GradeAllocation, "ss", $grade, $leavetype);
        mysqli_stmt_execute($stmt_GradeAllocation);
        mysqli_stmt_bind_result($stmt_GradeAllocation, $allocation);
        mysqli_stmt_fetch($stmt_GradeAllocation);
        return $allocation;
    }

    static function prepareLeaveCategoryGradeAllocations(mysqli $mysqli, string $leavetype): bool{
        $sql1 = "INSERT INTO Grade_Allocation(EmployeeGrade, Allocations, LeaveCategory) ";
        $sql2 = "SELECT Grade.EmployeeGrade, 0 AS Allocations, ? AS LeaveCategory FROM Grade WHERE NOT EXISTS(";
        $sql3 = "SELECT 1 FROM Grade_Allocation GA WHERE GA.EmployeeGrade = Grade.EmployeeGrade AND GA.LeaveCategory = ?);";
        $stmt_GradeAllocation = mysqli_prepare($mysqli, $sql1 . $sql2 . $sql3);
        mysqli_stmt_bind_param($stmt_GradeAllocation, "ss", $leavetype, $leavetype);
        $status = mysqli_stmt_execute($stmt_GradeAllocation);

        return $status;
    }

    function getLeaveType(){return $this->leavetype;}

    function getIterator(): Traversable {return new ArrayIterator($this->gradeallocations);}

    static function TryCatch(callable $func, ...$funcParams): GradeAllocation|int|string|bool|null{
        try {
            return $func(...$funcParams);
        } catch(mysqli_sql_exception $err){
            die($err->getMessage());
        } catch (\Throwable $th) {
            die($th);
        }
    }
}

class LeaveCategories implements IteratorAggregate{
    public const col_LeaveCategory = "LeaveCategory";

    private $leavecategories = [];

    private function __construct(array $leavecategories){
        $this->leavecategories = $leavecategories;
    }

    static function getLeaveCategories(mysqli $mysqli): ?LeaveCategories{
        $_LeaveType = null;
        $stmt_LeaveType = mysqli_prepare($mysqli, "SELECT LeaveCategory FROM LeaveType");
        mysqli_stmt_execute($stmt_LeaveType);
        $Result = mysqli_stmt_get_result($stmt_LeaveType);
        $LeaveCategories = [];
        while ($leavecategory = mysqli_fetch_assoc($Result)) {
            $LeaveCategories[] = $leavecategory["LeaveCategory"];
        }
        $_LeaveType = new LeaveCategories($LeaveCategories);
        return $_LeaveType;
    }

    static function addLeaveType(mysqli $mysqli, string $LeaveCategory, ?LeaveCategories $LT): bool{
        $stmt_LeaveType = mysqli_prepare($mysqli, "INSERT INTO LeaveType (LeaveCategory) VALUES (?)");
        mysqli_stmt_bind_param($stmt_LeaveType, "s", $LeaveCategory);
        $status = mysqli_stmt_execute($stmt_LeaveType);
        if(!$status){
            return $status;
        }
        if($LT){
            $LT->leavecategories[] = $LeaveCategory;
        }
        return $status;
    }

    static function deleteLeaveType(mysqli $mysqli, string $LeaveCategory, ?LeaveCategories $LT): bool{
        $stmt_LeaveType = mysqli_prepare($mysqli, "DELETE FROM LeaveType WHERE LeaveCategory = ?");
        mysqli_stmt_bind_param($stmt_LeaveType, "s", $LeaveCategory);
        $status = mysqli_stmt_execute($stmt_LeaveType);
        if(!$status){
            return $status;
        }
        if($LT){
            $LT->leavecategories = array_filter($LT->leavecategories, function($_LeaveCategory) use($LeaveCategory){
                return $_LeaveCategory !== $LeaveCategory;
            });
        }
        return $status;
    }

    function getIterator(): Traversable {return new ArrayIterator($this->leavecategories);}

    static function TryCatch(callable $func, ...$funcParams): LeaveCategories|string|bool|null{
        try {
            return $func(...$funcParams);
        } catch(mysqli_sql_exception $err){
            die($err->getMessage());
        } catch (\Throwable $th) {
            die($th);
        }
    }
}

class LeaveApplications implements IteratorAggregate{

    public const col_LeaveApplicationID = "LeaveApplicationID";
    public const col_EmployeeID = "EmployeeID";
    public const col_LeaveCategory = "LeaveCategory";
    public const col_StartDate = "StartDate";
    public const col_EndDate = "EndDate";
    public const col_LeaveStatus = "LeaveStatus";
    public const col_ApprovedBy = "ApprovedBy";
    public const col_EmployeeGrade = "EmployeeGrade";
    public const col_EmployeeName = "EmployeeName";

    private $leaveapplications = [];

    private function __construct(array $leaveapplications)
    {
        $this->leaveapplications = $leaveapplications;
    }

    static function getAllPendingLeaveApplications(mysqli $mysqli): LeaveApplications{
        $stmt_LeaveApplication = mysqli_prepare($mysqli, "SELECT * FROM Leave_Application ". 
        "INNER JOIN Employee ON Leave_Application.EmployeeID = Employee.EmployeeID WHERE Leave_Application.LeaveStatus = ?");
        $appliStatus = LeaveStatus::Pending;
        mysqli_stmt_bind_param($stmt_LeaveApplication, "s", $appliStatus);
        mysqli_stmt_execute($stmt_LeaveApplication);
        $result = mysqli_stmt_get_result($stmt_LeaveApplication);
        $_leaveapplications = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $_leaveapplications[] = $row;
        }
        return new LeaveApplications($_leaveapplications);
    }

    static function getLeaveApplications_withEmployeeID(mysqli $mysqli, string $EmployeeID){
        $stmt_LeaveApplication = mysqli_prepare($mysqli, "SELECT * FROM Leave_Application ". 
        "INNER JOIN Employee ON Leave_Application.EmployeeID = Employee.EmployeeID ".
        " WHERE Leave_Application.". 
        LeaveApplications::col_EmployeeID ." = ?");
        mysqli_stmt_bind_param($stmt_LeaveApplication, "s", $EmployeeID);
        mysqli_stmt_execute($stmt_LeaveApplication);
        $result = mysqli_stmt_get_result($stmt_LeaveApplication);
        $_leaveapplications = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $_leaveapplications[] = $row;
        }
        return new LeaveApplications($_leaveapplications);
    }

    static function setStatusEmployeeLeaveApplication(
        mysqli $mysqli, 
        string $LeaveApplicationID, 
        string $approvalstatus
        ): bool{
        $stmt_LeaveApplication = mysqli_prepare($mysqli, "UPDATE Leave_Application SET LeaveStatus = ? " . 
        "WHERE Leave_Application." . LeaveApplications::col_LeaveApplicationID . " = ?");
        mysqli_stmt_bind_param($stmt_LeaveApplication, "ss", $approvalstatus, $LeaveApplicationID);
        $status = mysqli_stmt_execute($stmt_LeaveApplication);
        return $status;
    }

    static function createEmployeeLeaveApplication(
        mysqli $mysqli, 
        string $SentLeaveType, 
        string $From, 
        string $To, 
        string $EmpID
        ): bool{
        $sql1 = "INSERT INTO Leave_Application (EmployeeID, LeaveCategory, StartDate, EndDate, LeaveStatus) ";
        $sql2 = "VALUES (?, ?, ?, ?, ?)";
        if(!$SentLeaveType or !$From or !$To or !$EmpID){
            throw new Exception("Creating Employee Leave Application failed, empty inputs!", 1);
        }
        $PendingStatus = LeaveStatus::Pending;
        $stmt_LeaveApplication = mysqli_prepare($mysqli, $sql1 . $sql2);
        mysqli_stmt_bind_param($stmt_LeaveApplication, "sssss", $EmpID, $SentLeaveType, $From, $To, $PendingStatus);
        $status = mysqli_stmt_execute($stmt_LeaveApplication);
        return $status;
    }

    function getIterator(): Traversable {return new ArrayIterator($this->leaveapplications);}

    static function TryCatch(callable $func, ...$funcParams): LeaveApplications|string|bool|null{
        try {
            return $func(...$funcParams);
        } catch(mysqli_sql_exception $err){
            die($err->getMessage());
        } catch (\Throwable $th) {
            die($th);
        }
    }
}

class EmployeeAllocation implements IteratorAggregate{

    public const col_EmployeeID = "EmployeeID";
    public const col_LeaveCategory = "LeaveCategory";
    public const col_UsedAllocations = "UsedAllocations";

    private $employeeallocation = [];
    

    private function __construct(array $employeeallocation)
    {
        $this->employeeallocation = $employeeallocation;
    }

    static function queryAllEmployeeAllocations(mysqli $mysqli): EmployeeAllocation{
        $approved = LeaveStatus::Approved;
        $sql = <<< Group
        SELECT * FROM Leave_Application
        INNER JOIN (SELECT LA.EmployeeID, Max(LA.EndDate) AS MaxEndDate 
        FROM Leave_Application LA GROUP BY LA.EmployeeID) AS 
        Latest ON Leave_Application.EmployeeID = Latest.EmployeeID AND Leave_Application.EndDate = Latest.MaxEndDate
        INNER JOIN Employee ON Employee.EmployeeID = Leave_Application.EmployeeID
        INNER JOIN Grade_Allocation ON 
        Grade_Allocation.EmployeeGrade = Employee.EmployeeGrade AND Grade_Allocation.LeaveCategory = Leave_Application.LeaveCategory
        INNER JOIN Employee_Allocation ON 
        Employee_Allocation.EmployeeID = Leave_Application.EmployeeID AND Employee_Allocation.LeaveCategory = Leave_Application.LeaveCategory
        WHERE Leave_Application.LeaveStatus = '$approved' GROUP BY Leave_Application.EmployeeID ORDER BY Leave_Application.LeaveApplicationID ASC
        Group;
        $stmt_EmployeeAllocation = mysqli_prepare($mysqli, $sql);
        #mysqli_stmt_bind_param($stmt_EmployeeAllocation, "s", $approved);
        mysqli_stmt_execute($stmt_EmployeeAllocation);
        $result = mysqli_stmt_get_result($stmt_EmployeeAllocation);
        $_employeeallocation = [];
        while ($row = mysqli_fetch_assoc($result)){
            $_employeeallocation[] = $row;
        }
        return new EmployeeAllocation($_employeeallocation);
    }

    static function incrementUsedAllocations(mysqli $mysqli, string $employeeID, string $leaveCategory): bool{
        $sql1 = "INSERT INTO Employee_Allocation (EmployeeID, LeaveCategory) VALUES (?, ?) ".
        " ON DUPLICATE KEY UPDATE UsedAllocations = UsedAllocations + 1";
        $stmt_EmployeeAllocation = mysqli_prepare($mysqli, $sql1);
        mysqli_stmt_bind_param($stmt_EmployeeAllocation, "ss", $employeeID, $leaveCategory);
        $status = mysqli_stmt_execute($stmt_EmployeeAllocation);
        return $status;
    }
    
    function getIterator(): Traversable {return new ArrayIterator($this->employeeallocation);}

    static function TryCatch(callable $func, ...$funcParams): EmployeeAllocation|string|bool|null{
        try {
            return $func(...$funcParams);
        } catch(mysqli_sql_exception $err){
            die($err->getMessage());
        } catch (\Throwable $th) {
            die($th);
        }
    }
}
?>