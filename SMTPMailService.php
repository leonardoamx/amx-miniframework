<?php
require_once "Mail.php";

class SMTPMailService {
	public $reply	='';
	public $from	='';
	public $to		='';
	public $subject	='';
	public $message	='';

	public function __construct (){
	}

	public function send ($host, $port, $username, $password){
		$result =false;
		$smtp = Mail::factory('smtp',
			array (
				'host' => $host,
				'port' => $port,
				'auth' => true,
				'username' => $username,
				'password' => $password
			)
		);

		$cabeceras =array (
			 'From' =>$this->from
			,'To' =>$this->to
			,'Subject' =>$this->subject

			,'MIME-Version' =>'1.0'
			,'Content-type' =>'text/html; charset=UTF-8'
		);
		if ($this->reply != '')
			$cabeceras['Reply-To'] =$this->reply;

		$mail =$smtp->send($this->to, $cabeceras, $this->message);

		if (PEAR::isError($mail)) {
			error_log ($mail->getMessage());
		} else {
			$result =true;
		}

		return $result;	
	}
}?>