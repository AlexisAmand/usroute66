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

$acl_hlp_inst = new acl_helper();

$acl_hlp_inst->ebconfig = &JComponentHelper::getParams( 'com_easybookreloaded' );
$acl_hlp_inst->acl_hack = true;
$acl_hlp_inst->acl = &JFactory::getACL();

$acl_hlp_inst->setACLs();

class acl_helper 
{
	var $ebconfig;
	var $acl;
	var $acl_hack;

	function getChilds($gid) 
	{
		static $hack_cache;
		static $cache;
		
		if (!isset($cache)) 
		{
			$tmp = $this->acl->get_group_children("30", "ARO", "RECURSE");
			$cache = array("30" => $tmp);
			$hack_cache = array_merge(array("30"), $tmp);
		}

		if (!array_key_exists($gid, $cache)) 
		{
			$tmp = $this->acl->get_group_children($gid, "ARO", "RECURSE");
			
			if($this->acl_hack && in_array(21, $tmp)) 
			{
				$tmp = array_merge($tmp, $hack_cache);
			}
			$cache[$gid] = $tmp;
		}
		
		return $cache[$gid];
	}

	function getGroupName($gid) 
	{
		static $cache = array();
		
		if(!array_key_exists($gid, $cache)) 
		{
			$cache[$gid] = $this->acl->get_group_name($gid);
		}
		
		return $cache[$gid];
	}

	function setACLs()
	{
		$addARO = $this->ebconfig->get('add_acl', 18);
		$editOwnARO = $this->ebconfig->get('owner_acl', 20);
		$editAllARO = $this->ebconfig->get('admin_acl', 20);

		//when access is set to everybody...
		if ($addARO == 0) 
		{
			$this->acl->addACL('com_easybookreloaded', 'add', 'users', null);
			$addARO = 17;
		}
		
		if ($editOwnARO == 0) 
		{
			/*$this->acl->addACL('com_easybookreloaded', 'publish', 'users', null, 'content', 'own');
			$this->acl->addACL('com_easybookreloaded', 'remove', 'users', null, 'content', 'own');
			$this->acl->addACL('com_easybookreloaded', 'edit', 'users', null, 'content', 'own');*/
			$editOwnARO = 17;
		}
		
		if ($editAllARO == 0) 
		{
			$this->acl->addACL('com_easybookreloaded', 'publish', 'users', null, 'content', 'all');
			$this->acl->addACL('com_easybookreloaded', 'remove', 'users', null, 'content', 'all');
			$this->acl->addACL('com_easybookreloaded', 'edit', 'users', null, 'content', 'all');
			$this->acl->addACL('com_easybookreloaded', 'comment', 'users', null, 'content', 'all');
			$this->acl->addACL('com_easybookreloaded', 'savecomment', 'users', null, 'content', 'all');
			$editAllARO = 17;
		}
		
		//set ACLs for group and child groups
		$childs = $this->getChilds(17);
		$childs = array_merge(array(17), $childs);
		$this->acl->addACL('com_easybookreloaded', 'display', 'users', null);
		$this->acl->addACL('com_easybookreloaded', 'save', 'users', null);
		
		if ($send_mail = $this->ebconfig->get('send_mail')) 
		{
			$this->acl->addACL('com_easybookreloaded', 'publish_mail', 'users', null);
			$this->acl->addACL('com_easybookreloaded', 'remove_mail', 'users', null);
			$this->acl->addACL('com_easybookreloaded', 'comment_mail', 'users', null);
			$this->acl->addACL('com_easybookreloaded', 'savecomment_mail', 'users', null);
			$this->acl->addACL('com_easybookreloaded', 'edit_mail', 'users', null);
			$this->acl->addACL('com_easybookreloaded', 'save_mail', 'users', null);
		}
		
		foreach($childs as $i) 
		{
			$this->acl->addACL('com_easybookreloaded', 'display', 'users', $this->getGroupName($i));
			$this->acl->addACL('com_easybookreloaded', 'save', 'users', $this->getGroupName($i));
			if ($send_mail = $this->ebconfig->get('send_mail')) 
			{
				$this->acl->addACL('com_easybookreloaded', 'publish_mail', 'users', $this->getGroupName($i));
				$this->acl->addACL('com_easybookreloaded', 'remove_mail', 'users', $this->getGroupName($i));
				$this->acl->addACL('com_easybookreloaded', 'comment_mail', 'users', $this->getGroupName($i));
				$this->acl->addACL('com_easybookreloaded', 'savecomment_mail', 'users', $this->getGroupName($i));
				$this->acl->addACL('com_easybookreloaded', 'edit_mail', 'users', $this->getGroupName($i));
				$this->acl->addACL('com_easybookreloaded', 'save_mail', 'users', $this->getGroupName($i));
			}
		}

		$childs = $this->getChilds($addARO);
		$childs = array_merge(array($addARO), $childs);
		
		foreach($childs as $i) 
		{
			$this->acl->addACL('com_easybookreloaded', 'add', 'users', $this->getGroupName($i));
		}
		
		$childs = $this->getChilds($editOwnARO);
		$childs = array_merge(array($editOwnARO), $childs);
		
		foreach($childs as $i) 
		{
			/*$this->acl->addACL('com_easybookreloaded', 'publish', 'users', $this->getGroupName($i), 'content', 'own');
			$this->acl->addACL('com_easybookreloaded', 'remove', 'users', $this->getGroupName($i), 'content', 'own');
			$this->acl->addACL('com_easybookreloaded', 'edit', 'users', $this->getGroupName($i), 'content', 'own');*/
		}
		
		$childs = $this->getChilds($editAllARO);
		$childs = array_merge(array($editAllARO), $childs);
		
		foreach($childs as $i) 
		{
			$this->acl->addACL('com_easybookreloaded', 'publish', 'users', $this->getGroupName($i), 'content', 'all');
			$this->acl->addACL('com_easybookreloaded', 'remove', 'users', $this->getGroupName($i), 'content', 'all');
			$this->acl->addACL('com_easybookreloaded', 'edit', 'users', $this->getGroupName($i), 'content', 'all');
			$this->acl->addACL('com_easybookreloaded', 'comment', 'users', $this->getGroupName($i), 'content', 'all');
			$this->acl->addACL('com_easybookreloaded', 'savecomment', 'users', $this->getGroupName($i), 'content', 'all');
		}
	}
}

?>