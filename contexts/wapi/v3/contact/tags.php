<?php
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
function _config_tags() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_tags($template, $args) {
	$world = $args['world'];
	$user = $args['user'];
	$results = $world->db->CacheExecute("SELECT DISTINCT lower(trim(unnest(string_to_array(tags, ',')))) AS tag FROM people WHERE tags IS NOT NULL AND tags != '' AND tags != ' '");
	$array = Array();
	foreach($results as $result) {
		if(strpos($result["tag"], $_REQUEST["term"]) === 0) $array[] = $result["tag"];
	}
	sort($array);
	echo json_encode($array);
}


?>
