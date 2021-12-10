<?php
/*
 * @version		1.5.3 (October 2011)
 * @package		Joomla.Plugin
 * @subpackage	ValAddThis - Content Plugin (valaddthis)
 * @author		Chrysovalantis Mochlas (valandis@valandis.de)
 * @copyright	Copyright (C) 2010-2011 by Chrysovalantis Mochlas (http://www.valandis.de)
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// load Joomla's library files
jimport('joomla.plugin.plugin');
jimport('joomla.version');

class plgContentValAddThis extends JPlugin{

	//------------------------BEGIN--> ValAddThis plugin initialization
	public function plgContentValAddThis(&$subject, $config)
	{
		parent::__construct($subject, $config);
		
		// get Joomla's version
		$version = new JVersion;
		$this->joomla = substr($version->getShortVersion(),0,3);
		
		// get document
		$this->doc =& JFactory::getDocument();

		/*
		 * get plugin's parameters
		 */
		// in J1.5 retrieve the Plugin and JParameter object
		if ($this->joomla == "1.5") {
			$this->_plugin = JPluginHelper::getPlugin('content', 'valaddthis');
			$params = new JParameter($this->_plugin->params);
		} else {
		// in J1.6 - J1.7 load the language files
			$this->loadLanguage();
		}
		// put parameter names in an array
		$valParams = array("plugin_class", "addthis_pub", "secure_server", "addthis_type", "addthis_type_cat", "addthis_type_sec", "addthis_type_front", "addthis_position", "show_cat", "show_sec", "show_front", "filter_art", "filter_cat", "filter_sec", "html_before", "html_after", "services_compact", "services_expanded", "services_exclude", "ui_click", "ui_use_addressbook", "data_track_linkback", "data_use_flash", "data_use_cookies", "ui_use_css", "ui_header_color", "ui_header_background", "ui_offset_top", "ui_offset_left", "ui_delay", "ui_hover_direction", "ui_cobrand", "ui_use_embeddable_services_beta", "data_ga_tracker", "ga_tracker_object", "ui_language", "text_share_caption", "text_email_caption", "text_email", "text_favorites", "text_more", "button_type", "custom_choice", "addthis_button", "custom_button", "custom_text", "text_style", "alt_text", "rssfeed_url", "quick_toolbox", "toolbox_services", "use_text_flag", "toolbox_style", "toolbox_width", "toolbox_sharetext", "toolbox_shareurl", "use_more_flag", "toolbox_more", "toolbox_separator", "use_nofollow", "tooltip_text");
        // fill array with parameter values
		foreach ($valParams as $key => $value) {
			if ($this->joomla == "1.5") {
				$this->_params[$value] = $params->get($value); // case J1.5
			} else {
				$this->_params[$value] = $this->params->def($value); // case J1.6 or J1.7
			}
		}
	}
	//------------------------END--> ValAddThis plugin initialization
	
	//------------------------BEGIN--> function: Prepare content for ValAddThis
	/*
	 * Joomla 1.6 - 1.7 listening event code
	 */
	public function onContentBeforeDisplay( $item, &$article, &$params, $limitstart = 0 )
	{
		$result = $this->onPrepareContent( $article, $params, $limitstart );
	}

	/*
	 * Joomla 1.5 listening event code
	 */
	public function onPrepareContent( &$article, &$params, $limitstart )
	{
		/*
		 * define the regular expression for activating the plugin
		 */
		$regex = "#{valaddthis}(.*?){/valaddthis}#s";
		
		$this->_article = $article; // make article object global
		$this->_changeCount = array(); // define array for changing counter button ids
		$this->count = 0; // reset count of AddThis counter buttons
		
		/*
		 * replace valaddthis tags (if present they override the plugin configuration too)
		 */
		if ((isset($article->text)) and (strpos($article->text, "{valaddthis}") == true)) {
			$article->text = preg_replace_callback($regex, array($this,"replaceTags"), $article->text);
		} elseif ((isset($article->introtext)) and (strpos($article->introtext, "{valaddthis}") == true)) {
			$article->introtext = preg_replace_callback($regex, array($this,"replaceTags"), $article->introtext);
		} else {
			
			/*
			 * define arrays with filtered views
			 */
			$this->_params["filter_art"] = trim($this->_params["filter_art"]); // wipe out spaces
			$this->_params["filter_art"] = str_replace(" ", "", $this->_params["filter_art"]);
			$filter_artArray = explode(",", $this->_params["filter_art"]); // array with excluded articles
			
			$this->_params["filter_cat"] = trim($this->_params["filter_cat"]); // wipe out spaces
			$this->_params["filter_cat"] = str_replace(" ", "", $this->_params["filter_cat"]);
			$filter_catArray = explode(",", $this->_params["filter_cat"]); // array with excluded categories
			
			$this->_params["filter_sec"] = trim($this->_params["filter_sec"]); // wipe out spaces
			$this->_params["filter_sec"] = str_replace(" ", "", $this->_params["filter_sec"]);
			$filter_secArray = explode(",", $this->_params["filter_sec"]); // array with excluded sections
			
			// default display variable
			$display = false;
			
			// get Joomla's view variable
			$this->_currentView = JRequest :: getVar('view');
			
			/*
			 * display plugin according to exclusion lists and Joomla version
			 *
			 *
			 * ------------------------------------in case of J1.5
			 */
			if ($this->joomla == "1.5") {
				/*
				 *
				 * Case frontpage layout
				 */
				if ($this->_currentView == "frontpage") { // case frontpage view
					if ($this->_params["show_front"] != "1") { // only if frontpage display is enabled
						if ($this->_params["filter_art"] != "" or $this->_params["filter_cat"] != "" or $this->_params["filter_sec"] != "") {
							if (!in_array($article->catid, $filter_catArray) and !in_array($article->sectionid, $filter_secArray) and !in_array($article->id, $filter_artArray)) { // display plugin in article if its ID or category and section it belongs to are not excluded
								$display = true;
							}
						} else {
							$display = true;
						}
					}
				/*
				 *
				 * Case section layout
				 */
				} elseif ($this->_currentView == "section" and $article->sectionid != "") { // case section view
					if ($this->_params["show_sec"] != "1") { // only if section display is enabled
						if ($this->_params["filter_art"] != "" or $this->_params["filter_cat"] != "" or $this->_params["filter_sec"] != "") {
							if (!in_array($article->catid, $filter_catArray) and !in_array($article->sectionid, $filter_secArray) and !in_array($article->id, $filter_artArray)) { // display plugin in article if section it belongs to is not excluded
								$display = true;
							}
						} else {
							$display = true;
						}
					}
				/*
				 *
				 * Case category layout
				 */
				} elseif ($this->_currentView == "category" and $article->catid != "") { // case category view
					if ($this->_params["show_cat"] != "1") { // only if category display is enabled
						if ($this->_params["filter_art"] != "" or $this->_params["filter_cat"] != "" or $this->_params["filter_sec"] != "") {
							if (!in_array($article->catid, $filter_catArray) and !in_array($article->sectionid, $filter_secArray) and !in_array($article->id, $filter_artArray)) { // display plugin in article if category and section it belongs to are not excluded
								$display = true;
							}
						} else {
							$display = true;
						}
					}
				/*
				 *
				 * Case article layout
				 */
				} elseif ($this->_currentView == "article") { // case article view
					if ($this->_params["filter_art"] != "" or $this->_params["filter_cat"] != "" or $this->_params["filter_sec"] != "") {
						if (!in_array($article->catid, $filter_catArray) and !in_array($article->sectionid, $filter_secArray) and !in_array($article->id, $filter_artArray)) { // display plugin in article if its ID or category and section it belongs to are not excluded
							$display = true;
						}
					} else {
						$display = true;
					}
				}
			
			/*
			 *
			 * ------------------------------------in case of J1.6 and J1.7
			 */
			} else {
				/*
				 *
				 * Case featured layout (frontpage)
				 */
				if (($this->_currentView == "featured") and ($this->_params["show_front"] == "0")) {
					if ($this->_params["filter_art"] != "" or $this->_params["filter_cat"] != "") {
						if (!in_array($article->catid, $filter_catArray) and !in_array($article->id, $filter_artArray)) { // display plugin in article if its ID or category it belongs to are not excluded
							$display = true;
						}
					} else {
						$display = true;
					}
				}
				/*
				 *
				 * Case category layout
				 */
				if ($this->_currentView == "category" and $article->catid != "") { // case category view
					if ($this->_params["show_cat"] != "1") { // only if category display is enabled
						if ($this->_params["filter_art"] != "" or $this->_params["filter_cat"] != "") {
							if (!in_array($article->catid, $filter_catArray) and !in_array($article->id, $filter_artArray)) { // display plugin in article if category it belongs to is not excluded
								$display = true;
							}
						} else {
							$display = true;
						}
					}
				}
				/*
				 *
				 * Case article layout
				 */
				if ($this->_currentView == "article") { // case article view
					if ($this->_params["filter_art"] != "" or $this->_params["filter_cat"] != "") {
						if (!in_array($article->catid, $filter_catArray) and !in_array($article->id, $filter_artArray)) { // display plugin in article if its ID or category it belongs to are not excluded
							$display = true;
						}
					} else {
						$display = true;
					}
				}
			}
		
			/*
			 *
			 * display plugin if appropriate
			 */
			if ($display == true) {
				$plugincode = $this->renderAddThis(); // get plugin code to render
				if ($this->_params["addthis_position"] == "0") { // position plugin on top of the article
					if (isset($article->text)) {
						$article->text = $plugincode.$article->text;
					} elseif (isset($article->introtext)) {
						$article->introtext = $plugincode.$article->introtext;
					}
				} elseif ($this->_params["addthis_position"] == "1") { // position plugin on the bottom of the article
					if (isset($article->text)) {
						$article->text .= $plugincode;
					} elseif (isset($article->introtext)) {
						$article->introtext .= $plugincode;
					}
				} else { // position plugin both on the top and the bottom of the article
					if (isset($article->text)) {
						$article->text = $plugincode.$article->text;
					} elseif (isset($article->introtext)) {
						$article->introtext = $plugincode.$article->introtext;
					}
					// replace counter button ids if appropriate
					for ($row = 0; $row < $this->count; $row++) {
						$plugincode = str_replace($this->_changeCount[$row][0], $this->_changeCount[$row][1], $plugincode);
					}
					if (isset($article->text)) {
						$article->text .= $plugincode;
					} elseif (isset($article->introtext)) {
						$article->introtext .= $plugincode;
					}
				}
			}
		}
		
		return true;
	}
	//------------------------END--> function: Prepare content for ValAddThis
	
	//------------------------BEGIN--> function: Replace plugin tags
	public function replaceTags(&$matches) {
		
		// force joomla layout to be "article"
		$this->_currentView = "article";
		
		// get configuration parameters (parameter=value)
		$tags = explode("|", trim($matches[1]));
		
		/*
		 * ---------check parameters and their values
		 */
		foreach ($tags as $tag) {
			$var = explode("=", $tag); // split parameter and value
			
			if (isset($var[0]) and isset($var[1])) { // continue if any parameters have been set otherwise use default values
				$param = $var[0]; // parameter name
				$value = $var[1]; // parameter value
				
				switch ($param) { // check for valid parameters (they override plugin configuration)
				case "type": // AddThis type
					if ($value == "button") {
						$this->_params["addthis_type"] = 0;
					} elseif ($value == "toolbox") {
						$this->_params["addthis_type"] = 1;
					}
					break;
				case "button_type": // Button type
					if ($value == "default") {
						$this->_params["button_type"] = 0;
					} elseif ($value == "rss") {
						$this->_params["button_type"] = 1;
					} elseif ($value == "email") {
						$this->_params["button_type"] = 2;
					}
					break;
				case "html_before": // HTML code before plugin
					$value = str_replace(">>","=",$value); // replace >> inside HTML with =
					$this->_params["html_before"] = $value;
					break;
				case "html_after": // HTML code after plugin
					$value = str_replace(">>","=",$value); // replace >> inside HTML with =
					$this->_params["html_after"] = $value;
					break;
				case "button_img": // Button standard image
					if ($value == "addthis-long") {
						$this->_params["addthis_button"] = 0;
					} elseif ($value == "addthis") {
						$this->_params["addthis_button"] = 1;
					} elseif ($value == "bm-long") {
						$this->_params["addthis_button"] = 2;
					} elseif ($value == "bm") {
						$this->_params["addthis_button"] = 3;
					} elseif ($value == "share-long") {
						$this->_params["addthis_button"] = 4;
					} elseif ($value == "share") {
						$this->_params["addthis_button"] = 5;
					} elseif ($value == "plus") {
						$this->_params["addthis_button"] = 6;
					} elseif ($value == "rss-feed-big") {
						$this->_params["addthis_button"] = 7;
					} elseif ($value == "rss-feed-long") {
						$this->_params["addthis_button"] = 8;
					} elseif ($value == "rss-feed") {
						$this->_params["addthis_button"] = 9;
					} elseif ($value == "subscribe-big") {
						$this->_params["addthis_button"] = 10;
					} elseif ($value == "subscribe-long") {
						$this->_params["addthis_button"] = 11;
					} elseif ($value == "subscribe") {
						$this->_params["addthis_button"] = 12;
					} elseif ($value == "email") {
						$this->_params["addthis_button"] = 13;
					} elseif ($value == "counter") {
						$this->_params["addthis_button"] = 14;
					}
					break;
				case "lang": // UI language
					$this->_params["ui_language"] = $value;
					break;
				case "share_caption": // Text for "Bookmark & Share"
					$this->_params["text_share_caption"] = $value;
					break;
				case "email_caption": // Text for "Email a Friend"
					$this->_params["text_email_caption"] = $value;
					break;
				case "email": // Text for "Email"
					$this->_params["text_email"] = $value;
					break;
				case "favorites": // Text for "Favorites"
					$this->_params["text_favorites"] = $value;
					break;
				case "more": // Text for "More"
					$this->_params["text_more"] = $value;
					break;
				case "compact": // Compact menu services
					$this->_params["services_compact"] = $value;
					break;
				case "expanded": // Expanded menu services
					$this->_params["services_expanded"] = $value;
					break;
				case "exclude": // Menu services to exclude
					$this->_params["services_exclude"] = $value;
					break;
				case "tool_quick": // Quick Toolbox
					$this->_params["quick_toolbox"] = $value;
					break;
				case "tool_services": // Toolbox services
					$this->_params["toolbox_services"] = $value;
					break;
				case "tool_style": // Toolbox style
					if ($value == "default") {
						$this->_params["toolbox_style"] = "default";
					} elseif ($value == "vertical") {
						$this->_params["toolbox_style"] = "vertical";
					} elseif ($value == "default32") {
						$this->_params["toolbox_style"] = "default32";
					} elseif ($value == "vertical32") {
						$this->_params["toolbox_style"] = "vertical32";
					} elseif ($value == "css-hor") {
						$this->_params["toolbox_style"] = "cssHorizontal";
					} elseif ($value == "css-vert1") {
						$this->_params["toolbox_style"] = "cssVertical1";
					} elseif ($value == "css-vert2") {
						$this->_params["toolbox_style"] = "cssVertical2";
					} elseif ($value == "css-user") {
						$this->_params["toolbox_style"] = "cssUser";
					}
					break;
				case "tool_width": // Toolbox width
					$this->_params["toolbox_width"] = $value;
					break;
				case "tool_share": // Toolbox share (alt) text
					$this->_params["toolbox_sharetext"] = $value;
					break;
				case "tool_shareurl": // Toolbox share graphic URL
					$this->_params["toolbox_shareurl"] = $value;
					break;
				case "tool_show_names": // Toolbox display service names
					if ($value == "yes") {
						$this->_params["use_text_flag"] = 1;
					} elseif ($value == "no") {
						$this->_params["use_text_flag"] = 0;
					}
					break;
				case "tool_show_more": // Toolbox display "More" icon
					if ($value == "yes") {
						$this->_params["use_more_flag"] = 1;
					} elseif ($value == "no") {
						$this->_params["use_more_flag"] = 0;
					}
					break;
				case "tool_more": // Toolbox text for "More"
					$this->_params["toolbox_more"] = $value;
					break;
				case "tooltip": // Toolbox text for "Send to" tooltip
					$this->_params["tooltip_text"] = $value;
					break;
				case "tool_separator": // Toolbox text for separator (pipe for |)
					if ($value == "pipe") {
						$this->_params["toolbox_separator"] = "|";
					} else {
						$this->_params["toolbox_separator"] = $value;
					}
					break;
				case "swfurl": // URL of a Flash object to share, along with the link
					$this->_swf_string .= "swfurl: ".$value;
					break;
				case "swf_width": // Ideal width of any provided Flash object
					if ($this->_swf_string != "") {
						$this->_swf_string .= ", ";
					}
					$this->_swf_string .= "width: ".$value;
					break;
				case "swf_height": // Ideal height of any provided Flash object
					if ($this->_swf_string != "") {
						$this->_swf_string .= ", ";
					}
					$this->_swf_string .= "height: ".$value;
					break;
				case "screenshot": // The URL of an image that shows a preview of the content being shared
					if ($this->_swf_string != "") {
						$this->_swf_string .= ", ";
					}
					$this->_swf_string .= "screenshot: ".$value;
					break;
				}
			}
		}
		return $this->renderAddThis(); // return rendered HTML code
	}
	//------------------------END--> function: Replace plugin tags
	
	//------------------------BEGIN--> function: Render HTML for ValAddThis
	public function renderAddThis() {
		
		/*
		 * ------determine the type of AddThis button to be displayed according to joomla's layout
		 */
		//-----case article layout
		if ($this->_currentView == "article" and $this->_params["addthis_type"] == 0) {
			$display_button = true;
		//-----case category layout
		} elseif ($this->_currentView == "category" and $this->_params["addthis_type_cat"] == 0) {
			$display_button = true;
		//-----case section layout
		} elseif ($this->_currentView == "section" and $this->_params["addthis_type_sec"] == 0) {
			$display_button = true;
		//-----case frontpage/featured layout
		} elseif (($this->_currentView == "frontpage" or $this->_currentView == "featured") and $this->_params["addthis_type_front"] == 0) {
			$display_button = true;
		//-----otherwise display a toolbox
		} else {
			$display_button = false;
		}
		
		if ($display_button == true) { // case AddThis Button, initialize images
			/*
			 * -------define array with images links and dimensions for AddThis button
			 *
			 *
			 * determine path to images according to Joomla version
			 */
			if ($this->joomla != "1.6" and $this->joomla != "1.7") {
				$path = "plugins/content/valaddthis/images/"; // case J1.5
			} else {
				$path = "plugins/content/valaddthis/valaddthis/images/"; // case J1.6 or J1.7
			}
			$button_array[0]["button_link"] = $path."addthis-long.gif";
			$button_array[0]["button_x"] = "125"; $button_array[0]["button_y"] = "16";
			$button_array[1]["button_link"] = $path."addthis-short.gif";
			$button_array[1]["button_x"] = "83"; $button_array[1]["button_y"] = "16";
			$button_array[2]["button_link"] = $path."bm-long.gif";
			$button_array[2]["button_x"] = "125"; $button_array[2]["button_y"] = "16";
			$button_array[3]["button_link"] = $path."bm-short.gif";
			$button_array[3]["button_x"] = "83"; $button_array[3]["button_y"] = "16";
			$button_array[4]["button_link"] = $path."share-long.gif";
			$button_array[4]["button_x"] = "125"; $button_array[4]["button_y"] = "16";
			$button_array[5]["button_link"] = $path."share-short.gif";
			$button_array[5]["button_x"] = "83"; $button_array[5]["button_y"] = "16";
			$button_array[6]["button_link"] = $path."plus-small.gif";
			$button_array[6]["button_x"] = "16"; $button_array[6]["button_y"] = "16";
			$button_array[7]["button_link"] = $path."rss-feed-big.gif";
			$button_array[7]["button_x"] = "160"; $button_array[7]["button_y"] = "24";
			$button_array[8]["button_link"] = $path."rss-feed-long.gif";
			$button_array[8]["button_x"] = "125"; $button_array[8]["button_y"] = "16";
			$button_array[9]["button_link"] = $path."rss-feed-short.gif";
			$button_array[9]["button_x"] = "83"; $button_array[9]["button_y"] = "16";
			$button_array[10]["button_link"] = $path."subscribe-big.gif";
			$button_array[10]["button_x"] = "160"; $button_array[10]["button_y"] = "24";
			$button_array[11]["button_link"] = $path."subscribe-long.gif";
			$button_array[11]["button_x"] = "125"; $button_array[11]["button_y"] = "16";
			$button_array[12]["button_link"] = $path."subscribe-short.gif";
			$button_array[12]["button_x"] = "83"; $button_array[12]["button_y"] = "16";
			$button_array[13]["button_link"] = $path."email-short.gif";
			$button_array[13]["button_x"] = "54"; $button_array[13]["button_y"] = "16";
		
			/*
			 * -----determine the image for the AddThis button and its dimensions
			 */
			if ($this->_params["custom_choice"] == 0) {
				if ($this->_params["addthis_button"] != 14) { // do nothing if it is the AddThis counter button
					$this->_but_link = $button_array[$this->_params["addthis_button"]]["button_link"];
					$this->_but_x = $button_array[$this->_params["addthis_button"]]["button_x"];
					$this->_but_y = $button_array[$this->_params["addthis_button"]]["button_y"];
				}
			} else {
				$this->_but_link = $this->_params["custom_button"];
				//	list($this->_but_x, $this->_but_y) = getimagesize($this->_but_link);
			}
		}
		
		/*
		 * -----get article's URL, title, description and ID
		 */
		$articleURL = urldecode($this->getURL($this->_article));
		$articleTitle = $this->_article->title;
		$articleDesc = $this->_article->metadesc;
		$articleID = $this->_article->id;
		
		/*
		 * -----strip double quotes " and CR/LF from article's title and description
		 */
		$articleTitle = str_replace("\"", "'", $articleTitle); // convert double quotes to single quotes
		$articleDesc = str_replace("\"", "'", $articleDesc);
		
		$crlf   = array("\r\n", "\n", "\r"); // define array with CR/LF chars
		$articleTitle = str_replace($crlf , " ", $articleTitle); // convert CR/LF to spaces
		$articleDesc = str_replace($crlf , " ", $articleDesc);
		
		// create unique class for AddThis according to article's ID
		$addThis_class = "valaddthis_id".$articleID;
		
		$output = "\n<!-- BEGIN: ValAddThis Plugin -->\n";
		
		/*
		 * -----display HTML code before article if it is defined
		 */
		if (isset($this->_params["html_before"])) {
			$output .= $this->_params["html_before"];
		}
		
		/*
		 * -----call the remote AddThis script in the head
		 */
//		$output .= "<script charset=\"utf-8\" type=\"text/javascript\" src=\"";
		if ($this->_params["secure_server"] == 0) { // if plugin is installed on a secure Web-Server
//			$output .= "https://s7.addthis.com/js/250/addthis_widget.js";
			$this->doc->addScript("https://s7.addthis.com/js/250/addthis_widget.js");
		} else { // if plugin is installed on a non-secure Web-Server
//			$output .= "http://s7.addthis.com/js/250/addthis_widget.js";
			$this->doc->addScript("http://s7.addthis.com/js/250/addthis_widget.js");
		}
/*		$output .= "\"></script>\n"; */
		
		if ($this->_params["plugin_class"] != "") { // wrap plugin in a div tag, if a plugin CSS class is defined
			$output .= "<div class=\"{$this->_params["plugin_class"]}\">\n";
		}
		
		$output .= "<script type=\"text/javascript\" language=\"javascript\">\n";
		if (!empty($this->_params["addthis_pub"])) { // AddThis ID
			$output .= "var addthis_pub = \"{$this->_params["addthis_pub"]}\";\n";
		}
		
		/*
		 * -----pass localization variables for AddThis captions
		 */
		if ($this->_params["text_share_caption"] != "" or $this->_params["text_email_caption"] != "" or $this->_params["text_email"] != "" or $this->_params["text_favorites"] != "" or $this->_params["text_more"] != "") {
			$output .= "var addthis_localize = {
				share_caption: \"{$this->_params["text_share_caption"]}\",
				email_caption: \"{$this->_params["text_email_caption"]}\",
				email: \"{$this->_params["text_email"]}\",
				favorites: \"{$this->_params["text_favorites"]}\",
				more: \"{$this->_params["text_more"]}\"
			};\n";
		}
		
		/*
		 * -----pass global congiguration variables to AddThis script
		 */
		$output .= "var addthis_config = {";
		if ($this->_params["ui_language"] != "") { // Default language
			$output .= "ui_language: \"{$this->_ui_language}\",";
		}
		if ($this->_params["services_compact"] != "") { // Displayed services in compact menu
			$output .= "services_compact: \"{$this->_params["services_compact"]}\",";
		}
		if ($this->_params["services_expanded"] != "") { // Displayed services in expanded menu
			$output .= "services_expanded: \"{$this->_params["services_expanded"]}\",";
		}
		if ($this->_params["services_exclude"] != "") { // Excluded services in all menus
			$output .= "services_exclude: \"{$this->_params["services_exclude"]}\",";
		}
		if ($this->_params["ui_header_color"] != "") { // Header color
			$output .= "ui_header_color: \"#{$this->_params["ui_header_color"]}\",";
		}
		if ($this->_params["ui_header_background"] != "") { // Header background color
			$output .= "ui_header_background: \"#{$this->_params["ui_header_background"]}\",";
		}
		if ($this->_params["ui_offset_top"] != "") { // Offset top
			$output .= "ui_offset_top: {$this->_params["ui_offset_top"]},";
		}
		if ($this->_params["ui_offset_left"] != "") { // Offset left
			$output .= "ui_offset_left: {$this->_params["ui_offset_left"]},";
		}
		if ($this->_params["ui_delay"] != "") { // Hover delay
			$output .= "ui_delay: {$this->_params["ui_delay"]},";
		}
		if ($this->_params["ui_cobrand"] != "") { // Brand name
			$output .= "ui_cobrand: \"{$this->_params["ui_cobrand"]}\",";
		}
		if ($display_button == true) { // ui_click will be used only for the AddThis button, not the toolbox
			if ($this->_params["ui_click"] == 1) { // Display upon
				$output .= "ui_click: true,";
			} else {
				$output .= "ui_click: false,";
			}
		}
		if ($this->_params["ui_hover_direction"] == 1) { // Hover direction
			$output .= "ui_hover_direction: true,";
		} else {
			$output .= "ui_hover_direction: false,";
		}
		if ($this->_params["ui_use_addressbook"] == 1) { // Use addressbook
			$output .= "ui_use_addressbook: true,";
		} else {
			$output .= "ui_use_addressbook: false,";
		}
		if ($this->_params["data_track_linkback"] == 1) { // Data track linkback
			$output .= "data_track_linkback: true,";
		} else {
			$output .= "data_track_linkback: false,";
		}
		if ($this->_params["data_use_flash"] == 1) { // Use flash
			$output .= "data_use_flash: true,";
		} else {
			$output .= "data_use_flash: false,";
		}
		if ($this->_params["data_use_cookies"] == 1) { // Use cookies
			$output .= "data_use_cookies: true,";
		} else {
			$output .= "data_use_cookies: false,";
		}
		if ($this->_params["ui_use_css"] == 1) { // Use AddThis CSS
			$output .= "ui_use_css: true,";
		} else {
			$output .= "ui_use_css: false,";
		}
		if ($this->_params["ui_use_embeddable_services_beta"] == 1) { // Use embeddable-only services
			$output .= "ui_use_embeddable_services_beta: true";
		} else {
			$output .= "ui_use_embeddable_services_beta: false";
		}
		if ($this->_params["data_ga_tracker"] == 1) { // Use GA tracking object
			if ($this->_params["ga_tracker_object"] != "") { // User-defined object name
				$output .= ",data_ga_tracker: ".$this->_params["ga_tracker_object"];
			} else {
				$output .= ",data_ga_tracker: pageTracker"; // Use default object name
			}
		}
		$output .= "}; </script>\n";

		/*
		 * ---------------------------case it's an AddThis Button
		 *
		 */
		if ($display_button == true) {
			/*
			 * -----standard bookmarking services button
			 */
			if ($this->_params["button_type"] == 0) {
				if ($this->_params["addthis_button"] != 14) {
					$output .= "<a class=\"{$addThis_class}\" ";
				} else { // case it's the share counter button
					$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
					$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
					$output .= "<a class=\"addthis_pill_style\" id=\"{$addThis_class}_$this->count\" ";
				}
				/*
				 * -----user-defined text style
				 */
				if (!empty($this->_params["text_style"])) {
					$output .= "style=\"{$this->_params["text_style"]}\" ";
				}
				$output .= ">";
				/*
				 * -----standard image button
				 */
				if ($this->_params["custom_choice"] == 0) {
					if ($this->_params["addthis_button"] != 14) {
						$output .= "<img src=\"{$this->_but_link}\" width=\"{$this->_but_x}\" height=\"{$this->_but_y}\" border=\"0\" alt=\"{$this->_params["alt_text"]}\" />\n";
					}
				}
				/*
				 * -----user-defined image button
				 */
				if ($this->_params["custom_choice"] == 1 or $this->_params["custom_choice"] == 2) {
					$output .= "<img src=\"{$this->_but_link}\" border=\"0\" alt=\"{$this->_params["alt_text"]}\" />\n";
				}
				/*
				 * -----text is displayed as a link
				 */
				if ($this->_params["custom_choice"] >= 2) {
					$output .= $this->_params["custom_text"];
				}
				$output .= "</a>\n";

			/*
			 * -----RSS feed services button
			 */
			} elseif ($this->_params["button_type"] == 1) {
				$output .= "<a alt=\"Subscribe using any feed reader!\" href=\"http://www.addthis.com/feed.php?pub={$this->_params["addthis_pub"]}&amp;h1={$this->_params["rssfeed_url"]}&amp;t1=\" ";
				/*
				 * -----user-defined text style
				 */
				if (!empty($this->_params["text_style"])) {
					$output .= "style=\"{$this->_params["text_style"]}\"";
				}
				$output .= "onclick=\"return addthis_open(this, 'feed', '{$this->_params["rssfeed_url"]}', '[TITLE]');\" target=\"_blank\">\n";
				/*
				 * -----image button
				 */
				if ($this->_params["custom_choice"] != 3) {
					$output .= "<img src=\"{$this->_but_link}\" width=\"{$this->_but_x}\" height=\"{$this->_but_y}\" border=\"0\" alt=\"{$this->_params["alt_text"]}\" />\n";
				}
				/*
				 * -----text is displayed as a link
				 */
				if ($this->_params["custom_choice"] >= 2) {
					$output .= $this->_params["custom_text"];
				}
				$output .= "</a>\n";
			
			/*
			 * -----email button
			 */
			} else {
				$output .= "<a class=\"addthis_button_email\" ";
				/*
				 * -----user-defined text style
				 */
				if (!empty($this->_params["text_style"])) {
					$output .= "style=\"{$this->_params["text_style"]}\"";
				}
				$output .= ">";
				/*
				 * -----image button
				 */
				if ($this->_params["custom_choice"] != 3) {
					$output .= "<img src=\"{$this->_but_link}\" width=\"{$this->_but_x}\" height=\"{$this->_but_y}\" border=\"0\" alt=\"{$this->_params["alt_text"]}\" />\n";
				}
				/*
				 * -----text is displayed as a link
				 */
				if ($this->_params["custom_choice"] >= 2) {
					$output .= $this->_params["custom_text"];
				}
				$output .= "</a>\n";
			}
			
			/*
			 * Render AddThis Button using Javascript (unless it's an RSS Feed or Email-only Button)
			 *
			 */
			if ($this->_params["button_type"] == 0) {
				if ($this->_params["addthis_button"] == 14) { // case it's the share counter button
					$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"";
					$this->count++; // next unique id
				} else {
					$output .= "<script type=\"text/javascript\">\n addthis.button(\".{$addThis_class}\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"";
				}
				if (isset($this->_swf_string)) {
					$output .= ", ".$this->_swf_string;
				}
				$output .= "});\n</script>";
			}
			
		/*
		 * ----------------------------------------------case it's an AddThis Toolbox
		 *
		 */
		} else {
			if ($this->_params["quick_toolbox"] != "0") { // case quick toolbox selected
				switch ($this->_params["quick_toolbox"]) { // define services for quick toolbox according to selection
					case "1":
						$this->_params["toolbox_services"] = "google_plusone,googlebuzz_counter,counter_pill";
						break;
					case "2":
						$this->_params["toolbox_services"] = "facebook_like,tweet,counter_pill";
						break;
					case "3":
						$this->_params["toolbox_services"] = "facebook_like,tweet,linkedin_counter,counter_pill";
						break;
					case "4":
						$this->_params["toolbox_services"] = "facebook_like,tweet,googlebuzz_counter,counter_pill";
						break;
					case "5":
						$this->_params["toolbox_services"] = "facebook_like,tweet,stumbleupon_counter,counter_pill";
						break;
					case "6":
						$this->_params["toolbox_services"] = "facebook_like,tweet,googlebuzz_counter,stumbleupon_counter,counter_pill";
						break;
					case "7":
						$this->_params["toolbox_services"] = "facebook_like,tweet,googlebuzz_counter,linkedin_counter,stumbleupon_counter,counter_pill";
						break;
					case "8":
						$this->_params["toolbox_services"] = "google_plusone_medium,facebook_like,tweet,googlebuzz_counter,linkedin_counter,stumbleupon_counter,counter_pill";
						break;
					case "9":
						$this->_params["toolbox_services"] = "tweet,googlebuzz_counter,stumbleupon_counter,counter_pill";
						break;
					case "10":
						$this->_params["toolbox_services"] = "digg_counter,tweet,googlebuzz_counter,counter_pill";
						break;
					case "11":
						$this->_params["toolbox_services"] = "digg_counter,tweet,googlebuzz_counter,stumbleupon_counter,counter_pill";
						break;
					case "12":
						$this->_params["toolbox_services"] = "counter_vertical,google_plusone_tall,stumbleupon_vertical,tweet_vertical,linkedin_vertical,googlebuzz_vertical,digg_vertical,facebook_like_vertical";
						break;
				}
			}
			
			if ($this->_params["use_nofollow"] == 1 ) { // set rel=nofollow attribute if appropriate
				$nofollow = " rel=\"nofollow\"";
			} else {
				$nofollow = "";
			}
			
			if ($this->_params["toolbox_shareurl"] != "") { // prepare share graphic + alt text
				$shareText = "<div class=\"share\"><img src=\"{$this->_params["toolbox_shareurl"]}\" border=\"0\" alt=\"{$this->_params["toolbox_sharetext"]}\" title=\"{$this->_params["toolbox_sharetext"]}\" /></div>\n";
			} else {
				if ($this->_params["toolbox_sharetext"] != "") { // prepare share text
					$shareText = "<div class=\"share\">".$this->_params["toolbox_sharetext"]."&nbsp;</div>";
				} else {
					$shareText = "";
				}
			}
			
			if ($this->_params["toolbox_services"] != "") { // if any services defined
				$toolServices = explode(",", $this->_params["toolbox_services"]); // get CSV list of services
			}
			
			/*
			 * determine path to CSS files according to Joomla version
			 */
			if ($this->joomla != "1.6" and $this->joomla != "1.7") {
				$path = "plugins/content/valaddthis/css/"; // case J1.5
			} else {
				$path = "plugins/content/valaddthis/valaddthis/css/"; // case J1.6 or J1.7
			}
			
			$output .= "<div class=\"addthis_toolbox\">\n"; // wrap Toolbox in a div with CSS class
			
			/*
			 * ------------------------case default/default 32px/vertical/vertical 32px style
			 *
			 */
			if ($this->_params["toolbox_style"] == "default" or $this->_params["toolbox_style"] == "default32" or $this->_params["toolbox_style"] == "vertical" or $this->_params["toolbox_style"] == "vertical32") {
				$output .= "<div class=\"{$addThis_class}";
				if ($this->_params["toolbox_style"] == "default") { // default style
					$output .= " addthis_default_style\"";
				} elseif ($this->_params["toolbox_style"] == "default32") { // default 32px icons style
					$output .= " addthis_default_style addthis_32x32_style\"";
				} elseif ($this->_params["toolbox_style"] == "vertical32") { // vertical 32px icons style
					$output .= " addthis_32x32_style\"";
				} else {
					$output .= "\"";
				}
				if ($this->_params["toolbox_width"] != "") { // case width defined
					$output .= " style=\"width:{$this->_params["toolbox_width"]}\"";
				}
				$output .= ">\n";
				
				if ($this->_params["toolbox_style"] == "default") { // Add reference to the appropriate CSS file
					JHTML::stylesheet("default.css", $path);
				} elseif ($this->_params["toolbox_style"] == "default32") {
					JHTML::stylesheet("default32.css", $path);
				} elseif ($this->_params["toolbox_style"] == "vertical") {
					JHTML::stylesheet("vertical.css", $path);
				} elseif ($this->_params["toolbox_style"] == "vertical32") {
					JHTML::stylesheet("vertical32.css", $path);
				}
				$output .= $shareText; // display share text or share image (with alt share text)
				
				if (isset($toolServices)) { // if any services defined
					foreach ($toolServices as $service) { // display individual service icons
						$service = trim($service); // wipe out spaces
						$title = ucfirst(strtolower($service));
						if ($this->_params["use_text_flag"] == 1) { // display also service name?
							$service_name = "&nbsp;".$title;
						} else {
							$service_name = "";
						}
						if ($this->_params["tooltip_text"] != "") { // case custom tooltip text in place of "Send to"
							$tooltip_text = " title=\"".$this->_params["tooltip_text"]." ".$title."\"";
						} else {
							$tooltip_text = "";
						}
						
						/*
						 * ------------------------Special Counter Buttons----------
						 *
						 */
						//------display Facebook like counter button if appropriate
						if ($service == "facebook_like") {
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=button_count&amp;show_faces=false&amp;width=47&amp;action=like&amp;colorscheme=light&amp;height=21\" scrolling=\"no\" frameborder=\"0\" class=\"FBlike\"></iframe>\n";
						} elseif ($service == "facebook_rec") { // Facebook recommend
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=button_count&amp;show_faces=false&amp;width=47&amp;action=recommend&amp;colorscheme=light&amp;height=21\" scrolling=\"no\" frameborder=\"0\" class=\"FBrec\"></iframe>\n";
						} elseif ($service == "facebook_like_vertical") { // Facebook like vertical counter
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=box_count&amp;show_faces=false&amp;width=47&amp;action=like&amp;colorscheme=light&amp;height=90\" scrolling=\"no\" frameborder=\"0\" class=\"FBlikeB\"></iframe>\n";
						} elseif ($service == "facebook_rec_vertical") { // Facebook recommend vertical counter
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=box_count&amp;show_faces=false&amp;width=47&amp;action=recommend&amp;colorscheme=light&amp;height=90\" scrolling=\"no\" frameborder=\"0\" class=\"FBrecB\"></iframe>\n";
						//------display TweetMeme counter button if appropriate
						} elseif ($service == "tweetmeme") { // horizontal
							$output .= "<script type=\"text/javascript\">tweetmeme_style = 'compact';tweetmeme_url = '{$articleURL}';</script><script type=\"text/javascript\" src=\"http://tweetmeme.com/i/scripts/button.js\"></script>\n";
						} elseif ($service == "tweetmeme_vertical") { // vertical
							$output .= "<div class=\"tweetmeme_vert\"><script type=\"text/javascript\">tweetmeme_url = '{$articleURL}';</script><script type=\"text/javascript\" src=\"http://tweetmeme.com/i/scripts/button.js\"></script></div>\n";
						//------display Google Buzz counter button if appropriate (not valid XHTML)
						} elseif ($service == "googlebuzz_counter") { // horizontal counter
							$this->doc->addScript("http://www.google.com/buzz/api/button.js");
							$output .= "<a title=\"{$tooltip_text}Google Buzz\" class=\"google-buzz-button\" href=\"http://www.google.com/buzz/post\" data-button-style=\"small-count\" data-url=\"{$articleURL}\"></a>\n";
						} elseif ($service == "googlebuzz_vertical") { // vertical counter
							$this->doc->addScript("http://www.google.com/buzz/api/button.js");
							$output .= "<a title=\"{$tooltip_text}Google Buzz\" class=\"google-buzz-button\" href=\"http://www.google.com/buzz/post\" data-button-style=\"normal-count\" data-url=\"{$articleURL}\"></a>\n";
						//------display LinkedIn counter button if appropriate (not valid XHTML)
						} elseif ($service == "linkedin_counter") { // horizontal counter
							$this->doc->addScript("http://platform.linkedin.com/in.js");
							$output .= "<div class=\"linkedin_horz\"><script type=\"in/share\" data-url=\"{$articleURL}\" data-counter=\"right\"></script></div>\n";
						} elseif ($service == "linkedin_vertical") { // vertical counter
							$this->doc->addScript("http://platform.linkedin.com/in.js");
							$output .= "<div class=\"linkedin_vert\"><script type=\"in/share\" data-url=\"{$articleURL}\" data-counter=\"top\"></script></div>\n";
						//------display StumbleUpon counter if appropriate
						} elseif ($service == "stumbleupon_counter") { // horizontal counter
							$output .= "<div class=\"stumbleuponH\"><script type=\"text/javascript\" src=\"http://www.stumbleupon.com/hostedbadge.php?s=2&amp;r={$articleURL}\"></script></div>\n";
						} elseif ($service == "stumbleupon_vertical") { // vertical counter
							$output .= "<div class=\"stumbleuponV\"><script type=\"text/javascript\" src=\"http://www.stumbleupon.com/hostedbadge.php?s=5&amp;r={$articleURL}\"></script></div>\n";
						//------display Digg counter if appropriate
						} elseif ($service == "digg_counter") { // horizontal counter
							$diggURL = urlencode ($articleURL)."&amp;title=".urlencode ($articleTitle);
							$output .= "<script type=\"text/javascript\">(function() {var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];s.type = 'text/javascript';s.async = true;s.src = 'http://widgets.digg.com/buttons.js';s1.parentNode.insertBefore(s, s1);})();</script><a class=\"DiggThisButton DiggCompact\" href=\"{$diggURL}\"><span style=\"display:none\">{$articleDesc}</span></a>\n";
						} elseif ($service == "digg_vertical") { // vertical counter
							$diggURL = urlencode ($articleURL)."&amp;title=".urlencode ($articleTitle);
							$output .= "<script type=\"text/javascript\">(function() {var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];s.type = 'text/javascript';s.async = true;s.src = 'http://widgets.digg.com/buttons.js';s1.parentNode.insertBefore(s, s1);})();</script><a class=\"DiggThisButton DiggMedium\" href=\"{$diggURL}\"><span style=\"display:none\">{$articleDesc}</span></a>\n";
						//------display Google Plus +1 counter if appropriate
						} elseif ($service == "google_plusone_medium") { // medium size counter
							$this->doc->addScript("https://apis.google.com/js/plusone.js");
							$output .= "<![if gt IE 6]><div class=\"plusone\"><div class=\"g-plusone\" data-size=\"medium\" data-count=\"true\" data-href=\"{$articleURL}\"></div></div><![endif]>\n";
						} elseif ($service == "google_plusone_standard") { // standard size counter
							$this->doc->addScript("https://apis.google.com/js/plusone.js");
							$output .= "<![if gt IE 6]><div class=\"plusone\"><div class=\"g-plusone\" data-size=\"standard\" data-count=\"true\" data-href=\"{$articleURL}\"></div></div><![endif]>\n";
						} elseif ($service == "google_plusone_tall") { // vertical (tall) counter
							$this->doc->addScript("https://apis.google.com/js/plusone.js");
							$output .= "<![if gt IE 6]><div class=\"plusone\"><div class=\"g-plusone\" data-size=\"tall\" data-count=\"true\" data-href=\"{$articleURL}\"></div></div><![endif]>\n";
						//------display Tweet vertical counter if appropriate
						} elseif ($service == "tweet_vertical") {
							$this->doc->addScript("http://platform.twitter.com/widgets.js");
							$output .= "<a href=\"http://twitter.com/share\" class=\"twitter-share-button\" data-url=\"{$articleURL}\" data-text=\"{$articleDesc}\" data-count=\"vertical\">Tweet</a>\n";
						//------display AddThis Pill counter button if appropriate
						} elseif ($service == "counter_pill") {
							// prepare array for changing ids in case of double plugin display
							$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
							$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
							$output .= "<a class=\"addthis_pill_style\" id=\"{$addThis_class}_$this->count\"></a>\n";
							$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"});</script>";
							$this->count++; // next unique id
						//------display AddThis Vertical counter button if appropriate
						} elseif ($service == "counter_vertical") {
							$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
							$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
							$output .= "<a class=\"addthis_vertical_style\" id=\"{$addThis_class}_$this->count\"></a>\n";
							$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"});</script>";
							$this->count++; // next unique id
						//------display AddThis Bubble counter button if appropriate
						} elseif ($service == "counter_bubble") {
							$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
							$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
							$output .= "<a class=\"addthis_bubble_style\" id=\"{$addThis_class}_$this->count\"></a>\n";
							$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"});</script>";
							$this->count++; // next unique id
						} else {
							$output .= "<a class=\"addthis_button_{$service}\"{$nofollow}{$tooltip_text}>{$service_name}</a>\n";
						}
					}
				}
				
				if ($this->_params["use_more_flag"] == "1") { // case "more" is used
					if ($this->_params["toolbox_style"] == "default") { // if default style check for separator
						$output .= "<span class=\"addthis_separator\">".$this->_params["toolbox_separator"]."</span>\n";
					}

					$output .= "<a class=\"addthis_button_expanded\"".($this->_params["toolbox_more"] != "" ? " title=\"{$this->_params["toolbox_more"]}\">{$this->_params["toolbox_more"]}" : ">")."</a>\n"; // "more" icon + optional text
				}
			} 
			
			/*
			 * ------------------------case CSS horizontal/CSS vertical #1/CSS user-defined style
			 *
			 */
			if ($this->_params["toolbox_style"] == "cssHorizontal" or $this->_params["toolbox_style"] == "cssVertical1" or $this->_params["toolbox_style"] == "cssUser") {
				// Add reference to CSS file and set CSS class for Toolbox
				if ($this->_params["toolbox_style"] == "cssHorizontal") {
					JHTML::stylesheet("horizontal.css", $path);
					$toolbox_class = "addHoriz";
					$spacer_char = "&nbsp;"; // set spacer character for share text
				} elseif ($this->_params["toolbox_style"] == "cssVertical1") {

					JHTML::stylesheet("vertical1.css", $path);
					$toolbox_class = "addVertical1";
					$spacer_char = "";
				} else {
					JHTML::stylesheet("user.css", $path);
					$toolbox_class = "addUser";
					$spacer_char = "&nbsp;"; // set spacer character for share text
				}
				$output .= "<div class=\"{$addThis_class}\"><div class=\"{$toolbox_class}\"".($this->_params["toolbox_width"] != "" ? " style=\"width:{$this->_params["toolbox_width"]}\"" : "").">\n";
				
				$output .= $shareText; // display share text
				
				if (isset($toolServices)) { // if any services defined
					foreach ($toolServices as $service) { // display individual service icons
						$service = trim($service); // wipe out spaces
						$title = ucfirst(strtolower($service));
						if ($this->_params["use_text_flag"] == 1) { // display also service name?
							$service_name = "&nbsp;".$title;
						} else {
							$service_name = "&nbsp;";
						}
						if ($this->_params["tooltip_text"] != "") { // case custom tooltip text in place of "Send to"
							$tooltip_text = " title=\"".$this->_params["tooltip_text"]." ".$title."\"";
						} else {
							$tooltip_text = "";
						}
						
						/*
						 * ------------------------Special Counter Buttons----------
						 *
						 */
						//------display Facebook like counter button if appropriate
						if ($service == "facebook_like") {
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=button_count&amp;show_faces=false&amp;width=47&amp;action=like&amp;colorscheme=light&amp;height=21\" scrolling=\"no\" frameborder=\"0\" class=\"FBlike\"></iframe>\n";
						} elseif ($service == "facebook_rec") { // Facebook recommend
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=button_count&amp;show_faces=false&amp;width=47&amp;action=recommend&amp;colorscheme=light&amp;height=21\" scrolling=\"no\" frameborder=\"0\" class=\"FBrec\"></iframe>\n";
						} elseif ($service == "facebook_like_vertical") { // Facebook like vertical counter
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=box_count&amp;show_faces=false&amp;width=47&amp;action=like&amp;colorscheme=light&amp;height=90\" scrolling=\"no\" frameborder=\"0\" class=\"FBlikeB\"></iframe>\n";
						} elseif ($service == "facebook_rec_vertical") { // Facebook recommend vertical counter
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=box_count&amp;show_faces=false&amp;width=47&amp;action=recommend&amp;colorscheme=light&amp;height=90\" scrolling=\"no\" frameborder=\"0\" class=\"FBrecB\"></iframe>\n";
						//------display TweetMeme counter button if appropriate
						} elseif ($service == "tweetmeme") { // horizontal
							$output .= "<script type=\"text/javascript\">tweetmeme_style = 'compact';tweetmeme_url = '{$articleURL}';</script><script type=\"text/javascript\" src=\"http://tweetmeme.com/i/scripts/button.js\"></script>\n";
						} elseif ($service == "tweetmeme_vertical") { // vertical
							$output .= "<div class=\"tweetmeme_vert\"><script type=\"text/javascript\">tweetmeme_url = '{$articleURL}';</script><script type=\"text/javascript\" src=\"http://tweetmeme.com/i/scripts/button.js\"></script></div>\n";
						//------display Google Buzz counter button if appropriate (not valid XHTML)
						} elseif ($service == "googlebuzz_counter") { // horizontal counter
							$this->doc->addScript("http://www.google.com/buzz/api/button.js");
							$output .= "<a title=\"{$tooltip_text}Google Buzz\" class=\"google-buzz-button\" href=\"http://www.google.com/buzz/post\" data-button-style=\"small-count\" data-url=\"{$articleURL}\"></a>\n";
						} elseif ($service == "googlebuzz_vertical") { // vertical counter
							$this->doc->addScript("http://www.google.com/buzz/api/button.js");
							$output .= "<a title=\"{$tooltip_text}Google Buzz\" class=\"google-buzz-button\" href=\"http://www.google.com/buzz/post\" data-button-style=\"normal-count\" data-url=\"{$articleURL}\"></a>\n";
						//------display LinkedIn counter button if appropriate (not valid XHTML)
						} elseif ($service == "linkedin_counter") { // horizontal counter
							$this->doc->addScript("http://platform.linkedin.com/in.js");
							$output .= "<div class=\"linkedin_horz\"><script type=\"in/share\" data-url=\"{$articleURL}\" data-counter=\"right\"></script></div>\n";
						} elseif ($service == "linkedin_vertical") { // vertical counter
							$this->doc->addScript("http://platform.linkedin.com/in.js");
							$output .= "<div class=\"linkedin_vert\"><script type=\"in/share\" data-url=\"{$articleURL}\" data-counter=\"top\"></script></div>\n";
						//------display StumbleUpon counter if appropriate
						} elseif ($service == "stumbleupon_counter") { // horizontal counter
							$output .= "<script type=\"text/javascript\" src=\"http://www.stumbleupon.com/hostedbadge.php?s=2&amp;r={$articleURL}\"></script>\n";
						} elseif ($service == "stumbleupon_vertical") { // vertical counter
							$output .= "<script type=\"text/javascript\" src=\"http://www.stumbleupon.com/hostedbadge.php?s=5&amp;r={$articleURL}\"></script>\n";
						//------display Digg counter if appropriate
						} elseif ($service == "digg_counter") { // horizontal counter
							$diggURL = urlencode ($articleURL)."&amp;title=".urlencode ($articleTitle);
							$output .= "<script type=\"text/javascript\">(function() {var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];s.type = 'text/javascript';s.async = true;s.src = 'http://widgets.digg.com/buttons.js';s1.parentNode.insertBefore(s, s1);})();</script><a class=\"DiggThisButton DiggCompact\" href=\"{$diggURL}\"><span style=\"display:none\">{$articleDesc}</span></a>\n";
						} elseif ($service == "digg_vertical") { // vertical counter
							$diggURL = urlencode ($articleURL)."&amp;title=".urlencode ($articleTitle);
							$output .= "<script type=\"text/javascript\">(function() {var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];s.type = 'text/javascript';s.async = true;s.src = 'http://widgets.digg.com/buttons.js';s1.parentNode.insertBefore(s, s1);})();</script><a class=\"DiggThisButton DiggMedium\" href=\"{$diggURL}\"><span style=\"display:none\">{$articleDesc}</span></a>\n";
						//------display Google Plus +1 counter if appropriate
						} elseif ($service == "google_plusone_medium") { // medium size counter
							$this->doc->addScript("https://apis.google.com/js/plusone.js");
							$output .= "<![if gt IE 6]><div class=\"plusone\"><div class=\"g-plusone\" data-size=\"medium\" data-count=\"true\" data-href=\"{$articleURL}\"></div></div><![endif]>\n";
						} elseif ($service == "google_plusone_standard") { // standard size counter
							$this->doc->addScript("https://apis.google.com/js/plusone.js");
							$output .= "<![if gt IE 6]><div class=\"plusone\"><div class=\"g-plusone\" data-size=\"standard\" data-count=\"true\" data-href=\"{$articleURL}\"></div></div><![endif]>\n";
						} elseif ($service == "google_plusone_tall") { // vertical (tall) counter
							$this->doc->addScript("https://apis.google.com/js/plusone.js");
							$output .= "<![if gt IE 6]><div class=\"plusone\"><div class=\"g-plusone\" data-size=\"tall\" data-count=\"true\" data-href=\"{$articleURL}\"></div></div><![endif]>\n";
						//------display Tweet vertical counter if appropriate
						} elseif ($service == "tweet_vertical") {
							$this->doc->addScript("http://platform.twitter.com/widgets.js");
							$output .= "<a href=\"http://twitter.com/share\" class=\"twitter-share-button\" data-url=\"{$articleURL}\" data-text=\"{$articleDesc}\" data-count=\"vertical\">Tweet</a>\n";
						//------display AddThis Pill counter button if appropriate
						} elseif ($service == "counter_pill") {
							// prepare array for changing ids in case of double plugin display
							$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
							$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
							$output .= "<a class=\"addthis_pill_style\" id=\"{$addThis_class}_$this->count\"></a>\n";
							$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"});</script>";
							$this->count++; // next unique id
						//------display AddThis Vertical counter button if appropriate
						} elseif ($service == "counter_vertical") {
							$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
							$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
							$output .= "<a class=\"addthis_vertical_style\" id=\"{$addThis_class}_$this->count\"></a>\n";
							$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"});</script>";
							$this->count++; // next unique id
						//------display AddThis Bubble counter button if appropriate
						} elseif ($service == "counter_bubble") {
							$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
							$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
							$output .= "<a class=\"addthis_bubble_style\" id=\"{$addThis_class}_$this->count\"></a>\n";
							$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"});</script>";
							$this->count++; // next unique id
						} else {
							$output .= "<div><a class=\"addthis_button_{$service}\"{$nofollow}{$tooltip_text}>{$service_name}</a></div>\n";
						}
					}
				}
				if ($this->_params["use_more_flag"] == "1") { // case "more" is used
					$output .= "<div><a class=\"addthis_button_expanded\"".($this->_params["toolbox_more"] != "" ? " title=\"{$this->_params["toolbox_more"]}\">&nbsp;{$this->_params["toolbox_more"]}" : ">")."</a></div>\n"; // "more" icon + optional text
				}
				$output .= "<div class=\"clearAddthis\"></div>\n</div>\n";
			}
			
			/*
			 * ------------------------case CSS vertical #2 style (uses vertical2.css)
			 *
			 */
			if ($this->_params["toolbox_style"] == "cssVertical2") {
				// Add reference to CSS file
				JHTML::stylesheet("vertical2.css", $path);
				
				$output .= "<div class=\"{$addThis_class}\"><div class=\"addVertical2\"".($this->_params["toolbox_width"] != "" ? " style=\"width:{$this->_params["toolbox_width"]}\"" : "").">\n";
				
				$output .= $shareText; // display share text
				
				if (isset($toolServices)) { // if any services defined
					foreach ($toolServices as $service) { // display individual service icons
						$service = trim($service); // wipe out spaces
						$title = ucfirst(strtolower($service));
						if ($this->_params["tooltip_text"] != "") { // case custom tooltip text in place of "Send to"
							$tooltip_text = " title=\"".$this->_params["tooltip_text"]." ".$title."\"";
						} else {
							$tooltip_text = "";
						}
						
						/*
						 * ------------------------Special Counter Buttons----------
						 *
						 */
						//------display Facebook like counter button if appropriate
						if ($service == "facebook_like") {
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=button_count&amp;show_faces=false&amp;width=47&amp;action=like&amp;colorscheme=light&amp;height=21\" scrolling=\"no\" frameborder=\"0\" class=\"FBlike\"></iframe>\n";
						} elseif ($service == "facebook_rec") { // Facebook recommend
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=button_count&amp;show_faces=false&amp;width=47&amp;action=recommend&amp;colorscheme=light&amp;height=21\" scrolling=\"no\" frameborder=\"0\" class=\"FBrec\"></iframe>\n";
						} elseif ($service == "facebook_like_vertical") { // Facebook like vertical counter
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=box_count&amp;show_faces=false&amp;width=47&amp;action=like&amp;colorscheme=light&amp;height=90\" scrolling=\"no\" frameborder=\"0\" class=\"FBlikeB\"></iframe>\n";
						} elseif ($service == "facebook_rec_vertical") { // Facebook recommend vertical counter
							$articleURL = urlencode ($articleURL);
							$output .= "<iframe src=\"http://www.facebook.com/plugins/like.php?href={$articleURL}&amp;layout=box_count&amp;show_faces=false&amp;width=47&amp;action=recommend&amp;colorscheme=light&amp;height=90\" scrolling=\"no\" frameborder=\"0\" class=\"FBrecB\"></iframe>\n";
						//------display TweetMeme counter button if appropriate
						} elseif ($service == "tweetmeme") { // horizontal
							$output .= "<script type=\"text/javascript\">tweetmeme_style = 'compact';tweetmeme_url = '{$articleURL}';</script><script type=\"text/javascript\" src=\"http://tweetmeme.com/i/scripts/button.js\"></script>\n";
						} elseif ($service == "tweetmeme_vertical") { // vertical
							$output .= "<div class=\"tweetmeme_vert\"><script type=\"text/javascript\">tweetmeme_url = '{$articleURL}';</script><script type=\"text/javascript\" src=\"http://tweetmeme.com/i/scripts/button.js\"></script></div>\n";
						//------display Google Buzz counter button if appropriate (not valid XHTML)
						} elseif ($service == "googlebuzz_counter") { // horizontal counter
							$this->doc->addScript("http://www.google.com/buzz/api/button.js");
							$output .= "<a title=\"{$tooltip_text}Google Buzz\" class=\"google-buzz-button\" href=\"http://www.google.com/buzz/post\" data-button-style=\"small-count\" data-url=\"{$articleURL}\"></a>\n";
						} elseif ($service == "googlebuzz_vertical") { // vertical counter
							$this->doc->addScript("http://www.google.com/buzz/api/button.js");
							$output .= "<a title=\"{$tooltip_text}Google Buzz\" class=\"google-buzz-button\" href=\"http://www.google.com/buzz/post\" data-button-style=\"normal-count\" data-url=\"{$articleURL}\"></a>\n";
						//------display LinkedIn counter button if appropriate (not valid XHTML)
						} elseif ($service == "linkedin_counter") { // horizontal counter
							$this->doc->addScript("http://platform.linkedin.com/in.js");
							$output .= "<div class=\"linkedin_horz\"><script type=\"in/share\" data-url=\"{$articleURL}\" data-counter=\"right\"></script></div>\n";
						} elseif ($service == "linkedin_vertical") { // vertical counter
							$this->doc->addScript("http://platform.linkedin.com/in.js");
							$output .= "<div class=\"linkedin_vert\"><script type=\"in/share\" data-url=\"{$articleURL}\" data-counter=\"top\"></script></div>\n";
						//------display StumbleUpon counter if appropriate
						} elseif ($service == "stumbleupon_counter") { // horizontal counter
							$output .= "<script type=\"text/javascript\" src=\"http://www.stumbleupon.com/hostedbadge.php?s=2&amp;r={$articleURL}\"></script>\n";
						} elseif ($service == "stumbleupon_vertical") { // vertical counter
							$output .= "<script type=\"text/javascript\" src=\"http://www.stumbleupon.com/hostedbadge.php?s=5&amp;r={$articleURL}\"></script>\n";
						//------display Digg counter if appropriate
						} elseif ($service == "digg_counter") { // horizontal counter
							$diggURL = urlencode ($articleURL)."&amp;title=".urlencode ($articleTitle);
							$output .= "<script type=\"text/javascript\">(function() {var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];s.type = 'text/javascript';s.async = true;s.src = 'http://widgets.digg.com/buttons.js';s1.parentNode.insertBefore(s, s1);})();</script><a class=\"DiggThisButton DiggCompact\" href=\"{$diggURL}\"><span style=\"display:none\">{$articleDesc}</span></a>\n";
						} elseif ($service == "digg_vertical") { // vertical counter
							$diggURL = urlencode ($articleURL)."&amp;title=".urlencode ($articleTitle);
							$output .= "<script type=\"text/javascript\">(function() {var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];s.type = 'text/javascript';s.async = true;s.src = 'http://widgets.digg.com/buttons.js';s1.parentNode.insertBefore(s, s1);})();</script><a class=\"DiggThisButton DiggMedium\" href=\"{$diggURL}\"><span style=\"display:none\">{$articleDesc}</span></a>\n";
						//------display Google Plus +1 counter if appropriate
						} elseif ($service == "google_plusone_medium") { // medium size counter
							$this->doc->addScript("https://apis.google.com/js/plusone.js");
							$output .= "<![if gt IE 6]><div class=\"plusone\"><div class=\"g-plusone\" data-size=\"medium\" data-count=\"true\" data-href=\"{$articleURL}\"></div></div><![endif]>\n";
						} elseif ($service == "google_plusone_standard") { // standard size counter
							$this->doc->addScript("https://apis.google.com/js/plusone.js");
							$output .= "<![if gt IE 6]><div class=\"plusone\"><div class=\"g-plusone\" data-size=\"standard\" data-count=\"true\" data-href=\"{$articleURL}\"></div></div><![endif]>\n";
						} elseif ($service == "google_plusone_tall") { // vertical (tall) counter
							$this->doc->addScript("https://apis.google.com/js/plusone.js");
							$output .= "<![if gt IE 6]><div class=\"plusone\"><div class=\"g-plusone\" data-size=\"tall\" data-count=\"true\" data-href=\"{$articleURL}\"></div></div><![endif]>\n";
						//------display Tweet vertical counter if appropriate
						} elseif ($service == "tweet_vertical") {
							$this->doc->addScript("http://platform.twitter.com/widgets.js");
							$output .= "<a href=\"http://twitter.com/share\" class=\"twitter-share-button\" data-url=\"{$articleURL}\" data-text=\"{$articleDesc}\" data-count=\"vertical\">Tweet</a>\n";
						//------display AddThis Pill counter button if appropriate
						} elseif ($service == "counter_pill") {
							// prepare array for changing ids in case of double plugin display
							$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
							$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
							$output .= "<a class=\"addthis_pill_style\" id=\"{$addThis_class}_$this->count\"></a>\n";
							$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"});</script>";
							$this->count++; // next unique id
						//------display AddThis Vertical counter button if appropriate
						} elseif ($service == "counter_vertical") {
							$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
							$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
							$output .= "<a class=\"addthis_vertical_style\" id=\"{$addThis_class}_$this->count\"></a>\n";
							$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"});</script>";
							$this->count++; // next unique id
						//------display AddThis Bubble counter button if appropriate
						} elseif ($service == "counter_bubble") {
							$this->_changeCount[$this->count][0] = $addThis_class."_".$this->count; // id to be changed..
							$this->_changeCount[$this->count][1] = $addThis_class."_".$this->count."0"; // ..to this id
							$output .= "<a class=\"addthis_bubble_style\" id=\"{$addThis_class}_$this->count\"></a>\n";
							$output .= "<script type=\"text/javascript\">\n addthis.counter(\"#{$addThis_class}_$this->count\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"});</script>";
							$this->count++; // next unique id
						} else {
							$output .= "<a class=\"addthis_button_{$service}\"{$nofollow}{$tooltip_text}>{$title}</a>\n";
						}
					}
				}
				
				if ($this->_params["use_more_flag"] == "1") { // case "more" is used
					if ($this->_params["toolbox_more"] == "") { // fix more icon if no text is next to it
						$this->_params["toolbox_more"] = "&nbsp;";
					}
					$output .= "<div class=\"more\"><a class=\"addthis_button_expanded\"".($this->_params["toolbox_more"] != "" ? " title=\"{$this->_params["toolbox_more"]}\">{$this->_params["toolbox_more"]}" : ">")."</a></div>\n"; // "more" icon + optional text
				}
				$output .= "</div>\n";
			}
			
			$output .= "</div>\n"; // close Toolbox div
			
			// Render AddThis Toolbox using Javascript
			$output .= "<script type=\"text/javascript\">\n addthis.toolbox(\".{$addThis_class}\", {}, {url: \"{$articleURL}\", title: \"{$articleTitle}\", description: \"{$articleDesc}\"";
			if (isset($this->_swf_string)) {
				$output .= ", ".$this->_swf_string;
			}
			$output .= "});\n</script>";
			$output .= "</div>";
		}
		
		if ($this->_params["plugin_class"] != "") { // wrap plugin in a div tag, if a plugin CSS class is defined
			$output .= "</div>";
		}
		
		//-----display HTML code after article if it is defined
		if (isset($this->_params["html_after"])) {
			$output .= $this->_params["html_after"];
		}
		
		$output .= "\n<!-- END: ValAddThis Plugin -->\n";
		
		return $output;
	}
	//------------------------END--> function: Render HTML for ValAddThis
	
	//------------------------BEGIN--> function: Get article URL
	public function getURL(&$article) {
		if (!is_null($article))	{
			require_once( JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php');
			
			$uri = &JURI::getInstance();
			$base = $uri->toString(array('scheme', 'host', 'port'));
			if (isset($article->sectionid)) {
				$url = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug, $article->sectionid));
			} elseif (isset($article->catslug)) {
				$url = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug));
			} else {
				$url = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid));
			}
			
			return JRoute::_($base . $url, true, 0);
		}
	}

	//------------------------END--> function: Get article URL
}
?>
