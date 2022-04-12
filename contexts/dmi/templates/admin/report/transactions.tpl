<!--{$subnav}-->
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
	$.get('./?do=wapi.layer.transactions'+makeUri(), function(data) {
		var JSONdata = {
			columns: [
				{ colvalue: "transaction_name", coltext: "transaction_name", header: "Transaction", sortbycol: "transaction_name", groupbyrank: null,  pivot: true},
				{ colvalue: "layer_id", coltext: "layer_name", header: "Map", sortbycol: "layer_name", groupbyrank: 1 },
				{ colvalue: "actor_name", coltext: "actor_name", header: "User", sortbycol: "actor_name", groupbyrank: 2 },
				{ colvalue: "count", coltext: "count", header: "count", sortbycol: "count", groupbyrank: null, result: true }
			],
			rows: data
		};
		$('#pivot').pivot({
			source: JSONdata,
		});
	});
}
</script>