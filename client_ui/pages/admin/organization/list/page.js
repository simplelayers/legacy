define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "sl_components/listings/organizations/widget",
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
        'dijit/form/DateTextBox',
        "dojo/text!./templates/work_area.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		org_listing,
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
    		datetextbox,
    		template){
        return declare('sl_pages/admin/organizations_manager',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'organizations_manager',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	currentView:null,
        	editorGroup:null,
        	license:null,
        	planChanged:false,
        	hasLoaded:false,
        	constructor:function() {
        		
        		pages.SetPageArg('pageSubnav','admin');
        		pages.SetPageArg('pageTitle','Admin - Organizations');
        		
        		
        	},
        	postCreate:function(){
        		var perms = pages.sl_permissions;
        		if(!perms.HasPermission('SysAdmin:Organizations:',perms.VIEW)) {
        			pages.GoTo('/');
        			return;
        		}
        		
        		if(pages.pageArgs.refId) { 
        			this.list_orgs.SetDefaults(pages.pageArgs);
        		} else if(pages.pageArgs.cmd) {
        			switch(pages.pageArgs.cmd) {
        			case 'new_org':
        				this.list_orgs.SetDefaults(null); 
        				break;
        			}
        		}
        		
        		on(this.list_orgs.domNode,'organization_listing/org_added',this.HandleOrgAdded.bind(this));
        	},
        	HandleOrgAdded:function(event) {
        		if(event.orgId) {
        			pages.GoTo('/organization/license/orgId:'+event.orgId);
        		}
        	},
        	Refresh:function() {
        		
        	}        	        	          

        });
});
