define([ "dojo/_base/declare" ], function(declare) {
	return declare(null, {
		GetFeatureText:function(feature,plid,layers) {
			var targetLayer = null;
			for( var l in layers) {
				var layer = layers[l];
				if( layer.plid==plid) {
					targetLayer = layer;
					break;
				}
			}
			var tooltip = targetLayer.tooltip.value;
			for( var field in feature) {
				var repField= "["+field+"]";
				var val = feature[field];
				if(val)	tooltip = tooltip.replace(repField,val);
			}
			return tooltip;
		}
	});
});