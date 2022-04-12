define(["dojo/_base/declare",
        "dojo/dom",
        "dojo/dom-attr",
        'dojo/dom-class',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_modules/sl_URL",
        "sl_modules/Pages",
        "sl_modules/model/utils/AccessUtil",
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
    		pages,
    		accessUtil,
    		icon,
    		template){
    	return declare('sl_components/cellRenderers/access_renderer',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
    		templateString:template,
    		getDecorator:function() {
    			return this.decorator.bind(this);
    		},
    		decorator:function() {
    			return this.templateString;
    		},
    		
    		setCellValue: function(gridData, storeData, cellWidget){
			   
			    
			    var info=cellWidget.cell.row.item();
			    
			    type = accessUtil.GetTypeString(+info['sharelevel']);
    			this.icon.setValue('icos_access_'+type);
    			if(pages.GetPageArg('userId')==info['owner']) {;
    		    	this.icon.setHREF(sl_url.getServerPath()+"?&do=layer.permissions&id="+info['id']);
    		    	
    			} else {
    				this.icon.setHREF("");
    			}
    			
			  }

    		
    		
        });
});
