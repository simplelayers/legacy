<!--{$subnav}-->

<form action="." method="post">
<input type="hidden" name="do" value="admin.accountlog"/>
Show the last <!--{html_options values=$howmany_choices output=$howmany_choices selected=$howmany name=howmany}--> entries. <input type="submit" value="go"/>
</form>


<table class="bordered" style="width:10in;">
<tr>
  <th>When</th>
  <th>Type</th>
  <th>Account</th>
  <th>Description</th>
</tr>
<!--{section name=i loop=$entries}-->
<!--{cycle values="color,altcolor" assign=class}-->
<tr>
  <td style="width:1in;" class="<!--{$class}-->"><!--{$entries[i].datetime|escape:'html'}--></td>
  <td style="width:1in;" class="<!--{$class}-->"><!--{$entries[i].type|escape:'html'}--></td>
  <td style="width:2in;" class="<!--{$class}-->"><!--{$entries[i].account|escape:'html'}--></td>
  <td style="width:7in;" class="<!--{$class}-->"><!--{$entries[i].description|escape:'html'}--></td>
</tr>
<!--{/section}-->
</table>
