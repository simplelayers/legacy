define([ "dojo/_base/declare", "dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", "dojo/dom", "dojo/dom-construct", "dojo/dom-attr",
		"dojo/on", "dojo/string", "dojo/_base/lang", 'dojo/dom-class',
		'dojo/topic', "sl_components/sl_button/widget",
		"dojo/text!./templates/data_pager.tpl.html" ],

function(declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
		dom, domConstruct, domAttr, on, string, lang, domClass, dojoTopic,
		sl_button, template) {
	return declare([ _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
			sl_button ], {
		templateString : template,
		baseClass : "data_pager",
		pagingData:null,
		postCreated:false,
		construct:function(args) {
			this.pagingData = args;
		},
		postCreate : function() {
			on(this.next_page,'click',this.GoNext.bind(this));
			on(this.prev_page,'click',this.GoPrev.bind(this));
			domClass.add(this.domNode,'hidden');
			this.postCreated = true;
		},
		SetPagingData:function(data) {
			this.pagingData = data;
			if(this.postCreated) this.FillData();
			domClass.remove(this.domNode,'hidden');
			
		},
		FillData:function() {
			
			this.from_record.innerHTML = +this.pagingData.first +1;
			this.to_record.innerHTML = +this.pagingData.last +1;
			this.num_records.innerHTML = this.pagingData.count;
			
			if(this.pagingData.prev < 0) {
				domClass.add(this.prev_page,'hidden');
			} else {
				domClass.remove(this.prev_page,'hidden');
			}
			if(this.pagingData.next=='') {
				domClass.add(this.next_page,'hidden');
			} else {
				domClass.remove(this.next_page,'hidden');
			}
			if(this.pagingData.count == 0) {
				domClass.remove(this.no_data_message,'hidden');
				domClass.add(this.paging_data_row,'hidden');
				
			} else {
				domClass.add(this.no_data_message,'hidden');
				domClass.remove(this.paging_data_row,'hidden');
			}
			
			
		},
		GoNext:function() {
			on.emit(this.domNode,'data_pager/next',{'src':this.pagingData});
			
		},
		GoPrev:function() {
			on.emit(this.domNode,'data_pager/prev',{'src':this.pagingData});
			
			
		}
		
	});
});
