<?php
include 'NoDirectPhpAcess.php';
?>

<?php
include 'Header.php';
?>

<br><br><br>

<div class="container">

    <div class="row">

	<div class="col-md-5"></div>

	<div class="col-md-5">

	    <form method="post" action="Script.php" id="signup_form">
		<legend>Sign up</legend>

		<input type="hidden" name="form_signup" value="true" />

		Full Name
		<input type="text" name="fullname" placeholder="Your full name" class="form-control" value="<?php echo isset($_SESSION['user_fullname_temp']) ? $_SESSION['user_fullname_temp'] : ''; ?>" required="required" id="full_name"/> <br>
		
		Student ID
		<input type="text" name="user_student_id" placeholder="Entre your student ID" class="form-control" value="<?php  echo isset($_SESSION['user_student_id_1']) ? $_SESSION['user_student_id_temp'] : ''; ?>" required="required" id="student_id"> <br>

		Email
		<input type="text" name="email" placeholder="Email" class="form-control" value="<?php echo $_SESSION['user_email']; ?>" required="required" id="email" /> <br>

		Password <label class="form-text">must include uppercase and lowercase letters, digits and special characters</label>
		<input type="password" class="form-control" name="password" placeholder="Enter password" required="required" id="password1" /> <br>

		Confirm Password
		<input type="password" class="form-control" name="confirmpassword" placeholder="Confirm password" required="required" id="password2" /> <br>
		<br>
		<button type="submit" class="btn btn-primary" id="signup_btn">Sign up</button>

		<?php
		error_reporting(E_ALL);
		if (isset($_SESSION['info_signup'])) {
                    echo  '<hr><div class="alert alert-danger" role="alert">' . $_SESSION['info_signup'] . '</div>';
                    $_SESSION['info_signup'] = null;
		}
		?>

            </form>
	</div>
    </div>
</div>

