<div class="filterNav input-group">
    <label class="form-label">Filter:</label><input class="form-control" id="filter" type="text"></input>
</div>
<!--<div class='workarea'>-->
<!--{include file='list/project.tpl'}-->
<script>


$(function(){
	if( $('#subnav') !== null) {$('#subnav').parent().css({margin:0,padding:0});}
	$(".filterNav").appendTo("#navRow");
});
</script>