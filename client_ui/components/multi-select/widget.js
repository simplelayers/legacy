define(["dojo/_base/declare",
        "dojo/aspect",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-construct",
        "dijit/form/Select",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        "dojo/text!./templates/multi-select.tpl.html"],
    function(declare,
    		aspect,
    		on,
    		dom,
    		domAttr,
    		domCon,
    		dijit_select,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		template){
        return declare('sl_components/multi-select',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "multi-select",
            templateString: template,
            selectors:null,
            selected:null,
            postCreate:function(){
            	this.selectors = [];
            },
            addSelection:function(newData) {
            	
            	//var defaultValue = (arguments.length == 2) ? arguments[1] : null;
            	/*for(var opt in newData.options) {
            		if(newData.options[opt].value == defaultValue) {
            			//newData.options[opt].selected = true;
            			break;
            		}
            	}*/
            	
            	var sel = new dijit_select({
            		name:'selectors_'+this.selectors.length,
            		options:newData.options
            		
            	});
            	
            	sel.placeAt(this.domNode).startup();
            	on(dijit.byId(sel.id),'change',this.SelHandler.bind(this));
            	var handler = this.SelHandler.bind(this);
            	aspect.after(sel, "_setValueAttr", function() {
            		if(sel.dropDown.isShowingNow === false) {
            			handler({"target":sel});
                    }
            	});

            	//sel.on('onchange',this.SelHandler.bind(this));
            	
            	on(sel.domNode,'change',this.SelHandler.bind(this));
            	
            	
            	if(!newData.handler) newData.handler = null;
            	var newHandler = newData.handler;
            	var selector = {'selector':sel,'handler':newHandler};
            	this.selectors.push(selector);
            	if(this.selected === null) {
            		this.SelHandler({target:sel});
            	}
            	return sel;
            	
            },
          
            SelHandler:function(e) {
            	selected = [];
            	targetIndex = -1;
            	for(var si in this.selectors) {
            		var selectorInfo = this.selectors[si];
            		targetIndex++;
            		selected.push(selectorInfo.selector.value);
            		if(e!==null) {
	            		if(selectorInfo.selector == e.target){ 
	            			
	            			if(selectorInfo.handler) {
	            				targetIndex++;
	                        	for(var i=this.selectors.length-1;i>=targetIndex ;i--) {
	                        		var selData = this.selectors.pop();
	                        		selData.selector.destroy();                        		
	                        	}
	                        	selectorInfo.handler(this,selected);                        	
	            				break;
	            			}
	            		}
            		}
            		
            	}
            	this.selected = selected;            	
            },
            Clear:function() {
            	for(var i=this.selectors.length-1;i>=0 ;i--) {
            		var selData = this.selectors.pop();
            		selData.selector.destroy();                        		
            	}
            	
            }

        });
});
