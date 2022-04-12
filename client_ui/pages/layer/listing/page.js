define([ "dojo/_base/declare", "dojo/on", "dojo/dom-attr","dojo/dom-style", "dojo/dom-class","dijit/_WidgetBase",
		"dijit/_TemplatedMixin", "dijit/_WidgetsInTemplateMixin",
		'dojo/dom','dojo/dom-construct','dojo/query',
		'sl_modules/WAPI',
		'sl_modules/sl_URL', 'sl_modules/Pages',
		"dojo/touch",
		"sl_modules/model/sl_ViewManager",
		'sl_components/listings/grid_listing/widget',
		'sl_components/selectors/layer_views_selector/widget',
		"sl_components/cell_renderers/layer_type_renderer/renderer",
		"sl_components/cell_renderers/access_renderer/renderer",
		"sl_components/cell_renderers/layer_name_renderer/renderer",
		"sl_components/cell_renderers/layer_bookmark_renderer/renderer",
		"dojo/text!./templates/ui.tpl.html" ], function(declare, dojoOn, domAttr,domStyle,domClass,
		_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, dom,domCon, query,
		wapi, sl_url, pages, 
		dojoTouch,
		viewMgr,
		grid_listing,
		layer_views,
		layerTypeRenderer,
		accessRenderer,
		layerNameRenderer,
		layerBMRenderer,
		template) {
	return declare('sl_pages/layer/listing', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		baseClass : 'page',
		templateString : template,
		listner:null,
		listing:null,
		projeciton_sel:null,
		format:null,
		grid:null,
		store:null,
		model:null,
		constructor : function() {
			pages.SetPageArg('pageSubnav', 'data');
			pages.SetPageArg('pageTitle', 'Data - Layer List');
			
			var typeRenderer = new layerTypeRenderer();
			var accRenderer = new accessRenderer();
			var lnameRenderer= new layerNameRenderer();
			var bookmarkRenderer = new layerBMRenderer();
			this.structure = [
    		     { id: 'type',field: 'type', name:'Type',width:'38px', widgetsInCell: true,
    		    	 decorator: typeRenderer.getDecorator(),
    		    	 setCellValue: typeRenderer.setCellValue 
    		     },
    		     { id: 'bookmarked',field: 'bookmarked', name:'',width:'38px', widgetsInCell: true,
    		    	 decorator: bookmarkRenderer.getDecorator(),
    		    	 setCellValue: bookmarkRenderer.setCellValue
    		     },
                 { id: 'name', field: 'name', name: 'Name',widgetsInCell: true,
    		    	 decorator: lnameRenderer.getDecorator(),
    		    	 setCellValue:lnameRenderer.setCellValue
                 },
                 { id: 'owner', field: 'owner_name', name: 'Owner'},
                 { id: 'description', field: 'description', name: 'Description',width:'auto'},
                 { id: 'access', field: 'sharelevel', name: 'My Access', widgetsInCell: true, width:"80px",
    		    	 decorator: accRenderer.getDecorator(),
    		    	 setCellValue: accRenderer.setCellValue},
		    	 { id: 'modified', field: 'last_modified', name: 'Last Modified'}
             ];
		},
		startup : function() {
			params = {};
			
			var widget = new grid_listing();
			widget.placeAt(this.grid_container);
			var views  = new layer_views();
			views.Setup(this.HandleView.bind(this));
			
			widget.Setup(null,this.structure,views);
			this.listing = widget;
			
            //console.log(node.parent());
            //var td = domCon.toDom('<td>Hello World</td>',node.innerHTML);
            
            
		

		},
		HandleView:function(selections) {
			this.listing.SetData([],this.structure);
    		
			var sel = selections.slice(-1).pop();
    		
    		if(sel.hasOwnProperty('wapi')) {
    			wapi = sel.wapi;
    			params = {};
    		} else {
    			wapi = selections.slice(-2,1).pop().wapi;
    			params = sel;        			
    		}
    		
    		viewMgr.getLayerView(wapi,params,this.ShowResults.bind(this));        	
		},
		ShowResults:function(event) {
			console.log(event);
			this.listing.SetData(event.results,this.structure);			
		}
	});
});