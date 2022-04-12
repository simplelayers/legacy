<table id="listContact" class="perm-list bordered" style="width:100%;">
<thead>

<tr>
	<th class="contact-heading username">Username</th>
	<th class="contactheading name" style="">Name</th>
	<th class="contact-heading option-set permissions">Permission</th>
        <!--{if $needRptLvl===true}-->
        <th class="options-heading option-set reporting">Reporting Level</th>        
        <!--{/if}-->
</tr>
</thead>
<tbody >
</tbody>
</table>
<script>
var listOfContacts = {};
$(function() {
$('#navRow').removeClass('hidden');
	$('#selectorContact').dataSelector(
            {
                'type' : 'contact'<!--{if isset($project)}-->, 
                'extend' : '&shareproject=<!--{$project->id}-->'
                <!--{else}-->, 
                'extend' : '&sharelayer=<!--{$layer->id}-->'<!--{/if}-->
            }
        ).bind("update", function(e, data){
            listOfContacts = data;rebuildListContact();}).bind("loading", 
                function(e){
                    $('#listContact').dataTable().fnClearTable();
                });
	$('#listContact').dataTable({
		"bPaginate": false,
		"bFilter": <!--{if isset($filter) && !$filter}-->false<!--{else}-->true<!--{/if}-->,
		"bInfo": false,
		"bAutoWidth": false,
		"sDom": '<"filterNavContact"f>lipt',
		"bStateSave": true,
		"aaSorting": [[ 3, "asc" ]],
		"aoColumns": [
			{ "sClass": "contact-value username" },
			{ "sClass": "contact-value name" },
			{ "sClass": "contact-value option-set permission" }
                        <!--{if $needRptLvl===true}-->,
                        { "sClass": "contact-value option-set reporting" }
                        <!--{/if}-->
                        
		],
		"oLanguage": {
		  "sEmptyTable": "No contacts to display.",
		  "sZeroRecords": "No matching contacts found."
		}
	});
});
function MakeContactPermissionRadioButton(contact,label,permission,isFirst) {
    const firstClass = isFirst ? 'first' : '';
    return `<input class="${firstClass} option-radio" type="radio"  name="c${contact["id"]}" value="${permission}" ${contact["permission"] == permission ? "checked" : ""}><label for="c${contact["id"]}">${label}</label>`
}
function MakeContactReportLvlRadioButton(contact,label,level,isFirst) {
    const firstClass = isFirst ? 'first' : '';
    return `<input class="${firstClass} option-radio" type="radio"  name="c${contact["id"]}_rpt" value="${level}" ${contact["reporting"] == level ? "checked" : ""}><label for="c${contact["id"]}_rpt">${label}</label>`
}
function rebuildListContact(type){
	$('#listContact').dataTable().fnClearTable();
	var rows = new Array();
        if(!listOfContacts.view) {
            return;
        }
        $.each(listOfContacts.view, function(i, contact) {
                rows.push([
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+contact["id"]+'">'+contact["username"]+'</a>',
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+contact["id"]+'">'+contact["realname"]+'</a>',
                         MakeContactPermissionRadioButton(contact,'None',0,true)
                        +MakeContactPermissionRadioButton(contact,'View',1)
                        +MakeContactPermissionRadioButton(contact,'Copy',2)
                        +MakeContactPermissionRadioButton(contact,'edit',3)
                        <!--{if $needRptLvl===true}-->,
                         MakeContactReportLvlRadioButton(contact,'None',0,true)
                        +MakeContactReportLvlRadioButton(contact,'View',1)
                        +MakeContactReportLvlRadioButton(contact,'Export',2)
                        +MakeContactReportLvlRadioButton(contact,'Geo Export',3)
                        <!--{/if}-->
                        
		]);
                
	});
        $('#listContact').dataTable().fnAddData(rows);
	rearmToolTips();
	$('#listContact .permission input').change(function(event){
		var target = $(event.target);
		var id = target.attr('name').substring(1);
		var level = target.val();
		addToUpdate("person","permissions", id, level);
	});
        $('#listContact .reporting input').change(function(event){
		var target = $(event.target);
		var id = target.attr('name').substring(1).split('_').shift();
		var level = target.val();
		addToUpdate("person","reporting", id, level);
	});
}
</script>