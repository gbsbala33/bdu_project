<?php



$killchars = array("%", "--", "alert(", "drop", "update", "delete", "create", "alter", "truncate");
if (isset($_POST))
	foreach ($_POST as $key => $val) {
		if (is_array($val)) {
			foreach ($val as $sub_key => $sub_val) {
				$_POST[$key][$sub_key] = str_replace($killchars, "", trim(pg_escape_string(strip_tags($sub_val))));
			}
		} else {
			$_POST[$key] = str_replace($killchars, "", trim(pg_escape_string(strip_tags($val))));
		}
	}



header('Content-Disposition: attachment');
header('Expire: 0');
header('Pragma: cache');
header('Cache-control: private');

define('PAGE', 'studentInternalMarkReport');

header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="' . 'report' . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
//ini_set('max_execution_time', 99000999);
ini_set('max_execution_time', '0');
ini_set('memory_limit', '-1');



include("includes/session_include.php");
include("includes/enc_conn_include.php");
include("includes/header_validate_code.php");


include("../includes/mpdf/mpdf.php");

//$mpdf = new mPDF('ta');
//$mpdf = new mPDF(['format' => 'Legal']);
//$mpdf = new mPDF(['mode' => 'utf-8', 'format' => [190, 236]]);
//$mpdf = new \Mpdf\Mpdf(['format' => 'Legal']);


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

if ($pagetoken != $_SESSION["pagetoken"]) {
?>
	<script>
		alert('<?php echo "Invalid Access"; ?>');
		window.location = "logout.php";
	</script>
<?php
	exit;
}


$mpdf->showImageErrors = true;
//$mpdf=new mPDF('','10.5cm',8,8,3,3,25,10,3,3);

$mpdf = new mPDF(
	'utf-8',   // mode - default ''
	array(105, 345),    // format - A4, for example, default ''
	8,     // font size - default 0
	'',    // default font family
	1,    // margin_left
	1,    // margin right
	1,     // margin top
	1,    // margin bottom
	0,     // margin header
	0,     // margin footer
	P
);  // L - landscape, P - portrait






$pagetoken = $_POST['pagetoken'];
$secretCode = pg_escape_string(strip_tags(trim($_POST['secret_code_dt'])));


$department_id = validateInputData($_SESSION['department_id']);
if ($department_id == '1') {
	$degreeName = "UG";
} else if (validateInputData($department_id) == '2') {
	$degreeName = "PG";
}


/* SELECT EXAM NAME */
$selExamName = "SELECT exam_name FROM inst.trn_exam_config WHERE exam_id = '" . $exam_id . "'";
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
	//your code/query
	$exeExamName = $db->query($selExamName);
} catch (PDOException $e) {
	//Do your error handling here
	$message = $e->getMessage();
	echo "POD ERROR1 <br>" . $message . "<br><br>";
}

$resExamName = $exeExamName->fetch(PDO::FETCH_ASSOC);
$exam_name = $resExamName['exam_name'];
/* SELECT EXAM NAME END*/


/* 	SELECT QUERY FOR GETTING STUDENT DETAILS BASED ON THE QR CODE 	*/
$selQuery = "SELECT DISTINCT subject_code, course_code, 
	(SELECT inst.function_find_degree_short_code(course_code::integer)) as degree_short_code,
	(SELECT inst.function_find_course_name(course_code::integer)) as course_name FROM inst.trn_student_cover_slip_barcode
	WHERE exam_id='" . $_SESSION['exam_id'] . "' 
	AND secret_code='" . trim($secretCode) . "' 
	AND delete_flag = '0'";

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
	//your code/query
	$exeQuery = $db->query($selQuery);
} catch (PDOException $e) {
	//Do your error handling here
	$message = $e->getMessage();
	echo "POD ERROR1 <br>" . $message . "<br><br>";
}

$resQuery = $exeQuery->fetch(PDO::FETCH_ASSOC);
$qrCodeSubCode = $resQuery['subject_code'];
$degreeShortCode = $resQuery['degree_short_code'];
$courseName = $resQuery['course_name'];
$degreeCourseName = $degreeShortCode . " - " . $courseName;



/* QUERY FOR DISPLAY REPORT BUTTON */
$selExtMarkEntryCnt = "SELECT (SELECT venue FROM inst.iml_entry_venue b WHERE b.venue_code::integer=a.unit_id::integer ) AS venue_name,  unit_id, examiner_id, chief_name, packet_number, marks_entry_date, COUNT(exam_type) as row_cnt FROM inst.trn_student_iml_external_mark a 
	WHERE exam_type = '" . $_SESSION['exam_id'] . "'
	AND secret_code = '" . $secretCode . "'
	AND academic_year='" . $academic_year . "' 
	AND delete_flag='0'
	GROUP BY examiner_id, chief_name, packet_number, unit_id, marks_entry_date, venue_name";

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
$venue_name = $resExtMarkEntryCnt['venue_name'];
$row_cnt = $resExtMarkEntryCnt['row_cnt'];
$examiner_id = $resExtMarkEntryCnt['examiner_id'];
$chiefId = $resExtMarkEntryCnt['chief_name'];
$packet_number = $resExtMarkEntryCnt['packet_number'];
$marks_entry_date = $resExtMarkEntryCnt['marks_entry_date'];


/* END */



/* SELECT SUBJECT DETAILS */
/*
	$selSubjectDetails = "SELECT degree_short_code, course_id, course_name, course_short_name,  semester, regulation, part, subject_type_code_univ, subject_type_code, subject_group_id, subject_id, subject_code, subject_name, internal_assessment_min, internal_assessment_max, university_exam_min, university_exam_max,  type_theory_or_practical FROM inst.view_course_subject_details WHERE subject_code = '".$qrCodeSubCode."' AND subject_common_deleted_date IS NULL AND display_status='Y'  LIMIT 1";
	*/

$selSubjectDetails = "SELECT degree_short_code, course_id, course_name, course_short_name,  semester, regulation, part, subject_type_code_univ, subject_type_code, subject_group_id, subject_id, subject_code, subject_name, internal_assessment_min, internal_assessment_max, university_exam_min, university_exam_max,  type_theory_or_practical FROM inst.mst_overall_unique_subject_code WHERE subject_code = '" . $qrCodeSubCode . "' AND display_status='Y' LIMIT 1";


$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
	//your code/query
	$exeSubjectDetails = $db->query($selSubjectDetails);
} catch (PDOException $e) {
	//Do your error handling here
	$message = $e->getMessage();
	echo "POD ERROR1 <br>" . $message . "<br><br>";
}

$resSubjectDetails = $exeSubjectDetails->fetch(PDO::FETCH_ASSOC);
$qrCodeDegreeCode = $resSubjectDetails['degree_short_code'];
$qrCodeCourseCode = $resSubjectDetails['course_short_name'];
$qrCodecourseName = $resSubjectDetails['course_name'];
$qrCodeSubCode = $resSubjectDetails['subject_code'];
$qrCodeSubName = $resSubjectDetails['subject_name'];
$qrCodeSubCodeMinMark = $resSubjectDetails['university_exam_min'];
$qrCodeSubCodeMaxMark = $resSubjectDetails['university_exam_max'];
$allowZoneExternalMarksEntry = $_POST['allowZoneExternalMarksEntry'];
$exam = '1';

$quote = "'";


/* 	SELECT MAJOR */
$selMajor = "SELECT * FROM inst.iml_course_subject_code
WHERE subject_code = '" . $qrCodeSubCode . "'";
$exeMajor = $db->query($selMajor);
$resMajor = $exeMajor->fetch(PDO::FETCH_ASSOC);





//$qrCodeDegreeCode

$html .= '<br><br><br><br>
	<table style="width:98%;border-collapse:collapse;" border="0" >
	
	<tr>

	<td style="height:20px;" ></td>
	<td></td>
	
	</tr>

	<tr>
	<td colspan="2"> 
	<img alt="College Logo" style="width:78%;margin-left:5%;" src="images/bdu_header.jpg" />
	<label style="text-align:center;"> <h4></h4>
	</label>
	</td>
	</tr>
	</table>
	

	
	<br><table style="width:98%;margin-left:2%;padding-top:3%;border-collapse:collapse;" border="0" >
	<tr> <td colspan="2" style="text-align:center;font-weight:bold;"> ' . $degreeName . ' DEGREE EXAMINATIONS, ' . strtoupper($exam_name) . ' </td></tr>
	<tr><td colspan="2" style="text-align:center;font-weight:bold;">THEORY EXTERNAL - INDIVIDUAL MARK LIST</td></tr>
	</table>

	<br>
	<table style="width:98%;margin-left:2%;padding-top:3%;border-collapse:collapse;" border="0">
	<tr>
	<td width="25%" style="vertical-align:top;"><b>C.V.Centre </b></td>
	<td width="2%" style="vertical-align:top;">:</td>
	<td style="text-align: justify;margin-left:2%;vertical-align:top;"> ' . strtoupper($venue_name) . '</td>
	</tr>
	<tr>
	<td width="25%;" style="vertical-align:top;"><b>Major </b></td> 
	<td width="2%" style="vertical-align:top;">:</td>
	<td style="vertical-align:top;"> ' . strtoupper(htmlentities($degreeCourseName)) . '</td>
	</tr>
	<tr>
	<td width="25%" style="vertical-align:top;"><b>Title </b></td>
	<td width="2%" style="vertical-align:top;">:</td>	
	<td style="vertical-align:top;"> ' . strtoupper($qrCodeSubName) . '</td>
	</tr>
	<tr>
	<td width="25%" style="vertical-align:top;"><b>Subject Code </b></td> 
	<td width="2%" style="vertical-align:top;">:</td>
	<td style="vertical-align:top;"> ' . strtoupper($qrCodeSubCode) . '</td>
	</tr>
	</table>
	<br> 
	<label style="color:#063;">&nbsp;&nbsp;&nbsp;
	<b>Max. Mark :</b> ' . $qrCodeSubCodeMaxMark . '
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
	<b>Min. Mark :</b> ' . $qrCodeSubCodeMinMark . ' <span style="color:brown;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<b>C.V. Date : </b>' . $marks_entry_date . '</span></label>
	<br><br>
	
	
	
	<table style="width:98%;border-collapse:collapse;margin-left:2%;" border="0" >
	<tr>
	<td style="width:50%;"></td>
	<td style="width:50%;border:1px solid black;text-align:right;padding:5px;"> Packet No : ' . $packet_number . ' </td>
	</tr>
	</table>
	<br>
	
	<table style="width:98%;border-collapse:collapse;margin-left:2%;" border="1" >
	<thead>
		<tr>
			<th width="10%">Sl No</th>
			<th width="15%">Univ Reg No</th>
			<th width="20%">Marks</th>
			<th width="20%">In Words</th>
		</tr>
	</thead>
	<tbody>';



if ($department_id == '1') {
	$tblName = " inst.trn_student_profile_ug";
} else if (validateInputData($department_id) == '2') {
	$tblName = " inst.trn_student_profile_pg";
}



/*
	$sel_student = "SELECT a.academic_year, a.institution_code, a.student_code, 
	a.student_registration_number, b.stud_code, b.firstname as student_name,
	b.graduation, a.degree_code as degree, 
	a.course_code as course, a.semester, subject_unique_code, subject_code, subject_type_code, a.subject_type as subjtype, 
	exam_id , secret_code, b.regulation
	FROM inst.trn_student_cover_slip_barcode a 
	left join inst.view_student_profile_ug_pg b on a.student_code = b.stud_code
	AND a.student_registration_number = b.univ_reg_no 
	AND a.institution_code = b.institution_code
	WHERE a.exam_id='".$_SESSION['exam_id']."' 
	AND secret_code='".$secretCode."' 
	AND delete_flag = '0' 
	ORDER BY student_registration_number, student_code ASC";
	*/


$sel_student = "SELECT a.academic_year, a.institution_code, a.student_code, 
	a.student_registration_number, b.stud_code, b.firstname as student_name,
	b.graduation, a.degree_code as degree, 
	a.course_code as course, a.semester, subject_unique_code, subject_code, subject_type_code, a.subject_type as subjtype, 
	exam_id , secret_code, b.regulation
	FROM inst.trn_student_cover_slip_barcode a 
	LEFT JOIN $tblName b on a.student_code = b.stud_code
	AND a.student_registration_number = b.univ_reg_no 
	AND a.institution_code = b.instcode
	WHERE a.exam_id='" . $_SESSION['exam_id'] . "' 
	AND secret_code='" . $secretCode . "' 
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
	$absentCnt = 0;
	$raCnt = 0;
	$valued = 0;
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

		//$external_marks = "";
		/*
			if($row["graduation"]=='1')
			{
				$regex = "/(\\d{4})(\\d{2})(\\d{3})(\\d{3})/"; 
				$universityReg=$row['student_registration_number'];
				$replacement = "$1 $2 $3 $4"; 
				$universityRegisterNumber = preg_replace($regex, $replacement, $universityReg);
			}else{
				$regex = "/(\\d{1})(\\d{4})(\\d{2})(\\d{3})(\\d{3})/"; 
				$universityReg=$row['student_registration_number'];
				$replacement = "$1 $2 $3 $4 $5"; 
				$universityRegisterNumber = preg_replace($regex, $replacement, $universityReg);
			}
*/



		$universityRegisterNumber = $row['student_registration_number'];

		$sel_external_marks = "SELECT course_id, external_marks, examiner_id, chief_name, chairman_name, flag FROM inst.trn_student_iml_external_mark 
			WHERE secret_code = '" . $secretCode . "'
			AND exam_type = '" . $_SESSION['exam_id'] . "'
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
		$course_id = $res_external_marks["course_id"];
		$external_marks = trim($res_external_marks["external_marks"]);
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


		if (strtoupper(numberTowords($external_marks)) != '') {
			$inWords = strtoupper(numberTowords($external_marks));
		} else {
			$inWords = '---';
		}




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



		/* SELECT COURSE ID FROM COURSE SUBJECT CODE TABLE - FROM UPLOAD FILE START */

		$selCourseId = "SELECT  * FROM inst.iml_course_subject_code 
			WHERE exam_id='" . $exam_id . "' AND subject_code = '" . $subjectCode . "'";

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

		/*********************** END ***************************/







		if (validateInputData($_SESSION['venueId']) == '59') {
			if ($matched_subject_code == '22PELLS1' || $matched_subject_code == '22PELLS2') {
				$matched_course_code = '40'; // UNIT SRI BHARATHI PAPER CODE 22PELLS1 AND 22PELLS1 EXAMINNER COMES UNDER BOTANY DEPARTMENT.
			}
			if ($matched_subject_code == '22PELPS1' || $matched_subject_code == '22PELPS2') {
				$matched_course_code = '55'; // UNIT SRI BHARATHI PAPER CODE 22PELPS1 AND 22PELPS2 EXAMINNER COMES UNDER MATHEMATICS DEPARTMENT.
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
			// PAPER CODE 22PELPS1 AND 22PELPS2 EXAMINNER COMES UNDER CSE DEPARTMENT.
			if ($matched_subject_code == '22PELPS1' || $matched_subject_code == '22PELPS2') {
				$matched_course_code = '42';
			}
		}

		if (validateInputData($_SESSION['venueId']) == '60') {

			// UNIT - I, BHARATH COLLEGE OF SCIENCE AND MANAGEMENT, THANJAVUR.
			// PAPER CODE 22PELPS1 AND 22PELPS2 EXAMINNER COMES UNDER ELECTRONICS DEPARTMENT.
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





		$selExaminerDt = "SELECT examiner_name, mobile, institution_code AS examiner_inst_code, (SELECT institution_name FROM inst.trn_institution_info b WHERE b.institution_code = a.institution_code) AS examiner_inst_name ,
			(SELECT bdu_district FROM inst.trn_institution_info b WHERE b.institution_code = a.institution_code) AS examiner_district,
			(SELECT pincode FROM inst.trn_institution_info b WHERE b.institution_code = a.institution_code) AS examiner_pincode
			FROM inst.iml_examiner_details a 
			WHERE exam_id='" . $exam_id . "' 
			AND course_id = '" . $matched_course_code . "' 
			AND examiner_id = '" . $examiner_id . "'
			AND role NOT IN ('Chief','Chairman') ORDER BY examiner_name";
		$exeExaminerDt = $db->query($selExaminerDt);
		$resExaminerDt = $exeExaminerDt->fetch(PDO::FETCH_ASSOC);
		$examiner_name = $resExaminerDt['examiner_name'];
		$mobile = $resExaminerDt['mobile'];
		$examiner_inst_code = $resExaminerDt['examiner_inst_code'];
		$examiner_inst_name = $resExaminerDt['examiner_inst_name'] . ', ' . $resExaminerDt['examiner_district'] . " - " . $resExaminerDt['examiner_pincode'] . ".";





		$selChairman = "SELECT role, examiner_name FROM inst.iml_examiner_details 
			WHERE exam_id='" . $exam_id . "' 
			AND venue = '" . $_SESSION['venueId'] . "'
			AND course_id = '" . $matched_course_code . "' 
			AND role IN ('CHAIRMAN') ORDER BY examiner_name";
		$exeChairman = $db->query($selChairman);
		$resChairman = $exeChairman->fetch(PDO::FETCH_ASSOC);

		if ($exam_id == '1124' && trim($_SESSION['venueId']) == '41') // THIS EXAM THEY SELECTED 2 CHAIRMAIN IN JAMAAL UNIT.
			$chairman = $chairman_name;
		else
			$chairman = $resChairman['examiner_name'];








		$selChif = "SELECT role, examiner_name FROM inst.iml_examiner_details WHERE exam_id='" . $exam_id . "' 
			AND venue = '" . $_SESSION['venueId'] . "'
			AND course_id = '" . $matched_course_code . "'
			AND examiner_id = '" . $chief_name . "'
			AND role IN ('CHIEF') ORDER BY examiner_name";
		$exeChif = $db->query($selChif);
		$resChif = $exeChif->fetch(PDO::FETCH_ASSOC);
		$chief = $resChif['examiner_name'];



		$html .= '<tr>
			<td style="padding:2px;">' . $i . '</td>
			<td style="padding:2px;padding-left:5%;">' . $universityRegisterNumber . '</td>
			<td style="padding:2px;text-align:center;">' . $underline1 . ' ' . $external_marks . ' ' . $underline2 . '</td>
			<td style="padding:2px;font-size:8px;padding-left:5%;">' . $inWords . '</td>
			</tr>';

		$i++;
		$studentsCount++;
	}
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





$html .= '</tbody>
		</table>
		
		
		<table style="width:98%;margin-left:2%;padding-top:3%;border-collapse:collapse;" border="0">
			<tr>
				<td colspan="1" style="text-align:left"><b>Total</b></td>
				<td><b>:</b></td>
				<td style="text-align:center">' . $externalMarksTotal . '</td>
				<td colspan="3"></td>
			</tr>
			
			<tr>
				<td colspan="1" style="text-align:left"><b>Regd.</b></td>
				<td><b>:</b></td>
				<td style="text-align:center">' . $coverStudCnt . '</td>
				<td colspan="3"></td>
			</tr>
			
			<tr>
				<td><b>Valued</b></td>
				<td><b>:</b></td>
				<td>' . $resValuedStudCnt . '</td>
				<td><b>Pass</b></td>
				<td><b>:</b></td>
				<td>' . $resStudentPassCnt . '</td>
			</tr>
			<tr>
				<td><b>Absent</b></td>
				<td><b>:</b></td>
				<td>' . $resStudentAbsentCnt . '</td>
				<td><b>RA</b></td>
				<td><b>:</b></td>
				<td>' . $resStudentRACnt . '</td>
			</tr>
		</table>

	
			
		<table style="width:98%; margin-left:2%; margin-top:2%; padding:10px; border-collapse:collapse;" border="0" >
			<tr>
				<td style="text-align:justify;"> The above mentioned answer scripts are valued by me. And the above subject code, register numbers and U.E. marks are verified with relevant answer scripts and certified that they are correct.
				</td>
			</tr>
		</table>
			
		<table style="width:98%; margin-left:2%; margin-top:2%; padding:10px; border-collapse:collapse;" border="1" >
			<tr>
				<td colspan="2" style="text-align:center; font-weight:bold; width:10%;">Examiner</td>
			</tr>
			<tr>
				<td width="25%"><b>&nbsp;&nbsp;Name </b></td> 
				<td width="75%">&nbsp;&nbsp; ' . $examiner_name . '</td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;<b>Mobile </b></td> 
				<td>&nbsp;&nbsp;' . $mobile . '</td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;<b>Signature</b> </td> 
				<td> <br><br> &nbsp;&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;<b>College</b> </td> 
				<td>&nbsp;&nbsp;' . $examiner_inst_code . ' - ' . $examiner_inst_name . '</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center; font-weight:bold;">Signature with Date</td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;<b>Chief</b> </td> 
				<td> <br><br>&nbsp;&nbsp;' . $chief . '</td></tr>
			<tr>
				<td>&nbsp;&nbsp;<b>Chairman</b> </td> 
				<td><br><br>&nbsp;&nbsp;' . $chairman . '</td>
			</tr>
			
			
		</table>
		
		
		
</fieldset>
</form>


	<div style="margin:2% 0% 0% 27%;"> <barcode code="' . $secretCode . '" type="QR" size="0.6"  style="float:left;"/> </td>  </div> 
	</div></div>
</div>';


$today = date('d-m-Y H:i:s');
$footer = '<div style=" padding-right:10px;" align="right">

Date Of Generation ' . $today . '   {PAGENO} / {nb}</div>';
$html .=	'</div>';



$mpdf->SetDisplayMode('fullpage');


$mpdf->SetWatermarkText('BHARATHIDASAN UNIVERSITY');
$mpdf->showWatermarkText = true;
$mpdf->watermarkTextAlpha = 0.03;

$mpdf->showWatermarkImage = true;



$mpdf->SetWatermarkImage('images/bdu_logo.jpg', 0.03, 'F');




//$mpdf->SetHeader($header,'0');
//$mpdf->SetHTMLFooter($footer);

$mpdf->SetDisplayMode('fullpage');
$stylesheet = file_get_contents('css/bootstrap.css');
$stylesheet = file_get_contents('css/gfonts1.css');
$stylesheet = file_get_contents('css/gfonts2.css');
//$stylesheet = file_get_contents('size: 21cm 29.7cm; margin: 0;');
$stylesheet = file_get_contents('css/style.css');
// external css
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($html, 2);
//$mpdf->AddPage(); 


// newl added
$mypdf->cacheTables = true;
$mypdf->simpleTables = true;
$mypdf->packTableData = true;
//newly added


$mpdf->Output('student_profile.pdf', 'I');
exit;

?>