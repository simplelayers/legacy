<!--{foreach from=$replies item=replys name=replys}-->
<!--{assign var="reply" value=$replys.data}-->
<!--{assign var="owner" value=$world->getPersonById($reply.owner)}-->
<div style="padding:0px;">
	<a name="<!--{$reply.id}-->"></a>
	<div style="font-size:10pt;padding:12px 6px 3px 6px;"><a href="./?do=contact.info&id=<!--{$owner->id}-->"><strong><!--{$owner->realname}--> (<!--{$owner->username}-->)</strong></a> - <span title="<!--{$reply.created|date_format:"%a %b %e, %Y %l:%M%P"}-->" class="toolTip"><!--{$reply.fromnow}--></span></div>
	<div style="font-size:12pt;padding:3px 6px;<!--{if $reply.text=="Comment Removed"}-->color:#999;font-style:italic;<!--{/if}-->"><!--{$reply.text}--></div>
	<div style="font-size:10pt;padding:3px 6px;"><a href="#" class="reply">reply</a><!--{if ($owner->id == $user->id || $user->admin || $project->owner->id == $user->id) && $reply.id}--> &nbsp;&nbsp; <a href="./?do=project.discussion&id=<!--{$project->id}-->&del=<!--{$reply.id}-->" class="delete">delete</a><!--{/if}-->
		<form style="display:none;" action="./?do=project.discussion&id=<!--{$project->id}-->" method="post">
			<textarea name="post" rows="8"></textarea><br/>
			<input type="hidden" name="parent" value="<!--{$reply.id}-->"/>
			<input type="submit" name="submit" value="Post"/> <input type="button" value="Cancel" class="cancel"/> 
		</form>
	</div>
	<div style="padding-left:24px;border-left:1px dotted #aaa;"><!--{if !empty($replys.children)}--><!--{include file="project/replies.tpl" project=$project replies=$replys.children world=$world user=$user}--><!--{/if}--></div>
</div>
<!--{/foreach}-->