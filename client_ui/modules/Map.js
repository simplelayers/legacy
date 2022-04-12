// In demo/myModule.js (which means this code defines
// the "demo/myModule" module):
 
define([
    // The dojo/dom module is required by this module, so it goes
    // in this list of dependencies.
    "dojo/dom",
    "dojo/json",
    "dojo/_base/array",
    "dojo/_base/lang",
    "sl_modules/WAPI"
], function(dom,JSON,dojo_array,lang,sl_wapi){
    // Once all modules in the dependency list have loaded, this
    // function is called to define the demo/myModule module.
    //
    // The dojo/dom module is passed as the first argument to this
    // function; additional modules in the dependency list would be
    // passed in as subsequent arguments.
 
    
    // This returned object becomes the defined value of this module
    return {
    	getRenderData:function(mapData) {
    		layers = [];
    		opacities =[];
    		labels=[];
    		
    		for(var key in mapData.layers){
    	        if(mapData.layers.hasOwnProperty(key)){
    	        	layer= mapData.layers[key];
    	        	if(layer.layer_on != 1) continue;
    	            layers.push(layer['plid']);
    	            opacities.push(layer.opacity)
    	            labels.push(layer.labels.labels_on);
    	        }
    	    }
    		return {'layers':layers,'opacities':opacities,'labels':labels};
    	},
    	appendRenderURL: function(url, mapData,width,height,token) {
    		var renderData = this.getRenderData(mapData);
    		url+='&layers='+renderData.layers.join()+'&opacities='+renderData.opacities.join()+'&labels='+renderData.labels.join();
			
			return url;
		}
    }
});
