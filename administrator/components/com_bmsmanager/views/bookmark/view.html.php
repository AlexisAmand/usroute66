<?php
defined('_JEXEC') or die;
/**
 * 
 * @package	Bookmark System Manager
 * @author	Aleksandar Bogdanovic
 */

// Import librairies
jimport( 'joomla.application.component.view' );

class BMSManagerViewBookmark extends JView
{
	/**
	 * display method of bookmark view
	 * @return void
	 **/
	function display($tpl = null)
	{
		// Toolbar
		$bookmark	= & $this->get( 'Bookmark' );
		$isNew		= ( $bookmark->id < 1 );
		$text 		= $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title( JText::_( 'Bookmark' ).': <small><small>[ ' . $text.' ]</small></small>', 'bookmark' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		
		$doc	= & JFactory::getDocument();
		$doc->addStyleDeclaration(
			'.icon-48-bookmark{'.
				'background-image:url(components/com_bmsmanager/assets/bookmark.png);'.
			'}'
		);
		
		$this->assignRef( 'bookmark', $bookmark );
		$this->assignRef( 'lists', $this->get( 'Lists' ) );
		
		parent::display($tpl);
	}
}
