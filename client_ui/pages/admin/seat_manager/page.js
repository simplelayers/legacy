define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "sl_components/listings/seats_listing/widget",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'dojo/parser',
        'dojo/dom-construct',
        'dojo/topic',
        'dojo/json',
        'sl_modules/WAPI',
        'sl_modules/Pages',
        "dojo/text!./templates/work_area.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		seats_listing,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		parser,
    		domCon,
    		topic,
    		json,
    		wapi,
    		pages,
    		template){
        return declare('sl_pages/admin/seat_manager',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'seat_manager',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	currentView:null,
        	roles:null,
        	constructor:function() {
        		pages.SetPageArg('pageSubnav','admin');
        		pages.SetPageArg('pageTitle','Admin - Seats Manager');
        	},
        	postCreate:function(){
        		
        	}
        });
});
