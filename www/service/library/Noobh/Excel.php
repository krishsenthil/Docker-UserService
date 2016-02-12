<?php 
//Adding external library (PHP Excel)
require_once(dirname(dirname(__FILE__)).'/Noobh/Excel/PHPExcel.php');
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
 * This is an abstract class which abstract the external Excel library from 
 * application. When ever we need to change the excel library we will be updating
 * the API's in this layer
 *
 *  @todo : Currently only wrapping download need to support other ways to write excel
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh
 * @since   0.1
 * @date Nov 20, 2013
 */
class Noobh_Excel
{

 /** 
  * Excel version supported
  */
 const EXCEL_VERSION_2003 = 'Excel5';
 const EXCEL_VERSION_2007 = 'Excel2007';
 /**
  * Store default header
  *
  * Default file header
  * header('Content-Description: File Transfer');
  * header('Content-type: application/vnd.ms-excel');
  * header('Content-Disposition: attachment; filename="'.$fileName.'"');
  * header('Content-Transfer-Encoding: binary');
  * header('Expires: 0');
  * header('Cache-Control: must-revalidate');
  * header('Pragma: public');
  * 
  * @access private
  * @var {array} $_defaultFileHeader
  */
  private $_defaultFileHeader = array('Content-Description: File Transfer',
    'Content-type: application/vnd.ms-excel',
    'Content-Transfer-Encoding: binary',
    'Expires: 0',
    'Cache-Control: must-revalidate',
    'Pragma: public'
  );
  private $_supportedExcelVersion = array(self::EXCEL_VERSION_2003,self::EXCEL_VERSION_2007);
  /**
   * Store default excel version to out put
   * @access private
   * @var {string} $_defaultExcelFormat
   */
  private $_defaultExcelVersion = self::EXCEL_VERSION_2003;
  /**
  * Store excel version to out put
  * @access private
  * @var {string} $_excelVersion
  */
  private $_excelVersion;
   /**
    * Store excel file header
    * @access private
    * @var {array} $_fileHeader
    */
   private $_fileHeader;
   /**
    * Store excel filename
    * @todo we can support header other than default header provided by PHP
    * @access private
    * @var {string} $_fileName
    */
   private $_fileName;
   /**
    * Store PHPExcel library object
    * @var {string} $_delimiter
    */
   private $_objPHPExcel;
   /**
    * Store active sheet Index for writting into file
    * @var {string} $_delimiter
    */
   private $_activeSheetIndex;
   /**
    * Initialize Excel object.
    * @access public
    * @param {array} $data
    * @param {array} $headers
    * @param {string} $fileName
    */
  public function __construct(){
    //Default active index
    $this->_activeSheetIndex = 0;
    $this->_objPHPExcel = new PHPExcel();
  }
  /**
   * Get default file name if not supplied by user
   * @access public
   * @param void
   * @param {string} fileName
   */
  private function _getDefaultFileName(){
    return 'Noobhfw_excel_'.rand('999','9999') .".xls";
  }
  /**
  * Set excel header
  * @access public
  * @param {array} $header
  * @return void
  */
  public function setFileHeader($header = array()){
    $this->_fileHeader = $header;
  }
 /**
  * Get excel header
  * @access public
  * @param void
  * @return {array} $header
  */
  public function getFileHeader(){
   	return $this->_fileHeader;
  }

  /**
  * Set excel version
  * @access public
  * @param {string} $excelVersion
  * @return void
  */
  public function setExcelVersion($excelVersion){
    if(in_array($excelVersion,$this->_supportedExcelVersion)){
      $this->_excelVersion = $excelVersion;
    }
  }

 /**
  * Get excel version
  * @access public
  * @param void
  * @return {string} $excelVersion
  */
  public function getExcelVersion(){
    return $this->_excelVersion;
  }
  /**
   * Set file name for excel
   * @access public
   * @param {string} $fileName
   * @return void
   */
  public function setFileName($fileName){
    $this->_fileName  = $fileName;
  }
  /**
   * Get excel file name
   * @access public
   * @param void
   * @return {string} $fileName
   */
  public function getFileName(){
    return $this->_fileName;
  }
 /**
  * Get specified cell based on column and row index
  * @access public 
  * @param {integer} $columnIndex , default 0
  * @param {integer} $rowIndex , default 1
  * @return void
  */
  public function getCell($columnIndex = 0,$rowIndex = 1){
    //Before writing attach worksheet
    $this->_objPHPExcel->setActiveSheetIndex($this->_activeSheetIndex);
    return $this->_objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columnIndex,$rowIndex);
  }

  /**
   * Write excel object to excel writer
   * 
   * @access public 
   * @param void
   * @return void
   */
  public function write(){
    require_once(dirname(dirname(__FILE__)).'/Noobh/Excel/PHPExcel/IOFactory.php');
    if(!$this->_excelVersion){
        $this->_excelVersion = $this->_defaultExcelVersion;
    }
    $this->_objWriter = PHPExcel_IOFactory::createWriter($this->_objPHPExcel, $this->_excelVersion);
  }

  /**
   * Out put the excel file to browser and will be able to 
   * download 
   *
   * @access public
   * @param void
   * @return void
   */
  public function download(){
    //Check for filename else get the default file name
    if(!$this->_fileName){
      $this->_fileName = $this->_getDefaultFileName();
    } 
    //Setting filename in header
    array_push($this->_defaultFileHeader, 'Content-Disposition: attachment; filename="'.$this->_fileName.'"');
    if(!$this->_fileHeader || count($this->_fileHeader) == 0){
      $this->_fileHeader = $this->_defaultFileHeader;
    }
    //Create header for file
    foreach($this->_fileHeader as $header){
       header($header);
    }
    ob_end_clean();
    $this->_objWriter->save('php://output');
    //After write object disconnect the work sheet
    $this->_objPHPExcel->disconnectWorksheets();
    //Clean object writer because it is a big object
    unset($this->_objWriter);
    //Stop after buffer output
    exit(0);
  }
 /**
  * Set data set to excel file object
  * @access public
  * @param {array} $data
  * @param {array} $columnNames, default to startRow column    
  * @param {integer} $startColumn , default to start cell column
  * @param {integer} $startRow
  * @return void
  */
 public function setDataSet($data = array(), $columnNames = array(),$startColumn = 0,$startRow = 1){
  $currentColumn = $startColumn;
  $currentRow = $startRow;
  //Before writing attach worksheet
  $this->_objPHPExcel->setActiveSheetIndex($this->_activeSheetIndex);
  //Write column header
  if(count($columnNames) > 0){
    foreach($columnNames as $title){
      $this->_objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($currentColumn, $currentRow, $title);
      $currentColumn++;
    }
    $currentRow++;
  } 
  $currentColumn = $startColumn;
  //Writing Dataset [Only Supporting two dimensional arrays]
  if(count($data) > 0){
    foreach($data as $row){
      if(is_array($row)){
        //Writing Row
        if(count($row) > 0){
          foreach($row as $item){
             $this->_objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($currentColumn++, $currentRow, $item); 
          }
          //Reset Column
          $currentColumn = $startColumn;
          $currentRow++;
        }
      }else{
        $this->_objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($currentColumn++, $currentRow, $row); 
      }
    }
  }
 }
}