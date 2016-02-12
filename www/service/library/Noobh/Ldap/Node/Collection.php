<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Node
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 * @see Noobh_Ldap_Collection
 */
require_once 'Noobh/Ldap/Collection.php';


/**
 * Noobh_Ldap_Node_Collection provides a collecion of nodes.
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @subpackage Node
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 */
class Noobh_Ldap_Node_Collection extends Noobh_Ldap_Collection
{
    /**
     * Creates the data structure for the given entry data
     *
     * @param  array $data
     * @return Noobh_Ldap_Node
     */
    protected function _createEntry(array $data)
    {
        /**
         * @see Noobh_Ldap_Node
         */
        require_once 'Noobh/Ldap/Node.php';
        $node = Noobh_Ldap_Node::fromArray($data, true);
        $node->attachLdap($this->_iterator->getLdap());
        return $node;
    }

    /**
     * Return the child key (DN).
     * Implements Iterator and RecursiveIterator
     *
     * @return string
     */
    public function key()
    {
        return $this->_iterator->key();
    }
}