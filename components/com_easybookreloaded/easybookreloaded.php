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
define('_EASYBOOK_VERSION', '2.0.6');

// Require the base controller
require_once( JPATH_COMPONENT.DS.'controller.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'content.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'smilie.php' );

$maj_ver = split('\.', PHP_VERSION);

if ($maj_ver[0] < 5) 
{
	require_once(JPATH_COMPONENT.DS.'acl-php4.php');
} 
else 
{
	require_once(JPATH_COMPONENT.DS.'acl.php');
}

// Require specific controller if requested
if ($controller = JRequest::getWord('controller')) 
{
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) 
	{
        require_once $path;
    } 
	else 
	{
        $controller = '';
    }
}

// Create the controller
$classname    = 'EasybookReloadedController'.$controller;
$controller   = new $classname();

$controller->setAccessControl('com_easybookreloaded');

// Perform the Request task
$controller->execute(JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();

?>