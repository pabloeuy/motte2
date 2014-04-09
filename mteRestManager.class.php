<?php
/**
 * Rest Manager
 *
 * @filesource
 * @package motte
 * @subpackage app
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Maicol Bentancor (maicol.bentancor@gmail.com) /
 *			Pablo Ilundain (pabloilundain@gmail.com) /
 * 			Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */

define('MTE_HTTP_VERSION', 'HTTP/1.2');
define('MTE_HTTP_RESPONSE_ERROR',   '406 Not Acceptable');
define('MTE_HTTP_RESPONSE_SUCCESS', '200 OK');
define('MTE_RESPONSE_CONTENT_TYPE', 'application/json');
define('MTE_REQUEST_CONTENT_TYPE', 'application/json');

// include
include_once(DIR_MOTTE.'/mteRoute.class.php');

class mteRestManager {
	private $_routes;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->_routes = array(	'GET'    => array(),
							  	'POST'   => array(),
							   	'DELETE' => array());
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
	}

	public function route($httpMet, $path){
		// Parse path
		$httpMet = strtoupper($httpMet);

		// Route
		$route = new mteRoute();
		if ($route->validPath($path)) {
			// Add
			$route->setPath($path);
			$this->_routes[$httpMet][$route->getController()][$route->getMethod()] = $route;
		}
	}

	private function _parseURL($srv) {
		// clean url
		$sn = explode('/', $srv['SCRIPT_NAME']);
		array_pop($sn);
 		return implode('/', array_diff(explode('/', $srv['REQUEST_URI']), $sn));
	}

	private function _execute($module, $method) {
		$src = !defined('DIR_CONTROLLER')?DIR_ROOT.'/controller':DIR_CONTROLLER . '/' . strtolower($module) . '.controller.php';
		if (is_readable($src)) {
			$ctr = mteCtr::get()->getControllerObject($module);
			if (method_exists($ctr, $method)) {
				$ctr->$method();
			}
			else {
				$this->responseError(__('Unknown method (mteCtr-'.$method.')'));
			}
		}
		else {
			$this->responseError(__('Unknown module (mteCtr-'.$module.')'));
		}
	}

	private function _magicGET($pApp, $pVar) {
		foreach ($pApp as $key => $value) {
			if (isset($pVar[$key])){
				$_GET[$value] = $pVar[$key];
			}
		}
	}

	private function _magicPOST(){
		$body = @file_get_contents('php://input');
		$record = json_decode($body, true);
		if (!is_null($record) && is_array($record)) {
			foreach ($record as $key => $value) {
				$_POST[$key] = $value;
			}
		}
        else {
        	$this->responseError(__('Malformed json'));
		}
	}

	private function _runRoute($httpMet, $path) {
		// Valid request method
		if (isset($this->_routes[$httpMet])) {
			// parse Route
			$routeUri = new mteRoute();
			$routeUri->setPath($path);

			// Valid Controller
			if (isset($this->_routes[$httpMet][$routeUri->getController()])) {
				// Valid Method
				if (isset($this->_routes[$httpMet][$routeUri->getController()][$routeUri->getMethod()])) {
					$routeApp = $this->_routes[$httpMet][$routeUri->getController()][$routeUri->getMethod()];

					// Valid Params
					if ($routeApp->countParams() == $routeUri->countParams()) {
						$this->_magicGET($routeApp->getParams(), $routeUri->getParams());
						if ($httpMet == 'POST') {
							$this->_magicPOST();
						}
						$this->_execute($routeUri->getController(), $routeUri->getMethod());
					}
					else {
						$this->responseError(__('Params not match'));
					}
				}
				else {
					$this->responseError(__('No routes for selected method'));
				}
			}
			else {
				$this->responseError(__('No routes for selected controller'));
			}
		}
		else {
			$this->responseError(__('No routes for selected request method'));
		}
	}

	// Run
	public function run(){
		$this->_runRoute($_SERVER['REQUEST_METHOD'], $this->_parseURL($_SERVER));
	}

	private function _response($code, $data) {
		header(sprintf('HTTP/%s %s', MTE_HTTP_VERSION, $code));
		header('Content-Type', MTE_RESPONSE_CONTENT_TYPE);
		if ($data != '') {
			echo(json_encode($data));
		}
	}

	public function responseError($msg = ''){
		$this->_response(MTE_HTTP_RESPONSE_ERROR, array('error' => $msg));
	}

	public function responseSuccess($data = ''){
		$this->_response(MTE_HTTP_RESPONSE_SUCCESS, $data);
	}

	public function getHttpMethod(){
		return $_SERVER['REQUEST_METHOD'];
	}
}
?>