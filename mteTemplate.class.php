<?php
/**
 * Clase para la lectura de plantillas HTML con Smarty
 *
 * @filesource
 * @package motte
 * @subpackage view
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public license
 * @author 	Pedro Gauna (pgauna@gmail.com) /
 * 		Carlos Gagliardi (carlosgag@gmail.com) /
 * 		Braulio Rios (braulioriosf@gmail.com) /
 * 		Pablo Erartes (pabloeuy@gmail.com) /
 * 		GBoksar/Perro (gustavo@boksar.info)
 */

// Smarty
include_once(DIR_MOTTE.'/lib/smarty3/libs/Smarty.class.php');

class mteTemplate {

	/**
	 *
	 * @var string
	 * @access private
	 */
	private $_template;

	/**
	 *
	 * @var string
	 * @access private
	 */
	private $_cacheId;

	/**
	 *
	 * @var array
	 * @access smarty
	 */
	private $_engine;


	/**
	 * Inicializa la clase con los datos de archivo y dir
	 *
	 * @access public
	 */
	public function __construct($compileDir = '', $templateDir = '', $template = '', $cacheLifeTime = 0, $cacheId = '') {
		// Motor Smarty
		$this->_engine = new Smarty();

		// Inicializo
		$this->setCompileDir($compileDir == ''?MTE_COMPILE_HTML:$compileDir);
		$this->setTemplateDir($templateDir == ''?MTE_TEMPLATE:$templateDir);
		$this->setTemplate($template);
		$this->setCacheLifeTime($cacheLifeTime);
		$this->setCacheId($cacheId);
		$this->clearVars();
	}
   
   private function _cleanHtml(&$buffer){
      if(MTE_CLEAN_HTML){
         $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '   '), '', $buffer);
      }
      return $buffer;
   }

	/**
	 *
	 * @param integer $value
	 */
	public function setCacheLifeTime($value = 0) {
		if ($value > 0) {
			$this->_engine->caching = 2;
			$this->_engine->compile_check = true;
			$this->_engine->cache_lifetime = $value;
			$this->_engine->cache_dir = MTE_CACHE_HTML;
		}
	}

	/**
	 *
	 * @return integer
	 */
	public function getCacheLifeTime() {
		return $this->_engine->cache_lifetime;
	}


	/**
	 *
	 * @param string $value
	 */
	public function setCacheId($value = '') {
		$this->_cacheId = $value;
	}

	/**
	 *
	 * @return string
	 */
	public function getCacheId() {
		return $this->_cacheId;
	}

	/**
	 * Define una nueva ubicacion para las plantillas
	 *
	 * @access public
	 * @param string $name = ''
	 */
	public function setTemplate($name = '') {
		$this->_template = $name;

	}

	/**
	 * Devuelve ubicacion para las plantillas
	 *
	 * @access public
	 * @return string
	 */
	public function getTemplate() {
		return $this->_template;
	}

	/**
	 * Define una nueva ubicacion para las plantillas
	 *
	 * @access public
	 * @param string $dir = ''
	 */
	public function setTemplateDir($dir = '') {
		$this->_engine->template_dir = $dir;
	}

	/**
	 * Devuelve ubicacion para las plantillas
	 *
	 * @access public
	 * @return string
	 */
	public function getTemplateDir() {
		return $this->_engine->template_dir;
	}

	/**
	 * Define una nueva ubicacion para las plantillas
	 *
	 * @access public
	 * @param string $dir = ''
	 */
	public function setCompileDir($dir = '') {
		$this->_engine->compile_dir = $dir;
	}

	/**
	 * Devuelve ubicacion para las plantillas
	 *
	 * @access public
	 * @return string
	 */
	public function getCompileDir() {
		return $this->_engine->compile_dir;
	}

	/**
	 * Limpia variables del template
	 *
	 * @param string $varName
	 */
	public function clearVar($varName) {
		$this->_engine->clear_assign($varName);
	}

	/**
	 * Limpia variables del template
	 *
	 */
	public function clearVars() {
		$this->_engine->clear_all_assign();
	}

	/**
	 * Asigna una variable al template
	 *
	 * @access public
	 * @param string $varName
	 * @param variant $varValue
	 */
	public function addVar($varName, $varValue) {
		$this->_engine->assign($varName, $varValue);
	}

	/**
	 * Asigna una variable al template
	 *
	 * @access public
	 * @param string $varName
	 * @param variant $varValue
	 */
	public function setVar($varName, $varValue) {
		$this->addVar($varName, $varValue);
	}

	/**
	 * Anexa una variable al template
	 *
	 * @access public
	 * @param string $varName
	 * @param variant $varValue
	 */
	public function appendVar($varName, $varValue) {
		$this->_engine->append($varName, $varValue);
	}

	/**
	 *
	 *
	 * @access public
	 * @param string $varName
	 * @return variant
	 */
	public function getVar($varName) {
		return $this->_engine->get_template_vars($varName);
	}

	/**
	 *
	 * @return boolena
	 */
	public function is_cached() {
		return ($this->getCacheLifeTime() > 0 && $this->_engine->is_cached($this->getTemplate(), $this->getCacheId()));
	}

	/**
	 * Devuelve el html dibujado
	 *
	 * @access public
	 * @return string
	 */
	public function getHtml() {
		return $this->_cleanHtml($this->_engine->fetch($this->getTemplate(), $this->getCacheId()));
	}

	/**
	 * Devuelve el html dibujado
	 *
	 * @access public
	 * @return string
	 */
	public function showHtml() {
		print $this->getHtml();
	}
}
