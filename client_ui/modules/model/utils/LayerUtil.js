define([
    // The dojo/dom module is required by this module, so it goes
    // in this list of dependencies.
], function(dom,array,lang){
    // Once all modules in the dependency list have loaded, this
    // function is called to define the demo/myModule module.
    //
    // The dojo/dom module is passed as the first argument to this
    // function; additional modules in the dependency list would be
    // passed in as subsequent arguments.
 
    
    // This returned object becomes the defined value of this module
    return {

    	LAYERTYPE_NONE:0,
		LAYERTYPE_VECTOR:1,
		LAYERTYPE_RASTER:2,
		LAYERTYPE_WMS:3,
		LAYERTYPE_ODBC:4,
		LAYERTYPE_RELATIONAL:5,
		LAYERTYPE_COLLECTION:6,
		LAYERTYPE_SMART_LAYER:7,
		LAYERTYPE_RELATABLE:8,
		
		GEOMTYPE_UNKNOWN: 0,
		GEOMTYPE_POINT: 1,
		GEOMTYPE_POLYGON: 2,
		GEOMTYPE_LINE : 3,
		GEOMTYPE_RASTER : 4,
		GEOMTYPE_WMS : 5,
		GEOMTYPE_COLLECTION : 6,
		GEOMTYPE_RELATABLE : 7,
		
		layerTypes:['none','vector','raster','wms','odbc','relational','collection','smart_layer','relatable'],
		geomTypes:	['unknown','point','polygon','line','raster','wms','collection','relatable'],
		GetTypeByString:function(typeName) {
			var idx = this.layertTypes.indexOf(typeName.toLowerCase());
			console.log(idx);
			return (idx>0)? idx :0;
		},
		GetTypeString:function(typeId) {
			if(this.layerTypes.length-1 < typeId) return null;
			return this.layerTypes[typeId];
		},
		GetGeomTypeByString:function(typeName) {
			return this.geomTypes.indexOf(typeName.toLowerCase());
		},
		GetGeomTypeString:function(typeId) {
			if(this.geomTypes.length-1 < typeId) return null;
			return this.geomTypes[typeId];
		}
		
    };
});