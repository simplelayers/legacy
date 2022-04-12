define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/json',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/scrollable_listing/new_item/widget",
        "sl_components/scrollable_listing/widget",
        "sl_components/scrollable_listing/footer/widget",
        "dojo/text!./templates/role_contexts.tpl.html",
        'sl_components/listings/role_context_listing/role_context',
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		json,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		new_item,
    		listing,
    		footer,
    		template,
    		role_context,
    		wapi
    		){
        return declare('role_context_listing',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
           
            // Our template - important!
            templateString: template,
            baseClass: "role_context_listing",
            item_obj: role_context,
            postCreate:function(){
            	topic.subscribe('new_item/addItem',this.AddItem.bind(this));
            	topic.subscribe('scrollable_listing/refresh',this.Refresh.bind(this));
            	topic.subscribe('scrollable_listing/update',this.SaveChanges.bind(this));
            	
            	this.Refresh();
            	//on(this.add_bttn,'click',this.AddItem.bind(this));
            },
            Refresh:function(event) {
            	if(!event) event = {src:this.footer};
            	if(event.src == this.footer) {
            		this.list.RemoveItems();
            		wapi.exec('permissions/roles',{action:'list','list':'context'},this.contextModelReady.bind(this));
            	}
            },
            AddItem:function(event) {
            	if(event.new_item==this.item_adder) {
            		var item = event.item;
            		wapi.exec('permissions/roles',{action:'add','add':'context','contextName':item},this.ItemAdded.bind(this));
            	}            	
            },
            ItemAdded:function(response) {
            	if(response.results.action_status=='added') {
            		this.Refresh();
            	}
        	},
        	SaveChanges:function(event) {
        		if(event.src != this.footer) return;
        		var items = json.stringify({'json':this.list.items});
        		wapi.exec('wapi/permissions/roles',{'action':'save','save':'context','changeset':items},this.ChangesSaved.bind(this));
        		 
        	},
        	ChangesSaved:function(response) {
        		this.Refresh();
        	},
            contextModelReady:function(response){ 
            	this.list.SetItems(response.results,this.item_obj);        
            	this.SelectFirst();
            },
            
            SelectFirst:function() {
            	this.list.itemObjs[0].HandleButtonClick();
            }
            
            

        });
});
