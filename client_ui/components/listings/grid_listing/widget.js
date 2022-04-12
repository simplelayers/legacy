define([ "dojo/_base/declare", "dojo/on", "dojo/dom-attr", "dojo/dom-style",
		"dojo/dom-class", "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", 'sl_modules/WAPI',
		"dojo/store/Memory", "gridx/core/model/cache/Sync", "gridx/Grid",
		"gridx/modules/Sort", "gridx/support/QuickFilter",
		"gridx/modules/Filter", "gridx/modules/Bar",
		"gridx/modules/TouchScroll", "gridx/modules/VirtualVScroller",
		"gridx/modules/ColumnResizer", "gridx/modules/extendedSelect/Row",	
		"gridx/modules/CellWidget",
		"dojo/text!./templates/ui.tpl.html" ], function(declare, dojoOn,
		domAttr, domStyle, domClass, _WidgetBase, _TemplatedMixin,
		_WidgetsInTemplateMixin, wapi, Memory, Cache, Grid, Sort, QuickFilter,
		Filter, Bar, TouchScroll, VirtualVScroller, ColumnResizer, Row,
		CellWidget,
		template) {
	return declare('sl_components/listings/grid_listing', [_WidgetBase,_TemplatedMixin,
	_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the
		// constructor
		baseClass : 'grid_listing',
		templateString : template,
		listner : null,
		projeciton_sel : null,
		format : null,
		grid : null,
		store : null,
		structure : null,
		modules:null,
		constructor : function() {
				console.log('in widget constructor');
			// Create grid widget.
				dojoOn(window, 'resize', this.ResizeGrid.bind(this));	
		},
		Setup : function(store,structure,module) {
			
			var cache = Cache;

			this.structure = structure;
			this.store = new Memory({data:store});
			params = {};
			this.grid = new Grid({
				id:'grid_'+this.id,
				cacheClass : cache,
				store : this.store,
				structure : this.structure,
				autoHeight : false,
				barTop : [
				      	module,
				          
				{
				   pluginClass : "gridx/support/QuickFilter",
					style : "text-align: right;"
				} ],
				modules : [ Filter, Bar, TouchScroll, VirtualVScroller,
						ColumnResizer, Sort, CellWidget
				]

			});

			
			// Put it into the DOM tree. Let's assume there's a
			// node with id "gridContainer".
			this.grid.placeAt(this.domNode);

			// Start it up.
			this.grid.startup();
		
			// this.grid.column('name').sort(true);

		},
		SetData:function(data,structure) {
			this.grid.model.clearCache();
			this.grid.model.store.setData(data)
			this.grid.body.refresh();			
			
		},
		ResizeGrid : function(event) {
			var styles = domStyle.getComputedStyle(this.domNode);
			console.log(styles);
			this.grid.resize(styles.innerWidth, styles.innerHeight);
		}

	});
});