define(["dojo/_base/declare",         
        "sl_modules/WAPI",
        "sl_modules/sl_URL"             
], function(declare,sl_api,sl_url){
	return{
		//data:null,
		layers:null,
		map:null,
		views:null,
		getLayerViews:function(params,handler) {
			sl_api.exec('wapi/layers/views/action:list',params,handler);
		},
		getLayerView:function(viewURL,params,handler) {
			for ( var key in params ) {
				var val = params[key];
				viewURL = viewURL.replace('['+key+']',val);
			}
			var segs = viewURL.split('[');
			var url = segs.shift();
			
			for( var s in segs) {
				var vals = segs[s].split(']');	
				var key = vals.shift();
				
				if(!params.hasOwnProperty(key)) {
					//url+=key; 
					continue;
				}
				url+=params[key];
				
				
			}
			
			sl_api.exec(url,{},(function(response) {
				this.views = response;
				handler(this.views);
			}).bind(this));
		}
	};
});