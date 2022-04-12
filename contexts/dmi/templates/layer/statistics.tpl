<p class="title">Maps using this layer</p>
<!--{if $projects}-->
<table class="bordered">
<tr>
  <th><a href=".?do=layer.statistics&id=<!--{$layer->id}-->&sortprojects=name&desc=<!--{$sortdesc}-->">Map</a></th>
  <th>Info</th>
  <th><a href=".?do=layer.statistics&id=<!--{$layer->id}-->&sortprojects=owner&desc=<!--{$sortdesc}-->">Owner</a></th>
  <th><a href=".?do=layer.statistics&id=<!--{$layer->id}-->&sortprojects=categoryname&desc=<!--{$sortdesc}-->">Category</a></th>
  <th><a href=".?do=layer.statistics&id=<!--{$layer->id}-->&sortprojects=description&desc=<!--{$sortdesc}-->">Description</a></th>
</tr>
<!--{section name=i loop=$projects}-->
<!--{assign var=project value=$projects[i]}-->
<tr>
  <td style="width:2in;" class="<!--{$class}-->"><a style="font-weight:bold;" href="javascript:openViewer(<!--{$project->id}-->);"><!--{$project->name|truncate:30:"..."}--></a></td>
  <td style="width:0.25in;" class="<!--{$class}-->"><a href=".?do=project.info&id=<!--{$project->id}-->">info</a></td>
  <td style="width:1in;" class="<!--{$class}-->"><a href=".?do=social.peopleinfo&id=<!--{$project->owner->id}-->"><!--{$project->owner->username}--></a></td>
  <td style="width:2in;" class="<!--{$class}-->"><!--{$project->categoryname|truncate:30:"..."}--></td>
  <td style="width:5in;" class="<!--{$class}-->"><!--{$project->description|truncate:80:"..."}--></td>
</tr>
<!--{/section}-->
</table>
<!--{else}-->
    <p>none</p>
<!--{/if}-->

<p class="title">People who have bookmarked this Layer</p>
<!--{if $people}-->
<table class="bordered">
  <tr>
    <th><a href=".?do=layer.statistics&id=<!--{$layer->id}-->&sortpeople=username&desc=<!--{$sortdesc}-->">User</a></th>
    <th><a href=".?do=layer.statistics&id=<!--{$layer->id}-->&sortpeople=realname&desc=<!--{$sortdesc}-->">Name</a></th>
    <th><a href=".?do=layer.statistics&id=<!--{$layer->id}-->&sortpeople=description&desc=<!--{$sortdesc}-->">Description</a></th>
  </tr>
<!--{section name=i loop=$people}-->
<!--{assign var=person value=$people[i]}-->
  <tr>
    <td style="width:1in;" class="<!--{$class}-->"><a href=".?do=social.peopleinfo&id=<!--{$person->id}-->"><!--{$person->username}--></a> &nbsp;</td>
    <td style="width:2in;" class="<!--{$class}-->"><!--{$person->realname|truncate:30:"..."}--> &nbsp;</td>
    <td style="width:7.5in;" class="<!--{$class}-->"><!--{$person->description|truncate:90:"..."}--> &nbsp;</td>
  </tr>
<!--{/section}-->
</table>
<!--{else}-->
    <p>none</p>
<!--{/if}-->

<p class="title">Layer Transactions</p>

<!--{html_options name=years output=$years values=$years selected=$selectYear}-->
<!--{html_options name=months output=$months values=$months selected=$selectMonth}-->
<!--{html_options name=days output=$days values=$days selected=$selectDay}-->
<input type="checkbox" name="range" value="range"/> Range
<!--{html_options name=years2 output=$years values=$years selected=$selectYear}-->
<!--{html_options name=months2 output=$months values=$months selected=$selectMonth}-->
<!--{html_options name=days2 output=$days values=$days selected=$selectDay}-->
<div id="pivot">

</div>
<script>
var year;
var month;
var day;
var year2;
var month2;
var day2;
$(function(){
	$('[name="range"]').click(function(){
		if($('[name="range"]:checked').length){
			$('[name="years2"], [name="months2"], [name="days2"]').removeAttr("disabled");
		}else{
			$('[name="years2"], [name="months2"], [name="days2"]').attr("disabled", "disabled");
		}
		refresh();
	});
	//test = $.jsonQueue('google', function(){}, function(){});
	year = $('[name="years"]').val();
	month = $('[name="months"]').val();
	day = $('[name="days"]').val();
	year2 = $('[name="years2"]').val();
	month2 = $('[name="months2"]').val();
	day2 = $('[name="days2"]').val();
	$('[name="years"]').css('min-width','60px').editableSelect({
		bg_iframe: true, case_sensitive: false, onSelect: function(list_item) {year = list_item.text();refresh();if(list_item.text() == '*'){ var temp = $('[name="months"], [name="days"]').editableSelectInstances()[0]; temp.pickListItem(temp.selectFirstListItem()); $('[name="months"], [name="days"]').attr("disabled", "disabled");}else{$('[name="months"]').removeAttr("disabled");}}
	}).css('min-width','60px');
	$('[name="months"]').css('min-width','60px').editableSelect({
		bg_iframe: true, case_sensitive: false, onSelect: function(list_item) {month = list_item.text();refresh(); var temp = $('[name="days"]').editableSelectInstances()[0]; if(list_item.text() == '*'){ temp.pickListItem(temp.selectFirstListItem()); $('[name="days"]').attr("disabled", "disabled");}else{$('[name="days"]').removeAttr("disabled");}setOptionsForMonth(temp, month, year);}
	}).css('min-width','60px');
	$('[name="days"]').css('min-width','60px').editableSelect({
		bg_iframe: true, case_sensitive: false, onSelect: function(list_item) {day = list_item.text();refresh();}
	}).css('min-width','60px');
	$('[name="years2"]').css('min-width','60px').editableSelect({
		bg_iframe: true, case_sensitive: false, onSelect: function(list_item) {year2 = list_item.text();refresh();if(list_item.text() == '*'){ var temp = $('[name="months2"], [name="days2"]').editableSelectInstances()[0]; temp.pickListItem(temp.selectFirstListItem());  $('[name="months2"], [name="days2"]').attr("disabled", "disabled");}else{$('[name="months2"]').removeAttr("disabled");}}
	}).css('min-width','60px');
	$('[name="months2"]').css('min-width','60px').editableSelect({
		bg_iframe: true, case_sensitive: false, onSelect: function(list_item) {month2 = list_item.text();refresh(); var temp = $('[name="days2"]').editableSelectInstances()[0]; if(list_item.text() == '*'){ temp.pickListItem(temp.selectFirstListItem()); $('[name="days2"]').attr("disabled", "disabled");}else{$('[name="days2"]').removeAttr("disabled");}setOptionsForMonth(temp, month2, year2);}
	}).css('min-width','60px');
	$('[name="days2"]').css('min-width','60px').editableSelect({
		bg_iframe: true, case_sensitive: false, onSelect: function(list_item) {day2 = list_item.text();refresh();}
	}).css('min-width','60px');
	$('[name="years2"], [name="months2"], [name="days2"]').attr("disabled", "disabled");
	refresh();
});
function makeUri(){
	var uri = '&layer_id=<!--{$layer->id}-->&range='+$('[name="range"]:checked').length;
	uri += '&year='+year;
	uri += '&month='+month;
	uri += '&day='+day;
	if($('[name="range"]:checked').length){
		uri += '&year2='+year2;
		uri += '&month2='+month2;
		uri += '&day2='+day2;
	}
	return uri;
}
function refresh(){
	if( $('#subnav') !== null) {$('#subnav').parent().css({margin:0,padding:0});}
	$.get('./?do=wapi.layer.transactions'+makeUri(), function(data) {
		var JSONdata = {
			columns: [
				{ colvalue: "transaction_name", coltext: "transaction_name", header: "Transaction", sortbycol: "transaction_name", groupbyrank: null,  pivot: true},
				{ colvalue: "project_name", coltext: "project_name", header: "Map", sortbycol: "project_name", groupbyrank: 1 },
				{ colvalue: "actor_name", coltext: "actor_name", header: "User", sortbycol: "actor_name", groupbyrank: 2 },
				{ colvalue: "count", coltext: "count", header: "count", sortbycol: "count", groupbyrank: null, result: true }
			],
			rows: eval(data)
		};
		$('#pivot').pivot({
			source: JSONdata,
		});
	});
}
</script>
