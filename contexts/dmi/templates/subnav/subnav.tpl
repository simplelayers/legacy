<div id="subnav">
	<div id="titleRow">
		<div id="lastUpdated" <!--{if isset($rightbar)}-->style="font-style:normal;"<!--{$rightbar}--><!--{/if}-->><!--{if isset($lastUpdated)}-->last updated <!--{$lastUpdated|prettydate}--><!--{else}-->&nbsp;<!--{/if}--></div>
		<div id="objectData" class="title"><!--{if isset($edit)}--><!--{$edit}--><!--{/if}--><!--{$objectData}--> &mdash; <span id="ownerData"><!--{$ownerData}--></span></div>
		<!--{if isset($communitymap) && $communitymap && $user->community}--><div class="title" style="text-align:center;color:#ff0000;">Note: Community Accounts cannot embed, share or generate WMS from maps</div><!--{/if}-->
	</div>
	<div id="navRow">
	
		<!--{counter assign=tableNumber}-->
		<!--{foreach  from=$navArray item=elements key=category}-->
		<table class="subnavTable"<!--{if $tableNumber > 1}--> style="padding-left:12px;border-left:1px solid #ddd;"<!--{/if}-->><tr>
			<th class="subnavHeader" colspan="<!--{$elements|@count}-->"><!--{$category}--></th>
		</tr><tr>
			<!--{foreach from=$elements item=do key=title}-->
				<!--{if $title=="Map" || $title=="Delete" || $title=="Rollback" || $title=="Backup"}-->
					<td><a href="<!--{eval var=$do}-->"<!--{if $title=="Delete"}--> style="color:#921600;"<!--{/if}-->><!--{$title}--></a></td>
				<!--{else}-->
					<!--{if $disArray[$category][$title] && $user->community}-->
					<td><a href="#" style="color:#999;"><!--{$title}--></a></td>
					<!--{else}-->
					<!--{eval var=$do assign='todo'}-->
					<td><a href="<!--{$baseURL}--><!--{if substr($todo,0,1) eq '/'}--><!--{substr($todo,1)}--><!--{else}-->/?do=<!--{eval var=$do}--><!--{/if}-->"><!--{$title}--></a></td>
					<!--{/if}-->
				<!--{/if}-->
			<!--{/foreach}-->
		</tr>
		</table>
		<!--{counter}-->
		<!--{/foreach}-->
		
	</div>
</div>

</td></tr>
<tr><td>
<div class="mainContent">