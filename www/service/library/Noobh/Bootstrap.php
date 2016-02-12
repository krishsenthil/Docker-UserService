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
 * Base bootstrap file which should be extended to
 * all bootstrap files in the application.
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package    Noobh_Auth
 * @subpackage Adapter
 * @since   0.1
 * @date Mar 28, 2012
 *
 */

class Noobh_Bootstrap
{
    
    /**
     * Store config object
     * @access private
     * @var {Noobh_Config}
     */
    private $_config = NULL;
    /**
     * Get all methods in the application bootstrap
     * file and execute one by one. Execution dependecy 
     * is from top to bottom
     * @access public
     * @param {Noobh_Config} $config
     * @return void
     */
    public function __construct ($config = NULL)
    {   
        $methods = get_class_methods('Bootstrap');
        foreach ($methods as $method){
            if(preg_match('/^_init/', $method)){
                call_user_func(array($this,$method));
            }
        }
        $this->_config = $config;
    }
    
    /**
     * @todo Need to run all functions in the bootstartp
     * and read config
     */    
    public function load(){
        // Instantiate baisc resources
        $router = new Noobh_Application_Resource_Router($this->_config);
        /**
         * @todo: Need to register router or not in session, currently it is registred
         * but need to confirm
         */
                
    }

}
