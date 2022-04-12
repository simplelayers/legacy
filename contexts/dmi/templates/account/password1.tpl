<p class="title">Change your password</p>

<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['oldpassword'].value) { return false; }
   if (!formdata.elements['newpassword1'].value) { return false; }
   if (!formdata.elements['newpassword2'].value) { return false; }
   if (formdata.elements['newpassword1'].value != formdata.elements['newpassword2'].value) {
      alert('The new passwords entered in the boxes did not match. Please try again.');
      return false;
   }
   return true;
}
</script>

<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="account.password2" />

<table>
  <tr><td>Old password:</td><td><input type="password" maxlength="30" name="oldpassword" style="width:2in;" /></td></tr>
  <tr><td>New password:</td><td><input type="password" maxlength="30" name="newpassword1" style="width:2in;" /></td></tr>
  <tr><td>New password again:</td><td><input type="password" maxlength="30" name="newpassword2" style="width:2in;" /></td></tr>
  <tr><td>&nbsp;</td><td><br/><input type="submit" name="submit" value="change password" style="width:2in;" /></td></tr>
</table>

</form>
