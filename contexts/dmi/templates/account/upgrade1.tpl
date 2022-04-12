<p class="title">Upgrade your account</p>

<script type="text/javascript">
function check_form(formdata) {
   if (!formdata.elements['cc_cardholder'].value) { return false; }
   if (!formdata.elements['cc_number'].value) { return false; }
   if (!formdata.elements['Date_Year'].selectedIndex) { return false; }
   if (!formdata.elements['Date_Month'].selectedIndex) { return false; }
}
</script>

<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="account.upgrade2"/>

<p>
Your current account grade is <b><!--{$accounttype}--></b><br/>
Your membership expires on <!--{$user->expirationdate}-->, in <!--{$user->daysUntilExpiration() }--> days.<br/>
<b>The choices listed below are to upgrade your account for the rest of your membership,<br/>including additional services such as extra storage.</b>
</p>

<!--{html_options options=$options name=newlevel }-->

<p>
Cardholder's name:<br/><input type="text" name="cc_cardholder" style="width:3in;" maxlength="50" value=""/><br/>
Card number:<br/><input type="text" name="cc_number" style="width:3in;" maxlength="50" value=""/><br/>
Card expiration date:<br/><!--{html_select_date display_days=no year_empty='' month_empty='' end_year='+15' }-->
</p>

<p><br/><input type="submit" name="submit" value="upgrade my membership" style="width:3in;"/></p>
</form>
