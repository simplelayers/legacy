define(["dojo/_base/declare",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/wms_preview_main.tpl.html", 
        "dojo/dom-style", 
        "dojo/dom-attr",
        "dojo/topic",
        "dojo/_base/fx", 
        "dojo/_base/lang",
        'sl_components/url_params/widget',
        'sl_modules/sl_URL',
        'sl_modules/WMS',
        "dojo/domReady!"
        ],
    function(declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,template, domStyle,domAttr, dojoTopic, baseFx, lang,url_params,sl_URL,WMS){
		
        return declare([_WidgetBase, _TemplatedMixin,url_params], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            name: "No Name",
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
            url:'',
            isContainer:false,
            
            // Our template - important!
            templateString: template,
            widgetsInTemplate:true,
            // A class to be applied to the root node in our template
            baseClass: "wms_preview",
            startup:function() {
            	
            	this.setImg(this.url);	
            },
            postCreate: function() {
            	this.setURL(this.url);
            
            	dojoTopic.subscribe('url_params/state/changed',lang.hitch(this,function(data) {
            		newURL = data.url;
            		if(data.to=='view') {
            			this.url = newURL;
            			this.setImg(this.url);
            		}
            	}));
            },
        	setURL:function(url) {
        		var params = sl_URL.getURLParamObj(url);
        		var version=params['version'];
        		
        		if(version=='1.1.1') {
        			url+= "&SRS=EPSG:4326";
        		} else {
        			url += "&CRS=EPSG:4326";
        		}
        		this.url = url;
        		this.paramPreview.setURL(url);
        		this.url = url;
        		
        	},
        	layout:function() {
        		this.inherited(arguments);
                
        	},
            setImg:function(url) {
            	var apiURL =  sl_URL.getAPIPath();
            	//domStyle.set(wms_preview_img_container,'width',this.paramPreview.getWidth()+'px');
            	var width = this.paramPreview.getWidth();//domStyle.get(this.wms_preview_img_container, 'width');
            	var height = domStyle.get(this.wms_preview_img_container, 'height');
            	
            	var wmsURL = WMS.getImageURL(this.url,width,height);
            	var imgURL  = 'do=wapi.layer.wms_helper&cmd=get_img&url='+encodeURIComponent(wmsURL);
            	
            	var params = sl_URL.getURLParamObj(wmsURL);
            	
            	domAttr.set(this.wms_preview_img,'src',apiURL+imgURL);
            	domStyle.set(this.wms_preview_img,'width',params['width']+'px');
            	domStyle.set(this.wms_preview_img,'height',params['height']+'px');
            	domStyle.set(this.wms_preview_img_container,'width',params['width']+'px');
            	
            	
            	
            }
        });
});
