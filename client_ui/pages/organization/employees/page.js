define(["dojo/_base/declare",
        "dojo/on",
        "dojo/dom-attr",
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        'dojo/dom-class',
        'dojo/dom-style',
        'sl_components/sl_button/widget',
        'sl_components/listings/property_list/widget',
        'sl_components/listings/employees/widget',
        'sl_modules/WAPI',
        'sl_modules/Pages',
        "dojo/text!./templates/employees.tpl.html"],
    function(declare,
    		on,
    		domAttr,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		domClass,
    		domStyle,
    		sl_button,
    		sl_propList,
    		sl_employees,
    		wapi,
    		pages,
    		template){
        return declare('sl_pages/organization/employees',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
        	baseClass:'organization_employees',
        	templateString: template,
        	permissions:null,
        	imporetViewed:false,
        	baseURL:'',
        	seatStats:null,
        	licenseLookup:null,
        	seatAssignments:null,
        	seatLookup:null,
        	orgId:null,
        	constructor:function() {
        	   	pages.SetPageArg('pageSubnav','org');
        	   	pages.SetPageArg('pageTitle',pages.GetPageArg('orgName')+' - Employees');
        	   	
        	},
        	postCreate:function(){
        	   	on(this.employees.domNode,'listings/employees/model_ready',this.HandleEmployeeModel.bind(this));
        	   	on(this.employees.domNode,'seat_change',this.HandleSeatChange.bind(this));
        	   	if(pages.GetPageArg('orgId')) this.orgId = pages.GetPageArg('orgId');
        	   	this.employees.LoadModel(pages.pageArgs);
        	},
            HandleEmployeeModel:function(event) {
            	orgId = this.employees.orgId;
            	seatAssignments = this.employees.seatAssignments;
            	seatLookup = this.employees.seatLookup;
            	this.seatLookup = seatLookup;
            	this.seatAssignments = seatAssignments;
            	this.licenseLookup = this.employees.licenseLookup;
            	
            	this.FillSeatData(seatLookup,seatAssignments);            	
        	},
            FillSeatData:function(seatLookup, assignments) {
            	this.seatStats = {};
            	
            	for( var id in seatLookup) {
            		seat = seatLookup[id];
            		
            		if(seat == 'Unassigned') continue;
            		if(!this.seatStats[seat]) this.seatStats[seat] = {count:0,limit:this.GetPlanLimit(id)};
            		for(var i in assignments) {
            			var assignment = assignments[i];
            			
            			if(assignment.data.seatId==id) {

            				this.seatStats[seat].count+=1;
            			}

            		}           		
            	}
            	
            	for(var i in this.seatStats) {
            		var stat = this.seatStats[i];
            		stat._hidden = (stat.limit === '0');
            	}
            	this.seats_stats.SetData(this.seatStats);
            	if(this.seats_stats.numProps == 0) {
            		domClass.add(this.seat_stats_heading,'hidden');
            	}
            	this.employees.Refresh({stats:this.seatStats});
            },
        	GetPlanLimit:function(seatId) {
        		if(!this.licenseLookup.seats) return 0;
        		if(!this.licenseLookup.seats[seatId]) return 0;
        		return this.licenseLookup.seats[seatId].count;
        		
        	},
            HandleSeatChange:function(event) {
            	this.FillSeatData(this.seatLookup,this.seatAssignments);
            	 
            }
        });
});