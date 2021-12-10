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
defined( '_JEXEC' ) or die( 'Restricted access' );

function EasybookReloadedBuildRoute(&$query)
{
	$segments = array();
	
	if (isset($query['controller'])) 
	{
		$segments[] = $query['controller'];
		unset($query['controller']);
	}
	
	if (isset($query['task'])) 
	{
		$segments[] = $query['task'];
		unset($query['task']);
	}
	
	if (isset($query['cid'])) 
	{
		$segments[] = $query['cid'];
		unset($query['cid']);
	}
	
	if (isset($query['view'])) 
	{
		if(!isset($query['Itemid'])) 
		{
			$segments[] = $query['view'];
		} 
		unset($query['view']);
	}
	return $segments;
}

function EasybookReloadedParseRoute($segments)
{
	$vars = array();

	//Handle View and Identifier
	if ($segments[0] == 'entry') 
	{
		switch($segments[1]) 
		{
			case 'add': 
			{
				$vars['controller'] = 'entry';
				$vars['task'] = 'add';
			} 
			break;
			
			case 'remove': 
			{
				$vars['controller'] = 'entry';
				$vars['task'] = 'remove';
				$vars['cid'] = $segments[2];
			} 
			break;
			
			case 'publish': 
			{
				$vars['controller'] = 'entry';
				$vars['task'] = 'publish';
				$vars['cid'] = $segments[2];
			} 
			break;
			
			case 'unpublish': 
			{
				$vars['controller'] = 'entry';
				$vars['task'] = 'unpublish';
				$vars['cid'] = $segments[2];
			} 
			break;
			
			case 'edit': 
			{
				$vars['controller'] = 'entry';
				$vars['task'] = 'edit';
				$vars['cid'] = $segments[2];
			} 
			break;
			
			case 'comment': 
			{
				$vars['controller'] = 'entry';
				$vars['task'] = 'comment';
				$vars['cid'] = $segments[2];
			} 
			break;			
		}
		
		return $vars;
	}
}