<?php



ini_set('max_execution_time', '0'); // for infinite time of execution 


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

$category = $_SESSION["category"];
$encrypt_sessionid = $crypt->encrypt('2468', session_id(), 1);
$pagetoken = pg_escape_string(strip_tags(killChars($_POST["pagetoken"])));

/*	CURRENT DATE AND TIME */
$add_date = $_SESSION['access_date'] . " " . $_SESSION['access_time'];


if (isset($_POST['generateCsv']) && validateInputData($_POST['generateCsv'])) {

  // GENERATE REPORT IN CSV FORMAT 	BALA	//
  $filenames = "Reg_number_not_available_records_" . date('dmY') . ".csv";
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=' . $filenames . '');
  $output = fopen("php://output", "w");



  fputcsv($output, array('Sl No', 'INSTITUTION CODE', 'UNIV REG NO', 'SUBJECT CODE', 'SEMESTER'));




  $selStudent = "
SELECT DISTINCT subject_code::text, institution_code, univ_reg_no, semester FROM student_result.stud_result_0619 WHERE univ_reg_no||'/'||subject_code NOT IN (
SELECT a.univ_reg_no||'/'||subject_code FROM student_result.stud_result_0619 a JOIN inst.view_student_profile_ug_pg b on b.univ_reg_no=a.univ_reg_no AND b.course = a.course_id  WHERE a.delete_flg='0' AND result = 'P' AND b.student_status = 'A' 
) and result != 'P' AND delete_flg = '0' AND course_id::integer IN (select course_id FROM inst.mst_course WHERE graduation_id = '1' AND degree_id != '12') GROUP BY institution_code, univ_reg_no, subject_code, semester ORDER BY institution_code, univ_reg_no, semester, subject_code";



  //echo "A==".$selStudent; exit;
  $exeStudent = $db->query($selStudent);

  $i = 1;
  while ($resStudentDetails = $exeStudent->fetch(PDO::FETCH_ASSOC)) {

    $institution_code = strtoupper($resStudentDetails['institution_code']);
    $semester = $resStudentDetails['semester'];
    $univ_reg_no = strtoupper($resStudentDetails['univ_reg_no']);
    $subject_code = strtoupper($resStudentDetails['subject_code']);


    fputcsv($output,  array($i, $institution_code, $univ_reg_no, $subject_code, $semester));


    $i++;
  }
  fclose($output);
  exit;
}
?>

<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Bharathidasan University, Tiruchirapalli, Tamil Nadu, India.</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="images/favicon.ico" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style>
    label {
      font-size: 11px;
    }
  </style>
  <div id="loaderId" class="mainLoader" style="display:none">
    <div class="preloader" id="preLoaderId">
      <div class="innercircle">
        <h4 style="margin-top: 20px;">BDU</h4>
        NIC
      </div>
    </div>
  </div>

</head>

<body>
  <?php include("header.php"); ?>

  <?php
  if ($_SESSION["category"] == 'CE' || $_SESSION['user_id'] == 'COE') {
  ?>

    <div class="contact-w3-agileits master" id="contact">
      <div class="container">
        <h3 class="heading_style" style="font-size: 15px;">Student Registration Number Reports</h3>
      </div>
      <br>
      <h4>
        <label style="text-transform: uppercase;margin-bottom: 0px;border-bottom: 1px solid #e5e5e5; color:coral; font-size:15px;text-align:left"><b>Student registration number not assigned reports.</b></label>
      </h4>
      <br>
    </div>
    <div class="contact-w3-agileits master">
      <div class="container">
        <form class="well" id="practical_paper_fees_empty" name="practical_paper_fees_empty" action="#" method="post" enctype="multipart/form-data">
          <input type="hidden" id="pagetoken" name="pagetoken" value="<?php echo $_SESSION["pagetoken"]; ?>">
          <div class="container">
            <div class="col-md-12">
              <div class="col-md-5"></div>
              <div class="col-md-2">
                <input class="btn btn-primary" type="submit" name="generateCsv" value="Download CSV"
                  style="padding: 5px 20px;background: #006899; color: #fff;" onclick="downloadResultUploadVerification();">
              </div>
              <div class="col-md-5"></div>
            </div>
          </div>
        </form>
      </div>
    </div>
  <?php
  }

  ?>

  <!-- Result Content	End	-->

  <form name="downloadResultUploadVerificationList" id="downloadResultUploadVerificationList"

    <div class="footer">
    <?php include("footer.php"); ?>
    </div>
</body>

</html>