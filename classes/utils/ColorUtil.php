<?php

namespace utils;

class ColorUtil {
	
	public static function Web2RGB($webcolor,&$r,&$g,&$b) {
	    if(is_null($webcolor)) $webcolor = "#000000";
	    $webcolor = trim(urldecode($webcolor));
		
		if($webcolor=="trans") {
			$r = $g = $b = -1;
			return;			
		}
		if(substr($webcolor,0,1)=="#") $webcolor = substr($webcolor,1);
		$r = hexdec( substr( $webcolor,0,2));
		$g = hexdec( substr( $webcolor,2,2));
		$b = hexdec( substr( $webcolor,4,2));							
	} 	
	
	public static function RGB2Web($r,$g,$b) {
		$webcolor = sprintf('#%02x%02x%02x',$r,$g,$b);
		return $webcolor;
	}
	
	public static function SetMSRGB($msColorObj,$webcolor){
		$r = $g = $b = -1;
		self::Web2RGB($webcolor,$r,$g,$b);
		$msColorObj->setRGB($r,$g,$b);
	}
	
}

?>