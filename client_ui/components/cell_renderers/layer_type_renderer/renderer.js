define(["dojo/_base/declare",
        "dojo/dom",
        "dojo/dom-attr",
        'dojo/dom-class',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_modules/sl_URL",
        "sl_modules/model/utils/LayerUtil",
        "sl_components/layer_icon/widget",
        "dojo/text!./renderer.tpl.html"
        ],
    function(declare,
    		dom,
    		domAttr,
    		domClass,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		sl_url,
    		layerUtil,
    		icon,
    		template){
    	return declare('sl_components/cellRenderers/layer_type_render',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
    		templateString:template,
    		getDecorator:function() {
    			return this.decorator.bind(this);
    		},
    		decorator:function() {
    			return this.templateString;
    		},
    		
    		setCellValue: function(gridData, storeData, cellWidget){
			   
			    
			    var info=cellWidget.cell.row.item();
			    
			    type = layerUtil.GetTypeString(+info['type']);
    			if(type=='vector') {
    				geomType = layerUtil.GetGeomTypeString(+info['geom']);
    				if(geomType != 'unknown') { 
    					type=geomType; 
    				} else {
    					type="unknown";
    				}
    				
    				
    			}
    			
    		
    			
			    this.icon.setValue(type);
			  }

    		
    		
        });
});
