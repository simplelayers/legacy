define(["dojo/_base/declare", 
        "dojo/Stateful", 
	    "dojo/dom",
	    "dojo/dom-attr",
	    "dojo/_base/lang",    
	    "sl_modules/sl_URL"             
], function(declare,stateful,dom,domAttr,lang,as){
	return declare("sl.Project",[stateful], {
		data:null,
		constructor:function(args) {
			this.data = args.project;
			lang.mixin(this.args);
		},
		showme:function(){
			//console.log(this.data);
		},
		_bboxGetter:function() {
			return this.data.extents.projected;
		},
		_layersGetter:function() {
			return this.data.layers;
		}
		
	});
});