define([ "dojo/_base/declare", 
         "dojo/on", 
         "dojo/topic", 
         'dojo/json',
		'dojo/dom-class', 
		"dijit/_WidgetBase", 
		"dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin",
		"dijit/Dialog",
		'dijit/form/Button',
		'sl_components/listings/employees/new_item',
		'sl_components/listings/employees/item', 
		'sl_modules/WAPI',
		"sl_components/scrollable_listing/widget",
		"sl_components/scrollable_listing/footer/widget",
		"dojo/text!./templates/items.tpl.html"],
		function(declare, 
					on, 
					topic, 
					json, 
					domClass, 
					_WidgetBase,
					_TemplatedMixin,
					_WidgetsInTemplateMixin, 
					dialog,
					dijit_button,
					new_item, 
					item, 
					wapi,
					listing, 
					footer, 
					template) {
			return declare('listings/employees', [ _WidgetBase, _TemplatedMixin,
					_WidgetsInTemplateMixin ], {
				orgId:null,
				members:null,
				licenseLookup:null,
				seatLookup:null,
				seatAassignments:null,
				templateString : template,
				baseClass : "employees",
				startMessage : '',
				item_obj : item,
				defaultHeading : '',
				signals:null,
				
				postCreate : function() {
					
					this.signals = [];
					on(this.item_adder.domNode,'new_item/add_item',this.AddItem.bind(this));
					on(this.footer.domNode,'scrollable_listing/update',this.SaveChanges.bind(this));
					on(this.footer.domNode,'scrollable_listing/refresh',this.LoadModel.bind(this));
					
				},
				Refresh : function(info) {
					this.list.UpdateItems(info);
				},
				LoadModel: function(event) {
					
					var params = {action:'list'};
					if(event) {
						if(event.hasOwnProperty('orgId')) {
							this.orgId = event.orgId;
						}
					}
					if(this.orgId) 	params['orgId']=this.orgId;
					
					wapi.exec('organization/employees',params,this.ModelReady.bind(this));
					
				},
				ModelReady : function(response) {
					results = response.results;
					
					this.orgId = results.org;
					this.employees = results.members;
					this.licenseLookup=results.license_lookup;
					this.seatAssignments = results.seatAssignments;		
					this.seatLookup = results.seats_lookup;
					this.list.SetItems(results.seatAssignments, item,{data:results,stats:this.seatStats});
					on.emit(this.domNode,'listings/employees/model_ready',{src:this,bubbles:false,cancelable:true});
				},
				GetSeatId:function(name) {
					for(var i in this.seatLookup) {
						if(name.toLowerCase().replace(' ','') == this.seatLookup[i].toLowerCase().replace(' ','') ) {
							return i; 
						}						
					}
				},
				AddItem : function(event) {
					
					params = event.item;
					params.action = 'add';
					params.orgId = this.orgId;
					params.seatId = this.GetSeatId('unassigned');
					wapi.exec('organization/employees',params,this.ItemAdded.bind(this));
				},
				ItemAdded : function(response) {
					this.LoadModel();

				},
				SaveChanges : function(event) {
					params = {};
					params.action = 'save';
					params.orgid = this.orgId;
					params.changeset = json.stringify({json:{assignments:this.seatAssignments,employees:this.employees}});
					
					wapi.exec('organization/employees',params,this.ChangesSaved.bind(this));
				},
				ChangesSaved : function(response) {
					this.LoadModel();
				},

			});
		});
