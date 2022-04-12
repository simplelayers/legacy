<?php
require_once (dirname ( __FILE__ ) . "/../../includes/main.inc.php");
$dojo = BASEURL . 'lib/js/dojo.1.9.1/';
$themeCSS = 'lib/js/dojo.1.9.1/dijit/themes/tundra/tundra.css';
$clientUI = 'client_ui/components/';
$baseURL = BASEURL;


$ini = System::GetIni ();
echo <<<DOC
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="{$baseURL}/client_ui/components/url_params/css/url_params.css" />
	<link rel="stylesheet" href="{$baseURL}/client_ui/components/wms_preview/css/wms_preview.css" /> 
	<script>
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
 				{ name: "sl_components", location: "client_ui/components/" },
 				{ name: "sl_modules", location: "client_ui/modules/" } 	
 			],
 				
			
		};
	</script>
</head>
<body id='wrapper'>
	<div id="target"></div>
	<canvas id="myCanvas" width="250" height="300" style="border:1px solid #d3d3d3;">
Your browser does not support the HTML5 canvas tag.</canvas>
	<textarea id='urlDemo' style="width:100%"></textarea>
	<!-- load dojo and provide config via data attribute -->
		<script src="$dojo/dojo/dojo.js" data-dojo-config="async:1"></script>
	<script>
		function test() {
		
			// Require the module we just created
			require(["dojo/dom",
					"dojo/dom-construct",
					"dojo/parser",
				 	"dojo/topic",
				 	"sl_components/wms_preview/widget",
				 	"sl_modules/Style",
					], 
				 function(dom,domConstruct,parser,dojoTopic,wms_preview,slStyle){
				 	var c= dom.byId('myCanvas');
				 	var ctx = c.getContext('2d');
				 	var img = new Image();
				 	domConstruct.empty(dom.byId('target'));
				 	var testURL = 'http://198.89.106.156/cgi-bin/mapserv?map=/mnt/wms/map.map&Layers=main&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&SRS=EPSG%3A4326&BBOX=-121.010151631017,34.63111,-118.557267349139,40.2595773262106&STYLES=&FORMAT=image/png&WIDTH=360&HEIGHT=288&TRANSPARENT=true';
					
					//slStyle.addStyleSheet("{$baseURL}/client_ui/components/url_params/css/url_params.css");
					slStyle.addStyleSheet("{$baseURL}/styles/weblay.css");
					slStyle.addStyleSheet("{$baseURL}/styles/buttons.css");
					//slStyle.addStyleSheet("{$baseURL}/client_ui/components/wms_preview/css/wms_preview.css");
					var widget = new wms_preview({url:testURL});
					
					widget.placeAt(dom.byId('target'));
					widget.startup();	
					dojoTopic.subscribe('url_params/state/changed',  function(data) {
							
						dom.byId('urlDemo').innerHTML = data.url;	
					},this);
					// Use our module to change the text in the greeting
				}	
				);
		}
		test()
							
	</script>
	
</body>
</html>

DOC;
?>