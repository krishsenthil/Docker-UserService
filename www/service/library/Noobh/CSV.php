<?php 
/**
 * Noobh Framework
 *
 * Collash Inc Internal
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
 * Class is basically used for creating and reading CSV files.
 *
 * This class wrap exisitng php native csv functions
 *
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh
 * @since   0.1
 * @link https://connectme.Collash.com/docs/DOC-138140
 * @date Nov 20, 2013
 */
class Noobh_CSV
{
   /**
    * Store csv data
    * @access private
    * @var {array} $_data
    */
   private $_data;
   /**
    * Store default header
    * @access private
    * @var {array} $_defaultFileHeader
    */
   private $_defaultFileHeader;

   /**
    * Store csv file header
    * @access private
    * @var {array} $_fileHeader
    */
   private $_fileHeader;
   /**
    * Store csv filename
    * @todo we can support header other than default header provided by PHP
    * @access private
    * @var {string} $_fileName
    */
   private $_fileName;
   /**
    * Store csv out put buffer
    * @access private
    * @var {string} $_outPut
    */
   private $_outPut;
   /**
    * Initialize CSV object.
    * @access public
    * @param {array} $data
    * @param {array} $headers
    * @param {string} $fileName
    */
   public function __construct($fileName = NULL){
    //Create unique file name
    $this->_fileName = ($fileName)? $fileName : 'Noobhfw_csv_'.rand('999','9999');
    /**
     *   Default file header
     *   header('Content-Description: File Transfer');
     *   header('Content-type: text/csv');
     *   header('Content-Disposition: attachment; filename="'.$fileName.'"');
     *   header('Content-Transfer-Encoding: binary');
     *   header('Expires: 0');
     *   header('Cache-Control: must-revalidate');
     *   header('Pragma: public');
     */
    $this->_defaultFileHeader = array('Content-Description: File Transfer',
      'Content-type: text/csv',
      'Content-Disposition: attachment; filename="'.$this->_fileName.'"',
      'Content-Transfer-Encoding: binary',
      'Expires: 0',
      'Cache-Control: must-revalidate',
      'Pragma: public',
    );
   }
   /**
    * Set csv header
    * @access public
    * @param {array} $header
    * @return void
    */
   public function setFileHeader($header = array()){
    $this->_fileHeader = $header;
   }
   /**
    * Get csv header
    * @access public
    * @param void
    * @return {array} $header
    */
   public function getFileHeader(){
   	return $this->_fileHeader;
   }

   /**
    * Set data for csv
    * @access public
    * @param {string} $data
    * @return void
    */
   public function setData($data){
    $this->_data = $data;
   }
   /**
    * Get csv data
    * @access public
    * @param {string} $data
    * @return void
    */
   public function getData(){
    return $this->_data;
   }
   /**
    * Interface for setting csv out put buffer
    * @access public
    * @param {string} $output
    * @return void
    */
   public function setOuputBuffer($output){
    $this->_outPut = $output;
   }
   /**
    * Get csv out put buffer
    * @access public
    * @param void
    * @return {string} $output
    */
   public function getOuputBuffer(){
    return $this->_outPut;
   }
   /**
    * Set file name for csv
    * @access public
    * @param {string} $fileName
    * @return void
    */
   public function setFileName($fileName){
    $this->_fileName  = $fileName;
   }

   /**
    * Get csv file name
    * @access public
    * @param void
    * @return {string} $fileName
    */
   public function getFileName(){
    return $this->_fileName;
   }
   /**
    * Out put CSV file
    * @access public
    * @param void
    * @return void
    */
   public function download(){
      if(count($this->_fileHeader) == 0){ 
         //Default header
         $this->_fileHeader = $this->_defaultFileHeader;
      }
      //Create header for CSV file
      foreach($this->_fileHeader as $header){
         header($header);
      }
      $content = ob_get_clean();
      echo $content;
      exit();
   }

   /**
    * Write data to CSV out put buffer
    * @access public
    * @param void
    * @return void
    */
   public function write(){
      //Over ride with default
      if(!$this->_outPut){
        $this->_outPut = fopen('php://output', 'w');
      }
      ob_get_clean();
      ob_start();
      foreach($this->_data  as $row){
        fputcsv($this->_outPut,$row);
      }
      fclose($this->_outPut);
   }

   // /**
   //  * Read data from csv file
   //  * @access public
   //  * @param {string} $path, if null try to read form current location
   //  * @param {string} $csize, longest line found in the file
   //  * @return void
   //  */
   // public function readFromFile($path = null,$csize = 1000){
   //  if (($handle = fopen($path, "r")) !== FALSE) {
   //   $row = 1;
   //   while (($data = fgetcsv($handle, $csize, $this->_delimiter)) !== FALSE) {
   //    if($row == 1){
   //     $this->_loadFromArrayData($data,FALSE);
   //    }else{
   //     $this->_loadFromArrayData($data);
   //    }
   //    $row++;
   //   }
   //   fclose($handle);
   //  }
   // }
}