define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/json',
        'dojo/dom-attr',
        'dojo/dom-class',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/listings/layer_import_report/item",
        "sl_components/scrollable_listing/widget",
        "dojo/text!./templates/items.tpl.html",
        'sl_modules/Pages',
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		json,
    		domAttr,
    		domClass,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		item,
    		listing,
    		template,
    		pages,
    		wapi
    		){
        return declare('listings/layer_import_report',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            templateString: template,
            baseClass: "layer_import_report listing",
            item_obj: item,
            lastAddedItem:null,
            items:null,
            editorGroup:null,
            subscriptions:null,
            
            postCreate:function(){
            	reportId = pages.GetPageArg('report');
            	
            	wapi.exec('wapi/reporting/import/action:get/', {'report':reportId},this.ModelLoaded.bind(this));
            },

            ModelLoaded:function(report) {

            	this.items = report.data.layers;
            	this.list.SetItems(this.items,this.item_obj);
            }
        });
});
