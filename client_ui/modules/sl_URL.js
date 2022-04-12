// In demo/myModule.js (which means this code defines
// the "demo/myModule" module):
 
define([
    // The dojo/dom module is required by this module, so it goes
    // in this list of dependencies.
    "dojo/dom",
    "dojo/_base/array",
    "dojo/_base/lang"
], function(dom,array,lang){
    // Once all modules in the dependency list have loaded, this
    // function is called to define the demo/myModule module.
    //
    // The dojo/dom module is passed as the first argument to this
    // function; additional modules in the dependency list would be
    // passed in as subsequent arguments.
 
    
    // This returned object becomes the defined value of this module
    return {
   
    	getServerPath:function() {
                
    		var trimSandbox = false;
    		if(arguments.length>0) {
    			var arg = arguments[0];
    			
    			if(arg=='trimSandbox') trimSandbox = true;
    		}
    		var url = document.location.href;
    		var hasSandbox = url.indexOf('~') > 0;
    		var pathSegs = url.split('/');
    		var baseURL = pathSegs.shift();
    		pathSegs.shift();
    		baseURL+='//';
    		var numSegs = (hasSandbox && !trimSandbox)  ? 3 : 1;
    		for(var i=0; i < numSegs ; i++)
    		{
    			baseURL+=pathSegs[i]+'/';
    		}
    		return baseURL;    		    	
    	},
    	getEmptyImgURL:function() {
    		return this.getServerPath()+'media/images/empty.png';    		
    	},    
    	getLogoURL:function() {
    		return this.getServerPath()+'logo.php';    		
    	},    
    	
    	getAPIPath:function() {
    		baseURL = this.getServerPath();
    		return baseURL+'wapi/';
    		
    	},
    	getURLParams: function(url) {
    		 var query = "";
    		 if( url.indexOf('?')>=0) {
    			var info = url.split('?');
    			base = info.shift();
    			query = info.join('?');
    		}
    		 
    		 var paramData = query.split('&');
    		 params = [];
    		 
    		 array.forEach(paramData,function(item){
    			 if(item=="") return;;	
    			 	var parts = item.split('=');
    			 	var key = parts[0];
    			 	var item = {};
    			 	if(parts.length > 1) {
    			 		item.paramName=key;
    			 		item.paramValue= lang.trim(decodeURIComponent(parts[1]));
    			 	} else {
    			 		item.paramName=key;
    			 		item.paramValue= true;    			 		
    			 	}    			    
    			 	this.push(item);
    			  }, params);
    		 
    		 return params;    		 
    	},
    	getURLPathParams: function(url){ 
    		if(url.substr(-1)=='/') url = url.substr(0,url.length-1);
    		var baseURL = this.getServerPath();
    		if(url.indexOf(baseURL)===0) {
    			url = url.substr(baseURL.length);
    		}
    		
    		var paths = url.split('/');
    		paths.shift();
    		paths.shift();
    		var params = {};
    		for ( var path in paths) {
    			var keyval=paths[path].split(':');
    			var key =keyval[0];
    			if(keyval.length ==0) {
    				params[key] = true;
    			} else {
    				keyval.shift();
    				params[key] = keyval.join(':');
    			}
    		}
    		return params;
    		
    	},
    	getURLParamObj:function(url) {
    		 var query = "";
    		 if( url.indexOf('?')>=0) {
    			var info = url.split('?');
    			base = info.shift();
    			query = info.join('?');
    		}
    		 
    		 var paramData = query.split('&');
    		 params = [];
    		 
    		 array.forEach(paramData,function(item){
    			 if(item=="") return;;	
    			 	var parts = item.split('=')
    			 	var key = parts[0].toLowerCase();
    			 	
    			 	if(parts.length > 1) {
    			 		params[key] =lang.trim(decodeURIComponent(parts[1]));
        			 } else {
        				params[key] = true;                			    			 		
    			 	}    			        			 	
    			  }, params);
    		 
    		 return params;    	
    	},
    	getURLBase: function(url) {
    		var info = url.split('?');
			base = info.shift();
			return base;
		
    	},
    	urlFromParamItems:function(base , params) {
    		if(base.indexOf('?')<0) base+='?';
    		var url = base;
    		for(var i in params ) {
    			var param = params[i];
    			url+='&'+i+'='+encodeURIComponent(param);
    		}
    		return url;
    	},
    	urlFromParamObj:function(baseURL , params) {
    		if(baseURL.indexOf('?')<0) base+='?';
    		var url = baseURL;
    		for( var key in params) {
    			url+='&'+key+'='+encodeURIComponent(params[key]);
    		}
    		return url;
    	}
    	
    	
    	
    };
});
