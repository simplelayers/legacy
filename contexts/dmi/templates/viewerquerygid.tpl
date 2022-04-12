<search viewer="<!--{$user->username}-->">
  <project pid="<!--{$project->id}-->" name="<!--{$project->name}-->" owner="<!--{$project->owner->username}-->" />
  <layer lid="<!--{$layer->id}-->" name="<!--{$layer->name}-->" owner="<!--{$layer->owner->username}-->" accesslevel="$accesslevel"/>
  <query gid="<!--{$querygid}-->"/>
  <results>
    <!--{if $feature}-->
    <result label="<!--{$label}-->">
    <!--{foreach from=$feature key=attrib item=value}-->
      <item Field="<!--{$attrib|escape:'htmlall'}-->" value="<!--{$value|escape:'htmlall'}-->" />
    <!--{/foreach}-->
    </result>
    <!--{/if}-->
  </results>
</search>
