<?php
/** Clase de funciones varias
** @author: Leonardo Molina lama_amx at hotmail dot com
** @version: 1.0
** @changelog:
	1.0 2013.02.15 Initial version
*/

class Logger {
	public static $logEnabled			=true;
	public static $logLevel				=2;
	public static $logToFile			=true;
	public static $logPathDeveloment	='logs-dev-amx.txt';
	public static $logPathLive			='logs-amx.txt';
	
	public static $isFirstLog			=true;

	public static function log ($text, $message='', $level=0) {
		if ($message != '')
			$message ="($message): ";
		$msg ="$message$text";
		if (self::$isFirstLog){
			$date =date ('==== Y-m-d H:i:s');
			$msg ="\n$date\n$msg";
			self::$isFirstLog =false;
		}
		if (self::$logEnabled){
			if (self::$logToFile){
				self::logToFile ($msg, $level);
			}else
				if ($level >= self::$logLevel || $level==0)
					echo "<p>$msg</p>";
		}
	}
	public static function trace ($variable, $message='', $level=0) {
		if ($message != '')
			$message .=': ';
		if (self::$logEnabled){
			$msg =$message .'[trace] '.print_r ($variable, true);
			if (self::$logToFile){
				self::logToFile ($msg, $level);
			}else{
				echo "<pre>$msg</pre>";
			}
		}
	}
	public static function logToFile ($msg, $level=0) {
		$logPath =$_SERVER['SERVER_NAME']=='localhost' ? self::$logPathDeveloment : self::$logPathLive;
		if ($level>=self::$logLevel || $level==0){
			$file =fopen ($logPath, 'a');
			fputs ($file, "$msg\r\n");
			fclose ($file);
		}
	}

}?>