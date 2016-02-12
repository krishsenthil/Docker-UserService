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
 * This interface is implemented in all Auth Adapters
 * used in Noobh frame work. You can declare common
 * variables as well in this interface
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package    Noobh_Auth
 * @subpackage Adapter
 * @since   0.1
 * @date Mar 28, 2012
 *
 */
 interface Noobh_Auth_Adapter_Interface {
 	/**
 	 * Authenticate the current user and store
 	 * user information in Auth Session. This should
 	 * be implemented in corresponding Auth Adapters
 	 * @access public
 	 * @param {void}
 	 * @return {Noobh_Auth_Result} $result
 	 */
     public function authenticate();
 }