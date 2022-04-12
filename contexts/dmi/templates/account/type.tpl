<p class="title">Account type and membership expiration</p>
<p>
Account type: <!--{$accounttype}-->
<!--{if $user->accounttype < $maxAccount}-->
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href=".?do=account.upgrade1">upgrade account</a>
<!--{/if}-->
</p>

<!--{if $user->expirationdate === false}-->
<p>Your account does not expire.</p>
<!--{/if}-->

<p>
<!--{if $user->daysUntilExpiration() === false }-->
Your account does not expire.
<!--{elseif $user->daysUntilExpiration() > 0 }-->
Expiration: <!--{$user->expirationdate}--> (<!--{$user->daysUntilExpiration()}--> days left)
<!--{else}-->
<span class="alert">Your account has expired.</span>
<!--{/if}-->
</p>

<p>
Database usage: <!--{Units::bytesToString($user->diskUsageDB(), 2)}--><br/>
Raster file usage: <!--{Units::bytesToString($user->diskUsageFiles(), 2)}--><br/>
Disk space allowed: <!--{Units::bytesToString($user->diskUsageAllowed(), 2)}--><br/>
Disk space remaining: <!--{Units::bytesToString($user->diskUsageRemaining(), 2)}-->
</p>

<table class="bordered" style="margin-left:0.5in;">
<tr>
  <th colspan="2">Disk usage detail</th>
</tr>
<!--{section loop=$layers name=i}-->
<!--{assign var=layer value=$layers[i]}-->
<!--{cycle values="color,altcolor" assign=class}-->
<tr>
  <td class="<!--{$class}-->"><!--{$layer->name|escape:'html'}--></td>
  <td style="text-align:right;" class="<!--{$class}-->">
  <!--{if is_null($layer->diskusage)}-->not found<!--{else}--><!--{Units::bytesToString($layer->diskusage, 2)}--><!--{/if}--></td>
</tr>
<!--{/section}-->
</table>
