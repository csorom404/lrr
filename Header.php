<?php
session_start();
error_reporting(0);
date_default_timezone_set('Asia/Shanghai');

// include "get_mysql_credentials.php";
$con = mysqli_connect("localhost", "root", "", "lrr");

// Check database connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>

<!DOCTYPE html>

<html lang="en-US">

    <head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>LRR</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>

	<link href="./font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
	<!-- *this css file can be used across all the websites
	     and any new css class can be added there.
	     * The reason is to make the css code reusable.
	     * the css file is used by submissions.php
	-->
	<link href = "./css/main.css" rel="stylesheet" type="text/css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script src="./css/jquery.datetimepicker.min.js" type="text/javascript"></script>

	<style>

	 .btn-default {
	     border: 2px solid #f8f8f8;
	     width: 100%;
	     text-align: left;
	     margin: 4px auto;
	 }

         .btn-default:hover {
             background: #f8f8f8;
	 }

	 h1,
	 h2,
	 h3,
	 h4 {
	     color: #03407B;
	 }

	 a {
	     color: #03407B;
	 }

	 .break-word {
	     word-wrap: break-word;
	     white-space: -moz-pre-wrap !important;
	     /* Mozilla, since 1999 */
	     white-space: -pre-wrap;
	     /* Opera 4-6 */
	     white-space: -o-pre-wrap;
	     /* Opera 7 */
	     white-space: pre-wrap;
	     /* css-3 */
	     word-wrap: break-word;
	     /* Internet Explorer 5.5+ */
	     white-space: -webkit-pre-wrap;
	     /* Newer versions of Chrome/Safari*/
	     word-break: break-all;
	     white-space: normal;
	 }

	 .ui-widget-content.ui-dialog {
	     border: 2px solid #03488B;

	 }

	 .ui-dialog>.ui-widget-header {
	     background: #03488B;
	     color: white
	 }

	 .ui-button {
	     background: #03488B;
	     color: white
	 }


    .ui-dialog-titlebar-close::before {
        content: "X";
        position: absolute;
        top: 1px;
        left: 3px;
        line-height: 1rem;
    }

	 #footer{
	     position:fixed;
	     bottom:0;
	     left:0;
	     text-align:center;
	     width:100%;
	 }

	 .form-control{
	     padding-top: 1px;
	     padding-bottom:1px;
	 }


	</style>

    </head>

    <body>

	<nav class="navbar navbar-expand-lg bg-body-tertiary" style="padding-left:180px;padding-right:150px;margin:auto;">
	    <div class="container-fluid">

		<a class="navbar-brand" href="~\..\index.php"> <img src="logo.png" style="width:30px;height:30px;" alt="LRR Logo"> LRR </a>

		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
		    <span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarNavAltMarkup">
		    <div class="navbar-nav">

			<a class="nav-link" href="#">
			    <?php
			    if (isset($_SESSION["user_fullname"])) {
                    echo "<b>" . $_SESSION['user_fullname'] . "</b>";
			    }
			    ?>

			    &nbsp;

			    <?php
			    $c_date =  date("Y-m-d H:i");
			    if (isset($_SESSION['user_student_id']))
                    echo "(" . $_SESSION['user_type'] . " ID: " . $_SESSION['user_student_id'] . ")   ";
			    elseif (isset($_SESSION['user_type']))
                    echo "(" . $_SESSION['user_type'] . ")   ";
			    ?>
			</a>

			<?php
			if (isset($_SESSION["user_fullname"])) {
                if ($_SESSION['user_type'] == "Student" || $_SESSION['user_type'] == 'Lecturer') {
                    echo "<a class='nav-link' href='~\..\Courses.php'><i class='fa fa-book'></i> My courses </a>";
                }
			?>


			<?php
			    if ($_SESSION['user_type'] == "Lecturer" || $_SESSION['user_type'] == 'Admin') { // Show Admin link
			        echo "&nbsp;&nbsp;&nbsp;&nbsp;<a class='nav-link' href=\"~\..\Admin.php\" id=\"admin_tab\"><i class='fa fa-cog'></i>Admin</a>";
			    }
			?>

			    &nbsp;&nbsp;&nbsp;&nbsp;
			    <a class="nav-link" href="#" onclick="updatePass(<?php echo $_SESSION['user_id']; ?>)"><i class="fa fa-user"> </i> Update password</a>

			    &nbsp;&nbsp;&nbsp;&nbsp;
			    <a class="nav-link" href="~\..\logout.php"><i class="fa fa-lock"> </i> Logout</a>

			<?php
			}  // Closing this conditional test block: if (isset($_SESSION["user_fullname"])) { ...
			?>

		    </div>
		</div>
	    </div>
	</nav>


	<script>
	 function updatePass(id) {

	     const pass = prompt("Enter your new password : ", "Enter a strong password");

	     if (!confirm('Are you sure you want to reset your password?')) {
		 return;
	     }

	     window.location.href = "\Script.php\?action=passchange&uid=" + id + "&pass=" + pass;
	 }

	</script>
