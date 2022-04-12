define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/json',
        'dojo/dom-attr',
        'dojo/dom-class',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/listings/organizations/new_item",
        "sl_components/listings/organizations/item",
        "sl_components/scrollable_listing/widget",
        'sl_components/scrollable_listing/footer/widget',
        "dojo/text!./templates/items.tpl.html",
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		json,
    		domAttr,
    		domClass,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		new_item,
    		item,
    		listing,
    		footer,
    		template,
    		wapi
    		){
        return declare('listings/organizations',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            templateString: template,
            baseClass: "organizations listing",
            item_obj: item,
            lastAddedItem:null,
            items:null,
            editorGroup:null,
            subscriptions:null,
            addedItem:null,
            postCreate:function(){
            	this.subscriptions = [];
            	this.editorGroup = domAttr.get(this.domNode,'data-editor');
            	on(this,'item_added',this.AddItem.bind(this));
            	this.subscriptions.push(on(this.footer.domNode,'scrollable_listing/refresh',this.Refresh.bind(this)));
            	this.subscriptions.push(on(this.footer.domNode,'scrollable_listing/update',this.SaveChanges.bind(this)));
            	this.Refresh();
            },
            SetDefaults:function(defaults) {
            	this.item_adder.SetDefaults(defaults);
            },
            ModelLoaded:function(event) {
            	this.items = event.results;
            	this.list.SetItems(this.items,this.item_obj);
            },
            EditItem:function(event) {
            },
            SetItems:function(items) { 
            	this.items = items;
            	//this.Refresh();
            },
            Clear:function(event) {
            	this.list.RemoveItems();
            },
            Refresh:function(event) {
            	wapi.exec('organization/organizations',{'action':'list','target':'org'},this.ModelLoaded.bind(this));            	
            },
            HandleValueChange:function(event) {
        		domClass.add(this.domNode,'changed');
        	},
            MatchItem:function(item) {
            	return (item == this.plan);
            },
        	Validate:function() {
        	},
            SaveChanges:function() {
            	params = {'action':'save','target':'org'};
            	params['changeset'] = json.stringify({'json':this.items});
            	
            	wapi.exec('organization/organizations',params,this.ItemAdded.bind(this));
            },
            AddItem:function(event) {
        		var params = event.item;
        		params['action'] = 'add';
        		params['target'] = 'org';
        		this.addedItem = event.item;
        		
        		wapi.exec('organization/organizations',params,this.ItemAdded.bind(this));
        	},
        	ItemAdded:function(event) {
        		on.emit(this.domNode,'organization_listing/org_added',{orgId:event.orgId,cancelable:true,bubbles:true});
        		
        		this.Refresh();
        		
        	}                   	
        });
});
