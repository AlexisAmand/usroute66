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

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );

class EasybookReloadedViewEntry extends JView
{
	/**
	 * Hellos view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		global $mainframe, $Itemid;
		$document	= &JFactory::getDocument();
		$menus		= &JSite::getMenu();
		$params 	= &JComponentHelper::getParams( 'com_easybookreloaded' );
		$task		= JRequest::getVar( 'task' );
		$session 	= JFactory::getSession();
		$user		= &JFactory::getUser();
		
		// Set CSS File
		JHTML::_('stylesheet', 'easybookreloaded.css', 'components/com_easybookreloaded/css/');
		$document->addCustomTag('
		<!--[if IE 6]>
    		<style type="text/css">
    				.easy_align_middle { behavior: url('.JURI::base().'components/com_easybookreloaded/scripts/pngbehavior.htc); }
    				.png { behavior: url('.JURI::base().'components/com_easybookreloaded/scripts/pngbehavior.htc); }
    		</style>
  		<![endif]-->');
  		
		// Get data from the model
		$entry = &$this->get('Data');
		
		// Kubik-Rubik.de - Easybook Extended 2.0
		// Get EasyCalcCheck
		$easycalccheck = &$this->get('EasyCalcCheck');
		
		// Set IP
		$entry->ip 	= getenv('REMOTE_ADDR');
		
		// Set the document page title
		switch($task)
		{
			case 'add':
				$heading = $document->getTitle()." - ".JTEXT::_('Sign Guestbook');
				break;
			case 'edit' OR 'edit_mail':
				$heading = $document->getTitle()." - ".JTEXT::_('Edit Entry');
				break;
			case 'comment' OR 'comment_mail':
				$heading = $document->getTitle()." - ".JTEXT::_('Edit Comment');
				break;
		}
		
		$this->assignRef('heading',	$heading);
		$this->assignRef('entry', $entry);
		$this->assignRef('params', $params);
		$this->assignRef('session', $session);
		$this->assignRef('user', $user);
		parent::display($tpl);
	}
} ?>