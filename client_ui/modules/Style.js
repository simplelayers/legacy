// In demo/myModule.js (which means this code defines
// the "demo/myModule" module):
 
define([
    // The dojo/dom module is required by this module, so it goes
    // in this list of dependencies.
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/query",
    
], function(dom,domConstruct,query){
    // Once all modules in the dependency list have loaded, this
    // function is called to define the demo/myModule module.
    //
    // The dojo/dom module is passed as the first argument to this
    // function; additional modules in the dependency list would be
    // passed in as subsequent arguments.
 
    
    // This returned object becomes the defined value of this module
    return {
    	addStyleSheet: function(styleSheetURL) {
    		var linkQuery = "link='"+styleSheetURL+"'";
    		var links = query(linkQuery);
    		if(links.length == 0) {
    			var head = query('head').shift();
    			if(head){
    				var node = domConstruct.toDom('<link rel="stylesheet" href="'+styleSheetURL+'">');
    				
    				head.appendChild(node);
    			}
    		}
    	}
    };
});