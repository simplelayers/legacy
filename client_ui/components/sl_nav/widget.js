define([ "dojo/_base/declare", 
         "dojo/parser", 
         "dojo/on", 
         "dojo/dom-class",
         "dojo/dom-attr",
         "dojo/dom-construct",
         'dojo/json',         
		"dojo/query", 
		"dojo/dom-prop", 
		"dijit/Toolbar",
		"dijit/form/Button",
		"dijit/form/ToggleButton", 
		"dijit/ToolbarSeparator",
		"dijit/_WidgetBase", "dijit/_TemplatedMixin", "dijit/_WidgetsInTemplateMixin", 
		'sl_components/sl_nav/sl_subnav',
		'sl_modules/sl_URL',
		'sl_modules/Pages',
		"dojo/text!./templates/ui.tpl.html"
		],

function(declare, parser, on, domClass, domAttr, domCon,  json,  query, domProp, toolbar, 
		button, toggleButton, sep,
		_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
		sl_subnav,
		sl_url,
		pages,
		template) {
	return declare('sl_components/sl_nav', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		baseClass : "sl_nav",
		templateString : template,
		buttons : null,
		subNavURL:null,
		subNavName:null,
		postCreate : function() {
		
			pages.SetPageNav(this);
			this.buttons = [ this.admin, this.organization, this.community,
					this.data, this.maps, this.permissions,this.help ];
			this.organization.containerNode.innerHTML=pages.GetPageArg('userOrgName');
			for ( var i in this.buttons) {
				var button = this.buttons[i];
				if (button)
					on(button, 'click', this.HandleButtonClick.bind(this));
			}
			if(!pages.sl_permissions.HasPermission(':SysAdmin:General:',pages.sl_permissions.VIEW)) {
				
				
				this.admin.destroy();
				this.admin_sep.destroy();
			}
			if(pages.sl_permissions.HasPermission(':Organizations:Invites:',pages.sl_permissions.CREATE)) {
				domAttr.set(this.invite_link,'href', sl_url.getServerPath()+'organization/invites/cmd:add/');
			} else {
				domClass.add(this.invite_link,'hidden');
			}
		},
		SetSubnav:function(subnavName) {
			baseURL = sl_url.getServerPath();
			this.subNavName = subnavName;
			this.subNavURL=baseURL+'client_ui/components/sl_nav/subnav/'+subnavName+'.dat.json';
			require(["dojo/text!"+this.subNavURL],this.ParseSubnav.bind(this));
		},
		ParseSubnav:function(data) {
			try{
				data = json.parse(data);
			} catch( e) {
				console.log('Bad script or script not found: '+this.subNavURL);
			}
			this.subnav.SetData(data,this.subNavName);
		},
		HandleButtonClick : function(event) {
			for ( var i in this.buttons) {
				var button = this.buttons[i];
				if (!button) continue;
				button._set('checked', false);
				button.startup();
				if (button.domNode == event.target.parentNode) {
					button._set('checked', true);
					switch(button) {
						case this.admin:
							pages.GoTo('/admin/organization/list/');
							break;
						case this.organization:
							pages.GoTo('?do=organization.info');
							break;
						case this.community:
							pages.GoTo('?do=group.list');
							break;
						case this.data:
							pages.GoTo('?do=layer.list');
							break;
						case this.maps:
							pages.GoTo('?do=project.list');
							break;
						case this.permissions:
							break;
						case this.help:
							break;
					}
				}
				
			}
			/*
			 * switch(event.target) { case this.admin: break; case this.org:
			 * break; case this.community: break; case this.data: break; case
			 * this.maps: break; case this.permissions: break; }
			 */
		}

	});
});
