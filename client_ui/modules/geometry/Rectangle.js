define([ "dojo/_base/declare","sl_modules/geometry/Point" ], function(declare,sl_point) {
	return declare(null, {
		startPoint:null,
		endPoint:null,
		FromPointAndDims:function(ulX,ulY,width,height) {
			this.startPoint = new sl_point(ulX,ulY);
			this.endPoint = this.startPoint.Clone();
			this.endPoint.MoveBy(width,height);
			return this;
		},
		FromBufferedPoint:function(pt, buffer) {
			this.startPoint = pt.Clone();
			this.startPoint.MoveBy(-buffer,-buffer);
			this.endPoint = pt.Clone();
			this.endPoint.MoveBy(buffer,buffer);
			return this;
		},
		AdjustPoint:function(pt) {
			pt.MoveBy(-this.startPoint.x,-this.startPoint.y);			
		},
		IsWithin:function(pt) {
			if(pt.x < this.startPoint.x) return false;
			if(pt.x > this.endPoint.x) return false;
			if(pt.y < this.startPoint.y) return false;
			if(pt.y > this.endPoint.y) return false;
			return true;
		},
		ToPBox:function() {
			points = [this.startPoint.x,this.startPoint.y,this.endPoint.x,this.endPoint.y];
			return points.join(',');
		},
		ToWKT:function() {
			var wkt = "POLYGON(";
			var pt = this.startPoint.clone();
			wkt+=pt.ToWKTPart()+',';
			pt.moveTo(this.endPoint.x, this.startPoint.y);
			wkt+=pt.ToWKTPart()+',';
			pt.moveTo(this.endPoint.x, this.endPoint.y);
			wkt+=pt.ToWKTPart()+',';
			pt.moveTo(this.startPoint.x, this.endPoint.y);
			wkt+=pt.ToWKTPart()+',';
			wkt+=startPoint.ToWKTPart();
			wkt+=')';
			
			return wkt;
		}		
	});
});