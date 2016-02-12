<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 * Noobh_Ldap_Node_Schema_ObjectClass_Interface provides a contract for schema objectClasses.
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
interface Noobh_Ldap_Node_Schema_ObjectClass_Interface
{
    /**
     * Gets the objectClass name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the objectClass OID
     *
     * @return string
     */
    public function getOid();

    /**
     * Gets the attributes that this objectClass must contain
     *
     * @return array
     */
    public function getMustContain();

    /**
     * Gets the attributes that this objectClass may contain
     *
     * @return array
     */
    public function getMayContain();

    /**
     * Gets the objectClass description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Gets the objectClass type
     *
     * @return integer
     */
    public function getType();

    /**
     * Returns the parent objectClasses of this class.
     * This includes structural, abstract and auxiliary objectClasses
     *
     * @return array
     */
    public function getParentClasses();
}