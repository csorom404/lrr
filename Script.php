<?php
include 'NoDirectPhpAcess.php';
?>


<?php

/* 
 * This file contains the main Server-side scripts for the project.
 */

session_start();

date_default_timezone_set('Asia/Shanghai');

// include "get_mysql_credentials.php";
$con = mysqli_connect("localhost",  "root", "", "lrr");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

error_reporting(0);

// #### FUNCTION CHECK FILE TYPES ////

function is_valid_student_number($student_id)
{
    // zjnu student number has 12 digits, and starts with 20
    if (strlen($student_id) == 12  && is_numeric($student_id) == TRUE && substr($student_id, 0, 2) == "20")
        return TRUE;
    return FALSE;
}

// ############################### SIGN UP ##################################
if (!empty($_POST["form_signup"])) {
    $student_id = trim(mysqli_real_escape_string($con, $_POST["user_student_id"]));
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $password = mysqli_real_escape_string($con, $_POST["password"]);
    $confirmpassword = mysqli_real_escape_string($con, $_POST["confirmpassword"]);

    $upperLetter     = preg_match('@[A-Z]@',    $password);
    $smallLetter     = preg_match('@[a-z]@',    $password);
    $containsDigit   = preg_match('@[0-9]@',    $password);
    $containsSpecial = preg_match('@[^\w]@',    $password);
    $containsAll = $upperLetter && $smallLetter && $containsDigit && $containsSpecial;

    // check for strong password
    if (!$containsAll) {
        $_SESSION['info_signup'] = "Password must have at least characters that include lowercase letters, uppercase letters, numbers and special characters (e.g., !?.,*^).";
        header("Location: signup.php");
        return;
    }

    // Check confirmed password
    if (strcasecmp($password, $confirmpassword) != 0) {
        $_SESSION['info_signup'] = "Password confirmation failed.";       
        header("Location: signup.php");
        return;
    }

    // validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['info_signup'] = "Invalid email address.";
        header("Location: signup.php");
        return;
    }

    // check if email is taken
    $result = mysqli_query($con, "SELECT * FROM users_table WHERE email='$email'");
    if (mysqli_num_rows($result) != 0) {
        $_SESSION["info_signup"] = "Email address " . $email . "  is already in use.  You have already signed up?";
    }

    $_SESSION['user_fullname'] = $_POST["fullname"];
    $_SESSION['user_fullname_temp'] = $_POST["fullname"];
    $_SESSION['user_email'] = $_POST["email"];
    $_SESSION['user_student_id_temp'] = $_POST["user_student_id"];
   


    // validate student number
    if (!is_valid_student_number($student_id)) {
        $_SESSION["info_signup"] = "Invalid student number.";
        $_SESSION['user_fullname'] = null;
        header("Location: signup.php");
        return;
    }

    // Check if this student number is a legal one
    $result = mysqli_query($con, "SELECT * FROM `students_data` WHERE Student_ID='$student_id'");
    if (mysqli_num_rows($result) == 0) {
        $_SESSION["info_signup"] = "Your entered student number could not be verified.  Please contact Student Management Office <lanhui at zjnu.edu.cn>.  Thanks.";
          $_SESSION['user_fullname'] = null;

        

        header("Location: signup.php");
        return;
    }


    // Check if the student number isn't already registered

    $student_result = mysqli_query($con, "SELECT * FROM `users_table` WHERE Student_ID='$student_id'");
    if (mysqli_num_rows($student_result) > 0) {
        $_SESSION["info_signup"] = "This Student ID is already in use! Please contact Student Management Office <lanhui at zjnu.edu.cn> for help.";
        $_SESSION['user_fullname'] = null;
        header("Location: signup.php");
        return;
    }
}

// ############################### CREATE STUDENT USER ##################################
if (!empty($_POST["form_signup"])) {
    $fullname = mysqli_real_escape_string($con, $_POST["fullname"]);
    $student_id = mysqli_real_escape_string($con, $_POST["user_student_id"]);

    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $password = mysqli_real_escape_string($con, $_POST["password"]);
    $confirmpassword = mysqli_real_escape_string($con, $_POST["confirmpassword"]);

    $_SESSION['user_student_id'] = $_POST["student_id"];
    $_SESSION['user_type'] = "Student";
    
    // check confirmed password
    if (strcasecmp($password, $confirmpassword) != 0) {
        $_SESSION['info_signup'] = "Password confirmation failed.";
        $_SESSION['user_fullname'] = null;  // such that Header.php do not show the header information.        
        header("Location: signup.php");
        return;
    }

    // validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['info_signup'] = "Invalid email address.";
        $_SESSION['user_fullname'] = null;

        header("Location: signup.php");
        return;
    }

    $upperLetter     = preg_match('@[A-Z]@',    $password);
    $smallLetter     = preg_match('@[a-z]@',    $password);
    $containsDigit   = preg_match('@[0-9]@',    $password);
    $containsSpecial = preg_match('@[^\w]@',    $password);
    $containsAll = $upperLetter && $smallLetter && $containsDigit && $containsSpecial;

    // check for strong password
    if (!$containsAll) {
        $_SESSION['info_signup'] = "Password must have at least characters that include lowercase letters, uppercase letters, numbers and special characters (e.g., !?.,*^).";
        $_SESSION['user_fullname'] = null;

        header("Location: signup.php");
        return;
    }

    // check if email is taken
    $result = mysqli_query($con, "SELECT * FROM users_table WHERE email='$email'");
    if(mysqli_num_rows($result) != 0)
    {
        $_SESSION["info_signup"]="Email address ".$email." is already in use.  Do you have an old LRR account?";
    }


    $_SESSION['user_type'] = "Student";
    $_SESSION['user_email'] = $email;
    $_SESSION['user_student_id'] = $student_id;

    // apply password_hash()
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO `users_table`(`Email`, `Password`, `HashPassword`, `Full_Name`, `UserType`, `Student_ID`) VALUES "
        . "('$email','$password_hash','','$fullname','Student','$student_id')";
    

    $_SESSION['user_fullname'] =$_SESSION['user_fullname_temp'];

    if ($con->query($sql) === TRUE) {
        header("Location: Courses.php");
    } else {
        echo "Something really bad (SQL insertion error) happened during sign up.";
    }
}

// ################################ LOGIN  #####################################

if (!empty($_POST["form_login"])) {

    $user = mysqli_real_escape_string($con, $_POST["user"]); // user could be a 12-digit student number or an email address
    $is_student_number = 0;

    $_SESSION["failed_login_user"] = $user;  // Save the entered username in a session variable
    echo "Failed login user: " . $_SESSION["failed_login_user"];

    // Validate student number
    if (is_valid_student_number($user)) {
        $is_student_number = 1;
    }


    // Validate email address if what provided is not a student number
    if (!$is_student_number && !filter_var($user, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["info_login"] = "Invalid email address: " . "$user";
        header("Location: index.php");
        return;
    }

    $password = mysqli_real_escape_string($con, $_POST["password"]);
    $result = mysqli_query($con, "SELECT * FROM users_table WHERE (Student_ID='$user') OR (Email='$user')");
    if (mysqli_num_rows($result) == 0) {
        $_SESSION["info_login"] = "Invalid user name information.";
        echo $_SESSION["info_login"];
        header("Location: index.php");
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            //  verify the hashed password and unhashed password
            $sha512pass = hash('sha512', $password); // for backward compatibility.  Old passwords were hashed using SHA512 algorithm.
            if (password_verify($password, $row["Password"]) or $sha512pass == $row["HashPassword"]) {

                $_SESSION['user_id'] = $row['User_ID'];
                $_SESSION['user_email'] = $row['Email'];
                $_SESSION['user_student_id'] = $row['Student_ID'];
                $_SESSION['user_type'] = $row['UserType'];
                $_SESSION['user_fullname'] = $row['Full_Name'];

                if ($_SESSION['user_type'] == "Student") {
                    header("Location: Courses.php");
                }

                if ($_SESSION['user_type'] == "Lecturer") {
                    header("Location: Courses.php");
                }

                if ($_SESSION['user_type'] == "TA") {
                    header("Location: Courses.php");
                }

                if ($_SESSION['user_type'] == "Admin") {
                    header("Location: Admin.php");
                }
                //  report wrong pass if not correct
                return;

            }  else {
                
                $_SESSION["wrong_pass"] = "Wrong Password.";
                echo $_SESSION["wrong_pass"];  // Optional: Display the error message for debugging
               
                header("Location: index.php");
                exit();  // Add this line to prevent further execution after redirect
            }
            // Add the following line to reset the session variable when needed
            unset($_SESSION["failed_login_user"]);

        }
    }
}

// ################################ Recover Password  #####################################

if (!empty($_POST["form_recover_password"])) {

    $student_id = mysqli_real_escape_string($con, $_POST["sno"]);
    $email = mysqli_real_escape_string($con, $_POST["email"]);

    // validate student number
    if (strlen($student_id) != 12  || is_numeric($student_id) == FALSE) {
        $_SESSION["info_recover_password"] = "Invalid student number.";
        header("Location: recover_password.php");
        return;
    }

    // validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["info_recover_password"] = "Invalid email address.";
        // echo "Invalid email address.";
        header("Location: recover_password.php");
        return;
    }

    $result = mysqli_query($con, "SELECT * FROM users_table WHERE Email='$email' and Student_ID='$student_id'");
    if (mysqli_num_rows($result) == 0) {
        $_SESSION["info_recover_password"] = "Email address is not recognised.";
        $_SESSION["info_recover_password"] = "Identity not recognized.  Try again or send an inquiry email message to lanhui at zjnu.edu.cn.";
        header("Location: recover_password.php");
    } else {
        $result = mysqli_query($con, "DELETE FROM users_table WHERE Email='$email' and Student_ID='$student_id'");
        header("Location: signup.php");
    }
}

// ################################ RESET Password  #####################################

if (!empty($_POST["form_reset_password"])) {
    $password = mysqli_real_escape_string($con, $_POST["password"]);
    $token = mysqli_real_escape_string($con, $_POST["token"]);
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $result = mysqli_query(
        $con,
        "SELECT * FROM Users_Table WHERE email='$email'"
    );
    if (mysqli_num_rows($result) == 0) {

        echo "invalid email";
        return;
    } else {
        while ($row = mysqli_fetch_assoc($result)) {

            $userid = $row['User_ID'];

            $email = $row['Email'];
            $id = $row['Student_ID'];

            $user_token = $userid * $userid * $userid + $userid * 0.00343;
            if ($user_token == $token) {
                // Password Update

                // Password Update
                $hashed_password = hash('sha512', $password);
                $sql = "UPDATE users_table set HashPassword='$hashed_password' where User_ID='$userid';";
                if ($con->query($sql) === TRUE) {

                    error_reporting(0);

                    $_SESSION["info_login"] = " Password changed successfully , you can login now with your new password ";
                    header("Location: index.php");
                } else {
                    echo "Error: " . $sql . "<br>" . $con->error;
                }
            } else {
                echo "Invalid Token ";
            }
        }
    }
}

// ############################### CREATE Lecturer/TA USER ##################################
if (!empty($_POST["form_createlecturrer"])){
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $fullname = mysqli_real_escape_string($con, $_POST["fullname"]);
    $type = mysqli_real_escape_string($con, $_POST["type"]);
    $password = mysqli_real_escape_string($con, $_POST["password"]);
    $pass_len = strlen($password);
    if ($pass_len == 0) {
        $password = generateStrongPassword();
    }

    $result = mysqli_query(
        $con,
        "SELECT * FROM users_table WHERE email='$email'"
    );
    if (mysqli_num_rows($result) != 0) {
        $_SESSION["info_Admin_Users"] = "Email address : " . $email . " is already in use.";
        header("Location: Admin.php");
        exit;
    }
    $password_hash = password_hash("$password", PASSWORD_DEFAULT);
    $sql = "INSERT INTO `users_table`(`Email`, `Password`, `HashPassword`, `Full_Name`, `UserType`) VALUES ('$email','$password_hash','','$fullname','$type')";

    try {
        $result = mysqli_query($con, $sql);
        $_SESSION["info_Admin_Users"] = $type . " user created successfully. Use email " . $email . " as account name and ". $password ." as password.";
        header("Location: Admin.php");
    } catch (Exception $ex) {
        echo "$ex";
    }
}

// ### FUNCTION TO GENERATE INITIAL PASSWORDS ###//
function generateStrongPassword() {

    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_';
    $password_length = 12;
    $gen_password = '';
    for ($i = 0; $i < $password_length; $i++) {
        $random_index = mt_rand(0, strlen($characters) - 1);
        $gen_password .= $characters[$random_index];
    }

    // Return the generated password
    return $gen_password;
}

// #### FUNCTION CHECK FILE TYPES ////

function is_valid_file_format($file)
{

    $allowed =  array(
        'pdf', 'rtf', 'jpg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'sql', 'txt', 'md', 'py', 'css', 'html',
        'cvc', 'c', 'class', 'cpp', 'h', 'java', 'sh', 'swift', 'zip', 'rar', 'ods', 'xlr', 'bak', 'ico', 'swf'
    );

    $filename = $_FILES[$file]['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $result = in_array($ext, $allowed);
    return $result;
}

// #### FUNCTION CREATE DIRECTORIES  ////

function Create_dir($upPath)
{
    try {
        // full path 
        $tags = explode('/', $upPath);            // explode the full path
        $mkDir = "";

        foreach ($tags as $folder) {
            $mkDir = $mkDir . $folder . "/";   // make one directory join one other for the nest directory to make
            echo '"' . $mkDir . '"<br/>';         // this will show the directory created each time
            if (!is_dir($mkDir)) {             // check if directory exist or not
                mkdir($mkDir, 0777);          // if not exist then make the directory
            }
        }
    } catch (Exception $e) {
        return FALSE;
    }
    return $upPath;
}

function mkdirs($path)
{
    if (file_exists($path))
        return $path;
    $result = mkdir($path, 0777, true);
    if ($result) {
        return $path;
    }
    return $result;
}

// ############################### #Post Assignment ##################################
if (!empty($_POST["form_uploadlab"])) {

    $course_id = mysqli_real_escape_string($con, $_POST["course_id"]);
    $deadlinedate = $_POST["deadlinedate"];
    $deadlinetime = $_POST["deadlinetime"];
    $instructions = mysqli_real_escape_string($con, $_POST["instructions"]);
    $title = mysqli_real_escape_string($con, $_POST["title"]);
    $marks = mysqli_real_escape_string($con, $_POST["marks"]);
    //  $url=mysqli_real_escape_string($con,$_POST["url"]);
    $url = $_SESSION['url']; //using real_escape_string was failing to redirect to the main page
    $type = mysqli_real_escape_string($con, $_POST["type"]);

    $deadline = $deadlinedate . " " . $deadlinetime;
    $date =  date("Y-m-d H:i:s");

    #print the date 
    echo $date;

    // GET UPLOADED FILES

    $current_year_month = date('Y-n');

    $target_dir = Create_dir("./../../lrr_submission/Lab_Report_Assignments/$course_id/$current_year_month/" . $title . "/");

    $rnd = rand(10, 1000);
    $rnd = ""; // no more required , creating folder for each lab
    $targetfile  = $target_dir . $rnd . $_FILES['attachment1']['name'];
    $targetfile2 = $target_dir . $rnd . $_FILES['attachment2']['name'];
    $targetfile3 = $target_dir . $rnd . $_FILES['attachment3']['name'];
    $targetfile4 = $target_dir . $rnd . $_FILES['attachment4']['name'];

    $count = 0;

    if (!is_valid_file_format("attachment1") && $_FILES["attachment1"]["name"] != "") {
        echo "Invalid File Type for Attachment 1";
        return;
    }
    if (!is_valid_file_format("attachment2") && $_FILES["attachment2"]["name"] != "") {
        echo "Invalid File Type for Attachment 2";
        return;
    }
    if (!is_valid_file_format("attachment3") && $_FILES["attachment3"]["name"] != "") {
        echo "Invalid File Type for Attachment 3";
        return;
    }

    // use 4 for missing file

    if (move_uploaded_file($_FILES['attachment1']['tmp_name'], $targetfile)) {
        $count++;
    } else {
        echo $_FILES['attachment1']['error'];
    }

    if (move_uploaded_file($_FILES['attachment2']['tmp_name'], $targetfile2)) {
        $count++;
    } else {
        echo $_FILES['attachment2']['error'];
    }

    if (move_uploaded_file($_FILES['attachment3']['tmp_name'], $targetfile3)) {
        $count++;
    } else {
        echo $_FILES['attachment3']['error'];
    }

    if (move_uploaded_file($_FILES['attachment4']['tmp_name'], $targetfile4)) {
        $count++;
    } else {
        echo $_FILES['attachment4']['error'];
    }

    echo $count . " File(s) uploaded";

    //CLEAN
    $targetfile = "";
    $targetfile2 = "";
    $targetfile3 = "";
    $targetfile4 = "";

    if ($_FILES['attachment1']['name'] != "") {
        $targetfile  = "/Lab_Report_Assignments/$course_id/$current_year_month/" . $title . "/" . $_FILES['attachment1']['name'];
    }
    if ($_FILES['attachment2']['name'] != "") {
        $targetfile2 = "/Lab_Report_Assignments/$course_id/$current_year_month/" . $title . "/" . $_FILES['attachment2']['name'];
    }
    if ($_FILES['attachment3']['name'] != "") {
        $targetfile3 = "/Lab_Report_Assignments/$course_id/$current_year_month/" . $title . "/" . $_FILES['attachment3']['name'];
    }
    if ($_FILES['attachment4']['name'] != "") {
        $targetfile4 = "/Lab_Report_Assignments/$course_id/$current_year_month" . $title . "/" . $_FILES['attachment4']['name'];
    }

    $sql = "INSERT INTO `lab_reports_table`(`Course_ID`, `Posted_Date`, `Deadline`, `Instructions`,
                     `Title`, `Attachment_link_1`, `Attachment_link_2`, `Attachment_link_3`, `Attachment_link_4`,Marks,Type) 
                     VALUES ('$course_id','$date','$deadline','$instructions','$title','$targetfile','$targetfile2','$targetfile3','$targetfile3','$marks','$type')";

    if ($con->query($sql) === TRUE) {

        $_SESSION["info_courses"] = $type . " lab report assignment posted successfully. ";
        header("Location: Courses.php?course=" . $url);
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

function checksize($file)
{
    $result = $_FILES["$file"]['size'] / (1024 * 1024);

    if ($result > 1) {
        return FALSE;
    }
    return TRUE;
}

// ############################### Submit Assignment ##################################
if (!empty($_POST["form_submitlab"])) {

    $lab_id = mysqli_real_escape_string($con, $_POST["lab_id"]);
    $student_id = $_POST["student_id"];
    $group_id = $_POST["group_id"];

    $instructions = mysqli_real_escape_string($con, $_POST["instructions"]);
    $title = mysqli_real_escape_string($con, $_POST["title"]);

    $url = mysqli_real_escape_string($con, $_POST["url"]);

    $deadline = $deadlinedate . " " . $deadlinetime;
    $date = date("Y-m-d H:i:s");

    // GET UPLOADED FILES
    $labName = mysqli_query($con, "SELECT * FROM `lab_reports_table` WHERE Lab_Report_ID='$lab_id'");
    while ($row = mysqli_fetch_assoc($labName)) {
        $lab_name = $row['Title'];
        $_SESSION['Sub_Type'] = $row['Type']; // submission type, either Individual or Group
    }

    $upload_folder = "Lab_Report_Submisions"; // old place for storing students' submissions
    $upload_folder = "./../../lrr_submission";
    $target_dir = mkdirs($upload_folder . "/" . $student_id . "/" . $url . "/" . $lab_name . "/"); # url is actually course code plus academic year, e.g., CSC3122020
    $targetfile  = $target_dir . $_FILES['attachment1']['name'];
    $targetfile2 = $target_dir . $_FILES['attachment2']['name'];
    $targetfile3 = $target_dir . $_FILES['attachment3']['name'];
    $targetfile4 = $target_dir . $_FILES['attachment4']['name'];

    $count = 0;

    //check zise
    if (!checksize("attachment1")) {
        echo "1 MB is the maximum file size allowed";
        return;
    }
    if (!checksize("attachment2") && $_FILES["attachment2"]["name"] != "") {
        echo "1 MB is the maximum file size allowed";
        return;
    }
    if (!checksize("attachment3") && $_FILES["attachment3"]["name"] != "") {
        echo "1 MB is the maximum file size allowed";
        return;
    }

    if (!is_valid_file_format("attachment1")) {
        echo "Invalid File Type for Attachment 1";
        return;
    }
    if (!is_valid_file_format("attachment2") && $_FILES["attachment2"]["name"] != "") {
        echo "Invalid File Type for Attachment 2";
        return;
    }
    if (!is_valid_file_format("attachment3") && $_FILES["attachment3"]["name"] != "") {
        echo "Invalid File Type for Attachment 3";
        return;
    }

    if ($_FILES["attachment1"]["error"] != 0) {
        echo "Error when uploading the file.";
        return;
    }

    // use 4 for missing file

    if (move_uploaded_file($_FILES['attachment1']['tmp_name'], $targetfile)) {
        $count++;
    } else {
        echo $_FILES['attachment1']['error'];
    }

    if (move_uploaded_file($_FILES['attachment2']['tmp_name'], $targetfile2)) {
        $count++;
    } else {
        echo $_FILES['attachment2']['error'];
    }

    if (move_uploaded_file($_FILES['attachment3']['tmp_name'], $targetfile3)) {
        $count++;
    } else {
        echo $_FILES['attachment3']['error'];
    }

    if (move_uploaded_file($_FILES['attachment4']['tmp_name'], $targetfile4)) {
        $count++;
    } else {
        echo $_FILES['attachment4']['error'];
    }

    echo $count . " File(s) uploaded";

    //CLEAN
    $targetfile1 = "";
    $targetfile2 = "";
    $targetfile3 = "";
    $targetfile4 = "";

    if (strlen($_FILES['attachment1']['name']) > 2) { // why greater than 2???
        $targetfile = "/" . $student_id . "/" . $url . "/" . $lab_name . "/" . rawurlencode($_FILES['attachment1']['name']);
    }

    if (strlen($_FILES['attachment2']['name']) > 2) {
        $targetfile2 = "/" . $student_id . "/" . $url . "/" . $lab_name . "/" . rawurlencode($_FILES['attachment2']['name']);
    }

    if (strlen($_FILES['attachment3']['name']) > 2) {
        $targetfile3 = "/" . $student_id . "/" . $url . "/" . $lab_name . "/" . rawurlencode($_FILES['attachment3']['name']);
    }

    if (strlen($_FILES['attachment4']['name']) > 2) {
        $targetfile4 = "/" . $student_id . "/" . $url . "/" . $lab_name . "/" . rawurlencode($_FILES['attachment4']['name']);
    }

    // When $group_id is not properly initialized, use integer 0 as its value.
    // This temporarily fixed the "Students unable to submit assignment after a recent change" bug at http://118.25.96.118/bugzilla/show_bug.cgi?id=65
    if (trim($group_id) === '') { // when $group_id is an empty string or contains only whitespace characters.
        $group_id = 0; // FIXME
    }

    $sql1 = "DELETE FROM lab_report_submissions where Lab_Report_ID='$lab_id' and Student_id='$student_id' and Course_Group_id='$group_id'";
    if ($con->query($sql1) === TRUE) {
    }

    $sql = "INSERT INTO `lab_report_submissions`(`Submission_Date`, `Lab_Report_ID`, `Student_id`,"
        . " `Course_Group_id`, `Attachment1`, `Notes`, `Attachment2`, `Attachment3`, `Attachment4`, `Status`, `Title`,`Remarking_Reason`)"
        . " VALUES ('$date','$lab_id','$student_id','$group_id','$targetfile','$instructions','$targetfile2','$targetfile3','$targetfile4',"
        . "'Pending','$title','')";

    if ($con->query($sql) === TRUE) {
        if ($_SESSION['Sub_Type'] == 'Individual') {
            $con->query($sql = "UPDATE `lab_report_submissions` SET `Course_Group_id` = '0' WHERE `lab_report_submissions`.`Lab_Report_ID` = '$lab_id'");
        }

        $_SESSION["info_courses"] = "Thanks.  You have successfully submitted your assignment.";
        header("Location: Course.php?url=" . $url);
    } else {
        echo "Error: <br>" . $con->error;
    }
}

// JOIN COURSE
if (!empty($_GET["JoinCourse"])) {

    $id = mysqli_real_escape_string($con, $_GET["id"]);
    $student_id = mysqli_real_escape_string($con, $_GET["std"]);
    $joining = mysqli_real_escape_string($con, $_GET["joining"]);
    $status = "Pending";

    if ($joining == 0) {
        $status = "Joined";
    }

    $sql = "INSERT INTO `course_students_table`(`Course_ID`, `Student_ID`,`Status`) VALUES ('$id','$student_id','$status')";

    if ($con->query($sql) === TRUE) {

        if ($joining == 0) {
            $_SESSION["info_Courses_student"] = "You enrolled in this course successfully.";
        } else {
            $_SESSION["info_Courses_student"] = "Course enrollment request was sent to the lecturer.";
        }

        header("Location: Courses.php");
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#MARK LAB REPORT

if (!empty($_GET["savemarks"])) {

    $id = mysqli_real_escape_string($con, $_GET["id"]);
    $marks = mysqli_real_escape_string($con, $_GET["marks"]);
    $total = mysqli_real_escape_string($con, $_GET["total"]);
    $feedback = mysqli_real_escape_string($con, $_GET["feedback"]);
    $header = mysqli_real_escape_string($con, $_GET["header"]);
    $labid = mysqli_real_escape_string($con, $_GET["labid"]);
    $status = "Marked";

    if ($marks > $total) {
        echo " Marks could not be greater than total";
        return;
    }
    $date =  date("Y-m-d H:i:s");
    $feedback = "<br>@$date : " . $feedback;

    $sql = "UPDATE `lab_report_submissions` SET `Marks`='$marks',`Status`='$status',"
        . ""
        . "Notes=if(Notes is null, ' ', concat(Notes, '$feedback'))"
        . ""
        . " WHERE Submission_ID=$id
              ";

    if ($con->query($sql) === TRUE) {

        $_SESSION["info_Marking"] = "Assignment marked";
        header("Location: Submissions.php?id=" . $labid . "&header=" . $header . "&total=" . $total);
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#Update Report Visibility  
if (!empty($_GET["updatevisibility"])) {

    $id = mysqli_real_escape_string($con, $_GET["id"]);
    $marks = mysqli_real_escape_string($con, $_GET["marks"]);
    $total = mysqli_real_escape_string($con, $_GET["total"]);
    $status = mysqli_real_escape_string($con, $_GET["status"]);
    $header = mysqli_real_escape_string($con, $_GET["header"]);
    $labid = mysqli_real_escape_string($con, $_GET["labid"]);

    $sql = "UPDATE `lab_report_submissions` SET `Visibility`='$status' WHERE Submission_ID='$id'
              ";

    if ($con->query($sql) === TRUE) {

        $_SESSION["info_Marking"] = "Lab Report Visibility Updated";
        header("Location: Submissions.php?id=" . $labid . "&header=" . $header . "&total=" . $total);
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#Remarking Request

if (!empty($_GET["remarking"])) {

    $id = htmlspecialchars(mysqli_real_escape_string($con, $_GET["id"]));
    $url = htmlspecialchars(mysqli_real_escape_string($con, $_GET["url"]));

    $status = htmlspecialchars(mysqli_real_escape_string($con, $_GET["status"]));
    $details = htmlspecialchars(mysqli_real_escape_string($con, $_GET["details"]));

    $sql = "UPDATE `lab_report_submissions` SET `Status`='Remarking',Remarking_Reason='$details' WHERE Submission_ID='$id'
              ";

    if ($con->query($sql) === TRUE) {

        $_SESSION["info_general"] = "Remarking Request Sent";
        header("Location: Course.php?url=" . $url . "&tab=Marked");
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#Create Group Request

if (!empty($_GET["creategroup"])) {

    $student_id = mysqli_real_escape_string($con, $_GET["student_id"]);
    $url = mysqli_real_escape_string($con, $_GET["url"]);
    $id = mysqli_real_escape_string($con, $_GET["id"]);
    $name = mysqli_real_escape_string($con, $_GET["name"]);

    $sql = "INSERT INTO `course_groups_table`(`Group_Name`, 
                  `Group_Leader`, `Course_id`) VALUES ('$name','$student_id','$id')";

    if ($con->query($sql) === TRUE) {

        $resultx1 = mysqli_query($con, "Select Max(Course_Group_id) as cnt from course_groups_table");
        while ($row = mysqli_fetch_assoc($resultx1)) {
            $gid = $row['cnt'];
        }

        $sql = "INSERT INTO `course_group_members_table`( `Course_Group_id`, `Student_ID`, `Status`) 
                          VALUES ('$gid','$student_id','Created')";
        if ($con->query($sql) === TRUE) {
            $_SESSION["info_general"] = "Course group Created";
            header("Location: Course.php?url=" . $url);
        } else {
            echo "Error: " . $sql . "<br>" . $con->error;
        }
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

//---------------------------------------Invite Group Request and add a new member into the database------------------------------------

if (!empty($_GET["groupinvite"])) {

    $student_id = mysqli_real_escape_string($con, $_GET["student_id"]);
    $url = mysqli_real_escape_string($con, $_GET["url"]);
    $courseid = mysqli_real_escape_string($con, $_GET["courseid"]);
    $groupid = mysqli_real_escape_string($con, $_GET["groupid"]);
    $student = mysqli_query($con, "SELECT * FROM students_data WHERE Student_ID = '$student_id'  ");

    if (mysqli_num_rows($student) > 0) {

        $result = mysqli_query($con, "SELECT * FROM course_group_members_table where Course_Group_id = '$groupid' and Student_ID = '$student_id'");
        if (mysqli_num_rows($result) > 0) {
            $_SESSION["info_general"] = $student_id . " has already been invited.";
            header("Location: Course.php?url=" . $url);
        } else {
            $sql = "INSERT INTO `course_group_members_table`( `Course_Group_id`, `Student_ID`, `Status`)
                        VALUES ('$groupid','$student_id','Invited')";
        }
    } else {
        $_SESSION["info_general"] = $student_id . " is an invalid student number.";
        header("Location: Course.php?url=" . $url);
    }

    if ($con->query($sql) === TRUE) {
        $resultx1 = mysqli_query($con, "SELECT * FROM course_groups_table where Course_Group_id ='$groupid'");

        while ($row = mysqli_fetch_assoc($resultx1)) {
            $Group_Member = $row['Group_Member'];
            $Group_Member4 = $row['Group_Member4'];
            $Group_Member2 = $row['Group_Member2'];
            $Group_Member3 = $row['Group_Member3'];
            $_SESSION['Group_Member4'] = $Group_Member4;
            $_SESSION['Group_Member3'] = $Group_Member3;
            $_SESSION['Group_Member2'] = $Group_Member2;
            $_SESSION['Group_Member'] = $Group_Member;

            if ($Group_Member == '0') {
                mysqli_query($con, "UPDATE `course_groups_table` SET `Group_Member` = ('" . $student_id . "') WHERE `course_groups_table`.`Course_Group_id` = '$groupid'");
                $_SESSION["info_general"] = $student_id . " was invited to the group.";
                header("Location: Course.php?url=" . $url);
            } elseif ($Group_Member2 == '0') {
                mysqli_query($con, "UPDATE `course_groups_table` SET `Group_Member2` = ('" . $student_id . "') WHERE `course_groups_table`.`Course_Group_id` = '$groupid'");
                $_SESSION["info_general"] = $student_id . " was invited to the group.";
                header("Location: Course.php?url=" . $url);
            } elseif ($Group_Member3 == '0') {
                mysqli_query($con, "UPDATE `course_groups_table` SET `Group_Member3` = ('" . $student_id . "') WHERE `course_groups_table`.`Course_Group_id` = '$groupid'");
                $_SESSION["info_general"] = $student_id . " was invited to the group.";
                header("Location: Course.php?url=" . $url);
            } elseif ($Group_Member4 == '0') {
                mysqli_query($con, "UPDATE `course_groups_table` SET `Group_Member4` = ('" . $student_id . "') WHERE `course_groups_table`.`Course_Group_id` = '$groupid'");
                $_SESSION["info_general"] = $student_id . " was invited to the group.";
                header("Location: Course.php?url=" . $url);
            } else {
                $_SESSION["info_general"] = " You cannot add any more members";
                header("Location: Course.php?url=" . $url);
            }
        }
        $_SESSION["info_general"] = $student_id . " was invited to the group.";
        header("Location: Course.php?url=" . $url);
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#Accept deny Group Invite

if (!empty($_GET["acceptinvite"])) {

    $student_id = mysqli_real_escape_string($con, $_GET["student_id"]);
    $url = mysqli_real_escape_string($con, $_GET["url"]);
    $action = mysqli_real_escape_string($con, $_GET["action"]);
    $groupid = mysqli_real_escape_string($con, $_GET["groupid"]);

    if ($action == 1) {
        $sql = "Update  `course_group_members_table` set Status='Joined' where  Course_Group_id ='$groupid' and student_id='$student_id' 
                         ";
    } else {
        $sql = "Delete from  `course_group_members_table`  where  Course_Group_id ='$groupid' and student_id='$student_id' 
                         ";
    }

    if ($con->query($sql) === TRUE) {
        $_SESSION["info_general"] = " Group invitation status updated";
        header("Location: Course.php?url=" . $url);
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#Remove a member from group

if (!empty($_GET["removemember"])) {

    $student_id = mysqli_real_escape_string($con, $_GET["student_id"]);
    $group_id = mysqli_real_escape_string($con, $_GET["group_id"]);
    $url = mysqli_real_escape_string($con, $_GET["url"]);

    $sql = "Delete from  `course_group_members_table`  where  student_id=$student_id and Course_Group_id=$group_id";

    if ($con->query($sql) === TRUE) {
        $_SESSION["info_general"] = " Member " . $student_id . " is gone.";
        header("Location: Course.php?url=" . $url);
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#Delete a whole group

if (!empty($_GET["deletegroup"])) {

    $group_id = mysqli_real_escape_string($con, $_GET["group_id"]);
    $url = mysqli_real_escape_string($con, $_GET["url"]);

    $sql1 = "Delete from  `course_group_members_table`  where  Course_Group_id=$group_id";
    $sql2 = "Delete from `course_groups_table` where Course_Group_id=$group_id";

    if ($con->query($sql1) === TRUE && $con->query($sql2) === TRUE) {
        $_SESSION["info_general"] = " Group has been deleted successfully. ";
        header("Location: Course.php?url=" . $url);
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#Extend Deadline

if (!empty($_GET["extenddeadline"])) {

    $id = mysqli_real_escape_string($con, $_GET["id"]);
    $date = mysqli_real_escape_string($con, $_GET["date"]);
    $time = mysqli_real_escape_string($con, $_GET["time"]);
    $type = mysqli_real_escape_string($con, $_GET["type"]);

    $studentid = mysqli_real_escape_string($con, $_GET["studentid"]);
    $reason = mysqli_real_escape_string($con, $_GET["reason"]);
    $url = mysqli_real_escape_string($con, $_GET["url"]);
    $deadline = $date . " " . $time;

    if ($type == 1) {
        $sql = "UPDATE `lab_reports_table` SET  `Deadline`='$deadline'  WHERE Lab_Report_ID='$id'";
    } else {
        $sql = "INSERT INTO `extended_deadlines_table`(`Student_ID`, "
            . "`Lab_Report_ID`, `Extended_Deadline_Date`,"
            . " `ReasonsForExtension`) VALUES ('$studentid','$id','$deadline','$reason')";
    }

    if ($con->query($sql) === TRUE) {

        $_SESSION["info_courses"] = " Assignment deadline extended successfully.";
        header("Location: Courses.php?course=" . $url);
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#IGNORE Remarking Request

if (!empty($_GET["ignoreremarking"])) {

    $id = mysqli_real_escape_string($con, $_GET["id"]);
    $total = mysqli_real_escape_string($con, $_GET["total"]);
    $header = mysqli_real_escape_string($con, $_GET["header"]);

    $subid = mysqli_real_escape_string($con, $_GET["subid"]);

    $sql = "UPDATE lab_report_submissions SET Status='Marked' WHERE Submission_ID='$subid'";

    if ($con->query($sql) === TRUE) {

        $_SESSION["info_Marking"] = "Remarking request ignored.";
        header("Location: Submissions.php?id=" . $id . "&header=" . $header . "&total=" . $total);
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

#Assign TA

if (!empty($_GET["assignTA"])) {
    $id = mysqli_real_escape_string($con, $_GET["id"]);
    $ta = mysqli_real_escape_string($con, $_GET["ta"]);

    // Check if the TA is already assigned to the course
    $check_sql = "SELECT * FROM course_ta WHERE Course_ID='$id' AND TA='$ta'";
    $check_result = $con->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Alert user about the duplicate assignment
        echo "<script>
            alert('The selected TA is already assigned to this course.');
            window.location.href='Admin.php';
        </script>";
    } else {
        // Proceed with the TA assignment
        $sql = "INSERT INTO course_ta(Course_ID, TA) VALUES ('$id','$ta')";

        if ($con->query($sql) === TRUE) {
            $_SESSION["info_Admin_Courses"] = $type . " Course TA Assigned ";
            header("Location: Admin.php");
        } else {
            echo "<script>
                alert('You must select a TA first!.');
                window.location.href='Admin.php';
            </script>";
        }
    }
}

//ACCEPT STUDNTS JOINING COURSSS

if (!empty($_GET["AcceptStudent"])) {

    $id = mysqli_real_escape_string($con, $_GET["id"]);
    $rs = mysqli_real_escape_string($con, $_GET["rs"]);

    if ($rs == "yes") {
        $sql = "Update  course_students_table set Status='Joined' Where ID='$id'";
    } else {
        $sql = "Delete FROM  course_students_table Where ID='$id'";
    }

    if ($con->query($sql) === TRUE) {

        if ($rs == "yes") {
            $_SESSION["info_courses"] = "Course Joining request Approved.";
        } else {
            $_SESSION["info_courses"] = "Course Joining request Declined & Removed.";
        }

        header("Location: Courses.php");
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

//action=passchange&uid=1&pass=1929

if (!empty($_GET["action"])) {

    $action = $_GET["action"];
    $uid = mysqli_real_escape_string($con, $_GET["uid"]);

    $pass = mysqli_real_escape_string($con, $_GET["pass"]);
    $pass = password_hash($pass, PASSWORD_DEFAULT);

    $status = mysqli_real_escape_string($con, $_GET["status"]);

    // validate uid
    if (intval($uid) < 0) {
        header("Location: index.php");
        return;
    }

    if ($action == "passchange"  && $_SESSION['user_id'] == $uid) {
        $sql = "UPDATE users_table set Password='$pass' where User_ID='$uid';";
        if ($con->query($sql) === TRUE) {
            error_reporting(0);
            echo "Password has been changed";
            //return;
            $_SESSION["infoChangePassword"] = $type . " User password was changed successfully.";
            header("Location: index.php");
        } else {
            // echo "Error: " . $sql . "<br>" . $con->error;
            echo "Something really bad happened while changing password.  Contact lanhui at zjnu.edu.cn.  Thanks!";
        }
    }

    if ($action == "statuschange" && ($_SESSION['user_type'] == "Lecturer" || $_SESSION['user_type'] == "Admin")) {
        $sql = "UPDATE users_table set Status='$status' where User_ID='$uid';";
        if ($con->query($sql) === TRUE) {
            $_SESSION["info_Admin_Users"] = $type . " user  Status updated successfully ";
            header("Location: Admin.php");
        } else {
            echo "Something really bad happened while changing status.  Contact lanhui at zjnu.edu.cn.  Thanks!";
        }
    }
}

// ############################### CREATE STUDENT USER ##################################
if (!empty($_POST["form_createCourse"])) {
    $name = mysqli_real_escape_string($con, $_POST["name"]);
    $academic = mysqli_real_escape_string($con, $_POST["academic"]);
    $lecturer = mysqli_real_escape_string($con, $_POST["lecturer"]);
    $ta = mysqli_real_escape_string($con, $_POST["ta"]);
    $faculty = mysqli_real_escape_string($con, $_POST["faculty"]);
    $code = mysqli_real_escape_string($con, $_POST["code"]);
    $url = mysqli_real_escape_string($con, $_POST["url"]);
    $verify = mysqli_real_escape_string($con, $_POST["verify"]);
    $who = mysqli_real_escape_string($con, $_POST["l"]);

    if ($url == "") {
        $url = $code . $academic;
    }

    if ($ta == "") {
        $ta = 0;
    }

    // check if email is taked
    //     $result = mysqli_query($con,
    //        "SELECT * FROM courses_table WHERE Course_Name='$name'");
    //   if(mysqli_num_rows($result)!=0)
    //    {
    //        $_SESSION["info_Admin_Courses"]="Course Name : ".$name." already used.";
    //        header("Location: Admin.php");        
    //    }
    //    

    $sql = "INSERT INTO `courses_table`(`Course_Name`, `Academic_Year`, `Faculty`, `Lecturer_User_ID`, `TA_User_ID`, `Course_Code`, `URL`, `Verify_New_Members`) 
            VALUES ('$name','$academic','$faculty','$lecturer','$ta','$code','$url','$verify')";

    if ($con->query($sql) === TRUE) {
        $_SESSION["info_Admin_Courses"] = "Course portal was Created successfully.";
        if ($who == "l") {
            header("Location: Courses.php");
        } else {
            header("Location: Admin.php");
        }
    } else {
        echo "Error: " . $sql . "<br>" . $con->error;
    }
}

// Export grade

if (!empty($_GET["exportgrade"])) {

    $lab = mysqli_real_escape_string($con, $_GET["lab"]);
    $lab_name = mysqli_real_escape_string($con, $_GET["lab_name"]);

    error_reporting(0);

    $select = "SELECT lab_reports_table.Title as 'LAB_Report', lab_reports_table.Marks as Lab_Marks,
 `Submission_Date`, lab_report_submissions.Student_id, users_table.Full_Name as Student_Name,  lab_report_submissions.Marks,`Notes`
FROM `lab_report_submissions`

INNER JOIN lab_reports_table on lab_reports_table.Lab_Report_ID=lab_report_submissions.Lab_Report_ID

INNER JOIN users_table on users_table.Student_ID=lab_report_submissions.Student_id

WHERE lab_report_submissions.Lab_Report_ID='$lab'";

    $export  = mysqli_query($con, $select);

    $fields = mysqli_num_fields($export);

    for ($i = 0; $i < $fields; $i++) {
        $header .= mysqli_fetch_field_direct($export, $i)->name . "\t";
    }

    while ($row = mysqli_fetch_row($export)) {
        $line = '';
        foreach ($row as $value) {
            if ((!isset($value)) || ($value == "")) {
                $value = "\t";
            } else {
                $value = str_replace('"', '""', $value);
                $value = '"' . $value . '"' . "\t";
            }
            $line .= $value;
        }
        $data .= trim($line) . "\n";
    }
    $data = str_replace("\r", "", $data);

    if ($data == "") {
        $data = "\n(0) Records Found!\n";
    }

    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=$lab_name Grade Sheet.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
}
?>
