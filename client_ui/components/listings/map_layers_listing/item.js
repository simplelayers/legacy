define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        'dijit/form/TextBox',
        "dijit/form/NumberSpinner",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/layer_icon/widget",
        'sl_components/sl_toggle_button/widget',
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
			numericSpinner,
			_WidgetBase, 
			_TemplatedMixin,
			_WidgetsInTemplateMixin,
			layer_icon,
			sl_tbutton,
			template,
			wapi,
			pages
			){
        return declare('listings/map_layers_listing/map_layer_item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	_i:-1,
        	templateString: template,
        	baseClass: "map_layer_item listing_item",
        	editorGroup:null,
        	changeListener:null,
        	state:null,
        	lookupObj:null,
        	list:null,
        	lookupObj:null,
        	ignoreStepperChange:false,
        	parentItems:null,
        	constructor:function(args) {
        		this.list = args.list;
        		
        		this.lookupObj = args.lookupObj;
        		delete args.lookupObj._i;
        		
        		//this.item = args.item;
        		//this.lookup = lookupObj;
        	},
        	Unsubscribe:function() {
        		
        		for(var s in this.subscriptions) {
        			this.subscriptions[s].remove();
        		}
            	
        	},
        	SetItem:function(item) {
        		this.item = item;
        		if(this.parentLayers) {
        			domClass.add(this.domNode,'has_parent_items');
        			domClass.remove(this.domNode,'parentless');
        		}
        		this.move_stepper.set('value',0);
        		this.move_stepper.set('constraints',{'min':0,'max':1,'places':0});
            	this.layer_name.innerHTML = this.item.name;
            	var icon_type = this.item.typeLabel;
            	if(icon_type=='vector') icon_type = this.item.geom;
            	this.type_ico.setValue(icon_type);
            	if(!this.item.sublayers) {
            		
            	} else {
            		this.classification_from_bttn.setTitle("Get classificaiton for ALL sub layers' default classificaitons");
            		this.classification_to_default_bttn.setTitle("Set ALL sub layers classifications as their default classificaiton");
            	}
            	this.visibility_bttn.toggle(this.item.layer_on ==1);
            	
            	if(this.item.typeLabel=='vector') {
            		
            		this.labels_bttn.toggle(this.item.labels.labels_on==1);
            		this.tooltip_bttn.toggle(this.item.tooltip.tooltip_on==1);
            		this.searchable_bttn.toggle(this.item.search_on==1);    
            		this.labels_bttn.setValue(1);
            		this.tooltip_bttn.setValue(1);
            		this.searchable_bttn.setValue(1);
            	} else {
            		this.labels_bttn.toggle(false);
            		this.tooltip_bttn.toggle(false);
            		this.searchable_bttn.toggle(false);    
            		
            		this.labels_bttn.setValue();
            		this.tooltip_bttn.setValue();
            		this.searchable_bttn.setValue();
            	}
            	if(!this.item.sublayers) {
            		domClass.add(this.sublayers_bttn.domNode,'hidden');
            		domClass.remove(this.classification_edit_bttn.domNode,'hidden');
            	} else {
            		domClass.remove(this.sublayers_bttn.domNode,'hidden');
            		domClass.add(this.classification_edit_bttn.domNode,'hidden');
            	}

        	},
        	
        	
        	postCreate:function(){
        		domAttr.set(this.move_stepper.upArrowNode,'title','Move layer above');
        		domAttr.set(this.move_stepper.downArrowNode,'title','Move layer below');
        		
        		this.subscriptions = [];
        		this.subscriptions.push(on(this.move_bttn,'sl_toggle_button/value_change',this.HandleMoveToggle.bind(this)));
        		this.subscriptions.push(on(this.move_stepper,'click',this.HandleIndexChange.bind(this)));
        		this.subscriptions.push(on(this.visibility_bttn.domNode,'sl_toggle_button/value_change',this.HandleChange.bind(this)));
        		this.subscriptions.push(on(this.labels_bttn.domNode,'sl_toggle_button/value_change',this.HandleChange.bind(this)));
        		this.subscriptions.push(on(this.searchable_bttn.domNode,'sl_toggle_button/value_change',this.HandleChange.bind(this)));
        		this.subscriptions.push(on(this.sublayers_bttn.domNode,'sl_toggle_button/value_change',this.HandleChange.bind(this)));
        		
            	/*on(this.labels_bttn.domNode,'sl_toggle_button/value_change',this.HandleChange.bind(this));
            	on(this.labels_bttn.domNode,'sl_toggle_button/value_change',this.HandleChange.bind(this));
            	on(this.labels_bttn.domNode,'sl_toggle_button/value_change',this.HandleChange.bind(this));
            	*/
            	
            	
            	
            	
            	this.SetItem(this.item);
            },
            HandleMoveToggle:function(event) {
            	
            	if(event.isOn) {
            		this.list.BeginMove(this.item);
            		domClass.add(this.domNode,'not_moving');
            		
            	} else {
            		if(domClass.contains(this.domNode,'not_moving')) {
            			this.list.EndMove(-this.item._i);
            		}
            	}
            	
            },
            HandleMoveStart:function(_i) {
            	domAttr.set(this.move_bttn.bttn,'title','Layer being moved, click again to cancel move');        	
            },
            HandleMoveEnd:function() {
            	domAttr.set(this.move_bttn.bttn,'title','Move layer');
            	if(!this.domNode) return;
            	domClass.remove(this.domNode,'not_moving');
            	this.move_bttn.toggle(false);
            	            	
            },
            ResetStepper:function(value) {
            	this.move_stepper.intermediateChanges=false;
            	this.move_stepper.set('value',value);
            	//this.move_stepper.intermediateChanges=true;
            	
            },
            HandleIndexChange:function(event) {
            	
            	
            	var stepVal = this.move_stepper.value;
            	this.ResetStepper(0);
            	
            	var new_i = -this.item._i - stepVal;
            	
            	this.list.EndMove(new_i);
            	//this.list.HandleIndexChange({"old":+Math.abs(this.item._i),"new":new_i});
            	
            },
            HandleChange:function(event) {
            	
            	if(event.toggleButton) {
            		switch(event.toggleButton) {
            		case this.visibility_bttn:
            			this.item.layer_on = event.isOn ? '1' : '0';
            			break;
            		case this.labels_bttn:
            			this.item.labels.labels_on = event.isOn ? '1' : '0';
            			break;
            		case this.tooltip_bttn:
            			this.item.tooltip.tooltip_on = event.isOn ? '1' : '0';
            			break;
            		case this.searchable_bttn:
            			this.item.labels.search_on = event.isOn ? '1' : '0';
            			break;
            		case this.sublayers_bttn:
            			this.list.ShowSublayers(this.item);
            		}
            	}
            	
            },
            HandleButtonClick:function(event) {
            	//topic.publish('role_context_item/show_roles',{context:this.item});
            },
            /*HandleValueChange:function(event) {
            	this.item.isChanged = true;
            	this.UpdateUI();
            	domClass.add(this.domNode,'changed');
            	on.emit(this.domNode,'changed',{bubbles:true,cancelable:true,target:this});
            	
            },
            EditItem:function() {
            	//https://bitbucket.org/denmiroch/jsontools/src/default/JsonSite/
            },
            DeleteItem:function(){
            	this.item.isDeleted = !this.item.isDeleted;
            	this.UpdateUI();
            },
            
            UpdateUI:function() {
            	if (this.item.isDeleted) {
            		domClass.add(this.domNode, 'deleted');
    				//this.del_item_bttn.setColor('red');
    				//domAtt	r.set(this.item_name, 'disabled', true);
    			} else {
    				domClass.remove(this.domNode, 'deleted');
    				var color = this.item.isChanged ? 'blue' : 'normal';
    				//this.del_item_bttn.setColor(color);
    				//domAttr.set(this.item_name, 'disabled', false);
    			}
            	if(this.item.isChanged) domClass.add(this.domNode,'changed');
            },
            MatchItem:function(item) {
            	return (item.planName == this.item.data.planName);
            }*/
        });
});
