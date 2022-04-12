<script type='text/javascript'>
	function forceThumbnail() {
		var url = document.getElementById('thumbnail').src;
		
		if(url.indexOf('force=')< 0) {
		
			document.getElementById('thumbnail').src = url+'&force='+new Date().getTime();
		} else {
			var segs=url.split('&');
			segs.pop();
			segs.push('force='+new Date().getTime());
			document.getElementById('thumbnail').src = segs.join('&');
		}
	}
	
</script>

<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="layer.editrelational2"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>

 
<table>
<tbody>
<tr>
	<td>Layer Features</td><td><!--{$recordcount}--></td>
</tr>
<tr >
		<td>Disk Space Used:</td> 
		<td><!--{Units::bytesToString($layer->diskusage,2)}--></td>		
	</tr>
 
 </tbody>
 </table>

<p>
Name:<br/>
<input type="text" name="name" style="width:3in;" value="<!--{$layer->name}-->"/>
</p>
<p>
Description:<br/>
<textarea name="description" style="width:6in;height:1in;"><!--{$layer->description}--></textarea>
</p>
<p>
Tags:<br/>
<textarea name="tags" style="width:6in;height:1in;"><!--{$layer->tags}--></textarea>
</p>



<p><input class='button' type="submit" name="submit" value="save changes" style="width:2in;"/></p>
</form>

<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['id'].value) return false;
   if (!formdata.elements['name'].value) return false;
   return true;
}
</script>



<script type="text/javascript">
function reminder() {
   alert('It will take a moment to prepare your download.\nPlease be patient.');
}
$(function(){
	$('textarea[name="tags"]').tagsInput({
		width: '6in'
	});
});
</script>
