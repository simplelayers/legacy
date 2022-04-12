<!--{$subnav}-->
<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="admin.configidentification2"/>

<p>
Title:<br/>
<span class="small">The title for your website in the browser's title bar.</span><br/>
<input type="text" name="title" value="<!--{$config.title|escape:'htmlall'}-->" style="width:3in;"/>
</p>

<p>
Contact name:<br/>
<span class="small">The name of the contact person for this website.<br/>This is used, for example, in the email when someone signs up for a new account.</span><br/>
<input type="text" name="admin_name" value="<!--{$config.admin_name|escape:'htmlall'}-->" style="width:3in;"/>
</p>

<p>
Contact email:<br/>
<span class="small">The email address of the contact person for this website.<br/>This is used, for example, in the email when someone signs up for a new account.</span><br/>
<input type="text" name="admin_email" value="<!--{$config.admin_email|escape:'htmlall'}-->" style="width:3in;"/>
</p>

<p>
USA ePay source key:<br/>
<span class="small">The source key for this SimpleLayers installation to connect to USA ePay.<br/>This is used for processing credit cards for signups, upgrades, etc.</span><br/>
<input type="text" name="creditcardkey" value="<!--{$config.creditcardkey|escape:'htmlall'}-->" style="width:3in;"/>
</p>

<p>
Alternate login page:<br/>
<span class="small">If this is not set, then the login page will be displayed as usual.<br/>If it is set, then the login page will instead forward to the specified URL as an alternate login page.</span><br/>
<input type="text" name="alternateloginpage" value="<!--{$config.alternateloginpage|escape:'htmlall'}-->" style="width:3in;"/>
</p>

<p><br/><input type="submit" name="submit" value="save changes"/></p>
</form>

<script type="text/javascript">
function check_form(formdata) {
   return confirm('Really save changes?');
}
</script>
