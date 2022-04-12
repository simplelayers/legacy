define(["dojo/_base/declare",
        "sl_components/sl_nav/widget",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/work_area.tpl.html"],
    function(declare,
    		sl_nav,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
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
        	postCreate:function(){
        		
        		// this.editorGroup = domAttr.get(this.domNode,'data-editor');
        		
        	},


        });
});
