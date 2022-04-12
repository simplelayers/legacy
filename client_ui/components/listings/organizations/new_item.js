define([ "dojo/_base/declare", "dojo/on", "dojo/topic", 'dojo/dom-class',
		"dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", 'dijit/form/DateTextBox',
		'dijit/Dialog', 'sl_components/sl_button/widget',
		'sl_components/selectors/plan_selector/widget',
		"dojo/text!./templates/new_item.tpl.html", ], function(declare, on,
		domClass, topic, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
		datebox, djitDialog, sl_button, plan_selector, template) {
	return declare('new_organization', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		// Using require.toUrl, we can get a path to our AuthorWidget's
		// space
		// and we want to have a default avatar, just in case

		// Our template - important!
		templateString : template,
		baseClass : "new_organization",
		roles : null,
		refId : null,
		inputs : null,
		newItem : null,
		lastItem:null,
		postCreate : function() {
			on(this.add_bttn, 'click', this.ShowDialog.bind(this));
			on(this.submit_item_bttn,'click',this.AddItem.bind(this));
			this.inputs = {
				'input' : [ 'input_org_name', 'input_account_name',
						'input_account_pw', 'input_email', 'input_refId' ],
				'sel' : [ 'sel_planId' ],
				'datebox' : [ 'datebox_starts', 'datebox_expires' ]
			};
			var input=null;
			
			for(var inputType in this.inputs) {
				switch(inputType) {
				case 'input':
					for( input in this.inputs[inputType]) {
						on(this[this.inputs[inputType][input]],'keyup',this.HandleChanges.bind(this));
					}
					break;
				case 'datebox':
					for( input in this.inputs[inputType]) {
						on(this[this.inputs[inputType][input]],'change',this.HandleChanges.bind(this));
					}
					break;
				case 'sel':
					for( input in this.inputs[inputType]) {
						on(this[this.inputs[inputType][input]],'plan_selector/selection',this.HandleChanges.bind(this));
					}
					break;
				}
			}
			
			
		},
		HandleChanges:function(event) {			
			if(this.newItem === null) return;
			this.newItem.org_name =this.input_org_name.value;
			this.newItem.account_name =this.input_account_name.value;
			this.newItem.account_pw =this.input_account_pw.value;
			this.newItem.email = this.input_email.value;
			this.newItem.refId = this.input_refId.value;
			this.newItem.planId = this.sel_planId.selectedItem;
			this.newItem.starts = ''+this.datebox_starts.toString();
			this.newItem.expires = ''+this.datebox_expires.toString();

			
		},		
		MakeNewItem : function(item) {
			
			if (item) {
				this.newItem = item;
				return;
			}
			this.newItem = {
				'org_name' : '',
				'account_name' : '',
				'account_pw' : '',
				'email' : '',
				'refId' : '',
				'planId'  : null,
				'starts' : null,
				'expires' : null
			};
		},
		SetDefaults : function(defaults) {
			this.MakeNewItem();
			if(defaults===null) {
				this.ShowDialog();
				return;
			} 
			this.refId = defaults.refId;
			this.MakeNewItem();
			this.newItem.refId = this.refId;
			this.newItem.org_name = defaults.org_name;
			this.newItem.account_name = defaults.org_name.replace(' ','_').toLowerCase();
			
			this.newItem.email  = defaults.owner_email;
			this.ShowDialog();
		},
		ShowDialog : function(event) {
			// this.ClearValidation();
			// this.newItem =
			// {username:'','realname':'','password':'','email':'','seat':null};
			// this.SetInputs(this.newItem);
			if(!this.newItem) this.MakeNewItem();
			
			this.SetInputs();
			this.dialog_neworg.show();
			this.refId = null;
		},
		ClearValidation : function() {
			for ( var inputType in inputs) {
				domClass.remove(inputs[inputType], 'invalid');
				/*
				 * switch(inputType) { case 'input': break; case 'sel': break;
				 * case 'datebox': break; }
				 */
			}
		},
		SetInputs : function() {
			if (this.newItem.refId) {
				this.input_refId.value = this.refId;
			}
			var offset = null;
			
			for ( var inputType in this.inputs) {
				
				for (offset in this.inputs[inputType]) {
					var inputName = this.inputs[inputType][offset];
					var propName = inputName.split('_');
					propName.shift();
					propName = propName.join('_');
			
			
					switch (inputType) {
					case 'input':
						this[inputName].value = this.newItem[propName];
						break;
					case 'sel':
						
						if (this.newItem[propName]) {
						
							this[inputName].SetSelection(this.newItem[propName]);
						}
						break;
					case 'datebox':
						if (this.newItem[propName])
							this[inputName].set('value', this.newItem[propName]);
						break;
					}
				}
			}
		},
		ValidateItem:function() {
			if(this.newItem.org_name.length < 3 ) return alert('Enter an Organization Name longer than three characters');
			if(this.newItem.account_name.length < 3 ) return alert('Enter an account name longer than three characters');
			if(this.newItem.account_pw=='') return alert('Password cannot be empty');
			if((this.newItem.email == '') || (this.newItem.email.indexOf('@') < 0 ) || (this.newItem.email.split('.').count < 2)) return alert('Please enter a valid email address');
			if(this.newItem.planId === null) return alert('A plan must be selected');
			if(this.newItem.starts === null || (''+this.newItem.starts == '')) return alert('Please select a start date');
			if(this.newItem.expires === null || (''+this.newItem.expires == '')) return alert('Please select an end data');
			return 'ok';
		},
		AddItem : function(event) {
			if(this.ValidateItem()=='ok') {
				on.emit(this.domNode,'item_added',{item:this.newItem,'cancelable':true,'bubbles':true});
				this.dialog_neworg.hide();
			} 
			
		}

	});
});
