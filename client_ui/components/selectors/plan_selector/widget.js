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
		"dojo/text!./templates/selector.tpl.html"],
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
        return declare('sl_components/selectors/plan_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_multiselect], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "plan_selector dropdownlist",
            templateString: template,
            selectedItem: null,
            plans:null,
            options:null,
            selectList:null,
            OptionSelected:function(multi,selections) {
            	
            	this.currentSelections = selections;
            	if(this.currentSelections.length == 0) {
            		sl_wapi.exec('invoicing/plans',{'format':'json','action':'list'},this.ModelLoaded.bind(this));
            	} else {
            		this.selectedItem = this.currentSelections[0];
            		
            		on.emit(this.domNode,'plan_selector/selection',{'target':this,'selection':this.selectedItem,'bubbles':true,'cancelable':true} );            		 
            	}            	
            },
            ModelLoaded:function(result){
            	if(this.selector == null) return;
            	
            	this.plans = result.results;
            	
            	options=this.SetOptions(this.plans,null);
            	
            	data = {'options':options,handler:this.OptionSelected.bind(this)};
            	this.selectList = this.selector.addSelection(data,options[0]);
            	//if(this.selection) this.SetSelection(this.selection);
            },
            SetOptions:function(newOptions,selection) {
            	this.selection =  selection;
            	this.selectedItem = selection;
            	
            	options = [];
            	if(this.selection === null) this.selection='default';
            	if(newOptions[0].id !='default') {
            		newOptions.unshift({label:'Select a plan',data:{planName:'Select a plan'},id:'default'});
            	}
				for(var i in newOptions) {
					
					
					newOptions[i].selected = (newOptions[i]['id']==this.selection);
					
					var item = newOptions[i];
					var name = (newOptions[i]['label']) ? newOptions[i]['label'] : item.data.planName;
					options.push({label:name,value:item.id,selected: newOptions[i].selected});
					
				}
				
				return options;
            },
            postCreate:function(){
            	this.currentSelections = [];
            	this.OptionSelected(this,[]);
			},
			value:function() {
				return this.selectedItem;
				if( this.currentSelections === null) return null;
				if(this.currentSelections.length==0) return null;
        		lastItem = this.currentSelections.pop();
        		this.currentSelections.push(lastItem);
        		
        		if(lastItem === null) return null;
        		
        		return lastItem.value;
        	},
        	SetSelection:function(planId) {
        		
            	this.selection = planId;
            	if(!this.plans) return;
            	this.options =  this.SetOptions(this.plans,planId);
            	for (var i in this.options ) {
            		this.options[i].selected ==( this.options[i].id == planId);
            		//this.selectList.removeOption(this.selectList.options[i]);	
            	}
            	this.selectList.options = this.options;
            	
            	this.selectList.startup();
            	
            	//this.selectList.startup();
            	/*if(this.plans === null) return;
            	for( var i=1; i < this.plans.length; i++) {
            		 if(this.plans[i].id == planId) {
            			 this.plans[i].selected=true;
            			 
            		 } else {
            			 this.plans[i].selected =false;
            		 }
            	}*/
            	//this.selectList.addOption(options);
            	
            	
        	},
        	startup:function(){
            	this.inherited('startup',arguments);
			}   
        });
});
