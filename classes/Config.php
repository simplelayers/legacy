<?php

namespace simplelayers;

class Config {
	private $config = array();
	public function Config() {
		$db = \System::GetDB(\System::DB_ACCOUNT_SU);
		$ini = \System::GetIni();
		$cx = $db->Execute('SELECT * FROM config')->getRows();
		foreach ($cx as $entry) {
			$key = $entry['key'];
			$val = $entry['val'];
			$this->config[$key] = $val;
			$ini->$key = $val;
		}
				
	}
	
	
	
}

?>