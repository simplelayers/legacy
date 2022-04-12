<div style="float:right;">
<span id="staff">Staff: <span class="used"></span>/<span class="has"><!--{$org->st_seats}--></span></span> | 
<span id="executive">Executive: <span class="used"></span>/<span class="has"><!--{$org->ex_seats}--></span></span> | 
<span id="power">Power User: <span class="used"></span>/<span class="has"><!--{$org->po_seats}--></span></span>
</div>
<table id="list" class="bordered" style="width:100%;">
<thead>
<tr>
	<th style="width:16px;"><span><img src="media/icons/user.png"/></span></th>
	<th style="width:16px;"><span><img src="media/icons/email.png"/></span></th>
	<th style="width:200px;">Username</th>
	<th style="">Name</th>
	<th style="width:100px;">Seat</th>
	<!--{if $group->moderator->id == $user->id || $user->admin}--><th style="width:32px;">Drop</th><!--{/if}-->
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
if(contact["id"] == <!--{$group->moderator->id}-->) return '<span style="color:#00CC00;">M </span>'+contact["realname"];
return contact["realname"];
}
function getSeat(contact){
var seat = "No Role";
var hasContact = true;
if(!contact){
	seat = "Staff";
	hasContact = false;
	contact = Array();
	contact["seat"] = 0;
	contact["id"] = "new";
}
var toReturn = "";
if(contact["seat"] == 1) seat = "Staff";
if(contact["seat"] == 2) seat = "Executive";
if(contact["seat"] == 3) seat = "Power User";
<!--{if $group->moderator->id == $user->id || $user->admin}-->
	toReturn = "<select class=\"seat\" "+(hasContact ? "" : "name=\"seat\"")+" id=\""+contact["id"]+"\">";
	if(hasContact) toReturn = toReturn+"<option value=\"0\" "+(contact["seat"] == 0 ? "selected=\"selected\"" : "")+">No Role</option>";
	if(contact["seat"] == 1 || staffUsed < $('#staff .has').text()) toReturn = toReturn+"<option value=\"1\" "+(contact["seat"] == 1 ? "selected=\"selected\"" : "")+">Staff</option>";
	if(contact["seat"] == 2 || executiveUsed < $('#executive .has').text()) toReturn = toReturn+"<option value=\"2\" "+(contact["seat"] == 2 ? "selected=\"selected\"" : "")+">Executive</option>";
	if(contact["seat"] == 3 || powerUsed < $('#power .has').text()) toReturn = toReturn+"<option value=\"3\" "+(contact["seat"] == 3 ? "selected=\"selected\"" : "")+">Power User</option>";
	toReturn = toReturn+"</select>";
	return toReturn;
<!--{else}-->
return seat;<!--{/if}-->
}
$(function() {
$('#navRow').removeClass('hidden');
	$('#list').dataTable({
		"bPaginate": false,
		"bFilter": true,
		"bInfo": false,
		"bAutoWidth": false,
		"sDom": '<"filterNav"f>lipt',
		"bStateSave": true,
		"aaSorting": [[ 3, "asc" ]],
		"aoColumns": [
			{ "sClass": "added" },
			{ "sClass": "mail" },
			{ "sClass": "username" },
			{ "sClass": "name" },
			{ "sClass": "actor" }
			<!--{if $group->moderator->id == $user->id || $user->admin}-->,{ "sClass": "action" }<!--{/if}-->
		],
		"oLanguage": {
		  "sEmptyTable": "No contacts to display.",
		  "sZeroRecords": "No matching contacts found."
		}
	});
});
var staffUsed = 0;
var executiveUsed = 0;
var powerUsed = 0;
function rebuildList(){
	$('#list').dataTable().fnClearTable();
	var rows = new Array();
	staffUsed = 0;
	executiveUsed = 0;
	powerUsed = 0;
	$.each(listOfContacts.view, function(i, contact) {
		if(contact["seat"] != 0){
			if(contact["seat"] == 1){
			staffUsed = staffUsed+1;
			}else if(contact["seat"] == 2){
			executiveUsed = executiveUsed+1;
			}else{
			powerUsed = powerUsed+1;
			}
		}
	});
	$('#staff .used').text(staffUsed);
	$('#executive .used').text(executiveUsed);
	$('#power .used').text(powerUsed);
	$.each(listOfContacts.view, function(i, contact) {
		rows.push([
			getAdded(contact),
			'<a style="font-weight:bold;" href=".?do=contact.email1&id='+contact["id"]+'"><img src="media/icons/email.png"/></a>',
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+contact["id"]+'">'+contact["username"]+'</a>',
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+contact["id"]+'">'+getName(contact)+'</a>',
			getSeat(contact)
			<!--{if $group->moderator->id == $user->id || $user->admin}-->,getGroupStatus(contact)<!--{/if}-->
		]);
	});
	$('#list').dataTable().fnAddData(rows);
	$('td.added img').click(added);
	rearmToolTips();
	$('.seat').change(listChange);
	$(".newSeat").html(getSeat(false));
}
function getGroupStatus(contact){
	if(contact["id"] == <!--{$group->moderator->id}-->) return '';
	if(contact["status"] == 1)return '<a href="./?do=group.action&action=kick&userid='+contact["id"]+'<!--{if isset($group)}-->&group=<!--{$group->id}-->"<!--{/if}-->><img src="media/icons/delete.png"/></a>';
	if(contact["status"] == 2)return '<a href="./?do=group.action&action=uninvite&userid='+contact["id"]+'<!--{if isset($group)}-->&group=<!--{$group->id}--><!--{/if}-->">Uninvite</a>';
	if(contact["status"] == 3)return '<a href="./?do=group.action&action=acceptrequest&userid='+contact["id"]+'<!--{if isset($group)}-->&group=<!--{$group->id}--><!--{/if}-->">Accept</a> - <a href="./?do=group.action&action=denyrequest&user='+contact["id"]+'<!--{if isset($group)}-->&group=<!--{$group->id}--><!--{/if}-->">Deny</a>';
	if(contact["status"] == 4)return '<a href="./?do=group.action&action=acceptrequest&userid='+contact["id"]+'<!--{if isset($group)}-->&group=<!--{$group->id}--><!--{/if}-->">Accept</a>';
	return '<a href="./?do=group.action&action=invite&userid='+contact["id"]+'<!--{if isset($group)}-->&group=<!--{$group->id}--><!--{/if}-->">Invite</a>';
}
var addImg = "media/icons/user_add.png";
var deleteImg = "media/icons/user_delete.png";
function added(e){
	var tar = $(e.target);
	if(tar.attr('src') == addImg){
		$.post('./?do=contact.add&id='+tar.attr("class"));
		tar.attr('src', deleteImg);
		tar.parent().children('span').html('d');
	}else{
		$.post('./?do=contact.remove&id='+tar.attr("class"));
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
function listChange(event){
	var selectList = $(event.currentTarget);
	$.each(listOfContacts.view, function(i, contact) {
		if(contact["id"] == selectList.attr('id')) contact["seat"] = selectList.val();
	});
	$.post('./?do=wapi.organization.changerole&id=<!--{$org->id}-->&employee='+selectList.attr('id')+'&seat='+selectList.val());
	rebuildList();
}
$(function() {
	queue = $('#list').jsonQueue();
	queue.nextQueue("./?do=wapi.contact.views&type=group&me=true&id=<!--{$group->id}-->&format=json", function(jsonData, context) {
		listOfContacts = jsonData;
		rebuildList();
	},
	function(jsonData){});
	
});
</script>