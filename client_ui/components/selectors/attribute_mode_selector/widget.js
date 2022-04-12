define(
		[ "dojo/_base/declare", "dojo/on", "dojo/query", "dojo/topic",
				'dojo/dom', 'dojo/dom-construct', 'dojo/dom-class',
				'dojo/dom-attr', "dijit/_WidgetBase", "dijit/_TemplatedMixin",
				"dijit/_WidgetsInTemplateMixin", "dojo/text!./ui.tpl.html", ],
		function(declare, on, query, topic, dom, domCon, domClass, domAttr,
				_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, template) {
			return declare('selectors/attribute_mode_selector', [ _WidgetBase,
					_TemplatedMixin, _WidgetsInTemplateMixin ], {
				// Some default values for our author
				// These typically map to whatever you're passing to the
				// constructor
				// Using require.toUrl, we can get a path to our AuthorWidget's
				// space
				// and we want to have a default avatar, just in case
				item : null,
				templateString : template,
				currentState : 'basic',
				baseClass : "button attribute_mode_selectort",
				postCreate : function(args) {
					on(this.modeButton,'onClick',this.ToggleState());
				},
				ToggleState:function() {
					this.currentState = (this.currentState == 'basic') ? 'advanced' : 'basic';
					this.UpdateButton();
				},
				UpdateButton : function() {
					switch (this.currentState) {
					case 'basic':
						domAttr.set(this.modeButton, 'value', 'Advanced');
						break;
					case 'advanced':
						domAttr.set(this.modeButton, 'value','Basic');
						break;
					}
				}

			});

		});
