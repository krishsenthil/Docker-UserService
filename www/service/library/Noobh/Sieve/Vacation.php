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
 * Create out of office / vacation sieve rule
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh_Sieve
 * @since   0.1
 * @date June 15, 2012
 *
 */
class Noobh_Sieve_Vacation{
 /**
  * Store subject
  * @var {string} $_subject
  */
 private $_subject;
 /**
  * Store content
  * @var {string} $_content
  */
 private $_content;
 /**
  * Store html content
  * @var {string} $_htmlContent
  */
 private $_htmlContent;
 
 /**
  * Store html template path
  * @var {string} $_templatePath
  */ 
 private $_templatePath;
 
 /**
  * Store rule
  * @var {string} $_rule
  */
 private $_rule;
 /**
  * Store vacation start date
  * @var {string} $_startDate
  */
 private $_startDate;
 /**
  * Store vacation end date
  * @var {string} $_endDate
  */
 private $_endDate;
 /**
  * Store number vacation
  * @var {integer} $_date
  */
 private $_days;  
  
 /**
  * Instanciate new vacation rule
  * 
  * @access public
  * @param {string} $subject
  * @param {string} $content
  * @return void
  */
  public function __construct($subject,$content=NULL,$htmlContent=NULL){
   $this->_subject = $subject;
   $this->_content = $content;
   $this->_htmlContent = $htmlContent;
  }
  
  /**
   * Set subject
   *
   * @access public
   * @param {string} $subject
   * @return void
   */
  public function setSubject($subject){
  	$this->_subject = $subject;
  }
  /**
   * Set content
   *
   * @access public
   * @param {string} $content
   * @return void
   */
  public function setContent($content){
  	$this->_content = $content;
  }
  /**
   * Set html content
   *
   * @access public
   * @param {string} $htmlContent
   * @return void
   */
  public function setHtmlContent($htmlContent){
   $this->_htmlContent = $htmlContent;
  }
  /**
   * Set template path
   *
   * @access public
   * @param {string} $templatePath
   * @return void 
   */
  public function setTemplatePath($path){
  	$this->_templatePath = $path;
  }
  
  /**
   * Set start date
   *
   * @access public
   * @param {string} $startDate
   * @return void
   */
  public function setStartDate($startDate){
  	$this->_startDate = $startDate;
  }
  /**
   * Set number of days for vacation
   *
   * @access public
   * @param {string} $days
   * @return void
   */
  public function setEndDate($days){
  	$this->_days = $days;
  }
  /**
   * Get subject
   *
   * @access public
   * @param void
   * @return {string} $subject
   */
  public function getSubject(){
  	return $this->_subject;
  }
  /**
   * Get content
   *
   * @access public
   * @param void
   * @return {string} $content
   */
  public function getContent(){
  	return $this->_content;
  }
  /**
   * Get html content
   *
   * @access public
   * @param void
   * @return {string} $htmlContent
   */
  public function getHtmlContent(){
  	return $this->_htmlContent;
  }
  /**
   * Get template path
   *
   * @access public
   * @param void
   * @return {string} $templatePath
   */
  public function getTemplatePath(){
  	return $this->_templatePath;
  }
  /**
   * Get start date
   *
   * @access public
   * @param void
   * @return {string} $startDate
   */
  public function getStartDate(){
  	return $this->_startDate;
  }
  /**
   * Get number of days for vacation
   *
   * @access public
   * @param void
   * @return {string} $days
   */
  public function getDays(){
  	return $this->_days;
  }
  /**
   * Set number of days for vacation
   *
   * @access public
   * @param {string} $days
   * @return void
   */
  public function setDays($days){
  	$this->_days = $days;
  }
  /**
   * Create vacation rule
   *
   * @access public
   * @param void
   * @return {string} $rule
   */
  public function createRule(){
  	$this->_rule = "vacation :days {$this->_days} :subject \"{$this->_subject}\" :mime".PHP_EOL. "\"";
  	if($this->_content){
  	 $this->_rule .= "Content-Type:multipart/alternative;".PHP_EOL.
  	                  addslashes($this->_content) .PHP_EOL;
  	}
  	if($this->_htmlContent){
     $this->_rule .= "Content-Type:text/html; charset=us-ascii".PHP_EOL;
  	 if($this->getTemplatePath()){
  	  $this->_rule .= addslashes(Noobh_View_Template_Parser::parse($this->getTemplatePath(),array('body' => $this->_htmlContent))) .PHP_EOL;
  	 }else{
  	  $this->_rule .= addslashes($this->_htmlContent) .PHP_EOL;
  	 }
  	}
  	$this->_rule .= "\";".PHP_EOL;
  	return $this->_rule;
  }
}