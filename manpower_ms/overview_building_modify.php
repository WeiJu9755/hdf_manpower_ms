<?php

session_start();

$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;


//載入公用函數
@include_once '/website/include/pub_function.php';

//連結資料
@include_once("/website/class/".$site_db."_info_class.php");

/* 使用xajax */
@include_once '/website/xajax/xajax_core/xajax.inc.php';
$xajax = new xajax();

$xajax->registerFunction("processform");
function processform($aFormValues){

	$objResponse = new xajaxResponse();
	
	SaveValue($aFormValues);
	
	$objResponse->script("setSave();");
	$objResponse->script("parent.overview_building_myDraw();");
	$objResponse->script("parent.$.fancybox.close();");
		
	
	return $objResponse;
}


$xajax->registerFunction("SaveValue");
function SaveValue($aFormValues){

	$objResponse = new xajaxResponse();
	
		//進行存檔動作
		$site_db				= trim($aFormValues['site_db']);
		$memberID				= trim($aFormValues['memberID']);
		$auto_seq				= trim($aFormValues['auto_seq']);
		$eng_description 		= trim($aFormValues['eng_description']);
		$layout 				= trim($aFormValues['layout']);
		$layout_number 			= trim($aFormValues['layout_number']);
		$scheduled_completion_date = trim($aFormValues['scheduled_completion_date']);
		$actual_completion_date = trim($aFormValues['actual_completion_date']);
		$scheduled_entry_date 	= trim($aFormValues['scheduled_entry_date']);
		$actual_entry_date 		= trim($aFormValues['actual_entry_date']);
		$construction_days_per_floor = trim($aFormValues['construction_days_per_floor']);
		$works_per_floor 		= trim($aFormValues['works_per_floor']);
		$standard_manpower 		= trim($aFormValues['standard_manpower']);
		$builder_id 			= trim($aFormValues['builder_id']);
		$case_id				= trim($aFormValues['case_id']);

		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();
		$mDB2 = "";
		$mDB2 = new MywebDB();

		$Qry="UPDATE overview_building set
				 eng_description	= '$eng_description'
				,layout				= '$layout'
				,layout_number		= '$layout_number'
				,scheduled_completion_date = '$scheduled_completion_date'
				,actual_completion_date = '$actual_completion_date'
				,scheduled_entry_date = '$scheduled_entry_date'
				,actual_entry_date 	= '$actual_entry_date'
				,construction_days_per_floor = '$construction_days_per_floor'
				,works_per_floor	= '$works_per_floor'
				,standard_manpower	= '$standard_manpower'
				,builder_id			= '$builder_id'
				,makeby				= '$memberID'
				,last_modify		= now()
				where auto_seq = '$auto_seq'";

		$mDB->query($Qry);

		// 更新主檔
		$Qry2="UPDATE CaseManagement 
			SET last_modify8 = NOW(), makeby8 = '$memberID' 
			WHERE case_id = '$case_id'";
		$mDB2->query($Qry2);
				
        $mDB->remove();
		$mDB2->remove();

		
	return $objResponse;
}

$xajax->processRequest();


$auto_seq = $_GET['auto_seq'];
$case_id = $_GET['case_id'];



$mDB = "";
$mDB = new MywebDB();


$fm = $_GET['fm'];

$mess_title = $title;


$Qry="SELECT * FROM overview_building
WHERE auto_seq = '$auto_seq'";
$mDB->query($Qry);
$total = $mDB->rowCount();
if ($total > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$building = $row['building'];
	$eng_description = $row['eng_description'];
	$layout = $row['layout'];
	$layout_number = $row['layout_number'];
	$scheduled_completion_date = $row['scheduled_completion_date'];
	$actual_completion_date = $row['actual_completion_date'];
	$scheduled_entry_date = $row['scheduled_entry_date'];
	$actual_entry_date = $row['actual_entry_date'];
	$construction_days_per_floor = $row['construction_days_per_floor'];
	$works_per_floor = $row['works_per_floor'];
	$standard_manpower = $row['standard_manpower'];
	$builder_id = $row['builder_id'];
}

//載入下包商-代工單位
$Qry="select subcontractor_id,subcontractor_name from subcontractor order by auto_seq";
$mDB->query($Qry);
$select_builder = "";
$select_builder .= "<option></option>";
if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_builder_id = $row['subcontractor_id'];
		$ch_builder_name = $row['subcontractor_name'];
		$select_builder .= "<option value=\"$ch_builder_id\" ".mySelect($ch_builder_id,$builder_id).">$ch_builder_id $ch_builder_name</option>";
	}
}

//載入下包商-放樣
$Qry="select subcontractor_id,subcontractor_name from subcontractor order by auto_seq";
$mDB->query($Qry);
$select_layout = "";
$select_layout .= "<option></option>";
if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_layout = $row['subcontractor_id'];
		$ch_layout_name = $row['subcontractor_name'];
		$select_layout .= "<option value=\"$ch_layout\" ".mySelect($ch_layout,$layout).">$ch_layout $ch_layout_name</option>";
	}
}

$mDB->remove();


$show_savebtn=<<<EOT
<div class="btn-group vbottom" role="group" style="margin-top:5px;">
	<button id="save" class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 5px 15px;"><i class="bi bi-check-circle"></i>&nbsp;存檔</button>
	<button id="cancel" class="btn btn-secondary display_none" type="button" onclick="setCancel();" style="padding: 5px 15px;"><i class="bi bi-x-circle"></i>&nbsp;取消</button>
	<button id="close" class="btn btn-danger" type="button" onclick="parent.$.fancybox.close();" style="padding: 5px 15px;"><i class="bi bi-power"></i>&nbsp;關閉</button>
</div>
EOT;


if (!($detect->isMobile() && !$detect->isTablet())) {
	$isMobile = 0;
	
$style_css=<<<EOT
<style>

.card_full {
    width: 100%;
	height: 100vh;
}

#full {
    width: 100%;
	height: 100%;
}

#info_container {
	width: 100% !Important;
	max-width: 1240px; !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:150px;display: none;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;max-width:400px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div3 {width:100%;max-width:800px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

.code_class {
	width:150px;
	text-align:right;
	padding:0 10px 0 0;
}

.maxwidth {
    width: 100%;
    max-width: 300px;
}

</style>

EOT;

} else {
	$isMobile = 1;

$style_css=<<<EOT
<style>

.card_full {
    width: 100%;
	height: 100vh;
}

#full {
    width: 100%;
	height: 100%;
}

#info_container {
	width: 100% !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:15px 10px 0 0;vertical-align: top;}
.field_div2 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 10px 0 0;vertical-align: top;}
.field_div3 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 10px 0 0;vertical-align: top;}

.code_class {
	width:auto;
	text-align:left;
	padding:0 10px 0 0;
}

.maxwidth {
    width: 100%;
}

</style>
EOT;

}



$show_center=<<<EOT
$style_css
<div class="card card_full">
	<div class="card-header text-bg-info">
		<div class="size14 weight float-start" style="margin-top: 5px;">
			$mess_title
		</div>
		<div class="float-end" style="margin-top: -5px;">
			$show_savebtn
		</div>
	</div>
	<div id="full" class="card-body data-overlayscrollbars-initialize">
		<div id="info_container">
			<form method="post" id="modifyForm" name="modifyForm" enctype="multipart/form-data" action="javascript:void(null);">
			<div class="w-100 mb-5">
				<div class="field_container3">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">棟別:</div> 
								<div class="inline mt-2" style="width:100%;max-width:900px;">
									<div class="inline size14 weight blue02 mt-2">$building</div>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">工程說明:</div> 
								<div class="field_div3">
									<input type="text" class="inputtext" id="eng_description" name="eng_description" size="50" maxlength="120" style="width:100%;max-width:800px;" value="$eng_description" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">放樣單位:</div> 
								<div class="inline mt-2">
									<select id="layout" name="layout" placeholder="請選擇" style="width:100%;max-width:350px;">
										$select_layout
									</select>
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">放樣人數:</div> 
								<div class="field_div2">
									<input type="text" class="inputtext" id="layout_number" name="layout_number" size="20" style="width:100%;max-width:80px;" value="$layout_number" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">預訂完工日:</div> 
								<div class="field_div2">
									<div class="input-group" id="scheduled_completion_date" style="width:100%;max-width:250px;">
										<input type="text" class="form-control" name="scheduled_completion_date" placeholder="請輸入預訂完工日" aria-describedby="scheduled_completion_date" value="$scheduled_completion_date">
										<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#scheduled_completion_date" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
									</div>
									<script type="text/javascript">
										$(function () {
											$('#scheduled_completion_date').datetimepicker({
												locale: 'zh-tw'
												,format:"YYYY-MM-DD"
												,allowInputToggle: true
											});
										});
									</script>
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">實際完工日:</div> 
								<div class="field_div2">
									<div class="input-group" id="actual_completion_date" style="width:100%;max-width:250px;">
										<input type="text" class="form-control" name="actual_completion_date" placeholder="請輸入實際完工日" aria-describedby="actual_completion_date" value="$actual_completion_date">
										<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#actual_completion_date" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
									</div>
									<script type="text/javascript">
										$(function () {
											$('#actual_completion_date').datetimepicker({
												locale: 'zh-tw'
												,format:"YYYY-MM-DD"
												,allowInputToggle: true
											});
										});
									</script>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">預訂進場日:</div> 
								<div class="field_div2">
									<div class="input-group" id="scheduled_entry_date" style="width:100%;max-width:250px;">
										<input type="text" class="form-control" name="scheduled_entry_date" placeholder="請輸入預訂進場日" aria-describedby="scheduled_entry_date" value="$scheduled_entry_date">
										<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#scheduled_entry_date" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
									</div>
									<script type="text/javascript">
										$(function () {
											$('#scheduled_entry_date').datetimepicker({
												locale: 'zh-tw'
												,format:"YYYY-MM-DD"
												,allowInputToggle: true
											});
										});
									</script>
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">實際進場日:</div> 
								<div class="field_div2">
									<div class="input-group" id="actual_entry_date" style="width:100%;max-width:250px;">
										<input type="text" class="form-control" name="actual_entry_date" placeholder="請輸入實際進場日" aria-describedby="actual_entry_date" value="$actual_entry_date">
										<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#actual_entry_date" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
									</div>
									<script type="text/javascript">
										$(function () {
											$('#actual_entry_date').datetimepicker({
												locale: 'zh-tw'
												,format:"YYYY-MM-DD"
												,allowInputToggle: true
											});
										});
									</script>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">每層施工天數:</div> 
								<div class="field_div2">
									<input type="text" class="inputtext maxwidth" id="construction_days_per_floor" name="construction_days_per_floor" size="50"  maxlength="50" value="$construction_days_per_floor" onchange="setEdit();"/>
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">每層工程量(M2):</div> 
								<div class="field_div2">
									<input type="text" class="inputtext maxwidth" id="works_per_floor" name="works_per_floor" size="50"  maxlength="50" value="$works_per_floor" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">標準人力需求:</div> 
								<div class="field_div2">
									<input type="text" class="inputtext maxwidth" id="standard_manpower" name="standard_manpower" size="50"  maxlength="50" value="$standard_manpower" onchange="setEdit();"/>
								</div> 
							</div> 
							<div class="col-lg-6 col-sm-12 col-md-12">
								<div class="field_div1">代工單位:</div> 
								<div class="field_div2">
									<select id="builder_id" name="builder_id" placeholder="請選擇代工單位" style="width:100%;max-width:350px;">
										$select_builder
									</select>
								</div> 
							</div> 
						</div>
					</div>
					<div>
						<input type="hidden" name="fm" value="$fm" />
						<input type="hidden" name="site_db" value="$site_db" />
						<input type="hidden" name="memberID" value="$memberID" />
						<input type="hidden" name="case_id" value="$case_id" />
						<input type="hidden" name="auto_seq" value="$auto_seq" />
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>
<script>

function CheckValue(thisform) {
	xajax_processform(xajax.getFormValues('modifyForm'));
	thisform.submit();
}

function SaveValue(thisform) {
	xajax_SaveValue(xajax.getFormValues('modifyForm'));
	thisform.submit();
}

function setEdit() {
	$('#close', window.document).addClass("display_none");
	$('#cancel', window.document).removeClass("display_none");
}

function setCancel() {
	$('#close', window.document).removeClass("display_none");
	$('#cancel', window.document).addClass("display_none");
	document.forms[0].reset();
}

function setSave() {
	$('#close', window.document).removeClass("display_none");
	$('#cancel', window.document).addClass("display_none");
}

/*
$(document).ready(async function() {
	//等待其他資源載入完成，此方式適用大部份瀏覽器
	await new Promise(resolve => setTimeout(resolve, 100));
	$('#engineering_overview').focus();
});
*/
</script>

EOT;

?>