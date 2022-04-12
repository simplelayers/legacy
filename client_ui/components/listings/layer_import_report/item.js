define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        'dijit/form/TextBox',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'sl_components/sl_button/widget',
        "dojo/text!./templates/item.tpl.html",
        "sl_modules/WAPI",
        "sl_modules/Pages",
        "sl_modules/sl_URL"
        
        ],
    function(declare,
    		on,
    		topic,
    		domClass,
    		domAttr,
    		textBox,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		sl_button,
    		template,
    		wapi,
    		pages,
    		sl_url
    		){
        return declare('listings/layer_import_report/item',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "layer_import_report_item listing_item",
        	editorGroup:null,
        	changeListener:null,
        	state:null,
        	layerName:'',
        	layerId:'',
        	layerInfo:null,
        	url:'',
        	constructor:function(args) {
        		this.item = args.item;
        		this.layerId = this.item.layerid;
        		this.url = sl_url.getServerPath()+'?do=layer.edit1&id='+this.layerId;
        		if(this.item.stats.status != 'problem') {
	        		this.layerName = this.item.stats.info.layer;
	        	
        		}
        	},
            postCreate:function(){
            
            	statusClass = 'status_'+this.item.stats.status;
            	domClass.add(this.domNode,statusClass);
            	if(this.item.stats.status=='ok') {
            		if(this.item.stats.info.layer_type=='raster') {
            			
            			domClass.remove(this.ok_view_nonvector,'hidden');
            			domClass.add(this.ok_view,'hidden');
            			this.infoDisplay.innerHTML = "<div class=\"raster_info\">"+this.item.stats.metadata+"</div";
            		} else {
            			domClass.remove(this.ok_view_nonvector,'hidden');
            			domClass.add(this.ok_view,'hidden');
	            		this.records_to_import.innerHTML = this.item.stats.import.records_to_import;
	            		this.num_attempted.innerHTML = this.item.stats.import.num_attempted;
	            		this.num_inserted.innerHTML = this.item.stats.import.numInserted;
	            		this.nullCount.innerHTML = this.item.stats.import.nullCount;
	            		this.invalidCount.innerHTML = this.item.stats.import.invalidCount;
            		}
            		
            	} else {
            		
            		this.error_message.innerHTML = this.item.stats.sl_message;
            		if(this.item.stats.error_info) { 
            			if(this.item.stats.error_info.ERROR) this.db_error.innerHTML = this.item.stats.error_info.ERROR.trim();
            			if(this.item.stats.error_info['LINE 1']) this.db_error_line.innerHTML = this.item.stats.error_info['LINE 1'].trim();
            			if( this.item.stats.error_info['problem_insert']) this.db_error_query.innerHTML = this.item.stats.error_info.problem_insert.trim();
            		}	
            	}
            }
        });
});
