<!--{$subnav}-->
<!--{if $user->diskUsageRemaining() <= 0 }-->
  <p class="alert">
  Your account is already at the maximum allowed storage.<br/>
  To import more data, either <a href=".?do=layer.list">delete some layers</a> or <a href=".?do=account.addstorage">purchase more storage space</a>
  </p>
<!--{else}-->
<font size="2">
<form action="." method="post" enctype="multipart/form-data" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="layer.io">
<input type="hidden" name="mode" value="import">
<input type="hidden" name="format" value="csv">
<input type="hidden" name="stage" value="2">
<p>This utility allows you to upload a file containing delimited point data, and will import that data for use in projects. You may also upload a ZIP file containing one or multiple delimited-text files. You may find this a very useful alternative to making many uploads, especially with very large files.</p>

<ul>
  <li>If a ZIP file is uploaded, all files in the ZIP will be processed, regardless of their extension.</li>
  <li>Acceptable delimiters are tab and comma. The &quot; text delimiter is optional. The delimiters will be automatically detected.</li>
  <li>The first row must contain the names of the columns.</li>
  <li>The columns <i>latitude</i> and <i>longitude</i>, if they exist, will be used to form the coordinates to place the points in space. If the <i>latitude</i> and <i>longitude</i> do not both exist, the points will all be placed at 0,0 latitude and longitude.</li>
  <li>Latitude and longitude must be in one of the supported projections listed below.</li>
</ul>


<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Delimited text file or ZIP file:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="file" name="source" size="44" /></p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Layer URL:<br/>&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="fileURL" style="width:2.75in;" /></p>

<!--{if RequestUtil::Get('layerid', false)}-->
	<input type="hidden" name="layerid" value="<!--{RequestUtil::Get('layerid', false)}-->"/>
<!--{else}-->
	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Base layer name:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="name" maxlength="50" style="width:3in;" /><br/>
<!--{/if}-->
<span class="small">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The <i>Base layer name</i> will be prepended to the name of each delimited file,<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
e.g. if you enter <i>san francisco</i> then the upload file <i>parcels.csv</i> would become the layer <i>san francisco parcels</i></span>
</p>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Projection:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<!--{html_options options=$projectionlist name=projection }--></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Submit" style="width:1.5in;" /></p>
<p>*To create a delimited text file from Microsoft Excel:</p>
<ul>
  <li>Create your spreadsheet, following the column guidelines above.</li>
  <li>Go to File and select Save As.</li>
  <li>Select the File Type as &quot;Text (tab delimited) (*.txt)&quot;</li>
  <li>Enter the filename and click Save.</li>
</ul>
</font>
</form>
<script type="text/javascript">
function check_form(formdata) {

    dot=/[^ _A-Za-z0-9]/;
    basename = formdata.elements['name'].value;
    checkname = basename.match(dot);
    if(checkname){
     alert('Base layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
     return false;
    }
							 
   
   if (!formdata.elements['source'].value) {
   if(!formdata.elements['fileURL'].value) {
  		alert('Please select a file or provide a URL.'); 
   		return false; 
   	}
   }
   //if (!formdata.elements['name'].value) { return false; }
   alert('Files may take a while to upload. Please be patient.');
   return true;
}
</script>

<!--{/if}-->
