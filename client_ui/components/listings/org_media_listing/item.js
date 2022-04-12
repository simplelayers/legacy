define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        'dojo/dom-construct',
        'dojo/mouse',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/item.tpl.html",
        "sl_modules/Pages",
        "sl_modules/WAPI"
        ],
    function(declare,
    		on,
    		topic,
    		domClass,
    		domAttr,
    		domCon,
    		dojoMouse,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		template,
    		pages,
    		wapi
    		){
        return declare('org_media_item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	itemName:null,
        	itemVal:null,
        	itemType:'image',
        	templateString: template,
        	baseClass: "org_media_item",
        	isDeleted:false,
        	isChanged:false,
        	constructor:function(args) {
        		console.log(args.item);
        		for(var key in args.item) {
        			this.itemName = key;
        			this.itemVal = args.item[key].url;
        			this.itemType = args.item[key].media_type;
        			parts = this.itemName.split('_');
        			
        			
        			for(var part in parts) {
        				firstChar = parts[part].substr(0,1);
        				
        				firstChar = firstChar.toUpperCase();
        				
        				parts[part] = firstChar+parts[part].substr(1);
        				
        			}
        			this.itemName = parts.join(' ');
        		}
        			
        	},
            postCreate:function(){
            	console.log(this.itemType);
            	switch( this.itemType) {
            		case 'org_media_image':
            			domCon.place("<img src='"+this.itemVal+"' title='Custom help link to be opened within the SimpleLayers Viewer.'></img>",this.org_media_tile,'first');
            			break;
            		case 'org_media_link':
            		case 'org_media_file':
            			domCon.place("<a title='Test the link to submitted media, content will open in a new tab or window' href='"+this.itemVal+"' target='_blank'>Test Link</a>", this.org_media_tile,'first');
            			break;            		
            	}
            	
            	
            	this.org_media_tile_label_txt.innerHTML=this.itemName;
            	if(pages.GetPageArg('orgActor')=='org_owner') {
	            	on(this.domNode,dojoMouse.enter,this.ItemEntered.bind(this));
	            	on(this.domNode,dojoMouse.leave,this.ItemExited.bind(this));
	            	on(this.org_media_tile_delete_btn,'click',this.DeleteItem.bind(this));
        		}
            	
            },
            ItemEntered:function(event) {
            	domClass.remove(this.org_media_tile_delete,'hidden');
            },
            ItemExited:function(event) {
            	domClass.add(this.org_media_tile_delete,'hidden');
            },
            DeleteItem : function(event) {
            	if(window.confirm('Deleting this item cannot be undone, do you wish to continue?') ) {
            		topic.publish('liistngs/org_media_listing/item_deleted/'+this.id,{"itemName":this.itemName,"itemValue":this.itemVal});
            	}
    		},
            UpdateUI:function() {
            
            }
        });
});
