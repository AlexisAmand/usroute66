<?php
/**
 * @package      Module Google +1 Button for Joomla! 1.5
 * @author       PLAVEB Corporation
 * @copyright    Copyright (C) 2011 PLAVEB Corporation. All rights reserved. 
 * @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
  */

// no direct access

defined( "_JEXEC" ) or die;?>
<?php
$url 	= $params->get("url");
$size 	= $params->get("size");
$lang 	= $params->get("Locale");
$count 	= $params->get("count");
$class  = $params->get("Suffix");
?>

<script type="text/javascript" src="http://apis.google.com/js/plusone.js">
{lang: '<?php echo $lang?>'}
</script>
<div class="<?php echo $class;?>" style="color:#999;margin-bottom:3px;font-size:11px; float:left; margin:5px; width:98px;">
<g:plusone size="<?php echo htmlspecialchars($size); ?>" href="<?php echo htmlspecialchars($url);?>" count="<?php echo $count;?>">
</g:plusone><br />
<span style="color:#999; font-size:8px; text-decoration:none; font-family:Arial, Helvetica, sans-serif">By</span> <a href="http://www.plaveb.com" target="_blank" style="color:#999; font-size:8px; font-family:Arial, Helvetica, sans-serif; text-decoration:none;">PLAVEB</a>
</div>






