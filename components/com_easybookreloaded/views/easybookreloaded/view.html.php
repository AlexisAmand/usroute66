<?php
/**
 * @package    Easybook
 * @link http://www.easy-joomla.org
 * @license    GNU/GPL
 *
 * Easybook Extended
 * Based on: Easybook by http://www.easy-joomla.org
 * @license    GNU/GPL
 * Project Page: http://www.kubik-rubik.de/joomla-hilfe/komponente-easybook-reloaded-joomla
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * Easybook View
 *
 * @package    Easybook
 */
class EasybookReloadedViewEasybookReloaded extends JView
{
	/**
	 * Easybook view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		global $mainframe;

		$document	= &JFactory::getDocument();
		$menus		= &JSite::getMenu();
		$params 	= &JComponentHelper::getParams('com_easybookreloaded');
		$task		= JRequest::getVar( 'task' );

		// Set CSS File
		if ($params->get('template_dark') == 0) 
		{
			JHTML::_('stylesheet', 'easybookreloaded.css', 'components/com_easybookreloaded/css/');
		}
		else
		{
			JHTML::_('stylesheet', 'easybookreloadeddark.css', 'components/com_easybookreloaded/css/');
		}
		
		$document->addCustomTag('
		<!--[if IE 6]>
    		<style type="text/css">
    				.easy_align_middle { behavior: url('.JURI::base().'components/com_easybookreloaded/scripts/pngbehavior.htc); }
    				.png { behavior: url('.JURI::base().'components/com_easybookreloaded/scripts/pngbehavior.htc); }
    		</style>
  		<![endif]-->');

		// Get some Data
		$entrys = $this->get('Data');
		$count = $this->get('Total');
		$pagination = $this->get('Pagination');

		// Show RSS Feed
		$link	= '&format=feed&limitstart=';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

		// Prepare ACL checks
		$user = & JFactory::getUser();
		$access = new stdClass();

		$access->canAdd			= $user->authorize('com_easybookreloaded', 'add');
		$access->canPublishOwn	= $user->authorize('com_easybookreloaded', 'publish', 'content', 'own');
		$access->canRemoveOwn	= $user->authorize('com_easybookreloaded', 'remove', 'content', 'own');
		$access->canEditOwn		= $user->authorize('com_easybookreloaded', 'edit', 'content', 'own');
		
		$access->canPublish		= $user->authorize('com_easybookreloaded', 'publish', 'content', 'all');
		$access->canRemove		= $user->authorize('com_easybookreloaded', 'remove', 'content', 'all');
		$access->canEdit		= $user->authorize('com_easybookreloaded', 'edit', 'content', 'all');
		$access->canComment		= $user->authorize('com_easybookreloaded', 'comment', 'content', 'all');

		// Set the document page title
		$menu    = $menus->getActive();
		switch($task)
		{
			case 'add':
				$document->setTitle($heading = $menu->name." - ".JTEXT::_('Sign Guestbook'));
				break;
			case 'edit':
				$document->setTitle($heading = $menu->name." - ".JTEXT::_('Edit Entry'));
				break;
			case 'comment':
				$document->setTitle($heading = $menu->name." - ".JTEXT::_('Edit Comment'));
				break;
		}
		
		$heading = $document->getTitle();
		
		// Assign Data to template
		$this->assignRef('heading',	$heading);
		$this->assignRef('entrys',	$entrys);
		$this->assignRef('count' , $count);
		$this->assignRef('pagination' , $pagination);
		$this->assignRef('params', $params);
		$this->assignRef('access', $access);

		// Add HTML Head Link
		if(method_exists($document, "addHeadLink")) 
		{
			$paginationdata = $pagination->getData();
			if($paginationdata->start->link) 
			{
				$document->addHeadLink($paginationdata->start->link, "first");
			}
			
			if($paginationdata->previous->link) 
			{
				$document->addHeadLink($paginationdata->previous->link, "prev");
			}
			
			if($paginationdata->next->link) 
			{
				$document->addHeadLink($paginationdata->next->link, "next");
			}
			
			if($paginationdata->end->link) 
			{
				$document->addHeadLink($paginationdata->end->link, "last");
			}
		}
		// Display template
		parent::display($tpl);
	}
}
