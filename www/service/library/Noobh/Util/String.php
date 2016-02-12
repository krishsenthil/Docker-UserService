<?php 
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Framework
 * @package    Noobh_Util
 * @subpackage Util
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */
/**
 *
 * Collash Inc Internal
 *
 * Utility fucntion for string related operations
 * are defined in this class
 *
 *
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh
 * @since   0.1
 * @date Aug 28, 2012
 */
class Noobh_Util_String extends ArrayObject
{
 /**
  * Convert all <br> to \n
  * @access public
  * @param {string} $string
  * @return {mixed}
  */
 public static function br2nl($string){
 	return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
 }
}