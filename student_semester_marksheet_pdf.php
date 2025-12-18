<?php

/*	ACADEMIC YEAR	*/
$academic_year_data = explode("-", $_SESSION["academic_year"]);
$academic_year = trim($academic_year_data[0]);
/*	EXAM ID	*/
$exam_id = $_SESSION['exam_id'];
$res_get_report[] = killChars(pg_escape_string(strip_tags($_POST["res_student_text_sm"])));
$graduation_id = killChars(pg_escape_string(strip_tags($_POST["degree_type_id_cm"])));

$examid = $_POST['examid_sm'];

header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="' . 'report' . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
ini_set('max_execution_time', 99000999);
ini_set('memory_limit', '-1');


if (!isset($_SESSION["academic_year"]) || !isset($_SESSION['exam_id']) || $_SESSION["academic_year"] == "" || $_SESSION['exam_id'] == "") {
?>
	<script>
		alert('<?php echo "Invalid Access."; ?>');
		window.location = "logout.php";
	</script>
<?php
	//exit;
}

$category = $_SESSION["category"];

if ($category != 'CE') {
?>
	<script>
		alert('<?php echo "Invalid Access"; ?>');
		window.location = "logout.php";
	</script>
<?php
	exit;
}

include("../includes/mpdf/mpdf.php");

include("./phpqrcode/qrlib.php");

//$mpdf = new mPDF('ta');
//$mpdf->showImageErrors = true;	


//$mpdf=new mPDF('utf-8', array(230,305),'','',1,0,1,1,0,0,P);
$mpdf = new mPDF(
	'utf-8',   // mode - default ''
	array(230, 305),    // format - A4, for example, default ''
	'',     // font size - default 0
	'',    // default font family
	1,    // margin_left
	1,    // margin right
	1,     // margin top
	1,    // margin bottom
	0,     // margin header
	0,     // margin footer
	P
);  // L - landscape, P - portrait


/*
 $mpdf = new mPDF('utf-8',   // mode - default ''
array(306,233),    // format - A4, for example, default ''
'',     // font size - default 0
'',    // default font family
 1,    // margin_left
 1,    // margin right
 1,     // margin top
 1,    // margin bottom
 0,     // margin header
 0,     // margin footer
 L);  // L - landscape, P - portrait
 */


//$mpdf=new mPDF('c','A4','','',1,1,1,1,5,5);
//==============================================================


$mpdf->pagenumPrefix = 'Page ';
$mpdf->pagenumSuffix = '';
$mpdf->nbpgPrefix = ' of ';
$mpdf->nbpgSuffix = ' pages.';



$stud = "'" . str_replace(",", "','", rtrim(implode(",", $res_get_report), ",")) . "'";

//echo "Q==".".rtrim(implode(',',$res_get_report),',').";
if ($graduation_id == '1') {
	$selQuery = "SELECT institution_code, stud_code, univ_reg_no, firstname, dob, current_semester, course, year_of_joining, graduation, student_photo,
(SELECT institution_name FROM inst.mst_institution where a.institution_code::integer=institution_code::integer) as instname, 
(SELECT graduation_name FROM inst.mst_graduation where a.graduation::integer=graduation_id::integer) as graduation_name, 
(SELECT degree_name FROM inst.mst_degree where a.degree::integer=degree_id::integer) as degree, 
(SELECT inst.function_find_degree_short_code(a.course::integer)) AS degree_short_code, 
(SELECT inst.function_find_course_name(a.course::integer)) AS course_name, 
(SELECT inst.function_find_course_short_name(a.course::integer)) AS course_short_code, batch, academic_year,
(SELECT (SELECT subject_code FROM inst.mst_overall_unique_subject_code WHERE q.course_id::integer=course_id::integer AND q.semester::integer=semester::integer AND q.subject_unique_code::integer=subject_id::integer GROUP BY subject_code) FROM student_result.stud_result_0619 q WHERE a.graduation=graduation AND a.degree=degree AND a.course=course AND univ_reg_no=a.univ_reg_no AND semester::integer='01'::integer AND subject_type_code='LC' AND current_arrear_flag='CR') FROM inst.view_student_profile_ug_pg a WHERE stud_code::double precision IN (" . $stud . ") ORDER BY univ_reg_no asc";

	//echo "A====".$selQuery;
	//exit;


	$exeQuery = $db->query($selQuery);


	$selExamName = "SELECT  exam_name FROM inst.trn_exam_config 
	WHERE active_state='1' AND exam_id = '" . $examid . "'";
	$exeExamName = $db->query($selExamName);
	$resExamName = $exeExamName->fetch(PDO::FETCH_ASSOC);
	$laststring = substr($resExamName['exam_name'], -4);
	$firststring = substr($resExamName['exam_name'], 0, 3);
	$term = $firststring . " " . $laststring;

	//strtoupper(date('F Y', strtotime(date('d-m-Y'))))


	if (($exeQuery->rowCount() > 0)) {
		$d = 1;

		while ($resQuery = $exeQuery->fetch(PDO::FETCH_ASSOC)) {

			$univRegNo = trim(strtoupper($resQuery['univ_reg_no']));
			$studentName = trim(strtoupper($resQuery['firstname']));
			$degreeName = validateInputData($resQuery['degree_short_code']);
			$courseName = validateInputData($resQuery['course_name']);
			$studentPhoto = $resQuery['student_photo'];


			$degreeDetails = "UG - " . $degreeName . ' - ' . $courseName;

			$year_of_joining = $resQuery['year_of_joining'];

			$year_of_passing = "";
			if ($resQuery['graduation'] == '1') {
				$year_of_passing  = $year_of_joining + 3;
			}


			$year_of_study = $year_of_joining . '-' . $year_of_passing;


			$subject = substr(trim($resQuery['subject_code']), -2, 1);

			if ($subject == 'T') {
				$lc = 'TAMIL';
			} else if ($subject == 'H') {
				$lc = 'HINDI';
			} else if ($subject == 'U') {
				$lc = 'URUDU';
			} else if ($subject == 'S') {
				$lc = 'SANSKRIT';
			} else if ($subject == 'F') {
				$lc = 'FRENCH';
			} else if ($subject == 'A') {
				$lc = 'ARABIC';
			}

			$institution_code = ltrim($resQuery['institution_code'], "0");


			$qrvalue = "Reg No : " . $univRegNo . " Name : " . $studentName . "  College Code : " . $institution_code . " - Exam Period :" . trim($term) . " Degree : " . $degreeDetails . "";


			/*
			<tr> 
			<td>&nbsp;</td> 
			<td>&nbsp;</td> 
			<td style="text-left:center;"><barcode code="' . ($qrvalue) . '" size="0.6" type="QR" error="Q" class="barcode" /></td> 
			<td style="text-align:right;"><img style="width:86px;height:86px;"  src="data:image/jpeg;base64,' . $studentPhoto . '"></td> 
			<td>&nbsp;</td> 
			<td>&nbsp;</td> 
			</tr>
			*/


			$html .= '<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">';

			$html .= '
			<tr> 
			<td>&nbsp;</td> 
			<td>&nbsp;</td> 
			<td style="text-left:center;"><barcode code="' . ($qrvalue) . '" size="1.9" type="QR" error="Q" class="barcode" /></td> 
			<td style="text-align:right;"><img style="width:86px;height:86px;"  src="data:image/jpeg;base64,' . $studentPhoto . '"></td> 
			<td>&nbsp;</td> 
			<td>&nbsp;</td> 
			</tr>

			<tr> 
			<td  style="text-align:center;width:15px;height:38px;">&nbsp;</td> 
			<td  style="text-align:center;width:158px;height:38px;">' . trim(strtoupper($resQuery['degree_short_code'])) . '</td> 
			<td  style="text-align:left;width:181px;height:38px;">' . trim(strtoupper($resQuery['univ_reg_no'])) . '</td> 
			<td  style="text-align:left;width:389px;height:38px;">' . trim(strtoupper($resQuery['firstname'])) . '</td> 
			<td  style="text-align:center;width:120px;height:38px;">U-0888464</td> 
			<td  style="text-align:center;width:11px;height:38px;">&nbsp;</td> 
			</tr> ';

			$html .= '</table>';



			$html .= '<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">
		<tr> 
	<td  style="text-align:center;width:15px;height:49px;">&nbsp;</td> 
	<td  style="text-align:center;width:158px;height:49px;">' . trim($lc) . '</td> 
	<td  style="text-align:left;width:181px;height:49px;">ENGLISH</td> 
	<td  style="text-align:left;width:389px;height:49px;">' . trim(strtoupper($resQuery['course_name'])) . '</td> 
	<td  style="text-align:center;width:120px;height:49px;">' . trim($year_of_study) . '</td> 
	<td  style="text-align:center;width:11px;height:49px;">&nbsp;</td> 
</tr> 
		 </table>';


			$html .= '<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
<tr> 
	<td  style="text-align:center;width:15px;height:49px;">&nbsp;</td> 
	<td  style="text-align:center;width:56px;height:49px;">&nbsp;</td> 
	<td  style="text-align:left;width:676px;height:49px;">' . trim($institution_code) . ' - ' . trim(strtoupper($resQuery['instname'])) . '</td> 
	<td  style="text-align:center;width:120px;height:49px;">' . trim($term) . '</td> 
	<td  style="text-align:center;width:11px;height:49px;">&nbsp;</td> 
</tr> 
</table>';

			/*
$selSubjectDetails = "SELECT b.part, a.semester, a.subject_code, b.subject_name, b.subject_credit, b.max_pass_mark, total_mark, 
CAST(cast(total_mark as decimal)/10 as decimal(10,1)) AS grade_point, 
(SELECT exam_short_display FROM inst.trn_exam_config WHERE active_state='1' AND exam_id=a.exam_id) AS month_year
FROM student_result.stud_result_0619 a
JOIN inst.mst_overall_unique_subject_code_tvu b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer
WHERE univ_reg_no = '".$resQuery['univ_reg_no']."' AND delete_flg = '0'   AND display_status='Y'
 ORDER BY semester, part, subject_code"; */


			$exp = "^\\\d+$";

			//$exp = "/^\\d+$/";
			/*echo "A==".$selSubjectDetails = "SELECT part, semester, subject_code, subject_name, subject_credit, max_pass_mark, total_mark, grade_point,
(SELECT letter_grade FROM inst.mst_grade_points WHERE  grade_point::numeric >= grade_points_min::numeric 
AND grade_point::numeric <= grade_points_max::numeric) AS letter_grade, month_year FROM 
(SELECT b.part, a.semester, a.subject_code, b.subject_name, b.subject_credit, b.max_pass_mark, total_mark, 
CASE WHEN total_mark~E'".$exp."' THEN CAST(CAST(cast(total_mark as decimal)/10 as decimal(10,1)) as text) ELSE CAST(total_mark as text) END AS grade_point,
(SELECT exam_short_display FROM inst.trn_exam_config WHERE active_state='1' AND exam_id=a.exam_id) AS month_year
FROM student_result.stud_result_0619 a
JOIN inst.mst_overall_unique_subject_code_tvu b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer
WHERE univ_reg_no = '".$resQuery['univ_reg_no']."' AND delete_flg = '0'   AND display_status='Y') k
ORDER BY semester, part, subject_code";*/

			$selSubjectDetails = "SELECT part, semester, subject_code, subject_name, subject_credit, internal, external, total_mark, result, month_year FROM (SELECT b.part, a.semester, a.subject_code, b.subject_name, b.subject_credit, b.max_pass_mark, internal, external, total_mark,result,
--CASE WHEN total_mark~E'^\\d+$' THEN CAST(CAST(cast(total_mark as decimal)/10 as decimal(10,1)) as text) ELSE CAST(total_mark as text) END AS grade_point, 
(SELECT exam_name FROM inst.trn_exam_config WHERE active_state='1' AND exam_id=a.exam_id) AS month_year FROM student_result.stud_result_0619 a 
JOIN inst.mst_overall_unique_subject_code b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer WHERE univ_reg_no = '" . $resQuery['univ_reg_no'] . "' AND a.semester::integer='" . $_POST['semester_sm'] . "'::integer AND exam_id='" . $examid . "' 
AND delete_flg = '0'  AND display_status='Y') k 
GROUP BY  part, semester, subject_code, subject_name, subject_credit, internal, external, total_mark, result, month_year
ORDER BY semester, part, subject_code";

			$exeSubjectDetails = $db->query($selSubjectDetails);

			$arrayNumberNumeric = array(0, 1, 2, 3, 4, 5);

			$arrayNumberRoman = array("0", "I", "II", "III", "IV", "V");

			$html .= '<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
<tr> 
	<td colspan="12" style="text-align:center;width:881px;height:45px;">&nbsp;</td> 
</tr>
<tr>
<td colspan="12"  valign="top" style="height:638px;width:881px">
<table style="border-collapse:collapse;font-size:11px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
';


			if (($exeSubjectDetails->rowCount() > 0)) {


				while ($resSubjectDetails = $exeSubjectDetails->fetch(PDO::FETCH_ASSOC)) {

					//$keyPartFind = array_search($resSubjectDetails['part'], $arrayNumberNumeric);
					//$part = $arrayNumberRoman[$keyPartFind];

					$part = $resSubjectDetails['part'];

					$month = substr($resSubjectDetails['month_year'], 0, 3);
					$year = substr($resSubjectDetails['month_year'], -2);

					$res_month_year = $month . ' ' . $year;

					if ($resSubjectDetails['result'] == 'P') {
						$result = 'PASS';
					} else {
						$result = $resSubjectDetails['result'];
					}

					$totalMark = trim($resSubjectDetails['total_mark']);

					$gradePoint = "";
					$grade = "";
					if ($totalMark >= 90) {
						$gradePoint = "10";
						$grade = "O";
					} else if (($totalMark >= 80) && ($totalMark < 90)) {
						$gradePoint = "9";
						$grade = "A+";
					} else if (($totalMark >= 70) && ($totalMark < 80)) {
						$gradePoint = "8";
						$grade = "A";
					} else if (($totalMark >= 60) && ($totalMark < 70)) {
						$gradePoint = "7";
						$grade = "B+";
					} else if (($totalMark >= 50) && ($totalMark < 60)) {
						$gradePoint = "6";
						$grade = "B";
					} else if (($totalMark >= 40) && ($totalMark < 50)) {
						$gradePoint = "5";
						$grade = "C";
					} else if ($totalMark < 40) {
						$gradePoint = "NA";
						$grade = "RA";
					}


					//$total_mark = $resSubjectDetails['total_mark'];
					//$length = 3;
					//$res_total_mark = substr(str_repeat(0, $length).$total_mark, - $length);

					//$grade = $resSubjectDetails['letter_grade'];

					//$grade_point = $resSubjectDetails['grade_point'];


					$html .= '<tr><td  style="text-align:center;width:15px;height:18px;">&nbsp;</td> 
	
	<td style="text-align:left;width:117px;height:18px;">' . trim($resSubjectDetails['semester']) . '-' . trim($part) . '-' . trim(strtoupper($resSubjectDetails['subject_code'])) . '</td> 
	<td style="text-align:left;width:347px;height:18px;">' . trim(strtoupper($resSubjectDetails['subject_name'])) . '</td> 
	<td style="text-align:center;width:45px;height:18px;">' . trim($resSubjectDetails['internal']) . '</td> 
	<td style="text-align:center;width:45px;height:18px;">' . trim($resSubjectDetails['external']) . '</td> 
	<td style="text-align:center;width:49px;height:18px;">' . trim($resSubjectDetails['total_mark']) . '</td> 
	<td style="text-align:center;width:49px;height:18px;">' . trim($gradePoint) . '</td> 
	<td style="text-align:center;width:37px;height:18px;">' . trim($grade) . '</td> 
	<td style="text-align:center;width:37px;height:18px;">' . trim($resSubjectDetails['subject_credit']) . '</td> 
	<td style="text-align:center;width:49px;height:18px;">' . trim($result) . '</td> 
	<td style="text-align:center;width:71px;height:18px;">' . strtoupper($res_month_year) . '</td> 
	
	<td  style="text-align:center;width:11px;height:18px;">&nbsp;</td> 
</tr>';
				}
			}
			$html .= '<tr> 
<td style="text-align:center;width:15px;height:18px;">&nbsp;</td> 
<td style="text-align:center;width:117px;height:18px;">&nbsp;</td>
	<td style="text-align:center;width:347px;height:18px;font-weight:bold;">*** END ***</td> 
	<td colspan="8" style="text-align:center;width:385px;height:18px;">&nbsp;</td>
	<td style="text-align:center;width:11px;height:18px;">&nbsp;</td>
</tr></table>
</td>
</tr>
<tr> 
	<td colspan="12" style="text-align:left;width:887px;height:18px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RESULT DECALED ON : 11-08-2022, MARK STATEMENT ISSUED ON : 11-10-2022</td> 
</tr>
</table>';

			$qrvalue = "REGISTER NUMBER : " . $resQuery['univ_reg_no'] . ", CENTER : " . $resQuery['institution_code'] . ' & ' . strtoupper($resQuery['instname']) . ", DEGREE : " . $resQuery['degree'] . ", COURSE : " . $resQuery['course_name'];



			$exp = "^\\\d+$";

			//$exp = "/^\\d+$/";
			/*
$selGradeDetails = "SELECT part, SUM(subject_credit) AS total_credit, SUM(CAST(marks as decimal(10,2))) AS wam FROM (SELECT b.part, b.subject_credit, 
CASE WHEN total_mark~E'".$exp."' THEN CAST(total_mark as decimal)/subject_credit * subject_credit ELSE 0 END AS marks
FROM student_result.stud_result_0619 a JOIN inst.mst_overall_unique_subject_code b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer WHERE univ_reg_no = '".$resQuery['univ_reg_no']."' 
AND a.semester::integer='".$_POST['semester_sm']."'::integer AND exam_id='".$examid."'  AND delete_flg = '0'  AND display_status='Y' GROUP BY b.part, b.subject_credit,total_mark) j GROUP BY part ORDER BY part";
*/

			$selGradeDetails = "SELECT part, total_credit, CAST(marks as decimal(10,2)) AS wam, CAST(gradepoint_val as decimal(10,2)) AS sgpa 
FROM (SELECT part, SUM(subject_credit) AS total_credit, SUM(CASE WHEN total_mark~E'" . $exp . "' THEN CAST(total_mark as decimal) * subject_credit ELSE 0 END)/SUM(subject_credit) AS marks, SUM(CASE WHEN gradepoint~E'" . $exp . "' THEN CAST(gradepoint as decimal)* subject_credit ELSE 0 END)/SUM(subject_credit)  AS gradepoint_val FROM (SELECT b.part, b.subject_credit, total_mark,
CASE
WHEN total_mark >= '90' THEN '10'
WHEN total_mark >= '80' AND total_mark < '90' THEN '9'
WHEN total_mark >= '70' AND total_mark < '80' THEN '8'
WHEN total_mark >= '60' AND total_mark < '70' THEN '7'
WHEN total_mark >= '50' AND total_mark < '60' THEN '6'
WHEN total_mark >= '40' AND total_mark < '50' THEN '5'
WHEN total_mark < '40' THEN '0'
END AS gradepoint
FROM student_result.stud_result_0619 a JOIN inst.mst_overall_unique_subject_code b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer 
WHERE univ_reg_no = '" . $resQuery['univ_reg_no'] . "'  AND a.semester::integer='" . $_POST['semester_sm'] . "'::integer AND exam_id='" . $examid . "' AND delete_flg = '0'  AND display_status='Y' GROUP BY b.part, b.subject_credit,total_mark) c GROUP BY part) d";


			$exeGradeDetails = $db->query($selGradeDetails);

			$html .= '<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
<tr> 
	<td colspan="12" style="text-align:center;width:881px;height:45px;"></td> 
</tr>
<tr>
<td colspan="12"  valign="top" style="height:98px;width:881px">
<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">';

			if (($exeGradeDetails->rowCount() > 0)) {
				while ($resGradeDetails = $exeGradeDetails->fetch(PDO::FETCH_ASSOC)) {

					$keyPartFind = array_search($resGradeDetails['part'], $arrayNumberNumeric);

					$part = $arrayNumberRoman[$keyPartFind];

					$sgpa = trim($resGradeDetails['sgpa']);

					$sgpa_grade = "";
					if ($sgpa >= 9.00) {
						$sgpa_grade = "O";
					} else if (($sgpa >= 8.00) && ($sgpa <= 8.99)) {
						$sgpa_grade = "A+";
					} else if (($sgpa >= 7.00) && ($sgpa <= 7.99)) {
						$sgpa_grade = "A";
					} else if (($sgpa >= 6.00) && ($sgpa <= 6.99)) {
						$sgpa_grade = "B+";
					} else if (($sgpa >= 5.00) && ($sgpa <= 5.99)) {
						$sgpa_grade = "B";
					} else if (($sgpa >= 4.00) && ($sgpa <= 4.99)) {
						$sgpa_grade = "C";
					}


					$html .= '<tr> 
	<td  style="text-align:center;width:15px;height:18px;">&nbsp;</td> 
	<td style="text-align:left;width:75px;height:18px;">PART-' . $part . '</td> 
	<td style="text-align:center;width:68px;height:18px;">' . trim($resGradeDetails['wam']) . '</td> 
	<td style="text-align:center;width:68px;height:18px;">' . trim($resGradeDetails['sgpa']) . '</td> 
	<td style="text-align:left;width:56px;height:18px;">' . $sgpa_grade . '</td> 
	<td style="text-align:right;width:49px;height:18px;">' . trim($resGradeDetails['total_credit']) . '</td> 
	<td style="text-align:center;width:68px;height:18px;">' . trim($resGradeDetails['wam']) . '</td> 
	<td style="text-align:center;width:68px;height:18px;">' . trim($resGradeDetails['sgpa']) . '</td> 
	<td style="text-align:left;width:56px;height:18px;">' . trim($sgpa_grade) . '</td> 
	<td style="text-align:right;width:49px;height:18px;">' . trim($resGradeDetails['total_credit']) . '</td> 
	<td  style="text-align:left;width:298px;height:18px;">&nbsp;&nbsp;-------</td> 
	<td  style="text-align:center;width:11px;height:18px;">&nbsp;</td> 
</tr>';
				}
			}

			$html .= '</table>
</td>
</tr>

</table>';

			/*$html.='<table style="border-collapse:collapse;font-size:10px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
<tr> 
	<td style="text-align:center;width:15px;height:90px;"></td> 
	<td style="text-align:center;width:207px;height:90px;" valign="bottom">'.date('d.m.Y').'</td> 
	<td style="text-align:center;width:113px;height:90px;">
		<barcode code="'.$qrvalue.'" size="0.6" type="QR" error="M" class="barcode" /></td> 
	<td colspan="4" style="text-align:center;width:445px;height:90px;"></td>  
	<td style="text-align:center;width:15px;height:90px;"></td> 
</tr>
</table>';*/


			if ($exeQuery->rowCount() != $d) {
				$html .= "<pagebreak />";
			}

			$d++;

			$html .=	'</div>';
		}
	}
} else if ($graduation_id == '2') {

	$selQuery = "SELECT institution_code, stud_code, univ_reg_no, firstname, dob, current_semester, course, year_of_joining, graduation,
(SELECT institution_name FROM inst.mst_institution where a.institution_code::integer=institution_code::integer) as instname, 
(SELECT graduation_name FROM inst.mst_graduation where a.graduation::integer=graduation_id::integer) as graduation_name, 
(SELECT degree_name FROM inst.mst_degree where a.degree::integer=degree_id::integer) as degree, 
(SELECT inst.function_find_degree_short_code(a.course::integer)) AS degree_short_code, 
(SELECT inst.function_find_course_name(a.course::integer)) AS course_name, 
(SELECT inst.function_find_course_short_name(a.course::integer)) AS course_short_code, batch, academic_year
FROM inst.view_student_profile_ug_pg a WHERE stud_code::double precision IN (" . $stud . ") ORDER BY univ_reg_no asc";


	$exeQuery = $db->query($selQuery);


	//echo "S=====".$selQuery;
	//exit;


	$selExamName = "SELECT  exam_name FROM inst.trn_exam_config WHERE active_state='1'
AND exam_id='0619'";
	$exeExamName = $db->query($selExamName);
	$resExamName = $exeExamName->fetch(PDO::FETCH_ASSOC);
	$laststring = substr($resExamName['exam_name'], -4);
	$firststring = substr($resExamName['exam_name'], 0, 3);
	$term = $firststring . " " . $laststring;

	//strtoupper(date('F Y', strtotime(date('d-m-Y'))))


	if (($exeQuery->rowCount() > 0)) {
		$d = 1;

		while ($resQuery = $exeQuery->fetch(PDO::FETCH_ASSOC)) {
			$year_of_joining = $resQuery['year_of_joining'];

			$year_of_passing = "";
			if ($resQuery['graduation'] == '2') {
				$year_of_passing  = $year_of_joining + 2;
			}


			$year_of_study = $year_of_joining . '-' . $year_of_passing;

			$institution_code = ltrim($resQuery['institution_code'], "0");


			$html .= '

<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">';

			$html .= '
			<tr> 
	<td colspan="6" style="text-align:center;width:881px;height:136px;">&nbsp;</td> </tr>

			<tr> 
	<td  style="text-align:center;width:15px;height:41px;">&nbsp;</td> 
	<td  style="text-align:left;width:464px;height:41px;">' . trim(strtoupper($resQuery['firstname'])) . '</td> 
	<td  style="text-align:center;width:240px;height:41px;">' . trim(strtoupper($resQuery['univ_reg_no'])) . '</td> 
	<td  style="text-align:left;width:124px;height:41px;">U-0888464</td> 
	<td  style="text-align:center;width:11px;height:41px;">&nbsp;</td> </tr>';

			$html .= '</table>';



			$html .= '<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">
		<tr> 
	<td  style="text-align:center;width:15px;height:38px;">&nbsp;</td> 
	<td  style="text-align:center;width:119px;height:38px;">' . trim(strtoupper($degree_short_code)) . '</td> 
	<td  style="text-align:left;width:607px;height:38px;">' . trim(strtoupper($resQuery['course_name'])) . '</td> 
	<td  style="text-align:left;width:124px;height:38px;">' . trim($year_of_study) . '</td> 
	<td  style="text-align:center;width:11px;height:38px;">&nbsp;</td> 
</tr> 
		 </table>';


			$html .= '<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
<tr> 
	<td  style="text-align:center;width:15px;height:38px;">&nbsp;</td> 
	<td  style="text-align:center;width:75px;height:38px;">&nbsp;</td> 
	<td  style="text-align:left;width:531px;height:38px;">' . trim($institution_code) . ' - ' . trim(strtoupper($resQuery['instname'])) . '</td> 
	<td  style="text-align:center;width:11px;height:38px;">&nbsp;</td> 
</tr> 
</table>';

			/*
$selSubjectDetails = "SELECT b.part, a.semester, a.subject_code, b.subject_name, b.subject_credit, b.max_pass_mark, total_mark, 
CAST(cast(total_mark as decimal)/10 as decimal(10,1)) AS grade_point, 
(SELECT exam_short_display FROM inst.trn_exam_config WHERE active_state='1' AND exam_id=a.exam_id) AS month_year
FROM student_result.stud_result_0619 a
JOIN inst.mst_overall_unique_subject_code_tvu b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer
WHERE univ_reg_no = '".$resQuery['univ_reg_no']."' AND delete_flg = '0'   AND display_status='Y'
 ORDER BY semester, part, subject_code"; */


			$exp = "^\\\d+$";

			//$exp = "/^\\d+$/";
			/*echo "A==".$selSubjectDetails = "SELECT part, semester, subject_code, subject_name, subject_credit, max_pass_mark, total_mark, grade_point,
(SELECT letter_grade FROM inst.mst_grade_points WHERE  grade_point::numeric >= grade_points_min::numeric 
AND grade_point::numeric <= grade_points_max::numeric) AS letter_grade, month_year FROM 
(SELECT b.part, a.semester, a.subject_code, b.subject_name, b.subject_credit, b.max_pass_mark, total_mark, 
CASE WHEN total_mark~E'".$exp."' THEN CAST(CAST(cast(total_mark as decimal)/10 as decimal(10,1)) as text) ELSE CAST(total_mark as text) END AS grade_point,
(SELECT exam_short_display FROM inst.trn_exam_config WHERE active_state='1' AND exam_id=a.exam_id) AS month_year
FROM student_result.stud_result_0619 a
JOIN inst.mst_overall_unique_subject_code_tvu b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer
WHERE univ_reg_no = '".$resQuery['univ_reg_no']."' AND delete_flg = '0'   AND display_status='Y') k
ORDER BY semester, part, subject_code";*/

			$selSubjectDetails = "SELECT part, semester, subject_code, subject_name, subject_credit, internal, external, total_mark, result, month_year FROM (SELECT b.part, a.semester, a.subject_code, b.subject_name, b.subject_credit, b.max_pass_mark, internal, external, total_mark,result,
--CASE WHEN total_mark~E'^\\d+$' THEN CAST(CAST(cast(total_mark as decimal)/10 as decimal(10,1)) as text) ELSE CAST(total_mark as text) END AS grade_point, 
(SELECT exam_name FROM inst.trn_exam_config WHERE active_state='1' AND exam_id=a.exam_id) AS month_year FROM student_result.stud_result_0619 a 
JOIN inst.mst_overall_unique_subject_code b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer WHERE univ_reg_no = '" . $resQuery['univ_reg_no'] . "' AND a.semester::integer='" . $_POST['semester_sm'] . "'::integer AND exam_id='" . $examid . "' 
AND delete_flg = '0'  AND display_status='Y') k 
GROUP BY  part, semester, subject_code, subject_name, subject_credit, internal, external, total_mark, result, month_year
ORDER BY semester, part, subject_code";

			$exeSubjectDetails = $db->query($selSubjectDetails);

			$arrayNumberNumeric = array(0, 1, 2, 3, 4, 5);

			$arrayNumberRoman = array("0", "I", "II", "III", "IV", "V");

			$html .= '<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
<tr> 
	<td colspan="12" style="text-align:center;width:881px;height:49px;">&nbsp;</td> 
</tr>
<tr>
<td colspan="12"  valign="top" style="height:650px;width:881px">
<table style="border-collapse:collapse;font-size:11px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
';


			if (($exeSubjectDetails->rowCount() > 0)) {


				while ($resSubjectDetails = $exeSubjectDetails->fetch(PDO::FETCH_ASSOC)) {

					//$keyPartFind = array_search($resSubjectDetails['part'], $arrayNumberNumeric);
					//$part = $arrayNumberRoman[$keyPartFind];

					$part = $resSubjectDetails['part'];

					$month = substr($resSubjectDetails['month_year'], 0, 3);
					//$year = substr($resSubjectDetails['month_year'], -2);
					$year = substr($resSubjectDetails['month_year'], -4);

					$res_month_year = $month . ' ' . $year;

					if ($resSubjectDetails['result'] == 'P') {
						$result = 'PASS';
					} else {
						$result = $resSubjectDetails['result'];
					}

					$totalMark = trim($resSubjectDetails['total_mark']);

					$gradePoint = "";
					$grade = "";
					if ($totalMark >= 90) {
						$gradePoint = "10";
						$grade = "O";
					} else if (($totalMark >= 80) && ($totalMark < 90)) {
						$gradePoint = "9";
						$grade = "A+";
					} else if (($totalMark >= 70) && ($totalMark < 80)) {
						$gradePoint = "8";
						$grade = "A";
					} else if (($totalMark >= 60) && ($totalMark < 70)) {
						$gradePoint = "7";
						$grade = "B+";
					} else if (($totalMark >= 50) && ($totalMark < 60)) {
						$gradePoint = "6";
						$grade = "B";
					} else if ($totalMark < 50) {
						$gradePoint = "NA";
						$grade = "RA";
					}


					//$total_mark = $resSubjectDetails['total_mark'];
					//$length = 3;
					//$res_total_mark = substr(str_repeat(0, $length).$total_mark, - $length);

					//$grade = $resSubjectDetails['letter_grade'];

					//$grade_point = $resSubjectDetails['grade_point'];


					$html .= '<tr><td  style="text-align:center;width:15px;height:18px;">&nbsp;</td> 
	
	<td style="text-align:left;width:130px;height:18px;">' . trim($resSubjectDetails['semester']) . '-' . trim($part) . '-' . trim(strtoupper($resSubjectDetails['subject_code'])) . '</td> 
	<td style="text-align:left;width:334px;height:18px;">' . trim(strtoupper($resSubjectDetails['subject_name'])) . '</td> 
	<td style="text-align:center;width:45px;height:18px;">' . trim($resSubjectDetails['internal']) . '</td> 
	<td style="text-align:center;width:39px;height:18px;">' . trim($resSubjectDetails['external']) . '</td> 
	<td style="text-align:center;width:53px;height:18px;">' . trim($resSubjectDetails['total_mark']) . '</td> 
	<td style="text-align:center;width:45px;height:18px;">' . trim($gradePoint) . '</td> 
	<td style="text-align:left;width:37px;height:18px;">' . trim($grade) . '</td> 
	<td style="text-align:center;width:37px;height:18px;">' . trim($resSubjectDetails['subject_credit']) . '</td> 
	<td style="text-align:center;width:49px;height:18px;">' . trim($result) . '</td> 
	<td style="text-align:center;width:75px;height:18px;">' . strtoupper($res_month_year) . '</td> 
	
	<td  style="text-align:center;width:11px;height:18px;">&nbsp;</td> 
</tr>';
				}
			}
			$html .= '<tr> 
<td style="text-align:center;width:15px;height:18px;">&nbsp;</td> 
<td style="text-align:center;width:130px;height:18px;">&nbsp;</td>
	<td style="text-align:center;width:334px;height:18px;font-weight:bold;">**** END OF STATEMENT ****</td> 
	<td colspan="8" style="text-align:center;width:385px;height:18px;">&nbsp;</td>
	<td style="text-align:center;width:11px;height:18px;">&nbsp;</td>
</tr></table>
</td>
</tr>
<tr> 
	<td colspan="12" style="text-align:left;width:887px;height:18px;word-spacing: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Declaration: A Candidate is declared to have completed the Post-Graduate <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Programme successfully only when the Cumulative Credits Earned is a minimum of 90. <br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RESULT DECALED ON : 11-08-2022, MARK STATEMENT ISSUED ON : 11-11-2022</td> 
</tr>
</table>';

			$qrvalue = "REGISTER NUMBER : " . $resQuery['univ_reg_no'] . ", CENTER : " . $resQuery['institution_code'] . ' & ' . strtoupper($resQuery['instname']) . ", DEGREE : " . $resQuery['degree'] . ", COURSE : " . $resQuery['course_name'];



			$exp = "^\\\d+$";

			//$exp = "/^\\d+$/";
			/*
$selGradeDetails = "SELECT part, SUM(subject_credit) AS total_credit, SUM(CAST(marks as decimal(10,2))) AS wam FROM (SELECT b.part, b.subject_credit, 
CASE WHEN total_mark~E'".$exp."' THEN CAST(total_mark as decimal)/subject_credit * subject_credit ELSE 0 END AS marks
FROM student_result.stud_result_0619 a JOIN inst.mst_overall_unique_subject_code b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer WHERE univ_reg_no = '".$resQuery['univ_reg_no']."' 
AND a.semester::integer='".$_POST['semester_sm']."'::integer AND exam_id='".$examid."'  AND delete_flg = '0'  AND display_status='Y' GROUP BY b.part, b.subject_credit,total_mark) j GROUP BY part ORDER BY part";
*/

			$selGradeDetails = "SELECT part, total_credit, CAST(marks as decimal(10,2)) AS wam, CAST(gradepoint_val as decimal(10,2)) AS sgpa 
FROM (SELECT part, SUM(subject_credit) AS total_credit, SUM(CASE WHEN total_mark~E'" . $exp . "' THEN CAST(total_mark as decimal) * subject_credit ELSE 0 END)/SUM(subject_credit) AS marks, SUM(CASE WHEN gradepoint~E'" . $exp . "' THEN CAST(gradepoint as decimal)* subject_credit ELSE 0 END)/SUM(subject_credit)  AS gradepoint_val FROM (SELECT b.part, b.subject_credit, total_mark,
CASE
WHEN total_mark >= '90' THEN '10'
WHEN total_mark >= '80' AND total_mark < '90' THEN '9'
WHEN total_mark >= '70' AND total_mark < '80' THEN '8'
WHEN total_mark >= '60' AND total_mark < '70' THEN '7'
WHEN total_mark >= '50' AND total_mark < '60' THEN '6'
WHEN total_mark < '50' THEN '0'
END AS gradepoint
FROM student_result.stud_result_0619 a JOIN inst.mst_overall_unique_subject_code b ON a.course_id::integer=b.course_id::integer AND a.subject_unique_code::integer=b.subject_id::integer 
WHERE univ_reg_no = '" . $resQuery['univ_reg_no'] . "'  AND a.semester::integer='" . $_POST['semester_sm'] . "'::integer AND exam_id='" . $examid . "' AND delete_flg = '0'  AND display_status='Y' GROUP BY b.part, b.subject_credit,total_mark) c GROUP BY part) d";


			$exeGradeDetails = $db->query($selGradeDetails);

			$html .= '<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
<tr> 
	<td colspan="12" style="text-align:center;width:881px;height:32px;"></td> 
</tr>
<tr>
<td colspan="12"  valign="top" style="height:45px;width:881px">
<table style="border-collapse:collapse;font-size:13px;font-style: normal;font-family:Didot, serif" border="0" width="100%">';

			if (($exeGradeDetails->rowCount() > 0)) {
				while ($resGradeDetails = $exeGradeDetails->fetch(PDO::FETCH_ASSOC)) {

					$keyPartFind = array_search($resGradeDetails['part'], $arrayNumberNumeric);

					$part = $arrayNumberRoman[$keyPartFind];

					$sgpa = trim($resGradeDetails['sgpa']);

					$sgpa_grade = "";
					if ($sgpa >= 9.00) {
						$sgpa_grade = "O";
					} else if (($sgpa >= 8.00) && ($sgpa <= 8.99)) {
						$sgpa_grade = "A+";
					} else if (($sgpa >= 7.00) && ($sgpa <= 7.99)) {
						$sgpa_grade = "A";
					} else if (($sgpa >= 6.00) && ($sgpa <= 6.99)) {
						$sgpa_grade = "B+";
					} else if (($sgpa >= 5.00) && ($sgpa <= 5.99)) {
						$sgpa_grade = "B";
					}


					$html .= '<tr> 
	<td  style="text-align:center;width:15px;height:18px;">&nbsp;</td> 
	<td style="text-align:left;width:75px;height:18px;">' . trim($resGradeDetails['wam']) . '</td> 
	<td style="text-align:center;width:68px;height:18px;">' . trim($resGradeDetails['sgpa']) . '</td> 
	<td style="text-align:center;width:51px;height:18px;">' . trim($sgpa_grade) . '</td> 
	<td style="text-align:left;width:52px;height:18px;">' . trim($resGradeDetails['total_credit']) . '</td> 
	<td style="text-align:right;width:68px;height:18px;">' . trim($resGradeDetails['wam']) . '</td> 
	<td style="text-align:center;width:71px;height:18px;">' . trim($resGradeDetails['sgpa']) . '</td> 
	<td style="text-align:center;width:52px;height:18px;">' . trim($sgpa_grade) . '</td> 
	<td style="text-align:left;width:49px;height:18px;">' . trim($resGradeDetails['total_credit']) . '</td> 
	<td  style="text-align:left;width:364px;height:18px;">&nbsp;&nbsp;-------</td> 
	<td  style="text-align:center;width:11px;height:18px;">&nbsp;</td> 
</tr>';
				}
			}

			$html .= '</table>
</td>
</tr>

</table>';

			/*$html.='<table style="border-collapse:collapse;font-size:10px;font-style: normal;font-family:Didot, serif" border="0" width="100%">	
<tr> 
	<td style="text-align:center;width:15px;height:90px;"></td> 
	<td style="text-align:center;width:207px;height:90px;" valign="bottom">'.date('d.m.Y').'</td> 
	<td style="text-align:center;width:113px;height:90px;">
		<barcode code="'.$qrvalue.'" size="0.6" type="QR" error="M" class="barcode" /></td> 
	<td colspan="4" style="text-align:center;width:445px;height:90px;"></td>  
	<td style="text-align:center;width:15px;height:90px;"></td> 
</tr>
</table>';*/


			if ($exeQuery->rowCount() != $d) {
				$html .= "<pagebreak />";
			}

			$d++;

			$html .=	'</div>';
		}
	}
}

$mpdf->SetX(0);
//$mpdf->SetX(0);
$mpdf->WriteHTML($html);
//$mpdf->AddPage(); 
$html = "";

$today = date('d-m-Y H:i:s');

$mpdf->Output('student_semester_marksheet_' . $today . '.pdf', 'I');
exit;

?>