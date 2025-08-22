<?php


//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

require_once '/website/os/Mobile-Detect-2.8.34/Mobile_Detect.php';
$detect = new Mobile_Detect;

if( $detect->isMobile() && !$detect->isTablet() ){
	$isMobile = 1;
} else {
	$isMobile = 0;
}


//載入公用函數
@include_once '/website/include/pub_function.php';

//取得預設值
$xml_lang=simplexml_load_file("/website/locale/locale.xml");

function getlang($key) {
	$gb_xml = $GLOBALS['xml_lang'];
	//$myLang = $_COOKIE["lang"];
	$gb_row_web = $GLOBALS['row_web_lang'];
	$myLang = $gb_row_web["appId"];
	$result = $gb_xml->xpath('//LOCALES/LOCALE[@key="'.$key.'"]')[0][$myLang];
	if (isnullorempty($result))
		$result = $key;
	return $result;
}

function getDataTable_de() {
	//$myLang = $_COOKIE["lang"];
	$gb_row_web = $GLOBALS['row_web_lang'];
	$myLang = $gb_row_web["appId"];
	if ($myLang == "en_US") {
		$dataTable_de = "/pub_style/de_US.txt";
	} else if ($myLang == "zh_CN") {
		$dataTable_de = "/pub_style/de_CN.txt";
	} else if ($myLang == "ja_JP") {
		$dataTable_de = "/pub_style/de_JP.txt";
	} else {
		$dataTable_de = "/pub_style/de_TW.txt";
	}
	return $dataTable_de;
}


$fm = $_GET['fm'];
$site_db = $_GET['site_db'];
$templates = $_GET['templates'];
$case_id = $_GET['case_id'];
$seq = $_GET['seq'];



$dataTable_de = getDataTable_de();

$sure_to_delete = getlang("您確定要刪除此筆資料嗎?");
$Prompt = getlang("提示訊息");
$Confirm = getlang("確認");
$Cancel = getlang("取消");


$show_fellow_btn=<<<EOT
<div class="btn-group" role="group">
	<button type="button" class="btn btn-danger mb-1 px-4" onclick="openfancybox_edit('/index.php?ch=overview_building_add&case_id=$case_id&seq=$seq&fm=$fm',1200,600,'');"><i class="bi bi-plus-circle"></i>&nbsp;新增棟別</button>
	<button type="button" class="btn btn-success text-nowrap mb-1 px-4" onclick="overview_building_table();"><i class="bi bi-arrow-repeat"></i>&nbsp;重整</button>
</div>
EOT; 


$overview_sub_row = getkeyvalue2($site_db.'_info','overview_sub',"auto_seq = '$seq'",'engineering_overview');
$engineering_overview = $overview_sub_row['engineering_overview'];


$list_view=<<<EOT
<div class="container-fluid">
	<div class="row">
		<div class="col-lg-12 col-sm-12 col-md-12">
			<div>
				<div class="inline size14 weight text-nowrap me-5">工程概況: <span class="blue02">$engineering_overview</span></div>
				<div class="inline">$show_fellow_btn</div>
			</div>
			<div>
				<table class="table table-bordered border-dark w-100" id="overview_building_table">
					<thead class="table-light border-dark">
						<tr style="border-bottom: 1px solid #000;">
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:7%;">棟別</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:14%;">工程說明</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:7%;">放樣單位</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:5%;">放樣人數</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:7%;">預訂<br>完工日</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:7%;">實際<br>完工日</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:7%;">預訂<br>進場日</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:7%;">實際<br>進場日</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:5%;">每層<br>施工天數</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:5%;">每層<br>工程量(M2)</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:5%;">標準<br>人力需求</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:7%;">代工單位</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:7%;">編輯</th>
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:10%;">工程人力</th>
						</tr>
					</thead>
					<tbody class="table-group-divider">
						<tr>
							<td colspan="14" class="dataTables_empty">資料載入中...</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div> 
	</div>
</div>
EOT;



$scroll = true;
if (!($detect->isMobile() && !$detect->isTablet())) {
	$scroll = false;
}


$show_overview_building=<<<EOT
<style>
#overview_building_table {
	width: 100% !Important;
	margin: 5px 0 0 0 !Important;
}
#overview_building_table {
	width: 100% !Important;
	margin: 5px 0 0 0 !Important;
}
</style>

$list_view

<script>
	var oTable;
	$(document).ready(function() {
		$('#overview_building_table').dataTable( {
			"processing": false,
			"serverSide": true,
			"responsive":  {
				details: true
			},//RWD響應式
			"scrollX": '$scroll',
			"paging": false,
			"searching": false,  //禁用原生搜索
			"ordering": false,
			"ajaxSource": "/smarty/templates/$site_db/$templates/sub_modal/project/func06/manpower_ms/server_overview_building.php?site_db=$site_db&case_id=$case_id&seq=$seq",
			"info": false,
			"language": {
						"sUrl": "$dataTable_de"
					},
			"fixedHeader": true,
			"fixedColumns": {
        		left: 1,
    		},
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) { 

				//棟別
				var building = "";
				if (aData[14] != null && aData[14] != "")
					building = aData[14];

				$('td:eq(0)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+building+'</div>' );

				//工程說明
				var eng_description = "";
				if (aData[15] != null && aData[15] != "")
					eng_description = aData[15];

				$('td:eq(1)', nRow).html( '<div class="d-flex justify-content-start align-items-center size12 text-start" style="height:auto;min-height:32px;">'+eng_description+'</div>' );
			
				//放樣
				var layout = '<div id="layout'+aData[11]+'"></div>';
				//代工單位
				//var builder_id = '<div id="builder_id'+aData[11]+'"></div>';

				xajax_returnValue(aData[11],aData[0]);

				$('td:eq(2)', nRow).html( '<div class="d-flex justify-content-center align-items-center text-center" style="height:auto;min-height:32px;">'+layout+'</div>' );
				
				//放樣人數
				var layout_number = "";
				if (aData[1] != null && aData[1] != "0")
					layout_number = aData[1];

				$('td:eq(3)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+layout_number+'</div>' );
				
				//預訂完工日
				var scheduled_completion_date = "";
				if (aData[2] != null && aData[2] != "" && aData[2] != "0000-00-00")
					scheduled_completion_date = aData[2];

				$('td:eq(4)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+scheduled_completion_date+'</div>' );

				//實際完工日
				var actual_completion_date = "";
				if (aData[3] != null && aData[3] != "" && aData[3] != "0000-00-00")
					actual_completion_date = aData[3];

				$('td:eq(5)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+actual_completion_date+'</div>' );

				//預訂進場日
				var scheduled_entry_date = "";
				if (aData[4] != null && aData[4] != "" && aData[4] != "0000-00-00")
					scheduled_entry_date = aData[4];

				$('td:eq(6)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+scheduled_entry_date+'</div>' );

				//實際進場日
				var actual_entry_date = "";
				if (aData[5] != null && aData[5] != "" && aData[5] != "0000-00-00")
					actual_entry_date = aData[5];

				$('td:eq(7)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+actual_entry_date+'</div>' );
				
				//每層施工天數
				var construction_days_per_floor = "";
				if (aData[6] != null && aData[6] != "0")
					construction_days_per_floor = aData[6];

				$('td:eq(8)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+construction_days_per_floor+'</div>' );

				//每層工程量(M2)
				var works_per_floor = "";
				if (aData[7] != null && aData[7] != "0")
					works_per_floor = number_format(aData[7]);

				$('td:eq(9)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+works_per_floor+'</div>' );
				
				//標準人力需求
				var standard_manpower = "";
				if (aData[8] != null && aData[8] != "0")
					standard_manpower = aData[8];

				$('td:eq(10)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+standard_manpower+'</div>' );
				
				//代工單位
				var subcontractor_name = "";
				if (aData[10] != null && aData[10] != "")
					subcontractor_name = aData[10];

				$('td:eq(11)', nRow).html( '<div class="d-flex justify-content-center align-items-center text-center" style="height:auto;min-height:32px;">'+subcontractor_name+'</div>' );

				//編輯
				var url1 = "openfancybox_edit('/index.php?ch=overview_building_modify&auto_seq="+aData[11]+"&case_id="+aData[12]+"&fm=$fm',1200,600,'');";
				var mdel = "overview_building_myDel('"+aData[11]+"','"+aData[12]+"','"+aData[13]+"');";

				var show_btn = '';
					show_btn = '<div class="btn-group text-nowrap">'
						+'<button type="button" class="btn btn-light" onclick="'+url1+'" title="編輯"><i class="bi bi-pencil-square"></i></button>'
						+'<button type="button" class="btn btn-light" onclick="'+mdel+'" title="刪除"><i class="bi bi-trash"></i></button>'
						+'</div>';
				
				$('td:eq(12)', nRow).html( '<div class="d-flex justify-content-center align-items-center text-center" style="height:auto;">'+show_btn+'</div>' );

				//工程人力
				var url2 = "openfancybox_edit('/index.php?ch=overview_manpower_sub&case_id="+aData[12]+"&seq="+aData[13]+"&seq2="+aData[11]+"&fm=$fm',1200,'96%','');";

				var show_btn2 = '';
					show_btn2 = '<div class="btn-group text-nowrap">'
						+'<button type="button" class="btn btn-light" onclick="'+url2+'" title="工程人力"><i class="bi bi-person-arms-up"></i>&nbsp;工程人力</button>'
						+'</div>';

				$('td:eq(13)', nRow).html( '<div class="d-flex justify-content-center align-items-center text-center" style="height:auto;">'+show_btn2+'</div>' );
				

				return nRow;
			}
			
		});
	
		
		oTable = $('#overview_building_table').dataTable();
		
	} );
	

var overview_building_myDel = function(auto_seq,case_id,seq) {

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
			xajax_DeleteRow(auto_seq,case_id,seq);
		}
	});

};


var overview_building_myDraw = function(){
	var oTable;
	oTable = $('#overview_building_table').dataTable();
	oTable.fnDraw(false);
}


</script>

EOT;

echo $show_overview_building;

?>