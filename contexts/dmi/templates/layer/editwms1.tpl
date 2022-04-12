<script type="text/javascript" src="media/prototype.js"></script>
<script type="text/javascript">
// Fix for wierdness where padding for top and left were altered after adding prototype.js script
$("contentWrapper").setStyle('padding-left:0px;padding-top:0px;');

function check_form(formdata) {
   if (!formdata.elements['name'].value) return false;
   dot=/[^ _A-Za-z0-9]/;
   basename = formdata.elements['name'].value;
   checkname = basename.match(dot);
   if(checkname){
		alert('Layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
		return false;
   }
return true;
}

</script>
<!--{$subnav}-->


<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="layer.editwms2"/>
<input type="hidden" name="id" value="<!--{$layer->id}-->"/>
<input type="hidden" name="bboxparam" value="" />
<p>
Name:<br/>
<input type="text" name="name" style="width:3in;" value="<!--{$layer->name}-->"/>
</p>
<p>
Category:<br/>
<select name="category" style="width:3in;">
<!--{html_options options=$categories selected=$layer->category}-->
</select>
</p>

<p>
Description:<br/>
<textarea name="description" style="width:8in;height:1in;"><!--{$layer->description}--></textarea>
</p>

<p>
Keywords:<br/>
<textarea name="keywords" style="width:8in;height:1in;"><!--{$layer->keywords}--></textarea>
</p>

<p>
WMS URL:<br/>
<!--{$layer->url}-->
</p>

<p><input id="saveButton" type="submit" name="submit" value="Save Changes" style="width:2in;"/></p>
</form>

<!-- part 2: a form for giving this layer away to someone else -->
<!--{if $canGiveAway}-->
<p><br/><br/><br/><br/></p>
<p class="title">Give this layer to someone else</p>
<p>This tool allows you to transfer the layer to another user's ownership. The layer will disappear from your list and will appear on theirs. When ownership is transferred, the layer will no longer be included into any projects, and all access controls (sharing) will be reset to defaults.</p>
<form action="." method="post" onSubmit="return confirm('Are you sure you want to transfer away ownership of this layer?\nOnce you give it away, you cannot regain control of it unless the recipient gives it back.\n\nClick OK to give away this layer.\nClick Cancel to NOT give away this layer.');">
<input type="hidden" name="do" value="layer.giveaway"/>
<input type="hidden" name="layerid" value="<!--{$layer->id}-->"/>
Transfer ownership to: <!--{html_options name=recipientid options=$friends}--> <input type="submit" name="submit" value="transfer ownership" style="width:2in;"/>
</form>
<!--{/if}-->
