<?xml version="1.0" encoding="UTF-8"?>
<search viewer="<!--{$user->username}-->" projectid="<!--{$project->id}-->" projectname="<!--{$project->name|escape:'html'}-->" projectowner="<!--{$project->owner->username}-->" bbox="<!--{$bbox}-->" pbox="<!--{$pbox}-->">
<!--{* iterate through each layer... *}-->
<!--{section name=l loop=$results}-->
<!--{assign var=layer value=$results[l][0]}-->
<!--{assign var=fields value=$results[l][1]}-->
<!--{assign var=features value=$results[l][2]}-->
<!--{assign var=plid value=$results[l][3]}-->

<results layerid="<!--{$layer->id}-->" plid="<!--{$plid}-->" layername="<!--{$layer->name|escape:'html'}-->" label="<!--{$layer->name|escape:'html'}-->" geom="<!--{$layer->geomtypestring}-->">
   <!--{* iterate through the features *}-->
   <!--{foreach name='featureLoop' item=feature from=$features }-->
   <result 
   	<!--{if $geom}-->geom="<!--{$feature.wkt_geom}-->"<!--{/if}-->
  	 
   			<!--{foreach item=val key=att from=$feature}-->
   				<!--{if in_array($att,$fields)}-->   					
   					<!--{$att|escape:'htmlall'}-->="<!--{$val|escape:'htmlall'}-->"
   				<!--{/if}-->
   			<!--{/foreach}-->
   	    
   />
   <!--{/foreach}-->
</results>
<!--{/section}-->
</search>
