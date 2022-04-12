<projects username="<!--{$user->username}-->" userid="<!--{$user->id}-->">
  <!--{section name=i loop=$projects}-->
  <project pid="<!--{$projects[i]->id}-->" name="<!--{$projects[i]->name|escape:'html'}-->"/>
  <!--{/section}-->
</projects>
