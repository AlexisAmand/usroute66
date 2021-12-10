<?php
/*
// PanelSharing Plugin for Joomla! 1.5.x - Version 1.2
// Copyright (c) 2010 Serafino Bilotta. All rights reserved.
// Released under the GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
// More info at http://www.p2warticles.com
// Designed and developed by Serafino Bilotta
// *** Last update: June 16th, 2010 ***
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Import library dependencies
jimport('joomla.event.plugin');
 
class plgContentPanelsharing extends JPlugin
{
	
    function plgContentPanelsharing( &$subject, $params )
    {
            parent::__construct( $subject );
 			global $mainframe;
			
			// load plugin parameters
            $this->_plugin = JPluginHelper::getPlugin( 'content', 'panelsharing' );
            $this->_params = new JParameter( $this->_plugin->params );
            $view_content = JRequest :: getVar('view');
                        if($view_content=='article'){
			//load jquery?
			$loadjquery = $this->_params->get( 'setjquery');
			$panelstyle = $this->_params->get('panelstyle');
                        $panelposition = $this->_params->get('panelposition');
                        $borderradius = $this->_params->get('borderradius');
			
			if ($loadjquery == 1){
				JHTML::_('behavior.mootools');
				$mainframe->addCustomHeadTag('
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
');
				$mainframe->addCustomHeadTag('
<script type="text/javascript">jQuery.noConflict();</script>
');
			}
			$mainframe->addCustomHeadTag('
<style type="text/css" media="screen">
/*
// Panelsharing Plugin v1.0 for Joomla! 1.5.x
// Copyright (c) 2010 Serafino Bilotta. All rights reserved.
// More info at http://www.p2warticles.com
*/
'.$panelstyle.'
');
                        if($panelposition=='fixed'){
                            $mainframe->addCustomHeadTag('.panel {
position: fixed;
left: 0;
-moz-border-radius-topright: '.$borderradius.'px;
-webkit-border-top-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomright: '.$borderradius.'px;
-webkit-border-bottom-right-radius: '.$borderradius.'px;
padding: 30px 30px 30px 130px;
}
a.trigger{
position: fixed;
left: 0;
padding: 20px 40px 20px 15px;
background:#333 url(http://img340.imageshack.us/img340/4080/pluse.png) 85% 55% no-repeat;
-moz-border-radius-topright: '.$borderradius.'px;
-webkit-border-top-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomright: '.$borderradius.'px;
-webkit-border-bottom-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomleft: 0px;
-webkit-border-bottom-left-radius: 0px;
}

a.trigger:hover{
position: fixed;
left: 0;
padding: 20px 40px 20px 20px;
background:#222 url(http://img340.imageshack.us/img340/4080/pluse.png) 85% 55% no-repeat;
-moz-border-radius-topright: '.$borderradius.'px;
-webkit-border-top-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomright: '.$borderradius.'px;
-webkit-border-bottom-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomleft: 0px;
-webkit-border-bottom-left-radius: 0px;
}
a.active.trigger {
background:#222222 url(http://img691.imageshack.us/img691/9669/minusn.png) 85% 55% no-repeat;
}</style>');
                        } elseif ($panelposition=='left') {
                            $mainframe->addCustomHeadTag('.panel {
position: absolute;
left: 0;
-moz-border-radius-topright: '.$borderradius.'px;
-webkit-border-top-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomright: '.$borderradius.'px;
-webkit-border-bottom-right-radius: '.$borderradius.'px;
padding: 30px 30px 30px 130px;
}
a.trigger{
position: absolute;
left: 0;
padding: 20px 40px 20px 15px;
background:#333 url(http://img340.imageshack.us/img340/4080/pluse.png) 85% 55% no-repeat;
-moz-border-radius-topright: '.$borderradius.'px;
-webkit-border-top-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomright: '.$borderradius.'px;
-webkit-border-bottom-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomleft: 0px;
-webkit-border-bottom-left-radius: 0px;
}

a.trigger:hover{
position: absolute;
left: 0;
padding: 20px 40px 20px 20px;
background:#222 url(http://img340.imageshack.us/img340/4080/pluse.png) 85% 55% no-repeat;
-moz-border-radius-topright: '.$borderradius.'px;
-webkit-border-top-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomright: '.$borderradius.'px;
-webkit-border-bottom-right-radius: '.$borderradius.'px;
-moz-border-radius-bottomleft: 0px;
-webkit-border-bottom-left-radius: 0px;
}
a.active.trigger {
background:#222222 url(http://img691.imageshack.us/img691/9669/minusn.png) 85% 55% no-repeat;
}</style>');
                        } else {
                            $mainframe->addCustomHeadTag('.panel {
position: absolute;
right: 0;
-moz-border-radius-topleft: '.$borderradius.'px;
-webkit-border-top-left-radius: '.$borderradius.'px;
-moz-border-radius-bottomleft: '.$borderradius.'px;
-webkit-border-bottom-left-radius: '.$borderradius.'px;
padding: 30px 130px 30px 30px;
}
a.trigger{
position: absolute;
right: 0;
padding: 20px 15px 20px 40px;
background:#333 url(http://img340.imageshack.us/img340/4080/pluse.png) 15% 55% no-repeat;
-moz-border-radius-topleft: '.$borderradius.'px;
-webkit-border-top-left-radius: '.$borderradius.'px;
-moz-border-radius-bottomleft: '.$borderradius.'px;
-webkit-border-bottom-left-radius: '.$borderradius.'px;
-moz-border-radius-bottomright: 0px;
-webkit-border-bottom-right-radius: 0px;
}

a.trigger:hover{
position: absolute;
right: 0;
padding: 20px 20px 20px 40px;
background:#222 url(http://img340.imageshack.us/img340/4080/pluse.png) 15% 55% no-repeat;
-moz-border-radius-topleft: '.$borderradius.'px;
-webkit-border-top-left-radius: '.$borderradius.'px;
-moz-border-radius-bottomleft: '.$borderradius.'px;
-webkit-border-bottom-left-radius: '.$borderradius.'px;
-moz-border-radius-bottomright: 0px;
-webkit-border-bottom-right-radius: 0px;
}
a.active.trigger {
background:#222222 url(http://img691.imageshack.us/img691/9669/minusn.png) 15% 55% no-repeat;
}</style>');
                        }

			$mainframe->addCustomHeadTag('<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery(".trigger").click(function(){
		jQuery(".panel").toggle("fast");
		jQuery(this).toggleClass("active");
		return false;
	});
});
</script>');
			
			if($this->_params->get('showTopsyButton')==1)
				$mainframe->addCustomHeadTag('<script type="text/javascript" src="http://cdn.topsy.com/topsy.js?init=topsyWidgetCreator"></script>');
			
                        }
    }
	
	function onPrepareContent( &$article, &$params ){
			$view_content = JRequest :: getVar('view');
                        if($view_content=='article' && $this->excludeFromCatSecArticle($article)){
			$id=JRequest::getVar('id');
			// load plugin parameters
                        $this->_plugin = JPluginHelper::getPlugin( 'content', 'panelsharing' );
                        $this->_params = new JParameter( $this->_plugin->params );
			
			
			$uri =& JURI::getInstance();
			$url = $uri->getScheme().'://'.$uri->getHost().$uri->getPath().'?'.$uri->getQuery();
						
			$VshowFBLikeButton=$this->_params->get('showFBLikeButton');
                        $VFBlikePosition=$this->_params->get('FBlikePosition');
			$VFBlikeTB=$this->_params->get('FBlikeTB');


                        $loaddivshare[].=implode('',$this->getAllButtons($url,$article->title));

    			if($VFBlikeTB==0)
    				$article->text = implode("\n", $loaddivshare).implode("",$this->likeButtonFB($VshowFBLikeButton,$VFBlikePosition,$url)).$article->text;
			else
                            {
				$article->text = implode("\n", $loaddivshare).$article->text.implode("",$this->likeButtonFB($VshowFBLikeButton,$VFBlikePosition,$url));
			}
                        }
	}
	
	function getAllButtons ($u,$t){
		// load plugin parameters
        $this->_plugin = JPluginHelper::getPlugin( 'content', 'panelsharing' );
        $this->_params = new JParameter( $this->_plugin->params );

		
		$url=$u;
		$loaddivshare[]= '<div class="panel"><div id="share_buttons">';
		$loaddivshare[].=$this->getCustomCode($this->_params->get('showCustomCode'),$this->_params->get('customCode'));
		$loaddivshare[].=$this->getTweetmeme($this->_params->get('showTweetmemeButton'),$url,$this->_params->get('tweetmemeUser'),$this->_params->get('tweetmemeUrlShort'));
		$loaddivshare[].=$this->getTopsy($this->_params->get('showTopsyButton'),$url,$t,$this->_params->get('tweetmemeUser'),$this->_params->get('topsyThemes'));
		$loaddivshare[].=$this->getDigg($this->_params->get('showDiggButton'));
		$loaddivshare[].=$this->getReddit($this->_params->get('showRedditButton'),$url);
		$loaddivshare[].=$this->getUpnews($this->_params->get('showUpnewsButton'),$url);
		$loaddivshare[].=$this->getFbshare($this->_params->get('showFbshareButton'),$url);
		$loaddivshare[].=$this->getStumbleupon($this->_params->get('showStumbleuponButton'),$url);
		$loaddivshare[].=$this->getSphinn($this->_params->get('showSphinnButton'),$url);
		$loaddivshare[].=$this->getBuzz($this->_params->get('showBuzzButton'),$url);
		$loaddivshare[].=$this->getDiggita($this->_params->get('showDiggitaButton'),$url);
		$loaddivshare[].=$this->getFacebook($this->_params->get('showFacebookButton'),$url);
		$loaddivshare[].=$this->getTechnotizie($this->_params->get('showTechnotizieButton'));
		$loaddivshare[].=$this->getEmail($this->_params->get('showEmailButton'),$url, $t);
		$loaddivshare[].=$this->getYBuzz($this->_params->get('showYBuzzButton'),$url);
		$loaddivshare[].=$this->getMySpace($this->_params->get('showMySpaceButton'));
		$loaddivshare[].=$this->getDesignFloat($this->_params->get('showDesignFloat'),$url);
		$loaddivshare[].=$this->getDZone($this->_params->get('showDZone'),$url,$t);
		$loaddivshare[].=$this->getPoweredBy($this->_params->get('showPoweredBy'));
		$loaddivshare[].='</div></div><a class="trigger" href="#">'.$this->_params->get('triggertext').'</a>';
		
		return $loaddivshare;
	}
	
	function excludeFromCatSecArticle(&$row){
		// load plugin parameters
		$plugin=&JPluginHelper::getPlugin('content', 'panelsharing');
		$Params=new JParameter( $plugin->params );
		$pluginRegistry=$Params->_registry['_default']['data'];
		if ($pluginRegistry->excludesection!=''){
			$Vexcludesection=array();
			$Vexcludesection=explode(',',$pluginRegistry->excludesection);
			if (in_array($row->sectionid,$Vexcludesection))
				return false;
		}
		if ($pluginRegistry->excludecat!=''){
			$Vexcludecat=array();
			$Vexcludecat=explode(',',$pluginRegistry->excludecat);
			if (in_array($row->catid,$Vexcludecat))
				return false;
		}
		if ($pluginRegistry->excludearticle!=''){
			$Vexcludearticle=array();
			$Vexcludearticle=explode(',',$pluginRegistry->excludearticle);
			if (in_array($row->id,$Vexcludearticle))
				return false;
		}
		return true;
	}
	
		
	function likeButtonFB($value, $position, $url){
		$like[]='';
		
		if ($value==1){
			$lb[]='
<div id="fb_like_button" style="margin:5px; float:';
			if ($position==0)
				$lb[].='left';
			else
				$lb[].='right';
			$lb[].='">
  <iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode(htmlentities($url));
			if($this->_params->get('FBlikeLayoutStyle')=='standard')
				$height='35';
			elseif($this->_params->get('FBlikeLayoutStyle')=='button_count')
				$height='21';
			if($this->_params->get('FBlikeLayoutStyle')=='standard' && $this->_params->get('FBlikeShowFaces')==1)
				$height='80';
			$lb[].='&amp;layout='.$this->_params->get('FBlikeLayoutStyle');
			$lb[].='&amp;show_faces='.$this->_params->get('FBlikeShowFaces');
			$lb[].='&amp;width='.$this->_params->get('FBlikeWidth');
			$lb[].='&amp;action='.$this->_params->get('FBlikeVerb');
			$lb[].='&amp;font='.$this->_params->get('FBlikeFont');
			$lb[].='&amp;colorscheme='.$this->_params->get('FBlikeColor');
			$lb[].='&amp;height='.$height;
			$lb[].='" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'.$this->_params->get('FBlikeWidth').'px; height:'.$height.'px;" allowTransparency="true"></iframe>
</div>
';
			$like=$lb;
			return $like;			
		}
		return $like;
	}
	//Buttons Functions
	function getTweetmeme($button, $u, $tweetmeme, $short){
		if($button==1){
			if ($short!='no')
				$shortener='tweetmeme_service = \''.$short.'\';';
			else
				$shortener='';
			return '
                            <div class="single_button">
<script type="text/javascript">
tweetmeme_source = \''.$tweetmeme.'\';
'.$shortener.'
</script>
<script type="text/javascript" src="http://tweetmeme.com/i/scripts/button.js"></script></div>
';}
		return '';
	}
	
	function getDigg($button){
		if($button==1)
		   return '
<div class="single_button">
  <script type="text/javascript">(function(){var s = document.createElement(\'SCRIPT\'), s1 = document.getElementsByTagName(\'SCRIPT\')[0];s.type = \'text/javascript\';s.src = \'http://widgets.digg.com/buttons.js\';s1.parentNode.insertBefore(s, s1);})();
</script>
  <a class="DiggThisButton DiggMedium"></a></div>
';
	  	return '';
	}
	
	function getUpnews($button,$u){
		if($button==1)
			return '
<div class="single_button">
	<script src="http://www.upnews.it/tools/button.php" type="text/javascript"></script>
</div>
';
		return '';
	}
	
	function getReddit($button,$u){
		if($button==1)
			return '
<div class="single_button">
	<script type="text/javascript" src="http://reddit.com/static/button/button2.js"></script>
</div>
';
		return '';
	}
	
	function getFbshare($button,$u){
		if($button==1)
			return '
<div class="single_button">
	<script src="http://widgets.fbshare.me/files/fbshare.js"></script>
</div>
';
		return '';
	}
	
	function getStumbleupon($button,$u){
		if($button==1)
			return '
<div class="single_button">
  <script src="http://www.stumbleupon.com/hostedbadge.php?s=5"></script>
</div>
';
		return '';
	}
	
	function getSphinn($button,$u){
		if($button==1)
			return '
<div class="single_button">
	<script type="text/javascript" src="http://sphinn.com/evb/button.php"></script>
</div>
';
		return '';
	}
	
	function getBuzz($button,$u){
		if($button==1)
			return '
<div class="single_button">
	<a title="Post on Google Buzz" class="google-buzz-button" href="http://www.google.com/buzz/post" data-button-style="normal-count"></a>
	<script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script>
</div>
';
		return '';
	}
	
	function getDiggita($button,$u){
		if($button==1)
			return '
<div class="single_button">
	<script type="text/javascript" src="http://www.diggita.it/evb/button.php"></script>
</div>
';
		return '';
	}
	
	function getFacebook($button,$u){
		if($button==1)
			return '
<div class="single_button"><a name="fb_share" type="box_count" href="http://www.facebook.com/sharer.php">Share</a>
  <script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
  <br />
</div>
';
		return '';
	}
	
	function getTechnotizie($button){
		if($button==1)
			return '
<div class="single_button">
  <script type="text/javascript">tech_url= encodeURIComponent(window.location.href);tech_title= encodeURIComponent(document.title);</script>
  <script src="http://www.technotizie.it/script/tech2.js" type="text/javascript"></script>
</div>
';
		return '';
	}
	
	function getEmail($button,$u,$t){
		if($button==1)
			return '
<div class="single_button">
  <iframe src="http://getmailcounter.com/mailcounter/?url='.urlencode(htmlentities($u)).'&title='.urlencode(htmlentities($t)).'" height="64" width="50" frameborder="0" scrolling="no"></iframe>
</div>
';
		return '';
	}
	
	function getYBuzz($button,$u){
		if($button==1)
			return '
<div class="single_button">
<script type="text/javascript">
    yahooBuzzArticleId = window.location.href;
</script>
  <script type="text/javascript" src="http://d.yimg.com/ds/badge2.js" badgetype="square"></script>
</div>
';
		return '';
	}
	function getMySpace($button){
		if($button==1)
			return '
<div class="single_button"><a href="javascript:void(window.open(\'http://www.myspace.com/Modules/PostTo/Pages/?u=\'+encodeURIComponent(document.location.toString()),\'ptm\',\'height=450,width=440\').focus())"><img src="http://cms.myspacecdn.com/cms/ShareOnMySpace/LargeSquare.png" border="0" alt="Share on MySpace" /></a></div>
';
		return '';
	}
	
	function getDesignFloat($button,$u){
		if($button==1)
			return '
<div class="single_button">
  <script type="text/javascript">submit_url =\''.urlencode(htmlentities($u)).'\';</script>
  <script type="text/javascript" src="http://www.designfloat.com/evb2/button.php"></script>
</div>
';
		return '';
	}
	
	function getDZone($button,$u,$t){
		if($button==1)
			return '
<div class="single_button">
  <script type="text/javascript">var dzone_url = \''.urlencode(htmlentities($u)).'\';</script>
  <script type="text/javascript">var dzone_title = \''.htmlentities($t).'\';</script>
  <script language="javascript" src="http://widgets.dzone.com/links/widgets/zoneit.js"></script>
</div>
';
		return '';
	}
	
	function getTopsy($button,$url,$t,$user,$theme){
		if($button==1)
			return '
<div class="single_button">
	<div class="topsy_widget_data"><!--
	    {
    	    "url": "'.$url.'",
        	"title": "'.$t.'",
			"theme": "'.$theme.'",
    	    "nick": "'.$user.'",
			"style": "big"
    	}
--></div>
</div>';
		return '';
	}
	
	function getCustomCode($button,$code){
		if ($button==1)
			return '<div class="single_button">'.$code.'</div>';
		return '';
	}
	
	function getPoweredBy($button){
		if($button==1)
			return '
<div class="single_button"><span style="font-size:9px;text-shadow:#000 1px 1px 4px;background-color:#fff;font-family:Arial Narrow"><a href="http://www.p2warticles.com" style="color:#fff;" target="_blank">PwdByEddie</a></span></div>
';
		return '';
	}
	
}