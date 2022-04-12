define(["dojo/_base/declare",
        "dojo/on",
        "dojo/topic",
        'dojo/dom-class',
        'dojo/dom-attr',
        'dojo/dom-construct',
        'dojo/query',
        "dijit/_WidgetBase", 
        "dijit/_TemplatedMixin", 
        "dijit/_WidgetsInTemplateMixin",
        "dojo/text!./templates/item.tpl.html",        
        "sl_modules/WAPI"],
    function(declare,
    		on,
    		topic,
    		domClass,
    		domAttr,
    		domCon,
    		query,
    		_WidgetBase, 
    		_TemplatedMixin,
    		_WidgetsInTemplateMixin,
    		template,
    		wapi
    		){
        return declare('listings/employees/employee',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
            // Some default values for our author
            // These typically map to whatever you're passing to the constructor
            // Using require.toUrl, we can get a path to our AuthorWidget's space
            // and we want to have a default avatar, just in case
        	item:null,
        	templateString: template,
        	baseClass: "organization employees",
        	isDeleted:false,
        	isChanged:false,
        	lookupData:null,
        	seatStats:null,
        	signals:null,
        	inputs:null,
        	constructor:function(args) {
        		this.item = args.item;
        		this.lookupData = args.lookupObj;        		        		
        	},
            postCreate:function(){
            	this.ResetSeatSelector();
            	this.inputs = query('input',this.domNode);
            	this.inputs.push(this.sel_seat);
            	var memberInfo = this.GetMemberInfo();
            	if(memberInfo) {
            		this.input_displayname.value=memberInfo.realname;
                	this.input_username.value = memberInfo.username.split('@').shift();
                	this.input_fwdEmail.value = memberInfo.email;
            	} else {
            		this.HandleDelete();
            	}
            	
            	this.signals = [];
            	this.signals.push(on(this.input_displayname,'keyup',this.HandleEmployeeChange.bind(this)));
            	this.signals.push(on(this.input_username,'keyup',this.HandleEmployeeChange.bind(this)));
            	this.signals.push(on(this.input_password,'keyup',this.HandleEmployeeChange.bind(this)));
            	this.signals.push(on(this.input_fwdEmail,'keyup',this.HandleEmployeeChange.bind(this)));
            	this.signals.push(on(this.sel_seat,'change',this.HandleSeatChange.bind(this)));
            	this.signals.push(on(this.del_item_bttn,'click',this.HandleDelete.bind(this)));
            	this.signals.push(on(this.email_employee,'click', this.HandleEmail.bind(this)));
            },
            ResetSeatSelector:function() {
            	if(this._beingDestroyed)return;
            	if(!this.lookupData.data.seats_lookup[this.item.data.seatId]) {
            		domClass.add(this.sel_seat,'hidden');
            		this.selector_cell.innerHTML = 'Organization Owner';
            		return;
            	}
            	domCon.empty(this.sel_seat);
            	
            	for(var seatId in this.lookupData.data.seats_lookup ){
            		seatName = this.lookupData.data.seats_lookup[seatId];
            		
            		if(this.lookupData.stats != null &&  (seatName != 'Unassigned')) {
            			if(this.lookupData.stats[seatName].limit != '') {
		        			if(this.lookupData.stats[seatName].count >= this.lookupData.stats[seatName].limit) {
		        				
		        					if(this.item.data.seatId!=seatId) continue;
		        				
		        			}
            			}
            		}
            		atts ={value:seatId,innerHTML:seatName}
            		if(this.item.data.seatId == seatId) atts.selected=true;
            		
            		node = domCon.create('option',atts,this.sel_seat);
            	}
            	
            },
            GetMemberInfo:function() {
            	
            	for(var i in this.lookupData.data.members) {
            		member = this.lookupData.data.members[i];
            		if(member.id == this.item.data.userId) return member;
            	}
            },   
            UpdateItem:function(updateInfo) {
            	
            	this.lookupData.stats = updateInfo.stats;
            	this.ResetSeatSelector();
            	
            },
            HandleSeatChange:function(event) {
            	
            	this.item.isChanged = true;
            	if(this._beingDestroyed)return;
            	this.item.data.seatId = this.sel_seat.value;
            	on.emit(this.domNode, 'seat_change',{item:this.item,bubbles:true,cancelable:false}  );
            	this.UpdateUI();
            },
            HandleEmployeeChange:function(event) {
            	memberInfo = this.GetMemberInfo();
            	if(memberInfo) {
            		memberInfo.password = this.input_password.value;
            		memberInfo.realname = this.input_displayname.value;
            		memberInfo.username = this.input_username.value;
            		memberInfo.email = this.input_fwdEmail.value;
            		memberInfo.isChanged = true;
            	}
            	this.UpdateUI();
            },
            Unsubscribe:function() {
            	for(var i in this.signals) {
            		this.signals[i].remove();
            	}
            },
            HandleEmail:function(event) {
            	window.location.href='mailto:'+this.GetMemberInfo().email;
            },
            HandleDelete:function(event) {
            	memberInfo = this.GetMemberInfo();
            	
            	if(this.item.isDeleted) {
            		delete( this.item.isDeleted);
            		if(memberInfo)	delete(memberInfo.isDeleted);
            	} else {
            		this.item.isDeleted = true;
            		if(memberInfo) memberInfo.isDeleted = false;
            	}
            	
            	this.UpdateUI();
            },
            HandleValueChange:function(event) {
            	
            },
            UpdateUI:function() {
            	
            	memberInfo = this.GetMemberInfo();
            	memberInfoChanged = false;
            	if(this.item.isChanged | memberInfoChanged) {
            		domClass.add(this.domNode,'changed');
            		
            	} else {
            		domClass.remove(this.domNode,'changed');
            	}
            	
            	if(this.memberInfo) {
            		memberInfoChanged = this.memberInfo.isChanged;
            	}
            	
            	var i=null;
            	
    			if (this.item.isDeleted) {
    				domClass.add(this.domNode,'deleted');
            		//domClass.add(this.item_name, 'deleted');
    				this.del_item_bttn.setColor('red');
    				for(i in this.inputs) {
    					domAttr.set(this.inputs[i], 'disabled', true);
    				}
    				//
    				
    			} else {
    				domClass.remove(this.domNode,'deleted');
    				//domClass.remove(this.item_name, 'deleted');
    				var color = (this.item.isChanged | memberInfoChanged) ? 'blue' : 'normal';
    				
    				this.del_item_bttn.setColor(color);
    				//domAttr.set(this.domNode, 'disabled', false);
    				for(i in this.inputs) {
    					domAttr.set(this.inputs[i], 'disabled', false);
    				}
    				
    			}
            }
        });
});
