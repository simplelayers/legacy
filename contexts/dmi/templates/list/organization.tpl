<table id="list" class="bordered" style="width:100%;">
<thead>
<tr>
	<!--{if isset($select) && $select}--><th style="width:36px;"></th><!--{/if}-->
	<th style="min-width:200px;">Name</th>
	<th style="min-width:100px;">Short</th>
	<th style="min-width:200px;">Owner</th>
	<th style="">Description</th>
</tr>
</thead>
<tbody>
</tbody>
</table>
<script>
var listOfOrgs = {};
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
		<!--{if isset($select) && $select}-->{ "sClass": "select" },<!--{/if}-->
      { "sClass": "org" },
      { "sClass": "short" },
      { "sClass": "owner" },
      { "sClass": "description" }
	  ]
    });
	$.expr[":"].econtains = function(obj, index, meta, stack){return (obj.textContent || obj.innerText || $(obj).text() || "").toLowerCase() == meta[3].toLowerCase();}
});
function rebuildList(type){
	$('#list').dataTable().fnClearTable();
	var rows = new Array();
	$.each(listOfLayers.view, function(i, org) {
		rows.push([
			<!--{if isset($select) && $select}-->'<input type="radio" name="id" value="'+org["id"]+'"/>',<!--{/if}-->
			'<a style="font-weight:bold;" href=".?do=admin.organization.edit1&id='+org["id"]+'">'+org["name"]+'</a>',
			'<a style="font-weight:bold;" href=".?do=organization.info&id='+org["id"]+'">'+org["short"]+'</a>',
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+org["owner"]+'">'+org["realname"]+" ("+org["username"]+')</a>',
			org["description"]
			]);
	});
	$('#list').dataTable().fnAddData(rows);
	rearmToolTips();
}
	$(function() {
		queue = $('#list').jsonQueue();
		queue.nextQueue("./?do=wapi.organization.views&format=json", function(jsonData, context) {
			listOfLayers = jsonData;
			rebuildList();
		},
		function(jsonData){});
		
	});
</script>