<style>
.projectlist-wrapper {
    width: calc(100vw);
        height: calc(100vh - 17.575em);
    display: block;
    overflow: auto;
    padding-left:.5em;
}
tr.table-instructions,
tr.table-instructions td {
	border-top-style: hidden !important;
	border-right-style: hidden !important;
	border-left-style: hidden !important;
	
}
a:not([href]):not([class]) {
  color: #005392 !important;
  text-decoration: underline !important;
  cursor:pointer !important;
}
</style>
<div class="projectlist-wrapper">
<table id="list" class="bordered" style="width:100%;">
<thead>
<tr class="table-instructions">
<td colspan="5">Click a map name to launch in SL V viewer, ctrl+click to open legacy viewer</td>
</tr>
<tr>
	<!--{if $showBookmarks}--><th style="width:16px;"><span><img src="media/icons/book.png"/></span></th><!--{/if}-->
	<th style="width:200px;">Map</th>
	<!--{if $showEdit}--><th style="width:54px;">Edit/Info</th><!--{/if}-->
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

function getShareLevel(project, mine){
	var isAdmin = <!--{if $isAdmin}-->true<!--{else}-->false<!--{/if}-->;
	if(isAdmin) return 3;
	var level = 0;
        console.log('Is mine ',mine ? 'yes' : 'no');
	if(mine){
		if(project["allowlpa"] == "t"){level = 1;}
		if(project["private"] == "f"){level = 2;}
	}else{
		level = 1;
		if(project["owner"] == "<!--{$user->id}-->"){ level = 3;}
		if(project["permission"] > level){ level = project["permission"] }
	}
        console.log(project.name,project.id,level);
	return level;
}
<!--{if $showBookmarks}-->
function getBookmark(project){
	var marked = project["bookmarked"];
	if(marked == 'true') return '<span style="display:none;">d</span><img title="Unbookmark this project." src="'+deleteImg+'" class="'+project["id"]+'"/>';
	return '<span style="display:none;">a</span><img title="Bookmark this project." src="'+addImg+'" class="'+project["id"]+'"/>';
}
<!--{/if}-->
function getShare(project, mine){
	

	var level = getShareLevel(project, mine);
		var isAdmin = <!--{if $isAdmin}-->true<!--{else}-->false<!--{/if}-->;
	if(isAdmin) return 'edit';
	if(mine){
		if(level <= 0){return 'private';}
		if(level == 1){return 'unlisted';}
		if(level == 2){return 'public';}
	}else{
		if(level <= 0){return 'none';}
		if(level == 1){return 'read';}
		else if(level == 2){return 'copy';}
		else if(level == 3){return 'edit';}
	}
	return 'UNKNOWN';
}
$(function() {
$('#navRow').removeClass('hidden');
	var selData = {'type':'project'};
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
		"bStateSave": true,
		"bDom":"",
		"aaSorting": [[ 2, "asc" ]],
		"aoColumns": [ <!--{if $showBookmarks}--> { "sClass": "bookmarked" },<!--{/if}-->
      { "sClass": "map" },
      <!--{if $showEdit}-->{ "sClass": "edit" },<!--{/if}-->
      { "sClass": "owner" },
      { "sClass": "description" },
      { "sClass": "sharing" }
    ],
	"oLanguage": {
      "sEmptyTable": "No maps to display.",
	  "sZeroRecords": "No matching maps found."
    }
    });
    $(".filterNav input").val('');
    $dt.fnFilter('');
    
     $(".filterNav input").bind("input",function(){
    	var val = $(".filterNav input").val();
    	$dt.fnFilter(val); 
    });
   // $(".filterNav input").bind("mouseup",function(){$dt.fnFilter($(".filterNav input").val());
    $('#list_filter').addClass('hidden');
    $.extend({postJSON: function( url, data, callback) {return jQuery.post(url, data, callback, "json");}});
	$.expr[":"].econtains = function(obj, index, meta, stack){return (obj.textContent || obj.innerText || $(obj).text() || "").toLowerCase() == meta[3].toLowerCase();}
});
var listOfLayers = {};
function rebuildList(type){
	$('#list').dataTable().fnClearTable();
	var rows = new Array();
	var mine = false;
	$('th.sharing').text('Permissions');
        console.log($('.sel option:selected').text());
	if($('.sel option:selected').text() == "My Maps"){ mine = true; $('th.sharing').text('Global Sharing');}
	
	$.each(listOfLayers.view, function(i, project) {
		<!--{if $showEdit}-->
		var edittext = (getShareLevel(project) == 3) ? '<a style="font-weight:bold;" href=".?do=project.edit1&id='+project["id"]+'">Edit</a>' : '<a style="font-weight:bold;" href=".?do=project.info&id='+project["id"]+'">Info</a>';
		<!--{else}-->
		var edittext= '';
		<!--{/if}-->
		rows.push([
			<!--{if $showBookmarks}-->getBookmark(project),<!--{/if}-->
			'<a style="font-weight:bold;"  onClick="openViewer('+project["id"]+',event);">'+project["name"]+'</a>',
			<!--{if $showEdit}-->edittext,<!--{/if}-->
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+project["owner"]+'">'+project["owner_name"]+'</a>',
			project["description"],
			getShare(project, mine)
			]);
	});
	$('#list').dataTable().fnAddData(rows);
	<!--{if $showBookmarks}-->$('td.bookmarked img').click(bookmark);<!--{/if}-->
	$('.sharing:econtains(none)').css({ "background-color": "#FF9999" });
	$('.sharing:econtains(read)').css({ "background-color": "#66FF66" });
	$('.sharing:econtains(copy)').css({ "background-color": "#99CCFF" });
	$('.sharing:econtains(edit)').css({ "background-color": "#FFFF66" });
	
	$('.sharing:econtains(private)').css({ "background-color": "#FF9999" });
	$('.sharing:econtains(unlisted)').css({ "background-color": "#FFFF66" });
	$('.sharing:econtains(public)').css({ "background-color": "#66FF66" });
	rearmToolTips();
}
var addImg = "media/icons/book_add.png";
var deleteImg = "media/icons/book_delete.png";
function bookmark(e){
	var tar = $(e.target);
	if(tar.attr('src') == addImg){
		$.post('./?do=project.addbookmark&id='+tar.attr("class"));
		tar.attr('src', deleteImg);
		tar.parent().children('span').html('d');
	}else{
		$.post('./?do=project.removebookmark&id='+tar.attr("class"));
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