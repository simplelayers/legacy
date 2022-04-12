<!--{$subnav}-->
<div id="listDiv">
	<!--{foreach from=$source item=table}-->
	<p class="title"><!--{$table.name}--></p>
	<table id="<!--{$table.id}-->" class="list bordered" style="width:100%;">
		<thead>
			<tr>
				<!--{foreach from=$table.head item=head}-->
					<th><!--{$head}--></th>
				<!--{/foreach}-->
			</tr>
		</thead>
		<tbody>
			<!--{foreach from=$table.data item=row}-->
				<tr>
					<!--{foreach from=$row item=cell}--><td><!--{$cell}--></td><!--{/foreach}-->
				</tr>
			<!--{/foreach}-->
		</tbody>
	</table>
	<!--{/foreach}-->
</div>
<script>
$(function(){
	$('.list').dataTable({
		"bPaginate": false,
		"bFilter": true,
		"bInfo": false,
		"bAutoWidth": true,
		"sDom": '<"filterNav"f>lipt',
		"bStateSave": true,
		"aaSorting": [[ 2, "asc" ]],
		"oLanguage": {
			"sEmptyTable": "No logs to display.",
			"sZeroRecords": "No matching logs found."
		}
	});
});
</script>