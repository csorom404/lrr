<?php
include 'NoDirectPhpAcess.php';
?>


<?php
$page='Courses+';
include 'Header.php';
?>

<div class="container">

    <?php
    $student_id = $_SESSION["user_student_id"];
    $group_id = $_SESSION["user_group_id"];
    $c_date = date("Y-m-d H:i");


    if(!empty($_GET["url"])) {
	$course_url = mysqli_real_escape_string($con, $_GET["url"]);
	$result = mysqli_query($con,"SELECT Course_ID, Course_Name, Academic_Year, Faculty, Lecturer_User_ID, TA_User_ID, Course_Code, URL, Verify_New_Members, users_table.Full_Name
                                     FROM courses_table
                                     INNER JOIN users_table ON users_table.User_ID=courses_table.Lecturer_User_ID
                                     WHERE URL='$course_url'");

	if(mysqli_num_rows($result) == 0) {

            echo "No course matching the given course URL: ".$course_url;

	} else {
            while ($row = mysqli_fetch_assoc($result)) {
		$name = $row['Course_Name'];
		$code = $row['Course_Code'];
		$faculty = $row['Faculty'];
		$lecturer = $row['Full_Name'];
		$academic = $row['Academic_Year'];
		$url = $row['URL'];
		$course_id = $row['Course_ID'];
		// also get teaching assistant names (if any)
		$ta_result = mysqli_query($con, "SELECT Full_Name FROM users_table WHERE User_ID IN (SELECT TA FROM course_ta WHERE Course_ID='$course_id');");
		if (mysqli_num_rows($ta_result) == 0) {
                    echo "<div class='alert' style='border-bottom:2px solid #eee;'>
                              Courses  > ($code) $name > Assignments <br>
                              <span style='font-size:8pt'>Faculty: $faculty &nbsp; Year: $academic &nbsp; Lecturer: $lecturer</span>
                          </div>";
		} else {
                    $ta_name = "";
                    while ($row = mysqli_fetch_assoc($ta_result)) {
			$ta_name = $ta_name.$row['Full_Name']." ";
                    }
                    $ta_name = trim($ta_name);
                    echo "<div class='alert' style='border-bottom:2px solid #eee;'>
                              Courses > ($code) $name  > Assignments <br>
                              <span style='font-size:8pt'>Faculty: $faculty &nbsp; Year: $academic &nbsp; Lecturer: $lecturer &nbsp; Teaching Assistant: $ta_name</span>
                          </div>";
		}
            }
	}
    }
    ?>


    <?php

    if (isset($_SESSION['info_general'])) {
	echo '<div class="alert alert-warning">' . $_SESSION['info_general'] . '</div>';
	$_SESSION['info_general'] = null;
    }

    if (isset($_SESSION['info_courses'])) {
	echo '<div class="alert alert-warning">' . $_SESSION['info_courses'] . '</div>';
	$_SESSION['info_courses'] = null;
    }
    ?>

    <?php
    if( $_SESSION['user_type'] == "Student")
    {
    ?>

	<div class="row">

	    <div class="col-9">

		<!-- Nav tabs -->

		<ul class="nav nav-tabs" id="myTab">
		    <li class="nav-item">
			<a class="nav-link <?php if (!isset($_GET['tab']) || $_GET['tab'] == 'New') echo 'active'; ?>" data-toggle="tab" href="#menu1">New</a>
		    </li>
		    <li class="nav-item">
			<a class="nav-link <?php if ($_GET['tab'] == 'Missed') echo 'active'; ?>" data-toggle="tab" href="#menu2">Missed</a>
		    </li>
		    <li class="nav-item">
			<a class="nav-link <?php if ($_GET['tab'] == 'Submitted') echo 'active'; ?>" data-toggle="tab" href="#menu3">Submitted</a>
		    </li>
		    <li class="nav-item">
			<a class="nav-link <?php if ($_GET['tab'] == 'Marked') echo 'active'; ?>" data-toggle="tab" href="#menu4">Marked</a>
		    </li>
		</ul>

		<div class="tab-content">

		    <div id="menu1" class="tab-pane <?php if (!isset($_GET['tab']) || $_GET['tab'] == 'New') echo 'active'; ?>">

			<?php

			// Get groups of this student
			$sql = "SELECT course_group_members_table.Course_Group_id
                                FROM course_group_members_table
                                INNER JOIN course_groups_table ON course_group_members_table.Course_Group_id=course_groups_table.Course_Group_id
                                WHERE course_group_members_table.Student_ID=$student_id AND course_groups_table.Course_id=$course_id";

			$resultx1 = mysqli_query($con, $sql);
			while($row = mysqli_fetch_assoc($resultx1))
			{
			    $_SESSION['group_id'] = $row['Course_Group_id'];
			}

			$group_id = $_SESSION['group_id'];

			if ($group_id == "")
			{
			    $group_id = 0; // no group.  If the student has a group, the group number should be greater than 0.
			}

			// Show the assignment iff the following conditions are met: (1)
			// Before the deadline (2) Before the students' extended deadline (if any)
			// (3) none of the student's group members have already submitted
			// the assignment.

			$sql_stmt = "SELECT Type, Lab_Report_ID, Marks, Course_ID, Posted_Date, Deadline, Instructions, lab_reports_table.Title, Attachment_link_1, Attachment_link_2, Attachment_link_3, Attachment_link_4
				     FROM lab_reports_table
				     WHERE Course_ID=$course_id
				     AND (Deadline > '$c_date' OR Lab_Report_ID IN (SELECT Lab_Report_ID FROM extended_deadlines_table WHERE Student_ID=$student_id AND Extended_Deadline_Date > '$c_date' AND Lab_Report_ID IN (SELECT Lab_Report_ID FROM lab_reports_table WHERE Course_ID=$course_id)))
				     AND Lab_Report_ID NOT IN (SELECT Lab_Report_ID FROM lab_report_submissions WHERE Course_Group_id IN (SELECT Course_Group_id FROM course_group_members_table WHERE Student_ID=$student_id))
				     ORDER BY Lab_Report_ID DESC";

			$result1 = mysqli_query($con, $sql_stmt);
			if(mysqli_num_rows($result1) == 0) {
			    echo "<br><div class='alert alert-warning'>No active assignments now.</div>";
			} else {
			    while($row = mysqli_fetch_assoc($result1)) {
				$title = $row['Title'];
				$type = $row['Type'];
				$Marks = $row['Marks'];
				$ins = $row['Instructions'];
				$posted = $row['Posted_Date'];
				$deadline = $row['Deadline'];
				$att1 = $row['Attachment_link_1'];
				$att2 = $row['Attachment_link_2'];
				$att3 = $row['Attachment_link_3'];
				$att4 = $row['Attachment_link_4'];
				$labid = $row['Lab_Report_ID'];
				$days_remaining = date_diff(date_create($deadline), date_create())->format('%a days, %h hours, %i minutes');
				$full_link = "<a href='~\..\Download.php?file=$att1'>$att1</a>";

				if($att2 != "") {
				    $full_link = $full_link."| <a href='~\..\Download.php?file=$att2'>$att2</a>";
				}
				if($att3 != "") {
				    $full_link = $full_link."| <a href='~\..\Download.php?file=$att3'>$att3</a>";
				}
				if($att4 != "") {
				    $full_link = $full_link."| <a href='~\..\Download.php?file=$att4'>$att4</a>";
				}

				echo "<div class='card mt-md-2' style='word-wrap: break-word;'>
                                          <div class='card-body'>
                                              <h5 class='card-title'>$title</h5>
                                              <h6 class='card-subtitle''>($Marks Marks, $type)</h6><br>
                                              <p class='card-text'> $ins </p>
                                              <p> <small>Attachments</small>: $full_link </p>
                                              <p class='card-text'> <small> Posted: $posted  &nbsp;&nbsp; Deadline: $deadline </small> </p>
                                              <div class='alert alert-warning'>Time left: $days_remaining</div>
                                              <p><a href='~\..\SubmitLab.php?id=$labid&url=$url' class='btn btn-primary'>Submit</a></p>
                                          </div>
                                      </div>";
			    }
			}
			echo "";
			?>

		    </div>



		    <div id="menu2" class="tab-pane <?php if ($_GET['tab'] == 'Missed') echo 'active'; ?>">

			<?php
			$group_id = $_SESSION['group_id'];

			if ($group_id == "") { // Individual assignment does not require the student to have a group id.  Therefore, the group is an empty string. To make the following SQL statement work properly, initialize the group id to -1.
			    $group_id = -1;
			}

			$result  = mysqli_query($con,"SELECT Lab_Report_ID, Marks, `Course_ID`, `Posted_Date`, `Deadline`, `Instructions`, lab_reports_table.Title, `Attachment_link_1`, `Attachment_link_2`, `Attachment_link_3`, `Attachment_link_4` FROM `lab_reports_table` WHERE Lab_Report_ID not in (select Lab_Report_ID from lab_report_submissions where (Student_id=$student_id or Course_Group_id=$group_id)) and Course_ID=$course_id and Deadline < '$c_date' ORDER by Lab_Report_ID DESC");

			if(mysqli_num_rows($result) == 0)
			{
			    echo '<br><div class="alert alert-warning">You missed no lab reports.</div>';

			} else {
			    while($row = mysqli_fetch_assoc($result)) {
				$title = $row['Title'];
				$marks = $row['Marks'];
				$ins = $row['Instructions'];
				$posted = $row['Posted_Date'];
				$deadline = $row['Deadline'];
				$att1 = $row['Attachment_link_1'];
				$att2 = $row['Attachment_link_2'];
				$att3 = $row['Attachment_link_3'];
				$att4 = $row['Attachment_link_4'];
				$id = $row['Lab_Report_ID'];

				$full_link = "None";

				if ($att1 != "") {
				    $full_link = "<a href='~\..\Lab_Report_Assignments\\$att1'>$att1</a>";
				}

				if($att2 != "") {
				    $full_link = $full_link."| <a href='~\..\Lab_Report_Assignments\\$att2'>$att2</a>";
				}

				if($att3 != "") {
				    $full_link = $full_link."| <a href='~\..\Lab_Report_Assignments\\$att3'>$att3</a>";
				}

				if($att4 != "") {
				    $full_link = $full_link."| <a href='~\..\Lab_Report_Assignments\\$att4'>$att4</a>";
				}


				echo "<div class='card mt-md-2' style='word-wrap: break-word;'>
                                          <div class='card-body'>
                                              <p><span class='btn btn-sm btn-outline-danger'>MISSED</span></p>
                                              <h5 class='card-title'>$title</h5>
                                              <h6 class='card-subtitle'>($marks Marks)</h6> <br>
                                              <p class='card-text'> $ins</p>
                                              <p class='card-text'><small>Posted: $posted &nbsp;&nbsp;&nbsp; Deadline: $deadline &nbsp;&nbsp;&nbsp;</small></p>
                                              <p class='card-text'><small>Attachments: $full_link </small></p>
                                          </div>
                                      </div><br>";

			    }
			}
			?>

		    </div>



		    <div id="menu3" class="tab-pane <?php if ($_GET['tab'] == 'Submitted') echo 'active'; ?>">
			<?php

			$group_id = $_SESSION['group_id'];
			if($group_id == "") {
			    $group_id = -1;
			}  // This fixes "Submitted report not shown" http://118.25.96.118/bugzilla/show_bug.cgi?id=176

			$sql_stmt = "SELECT Lab_Report_ID, Marks, Course_ID, Posted_Date, Deadline, Instructions, lab_reports_table.Title, Attachment_link_1, Attachment_link_2, Attachment_link_3, Attachment_link_4
                                     FROM lab_reports_table
                                     WHERE Lab_Report_ID IN
                                     (
                                      SELECT Lab_Report_ID
                                      FROM lab_report_submissions
				      WHERE Status='Pending' AND (Student_id=$student_id OR Course_Group_id=$group_id) AND Course_ID=$course_id
                                     )
                                     ORDER BY Lab_Report_ID DESC";

			$resultx  = mysqli_query($con, $sql_stmt);
			if(mysqli_num_rows($resultx) == 0) {
			    echo '<br><div class="alert alert-warning">You have no unmarked submissions. Check the Marked tab for your marked submissions (if any).</div>';
			} else {
			    while($row = mysqli_fetch_assoc($resultx)) {
				$lab_repo_id = $row['Lab_Report_ID'];
				$title = $row['Title'];
				$marks = $row['Marks'];
				$ins = $row['Instructions'];
				$posted = $row['Posted_Date'];
				$deadline = $row['Deadline'];
				$att1 = $row['Attachment_link_1'];
				$att2 = $row['Attachment_link_2'];
				$att3 = $row['Attachment_link_3'];
				$att4 = $row['Attachment_link_4'];
				$id = $row['Lab_Report_ID'];

				if ($c_date < $deadline) {
				    $submittedx = "<a href='~\..\SubmitLab.php?id=$id&url=$url' class='btn btn-sm btn-light'>Re-submit</a>";
				}

				$full_link = "<a href='~\..\Lab_Report_Assignments\\$att1'>$att1</a>";

				if ($att2 != "") {
				    $full_link = $full_link."| <a href='~\..\Lab_Report_Assignments\\$att2'>$att2</a>";
				}

				if ($att3 != "") {
				    $full_link = $full_link."| <a href='~\..\Lab_Report_Assignments\\$att3'>$att3</a>";
				}

				if ($att4 != "") {
				    $full_link = $full_link."| <a href='~\..\Lab_Report_Assignments\\$att4'>$att4</a>";
				}

				echo "<div class='btn btn-default break-word' style='dislay:block; word-wrap:break-word; border:1px solid #F0F0F0; border-left:1px solid #eee;'>
                                          $title ($marks Marks) &nbsp; <i class='fa fa-check-circle'></i>SUBMITTED<br>
                                          <span style='font-size:8pt'> $ins </span> <br>
                                          <small>Posted: $posted &nbsp; Deadline: $deadline</small> &nbsp;&nbsp;&nbsp; $submittedx &nbsp; <br>
                                          <small>Submitted files: ";

				$Sub_result = mysqli_query($con,"SELECT Submission_ID, Submission_Date, lab_report_submissions.Lab_Report_ID, lab_report_submissions.Student_id sub_std, lab_report_submissions.Course_Group_id, Attachment1, Notes, Attachment2, Attachment3, Attachment4, Marks, lab_report_submissions.Status, Title,users_table.Full_Name, course_group_members_table.Student_ID
                                                                 FROM lab_report_submissions
                                                                 LEFT JOIN users_table ON users_table.Student_ID=lab_report_submissions.Student_id
                                                                 LEFT JOIN course_group_members_table ON course_group_members_table.Course_Group_id=lab_report_submissions.Course_Group_id
                                                                 WHERE Lab_Report_ID=$lab_repo_id AND lab_report_submissions.Student_id='$student_id'");

				if(mysqli_num_rows($Sub_result) == 0) {
				    echo "No Attachments found.";
				} else {
				    while($row = mysqli_fetch_assoc($Sub_result)) {
					$att1 = $row['Attachment1'];
					$att2 = $row['Attachment2'];
					$att3 = $row['Attachment3'];
					$att4 = $row['Attachment4'];
					$base_att1 = basename($att1);
					$base_att2 = basename($att2);
					$base_att3 = basename($att3);
					$base_att4 = basename($att4);

					$full_link = "<a href='~\..\Download.php?file=$att1&attachment=1'>$base_att1</a>";  // prevent students from directly accessing their classmates' submissions

					if ($att2 != "") {
					    $full_link= $full_link." | <a href='~\..\Download.php?file=$att2&attachment=2'>$base_att2</a>";
					}

					if ($att3 != "") {
					    $full_link= $full_link." | <a href='~\..\Download.php?file=$att3&attachment=3'>$base_att3</a>";
					}

					if ($att4 != "") {
					    $full_link= $full_link." | <a href='~\..\Download.php?file=$att4&attachment=4'>$base_att4</a>";
					}

					echo $full_link;

				    }
				}

				echo "</small></div>";
			    }
			}
			echo "";
			?>

		    </div>


		    <?php

		    $sqli = mysqli_query($con, "SELECT * from course_groups_table WHERE Course_Group_id=$group_id and Course_id=$course_id");
		    while ($row = mysqli_fetch_assoc($sqli)) {
			$Group_Leader = $row['Group_Leader'];
			$Group_Member = $row['Group_Member'];
			$Group_Member2 = $row['Group_Member2'];
			$Group_Member3 = $row['Group_Member3'];
			$Group_Member4 = $row['Group_Member4'];
		    }

		    ?>


		    <div id="menu4" class="tab-pane <?php if ($_GET['tab'] == 'Marked') echo 'active'; ?>">
			<?php
			$resultx  = mysqli_query($con, "SELECT Submission_ID, Submission_Date, lab_reports_table.Lab_Report_ID, Student_id, Course_Group_id, Notes, lab_report_submissions.Marks, lab_report_submissions.Remarking_Reason, Status, lab_reports_table.Title Lab_Title, lab_reports_table.Marks Original_marks
                                                        FROM lab_report_submissions
						        INNER JOIN lab_reports_table ON lab_reports_table.Lab_Report_ID=lab_report_submissions.Lab_Report_ID
						        WHERE (lab_report_submissions.Student_id='$student_id'
                                                               OR (lab_report_submissions.Student_id='$Group_Leader' AND lab_report_submissions.Course_Group_id='$group_id')
                                                               OR (lab_report_submissions.Student_id='$Group_Member' AND lab_report_submissions.Course_Group_id='$group_id')
                                                               OR (lab_report_submissions.Student_id='$Group_Member2' AND lab_report_submissions.Course_Group_id='$group_id')
                                                               OR (lab_report_submissions.Student_id='$Group_Member3' AND lab_report_submissions.Course_Group_id='$group_id')
                                                               OR (lab_report_submissions.Student_id='$Group_Member4' AND lab_report_submissions.Course_Group_id='$group_id')
                                                              ) AND lab_reports_table.Lab_Report_ID IN (SELECT Lab_Report_ID
                                                                                                         FROM lab_report_submissions
                                                                                                         WHERE  (Status='Marked' or Status='Remarking') AND (Student_id=$student_id OR Course_Group_id=$group_id) AND Course_ID=$course_id)
                                                        ORDER BY Submission_ID DESC"); // TODO: over-complex, need to simplify

			if (mysqli_num_rows($resultx) == 0) {
			    echo '<br><div class="alert alert-warning">You have no marked submissions.</div>';
			} else {
			    while($row = mysqli_fetch_assoc($resultx)) {
				$title = $row['Lab_Title'];
				$marks = $row['Marks'];
				$original_marks = $row['Original_marks'];
				$ins = $row['Instructions'];
				$posted = $row['Posted_Date'];
				$deadline = $row['Deadline'];
				$att1 = $row['Attachment_link_1'];
				$att2 = $row['Attachment_link_2'];
				$att3 = $row['Attachment_link_3'];
				$att4 = $row['Attachment_link_4'];
				$id = $row['Lab_Report_ID'];
				$submission_id = $row['Submission_ID'];
				$notes = $row['Notes'];
				$status =  $row['Status'];
				$remarking_reason = $row['Remarking_Reason'];

				if ($status == 'Marked') {
				    $remarking_url = "\Script.php?remarking=yes&id=$submission_id&url=$url&status=Remarking";
				    $remarking = "<button  onclick='remarking(\"$remarking_url\")' class='btn btn-sm btn-light'>Request remarking</button>";
				}

				if ($status =='Remarking') {
				    $remarking = "<br> <span  style='color:orange'><i class='fa fa-info-circle'></i> Remarking request sent </span> Reasons for remarking: <i>$remarking_reason </i> <br>";
				}

				echo "<div class='card mt-md-2' style='word-wrap:break-word;'>
                                          <div class='card-body'>
                                               <h5 class='card-title'>$title</h5>
                                               <h6 class='card-subtitle'>($marks marks out of $original_marks)</h6><br>
                                               <p class='card-text'>Lecturer feedback $notes &nbsp;&nbsp; $remarking</p>
                                               <small>Submitted files: ";
				$sub_result = mysqli_query($con,"SELECT Submission_ID, Submission_Date, lab_report_submissions.Lab_Report_ID, lab_report_submissions.Student_id sub_std, lab_report_submissions.Course_Group_id, Attachment1, Notes, Attachment2, Attachment3, Attachment4, Marks, lab_report_submissions.Status, Title, users_table.Full_Name, course_group_members_table.Student_ID
                                                                 FROM lab_report_submissions
                                                                 LEFT JOIN users_table ON users_table.Student_ID=lab_report_submissions.Student_id
                                                                 LEFT JOIN course_group_members_table ON course_group_members_table.Course_Group_id=lab_report_submissions.Course_Group_id
                                                                 WHERE Lab_Report_ID=$id AND lab_report_submissions.Student_id='$student_id'");

				if (mysqli_num_rows($sub_result) == 0) {
				    echo "None.";
				} else {
				    while($row = mysqli_fetch_assoc($sub_result)) {
					$att1 = $row['Attachment1'];
					$att2 = $row['Attachment2'];
					$att3 = $row['Attachment3'];
					$att4 = $row['Attachment4'];

					$full_link = "<a href='~\..\Download.php?file=$att1'>$att1</a>";

					if($att2 != "") {
					    $full_link = $full_link."| <a href='~\..\Download.php?file=$att2'>$att2</a>";
					}
					if($att3 != "") {
					    $full_link = $full_link."| <a href='~\..\Download.php?file=$att3'>$att3</a>";
					}

					if($att4 != "") {
					    $full_link = $full_link."| <a href='~\..\Download.php?file=$att4'>$att4</a>";
					}

					echo $full_link;

				    }
				}

				echo "</small></div></div>"; // This statement's position must be correct.  Otherwise, the "My groups" part won't be placed correctly.
			    }
			}
			?>

		    </div> <!-- Closing menu4 -->
		</div>  <!-- Closing tab-content -->
	    </div>  <!-- Closing col-9 -->

	    <div class="col-3">

		<h1 class="display-6">My groups</h1>

		<?php
		echo " <button onclick='createGroup()' class='btn btn-primary'>Create group</button>";
		?>

		<hr>

		<?php

		$result = mysqli_query($con, "SELECT ID, course_group_members_table.Course_Group_id, Student_ID, Status,course_groups_table.Group_Name, course_groups_table.Course_id FROM course_group_members_table INNER JOIN course_groups_table ON course_groups_table.Course_Group_id=course_group_members_table.Course_Group_id WHERE Student_id=$student_id and course_groups_table.Course_id=$course_id");

		if(mysqli_num_rows($result) == 0) {
		    echo "You have no group in this course.";
		} else {
		    while($row = mysqli_fetch_assoc($result)) {
			$name = $row['Group_Name'];
			$id = $row['Course_Group_id'];
			$status = $row['Status'];
			$extra = " <a href='#' class='' onclick='invite($id)'> Invite member </a></small>";

			if($status == "Invited")
			{
			    $extra2 = "   <a href='#' class='' onclick='accept($id,1)'>Accept</a></small>";
			    $extra3 = "   <a href='#' class='' onclick='accept($id,0)'>Decline</a></small>";
			}

			echo "<ul class='list-group'>";

			echo "<li class='list-group-item'><b>$name</b> ($id) <br> $extra <br> $extra2 <br> $extra3 </li>";

			$rs2 = mysqli_query($con,"SELECT Course_Group_id, users_table.Student_ID, course_group_members_table.Status, users_table.Full_Name
                            FROM course_group_members_table
                            INNER JOIN users_table ON users_table.Student_ID=course_group_members_table.Student_ID
                            WHERE course_group_members_table.Student_ID AND course_group_members_table.Course_Group_id=$id");

			# Check whether the current user in session is the creator of the group
			$rs3 = mysqli_query($con, "SELECT Status from course_group_members_table where Student_ID = $student_id");
			$flag = mysqli_fetch_assoc($rs3)['Status'] == "Created";

			while ($row = mysqli_fetch_assoc($rs2)) {
			    $name = $row['Full_Name'];
			    $id = $row['Course_Group_id'];
			    $status = $row['Status'];
			    $Student_ID = $row['Student_ID'];

			    # Show group members + Kick out button next to each member except the creator of the group
			    if ($flag) {
				echo "<li class='list-group-item'>$name - $Student_ID ($status)&nbsp;".(($status != "Created")?"<button onclick='removeMember($Student_ID, $id)'
                class='btn btn-sm btn-warning'>Kick out</button>":"")."</li>";
			    } else{
				echo "<li class='list-group-item'><small> $name - $Student_ID ($status)</small>";
			    }
			}

			# Add "delete group" button and allow only group creator to delete it
			if ($status == 'Created') {
			    echo "<li class='list-group-item'> <button onclick='deleteGroup($id)' class='btn btn-sm btn-danger'>Delete group</button> </li>";
			}
			echo "</ul>";
		    }
		}
		?>
	    </div>

	</div>

    <?php
    }
    ?>

</div>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" integrity="sha512-ELV+xyi8IhEApPS/pSj66+Jiw+sOT1Mqkzlh8ExXihe4zfqbWkxPRi8wptXIO9g73FSlhmquFlUOuMSoXz5IRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js" integrity="sha512-57oZ/vW8ANMjR/KQ6Be9v/+/h6bq9/l3f0Oc7vn6qMqyhvPd1cvKBRWWpzu0QoneImqr2SkmO4MSqU+RpHom3Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<script>

 function createGroup() {
     try {
         $('<form id="frm" method="get" action="Script.php"> \
                <input type="hidden" name="creategroup" value="true"> \
                <input type="hidden" name="student_id" value="<?php echo $student_id; ?>" > \
                Group name \
                <input type="text" name="name"> \
                <input type="hidden" name="url" value="<?php echo $url; ?>">  \
                <input type="hidden" name="id" value="<?php echo $course_id; ?>"> \
           </form>').dialog({
	       modal: true,
	       title:'Create a group',
	       close: function () {
		   var closeBtn = $('.ui-dialog-titlebar-close');
		   closeBtn.html('');
	       },
	       buttons: {
		   'Create': function () {
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



 function invite(id) {
     try {
         $('<form id="frm" method="get" action="Script.php"> \
                <input type="hidden" name="groupinvite" value="true"> \
                <input type="hidden" name="groupid" value="'+id+'" > Enter Student ID to invite \
                <input type="text" name="student_id"> \
                <input type="hidden" name="url" value="<?php echo $url; ?>"> \
                <input type="hidden" name="courseid" value="<?php echo $course_id; ?>"> \
            </form>').dialog({
		modal: true,
		title:'Invite students to group',
		close: function () {
		    var closeBtn = $('.ui-dialog-titlebar-close');
		    closeBtn.html('');
		},
		buttons: {
		    'Invite': function () {
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



 function accept(id,val) {

     try {
         $('<form id="frm" method="get" action="Script.php"> \
                <input type="hidden" name="acceptinvite" value="true"> \
                <input type="hidden" name="groupid" value="'+id+'" > \
                <input type="hidden" name="action" value="'+val+'" > \
                <input type="hidden" name="student_id" value="<?php echo $student_id; ?>" > \
                <input type="hidden" name="url" value="<?php echo $url; ?>"> \
                <input type="hidden" name="courseid" value="<?php echo $course_id; ?>"> \
            </form>').dialog({
		modal: true,
		title:'Respond to group invitation',
		close: function () {
		    var closeBtn = $('.ui-dialog-titlebar-close');
		    closeBtn.html('');
		},
		buttons: {
		    'Confirm': function () {
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



 function remarking(data)
 {
     const details = prompt("Please enter your remarking reasons","");

     window.location.href = data+"&details="+details;
 }



 function removeMember(student_id, group_id) {
     try {
         $('<form id="frm" method="get" action="Script.php"> \
                <input type="hidden" name="removemember" value="true"> \
                <input type="hidden" name="student_id" value="'+student_id+'" > \
                <input type="hidden" name="group_id" value="'+group_id+'"> \
                <input type="hidden" name="url" value="<?php echo $url; ?>"></form>').dialog({
		    modal: true,
		    title:'Kick out '+student_id+'?',
		    close: function () {
			var closeBtn = $('.ui-dialog-titlebar-close');
			closeBtn.html('');
		    },
		    buttons: {
			'Yes': function () {
			    $('#frm').submit();
			    $(this).dialog('close');
			},
			'No': function () {
			    $(this).dialog('close');
			}
		    }
		});
     } catch(e) {
	 alert(e);
     }
 }



 function deleteGroup(id) {

     try {
         $('<form id="frm" method="get" action="Script.php"> \
                <input type="hidden" name="deletegroup" value="true"> \
                <input type="hidden" name="group_id" value="'+id+'" > \
                <input type="hidden" name="url" value="<?php echo $url; ?>"></form>').dialog({
		    modal: true,
		    title:'Delete this group?',
		    close: function () {
			var closeBtn = $('.ui-dialog-titlebar-close');
			closeBtn.html('');
		    },
		    buttons: {
			'Yes': function () {
			    $('#frm').submit();
			    $(this).dialog('close');
			},
			'No': function () {
			    $(this).dialog('close');
			}
		    }
		});
     } catch(e) {
	 alert(e);
     }
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
