define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/json',
        'dojo/dom-attr',
        'dojo/dom-class',
        "dojo/dnd/Source",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/listings/map_layers_listing/new_item",
        "sl_components/listings/map_layers_listing/item",
        "sl_components/scrollable_listing/widget",
        'sl_components/scrollable_listing/footer/widget',
        "dojo/text!./templates/items.tpl.html",
        "sl_modules/Pages",
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		json,
    		domAttr,
    		domClass,
    		dnd,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		new_item,
    		item,
    		listing,
    		footer,
    		template,
    		pages,
    		wapi
    		){
        return declare('add_selectors/layer_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            templateString: template,
            baseClass: "layers add_selector",
            item_obj: item,
            lastAddedItem:null,
            items:null,
            editorGroup:null,
            subscriptions:null,
            dndSrc:null,
            moveItem:null,
            parentItems:null,
            parentOf:null,
            postCreate:function(){
            	this.subscriptions = [];
            	this.subscriptions.push(on(this.footer.domNode,'scrollable_listing/refresh',this.Refresh.bind(this)));
            },
            SetDefaults:function(defaults) {
            	
            },
            ModelLoaded:function(event) {
            	/*this.SetItems( event.project.layers);
            	lookupObj = {'numItems':this.items.length}
            	this.list.SetItems(this.items,this.item_obj,lookupObj,null,this);*/
            },
            SetItems:function(items) { 
            	this.items = items;
            	/*this.items.sort(function(a,b){
            		return (a.name)<(b.name);
            	})*/           
            },
            Clear:function(event) {
            	this.list.RemoveItems();
            },
            Refresh:function(event) {
            	this.Clear();
            	 //wapi.exec('wapi/map/load',{'map':pages.GetPageArg('mapId'),'formatOptions':256},this.ModelLoaded.bind(this));            	 
            },
            AddItem:function(event) {
        		//wapi.exec('organization/organizations',params,this.ItemAdded.bind(this));
        	},
        	ItemAdded:function(event) {
        		this.Refresh();
        	}                   	
        });
});
