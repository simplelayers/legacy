<?php
use views\LayerUserViews;
use views\ProjectUserViews;
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
function _config_views() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	// Stop config
	return $config;
}

function _headers_views() {
	if(!isset($_REQUEST['format'] )) $_REQUEST['format'] = 'json';
	return;
	switch($_REQUEST['format'] ) {
		case "json":
			#header('Content-Type: text/javascript; charset=UTF-8'); 
			header('Content-Type: application/json');
			break;
		case "ajax":
		case "xml":
			header('Content-Type: text/xml');
			break;
	}	
}

function _dispatch_views($template, $args) {
	
	$world = $args['world'];
	$user = $args['user'];

	$format = $_REQUEST['format'];
	$object = $_REQUEST['object'];
	$viewType = $_REQUEST['type'];
	$views = null;
	
	switch( strtolower(trim($object)) ) {
		case "layer":
			
			$views  = new LayerUserViews($user->id,$world->db);
			
			break;
		case "project":
			$views = new ProjectUserViews($user->id,$world->db);
			break;
	}
	
	if( !$views) throw new Exception("Invalid Parameters: Object; $object not recognized");
	$results = null;

	
		//echo urlencode(json_encode(Array(Array("name","contains","test"))));
	$filter = (isset($_REQUEST['filter']) ? json_decode(urldecode($_REQUEST['filter'])) : false);
	$and = ((isset($_REQUEST['and']) and $_REQUEST['and'] == "or") ? false : true);
	
	switch( strtolower( trim($viewType) ) ) {
		case "mine":
			$results = $views->GetMine($filter,$and);
			break;
		case "marks":
			$results = $views->GetBookMarked($filter,$and);
			break;
		case "owners":
			$results = $views->GetOwners((isset($_REQUEST['min']) ? (int)$_REQUEST['min'] : 1), $filter,$and);
			break;
		case "owner":
			if( !isset($_REQUEST['owner'])  )$_REQUEST['owner'] = -1;
			$results = $views->GetByOwner( (int)$_REQUEST['owner'], (isset($_REQUEST['min']) ? (int)$_REQUEST['min'] : 1), $filter,$and);
			break;
		case "groups":
			$results = $views->GetGroups($filter,$and);
			break;
		case "group":
			if( !isset($_REQUEST['id']) )throw new \Exception("Group not found: id not specified");
			$results = $views->GetByGroup( (int)$_REQUEST['id'], $filter,$and);
			break;
		case "tag":
			$results = $views->GetByTag( $_REQUEST['tag'],$filter,$and);
			break;
	}
	
	if(!$results) throw new Exception("Invalid Parameters: Type; $viewType not recognized");
	
	switch(strtolower($format)) {
		case "ajax":
		case "xml":
			ResultsToXML($results);
			break;
		case "json":
			ResultsToJSON($results);
			break;
	}
	
	

}

function ResultsToXML($results) {
    WAPI::SetWapiHeaders(WAPI::FORMAT_XML);
	echo '<?xml version="1.0" encoding="UTF-8" ?>';
	echo "<view>";
	foreach($results as $result) {
		echo "\n\t<item";
		foreach( $result as $att=>$val) {
			if( !$val) $val = "";

			$val=htmlentities($val);
			//echo("<br/>");
			echo " $att=\"$val\"";
		}
		echo ">\n\t</item>";
	}
	echo "</view>";
}

function ResultsToJSON($results) {
    WAPI::SetWapiHeaders(WAPI::FORMAT_JSON);
	$array["view"] = Array();
	#ResponseUtil::Write('{"view":');
	$i=0;
	$numResults = iterator_count($results);
	foreach($results as $resultid => $result ) {
		#ResponseUtil::Write('[');
		foreach($result as $key=>$val) {
			#ResponseUtil::Write('"'.$key.'":"'.$val.'"');
			$array["view"][$resultid][$key] = $val;			
		}
		#ResponseUtil::Write(']');
		#if($i<$numResults) ResponseUtil::Write(',');
		$i+=1;
	}
	die(json_encode($array));
	#ResponseUtil::Write('}');
	die();
	
}


?>
