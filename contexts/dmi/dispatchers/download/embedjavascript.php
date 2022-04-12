<?php
ini_set('display_errors', 1);
/**
 * The HTML page for a utility to generate HTML iframe paragraphs.
 * @package Dispatchers
 */
/**
  */
function _config_embedjavascript() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["sendUser"] = false;
	$config["authUser"] = 0;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_embedjavascript() {
	header("Content-type: text/javascript");
}
function _dispatch_embedjavascript($template, $args) {
$world = System::Get();

//
//$project = $world->$_REQUEST['id'];
//$template->assign('swf',$project);
$template->assign('world',$world);

$params = "";
foreach( $_REQUEST as $key=>$val ) {
	if( $key == "do" ) { continue; }
	if( $key=="format" ) { continue; }
	if( $key=="asset") { continue; }
	if( $key=="PHPSESSION" ){continue; }
	if( $key=="width" ){continue; }
	if( $key=="height" ){continue; }
	if( substr($key,0,13)=="showInherited") {continue;}
	if( substr($key,0,2)=="__") {continue;}
	if( $key=="style"){continue;}
	$params .= "&".$key."=".$val;
	//echo( $key."=".$val."<br/>");
}
$template->assign('swf',"https://www.cartograph.com/v2.5/viewer?do=get&format=swf&asset=CGExperience.swf".$params);
//$template->assign('swf',"https://www.cartograph.com/~doug/intersect/url.swf");

$width = isset($_REQUEST['width']) ? $_REQUEST['width'] : "100%";
$height = isset($_REQUEST['height']) ? $_REQUEST['height'] : "100%";
$width = strpos($width,'%') ? $width : $width."px";
$height = strpos($height,'%') ? $height : $height."px";
$template->assign('height',$height);
$template->assign('width',$width);

$template->display('download/swfobject.tpl');
$template->display('download/embedjavascript.tpl');

} ?>