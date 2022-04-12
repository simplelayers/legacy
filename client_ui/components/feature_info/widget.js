define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dijit/Dialog",
        'dojo/dom-class',
        'dojo/dom-style',
        "sl_components/sl_button/widget",
        "sl_modules/FeatureInfo",
        "dojo/text!./templates/feature_info.tpl.html"
        ],
    function(declare,
    		on,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		dialog,
    		domClass,
    		domStyle,
    		sl_button,
    		featureInfo,
    		template){
        return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,dialog], {
        	templateString:template,
        	features:null,
        	currentFeature:-1,
        	featureLabel:'Query Results:',
        	featureOffset:-1,
        	featureCount:-1,
        	SetQueryResults:function(results,layers) {
        		this.titleNode.innerHTML = 'Query Results:'
        		this.featureCount = results.featureCount;
        		this.featureCountLabel.innerHTML = this.featureCount;
        		if(this.featureCount > 0) this.currentFeature = 0;
        		results = results.results;
        		this.features = [];
        		for (var r in results) {
        			var result = results[r];
        			for( var f in result.features ) {}
        				var feature = result.features[f];
        				var plid = result.plid;
        				feature._featureText = featureInfo().GetFeatureText(feature,plid,layers);
        			this.features.push( feature );
        		}
        		this.ShowFeature(this.currentFeature);
        		  
        	},
        	ShowFeature:function(offset) {
        		this.featureOffset = offset+1;
        		if(!this.featureOffset) return false;
        		this.featureOffset = offset+1;
        		this.featureOffsetLabel.innerHTML = this.featureOffset;        		
        		this.featureDisplay.innerHTML = this.features[offset]._featureText;
        	}
        	
        
        	
        
        }
        
        );
});
