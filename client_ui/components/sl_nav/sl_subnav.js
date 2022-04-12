define(["dojo/_base/declare",
        "dojo/parser", 
        "dojo/on",
        "dojo/dom-construct",
        "dojo/dom-class",
        "dojo/dom-attr",
        "dojo/query",
        "dojo/dom-prop",
        "dijit/Toolbar", 
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'sl_components/sl_nav/link_group',
        'sl_modules/Pages',
        'sl_modules/sl_URL',
        "dojo/text!./templates/sl_subnav.tpl.html"
        ],
    function(declare,
    		parser,
    		on,
    		domCon,
    		domClass,
    		domAttr,
    		query,
    		domProp,
    		toolbar,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		link_group,
    		pages,
    		sl_url,
    		template  
    		) {
        return declare('sl_components/sl_subnav',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "sl_subnav",
            templateString: template,
            data:null,
            subNavName:null,
            postCreate:function() {
            	domAttr.set(this.section_icon,'src',
            			sl_url.getEmptyImgURL());
            	
            	
            },
            SetData:function(data,name) {
            	this.data = data;
            	this.subNavName = name;
            	this.Refresh();
            },
            Refresh:function() {
            	
            	var iconClass = (this.subNavName == 'org') ? 'organization' : this.subNavName;
            	domClass.remove(this.section_icon);
            	domClass.add(this.section_icon,iconClass);
            	domClass.add(this.section_icon,'section_icon');
            	
            	domCon.empty(this.subnav_toolbar.domNode);
            	
            	for( var group_i in this.data) {
            		
            		var val = this.data[+group_i];
            		var match = pages.MeetsRequirements(val.requirements);
            		
            		if(match === true) {
            			try {
            			group = new link_group(val);
            			this.subnav_toolbar.addChild(group);
            			} catch(e) {
            				console.log(e);
            			}
            			
            		}            		            		
            	}
            	
            }
           
         });
});
