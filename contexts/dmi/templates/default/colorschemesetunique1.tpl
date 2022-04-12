<a href=".?do=default.colorscheme&id=<!--{$layer->id}-->">cancel editing</a>

<form action="." method="post">
<input type="hidden" name="do" value="default.colorschemesetunique2"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>

<p>This tool will replace the current color and classification scheme with an unique-value scheme. A unique-value scheme finds all unique values in the selected field, then assigns a classification for each value.</p>

<p>Use which field for classification?<br/>
<!--{html_options name=column values=$fields output=$fields}-->
</p>

<p>
Use which palette?<br/>
<p><img id='paletteImg' class='colorbrewer_palette'></img></p>
<!-- <input type="hidden" name="schemetype" value=""/> -->
<input type="hidden" name="schemenumber" value=""/>
<input type="hidden" name="schemename" value=""/>
<!-- <select size="7" style="width:1.5in;" name="schemetypeselector"   onClick="set_type();"></select> -->
<select size="7" style="width:1.5in;" name="schemenumberselector" onClick="set_number();"></select>
<select size="7" style="width:1.5in;" name="schemenameselector"   onClick="set_name();"></select>
</p>

<p><input type="submit" name="submit" value="set a unique-value scheme" style="width:3in;"/></p>
</form>


<script type="text/javascript">
// shortcuts to the selection widgets
var colorschemes = eval(<!--{$colorschemes}-->);
//var type_selector = document.forms[0].elements['schemetypeselector'];
var numb_selector = document.forms[0].elements['schemenumberselector'];
var name_selector = document.forms[0].elements['schemenameselector'];
//var scheme_type   = document.forms[0].elements['schemetype'];
var scheme_number = document.forms[0].elements['schemenumber'];
var scheme_name   = document.forms[0].elements['schemename'];
// now populate the "type" field, which is a fixed list
// disabled, as this is unique-only
//for (var type in colorschemes) type_selector[type_selector.length] = new Option(type,type);
set_type();
scheme_number.value = numb_selector.options[0].value;
numb_selector.hidden=true;
numb_selector.selectedIndex = 0;
set_number();
   document.images['paletteImg'].src = '';
// and now the event handlers for when those selectors are clicked
function set_type() {
/*  disabled, as this is unique-only
   // store their selection, for later retrieval into the form
   scheme_type.value = type_selector.options[type_selector.selectedIndex].value;
   // purge the number and name boxes
   numb_selector.length = 0;
   name_selector.length = 0;
   // then populate the number box
      document.images['paletteImg'].src = '';
   var numbers = colorschemes[scheme_type.value];
*/
   var numbers = colorschemes['unique'];
   for (var num in numbers) numb_selector[numb_selector.length] = new Option(num,num);
}
function set_number() {
if(numb_selector.selectedIndex < 0 ) return;
   // store their selection, for later retrieval into the form
   scheme_number.value = numb_selector.options[numb_selector.selectedIndex].value;
   // purge the name box, then populate it with the new set
   name_selector.length = 0;
   var names = colorschemes['unique'][scheme_number.value];
   document.images['paletteImg'].src = '';
   for (var name in names)  name_selector[name_selector.length] = new Option(name,name);
}
function set_name() {
   // store their selection, for later retrieval into the form
     scheme_name.value = name_selector.options[name_selector.selectedIndex].value;
 document.images['paletteImg'].src = 'media/images/colorbrewer/'+scheme_name.value+'_'+scheme_number.value+'_unique.png';
    
 
}
</script>
