<?php

class StorageUpgrades  {

		const GB_PRICE = .33; // $100/300GB = $.33/GB
	
		const MB_100 = 0.1;
		const MB_500 = 0.5;
		const GB_1 = 1.0;
		const GB_2 = 2.0;
		const GB_5 = 5.0;
		const GB_300 = 300;
		const GB_600 = 600;
		const GB_1200= 1200;

		private static $enum;
		private static $priceEnum;
		
		public static function GetEnum($replace=false) {
			if( (self::$enum !==NULL) and !$replace ) return self::$enum;
			
			$mappings = array(	''.self::MB_100 =>	'Add 100 MB of storage',
								''.self::MB_500 =>	'Add 500 MB of storage',
								''.self::GB_1 =>	'Add   1 GB of storage',
								''.self::GB_2 =>	'Add   2 GB of storage',
								''.self::GB_5 =>	'Add   5 GB of storage',
								''.self::GB_300 =>	'Add 300 GB of storage',
								''.self::GB_600 =>	'Add 600 GB of storage',
								''.self::GB_1200 =>'Add 1.2 TB of storage',
							);
			self::$enum = new Enum($mappings);
			
			return self::$enum;			
		}
		
		public static function GetPrice($upgrade,$asDollarString = FALSE) {
			$price = $upgrade * self::GB_PRICE;
			if($asDollarString) return '$'. ($price < 1) ? '0'.$price : $price;
			return $price;
		}
}