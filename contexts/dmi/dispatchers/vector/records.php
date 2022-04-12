<?php
use utils\PageUtil;
use model\SL_Query;
use utils\ParamUtil;
use model\Permissions;

/**
 * List the records in the specified vector layer; with links to sort, update, delete, etc.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_records()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_records($template, $args, $org, $pageArgs)
{
    $world = System::Get();
    $user = SimpleSession::Get()->GetUser();
    
    if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:Records:', Permissions::VIEW)) {
        print javascriptalert('Your  do not have permission to view layer records.');
        return print redirect('layer.list');
    }
    
    // load the layer and verify their access
    $layer = Layer::GetLayer($_REQUEST['id']);
    
    if (! $layer or $layer->getPermissionById($user->id) < AccessLevels::READ) {
        print javascriptalert('You do not have permission to view that layer.');
        return print redirect('layer.list');
    }
    $template->assign('layer', $layer);
    
    // the template needs this, so it knows whether to draw additional editing elements, e.g. checkboxes and delete widgets
    if ($layer->getHasRecords()) {
        $perm = $layer->getPermissionById($user->id);
        $editaccess = $perm >= AccessLevels::EDIT;
        if(!$layer->getHasEditableRecords()) $editaccess = false;
        
        $viewaccess = ($perm >= AccessLevels::READ);
    /*} elseif($layer->type == LayerTypes::RELATIONAL) {
        $editaccess = $viewaccess = AccessLevels::NONE;    
        print javascriptalert('This function is not appropriate for this layer type.');
        return print redirect('layer.list');
    */
    } else {
        print javascriptalert('This function is not appropriate for this layer type.');
        return print redirect('layer.list');
    }
    
    if (! Permissions::HasPerm($pageArgs['permissions'], 'Layers:Records:', Permissions::EDIT)) {
        $editaccess = AccessLevels::NONE;
    }
    if (! Permissions::HasPerm($pageArgs['permissions'], 'Layers:Records:', Permissions::VIEW)) {
        $viewaccess = AccessLevels::NONE;
    }
    
    
    $template->assign('editaccess', $editaccess);
    $template->assign('viewaccess', $viewaccess);
    
    // how many filters do we allow/display in the HTML?
    $HOWMANYFILTERS = 20;
    $FILTERNUMBERS = range(0, $HOWMANYFILTERS - 1);
    $template->assign('howmanyfilters', $HOWMANYFILTERS);
    $template->assign('filternumbers', $FILTERNUMBERS);
    
    // the options for AND/OR selection
    if (! isset($_REQUEST['andor']))
        $_REQUEST['andor'] = 'AND';
    $andor_options = array(
        'AND' => 'match all of the above criteria',
        'OR' => 'match any of the above criteria'
    );
    $template->assign('andor_options', $andor_options);
    $template->assign('andor', $_REQUEST['andor']);
    
    // save the sort and limit to their session, then set defaults if it's not set, and read it back
    $session = SimpleSession::Get();
    
    $layers = isset($session['layersorts']) ? $session['layersorts'] : array();
    $session['vectorRecordsPerPage'] = array();
    
    $sid = "layer_" . $layer->id;
    $sort = ParamUtil::Get($_REQUEST, 'sort'); // RequestUtil::GetAndSave('sort','gid');
    if ($sort) {
        if (substr($sort, 0, 1) == '<')
            $sort = 'gid';
    } else {
        $sort = 'gid';
    }
    $desc = ParamUtil::Get($_REQUEST, 'desc'); // (int)(bool) RequestUtil::GetAndSave('desc',true, $sid);
    
    $limit = ParamUtil::Get($_REQUEST, 'limit', 50);
    if ($limit == 0)
        $limit = 50;
    $offset = ParamUtil::Get($_REQUEST, 'offset', 0); // (int)RequestUtil::Get('offset',0);
    
    $layers[$layer->id] = array(
        'sort' => $sort,
        'desc' => $desc,
        'limit' => $limit,
        'offset' => $offset
    );
    $session['layersorts'] = $layers;
    RequestUtil::Set('sort', $sort);
    // RequestUtil::Set('desc',$desc);
    RequestUtil::Set('limit', $limit);
    RequestUtil::Set('offset', $offset);
    
    if (substr($sort, 0, 1) == '<')
        $sort = 'gid';
    
    $template->assign('sort', $sort);
    $template->assign('desc', $desc);
    $template->assign('limit', $limit);
    $template->assign('offset', $offset);
    
    $template->assign('offset_next', $offset + $limit);
    $template->assign('offset_prev', $offset - $limit);
    
    // if they specified filter/sort, store it in their session. Note the excessive and ugly
    // use of the $layer->id, to cause these preferences to be for this specific layer
    $critera1prefilled = array();
    $critera2prefilled = array();
    $critera3prefilled = array();
    
    // $filters =isset($session['filters']) ? $session['filters'] : array();

    $filters = array();
    foreach ($FILTERNUMBERS as $id) {
        if (RequestUtil::Get("filter{$id}_criteria1", false) !== false || ! isset($filters[$layer->id][$id])) {
            
            $criteria = array();
            $criteria["criteria1"] = RequestUtil::Get("filter{$id}_criteria1", '');
            $criteria["criteria2"] = RequestUtil::Get("filter{$id}_criteria2", '');
            $criteria["criteria3"] = RequestUtil::Get("filter{$id}_criteria3", '');
        } else {
            // $criteria = $filters[$layer->id][$id];
        }
        
        RequestUtil::Set("filter{$id}_criteria1", $criteria['criteria1']);
        RequestUtil::Set("filter{$id}_criteria2", $criteria['criteria2']);
        RequestUtil::Set("filter{$id}_criteria3", $criteria['criteria3']);
        
        $filters[$layer->id][$id] = $criteria;
        
        $session['filters'] = $filters;
        $critera1prefilled[$id] = $criteria['criteria1']; // $_REQUEST["filter{$id}_criteria1"] = $_SESSION["filter{$id}_criteria1_{$layer->id}"];
        $critera2prefilled[$id] = $criteria['criteria2']; // $_REQUEST["filter{$id}_criteria2"] = $_SESSION["filter{$id}_criteria2_{$layer->id}"];
        $critera3prefilled[$id] = $criteria['criteria3']; // $_REQUEST["filter{$id}_criteria3"] = $_SESSION["filter{$id}_criteria3_{$layer->id}"];
        
        /*
         * if (isset($_REQUEST["filter{$id}_criteria1"])) $_SESSION["filter{$id}_criteria1_{$layer->id}"] = $_REQUEST["filter{$id}_criteria1"];
         * if (isset($_REQUEST["filter{$id}_criteria2"])) $_SESSION["filter{$id}_criteria2_{$layer->id}"] = $_REQUEST["filter{$id}_criteria2"];
         * if (isset($_REQUEST["filter{$id}_criteria3"])) $_SESSION["filter{$id}_criteria3_{$layer->id}"] = $_REQUEST["filter{$id}_criteria3"];
         * if (!isset($_SESSION["filter{$id}_criteria1_{$layer->id}"])) $_SESSION["filter{$id}_criteria1_{$layer->id}"] = '';
         * if (!isset($_SESSION["filter{$id}_criteria2_{$layer->id}"])) $_SESSION["filter{$id}_criteria2_{$layer->id}"] = '';
         * if (!isset($_SESSION["filter{$id}_criteria3_{$layer->id}"])) $_SESSION["filter{$id}_criteria3_{$layer->id}"] = '';
         * $critera1prefilled[$id] = $_REQUEST["filter{$id}_criteria1"] = $_SESSION["filter{$id}_criteria1_{$layer->id}"];
         * $critera2prefilled[$id] = $_REQUEST["filter{$id}_criteria2"] = $_SESSION["filter{$id}_criteria2_{$layer->id}"];
         * $critera3prefilled[$id] = $_REQUEST["filter{$id}_criteria3"] = $_SESSION["filter{$id}_criteria3_{$layer->id}"];
         */
    }
   
    $template->assign('sort', $sort);
    $template->assign('criteria1', $critera1prefilled);
    $template->assign('criteria2', $critera2prefilled);
    $template->assign('criteria3', $critera3prefilled);
    /* @var $layer \Layer */
    // the list of attributes in the table
    $attribs = $layer->getAttributes();
    
    unset($attribs['gid']);
    unset($attribs['id']);
    if ($layer->area !== false) {
        $attribs["squaremiles"] = DataTypes::FLOAT;
        $attribs["squarekilometers"] = DataTypes::FLOAT;
    }
    
    $template->assign('attribs', $attribs);
    
    
    // the options for filtering
    global $COMPARISONS;
    
    $template->assign('criteria2_list', Comparisons::$comparisons);
    $criteria1_list = array_keys($attribs);
    array_unshift($criteria1_list, '');
    $template->assign('criteria1_list', $criteria1_list);
    
    // how many records to display on a page? here are their choices
    $template->assign('perpagechoices', array(
        5,
        25,
        50,
        100,
        200,
        500,
        750,
        1000
    ));
    
    // if this is a ODBC layer, prep the ODBC connection
    if ($layer->type == LayerTypes::ODBC) {
        $odbcinfo = $layer->url;
        
        if ($_REQUEST['sort'] == 'gid')
            $_REQUEST['sort'] = $_SESSION["sort_{$layer->id}"] = 'id';
        
        switch ($odbcinfo->driver) {
            case ODBCUtil::MYSQL:
                $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                break;
            case ODBCUtil::PGSQL:
                $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                break;
            case ODBCUtil::MSSQL:
                list ($odbc, $odbcini, $freetdsconf) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                break;
        }
    }
    
    //
    // wow, all of that was just setting up the environment! now time for some real work
    // this necessarily involves writing some raw SQL, since we want to fetch subsets and all
    //
    
    $sort = (! ($sort == "") && ! is_null($sort)) ? $sort : 'gid';
    
    // set up the limits and offsets and generate the list of criteria
    $sortby = preg_replace('/\W/', '', $sort);
    
    $limit = (int) $_REQUEST['limit'];
    $offset = (int) $_REQUEST['offset'];
    
    $desc = $desc ? SL_Query::ORDER_DESC : SL_Query::ORDER_ASC;
    $andor = $_REQUEST['andor'] == 'OR' ? 'OR' : 'AND';
    $criteria = array();
    
    foreach ($FILTERNUMBERS as $id) {
        if ($layer->type == LayerTypes::ODBC)
            $criterion = $world->criteria_to_sql($_REQUEST["filter{$id}_criteria1"], $_REQUEST["filter{$id}_criteria2"], $_REQUEST["filter{$id}_criteria3"], $odbcinfo->driver);
        else
            $criterion = $world->criteria_to_sql($_REQUEST["filter{$id}_criteria1"], $_REQUEST["filter{$id}_criteria2"], $_REQUEST["filter{$id}_criteria3"]);
        array_push($criteria, $criterion);
    }
    $criteria = array_filter($criteria, create_function('$a', 'return $a!="true";'));
    $criteria = implode(" $andor ", $criteria);
    if ($criteria)
        $criteria = " WHERE $criteria";
        //$world->db->debug =true;
        // query 1: a count of how many records matched the criteria (the if stuff handles a bad query, e.g. bad data types)
    if ($layer->type == LayerTypes::ODBC)
        $howmany = $db->Execute("SELECT count(*) AS count FROM {$odbcinfo->table} $criteria");
    else
        //$world->db->debug=true;
        $howmany = $world->db->Execute("SELECT count(*) AS count FROM {$layer->url} $criteria");
    $howmany = $howmany ? $howmany->fields['count'] : 0;
   
    $template->assign('matchingrecords', $howmany);
    
    $area = "";
    if($layer->type != LayerTypes::RELATIONAL) {
        if (isset($attribs['the_geom'])) {
            
            if ($layer->area !== false)
                $area = ", ST_Area(ST_Transform(the_geom,900913))*0.000000386102 AS squaremiles, ST_Area(ST_Transform(the_geom,900913))*0.000001 AS squarekilometers";
        }
    }
    // query 2: the actual fetch, with limit and offset and all (the if stuff handles a bad query, e.g. bad data types)
    if ($layer->type == LayerTypes::ODBC)
        $records = $db->Execute("SELECT *, id AS gid FROM {$odbcinfo->table} $criteria ORDER BY {$sortby} $desc LIMIT {$limit} OFFSET {$offset}");
    
    else {
        
        $orderby = "ORDER BY ($sortby)";
        $records = $world->db->Execute("SELECT *{$area} FROM {$layer->url} $criteria  $orderby $desc LIMIT {$limit} OFFSET {$offset}");
    }
    
    $records = $records ? $records->getRows() : array();
    
    $template->assign('records', $records);
    
    // reset the next/prev offsets, if we're already at the start/end of the dataset
    if ($_REQUEST['offset'] <= 0)
        $template->assign('offset_prev', '');
    if ($_REQUEST['offset'] + $_REQUEST['limit'] >= $howmany)
        $template->assign('offset_next', '');
    if ($_REQUEST['limit'] == 0)
        $_REQUEST['limit'] = 5;
        
        // set the "page number XX of YY" values
    $template->assign('pagenumber', 1 + (int) ($_REQUEST['offset'] / $_REQUEST['limit']));
    $template->assign('totalpages', 1 + (int) ($howmany / $_REQUEST['limit']));
    
    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['pageTitle'] = 'Data - Editing records for ' . $layer->name;
    $pageArgs['layerId'] = $layer->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    $pageArgs = PageUtil::MixinLayerArgs($template);
    $template->assign('isLayerEditor', $pageArgs['isLayerEditor'] == 'true');
    
    // finally! the template
    $template->display('vector/records.tpl');
}
?>