define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'dojo/dom-attr',
        'dojo/parser',
        'dojo/topic',
        'dojo/dom-construct',
        'dijit/layout/ContentPane',
        'dijit/layout/StackContainer',
        "dijit/layout/StackController",
        'sl_components/sl_button/widget',
        'sl_modules/WAPI',
        "dojo/text!./templates/permission_entry.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		domAttr,
    		parser,
    		topic,
    		domCon,
    		contentPane,
    		stackContainer,
    		stackController,
    		sl_button,
    		wapi,
    		template){
        return declare('sl_components/employees/employee',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'permission_item',
        	data:null,
        	templateString: template,
        	isDeleted:false,
        	isAdded:false,
        	postCreate:function(){
        		on(this.del_item_bttn,'click',this.BandleDelete.bind(this));
        		on(this.permission,'change',this.UpdateValue.bind(this));
        		
            },
            setItem:function(data) {
            	this.data = data;
            	this.permission.value = this.data.permission;
            	if(this.data.id === null) this.isAdded = true;
            	if(this.isAdded) this.del_item_bttn.setColor('blue');
            },
            HandleDelete:function(event) {
            	
            },
            UpdateValue:function() {
            	this.data.permission = this.permission.value;
            	on.emit(this.domNode,'sl_permission/permission_item/update',{src:this,value:this.permission.value})
            }
    
            

        });
});

