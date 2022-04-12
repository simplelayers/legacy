define([ "dojo/_base/declare", "dojo/on", "dojo/dom-attr", "dojo/dom-class","dijit/_WidgetBase",
		"dijit/_TemplatedMixin", "dijit/_WidgetsInTemplateMixin",
		'dojo/dom','dojo/dom-construct','dojo/query',
		'sl_components/include_content/widget',
		'sl_modules/WAPI',
		'sl_modules/sl_URL', 'sl_modules/Pages', 
		"dojo/text!./templates/ui.tpl.html" ], function(declare, dojoOn, domAttr,domClass,
		_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, dom,domCon, query,
		include_content, wapi, sl_url, pages, template) {
	return declare('sl_pages/layer/import', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		baseClass : 'layer_import',
		templateString : template,
		listner:null,
		projeciton_sel:null,
		format:null,
		constructor : function() {
			pages.SetPageArg('pageSubnav', 'data');
			pages.SetPageArg('pageTitle', 'Data - Import Layer');
			if(pages.GetPageArg('canCreate')===false) {
				pages.GoTo('?do=layer.list');
			}	
		},
		postCreate : function() {
			params = {};
			var layerId = pages.GetPageArg('layerId');
			
			if(layerId) this.import_layer_id.value = layerId;
			var format= pages.GetPageArg('format');
			this.format = format;
			domAttr.set(this.import_fmt,'value',format.toLowerCase());
			domAttr.set(this.import_form,'action',sl_url.getAPIPath()+'/layers/import/format:html');
			this.title.innerHTML = 'Import - '+format.toUpperCase();
			
			pages.SetPageArg('pageTitle', 'Data - Import '+format.toUpperCase()+' Layer');
			if(pages.GetPageArg('layerId')) {
				pages.SetPageArg('pageTitle', 'Data - Updating  Layer #'+pages.GetPageArg('layerId')+' via '+format.toUpperCase());
			}
			this.common_ins_txt.SetSrc( sl_url.getServerPath()+'client_ui/pages/layer/import/templates/common.ins.html');
			this.format_ins_txt.SetSrc( sl_url.getServerPath()+'client_ui/pages/layer/import/formats/'+format.toLowerCase()+'.ins.html');
			dojoOn(this.projection_container,'content_ready',this.ProjectionsReady.bind(this));
			this.projection_container.SetSrc(sl_url.getAPIPath()+'map/projections/action:get/format:html');
			dojoOn(this.import_form,'submit',this.ValidateForm.bind(this));
			this.format_ins_head.innerHTML = format.toUpperCase()+" Instructions:";
			require(['dojo/text!'+sl_url.getServerPath()+'client_ui/pages/layer/import/formats/'+format.toLowerCase()+'.frm.html'],this.FormatFormReady.bind(this));
		},
		FormatFormReady:function(form) {
			
			domCon.place(form,this.format_frm,'before');
			// this.format_frm.remove();
			switch(this.format.toLowerCase()) {
				case 'csv':
					var radios = query("input[name='geometry_format']");
					for(var i=0; i < radios.length ; i++) {
						dojoOn(radios[i],'click',this.HandleGeomFieldSelection.bind(this));						
					}
					var fauxEvent = {target:radios[1]};
					this.HandleGeomFieldSelection(fauxEvent);
					break;
			}
		},
		HandleGeomFieldSelection:function(event) {
			var type = event.target.value;
			domAttr.set(dom.byId('gfmt'),'value',type);
			var formItems = query('.csv_item');
			for(var i = 0; i< formItems.length; i++ ) {
				domClass.add(formItems[i],'hidden');
				if(domClass.contains(formItems[i].id, 'csv_'+type)) {
					domClass.remove(formItems[i],'hidden');
				}				
			}
		
		},
		
		ProjectionsReady:function(event) {
			this.projection_sel = dom.byId('projection_sel');
			dojoOn(this.projection_sel,'change',this.ProjectSelected.bind(this));
		},
		
		ValidateForm : function(event) {
			var formdata = event.target;
			dot = /[^ _A-Za-z0-9]/;
			basename = formdata.elements['name'].value;
			checkname = basename.match(dot);
			if (checkname) {
				alert('Base layer names are restricted to alphanumeric characters, spaces, and underscores.  Please rename.');
				event.preventDefault();
				return false;
			}

			if (!formdata.elements['source'].value) {
				if (!this.fileURL.value) {
					alert('Please select a file or provide a URL.');
					event.preventDefault();
					return false;
				}
			}
			
			// if (!formdata.elements['name'].value) { return false; }
			alert('Files may take a while to upload. Please be patient.');
			return true;
		},
		
		ProjectSelected:function(event) {
			//console.log(this.projection_sel.value);
		}
		
	});
});