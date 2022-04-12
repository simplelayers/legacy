define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/json',
        'dojo/dom-attr',
        'dojo/dom-class',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/selectors/seat_selector/widget",
        "sl_components/listings/plan_viewer/item",
        "sl_components/scrollable_listing/widget",
        "dojo/text!./templates/items.tpl.html",
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		json,
    		domAttr,
    		domClass,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		roleListing,
    		item,
    		listing,
    		template,
    		wapi
    		){
        return declare('listings.plan_viewer',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
           
            // Our template - important!
            templateString: template,
            baseClass: "plan_viewer listing",
            item_obj: item,
            lastAddedItem:null,
            items:null,
            editorGroup:null,
            plan:null,
            state:'',
            displayas:'',
            startPlan:null,
            hasEdited:false,
            planSet:false,
            postCreate:function(){
            	if(!this.plan) domClass.add(this.domNode,'hidden');
            	this.editorGroup = domAttr.get(this.domNode,'data-editor');
            	this.state = domAttr.get(this.domNode,'data-state');
            	this.displayas = domAttr.get(this.domNode,'data-displayas');
            	domClass.add(this.domNode,this.displayas);
            	domClass.add(this.domNode,this.state);
            	topic.subscribe(this.editorGroup+'/edit_plan',this.EditPlan.bind(this));
            	topic.subscribe(this.editorGroup+'/saving',this.Clear.bind(this));
            	topic.subscribe(this.editorGroup+'/plans_loading',this.Clear.bind(this));
            	on(this.advertise_cb,'click',this.HandleAdvert.bind(this));
            	domClass.add(this.advertise_options,'hidden');
            	//on(this.add_bttn,'click',this.AddItem.bind(this));
            },
            HandleAdvert:function() {
            	if(this.advertise_cb.checked) {
            		domClass.remove(this.advertise_options,'hidden');
            	} else {
            		domClass.add(this.advertise_options,'hidden');
            	}
            },
            
            HideWebIntegration:function() {
            	domClass.add(this.web_integration,'hidden');
            },
            SetState:function(state) {
            	
            	this.state = state;
            	var display = '';
            	switch(state) {
            	case 'view':
            		display = 'viewer';
            		break;
            	case 'edit':
            		display = 'editor';
            		break;
            	}
            	this.displayAs = display;
            	
            	
            	
            	domAttr.set(this.domNode,'data-displayas',display);
            	this.Refresh();
            },
            EditPlan:function(event) {
            	
            	domClass.remove(this.domNode,'hidden');
            	this.plan = event.plan;
            	if(event.comparePlan) {
           			this.startPlan = event.comparePlan;
            	}
            	
            	if(this.plan) this.SetItems(this.plan.data.seats);
            	if(!this.hasEdited) {
            		on(this.input_plan_name,'keyup',this.HandleValueChange.bind(this));
                	on(this.input_maxSpace,'keyup',this.HandleValueChange.bind(this));
                	on(this.input_maxLayers,'keyup',this.HandleValueChange.bind(this));
                	on(this.sel_seat,'change',this.HandleValueChange.bind(this));
                	on(this.advertise_cb,'change',this.HandleValueChange.bind(this));
                	on(this.allow_pubcat,'change',this.HandleValueChange.bind(this));
                	on(this.allow_pubmap,'change',this.HandleValueChange.bind(this));
                	on(this.allow_pubutils,'change',this.HandleValueChange.bind(this));
                	on(this.cost,'change',this.HandleValueChange.bind(this));
                }
            	this.hasEdited = true;
            	
            },
            SetItems:function(items) {
            	this.items = items;
            	
            	this.Refresh();
            },
            Clear:function(event) {
            	
            	this.plan = null;
            	this.items = null;
            	this.input_plan_name.value = '';
            	this.input_maxSpace.value = '';
            	this.input_maxLayers.value = '';
            	this.list.RemoveItems();
            	this.advertise_cb.checked = false;
            	this.allow_pubcat.checked = false;
            	this.allow_pubmap.checked = false;
            	this.allow_pubutils.checked  = false;
            	this.cost.value = "";
            	domClass.add(this.advertise_options,'hidden');
            },
            Refresh:function(event) {
            	if(!this.plan) return;
            	this.input_plan_name.value =this.plan.data.planName;
            	this.input_maxSpace.value = this.plan.data.max_space;
            	this.input_maxLayers.value = this.plan.data.max_layers;
            	this.advertise_cb.checked = this.plan.data.advertise;
            	this.allow_pubcat.checked = this.plan.data.allow_pubcat;
            	this.allow_pubmap.checked = this.plan.data.allow_pubmap;
            	this.allow_pubutils.checked = this.plan.data.allow_pubutils;
            	this.cost.value = (this.plan.data.cost==false) ? '0': ''+this.plan.data.cost;
            	this.HandleAdvert();
            	domClass.remove(this.domNode,'hidden');
            	switch( this.state) {
            	            	
            	case 'view':
            		
            		this.sel_seat.DisplaySelection(this.plan.data.owner_seat)
            		domClass.add(this.input_plan_name,'noneditable');
            		domAttr.set(this.input_plan_name,'disabled','disabled');
            		
            		if(this.displayAs=='editor') {
            			
            			this.list.RemoveItems();
            			this.list.SetItems(this.items,this.item_obj,{editorGroup:this.editorGroup,'state':'edit',listener:this.HandleValueChange.bind(this)});
            			
                		
            			
            		} else {
            			this.HideWebIntegration();
            			domClass.add(this.input_maxSpace,'noneditable');
            			domAttr.set(this.input_maxSpace,'disabled','disabled');
            			domClass.add(this.input_maxLayers,'noneditable');
            			domAttr.set(this.input_maxLayers,'disabled','disabled');
                		
            			this.list.RemoveItems();
            			this.list.SetItems(this.items,this.item_obj,{editorGroup:this.editorGroup,'state':'view',listener:this.HandleValueChange.bind(this)});            			
            		} 
            		
            		break;
            	case 'edit':
            		
            		this.sel_seat.SetSelection(this.plan.data.owner_seat)
            		
           			this.list.RemoveItems();
           			this.list.SetItems(this.items,this.item_obj,{editorGroup:this.editorGroup,'state':'edit',listener:this.HandleValueChange.bind(this)});
            		
            		break;
            	}
            	

        		
            },
            UpdateInputClasses:function(){
            	if(this.state == 'view') {
            		domAttr.set(this.input_plan_name,'disabled','disabled');
            		domAttr.set(this.input_maxSpace,'disabled','disabled');
            	}
            },
            HandleValueChange:function(event) {
        		this.isChanged = this.plan.isChanged = true;
            	this.plan.data.max_space = this.input_maxSpace.value;
            	this.plan.data.max_layers = this.input_maxLayers.value;
            	this.plan.data.advertise = this.advertise_cb.checked;
            	this.plan.data.allow_pubcat = this.allow_pubcat.checked;
            	this.plan.data.allow_pubmap = this.allow_pubmap.checked;
            	this.plan.data.allow_pubutils = this.allow_pubutils.checked;
            	this.plan.data.cost = this.cost.value;
            	var seatSel = this.sel_seat.GetSelection();
            	if(seatSel) this.plan.data.owner_seat = seatSel;
            	
              	this.plan.data.planName = this.input_plan_name.value;
            	topic.publish(this.editorGroup+'/changed',{target:this,plan:this.plan});
            	console.log(this.plan);
            	domClass.add(this.domNode,'changed');
        	},
            MatchItem:function(item) {
            	return (item == this.plan);
            },
        	Validate:function() {
        		this.data.max_space =  this.RequireMin(0+this.startPlan.data.max_space, 0+this.plan.data.max_space);
        		this.data.max_layers = this.RequireMin(0+this.startPlan.data.max_layers,0+this.plan.data.max_layers);
        		for(var i in this.startPlan.data.seats) {
        			this.data.seats[i].count = this.RequireMin(this.startPlan.data.seats[i].count,0+this.plan.data.seats[i].count);
        		}
        		
        	},
            RequireMin:function(targetMin, compareValue) {
            	if( targetMin <= compareValue) return comparevalue;
            	return targetMin;
            	
            }
        		

        });
});
