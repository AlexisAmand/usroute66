<?php
/**
 * @package      Module Google +1 Button for Joomla! 1.5
 * @author       PLAVEB Corporation
 * @copyright    Copyright (C) 2011 PLAVEB Corporation. All rights reserved. 
 * @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
  */

// no direct access
defined( "_JEXEC" ) or die;
if($params->get("AutoLang", 0)) {
    $lang = JFactory::getLanguage();
    $locale = $lang->getTag();
    $locale = str_replace("-","_",$locale);
} else {
    $locale = $params->get("Locale", "en-US");
}

require(JModuleHelper::getLayoutPath('mod_google_plus'));