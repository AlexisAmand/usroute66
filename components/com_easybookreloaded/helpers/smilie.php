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

// Component Helper
jimport('joomla.application.component.helper');

class EasybookReloadedHelperSmilie 
{
	function getSmilies()
	{
		$smiley[':zzz']   = "sm_sleep.gif";    
		$smiley[';)']     = "sm_wink.gif";     
		$smiley['8)']     = "sm_cool.gif";
		$smiley[':p']     = "sm_razz.gif";     
		$smiley[':roll']  = "sm_rolleyes.gif";
		$smiley[':eek']   = "sm_bigeek.gif";   
		$smiley[':grin']  = "sm_biggrin.gif";
		$smiley[':)']     = "sm_smile.gif";    
		$smiley[':sigh']  = "sm_sigh.gif";
		$smiley[':?']     = "sm_confused.gif"; 
		$smiley[':cry']   = "sm_cry.gif";
		$smiley[':(']     = "sm_mad.gif";      
		$smiley[':x']     = "sm_dead.gif";
		$smiley[':upset'] = "sm_upset.gif";
		return $smiley;
	}
}
?>
