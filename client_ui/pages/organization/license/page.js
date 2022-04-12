define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "sl_components/listings/plan_viewer/widget",
        "sl_components/selectors/plan_selector/widget",
        "sl_components/scrollable_listing/footer/widget",
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
        'sl_modules/Pages',
        'dijit/form/DateTextBox',
        "dojo/text!./templates/work_area.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		plan_viewer_listing,
    		plan_selector,
    		footer,
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
    		pages,
    		datetextbox,
    		template){
        return declare('organization.license',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'org_license',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	currentView:null,
        	editorGroup:null,
        	license:null,
        	planChanged:false,
        	hasLoaded:false,
        	constructor:function() {
        	   	pages.SetPageArg('pageSubnav','org');
        	   	pages.SetPageArg('pageTitle',pages.GetPageArg('orgName')+' - License');        	   	
        	},
        	postCreate:function(){
        		
        		
        		
        		if(pages.GetPageArg('pageActor') != 'admin') {
        			this.plan_viewer.SetState('view');
        		} else {
        			this.plan_viewer.SetState('edit');
        		}
        		
        		this.Refresh();
        		
        		// this.editorGroup = domAttr.get(this.domNode,'data-editor');
        		
        	},
        	Refresh:function() {
        		domClass.add(this.domNode,'hidden');
        		
        		params  = {'action':'get'};
        		orgId = pages.GetPageArg('orgId');
        		if(orgId) {
        			params.orgId = orgId;
        		}
        		this.Clear();
        		if(this.planChanged) params['new_plan'] = this.sel_plans.value();

        		wapi.exec('organization/license',params,this.LicenseLoaded.bind(this));
        		this.planChanged = false;
        	},
        	HandlePlanChange:function() {
        		if(this.license == null) return;
        		if(this.sel_plans.value() == 'default') return;
        		this.planChanged = (this.license.data.planId != this.sel_plans.value());
        		if(this.planChanged) this.Refresh();
        		
        	},
        	LicenseLoaded:function(result) {
        		this.license = result.results;
        		
        		domClass.remove(this.domNode,'hidden');

        		if(this.license.data.startdate) {
        			startDate = this.license.data.startdate.split("T")[0].split('-');
        	    	startDate = new Date(startDate[0],startDate[1]-1,startDate[2]);
        	    	this.widget_startdate.set('value',startDate );
        		}
            	
        		if(this.license.data.expires) {
        			expires = this.license.data.expires.split("T")[0].split('-');
        			expires = new Date(expires[0],expires[1]-1,expires[2]);
        			this.widget_expires.set('value',new Date(expires) );
        		}
        		
        		
        		this.sel_plans.SetSelection(this.license.data.planId);
        		domClass.remove(this.selector_pane,'hidden');
        		
        		if(this.license.data.planId != null) {
        			
        			var comparePlan = (this.license.data.plan.seats) ? this.license.data.plan.seats : this.license.data.seats;//.plan.data.seats;
        			
        			
        			$data = {'plan':this.license.data.seats,'comparePlan':comparePlan };
        			this.plan_viewer.EditPlan($data);
        		}
        		
        		if(!this.hasLoaded) {
        			on(this.footer,'scrollable_listing/refresh', this.Refresh.bind(this));
            		on(this.footer,'scrollable_listing/update',  this.SaveLicense.bind(this));
            		on(this.sel_plans,'plan_selector/selection', this.HandlePlanChange.bind(this));
        		}
        		this.hasLoaded = true;
        	},
        	ToDate:function(str) {
        		
        	},
        	
        	SaveLicense:function(event) {
        		this.license.data.isChanged = true;
        		
        		if(this.license.data.seats.isChanged) {
        			delete(this.license.data.seats.isChanged);
        		}
        		this.license.data.startdate = (this.widget_startdate.value == null) ? null : this.widget_startdate.value.toJSON();
				this.license.data.expires = (this.widget_expires.value == null) ? null : this.widget_expires.value.toJSON();
				

            	var license = json.stringify({'json':this.license});
            	
        		params = {action:'save','changeset':[license]};
        		
        		wapi.exec('organization/license',params,this.ChangesSaved.bind(this));
        		
        	},
        	Clear:function() {
        		this.widget_startdate.set('value',  null);
        		this.widget_expires.set('value',null);
        		if(!this.hasLoaded)		this.plan_viewer.Clear();
        	},
        	
        	ChangesSaved:function(event) {
        		this.Refresh();
        	}
           

        });
});
