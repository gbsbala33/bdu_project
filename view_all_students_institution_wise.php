<?php

// MODIFIED BY NICBA ON : 17122025.

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


// CONTENT REMOVED BY SIJITHRA
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