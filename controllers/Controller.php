<?php
	require_once (APPLICATION_LIB.'/data/data_table.php');
	require_once (APPLICATION_LIB.'/vo/ValueObject.php');
    require_once (APPLICATION_LIB.'/vo/Response.php');
	require_once (APPLICATION_LIB.'/controllers/Permissions.php');
	require_once (APPLICATION_LIB.'/controllers/ValidationResult.php');

class Controller {
	public $targetTable;

	public function __construct (){
		$this->targetTable =new data_table ();
	}

	public function getRecords ($where_str=false, $order_str=false, $count=null, $start=0){
		$result =array ();
		$result =$this->targetTable->getRecords ($where_str,$order_str, $count, $start);
		//add custom code
		return $result;
	}

	public function getRecord ($idItem) {
		$validation =new ValidationResult ();
		$itemInfo =new ValueObject ($this->targetTable->getRecord ($idItem));
		$permissions =$this->getPermissions ($itemInfo);

        return new Response ($validation, $itemInfo, $permissions);
	}

	public function insertRecord (ValueObject $itemInfo) {
		$validation =$this->getFieldsValidation ($itemInfo);
        $itemId =0;
		if ($validation->errorCount == 0){
			$itemId =$this->targetTable->insertRecord (array (
				 $itemInfo->properties
			));
			if ($itemId > 0){
				$validation->addInfoMessage ("the item has been added");
            } else {
				$validation->addInfoMessage ("the item couldn't been added");
            }
		}
        $itemResponse =$this->getRecord ($itemId);
        $itemResponse->validation->appendValidation ($validation);
        return $itemResponse;
	}

	public function updateRecord (ValueObject $itemInfo) {
		$validation =new ValidationResult ();
		$permissions =$this->getPermissions ($itemInfo);
		if ($permissions->canEdit){
			$validation =$this->getFieldsValidation ($itemInfo);
			if ($validation->errorCount == 0){
                $this->targetTable->updateField ($itemInfo->id, 'field', $itemInfo->field);
				//place update statements
				$validation->addInfoMessage ("Record updated successfully");
			}
		} else {
            $validation->addErrorMessage ("El usuario actual no tiene permisos de edición");
        }
        $itemResponse =$this->getRecord ($itemInfo->id);
        $itemResponse->validation->appendValidation ($validation);
        return $itemResponse;
	}

	public function deleteRecord ($idItem) {
		$validation =new ValidationResult ();
		$itemInfo =new ValueObject ($this->targetTable->getRecord ($idItem));
		$permissions =$this->getPermissions ($itemInfo);
		if (!$permissions->canDelete){
			$validation->addErrorMessage ("This item only can't be deleted");
		}

		if ($validation->errorCount == 0){
			$this->targetTable->deleteRecord ($idItem);
			$validation->addInfoMessage ("Record deleted successfully");
		}
        return new Response ($validation, $itemInfo, $permissions);
	}

	public function getPermissions (ValueObject $itemInfo) {
		$result =new Permissions ();
		//customize permissions
		return $result;
	}

	public function getFieldsValidation (ValueObject $item){
		$result =new ValidationResult ();
		//customize validations
		return $result;
	}

}
