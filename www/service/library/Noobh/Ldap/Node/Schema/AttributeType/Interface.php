<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 * Noobh_Ldap_Node_Schema_AttributeType_Interface provides a contract for schema attribute-types.
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
interface Noobh_Ldap_Node_Schema_AttributeType_Interface
{
    /**
     * Gets the attribute name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the attribute OID
     *
     * @return string
     */
    public function getOid();

    /**
     * Gets the attribute syntax
     *
     * @return string
     */
    public function getSyntax();

    /**
     * Gets the attribute maximum length
     *
     * @return int|null
     */
    public function getMaxLength();

    /**
     * Returns if the attribute is single-valued.
     *
     * @return boolean
     */
    public function isSingleValued();

    /**
     * Gets the attribute description
     *
     * @return string
     */
    public function getDescription();
}