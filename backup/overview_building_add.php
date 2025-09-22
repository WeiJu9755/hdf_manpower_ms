<?php

session_start();
$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;


@include_once("/website/class/".$site_db."_info_class.php");

/* 使用xajax */
@include_once '/website/xajax/xajax_core/xajax.inc.php';
$xajax = new xajax();

$xajax->registerFunction("processform");

function processform($aFormValues){

	$objResponse = new xajaxResponse();
	
	$bError = false;
	
	if (trim($aFormValues['building']) == "")	{
		$objResponse->script("jAlert('警示', '請選擇棟別', 'red', '', 2000);");
		return $objResponse;
		exit;
	}

	if (!$bError) {
		$fm					= trim($aFormValues['fm']);
		$site_db			= trim($aFormValues['site_db']);
		$memberID			= trim($aFormValues['memberID']);
		$case_id			= trim($aFormValues['case_id']);
		$seq				= trim($aFormValues['seq']);
		$builder_id			= trim($aFormValues['builder_id']);
		$building			= trim($aFormValues['building']);
		$case_id			= trim($aFormValues['case_id']);

		
		//存入實體資料庫中
		$mDB = "";
		$mDB = new MywebDB();
		$mDB2 = "";
		$mDB2 = new MywebDB();

		
	  
		$Qry="insert into overview_building (case_id,seq,builder_id,building,makeby,last_modify) values ('$case_id','$seq','$builder_id','$building','$memberID',now())";
		$Qry2 = "UPDATE CaseManagement SET last_modify8 = NOW(), makeby8 = '$memberID' WHERE case_id = '$case_id'";
		$mDB->query($Qry);
		$mDB2->query($Qry2);
		//再取出auto_seq
		$Qry="select auto_seq from overview_building where case_id = '$case_id' and seq = '$seq' order by auto_seq desc limit 0,1";
		$mDB->query($Qry);
		if ($mDB->rowCount() > 0) {
			//已找到符合資料
			$row=$mDB->fetchRow(2);
			$auto_seq = $row['auto_seq'];
		}

        $mDB->remove();

		if (!empty($auto_seq)) {
			$objResponse->script("overview_building_myDraw();");
			//$objResponse->script("art.dialog.tips('已新增，請繼續輸入其他資料...',2);");
			$objResponse->script("window.location='/?ch=overview_building_modify&auto_seq=$auto_seq&fm=$fm';");
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


$mess_title = $title;
/*
$super_admin = "N";
$mem_row = getkeyvalue2('memberinfo','member',"member_no = '$memberID'",'admin,admin_readonly');
$super_admin = $mem_row['admin'];
$admin_readonly = $mem_row['admin_readonly'];


$cando = true;

if ($cando == true) {
*/

//取得上層工程概況的代工單位
$overview_sub_row = getkeyvalue2($site_db.'_info','overview_sub',"case_id = '$case_id' and auto_seq = '$seq'",'builder_id');
$builder_id = $overview_sub_row['builder_id'];



//讀取資料
$mDB = "";
$mDB=new MywebDB();

//載入棟別
$Qry="select caption from items where pro_id = 'building' order by pro_id,orderby";
$mDB->query($Qry);
$select_building = "";
$select_building .= "<option></option>";

if ($mDB->rowCount() > 0) {
	while ($row=$mDB->fetchRow(2)) {
		$ch_caption = $row['caption'];
		$select_building .= "<option value=\"$ch_caption\">$ch_caption</option>";
	}
}

$mDB->remove();



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
.field_div2 {width:100%;max-width:380px;display: none;font-size:18px;color:#000;text-align:left;font-weight:700;padding:8px 0 0 0;vertical-align: top;display:inline-block;zoom: 1;*display: inline;}

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
						<div class="field_div1">請選擇棟別:</div> 
						<div class="field_div2">
							<select id="building" name="building" placeholder="請選擇棟別" class="w-100" style="max-width:320px;">
								$select_building
							</select>

						</div> 
					</div>
				</div>
				<div class="form_btn_div mt-5">
					<input type="hidden" name="fm" value="$fm" />
					<input type="hidden" name="site_db" value="$site_db" />
					<input type="hidden" name="memberID" value="$memberID" />
					<input type="hidden" name="case_id" value="$case_id" />
					<input type="hidden" name="seq" value="$seq" />
					<input type="hidden" name="builder_id" value="$builder_id" />
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