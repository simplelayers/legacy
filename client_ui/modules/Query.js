define([
    "sl_modules/sl_URL",
    "sl_modules/WAPI",
    "sl_modules/geometry/Rectangle"
], function(sl_url,wapi,sl_rect){
	return {
		query:function(mLayers,projectId,criteria) {
			
		},
		queryROI:function(mLayers,projectId,bbox,pxBox) {
			
		},
		queryPtBufferedPx:function(mLayers,projectId,bbox,width,height,pxPt,pxRadius) {
			
			var rect = new sl_rect();
			rect.FromBufferedPoint(	pxPt,pxRadius );
			layerIds = [];
			
			for (var lid in mLayers) {
				var layer = mLayers[lid];
				
				if((layer.layer_on==1) && (layer.search_on==1) && (layer.typeLabel=='vector'))  {
					
					layerIds.push(layer.plid);
				}
			}
			
			request = {project:projectId,pxrect:rect.ToPBox(),'bbox':bbox,players:layerIds.join(','),'width':width,'height':height};
			
			func = this.resultHandler.bind(this);
			if(arguments.length==8) func = arguments[7];
			wapi.exec('features/query',request,func);
			
		},
		queryPt:function(mLayers,projectId,bbox,width,height,pxPt) {
			func = this.resultHandler.bind(this);
			if(arguments.length==7) func = arguments[6];
			this.queryPtBufferedPx(mLayers,projectId,bbox,width,height,pxPt,10,func);
			
		},
		resultHandler:function(result) {
			
		}
		
		
		
	}
});