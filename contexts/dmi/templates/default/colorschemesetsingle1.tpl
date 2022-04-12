<!--{$subnav}-->
<p class="title">Default color scheme for layer: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href=".?do=default.colorscheme&id=<!--{$layer->id}-->">cancel editing</a><br/>
<!--{$layer->name|escape:'htmlall'}--> (<!--{$layer->geomtypestring}-->)<br/>
Setting a single-value scheme
</p>
<form action="." method="post" name="theform">
<input type="hidden" name="do" value="default.colorschemesetsingle2"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>

<p>This tool will replace the current color and classification scheme with a single classification.</p>

<p>Use this color:<br/>
<!--{$colorpicker1}-->
</p>

<p><input type="submit" name="submit" value="set a single-classification scheme" style="width:3in;"/></p>
</form>
