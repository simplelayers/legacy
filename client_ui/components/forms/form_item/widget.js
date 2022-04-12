define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-class",
        "dojo/dom-construct",
        'dijit/form/Button',
        'dijit/form/CheckBox',
        'dijit/form/Select',
        'dijit/form/TextBox',
        'dijit/form/ValidationTextBox',
        'dijit/form/Textarea',
        'dijit/form/DateTextBox',
		'dijit/form/TimeTextBox',
		'dijit/form/NumberTextBox',
		"dijit/form/ComboBox", 
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-style',
        "dojo/Evented",
        'sl_modules/WAPI',
        'sl_modules/sl_URL',
        'sl_modules/sl_Sorts',
        'sl_modules/model/attributes',
         "dojo/text!./templates/form_item.tpl.html"],
    function(declare,
    		on,
    		dom,
    		domAttr,
    		domClass,
    		domCon,
    		dijit_formButton,
    		dijit_checkbox,
    		dijit_sel,
    		dijit_text,
    		dijit_validationTB,
    		dijit_textarea,
    		dijit_date,
    		dijit_time,
    		dijit_numeric,
    		ComboBox,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domStyle,
    		Evented,
    		sl_wapi,
    		sl_url,
    		sl_sorts,
    		sl_attributes,
    		template){
        return declare('sl_components/forms/form_item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,Evented], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "form_item",
            problemId:null,
            startState:null,
            reporter:null,
            reporterName:null,
            templateString: template,
            selectedAtt:null,
            name:null,
            display:null,
            value:null,
            inputObj:null,
            constructor:function(args) {
            	
            	return this;
            },
            postCreate:function() {
            	
            	
            },
            SetItem:function(attName,att){
            	
            	this.label.innerHTML = att.display;
            	console.log(att);
            	var requirement = att.rquires;
            	if(att.vocab) {
	            	if(att.vocab.data.length>0){ 
	            		requirement=  sl_attributes.REQUIRES_FILTERING_SELECT;
	            	}
            	}
            	
            	switch(requirement) {
            		case sl_attributes.REQUIRES_INTEGER:
            			var intInput = new dijit_numeric({name:attName});
            			on(intInput,'change',this.ValueHandler.bind(this));
	            		//intInput.validator = this.numericValidator.bind(this);
            			intInput.placeAt(this.input,'first');	
            			this.inputObj = intInput;
            			break;
            		case sl_attributes.REQUIRES_TEXT:
            		case sl_attributes.REQUIRES_INPUT:
                		var textInput = new dijit_text({name:attName,regExp:'^(\+|-)?\d+$'});
            			on(textInput,'change',this.ValueHandler.bind(this));
            			textInput.placeAt(this.input,'first');	            
            			this.inputObj = textInput;
            				break;
            		case sl_attributes.REQUIRES_TEXT_AREA:
            			var textAreaInput = new dijit_textarea({name:attName});
            			on(textAreaInput,'change',this.ValueHandler.bind(this));
            			textAreaInput.placeAt(this.input,'first');
            			this.inputObj = textAreaInput;
        				break;
        			case sl_attributes.REQUIRES_FLOAT:
        				var intInput = new dijit_numeric({name:attName});
        				on(intInput,'change',this.ValueHandler.bind(this));
	            		//intInput.validator = this.numericValidator.bind(this);
            			intInput.placeAt(this.input,'first');
            			this.inputObj = intInput;         			
            			break;
            		case sl_attributes.REQUIRES_BOOLEAN:
            			var cbInput = new dijit_checkbox({name:attName,value:true});
            			cbInput.placeAt(this.input,'first');
            			on(cbInput,'change',this.ValueHandler.bind(this));
            			this.inputObj = cbInput;         			
            			break;
            		case sl_attributes.REQUIRES_DATE:
            			var date = new Date();
            			var dateInput = new dijit_date({name:attName,value:date});
            			dateInput.placeAt(this.input,'first');
            			on(dateInput,'change',this.ValueHandler.bind(this));
            			this.inputObj = dateInput;    
    			
            			break;
            		case sl_attributes.REQUIRES_URL:
            			var urlInput = new dijit_text({name:attName});
            			urlInput.placeAt(this.input,'first');
            			on(urlInput,'change',this.ValueHandler.bind(this));
            			var testBttn = new dijit_formButton({label:'Test URL'});
            			testBttn.placeAt(this.input,'last');
            			this.inputObj = urlInput;         			
            		case sl_attributes.REQUIRES_FILTERING_SELECT:
            			var selInput = new ComboBox({name:attName,value:'',store:att.vocab,searchAttr:'name'},attName);
            			selInput.placeAt(this.input,'first');
            			on(selInput,'change',this.ValueHandler.bind(this));
            			this.inputObj = selInput;
            			break;
            	}
            	this.display = att.display;
            	
            },
            SetPhotoItem:function(attName,att){
            	this.name = attName;
            	this.label.innerHTML = att.display;
            	this.input.innerHTML = '<input name="'+attName+'" class="dijit dijitTextBox" type="file"  accept="image/*" capture="camera" ></input>';
            	this.display = att.display;
            	
            },
            SetDateItem:function(attName,att){
            	this.name = attName;
            	var date = new Date();
            	this.label.innerHTML = att.display + ': '+(''+date).split('(').shift();
            	this.input.innerHTML = '<input name="'+attName+'" type="hidden" value="'+(''+date).split('(').shift()+'" ></input>';
            	this.display = att.display;
            	
            },
            SetLocationItem:function(name,display) {
            	this.name = name;
            	this.label.innerHTML = display;
            	this.display = display;
            	var bttn = new dijit_formButton({label:'Get Location'});
            	bttn.placeAt(this.input,'last');
            	on(bttn,'click',this.GetLocation.bind(this));
            	this.locButton = bttn;
            },
            
            GetLocation:function(accuratly) {
            	this.label.innerHTML = this.display;
            	if(accuratly===null) accuratly=true;
            	navigator.geolocation.getCurrentPosition(this.SetLocation.bind(this),null);
            	/*if(accuratly) {
            		navigator.geolocation.getCurrentPosition(this.SetLocation.bind(this),this.LocationError.bind(this),{'enableHighAccuracy':true,"timeout":10});
            	} else {
            		
            	}*/
            	            	
            },
            LocationError:function(event) {
            	this.GetLocation(false);
            },
            ClearDisplay:function() {
            	this.label.innerHTML = this.display;
            },
            SetLocation:function(location) {
            	var wkt_geom = 'POINT('+location.coords.longitude+' '+location.coords.latitude+')';
            	this.label.innerHTML = this.display +':<BR>'+location.coords.longitude+' deg Lon<BR>'+location.coords.latitude+' deg Lat';
            	this.input_hidden.innerHTML ='<input type="hidden" name="wkt_geom" value="'+wkt_geom+'" ></input>';
            	this.emit('location_ready',{"location":location});
            },
            SetSubmit:function(label) {
            	this.label.innerHTML='';
            	domCon.destroy(this.label_container);
            	var bttn = new dijit_formButton({label:label,type:'submit'});
            	this.input.innerHTML = '<hr/>';
            	var div = domCon.place(domCon.toDom('<div class="container"></div>'),this.input);
            	
            	bttn.placeAt(div);
            	
            	
            	domClass.add(bttn,'form_submit_button');
            	
            },
            
            SetGotoItem:function(url) {
            	this.label.innerHTML='';
            	this.input_hidden.innerHTML = '<input name="goto" type="hidden" value="'+url+'"></input>';   
            	
            },
            
            numericValidator:function(value,constraints) {
            	if(parseInt(value) == NaN) return false;
            	
            },
            SetValue:function(val) {
            	this.inputObj.set('value',val);
            	this.ValueHandler(val);
            },
            ValueHandler:function(val) {
            	this.value = val;
            }
           
           
          
        });
});
