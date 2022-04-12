<!--{$subnav}-->
<form action="." method="post">
<input type="hidden" name="do" value="admin.organization.new2"/>
<table style="width:100%;">
	<tr><th style="width:220px;"></th><th></th></tr>
	<tr><td>Name:</td><td><input type="text" name="name" /></td></tr>
	<tr><td>Short Name (Permalink):</td><td><input type="text" name="short"  maxlength="32"/></td></tr>
	<tr><td>Owner Account:</td><td>New User: <input type="radio" name="contact" id="newBox" value="new" checked="checked"/> Username: <input type="text" name="account_username" style="width:2in;" maxlength="16"/> Password: <input type="text" name="account_password" style="width:2in;" maxlength="16"/></td></tr>
	<tr><td></td><td><div id="selector"></div><!--{include file='list/contact.tpl'}--></td></tr>
	<tr><td></td><td><input name="submit" type="submit" /></tr>
</table>
</form>
