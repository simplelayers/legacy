<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="vector.create2"/>

<p>Geometry type: <!--{html_options name=type options=$geomtypes selected=$selected}--></p>

<p>
Layer name:<br/><input type="text" name="name" style="width:2in;" maxlength="25" />
</p>

<p><input type="submit" name="submit" value="create new layer"/></p>
</form>

<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['name'].value) { return false; }
   return true;
}
</script>
