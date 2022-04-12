define([], function() {
	return {
		makePxPt : function(x, y) {
			pxPt = {
				'x' : x,
				'y' : y,
				toString : function() {
					return this.x + ',' + this.y;
				}
			};

			return pxPt;
			
		},
		makePxBox : function(x1, y1, x2, y2) {
			var xMin = Math.min(x1, x2);
			var xMax = Math.max(x1, x2);
			var yMin = Math.min(y1, y2);
			var yMax = Math.max(y1, y2);
			var box = {
				'x1' : xMin,
				'y1' : yMin,
				'x2' : xMax,
				'y2' : yMax,
				toString : function() {
					return Math.round(this.x1) + ',' + Math.round(this.y1) + ',' + Math.round(this.x2) + ','+
							Math.round(this.y2);
				}
			};
			return box;

		},
		ptToBox : function(x, y, pxRadius) {
			return this.makePxBox(x - pxRadius, y - pxRadius, x + pxRadius, y
					+ pxRadius);
		}
	}
})