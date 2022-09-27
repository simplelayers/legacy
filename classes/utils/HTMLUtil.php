<?php

namespace utils;

class HTMLUtil {
	public static function StartDoc() {
	  \WAPI::SetWapiHeaders('html');
		echo <<<DOC
<!DOCTYPE html>
<html>
DOC;
	}
	public static function WriteHead($title, $styles,$scripts=null) {
		$styleBlock = "";
		foreach ( $styles as $style ) {
			$styleBlock .= "<link rel='stylesheet' href='$style' />\n";
		}
		
		echo <<<HEAD
	   
		<title>$title</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		$styleBlock

HEAD;
	}
	
	public static function StartHead($scripts=null) {
	    $baseURL = BASEURL;
		echo <<<HEAD
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<meta name="apple-mobile-web-app-capable" content="yes">
HEAD;
		
if(!is_null($scripts) ) {
    foreach($scripts as $script) {
        echo '<script src="'.$script.'" type="text/javascript" charset="utf-8"></script>';
    }
}
echo <<<HEAD
<script>
		
/*document.addEventListener(
  'touchmove',
  function(e) {
    e.preventDefault();
  },
  false
);*/
</script>
HEAD;
	}
	
	public static function EndHead() {
		echo "</head>";
	}
	
	public static function WriteBody($dojoJS, $requiredJS,$pageArgs) {
	  
	    $session = \SimpleSession::Get();
	    //$pageArgs['permissions'] = $session['permissions'];
		$handlervars = implode(',',array_keys($requiredJS));		
		$requiredJS = '"' . implode ( '","', array_values($requiredJS) ) . '"';
		
		$pageArgs = json_encode($pageArgs);
		echo <<<DOC
		
<body class='tundra' id='sl_window' >

	<script src="$dojoJS/dojo/dojo.js" ></script>
	<script>
	
		function test() {
			// Require the module we just created
			require([$requiredJS],
				 function($handlervars){
				 	pages.MergePageData(this.GetPageArgs());
				 	var app =  new sl_app();
				 	try {
				 	app.placeAt(dom.byId('sl_window'));
				    app.startup();
				    
				 	  //map.innerHTML = "hello world";
					  //map.placeAt(sl_window);				
				 	}catch( e) {
				 		dom.byId('sl_window').innerHTML = e.trace;
				 	}	
				 
				}
			);
		}
		
		function GetPageArgs() {
		  
		      return $pageArgs;
		}
		test();		          
	     
	</script>
				 			
</body>

DOC;
	}
	public static function EndDoc() {
		echo "</html>";
	}
}