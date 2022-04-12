<script type="text/javascript">
function check_form(formdata) {
	if (!formdata.elements['password1'].value) { return false; }
	if (!formdata.elements['password2'].value) { return false; }
	if (formdata.elements['password1'].value != formdata.elements['password2'].value) {
		alert('Your two password entries did not match.\nPlease enter the same password into both boxes and try again.');
		return false;
	}
	return true;
}
</script>
<!--{if $loggedIn }--><script>window.location.replace("./?do=project.list");</script><!--{/if}-->
<form id="form1" name="form1" method="post" action="." target="_top" onSubmit="return check_form(this);">
	<div id="login" >
		<h3>Data Management Interface</h3>
		<div id="loginWarning" class="warning"><span class="header">Notice:</span><hr/>
			<div style="padding:5px 0;">This will change your accounts password.</div>
		</div>
		<table class="textblock">
			<tr>
				<td class="textblockmid">
					<table>
						<tr>
							<td style="text-align:right;">New Password:</td>
							<td><input type="text" name="password1" id="password1" style="width: 1.8in;" value=""/></td>
						</tr>
						<tr>
							<td style="text-align:right;">Confirm Password:</td>
							<td><input type="text" name="password2" id="password2" style="width: 1.8in;" value=""/></td>
						</tr>
						<tr>
							<td colspan="2"><input name="submit" type="submit" class="submit" id="Login" value="Change Password" style="width:100%;"/></td>
						</tr>
					</table>
					<!--<input name="sandbox" type="hidden" value="<?php echo $_SERVER['QUERY_STRING']; ?>" />-->
					<input name="do" type="hidden" value="account.forgotpassword4" />
					<input name="uid" type="hidden" value="<!--{$uid}-->" />
					<input name="hash" type="hidden" value="<!--{$hash}-->" />
				</td>
			</tr>
		</table>
	</div>
</form>