<layers>
<!--{section name=i loop=$layers}-->
<!--{assign var=layer value=$layers[i]}-->
  <layer id="<!--{$layer->id}-->" name="<!--{$layer->name|escape:'htmlall'}-->" description="<!--{$layer->description|escape:'htmlall'}-->" lastupdateago="<!--{$layer->last_modified_seconds}-->" />
<!--{/section}-->
</layers>
