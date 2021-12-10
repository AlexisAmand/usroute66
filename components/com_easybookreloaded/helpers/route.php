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

class EasybookReloadedHelperRoute
{
	function getEasybookReloadedRoute($id)
	{
		$Itemids = EasybookReloadedHelperRoute::_findItem();
		$limit = EasybookReloadedHelperRoute::_limitstart($id);
		$link = 'index.php?option=com_easybookreloaded&view=easybookreloaded';
		$link .= '&Itemid='.$Itemids['easybookreloaded'];
		
		if ($limit != 0) 
		{
			$link .= '&limitstart='.$limit;
		}
		
		$link .= '#gbentry_'.$id;
		
		return $link;		
	}
	
	function getEasybookReloadedRouteHashPublish($id)
	{
		//Create the link
		$Itemids = EasybookReloadedHelperRoute::_findItem();
		$link = 'index.php?option=com_easybookreloaded&task=publish_mail';
		$link .= '&Itemid='.$Itemids['easybookreloaded'];
		$link .= '&hash=';
		
		return $link;		
	}
	
	function getEasybookReloadedRouteHashDelete($id)
	{
		$Itemids = EasybookReloadedHelperRoute::_findItem();
		$link = 'index.php?option=com_easybookreloaded&task=remove_mail';
		$link .= '&Itemid='.$Itemids['easybookreloaded'];
		$link .= '&hash=';
		
		return $link;		
	}
	
	function getEasybookReloadedRouteHashComment($id)
	{
		$Itemids = EasybookReloadedHelperRoute::_findItem();
		$link = 'index.php?option=com_easybookreloaded&task=comment_mail';
		$link .= '&Itemid='.$Itemids['easybookreloaded'];
		$link .= '&hash=';
		
		return $link;		
	}

	function getEasybookReloadedRouteHashEdit($id)
	{
		$Itemids = EasybookReloadedHelperRoute::_findItem();
		$link = 'index.php?option=com_easybookreloaded&task=edit_mail';
		$link .= '&Itemid='.$Itemids['easybookreloaded'];
		$link .= '&hash=';
		
		return $link;		
	}
	
	function _findItem()
	{
		$component = &JComponentHelper::getComponent('com_easybookreloaded');

		$menus	= &JApplication::getMenu('site', array());
		$items	= $menus->getItems('componentid', $component->id);
		$match['easybookreloaded'] = null;
		$match['entryForm'] = null;
		
		if ($items) 
		{
			// in most cases there's only one link, so i don't make any break; in this foreach 
			foreach($items as $item) 
			{
				$view = @$item->query['view'];
				if ($view == 'easybookreloaded' && !$match['easybookreloaded']) 
				{
					$match['easybookreloaded'] = $item->id;
				} 
				elseif ($view == 'entry' && !$match['entryForm']) 
				{
					$match['entryForm'] = $item->id;
				}
			}
		}
		
		return $match;
	}
	
	// Limitstart ermitteln - Easybook Reloaded 2.0.2
	function _limitstart($id)
	{
		$bookParams = JComponentHelper::getParams('com_easybookreloaded');
		$bookEntrieperPage = $bookParams->get('entries_perpage', 5);
		
		$query = 'SELECT * FROM #__easybook WHERE published = 1 ORDER BY `id` DESC';
		$db	=& JFactory::getDBO();
		$db->setQuery($query);
		$db->query();
		$result = $db->loadResultArray();
		
		$key = array_search($id, $result);
		$limit = $bookEntrieperPage * intval($key/$bookEntrieperPage);
		
		return $limit;
	}
}
?>
