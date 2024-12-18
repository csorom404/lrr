<?php
include 'NoDirectPhpAcess.php';
?>

<?php
$page = "admin";
include 'Header.php';
?>


<?php
 //Only Lecturer or Admin could access this page
if ($_SESSION['user_type'] != "Lecturer" && $_SESSION['user_type'] != "Admin") {
    die("Sorry.  Nothing to see here.");
}
?>

<div class="container">


    <br>
    <h1 class="display-6"> Administration panel </h1>

    <hr>
    <div class="row">

	<div class="col-md-6">

           <!-- Nav tabs -->
	    <ul class="nav nav-tabs" id="myTab">

		<li class="nav-item">
		    <a class="nav-link active" href="#tab-student-accounts" id="batch_tab">Create student accounts</a>
		</li>

		<li class="nav-item">
		    <a class="nav-link" href="#tab-ins-accounts" id="tab_ins_accounts">Create instructor account</a>
		</li>

		<li class="nav-item">
		    <a class="nav-link" href="#tab-existing-accounts" id="existing_accounts_tab">Existing accounts</a>
		</li>
	    </ul>

            <!-- Tab panes -->
	    <div class="tab-content">

		<!-- code contributed by Xu Xiaopeng (https://github.com/xxp1999) -->
		<div id="tab-student-accounts" class="tab-pane active" style="margin-top:5px">
		    <p class="text-muted">Copy & paste student number to the following box, and separate two student numbers with a space.</p>
		    <form action="batch_insert.php" method="post" id="batch_form">
			<textarea cols="60" rows="16" name="users" required=""></textarea>
			<button type="submit" class="btn btn-primary" id="register_btn">Register students</button>
		    </form>
		</div>

		<div id="tab-ins-accounts" class="tab-pane"><br>

		    <?php
		    if ($_SESSION['user_type'] == "Lecturer") {
			echo "<p class=\"text-muted\">Create TA Accounts</p>";
		    }
		    else if($_SESSION['user_type'] == "Admin"){
			echo "<p class=\"text-muted\">Create Lecturer Accounts</p>";
		    }

		    ?>
		    <form method="post" action="Script.php"  id="create_account_form">
			<input type="hidden" name="form_createlecturrer" value="true" required="" />
			Full name
			<input type="text" name="fullname" placeholder="Full Name" class="form-control" required=""> <br>
			Email
			<input type="text" name="email" placeholder="Email / Student Number" class="form-control" > <br>
                        Initial password (Enter a strong password or leave it empty to let LRR generate one)
                        <input type="password" class="form-control" name="password"  minlength="8" placeholder="Initial password" > <br>
			User type:
                <?php

                if ($_SESSION['user_type'] == "Lecturer") {
                    echo ' <input type="radio" name="type" value="TA" required="" id="role_TA"> TA (Teaching Assistant) ';
                } else if ($_SESSION['user_type'] == "Admin"){
                    echo " <input type='radio' name='type' value='Lecturer' required='' id='role_lecturer'> Lecturer ";
                }

                ?>

			<br><br>
                <button type="submit" class="btn btn-primary" name="create_btn">Create</button>

			<?php
			error_reporting(E_ALL);
			if (isset($_SESSION['info_Admin_Users'])) {
			    echo  '<hr><div class="alert alert-warning" role="alert">' . $_SESSION['info_Admin_Users'] . '</div>';
			    $_SESSION['info_Admin_Users'] = null;
			}
			if (isset($_SESSION['info_Admin_Users'])) {
			    echo  '<hr><div class="alert alert-warning" role="alert">' . $_SESSION['info_Admin_Users'] . '</div>';
			    $_SESSION['info_Admin_Users'] = null;
			}
			?>

		    </form>

		</div>


		<div id="tab-existing-accounts" class="tab-pane"><br>

		    <table class="table" style="font-size: 10pt;">
			<tr style="font-size:10pt;">
			    <th>ID</th>
			    <th>Name</th>
			    <th>Email</th>
			    <th>Reset password </th>
			    <th>Block/Activate </th>
			</tr>
			<?php

			if ($_SESSION['user_type'] == "Lecturer") {

			    $result = mysqli_query(
				$con,
				"SELECT * FROM users_table WHERE UserType in ('TA')"
			    );
			}

			else if ($_SESSION['user_type'] == "Admin"){
			    $result = mysqli_query(
				$con,
				"SELECT * FROM users_table WHERE UserType in ('Lecturer')"
			    );
			}

			while ($row = mysqli_fetch_assoc($result)) {
			    $pass =  $row['Password'];
			    $btn = "<button class='btn btn-warning' onclick=\"updatePassword(" . $row['User_ID'] . ",'$pass')\">Reset</button>";
			    if ($row['Status'] == "Active") {
				$newstatus = "Blocked";
				$btnBlock = "<button class='btn btn-danger' onclick=\"blockUser(" . $row['User_ID'] . ",'$newstatus')\" id=\"block_account_1\">Block</button>";
			    } else {
				$newstatus = "Active";
				$btnBlock = "<button class='btn btn-success' onclick=\"blockUser(" . $row['User_ID'] . ",'$newstatus')\" id=\"activate_account_1\">Activate</button>";
			    }

			    echo "<tr><td>" . $row['User_ID'] . "</td><td>" . $row['Full_Name'] . "</td><td>" . $row['Email'] . "</td><td>$btn</td><td>$btnBlock</td></tr>";
			}
			?>
		    </table>
		</div>

	    </div>

	</div>

	<div class="col-md-6">

	    <ul class="nav nav-tabs" id="myTab">
		<li class="nav-item">
		    <a class="nav-link active" href="#tab-existing-courses" id="existing_courses">Existing courses</a>
		</li>
	    </ul>

            <div id="tab-existing-courses" class="tab-pane active"><br>

		<p class="text-muted"> Past courses </p>

		<table class="table" style="font-size: 10pt;">
		    <tr>
			<th>Course name</th>
			<th>Faculty</th>
			<th>Lecturer</th>
			<th>TAs</th>
			<th>Assign a new TA </th>
		    </tr>

		    <?php
		    $user_id = $_SESSION['user_id'];
		    if ($_SESSION['user_type'] == 'Lecturer') {
			$result = mysqli_query($con, "SELECT `Course_ID`, `Course_Name`, `Academic_Year`, `Faculty`, `TA_User_ID`, `Course_Code`, `Full_Name` FROM courses_table INNER JOIN users_table ON users_table.User_ID=courses_table.Lecturer_User_ID WHERE User_ID=$user_id ORDER BY Academic_Year DESC;");
		    } else if ($_SESSION['user_type'] == 'Admin') {
			$result = mysqli_query($con, "SELECT `Course_ID`, `Course_Name`, `Academic_Year`, `Faculty`, `TA_User_ID`, `Course_Code`, `Full_Name` FROM courses_table INNER JOIN users_table ON users_table.User_ID=courses_table.Lecturer_User_ID ORDER BY Academic_Year DESC;");
		    }
		    if (mysqli_num_rows($result) != 0) {
			$counter = 0;
			while ($row = mysqli_fetch_assoc($result)) {
			    $name = $row['Course_Name'];
			    $code = $row['Course_Code'];
			    $faculty = $row['Faculty'];
			    $lecturer = $row['Full_Name'];
			    $academic = $row['Academic_Year'];
			    $c_id = $row['Course_ID'];
			    $counter += 1;

			    $resultTA = mysqli_query($con, "SELECT `Course_ID`, `TA`, users_table.Full_Name as TA_NAME FROM course_ta INNER JOIN users_table on users_table.User_ID=course_ta.TA where course_ta.Course_ID=$c_id");

			    $ta = "";
			    while ($rowTA = mysqli_fetch_assoc($resultTA)) {
				$ta = $ta . "  " . $rowTA['TA_NAME'];
			    }

			    echo "
                          <tr> <td>$code - $name</td>  <td>$faculty </td> <td>$lecturer</td><td>$ta</td>  <td><form method='get' action='Script.php' id='drop_menu_form_$counter'> <select name='ta' class=''>";

			    $resultx = mysqli_query($con, "SELECT * FROM users_table WHERE UserType='TA'");
			    if (mysqli_num_rows($resultx) == 0) {
			    } else {
				while ($row = mysqli_fetch_assoc($resultx)) {
				    $id = $row['User_ID'];
				    $name = $row['Full_Name'];
				    echo "<option value='$id'> $name </option>";
				}
			    }

			    echo "</select>  <input type='hidden' name='assignTA' value='true'> <input type='hidden' name='id' value='$c_id'>  <button class='btn btn-outline-secondary btn-sm' type='submit' id='assign_btn_$counter'>assign</button></form> </td></tr>
                            ";
			}
		    }
		    ?>

		</table>

            </div>

	</div>

    </div>
    
</div>

<?php include 'Footer.php';?>

<script>
 function updatePassword(id, pass) {
     if (!confirm('Are you sure to reset user password?')) {
         return;
     }

     window.location.href = "\Script.php\?action=passchange&uid=" + id + "&pass=" + pass;
 }

 function blockUser(id, status) {
     if (!confirm('Are you sure to change user status?')) {
         return;
     }
     window.location.href = "\Script.php\?action=statuschange&uid=" + id + "&status=" + status;
 }

 /* For tabs to work */
 const triggerTabList = document.querySelectorAll('#myTab a')
 triggerTabList.forEach(triggerEl => {
     const tabTrigger = new bootstrap.Tab(triggerEl)
     triggerEl.addEventListener('click', event => {
	 event.preventDefault()
	 tabTrigger.show()
     })
 })

</script>


</body>
</html>

