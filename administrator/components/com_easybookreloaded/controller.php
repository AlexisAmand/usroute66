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

jimport('joomla.application.component.controller');

/**
 * Easybook Component Controller
 *
 * @package    Easybook
 */
class EasybookReloadedController extends JController
{
    /**
     * Method to display the view
     *
     * @access    public
     */
    function display()
    {
        parent::display();
    }
    
    function about()
    {
    	JRequest::setVar( 'view', 'about' );
	    JRequest::setVar( 'layout', 'default'  );
	    
	    parent::display();
    }
	
	function config()
    {
    	JRequest::setVar( 'view', 'config' );
	    JRequest::setVar( 'layout', 'default'  );
	    
	    parent::display();
    }
}
?>