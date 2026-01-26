<?php

//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

session_start();

$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;

if (!($detect->isMobile() && !$detect->isTablet())) {
	$isMobile = "0";
} else {
	$isMobile = "1";
}

@include_once("/website/class/".$site_db."_info_class.php");

/* 使用xajax */
@include_once '/website/xajax/xajax_core/xajax.inc.php';
$xajax = new xajax();

$xajax->registerFunction("returnValue");
function returnValue($auto_seq, $case_id, $engineering_date, $building, $floor, $construction_days_per_floor,$server_actual_manpower,$available_manpower,$standard_manpower){
    $objResponse = new xajaxResponse();

    $start_date = $engineering_date;
   

    $total_manpower = 0;
	$use_manpower = 0 ;

    $mDB = new MywebDB();

    $Qry = "SELECT SUM(b.manpower) AS total_manpower
				   ,COUNT(DISTINCT a.dispatch_date) AS total_days
				FROM dispatch a
				LEFT JOIN dispatch_construction b ON b.dispatch_id = a.dispatch_id
				LEFT JOIN construction c ON c.construction_id = b.construction_id
				WHERE  c.case_id = '$case_id' 
								AND a.dispatch_date >='$start_date'
								AND b.building = '$building'
								AND b.floor = '$floor'
								AND a.ConfirmSending = 'Y'
    ";

    $mDB->query($Qry);
    $row = $mDB->fetchRow(2);
    $total_manpower = $row['total_manpower'];
	$total_days = $row['total_days'];

	// 實際人力: 派工人數/施工天數
	// 決定使用的人力：優先用資料庫的 total_manpower，否則用 server 回傳的人力 * 天數
		// 預設為 0
		$actual_manpower = 0;

		// 決定要用的人力
		if (!empty($total_manpower)) {
			$use_manpower = $total_manpower;
		} else {
			$use_manpower = $server_actual_manpower * $construction_days_per_floor;
		}

		// 優先用資料庫天數來除
		if ($total_days > 0) {
			$actual_manpower = ceil($use_manpower / $total_days);

		// 沒有資料庫天數，就用樓層施工天數
		} elseif ($construction_days_per_floor > 0) {
			$actual_manpower = ceil($use_manpower / $construction_days_per_floor);
		}

	// 差額:預定標準人力減工班可派人力，當實際出工人力有值，則變成預定標準人力減實際出工人力
	if ($actual_manpower != 0) {
		$manpower_gap = $standard_manpower - $actual_manpower;
		} else {
			$manpower_gap = $standard_manpower - $available_manpower;
		}

		// 如果結果小於 0，強制為 0
		if ($manpower_gap < 0) {
			$manpower_gap = 0;
		}
		// 設定顏色樣式
		if ($manpower_gap > 0) {
			// 有缺額 → 紅字粗體
			$gap_style = "<span style='color:red;font-weight:bold;'>{$manpower_gap}</span>";
		} else {
			// 無缺額 → 黑字粗體
			$gap_style = "<span style='color:black;font-weight:bold;'>{$manpower_gap}</span>";
		}
	

    $mDB->remove();

       $objResponse->assign("actual_manpower".$auto_seq,"innerHTML",$actual_manpower);
	   $objResponse->assign("manpower_gap".$auto_seq, "innerHTML", $gap_style);
    return $objResponse;
}

// function returnValue($auto_seq, $case_id, $engineering_date, $building, $floor, $construction_days_per_floor,$server_actual_manpower,$available_manpower,$standard_manpower){
//     $objResponse = new xajaxResponse();

//     $actual_manpower = "";
// 	  $start_date = $engineering_date;
	 
// 	$Qry = "SELECT SUM(b.manpower) AS total_manpower
// 				,COUNT(DISTINCT a.dispatch_date) AS total_days
// 				FROM dispatch a
// 				LEFT JOIN dispatch_construction b ON b.dispatch_id = a.dispatch_id
// 				LEFT JOIN construction c ON c.construction_id = b.construction_id
// 				WHERE c.case_id = '$case_id' 
// 				AND a.dispatch_date >='$start_date'
// 				AND b.building = '$building'
//  				AND b.floor = '$floor'
//     ";

// 	$actual_manpower .=<<<EOT
// 			<div>$Qry</div>
// 			<div>$auto_seq</div>
// 			<div>$auto_seq</div>
// 			<div>$case_id</div>
// 			<div>$engineering_date</div>
// 			<div>$building</div>
// 			<div>$floor</div>
// 			<div>$construction_days_per_floor</div>
// 			<div>$server_actual_manpower</div>
// 			<div>$available_manpower</div>
// 			<div>$standard_manpower</div>
// 			<div>$start_date</div>


// EOT;
	



//        $objResponse->assign("actual_manpower".$auto_seq,"innerHTML",$actual_manpower);

//     return $objResponse;
// }




//    $objResponse->assign("actual_manpower".$auto_seq,"innerHTML",$actual_manpower);
//     return $objResponse;
// }





$xajax->registerFunction("DeleteRow");
function DeleteRow($auto_seq,$case_id,$memberID){

	$objResponse = new xajaxResponse();
	
	$mDB = "";
	$mDB = new MywebDB();
	$mDB2 = "";
	$mDB2 = new MywebDB();

	//刪除主資料
	$Qry="delete from overview_manpower_sub where auto_seq = '$auto_seq'";
	$mDB->query($Qry);

	
	// 更新主檔
    $Qry2="UPDATE CaseManagement 
           SET last_modify8 = NOW(), makeby8 = '$memberID' , update_count8 = update_count8 + 1
           WHERE case_id = '$case_id'";
    $mDB2->query($Qry2);
	
	$mDB->remove();
	$mDB2->remove();
	
    $objResponse->script("oTable = $('#overview_manpower_sub_table').dataTable();oTable.fnDraw(false)");
	$objResponse->script("autoclose('提示', '資料已刪除！', 500);");

	return $objResponse;
	
}

$xajax->processRequest();




$fm = $_GET['fm'];
$case_id = $_GET['case_id'];
$seq = $_GET['seq'];
$seq2 = $_GET['seq2'];

$today = date("Y-m-d");

$dataTable_de = getDataTable_de();
$Prompt = getlang("提示訊息");
$Confirm = getlang("確認");
$Cancel = getlang("取消");

//取得棟別及工程說明
$overview_building_row = getkeyvalue2($site_db.'_info','overview_building',"auto_seq = '$seq2'",'building,eng_description');
$building = $overview_building_row['building'];
$eng_description = $overview_building_row['eng_description'];


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

</style>

EOT;


$show_modify_btn=<<<EOT
<div class="inline ms-5">
	<div class="btn-group" role="group" style="margin-top:-2px;">
		<button type="button" class="btn btn-danger px-4 text-nowrap" onclick="openfancybox_edit('/index.php?ch=overview_manpower_sub_modify&times=5&case_id=$case_id&seq=$seq&seq2=$seq2&fm=$fm',800,420,'');"><i class="bi bi-plus-circle"></i>&nbsp;乙次新增5個樓層</button>
		<button type="button" class="btn btn-danger text-nowrap" onclick="openfancybox_edit('/index.php?ch=overview_manpower_sub_add&case_id=$case_id&seq=$seq&seq2=$seq2&fm=$fm',800,400,'');"><i class="bi bi-plus-circle"></i>&nbsp;新增資料</button>
		<button type="button" class="btn btn-success text-nowrap" onclick="overview_manpower_sub_Draw();"><i class="bi bi-arrow-repeat"></i>&nbsp;重整</button>
	</div>
</div>
EOT;



$list_view=<<<EOT
<div class="w-100 m-auto p-1 mb-5 bg-white">
	<div style="position: relative;margin: 0 0 -30px 170px;">
		<div class="inline size14 me-5">棟別：<span class="blue02 weight">$building</span></div>
		<div class="inline size14">工程說明：<span class="blue02 weight">$eng_description</span></div>
	</div>
	<table class="table table-bordered border-dark w-100" id="overview_manpower_sub_table" style="min-width:1160px;">
		<thead class="table-light border-dark">
			<tr style="border-bottom: 1px solid #000;">
				<th class="text-center text-nowrap" style="width:15%;padding: 10px;background-color: #CBF3FC;">進場日</th>
				<th class="text-center text-nowrap" style="width:15%;padding: 10px;background-color: #CBF3FC;">預計進場日</th>
				<th class="text-center text-nowrap" style="width:15%;padding: 10px;background-color: #CBF3FC;">樓層數</th>

				<th class="text-center text-nowrap" style="width:10%;padding: 10px;background-color: #CBF3FC;">預定標準人力</th>
				<th class="text-center text-nowrap" style="width:10%;padding: 10px;background-color: #CBF3FC;">工班可派人力</th>
				<th class="text-center text-nowrap" style="width:10%;padding: 10px;background-color: #CBF3FC;">實際出工人力</th>

				<th class="text-center text-nowrap" style="width:5%;padding: 10px;background-color: #CBF3FC;">差額</th>
				<th class="text-center text-nowrap" style="width:15%;padding: 10px;background-color: #CBF3FC;">人員差異</th>
				<th class="text-center text-nowrap" style="width:5%;padding: 10px;background-color: #CBF3FC;">處理</th>
			</tr>
			</thead>
		<tbody class="table-group-divider">
			<tr>
				<td colspan="6" class="dataTables_empty">資料載入中...</td>
			</tr>
		</tbody>
	</table>
</div>
EOT;

$show_savebtn=<<<EOT
<div class="btn-group" role="group" style="margin-top:10px;">
	<button id="close" class="btn btn-danger" type="button" onclick="parent.manpower_sub_myDraw();parent.$.fancybox.close();" style="padding: 5px 15px;"><i class="bi bi-power"></i>&nbsp;關閉</button>
</div>
EOT;


$scroll = true;
if (!($detect->isMobile() && !$detect->isTablet())) {
	$scroll = false;
}
	
	
$show_center=<<<EOT

$style_css

<style type="text/css">
#overview_manpower_sub_table {
	width: 100% !Important;
	margin: 5px 0 0 0 !Important;
}

</style>
<div class="card card_full">
	<div class="card-header text-bg-info">
		<div class="size14 weight float-start" style="margin-top: 5px;">
			工程人力 $show_modify_btn
		</div>
		<div class="float-end" style="margin-top: -5px;">
			$show_savebtn
		</div>
	</div>
	<div id="full" class="card-body p-3" data-overlayscrollbars-initialize>
		<div id="info_container">
			$list_view
		</div>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
	var oTable;
	var baseScheduledDate = null;
	$(document).ready(function() {
		$('#overview_manpower_sub_table').dataTable( {
			"processing": true,
			"serverSide": true,
			"responsive":  {
				details: true
			},//RWD響應式
			"scrollX": '$scroll',
			/*"scrollY": 600,*/
			"paging": true,
			"pageLength": 50,
			"lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
			"pagingType": "full_numbers",  //分页样式： simple,simple_numbers,full,full_numbers
			"searching": true,  //禁用原生搜索
			"ordering": false,
			"ajaxSource": "/smarty/templates/$site_db/$templates/sub_modal/project/func06/manpower_ms/server_overview_manpower_sub.php?site_db=$site_db&case_id=$case_id&seq=$seq&seq2=$seq2&fm=$fm",
			"language": {
						"sUrl": "$dataTable_de"
						/*"sUrl": '//cdn.datatables.net/plug-ins/1.12.1/i18n/zh-HANT.json'*/
					},
			"fixedHeader": true,
			"fixedColumns": {
        		left: 1,
    		},
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) { 


				// 進場日
				var actual_entry_date = "";
				var engineering_date = "";
				var date_status = "";

				if (aData[11] && aData[11] !== "0000-00-00") {
					actual_entry_date = aData[11];
				}
				if (aData[3] && aData[3] !== "0000-00-00") {
					engineering_date = aData[3];
				}

				date_status = aData[17]; 

				if (engineering_date !== "") {
					if (engineering_date === actual_entry_date || date_status === "Y") {
						
						$('td:eq(0)', nRow).html(
							'<div class="d-flex justify-content-center align-items-center size12 text-center" ' +
							'style="height:auto;min-height:32px;">' +
							engineering_date + ' <span style="color:green;">&nbsp;(實際)</span></div>'
						);
					} else {
						
						$('td:eq(0)', nRow).html(
							'<div class="d-flex justify-content-center align-items-center size12 text-center" ' +
							'style="height:auto;min-height:32px;">' +
							engineering_date + ' <span style="color:blue;">&nbsp;(預計)</span></div>'
						);
					}
				} else {
					
					$('td:eq(0)', nRow).html(
						'<div class="d-flex justify-content-center align-items-center size12 text-center" ' +
						'style="height:auto;min-height:32px;">—</div>'
					);
				}

					// 預計進場日 (只抓第一筆作基準)
					
					if (baseScheduledDate === null && aData[14] && aData[14] !== "0000-00-00") {
						baseScheduledDate = aData[14];
					}

					var construction_days_first_floor = 0;
					if (aData[16] && aData[16] !== "0") {
						construction_days_first_floor = parseInt(aData[16], 10);
					}

					var construction_days_per_floor = 0;
					if (aData[15] && aData[15] !== "0") {
						construction_days_per_floor = parseInt(aData[15], 10);
					}

					// 計算新的日期：根據 row index 決定要加的天數
					var scheduled_entry_date_tooltip = "";
					if (baseScheduledDate) {
						var n = iDisplayIndex; // 0-based row index (第一筆為 0)
						var offsetDays = 0;

						if (n === 0) {
							// 第一筆：基準日，不加天數
							offsetDays = 0;
						} else if (n === 1) {
							// 第二筆：從基準日加首層施工天數
							offsetDays = construction_days_first_floor || 0;
						} else {
							// 第三筆以後：首層 + (n-1) * 每層施工天數
							offsetDays = (construction_days_first_floor || 0) + (n - 1) * (construction_days_per_floor || 0);
						}

						var baseDate = new Date(baseScheduledDate);
						baseDate.setDate(baseDate.getDate() + offsetDays);

						// 格式化 yyyy-mm-dd
						var yyyy = baseDate.getFullYear();
						var mm = ("0" + (baseDate.getMonth() + 1)).slice(-2);
						var dd = ("0" + baseDate.getDate()).slice(-2);
						scheduled_entry_date_tooltip = yyyy + "-" + mm + "-" + dd;
					}

					$('td:eq(1)', nRow).html(
						'<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">' +
						scheduled_entry_date_tooltip +
						'</div>'
					);


				//樓層數
				var floor = "";
				if (aData[4] != null && aData[4] != "0")
					floor = aData[4];

				$('td:eq(2)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+floor+'</div>' );

				//標準人力
				var standard_manpower = "";
				if (aData[5] != null )
					standard_manpower = aData[5];

				$('td:eq(3)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+standard_manpower+'</div>' );

				//可派人力
				var available_manpower = "";
				if (aData[6] != null )
					available_manpower = aData[6];

				$('td:eq(4)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+available_manpower+'</div>' );

				//實際出工人力
				var actual_manpower = '<div id="actual_manpower'+aData[0]+'"></div>';
				
				xajax_returnValue(aData[0],aData[1],engineering_date,aData[18],floor,construction_days_per_floor,aData[12],aData[6],aData[5]);

				$('td:eq(5)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+actual_manpower+'</div>' );

				// 差額
				var manpower_gap = '<div id="manpower_gap'+aData[0]+'"></div>';

					// 預設顏色 class
					var colorClass = "red";

					// 如果 manpower_gap 是 0 (數字或字串)
					if (manpower_gap == 0) {
						colorClass = "black";
					}

					$('td:eq(6)', nRow).html(
						'<div class="d-flex justify-content-center align-items-center size12 ' + colorClass + ' weight text-center" style="height:auto;min-height:32px;">'
						+ manpower_gap +
						'</div>'
);

				//人員差異
				var manpower_type = "";
				if (aData[13] != null && aData[13] != "0")
					manpower_type = aData[13];

				$('td:eq(7)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 weight text-center" style="height:auto;min-height:32px;">'+manpower_type+'</div>' );

				//處理
				var url1 = "openfancybox_edit('/index.php?ch=overview_manpower_sub_modify&auto_seq="+aData[0]+"&fm=$fm',800,420,'');";
				var mdel = "myDel(" + aData[0] + ", '$case_id', '$memberID');";

				var show_btn = '';
				/*
				if (('$powerkey'=="A") || ('$super_admin'=="Y")) {
					show_btn = '<div class="btn-group text-nowrap">'
						+'<button type="button" class="btn btn-light" onclick="'+url1+'" title="修改"><i class="bi bi-pencil-square"></i></button>'
						+'<button type="button" class="btn btn-light" onclick="'+mdel+'" title="刪除"><i class="bi bi-trash"></i></button>'
						+'</div>';
				} else {
					show_btn = '<div class="btn-group text-nowrap">'
						+'<button type="button" class="btn btn-light" onclick="'+url1+'" title="修改"><i class="bi bi-pencil-square"></i></button>'
						+'</div>';
				}
				*/

				show_btn = '<div class="btn-group text-nowrap">'
					+'<button type="button" class="btn btn-light" onclick="'+url1+'" title="修改"><i class="bi bi-pencil-square"></i></button>'
					+'<button type="button" class="btn btn-light" onclick="'+mdel+'" title="刪除"><i class="bi bi-trash"></i></button>'
					+'</div>';


				$('td:eq(8)', nRow).html( '<div class="d-flex justify-content-center align-items-center text-center" style="height:auto;min-height:32px;">'+show_btn+'</div>' );



				return nRow;
			
			}
			
		});
	
		/* Init the table */
		oTable = $('#overview_manpower_sub_table').dataTable();
		
	} );

var myDel = function(auto_seq,case_id,memberID) {

	Swal.fire({
	title: "您確定要刪除此筆資料嗎?",
	text: "此項作業會刪除所有與此筆案件記錄有關的資料",
	icon: "question",
	showCancelButton: true,
	confirmButtonColor: "#3085d6",
	cancelButtonColor: "#d33",
	cancelButtonText: "取消",
	confirmButtonText: "刪除"
	}).then((result) => {
		if (result.isConfirmed) {
			xajax_DeleteRow(auto_seq, case_id, memberID);
		}
	});

};

var overview_manpower_sub_Draw = function(){
	var oTable;
	oTable = $('#overview_manpower_sub_table').dataTable();
	oTable.fnDraw(false);
}

	
</script>

EOT;

?>