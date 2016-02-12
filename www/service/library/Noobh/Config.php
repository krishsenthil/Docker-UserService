<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Framework
 * @package    Noobh
 * @subpackage    Noobh
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 * 
 * Collash Inc Internal
 * 
 * Allows for multi-dimensional ini files.
 *
 * The native parse_ini_file() function will convert the following ini file:...
 *
 * [production]
 * localhost.database.host = 1.2.3.4
 * localhost.database.user = root
 * localhost.database.password = abcdef
 * debug.enabled = false
 *
 * [development : production]
 * localhost.database.host = localhost
 * debug.enabled = true
 *
 * ...into the following array:
 *
 * array
 *   'localhost.database.host' => 'localhost'
 *   'localhost.database.user' => 'root'
 *   'localhost.database.password' => 'abcdef'
 *   'debug.enabled' => 1
 *
 * This class allows you to convert the specified ini file into a multi-dimensional
 * array. In this case the structure generated will be:
 *
 * array
 *   'localhost' =>
 *     array
 *       'database' =>
 *         array
 *           'host' => 'localhost'
 *           'user' => 'root'
 *           'password' => 'abcdef'
 *   'debug' =>
 *     array
 *       'enabled' => 1
 *
 * As you can also see you can have sections that extend other sections (use ":" for that).
 * The extendable section must be defined BEFORE the extending section or otherwise
 * you will get an exception.
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh_DB
 * @since   0.1
 * @date Mar 28, 2012
 * 
 */
class Noobh_Config
{
    /**
     * Internal storage array for config file
     * @access private
     * @var {array}
     */
    private $_iniFile = array();
    /**
     * Store singleton instance of config object
     * @access private
     * @var {Noobh_Config}
     */
    private static $_config;
    /**
     * Store current environment for config
     * @access private
     * @var {String}
     */
    private $_currentEnv;
    /**
     * Store top most priority environment in the
     * list application.ini list which will be stored
     * with the global values and overrided with applicaiton
     * specific global values. It can be production in most 
     * of the cases
     * @access private
     * @var {string}
     */
    private $_priorityEnv;
    
    /**
     * Loads in the ini file specified in filename, and returns the settings in
     * it as an associative multi-dimensional array or object
     *
     * @todo : Need to optimize at some point of time, when it grows bigger
     *      
     * @param mixed $configFiles, can be array of inis or single ini file path,
     *            The filename of the ini file being parsed
     * @throws Exception
     * @return {Noobh_Config}
     */
    private function __construct ($configFiles,$globalFiles,$currentEnv)
    {
       // load the raw ini file
       if(is_array($configFiles)){
        foreach($configFiles as $file){
        	if(!isset($ini)){
        		$ini = parse_ini_file($file, TRUE);
        	}else{
        		$ini = array_replace_recursive($ini,parse_ini_file($file, TRUE));
        	}
        }
       }else{
        $ini = parse_ini_file($configFiles, TRUE);
       }
       // fail if there was an error while processing the specified ini file
        if ($ini === false) {
            throw new Exception('There is no ini file.');
        }
        $sessionNames = array_keys($ini);
        $this->_priorityEnv = $sessionNames[0];
        /**
         * Fall back logic for active environment. Session passed
         * while calling the config class is having the highest priority.
         * If it is not present then it will fall back to APPLICATION_ENV
         * and if that constant is not present then it will be using the
         * top most environment in the application.ini file
         * 
         * If all the above conditions fail then it will exception
         */
        if(!$currentEnv){
            if(defined('APPLICATION_ENV')){
             $this->_currentEnv = APPLICATION_ENV;
            }else{
               //Set top most ini environment as the current environment
             	$envs = array_keys($ini);
             	if(count($envs) > 0){
             	    $this->_currentEnv = $envs[0];
             	}else{
             	    throw new Exception('No active environment variable for config to set');
             	}
            }
         }else{
              $this->_currentEnv = $currentEnv;
         }
         
         //Adding global config params and override with application configs
         $this->_setGlobalConfig($globalFiles);
         
        /**
         * loop the config and convert to object, Config overriding happens here
         *
         * @todo : Dynamic config changes
         */
        foreach ($ini as $index => $configSession) {
            $explode = explode(" : ", $index);
            if (count($explode) >= 2) {
                $currentEnv = $explode[0];
                $overrideEnv = $explode[1];     
            }else{
                $currentEnv = $explode[0];
            } 
            if(count($configSession) > 0){
                if(!array_key_exists($currentEnv,$this->_iniFile)){
                    $this->_iniFile[$currentEnv] = array();
                }
            }else{
                if(!array_key_exists($currentEnv,$this->_iniFile)){
                    $this->_iniFile[$currentEnv] = array();
                }
            }
            /**
             * Override global config with priority config
             */
            if($this->_priorityEnv == $currentEnv){
                $this->_iniFile[$currentEnv] =  array_replace_recursive($this->_iniFile[$currentEnv],$this->_iniSessionToArray($configSession));
               
            }else{
               $this->_iniFile[$currentEnv] = $this->_iniSessionToArray($configSession);
            }
            
            
            /**
             * Override current config session with active environment
             */
            if (isset($explode[1])) {
                if (isset($this->_iniFile[$overrideEnv])) {
                    if (count($this->_iniFile[$overrideEnv]) > 0 && count($this->_iniFile[$currentEnv]) > 0 ){
                        $this->_iniFile[$currentEnv] = array_replace_recursive($this->_iniFile[$overrideEnv],$this->_iniFile[$currentEnv]);
                    }else{
                        $this->_iniFile[$currentEnv] = $this->_iniFile[$overrideEnv];                        
                    }
                }
            }
         }
    }
    
    /**
     * Create a multidimention array from ini session
     * @access private
     * @param {array} $session
     * @return {array}  $result
     */
    private function _iniSessionToArray(array $session){
        $result = array();
        foreach($session as $key => $value){
           $explode = explode(".",$key);
           $resultSession = array();
           for($i= count($explode)-1 ; $i >= 0;){
               if(count($resultSession) == 0){
                   $resultSession[$explode[$i-1]][$explode[$i]] =   $value;
                   $i = $i-2;
               }else{
                   $temp = $resultSession;
                   unset($resultSession);
                   $resultSession[$explode[$i]] =  $temp;     
                   $i = $i -1;
               }
               
           }
           if(count($result) == 0){
               $result = $resultSession;
           }else{
               $result = array_merge_recursive($result,$resultSession);
           }
        }
        return $result;        
    }
    
    /**
     * This function is to support the existing global variables
     * and use them in the new applications.This read all the 
     * variables from the file and add it to the config list
     * dynamically.Global variables are added under 'production'
     * session in the config and will be overridden by applicaiton
     * variables
     * @access private
     * @param string $file 
     * @return {array}
     */
    private function _mashupConfig($file = NULL){
        /**
         * @todo: This is a dependency which we need to remove
         */
        if(defined('GLOBAL_CONFIG')){
          require GLOBAL_CONFIG;
        }
       /**
        * Include global file
        * Get all global variables
        * Add variables to config
        */
        if($file){
            require "{$file}";
        }
        $variables = get_defined_vars();        
        if(!isset($this->_iniFile[$this->_priorityEnv])){
            $this->_iniFile[$this->_priorityEnv] = NULL;
        }
        if(count($this->_iniFile[$this->_priorityEnv]) == 0){
            $this->_iniFile[$this->_priorityEnv] = $variables;
        }else{
            $this->_iniFile[$this->_priorityEnv] = array_replace_recursive($this->_iniFile[$this->_priorityEnv],$variables);
        }
    }
    /**
     * If global file exist then load all the params
     * to current config object and then override with
     * the application specific configuration. Global
     * values are assigned under production session so other
     * sessions can override this values
     * 
     * @access private
     * @param {array/string} $file
     * @return {void} 
     */
    private function _setGlobalConfig($globalFiles){
         if(is_array($globalFiles)){
             foreach ($globalFiles as $file){
                  $this->_mashupConfig($file);
              }
          }else{
              $this->_mashupConfig($globalFiles);
          }
    }
    
    /**
     * Laod ini file and returns the settings in it as an associative 
     * multi-dimensional array or object. This is a singleton object
     * so only single instance of config file will be loaded
     * @access public
     * @param mixed $configFiles, can be array of inis or single ini file path,
     *            The filename of the ini file being parsed
     * @return {stdClass}
     */
    public static function getInstance($configIniFiles = NULL,$globalFiles = NULL,$currentEnv = NULL)
    {
        if (!self::$_config)
        {
            if($configIniFiles){
                self::$_config = new self($configIniFiles,$globalFiles,$currentEnv);
            }else{
               throw new Exception('For loading configuration you need a valid ini file'); 
            }
            
        }
        return self::$_config->_getConfig();
    }
    
    /**
     * Get Config file and convert to object
     * @access private
     * @param void
     * @return {stdClass}
     */
    private function _getConfig(){
        return $this->_iniFile[$this->_currentEnv];
    }
}