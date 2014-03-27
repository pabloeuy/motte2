<?php
/**
 * mteModel
 *
 * @filesource
 * @package motte
 * @subpackage app
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */

// driver
switch (DB_DRIVER) {
	case 'mySql':
		include_once(DIR_MOTTE.'/mteCnx.class.php');
		include_once(DIR_MOTTE.'/mteCnxMySql.class.php');
		include_once(DIR_MOTTE.'/mteRecordSet.class.php');
		include_once(DIR_MOTTE.'/mteDataSql.class.php');
		include_once(DIR_MOTTE.'/mteTableSql.class.php');
		include_once(DIR_MOTTE.'/mteOrderSql.class.php');
		include_once(DIR_MOTTE.'/mteWhereSql.class.php');
		break;
}

class mteModel {

	private $_tables;
	private $_cnx;
	private $_dataSql;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return cnx
	 */
	public function __construct() {
		$this->_tables  = array();
		$this->_dataSql = NULL;
		$this->_cnx     = NULL;
	}

	/**
	 * Destructor
	 *
	 * @access public
	 */
	public function __destruct() {

	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                C O N E X I O N
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function getCnx() {
		// driver
		switch (DB_DRIVER) {
			case 'mySql':
				if (!($this->_cnx instanceof mteCnxMySql)) {
					$this->_cnx = new mteCnxMySql(DB_HOST, DB_USER, DB_PASS, DB_NAME, false, false);
					$this->_cnx->connect(true);
					$this->_cnx->setDebug(false);
					mysql_set_charset('utf8', $this->_cnx->getIdDatabase());
				}
				break;
			}
			return $this->_cnx;
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                 D A T A   S Q L
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	/**
	 * get object dataSql
	 *
	 * @return mteDataSql
	 */
	public function getDataSql() {
		if (!($this->_dataSql instanceof mteDataSql)) {
			$this->_dataSql = new mteDataSql($this->getCnx());
		}
		return $this->_dataSql;
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                    T A B L E S
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public function getTable($name, $prefix = 'tbl_') {
		if (isset($this->_tables[$name]) && ($this->_tables[$name] instanceof mteTableSql)) {
			$tableObj = $this->_tables[$name];
		}
		else {
			$file = (defined('DIR_MODEL_TABLES')?DIR_MODEL_TABLES:DIR_MODEL) . '/' . $name . '.table.php';
			if (is_readable($file)) {
				include_once($file);
				$objName = $prefix . $name;
				$tableObj = new $objName($this->getCnx());
			} else {
				$tableObj = new mteTableSql($name, $this->getCnx(), true);
			}
			$this->_tables[$name] = $tableObj;
		}
		return $this->_tables[$name];
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//               T R A N S A C T I O N
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function startTransaction() {
		$this->getCnx()->executeSql('SET AUTOCOMMIT=0');
		$this->getCnx()->executeSql('START TRANSACTION');
	}

	public function commit() {
		$this->getCnx()->executeSql('COMMIT');
	}

	public function rollback() {
		$this->getCnx()->executeSql('ROLLBACK');
	}
}
?>