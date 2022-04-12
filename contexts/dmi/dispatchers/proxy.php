<?php
error_reporting ( E_ALL );
ini_set ( 'show_errors', 1 );
/**
 * * A HTTP proxy, for AJAX calls to remote servers. Particularly useful for probing remote WMS servers for layer lists.
 * * @package Dispatchers
 * */
//require_once (dirname ( __FILE__ ) . '/../classes/WMSUtil.php');
/**
 * 
 * */
function _config_proxy() {
	$config = Array ();
	$config ["header"] = false;
	$config ["footer"] = false;
	$config ["customHeaders"] = true;
	// Start config
	$config ["sendUser"] = false;
	$config ["authUser"] = 0;
	$config ["sendWorld"] = false;
	// Stop config
	return $config;

}

function _headers_proxy() {
	if($_REQUEST['service']=='capabilities') header('Content-Type: text/xml');
}


	$wms = new WMSUtil( $_REQUEST ['url'] );
	$capabilities = $wms->LoadCapabilities ();

function writeLayer($layer, $indent = "") {
	
	$title = $layer->Title;
	$name = $layer->Name;
	
	$bbox = $layer->bbox;
	
	
	$has_name= !is_null($name) ? "<input name='vis_layers[]' value='$name' type='checkbox'></input>" : "";
	foreach(explode("\t",$indent) as $td) {
		#echo "<span>&nbsp;</span>";
	}
	echo "<li>$has_name $title</li>";
	#echo "$indent$title\t$name\t$has_name\n";
	
	$layers = $layer->__get ( WMSLayer::LAYER_LAYER );
	if($layers) {
			echo "<ul>";
		foreach ( $layers as $subLayer ) {
	
			writeLayer ( $subLayer, $indent."\t" );
		}
		echo "</ul>";
	}
	
}

function _dispatch_proxy($template, $args) {
	global $wms,$capabilities;
	/* @var $wms WMS_URL */
        
	$_REQUEST ['service'] = isset ( $_REQUEST ['service'] ) ? $_REQUEST ['service'] : 'capabilities';
	
	switch ($_REQUEST ['service']) {
		case 'capabilities' :
			$wms->GetCapabilities ();
			return;
		case 'layers' :
			#$capabilities = $wms->LoadCapabilities ();
			/* @var $layer WMSLayer */
			$layer = $capabilities->getCapability ( WMSCapabilities::CAPABILITY_LAYER );
			$layers = $layer->__get ( WMSLayer::LAYER_LAYER );
			echo "<ul>";
			writeLayer ( $layer );
			echo "</ul>";

			break;
	
	}
	return;
	
	/* @var $capabilities WMSCapabilities */
	/*$capabilities  = ($wms->LoadCapabilities());
	
	
	$request = $capabilities->getCapability(WMSCapabilities::CAPABILITY_REQUEST);
	
	die();
	*/
	#$url = pruneWMSurl($_REQUEST['url']);
	#die();
	
	
        
	$url = $url . '?SERVICE=WMS&REQUEST=GetCapabilities';
	//$digiglobe = preg_match('/services\.digitalglobe\.com/',$url);
	//following works in WMS class import function but not in this dispatcher script?
	//$digiglobe=stripos($url,'/services\.digitalglobe\.com/');
	if (preg_match ( '/services\.digitalglobe\.com/i', $url )) {
		$url = $url . '&CONNECTID=58a1d1e1-7089-4238-acc3-1276ef9020b5&USERNAME=cloud&PASSWORD=DGcs253787';
	}
	/*	$capabilities = file_get_contents($url);
	if(($attempts < 1 ) && ($capabilities=="")) $capabilities = _dispatch_proxy($template,$args,$attempts+1);
echo $capabilities;*/
	readfile ( $url );
}

?>