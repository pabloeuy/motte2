<?php
/**
 * Facade client using HTTPFul to facilitate calls to the REST API
 *
 * @filesource
 * @package motte
 * @subpackage app
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.5
 * @author 	Maicol Bentancor (maibenta@correo.ucu.edu.uy) /
 *			Pablo Ilundain (pabloilundain@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */
define('MTE_RESTCLIENT_OK_RESPONSE', 200);
include_once(DIR_MOTTE.'/lib/httpful.phar');

class mteRestClient {

	private $_user;
	private $_pass;
	private $_auth;
    private $_debug;
    private $_uriApi;

	/**
     * Constructor
     */
    public function __construct($uriApi = '', $authenticate = false) {
    	$this->_auth   = $authenticate;
        $this->_debug  = false;
        $this->_uriApi = $uriApi;
    }

    /*
     * Destructor
     */
    public function __destruct() {

    }

    public function setCredentials($user, $pass){
    	$this->_user = $user;
    	$this->_pass = $pass;
    }

    public function setDebug($state = false) {
        $this->_debug = $state;
    }

    public function setUriApi($uri = '') {
        $this->_uriApi = $uri;
    }

    private function _debug($msg = '') {
        if ($this->_debug == true) {
            print($msg);
        }
    }

    /**
     * Retrieve by GET method a json which then transforms into an array, usually to retrieve a resource
     * @param  string $uri : the route make request GET
     * @return the data received via GET
     */
    public function get($uri){
        if($this->_auth){
	        $response = json_decode(\Httpful\Request::get($this->_uriApi.$uri)
	                ->authenticateWith($this->_user,$this->_pass)
	                ->send(),true);

        }else{
        	$response = json_decode(\Httpful\Request::get($this->_uriApi.$uri)
	                ->send(),true);
        }
        $this->_debug($response);
        return $response;
    }

    /**
     * Send by json POST from a PHP array, usually to add/edit a resource
     * @param  string $uri : the route make request POST
     * @param  array $arr  : data to send
     * @return the response obtained by calling the REST by POST
     */
    public function post($uri,$arr){
        if($this->_auth){
	        $response = \Httpful\Request::post($this->_uriApi.$uri)
		                ->sendsJson()
		                ->authenticateWith($this->_user,$this->_pass)
		                ->body(json_encode($arr))
		                ->send();
		}else{
			$response = \Httpful\Request::post($this->_uriApi.$uri)
		                ->sendsJson()
		                ->body(json_encode($arr))
		                ->send();
		}
        $this->_debug($response);
        return $response;
    }

    /**
     * Send by DELETE to delete a resource (works like GET)
     * @param  string $uri : the route make request DELETE
     * @return the response obtained by calling the REST by DELETE
     */
    public function delete($uri){
    	if($this->_auth){
	        $response = \Httpful\Request::delete($this->_uriApi.$uri)
		               	->authenticateWith($this->_user,$this->_pass)
		                ->send();
	    }else{
	    	$response = \Httpful\Request::delete($this->_uriApi.$uri)
		                ->send();
	    }
        $this->_debug($response);
        return $response;
    }

    /**
     * Work like DELETE and send a response to client side in the MVC App
     * @param  string $uri : the route make request DELETE
     */
    public function deleteWithResponse($uri){
        $response=self::delete($uri);
        if($response->code==MTE_RESTCLIENT_OK_RESPONSE){
            mteCtr::get()->getResponse()->setStatusOk();
            mteCtr::get()->getResponse()->addBlock("response",$response);
        }else{
            mteCtr::get()->getResponse()->setStatusError();
            mteCtr::get()->getResponse()->addError($response);
        }
    }

    /**
     * Work like POST and send a response to client side in the MVC App
     * @param  string $uri : the route make request POST
     * @param  array $arr  : data to send
     */
    public function postWithResponse($uri,$arr){
        $response=self::post($uri,$arr);
        if($response->code==MTE_RESTCLIENT_OK_RESPONSE){
            mteCtr::get()->getResponse()->setStatusOk();
            mteCtr::get()->getResponse()->addBlock("response",$response);
        }else{
            mteCtr::get()->getResponse()->setStatusError();
            mteCtr::get()->getResponse()->addError($response);
        }
    }

    /**
     * Indicates if a GET response has an error
     * @param  array   : $response API REST's response
     * @return boolean : if has an error
     */
    public function hasError($response){
        return isset($response['error']);
    }
}