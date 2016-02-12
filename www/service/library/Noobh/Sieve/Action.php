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
 * This is a static class which is used for creating
 * sieve actions.This can be also used out side framewok
 * by directly calling for getting sieve actoin rules
 * 
 * Create sieve rule actions. This is a Mapper calss
 * which maps sieve action to sieve script.
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh_Sieve
 * @since   0.1
 * @date June 15, 2012
 *
 */
class Noobh_Sieve_Action
{
    /** 
     * Supported action types for sieve rules
     */
    const ACTION_FILEINTO = 'fileinto';
    //This will send copy of the message to the redirected mail and delete original message
    const ACTION_REDIRECT = 'redirect';
    const ACTION_REJECT = 'reject';
    const ACTION_KEEP = 'keep';
    const ACTION_DISCARD = 'discard';
    const ACTION_CUSTOM = 'custom';
    const ACTION_VACATION = 'vacation';
    const ACTION_ADDFLAG = 'addflag';
    //Which will be internally mapped to \\Seen
    const ACTION_MARK_READ = 'read';
    const ACTION_NOTIFY = '\\Flagged';
    const ACTION_STOP = 'stop';
    const ACTION_COPY = ':copy';
    //Keep a copy and forward the message to another mail
    const ACTION_FORWARD = 'forward';
    
    /**
     * Following are the list of act on constants
     */
    const ACT_ON_FOLDER_NAME = 'folderName';
    const ACT_ON_EMAIL = 'email';
    const ACT_ON_MESSAGE = 'message';
    /**
     * Supported flags
     */
    const FLAG_RED = 'red';
    const FLAG_ORANGE = 'orange';
    const FLAG_YELLOW = 'yellow';
    const FLAG_GREEN = 'green';
    const FLAG_BLUE = 'blue';
    const FLAG_PURPLE = 'purple';
    const FLAG_GRAY = 'gray';
    const FLAG_REMOVE = 'removeFlag';
    
    /**
     * Static properties which are used in the application
     * for over riding the existing behavior and inject new
     * customized operations. For dependency injection this properties
     * should be defined in the application level
     * 
     * Maps actions to sieve actions
     *
     * @todo: Refer example in wiki:
     */
    public static $actions = array();
    /**
     * Static properties which are used in the application
     * for over riding the existing behavior and inject new
     * customized operations. For dependency injection this properties
     * should be defined in the application level
     *
     * Store supported email domains for forwarding emails
     *
     * @todo: Refer example in wiki:
     */
    public static $supportedDomains = array();
    /**
     * Create action rule and return sieve script
     *
     * Sample sieve rule
     *
     * fileinto "my  mailbox";
     * stop;
     *
     * @access public
     * @param void
     * @return {string} $sieveAction
     */
    public static function create(SimpleXMLElement $xml){
        //Move command should go to the last in all operation
        $moveCommand = '';
        $sieveAction = PHP_EOL."{";
        if(isset($xml->actionGroup)){
            foreach($xml->actionGroup as $element){
                $sieveAction .= PHP_EOL;
                if(isset($element->action)){
                 if(!array_key_exists((string)$element->action, self::$actions)){
                 	throw new Exception('Action is not in Noobh_Sieve_Condition::$actions list');
                 }
                 $mappedActions = isset(self::$actions[(string)$element->action])? self::$actions[(string)$element->action] : $element->action;
                 $actOnSieve = '';
                 //Set require module according to action
                 foreach($mappedActions as $action){
                  switch ($action){
                   /**
                    * Example file into folder
                    *
                    * fileinto "Test-Folder";
                    */
                  	case  'fileinto' :
                  	 Noobh_Sieve_Rule::$requireModules[] = 'fileinto';
                  	 $moveCommand .= " fileinto \"" . $element->actOn . "\";". PHP_EOL;
                  	 break;
                  	 /**
                  	  * Example file into folder
                  	  *
                  	  * We copy the message from default folder to the given folder
                  	  *
                  	  * fileinto :copy "Test-Folder";
                  	  */
                  	case  ':copy' :
                  	 Noobh_Sieve_Rule::$requireModules[] = 'copy';
                  	 Noobh_Sieve_Rule::$requireModules[] = 'fileinto';
                  	 $actOnSieve .= " fileinto :copy \"" . $element->actOn . "\";". PHP_EOL;
                  	 break;
                  	 /**
                  	  * Example forward
                  	  *
                  	  * redirect :copy "other@example.net";
                  	  */
                  	case  'forward' :
                  	 Noobh_Sieve_Rule::$requireModules[] = 'copy';
                  	 //Validate email
                  	 $email = (string) $element->actOn;
                  	 if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                  	 	throw new Exception('Invalid Email adress');
                  	 }
                  	 if(count(self::$supportedDomains) > 0){
                  	  if(!in_array(substr(strrchr($email, "@"), 1), self::$supportedDomains)){
                  	   throw new Exception('Your forward email contains non-supporting domains');
                  	  } 
                  	 }
                  	 $actOnSieve .= " redirect :copy \"" . $email . "\";". PHP_EOL;
                  	 break;
                  	 /**
                  	  * Example redirect
                  	  *
                  	  * For safty we trash the forwarded messages
                  	  *
                  	  * redirect :copy "other@example.net";
                  	  * fileinto "Trash";
                  	  */
                  	case  'redirect' :
                  	 Noobh_Sieve_Rule::$requireModules[] = 'copy';
                  	 Noobh_Sieve_Rule::$requireModules[] = 'fileinto';
                  	 $actOnSieve .= " redirect \"" . $element->actOn . "\";". PHP_EOL.
                  	                " fileinto \"Trash\";". PHP_EOL;
                  	 break;
                  	 /**
                  	  * Example addflag
                  	  *
                  	  * #Message read
                  	  * setflag "\\seen";
                  	  *
                  	  */
                  	case  'read' :
                  	 Noobh_Sieve_Rule::$requireModules[] = 'imap4flags';
                  	 $actOnSieve .= " setflag \"\\\seen\";". PHP_EOL;
                  	 break;
                  	  
                  	 /**
                  	  * Example addflag
                  	  *
                  	  * #Message with flagged with different colors
                  	  * setflag "\\Flagged Red";
                  	  *
                  	  */
                  	case  'addflag' :
                  		Noobh_Sieve_Rule::$requireModules[] = 'imap4flags';
                  		//According to color create different sive actions
                  		switch($element->actOn){
                  		   case self::FLAG_BLUE: 
                  		      $actOnSieve .= " setflag \"\\\Flagged + \$MailFlagBit2\";". PHP_EOL;
                  		      break;
                  		   case self::FLAG_GREEN:
                  		      $actOnSieve .= " setflag \"\\\Flagged + \$MailFlagBit1 + \$MailFlagBit0 \";". PHP_EOL;
                  		      break;
                  		   case self::FLAG_ORANGE: 
                  		      $actOnSieve .= " setflag \"\\\Flagged + \$MailFlagBit0\";". PHP_EOL;
                  		      break;
                  		   case self::FLAG_GRAY: 
                  		      $actOnSieve .= " setflag \"\\\Flagged + \$MailFlagBit2 + \$MailFlagBit1 \";". PHP_EOL;
                  		      break;
                  		   case self::FLAG_PURPLE:
                  		      $actOnSieve .= " setflag \"\\\Flagged + \$MailFlagBit2 + \$MailFlagBit0 \";". PHP_EOL;
                  		      break;
                  		   case self::FLAG_YELLOW:
                  		      $actOnSieve .= " setflag \"\\\Flagged + \$MailFlagBit1\";". PHP_EOL;
                  		      break;
                  		   case self::FLAG_REMOVE: 
                  		      $actOnSieve .= " removeflag \"\\\Flagged\";". PHP_EOL;
                  		      break;
                  		   default : //Default is Red
                  		      $actOnSieve .= " setflag \"\\\Flagged\";". PHP_EOL;
                  		}
                  		break; 
                  }
                 }
                 $sieveAction .= $actOnSieve;
                }else{
                    //@todo: Need to add the action xml while throwing the exception back
                    throw new Exception('Invalid action for the action group clause: ', 'IST_SIEVE_1006');
                }
            }
        
        }else{
            //@todo: Need to add the action xml while throwing the exception back
            throw new Exception('Invalid action for the if clause: ', 'IST_SIEVE_1005');
        }
        $sieveAction .= $moveCommand . " stop;".PHP_EOL."}".PHP_EOL;
        return $sieveAction; 
    }
    
    
}