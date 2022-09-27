<?php

namespace model\media;

class IconSets {
	
	const icons_ini = '/etc/simplelayers/icons.ini';
	
	public static function GetIconsPath() {
		$ini = parse_ini_file('/etc/simplelayers/icons.ini');
		$iconPath  = $ini['icons_path'];		
		return $iconPath;
	}
	
	public static function ListIconSets() {
		$iconsets = self::GetIconsetIni(self::GetIconsPath());
		return $iconsets;
		
	}
	
	public static function ListIcons($iconSet) {
		$ini = parse_ini_file('/etc/simplelayers/icons.ini');
		$iconPath  = $ini['icons_path'];
		$iconSets = self::GetIconsetIni($iconPath);
		foreach($iconSets as $set=>$info ) {
			if($set == $iconSet) {
				$data = parse_ini_file($iconPath.$info['ini'],true);
				$icons = array();
				foreach($data['icons'] as $icon) {
					list($icon,$category) = explode(':',$icon);
					$icons[] = array('icon'=>$icon,'category'=>$category);					
				}
				return $icons;
			}
		}		
	}
	
	public static function GetIconsetIni($path) {
		return parse_ini_file($path.'iconsets.ini',true);

	}
	
	public static function GenerateCSS($iconSet) {
		
	}
	
}

?>