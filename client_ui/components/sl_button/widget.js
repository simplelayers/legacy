define(["dojo/_base/declare",
        "dojo/on",
        "dojo/touch",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        "dojo/text!./templates/sl_button.tpl.html",
        "sl_modules/sl_URL"],
    function(declare,
    		on,
    		touch,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		template,
    		sl_url){
        return declare('ico_bttn',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            bttnlabel:"",
            bttnicon:"",
            bttncolor:"normal",
        	baseClass : "sl_button",
            _buttonColor:this.bttncolor,
            _startDisplay:null,
            startSize:null,
            buttonStatus:null,
            startClass:null,
            _bttnName:null,
            
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
           
            // Our template - important!
            templateString: template,
            emptyImgURL:'',
            constructor:function() {
            	this.emptyImgURL= sl_url.getEmptyImgURL();
            	
            },
            setColor:function(newColor) {
            	if(!this.bttn) return;
            	var oldColor = this.bttncolor;
            	if(domClass.contains(this.bttn,oldColor) ) {
            		domClass.remove(this.bttn,oldColor);
            	}
            	this.bttncolor = newColor;
            	
            	domClass.add(this.bttn,this.bttncolor);
            	var name = domAttr.get(this.bttn,'name');
            	
            	domAttr.set(this.bttn,'name','_temp_');
            	domAttr.set(this.bttn,'name',name);
            },
            setId:function(id) {
            	this._bttnName= id;
            },
            setLabel:function(newLabel) {
            	this.sl_bttn_label.innerHTML = newLabel;
            	//domAttr.set(this.sl_bttn_ico,'alt',newLabel);
            },
            setIcon:function(newIcon) {
            	domClass.remove(this.sl_bttn_ico,'pencil');
            	if(newIcon == '') newIcon = 'none';
            	
            	ignoreStart = false;
            	if(newIcon.substr(0,5)=='icos_') {
            		domClass.remove(this.sl_bttn_ico);
            		ignoreStart = true;
            	}
            	
            	
            	if(!this.bttn) return;
            	
            	//var oldIcon = this.bttnicon;
            	if(domClass.contains(this.sl_bttn_ico,this.bttnicon) ) {
            		domClass.remove(this.sl_bttn_ico);
            	}
            	if(!ignoreStart) domClass.add(this.sl_bttn_ico,this.startClass);
            	
            	this.bttnicon = newIcon;
            	if(this.bttnicon == 'none') {
            		domClass.add(this.sl_bttn_ico,'hidden');
            		domClass.remove(this.domNode,'ico_button');
            		
            	}
            	else {
            		if(this.bttnicon != '') domClass.add(this.sl_bttn_ico,this.bttnicon);
            	}
            	
            },
            // A class to be applied to the root node in our template
            baseClass: "ico_button",
            postCreate:function(){
            	this.startup();
            	
            	
            },
            startup: function() {
            	
            	if(!this.bttn) return;
            	if(this.sl_bttn_ico) this.startClass = domAttr.get(this.sl_bttn_ico,'class');
            	
            	var color = domAttr.get(this.bttn,'data-bttnColor');
            	if(color) {
            		this.setColor(color);
            	} else {
            		this.setColor(this.bttncolor);
            	}
            	var prefLabel = domAttr.get(this.bttn,'data-bttnLabel');
            	
            	if(prefLabel) { this.setLabel(prefLabel); }
            	
            	else {
            		domClass.add(this.bttn,'noLabel');
            	}
            	
            	var prefIco = domAttr.get(this.bttn,'data-bttnIcon');
            	if(prefIco == '') prefIco = 'none';
            	if(prefIco) this.setIcon(prefIco);
            	
            	var prefSize = domAttr.get(this.bttn,'data-bttnSize');
            	
            	if(prefSize) {
            		domClass.add(this.bttn,prefSize); 
            	} else if(this.startSize) {
            		domClass.add(this.bttn,this.startSize);
            	}
            	//this.enable();
            	var ico  = domAttr.get(this.domNode,'data-bttnIcon');
            	if(ico) this.setIcon(ico);
            	
            	domAttr.set(this.sl_bttn_ico,'src',this.emptyImgURL);
            	 /*if(this.bttn) {
		            	 on(this.bttn,'click',function(evt) {
		            		 alert('clicked');
		            	 });
            	 }*/
            },
            setSize:function(size) {
            	this.startSize = size;
            },
            hide:function(){
            	this._startDisplay = domStyle.get(this.bttn,'display');
            	domStyle.set(this.bttn,'display','none');
            },
            show:function() {
            	domStyle.set(this.bttn,'display',this._startDisplay);
            },
            enable:function() {
            	domClass.remove(this.domNode,'disabled');
            	domAttr.set(this.domNode,'disabled',false);
            	this.buttonStatus = 'enabled';
            	return this;
            },
            disable:function() {
            	domClass.add(this.domNode,'disabled');
            	domAttr.set(this.domNode,'disabled','disabled');
            	this.buttonStatus = 'disabled';
            	return this;
            },
            addListener:function(func) {
            	touch.press(this.domNode);
            	
            }

        });
});
