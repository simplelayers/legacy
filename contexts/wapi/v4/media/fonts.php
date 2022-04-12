<?php

use utils\ParamUtil;
use model\classifcation\Fonts;
/**
 * A list of the vector layer's columns/attributes, and widgets for adding/deleting columns.
 * 
 * @package Dispatchers
 */
/**
 */

function _exec() {
	
	$params = WAPI::GetParams();
	list($action) = ParamUtil::Requires($params,'action');
	$fonts = new Fonts();
	
	switch($action) {
	    case WAPI::ACTION_DRIVER:
	        WAPI::SendSimpleResponse($fonts->GetDriver($fonts::DRIVER_GOOGLE,true));
	        break;
		case WAPI::ACTION_LIST:
			$font_type = ParamUtil::Get($params,'font_type',Fonts::FONT_TYPE_SYMBOL);
			WAPI::SendSimpleResults($fonts->ListFonts($font_type));
			break;
		case WAPI::ACTION_GET:
			
			$target = ParamUtil::RequiresOne($params,'target');
			
			switch($target) {
				case 'charmap':
					$font = ParamUtil::RequiresOne($params,'font');
					$fonts->WriteCharMap($font,false);
					break;
				case 'char':
					list($charCode,$font) = ParamUtil::Requires($params,'code','font');
					$size = ParamUtil::Get($params,'size',null);
					$color = ParamUtil::Get($params,'color');
					
					$fonts->WriteCharImage($font,$charCode,false,$size,$color);
					break;
				case 'sample':
				    list($font) = ParamUtil::Requires($params,'font');
				    $im = imagecreatetruecolor(400, 30);
				    
				    $size = ParamUtil::Get($params,'size',null);
				    $padding=15;
				    $height =$size+(2*$padding);;
				    
				    $x = $padding;
				    $y = $height-$padding;
				    $color = ParamUtil::Get($params,'color');
				    $sample = ParamUtil::Get($params,'sample','The quick red fox jumps over the lazy brown dog.');
				    $fonts->GetSampleImage($font,$sample,$size,$color);
			}
			
			
			
	}
	
	return true;
	
}

?>
