<style>
.layerlist-wrapper {
    width: calc(100vw);
    height: calc(100vh - 18.575em);
    display: block;
    overflow: auto;
    padding-left:.5em;
}
.perm-list thead th {
        font-size: 1.25rem;
        padding-left:.5em;
    }
    
</style>
<div class="layerlist-wrapper">
<table id="list" class="bordered layerlist" style="width:100%;">
<thead>
<tr>
	<!--{if isset($select) && $select}--><th style="width:36px;"></th><!--{/if}-->
	<th class="type-col" style="width:36px;">Type</th>
	<th class="layer-col" style="width:16px;"><span><img src="media/icons/book.png"/></span></th>
	<th style="width:200px;">Layer</th>
	<th style="width:120px;">Owner</th>
	<th style="">Description</th>
	<th style="width:54px;">Global Sharing</th>
</tr>
</thead>
<tbody>
</tbody>
</table>
</div>
<script>

<!--{$layerTypeEnum}-->
<!--{$geomTypeEnum}-->
function getGeom(layer){
	$('#navRow').removeClass('hidden');
	
	if(layer["type"] == 2){return '<span style="display:none;">raster</span><img src="media/icons_16x16/raster.png" title="raster"/>';}
	else if(layer["type"] == 3){return '<span style="display:none;">wms</span><img src="media/icons_16x16/wms2.png" title="wms"/>';}
	else if(layer["type"] == 5){
		return '<span style="display:none;">relational</span></span><img title="relational" src="<!--{$baseURL}-->wapi/media/icons/action:get/target:icon/icon:Content-36/size:16/category:Icons" />';
		}
	else if(layer["type"] == 6){return '<span style="display:none;">collection</span><img src="media/icons_16x16/collection.png" title="collection"/>';}
	else if(layer["type"] == 8){return '<span style="display:none;">relatable</span><img src="<!--{$baseURL}-->wapi/media/icons/action:get/target:icon/icon:Programing-51/size:16/category:Icons" title="relatable"/>';}
	else{
		if(typeof geomTypes != 'undefined') {
		geomType = geomTypes[layer['geom']];
			switch(geomType) {
				case 'point':
					return '<span style="display:none;">point</span><img src="media/icons_16x16/point.png" title="point"/>';
				case 'polygon':
					return '<span style="display:none;">poly</span><img src="media/icons_16x16/poly.png" title="polygon"/>';
				case 'line': 
					return '<span style="display:none;">line</span><img src="media/icons_16x16/line.png" title="line"/>';
					
			}
		}
	}
	return '<img src="media/icons_16x16/unknown.png" title="unknown"/>';
}
function getShare(layer, mine){
	var level = layer["sharelevel"];
	if(layer["owner"] == "<!--{$user->id}-->" && mine === false){ level = 3;}
	if(layer["permission"] > level && !mine){ level = layer["permission"] }
	var isAdmin = <!--{if $isAdmin}-->true<!--{else}-->false<!--{/if}-->;
	if(isAdmin) return 'edit';
	
	if(level == 0){return 'none';}
	else if(level == 1){return 'read';}
	else if(level == 2){return 'copy';}
	else if(level == 3){return 'edit';}
	return 'UNKNOWN';
}
function getBookmark(layer){
	var marked = layer["bookmarked"];
	if(marked == 'true') return '<span style="display:none;">d</span><img title="Unbookmark this layer." src="'+deleteImg+'" class="'+layer["id"]+'"/>';
	return '<span style="display:none;">a</span><img title="Bookmark this layer." src="'+addImg+'" class="'+layer["id"]+'"/>';
}
var listOfLayers = {};
$(function() {

var selData = {'type':'project'};
	var selData = {};
	<!--{if $default}-->
		selData.default='<!--{$default}-->';
		selData.groupId=<!--{$groupId}-->;
	<!--{/if}-->
	

	<!--{if isset($selector) === false}-->$('#selector').dataSelector(selData).bind("update", function(e, data){listOfLayers = data;rebuildList();}).bind("loading", function(e){$('#list').dataTable().fnClearTable();});<!--{/if}-->
	$dt = $('#list').dataTable({
        "bPaginate": false,
        "bFilter": true,
        "bInfo": false,
		"bAutoWidth": false,
		"sDom": '',
		"bStateSave": true,
		"aaSorting": [[ 3, "asc" ]],
		"aoColumns": [ 
		<!--{if isset($select) && $select}-->{ "sClass": "select" },<!--{/if}-->
		{ "sClass": "type" },
		{ "sClass": "bookmarked" },
      { "sClass": "layer" },
      { "sClass": "owner" },
      { "sClass": "description" },
      { "sClass": "sharing" }
	  ]
    });
    $dt.fnFilter('');
      $(".filterNav input").val('');
     $(".filterNav input").bind("input",function(){
    	var val = $(".filterNav input").val();
    	$dt.fnFilter(val); 
    });
   
	$.expr[":"].econtains = function(obj, index, meta, stack){return (obj.textContent || obj.innerText || $(obj).text() || "").toLowerCase() == meta[3].toLowerCase();}
});
function rebuildList(type){
	$('#navRow').removeClass('hidden');
	$('#list').dataTable().fnClearTable();
	var rows = new Array();
	var mine = false;
	$('th.sharing').text('Permissions');
	if($('.sel option:selected').text() == "My Layers"){ mine = true; $('th.sharing').text('Global Sharing');}
	if(listOfLayers.view) { 
	$.each(listOfLayers.view, function(i, layer) {
		rows.push([
			<!--{if isset($select) && $select}-->'<input type="radio" name="id" value="'+layer["id"]+'"/>',<!--{/if}-->
			getGeom(layer),
			getBookmark(layer),
			'<a style="font-weight:bold;" href=".?do=layer.edit1&id='+layer["id"]+'">'+layer["name"]+'</a>',
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+layer["owner"]+'">'+layer["owner_name"]+'</a>',
			layer["description"],
			getShare(layer, mine)
			]);
	});
	};
	$('#list').dataTable().fnAddData(rows);
	$('td.bookmarked img').click(bookmark);
	$('.sharing:econtains(none)').css({ "background-color": "#FF9999" });
	$('.sharing:econtains(read)').css({ "background-color": "#66FF66" });
	$('.sharing:econtains(copy)').css({ "background-color": "#99CCFF" });
	$('.sharing:econtains(edit)').css({ "background-color": "#FFFF66" });
	rearmToolTips();
}
var addImg = "media/icons/book_add.png";
var deleteImg = "media/icons/book_delete.png";
function bookmark(e){
	var tar = $(e.target);
	if(tar.attr('src') == addImg){
		$.post('./?do=layer.addbookmark&id='+tar.attr("class"));
		tar.attr('src', deleteImg);
		tar.parent().children('span').html('d');
	}else{
		$.post('./?do=layer.removebookmark&id='+tar.attr("class"));
		tar.attr('src', addImg);
		tar.parent().children('span').html('a');
		if($('.sel').val() == 2){
			var aTrs = $('#list').dataTable().fnGetNodes();
			for ( var i=0 ; i<aTrs.length ; i++ ){
				if ( $(aTrs[i]).html() == $(e.target).parent().parent().html()){
					$('#list').dataTable().fnDeleteRow( aTrs[i] );
				}
			}
		}
	}
}
$(function() {
	$('.tundra').toggleClass('hidden-overflow',true);
	$('.contentarea').toggleClass('padless',true);
})
</script>
