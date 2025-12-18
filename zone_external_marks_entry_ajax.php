<?php
error_reporting(E_ALL);
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



<?php

$db->query("BEGIN WORK");


if (isset($_POST['type']) && $_POST['type'] == 'getExaminerDetails') {
	if ($_POST['pagetoken'] == $_SESSION["pagetoken"]) {
		$chiefId = pg_escape_string(strip_tags(trim($_POST['chiefId'])));
		$barCode = pg_escape_string(strip_tags(trim(strtoupper($_POST['barCode']))));

		$selExaminerList = "SELECT *, (SELECT examiner_name FROM inst.iml_examiner_details b WHERE b.examiner_id::integer=a.examiner_id::integer) AS examiner_name, (SELECT mobile FROM inst.iml_examiner_details b WHERE b.examiner_id::integer=a.examiner_id::integer) AS mobile FROM inst.iml_chief_allotment_examiner_details a
		WHERE exam_id='" . $exam_id . "' 
		AND chief_id = '" . $chiefId . "'  ORDER BY examiner_name";


		$selExaminerId = "SELECT DISTINCT examiner_id FROM inst.trn_student_iml_external_mark
		WHERE secret_code = '" . $barCode . "' 
		AND exam_type = '" . $exam_id . "' 
		AND chief_name = '" . $chiefId . "'
		AND delete_flag = '0'";
		$exeExaminerId = $db->query($selExaminerId);
		$resExaminerId = $exeExaminerId->fetch(PDO::FETCH_ASSOC);
		$examinerPostId = $resExaminerId['examiner_id'];


		$exeExaminerList = $db->query($selExaminerList);

		echo '<option value="">Select Examiner</option>';
		if ($exeExaminerList->rowCount() > 0) {
			while ($resExaminerList = $exeExaminerList->fetch(PDO::FETCH_ASSOC)) {
				$selected = "";
				if ($examinerPostId == $resExaminerList['examiner_id']) {
					$selected = 'selected';
				}

				echo '<option value="' . $resExaminerList['examiner_id'] . '" ' . $selected . '>' . $resExaminerList['examiner_name'] . ' - ' . $resExaminerList['mobile'] . '</option>';
			}
		}
	}
}



/*	DISPLAY INSTITUTION REPORT		*/
if (isset($_POST['type']) && $_POST['type'] == 'qrBasedUEEntry') {
	if (isset($_POST['data']) && $_POST['data'] == 'qrBasedUEEntryData') {
		if ($_POST['pagetoken'] == $_SESSION["pagetoken"]) {
			$pagetoken = $_POST['pagetoken'];
			$qr_code = pg_escape_string(strip_tags(trim(strtoupper($_POST['bar_code']))));
			$allowZoneExternalMarksEntry = pg_escape_string(strip_tags(trim($_POST['allowZoneExternalMarksEntry'])));



			/* QUERY FOR DISPLAY REPORT BUTTON */
			$selExtMarkEntryCnt = "SELECT examiner_id, chief_name, packet_number, COUNT(exam_type) as row_cnt FROM inst.trn_student_iml_external_mark 
			WHERE exam_type = '" . $_SESSION['exam_id'] . "'
			AND secret_code = '" . $qr_code . "'
			AND academic_year='" . $academic_year . "' 
			AND delete_flag='0'
			GROUP BY examiner_id, chief_name, packet_number";

			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			try {

				//echo "A====".$selExtMarkEntryCnt;exit;

				//your code/query
				$exeExtMarkEntryCnt = $db->query($selExtMarkEntryCnt);
			} catch (PDOException $e) {
				//Do your error handling here
				$message = $e->getMessage();
				echo "POD ERROR1 <br>" . $message . "<br><br>";
			}
			$resExtMarkEntryCnt = $exeExtMarkEntryCnt->fetch(PDO::FETCH_ASSOC);
			$row_cnt = $resExtMarkEntryCnt['row_cnt'];
			$examiner_id = $resExtMarkEntryCnt['examiner_id'];
			$chiefId = $resExtMarkEntryCnt['chief_name'];
			$packet_number = $resExtMarkEntryCnt['packet_number'];

			/* END */



			/* 	SELECT QUERY FOR GETTING STUDENT DETAILS BASED ON THE QR CODE 	*/
			$selQuery = "SELECT DISTINCT subject_code, course_code FROM inst.trn_student_cover_slip_barcode
			WHERE exam_id='" . $_SESSION['exam_id'] . "' 
			AND secret_code='" . trim($qr_code) . "' 
			AND delete_flag = '0' ";

			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			try {
				//your code/query
				//echo "A====".$selQuery;exit;
				$exeQuery = $db->query($selQuery);
			} catch (PDOException $e) {
				//Do your error handling here
				$message = $e->getMessage();
				echo "POD ERROR1 <br>" . $message . "<br><br>";
			}

			$resQuery = $exeQuery->fetch(PDO::FETCH_ASSOC);
			$qrCodeSubCode = $resQuery['subject_code'];
			$course_code = $resQuery['course_code'];





			/* SELECT COURSE ID FROM COURSE SUBJECT CODE TABLE - FROM UPLOAD FILE START */

			$selCourseId = "SELECT  * FROM inst.iml_course_subject_code 
			WHERE exam_id='" . $exam_id . "' AND subject_code = '" . $qrCodeSubCode . "'";

			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			try {
				//your code/query
				//echo "A====".$selCourseId;exit;
				$exeCourseId = $db->query($selCourseId);
			} catch (PDOException $e) {
				//Do your error handling here
				$message = $e->getMessage();
				echo "POD ERROR1 <br>" . $message . "<br><br>";
			}

			$resCourseId = $exeCourseId->fetch(PDO::FETCH_ASSOC);
			$matched_subject_code = $resCourseId['subject_code'];
			$matched_course_code = $resCourseId['course_id'];



			// UNIT SRI BHARATHI PAPER CODE 22PELLS1 AND 22PELLS1 EXAMINNER COMES UNDER BOTANY DEPARTMENT.

			if (validateInputData($_SESSION['venueId']) == '59') {
				if ($matched_subject_code == '22PELLS1' || $matched_subject_code == '22PELLS2') {
					$matched_course_code = '40';
				}

				if ($matched_subject_code == '22PELPS1' || $matched_subject_code == '22PELPS2') {
					$matched_course_code = '55';
				}
			}



			if (validateInputData($_SESSION['venueId']) == '57') {

				// UNIT JAMAL MOHAMED PAPER CODE 22PELAS1 AND 22PELAS2 EXAMINNER COMES UNDER HISTORY DEPARTMENT.
				/* if ($matched_subject_code == '22PELAS1' || $matched_subject_code == '22PELAS2') {
					$matched_course_code = '8';
				} */

				// UNIT JAMAL MOHAMED PAPER CODE 22PELAS1 AND 22PELAS2 EXAMINNER COMES UNDER ELECTRONICS DEPARTMENT.
				if ($matched_subject_code == '22PELAS1' || $matched_subject_code == '22PELAS2') {
					$matched_course_code = '43';
				}
			}



			if (validateInputData($_SESSION['venueId']) == '62') {

				// UNIT - I, GOVERNMENT ARTS COLLEGE (AUTO.), KUMBAKONAM.
				// PAPER CODE 22PELAS1 AND 22PELAS2 EXAMINNER COMES UNDER ELECTRONICS DEPARTMENT.
				if ($matched_subject_code == '22PELPS1' || $matched_subject_code == '22PELPS2') {
					$matched_course_code = '42';
				}
			}



			if (validateInputData($_SESSION['venueId']) == '60') {

				// UNIT - I, BHARATH COLLEGE OF SCIENCE AND MANAGEMENT, THANJAVUR.
				// PAPER CODE 22PELAS1 AND 22PELAS2 EXAMINNER COMES UNDER ELECTRONICS DEPARTMENT.
				if ($matched_subject_code == '22PELPS1' || $matched_subject_code == '22PELPS2') {
					$matched_course_code = '60';
				}
			}


			if (validateInputData($_SESSION['venueId']) == '63') {

				// UNIT - I, BHARATH COLLEGE OF SCIENCE AND MANAGEMENT, THANJAVUR.
				// PAPER CODE 22PELLS1 AND 22PELLS2 EXAMINNER COMES UNDER ZOOLOGY DEPARTMENT.
				if ($matched_subject_code == '22PELLS1' || $matched_subject_code == '22PELLS2') {
					$matched_course_code = '68';
				}
			}







			/*********************** END ***************************/


			/* SELECT SUBJECT DETAILS */
			$selSubjectDetails = "SELECT degree_short_code, course_id, course_name, course_short_name,  semester, regulation, part, subject_type_code_univ, subject_type_code, subject_group_id, subject_id, subject_code, subject_name, internal_assessment_min, internal_assessment_max, university_exam_min, university_exam_max,  type_theory_or_practical FROM inst.view_course_subject_details WHERE subject_code = '" . $qrCodeSubCode . "' AND subject_common_deleted_date IS NULL AND display_status='Y'  LIMIT 1";

			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			try {
				//your code/query
				//echo "A====".$selSubjectDetails;exit;
				$exeSubjectDetails = $db->query($selSubjectDetails);
			} catch (PDOException $e) {
				//Do your error handling here
				$message = $e->getMessage();
				echo "POD ERROR1 <br>" . $message . "<br><br>";
			}

			$resSubjectDetails = $exeSubjectDetails->fetch(PDO::FETCH_ASSOC);
			$qrCodeDegreeCode = $resSubjectDetails['degree_short_code'];
			$qrCodeCourseCode = $resSubjectDetails['course_short_name'];
			$qrCodeSubCode = $resSubjectDetails['subject_code'];
			$qrCodeSubName = $resSubjectDetails['subject_name'];
			$qrCodeSubCodeMinMark = $resSubjectDetails['university_exam_min'];
			$qrCodeSubCodeMaxMark = $resSubjectDetails['university_exam_max'];
			$exam = '1';


			$quote = "'";

			echo '<div class="container-fluid">
				   <form name="internal_mark" id="internal_mark" method="post" action="#" autocomplete="off">
					<input type="hidden" id="pagetoken" name="pagetoken" value=' . $pagetoken . '>
					<input type="hidden" id="qr_code" name="qr_code" value=' . $qr_code . '>
					<input type="hidden" name="externalAcademicYear" id="externalAcademicYear" value=' . $academic_year . '>';


			$selExaminerDetails = "SELECT * FROM inst.iml_examiner_details 
					WHERE exam_id='" . $exam_id . "' 
					AND course_id = '" . $matched_course_code . "' 
					AND venue='" . $_SESSION['venueId'] . "'
					AND role='EXAMINER' 
					ORDER BY examiner_name";
			$exeExaminerDetails = $db->query($selExaminerDetails);
			//echo "A====".$selExaminerDetails; exit;



			/*	SELECT QUERT FOR GETTING CHAIRMAN DETAILS */
			$selChairman = "SELECT role, examiner_name AS chairman FROM inst.iml_examiner_details 
					WHERE exam_id='" . $exam_id . "' 
					AND course_id = '" . $matched_course_code . "' 
					AND venue = '" . $_SESSION['venueId'] . "'
					AND role IN ('CHAIRMAN') 
					ORDER BY examiner_name";
			$exeChairman = $db->query($selChairman);

			/*
					if($exeChairman->rowCount()==0)
					{
						echo '###CHAIRMAN';
						EXIT;
					}
					*/


			/*	SELECT QUERT FOR GETTING CHIEF DETAILS */
			$selChif = "SELECT examiner_id, role, examiner_name FROM inst.iml_examiner_details 
					WHERE exam_id='" . $exam_id . "' 
					AND venue = '" . $_SESSION['venueId'] . "'
					AND course_id = '" . $matched_course_code . "' 
					AND role IN ('CHIEF') 
					ORDER BY examiner_name";
			$exeChif = $db->query($selChif);

			/*
					if($exeChif->rowCount()==0 )
					{
						echo '###CHIEF';
						EXIT;
					}
					*/




			/*	DISPLAY DEGREE AND COURSE NAME DETAILS */

			$selDegreeCourseName = "SELECT (SELECT inst.function_find_degree_short_code(course_code::integer)) as degree_short_code,
				(SELECT inst.function_find_course_short_name(course_code::integer)) as course_short_name FROM inst.trn_student_cover_slip_barcode 
				WHERE exam_id='" . $exam_id . "' 
				AND delete_flag = '0' 
				AND secret_code = '" . $qr_code . "'
				GROUP BY degree_short_code, course_short_name LIMIT 1";
			$exeDegreeCourseName = $db->query($selDegreeCourseName);
			$resDegreeCourseName = $exeDegreeCourseName->fetch(PDO::FETCH_ASSOC);
			$degreeShortCode = $resDegreeCourseName['degree_short_code'];
			$courseshortName = $resDegreeCourseName['course_short_name'];





			echo '<div class="container well" style="margin-bottom:5px;">
		<legend style="margin-bottom:1px;">
				<h4 style="text-transform: uppercase; text-align:left"><b>Examiner Details</b>';

			if ($row_cnt > 0) {
				echo '<div class="col-md-12">
					<div class="col-md-10"></div>
					<div class="col-md-2">
					<input class="btn btn-primary" type="button" onclick="get_iml_value_report(' . $quote . '' . $qr_code . '' . $quote . ')" name="generatepdf" value="Report" style="float:right; margin:-15% 0% 0% 0%; background:chocolate; border-color:chocolate">
					</div>
					</div>
					</br></br>';
			}

			echo '</h4>
			</legend>
		<div class="col-md-12 textbox_width" id="display_degree" style="margin-top:1%;margin-bottom:1%;">
		<div class="col-md-4">
			<label class="col-md-7 control-label">Chairman</label>';


			if ($exam_id == '1124' && trim($_SESSION['venueId']) == '41') // THIS EXAM THEY SELECTED 2 CHAIRMAIN IN JAMAAL UNIT.
			{
				echo '<select class="form-control" name="chairman" id="chairman">
				<option value="">Select chairman</option>';
				if ($exeChairman->rowCount() > 0) {
					while ($resChairman = $exeChairman->fetch(PDO::FETCH_ASSOC)) {
?>
						<option value="<?php echo $resChairman['chairman']; ?>"><?php echo $resChairman['chairman']; ?></option>
					<?php
					}
				}
				echo '
				</select>';
			} else {
				$resChairman = $exeChairman->fetch(PDO::FETCH_ASSOC);
				$chairman = $resChairman['chairman'];

				echo '<input type="text" name="chairman" id="chairman" class="form-control" style="text-transform:uppercase;" value="' . $chairman . '">';
			}



			echo '</div>
        <div class="col-md-4">
          <label class="col-md-5 control-label">Chief<sup>*</sup></label>
          
			<select class="form-control" name="chief" id="chief" onChange="sel_examiner();">
			<option value="">Select Chief</option>';
			if ($exeChif->rowCount() > 0) {
				while ($resChif = $exeChif->fetch(PDO::FETCH_ASSOC)) {
					$selected = "";
					if ($chiefId == $resChif['examiner_id']) {
						$selected = 'selected';
					}
					?>
					<option value="<?php echo $resChif['examiner_id']; ?>" <?php echo $selected; ?>>
						<?php echo  $resChif['examiner_name'];  ?></option>
			<?php
				}
			}
			?>
			<script>
				sel_examiner();
			</script>
<?php

			echo '
			</select>
        </div>
        <div class="col-md-4">
          <label class="col-md-5 control-label">Examiner<sup>*</sup></label>
          
		  <select class="form-control" name="examiner" id="examiner">
						<option value="">Select Examiner</option>';

			/*
						if($exeExaminerDetails->rowCount() > 0 )
						{
							while($resExaminerDetails = $exeExaminerDetails->fetch(PDO::FETCH_ASSOC))
							{
								$selected = "";
								if($examiner_id==$resExaminerDetails['examiner_id'])
								{
									$selected = 'selected';	
								}
							?>
							<option value="<?php echo $resExaminerDetails['examiner_id']; ?>" <?php echo $selected; ?>> 
							<?php echo  $resExaminerDetails['examiner_name'].' - '.$resExaminerDetails['mobile'];  ?></option>
							<?php
							}
						}
						*/

			echo '
					 </select>
				 </div>
			</div>
			</fieldset>';

			echo '<br><label style="font-size:15px;color:#063;text-decoration: underline;">' . $degreeShortCode . ' - ' . $courseshortName . ' - ' . $qrCodeSubCode . ' - ' . $qrCodeSubName . '</label> 
			<br><br> 
			<label style="font-size:15px;color:#063;width:100%;">
			Max. Mark : ' . $qrCodeSubCodeMaxMark . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
			Min. Mark : ' . $qrCodeSubCodeMinMark . ' <span style="color:brown;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<span style="float:right;"> Packet No : <input type="text" name="packet_no" id="packet_no" value="' . $packet_number . '"></span>&nbsp;&nbsp;

			</span></label>
			
			
			<table class="table table-striped table-bordered" style="width:100%;" id="internal_marks_table">
			<thead>
				<tr>
					<th width="10%">Sl No</th>
					<th width="15%">Univ Reg No</th>
					<th width="20%">External Mark</th>
				</tr>
			</thead>
			<tbody style="font-size: 14px;">';


			$sel_student = "SELECT a.academic_year, a.institution_code, a.student_code, 
			a.student_registration_number, b.stud_code, b.firstname as student_name,
			b.graduation, a.degree_code as degree, 
			a.course_code as course, a.semester, subject_unique_code, subject_code, subject_type_code, a.subject_type as subjtype, 
			exam_id , secret_code, b.regulation
			FROM inst.trn_student_cover_slip_barcode a 
			left join inst.view_student_profile_ug_pg b on a.student_code = b.stud_code
			AND a.student_registration_number = b.univ_reg_no 
			AND a.institution_code = b.institution_code
			WHERE a.exam_id='" . $_SESSION['exam_id'] . "' 
			AND secret_code='" . $qr_code . "' 
			AND delete_flag = '0' 
			ORDER BY student_registration_number, student_code ASC";

			//echo "A====".$sel_student;exit;

			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			try {
				//your code/query
				$exe_student = $db->query($sel_student);
			} catch (PDOException $e) {
				//Do your error handling here
				$message = $e->getMessage();
				echo "POD ERROR1 <br>" . $message . "<br><br>";
			}


			if ($exe_student->rowCount() > 0) {
				$i = 1;
				$studentsCount = 0;
				$passCnt = 0;
				$valued = 0;
				$absentCnt = 0;
				$raCnt = 0;

				while ($row = $exe_student->fetch(PDO::FETCH_ASSOC)) {
					$secretCode = $row['secret_code'];
					$institutionCode = $row['institution_code'];
					$studentCode = $row['student_code'];
					$resRegNo = $row['student_registration_number'];
					$graduation = $row["graduation"];
					$degreeId = $row['degree'];
					$courseId = $row['course'];
					$semester = $row['semester'];
					$subjectUniqueCode = $row['subject_unique_code'];
					$subjectCode = $row['subject_code'];
					$subjectTypeCode = $row['subject_type_code'];
					$subjtype = $row['subjtype'];
					$regulation = $row['regulation'];

					if ($row["graduation"] == '1') {
						$regex = "/(\\d{4})(\\d{2})(\\d{3})(\\d{3})/";
						$universityReg = $row['student_registration_number'];
						$replacement = "$1 $2 $3 $4";
						$universityRegisterNumber = preg_replace($regex, $replacement, $universityReg);
					} else {
						$regex = "/(\\d{1})(\\d{4})(\\d{2})(\\d{3})(\\d{3})/";
						$universityReg = $row['student_registration_number'];
						$replacement = "$1 $2 $3 $4 $5";
						$universityRegisterNumber = preg_replace($regex, $replacement, $universityReg);
					}



					$sel_external_marks = "SELECT external_marks, examiner_id, chief_name, chairman_name, flag, packet_number FROM inst.trn_student_iml_external_mark 
					WHERE exam_type = '" . $_SESSION['exam_id'] . "'
					AND secret_code = '" . $qr_code . "'
					AND student_code = '" . $row['student_code'] . "'
					AND institution_code = '" . $institutionCode . "' 
					AND course_id = '" . $courseId . "' 
					AND subject_id = '" . $subjectUniqueCode . "' 
					AND semester::integer = '" . $semester . "'::integer
					AND external_exam ='" . $exam . "' 
					AND academic_year='" . $academic_year . "' 
					AND delete_flag='0'";
					//echo "B====".$sel_external_marks;	exit;

					$exe_external_marks = $db->query($sel_external_marks);
					$res_external_marks = $exe_external_marks->fetch(PDO::FETCH_ASSOC);
					$packet_no = $res_external_marks["packet_number"];
					$external_marks = $res_external_marks["external_marks"];
					$examiner_id = $res_external_marks["examiner_id"];
					$chief_name = strtoupper($res_external_marks["chief_name"]);
					$chairman_name = strtoupper($res_external_marks["chairman_name"]);
					$flag = trim($res_external_marks["flag"]);

					$external_marks_strlen = strlen($external_marks);
					if ($external_marks_strlen == '1') {
						$external_marks = "0" . $external_marks;
					}

					$disabled = '';
					if ($flag == '1') {
						$disabled = 'disabled';
					}


					/**** DISPLAY TOTAL, VALUED, PASS, ABSENT, RA COUNT ****/
					
					/*
					if(trim($res_external_marks["external_marks"])=='-6')
					{
						$valuedStudCnt+=$valued+1;
					}
					
					if($external_marks>0)
					{
						$externalMarksTotal+=$external_marks+0;
						$valuedStudCnt+=$valued+1;
					}
					
					
					if($external_marks>=$qrCodeSubCodeMinMark)
					{
						$studentPassCnt+=$passCnt+1;
					}

					
					if(trim($external_marks)=='-1')
					{
						$studentAbsentCnt+=$absentCnt+1;
						
					}
					
					if(trim($external_marks)=='-6')
					{
						$studentRACnt+=$raCnt+1;
					}

					*/


					/**** DISPLAY TOTAL, VALUED, PASS, ABSENT, RA COUNT ****/
					if (trim($res_external_marks["external_marks"]) == '-6') {
						$valuedStudCnt += $valued + 1;
					}

					if (trim($res_external_marks["external_marks"]) > 0) {
						$externalMarksTotal += $external_marks + 0;
						$valuedStudCnt += $valued + 1;
					}



					/* STUDENT WHO ARE ALL PASS COUNT */
					if (trim($res_external_marks["external_marks"]) >= $qrCodeSubCodeMinMark) {
						$studentPassCnt += $passCnt + 1;
					}



					/* STUDENT WHO ARE ALL ABSENT COUNT */
					if (trim($res_external_marks["external_marks"]) == '-1') {
						$external_marks = 'AAA';
						$studentAbsentCnt += $absentCnt + 1;
					}



					/*	MALPRACTICE STUDENTS COUNT	*/
					if (trim($res_external_marks["external_marks"]) == '-2') {
						$external_marks = 'M.P.';
					}



					/* MARKS NOT AVAILABLE STUDENTS COUNT  */
					if (trim($res_external_marks["external_marks"]) == '-4') {
						$external_marks = 'MNA';
					}


					/* ZERO MARKS STUDENTS COUNT  */
					if (trim($res_external_marks["external_marks"]) == '-6') {
						$inWords = 'ZERO';
						$external_marks = '00';
					}


					/* REAPPEAR EXAM STUDENTS COUNT */
					if ((trim($res_external_marks["external_marks"]) < $qrCodeSubCodeMinMark && trim($res_external_marks["external_marks"]) >= 0) || $external_marks == '00') {
						$studentRACnt += $raCnt + 1;
					}


					/*	EXCEMPTION STUDENTS COUNT */
					if (trim($res_external_marks["external_marks"]) == '-8') {
						$external_marks = 'EX.';
					}


					if (trim($res_external_marks["external_marks"]) < $qrCodeSubCodeMinMark) {
						$underline1 = '<b><u>';
						$underline2 = '</u><b>';
					} else {
						$underline1 = '';
						$underline2 = '';
					}


					echo '<tr>
					 <input type="hidden" name="secretCode" id="secretCode" value="' . $qr_code . '">
					 <input type="hidden" name="packetNo" id="packetNo" value="' . $packet_no . '">
					 <input type="hidden" name="institutionCode" value="' . $institutionCode . '">
					 <input type="hidden" name="univ_reg_no[]" value="' . $resRegNo . '">
					 <input type="hidden" name="studentCode[]" value="' . $studentCode . '">
					 <input type="hidden" name="graduationId" id="graduationId" value="' . $row["graduation"] . '">
					 <input type="hidden" name="degree" id="degree" value="' . $degreeId . '">
					 <input type="hidden" name="course" id="course" value="' . $courseId . '">
					 <input type="hidden" name="semester" id="semester" value="' . $semester . '">
					 <input type="hidden" name="regulation" id="regulation" value="' . $regulation . '">
					 <input type="hidden" name="externalExam" id="externalExam" value="1">
					 <input type="hidden" name="subjectUniqueCode" id="subjectUniqueCode" value="' . $subjectUniqueCode . '">
					 <input type="hidden" name="subjectCode" id="subjectCode" value="' . $subjectCode . '">
					 <input type="hidden" name="subjectTypeCode" id="subjectTypeCode" value="' . $subjectTypeCode . '">
					 <input type="hidden" name="subjtype" id="subjtype" value="' . $subjtype . '">
					 
					 
					<td style="padding:8px;">' . $i . '</td>
					<td style="padding:8px;">' . trim($universityRegisterNumber) . '</td>
					<td style="padding:8px;"><input style="text-align:right;width:50%;" min="0" type="text" name="mark[]" id="mark_id_' . $i . '" class="width" maxlength="3" onKeyPress="return onlynumeric_with_negative(event,this)" onChange="checkMarks(this.value, this.id)"  ' . $disabled . ' value=' . trim($res_external_marks["external_marks"]) . '>
					</td>
						
					  </tr>';



					$i++;
					$studentsCount++;
				}



				/* BASED ON THE STUDENTS COUNT, IF THE COUNT IS NIL NEED TO ADD "--" */

				if ($valuedStudCnt == '') {
					$resValuedStudCnt = '--';
				} else {
					$resValuedStudCnt = $valuedStudCnt;
				}

				if ($studentPassCnt == '') {
					$resStudentPassCnt = '--';
				} else {
					$resStudentPassCnt = $studentPassCnt;
				}

				if ($studentAbsentCnt == '') {
					$resStudentAbsentCnt = '--';
				} else {
					$resStudentAbsentCnt = $studentAbsentCnt;
				}

				if ($studentRACnt == '') {
					$resStudentRACnt = '--';
				} else {
					$resStudentRACnt = $studentRACnt;
				}

				$coverStudCnt = $i - 1;





				echo '<input type="hidden" name="total_count" id="total_count" value=' . $i . '>
				<input type="hidden" name="studentsCount" id="studentsCount" value=' . $studentsCount . '>
				<input type="hidden" name="university_exam_max" id="university_exam_max" value=' . $qrCodeSubCodeMaxMark . '>
				<input type="hidden" name="university_exam_min" id="university_exam_min" value=' . $qrCodeSubCodeMinMark . '>
				
				';
			}

			echo '</tbody>
				</table>';



			/*
				$selExaminerDetails ="SELECT role, string_agg((examiner_name||'-'||CASE WHEN mobile IS NULL THEN '' ELSE mobile END )::text, ',' ORDER BY examiner_name) AS name_list FROM inst.iml_examiner_details WHERE exam_id='".$exam_id."' AND course_id = '".$courseId."'  GROUP BY role ORDER BY examiner_name";
				*/


			if ($row_cnt > 0) {
				echo '<div class="col-md-12">
					<div class="col-md-4"></div>
					<div class="col-md-8">
					<label style="font-size:15px;color:#063;text-decoration: underline;">
					<label style="font-size:15px;color:#063;">
					<b>Total : </b>' . $externalMarksTotal . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Valued : </b>' . $resValuedStudCnt . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Pass : </b>' . $resStudentPassCnt . ' 
					
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Absent : </b> ' . $resStudentAbsentCnt . '
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b> RA : </b> ' . $resStudentRACnt . ' 
					
					</label>
					</div>
					</div>';
			}


			if ($exe_student->rowCount() > 0) {

				if ($allowZoneExternalMarksEntry == '1') {
					if ($flag != 1) {

						/*
					<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#exampleModalCenter" id="details_btn" onclick="openPreview( \''.trim($institution_code).'\', '.$graduationCode.', '.$degreeCode.', '.$courseId.','.$semester.', '.$regulation.', \''.trim($subjectCode).'\', \''.trim($subjectType).'\')" style=" margin:0% 1% 1% 1%; background:#e91e63; border-color:#e91e63">PREVIEW</button>
						
						*/


						echo '
					
					<div class="col-md-12" id="update_internal_mark_button" style="text-align:right; margin-top:2%;">
						<p style="color: #FF0000; font-weight: bold;">
						<input type="checkbox" id="verified" name="verified" value="1" class="check"> 
						 Please tick the check box for submit. Please ensure correct entry and submit. Once you submit No Update will be allowed.
						 
						 <br><br>
						 <input type="submit" name="update_external_mark" id="update_external_mark" value="SAVE" class="btn btn-primary" 
						style=" margin:0% 0% 1% 0%; padding:10px 25px 10px 25px; background:chocolate; border-color:chocolate" onClick="return validateAllExternalMark(1)">
							 
					    <input type="submit" name="submit_external_mark" id="submit_external_mark" value="SUBMIT" class="btn btn-primary" disabled="disabled" 
						style=" margin:0% 0% 1% 0%; padding:10px 25px 10px 25px; background: green; border-color:green;" onClick="return validateAllExternalMark(2)">
					</div>';
					} else {
						echo '<br><br><br><p style="color:red; text-align:center;"><b>Marks are already finalized.  Further modification not allowed.</b></p>';
					}
				}
			}

			echo '</form>
				</div></div>
</div>';
		} else {
			echo 'error';
		}
	}
}



if (isset($_POST['type']) && $_POST['type'] == 'view_internal_marks') {
	if ($_POST['pagetoken'] == $_SESSION['pagetoken']) {

		$institution = $_POST["institution"];
		$graduation = $_POST["graduation"];
		$degree = $_POST["degree"];
		$course = $_POST["course"];
		$semester = $_POST["semester"];
		$regulation = $_POST["regulation"];
		$subjectcode = $_POST["subjectcode"];
		$header = $_POST["header"];
		$serialNo = $_POST["serialNo"];
		$univregno = $_POST["univregno"];
		$studCode = $_POST["studCode"];
		$stud_name = $_POST["stud_name"];
		$internalmark1 = $_POST["internalmark1"];
		$internalmark2 = $_POST["internalmark2"];
		$internalmark3 = $_POST["internalmark3"];
		$internalmark4 = $_POST["internalmark4"];
		$internalmark5 = $_POST["internalmark5"];
		$subjectType = $_POST["subjectType"];


		echo '<h4>Internal Mark Details:</h4>
				<table id="combined_subject_details" class="table table-striped table-bordered" border="1" width="80%">
		<thead style="font-weight: 600;">';

		echo '<tr>';
		foreach ($header as $key => $header_key) {
			echo '<th style="text-align:center;">' . $header[$key] . '</th>';
		}
		echo '</tr>
          </thead>
		  <tbody>';

		// 			<td style='padding:10px'>".$studCode[$key]."</td>


		foreach ($univregno as $key => $value) {

			echo "<tr>";
			echo "<td style='padding:10px'>" . $serialNo[$key] . "</td>
			<td style='padding:10px'>" . $univregno[$key] . "</td>
			<td style='padding:10px'>" . $stud_name[$key] . "</td>
			<td style='padding:10px'>" . $internalmark1[$key] . "</td>";

			if ($subjectType == 'PRA') {
				echo "<td style='padding:10px'>" . $internalmark2[$key] . "</td>";
			}

			echo "<td style='padding:10px'>" . $internalmark3[$key] . "</td>
			<td style='padding:10px'>" . $internalmark4[$key] . "</td>
			<td style='padding:10px'>" . $internalmark5[$key] . "</td>
			</tr>";
		}





		echo '</tbody>
		</table>';
	} else {
		echo "7";
	}
}





$db->query("COMMIT");
?>



<script>
	$('.check').change(function() {
		if ($('.check:checked').length) {
			$('#submit_external_mark').removeAttr('disabled');
		} else {
			$('#submit_external_mark').attr('disabled', 'disabled');
		}
	});
</script>