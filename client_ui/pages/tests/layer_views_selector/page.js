define(["dojo/_base/declare",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "sl_components/dialog_selectors/layer_selector/widget",
        "dojo/text!./templates/work_area.tpl.html"],
    function(declare,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		layerSel,
    		template){
        return declare('nav_test',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'nav_test',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	currentView:null,
        	editorGroup:null,
        	license:null,
        	planChanged:false,
        	hasLoaded:false,
        	postCreate:function() {
        		//this.views.Setup(this.HandleViews.bind(this))
        		// this.editorGroup = domAttr.get(this.domNode,'data-editor');        		
        	
        	}
        	

        });
});
