define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/json',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/listings/seats_listing/seat",
        "sl_components/listings/seats_listing/new_item",
        "sl_components/scrollable_listing/widget",
        "sl_components/scrollable_listing/footer/widget",
        "dojo/text!./templates/seats.tpl.html",
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		json,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		seat,
    		new_item,
    		listing,
    		footer,
    		template,
    		wapi
    		){
        return declare('listings/seats_listing',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
           
            // Our template - important!
            templateString: template,
            baseClass: "seats_listing listing",
            item_obj: seat,
            postCreate:function(){
            	topic.subscribe('new_seat new_item/addItem',this.AddItem.bind(this));
            	topic.subscribe('scrollable_listing/refresh',this.Refresh.bind(this));
            	topic.subscribe('scrollable_listing/update',this.SaveChanges.bind(this));
            	
            	this.Refresh();
            	//on(this.add_bttn,'click',this.AddItem.bind(this));
            },
            Refresh:function(event) {
            	if(!event) event = {src:this.footer};
            	if(event.src == this.footer) {
            		this.list.RemoveItems();
            		wapi.exec('invoicing/seats',{action:'list'},this.ModelReady.bind(this));
            	}
            },
            AddItem:function(event) {
            	if(event.src!=this.item_adder) return;
            	var item = event.item;
            	wapi.exec('invoicing/seats',{action:'add','seatName':item.name,'roleId':item.role},this.ItemAdded.bind(this));            	
            },
            ItemAdded:function(response) {
            	if(response.results.action_status=='added') {
            		this.Refresh();
            	}
        	},
        	ModelReady:function(response){ 
        		this.list.SetItems(response.results,this.item_obj);
            },
        	SaveChanges:function(event) {
        		if(event.src != this.footer) return;
        		var items = json.stringify({'json':this.list.items});
        		wapi.exec('invoicing/seats',{'action':'save','changeset':items},this.ChangesSaved.bind(this));
        	},
        	ChangesSaved:function(response) {
        		this.Refresh();
        	}

        });
});
