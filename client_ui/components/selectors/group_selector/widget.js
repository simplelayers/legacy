define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-construct",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        "dojo/topic",
        "sl_components/multi-select/widget",
        "sl_modules/WAPI", 
		"dojo/text!./templates/elector.tpl.html"],
    function(declare,
    		on,
    		dom,
    		domAttr,
    		domCon,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		dojoTopic,
    		sl_multiselect,
    		sl_wapi,
    		template){
        return declare('sl_components/selectors/group_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_multiselect], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "group_selector",
            templateString: template,
            currentSelections:null,
            selectedGroup: null,
            optionSelected:function(multi,selections) {
            	this.currentSelections = selections;
            	if(this.currentSelections.length == 0) {
            		sl_wapi.exec('contact/views',{'format':'json','type':'groups'},this.viewLoaded.bind(this));
            	} else {
            		this.selectedGroup = this.currentSelections[0].value;
            		dojoTopic.publish('group-selector/selection',{'target':this,'group':this.selectedGroup} );            		 
            	}            	
            },
            viewLoaded:function(result){
            	if(this.group_selector == null) return;
            	var viewList = result.view;
            	options=this.viewToOptions(viewList);
            	data = {'options':options,handler:this.optionSelected.bind(this)}
            	this.group_selector.addSelection(data,options[0]);            	
            },
            viewToOptions:function(subView) {
            	options = [];
				for(var vi in subView) {
					var viewData = subView[vi];
					var name ='';
					if(viewData.realname) {
						name = viewData.realname;
					} else if(viewData.username ) {
						name = viewData.username;
					} else {
						name = viewData.name;
					}
					options.push({label:name,value:viewData.id});
				}
				return options;
            },
            postCreate:function(){
            	this.currentSelections = [];
            	this.optionSelected(this,[]);
			},
			value:function() {
        		if( this.currentSelections === null) return null;
        		lastItem = this.currentSelections.pop();
        		this.currentSelections.push(lastItem);
        		return lastItem.value;
        	},
        	startup:function(){
            	this.inherited('startup',arguments);
			}   
        });
});
