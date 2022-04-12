<!--{$subnav}-->
<!--{include file='list/layer.tpl'}-->
<script>
	$(function() {
		if( $('#subnav') !== null) {$('#subnav').parent().css({margin:0,padding:0});}
		$(".filterNav").prependTo("#navRow");
		queue = $('#list').jsonQueue();
		queue.nextQueue("./?do=wapi.views&type=group&object=layer&id=<!--{$group}-->&format=json", function(jsonData, context) {
			listOfLayers = jsonData;
			rebuildList();
		},
		function(jsonData){});
		
	});
</script>