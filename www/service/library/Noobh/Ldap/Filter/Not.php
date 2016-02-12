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
 * Noobh_Ldap_Filter_Not provides a negation filter.
 *
 * @uses       Noobh
 * @package    Noobh_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Filter_Not extends Noobh_Ldap_Filter_Abstract
{
    /**
     * The underlying filter.
     *
     * @var Noobh_Ldap_Filter_Abstract
     */
    private $_filter;

    /**
     * Creates a Noobh_Ldap_Filter_Not.
     *
     * @param Noobh_Ldap_Filter_Abstract $filter
     */
    public function __construct(Noobh_Ldap_Filter_Abstract $filter)
    {
        $this->_filter = $filter;
    }

    /**
     * Negates the filter.
     *
     * @return Noobh_Ldap_Filter_Abstract
     */
    public function negate()
    {
        return $this->_filter;
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        return '(!' . $this->_filter->toString() . ')';
    }
}