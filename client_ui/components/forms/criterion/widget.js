define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom",
        "dojo/dom-attr",
        "dojo/dom-construct",
        'dijit/form/Select',
        'dijit/form/TextBox',
        'dijit/form/Textarea',
        'dijit/form/DateTextBox',
		'dijit/form/TimeTextBox',
		'dijit/form/NumberTextBox',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'sl_modules/WAPI',
        'sl_components/sl_toggle_button/widget',    
        'sl_components/icon/widget',
        'sl_modules/sl_URL',
        'sl_modules/sl_Sorts',
         "dojo/text!./templates/form.tpl.html"],
    function(declare,
    		on,
    		dom,
    		domAttr,
    		domCon,
    		dijit_sel,
    		dijit_text,
    		dijit_textarea,
    		dijit_date,
    		dijit_time,
    		dijit_numeric,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		sl_wapi,
    		sl_button,
    		sl_icon,
    		sl_url,
    		sl_sorts,
    		template){
        return declare('sl_components/forms/criterion',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "search_criterion",
            problemId:null,
            startState:null,
            reporter:null,
            reporterName:null,
            templateString: template,
            selectedAtt:null,
            attReqCategories:['alphanum','alphanum_area','date','time','numeric'],
            constructor:function(args) {
            	
            	return this;
            },
            postCreate:function() {
            	on(this.criteria1,'change',this.HangleAttChange.bind(this));
            	isFirst  = domAttr.get(this.domNode,'data-is_first');
            	on(this.numeric,'keydown',this.NumberCleaner.bind(this));
            	on(this.delete_bttn,'click',this.HandleRemove.bind(this));
            	domClass.add(this.heading,'hidden');
            	if(isFirst) {
            		domClass.add(this.comparisons.domNode,'hidden');
            		domClass.add(this.delete_bttn.domNode,'hidden');
            		domClass.remove(this.heading,'hidden');
            			
            		
            		this.end_group_bttn.setValue(null);
            	}
            	for(var c in this.attReqCategories) {
            		
            		var cat = this.attReqCategories[c];
            		domClass.add(this[cat].domNode,'hidden');            		
            	}
            	
            },
            HangleAttChange:function(event) {
            	
            	this.selectedAtt = event.selectedItem;
            	
            	var meta = this.selectedAtt.meta_info;
            	for(var c in this.attReqCategories) {
            		var cat = this.attReqCategories[c];
            		domClass.add(this[cat].domNode,'hidden');            		
            	}
            	switch(meta.category) {
            	case 'binary':
            		break;
            	case 'alphanum': 
            		if(this.selectedAtt.requires=='text area') {
            			domClass.remove(this.alphanum_area.domNode,'hidden');
            			this.alphanum_area.set('value','');
            			
            		} else {
            			domClass.remove(this.alphanum.domNode,'hidden');
            			this.alphanum.set('value','');
            		}
            		break;
            	case 'date':
            		domClass.remove(this.date.domNode,'hidden');
            		break;
            	case 'time':
            		domClass.remove(this.date.domNode,'hidden');
            		break;
            	case 'numeric':
            		domClass.remove(this.numeric.domNode,'hidden');
            		break;
            	}
            	
            },
           NumberCleaner:function(event) {
        	   
        	   if(event.char.replace(/[-\d\\.]/g,'').length > 0){
        		   
        		   
        		   if(event.key == 'Backspace') return;
        		   if(event.key == 'Tab') return;
        		   event.preventDefault();
        	   } else {
        		   
        		   //if(event.char.split('.').length>2) event.preventDefault();
        	   }
           },           
           HandleRemove:function(event) {
        	   this.emit('deleted',{cancelable:true, bubbles:true,item:this});
        	   this.destroy();
        	 
           },
           GetFilterInfo:function() {
        	   var filter = {};
        	   filter.andor = this.comparisons.get('value');
        	   filter.group  = this.start_group_bttn.value == 1;
        	   filter.group_end = this.end_group_bttn.value == 1;
        	   filter.is_not = this.not_bttn.value == 1;
        	   filter.field = (this.criteria1.selectedItem) ?  this.criteria1.selectedItem.display : '';
        	   filter.compare = (this.criteria2.selectedItem) ? this.criteria2.selectedItem : '';
        	   filter.modifier =  (this.ignore_case_bttn.value == 1) ? '' : 'lower';
        	   
        	   var meta =   (this.selectedAtt) ? this.selectedAtt.meta_info : '';
        	   category = meta ? meta.category : '';
        		switch(category) {
               	case 'binary':
               		break;
               	case 'alphanum':
               		if(this.selectedAtt.requires=='text area') {
               			filter.value = this.alphanum_area.get('value');
               			
               		} else {
               			filter.value = this.alphanum.getDisplayedValue();
               		}
               		break;
               	case 'date':
               		filter.value = this.date.get('value');
               		break;
               	case 'time':
               		filter.value = this,time.get('value');
               		break;
               	case 'numeric':
               		filter.value = this.numeric.get('value');
               		break;
               	}
        		return filter;	
           }
           
          
        });
});
