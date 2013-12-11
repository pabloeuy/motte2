<?php
/**
 * Tools (static methods)
 *
 * @filesource
 * @package motte
 * @subpackage tools
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.44
 * @author 	Pedro Gauna (pgauna@gmail.com)
 * 			Braulio Rios (braulioriosf@gmail.com)
 * 			Pablo Erartes (pabloeuy@gmail.com)
 */
class tools {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Destructor
	 */
	public function __destruct() {

	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//             M I S C E L L A N E O U S E
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public static function getIpClient(){
		$ipActiva = '';
		if (getenv("HTTP_X_FORWARDED_FOR")) {
			$ipActiva = getenv("HTTP_X_FORWARDED_FOR");
		}
		else {
			$ipActiva = getenv("REMOTE_ADDR");
		}
		return $ipActiva;
	}

	public static function arrayToMatrix($array, $cantCol, $emptyVal = ''){
 		$result = array();
 		while (count($array)%$cantCol != 0) {
 			$array[] = $emptyVal;
 		}
        $row = 1; $col = 1;
        foreach ($array as $record) {
            $result[$row][$col] = $record;
            $col++;
            if ($col > $cantCol) {
                $col = 1;
                $row++;
            }
        }
        return $result;
	}

	static function getAge($birth, $now = ''){
            date_default_timezone_set("UTC");
            $dob = date("Y-m-d",strtotime($birth));
            $dobObject = new DateTime($dob);
            $nowObject = new DateTime();
            $diff = $dobObject->diff($nowObject);
            return $diff->y;
            
		/*$result = 0;
		list($year, $month, $day) = explode("-", $birth);
		if (checkdate($month, $day, $year)){
			$now       = explode('-', ($now == ''?date('Y-m-d'):$now));
			$result    = (int)$now[0]-$year;
			$month_dif = (int)$now[1]-$month;
			$day_dif   = (int)$now[2]-$day;
			if ($day_dif < 0 || $month_dif < 0){
				$result--;
			}
		}
		return $result;*/
	}

	public static function sanitizeFileName($dangerous_filename) {
		$dangerous_characters = array(" ", '"', "'", "&", "/", "\\", "?", "#", "=");
		return str_replace($dangerous_characters, '', $dangerous_filename);
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                         D E V E L
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public static function dump($o) {
		return '<pre>' . print_r($o, true) . '</pre>';
	}

	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                   I N I   F I L E S
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public static function constant2inifile($file) {
		$const = get_defined_constants(true);
		$aux = array();
		foreach ($const['user'] as $key => $value) {
			$aux[] = $key.'='.$value."\n";
		}
		file_put_contents($file, $aux);
		@chmod($file, 0775);
	}

	public static function inifile2constant($file) {
		if (is_readable($file) && is_file($file)) {
			foreach (parse_ini_file($file) as $key => $value) {
				define(strtoupper($key), $value);
			}
		}
	}


	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                       H T T P
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public static function getParam($name) {
		return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : '');
	}

	public static function getPostVar($name) {
		return isset($_POST[$name]) ? $_POST[$name] : '';
	}

	public static function getGetVar($name) {
		return isset($_POST[$name]) ? $_POST[$name] : '';
	}

	public static function checkRemoteFile($url) {
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL,$url);
	    // don't download content
    	curl_setopt($ch, CURLOPT_NOBODY, 1);
	    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	return (curl_exec($ch)!== FALSE);
    }


	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                   S E S S I O N
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	public static function nameSession() {
		return !defined('NAME_SESSION')?'MOTTE':NAME_SESSION;
	}

	public static function setSessionVar($name, $value, $nameSession = '') {
		$_SESSION[$nameSession == ''?self::nameSession():$nameSession][$name] = $value;
	}

	public static function getSessionVar($name, $nameSession = '') {
		$nameSession = $nameSession == ''?self::nameSession():$nameSession;
		return isset($_SESSION[$nameSession][$name])?$_SESSION[$nameSession][$name]:'';
	}

	public static function unsetSessionVar($name, $nameSession = '') {
		unset($_SESSION[$nameSession == ''?self::nameSession():$nameSession][$name]);
	}

	public static function killSession($nameSession = '') {
		unset($_SESSION[$nameSession == ''?self::nameSession():$nameSession]);
	}


	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	//                   C A L E N D A R
	//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
	static function dmyToMySql($date){
		$aux = explode('/', $date);
		if (is_array($aux) && count($aux) == 3) {
			return $aux[2].'-'.$aux[1].'-'.$aux[0];
		}
		else {
			return '0000-00-00';
		}
	}

	static function mysqlTodmy($date){
		$aux = explode('-', $date);
		return $aux[2].'/'.$aux[1].'/'.$aux[0];
	}

	static function getYM($month, $year) {
		return $year . (((int) $month < 10) ? '0' . (int) $month : $month);
	}

	static function date2YM($date = '') {
		return substr(str_replace(array('-', '/', '_', ' '), '', ($date == '' ? date('Y-m-d') : $date)), 0, 6);
	}

	static function YMtoMY($ym) {
		return $ym[4] . $ym[5] . substr($ym, 0, 4);
	}

	static function YMtoString($ym, $separador = '/') {
		return $ym[4] . $ym[5] . $separador . substr($ym, 0, 4);
	}

	static function MYtoYM($ym) {
		return substr($ym, 2, 6) . $ym[0] . $ym[1];
	}

	static function validYM($ym) {
		return  (strlen($ym) == 6) &&
				(0 < ($m = (int) ($ym[4] . $ym[5]))) && ($m <= 12) &&
				(1990 <= ($y = (int) substr($ym, 0, 4))) && $y <= 2100;
	}

	static function getNameMonths() {
		return array(	__('Enero'), __('Febrero'), __('Marzo'),
						__('Abril'), __('Mayo'), __('Junio'), __('Julio'),
						__('Agosto'), __('Setiembre'), __('Octubre'),
						__('Noviembre'), __('Diciembre'));
	}

	static function getNameDays() {
		return array(	__('Domingo'), __('Lunes'), __('Martes'),
						__('Miercoles'), __('Jueves'), __('Viernes'),
						__('Sabado'));
	}

	static function getNameYM($ym) {
		$meses = tools::getNameMonths();
		return $meses[(int) ($ym[4] . $ym[5]) - 1] . ' - ' . substr($ym, 0, 4);
	}

	static function getNameMonth($m) {
		$meses = tools::getNameMonths();
		return $meses[(int)($m - 1)];
	}

	static function getYear($ym) {
		return (int) substr($ym, 0, 4);
	}

	static function getMonth($ym) {
		return (int) ($ym[4] . $ym[5]);
	}

	static function dayOfYear($y) {
		if (!checkdate(02, 29, $y)) {
			return 365;
		} else {
			return 364;
		}
	}

	static function getNumberWeekYear($ymd){
		return date('W', strtotime($ymd));
	}

	static function getWeekRange($ymd) {
	    $ts = strtotime($ymd);
	    $start = (date('w', $ts) == 0) ? $ts : strtotime('last sunday', $ts);
	    return array(date('Y-m-d', $start),
	                 date('Y-m-d', strtotime('next saturday', $start)));
	}

	static function getNameDayWeek($d) {
		$days = tools::getNameDays();
		return $days[$d];
	}
}
?>
