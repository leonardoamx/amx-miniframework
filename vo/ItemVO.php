<?php
require_once ('ValueObject.php');

class ItemVO extends ValueObject {
	public $property1 =0;
	public $property2 ='';

	public function __construct ($data){
		if ($data){
			if (isset ($data['property1']))
				$this->property1 =intval ($data['property1']);
			if (isset ($data['property2']))
				$this->property2 =$data['property2'];
		}
	}

}
?>