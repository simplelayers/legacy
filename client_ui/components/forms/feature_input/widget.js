define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-construct",
        "dojo/query",
        'dijit/form/TextBox',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'dojo/json',
        'dojo/topic',
        'sl_modules/WAPI',
        'sl_components/forms/criterion/widget',
        'sl_components/sl_toggle_button/widget',    
        'sl_components/selectors/attribute_selector/widget',
        'sl_components/selectors/operator_selector/widget',
        'sl_components/icon/widget',
        'sl_modules/sl_URL',
        'sl_modules/sl_Sorts',
        'sl_modules/Pages',
        'sl_modules/WAPI',
        "dojo/text!./templates/form.tpl.html"],
    function(declare,
    		on,
    		dom,
    		domAttr,
    		domCon,
    		query,
    		textbox,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		json,
    		topic,
    		sl_wapi,
    		criterion,
    		sl_button,
    		attr_sel,
    		operator_sel,
    		sl_icon,
    		sl_url,
    		sl_sorts,
    		pages,
    		wapi,
    		template){
        return declare('sl_components/forms/feature_search',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "feature_search",
            problemId:null,
            startState:null,
            reporter:null,
            reporterName:null,
            templateString: template,
            firstFilter:null,
            filters:null,
            constructor:function(args) {
            	
            	return this;
            },
            postCreate:function() {
            	//this.firstFIlter.HandleRemove();
            	this.filters = [];
            	this.filters.push(this.firstFilter);
            },
         
          
        });
});
