define([    
    "dojo/dom",
    "dojo/dom-attr",
    "dojo/_base/lang",    
    "sl_modules/sl_URL",
   "dojo/_base/xhr"
             
], function(dom,domAttr,lang,sl_url,xhr,as){
	return {
		BBoxStr2Extents:function(bbox_str,view) {
			bbox = bbox_str.split(',');
			for(var i=0; i< bbox.length; i++ ) {
				bbox[i] = parseFloat(bbox[i]);
			}
			return [[bbox[1], bbox[0]],[bbox[3],bbox[2]]];
			
		}
	};
});