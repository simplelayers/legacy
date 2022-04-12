<!--{$subnav}-->
<font size="2">
<!--{if $user->diskUsageRemaining() <= 0 }-->
  <p class="alert">
  Your account is already at the maximum allowed storage.<br/>
  To import more data, either <a href=".?do=layer.list">delete some layers</a> or <a href=".?do=account.addstorage">purchase more storage space</a>
  </p>
<!--{else}-->

<form action="." method="post" enctype="multipart/form-data" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="layer.io">
<input type="hidden" name="stage" value="2">
<input type="hidden" name="format" value="gps">
<input type="hidden" name="mode" value="import">


This utility allows you to upload GPS data and use it in projects.

<ul>
  <li>Select the GPS file to upload and the name of the layer you'd like to create from it.</li>
  <li>Select which features to import (waypoint, track, route), and the file format of your GPS file.</li>
  <li>Data must be in Lat/Long with WGS84 or NAD83 datum for it to line up with the other data in the system.</li>
</ul>


<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GPS file:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="file" name="source" size="44" /></p>
<!--{if RequestUtil::Get('layerid', false)}-->
	<input type="hidden" name="layerid" value="<!--{RequestUtil::Get('layerid', false)}-->"/>
<!--{else}-->
	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Layer name:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="name" maxlength="50" style="width:3in;" /></p>
<!--{/if}-->
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;File Format:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="gpsformat" style="width:3in;">
  <option></option>
  <option value="gpx">.gpx (GPS Exchange Format)</option>
  <option value="mps">.mps (Garmin Mapsource)</option>
</select>
</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Type:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="type" style="width:3in;">
  <option></option>
  <option value="waypoint">waypoints</option>
  <option value="track">tracks</option>
  <option value="route">routes</option>
</select>
</p>
</font>
<p>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Submit" style="width:1.5in;" /></p>
<br>
<br>
<br>
<br>

</form>


<script type="text/javascript">
function check_form(formdata) {

   dot=/[^ _A-Za-z0-9]/;
   layername = formdata.elements['name'].value;
   checkname = layername.match(dot);
   if(checkname){
    alert('Layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
    return false;
   }

   if (!formdata.elements['source'].value) {
    alert('Please select a file.');
    return false; 
   }
   
   if (!formdata.elements['name'].value) { 
    alert('Please enter a name for the file.');
    return false; 
   }
   
   if (!formdata.elements['gpsformat'].selectedIndex) { 
    alert('Please select a format.'); 
	return false; 
   }
  
   if (!formdata.elements['type'].selectedIndex) { 
    alert('Please select a type.');
	return false; 
   }
   
   alert('Files may take a while to upload. Please be patient.');
   return true;
}
</script>

<!--{/if}-->
