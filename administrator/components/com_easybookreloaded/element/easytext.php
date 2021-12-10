<?php
/**
 * @package   	Easybook
 * @link 		http://www.easy-joomla.org
 * @license    	GNU/GPL
 *
 * Name:			Easybook Reloaded
 * Based on: 		Easybook by http://www.easy-joomla.org
 * License:    		GNU/GPL
 * Project Page: 	http://www.kubik-rubik.de/joomla-hilfe/komponente-easybook-reloaded-joomla
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JElementEasyText extends JElement {

	function fetchElement($name, $value, &$node, $control_name)
	{
		
		// For the menu - then the global value will be used
		if (JRequest::getCmd('option') == 'com_menus' && $value == $node->attributes('default')) {
			$value = '';			
		}
		
		// Set Class
		$class = $node->attributes('class');
		if (empty($class)) {
			$class = 'text_area';
		}
		
		// Set Size
		$size = $node->attributes('size');
		if (!empty($size)) {
			$size = 'size="'.$node->attributes('size').'"';
		}
		
		$html ='<input type="text" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" class="'.$class.'" '.$size.' />';
		return $html;
	}
}