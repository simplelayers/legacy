<?php

class Graphics {
	
	private static $colors;
	
	public static function GetColors() {
		if(self::$colors ) return self::$colors;
		
		self::$colors = array(
		   '#000000','#696969','#808080','#a9a9a9','#c0c0c0','#d3d3d3','#dcdcdc','#ffffff','#708090',
		   '#bc8f8f','#cd5c5c','#a52a2a','#b22222','#f08080','#800000','#8b0000','#ff0000','#fa8072',
		   '#ff6347','#dc143c','#ffc0cb','#ffb6c1','#e9967a','#ff4500','#ff7f50','#ffa07a','#a0522d',
		   '#d2691e','#8b4513','#f4a460','#cd853f','#ff8c00','#d2b48c','#deb887','#ffdead','#ffebcd',
		   '#ffe4b5','#f5deb3','#ffa500','#daa520','#b8860b','#fff8dc','#ffd700','#f0e68c','#eee8aa',
		   '#bdb76b','#808000','#ffff00','#6b8e23','#9acd32','#556b2f','#adff2f','#7cfc00','#7fff00',
		   '#8fbc8f','#228b22','#32cd32','#90ee90','#98fb98','#006400','#008000','#00ff00','#2e8b57',
		   '#3cb371','#00ff7f','#00fa9a','#66cdaa','#7fffd4','#40e0d0','#20b2aa','#48d1cc','#2f4f4f',
		   '#afeeee','#008080','#008b8b','#00ffff','#00ffff','#00ced1','#5f9ea0','#b0e0e6','#add8e6',
		   '#191970','#000080','#00008b','#0000cd','#0000ff','#483d8b','#6a5acd','#7b68ee','#00bfff',
		   '#87ceeb','#87cefa','#4682b4','#1e90ff','#b0c4de','#6495ed','#4169e1','#9370db','#8a2be2',
		   '#4b0082','#9932cc','#9400d3','#ba55d3','#d8bfd8','#dda0dd','#ee82ee','#800080','#8b008b',
		   '#ff00ff','#ff00ff','#da70d6','#c71585','#ff1493','#ff69b4','#db7093'
		);
	}
	
}

?>