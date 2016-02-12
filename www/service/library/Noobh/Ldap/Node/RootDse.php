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
 * @see Noobh_Ldap_Node_Abstract
 */
require_once 'Noobh/Ldap/Node/Abstract.php';

/**
 * Noobh_Ldap_Node_RootDse provides a simple data-container for the RootDSE node.
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage RootDSE
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Node_RootDse extends Noobh_Ldap_Node_Abstract
{
    const SERVER_TYPE_GENERIC         = 1;
    const SERVER_TYPE_OPENLDAP        = 2;
    const SERVER_TYPE_ACTIVEDIRECTORY = 3;
    const SERVER_TYPE_EDIRECTORY      = 4;

    /**
     * Factory method to create the RootDSE.
     *
     * @param  Noobh_Ldap $ldap
     * @return Noobh_Ldap_Node_RootDse
     * @throws Noobh_Ldap_Exception
     */
    public static function create(Noobh_Ldap $ldap)
    {
        $dn = Noobh_Ldap_Dn::fromString('');
        $data = $ldap->getEntry($dn, array('*', '+'), true);
		if (isset($data['structuralobjectclass']) &&
                $data['structuralobjectclass'][0] === 'OpenLDAProotDSE') {
            /**
             * @see Noobh_Ldap_Node_RootDse_OpenLdap
             */
            require_once 'Noobh/Ldap/Node/RootDse/OpenLdap.php';
            return new Noobh_Ldap_Node_RootDse_OpenLdap($dn, $data);
        } else {
            return new self($dn, $data);
        }
    }

    /**
     * Constructor.
     *
     * Constructor is protected to enforce the use of factory methods.
     *
     * @param  Noobh_Ldap_Dn $dn
     * @param  array        $data
     */
    protected function __construct(Noobh_Ldap_Dn $dn, array $data)
    {
        parent::__construct($dn, $data, true);
    }

    /**
     * Gets the namingContexts.
     *
     * @return array
     */
    public function getNamingContexts()
    {
        return $this->getAttribute('namingContexts', null);
    }

    /**
     * Gets the subschemaSubentry.
     *
     * @return string|null
     */
    public function getSubschemaSubentry()
    {
        return $this->getAttribute('subschemaSubentry', 0);
    }

    /**
     * Determines if the version is supported
     *
     * @param  string|int|array $versions version(s) to check
     * @return boolean
     */
    public function supportsVersion($versions)
    {
        return $this->attributeHasValue('supportedLDAPVersion', $versions);
    }

    /**
     * Determines if the sasl mechanism is supported
     *
     * @param  string|array $mechlist SASL mechanisms to check
     * @return boolean
     */
    public function supportsSaslMechanism($mechlist)
    {
        return $this->attributeHasValue('supportedSASLMechanisms', $mechlist);
    }

    /**
     * Gets the server type
     *
     * @return int
     */
    public function getServerType()
    {
        return self::SERVER_TYPE_GENERIC;
    }

    /**
     * Returns the schema DN
     *
     * @return Noobh_Ldap_Dn
     */
    public function getSchemaDn()
    {
        $schemaDn = $this->getSubschemaSubentry();
        /**
         * @see Noobh_Ldap_Dn
         */
        require_once 'Noobh/Ldap/Dn.php';
        return Noobh_Ldap_Dn::fromString($schemaDn);
    }
}