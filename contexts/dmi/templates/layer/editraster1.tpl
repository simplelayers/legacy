<div style="float:right;display:inline-block;background:#EEE;border:1px solid #DDD;padding:6px 0 6px 6px;">

  <table>
	<tr>
		<td>Disk Space Used:</td>
		<td><!--{Units::bytesToString($layer->diskusage,2)}--></td>
	</tr>
  </table>
  <img class="thumbnail" style="padding-top:10px;border-top:1px solid #DDD;" id="thumbnail" src=".?do=download.imagethumbnail&id=<!--{$layer->id}-->" onClick="document.getElementById('thumbnail').src += '&force=true'" />
  <br/>
  <!--{$layer->getExtentPretty()}-->
</div>

<form action="." method="post" onSubmit="return check_form(this);" enctype="multipart/form-data">
<input type="hidden" name="do" value="layer.editraster2"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>
<div style="float:left;display:inline-block;">
<table style="width:100%;">
<tr>
	<td style="width:150px;">Name:</td>
	<td style="width:420px;"><input type="text" name="name" style="width:3in;" value="<!--{$layer->name}-->"/></td>
</tr>
<tr>
	<td>Description:</td>
	<td><textarea name="description" style="width:100%;height:1in;"><!--{$layer->description}--></textarea></td>
</tr>
<tr>
	<td>Tags:</td>
	<td><textarea name="tags" style="width:100%;height:1in;"><!--{$layer->tags}--></textarea></td>
</tr>
<!--{if $isowner}-->
<tr><td>Change Owner:</td><td><a href="#" onclick="$('.toggle').toggle();"><span class="toggle" id="show">Show List</span><span class="toggle">Hide List</span></a><span class="toggle"><!--{include file='list/contact.tpl'}--><a href="#" onclick="$('input[name=\'contact\']').removeAttr('checked');">Clear Selection</a></span></td></tr>
<!--{/if}-->
<tr>
<td colspan="2"><input type="submit" name="submit" value="save changes" style="width:2in;"/><td>
</tr>
</table>
</div>
<div style="clear:both;"></div>
</form>

<script type="text/javascript">
$('#show').toggle();
$('.toggle').toggle();
function check_form(formdata) {
   if (!formdata.elements['id'].value) return false;
   if (!formdata.elements['name'].value) return false;

   dot=/[^ _A-Za-z0-9]/;
   basename = formdata.elements['name'].value;
   checkname = basename.match(dot);
   if(checkname){
       	alert('Layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
       	return false;
   }
   if (formdata.elements['source'].value) alert('File uploads can take a while. Please be patient.');
		return true;
}
function reminder() {
   alert('It will take a moment to prepare your download.\nPlease be patient.');
}
$(function(){
	$('textarea[name="tags"]').tagsInput({
		width: '100%'
	});
});
</script>
