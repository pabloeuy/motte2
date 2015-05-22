<?php
/**
 * route Manager
 *
 * @filesource
 * @package motte
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.5
 * @author 	Maicol Bentancor (maibenta@correo.ucu.edu.uy) /
 *			Pablo Ilundain (pabloilundain@gmail.com) /
 * 			Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */

class mteRoute {
	private $_controller;
	private $_method;
	private $_params;

	/**
	 * Constructor
	 */
	public function __construct($path = '') {
		// initialize
		$this->_controller = '';
		$this->_method     = '';
		$this->_params     = array();

		// set route
		$this->setPath($path);
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
	}


	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                     P R O P E R T I E S
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

	// ----------------- Path  -----------------
	public function validPath($path) {
		$crumbs = explode('/', $path);
		return is_array($crumbs)  && count($crumbs) > 1;
	}

	public function getPath() {
		// create path
		$result = array();
		if ($this->getController() != '' && $this->getMethod() != '') {
			$result[] = $this->getController();
			$result[] = $this->getMethod();
		}
		// Params
		$result[] = $this->_paramsToPath();
		return implode('/', $result);
	}

	public function setPath($path) {
		// Parse
		if ($this->validPath($path)) {
			// parse
			$crumbs = explode('/', $path);
			if ($path[0] == '/') {
				unset($crumbs[0]);
			}

			// Controller & Method
			$this->setController(array_shift($crumbs));
			$this->setMethod(array_shift($crumbs));

			// Params
			foreach ($crumbs as $value) {
				$this->addParam($value);
			}
		}
	}

	// ----------------- Controller  -----------------
	public function getController() {
		return $this->_controller;
	}

	public function setController($value) {
		$this->_controller = (string)$value;
	}

	// ----------------- Method -----------------
	public function getMethod() {
		return $this->_method;
	}

	public function setMethod($value) {
		$this->_method = (string)$value;
	}

	// ----------------- Params  -----------------
	public function addParam($value) {
		$this->_params[] = str_replace(':', '', (string)$value);
	}

	public function delParam($value) {
		$pos = array_search($value, $this->_params);
		if (!($pos === FALSE)) {
			unset($this->_params[$pos]);
		}
	}

	public function countParams() {
		return count($this->_params);
	}

	private function _paramsToPath() {
		$result = array();
		foreach ($$this->_params as $value) {
			$result[] = ':'.$value;
		}
		return implode('/', $result);
	}

	public function getParams() {
		return $this->_params;
	}
}
?>