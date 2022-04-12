define([ "dojo/_base/declare", "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", 
		"dojo/dom", 
		"dojo/dom-construct", 
		"dojo/dom-attr",
		'dojo/dom-style',
		'dojo/dom-class',
		"dojo/on",
		"dojo/topic",
		"dojo/_base/lang",
		"dijit/form/ComboBox",
		"sl_modules/sl_URL",
		"sl_modules/WAPI", 
		'sl_modules/Pages',
		'sl_modules/model/attributes',
		'sl_components/forms/form_item/widget',
		"dojo/text!./templates/app.html"
		],
function(declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, 
			dom, domConstruct, domAttr, domStyle,domClass,dojoOn,topic, lang,
			ComboBox,
			sl_url,	sl_wapi,pages,model_attributes,form_item,template
			) {
	
	return declare('sl_apps/data_entry',[ _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
		templateString : template,
		application: 'data_entry',
		baseClass : "data_entry",
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
		locItem:null,
		lonItem:null,
		latItem:null,
		accItem:null,
		idField:null,
		constructor: function() {
			sl_wapi.setApp('data_entry');
			console.log('in app constructor');
			this.inherited(arguments);
			//dojoOn(window,'orientationchange',lang.hitch(this,this.updateMap));
			return this;
		},
		startup:function() {
			domAttr.set(this.logo,'src',sl_url.getLogoURL());
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
			topic.subscribe('sl_model/attributes',this.AttributesReady.bind(this));
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
			
			var url = ''+document.location;
			console.log(url);
			var pageArgs = sl_url.getURLPathParams(url);
			console.log(pageArgs);
		
			pageArgs.permissions = response.permissions;
			pageArgs.token = response.token;
			pageArgs.userInfo = response;
			pages.MergePageData(pageArgs);
			this.LoadAttributes();
			
		},
		LoadAttributes:function() {
			model_attributes.CacheLayerAttributes(false);
				
		},
		AttributesReady:function(response) {
			var attributes = pages.GetPageArg('dataSource_attributes');
			
			var lonField = pages.GetPageArg('lon_field');
			var latField = pages.GetPageArg('lat_field');
			var accField = pages.GetPageArg('accuracy_field');
			var idField = pages.GetPageArg('id_field');
			
			imageField = pages.GetPageArg('image_field');
			
			for ( var attName in attributes ) {
				console.log(attName);
				var placeNode = true;
				console.log(attName);
				var att = attributes[attName];
				if(att.visible==false) continue;
				
				att.form_item =  new form_item();
				
				if(lonField != null) {
					if( attName.toLowerCase() == lonField.toLowerCase()) this.lonItem = att.form_item;
				}
				if(latField != null) {
					if( attName.toLowerCase() == latField.toLowerCase()) this.latItem = att.form_item;
				}
				if(accField != null) {
					if( attName.toLowerCase() == accField.toLowerCase()) this.accItem = att.form_item;
				}
				if(idField != null) {
					if( attName.toLowerCase() == idField.toLowerCase()) {
						this.idField = attName;
						placeNode = false;
					}
					
				}
				
				if(imageField == attName) {
					att.form_item.SetPhotoItem(attName,att);
				} else if(pages.GetPageArg('date_field')==attName) {
					att.form_item.SetDateItem(attName,att);
				} else {
				
					att.form_item.SetItem(attName,att);
				}
				if(placeNode) {
					att.form_item.placeAt(this.data_entry_form);
				}
				
			}
			
			var locating = false;
			if(pages.GetPageArg('locate')==1) {
				locating = true;
				this.locItem = new form_item();
				this.locItem.SetLocationItem('wkt_geom','Location');
				
				this.locItem.on('location_ready',this.UpdateLatLon.bind(this));
				this.locItem.placeAt(this.data_entry_form);
			}
			
			
			
		
			/*var go2 = sl_url.getServerPath()+'app/data_entry/layerId:'+pages.GetPageArg('layerId');
			if(pages.GetPageArg('image_field')) go2+='/image_field:'+pages.GetPageArg('image_field');
			if(pages.GetPageArg('date_field')) go2+='/date_field:'+pages.GetPageArg('date_field');
			if(pages.GetPageArg('locate')) go2+= '/locate:1';
			
			*/
			
			var submitBtn = new form_item();
			submitBtn.SetSubmit('Save Changes');
			submitBtn.placeAt(this.data_entry_form);
			var gotoItem = new form_item();
			var go2 = document.location.href;
			gotoItem.SetGotoItem(go2);
			gotoItem.placeAt(this.data_entry_form);
			
			
			var action = sl_url.getAPIPath();
			action+= 'features/feature/action:save';
			if(imageField) action+='/image_field:'+imageField;
			if(locating) action+='/locate:1';
			action+='/layerId:'+pages.GetPageArg('layerId');
			if(this.idField) {
				action+='/id_field:'+this.idField;
			}
			domAttr.set(this.data_entry_form,'action',action);
			domAttr.set(this.data_entry_form,'method','POST');
			domAttr.set(this.data_entry_form,'enctype',"multipart/form-data");
			
			
			
			
		},
		UpdateLatLon:function(event) {
			console.log(event.location);
			var fields = 0;
			if(this.lonItem) {
				fields+=1;
				this.lonItem.SetValue(''+event.location.coords.longitude);				
			}
			if(this.latItem) {
				fields+=1;
				this.latItem.SetValue(''+event.location.coords.latitude);				
			}
			if(fields==2) {
				this.locItem.ClearDisplay();
			}
			if(this.accItem) {
				this.accItem.SetValue(''+event.location.coords.accuracy);
			}
		
		}
		
	});
});
