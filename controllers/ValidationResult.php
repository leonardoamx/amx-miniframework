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
        $this->addMessage ($text, self::ERROR);
		$this->errorCount++;
		$this->issuesCount++;
	}

	public function addWarningMessage ($text){
        $this->addMessage ($text, self::WARNING);
		$this->warningCount++;
		$this->issuesCount++;
	}

	public function addInfoMessage ($text){
        $this->addMessage ($text, self::INFO);
	}

	public function appendValidation (ValidationResult $validation){
        foreach ($validation->messages as $item){
            if ($item[1] == self::ERROR){
                $this->addErrorMessage ($item[0]);
            }
            if ($item[1] == self::WARNING){
                $this->addWarningMessage ($item[0]);
            }
            if ($item[1] == self::INFO){
                $this->addInfoMessage ($item[0]);
            }
        }
	}

	private function addMessage ($text, $type){
        // Insert only unique messages
        $shouldAdd =true;
        foreach ($this->messages as $item){
            if ($text == $item[0]) {
                $shouldAdd =false;
                break;
            }
        }
        if ($shouldAdd){
            array_push ($this->messages, array ($text, $type));
        }
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

}
