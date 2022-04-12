define([ "dojo/_base/declare", "dojo/on", "dojo/topic", 
		"dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", 'sl_components/sl_button/widget',
		"dojo/text!./templates/new_item.tpl.html", ], function(declare, on,
		topic,  _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
		sl_button, template) {
	return declare('new_item', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		// Using require.toUrl, we can get a path to our AuthorWidget's
		// space
		// and we want to have a default avatar, just in case

		// Our template - important!
		templateString : template,
		baseClass : "new_item",
		postCreate : function() {

			on(this.add_bttn, 'click', this.AddItem.bind(this));
			

		},
		AddItem : function() {
			var itemVal = this.new_item_value.value;
			var message = {};
			message.item = itemVal;
			message[this.baseClass] = this;
			topic.publish(this.baseClass + '/addItem', message);

		}

	});
});
