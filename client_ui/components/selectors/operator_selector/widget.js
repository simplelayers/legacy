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
        "sl_modules/WAPI"],
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
    		wapi
    		){
        return declare('selectors/operator_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "operator_selector dropdownlist",
        	isDeleted:false,
        	isChanged:false,
        	contextId:null,
        	value:null,
        	state:null,
        	i:null,
        	selection:null,
        	selectedItem:null,

            operators:  [
                         	{'label':"=",'value':'==','title':'Equals','enabled':true},
                         	{'label':"<",'value':'<','title':'Less than','enabled':true},
    						{'label':"<=",'value':'<=','title':'Less than or equal','enabled':true},
    						{'label':">",'value':'>','title':'Greater than','enabled':true},
    						{'label':">=",'value':'>=','title':'Greater than or equal','enabled':true},
    						{'label':'< >','value':'between','title':'Between','enabled':false},
    						{'label':'Starts','value':'starts','title':'Match field-values that start with the specified value','enabled':true},
    						{'label':'Contains','value':'contains','title':'If the specified is contained anywhere within the field-value','enabled':true},
    						{'label':'Ends','value':'ends','title':'Match field-values that end with the specified value','enabled':true},
    						{'label':'Is Null','value':'isnull','title':'Match field-values that end with the specified value','enabled':true}
     						],
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
            	this.SetItems(this.operators);
            	
            },
        	DisplaySelection:function(itemId) {
        		this.state = 'view'
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
        	SetItems:function(items) {
        		this.items = items;
        		items.unshift({label:"",value:"",selected:true});
        		this.selectedItem = this.items[0];
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
            		if(item.enabled === false) continue;
            		option = '<option value="'+item.value+'" ';
        			if(isFirst) option+=' selected="true" ';
        			option+='>'+item.label+'</option>';
        			
        			this.selector.options.push(domCon.toDom(option));
        			
        			isFirst = false;
        		}
            	this.NotifyChangeListeners();
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
            	this.selectedItem = this.GetSelection();
            	on.emit(this.domNode,'change',{bubbles:true,cancelable:true,selectedItem:this.selectedItem});
            }
        	
            
        });
            
});
