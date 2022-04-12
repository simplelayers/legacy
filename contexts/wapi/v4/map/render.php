<?php
/**
  * Viewer: Take a list of layers, and render all of them, to make a "screenshot" of the current map view.
  * Render a "screenshot" of the current map view: bounding box, layers, opacities, etc.
  *
  * Parameters:
  *
  * project -- The unique ID# of the Project.
  *
  * bbox -- A bounding box, in comma-separated format, e.g. "12.34,56.7,89.0,123.4"
  *
  * width -- The width of the image to generate.
  *
  * height -- The height of the image to generate.
  * 
  * filename -- The filename that will be suggested for the downloaded image, minus the extension. The file extension will be added automatically.
  *
  * geotiff -- By default, the download is in JPEG format. However, if this parameter is set then the download will instead be a georeferenced TIFF (GeoTIFF) suitable for use with other GIS software.
  *
  * layers -- A comma-joined list of layer IDs. The layers are rendered in reverse order; the first layer listed is the topost layer, the last layer is the basemap.
  *
  * opacities -- A comma-joined list of opacities, corresponding to the list of layers.  Each opacity ranges from 0 to 1.
  *
  * labels -- A comma-joined list of 1/0 corresponding to the list of layers.  Each 1 or 0 indicates whether labels should be generated when the layer is rendered.
  *
  * An example of specifying a list of layers, opacities, and labels:
  * {@example docs/examples/viewerscreenshot.txt}
  * 
  * Return:
  *
  * A binary stream, being an image in JPEG or GeoTIFF format. If access is denied, a param string will be returned that says "&status=NO&"
  *
  * @package ViewerDispatchers
  */
/**
  * @ignore
  */
function _exec() {
//function _dispatch_rendermap($request,$world,$user,$template,$project,$embedded,$permission) {
	
	$world = System::Get();
	$wapi= System::GetWapi();
	
	$map = RequestUtil::Get('map');
	
	$info = $wapi->RequireProject($map);
	$project = $info['project'];
	
	$permission = $info['permission'];
	
	$user = $wapi->RequireToken();
	
	$suffix = 'png';
	
	$projector = new Projector_MapScript();
	$defaultProj4 = $world->projections->defaultProj4;
	
	$mapper = $world->getMapper();
		
	$projector = new Projector_MapScript();

	$projector->SetProjection( $defaultProj4);
	$projector->SetViewSize((int)RequestUtil::Get('width'),(int)RequestUtil::Get('height'));
	$projector->SetViewExtents(RequestUtil::Get('bbox'));
	
	$exts = $projector->GetROIExtents('string');
	// set some defaults, to get rid of spurious warnings in the error_log
	
	$mapper->init(true, $projector->mapObj);
	
	// go through each layer, load the ProjectLayer for it, and add it to the Map
	$request['layers']    = array_reverse( RequestUtil::GetList('layers',','));
	$request['opacities'] = array_reverse( RequestUtil::GetList('opacities',',') );
	$request['labels']  = array_reverse( RequestUtil::GetList('labels',',') );

	if( isset($request['bgcolor'])) $mapper->bgcolor = RequestUtil::Get('bgcolor');
	
	for ($i=0; $i<sizeof($request['layers']); $i++) {
		$projectlayer = $project->getLayerById($request['layers'][$i]);

	   if (!$projectlayer) continue;
	   //if($projectlayer->layer->type == LayerTypes::WMS) continue;
	   if($projectlayer->layer->type == LayerTypes::COLLECTION) {
	   		foreach( $project->getSubLayers($projectlayer->id) as $sub ) {
	   			$mapper->addLayer( $sub,$request['opacities'][$i],$request['labels'][$i]);
	   		}
	   } else {
	   	
	   	
	  	 $mapper->addLayer($projectlayer,(float)$request['opacities'][$i],(int)$request['labels'][$i]);
	   }
	   
	}
	
	//$mapper->geotiff isset($request['geotiff']);
	
	$ext = ".png";
	// all set!
	header('Content-type: image/png',true);
	//header("Content-Disposition: attachment; filename=\"{$project->name}$ext\"");
	$stream = $mapper->renderStream();
	$size = strlen($stream);
	header("Content-Length: $size", true );
	
	print $stream;
}

?>