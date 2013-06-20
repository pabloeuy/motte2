<?php
/**
 * Class to manipulate a recordset of objects
 * Encapsulate navigation of a record set of objects
 *
 * @filesource
 * @package 	motte
 * @subpackage model
 * @version 	1.0
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public license
 * @author 		Pedro Gauna (pgauna@gmail.com) /
 * 				Carlos Gagliardi (carlosgag@gmail.com) /
 * 				Braulio Rios (braulioriosf@gmail.com) /
 * 				Pablo Erartes (pabloeuy@gmail.com) /
 * 				GBoksar/Perro (gustavo@boksar.info)
 */

class mteRecordSet implements Iterator {

/**
 * Index
 *
 * @var integer
 * @access private
 */
	private $_index;

	/**
	 * End of RecordSet
	 *
	 * @var integer
	 * @access private
	 */
	private $_eof;

	/**
	 * Begin of RecordSet
	 *
	 * @var integer
	 * @access private
	 */
	private $_bof;

	/**
	 * Records
	 * @var array
	 */
	private $_records;

	/**
	 * Engine providing static methods to access the link contents, in case you set a link
	 * mteCnx @var
	 */
	private $_engine;

	/**
	 * Resource with contents to acced
	 * resource @var
	 */
	private $_link;

	/**
	 * Active record
	 * array @var
	 */
	public $record;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return mteRecordSet
	 */
	public function __construct() {
	// Initialize attributes
		$this->clearData();
	}

	/**
	 * Destructor
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct() {
	}

	//--------------- ITERATOR METHODS -----------------------------------------
	/**
	 * Reset internal pointer
	 * @return void
	 */
	public function rewind() {
		$this->first();
	}

	/**
	 * Return current record
	 * @return array
	 */
	public function current() {
		$this->_loadRecord();
		return $this->record;
	}

	/**
	 * get current position of internal pointer
	 * @return integer
	 */
	public function key() {
		return $this->_index;
	}

	/**
	 * check if current index is valid
	 * @return boolean
	 */
	public function valid() {
		return $this->_loadRecord();
	}

	/**
	 * Move the pointer to the next record (if its the last record do nothing)
	 *
	 * @access public
	 * @return void
	 */
	public function next() {
		if (!$this->isEOF()) {
		// next
			$this->_index++;
		}
		return $this->_loadRecord();
	}

	//-----------------------------------END ITERATOR METHODS--------------------------------------
	//------------------OTHER NAVIGATION METHODS---------------------
	/**
	 * Move the pointer to the first record
	 *
	 *  @access public
	 *  @return void
	 */
	public function first() {
	// first record
		$this->_index = 0;
		// load record
		return $this->_loadRecord();
	}

	/**
	 * Move the pointer to the last record
	 *
	 * @access public
	 * @return void
	 */
	public function last() {
	// last record
		$this->_index = $this->_eof - 1;
		// load record
		return $this->_loadRecord();
	}

	/**
	 * Move the pointer to the previous record (if its the first record do nothing)
	 *
	 * @access public
	 * @return void
	 */
	public function prev() {
		if (!$this->isBOF()) {
		// previous
			$this->_index--;
		}
		return $this->_loadRecord();
	}

	/**
	 * Move the pointer to the record of index $index
	 *
	 * @access public
	 * @param integer $index
	 * @return void
	 */
	public function gotoIndex($id) {
	// if _index is valid
		if (($id >= $this->_bof) && ($id <= $this->_eof)) {
			$this->_index = $id;
			return $this->_loadRecord();
		}
		else {
			return false;
		}
	}
	
	/**
	 * Initialize recordSet
	 *
	 * @access public
	 * @return void
	 */
	public function clearData() {
		$this->_link = NULL;
		$this->_engine = NULL;
		$this->_index = 0;
		$this->_eof = 0;
		$this->_bof = -1;
		$this->_records = array ();
		$this->record = NULL;
	}

	/**
	 * Return true if recordset is empty or false if not
	 *
	 * @access public
	 * @return boolean
	 */
	public function isEmpty() {
		return ($this->recordCount() == 0);
	}


	/**
	 * Return true if pointer is set to begin of recordset or false if not
	 *
	 * @access public
	 * @return boolean
	 */
	public function isBOF() {
		return ($this->_index == $this->_bof);
	}

	/**
	 * Return true if pointer is set to end of recordset or false if not
	 *
	 * @access public
	 * @return boolean
	 */
	public function isEOF() {
		return ($this->_index == $this->_eof);
	}

	/**
	 * Return number of records
	 *
	 * @access public
	 * @return integer
	 */
	public function recordCount() {
		$count = count($this->_records);
		if (is_array($this->_link) && is_resource($this->_link[0])) {
			$count += $this->_engine->getRecordCount($this->_link[0]);
		}
		return $count;
	}

	/**
	 * Return total page
	 *
	 * @param integer $numRows
	 * @return integer
	 */
	public function getTotalPages($numRows = 50) {
		if ($numRows == 0) {
			$numRows = MTE_GRID_ROWS;
		}
		$numRecs = $this->recordCount();
		$result = (int)($numRecs/$numRows);

		if ($numRecs%$numRows > 0) {
			$result = $result+1;
		}
		return $result == 0?1:$result;
	}

	/**
	 * Return current index
	 *
	 * @access public
	 * @return integer
	 */
	public function getIndexRecord() {
		return ($this->_index);
	}


	/**
	 * set a link to a query which the recordSet will handle
	 * @return boolean $validResource
	 * @param resource $resource
	 */
	public function setLink($resource, $engine) {
		if (is_resource($resource)) {
			$this->_engine = $engine;
			$currentSize = count($this->_records);
			$this->_link = array ($resource, $currentSize, $this->_engine->getRecordCount($resource)); //resource, position where insert and lenght
			$this->_eof = $currentSize+$this->_engine->getRecordCount($resource);
			$this->first();
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * clear link but not internal records, unlike clearData()
	 */
	public function clearLink() {
		$this->_engine = NULL;
		$this->_link = NULL;
		$this->_eof = count($this->_records);
		$this->first();
	}

	/**
	 * add data of an array to the recordset
	 *
	 * @access public
	 * @param array $data 	See the php manual for syntaxis if $data is not an array
	 * @return void
	 */
	public function addData( & $data) {
	// Si hay datos
		if (( isset ($data)) && (!is_null($data))) {
		// extract data from $data (not array)
			if (!is_array($data)) {
				$data = array ($data);
			}
			// load data into recordset
			if (count($data) > 0) {
				foreach ($data as $record) {
					$this->addRecord($record);
				}
			}

			// Primer registro
			$this->first();
		}
	}

	/**
	 * add one record to the recordset
	 *
	 * @access public
	 * @param $record
	 * @return void
	 */
	public function addRecord($record) {
		if ((!is_null($record))) {
			if(!is_array($record)){
				$record = array($record);
			}
			$this->_records[] = $record;
			$this->_eof++;
		}
	}

	/**
	 * Return the last index of the recordset
	 *
	 * @access private
	 * @return integer
	 */
	private function _getLastIndex() {
		return ($this->recordCount()-1);
	}

	/**
	 * Load current record from current index
	 *
	 * @access private
	 * @return void
	 */
	private function _loadRecord() {
		$this->record = NULL;
		if ($this->_index > $this->_bof && $this->_index < $this->_eof) {
			if ($this->_link !== NULL && $this->_index >= $this->_link[1]) {    //after the insert position of the link
				if ($this->_index < $this->_link[1]+$this->_link[2]) {       //the index corresponds to a linked record
					$this->record = $this->_engine->fetchRow($this->_link[0], $this->_index - $this->_link[1]);
				}
				else { //after initial position of link, and also after ending position of the link
					$this->record = $this->_records[$this->_index-$this->_link[2]];
				}
			}
			else {    //before any inserted link, or there is no link
				$this->record = $this->_records[$this->_index];
			}
			return true;
		}
		else {
			return false;
		}
	}



	/**
	 * return an array with recordset content
	 *
	 * @access private
	 * @return array
	 */
	public function getArray($field = '') {
		$result = array ();
		if($this->_link === NULL && $field == '') {
			$result = $this->_records;
		}
		else {
			$indexActive = $this->getIndexRecord();
			$this->first();
			while (!$this->isEOF()) {
				if ($field == '') {
					$result[] = $this->record;
				}
				else {
					$result[] = $this->record[$field];
				}
				$this->next();
			}
			$this->gotoIndex($indexActive);
		}
		return $result;
	}


	/**
	 * get associative array indexed by the keyFields passed.
	 * @return array
	 * @param string/array $keyFields
	 * @param string/array $fields[optional]
	 */
	public function getAssocArray($keyFields, $fields = '') {
		$indexActive = $this->getIndexRecord(); //keep current position
		$this->first();
		$result = array ();
		if(!is_array($keyFields)) {
			$keyFields = array($keyFields);
		}
		$nKeys = count($keyFields);
		if($fields == '') {
			$fields = array_keys($this->record); //first record's fields are taken by default
		}
		elseif(!is_array($fields)) {
			$fields = array($fields);
		}
		$keyFields = array_reverse($keyFields);
		while (!$this->isEOF()) {
			$cK = 1;
			$auxResult = array();
			foreach($keyFields as $key) {
				$auxResult = array();
				if($cK == 1) {
					$record = array();
					foreach($fields as $field) {
						$record[$field] = $this->record[$field];
					}
				}
				$auxResult[$this->record[$key]] = $record;
				if($cK == $nKeys) {
					$result[$this->record[$key]] = $auxResult[$this->record[$key]];
				}
				else {
					$record = $auxResult;
					$cK ++;
				}
			}
			$this->next();
		}
		$this->gotoIndex($indexActive); //restore initial position
		return $result;
	}


}
?>
