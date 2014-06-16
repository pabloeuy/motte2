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
include_once(DIR_MOTTE.'/mteCnx.class.php');
if (defined('DB_DRIVER')) {
	switch (DB_DRIVER) {
		case 'mySql':
			include_once(DIR_MOTTE.'/mteCnxMySql.class.php');
			break;
		case 'mySqli':
			include_once(DIR_MOTTE.'/mteCnxMySqli.class.php');
			break;
	}
}
include_once(DIR_MOTTE.'/mteRecordSet.class.php');
include_once(DIR_MOTTE.'/mteDataSql.class.php');
include_once(DIR_MOTTE.'/mteTableSql.class.php');
include_once(DIR_MOTTE.'/mteOrderSql.class.php');
include_once(DIR_MOTTE.'/mteWhereSql.class.php');


class mteModel {
	private static $_instance;
	private $_tables;
	private $_cnx;
	private $_dataSql;
	private $_obj;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return cnx
	 */
	public function __construct() {
		$this->_obj     = array();
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

	public static function get() {
		if (!isset(self::$_instance)) {
			self::$_instance = new mteModel();
		}
		return self::$_instance;
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                C O N E X I O N
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function getCnx($hostName = '', $userName = '', $password = '', $database = '', $port = 3306, $persistent = false, $autoconnect = true, $debug = false, $charset = 'utf8') {
		$hostName    = defined('DB_HOST')?DB_HOST:$hostName;
		$userName    = defined('DB_USER')?DB_USER:$userName;
		$password    = defined('DB_PASS')?DB_PASS:$password;
		$database    = defined('DB_NAME')?DB_NAME:$database;
		$persistent  = defined('DB_PERSISTENT')?DB_PERSISTENT:$persistent;
		$autoconnect = defined('DB_AUTOCONNECT')?DB_AUTOCONNECT:$autoconnect;
		$port        = defined('DB_PORT')?DB_PORT:$port;
		$debug       = defined('DB_DEBUG')?DB_DEBUG:$debug;
		$charset     = defined('DB_CHARSET')?DB_CHARSET:$charset;

		// driver
		switch (DB_DRIVER) {
			case 'mySql':
				if (!($this->_cnx instanceof mteCnxMySql)) {
					$this->_cnx = new mteCnxMySql($hostName, $userName, $password, $database, $persistent, false, $port, $charset);
				}
				break;
			case 'mySqli':
				if (!($this->_cnx instanceof mteCnxMySqli)) {
					$this->_cnx = new mteCnxMySqli($hostName, $userName, $password, $database, $persistent, false, $port, $charset);
				}
				break;
		}
		if ($autoconnect) {
			$this->_cnx->connect(true);
		}
		$this->_cnx->setDebug($debug);

		return $this->_cnx;
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//              M O D E L   O B J E C T
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function getObject($model) {
		if (!array_key_exists('mdl'.strtolower($model), $this->_obj)) {
			$scr = (!defined('DIR_MODEL')?DIR_ROOT.'/model':DIR_MODEL). '/' . strtolower(str_replace('_', '/', $model)) . '.model.php';
			if (is_readable($scr) && is_file($scr)) {
				include_once ($scr);
				$pos = strpos($model, '_');
				if ($pos === false) {
					$objMdl = 'mdl' . ucfirst($model);
				} else {
					$objMdl = 'mdl' . ucfirst(substr($model, $pos + 1));
				}
				$this->_obj['mdl'.strtolower($model)] = new $objMdl(mteModel::get());
			}
			else {
				die(__('Unknown model').' '.$model);
			}
		}
		return $this->_obj['mdl'.strtolower($model)];
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                 D A T A   S Q L
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
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
			}
			else {
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