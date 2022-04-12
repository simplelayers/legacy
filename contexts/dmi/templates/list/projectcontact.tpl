<table id="list2" class="bordered" style="width:100%;">
<thead>
<tr>
	<th style="width:16px;"><span><img src="media/icons/book.png"/></span></th>
	<th style="width:200px;">Map</th>
	<th style="width:54px;">Edit/Info</th>
	<th style="width:120px;">Owner</th>
	<th style="">Description</th>
	<th style="width:54px;">Sharing</th>
</tr>
</thead>
<tbody>

</tbody>
</table>
<script>
function getShareLevel2(project, mine){
	var level = 0;
	if(mine){
		if(project["allowlpa"] == "t"){level = 1;}
		if(project["private"] == "f"){level = 2;}
	}else{
		level = 1;
		if(project["owner"] == "<!--{$user->id}-->"){ level = 3;}
		if(project["permission"] > level){ level = project["permission"] }
	}
	return level;
}
function getBookmark2(project){
	var marked = project["bookmarked"];
	if(marked == 'true') return '<span style="display:none;">d</span><img title="Unbookmark this project." src="'+delete2Img+'" class="'+project["id"]+'"/>';
	return '<span style="display:none;">a</span><img title="Bookmark this project." src="'+add2Img+'" class="'+project["id"]+'"/>';
}
function getShare2(project, mine){
	var level = getShareLevel2(project, mine);
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
	<!--{if isset($selector) === false}-->$('#selector2').dataSelector({'type' : 'project'}).bind("update", function(e, data){listOfProjects = data;rebuildList2();}).bind("loading", function(e){$('#list2').dataTable().fnClearTable();});<!--{/if}-->
	$('#list2').dataTable({
        "bPaginate": false,
        "bFilter": true,
        "bInfo": false,
		"bAutoWidth": false,
		"sDom": '<"filterNav"f>lipt',
		"bStateSave": true,
		"aaSorting": [[ 2, "asc" ]],
		"aoColumns": [ 
      { "sClass": "bookmarked" },
      { "sClass": "map" },
      { "sClass": "edit" },
      { "sClass": "owner" },
      { "sClass": "description" },
      { "sClass": "sharing" }
    ],
	"oLanguage": {
      "sEmptyTable": "No maps to display.",
	  "sZeroRecords": "No matching maps found."
    }
    });
	$.extend({postJSON: function( url, data, callback) {return jQuery.post(url, data, callback, "json");}});
	$.expr[":"].econtains = function(obj, index, meta, stack){return (obj.textContent || obj.innerText || $(obj).text() || "").toLowerCase() == meta[3].toLowerCase();}
});
var listOfProjects = {};
function rebuildList2(type){
	$('#list2').dataTable().fnClearTable();
	var rows = new Array();
	var mine = false;
	$('th.sharing').text('Permissions');
	if($('.sel option:selected').text() == "My Maps"){ mine = true; $('th.sharing').text('Sharing');}
	$.each(listOfProjects.view, function(i, project) {
		var edittext = (getShareLevel2(project) == 3) ? '<a style="font-weight:bold;" href=".?do=project.edit1&id='+project["id"]+'">Edit</a>' : '<a style="font-weight:bold;" href=".?do=project.info&id='+project["id"]+'">Info</a>';
		rows.push([
			getBookmark2(project),
			'<a style="font-weight:bold;" href="javascript:openViewer('+project["id"]+');">'+project["name"]+'</a>',
			edittext,
			'<a style="font-weight:bold;" href=".?do=contact.info&id='+project["owner"]+'">'+project["owner_name"]+'</a>',
			project["description"],
			getShare2(project, mine)
			]);
	});
	$('#list2').dataTable().fnAddData(rows);
	$('#list2 td.bookmarked img').click(bookmark2);
	$('.sharing:econtains(none)').css({ "background-color": "#FF9999" });
	$('.sharing:econtains(read)').css({ "background-color": "#66FF66" });
	$('.sharing:econtains(copy)').css({ "background-color": "#99CCFF" });
	$('.sharing:econtains(edit)').css({ "background-color": "#FFFF66" });
	
	$('.sharing:econtains(private)').css({ "background-color": "#FF9999" });
	$('.sharing:econtains(unlisted)').css({ "background-color": "#FFFF66" });
	$('.sharing:econtains(public)').css({ "background-color": "#66FF66" });
	rearmToolTips();
}
var add2Img = "media/icons/book_add.png";
var delete2Img = "media/icons/book_delete.png";
function bookmark2(e){
	var tar = $(e.target);
	if(tar.attr('src') == add2Img){
		$.post('./?do=project.addbookmark&id='+tar.attr("class"));
		tar.attr('src', delete2Img);
		tar.parent().children('span').html('d');
	}else{
		$.post('./?do=project.removebookmark&id='+tar.attr("class"));
		tar.attr('src', add2Img);
		tar.parent().children('span').html('a');
		if($('.sel').val() == 2){
			var aTrs = $('#list2').dataTable().fnGetNodes();
			for ( var i=0 ; i<aTrs.length ; i++ ){
				if ( $(aTrs[i]).html() == $(e.target).parent().parent().html()){
					$('#list2').dataTable().fnDeleteRow( aTrs[i] );
				}
			}
		}
	}
}
</script>