<?php

	session_start();

	$memberID = $_SESSION['memberID'];
	$powerkey = $_SESSION['powerkey'];


	//載入公用函數
	@include_once '/website/include/pub_function.php';

	//檢查是否為管理員及進階會員
	$super_admin = "N";
	$super_advanced = "N";
	$mem_row = getkeyvalue2('memberinfo','member',"member_no = '$memberID'",'admin,advanced,checked,luck,admin_readonly,advanced_readonly');
	$super_admin = $mem_row['admin'];
	$super_advanced = $mem_row['advanced'];


	$site_db = $_GET['site_db'];
	$fm = $_GET['fm'];
	//$ShowConfirmSending = $_GET['ShowConfirmSending'];
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables 詮能
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	
//	$aColumns = array( 'a.auto_seq','a.case_date','a.company_id','b.company_name','b.short_name','a.team_id','c.team_name','a.web_id','a.project_id','a.auth_id','a.case_id'
//			,'a.case_type','a.ConfirmSending','a.ConfirmSending_datetime');

	$aColumns = array( 'a.status1','a.status2','a.region','a.case_id','a.construction_id','a.builder_id','a.contractor_id','a.contact','a.makeby8','a.last_modify8','a.zipcode'
		,'a.county','a.town','a.address','a.auto_seq','c.builder_name','d.contractor_name','f.member_name','a.case_type','a.owner_builder','a.owner_contractor'
		,'a.location_URL','a.site_status');
			
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "auto_seq";
	
	/* DB table to use */
	$sTable = "CaseManagement";
	
//	include( $_SERVER['DOCUMENT_ROOT']."/class/products_db.php" );
	include( "/website/class/".$site_db."_db.php" );
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
	 * no need to edit below this line
	 */
	
	/* 
	 * MySQL connection
	 */
	$gaSql['link'] =  mysql_pconnect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) or
		die( 'Could not open connection to server' );
	
	mysql_select_db( $gaSql['db'], $gaSql['link'] ) or 
		die( 'Could not select database '. $gaSql['db'] );
	
	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	$sOrder = "ORDER BY a.auto_seq DESC";
	/*
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				 	".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	*/
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	
	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
		if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
		}
	}
	
	/*
	 * SQL queries
	 * Get data to display
	 */
	 
	/* 
	if (($powerkey == "A") || ($super_admin == "Y") || ($super_advanced == "Y")) {

		if ($ShowConfirmSending == "Y") {
			if ($sWhere=="")
				$sWhere = "WHERE (a.web_id = '$web_id' and a.project_id = '$project_id' and a.case_date <= CURDATE()) ";
			else
				$sWhere .= " and (a.web_id = '$web_id' and a.project_id = '$project_id' and a.case_date <= CURDATE()) ";
		} else {
			if ($sWhere=="")
				$sWhere = "WHERE (a.web_id = '$web_id' and a.project_id = '$project_id' and a.case_date <= CURDATE() and a.ConfirmSending <> 'Y') ";
			else
				$sWhere .= " and (a.web_id = '$web_id' and a.project_id = '$project_id' and a.case_date <= CURDATE() and a.ConfirmSending <> 'Y') ";
		}

	} else {

		if ($ShowConfirmSending == "Y") {
			if ($sWhere=="")
				$sWhere = "WHERE (a.web_id = '$web_id' and a.project_id = '$project_id' and a.case_date <= CURDATE() and a.team_id = '$team_id') ";
			else
				$sWhere .= " and (a.web_id = '$web_id' and a.project_id = '$project_id' and a.case_date <= CURDATE() and a.team_id = '$team_id') ";
		} else {
			if ($sWhere=="")
				$sWhere = "WHERE (a.web_id = '$web_id' and a.project_id = '$project_id' and a.case_date <= CURDATE() and a.team_id = '$team_id' and a.ConfirmSending <> 'Y') ";
			else
				$sWhere .= " and (a.web_id = '$web_id' and a.project_id = '$project_id' and a.case_date <= CURDATE() and a.team_id = '$team_id' and a.ConfirmSending <> 'Y') ";
		}

	}
	*/


	/*
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere
		$sOrder
		$sLimit
	";
	*/

	/*
	if (($powerkey=="A") || ($super_admin=="Y")) {

		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
			FROM   $sTable
			$sWhere
			$sOrder
			$sLimit
		";
	
	} else {

		if ($super_advanced == "Y") {

			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
				FROM   $sTable  a
				LEFT JOIN company b ON b.company_id = a.company_id
				LEFT JOIN team c ON c.team_id = a.team_id
				RIGHT JOIN group_company d ON d.company_id = a.company_id and d.member_no = '$memberID'
				$sWhere
				$sOrder
				$sLimit
			";

		} else {

			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
				FROM   $sTable  a
				LEFT JOIN company b ON b.company_id = a.company_id
				LEFT JOIN team c ON c.team_id = a.team_id
				$sWhere
				$sOrder
				$sLimit
			";

		}

	}
	*/

	if ($sWhere=="")
		$sWhere = "WHERE a.status1 = '已簽約' AND (a.ContractingModel = '連工帶料(UH)' OR a.ContractingModel = '代工(WH)') ";
	else
		$sWhere .= " and a.status1 = '已簽約' AND (a.ContractingModel = '連工帶料(UH)' OR a.ContractingModel = '代工(WH)') ";

	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable a
		LEFT JOIN builder c ON c.builder_id = a.builder_id
		LEFT JOIN contractor d ON d.contractor_id = a.contractor_id
		LEFT JOIN memberinfo.member f on f.member_no = a.makeby8
		$sWhere
		$sOrder
		$sLimit
		";

	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
	";
	$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultTotal = mysql_fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
	
	while ( $aRow = mysql_fetch_array( $rResult ) )
	{
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( $aColumns[$i] == "version" )
			{
				/* Special output formatting for 'version' column */
				$row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
			}
			else if ( $aColumns[$i] != ' ' )
			{
				/* General output */
				//$row[] = $aRow[ $aColumns[$i] ];

				$field = $aColumns[$i];
				$field = str_replace("a.","",$field);
				$field = str_replace("b.","",$field);
				$field = str_replace("c.","",$field);
				$field = str_replace("d.","",$field);
				$field = str_replace("e.","",$field);
				$field = str_replace("f.","",$field);
				
				$row[] = $aRow[ $field ];
				
			}
		}
		$output['aaData'][] = $row;
	}
	
	echo json_encode( $output );
?>