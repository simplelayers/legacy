define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'dojo/parser',
        'dojo/dom-construct',
        'dojo/topic',
        'dojo/json',
        'dijit/layout/ContentPane',
        'dijit/layout/StackContainer',
        "dijit/layout/StackController",
        'sl_components/sl_button/widget',
        "sl_components/listings/map_layers_listing/widget",
        'sl_modules/WAPI',
        'sl_modules/Pages',
        "dojo/text!./templates/ui.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		parser,
    		domCon,
    		topic,
    		json,
    		contentPane,
    		stackContainer,
    		stackController,
    		sl_button,
    		map_layers_listing,
    		wapi,
    		pages,
    		template){
        return declare('sl_pages/map/layers',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'map_layers ',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	constructor:function() {
        		pages.SetPageArg('pageSubnav','maps');
        		
        	},
           postCreate:function(){
        	 
        	   wapi.exec('wapi/map/load',{'map':pages.GetPageArg('mapId'),'formatOptions':256},this.LayerDataReady.bind(this));
           },
           LayerDataReady:function(result) {

           }
        	
          
        });
});
