
<form id="form1" name="form1" method="post" action="." target="_top">
	<div id="login" >
		<h3>Join <!--{$org->name}--></h3>
		<!--{if isset($error)}-->
		<div id="loginWarning" class="warning"><span class="header">Warning:</span><hr/>
			<div style="padding:5px 0;"><!--{$error}--></div>
		</div>
		<!--{/if}-->
		<input name="code" type="hidden" value="<!--{$code}-->" />
		<!--{if $user && $user->id != 0}-->
		<table class="textblock">
			<tr>
				<td class="textblockmid">
					<table>
						<tr>
							<td colspan="2"><input name="join" type="submit" class="submit" id="Login" value="Join <!--{$org->name}-->" style="width:100%;"/></td>
						</tr>
					</table>
					<a href="./?do=wapi.secure_connection&action=logout" >Logout of this account<a/>
					<input name="do" type="hidden" value="organization.join" />
				</td>
			</tr>
		</table>
		<!--{else}-->
		<table class="textblock">
			<tr>
				<td class="textblockmid">
					<table>
						<tr>
							<td style="text-align:right;">Username:</td>
							<td><input type="text" name="account_username" id="username" style="width: 1.8in;" value=""/></td>
						</tr>
						<tr>
							<td style="text-align:right;">Password:</td>
							<td><input type="password" name="account_password" id="password" style="width: 1.8in;" /></td>
						</tr>
						<tr>
							<td style="text-align:right;">Email:</td>
							<td><input type="text" name="account_email" id="email" style="width: 1.8in;" value="<!--{$email}-->"/></td>
						</tr>
						<tr>
							<td colspan="2"><input name="create" type="submit" class="submit" id="Login" value="Join <!--{$org->name}-->" style="width:100%;"/></td>
						</tr>
					</table>
					<a href="./?do=account.login">Login to your account</a>
					<input name="do" type="hidden" value="organization.join" />
				</td>
			</tr>
		</table>
		<!--{/if}-->
	</div>
</form>
