<?php 


?>
<html>
<body>
<form>
<textarea name='textarea' rows="10" >
</textarea>
<br>
<textarea name='textarea2' rows="10" style="width:500px">
</textarea>

</form>
<script>
   
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(showPosition);
    } else {
    	window.document.forms[0].textarea.value = "Geolocation is not supported by this browser.";
    }
    
 function showPosition(position) {
	  	window.document.forms[0].textarea.value = "";
	 for (key in position['coords']) {
		 
	     	window.document.forms[0].textarea.value+= "\n"+key+'='+position['coords'][key];
	 	}		
  
 }
 if (window.DeviceOrientationEvent) {
	  // Listen for the event and handle DeviceOrientationEvent object
	  window.addEventListener('deviceorientation', devOrientHandler, false);
	}

 function devOrientHandler(eventData)  {
	   window.document.forms[0].textarea2.value = "";
	   for( key in eventData ) {
		   window.document.forms[0].textarea2.value += "\n"+key+"="+eventData[key];
	   }
  }

 
</script>

</body>
</html>
<?php 
return;

if (ob_get_level() == 0) ob_start();




for ($i = 0; $i<10; $i++){

        echo "<br> Line to show.";
        echo str_pad('',4096)."\n";    

        ob_flush();
        flush();
        sleep(2);
}

echo "Done.";

ob_end_flush();
?>