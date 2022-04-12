<?php
use views\GroupViews;

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
// Report all PHP errors (see changelog)
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);


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
	$viewType = $_REQUEST['type'];
	$views = null;
	$views = new GroupViews($user->id,$world->db);
	if( !$views) throw new Exception("No views found for specified user");
	$results = null;
	//echo urlencode(json_encode(Array(Array("name","contains","test"))));
	$filter = (isset($_REQUEST['filter']) ? json_decode(urldecode($_REQUEST['filter'])) : false);
	$and = ((isset($_REQUEST['and']) and $_REQUEST['and'] == "or") ? false : true);
	$permissionsFor = false;
	if(isset($_REQUEST['shareproject'])) $permissionsFor = Array('shareproject', $_REQUEST['shareproject']);
	if(isset($_REQUEST['sharelayer'])) $permissionsFor = Array('sharelayer', $_REQUEST['sharelayer']);
	switch( strtolower( trim($viewType) ) ) {
	    case "mine":
	        $results = $views->GetMine($permissionsFor,$filter,$and);
	        break;
		case "imoderate":
			$results = $views->GetIModerate($permissionsFor,$filter,$and);
			break;
		case "iamin":
			$results = $views->GetIAmIn($permissionsFor,$filter,$and);
			break;
		case "open":
			$results = $views->GetOpen($permissionsFor,$filter,$and);
			break;
		case "invite":
			$results = $views->GetInvite($permissionsFor,$filter,$and);
			break;
		case "tag":
			$results = $views->GetByTag( $_REQUEST['tag'],$permissionsFor,$filter,$and);
			break;
	}
	
	if(!$results) throw new Exception("Invalid Parameters: Type; $viewType not recognized");
	
	switch(strtolower($format)) {
		case "ajax":
		case "xml":
			ResultsToXML($results);
			break;
		case "json":
			ResultsToJSON($results, $views);
			break;
	}
}

function ResultsToXML($results) {
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

function ResultsToJSON($results, $views) {
	$array["view"] = Array();
	if($results->_numOfRows != 0){
		foreach($results as $resultid => $result ) {
			$members = $views->GetMembers($result["id"])+1;
			$status = $views->GetStatus($result["id"]);
			//$layers = $views->GetLayers($result["id"]);
			foreach($result as $key=>$val) {
				$array["view"][$resultid][$key] = $val;
			}
			$array["view"][$resultid]["members"] = $members;
			$array["view"][$resultid]["status"] = $status;
		}
	}
	echo json_encode($array);
}


?>
