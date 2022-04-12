<!--{$subnav}-->
<form action="." method="post" onSubmit="return check_form(this);">
<input type="hidden" name="do" value="admin.configquotas2"/>
<table>

<!--{foreach from=$accounttypes key=level item=label}-->
  <tr>
  <td>
  <!--{assign var=price value=$world->getAccountPrice($level)}-->
  Annual price for <!--{$label}--> accounts:
  </td>
  <td>
  $ <input type="text" name="accountprice_<!--{$level}-->" style="width:0.5in;" value="<!--{$price}-->"/> per year<br/>
  </td>
  </tr>
<!--{/foreach}-->

<tr><td colspan="2"><br/></td></tr>

<tr>
  <td>Default disk space for new accounts:</td>
  <td>
    &nbsp; <input type="text" name="diskquota" value="<!--{$config.diskquota|string_format:'%d'}-->" style="width:0.5in;"/> MB (megabytes)
  </td>
</tr>
<tr>
  <td>Annual price for additional disk space:</td>
  <td>
    $<input type="text" name="storagepergb" style="width:0.5in;" value="<!--{$config.storagepergb|string_format:'%.2f'}-->"/> per GB (gigabyte) per year
  </td>
</tr>



</table>
<p><input type="submit" name="submit" value="save changes"/>
</form>

<script type="text/javascript">
function check_form(formdata) {
   return confirm('Really save changes?');
}
</script>
