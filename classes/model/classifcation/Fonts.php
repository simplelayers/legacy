<?php

namespace model\classifcation;
use utils\ColorUtil;
\System::RequireCharMap();

class Fonts {
	
	const INI_FILE = '/mnt/media/fonts/fonts.ini';
	const FONT_TYPE_SYMBOL = 'symbol_fonts';
	const FONT_TYPE_TEXT = 'text_fonts';
	const DRIVER_GOOGLE_PATH = '/mnt/media/fonts/fonts_google.json';
	
	const DRIVER_GOOGLE = 'google';
	
	private $fontData;
	
	public function __construct() {
		$fontData = parse_ini_file(self::INI_FILE,true);
		$this->fontData = array();
		foreach($fontData as $font_type=>$data) {
			$this->fontData[$font_type] = $data['fonts'];
		}
		
		
	}

	
	public function WriteCharMap($fontName,$asFontFile=false) {
		
		$fontPath = $this::GetFontPath($fontName);
		
		$format = ($asFontFile==true) ? "*.png|9" : 'png';
		charmap($fontPath,18,range(hexdec('20'),hexdec('FF')),$format,null,5);
		//echo($img);
		
	}
	
	public function WriteCharImage($fontName,$code,$asFontFile=false,$size=null,$color='#000000') {
		if(is_null($size)) $size = 32;
		$fontPath = $this::GetFontPath($fontName);
		$format = ($asFontFile==true) ? "U".$code.".png|9" : 'png';
		$size = intval($size);
		$r=$g=$b = 0;
		
		ColorUtil::Web2RGB($color, $r, $g, $b); 
		charmap($fontPath,$size,range(hexdec($code),hexdec($code)), $format,array('text'=>array($r,$g,$b),'index'=>array(255,255,255),'border'=>array(255,255,255)),5);
	}
	
	public static function Get() {
		
	}
	
	public function GetFontPath($fontName) {
		$ini = \System::GetIni();
		
		if(isset($this->fontData[self::FONT_TYPE_SYMBOL][$fontName])) {
			return $ini->fonts_path.$this->fontData[self::FONT_TYPE_SYMBOL][$fontName];
		}
		
		if(isset($this->fontData[self::FONT_TYPE_TEXT][$fontName])) {
			return $ini->fonts_path.$this->fontData[self::FONT_TYPE_TEXT][$fontName];
		}		
	}
	
	public function GetDriver($driver=self::DRIVER_GOOGLE,$decode=false) {
	    $data = file_get_contents(self::DRIVER_GOOGLE_PATH);
	    
	    return $decode ? json_decode($data,true) : $data;
	}
	
	public function GetSampleImage($fontName,$string,$size=32,$color="#000000",$asFontFile=false) {
	    if(is_null($size)) $size = 32;
	    $fontPath = $this::GetFontPath($fontName);
	    if(!file_exists($fontPath)) {
	        throw new \Exception("Font $fontName @ $fontPath not found");
	    }
	    $format = ($asFontFile==true) ? "U".$string.".png|9" : 'png';
	    $size = intval($size);
	    $r=$g=$b = 0;
	    $padding=10;
	    $bbox = imagettfbbox($size, 0, $fontPath, $string);
	    list($llx,$lly,$lrx,$lry,$urx,$ury,$ulx,$uly) = $bbox;
	    $width = $lrx-$llx;
	    $height = $lry-$uly;
	    $padWidth =$width+2*$padding;
	    $padHeight = $height+2*$padding;
	    $image = @imagecreatetruecolor($padWidth, $padHeight);
	    $color = ColorUtil::Web2RGB($color, $r, $g, $b);
	    $colorText = imagecolorallocate($image, $r,$g,$b);
	    $white = imagecolorallocate($image, 255, 255, 255);
	    imagefill($image, 0, 0, $white);
	    imagettftext($image,$size,0,$padding,($padding-$uly),$colorText,$fontPath,$string);
	    imagepng($image);
	    imagedestroy($image);
	    exit(0);
	       //0 lower left corner, X position 1 lower left corner, Y position 2 lower right corner, X position 3 lower right corner, Y position 4 upper right corner, X position 5 upper right corner, Y position 6 upper left corner, X position 7 upper left corner, Y position
	    //var_dump($bbox);
	    
	}
	
	public function ListFonts($type) {
		return array_keys($this->fontData[$type]);
	}
	
}

?>