define([ "dojo/_base/declare",
         "dojo/on", 
         "dojo/topic", 
         "dojo/dom-class",
         "dijit/_WidgetBase",
		"dijit/_TemplatedMixin", 
		"dijit/_WidgetsInTemplateMixin",
		'sl_components/sl_button/widget',
		'sl_modules/Pages',
		"dojo/text!./templates/new_item.tpl.html" ], function(
				declare, 
				on,
				topic, 
				domClass,
				_WidgetBase, 
				_TemplatedMixin, 
				_WidgetsInTemplateMixin,
				sl_button, 
				pages,
				template) {
	return declare('listings/employees/new_employee', [ _WidgetBase,
			_TemplatedMixin, _WidgetsInTemplateMixin ], {

		templateString : template,
		baseClass : "new_employee new_item",
		newItem:{},
		inputs:null,
		postCreate : function() {
			this.orgAccount.innerHTML = '@'+pages.GetPageArg('orgAccount');
						this.inputs = [this.new_email_input,this.new_displayname_input,this.new_pwd_input,this.new_fwd_input];
			on(this.add_bttn,'click', this.ShowDialog.bind(this));
			on(this.ok_button,'click',this.AddItem.bind(this));
			on(this.cancel_button,'click',this.CancelAdd.bind(this));
			on(this.new_email_input,'keyup',this.EmailChanged.bind(this));
			on(this.new_email_input,'blur',this.SyncFwdEmail.bind(this));			
			on(this.new_displayname_input,'keyup',this.DisplayNameChanged.bind(this));
			on(this.new_pwd_input,'keyup',this.PasswordChanged.bind(this));
			on(this.new_fwd_input,'keyup',this.FwdEmailChanged.bind(this));
			this.ClearValidation();
			
		},
		SyncFwdEmail:function() {
			if(this.new_fwd_input.value=='') this.new_fwd_input.value = this.new_email_input.value;
			this.FwdEmailChanged();
		},
		ClearValidation:function() {
			for( var input in this.inputs) {
				if(this.inputs[input]) {
					domClass.remove(this.inputs[input],'invalid');
				}
			}
			
			
		},
		ShowDialog:function(event) {
			this.ClearValidation();
			this.newItem =   {username:'','realname':'','password':'','email':'','seat':null};
			this.SetInputs(this.newItem);
			
			this.new_employee.show();
		},
		SetInputs:function(newItem) {
			if(newItem) {
				this.new_email_input.value = this.newItem.realname;
				this.new_displayname_input.value = this.newItem.realname;
				this.new_pwd_input.value = this.newItem.password;
				this.new_fwd_input.value = this.newItem.email;
				return;
			}
			for( var input in this.inputs) {
				this.inputs[input].value='';
			}
		},		
		AddItem : function(event) {
			this.ClearValidation();
			var isValid = true;
			
			this.newItem.username = this.newItem.username+'@'+pages.GetPageArg('orgAccount');
			/*if(!this.ValidateEmail(this.newItem.username)) {
				domClass.add(this.new_email_input,'invalid');
				isValid = false;
			}*/
			
			if(this.newItem.realname==''){ isValid=false;	domClass.add(this.new_displayname_input,'invalid'); }
			if(this.newItem.password=='') {isValid=false; 	domClass.add(this.new_pwd_input,'invalid') };
			if(!this.ValidateEmail(this.newItem.email)) {isValid=false; 	domClass.add(this.new_fwd_input,'invalid') };
			
			if(isValid) {
				on.emit(this.domNode,'new_item/add_item',{item:this.newItem,src:this});
				// We generally don't want to keep input or item info around any longer than we have to.
				this.SetInputs();
				this.newItem = null; 
				this.new_employee.hide();
			}
			
		},
		CancelAdd:function(event) {
			
			this.new_employee.hide();
			
		},
		ValidateEmail:function(email){
			var segs = email.split('@');
			if(segs.length == 1 ) return false;
			if(segs.length > 2) return false;
			if(segs[1].split('.').length < 2) return false;
			if(email.indexOf('..')!=-1) return false;
			return true;
		},
		
		EmailChanged:function(event) {
			this.newItem.username = this.new_email_input.value;
			domClass.add(this.new_email_input,'changed');
		},
		DisplayNameChanged:function(event) {
			this.newItem.realname = this.new_displayname_input.value;
			domClass.add(this.new_displayname_input,'changed');
		},
		PasswordChanged:function(event) {
			this.newItem.password = this.new_pwd_input.value;
			domClass.add(this.new_pwd_input,'changed');
		},
		FwdEmailChanged:function(event) {
			this.newItem.email = this.new_fwd_input.value;
			domClass.add(this.new_fwd_input,'changed');
		}
		
		
		

	});
});
