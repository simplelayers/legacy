<?xml version="1.0" encoding="UTF-8" ?>
<colorscheme pid="<!--{$project->id}-->" lid="<!--{$layer->id}-->" type="<!--{$colorscheme}-->" fill="<!--{$fill_color}-->" stroke="<!--{$stroke_color}-->" size="<!--{$smarty.request.symbol_size}-->" symbol="<!--{$smarty.request.symbol}-->">

<!--{section name=i loop=$ruleset}-->
<!--{assign var=rule value=$ruleset[i]}-->
<rule priority="<!--{math equation="x+1" x=$smarty.section.i.index}-->" fill="<!--{$rule.fill_color|strippound}-->" stroke="<!--{$rule.stroke_color|strippound}-->" size="<!--{$rule.symbol_size}-->" symbol="<!--{$rule.symbol}-->"
 <!--{if $colorscheme ne 'single'}-->field="<!--{$rule.criteria1|escape:'htmlall'}-->" operator="<!--{$rule.criteria2|escape:'htmlall'}-->" value="<!--{$rule.criteria3|escape:'htmlall'}-->"<!--{/if}-->
  generated="1" />
<!--{/section}-->

</colorscheme>
