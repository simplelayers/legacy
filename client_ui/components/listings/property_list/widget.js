define(["dojo/_base/declare",
		 "dojo/on",
		 "dojo/dom-construct",
		 'dojo/dom-class',
		 "dijit/_WidgetBase",
		 "dijit/_TemplatedMixin",
		 "dojo/text!./templates/properties.tpl.html"],
		function (declare,
				  on,
				  domCon,
				  domClass,
				  _WidgetBase,
				  _TemplatedMixin,
				  template) {
		    return declare('listincgs/property_list', [_WidgetBase, _TemplatedMixin],
			{
			    data: null,
			    startHeading: null,
			    templateString: template,
                baseClass:'property_list',
                propertiesTemplate:'',
                itemTemplate:'',
                numProps:0,
                postCreate: function () {
			    	this.itemTemplate = this.item_tpl.innerHTML;
			    	this.propertiesTemplate = this.prop_tpl.innerHTML;
			    	domCon.empty(this.domNode);

			    },
			    SetData: function (propObject) {
			    	
			    	domCon.empty(this.domNode);
			    	this.numProps = 0;
			    	for(var key in propObject) {
			    		var propInfo = propObject[key];
			    		if(propInfo._hidden) continue;
			    		this.numProps ++;
			    		var item = this.itemTemplate+'';
			    		item = item.replace('#itemName#',key);
			    		var properties = '';
			    		var lastProp = '';
			    		for( var prop in propInfo) {
			    			lastProp = prop;
			    		}
			    		
			    		for( prop in propInfo) {
			    			if(prop.substr(0,1)=='_') continue;
			    			var propTemplate = this.propertiesTemplate +'';
			    			var propVal = propInfo[prop];
			    			
			    			propTemplate = propTemplate.replace('#propertyName#',prop);
			    			propTemplate = propTemplate.replace('#propertyValue#',propVal);
			    			propTemplate = (prop == lastProp) ? propTemplate.replace('#lastClass#','last_property') : propTemplate.replace('#lastClass#','');
			    			properties+=propTemplate;
			    		}
			    		
			    		item = item.replace('#itemProperties#',properties);
			    		
			    		domCon.place(domCon.toDom(item),this.domNode);
			    		
			    	}
			    	
			        this.data = propObject;
			        
			    }
			  });
		}
	);
