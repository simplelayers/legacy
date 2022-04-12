<?
error_reporting(E_ALL);
function _config_metadata() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	$config['sendWorld'] = true;
	// Stop config
	return $config;
}

function _headers_metadata() {
	if(!isset($_REQUEST['format'] )) $_REQUEST['format'] = 'json';
	switch($_REQUEST['format'] ) {
		case "json":
		case "ajax":
			header('Content-Type: application/json');
			break;
		case "xml":
			header('Content-Type: text/xml');
			break;
		case "phpserial":
			header('Content-Type: text/plain');
			break;
	}
}

function _dispatch_metadata($template, $args) {
	if(!isset($_REQUEST['format'])) $_REQUEST['format'] = "json";
	if(!isset($_REQUEST['crud']) ) throw new Exception("Invalid CRUD operation: missing crud parameter");
	$world = $args['world'];
	$user = $args['user'];
	
	$levels = array_reverse(explode(',',$_REQUEST['position']));
	$type = (isset($_REQUEST['type']) ? $_REQUEST['type'] : 'key');
	$id = $_REQUEST['id'];
	$layer = $world->getLayerById($id);
	$permission = $layer->getPermissionById($user->id);
	if ($permission < AccessLevels::EDIT){die("No permissions.");}
	array_remove($levels, "", true);
	switch($_REQUEST['crud']) {
		case "create":
		case "c";
			$layer->metadata = createMetadata($layer->metadata, $levels, $type, $_REQUEST['new']);
			return false;
			break;
		case "update":
		case "u":
			$layer->metadata = updateMetadata($layer->metadata, $levels, $type, $_REQUEST['new']);
			return false;
			break;
		case "delete":
		case "d":
			$layer->metadata = deleteMetadata($layer->metadata, $levels, $type);
			return false;
			break;
	}
}
function createMetadata($array, $levels, $type, $new){
	$editArray = &$array;
	$unset = count($levels)-1;
	$key = $levels[$unset];
	unset($levels[$unset]);
	foreach($levels as $value)
		$editArray = &$editArray[$value];
	if($type == 'key'){
		$editArray[$key][$new] = null;
	}else{
		$editArray[$key] = Array();
		$editArray[$key][$new] = null;
	}
	return $array;
}
function updateMetadata($array, $levels, $type, $new){
	$editArray = &$array;
	$unset = count($levels)-1;
	$key = $levels[$unset];
	unset($levels[$unset]);
	foreach($levels as $value)
		$editArray = &$editArray[$value];
	if($type == 'key'){
		$editArray[$new] = $editArray[$key];
		unset($editArray[$key]);
	}else{
		$editArray[$key] = $new;
	}
	return $array;
}
function deleteMetadata(&$array, $levels,  $type){
	$editArray = &$array;
	$unset = count($levels)-1;
	$key = $levels[$unset];
	unset($levels[$unset]);
	foreach($levels as $value)
		$editArray = &$editArray[$value];
	print_r($editArray);
	if($type == 'key'){
		unset($editArray[$key]);
		if(empty($editArray)){
			$editArray = null;
		}
	}else{
		$editArray[$key] = null;
	}
	return $array;
}
function array_remove(&$array, $search, $strict = false) { 
    $keys = array_keys($array, $search, $strict);
    if(!$keys)
        return false;
    foreach($keys as $key)
        unset($array[$key]);
    return count($keys);
}

?>