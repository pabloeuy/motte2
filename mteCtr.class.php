<?php
/**
 * app
 *
 * @filesource
 * @package motte
 * @subpackage app
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */

define('SRV_HTML', 0);
define('SRV_AJAX_XML', 1);
define('SRV_AJAX_HTML', 2);
define('SRV_AJAX_JSON', 3);
define('SRV_UPLOADIFY', 4);

// Default defined
if (!defined('MTE_ENGINE_TEMPLATE')) {
	define('MTE_ENGINE_TEMPLATE', 'mteSimpleTemplate');
}
if (!defined('MTE_ACTIVE_TRANSLATE')) {
	define('MTE_ACTIVE_TRANSLATE', '1');
}

// Template engine
if (MTE_ENGINE_TEMPLATE == 'mteSimpleTemplate') {
	include_once(DIR_MOTTE.'/mteSimpleTemplate.class.php');
}
elseif (MTE_ENGINE_TEMPLATE == 'smarty') {
	include_once(DIR_MOTTE.'/mteTemplate.class.php');
}

// include
include_once(DIR_MOTTE.'/mteAjaxResponse.class.php');
include_once(DIR_MOTTE.'/mteTools.class.php');
include_once(DIR_MOTTE.'/mteHtmlResponse.class.php');
include_once(DIR_MOTTE.'/lib/gettext/gettext.php');
include_once(DIR_MOTTE.'/lib/gettext/stringReader.php');
include_once(DIR_MOTTE.'/mtei18n.class.php');
include_once(DIR_MOTTE.'/lib/class.inputfilter.php');
include_once(DIR_MOTTE.'/mteRestManager.class.php');
include_once(DIR_MOTTE.'/mteRestClient.class.php');


// alias
function __($text = '') {
	return MTE_ACTIVE_TRANSLATE == '1'?mteCtr::get()->_($text):$text;
}

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//                C L A S S
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
class mteCtr {

	private static $_instance;
	private $_obj = array();
	private $_lang;
	private $_service;
	private $_module;
	private $_method;
	private $_htmlResponse;
	private $_response;
	private $_restManager;
	private $_restClient;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->setService(array_key_exists('S', $_GET) && $_GET['S'] != '' ? $_GET['S'] : SRV_HTML);
		$this->setModule(array_key_exists ('Q', $_GET) && $_GET['Q'] != '' ? $_GET['Q'] : (defined('MODULE_DEFAULT')?MODULE_DEFAULT:'home'));
		$this->setMethod(array_key_exists ('M', $_GET) && $_GET['M'] != '' ? $_GET['M'] : (defined('METHOD_DEFAULT')?METHOD_DEFAULT:'index'));
	}

	/**
	 * Destructor
	 */
	public function __destruct() {

	}

	/**
	 *
	 * @return mteCtr
	 */
	public static function get() {
		if (!isset(self::$_instance)) {
			self::$_instance = new mteCtr();
		}
		return self::$_instance;
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                P R O P E R T I E S
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function setService($serv) {
		$this->_service = $serv;
	}

	public function getService() {
		return $this->_service;
	}

	public function setModule($mod) {
		$this->_module = $mod;
	}

	public function getModule() {
		return $this->_module;
	}

	public function setMethod($met) {
		$this->_method = $met;
	}

	public function getMethod() {
		return $this->_method;
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                C O N T R O L L E R
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

	/*TODO: NOT USED*/
	public function verifyModuleMethod($module, $method) {
		$result = false;
		$module = strtolower($module);
		$method = strtolower($method);

		$src = !defined('DIR_CONTROLLER')?DIR_ROOT.'/controller':DIR_CONTROLLER . "/$module.controller.php";
		if (is_readable($src)) {
			$ctr = $this->getControllerObject($module);
			if (method_exists($ctr, $method)) {
				$result = true;
			}
		}
		return $result;
	}

	public function execute($module, $method = '', $extra = '') {
		$result = '';
		$error  = '';
		$module = strtolower($module);
		$method = strtolower($method);

		$src = !defined('DIR_CONTROLLER')?DIR_ROOT.'/controller':DIR_CONTROLLER . "/$module.controller.php";

		if (is_readable($src)) {
			$ctr = $this->getControllerObject($module);
			if (method_exists($ctr, $method)) {
				$result = $ctr->$method($extra);
			} else {
				$error = __('Unknown method')." $module/$method";
			}
		} else {
			$error = __('Unknown module')." $module/$method";
		}
		if ($error != '') {
			$result = die($error);
		}
		return $result;
	}

	public function ajax($module, $method) {
		$src = !defined('DIR_CONTROLLER')?DIR_ROOT.'/controller':DIR_CONTROLLER . '/' . strtolower($module) . '.controller.php';
		if (is_readable($src)) {
			$ctr = $this->getControllerObject($module);
			if (method_exists($ctr, $method)) {
				$ctr->$method();
			} else {
				$this->getResponse()->setStatusError();
				$this->getResponse()->addError(__('Unknown method'));
			}
		} else {
			$this->getResponse()->setStatusError();
			$this->getResponse()->addError(__('Unknown module'));
		}
	}

	public function getControllerObject($module) {
		if (!array_key_exists('ctr'.strtolower($module), $this->_obj)) {
			$scr = (!defined('DIR_CONTROLLER')?DIR_ROOT.'/controller':DIR_CONTROLLER). '/' . $module . '.controller.php';
			if (is_readable($scr) && is_file($scr)) {
				include_once ($scr);
				$objCtr = 'ctr' . ucfirst($module);
				$this->_obj['ctr'.strtolower($module)] = new $objCtr();
			}
			else {
				die(__('Unknown module').' '.$module);
			}
		}
		return $this->_obj['ctr'.strtolower($module)];
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//          R E S T   M A N A G E R
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function getRM() {
		if (!isset($this->_restManager)) {
			$this->_restManager = new mteRestManager();
		}
		return $this->_restManager;
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//          R E S T   C L I E N T
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function getRClient($uri = '', $auth = true) {
		if (!isset($this->_restClient)) {
			$this->_restClient = new mteRestClient(defined('DIR_API_REST')?DIR_API_REST:$uri, defined('AUTH_API_REST')?AUTH_API_REST:$auth);
		}
		return $this->_restClient;
	}


	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//          R E S P O N S E   A J A X   /   J S O N
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function getResponse() {
		if (!isset($this->_response)) {
			$this->_response = new mteAjaxResponse();
		}
		return $this->_response;
	}

	static function clearResponse() {
		$this->_response = new mteAjaxResponse();
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//          R E S P O N S E   H T M L
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function htmlResponse() {
		if (!isset($this->_htmlResponse)) {
			$this->_htmlResponse = new mteHtmlResponse();
		}
		return $this->_htmlResponse;
	}

	public function setHtmlVar($name, $value) {
		mteCtr::get()->htmlResponse()->addVarTpl($name, $value);
	}

	public function getHtml($template = ''){
		$tpl = mteCtr::get()->getTemplate($template == ''?'page':$template);
		$tpl->setVar('JS', mteCtr::get()->htmlResponse()->getJs());
        $tpl->setVar('CSS', mteCtr::get()->htmlResponse()->getCss());
		foreach (mteCtr::get()->htmlResponse()->getVarTpl() as $key => $value) {
        	$tpl->setVar($key, $value);
		}
		return $tpl->getHtml();
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                      V I E W
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function getTemplate($tplName, $tplDir = '') {
		$pathParts = pathinfo($tplName);
		if (!isset($pathParts['extension']) || $pathParts['extension'] == '') {
			$tplName .= '.php';
		}

		// Smarty cache
		$cacheLife = 0;
		$cacheId   = '';
		if (MTE_ENGINE_TEMPLATE == 'smarty') {
			if (MTE_HTML_CACHE == true) {
				$cacheLife = defined('MTE_SMARTY_CACHE_LIFE_TIME')?MTE_HTML_CACHE_LIFE_TIME:60;
				$cacheId   = strtolower(str_replace('/', '_', $_SERVER['REQUEST_METHOD'].'_'.$_SERVER["QUERY_STRING"] == ''?'home':$_SERVER["QUERY_STRING"]));
			}
		}
		return $this->htmlResponse()->getTemplate($tplName, $tplDir, $cacheLife, $cacheId);
	}

	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                  I N T E R N A C I O N A L I Z A T I O N
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function _($text) {
		if ($text != '' && defined('MTE_LANG') && defined('MTE_LANG_DOMAIN') && defined('DIR_LANG')) {
			if (!isset($this->_lang)) {
				$this->_lang = new mtei18n(MTE_LANG, DIR_LANG, MTE_LANG_DOMAIN);
			}
			$text = $this->_lang->_($text);
		}
		return $text;
	}
}
?>