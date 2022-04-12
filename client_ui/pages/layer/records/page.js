define([ "dojo/_base/declare", "dojo/on", "dojo/dom-attr", "dijit/_WidgetBase",
		"dijit/_TemplatedMixin", "dijit/_WidgetsInTemplateMixin",
		"dijit/form/TextBox", 'dojo/dom-class', 'dojo/dom-style',
		'dojo/parser', 'dojo/dom-construct', 'dojo/topic', 'dojo/json',
		'dojo/store/Memory',
		'dgrid/OnDemandGrid','dgrid/extensions/ColumnReorder','dgrid/Keyboard', 'dgrid/Selection','sl_components/sl_button/widget',
		'sl_components/data_pager/widget','sl_components/forms/feature_search/widget', 'sl_modules/WAPI',
		'sl_modules/sl_URL', 'sl_modules/Pages', 'sl_modules/model/attributes',
		"dojo/text!./templates/ui.tpl.html" ], function(declare, on, domAttr,
		_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, textBox,
		domClass, domStyle, parser, domCon, topic, json,Memory, dgrid, ColumnReorder,Keyboard,Selection,sl_button,
		data_pager,feature_search, wapi, sl_url, pages,attributes_model, template) {
	return declare('sl_pages/layer/records', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		baseClass : 'layer_records ',
		templateString : template,
		permissions : null,
		imporetViewed : false,
		baseURL : '',
		loginInfo : null,
		grid:null,
		fieldData:null,
		listner:null,
		constructor : function() {
			pages.SetPageArg('pageSubnav', 'data');
			pages.SetPageArg('pageTitle', 'Data - Layer Records');
			topic.subscribe('sl_model/attributes',this.FieldsReady.bind(this));
			topic.subscribe('sl_form/feature_search',this.ResultsReady.bind(this));
		},
		postCreate : function() {
			params = {};
        	params.layerId = pages.GetPageArg('layerId');
        	params.features = 'meta';
        	if(domAttr.get(this.domNode,'data-searchable')) {
        		params.features += ',searchable';
        	}
        	attributes_model.CacheLayerAttributes();
        	this.searchUI.AttachPager(this.pager);


		},
		FieldsReady:function(info) {
			this.fieldData = info.data;
			this.fieldData['_action'] = {label:'',rendeCell:this.ActionRenderer.bind(this)};
			this.fieldData['_edit'] = {label:'',renderCell:this.EditRenderer.bind(this)};
		
		},
		ActionRenderer:function(object, value, node, options) {
			
		},
		EditRenderer:function(object, value, node, options) {
			
		},
		ResultsReady:function(info) {
			
			if(this.grid) {
				if(this.listener)this.listener.remove();
			}
			
			this.grid = new (declare([dgrid,ColumnReorder,Selection,Keyboard]))({
				selectionMode: 'toggle',
				columns : this.fieldData,
			}, this.results_dgrid);
			this.grid.renderArray(info.data.results);
			this.listener = on(this.grid,'dgrid-select',this.SelectionHandler.bind(this));			
			this.pager.SetPagingData(info.data.metadata.pagingData);
		},
		SelectionHandler:function(event) {
			
		}
		
	});
});
