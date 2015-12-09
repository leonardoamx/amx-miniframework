<?php
/** Class SessionManager
* Hace un manejo simple de sesiones PHP
* PHP version: 5.x
* @version 1.2
* @author: Leonardo Molina; lama_amx AT hotmail DOT com
*
*/
/* CHANGELOG
    1.2 2014-08-28 Se quitó el soporte para userInfo
    1.1 2013-04-08 Se agrega la constante userInfo
    1.0 Versión inicial
*/

abstract class SessionManager {

    public static $sessionName ='APPSESSION1';
    public static $sessionTimeLimit =0;

    public static function start (){
        self::init ();
        self::resetTimestamp();
        $_SESSION['fingerprint'] = md5($_SERVER['HTTP_USER_AGENT']);
    }

    public static function destroy (){
        self::init ();
        $_SESSION =array ();
//        session_destroy (); //Don't use it. In shared hosting can destroy sessions from other applications
        $parametros_cookies = session_get_cookie_params();
        setcookie(session_name(),0,1,$parametros_cookies["path"]);

    }

    public static function exists (){
        $exists =false;
        self::init ();
        if (!empty ($_SESSION)){
            $exists =isset ($_SESSION['timestamp']) && $_SESSION['fingerprint']==md5($_SERVER['HTTP_USER_AGENT']);
        }
        if ($exists && self::expired()){
            self::destroy();
            $exists =false;
        }
        return $exists;
    }

    public static function expired (){
        $expired =false;
        if (self::$sessionTimeLimit > 0){
            $currentTime =time();
            $duration =$currentTime -(int)$_SESSION['timestamp'];
            $expired =$duration > self::$sessionTimeLimit;
        }
        if (!$expired){
            self::resetTimestamp();
        }
        return $expired;
    }

    
    private static function init (){
        if (session_id() == ''){
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            session_name (self::$sessionName);
            session_start ();
        }
    }
    
    private static function resetTimestamp (){
        $_SESSION['timestamp'] =time ();
    }
}?>