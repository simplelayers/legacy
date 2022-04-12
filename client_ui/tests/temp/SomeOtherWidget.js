define([
	"dojo/_base/declare",
	"dijit/_WidgetBase",
	"dijit/_TemplatedMixin",
	"dijit/_WidgetsInTemplateMixin", 
	'testwidgets/SomeWidget',
	"dojo/text!./templates/SomeOtherWidget.html"
],function(declare, _WidgetBase, _TemplatedMixin,_WidgetsInTemplateMixin, someWidget,template){

	return declare('SomeOtherWidget',[_WidgetBase, _TemplatedMixin,_WidgetsInTemplateMixin], {
		constructor:function() {
			
		},
		//	set our template
		templateString: template,
		//	some properties
		baseClass: "someWidget",
		postCreate: function(){
			var widget = new someWidget({'aParam':'aval'});
			widget.placeAt(this.someWidgets);		
		}
	});
});