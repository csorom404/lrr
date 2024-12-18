<?php
include 'NoDirectPhpAcess.php';
?>

<?php
$page = 'Courses+';
include 'Header.php';
$student_id = $_SESSION["user_student_id"];
$group_id = $_SESSION["user_group_id"];
$c_date = date("Y-m-d H:i");

if (!empty($_GET["id"])) {
    $id = mysqli_real_escape_string($con, $_GET["id"]);
    $course_id = $id;
}

if (!empty($_GET["header"])) {
    $header = $_GET["header"];
}

if (!empty($_GET["total"])) {
    $total = $_GET["total"];
} else {
    $total = 0;
}

$resultx1 = mysqli_query($con, "SELECT Lab_Report_ID, Title, lab_reports_table.Course_ID, Posted_Date, Deadline, Marks, Type, courses_table.URL
                                FROM lab_reports_table
                                INNER JOIN courses_table ON courses_table.Course_ID=lab_reports_table.Course_ID
                                WHERE Lab_Report_ID=$id");
while ($row = mysqli_fetch_assoc($resultx1)) {
    $report_type = $row['Type'];
    $c_id = $row['Course_ID'];
    $report_title = $row['Title'];
    $url = $row['URL'];
}
?>

<div class="container">
    
<?php
echo "<div><a href='Courses.php?course=$url'> $header </a></div>";
?>


<div class="row">

    <!--    Lecturer  CODE-->
    <?php

    if ($_SESSION['user_type'] == "Lecturer" || $_SESSION['user_type'] == "TA") {

    ?>

        <div class="col-md-12">

            <?php

            error_reporting(0);

            if (isset($_SESSION['info_Marking'])) {
                echo  '<div class="alert alert-warning">' . $_SESSION['info_Marking'] . '</div>';
                $_SESSION['info_Marking'] = null;
            }

            $resultx1 = mysqli_query($con, "SELECT Count(*) AS cnt FROM lab_report_submissions WHERE lab_report_submissions.Lab_Report_ID=$id");
            $row = mysqli_fetch_assoc($resultx1);
            $count_submissions = $row['cnt'];

            $resultx2 = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM lab_report_submissions WHERE lab_report_submissions.Lab_Report_ID=$id and Status='Marked'");
	    $row = mysqli_fetch_assoc($resultx2);
            $count_marked = $row['cnt'];

            $resultx3 = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM lab_report_submissions WHERE lab_report_submissions.Lab_Report_ID=$id and Status='Pending'");
            $row = mysqli_fetch_assoc($resultx3);
            $count_unmarked = $row['cnt'];

            $resultx4 = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM lab_report_submissions WHERE lab_report_submissions.Lab_Report_ID=$id and Status='Remarking'");
            $row = mysqli_fetch_assoc($resultx4);
            $count_remark = $row['cnt'];

            $resultx5 = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM course_groups_table WHERE Course_id=$c_id");
            $row = mysqli_fetch_assoc($resultx5);
            $count_group = $row['cnt'];

            ?>

	    <br>
            <p class="text-muted"><b>Total submissions (<?php echo $count_submissions; ?>)</b></p>
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="myTab">
                <li class="nav-item">
                    <a class="nav-link active" href="#menu1">Unmarked submissions (<?php echo $count_unmarked; ?>)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#menu2">Marked submissions (<?php echo $count_marked; ?>)</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#menu3">Remarking requests (<?php echo $count_remark; ?>)</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#menu4">Course groups (<?php echo $count_group; ?>)</a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="menu1" class="tab-pane active"><br>

                    <?php

                    if ($report_type == "Group") {
                        $result1 = mysqli_query($con, "SELECT Submission_ID, Submission_Date, lab_report_submissions.Lab_Report_ID, lab_report_submissions.Course_Group_id, Attachment1, Notes, Attachment2, Attachment3, Attachment4, Marks, lab_report_submissions.Status, Title, course_groups_table.Group_Name, course_groups_table.Group_Leader, users_table.Full_Name, users_table.Student_id
                                                       FROM lab_report_submissions
                                                       LEFT JOIN users_table ON users_table.Student_ID=lab_report_submissions.Student_id
                                                       LEFT JOIN course_groups_table ON course_groups_table.Course_Group_id=lab_report_submissions.Course_Group_id
                                                       WHERE Lab_Report_ID=$id AND lab_report_submissions.Status='Pending' ORDER BY Submission_Date DESC");
                    } else {
                        $result1 = mysqli_query($con, "SELECT Submission_ID, Submission_Date, lab_report_submissions.Lab_Report_ID, lab_report_submissions.Student_id sub_std, lab_report_submissions.Course_Group_id, Attachment1, Notes, Attachment2, Attachment3, Attachment4, Marks, lab_report_submissions.Status, Title, users_table.Full_Name, course_group_members_table.Student_ID
                                                       FROM lab_report_submissions
                                                       LEFT JOIN users_table ON users_table.Student_ID=lab_report_submissions.Student_id
                                                       LEFT JOIN course_group_members_table ON course_group_members_table.Course_Group_id=lab_report_submissions.Course_Group_id
                                                       WHERE Lab_Report_ID=$id AND lab_report_submissions.Status='Pending' ORDER BY Submission_Date DESC");
                    }

                    if (mysqli_num_rows($result1) == 0) {
                        echo "No unmarked submissions.";
                    } else {
                        while ($row = mysqli_fetch_assoc($result1)) {
                            $title = $row['Title'];
                            $Marks = $row['Marks'];
                            $posted = $row['Submission_Date'];
                            $deadline = $row['Deadline'];
                            $att1 = $row['Attachment1'];
                            $att2 = $row['Attachment2'];
                            $att3 = $row['Attachment3'];
                            $att4 = $row['Attachment4'];
                            $labid = $row['Lab_Report_ID'];

                            $submitter_student_number = $row['Student_id'];
                            $submitted_group = $row['Course_Group_id'];
                            $Submission_ID = $row['Submission_ID'];
                            $student_name = $row['Full_Name'];
                            $groupname = $row['Group_Name'];
                            $groupleader = $row['Group_Leader'];
                            $student_id = $row['sub_std'];

                            if ($submitted_group == 0) {
                                $submitted_by = $student_name . " (" . $student_id . ")";
                            } else {
                                $submitted_by = "$student_name ($submitter_student_number) for group $groupname ";
                            }

                            $base_att1 = basename($att1);
                            $base_att2 = basename($att2);
                            $base_att3 = basename($att3);
                            $base_att4 = basename($att4);

                            $full_link = "<a href='~\..\Download.php?file=$att1&attachment=1'>$base_att1</a>";  // prevent students from directly accessing their classmates' submissions

                            if ($att2 != "") {
                                $full_link = $full_link . " | <a href='~\..\Download.php?file=$att2&attachment=2'>$base_att2</a>";
                            }
                            if ($att3 != "") {
                                $full_link = $full_link . " | <a href='~\..\Download.php?file=$att3&attachment=3'>$base_att3</a>";
                            }

                            if ($att4 != "") {
                                $full_link = $full_link . " | <a href='~\..\Download.php?file=$att4&attachment=4'>$base_att4</a>";
                            }

                            echo "<div class='btn btn-default break-word' style='dislay:block; word-wrap: break-word; border: 1px solid #F0F0F0;border-left:1px solid #eee;'>
                                      $title  <br>
                                      By: <b> <span class='text-selectable'> $submitted_by </span> </b> <br>
                                      <small>Submitted on: $posted</small>
                                      <button class='btn btn-sm btn-primary' style='margin-left:50px;' onclick='mark($Submission_ID,\"$title\",$total)'> Mark </button><br>
                                      Attachments : $full_link
                                  </div>";
                        }
                    }
                    echo "";
                    ?>

                </div>

                <div id="menu2" class="tab-pane"><br>

                    <?php

                    if ($report_type == "Group") {
                        $result = mysqli_query($con, "SELECT Submission_ID, Visibility, Submission_Date, lab_report_submissions.Lab_Report_ID, lab_report_submissions.Course_Group_id, Attachment1, Notes, Attachment2, Attachment3, Attachment4, Marks, lab_report_submissions.Status, Title, course_groups_table.Group_Name
                                                      FROM lab_report_submissions
                                                      LEFT JOIN course_groups_table ON course_groups_table.Course_Group_id=lab_report_submissions.Course_Group_id
                                                      WHERE Lab_Report_ID=$id and lab_report_submissions.Status='Marked'");
                    } else {
                        $result = mysqli_query($con, "SELECT Submission_ID, Visibility, Submission_Date, lab_report_submissions.Lab_Report_ID, lab_report_submissions.Student_id sub_std, lab_report_submissions.Course_Group_id, Attachment1, Notes, Attachment2, Attachment3, Attachment4, Marks, lab_report_submissions.Status, Title, users_table.Full_Name, course_group_members_table.Student_ID
                                                      FROM lab_report_submissions
                                                      LEFT JOIN users_table ON users_table.Student_ID=lab_report_submissions.Student_id
                                                      LEFT JOIN course_group_members_table ON course_group_members_table.Course_Group_id=lab_report_submissions.Course_Group_id
                                                      WHERE Lab_Report_ID=$id AND lab_report_submissions.Status='Marked' ORDER BY lab_report_submissions.Student_id Desc");
                    }

                    if (mysqli_num_rows($result) == 0) {

                        echo "No marked submissions.";

                    } else {

                        echo "<h3><a href='~\..\Script.php?exportgrade=true&lab=$id&lab_name=$report_title'><i class='fa fa-book'></i> Export grades</a></h3>";

                        while ($row = mysqli_fetch_assoc($result)) {
                            $title = $row['Title'];
                            $Marks = $row['Marks'];
                            $posted = $row['Submission_Date'];
                            $deadline = $row['Deadline'];
                            $att1 = $row['Attachment1'];
                            $att2 = $row['Attachment2'];
                            $att3 = $row['Attachment3'];
                            $att4 = $row['Attachment4'];
                            $labid = $row['Lab_Report_ID'];

                            $submitter_student_number = $row['Student_id'];
                            $submitted_group = $row['Course_Group_id'];
                            $Submission_ID = $row['Submission_ID'];
                            $student_name = $row['Full_Name'];
                            $student_id = $row['sub_std'];
                            $Visibility = $row['Visibility'];
                            $notes = $row['Notes'];

                            if ($submitted_group == 0) {
                                $submitted_by = $student_name . "(" . $student_id . ")";
                            } else {
                                $submitted_by = "<i>(GROUP)</i> Group X ";
                            }

                            $base_att1 = basename($att1);

                            $full_link = "<a href='~\..\Download.php?file=$att1&attachment=1'>$base_att1</a>";  // prevent students from directly accessing their classmates' submissions

                            if ($att2 != "") {
                                $full_link = $full_link . "| <a href='~\..\Lab_Report_Submisions\\$att2'>$att2</a>";
                            }
                            if ($att3 != "") {
                                $full_link = $full_link . "| <a href='~\..\Lab_Report_Submisions\\$att3'>$att3</a>";
                            }

                            if ($att4 != "") {
                                $full_link = $full_link . "| <a href='~\..\Lab_Report_Submisions\\$att4'>$att4</a>";
                            }
                            // you will notice why i used span here to wrap the $submitted_by variable
                            // because if we wrap with span , the css class text-selectable can be used only by the submittedBy variable
                            // if you want to use text-selectable class on whole div, just call the css class


                            echo "<div class='btn btn-default break-word' style='dislay:block; word-wrap:break-word; border:1px solid #F0F0F0; border-left:1px solid #eee;'>
                                      <b> $title </b>  &nbsp;&nbsp; [Marks: $Marks] <button class='btn btn-light btn-sm' onclick='mark($Submission_ID,\"$title\",$total)'>Remark</button><br>
                                      <small>Submitted by <span class = 'text-selectable'>$submitted_by</span> on $posted</small>
                                      <span class='badge badge-info'>Marking comments</span> $notes <br>
                                      Attachments : $full_link
                                  </div>";
                        }
                    }
                    echo "";
                    ?>

                </div>

                <div id="menu3" class="tab-pane"><br>

                    <?php

                    if ($report_type == "Group") {
                        $resulty = mysqli_query($con, "SELECT Submission_ID, Submission_Date, lab_report_submissions.Lab_Report_ID, lab_report_submissions.Course_Group_id, Attachment1, Notes, Attachment2, Attachment3, Attachment4, lab_report_submissions.Marks, lab_report_submissions.Status, Title, course_groups_table.Group_Name
                                                       FROM lab_report_submissions
                                                       LEFT JOIN course_groups_table ON course_groups_table.Course_Group_id=lab_report_submissions.Course_Group_id
                                                       WHERE Lab_Report_ID=$id AND lab_report_submissions.Status='Remarking'");
                    } else {
                        $resulty = mysqli_query($con, "SELECT Submission_ID, Submission_Date, lab_report_submissions.Lab_Report_ID,  lab_report_submissions.Remarking_Reason, lab_report_submissions.Student_id sub_std, lab_report_submissions.Course_Group_id, Attachment1, Notes, Attachment2, Attachment3, Attachment4, lab_report_submissions.Marks, lab_report_submissions.Status, Title, users_table.Full_Name, course_group_members_table.Student_ID
                                                       FROM lab_report_submissions
                                                       LEFT JOIN users_table ON users_table.Student_ID=lab_report_submissions.Student_id
                                                       LEFT JOIN course_group_members_table ON course_group_members_table.Course_Group_id=lab_report_submissions.Course_Group_id
                                                       WHERE Lab_Report_ID=$id AND lab_report_submissions.Status='Remarking'");
                    }

                    if (mysqli_num_rows($resulty) == 0) {
                        echo "No remarking requests.";
                    } else {
                        while ($row = mysqli_fetch_assoc($resulty)) {
                            $title = $row['Title'];
                            $Marks = $row['Marks'];
                            $posted = $row['Submission_Date'];
                            $deadline = $row['Deadline'];

                            $att1 = $row['Attachment1'];
                            $att2 = $row['Attachment2'];
                            $att3 = $row['Attachment3'];
                            $att4 = $row['Attachment4'];
                            $labid = $row['Lab_Report_ID'];

                            $remarking_reason = $row['Remarking_Reason'];

                            $submitter_student_number = $row['Student_id'];
                            $submitted_group = $row['Course_Group_id'];
                            $Submission_ID = $row['Submission_ID'];
                            $student_name = $row['Full_Name'];
                            $student_id = $row['sub_std'];
                            $gname = $row['Group_Name '];

                            if ($submitted_group == 0) {
                                $submitted_by = $student_name . "(" . $student_id . ")";
                            } else {
                                $submitted_by = "<i>(GROUP)</i> $gname";
                            }

                            $full_link = "<a href='~\..\Lab_Report_Submisions\\$att1'>$att1</a>";

                            if ($att2 != "") {
                                $full_link = $full_link . "| <a href='~\..\Lab_Report_Submisions\\$att2'>$att2</a>";
                            }
                            if ($att3 != "") {
                                $full_link = $full_link . "| <a href='~\..\Lab_Report_Submisions\\$att3'>$att3</a>";
                            }

                            if ($att4 != "") {
                                $full_link = $full_link . "| <a href='~\..\Lab_Report_Submisions\\$att4'>$att4</a>";
                            }
                            echo "<div class='btn btn-default break-word'  style='dislay:block; word-wrap: break-word; border: 1px solid #F0F0F0;border-left: 2px solid #eee;'>"
                               .   "$title <br>"
			       .   "Submitted by: <b> <span class = 'text-selectable'>$submitted_by </span>  &nbsp; &nbsp;&nbsp;&nbsp;&nbsp; [ Marks: $Marks ] </b> <br>"
			       .   "<span style='color:orange'><i class='fa fa-info-circle'></i> Remarking reason:</span> $remarking_reason <br>"
                               .   "<button class='btn btn-light btn-sm' onclick='mark($Submission_ID,\"$title\",$total)'>Remark</button>"
                               .   "&nbsp; <a href='~\..\Script.php?ignoreremarking=yes&id=$id&subid=$Submission_ID&header=$header&total=$total&status=Marked' class='btn btn-sm btn-light'>Ignore request</a> <br>"
			       .   "<small>Submitted at: $posted <br> Attachments: $full_link </small>"
			       . "</div>";
                        }
                    }
                    echo "";
                    ?>

                </div>

                <div id="menu4" class="tab-pane"><br>

                    <div class="col-md-7">
                    <?php

                    $result = mysqli_query($con, "SELECT Course_Group_id, Group_Name, Group_Leader, Course_id, users_table.Full_Name
                                                  FROM course_groups_table
                                                  INNER JOIN users_table ON users_table.Student_ID=course_groups_table.Group_Leader
                                                  WHERE Course_id=$c_id");
                    if (mysqli_num_rows($result) == 0) {
                        echo "No student groups.";
                    } else {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $name = $row['Group_Name'];
			    $leader_student_no = $row['Group_Leader'];
                            $id = $row['Course_Group_id'];

                            echo "<ul class='list-group'>";
			    echo "    <li class='list-group-item disabled'>Group $name</li>";

                            $rs2 = mysqli_query($con, "SELECT ID, Course_Group_id, course_group_members_table.Student_ID, course_group_members_table.Status, users_table.Full_Name
                                                       FROM course_group_members_table 
                                                       INNER JOIN users_table ON users_table.Student_ID=course_group_members_table.Student_ID
                                                       WHERE course_group_members_table.Student_ID AND course_group_members_table.Course_Group_id=$id");

                            while ($row = mysqli_fetch_assoc($rs2)) {
                                $name = $row['Full_Name'];
                                $id = $row['Course_Group_id'];
                                $status = $row['Status'];
                                $Student_ID = $row['Student_ID'];
                                if ($leader_student_no == $Student_ID) {
                                    echo "<li class='list-group-item'>$name ($Student_ID) - $status - Leader</li>";
				} else {
                                    echo "<li class='list-group-item'>$name ($Student_ID) - $status</li>";
				}
                            }
			    echo "</ul><br>";
                        }
                    }
                    ?>
                    </div>
                </div>

            </div>

        </div>

</div>

    <?php
    }
    ?>

    <?php include 'Footer.php';?>


</div>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" integrity="sha512-ELV+xyi8IhEApPS/pSj66+Jiw+sOT1Mqkzlh8ExXihe4zfqbWkxPRi8wptXIO9g73FSlhmquFlUOuMSoXz5IRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>	    
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js" integrity="sha512-57oZ/vW8ANMjR/KQ6Be9v/+/h6bq9/l3f0Oc7vn6qMqyhvPd1cvKBRWWpzu0QoneImqr2SkmO4MSqU+RpHom3Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>

    function mark(id, title, marks) {

        try {

            $(`<form id="submit-form" method="get" action="Script.php">
	        ${title} (${marks}  marks)
	        <input type="hidden" name="savemarks" value="true">
                <input type="hidden" name="total" value="${marks}" >
                <input type="hidden" name="id" value="${id}" >
                <br> Marks
                <input type="text" name="marks">\n\
                Comments <textarea name="feedback"></textarea>  \n\
                <input type="hidden" name="labid" value="<?php echo $course_id; ?>">
                <input type="hidden" name="header" value="<?php echo $header; ?>">
              </form>`).dialog({
                  modal: true,
                  title: 'Mark submission',
		  close: function () {
		      var closeBtn = $('.ui-dialog-titlebar-close');
		      closeBtn.html('');
		  },     
                  buttons: {
                      'Submit': function() {
                        $('#submit-form').submit();
                        $(this).dialog('close');
                    },
                    'Cancel': function() {
                        $(this).dialog('close');
                    }

                }
            });

        } catch (e) {
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
