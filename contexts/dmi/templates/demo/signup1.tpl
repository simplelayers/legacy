<script type="text/javascript">
function checkFields(formdata) {
   var error = '';
   if (formdata.elements['signup_email2'].value != formdata.elements['signup_email'].value) { error = 'Please enter the same email into both email boxes.\nThis is to make sure you entered it correctly.'; }
   if (formdata.elements['signup_password2'].value != formdata.elements['signup_password'].value) { error = 'Please enter the same password into both password boxes.\nThis is to make sure you entered it correctly.'; }
   if (!formdata.elements['signup_password'].value) { error = 'You need to enter a password.'; }
   if (!formdata.elements['signup_password2'].value) { error = 'You need to enter a password.'; }
   if (!formdata.elements['signup_email'].value) { error = 'You need to enter your email address.'; }
   if (!formdata.elements['signup_username'].value) { error = 'You need to enter a choice of username.'; }
   if (error) {
      alert(error);
      return false;
   }
   return true;
}
function toggleCreditCardSection(accounttype) {
   var prices = new Array();
   prices[<!--{$smarty.const.AccountTypes::GPS}-->] = '<!--{$accountprices[$smarty.const.AccountTypes::GPS]}-->';
   prices[<!--{$smarty.const.AccountTypes::GOLD}-->] = '<!--{$accountprices[$smarty.const.AccountTypes::GOLD]}-->';
   prices[<!--{$smarty.const.AccountTypes::PLATINUM}-->] = '<!--{$accountprices[$smarty.const.AccountTypes::PLATINUM]}-->';
   var newprice = prices[accounttype];

   var visibility = (newprice == 'Free') ? 'none' : 'block';
   document.getElementById('creditcardsection').style.display = visibility;
}
</script>


<center>
<div class="greybox">
<form action="." method="post" onSubmit="return checkFields(this);">
<input type="hidden" name="do" value="demo.signup2" />

<p class="title">Sign up for an account</p>

<p><!--{$signup_header_message}--></p>

<div style="margin-left:1in;">

<table>
<tr>
   <td>Choose username:</td>
   <td><input type="text" name="signup_username" style="width:200px;" maxlength="20" value="<!--{$smarty.request.signup_username}-->"><br/><span class="small">up to 20 lowercase letters and numbers</span></td>
</tr>
<tr>
   <td>Choose password:</td>
   <td><input type="password" name="signup_password" style="width:200px;" maxlength="50" value="<!--{$smarty.request.signup_password}-->"></td>
</tr>
<tr>
   <td>Confirm password:</td>
   <td><input type="password" name="signup_password2" style="width:200px;" maxlength="50" value="<!--{$smarty.request.signup_password2}-->"></td>
</tr>
<tr>
   <td>Your email address:</td>
   <td><input type="text" name="signup_email" style="width:200px;" maxlength="50" value="<!--{$smarty.request.signup_email}-->"></td>
</tr>
<tr>
   <td>Confirm email address:</td>
   <td><input type="text" name="signup_email2" style="width:200px;" maxlength="50" value="<!--{$smarty.request.signup_email2}-->"></td>
</tr>
<tr>
   <td>Where did you hear about us?</td>
   <td><input type="text" name="signup_referred" style="width:200px;" maxlength="50" value="<!--{$smarty.request.signup_referred}-->"></td>
</tr>
<tr>
   <td>Enter the confirmation code:</td>
   <td><input type="text" name="captcha" style="width:200px;" maxlength="50"><br/><img src="<!--{$captchaimageurl}-->"/></td>
</tr>
</table>


<p>
Choose account level:<br/>
 &nbsp;&nbsp;&nbsp; <input type="radio" class="nopad" name="signup_accounttype" value="<!--{$smarty.const.AccountTypes::GPS}-->" onClick="toggleCreditCardSection(<!--{$smarty.const.AccountTypes::GPS}-->)" <!--{if $smarty.request.signup_accounttype == $smarty.const.AccountTypes::GPS }-->checked<!--{/if}-->>Basic: <!--{$accountprices[$smarty.const.AccountTypes::GPS]}--><br/>
 &nbsp;&nbsp;&nbsp; <input type="radio" class="nopad" name="signup_accounttype" value="<!--{$smarty.const.AccountTypes::GOLD}-->" onClick="toggleCreditCardSection(<!--{$smarty.const.AccountTypes::GOLD}-->)" <!--{if $smarty.request.signup_accounttype == $smarty.const.AccountTypes::GOLD }-->checked<!--{/if}-->>PRO: <!--{$accountprices[$smarty.const.AccountTypes::GOLD]}--><br/>
 &nbsp;&nbsp;&nbsp; <input type="radio" class="nopad" name="signup_accounttype" value="<!--{$smarty.const.AccountTypes::PLATINUM}-->" onClick="toggleCreditCardSection(<!--{$smarty.const.AccountTypes::PLATINUM}-->)" <!--{if $smarty.request.signup_accounttype == $smarty.const.AccountTypes::PLATINUM }-->checked<!--{/if}-->>PRO: <!--{$accountprices[$smarty.const.AccountTypes::PLATINUM]}--><br/>
<div id="creditcardsection" style="margin-left:1in;font-size:70%;">
   Cardholder's name:<br/><input type="text" name="cc_cardholder" size="40" maxlength="50" value="<!--{$smarty.request.cc_cardholder}-->" style="margin-left:0.5in;"/><br/>
   Card number:<br/><input type="text" name="cc_number" size="40" maxlength="50" value="<!--{$smarty.request.cc_number}-->" style="margin-left:0.5in;"/><br/>
   Card exp. date:<br/>
   <span style="margin-left:0.5in;">
   <!--{html_select_date display_days=no year_empty='' month_empty='' end_year='+15' }-->
   </span>
   <br/>
   <!--{$signup_discount_message}-->
</div>
</p>


<br/>
<table class="bordered">
<tr>
   <th colspan="3" style="background-color:#777777;color:#000000;">Account Level Comparison</th>
<tr>
<tr>
   <th style="background-color:#999999;color:#000000;">Basic</th>
   <th style="background-color:#999999;color:#000000;">GPS</th>
   <th style="background-color:#999999;color:#000000;">PRO</th>
<tr>
<tr>
   <td>Create unlimited projects<br/>Digitize data onscreen</td>
   <td>Create unlimited projects<br/>Digitize data onscreen<br/>Interchange data with GPS units</td>

   <td>Create unlimited projects<br/>Digitize data onscreen<br/>Interchange data with GPS units<br/>Interchange data with GIS software<br/>Data layer sharing<br/>Analysis tools</td>
<tr>
</table>


<p><input type="submit" name="submit" value="Join the mapping community!"></p>
</form>

</div> <!-- end of indentation -->


<p style="font-size:60%;"><b>Enterprise licensing also available:</b> CartoGraph is available as a licensed server system, allowing you total<br/>
control over branding, access, data, and account administration. Please inquire at <a href="mailto:info@cartograph.com">info@cartograph.com</a></p>

<script type="text/javascript">toggleCreditCardSection(<!--{$smarty.request.signup_accounttype}-->);</script>

<p><br/></p>
</div>
</center>
