<div class='maincontent'>

<!--{if $user->diskUsageRemaining() <= 0 }-->
  <p class="alert">
  Your account is already at the maximum allowed storage.<br/>
  To import more data, either <a href=".?do=layer.list">delete some layers</a> or <a href=".?do=account.addstorage">purchase more storage space</a>
  </p>
<!--{else}-->

<form action="." method="post" enctype="multipart/form-data" onSubmit="return check_form(this);">

<input type="hidden" name="do" value="layer.io">
<input type="hidden" name="mode" value="import">
<input type="hidden" name="format" value="shp">
<input type="hidden" name="stage" value="2">

<p>
<font size="2">
This utility allows you to upload a ZIP file containing one or more shapefiles to use in projects.</p>
<ul>
  <li>Enter a &quot;base name&quot; for all of the layers that will be created. The shapefile name will be appended to this to make the name of the layer. For example, if you enter <i>sanfrancisco</i>, then the shapefile <i>districts.shp</i> would become the layer <i>sanfrancisco districts.</i></li>
  <li>The suffixes on files must be lowercase, e.g. parcels.<i>shp</i> will work, but parcels.<i>Shp</i> will not work.</li>
  <li>The maximum size of the ZIP file is <!--{$maxfilesize}--> MB.</li>
  <li>You may upload a ZIP file which contains other ZIP files. These will each be processed as described above.</li>
  <li>You may upload a ZIP file, or enter a URL the zip file may be downloaded from.</li>
  <li>If a file is specified and a URL is provided the file will be used instead of the URL.</li>    
</ul>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ZIP File:<br/>&nbsp;&nbsp;&nbsp;&nbsp; <input type="file" id="source" name="source" size="40"  /></p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Layer URL:<br/>&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="fileURL" style="width:2.75in;" /></p>
<!--{if RequestUtil::Get('layerid', false)}-->
	<input type="hidden" name="layerid" value="<!--{RequestUtil::Get('layerid', false)}-->"/>
<!--{else}-->
	
	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Base Layer Name:<br/>&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="basename" style="width:2.75in;" /></p>
<!--{/if}-->
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Multiple layers with the same name are allowed. To overwrite a layer, select the option to "overwrite" from with the layer overview page</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Projection:<br/><span style="font-size:80%"><i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; This projection will be used for shapefiles that do not have a PRJ file.</span><br/></i>&nbsp;&nbsp;&nbsp;&nbsp; <!--{html_options options=$projectionlist name=projection }--></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Submit" style="width:1in;height:.3in;" /></p>
</font>
<br><br>
</form>

<script type="text/javascript">

	
function check_form(formdata) {

   dot=/[^ _A-Za-z0-9]/;
   basename = formdata.elements['basename'].value;
   checkname = basename.match(dot);
   if(checkname){
      alert('Base layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');  
      return false;
  }
   
/* 
   dot = /\./;
   if(checkname>0){
     alert('Layer name must not have periods.');
     return false;
   }
*/
   if (!formdata.elements['source'].value) { 
   	  alert('Please select a file.');
	  return false;   
   }
   
   if (!formdata.elements['source'].value.match(/([^\\:\/]+)\.zip$/i)) {
      alert('The upload must be a ZIP file.'); return false; }
   alert('Files may take a while to upload. Please be patient.');
   return true;
//}

</script>

<!--{/if}-->
</div>