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
 * @see Noobh_Ldap_Filter_String
 */
require_once 'Noobh/Ldap/Filter/String.php';


/**
 * Noobh_Ldap_Filter_Mask provides a simple string filter to be used with a mask.
 *
 * @uses       Noobh
 * @package    Noobh_Ldap
 * @subpackage Filter
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Filter_Mask extends Noobh_Ldap_Filter_String
{
    /**
     * Creates a Noobh_Ldap_Filter_String.
     *
     * @param string $mask
     * @param string $value,...
     */
    public function __construct($mask, $value)
    {
        $args = func_get_args();
        array_shift($args);
        for ($i = 0; $i<count($args); $i++) {
            $args[$i] = self::escapeValue($args[$i]);
        }
        $filter = vsprintf($mask, $args);
        parent::__construct($filter);
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        return $this->_filter;
    }
}