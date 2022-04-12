define([
    "dojo/dom",
    "dojo/dom-attr",
    "dojo/_base/lang",    
    "dojo/request",
    "sl_modules/sl_URL"
], function(dom,domAttr,lang,request,sl_url){
	
	return {
		getCapabilities:function(capabilitiesURL) {
			var requestURL = sl_url.getAPIPath();
			//request.post('');
		},
		
		
		getImageURL:function(url,width,height) {
			var baseURL = url.split('?').shift()+'?';
			var params = sl_url.getURLParamObj(url);
			var bbox = params['bbox'].split(',');
			var version = params['version'];
			
			if (version=='1.1.1' || version =='1.1.0'){
				var bbox1 = bbox[0];// xhr.find('LatLonBoundingBox').first().attr('minx');
				var bbox2 = bbox[1];//xhr.find('LatLonBoundingBox').first().attr('miny');
				var bbox3 = bbox[2];//xhr.find('LatLonBoundingBox').first().attr('maxx');
				var bbox4 = bbox[3];//xhr.find('LatLonBoundingBox').first().attr('maxy');
				xdiff = bbox3-bbox1;
				ydiff = bbox4-bbox2;

	 		} else {
				var bbox1 = bbox[1];
				var bbox2 = bbox[0];
				var bbox3 = bbox[3];
				var bbox4 = bbox[2];
				minx = Math.min(bbox4,bbox2);
				miny = Math.min(bbox3,bbox[0]);
				maxx = Math.max(bbox4,bbox2);
				maxy = Math.max(bbox3,bbox[0]);
				xdiff = maxx -minx;
				ydiff = maxy - miny;
			}
			isXMax = Math.max(xdiff,ydiff) == xdiff;
			imgWidth = width;
			imgHeight = height;
			if(isXMax) {
				imgHeight = imgWidth * ydiff/xdiff;
			} else {
				imgWidth = imgHeight * xdiff/ydiff;
			}
			params['width'] = Math.round(imgWidth);
			params['height'] = Math.round(imgHeight);
			url = sl_url.urlFromParamObj(baseURL, params);

			
			return url;
		}				
	}
	
});