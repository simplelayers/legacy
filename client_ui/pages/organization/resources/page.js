define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'dojo/parser',
        'dojo/dom-construct',
        'dojo/topic',
        'dojo/json',
        'sl_modules/WAPI',
        'sl_modules/sl_URL',
        'sl_modules/Pages',
        'sl_components/selectors/media_selector/widget',
        'sl_components/sl_button/widget',
        'sl_components/listings/org_media_listing/widget',        
        "dojo/text!./templates/work_area.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		parser,
    		domCon,
    		topic,
    		json,
    		wapi,
    		sl_url,
    		pages,
    		media_selector,
    		sl_button,
    		org_media_listing,
    		template){
        return declare('sl_pages/organization/media',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'org_media',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	currentView:null,
        	roles:null,
        	orgId:null,
        	constructor:function() {
        	   	pages.SetPageArg('pageSubnav','org');
        	   	pages.SetPageArg('pageTitle',pages.GetPageArg('orgName')+' - Resources');
        	   	
        	},
        	postCreate:function(){
        		var action = sl_url.getServerPath()+'wapi/organization/organizations/action:upsert/target:media/return_to:organization__resources';
        		
        		on(this.sel_media_options,'change',this.UpdateForm.bind(this));
        		this.link_label.innerHTML = 'Help Link: ';
        		this.file_label.innerHTML = 'Logo File: ';
        		if(pages.pageArgs) {
        			if(pages.GetPageArg('orgId')) {
        				this.orgId = pages.GetPageArg('orgId');
        				action+='/orgId:'+this.orgId;
        			}
        			
        			if(!(pages.GetPageArg('orgActor') == 'org_owner') && !(pages.GetPageArg('pageActor') == 'admin')) {
        				domClass.add(this.media_form , 'hidden');
        			}
        		}
        		domAttr.set(this.form,'action',action);
        		
        	},
        	UpdateForm:function() {
        		media_type = this.sel_media_options.GetSelection();
        		if(media_type=='help_link') {
        			domClass.add(this.file_row,'hidden');
        			domClass.remove(this.link_row,'hidden');
        			
        		} else {
        			domClass.add(this.link_row,'hidden');
        			domClass.remove(this.file_row,'hidden');        			
        		}
        		var mediaInfo = this.sel_media_options.GetItemByKey(media_type);
        		this.file_label.innerHTML = mediaInfo.label;
        		domAttr.set(this.media_name,'value', media_type);
        	}
                  	
        });
});
