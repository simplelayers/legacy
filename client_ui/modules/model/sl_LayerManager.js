define(["dojo/_base/declare", 
        "dojo/Stateful"
        
], function(declare,stateful){
	return declare('sl.LayerManager',[stateful], {
		//data:null,
		layers:null,
		map:null,
		constructor:function(args) {
			this.map = args.map;
			this.layers = [];

			for(var i in args.layers ) {
				
				new_layer = new sl.Layer(args.layers[i]);
				this.layers.push(new_layer);
				var z = args.layers[i].z;
				this.layers[Math.abs(z)] = new_layer;
			}
			for(var i in this.layers) {
				this.layers[i].AddLayerToMap(this.map);
			};
		},
		GetLayerByLId:function(lid) {
			for(var i=0; i < this.layers.length ;i++ ) {
				if(this.layers[i].id == lid) return this.layers[i];
			}
		},
		GetLayerByPLId:function(plid) {
			for(var i=0; i < this.layers.length ;i++ ) {
				if(this.layers[i].id == lid) return this.layers[i];
			}
		}	
		
	
	});
});