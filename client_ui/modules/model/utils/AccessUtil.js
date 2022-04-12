define([    
], function(dom,array,lang){
	return {
    	NONE:0,
    	READ:1,
    	COPY:2,
    	EDIT:3,
		layerTypes:['none','read','copy','edit'],
		
		GetTypeByString:function(typeName) {
			return this.layertTypes.indexOf(typeName.toLowerCase());
		},
		GetTypeString:function(typeId) {
			if(this.layerTypes.length-1 < typeId) return null;
			return this.layerTypes[typeId];
		},		
		
    };
});