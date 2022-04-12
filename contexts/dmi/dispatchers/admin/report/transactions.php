<?php
use utils\PageUtil;
/**
 * View a Layer's statistics: who has it bookmarked, where it's used, etc.
 * @package Dispatchers
 */
/**
  */
function _config_transactions() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_transactions($template, $args,$org, $pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	$pageArgs['pageTitle'] = 'Admin - Account Transactions Log';
	PageUtil::SetPageArgs($pageArgs, $template);
	$user = $args['user'];
	$world = $args['world'];

	
	$db = $world->db;
	$years = Array('*');
	$months = Array('*');
	$days = Array('*');
	foreach($db->Execute("SELECT EXTRACT(YEAR FROM timestamp) AS year FROM _reporting GROUP BY year ORDER BY year DESC")->getRows() as $row) $years[] = $row["year"];
	for ($i = 1; $i <= 31; $i++) $days[] = $i;
	for ($i = 1; $i <= 12; $i++) $months[] = date('M', mktime(0,0,0,$i,1)).'.';
	$today = getdate();
	$template->assign('years', $years);
	$template->assign('months', $months);
	$template->assign('days', $days);
	$template->assign('selectYear', $today["year"]);
	$template->assign('selectMonth', substr($today["month"], 0, 3).'.');
	$template->assign('selectDay', $today["mday"]);
	
		/*$subnav = new AdminSubnav();
		$subnav->makeDefault($args["user"], "Layer Transactions Log");
		$template->assign('subnav',$subnav->fetch());*/
	
	$template->display('admin/report/transactions.tpl');
}?>