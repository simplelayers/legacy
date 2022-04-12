<layer lid="<!--{$layer->id}-->" name="<!--{$layer->name}-->" owner="<!--{$layer->owner->username}-->" geom="<!--{$layer->geomtypestring}-->" access="<!--{$layer->getPermissionById($user->id)}-->" opacity="<!--{$projectlayer->opacity}-->" layer_on="<!--{$projectlayer->on_by_default}-->" search_on="<!--{$projectlayer->searchable}-->" bbox="<!--{$layer->getSpaceExtent()}-->">

    <labels attribute="<!--{$projectlayer->labelitem}-->" labels_on="<!--{$projectlayer->labels_on}-->" />
    <tooltip tooltip_on="<!--{$projectlayer->tooltip_on}-->"><!--{$projectlayer->tooltip}--></tooltip>
    <attributes>
    <!--{foreach from=$attribs key=attrib item=datatype}-->
      <attrib name="<!--{$attrib}-->" type="<!--{$datatype}-->" />
    <!--{/foreach}-->
    </attributes>

</layer>
