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


    static function input($type, $name, $value = null, $options = array())
    {
        if ( ! isset($options['name'])){
            $options['name'] = $name;
        }
        $options['type'] = $type;
        $options['id'] = self::getIdAttr($name, $options);

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

    private static function getIdAttr($name, $options)
    {
        if (array_key_exists('id', $options))
        {
            return $options['id'];
        }
        return $name;
    }

    private static function attributes($attributes)
    {
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

    protected static function htmlAttr($key, $value)
    {
        if ( ! is_null($value)){
            return $key.'="'.$value.'"';
        }else{
            return $key;
        }
    }

    static function label($name, $text = null, $options = array())
    {

        $options = self::attributes($options);

        $text = self::formatLbl($name, $text);

        return '<label for="'.$name.'"'.$options.'>'.$text.'</label>';
    }

    private static function formatLbl($name, $text)
    {
        if(!$text){
            return ucwords(
                    self::camelCaseToHumanString(
                        str_replace('_', ' ', $name)));
        }
        return $text;
    }

    static function memo($name, $value = null, $options = array())
    {
        if ( ! isset($options['name'])){
            $options['name'] = $name;
        }

        $options['id'] = self::getIdAttr($name, $options);

        $options = self::attributes($options);

        return '<textarea'.$options.'>'.$value.'</textarea>';
    }

    static function camelCaseToHumanString($string) {
            $regex = '/(?<=[a-z])(?=[A-Z])/x';
            $result = preg_split($regex, $string);
            return join($result, " " );
    }

    static function dropdown($name, $selectOptions = array(), $selected = null, $options = array())
    {
        $options['id'] = self::getIdAttr($name, $options);

        if ( ! isset($options['name'])){
            $options['name'] = $name;
        }

        $html = array();

        foreach ($selectOptions as $value => $label)
        {
            $html[] = self::option($value, $label, $selected);
        }

        $options = self::attributes($options);

        $selectOptions = implode('', $html);

        return "<select{$options}>{$selectOptions}</select>";
    }


    private static function option($value, $label, $selected)
    {
        $selected = $this->getSelValue($value, $selected);

        $options = array(
            'value' => $value,
            'selected' => $selected
        );

        return '<option'.self::attributes($options).'>'.$label.'</option>';
    }

    protected function getSelValue($value, $selected)
    {
        return ((string) $value == (string) $selected) ? 'selected' : null;
    }

}
?>

