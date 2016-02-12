<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Framework
 * @package    View
 * @subpackage Helper
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 * @TODO: We can enchance this editor to use different styles and colors like
 * a standard editor.
 * 
 * View helper for creating what you see is what you get editor
 *
 * Dependent JS
 *
 *  1. assets/plugins/wysiwyg-editor/editor.js
 *  
 * Dependent css
 * 
 * 1. assets/plugins/wysiwyg-editor/editor.css
 * 
 * Usage: 
 * 
 *     PHP CODE:
 *    
 *     Controller Code:
 *     
 *     $this->headLink()->prependScript('/assets/framework/plugins/wysiwyg-editor/js/wysiwyg-editor.js');
 *     $this->headLink()->prependStyleSheet('/assets/framework/plugins/wysiwyg-editor/css/wysiwyg-editor.css');
 *     
 *     View Code:
 *    
 * 			$attributes = array(Noobh_Helper_View_WysiwygEditorFactory::TEXTAREA_ATTRIBUTES => array('name' => 'description',
 *                                                                                              'onblur' => 'validation.checkEmpty(this.id)'),
 *                        Noobh_Helper_View_WysiwygEditorFactory::DISPLAYAREA_ATTRIBUTES => array('style' => 'background-color:white;width:100%;min-height:50px;')
 *                        );
 *      
 *      $editor = Noobh_Helper_View_WysiwygEditorFactory::createEditor('someId','content',$attributes);
 *      echo $editor->getEditorHTML();
 *      echo $editor->getDisplayHTML();
 *     
 *     JAVASCRIPT CODE:
 *     
 *      //Initialize plugin after dom loads
 *      Noobh.fw.editor.init('someId');
 *      
 *      
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  View
 * @subpackage Helper
 * @since   0.1
 * @date Sept 10, 2013
 * 
 */

class Noobh_Helper_View_WysiwygEditorFactory{
 /**
  * Html attributes for text area
  * @var {string} TEXTAREA_ATTRIBUTES
  */
 const TEXTAREA_ATTRIBUTES = 'TEXTAREA_ATTRIBUTES';
 /**
  * Html attributes for display area
  * @var {string} DISPLAYAREA_ATTRIBUTES
  */
 const DISPLAYAREA_ATTRIBUTES = 'DISPLAYAREA_ATTRIBUTES';
 
 /**
  * This is a factory method for creating editor object.If Id is not passed
  * it will generate a random editor id
  * 
  * @access public
  * @param {string} $editorId
  * @param {string} $content, Editor display content
  * @param {array} $HTMLattributes
  * @param {boolean} $isHTML, Html replaced with  plain text if false
  * @return {Editor} $editor
  */
 public static function createEditor($editorId = NULL,$content = NULL,$HTMLattributes = array(),$isHTML = true){
   $editorId = ($editorId)? $editorId : uniqid('Noobhfw_wysiwyg_'.rand('999','9999'));
   //Set id 
   $HTMLattributes[self::TEXTAREA_ATTRIBUTES]['id'] = $editorId;
   $HTMLattributes[self::DISPLAYAREA_ATTRIBUTES]['id'] = 'display_'.$editorId;
   $HTMLattributes[self::DISPLAYAREA_ATTRIBUTES]['ishtml'] = $isHTML;
   $editor = new Editor();
   $textArea =  '<textarea class="NoobhEditor" ';
   $displayArea =  '<div class="NoobhDisplay" ';
   if(count($HTMLattributes) > 0){
    //Set all information for creating editor
    if(isset($HTMLattributes[self::TEXTAREA_ATTRIBUTES]) && count($HTMLattributes[self::TEXTAREA_ATTRIBUTES]) > 0){
     $attributes = $HTMLattributes[self::TEXTAREA_ATTRIBUTES];
     foreach($attributes as $attrKey => $attrValue){
       $textArea .= " {$attrKey}='{$attrValue}' ";
      //$textArea .= ' ' . $attrKey .'=\"' .$attrValue .'\"';
     }
    }
    if(isset($HTMLattributes[self::DISPLAYAREA_ATTRIBUTES]) && count($HTMLattributes[self::DISPLAYAREA_ATTRIBUTES]) > 0){
     $attributes = $HTMLattributes[self::DISPLAYAREA_ATTRIBUTES];
     foreach($attributes as $attrKey => $attrValue){
      $displayArea .= " {$attrKey}='{$attrValue}' ";
     }
    }
   }   
   $content  =  ($content)? $content:'';
   $textArea .= '>' . $content . '</textarea>';
   if($isHTML != true){
    //Convert html to plain text and convert "\r" and "\n" to <br>
    $content = strtr($content,Array("<"=>"&lt;",">"=>"&gt;","\r"=>" ","\n"=>"<br/>"));
   }
   $displayArea .= ' >'. $content . '</div>';
   $editor->setId($editorId);
   $editor->setEditorHtml($textArea);
   $editor->setDisplayHtml($displayArea);
   $editor->setIsHTML($isHTML);
   return $editor;
 }
 
}
/**
 * Avoid loading one more file for editor components we are declaring
 * Editor class in this file
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  View
 * @subpackage Helper
 * @since   0.1
 * @date Sept 10, 2013
 */

class Editor{
 /**
  * Store editor id
  * @access private
  * @var {string} $_id
  */
 private $_id;
 /**
  * Store editor html
  * @access private 
  * @var {string} $_editorHTML
  */
 private $_editorHTML;
 /**
  * Store display html
  * @access private
  * @var {string} $_displayHTML
  */
 private $_displayHTML;
 /**
  * Store display is html or not
  * @access private
  * @var {string} $_displayHTML
  */
 private $_isHTML; 
 /**
  *Create text editor instance  
  * @access public
  * @param {void}
  * @return {void}
  */
 public function __construct(){
  
 }
 /**
  * Set editor html
  * @param {string} $html
  * @return {void}
  */
 public function setEditorHTML($html){
  $this->_editorHTML = $html;
 }
 /**
  * Get editor html
  * @param {void}
  * @return {string} $editorHTML
  */
 public function getEditorHTML(){
  return $this->_editorHTML;
 }
 /**
  * Set display html
  * @param {string} $html
  * @return {void}
  */
 public function setDisplayHTML($html){
  $this->_displayHTML = $html;
 }
 /**
  * Get display html
  * @param {void}
  * @return {string} $displayHTML
  */
 public function getDisplayHTML(){
  return $this->_displayHTML;
 } 
 /**
  * Set display is html
  * @param {string} $isHTML
  * @return {void}
  */
 public function setIsHTML($isHTML){
 	$this->_isHTML = $isHTML;
 }
 /**
  * Get display is html
  * @param {void}
  * @return {string} $isHTML
  */
 public function getIsHTML(){
 	return $this->_isHTML;
 }

 /**
  * Set editor Id
  * @param {string} $id
  * @return {void}
  */
 public function setId($id){
 	$this->_id = $id;
 }
 /**
  * Get editor Id
  * @param {void}
  * @return {string} $id
  */
 public function getId(){
 	return $this->_id;
 }
}