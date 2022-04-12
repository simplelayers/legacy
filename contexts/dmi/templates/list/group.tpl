<table id="list" class="bordered" style="width:100%;">
<thead>
<tr>
	<th style="width:16px;">&nbsp;</th>
	<th style="">Name</th>
	<th style="">Description</th>
	<th style="">Members</th>
	<th style="">Maps</th>
	<th style="">Layers</th>
	<th style="">Action</th>
</tr>
</thead>
<tbody>
</tbody>
</table>
<script>
var listOfGroups = {};
$(function() {
$('#navRow').removeClass('hidden');
	$('#selector').dataSelector({'type' : 'group'<!--{if $tag}-->, 'default' : '5', 'tag' : '<!--{$tag}-->'<!--{/if}-->}).bind("update", function(e, data){listOfGroups = data;rebuildList();}).bind("loading", function(e){$('#list').dataTable().fnClearTable();});
	$dt = $('#list').dataTable({
		"bPaginate": false,
		"bFilter": true,
		"bInfo": false,
		"bAutoWidth": false,
		"sDom": '',
		"bStateSave": true,
		"aaSorting": [[ 3, "desc" ]],
		"aoColumns": [
			{ "sClass": "status" },
			{ "sClass": "name" },
			{ "sClass": "description" },
			{ "sClass": "members" },
			{ "sClass": "maps" },
			{ "sClass": "layers" },
			{ "sClass": "action" }
		],
		"oLanguage": {
		  "sEmptyTable": "No groups to display.",
		  "sZeroRecords": "No matching groups found."
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
	var rows = new Array();
	$.each(listOfGroups.view, function(i, group) {
		rows.push([
			getstatus(group["status"]),
			'<a href="./?do=group.info&groupId='+group["id"]+'">'+group["title"]+'</a>',
			group["description"],
			group["members"],
			'<a href="./?do=project.list&groupId='+group["id"]+'">Maps</a>',
			'<a href="./?do=layer.list&groupId='+group["id"]+'">Layers</a>',
			getAction(group)
		]);
	});
	$('#list').dataTable().fnAddData(rows);
	rearmToolTips();
}
function getstatus(status){
	if(status == 1)return '<span style="color:#00CC00;" title="Approved. You are in this group.">A</span>';
	if(status == 2)return '<span style="color:#0033CC;" title="Pending. You may accept or deny.">P</span>';
	if(status == 3)return '<span style="color:#0033CC;" title="Pending. Waiting for moderator approval.">P</span>';
	if(status == 4)return '<span style="color:#CC0000;" title="Denied. You have not been accepted.">D</span>';
	if(status == 5)return '<span style="color:#00CC00;" title="Moderator. You are the moderator of this group.">M</span>';
	return '&nbsp;';
}
function getAction(group){
	if(group["status"] == 1)return '<a href="./?do=group.action&action=leave&group='+group["id"]+'">Leave</a>';
	if(group["status"] == 2)return '<a href="./?do=group.action&action=acceptinvite&group='+group["id"]+'">Accept</a> - <a href="./?do=group.action&action=denyinvite&group='+group["id"]+'">Deny</a>';
	if(group["status"] == 3)return '<a href="./?do=group.action&action=unrequest&group='+group["id"]+'">Unrequest</a>';
	if(group["status"] == 4)return '';
	if(group["status"] == 5)return '<a href="./?do=group.info&groupId='+group["id"]+'">Manage</a>';
	if(group["invite"] == "t"){
		return '<a href="./?do=group.action&action=request&group='+group["id"]+'">Request</a>';
	}else{
		return '<a href="./?do=group.action&action=join&group='+group["id"]+'">Join</a>';
	}
	return '';
}
var addImg = "media/icons/user_add.png";
var deleteImg = "media/icons/user_delete.png";
</script>