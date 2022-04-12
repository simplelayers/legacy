<link rel="stylesheet" type="text/css" href="styles/style.css" />

<p class="title">
Editing color scheme for<br/>
<!--{$project->name|escape:'htmlall'}--><br/>
<!--{$layer->name|escape:'htmlall'}-->
</p>

<form action="." method="post" name="theform">
<input type="hidden" name="do" value="viewerprojectlayercolorschemesetsingle2"/>
<input type="hidden" name="layer" value="<!--{$layer->id}-->"/>
<input type="hidden" name="project" value="<!--{$project->id}-->"/>

<p>This tool will replace the current color and classification scheme with a single classification.</p>

<p>Use this color:<br/>
<!--{$colorpicker1}-->
</p>

<p><input type="submit" name="submit" value="set a single-classification scheme" style="width:3in;"/></p>
</form>
