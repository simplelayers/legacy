<link rel="stylesheet" type="text/css" href="styles/style.css" />

<p class="title">
Editing color scheme for<br/>
<!--{$project->name|escape:'htmlall'}--><br/>
<!--{$layer->name|escape:'htmlall'}-->
</p>

<ul>
  <li>The classifications each have criteria, such as &quot;population is greater than 1000000&quot; A classification can also have no criteria specified, in which case all features will match it.</li>
  <li>When the layer is rendered as part of a project, map features are compared against each classification in the order listed. <i>The color scheme used for a feature is the first classification whose criteria match the feature.</i></li>
  <li>It is suggested, though not required, that you have a class with no criteria as the very last classification. This will provide a default icon for any features which were not matched by the previous criteria.</li>
  <li>Any feature which matches no criteria at all (if you do not have a default set as described) will not be drawn in the map.</li>
  <li>At the bottom of this page are links which can help you to set up a new color scheme.</li>
</ul>

<!-- part 1: a simple link to add a new entry, and the table of existing entries -->
<p class="alert">
  <a href=".?do=viewerprojectlayercolorschemeadd&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->">add a new entry to this color scheme</a>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <a href=".?do=viewerprojectlayercolorschemesymbol1&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->">set the symbol for all classes</a>
</p>

<table class="bordered">
<tr>
  <th style="width:0.5in;">stroke</th>
  <!--{if !$nofill}-->
  <th style="width:0.5in;">fill</th>
  <!--{/if}-->
  <th style="width:1in;">symbol</th>
  <th style="width:3in;">description</th>
  <th style="width:3in;">criteria</th>
  <th style="width:0.25in;">move up</th>
  <th style="width:0.25in;">move down</th>
  <th style="width:0.25in;">edit</th>
  <th style="width:0.25in;">delete</th>
</tr>
<!--{section loop=$schemeentries name=i}-->
<!--{cycle values="color,altcolor" assign=class}-->
<!--{assign var=symbol value=$schemeentries[i]->symbol}-->
<!--{assign var=symbol value=$symbols[$symbol]}-->
<!--{assign var=symbolsize value=$schemeentries[i]->symbol_size}-->
<!--{assign var=symbolsize value=$symbolsizes[$symbolsize]}-->
<tr>
  <td class="<!--{$class}-->" style="background-color:<!--{$schemeentries[i]->stroke_color|escape:'html'}-->">&nbsp;</td>
  <!--{if !$nofill}-->
  <td class="<!--{$class}-->" style="background-color:<!--{$schemeentries[i]->fill_color|escape:'html'}-->">&nbsp;</td>
  <!--{/if}-->
  <td class="<!--{$class}-->"><!--{$symbolsize}--> <!--{$symbol|escape:'html'}--></td>
  <td class="<!--{$class}-->"><!--{$schemeentries[i]->description|truncate:30:'...'|escape:'html'}--></td>
  <td class="<!--{$class}-->"><!--{$schemeentries[i]->criteria1|escape:'html'}--> <!--{$schemeentries[i]->criteria2|escape:'html'}--> <!--{$schemeentries[i]->criteria3|truncate:30:'...'|escape:'html'}--></td>
  <td class="<!--{$class}-->"><!--{if !$smarty.section.i.first}--><a href=".?do=viewerprojectlayercolorschememoveup&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->&cid=<!--{$schemeentries[i]->id}-->">move up</a><!--{/if}--></td>
  <td class="<!--{$class}-->"><!--{if !$smarty.section.i.last}--><a href=".?do=viewerprojectlayercolorschememovedown&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->&cid=<!--{$schemeentries[i]->id}-->">move down</a><!--{/if}--></td>
  <td class="<!--{$class}-->"><a href=".?do=viewerprojectlayercolorschemeedit1&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->&cid=<!--{$schemeentries[i]->id}-->">edit</a></td>
  <td class="<!--{$class}-->"><a href=".?do=viewerprojectlayercolorschemedelete&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->&cid=<!--{$schemeentries[i]->id}-->" onClick="return confirm('Really delete this color scheme entry?');">delete</a></td>
</tr>
<!--{/section}-->
</table>

<!-- part 2: a small form for setting the labelitem -->
<p class="title">Labelling</p>
<form action="." method="post">
<input type="hidden" name="do" value="viewerprojectlayercolorschemesetlabelitem"/>
<input type="hidden" name="layer" value="<!--{$layer->id}-->"/>
<input type="hidden" name="project" value="<!--{$project->id}-->"/>
<p>
Which field should be used to label features? 
<!--{html_options name=labelitem options=$fields selected=$labelitem}--> 
</p>
<p>
Should labels be on or off when the project is initially loaded?
<!--{html_options name=labels_on options=$labelson_options selected=$projectlayer->labels_on}-->
</p>
<p> <input type="submit" value="save changes"/> </p>
</form>


<p><br/><br/></p>

<!-- part 3: links to alternate color schemes -->
<p class="title">Assign a calculated color scheme</p>
<p>The following wizards will help you set up a new color scheme based on the data in your layer.</p>
<ul>
  <li><a href=".?do=viewerprojectlayercolorschemesetsingle1&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->">set a single class for all features</a></li>
  <li><a href=".?do=viewerprojectlayercolorschemesetunique1&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->">set a unique-value scheme</a></li>
  <!--{if $numericfields}-->
  <li><a href=".?do=viewerprojectlayercolorschemesetquantile1&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->">set a quantile distribution scheme</a></li>
  <li><a href=".?do=viewerprojectlayercolorschemesetequalinterval1&layer=<!--{$layer->id}-->&project=<!--{$project->id}-->">set a equal-interval distribution scheme</a></li>
  <!--{/if}-->
</ul>

