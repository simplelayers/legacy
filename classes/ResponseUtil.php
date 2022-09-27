<?php
class ResponseUtil {
	
	public static function Write($message) {
		echo $message;
		ob_flush();
	}
}

?>