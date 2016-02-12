<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 * Noobh_Ldap_Collection_Iterator_Default is the default collection iterator implementation
 * using ext/ldap
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Collection_Iterator_Default implements Iterator, Countable
{
    const ATTRIBUTE_TO_LOWER  = 1;
    const ATTRIBUTE_TO_UPPER  = 2;
    const ATTRIBUTE_NATIVE    = 3;

    /**
     * LDAP Connection
     *
     * @var Noobh_Ldap
     */
    protected $_ldap = null;

    /**
     * Result identifier resource
     *
     * @var resource
     */
    protected $_resultId = null;

    /**
     * Current result entry identifier
     *
     * @var resource
     */
    protected $_current = null;

    /**
     * Number of items in query result
     *
     * @var integer
     */
    protected $_itemCount = -1;

    /**
     * The method that will be applied to the attribute's names.
     *
     * @var  integer|callback
     */
    protected $_attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;

    /**
     * Constructor.
     *
     * @param  Noobh_Ldap $ldap
     * @param  resource  $resultId
     * @return void
     */
    public function __construct(Noobh_Ldap $ldap, $resultId)
    {
        $this->_ldap = $ldap;
        $this->_resultId = $resultId;
        $this->_itemCount = @ldap_count_entries($ldap->getResource(), $resultId);
        if ($this->_itemCount === false) {
            /**
             * @see Noobh_Ldap_Exception
             */
            require_once 'Noobh/Ldap/Exception.php';
            throw new Noobh_Ldap_Exception($this->_ldap, 'counting entries');
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the current result set
     *
     * @return bool
     */
    public function close()
    {
        $isClosed = false;
        if (is_resource($this->_resultId)) {
             $isClosed = @ldap_free_result($this->_resultId);
             $this->_resultId = null;
             $this->_current = null;
        }
        return $isClosed;
    }

    /**
     * Gets the current LDAP connection.
     *
     * @return Noobh_Ldap
     */
    public function getLdap()
    {
        return $this->_ldap;
    }

    /**
     * Sets the attribute name treatment.
     *
     * Can either be one of the following constants
     * - Noobh_Ldap_Collection_Iterator_Default::ATTRIBUTE_TO_LOWER
     * - Noobh_Ldap_Collection_Iterator_Default::ATTRIBUTE_TO_UPPER
     * - Noobh_Ldap_Collection_Iterator_Default::ATTRIBUTE_NATIVE
     * or a valid callback accepting the attribute's name as it's only
     * argument and returning the new attribute's name.
     *
     * @param  integer|callback $attributeNameTreatment
     * @return Noobh_Ldap_Collection_Iterator_Default Provides a fluent interface
     */
    public function setAttributeNameTreatment($attributeNameTreatment)
    {
        if (is_callable($attributeNameTreatment)) {
            if (is_string($attributeNameTreatment) && !function_exists($attributeNameTreatment)) {
                $this->_attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
            } else if (is_array($attributeNameTreatment) &&
                    !method_exists($attributeNameTreatment[0], $attributeNameTreatment[1])) {
                $this->_attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
            } else {
                $this->_attributeNameTreatment = $attributeNameTreatment;
            }
        } else {
            $attributeNameTreatment = (int)$attributeNameTreatment;
            switch ($attributeNameTreatment) {
                case self::ATTRIBUTE_TO_LOWER:
                case self::ATTRIBUTE_TO_UPPER:
                case self::ATTRIBUTE_NATIVE:
                    $this->_attributeNameTreatment = $attributeNameTreatment;
                    break;
                default:
                    $this->_attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
                    break;
            }
        }
        return $this;
    }

    /**
     * Returns the currently set attribute name treatment
     *
     * @return integer|callback
     */
    public function getAttributeNameTreatment()
    {
        return $this->_attributeNameTreatment;
    }

    /**
     * Returns the number of items in current result
     * Implements Countable
     *
     * @return int
     */
    public function count()
    {
        return $this->_itemCount;
    }

    /**
     * Return the current result item
     * Implements Iterator
     *
     * @return array|null
     * @throws Noobh_Ldap_Exception
     */
    public function current()
    {
        if (!is_resource($this->_current)) {
            $this->rewind();
        }
        if (!is_resource($this->_current)) {
            return null;
        }

        $entry = array('dn' => $this->key());
        $ber_identifier = null;
        $name = @ldap_first_attribute($this->_ldap->getResource(), $this->_current,
            $ber_identifier);
        while ($name) {
            $data = @ldap_get_values_len($this->_ldap->getResource(), $this->_current, $name);
            unset($data['count']);

            switch($this->_attributeNameTreatment) {
                case self::ATTRIBUTE_TO_LOWER:
                    $attrName = strtolower($name);
                    break;
                case self::ATTRIBUTE_TO_UPPER:
                    $attrName = strtoupper($name);
                    break;
                case self::ATTRIBUTE_NATIVE:
                    $attrName = $name;
                    break;
                default:
                    $attrName = call_user_func($this->_attributeNameTreatment, $name);
                    break;
            }
            $entry[$attrName] = $data;
            $name = @ldap_next_attribute($this->_ldap->getResource(), $this->_current,
                $ber_identifier);
        }
        ksort($entry, SORT_LOCALE_STRING);
        return $entry;
    }

    /**
     * Return the result item key
     * Implements Iterator
     *
     * @return string|null
     */
    public function key()
    {
        if (!is_resource($this->_current)) {
            $this->rewind();
        }
        if (is_resource($this->_current)) {
            $currentDn = @ldap_get_dn($this->_ldap->getResource(), $this->_current);
            if ($currentDn === false) {
                /** @see Noobh_Ldap_Exception */
                require_once 'Noobh/Ldap/Exception.php';
                throw new Noobh_Ldap_Exception($this->_ldap, 'getting dn');
            }
            return $currentDn;
        } else {
            return null;
        }
    }

    /**
     * Move forward to next result item
     * Implements Iterator
     *
     * @throws Noobh_Ldap_Exception
     */
    public function next()
    {
        if (is_resource($this->_current)) {
            $this->_current = @ldap_next_entry($this->_ldap->getResource(), $this->_current);
            /** @see Noobh_Ldap_Exception */
            require_once 'Noobh/Ldap/Exception.php';
            if ($this->_current === false) {
                $msg = $this->_ldap->getLastError($code);
                if ($code === Noobh_Ldap_Exception::LDAP_SIZELIMIT_EXCEEDED) {
                    // we have reached the size limit enforced by the server
                    return;
                } else if ($code > Noobh_Ldap_Exception::LDAP_SUCCESS) {
                     throw new Noobh_Ldap_Exception($this->_ldap, 'getting next entry (' . $msg . ')');
                }
            }
        }
    }

    /**
     * Rewind the Iterator to the first result item
     * Implements Iterator
     *
     * @throws Noobh_Ldap_Exception
     */
    public function rewind()
    {
        if (is_resource($this->_resultId)) {
            $this->_current = @ldap_first_entry($this->_ldap->getResource(), $this->_resultId);
            /** @see Noobh_Ldap_Exception */
            require_once 'Noobh/Ldap/Exception.php';
            if ($this->_current === false &&
                    $this->_ldap->getLastErrorCode() > Noobh_Ldap_Exception::LDAP_SUCCESS) {
                throw new Noobh_Ldap_Exception($this->_ldap, 'getting first entry');
            }
        }
    }

    /**
     * Check if there is a current result item
     * after calls to rewind() or next()
     * Implements Iterator
     *
     * @return boolean
     */
    public function valid()
    {
        return (is_resource($this->_current));
    }

}