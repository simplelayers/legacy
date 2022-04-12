<link rel="stylesheet" type="text/css" href="styles/style.css" />

<p class="title">
Editing color scheme for<br/>
<!--{$project->name|escape:'htmlall'}--><br/>
<!--{$layer->name|escape:'htmlall'}-->
</p>

<form action="." method="post" name="theform">
<input type="hidden" name="do" value="viewerprojectlayercolorschemesetquantile2"/>
<input type="hidden" name="layer" value="<!--{$layer->id}-->"/>
<input type="hidden" name="project" value="<!--{$project->id}-->"/>

<p>This tool will replace the current color and classification scheme with a quantile distribution scheme. A quantile scheme classifies by a numeric field by placing an equal number of features in each class. This works best for data that is skewed to one end of the scale or is not normally distributed.</p>

<p>
Create <!--{html_options name=howmany values=$howmany output=$howmany}--> classes, using the 
<!--{html_options name=column values=$numericfields output=$numericfields}--> column.
</p>

<p>Have the colors range from this color:
<!--{$colorpicker1}-->
to this color:
<!--{$colorpicker2}-->
</p>

<p><input type="submit" name="submit" value="set a quantile distribution scheme" style="width:3in;"/></p>
</form>
