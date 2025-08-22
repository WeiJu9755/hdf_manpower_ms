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

/*
$xajax->registerFunction("DeleteRow");
function DeleteRow($auto_seq){

	$objResponse = new xajaxResponse();
	
	$mDB = "";
	$mDB = new MywebDB();

	//刪除主資料
	$Qry="delete from CaseManagement where auto_seq = '$auto_seq'";
	$mDB->query($Qry);
	
	$mDB->remove();
	
    $objResponse->script("oTable = $('#db_table').dataTable();oTable.fnDraw(false)");
	$objResponse->script("autoclose('提示', '資料已刪除！', 1500);");

	return $objResponse;
	
}
*/

$xajax->registerFunction("returnValue");
function returnValue($auto_seq,$case_id){
	$objResponse = new xajaxResponse();

	$return_list = "";

	//取得各工地詳細資料
	$mDB = "";
	$mDB = new MywebDB();

	$mDB2 = "";
	$mDB2 = new MywebDB();

	$Qry="SELECT * FROM overview_sub
	WHERE case_id = '$case_id'
	ORDER BY auto_seq";
	$mDB->query($Qry);
	if ($mDB->rowCount() > 0) {

		while ($row=$mDB->fetchRow(2)) {
			$seq = $row['auto_seq'];
			$engineering_overview = $row['engineering_overview'];
			$eng_description = $row['eng_description'];

$return_list.=<<<EOT
<div class="mytable w-100">
	<div class="myrow">
		<div class="mycell w-100">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">工程概況：</div>
					<div class="mycell" style="width:80%;">$engineering_overview</div>
				</div>
			</div>
		</div>
		<!--
		<div class="mycell w-75">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">工程說明：</div>
					<div class="mycell" style="width:80%;">$eng_description</div>
				</div>
			</div>
		</div>
		-->
	</div>
</div>
EOT;


			$Qry2="SELECT a.*,b.subcontractor_name as builder_name,c.subcontractor_name as layout_name FROM overview_building a
			LEFT JOIN subcontractor b ON b.subcontractor_id = a.builder_id
			LEFT JOIN subcontractor c ON c.subcontractor_id = a.layout
			WHERE a.case_id = '$case_id' AND a.seq = '$seq'
			ORDER BY a.auto_seq";
			$mDB2->query($Qry2);
			if ($mDB2->rowCount() > 0) {
		
				while ($row2=$mDB2->fetchRow(2)) {
					$building = $row2['building'];
					$builder_id = $row2['builder_id'];
					$builder_name = $row2['builder_name'];
					$eng_description = $row2['eng_description'];
					$layout = $row2['layout'];
					$layout_name = $row2['layout_name'];
					$layout_number = $row2['layout_number'];
					$scheduled_completion_date = $row2['scheduled_completion_date'];
					$actual_completion_date = $row2['actual_completion_date'];
					$scheduled_entry_date = $row2['scheduled_entry_date'];
					$actual_entry_date = $row2['actual_entry_date'];
					$construction_days_per_floor = $row2['construction_days_per_floor'];
					$works_per_floor = $row2['works_per_floor'];
					$standard_manpower = $row2['standard_manpower'];


$return_list.=<<<EOT
<div class="mytable ms-5 border-bottom" style="width:93%;">
	<div class="myrow">
		<div class="mycell w-25">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">棟別：</div>
					<div class="mycell" style="width:80%;">$building</div>
				</div>
			</div>
		</div>
		<div class="mycell w-25">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">工程說明：</div>
					<div class="mycell" style="width:80%;">$eng_description</div>
				</div>
			</div>
		</div>
		<div class="mycell w-25">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">放樣單位：</div>
					<div class="mycell" style="width:80%;">$layout_name</div>
				</div>
			</div>
		</div>
		<div class="mycell w-25">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">放樣人數：</div>
					<div class="mycell" style="width:80%;">$layout_number</div>
				</div>
			</div>
		</div>
	</div>
	<div class="myrow">
		<div class="mycell">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">預訂完工日：</div>
					<div class="mycell" style="width:80%;">$scheduled_completion_date</div>
				</div>
			</div>
		</div>
		<div class="mycell">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">實際完工日：</div>
					<div class="mycell" style="width:80%;">$actual_completion_date</div>
				</div>
			</div>
		</div>
		<div class="mycell">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">預訂進場日：</div>
					<div class="mycell" style="width:80%;">$scheduled_entry_date</div>
				</div>
			</div>
		</div>
		<div class="mycell">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">實際進場日：</div>
					<div class="mycell" style="width:80%;">$actual_entry_date</div>
				</div>
			</div>
		</div>
	</div>
	<div class="myrow">
		<div class="mycell">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">每層施工天數：</div>
					<div class="mycell" style="width:80%;">$construction_days_per_floor</div>
				</div>
			</div>
		</div>
		<div class="mycell">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">每層工程量(M2)：</div>
					<div class="mycell" style="width:80%;">$works_per_floor</div>
				</div>
			</div>
		</div>
		<div class="mycell">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">標準人力需求：</div>
					<div class="mycell" style="width:80%;">$standard_manpower</div>
				</div>
			</div>
		</div>
		<div class="mycell">
			<div class="mytable">
				<div class="myrow">
					<div class="mycell weight text-nowrap">代工單位：</div>
					<div class="mycell" style="width:80%;">$builder_name</div>
				</div>
			</div>
		</div>
	</div>
</div>
EOT;
				}
			}

		}
	}

	$mDB2->remove();
	$mDB->remove();

	
	$objResponse->assign("return_list".$auto_seq,"innerHTML",$return_list);

    return $objResponse;
}

$xajax->processRequest();


$fm = $_GET['fm'];
//$pjt = $_GET['pjt'];
//$project_id = $_GET['project_id'];
//$auth_id = $_GET['auth_id'];

$project_id = "202502030001";
$auth_id = "OV03";
if (isset($_GET['pjt']))
	$pjt = $_GET['pjt'];
else
	$pjt = "工程人力";


$tb = "CaseManagement";
$pro_id = "manpower";

$m_t = urlencode($_GET['pjt']);

$mess_title = $pjt;


$today = date("Y-m-d");

$dataTable_de = getDataTable_de();
$Prompt = getlang("提示訊息");
$Confirm = getlang("確認");
$Cancel = getlang("取消");

$pubweburl = "//".$domainname;



//網頁標題
$page_title = $pjt;
$page_description = trim(strip_tags($pjt));
$page_description = utf8_substr($page_description,0,1024);
$page_keywords = $pjt;

//載入上方索引列模組
@include $m_location."/sub_modal/base/project_index.php";


$m_pjt = urlencode($_GET['pjt']);

$mk = $_GET['mk'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];


$today = date("Y-m-d");


$pubweburl = "//".$domainname;


//載入功能選單模組
@include $m_location."/sub_modal/base/project_menu.php";


$fellow_count = 0;
//取得指定管理人數
$pjmyfellow_row = getkeyvalue2($site_db."_info","pjmyfellow","web_id = '$web_id' and project_id = '$project_id' and auth_id = '$auth_id' and pro_id = '$pro_id'","count(*) as fellow_count");
$fellow_count =$pjmyfellow_row['fellow_count'];
if ($fellow_count == 0)
	$fellow_count = "";

/*
$warning_count = 0;
//取得指定管理人數(警訊通知對象)
$pjmyfellow_row = getkeyvalue2($site_db."_info","pjmyfellow","web_id = '$web_id' and project_id = '$project_id' and auth_id = '$auth_id' and pro_id = 'alertlist'","count(*) as warning_count");
$warning_count =$pjmyfellow_row['warning_count'];
if ($warning_count == 0)
	$warning_count = "";
*/

$pjItemManager = false;
//檢查是否為指定管理人
$pjmyfellow_row = getkeyvalue2($site_db."_info","pjmyfellow","web_id = '$web_id' and project_id = '$project_id' and auth_id = '$auth_id' and pro_id = '$pro_id' and member_no = '$memberID'","count(*) as enable_count");
$enable_count =$pjmyfellow_row['enable_count'];
if ($enable_count > 0)
	$pjItemManager = true;


//設定權限
$cando = "N";
if (($powerkey=="A") || ($super_admin=="Y") || ($pjItemManager == true)) {
	$cando = "Y";
}


//取得使用者員工身份
$member_picture = getmemberpict160($memberID);

$member_row = getkeyvalue2("memberinfo","member","member_no = '$memberID'","member_name");
$member_name = $member_row['member_name'];

$employee_row = getkeyvalue2($site_db."_info","employee","member_no = '$memberID'","count(*) as manager_count,employee_name,employee_type,team_id");
$manager_count =$employee_row['manager_count'];
$team_id = $employee_row['team_id'];
if ($manager_count > 0) {
	$employee_name = $employee_row['employee_name'];
	$employee_type = $employee_row['employee_type'];

	$team_row = getkeyvalue2($site_db."_info","team","team_id = '$team_id'","team_name");
	$team_name = $team_row['team_name'];
} else {
	$employee_name = $member_name;
	$team_name = "未在員工名單";
}


$member_logo=<<<EOT
<div class="mytable bg-white m-auto rounded">
	<div class="myrow">
		<div class="mycell" style="text-align:center;width:73px;padding: 5px 0;">
			<img src="$member_picture" height="75" class="rounded">
		</div>
		<div class="mycell text-start p-2 vmiddle" style="width:107px;">
			<div class="size14 blue02 weight mb-1 text-nowrap">$employee_name</div>
			<div class="size12 weight text-nowrap">$team_name</div>
			<div class="size12 weight text-nowrap">$employee_type</div>
		</div>
	</div>
</div>
EOT;


$show_disabled = "";
$show_disabled_warning = "";
/*
//if ((empty($team_id)) || ((($super_admin=="Y") && ($admin_readonly == "Y")) || (($super_advanced=="Y") && ($advanced_readonly == "Y")))) {
if (((($super_admin=="Y") && ($admin_readonly == "Y")) || (($super_advanced=="Y") && ($advanced_readonly == "Y")))) {
	if ($pjItemManager <> "Y") {
		$show_disabled = "disabled";
		$show_disabled_warning = "<div class=\"size12 red weight text-center p-2\">此區為管理人專區，非經授權請勿進行任何處理</div>";
	}
}
*/

//if ($cando == "Y") {
	if (($super_admin == "Y") && ($admin_readonly == "Y")) {
		$show_disabled = "disabled";
		$show_disabled_warning = "<div class=\"size12 red weight text-center p-2\">此區為管理人專區，非經授權請勿進行任何處理</div>";
	}
//}


$show_admin_list = "";


if ($cando == "Y") {

	$show_modify_btn = "";

		if (($powerkey == "A") || (($super_admin=="Y") && ($admin_readonly <> "Y"))) {
$show_admin_list=<<<EOT
<div class="text-center">
	<div class="btn-group me-2 mb-2" role="group">
		<a role="button" class="btn btn-light" href="javascript:void(0);" onclick="openfancybox_edit('/index.php?ch=fellowlist&project_id=$project_id&auth_id=$auth_id&pro_id=$pro_id&t=指定管理人&fm=base',850,'96%',true);" title="指定管理人"><i class="bi bi-shield-fill-check size14 red inline me-2 vmiddle"></i><div class="inline size12 me-2">指定管理人</div><div class="inline red weight vmiddle">$fellow_count</div></a>
		<!--
		<a role="button" class="btn btn-light" href="javascript:void(0);" onclick="openfancybox_edit('/index.php?ch=fellowlist&project_id=$project_id&auth_id=$auth_id&pro_id=alertlist&t=警訊通知對象&fm=base',850,'96%',true);" title="警訊通知對象"><i class="bi bi-bell-fill size14 red inline me-2 vmiddle"></i><div class="inline size12 me-2">警訊通知對象</div><div class="inline red weight vmiddle">$warning_count</div></a>
		-->
	</div>
</div>
EOT;
		}

$show_modify_btn=<<<EOT
<div class="text-center my-2">
	<div class="btn-group me-2 mb-2" role="group">
		<!--
		<button $show_disabled type="button" class="btn btn-danger text-nowrap" onclick="openfancybox_edit('/index.php?ch=add&project_id=$project_id&auth_id=$auth_id&t=$t&fm=$fm',1800,'96%','');"><i class="bi bi-plus-circle"></i>&nbsp;新增資料</button>
		-->
		<button type="button" class="btn btn-success text-nowrap" onclick="myDraw();"><i class="bi bi-arrow-repeat"></i>&nbsp;重整</button>
		<button type="button" class="btn btn-warning text-nowrap" onclick="add_shortcuts('$site_db','$web_id','$templates','$project_id','$auth_id','$pjcaption','$i_caption','$fm','$memberID');"><i class="bi bi-lightning-fill red"></i>&nbsp;加入至快捷列</button>
	</div>
	<div class="btn-group mb-2" role="group">
		<!--
		<a $show_disabled role="button" class="btn btn-success text-nowrap" href="/index.php?ch=case_day_summary&project_id=$project_id&auth_id=$auth_id&fm=$fm" target="_blank"><i class="bi bi-printer"></i>&nbsp;出工日報表</a>
		-->
	</div>
</div>
$show_admin_list
EOT;





$list_view=<<<EOT
<div class="w-100 m-auto p-1 mb-5 bg-white">
	<div style="width:auto;padding: 5px;">
		<div class="inline float-start me-1 mb-2">$left_menu</div>
		<a role="button" class="btn btn-light px-2 py-1 float-start inline me-3 mb-2" href="javascript:void(0);" onClick="parent.history.back();"><i class="bi bi-chevron-left"></i>&nbsp;回上頁</a>
		<a role="button" class="btn btn-light p-1" href="/">回首頁</a>$mess_title
	</div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-2 col-sm-12 col-md-12 p-1 d-flex flex-column justify-content-center align-items-center">
				$member_logo
			</div> 
			<div class="col-lg-8 col-sm-12 col-md-12 p-1">
				<div class="size20 pt-1 text-center">$pjt</div>
				$show_modify_btn
				$show_disabled_warning
			</div> 
			<div class="col-lg-2 col-sm-12 col-md-12">
			</div> 
		</div>
	</div>
	$show_ConfirmSending_btn
	<table class="table table-bordered border-dark w-100" id="db_table" style="min-width:1200px;">
		<thead class="table-light border-dark">
			<tr style="border-bottom: 1px solid #000;">
				<th class="text-center text-nowrap" style="width:5%;padding: 10px;background-color: #CBF3FC;">工地狀態</th>
				<th class="text-center text-nowrap" style="width:5%;padding: 10px;background-color: #CBF3FC;">案件編號</th>
				<th class="text-center text-nowrap" style="width:4%;padding: 10px;background-color: #CBF3FC;">區域</th>
				<th class="text-center text-nowrap" style="width:12%;padding: 10px;background-color: #CBF3FC;">工程名稱</th>
				<th class="text-center text-nowrap" style="width:60%;padding: 10px;background-color: #CBF3FC;">各工地詳細資料</th>
				<th class="text-center text-nowrap" style="width:6%;padding: 10px;background-color: #CBF3FC;">工程人力</th>
				<th class="text-center text-nowrap" style="width:8%;padding: 10px;background-color: #CBF3FC;">最後修改</th>
			</tr>
		</thead>
		<tbody class="table-group-divider">
			<tr>
				<td colspan="7" class="dataTables_empty">資料載入中...</td>
			</tr>
		</tbody>
	</table>
</div>
EOT;



$scroll = true;
if (!($detect->isMobile() && !$detect->isTablet())) {
	$scroll = false;
}
	
	
$show_view=<<<EOT

<style type="text/css">
#db_table {
	width: 100% !Important;
	margin: 5px 0 0 0 !Important;
}

</style>

$list_view

<script type="text/javascript" charset="utf-8">
	var oTable;
	$(document).ready(function() {
		$('#db_table').dataTable( {
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
			"ajaxSource": "/smarty/templates/$site_db/$templates/sub_modal/project/func06/manpower_ms/server_manpower.php?site_db=$site_db&fm=$fm",
			"language": {
						"sUrl": "$dataTable_de"
						/*"sUrl": '//cdn.datatables.net/plug-ins/1.12.1/i18n/zh-HANT.json'*/
					},
			"fixedHeader": true,
			"fixedColumns": {
        		left: 1,
    		},
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) { 

				//工地狀態
				//var site_status_url = "openfancybox_edit('/index.php?ch=site_status&auto_seq="+aData[14]+"&fm=$fm',400,250,'');";
				//var show_site_status_btn = '<button type="button" class="btn btn-light btn-sm me-2" onclick="'+site_status_url+'" title="工地狀態"><i class="bi bi-chevron-down"></i></button>';

				var site_status = "";
				if (aData[22] != null && aData[22] != "")
					site_status = '<span class="size12 text-nowrap">'+aData[22]+'</span>';

				$('td:eq(0)', nRow).html( '<div class="text-center text-nowrap" style="height:auto;min-height:32px;">'+site_status+'</div>' );

				//案件編號
				var case_id = "";
				if (aData[3] != null && aData[3] != "")
					case_id = aData[3];

				$('td:eq(1)', nRow).html( '<div class="d-flex justify-content-center align-items-center text-center size12 weight text-nowrap" style="height:auto;min-height:32px;">'+case_id+'</div>' );

				//區域
				var region = "";
				if (aData[2] != null && aData[2] != "")
					region = aData[2];

				$('td:eq(2)', nRow).html( '<div class="d-flex justify-content-center align-items-center text-center size12 text-nowrap" style="height:auto;min-height:32px;">'+region+'</div>' );

				//工程名稱
				var construction_id = "";
				if (aData[4] != null && aData[4] != "")
					construction_id = aData[4];

				$('td:eq(3)', nRow).html( '<div class="d-flex justify-content-center align-items-center size12 text-center" style="height:auto;min-height:32px;">'+construction_id+'</div>' );

				var return_list = '<div id="return_list'+aData[14]+'"></div>';

				xajax_returnValue(aData[14],aData[3]);


				$('td:eq(4)', nRow).html( '<div style="height:auto;min-height:32px;">'+return_list+'</div>' );


				var url1 = "openfancybox_edit('/index.php?ch=edit&auto_seq="+aData[14]+"&fm=$fm',1800,'96%','');";

				var show_btn = '';
					show_btn = '<button type="button" class="btn btn-light btn-sm text-nowrap" onclick="'+url1+'" title="工程人力"><i class="bi bi-people-fill"></i>&nbsp;工程人力</button>';


				$('td:eq(5)', nRow).html( '<div class="d-flex justify-content-center align-items-center text-center" style="height:auto;min-height:32px;">'+show_btn+'</div>' );


				//最後修改
				var last_modify = "";
				if (aData[9] != null && aData[9] != "")
					last_modify = '<div class="text-nowrap">'+moment(aData[9]).format('YYYY-MM-DD HH:mm')+'</div>';
				
				//編輯人員
				var member_name = "";
				if (aData[17] != null && aData[17] != "")
					member_name = '<div class="text-nowrap">'+aData[17]+'</div>';

				$('td:eq(6)', nRow).html( '<div class="text-center" style="height:auto;min-height:32px;">'+last_modify+member_name+'</div>' );


				return nRow;
			
			}
			
		});
	
		/* Init the table */
		oTable = $('#db_table').dataTable();
		
	} );

var myDel = function(auto_seq) {

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

var myDraw = function(){
	var oTable;
	oTable = $('#db_table').dataTable();
	oTable.fnDraw(false);
}

	
</script>

EOT;

} else {

	$sid = "mbwarning";
	$show_view = mywarning("很抱歉! 目前此功能只開放給本站特定會員，或是您目前的權限無法存取此頁面。");

}

?>