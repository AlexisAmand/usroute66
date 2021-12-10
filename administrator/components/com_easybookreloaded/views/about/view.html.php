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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * Easybook View
 *
 * @package    Easybook
 */
class EasybookReloadedViewAbout extends JView
{
	/**
	 * Easybook view display method
	 * @return void
	 **/
	function display($tpl = null)
    {        
        JToolBarHelper::title( JText::_( 'Easybookreloaded' ), 'easybookreloaded' );
		JHTML::_('stylesheet', 'easybookreloaded.css', 'administrator/components/com_easybookreloaded/css/');
		
        parent::display($tpl);
    }
}
?>