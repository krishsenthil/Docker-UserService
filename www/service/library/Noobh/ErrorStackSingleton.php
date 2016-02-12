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
 * Stack all errors in the application, We can even perform
 * error translation to different languages using this
 * error stack. Currently commenting error translation
 * code for performence issue. None of our application is
 * supporting multiple languages.
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package    Noobh_Auth
 * @subpackage Adapter
 * @since   0.1
 * @date Sept 12, 2012
 *
 */

class Noobh_ErrorStackSingleton {

   /**
    * Store list of errors
    * @access private
    * @var {Array}
    */
   private static $_errorStack;

   /**
    * Store instance of this class
    * @access private
    * @var {Object} error stack
    */
   private static $_instance = NULL;

   /**
    * Map error code to error messages from a file
    * or database 
    * 
    * @todo: Currently we are not supporting translation
    * 
    * @access private
    * @var {Array}
    */
   private $_messages = array();

   
	/**
	 * Get a new error stack, this constructor
	 * should not be instansiated directly, it is
	 * a singleton class
	 * 
	 * @access private
	 * @param void
	 * @return Noobh_ErrorStackSingleton
	 */
	private function __construct() {
		self::$_errorStack = null;
	}

	/**
	 * Instansiate error stack singleton object
	 * 
	 * @access private
	 * @param void
	 * @return Noobh_ErrorStackSingleton
	 */
	public static function getInstance() {
		if (NULL === self::$_instance) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	
	/**
	 * Push error code to the error stack
	 * 
	 * @param {String} $errorGroup, to group errors
	 * @param {String} $errorCode, error code as the key
	 * @param {String} $message, message for an error code
	 * @return {Object} error stack
	 */
	public function push($errorGroup,$errorCode,$message = NULL) {
	   /** 
	    * We are storing error messages dynamically and this check
	    * can be re-intorduced when we start error translation
	    */
	  /*if (array_key_exists ( $errorCode, $this->_messages )) {
				self::$_errorStack [$errorGroup][$errorCode] = $this->_messages [$errorCode];
		} else {
				throw new Exception ( 'Trying to add unlisted error code into the stack' );
		}*/
	  self::$_errorStack [$errorGroup][$errorCode] = $message;
		return $this;
	}

	/**
	 * Get list of errors with messages
	 * 
	 * @param {void}
	 * @return {Array} list of error message
	 */
	public function getErrorList(){
		return self::$_errorStack;
	}

	/**
	 * Check error list is empty
	 * @param {void}
	 * @return {Boolean}
	 */
	public function isEmpty(){
		$return = true;
		if(count(self::$_errorStack) > 0){
			$return = false;
		}
		return $return;
	}

	/**
	 * Clear error stack
	 * @param {void}
	 * @return {objet} error statck object
	 */
	public function clearStack(){
		self::$_errorStack = null;
		return $this;
	}

	/**
	 * Get all error messages
	 * @param {String} error group
	 * @return {Array} list of error messages
	 */
     public function getMessages($errorGroup = null){
     	$messages = array();
     	if(!$this->isEmpty()){
     	  if($errorGroup != null){
     	     if(isset(self::$_errorStack[$errorGroup])){
     	       //Read message from error stack with key
     	       foreach(self::$_errorStack[$errorGroup] as $message){
     	  	        $messages[] = $message;
     	  	   }
     	     }
     	  }else{
     	    //Read message from error stack with out key
     	    foreach(self::$_errorStack as $codeMessage){
     	  	  if(is_array($codeMessage)){
     	  	      foreach($codeMessage as $message){
     	  	        $messages[] = $message;
     	  	      }
     	  	  }
     	    }
     	  }
     	}
     	return $messages;
     }
}