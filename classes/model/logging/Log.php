<?php

namespace model\logging;

class Log {
	
	public static function Alert($message) {
		$message = '[ALERT]:'.$message;
		openlog('php', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
		syslog(LOG_ALERT, $message);
		closelog();
	}
	
	public static function Debug($message) {
		$message = '[DEBUG]:'.$message;
		openlog('php', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
		syslog(LOG_DEBUG, $message);
		closelog();
	}
	
	public static function Error($error) {
		if(is_a($error,'Exception')) {
			$message = "".$error;
		} else {
			$message = $error;
		}
		$message = '[ERROR]:'.$message;
		openlog('php', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
		syslog(LOG_ERR, $message);
		closelog();
		
	}
	
	public static function Message($message) {
		$message = '[INFO]:'.$message;
		openlog('php', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
		
		syslog(LOG_INFO, $message);
		closelog();
	}
	
	public static function Notice($message) {
		$message = '[NOTICE]:'.$message;
		openlog('php', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
		syslog(LOG_NOTICE, $message);
		closelog();
	}
		
	public static function Warning($message) {
		$message = '[WARNING]:'.$message;
		openlog('php', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
		syslog(LOG_WARNING, $message);
		closelog();
	}

}

?>