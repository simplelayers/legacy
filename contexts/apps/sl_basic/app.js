define([ "dojo/_base/declare", "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", 
		"dojo/dom", 
		"dojo/dom-construct", 
		"dojo/dom-attr",
		'dojo/dom-style',
		"dojo/on",
		"dojo/topic",
		"dojo/_base/lang",
		"sl_modules/WAPI", 
		"sl_modules/Map",
		'sl_modules/Query',
		'sl_modules/Interactions',
		"sl_components/feature_info/widget",
		"sl_components/map_flat/widget",
		'sl_modules/Pages',
		"dojo/text!./templates/app.html"
		],
function(declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, 
			dom, domConstruct, domAttr, domStyle, dojoOn,topic, lang,
			sl_wapi, sl_map, sl_query, sl_interaction,sl_featureInfo, slapp_map_flat,
			pages,template
			) {
	
	return declare('sl_apps/sl_basic',[ _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
		templateString : template,
		application: 'sl_basic',
		baseClass : "sl_basic",
		mapId:'',
		token: '',
		user_full:'',
		user_name:'',
		auth_state:'',
		authState:'',
		sessState:'',
		accountType:'',
		map:null,
		featureInfo:null,
		flatMap:null,
		constructor: function() {
			sl_wapi.setApp('sl_basic');
			console.log('in app constructor');
			
		
			this.inherited(arguments);

			dojoOn(window,'orientationchange',lang.hitch(this,this.updateMap));
			return this;
		},
		updateMap:function() {
			this.map.render();
		},
		startup:function() {
			
			this.inherited(arguments);
		},
		updateRequest: function(request) {
			request.application=this.application;
			if(this.token != null) request.token=this.token;
			return request;
		},
		postCreate : function() {
			this.mapId=pages.GetPageArg('mapId');
			console.log('in postCreate');
			this.featureInfo = new sl_featureInfo({style:"width:300px"});
			//this.featureInfo.placeAt(this);
			//this.app_content.innerHTML="hello world";
			var request = {};
			this.updateRequest(request);
			sl_wapi.exec('auth/authenticate',request,this.loginHandler.bind(this));			
		},
		addSizeToRequest: function(request) {
			var styles = domStyle.getComputedStyle(this.app_content);
			var width = styles.width.replace('px','');
			var height = styles.height.replace('px','');
			request.width = width;
			request.height = height;
			
		},
		getApplication:function() {
			return this.application;
		},
		getWidth:function(){
			var styles = domStyle.getComputedStyle(this.app_content);
			return styles.width.replace('px','');
		},
		getHeight:function() {
			var styles = domStyle.getComputedStyle(this.app_content);
			return styles.height.replace('px','');
		},
		loginHandler : function(response) {
			
			console.log(response);
			this.token = response.token;
			this.user_name = response.username;
			this.user_full = response.fullname;
			this.auth_state = response.state;
			this.authState = response.authState;
			this.sessState = response.sessState;
			this.accountType = response.accounttype;
			var styles = domStyle.getComputedStyle(this.app_content);
			var width = styles.width;
			var height = styles.height;
			width = width.replace('px','');
			height = height.replace('px','');
			var request = {'project':this.mapId};
			this.addSizeToRequest(request);
			this.updateRequest(request);
			console.log(request);
			sl_wapi.exec('map/load',request,this.projectReady.bind(this));
		},
		projectReady: function(response) {
			console.log('projectReady');
			try{
				var map = new slapp_map_flat({'mapData':response.project,'addedBy':this});
				map.placeAt(this.app_content)
				this.interactionMgr = new sl_interaction(map);
				topic.subscribe('sl_interaction/clicks',this.mapClicked.bind(this));
				this.map = map;
			//this.flatMap = new slapp_map_flat(this, response.project, this.token);
			}catch(e) {
				this.app_content.innerHTML = e.stack;
				return;
			}
			
			
			map.render();
			
		},
		mapClicked:function(data) {
			var output = "Points Clicked: ";
			if('points' in data) {
				for(var i=0; i < data.points.length; i++ ){
					var point = data.points[i];
					if(!point) continue;
					output += (i > 0) ? "," : "";
					output += point.ToWKT()
					
				}	
				var mapData = this.map.GetMapData();
				var layers = mapData.layers;
				console.log(layers);
				this.map.message.innerHTML = output;
				lastPoint = points.pop();
				
				sl_query.queryPt(layers,mapData.id,mapData.extents.projected,this.getWidth(),this.getHeight(),lastPoint,this.HandleQueryResults.bind(this));
				
			}
			//;
			
		},
		HandleQueryResults:function(results){
			console.log('in handle query results');
			console.log(results);
			this.featureInfo.SetQueryResults(results,this.map.GetMapData().layers);
			if(results.featureCount > 0) this.featureInfo.show();
		}
	});
});
