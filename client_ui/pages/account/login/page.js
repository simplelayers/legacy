define([ "dojo/_base/declare", "dojo/on", "dojo/cookie","dojo/dom-attr", "dijit/_WidgetBase",
		"dijit/_TemplatedMixin", "dijit/_WidgetsInTemplateMixin",
		"dijit/form/TextBox", 'dojo/dom-class', 'dojo/dom-style',
		'dojo/parser', 'dojo/dom-construct', 'dojo/topic', 'dojo/json',
		'sl_components/sl_button/widget', 'sl_modules/WAPI',
		'sl_modules/sl_URL', 'sl_modules/Pages',
		"dojo/text!./templates/ui.tpl.html" ], function(declare, on, cookie, domAttr,
		_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, textBox,
		domClass, domStyle, parser, domCon, topic, json, sl_button, wapi,
		sl_url, pages, template) {
	return declare('sl_pages/login', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		baseClass : 'login_page ',
		templateString : template,
		permissions : null,
		imporetViewed : false,
		baseURL : '',
		loginInfo : null,
		constructor : function() {

		},
		isSandboxed:false,
		postCreate : function() {
			this.loginInfo = pages.GetPageArg('loginInfo');

		
			if(this.loginInfo.hasOwnProperty('sandbox_org')) {
				this.isSandboxed = true;
				var sb_org = this.loginInfo['sandbox_org'];
														
				if( sb_org >  1) {
					if(this.loginInfo['messageHeader'].substr(0,7)=='Welcome'){
						this.loginInfo['messageHeader'] = '&nbsp;';
						domAttr.set(this.logo_link,'href','./');
					}
				}
				if(this.loginInfo.hasOwnProperty('org_disclaimer')) {
					domAttr.set(this.disclaimer ,'src',sl_url.getServerPath()+'wapi/organization/disclaimer/action:get/application:dmi/orgId:'+sb_org );
					domClass.remove(this.disclaimerContainer,'hidden');
					domClass.add(this.loginContainer,'hidden');					
				}
				
			}
			on(this.accept_bttn,'click',this.HandleAccept.bind(this));
			this.setupForm(this.loginInfo);
			domAttr.set(this.logo_img, 'src', sl_url.getServerPath()
					+ 'logo.php');
			domAttr.set(this.login_form,'action',sl_url.getServerPath()+'?do=account.login');
			domClass.remove(this.logo_img, 'hidden');
			var path = pages.GetPageArg('go_to');
			domAttr.set(this.return_to ,'value',path);
			
			
		},
		
		HandleAccept:function(){
			domClass.add(this.disclaimerContainer,'hidden');
			domClass.remove(this.loginContainer,'hidden');
		},
		
		setState : function(state) {

		},
		setupForm : function(loginInfo) {
			this.login_header.innerHTML = loginInfo['messageHeader'];
			this.login_message.innerHTML = loginInfo['message'];
			domClass.add(this.domNode, loginInfo['state']);
		}

	});
});
