<?php require "NavbarLinks.php"; ?>
<!DOCTYPE html>
<html>
 <head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>leave request</title>
   <link rel="stylesheet" href="CommonVariables.css">
   <link rel="stylesheet"  href="Menu.css">
   <link rel="stylesheet"  href="form.css">
 </head>

<body>
<!-- Navigation bar -->
 <nav id="Navbar">
        <div id="LinkContainer">
            <?php echo $NavbarLinks; ?>
        </div>
        <!-- Log out button -->
        <button id="LogOut">Log Out</button>
    </nav>

<!-- container and leave type input field -->
  <fieldset>
  <form action="/leave_manage.php" method="post">
   <div class="row">
   <div class="col-25">
  <label for= "leave-type">Leave Type:</label>
  </div>
    <div class="col-75">
   <select name="leave-type" id="leave-type">
   <option value= "sick">Sick</option>
   <option value="maternaty leave">maternaty leave</option>
   <option value="unpaid leave">unpaid leave</option>
   <option value="anual leave">anual leave</option>
   </select>
  
  
  <!-- From input field-->
      <div class="row">
     <div class="col-25">
	 <label for="leave-from"> From:</label>
	 </div>
	 <div class="col-75">
	  <input type="date" id="leave-from" name="From" value="date">
	</div>
	</div>

  <!-- To input field -->	
	 <div class="row">
     <div class="col-25">
	 <label for="leave-to">To:</label>
	 </div>
	 <div class="col-75">
	  <input type="date" id="leave-to" name="From" value="date">
	</div>
	</div>
	
	<!-- submit button -->	
<div class="row">
   <input type="submit" value="submit"/>
   </div>
  </div> 
  </fieldset>
   
   
 

  </form>
  
 </body>
</html>