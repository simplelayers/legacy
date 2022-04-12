<projects>
<!--{section name=i loop=$projects}-->
<!--{assign var=project value=$projects[i]}-->
  <project id="<!--{$project->id}-->" name="<!--{$project->name|escape:'htmlall'}-->" description="<!--{$project->description|escape:'htmlall'}-->" lastupdateago="<!--{$project->last_modified_seconds}-->" />
<!--{/section}-->
</projects>
