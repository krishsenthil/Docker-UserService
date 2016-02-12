<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Framework
 * @package    Noobh_Auth
 * @subpackage Adapter
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 *
 * Collash Inc Internal
 * 
 * Base Noobh Auth class to which different Auth adapters are passed for authentication.
 * 
 * @TODO:After successful authentication,this class stores authenticated user information 
 * in session.
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package    Noobh_Auth
 * @subpackage Adapter
 * @since   0.1
 * @date Mar 28, 2012
 *
 */
 class Noobh_Auth{
     /**
     * Singleton instance
     *
     * @var Noobh_Auth
     */
    protected static $_instance = null;


    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @access protected
     * @param void
     * @return void
     */
    protected function __construct()
    {}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     * 
     * @access protected
     * @param void
     * @return void
     */
    protected function __clone()
    {}

    /**
     * Returns an instance of Noobh_Auth
     *
     * Singleton pattern implementation
     * 
     * @access public
     * @param void
     * @return Noobh_Auth Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    /**
     * Authenticates against the supplied adapter
     *
     * @param  Noobh_Auth_Adapter_Interface $adapter
     * @return Noobh_Auth_Result
     */
    public function authenticate(Noobh_Auth_Adapter_Interface $adapter)
    {
        return $adapter->authenticate();
    }
 }