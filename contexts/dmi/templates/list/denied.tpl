<table id="list3" class="bordered" style="width:100%;">
<thead>
<tr>
	<th style="width:16px;"><span><img src="media/icons/user.png"/></span></th>
	<th style="width:16px;"><span><img src="media/icons/email.png"/></span></th>
	<th style="width:200px;">Username</th>
	<th style="">Name</th>
	<th style="">Action</th>
</tr>
</thead>
<tbody>
</tbody>
</table>
<script>
var listOfContacts3 = {};
$(function() {
$('#navRow').removeClass('hidden');
	$('#list3').dataTable({
		"bPaginate": false,
		"bFilter": <!--{if isset($filter) && !$filter}-->false<!--{else}-->true<!--{/if}-->,
		"bInfo": false,
		"bAutoWidth": false,
		"sDom": '<"filterNav"f>lipt',
		"bStateSave": true,
		"aaSorting": [[ 3, "asc" ]],
		"aoColumns": [
			{ "sClass": "added3" },
			{ "sClass": "mail" },
			{ "sClass": "username" },
			{ "sClass": "name" }
			,{ "sClass": "action" }
		],
		"oLanguage": {
		  "sEmptyTable": "No contacts to display.",
		  "sZeroRecords": "No matching contacts found."
		}
	});
});
function rebuildList3(type){
	$('#list3').dataTable().fnClearTable();
	var rows = new Array();
	$.each(listOfContacts3.view, function(i, contact) {
		rows.push([
			getAdded(contact),
			'<a style="font-weight:bold;" href=".?do=contact.email1&id='+contact["id"]+'"><img src="media/icons/email.png"/></a>',
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+contact["id"]+'">'+contact["username"]+'</a>',
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+contact["id"]+'">'+contact["realname"]+'</a>'
			,getGroupStatus(contact)
		]);
	});
	$('#list3').dataTable().fnAddData(rows);
	$('td.added3 img').click(added3);
	rearmToolTips();
}
function added3(e){
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
			var aTrs = $('#list3').dataTable().fnGetNodes();
			for ( var i=0 ; i<aTrs.length ; i++ ){
				if ( $(aTrs[i]).html() == $(e.target).parent().parent().html()){
					$('#list3').dataTable().fnDeleteRow( aTrs[i] );
				}
			}
		}
	}
}
</script>