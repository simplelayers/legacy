<div id="subnav">
	<div id="titleRow">
		<div id="objectData" class="title">Reports</div>
	</div>
	<div style="float:right;">&nbsp;</div>
	<div id="navRow">
	<div id="selector"></div>
	<div class="clear"></div>
	</div>
</div>
</div>
<div style="left:0;right:0;padding:5px 10px 0 10px;">
<!--{html_options name=years output=$years values=$years selected=$selectYear}-->
<!--{html_options name=months output=$months values=$months selected=$selectMonth}-->
<!--{html_options name=days output=$days values=$days selected=$selectDay}-->
<input type="checkbox" name="range" value="range"/> Range
<!--{html_options name=years2 output=$years values=$years selected=$selectYear}-->
<!--{html_options name=months2 output=$months values=$months selected=$selectMonth}-->
<!--{html_options name=days2 output=$days values=$days selected=$selectDay}-->
<div id="pivot">

</div>
<div id="listDiv">
	<table id="list" class="bordered" style="width:100%;">
		<thead>
			<tr>
				<th>Actor</th>
				<th>Actor IP</th>
				<th>Activity</th>
				<th>Target Type</th>
				<th>Target</th>
				<th>Recipient</th>
			</tr>
		</thead>
		<tbody>

		</tbody>
	</table>
</div>
<script>
var year;
var month;
var day;
var year2;
var month2;
var day2;
$(function(){
	$('#listDiv').css('display','none');
	$('#list').dataTable({
		"bPaginate": false,
		"bFilter": true,
		"bInfo": false,
		"bAutoWidth": true,
		"sDom": '<"filterNav"f>lipt',
		"bStateSave": true,
		"aaSorting": [[ 2, "asc" ]],
		"aoColumns": [
			{ "sClass": "actor" },
			{ "sClass": "actor_ip" },
			{ "sClass": "activity" },
			{ "sClass": "target_type" },
			{ "sClass": "target" },
			{ "sClass": "recipient" }
		],
		"oLanguage": {
			"sEmptyTable": "No logs to display.",
			"sZeroRecords": "No matching logs found."
		}
	});
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
	var uri = '&range='+$('[name="range"]:checked').length;
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
	$.get('./?do=wapi.admin.reports'+makeUri(), function(data) {
		var JSONdata = {
			columns: [
				{ colvalue: "activity", coltext: "activity", header: "Activity", sortbycol: "activity", groupbyrank: null,  pivot: true},
				{ colvalue: "target", coltext: "target", header: "Target Type", sortbycol: "target", groupbyrank: 1 },
				{ colvalue: "target_name", coltext: "target_name", header: "Target", sortbycol: "target_name", groupbyrank: 2 },
				{ colvalue: "sum", coltext: "sum", header: "sum", sortbycol: "sum", groupbyrank: null, result: true }
			],
			rows: data
		};
		$('#pivot').pivot({
			source: JSONdata,
			onResultCellClicked: function (data) { makeTable(JSON.stringify(data)); }
		});
		var total = $('#pivot tr.total td.total');
		total.html('<a href="javascript:makeTable(\'\');">'+total.html()+'</a>');
		if(lastClicked !== false) makeTable(lastClicked);
	});
}
var lastClicked = false;
function makeTable(clickData){
	var temp = new Object;
	temp.data = clickData;
	lastClicked = clickData;
	$.getJSON('./?do=wapi.admin.reports'+makeUri()+'&full=1', temp, function(data) {
		$('#list').dataTable().fnClearTable();
		$('#listDiv').css('display','block');
		var rows = new Array();
		$.each(data, function(key, record) {
			rows.push([
				record["actor"],
				record["actor_ip"],
				record["activity"],
				record["target"],
				record["target_name"],
				(record["recipient"] != null ? record["recipient"] : "")
			]);
		});
		$('#list').dataTable().fnAddData(rows);
	});
}
</script>