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
 * @see Noobh_Ldap_Node_Schema_ObjectClass_Interface
 */
require_once 'Noobh/Ldap/Node/Schema/ObjectClass/Interface.php';

/**
 * Noobh_Ldap_Node_Schema_ObjectClass_OpenLdap provides access to the objectClass
 * schema information on an OpenLDAP server.
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Node_Schema_ObjectClass_OpenLdap extends Noobh_Ldap_Node_Schema_Item
    implements Noobh_Ldap_Node_Schema_ObjectClass_Interface
{
    /**
     * All inherited "MUST" attributes
     *
     * @var array
     */
    protected $_inheritedMust = null;
    /**
     * All inherited "MAY" attributes
     *
     * @var array
     */
    protected $_inheritedMay = null;


    /**
     * Gets the objectClass name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the objectClass OID
     *
     * @return string
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * Gets the attributes that this objectClass must contain
     *
     * @return array
     */
    public function getMustContain()
    {
        if ($this->_inheritedMust === null) {
            $this->_resolveInheritance();
        }
        return $this->_inheritedMust;
    }

    /**
     * Gets the attributes that this objectClass may contain
     *
     * @return array
     */
    public function getMayContain()
    {
        if ($this->_inheritedMay === null) {
            $this->_resolveInheritance();
        }
        return $this->_inheritedMay;
    }

    /**
     * Resolves the inheritance tree
     *
     * @return void
     */
    protected function _resolveInheritance()
    {
        $must = $this->must;
        $may = $this->may;
        foreach ($this->getParents() as $p) {
            $must = array_merge($must, $p->getMustContain());
            $may = array_merge($may, $p->getMayContain());
        }
        $must = array_unique($must);
        $may = array_unique($may);
        $may = array_diff($may, $must);
        sort($must, SORT_STRING);
        sort($may, SORT_STRING);
        $this->_inheritedMust = $must;
        $this->_inheritedMay = $may;
    }

    /**
     * Gets the objectClass description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->desc;
    }

    /**
     * Gets the objectClass type
     *
     * @return integer
     */
    public function getType()
    {
        if ($this->structural) {
            return Noobh_Ldap_Node_Schema::OBJECTCLASS_TYPE_STRUCTURAL;
        } else if ($this->abstract) {
            return Noobh_Ldap_Node_Schema::OBJECTCLASS_TYPE_ABSTRACT;
        } else if ($this->auxiliary) {
            return Noobh_Ldap_Node_Schema::OBJECTCLASS_TYPE_AUXILIARY;
        } else {
            return Noobh_Ldap_Node_Schema::OBJECTCLASS_TYPE_UNKNOWN;
        }
    }

    /**
     * Returns the parent objectClasses of this class.
     * This includes structural, abstract and auxiliary objectClasses
     *
     * @return array
     */
    public function getParentClasses()
    {
        return $this->sup;
    }

    /**
     * Returns the parent object classes in the inhertitance tree if one exists
     *
     * @return array of Noobh_Ldap_Node_Schema_ObjectClass_OpenLdap
     */
    public function getParents()
    {
        return $this->_parents;
    }
}