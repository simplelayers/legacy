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
        return declare('listings/maps_layers',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            templateString: template,
            baseClass: "map_layers listing",
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
            	//this.dndSrc = new dnd(this.list.domNode);
            	//this.dndSrc.startup();
            	//on(this.item_adder,'item_added',this.AddItem.bind(this));
            	on(this.item_adder,'show_parent',this.ShowParent.bind(this));
            	this.subscriptions.push(on(this.footer.domNode,'scrollable_listing/refresh',this.Refresh.bind(this)));
            	this.subscriptions.push(on(this.footer.domNode,'scrollable_listing/update',this.SaveChanges.bind(this)));
            	
            	
            	this.Refresh();
            },
            
            SetDefaults:function(defaults) {
            },
            ModelLoaded:function(event) {
            	this.SetItems( event.project.layers);
            	lookupObj = {'numItems':this.items.length}
            	this.list.SetItems(this.items,this.item_obj,lookupObj,null,this);
            },
            HandleMove:function(event) {
            	
            	if(event['new'] == event.old) return;
            	if(event['new'] === undefined) return;
            	
            	tmpItems = [];
            	event.old = Math.abs(event.old);
            	var item = this.items[Math.abs(event.old)];
            	this.items.splice(event.old,1);
            	
            	adjNew =   (event['new']<event['old']) ? event['new'] + 1 : event['new'];
            	
            	
            	if(adjNew == -1) {
            		this.items.unshift(item);
            	} else {
            		this.items.splice(adjNew,0,item);
            	}
            	
            	z = -1;
    			for(var i = 0; i < this.items.length; i++ ) {
    				z++;
    				this.items[i].z = -z;
    				this.items[i]._i = -i;
    				if(this.items[i].typeLabel == 'collection') {
    					for(var ii in  this.items[i].sublayers) {
    						if(this.items[i] === undefined) continue;
    						z++;
    						this.items[i].sublayers[ii].z = -z;
    						
    					}
    				}
    				
    			}
    			
    			this.list.ResetItems(this.items);
            	
            	//this.list.SetItems(this.items,this.item_obj,{"list":this});
            	
            },
            BeginMove:function(fromItem) {
            	this.moveItem = fromItem;
            	domClass.toggle(this.domNode,'not_moving');
            	domClass.toggle(this.domNode,'moving');
            	
            	for( var i in this.list.itemObjs) {
            		var item = this.list.itemObjs[i];
            		if(item == fromItem) continue;
            		item.HandleMoveStart();
            	}
            },
            EndMove:function(targetIndex) {
            	if(!this.moveItem) return; 
            	domClass.toggle(this.domNode,'not_moving');
            	domClass.toggle(this.domNode,'moving');            	
            	for( var i in this.list.itemObjs) {
            		var item = this.list.itemObjs[i];
            		
            		item.HandleMoveEnd();
            	}
            	this.HandleMove({"old":-this.moveItem._i,"new":targetIndex});
            	this.moveItem = null;
            },
            ShowSublayers:function(item) {
            	this.parentItems = this.items;
            	itemIndex = this.items.indexOf(item);
            	this.parentOf = itemIndex;
            	
            	this.SetItems(item.sublayers);
            	
            	lookupObj = {'numItems':this.items.length};
            	this.list.SetItems(this.items,this.item_obj,lookupObj,null,this);
            	domClass.remove(this.domNode,'parentless');
            },
            ShowParent:function() {
            	alert('show parent');
            	this.parentItems[this.parentOf].sublayers = this.items;
            	this.items = this.parentItems;
            	this.parentItems = null;
            	this.parentOf = null;
            	this.SetItems(this.items);
            	lookupObj = {'numItems':this.items.length}
            	this.list.SetItems(this.items,this.item_obj,lookupObj,null,this);
            	
            },
            EditItem:function(event) {
            },
            SetItems:function(items) { 
            	if(!this.parentItems) {
            		domClass.add(this.domNode,'parentless');
            	} else {
            		domClass.remove(this.domNode,'parentless');
            	}
            	this.items =  [];
            	for(var i in items) {
            	
            		this.items.push(items[i]);
            	}
            	
            	this.items.sort(function(a,b){
            		return (-a.z)-(-b.z);
            	})
            	for(var i in this.items) {
            		this.items[i]._i = -i;
            		
            	}
            	
            	
            },
            
            Clear:function(event) {
            	this.list.RemoveItems();
            	domClass.add(this.domNode,'parentless');
            },
            Refresh:function(event) {
            	this.Clear();
            	 wapi.exec('wapi/map/load',{'map':pages.GetPageArg('mapId'),'formatOptions':256},this.ModelLoaded.bind(this));            	 
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
            	//wapi.exec('organization/organizations',params,this.ItemAdded.bind(this));
            },
            AddItem:function(event) {
        		//wapi.exec('organization/organizations',params,this.ItemAdded.bind(this));
        	},
        	ItemAdded:function(event) {
        		this.Refresh();
        		
        	}                   	
        });
});
