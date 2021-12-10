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

jimport('joomla.application.component.controller');

/**
 * Easybook Component Controller
 *
 * @package    Easybook Reloaded
 */
class EasybookReloadedControllerBadwords extends JController
{
    function __construct()
	{
    	parent::__construct();

    	// Register Extra tasks
    	$this->registerTask( 'add'  ,     'edit' );
	}

	function edit()
	{
	    JRequest::setVar( 'view', 'badword' );
	    JRequest::setVar( 'layout', 'form'  );
	    JRequest::setVar('hidemainmenu', 1);
	
	    parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$model = $this->getModel( 'badword' );
	
		if ($model->store()) {
        	$msg = JText::_( 'Badword Saved' );
  			$type = 'message';
	    } else {
	        $msg = JText::_( 'Badword Saving Entry' );
	        $type = 'error';
	    }
			
		$this->setRedirect( 'index.php?option=com_easybookreloaded&controller=badwords', $msg, $type );
	}

	/**
	 * remove record
	 * @return void
	 */
	function remove()
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		//Load model and delete entry - redirect afterwards
		$model = $this->getModel( 'badword' );
		if (!$model->delete()) {
			$msg = JText::_( 'Error: Badword could not be deleted' );
			$type = 'error';
		} else {
			$msg = JText::_( 'Badword(s) Deleted' );
			$type = 'message';
		}
		$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded&controller=badwords', false ), $msg, $type );
	}
	
	
	function cancel()
	{
	    $msg = JText::_( 'Operation Cancelled' );
	    $this->setRedirect( 'index.php?option=com_easybookreloaded&controller=badwords', $msg, 'notice' );
	}

    /**
     * Method to display the view
     *
     * @access    public
     */
    function display()
    {
        JRequest::setVar( 'view', 'badwords' );
        parent::display();
    }

}
?>