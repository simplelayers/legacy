<form action="." method="post" onSubmit="return check_form(this);" name="editor">
<input type="hidden" name="do" value="layer.createrelational2"/>

<p class='intro'>
    A relational layer allows you to &quot;join&quot; two of your layers and render them as if they were one layer. This is commonly done if you have spatial data in one table and related supplemental data in a second table, and it is infeasible to integrate both of them into the same layer.
</p>
<p class='intro'>
    In order to form a relation, you must have 2 vector layers already uploaded.
 
    <ul class='intro'>
    <li>One of these will supply the geometry for the map (for example, property parcel outlines).</li>
    <li>The other supplies additional information to be related to the map features (for example, the appraisal price of the parcel).</li>
    <li>These 2 tables must have a field in common which uniquely identifies records as being the same record (for example, the AP#).</li>
    </ul>
</p>

<p class='instruction'>
Layer name:<br/><input type="text" name="name" style="width:2in;" maxlength="25" />
</p>

<p class="title">Spatial Layer</p>

<p class='instruction'>
    Which layer supplies the spatial component?<br/>
    <!--{html_options name=table1 options=$tables selected=$config.table1 onChange="updateColumns(1)" }--><br/>
    <br/>
    Which field contains the identifying relation (foreign key) common to the other layer?<br/>
    <!--{html_options name=column1 options=$columns1}--><br/>
</p>

<p class="title">Supplemental Layer</p>

<p class='instruction'>
    Which layer supplies the related, supplemental data?<br/>
    <!--{html_options name=table2 options=$tables2 selected=$config.table2 onChange="updateColumns(2)" }--><br/>
    <br/>
    Which field contains the identifying relation (foreign key) common to the other layer?<br/>
    <!--{html_options name=column2 options=$columns2}--><br/>
</p>

<p class="title">Relation Type</p>

<p class='instruction'>In what way do you want the data related?</p>

<input type="radio" name="relationtype" value="left" checked="checked"  /> <b>Show all spatial records, even those without supplemental data.</b> 
<div class='instruction'>All of the records in the spatial layer will be available; those which do not correspond to supplemental records will have NULL values for the supplemental fields. This is also called a LEFT OUTER JOIN and is the most commonly desired association.<br/></div>
<input type="radio" name="relationtype" value="inner" /> <b>Show only spatial records matched to a supplemental record.</b>
<div class='instruction'>The resulting layer will show only spatial records which have a corresponding entry in the supplemental data. This is also called an INNER JOIN.</div>
<input type="radio" name="relationtype" value="right" /> <b>Show all supplemental records, even those without spatial information.</b>
<div class='instruction'>The resulting layer will show all supplemental records, even those with no spatial information and which will not be drawn on a map. This is usually only desired for quality-checking or for analysis of which records lack a spatial relation. This is also called a RIGHT OUTER JOIN.</div>

<p><input type="submit" name="submit" value="create new layer"/></p>
</form>

<script type="text/javascript" src="media/prototype.js"></script>
<script type="text/javascript">
var columns = [];
columns[1] = <!--{$config.column1|json_encode}-->;
columns[2] = <!--{$config.column2|json_encode}-->;

function updateColumns(which) {
    var tablepicker     = document.forms['editor'].elements['table'+which];
    var columnpicker    = document.forms['editor'].elements['column'+which];
    var layerid         = tablepicker.options[tablepicker.selectedIndex].value;
    var column          = columns[which];

    var url = '.?do=wapi.listcolumns&id=' + layerid;
    
  	$.ajax( {
		url      : url,
		dataType : 'xml',
		type     :  'GET',
		success  : function(responseXML){ 
		    var columns = $('column',responseXML);
            columnpicker.options.length = 0;
            columnpicker.options[0] = new Option('','');
            for (var i=0; i<columns.length; i++) {
                var value = $(columns[i]).attr('name');
                var label = $(columns[i]).attr('name') + ' (' + $(columns[i]).attr('type') + ')';
                var selected = (value == column);
                columnpicker.options[columnpicker.options.length] = new Option(label,value,selected);
            }
		}
	});
}


//updateColumns(1);
//updateColumns(2);
</script>

<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['name'].value) { return false; }
   if (! formdata.elements['table1'].selectedIndex) return false;
   if (! formdata.elements['table2'].selectedIndex) return false;
   if (! formdata.elements['column1'].selectedIndex) return false;
   if (! formdata.elements['column2'].selectedIndex) return false;
   return true;
}
</script>
