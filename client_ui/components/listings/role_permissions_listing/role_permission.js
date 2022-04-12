define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/role_permissions.tpl.html",      
        'sl_components/permissions/permission_set/widget',
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		domClass,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		template,
    		perms,
    		wapi
    		){
        return declare('role_permission',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "role_permission listing_item",
        	isDeleted:false,
        	isChanged:false,
        	constructor:function(item) {
        		this.item = item;        		
        	},
            postCreate:function(){
            	this.permission_label.innerHTML = this.item.value.name;
            	this.permissionSet.setValue(this.item.value.value);
            	topic.subscribe('permission_set/value_change',this.ValueChanged.bind(this));
            		
            },
            ValueChanged:function(event) {
            	if(event.src != this.permissionSet) return;
            	this.item.value = event.permission;
            	topic.publish('role_permission/value_changed',{src:this,item:this.item});            	
            }
            
            
        });
});
