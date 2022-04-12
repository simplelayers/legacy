define(["dojo/_base/declare",
        "dojo/dom",
        "dojo/dom-attr",
        'dojo/dom-class',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_modules/sl_URL",
        "sl_modules/model/utils/LayerUtil",
        "dojo/text!./templates/icon.tpl.html"
        ],
    function(declare,
    		dom,
    		domAttr,
    		domClass,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		sl_url,
    		layerUtil,
    		template){
        return declare('sl_components/layer_icon',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass : "layer_icon",
            problemId:null,
            startState:null,
            reporter:null,
            reporterName:null,
            templateString: template,
            startClasses:null,
            postCreate:function(){ 
            	this.startClasses = domAttr.get(this.img,'class');
            },
            setValue:function(label) {
            	/*if(!isNaN(parseFloat(layerTypeLabel)) && isFinite(layerTypeLabel)) {
            		var icoType = domAttr.get(this.domNode,'data-sl-ico-type');
            		if(icoType == null) icoType='geom';
            		layerTypeLabel = layerUtil.GetTypeString(layerTypeLabel);
            	}*/
            	
            	var icon = label+'_ico';
            	
            	domClass.remove(this.img);
            	domAttr.set(this.img,'class',this.startClasses);
            	domAttr.set(this.img,'title',label.split("_").pop());
            	domAttr.set(this.img,'src',sl_url.getEmptyImgURL());
            	domClass.add(this.img,icon);
            	
            	/*
            	 var char1 = icon.substr(0,1);
            	var title = icon.substr(1);
            	title = char1.toUpperCase()+title;
            	title = title.split('_').shift() + ' Layer';
            	//domAttr.set(this.domNode,'title',title);
            	*/
            },
            setHREF:function(href) {
            	this.link.href=href;
            },
            ready:function(){
            	parser.parse();
            }

        });
});
