<?php
/**
 * Driver to connect to MySql i database
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
 * @link       http://motte.codigolibre.net Motte Website
 */

class mteCnxMySqli extends mteCnx {

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
	function __construct($hostName, $userName, $password, $baseName, $persistent = false, $autoconnect = true, $port = 3306, $charset = 'utf8') {
		//	Initializing
		parent::__construct();

		// Sets connection values
		$this->setHost($hostName);
		$this->setUser($userName);
		$this->setPass($password);
		$this->setBaseName($baseName);
		$this->setPersistent($persistent);
		$this->setPort($port);
		$this->setCharset($charset);
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
	  * @param boolean $newLink (Only used in old Mysql)
	  * @access public
	  * @return void
	  */
	public function connect($newLink = false){
		$result = false;

		if($this->checkParams()){
			$this->setIdDatabase(mysqli_init());
			$result = mysqli_real_connect(	$this->getIdDatabase(),
											($this->getPersistent()?'p:':'').$this->getHost(),
											$this->getUser(),
											$this->getPass(),
											$this->getBaseName(),
											$this->getPort());
			if ($result === false) {
				$this->setEventMsg('Error: ('.mysqli_connect_errno().') '.mysqli_connect_error());
			}
			else {
				mysqli_set_charset($this->getIdDatabase(), $this->getCharset());
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
		mysqli_close($this->getIdDatabase());
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
	  * Execute SQL sentence and returns data on an array or false in case of error
	  *
	  * @access public
	  * @param 	string 	$sql
	  * @return array or boolean
	  */
	public function executeSql($sql = ''){
		$result = false;

		$query = mysqli_query($this->getIdDatabase(), $sql);

		if ($query === false){
			$this->setEventMsg(mysqli_error($this->getIdDatabase()));
		}
		else {
			if ($query === true){
				$result = true;
			}
			else {
				while ($row = $query->fetch_assoc()) {
					$result[] = $row;
				}
			}
		}
		return $result;
	}

	private function _getLimit($nRows, $offset) {
		// Offset
		$offsetStr = '';
		if ($offset >= 0){
			$offsetStr = "$offset, ";
		}
		if ($nRows < 0){
			$nRows = '18446744073709551615';
		}
		return " LIMIT ".$offsetStr.$nRows;
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
		return $this->executeSql($sql.$this->_getLimit($nRows, $offset));
	}

	public function getSqlResource($sql = ''){
		return $this->getIdDatabase()->query($sql);
	}

	public function getSqlResourceLimit($sql = '', $nRows = -1 , $offset = -1){
		return $this->getSqlResource($sql.$this->_getLimit($nRows, $offset));
	}

	static function fetchRow($query, $row = ''){
		if ($row !== ''){
			if(($row >= 0) && ($row < $this->getRecordCount($query))){
				mysqli_data_seek($query, $row);
				$record = mysqli_fetch_row($query);
				mysqli_data_seek($query, $row); //mysql_fetch_assoc advances one position, we don't want it
			}
			else{
				$record = false;
			}
		}
		else {
			$record = $query->mysqli_fetch_assoc();
		}
		return $record;
	}

	static function getRecordCount($query){
		return mysqli_affected_rows($query);
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