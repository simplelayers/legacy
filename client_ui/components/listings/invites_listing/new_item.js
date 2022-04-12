define([ "dojo/_base/declare", "dojo/on", 'dojo/dom-class',
		"dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", 'dijit/form/TextBox','dijit/form/Textarea',
		'dijit/Dialog', 'sl_components/sl_button/widget',
		'sl_components/selectors/plan_selector/widget',
		"dojo/text!./templates/new_item.tpl.html" ], function(declare, on,
		domClass, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
		textbox, textarea, djitDialog, sl_button, plan_selector, template) {
	return declare('new_item', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		// Using require.toUrl, we can get a path to our AuthorWidget's
		// space
		// and we want to have a default avatar, just in case

		// Our template - important!
		templateString : template,
		baseClass : "new_invite",
		roles : null,
		refId : null,
		inputs : null,
		newItem : null,
		postCreate : function() {
			on(this.add_bttn, 'click', this.ShowDialog.bind(this));
			on(this.submit_item_bttn,'click',this.AddItem.bind(this));
			
			this.inputs = {
				'textbox' : [ 'textbox_name', 'textbox_organization',
				              'textbox_title','textbox_email'],
				'textarea' : [ 'textarea_reason' ]
			};
			var input=null;
			for(var inputType in this.inputs) {
				switch(inputType) {
				case 'textbox':
				case 'textarea':
					for( input in this.inputs[inputType]) {
						on(this[this.inputs[inputType][input]],'keyup',this.HandleChanges.bind(this));
					}
					break;
				}
			}
			
			
		},
		HandleChanges:function(event) {
			for( var inputType in this.inputs) {
				for( input in this.inputs[inputType]) {
					
					itemProp = this.inputs[inputType][input].split('_');
					itemProp.shift();
					itemProp = itemProp.join('_');
					
					if(this[this.inputs[inputType][input]]) {
						this.newItem[itemProp] = this[this.inputs[inputType][input]].get('value');
					}
				}
				
			}
			
			/*this.newItem.org_name =this.input_org_name.value;
			this.newItem.account_name =this.input_account_name.value;
			this.newItem.account_pw =this.input_account_pw.value;
			this.newItem.email = this.input_email.value;
			this.newItem.refId = this.input_refId.value;
			this.newItem.planId = this.sel_planId.value();
			this.newItem.starts = ''+this.datebox_starts;
			this.newItem.expires = ''+this.datebox_expires;
			*/
		},		
		MakeNewItem : function(item) {
			if (item) {
				this.newItem = item;
				return;
			}
			this.newItem = {
				'name' : '',
				'organization' : '',
				'title' : '',
				'email' : '',
				'reason' : ""				
			};
		},
		ShowDialog : function(event) {
			this.ClearValidation();
			this.MakeNewItem();
			this.SetInputs();
			this.dialog_invite.show();			
		},
		
		ClearValidation : function() {
			for ( var inputType in this.inputs) {
				domClass.remove(this.inputs[inputType],'invalid');				
			}
		},
		SetInputs : function() {
			var i =0;
			for ( var inputType in this.inputs) {
				for (i=0;i<this.inputs[inputType].length;i++) {
					
					var inputName = this.inputs[inputType][i];
					var propName = inputName.split('_');
					propName.shift();
					propName = propName.join('_');
					switch (inputType) {
						case 'textarea':
						case 'textbox':
							this[inputName].set('value',this.newItem[propName]);
							
							break;
					}
				}
			}
		},
		ValidateItem:function() {
			/*
			if(this.newItem.org_name.length < 3 ) return alert('Enter an Organization Name longer than three characters');
			if(this.newItem.account_name.length < 3 ) return alert('Enter an account name longer than three characters');
			if(this.newItem.account_pw=='') return alert('Password cannot be empty');
			if(this.newItem.email == '') return alert('Email cannot be empty');
			if(this.newItem.planId === null) return alert('A plan must be selected');
			if(this.new_item.starts === null || (''+this.new_item.starts == '')) return alert('Please select a start date');
			if(this.new_item.ends === null || (''+this.new_item.ends == '')) return alert('Please select an end data');
			*/
		},
		AddItem : function() {
			on.emit(this.domNode,'item_added',{'cancelable':true,'bubbles':true,'item':this.newItem});
			this.dialog_invite.hide();
		}

	});
});
