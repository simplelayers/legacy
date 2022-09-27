<?php

namespace model\media;

use utils\ParamUtil;
use utils\ColorUtil;
class Icons {
	
	private $ini;
	
	public function __construct() {
		$this->ini = parse_ini_file('/etc/simplelayers/icons.ini');
		
	}
	
	public function ListIcons($params) {
		list($category) = ParamUtil::Requires($params,'category');
		$path = $this->ini['icons_path'];
		
		$dir = scandir($path.DIRECTORY_SEPARATOR.$category.DIRECTORY_SEPARATOR.'32px');
		$icons = array();
		$ctr = 0;
		$row = 0;
		foreach($dir as $fileName) {
			if(in_array($fileName,array('.','..'))) continue;
			$fileName = explode('.',$fileName);
			array_pop($fileName);
			$fileName = implode('.',$fileName);
			$icons[] = $fileName;
			$ctr++;			
			
			//$icons[] = $file->getFilename();
		}
		
		//var_dump($icons);
		return $icons;
	}
	
	public function ListCategories() {
		return $this->ini['categories'];
	}
	public function ListSizes() {
		return $this->ini['sizes'];
	}
	
	public function GetIcon($params,$renderToStream=false) {
		list($category,$icon,$size) = ParamUtil::Requires($params,'category','icon','size');
		$color = ParamUtil::Get($params,'color');
		$style = ParamUtil::Get($params,'style','normal');
		if(!stripos($size,'px')) $size.='px';
		if(!in_array($size,$this->ListSizes())) throw new \Exception('Invalid icon size: Size '.$size.' not found');
		if($renderToStream) {
			header('Content-Type: image/png');
			if($color) {
				$r = $g = $b= 0;
				$color = ColorUtil::Web2RGB($color, $r, $g, $b);
				$imgSize = str_replace('px','',$size);
				$baseImg = imagecreatetruecolor($imgSize,$imgSize);
				
				if($style=='reverse') { 
					$c = imagecolorallocate($baseImg,$r,$g,$b);
					imagefill($baseImg,0,0,$c);
				} else {
					imagefill($baseImg,0,0,IMG_COLOR_TRANSPARENT);
				}
				imagesavealpha( $baseImg, true );
				
				
				$im = imagecreatefrompng($this->GetIconPath($category,$icon,$size));
				
				$rr = $rg =$rb = 0;
				$rcolor = ParamUtil::Get($params,'rev_color','ffffff');
				$rcolor = ColorUtil::Web2RGB($rcolor, $rr, $rg, $rb);
				
				if($style=='reverse') {
					#imagefilter($im,IMG_FILTER_NEGATE);
					imagefilter($im,IMG_FILTER_COLORIZE,$rr,$rg,$rb,0);
					
					#imagefilter($im,IMG_FILTER_NEGATE);
					//imagepng($im);
					//imagedestroy($im);

					imagecopy($baseImg, $im, 0, 0, 0, 0, $imgSize, $imgSize);
					#imagefilter($baseImg,IMG_FILTER_NEGATE);
					#magefilter($baseImg,IMG_FILTER_COLORIZE,$r,$g,$b);
				}  else {
					imagefilter($im,IMG_FILTER_COLORIZE,$r,$g,$b);
					
					
				}
				
				if($renderToStream) {
					if($style=='reverse') {
						imagepng($baseImg);
						imagedestroy($baseImg);
					} else {
						imagepng($im);
					}
					imagedestroy($im);
					
						
					return null;
				} else {
					imagedestroy($im);
					return $baseImg;
				}
				
			}
			readfile($this->GetIconPath($category,$icon,$size));
		}
	}
	public function GetIconURL($category,$icon,$size) {
		return implode(DIRECTORY_SEPARATOR,array(BASEURL,'wapi','media','icons','action:get','target:icon','category:'.$category,'icon:'.$icon,'size:'.$size));
		
	}
	private function GetIconPath($category,$icon,$size) {
		return $this->ini['icons_path'].DIRECTORY_SEPARATOR.$category.DIRECTORY_SEPARATOR.$size.DIRECTORY_SEPARATOR.$icon.'.png';
	}
}

?>