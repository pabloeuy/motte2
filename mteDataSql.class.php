<?php
/**
 * SQL management class
 * Extract data from received connection. Uses mteTableSql for update processes
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
class mteDataSql {
	/**
	* DB Engine
	*
	* @access private
	* @var mteCnx $_engine
	*/
	private $_engine;

	/**
	* construct()
	*
	* @access public
	* @param resource  $engine
	* @return mteData  $this
	*/
	public function __construct($engine) {
		if (!is_null($engine)){
			$this->setEngine($engine);
		}
	}

	/**
	* destruct()
	*
	* @access public
	*/
	public function __destruct(){
	}

	/**
	* setEngine()
	* Set DB engine
	*
	* @access public
	* @param mteCnx $engine
	* @return void
	*/
	public function setEngine($engine){
		$this->_engine = $engine;
	}

	/**
	* getEngine()
	* Returns DB engine
	*
	* @access public
	* @return resource
	*/
	public function getEngine(){
		return($this->_engine);
	}

	/**
	* getColumnValues()
	* Returns recordSet
	*
	* @access public
	* @param string $colName
	* @param string $sql
	* @return mteRecordSet
	*/
	public function getColumnValues($colName = '',$sql = ''){
		$result = $this->getEngine()->executeSql($sql);
		if($result){
			$rSet = new mteRecordSet();
			if (is_array($result)){
				foreach($result as $row){
					$rSet->addRecord($row[$colName]);
				}
			}
			return $rSet;
		}
		else{
			return false;
		}
	}

	/**
	* getValue()
	* Returns first column of the result recordSet
	*
	* @access public
	* @param 	string 	$sql
	* @return Object or boolean
	*/
	public function getValue( $sql = '', $numRows = -1, $offset = -1 ){
		$result = $this->getEngine()->executeSqlLimit($sql,$numRows,$offset);
		if ($result){
			if (is_array($result)){
				$aux = array_shift($result);
				return array_shift($aux);
			}
		}
		else{
			return false;
		}
	}

	/**
	* getRecordSet()
	* Returns a recordSet as the result of the given SQL sentence
	*
	* @param string $sql
	* @param int $numRows
	* @param int $offset
	* @return array, boolean or mteRecordSet
	*/
	public function getRecordSet($sql = '', $numRows = -1 , $offset = -1){
		if (($numRows > -1) || ($offset > -1)){
			$resource = $this->getEngine()->getSqlResourceLimit($sql,$numRows,$offset);
		}
		else{
			$resource = $this->getEngine()->getSqlResource($sql);
		}
		$rSet = new mteRecordSet();
		if ($resource && is_resource($resource)){
			$rSet->setLink($resource, $this->getEngine());
		}
		return $rSet;
	}

	/**
	* 
	* @return 
	* @param object $sql
	*/
	public function getRecord($sql){
		$recordSet = mteDataSql::getRecordSet($sql, 1, 0);
		$recordSet->first();
		return $recordSet->record;
	}
}
?>