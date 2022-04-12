<?xml version="1.0" encoding="UTF-8" ?>
<colorscheme layer="<!--{$layer->id}-->" type="<!--{$layer->colorschemetype}-->" fill="<!--{$fill|strippound}-->" stroke="<!--{$stroke|strippound}-->" size="<!--{$size}-->" symbol="<!--{$symbol}-->" column="<!--{$column}-->" label_style="<!--{$layer->label_style_string|escape:'htmlall'}-->" key="value">
<!--{if $showrules}-->
  <!--{section loop=$entries name=i}-->
  <!--{assign var=entry value=$entries[i]}-->
  <rule crid="<!--{$entry->id}-->" description="<!--{$entry->description|escape:'htmlall'}-->" priority="<!--{$entry->priority}-->" fill="<!--{$entry->fill_color|strippound}-->" stroke="<!--{$entry->stroke_color|strippound}-->" field="<!--{$entry->criteria1|escape:'htmlall'}-->" operator="<!--{$entry->criteria2|escape:'htmlall'}-->" value="<!--{$entry->criteria3|escape:'htmlall'}-->" symbol="<!--{$entry->symbol|escape:'html'}-->" size="<!--{$entry->symbol_size|escape:'html'}-->" label_style="<!--{$entry->label_style_string|escape:'html'}-->" />
  <!--{/section}-->
<!--{/if}-->
</colorscheme>
