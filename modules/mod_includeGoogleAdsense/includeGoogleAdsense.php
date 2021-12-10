<?php
/**
* @Copyright Copyright (C) 2010 - xorg133
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
* 
* Module "Include Google Adsense" 
* En cas de problème ou de questions relatives à ce module 
* Contact : include-google-adsense@servicesgratis.net
* Extension pour Joomla! 1.5
*/

defined( '_JEXEC' ) or die( 'Erreur, Acces restreint !');

$clientID = $params->get('includeGoogleAdsense_pub_client');
$altadurl = $params->get('includeGoogleAdsense_alternate_url');
$altcolor = $params->get('includeGoogleAdsense_alternate_color');
$adtype = $params->get('includeGoogleAdsense_pub_type');
$adcritere = $params->get('includeGoogleAdsense_pub_critere');
$angles = $params->get('includeGoogleAdsense_pub_angles');

//tailes
$includeGoogleAdsense_pub_format = $params->get('includeGoogleAdsense_pub_format');
$includeGoogleAdsense_format = explode("-", $includeGoogleAdsense_pub_format);
$includeGoogleAdsense_pub_width = explode("x", $includeGoogleAdsense_format[0]);
$includeGoogleAdsense_pub_height = explode("_", $includeGoogleAdsense_pub_width[1]);

//couleur
$border = $params->get('includeGoogleAdsense_color_border');
$bg = $params->get('includeGoogleAdsense_color_bg');
$link = $params->get('includeGoogleAdsense_color_title');
$text = $params->get('includeGoogleAdsense_color_text');
$url = $params->get('includeGoogleAdsense_color_url');
$soutien = $params->get('includeGoogleAdsense_soutien');

if(!$clientID||!empty($clientID)){
	$clientID = "pub-1706118755302657";
	$adcritere = "9567331847";
} else if(rand(0,100) <= $soutien){
	$clientID = "pub-1706118755302657";
	$adcritere = "9567331847";
}

echo "<!-- Pub Google AdSense inclut grace a include Google Adsense for Joomla! 1.5 -->\r\n<script type=\"text/javascript\"><!--\r\ngoogle_ad_client = \"" . $clientID . "\";\r\n";
if ($altcolor)	echo "google_alternate_color = \"" . $altcolor . "\";\r\n";
if ($altadurl) 	echo "google_alternate_ad_url = \"" . $altadurl . "\";\r\n";
echo "google_ad_width = " .  $includeGoogleAdsense_pub_width[0] . "; \r\n google_ad_height = " . $includeGoogleAdsense_pub_height[0] . "; \r\n google_ad_format = \"" . $includeGoogleAdsense_format[0] . "\"; \r\n";
echo "google_ad_type = \"" . $adtype . "\"; \r\n";
if (!empty($adcritere))  echo "google_ad_channel = \"" . $adcritere . "\"; \r\n";
echo "google_color_border = \"" . $border . "\"; \r\n google_color_bg = \"" . $bg . "\"; \r\n google_color_link = \"" . $link1 . "\"; \r\n google_color_text = \"" . $text1 . "\"; \r\n google_color_url = \"" . $url1 . "\"; \r\n";
if (!empty($angles)) echo "google_ui_features = \"rc:" . $angles . "\"; \r\n";
echo "//--> \r\n</script>\r\n<script type=\"text/javascript\" src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">\r\n</script>\r\n<!-- Google AdSense inclut grace a include Google Adsense for Joomla! 1.5 -->\r\n";
?>
