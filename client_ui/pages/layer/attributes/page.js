define([ "dojo/_base/declare", "dojo/on", "dojo/dom-attr","dojo/dom-style", "dojo/dom-class","dijit/_WidgetBase",
		"dijit/_TemplatedMixin", "dijit/_WidgetsInTemplateMixin",
		'dojo/dom','dojo/dom-construct','dojo/query',
		'sl_modules/WAPI',
		'sl_modules/sl_URL', 'sl_modules/Pages',
		"dojo/touch",
		"sl_components/listings/attribute_listing/widget",
		"dojo/text!./ui.tpl.html" ], function(declare, dojoOn, domAttr,domStyle,domClass,
		_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, dom,domCon, query,
		wapi, sl_url, pages, 
		dojoTouch,
		attributesListing,
		template) {
	return declare('sl_pages/layer/attributes', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		baseClass : 'page',
		templateString : template,
		listner:null,
		listing:null,
		projeciton_sel:null,
		format:null,
		grid:null,
		store:null,
		model:null,
		constructor : function() {
			pages.SetPageArg('pageSubnav', 'data');
			pages.SetPageArg('pageTitle', 'Data - Layer List');
		},
		startup : function() {
			params = {};
			
			var widget = new attributesListing();
			widget.placeAt(this.grid_container);
				
			widget.StartUp(pages.GetPageArg('layerId'));
			this.listing = widget;
		}
	});
});