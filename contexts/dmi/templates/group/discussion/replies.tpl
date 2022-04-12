<!--{foreach from=$replies item=replys name=replys}-->
<!--{assign var="reply" value=$replys.data}-->
<!--{assign var="owner" value=$world->getPersonById($reply.owner)}-->
<div class='group_forum' style="padding:0px;">
	<a name="<!--{$reply.id}-->"></a>
	<div style="border:1px solid #ccc;border-top:0;<!--{if $color}-->background-color:#efefef;<!--{/if}-->">
	<div style="font-size:10pt;padding:12px 6px 3px 6px;"><a href="./?do=contact.info&id=<!--{$owner->id}-->"><strong><!--{$owner->realname}--> (<!--{$owner->username}-->)</strong></a> - <span title="<!--{$reply.created|date_format:"%a %b %e, %Y %l:%M%P"}-->" class="toolTip"><!--{$reply.fromnow}--></span></div>
	<div style="font-size:12pt;padding:3px 6px;<!--{if $reply.text=="Comment Removed"}-->color:#999;font-style:italic;<!--{/if}-->"><!--{$reply.text}--></div>
	<div style="font-size:10pt;padding:3px 6px;"><button type='button'  class="reply button">reply</button><!--{if $owner->id == $user->id || $user->admin || $group->owner->id == $user->id}--> &nbsp;&nbsp; <button  onClick="document.location='./?do=group.discussion.view&groupId=<!--{$group->id}-->&view=<!--{$dis.id}-->&del=<!--{$reply.id}-->'" class="delete button">delete</button><!--{/if}-->
		<form style="display:none;" action="./?do=group.discussion.view&groupId=<!--{$group->id}-->&view=<!--{$dis.id}-->" method="post">
			<p/>
			<div>
				<textarea name="post" rows="8"></textarea>
				<input type="hidden" name="parent" value="<!--{$reply.id}-->"/>
			</div>
			<p/>
			<div>
			<input type="hidden" name="is_first" value="<!--{$smarty.foreach.replys.index}-->" /> 
			<button class='button' type="submit" name="submit">Post</button> <button type="button" class="cancel button">Cancel</button>
			</div> 
		</form>
	</div>
	</div>
<!--{if $color}--><!--{assign var="color" value=false}--><!--{else}--><!--{assign var="color" value=true}--><!--{/if}-->
	<div style="padding-left:24px;border-left:1px dotted #aaa;"><!--{if !empty($replys.children)}--><!--{include file="group/discussion/replies.tpl" group=$group replies=$replys.children color=$color world=$world user=$user}--><!--{/if}--></div>
</div>
<!--{/foreach}-->