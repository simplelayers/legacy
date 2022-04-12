<?xml version="1.0" encoding="UTF-8" ?>
<features layerid="<!--{$layerid}-->" plid="<!--{$plid}-->" geom="<!--{$layerType}-->" <!--{$paging->toAttString()}--> ><!--{foreach from=$features item=feature}-->
   	<feature<!--{foreach from=$feature key=fieldname item=fieldvalue }--><!--{if in_array($fieldname,$exclusions)==false}--> <!--{$fieldname}-->="<!--{$fieldvalue|escape:'htmlall'}-->"<!--{/if}--><!--{/foreach}--> />
   	<!--{/foreach}-->
</features>
