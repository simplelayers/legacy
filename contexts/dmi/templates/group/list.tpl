<div class="filterNav">
Filter: <input id="filter" type="text" >
</div>
<div class='maincontent'>
<script>
$(function() {
	if( $('#subnav') !== null) {$('#subnav').parent().css({margin:0,padding:0});}
	$(".filterNav").prependTo("#navRow");
});
</script>
<!--{include file='list/group.tpl'}-->
</div>
