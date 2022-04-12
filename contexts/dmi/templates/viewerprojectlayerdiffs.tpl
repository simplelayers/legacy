<layers order="<!--{$layerorder}-->" removed="<!--{$removed_layerids}-->" status="<!--{$status}-->">
  <!--{section loop=$added_projectlayers name=i}-->
    <!--{assign var=projectlayer value=$added_projectlayers[i] }-->
    <!--{assign var=layer value=$projectlayer->layer }-->
    <!--{assign var=id value=$layer->id}-->
    <layer lid="<!--{$id}-->" name="<!--{$layer->name}-->" owner="<!--{$layer->owner->username}-->" geom="<!--{$layer->geomtypestring}-->" access="<!--{$layer->getPermissionById($user->id)}-->" opacity="<!--{$projectlayer->opacity}-->" layer_on="<!--{$projectlayer->on_by_default}-->" search_on="<!--{$projectlayer->searchable}-->" bbox="<!--{$layer->getSpaceExtent()}-->" style="<!--{$style}-->">
      <labels attribute="<!--{$projectlayer->labelitem}-->" labels_on="<!--{$projectlayer->labels_on}-->" />
      <tooltip tooltip_on="<!--{$projectlayer->tooltip_on}-->"><!--{$projectlayer->tooltip}--></tooltip>
      <attributes>
        <!--{foreach from=$layerattributes.$layerid key=attribname item=attribtype}-->
        <attribute name="<!--{$attribname}-->" type="<!--{$attribtype}-->" />
        <!--{/foreach}-->
      </attributes>
    </layer>    
  <!--{/section}-->
</layers>
