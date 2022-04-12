define(["sl_modules/sl_URL",
        "sl_modules/WAPI",
        "sl_modules/model/sl_Layer",
        "sl_modules/model/sl_LayerManager",
        "sl_modules/model/sl_Project"
        
], function(sl_URL,wapi,sl_Layer,sl_LayerManager,sl_Project){
	return {
		//data:null,
		Layer:function(args) {
			return new 	sl_Layer(args);
		},
		LayerManger: function(args) {
			return new sl_LayerManager(args);
		},
		Project:function(args) {
			return new sl_Project(args);
		},
		GetLayer:function(layerId) {
			//ToDo: Get Layer
		},
		GetLayerAttributes:function(args,handler) {
			wapi.exec('layers/attributes/action:get/',args,handler);
		},
		GetMap:function(mapId) {
			//ToDo:Get A Map
		},
		
	};
	
	
});