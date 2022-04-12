<!--{$subnav}-->
<!--{include file='list/invoice.tpl'}-->
<script>
$(function(){
	if( $('#subnav') !== null) {$('#subnav').parent().css({margin:0,padding:0});}
	$(".filterNav").prependTo("#navRow");
});
</script>