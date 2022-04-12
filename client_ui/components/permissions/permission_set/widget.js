define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'dojo/topic',
        'sl_components/sl_toggle_button/widget',
        "dojo/text!./templates/permission_set.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		topic,
    		sl_toggle_button, 
    		template){
        return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Our template - important!
            templateString: template,
            buttons:null,
            value:null,
            created:false,
            postCreate:function() {
            	this.buttons = [this.none_button, this.view_button, this.edit_button, this.copy_button, this.create_button, this.save_button, this.trash_button,this.all_button];
            	
            	topic.subscribe('sl_toggle_button/value_change',this.HandleToggle.bind(this));
            	this.created = true;
            	
            	if(this.value) this.setValue(this.value);
            },
        	setValue:function(val) {
        		
        		this.value = +val;
        		if(!this.created) return;
            	for( var i in this.buttons) {
            		var button = this.buttons[i];
            		if(val===null) { 
            			button.disable();
            			continue;
            		}
            		if(i==0) {
            			if(val==0) {
            				for(var i2 in this.buttons) {
            					this.buttons[i2].toggle(false);
            				}
            				button.toggle(true);
            				this.all_button.toggle(this.value==+this.all_button['data-value']);
        					return;
            			}
            			button.toggle(false);
            		} else {
            			button.toggle((+button['data-value'] & +this.value) == +button['data-value']);                  			
            		}
            		 
            	}
            	this.all_button.toggle(this.value==this.all_button['data-value']);
        	},
        	HandleToggle:function(data) {
        		
        		var isMemberButton = false;
        		if(data.toggleButton == this.all_button) {
        			if(data.isOn) {
        				this.setValue(this.all_button.value);
        				topic.publish('permission_set/value_change',{permission:this.value,src:this});
        				return;
        			} else {
        				this.all_button.toggle(true);
        				topic.publish('permission_set/value_change',{permission:this.value,src:this});
        				return;
        			}
        			
        		}
        		
        		for( var i in this.buttons) {
        			if(this.buttons[i] == data.toggleButton) {
        				isMemberButton = true;        				
        			}
        			
        		}
        		if(!isMemberButton) return;
        		
        		if(data.isOn) {
        			if(+data.value == 0) {
        				this.setValue(0);
        				this.all_button.toggle(this.value==+this.all_button.onValue);
        				topic.publish('permission_set/value_change',{permission:this.value,src:this});
        				return;
        			}
        			this.none_button.toggle(0);
        			if((this.value & +data.value) != +data.value) this.value += (+data.toggleButton.onValue);
        			
        			
        		} else {
        			if((this.value & +data.value) == +data.value) this.value-= (+data.toggleButton.onValue);
        			if(this.value == 0) this.setValue(0);
        		}
        		
        		this.all_button.toggle(this.value==+this.all_button.onValue);
        		
        		topic.publish('permission_set/value_change',{permission:this.value,src:this});
        	}
        
        
        });
});
