<?php
/**
 * sqlLite
 *
 * @filesource
 * @package motte
 * @subpackage model
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com)
 * 			Braulio Rios (braulioriosf@gmail.com)
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */
 
class mteSqlite {
	private $_dbh;
	private $_debug;
	private $_dbFile;
	private $_logFile;
	
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($debug = false) {
		$this->_debug   = $debug;
		$this->_dbFile  = '';
		$this->_dbh     = NULL;
		$this->_logFile = NULL;
	}

	/**
	 * Destructor
	 *
	 * @access public
	 */
	public function __destruct() {
		$this->close();

		if (!is_null($this->_logFile)) {
			fclose($this->_logFile);	
		}
	}
	
	private function _log($action){
		if ($this->_debug){
			if (is_null($this->_logFile)) {
				$this->_logFile = fopen($this->_dbFile.'.log', "a+"); 				
			}
			fwrite($this->_logFile, date ('y-m-d h:i:s')."\t".tools::getIpClient()."\t".$this->_dbFile."\t".$action."\n");
		}
	}

	public function debug($st){
		$this->_debug = $st;
	}		
	
	public function open($file){
		$this->_dbFile = $file;
		try {
			$this->_dbh = new PDO('sqlite:'.$this->_dbFile);
			$this->_log('Open database');
		} catch(PDOException $e) {
			$this->_log('Open database error: ',$e);
			die(__('(sqlite) Error connection ').$e);
		}
	}
	
	public function close(){
		if (isset($this->_dbh)){
			$this->_log('Close database');
			unset($this->_dbh);
		}
	}
	
	public function exec($sql, $controlError = true){
		try {
			$query = $this->_dbh->exec($sql);
			$this->_log('Execute: '.$sql);
		} catch (PDOException $e) {
			if ($controlError) {
				$this->_log('Execute: '.$sql.' / error: '.$e);
			}
			return $e;
		}
		return '';
	}
	
	public function lastInsertId(){
		return $this->_dbh->lastInsertId();
	}
	
	public function getRecordSet($sql){
		$this->_log('Execute: '.$sql);
		$result = null;
		try {
			$st = $this->_dbh->query($sql);
			if($st)
            	$result = $st->fetchAll();
			else
				$result = 0;
		} catch (Exception $e) {
			die(__('(sqlite) sql error'));
		}
		return $result;
	}
	
	public function getRecord($sql){
		$rs = $this->getRecordSet($sql);
		return is_array($rs)?array_shift($rs):array();
	}
	
	public function getValue($field, $sql){
		$r = $this->getRecord($sql);
		return isset($r[$field])?$r[$field]:'';
	}

	public function quote($str) {
		return $this->_dbh->quote($str);
	}
}
?>