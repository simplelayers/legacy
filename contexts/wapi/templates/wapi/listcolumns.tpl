<columns layerid="<!--{$layer->id}-->">
<!--{foreach from=$columns key=columnname item=columntype}-->
    <column name="<!--{$columnname}-->" type="<!--{$columntype}-->" />
<!--{/foreach}-->
</columns>
