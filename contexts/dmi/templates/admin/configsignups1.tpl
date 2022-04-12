<!--{$subnav}-->
<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="admin.configsignups2"/>


<p>
Signup header<br/>
On the signup page, this message will be displayed at the very top, above the signup form.<br/>
<textarea name="signup_header_message" style="width:6in;height:1in;"><!--{$config.signup_header_message|escape:'htmlall'}--></textarea>
</p>

<p>
Discount or billing information<br/>
On the signup page, this message will be displayed with the credit card entry form.<br/>
This may be a good plaace to mention discounts, privacy policies, etc.<br/>
<textarea name="signup_discount_message" style="width:6in;height:1in;"><!--{$config.signup_discount_message|escape:'htmlall'}--></textarea>
</p>

<p>
Emailed welcome message<br/>
When new users sign up, they will receive the following welcome message via email.<br/>
<textarea name="signup_thankyoumessage" style="width:6in;height:1in;"><!--{$config.signup_thankyoumessage|escape:'htmlall'}--></textarea>
</p>


<p><input type="submit" name="submit" value="save changes"/>
</form>

<script type="text/javascript">
function check_form(formdata) {
   return confirm('Really save changes?');
}
</script>
