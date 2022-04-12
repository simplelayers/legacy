define([ "dojo/_base/declare", "dijit/_WidgetBase", "dijit/_TemplatedMixin","dijit/_WidgetsInTemplateMixin",
         "dojo/dom","dojo/on",
		"dojo/text!./templates/ui.tpl.html"],

function(declare, _WidgetBase, _TemplatedMixin,_WidgetsInTemplateMixin,dom,on,template) {
	return declare("sl_component/include_content/widget",[ _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
		// Some default values for our author
		// These typically map to whatever you're passing to the
		// constructor
		templateString : template,
		// A class to be applied to the root node in our
		// template
		baseClass : "include_content",
		src:'',
		SetSrc:function(src) {
			this.src = src;
			require(['dojo/text!'+src],this.ContentLoaded.bind(this));
			
		},
		ContentLoaded:function(src) {
			this.domNode.innerHTML = src;
			on.emit(this.domNode,'content_ready',{container:this});			
		}
	});
});
