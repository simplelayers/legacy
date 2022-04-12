<!--{if $loggedIn }--><script>window.location.replace("./?do=project.list");</script><!--{/if}-->
<form id="form1" name="form1" method="post" action="." target="_top">
	<div id="login" >
		<h3>Data Management Interface</h3>
		<div id="loginWarning" class="warning"><span class="header">Notice:</span><hr/>
			<div style="padding:5px 0;">A link to change your password will be sent to your email.</div>
		</div>
		<table class="textblock">
			<tr>
				<td class="textblockmid">
					<table>
						<tr>
							<td style="text-align:right;">Username:</td>
							<td><input type="text" name="username" id="username" style="width: 1.8in;" value="<!--{$username}-->"/></td>
						</tr>
						<tr>
							<td colspan="2"><input name="submit" type="submit" class="submit" id="Login" value="Send Recovery Email" style="width:100%;"/></td>
						</tr>
					</table>
					<!--<input name="sandbox" type="hidden" value="<?php echo $_SERVER['QUERY_STRING']; ?>" />-->
					<input name="do" type="hidden" value="account.forgotpassword2" />
				</td>
			</tr>
		</table>
	</div>
</form>