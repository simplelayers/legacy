<link rel="stylesheet" type="text/css" href="styles/style.css" />

<p class="title">
Editing color scheme for<br/>
<!--{$project->name|escape:'htmlall'}--><br/>
<!--{$layer->name|escape:'htmlall'}-->
</p>

<form action="." method="post">
<input type="hidden" name="do" value="viewerprojectlayercolorschemesymbol2"/>
<input type="hidden" name="layer" value="<!--{$layer->id}-->"/>
<input type="hidden" name="project" value="<!--{$project->id}-->"/>

<p>This utility will set the symbol for all of the existing classifications to whatever you select.</p>

<p>
Set all classes to this symbol: 
<!--{html_options name=symbol options=$symbols}-->
<!--{html_options name=symbolsize options=$symbolsizes selected=$smarty.const.SYMBOLSIZE_MEDIUM}-->
<br/>
</p>


<p><input type="submit" name="submit" value="set symbol for all classes" style="width:3in;"/></p>
</form>
