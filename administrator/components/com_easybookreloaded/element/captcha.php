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


class JElementCaptcha extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Captcha';

	function fetchElement($name, $value, &$node, $control_name)
	{
		jimport( 'joomla.filesystem.file' );
		$path = JPATH_SITE.DS.'components'.DS.'com_easycaptcha'.DS.'class.easycaptcha.php';

		$options = array ();
		
		if (JRequest::getCmd('option') == 'com_menus') {
			$options[] = JHTML::_('select.option', '', JText::_('USE GLOBAL'));
		} else {
			if(empty($value)) {
				$value = 0;
			}
		}
		
		foreach ($node->children() as $option) {
			$val	= $option->attributes('value');
			$text	= $option->data();
			$options[] = JHTML::_('select.option', $val, JText::_($text));
		}
		
		if(JFile::exists($path)) {
			return JHTML::_('select.genericlist', $options, ''.$control_name.'['.$name.']', '', 'value', 'text', $value, $control_name.$name );
		} else {
			$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
			return JTEXT::_('Please install EasyCaptcha').'<br /><input type="hidden" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="0" '.$class.' />';
		}
	}
}
?>