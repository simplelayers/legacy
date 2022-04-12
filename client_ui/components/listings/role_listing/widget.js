define([ "dojo/_base/declare", "dojo/on", "dojo/topic", 'dojo/json',
		'dojo/dom-class', "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin",
		"sl_components/scrollable_listing/new_item/widget",
		"sl_components/scrollable_listing/widget",
		"sl_components/scrollable_listing/footer/widget",
		'sl_components/listings/role_listing/role', 'sl_modules/WAPI',
		"dojo/text!./templates/roles.tpl.html", "sl_modules/sl_URL" ],
		function(declare, on, topic, json, domClass, _WidgetBase,
				_TemplatedMixin, _WidgetsInTemplateMixin, new_item, listing,
				footer, role, wapi, template) {
			return declare('role_listing', [ _WidgetBase, _TemplatedMixin,
					_WidgetsInTemplateMixin ], {
				// Some default values for our author
				// These typically map to whatever you're passing to the
				// constructor
				// Using require.toUrl, we can get a path to our AuthorWidget's
				// space
				// and we want to have a default avatar, just in case

				// Our template - important!
				templateString : template,
				baseClass : "role_listing",
				context : role,
				startMessage : '',
				item_obj : role,
				context : null,
				defaultHeading : '',
				postCreate : function() {
					this.startMessage = this.item_adder_noContext.innerHTML;
					this.defaultHeading = this.list_heading.innerHTML;
					topic.subscribe('new_item/addItem',this.AddItem.bind(this));
					topic.subscribe('scrollable_listing/refresh', this.Refresh
							.bind(this));
					topic.subscribe('scrollable_listing/update',
							this.SaveChanges.bind(this));
					this.SetMessage(this.startMessage);
				},
				SetMessage : function(message) {
					this.item_adder_noContext.innerHTML = message;
					domClass.add(this.item_adder.domNode, 'hidden');
					domClass.remove(this.item_adder_noContext, 'hidden');
				},
				SetHeading : function(message) {
					var heading = (message) ? this.context.context + ' - <i>'
							+ message + '</i>' : this.context.context;
					this.list_heading.innerHTML = this.defaultHeading + ' : '
							+ heading;

				},
				SetContext : function(context) {
					this.context = context;
					this.SetHeading();
					if (this.context)
						this.Refresh();

				},
				GetContext: function() {
					return this.context;
				},
				Refresh : function(event) {

					if (!event)
						event = {
							src : this.footer
						};
					if (event.src != this.footer)
						return;
					
					this.SetMessage('Loading Role Permissions');

					this.list.RemoveItems();

					wapi.exec('permissions/roles', {
						action : 'list',
						'list' : 'role',
						'contextId' : this.context.id
					}, this.ModelReady.bind(this));

				},
				ModelReady : function(response) {
					
					if (response.results.data.roles.length == 0) {
						this.SetHeading('no results found');
					}
					
					this.list.SetItems(response.results.data.roles,
							this.item_obj);
					domClass.remove(this.item_adder.domNode, 'hidden');
					domClass.add(this.item_adder_noContext, 'hidden');
				},
				AddItem : function(event) {
					if (event.new_item == this.item_adder) {
						var item = event.item;
						wapi.exec('permissions/roles', {
							action : 'add',
							'add' : 'role',
							'contextId' : this.context.id,
							'role' : item
						}, this.ItemAdded.bind(this));
					}
				},
				ItemAdded : function(event) {
					this.Refresh();
				},
				SaveChanges : function(event) {
					if (event.src != this.footer)
						return;
					;
					var items = json.stringify({
						'json' : this.list.items
					});

					wapi.exec('wapi/permissions/roles',{'action':'save','save':'role','contextId':this.context.id,'changeset':items},this.ChangesSaved.bind(this));

				},
				ChangesSaved : function(response) {
					this.Refresh();
				}

			});
		});
