define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-construct",
        "dojo/data/ObjectStore",
        "dojo/store/Memory",
        "dijit/layout/ContentPane",
        'dijit/Dialog',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        "dojo/topic",
        "gridx/Grid",
        "gridx/modules/VirtualVScroller",
    	"gridx/modules/ColumnResizer",
    	"gridx/modules/extendedSelect/Row",
    	"gridx/modules/SingleSort",
    	"gridx/modules/Filter",
    	"gridx/modules/select/Row",
    	"gridx/modules/CellWidget",
        "gridx/core/model/cache/Sync",
        "sl_components/cell_renderers/layer_type_renderer/renderer",
        "sl_components/multi-select/widget",
        "sl_modules/model/sl_ViewManager",
        'sl_components/selectors/layer_views_selector/widget',
        'sl_components/sl_button/widget',
        "sl_modules/Pages",
        "dojo/text!./templates/dialog.tpl.html"],
    function(declare,
    		on,
    		dom,
    		domAttr,
    		domCon,
    		objStore,
    		memory,
    		dijitDialog,
    		dijitContentPane,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		dojoTopic,
    		gridX,
    		VirtualVScroller, ColumnResizer, SelectRow, 
    		SingleSort, Filter, SelectRow, 
    		CellWidget,
    		gridXSync,
    		layerTypeRenderer,
    		sl_multiselect,
    		viewMgr,
    		viewSel,
    		slButton,
    		pages,
    		template){
        return declare("sl_components/dialog_selectors/layer_selector/widget",[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_multiselect], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "layer_views_selector",
            templateString: template,
            currentSelections:null,
            selectedGroup: null,
            callback:null,
            userId:null,
            grid:null,
            postCreate:function(){
            	this.inherited('postCreate',arguments);
            	this.currentSelections = [];
            	this.userId = pages.GetPageArg('userId');
            	on(this.add_bttn,'click',this.ShowDialog.bind(this));
            	
            },
            ShowDialog:function(e) {
            	this.dialog_neworg.show();
            	this.views.Setup(this.HandleViews.bind(this));
            },
            HandleViews:function(selections) {
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
        	ShowResults:function(response) {
        			
        		var store = new memory({data:response.results});
        		

                var cacheClass = gridXSync; //Use Cache directly
                var renderer = new layerTypeRenderer();
                
        		structure = [
        		     
        		     { id: 'type',field: 'type', name:'',width:'32px', widgetsInCell: true,
        		    	 decorator: renderer.getDecorator(),
        		    	 setCellValue: renderer.setCellValue 
        		     },
	                 { id: 'name', field: 'name', name: 'Name',width:"25%"},
	                 { id: 'owner', field: 'owner_name', name: 'Owner',width:"25%"},
	                 { id: 'access', field: 'sharelevel', name: 'Access',width:"25%"},
	                 
	                
	                 
	             ];
        		if(this.grid) this.grid.destroy();
        		
        		 //Create grid widget.
                this.grid = gridX({
                    id:this.baseClass+'_gridx',
                	cacheClass: cacheClass,
                    store: store,
                    structure: structure,
                    modules: [
                  			VirtualVScroller,
                  			ColumnResizer,
                  			SelectRow,
                  			SingleSort,
                  			Filter,
                  			SelectRow,
                  			CellWidget
                  		]
                  	

                });
                
                
                this.grid.placeAt(this.results);
                this.grid.startup();  

        		
        	}

        
        });
});
