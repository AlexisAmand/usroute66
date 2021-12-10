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
defined('_JEXEC') or die('Restricted access');

class TableBadword extends JTable
{
    /**
     * Primary Key
     *
     * @var int
     */
    var $id = null;
    var $word = null;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function TableBadword( &$db ) 
	{
        parent::__construct('#__easybook_badwords', 'id', $db);
    }
}
?>