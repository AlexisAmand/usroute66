<?php
defined('_JEXEC') or die;

/**
 * BMSManager controller
 * 
 * @package	Bookmark System Manager
 * @author	Aleksandar Bogdanovic
 */

jimport( 'joomla.application.component.controller' );

/**
 * BMSManager Controller
 *
 * @package	Bookmark System Manager
 */

class BMSManagerController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
		// Make the documentation the homepage
		if (!JRequest::getVar( 'view' )) {
			JRequest::setVar( 'view', 'bookmarks' );
		}
		
		parent::display();
	}
}
