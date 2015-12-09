<?php
	require_once (APP_DIR.'lib/data/albumes.php');
	require_once (APP_DIR.'lib/vo/Album.php');
	require_once (APP_DIR.'lib/controllers/Permissions.php');
	require_once (APP_DIR.'lib/controllers/ValidationResult.php');

class Controller {
	public $targetTable;

	public function __construct (){
		$this->targetTable =new albumes ();
	}

	public function getRecords ($where_str=false, $order_str=false, $count=null, $start=0){
		$result =array ();
		$result =$this->targetTable->getRecords ($where_str,$order_str, $count, $start);
		//add custom code
		return $result;
	}

	public function getRecord ($idItem) {
		$validation =new ValidationResult ();
		$itemInfo =new Album ($this->targetTable->getRecord ($idItem));
		$permissions =$this->getPermissions ($itemInfo);

		return array (
			 'validation'	=>$validation
			,'permissions'	=>$permissions
			,'item'			=>$itemInfo
		);
	}

	public function insertRecord (Album $itemInfo) {
		$validation =$this->getFieldsValidation ($itemInfo);
		if ($validation->errorCount == 0){
			$itemInfo->id =$this->targetTable->insertRecord (array (
				$itemInfo->properties //list of fields
			));
			if ($itemInfo->id > 0)
				$validation->addInfoMessage ("Record inserted successfully");
		}
		$permissions =$this->getPermissions ($itemInfo);
		return array (
			 'validation'	=>$validation
			,'permissions'	=>$permissions
			,'item'			=>$itemInfo
		);
	}

	public function updateRecord (Album $itemInfo) {
		$validation =new ValidationResult ();
		$permissions =$this->getPermissions ($itemInfo);
		if ($permissions->canEdit){
			$validation =$this->getFieldsValidation ($itemInfo);
			if ($validation->errorCount == 0){
				$idAlbum =$itemInfo->idAlbum;
				//place update statements
//				$this->targetTable->updateField ($idAlbum, 'idColor', $itemInfo->idColor);
				$validation->addInfoMessage ("Record updated successfully");
			}
		}
		return array (
			 'validation'	=>$validation
			,'permissions'	=>$permissions
			,'item'			=>$itemInfo
		);
	}

	public function deleteRecord ($idAlbum) {
		$validation =new ValidationResult ();
		$itemInfo =new Album ($this->targetTable->getRecord ($idAlbum));
		$permissions =$this->getPermissions ($itemInfo);
		if (!$permissions->canDelete){
			$validation->addErrorMessage ("This album only can't be deleted");
		}

		if ($validation->errorCount == 0){
			$this->targetTable->deleteRecord ($idAlbum);
			$validation->addInfoMessage ("Record deleted successfully");
		}
		return array (
			 'validation' =>$validation
			,'permissions' =>$permissions
			,'item' =>$itemInfo
		);
	}

	public function getPermissions (Album $itemInfo) {
		$result =new Permissions ();
		//customize permissions
		return $result;
	}

	public function getFieldsValidation (Album $item){
		$result =new ValidationResult ();
		//customize validations
		return $result;
	}

}
?>