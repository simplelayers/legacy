<form name="odbcform" action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="import.odbc2">
<font size="2">
<p>This utility allows you to enter connection parameters for a remote database which is compatible with ODBC. Instead of &quot;importing&quot; records by copying them to SimpleLayers, the data will be read over the Internet from your own database.</p>

<ul>
    <li>Only POINT layers are supported, not polygons nor lines.</li>
    <li>You must specify which fields provide the longitude and latitude data. These should be numeric fields. Coordinates must be in WGS84.</li>
    <li>The table must have a field named <i>id</i> containing a unique ID for each record. If you intend to edit records, this should be a auto-incrementing primary key.</li>
    <li>There must already be some data in the table, in order to verify the connection.</li>
    <li>We support the following servers: <!--{$odbcservernames}--></li>
</ul>

<p>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Layer name:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="name" maxlength="50" style="width:3in;" /><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Server type:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<!--{html_options name=servertype values=$odbcserveroptions output=$odbcserveroptions onChange="updateTcpPort();"}--><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hostname:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="odbchost" maxlength="50" style="width:3in;" /><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TCP Port number:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="odbcport" maxlength="5" style="width:3in;" /><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Username:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="odbcuser" maxlength="50" style="width:3in;" /><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Password:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="odbcpass" maxlength="50" style="width:3in;" /><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Database:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="odbcbase" maxlength="50" style="width:3in;" /><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Schema &amp; Table:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="table" maxlength="50" style="width:3in;" /><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Longitude column:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="loncolumn" maxlength="50" style="width:3in;" /><br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Latitude column:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="latcolumn" maxlength="50" style="width:3in;" /><br/>
</p>

<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Submit" style="width:1.5in;" /></p>
</font>
</form>

<script type="text/javascript">
function check_form(form) {
    
	/*
	dot=/[^ _A-Za-z0-9]/;
	layername = form.elements['name'].value;
	checkname = layername.match(dot);
	if(checkname){
		alert('Layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
		return false;
	}*/

	var error = null;
    if (!form.elements['name'].value)      error = "The layer's name is required.";
    if (!form.elements['odbchost'].value)  error = "The remote database server's hostname is required.";
    if (!form.elements['odbcport'].value)  error = "The remote database server's TCP port number is required.";
    if (!form.elements['odbcuser'].value)  error = "A username is required.";
    if (!form.elements['odbcpass'].value)  error = "A password is required.";
    if (!form.elements['odbcbase'].value)  error = "The database name is required.";
    if (!form.elements['table'].value)     error = "The name of the database table is required.";
    if (!form.elements['loncolumn'].value) error = "Please specify which numeric column contains longitude values.";
    if (!form.elements['latcolumn'].value) error = "Please specify which numeric column contains latitude values.";
 	dot=/[^ _A-Za-z0-9]/;
    layername = form.elements['name'].value;
	checkname = layername.match(dot);
	if(checkname) error = "Layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.";

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
updateTcpPort();
</script>
