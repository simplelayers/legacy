<!--{$subnav}-->
<!--{include file="project/replies.tpl" project=$project replies=$project->getNestedReplies() world=$world  user=$user}-->
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
		$(this).parent().hide();
		$(this).parent().parent().find('a').show();
	});
</script>