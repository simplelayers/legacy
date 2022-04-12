define([ "dojo/_base/declare", "dojo/on", "dojo/topic", 'dojo/dom-class',
		"dijit/_WidgetBase", "dijit/_TemplatedMixin",
		"dijit/_WidgetsInTemplateMixin", 'dijit/form/DateTextBox',
		'dijit/Dialog', 'sl_components/sl_button/widget',
		"dojo/text!./templates/new_item.tpl.html", ], function(declare, on,
		domClass, topic, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
		datebox, djitDialog, sl_button,  template) {
	return declare('map_layers_header', [ _WidgetBase, _TemplatedMixin,
			_WidgetsInTemplateMixin ], {
		// Some default values for our author
		// These typically map to whatever you're passing to the constructor
		// Using require.toUrl, we can get a path to our AuthorWidget's
		// space
		// and we want to have a default avatar, just in case

		// Our template - important!
		templateString : template,
		baseClass : "map_layers_header",
		roles : null,
		refId : null,
		inputs : null,
		newItem : null,
		postCreate : function() {
			
			on(this.back_button.domNode,'sl_toggle_button/value_change',this.GoBack.bind(this))
			//on(this.add_bttn, 'click', this.ShowDialog.bind(this));		
			
		},
		GoBack:function(event) {
			alert('go back');
			this.emit('show_parent',{'cancelable':true,'bubbles':true});
		},
		HandleChanges:function(event) {			
			
			
		},		
		SetDefaults : function(defaults) {
		},
		ShowDialog : function(event) {
			// this.ClearValidation();
			// this.newItem =
			// {username:'','realname':'','password':'','email':'','seat':null};
			// this.SetInputs(this.newItem);
			//if(!this.newItem) this.MakeNewItem();
			
			//this.SetInputs();
			this.dialog_newlayers.show();
			this.refId = null;
		},
		SetInputs : function() {
		},
		AddItem : function(event) {
			on.emit(this.domNode,'item_added',{item:this.newItem,'cancelable':true,'bubbles':true});
			this.dialog_neworg.hide();			 
		}

	});
});
