define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/json',
        'dojo/dom-attr',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/listings/plans_listing/item",
        "sl_components/listings/plans_listing/new_item",
        "sl_components/scrollable_listing/widget",
        "sl_components/scrollable_listing/footer/widget",
        "dojo/text!./templates/items.tpl.html",
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		json,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		item,
    		new_item,
    		listing,
    		footer,
    		template,
    		wapi
    		){
        return declare('listings/plans_listing',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
           
            // Our template - important!
            templateString: template,
            baseClass: "plans_listing listing",
            item_obj: item,
            lastAddedItem:null,
            editorGroup:null,
            postCreate:function(){
            	on(this.item_adder.domNode,'new_plan/addItem',this.AddItem.bind(this));
            	
            	topic.subscribe('scrollable_listing/refresh',this.Refresh.bind(this));
            	topic.subscribe('scrollable_listing/update',this.SaveChanges.bind(this));
            	
                
            	this.editorGroup = domAttr.get(this.domNode,'data-editor');
            	
            	this.Refresh();
            	//on(this.add_bttn,'click',this.AddItem.bind(this));
            },
            Refresh:function(event) {
            	topic.publish(this.editorGroup+'/plans_loading');
            	if(!event) event = {src:this.footer};
            	if(event.src == this.footer) {
            		this.list.RemoveItems();
            		wapi.exec('invoicing/plans',{action:'list','what':'plan'},this.ModelReady.bind(this));
            	}
            },
            AddItem:function(event) {
            	
            	this.lastItemAdded = event.item;
            	
            	var item = json.stringify({'json':this.lastItemAdded});
            	wapi.exec('invoicing/plans',{'action':'add','what':'plan','item':item},this.ItemAdded.bind(this));
            	delete this.lastItemAdded.action;
            },
           
            
            ItemAdded:function(response) {
            	if(!response.hasOwnProperty('results')) return;
            	if(response.results.action_status=='added') {
            		this.Refresh();
            	}
            	itemObj = this.list.GetItem(this.lastItemAdded);
            	if(itemObj) itemObj.EditItem();
            	
//            	/this.lastItemAdded = null;
            	
        	},
        	ModelReady:function(response){ 
        		this.list.SetItems(response.results,this.item_obj,{group:this.editorGroup});
            },
        	SaveChanges:function(event) {
        		if(event.src != this.footer) return;
        		var items = json.stringify({'json':this.list.items});
        		topic.publish(this.editorGroup+'/saving');
        		
        		wapi.exec('invoicing/plans',{'action':'save','changeset':items},this.ChangesSaved.bind(this));
        	},
        	ChangesSaved:function(response) {
        		this.Refresh();
        	}
            
            
            
            

        });
});
