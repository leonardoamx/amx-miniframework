<?php
	//Requires LinkSQL
	/**
	 * @author Leonardo Molina
	 */
	class ConfigLoader extends LinkSQL {
		const MENU_URL ="menu_url";
		
		public $params;
		
		public function __construct() {
			parent::__construct ("configuration");
			$this->fields =array (
				 array ('public',	'`key`')
				,array ('public',	'`value`')
			);
			$this->primaryKey ='key';
			$this->defaultOrder = '`key` ASC';
			$this->loadParams ();
		}

		public function setParam ($key, $value){
			if ($this->params[$key] != $value) {
				if (!isset ($this->params[$key])) {
					$query = "INSERT INTO configuration (key, value) VALUES (\"$key\", \"$value\")";
				}else {
					$query = "UPDATE configuration SET value=\"$value\" WHERE key=\"$key\"";
				}
				mysql_query ($query);
				$this->loadParams ();
			}
		}

		public function loadParams () {
			$result = array();
			$records =$this->getRecords ();
			foreach ($records as $item) {
				$result[$item['key']] =$item['value'];
			}
			$this->params =$result;
		}

	}
?>