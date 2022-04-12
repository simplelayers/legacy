<?php 
function _config_searchfeatures() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_searchfeatures() {
	switch ($_REQUEST['format']) {
		case('csv'):
			header('Content-Encoding: UTF-8');
			header('Content-type: text/csv; charset=UTF-8');
			header('Content-Disposition: attachment; filename=SearchResults.csv.txt');
			echo "\xEF\xBB\xBF"; // UTF-8 BOM
			break;
		case('xls'):
			header('Content-Encoding: UTF-8');
			header('Content-type: application/vnd.ms-excel; charset=UTF-8');
			header('Content-Disposition: attachment; filename=SearchResults.xls');
			break;
	}
}
function _dispatch_searchfeatures($template, $args) {
	global $connection;
	$world = $args['world'];
	$user = $args['user'];
	if(isset($_REQUEST['token'])) {
		//$connection->LoginToken($_REQUEST['token']);
		$tokenValid =  $connection->userValid;// $world->auth->ValidateAppToken( $_REQUEST['token'] , session_id() );
		//$world->auth->ValidateToken($_REQUEST['token']);
		//$world->auth->ValidateUser();
		$user= $connection->user;
		if(!$tokenValid) {
			
			$template->assign('message',"invalid token");
			$template->assign('query',"");
			$template->display("wapi/error.tpl");
			die();
		}
 		//$user = $world->auth->user;
	} else {
		$user = $args['user'];
	}
	// Step 1: Verify user has read permission for specified project
	//error_log("user :". var_export($user,true));
	$permission = isset($permission) ? $permission : null;
	if (isset($permission) && ($permission < AccessLevels::READ) && (!isset($args['embedded']))) denied('You do not have permission to view this project.');

	// Step 2: Verify that the layer parameter has been set
	if(!isset( $_REQUEST["layer"]) ){ denied('layer not specified'); return; }
	$projectLayer = (isset( $_REQUEST["layer"]) ) ?  $_REQUEST["layer"] : false;
	
	if(!$projectLayer ) { denied('Invalid layer');return;}
	$projectLayers = array();
	if( stripos($projectLayer,",") ) {
		$projectLayers = explode(",",$projectLayer);
	} else {
		$projectLayers[] =$projectLayer;
	}
	// Step 4: Get parameters)
	$paging = new Paging();

	$getGeom = isset($_REQUEST['geom'])? true : false;
	$method = isset($_REQUEST['method'])? $_REQUEST['method'] : 'OR';
	$orderBy = isset($_REQUEST['orderby'])? $_REQUEST['orderby'] : 'gid';
	$idField = isset($_REQUEST['idfield'])? $_REQUEST['idfield'] : 'gid';
	
	$userId = is_null($user) ? 0 : $user->id;
	$project = $world->getProjectById($_REQUEST['project']);
	$canSearch = $project->getPermissionById($userId) >= AccessLevels::READ;
	// Step 5: Turn array of layer ids into an array of Layer objects.
	$layers = array();
	foreach($projectLayers as &$projectLayer) {
		$layerid = $projectLayer;
		$projectLayer = new ProjectLayer($world,$project,$projectLayer);
		
		
		if( !$projectLayer ) { denied('invalid layer:'.$layerid ); return;}		
		$layerType = $projectLayer->layer->type;
		if( $layerType != LayerTypes::VECTOR and $layerType != LayerTypes::RELATIONAL and $layerType != LayerTypes::ODBC) {denied('incorrect layer type:'.$layerid ); return;}
		if( !$canSearch ) {		
			if( !$projectLayer->searchable ) {denied('not authorized to view layer'); return; }
			else { $layers[] = $projectLayer->layer; }
		} else { $layers[] = $projectLayer->layer; }
	}

	$bbox = isset($_REQUEST['bbox'])? $_REQUEST['bbox'] : null;
	// Step 6: Either process criteria or do a general feature query on the layer
	if( isset($_REQUEST['criteria']) ) {
		$searchTerms = array();
		
		$criteria = explode(";",$_REQUEST['criteria']);	
		$i=0;
		foreach($criteria as &$criterion ) {
			$options = explode('|',$criterion);
			if(count($options)>1){
				list($criterion,$logic) = $options;
				if($i==0) {
					$logic = (substr($logic,0,1)=='!') ? 'not' : '';
				} 
			} else {
				$logic = '';
			}
			
			$items = explode(",",$criterion );
			$field = array_shift($items);
			$compare = array_shift($items);
			$value = implode(",",$items);
			
			$criterion = array($field,$compare,$value,$logic);
			$i+=1;
		}
		if(sizeof($layers) > 1 ) { $features = $world->unionFeatureSearch($layers,$criteria,$paging,$method,$orderBy,$idField); }
		elseif( isset($bbox) ) { $features = $layers[0]->searchFeaturesWithinBBox($bbox,$project->projection,$criteria, $paging, $getGeom,$method, $orderBy ); }
		else { $features = $layers[0]->searchFeatures($criteria, $paging, $getGeom,$method, $orderBy ); }
	} else { 
	 if(sizeof($layers) > 1 ) { $features = $world->unionFeatureSearch($layers,array(),$paging,$method,$orderBy,$idField); }
	 elseif( !is_null($bbox) ) { $features = $layers[0]->searchFeaturesWithinBBox($bbox,$project->projection,array(), $paging, $method, $getGeom,$orderBy ); }
	 else { $features = $layers[0]->getRecords($orderBy,$paging); }
	}
	
	//$paging->setResults($features);
	$attributeExclusions = array('the_geom','box_geom');
	if( !$getGeom ) array_push($attributeExclusions,'wkt_geom');
	
	$needLabel = true;
	if( sizeof($features) > 0 ) {
		$needLabel = (array_key_exists('label',$features[0]) == false );
	}	
	$delim = "\t";
	if (!isset($_REQUEST['format'])) $_REQUEST['format'] = 'xml';
	switch( $_REQUEST['format'] ){
		case 'xls':
			$delim="\t";
		case 'csv':
			
			$f = fopen("php://output",'w');
			$fields = array_keys($features[0]);
			foreach($fields as $i=>$val) {
				if(in_array($val,$attributeExclusions)) {
					unset($fields[$i]);
				}
			}
			fputcsv($f,$fields,$delim);
			
			foreach($features as $feature) {
				foreach($attributeExclusions as $exclusion) {
					unset($feature[$exclusion]);
				}
				fputcsv($f,$feature,$delim);
			}
			return;
			break;
	    case 'xml':
	        //$template->assign('sql', $sql );
	        $template->assign('fields', $fields );
	        $template->assign('results',$features);
	        $template->display('wapi/getfeatures.tpl');
	        break;
	    case 'php':
	        print serialize($features);
	        break;
	    default:
	         
	        return;
	}
	
	
	//$template->assign('needLabel',$needLabel);
	$template->assign('exclusions',$attributeExclusions);
	//$template->assign('project', $project);
	$template->assign('layerid', $projectLayer->layer->id );
	$template->assign('plid', $projectLayer->id );
	$template->assign('features',$features);
	$template->assign('paging',$paging);
	global $GEOMTYPES;
	$template->assign('layerType',$GEOMTYPES[$layerType]);
	// and now print the list of ProjectLayers
	$template->display('wapi/listfeatures.tpl');
} ?>
