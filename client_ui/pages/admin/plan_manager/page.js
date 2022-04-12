define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "sl_components/listings/plans_listing/widget",
        "sl_components/listings/plan_viewer/widget",
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
    		plans_listing,
    		plan_seats_listing,
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
        return declare('sl_pages/admin/plan_manager',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'plan_manager',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	currentView:null,
        	editorGroup:null,
        	constructor:function() {
        		pages.SetPageArg('pageSubnav','admin');
        		pages.SetPageArg('pageTitle','Admin - Plan Manager');
        		
        	},
        	postCreate:function(){
        		this.editorGroup = domAttr.get(this.domNode,'data-editor');
        		
        	}
           

        });
});
