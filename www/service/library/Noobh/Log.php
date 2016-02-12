<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Framework
 * @package    Noobh
 * @subpackage Noobh_DB
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 *
 * Collash Inc Internal
 *
 * Application logs are filed to the log file.
 * 
 * @todo: Need to updated database logging
 * 
 * Log file paths will be taken from Noobh_config file
 * 
 * Example
 * $log = Log::getInstance();
 * $log->LogInfo("Some information regarding the operation");
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh_DB
 * @since   0.1
 * @date Aug 28, 2012
 */
class Noobh_Log
{
	/**
	 * Log levels
	 */
	const DEBUG = 1; 
	const INFO = 2; 
	const WARNING = 3; 
	const ERROR = 4;
	const FATAL = 5;
	//Nothing will be logged in the file
	const OFF = 6; 
	
	//Log file status
	const LOG_FILE_OPEN = 1;
	const LOG_FILE_OPENING_FAILED = 2;
	const LOG_FILE_CLOSED = 3;
	const DATE_FORMAT = DATE_W3C;
	
	// [day/month/year:hour:minute:second zone]
	const DATE_FORMAT_SPLUNK = "d/M/Y:H:i:s O" ;

	/**
	 * Store template variables
	 * @access public
	 * @var {string}
	 */
	public static $templateVariables = array();
	/**
	 * Store current log file writing status,
	 * default value is close
	 * @access private
	 * @var {string}
	 */
	private $_logStatus;
	/**
	 * Store type of log level for logging
	 * @access private
	 * @var {array}
	 */
	private $_levels = array('1' => 'DEBUG',
	  '2' => 'INFO',
	  '3' => 'WARNING',
	  '4' => 'ERROR',
	  '5' => 'FATAL',
	   '6' => 'OFF'
	  );   
	/**
	 * Store type of log level for current log
	 * message
	 * @access private
	 * @var {string}
	 */
	private $_currentLevel;
	/**
	 * Store log file path
	 * @access private
	 * @var {string}
	 */
	private $_filePath;
	/**
	 * Store template path
	 * @access private
	 * @var {string}
	 */
	private $_template;
	/**
	 * Set log priority
	 * @access private
	 * @var {string}
	 */
	private $_priority;
	/**
	 * Set reason for error
	 * @access private
	 * @var {string}
	 */
	private $_reason;
	/**
	 * Set suggessions for solving problem
	 * @access private
	 * @var {string}
	 */
	private $_suggesion;
	/**
	 * Store file object to write
	 * @access private
	 * @var {Object}
	 */
	private $_file;
	/**
	 * Store application config
	 * @access private
	 * @var {Noobh_Config}
	 */
	private $_config;
	
	/**
	 * Store singleton instance of log object
	 * @access private
	 * @var {Noobh_Log}
	 */
	private static $_log;
	
	/**
	 * Create log object for writing log messages to the
	 * file
	 * 
	 * @param {string} $filepath, if NULL it will read from application config
	 * @param {string} $priority, if null it will check for application config,
	 * 				if not set in application config then default to log only FATAL Errors
	 */
	public function __construct ($filepath = NULL, $priority = NULL)
	{
		if ($this->_priority == self::OFF)
			return;
		//Get application config 
		$this->_config = Noobh_config::getInstance();
		//Set template
		if(isset($this->_config['resource']['log']['email']['template']['name'])){
			$this->_template = __DIR__.'/log/templates/'.$this->_config['resource']['log']['email']['template']['name'];
		}
		if(isset($this->_config['resource']['log']['email']['template']['path'])){
		 $this->_template = $this->_config['resource']['log']['email']['template']['path'];
		}
		//Load default template
		if(!$this->_template && count(self::$templateVariables) > 0){
		 $this->_template = __DIR__.'/log/templates/default.html';
		}
		//Set log file
		if($filepath == NULL){
			$filePath = isset($this->_config['resource']['log']['file'])? $this->_config['resource']['log']['file'] : NULL;
		}
		$this->setFile($filePath);
		//Set log priority
		$this->_priority = self::FATAL;
		if($priority == NULL){
			$this->_priority = isset($this->_config['resource']['log']['priority'])? $this->_config['resource']['log']['priority'] : NULL;
		}
	}
	/**
	 * Get a logger singleton instance
	 * 
	 * @access public
	 * @param string $filePath,
	 *            logger file path
	 * @param string $priority,
	 *            logger priority
	 * @return {Noobh_Log}
	 */
	public static function getInstance ($filepath = NULL, $priority = NULL)
	{
		if (! self::$_log) {
			self::$_log = new self($filepath, $priority);
		}
		return self::$_log;
	}
	
	/**
	 * Close open file stream
	 * @access public
	 * @param {void}
	 * @return void
	 */
	public function __destruct ()
	{
	   if ($this->_file){
		 fclose($this->_file);
		}   
	}
	
	
	
	/**
	 * Set file path for log file
	 * @access public
	 * @param {string} $filePath
	 * @return void
	 */
	public function setFile($filePath){

		if($filePath){
			$this->_filePath = $filePath;
			
			// Validate file path and write access
			$this->_logStatus = self::LOG_FILE_CLOSED;

			if ( file_exists($this->_filePath) ) {
				if (! is_writable($this->_filePath)) {
					$this->_logStatus = self::LOG_FILE_OPENING_FAILED;
					throw new Exception("The file exists in {$this->_filePath}, but could not be opened for writing. Check that appropriate permissions have been set.");
				}
			} else {

				// create new file
				touch($this->_filePath);
				chmod($this->_filePath, 0664);

				// throw new Exception("Check log file exist in following path: {$this->_filePath}");
			}

			// create file if does not exists
			if ( $this->_file = fopen($this->_filePath, "a+") ) {
				$this->_logStatus = self::LOG_FILE_OPEN;
			} else {
				$this->_logStatus = self::LOG_FILE_OPENING_FAILED;
				throw new Exception("The file exists in {$this->_filePath}, but could not be opened for writing. Check that appropriate permissions have been set.");
			}
		}else{
			throw new Exception('You need to set log file path for using logger');
		}
	}
	
	/**
	 * Close open file stream
	 * @access public
	 * @param {void}
	 * @return void
	 */
	public static function info ($message, $reason = null, $suggestion = null)
	{
		self::getInstance()->_log($message, self::INFO, $reason, $suggestion);
	}
	/**
	 * Close open file stream
	 * @access public
	 * @param {void}
	 * @return void
	 */
	public static function debug ($message, $reason = null, $suggestion = null)
	{
		self::getInstance()->_log($message, self::DEBUG, $reason, $suggestion);
	}
	/**
	 * Close open file stream
	 * @access public
	 * @param {void}
	 * @return void
	 */
	public static function warning ($message, $reason = null, $suggestion = null)
	{
		self::getInstance()->_log($message, self::WARNING, $reason, $suggestion);
	}
	/**
	 * Close open file stream
	 * @access public
	 * @param {void}
	 * @return void
	 */
	public static function error ($message, $reason = null, $suggestion = null)
	{
		self::getInstance()->_log($message, self::ERROR, $reason, $suggestion);
	}
	/**
	 * Close open file stream
	 * @access public
	 * @param {void}
	 * @return void
	 */
	public static function fatal ($message, $reason = null, $suggestion = null)
	{
		self::getInstance()->_log($message, self::FATAL, $reason, $suggestion);
	}
	/**
	 * Logging message internally
	 * @access public
	 * @param {string} $message
	 * @param {string} $priority
	 * @return void
	 */
	private function _log ($message, $priority, $reason, $suggestion)
	{
		$this->_reason = $reason;
		$this->_suggesion = $suggestion;
		$this->_currentLevel = $this->_levels[$priority];;
		if ($this->_priority <= $priority && $this->_priority != self::OFF) {
			$status = $this->_createLogMessage($priority);
						$log = "{$status} MESSAGE=\"".addslashes($message)."\"\n";
			$this->_write($log);
		}
	}
	/**
	 * Write logger message to file
	 * If we set to email in application.ini,it will 
	 * send log emails to given address, we send email for errors
	 * equal to or higher than 'ERROR'
	 * 
	 * 
	 * @todo: Need to integrate with database logging
	 * 
	 * @access public
	 * @param {string} $message
	 * @return void
	 * @throws Exception
	 */
	private function _write($message)
	{
	   //We don't call this method if error log is off
	   if(isset($this->_config['resource']['log']['email']['to']) && $this->_priority >= self::ERROR){
		 //If template use it else set body by default
		 $email = new Noobh_Email($this->_config['resource']['log']['email']['to']);
		 $email->setEmailFrom($this->_config['resource']['log']['email']['from']);
		 $subject = isset($this->_config['resource']['log']['email']['appName'])? $this->_currentLevel . ' - ' .$this->_config['resource']['log']['email']['appName'] : $this->_currentLevel . ' -  Alert';
		 $email->setSubject($subject);
		 if($this->_template){
		  //Create body using template
		  try{
		   self::$templateVariables['errorStatus'] = $this->_currentLevel;
		   self::$templateVariables['logMessage'] = $message;
		   self::$templateVariables['reason'] = $this->_reason;
		   self::$templateVariables['suggestion'] = $this->_suggesion;
		   self::$templateVariables['requestUrl'] = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		   self::$templateVariables['environment'] = APPLICATION_ENV;
		   self::$templateVariables['header'] = str_replace(',', '<br>', implode( ",",apache_request_headers()));
		   $body = Noobh_View_Template_Parser::parse($this->_template, self::$templateVariables);
		  }catch (Exception $e){
		   //supress exemption if template is not present
		   $body = $message;
		  }
		 }else{
		  $body = $message;
		 }
		 $email->setBody($body);
		 try{
		  $email->send();
		 }catch(Exception $e){
		  //Just supress the error if mail sever is not present
		 }
		}
		if ($this->_logStatus == self::LOG_FILE_OPEN) {
			if (fwrite($this->_file, $message) === false) {
			   throw new Exception("Check log file permission, Unable to write log in file: {$this->_filePath}.");
			}
		}
	}
	/**
	 * Create log messages by adding date time and priority level
	 * @access public
	 * @param {string} $priority
	 * @return {string}
	 */
	private function _createLogMessage ($priority)
	{
		$time = date(self::DATE_FORMAT_SPLUNK);
		$hostname = php_uname('n');
		$userIP = $this->_getUserIp();
		$appId = ( isset($this->_config['application']['resource']['appId']) )? $this->_config['application']['resource']['appId'] : 'NOT_DEFINED';
		switch ($priority) {
			case self::INFO:
				return "[{$time}] APP_ID={$appId} TYPE=INFO SERVER={$hostname} CLIENT_IP={$userIP}";
			case self::WARNING:
				return "[{$time}] APP_ID={$appId} TYPE=WARNING SERVER={$hostname} CLIENT_IP={$userIP}";
			case self::DEBUG:
				return "[{$time}] APP_ID={$appId} TYPE=DEBUG SERVER={$hostname} CLIENT_IP={$userIP}";
			case self::ERROR:
				return "[{$time}] APP_ID={$appId} TYPE=ERROR SERVER={$hostname} CLIENT_IP={$userIP}";
			case self::FATAL:
				return "[{$time}] APP_ID={$appId} TYPE=FATAL SERVER={$hostname} CLIENT_IP={$userIP}";
			default:
				return "[{$time}] APP_ID={$appId} TYPE=LOG SERVER={$hostname} CLIENT_IP={$userIP}";
		}
	}

	/**
	* Return user ip address
	*
	* @access protected
	* @param void
	* @return {string}
	*/
	private function _getUserIp(){

		if ( getenv("HTTP_X_FORWARDED_FOR") ) {
			$remoteIP = getenv("HTTP_X_FORWARDED_FOR");
		} elseif ( getenv("REMOTE_ADDR") ) {
			$remoteIP = getenv("REMOTE_ADDR");
		} elseif(getenv("SERVER_ADDR")) {
			$remoteIP = getenv("SERVER_ADDR");
		} elseif(getenv("HTTP_PC_REMOTE_ADDR")) {
			$remoteIP = getenv("HTTP_PC_REMOTE_ADDR");
		} else {
			return NULL;
		}

		return $remoteIP;
	
	}
}