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
 * 			Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */
define('MTE_RESTCLIENT_DEBUG', false);
define('MTE_RESTCLIENT_OK_RESPONSE', 200);
include_once(DIR_MOTTE.'/lib/httpful.phar');

class mteRestClient {

	private $_user;
	private $_pass;
	private $_auth;

	/**
     * Constructor
     */
    public function __construct($authenticate = true) {
    	$this->_auth = $authenticate;
    }
    /*
     * Destructor
     */
    public function __destruct() {

    }

    public function setCredentials($user,$pass){
    	$this->_user = $user;
    	$this->_pass = $pass;
    }

    /**
     * Obtiene mediante el metodo GET un json que luego lo transforma en un array, generalmente para obtener un recurso
     * @param  string $uri la ruta a realizar la peticion de servicio REST
     * @return los datos que se recibio mediante GET
     */
    public function get($uri){
        if($this->_auth){
	        $response = json_decode(\Httpful\Request::get(DIR_API_REST.$uri)
	                ->authenticateWith($this->_user,$this->_pass)
	                ->send(),true);

        }else{
        	$response = json_decode(\Httpful\Request::get(DIR_API_REST.$uri)
	                ->send(),true);
        }
        if(MTE_RESTCLIENT_DEBUG){
        	print($response);
        }
        return $response;
    }

    /**
     * Envia mediante POST un json a partir de un array PHP, generalmente para agregar/editar un recurso
     * @param  string $uri la ruta a realizar la peticion de servicio REST
     * @param  array $arr el array a enviar como datos
     * @return la respuesta obtenida al ejecutar el servicio REST a partir del POST enviado
     */
    public function post($uri,$arr){
        if($this->_auth){
	        $response = \Httpful\Request::post(DIR_API_REST.$uri)
		                ->sendsJson()
		                ->authenticateWith($this->_user,$this->_pass)
		                ->body(json_encode($arr))
		                ->send();
		}else{
			$response = \Httpful\Request::post(DIR_API_REST.$uri)
		                ->sendsJson()
		                ->body(json_encode($arr))
		                ->send();
		}
        if(MTE_RESTCLIENT_DEBUG){
        	print($response);
        }
        return $response;
    }

    /**
     * Envia mediante DELETE una peticion de borrado de un recurso
     * @param  string $uri la ruta a realizar la peticion de servicio REST
     * @return la respuesta obtenida al ejecutar el servicio REST a partir del DELETE enviado, si borro o no el recurso
     */
    public function delete($uri){
    	if($this->_auth){
	        $response = \Httpful\Request::delete(DIR_API_REST.$uri)
		               	->authenticateWith($this->_user,$this->_pass)
		                ->send();
	    }else{
	    	$response = \Httpful\Request::delete(DIR_API_REST.$uri)
		                ->send();
	    }
        if(MTE_RESTCLIENT_DEBUG){print($response);}
        return $response;
    }

    /**
     * Envia la respuesta al sistema si se pudo borrar o no el recurso
     * @param  string $uri la ruta a realizar la peticion de servicio REST
     */
    public function deleteWithResponse($uri){
        $response=RestClient::delete($uri);
        if($response->code==MTE_RESTCLIENT_OK_RESPONSE){
            mteCtr::get()->getResponse()->setStatusOk();
            mteCtr::get()->getResponse()->addBlock("response",$response);
        }else{
            mteCtr::get()->getResponse()->setStatusError();
            mteCtr::get()->getResponse()->addError($response);
        }
    }

    /**
     * Envia la respuesta al sistema si se pudo guardar o no el recurso
     * @param  string $uri la ruta a realizar la peticion de servicio REST
     * @param  array $arr el array a enviar como datos
     */
    public function postWithResponse($uri,$arr){
        $response=RestClient::post($uri,$arr);
        if($response->code==MTE_RESTCLIENT_OK_RESPONSE){
            mteCtr::get()->getResponse()->setStatusOk();
            mteCtr::get()->getResponse()->addBlock("response",$response);
        }else{
            mteCtr::get()->getResponse()->setStatusError();
            mteCtr::get()->getResponse()->addError($response);
        }
    }

    /**
     * Indica si una respuesta de GET tiene un error
     * @param  array  $response respuesta de la API REST
     * @return boolean           si tiene error
     */
    public function hasError($response){
        return isset($response['error']);
    }
}