<!--{if $user->diskUsageRemaining() <= 0 }-->
  <p class="alert">
  Your account is already at the maximum allowed storage.<br/>
  To import more data, either <a href=".?do=layer.list">delete some layers</a> or <a href=".?do=account.addstorage">purchase more storage space</a>
  </p>
<!--{else}-->


<form action="." method="post" enctype="multipart/form-data" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="import.kml2">
<font size="2">
<p>This utility allows you to upload a KML file, or a KMZ file containing one or more KML files. These files will be imported and can then be used in projects.</p>
<ul>
  <li>Only Folder and Placemark data can be imported, and only the Name and Description fields will be imported. Most KML features are not supported, e.g. View, Icon and Style, and NetworkLink.</li>
  <li>The names of the layers which will be created is based on the KML file's name and the name of the Folders within the KML. For example, if you upload <i>districts.kml</i> and it has folders named <i>northern</i> and <i>southern</i>, then the layers <i>districts northern</i> and <i>districts southern</i> will be created.</li>
  <li>The file you upload must end in .kmz or .kml</li>
  <li>The maximum size of the uploaded file is <!--{$maxfilesize}--> MB.</li>
</ul>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;KML or KMZ file:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="file" name="source" size="40" /></p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Submit" style="width:1.5in;" /></p>
</font>
</form>


<script type="text/javascript">
function check_form(formdata) {
/*	//commented b/c there is no name field
	dot = /\./;
   	kmlname = formdata.elements['name'].value;
    checkname = kmlname.search(dot);
	if(checkname>0){
	   alert('Layer name must not have periods.');
	   return false;
	}
*/
   if (!formdata.elements['source'].value) { 
       alert('Please select a file.');
   	   return false; 
   }
   
   if (!formdata.elements['source'].value.match(/([^\\:\/]+)\.kml$/i) && !formdata.elements['source'].value.match(/([^\\:\/]+)\.kmz$/i)) {
      alert('The upload must be a KML or KMZ file.'); return false; }
  
   alert('Files may take a while to upload. Please be patient.');
   return true;
}
</script>

<!--{/if}-->
