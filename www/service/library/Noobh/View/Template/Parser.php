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
 * Parse template and populate template variables
 *
 * @author Vijay <vbose@collash.com>
 * @copyright Collash Inc
 * @package    Noobh_View
 * @subpackage View
 * @since   0.1
 * @date Mar 28, 2012
 *
 */
class Noobh_View_Template_Parser{
    /**
     * Parser template and populate with variable
     *
     * $variableList = [variableName] => 'value' 
     *
     * @param {string} $templatePath, file path for template
     * @param {array} $variableList, list of variable to be populated in the template
     * @throws if template file dosen't exist
     * @return {string} $template, parsed and variable populated template
     */
    public static function parse($templatePath,array $variableList){
        $template = '';
        if(file_exists($templatePath)){
            $template = file_get_contents($templatePath);
            if(count($variableList) > 0){
                $search = preg_match_all('/{.*?}/', $template, $variable);
                for($i = 0; $i < $search; $i++)
                {
                    $matches[0][$i] = str_replace(array('{', '}'), null, $variable[0][$i]);

                }
                
                foreach($matches[0] as $value)
                {
                 if(isset($variableList[$value])){
                  $template = str_replace('{' . $value . '}', $variableList[$value], $template);
                 }  
                }
            }
        }else{
            throw new Exception('Template file doesn\'t exist in - '. $templatePath);
        }
        return $template;
    }

}