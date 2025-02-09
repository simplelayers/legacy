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
        'sl_modules/WAPI',
        'sl_modules/Pages',
        'sl_components/listings/layer_import_report/widget',
        'sl_components/sl_button/widget',
        "dojo/text!./templates/work_area.tpl.html"],
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
    		wapi,
    		pages,
    		widget,
    		sl_button,
    		template){
        return declare('sl_pages/import/reports/layers',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'import-reports',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	currentView:null,
        	roles:null,
        	orgId:null,
        	constructor:function() {
				var div = document.createElement('div');
				div.classList.toggle('mainContent',true);
				
        		document.getElementById('page_content').appendChild(div);

				pages.SetPageArg('pageSubnav','data');
        	   	pages.SetPageArg('pageTitle','Data: Import Report');
				
        	},
        	postCreate:function(){
        		domClass.add(this.domNode,pages.GetPageActor());
        	}
        });
});
