define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/json',
        'dojo/dom-class',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/scrollable_listing/new_item/widget",
        "sl_components/scrollable_listing/widget",
        "sl_components/scrollable_listing/footer/widget",
        'sl_components/listings/role_permissions_listing/role_permission',
        'sl_modules/WAPI',
        "dojo/text!./templates/role_permissions_listing.tpl.html",   
        "sl_modules/sl_URL"],
    function(declare,
    		on,
    		topic,
    		json,
    		domClass,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		new_item,
    		listing,
    		footer,
    		role_permissions,
    		wapi,
    		template
    		){
        return declare('role_permissions_listing',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
           
            // Our template - important!
            templateString: template,
            baseClass: "role_permissions listing",
            role: null,
            startMessage:'',
            item_obj: role_permissions,
            context: null,
            defaultHeading: '',
            postCreate:function(){
            	//this.startMessage = this.item_adder_noContext.innerHTML;
            	this.defaultHeading = this.list_heading.innerHTML;
            	topic.subscribe('scrollable_listing/refresh',this.Refresh.bind(this));
            	topic.subscribe('scrollable_listing/update',this.SaveChanges.bind(this));
            	topic.subscribe('role_permission/value_changed',this.UpdateValue.bind(this));
            	on(this.back_link,'click',this.GoBack.bind(this));
            	this.SetMessage(this.startMessage);
            },
            GoBack:function(event){
            	topic.publish('role_permissions/go_back/clicked',{src:this,clickEvent:event});
            },            
            SetRole:function(context,role) {
            	this.context = context;
            	this.role = role;
            	this.SetHeading('Permissions for '+this.context.context+':'+this.role.name);
            	this.Refresh();
            },
            SetMessage:function(message) {
            	//this.item_adder_noContext.innerHTML = message;
            	//domClass.add(this.item_adder.domNode,'hidden');
            	//domClass.remove(this.item_adder_noContext,'hidden');
            },
            SetHeading:function(message) {
            	this.list_heading.innerHTML = message;
            },
            Refresh:function(event) {
            	if(!event) event = {src:this.footer};
            	if(event.src != this.footer) return;
            	this.SetMessage('Loading Permissions');
            	this.list.RemoveItems();
            	wapi.exec('permissions/roles',{action:'list','list':'permission','permissionsId':this.role.permissions.$id.$oid},this.ModelReady.bind(this));    
            	
            },
            ModelReady:function(response) {
            	if(response.results.length==0) {
            		this.SetHeading('no results found');
            	}
            	
            	this.list.SetItems(response.results,this.item_obj);
            	
            },
            ItemAdded:function(event) {
            	this.Refresh();
            },
            SaveChanges:function(event) {
        		if(event.src != this.footer) return;;
        		var items = json.stringify({'json':{role:this.role,permissions:this.list.items}});
        		
        		wapi.exec('wapi/permissions/roles',{'action':'save','save':'permission','changeset':items},this.ChangesSaved.bind(this));
        		 
        	},
        	ChangesSaved:function(response) {
        		this.Refresh();
        	},
        	UpdateValue:function(event) {
        		if( this.list.items[event.item.id] === undefined) return;
        		this.list.items[event.item.id]['value'] =event.item.value;
        		
        		
        		
        	}
        });
});
