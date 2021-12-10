<?php
defined('_JEXEC') or die;

// Import librairies
jimport( 'joomla.application.component.view' );

class BMSManagerViewBookmarks extends JView
{
	function display($tpl = null)
	{
		// Sub menu
		JSubMenuHelper::addEntry( JText::_( 'Bookmarks' ), index .'&view=bookmarks' );
		
		// Toolbar
		JToolBarHelper::title( JText::_( 'Bookmarks' ), 'bookmarks' );
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		
		$doc	= & JFactory::getDocument();
		$doc->addStyleDeclaration(
			'.icon-48-bookmarks{'.
				'background-image:url(components/com_bmsmanager/assets/icon.bms.png);'.
			'}'
		);
		
		$this->assignRef('bookmarks',$this->get('Bookmarks'));
		$this->assignRef('page',$this->get('Pagination'));
		$this->assignRef('order',$this->get('Order'));
		
		parent::display($tpl);
	}
}
