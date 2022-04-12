
<div style="float:right;">
  <img class="thumbnail" id="thumbnail" src=".?do=download.imagethumbnail&id=<!--{$layer->id}-->" onClick="document.getElementById('thumbnail').src += '&force=true'" />
  <br/>
  <!--{$layer->getExtentPretty()}-->
</div>

<form name="odbcform" action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="layer.editodbc2"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>

<p>
Name:<br/><input type="text" name="name" style="width:3in;" value="<!--{$layer->name}-->"/><br/>
Server type:<br/><!--{html_options name=servertype values=$odbcserveroptions output=$odbcserveroptions selected=$odbcinfo->driver}--><br/>
Hostname:<br/><input type="text" name="odbchost" maxlength="50" style="width:3in;" value="<!--{$odbcinfo->odbchost}-->"/><br/>
TCP Port number:<br/><input type="text" name="odbcport" maxlength="5" style="width:3in;" value="<!--{$odbcinfo->odbcport}-->"/><br/>
Username:<br/><input type="text" name="odbcuser" maxlength="50" style="width:3in;" value="<!--{$odbcinfo->odbcuser}-->"/><br/>
Password:<br/><input type="text" name="odbcpass" maxlength="50" style="width:3in;" value="<!--{$odbcinfo->odbcpass}-->"/><br/>
Database:<br/><input type="text" name="odbcbase" maxlength="50" style="width:3in;" value="<!--{$odbcinfo->odbcbase}-->"/><br/>
Schema &amp; Table:<br/><input type="text" name="table" maxlength="50" style="width:3in;" value="<!--{$odbcinfo->table}-->"/><br/>
Longitude column:<br/><input type="text" name="loncolumn" maxlength="50" style="width:3in;" value="<!--{$odbcinfo->loncolumn}-->"/><br/>
Latitude column:<br/><input type="text" name="latcolumn" maxlength="50" style="width:3in;" value="<!--{$odbcinfo->latcolumn}-->"/><br/>
</p>
<!--{if $isowner}-->
<p>
			<select name="reimportOption" id="reimportOption">
				<option value="" selected>Overwrite Layer</option>
				<option value="shp">Shapefile</option>
				<option value="raster">Raster</option>
				<option value="wms">WMS</option>
				<option value="gps">GPS</option>
			</select>
			<script>
				$('#reimportOption').change(function(){
					var option = $('#reimportOption').val();
					$('#reimportOption option').removeAttr('selected');
					$('#reimportOption option').first().attr('selected','selected');
					if(option) window.location.href = "./?do=layer.io&mode=import&stage=1&format="+option+"&layerid=<!--{$layer->id}-->";
				});
			</script>
		</p><!--{/if}-->
<p>
Description:<br/>
<textarea name="description" style="width:8in;height:1in;"><!--{$layer->description}--></textarea>
</p>
<p>
Tags:<br/>
<textarea name="tags" style="width:8in;height:1in;"><!--{$layer->tags}--></textarea>
</p>

<p><input type="submit" name="submit" value="save changes" style="width:2in;"/></p>
</form>

<script type="text/javascript">
function check_form(form) {
    var error = null;
    if (!form.elements['name'].value)      error = "The layer's name is required.";
 	dot=/[^ _A-Za-z0-9]/;
    basename = formdata.elements['basename'].value;
	checkname = basename.match(dot);
	if(checkname){
	    alert('Base layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');  
	    return false;
	}
 	if (!form.elements['odbchost'].value)  error = "The remote database server's hostname is required.";
    if (!form.elements['odbcport'].value)  error = "The remote database server's TCP port number is required.";
    if (!form.elements['odbcuser'].value)  error = "A username is required.";
    if (!form.elements['odbcpass'].value)  error = "A password is required.";
    if (!form.elements['odbcbase'].value)  error = "The database name is required.";
    if (!form.elements['table'].value)     error = "The name of the database table is required.";
    if (!form.elements['loncolumn'].value) error = "Please specify which numeric column contains longitude values.";
    if (!form.elements['latcolumn'].value) error = "Please specify which numeric column contains latitude values.";
    if (error) { alert(error); return false; }
    return true;
}

function updateTcpPort() {
    var driver = document.forms['odbcform'].elements['servertype']; driver = driver.options[driver.selectedIndex].value;
    var port = ODBCSERVERPORTS[driver];
    document.forms['odbcform'].elements['odbcport'].value = port;
}

var ODBCSERVERPORTS = [];
<!--{foreach from=$odbcserverports key=driver item=portnumber}-->
ODBCSERVERPORTS['<!--{$driver}-->'] = '<!--{$portnumber}-->';
<!--{/foreach}-->
$(function(){
	$('textarea[name="tags"]').tagsInput({
		width: '6in'
	});
});
</script>



<!-- part 2: a form for giving this layer away to someone else -->
<!--{if $canChangeOwner}-->
<p><br/><br/><br/><br/></p>
<p class="title">Change Owner</p>
<p>This tool allows you to transfer the layer to another user's ownership. The layer will disappear from your list and will appear on theirs. When ownership is transferred, the layer will no longer be included into any projects, and all access controls (sharing) will be reset to defaults.</p>
<form action="." method="post" onSubmit="return confirm('Are you sure you want to transfer away ownership of this layer?\nOnce you give it away, you cannot regain control of it unless the recipient gives it back.\n\nClick OK to give away this layer.\nClick Cancel to NOT give away this layer.');">
<input type="hidden" name="do" value="layer.giveaway"/>
<input type="hidden" name="layerid" value="<!--{$layer->id}-->"/>
Transfer ownership to: <!--{html_options name=recipientid options=$friends}--> <input type="submit" name="submit" value="transfer ownership" style="width:2in;"/>
</form>
<!--{/if}-->

<script type="text/javascript">
function reminder() {
   alert('It will take a moment to prepare your download.\nPlease be patient.');
}
</script>
