<?php
defined('_JEXEC') or die;

// Import librairies
jimport( 'joomla.application.component.view' );

class BMSManagerViewDocs extends JView
{
	
	function display($tpl = null)
	{
		// Sub menu
		JSubMenuHelper::addEntry( JText::_( 'Documentation' ), index .'&view=docs' );
		JSubMenuHelper::addEntry( JText::_( 'Bookmarks' ), index .'&view=bookmarks' );
		
		// Toolbar
		JToolBarHelper::title( JText::_( 'Documentation' ), 'info' );
		JToolBarHelper::preferences( 'com_bmsmanager' );
		
		$doc	= & JFactory::getDocument();
		
		// Content styles
		$doc->addStyleDeclaration(
			'.icon-48-info{'.
				'background-image:url(components/com_bmsmanager/assets/icon.docs.png);'.
			'}'.
			'.bmsmanagerc{'.
				'padding:0 20px;'.
			'}'
		);
		
		// HTML examples
		define( 'lDiv', '&lt;div class=&quot;%s&quot;&gt;<br/>' );
		define( 'lTab', '&nbsp;&nbsp;&nbsp;' );
		
		// Template
		$this->about	= $this->get( 'Template' );
		
		parent::display($tpl);
	}
}
