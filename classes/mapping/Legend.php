<?php

namespace mapping;

use utils\ColorUtil;

class Legend {
	public static function SetMSLegend($mapObj, $width, $height, $bgColor = '#cccccc', $labelColor = '#000000') {
		
		// configure the legend
		$mapObj->legend->set ( 'width', $width );
		$mapObj->legend->set ( 'height', $height );
		$mapObj->legend->set ( 'status', MS_ON );
		ColorUtil::SetMSRGB ( $mapObj->legend->imagecolor, $bgColor );
		ColorUtil::SetMSRGB ( $mapObj->legend->label->color, $labelColor );
		$mapObj->legend->label->set ( 'size', 9 );
		if(defined('IS_DEV_SANDBOX')) {
            $mapObj->legend->label->set ( 'type', MS_TRUETYPE );		    
		}
		$mapObj->legend->label->set ( 'font', 'Vera' );
		$mapObj->legend->label->set ( 'position', MS_UR );
		$mapObj->legend->set ( 'keysizex', 18 );
		$mapObj->legend->set ( 'keysizey', 12 );
		$mapObj->legend->set ( 'keyspacingx', 15 );
		$mapObj->legend->set ( 'keyspacingy', 14 );
	}
}
?>