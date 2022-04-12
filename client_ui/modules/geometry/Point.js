define([ "dojo/_base/declare","dojo/_base/lang"], function(declare,lang) {
	return declare(null, {
		x:0,
		y:0,
		constructor:function(x,y) {
			this.x = x;
			this.y = y;
			return this;
		},
		Clone:function() {
			return lang.delegate(this);
			//return new sl_point(this.x,this.y);
		},
		MoveTo:function(x,y) {
			this.x = x;
			this.y = y;
		},
		MoveBy:function(x,y) {
			this.x+=x;
			this.y+=y;
		},
		ToWKT:function() {
			var wkt = "POINT("+this.x+" "+this.y+")";
			return wkt;
		},
		ToWKTSubPoint:function() {
			return this.x+" "+this.y;
		}
	});
});