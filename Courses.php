<?php
include 'NoDirectPhpAcess.php';
?>


<?php
$page='Courses';
include 'Header.php';
?>

<div class="container">

    <?php
    $user_id = $_SESSION['user_id'];
    if( $_SESSION['user_type']=="Lecturer" || $_SESSION['user_type']=="TA")
    {
    ?>

	<!--    FOR LECTURER-->


	<div class="row">

	    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" integrity="sha512-ELV+xyi8IhEApPS/pSj66+Jiw+sOT1Mqkzlh8ExXihe4zfqbWkxPRi8wptXIO9g73FSlhmquFlUOuMSoXz5IRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>	    
	    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js" integrity="sha512-57oZ/vW8ANMjR/KQ6Be9v/+/h6bq9/l3f0Oc7vn6qMqyhvPd1cvKBRWWpzu0QoneImqr2SkmO4MSqU+RpHom3Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <script>
            function extendDeadline(id) {
                const dropstudents = $("#dropstudents").html();
                try {
                    $(`<form id="frm" method="get" action="Script.php">
                                <input type="hidden" name="extenddeadline" value="true" >
                                <input type="hidden" name="id" value="${id}" >
                                New date and time<br>
                                <input type="date" name="date" required="">
                                <input type="time" name="time" required="">
                                <br>
                                <input type="radio" value="1" name="type" required=""> Extend for all
                                <br>
                                <input type="radio" value="2" name="type" required=""> Extend for one
                                <br>
                                ${dropstudents}
                           </form>`).dialog({
                                   modal: true,
                                    title:'Extend deadline',
                                    close: function () {
                                        var closeBtn = $('.ui-dialog-titlebar-close');
                                        closeBtn.html('');
                                    },
                                    buttons: {
                                       'Submit': function () {
                                           $('#frm').submit();
                                           $(this).dialog('close');
                                       },
                                    'Cancel': function () {
                                        $(this).dialog('close');
                                    }
                                    }
                               });
                } catch(e) {
                    alert(e);
                }
            }
        </script>

	    <?php
	    if (!empty($_GET["course"])) {
            $course_url = mysqli_real_escape_string($con, $_GET["course"]);
            $result = mysqli_query($con,"SELECT Course_ID, Course_Name, Academic_Year, Faculty, Lecturer_User_ID, TA_User_ID, Course_Code, URL, Verify_New_Members, users_table.Full_Name
                                         FROM courses_table
                                         INNER JOIN users_table
                                         ON users_table.User_ID=courses_table.Lecturer_User_ID
                                         WHERE URL='$course_url' ");

		if(mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                    $name = $row['Course_Name'];
                    $code = $row['Course_Code'];
                    $faculty = $row['Faculty'];
                    $lecturer = $row['Full_Name'];
                    $academic = $row['Academic_Year'];
                    $url = $row['URL'];
                    $id = $row['Course_ID'];
                    $course_id = $row['Course_ID'];
                    echo "<p class='mt-md-1 mb-md-1'> <strong>($code) - $name</strong> </p>
                          <br>
                          <small> Faculty: $faculty &nbsp;&nbsp; Year: $academic &nbsp;&nbsp; Lecturer: $lecturer </small>
                          <hr>
                          <div class='row'>";
                    echo "<div class='col-md-5'>";
            }

		    // ------------------------------Editing Lab Assignment by Lecturer ------------------------------------


		    if ($_GET['act'] == "edit") {
                $getid = mysqli_real_escape_string($con, $_GET["cid"]);
                $result1 = mysqli_query($con, "SELECT * from lab_reports_table WHERE Lab_Report_ID = '$getid'");
                while($row1 = mysqli_fetch_assoc($result1)) {
                    $Deadline = $row1['Deadline'];
                    $_SESSION['Date'] = trim( strstr($Deadline, ' ', true) );
                    $_SESSION['Time'] = trim( strstr($Deadline, ' ') );
                    $_SESSION['Instructions'] = $row1['Instructions'];
                    $_SESSION['Title'] = $row1['Title'];
                    $_SESSION['Marks'] = $row1['Marks'];
                    $_SESSION['Type'] = $row1['Type'];
                }

                if (isset($_POST['form_uploadlab'])) {
                    $deadlinedate = trim(mysqli_real_escape_string($con, $_POST["deadlinedate"])); // remove spaces
                    $deadlinetime = trim(mysqli_real_escape_string($con, $_POST["deadlinetime"])); // remove spaces
                    $instructions = mysqli_real_escape_string($con, $_POST["instructions"]);
                    $title = mysqli_real_escape_string($con, $_POST["title"]);
                    $marks = mysqli_real_escape_string($con, $_POST["marks"]);
                    $type  = mysqli_real_escape_string($con, $_POST["type"]);
                    $Deadline = $deadlinedate." ".$deadlinetime;
                    $date =  date("Y-m-d H:i");

                    $sql = "UPDATE `lab_reports_table` SET `Deadline` = ('" . $Deadline . "'), `Instructions` = ('" . $instructions . "'), `Title` = ('" . $title . "'), `Marks` = ('" . $marks . "'), `Type` = ('" . $type . "') WHERE `lab_reports_table`.`Lab_Report_ID` = '$getid'";
                    if ($con->query($sql) === TRUE) {
                        $_SESSION["info_Updated"]="Assignment information updated successfully.";
                    } else {
                        echo "Serious error happened while updating assignment information.";
                    }
                }

                if (isset($_POST['form_deletelab'])) {
                    $sql = "DELETE FROM lab_reports_table WHERE Lab_Report_ID='$getid'";
                    if ($con->query($sql) === TRUE) {
                        $_SESSION["info_Updated"]="Assignment deleted successfully.";
                    } else {
                        echo "Serious error happened while deleting the assignment.";
                    }
                }


                if ($_SESSION['user_type'] == "Lecturer") {
                    $Date = $_SESSION['Date'];
                    $Time = $_SESSION['Time'];
                    $Instructions = $_SESSION['Instructions'];
                    $Title = $_SESSION['Title'];
                    $Marks = $_SESSION['Marks'];
                    $Type = $_SESSION['Type'];
                    echo "<h3><a href='Courses.php?course=".$url."'>Edit assignment information</a></h3>";
            ?>
                <form method='post' enctype='multipart/form-data' action=''>
                    <input type='hidden' name='form_uploadlab' value='true' required=''/>
                    <input type='hidden' name='course_id' value='<?php echo "$id" ?>' required=''/>
                    <input type='hidden' name='url' value='<?php echo ".$course_url." ?>' required=''/>

                    Deadline Date/Time
                    <div class='row'>
                        <div class='col-md-7'><input type='date' id='date' name='deadlinedate' placeholder='' class='form-control' required='' value="<?php echo isset($_GET['act']) && $_GET['act']=="edit" ? $Date : ""; ?>"> </div>
                        <div class='col-md-5'> <input type='text' id='time' class='form-control' name='deadlinetime' value="<?php echo isset($_GET['act']) && $_GET['act']=="edit" ? $Time : ""; ?>"> </div>
                    </div>

                    Title
                    <input type='text'  name='title' placeholder='Title' class='form-control' required='' value="<?php echo isset($_GET['act']) && $_GET['act']=="edit" ? $Title : ""; ?>">
                    Instructions
                    <textarea  name='instructions' placeholder='Assignment Instructions' class='form-control' required='' ><?php echo isset($_GET['act']) && $_GET['act']=='edit' ? $Instructions : ''; ?></textarea>
                    Marks
                    <input type='text'  name='marks' placeholder='Marks' class='form-control' required='' value="<?php echo isset($_GET['act']) && $_GET['act']=="edit" ? $Marks : ""; ?>">
                    Attachment 1
                    <input type='file'  name='attachment1' placeholder='Attachment 1' class='form-control'>
                    Attachment 2
                    <input type='file' name='attachment2' placeholder='Attachment 1' class='form-control'>
                    Attachment 3
                    <input type='file'  name='attachment3' placeholder='Attachment 1' class='form-control' >
                    Attachment 4
                    <input type='file'  name='attachment4' placeholder='Attachment 4' class='form-control' >
                    <br>
		    <?php
                    if ($Type == "Individual") {
                        echo "Submission Type  <input type='radio' name='type' value='Individual' checked /> Individual  <input type='radio' name='type' value='Group' /> Group";
                    } else {
                        echo "Submission Type  <input type='radio' name='type' value='Individual' /> Individual  <input type='radio' name='type' value='Group' checked> Group";
                    }
                    ?>
		    <br>
                    <input type='submit' class='btn btn-primary' value='Update assignment'><br>
                </form><br><br><br><br>

                <form method='post' action=''>
                    <input type='hidden' name='form_deletelab' value='true' required=''/>
                    <input type='submit' class='btn btn-danger' value='Delete assignment'><br>
                </form>
	    <?php
                }
            } else {

		// ------------------------------Posting New Lab Assignment------------------------------------

		// Mysql to split 1 string into 2 similar to the tsrstr in php
		// SELECT SUBSTRING_INDEX(Deadline, ' ', 1) as Date, SUBSTRING_INDEX(Deadline, ' ', -1) as Time from lab_reports_table

		if ($_SESSION['user_type'] == "Lecturer") {
            ?>

		<h3>New assignment</h3>

                <form method='post' enctype='multipart/form-data' action='Script.php'>
		        <?php
                $_SESSION['url'] = $url;
                ?>
                    <input type='hidden' name='form_uploadlab' value='true' required=''/>
                    <input type='hidden' name='course_id' value='<?php echo "$id" ?>' required=''/>
                    <input type='hidden' name='url' value='<?php echo ".$course_url." ?>' required=''/>

                    Deadline (date and time)
                    <div class='row'>
                        <div class='col-md-7'><input type='date' id='date' name='deadlinedate' placeholder='' class='form-control' required='' value=""> </div>
                        <div class='col-md-5'> <input type='time' class='form-control' name='deadlinetime' value=""> </div>
                    </div>

                    Title
                    <input type='text'  name='title' placeholder='Title' class='form-control' required='' value="">
                    Instruction
                    <textarea  name='instructions' placeholder='Assignment Instructions' class='form-control' required='' value=""></textarea>
                    Mark
                    <input type='text'  name='marks' placeholder='Marks' class='form-control' required='' value="">
                    Attachment 1
                    <input type='file'  name='attachment1' placeholder='Attachment 1' class='form-control'>
                    Attachment 2
                    <input type='file' name='attachment2' placeholder='Attachment 1' class='form-control'>
                    Attachment 3
                    <input type='file'  name='attachment3' placeholder='Attachment 1' class='form-control' >
                    Attachment 4
                    <input type='file'  name='attachment4' placeholder='Attachment 4' class='form-control' >
                    <br>
                    Submission type: <input type='radio' name='type' value='Individual' required=''> Individual

                    <input type='radio' name='type' value='Group' required=''> Group
                    <hr>
                    <input type='submit' class='btn btn-primary' value='Post'><br>
		        </form><br><br><br><br>
	    <?php
        }
            }

        }
            echo "</div>";
            echo "<div class='col-md-7'><h3>Assignment list</h3>";
            error_reporting(0);
            if (isset($_SESSION["info_Updated"])) {
                echo '<hr><div class="alert alert-warning" role="alert">' . $_SESSION['info_Updated'] . '</div>';
                $_SESSION['info_Updated'] = null;
            }
            if (isset($_SESSION['info_courses'])) {
                echo '<hr><div class="alert alert-warning" role="alert">' . $_SESSION['info_courses'] . '</div>';
                $_SESSION['info_courses'] = null;
            }
            if (isset($_SESSION['info_courses'])) {
                echo '<hr><div class="alert alert-warning" role="alert">' . $_SESSION['info_courses'] . '</div>';
                $_SESSION['info_courses']=null;
            }

            if( $_SESSION['user_type'] == "TA") {
                echo "<b style='color:gray'>Only Lecturer can post assignments.</b><br>";
            }

            $result = mysqli_query($con, "SELECT Lab_Report_ID, Type, Marks, Course_ID, Posted_Date, Deadline, Instructions, Title, Attachment_link_1, Attachment_link_2, Attachment_link_3, Attachment_link_4
                                          FROM lab_reports_table
                                          WHERE Course_ID=$id ORDER BY Lab_Report_ID DESC");

            if(mysqli_num_rows($result)==0) {
                echo "No assignments posted so far.";
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    $marks = $row['Marks'];
                    $title = $row['Title'];
                    $ins = $row['Instructions'];
                    $posted = $row['Posted_Date'];
                    $deadline = $row['Deadline'];
                    $att1 = $row['Attachment_link_1'];
                    $att2 = $row['Attachment_link_2'];
                    $att3 = $row['Attachment_link_3'];
                    $att4 = $row['Attachment_link_4'];
                    $id = $row['Lab_Report_ID'];
                    $cours_id = $row['Course_ID'];
                    $as_type = $row['Type'];
                    $full_link = "<a href='~\..\Download.php?file=$att1'>$att1</a>";

                    if ($att2 != "") {
                        $full_link = $full_link." &nbsp|&nbsp <a href='~\..\Download.php?file=$att2'>$att2</a>";
                    }
                    if ($att3 != "") {
                        $full_link = $full_link." &nbsp|&nbsp <a href='~\..\Download.php?file=$att3'>$att3</a>";
                    }
                    if ($att4 != "") {
                        $full_link = $full_link." &nbsp; | &nbsp <a href='~\..\Download.php?file=$att4'>$att4</a>";
                    }

                    $resultx1 = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM lab_report_submissions WHERE lab_report_submissions.Lab_Report_ID=$id");
                    while ($row = mysqli_fetch_assoc($resultx1)) {
                        $count_subs = $row['cnt'];
                    }

                    $resultx2 = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM lab_report_submissions WHERE lab_report_submissions.Lab_Report_ID=$id AND Marks IS NOT null");
                    if (mysqli_num_rows($resultx2) == 0) {
                        $count_marked = 0;
                    } else {
                        while ($row = mysqli_fetch_assoc($resultx2)) {
                            $count_marked = $row['cnt'];
                        }
                    }

                    $header="Courses > ".$name."($code) > Assignments > ".$title;

                    echo "    <div class='card mt-md-2'>
                                  <div class='card-body'>
                                     <h5 class='card-title'>$title ($marks Marks, $as_type)</h5>
                                     <h6 class='card-subtitle'>$ins</h6>
                                     <p class='card-text text-muted'><small>Posted: $posted &nbsp;&nbsp; Deadline: $deadline</small></p>
                                     <p class='card-text'>$count_subs Submissions ( $count_marked Marked )</p>
                                     <a class='card-link' href='Courses.php?course=".$url."&act=edit&cid=".$id."'> Edit </a>
                                     <a class='card-link' href='~\..\Submissions.php?id=$id&header=$header&total=$marks' onclick=''> View </a>
                                     <a class='card-link' href='#'  onclick='extendDeadline($id)'> Extend Deadline </a>
                                     <p class='card-text'> Attachments: $full_link</p>
                                  </div>
                              </div>";

                }
            }
            echo "</div>";

            $resultx1 = mysqli_query($con, "SELECT course_students_table.Student_ID, users_table.Full_Name
                                            FROM course_students_table
                                            INNER JOIN users_table on users_table.Student_ID=course_students_table.Student_ID
                                            WHERE Course_ID=$course_id");

            echo "<span id='dropstudents' style='display:none;'> <select name='studentid'>";
            while($row = mysqli_fetch_assoc($resultx1)) {
                $studentid = $row['Student_ID'];
                $stdname = $row['Full_Name'];
                echo "<option value='$studentid'> $stdname($studentid) </option> ";
            }
            echo "</select><br>Reason <input type='text' name='reason'>"
               . "<input type='hidden' name='url' value='$course_url'>"
               . " </span>";
            return;
	    }

	    ?>



	    <div class="col-md-8">

		<?php
		$user_name = $_SESSION['user_fullname'];

		echo "<h1 class='display-6'>My courses</h1>";

		$result = mysqli_query($con, "SELECT Course_ID, Course_Name, Academic_Year, Faculty, Lecturer_User_ID, TA_User_ID, Course_Code, URL, Verify_New_Members, users_table.Full_Name
                                      FROM courses_table
                                      INNER JOIN users_table ON users_table.User_ID=courses_table.Lecturer_User_ID
                                      WHERE courses_table.Lecturer_User_ID=$user_id
                                      ORDER BY Academic_Year DESC, URL ASC");

		if ($_SESSION['user_type'] == "TA") {
		    $result = mysqli_query($con, "SELECT course_ta.Course_ID, Course_Name, Academic_Year, Faculty, Lecturer_User_ID, TA_User_ID, Course_Code, URL, Verify_New_Members
                                          FROM courses_table
                                          INNER JOIN course_ta ON course_ta.Course_ID=courses_table.Course_ID
                                          WHERE course_ta.TA=$user_id");
		}

		if (mysqli_num_rows($result) != 0) {
		    while ($row = mysqli_fetch_assoc($result)) {
                $id = $row['Course_ID'];
                $name = $row['Course_Name'];
                $code = $row['Course_Code'];
                $faculty = $row['Faculty'];
                $lecturer = $row['Full_Name'];
                $academic = $row['Academic_Year'];
                $url = $row['URL'];
                $resultTA = mysqli_query($con, "SELECT Course_ID, TA, users_table.Full_Name AS TA_NAME
                                                FROM course_ta
                                                INNER JOIN users_table ON users_table.User_ID=course_ta.TA
                                                WHERE course_ta.Course_ID=$id");
                $ta = "";

                while ($rowTA = mysqli_fetch_assoc($resultTA)) {
                    $ta = $ta." ".$rowTA['TA_NAME'];
                }

                if ($ta == "") {
                    $ta = " None";
                }


                echo" <a href='~\..\Courses.php?course=$url'>
                          <div class='btn btn-default'>
                              ($code) - $name
                              <p class='text-muted'><small> Faculty: $faculty &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Year:  $academic  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Lecturer: $lecturer  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  TA:$ta </small></p>
                         </div>
                      </a>";
		    }
		}
		?>

	    </div>
	    <div class="col-md-4">
		<br>
		<b> Course joining requests </b>

		<?php
		$lecturer_id= $_SESSION['user_id'];
		$result = mysqli_query($con, "SELECT course_students_table.ID, users_table.Full_Name, courses_table. Course_ID, Course_Name, Academic_Year, Faculty, Lecturer_User_ID, TA_User_ID, Course_Code, URL, Verify_New_Members
                                      FROM courses_table
                                      INNER JOIN course_students_table ON course_students_table.Course_ID=courses_table.Course_ID
                                      INNER JOIN users_table ON users_table.Student_ID=course_students_table.Student_ID
                                      WHERE Lecturer_User_ID=$lecturer_id AND course_students_table.Status='Pending'");

		if(mysqli_num_rows($result) == 0) {
		    echo "<br>  <i class='fa fa-info-circle'></i>No course-joining request so far for your courses<hr>";
		} else {
            while ($row = mysqli_fetch_assoc($result)) {
                $id = $row['ID'];
                $name = $row['Course_Name'];
                $code = $row['Course_Code'];
                $faculty = $row['Faculty'];
                $student_name = $row['Full_Name'];
                $academic = $row['Academic_Year'];

                echo "<div class='btn btn-default'>
                          $student_name is Requesting to join <br> [($code) - $name ] &nbsp;&nbsp;&nbsp;&nbsp; <br>
                          <a href='~\..\Script.php?AcceptStudent=y&id=$id&rs=yes' class='btn btn-sm btn-success' onclick=return confirm(\"are you sure to join this course?\")' > Accept </a> &nbsp;&nbsp;<a href='~\..\Script.php?AcceptStudent=y&id=$id&rs=no' class='btn btn-sm btn-danger' onclick=return confirm(\"are you sure to join this course?\")' > Decline </a>
                      </div>";
            }
		}
		?>


		<?php
		if ($_SESSION['user_type'] == "TA") {
		    echo "<center>Only Lecturer can post assignments</center>";
		}

		if ($_SESSION['user_type'] == "Lecturer"){
        ?>

		    <b>Create a new course</b>

		    <form method="post" action="Script.php">
			<input type="hidden" name="form_createCourse" value="true" required=""/>
			<input type="hidden" name="l" value="l" required=""/>
			Course name
			<input type="text" name="name" placeholder="Course Name" class="form-control" required="">

			Course code
			<input type="text" name="code" placeholder="Course Code" class="form-control" required="">

			URL (leave blank to use course code & year)
			<input type="text" name="url" placeholder="Choose Custom URL " class="form-control">

			Academic year
			<input type="text" name="academic" placeholder="Academic Year" class="form-control" required="">

			Faculty <br>
			<input type="text" name="faculty" placeholder="Faculty" class="form-control" required="">

			<input type="hidden" name="lecturer" value="<?php echo $_SESSION['user_id'];  ?>">

			Verify joining students?
			<input type="radio" name="verify" value="1"> Yes
			<input type="radio" name="verify" value="0" checked=""> No

			<br><br>
			<input type="submit" class="btn btn-primary" value="Create"><br>

		    </form>

		<?php }  ?>

            </div>

	    <!--   END LECTURER   -->

    <?php
    }


    if ($_SESSION['user_type'] == "Student") {
    ?>

	<!--STUDENT CODE-->
	<div class="row">
	    <div class="col-md-6">

		<?php
		error_reporting(0);
		if (isset($_SESSION['info_Courses_student'])) {
		    echo '<hr><span class="alert alert-success" role="alert">' . $_SESSION['info_Courses_student'] . '</span>';
		    $_SESSION['info_Courses_student'] = null;
		}
		if (isset($_SESSION['info_signup'])) {
            echo  '<hr><div class="alert alert-danger" role="alert">' . $_SESSION['info_signup'] . '</div>';
            $_SESSION['info_signup'] = null;
		}
		?>
		<br><br>
	    </div>
	    <div class="col-md-6"></div>
	</div>


	<div class="row">
	    <div class="col-md-6">

		<?php
		error_reporting(0);
		$student_id = $_SESSION['user_student_id'];
		// current academic year - i.e 2021 - 2022 , so we will show in search result:
		// course containing either 2021 or 2022 as academic year.
		$oldest_academic_year = date('Y') - 1;
		if (!empty($_GET["search"]) || !empty($_GET["faculty"])) {
		    $search = trim(mysqli_real_escape_string($con, $_GET["search"]));
		    $search = strtoupper($search); # was strtoupper($_GET['search']);
		    $faculty = mysqli_real_escape_string($con, $_GET["faculty"]);

		    // the user has not entered something under "Find course by Code"
		    if ($search != "") {
                echo "<h4>Search results for course code: $search </h4><hr>";
                $result = mysqli_query($con, "SELECT Course_ID, Course_Name, Academic_Year, Faculty, Lecturer_User_ID, TA_User_ID, Course_Code, URL, Verify_New_Members, users_table.Full_Name
                                              FROM courses_table
                                              INNER JOIN users_table
						                      ON users_table.User_ID=courses_table.Lecturer_User_ID
                                              WHERE Academic_Year >= $oldest_academic_year AND Course_Code LIKE '%{$search}%' AND courses_table.Course_ID NOT IN
                                                    (SELECT course_id FROM course_students_table WHERE Student_ID=$student_id) ORDER BY Academic_Year DESC");
		    } else if ($faculty != "") { // the user has entered something under "Find course by Code"
                echo "<h3> Find courses under faculty: $faculty</h3>";
                $result = mysqli_query($con, "SELECT Course_ID, Course_Name, Academic_Year, Faculty, Lecturer_User_ID, TA_User_ID, Course_Code, URL, Verify_New_Members, users_table.Full_Name
                                              FROM courses_table
                                              INNER JOIN users_table ON users_table.User_ID=courses_table.Lecturer_User_ID
                                              WHERE Academic_Year >= $oldest_academic_year AND Faculty='$faculty' AND courses_table.Course_ID NOT IN
                                                    (SELECT course_id FROM course_students_table WHERE Student_ID=$student_id) ORDER BY Academic_Year DESC");
		    }

		    if (mysqli_num_rows($result) == 0) {
                echo "No results. <hr>";
		    } else {
                while($row = mysqli_fetch_assoc($result)) {
                    $name = $row['Course_Name'];
                    $code = $row['Course_Code'];
                    $faculty = $row['Faculty'];
                    $lecturer = $row['Full_Name'];
                    $academic = $row['Academic_Year'];
                    $url = $row['URL'];
                    $id = $row['Course_ID'];
                    $v = $row['Verify_New_Members'];
                    if($v > 0) {
                        $msg = "<i class='fa fa-exclamation-circle'></i> Lecturer verification required";
                        $msg2 = "Send Joining Request";
                    }

                    echo "<div class='btn btn-default' style='word-wrap:break-word'>
                              ($code) $name <br>($url) <br>
                              <a href='~\..\Script.php?JoinCourse=y&id=$id&std=$student_id&joining=$v' class='btn btn-sm btn-success' onclick=return confirm(\"Are you sure to join this course?\")' >Join</a> <br>
                              <span style='font-size:10pt'>Faculty: $faculty &nbsp; Year: $academic &nbsp; Lecturer: $lecturer </span><br>
                              $msg
                         </div>";
                }
		    }
		}
		// Otherwise, list the student's joined courses (already done), in reverse chronological order
		echo "<h1 class='display-6'> My courses </h1>";
		$result = mysqli_query($con, "SELECT users_table.Full_Name, course_students_table.Status, courses_table.Course_ID, Course_Name, Academic_Year, Faculty, Lecturer_User_ID, TA_User_ID, Course_Code, URL, Verify_New_Members
                                      FROM courses_table
                                      INNER JOIN users_table ON users_table.User_ID=courses_table.Lecturer_User_ID
                                      INNER JOIN course_students_table ON course_students_table.Course_ID=courses_table.Course_ID
                                      WHERE course_students_table.Student_ID=$student_id ORDER BY Academic_Year DESC, URL ASC");

		if (mysqli_num_rows($result) == 0) {
		    echo "<i class='fa fa-exclamation-circle'></i> You are not enrolled in any Course";
		} else {
		    while($row = mysqli_fetch_assoc($result)) {
                $name = $row['Course_Name'];
                $code = $row['Course_Code'];
                $faculty = $row['Faculty'];
                $lecturer = $row['Full_Name'];
                $academic_year = $row['Academic_Year'];
                $url = $row['URL'];
                $id = $row['Course_ID'];
                $status = $row['Status'];
                if($status == "Joined") {
                    echo "<a href='~\..\Course.php?url=$url'>
                          <div class='btn btn-default' style='word-wrap:break-word'>
                             ($code) $name <br>
                             ($url) &nbsp;&nbsp;&nbsp; <i class='fa fa-check-circle'></i> $status &nbsp;&nbsp;&nbsp;&nbsp; <br>
                             <span style='font-size:8pt'>Faculty: $faculty &nbsp; Year: $academic_year &nbsp; Lecturer: $lecturer </span>
                          </div>
                          </a>";
                } else {
                    echo "<div class='btn btn-default'>
                          ($code) $name  <i class='btn btn-sm btn-danger'> $status</i> <br>
                          <span style='font-size:8pt'>Faculty: $faculty &nbsp; Year: $academic_year &nbsp; Lecturer: $lecturer </span>
                          </div>";
                }
		    }
		}

		echo "</div><div class='col-md-6'>

        <form method='get' action='Courses.php'>
            <div class='row'>
                <div class='col-md-12'>
                    <div class='row'>
                        <div class='col-md-5'>
                            Find new course by course code
                            <input  type='text' class='form-control' name='search' maxlength='11' placeholder='Enter course code'>
                        </div>

                        <div class='col-md-5'>
                            List courses by faculty
                            <select name='faculty' class='form-control'>";
		$result = mysqli_query($con, "SELECT DISTINCT(Faculty) AS Faculty FROM courses_table");
		if (mysqli_num_rows($result) > 0){
		    while($row = mysqli_fetch_assoc($result)) {
                $faculty = $row['Faculty'];
                echo "      <option value='$faculty'> $faculty </option>";
            }
        }

		echo "          </select>
                        </div>

                        <div class='col-md-2'> <br>
                            <button type='submit' class='btn btn-primary'>Find</button>
                       </div>
                    </div>
                </div>
            </div>
        </form>
    </div></div>";

    }

		?>

		<?php include 'Footer.php';?>

	    </div>

</body>
</html>
