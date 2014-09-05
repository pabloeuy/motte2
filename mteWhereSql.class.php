<?php
/**
 * Class to manage SQL WHERE clause
 *
 * @filesource
 * @package    motte
 * @version    1.0
 * @license    http://opensource.org/licenses/gpl-license.php GPL - GNU Public license
 * @author     Pedro Gauna (pgauna@gmail.com) /
 *             Carlos Gagliardi (carlosgag@gmail.com) /
 *             Braulio Rios (braulioriosf@gmail.com) /
 *             Pablo Erartes (pabloeuy@gmail.com) /
 *             GBoksar/Perro (gustavo@boksar.info)
 */

    class mteWhereSql {

     /**
      * Array Where condition
      *
      * @var array
      * @access private
      */
        private $_arrayCond;

     /**
      * construct()
      *
      * @access public
      * @return mteWhereSql
      */
        public function __construct() {
            $this->clear();
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
      * _addCondArray()
      * Stores condition in arrayCond property
      *
      * @access 	private
      * @param 	string  $cond
      * @return 	void
      */
        private function _addCondArray($cond = ''){
            if($cond != ''){
                $this->_arrayCond[] = $cond;
            }
        }


     /**
      * addCond()
      * Add new conditions to arrayCond
      *
      * @access private
      * @param	string	$op	string 'AND' or 'OR'
      * @param	boolean	$deny
      * @param 	string 	$value1
      * @param 	string 	$opRel
      * @param 	string 	$value2
      * @return void
      */
        private function _addCond($op = 'AND',$deny = false,$value1, $opRel, $value2){
            $condition = $this->_createRel($value1,$opRel,$value2);
            if ($deny){
                $condition = 'NOT('.$condition.')';
            }
            if ($op == 'OR'){
                $condition = ' OR ('.$condition.')';
            }
            else{
                $condition = ' AND ('.$condition.')';
            }
            $this->_addCondArray($condition);
        }

     /**
      * _createRel()
      * Returns string with RELATIONS included
      *
      * @access private
      * @param 	string 	$value1
      * @param 	string 	$opRel
      * @param 	string 	$value2
      * @return string
      */
        private function _createRel($value1,$opRel,$value2){
            $relation = '';
            $simpleOption = array('=','<','>','<=','>=', '<>');
            if(in_array($opRel,$simpleOption)){
                $relation = $value1.$opRel.$value2;
            }
            else{
                switch ($opRel){
                    case 'LIKE_IN':
                    {
                        $relation = $value1." LIKE '%".$value2."%'";
                        break;
                    }
                    case 'LIKE_INI':
                    {
                        $relation = $value1." LIKE '%".$value2."'";
                        break;
                    }
                    case 'LIKE_END':
                    {
                        $relation = $value1." LIKE '".$value2."%'";
                        break;
                    }
                }
            }
            return $relation;
        }

     /**
      * addAND()
      * Creates an AND expression
      *         AND : AND ($value1 $operator $value2)
      *
      * @access public
      * @param string 	$value1
      * @param string 	$opRel
      * @param string 	$value2
      * @param boolean	$deny
      * @return void
      */
        public function addAND($value1, $opRel, $value2, $deny = false){
            $this->_addCond('AND',$deny,$value1,$opRel,$value2);
        }

     /**
      * addOR()
      * Creates an OR expression
      *         OR : OR ($value1 $operator $value2)
      *
      * @access public
      * @param string 	$value1
      * @param string 	$opRel
      * @param string 	$value2
      * @param boolean	$deny
      * @return void
      */
        public function addOR($value1, $opRel, $value2, $deny = false){
            $this->_addCond('OR',$deny,$value1,$opRel,$value2);
        }

     /**
      * clear()
      * Clear WHERE Clause
      *
      * @access public
      * @return void
      */
        public function clear(){
            $this->_arrayCond=array();
            $this->_addCondArray('(1=1)');
        }

     /**
      * fetch()
      * Returns complete SQL Condition from arrayCond
      *
      * @access public
      * @return string
      */
        public function fetch(){
            return $this->optimizeCondition((implode("",$this->_arrayCond)));
        }

     /**
      * optimize condition
      *
      * @param string $where
      * @return string
      */
        public function optimizeCondition($where){
            $aux = str_replace('(1=1) AND ', '', $where);
            $result = $where;
            if (trim($aux) != ''){
                $result = $aux;
            }
            return $result;
        }
  }
?>