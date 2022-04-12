var dojoConfig = { 
baseUrl : "src", 
      packages : [ { 

             name : "dojo", 
             location : "dojo" 
         }, { 
             name : "dijit", 

             location : "dijit" 
         }, { 

             name : "dojox", 
             location : "dojox" 
         }, { 

             name : "client_ui", 
             location : "$client_ui" 
         }, {
        	 name: "sl_app",
        	 location: "$slAppURL"
         }], 
         parseOnLoad : true, 
         async : true 

}; 

