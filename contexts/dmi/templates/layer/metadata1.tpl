<!--{$subnav}-->
<!--{if $hasMetadata}-->
<p class="title">Export Metadata</p>
<a href="./?do=download.metadata&id=<!--{$layer->id}-->">Export as metadata XML</a>
<p class="title">&nbsp;</p>
<!--{/if}-->
<!--{$metadata}-->
<!--{if $user->diskUsageRemaining() <= 0 }-->
  <p class="alert">
  Your account is already at the maximum allowed storage.<br/>
  To import more data, either <a href=".?do=layer.list">delete some layers</a> or <a href=".?do=account.addstorage">purchase more storage space</a>
  </p>
<!--{else}-->

<form action="." method="post" enctype="multipart/form-data" onSubmit="return check_meta(this);">

<input type="hidden" name="do" value="layer.metadata2">
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>


<p>
You can upload a metadata XML file and attach them to a layer here.</p>
<table>
	<tr>
	<td colspan="2">
	XML File:<br/><input type="file" name="source" size="40" />
	</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="submit" name="submit" value="Import Metadata" />
		</td>
	</tr>
</table>
</form>

<!--{if $hasMetadata }-->
<p class="title">Delete Metadata</p>
<form action="." method="post" onSubmit="if(confirm('Are you sure you want to delete this layer\'s metadata?\nThere is no way to un-delete or recover a layer\'s metadata once it has been deleted.\n\nClick OK to delete this layer\'s metadata.\nClick Cancel to NOT delete this layer\'s metadata.')){return true;}else{return false;}">
<input type="hidden" name="do" value="layer.deletemetadata">
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>
<input type="submit" name="clear" value="Delete Metadata"/>
</form>
<!--{/if}-->
<script>
function check_meta(formdata) {
	var error = "";
	if (!formdata.elements['source'].value) { 
		error = error+'Please select a XML file.\r\n';   
	}else{
		if (!formdata.elements['source'].value.match(/([^\\:\/]+)\.xml$/i)) {
			error = error+'The upload must be a XML file.\r\n';
		}
	}
	if(error == ""){
		return true;
	}else{
		alert(error);
		return false;
	}
}
var alphanumeric = /^[a-zA-Z0-9_@]+$/;
$(function(){
	$("img.edit").disableSelection().click(edit);
	$("img.add").disableSelection().click(add);
});
function accept(e){
	var parent = $(e.target).parent();
	var text = parent.children('input:text').val();
	var key = parent.is('legend');
	if(key){key='key';if(!alphanumeric.test(text)){alert('Must be alphanumeric a value. No spaces. Use camelCase.');return false;}}else{key='value';}
	var trail = parent.children('.trail').text();
	parent.html(''+text+'<img class="edit" src="media/icons/page_edit.png"/>');
	parent.children('.edit').click(edit);
	$.ajax('./?do=wapi.layer.metadata&crud=u&id=<!--{$layer->id}-->&position='+trail+'&type='+key+'&new='+text);
	return false;
}
function del(e){
	if(!confirm("Are you sure you want to delete?")){return false;}
	var parent = $(e.target).parent();
	var key = parent.is('legend');
	if(key){key='key';}else{key='value';}
	var trail = parent.children('.trail').text();
	var keyval = parent.is('legend');
	var grandparent;
	if(keyval){grandparent = parent.parent().parent(); parent.parent().remove();}else{parent.html('<img class="edit" src="media/icons/page_edit.png"/>');parent.children('.edit').click(edit);}
	$.ajax('./?do=wapi.layer.metadata&crud=d&id=<!--{$layer->id}-->&position='+trail+'&type='+key);
	if(keyval && grandparent.children().length <= 2){
		grandparent.children('img').remove();
		grandparent.append('<span><img class="edit" src="media/icons/page_edit.png"/></span>');
		grandparent.children().children('.edit').click(edit);
	}
}
function makeTrail(p){
	var t = "";
	p.parents('fieldset').children('legend').each(function(){t = t+','+$(this).text();});
	return t;
}
function edit(e){
	var parent = $(e.target).parent();
	var text = parent.text();
	parent.children('.trail').remove();
	var trail = $('<span class="trail" style="display:none;"></span>');
	trail.append(makeTrail(parent));
	parent.html('<input class="prev" type="hidden" value="'+text+'"/><input type="text" value="'+text+'"/><br/><img class="delete" src="media/icons/delete.png"/><img src="media/icons/empty.png"/><img class="cancel" src="media/icons/delete2.png"/>'+(!parent.is('legend') ? '<img class="add" src="media/icons/add.png"/>' : '')+'<img class="accept" src="media/icons/accept.png"/>');
	parent.append(trail);
	var input = parent.children('input:text');
	input.focus().val(input.val());
	parent.children('.accept').click(accept);
	parent.children('.add').click(add);
	parent.children('.delete').click(del);
	parent.children('.cancel').click(cancel);
	return false;
}
function cancel(e){
	var parent = $(e.target).parent();
	var input = parent.children('input:text');
	var old = parent.children('input:hidden');
	input.val(old.val());
	accept(e);
}
function add(e){
	var parent = $(e.target).parent();
	if(parent.is('fieldset')){
		var trail = $('<span class="trail" style="display:none;"></span>');
		trail.append(","+parent.children('legend').text()+makeTrail(parent));
		var fieldset = $('<fieldset></fieldset>');
		var legend = $('<legend><input type="text" value=""/><br/><img class="delete" src="media/icons/delete.png"/><img src="media/icons/empty.png"/><img class="cancel" src="media/icons/delete2.png"/><img class="accept" src="media/icons/accept.png"/></legend>');
		parent.append(fieldset);
		fieldset.append(legend);
		fieldset.append($('<span><img class="edit" src="media/icons/page_edit.png"/></span>'));
		legend.append(trail);
		legend.children('input').focus();
		legend.children('.accept').click(acceptAdd);
		legend.children('.delete, .cancel').click(function(){$(this).parent().parent().remove();});
		parent.append($(this));
	}else{
		var text = parent.children('input:text').val();
		if(!alphanumeric.test(text)){alert('Must be alphanumeric a value. No spaces. Use camelCase.');return false;}
		var key='value';
		var trail = parent.children('.trail').text();
		var grandparent = parent.parent();
		grandparent.children('.trail').remove();
		parent.remove();
		var fieldset = $('<fieldset><legend>'+text+'<img class="edit" src="media/icons/page_edit.png"/></legend><span><img class="edit" src="media/icons/page_edit.png"/></span></fieldset><img class="add" src="media/icons/add.png"/>');
		grandparent.append(fieldset);
		grandparent.children().children().children('.edit').click(edit);
		grandparent.children('.add').click(add);
		$.ajax('./?do=wapi.layer.metadata&crud=c&id=<!--{$layer->id}-->&position='+trail+'&type='+key+'&new='+text);
	}
	return false;
}
function acceptAdd(e){
	var parent = $(e.target).parent();
	var text = parent.children('input').val();
	if(!alphanumeric.test(text)){alert('Must be alphanumeric a value. No spaces. Use camelCase.');return false;}
	var key='key';
	var trail = parent.children('.trail').text();
	parent.html(''+text+'<img class="edit" src="media/icons/page_edit.png"/>');
	parent.parent().children().children('.edit').click(edit);
	$.ajax('./?do=wapi.layer.metadata&crud=c&id=<!--{$layer->id}-->&position='+trail+'&type='+key+'&new='+text);
	return false;
}
</script>

<!--{/if}-->