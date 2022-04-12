<!--{$subnav}-->
<!--{assign var="color" value=false}-->
<button class='button' onClick="document.location='./?do=group.discussion.list&groupId=<!--{$group->id}-->'">Back to forum</button>
<button class='button' onClick="document.location='./?do=group.discussion.view&groupId=<!--{$group->id}-->&view=<!--{$dis.id}-->'">Refresh</button>
<!--<h3 class='section_head'><a href="./?do=group.discussion.view&groupId=<!--{$group->id}-->&view=<!--{$dis.id}-->">Topic: <!--{$dis.name}--></a></h3>-->

<!--{include file="group/discussion/replies.tpl" group=$group dis=$dis replies=$replies world=$world color=$color user=$user}-->
<script>
	$('.reply').click(function(event){
		event.preventDefault();
		$(this).parent().find('a').hide();
		$(this).parent().find('form').show();
		$(this).parent().find('textarea').focus();
	});
	$('.cancel').click(function(event){
		event.preventDefault();
		$(this).parent().find('textarea').val('');
		$(this).parent().parent().hide();
		
		//$(this).parent().parent().find('a').show();
	});
</script>