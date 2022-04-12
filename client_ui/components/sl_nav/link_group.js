define(["dojo/_base/declare",
        "dojo/parser", 
        "dojo/on",
        "dojo/dom-construct",
        "dojo/dom-class",
        'dojo/dom-attr',
        "dojo/query",
        "dojo/dom-prop",
        "dijit/Toolbar", 
        "dijit/form/DropDownButton",
        "dijit/ColorPalette",
        "dijit/TooltipDialog",
        "dijit/form/TextBox", 
        "dijit/form/Button", 
        "dijit/form/ToggleButton", 
        "dijit/ToolbarSeparator",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'sl_components/sl_nav/link_item',
        'sl_modules/Pages',
        "dojo/text!./templates/link_group.tpl.html"
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
    		dropDownButton,
    		colorPalette,
    		tooltipDialog,
    		texstBox,
    		button,
    		toggleButton,
    		sep,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		link_item,
    		pages,
    		template){
        return declare('sl_components/sl_nav/link_group',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            baseClass : "link_group",
            templateString: template,
            num_rows:2,
            links:null,
            title:null,
            constructor:function(args) {
            	
            	this.title  = (args.titleArg) ? pages.GetPageArg(args.titleArg) : args.groupTitle;
            	this.links = args.links;
            	
            	
            },
            Refresh:function() {
            	domCon.empty(this.group_links);
            	links = [];
            	for( var i =0; i < this.links.length;i++) {
            		if(pages.MeetsRequirements(this.links[i])) {
            			links.push(this.links[i]);
            		}
            	}
            	if(links.length == 0) {
            		domClass.add(this.domNode.parentNode,'hidden');
            		return;
            	}
            	
            	var num_cols = Math.ceil(links.length / this.num_rows);
            	i=0;
            	for(var r=0; r < this.num_rows;r++) {
            		row = domCon.toDom('<tr ></tr>');
        			domCon.place(row,this.group_links);
        			for(var c=0; c < num_cols;c++) {
        				if(i < links.length) {
        					alink = new link_item(links[i]);
        					if(alink.valid) alink.placeAt(row);
        					i++;
        				} else {
        					domCon.place('<td>&nbsp;</td>',row);
        					
        				}
        			}        			
            	}

            	
            	
            	
            },
            postCreate:function(){
            	this.group_title.innerHTML = this.title;
            	this.Refresh();
            },
            MakeLink:function(linkData) {
            	
            },
            HandleLinkClick:function(event) {
           
            }
            
         });
});
