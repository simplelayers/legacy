define([
    // The dojo/dom module is required by this module, so it goes
    // in this list of dependencies.
    "dojo/dom",
    "dojo/_base/array",
    "dojo/_base/lang"
], function(dom,array,lang){
	return {
		
		LoadRoles:function() {
			wapi.exec('permissions/roles',params,handler);
		},
		RolesLoaded:function(results) {
			
		}
		
	};
});