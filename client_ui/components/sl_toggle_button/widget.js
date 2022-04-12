define(["dojo/_base/declare",
        "dojo/on",
        "dojo/touch",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'dojo/topic',
        'sl_components/sl_button/widget',
        "dojo/text!./templates/sl_button.tpl.html"],
    function(declare,
    		on,
    		touch,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		topic,
    		sl_button,
    		template){
        return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,sl_button], {
            // Our template - important!
            templateString: template,
            baseClass : "sl_toggle_button",
            onValue:null,
            offValue:0,
            noValue:'',
            onColor:'blue',
            offColor:'normal',
            buttonState:false,
            toggleMode:true,
            value:null,
            inputType:'mouse',
            toggleQuiet:false,
            postCreate:function() {
            	
            },
            startup:function() {
            	this.inherited(arguments);
            	var onColor = domAttr.get(this.bttn,'data-onColor');
            	var offColor = domAttr.get(this.bttn,'data-offColor');
            	var toggleMode = domAttr.get(this.bttn,'data-toggling');
            	var defaultState = domAttr.get(this.bttn,'data-state');
            	if(toggleMode === null) toggleMode ='on';
            	
            	if(!onColor) this.onColor = 'blue';
            	this.offColor = offColor;
            	
            	if(!(domAttr.get(this.domNode,'type')=='submit')) {
            		domAttr.set(this.domNode,'type','button');
            	} 
            	
            	
            	
            	if(!this.offColor) {
            		this.offColor = 'normal';
            	}
            	
            	if(toggleMode=='off') this.toggleMode = false;
            	
            	var value=domAttr.get(this.bttn,'data-value');

            	this.setValue(value);
            	if(defaultState !== null ) {
            		if(defaultState == 'on') {
            			this.toggle(true);
            		} else {
            			this.toggle(false);
            		}
            	}
            	//touch.press(this.domNode,this.toggleClickHandler.bind(this));
            	this.domNode.addEventListener('touchend',this.toggleClickHandler.bind(this),false);
            	on(this.domNode,'click',this.toggleClickHandler.bind(this));
             	
            },
        	setValue:function(val) {
        		
        		if(val===undefined) val = null;
        		
        		this.onValue = val;
        		this.value = val;
        		if(val===null) {
        			domAttr.remove(this.domNode,'data-value');
        			this.setColor(this.offColor);
        			this.disable();
        		} else {
        			domAttr.set(this.domNode,'data-value',val);
        			this.enable();
        		}
        		
        		this.toggle(this.buttonState);
        	},
        	setTitle:function(title){ 
        		domAttr.set(this.domNode,'title',title);
        	},
        	enable:function() {
        		this.inherited(arguments);
        		
        		
        	},
        	disable:function() {
        	 	domAttr.set(this.bttn,'disabled',true);
            	this.toggleMode = false;
        		
        	},
            toggle:function() {
            	
            	if(!this.toggleMode) {
            		
            		this.setColor(this.offColor);
            		return;
            	}
            	if(arguments.length>0){
            		this.buttonState = arguments[0];
            	} else {
            		this.buttonState = !this.buttonState;
            	}
            	if(this.buttonState) {
            		this.setColor(this.onColor);
            		domClass.remove(this.domNode,'toggled_off');
            		domClass.add(this.domNode,'toggled_on');
            		this.value = this.onValue;
            	} else {
            		this.setColor(this.offColor);
            		domClass.remove(this.domNode,'toggled_on');
            		domClass.add(this.domNode,'toggled_off');
            		this.value = this.offValue;
            	}
            	//this.enable();            	
            }, 
            toggleClickHandler:function(event) {
            	
        		if(event.type=='touchend') this.inputType='touch';
        		if(this.inputType=='touch' && event.type=='click') return;
        		event.stopImmediatePropagation();
        		if(this.domNode) if(domClass.contains(this.domNode,'disabled')) return;
        		if(this.buttonStatus == 'disabled') return;
        		
        		this.toggle();
        		this.emit('sl_toggle_button/value_change',{bubbles: true, cancelable: true,toggleButton:this,value:this.value,isOn:this.buttonState});
        		topic.publish('sl_toggle_button/value_change',{toggleButton:this,value:this.value,isOn:this.buttonState});
        		
        	}
        });
});
