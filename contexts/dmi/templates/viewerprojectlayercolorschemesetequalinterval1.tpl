<link rel="stylesheet" type="text/css" href="styles/style.css" />

<p class="title">
Editing color scheme for<br/>
<!--{$project->name|escape:'htmlall'}--><br/>
<!--{$layer->name|escape:'htmlall'}-->
</p>

<form action="." method="post" name="theform">
<input type="hidden" name="do" value="viewerprojectlayercolorschemesetequalinterval2"/>
<input type="hidden" name="layer" value="<!--{$layer->id}-->"/>
<input type="hidden" name="project" value="<!--{$project->id}-->"/>

<p>This tool will replace the current color and classification scheme with an equal-interval scheme. An equal-interval scheme classifies by a numeric field, dividing the full range of values into classes of equal width. This works well for data with a normal distribution.</p>

<p>
Create <!--{html_options name=howmany values=$howmany output=$howmany}--> classes, using the
<!--{html_options name=column values=$numericfields output=$numericfields}--> column.
</p>

<p>Have the colors range from this color:
<!--{$colorpicker1}-->
to this color:
<!--{$colorpicker2}-->
</p>


<p><input type="submit" name="submit" value="set an equal-interval scheme" style="width:3in;"/></p>
</form>
