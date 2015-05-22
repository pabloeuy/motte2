<?php
/**
 * Clase para el manejo de tablas SQL
 *
 * @filesource
 * @package    motte
 * @version    2.5
 * @license    http://opensource.org/licenses/gpl-license.php GPL - GNU Public license
 * @author     Pedro Gauna (pgauna@gmail.com) /
 *             Carlos Gagliardi (carlosgag@gmail.com) /
 *             Braulio Rios (braulioriosf@gmail.com) /
 *             Pablo Erartes (pabloeuy@gmail.com) /
 *             GBoksar/Perro (gustavo@boksar.info)
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
				$strFK = $this->_fieldsForeign[$value]['addPrefix']?$this->_fieldsForeign[$value]['tableF'].".":'';
				$sqlFields[$key] = $strFK . $this->_fieldsForeign[$value]['descF'] . " AS " . $value;
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
				$inner[$value['tableF']] = " INNER JOIN ".$value['tableF'] . " ON " . $this->getTableName() . "." .
											$value['key'] . "=" . $value['tableF'] . "." . $value['keyF'];
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
	 * Returns the table's name
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
	 * Returns the schema's name
	 *
	 * @access public
	 * @return string
	 */
	public function getSchemaName() {
		return ($this->_schema);
	}

	/**
	 * Returns the table's fields
	 *
	 * @access public
	 * @return array
	 */
	public function getFields() {
		return $this->_fields;
	}

	/**
	 * Returns the table's key fields
	 *
	 * @access public
	 * @return array
	 */
	public function getFieldsKey() {
		return $this->_fieldsKey;
	}

	/**
	 * Returns the table's calc fields
	 *
	 * @access public
	 * @return array
	 */
	public function getFieldsCalc() {
		return $this->_fieldsCalc;
	}

	/**
	 * Returns the table's foreign key fields
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
	 * From a record returns the sql where with the primary key
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
	 * Make foreign field to the table structure
	 * (used to simulate FK when the DBMS does not support it)
	 *
	 * @access public
	 * @param string $fieldName the name that will be given to the field
	 * @param string $fieldKey the foreign field
	 * @param string $tableForeign external table
	 * @param string $fieldKeyForeign external field
	 * @param string $fieldDescForeign the field description
	 * @return void
	 */
	public function addFieldForeignKey($fieldName, $fieldKey, $tableForeign, $fieldKeyForeign = '',
		                               $fieldDescForeign = '', $addPrefix = true) {
		// Si no existe
		if (($fieldName != '') && (!$this->_fieldExist($fieldName))) {
			$fk = array (
				'key'      => $fieldKey,
				'tableF'   => $tableForeign,
				'keyF'     => ($fieldKeyForeign == '')?$fieldKey:$fieldKeyForeign,
				'descF'    => $fieldDescForeign,
				'addPrefix'=> $addPrefix
			);
			$this->_fieldsForeign[$fieldName] = $fk;
		}
	}

	/**
	 * Add a calc field
	 *
	 * @access public
	 * @param string $fieldName
	 * @param string $expresion	calc field
	 * @return void
	 */
	public function addFieldCalcSql($fieldName, $expresion) {
		// if doesn't exists
		if (($fieldName != '') && (!$this->_fieldExist($fieldName))) {
			$this->_fieldsCalc[$fieldName] = $expresion;
		}
	}

	/**
	 * Add a calculated field to the array of calculated fields
	 *
	 * @access public
	 * @param string $fieldName name that will have the calculated field
	 * @param array	$toConcat array of strings to concatenate
	 * @return void
	 */
	public function addFieldCalcConcat($fieldName = '', $toConcat = '') {
		$this->addFieldCalcSql($fieldName, $this->getEngine()->getConcat($toConcat));
	}

	/**
	 * Load data from an array the data of a record
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
			$result = ($this->_fieldsForeign[$field]['addPrefix'] ? $this->_fieldsForeign[$field]['tableF'].".":'') .
						$this->_fieldsForeign[$field]['descF'];
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
	 * Return a mteRecordSet containing the parameter values ​​of column
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
	 * Return the first column of the first record after the order indicated in the parameter
	 * @access  public
	 * @param  string $fieldName This parameter will contain only one field
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
	 * Returns the max table's identifier
	 *
	 * @access public
	 * @param string $fieldName if it comes empty pk takes the first table's pk,
	 *                          but returns the max of the parameter field (which is assumed to be pk)
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
	 * Insert the new record and returns error or success, assumes that the data formats
	 * are correct, for example, the integer field requires an integer value.
	 * Also calls before Insert and after Insert.
	 *
	 * @access public
	 * @param array $record
	 * @return string returns '' if success
	 */
	public function insertRecord( & $record) {
		$error = '';
		if ((is_array($record)) && (is_array($this->getFields()))) {
			// add fields that are not on record and escape
			$record = $this->_escaped($this->_fieldControl($record));

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
					$sqlInsert = 'INSERT INTO '.$this->getTableName().' ('.implode(',', $this->getFields()) .
								 ') VALUES ('.implode(',', $pairs).')';
					$result = $this->getEngine()->executeSQL($sqlInsert);
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
	 * Updates the record and returns error or success
	 * Also calls before Update and after Update.
	 *
	 * @access public
	 * @param  array  $record fields to update
	 * @param  string $where filter to update the table
	 * @return string returns '' if success
	 */
	public function updateRecord( & $record, $where = '') {
		$error = '';

		if ((is_array($record)) && (is_array($this->getFields()))) {
			// escape data
			$record = $this->_escaped($record);

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
     * Actualiza de una tabla un array de valores al primer registro que matchea el where
     *
     * @access public
     * @param  array  $values valores a actualizar
     * @param  string $where filtro para actualizar la tabla
	 * @return string returns '' if success
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
	 * @return string returns '' if success
     */
    public function upsertRecord($record, $where) {
        if($this->exists($where)){
        	return $this->updateRecord($record,$where);
        }else{
        	return $this->insertRecord($record);
        }
    }


	/**
	 * Delete the record and returns error or success
	 * Also calls before Delete and after Delete.
	 *
	 * @access public
	 * @param string $where
	 * @param array	$record	The record is if haven't where,
	 * 			            the record contains the records to be deleted
	 *                		so the where is constructed from the deleting record
	 * @return string returns '' if success
	 */


	public function deleteRecord( & $record, $where = '') {
		$error = '';

		if ((is_array($record)) && (is_array($this->getFields()))) {
			// add fields that are not on record and escape
			$record = $this->_escaped($this->_fieldControl($record));

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
	 * Deletes records by a sql where and returns error or success
	 * @param string $where
	 * @return string returns '' if success
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
	 * Returns the first record according to the where and the order received as parameter
	 *
	 * @access public
	 * @param string $order
	 * @param string $where
	 * @return array
	 */
	public function getRecord($where = '', $order = '', $calcFields = true, $foreignFields = TRUE) {

		// Before load
		if (method_exists($this, 'beforeGetRecord')) {
			$this->beforeGetRecord($record);
		}

		// Load record
		$recordSet = $this->getRecordSet('*', $where, $order, 1, 0, false, false, $foreignFields);
		$recordSet->first();
		$record = $recordSet->record;

		// Before load
		if (method_exists($this, 'afterGetRecord')) {
			$this->afterGetRecord($record);
		}

		// Generate calc fields
		if ((method_exists($this, 'onCalcFields')) && ($calcFields)) {
			$error = $this->onCalcFields($record);
		}

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
	 * Return true if in the where have records or false if not
	 *
	 * @access public
	 * @param string $where
	 * @return boolean
	 */
	public function exists($where = '') {
		return ($this->recordCount($where) > 0);
	}

	/**
	 * Return the number of records according the where
	 *
	 * @access public
	 * @param string $where
	 * @return int
	 */
	public function recordCount($where = '') {
		return $this->getValue("COUNT(*)", $where);
	}

	/**
	 * Returns number of pages for the filter parameter
	 *
	 * @access public
	 * @param string $where
	 * @param int $numRows Number of rows per page
	 * @return int Page's count
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
	 * Return an emtpy record
	 *
	 * @access public
	 * @param
	 * @return  array o string (error)
	 */
	public function getEmptyRecord() {
		// Generate fields
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
	 * Generate an array with key and label from a record
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
	 * Clean error handler execution
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
	 * Make execution error
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
	 * Return error's array
	 *
	 * @access public
	 * @return array
	 *
	 */
	public function getErrorExec() {
		return $this->_errorExec;
	}

	/**
	 * Return error's count
	 *
	 * @access public
	 * @return integer
	 */
	public function countErrorExec() {
		return count($this->_errorExec);
	}

	/**
	 * Parsing errors
	 *
	 * @access public
	 * @param  string $msgIni
	 * @param string $msgEnd
	 * @param  string $glue
	 * @return string
	 *
	 */
	public function parseErrorExec($msgIni = '', $msgEnd = '', $glue = '') {
		return $this->countErrorExec() > 0 ?
						"\n".$msgIni.$glue.implode($glue, $this->_errorExec).$glue.$msgEnd : '' ;
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

		return $literal;
	}

}
?>