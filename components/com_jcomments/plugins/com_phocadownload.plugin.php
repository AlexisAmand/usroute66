<?php
/**
 * JComments plugin for PhocaDownload
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2009 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 **/

class jc_com_phocadownload extends JCommentsPlugin
{
	function getTitles($ids)
	{
		$db = & JCommentsFactory::getDBO();
		$db->setQuery( 'SELECT id, title FROM #__phocadownload_categories WHERE id IN (' . implode(',', $ids) . ')' );
		return $db->loadObjectList('id');
	}

	function getObjectTitle($id)
	{
		$db = & JCommentsFactory::getDBO();
		$db->setQuery( 'SELECT title, id FROM #__phocadownload_categories WHERE id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$db = & JCommentsFactory::getDBO();
		$query = 'SELECT cc.id, CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END as slug'
			. ' FROM #__phocadownload_categories AS cc'
			. ' WHERE cc.id = ' . $id
			;
		$db->setQuery($query);
		$slug = $db->loadResult();

		$_Itemid = JCommentsPlugin::getItemid('com_phocadownload');
		$link = 'index.php?option=com_phocadownload&view=category&id=' . $slug;
		$link .= ($_Itemid > 0) ? ('&Itemid=' . $_Itemid) : '';
		$link = JRoute::_($link);

		return $link;
	}
}
?>