<?php
/**
 * Class for Motte internationalization
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

class mtei18n {
    private $_domain;
    private $l10n;
    private $merged_filters;
    private $mte_filter;

    // constructor
    public function __construct($lang, $langDir, $textDomain) {
        // load .mo
        $this->_domain = $textDomain;
        $mofile = $langDir.'/locale/'.$lang.'/LC_MESSAGES/'.$textDomain.'.mo';
        if (!isset($this->l10n[$textDomain])){
            if (is_readable($mofile)){
                $this->l10n[$textDomain] = new gettext_reader(new CachedFileReader($mofile));
            }
            else {
                die('Languague file does not exists '.$mofile);
            }
        }
    }

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct(){
    }

    /**
     *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
     *                           M E T H O D S
     *  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
     */
    public function getTextDomain() {
        return $this->_domain;
    }
    /**
     * _getLocale()
     * Guess browser locale settings and set it up as app lang.
     *
     * @return array
     */
    private function _getLocale() {
        if (isset($this->lang)){
            return $this->_applyFilters('locale', $lang);
        }
        $locale = $this->_applyFilters('locale', $lang);
        return $lang;
    }

    /**
     * i18n()
     * Internazionalization function. Based on .mo files and gettext.
     *
     * @param string $text
     * @return string
     */
    public function i18n($text) {
        $domain = $this->getTextDomain();
        if (isset($this->l10n[$domain])){
			return $this->_applyFilters('gettext', $this->l10n[$domain]->translate($text));
        }
        else{
            return $text;
        }
    }

    public function _($text) {
        return($this->i18n($text));
    }

    private function _applyFilters($tag, $string) {
        if (!isset( $this->mergedFilters[$tag])){
            $this->_mergeFilters($tag);
        }

        if (!isset($this->mte_filter[$tag])){
            return $string;
        }

        reset( $this->mte_filter[ $tag ] );
        $args = func_get_args();

        do{
            foreach((array) current($this->mte_filter[$tag]) as $the_)
            if ( !is_null($the_['function']) ){
                $args[1] = $string;
                $string = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
            }
        } while ( next($this->mte_filter[$tag]) !== false );
        return $string;
    }

    private function _mergeFilters($tag) {
        if ( isset($this->mte_filter['all']) && is_array($this->mte_filter['all']) ){
            $this->mte_filter[$tag] = array_merge($this->mte_filter['all'], (array) $this->mte_filter[$tag]);
        }

        if ( isset($this->mte_filter[$tag]) ){
            reset($this->mte_filter[$tag]);
            uksort($this->mte_filter[$tag], "strnatcasecmp");
        }
        $this->mergedFilters[ $tag ] = true;
    }
}
?>