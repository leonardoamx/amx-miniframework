<?php
class ValidationResult {
	const INFO ='info';
	const ERROR ='error';
	const WARNING ='warning';

	public $warningCount	=0;
	public $errorCount		=0;
	public $issuesCount		=0;
	public $messages;

	public function  __construct (){
		$this->messages =array ();
	}

	public function addErrorMessage ($text){
		array_push ($this->messages, array ($text, self::ERROR));
		$this->errorCount++;
		$this->issuesCount++;
	}

	public function addWarningMessage ($text){
		array_push ($this->messages, array ($text, self::WARNING));
		$this->warningCount++;
		$this->issuesCount++;
	}

	public function addInfoMessage ($text){
		array_push ($this->messages, array ($text, self::INFO));
	}

	public function getXML (){
		$result =<<<EOT
<validation issues="{$this->issuesCount}" errors="{$this->errorCount}" warnings="{$this->warningCount}">
EOT;
		foreach ($this->messages as $item){
			$result .=<<<EOT
				<message type="{$item[1]}">
					<![CDATA[{$item[0]}]]>
				</message>
EOT;
		}
		$result .='</validation>';
		return $result;
	}

	public function getJSON (){
	
		$messagesList =array ();
		foreach ($this->messages as $item){
			array_push ($messagesList, '{"type":"'.$item[1].'", "message":"'.$item[0].'"}');
		}
		$messages =implode (',', $messagesList);
		$result =<<<EOT
			"validation":{
				 "issues":{$this->issuesCount}
				,"errors":{$this->errorCount}
				,"warnings":{$this->warningCount}
				,"messages":[
					$messages
				]
			}
EOT;
		return $result;
	}

	private function addMessage ($text, $type){
		array_push ($this->messages, array ($text, $type));
	}

}
?>