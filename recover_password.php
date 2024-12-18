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
	    <form method="post" action="Script.php">
		<legend>Recover password</legend>
		<input type="hidden" name="form_recover_password" value="true"/>
		Student number
		<input type="text" name="sno" placeholder="Enter your student number" class="form-control" required="required" value="<?php echo htmlspecialchars($_SESSION['student_number']); ?>"> <br/>
		Email
		<input type="text" name="email" placeholder="Enter your email address" class="form-control" required="required" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>"> <br/>
		<button type="submit" class="btn btn-primary">Recover</button>
	    </form>
	</div>
    </div>

    <?php
    if(isset($_SESSION['info_recover_password'])) {
	echo  '<hr><div class="alert alert-danger" role="alert">'.htmlspecialchars($_SESSION['info_recover_password']).'</div>';
	$_SESSION['info_recover_password'] = null;
    }
    ?>

</div>

