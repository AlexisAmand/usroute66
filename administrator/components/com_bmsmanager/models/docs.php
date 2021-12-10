<?php
defined('_JEXEC') or die;
jimport( 'joomla.application.component.model' );

/**
 * Bookmarks model for BMS component
 * 
 * @package	Bookmark System Manager
 * @author	Aleksandar Bogdanovic
 */
class BMSManagerModelDocs extends JModel
{
	function getTemplate()
	{
		global $mainframe;
		
		$tmpl	= $mainframe->getUserStateFromRequest( 'bmsmanager.docs', 'about', '', 'word' );
		
		switch ( $tmpl )
		{
			case 'bookmarks':
				$template	= 'bookmarks';
				break;
			default:
				$template	= 'bookmarks';
		}
		
		return $template;
	}
}
