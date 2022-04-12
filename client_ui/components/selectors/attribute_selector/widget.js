define(["dojo/_base/declare",
        "dojo/on",
        "dojo/query",
        "dojo/topic",
        'dojo/dom',
        'dojo/dom-construct',
        'dojo/dom-class',
        'dojo/dom-attr',
        'dijit/form/Select',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/selector.tpl.html",        
        "sl_modules/WAPI",
        'sl_modules/Pages',
        'sl_modules/sl_Sorts'],
    function(declare,
    		on,
    		query,
    		topic,
    		dom,
    		domCon,
    		domClass,
    		domAttr,
    		select,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		template,
    		wapi,
    		pages,
    		sorts
    		){
        return declare('selectors/attribute_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "attribute_selector dropdownlist",
        	isDeleted:false,
        	isChanged:false,
        	contextId:null,
        	value:null,
        	state:null,
        	i:null,
        	selection:null,
        	selectedItem:null,
        	fields:null,
            constructor:function(args) {
        		
        	},
        	postCreate:function(){
        		on(this.selector,'change', this.NotifyChangeListeners.bind(this));
            	this.LoadItems();
            	
            	
            	
            	//var q = this.selector.id+'_menu';
            	//var r = query('.dijitMenuItem',document);

            	
            },
            HideLabel:function() {
            	domClass.add(this.label,'hidden');
            },
            LoadItems:function() {
            	var attributes = pages.GetPageArg('dataSource_attributes');
            	if(attributes) {
            		this.ModelReady({'attributes':attributes});
            		return;
            	}
            	params = {};
            	params.layerId = pages.GetPageArg('layerId');
            	params.features = 'meta';
            	if(domAttr.get(this.domNode,'data-searchable')) {
            		params.features += ',searchable';
            	}
            	wapi.exec('wapi/layers/attributes/action:get/',params,this.ModelReady.bind(this));
            	
            	
            },
            ModelReady:function(results) {
            	
            	items = [];
            	this.fields = results.attributes;
            	for( var att in results.attributes) {
            		items.push(att);
            	}
            	items.sort(sorts.SortAlphaNumeric);
            	atts = [];
            	
            	ctr=0;
            	for( var i in items) {
            		
            		var item = items[i];
            		atts[ctr] = {label:item,value:items[i]};
            		ctr++;
            	}          
            	atts.unshift({label:'',value:''});
            	this.SetItems(atts);
            },
        	DisplaySelection:function(itemId) {
        		
        		
        		return;
        		this.state = 'view';
        		this.selectedItem  = itemId;
        		
        		var item = this.GetItem(itemId);
        		
        		if(!item) return;
        		//this.lbl_selected.innerHTML = item.data.seatName;
        		//domClass.remove(this.view,'hidden');
        		//domClass.add(this.selector,'hidden');
        		
        	},
        	GetItem:function(itemId) {
        		return this.fields[itemId];
        		for(var i in this.items) {
        			var item = this.items[i];
        			if(item.id==itemId) return item;
        		}        		
        	},
        	SetItems:function(items) {
        		this.items = items;
        		var first  =this.FillSelector();
        		if(!this.selectedItem) this.selectedItem = this.GetItem(first); 
        		
        		this.DisplaySelection(this.selectedItem);
        		this.NotifyChangeListeners();
        		
         	},
            FillSelector:function() {
            	domClass.remove(this.select,'hidden');
            	this.selector.innerHTML = '';
            	var isFirst = true;
            	firstItem = null;
            	for(var i in this.items) {
            		var item = this.items[i];
            		if(item.enabled === false) continue;
            		option = '<option value="'+item.value+'" ';
        			//if(isFirst) option+=' data-selected=true ';
        			option+='>'+item.label+'</option>';
        			this.selector.options.push(domCon.toDom(option));
        			
        			if(isFirst) firstItem = item.label;
        			isFirst = false;
        		}
            	this.selector.startup();
            	return firstItem;
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
            SetSelection:function(sel) {
            	this.selection = sel;
            	if(this.items === null) return;
            	this.state =  'edit';
            	
            	for( var i=0; i < this.items.length; i++) {
            		 if(this.items[i].value == sel) {
            			 this.selector.selectedIndex = i;
            			break;
            		 }
            	}            
            	domClass.remove(this.select,'hidden');
            	
            },
            NotifyChangeListeners:function() {
            	var sel = this.GetSelection();
            	this.selectedItem = this.GetItem(sel);
            	this.emit('change',{bubbles:true,cancelable:true,selectedItem:this.selectedItem});
            }
        
            
        });
            
});
