<width:3in;p class="title">Purchase additional storage</p>

<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['cc_cardholder'].value) { return false; }
   if (!formdata.elements['cc_number'].value) { return false; }
   if (!formdata.elements['Date_Year'].selectedIndex) { return false; }
   if (!formdata.elements['Date_Month'].selectedIndex) { return false; }
}
</script>

<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="account.addstorage2"/>

<p>
Your membership expires on <!--{$user->expirationdate}-->, in <!--{$user->daysUntilExpiration() }--> days.<br/>
The choices listed below are to upgrade your storage for the rest of your membership.
</p>

<!--{html_options options=$options name=storage }-->

<p>
Cardholder's name:<br/><input type="text" name="cc_cardholder" maxlength="50" style="width:3in;" value=""/><br/>
Card number:<br/><input type="text" name="cc_number" maxlength="50" style="width:3in;" value=""/><br/>
Card expiration date:<br/><!--{html_select_date display_days=no year_empty='' month_empty='' end_year='+15' }-->
</p>

<p><br/><input type="submit" name="submit" value="upgrade your account's storage" style="width:3in;"/></p>
</form>
