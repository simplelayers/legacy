define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        'dijit/form/TextBox',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'sl_components/sl_button/widget',
        "dojo/text!./templates/item.tpl.html",
        "sl_modules/WAPI",
        "sl_modules/Pages"
        
        ],
    function(declare,
    		on,
    		topic,
    		domClass,
    		domAttr,
    		textBox,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		sl_button,
    		template,
    		wapi,
    		pages
    		){
        return declare('listings/organizations/org_item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "org_item listing_item",
        	editorGroup:null,
        	changeListener:null,
        	state:null,
        	constructor:function(args) {
        		this.item = args.item;
        		
        	},
            postCreate:function(){
            	
            	this.input_org_name.set('value', this.item.name);            
            	on(this.input_org_name,'keyup',this.HandleValueChange.bind(this));
            	on(this.edit_item_bttn,'click',this.EditItem.bind(this));
            	on(this.del_item_bttn,'click',this.DeleteItem.bind(this));
            	
            },
            
            HandleButtonClick:function(event) {
            	//topic.publish('role_context_item/show_roles',{context:this.item});
            },
            HandleValueChange:function(event) {
            	this.item.name = ''+this.input_org_name.get('value');
            	this.item.isChanged = true;
            	this.UpdateUI();
            	domClass.add(this.domNode,'changed');
            	
            	on.emit(this.domNode,'changed',{bubbles:true,cancelable:true,target:this});
            	
            },
            EditItem:function() {
            	pages.GoTo('?do=organization.info&orgId='+this.item.id,'');
            },
            DeleteItem:function(){
            	this.item.isDeleted = !this.item.isDeleted;
            	this.UpdateUI();
            },
            
            UpdateUI:function() {
            	if (this.item.isDeleted) {
            		domClass.add(this.domNode, 'deleted');
    				this.del_item_bttn.setColor('red');
    				//domAtt	r.set(this.item_name, 'disabled', true);
    			} else {
    				domClass.remove(this.domNode, 'deleted');
    				var color = this.item.isChanged ? 'blue' : 'normal';
    				this.del_item_bttn.setColor(color);
    				//domAttr.set(this.item_name, 'disabled', false);
    			}
            	if(this.item.isChanged) domClass.add(this.domNode,'changed');
            },
            MatchItem:function(item) {
            	return (item.planName == this.item.data.planName);
            }
        });
});
