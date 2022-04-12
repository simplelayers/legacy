<?php

use views\ContactViews;
use model\Permissions;

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
    if (!isset($_REQUEST['format']))
        $_REQUEST['format'] = 'json';
    switch ($_REQUEST['format']) {
        case "json":
            header('Content-Type: text/javascript; charset=UTF-8');
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
    $views = new ContactViews($user->id, $world->db);
    if (!$views)
        throw new Exception("No contact views found for specified user");
    $results = null;
    //echo urlencode(json_encode(Array(Array("name","contains","test"))));
    $filter = (isset($_REQUEST['filter']) ? json_decode(urldecode($_REQUEST['filter'])) : false);
    $and = ((isset($_REQUEST['and']) and $_REQUEST['and'] == "or") ? false : true);
    $session = SimpleSession::Get();
    $permissions = $session['permissions'];


    $isSysAdmin = Permissions::HasPerm($permissions, ':SysAdmin:General:', Permissions::VIEW);



    $permissionsFor = false;
    if (isset($_REQUEST['shareproject']))
        $permissionsFor = Array('shareproject', $_REQUEST['shareproject']);
    if (isset($_REQUEST['sharelayer']))
        $permissionsFor = Array('sharelayer', $_REQUEST['sharelayer']);
    switch (strtolower(trim($viewType))) {
        case "mine":
            $results = $views->GetMine2($permissionsFor, $filter, $and);            
            break;
        case "groups":
            $results = $views->GetGroups($filter, $and);
            break;
        case "group":
            $results = $views->GetGroup($_REQUEST['id'], isset($_REQUEST['me']), $permissionsFor, $filter, $and);
            
            break;
        case "everyoneelse":
            $results = $views->GetEveryoneElse($permissionsFor, $filter, $and, !$isSysAdmin);
            break;
        case "applicants":
            $results = $views->GetApplicants($_REQUEST['id'], isset($_REQUEST['me']), $permissionsFor, $filter, $and);
            break;
        case "denied":
            $results = $views->GetDenied($_REQUEST['id'], $permissionsFor, isset($_REQUEST['me']), $filter, $and);
            break;
        case "others":
            $results = $views->GetOthers($permissionsFor, $filter, $and);
            break;
        case "tag":
            $results = $views->GetTags($_REQUEST['tag'], $permissionsFor, $filter, $and);
            break;
    }
    
    if (!$results)
        throw new Exception("Invalid Parameters: Type; $viewType not recognized");
    if (isset($_REQUEST['group'])) {
        $group = new views\GroupViews($user->id, $world->db);
    } else {
        $group = null;
    }

    switch (strtolower($format)) {
        case "ajax":
        case "xml":
            ResultsToXML($results);
            break;
        case "json":
            ResultsToJSON($results, $group, (isset($_REQUEST['group']) ? $_REQUEST['group'] : null));
            break;
    }
}

function ResultsToXML($results) {
    echo '<?xml version="1.0" encoding="UTF-8" ?>';
    echo "<view>";
    foreach ($results as $result) {
        echo "\n\t<item";
        foreach ($result as $att => $val) {
            if (!$val)
                $val = "";

            $val = htmlentities($val);
            //echo("<br/>");
            echo " $att=\"$val\"";
        }
        echo ">\n\t</item>";
    }
    echo "</view>";
}

function ResultsToJSON($results, $view = null, $groupid = null) {
    $array["view"] = Array();
    if ($results->_numOfRows != 0) {
        foreach ($results as $resultid => $result) {
            foreach ($result as $key => $val) {
                $array["view"][$resultid][$key] = $val;
            }
            if ($view !== null)
                $array["view"][$resultid]["status"] = $view->GetStatus($groupid, $result['id']);
        }
    }
    echo json_encode($array);
}

?>
