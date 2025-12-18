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

if (isset($_POST['generateCsv']) && validateInputData($_POST['generateCsv'])) {


	// GENERATE REPORT IN CSV FORMAT 	BALA	//
	$filenames = "Reg_number_not_available_records_" . date('dmY') . ".csv";
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=' . $filenames . '');
	$output = fopen("php://output", "w");



	fputcsv($output, array(
		'Sl No',
		'INSTITUTION CODE',
		'ENROLMENT NUMBER',
		'UNIV REG NO',
		'STUDENT NAME',
		'GRADUATION',
		'DEGREE',
		'DISCIPLINE',
		'BATCH',
		'MODE OF STUDY',
		'EDUCATIONAL_INSTITUTION',
		'SECTION',
		'DATE OF ADMISSION',
		' COLLEGE ROLL NUMBER',
		'YEAR OF JOINING',
		'CURRENT SEMESTER',
		'REGULATION',
		'MEDIUM OF INSTRUCTION',
		'BOARD IN SCHOOL',
		'MEDIUM OF SCHOOL',
		'UG DEGREE',
		'DATE',
		'MONTH',
		'YEAR',
		'GENDER',
		' ADHAAR',
		'EMAIL',
		'PHYCHALLENGE',
		'VISUALLY CHALLENGED',
		'MOTHERTONGUE',
		'MARITAL STATUS',
		'FATHERNAME',
		'MOTHERNAME',
		'OCCUPATION',
		'NATIONALITY',
		'RELIGION',
		'COMMUNITY',
		'CASTE',
		'COUNTRY',
		'STATE',
		'DISTRICT',
		'CITY',
		'ADDRESS',
		'PINCODE',
		'MOBILE NO',
		'DEGREE ID',
		'COURSE ID'
	));




	$selStudent = "SELECT (select concat_ws(' - ',institution_code,institution_name) from inst.trn_institution_info where institution_code::character varying=college::character varying) as institution_code, stud_code, univ_reg_no, firstname, lastname, dob, 
(select gender_name from inst.mst_gender where gender::text=gender_id::text) as gender, adhaar, email, phychallenge, 
(SELECT mothertongue_name FROM inst.mst_mothertongue where mothertongue::text= mothertongue_id::text) as mothertongue, fathername,  mothername, 
(select occupation_name from inst.mst_occupation where occupation::text=occupation_id::text) as occupation, 
CASE WHEN nationality='1' THEN 'indian' WHEN nationality='2' THEN 'others' END  AS nationality, 
(SELECT religion_name FROM inst.mst_religion where religion::text= religion_id::text) as religion, 
(select community_code from inst.mst_community where community::text=community_id::text) as community ,
(select caste_name from inst.mst_caste where caste_id::text=caste::text) as caste ,
(SELECT graduation_name FROM inst.mst_graduation where graduation::text=graduation_id::text) as graduation, degree AS degree_id,
(SELECT concat_ws(' - ',degree_short_code,degree_name) FROM inst.mst_degree where degree::text=degree_id::text) as degree,
modeofstudy, address,city, 
(SELECT country_name FROM inst.mst_country where country::text=country_id::text) as country, 
(SELECT state_name FROM inst.mst_state where state::text=state_id::text) as state,
(SELECT district_name FROM inst.mst_district where district::text=district_id::text) as district, 
pincode, mobile_no, year_of_joining, academic_year, course AS course_id,
(select course_short_name from inst.mst_course where course::text=course_id::text) as course, 
current_semester, regulation,  
student_pass_flag, 
CASE WHEN medium_of_instruction='2' THEN 'tamil' WHEN medium_of_instruction='1' THEN 'english' END  AS medium_of_instruction, 
CASE WHEN medium_of_school='T' THEN 'tamil' WHEN medium_of_school='2' THEN 'english' END  AS medium_of_school, 
CASE WHEN medium_of_board='CB' THEN 'Central Board' WHEN medium_of_board='SB' THEN 'State Board' END  AS medium_of_board, 
CASE WHEN ug_degree='1' THEN 'BARD' WHEN ug_degree='2' THEN 'Others' END  AS ug_degree, 
CASE WHEN marital_status='1' THEN 'Married' WHEN marital_status='2' THEN 'Single' WHEN marital_status='3' THEN 'Widowed / Divorced' END  AS marital_status, 
CASE WHEN batch='E' THEN 'Evening' WHEN batch='M' THEN 'Day' END  AS batch, 
modeofstudy,  
CASE WHEN educational_institution='A' THEN 'Govt./Aided' WHEN educational_institution='S' THEN 'Self-Financing' END  AS educational_institution, 
section, date_of_admission, college_roll_number, visually_challenged, student_type,
b.project_guide_reference_no, (SELECT prefix FROM inst.mst_prefix where id::text= prefix_id::text) as prefix,
b.prefix_id, b.project_guide_name, b.project_guide_qualification, 
(SELECT designation_name FROM inst.mst_designation where designation_id::text= project_guide_designation_id::text) as designation,
b.project_guide_designation_id, 
(SELECT department_name FROM inst.mst_department where department_code::text= project_guide_department_id::text) as department,
b.project_guide_department_id, b.project_guide_contact_number, 
b.project_guide_email, guide_status, b.bank_account_number, b.ifsc_code
FROM inst.view_student_profile_ug_pg a
LEFT JOIN 
inst.trn_project_guide_details b ON a.stud_code::text = b.student_code::text
WHERE student_status != 'D' AND  current_semester = '01' AND univ_reg_no IS NULL AND year_of_joining = '" . $academic_year . "'
ORDER BY institution_code, graduation, degree, course, univ_reg_no, stud_code";


	//echo "A==".$selStudent; exit;
	$exeStudent = $db->query($selStudent);

	$i = 1;
	while ($resStudentDetails = $exeStudent->fetch(PDO::FETCH_ASSOC)) {

		$institution_code = strtoupper($resStudentDetails['institution_code']);
		$stud_code = $resStudentDetails['stud_code'];
		$univ_reg_no = strtoupper($resStudentDetails['univ_reg_no']);

		$getStudentName = trim(strtoupper($resStudentDetails['firstname'] . ' ' . $resStudentDetails['lastname']));
		$var1 = trim(substr($getStudentName, 0, -1));
		$var2 = trim(substr($getStudentName, -1));

		$studentName = $var1 . ' ' . $var2;


		$dob = explode('-', $resStudentDetails['dob']);
		$dobDate = $dob[0];
		$dobMonth = $dob[1];
		$dobYear = $dob[2];


		$gender = strtoupper($resStudentDetails['gender']);
		$adhaar = $resStudentDetails['adhaar'];
		$email = strtolower($resStudentDetails['email']);
		$phychallenge = strtoupper($resStudentDetails['phychallenge']);
		$mothertongue = strtoupper($resStudentDetails['mothertongue']);
		$fathername = strtoupper($resStudentDetails['fathername']);
		$mothername = strtoupper($resStudentDetails['mothername']);
		$occupation = strtoupper($resStudentDetails['occupation']);
		$nationality = strtoupper($resStudentDetails['nationality']);
		$religion = strtoupper($resStudentDetails['religion']);
		$community = strtoupper($resStudentDetails['community']);
		$caste = strtoupper($resStudentDetails['caste']);
		$graduation = strtoupper($resStudentDetails['graduation']);
		$degree = strtoupper($resStudentDetails['degree']);
		$modeofstudy = strtoupper($resStudentDetails['modeofstudy']);
		$city = strtoupper($resStudentDetails['city']);
		$country = strtoupper($resStudentDetails['country']);
		$state = strtoupper($resStudentDetails['state']);
		$district = strtoupper($resStudentDetails['district']);
		$pincode = $resStudentDetails['pincode'];
		$mobile_no = $resStudentDetails['mobile_no'];
		$year_of_joining = $resStudentDetails['year_of_joining'];

		$course = strtoupper($resStudentDetails['course']);
		$current_semester = $resStudentDetails['current_semester'];
		$regulation = $resStudentDetails['regulation'];
		$student_pass_flag = $resStudentDetails['student_pass_flag'];
		$medium_of_instruction = strtoupper($resStudentDetails['medium_of_instruction']);



		$medium_of_school = strtoupper($resStudentDetails['medium_of_school']);
		$medium_of_board = strtoupper($resStudentDetails['medium_of_board']);
		$ug_degree = strtoupper($resStudentDetails['ug_degree']);



		$marital_status = strtoupper($resStudentDetails['marital_status']);
		$batch = strtoupper($resStudentDetails['batch']);
		$modeofstudy = strtoupper($resStudentDetails['modeofstudy']);
		$educational_institution = strtoupper($resStudentDetails['educational_institution']);
		$section = $resStudentDetails['section'];
		$date_of_admission = $resStudentDetails['date_of_admission'];
		$college_roll_number = strtoupper($resStudentDetails['college_roll_number']);
		$visually_challenged = strtoupper($resStudentDetails['visually_challenged']);
		$student_type = strtoupper($resStudentDetails['student_type']);
		$address = strtoupper($resStudentDetails['address']);
		$degree_id = $resStudentDetails['degree_id'];
		$course_id = $resStudentDetails['course_id'];


		fputcsv($output,  array($i, $institution_code, $stud_code,  $univ_reg_no, $studentName, $graduation, $degree, $course, $batch, $modeofstudy, $educational_institution, $section, $date_of_admission, $college_roll_number, $year_of_joining, $current_semester, $regulation, $medium_of_instruction, $medium_of_board, $medium_of_school, $ug_degree, $dobDate, $dobMonth, $dobYear, $gender, $adhaar, $email, $phychallenge, $visually_challenged, $mothertongue, $marital_status, $fathername, $mothername, $occupation, $nationality, $religion, $community, $caste, $country, $state, $district, $city, $address, $pincode, $mobile_no,  $degree_id, $course_id));


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
	if (validateInputData($_SESSION["category"]) == 'CE' || validateInputData($_SESSION['user_id']) == 'COE') {
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

	<?php include("footer.php"); ?>
</body>

</html>