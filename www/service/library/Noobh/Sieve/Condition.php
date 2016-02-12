<?php
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
 * This is a static class which is used by sieve rule
 * creator class. This can be also used out side framewok
 * by directly calling for getting sieve condition rules
 *
 * Create sieve rule conditions. This is a Mapper calss
 * which maps sieve conditions to sieve script.
 * 
 * 
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  Noobh_Sieve
 * @since   0.1
 * @date June 15, 2012
 *
 */
class Noobh_Sieve_Condition
{
    /**
     * Supported operators
     * @var {sting}
     */
    const OPERATOR_IS = ':is';
    const OPERATOR_CONTAINS = ':contains';
    const OPERATOR_MATCHES = ':matches';
    const OPERATOR_DOMAIN = ':domain';
    const OPERATOR_REGEX = ':regex';
    const GROUP_CONDITION_ANY_OF = 'anyof';
    const GROUP_CONDITION_ALL_OF = 'allof';
    const COMPARATOR = ':comparator';

    /**
     * Differet supported comparators
     * By default we use COMPARATOR_OCTET(UTF-8) for all filter comparison
     */
    const COMPARATOR_OCTET = 'i;octet';
    const COMPARATOR_ASCII_CASEMAP = 'i;ascii-casemap';
    
    /**
     * Supported match types in sieve condition,
     * these are list of left match conditions
     * @var {sting}
     */
    const MATCH_ADDRESS = 'address';
    const MATCH_HEADER = 'header';
    const MATCH_SIZE = 'size';
    const MATCH_BODY = 'body';
    
    
    /**
     * Static properties which are used in the application
     * for over riding the existing behavior and inject new
     * customized operations. For dependency injection this properties
     * should be defined in the application level
     * 
     * @todo: Refer example in wiki: 
     */
    
    //Map left match conditions to sieve left matches conditions
    public static $leftMatchMapper = array();
    //Maps left mail componets to left match conditions
    public static $rightMatchMailComponentMapper = array();
    //Maps operators to sieve operators
    public static $operators = array();
    //Maps not operators to sieve operators
    public static $notOperators = array();
    //Comparator used for filter comparison. Default to OCTECT(UTF-8)
    public static $comparator = self::COMPARATOR_OCTET;

    /**
     * Create conditions and return sieve script
     *
     * Sample sieve rule
     *
     * address :contains ["From","Sender","Resent-from","Resent-sender","Return-path"]
     * "test3"
     *
     * @access public
     * @param void
     * @return {string} $sieveIfRule
     */
    public static function create(SimpleXMLElement $xml){
        if(isset($xml->conditions->condition)){
            foreach ($xml->conditions->condition as $condition) {
                $sieveIfRule = isset($sieveIfRule) ? $sieveIfRule . ', ' : '';
                if (isset($condition->leftMatch)) {
                    $match = '';
                    $conditionList = array();
                    foreach ($condition->leftMatch as $leftmatch) {
                        $leftCondition = (string) $leftmatch;
                        //Validating left match condition
                        if(!array_key_exists($leftCondition, self::$leftMatchMapper)){
                        	throw new Exception('Left condition is not in Noobh_Sieve_Condition::$leftMatchMapper list');
                        }
                        // Check for custom supported left match
                        $leftCondition = isset(self::$leftMatchMapper[$leftCondition]) ? self::$leftMatchMapper[$leftCondition] : $leftCondition;
                        $leftCondition = (is_array($leftCondition)) ? implode(" ", $leftCondition) : $leftCondition;
                        if(!in_array($leftCondition, $conditionList)){
                            $conditionList[] = $leftCondition;
                            $match = ($match) ? $match .= ' ' . $leftCondition : $leftCondition;
                        }
                        $rightMatchComponent = implode(",", self::$rightMatchMailComponentMapper[(string) $leftmatch]);
                    }
                    // Creation operator rule
                    if (isset($condition->operator)) {
                     //Adding not condition. Check current operator is in notOperator list then add 'not' in front of leftmatch
                     if(in_array($condition->operator, self::$notOperators)){
                      $match = ' not '. $match;
                     }
                     ($match) ? $sieveIfRule .= $match : '';
                     
                     if(!array_key_exists((string)$condition->operator, self::$operators)){
                     	   throw new Exception('Operator is not in Noobh_Sieve_Condition::$operators list');
                        }
                        $match = '';
                        foreach ($condition->operator as $operator) {
                            $operator = (string) $operator;
                            // Check for custom supported operator
                            $operator = isset(self::$operators[$operator]) ? self::$operators[$operator] : $operator;
                            $operator = (is_array($operator)) ? implode(" ", $operator) : $operator;
                            $match = ($match) ? $match .= ' ' . $operator : $operator;
                        }
                        // Create rule till operator
                        ($match) ? $sieveIfRule .= ' ' . $match . ' ' : '';
                        //Add Comparator - :comparator "i;octet"
                        
                        // if(isset(self::$comparator)){
                        //    $sieveIfRule .= " " . self::COMPARATOR . ' "' . self::$comparator .'" ';
                        // }

                        

                        // Add leftComponents if present
                        $sieveIfRule .= isset($rightMatchComponent) ? '["' . preg_replace('/[*,*]/', '" , "', $rightMatchComponent) . '"] ' : ' ';
                        //Creating rules till right match
                        if (isset($condition->rightMatch)) {
                            $match = '';
                            foreach ($condition->rightMatch as $rightMatch) {
                                $rightMatch = addslashes((string)$rightMatch);
                                $match = ($match) ? $match .= ',' . '"' . $rightMatch .'"' : '"'. $rightMatch .'"';
                            }
                            //Adding right match value for the conditions
                            $sieveIfRule .= (isset($match)? ( ($condition->rightMatch->count() > 1) ? ' ['. $match .']' : ' ' . $match) : '');
                            $sieveIfRule .= PHP_EOL;
                        }else{
                            //@todo: Need to add error code with xml output
                            throw new Exception('Invalid Right Match condition','IST_SIEVE_1004');
                        }
    
                    }else{
                        //@todo: Need to add error code with xml output
                        throw new Exception('Invalid Operator condition','IST_SIEVE_1003');
                    }
                }else{
                    //@todo: Need to add error code with xml output
                    throw new Exception('Invalid Left Match condition','IST_SIEVE_1002');
                }
            }
        }
        //Add group condition if exist
        $sieveIfRule = isset($xml->GroupCondition)? $xml->GroupCondition .'('. $sieveIfRule .')' : $sieveIfRule;
        //Create action rules
        $sieveIfRule .= Noobh_Sieve_Action::create($xml->actions);
        return $sieveIfRule;
    
    }
}