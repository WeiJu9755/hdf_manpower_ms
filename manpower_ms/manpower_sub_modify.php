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
	
	$memberID				= trim($aFormValues['memberID']);
	$auto_seq				= trim($aFormValues['auto_seq']);
	//$engineering_overview	= trim($aFormValues['engineering_overview']);
	$builder_id				= trim($aFormValues['builder_id']);
	$layout_number				= trim($aFormValues['layout_number']);
	
	/*
	if (trim($aFormValues['engineering_overview']) == "")	{
		$objResponse->script("jAlert('警示', '請輸入工程概況', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	*/
	/*
	if (trim($aFormValues['member_no']) != "")	{
		//檢查會員帳號是否正確
		$member_row = getkeyvalue2("memberinfo","member","member_no = '$member_no'","count(member_no) as m_count");
		$m_count = $member_row['m_count'];
		if ($m_count <= 0)	{
			$objResponse->script("jAlert('警示', '您輸入的會員帳號不存在', 'red', '', 2000);");
			return $objResponse;
			exit;
		}
	}
	*/
	
	SaveValue($aFormValues);
	
	$objResponse->script("setSave();");
	$objResponse->script("parent.manpower_sub_myDraw();");

	$objResponse->script("art.dialog.tips('已存檔!',1);");
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
		//$engineering_overview 	= htmlspecialchars(trim($aFormValues['engineering_overview']), ENT_QUOTES, 'UTF-8');
		$eng_description 		= htmlspecialchars(trim($aFormValues['eng_description']), ENT_QUOTES, 'UTF-8');
		$builder_id 			= trim($aFormValues['builder_id']);
		$layout_number 			= trim($aFormValues['layout_number']);
		$scheduled_completion_date = trim($aFormValues['scheduled_completion_date']);
		$actual_completion_date = trim($aFormValues['actual_completion_date']);
		$scheduled_entry_date 	= trim($aFormValues['scheduled_entry_date']);
		$actual_entry_date 		= trim($aFormValues['actual_entry_date']);
		$construction_days_per_floor = trim($aFormValues['construction_days_per_floor']);
		$works_per_floor 		= trim($aFormValues['works_per_floor']);
		$standard_manpower 		= trim($aFormValues['standard_manpower']);
		$site_manager 			= htmlspecialchars(trim($aFormValues['site_manager']), ENT_QUOTES, 'UTF-8');
		$superintendent 		= htmlspecialchars(trim($aFormValues['superintendent']), ENT_QUOTES, 'UTF-8');
		$scaffold 				= trim($aFormValues['scaffold']);
		$rebar 					= trim($aFormValues['rebar']);
		$hydropower 			= trim($aFormValues['hydropower']);
		$layout 				= trim($aFormValues['layout']);
		$concrete 				= trim($aFormValues['concrete']);
		$concrete_plant 		= trim($aFormValues['concrete_plant']);
		$masonry 				= trim($aFormValues['masonry']);
		$painting 				= trim($aFormValues['painting']);
		$drywall 				= trim($aFormValues['drywall']);
		$responsible 			= trim($aFormValues['responsible']);
		$maincontractor_pricing_staff = trim($aFormValues['maincontractor_pricing_staff']);
		$subcontractor_pricing_staff = trim($aFormValues['subcontractor_pricing_staff']);

		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();

		$Qry="UPDATE overview_sub set
				 eng_description	= '$eng_description'
				,builder_id			= '$builder_id'
				,layout_number		= '$layout_number'
				,scheduled_completion_date = '$scheduled_completion_date'
				,actual_completion_date = '$actual_completion_date'
				,scheduled_entry_date = '$scheduled_entry_date'
				,actual_entry_date 	= '$actual_entry_date'
				,construction_days_per_floor = '$construction_days_per_floor'
				,works_per_floor	= '$works_per_floor'
				,standard_manpower	= '$standard_manpower'
				,site_manager		= '$site_manager'
				,superintendent		= '$superintendent'
				,scaffold			= '$scaffold'
				,rebar				= '$rebar'
				,hydropower			= '$hydropower'
				,layout				= '$layout'
				,concrete			= '$concrete'
				,concrete_plant		= '$concrete_plant'
				,masonry			= '$masonry'
				,painting			= '$painting'
				,drywall			= '$drywall'
				,responsible		= '$responsible'
				,maincontractor_pricing_staff = '$maincontractor_pricing_staff'
				,subcontractor_pricing_staff = '$subcontractor_pricing_staff'
				,makeby				= '$memberID'
				,last_modify		= now()
				where auto_seq = '$auto_seq'";
				
		$mDB->query($Qry);
        $mDB->remove();

		
	return $objResponse;
}

$xajax->processRequest();


$auto_seq = $_GET['auto_seq'];
$case_id = $_GET['case_id'];



$mDB = "";
$mDB = new MywebDB();


$fm = $_GET['fm'];

$mess_title = $title;


$Qry="SELECT a.*,b.employee_name as responsible_name,c.employee_name as maincontractor_name,d.employee_name as subcontractor_name FROM overview_sub a
LEFT JOIN employee b ON b.employee_id = a.responsible
LEFT JOIN employee c ON c.employee_id = a.maincontractor_pricing_staff
LEFT JOIN employee d ON d.employee_id = a.subcontractor_pricing_staff
WHERE a.auto_seq = '$auto_seq'";
$mDB->query($Qry);
$total = $mDB->rowCount();
if ($total > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$engineering_overview = $row['engineering_overview'];
	$eng_description = $row['eng_description'];
	$builder_id = $row['builder_id'];
	$layout_number = $row['layout_number'];
	$scheduled_completion_date = $row['scheduled_completion_date'];
	$actual_completion_date = $row['actual_completion_date'];
	$scheduled_entry_date = $row['scheduled_entry_date'];
	$actual_entry_date = $row['actual_entry_date'];
	$construction_days_per_floor = $row['construction_days_per_floor'];
	$works_per_floor = $row['works_per_floor'];
	$standard_manpower = $row['standard_manpower'];
	$site_manager = $row['site_manager'];
	$superintendent = $row['superintendent'];
	$scaffold = $row['scaffold'];
	$rebar = $row['rebar'];
	$hydropower = $row['hydropower'];
	$layout = $row['layout'];
	$concrete = $row['concrete'];
	$concrete_plant = $row['concrete_plant'];
	$masonry = $row['masonry'];
	$painting = $row['painting'];
	$drywall = $row['drywall'];
	$responsible = $row['responsible'];
	$responsible_name = $row['responsible_name'];
	$maincontractor_pricing_staff = $row['maincontractor_pricing_staff'];
	$maincontractor_name = $row['maincontractor_name'];
	$subcontractor_pricing_staff = $row['subcontractor_pricing_staff'];
	$subcontractor_name = $row['subcontractor_name'];

}

/*
$Qry="SELECT company_id,company_name FROM company ORDER BY company_id";
$mDB->query($Qry);
$select_company = "";
$select_company .= "<option></option>";
if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_company_id = $row['company_id'];
		$ch_company_name = $row['company_name'];
		$select_company .= "<option value=\"$ch_company_id\" ".mySelect($ch_company_id,$company_id).">$ch_company_name $ch_company_id</option>";
	}
}
*/

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
								<div class="field_div2">
									<div class="inline code_class">案件編號:</div>
									<div class="inline" style="padding:8px 0;font-size:18px;color:blue;text-align:left;font-weight:700;">$case_id</div>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">工程概況:</div> 
								<div class="inline mt-2" style="width:100%;max-width:900px;">
									<div class="inline" style="padding:8px 0;font-size:18px;color:blue;text-align:left;font-weight:700;">$engineering_overview</div>
									<!--
									<input type="text" class="inputtext" id="engineering_overview" name="engineering_overview" size="50" maxlength="120" style="width:100%;max-width:872px;" value="$engineering_overview" onchange="setEdit();"/>
									-->
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">工程說明:</div> 
								<div class="inline mt-2" style="width:100%;max-width:900px;">
									<input type="text" class="inputtext" id="eng_description" name="eng_description" size="50" maxlength="120" style="width:100%;max-width:872px;" value="$eng_description" onchange="setEdit();"/>
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