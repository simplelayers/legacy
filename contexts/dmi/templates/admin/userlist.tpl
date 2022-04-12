<!--{$subnav}-->

<p>Last user login: <i><!--{$lastlogin.username}--></i> at <i><!--{$lastlogin.datetime}--></i> from <i><!--{$lastlogin.ipaddress}--></i></p>

<table class="bordered" style="width:10in;">
<tr>
  <th><a href=".?do=admin.userlist&sort=id&desc=<!--{$sortdesc}-->">ID</a></th>
  <th><a href=".?do=admin.userlist&sort=username&desc=<!--{$sortdesc}-->">Username</a></th>
  <th><a href=".?do=admin.userlist&sort=realname&desc=<!--{$sortdesc}-->">Name</a></th>
  <th><a href=".?do=admin.userlist&sort=expirationdate&desc=<!--{$sortdesc}-->">Expiration</a></th>
  <th><a href=".?do=admin.userlist&sort=accounttype&desc=<!--{$sortdesc}-->">Type</a></th>
  <th><a href=".?do=admin.userlist&sort=comment1&desc=<!--{$sortdesc}-->">Comment</a></th>
</tr>
<!--{section name=i loop=$people}-->
	<!--{cycle values="color,altcolor" assign=class}-->
	<!--{assign var=accounttype value=$people[i]->accounttype}-->
	<!--{assign var=accounttype value=$accounttypes[$accounttype]}-->
	<!--{assign var=expired value=$people[i]->id}-->
	<!--{if isset($expired[$people[i]->id])}-->
		<!--{assign var=expired value=$expiredaccounts[$expired]}-->
		<!--{assign var=class value=expired}-->
	<!--{/if}-->
	<tr>
	  <td style="width:0.5in;" class="<!--{$class}-->"><!--{$people[i]->id}--></td>
	  <td style="width:2in;" class="<!--{$class}-->"><a href=".?do=admin.edituser1&id=<!--{$people[i]->id}-->"><!--{$people[i]->username|truncate:30:"..."}--></a></td>
	  <td style="width:2in;" class="<!--{$class}-->"><!--{$people[i]->realname|truncate:30:"..."}--></td>
	  <td style="width:1in;" class="<!--{$class}-->"><!--{$people[i]->expirationdate}--></td>
	  <td style="width:1in;" class="<!--{$class}-->"><!--{$accounttype}--></td>
	  <td style="width:3.5in;" class="<!--{$class}-->"><!--{$people[i]->comment1|truncate:80:"..."}--></td>
	</tr>
<!--{/section}-->
</table>

