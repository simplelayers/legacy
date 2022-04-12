define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-construct",
        "dojo/data/ObjectStore",
        "dojo/store/Memory",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        "dojo/topic",
        "sl_components/multi-select/widget",
        "sl_modules/model/sl_ViewManager",
        "sl_modules/Pages",
		"dojo/text!./templates/selector.tpl.html"],
    function(declare,
    		on,
    		dom,
    		domAttr,
    		domCon,
    		objStore,
    		memory,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		dojoTopic,
    		sl_multiselect,
    		viewMgr,
    		pages,
    		template){
        return declare('sl_components/selectors/layer_views_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_multiselect], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "layer_views_selector",
            templateString: template,
            currentSelections:null,
            selectedGroup: null,
            views:null,
            callback:null,
            userId:null,
            isSetup:false,
            postCreate:function(){
            	this.inherited('postCreate',arguments);
            	this.currentSelections = [];
            	this.userId = pages.GetPageArg('userId');
            },
			Setup:function(handler) {
				this.view_selector.Clear();
				this.callback = handler;
				viewMgr.getLayerViews({'userId':this.userId},this.ModelLoaded.bind(this));
				//wapi.exec("wapi/layers/views/view:list/format:json",{'userId':userId},this.ModelLoaded.bind(this));
			},
			ModelLoaded:function(result) {
				this.views = result.views;
				
				options=this.ViewToOptions(this.views);
				
				data = {'options':options,handler:this.OptionSelected.bind(this)};
            	this.view_selector.addSelection(data,options[0]);    
				
			},
            OptionSelected:function(multi,selections) {
            	if( !selections) selections = [];
            	selection = (selections.length >0 )? selections[selections.length-1] : null;
            	options = [];
            	data = null;
            	
            	if(selection) {
            		
            		if(selection.options) {
            			options=this.ViewToOptions(selection.name);
            			if(options.length==0) return this.callback(selections);;
            			data = {'options':options,handler:this.OptionSelected.bind(this)};
            			this.view_selector.addSelection(data,options[0]);
            		}
            		
            		
            	} else {
            		options=this.ViewToOptions(this.views);
            		data = {'options':{'data':options},handler:this.OptionSelected.bind(this)};
            		this.view_selector.addSelection(data,options[0]);
            	}
            	this.callback(selections);
            	return data;	
            },
            ViewToOptions:function(subView) {
            	options = [];
            	if(subView == this.views) {
            		offset = -1;
            		for( var view in this.views) {
            			offset++;
            			viewData = this.views[view];
            			option = {"label":viewData.label,"value":viewData};
    					options.push(option);
            		}
            		return options;
            	} 
	            switch(subView) {
	            case 'mine':
	            case 'tags':
	            case 'bookmarked':
	            	return false;
	            	break;
	            case 'shared':
	            case 'groups':
	            	for( var o in this.views[subView].options) {
	            		optionData = this.views[subView].options[o];
	        			option = {"label":optionData.name,"value":optionData};
    					options.push(option);
            		 	
	            	}
	            	break;
	            }
            	return options;
            	
            },
            value:function() {
        		if( this.currentSelections === null) return null;
        		lastItem = this.currentSelections.pop();
        		this.currentSelections.push(lastItem);
        		return lastItem.value;
        	}
        	
        });
});
