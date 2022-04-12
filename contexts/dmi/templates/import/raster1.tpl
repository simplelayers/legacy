<!--{$subnav}-->
<p class="title">Import a Raster/Image File</p>

<!--{if $user->diskUsageRemaining() <= 0 }-->
  <p class="alert wrapped">
  Your account is already at the maximum allowed storage.<br/>
  To import more data, either <a href=".?do=layer.list">delete some layers</a> or <a href=".?do=account.addstorage">purchase more storage space</a>
  </p>
<!--{else}-->


<form action="." method="post" enctype="multipart/form-data" onSubmit="return check_form(this);">

<input type="hidden" name="do" value="layer.io">
<input type="hidden" name="mode" value="import">
<input type="hidden" name="format" value="raster">
<input type="hidden" name="stage" value="2">

<font size="2">
<p >This utility allows you to upload a raster file and use it in projects.</p>
<ul class='wrapped'>
  <li>Select the image file and the world file to upload, and the name of the layer you would like to create. If your image is not in lat/lon projection, also specify which projection your image uses.</li>
  <li>Most georeferenced image formats are supported, including: GeoTIFF, TIFF, ECW, JPEG2000, GIF, and JPEG. Some image formats (e.g. GeoTIFF) do not require a world file.</li>
  <li>Remember that file transfers can take a very long time, from several minutes to a few hours, depending on the size of the file and the upload speed of your Internet connection.</li>
  <li>The maximum file size is <!--{$maxfilesize}--> MB. If you need to upload a larger image, contact us and we can make alternative arrangements for importing your data.</li>
</ul>


<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Image file:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="file" name="source" size="44" /></p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;World file:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="file" name="worldfile" size="44" /></p>

<!--{if RequestUtil::Get('layerid', false)}-->
	<input type="hidden" name="layerid" value="<!--{RequestUtil::Get('layerid', false)}-->"/>
<!--{else}-->
	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Layer name:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="name" maxlength="50" style="width:3in;" /></p>
<!--{/if}-->
<p class='wrapped' style='margin-left: 2em;'>Multiple layers with the same name are allowed. To overwrite a layer, select the option to "overwrite" from with the layer overview page</p>
<p style='margin-left: 2em;'>Projection:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<!--{html_options options=$projectionlist name=projection }--></p>
<p style='margin-left: 2em;'><input class='button' type="submit" name="submit" value="Submit" style="width:1.5in;" /></p>
</form>


<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['source'].value) { 
   	alert('Please select a file to upload.'); 
   	return false; 
   }

   if (!formdata.elements['name'].value) { 
   	alert('Please enter a name for the file.');
   	return false; 
   }
   
   	dot=/[^ _A-Za-z0-9]/;
	layername = formdata.elements['name'].value;
	checkname = layername.match(dot);
	if(checkname){
	  alert('Layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
	  return false;
	}

   if (!formdata.elements['worldfile'].value) {
    return confirm('You did not supply a world file.\nClick OK to continue without one.'); 
   }
   
   alert('Files may take a while to upload. Please be patient.');
   return true;
}
</script>

<!--{/if}-->