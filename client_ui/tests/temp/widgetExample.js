define([
	"dojo/_base/declare",
	"dijit/_WidgetBase",
	"dijit/_TemplatedMixin",
	"testwidgets/SomeWidget",
	"testwidgets/SomeOtherWidget",
	"dojo/text!./templates/WidgetExample.html"
],function(declare, _WidgetBase, _TemplatedMixin,someWidget,someOtherWidget, template){

	return declare('SomeWidget',[_WidgetBase, _TemplatedMixin], {
		templateString: template,
		baseClass: "someWidget",
		postCreate: function(){
			var widget2 = new someOtherWidget();
			widget2.placeAt(this.content);
			var widget = new someWidget();
			widget.placeAt(this.content);		
		}
	});
});