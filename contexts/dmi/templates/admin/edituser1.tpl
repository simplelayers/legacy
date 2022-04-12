<!--{$subnav}-->

<p>Last login: <i><!--{$lastlogin.datetime}--></i> from <i><!--{$lastlogin.ipaddress}--></i></p>

<form action="." method="post">
<input type="hidden" name="do" value="admin.edituser2"/>
<input type="hidden" name="id" value="<!--{$person->id}-->"/>

<p>Name:<br/><input type="text" name="account_realname" style="width:3in;" maxlength="50" value="<!--{$person->realname|escape:'html'}-->"/></p>
<p>Email:<br/><input type="text" name="account_email" style="width:3in;" maxlength="50" value="<!--{$person->email|escape:'html'}-->"/></p>
<p>Password (if being changed):<br/><input type="text" name="account_password" style="width:3in;" maxlength="50"/></p>
<p>User's self-description:<br/><textarea name="account_description" style="width:6in;height:1in;"><!--{$person->description|escape:'html'}--></textarea></p>

<p><br/></p>
<p>
Account type:<br/>
<!--{html_options options=$accounttypes name=account_accounttype selected=$person->accounttype}--><br/>
Expiration:<br/>
<!--{assign var=expdate value=$person->expirationdate}-->
<!--{$expdate}-->
<!--{if !$expdate}--><!--{assign var=expdate value=null}--><!--{/if}-->
<!--{html_select_date prefix=expiration_ start_year='-1' end_year='+10' year_empty='never' month_empty='never' day_empty='never' time=$expdate}-->
</p>

<p><br/></p>
<p>Administrative comments, brief and lengthy (not visible to anybody):<br/>
<input type="text" name="account_comment1" style="width:6in;" maxlength="100" value="<!--{$person->comment1|escape:'html'|truncate:97:'...'}-->"/><br/>
<textarea name="account_comment2" style="width:6in;height:1in;"><!--{$person->comment2|escape:'html'}--></textarea>
</p>

<p><input type="submit" name="submit" value="save changes" style="width:2in;"/></p>
</form>


<p><br/><br/></p>
<form action="." method="post" onSubmit="return confirm('Really delete this user?\nThere is no way to undo this!');">
<input type="hidden" name="do" value="admin.deleteuser"/>
<input type="hidden" name="id" value="<!--{$person->id}-->"/>

<p><input type="submit" name="submit" value="delete this account" style="width:2in;"/></p>
</form>
