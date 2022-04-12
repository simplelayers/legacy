<?php
require_once (dirname ( __FILE__ ) . "/../../includes/main.inc.php");
$dojo = BASEURL . 'lib/js/dojo.1.9.1/';
$themeCSS = 'lib/js/dojo.1.9.1/dijit/themes/tundra/tundra.css';
$DialogCSS =  'lib/js/dojo.1.9.1/dijit/themes/tundra/Dialog.css';
$clientUI = 'client_ui/components/';
$baseURL = BASEURL;

$ini = System::GetIni ();
$user = SimpleSession::Get()->GetUserInfo ();
$userInfo = json_encode($user,JSON_FORCE_OBJECT);

echo <<<DOC
	<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="{$baseURL}/client_ui/components/url_params/css/url_params.css" />
	<link rel="stylesheet" href="{$baseURL}/client_ui/components/wms_preview/css/wms_preview.css" />
	<link rel="stylesheet" href="{$baseURL}/styles/buttons.css" />
	<link rel="stylesheet" href="{$baseURL}/styles/weblay.css" />
	
	<link rel="stylesheet" href="{$baseURL}{$themeCSS}" />
	<link rel="stylesheet" href="{$baseURL}{$DialogCSS}" />
	
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
 			theme:'tundra',
 				
			
		};
	</script>
</head>
<body class='tundra' id='wrapper'>

	

	<script src="$dojo/dojo/dojo.js" data-dojo-config="async:1"></script>
	<script>
		function test() {
		
			// Require the module we just created
			require(["dojo/dom",
					"dojo/dom-construct",
					"dojo/parser",
				 	"dojo/topic",
					'sl_components/listings/problems/widget',
				 	"sl_modules/Style",
					"sl_modules/WAPI" 
					], 
				 function(dom,domConstruct,parser,dojoTopic,sl_problem_list,slStyle,sl_wapi){
					var user = {$userInfo};
				 	try{
				 	var widget = new sl_problem_list();
					widget.placeAt(dom.byId('wrapper'));
					widget.startup();
					} catch(error) {
				 		console.log(error);		
					}
				});		
		}
		test()
							
	</script>
</body>
</html>

DOC;
?>