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
 * Noobh_Ldap_Filter_String provides a simple custom string filter.
 *
 * @uses       Noobh
 * @package    Noobh_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Filter_String extends Noobh_Ldap_Filter_Abstract
{
    /**
     * The filter.
     *
     * @var string
     */
    protected $_filter;

    /**
     * Creates a Noobh_Ldap_Filter_String.
     *
     * @param string $filter
     */
    public function __construct($filter)
    {
        $this->_filter = $filter;
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        return '(' . $this->_filter . ')';
    }
}