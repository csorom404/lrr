<?php
$page='Home';
require 'Header.php';
session_start();
?>

<?php
// if the user has already logged in, then clicking the LRRS icon should not display the login page (i.e., index.php).
if (isset($_SESSION["user_fullname"])) {
    echo  '<div class="container alert alert-info"> You\'ve already logged in.</div>';
    exit();
}
?>

<br><br><br>

<div class="container">

    <div class="row">
	<div class="col-md-5">
	    <img src="logo.png" style="width:32%; position:relative; right:-95px; top:1px;" alt="LRR Logo">
	    <br><br>
	    <div style="width:32%; position:relative; right:-90px; font-family:Poppins-Regular;">
		<h1>Lab Report Repository</h1>
	    </div>
	</div>

	<div class="col-md-5">
	    <form method="post" action="Script.php" name="form_login">
		<legend>Sign in</legend>
		<input type="hidden" name="form_login" value="true"/>
		<label for="user_name" class="form-label">Account name</label>
		<input type="text" name="user" placeholder="Student Number / Email address" class="form-control" required="required" id="user_name" value="<?php echo isset($_SESSION['failed_login_user']) ? htmlspecialchars($_SESSION['failed_login_user']) : ''; ?>" />
		<br>
		<label for="user_password" class="form-label">Password</label>
		<input type="password" class="form-control"  name="password" placeholder="password" required="required" id="user_password" />
		<br>
		<button type="submit" class="btn btn-primary" id="login_btn">Sign in</button>

		<br>
		<label class="form-text">Don't have an account yet?</label> <a href="signup.php" id="signup_link">Sign up</a>

		<br>
		<label class="form-text">Forget your password?</label> <a href="recover_password.php">Recover</a>


		<?php

		error_reporting(E_ALL);

		if(isset($_SESSION['info_login'])) {
		    echo  '<hr><div class="alert alert-danger" role="alert">'.$_SESSION['info_login'].'</div>';
		    $_SESSION['info_login'] = null;
		}


		// wrong password
		if(isset($_SESSION['wrong_pass'])) {
		    echo  '<hr><div class="alert alert-danger" role="alert">'.$_SESSION['wrong_pass'].'</div>';
		    $_SESSION['wrong_pass'] = null;
		}


		if(isset($_SESSION['infoChangePassword'])) {
		    echo  '<hr><div class="alert alert-danger" role="alert">'.$_SESSION['infoChangePassword'].'</div>';
		    $_SESSION['infoChangePassword'] = null;
		}
		?>
	    </form>
	</div>
    </div>
</div>

<div id="footer">
    LRR was originally developed in 2018 as a <a href="http://lanlab.org/course/2018f/se/homepage.html">software engineering course project</a> by Mohamed Nor and Elmahdi Houzi.  Please submit your bug reports to Mr Lan. <a href="./homepage">More information ...</a>
</div>

</body>
</html>
