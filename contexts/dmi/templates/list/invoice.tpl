<table id="list" class="bordered" style="width:100%;">
<thead>
<tr>
	<!--{if isset($select) && $select}--><th style="width:36px;"></th><!--{/if}-->
	<!--{if !$org }--><th>Organization</th><!--{/if}-->
	<th>Created</th>
	<th>Paid</th>
	<th>Invoice Number</th>
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
		"aaSorting": [[ 4, "asc" ]],
		"aoColumns": [ 
		<!--{if isset($select) && $select}-->{ "sClass": "select" },<!--{/if}-->
		<!--{if !$org }-->{ "sClass": "org" },<!--{/if}-->
      { "sClass": "created" },
      { "sClass": "paid" },
      { "sClass": "number" }
	  ]
    });
	$.expr[":"].econtains = function(obj, index, meta, stack){return (obj.textContent || obj.innerText || $(obj).text() || "").toLowerCase() == meta[3].toLowerCase();}
});
function rebuildList(type){
	$('#list').dataTable().fnClearTable();
	var rows = new Array();
	$.each(listOfLayers.view, function(i, inv) {
		rows.push([
			<!--{if isset($select) && $select}-->'<input type="radio" name="id" value="'+inv["id"]+'"/>',<!--{/if}-->
			<!--{if !$org }-->'<a style="font-weight:bold;" href=".?do=organization.info&id='+inv["org_id"]+'">'+inv["org_name"]+'</a>',<!--{/if}-->
			'<a style="font-weight:bold;" href=".?do=organization.invoice.view&id='+inv["id"]+'">'+inv["created"]+'</a>',
			'<a style="font-weight:bold;" href=".?do=organization.invoice.view&id='+inv["id"]+'">'+(inv["paid"] == null ? 'Unpaid' : inv["paid"])+'</a>',
			'<a style="font-weight:bold;" href=".?do=organization.invoice.view&id='+inv["id"]+'">#'+inv["id"]+'</a>',
			inv["description"]
			]);
	});
	$('#list').dataTable().fnAddData(rows);
	rearmToolTips();
}
	$(function() {
		queue = $('#list').jsonQueue();
		queue.nextQueue("./?do=wapi.organization.invoice.views&format=json&org=<!--{if !$org }-->false<!--{else}--><!--{$org->id}--><!--{/if}-->", function(jsonData, context) {
			listOfLayers = jsonData;
			rebuildList();
		},
		function(jsonData){});
		
	});
</script>