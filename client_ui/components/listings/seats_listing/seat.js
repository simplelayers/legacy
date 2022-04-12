define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/seat.tpl.html",        
        "sl_modules/WAPI",
        "sl_components/selectors/victem_selector/widget"],
    function(declare,
    		on,
    		topic,
    		domClass,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		template,
    		wapi,
    		sl_selector
    		){
        return declare('role_context_item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "role_context_item listing_item",
        	isDeleted:false,
        	isChanged:false,
        	constructor:function(item) {
        		this.item = item;
        	},
            postCreate:function(){
            	
            	
            	this.item_name.value = this.item.data.seatName;
            	
            	this.role_selector.SetSelection(this.item.data.roleId);
            	//	this.item_name.value = this.item.context;
            	on(this.item_name,'keyup',this.HandleValueChange.bind(this));
            	on(this.role_selector,'change',this.HandleValueChange.bind(this));
            	on(this.del_item_bttn,'click',this.HandleDelete.bind(this));
            },
            HandleButtonClick:function(event) {
            	topic.publish('role_context_item/show_roles',{context:this.item});
            },
            HandleDelete:function(event) {
            	this.DeleteItem();
            	topic.publish('role_context_item/remove_item',{context:this.item});
            },
            HandleValueChange:function(event) {
            	this.item.data.seatName = this.item_name.value;
            	this.item.data.roleId = this.role_selector.GetSelection();
            	this.isChanged = this.item.isChanged = true;
            	
            	domClass.add(this.item_name,'changed');
            	domClass.add(this.role_selector,'changed');
            	
            	this.UpdateUI();
            	
            },
            DeleteItem : function() {
            	if (this.isDeleted) {
    				this.isDeleted = false;
    				
    			} else {
    				this.isDeleted = true;
    				
    			}
            	this.UpdateUI();
    			this.item.isDeleted = this.isDeleted;
    			topic.publish('role_context_item/remove_item', {
    				'node' : this.domNode,
    				'item' : this.item,
    				'widget' : this,    				
    			});
    			return;
    		},
            UpdateUI:function() {
            	if (this.isDeleted) {
            		domClass.add(this.item_name, 'deleted');
    				this.del_item_bttn.setColor('red');
    				domAttr.set(this.item_name, 'disabled', true);
    				
    			} else {
    				domClass.remove(this.item_name, 'deleted');
    				var color = this.isChanged ? 'blue' : 'normal';
    				this.del_item_bttn.setColor(color);
    				domAttr.set(this.item_name, 'disabled', false);
    			}
            }
        });
});
