<style type="text/css">
.frame::-webkit-scrollbar {
	-webkit-appearance: none;
	width: 10px;
	height: 20px;
}

.frame::-webkit-scrollbar-thumb {
	border-radius: 8px;
	border: 2px solid white;
	/* should match background, can't be transparent */
	background-color: rgba(0, 0, 0, .5);
}

.frame {
	background: #eeeeee;
	padding: 0;
	width: 8in;
	overflow-y: auto;
	overflow-x: hidden;
	margin-left: 30px;
	border: 1px solid black;
}

.layers {
	margin-top: 5px;
	list-style: none;
	padding: 0;
	padding-left: 1em;
}

.off {
	opacity: 0.5;
	visibility: hidden;
}

lh {
	font-style: italic;
}

p {
	margin-left: 30px;
}

.indent {
	margin-left: 40px;
}
</style>

<script type="text/javascript">

var capabilities=null;

function check_form(formdata) {
	dot=/[^ _A-Za-z0-9]/;
	name = formdata.elements['name'].value;
	checkname = name.match(dot);
	if(checkname){     
		alert('Names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
		return false;	 
		} 
	if (!formdata.elements['name'].value) { 	
		alert('Please enter a name for the file.');
		return false;
		}
		
	if (!formdata.elements['getMapURL'].value) {	
		alert("Please enter a Get Map URL, or use the layer list to generate one automatically.");
		return false;
		}
    
	var returnform;
	var name = document.forms[0].elements['name'].value;
	
	url='./?do=wapi.layer.is_unique&name='+name;
	//alert(url);
	$.ajax({       
		url: url,       
		async: false,       
		dataType: "json",      
		success: function (data) { 
			if (data.response==true){
				alert("A layer with the same name already exists.  Please enter a unique layer name.");
				returnform=false;															
				}else if(data.response==false){
			  	//alert("layer doesn't exist");
				returnform=true;
			} 																
		}		
	});
	//alert(returnform);
	return returnform;
}





function basicAuthentication(url){

//if this is a geoeye layer, add username and password
	if (url){
    	geoeye = url.search(/eyeq2\.geoeye\.com/i); //\/EyeQService\/wms\/site\/2618/i);
		// alert(geoeye);
		if(geoeye>=0){
		//username and password can be parameters passed into this function from user input fields
		//however we'd have to code it in to replace any "[]" square brackets with the unencoded value (ex:%5B below) otherwise passing it to the proxy dispatcher doesn't work correctly.
			url='https://ttierney:6cartograph%5B@eyeq2.geoeye.com/EyeQService/wms/site/2618';
			return url;
		}
	}
}




function specialParams(url){	
//if this is a digiglobe layer, add connect id and featureprofile
//param names and values can be input fields in import form	


	if( ! url ) return url;
	
	urlSegs = url.split('?');
	
	if( urlSegs.length > 1 ) {

		var urlParams = urlSegs[1].split('&');
		$.each(urlParams,function(key,item){
			keyval = item.split('=');
			key = keyval[0];
			uKey = key.toUpperCase();
			if( uKey =='REQUEST') return;
			if( uKey == 'SERVICE') return;
			if( uKey =='VERSION') return;
			if( uKey =='WMTVERSION') return;
			if( uKey =='FORMAT') return;
			if(keyval.length>1) params[key] = keyval[1];
			else params[key] = 1; 
		});
	}

	
	/*digiglobe = url.search(/services\.digitalglobe\.com/i); //https://services.digitalglobe.com/mapservice/wmsaccess
	
	if (digiglobe>=0){
		
		url=url + '&CONNECTID=58a1d1e1-7089-4238-acc3-1276ef9020b5&featureProfile=Currency_Profile';
		return url;
	}*/
	url = urlSegs[0]+'?';
	$.each(params,function(param,val) {
		if(param=="") return;
		url+='&'+param+'='+val;
	});
	return url;
	
}





function updateWMSlayers() {
		// a quick fix for the WMS URL, trim extra spaces
		// commented the following out-really we don't want any whitespace characters, right?  not just at the beg/end of the url
		// document.forms[0].elements['getCapURL'].value = document.forms[0].elements['getCapURL'].value.replace(/^\s*/, "").replace(/\s*$/, "");
	    var url = document.forms[0].elements['getCapURL'].value;
	    
		if (!url){
			return;
		}
		url = url.replace(/^\s*/, "");
		url = url.replace(/\s*$/, "");
		urlSegs = url.split('?');
		
 		//url = url.replace(/\?.*/,"");
		basicCheck=basicAuthentication(url);

		if (basicCheck){url=basicCheck;}

		if(urlSegs.length=== 0) url+="?";

		specialParamCheck=specialParams(url);
		url+= '&SERVICE=WMS&REQUEST=GetCapabilities';
		if (specialParamCheck){url=specialParamCheck;}
	//	alert(url);
		
		document.forms[0].elements['getCapURL'].value = url;
		
		// when the layer list is populated, which layer should be selected?
		// var selected_layer = <!--{$baselayer|json_encode}-->;
		
		// purge the existing layer list
		$('#layerpicker').html("");
		// toggle the layer selector with the Loading spinner
		$('#layerframe').hide();
		$('#loading').show();
		$('#submit').hide();
		// get the URL and do some AJAX
		
		var proxyurl = '.?do=proxy&service=capabilities&url=' + encodeURIComponent(url);
		//alert(proxyurl);
		console.log(proxyurl);
		$.get(proxyurl,
			function(data) { 
				//console.log(data);
				layerName=$(data).find("Capability > Layer > Layer > Name").text();
				//console.log(layerName);
				//childrenName=$(data).children;

//				console.log(childrenName);
				capabilities=$(data);
			
				// xmlDoc = $.parseXML(capabilities),
				// xmldata=capabilities.responseXML;
// 				xmltext = capabilities.responseText;
			
				 function makeToc($xml) {
					// variable to accumulate markup
					var markup = "";
					var count = 0;
					
				
					// worker function local to makeToc
					function processXml() {
						console.log("called processxml")
						console.log($(this))
						

						function singleLyr(y){
							x=y.contents().filter("Title").first().text();
							layername=y.contents().filter("Name").first().text();
							console.log("single layer called" + x + " : " + layername);
							if (layername){
							markup += "<li style='padding-left:1em'><input type='checkbox' onChange='layerHandler(this.value,this.checked)'" + " value='" + layername + "'>" + x + "</li>"; //onChange='layerHandler(this.value, this.checked)'" + >" 						
							count = 1;
							undent = 1;
							}
						}
						
						
						console.log("Layer title " + $(this).find("Layer > Title").text());
												
						var layerChildren=$(this).find("Layer > Title").text().length;
						//var layerChildrenSpace=$(this).find("Layer > Name").text().indexOf(' ');
						 				
						console.log(layerChildren);
						
 						if ((layerChildren > 0) && $(this).contents().filter("Name").length > 0){
 								
 								console.log("group layer title with name" + $(this).contents().find("Title").first().text());
 								
 								layername=$(this).contents().filter("Name").first().text();
 								groupLayer =$(this).contents().filter("Title").first().text();
 								console.log(groupLayer + ":" + layername); 	

  								markup += "<ul class='layers'><input type='checkbox' onChange='layerHandler(this.value,this.checked)'" + " value='" + layername + "'><lh>" + groupLayer + "</lh>"; 	

 								//for each child, processXml 
 								$(this).contents().filter("Layer").each(processXml);	
 								markup += "</ul>"
 						//if children but no names
 						} else if (layerChildren > 0 && $(this).contents().filter("Name").length ==0){
 								
 								groupLayer =$(this).contents().filter("Title").first().text();
 								console.log("Group layer but no name " + groupLayer); 	
 				
								markup += "<ul class='layers'><input type='checkbox' class='off' disabled='disabled' value='" + "" + "'><lh>" + groupLayer + "</lh>"; 	
 						
 								//for each child, processXml 
 								console.log($(this).contents().filter("Layer"));
 								$(this).contents().filter("Layer").each(processXml);
						markup +="</ul>"
						//if no children with names are found treat as single lyr
						//} else if ($(this).find("Layer > Layer > Name").length < 1 || 
						}else if ($(this).find("Layer > Name").text().length < 1){ 
							console.log("no sublayer found");
							singleLyr($(this));
							//If the child node is not named "Layer", recurse/cycle through children until you find a Layer node.
 						}
					}//end processxml
					console.log($xml.find("Layer"))
					$xml.find("Layer").first().each(processXml);
					//alert(markup);
					return markup;
 				}//end maketoc
			
			var tocOutput = makeToc($(data));
			//alert("var TOCOutput" + tocOutput);
			// call worker function on all children only checks first level of children.
			
			$("#loading").hide();
			$("#layerframe").show();
			$("#submit").show();
			$("#layerpicker").html(tocOutput);
	
	});


//  error:$(function(xhr, err, other){
//  alert("ERROR:" + xhr+' '+err+' '+other);}); 
}

currentLayers=[];

function layerHandler(layername, layerstate) {
	
	offset=$.inArray(layername, currentLayers);
	hasentry=offset>-1;
	if (layerstate && hasentry){
		//do nothing
	}else if(layerstate && !hasentry){
		currentLayers.push(layername);
	}else if(!layerstate && hasentry){
		currentLayers.splice(offset,1);
	}else if(!layerstate && !hasentry){
		//do nothing
	}
	//alert(currentLayers.join(','));
}
var params = {};
function generateWMSURL(layerpicker) {

//get the URL and do some AJAX
//are these necessary? SFOO
	// var getCapabilitiesurl = document.forms[0].elements['getCapURL'].value;
// 	getCapabilitiesurl = '.?do=proxy&url=' + encodeURI(getCapabilitiesurl);
	
	
	//return the first *element* node, which should have the WMS version attribute in it
	function get_firstchild(){
		var xhr=capabilities;
		console.log($(capabilities[0]).val());
		
		var version=xhr.find("WMT_MS_Capabilities").attr("version");
		if (!version){
			version = xhr.find("WMS_Capabilities").attr("version");
		}	
		
		if (version=='1.1.1' || version =='1.1.0'){
			
			var bbox1 = xhr.find('LatLonBoundingBox').first().attr('minx');
			var bbox2 = xhr.find('LatLonBoundingBox').first().attr('miny');
			var bbox3 = xhr.find('LatLonBoundingBox').first().attr('maxx');
			var bbox4 = xhr.find('LatLonBoundingBox').first().attr('maxy');
			xdiff = bbox3-bbox1;
			ydiff = bbox4-bbox2;

			

 		} else if (version=='1.3.0') {
			var bbox1 = xhr.find('southBoundLatitude').first().text();
			var bbox2 = xhr.find('westBoundLongitude').first().text();
			var bbox3 = xhr.find('northBoundLatitude').first().text();
			var bbox4 = xhr.find('eastBoundLongitude').first().text();
			minx = Math.min(bbox4,bbox2);
			miny = Math.min(bbox3,bbox1);
			maxx = Math.max(bbox4,bbox2);
			maxy = Math.max(bbox3,bbox1);
			xdiff = maxx -minx;
			ydiff = maxy - miny;
		}
		isXMax = Math.max(xdiff,ydiff) == xdiff;
		imgWidth = 500;
		imgHeight = 500;
		if(isXMax) {
			imgHeight = imgWidth * ydiff/xdiff;
		} else {
			imgWidth = imgHeight * xdiff/ydiff;
		}
		
		
		
		
		
		document.forms[0].elements['bboxparam'].value = bbox1 + ',' + bbox2 + ',' + bbox3 +','+ bbox4;
		
		
		
		var getMapURL = "";
		//alert(document.forms[0].elements['bboxparam'].value);
 		try{
			getMapURL = capabilities.find("Get > OnlineResource").first().attr("xlink:href");
			//getMapURL = getMapURL.replace(/\?.*/, "");
			getMapURL = getMapURL.replace(/\s*/, "");		
			//alert(getMapURL); 
			}
		catch(err){
			return alert('Unable to find Get Map URL for WMS.'); 
		}
		//if this is a geoeye layer, add username and password
		basicCheck=basicAuthentication(getMapURL);
		if (basicCheck){getMapURL=basicCheck;}
		//alert("basic check" + getMapURL);
		getMapURL=specialParams(getMapURL);

		prefix = (getMapURL.indexOf('?') >= 0 ) ? '' : '?';
		getMapURL += prefix+'&REQUEST=GetMap' + '&VERSION=' + version + '&BBOX=' + bbox1 + ',' + bbox2 + ',' + bbox3 + ',' + bbox4 + '&LAYERS=' + currentLayers.join(',');

		//alert("added params" + getMapURL);
		//  if this is a digiglobe layer, add connect id and featureprofile
		//alert("spec check" + getMapURL);
		//if (specialParamCheck){getMapURL=specialParamCheck;}
		//alert("yes spec params" + getMapURL);
		document.forms[0].elements['getMapURL'].value=getMapURL;
		//alert(document.forms[0].elements['getMapURL'].value=getMapURL);
		$('submit').show();
		imgURL = getMapURL + '&WIDTH='+imgWidth+'&HEIGHT='+imgHeight+'&format=image/png';
		if(version=='1.1.1') {
			imgURL+= "&SRS=EPSG:4326";
		} else {
			imgURL += "&CRS=EPSG:4326";
		}
		$('#sampleURL').text(imgURL).css('max-width',imgWidth);
		$('#sampleURL').text(imgURL).css('width',imgWidth);
		$('#sampleURL').text(imgURL).css('word-wrap','break-word');
		$('#sampleURL').text(imgURL).css('padding','0');
		$('#sampleURL').text(imgURL).css('margin','0');
		$('#sampleURL').text(imgURL).css('font-size','10pt');
		
		
		
		imgURL = encodeURIComponent(imgURL);
		$('#sample').attr('src','?do=wapi.layer.wms_helper&cmd=get_img&url='+imgURL).attr('height',imgHeight).attr('width',imgWidth);
		
		
	}
	var get_version=get_firstchild();

}


jQuery(document).ready(
	$(function () {$('#loading').hide();}))

// not working:
// window.onload = updateWMSlayers;
</script>
<!--{$subnav}-->

<div style="position:absolute;right:0px;;left:850px;padding-right:10px;padding-top:10px;max-width:550px;">
<img id="sample" />
<p id='sampleURL'></p>
</div>
<div >
<form action="." method="post" onSubmit="return check_form(this);"><input
	type="hidden" name="do" value="layer.io" /> <input type="hidden"
	name="mode" value="import" /> <input type="hidden" name="format"
	value="wms" /> <input type="hidden" name="stage" value="2"> <input
	type="hidden" name="bboxparam" value="" /> <font size="2">
<p class='instruction'>This utility allows you to use a remote raster (Web Map Service)
layer for use in projects.</p>
<p>You may enter a WMS in a few ways:</p>

<ul class="indent" class='wrapped'>
	<li>Enter the base URL of the WMS service.</li>
	<li>Enter the URL of a WMS service's Capabilities document.</li>
	<li>Enter the URL of a valid GetMap request, including LAYERS=, WIDTH=,
	HEIGHT= and other parameters.</li>
</ul>
<!--{if RequestUtil::Get('layerid', false)}-->
	<input type="hidden" name="layerid" value="<!--{RequestUtil::Get('layerid', false)}-->"/>
<!--{else}-->
	<p>Name:<br />
	<input type="text" name="name" style="width: 3in;" value="" /></p>
<!--{/if}-->
<p>Base URL of the WMS service:<br />
<input type="text" name="getCapURL" style="width: 7in;" value="" />&nbsp;<input
	type="button" style="width: 1in;" value="Load &gt;&gt;"
	onClick="updateWMSlayers()" /> &nbsp;<br>
<br>
Select a Layer:


<div class="frame" id="layerframe" style="height: 1.9in;min-width:500px;">
<div name="wmslayer" multiple="multiple" id="layerpicker"
	style="height: 100%; width: 8in;"></div>
</div>

<p><input type="button" value="Generate URL &gt;&gt;"
	onClick="generateWMSURL(layerpicker);"></p>
<p>Generated URL. Alternately, you can paste in the URL of a working
image request.<br />
<input type="text" name="getMapURL" id="getMapURL" style="width: 8in;"
	value="" /></p>

<p><input id="submit" type="submit" name="submit" value="Submit"
	style="width: 2in;" /></p></form>
</div>
</div>
