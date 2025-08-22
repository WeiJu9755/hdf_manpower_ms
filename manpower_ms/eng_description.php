<?php


session_start();
$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


//載入公用函數
@include_once '/website/include/pub_function.php';

@include_once("/website/class/".$site_db."_info_class.php");

// 使用xajax
@include_once '/website/xajax/xajax_core/xajax.inc.php';
$xajax = new xajax();

$xajax->registerFunction("processform");
function processform($aFormValues){

	$objResponse = new xajaxResponse();
	

	
	$auto_seq			= trim($aFormValues['auto_seq']);
	$memberID			= trim($aFormValues['memberID']);
	$eng_description	= trim($aFormValues['eng_description']);

	//存入實體資料庫中
	$mDB = "";
	$mDB = new MywebDB();
	
	
	$Qry = "UPDATE overview_sub set
			`eng_description` = '$eng_description'
			where auto_seq = '$auto_seq'";
	$mDB->query($Qry);
	
	$mDB->remove();
	
	$objResponse->script("myDraw();");
	$objResponse->script("art.dialog.tips('已存檔!',1);");
	$objResponse->script("parent.$.fancybox.close();");
	
	return $objResponse;
}

$xajax->processRequest();


$auto_seq = $_GET['auto_seq'];


$mDB = "";
$mDB = new MywebDB();

$Qry="SELECT eng_description FROM overview_sub
WHERE auto_seq = '$auto_seq'";
$mDB->query($Qry);
$total = $mDB->rowCount();
if ($total > 0) {
    //已找到符合資料
	$row=$mDB->fetchRow(2);
	$eng_description = $row['eng_description'];
}

$mDB->remove();




$show_center=<<<EOT

<style type="text/css">

.card_full {
	width:100%;
	height:100vh;
}

#full {
	width: 100%;
	height: 100%;
}

#info_container {
	width: 100% !Important;
	margin: 10px auto !Important;
}

</style>
<div class="card card_full">
	<div id="full" class="card-body data-overlayscrollbars-initialize">
		<div id="info_container">
			<form method="post" id="modifyForm" name="modifyForm" enctype="multipart/form-data" action="javascript:void(null);">
			<div style="width:auto;margin: 0;padding:0;">
				<div class="field_container3 px-5 size14" style="margin-bottom: 50px;">
					<div>
						<div class="pb-1 weight">工程說明:</div> 
						<div>
							<input type="text" class="inputtext" id="eng_description" name="eng_description" size="80" maxlength="120" style="width:100%;max-width:600px;" value="$eng_description">
						</div> 
					</div>
				</div>
				<div class="form_btn_div">
					<input type="hidden" name="auto_seq" value="$auto_seq" />
					<input type="hidden" name="memberID" value="$memberID" />
					<button class="btn btn-primary" type="button" onclick="CheckValue(this.form);" style="padding: 10px;margin-right: 10px;"><i class="bi bi-check-lg green"></i>&nbsp;&nbsp;存檔</button>
					<button class="btn btn-warning" type="button" onclick="clearall();" style="padding: 10px;margin-right: 10px;"><i class="bi bi-x-lg"></i>&nbsp;&nbsp;清除</button>
					<button class="btn btn-danger" type="button" onclick="parent.$.fancybox.close();" style="padding: 10px;"><i class="bi bi-power"></i>&nbsp;&nbsp;關閉</button>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">

function CheckValue(thisform) {
	xajax_processform(xajax.getFormValues('modifyForm'));
	thisform.submit();
}

function clearall() {
	$("#eng_description").val("");
}


var myDraw = function(){
	var oTable;
	oTable = parent.$('#manpower_sub_table').dataTable();
	oTable.fnDraw(false);
}

</script>

EOT;

?>