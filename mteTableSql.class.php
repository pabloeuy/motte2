<?php
/**
 * Clase para el manejo de tablas SQL
 *
 * @filesource
 * @package    motte
 * @subpackage model
 * @version    1.0
 * @license    http://opensource.org/licenses/gpl-license.php GPL - GNU Public license
 * @author     Pedro Gauna (pgauna@gmail.com) /
 *             Carlos Gagliardi (carlosgag@gmail.com) /
 *             Braulio Rios (braulioriosf@gmail.com) /
 *             Pablo Erartes (pabloeuy@gmail.com) /
 *             GBoksar/Perro (gustavo@boksar.info)
 * @link       http://motte.codigolibre.net Motte Website
 */
class mteTableSql extends mteDataSql {

	/**
	 * table name
	 *
	 * @var string
	 * @access private
	 */
	private $_tableName;

	/**
	 * schema name
	 *
	 * @var string
	 * @access private
	 */
	private $_schema;

	/**
	 * key fields
	 *
	 * @var array
	 * @access private
	 */
	private $_fieldsKey;

	/**
	 * fields
	 *
	 * @var array
	 * @access private
	 */
	private $_fields;

	/**
	 * labels
	 *
	 * @var array
	 * @access private
	 */
	private $_labels;

	/**
	 * table Foreign Keys
	 *
	 * @var array
	 * @access private
	 */
	private $_fieldsForeign;

    /**
	 * table FROM
	 *
	 * @var string
	 * @access private
	 */
	private $_from;

	/**
	 * Generated calc fields
	 *
	 * @var array
	 * @access private
	 */
	private $_fieldsCalc;

	/**
	 * error
	 *
	 * @var array
	 * @access private
	 */
	private $_errorExec;

	/**
	 * FieldLogicalDelete
	 *
	 * @var string
	 * @access private
	 */
	private $_fieldLogicalDelete;

	/**
	 * FieldLogicalDelete
	 *
	 * @var string
	 * @access private
	 */
	private $_valueLogicalDelete;

	/**
	 * __construct()
	 *
	 * @access public
	 * @param string $tableName
	 * @param resource $engine
	 * @param boolen $autoStructure
	 * @param string $schema
	 * @return mteTableSql
	 */
	function __construct($tableName = '', $engine, $autoStructure = true, $schema = '') {
		// Invoking parent constructor
		parent::__construct($engine);

		// Initialize
		$this->initialize();
		$this->setTableName($tableName);
		$this->setSchemaName($schema);
        $this->setFrom();
		$this->clearErrorExec();

		// Structure
		if ($autoStructure) {
			$this->setFields();
			$this->setFieldsKey();
		}
	}

	/**
	 * __destruct()
	 *
	 * @access public
	 * @return void
	 */
	function __destruct() {
	}

	/**
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 *                   I N T E R N A L   M E T H O D S
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 */
	 /**
	 * Initialize class attributes
	 * @access 	public
	 * @return 	void
	 */
	public function initialize() {
		$this->_fields           = array();
		$this->_fieldsCalc       = array();
		$this->_fieldsForeign    = array();
		$this->_fieldsKey        = array();
		$this->_labels           = array();
		$this->_fieldLogicalDelete = '';
		$this->_valueLogicalDelete = '';
	}

    public function setFrom($from = ''){
        $this->_from = ($from == '')?$this->getTableName():$from;
    }

    public function getFrom(){
        return $this->_from;
    }

	/**
	 * setFieldsKey()
	 * Sets phisical key fields from table
	 *
	 * @access private
	 * @return void
	 */
	public function setFieldsKey($data = '') {
		if ($data == '') {
			if ($this->getTableName() != '') {
				$this->_fieldsKey = $this->getEngine()->getFieldsKeyName($this->getTableName());
			}
		}
		else {
			$this->_fieldsKey = $this->_exlodeParam($data);
		}

	}

	/**
	 * setFields()
	 * Sets phisical field from table
	 *
	 * @access private
	 * @return void
	 */
	public function setFields($data = '') {
		if ($data == '') {
			if ($this->getTableName() != '') {
				$this->_fields = $this->getEngine()->getFieldsName($this->getTableName());
			}
		}
		else {
			$this->_fields = $this->_exlodeParam($data);
		}
	}

	/**
	 * setFieldLogicalDelete()
	 * Sets exp Logical Del field
	 *
	 * @access private
	 * @return void
	 */
	public function setFieldLogicalDelete($exp) {
		$this->_fieldLogicalDelete = $exp;
	}

	/**
	 * getFieldLogicalDelete()
	 * Get exp Logical Del field
	 *
	 * @access private
	 * @return void
	 */
	public function getFieldLogicalDelete() {
		return $this->_fieldLogicalDelete;
	}

	/**
	 * setValueLogicalDelete()
	 * Sets exp Logical Del field
	 *
	 * @access private
	 * @return void
	 */
	public function setValueLogicalDelete($exp) {
		$this->_valueLogicalDelete = $exp;
	}

	/**
	 * getValueLogicalDelete()
	 * Get exp Logical Del field
	 *
	 * @access private
	 * @return void
	 */
	public function getValueLogicalDelete() {
		return $this->_valueLogicalDelete;
	}

	/**
	 * setLabel()
	 * Sets labels field
	 *
	 * @access private
	 * @return void
	 */
	public function setLabel($fieldname, $label) {
		$this->_labels[$fieldname] = $label;
	}

	/**
	 * getLabel()
	 * Get labels field
	 *
	 * @access private
	 * @return void
	 */
	public function getLabel($fieldname) {
		return isset($this->_labels[$fieldname])?$this->_labels[$fieldname]:'';
	}

	/**
	 * getLabels()
	 * Gets labels field
	 *
	 * @access private
	 * @return void
	 */
	public function getLabels() {
		return $this->_labels;
	}

	/**
	 *
	 * Search on every array for the given fieldName
	 *
	 * @access private
	 * @param string $fieldName
	 * @return boolean
	 */
	private function _fieldExist($fieldName) {
		return @in_array($fieldName, $this->getFields()) ||
		@in_array($fieldName, $this->getFieldsCalc()) ||
		@in_array($fieldName, $this->getFieldsForeign());
	}

	/**
	 *
	 * escaping special chars on record
	 *
	 * @param array $record
	 * @return unknown
	 */
	private function _escaped($record) {
		if (is_array($record)) {
			foreach ($record as $key=>$value) {
				$record[$key] = '\''.(get_magic_quotes_gpc() == 1)?addslashes($value):
				$value.'\'';
			}
		}
		return $record;
	}

	/**
	 *
	 * @access private
	 * @param string $param
	 * @return array
	 */
	private function _exlodeParam($param) {
		if (is_array($param)) {
			$result = $param;
		}
		else {
			$result = array ();
			if ($param != '') {
				$param = str_replace(array("\t", "\n", "\r"), '', $param);
//				$param = ereg_replace("\t", "", ereg_replace(" ", "", ereg_replace("\n", "", ereg_replace("\r", "", $param))));
				$result = explode(',', $param);
				if (is_array($result)) {
					foreach ($result as $key=>$element) {
						$result[$key] = str_replace(' ', '', $element);
					}
				}
			}
		}
		return $result;
	}

	/**
	 *
	 * validate record fields against table fields
	 *
	 * @param array $record
	 * @return unknown
	 */
	private function _fieldControl($record) {
		if ((is_array($record)) && (is_array($this->getFields()))) {
			foreach ($this->getFields() as $key=>$fieldName) {
				if (!array_key_exists($fieldName, $record)) {
					$record[$fieldName] = '';
				}
			}
			foreach ($record as $key=>$fieldName) {
				if (!in_array($key, $this->getFields())) {
					unset ($record[$key]);
				}
			}
		}
		return $record;
	}

	/**
	 *
	 * Returns data based on WHERE and ORDER clauses
	 *
	 * @access public
	 * @param string $fieldsToShow
	 * @return string
	 */
	private function _getSql($fieldsToShow = '*', $where = '', $order = '', $distinct = false, $foreignFields = TRUE) {
		return $this->getSql($fieldsToShow, $where, $order, $distinct, $foreignFields);
	}

	public function getSql($fieldsToShow = '*', $where = '', $order = '', $distinct = false, $foreignFields = TRUE) {
		// All fields
		if ($fieldsToShow == '*') {
			$fieldsToShow = implode(',', $this->_fields);

			// Foreign fields
			if ($foreignFields && count($this->_fieldsForeign) > 0) {
				if ($fieldsToShow != '') {
					$fieldsToShow .= ',';
				}
				$fieldsToShow .= implode(',', array_keys($this->_fieldsForeign));
			}

			// Calc fields
			if (count($this->_fieldsCalc) > 0) {
				if ($fieldsToShow != '') {
					$fieldsToShow .= ',';
				}
				$fieldsToShow .= implode(',', array_keys($this->_fieldsCalc));
			}
		}

		// Remove blanks
		$auxFields = explode(',', str_replace(' ', '', $fieldsToShow));

		//auxFields (array)
		$sqlFields = array ();

		foreach ($auxFields as $key=>$value) {
			//add table common fields
			if (in_array(str_replace($this->getTableName().".", '', $value), $this->_fields)) {				
				$sqlFields[$key] = $this->getTableName().".".str_replace($this->getTableName().".", '', $value);
			}
			//add table foreign fields
			if (in_array($value, array_keys($this->_fieldsForeign))) {
				$sqlFields[$key] = ($this->_fieldsForeign[$value]['addPrefix']?$this->_fieldsForeign[$value]['tableF'].".":'').$this->_fieldsForeign[$value]['descF']." AS ".$value;
			}
			//add calc fields
			if (in_array($value, array_keys($this->_fieldsCalc))) {
				$sqlFields[$key] = $this->_fieldsCalc[$value]." AS ".$value;
			}
			// add count fields
			if (strtoupper($value) == 'COUNT(*)') {
				$sqlFields['COUNT(*)'] = 'COUNT(*)';
			}
		}
		$fieldsToShow = implode(',', $sqlFields);

        //FROM AND INNER JOINS
		$query = 'SELECT '.($distinct?'DISTINCT ':'').$fieldsToShow.' FROM '.$this->getFrom();
		$arrayFieldsForeign = $this->getFieldsForeign();
		$inner = array ();
		foreach ($arrayFieldsForeign as $key=>$value) {
			if (!array_key_exists($value['tableF'], $inner)) {
				$inner[$value['tableF']] = " INNER JOIN ".$value['tableF']." ON ".$this->getTableName().".".$value['key']."=".$value['tableF'].".".$value['keyF'];
			}
		}
		$query = $query.implode("\n", $inner);

		// WHERE:
		if ($where != '') {
			// Calc fields
			if (count($this->_fieldsCalc) > 0) {
				foreach ($this->_fieldsCalc as $auxFieldKey => $auxFieldValue) {
				   if(!array_key_exists($auxFieldKey, $sqlFields)){
                     $where = str_replace($auxFieldKey,"($auxFieldValue)", $where);
				   }
				}
			}
			$query .= ' WHERE '.$where;
		}

		// ORDER BY:
		if ($order != '') {
			$query .= ' ORDER BY '.$order;
		}

		//print $query;
		return $query;
	}

	/**
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 *                        P R O P E R T I E S
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 */
	 /**
	 * Sets table name
	 *
	 * @access public
	 * @param string $tb
	 * @return void
	 */
	public function setTableName($tb) {
		$this->_tableName = $tb;
	}

	/**
	 * Devuelve nombre de la tabla
	 *
	 * @access public
	 * @return string
	 */
	public function getTableName() {
		$result = $this->_tableName;
		if ($this->getSchemaName() != '') {
			$result = $this->getSchemaName().'.'.$this->_tableName;
		}
		return ($result);
	}

	/**
	 * Sets table name
	 *
	 * @access public
	 * @param string $tb
	 * @return void
	 */
	public function setSchemaName($value = '') {
		$this->_schema = $value;
	}

	/**
	 * Devuelve nombre de la tabla
	 *
	 * @access public
	 * @return string
	 */
	public function getSchemaName() {
		return ($this->_schema);
	}

	/**
	 * Devuelve el array _fields
	 *
	 * @access public
	 * @return array
	 */
	public function getFields() {
		return $this->_fields;
	}

	/**
	 * Devuelve el array _fieldsKey
	 *
	 * @access public
	 * @return array
	 */
	public function getFieldsKey() {
		return $this->_fieldsKey;
	}

	/**
	 * Devuelve el array _fieldsCalc
	 *
	 * @access public
	 * @return array
	 */
	public function getFieldsCalc() {
		return $this->_fieldsCalc;
	}

	/**
	 * Devuelve el array _fieldsForeign
	 *
	 * @access public
	 * @return array
	 */
	public function getFieldsForeign() {
		return $this->_fieldsForeign;
	}

	/**
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 *                   F I E L D   M A N A G M E N T   M E T H O D S
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 */
	 /**
	 * devuelve where id
	 *
	 * @param array $record
	 * @return string
	 */
	public function getWherePrimaryKeys($record) {
		$where = new mteWhereSQL();
		if (is_array($this->getFieldsKey())) {
			foreach ($this->getFieldsKey() as $fieldName) {
				$where->addAND($fieldName, '=', "'".$record[$fieldName]."'");
			}
		}
		return $where->fetch();
	}

	/**
	 * Agrega campo foraneo a la estructura de la tabla
	 * (sirve para simular FK cuando el SGBD no lo soporta)
	 *
	 * @access public
	 * @param string $fieldName el nombre que se le dara al campo
	 * @param string $fieldKey es el campo foreign de this
	 * @param string $tableForeign es la tabla externa
	 * @param string $fieldKeyForeign es el campo externo
	 * @param string $fieldDescForeign es la descripcion del campo
	 * @return void
	 */
	public function addFieldForeignKey($fieldName, $fieldKey, $tableForeign, $fieldKeyForeign = '', $fieldDescForeign = '', $addPrefix = true) {
		// Si no existe
		if (($fieldName != '') && (!$this->_fieldExist($fieldName))) {
			$this->_fieldsForeign[$fieldName] = array ('key'=>$fieldKey, 'tableF'=>$tableForeign, 'keyF'=>($fieldKeyForeign == '')?$fieldKey:$fieldKeyForeign, 'descF'=>$fieldDescForeign, 'addPrefix'=>$addPrefix);
		}
	}

	/**
	 * Agrega un campo calculado
	 *
	 * @access public
	 * @param string $fieldName
	 * @param string $expresion	Campo calculado
	 * @return void
	 */
	public function addFieldCalcSql($fieldName, $expresion) {
		// Si no existe
		if (($fieldName != '') && (!$this->_fieldExist($fieldName))) {
			$this->_fieldsCalc[$fieldName] = $expresion;
		}
	}

	/**
	 * Agrega un campo calculado al array de campos calculados
	 *
	 * @access public
	 * @param string $fieldName nombre que tendra el campo calculado
	 * @param array	$toConcat array de strings a concatenar
	 * @return void
	 */
	public function addFieldCalcConcat($fieldName = '', $toConcat = '') {
		$this->addFieldCalcSql($fieldName, $this->getEngine()->getConcat($toConcat));
	}

	/**
	 * Carga desde un array los datos de un record
	 *
	 * @access public
	 * @return array
	 */
	public function toRecord($record, $data) {
		if (is_array($record)) {
			foreach ($record as $fieldName=>$value) {
				if ( isset ($data[$fieldName])) {
					$record[$fieldName] = $data[$fieldName];
				}
			}
		}
		return $record;
	}

	/**
	 *
	 *
	 * @access public
	 * @param string $field
	 * @return string
	 */
	public function getFieldName($field) {
		$result = $field;
		if (in_array($field, $this->_fields)) {
			$result = $this->getTableName().".".$field;
		}
		if (array_key_exists($field, $this->_fieldsForeign)) {
			$result = ($this->_fieldsForeign[$field]['addPrefix']?$this->_fieldsForeign[$field]['tableF'].".":'').$this->_fieldsForeign[$field]['descF'];
		}

		// return
		return $result;
	}

	/**
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 *               D A T A   M A N A G M E N T   M E T H O D S
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 */
	 /**
	 * Devuelve un mteRecordSet conteniendo los valores de la columna parametro
	 *
	 * @access public
	 * @param string $fieldsToShow
	 * @param string $where
	 * @param string $order
	 * @return mteRecordSet
	 */
	public function getColumnValues($fieldName = '', $where = '', $order = '', $distinct = false) {
		// sql
		$result = $this->getEngine()->executeSql($this->getSql($fieldName, $where, $order, $distinct));
		if ($result) {
			$recordSet = new mteRecordSet();
			if (is_array($result)) {
				$recordSet->addData($result);
			}
			$recordSet->first();
			// result
			$result = $recordSet;
		}
		return $result;
	}

	/**
	 * Devuelve la primer columna del primer registro segun el orden indicado en el parametro
	 * @access  public
	 * @param  string $fieldName Este parametro contendra solo un campo
	 * @param  string $where
	 * @param  string $order
	 * @return  Object o boolean
	 */
	public function getValue($fieldName = '', $where = '', $order = '') {
		$result = $this->getRecordSet($fieldName, $where, $order, 1, 0, false);
		$result->first();
		$value = '';
		if (is_array($result->record)) {
			$value = array_shift($result->record);
		}
		return $value;
	}

	/**
	 * Devuelve el maximo identificador de la tabla
	 *
	 * @access public
	 * @param string $fieldName si viene vacio toma el primer pk de la tabla,sino retorna el maximo del campo parametro (que se asume es pk)
	 * @return Object o boolean
	 */
	public function lastId($fieldName = '') {
		if ($fieldName == '') {
			$primaryKeys = $this->getFieldsKey();
			$fieldName = $primaryKeys[0];
		}
		$result = $this->getEngine()->executeSql('SELECT MAX('.$fieldName.') FROM '.$this->getTableName());
		if (is_array($result)) {
			return array_shift($result[0]);
		}
		else {
			return false;
		}
	}

	/**
	 * Inserta el nuevo registro y devuelve error o exito asume que los formatos de datos
	 * son correctos, por ejemplo, campo integer necesita un entero como valor (ademas
	 * llama a before insert y after insert)
	 *
	 * @access public
	 * @param array $record
	 * @return string devuelve '' en caso de exito
	 */
	public function insertRecord( & $record) {
		$error = '';
		if ((is_array($record)) && (is_array($this->getFields()))) {
			// agrega campos que no estan el record y escapea
			$record = $this->_escaped($this->_fieldControl($record));

			// ejecuta before insert si es que existe
			if (method_exists($this, 'beforeInsert')) {
				$error = $this->beforeInsert($record);
			}

			if ($error == '') {
				if (is_array($record)) {
					$pairs = array ();
					foreach ($this->getFields() as $field) {
						if($record[$field] == 'NULL'){
							$pairs[] = $record[$field];
						}
						else{
							$pairs[] = "'".$record[$field]."'";
						}
					}

					$result = $this->getEngine()->executeSQL('INSERT INTO '.$this->getTableName().' ('.implode(',', $this->getFields()).
						') VALUES ('.implode(',', $pairs).')');
					if ($result === false) {
						$error = $this->getEngine()->getEventMsg();
					}
					else {
						if (method_exists($this, 'afterInsert')) {
							$error = $this->afterInsert($record);
						}
					}
				}
			}
		}
		else {
			$error = __('INSERT - Not Enough Parameters').' ('.$this->getTableName().')';
		}
		return $error;
	}

	/**
	 * Actualiza el registro y devuelve error o exito (ademas llama a beforeUpdate y afterUpdate)
	 *
	 * @access public
	 * @param  array  $record campos a actualizar
	 * @param  string $where filtro para actualizar la tabla
	 * @return string
	 */
	public function updateRecord( & $record, $where = '') {
		$error = '';

		if ((is_array($record)) && (is_array($this->getFields()))) {
			// escapea datos
			$record = $this->_escaped($record);

			// ejecuta before update si es que existe
			if (method_exists($this, 'beforeUpdate')) {
				$error = $this->beforeUpdate($record);
			}

			if ($error == '') {
				if (is_array($record)) {
					$pairs = array ();
					foreach ($this->getFields() as $field) {
					   if($record[$field] == 'NULL'){
							$pairs[] = $field."=".$record[$field];
						}
						else{
							$pairs[] = $field."='".$record[$field]."'";
						}
					}

					if ($where == '') {
						$where = $this->getWherePrimaryKeys($record);
					}

					$result = $this->getEngine()->executeSQL('UPDATE '.$this->getTableName().' SET '.implode(',', $pairs).' WHERE '.$where);

					if ($result === false) {
						$error = $this->getEngine()->getEventMsg();
					}
					else {
						if (method_exists($this, 'afterUpdate')) {
							$error = $this->afterUpdate($record);
						}
					}
				}
			}
		}
		else {
			$error = __('UPDATE - Not Enough Parameters').' ('.$this->getTableName().')';
		}
		return $error;
	}

	/**
	 * borra el registro y devuelve error o exito
	 * (ademas llama a before delete y after delete)
	 *
	 * @access public
	 * @param string $where
	 * @param array	$record	El record es por si no se le pasa where,
	 * 			el record contiene los registros a borrar
	 * 			por lo que se construye el where a partir de
	 * 			los registros a borrar
	 * @return string
	 */
	public function deleteRecord( & $record, $where = '') {
		$error = '';

		if ((is_array($record)) && (is_array($this->getFields()))) {
			// agrega campos que no estan el record y escapea
			$record = $this->_escaped($this->_fieldControl($record));

			// ejecuta before delete si es que existe
			if (method_exists($this, 'beforeDelete')) {
				$error = $this->beforeDelete($record);
			}

			if ($error == '') {
				if (is_array($record)) {
					if ($where == '') {
						$where = $this->getWherePrimaryKeys($record);
					}

					$result = $this->getEngine()->executeSQL('DELETE FROM '.$this->getTableName().' WHERE '.$where);
					if ($result === false) {
						$error = $this->getEngine()->getEventMsg();
					}
					else {
						if (method_exists($this, 'afterDelete')) {
							$error = $this->afterDelete($record);
						}
					}
				}
			}
		}
		else {
			$error = __('DELETE - Not Enough Parameters').' ('.$this->getTableName().')';
		}
		return $error;
	}

	/**
	 *
	 * @param <type> $where
	 * @return <type>
	 */
	public function deleteRecords($where){
		$error = '';
		if ($where != ''){
			$result = $this->getEngine()->executeSQL('DELETE FROM '.$this->getTableName().' WHERE '.$where);
			if ($result === false) {
				$error = $this->getEngine()->getEventMsg();
			}
		}
		else {
			$error = __('MASSIVE DELETE - Not Enough Parameters').' ('.$this->getTableName().')';
		}
		return $error;
	}


	/**
	 * Retorna el primer registro segun el where y orden recibidos como parametro
	 *
	 * @access public
	 * @param string $order
	 * @param string $where
	 * @return array
	 */
	public function getRecord($where = '', $order = '', $calcFields = true, $foreignFields = TRUE) {

		// Antes de cargar
		if (method_exists($this, 'beforeGetRecord')) {
			$this->beforeGetRecord($record);
		}

		// Cargo record
		$recordSet = $this->getRecordSet('*', $where, $order, 1, 0, false, false, $foreignFields);
		$recordSet->first();
		$record = $recordSet->record;

		// Antes de cargar
		if (method_exists($this, 'afterGetRecord')) {
			$this->afterGetRecord($record);
		}

		// Genero campos calculados
		if ((method_exists($this, 'onCalcFields')) && ($calcFields)) {
			$error = $this->onCalcFields($record);
		}

		// Devuelvo
		return $record;
	}

	/**
	 * Retorna
	 *
	 * @access public
	 * @param array	$columns Esto engloba campos, FK, PK, calculados en caso que * sea '*' y sino es un array de campos
	 * @param string $where
	 * @param string $order
	 * @param number $numRows Cantidad de registros a mostrar
	 * @param  number $offset A partir de que registro se quiere mostrar
	 * @return mteRecordSet
	 */
	public function getRecordSet($columns = '*', $where = '', $order = '', $numRows = -1, $offset = -1, $calcFields = true, $distinct = false, $foreignFields = TRUE) {
		// result
		$recordSetResult = parent::getRecordSet($this->getSql($columns, $where, $order, $distinct, $foreignFields), $numRows, $offset);
		// Genero campos calculados
		if ($calcFields && (method_exists($this, 'onCalcFields'))) {
			$recordSetCalc = new mteRecordSet();
			$recordSetResult->first();
			while (!$recordSetResult->isEOF()) {
				// campo calculado
				$record = $recordSetResult->record;
				$this->onCalcFields($record);
				// Agrego al nuevo recordset
				$recordSetCalc->addRecord($record);
				// next
				$recordSetResult->next();
			}
			$recordSetCalc->first();
			return $recordSetCalc;
		}
		else {
			$recordSetResult->first();
			return $recordSetResult;
		}
	}

	/**
	 * Retorna true si para el where hay registros o false si no
	 *
	 * @access public
	 * @param string $where
	 * @return boolean
	 */
	public function exists($where = '') {
		return ($this->recordCount($where) > 0);
	}

	/**
	 * Devuelve cuantos registros cumplen la condicion
	 *
	 * @access public
	 * @param string $where
	 * @return int
	 */
	public function recordCount($where = '') {
		return $this->getValue("COUNT(*)", $where);
	}

	/**
	 * Devuelve cantidad de paginas para el filtro parametro
	 *
	 * @access public
	 * @param string $where
	 * @param int $numRows Cantidad de filas por cada pagina
	 * @return int Cantidad de paginas
	 */
	public function getTotalPages($numRows = 50, $where = '') {
		if ($numRows == 0) {
			$numRows = MTE_GRID_ROWS;
		}
		$numRecs = $this->recordCount($where);
		$result = (int)($numRecs/$numRows);

		if ($numRecs%$numRows > 0) {
			$result = $result+1;
		}

		return $result == 0?1:$result;
	}

	/**
	 * Devuelve un registro en blanco (adentro pregunta si existe el metodo
	 * onnewrecord en el hijo de table x ej, TPersona)
	 *
	 * @access public
	 * @param
	 * @return  array o string (error)
	 */
	public function getEmptyRecord() {
		// Genero campos
		$record = array ();
		foreach ($this->getFields() as $fieldName) {
			$record[$fieldName] = '';
		}
		if (method_exists($this, 'onNewRecord')) {
			$this->onNewRecord($record);
		}
		return $record;
	}

	/**
	 *
	 * @param string $fieldKey
	 * @param string $fieldDesc
	 * @param string $where
	 * @param string $order
	 * @return array
	 */
	public function getArrayCombo($where = '', $order = '', $fieldKey = '', $fieldDesc = ''){
		$result = array();

		// Field Key
		if ($fieldKey == ''){
			$aux = $this->getFieldsKey();
			$fieldKey = array_shift($aux);
		}

		// Desc
		if ($fieldDesc == ''){
			$aux = $this->getFields();
			$fieldDesc = $aux[1];
		}

		// Order
		if ($order == ''){
			$order = $fieldDesc;
		}

		$recordSet = $this->getRecordSet($fieldKey.','.$fieldDesc, $where, $order);
		$recordSet->first();
		while (!$recordSet->isEOF()){
			$desc = '';
			foreach (explode(',',$fieldDesc) as $field) {
				$desc .= ' '.$recordSet->record[$field];
			}
			$result[$recordSet->record[$fieldKey]] = $desc;
			$recordSet->next();
		}

		return $result;
	}

	/**
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 *                      E R R O R   M A N A G M E N T
	 *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	 */
	 /**
	 * Limpia manejador de errores de ejecucion
	 *
	 * @access public
	 * @param
	 * @return void
	 *
	 */
	public function clearErrorExec() {
		$this->_errorExec = array ();
	}

	/**
	 * Agrega error de ejecucion
	 *
	 * @access public
	 * @param  string $error
	 * @return void
	 *
	 */
	public function addErrorExec($error = '') {
		if ($error != '') {
			$this->_errorExec[] = $error;
		}
	}

	/**
	 * devuelve array errores
	 *
	 * @access public
	 * @return array
	 *
	 */
	public function getErrorExec() {
		return $this->_errorExec;
	}

	/**
	 * Devuelve cantidad de errores
	 *
	 * @access public
	 * @return integer
	 */
	public function countErrorExec() {
		return count($this->_errorExec);
	}

	/**
	 * Parsea errores
	 *
	 * @access public
	 * @param  string $msgIni
	 * @param string $msgEnd
	 * @param  string $glue
	 * @return string
	 *
	 */
	public function parseErrorExec($msgIni = '', $msgEnd = '', $glue = '') {
		return $this->countErrorExec() > 0?"\n".$msgIni.$glue.implode($glue, $this->_errorExec).$glue.$msgEnd:
				'';
	}

	/**
	 * Gets error literal
	 *
	 * @access public
	 * @return string
	 */
	public function getLiteralError() {
		// Literals
		$literal = '';
		if ($this->countErrorExec() > 0) {
			$literal = $this->countErrorExec().' '.__('error(s) found');
		}
		// Returns
		return $literal;
	}

	/**
     * Actualiza de una tabla un array de valores al primer registro que matchea el where
     *
     * @access public
     * @param  array  $values valores a actualizar
     * @param  string $where filtro para actualizar la tabla
     * @return string
     */
    public function updateValues($values, $where = '') {
        $error = '';
        $record=$this->getRecord($where);
        if(empty($record)){
            return __('UPDATE - Where is not matching').' ('.$this->getTableName().')';
        }
        foreach ($values as $fieldName => $value) {
            $record[$fieldName]=$value;
        }
        return $this->updateRecord($record,$where);
    }

    /**
     * Inserta o actualiza dependiendo si se encuentra el registro
     *
     * @access public
     * @param  array  $record array a insertar/actualizar
     * @param  string $where filtro para buscar si ya existe para actualizar, sino se inserta
     * @return string
     */
    public function upsertRecord($record,$where) {
        if($this->exists($where)){
        	return $this->updateRecord($record,$where);
        }else{
        	return $this->insertRecord($record);
        }
    }
}
?>