<div id="subnav">
	<div id="titleRow">
		<div id="objectData" class="title">New Form</div>
	</div>
	<div style="float:right;">&nbsp;</div>
	<div id="navRow">
		<div id="selector"></div>
	<div class="clear"></div>
	</div>
</div>
</div>
<div style="left:0;right:0;padding:5px 10px 0 10px;">
<form action="." method="post" enctype="multipart/form-data" >
	<div style="float:right;"><input type="submit" name="submit" value="continue" /></div>
	<input type="hidden" name="do" value="forms.new2"/>
	Name: <input type="text" id="name" name="name" />
	<!--{include file='list/layer.tpl'}-->
	<script>
	$(function() {
		if( $('#subnav') !== null) {$('#subnav').parent().css({margin:0,padding:0});}
		$(".filterNav").prependTo("#navRow");
	});
	</script>
	<div style="float:right;"><input type="submit" name="submit" value="continue" /></div>
</form>