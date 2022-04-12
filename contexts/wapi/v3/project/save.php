<?php
ini_set ( 'display_errors', 1 );
error_reporting ( E_ALL );
function _config_save() {
	$config = array();
	return WAPI::DecorateConfig ($config );
}

function _headers_save() {
	//WAPI::SetWapiHeaders ( 'xml' );
}

function _dispatch_save($template, $args) {
	/* @var $world World */
	$world = $args ['world'];
	/* @var $wapi WAPI */
	$wapi = $world->wapi;
	
	
	/*$test = <<<XML
<projectView project="103" projection="ESPG:1234" bbox="10 10 200 200" windowsize="500,500" >
	<layerInfo  layers="0001,0002,0003,0004,0005" 
				    on="1,1,0,1,0"
				labels="0,0,1,0,1"
			opaciities="0,1,0,1,0" />
</projectView>
XML;
$request['XMLInput'] = new SimpleXMLElement($test);
*/
	$data = WAPI::GetInputXML();
	
	$metadata = $data->attributes ();
	
	$hasLayerInfo = sizeof ( $data->xpath ( 'layerInfo' ) ) > 0;
	
	$projectId = ( string ) $data ['project'];
	// load the project

	list ( $project, $permission ) = array_values($wapi->RequireProject( $projectId ));
	
	if ($permission < AccessLevels::EDIT)
		$wapi->HandleError( new Exception( DENIED_NEEDEDIT ) );

	if(isset($data->mapConfig)) {
	    $project->config = $data->mapConfig;
	}
	
	if ($hasLayerInfo) {
		$isProjectLayers = isset($data->layerInfo['players']);
		
		if ($isProjectLayers ) {
			$layers = explode ( ",", ( string ) $data->layerInfo ['players'] );
		} else {
			$layers = explode ( ",", ( string ) $data->layerInfo ['layers'] );
		}
		$on = explode ( ",", ( string ) $data->layerInfo ['on'] );
		$labels = explode ( ",", ( string ) $data->layerInfo ['labels'] );
		$opacities = explode ( ",", ( string ) $data->layerInfo ['opacities'] );
		
		for($i = 0; $i < sizeof ( $layers ); $i ++) {
			if ($isProjectLayers ) {
				$projectlayer = new ProjectLayer ( $world, $project, $layers [$i] );
			} else {
				$projectlayer = $project->getLayerById ( $layers [$i] );
			}
			if (! $projectlayer)
				continue;
		
			$projectlayer->z = - ($i + 1);
			$projectlayer->opacity = $opacities [$i];
			$projectlayer->labels_on = $labels [$i];
			$projectlayer->on_by_default = $on [$i];
		
		}
	
	}
	
	$bbox = ( string ) $metadata ['bbox'];
	$projection = ( string ) $metadata ['projection'];
	$windowsize = ( string ) $metadata ['windowsize'];
	
	// save the bbox and windowsize
	$project->bbox = $bbox;
	$project->windowsize = $windowsize;
	$project->projection = $projection;
	
	// hooray!
	$template->assign ( 'ok', "OK" );
	$message = isset($message) ? $message : 'Map Saved';
	$template->assign ( 'message', "$message" );
	$template->display ( 'wapi/okno.tpl' );
}
/**
Note: the comma separated values for the attributes for layerInfo are the same length.
<projectView project="..." projection="..." bbox="..." >
	<viewport width="..." height="..." />
	<layerInfo  layers="...,...,...,...,..." 
				    on="...,...,...,...,..."
				labels="...,...,...,...,..."
			opaciities="...,...,...,...,..." />
</projectView>

 */

?>
