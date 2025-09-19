<?php

session_start();

$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


function isValidDate($date) {
    if (empty($date) || $date === null) {
        return false; // 空的或 null
    }
    
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    return $dateObj && $dateObj->format('Y-m-d') === $date;
}

require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;

function dateDiffDays(string $date1, string $date2, bool $withSign = false): int {
    $d1 = new DateTime($date1);
    $d2 = new DateTime($date2);
    $diff = $d1->diff($d2);

    if ($withSign && $diff->invert) {
        return -$diff->days;
    }

    return $diff->days;
}

	$mDB = "";
	$mDB = new MywebDB();

//人員差異
$Qry = "SELECT manpower_type FROM overview_manpower_sub
WHERE auto_seq = '$auto_seq'";

$select_manpower_type  = "";
$select_manpower_type .= "<option></option>";

// 手動定義選項
$manpower_type_options = array("效率好", "每日施作面積增加");

foreach ($manpower_type_options as $ch_caption) {
    $select_manpower_type .= "<option value=\"$ch_caption\" " . mySelect($ch_caption, $manpower_type) . ">$ch_caption</option>";
}



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
	$engineering_date		= trim($aFormValues['engineering_date']);
	$scheduled_entry_date_per_floor	= trim($aFormValues['scheduled_entry_date_per_floor']);
	$floor_list				= trim($aFormValues['floor_list']);
	$standard_manpower		= trim($aFormValues['standard_manpower']);
	$available_manpower		= trim($aFormValues['available_manpower']);
	$actual_manpower		= trim($aFormValues['actual_manpower']);
	$manpower_type			= trim($aFormValues['manpower_type']);
	
		$manpower_gap = max(0, 
    ($actual_manpower !== "" && $actual_manpower !== 0)
        ? $standard_manpower - $actual_manpower
        : $standard_manpower - $available_manpower
);

	//取得原來未修改前的進廠日期
	$overview_manpower_sub_row = getkeyvalue2("eshop_info","overview_manpower_sub","auto_seq = '$auto_seq'","engineering_date,case_id,seq,scheduled_entry_date");
	$org_engineering_date = $overview_manpower_sub_row['engineering_date'];
	$org_scheduled_entry_date_per_floor = $overview_manpower_sub_row['scheduled_entry_date'];
	$case_id = $overview_manpower_sub_row['case_id'];
	$seq = $overview_manpower_sub_row['seq'];

	//存入實體資料庫中
	$mDB = "";
	$mDB = new MywebDB();

	$mDB2 = "";
	$mDB2 = new MywebDB();

	$mDB3 = "";
	$mDB3 = new MywebDB();

	$Qry="UPDATE overview_manpower_sub set
			engineering_date 		= '$engineering_date'
			,floor 					= '$floor_list'
			,scheduled_entry_date 	= '$scheduled_entry_date_per_floor'
			,standard_manpower		= '$standard_manpower'
			,available_manpower		= '$available_manpower'
			,actual_manpower		= '$actual_manpower'
			,manpower_gap			= '$manpower_gap'
			,manpower_type			= '$manpower_type'
			,makeby					= '$memberID'
			,last_modify			=  now()
			where auto_seq      	= '$auto_seq'";
			
	$mDB->query($Qry);
	
	//更新此筆資料後面的日期
	if ((!isset($org_engineering_date)) || ($org_engineering_date <> "0000-00-00")) {
		//檢查是否有日期差
		if ($engineering_date == $org_engineering_date) {
			//沒有差異則不做任何處理
		} else {
			//更新
			$diff = dateDiffDays($org_engineering_date, $engineering_date, true);

			$Qry="SELECT * FROM overview_manpower_sub
			WHERE case_id = '$case_id' AND seq = '$seq' AND auto_seq > '$auto_seq'
			ORDER BY auto_seq";
			$mDB->query($Qry);
			if ($mDB->rowCount() > 0) {
				while ($row=$mDB->fetchRow(2)) {
					$auto_seq2 = $row['auto_seq'];
					$Qry2="UPDATE overview_manpower_sub set
							engineering_date = engineering_date + INTERVAL ".$diff." DAY
							,last_modify		= now()
							where auto_seq = '$auto_seq2'";
					$mDB2->query($Qry2);
				}
			}
		
		}
	}
	if ((!isset($org_scheduled_entry_date_per_floor)) || ($org_scheduled_entry_date_per_floor <> "0000-00-00")) {
		//檢查是否有日期差
		if ($scheduled_entry_date_per_floor == $org_scheduled_entry_date_per_floor) {
			//沒有差異則不做任何處理
		} else {
			//更新
			$diff = dateDiffDays($org_scheduled_entry_date_per_floor, $scheduled_entry_date_per_floor, true);

			$Qry="SELECT * FROM overview_manpower_sub
			WHERE case_id = '$case_id' AND seq = '$seq' AND auto_seq > '$auto_seq'
			ORDER BY auto_seq";
			$mDB->query($Qry);
			if ($mDB->rowCount() > 0) {
				while ($row=$mDB->fetchRow(2)) {
					$auto_seq2 = $row['auto_seq'];
					$Qry2="UPDATE overview_manpower_sub set
							scheduled_entry_date = scheduled_entry_date + INTERVAL ".$diff." DAY
							,last_modify		= now()
							where auto_seq = '$auto_seq2'";
					$mDB2->query($Qry2);
				}
			}
		
		}
	}
	// 更新主檔
    $Qry3="UPDATE CaseManagement 
           SET last_modify8 = NOW(), makeby8 = '$memberID' 
           WHERE case_id = '$case_id'";
    $mDB3->query($Qry3);

	$mDB3->remove();
	$mDB2->remove();
	$mDB->remove();

	$objResponse->script("setSave();");
	$objResponse->script("parent.overview_manpower_sub_Draw();");

	$objResponse->script("art.dialog.tips('已存檔!',1);");
	$objResponse->script("parent.$.fancybox.close();");
		
	
	return $objResponse;
}

$xajax->processRequest();





if (isset($_GET['times'])){

	$times = $_GET['times'];
	$case_id = $_GET['case_id'];
	$seq = $_GET['seq'];
	$seq2 = $_GET['seq2'];

	$overview_building_row = getkeyvalue2($site_db."_info","overview_building","auto_seq = '$seq2' and case_id = '$case_id' and seq = '$seq'","scheduled_entry_date,actual_entry_date,construction_days_per_floor,standard_manpower");
	$scheduled_entry_date = $overview_building_row['scheduled_entry_date'];
	$actual_entry_date = $overview_building_row['actual_entry_date'];
	$construction_days_per_floor = $overview_building_row['construction_days_per_floor'];
	$standard_manpower = $overview_building_row['standard_manpower'];

	$manpower_gap = 0-$standard_manpower;

	//先判斷是否已有資料，沒有資料則直接帶入實際進場日，有資料則取得上一筆的日期
	$mDB = "";
	$mDB = new MywebDB();
	$mDB2 = "";
	$mDB2 = new MywebDB();

$last_engineering_date = null;
$last_scheduled_entry_date = null;
$last_floor = null;

// 先嘗試撈最後一筆
$Qry="SELECT engineering_date, scheduled_entry_date, floor 
      FROM overview_manpower_sub
      WHERE case_id = '$case_id' AND seq = '$seq' AND seq2 = '$seq2'
      ORDER BY auto_seq DESC LIMIT 1";
$mDB->query($Qry);

if ($mDB->rowCount() > 0) {
    $row=$mDB->fetchRow(2);
    $last_engineering_date = $row['engineering_date'];
    $last_scheduled_entry_date = $row['scheduled_entry_date'];
    $last_floor = $row['floor'];
} else {
    // 沒有資料，從頭開始
    $last_engineering_date = isValidDate($actual_entry_date) ? $actual_entry_date : (isValidDate($scheduled_entry_date) ? $scheduled_entry_date : date("Y-m-d"));
    $last_scheduled_entry_date = $last_engineering_date;
    $last_floor = "0F"; // 從 0F 開始，等下會 +1
}

for ($i = 1; $i <= 5; $i++) {
    // 處理樓層
    preg_match('/\d+/', $last_floor, $matches);
    $number = isset($matches[0]) ? (int)$matches[0] + 1 : 1;
    $next_floor = $number . "F";

    // 累加日期
    $default_day  = date("Y-m-d", strtotime($last_engineering_date . " +{$construction_days_per_floor} days"));
    $default_day2 = date("Y-m-d", strtotime($last_scheduled_entry_date . " +{$construction_days_per_floor} days"));

    // 寫入 DB
    $Qry="INSERT INTO overview_manpower_sub 
          (case_id, seq, seq2, engineering_date, scheduled_entry_date, floor, standard_manpower, actual_manpower, manpower_type, manpower_gap) 
          VALUES 
          ('$case_id','$seq','$seq2','$default_day','$default_day2','$next_floor','$standard_manpower','$actual_manpower','$manpower_type','$manpower_gap')";
    $mDB->query($Qry);

    // 更新基準
    $last_engineering_date = $default_day;
    $last_scheduled_entry_date = $default_day2;
    $last_floor = $next_floor;

    if ($i == 1) {
        $Qry="SELECT auto_seq 
              FROM overview_manpower_sub 
              WHERE case_id = '$case_id' AND seq = '$seq' AND seq2 = '$seq2' 
              ORDER BY auto_seq DESC LIMIT 1";
        $mDB->query($Qry);
        if ($mDB->rowCount() > 0) {
            $row=$mDB->fetchRow(2);
            $auto_seq = $row['auto_seq'];
        }
    }
}

	// 更新主檔
	$Qry2="UPDATE CaseManagement 
		   SET last_modify8 = NOW(), makeby8 = '$memberID' 
		   WHERE case_id = '$case_id'";
	$mDB->query($Qry2);

	$mDB->remove();
	$mDB2->remove();


} else {
	$auto_seq = $_GET['auto_seq'];
}

$mDB = "";
$mDB = new MywebDB();



$fm = $_GET['fm'];

$mess_title = $title;

$floor_list = array();


//人員差異
$Qry = "SELECT manpower_type FROM overview_manpower_sub
WHERE auto_seq = '$auto_seq'";
$mDB->query($Qry);
$select_manpower_type  = "";
$select_manpower_type .= "<option></option>";

if ($mDB->rowCount() > 0) {
    $row = $mDB->fetchRow(2);
    $manpower_type = $row['manpower_type'];
} else {
    $manpower_type = "";
}

// 手動定義選項
$manpower_type_options = array("效率好", "每日施作面積增加");

foreach ($manpower_type_options as $ch_caption) {
    $select_manpower_type .= "<option value=\"$ch_caption\" " . mySelect($ch_caption, $manpower_type) . ">$ch_caption</option>";
}

$Qry="SELECT * FROM overview_manpower_sub
WHERE auto_seq = '$auto_seq'";
$mDB->query($Qry);
$total = $mDB->rowCount();
if ($total > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$case_id = $row['case_id'];
	$seq = $row['seq'];
	$seq2 = $row['seq2'];
	$engineering_date = $row['engineering_date'];
	$scheduled_entry_date_per_floor	= $row['scheduled_entry_date'];
	$floor = $row['floor'];
	$standard_manpower = $row['standard_manpower'];
	$available_manpower = $row['available_manpower'];
	$actual_manpower = $row['actual_manpower'];
	$manpower_type = $row['manpower_type'];
	$manpower_gap = $row['manpower_gap'];

	$floor_list = explode(',', $floor);
}

$series_floor_list = json_encode($floor_list);


$select_list = array();

$Qry="select * from items where pro_id = 'floor' order by orderby";
$mDB->query($Qry);
if ($mDB->rowCount() > 0) {
    //已找到符合資料
	while ($row=$mDB->fetchRow(2)) {
		$caption = $row['caption'];
		$orderby = $row['orderby'];

		$select_list[] = $caption;
	}

}

$mDB->remove();

$series_select_list = json_encode($select_list);




//自動帶入實際進場日，如沒有則帶入預計進場日
//$overview_sub_row = getkeyvalue2($site_db."_info","overview_sub","auto_seq = '$seq' and case_id = '$case_id'","standard_manpower");
//$org_standard_manpower = $overview_sub_row['standard_manpower'];

$overview_building_row = getkeyvalue2($site_db."_info","overview_building","auto_seq = '$seq2' and case_id = '$case_id' and seq = '$seq'","standard_manpower");
$org_standard_manpower = $overview_building_row['standard_manpower'];


if ($standard_manpower == 0)
	$standard_manpower = $org_standard_manpower;


$show_savebtn=<<<EOT
<div class="btn-group vbottom" role="group" style="margin-top:5px;">
	<button id="save" class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 5px 15px;"><i class="bi bi-check-circle"></i>&nbsp;存檔</button>
	<button id="cancel" class="btn btn-secondary display_none" type="button" onclick="setCancel();" style="padding: 5px 15px;"><i class="bi bi-x-circle"></i>&nbsp;取消</button>
	<button id="close" class="btn btn-danger" type="button" onclick="parent.overview_manpower_sub_Draw();parent.$.fancybox.close();" style="padding: 5px 15px;"><i class="bi bi-power"></i>&nbsp;關閉</button>
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
	max-width: 800px; !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:200px;display: none;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;max-width:500px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

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

</style>
EOT;

}



$show_center=<<<EOT
<!-- Styles -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>

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
								<div class="field_div1">進場日:</div>
								<div class="field_div2">
									<!--
									<div style="padding:8px 0;font-size:18px;color:blue;text-align:left;font-weight:700;">$engineering_date</div>
									-->
									<div class="input-group" id="engineering_date" style="width:100%;max-width:250px;">
										<input type="text" class="form-control" name="engineering_date" placeholder="請選擇進場日期" aria-describedby="engineering_date" value="$engineering_date">
										<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#engineering_date" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
									</div>
									<script type="text/javascript">
										$(function () {
											$('#engineering_date').datetimepicker({
												locale: 'zh-tw'
												,format:"YYYY-MM-DD"
												,allowInputToggle: true
											});
										});
									</script>
								</div> 
							</div> 
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">預計進場日:</div>
								<div class="field_div2">
									<!--
									<div style="padding:8px 0;font-size:18px;color:blue;text-align:left;font-weight:700;">$scheduled_entry_date_per_floor</div>
									-->
									<div class="input-group" id="scheduled_entry_date_per_floor" style="width:100%;max-width:250px;">
										<input type="text" class="form-control" name="scheduled_entry_date_per_floor" placeholder="請選擇進場日期" aria-describedby="scheduled_entry_date_per_floor" value="$scheduled_entry_date_per_floor">
										<button class="btn btn-outline-secondary input-group-append input-group-addon" type="button" data-target="#scheduled_entry_date_per_floor" data-toggle="datetimepicker"><i class="bi bi-calendar"></i></button>
									</div>
									<script type="text/javascript">
										$(function () {
											$('#scheduled_entry_date_per_floor').datetimepicker({
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
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">樓層:</div> 
								<div class="field_div2">
									<select class="form-select form-select-lg select2" multiple="multiple" id="floor" name="floor" data-placeholder="請選擇樓層" data-width="400px" onchange="setEdit();"></select>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">預定標準人力:</div> 
								<div class="field_div2">
									<input type="text" class="form-control" id="standard_manpower" name="standard_manpower" size="50" style="width:100%;max-width:200px;" value="$standard_manpower" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">工班可派人力:</div> 
								<div class="field_div2">
									<input type="text" class="form-control" id="available_manpower" name="available_manpower" size="50" style="width:100%;max-width:200px;" value="$available_manpower" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">實際出工人力:</div> 
								<div class="field_div2">
									<input type="text" class="form-control" id="actual_manpower" name="actual_manpower" size="50" style="width:100%;max-width:200px;" value="$actual_manpower" onchange="setEdit();"/>
								</div> 
							</div> 
						</div>
					</div>
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-md-12">
								<div class="field_div1">人員差異:</div> 
								<div class="field_div2">
									<select id="manpower_type" name="manpower_type" placeholder="請選擇案件種類" class="w-100" style="max-width:300px;">
										$select_manpower_type
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
						<input type="hidden" id="floor_list" name="floor_list" value="$floor_list" />
					</div>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>
<script>

function CheckValue(thisform) {

	// 獲取 select 元素
	var selectedValue = $('#floor').val();

	$('#floor_list').val(selectedValue);

	xajax_processform(xajax.getFormValues('modifyForm'));
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

</script>

<script>

	var series_select_list = JSON.parse('$series_select_list');

	/*
	$('.select2').select2({
		data: series_select_list,
		tags: true,
		maximumSelectionLength: 100,
		tokenSeparators: [',', ' '],
		placeholder: "請選擇樓別",
		//minimumInputLength: 1,
		//ajax: {
		//   url: "you url to data",
		//   dataType: 'json',
		//  quietMillis: 250,
		//  data: function (term, page) {
		//     return {
		//         q: term, // search term
		//    };
		//  },
		//  results: function (data, page) { 
		//  return { results: data.items };
		//   },
		//   cache: true
		// }
	});

	var series_floor_list = JSON.parse('$series_floor_list');
	$("#floor").val(series_floor_list).select2();
	*/

	$( '.select2' ).select2( {
		theme: "bootstrap-5",
		data: series_select_list,
		width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
		placeholder: $( this ).data( 'placeholder' ),
		closeOnSelect: false,
		selectionCssClass: 'select2--large',
    	dropdownCssClass: 'select2--large',
	} );	

	var series_floor_list = JSON.parse('$series_floor_list');
	$("#floor").val(series_floor_list).select2();



</script>

EOT;

?>