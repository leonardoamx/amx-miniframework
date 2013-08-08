<?php
/** Basic message managment
 * v0.1
 * 
 * Changelog:
 * 0.1 2012.10.08 Alpha version
 * */

class MessageManager {
	const INFO ='info';
	const ERROR ='error';
	const WARNING ='warning';

	static public $template ='
ini[
	<div class="amx_messages">
		<ul>
]ini
item[
			<li class="message {class}"><span>{message}</span></li>
]item
end[
		</ul>
	</div>
]end
';

	static public function addMessage ($message, $type){
		if (!isset ($_SESSION['amxMessages']))
			$_SESSION['amxMessages'] =array ();
		array_push ($_SESSION['amxMessages'], array ('class'=>$type, 'message'=>$message));
	}
	
	static public function printMessages ($flush=true){
		if (!empty ($_SESSION['amxMessages'])){
			$tplMessages =new Template ();
			$tplMessages->source =self::$template;
			$tplMessages->addRegion ('ini');
			foreach ($_SESSION['amxMessages'] as $message){
				$tplMessages->addRegion ('item', $message);
			}
			$tplMessages->addRegion ('end');
			$tplMessages->printResult ();
		}
		if ($flush)
			self::flush();
	}
	
	static public function flush (){
		if (isset ($_SESSION))
			unset ($_SESSION['amxMessages']);
	}
}
?>