<link rel="stylesheet" type="text/css" href="styles/style.css" />

<p class="title">
Editing color scheme for<br/>
<!--{$project->name|escape:'htmlall'}--><br/>
<!--{$layer->name|escape:'htmlall'}-->
</p>

<form action="." method="post">
<input type="hidden" name="do" value="viewerprojectlayercolorschemesetunique2"/>
<input type="hidden" name="layer" value="<!--{$layer->id}-->"/>
<input type="hidden" name="project" value="<!--{$project->id}-->"/>

<p>This tool will replace the current color and classification scheme with an unique-value scheme. A unique-value scheme finds all unique values in the selected field, then assigns a classification for each value. Note: There is a maximum of <!--{$smarty.const.COLORCLASSES_MAX}--> classifications; if the selected field has more unique values than that, only <!--{$smarty.const.COLORCLASSES_MAX}--> classifications will be created.</p>

<p>Use which field for unique value classifications?<br/>
<!--{html_options name=column values=$fields output=$fields}-->
</p>


<p><input type="submit" name="submit" value="set a unique-value scheme" style="width:3in;"/></p>
</form>
