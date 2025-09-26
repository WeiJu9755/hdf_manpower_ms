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


@include_once("/website/class/".$site_db."_info_class.php");

/* 使用xajax */
@include_once '/website/xajax/xajax_core/xajax.inc.php';
$xajax = new xajax();

$xajax->registerFunction("processform");

function processform($aFormValues){

	$objResponse = new xajaxResponse();
	
	//$powerkey = trim($aFormValues['powerkey']);
	//$super_admin = trim($aFormValues['super_admin']);
	//$admin_readonly = trim($aFormValues['admin_readonly']);
	
	$bError = false;
	
	/*	
	if (trim($aFormValues['region']) == "")	{
		$objResponse->script("jAlert('警示', '請選擇區域', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	if (trim($aFormValues['construction_id']) == "")	{
		$objResponse->script("jAlert('警示', '請輸入工程名稱', 'red', '', 2000);");
		return $objResponse;
		exit;
	}
	
	
	//自動編碼 case_id
	//案件編號：西元年後2碼+H+區域+流水號3碼，ex.24HN001→可否自動編碼
	//$o_id = substr(date("Y"),-2,2)."H".$m_region;
	$o_id = substr(date("Y"),-2,2);

	$mDB = "";
	$mDB = new MywebDB();
	
	//取得最後的群組代號
	$Qry = "SELECT case_id FROM CaseManagement WHERE SUBSTRING(case_id,1,2) = '$o_id' ORDER BY auto_seq DESC LIMIT 0,1";
	$mDB->query($Qry);
	if ($mDB->rowCount() > 0) {
		$row=$mDB->fetchRow(2);
		$temp_case_id = $row['case_id'];
		$str3 = (int)substr($temp_case_id,-3,3);
		$num = $str3+1;
		$filled_int = sprintf("%03d", $num);
		$new_case_id = $o_id."H".$m_region.$filled_int;
	} else {
		$new_case_id = $o_id."H".$m_region."001";
	}
	*/
	
	if (!$bError) {
		$fm					= trim($aFormValues['fm']);
		$site_db			= trim($aFormValues['site_db']);
		$case_id			= trim($aFormValues['case_id']);
		$seq				= trim($aFormValues['seq']);
		$seq2				= trim($aFormValues['seq2']);
		$engineering_date	= trim($aFormValues['engineering_date']);
		$memberID       	= $_SESSION['memberID'];

		
		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();
		$mDB2 = "";
		$mDB2 = new MywebDB();
	  
		$Qry="insert into overview_manpower_sub (case_id,seq,seq2,engineering_date) values ('$case_id','$seq','$seq2','$engineering_date')";
		$Qry2 = "UPDATE CaseManagement SET last_modify8 = NOW(), makeby8 = '$memberID' WHERE case_id = '$case_id'";
		$mDB->query($Qry);
		$mDB2->query($Qry2);
		//再取出auto_seq
		$Qry="select auto_seq from overview_manpower_sub where case_id = '$case_id' and seq = '$seq' and seq2 = '$seq2' order by auto_seq desc limit 0,1";
		$mDB->query($Qry);
		if ($mDB->rowCount() > 0) {
			//已找到符合資料
			$row=$mDB->fetchRow(2);
			$auto_seq = $row['auto_seq'];
		}

        $mDB->remove();
		$mDB2->remove();	

		if (!empty($auto_seq)) {
			$objResponse->script("myDraw();");
			//$objResponse->script("art.dialog.tips('已新增，請繼續輸入其他資料...',2);");
			$objResponse->script("window.location='/?ch=overview_manpower_sub_modify&auto_seq=$auto_seq&fm=$fm';");
			//$objResponse->script("parent.$.fancybox.close();");
		} else {
			//$objResponse->script("art.dialog.alert('發生不明原因的錯誤，資料未新增，請再試一次!');");
			$objResponse->script("parent.$.fancybox.close();");
		}
	};
	
	return $objResponse;	
}

$xajax->processRequest();


$fm = $_GET['fm'];
$case_id = $_GET['case_id'];
$seq = $_GET['seq'];
$seq2 = $_GET['seq2'];

//自動帶入實際進場日，如沒有則帶入預計進場日
/*
$overview_sub_row = getkeyvalue2($site_db."_info","overview_sub","auto_seq = '$seq' and case_id = '$case_id'","actual_entry_date,standard_manpower");

$actual_entry_date = $overview_sub_row['actual_entry_date'];

$standard_manpower = $overview_sub_row['standard_manpower'];
*/
$overview_building_row = getkeyvalue2($site_db."_info","overview_building","auto_seq = '$seq2' and case_id = '$case_id' and seq = '$seq'","actual_entry_date,construction_days_per_floor,construction_days_first_floor,standard_manpower");
$actual_entry_date = $overview_building_row['actual_entry_date'];
$construction_days_per_floor = $overview_building_row['construction_days_per_floor'];
$standard_manpower = $overview_building_row['standard_manpower'];
$construction_days_first_floor = $overview_building_row['construction_days_first_floor'];

//先判斷是否已有資料，沒有資料則直接帶入實際進場日，有資料則取得上一筆的日期
$mDB = "";
$mDB = new MywebDB();

// 查總共有幾筆
$QryCount = "SELECT COUNT(*) as cnt
				FROM overview_manpower_sub
				WHERE case_id = '$case_id' AND seq = '$seq' AND seq2 = '$seq2'
";
$mDB->query($QryCount);
$rowCount = $mDB->fetchRow(2);
$recordCount = (int)$rowCount['cnt'];

// 查最後一筆日期
$QryLast = "SELECT engineering_date
				FROM overview_manpower_sub
				WHERE case_id = '$case_id' AND seq = '$seq' AND seq2 = '$seq2'
				ORDER BY auto_seq DESC
				LIMIT 1
";
$mDB->query($QryLast);
$lastDateRow = $mDB->fetchRow(2);
$engineering_date = $lastDateRow ? $lastDateRow['engineering_date'] : null;

// 決定 default_day
if ($recordCount == 0) {
    // 第一筆 → 實際進場日或今天
    if (isValidDate($actual_entry_date)) {
        $default_day = $actual_entry_date;
    } else {
        $default_day = date("Y-m-d");
    }
} elseif ($recordCount == 1) {
    // 第二筆 → 第一筆日期 + construction_days_first_floor
    if (isValidDate($engineering_date)) {
        $default_day = date("Y-m-d", strtotime($engineering_date . " +{$construction_days_first_floor} days"));
    } else {
        $default_day = date("Y-m-d", strtotime("+{$construction_days_first_floor} days"));
    }
} else {
    // 第三筆以後 → 前一筆日期 + construction_days_per_floor
    if (isValidDate($engineering_date)) {
        $default_day = date("Y-m-d", strtotime($engineering_date . " +{$construction_days_per_floor} days"));
    } else {
        $default_day = date("Y-m-d", strtotime("+{$construction_days_per_floor} days"));
    }
}


/*
if ($fm == "CaseManagement") {
	$default_day = date("Y-m-d");
} else {
	$default_day = date("Y-m-d", strtotime("+1 day"));
}
*/


$mess_title = $title;
/*
$super_admin = "N";
$mem_row = getkeyvalue2('memberinfo','member',"member_no = '$memberID'",'admin,admin_readonly');
$super_admin = $mem_row['admin'];
$admin_readonly = $mem_row['admin_readonly'];


$cando = true;

if ($cando == true) {
*/


if (!($detect->isMobile() && !$detect->isTablet())) {
	$isMobile = 0;

$style_css=<<<EOT
<style>

.card_full {
    width: 100vw;
	height: 100vh;
}

#full {
    width: 100vw;
	height: 100vh;
}

#info_container {
	width: 800px !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:200px;display: none;font-size:18px;color:#000;text-align:right;font-weight:700;padding:15px 10px 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}
.field_div2 {width:100%;max-width:580px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

.maxwidth {
    width: 100%;
    max-width: 250px;
}
</style>
EOT;

} else {
	$isMobile = 1;
$style_css=<<<EOT
<style>

.card_full {
    width: 100vw;
	height: 100vh;
}

#full {
    width: 100vw;
	height: 100vh;
}

#info_container {
	width: 100% !Important;
	margin: 0 auto !Important;
}

.field_div1 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:15px 10px 0 0;vertical-align: top;}
.field_div2 {width:100%;display: block;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 10px 0 0;vertical-align: top;}

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
		<div class="size14 weight float-start">
			$mess_title
		</div>
	</div>
	<div id="full" class="card-body data-overlayscrollbars-initialize">
		<div id="info_container">
			<form method="post" id="addForm" name="addForm" enctype="multipart/form-data" action="javascript:void(null);">
				<div class="field_container3">
					<div>
						<div class="field_div1">進場日期:</div> 
						<div class="field_div2">
							<div class="input-group" id="engineering_date" style="width:100%;max-width:250px;">
								<input type="text" class="form-control" name="engineering_date" placeholder="請選擇進場日期" aria-describedby="engineering_date" value="$default_day">
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
				</div>
				<div class="form_btn_div mt-5">
					<input type="hidden" name="fm" value="$fm" />
					<input type="hidden" name="site_db" value="$site_db" />
					<input type="hidden" name="member_no" value="$memberID" />
					<input type="hidden" name="case_id" value="$case_id" />
					<input type="hidden" name="seq" value="$seq" />
					<input type="hidden" name="seq2" value="$seq2" />
					<button class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 10px;margin-right: 10px;"><i class="bi bi-check-lg green"></i>&nbsp;確定新增</button>
					<button class="btn btn-danger" type="button" onclick="parent.$.fancybox.close();" style="padding: 10px;"><i class="bi bi-power"></i>&nbsp關閉</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script>

function CheckValue(thisform) {
	xajax_processform(xajax.getFormValues('addForm'));
	thisform.submit();
}

var myDraw = function(){
	var oTable;
	oTable = parent.$('#db_table').dataTable();
	oTable.fnDraw(false);
}
	
</script>
EOT;

//}

?>