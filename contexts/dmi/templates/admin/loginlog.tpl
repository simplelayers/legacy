<!--{$subnav}-->

<form action="." method="post">
<input type="hidden" name="do" value="admin.loginlog"/>
Show the last <!--{html_options values=$howmany_choices output=$howmany_choices selected=$howmany name=howmany}--> entries. <input type="submit" value="go"/>
</form>


<table class="bordered" style="width:10in;">
<tr>
  <th>When</th>
  <th>Userame</th>
  <th>IP Address</th>
</tr>
<!--{section name=i loop=$entries}-->
<!--{cycle values="color,altcolor" assign=class}-->
<tr>
  <td style="width:1in;" class="<!--{$class}-->"><!--{$entries[i].datetime|escape:'html'}--></td>
  <td style="width:2in;" class="<!--{$class}-->"><!--{$entries[i].username|escape:'html'}--></td>
  <td style="width:2in;" class="<!--{$class}-->"><!--{$entries[i].ipaddress|escape:'html'}--></td>
</tr>
<!--{/section}-->
</table>
