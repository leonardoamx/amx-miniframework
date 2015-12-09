<?php
/** Class SessionManager
* Hace un manejo simple de sesiones PHP
* PHP version: 5.x
* @version 1.1
* @author: Leonardo Molina; lama_amx AT hotmail DOT com
*
*/
/* CHANGELOG
	1.1 2013-04-08 Se agrega la constante userInfo
	1.0 Versión inicial
*/

class SessionManager {
	const KEY_USER_INFO ='userInfo';

	public static $sessionName ='INDAPPBEB';
	public static $userInfo;
	public static $userId;

	public static function start (){
		self::init ();
		$_SESSION['timestamp'] =date ('c');
		$_SESSION['fingerprint'] = md5($_SERVER['HTTP_USER_AGENT']);
	}

	public static function destroy (){
		self::init ();
		$_SESSION =array ();
//		session_destroy (); //Don't use it. In shared hosting can destroy sessions from other applications
		$parametros_cookies = session_get_cookie_params();
		setcookie(session_name(),0,1,$parametros_cookies["path"]);

	}

	public static function exists (){
		$exists =false;
		self::init ();
		if (!empty ($_SESSION))
			$exists =isset ($_SESSION['timestamp']) && $_SESSION['fingerprint']==md5($_SERVER['HTTP_USER_AGENT']);
		return $exists;
	}

	public static function expired (){
		$expired =true;
		return $expired;
	}

	public static function setUserInfo ($data){
		$_SESSION[self::KEY_USER_INFO] =$data;
		self::$userInfo =$data;
	}
	
	

	private static function init (){
		if (session_id() == ''){
			ini_set('session.use_only_cookies', 1);
			ini_set('session.cookie_httponly', 1);
			session_name (self::$sessionName);
			session_start ();
		}
		if (isset ($_SESSION[self::KEY_USER_INFO])){
			self::$userInfo =$_SESSION[self::KEY_USER_INFO];
			if (isset (self::$userInfo['idUsuario']))
				self::$userId =intval (self::$userInfo['idUsuario']);
		}
	}
}?>