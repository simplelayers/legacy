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
        'dojo/topic',
        'sl_modules/WAPI',
        "sl_components/multi-select/widget",
         "dojo/text!./templates/user_selector.tpl.html"],
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
    		sl_wapi,
    		sl_multiselect,
    		template){
        return declare('sl_components/selectors/user_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_multiselect], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "user_selector",
            templateString: template,
            currentSelections:null,
            optionSelected:function(multi,selections) {
            	this.currentSelections = selections;
            	if(this.currentSelections.length == 0 ) {
            		sl_wapi.exec('contact/views',{'format':'json'},this.viewLoaded.bind(this));
            	} else if(this.currentSelections.length == 1) {
            		var view = selections[0].value;
            		if(view=='groups') {
            			sl_wapi.exec('contact/views',{'format':'json','type':view},this.viewLoaded.bind(this));
            		} else {
            			sl_wapi.exec('contact/views',{'format':'json','type':view},this.viewLoaded.bind(this));
            		}            		
            	} else if(this.currentSelections.length == 2) {
            		var lastItem  = this.currentSelections[this.currentSelections.length-1].value;
            		
            		var view = selections[0].value;
            		if(view == 'groups') {
            			sl_wapi.exec('contact/views',{'format':'json','type':'group','id':lastItem},this.viewLoaded.bind(this));
            		}
            		
            		
            		dojoTopic.publish('user-selector/selection',{'target':this,'user':lastItem});
            	}            	
            },
            viewLoaded:function(result){
            	if(this.user_selector==null) return;
            	var viewList = result.view;
            	options= (this.currentSelections.length==0) ? viewList : this.viewToOptions(viewList);
            	data = {'options':options,handler:this.optionSelected.bind(this)}
            	this.user_selector.addSelection(data,options[0]);
            	
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
            postCreate:function() {
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
