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
        return declare('plan_seat_item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "plan_seat_item listing_item",
        	isDeleted:false,
        	isChanged:false,
        	editorGroup:null,
        	changeListener:null,
        	state:null,
        	constructor:function(args) {
        		this.item = args.item;
        		this.changeListener = args.lookupObj.listener;
        		this.editorGroup = args.lookupObj.editorGroup;
        		this.state = args.lookupObj.state;
        		
        		
        	},
            postCreate:function(){
            	this.lbl_item_name.innerHTML = this.item.name;
            	
            	on(this.input_count,'keyup',this.HandleValueChange.bind(this))
            	on(this.domNode,'changed',this.changeListener);
            	            	
        		if(this.state=='view') {
        		
        			this.input_count.value = (this.item.count=='') ? 'unlimited' : this.item.count;
        			domClass.add(this.input_count,'noneditable');
        			domAttr.set(this.input_count,'disabled','disabled');
        		} else {
        			this.input_count.value = this.item.count;	
        		}
        		
            	
            	
            },
            HandleButtonClick:function(event) {
            	//topic.publish('role_context_item/show_roles',{context:this.item});
            },
            HandleValueChange:function(event) {
            	//this.item.data.seatName = this.item_name.value;
            	this.isChanged = true;
            	this.item.count = this.input_count.value;
            	domClass.add(this.domNode,'changed');
            	on.emit(this.domNode,'changed',{bubbles:true,cancelable:true});
            	
            	
            },
            EditItem:function() {
            	
            },
            UpdateUI:function() {
            },
            MatchItem:function(item) {
            	return (item.planName == this.item.data.planName);
            }
        });
});
