<?php

//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

session_start();

$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


//計算請假時間
function calculateLeaveHours($startTime, $endTime) {
    // 設定工作時間的起點與終點
    $workStartTime = new DateTime('08:00');
    $workEndTime = new DateTime('17:00');
    
    // 中午休息時間
    $lunchStartTime = new DateTime('12:00');
    $lunchEndTime = new DateTime('13:00');

    // 將請假開始時間與結束時間轉為 DateTime 物件
    $start = new DateTime($startTime);
    $end = new DateTime($endTime);

    // 檢查是否在工作時間範圍內
    if ($start < $workStartTime) $start = $workStartTime;
    if ($end > $workEndTime) $end = $workEndTime;

    // 計算總請假時數（包含中午時間）
    $interval = $start->diff($end);
    $leaveHours = $interval->h + ($interval->i / 60);

    // 檢查是否跨越中午休息時間，並扣除 1 小時
    if ($start < $lunchEndTime && $end > $lunchStartTime) {
        $leaveHours -= 1; // 扣除 1 小時中午休息時間
    }

    // 將請假時間以半小時為單位計算
    $leaveHours = ceil($leaveHours * 2) / 2;

    return $leaveHours;
}


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
	
	$web_id				= trim($aFormValues['web_id']);
	$project_id			= trim($aFormValues['project_id']);
	$auth_id			= trim($aFormValues['auth_id']);

	$dispatch_id		= trim($aFormValues['dispatch_id']);
	$memberID			= trim($aFormValues['memberID']);
	
	SaveValue($aFormValues);
	
	$objResponse->script("setSave();");
	$objResponse->script("updispatch('$dispatch_id');");
	$objResponse->script("parent.myDraw();");

	$objResponse->script("art.dialog.tips('已存檔!',1);");
	$objResponse->script("parent.$.fancybox.close();");
		
	
	return $objResponse;
}


$xajax->registerFunction("SaveValue");
function SaveValue($aFormValues){

	$objResponse = new xajaxResponse();
	
		//進行存檔動作
		$site_db			= trim($aFormValues['site_db']);
		$web_id				= trim($aFormValues['web_id']);
		$auto_seq			= trim($aFormValues['auto_seq']);
		$content			= trim($aFormValues['content']);

		$dispatch_id		= trim($aFormValues['dispatch_id']);
		$memberID			= trim($aFormValues['memberID']);
		
		//更新
		$mDB = "";
		$mDB = new MywebDB();
		
		$Qry="UPDATE dispatch set
				content = '$content'
				,makeby = '$memberID'
				,last_modify = now()
				where auto_seq = '$auto_seq'";
				
		$mDB->query($Qry);
        $mDB->remove();

		//update_dispatch($dispatch_id,$memberID);

		$objResponse->script("$('#myConfirmSending').prop('disabled', true);");
		
	return $objResponse;
}


$xajax->registerFunction("DeleteRow");
function DeleteRow($auto_seq,$case_id,$seq){

	$objResponse = new xajaxResponse();
	$memberID = $_SESSION['memberID']; // 從 session 取
	
	$mDB = "";
	$mDB = new MywebDB();
	$mDB2 = "";
	$mDB2 = new MywebDB();

	//刪除人力資料
	$Qry="delete from overview_manpower_sub where case_id = '$case_id' and seq = '$seq' and seq2 = '$auto_seq'";
	$mDB->query($Qry);

	//刪除主資料
	$Qry="delete from overview_building where auto_seq = '$auto_seq'";
	$mDB->query($Qry);

	// 更新主檔
    $Qry2="UPDATE CaseManagement 
           SET last_modify8 = NOW(), makeby8 = '$memberID' 
           WHERE case_id = '$case_id'";
    $mDB2->query($Qry2);
	
	$mDB->remove();
	$mDB2->remove();
	
    $objResponse->script("oTable = $('#overview_building_table').dataTable();oTable.fnDraw(false)");
	$objResponse->script("autoclose('提示', '資料已刪除！', 500);");

	return $objResponse;
	
}


$xajax->registerFunction("returnValue");
function returnValue($auto_seq,$layout){
	$objResponse = new xajaxResponse();

	$mDB = "";
	$mDB = new MywebDB();
	
	//放樣
	$Qry="SELECT subcontractor_name FROM subcontractor WHERE subcontractor_id = '$layout'";
	$mDB->query($Qry);
	if ($mDB->rowCount() > 0) {
		$row=$mDB->fetchRow(2);
		$layout_name = $row['subcontractor_name'];
	}
	//$show_layout_name = "<div class=\"size12\">".$layout_name."</div><div class=\"size08\">".$layout."</div>";
	$show_layout_name = $layout_name;
	$objResponse->assign("layout".$auto_seq,"innerHTML",$show_layout_name);	

	$mDB->remove();
	

    return $objResponse;
	
}

$xajax->processRequest();


$auto_seq = $_GET['auto_seq'];
$fm = $_GET['fm'];

$mess_title = $title;

/*
//取得預設值
$settings_row = getkeyvalue2($site_db."_info","settings","auto_seq = '1'","def_attendance_end");
if (!empty($settings_row['def_attendance_end']))
	$def_attendance_end = $settings_row['def_attendance_end'];
else
	$def_attendance_end = "";
*/

$mDB = "";
$mDB = new MywebDB();
$Qry="SELECT a.* FROM CaseManagement a
WHERE a.auto_seq = '$auto_seq'";
$mDB->query($Qry);
$total = $mDB->rowCount();
if ($total > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$case_id = $row['case_id'];
	$region = $row['region'];
	$construction_id = $row['construction_id'];
	$builder_id = $row['builder_id'];
	$contractor_id = $row['contractor_id'];
	$county = $row['county'];
	$town = $row['town'];
	$zipcode = $row['zipcode'];
	$address = $row['address'];
	$status1 = $row['status1'];
	$status2 = $row['status2'];
	$makeby9 = $row['makeby9'];
	$last_modify9 = $row['last_modify9'];

}

$mDB->remove();


$show_savebtn=<<<EOT
<div class="btn-group vbottom" role="group" style="margin-top:5px;">
	<button id="close" class="btn btn-danger" type="button" onclick="parent.myDraw();parent.$.fancybox.close();" style="padding: 5px 15px;"><i class="bi bi-power"></i>&nbsp;關閉</button>
</div>
EOT;


//取得使用者員工身份
if (empty($makeby9))
	$makeby9 = $memberID;

$member_picture = getmemberpict50($makeby9);

$member_row = getkeyvalue2("memberinfo","member","member_no = '$makeby9'","member_name");
$member_name = $member_row['member_name'];

$employee_row = getkeyvalue2($site_db."_info","employee","member_no = '$makeby9'","count(*) as manager_count,employee_name,employee_type");
$manager_count =$employee_row['manager_count'];
if ($manager_count > 0) {
	$employee_name = $employee_row['employee_name'];
	$employee_type = $employee_row['employee_type'];
} else {
	$employee_name = $member_name;
	$employee_type = "未在員工名單";
}

$member_logo=<<<EOT
<div class="float-end text-nowrap mt-3 size14 weight">
	<div class="inline mytable bg-white rounded">
		<div class="myrow">
			<div class="mycell text-center text-nowrap">
				<div class="inline me-1">By：</div>
				<img src="$member_picture" height="32" class="inline rounded">
			</div>
			<div class="mycell text-start ps-1 w-auto">
				<div class="size08 blue02 weight text-nowrap">$employee_name</div>
				<div class="size06 weight text-nowrap">$employee_type</div>
			</div>
		</div>
	</div>
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
	max-width: 1800px; !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:150px;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div1a {width:120px;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;max-width:240px;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2a {width:100%;max-width:150px;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div3 {width:100%;max-width:550px;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

.code_class {
	width:150px;
	text-align:right;
	padding:0 10px 0 0;
}

.maxwidth {
    width: 100%;
    max-width: 220px;
}

.maxwidth2 {
    width: 100%;
    max-width: 500px;
}

.maxwidth3 {
    width: 100%;
    max-width: 220px;
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
.field_div1a {width:auto;font-size:18px;color:#000;text-align:left;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 10px 0 0;vertical-align: top;}
.field_div2a {width:auto;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div3 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 10px 0 0;vertical-align: top;}

.code_class {
	width:auto;
	text-align:left;
	padding:0 10px 0 0;
}

.maxwidth {
    width: 100%;
}

.maxwidth2 {
    width: 100%;
}

.maxwidth3 {
    width: 100%;
    max-width: 220px;
}


</style>
EOT;

}



$m_location		= "/website/smarty/templates/".$site_db."/".$templates;
include $m_location."/sub_modal/project/func06/manpower_ms/manpower_sub.php";



$now = date('Y-m-d  H:i');

$show_center=<<<EOT
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.1.1/socket.io.js"></script>

<script src="/os/Autogrow-Textarea/jquery.autogrowtextarea.min.js"></script>

$style_css

<style>

    .tooltip-box {
        position: absolute;
        top: 50px; /* 調整為按鈕正下方 */
        left: 30%;
        transform: translateX(-30%);
        padding: 10px;
        background-color: #333;
        color: white;
        border-radius: 5px;
        display: none;
		/*
        white-space: nowrap;
		*/
        z-index: 1000;
    }

    .tooltip-box::after {
        content: '';
        position: absolute;
        top: -8px;
        left: 30%;
        transform: translateX(-30%);
        border-width: 8px;
        border-style: solid;
        border-color: transparent transparent #333 transparent;
    }
</style>

<div class="card card_full">
	<div class="card-header text-bg-info">
		<div class="size14 weight float-start" style="margin-top: 5px;">
			$mess_title
		</div>
		<div class="float-end" style="margin-top: -5px;">
			$show_savebtn
		</div>
	</div>
	<div id="full" class="card-body p-1" data-overlayscrollbars-initialize>
		<div id="info_container">
			<form method="post" id="modifyForm" name="modifyForm" enctype="multipart/form-data" action="javascript:void(null);">
			<div class="w-100 mb-5">
				<div class="field_container3">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-2 col-sm-12 col-md-12">
								<div class="field_div1a">案件編號:</div> 
								<div class="field_div2a">
									<div class="inline weight blue02 pt-2 me-2">$case_id</div>
								</div> 
							</div> 
							<div class="col-lg-10 col-sm-12 col-md-12">
								<div class="field_div1a">工程名稱:</div> 
								<div class="field_div3 blue02 pt-3">
									$construction_id
								</div> 
								$member_logo
							</div> 
						</div>
					</div>
					$show_manpower_sub
				<div>
					<input type="hidden" name="fm" value="$fm" />
					<input type="hidden" name="site_db" value="$site_db" />
					<input type="hidden" name="auto_seq" value="$auto_seq" />
					<input type="hidden" name="memberID" value="$memberID" />
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
	//thisform.submit();
}

function setEdit() {
	$('#close', window.document).addClass("display_none");
	$('#cancel', window.document).removeClass("display_none");
	$('#myConfirmSending').prop('disabled', true);
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


$(document).ready(function() {
	$("#content").autoGrow({
		extraLine: true // Adds an extra line at the end of the textarea. Try both and see what works best for you.
	});
});


var PushNotice = function(thisform) {	

	let  dispatch_id = thisform.dispatch_id.value;
	let  caption = thisform.caption.value;
	let  employee_name = thisform.employee_name.value;
	let  io_content = thisform.content.value;

	Swal.fire({
	title: "您確定要發佈「任務內容」通知嗎?",
	text: "",
	icon: "question",
	showCancelButton: true,
	confirmButtonColor: "#3085d6",
	cancelButtonColor: "#d33",
	cancelButtonText: "取消",
	confirmButtonText: "確定"
	}).then((result) => {
		if (result.isConfirmed) {
			SendPushNotices('dispatch',dispatch_id,io_content);
		}
	});
	
};

var SendPushNotices = function(tb,dispatch_id,io_content){
	var site_db = '$site_db';
	var web_id = '$web_id';
	var fm = '$fm';
	var templates = '$templates';
	var memberID = '$memberID';
	var project_id = '$project_id';
	var auth_id = '$auth_id';
	var caption = '$caption';
	var member_name = '$employee_name';
	var dispatch_date = '$dispatch_date';
	var now = '$now';
	//存入訊息
	$.post("/smarty/templates/"+site_db+"/"+templates+"/sub_modal/project/func08/dispatch_ms/ajax_PushNotice.php",{
			"site_db": site_db,
			"web_id": web_id,
			"project_id": project_id,
			"auth_id": auth_id,
			"from_id": memberID,
			"tb": tb,
			"dispatch_id": dispatch_id,
			"PushContent": io_content
			},
		function(data){

			var dispatch_desc = "日期："+dispatch_date+" (#"+dispatch_id+")";
			//var PushContent = member_name+" 於 "+now+" 發出了通知訊息<br>"+caption+"<br>"+dispatch_desc+"<br>"+io_content;

			var url = "/index.php?ch=view&pjt="+caption+"&dispatch_id="+dispatch_id+"&project_id="+project_id+"&auth_id="+auth_id+"&fm="+fm+"#myScrollspy";

			var mynotices_message = caption+"<div class=\"mytable\"><div class=\"myrow\"><div class=\"mycell w-auto px-1\"><div><div class=\"size12 weight\">"+member_name+" 於 <span class=\"red\">"+now+"</span> 發出了通知訊息</div></div><div style=\"padding: 0 3px 3px 0;\"><div class=\"size12 blue weight\">出工任務內容</div><div class=\"size12 weight\">"+dispatch_desc+"</div><div class=\"block-with-text\" style=\"max-height: 7.2em;\">"+io_content+"</div></div></div></div></div>";


			io.connect('$FOREVER').emit('sendnotice', '$web_id', data, mynotices_message);
			art.dialog.tips('已發佈通知!',1);
		},
		"json"
	);
}

var Checkall = function(thisform) {	
	xajax_Checkall(xajax.getFormValues('modifyForm'));
};


var ConfirmSending = function(thisform) {	

	//var dispatch_id = thisform.dispatch_id.value;
	//var content = thisform.content.value;

	Swal.fire({
	title: "請確認完成收工作業及資料驗證，您確定要「確認送出」嗎?",
	text: "",
	icon: "question",
	showCancelButton: true,
	confirmButtonColor: "#3085d6",
	cancelButtonColor: "#d33",
	cancelButtonText: "取消",
	confirmButtonText: "確定"
	}).then((result) => {
		if (result.isConfirmed) {
			xajax_ConfirmSending(xajax.getFormValues('modifyForm'));
		}
	});

};

var Reduction = function(thisform) {

	Swal.fire({
	title: "您確定要還原嗎?",
	text: "",
	icon: "question",
	showCancelButton: true,
	confirmButtonColor: "#3085d6",
	cancelButtonColor: "#d33",
	cancelButtonText: "取消",
	confirmButtonText: "確定"
	}).then((result) => {
		if (result.isConfirmed) {
			xajax_Reduction(xajax.getFormValues('modifyForm'));
		}
	});

}


function join_foreign_worker(dispatch_id,def_attendance_start) {
	xajax_join_foreign_worker(dispatch_id,def_attendance_start);
	return false;
}

function join_seconded_staff(dispatch_id,def_attendance_start) {
	xajax_join_seconded_staff(dispatch_id,def_attendance_start);
	return false;
}

var oneclick_copy = function(dispatch_id,copy_str) {	

	Swal.fire({
	title: "您確定要進行一鍵複製嗎?",
	text: "",
	icon: "question",
	showCancelButton: true,
	confirmButtonColor: "#3085d6",
	cancelButtonColor: "#d33",
	cancelButtonText: "取消",
	confirmButtonText: "確定"
	}).then((result) => {
		if (result.isConfirmed) {
			xajax_oneclick_copy(dispatch_id,copy_str);
		}
	});

};


var ConfirmKnock_off = function(dispatch_id,def_attendance_end) {	

	Swal.fire({
	title: "您確定要進行收工作業嗎?",
	text: "",
	icon: "question",
	showCancelButton: true,
	confirmButtonColor: "#3085d6",
	cancelButtonColor: "#d33",
	cancelButtonText: "取消",
	confirmButtonText: "確定"
	}).then((result) => {
		if (result.isConfirmed) {
			xajax_ConfirmKnock_off(dispatch_id,def_attendance_end);
		}
	});

};


var Calculation = function(dispatch_id) {	

	Swal.fire({
	title: "您確定要進行人數及工時計算嗎?",
	text: "",
	icon: "question",
	showCancelButton: true,
	confirmButtonColor: "#3085d6",
	cancelButtonColor: "#d33",
	cancelButtonText: "取消",
	confirmButtonText: "確定"
	}).then((result) => {
		if (result.isConfirmed) {
			xajax_Calculation(dispatch_id);
		}
	});

};

</script>

<script>
	// 取得按鈕和提示框元素
	const tooltipButton1 = document.getElementById('tooltipButton1');
	const tooltipContent1 = document.getElementById('tooltipContent1');

	let timeoutId; // 記錄計時器的ID

	// 設定按鈕點擊事件，控制提示框的顯示與隱藏
	tooltipButton1.addEventListener('click', function() {
		if (tooltipContent1.style.display === 'none' || tooltipContent1.style.display === '') {
			tooltipContent1.style.display = 'block';

			// 清除之前的計時器，避免重複計時
			clearTimeout(timeoutId);

			// 設定7秒後自動隱藏
			timeoutId = setTimeout(function() {
			tooltipContent1.style.display = 'none';
			}, 7000); // 7000毫秒 = 7秒
		} else {
			tooltipContent1.style.display = 'none';
			clearTimeout(timeoutId); // 如果手動關閉，清除計時器
		}
	});

	// 取得按鈕和提示框元素
	const tooltipButton2 = document.getElementById('tooltipButton2');
	const tooltipContent2 = document.getElementById('tooltipContent2');

	// 設定按鈕點擊事件，控制提示框的顯示與隱藏
	tooltipButton2.addEventListener('click', function() {
		if (tooltipContent2.style.display === 'none' || tooltipContent2.style.display === '') {
			tooltipContent2.style.display = 'block';

			// 清除之前的計時器，避免重複計時
			clearTimeout(timeoutId);

			// 設定7秒後自動隱藏
			timeoutId = setTimeout(function() {
			tooltipContent2.style.display = 'none';
			}, 7000); // 7000毫秒 = 7秒
		} else {
			tooltipContent2.style.display = 'none';
			clearTimeout(timeoutId); // 如果手動關閉，清除計時器
		}
	});


var updispatch = function(dispatch_id){

	var site_db = '$site_db';
	var templates = '$templates';
	//var dispatch_id = '$dispatch_id';

	var url = '/smarty/templates/'+site_db+'/'+templates+'/sub_modal/project/func08/dispatch_ms/ajax_update_dispatch.php'; 

	$.ajax({
		url: url, 
		type: 'GET',
		data: { dispatch_id: dispatch_id },
		dataType: 'text', 
		success: function(data) {
		},
		error: function() {
		}
	});

}

</script>

EOT;

?>