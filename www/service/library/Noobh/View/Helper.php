<?php 
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Framework
 * @package    Noobh_Helper
 * @subpackage View
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 *
 * Collash Inc Internal
 *
 * View helper class for performing
 * common actions in the view
 * 
 * This class is only used for extensive usage of
 * controller helper functions so it is added in the
 * base controller file itself
 *
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package    Noobh_View
 * @subpackage View
 * @since   0.1
 * @date Mar 28, 2012
 *
 */
class Noobh_View_Helper{
    
    /**
     * Constant for append type files
     * @access private
     * @var {string}
     */
    private $_append = 'APPEND';
    /**
     * Constant for prepend type files
     * @access private
     * @var {string}
     */
    private $_prepend = 'PREPEND';
    
    /**
     * Constant for script type files
     * @access private
     * @var {string}
     */
    private $_scripts = 'SCRIPTS';
   /**
    * Constant for style sheet type files
    * @access private
    * @var {string}
    */
    private $_styleSheets = 'STYLE_SHEETS';
    /**
     * Store files to be added 
     * @access private
     * @var {array}
     */
    private $_files;
    
    /**
     * While getting the script and style files, we need to attach another file
     * and exopse the method like append and prepend when required
     * but for reducing the object instantiation we are using a switch variable
     * which set which type of file required, currently we are only having
     * Stylesheet and script
     * @access private
     * @var {string}
     */
    private $_currentFileType;
    
    /**
     * Helper class for controller
     * @access public
     * @param void
     * @return Noobh_Controller_Helper
     */
    public function __construct(){
        $this->_files= array();
    }
    /**
     * Append script
     * @access public
     * @param {string} $scriptPath
     * @return void
     */
    public function appendScript($scriptPath){
        $this->_files[$this->_scripts][$this->_append][] = $scriptPath;
    }
    /**
     * Append style sheet
     * @access public
     * @param {string} $styleSheetPath
     * @return void
     */
    public function appendStyleSheet($styleSheetPath){
        $this->_files[$this->_styleSheets][$this->_append][] = $styleSheetPath;
    }
    
    /**
     * Prepend script
     * @access public
     * @param {string} $scriptPath
     * @return void
     */
    public function prependScript($scriptPath){
        $this->_files[$this->_scripts][$this->_prepend][] = $scriptPath;
    }
    /**
     * Prepend style sheet
     * @access public
     * @param {string} $styleSheetPath
     * @return void
     */
    public function prependStyleSheet($styleSheetPath){
        $this->_files[$this->_styleSheets][$this->_prepend][] = $styleSheetPath;
    }    
    /**
     * Get append script
     * @access public
     * @param {void}
     * @return {array} 
     */
    public function getAppendScripts(){
     return (isset($this->_files[$this->_scripts][$this->_append])? $this->_files[$this->_scripts][$this->_append] : array());
    }
    
    /**
     * Get append style sheet
     * @access public
     * @param {void}
     * @return {array} 
     */
    public function getAppendStyleSheets(){
     return (isset($this->_files[$this->_styleSheets][$this->_append])? $this->_files[$this->_styleSheets][$this->_append] : array());
    }
    
    /**
     * Get prepend script
     * @access public
     * @param {void}
     * @return {array} 
     */
    public function getPrependScripts(){
     return (isset($this->_files[$this->_scripts][$this->_prepend])? $this->_files[$this->_scripts][$this->_prepend] : array());
    }
    /**
     * Get prepend style sheet
     * @access public
     * @param {void}
     * @return {array} 
     */
    public function getPrependStyleSheets(){
     return (isset($this->_files[$this->_styleSheets][$this->_prepend])? $this->_files[$this->_styleSheets][$this->_prepend] : array());
    }
    /**
     * Create links for view
     * @param {string} $fileType
     * @param {string} $position
     * @return {string} $result
     */
    private function _createlinks($fileType,$position){
     $result = '';
     if($fileType == $this->_scripts){
      if($position == $this->_append){

       foreach($this->_files[$this->_scripts][$this->_append] as $link){
        $formatted = "<script type='text/javascript' src='" . $link ."'></script>" .PHP_EOL;
        if(isset($result)){
         $result .= $formatted;
        }else{
         $result = $formatted;
        }
       }
      }
      if($position == $this->_prepend){
       foreach($this->_files[$this->_scripts][$this->_prepend] as $link){
        $formatted = "<script type='text/javascript' src='" . $link ."'></script>" .PHP_EOL;
        if(isset($result)){
         $result .= $formatted;
        }else{
         $result = $formatted;
        }
       }
      }
     }
     return $result;
    }

    /**
     * Read file list via standard object
     * This will give a fluid interface than
     * creating a new class and instantiating it.
     *
     * Object contain both append and prepend scripts
     *
     *
     * return example:
     *
     * <script type='text/javascript' src='http://Noobh.Collash.com/js/jquery/jquery.js'></script>
     *
     * @access public
     * @return {stdClass}
     */
    public function getScripts(){
     $scripts = new stdClass();
     $scripts->append = NULL;
     $scripts->prepend = NULL;
     if(isset($this->_files[$this->_scripts][$this->_append])){
      $scripts->append = $this->_createlinks($this->_scripts, $this->_append);
     }
     if(isset($this->_files[$this->_scripts][$this->_prepend])){
      $scripts->prepend = $this->_createlinks($this->_scripts, $this->_prepend);
     }
     return $scripts;
    }    
    
    /**
     * Read file list via standard object
     * This will give a fluid interface than
     * creating a new class and instantiating it.
     *
     * Object contain both append and prepend style sheeets
     *
     *
     * return example:
     *
     * <script type='text/javascript' src='http://Noobh.Collash.com/js/jquery/jquery.css'></script>
     *
     * @access public
     * @return {stdClass}
     */
    public function getStyleSheets(){
        $styleSheet = new stdClass();
        $styleSheet->append = NULL;
        $styleSheet->prepend = NULL;
        if(isset($this->_files[$this->_styleSheets][$this->_append])){
         $styleSheet->append = $this->_createlinks($this->_styleSheets, $this->_append);
        }
        if(isset($this->_files[$this->_styleSheets][$this->_prepend])){
         $styleSheet->prepend = $this->_createlinks($this->_styleSheets, $this->_prepend);
        }
        return $styleSheet;
    }
    
}