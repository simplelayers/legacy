define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/item.tpl.html",        
        "sl_modules/WAPI"
        ],
    function(declare,
    		on,
    		topic,
    		domClass,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		template,
    		wapi
    		){
        return declare('plan_item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "plan_item listing_item",
        	isDeleted:false,
        	isChanged:false,
        	editorGroup:null,
        	subscriptions:null,
        	constructor:function(args){//item,lookupObj) {
        		this.item = args.item;
        		this.editorGroup = args.lookupObj.group;
        		
        	},
            postCreate:function(){
            	this.subscriptions = [];
            	this.lbl_item_name.innerHTML = this.item.data.planName;
            	on(this.del_item_bttn,'click',this.HandleDelete.bind(this));
            	on(this.edit_item_button,'click',this.EditItem.bind(this));
            	this.subscriptions.push( topic.subscribe(this.editorGroup+'/changed',this.HandleValueChange.bind(this)) );
            	
            },
            HandleButtonClick:function(event) {
            	//topic.publish('role_context_item/show_roles',{context:this.item});
            },
            HandleDelete:function(event) {
            	this.DeleteItem();
            	topic.publish('plan_item/remove_item',{plan:this.item});
            },
            HandleValueChange:function(event) {
            	if(event.plan.id != this.item.id) return;
            	this.isChanged = this.item.isChanged = true;
            	domClass.add(this,'changed');
            	this.UpdateUI();
            	
            	
            },
            EditItem:function() {
            	topic.publish(this.editorGroup+'/edit_plan',{'plan':this.item});
            },
            DeleteItem : function() {
            	if (this.isDeleted) {
    				this.isDeleted = false;
    				
    			} else {
    				this.isDeleted = true;
    				
    			}
            	this.UpdateUI();
    			this.item.isDeleted = this.isDeleted;
    			
    			return;
    		},
            UpdateUI:function() {
            	this.lbl_item_name.innerHTML = this.item.data.planName;
            	if (this.isDeleted) {
            		domClass.add(this.lbl_item_name, 'deleted');
    				this.del_item_bttn.setColor('red');
    				//domAttr.set(this.item_name, 'disabled', true);
    				
    			} else {
    				domClass.remove(this.lbl_item_name, 'deleted');
    				var color = this.item.isChanged ? 'blue' : 'normal';
    				this.del_item_bttn.setColor(color);
    				//domAttr.set(this.item_name, 'disabled', false);
    			}
            	if(this.item.isChanged) domClass.add(this.domNode,'changed');
            },
            MatchItem:function(item) {
            	return (item.planName == this.item.data.planName);
            },
            destroy:function() {
            	
            	for(var s in this.subscriptions) {
            		sub = this.subscriptions[s];
            		sub.remove();
            	}
            	this.inherited(arguments);
            }
        });
});
