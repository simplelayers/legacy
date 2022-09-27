<?php
class Security {

	public static function Encode_1way($string,$salt) {
		$ini = System::GetIni();
		$pepper = $ini->secret_spice;
		return hash ( "sha512", hash ( "sha256", $pepper . $string . hash ( "sha256", strtolower ( $salt ) ) ) );
	}
}

?>