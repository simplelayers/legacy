define(["dojo/_base/declare", 
        "dojo/Stateful", 
	    "dojo/_base/lang",
	    "sl_modules/sl_URL",
	    "sl_modules/Pages",
	    "sl_modules/PixoSpatial"
             
], function(declare,stateful,lang,sl_url,pages,pixo){
	return declare("sl.Layer",[stateful], {
		data:null,
		layer:null,
		map:null,
		url:null,
		constructor:function(layerInfo) {
			if(layerInfo !=null) {
				this.data = layerInfo;
				lang.mixin(layerInfo);
			}
			
		},
		AddLayerToMap:function(map) {
			this.UpdateURL();
			this.layer = new L.TileLayer(this.url, { attribution: this.data.details,updateWhenIdle:false,opacity:.5});
			map.addLayer(this.layer);
			
		},
		UpdateURL:function() {
			this.url = sl_url.getAPIPath();
			this.url+= 'layers/render/pLayerId:'+this.data.plid;
			this.url+= '/token:'+pages.GetPageArg('token');
			this.url+= '/opacity:1.0';
			this.url+= '/labels:'+this.data.labels_on;
			this.url+= '/x:{x}/y:{y}/z:{z}';
		},
		
	});
});