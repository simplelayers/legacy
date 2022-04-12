<legend layer="<!--{$layer->id}-->" name="<!--{$layer->name}-->" type="<!--{$layer->colorschemetype}-->" layer_type="<!--{$layer->type}-->" >
<!--{section loop=$info name=item }-->
  <!--{assign var=entry value=$info[item].class}-->
  <!--{assign var=icon value=$info[item].icon}-->
  <item icon="<!--{$icon|escape:'htmlall'}-->" label="<!--{$entry->description|escape:'htmlall'}-->" tooltip="<!--{$entry->criteria1|escape:'htmlall'}--> <!--{$entry->criteria2|escape}--> <!--{$entry->criteria3|escape:'htmlall'}-->"  />
<!--{/section}-->
</legend>
