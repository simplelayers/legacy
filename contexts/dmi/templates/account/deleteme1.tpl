<p class="title">Delete your account</p>
<p class="alert">NOTE: There is no way to un-delete your account <br/>or to recover the data in your account after it is deleted. <br/>Make sure you have backups before you do this!</p>

<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['confirm'].checked) { return false; }
   if (!formdata.elements['oldpassword'].value) { return false; }
   if (!confirm('One last chance:\nClick OK to delete your account.\nClick Cancel to keep your account.')) { return false; }
   return true;
}
</script>


<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="account.deleteme2"/>

<p class="small"><input type="checkbox" name="confirm" value="1"/> I understand that I am about to permanently delete <br/>my account, and that there is no way to un-delete <br/>or recover the information in my account.</p>

<p>Enter your password:<br/><input type="password" name="oldpassword" style="width:2in;"/></p>

<p><input type="submit" name="submit" value="delete your account" style="width:2in;" /></p>

</form>
