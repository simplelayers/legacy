define([ "dojo/_base/declare", "dojo/dom", "dojo/dom-attr", "dojo/_base/lang",
		"sl_modules/sl_URL"

], function(declare, dom, domAttr, lang, sl_url) {
	return {
		NONE : 0,
		VIEW : 1,
		EDIT : 2,
		COPY : 4,
		CREATE : 8,
		SAVE : 16,
		DELETE : 32,
		FULL : 63,
		permissions : null,
		SetPermissions : function(perms) {
			this.permissions = perms;		
		},
		HasPermission : function(permName, permValue) {
			if(permName.substr(0,1)!=':') permName = ':'+permName;
			if(permName.substr(-1,1)!=':') permName+=':';
			
			return ((this.permissions[permName] & permValue) > 0);
		},
		StrToPermValue:function(str) {
			str = str.toUpperCase();
			if(!this.hasOwnProperty(str)) return null;
			return this[str];
		}
	};
	
	//if(!Document.prototype.sl_permissions) Document.prototype.sl_permissions = new sl.Permissions();

});