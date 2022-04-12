
	<p>Name: <input type="text" id="name" name="name" value="<!--{$form->name}-->"/><p>
	<table class="bordered ui-sortable" style="width:100%;" id="tableList">
		<!--{foreach from=$attributes item=attributeData key=attribute}-->
		<tr class="<!--{$attribute}-->">
			<td style="width:200px;"><!--{$attributeData.display}--></td>
			<td style="width:100px;">
				<span class="dataTypes"></span>
			</td>
			<td>
				<span class="options"></span>
			</td>
		</tr>
		<!--{/foreach}-->
	</table>
	<input type="submit" value="Save"onclick="save();return false;"/>
<script>
	var fields = <!--{$fields}-->;
	$(function(){
		$.each(attributeData, function(index, value) {
			$('.'+index+' .dataTypes').html(buildDataSelect(index, value["requires"])).change(dataSelected);
			$('.'+index+' .dataTypes select').change();
		});
		$.each(fields, function(index, value){
			theRow = $('tr.'+index);
			theRow.find('.dataTypes select').val(value["dataType"]).change();
			if(value["dataType"] == 5 || value["dataType"] == 6 || value["dataType"] == 8){
				theRow.find('span.options span.options span').remove();
			}
			$.each(value, function(index2, value2){
				if(index2 != "mathOperator"){
					if(index2 == "option"){
						$.each(value2, function(index3, value3){
							theRow.find('span.options span.options').append(makeNewMultiInput(index, attributeData[index]["requires"], value3));
							if(index3 == value["odefault"]){
								theRow.find('span.options span.options span input[type="radio"]').last().prop('checked',true);
							}
						});
					}else if(index2 == "mathAttr"){
						$.each(value2, function(index3, value3){
							theRow.find('span.options span.options').append(makeNewAttrInput(index, value3, (index3==0 ? "+" : value["mathOperator"][index3-1]), (index3==0 ? true : false)));
						});
					}else{
						if(theRow.find('span.options input.'+index2).attr("type") == "checkbox" && value2){
							theRow.find('span.options input.'+index2).prop('checked',true);
						}else{
							theRow.find('span.options input.'+index2).val(value2);
						}
					}
				}
			});
			theRow.appendTo('#tableList');
		});
	});
	var attributeData = <!--{$jsonAttribute}-->;
	function buildDataSelect(name, type){
		var toReturn = "<select>";
		switch(type){
			case "text area":
				toReturn += '<option value="1">Text</option><option value="3">Date</option>';
			case "int":
			case "float":
				toReturn += '<option value="2">Number</option><option value="5">Dropdown</option><option value="6">Radio</option><option value="8">Math</option>';
			case "boolean":
				toReturn += '<option value="4">Checkbox</option><option value="7">Constant</option>';
			break;
			case "url":
				toReturn += '<option value="1">Text</option><option value="7">Constant</option>';
			break;
		}
		toReturn += "</select>";
		return toReturn;
	}
	function getStandardType(type){
		var fieldType = "checkbox";
		if(type == "text area" || type == "url") fieldType = "text";
		else if(type == "int" || type == "float") fieldType = "number";
		return fieldType;
	}
	function makeNewMultiInput(name, type, def, defChecked){
		def = typeof def !== 'undefined' ? def : '';
		defChecked = typeof defChecked !== 'undefined' ? defChecked : false;
		return '<span>Option: <input type="radio" class="default" name="'+name+'_default" '+(defChecked ? 'checked="checked"' : '')+'/><input type="'+getStandardType(type)+'" class="option" value="'+def+'" /><img src="media/icons/delete.png" onclick="var sPar=$(this).parent().parent();if(sPar.children(\'span\').length > 1){$(this).parent().remove();}if(sPar.find(\'input[type=\\\'radio\\\']:checked\').length == 0){sPar.find(\'input[type=\\\'radio\\\']\').first().prop(\'checked\',true);}"/><span class="error"></span><br/></span>';
	}
	function makeNewAttrInput(name, def, defSign, first){
		def = typeof def !== 'undefined' ? def : '';
		defSign = typeof defSign !== 'undefined' ? defSign : '+';
		first = typeof first !== 'undefined' ? first : false;
		toReturn = '';
		toReturn += '<span>';
		if(!first) toReturn += '<select class="mathOperator"><option value="+" '+(defSign == '+' ? 'selected' : '')+'>+</option><option value="-" '+(defSign == '-' ? 'selected' : '')+'>-</option><option value="*" '+(defSign == '*' ? 'selected' : '')+'>*</option><option value="/" '+(defSign == '/' ? 'selected' : '')+'>/</option></select>'
		toReturn += '<select class="mathAttr">';
		$.each(fields, function(index, value){
			if((attributeData[index]["requires"] == "int" || attributeData[index]["requires"] == "float" || value["dataType"] == 8) && index != name)
			toReturn +='<option value="'+index+'" '+(def == index ? 'selected' : '')+'>'+attributeData [index]["display"]+'</option>';
		});
		toReturn += '</select>'+(first ? '' : '<img src="media/icons/delete.png" onclick="var sPar=$(this).parent().parent();$(this).parent().remove();"/>')+'<br/></span>';
		if(def != '') $(toReturn).find("select.mathAttr").val(def).change();
		return toReturn;
	}
	function addMathBlocks(){
		$.each(fields, function(index, value){
			var dataValue = $('#tableList tr.'+index).find('select').val();
			var create = false;
			if(attributeData[index]["requires"] == "int" || attributeData[index]["requires"] == "float" || dataValue == "8") create = true;
			$("select.mathAttr").each(function(index2, value2){
				if($(value2).find("option[value='"+index+"']").length == 0 && create){
					if($(value2).parents("tr").attr('class') != index) $(value2).append('<option value="'+index+'">'+attributeData [index]["display"]+'</option>');
				}else if($(value2).find("option[value='"+index+"']").length != 0 && !create){
					$(value2).find("option[value='"+index+"']").remove();
				}
			});
		});
	}
	function dataSelected(event){
	addMathBlocks();
		var dataOption = $(event.target).val();
		var attribute = $(event.target).parent().parent().parent().attr('class');
		var dataType = attributeData[attribute]["requires"];
		var toMakeOptions = "";
		switch(dataOption){
			case "1":
				toMakeOptions += 'Min Length <img src="media/icons/information.png" title="Minimum number of characters required."/>: <input type="number" class="min" min="0" value="0" required="required" /><span class="error"></span><br/>';
				toMakeOptions += 'Max Length <img src="media/icons/information.png" title="Maximum number of characters allowed. (0 no limit)"/>: <input type="number" class="max" min="0"  value="0" required="required"/><span class="error"></span><br/>';
				toMakeOptions += 'Default Value <img src="media/icons/information.png" title="Default value for this form field before a user enters data."/>: <input type="text" class="default" /><span class="error"></span><br/>';
				toMakeOptions += 'Regex <img src="media/icons/information.png" title="A regular expression that must pass to be accepted. (Blank none)"/>: <input type="text" class="regex" /><span class="error"></span><br/>';
			break;
			case "2":
				toMakeOptions += 'Min <img src="media/icons/information.png" title="Minimum value required. (blank no limit)"/>: <input type="number" class="min" /><span class="error"></span><br/>';
				toMakeOptions += 'Max <img src="media/icons/information.png" title="Maximum value allowed. (blank no limit)"/>: <input type="number" class="max" /><span class="error"></span><br/>';
				if(dataType == "float") toMakeOptions += 'Precision <img src="media/icons/information.png" title="Number of decimal places to save. (0 whole number, blank all)"/>: <input type="number" class="precision" min="0"/><span class="error"></span><br/>';
				else toMakeOptions += '<input type="hidden" class="precision" value="0"/>';
				toMakeOptions += 'Default Value <img src="media/icons/information.png" title="Default value for this form field before a user enters data."/>: <input type="number" class="default" /><span class="error"></span><br/>';
			break;
			case "3":
				toMakeOptions += 'Defaults offset: <img src="media/icons/information.png" title="Number of days to skip. (7 is one week from now, -7 is a week ago.)"/>: <input type="number" class="offset" value="0"/><span class="error"></span><br/>';
			break;
			case "4":
				if(dataType != "boolean"){
					toMakeOptions += 'Checked Value <img src="media/icons/information.png" title="Value stored when field is checked by the user."/>: <input type="'+getStandardType(dataType)+'" class="checked" /><span class="error"></span><br/>';
					toMakeOptions += 'Unchecked Value <img src="media/icons/information.png" title="Value stored when field is unchecked by the user."/>: <input type="'+getStandardType(dataType)+'" class="unchecked" /><span class="error"></span><br/>';
				}
				toMakeOptions += 'Default Position <img src="media/icons/information.png" title="Default position for this check box."/>: <input type="checkbox" class="default" /><span class="error"></span><br/>';
			break;
			case "5":
			case "6":
				toMakeOptions += '<span class="options">'+makeNewMultiInput(attribute, dataType, "", true)+'</span>';
				toMakeOptions += '<img src="media/icons/add.png" title="Add a new option." onclick="$(this).parent().children(\'.options\').append(makeNewMultiInput(\''+attribute+'\', \''+dataType+'\'));rearmToolTips();"/><br/>';
			break;
			case "7":
				toMakeOptions += 'Value <img src="media/icons/information.png" title="Value to be stored with every entry."/>: <input type="'+getStandardType(dataType)+'" class="value" /><span class="error"></span><br/>';
			break;
			case "8":
				toMakeOptions += '<span class="options"><img src="media/icons/information.png" title="Math blocks calculates each equation in the order provided not order of operations. Eg. Int1+Int2*Int3 will first add Int1 and Int2 then multiply the result by Int3."/>'+makeNewAttrInput(attribute, "", "+", true)+'</span>';
				toMakeOptions += '<img src="media/icons/add.png" title="Add a new variable." onclick="$(this).parent().children(\'.options\').append(makeNewAttrInput(\''+attribute+'\'));rearmToolTips();"/><br/>';
			break;
		}
		$('.'+attribute+' .options').html(toMakeOptions);
		rearmToolTips();
	}
	
	function save(){
		var formFields = {};
		var errorList = {};
		var j = 0;
		$('#tableList tr').each(function( index, value) {
			var field = $(value).attr('class');
			formFields[field] = {};
			dataType = $(value).find('select').val();
			formFields[field]["dataType"] = dataType;
			var i = 0;
			var j = 0;
			$(value).find('input, select.mathOperator, select.mathAttr').each(function( i2, child) {
				var childClass = $(child).attr('class');
				if(dataType == "8"){
					if(childClass == "mathAttr"){
						if(i == 0)formFields[field][childClass] =  {};
						formFields[field][childClass][i++] = $(child).val();
					}
					if(childClass == "mathOperator"){
						if(j == 0)formFields[field][childClass] =  {};
						formFields[field][childClass][j++] = $(child).val();
					}
				}else{
					if(childClass == "option"){
						if(i == 0)formFields[field][childClass] =  {};
						if($(child).prev("input:checked").length == 1){
							formFields[field]["odefault"] = i;
						}
						formFields[field][childClass][i++] = $(child).val();
					}else{
						if($(child).attr("type") == "checkbox"){
							formFields[field][childClass] = ($(child).is(':checked') ? 1 : 0);
						}else{
							formFields[field][childClass] = $(child).val();
						}
						
					}
				}
				var error = validate(field, dataType, childClass, $(child).val());
				if(error !== false) errorList[j++] = error;
			});
		});
		var jsonToSend = JSON.stringify(formFields);
		//-- Send it.
		$("span.error").html("");
		if(JSON.stringify(errorList) == "{}"){
			$.ajax({
				type: "POST",
				url: "./?do=wapi.forms.edit&id=<!--{$form->id}-->",
				data: {fields: JSON.stringify(formFields), name: $("#name").val()},
				dataType: dataType
			}).complete (function(){
				window.location.href = "./?do=forms.edit&id=<!--{$form->id}-->";
			});
		}else{
			$.each(errorList, function(index, value) {
				if(value["Field"] == "option"){
					$("#tableList tr."+value["Attribute"]+" input."+value["Field"]).each(function(index2, value2) {
						if(value["Value"] == $(value2).val()){
							$(value2).nextAll("span.error").html(value["Issue"]);
						}
					});
				}else{
					$("#tableList tr."+value["Attribute"]+" input."+value["Field"]).next("span.error").html(value["Issue"]);
				}
			});
		}
	}
	
	function validate(attr, dataType, field, value){
		var databaseType = attributeData[attr]["requires"];
		var toReturn = {};
		toReturn["Attribute"] = attr;
		toReturn["Field"] = field;
		toReturn["Value"] = value;
		var passed = true;
		switch(dataType){
			case "1":
				if((field == "min" || field == "max") && numAboveZero.test(value) == false){passed=false;toReturn["Issue"]="Must be a whole number.";}
			break;
			case "2":
				if(field == "min" || field == "max"){
					if(databaseType == "int" && anyNumOrBlank.test(value) == false){passed=false;toReturn["Issue"]="Must be an integer or blank.";}
					if(databaseType == "float" && anyFloatOrBlank.test(value) == false){passed=false;toReturn["Issue"]="Must be a number or blank.";}
				}
				if(field == "precision" && numAboveZeroOrBlank.test(value) == false){passed=false;toReturn["Issue"]="Must be a whole number or blank.";}
			break;
			case "3":
				if(field == "offset" && anyNum.test(value) == false){passed=false;toReturn["Issue"]="Must be an integer.";}
			break;
			case "4":
				if(field == "checked" || field == "unchecked"){
					if(databaseType == "int" && anyNum.test(value) == false){passed=false;toReturn["Issue"]="Must be an integer.";}
					if(databaseType == "float" && anyFloat.test(value) == false){passed=false;toReturn["Issue"]="Must be a number.";}
				}
			break;
			case "5":
			case "6":
				if(field == "option"){
					if(databaseType == "int" && anyNum.test(value) == false){passed=false;toReturn["Issue"]="Must be an integer.";}
					if(databaseType == "float" && anyFloat.test(value) == false){passed=false;toReturn["Issue"]="Must be a number.";}
				}
			break;
			case "7":
				if(field == "default"){
					if(databaseType == "int" && anyNumOrBlank.test(value) == false){passed=false;toReturn["Issue"]="Must be an integer or blank.";}
					if(databaseType == "float" && anyFloatOrBlank.test(value) == false){passed=false;toReturn["Issue"]="Must be a number or blank.";}
				}
			break;
		}
		if(passed) return false;
		return toReturn;
	}
	$( "#tableList" ).sortable({
		items: "tr:not(.noSort)",
		axis: "y",
		delay: 200,
		distance: 10
	});
	var numAboveZero = /^[0-9]\d*\.?[0]*$/;
	var numAboveZeroOrBlank = /^$|^[0-9]\d*\.?[0]*$/;
	var numAboveOne = /^[1-9]\d*\.?[0]*$/;
	var anyNum = /^[-+]?[0-9]\d*\.?[0]*$/;
	var anyFloat = /^[-+]?([0-9]*\.[0-9]+|[0-9]+)$/;
	var anyNumOrBlank = /^$|^[-+]?[0-9]\d*\.?[0]*$/;
	var anyFloatOrBlank = /^$|^[-+]?([0-9]*\.[0-9]+|[0-9]+)$/;
</script>