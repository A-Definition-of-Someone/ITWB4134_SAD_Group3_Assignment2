# Software Archtecture and Design - Assignment 2 - Group 3

Group Members consists of Muhammad Ayman bin Muhammad Idzmi (MC230120384) and Karthiga A/P Nilamegan (MC230923862).
For Assignment 2 we chose to make a Leave Management system.

## Installation

# Folder Extraction
Extract zip file to Xampp's htdocs and make sure the folder is named ITWB4134_SAD_GROUP3_ASSIGNMENT2 and the folder is not in any other folder considering the path is hardcoded. Expected path: htdocs/ITWB4134_SAD_Group3_Assignment2/

# Database Initalization

<ol>
    <li>Create a database name in PHPMYADMIN</li>
    <li>Go to Import option</li>
    <li>Press Choose file: LeaveManagement.sql</li>
</ol>

# htdocs Configuration

1) Open index.php in the htdocs folder and look at line 8 with the header function.
2) Replace the url listed with '/ITWB4134_SAD_Group3_Assignment2/'

## Using the Leave Management System

# Account
There are two accounts in the database that can be used to access the system:
1) Username: HR, Password: HR, Grade: Manager (Do not delete this grade as it is hardcoded in the system)
2) Username: HEY, Password: Hey, Grade: Test

# Hardcoded Role
Manager is a hardcoded role in the system which allows one to:
1) Allocate leave allocation to Grade per Leave Type
2) Add / Delete Leave Type
3) Add / Delete Grade

# Employee Registration (URL: http://localhost/ITWB4134_SAD_Group3_Assignment2/Admin%20-%20Register%20Employee.php)
For convenience, employee registration is not locked behind accounts, just in case HR account is deleted.

# Start Accessing the system
The system can be accessed by typing localhost, the index.php modified in htdocs should redirect to login page