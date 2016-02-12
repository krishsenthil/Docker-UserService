<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage RootDSE
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 * @see Noobh_Ldap_Node_RootDse
 */
require_once 'Noobh/Ldap/Node/RootDse.php';

/**
 * Noobh_Ldap_Node_RootDse provides a simple data-container for the RootDSE node of
 * an OpenLDAP server.
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage RootDSE
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Node_RootDse_OpenLdap extends Noobh_Ldap_Node_RootDse
{
    /**
     * Gets the configContext.
     *
     * @return string|null
     */
    public function getConfigContext()
    {
        return $this->getAttribute('configContext', 0);
    }

    /**
     * Gets the monitorContext.
     *
     * @return string|null
     */
    public function getMonitorContext()
    {
        return $this->getAttribute('monitorContext', 0);
    }

    /**
     * Determines if the control is supported
     *
     * @param  string|array $oids control oid(s) to check
     * @return boolean
     */
    public function supportsControl($oids)
    {
        return $this->attributeHasValue('supportedControl', $oids);
    }

    /**
     * Determines if the extension is supported
     *
     * @param  string|array $oids oid(s) to check
     * @return boolean
     */
    public function supportsExtension($oids)
    {
        return $this->attributeHasValue('supportedExtension', $oids);
    }

    /**
     * Determines if the feature is supported
     *
     * @param  string|array $oids feature oid(s) to check
     * @return boolean
     */
    public function supportsFeature($oids)
    {
        return $this->attributeHasValue('supportedFeatures', $oids);
    }

    /**
     * Gets the server type
     *
     * @return int
     */
    public function getServerType()
    {
        return self::SERVER_TYPE_OPENLDAP;
    }
}