<?php
class Security
{

	public static function Encode_1way($string, $salt)
	{
		$ini = System::GetIni();
		$pepper = $ini->secret_spice;
		return hash("sha512", hash("sha256", $pepper . $string . hash("sha256", strtolower($salt))));
	}

	public static function Encode_TwoWay($value,$salt=null,$iv=null)
	{
		$ini = System::GetIni();
		$salt = is_null($salt) ?  base64_decode($ini->secret) : $salt;
		$iv = is_null($iv) ? self::IVize16($ini->secret) : self::IVize16($iv);
		return \openssl_encrypt($value, "AES-256-CBC", $salt, 0, $iv);
	}

	public static function SRead($val, $salt = null)
	{
		$ini = System::GetIni();
		$pepper = $ini->secret;
		$em = "AES-256-CBC";
		$salt = is_null($salt) ? base64_decode($pepper) : $salt;
		$iv = self::IVize16($ini->secret);
		$retval = openssl_decrypt($val, $em, $salt, 0, $iv);
		return $retval;
	}

	public static function IVize16($ivString)
	{
		// Desired IV length for AES (16 bytes = 128 bits)
		$ivLength = 16;
		// If the IV is longer than 16 bytes, truncate it
		if (strlen($ivString) > $ivLength) {
			return substr($ivString, 0, $ivLength);
		}
		// If the IV is shorter than 16 bytes, pad it with null bytes
		if (strlen($ivString) < $ivLength) {
			return str_pad($ivString, $ivLength, "\0");
		}

		// If the IV is exactly 16 bytes, return it as is
		return $ivString;
	}
}
