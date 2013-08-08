<?php
/** Clase abstracta para el manejo de datos en tablas
* @author: Leonardo Molina; lama_amx AT hotmail DOT com
* @version 6.10
*/

/* HOW TO USE
class table_name extends LinkSQL {
	public function __construct (){
		parent::__construct ("table_name");
		$this->primaryKey ='id';
		$this->fields =array (
			 array ('private',	$this->primaryKey, "''")
			,array ('public',	'usuario')
			,array ('private',	'timestamp', 'CURRENT_TIMESTAMP')
			,array ('system',	'(SOME SUBQUERY) AS field') //For advanced function, it's better to make JOIN queries
			,array ('record',	'(SOME SUBQUERY) AS field') //Aditional fields used within getRecord method
		);
//fields are:
//private: when they're not editable. You must provide a default value to use on INSERT query
//public: any other field
//system: some subquery to be returned as a field
//record: some subquery to be returned as a field of a single record
		$this->defaultOrder =$this->primaryKey .' ASC'; //default order unless you especify another one in getRecords method
	}
}
*/
//set_time_limit ( 0);

/* changelog
* @todo: opción de desactivar log
	6.10	 2013-06-04: Changed useFieldsList method to useFields
	6.9.1	 2013-05-20: Little optimization of getField with useFieldsList method.
	6.9	 2013-05-06: Added 'record' type in fieldList
	6.8	 2013-04-16: Added useJoinClause method
	6.7	 2013-04-10: Added useFieldsList method
	6.6	 2013-02-15: Added sql method
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

//	const NO_CASE_TRANSFORM		=false;
//	const CASE_TRANSFORM		=true;
//	const PRESERVE_WHITESPACE	=false;
//	const DELETE_WHITESPACE		=true;

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
	
	private $currentJoinQuery;
	private $currentFieldList;

	/** Constructor
		** @param $table Nombre de la tabla que se manejará por esta instancia
		*/
	public function __construct ($table){
		$this->table =$table;
		$this->fields =array ();
	}

		/** Convierte el resultado de una consulta SQL a una matriz asociativa
		 * @param $consulta String, Consulta SQL a ejecutar
		 * @return resultado, como arreglo asociativo.
		 */
	public static function sql ($consulta, $returnResult=true){
		$consQ =mysql_query (($consulta));
		$error =mysql_error ();
		$logLevel =1;
		if ($error!=''){
			self::logError ($error, 'LinkSQL::sql');
			$logLevel =0;
		}
		self::logError ($consulta, 'query', $logLevel);
		$resultado =array ();
		if ($returnResult){
			if ($consQ){
				while ($consF =mysql_fetch_assoc ($consQ))
					array_push ($resultado, $consF);
			}
		}
		return $resultado;
	}

////////		Public Methods

	public function getRowCount ($where_str=1){
		$result =0;
//		$records =$this->sql ('SELECT COUNT(*) AS total FROM '.$this->table.' WHERE '.$where_str);
		$records =self::sql ('SELECT COUNT(*) AS total FROM '.$this->table.' WHERE '.$where_str);
		if (!empty ($records))
			$result =$records[0]['total'];
		return $result;
	}
	
		/** Define una instrucción SQL que será insertada antes de la instrucción WHERE. Se aplica en la siguiente consulta SELECT y su valor se anula después de correr.
		** @param string $query. Instrucción SQL a insertar
		*/
	public function useJoinClause ($query){
		$this->currentJoinQuery =$query;
	}
	
		/* Define los campos que la siguiente consulta getRecords, getRecordset o getRecord usará. Se usa antes de llamar uno de esos métodos. El ajuste es temporal; se descarta una vez que el método ha sido llamado
		* @param $fieldList: Cadena=''. Lista de campos de la tabla
		*/
	public function useFields ($fieldList){
		$this->currentFieldList =$fieldList;
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
		if (empty ($this->currentFieldList))
			$campos =$this->getAllFields ();
		else {
			$campos =$this->currentFieldList;
			$this->currentFieldList =null;
		}
		$joinQuery ='';
		if (!empty ($this->currentJoinQuery)){
			$joinQuery =$this->currentJoinQuery;
			$this->currentJoinQuery =null;
		}
		$query ="SELECT $campos FROM {$this->table} $joinQuery $where $order $limit";
		switch ($resultFormat){
			case self::SQL_FORMAT:
				$result =mysql_query ($query);
				$this->lastSQL =$query;
				$error =mysql_error();
				$logLevel =1;
				if ($error != ''){
					self::logError (mysql_error (), 'LinkSQL->_getRecords error');
					$logLevel =0;
				}
				self::logError ($query, 'LinkSQL->_getRecords', $logLevel);
				break;
			case self::ARRAY_FORMAT:
				$this->lastSQL =$query;
				$result =self::sql ($query);
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
		$this->useFields ($field);
		$records =$this->getRecords ($where_str, $order_str, $count, $start);
		$result =array ();
		foreach ($records as $row){
			array_push ($result, $row[$field]);
		}
		return $result;
	}

		/** Devuelve un registro de la tabla
		** @param $id: Entero. Id del registro a devolver.
		*/
	public function getRecord ($id){
		if (empty ($this->currentFieldList)){
			$campos =$this->getNameFields ('public|private|system|record');
			$campos =implode (', ', $campos);
			$this->useFields ($campos);
		}
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
		mysql_query ($query);
		$this->lastSQL =$query;
		$error =mysql_error();
		$logLevel =2;
		if ($error != ''){
			self::logError ($error, 'insertRecord error', $logLevel);
			$logLevel =0;
		}else
			self::logError ($this->lastIdInserted (), 'lastIdInserted at '.$this->table, $logLevel);
		self::logError ($query, 'LinkSQL->insertRecord', $logLevel);
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
		mysql_query ($query);
		$logLevel =2;
		if ($error != ''){
			self::logError (mysql_error (), 'insertRecords error');
			$logLevel =0;
		}
		self::logError ($query, 'LinkSQL->insertRecords', $logLevel);
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
		if (is_array ($ids))
			$ids	=implode (',', $ids);
		return $this->deleteRecordsByCondition ("{$this->primaryKey} IN ($ids)");
	}

		/** Borra varios registros de la tabla basado en una condición
		** @param $where_str: Cadena. Condición que deben cumplir los registros que se borrarán.
		*/
	public function deleteRecordsByCondition ($where_str){
		$sql ="DELETE FROM {$this->table} WHERE $where_str";
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
		mysql_query ($sql);
		$this->lastSQL =$sql;
		return $this->validateOperation ();
	}

		/** Devuelve el id del último registro
		*/
	public static function lastIdInserted (){
		return mysql_insert_id ();
	}

	public function getAllFields ($asArray=false){
		$return =$this->getNameFields ('public|private|system');
		return $asArray ? $return : implode (', ', $return);
	}

	public function getTableFields ($asArray=false){
		$return =array_merge (
			$this->getNameFields ('private'),
			$this->getNameFields ('public')
		);
		return $asArray ? $return : implode (', ', $return);
	}

	public function getEditableFields ($asArray=false){
		$return =$this->getNameFields ('public');
		return $asArray ? $return : implode (', ', $return);
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
		$prefix ='';
		if (!empty ($this->currentJoinQuery))
			$prefix =$this->table .'.';
		foreach ($fields as $field){
			array_push ($return, $prefix.$field[1]);
		}
		return $return;
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
	
	public static function logError ($variable, $message='', $level=0){
//		if (isset (Logger::$logEnabled)){
//			Logger::log ($variable, $message, $level);
//		}else{
//			if ($level == 0)
//				error_log ("$message: $variable");
//		}
//		if ($level == 0)
			Logger::log ($variable, $message, $level);
//			error_log ("$message: $variable");
	}
	
		/** @private */
	protected function validateOperation (){
		$result =mysql_error();
		$logLevel =2;
		if ($result != ''){
			self::logError ($result, 'mysql_error');
			$logLevel =0;
		}
		self::logError ($this->lastSQL, 'lastSQL', $logLevel);
		return $result=='' ? true : false;
	}
}?>