<!--{$subnav}-->
<div>
<!--{if ($org->seatsLeft(1) > 0 || $org->seatsLeft(2) > 0 || $org->seatsLeft(3) > 0)}-->
<form action="./?do=organization.invite" method="post">
	<input type="hidden" name="action" value="add" />
	<input type="hidden" name="id" value="<!--{$org->id}-->" />
	<select name="seat">
		<!--{if $org->seatsLeft(1) > 0}--><option value="1">Staff</option><!--{/if}-->
		<!--{if $org->seatsLeft(2) > 0}--><option value="2">Executive</option><!--{/if}-->
		<!--{if $org->seatsLeft(3) > 0}--><option value="3">Power User</option><!--{/if}-->
	</select>
	<input type="submit" name="submit" value="Create New Code" />
</form>
<!--{/if}-->
<table id="list" class="bordered" style="width:100%;">
<thead>
<tr>
	<th>Seat</th>
	<th>Code</th>
	<th>Join Link</th>
	<th>Email</th>
	<th style="width:16px"></th>
</tr>
</thead>
<tbody>
<!--{foreach from=$invites item=row}-->
<tr>
<td>
<!--{if $row.seat == 1}-->Staff<!--{/if}-->
<!--{if $row.seat == 2}-->Executive<!--{/if}-->
<!--{if $row.seat == 3}-->Power User<!--{/if}-->
</td>
<td><input type="text" class="select" readonly="readonly" value="<!--{$row.code}-->" /></td><td><input type="text" class="select" readonly="readonly" value="https://www.cartograph.com/~doug/cartograph/?do=organization.join&code=<!--{$row.code}-->" /></td>
<td>
<!--{if !is_null($row.email)}-->
	<!--{$row.email}-->
<!--{else}-->
	<form action="./?do=organization.invite" method="post">
		<input type="hidden" name="action" value="email" />
		<input type="hidden" name="id" value="<!--{$org->id}-->" />
		<input type="hidden" name="row" value="<!--{$row.id}-->" />
		<input type="text" name="email" />
		<input type="submit" name="submit" value="Send Email" />
	</form>
<!--{/if}-->
</td>
<td><a href="./?do=organization.invite&action=delete&id=<!--{$org->id}-->&row=<!--{$row.id}-->"><img src="media/icons/delete.png" alt="Delete"/></a></td>
</tr>
<!--{/foreach}-->
</tbody>
</table>
<script>
$(function(){
	$('#list').dataTable({
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false,
		"bAutoWidth": false,
		"sDom": '<"filterNav"f>lipt',
		"bStateSave": true,
		"aaSorting": [[ 3, "asc" ]],
		"aoColumns": [
			{ "sClass": "seat" },
			{ "sClass": "code" },
			{ "sClass": "join" },
			{ "sClass": "email" },
			{ "sClass": "delete" }
		],
		"oLanguage": {
			"sEmptyTable": "No invite codes to display.",
			"sZeroRecords": "No matching invite codes found."
		}
    });
	
	$("input:text.select").click(function() { $(this).select(); } );
});
</script>
<div style="clear:both;"></div>