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
 * @see Noobh_Ldap_Filter_Abstract
 */
require_once 'Noobh/Ldap/Filter/Abstract.php';
/**
 * @see Noobh_Ldap_Filter_String
 */
require_once 'Noobh/Ldap/Filter/String.php';

/**
 * Noobh_Ldap_Filter_Logical provides a base implementation for a grouping filter.
 *
 * @uses       Noobh
 * @package    Noobh_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
abstract class Noobh_Ldap_Filter_Logical extends Noobh_Ldap_Filter_Abstract
{
    const TYPE_AND = '&';
    const TYPE_OR  = '|';

    /**
     * All the sub-filters for this grouping filter.
     *
     * @var array
     */
    private $_subfilters;

    /**
     * The grouping symbol.
     *
     * @var string
     */
    private $_symbol;

    /**
     * Creates a new grouping filter.
     *
     * @param array  $subfilters
     * @param string $symbol
     */
    protected function __construct(array $subfilters, $symbol)
    {
        foreach ($subfilters as $key => $s) {
            if (is_string($s)) $subfilters[$key] = new Noobh_Ldap_Filter_String($s);
            else if (!($s instanceof Noobh_Ldap_Filter_Abstract)) {
                /**
                 * @see Noobh_Ldap_Filter_Exception
                 */
                require_once 'Noobh/Ldap/Filter/Exception.php';
                throw new Noobh_Ldap_Filter_Exception('Only strings or Noobh_Ldap_Filter_Abstract allowed.');
            }
        }
        $this->_subfilters = $subfilters;
        $this->_symbol = $symbol;
    }

    /**
     * Adds a filter to this grouping filter.
     *
     * @param  Noobh_Ldap_Filter_Abstract $filter
     * @return Noobh_Ldap_Filter_Logical
     */
    public function addFilter(Noobh_Ldap_Filter_Abstract $filter)
    {
        $new = clone $this;
        $new->_subfilters[] = $filter;
        return $new;
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        $return = '(' . $this->_symbol;
        foreach ($this->_subfilters as $sub) $return .= $sub->toString();
        $return .= ')';
        return $return;
    }
}