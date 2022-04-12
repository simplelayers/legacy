<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);


function _config_views() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	$config["admin"] = false;
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
    return false;
	/*$format = $_REQUEST['format'];
	$views = null;
	$views = new InvoiceViews($user->id,$world->db);
	if( !$views) throw new Exception("Invalid Parameters: Object; $object not recognized");
	$results = null;
	if($_REQUEST['org'] == "false" and $user->admin){
		$results = $views->GetAll();
	}else{
		$results = $views->GetByOrg($_REQUEST['org']);
	}
	if(!$results) throw new Exception("Invalid Parameters: Type; org not identified");
	
	switch(strtolower($format)) {
		case "ajax":
		case "xml":
			ResultsToXML($results);
			break;
		case "json":
			ResultsToJSON($results, $views);
			break;
	}*/
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
			foreach($result as $key=>$val) {
				$array["view"][$resultid][$key] = $val;
			}
		}
	}
	echo json_encode($array);
}


?>
