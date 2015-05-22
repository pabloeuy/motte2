<?php
/**
 * view
 *
 * @filesource
 * @package motte
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com)
 * 			Braulio Rios (braulioriosf@gmail.com)
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */

class mteHtmlResponse {

	private $_js;
	private $_css;
	private $_varTpl;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Html
		$this->_js      = array();
		$this->_css     = array();
		$this->_varTpl  = array();
	}

	/**
	 * Destructor
	 */
	public function __destruct() {

	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//          P R O P E R T I E S   M E T H O D S
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function addVarTpl($var, $value) {
		$this->_varTpl[$var] = $value;
	}

	public function getVarTpl($var = ''){
		return $var==''?$this->_varTpl:$this->_varTpl[$var];
	}

	public function assign($var, $value) {
		$this->_varTpl[$var] = $value;
	}

	public function addJs($files) {
		if (is_string($files) && is_readable($files)) {
			$this->_js[$files] = True;
		} else {
			if (is_array($files)) {
				foreach ($files as $f) {
					$this->addJs($f);
				}
			}
		}
	}

	public function addCss($files) {
		if (is_string($files)) {
			if (is_readable($files)) {
				$this->_css[$files] = True;
			}
		} else {
			if (is_array($files)) {
				foreach ($files as $f) {
					$this->addCss($f);
				}
			}
		}
	}

	public function getJs() {
		return array_keys($this->_js);
	}

	public function getCss() {
		return array_keys($this->_css);
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//           M E T O D O S   T E M P L A T E
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function getTemplate($tplName, $tplDir = '', $cacheLifeTime = 0, $cacheId = '') {
		// params
		$tplFile = '';
		if (is_readable($tplName) && is_file($tplName)) {
			$pathParts = pathinfo($tplName);
			$tplFile = $pathParts['basename'];
			$tplDir  = $pathParts['dirname'];
		}
		else {
			if ($tplDir == '' && !defined('DIR_TEMPLATES')) {
				die(__('Path to the template directory not defined (constant DIR_TEMPLATES)'));
			}
			else {
				if (is_readable($tplDir.'/'. $tplName) && is_file($tplDir.'/'. $tplName)) {
					$tplFile = $tplName;
				}
				elseif (defined('DIR_TEMPLATES') && is_readable(DIR_TEMPLATES.'/'. $tplName) && is_file(DIR_TEMPLATES.'/'. $tplName)) {
					$tplFile = $tplName;
					$tplDir  = DIR_TEMPLATES;
				}
				else {
					die(__('Template not found ').$tplName);
				}
			}
		}

		// js and css files added automatically (by name)
		$pathParts = pathinfo($tplDir.'/'.$tplFile);
		$this->addJs((defined('DIR_JS')?DIR_JS:$tplDir.'/js').'/'.$pathParts['filename'].'.js');
		$this->addCss((defined('DIR_CSS')?DIR_CSS:$tplDir.'/css').'/'.$pathParts['filename'].'.css');

		$result = '';
		if (MTE_ENGINE_TEMPLATE == 'mteSimpleTemplate') {
			$result = new mteSimpleTemplate($tplFile, $tplDir);
		}
		elseif (MTE_ENGINE_TEMPLATE == 'smarty') {
			$result = new mteTemplate($tplDir, $template, $cacheLifeTime, $cacheId);
		}

		// return template object
		return $result;
	}
}
?>