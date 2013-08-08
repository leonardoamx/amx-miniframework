<?php
class MailService {
	public $reply	='';
	public $from	='';
	public $to		='';
	public $subject	='';
	public $message	='';
	public $charset	='UTF-8';

	public function send (){
		$cabeceras ='';
		$cabeceras .="MIME-Version: 1.0\r\n";
		$cabeceras .="Content-type: text/html; charset={$this->charset}\r\n";
		$cabeceras .="From: {$this->from}\r\n";
		$cabeceras .="X-Mailer: PHP/".phpversion();
		if ($this->reply != '')
			$cabeceras .="Reply-To: {$this->reply}\r\n";
		$couldSend =@mail (
			$this->to,
			$this->subject,
			$this->message,
			$cabeceras
			);
		return $couldSend;	
	}
}?>