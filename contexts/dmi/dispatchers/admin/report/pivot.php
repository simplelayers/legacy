<?php
/**
  * Administration: Form to create a new user.
  * @package Dispatchers
  */
/**
  */
function _config_pivot() {
	$config = Array();
	// Start config
	$config["sendUser"] = true;
	$config["sendWorld"] = true;
	$config["admin"] = false;
	// Stop config
	return $config;
}

function _dispatch_pivot($template, $args) {
$world = $args["world"];
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
$template->display('admin/report/pivot.tpl');

}?>
