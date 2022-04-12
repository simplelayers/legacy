<!--{$subnav}-->

<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="admin.adduser2"/>

<p>
Username:<br/><input type="text" name="account_username" style="width:2in;" maxlength="16"/>
</p>
<p>
Password:<br/><input type="text" name="account_password" style="width:2in;" maxlength="16"/>
</p>

<p><input type="submit" name="submit" value="create account" style="width:2in;"/></p>
</form>


<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['account_username'].value) { return false; }
   return true;
}
document.forms[0].elements['account_username'].focus();
</script>
