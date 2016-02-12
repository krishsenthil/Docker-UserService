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
 * View helper for creating dynamic settings menu items
 *
 * This view helper is having dependency with the following
 * global components
 *
 *  1. Noobh/Documents/assets/fonts
 *  2. Noobh/Documents/css/font-awesome.css
 *
 *
 * @author Vijay <vbose@Collash.com>
 * @copyright Collash Inc
 * @package  View
 * @subpackage Helper
 * @since   0.1
 * @date Oct 24, 2012
 */

class Noobh_Helper_View_Settings{
 /**
  * Set attributes for a link
  * @var {string}
  */
 const Noobh_SETTINGS_ATTRIBUTES = 'Noobh_SETTINGS_ATTRIBUTES';
 /**
  * Create html with given data in menu format
  *
  * $menuList contain all the menu items, Menu items can be
  * single key value pair or key with multiple values. This
  * view helper support the following format
  *
  * Example menu
  * ============
  *
  *     View/Edit
  *     -------------------
  *     Get Registration
  *     -------------------
  *     Add Class
  *     -------------------
  *     Add your sets
  *
  *             Key note 101
  *         ALAC New Employee
  *             EMIEA Emloyee
  *
  *
  * $menuList array format1:
  *
  * ['View/Edit'] => '/edit/track/id/21',
  * ['Get Registration'] => '/registration/id/21',
  * ['Add Class'] => '/class/add/',
  * ['Add your sets'] => ['Key note 101'] => '/keynote/101',
  *                      ['ALAC New Employee'] => '/employee/new',
  *                      ['EMIEA Emloyee'] => '/emia/employee'
  *
  *
  * $menuList array format2:
  *
  * If we need to user html attributes for an href use the following format
  *
  * ['Get Registration'] => '/registration/id/21',
  * ['View/Edit'] =>['Noobh_SETTINGS_ATTRIBUTES'] => ['href'] => '/edit/track/id/21',
  *                                                   ['onclick'] => 'validate();',
  *                                                   ['css'] => 'appcss globalcss',
  * ['Add Class'] => '/class/add/',
  * ['Add your sets'] => ['Key note 101'] => '/keynote/101',
  *                      ['ALAC New Employee'] => '/employee/new',
  *                      ['EMIEA Emloyee'] => '/emia/employee'
  *
  * For adding attributes to a link we, need to set keyword 'Noobh_SETTINGS_ATTRIBUTES'
  *
  *
  * Restricting support for only one level depth in the menu
  *
  * @param {array} $menuList, list all menu items
  * @param {string} $listMenuId, unique id for menu list
  * @return {string} $html
  */
 public static function display(array $listMenu,$listMenuId = NULL){
  $html = '';
  if(count($listMenu) > 0){
		$listMenuId = isset($listMenuId)?$listMenuId : uniqid('Noobhfw_gear_'.rand('999','9999'));
   	//Span for gear
   	$html = '<div class="settings-container"><span role="button" aria-label="Action Menu" aria-haspopup="true" aria-flowto="settings-first-element-'. $listMenuId .'" tabindex="0" id="icon-cog-'. $listMenuId .'" onclick="settings.open(\''.$listMenuId.'\');" class="icon-cog settings-toggle"></span>'.PHP_EOL;
		// $html = '<span aria-flowto="settings-first-element-'. $listMenuId .'" role="menu-button" aria-label="Action Menu" aria-haspopup="true" id="icon-cog-'. $listMenuId .'" onclick="settings.open(\''.$listMenuId.'\');" class="icon-cog settings-toggle">'.PHP_EOL;
   	//Start listing items
   	$html .= '<ul role="menu" id="settings-'.$listMenuId.'" class="settings">'.PHP_EOL;
   	$html .= self::_createList($listMenu);
   	$html .= '</ul></div>'. PHP_EOL;
   	// $html .= '</span>' .PHP_EOL;
  }
  return $html;
 }

 /**
  * Create a single menu item
  * @param {string} $key
  * @param {string} $value
  * @param {boolean} $isGroupMenu, is group menu or not
  * @return {string} html li element
  */
 private static function _createList($list, $isGroupMenu = false){
  $html = '';
  foreach($list as $key => $value){
   //Check is a group menu and attributes list is present
   if(is_array($value)){
    $isGroupMenuHeading = true;
   }else{
    $isGroupMenuHeading = false;
   }
   if(isset($value[self::Noobh_SETTINGS_ATTRIBUTES])){
    $isAttributeList = true;
   }else{
    $isAttributeList = false;
   }
   //Print Group menu heading
   if($isGroupMenuHeading && !$isAttributeList){
    //Group menu heading
    $html .= '<li class="sets-label">'.$key.'</li>'.PHP_EOL;
    //Recursive printing call
    $html .= self::_createList($value,true);
   }elseif($isGroupMenu){
    if($isAttributeList){
     //Group or set menu items
     $value['class'] = 'set';
     $value['title'] = isset($value['title'])? $value['title'] : $key;
     $value['href'] = $value;
    }
    $html .='<li class="sets"><a role="button" tabindex="0" aria-label="'.$key.'" '. self::_createAttributes($value).'>'.$key.'</a></li>' .PHP_EOL;
		// $html .='<li class="sets"><a role="button" aria-label="'.$key.'" '. self::_createAttributes($value).'>'.$key.'</a></li>' .PHP_EOL;
   }else{
    //Normal menu items which are not part of set or group menu
    $html .='<li><a role="button" tabindex="0" aria-label="'.$key.'" '. self::_createAttributes($value).'>'.$key.'</a></li>' .PHP_EOL;
		// $html .='<li><a role="button" aria-label="'.$key.'" '. self::_createAttributes($value).'>'.$key.'</a></li>' .PHP_EOL;
   }
  }
  return $html;
 }

 /**
  * Create anchor attributes for menu items
  * @access private
  * @param {mixed} $elementValue
  * @return {string}
  */
 private static function _createAttributes($elmentValue){
  $attributeHtml = '';
  if(is_array($elmentValue)){
   if(isset($elmentValue[self::Noobh_SETTINGS_ATTRIBUTES])){
    $element = $elmentValue[self::Noobh_SETTINGS_ATTRIBUTES];
    foreach($element as $attrKey => $attrValue){
     $attributeHtml .= ' ' .$attrKey.'="'. $attrValue.'" ';
    }
   }
  }else{
   $elmentValue = ($elmentValue)? $elmentValue : 'javascript:void(0)';
   $attributeHtml .=' href="'.$elmentValue.'" ';
  }
  return $attributeHtml;
 }
}