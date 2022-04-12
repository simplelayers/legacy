<project pid="<!--{$project->id}-->" name="<!--{$project->name}-->" owner="<!--{$project->owner->username}-->" access="<!--{$permission}-->" viewer="<!--{$user->username}-->">
<bbox llx="<!--{$bbox[0]}-->" lly="<!--{$bbox[1]}-->" urx="<!--{$bbox[2]}-->" ury="<!--{$bbox[3]}-->" />
<viewsize width="<!--{$viewsize[0]}-->" height="<!--{$viewsize[1]}-->" />
<layers>
<!--{section name=i loop=$layerlist}-->
  <!--{assign var=projectlayer    value=$layerlist[i]}-->
  <!--{assign var=projectlayerid  value=$projectlayer->id}-->
  <!--{assign var=layer           value=$projectlayer->layer}-->
  <!--{assign var=layerid         value=$layer->id}-->
  <!--{assign var=style           value=$styles.$projectlayerid}-->

<layer lid="<!--{$layer->id}-->" name="<!--{$layer->name}-->" owner="<!--{$layer->owner->username}-->" geom="<!--{$layer->geomtypestring}-->" access="<!--{$layer->getPermissionById($user->id)}-->" opacity="<!--{$projectlayer->opacity}-->" layer_on="<!--{$projectlayer->on_by_default}-->" search_on="<!--{$projectlayer->searchable}-->" bbox="<!--{$layer->getSpaceExtent()}-->" style="<!--{$style}-->">

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

</project>
