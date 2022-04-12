define([
	"dojo/_base/declare",
	"dijit/_WidgetBase",
	"dijit/_TemplatedMixin",
	"dijit/_WidgetsInTemplateMixin", 
	"dojo/text!./templates/SomeWidget.html"
],function(declare, _WidgetBase, _TemplatedMixin,_WidgetsInTemplateMixin, template){

	return declare('SomeWidget',[_WidgetBase, _TemplatedMixin,_WidgetsInTemplateMixin], {
		templateString: template,
		baseClass: "someWidget",
		postCreate: function(){
				
		}
	});
});