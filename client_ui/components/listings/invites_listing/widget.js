define([ "dojo/_base/declare", "dojo/on", "dojo/topic", 'dojo/json',
		'dojo/dom-class', "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin",
		"sl_components/listings/invites_listing/new_item",
		"sl_components/scrollable_listing/widget",
		"sl_components/scrollable_listing/footer/widget",
		'sl_components/listings/invites_listing/invite', 
		'sl_modules/WAPI',
		'sl_modules/Pages',
		"dojo/text!./templates/items.tpl.html", "sl_modules/sl_URL" ],
		function(declare, on, topic, json, domClass, _WidgetBase,
				_TemplatedMixin, _WidgetsInTemplateMixin, newItem, listing,
				footer, invite, wapi, pages, template) {
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
				baseClass : "invites_listing listing",
				item_obj : invite,
				postCreate : function() {
					on(this.domNode,'item_added',this.AddItem.bind(this));
					on(this.footer,'scrollable_listing/refresh',this.Refresh.bind(this));
					on(this.footer,'scrollable_listing/update',this.SaveChanges.bind(this));
					on(this.list.domNode,'invite_item/create_org',this.CreateOrg.bind(this));

					/*topic.subscribe('new_item/addItem',this.AddItem.bind(this));
					topic.subscribe('scrollable_listing/refresh', this.Refresh
							.bind(this));
					topic.subscribe('scrollable_listing/update',
							this.SaveChanges.bind(this));
					*/
					
					wapi_cmd='organization/organizations';
					params =this.GetParams();
					
				},
				GetParams:function() {
					params = {'action':'list','target':'invites'};
					
					if((pages.GetPageArg('pageActor')!='admin') && pages.GetPageArg('orgId')) {
						params['orgId'] = pages.GetPageArg('orgId');
					}
					return params;
				},
				NewItem:function() {
					this.new_item.ShowDialog();
				},
				Refresh:function() {
					wapi_cmd='organization/organizations';
					params = this.GetParams();
					wapi.exec(wapi_cmd,params,this.ModelReady.bind(this));
				},
				ModelReady : function(response) {
					results = response.results;
					this.list.SetItems(results,this.item_obj);
				},
				AddItem : function(event) {
					var baseParams = {'action':'add','target':'invites'};
					for(var key in event.item) {
						baseParams[key] = event.item[key];
					}
					baseParams['orgId'] = pages.GetPageArg('orgId');
					wapi.exec('organization/organizations', baseParams , this.ItemAdded.bind(this));
				},
				ItemAdded : function(event) {
					this.Refresh();
				},
				SaveChanges : function(event) {
					var items = json.stringify({
						'json' : this.list.items
					});
					var params={'action':'save','target':'invites','changeset':items};
					if(pages.GetPageArg('orgId')) {
						params['orgId'] = pages.GetPageArg('orgId');
					}
					
					wapi.exec('organization/organizations',params,this.ChangesSaved.bind(this));
				},
				ChangesSaved : function(response) {
					this.Refresh();
				},
				CreateOrg:function(event){
					var params = {};
					if(pages.GetPageArg('orgId')) params['orgId'] = pages.GetPageArg('orgId');
					params['org_name'] = event.item.organization;
					params['owner_name']= event.item.name;
					params['owner_email'] = event.item.email;
					params['refId']=event.item.id;
					pages.SetPageData('admin/organization/list',params);
					pages.GoTo('admin/organization/list',{},false);
					
				}

			});
		});
