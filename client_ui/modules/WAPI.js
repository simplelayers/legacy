define([
    "dojo/dom",
    "dojo/dom-attr",
    "dojo/_base/lang",    
    "sl_modules/sl_URL",
   "dojo/_base/xhr"
             
], function(dom,domAttr,lang,sl_url,xhr,as){
	return {
		appName:'dmi',
		baseURL:sl_url.getAPIPath(),
		setApp:function(app) {
			this.appName = app;
		},
		exec:function(do_cmd,params,handler) {
			console.log(do_cmd);
			if(!as) as='json';
			params.format='json';
			if(do_cmd.substr(0,1)=='/') do_cmd = do_cmd.substr(1);
			if(do_cmd.substr(0,5)=='wapi/') do_cmd = do_cmd.substr(5);
			var doURL = this.baseURL+do_cmd+'/';
                        
			params['application'] = this.appName;
			xhr.post({
				url: doURL,
				content: params,
				handleAs:as,
				load:function(result) {
					if(!(handler===undefined)) { 
						handler(result);
					}
				}
			});
			//handleAs:as,
		}
	};
});