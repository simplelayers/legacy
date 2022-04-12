define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/role_selector.tpl.html",        
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
        return declare('selectors/role_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "role_selector dropdownlist",
        	isDeleted:false,
        	isChanged:false,
        	contextId:null,
        	value:null,
        	roles:null,
        	selection:null,
        	constructor:function(args) {
        		if(args['roles']) args.roles;
        	},
        	postCreate:function(){
            	if(domAttr.has(this.domNode,'data-context-id')) {
            		this.contextId = domAttr.get(this.domNode,'data-context-id');
            	}
            	if(domAttr.has(this.domNode,'data-label')) {
            		this.label.innerHTML = domAttr.get(this.domNode,'data-label');
            	}
            	on(this.selector,'change', this.NotifyChangeListeners.bind(this));
            	this.LoadRoles();
            },
            HideLabel:function() {
            	domClass.add(this.label,'hidden');
            },
            LoadRoles:function() {
            	
            	if(this.roles !=null) return;
            	var params = {action:'list','list':'role'};
            	params.contextId = (this.contextId === null) ? 'default' : this.contextId;
            	
            	wapi.exec('permissions/roles',params,this.ModelReady.bind(this));
            },
        	ModelReady:function(event) {
        		this.SetRoles( event.results.data.roles );
        		topic.publish('selectors/role_selector/roles_loaded',{src:this,roles:this.roles});
        	},
        	SetRoles:function(roles) {
        		this.roles = roles;
        		this.FillSelector();
        	},
            FillSelector:function() {
            	this.selector.innerHTML = '';
            	var isFirst = true;
            	for(var i in this.roles) {
        			var role = this.roles[i];
        			var option = "<option value=\""+role.id+"\"";
        			if(isFirst) option+=' selected';
        			option+=">"+role.name+"</role>";
        			this.selector.innerHTML+=option;
        			isFirst = false;
        		}
            	if(this.selection) this.SetSelection(this.selection);
            },
        	GetSelection:function() {
        		var index = this.selector.selectedIndex;
        		return this.selector.options[index].value;
        	},
            SetSelection:function(roleId) {
            	
            	this.selection = roleId;
            	if(this.roles === null) return;
            	for( var i=0; i < this.roles.length; i++) {
            		 if(this.roles[i].id == roleId) {
            			 this.selector.selectedIndex = i;
            			 return;
            		 }
            	}            	
            },
            NotifyChangeListeners:function() {
            	on.emit(this,'change',{bubbles:true,cancelable:true});
            }
        	
            
        });
            
});
