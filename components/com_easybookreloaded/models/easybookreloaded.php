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

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

/**
 * Easybook Model
 *
 * @package    Easybook
 */
class EasybookReloadedModelEasybookReloaded extends JModel
{
	/**
	 * Easybook entry array
	 *
	 * @var array
	 */
	var $_data;
	var $_total;
	var $_pagination;
	
	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$ebconfig = &JComponentHelper::getParams( 'com_easybookreloaded' );
		
		//preventing sql injections
		$start = ( JRequest::getVar( 'limitstart', 0, '', 'int' ) );
		$order = $this->_db->getEscaped($ebconfig->get('entries_order', "DESC"));
		$limit = intval($ebconfig->get('entries_perpage', 5));
		
		//loading acl stuff
		$user = & JFactory::getUser();
		$canPublish	= $user->authorize('com_easybookreloaded', 'publish', 'content', 'all');
		
			//bulding querys
			if ($canPublish) 
			{
				$query = "SELECT * FROM #__easybook"
				. "\nORDER BY gbdate " . $order
				. "\nLIMIT $start, " .$limit;
			} 
			else 
			{
				$query = "SELECT * FROM #__easybook"
				. "\nWHERE published = 1"
				. "\nORDER BY gbdate " . $order
				. "\nLIMIT $start, " .$limit;
			}
			
		return $query;
	}
	
	function _buildCountQuery()
	{
		//preparing ACL stuff
		$user = & JFactory::getUser();
		$canPublish	= $user->authorize('com_easybookreloaded', 'publish', 'content', 'all');

			//building query
			if ($canPublish)
			{
				$query = "SELECT * FROM #__easybook";
			} 
			else 
			{
				$query = "SELECT * FROM #__easybook"
				. " WHERE published = 1";
			}
			
		return $query;
	}
	
	/**
	 * Retrieves the guestbook entrys
	 * @return array Array of objects containing the data from the database
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data )) 
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query );
		}
		
		return $this->_data;
	}

	function getPagination()
	{
		global $mainframe;
		$ebconfig = &JComponentHelper::getParams( 'com_easybookreloaded' );
		
		if (empty($this->_pagination)) 
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), JRequest::getVar( 'limitstart', 0 ), $ebconfig->get('entries_perpage', 5) );
		}
		
		return $this->_pagination;
	}
	/**
	 * Retrieves the count of guestbook entrys
	 * @return array Array of objects containing the data from the database
	 */
	function getTotal()
	{
		if (empty($this->_total)) 
		{
			$query = $this->_buildCountQuery();
			$this->_total = $this->_getListCount($query);
		}
		
		return $this->_total;
	}
}
?>