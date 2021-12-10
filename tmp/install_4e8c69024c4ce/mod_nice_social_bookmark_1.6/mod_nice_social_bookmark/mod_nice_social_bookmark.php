<?php
// no direct access
//defined( '_VALID_MOS' ) or die( 'Restricted access' );
$url = "http://".$_SERVER['HTTP_HOST'] . getenv('REQUEST_URI'); 
$rss_url = "http://".$_SERVER['HTTP_HOST'];
$isize = $params->get('isize');
$iset = $params->get('iset');
$iposition = $params->get('iposition');
$document = JFactory::getDocument();
$opac = $params->get('opac');
if ($opac=="yes"){
		$document->addStyleSheet('modules/mod_nice_social_bookmark/css/nsb-opac.css');
		}
		elseif ($opac=="invert"){$document->addStyleSheet('modules/mod_nice_social_bookmark/css/nsb-opac-inv.css');
		}
		else{$document->addStyleSheet('modules/mod_nice_social_bookmark/css/nsb.css');
		}
$twlink = $params->get('twlink');
$fblink = $params->get('fblink');
$mslink = $params->get('mslink');
$lilink = $params->get('lilink');
$rsslink = $params->get('rsslink');

echo '<div class="nsb_container" align="'.$iposition.'">';
$tt = $params->get('s1', 'yes');
if ($tt == "yes"){ if ($fblink == "")
echo '<a id="l1" target="_blank" rel="nofollow" href="http://www.facebook.com/sharer.php?u='.$url.'&title="><img title="Facebook" border="0" src="modules/mod_nice_social_bookmark/icons/facebook_'.$iset.'_'.$isize.'.png" alt="Facebook" /></a>';
else echo '<a id="l1" target="_blank" rel="nofollow" href="http://'.$fblink.'"><img title="Facebook" border="0" src="modules/mod_nice_social_bookmark/icons/facebook_'.$iset.'_'.$isize.'.png" alt="Facebook" /></a>';}
$tt = $params->get('s2', 'yes');
if ($tt == "yes"){ if ($mslink == "")
echo '<a id="l2" target="_blank" rel="nofollow" href="http://www.myspace.com/Modules/PostTo/Pages/?l=3&amp;u='.$url.'&amp;title="><img title="MySpace" border="0" src="modules/mod_nice_social_bookmark/icons/myspace_'.$iset.'_'.$isize.'.png" alt="MySpace" /></a>';
else echo '<a id="l2" target="_blank" rel="nofollow" href="http://'.$mslink.'"><img title="MySpace" border="0" src="modules/mod_nice_social_bookmark/icons/myspace_'.$iset.'_'.$isize.'.png" alt="MySpace" /></a>';}
$tt = $params->get('s3', 'yes');
if ($tt == "yes"){ if ($twlink == "")
echo '<a id="l3" target="_blank" rel="nofollow" href="http://twitter.com/home?status='.$url.'&amp;title="><img title="Twitter" border="0" src="modules/mod_nice_social_bookmark/icons/twitter_'.$iset.'_'.$isize.'.png" alt="Twitter" /></a>';
else echo '<a id="l3" target="_blank" rel="nofollow" href="http://'.$twlink.'"><img title="Twitter" border="0" src="modules/mod_nice_social_bookmark/icons/twitter_'.$iset.'_'.$isize.'.png" alt="Twitter" /></a>';}
$tt = $params->get('s4', 'yes');
if ($tt == "yes")echo '<a id="l4" target="_blank" rel="nofollow" href="http://digg.com/submit?phase=2&amp;url='.$url.'&amp;title="><img title="Digg" border="0" src="modules/mod_nice_social_bookmark/icons/digg_'.$iset.'_'.$isize.'.png" alt="Digg" /></a>';
$tt = $params->get('s5', 'yes');
if ($tt == "yes")echo '<a id="l5" target="_blank" rel="nofollow" href="http://del.icio.us/post?url='.$url.'&amp;title="><img title="Delicious" border="0" src="modules/mod_nice_social_bookmark/icons/delicious_'.$iset.'_'.$isize.'.png" alt="Delicious" /></a>';
$tt = $params->get('s6', 'yes');
if ($tt == "yes")echo '<a id="l6" target="_blank" rel="nofollow" href="http://www.stumbleupon.com/submit?url='.$url.'&amp;title="><img title="Stumbleupon" border="0" src="modules/mod_nice_social_bookmark/icons/stumbleupon_'.$iset.'_'.$isize.'.png" alt="Stumbleupon" /></a>';
$tt = $params->get('s7', 'yes');
if ($tt == "yes")echo '<a id="l7" target="_blank" rel="nofollow" href="http://www.google.com/bookmarks/mark?op=edit&amp;bkmk='.$url.'&amp;title="><img title="Google Bookmarks" border="0" src="modules/mod_nice_social_bookmark/icons/google_'.$iset.'_'.$isize.'.png" alt="Google Bookmarks" /></a>';
$tt = $params->get('s8', 'yes');
if ($tt == "yes")echo '<a id="l8" target="_blank" rel="nofollow" href="http://reddit.com/submit?url='.$url.'&amp;title="><img title="reddit" border="0" src="modules/mod_nice_social_bookmark/icons/reddit_'.$iset.'_'.$isize.'.png" alt="Reddit" /></a>';
$tt = $params->get('s9', 'yes');
if ($tt == "yes")echo '<a id="l9" target="_blank" rel="nofollow" href="http://www.newsvine.com/_tools/seed&amp;save?u='.$url.'&amp;h="><img title="newsvine" border="0" src="modules/mod_nice_social_bookmark/icons/newsvine_'.$iset.'_'.$isize.'.png" alt="Newsvine" /></a>';
$tt = $params->get('s10', 'yes');
if ($tt == "yes")echo '<a id="l10" target="_blank" rel="nofollow" href="http://technorati.com/faves?add='.$url.'&amp;title="><img title="technorati" border="0" src="modules/mod_nice_social_bookmark/icons/technorati_'.$iset.'_'.$isize.'.png" alt="Technorati" /></a>';
$tt = $params->get('s11', 'yes');
if ($tt == "yes"){ if ($lilink == "")
echo '<a id="l11" target="_blank" rel="nofollow" href="http://www.linkedin.com/shareArticle?mini=true&amp;url='.$url.'&amp;summary=%5B..%5D&amp;source="><img title="linkedin" border="0" src="modules/mod_nice_social_bookmark/icons/linkedin_'.$iset.'_'.$isize.'.png" alt="Linkedin" /></a>';
else echo '<a id="ll1" target="_blank" rel="nofollow" href="http://'.$lilink.'"><img title="LinkedIn" border="0" src="modules/mod_nice_social_bookmark/icons/linkedin_'.$iset.'_'.$isize.'.png" alt="LinkedIn" /></a>';}
$tt = $params->get('s12', 'yes');
if ($tt == "yes")echo '<a id="l12" target="_blank" rel="nofollow" href="http://www.mixx.com/submit?page_url='.$url.'&amp;title="><img title="Mixx" border="0" src="modules/mod_nice_social_bookmark/icons/mixx_'.$iset.'_'.$isize.'.png" alt="Mixx" /></a>';
$tt = $params->get('s14', 'yes');
if ($tt == "yes"){if ($rsslink == "")
echo '<a id="l8" target="_blank" rel="nofollow" href="'.$rss_url.'/index.php?format=feed&amp;type=rss&amp;title="><img title="RSS Feed" border="0" src="modules/mod_nice_social_bookmark/icons/rss_'.$iset.'_'.$isize.'.png" alt="RSS Feed" /></a>';
else echo '<a id="l14" target="_blank" rel="nofollow" href="'.$rsslink.'"><img title="RSS Feed" border="0" src="modules/mod_nice_social_bookmark/icons/rss_'.$iset.'_'.$isize.'.png" alt="RSS Feed" /></a>';}
echo '</div><div style="clear:both;"></div>';
?>