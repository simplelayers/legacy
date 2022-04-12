define([ "dojo/_base/declare", "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", 
		"dojo/dom", 
		"dojo/dom-construct", 
		"dojo/dom-attr",
		'dojo/dom-style',
		'dojo/dom-class',
		"dojo/on",
		"dojo/_base/lang",
		"sl_modules/WAPI", 
		"sl_modules/Pages",
		'sl_modules/sl_URL',
		'jslib/swfobject',
		"dojo/text!./templates/flexapp.html"
		],
function(declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, 
			dom, domConstruct, domAttr, domStyle, domClass,dojoOn, lang,
			sl_wapi, pages, sl_url, swfobj, template
			) {
	
	return declare('sl_apps/sl_basic',[ _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
		templateString : template,
		application: 'sl_app',
		baseClass : "sl_app",
		title:"",
		baseurl:'',
		mapId:'',
		token: '',
		user_full:'',
		user_name:'',
		auth_state:'',
		authState:'',
		sessState:'',
		accountType:'',
		map:null,
		swif:null,
		flatMap:null,
		constructor: function() {
			this.title = 'SimpleLayers: Map';
			
			this.baseurl = sl_url.getServerPath();
			var params = sl_url.getURLPathParams(document.location.href);
			
			var url = this.baseurl+("?do=get&format=swf&asset=SimpleLayers.swf");
			delete params['do'];
			url = sl_url.urlFromParamItems(url,params);
			
			this.application = pages.GetPageArg('application');
			this.swf = url;
			
			this.mapId = pages.GetPageArg('mapId');
			if(!this.mapId) this.mapId = pages.GetPageArg('project');
			
			this.inherited(arguments);
			
			return this;
		},
		startup : function() {
			domClass.add(this.domNode,'noscroll');
			//EmbedSwf();
			console.log(pages.pageArgs);
			swfobject.embedSWF(this.swf, "mApp", "100%", "100%", "10.0.0", "{$baseurl}expressInstall.swf",null,null,null,function(cb){
				//alert(cb.success);
			});
			domClass.remove(this.flex_div,'hidden');
		}
		
		

	});
});
