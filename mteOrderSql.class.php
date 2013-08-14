<?php
/**
 * Class for managing ORDER clause for SQL sentences
 *
 * @filesource
 * @package    motte
 * @subpackage model
 * @version    1.0
 * @license    http://opensource.org/licenses/gpl-license.php GPL - GNU Public license
 * @author     Pedro Gauna (pgauna@gmail.com) /
 *             Carlos Gagliardi (carlosgag@gmail.com) /
 *             Braulio Rios (braulioriosf@gmail.com) /
 *             Pablo Erartes (pabloeuy@gmail.com) /
 *             GBoksar/Perro (gustavo@boksar.info)
 * @link       http://motte.codigolibre.net Motte Website
 */
    class mteOrderSql {

     /**
      * Estructura auxiliar para guardar prden
      *
      * @access public
      * @var _order
      */
        private $_order;


     /**
      * construct()
      *
      * @access public
      * @return void
      */
        public function __construct() {
        }

     /**
      * destruct()
      *
      * @access public
      * @return void
      */
        public function __destruct(){
        }

     /**
     * Add ASC to ORDER clause
     *
     * @access 	public
     * @param 	string fieldName
     * @return 	void
     */
        public function addAsc($fieldName=''){
            if (!is_null($fieldName)){
                $this->_order[] = $fieldName;
            }
        }

     /**
      * add DESC to ORDER clause
      *
      * @access public
      * @param 	string fieldName
      * @return void
      */
        public function addDesc($fieldName=''){
            if (!is_null($fieldName)){
                $this->_order[] = $fieldName.' DESC';
            }
        }

     /**
      * Returns a SQL sentence
      *
      * @param
      * @return string
      */
        public function fetch(){
            $result = '';
            if (is_array($this->_order)){
                $result = implode(', ',$this->_order);
            }
            return $result;
        }
    }
?>