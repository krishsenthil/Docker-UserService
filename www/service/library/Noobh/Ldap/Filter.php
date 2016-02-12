<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 * @see Noobh_Ldap_Filter_String
 */
require_once 'Noobh/Ldap/Filter/String.php';

/**
 * Noobh_Ldap_Filter.
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Filter extends Noobh_Ldap_Filter_String
{
    const TYPE_EQUALS         = '=';
    const TYPE_GREATER        = '>';
    const TYPE_GREATEROREQUAL = '>=';
    const TYPE_LESS           = '<';
    const TYPE_LESSOREQUAL    = '<=';
    const TYPE_APPROX         = '~=';

    /**
     * Creates an 'equals' filter.
     * (attr=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Noobh_Ldap_Filter
     */
    public static function equals($attr, $value)
    {
        return new self($attr, $value, self::TYPE_EQUALS, null, null);
    }

    /**
     * Creates a 'begins with' filter.
     * (attr=value*)
     *
     * @param  string $attr
     * @param  string $value
     * @return Noobh_Ldap_Filter
     */
    public static function begins($attr, $value)
    {
        return new self($attr, $value, self::TYPE_EQUALS, null, '*');
    }

    /**
     * Creates an 'ends with' filter.
     * (attr=*value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Noobh_Ldap_Filter
     */
    public static function ends($attr, $value)
    {
        return new self($attr, $value, self::TYPE_EQUALS, '*', null);
    }

    /**
     * Creates a 'contains' filter.
     * (attr=*value*)
     *
     * @param  string $attr
     * @param  string $value
     * @return Noobh_Ldap_Filter
     */
    public static function contains($attr, $value)
    {
        return new self($attr, $value, self::TYPE_EQUALS, '*', '*');
    }

    /**
     * Creates a 'greater' filter.
     * (attr>value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Noobh_Ldap_Filter
     */
    public static function greater($attr, $value)
    {
        return new self($attr, $value, self::TYPE_GREATER, null, null);
    }

    /**
     * Creates a 'greater or equal' filter.
     * (attr>=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Noobh_Ldap_Filter
     */
    public static function greaterOrEqual($attr, $value)
    {
        return new self($attr, $value, self::TYPE_GREATEROREQUAL, null, null);
    }

    /**
     * Creates a 'less' filter.
     * (attr<value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Noobh_Ldap_Filter
     */
    public static function less($attr, $value)
    {
        return new self($attr, $value, self::TYPE_LESS, null, null);
    }

    /**
     * Creates an 'less or equal' filter.
     * (attr<=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Noobh_Ldap_Filter
     */
    public static function lessOrEqual($attr, $value)
    {
        return new self($attr, $value, self::TYPE_LESSOREQUAL, null, null);
    }

    /**
     * Creates an 'approx' filter.
     * (attr~=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Noobh_Ldap_Filter
     */
    public static function approx($attr, $value)
    {
        return new self($attr, $value, self::TYPE_APPROX, null, null);
    }

    /**
     * Creates an 'any' filter.
     * (attr=*)
     *
     * @param  string $attr
     * @return Noobh_Ldap_Filter
     */
    public static function any($attr)
    {
        return new self($attr, '', self::TYPE_EQUALS, '*', null);
    }

    /**
     * Creates a simple custom string filter.
     *
     * @param  string $filter
     * @return Noobh_Ldap_Filter_String
     */
    public static function string($filter)
    {
        return new Noobh_Ldap_Filter_String($filter);
    }

    /**
     * Creates a simple string filter to be used with a mask.
     *
     * @param string $mask
     * @param string $value
     * @return Noobh_Ldap_Filter_Mask
     */
    public static function mask($mask, $value)
    {
        /**
         * Noobh_Ldap_Filter_Mask
         */
        require_once 'Noobh/Ldap/Filter/Mask.php';
        return new Noobh_Ldap_Filter_Mask($mask, $value);
    }

    /**
     * Creates an 'and' filter.
     *
     * @param  Noobh_Ldap_Filter_Abstract $filter,...
     * @return Noobh_Ldap_Filter_And
     */
    public static function andFilter($filter)
    {
        /**
         * Noobh_Ldap_Filter_And
         */
        require_once 'Noobh/Ldap/Filter/And.php';
        return new Noobh_Ldap_Filter_And(func_get_args());
    }

    /**
     * Creates an 'or' filter.
     *
     * @param  Noobh_Ldap_Filter_Abstract $filter,...
     * @return Noobh_Ldap_Filter_Or
     */
    public static function orFilter($filter)
    {
        /**
         * Noobh_Ldap_Filter_Or
         */
        require_once 'Noobh/Ldap/Filter/Or.php';
        return new Noobh_Ldap_Filter_Or(func_get_args());
    }

    /**
     * Create a filter string.
     *
     * @param  string $attr
     * @param  string $value
     * @param  string $filtertype
     * @param  string $prepend
     * @param  string $append
     * @return string
     */
    private static function _createFilterString($attr, $value, $filtertype, $prepend = null, $append = null)
    {
        $str = $attr . $filtertype;
        if ($prepend !== null) $str .= $prepend;
        $str .= self::escapeValue($value);
        if ($append !== null) $str .= $append;
        return $str;
    }

    /**
     * Creates a new Noobh_Ldap_Filter.
     *
     * @param string $attr
     * @param string $value
     * @param string $filtertype
     * @param string $prepend
     * @param string $append
     */
    public function __construct($attr, $value, $filtertype, $prepend = null, $append = null)
    {
        $filter = self::_createFilterString($attr, $value, $filtertype, $prepend, $append);
        parent::__construct($filter);
    }
}