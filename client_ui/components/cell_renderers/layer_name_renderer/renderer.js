define(["dojo/_base/declare",
        "dojo/html",
        "dojo/dom",
        "dojo/dom-attr",
        'dojo/dom-class',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_modules/sl_URL",
        "sl_modules/model/utils/AccessUtil",
        "sl_components/layer_icon/widget",
        "dojo/text!./renderer.tpl.html"
        ],
    function(declare,
    		html,
    		dom,
    		domAttr,
    		domClass,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		sl_url,
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
			    console.log(gridData);
			    this.link.innerHTML=gridData;
			    
			   this.link.href=sl_url.getServerPath()+"?do=layer.edit1&id="+info['id'];
    			
			  }

    		
    		
        });
});
