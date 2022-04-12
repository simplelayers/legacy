<!--{$subnav}-->
<form action="./?do=group.editinfo" method="post">
	<div style="float:left;width:50%;" id="infoarea">
		<input type="hidden" name="id" value="<!--{$group->id}-->" />
		<div style="font-size:10pt;padding:6px 0;"><div style="font-weight:700;font-size:12pt;margin-bottom:6px;">Name:</div><span id="name"><!--{$group->title}--></span></a></div>
		<span id="show" style="">
			<div style="font-size:10pt;padding:6px 0;"><div style="font-weight:700;font-size:12pt;margin-bottom:6px;">Privacy:</div>
				<!--{if $invite}-->Invite Only<!--{else}-->Open Invitation<!--{/if}--><br/>
				<!--{if $hidden}-->Hidden From List<!--{else}-->Displayed In List<!--{/if}-->
			</div>
		</span>
		<span id="hide" style="display:none;">
		<div style="font-size:10pt;padding:6px 0;"><div style="font-weight:700;font-size:12pt;margin-bottom:6px;">Privacy:</div>
			<input type="checkbox" name="invite" <!--{if $invite}-->checked="checked"<!--{/if}-->/> Invite Only<br/>
			<input type="checkbox" name="hidden" <!--{if $hidden}-->checked="checked"<!--{/if}-->"/> Hide In List
		</div>
		<div style="font-size:10pt;padding:6px 0;"><div style="font-weight:700;font-size:12pt;margin-bottom:6px;">Change Moderator:</div>
		<!--{html_options options=$moderators name=moderator}-->
		
		</div>
		</span>
		<!--{if $isModerator}--><button id="edit" style="margin-bottom:6px;">Edit</button><!--{/if}-->
	</div>
	<div style="float:left;width:50%;">
		<div style="font-size:10pt;padding:6px 0;">
			<div style="font-weight:700;font-size:12pt;margin-bottom:6px;">Description:</div>
			<span id="desc" class='wrapped'><!--{$group->description|nl2br}--></span>
		</div>
		<div style="font-size:10pt;padding:6px 0;">
			<div style="font-weight:700;font-size:12pt;margin-bottom:6px;">Tags:</div>
			<div id="tags"><!--{$taglinks}--></div>
		</div>
		
	</div>
	<br style="clear:both;"/>
</form>
<div style="font-weight:700;font-size:12pt;margin-bottom:6px;">Members:</div>
<!--{include file='list/contact.tpl'}--><!--{if $group->moderator->id == $user->id}-->
<br/>
<div style="font-weight:700;font-size:12pt;margin-bottom:6px;">Applicants:</div>

<!--{include file='list/applicants.tpl'}-->
<br/>
<div style="font-weight:700;font-size:12pt;margin-bottom:6px;">Denied Applicants:</div>

<!--{include file='list/denied.tpl'}-->
<!--{/if}-->
<script>
	var edit = false;
	function toggleEdit(){
		if(edit){
			return true;
		}else{
			$('#name').html('<input style="width:90%;" type="text" name="name" value="'+$('#name').text()+'" />');
			$('#desc').html('<textarea style="width:90%;height:90px;" name="desc">'+$('#desc').text()+'</textarea>');
			$('#hide').css('display', 'inline');
			$('#show').css('display', 'none');
			$('#tags').html('<textarea style="width:90%;height:90px;" name="tags">'+$('#tags').text()+'</textarea>');
			$('#edit').text('Save Changes');
			$('<button id="cancel" style="margin-bottom:6px;">Cancel</button>').appendTo('#infoarea').click(function(){window.location.replace('./?do=group.info&groupId=<!--{$group->id}-->');return false;});
			$('#tags textarea').tagsInput({
				width: 'auto'
			});
			edit = true;
			return false;
		}
	}
	$(function() {
		$('#edit').click(toggleEdit);
		queue = $('#list').jsonQueue();
		queue.nextQueue("./?do=wapi.contact.views&type=group&id=<!--{$group->id}-->&group=<!--{$group->id}-->&me=1&format=json", (function(jsonData, context) {
			listOfContacts = jsonData;
			rebuildList();
		}).bind(this),
		function(jsonData){});
		<!--{if $group->moderator->id == $user->id}-->$.ajax({
		  url: "./?do=wapi.contact.views&type=applicants&id=<!--{$group->id}-->&group=<!--{$group->id}-->&me=1&format=json",
		  dataType: 'json',
		  success: function(jsonData){
				listOfContacts2 = jsonData;
				rebuildList2();
			}
		});
		$.ajax({
		  url: "./?do=wapi.contact.views&type=denied&id=<!--{$group->id}-->&group=<!--{$group->id}-->&me=1&format=json",
		  dataType: 'json',
		  success: function(jsonData){
				listOfContacts3 = jsonData;
				rebuildList3();
			}
		});<!--{/if}-->
		$('.dataTables_filter').remove();
	});
</script>