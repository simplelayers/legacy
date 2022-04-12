define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        'dojo/query',
        'dijit/form/TextBox',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/item.tpl.html",
        'sl_modules/Pages',
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		domClass,
    		domAttr,
    		query,
    		textbox,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		template,
    		pages,
    		wapi
    		){
        return declare('invite_item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "invite_item listing_item",
        	isDeleted:false,
        	isChanged:false,
        	constructor:function(args) {
        		this.item = args.item;
        	},
            postCreate:function(){
            	
            	if(this.item == null) return;
            	on(this.textbox_name,'keyup',this.HandleValueChange.bind(this));
            	on(this.textbox_title,'keyup',this.HandleValueChange.bind(this));
            	on(this.textbox_organization,'keyup',this.HandleValueChange.bind(this));
            	on(this.textbox_email,'keyup',this.HandleValueChange.bind(this));
            	on(this.add_org_bttn, 'click', this.CreateOrgClick.bind(this));
            	on(this.del_item_bttn,'click',this.DeleteItem.bind(this));
            	if(!pages.sl_permissions.HasPermission(':Organizations:Invites:',pages.sl_permissions.DELETE)) {
            		domClass.add(this.del_item_bttn,'hidden');
            	}
            	this.SetInputs();
            },
            HandleEditItem:function(event) {
            	
            },
            HandleDelete:function(event) {
            	this.DeleteItem();
            },
            HandleValueChange:function(event) {
            	this.item.name = this.textbox_name.get('value');
            	this.item.organization = this.textbox_organization.get('value');
            	this.item.title = this.textbox_title.get('value');
            	this.item.email = this.textbox_email.get('value');
            	domAttr.set(this.link_email,'href','mailto:'+this.item.email);
            	this.item.isChanged = true;
            	event.target.focus();
            	this.UpdateUI();
            	
            },
            SetInputs: function() {
            	this.textbox_name.set('value', this.item.name);
            	this.textbox_organization.set('value',this.item.organization);
            	this.textbox_title.set('value',this.item.title);
            	this.textbox_email.set('value',this.item.email);
            	
            	domAttr.set(this.link_email,'href','mailto:'+this.item.email);
            	
            	this.span_referrer_name.innerHTML=this.item.referrer_name;
            	
            	query('.viewstates',this.domNode).forEach((function(node){
	            	domClass.remove(node,'state_inactive');
	            	if(domClass.contains(node,'invite_'+this.item.status)) {
	            		//domClass.add(node,'state_active');
	            	} else {
	            		domClass.add(node,'state_inactive');
	            	}
	            	
            	}).bind(this) );
            	
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
    		ToggleInput:function(input,onOrOff) {
    			input.disable=true;
    			var node = query('>* input',input.domNode).pop();
    			domAttr.set(node,'disabled',(onOrOff) ? null : 'disabled');
    			//domClass.add(node,'deleted');
    		},
            UpdateUI:function() {
            	if (this.isDeleted) {
            		domClass.add(this, 'deleted');
    				this.del_item_bttn.setColor('red');
    				this.ToggleInput(this.textbox_name,false);
    				this.ToggleInput(this.textbox_organization,false);
    				this.ToggleInput(this.textbox_title,false);
    				this.ToggleInput(this.textbox_email,false);  
    				domClass.add(this.domNode,'deleted');
    			} else {
    				domClass.remove(this, 'deleted');
    				if(this.item.isChanged) domClass.add(this.domNode,'changed');
    				var color = this.isChanged ? 'blue' : 'normal';
    				this.del_item_bttn.setColor(color);
    				this.ToggleInput(this.textbox_name,true);
    				this.ToggleInput(this.textbox_organization,true);
    				this.ToggleInput(this.textbox_title,true);
    				this.ToggleInput(this.textbox_email,true);  
    				domClass.remove(this.domNode,'deleted');
    				//domAttr.set(this.item_value, 'disabled', false);
    			}
            },
            CreateOrgClick:function() {
    			on.emit(this.domNode,'invite_item/create_org',{'cancelable':true,'bubbles':true,item:this.item});
    		}
        });
});
