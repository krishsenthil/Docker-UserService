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
 * Create sieve rule from Simplexml 
 *
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package Noobh_Sieve
 * @since 0.1
 * @date May 15, 2012
 *       
 */
class Noobh_Sieve_Rule
{
    /**
     * Sieve templates
     * @var {string}
     */
    const TEMPLATE_VACATION = 'Vacation';
    const TEMPLATE_FILE_TO_FOLDER = 'File To Folder';
    
    /**
     * Recure modules for the rule according to action
     * @access public
     * @var {array} $requireModules 
     */
    public static $requireModules = array();
    /**
     * Store template
     *
     * @access private
     * @var {string} $template
     */
    private $_template;
    
    /**
     * Store rule name
     *
     * @access private
     * @var {string} $name
     */
    private $_name;
    
    /**
     * Store rule order
     *
     * @access private
     * @var {string} $order
     */
    private $_order;
    
    /**
     * Store xml object
     *
     * @access private
     * @var {SimpleXMLElement} $xml
     */
    private $_xml;
    
    /**
     * Store sieve rule
     *
     * @access private
     * @var {string} $rule
     */
    private $_rule;
        
    /**
     * Extract basic information from the xml object
     * and populate sieve rule
     *         
     * @param {SimpleXMLElement} $xml            
     * @return {Noobh_Sieve_Rule}
     */
    public function __construct (SimpleXMLElement $xml)
    {
      $this->_xml = $xml;
    	$this->_name = isset($xml->name)? (string) $xml->name : md5(rand(999,9999) . date('m-d-Y'));
    	$this->_template = isset($xml->template)? (string) $xml->template : 'Noobh';
    	$this->_order = isset($xml->order)? (string) $xml->order:1;
    }
    
    /**
     * Get template name
     * @access public
     * @param void
     * @return {string} $template
     */
    public function getTemplate ()
    {
        return $this->_template;
    }
    
    /**
     * Get rule name
     * @access public
     * @param void
     * @return {string} $name
     */
    public function getName ()
    {
        return $this->_name;
    }
    
    /**
     * Get rule order
     * @access public
     * @param void
     * @return {string} $order
     */
    public function getOrder ()
    {
        return $this->_order;
    }
    
    /**
     * Get rule module list
     * @access public
     * @param void
     * @return {string} $requireModules
     */
    public function getRequiredModule ()
    {
        return self::$requireModules;
    }
        
    /**
     * Set template name
     * @access public
     * @param {string} $template
     * @return void
     */
    public function setTemplate ($template)
    {
        $this->_template = $template;
    }
    
    /**
     * Set rule name
     * @access public
     * @param {string} $name
     * @return void
     */
    public function setName ($name)
    {
        $this->_name = $name;
    }
    
    /**
     * Set rule order
     * @access public
     * @param {string} $order
     * @return void
     */
    public function setOrder ($order)
    {
        $this->_order = $order;
    }
    
    /**
     * Set rule module list
     * @access public
     * @param {array} $requiredModuleList
     * @return void 
     */
    public function setRequiredModuleList (array $requiredModuleList)
    {
        $this->_requiredModuleList = $requiredModuleList;
    } 
    
    /**
     * Create sieve rules example
     * 
     * #RULE: $Template="File To Folder" $Name="1662959434e5bd9de96622" $Order=0
     * require "fileinto";
     * if address :contains ["From","Sender","Resent-from","Resent-sender","Return-path"]
     * "testing2"
     * {
     * fileinto "Oc̩ane";
     * stop;
     * }
     * 
     * @access public
     * @param void
     * @return {string} $rule
     * 
     */
    public function create(){
        /**
         * Create rules
         *
         * If there is no if condtion then there is no point in creating rules. This is a
         * basic validation for sieve rules
         * 
         * @todo: Need to hadel If condition inside the action as enhancement
         */
        $this->_rule = '';
        if(isset($this->_xml->if)){
         $this->_rule .= 'if ';
         $this->_rule .= Noobh_Sieve_Condition::create($this->_xml->if);
         //Create elseIf rule if present
         if(isset($this->_xml->elseIf)){
          foreach($this->_xml->elseIf->if as $if){
           $this->_rule .= PHP_EOL." elseIf ";
           $this->_rule .= Noobh_Sieve_Condition::create($if);
          }
         }
         //Without action we will not be calling else clause
         if(isset($this->_xml->else->actions)){
          $this->_rule .= "\r\n else ";
          $this->_rule .= Noobh_Sieve_Action::create($this->_xml->else->actions);
         }
         /**
          * Sieve header creation
          * 
          * For name give a hash number so each rule in ldap will be unique
          */
         $headerRule = '#RULE: $Template="'. $this->_template .'" $Name="'. addslashes($this->_name) .'('.uniqid('Noobh_',true).')" $Order='. $this->_order . PHP_EOL;
         if( count(self::$requireModules) > 0){
          self::$requireModules = array_unique(self::$requireModules);
          $addedModule = '';
          $headerRule .= 'require [';
          foreach(self::$requireModules as $index => $module){
           if($index > 0){
            $headerRule .= ", ";
           }
           $headerRule .= '"'. $module .'"';
          }
          $headerRule .= $addedModule.'];'. PHP_EOL;
         }
         $this->_rule = $headerRule . $this->_rule;
        }else{
         throw new Exception('Invalid sieve rule - Basic IF condition is not present in the sieve input','ISW_SIEVE_1000');
        }
        return $this->_rule;
    }
}
?>