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


class JElementACL extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'ACL';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$acl =& JFactory::getACL();
		
		if (JRequest::getCmd('option') == 'com_menus') 
		{
			$global = JHTML::_('select.option', '', JText::_('USE GLOBAL'));
		}
		
		$gtree = $acl->get_group_children_tree( null, 'USERS', false );

		$temp = new stdClass();
		$temp->value = '0';
		$temp->text = 'Everybody';
		$temp->disabled = false;
		$gtree = array_merge(array($temp), $gtree);
		$gtree = array_merge(array($global), $gtree);

		return JHTML::_('select.genericlist',   $gtree, $control_name.'['.$name.']', 'id="'.$control_name.$name.'"', 'value', 'text', $value );
	}
}
?>