<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="project.create2"/>

<p>
Name for the new map:<br/>
<input type="text" name="name" maxlength="50" style="width:3in;"/>
</p>

<p>Description:<br/>
<textarea name="description" style="width:6in;height:2in;"></textarea>
</p>

<p><input type="submit" name="submit" value="create new map"/></p>

</form>

<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['name'].value) { return false; }
   return true;
}
document.forms[0].elements['name'].focus();
</script>
