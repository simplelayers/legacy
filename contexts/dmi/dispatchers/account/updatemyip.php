<?php

	function _config_updatemyip() {
		$config = Array();
		$config['sendUser'] = true;
		// Start config
		// Stop config
		return $config;
	}

	function _dispatch_updatemyip($template, $args) {
		$world = $args['world'];
		$user = $args['user'];
		
		$record = $world->db->GetRow("select * from _developers where cguserid={$user->id}");
		if(!$record) die("user not recoginzed as a developer");

		$world->db->Execute("update _developers set ipaddress=? where cguserid=?",array($_SERVER['REMOTE_ADDR'],$user->id));
		exec("/usr/bin/php /usr/local/share/dyn/cg_dyn_updater.php > /dev/null 2>&1");
		echo "Updated IP address to ".$_SERVER['REMOTE_ADDR'];	
	}

?>
