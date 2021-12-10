DROP TABLE IF EXISTS `#__bmsmanager_bookmarks`;

CREATE TABLE `#__bmsmanager_bookmarks` (
	`id` int(5) NOT NULL auto_increment,
	`name` varchar(20) NOT NULL default '',
	`text` varchar(50) NOT NULL default '',
	`size` varchar(20) NOT NULL default 'text',
	`htmltext` tinytext NOT NULL default '',
	`htmlsmall` tinytext NOT NULL default '',
	`htmllarge` tinytext NOT NULL default '',
	`htmlbutton` tinytext NOT NULL default '',
	`htmlcustom` tinytext NOT NULL default '',
	`ordering` tinyint(2) NOT NULL default '0',
	`icon` varchar(100) NOT NULL default '',
	`popular` int(1) NOT NULL default '0',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__bmsmanager_bookmarks` VALUES
(1, 'Wikio', 'Wikio', 'small', '<a href=\"http://www.wikio.fr/vote?url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.wikio.fr/vote?url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/wikio.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 1, 'components/com_bmsmanager/assets/badges', 1),
(2, 'Scoopeo', 'Scoopeo', 'small', '<a href=\"http://www.scoopeo.com/scoop/new?newurl=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.scoopeo.com/scoop/new?newurl=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/scoopeo.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 2, 'components/com_bmsmanager/assets/badges', 1),
(3, 'Jamespot', 'Jamespot', 'small', '<a href=\"http://www.jamespot.com/?action=spotit&url=[url]&title=[title]&text=[desc];\" [twin]>[text]</a>', '<a href=\"http://www.jamespot.com/?action=spotit&url=[url]&title=[title]&text=[desc];\" [twin]>\r\n<img src=\"[badgespath]/jamespot.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 3, 'components/com_bmsmanager/assets/badges', 1),
(4, 'TapeMoi', 'TapeMoi', 'small', '<a href=\"http://www.tapemoi.com/submit.php?url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.tapemoi.com/submit.php?url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/tapemoi.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 4, 'components/com_bmsmanager/assets/badges', 1),
(5, 'Fuzz', 'Fuzz', 'small', '<a href=\"http://www.fuzz.fr/submit?url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.fuzz.fr/submit?url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/fuzz.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 5, 'components/com_bmsmanager/assets/badges', 1),
(6, 'Mr. Wong', 'Mr. Wong', 'small', '<a href=\"http://www.mister-wong.fr/index.php?action=addurl&amp;bm_url=[url]&bm_description=[title]&bm_notice=[desc];\" [twin]>[text]</a>', '<a href=\"http://www.mister-wong.fr/index.php?action=addurl&amp;bm_url=[url]&bm_description=[title]&bm_notice=[desc];\" [twin]>\r\n<img src=\"[badgespath]/misterwong.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '', '', '', 6, 'components/com_bmsmanager/assets/badges', 1),
(7, 'Facebook', 'Facebook', 'small', '<a href=\"http://www.facebook.com/sharer.php?u=[url]&t=[title];\" [twin]>[text]</a>', '<a href=\"http://www.facebook.com/sharer.php?u=[url]&t=[title];\" [twin]>\r\n<img src=\"[badgespath]/facebook.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '', '', '', 7, 'components/com_bmsmanager/assets/badges', 1),
(8, 'Google', 'Google', 'small', '<a href=\"http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=[url]&title=[title]&annotation=[desc];\" [twin]>[text]</a>', '<a href=\"http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=[url]&title=[title]&annotation=[desc];\" [twin]>\r\n<img src=\"[badgespath]/google.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '', '', '', 8, 'components/com_bmsmanager/assets/badges', 1),
(9, 'Technorati', 'Technorati', 'small', '<a href=\"http://technorati.com/faves?add=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://technorati.com/faves?add=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/technorati.small.gif\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>', '', '', '', 9, 'components/com_bmsmanager/assets/badges', 1),
(10, 'Blogmemes', 'Blogmemes', 'small', '<a href=\"http://www.blogmemes.fr/post.php?url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.blogmemes.fr/post.php?url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/blogmemes.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 10, 'components/com_bmsmanager/assets/badges', 1),
(11, 'Pioche', 'Pioche', 'small', '<a href=\"http://www.pioche.fr/soumettre_nouvelle.php?url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.pioche.fr/soumettre_nouvelle.php?url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/pioche.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 11, 'components/com_bmsmanager/assets/badges', 1),
(12, 'Gmiix', 'Gmiix', 'small', '<a href=\"http://www.gmiix.com/edit_actu.php?ext=1&url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.gmiix.com/edit_actu.php?ext=1&url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/gmiix.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 12, 'components/com_bmsmanager/assets/badges', 1),
(13, 'Bluegger', 'Bluegger', 'small', '<a href=\"http://www.bluegger.com/submit.php?url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.bluegger.com/submit.php?url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/bluegger.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 13, 'components/com_bmsmanager/assets/badges', 1),
(14, 'Digg-France', 'Digg-France', 'small', '<a href=\"http://www.digg-france.com/publier?url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.digg-france.com/publier?url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/diggfrance.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 14, 'components/com_bmsmanager/assets/badges', 1),
(15, 'Yoolink', 'Yoolink', 'small', '<a href=\"http://www.yoolink.fr/addorshare?url_value=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.yoolink.fr/addorshare?url_value=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/yoolink.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 15, 'components/com_bmsmanager/assets/badges', 1),
(16, 'VisitezMonSite', 'VisitezMonSite', 'small', '<a href=\"http://www.visitezmonsite.com/publier?url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://www.visitezmonsite.com/publier?url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/visitezmonsite.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 16, 'components/com_bmsmanager/assets/badges', 1),
(17, 'Twinik', 'Twinik', 'small', '<a href=\"http://twinik.com/submit.php?url=[url]&title=[title];\" [twin]>[text]</a>', '<a href=\"http://twinik.com/submit.php?url=[url]&title=[title];\" [twin]>\r\n<img src=\"[badgespath]/twinik.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 17, 'components/com_bmsmanager/assets/badges', 1),
(18, 'Atypicom', 'Atypicom', 'small', '<a href=\"http://www.labo.atypicom.fr/telechargement/joomla/plugin-bookmarks-sociaux-francais.html\" [twin]>[text]</a>', '<a href=\"http://www.labo.atypicom.fr/telechargement/joomla/plugin-bookmarks-sociaux-francais.html\" [twin]>\r\n<img src=\"[badgespath]/atypicom.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 18, 'components/com_bmsmanager/assets/badges', 1),
(19, 'Twitter', 'Twitter', 'small', '<a href=\"http://www.twitter.com/home/?status=[title][url];\" [twin]>[text]</a>', '<a href=\"http://www.twitter.com/home/?status=[title][url];\" [twin]>\r\n<img src=\"[badgespath]/twitter.png\" alt=\"[text]\" border=\"0\" width=\"16\" height=\"16\" />\r\n</a>','', '', '', 19, 'components/com_bmsmanager/assets/badges', 1);

