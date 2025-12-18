<?php

include("includes/session_include.php");
include("includes/enc_conn_include.php");
include("includes/header_validate_code.php");


/*	ACADEMIC YEAR	*/
$academic_year_data = explode("-", $_SESSION["academic_year"]);
$academic_year = trim($academic_year_data[0]);

/*	EXAM ID	*/
$exam_id = $_SESSION['exam_id'];

if (!isset($_SESSION["academic_year"]) || !isset($_SESSION['exam_id']) || $_SESSION["academic_year"] == "" || $_SESSION['exam_id'] == "") {
?>
  <script>
    alert('<?php echo "Invalid Access"; ?>');
    window.location = "logout.php";
  </script>
<?php
  exit;
}

?>

<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Bharathidasan University, Tiruchirappalli, Tamil Nadu, India.</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="images/favicon.ico" />

</head>

<body>
  <?php include("header.php"); ?>
  <div class="contact-w3-agileits" id="contact">
    <div class="container">
      <div class="container box">
        <!-- <br />
   <h3 align="center" style="font-size:15px; width:55%">Course Master Report</h3><br /> -->
        <fieldset>
          <legend>
            <font size="+1" align="left" color="#993300" face="Times New Roman, Times, serif">Discipline Master Report</font>
          </legend>

          <div class="contact-w3-agileits master" id="contact" style="padding:0px 0px;margin-top:2%">
            <form id="course_master_report" name="course_master_report" action="view_course_master_report.php" method="post" enctype="multipart/form-data">
              <input type="hidden" id="pagetoken" name="graduation" value="<?php echo $searchGraduation; ?>">
              <div class="form-group">
                <table class="table table-striped table-bordered" style="width:100%;">
                  <tr class="table-color">
                    <td><b>S.No</b></td>
                    <td><b>Course ID</b></td>
                    <td><b>Course Name</b></td>
                    <td><b>Subject Code</b></td>
                  </tr>
                  <?php


                  $selSubject = "SELECT * FROM inst.iml_course_subject_code
					WHERE exam_id=:exam_id
					AND graduation_id=:department_id
					ORDER BY  course_name, subject_code ";

                  $exeSubject = $db->prepare($selSubject);
                  $exeSubject->bindParam(':exam_id', validateInputData($exam_id));
                  $exeSubject->bindParam(':department_id', validateInputData($_SESSION['department_id']));
                  $exeSubject->execute();


                  //$exeSubject=$db->query($selSubject);
                  if ($exeSubject->rowCount() > 0) {
                    $i = 1;
                    while ($resSubject = $exeSubject->fetch(PDO::FETCH_ASSOC)) {
                  ?>
                      <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo htmlentities($resSubject["course_id"]); ?></td>
                        <td><?php echo htmlentities($resSubject["course_name"]); ?></td>
                        <td><?php echo htmlentities($resSubject["subject_code"]); ?></td>
                      </tr>
                    <?php
                      $i++;
                    } // end of while
                  } // end of if condition
                  else {
                    ?>

                    <tr>
                      <td colspan="4" style="color:red; font-weight:bold; text-align:center;"> No Records found.!</td>
                    </tr>
                  <?php } ?>

                </table>
                <?PHP /* <div class="col-md-12">
                <div  class="col-md-10"></div>
                <div  class="col-md-2">
                  <input  class="btn btn-primary" type="submit" name="generateReport" value="Generate Report" style="margin: 2% 0%;padding: 5px 20px;background: #006899;
color: #fff;">
                </div>
              </div> <?PHP */ ?>
              </div>
            </form>
          </div>
        </fieldset>
      </div>
    </div>
  </div>
  <?php include("footer.php"); ?>
  <script type="text/javascript">
    function validate_discipline_details() {
      var searchGraduationType = $('#searchGraduationType').val();

      if (searchGraduationType == "") {
        var msg = 'Select Degree Type';
        $("#searchGraduationType").focus();
        $('#searchGraduationType').css('border-color', 'red');
        $('#searchGraduationType').css('box-shadow', '0 0 0.15rem crimson');
        message_error(msg);
        $('#searchGraduationType').val('');
        return false;
      } else {
        $('#searchGraduationType').css('border-color', '');
        $('#searchGraduationType').css('box-shadow', '');
      }


      $('#sel_discipline').submit();

    }
  </script>
</body>

</html>