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
function _config_collections() {
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

function _headers_collections() {
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

function _dispatch_collections($template, $args) {
	if(!isset($_REQUEST['format'])) $_REQUEST['format'] = "json";
	if(!isset($_REQUEST['crud']) ) throw new Exception("Invalid CRUD operation: missing crud parameter");
	$world = System::Get();
	$user = SimpleSession::Get()->GetUser();
	
	switch($_REQUEST['crud']) {
		case "create":
		case "c":
			$layerName = $_REQUEST['name'];
			$isUnique = $world->db->Execute("select * from layers where name=? and owner=?", array($layerName,$user->id));
			if($isUnique->RecordCount() ) throw new Exception("Name already exists");
			$subs = $_REQUEST['layers'];
			$layer = $user->createLayer($layerName,LayerTypes::COLLECTION);
			$layer->fixDBPermissions();
			$layer->setDBOwnerToOwner();
			LayerCollection::SetSubs($world,$layer->id,$subs);
			if ($layer) {
             			if (isset($_REQUEST['description'])) {
                    	   		$layer->description = $_REQUEST['description'];
                		}
                		if (isset($_REQUEST['tags'])) {
                	    		$layer->tags = $_REQUEST['tags'];
                		}
            		}
			$_REQUEST['crud'] = 'r';
			$_REQUEST['layer'] =  $layer->id;
			$reportEntry = Report::MakeEntry( REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
			$report = new Report($args['world'],$reportEntry);
			$report->commit();
			return _dispatch_collections($template,$args);
			break;
		case "retrieve":
		case "r":
			$id = $_REQUEST['layer'];
			$layer = $world->getLayerById($id);
			
			
			$formatter = new LayerFormatter($world,$user);
			$permission = $layer->getPermissionById($user->id);

			if ($permission < AccessLevels::READ) throw new Exception("You lack sufficient permission.");
			switch($_REQUEST['format'] ) {
				case "json":
				case "ajax":
					$formatter->WriteJSON($layer);
					break;
				case "xml":
				default:
					$formatter->WriteXML($layer);
					break;
			}
			break;
		case "update":
		case "u":
			$id = $_REQUEST['layer'];
			$layer = $world->getLayerById($id);
			
			$permission = $layer->getPermissionById($user->id);
			if ($permission < AccessLevels::EDIT) throw new Exception("You lack sufficient permission.");
			
			$layer->fixDBPermissions();
			$layer->setDBOwnerToOwner();
			if ($layer) {
                                if (isset($_REQUEST['description'])) {
                                        $layer->description = $_REQUEST['description'];
                                }
                                if (isset($_REQUEST['tags'])) {
                                        $layer->tags = $_REQUEST['tags'];
                                }
                        }

			$subs = $_REQUEST['layers'];
			$subLayers = LayerCollection::SetOrder($world,$id,$subs);
			$layer->name = $user->uniqueLayerName($_REQUEST['name'],$layer);
			$_REQUEST['crud'] = 'r';
			return _dispatch_collections($template,$args);
			break;
		case "delete":
		case "d":
			$id = $_REQUEST['layer'];
			$layer = $world->getLayerById($id);
			
			$permission = $layer->getPermissionById($user->id);
			if ($permission < AccessLevels::EDIT) throw new Exception("You lack sufficient permission.");
			
			if( !$layer ) return SetStatus( "error","layer not found","layer $id could not be retrieved and may not exist");
			$layer->delete();
			SetStatus("ok","layer deleted");
			break;
	}
	
}

function SetStatus($status="ok",$display="",$verbose="") {

	if($status !== "ok") error_log(__FILE__.": $status - $display : $verbose");

	
	switch( $_REQUEST['format'] ) {
		case "json":
		case "ajax":
			echo '{"status":"'.$status.'","message":"'.$display.'"}';
			break;
		case "xml":
			echo "<result status='$status' message='$display'></result>";
			break;
		case "phpserial":
			echo serialize(array('status'=>$status,'message'=>$display));
			break;
		case "internal":
			return array('status'=>$status,'message'=>$display,'problem'=>$verbose);
			break;
	}

}





?>
