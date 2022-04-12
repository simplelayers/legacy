<table id="list" class="bordered" style="width:100%;">
<thead>
<tr>
	<!--{if isset($radio) && $radio}--><th style="width:16px;"></th><!--{/if}-->
	<!--{if !isset($hideEmail) || (isset($hideEmail) && !$hideEmail)}--><th style="width:16px;"><span><img src="media/icons/user.png"/></span></th><!--{/if}-->
	<!--{if !isset($hideBook) || (isset($hideBook) && !$hideBook)}--><th style="width:16px;"><span><img src="media/icons/email.png"/></span></th><!--{/if}-->
	<th style="width:200px;">Username</th>
	<th style="">Name</th>
	<!--{if isset($group) && ($group->moderator->id == $user->id || $user->admin)}--><!--{if isset($inviteonly) && $inviteonly}--><th>Action<!--{else}--><th style="width:16px"><!--{/if}--></th><!--{/if}-->
	</tr>
</thead>
<tbody>
</tbody>
</table>
<script>
var listOfContacts = {};
function getAdded(contact){
	if(contact["id"] == <!--{$user->id}-->) return '&nbsp;';
	var marked = contact["added"];
	if(marked == 'true') return '<span style="display:none;">d</span><img title="Unbookmark this user." src="'+deleteImg+'" class="'+contact["id"]+'"/>';
	return '<span style="display:none;">a</span><img title="Bookmark this user." src="'+addImg+'" class="'+contact["id"]+'"/>';
}
function getName(contact){
<!--{if isset($group)}-->if(contact["id"] == <!--{$group->getMod()}-->) return '<span style="color:#00CC00;">M </span>'+contact["realname"];<!--{/if}-->
return contact["realname"];
}
$(function() {
$('#navRow').removeClass('hidden');
	<!--{if isset($contact_selector) === false}-->
	$('#contact_selector').dataSelector(
		{'type' : 'contact'
		<!--{if isset($group) && ($group->moderator->id == $user->id || $user->admin)}-->, 'extend' : '&group=<!--{$group->id}-->'<!--{/if}-->
		<!--{if isset($tag) && $tag}-->, 'default' : '5', 'tag' : '<!--{$tag}-->'<!--{/if}-->}
		).bind("update", function(e, data){listOfContacts = data;rebuildList();}).bind("loading", function(e){$('#list').dataTable().fnClearTable();<!--{if isset($radio) && $radio}-->$('#newBox').attr('checked',true);<!--{/if}-->});<!--{/if}-->
	$dt = $('#list').dataTable({
		"bPaginate": false,
		"bFilter": <!--{if isset($filter) && !$filter}-->false<!--{else}-->true<!--{/if}-->,
		"bInfo": false,
		"bAutoWidth": false,
		"sDom": '',
		"bStateSave": true,
		"aaSorting": [[ 3, "asc" ]],
		"aoColumns": [
			<!--{if isset($radio) && $radio}-->{ "sClass": "radio" },<!--{/if}-->
			<!--{if !isset($hideEmail) || (isset($hideEmail) && !$hideEmail)}-->{ "sClass": "added" },<!--{/if}-->
			<!--{if !isset($hideBook) || (isset($hideBook) && !$hideBook)}-->{ "sClass": "mail" },<!--{/if}-->
			{ "sClass": "username" },
			{ "sClass": "name" }
			<!--{if isset($group) && ($group->moderator->id == $user->id || $user->admin)}-->,{ "sClass": "action" }<!--{/if}-->
		],
		"oLanguage": {
		  "sEmptyTable": "No contacts to display.",
		  "sZeroRecords": "No matching contacts found."
		}
	});
	$dt.fnFilter('');
      $(".filterNav input").val('');
     $(".filterNav input").bind("input",function(){
    	var val = $(".filterNav input").val();
    	$dt.fnFilter(val); 
    });
	
});
function rebuildList(type){
	$('#list').dataTable().fnClearTable();
	;
	var rows = new Array();
	
	if(listOfContacts.view) {
		$.each(listOfContacts.view, function(i, contact) {
			var usernameRow = '<a style="font-weight:bold;" href=".?do=contact.info&contactId='+contact["id"]+'">'+contact["username"]+'</a>';
			var realnameRow = '<a style="font-weight:bold;" href=".?do=contact.info&contactId='+contact["id"]+'">'+getName(contact)+'</a>';
		
			<!--{if !$isSys}-->
			if((contact['visible']=='f') || (contact['visible']===false)) {
				var usernameRow = contact["username"];
				var realnameRow = getName(contact);
			}
			<!--{/if}-->
			<!--{if isset($inviteonly) && $inviteonly}-->if(contact["status"] != 1){<!--{/if}-->
			rows.push([
				<!--{if isset($radio) && $radio}-->'<input type="radio" name="contact" value="'+contact["id"]+'" />',<!--{/if}-->
				<!--{if !isset($hideEmail) || (isset($hideEmail) && !$hideEmail)}-->getAdded(contact),<!--{/if}-->
				<!--{if !isset($hideBook) || (isset($hideBook) && !$hideBook)}-->'<a style="font-weight:bold;" href="<!--{$baseURL}-->?do=contact.email1&contactId='+contact["id"]+'"><img src="media/icons/email.png"/></a>',<!--{/if}-->
				usernameRow,
				realnameRow
				<!--{if isset($group) && ($group->moderator->id == $user->id || $user->admin)}-->,getGroupStatus(contact)<!--{/if}-->
			]);
			<!--{if isset($inviteonly) && $inviteonly}-->}<!--{/if}-->
		});
	} else {
	
	}
	$('#list').dataTable().fnAddData(rows);
	$('td.added img').click(added);
	rearmToolTips();
}
function getGroupStatus(contact){
	<!--{if isset($group)}-->if(contact["id"] == <!--{$group->moderator->id}-->) return '';<!--{/if}-->
	var out = '<a href="<!--{$baseURL}-->/group/action/action:invite/userid:'+contact["id"]+'<!--{if isset($group)}-->/groupId:<!--{$group->id}--><!--{/if}-->">Invite</a>';
	if(contact["status"] == 1)out = '<a href="<!--{$baseURL}-->group/action/action:kick/userid:'+contact["id"]+'<!--{if isset($group)}-->/groupId:<!--{$group->id}-->"<!--{/if}-->><img src="media/icons/delete.png"/></a>';
	if(contact["status"] == 2)out = '<a href="<!--{$baseURL}-->group/action/action:uninvite/userid:'+contact["id"]+'<!--{if isset($group)}-->/groupId:<!--{$group->id}--><!--{/if}-->">Uninvite</a>';
	if(contact["status"] == 3)out = '<a href="<!--{$baseURL}-->group/action/action:acceptrequest/userid:'+contact["id"]+'<!--{if isset($group)}-->/groupId:<!--{$group->id}--><!--{/if}-->">Accept</a> - <a href="<!--{$baseURL}-->/group/action/action:denyrequest/user:'+contact["id"]+'<!--{if isset($group)}-->/group:<!--{$group->id}--><!--{/if}-->">Deny</a>';
	if(contact["status"] == 4)out = '<a href="<!--{$baseURL}-->group/action/action:acceptrequest/userid:'+contact["id"]+'<!--{if isset($group)}-->/groupId:<!--{$group->id}--><!--{/if}-->">Accept</a>';
	<!--{if $user->admin}-->if(contact["status"] != 1) out += '<a style="float:right;" href="<!--{$baseURL}-->/group/action/action:forceadd/userid:'+contact["id"]+'<!--{if isset($group)}-->/groupId:<!--{$group->id}--><!--{/if}-->">Force Add</a>';<!--{/if}-->
	return out;
}
var addImg = "media/icons/user_add.png";
var deleteImg = "media/icons/user_delete.png";
function added(e){
	var tar = $(e.target);
	if(tar.attr('src') == addImg){
		$.post('./?do=contact.add&noreply=1&contactId='+tar.attr("class"));
		tar.attr('src', deleteImg);
		tar.parent().children('span').html('d');
	}else{
		$.post('./?do=contact.remove&noreply=1&contactId='+tar.attr("class"));
		tar.attr('src', addImg);
		tar.parent().children('span').html('a');
		if($('.sel').val() == 1){
			var aTrs = $('#list').dataTable().fnGetNodes();
			for ( var i=0 ; i<aTrs.length ; i++ ){
				if ( $(aTrs[i]).html() == $(e.target).parent().parent().html()){
					$('#list').dataTable().fnDeleteRow( aTrs[i] );
				}
			}
		}
	}
}
</script>