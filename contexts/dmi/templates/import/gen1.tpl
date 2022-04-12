<font size="2">
<!--{if $user->diskUsageRemaining() <= 0 }-->
  <p class="alert">
  Your account is already at the maximum allowed storage.<br/>
  To import more data, either <a href=".?do=layer.list">delete some layers</a> or <a href=".?do=account.addstorage">purchase more storage space</a>
  </p>
<!--{else}-->

<form action="." method="post" enctype="multipart/form-data" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="import.gen2">

<p>This utility allows you to upload a file containing GEN data for use in projects.</p>

<ul>
  <li>Select the GEN file to upload and the name of the layer you'd like to create.</li>
  <li>Data must be in Lat/Long with WGS84 or NAD83 datum for it to line up with the other data in the system.</li>
</ul>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GEN file:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="file" name="source" size="44" /></p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Layer name:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="name" maxlength="50" style="width:3in;" /></p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Type:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="type" style="width:3in;">
  <option></option>
  <option value="points">points</option>
  <option value="lines">lines</option>
  <option value="polygons">polygons</option>
</select>
</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Submit" style="width:1.5in;" /></p>
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
  
  	if (!formdata.elements['type'].selectedIndex) { 
    	alert('Please select a type.');
    	return false; 
   	}
   
   	alert('Files may take a while to upload. Please be patient.');
   	return true;
}
</script>

<!--{/if}-->
