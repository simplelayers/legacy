<?php

require_once( dirname(__FILE__).'/main.inc.php');
$ini=\System::GetIni();
$dojoVersion = $ini->dojo_version;

$asSrc = isset($_REQUEST['asSrc']);

$baseURL =  isset($baseURL) ? $baseURL : "https://".$_SERVER['SERVER_NAME'].dirname(dirname($_SERVER['REQUEST_URI']));
$clientUI  = isset($clientUI) ? $clientUI : $baseURL.'/client_ui/';
$dojo  = isset($dojo) ? $dojo : $baseURL.'/lib/js/'.$dojoVersion;
$componentsURL  = isset($componentsURL) ? $componentsURL :$clientUI.'components';
$modulesURL  = isset($modulesURL) ? $modulesURL : $clientUI.'modules';
$slAppURL  = isset($slAppURL) ? $slAppURL : $clientUI.'apps';
$libURL = $baseURL.'/lib';
$jsLibURL = $libURL.'/js';
if($asSrc) header('Content-Type: text/javascript');
if(!$asSrc) echo "<script>";
echo <<<DOJO
// Instead of using data-dojo-config, we are creating a dojoConfig object
// *before* we load dojo.js; they are functionally identical.
var dojoConfig = {
	async: true,
	baseUrl: "$baseURL",
	// This code registers the correct location of the "demo" package
	// so we can load Dojo from the CDN whilst still being able to
	// load local modules
	modulePaths: "$clientUI",
	packages: [
	{ name: "dojo", location: "$dojo/dojo" },
	{ name: "dijit", location: "$dojo/dijit" },
	{ name: "dojox", location: "$dojo/dojox" },
	{ name: "sl_app", location:"$slAppURL" }, 
	{ name: "jslib", location:"$jsLibURL"},
    { name: "sl_pages", location: "$baseURL/client_ui/pages" },
	{ name: "gridx", location: "$jsLibURL/gridx-1.3.8"},
	{ name: "sl_components", location: "$componentsURL" },
	{ name: "sl_modules", location: "$modulesURL" },
	{ name: "sl_modules_open", location: "$baseURL/open/modules" },
	{ name: "sl_components_open", location: "$baseURL/open/components" },
	{ name: "bootstrap", location: "$libURL/dojo_packages/xsokev-Dojo-Bootstrap-37e0a4d" },	
	]
	 
};
   /*
   { name: "put-selector", location: "$dojo/dojo-packages/put-selector" },
   { name: "xstyle", location: "$dojo/dojo-packages/xstyle" },
	{ name: "dgrid", location: "$dojo/dojo-packages/dgrid" }
	*/
DOJO;
if(!$asSrc) echo "</script>";



?>