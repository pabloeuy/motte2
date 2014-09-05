<?php
/**
 * mteCrud
 *
 * @filesource
 * @package motte
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com) /
 * 			Braulio Rios (braulioriosf@gmail.com) /
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */

// export type
define('EXPORT_HTML', 'html');
define('EXPORT_CSV', 'csv');
define('EXPORT_PDF', 'pdf');
define('EXT_SEPARATOR', ',');
define('EXT_FINLINEA', "\n");


class mteCrud {
	// Actions
	const ACTION_GRID        = 'grid';
	const ACTION_NEW         = 'add';
	const ACTION_EDIT        = 'edit';
	const ACTION_DEL         = 'del';
	const ACTION_VIEW        = 'view';
	const ACTION_VIEW_HIDDEN = 'hidden';
	const ACTION_EXPORT      = 'export';
	const ACTION_FILTER      = 'filter';
	const ACTION_SUCCESS     = 'success';
	const ACTION_CONFIRM_DEL = 'confirmdel';

	private $_crud_id;
	private $_sourceData;
	private $_fieldsGrid;
	private $_expLogicalDel;
	private $_valLogicalDel;
	private $_htmlGrid;
	private $_htmlError;
	private $_htmlNotification;
	private $_fields;
	private $_labels;
	private $_actions;
	private $_fieldsPrimaryKey;
	private $_defualtWhere;
	private $_pagination;

	/**
	 * Constructor
	 *
	 * @access public
	 * @return ctrDrefault
	 */
	public function __construct($id, $sourceData) {
		$this->_sourceData = $sourceData;
		$this->_crud_id    = $id;

		// Initialize
		$this->_expLogicalDel    = '';
		$this->_fieldLogicalDel  = '';
		$this->_valLogicalDel    = '';
		$this->_defualtWhere     = '';
		$this->_pagination       = true;
		$this->_fieldsPrimaryKey = array();
		$this->_labels           = array();
		$this->_fields           = array();
		$this->_fieldsGrid       = array();
		$this->_htmlGrid         = DIR_MOTTE.'/tpl/mteGrid.html';
		$this->_htmlError        = DIR_MOTTE.'/tpl/mteError.html';
		$this->_htmlNotification = DIR_MOTTE.'/tpl/mteNotification.html';
		$this->_cantRows         = ROWS_GRID;
		$this->_htmlForm         = '';
		$this->_readonly         = false;

		// Actions
		$this->_actions[mteCrud::ACTION_NEW]         = true;
		$this->_actions[mteCrud::ACTION_EDIT]        = true;
		$this->_actions[mteCrud::ACTION_DEL]         = true;
		$this->_actions[mteCrud::ACTION_VIEW]        = true;
		$this->_actions[mteCrud::ACTION_VIEW_HIDDEN] = true;
		$this->_actions[mteCrud::ACTION_EXPORT]      = true;
		$this->_actions[mteCrud::ACTION_FILTER]      = true;

		// fields
		if ($this->_sourceData instanceof mteTableSql) {
			// fields & labels
			$this->_fields     = $this->_sourceData->getFields();
			$this->_fieldsGrid = $this->_fields;
			$this->_labels     = $this->_sourceData->getLabels();

			foreach ($this->_fields as $fieldname) {
				if (!isset($this->_labels[$fieldname])) {
					$this->_labels[$fieldname] = $fieldname;
				}
			}

			// Logical delete
			$this->_fieldLogicalDel = $this->_sourceData->getFieldLogicalDelete();
			$this->_valLogicalDel   = $this->_sourceData->getValueLogicalDelete();

			if ($this->_fieldLogicalDel != '' || $this->_valLogicalDel != '') {
				$this->_expLogicalDel   = $this->_getFieldName($this->_sourceData->getFieldLogicalDelete()).' <> '.
										  $this->_sourceData->getValueLogicalDelete();
			}

			// Fields Key
			$this->_fieldsPrimaryKey = $this->_sourceData->getFieldsKey();
		}
	}

	/**
	 * Destructor
	 *
	 * @access public
	 */
	public function __destruct() {
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                  P R O P E R T I E S
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function setExpLogicalDelete($f, $v = '') {
		$this->_expLogicalDel = $f;
	}

	public function setFieldLogicalDelete($f) {
		$this->_fieldLogicalDel = $f;
	}

	public function setValueLogicalDelete($f) {
		$this->_valLogicalDel = $f;
	}

	public function setTemplateGrid($h) {
		$this->_htmlGrid = $h;
	}

	public function setTemplateForm($h) {
		$this->_htmlForm = $h;
	}

	public function setTemplateError($h) {
		$this->_htmlError = $h;
	}

	public function setTemplateNotification($h) {
		$this->_htmlNotification = $h;
	}

	public function setFields($a) {
		$this->_fields = $a;
	}

	public function setLabels($a) {
		$this->_labels = $a;
	}

	public function setFieldsKey($a) {
		$this->_fieldsPrimaryKey = $a;
	}

	public function onAction($a) {
		if (isset($this->_actions[$a])) {
			$this->_actions[$a] = true;
		}
	}

	public function offAction($a) {
		if (isset($this->_actions[$a])) {
			$this->_actions[$a] = false;
		}
	}

	public function onPagination() {
		$this->_pagination = true;
	}

	public function offPagination() {
		$this->_pagination = false;
	}

	public function setFieldsGrid($a) {
		return $this->_fieldsGrid = $a;
	}

	public function setDefaultFilter($a) {
		$this->_defualtWhere = $a;
	}

	public function setLabel($fieldname, $label) {
		$this->_labels[$fieldname] = $label;
	}

	public function setCantRowsGrid($a) {
		$this->_cantRows = $a;
	}

	public function setOrder($orderField, $orderDirection = 'asc') {
		$this->_setCrudParam('orderField', $orderField );
		$this->_setCrudParam('orderDirection', $orderDirection);
	}

	public function onEditable() {
		$this->_readonly = false;
	}

	public function offEditable() {
		$this->_readonly = true;
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//               P R I V A T E
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	private function _getErrorHtml($txt)  {
		$tpl = mteCtr::get()->getTemplate($this->_htmlError);
		$tpl->setVar('ERROR', $txt);
		return $tpl->getHtml();
	}

	private function _getNotificationHtml($txt)  {
		$tpl = mteCtr::get()->getTemplate($this->_htmlNotification);
		$tpl->setVar('MESSAGE', $txt);
		return $tpl->getHtml();
	}

	private function _getFieldName($field) {
		$result = '';
		if ($this->_sourceData instanceof mteTableSql) {
			$result = $this->_sourceData->getFieldName($field);
		}
		return $result;
	}

	private function _setCrudParam($var, $value) {
		tools::setSessionVar($this->_crud_id.$var, $value);
	}

	private function _getCrudParam($var) {
		return tools::getSessionVar($this->_crud_id.$var);
	}

	private function _getSqlWhere($filterValue, $fields) {

		// Search
		$where = '1 = 1';
		if ($filterValue != '') {
			$aux = array();
			foreach ($fields as $key => $fieldname) {
				$aux[] = $this->_getFieldName($fieldname)." LIKE '%$filterValue%'";
			}
			$where = '('.implode(' OR ', $aux).')';
		}

		// Logical Delete
		if ($this->_expLogicalDel != '') {
			if ($this->_getCrudParam('showHidden') == 0) {
				$where .= ' AND not('.$this->_expLogicalDel.')';
			}
		}
		return $where.($this->_defualtWhere != ''?' AND '.$this->_defualtWhere:'');
	}

	private function _getSqlOrder($orderField, $orderDirection) {
		return $this->_getFieldName($orderField).' '.strtoupper($orderDirection);
	}

	private function _form($record, $readonly = false, $error = '', $msg = '') {
		// frm
		$htmlFrm = '';
		if (is_readable($this->_htmlForm)) {
			$tplForm = mteCtr::get()->getTemplate($this->_htmlForm);
			$tplForm->setVar('RECORD', $record);
			$tplForm->setVar('READONLY', $readonly);
			$tplForm->setVar('ERROR', $this->_getErrorHtml($error));
			$tplForm->setVar('MESSAGE', $this->_getNotificationHtml($msg));
			$htmlFrm = $tplForm->getHtml();
		}
		return $htmlFrm;
	}

	private function _getRecord($id) {
		$values   = explode('|', $id);
		$nroField = 0;

		$record = array();
		if ($this->_sourceData instanceof mteTableSql) {
			if ($id == 0) {
				$record = $this->_sourceData->getEmptyRecord();
			}
			else {
				$aux = array();
				foreach ($this->_sourceData->getFieldsKey() as $field) {
					if (isset($values[$nroField])) {
						$aux[] = $field." = '".$values[$nroField]."'";
					}
					else {
						$aux[] = $field." = 'NODATA'";
					}
					$nroField++;
				}
				$record = $this->_sourceData->getRecord(implode(' AND ',$aux));
			}
		}

		return $record;
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                  N E W   R E C O R D
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function add() {
		return $this->_form($this->_getRecord(0));
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                 E D I T   R E C O R D
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function edit($id) {
		return $this->_form($this->_getRecord($id));
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//              D E L E T E   R E C O R D
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function del($id) {
		return $this->_form($this->_getRecord($id), true);
	}

	public function confirmdel($id) {
		$record = $this->_getRecord($id);
		$result = 'Internal error';

		if ($this->_sourceData instanceof mteTableSql) {
			$result = $this->_sourceData->deleteRecord($record);
		}
		return $result == ''?'OK':$this->_form($record, false, $result);
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                 S A V E   R E C O R D
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function save($id, $data) {
		$record = $this->_getRecord($id);
		$result = 'Internal error';

		if ($this->_sourceData instanceof mteTableSql) {
			$record = $this->_sourceData->toRecord($record, $data);

			if ($id == 0) {
				$result = $this->_sourceData->insertRecord($record);
			}
			else {
				$result = $this->_sourceData->updateRecord($record);
			}
		}
		return $result == ''?'OK':$this->_form($record, false, $result);
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                 V I E W   R E C O R D
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function view($id) {
		return $this->_form($this->_getRecord($id), true);
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                    G R I D
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public function initialize(){
		$this->_setCrudParam('filterValue', '' );
		$this->_setCrudParam('orderField', '' );
		$this->_setCrudParam('orderDirection', '');
		$this->_setCrudParam('showHidden', 0);
	}

	private function _getTotalPage($cantRows, $where){
		$result = 1;
		if ($this->_sourceData instanceof mteTableSql) {
			$result = $this->_sourceData->getTotalPages($cantRows, $where);
		}
		return $result;
	}

	private function _getDataGrid($where, $order, $cantRows = 0, $currentPage = 0) {
		$result = array();
		if ($this->_sourceData instanceof mteTableSql) {
			$result = array();

			if ($this->_pagination) {
				$dataSet = $this->_sourceData->getRecordSet('*', $where, $order, $cantRows, ($currentPage-1)*$cantRows);
			}
			else {
				$dataSet = $this->_sourceData->getRecordSet('*', $where, $order);
			}

			foreach ($dataSet->getArray() as $key => $record) {
				$aux = array();
				foreach ($this->_fieldsPrimaryKey as $field) {
					$aux[] = $record[$field];
				}
				// idRow
				$record['idRow'] = implode('|', $aux);

				// status
				$record['statusRow'] = -1;
				if ($this->_valLogicalDel != '' && $this->_fieldLogicalDel != '') {
					if (isset($record[$this->_fieldLogicalDel])) {
						$record['statusRow'] = $record[$this->_fieldLogicalDel] == $this->_valLogicalDel?'1':'0';
					}
				}

				$result[$key] = $record;
			}
		}
		return $result;
	}

	public function grid($fieldsGrid = '', $cantRows = 0) {
		// params
		$fieldsGrid = ($fieldsGrid == '')?$this->_fieldsGrid:$fieldsGrid;
		$cantRows   = $cantRows == 0?$this->_cantRows:$cantRows;

		// Logical delete
		if ($this->_expLogicalDel != '') {
			if (tools::getParam('SH') == '1' || tools::getParam('SH') == '0')  {
				$this->_setCrudParam('showHidden', tools::getParam('SH'));
			}
		}
		$showHidden = $this->_getCrudParam('showHidden');

		// filter
		if (tools::getParam('FF') != '')  {
			$this->_setCrudParam('filterValue', tools::getParam('FF')=='-'?'':tools::getParam('FF'));
		}
		$filterValue = $this->_getCrudParam('filterValue');

		// order
		$orderField     = $this->_getCrudParam('orderField');
		$orderDirection = $this->_getCrudParam('orderDirection');
		if ($orderField == '') {
			$aux = $fieldsGrid;
			$orderField     = array_shift($aux);
			$orderDirection = 'asc';
		}
		if (tools::getParam('SF') != '')  {
			$orderField = tools::getParam('SF');
			if (tools::getParam('SD') != '') {
				$orderDirection = 'asc';
				if (tools::getParam('SD') == 'asc' || tools::getParam('SD') == 'desc') {
					$orderDirection = tools::getParam('SD');
				}
			}
		}
		$this->setOrder($orderField, $orderDirection);

		// paged
		if ($this->_pagination) {
			$totalPage =  $this->_getTotalPage($cantRows, $this->_getSqlWhere($filterValue, $fieldsGrid));

			$currentPage = tools::getParam('P');
			if (!is_numeric($currentPage) || $currentPage < 1) {
				$currentPage = 1;
			}
			elseif ($currentPage > $totalPage) {
				$currentPage = $totalPage;
			}
		}
		else {
			$totalPage   = 1;
			$currentPage = 1;
		}

		$tpl = mteCtr::get()->getTemplate($this->_htmlGrid);
		$tpl->setVar('CRUD_ID', $this->_crud_id);
		$tpl->setVar('LABELS',  $this->_labels);
		$tpl->setVar('COLUMNS', $fieldsGrid);
		$tpl->setVar('ACTIONS', $this->_actions);
		$tpl->setVar('RECORDS', $this->_getDataGrid($this->_getSqlWhere($filterValue, $fieldsGrid),
													$this->_getSqlOrder($orderField, $orderDirection),
													$cantRows, $currentPage));
		$tpl->setVar('SHOW_HIDDEN', $showHidden);
		$tpl->setVar('EXP_LOGICAL', $this->_expLogicalDel);
		$tpl->setVar('FIELD_LOGICAL', $this->_fieldLogicalDel);
		$tpl->setVar('TEXT_FILTER', $filterValue);
		$tpl->setVar('PAGE_CURRENT', $currentPage);
		$tpl->setVar('PAGE_TOTAL', $totalPage);
		$tpl->setVar('ROWS_COUNT', $cantRows);
		$tpl->setVar('SORT_FIELD', $orderField);
		$tpl->setVar('SORT_DIRECTION', $orderDirection);
		$tpl->setVar('READONLY', $this->_readonly);

		return $tpl->getHtml();
	}

}
?>