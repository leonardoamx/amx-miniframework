<?php
class SessionManager {
	public static $sessionName ='tester';

	public static function start (){
		self::init ();
		$_SESSION['timestamp'] =date ('c');
		$_SESSION['fingerprint'] = md5($_SERVER['HTTP_USER_AGENT']);
	}
	public static function destroy (){
		self::init ();
		$_SESSION =array ();
//		session_destroy ();
		$parametros_cookies = session_get_cookie_params();  
		setcookie(session_name(),0,1,$parametros_cookies["path"]); 

	}
	public static function exists (){
		$exists =false;
		if (empty ($_SESSION)){
			self::init ();
		}
		$exists =isset ($_SESSION['timestamp']) && $_SESSION['fingerprint']==md5($_SERVER['HTTP_USER_AGENT']);
		return $exists;
	}
	public static function expired (){
		$expired =true;
		return $expired;
	}
		
	private static function init (){
		ini_set('session.use_only_cookies', 1);
		ini_set('session.cookie_httponly', 1);
		session_name (self::$sessionName);
		session_start ();
	}
}?>