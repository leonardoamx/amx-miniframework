<?php
/** Contains method to log debug messages in a file or screen.
** @author: Leonardo Molina lama_amx at hotmail dot com
** @version: 1.2
** @changelog:
	1.2 2014.03.20 Added logTime
	1.1 2014.03.03 Added logStackTrace
	1.0 2013.02.15 Initial version
*/

class Logger {
	public static $logEnabled			=true;
	public static $logLevel				=2;
	public static $logToFile			=true;
	public static $logPath  			='logs-amx.txt';

	public static $isFirstLog			=true;
    public static $timesList;

        /** Logs the enlapsed time between two points of the code execution
        * param $id. String. An identifier to separate time tracking of different aspects of the code.
        * param $message. String. Custom hint to track the code execution
        * param $level. Int. @see $logLevel and log method.
        */
	public static function logTime ($id, $message='', $level=0) {
        $isFirstRun =false;
        if (empty (self::$timesList)){
            $isFirstRun =true;
        }
        if (empty (self::$timesList[$id])){
            $startTime =microtime (true);
            self::$timesList[$id] =array (
                 'startTime' => $startTime
                ,'lastTime'  => $startTime
            );
        }

        $now =microtime (true);
        $fromStartTime =$now -self::$timesList[$id]['startTime'];
        $fromLastTime  =$now -self::$timesList[$id]['lastTime'];
        self::$timesList[$id]['lastTime'] =$now;

        $stringId =str_pad ($id, 10, ' ', STR_PAD_LEFT);
        $fromStartTime =number_format ($fromStartTime, 13, '.', '');
        $fromLastTime  =number_format ($fromLastTime, 13, '.', '');
        $fromStartTime =str_pad ($fromStartTime, 12, ' ', STR_PAD_LEFT);
        $fromLastTime =str_pad ($fromLastTime, 12, ' ', STR_PAD_LEFT);
        if ($isFirstRun)
            self::log ("             Seconds since start | Since last check");
        self::log ("Time $stringId. $fromStartTime |  $fromLastTime  $message", '', $level);
    }

        /** Logs a custom message
        * param $text. String. Custom text.
        * param $message. String, Optional. Additional custom text.
        * param $level. Int, default 0. The log level of this messages. @see $logLevel.
        * if $message param is provided, a string with the format ($message): $text will be logged; otherwise, $text content will be logged.
        */
	public static function log ($text, $message='', $level=0) {
		if ($message != '')
			$message ="($message): ";
		$msg ="$message$text";
		if (self::$logEnabled){
			if (self::$logToFile){
				self::logToFile ($msg, $level);
			}else
				if ($level >= self::$logLevel || $level==0)
					echo "<p>$msg</p>";
		}
	}

        /** Creates an Exception & logs the stack trace, as formatted by getTraceAsString method.
        * param $message. String, Optional. Additional custom text.
        * param $level. Int, default 0. The log level of this messages. @see $logLevel.
        */
	public static function logStackTrace ($message='', $level=0) {
        $e =new Exception ("Logger::logStackTrace");
        self::log ("$message " .$e->getTraceAsString (), $level);
	}

        /** Logs a custom message
        * param $variable. Mixed. Variable to trace. It's logged as formatted by print_r method
        * param $message. String, Optional. Additional custom text.
        * param $level. Int, default 0. The log level of this messages. @see $logLevel.
        * if $message param is provided, a string with the format ($message): $variable will be logged; otherwise, $variable content will be logged.
        */
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

        /** Logs a string into a text file
        * param $message. String. Text to log.
        * param $level. Int, default 0. The log level of this messages. @see $logLevel.
        * In the first call of this method, a timestamp is inserted too. This allows to distinguish logs from different processes or dates.
        */
	public static function logToFile ($message, $level=0) {
		if ($level>=self::$logLevel || $level==0){
			$file =fopen (self::$logPath, 'a');
            $logHeader =self::getLogHeader ();
			fputs ($file, "$logHeader$message\r\n");
			fclose ($file);
		}
	}

    private static function getLogHeader (){
        $result ='';
		if (self::$isFirstLog){
			$date =date ('==== Y-m-d H:i:s');
			$result ="\n$date\n";
			self::$isFirstLog =false;
		}
        return $result;
    }

}