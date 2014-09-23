<?php
/**
 * Tools for Html (static methods)
 *
 * @filesource
 * @package motte
 * @license GPLv2 http://opensource.org/licenses/gpl-license.php GNU Public license
 * @version 2.5
 * @author 	Maicol Bentancor (maibenta@correo.ucu.edu.uy)
 */
class mteHtml {


    /**
     * Empty value for forms
     */
    const EMPTY_VALUE_FORM = '';

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

	/******************************************************************************************************************************************
     * FORM INPUTS
     * ***************************************************************************************************************************************/

    /**
     * Generate a input with type, name, value and optional fields of html
     * @param  string $type    text, email, password, checkbox, file, url, etc
     * @param  string $name    the name that set the id and name input attribute
     * @param  mixed  $value   the value or model that contains the value by name
     * @param  array  $options additionals fields of html input
     * @return string the html input generated
     */
    static function input($type, $name, $value = null, $options = array()) {
        if ( ! isset($options['name'])){
            $options['name'] = $name;
        }
        $options['type'] = $type;
        $options['id'] = self::getIdAttr($name, $options);

        $value = self::getValue($value,$name);

        if($value){
            switch ($type) {
                case 'password':
                case 'file':
                    break;
                case 'checkbox':
                    $options['checked'] = 'checked';
                    break;
                case 'radio':
                    // TODO
                    break;
                default:
                    $options['value'] = $value;
            }
        }
        return '<input'.self::attributes($options).'>';
    }

    /**
     * Get Id according the options or name by default
     */
    private static function getIdAttr($name, $options) {
        if (array_key_exists('id', $options))
        {
            return $options['id'];
        }
        return $name;
    }

    /**
     * Generate Html attributes by array
     */
    private static function attributes($attributes) {
        $html = array();

        foreach ($attributes as $key => $value)
        {
            $element = self::htmlAttr($key, $value);
            if ( ! is_null($element)) {
                $html[] = $element;
            }
        }
        return count($html) > 0 ? ' '.implode(' ', $html) : '';
    }

    /**
     * Generate a html attribute by key-value
     */
    protected static function htmlAttr($key, $value) {
        if ( ! is_null($value)){
            return $key.'="'.$value.'"';
        }else{
            return $key;
        }
    }

    /**
     * Generate a label for input
     */
    static function label($name, $text = null, $options = array()) {

        $options = self::attributes($options);

        $text = self::formatLbl($name, $text);

        return '<label for="'.$name.'"'.$options.'>'.$text.'</label>';
    }

    /**
     * Format the text of a label according the text label or name by default
     */
    private static function formatLbl($name, $text) {
        if(!$text){
            return ucwords(
                    self::camelCaseToHumanString(
                        str_replace('_', ' ', $name)));
        }
        return $text;
    }

    /**
     * Generate a text area input
     */
    static function memo($name, $value = null, $options = array()) {
        if ( ! isset($options['name'])){
            $options['name'] = $name;
        }

        $value = self::getValue($value,$name);

        $options['id'] = self::getIdAttr($name, $options);

        $options = self::attributes($options);

        return '<textarea'.$options.'>'.$value.'</textarea>';
    }

    static function camelCaseToHumanString($string) {
            $regex = '/(?<=[a-z])(?=[A-Z])/x';
            $result = preg_split($regex, $string);
            return join($result, " " );
    }

    static function dropdown($name, $selectOptions = array(), $selected = null, $options = array()) {
        $options['id'] = self::getIdAttr($name, $options);

        if ( ! isset($options['name'])){
            $options['name'] = $name;
        }

        $html = array();

        foreach ($selectOptions as $value => $label)
        {
            $selected = self::getValue($selected,$name);
            $html[] = self::option($value, $label, $selected);
        }

        $options = self::attributes($options);

        $selectOptions = implode('', $html);

        return "<select{$options}>{$selectOptions}</select>";
    }


    private static function option($value, $label, $selected) {
        $selected = $this->getSelValue($value, $selected);

        $options = array(
            'value' => $value,
            'selected' => $selected
        );

        return '<option'.self::attributes($options).'>'.$label.'</option>';
    }

    private function getSelValue($value, $selected) {
        return ((string) $value == (string) $selected) ? 'selected' : null;
    }

    /**
     * Returns a value depending on whether it is an array or not (if so it's a model)
     */
    private static function getValue($value,$name) {
        if(!is_null($value)){

            if(is_array($value)){
                return isset($value[$name]) ? $value[$name] : self::EMPTY_VALUE_FORM;
            }

            return $value;
        }
        return self::EMPTY_VALUE_FORM;
    }

}
?>

