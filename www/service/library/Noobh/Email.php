<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * All email related function are specified in this
 * file
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
 * Email wrapper class for native mail().
 * Validate different input parameters and
 * send email to corresponding address. With out
 * a valid email sender this class with throw exception
 *
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package    Noobh
 * @subpackage    Noobh
 * @since   0.1
 * @date Oct 10, 2012
 *
 */

class Noobh_Email
{

 /**
  * Store header "From"
  * @property
  * @var from header
  */
 const HEADER_FROM = "From: ";
 /**
  * Store header "To"
  * @property
  * @var const to header
  */
 const HEADER_TO = "To: ";
 /**
  * Store header "Cc"
  * @property
  * @var const Cc header
  */
 const HEADER_CC = "Cc: ";
 /**
  * Store header "Bcc"
  * @property
  * @var const Bcc header
  */
 const HEADER_BCC = "Bcc: ";

 /**
  * Store reciver email address
  * @property
  * @access private
  * @var {Mixed} One reciver email address or an array of email addresses
  */
 private $_emailTo;

 /**
  * Store cc email address
  * @property
  * @access private
  * @var {Mixed} One reciver email address or an array of email addresses
  */
 private $_emailCc;

 /**
  * Store Bcc email address
  * @property
  * @access private
  * @var {Mixed} One reciver email address or an array of email addresses
  */
 private $_emailBcc;

 /**
  * Store sender email address
  * @property
  * @access private
  * @var {String} email address from
  */
 private $_emailFrom;

 /**
  * Store header
  * @property
  * @access private
  * @var {String} email header
  */
 private $_header;

 /**
  * Store email subject
  * @property
  * @access private
  * @var {String} email subject
  */
 private $_subject;

 /**
  * Store body of email
  * @property
  * @access private
  * @var {String} email body
  */
 private $_body;


 /**
  * Need to pass reciver email address for create an email object
  *
  * @access public
  * @param {Mixed} One reciver email address or an array of email addresses
  * @return void
  */
 public function __construct($toEmail){

  $this->setEmailTo($toEmail);
 }


 /**
  * Return reciver email address
  * @access public
  * @param void
  * @return {Mixed} One reciver email address or an array of email addresses
  */
 public function getEmailTo(){
  return $this->_emailTo;
 }

 /**
  * Set reciver email address
  * @access public
  * @param {Mixed} One reciver email address or an array of email addresses
  * @return void
  */
 public function setEmailTo($emailAddress) {
  $this->_emailTo = $this->_isValidEmail($emailAddress);
 }

 /**
  * Return cc email address
  * @access public
  * @param void
  * @return {Mixed} One cc email address or an array of email addresses
  */
 public function getEmailCc(){
  return $this->_emailCc;
 }

 /**
  * Set cc email address
  * @access public
  * @param {Mixed} One cc email address or an array of email addresses
  * @return void
  */
 public function setEmailCc($emailAddress) {
  $this->_emailCc = $this->_isValidEmail($emailAddress);
 }

 /**
  * Return Bcc email address
  * @access public
  * @param void
  * @return {Mixed} One Bcc email address or an array of email addresses
  */
 public function getEmailBcc(){
  return $this->_emailBcc;
 }

 /**
  * Set Bcc email address
  * @access public
  * @param {Mixed} One Bcc email address or an array of email addresses
  * @return void
  */
 public function setEmailBcc($emailAddress) {
  $this->_emailBcc = $this->_isValidEmail($emailAddress);
 }

 /**
  * Return sender email address
  * @access public
  * @param void
  * @return {String} email address
  */
 public function getEmailFrom(){
  return $this->_emailFrom;
 }

 /**
  * Set sender email address
  * @access public
  * @param {String} email address
  * @return void
  */
 public function setEmailFrom($emailAddress){
  $this->_emailFrom = $this->_isValidEmail($emailAddress);
 }

 /**
  * Return email header
  * @access public
  * @param void
  * @return {String} email header
  */
 public function getHeader(){
  return $this->_header;
 }

 /**
  * Set email header
  * @access public
  * @param {String} email header
  * @return void
  */
 public function setHeader($header){
  $this->_header = (string) $header;
 }

 /**
  * Return email subject
  * @access public
  * @param void
  * @return {String} email subject
  */
 public function getSubject(){
  return $this->_subject;
 }

 /**
  * Set email subject
  * @access public
  * @param {String} email subject
  * @return void
  */
 public function setSubject($subject){
  $this->_subject = (string) $subject;
 }

 /**
  * Return email body
  * @access public
  * @param void
  * @return {String} email body
  */
 public function getBody(){
  return $this->_body;
 }

 /**
  * Set email body
  * @access public
  * @param {String} email body
  * @return void
  */
 public function setBody($body){
  $this->_body = (string) $body;
 }

 /**
  * Send email
  *
  * @access public
  * @param void
  * @return void
  */
 public function send() {

  //Check reciver email address is not null
  if (isset ( $this->_emailTo )) {

   //If header is not present create a new header
   if (! isset ( $this->_header )) {

    //We need a from email address to send any email
    if(isset($this->_emailFrom)){

     // To send HTML mail, the Content-type header must be set
     $headers = 'MIME-Version: 1.0' . "\r\n";
     $headers .= 'Content-type: text/html; charset=utf-8; Content-Transfer-Encoding=base64 ' . "\r\n";
     $this->_header = $headers ;
     $this->_header .= self::HEADER_FROM . $this->_emailFrom . "\r\n";
     //Create "TO" part
     if (isset ( $this->_emailTo )) {

      //Check multiple recipients
      if(is_array($this->_emailTo)){
       $this->_emailTo = $this->_arrayToString($this->_emailTo);
      }
     }

     //Create Header "CC"
     if (isset ( $this->_emailCc )) {
      $this->_header .= self::HEADER_CC ;
      $this->_createHeader(
        $this->_emailCc
      );
      $this->_header .= "\r\n" ;
     }

     //Create Header "BCC"
     if (isset ( $this->_emailBcc )) {
      $this->_header .= self::HEADER_BCC ;
      $this->_createHeader(
        $this->_emailBcc
      );
      $this->_header .= "\r\n" ;
     }

    }else{
     throw new Exception (
       "You have to give sender email address"
     );
    }

   } else {
    //we have email header set already from the calling method

    //Check multiple recipients
    if(is_array($this->_emailTo)){
     $this->_emailTo = $this->_arrayToString($this->_emailTo);
    }
   } // end of IF- else loop - Header check
    
   //Send mail
   $success = mail (
     $this->_emailTo,
     $this->_subject,
     $this->_body,
     $this->_header
   );
   if (!$success) {
    throw new Exception (
      "Error in sending mail to " . $this->_emailTo
    );
   }

  } else {
   throw new Exception ( "You need to set reciver email address" );
  }
 }

 /**
  * If header is not set this method will create a fixed header
  * for any email
  *
  * @access private
  * @param void
  * @return void
  */
 private function _createHeader($emails) {

  if ($emails != null) {

   if (is_array ( $emails )) {

    //Check single or multiple emails
    $count = 0;
    foreach ( $emails as $emailAddress ) {

     if($count > 0){
      $this->_header .= ' , ';
     }
     $this->_header .= $emailAddress;
     $count++;
    }
   } else {
    $this->_header .= $emails;
   }
  }

 }

 /**
  * Validate email address
  * @access private
  * @param {String} Email address
  * @return {String} email address
  */
 private function _isValidEmail($emailAddress){
  //Check for an array of email address
  if(is_array($emailAddress)){
   $emailArray = array();
   foreach($emailAddress as $row){

    if (!filter_var($row, FILTER_VALIDATE_EMAIL)) {
     // email is invalid; throw exception
     throw new Exception (
       $row . "is not a valid email address"
     );
    }
    //Create valid array of emails
    $emailArray[] = (string)$row;
   }
   $emailAddress = $emailArray;
  }else{

   $emailAddress = (string)$emailAddress;
   if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
    // email is invalid; throw exception
    throw new Exception (
      $emailAddress . "is not a valid email address."
    );

   }
  }
  // email appears to be valid
  return $emailAddress;
 }

 /**
  *
  * Converts given array into comma seperated string
  * @param {Array} $input
  * @access private
  * @param {void}
  * @return {string}
  */
 private function _arrayToString(array $input) {
  if(!is_array($input)) {
   throw new Exception (
     "Given input should be an array." . gettype($input). "is given"
   );
  } else {
   //To add comma after first email
   $count = 0;
   //Store "TO" email string
   $emails = null;
   foreach($input as $email){
    if($count > 0){
     $emails .= ' , ';
    }
    $emails .= $email;
    $count++;
   }
   return $emails;
  }
 }
}