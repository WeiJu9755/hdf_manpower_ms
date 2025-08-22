<?php

session_start();

$memberID = $_SESSION['memberID'];
$powerkey = $_SESSION['powerkey'];


//載入公用函數
@include_once '/website/include/pub_function.php';

@include_once("/website/class/".$site_db."_info_class.php");


$m_location		= "/website/smarty/templates/".$site_db."/".$templates;
$m_pub_modal	= "/website/smarty/templates/".$site_db."/pub_modal";

$sid = "";
if (isset($_GET['sid']))
	$sid = $_GET['sid'];


//程式分類
$ch = empty($_GET['ch']) ? 'default' : $_GET['ch'];
switch($ch) {
	case 'add':
		$title = "新增資料";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/manpower_add.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	case 'edit':
		$title = "工程人力";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/manpower_modify.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
		/*
	case 'mview':
	case 'view':
		$title = "資料瀏覽";
		if (empty($sid))
			$sid = "mbpjitem";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/manpower_view.php";
		include $modal;
		//$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
		*/
	case 'manpower':
		$title = "修改總人力";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/manpower_modify.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	case 'manpower_sub_edit':
		$title = "編輯工程人力";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/manpower_sub_modify.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	case 'ch_employee':
	case 'ch_employee2':
		$title = "員工名單";
		if (empty($sid))
			$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/ch_employee.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	case 'overview_manpower_sub':
		$title = "工程人力";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/overview_manpower_sub.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	case 'overview_manpower_sub_add':
		$title = "新增工程人力";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/overview_manpower_sub_add.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	case 'overview_manpower_sub_modify':
		$title = "修改工程人力";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/overview_manpower_sub_modify.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	case 'overview_building_add':
		$title = "新增棟別";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/overview_building_add.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	case 'overview_building_modify':
		$title = "編輯棟別";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/overview_building_modify.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	case 'eng_description':
		$title = "工程說明";
		$sid = "view01";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/eng_description.php";
		include $modal;
		$smarty->assign('show_center',$show_center);
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
	default:
		if (empty($sid))
			$sid = "mbpjitem";
		$modal = $m_location."/sub_modal/project/func06/manpower_ms/manpower.php";
		include $modal;
		$smarty->assign('xajax_javascript', $xajax->getJavascript('/xajax/'));
		break;
};

?>