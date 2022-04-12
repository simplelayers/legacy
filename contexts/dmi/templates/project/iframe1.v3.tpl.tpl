<!--{$subnav}-->
<script language='javascript'>
	var layerFields = <!--{$projectLayerFields}-->

	function setFieldOptions(layerId) {
		var searchField = document.getElementById('search_field');
		for( var i=searchField.options.length;i>-1;i--) {
			searchField.remove(i);
		}
		$.each(layerFields[layerId],function(key,val) {
			
			var option=document.createElement("option");
			option.text = val;
			option.value = val;
			try
			{
			  // for IE earlier than version 8
				searchField.add(option,searchFields.options[null]);
			} catch (e) {
				
				searchField.add(option,null);
			 }
		});
	}
	$(function(){
		var searchLayer = document.getElementById('search_layer');
		//alert(searchLayer.selectedIndex);
		for( var i=0; i< searchLayer.options.length;i++) {
			//alert(searchLayer.options[i].text);
		}
		//alert('layer:'+$('#search_layer option:selected').value());
		if(searchLayer.selectedIndex < 0) return; 
		setFieldOptions(searchLayer.options[searchLayer.selectedIndex].value);
	});
</script>
<div style="font-size:10pt;width:575;clip:auto;" >
<form name="theform">
<input type="hidden" name="do" value="project.iframe2"/>
<input type="hidden" name="id" value="<!--{$project->id}-->"/>
<h3 class='section_head'>Select the map application to embed:</h3>
<div style="width:auto;">
	<select name="applicaiton" id="mapOptions"  onchange="handleChange()" >
	  <option value="SimpleMap">Simple Map</option>
	  <option value="MainMap">Main Map</option>
	  <option value="CG3Viewer">SimpleLayers Viewer</option>
	</select>
	<div id="output" style="display:inline-block;max-width:400px" class='instruction'></div>
</div>
<h3 class='section_head'>Enter the dimensions to use for your embedded map.</h3>
<div class='instruction'>You may enter a number or a percentage (i.e. 250 or 100%)</div>

<div>
<span style="width:200;" >Width: <input onKeyUp="updateEmbedCode()" id="width" type="text" style="width:0.5in" name="width" value="735" /></span>
<span style="width:200;">Height: <input onKeyUp="updateEmbedCode()" id="height" type="text" style="width:0.5in" name="height" value="735" /></span>
</div>

<!-- Background Color Code HTML Start -->
<h3 class='section_head'>Background Color</h3>

<!--{$colorpicker_background}-->
<!-- Background Color Code HTML End -->
<h3 class='section_head'>Special Behavior</h3>
<input id='context' type='hidden' name='context' onkeyup='updateEmbedCode()' value='<!--{$orgId}-->' ></input>
<input id="query_url" type="checkbox" name="query_url" onchange="updateEmbedCode()"  /> 
Query-URL: <span class='instruction'>If enabled when your maps are clicked and a feature with a url is detected, the url will be opened in a new window.</span>

<div id="searchCriteria" style="display:none">
	<input id="default_search" type="checkbox" name="default_search" onchange="handleChange()" />
	Default Search: <span class='instruction'>if enabled, the search interface will be shown initially and the selections below will be used as the default search layer and criteria.</span>
	<span id="simpleSearchHide">
	<br/>&nbsp;&nbsp;&nbsp;<input id="simple_mode" type="checkbox" name="simple_mode" onchange="updateEmbedCode()" />
	Minimal Search: <span class='instruction'>Interface with one criteria based on the search layer and its' default criteria.</span>
	<br/>&nbsp;&nbsp;&nbsp;<input id="auto_search" type="checkbox" name="auto_search" onchange="updateEmbedCode()" />
	Auto-search: <span class='instruction'>Initiate search on default criteria.</span>
<div>
<span style="float:left;width:110px" >Layer:</span>
<select id="search_layer" name="search_layer" onChange="setFieldOptions(this.value);updateEmbedCode()">
<!--assign var=selected value='selected'-->

<!--{section name=i loop=$layerlist}-->
  <!--{assign var=projectlayer    value=$layerlist[i]}-->
  <!--{assign var=projectlayerid  value=$projectlayer->id}-->
  <!--{assign var=layer           value=$projectlayer->layer}-->
  <!--{assign var=layerid         value=$layer->id}-->
  <!--{assign var=layername         value=$layer->name}-->
  <!--{if $layer->name ne ''}-->
  <option value="<!--{$layerid}-->" selected="<!--{$selected}-->"><!--{$layername}--></option>
	  <!--{if $selected eq 'selected'}-->
	  	<!--{assign var=selected value=''}-->
	  <!--{/if}-->
  <!--{/if}-->
<!--{/section}-->
</select>
</div>
<p>
Search Criteria: <select name="search_field" id="search_field" onChange="updateEmbedCode()"  ></select>
<select id="comparison" name="comparison" onChange="updateEmbedCode()" >
<option label="equals" value="==">equals</option>
<option label="is greater than" value="&gt;">is greater than</option>
<option label="is greater than or equal to" value="&gt;=">is greater than or equal to</option>
<option label="is less than" value="&lt;">is less than</option>
<option label="is less than or equal to" value="&lt;=">is less than or equal to</option>
<option label="contains" value="contains">contains</option>
<!--<option label="is null or blank" value="isnull">is null or blank</option>-->
</select>
<input id="searchVal" type="text" onKeyUp="updateEmbedCode()" />
</p>
</span>
</div>

<h3 class='section_head'>Embed Code</h3>
<div class='instruction'>The iframe code below may be copied and pasted it into your webpage<div>

<textarea name="embedCode" id="embedCode" style="width:100%;" rows="5" ></textarea>

</div>
<input style="margin-top:10px;" type="button" value="Test in new window" onclick="test()" />

</form>

<script language="javascript" >
	var messages= new Array();
	var baseURL = "<!--{$baseURL}-->";
	var project = "<!--{$project->id}-->";
	var embedData = "";
	
	messages["SimpleMap"] = "A very simple interface with just the map, and tooltip enabled. Good for informational display.";
	messages["MainMap"] = "Having the ability to navigate on the map and some layer manipulation abilities, this interface is still simple and the map is predominant.";
	messages["CG3Viewer"] = "This is the same viewer used when you view a map through Cartograph. Note that embedded maps only provide limited public access and not all features will be available.";
	
	function handleChange() {
		var message = messages[document.getElementById('mapOptions').value];
		$("#output").html(message);
		document.getElementById('searchCriteria').style.display='none';
		var frameWidth = document.getElementById('width');
		if( document.getElementById('mapOptions').value == "CG3Viewer" ) {
			frameWidth.value = 975;
			if(<!--{count($layerlist)}-->) {
			document.getElementById('searchCriteria').style.display="block";
			}
		}
		if($('#default_search:checked').length ){
			$('#simpleSearchHide').css('display', 'inline');
		}else{
			$('#simpleSearchHide').css('display', 'none');
		}
		updateEmbedCode();
	}
	
	function updateEmbedCode() {
		var baseURL = this.baseURL;// document.location.href.substring(0,document.location.href.indexOf("?"));
		
		var frameWidth = document.getElementById('width').value;
		var frameHeight = document.getElementById('height').value;
		var queryUrl = document.getElementById('query_url');
		
		var backgroundColor =  $('#sample_backgroundColor').css('background-color');
		if(mapBackground == "trans"){
			backgroundColor = "trans";
		}else{
			backgroundColor = rgb2hex(backgroundColor);
		}
		
		var appURL = baseURL+'app/flexapp/';
		appURL+="application:"+document.getElementById('mapOptions').value;
		appURL+="/project:"+project;
		appURL+="/embedded:1"
		appURL+="/bgcolor:"+backgroundColor;
		appURL+="/width:"+escape(frameWidth);
		appURL+="/height:"+escape(frameHeight);
		
		//var embed = "<script type=\"text/javascript\" src=\""+baseURL+"?do="+cmd+"&project="+project+"&application="+document.getElementById('mapOptions').value+"&embedded=1"+"&bgcolor="+backgroundColor+"&width="+frameWidth+"&height="+frameHeight;
		var embed = "<iframe width=\""+frameWidth+"\" height=\""+frameHeight+"\" src=\""+appURL;
		if( queryUrl.checked) {
			embed+="/query_url:1";
		} else {
			embed+="/query_url:0";
		}
		if($('#default_search:checked').length ) 
			{
				var searchField = document.getElementById('search_field');
				if( searchField.selectedIndex < 0 ) searchField.selectedIndex=0;
				embed+= "/search_layer:"+document.getElementById('search_layer').options[document.getElementById('search_layer').selectedIndex].value;
				var selectedField = searchField.options[searchField.selectedIndex];
				
				if(selectedField.text != ""){
					embed+= "/criteria:"+selectedField.value;
					embed+= ";"+document.getElementById('comparison').options[document.getElementById('comparison').selectedIndex].value;
					embed+= ";"+document.getElementById('searchVal').value;
				}
			}
		
		if( $('#simple_mode:checked').length || $('#auto_search:checked').length)	embed+= "/search_mode:";
		if( $('#simple_mode:checked').length ){ embed+= "simple";}
		if( $('#auto_search:checked').length ){ embed+= "|auto";}
		embed+="\"";
		//embed+="><\/script>";
		embed+="><\/iframe>";
		
			
		embedData = embed;
		document.getElementById('embedCode').value = embedData;
		//embedCode.innerHTML = "&lt;textarea style='width:100%;height:100%;' text=\"hi\" /&gt;";
	}
	
	
	function test() {
		var win = window.open();
		var embed = $('#embedCode').val();
		win.document.open();
		win.document.write(embed);
		win.document.close();
	}
	
	var hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 

	//Function to convert hex format to a rgb color 
	function rgb2hex(rgb) {
		start = rgb.indexOf("(") +1;
		rgb = rgb.substr(start,rgb.length-start-1);
		rgb = rgb.split(",");
		return "" + hex(rgb[0]) + hex(rgb[1]) + hex(rgb[2]);
	}

	function hex(x) {
		return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
	}

	$('#sample_backgroundColor').css('background-color', "#FFFFFF");
	handleChange();
</script>





</div>
