<?php require_once ('_php/amx/Utils.php'); require_once ('_php/sqlconfig.php');
/* HOW TO USE
class BDadmin_usuarios extends LinkSQL {
	public function __construct (){
		parent::__construct ("table_name");
		$this->primaryKey ='id';
		$this->fields =array (
			 array ('private',	$this->primaryKey, "''")
			,array ('public',	'usuario')
			,array ('private',	'timestamp', 'CURRENT_TIMESTAMP')
			,array ('system',	'(SOME SUBQUERY) AS field')
		);
//fields are:
//private: when they're not editable. You must provide a default value to use on INSERT query
//public: any other field
//system: some subquery to be returned as a field
		$this->defaultOrder ='id ASC'; //default order unless you especify another one in getRecords method
	}
}
*/
set_time_limit ( 0);
/** Clase abstracta para el manejo de datos en tablas
* @author: Leonardo Molina; lama_amx AT hotmail DOT com
* @version 6.5
*/
/* changelog
* @todo: opción de desactivar log
	6.5	 2012-10-04: _getRecords method changed. Now it used $order_str param if defined; otherwise $defaultOrder is used.
	6.4	 2012-09-27: Added getRowCount method
	6.3	 2011-06-15: Se rehabilita la función utf8_decode en insertRecord / insertRecords
	6.2	 2011-06-07: Se implementa la propiedad encoding en getRecords; se dehabilita la función utf8_decode en insertRecord / insertRecords
	6.1	 2011-06-07: insertRecords devuelve el número de registros insertados
	6.0	 2011-05-26: Revisión
	5.10:	Se agrega la propiedad pública $lastSQL. Contiene la sentencia SQL de la última operación realizada
	5.9:	Se agrega la propiedad pública $primaryKey para indicar la llave primaria de la tabla
	5.8:	Se cambia propiedad $encoding de pública a estática
	5.7:	Se agregan algunas comprobaciones para evitar errores en tiempo de ejecución
	5.6. 2011-05-25:	Se modifica el método insertRecords
	5.5. 2011-01-14:	El método validateOperation ahora escribe en log el error sql si hubo alguno
	5.4. 2011-01-12:	Se Agrega método insertRecords
	5.3. 2011-01-10:	Se agrega opción de indicar el nombre de la llave primaria
	5.2. 2011-01-07:	Se modifica el método sql
	5.1:	Se agrega la función deleteRecordsByCondition
	5.0:	Se elimina la propiedad $resultFormat. El método getRecords devuelve un arreglo asociativo y el método getRecordset, un recurso MySQL
	4.7:	Se agrega el método getField
	4.6:	Se agrega el método interno getRecords
	4.5:	Se sustituye variable $returnSQLResult por $resultFormat
			En método getRecordset, se agrega variable $toggleFormat
	4.3: getRecord method fixed
	4.2: lastIdInserted static method added
	4.1: $defaultOrder property added
*/
abstract class LinkSQL {
	const UTF8 ='utf8';
	const HTML ='html';
	const NONE ='none';
	const SQL_FORMAT ='sql';
	const ARRAY_FORMAT ='array';

	/** forma en que se devolverán los campos de textos
	* @default LinkSQL::NONE
	*/
	public static $encoding =self::NONE;
		/** Nombre de la tabla */
	public $table;
		/** definicion de campos de la tabla
		** @code: $instance->fields =array (	array (fieldName, class, defaultValue), ...	);
		fieldName: nombre del campo en la tabla
		class: tipo de campo (public, private, system)
		*/
	public $fields;
		/** nombre de la llave primaria */
	public $primaryKey ='id';
		/** Orden que se usará por defaul en la función getRecordset */
	public $defaultOrder ='';
	/** última sentencia SQL ejecutada */
	public $lastSQL;

	/** Constructor
		** @param $table Nombre de la tabla que se manejará por esta instancia
		*/
	public function __construct ($table){
		$this->table =$table;
		$this->fields =array ();
	}

////////		Public Methods

	public function getRowCount ($where_str=1){
		$result =0;
		$records =$this->sql ('SELECT COUNT(*) AS total FROM '.$this->table.' WHERE '.$where_str);
		if (!empty ($records))
			$result =$records[0]['total'];
		return $result;
	}
	
		/** Devuelve los registros de la tabla
		** @param $where_str: Cadena=''. Condición para filtrar resultados.
		** @param $order_str: Cadena=''. Campo sobre el que se ordenarán los registros.
		** @param $count: Entero =false . Número de registros a devolver. Si es false, toda la tabla
		** @param $start: Entero =0. Indica a partir de qué registros se devuelven datos, por default 0.
		*/
	public function getRecordset ($where_str=false, $order_str=false, $count=false, $start=0){
		return $this->_getRecords ($where_str, $order_str, $count, $start, self::SQL_FORMAT);
	}
		/** Devuelve los registros de la tabla como arreglo asociativo, independientemente del valor de la propiedad resultFormat
		** Todos los parámetros son como en getRecordset
		** @see getRecord
		*/
	public function getRecords ($where_str=false, $order_str=false, $count=false, $start=0){
		return $this->_getRecords ($where_str, $order_str, $count, $start, self::ARRAY_FORMAT);
	}
	private function _getRecords ($where_str=false, $order_str=false, $count=false, $start=0, $resultFormat){
		$where =$where_str ? "WHERE $where_str" : "";
		$order ='';
		if ($this->defaultOrder != '')
			$order ="ORDER BY {$this->defaultOrder}";
		$order =$order_str ? "ORDER BY $order_str" : $order;
		$limit = $count ? "LIMIT $start, $count" : "";
		$campos =$this->getAllFields ();
		$query ="SELECT $campos FROM {$this->table} $where $order $limit";
		switch ($resultFormat){
			case self::SQL_FORMAT:
				$result =mysql_query ($query);
				$this->lastSQL =$query;
				Utils::log ($query, 'LinkSQL->_getRecords', 1);
				Utils::log (mysql_error (), 'LinkSQL->_getRecords error', 1);
				break;
			case self::ARRAY_FORMAT:
				$result =$this->sql ($query);
				break;
		}

		foreach ($result as $k=>$record){
			foreach ($record as $col=>$val){
				switch (self::$encoding) {
					case self::UTF8:
						$record[$col] =utf8_encode ($val);
					break;
					case self::HTML:
						$record[$col] =utf8_decode ($val);
					break;
				}
			}
			$result[$k] =$record;
		}
		return $result;
	}

		/** Devuelve la columna indicada de los registros de la tabla como arreglo asociativo
		** @param $field:Cadena. Nombre de la columna a devolver
		** Los demás parámetros son como en getRecordset
		** @see getRecord
		*/
	public function getField ($field, $where_str=false, $order_str=false, $count=false, $start=0){
		$records =$this->getRecords ($where_str, $order_str, $count, $start);
		$result =Utils::extractField ($records, $field);
		return $result;
	}

		/** Devuelve un registro de la tabla
		** @param $id: Entero. Id del registro a devolver.
		*/
	public function getRecord ($id){
		$return =array ();
		$id =intval ($id);
		if ($id){
			$return =$this->getRecords ("{$this->primaryKey}=$id", false, 1, 0);
			$return =$return[0];
		}
		return $return;
	}

		/** Inserta un registro a la tabla
		** @param $data: Matriz con los valores a insertar. Deben estar declarados en el mismo orden en que los campos están definidos en la propiedad $fields
		*/
	public function insertRecord ($data){
		$campos =$this->getTableFields ();
		$sysData =$this->getDefaultValues ();
		foreach ($data as $k=>$value)
			$data[$k] =utf8_decode($value);
		$data =implode ("', '", $data);
		$query ="INSERT INTO {$this->table} ($campos) VALUES ($sysData, '$data')";
		if (empty ($sysData))
			$query ="INSERT INTO {$this->table} ($campos) VALUES ('$data')";
Utils::log ($query, 'LinkSQL->insertRecord', 2);
		mysql_query ($query);
		$this->lastSQL =$query;
Utils::log ($this->lastIdInserted (), 'lastIdInserted', 2);
Utils::log (mysql_error (), 'insertRecord error', 2);
		return $this->lastIdInserted ();
	}

		/** Inserta varios registros a la tabla
		** @param $data: Matriz de dos dimensiones con los valores a insertar. Deben estar declarados en el mismo orden en que los campos están definidos en la propiedad $fields
			[
				 [field1, field2, field3, field4, ...]
				,[field1, field2, field3, field4, ...]
				,...
			]
		*/
	public function insertRecords ($listData){
		$campos =$this->getTableFields ();
		$sysData =$this->getDefaultValues ();

		$records =array ();
		foreach ($listData as $data){
			foreach ($data as $k=>$value)
				$data[$k] =utf8_decode($value);
			$data =implode ('", "', $data);
			if (empty ($sysData))
				array_push ($records, "(\"$data\")");
			else
				array_push ($records, "($sysData, \"$data\")");
		}
		$records =implode (', ', $records);
		$query ="INSERT INTO {$this->table} ($campos) VALUES $records";
Utils::log ($query, 'insertRecords', 2);
		mysql_query ($query);
Utils::log (mysql_affected_rows (), 'mysql_affected_rows', 2);
		return mysql_affected_rows();
	}

		/** Actualiza registros de una tabla
		** @param $where_str: Cadena. Condición a cumpir por los registros que se actualizarán.
		** @param $data: Matriz con los valores. Deben estar declarados en el mismo orden en que los campos están definidos en la propiedad $fields
		**/
	public function updateRecords ($where_str, $data){
		$campos =$this->getEditableFields (true);
		$datos =array ();
		foreach ($data as $k=>$value)
			$data[$k] =utf8_decode($value);
		foreach ($campos as $ind => $campo){
			$current_data =$data[$ind];
			array_push ($datos, "$campo=\"$current_data\"");
		}
		$datos =implode (", ", $datos);
		$sql ="UPDATE {$this->table} SET $datos WHERE $where_str";
Utils::log ($sql, 'updateRecords', 2);
		mysql_query ($sql);
		$this->lastSQL =$sql;
		return $this->validateOperation ();
	}

		/** Actualiza un registro a la tabla
		** @param $id: Id del registro a actualizar
		** @param $data: Matriz con los valores. Deben estar declarados en el mismo orden en que los campos están definidos en la propiedad $fields
		**/
	public function updateRecord ($id, $data){
		$id =intval ($id);
		if ($id){
			return $this->updateRecords ("{$this->primaryKey}=$id", $data);
		}
	}

		/** Borra un registro de la tabla
		** @param $id: Id del registro a borrar
		*/
	public function deleteRecord ($id){
		return $this->deleteRecordsByCondition ("{$this->primaryKey}=$id");
	}

		/** Borra varios registros de la tabla
		** @param $ids: Arreglo de indices a eliminar
		*/
	public function deleteRecords ($ids){
		$ids	=implode (',', $ids);
		return $this->deleteRecordsByCondition ("{$this->primaryKey} IN ($ids)");
	}

		/** Borra varios registros de la tabla basado en una condición
		** @param $where_str: Cadena. Condición que deben cumplir los registros que se borrarán.
		*/
	public function deleteRecordsByCondition ($where_str){
		$sql ="DELETE FROM {$this->table} WHERE $where_str";
Utils::log ($sql, 'deleteRecordsByCondition', 2);
		mysql_query ($sql);
		$this->lastSQL =$sql;
		return $this->validateOperation ();
	}

		/** Para uno o varios registros de la tabla, cambia el valor de un campo
		** @param $ids: Arreglo de indices donde aplicar el cambio o id del registro (entero)
		** @param $field: Texto. Nombre del campo a cambiar
		** @param $value: Texto. Nuevo valor del campo
		*/
	public function updateField ($ids, $field, $value){
			$value =utf8_decode ($value);
			if (is_array ($ids))
				$ids =implode (',', $ids);
			$sql	="UPDATE {$this->table} SET $field=\"$value\" WHERE {$this->primaryKey} IN ($ids)";
Utils::log ($sql, 'sql', 2);
			mysql_query ($sql);
			$this->lastSQL =$sql;
			return $this->validateOperation ();
	}

		/** Devuelve el id del último registro
		*/
	public static function lastIdInserted (){
		return mysql_insert_id ();
	}

////////		Private Methods

		/** @private */
	private function getFieldsByType ($type=''){
		$return =array ();
		$types =explode ('|', $type);
		foreach ($this->fields as $field){
			$includeField =false;
			foreach ($types as $t){
				if ($field[0] == $t){
					array_push ($return, $field);
				}
			}
		}
		return $return;
	}
		/** @private */
	private function getNameFields ($type){
		$return =array ();
		$fields =$this->getFieldsByType ($type);
		foreach ($fields as $field){
			array_push ($return, $field[1]);
		}
		return $return;
	}
		/** @private */
	private function getAllFields ($asArray=false){
		$return =$this->getNameFields ('public|private|system');
		return $asArray ? $return : implode (', ', $return);
	}
		/** @private */
	private function getTableFields ($asArray=false){
		$return =array_merge (
			$this->getNameFields ('private'),
			$this->getNameFields ('public')
		);
		return $asArray ? $return : implode (', ', $return);
	}
		/** @private */
	private function getEditableFields ($asArray=false){
		$return =$this->getNameFields ('public');
		return $asArray ? $return : implode (', ', $return);
	}
		/** @private */
	private function getDefaultValues ($asArray=false){
		$return =array ();
		$fields =$this->getFieldsByType ('private');
		foreach ($fields as $field){
			$result ='';
			if (isset ($field[2]))
				$result =$field[2];
			if (empty($result))
				$result ="''";
			array_push ($return, $result);
		}
		return $asArray ? $return : implode (', ', $return);
	}
		/** @private */
	protected function validateOperation (){
		$result =mysql_error();
		if ($result != '')
			Utils::log ($result, 'validateOperation', 0);
		return $result=='' ? true : false;
	}
		/** @private */
	protected function sql ($consulta){
		$this->lastSQL =$consulta;
		return Utils::sql ($consulta);
	}
}?>