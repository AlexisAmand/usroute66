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
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

class EasybookReloadedHelperContent
{
	function parse($message)
	{
		global $mainframe;
		$ebconfig = &$mainframe->getParams();

		// Convert CR and LF to HTML BR command
		$message = preg_replace("/(\015\012)|(\015)|(\012)/","<br />", $message);

		if ($ebconfig->get('support_smilie', true))
		{
			EasybookReloadedHelperContent::replaceSmilies($message);
		}

		if ($ebconfig->get('support_bbcode', true))
		{
			EasybookReloadedHelperContent::convertBbCode($message, $ebconfig);
		}

		if ($ebconfig->get('wordwrap', true))
		{
			EasybookReloadedHelperContent::wordwrap($message);
		}

		return $message;
	}

	// Lange Wörter kürzen
	function wordwrap(&$message)
	{
		global $mainframe;
		$ebconfig = &$mainframe->getParams();
		$size = $ebconfig->get('maxlength', 75);
		$words = explode(" ", $message);
		$anzahl = count($words);
		$message = NULL;

		for ($i=0; $i<$anzahl; $i++)
		{
			if (strlen($words[$i]) > $size)
			{
				$words[$i] = wordwrap($words[$i], $size, "\n", 1);
			}
			$message = $message . " " . $words[$i];
		}
	}

	// BBCode konvertieren
	function convertBbCode(&$message, $ebconfig)
	{
		$message = preg_replace("#\[quote\](.*?)\[/quote]#si", "<strong>Quote:</strong><hr /><blockquote>\\1</blockquote><hr />", $message);
		$message = preg_replace("#\[b\](.*?)\[/b\]#si", "<strong>\\1</strong>", $message);
		$message = preg_replace("#\[i\](.*?)\[/i\]#si", "<i>\\1</i>", $message);
		$message = preg_replace("#\[u\](.*?)\[/u\]#si", "<u>\\1</u>", $message);

		if ($ebconfig->get('support_link', false))
		{
			$message = preg_replace("#\[url=(http://)?(.*?)\](.*?)\[/url\]#si", "<a href=\"http://\\2\" title=\"\\3\" rel=\"nofollow\" target=\"_blank\">\\3</a>", $message);
		}

		if ($ebconfig->get('support_code', false))
		{
			$message = preg_replace("#\[code=?(.*?)\](<br />)*(.*?)(<br />)*\[/code\]#si", "<pre xml:\\1>\\3</pre>", $message);
			// Geshi Code-Hervorhebung - 2.0.6
			$regex = "#<pre xml:s*(.*?)>(.*?)</pre>#s";
			$message = preg_replace_callback( $regex, array('EasybookReloadedHelperContent', 'geshi_replacer'), $message );
		}

		if ($ebconfig->get('support_mail', true))
		{
			if (preg_match_all("#\[email\](.*?)\[/email\]#si", $message, $treffer))
			{
				foreach ($treffer[1] as $wert)
				{
					$message = preg_replace("#\[email\](.*?)\[/email\]#si", JHtml::_('email.cloak', $wert), $message);
				}
			}
		}

		if ($ebconfig->get('support_pic', false))
		{
			$message = preg_replace("#\[img\](.*)\[/img\]#siU", "<img src=\"\\1\" alt=\"\\1\"  title=\"\\1\" />", $message);
			$message = preg_replace("#\[imglink=(http://)?(.*?)\](.*?)\[/imglink\]#si", "<a href=\"http://\\2\" title=\"\\2\" rel=\"nofollow\" target=\"_blank\"><img src=\"\\3\" alt=\"\\3\" /></a>", $message);
		}

		$matchCount = preg_match_all("#\[list\](.*?)\[/list\]#si", $message, $matches);
		for ($i = 0; $i < $matchCount; $i++)
		{
			$currMatchTextBefore = preg_quote($matches[1][$i]);
			$currMatchTextAfter = preg_replace("#\[\*\]#si", "<li>", $matches[1][$i]);
			$message = preg_replace("#\[list\]$currMatchTextBefore\[/list\]#si", "<ul>$currMatchTextAfter</ul>", $message);
		}

		$matchCount = preg_match_all("#\[list=([a1])\](.*?)\[/list\]#si", $message, $matches);
		for ($i = 0; $i < $matchCount; $i++)
		{
			$currMatchTextBefore = preg_quote($matches[2][$i]);
			$currMatchTextAfter = preg_replace("#\[\*\]#si", "<li>", $matches[2][$i]);
			$message = preg_replace("#\[list=([a1])\]$currMatchTextBefore\[/list\]#si", "<ol type=\\1>$currMatchTextAfter</ol>", $message);
		}
	}

	// Smileys konvertieren - keine Smileys in Code
	function replaceSmilies(&$message)
	{
		$mainframe = JFactory::getApplication();
		$params = &$mainframe->getParams();
		$smiley = EasybookReloadedHelperSmilie::getSmilies();

		preg_match_all("#\[code=?(.*?)\](<br />)*(.*?)(<br />)*\[/code\]#si", $message, $matches);

		$i = 0;
		foreach ($matches[0] as $match)
		{
			$message = str_replace($match, '[codetemp'.$i.']', $message);
			$i++;
		}

		foreach ($smiley as $i=>$sm)
		{
			if ($params->get('smilie_set') == 0)
			{
				$message = str_replace ("$i", "<img src='".JURI::base()."components/com_easybookreloaded/images/smilies/$sm' border='0' alt='$i' title='$i' />", $message);
			}
			else
			{
				$message = str_replace ("$i", "<img src='".JURI::base()."components/com_easybookreloaded/images/smilies2/$sm' border='0' alt='$i' title='$i' />", $message);
			}
		}

		$i = 0;
		foreach ($matches[0] as $match)
		{
			$message = str_replace('[codetemp'.$i.']', $match, $message);
			$i++;
		}
	}

	// Modifizierte Funktion aus dem Content-Plugin Geshi
	function geshi_replacer(&$matches)
	{
		jimport('geshi.geshi');
		$mainframe = JFactory::getApplication();
		$params = &$mainframe->getParams('com_easybookreloaded');

		$text = $matches[2];
		$lang = $matches[1];

		if ($lang == "html")
		{
			$lang = "html4strict";
		}
		elseif ($lang == "js")
		{
			$lang = "javascript";
		}

		$html_entities_match = array("|\<br \/\>|", '#&amp;#', "#<#", "#>#", "|&#039;|", '#&quot;#', '#&nbsp;#');
		$html_entities_replace = array("\n", '&', '&lt;', '&gt;', "'", '"', ' ');
		$text = preg_replace( $html_entities_match, $html_entities_replace, $text );

		$text = str_replace('&lt;', '<', $text);
		$text = str_replace('&gt;', '>', $text);
		$text = str_replace("\t", '  ', $text);

		$geshi = new GeSHi($text, $lang);
		$geshi->enable_keyword_links(false);

		if ($params->get('geshi_lines') == 1)
		{
			$geshi->enable_line_numbers( GESHI_NORMAL_LINE_NUMBERS );
		}

		$text = $geshi->parse_code();

		return $text;
	}
}
?>