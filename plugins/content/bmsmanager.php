<?php
defined('_JEXEC') or die;

/**
 * @package	Bookmark System Manager - Content rendering
 * @copyright	Copyright (C) 2008 Aleksandar Bogdanovic. All Rights Reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

jimport( 'joomla.plugin.plugin' );
jimport('joomla.enviroment.request');
// Testing and Debuging
define( 'BMSMANAGER_LIVE', '1.1.2' );global $bmsmanager_debug;$bmsmanager_debug=array();$bmsmanager_debug['debug']=0;$bmsmanager_debug['error_reporting']=0;$bmsmanager_debug['display_errors']=0;

class BMS
{
	function init($suppress,$debug=null){global $bmsmanager_debug;jimport('joomla.application.component.helper');$params=&JComponentHelper::getParams('com_bmsmanager');$bmsmanager_debug['debug']=(JDEBUG||$params->get('debug','0'));$bmsmanager_debug['error_reporting']=ini_get('error_reporting');$bmsmanager_debug['display_errors']=ini_get('display_errors');if($suppress){error_reporting(0);ini_set('display_errors',0);}if(!is_null($debug)){Linkr::set($debug);}}
	function set($debug=0){global $bmsmanager_debug;if($debug==1){$bmsmanager_debug['error_reporting']=error_reporting(E_ALL);$bmsmanager_debug['display_errors']=ini_set('display_errors',1);}elseif($debug==-1) {error_reporting($bmsmanager_debug['error_reporting']);ini_set('display_errors',$bmsmanager_debug['display_errors']);}}
	function dump($var){if(BMSMANAGER_LIVE)return;ob_clean();jexit(var_dump($var));}
	function log($msg){global $bmsmanager_debug;if(empty($msg)||!$bmsmanager_debug['debug'])return;static $log;if(is_null($log)){jimport('joomla.error.log');$o=array('format'=>'{DATE} {TIME} ({C-IP}), {COMMENT}');$log=&JLog::getInstance('bmsmanager.php',$o);}$log->addEntry(array('comment'=>$msg));}
}
/**
 * Article bookmarks
 *
 * @author Francisa Mankrah and Aleksandar Bogdanovic <albog@banitech.com>
 * @package Content
 */
class plgContentBMSManager extends JPlugin
{
	var $settings	= array();
	var $loaded		= array();
	var $pluginParams;
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgContentBMSManager( &$subject, $config ) {
		parent::__construct( $subject, $config );
		$plugin = & JPluginHelper::getPlugin('content', 'bmsmanager');
      		$pluginParams  = new JParameter( $plugin->params );
      		$this->params  = $pluginParams->_registry['_default']['data'];
		JPlugin::loadLanguage( 'plg_content_bmsmanager', JPATH_ADMINISTRATOR );
		
		// check whether plugin has been unpublished
    		if ( !$pluginParams->get( 'enabled', 1 ) ) {
    			return true;
    		}

		$document = & JFactory :: getDocument();
		$doctype = $document->getType();

		// Only render for HTML output
		if ($doctype !== 'html') {
			return false;
		}
		
		$document->addStyleSheet($this->plgGetSiteURL() . 'plugins/content/bmsmanager/'. $pluginParams->get( 'bms_css', 'classic' )  .'.css', 'text/css');
	}
	
	/**
	 * Replace anchors with links
	 * @param 	object $article The article object (by reference)
	 * @param 	object $params The article parameters object (by reference)
	 * @return void
	 */
	function onPrepareContent( &$article, &$params )
	{

		$sbmsmFrontpage = $this->params->bmsm_show_frontpage;
		$sbmsmSection = $this->params->bmsm_show_section;
		$sbmsmCategory = $this->params->bmsm_show_cate;
      		if ($this->params->auto_manual == '1'){
		if ($this->plgShowIntoFrontpageSectionCategoryOrArticle($sbmsmFrontpage, $sbmsmSection, $sbmsmCategory)  == 'True'){
			if ($this->plgAllowIntoSectionCategoryOrArticle($article) == 'True'){
			// Start by replacing links
				if ($this->plgShowInIntro($article, $this->params->bmsm_show_in_intro_text) == 'True'){
				$this->article	= & $article;
		      		$bmsmAutoManual = $this->params->auto_manual;		      		
		      		$bmsmSize = $this->params->bm_size;	
		      		$bmsmSeparator = $this->params->bm_sep;
				$sAfterOrBefore = $this->params->bmsm_after_before;
		      		$sbmsmBadgesList = $this->params->bmsm_list;
				$sbmsmLeftRight = $this->params->leftright;
				$sbmsmAfterOrBefore = $this->params->bmsm_after_before;
				$sbmsmTextByBadges = $this->params->bm_text;
				$sbmsmInIntro = $this->params->bmsm_show_in_intro_text;
				if ($bmsmAutoManual == '1') {
					$regex = "#{bmsmanager(.*?)}#s";
    					$this->article->text = preg_replace( $regex, '', $this->article->text );

					$inserbadget ="{bmsmanager:bookmarks;size:". $bmsmSize .";text:".$sbmsmTextByBadges.";separator:".$bmsmSeparator.";badges:".$sbmsmBadgesList."}";
					if ($sAfterOrBefore == '1'){
                    					$article->text = $article->text.$inserbadget;
                    			}elseif($sAfterOrBefore == '0'){
                    					$article->text = $inserbadget.$article->text;
                  			}else{
            					$article->text = $inserbadget.$article->text.$inserbadget;
            				}
				}
				$sets	= $this->getSettings();
				if (!$sets || empty( $sets )) {
					return true;
				}
				foreach ($sets as $s)
				{
					// $bmsmanager	= $s->get( 'bmsmanager', 'none' );
					$html	= '';
					$this->getBookmarks( $s, $this->params);
				}
		
				// Done
			}
			}
		}
		}else{
				$this->article	= & $article;
				$sets	= $this->getSettings();
				if (!$sets || empty( $sets )) {
					return true;
				}
				foreach ($sets as $s)
				{
					// $bmsmanager	= $s->get( 'bmsmanager', 'none' );
					$html	= '';
					$this->getBookmarks( $s);
				}
		}
		
	}
	
	/**
	 * Creates bookmark list
	 *
	 * @param 	object $sets The settings for the bookmarks
	 * @param 	object $this->article The article object
	 * @return string HTML for bookmark list
	 */
	function getBookmarks( $sets)
	{
		$badges	= $this->params->bmsm_list;

		$sbmsmHeader = $this->params->bmsm_header;
	        
		if (empty( $badges )) {
			$this->article->text	= JString::str_ireplace( $sets->match, '', $this->article->text );
			return;
		}
		
		$db	= & JFactory::getDBO();
		$q	= 'SELECT * FROM #__bmsmanager_bookmarks ';
		if ($badges == ''){
			$q = $q .'ORDER BY ordering ASC';
		}elseif ($badges != '*'){
			$q	.= 'WHERE id IN ('. $badges .') ORDER BY ordering ASC';
		}else
		{
			$q = 'WHERE id IN ('. $badges .') ORDER BY ordering ASC';
		}

		$db->setQuery($q);
		if (!($list = $db->loadObjectList())) {
			$this->article->text	= JString::str_ireplace( $sets->match, '', $this->article->text );
			return;
		}
		if (!$list = $db->loadObjectList()) {
			BMS::log( 'Could not retrieve bookmarks: '. $db->getErrorMsg() );
			return $this->remove( $sets->match );
		}
		JHTML::_( 'behavior.tooltip' );
		$_size	= $sets->get( 'size', 'text' );
		$html	= 'html'. $_size;
		$content	= array();
		
		foreach ($list as $bm)
		{

			if (!empty( $bm->$html )) {
				$size	= $_size;
				$code	= $bm->$html;
			} else {
				$size	= ($bm->size) ? $bm->size : 'text';
				$def	= 'html'. $size;
				$code	= $bm->$def;
			}
			
			if (!empty( $code )) 
			{
				$this->repBookmarkAnchors( $sets, $code, $bm->text);
				// $title	= JText::sprintf( 'ADD_BM', $bm->text, JString::ucfirst( $bm->name ) );
				$title	= JText::sprintf( 'ADD_BM', $bm->text, JString::ucfirst( $bm->text ) );
				
				// Add text
				if ($size != 'text')
				{
					switch ( $sets->get( 'text', 'nn' ) )
					{
						case 'yl':
							$code	= $bm->text .' '. $code;
							break;
						
						case 'yr':
							$code	.= ' '. $bm->text;
							break;
					}
				}
				$content[]	=	'<span class="hasTip" title="'.
								$title .'">'. $code .'</span>';
			}
			
		}
		if (empty( $content )) {
			$this->article->text	= JString::str_ireplace( $sets->match, '', $this->article->text );
			return;
		}
		$sep =  $sets->get( 'separator', '&nbsp;');
		if ($this->params->auto_manual == '1') {
			if ($this->params->leftright == 'left')
			{
	        	       	$content = '<div align="left" class="bmsmanager-bm">'.
							implode( $sep, $content ) .
						'</div>';
			}else{
			         $content = '<div align="right" class="bmsmanager-bm">'.
							$sbmsmHeader.'<br/>'.implode($sep, $content ) .
						'</div>';
				
		        }
		        $regex = "#{bmsmanager(.*?)}#s";
			$this->article->text = preg_replace( $regex, $content, $this->article->text );
	        }else{
			$content	=	'<div class="bmsmanager-bm">'.
							$sbmsmHeader.'<br/>'.implode($sep, $sbmsmHeader.''.$content ) .
						'</div>';
			$this->article->text	= JString::str_ireplace($sets->match, $content, $this->article->text);
		}
	}
	
	/**
	 * Replaces anchors in bookmark's HTML codes
	 *
	 * @param 	object $s		The settings for the bookmark
	 * @param 	string $html	HTML code for bookmark
	 * @param 	string $text	Bookmark text
	 * @return string HTML code for bookmark list
	 */
	function repBookmarkAnchors( $s, &$html, $text)
	{
		// [twin]: Target of a Window
		if (JString::strpos( $html, '[twin]' )) {
			$target	= $s->get( 'twin', '' );
			if ($target == '1'){
				$target = 'target="'. $this->plgStringRandomize(3, 'alpha') .'" ';
			}elseif ($target == '2'){
				// popup
				$pwn = $this->plgStringRandomize(3, 'alpha');
				$target = 'target="'. $pwn .'" ';
				$target = $target.'onclick="window.open(this.href,';
				$target = $target. "'".$pwn. "',";
				$target = $target. "'width=".$pluginRegistry->bmsm_width_popup.",height=".$this->params->bmsm_high_popup.",toolbar=".$this->params->bmsm_popup_toolbar.",location=".$pluginRegistry->bmsm_popup_location.",status=".$this->params->bmsm_popup_status.",scrollbars=".$this->params->bmsm_popup_scrollbars.",resizable=".$this->params->bmsm_popup_resizable."')";
				$target = $target. ';return false;"';
			}else{
				$target = '';
			}
			$html	= JString::str_ireplace( '[twin]', $target, $html );
		}
		
		// [badgespath]: Path to badges folder
		if (JString::strpos( $html, '[badgespath]' )) {
			$path	= JURI::root() .'components/com_bmsmanager/assets/badges';
			$html	= JString::str_ireplace( '[badgespath]', $path, $html );
		}
		
		// [url]: Article URL
		if (JString::strpos( $html, '[url]' )) {
			$url	= $this->plgGetUrl($this->article);
			$html	= JString::str_ireplace( '[url]', $url, $html );
		}
		
		// [title]: Article title
		if (JString::strpos( $html, '[title]' )) {
			// $title	= $s->get( 'articleTitle', '' );
			$title = $this->article->title;
			
			/*if (JString::strpos( $html, '[desc]' ) == '0')
			{
				echo 'yes';

			}*/
			$html = JString::str_ireplace( '[title];', '[title]', $html);
			$html = JString::str_ireplace( '[title]', $title, $html);
		}

		// [desc]: Article summary
		if (JString::strpos( $html, '[desc]' )) {
			// $desc	= $this->plgHtmlToText($s->get( 'articleDesc', '' ));
			$html	= JString::str_ireplace( '[desc]', $desc, $html );
		}
		
		// [text]: Bookmark text
		if (JString::strpos( $html, '[text]' )) {
			$html	= JString::str_ireplace( '[text]', $text, $html );
		}
		
	}
	
	
	/**
	 * Retrieves settings from anchor in text
	 *
	 * @param 	string $text The article text to search in
	 * @return object BMS settings or false on failure
	 */
	function getSettings( $off = 0 )
	{
		if (JString::strpos( $this->article->text, '{bmsmanager:none}' ) !== false) {
			$this->settings	= false;
			return false;
		}
		
		if (($a = JString::strpos( $this->article->text, '{bmsmanager', $off )) === false) {
			return $this->getDefaultSettings();
		}
		
		if (($b = JString::strpos( $this->article->text, '}', $a )) === false) {
			return false;
		}
		
		if (($c = JString::substr( $this->article->text, $a, $b - $a + 1 )) === false) {
			return false;
		}
		
		// Format settings
		$d	= str_replace( array( '{', '}' ), '', $c );
		
		if ($d = @explode( ';', $d ))
		{
			$s	= new JObject();
			
			foreach ($d as $e)
			{
				if (strpos( $e, ':' ) !== false) {
					$e	= explode( ':', $e );
					$s->set( $e[0], $e[1] );
				} else {
					$s->set( $e, true );
				}
			}
			$s->set( 'match', $c );
			$s->set( 'articleTitle', $this->article->title);
			$i_text= $this->article->introtext;
			$s->set( 'articleDesc', ''); 
			//$this->plgUtfPrepare($this->plgHtmlToText($i_text)));
			
			$this->settings[]	= & $s;
			return $this->getSettings( $b + 1 );
		}
		
		return false;
	}
	
	function getDefaultSettings()
	{
		if (JString::strpos( $this->article->text, '{bmsmanager:none}' ) !== false) {
			$this->remove( '{bmsmanager:none}' );
			return empty( $this->settings ) ? false : $this->settings;
		}
		
		$settings	= array();
		
		// Show bookmarking by default
		
			$listofbatgets = $this->params->bmsm_list;
			$sbmsm_popular = $this->params->bmsm_popular;
			if ($sbmsm_popular == '1'){
				$q	= 'SELECT id FROM #__bmsmanager_bookmarks WHERE popular=1 ORDER BY ordering ASC';
			}else{
  				if ($listofbatgets =='' || $listofbatgets == '*')
  	  			{  				
	  				$q	= 'SELECT id FROM #__bmsmanager_bookmarks ORDER BY ordering DESC';
	  			}else{
  					$q	= 'SELECT id FROM #__bmsmanager_bookmarks WHERE id IN ('.$listofbatgets .') ORDER BY ordering DESC';
  				}
			}
			$db	= & JFactory::getDBO();
			$db->setQuery( $q );
						
			if ($list = $db->loadRowList())
			{
				$badges	= array();
				foreach ($list as $id) {
					$badges[]	= $id[0];
				}
				
				// $this->article->text	.= '{bmsmanager:bookmarks}';
				$s	= new JObject();
				$s->set( 'bmsmanager', 'bookmarks' );
				$s->set( 'size', $this->params->bm_size);
				$s->set( 'separator', $this->params->bm_sep);
				$s->set( 'badges', implode( ',', $badges ) );
				// $s->set( 'match', '{bmsmanager:bookmarks}' );
				$s->set( 'articleTitle', $this->article->title);
				$i_text= $this->article->introtext;
				$s->set( 'articleDesc', ''); 
				// $this->plgUtfPrepare($this->plgHtmlToText($i_text)));
				$s->set( 'twin', $this->params->newpopparent);
				$settings[]	= $s;
			}

		
	
		if (empty( $settings ) && empty( $this->settings )) {
			return false;
		} else {
			$this->settings	= array_merge( $settings, $this->settings );
			return $this->settings;
		}
	}
			
	function plgStringRandomize($length = 8, $seeds = 'alpha')
	{
          // Possible seeds
          $seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
          $seedings['numeric'] = '0123456789';
          $seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
          $seedings['hexidec'] = '0123456789abcdef';
          
          // Choose seed
          if (isset($seedings[$seeds]))
          {
              $seeds = $seedings[$seeds];
          }
          
          // Seed generator
          list($usec, $sec) = explode(' ', microtime());
          $seed = (float) $sec + ((float) $usec * 100000);
          mt_srand($seed);
          
          // Generate
          $str = '';
          $seeds_count = strlen($seeds);
          for ($i = 0; $length > $i; $i++)
          {
              $str .= $seeds
              {
                  mt_rand(0, $seeds_count - 1)
                  };
          }
          
          return $str;
	}
	function plgAllowIntoSectionCategoryOrArticle(&$row)
	{
		$value_sec = 0;
		$value_cat = 0;
		$value_art = 0;
		if ($this->params->sectionid !=''){
	
			// Check accepted section	
  			$aAcceptedSectionsArray = array();
  			$aAcceptedSectionsArray = explode(',',$this->params->sectionid);
  			if(in_array($row->sectionid, $aAcceptedSectionsArray) != true){
   				$value_sec = '1';
  			}
  			unset($aAcceptedSectionsArray);
  		}
	  	
  			// Check accepted category
  		if ($this->params->catid !=''){
  			$aAcceptedCategoryArray = array();
  			$aAcceptedCategoryArray = explode(',',$this->params->catid);
  			if(in_array($row->catid, $aAcceptedCategoryArray) != true){
    				$value_cat = '1';
  			}
			unset($aAcceptedCategoryArray);
  		}
  			
  			
  			// Check ignored articles
  		if ($this->params->articleid !=''){
	  		$aIgnoredArticleArray = array();
  			$aIgnoredArticleArray = explode(',',$this->params->articleid);
  			if(in_array($row->id, $aIgnoredArticleArray) ){
				$value_art = '1';
	  		}
	  		unset($aIgnoredArticleArray);
  		}
  		if( ($value_sec==1) || ($value_cat==1) || ($value_art==1) ){
  			return false;
  		}else{
  			return true;
  		}
  		
  	}
  	function plgShowInIntro(&$obj, &$inintro)
	{
		if ($inintro == '1')
		{
			return true;
		}
		else
		{
			
			if (JString::strlen(strip_tags($obj->fulltext)) !='0')
			{
				if ((JRequest :: getVar('view')) == 'article')
				{
					return true;				
				}else{
					return false;
				}
			}else{
				return true;
			}
		}
	}
	function plgShowIntoFrontpageSectionCategoryOrArticle($fron_page, $sec, $cat)
	{
		$view_bmsm = JRequest :: getVar('view');
		switch ($view_bmsm)
		{
			case 'article':
				return true;
				break;
			case 'frontpage':
				if ($fron_page == '1')
				{
					return true;
				}else{
					return false;
				}
				break;
			case 'section':
				if ($sec == '1')
				{
					return true;
				}else{
					return false;
				}
				break;
			case 'category':
				if ($cat == '1')
				{
					return true;
				}else{
					return false;
				}
				break;
		}
	}
	function plgGetUrl(&$obj, $endslash = '1')
	{
		$url ='Error, No URL';
		if (!is_null($obj)) 
		{
			require_once( JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php' );
			$url = JRoute::_(ContentHelperRoute::getArticleRoute($obj->slug, $obj->catslug, $obj->sectionid));
			$uri     =& JURI::getInstance();
      		$base  = $uri->toString( array('scheme', 'host', 'port'));
			$url = $base . $url;
			$url = JRoute::_($url, false, 0);
			if ($endslash == '1'){
				$url = $this->add_ending_slash($url);
			}
			return $url;
		}
		return $url;
	}
	function add_ending_slash($path){
 
    	$slash_type = (strpos($path, '\\')===0) ? 'win' : 'unix'; 
 
    	$last_char = substr($path, strlen($path)-1, 1);
 
    	if ($last_char != '/' and $last_char != '\\') {
        	// no slash:
        	$path .= ($slash_type == 'win') ? '\\' : '/';
    	}
 
    	return $path;
	}
	
	function plgReplaceLastChraracterWithIf($var, $with = '', $iflast = ';')
	{
    	$lminusone = strlen($var)-1;
		$last_char = substr($var, $lminusone, 1);
 
    	if ($last_char == $iflast) {
        	$var =  substr($var, 0, $lminusone).$with; 
    	}
 
    	return $var;
	}
	
	function plgGetSiteURL() {
		global $mainframe;	
		return ($mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base());
	}
	function plgHtmlToText($var)
	{
		return strip_tags($var);
	}
	function plgUtfPrepare($var)
	{    	
		
   		return $var;
	}

}
