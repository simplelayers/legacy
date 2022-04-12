define([ "dojo/_base/declare", "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", "dojo/dom", "dojo/dom-construct", "dojo/dom-attr",
		"dojo/on", "dojo/string", "dojo/_base/lang", 'dojo/dom-style',
		'dojo/topic', "sl_components/sl_button/widget",
		"dojo/text!./templates/login.tpl.html" ],

function(declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
		dom, domConstruct, domAttr, on, string, lang, domStyle, dojoTopic,
		sl_button, template) {
	return declare([ _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
			sl_button ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the
		// constructor
		templateString : template,
		current_state : 'view',
		// A class to be applied to the root node in our
		// template
		baseClass : "login_panel",
		postCreate : function() {
			//this.login_button.setLabel('Login');
    		//this.login_button.setColor('green');
    		//this.login_button.setIcon('power_ico');
			
			this.heading.innerHTML = "Welcome to SimpleLayers";        	
			this.message.innerHTML = "Please enter a username and password to continue";
        	this.setState(this.current_state);
        	
		},
		startup : function() {
			this.inherited(arguments);

		},
		setState : function(whichState) {
			var oldState = this.current_state;
			switch (whichState) {
			default:
				break;
			}
			this.current_state = whichState;
			dojoTopic.publish('login_window/state/changed', {
				from : oldState,
				to : this.current_state,
				target : this
			})
		},
		switchState : function() {
			switch (this.current_state) {
			default:
				/*
				 * this.setState('view');
				 */
				break;
			}
			
		}

	});
});
