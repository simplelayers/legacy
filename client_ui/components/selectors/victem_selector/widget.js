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
        'dojo/query',
        'dojo/topic',
        'sl_modules/WAPI',
        "sl_components/selectors/group_selector/widget",        
        "sl_components/selectors/user_selector/widget",
        "dojo/text!./templates/victem_selector.tpl.html"],
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
    		dojoQuery,
    		dojoTopic,
    		sl_wapi,
    		sl_groups,
    		sl_users,
    		template){
		return declare('sl_components/selectors/victem_selector',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_users,sl_groups], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "victem_selector",
            templateString: template,
            selectedOption:null,
            selectorName:null,
            	
            startup:function(){
            	var radioButtons = dojoQuery("input[name="+this.selectorName+"]");
            	for(var r=0;r< radioButtons.length;r++) {
            		var radioButton =radioButtons[r];
            		on(radioButton,'click',this.radioButtonClicked.bind(this));
            	}
            	this.selectedOption = radioButtons[0].value;
            	this.radioButtonClicked({target:radioButtons[0]});
            	
            	this.inherited('startup',arguments);
            	
            	
			},
			constructor:function(name) {
            	if(name) this.selectorName = name;
            	
            	return this;
            },
			radioButtonClicked:function(e) {
    			
				this.selectedOption = e.target.value;
    			var groupVis = (this.selectedOption == 'Group') ? 'initial' : 'none';
    			var userVis = (this.selectedOption == 'Person') ? 'initial' : 'none';
    			domStyle.set(this.group_sel.domNode,'display',groupVis);
    			domStyle.set(this.user_sel.domNode,'display',userVis);
    			
    		},
    		getValue:function() {
    			response = {};
    			response.type = this.selectedOption;
    			
    			switch(response.type) {
	    			case 'Group':
	    				response.id = this.group_sel.value();
	    				break;
	    			case 'Person':
	    				response.id = this.user_sel.value();
	    				break;
	    			case 'Public':
	    				response.id = 0;
	    				break;
	    			default:
	    				response.id = '';
	    				break;
    			}    			
    			return response;
    		}    		
        });
});
