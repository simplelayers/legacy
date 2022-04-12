define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/selector.tpl.html",        
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
    		wapi
    		){
        return declare('selectors/seat_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "seat_selector dropdownlist",
        	isDeleted:false,
        	isChanged:false,
        	contextId:null,
        	value:null,
        	state:null,
        	i:null,
        	selection:null,
        	selectedItem:null,
        	constructor:function(args) {
        	
        	},
        	postCreate:function(){
        		if(domAttr.has(this.domNode,'data-context-id')) {
            		this.contextId = domAttr.get(this.domNode,'data-context-id');
            	}
            	if(domAttr.has(this.domNode,'data-label')) {
            		this.label.innerHTML = domAttr.get(this.domNode,'data-label');
            	}
            	on(this.selector,'change', this.NotifyChangeListeners.bind(this));
            	this.LoadItems();
            },
            HideLabel:function() {
            	domClass.add(this.label,'hidden');
            },
            LoadItems:function() {
            	if(this.items !=null) return;
            	var params = {action:'list','list':'role'};
            	params.contextId = (this.contextId === null) ? 'default' : this.contextId;
            	
            	wapi.exec('invoicing/seats',params,this.ModelReady.bind(this));
            },
        	ModelReady:function(event) {
        		this.SetItems( event.results );
        		topic.publish('selectors/seat_selector/seats_loaded',{src:this,seats:this.items});
        	},
        	DisplaySelection:function(itemId) {
        		this.state = 'view'
        		this.selectedItem  = itemId;
        		var item = this.GetItem(itemId);
        		if(!item) return;
        		this.lbl_selected.innerHTML = item.data.seatName;
        		domClass.remove(this.view,'hidden');
        		domClass.add(this.selector,'hidden');
        	},
        	GetItem:function(itemId) {
        		for(var i in this.items) {
        			var item = this.items[i];
        			if(item.id==itemId) return item;
        		}        		
        	},
        	SetItems:function(items) {
        		this.items = items;
        		this.FillSelector();
        		if(this.selectedItem) {
        			this.DisplaySelection(this.selectedItem);
        		}
        		

        	},
            FillSelector:function() {
            	this.selector.innerHTML = '';
            	var isFirst = true;
            	for(var i in this.items) {
        			var item = this.items[i];
        			var option = "<option value=\""+item.id+"\"";
        			if(isFirst) option+=' selected';
        			option+=">"+item.data.seatName+"</option>";
        			this.selector.innerHTML+=option;
        			isFirst = false;
        		}
            	if(this.selection) this.SetSelection(this.selection);
            },
        	GetSelection:function() {
        		this.state = 'edit';
        		if(this.selector.slectedIndex <0) return null;
        		var index = this.selector.selectedIndex;
        		return this.selector.options[index].value;
        	},
            SetSelection:function(itemId) {
            	this.selection = itemId;
            	if(this.items === undefined) return;
            	if(this.items.length == 0) return;
            	this.state =  'edit';
            	
            	for( var i=0; i < this.items.length; i++) {
            		 if(this.items[i].id == itemId) {
            			 this.selector.selectedIndex = i;
            			break;
            		 }
            	}            
            	domClass.remove(this.select,'hidden');
            	
            },
            NotifyChangeListeners:function() {
            	on.emit(this,'change',{bubbles:true,cancelable:true});
            }
        	
            
        });
            
});
