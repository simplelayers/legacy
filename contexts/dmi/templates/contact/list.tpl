<div class="filterNav">
Filter: <input id="filter" type="text"></input>
</div>
<div id="lastUpdated"><form action="./?do=contact.add" method="post"><input type="text" id="name" name="name"/><input type="submit" value="Bookmark User" /></form></div>	
<div style="left:0;right:0;padding:5px 10px 0 10px;">
<div id='contact_selector' ></div>
<!--{include file='list/contact.tpl'}-->
<script>
$(function() {
	if( $('#subnav') !== null) {$('#subnav').parent().css({margin:0,padding:0});}
	$(".filterNav").prependTo("#navRow");
	$("#contact_selector").prependTo("#navRow");
	$("#lastUpdated").addClass('hidden');
});
</script>