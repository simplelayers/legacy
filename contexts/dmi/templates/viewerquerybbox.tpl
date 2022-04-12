<search viewer="<!--{$user->username}-->" projectid="<!--{$project->id}-->" projectname="<!--{$project->name|escape:'html'}-->" projectowner="<!--{$project->owner->username}-->" llx="<!--{$bbox[0]}-->" lly="<!--{$bbox[1]}-->" urx="<!--{$bbox[2]}-->" ury="<!--{$bbox[3]}-->">

<!--{* iterate through each layer... *}-->
<!--{section name=l loop=$results}-->
<!--{assign var=layer value=$results[l][0]}-->
<!--{assign var=fields value=$results[l][1]}-->
<!--{assign var=features value=$results[l][2]}-->
<results layerid="<!--{$layer->id}-->" layername="<!--{$layer->name|escape:'html'}-->" label="<!--{$layer->name|escape:'html'}-->">

   <!--{* iterate through the features *}-->
   <!--{section name=f loop=$features}-->
   <!--{assign var=feature value=$features[f]}-->
   <result gid="<!--{$feature.gid}-->" label="<!--{$feature._label}-->" name="<!--{$feature._name}-->"
      <!--{if $geom}-->geom="<!--{$feature.wkt_geom}-->"<!--{/if}-->
      <!--{section name=i loop=$fields}-->
         <!--{assign var=fieldname value=$fields[i]}-->
         <!--{assign var=fieldvalue value=$feature.$fieldname}-->
         <!--{$fieldname}-->="<!--{$fieldvalue|escape:'htmlall'}-->"
      <!--{/section}-->
   />
   <!--{/section}-->

</results>
<!--{/section}-->

</search>
