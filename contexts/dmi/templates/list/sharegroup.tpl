<table id="listGroup" class="perm-list bordered" style="width:100%;">
<thead>
<tr>
	<th class="contact-heading status">Status</th>
	<th class="contactheading name">Name</th>
	<th class="contact-heading option-set permissions">Permission</th>
        <!--{if $needRptLvl}-->
        <th class="options-heading option-set reporting">Reporting Level</th>        
        <!--{/if}-->
</tr>
</thead>
<tbody>
</tbody>
</table>
<script>
var listOfGroups = {};
$(function() {
$('#navRow').removeClass('hidden');
	$('#selectorGroup').dataSelector({'type' : 'groupmine'<!--{if isset($project)}-->, 'extend' : '&shareproject=<!--{$project->id}-->'<!--{else}-->, 'extend' : '&sharelayer=<!--{$layer->id}-->'<!--{/if}-->}).bind("update", function(e, data){listOfGroups = data;rebuildListGroup();}).bind("loading", function(e){$('#listGroup').dataTable().fnClearTable();});
	$('#listGroup').dataTable({
		"bPaginate": false,
		"bFilter": true,
		"bInfo": false,
		"bAutoWidth": false,
		"sDom": '<"filterNavGroup"f>lipt',
		"bStateSave": true,
		"aaSorting": [[ 3, "desc" ]],
		"aoColumns": [
                        { "sClass": "contact-value status" },
			{ "sClass": "contact-value name" },
			{ "sClass": "contact-value option-set permission" }
                        <!--{if $needRptLvl}-->,
                        { "sClass": "contact-value option-set reporting" }
                        <!--{/if}-->
		],
		"oLanguage": {
		  "sEmptyTable": "No groups to display.",
		  "sZeroRecords": "No matching groups found."
		}
	});
});
function MakePermissionRadioButton(group,label,permission,isFirst) {
    const firstClass = isFirst ? 'first' : '';
    return `<input class="${firstClass} option-radio" type="radio" name="g${group["id"]}" value="${permission}" ${group["permission"] == permission ? "checked" : ""}><label for="g${group["id"]}">${label}</label>`;
}
function MakeReportLvlRadioButton(group,label,level,isFirst) {
    const firstClass = isFirst ? 'first' : '';
    return `<input class="${firstClass} option-radio" type="radio" name="g${group["id"]}_rpt" value="${level}" ${group["reporting"] == level ? "checked" : ""}><label for="g${group["id"]}_rpt">${label}</label>`;
}
function rebuildListGroup(type){
	$('#listGroup').dataTable().fnClearTable();
	var rows = new Array();
        if(!listOfGroups.view) {
            return;
        }
	$.each(listOfGroups.view, function(i, group) {
            console.log(group);
		rows.push([
			getstatus(group["status"]),
			'<a href="./?do=group.info&groupId='+group["id"]+'">'+group["title"]+'</a>',
			MakePermissionRadioButton(group,'None',0,true)
                        +MakePermissionRadioButton(group,'View',1)
                        +MakePermissionRadioButton(group,'Copy',2)
                        +MakePermissionRadioButton(group,'edit',3)
                        <!--{if $needRptLvl}-->,
                         MakeReportLvlRadioButton(group,'None',0,true)
                        +MakeReportLvlRadioButton(group,'View',1)
                        +MakeReportLvlRadioButton(group,'Export',2)
                        +MakeReportLvlRadioButton(group,'Geo Export',3)
                        <!--{/if}-->
		]);
	});
	$('#listGroup').dataTable().fnAddData(rows);
	rearmToolTips();
	$('#listGroup .permission input').change(function(event){
		var target = $(event.target);
		var id = target.attr('name').substring(1);
		var level = target.val();
		addToUpdate("group",'permissions', id, level);
	});
        $('#listGroup .reporting input').change(function(event){
		var target = $(event.target);
		var id = target.attr('name').substring(1).split('_').shift();
		var level = target.val();
		addToUpdate("group",'reporting', id, level);
	});
}
function getstatus(status){
	if(status == 1)return '<span style="color:#00CC00;" title="Approved. You are in this group.">A</span>';
	if(status == 2)return '<span style="color:#0033CC;" title="Pending. You may accept or deny.">P</span>';
	if(status == 3)return '<span style="color:#0033CC;" title="Pending. Waiting for moderator approval.">P</span>';
	if(status == 4)return '<span style="color:#CC0000;" title="Denied. You have not been accepted.">D</span>';
	if(status == 5)return '<span style="color:#00CC00;" title="Moderator. You are the moderator of this group.">M</span>';
	return '&nbsp;';
}
</script>