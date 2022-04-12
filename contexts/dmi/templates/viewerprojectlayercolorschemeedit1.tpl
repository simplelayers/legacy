<link rel="stylesheet" type="text/css" href="styles/style.css" />

<p class="title">
Editing color scheme for<br/>
<!--{$project->name|escape:'htmlall'}--><br/>
<!--{$layer->name|escape:'htmlall'}-->
</p>


<!-- the form has a name 'theform' which is used by the color_picker() function
     You can change the name, but also update the viewerprojectlayercolorschemeedit1 dispatcher to reflect it -->
<form action="." method="post" name="theform">
<input type="hidden" name="do" value="viewerprojectlayercolorschemeedit2"/>
<input type="hidden" name="layer" value="<!--{$layer->id}-->"/>
<input type="hidden" name="project" value="<!--{$project->id}-->"/>
<input type="hidden" name="cid" value="<!--{$entry->id}-->"/>


<p>Description:<br/>
<input type="text" name="description" style="width:4in;" maxlength="100" value="<!--{$entry->description|truncate:100|escape:'htmlall'}-->"/>
</p>
<p>Criteria:<br/>
<!--{html_options name=criteria1 values=$criteria1_list output=$criteria1_list selected=$entry->criteria1}-->
<!--{html_options name=criteria2 options=$criteria2_list selected=$entry->criteria2}-->
<input type="text" name="criteria3" value="<!--{$entry->criteria3|truncate:50|escape:'htmlall'}-->" style="width:2in;" maxlength="50"/>
</p>
<p>
Symbol:<br/>
<!--{html_options name=symbol options=$symbols selected=$entry->symbol}-->
<!--{html_options name=symbol_size options=$symbolsizes selected=$entry->symbol_size}-->
</p>
<p>
<!--{$colorpicker_fill}-->
<!--{$colorpicker_stroke}-->
</p>

<p><input type="submit" name="submit" value="save changes" style="width:2in;" /></p>
</form>
