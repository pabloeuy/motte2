<?php
/**
 * Class for reading HTML templates with OB
 *
 * @filesource
 * @package motte
 * @subpackage view
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */
class mteSimpleTemplate {

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
	private $_templateDir;

	/**
	 *
	 * @var array
	 * @access private
	 */
	private $_var;

	/**
	 *
	 * @var string
	 * @access private
	 */
	private $_blocks;

	public function __construct($template = '', $templateDir = '') {
		$this->setTemplateDir($templateDir);
		$this->setTemplate($template);
		$this->clearVars();
		$this->_blocks = array();
	}

	public function setTemplate($name = '') {
		$this->_template = $name;
	}

	public function getTemplate() {
		return $this->_template;
	}

	public function setTemplateDir($dir = '') {
		$this->_templateDir = $dir;
	}

	public function getTemplateDir() {
		return $this->_templateDir;
	}

	public function clearVar($varName) {
		$this->_engine->clear_assign($varName);
	}

	public function clearVars() {
		$this->_var = array();
	}
	public function includeBlock($block) {
		$this->_blocks[] = $block;
	}
	public function addVar($varName, $varValue) {
		$this->_var[$varName] = $varValue;
	}

	public function setVar($varName, $varValue) {
		$this->addVar($varName, $varValue);
	}
        
	public function setVars($vars) {
	foreach($vars as $k=>$v)
    	$this->setVar($k,$v);
	}

	public function appendVar($varName, $varValue) {
		$this->_var[$varName][] = $varValue;
	}

	public function getVar($varName) {
		return $this->_var[$varName];
	}

	public function getHtml() {
		$html = '';
		$file = $this->getTemplateDir() . '/' . $this->getTemplate();
		if (is_file($file)) {
			preg_match_all("(\\$[\w|\d]+)", file_get_contents($file), $vars);
			if (is_array($vars)) {
				foreach ($vars[0] as $key => $var) {
					$var = substr($var, 1);
					if (!array_key_exists($var, $this->_var)) {
						$this->setVar($var, '');
					}
				}
			}
			extract($this->_var);
			ob_start();
			include($this->getTemplateDir() . '/' . $this->getTemplate());
			$html = ob_get_clean();
		}
		return $html . implode('\n', $this->_blocks);
	}

	public function showHtml() {
		print $this->getHtml();
	}
}
?>