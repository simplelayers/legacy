define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-construct",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        "dojo/text!./templates/role_editor.tpl.html"],
    function(declare,
    		on,
    		dom,
    		domAttr,
    		domCon,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		template){
        return declare('sl_components/role_editor',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "role_editor",
            templateString: template,
            postCreate:function(){
            },
            LoadRoles:function() {
            	
            },
            RolesLoaded:function(results) {
            	
            },
            SaveRoles:function() {
            	
            },
            RolesSaved:function(results) {
            	
            },
            ClearList:function() {
            	
            },
            PopulateList:function() {
            	
            }
            
         });
});

