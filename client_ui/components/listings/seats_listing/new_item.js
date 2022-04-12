define([ "dojo/_base/declare", "dojo/on", "dojo/topic", "dijit/_WidgetBase",
		"dijit/_TemplatedMixin", "dijit/_WidgetsInTemplateMixin",
		'sl_components/sl_button/widget',
		'sl_components/selectors/role_selector/widget',
		"dojo/text!./templates/new_item.tpl.html" ], function(declare, on,
		topic, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
		sl_button, role_selector, template) {
	return declare('new_seat', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		// Using require.toUrl, we can get a path to our AuthorWidget's
		// space
		// and we want to have a default avatar, just in case

		// Our template - important!
		templateString : template,
		baseClass : "new_seat new_item",
		roles : null,
		postCreate : function() {
			topic.subscribe('selectors/role_selector/roles_loaded',
					this.RolesReady.bind(this));
			on(this.add_bttn, 'click', this.AddItem.bind(this));
		},
		RolesReady : function(event) {
			if (event.src != this.role_selector)
				return;
			this.roles = event.roles;
			topic.publish('new_seat/roles_ready', {
				src : event.roles,
				roles : this.roles
			});
		},
		AddItem : function() {
			var seat = {};
			seat.role = this.role_selector.GetSelection();
			seat.name = this.seat_name.value; 
			var message = {};
			message.item = seat;
			message[this.baseClass] = this;
			message['src'] = this;
			topic.publish(this.baseClass + '/addItem', message);

		}

	});
});
