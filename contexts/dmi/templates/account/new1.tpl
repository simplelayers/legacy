<form action="." method="post" autocomplete="off">
<div id="login" >
		<h3>Welcome to Simple Layers</h3>
		<!--{if isset($issue) && $issue}-->
		<div id="loginWarning" class="warning"><span class="header">A problem occurred:</span><hr/>
			<div style="padding:5px 0;"><!--{$issue}--></div>
		</div>
		<!--{/if}-->
		<table class="textblock">
			<tr>
				<td class="textblockmid">
					<table>
						<tr>
							<td style="text-align:right;">Username:</td>
							<td><input type="text" name="new_account_username" maxlength="16" id="username" style="width: 1.8in;" value="<!--{if isset($username)}--><!--{$username}--><!--{/if}-->"  autocomplete="off"/></td>
						</tr>
						<tr>
							<td style="text-align:right;">Password:</td>
							<td><input type="password" name="new_account_password" maxlength="16" id="password" style="width: 1.8in;" autocomplete="off"/></td>
						</tr>
						<tr>
							<td colspan="2"><input name="submit" type="submit" class="submit" id="Login" value="Create Account" style="width:100%;"/></td>
						</tr>
					</table><a style="font-size:0.8em;float:left;" href="<!--{$baseURL}-->/account/login" target="_self">Returning User?</a>
					<input type="hidden" name="do" value="account.new2"/>
					<input type="text" name="username" maxlength="16" id="fusername" style="display:none;" value=""  autocomplete="off"/>
					<input type="text" name="password" maxlength="16" id="fpassword" style="display:none;" value=""  autocomplete="off"/>
					<input type="text" name="email" maxlength="16" id="femail" style="display:none;" value=""  autocomplete="off"/>
					<input type="text" name="javascript" maxlength="16" id="javascriptcheck" style="display:none;" value=""  autocomplete="off"/>
				</td>
			</tr>
		</table>
	</div>
</form>
<script>
	$('#javascriptcheck').val('nobots');
</script>