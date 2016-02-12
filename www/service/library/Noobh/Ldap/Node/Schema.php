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
 * @see Noobh_Ldap_Node_Abstract
 */
require_once 'Noobh/Ldap/Node/Abstract.php';

/**
 * Noobh_Ldap_Node_Schema provides a simple data-container for the Schema node.
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Node_Schema extends Noobh_Ldap_Node_Abstract
{
    const OBJECTCLASS_TYPE_UNKNOWN    = 0;
    const OBJECTCLASS_TYPE_STRUCTURAL = 1;
    const OBJECTCLASS_TYPE_ABSTRACT   = 3;
    const OBJECTCLASS_TYPE_AUXILIARY  = 4;

    /**
     * Factory method to create the Schema node.
     *
     * @param  Noobh_Ldap $ldap
     * @return Noobh_Ldap_Node_Schema
     * @throws Noobh_Ldap_Exception
     */
    public static function create(Noobh_Ldap $ldap)
    {
        $dn = $ldap->getRootDse()->getSchemaDn();
        $data = $ldap->getEntry($dn, array('*', '+'), true);
        switch ($ldap->getRootDse()->getServerType()) {
            case Noobh_Ldap_Node_RootDse::SERVER_TYPE_OPENLDAP:
                require_once 'Noobh/Ldap/Node/Schema/OpenLdap.php';
                return new Noobh_Ldap_Node_Schema_OpenLdap($dn, $data, $ldap);
            case Noobh_Ldap_Node_RootDse::SERVER_TYPE_EDIRECTORY:
            default:
                return new self($dn, $data, $ldap);
        }
    }

    /**
     * Constructor.
     *
     * Constructor is protected to enforce the use of factory methods.
     *
     * @param  Noobh_Ldap_Dn $dn
     * @param  array        $data
     * @param  Noobh_Ldap    $ldap
     */
    protected function __construct(Noobh_Ldap_Dn $dn, array $data, Noobh_Ldap $ldap)
    {
        parent::__construct($dn, $data, true);
        $this->_parseSchema($dn, $ldap);
    }

    /**
     * Parses the schema
     *
     * @param  Noobh_Ldap_Dn $dn
     * @param  Noobh_Ldap    $ldap
     * @return Noobh_Ldap_Node_Schema Provides a fluid interface
     */
    protected function _parseSchema(Noobh_Ldap_Dn $dn, Noobh_Ldap $ldap)
    {
        return $this;
    }

    /**
     * Gets the attribute Types
     *
     * @return array
     */
    public function getAttributeTypes()
    {
        return array();
    }

    /**
     * Gets the object classes
     *
     * @return array
     */
    public function getObjectClasses()
    {
        return array();
    }
}