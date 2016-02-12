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
 * @see Noobh_Ldap_Node_Schema_Item
 */
require_once 'Noobh/Ldap/Node/Schema/Item.php';
/**
 * @see Noobh_Ldap_Node_Schema_AttributeType_Interface
 */
require_once 'Noobh/Ldap/Node/Schema/AttributeType/Interface.php';

/**
 * Noobh_Ldap_Node_Schema_AttributeType_OpenLdap provides access to the attribute type
 * schema information on an OpenLDAP server.
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Node_Schema_AttributeType_OpenLdap extends Noobh_Ldap_Node_Schema_Item
    implements Noobh_Ldap_Node_Schema_AttributeType_Interface
{
    /**
     * Gets the attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the attribute OID
     *
     * @return string
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * Gets the attribute syntax
     *
     * @return string
     */
    public function getSyntax()
    {
        if ($this->syntax === null) {
            $parent = $this->getParent();
            if ($parent === null) return null;
            else return $parent->getSyntax();
        } else {
            return $this->syntax;
        }
    }

    /**
     * Gets the attribute maximum length
     *
     * @return int|null
     */
    public function getMaxLength()
    {
        $maxLength = $this->{'max-length'};
        if ($maxLength === null) {
            $parent = $this->getParent();
            if ($parent === null) return null;
            else return $parent->getMaxLength();
        } else {
            return (int)$maxLength;
        }
    }

    /**
     * Returns if the attribute is single-valued.
     *
     * @return boolean
     */
    public function isSingleValued()
    {
        return $this->{'single-value'};
    }

    /**
     * Gets the attribute description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->desc;
    }

    /**
     * Returns the parent attribute type in the inhertitance tree if one exists
     *
     * @return Noobh_Ldap_Node_Schema_AttributeType_OpenLdap|null
     */
    public function getParent()
    {
        if (count($this->_parents) === 1) {
            return $this->_parents[0];
        }
    }
}