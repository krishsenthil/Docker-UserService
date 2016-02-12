<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 * Noobh_Ldap_Filter_Abstract provides a base implementation for filters
 *
 * @uses       Noobh
 * @package    Noobh_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
abstract class Noobh_Ldap_Filter_Abstract
{
    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    abstract public function toString();

    /**
     * Returns a string representation of the filter.
     * @see toString()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Negates the filter.
     *
     * @return Noobh_Ldap_Filter_Abstract
     */
    public function negate()
    {
        /**
         * Noobh_Ldap_Filter_Not
         */
        require_once 'Noobh/Ldap/Filter/Not.php';
        return new Noobh_Ldap_Filter_Not($this);
    }

    /**
     * Creates an 'and' filter.
     *
     * @param  Noobh_Ldap_Filter_Abstract $filter,...
     * @return Noobh_Ldap_Filter_And
     */
    public function addAnd($filter)
    {
        /**
         * Noobh_Ldap_Filter_And
         */
        require_once 'Noobh/Ldap/Filter/And.php';
        $fa = func_get_args();
        $args = array_merge(array($this), $fa);
        return new Noobh_Ldap_Filter_And($args);
    }

    /**
     * Creates an 'or' filter.
     *
     * @param  Noobh_Ldap_Filter_Abstract $filter,...
     * @return Noobh_Ldap_Filter_Or
     */
    public function addOr($filter)
    {
        /**
         * Noobh_Ldap_Filter_Or
         */
        require_once 'Noobh/Ldap/Filter/Or.php';
        $fa = func_get_args();
        $args = array_merge(array($this), $fa);
        return new Noobh_Ldap_Filter_Or($args);
    }

    /**
     * Escapes the given VALUES according to RFC 2254 so that they can be safely used in LDAP filters.
     *
     * Any control characters with an ACII code < 32 as well as the characters with special meaning in
     * LDAP filters "*", "(", ")", and "\" (the backslash) are converted into the representation of a
     * backslash followed by two hex digits representing the hexadecimal value of the character.
     * @see Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
     * @link http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values Array of values to escape
     * @return array Array $values, but escaped
     */
    public static function escapeValue($values = array())
    {
        /**
         * @see Noobh_Ldap_Converter
         */
        require_once 'Noobh/Ldap/Converter.php';

        if (!is_array($values)) $values = array($values);
        foreach ($values as $key => $val) {
            // Escaping of filter meta characters
            $val = str_replace(array('\\', '*', '(', ')'), array('\5c', '\2a', '\28', '\29'), $val);
            // ASCII < 32 escaping
            $val = Noobh_Ldap_Converter::ascToHex32($val);
            if (null === $val) $val = '\0';  // apply escaped "null" if string is empty
            $values[$key] = $val;
        }
        return (count($values) == 1) ? $values[0] : $values;
    }

    /**
     * Undoes the conversion done by {@link escapeValue()}.
     *
     * Converts any sequences of a backslash followed by two hex digits into the corresponding character.
     * @see Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
     * @link http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values Array of values to escape
     * @return array Array $values, but unescaped
     */
    public static function unescapeValue($values = array())
    {
        /**
         * @see Noobh_Ldap_Converter
         */
        require_once 'Noobh/Ldap/Converter.php';

        if (!is_array($values)) $values = array($values);
        foreach ($values as $key => $value) {
            // Translate hex code into ascii
            $values[$key] = Noobh_Ldap_Converter::hex32ToAsc($value);
        }
        return (count($values) == 1) ? $values[0] : $values;
    }
}