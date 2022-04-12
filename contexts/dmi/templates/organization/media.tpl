<!--{$subnav}-->
<div>
<form action="./?do=organization.media" method="post" enctype="multipart/form-data">
<table>
	<tr><td>Media Type:</td><td><input id="imageOption" type="radio" name="type" value="image" checked> Image
	<input id="textOption" type="radio" name="type" value="text"> Text</td></tr>
	<tr><td>Media:</td><td><input type="hidden" name="id" value="<!--{$org->id}-->" />
	<input id="textSelect" style="display:none;" type="text" name="text" id="text"/>
	<input id="imageSelect" type="file" name="file" id="file"/></td></tr>
	<tr><td>Media Name:</td><td><!--{html_options name=names output=$names values=$names}--></td></tr>
	<tr><td><input type="submit" name="submit" value="Upload" /></td><td></td></tr>
</table>
</form>
<script>
$('#textOption').change(function(){
	$('#textSelect').css('display', 'inline');
	$('#imageSelect').css('display', 'none');
});
$('#imageOption').change(function(){
	$('#textSelect').css('display', 'none');
	$('#imageSelect').css('display', 'inline');
});
</script>
<table id="list" class="bordered" style="width:100%;">
<thead>
<tr>
	<th>Name</th>
	<th>Media</th>
	<th style="width:16px"></th>
</tr>
</thead>
<tbody>
<!--{foreach from=$org->getMedia() item=row}-->
<tr>
<td><!--{$row.name}--></td>
<td><!--{if $row.type != "plain/text"}--><img src="./?do=organization.media&id=<!--{$org->id}-->&get=<!--{$row.name}-->" style="max-height:250px;max-width:600px;"/><!--{else}--><!--{$row.link}--><!--{/if}--></td>
<td><a href="./?do=organization.media&id=<!--{$org->id}-->&delete=<!--{$row.name}-->"><img src="media/icons/delete.png" /></a></td>
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
			{ "sClass": "name" },
			{ "sClass": "media" },
			{ "sClass": "delete" }
		],
		"oLanguage": {
			"sEmptyTable": "No media to display.",
			"sZeroRecords": "No matching media found."
		}
    });
	$('[name="names"]').css('min-width','60px').editableSelect({
		bg_iframe: true, case_sensitive: false
	}).css('min-width','60px');
});
</script>
<div style="clear:both;"></div>