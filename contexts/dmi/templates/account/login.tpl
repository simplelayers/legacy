<tr id='workarea'>
<td>

<form id="login_form" name="form1" method="post" action="<!-{$baseURL}-->/account/login" target="_top">
	<div id="login" >
		<h3>Data Management Interface</h3>
		<!--{if $message != ''}-->
		<div id="loginWarning" class="warning">
			<span class="header"><!--{$messageHeader}--></span><hr/>
			<div style="padding:5px 0;"><!--{$message}--></div>
		</div>
		<input name="force" type="hidden" value="1" />	 
		<!--{/if}-->
		<table class="textblock">
			<tr>
				<td class="textblockmid">
					<table>
						<tr>
							<td style="text-align:right;">Username:</td>
							<td><input type="text" name="username" id="username" style="width: 1.8in;" value="<!--{if isset($username)}--><!--{$username}--><!--{/if}-->"/></td>
						</tr>
						<tr>
							<td style="text-align:right;">Password:</td>
							<td><input type="password" name="password" id="password" style="width: 1.8in;" /></td>
						</tr>
						<tr>
							<td colspan="2"><input name="Login" type="submit" class="submit" id="Login" value="Log In" style="width:100%;"/></td>
						</tr>
					</table>
					<input name="action" type="hidden" value="handle_login" />
					<input name="state" type="hidden" value="<!--{$state}-->" />	
								
				</td>
			</tr>
		</table>
	</div>
</form>
</td>
</tr>