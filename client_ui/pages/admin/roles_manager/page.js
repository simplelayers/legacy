define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        'sl_components/listings/role_context_listing/widget',
        'sl_components/listings/role_listing/widget',
        'sl_components/listings/role_permissions_listing/widget',
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
        "dojo/text!./templates/roles_manager.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		context_listing,
    		role_listing,
    		role_permissions,
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
        return declare('sl_pages/admin/roles_manager',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'roles_manager',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	currentView:null,
        	constructor:function() {
        		pages.SetPageArg('pageSubnav','admin');
        		pages.SetPageArg('pageTitle','Admin - Role Manager');
        		
        	},
        	postCreate:function(){
        		
        		topic.subscribe('role_context_item/show_roles',this.ShowRoles.bind(this));
        		topic.subscribe('role_item/edit_role_permissions',this.ShowPermissions.bind(this));
        		this.currentView = this.view_contextAndRoles;
        		this.ToggleViews(this.currentView);
        		
        		topic.subscribe('role_permissions/go_back/clicked',this.GoToContextAndRoles.bind(this));
        		
        	
        		
            },
            GoToContextAndRoles:function(view) {
            	this.ToggleViews(this.view_contextAndRoles);
            },
            ToggleViews:function(view) {
            	domClass.add(this.view_contextAndRoles,'hidden');
            	domClass.add(this.view_permissions,'hidden');
            	if(view != null) {
            		this.currentView = view;
            	} else {
            		this.currentView = (this.currentView == this.view_contextAndRoles) ? this.view_permissions : this.view_contextAndRoles;
                	
            	}
            	domClass.remove(this.currentView,'hidden');
            },
            ShowRoles:function(event) {
            	
            	//this.roles_context 
            	var selectedContext = event.context;
            	this.roles.SetContext(selectedContext);
            },
	        ShowPermissions:function(event) {
	        	this.ToggleViews();
	        	
	        	this.role_permissions.SetRole(this.roles.GetContext(),event.role);
	        	
	        	//this.role_permissions.Set
	        }
            
                     

        });
});
