define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        'dijit/form/Select',
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
    		select,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		template,
    		wapi
    		){
        return declare('selectors/media_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "media_selector dropdownlist",
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
        		on(this.selector,'change', this.NotifyChangeListeners.bind(this));
            	this.LoadItems();
            },
            HideLabel:function() {
            	domClass.add(this.label,'hidden');
            },
            LoadItems:function() {
            	if(this.items !=null) return;
            	var params = {'action':'list','target':'media_options'};
            	wapi.exec('organization/organizations',params,this.ModelReady.bind(this));
            },
        	ModelReady:function(event) {
        		this.SetItems( event.results );
        		//topic.publish('selectors/org_selector/media_types_loaded',{src:this,seats:this.items});
        	},
        	DisplaySelection:function(itemId) {
        		this.state = 'view';
        		this.selectedItem  = itemId;
        		var item = this.GetItem(itemId);
        		if(!item) return;
        		this.lbl_selected.innerHTML = item.data.seatName;
        		domClass.remove(this.view,'hidden');
        		//domClass.add(this.selector,'hidden');
        	},
        	GetItem:function(itemId) {
        		for(var i in this.items) {
        			var item = this.items[i];
        			if(item.id==itemId) return item;
        		}        		
        	},
        	GetItemByKey:function(key) {
        		if(!this.items.hasOwnProperty(key)) return null;
        		return this.items[key];
        	},
        	SetItems:function(items) {
        		this.items = items;
        		this.FillSelector();
        		if(this.selectedItem) {
        			this.DisplaySelection(this.selectedItem);
        		}
        		this.NotifyChangeListeners();
        		
         	},
            FillSelector:function() {
            	domClass.remove(this.select,'hidden');
            	this.selector.innerHTML = '';
            	var isFirst = true;
            	var options = [];
            	for(var i in this.items) {
        			var item = this.items[i];
        			var option = {'label':item.label,'value':i};
        			if(isFirst) option.selected=true;
        			options.push(option)
        			isFirst = false;
        		}
            	this.selector.set('options',options);
            	this.selector.startup();
    			
            	//if(this.selection) this.SetSelection(this.selection);
            	},
        	GetSelection:function() {
        		this.state = 'edit';
        		options = this.selector.get('options');
        		for(var i in options) {
        			var option = options[i];
        			if(option.selected) return option.value;
        		}
        	},
            SetSelection:function(mediaType) {
            	this.selection = mediaType;
            	if(this.items === null) return;
            	this.state =  'edit';
            	
            	for( var i=0; i < this.items.length; i++) {
            		 if(this.items[i].value == mediaType) {
            			 this.selector.selectedIndex = i;
            			break;
            		 }
            	}            
            	domClass.remove(this.select,'hidden');
            	
            },
            NotifyChangeListeners:function() {
            	on.emit(this.domNode,'change',{bubbles:true,cancelable:true});
            }
        	
            
        });
            
});
