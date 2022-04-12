<p class="title">Add time to your membership</p>
<!--{if $user->daysUntilExpiration() > 0}-->
<p>Your membership expires on <!--{$user->expirationdate}--></p>
<!--{else}-->
<p class="alert">Your membership has expired. You will need to add time to use your account.</p>
<!--{/if}-->

<p style="font-weight:bold;">The price of extending your membership is based on your account type,<br/>and also includes any extra services with your account, such as increased storage space.</p>

<script type="text/javascript">
function check_form(formdata) {
   if (! <!--{$free}-->) { return true; }
   if (!formdata.elements['cc_cardholder'].value) { return false; }
   if (!formdata.elements['cc_number'].value) { return false; }
   if (!formdata.elements['Date_Year'].selectedIndex) { return false; }
   if (!formdata.elements['Date_Month'].selectedIndex) { return false; }
}
</script>

<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="account.addtime2"/>
<p>Add how much time to your membership?<br/><!--{html_options options=$options name=years }--></p>

<p>
<!--{if ! $free}-->
Cardholder's name:<br/><input type="text" name="cc_cardholder" style="width:3in;" maxlength="50" value=""/><br/>
Card number:<br/><input type="text" name="cc_number" style="width:3in;" maxlength="50" value=""/><br/>
Card expiration date:<br/><!--{html_select_date display_days=no year_empty='' month_empty='' end_year='+15' }-->
<!--{/if}-->
</p>

<p><br/><input type="submit" name="submit" value="add time to my membership" style="width:3in;"/></p>
</form>
