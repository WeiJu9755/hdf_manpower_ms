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


$fm = $_GET['fm'];

$sure_to_delete = getlang("您確定要刪除此筆資料嗎?");

$dataTable_de = getDataTable_de();
$Prompt = getlang("提示訊息");
$Confirm = getlang("確認");
$Cancel = getlang("取消");


//取得 overview_sub 第一筆
$overview_sub_row = getkeyvalue2($site_db."_info","overview_sub","case_id = '$case_id' LIMIT 1","auto_seq");
$seq = $overview_sub_row['auto_seq'];


$list_view=<<<EOT
<div class="container-fluid">
	<div class="row">
		<div class="col-lg-2 col-sm-12 col-md-12">
			<div class="p-2">
				<table class="table table-bordered border-dark w-100" id="manpower_sub_table">
					<thead class="table-light border-dark">
						<tr style="border-bottom: 1px solid #000;">
							<th scope="col" class="text-center text-nowrap vmiddle" style="width:100%;">工程概況</th>
						</tr>
					</thead>
					<tbody class="table-group-divider">
						<tr>
							<td colspan="2" class="dataTables_empty">資料載入中...</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div> 
		<div class="col-lg-10 col-sm-12 col-md-12">
			<div class="p-2">
				<div id="BuildingBox"></div>
			</div>
		</div> 
	</div>
</div>
EOT;



$scroll = true;
if (!($detect->isMobile() && !$detect->isTablet())) {
	$scroll = false;
}


$show_manpower_sub=<<<EOT
<style>
#manpower_sub_table {
	width: 100% !Important;
	margin: 5px 0 0 0 !Important;
}
#overview_building_table {
	width: 100% !Important;
	margin: 5px 0 0 0 !Important;
}


a:link {
  color: #20809B !important; /* 未造訪的連結 */
}

a:visited {
  color: #6610f2 !important; /* 已造訪的連結 */
}

a:hover {
  color: #0056b3 !important; /* 滑鼠懸停 */
  text-decoration: underline; /* 顯示底線（可選） */
}

a:active {
  color: #dc3545 !important; /* 點擊當下 */
}

</style>

$list_view

<script>
	var oTable;
	$(document).ready(function() {
		$('#manpower_sub_table').dataTable( {
			"processing": false,
			"serverSide": true,
			"responsive":  {
				details: true
			},//RWD響應式
			"scrollX": '$scroll',
			"paging": false,
			"searching": false,  //禁用原生搜索
			"ordering": false,
			"ajaxSource": "/smarty/templates/$site_db/$templates/sub_modal/project/func06/manpower_ms/server_manpower_sub.php?site_db=$site_db&case_id=$case_id",
			"info": false,
			"language": {
						"sUrl": "$dataTable_de"
					},
			"fixedHeader": true,
			"fixedColumns": {
        		left: 1,
    		},
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) { 

				var overview_building = "LoadBuilding('"+aData[0]+"');";


				//工程概況
				var engineering_overview = "";
				if (aData[2] != null && aData[2] != "")
					engineering_overview = aData[2];

				$('td:eq(0)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-start" style="height:auto;min-height:32px;"><a href="javascript:void(0);" class="blue02 weight" onclick="'+overview_building+'">'+engineering_overview+'</a></div>' );

				return nRow;
			}
			
		});
	
		/* Init the table */
		oTable = $('#manpower_sub_table').dataTable();
		
	} );
	

var manpower_sub_myDel = function(auto_seq) {

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
			xajax_DeleteRow(auto_seq);
		}
	});

};


var manpower_sub_myDraw = function(){
	var oTable;
	oTable = $('#manpower_sub_table').dataTable();
	oTable.fnDraw(false);
}




function LoadBuilding(seq) {

var site_db = "$site_db"; 
var case_id = "$case_id"; 
var templates = "$templates"; 
var fm = "$fm"; 
var skins = "$skins"; 

var murl = '/smarty/templates/'+site_db+'/'+templates+'/sub_modal/project/func06/manpower_ms/overview_building.php';

$.ajax({
	url: murl,
	cache: false,
	dataType: 'html',
	type:'GET',
	data: { "site_db": site_db,"case_id": case_id,"seq": seq,"templates": templates,"fm": fm},
	success: function(response) {
		$('#BuildingBox').html(response).fadeIn();
		
	}
});

}

LoadBuilding('$seq');

</script>

EOT;

?>