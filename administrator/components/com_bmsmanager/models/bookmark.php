<?php
defined('_JEXEC') or die;
jimport( 'joomla.application.component.model' );

/**
 * Bookmark model for Linkr component
 * 
 * @package	Bookmark System Manager
 * @author	Aleksandar Bogdanovic
 */
class BMSManagerModelBookmark extends JModel
{
	function __construct()
	{
		parent::__construct();
		
		$ids	= JRequest::getVar( 'bid', array(0), '', 'array' );
		$this->_setID( (int) $ids[0] );
	}
	
	function _setID( $id ) {
		$this->_id		= $id;
		$this->_data	= null;
	}
	
	function getBookmark()
	{
		if (empty( $this->_bookmark ))
		{
			$q	=	' SELECT * FROM #__bmsmanager_bookmarks '.
					' WHERE id = '. $this->_id;
			$this->_db->setQuery( $q );
			$this->_bookmark	= $this->_db->loadObject();
		}
		if (!$this->_bookmark)
		{
			$this->_bookmark	= new stdClass;
			$this->_bookmark->id	= 0;
			$this->_bookmark->name	= '';
			$this->_bookmark->text	= '';
			$this->_bookmark->size	= 'text';
			$this->_bookmark->htmltext	= '';
			$this->_bookmark->htmlsmall	= '';
			$this->_bookmark->htmllarge	= '';
			$this->_bookmark->htmlbutton	= '';
			$this->_bookmark->htmlcustom	= '';
			$this->_bookmark->ordering	= 0;
			$this->_bookmark->icon	= '';
			$this->_bookmark->popular	= 0;
		}
		
		return $this->_bookmark;
	}
	
	function getLists()
	{
		$b		= $this->getBookmark();
		
		// Lists
		$lists	= array();
		$size	= array();
		
		// Size
		$size[]	= JHTML::_( 'select.option', 'text', JText::_( 'SIZE_TEXT' ) );
		$size[]	= JHTML::_( 'select.option', 'small', JText::_( 'SIZE_SMALL_M' ) );
		$size	= JHTML::_( 'select.genericlist', $size, 'size', '', 'value', 'text', $b->size );
		
		// Return lists
		$lists['size']	= $size;
		return $lists;
	}
	
	function store()
	{
		$table	= & $this->getTable();
		$info	= JRequest::get( 'post' );
		
		// Fix HTML
		$info['htmltext']	= JRequest::getString( 'htmltext', '', 'post', JREQUEST_ALLOWRAW );
		$info['htmlsmall']	= JRequest::getString( 'htmlsmall', '', 'post', JREQUEST_ALLOWRAW );
		$info['htmllarge']	= JRequest::getString( 'htmllarge', '', 'post', JREQUEST_ALLOWRAW );
		$info['htmlbutton']	= JRequest::getString( 'htmlbutton', '', 'post', JREQUEST_ALLOWRAW );
		$info['htmlcustom']	= JRequest::getString( 'htmlcustom', '', 'post', JREQUEST_ALLOWRAW );
		
		// Bind form data to table fields
		if (!$table->bind( $info )) {
	        $this->setError( $table->getError() );
	        return false;
		}
		
		// Make sure the record is a valid one
	    if (!$table->check()) {
	        $this->setError( $table->getError() );
	        return false;
	    }
		
		// Save bookmark
	    if (!$table->store()) {
	        $this->setError( $table->getError() );
	        return false;
	    }
		
		return true;
	}
	
	function delete()
	{
		$ids	= JRequest::getVar( 'bid', array(0), 'request', 'array' );
		$table	= & $this->getTable();
		
		if (count( $ids )) {
			foreach($ids as $id) {
				if (!$table->delete( $id )) {
					$this->setError( $table->getError() );
					return false;
				}
			}						
		}
		
		return true;
	}
	
	function makePopular( $pop = 1 )
	{
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$ids	= JRequest::getVar( 'bid', array(), 'post', 'array' );
		JArrayHelper::toInteger($ids);
		
		if (count( $ids ) < 1) {
			$this->setError( JText::_( 'bad request' ) );
			return false;
		}
		
		// Update bookmark
		$ids	= implode( ',', $ids );
		$query	= 'UPDATE #__bmsmanager_bookmarks' .
				' SET popular = '. (int) $pop .
				' WHERE id IN ( '. $ids .' )';
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		} else {
			return true;
		}
	}
	
	function orderItem( $id, $to )
	{
		$id	= (int) $id;
		if (!$id) {
			$this->setError( JText::_( 'Invalid ID' ) );
			return false;
		}
		
		$table	= & $this->getTable();
		$table->load( $id );
		if (!$table->move( $to )) {
			$this->setError( $table->getError() );
			return false;
		}
		
		return true;
	}
	
	function reorder( $ids, $orders )
	{
		$total	= count( $ids );
		$table	= & $this->getTable();
		
		// update ordering values
		for($i = 0; $i < $total; $i++)
		{
			$table->load( $ids[$i] );
			
			if ($table->ordering != $orders[$i]) {
				$table->ordering	= $orders[$i];
				if (!$table->store()) {
					$this->setError( $table->getError() );
					return false;
				}
			}
		}
		
		// Sort
		$table->reorder();
		
		return true;
	}
}
