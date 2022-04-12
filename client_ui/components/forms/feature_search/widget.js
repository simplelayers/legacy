define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-construct",
        "dojo/query",
        'dijit/form/TextBox',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'dojo/json',
        'dojo/topic',
        'sl_modules/WAPI',
        'sl_components/forms/criterion/widget',
        'sl_components/sl_toggle_button/widget',    
        'sl_components/selectors/attribute_selector/widget',
        'sl_components/selectors/operator_selector/widget',
        'sl_components/icon/widget',
        'sl_modules/sl_URL',
        'sl_modules/sl_Sorts',
        'sl_modules/Pages',
        'sl_modules/WAPI',
        "dojo/text!./templates/form.tpl.html"],
    function(declare,
    		on,
    		dom,
    		domAttr,
    		domCon,
    		query,
    		textbox,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		json,
    		topic,
    		sl_wapi,
    		criterion,
    		sl_button,
    		attr_sel,
    		operator_sel,
    		sl_icon,
    		sl_url,
    		sl_sorts,
    		pages,
    		wapi,
    		template){
        return declare('sl_components/forms/feature_search',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "feature_search",
            problemId:null,
            startState:null,
            reporter:null,
            reporterName:null,
            templateString: template,
            firstFilter:null,
            filters:null,
            constructor:function(args) {
            	
            	return this;
            },
            postCreate:function() {
            	this.firstFilter = this.first_criterion;
            	//this.firstFIlter.HandleRemove();
            	on(this.add_bttn,'click',this.AddFilter.bind(this));      
            	on(this.search_bttn,'click',this.HandleSearch.bind(this));
            	this.filters = [];
            	this.filters.push(this.firstFilter);
            },
           AddFilter:function() {
        	   var filter = new criterion();
        	   on(filter,'deleted',this.HandleDeletion.bind(this));
        	   filter.placeAt(this.criteria);
        	   this.filters.push(filter);
           },
           HandleDeletion:function(event) {
        	   i = this.filters.indexOf(event.item);
        	   this.filters.splice(i,1);        	   
           },
           HandleSearch:function(event) {
        	   this.Search(0);
        	  
           },
           HandleResults:function(results) {
        	   topic.publish('sl_form/feature_search',{'form':this,'data':results});
           },
           Search:function(start) {
        	   searchFilters = [];
        	   filterSet = this.filters.slice(0);
        	   if(filterSet.length == 1) {
        		   var filter = filterSet[0].GetFilterInfo();
        		   if(filter.field == '') filterSet = [];
        		   
        	   }
        	   for(var i in filterSet) {
        		   searchFilters.push(filterSet[i].GetFilterInfo());
        	   }
        	  params = {};
        	  params.filters = json.stringify(searchFilters);
        	  params.limit = this.limit_search.value;
        	  params.first = start;
        	  
        	  wapi.exec('wapi/features/filtered_search/layerId:'+pages.GetPageArg('layerId'),params,this.HandleResults.bind(this));
           },
           PageNext:function(event) {
        	 this.Search(event.src.next);   
           },
           PagePrev:function(event) {
        	   this.Search(event.src.prev);
           },
           AttachPager:function(pager) {
        	   on(pager.domNode,'data_pager/next',this.PageNext.bind(this));
        	   on(pager.domNode,'data_pager/prev',this.PagePrev.bind(this));
           	
           }
          
        });
});
