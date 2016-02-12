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
 *
 * Collash Inc Internal
 * 
 * 
 * 
 * Refer Documentation: https://dtswiki.Collash.com/wiki/pages/Z4A6N92/IST_Web_Session.html
 *
 * All IS&T Web apps will be using this session class for storing
 * app specific session information. Session class is not using
 * PHP Session, instead each app should create a specific table in
 * the data base with the following schema
 *
 * If table doesnt exist then this class will throw an exception back to
 * the application.
 *
 * Basic requirement: For using IS&T Web Session class application should
 * create a table with following schema.
 *
 * Following is the table schema for storing session
 *
 * CREATE TABLE `session` (
 * `session_sessionKey` varchar(42) NOT NULL default '',
 * `session_userId` varchar(42) NOT NULL default '',
 * `session_expires` datetime NOT NULL,
 * `session_ipAddress` varchar(10) default NULL,
 * PRIMARY KEY (`session_sessionKey`)
 * );
 *
 *
 * If table doesn't exist then this class will create table
 * automatically.
 * 
 * This session table will only contain userId and this will be used
 * as a forgine key in all other tables for extracting user related information
 * 
 *
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package Noobh
 * @since 0.1
 * @date May 03, 2012
 *       
 *       
 */
class Noobh_SessionSingleton
{

    /**
     * Store database adapter to connect with database
     * which contain the app specific session table
     *
     * @access private
     * @var Noobh_DB_Adapter
     */
    private $_dbAdapter;

    /**
     * Store database adapter to connect with database
     * which contain the app specific session table
     *
     * @access private
     * @var Noobh_DB_Adapter
     */
    private static $_session;
    /**
     * Store session key
     *
     * @access private
     * @var string
     */
    private $_sessionKey;
    /**
     * Store session table name. Keeping this as
     * private static to do some table name validation
     * in future
     *
     * @access private
     * @var string
     */
    private static $_tableName;
    /**
     * Store active user id
     *
     * @access private
     * @var string
     */
    private $_userId;
    /**
     * Store current session expiration
     * time
     *
     * @access private
     * @var string
     */
    private $_expirationTime;
    /**
     * Store current user's ipaddress
     *
     * @access private
     * @var string
     */
    private $_ipAddress;
     
    /**
     *
     * This is a singleton session class
     *
     * For using IS&Tweb session we need a database connection.
     * Noobh sessions should only be stored in database.
     * 
     * If sessionkey exist then existing session will be loaded
     * from database,else it will create a new session. One user 
     * will be only having one session
     *
     * @access private
     * @param {string} $sessionKey, Session key
     * @param {Noobh_Config} $config, override exisitng configuration
     * @return Noobh_Session
     */
    private function __construct($sessionKey,$config = NULL){
        if(!$this->_dbAdapter){
            $this->_dbAdapter = new Noobh_DB_Adapter();
            $config = ($config == NULL)?Noobh_config::getInstance() : $config;
            //Set database table name if set in config file
            self::$_tableName = isset($config['resource']['session']['db']['table'])? $config['resource']['session']['db']['table'] : '';
        }
        $this->_sessionKey = $sessionKey;
        $this->_loadSession();
    }
    /**
     * Get Noobh Session.
     * @access public
     * @param {string} $sessionKey
     * @return {Noobh_SessionSingleton}
     */
    public static function getInstance($sessionKey = NULL, $config = NULL)
    {
        if (!self::$_session)
        {
            self::$_session = new self($sessionKey,$config);
        }
        return self::$_session;
    }

    /**
     * Get current session key
     * @access public
     * @param void
     * @return {string}
     */
    public function getSessionKey(){
        return $this->_sessionKey;
    }
    /**
     * Get current session table name used in the database
     * @access public
     * @param void
     * @return {string}
     */
    public function getTableName(){
        return self::$_tableName;
    }
    /**
     * Get current user id stored in session
     * @access public
     * @param void
     * @return {string}
     */
    public function getCurrentUserId(){
        return $this->_userId;
    }
    /**
     * Get current session experation time
     * @access public
     * @param void
     * @return {string}
     */
    public function getExpirationTime(){
        return $this->_expirationTime;
    }
    /**
     * Get current users ipaddress
     * @access public
     * @param void
     * @return {string}
     */
    public function getIpAddress(){
        return $this->_ipAddress;
    }
    /**
     * Set current session table name used in the database
     * @access public
     * @param {string} $tableName
     * @return void
     */
    public static function setTableName($tableName){
        if($tableName){
            self::$_tableName = $tableName;
        }
    }
    /**
     * Set user id in session. One user should only have
     * session record in the session table.So all other session information 
     * stored in the session table will be cleared.
     * 
     * @access public
     * @param {string}
     * @return void
     */
    public function setCurrentUserId($userId){
        if($userId){
         $this->_update($userId, NULL);
         $this->_userId = $userId;
        }
    }
    /**
     * Set current users ipaddress
     * @access public
     * @param {sting}
     * @return void
     */
    public function setIpAddress($ipAddress){
        if($ipAddress){
         $this->_update(NULL, $ipAddress);
         $this->_ipAddress = $ipAddress;
        }
    }    

    /**
     * This is a handy method for setting all data for the
     * session for single time
     * 
     * @param public
     * @param {sting} $userId
     * @param {sting} $ipAddress
     * @return void
     */
    public function setData($userId,$ipAddress){
     $this->_update($userId, $ipAddress);
     $this->_userId = $userId;
     $this->_ipAddress = $ipAddress;
    }
    /**
     * Destroy session by session key. Method will delete session
     * from database
     * @access public
     * @param {string} $sessionKey
     * @throws For Database failure framework will throw error to above layer
     * @return {boolean} TRUE for properly destroyed session
     */
    public function destroy ($sessionKey = NULL)
    {
        $sessionKey = ($sessionKey == NULL) ? $this->_sessionKey : $sessionKey;
        if($sessionKey){
            self::$_tableName = (self::$_tableName)? self::$_tableName : 'session';
            $sql = 'DELETE FROM `'. self::$_tableName .'` where `session_sessionKey` = ?';
            $bind = array($sessionKey);
            try{
             $this->_dbAdapter->query($sql,$bind);
             $this->_sessionKey = NULL;
             $this->_userId = NULL;
             $this->_expirationTime = NULL;
             $this->_ipAddress = NULL;
            }catch (Exception $e){
             throw new Exception('Unable to destroy session information due following error: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
            }
            return true;
        }
        return false;
    }
    /**
     * For existing users load session from the database and update the
     * expiration time else create a new session.
     *
     * @access private
     * @param {void}            
     * @return {void}
     */
    private function _loadSession ()
    {
        if ($this->_sessionKey) {
						if($this->_search())
            	$this->_updateSessionTime();
						else
							$this->_create();
        } else {
            //Create new session
            $this->_create();
        }
    }

    /**
     * Search current session is active or not
     * @access private
     * @param void
     * @return boolean
     */
    private function _search(){
        self::$_tableName = (self::$_tableName)? self::$_tableName : 'session';
        $sql = 'SELECT * FROM `'. self::$_tableName .'` where `session_sessionKey` = ? AND `session_expires` > ?';
        $bind = array($this->_sessionKey, date('Y-m-d H:i:s'));
        try{
         $statement = $this->_dbAdapter->query($sql,$bind);
         $result = $statement->fetchAssoc();
         if (count($result) > 0){
            //Load session
            $this->_userId = $result[0]['session_userId'];
            $this->_expirationTime = $result[0]['session_expires'];
            $this->_ipAddress = $result[0]['session_ipAddress'];
						return true;
         }
        }catch (Exception $e){
         throw new Exception('Unable to search session due to following error: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
        }
				return false;
    }
    /**
     * Create a new session in the database
     * @access private
     * @param void
     * @return Exception
     */    
    private function _create(){
     //Generate session key
     $this->_sessionKey = ($this->_sessionKey)? $this->_sessionKey : $this->_uniqSessionId();
     $this->_expirationTime = $this->_generateExperationTime();
     //Create a new session
     self::$_tableName = (self::$_tableName)? self::$_tableName : 'session';
     $sql = 'INSERT INTO `'. self::$_tableName .'` (`session_sessionKey`,`session_userId`,`session_expires`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `session_userId`=?, `session_expires`=?';
     $bind = array($this->_sessionKey,NULL,$this->_expirationTime,NULL,$this->_expirationTime);
     try{
      $this->_dbAdapter->query($sql,$bind);
     }catch (Exception $e){
      throw new Exception('Unable to create new session information due to following error: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
     }
    }
    
    /**
     * Create unique session if for a user
     * @access private
     * @param void
     * @return {string}
     */
    private function _uniqSessionId(){
     $salt = '';
     $salt .= (isset($_SERVER['REQUEST_TIME']))? $_SERVER['REQUEST_TIME'] : '';
     $salt .= (isset($_SERVER['HTTP_USER_AGENT']))? $_SERVER['HTTP_USER_AGENT'] : '';
     $salt .= (isset($_SERVER['LOCAL_ADDR']))? $_SERVER['LOCAL_ADDR'] : '';
     $salt .= (isset($_SERVER['LOCAL_PORT']))? $_SERVER['LOCAL_PORT'] : '';
     $salt .= (isset($_SERVER['REMOTE_ADDR']))? $_SERVER['REMOTE_ADDR'] : '';
     $salt .= (isset($_SERVER['REMOTE_PORT']))? $_SERVER['REMOTE_PORT'] : '';
     $salt .= mt_rand();
     return hash('sha1', uniqid($salt,true));
    }
    
    
    /**
     * Update an existing session with user id or ipaddress
     * If session key is null, then we create a new session
     * and save the data
     * 
     * If session is unable to update database then throw exception
     * back to the application and let application handel it.
     * 
     * @access private
     * @param void
     * @return boolean/Exception
     */    
    private function _update($userId, $ipAddress){
        if(!$this->_sessionKey){
            //Create session
            $this->_create();
        }
        //This is a clean up before setting a new userId, here we clean up all records for existing user except current session
        if($userId){
            self::$_tableName = (self::$_tableName)? self::$_tableName : 'session';
            $sql = 'DELETE FROM `'. self::$_tableName .'` where `session_userId` = ? AND `session_sessionKey` <> ?' ;
            $bind = array($userId,$this->_sessionKey);
            try{
             $this->_dbAdapter->query($sql,$bind);
            }catch (Exception $e){
             throw new Exception('Unable to clean up session information before update due to following error: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
            }
        }
        $userId = ($userId) ? $userId : $this->_userId;
        $ipAddress = ($ipAddress) ? $ipAddress : $this->_ipAddress;
        //Update existing session information 
        $sql = 'UPDATE `'. self::$_tableName .'` SET `session_userId` = ?,`session_ipAddress` = ? WHERE  `session_sessionKey` = ?';
        $bind = array($userId,$ipAddress,$this->_sessionKey);
        try{
          $this->_dbAdapter->query($sql,$bind);
        }catch (Exception $e){
        	throw new Exception('Unable to update session information due to following error: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
        }       
    }
    /**
     * Update session expiration time for an existing session
     * @access private
     * @param void
     * @return void
     */    
    private function _updateSessionTime(){
        $this->_expirationTime = $this->_generateExperationTime();
        self::$_tableName = (self::$_tableName)? self::$_tableName : 'session';
        $sql = 'UPDATE `'. self::$_tableName .'` SET `session_expires` = ? WHERE  `session_sessionKey` = ?';
        $bind = array($this->_expirationTime,$this->_sessionKey);
        try{
         $this->_dbAdapter->query($sql,$bind);
        }catch (Exception $e){
         	throw new Exception('Unable to up session time information due to following error: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
        }
        
    }
    /**
     * Get experation time for a session, We read it 
     * from config file and add it to current time
     *
     * Default session expiration time is 1 hour
     * 
     * @access protected
     * @param {void}
     * @return {string}
     */
    protected function _generateExperationTime(){
        $time =  time(date('Y-m-d H:i:s'));
        $config = Noobh_config::getInstance();
        if(isset($config['resource']['session']['expire'])){
            $time = $time + $config['resource']['session']['expire'];
        }else{
            //For one hour
            $time = $time + 3600;
        } 
        return date("Y-m-d H:i:s",$time);
    }
		/**
		* Return user ip address
		*
		* @access private
		* @param void
		* @return {string}
		*/
		private function _getUserIp(){
		   if(getenv("REMOTE_ADDR")) {
		       $remoteIP = getenv("REMOTE_ADDR");
		   } elseif(getenv("HTTP_PC_REMOTE_ADDR")) {
		       $remoteIP = getenv("HTTP_PC_REMOTE_ADDR");
		   } elseif(getenv("HTTP_X_FORWARDED_FOR")) {
		       $remoteIP = getenv("HTTP_X_FORWARDED_FOR");
		   } else {
		       return NULL;
		   }
		   return $remoteIP;

		}
}