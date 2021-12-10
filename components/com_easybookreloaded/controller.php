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
 * @package    Easybook
 */
class EasybookReloadedController extends JController
{
	/**
     * Method to display the view
     *
     * @access    public
     */
    function display()
    {
        parent::display();
    }

	// Funktionen für E-Mail Versand
	function publish_mail()
	{
		$hashrequest = JRequest::getVar('hash', '', 'default', 'base64');
		$error = $this->performMail($hashrequest);

		if ($error == false)
		{
			$model = $this->getModel('entry');
			switch($model->publish())
			{
				case -1:
					$msg = JText::_( 'Error: Could not change publish status' );
					$type = 'error';
					break;
				case 0:
					$msg = JText::_( 'Entry unpublished' );
					$type = 'message';
					break;
				case 1:
					$msg = JText::_( 'Entry published' );
					$type = 'message';
					break;
			}
			$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded', false ), $msg, $type );
		}
		else
		{
			$msg = JText::_( 'Error: Could not change publish status' );
			$type = 'error';
			$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded', false ), $msg, $type );
		}
	}

	function remove_mail()
	{
		$hashrequest = JRequest::getVar('hash', '', 'default', 'base64');
		$error = $this->performMail($hashrequest);

		if ($error == false)
		{
			$model = $this->getModel('entry');
			if (!$model->delete())
			{
				$msg = JText::_( 'Error: Entry could not be deleted' );
				$type = 'error';
			}
			else
			{
				$msg = JText::_( 'Entry Deleted' );
				$type = 'message';
			}
			$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded', false ), $msg, $type );
		}
		else
		{
			$msg = JText::_( 'Error: Entry could not be deleted' );
			$type = 'error';
			$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded', false ), $msg, $type );
		}
	}

	function comment_mail()
	{
		$hashrequest = JRequest::getVar('hash', '', 'default', 'base64');
		$error = $this->performMail($hashrequest);

		if ($error == false)
		{
			JRequest::setVar( 'view', 'entry' );
			JRequest::setVar( 'layout', 'commentform_mail' );
			JRequest::setVar( 'hidemainmenu', 1 );
			parent::display();
		}
		else
		{
			$msg = JText::_( 'Error: Could not save comment' );
			$type = 'error';
			$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded', false ), $msg, $type );
		}
	}

	function savecomment_mail()
	{
		$hashrequest = JRequest::getVar('hash', '', 'default', 'base64');
		$error = $this->performMail($hashrequest);

		if ($error == false)
		{
			$model = $this->getModel('entry');
			if (!$model->savecomment())
			{
				  $msg = JText::_( 'Error: Could not save comment' );
				  $type = 'error';
			}
			else
			{
				  $msg = JText::_( 'Comment saved' );
				  $type = 'message';
			}
			$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded', false ), $msg, $type );
		}
		else
		{
			$msg = JText::_( 'Error: Could not save comment' );
			$type = 'error';
			$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded', false ), $msg, $type );
		}
	}

	function edit_mail()
	{
		$hashrequest = JRequest::getVar('hash', '', 'default', 'base64');
		$error = $this->performMail($hashrequest);

		if ($error == false)
		{
			JRequest::setVar( 'view', 'entry' );
			JRequest::setVar( 'layout', 'form_mail' );
			parent::display();
		}
		else
		{
			$msg = JText::_( 'Error: Please validate your inputs' );
			$type = 'error';
			$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded', false ), $msg, $type );
		}
	}

	function save_mail()
	{
		$params = &JComponentHelper::getParams( 'com_easybookreloaded' );
		$hashrequest = JRequest::getVar('hash', '', 'default', 'base64');
		$error = $this->performMail($hashrequest);

		if ($error == false)
		{
			$session = JFactory::getSession();
			$time = $session->get('time', null, 'easybookreloaded');
			$session->set('time', $time - $params->get('type_time_sec'), 'easybookreloaded');

			$model = $this->getModel( 'entry' );
			if ($model->store())
			{
				if ($params->get('default_published', true))
				{
					$msg = JText::_( 'Entry Saved' );
					$type = 'message';
				}
				else
				{
					$msg = JText::_( 'Entry saved but has to be approved');
					$type = 'notice';
				}
				$link = JRoute::_( 'index.php?option=com_easybookreloaded&view=easybookreloaded', false );
			}
			else
			{
				$msg = JText::_( 'Error: Could not save comment' );
				$link = JRoute::_( 'index.php?option=com_easybookreloaded&view=easybookreloaded', false );
				$type = 'notice';
			}
			$this->setRedirect( $link, $msg, $type );
		}
		else
		{
			$msg = JText::_( 'Error: Could not save comment' );
			$type = 'error';
			$this->setRedirect( JRoute::_( 'index.php?option=com_easybookreloaded', false ), $msg, $type );
		}
	}
	
	// Funktion, die den übermittelten Hash prüft
	function performMail($hashrequest)
	{
		$model = $this->getModel( 'entry' );

		$params = &JComponentHelper::getParams( 'com_easybookreloaded' );
		$secretword = $params->get('secret_word');
		$error = false;

		jimport('joomla.utilities.simplecrypt');
		$crypt = new JSimpleCrypt();

		if ($hashrequest == '')
		{
			$error = true;
			return $error;
		}
		$hash = base64_decode($hashrequest);
		$hash = $crypt->decrypt($hash);
		$hash = unserialize($hash);

		if (isset($hash['id']))
		{
			$gbrow = $model->getRow($hash['id']);
		}
		else
		{
			$error = true;
			return $error;
		}
		
		$app = JFactory::getApplication();
		$offset = $app->getCfg('offset');
 
		$date_entry = JFactory::getDate($gbrow->get('gbdate'));
		$date_entry->setOffset($offset);
		
		$date_now = JFactory::getDate();
		$date_now->setOffset($offset);
		
		$valid_time_emailnot = $params->get('valid_time_emailnot') * 60 * 60 * 24;
		
		if ($date_entry->toUnix() + $valid_time_emailnot <= $date_now->toUnix())
		{
			$error = true;
			return $error;
		}

		if (md5($gbrow->get('gbmail')) != $hash['gbmail'])
		{
			$error = true;
			return $error;
		}

		if($gbrow->get('gbname') != $hash['username'])
		{
			$error = true;
			return $error;
		}

		if ($hash['custom_secret'] != $secretword)
		{
			$error = true;
			return $error;
		}
		
		// Hash nachbauen und vergleichen
		$hash = array();
		$hash['id'] = (int)$gbrow->get('id');
		$hash['gbmail'] = md5($gbrow->get('gbmail'));
		$hash['custom_secret'] = $secretword;
		$hash['username'] = $gbrow->get('gbname');
		$hash = serialize($hash);
		$hash = $crypt->encrypt($hash);
		$hash = base64_encode($hash);

		if ($hash != $hashrequest)
		{
			$error = true;
			return $error;
		}
		
	return $error;
	}
}
?>