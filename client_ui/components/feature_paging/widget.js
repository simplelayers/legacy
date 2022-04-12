define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'sl_components/sl_button/widget',
        "dojo/text!./templates/feature_paging.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		template){
        return declare('feature_paging',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            templateString: template,
            postCreate:function() {
            	this.featureLabel = 'Feature: ';
        	},
            SetPagingdata:function(featureOffset, featureCount) {
            	this.featureOffset = featureOffset;
            	this.featureCount = featureCount;
            },
            SetFeature:function(layerInfo,feature) {
            	//TODO: Figure out tooltip
            }
           
        });
});
