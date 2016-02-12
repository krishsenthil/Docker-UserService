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
 * View helper for displaying table related contents
 * We can add more functions in this helper calss when
 * needed
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
 *
 * @author Asif Iqbal <aiqbal3@Collash.com>
 * @modifiedAt Oct 08, 2013
 */
class Noobh_Helper_View_Table {

	/**
	 * Row attribute constant
	 * @var {string}
	 */
	const ROW_ATTRIBUTES = 'ROW_ATTRIBUTES';

	/**
	 * Column attribute constant
	 * @var {string}
	 */
	const COLUMN_ATTRIBUTES = 'COLUMN_ATTRIBUTES';

	/**
	 * tbody attribute constant
	 * @var {string}
	 */
	const TBODY_ATTRIBUTES = 'TBODY_ATTRIBUTES';

	/**
	 * Error message displayed in the table body
	 * @var {string}
	 */
	const ERROR_MESSAGE = 'ERROR_MESSAGE';

	/**
	 * Store pagination status for table
	 * @access private
	 * @var {boolean} $_isPaginationEnabled, default value false
	 */
	private static $_isPaginationEnabled = false;

	/**
	 * Store pagination result per page
	 * @access private
	 * @var {integer} $_resultsPerPage
	 */
	private static $_resultsPerPage;

	/**
	 * Store default sort column
	 * @access private
	 * @var {string} $_isPaginationEnabled, default value false
	 */
	private static $_defaultSortColumn;

	/**
	 * Store pagination status for table
	 * @access private
	 * @var {boolean} $_isPaginationEnabled, default value false
	 */
	private static $_currentPage;

	/**
	 * Store sort type
	 * @access private
	 * @var {string} $_sortType, default value asc
	 */
	private static $_sortType;

	/**
	 * Create html with given data in table view
	 *
	 * Example input params
	 *
	 * $headers = array('First Name' => array('width' => '70px',
	 *                                  'class' => 'sortheader',
	 *                                  'onclick' => "callSortColumn('trackAdminsTable',0);return false;"),
	 *             'Last Name' => array('width' => '95px',
	 *                                  'class' => 'sortheader',
	 *                                  'onclick' => "callSortColumn('trackAdminsTable',1);return false;"),
	 *              'Email'=> array('width' => '190px',
	 *                              'class' => 'sortheader',
	 *                              'onclick' => "callSortColumn('trackAdminsTable',2);return false;"),
	 *              'DSID' => array('width' => '70px',
	 *                              'class' => 'sortheader',
	 *                              'onclick' => "callSortColumn('trackAdminsTable',3);return false;"),
	 *
	 *              'Employee&nbsp;#' => array('width' => '70px',
	 *                                    'class' => 'sortheader',
	 *                                    'onclick' => "callSortColumn('trackAdminsTable',4);return false;"),
	 *
	 *               'Action' => array('width' => '70px',
	 *                                'class' => 'unsortable action-heading')
	 *
	 *       );
	 * $columnNames = array('first_name','last_name','email','dsid','employee_number');
	 * $actionHtmlTemplate = "<span class='icon-cog icon-large settings-toggle' onclick=\"viewDetails({id})\"></span>";
	 * $tableAttributes = array('class' => 'tableCss1 tableCss2 tableCss3',
	 *                           'id' => 'idForTable',
	 *                            Noobh_Helper_View_Table::ROW_ATTRIBUTES => array('class'=>'rowCss1 rowCss2 rowCss3','id'=>'{id}')
	 *                   );
	 *
	 *
	 * Example passing error message:
	 * $data = array(Noobh_Helper_View_Table::ERROR_MESSAGE => 'No results found');
	 *
	 * @param {array} $headers, Display header
	 * @param {array} $columnNames, Column names in $data
	 * @param {array} $data, Data set
	 * @param {string} $actionHtmlTemplate, html to be displayed in action column
	 * @param {array} $tableAttributes, html attributes for <table> tag like css class names
	 * @param {array} $tableName, display name for the table
	 * @param {boolean} $optionBar, enable and disable option bar in the table
	 * @param {boolean} $searchOption,hide and show table rows based on the search text
	 * @return {string} $html
	 */
	public static function display(array $headers, array $columnNames, array $data, $actionHtmlTemplate = null, array $tableAttributes = array(), $tableName = null, $optionsBar = true,$searchOption=false) {
		//Table need css 'sortable' and id attribute for pagination, if not present in $tableAttributes then add it
		$tableCss = 'altfills crs-table';
		$tableId = isset($tableAttributes['id']) ? $tableAttributes['id'] : $tableAttributes['id'] = uniqid('Noobhfw_table_' . rand('999', '9999'));
		//Check options bar required
		if ($optionsBar) {
			$html = '<div class="optionsBar">' . PHP_EOL .
					'<nav class="range"><span>' . $tableName . ''
					. '</span>' . PHP_EOL;
			
			// Add the search input field in the tabel option bar
			if ( $searchOption ) {
				$html .= '<input type="search" name="q" id="table_search" tableid="' . $tableId . '" placeholder="Search" results="0" maxlength="100" autocorrect="off" autocapitalize="off" autocomplete="off" />' . PHP_EOL;
			}
			
			$html.='</nav>'. PHP_EOL;
			if (self::$_isPaginationEnabled) {
				$html .= '<nav class="pagination" tableid="' . $tableId . '"></nav>' . PHP_EOL;
				$tableCss .= ' sortable';
			}
			$html .= '</div>' . PHP_EOL;
		} else {
			$html = '';
		}
		if (isset($data[self::ERROR_MESSAGE])) {
			//Display error message passed as table content
			$html .= $data[self::ERROR_MESSAGE];
		} else {
			if (count($headers) > 0 && count($columnNames) > 0 && count($data)) {
				//Table start
				$html .= '<table';
				$tableAttributes['class'] = isset($tableAttributes['class']) ? $tableAttributes['class'] . $tableCss : $tableCss;
				//Take all table properties from other list
				$copyTableAttr = $tableAttributes;
				unset($copyTableAttr[self::TBODY_ATTRIBUTES]);
				unset($copyTableAttr[self::ROW_ATTRIBUTES]);
				unset($copyTableAttr[self::COLUMN_ATTRIBUTES]);
				if (count($copyTableAttr) > 0) {
					foreach ($copyTableAttr as $attrKey => $attrValue) {
						$html .= ' ' . $attrKey . '="' . $attrValue . '"';
					}
				}
				$html.='>' . PHP_EOL . '<thead>' . PHP_EOL .
						'<tr>' . PHP_EOL;
				//Header starts
				foreach ($headers as $key => $value) {
					$html .= '<th';
					if (is_array($value)) {
						foreach ($value as $attrKey => $attrValue) {
							$html .= ' ' . $attrKey . '="' . $attrValue . '"';
						}
						$html .= '  role="columnheader" scope="col" aria-label="' . $key . '">' . $key;
					} else {
						$html .= '  role="columnheader" scope="col" aria-label="' . $value . '">' . $value;
					}
					$html .= '</th>' . PHP_EOL;
				}
				//Header ends
				$html .= '</tr>' . PHP_EOL .
						'</thead>' . PHP_EOL;
				//Create tbody
				if (isset($tableAttributes[self::TBODY_ATTRIBUTES])) {
					$tAttributes = $tableAttributes[self::TBODY_ATTRIBUTES];
					$html .= '<tbody ';
					foreach ($tAttributes as $key => $value) {
						$html .= ' ' . $key . '=' . $value . ' ';
					}
					$html .= '>' . PHP_EOL;
				} else {
					$html .= '<tbody>' . PHP_EOL;
				}
				//Create row
				foreach ($data as $rowNumb => $row) {
					if (isset($tableAttributes[self::ROW_ATTRIBUTES])) {
						$rowAttr = $tableAttributes[self::ROW_ATTRIBUTES];
						$html .= '<tr';
						foreach ($rowAttr as $key => $value) {
							$html .= ' ' . $key . '=' . self::_parser($value, $row);
						}
						$html .= ' >' . PHP_EOL;
					} else {
						$html .= '<tr>' . PHP_EOL;
					}
					foreach ($columnNames as $column) {
						if (isset($row[$column])) {
							$html .= '<td';
							//Add column attribute
							if (isset($tableAttributes[self::COLUMN_ATTRIBUTES])) {
								if (isset($tableAttributes[self::COLUMN_ATTRIBUTES][$rowNumb])) {
									if (isset($tableAttributes[self::COLUMN_ATTRIBUTES][$rowNumb][$column])) {
										foreach ($tableAttributes[self::COLUMN_ATTRIBUTES][$rowNumb][$column] as $tdAttrName => $tdAttrValue) {
											$html .= " {$tdAttrName}=\"{$tdAttrValue}\" ";
										}
									}
								}
							}
							$html .= '>' . $row[$column] . '</td>' . PHP_EOL;
						}
					}
					//Add action if exist
					if ($actionHtmlTemplate) {
						$html .= '<td>' . self::_parser($actionHtmlTemplate, $row) . '</td>' . PHP_EOL;
					}
					$html .= '</tr>' . PHP_EOL;
				}
				//Tbody and Table ends
				$html .= '</tbody>' . PHP_EOL .
						'</table>' . PHP_EOL;
			}
		}
		if (self::$_isPaginationEnabled) {
			$html .= '<script>' . PHP_EOL .
					'sortables_init("' . $tableId . '",' . self::$_resultsPerPage . ');' . PHP_EOL .
					'callSortColumn("' . $tableId . '",' . self::$_defaultSortColumn . ',' . self::$_resultsPerPage . ',"' . self::$_sortType . '");' . PHP_EOL .
					'var curPage = ' . self::$_currentPage . ';' . PHP_EOL .
					'var rpp = ' . self::$_resultsPerPage . ';' . PHP_EOL .
					'paging(' . self::$_currentPage . ',"' . $tableId . '",true,' . self::$_resultsPerPage . ');' . PHP_EOL .
					'</script>' . PHP_EOL;
		}
		return $html;
	}

	public static function enablePagination($resultsPerPage = 10, $defaultSortColumn = 0, $currentPage = 1, $sortType = 'asc') {
		self::$_isPaginationEnabled = true;
		self::$_resultsPerPage = $resultsPerPage;
		self::$_defaultSortColumn = $defaultSortColumn;
		self::$_currentPage = $currentPage;
		self::$_sortType = $sortType;
	}

	/**
	 * Parse html and replace parse variables with values
	 * @param {string} $text, text to be parsed
	 * @param {array} $values, parse variable values
	 * @return {string} $text, return parsed text
	 */
	private static function _parser($text, $values) {
		preg_match_all('/\{(.*?)\}/', $text, $result);
		$matches = isset($result[1]) ? $result[1] : null;
		if ($matches) {
			foreach ($matches as $variable) {
				$variable = strtolower($variable);
				$text = str_replace("{" . $variable . "}", $values[$variable], $text);
			}
		}
		return $text;
	}

}
