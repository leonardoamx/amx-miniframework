<?php 
	class Permissions {
		public $canView =false;
		public $canAdd =false;
		public $canEdit =false;
		public $canDelete =false;
		
		public function getXML (){
			$result ='<permissions>';
			foreach ($this as $field=>$value){
				$value =intval($value);
				$result .="<$field>$value</$field>";
			}
			$result .='</permissions>';
			return $result;
		}
	}
?>