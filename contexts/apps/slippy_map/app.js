define([ "dojo/_base/declare", "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", "dojo/dom", "dojo/dom-construct",
		"dojo/dom-attr", 'dojo/dom-style', "dojo/on", "dojo/topic",
		"dojo/_base/lang", "sl_modules/WAPI", "sl_modules/model/sl",
		'sl_modules/Pages', 'sl_modules/PixoSpatial',
		'jslib/leaflet-0.7.3/leaflet',
		'dojo/text!./templates/app.html' ], function(
		declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, dom,
		domConstruct, domAttr, domStyle, dojoOn, topic, lang, sl_wapi,
		sl, pages, pixospatial,L,template) {

	return declare('sl_apps/sl_basic', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		templateString : template,
		project : null,
		application : 'slippy_map',
		baseClass : "slippy_map",
		mapDiv : null,
		mapId : '',
		token : '',
		user_full : '',
		user_name : '',
		auth_state : '',
		authState : '',
		sessState : '',
		accountType : '',
		slippy_map : null,
		layerManager:null,
		featureInfo : null,
		flatMap : null,

		constructor : function() {
			sl_wapi.setApp('slippy_map');
			this.inherited(arguments);

			return this;
		},
		updateMap : function() {
			// this.map.render();
		},
		startup : function() {
			
			this.inherited(arguments);
			this.slippy_map = new L.Map(this.map_div,{'minZoom':0,'maxZoom':19});
			// create the tile layer with correct attribution
					
			
			// start the map in South-East England
			if (navigator.geolocation) {
		        navigator.geolocation.getCurrentPosition(this.setupMap.bind(this));
		    } else {
		    	this.slippy_map.setView(new L.LatLng(34.5,-119.25),12);
		    }
			
			
			//this.slippy_map.addLayer(osm);
		},
		setupMap:function(position) {
			
			this.slippy_map.setView(new L.LatLng(position.coords.latitude,position.coords.longitude),12);
		},
		updateRequest:function(request) {
			/*
			 * request.application=this.application; if(this.token != null)
			 * request.token=this.token; return request;
			 */
		},
		postCreate : function() {
			this.mapId = pages.GetPageArg('mapId');
			var request = {};
			this.updateRequest(request);
			
			sl_wapi.exec('auth/authenticate', request, this.loginHandler.bind(this));
		},
		addSizeToRequest : function(request) {
			/*
			 var styles = domStyle.getComputedStyle(this.app_content); var
			 width = styles.width.replace('px',''); var height =
			 styles.height.replace('px',''); request.width = width;
			 request.height = height;
			 */
		},
		getApplication : function() {
			// return this.application;
		},
		getWidth : function() {
			// var styles = domStyle.getComputedStyle(this.app_content);
			// return styles.width.replace('px','');
		},
		getHeight : function() {
			// var styles = domStyle.getComputedStyle(this.app_content);
			// return styles.height.replace('px','');
		},
		loginHandler : function(response) {
			
			console.log(response);
			this.token = response.token;
			pages.SetPageArg('token',this.token);
			
			this.user_name = response.username;
			this.user_full = response.fullname;
			this.auth_state = response.state;
			this.authState = response.authState;
			this.sessState = response.sessState;
			this.accountType = response.accounttype;
			var styles = domStyle.getComputedStyle(this.map_div);
			var width = styles.width;
			var height = styles.height;
			width = width.replace('px', '');
			height = height.replace('px', '');
			var request = {
				'project' : this.mapId
			};
			this.addSizeToRequest(request);
			this.updateRequest(request);
			console.log(request);
			sl_wapi.exec('map/load', request, this.projectReady.bind(this));
		},
		projectReady : function(response) {
			this.project = sl.Project({
				'project' : response.project
			});
			console.log(this.project.project);
			var bbox = this.project.get('bbox');
			//var bounds = pixospatial.BBoxStr2Extents(bbox);
			//this.slippy_map.fitBounds(bounds);
			//this.slippy_map.setMaxBounds(bounds);
			
			
			var osmUrl='https://a.tile.openstreetmap.org/{z}/{x}/{y}.png';
			var osmAttrib='Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
			
			var osm = new L.TileLayer(osmUrl, { attribution: osmAttrib});
			this.slippy_map.addLayer(osm);
			this.layerManager = new sl.LayerManger({'layers':this.project.get('layers'),'map':this.slippy_map});
			
			// this.ol_map.getView().setCenter(new
			// ol.Coordinate(center.x,center.y));

		},
		mapClicked : function(data) {
			/*
			 * var output = "Points Clicked: "; if('points' in data) { for(var
			 * i=0; i < data.points.length; i++ ){ var point = data.points[i];
			 * if(!point) continue; output += (i > 0) ? "," : ""; output +=
			 * point.ToWKT() } var mapData = this.map.GetMapData(); var layers =
			 * mapData.layers; console.log(layers); this.map.message.innerHTML =
			 * output; lastPoint = points.pop();
			 * 
			 * sl_query.queryPt(layers,mapData.id,mapData.extents.projected,this.getWidth(),this.getHeight(),lastPoint,this.HandleQueryResults.bind(this)); }
			 * //;
			 */

		},
		HandleQueryResults : function(results) {
			/*
			 * console.log('in handle query results'); console.log(results);
			 * this.featureInfo.SetQueryResults(results,this.map.GetMapData().layers);
			 * if(results.featureCount > 0) this.featureInfo.show();
			 */
		}
	});
});
