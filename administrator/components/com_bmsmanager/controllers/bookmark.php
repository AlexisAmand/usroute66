<?php
defined('_JEXEC') or die;

class BMSManagerControllerBookmark extends JController
{
	function __construct()
	{
		parent::__construct();
		
		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );
	}
	
	function edit()
	{
		JRequest::setVar( 'view', 'bookmark' );
		JRequest::setVar( 'layout', 'edit' );
		JRequest::setVar( 'hidemainmenu', 1 );
		
		parent::display();
	}
	
	function save()
	{
		
		JRequest::checkToken() or jexit( 'invalid token' );

		$model	= $this->getModel( 'Bookmark' );
		
		if ($model->store( $post )) {
			$msg = JText::_( 'NOTICE_SAVED' );
		} else {
			$msg = $model->getError();
		}
		
		$this->setRedirect( index .'&view=bookmarks', $msg );
	}
	
	function remove()
	{
		JRequest::checkToken() or jexit( 'invalid token' );

		$model	= $this->getModel( 'Bookmark' );
		
		if(!$model->delete()) {
			$msg = JText::_( 'NOTICE_DEL_ERROR' );
		} else {
			$msg = JText::_( 'NOTICE_DELETED' );
		}
		
		$this->setRedirect( index .'&view=bookmarks', $msg );
	}
	
	function cancel() {
		JRequest::checkToken() or jexit( 'invalid token' );
		$msg	= JText::_( 'NOTICE_CANCELLED' );
		$this->setRedirect( index .'&view=bookmarks', $msg );
	}
	
	function makepop()
	{
		$model	= $this->getModel( 'Bookmark' );
		
		if ($model->makePopular( 1 )) {
			$msg = JText::_( 'NOTICE_SAVED' );
		} else {
			$msg = $model->getError();
		}
		
		$this->setRedirect( index .'&view=bookmarks', $msg );
	}
	
	function unpop()
	{
		$model	= $this->getModel( 'Bookmark' );
		
		if ($model->makePopular( 0 )) {
			$msg = JText::_( 'NOTICE_SAVED' );
		} else {
			$msg = $model->getError();
		}
		
		$this->setRedirect( index .'&view=bookmarks', $msg );
	}
	function orderup()
	{
		JRequest::checkToken() or jexit( 'invalid token' );
		
		$model	= $this->getModel( 'Bookmark' );
		$cid	= JRequest::getVar( 'bid', array(), 'post', 'array' );
		JArrayHelper::toInteger( $cid );
		
		if ($model->orderItem( $cid[0], -1 )) {
			$msg = JText::_( 'NOTICE_SAVED' );
		} else {
			$msg = $model->getError();
		}
		
		$this->setRedirect( index .'&view=bookmarks', $msg );
	}
	
	function orderdown()
	{
		JRequest::checkToken() or jexit( 'invalid token' );
		
		$model	= $this->getModel( 'Bookmark' );
		$cid	= JRequest::getVar( 'bid', array(), 'post', 'array' );
		JArrayHelper::toInteger( $cid );
		
		if ($model->orderItem( $cid[0], 1 )) {
			$msg = JText::_( 'NOTICE_SAVED' );
		} else {
			$msg = $model->getError();
		}
		
		$this->setRedirect( index .'&view=bookmarks', $msg );
	}
}
