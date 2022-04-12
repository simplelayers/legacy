<colorscheme pid="<!--{$project->id}-->" lid="<!--{$layer->id}-->" type="<!--{$projectlayer->colorschemetype}-->" fill="<!--{$projectlayer->colorschemefill|strippound}-->" stroke="<!--{$projectlayer->colorschemestroke|strippound}-->" size="<!--{$projectlayer->colorschemesymbolsize}-->" symbol="<!--{$projectlayer->colorschemesymbol}-->" column="<!--{$projectlayer->colorschemecolumn}-->">

<!--{if $showrules}-->
  <!--{section loop=$entries name=i}-->
  <!--{assign var=entry value=$entries[i]}-->
  <rule crid="<!--{$entry->id}-->" priority="<!--{$entry->priority}-->" fill="<!--{$entry->fill_color|strippound}-->" stroke="<!--{$entry->stroke_color|strippound}-->" field="<!--{$entry->criteria1|escape:'htmlall'}-->" operator="<!--{$entry->criteria2|escape:'htmlall'}-->" value="<!--{$entry->criteria3|escape:'htmlall'}-->" symbol="<!--{$entry->symbol|escape:'html'}-->" size="<!--{$entry->symbol_size|escape:'html'}-->" />
  <!--{/section}-->
<!--{/if}-->

</colorscheme>
