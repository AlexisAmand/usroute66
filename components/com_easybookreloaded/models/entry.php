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
 * Easybook Reloaded Model
 *
 * @package    Easybook Reloaded
 */
class EasybookReloadedModelEntry extends JModel
{
	var $_data = null;
	var $_id = null;
	var $_badwords = null;			
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access    public
	 * @return    void
	 */
	function __construct()
	{
    	parent::__construct();
		
		// Id aus Hash generieren, wenn übermittelt
		if ($hashrequest = JRequest::getVar('hash', '', 'default', 'base64')) 
		{
			jimport('joomla.utilities.simplecrypt');
			$crypt = new JSimpleCrypt();
			$hash = base64_decode($hashrequest);
			$hash = $crypt->decrypt($hash);
			$hash = unserialize($hash);
			$id = $hash['id'];
		} 
		else 
		{
			$id = JRequest::getVar('cid',  0, '', 'int');
		}
		
    	$this->setId($id);
	}
	
	/**
 	* Method to set the entry identifier
 	*
 	* @access    public
 	* @param    int Entry identifier
 	* @return    void
 	*/
	function setId($id)
	{
    	// Set id and wipe data
    	$this->_id    	= $id;
    	$this->_data  	= null;
	}
	
	/**
 	* Method to get a entry
 	* @return object with data
 	*/
	function getData()
	{
    	global $mainframe;
    	$user = &JFactory::getUser();
    	
    	if (JRequest::getVar('retry') == 'true') 
		{
    		$this->_data = $this->getTable();
    		$this->_data->bind($mainframe->getUserState('eb_validation_data'));
    	}
    	
    	// Load the data
    	if (empty( $this->_data )) 
		{
			$query = ' SELECT * FROM #__easybook '.
			'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
    	}
    	// When not editing an entry, create a new one
    	if (!$this->_data) 
		{
    	    $this->_data = $this->getTable();
    	    $this->_data->id = 0;
    	    //Insert name and email of the registred user
    	    if ($user->get('id')) 
			{
	    	    $this->_data->gbname = $user->get('name');;
	    	    $this->_data->gbmail = $user->get('email');;
    	    }
    	}    	    
    	return $this->_data;
	}
	
	/**
	 * Method to store a record
 	*
 	* @access    public
 	* @return    boolean    True on success
 	*/
	function store()
	{
    	global $mainframe;
    	jimport('joomla.utilities.date');
    	
    	$row = &$this->getTable();
		$params = &JComponentHelper::getParams( 'com_easybookreloaded' );
    	$data = JRequest::get('post');
		$data['gbtext'] = htmlspecialchars(JRequest::getVar('gbtext', NULL, 'post', 'none' ,JREQUEST_ALLOWRAW), ENT_QUOTES);
		$session = JFactory::getSession();
		$date = &JFactory::getDate();

		// Set Default Values
		if (!isset($data['id'])) 
		{
			$data['gbdate'] = $date->toMysql();
			$data['published'] = $params->get('default_published', 1);
			
			if ($params->get('enable_log', true)) 
			{
				$data['gbip'] = getenv('REMOTE_ADDR');
			} 
			else 
			{
				$data['gbip'] = "0.0.0.0";
			}
			
			$data['gbcomment'] = null;
		}
		
		// Eintrag validieren
	    if (!$this->validate($data)) 
		{
   		     return false;
    	}
    	
    	// Formfelder an Tabelle binden
    	if (!$row->bind($data)) 
		{
       	 	$this->setError($this->_db->getErrorMsg());
        	
			return false;
    	} 

   		// Eintrag in die Datenbank speichern
    	if (!$row->store()) 
		{
        	$this->setError($this->_db->getErrorMsg());
        	
			return false;
    	}
		
		$session->clear('spamcheck1', 'easybookreloaded');
		$session->clear('spamcheck2', 'easybookreloaded');
		$session->clear('spamcheckresult', 'easybookreloaded');
		$session->clear('time', 'easybookreloaded');
		
    	return $row;
	}
	
	function delete()
	{
    	$row = &$this->getTable();

       	if (!$row->delete( $this->_id )) 
		{
           	$this->setError( $this->_db->getErrorMsg() );
           	return false;
       	}
		
    	return true;
	}
	
	// Publishes an entry or unpublishes it
	function publish() 
	{
		$data = $this->getData();
		$status = $data->published;

		$query = 'UPDATE #__easybook SET `published` = '.(int)!$status.' WHERE `id` = '.$this->_id.' LIMIT 1;';
		$this->_db->SetQuery($query);
		
		if (!$this->_db->query()) 
		{
			$this->setError($this->_db->getErrorMsg());
			return -1;
		}
		
		return (int)!$status;
	}
	
	function getEasyCalcCheck () 
	{
		global $mainframe;
    	$params = &JComponentHelper::getParams( 'com_easybookreloaded' );
		$session = JFactory::getSession();
		$user = &JFactory::getUser();
		
		if ($params->get('enable_spam', true) AND ($params->get('enable_spam_reg') OR !$user->gid)) 
		{			
			$spamcheck1 = mt_rand(1, $params->get('max_value', 20));
			$spamcheck2 = mt_rand(1, $params->get('max_value', 20));
			$spamcheckresult = $spamcheck1 + $spamcheck2;
			
			$session->set('spamcheck1', $spamcheck1, 'easybookreloaded');
			$session->set('spamcheck2', $spamcheck2, 'easybookreloaded');
			$session->set('spamcheckresult', $spamcheckresult, 'easybookreloaded');
			$session->set('time', time(), 'easybookreloaded');
		}
	}
	
	function validate(&$data) 
	{
    	global $mainframe;
    	$params = &JComponentHelper::getParams( 'com_easybookreloaded' );
    	$session = JFactory::getSession();
		$user = &JFactory::getUser();
    	jimport('joomla.mail.helper');
    	jimport('joomla.filter.filterinput');
    	$filter = &JFilterInput::getInstance();
    	$errors = array();
		$error = false;
		
		$spamcheck1 = $session->get('spamcheck1', null, 'easybookreloaded');
		$spamcheck2 = $session->get('spamcheck2', null, 'easybookreloaded');
		$spamcheckresult = $session->get('spamcheckresult', null, 'easybookreloaded');
		$time = $session->get('time', null, 'easybookreloaded');
		
		// Name darf nicht leer sein
		if (empty($data['gbname'])) 
		{
    		$error = true;
    		$errors['name'] = true;
    	}
		
		// Gästebuchtext darf nicht leer sein
		if (empty($data['gbtext'])) 
		{
			$error = true;
			$errors['text'] = true; 
		}
		
		// Security Fix
		// Session-Variablen gesetzt? Wichtig bei deaktivierten Cookies - 2.0.1
		if (($params->get('enable_spam', true) AND ($params->get('enable_spam_reg') OR !$user->gid)) AND (($spamcheck1 == '') OR ($spamcheck2 == '') OR ($spamcheckresult == '') OR ($time == ''))) 
		{
    		$error = true;
    		$errors['sessionvariable'] = true;
    	}
		
		// valid text - security fix against XSS through img-tag
		if (!empty($data['gbtext'])) 
		{
			if (preg_match_all('@\[img\].+\[/img\]@isU', $text, $treffer)) 
			{
				$text = $data['gbtext'];
				
				foreach ($treffer[0] as $wert) 
				{
					$img = str_replace(array('\'', "\""), '', $wert);
					
					if (strpos($img, ' ') == true) 
					{
						$img_neu = substr($img, 0, strpos($img, ' ')).'[/img]';
						$text = str_replace($wert, $img_neu, $text);
					}
				}
				$data['gbtext'] = $text;
			}
		}
		
		// EasyCalcCheck
		if (($params->get('enable_spam', true) AND ($params->get('enable_spam_reg') OR !$user->gid)) AND (($data['easycalccheck'] != $spamcheckresult) OR ((time() - $params->get('type_time_sec')) <= $time))) 
		{
			$error = true;
			$errors['easycalccheck'] = true; 
		}
		
		// Check AIM Adress
		if (!empty($data['gbaim'])) 
		{
			$allowed = '@^[A-Za-z0-9_\.]+$@';
			
			if (!preg_match($allowed, $data['gbaim'])) 
			{
				$error = true;
				$errors['aim'] = true;
			}
		}
		
		// Check ICQ Adress
		if (!empty($data['gbicq'])) 
		{
			$allowed = '@^[0-9]+$@';
			
			if (!preg_match($allowed, $data['gbicq'])) 
			{
				$error = true;
				$errors['icq'] = true;
			}
		}
		
		// Check Yahoo Adress
		if (!empty($data['gbyah'])) 
		{
			$allowed = '@^[A-Za-z0-9_\.]+$@';
			
			if (!preg_match($allowed, $data['gbyah'])) 
			{
				$error = true;
				$errors['yah'] = true;
			}
		}
		
		// Check Skype Adress
		if (!empty($data['gbskype'])) 
		{
			$allowed = '@^[A-Za-z0-9_\.-]+$@';
			
			if (!preg_match($allowed, $data['gbskype'])) 
			{
				$error = true;
				$errors['skype'] = true;
			}
		}
		
		// Check URL
		if (!empty($data['gbpage'])) 
		{
			$data['gbpage'] = str_replace(array('\'', "\""), '', $data['gbpage']);
			
			if (strpos($data['gbpage'], ' ') == true) 
			{
				$data['gbpage'] = substr($data['gbpage'], 0, strpos($data['gbpage'], ' '));
			}
			
			$data['gbpage'] = htmlspecialchars($data['gbpage'], ENT_QUOTES);
		}
		
		//valid email-address supplied?
		if ((!empty($data['gbmail']) || $params->get('require_mail', true)) && !JMailHelper::isEmailAddress($data['gbmail'])) 
		{
			$error = true;
			$errors['mail'] = true; 
		}
		
		// Check MSN Adress
		if (!empty($data['gbmsn']) && !JMailHelper::isEmailAddress($data['gbmsn'])) 
		{
			$error = true;
			$errors['msn'] = true;
		}
		
		if ($params->get('badwordfilter', true)) 
		{
			//replace bad words
			$badwords = $this->_getBadwordList();
			foreach ($badwords as $badword) 
			{
				$data['gbtext'] = preg_replace("/\b".$badword->word."\b/i", "***" , $data['gbtext']);
			}
		}
		
		if ($error) 
		{	
			$session->set('errors', $errors, 'easybookreloaded');
			$mainframe->setUserState( 'eb_validation_errors', $errors);
			$mainframe->setUserState( 'eb_validation_data' , $data);
			return false;
		} 
		else 
		{
			return true;
		}
    }
	  
	function savecomment() 
	{
		$row = &$this->getTable();
    	$data = JRequest::get('post');
		$data['gbcomment'] = htmlspecialchars(JRequest::getVar('gbcomment', NULL, 'post', 'none' ,JREQUEST_ALLOWRAW), ENT_QUOTES);
	    
	    // Bind the form fields to the table
    	if (!$row->bind($data)) 
		{
       	 	$this->setError($this->_db->getErrorMsg());
        	return false;
    	} 

   		// Store the entry to the database
    	if (!$row->store()) 
		{
        	$this->setError($this->_db->getErrorMsg());
        	return false;
    	}
    	
		return true;
	}
	
	
	function _getBadwordList()
	{
    	if (empty($this->_badwords)) 
		{
			 $query = ' SELECT * FROM #__easybook_badwords';
			 $this->_db->setQuery( $query );
			 $this->_badwords = $this->_db->loadObjectList();
    	} 
    	
		return $this->_badwords;
	}
	
	function getRow($id)
	{
		$id = (int)$id;
		$table = $this->getTable('entry');
		$table->load($id);
		
		return $table;
	}
}
?>