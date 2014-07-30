<?php
/**
 * Ajax Response
 *
 * @filesource
 * @package motte
 * @subpackage misc
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */

class mteAjaxResponse {

	/**
	 * status
	 *
	 * @access private
	 * @var integer
	 */
	private $_status;

	/**
	 * Resultado en XML
	 *
	 * @access private
	 * @var array
	 */
	private $_result;

	/**
	 * error
	 *
	 * @access private
	 * @var array
	 */
	private $_error;


	/**
	 * charset
	 *
	 * @access private
	 * @var string
	 */
	private $_charset;


	// String
	private $_valueSeparator;
	private $_recordSeparator;
	private $_substituteCharacter;

	/**
	 * Constructor
	 */
	public function __construct($charset = 'utf-8') {
		$this->clearResponse();

		// Set
		$this->setCharset($charset);
		$this->setValueSeparator();
		$this->setRecordSeparator();
		$this->setSubstituteCharacter();
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
	}

	private function _replaceSeparators($string){
		return str_replace(array($this->_valueSeparator, $this->_recordSeparator), $this->_substituteCharacter, $string);
	}

	/**
	 * Set the separator between the key and the value in the response string
	 * @return
	 * @param string $separator[optional]
	 */
	public function setValueSeparator($separator = '|'){
		$this->_valueSeparator = $separator;
	}

	/**
	 * Set the separator between two different records, by default it's a break line
	 * @return
	 * @param object $separator[optional]
	 */
	public function setRecordSeparator($separator = "\n"){
		$this->_recordSeparator = $separator;
	}


	/**
	 * Set charset
	 * @return
	 * @param charset
	 */
	public function setCharset($charset){
		$this->_charset = $charset;
	}

	public function getCharset(){
		return $this->_charset;
	}

	/**
	 * If a string contains a value or record separator character, this character will replace it
	 * @return
	 * @param string  $character[optional]
	 */
	public function setSubstituteCharacter($character = '_'){
		$this->_substituteCharacter = $character;
	}

	/**
	 * Clean response for ajax
	 */
	public function clearResponse() {
		$this->_error  = array ();
		$this->_result = array ();
		$this->_status = 0;
	}

	/**
	 * Add error
	 */
	public function addError($error) {
		$this->_error[] = $error;
	}

	/**
	 * Set status OK
	 */
	public function setStatusOk() {
		$this->setStatus(1);
	}

	/**
	 * Set status ERROR
	 */
	public function setStatusError() {
		$this->setStatus(0);
	}

	/**
	 * Set a status response
	 */
	public function setStatus($status) {
		$this->_status = $status;
	}

	/**
	 * Add a block to response of xml
	 *
	 * @param string $tag
	 * @param array $data
	 */
	public function addBlock($tag, $record) {
		$this->_result[] = array ('tag'=>$tag, 'value'=>$record);
	}

	/**
	 * Add a block to response of json
	 *
	 * @param mixed $id
	 * @param mixed $value
	 */
	public function addBlockJSon($id, $value) {
		$this->_result[$id] = $value;
	}


	//--------------------------------------------------------------------------
	//                                 X M L
	//--------------------------------------------------------------------------
	/**
	 * Parse a xml
	 */
	public function parseXml() {
		$xml = "<?xml version=\"1.0\" encoding=\"".$this->getCharset()."\"?>";
		$xml .= "<result>";
		// Status
		$xml .= "<status>".$this->_status."</status>";

		// Result
		foreach ($this->_result as $value) {
			$xml .= "<".$value['tag'].">";
			if (is_array($value['value'])){
				foreach ($value['value'] as $field=>$valueData) {
					$xml .= "<$field>".str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $valueData)."</$field>";
				}
			}
			else{
				$xml .= str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $value['value']);
			}
			$xml .= "</".$value['tag'].">";
		}

		// Error
		foreach ($this->_error as $value) {
			$xml .= "<error>".htmlspecialchars($value)."</error>";
		}
		$xml .= "</result>";
		return $xml;
	}

	/**
	 * Retuen a xml
	 *
	 * @return string
	 */
	public function getXml() {
		return $this->parseXml();
	}

	//--------------------------------------------------------------------------
	//                                 J S O N
	//--------------------------------------------------------------------------
	public function parseJSon(){
		// Result
		$result = array();
		foreach ($this->_result as $value) {
			$result[$value['tag']] = $value['value'];
		}
		return json_encode($result);
	}

	public function getJSon(){
		return $this->parseJSon();
	}

	//--------------------------------------------------------------------------
	//                                 S T R I N G
	//--------------------------------------------------------------------------
	/**
	 * Parse a string
	 */
	public function parseString() {
		$result = array();
		foreach ($this->_result as $key => $value) {
			$result[] = $this->_replaceSeparators($value['tag']).$this->_valueSeparator.$this->_replaceSeparators($value['value']);
		}
		return implode($this->_recordSeparator, $result);
	}

	/**
	 * Return a string
	 *
	 * @return string
	 */
	public function getString() {
		return $this->parseString();
	}

}
?>