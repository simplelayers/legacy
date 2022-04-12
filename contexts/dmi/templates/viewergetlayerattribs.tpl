<layerattributes>
  <layer lid="<!--{$layer->id}-->" name="<!--{$layer->name}-->" owner="<!--{$layer->owner->username}-->"/>
  <attributes>
  <!--{foreach from=$attribs key=attrib item=datatype}-->
    <!--{assign var=islabel value=0}-->
    <!--{if $labelitem==$attrib}--><!--{assign var=islabel value=1}--><!--{/if}-->
    <attrib name="<!--{$attrib}-->" type="<!--{$datatype}-->" islabel="<!--{$islabel}-->" />
  <!--{/foreach}-->
  </attributes>
</layerattributes>
