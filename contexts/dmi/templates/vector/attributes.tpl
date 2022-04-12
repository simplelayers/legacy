<script type="text/javascript" src="lib/js/jquery.checkbox.min.js"></script>
<!--{$subnav}-->
<div class="intro">Your data layers are tables that include fields (columns) called attributes. You may control how attributes appear throughout the system by modifying the information below. In BASIC mode, you have access to tools for presenting the data to end users. In ADVANCED mode there are additional tools for modifying the data itself (renaming, dropping, and adding attributes). </div>
<script>
$(function() {
	$( "#rows" ).sortable({
		items: "tr:not(.noSort)",
		stop: function(event, ui) { letLeave = false;$('#saveButton').removeAttr('disabled');setOrder();}
	});
	$("input[name='drop']").checkbox({cls:'jquery-checkbox', empty: 'media/empty.png'});
	function goodbye(e) {
	if(letLeave) return;
	if(!e) e = window.event;
		e.cancelBubble = true;
		e.returnValue = 'This page is asking you to confirm that you want to leave - data you have entered may not be saved.';

		if (e.stopPropagation) {
			e.stopPropagation();
			e.preventDefault();
		}
	}
	window.onbeforeunload=goodbye;
	$("#rows select").change(function(){letLeave = false;$('#saveButton').removeAttr('disabled');});
	$("#rows input").change(function(){letLeave = false;$('#saveButton').removeAttr('disabled');});
	$("#rows :text").keyup(function(){letLeave = false;$('#saveButton').removeAttr('disabled');});
	editAttributes();
	setOrder();
});
var data = Array();
function sendForm(){
	
	if($('#rows input[name="drop"]:checked').length){
		if(!confirm("Are you sure you would like to save these changes. Any deleted attributes will result in it's column data being lost.")) return false;
	}
	var data = [];
	$("#rows tr").each(function(){
		if($(this).children("td").length){
			var temp = new Object;
			temp.startName = $(this).find('input[name="startName"]').val();
			temp.name = $(this).find('input[name="name"]').val();
			temp.display = $(this).find('input[name="display"]').val();
			temp.startType = $(this).find('input[name="startType"]').val();
			temp.type = $(this).find('select[name="type"]').val();
			temp.visible = ($(this).find('input[name="visible"]:checked').length ? true : false);
			temp.searchable = ($(this).find('input[name="searchable"]:checked').length ? true : false);
			temp.drop = ($(this).find('input[name="drop"]:checked').length ? true : false);
			data.push(temp);
		}
	});
	data = {'data': JSON.stringify(data)};
	letLeave = true;
	$.ajax({
		type: 'POST',
		url: "./?do=vector.attributessavechanges&id=<!--{$layer->id}-->",
		data: data,
		success: function(data){console.log(data);  window.location.href = "./?do=vector.attributes&id=<!--{$layer->id}-->";},
		dataType: "text"
	});
	
}
function createAdd(){
	var newRow = $('<tr>'+
    '<td class="order"></td>'+
    '<td style="width:2in;"><span class="name"></span><input name="startName" type="hidden" value="_newColumnName_"/><input name="name" class="hide" type="text" value=""/></td>'+
    '<td style="width:2in;"><input name="display" type="text" value=""/></td>'+
    '<td style="width:1in;"><input name="startType" type="hidden" value=""/><select name="type">'+
		<!--{foreach from=$columntypes key=typekey item=type}-->
			'<option label="<!--{$type}-->" value="<!--{$type}-->"><!--{$type}--></option>'+
		<!--{/foreach}-->
	'</select></td>'+
    '<td style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="visible" value="" checked/></td>'+
    '<td style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="visible" value="" checked/></td>'+
    '<td class="hide" style="width:0.25in;text-align:center;"><a href="#" onclick="$(this).parent().parent().remove();return false;"><img class="nopad" class="sudoDelete" src="media/icons/delete.png"/></a></td>'+
  '</tr>').appendTo('#rows').find('.hide').show();
  letLeave = false;
  $('#saveButton').removeAttr('disabled');
  setOrder();
}
function resetAttributes() {
    require(["sl_modules/WAPI"],function(wapi) {
		wapi.exec("layers/attributes/action:reset_attributes/layerId:"+<!--{$layer->id}-->+"/",{},function(response){
                    console.log(response);
                window.location.href = "./?do=vector.attributes&id=<!--{$layer->id}-->";});
	});
        return false;
}
function editAttributes(){
	if($('#adv').text() == "Enter Advanced"){
		$(".hide").toggleClass("hide",false).toggleClass("unhide",true).show();
		$("#rows span.name").hide()
		$('#adv').text("Enter Basic");
		$('#adv').text("Enter Basic");
		
		$('#enterNextModeText').text("ADVANCED")
	}else{
		$(".unhide").toggleClass("unhide",false).toggleClass("hide",true).hide();
		cancelVocab();	
		$("#rows span.name").show()
		$("#rows span.name").each(function(){
			$(this).text($(this).parent().find('[name="name"]').val());
		});
		$('#adv').text("Enter Advanced");

		$('#enterNextModeText').text("BASIC")
	}
	rearmToolTips();
}

var vocabAttribute='';
var vocabLayer="<!--{$layer->id}-->";
function editVocab(attribute,layerId){
	vocabAttribute = attribute;
	$("#vocabHeading").val("Editing Controlled Vocabulary for "+attribute);
	$("#vocab_entry").val('Loading...');
	$("#vocab_editor_v0").toggleClass('hidden',false);
	require(["sl_modules/WAPI"],function(wapi) {
		wapi.exec("layers/attributes/action:get_vocab/layerId:"+layerId+"/attribute:"+attribute,{},vocabReady);
	});

}
function cancelVocab() {
	$('#vocab_editor_v0').toggleClass('hidden',true);
}

function vocabReady(response){
	$("#vocab_entry").val(response.results.vocab);	
}

function saveVocab() {
	var vocab =$("#vocab_entry").val();
	
	require(["sl_modules/WAPI"],function(wapi) {
				wapi.exec("layers/attributes/action:set_vocab/layerId:"+vocabLayer+"/attribute:"+vocabAttribute,{vocab:vocab},vocabSaved);
	});
	$("#vocab_entry").val('Saving...');
	
}

function vocabSaved(){
	$('#vocab_editor_v0').toggleClass('hidden',true);
}
$(function(){
	rearmToolTips();
});
var letLeave = true;
function setOrder(){
	$('.order').each(function(index, element){
		$(element).html(index+1);
	});
}

</script>
<form action="." method="post" onSubmit="sendForm(); return false;">
<!--{if !$isRelational}-->
<div style="width:100%;min-width: 1024px;">
<div style="margin-bottom:5px;">
Now in <span id="enterNextModeText">BASIC</span> mode, click here to <button class='button' id="adv" onclick="editAttributes();return false;">Enter Basic</button> mode
</div>

<!--{/if}-->

<div style="width:100%;float:left">
<table class="bordered" id="rows">
<tr class="noSort">
  <th style="padding-bottom:2px;cursor:auto;">Display Order<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Order of display to end users, from left to right. Populate each cell in the column from 1, 2, 3, ..."/></th>
  <th style="padding-bottom:2px;cursor:auto;">Attribute<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="The attribute's name. Change in adv. mode."/></th>
  <th style="padding-bottom:2px;cursor:auto;">Display Name<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="The name displayed to the end user"/></th>
  <th style="padding-bottom:2px;cursor:auto;">Type<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Data type. Text changed to 'url' will be treated as hyperlinks."/></th>
  <th style="padding-bottom:2px;cursor:auto;">Visible<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Checked items will show in search results."/></th>
  <th style="padding-bottom:2px;cursor:auto;">Searchable<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Checked items are selectable in serching operations."/></th>
  <th style="padding-bottom:2px;cursor:auto;" class="hide">Vocab<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Manage a controlled vocabulary to use for this attribute."/></th>
  <th style="padding-bottom:2px;cursor:auto;" class="hide">Delete<img style="margin-left:5px;margin-bottom:-4px;" src="media/icons/information.png" title="Deleted attributes cannot be recovered after &quot;save changes&quot; is entered."/></th>
</tr>
<!--{foreach from=$columns key=columnname item=column}-->
<!--{cycle values="color,altcolor" assign=class}-->
  <tr>
    <td class="order"><!--{abs($column.z)}--></td>
    <td class="<!--{$class}-->"><span class="name"><!--{$column.name}--></span><input name="startName" type="hidden" value="<!--{$column.name}-->"/><input name="name" class="hide" type="text" value="<!--{$column.name}-->"/></td>
    <td class="<!--{$class}-->"><input name="display" type="text" value="<!--{$column.display}-->"/></td>
    <td class="<!--{$class}-->" style="width:0.75in;"><input name="startType" type="hidden" value="<!--{$column.requires}-->"/><!--{if in_array($column.type, $conversions) && !$isRelational}--><!--{html_options name=type output=$conversions values=$conversions selected=$column.type}--><!--{else}--><span style="padding-left:2px;"><!--{$column.type}--></span><!--{/if}--></td>
    <td class="<!--{$class}-->" style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="visible" <!--{if $column.visible === true}-->checked="true"<!--{/if}-->/></td>
    <td class="<!--{$class}-->" style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="searchable" <!--{if $column.searchable === true}-->checked="true"<!--{/if}-->/></td>
    <td class="<!--{$class}--> hide" style="text-align:left;"><button type="button" class="button" onClick="editVocab('<!--{$columnname}-->',<!--{$layer->id}-->)" value="V">V</button></td>
    <td class="<!--{$class}--> hide" style="width:0.25in;text-align:center;"><input type="checkbox" class="nopad" name="drop" /></td>
  </tr>
<!--{/foreach}-->
</table>

<!--{if !$isRelational}-->
<button class='button' onclick="createAdd();return false;" class="hide">Add New Attribute</button>
<button class="button" type="button" onclick="resetAttributes();return false;">Rebuild Attributes</button>
<!--{/if}-->
<p style=""><button class='button' id="saveButton" type="submit" name="submit" disabled='true' style="width:2in;">Save Changes</button></p>
</div>
</div>

<div id="vocab_editor_v0" class="vocab_editor_v0 hidden" style="position:absolute;left: 0px;right:0px ;background:#ffffff;padding-left:10px;padding-right:10px;bottom: 16px;top: 362px;min-width:300px">
<H2 id='vocabHeading'>Vocabulary editor</H2>
<p>Provide a comma-separated list of values in the data entry field below. This will be used for data entry in certain utilities as a drop-down list of options.
</p>
<textarea id="vocab_entry" style="display:block;width:960px;height: 25%" columns=10 >Loading ...</textarea>
<button class="button" type="button" onClick="cancelVocab()">Cancel</button><button class="button" type="button" onClick="saveVocab()">Save Vocab Changes</button>
</div>



</form>
</div>