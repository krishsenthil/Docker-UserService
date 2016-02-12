<?php
/**
 * Noobh Framework
 *
 * Collash Inc Internal
 *
 *
 * @category   Noobh
 * @package    Noobh_Ldap
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 */

/**
 * Base class which abstract all core functionalies for
 * all calander functionalities a like out putting header
 * file making and so on
 * 
 * @todo : Tested with only mac clander
 * @author Vijay <vbose@Collash.com>
 * @category   Noobh
 * @package    Noobh_ICS
 * @copyright  Copyright (c) Collash Inc
 * @license    Collash Inc
 * @since   0.1
 * @date Oct 10, 2014
 */
abstract class Noobh_Ics_Abstract{
	/** 
	 * Setting correct hearder for the ics files
	 *
	 * @access public
	 * @param {string} $filename
	 * @return void
	 */
	public static function printHeader($fileName){
		if($fileName){
			header('Content-type: text/calendar; charset=utf-8');
		    header('Content-Disposition: attachment; filename=' . $filename);
		}else{
			throw new Exception("Empty filename for ics file while printing header", "Noobh_Ics_Abstract-1");
		}
	}
}