<!--{$subnav}-->
<form action="./?do=group.discussion.list&groupId=<!--{$group->id}-->" method="post">
<table style="width:100%;">
	<tr>
		<td>
			<input alt="New Topic Title" class="ghostText" type="text" name="name" style="width:100%"/>
		</td>
		<td style="width:20%;">
			<input type="submit" name="submit" value="Make New Post" style="width:100%"/>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea class="ghostText" alt="First Post Content" name="post" rows="8" style="width:100%;"></textarea>
		</td>
	</tr>
</table>
</form>
<table class="forum" style="width:100%;">
	<tr class="forum_header">
		<th>Discussion Topic</th>
		<th style="width:80px;">Replies</th>
		<th style="width:80px;">Views</th>
		<th>Last Post</th>
	</tr>
	<!--{foreach from=$group->getDiscussion($user) item=dis}-->
	<!--{assign var="ran" value=true}-->
	<tr class="forum_row<!--{if $dis.last_viewed < $dis.lastposttime}--> new<!--{/if}-->">
		<td><a href="./?do=group.discussion.view&groupId=<!--{$group->id}-->&view=<!--{$dis.id}-->" class="forum_link">
			<div class="forum_title"><!--{$dis.name}--></div>
			<div class="forum_desc">By:  <!--{$dis.owner_name}--> &raquo; <!--{$dis.created|date_format:"%a %b %e, %Y %l:%M%P"}--></div>
		</a></td>
		<td class="forum_cell_middle"><!--{$dis.replies}--></td>
		<td class="forum_cell_middle"><!--{$dis.views}--></td>
		<td><a href="./?do=group.discussion.view&groupId=<!--{$group->id}-->&view=<!--{$dis.id}-->#last" class="forum_link">
			<span class="forum_last">By: <!--{$dis.lastpostby_name}--><br/><!--{$dis.lastposttime|date_format:"%a %b %e, %Y %l:%M%P"}--></span></a>
		</td>
	</tr>
	<!--{/foreach}-->
	<!--{if !isset($ran)}-->
		<tr class="forum_row">
			<td colspan="4"><span class="forum_title new">No discussions yet for this group.</span></td>
		</tr>
	<!--{/if}-->
</table>
<a href="./?do=group.discussion.list&groupId=<!--{$group->id}-->&markall=1">Mark All Read</a>
<script>
	$(".ghostText").focus(function(srcc)
    {
        if ($(this).val() == $(this).attr("alt"))
        {
            $(this).removeClass("ghostTextActive");
            $(this).val("");
        }
    });
    
    $(".ghostText").blur(function()
    {
        if ($(this).val() == "")
        {
            $(this).addClass("ghostTextActive");
            $(this).val($(this).attr("alt"));
        }
    });
    
    $(".ghostText").blur();    
</script>