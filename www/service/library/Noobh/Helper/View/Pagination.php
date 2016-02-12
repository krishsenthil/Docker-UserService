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
 * View helper for displaying data pagination
 *
 * Dependent JS and CSS files
 *  
 *  1. sorttable.js
 *  2. pagination.js
 *  3. training.global.css
 *  4. training.global.js
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  View
 * @subpackage Helper
 * @since   0.1
 * @date Oct 10, 2012
 */

abstract class Noobh_Helper_View_Pagination{

    /**
     * Create html with given data for pagination view
     * 
     * Example input params
     * 
     * @param {string} $tableId, Table ID
     * @param {string} $tableName, Name for table
     * @param {string} $resultsPerPage, Page result to be shown in each paginated page
     * @param {integer} $defaultSortColumn, Default column for sorting (Default value 0)
     * @param {integer} $currentPage, current display page (Default value 1)
     * @return {string} $html
     */
    protected static function display($tableId,$tableName,$resultsPerPage = 10,$defaultSortColumn = 0,$currentPage = 1){
        return '<div class="optionsBar">' .PHP_EOL.
                '<nav class="range">'.$tableName.'</nav>' .PHP_EOL.
                '<nav class="pagination" tableid="'.$tableId.'"></nav>' .PHP_EOL.
                '</div>'.PHP_EOL;
    }
    
    protected static function script($tableId,$tableName,$resultsPerPage = 10,$defaultSortColumn = 0,$currentPage = 1){
       return  '<script>' .PHP_EOL.
               'sortables_init("'.$tableId.'",'.$resultsPerPage.');' .PHP_EOL.
               'callSortColumn("'.$tableId.'",' .$defaultSortColumn. ','.$resultsPerPage.');' .PHP_EOL.
               'var curPage = '.$currentPage.';' .PHP_EOL.
               'var rpp = '.$resultsPerPage.';'.PHP_EOL.
               'paging('.$currentPage.',"'.$tableId.'",'. true .','.$resultsPerPage.');'.PHP_EOL.
               '</script>' .PHP_EOL;
    }
    
}