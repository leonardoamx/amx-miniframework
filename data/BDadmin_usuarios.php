<?php
	/** Clase para acceso de datos
	* Hereda de: LinkSQL
	* @version: 1.0
	* @author: Arturo Leonardo Molina Aguilar (lama_amx AT hotmail DOT com)
	*/
class BDadmin_usuarios extends LinkSQL {
	const PERMISO_MENSAJES =2;
	const PERMISO_USUARIOS =4;

	public function __construct (){
		parent::__construct ("admin_usuarios");
		$this->primaryKey ='id';
		$this->fields =array (
			 array ('private',	$this->primaryKey, '')
			,array ('public',	'nombre')
			,array ('public',	'email')
			,array ('public',	'activo')
			,array ('public',	'permisos')
			,array ('private',	'password', '')
			,array ('private',	'fecha_creacion', 'CURRENT_TIMESTAMP')
		);
		$this->defaultOrder ='id ASC';
	}

	public function getUserLogin ($email, $password){
		$result =array ();
		$records =$this->getRecords ("email=\"$email\" AND password=\"$password\"");
		if (!empty ($records))
			$result =$records[0];
		return $result;
	}

	public function updatePassword ($id, $newPassword){
		return updateField ($id, 'password', $newPassword);
	}

	public function resetPassword ($id){
		$newPassword =$this->generatePassword ();
		updatePassword ($id, sha1($newPassword));
		return $newPassword;
	}

	private function generatePassword($length=9, $strength=4) {
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength & 1) {
			$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($strength & 2) {
			$vowels .= "AEUY";
		}
		if ($strength & 4) {
			$consonants .= '23456789';
		}
		if ($strength & 8) {
			$consonants .= '@#$%';
		}

		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}
}?>