<!--{$subnav}-->

<form action="." method="post">
<input type="hidden" name="do" value="admin.projectlog"/>
Show the last <!--{html_options values=$howmany_choices output=$howmany_choices selected=$howmany name=howmany}--> entries. <input type="submit" value="go"/>
</form>


<table class="bordered" style="width:10in;">
<tr>
  <th>When</th>
  <th>Who accessed</th>
  <th>Map owner</th>
  <th>Map name</th>
  <th>Source / Comment</th>
</tr>
<!--{section name=i loop=$entries}-->
<!--{cycle values="color,altcolor" assign=class}-->
<tr>
  <td style="width:1in;" class="<!--{$class}-->"><!--{$entries[i].datetime|escape:'html'}--></td>
  <td style="width:2in;" class="<!--{$class}-->"><!--{if !is_null($entries[i].account_id)}--><a href="./?do=contact.info&id=<!--{$entries[i].account_id}-->"><!--{$entries[i].account|escape:'html'}--></a><!--{else}--><!--{$entries[i].account|escape:'html'}--><!--{/if}--></td>
  <td style="width:2in;" class="<!--{$class}-->"><!--{if !is_null($entries[i].owner_id)}--><a href="./?do=contact.info&id=<!--{$entries[i].owner_id}-->"><!--{$entries[i].owner|escape:'html'}--></a><!--{else}--><!--{$entries[i].owner|escape:'html'}--><!--{/if}--></td>
  <td style="width:5in;" class="<!--{$class}-->"><!--{if !is_null($entries[i].project_id)}--><a href="./?do=project.edit1&id=<!--{$entries[i].project_id}-->"><!--{$entries[i].project|escape:'html'}--></a><!--{else}--><!--{$entries[i].project|escape:'html'}--><!--{/if}--></td>
  <td style="width:5in;" class="<!--{$class}-->"><!--{$entries[i].comment|escape:'html'}--></td>
</tr>
<!--{/section}-->
</table>
