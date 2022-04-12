define([ "dojo/_base/declare", "dojo/on", "dojo/dom-attr", "dojo/dom-style",
		"dojo/dom-class",  'sl_modules/WAPI',
		"dojo/store/Memory","gridx/core/model/cache/Sync", "gridx/Grid",
		"gridx/modules/Sort",  "gridx/modules/Bar",
		"sl_components/listings/grid_listing/widget",
		"sl_components/selectors/attribute_mode_selector/widget",
		"sl_modules/model/sl"
		 ], function(declare, dojoOn,
		domAttr, domStyle, domClass, wapi,
		Memory,Cache,Grid,
		Sort,
		Bar,
		ModeSelector,
		slGridListing,
		sl) {
	return declare('sl_components/listings/attribute_listing', [slGridListing ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the
		// constructor
		baseClass : 'gridx attribute_listing',
		listner : null,
		projeciton_sel : null,
		format : null,
		grid : null,
		store : null,
		structures : null,
		modules:null,
		currentState:null,
		hasSetup:null,
		constructor : function(args) {
				console.log('in widget constructor');
			// Create grid widget.
				this.currentState='basic';
				this.hasSetup = false;
				dojo.safeMixin(this,args);
		},
		StartUp:function(layerId) {
			sl.GetLayerAttributes({layerId:layerId},this.HandleResults.bind(this));
			
		},
		Init : function() {
			this.structures ={basic: [
     		     { id: 'name',field: 'name', name:'Attribute', widgetsInCell: true },
     		     { id: 'requirement',field: 'requires', name:'Type'},
     		     { id: 'visible', field: 'visible', name: 'Visible'},
     		     { id: 'searchable', field: 'searchable', name: 'Searchable' }		  
              ],
              advanced: [
     		     { id: 'name',field: 'name', name:'Attribute', widgetsInCell: true },
     		     { id: 'display', field: 'display', name: 'Display Name'},
     		     { id: 'requirement',field: 'requires', name:'type'},
     		     { id: 'visible', field: 'visible', name: 'Visible'},
     		     { id: 'searchable', field: 'searchable', name: 'Searchable'},
     		     { id: 'vocab', field: 'has_vocab', name: 'Vocabulary',width:'auto'},
                  { id: 'delete', field: 'delete', name: 'Delete', widgetsInCell: true, width:"80px"}
              ]};
		},
		GetStructure:function() {
			return this.structures[this.currentState];
		},
		UpdateGrid:function() {
			if(this.grid) {
				this.grid.remove();
				this.grid.destroy();
			
			}
			this.Init(this.store);
			var cache = Cache;
			this.grid = new Grid({
				id:'grid_'+this.id,
				cacheClass : cache,
				store : this.store,
				structure : this.GetStructure(),
				autoHeight : false,
				barTop : [
				      ModeSelector
				      ],
				modules : [ Bar
				]

			});
			
			// Put it into the DOM tree. Let's assume there's a
			// node with id "gridContainer".
			this.grid.placeAt(this.domNode);
			// Start it up.
			this.grid.startup();
			
			// this.grid.column('name').sort(true);
			this.hasSetup = true;
			
		},
		HandleResults:function(event) {
			console.log(event);
			var data = [];
			for( var att in event.attributes) {
				data.push(event.attributes[att]);
			}
			this.store = new Memory({data:data,idProperty:'z'});
			console.log(this.store);
			this.UpdateGrid();
		}

	});
});