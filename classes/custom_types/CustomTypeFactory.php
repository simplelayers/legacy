<?php
namespace custom_types;

class CustomTypeFactory
{

	const CG_URL ="cg_url";

	public static function GetTypes() {
		return array(self::CG_URL);
	}

	public static function GetCustomType($db,$type) {
		switch($type) {
			case self::CG_URL:
				return new Type_CG_URL($db);
				break;
		}
	}

}

?>