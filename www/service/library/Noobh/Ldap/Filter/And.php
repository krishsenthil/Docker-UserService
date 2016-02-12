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
 * @see Noobh_Ldap_Filter_Logical
 */
require_once 'Noobh/Ldap/Filter/Logical.php';
/**
 * Noobh_Ldap_Filter_And provides an 'and' filter.
 *
 * @uses       Noobh
 * @package    Noobh_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Filter_And extends Noobh_Ldap_Filter_Logical
{
    /**
     * Creates an 'and' grouping filter.
     *
     * @param array $subfilters
     */
    public function __construct(array $subfilters)
    {
        parent::__construct($subfilters, self::TYPE_AND);
    }
}