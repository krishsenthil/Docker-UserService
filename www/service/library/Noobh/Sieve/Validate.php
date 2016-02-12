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
 * Dependency:
 * 
 * 1. Need public key in Sieve Servers
 * 
 * Conect to sieve validation servers 
 * and validate sieve rules over SSH connection
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh_Sieve
 * @since   0.1
 * @date June 15, 2012
 *
 */
class Noobh_Sieve_Validate{
 
 /**
  * Sieve server command for validation
  * 
  * @access private
  * @var {string} $_validateComand
  */
 private $_validateComand = 'imsimta';
 /**
  * Store server login user name for SSH
  * @acess private
  * @var {string} $_username
  */
 private $_username;
 /**
  * Store server name
  * 
  * @access private
  * @var {string} $_serverName
  */
 private $_serverName; 
 /**
  * Store server path to run imsimta commands
  * 
  * @access private
  * @var {string} $_path
  */
 private $_path; 
 
 /**
  * Create Sieve Validation oject
  * 
  * If parameters are null then class will read it from 
  * Application.ini
  * 
  * @param {string} $userName , user name for server login
  * @param {string} $serverName , Name of the server
  * @param {string} $path, Path in the server for running imsimta commands
  * @return Noobh_Sieve_Valide
  */
  public function __construct($userName = null,$serverName = null,$path = null){
   if($userName && $serverName && $path){
    $this->_username = $userName;
    $this->_serverName = $serverName;
    $this->_path = $path;
   }else{
     //Fetch from Application.ini
    
   }
  }
  
  /**
   * Validate rules
   * @access public
   * @param {string} $rule
   * @return {string} $response
   */
  public function validate($rule){
   //Login to server
   echo 'ssh '.$this->_username.'@'.$this->_serverName. ' ' .$this->_path.$this->_validateComand. ' test -exp -mm -block -input=temp.filter -message=test_rfc2822.msg';
   exec('ssh '.$this->_username.'@'.$this->_serverName.$this->_path. 'test -exp -mm -block -input=temp.filter -message=test_rfc2822.msg',$output);
   
  }
  
 
 
 
}