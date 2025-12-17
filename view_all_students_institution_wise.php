<?php
ini_set('memory_limit', '100000M');
ini_set('max_execution_time', 25000000);

//ini_set('max_execution_time', 0);	
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


/*****	AS GRADUATION UG / PG	*****/
$degreeCode = $_REQUEST['status'];



// GENERATE REPORT IN XL FORMAT 	BALA	//
/*if($_POST['downloadReport'])
{
	
	$degreeCode = $_POST['graduation'];
	
	
	if($_SESSION['category']=='CE')
	{
		$institution_code = $_POST['institution_code'];
	}
	else if($_SESSION['category']=='I')
	{
		$institution_code = $_SESSION['user_id'];
	}
	
	$degreeMulSelectVal = implode(',',$_POST['searchMultiple']);
	
	
	/*if($degreeCode=='1')
	{
		$semester = $_POST['ug_semester'];
	}
	else if($degreeCode=='2')
	{
		$semester = $_POST['pg_semester'];
	}*/
	
/*	
	$mode_of_study = $_POST['mode_of_study'];
	
	if($mode_of_study=='fulltime')
	{
		$modeOfStudy = " AND modeofstudy = 'fulltime'";
	}
	else if($mode_of_study=='parttime')
	{
		$modeOfStudy = " AND modeofstudy = 'parttime'";
	}
	
	
	
	$semester = $_POST['ug_semester'];
	
	$searchCondition =  "a.institution_code = '".$institution_code."' AND student_status!= 'D' and graduation='".$degreeCode."' and current_semester::integer='".$semester."'  ".$modeOfStudy."";
	
	/***	GETTING UG DEGREE STUDENTS SEARCH OPTIONS	**/
	/*if($degreeCode=='1')
	{
		$searchCondition =  "a.institution_code = '".$institution_code."' AND student_status!= 'D' and graduation='".$degreeCode."' and current_semester::integer='".$semester."'  ".$modeOfStudy."";
	}*/



/***	GETTING PG DEGREE STUDENTS SEARCH OPTIONS	**/

/*else  if($degreeCode=='2')
	{
		if($semester !="" && $degreeMulSelectVal!='')
		{
			$searchCondition =  "a.institution_code = '".$institution_code."' AND student_status!= 'D' and graduation='".$degreeCode."'  ".$modeOfStudy." AND degree in (".$degreeMulSelectVal.") and current_semester::integer='".$semester."'";
		}
		else 
		{
			$searchCondition =  "a.institution_code = '".$institution_code."' AND student_status!= 'D' and graduation='".$degreeCode."' and current_semester::integer='".$semester."'  ".$modeOfStudy."";
		}
	
	}*/


/*
$exeStudentDetails = $db->query("SELECT (select concat_ws(' - ',institution_code,institution_name) from inst.trn_institution_info where institution_code::character varying=college::character varying) as institution_code, stud_code, univ_reg_no, firstname, lastname, dob, 
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
CASE WHEN medium_of_instruction='1' THEN 'tamil' WHEN medium_of_instruction='2' THEN 'english' END  AS medium_of_instruction, 
CASE WHEN medium_of_school='T' THEN 'tamil' WHEN medium_of_school='2' THEN 'english' END  AS medium_of_school, 
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
WHERE $searchCondition
ORDER BY institution_code, graduation, degree, course, univ_reg_no, stud_code");	



	
	
	$sample_arr = array();
	
	$secArray = array();

		$i=1;
		while($resStudentDetails = $exeStudentDetails->fetch(PDO::FETCH_ASSOC))
		{
		
			$institution_code = strtoupper($resStudentDetails['institution_code']);
			$stud_code = $resStudentDetails['stud_code'];
			$univ_reg_no = strtoupper($resStudentDetails['univ_reg_no']);
			
			//$firstname = $resStudentDetails['firstname'];
			//$lastname = $resStudentDetails['lastname'];
			
			$getStudentName = trim(strtoupper($resStudentDetails['firstname'].' '.$resStudentDetails['lastname']));
			$var1 = trim(substr($getStudentName, 0, -1));
			$var2 = trim(substr($getStudentName, -1));
			
			$studentName = $var1.' '.$var2;
			
			
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
			//$academic_year = $resStudentDetails['academic_year'];
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
			
			
			$project_guide_reference_no = strtoupper($resStudentDetails['project_guide_reference_no']);
			$project_guide_name = $resStudentDetails['prefix'].' '.strtoupper($resStudentDetails['project_guide_name']);
			$designation = strtoupper($resStudentDetails['designation']);
			$department = strtoupper($resStudentDetails['department']);
			$project_guide_qualification = strtoupper($resStudentDetails['project_guide_qualification']);
			$project_guide_contact_number = strtoupper($resStudentDetails['project_guide_contact_number']);
			$project_guide_email = $resStudentDetails['project_guide_email'];
			$bank_account_number = strtoupper($resStudentDetails['bank_account_number']);
			$ifsc_code = strtoupper($resStudentDetails['ifsc_code']);
			
			
			$sample_arr['Sl. No'] = $i;
			$sample_arr['INSTITUTION CODE'] = $institution_code;
			$sample_arr['ENROLMENT NUMBER'] = $stud_code;
			
			$sample_arr['UNIV REG NO'] = $univ_reg_no;
			
			/* $sample_arr['STUDENT NAME'] = $firstname." ".$lastname; */
/*$sample_arr['STUDENT NAME'] = $studentName;

			$sample_arr['GRADUATION'] = $graduation;
			$sample_arr['DEGREE'] = $degree;
			$sample_arr['DISCIPLINE'] = $course;
			$sample_arr['BATCH'] = $batch;
			$sample_arr['MODE OF STUDY'] =  $modeofstudy;
			$sample_arr['EDUCATIONAL_INSTITUTION'] = $educational_institution;
			$sample_arr['SECTION'] = $section;
			$sample_arr['DATE OF ADMISSION'] = $date_of_admission;
			$sample_arr['COLLEGE ROLL NUMBER'] = $college_roll_number;
			$sample_arr['YEAR OF JOINING'] = $year_of_joining;
			//$sample_arr['ACADEMIC YEAR'] = $academic_year;
			$sample_arr['CURRENT SEMESTER'] = $current_semester;
			$sample_arr['REGULATION'] = $regulation;
			$sample_arr['MEDIUM OF INSTRUCTION'] = $medium_of_instruction;
			
			$sample_arr['BOARD IN SCHOOL'] = $medium_of_board;
			$sample_arr['MEDIUM OF SCHOOL'] = $medium_of_school;
			$sample_arr['UG DEGREE'] = $ug_degree;
			
			/* $sample_arr['DOB'] = $dob; */
/*$sample_arr['DATE'] = $dobDate;
			$sample_arr['MONTH'] = $dobMonth;
			$sample_arr['YEAR'] = $dobYear;
			$sample_arr['GENDER'] = $gender;
			$sample_arr['ADHAAR'] = $adhaar;
			$sample_arr['EMAIL'] = $email;
			$sample_arr['PHYCHALLENGE'] = $phychallenge;
			$sample_arr['VISUALLY CHALLENGED'] = $visually_challenged;
			$sample_arr['MOTHERTONGUE'] = $mothertongue;
			$sample_arr['MARITAL STATUS'] = $marital_status;
			$sample_arr['FATHERNAME'] = $fathername;
			$sample_arr['MOTHERNAME'] = $mothername;
			$sample_arr['OCCUPATION'] = $occupation;
			$sample_arr['NATIONALITY'] = $nationality;
			$sample_arr['RELIGION'] = $religion;
			$sample_arr['COMMUNITY'] = $community;
			$sample_arr['CASTE'] = $caste;
			$sample_arr['COUNTRY'] = $country;
			$sample_arr['STATE'] = $state;
			$sample_arr['DISTRICT'] = $district;
			$sample_arr['CITY'] = $city;
			$sample_arr['ADDRESS'] = $address;
			$sample_arr['PINCODE'] = $pincode;
			$sample_arr['MOBILE NO'] = $mobile_no;
			
			if($degreeMulSelectVal=="'12'"){
				$sample_arr['GUIDE REFERENCE NUMBER'] = $project_guide_reference_no;
				$sample_arr['GUIDE NAME'] = $project_guide_name;
				$sample_arr['GUIDE QUALIFICATION'] = $project_guide_qualification;
				$sample_arr['GUIDE DESIGNATION'] = $designation;
				$sample_arr['GUIDE DEPARTMENT'] = $department;
				$sample_arr['GUIDE BANK ACCOUNT NUMBER'] = $bank_account_number;
				$sample_arr['GUIDE IFSC CODE'] = $ifsc_code;
				$sample_arr['CONTACT NUMBER'] = $project_guide_contact_number;
				$sample_arr['GUIDE EMAIL'] = $project_guide_email;
				}
				
			$sample_arr['DEGREE ID'] = $degree_id;
			$sample_arr['COURSE ID'] = $course_id;
		
		$secArray[$i] = $sample_arr;
		
		$i++; 
		 
		 }	
		
	function filterData(&$str)
	{
		$str = preg_replace("/\t/", "\\t", $str);
		$str = preg_replace("/\r?\n/", "\\n", $str);
		if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
	}
	
	// FILE NAME FOR DOWNLOAD
	$fileName = "Report_".date('Ymd').".xls";
	
	// HEADERS FOR DOWNLOAD
	header("Content-Disposition: attachment; filename=\"$fileName\"");
	header("Content-Type: application/vnd.ms-excel");
	
	$flag = false;
	foreach($secArray as $row) 
	{
		if(!$flag) 
		{
			// DISPLAY COLUMN NAME
			echo implode("\t", array_keys($row)) . "\n";
			$flag = true;
		}
		// ALL DATA
		
		array_walk($row, 'filterData');
		echo implode("\t", array_values($row)) . "\n";
	
	}
	
	exit;
}*/


?>


<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title>Bharathidasan University, Tiruchirapalli, Tamil Nadu, India.</title>

	<!-- Favicon -->
	<link rel="shortcut icon" href="images/favicon.ico" />


	<style>
		@media all and (max-width: 599px) and (min-width: 320px) {
			h2 {
				margin: 12px 0px;
				font-size: 18px;
			}

			h3 {
				font-size: 19px;
			}

			h4 {
				font-size: 10px;
				margin-top: 1px;
			}

			.slideshow img {
				height: 195px;
			}
		}


		.buttonSubmit,
		.buttonSubmitRoll {

			background: #00BCD4;
			border: none;
			padding: 10px 15px;
			color: #fff;
			font-size: 14px;
			outline: none;
			margin-top: 30px;

		}
	</style>

</head>

<body>


	<?php include("header.php"); ?>

	<script>
		/*	SCRIPT FOR LOAD DEGREE	*/
		/* GETTING DEGREE BASED ON SELECTED AFFILIATION		*/
		$(document).ready(function() {
			$("#graduation").change(function() {


				var user_id = '<?php echo $_SESSION['category']; ?>';

				if (user_id == 'CE') {
					var institution = $("#institution_code").val();
				} else {
					var institution = '<?php echo $_SESSION['user_id']; ?>';
				}



				var degree_type = $('#graduation').val();

				$("#loaderId").show();
				$.ajax({
					type: "POST",
					url: "ajax_get_degree_course_master.php",
					data: {
						degree_type: degree_type,
						institution: institution,
						type: 'getDegreeDetails'
					},
					cache: false,
					success: function(data) {
						$('#loaderId').delay(200).fadeOut('slow');
						//alert(data);
						$("#degree").html(data);
					}
				});
			});
		});


		$(document).ready(function() {

			$("#degree").change(function() {

				var degree_type = $('#graduation').val();

				$.ajax({
					type: "POST",
					url: "ajax_get_degree_course_master.php",
					data: {
						degree_type: degree_type,
						type: 'getExamIdActiveSemesterDetails'
					},
					cache: false,
					success: function(data) {
						//alert(data);
						if (data != '0') {
							$("#semester").html(data);
						} else {
							var msg = 'Failed..!';
							message_error(msg);
							return false;
						}
					}
				});
			});
		});
	</script>

	<script>
		/*$(document).ready(function() {
$('.mdb-select').materialSelect();
});

*/
	</script>

	<!--	DOB DIV =====  DOB CORRECTION	-->
	<style>
		.ui-widget-header {
			border: 1px solid #006899;
			background: none;
			background-color: #006899;
			color: #000000;
		}
	</style>


	<!--	SCRIPT FOR DATE PICKER	END		-->
	<div class="contact-w3-agileits master">
		<div class="container">
			<h3 class="heading_style" style="font-size: 15px;">Download All Students - Institution Wise</h3>
		</div>

		<div class="container">
			<form class="well form-horizontal needs-validation" id="profileDetails" name="profileDetails" action="#" method="post"
				style="min-height: 85px;">
				<input type="hidden" id="pagetoken" name="pagetoken" value="<?php echo $_SESSION["pagetoken"]; ?>">

				<input type="hidden" id="academic_year" name="academic_year" value="<?php echo $academic_year; ?>">


				<?php if ($category == 'CE') { ?>

					<div class="col-md-12 textbox_width">

						<label class="col-md-1 control-label">Institution<sup>*</sup></label>
						<select class="form-control" name="institution_code" id="institution_code">
							<option value="">Select Institution</option>
							<?php

							/* LOAD INSTITUTION */
							$selInstitution = "SELECT * FROM inst.trn_institution_info WHERE institution_code not in ('9999', '0040') ORDER BY institution_code ASC";
							$exeInstitution = $db->query($selInstitution);

							if ($exeInstitution->rowCount() > 0) {
								while ($resInstitution = $exeInstitution->fetch(PDO::FETCH_ASSOC)) {
									$inst_name = '';

									$bdu_city = '';

									$bdu_district = '';

									$bdu_pincode = '';

									$inst_name = $resInstitution["institution_name"];

									if ($resInstitution["bdu_city"] != '') {
										$bdu_city = $resInstitution["bdu_city"] . ", ";
									}

									if ($resInstitution["bdu_district"] != '') {
										$bdu_district = $resInstitution["bdu_district"] . ", ";
									}

									if ($resInstitution["pincode"] != '') {
										$bdu_pincode = $resInstitution["pincode"] . ".";
									}

									$institution_name = $inst_name . ', ' . $bdu_city . ' ' . $bdu_district . ' ' . $bdu_pincode;

							?>
									<option value=<?php echo $resInstitution['institution_code'] ?> <?php echo $selected; ?>>
										<?php echo $resInstitution['institution_code'] . " - " . $institution_name; ?> </option>
							<?php
								}
							}
							?>
						</select>

					</div>


					<br><br><br><br>

				<?php
				}
				?>



				<div class="col-md-12">
					<?php
					//if($degreeCode=='1')
					//{
					//$selGraduation = "SELECT * FROM inst.mst_graduation WHERE graduation_id='".$degreeCode."' ORDER BY graduation_id";

					//echo $_SESSION['clg_cat'];

					/* LOAD GRADUATION */

					/* if ($_SESSION['clg_cat'] != 'AF') {
						$graduation_id = " WHERE graduation_id = '" . $_SESSION['clg_cat'] . "'";
					} */

					$selGraduation = "SELECT * FROM inst.mst_graduation $graduation_id ORDER BY graduation_id ASC";
					$exeGraduation = $db->query($selGraduation);

					?>

					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-5 control-label">Degree Type<sup>*</sup></label>
							<div class="col-md-7 inputGroupContainer">
								<select class="form-control" name="graduation" id="graduation">
									<option value="">Select</option>
									<?php while ($resGraduation = $exeGraduation->fetch(PDO::FETCH_ASSOC)) { ?>

										<option value="<?php echo $resGraduation['graduation_id']; ?>"><?php echo $resGraduation['graduation_name']; ?></option>

									<?php } ?>
								</select>
							</div>
						</div>
					</div>


					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-5 control-label">Degree<sup>*</sup></label>
							<div class="col-md-7 inputGroupContainer">
								<select class="form-control" name="degree" id="degree">
									<option value="">Select</option>
								</select>
							</div>
						</div>
					</div>



					<?php
					//}  
					/*if($degreeCode=='2')
	{
		
		$selDegree = "SELECT * FROM inst.mst_degree WHERE graduation_id = '".$degreeCode."'";
		$exeDegree = $db->query($selDegree);
		$i=1;
		?> 
          <div class="col-md-4">
            <div class="form-group">
              <label class="col-md-5 control-label">Select Degree<sup>*</sup></label>
              <div class="col-md-7 inputGroupContainer">
                    <select class="mdb-select md-form" multiple id="searchMultiple" name="searchMultiple[]">
                    <option value="" disabled selected>Choose Degree</option>
                    <?php 
					
					while($resDegree = $exeDegree->fetch(PDO::FETCH_ASSOC)) { ?>
                    
                    <option value="'<?php echo $resDegree['degree_id']; ?>'"><?php echo $resDegree['degree_short_code']; ?></option>
                   
                    <?php  $i++; } ?>
                    </select>
              </div>
            </div>
          </div>
         
  <?php
		} */
					?>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-5 control-label">Semester<sup>*</sup></label>
							<div class="col-md-7 inputGroupContainer">
								<div class="input-group">
									<select class="form-control" width="100%" name="semester" id="semester">
										<option value="">Select Semester</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4" style="display:none;">
						<div class="form-group">
							<label class="col-md-5 control-label">Mode of Study</label>
							<div class="col-md-7 inputGroupContainer">
								<div class="input-group">
									<select class="form-control" name="mode_of_study" id="mode_of_study">
										<option value="">Select Mode Of Study</option>
										<option value="fulltime">Full Time</option>
										<option value="parttime">Part Time</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="contact-w3-agileits master" id="contact">
					<div class="container">


						<div class="col-md-12 textbox_width" id="display_institution" style="margin-top:2%;">
							<div class="col-md-4"></div>



							<?php /*
	
	<?php if($_SESSION['category']=='CE') { ?>
	
	
	
    <div class="col-md-2" style="text-align:center;">
      <input class="btn btn-primary" type="button" name="generateXLCsv" value="Download Excel" 
				style="padding: 5px 20px;background: #006899; color: #fff;" onclick="validateExcelCsvReport(1);">
      </div>
	  
    <?php } ?>  
	
	*/ ?>

							<div class="col-md-4" style="text-align:center;">
								<input class="btn btn-primary" type="button" name="generateXLCsv" value="Download CSV"
									style="padding: 5px 20px;background: #006899; color: #fff;" onclick="validateExcelCsvReport(2);">
							</div>


							<div class="col-md-4"></div>

						</div>
					</div>
				</div>
			</form>

		</div>

		<form name="downloadStudentProfileList" id="downloadStudentProfileList" method="post" action="coe_student_profile_excel_csv.php" target="_blank">
			<input type="hidden" id="pagetoken" name="pagetoken" value="<?php echo htmlentities($_SESSION["pagetoken"]); ?>">
			<input type="hidden" id="view_institution" name="view_institution">
			<input type="hidden" id="view_graduation" name="view_graduation">
			<input type="hidden" id="view_degree" name="view_degree">
			<input type="hidden" id="view_semester" name="view_semester">
			<input type="hidden" id="view_mode_of_study" name="view_mode_of_study">
			<input type="hidden" id="downloadType" name="downloadType">
			<!--<input type="hidden" id="view_searchMultiple" name="view_searchMultiple">-->
		</form>



		<?php include("footer.php"); ?>



		<script type="text/javascript">
			function validateExcelCsvReport(a) {

				var user_id = '<?php echo $_SESSION['category']; ?>';

				//var degreeCode = '<?php echo  $degreeCode; ?>';

				if (user_id == 'CE') {
					var institution_code = $("#institution_code").val();
				} else {
					var institution_code = '<?php echo $_SESSION['user_id']; ?>';
				}


				var graduation = $("#graduation").val();
				var degree = $("#degree").val();


				/*}else if(degreeCode=='2'){
					var graduation  = '2';
					var searchMultiple=document.getElementsByName('searchMultiple[]');
				}*/


				var semester = $("#semester").val();
				var modeofstudy = $("#mode_of_study").val();

				if (institution_code == "") {
					var msg = 'Select Institution Code';
					$("#institution_code").val('');
					$("#institution_code").focus();
					$('#institution_code').css('border-color', 'red');
					$('#institution_code').css('box-shadow', '0 0 0.15rem crimson');
					message_error(msg);
					return false;
				} else {
					$('#institution_code').css('border-color', '');
					$('#institution_code').css('box-shadow', '');
				}


				if (graduation == "") {
					var msg = 'Select Graduation';
					$("#graduation").val('');
					$("#graduation").focus();
					$('#graduation').css('border-color', 'red');
					$('#graduation').css('box-shadow', '0 0 0.15rem crimson');
					message_error(msg);
					return false;
				} else {
					$('#graduation').css('border-color', '');
					$('#graduation').css('box-shadow', '');
				}

				if (degree == "") {
					var msg = 'Select degree';
					$("#degree").val('');
					$("#degree").focus();
					$('#degree').css('border-color', 'red');
					$('#degree').css('box-shadow', '0 0 0.15rem crimson');
					message_error(msg);
					return false;
				} else {
					$('#degree').css('border-color', '');
					$('#degree').css('box-shadow', '');
				}


				/*if(degreeCode=='2'){
					for(key=0; key < searchMultiple.length; key++)  {
							if((searchMultiple[key].value).trim()=="")
							{
								alert('Degree Should Not be Empty..!!');
								$(searchMultiple[key]).focus();
								$(searchMultiple[key]).css('border-color', 'red');
								$(searchMultiple[key]).css('box-shadow', '0 0 0.15rem crimson');
								message_error(msg);
								return false;
							}else{
								$(searchMultiple[key]).css('border-color', '');
								$(searchMultiple[key]).css('box-shadow', '');
								}
						}
						
					}
					
					
					
					searchMultipleArray = [];
					$("#searchMultiple :selected").each(function() {
				            //alert(this.value);
							searchMultipleArray.push($(this).val());
				        });*/

				if (semester == "") {
					var msg = 'Select Semester';
					$("#semester").val('');
					$("#semester").focus();
					$('#semester').css('border-color', 'red');
					$('#semester').css('box-shadow', '0 0 0.15rem crimson');
					message_error(msg);
					return false;
				} else {
					$('#semester').css('border-color', '');
					$('#semester').css('box-shadow', '');
				}


				//document.getElementById("view_searchMultiple").value = searchMultipleArray;
				document.getElementById("view_institution").value = institution_code;
				document.getElementById("view_graduation").value = graduation;
				document.getElementById("view_degree").value = degree;
				document.getElementById("view_semester").value = semester;
				document.getElementById("view_mode_of_study").value = modeofstudy;
				if (a == 1) {
					document.getElementById("downloadType").value = 'xls';
				} else if (a == 2) {
					document.getElementById("downloadType").value = 'csv';
				}
				$("#downloadStudentProfileList").submit();
			}
		</script>



		<script src="js/xls_download_script.js" type="text/javascript"></script>
		<script type="text/javascript">
			function Export() {
				$("#auditTrailData").table2excel({
					filename: "students_data.xls"
				});
			}
		</script>




</body>

</html>