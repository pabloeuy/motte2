<?php
/**
 * Driver to connect to MySql database
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

class mteCnxMySql extends mteCnx {

	 /**
	  * construct()
	  *
	  * @access public
	  * @param 	string 	$hostName
	  * @param 	string 	$userName
	  * @param 	string 	$password
	  * @param 	string 	$baseName
	  * @param 	boolean	$persistent
	  * @param 	boolean	$autoconnect
	  * @return mteCnxMySql
	  */
	function __construct($hostName, $userName, $password, $baseName, $persistent = false, $autoconnect = true) {
		//	Initializing
		parent::__construct();

		// Sets connection values
		$this->setHost($hostName);
		$this->setUser($userName);
		$this->setPass($password);
		$this->setBaseName($baseName);
		$this->setPersistent($persistent);
		if($autoconnect){
			$this->connect();
		}
	}

	 /**
	  * desctruct()
	  *
	  * @access public
	  */
	function __destruct(){
	}

	 /**
	  * connect()
	  * Connects to DB based on data stored in object attributes
	  *
	  * @param boolean $newLink
	  * @access public
	  * @return void
	  */
		public function connect($newLink = false){
		$result = false;
		if($this->checkParams()){
			if(!$this->getPersistent()){
				$aux = @mysql_connect( $this->getHost(),
					$this->getUser(),
					$this->getPass(),
					$newLink);
			}
			else{
				$aux = @mysql_pconnect( $this->getHost(),
					$this->getUser(),
					$this->getPass(),
					$newLink);
			}
			if($aux){
				$result = @mysql_select_db($this->getBaseName(),$aux);
				$this->setIdDatabase($aux);
			}
		}
		// return
		return $result;
	}
	 /**
	  * disconnect()
	  * Disconnect from DB
	  *
	  * @access public
	  * @return bool
	  */
	public function disconnect(){
		$acutalState = true;
		if( !(@mysql_close( $this->getIdDatabase()) ) ){
			$acutalState = false; //Error on closing
		}
		return $acutalState;
	}

	 /**
	  * showTables()
	  * Returns an array with DB table names or false in case of error
	  *
	  * @access public
	  * @return array o boolean
	  */
	public function showTables(){
		return $this->executeSql("SHOW TABLES FROM ".$this->getBaseName());
	}

	 /**
	  * executeSql()
	  * Execute SQL sentence with and returns data on an array or fals in case of error
	  *
	  * @access public
	  * @param 	string 	$sql
	  * @return array o boolean
	  */
	public function executeSql($sql = ''){		
		$query = @mysql_query($sql, $this->getIdDatabase());
		$result = false;
		// Si hay error
		if ($query === false){
			$this->setEventMsg(mysql_error());
			$result = false;
		}
		else{
			if ($query === true){
				$result = true;
			}
			else {
				// Si devolvio un resource
				if (is_resource($query)){
					while ($row = mysql_fetch_assoc($query)){
						$result[] = $row;
					}
				}
			}
		}
		return $result;
	}


	 /**
	 * executeSqlLimit()
	 * Executes SQL sentence with Filters and limit clauses
	 *
	 * @access 	public
	 * @param 	string 	$sql
	 * @param	integer	$nRows
	 * @param	integer	$offset
	 * @return 	array o boolean
	 */
	public function executeSqlLimit($sql = '', $nRows = -1 , $offset = -1){
		// Offset
		$offsetStr = '';
		if ($offset >= 0){
			$offsetStr = "$offset, ";
		}
		if ($nRows < 0){
			$nRows = '18446744073709551615';
		}
		return $this->executeSql($sql." LIMIT ".$offsetStr.$nRows);
	}

	public function getSqlResource($sql = ''){
		return  @mysql_query($sql, $this->getIdDatabase());
	}

	public function getSqlResourceLimit($sql = '', $nRows = -1 , $offset = -1){
		// Offset
		$offsetStr = '';
		if ($offset >= 0){
			$offsetStr = "$offset, ";
		}
		if ($nRows < 0){
			$nRows = '18446744073709551615';
		}
		return $this->getSqlResource($sql." LIMIT ".$offsetStr.$nRows);
	}

	static function fetchRow($query, $row = ''){
		if($row !== ''){
			if(($row >= 0) && ($row < mysql_num_rows($query))){
				mysql_data_seek($query, $row);
				$record = mysql_fetch_assoc($query);
				mysql_data_seek($query, $row); //mysql_fetch_assoc advances one position, we don't want it
			}
			else{
				$record = false;
			}
		}
		else{
			$record = mysql_fetch_assoc($query);
		}
		return $record;
	}

	static function getRecordCount($query){
		return mysql_num_rows($query);
	}


	 /**
	  * getConcat()
	  *
	  * @access public
	  * @param  array	$strings
	  * @return string
	  */
	public function getConcat($strings = ''){
		// parameters
		if (!is_array($strings)){
			$strings = array($strings);
		}
		return 'CONCAT('.implode(',',$strings).')';
	}

	 /**
	  * describeTable()
	  * Describes a DB table's structure
	  *
	  * @access public
	  * @param string 	$tableName
	  * @return array or boolean
	  */
	public function describeTable($tableName = ''){
		return $this->executeSql('SHOW COLUMNS FROM '.$tableName);
	}


	 /**
	 * Returns field names from a DB table
	 *
	 * @access 	public
	 * @param 	string 	$tableName
	 * @return 	array
	 */
	public function getFieldsName($tableName = ''){
		// Get table structure
		$fields = $this->describeTable($tableName);

		// load result
		$return = array();
		if (is_array($fields)){
			foreach($fields as $row){
				$return[] = $row['Field'];
			}
		}
		return $return;
	}

	 /**
	 * Returns key field names from a DB table
	 *
	 * @access public
	 * @param string $tableName
	 * @return array
	 */
	public function getFieldsKeyName($tableName = ''){
		// Get table structure
		$fields = $this->describeTable($tableName);

		// Load data
		$return = array();
		if (is_array($fields)){
			foreach($fields as $row)
			if ($row['Key'] == 'PRI'){
				$return[] = $row['Field'];
			}
		}
		return $return;
	}
}
?>
