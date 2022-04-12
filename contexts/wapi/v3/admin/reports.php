<?php
use reporting\Reporting;
/**
 * Fetch a list of one's own layers.
 *
 * Parameters:
 *
 * (none)
 *
 * Return:
 *
 * XML representing the list of data layers, or else an error.
 * {@example docs/examples/wapi_error.txt}
 *
 * @package WebAPI
 */
/**
  * @ignore
  */
function _config_reports() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}

function _headers_reports() {
	header('Content-type: application/json');
}

function _dispatch_reports($template, $args) {
$world = $args['world'];
$user = $args['user'];
$full = false;
if(isset($_REQUEST["full"]) && $_REQUEST["full"]) $full = true;
$filter = null;
if(isset($_REQUEST["data"]) && $_REQUEST["data"] !== ''){
	$filter = Array();
	$rawData = json_decode($_REQUEST["data"]);
	foreach($rawData->groups as $groups) $filter[$groups->dataidGroup] = Reporting::columnAndStringToNumber($groups->dataidGroup, $groups->groupbyval);
	$filter[$rawData->pivot->dataidPivot] = Reporting::columnAndStringToNumber($rawData->pivot->dataidPivot, $rawData->pivot->pivotvalue);
}
$dateFilter = null;
if(isset($_REQUEST["range"])){
	$dateFilter = Array();
	$dateFilter["range"] = ($_REQUEST["range"] ? true : false);
	if($_REQUEST["range"]){
		$dateFilter["year"] = $_REQUEST["year"];
		if($_REQUEST["year"] == "*") $dateFilter["year"] = 2000;
		$dateFilter["day"] = $_REQUEST["day"];
		if($_REQUEST["day"] == "*") $dateFilter["day"] = 1;
		$dateFilter["month"] = monthNameToNumber($_REQUEST["month"]);
		if($_REQUEST["month"] == "*") $dateFilter["month"] = 1;
		$dateFilter["year2"] = $_REQUEST["year2"];
		if($_REQUEST["year2"] == "*") $dateFilter["year2"] = 3000;
		$dateFilter["month2"] = monthNameToNumber($_REQUEST["month2"]);
		if($_REQUEST["month2"] == "*") $dateFilter["month2"] = 12;
		$dateFilter["day2"] = $_REQUEST["day2"];
		if($_REQUEST["day2"] == "*") $dateFilter["day2"] = date("d",mktime(0, 0, 0, ($dateFilter["month2"] + 1), 0, $dateFilter["year2"]));
	}else{
		$dateFilter["year"] = $_REQUEST["year"];
		if($_REQUEST["year"] == "*") $dateFilter["year"] = false;
		$dateFilter["day"] = $_REQUEST["day"];
		if($_REQUEST["day"] == "*") $dateFilter["day"] = false;
		$dateFilter["month"] = monthNameToNumber($_REQUEST["month"]);
		if($_REQUEST["month"] == "*") $dateFilter["month"] = false;
	}
}
echo Reporting::getReports($world->db, $full, $filter, $dateFilter);
} 
function monthNameToNumber($name){
	for ($i = 1; $i <= 12; $i++){
		if(date('M', mktime(0,0,0,$i,1)).'.' == $name) return $i;
	}
	return false;
}?>
