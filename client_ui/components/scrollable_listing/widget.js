define([ "dojo/_base/declare", 
         "dojo/_base/lang",
         "dojo/on", "dojo/topic", "dojo/dom-construct",
         'dojo/dom-class',         
		"dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin",
		"dojo/text!./templates/scrollable_listing.tpl.html" ], function(
		declare, lang,on, topic, domCon, domClass,_WidgetBase, _TemplatedMixin,
		_WidgetsInTemplateMixin, template) {
	return declare('scrollable_listing', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		// Using require.toUrl, we can get a path to our AuthorWidget's space
		// and we want to have a default avatar, just in case

		// Our template - important!
		templateString : template,
		baseClass : "scrollable_listing",
		items : null,
		itemObjs:null,
		itemObj:null,
		postCreate : function() {
			this.itemObjs = [];
		},
		ResetItems:function (items) {
			for(var i=0; i < items.length; i++) {
				this.itemObjs[i].SetItem(items[i]);
			}
			this.items = items;
		},
		RemoveItems : function() {
			var widgets = dijit.registry.findWidgets(this.listing);
			this.items = null;
			for ( var i = 0; i < widgets.length; i++) {
				var w = widgets[i];
				if(w.Unsubscribe) w.Unsubscribe();
				w.destroyRecursive();
			}
			domCon.empty(this.listing);
			this.itemObjs = [];
		},
		SetItems : function(items, item_obj,lookupData,style,parentList) {
			this.item_obj = item_obj;
			if(!lookupData) lookupData = {};
			this.RemoveItems();
			this.items = items;
			
			if (Object.prototype.toString.call( items ) === '[object Array]') {
				for ( var i = 0; i < items.length; i++) {
					lookupData._i =  i;
					
					var item = this.AddItem(items[i], item_obj,lookupData,style,parentList);
				}
			} else {
				for( var key in items) {
					this.AddItem({id:key,value:items[key]},item_obj,lookupData,style,parentList);
				}
			}
		},
		AddItem : function(item, item_obj,lookupData,style,parentList) {
			if(!lookupData) lookupData = {};
			params = {'item':item,'lookupObj':lookupData};
			if(parentList) params['list'] = parentList;
			var newItem = new item_obj(params);
			if(style) {
				var styles = style.split(',');
				for(var i in styles) {
					var style = styles[i];
					domClass.add(newItem,style);
				}
				
			}
			this.itemObjs.push(newItem);
			newItem.placeAt(this.listing);
			return newItem;
			
		},
		GetItem:function(item) {
			for(var i in this.itemObjs) {
				itemObj = this.itemObjs[i];
				if(itemObj.MatchItem) {
					if(itemObj.MatchItem(item)) return itemObj;
				}
			}
			return false;
		},
		UpdateItems:function(updateObj) {
			for( var i in this.itemObjs) {
				itemObj = this.itemObjs[i];
				if(itemObj.UpdateItem) {
					itemObj.UpdateItem(updateObj);
				}
			}
		}
		
		
	});
});
