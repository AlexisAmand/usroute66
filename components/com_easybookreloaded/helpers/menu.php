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

// Component Helper
jimport('joomla.application.component.helper');

class EasybookReloadedHelperMenu
{
	function getName()
	{
		$component = &JComponentHelper::getComponent('com_easybookreloaded');

		$menus	= &JApplication::getMenu('site', array());
		$items	= $menus->getItems('componentid', $component->id);
		$match = null;

		foreach($items as $item) {
			if ((@$item->query['view'] == 'easybookreloaded')) {
				$match = $item->name;
				break;
			}
		}
		return $match;		
	}
}
?>
