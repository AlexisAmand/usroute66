<?php
/********************************************************************
 iGalerie - script de galerie d'images
 Copyright (C) 2006-2007 - http://www.igalerie.org/

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
********************************************************************/

error_reporting(E_ALL);
if (@extension_loaded('zlib')
&& !@ini_get('zlib.output_compression')) {
	@ob_start('ob_gzhandler');
}
ignore_user_abort(TRUE);
if (function_exists('set_time_limit')) {
	@set_time_limit(30);
}


// On mémorise l'heure exacte du début du script.
global $_TIMESTART;
$_TIMESTART = explode(' ', microtime());

// Debug : requêtes SQL.
global $_MYSQL;
$_MYSQL = array();
$_MYSQL['debug'] = 0;
$_MYSQL['mysql_requetes'] = 0;
$_MYSQL['nb_requetes'] = 0;

// Paramètres de langue.
setlocale(LC_ALL, 'fr');
setlocale(LC_ALL, 'fr_FR');
setlocale(LC_ALL, 'french');
setlocale(LC_ALL, 'fr_FR.ISO8859-1');

setlocale(LC_NUMERIC, 'en');
setlocale(LC_NUMERIC, 'en_US');
setlocale(LC_NUMERIC, 'english');
setlocale(LC_NUMERIC, 'en_US.ISO8859-1');

// On annule la fonction magic_quotes_gpc.
function strip_magic_quotes(&$valeur) {
	if (is_array($valeur)) {
		array_walk($valeur, 'strip_magic_quotes');
	} else {
		$valeur = stripslashes($valeur);
	}
}
if (get_magic_quotes_gpc()) {
	array_walk($_GET, 'strip_magic_quotes');
	array_walk($_POST, 'strip_magic_quotes');
	array_walk($_COOKIE, 'strip_magic_quotes');
	array_walk($_REQUEST, 'strip_magic_quotes');
}

// On désactive la fonction magic_quotes_runtime
if (get_magic_quotes_runtime() && 
	function_exists('set_magic_quotes_runtime')) {
	set_magic_quotes_runtime(0);
}

// On supprime tout paramètre POST et COOKIE non existant.
$R = array('auteur', 'courriel', 'message', 'note', 'preview', 'siteweb', 'decode_courriel',
'molpac', 'password', 'contact_mail', 'contact_sujet', 'contact_message', 'contact_nom', 'upload_categorie',
'upload_images', 's_query', 's_mode', 's_nom', 's_path', 's_desc', 's_mc', 's_com', 's_make', 's_model', 
's_alb', 's_casse', 's_accents', 's_date', 's_date_type', 's_date_start_jour', 's_date_start_mois',
's_date_start_an', 's_date_end_jour', 's_date_end_mois', 's_date_end_an', 's_taille', 's_width_start',
's_width_end', 's_height_start', 's_height_end', 's_poids', 's_poids_start', 's_poids_end', 'sid',
'new_login', 'new_pass', 'new_pass_confirm', 'new_mail', 'new_mail_public', 'new_web', 'new_lieu', 'new_newsletter',
'ident_login', 'ident_pass', 'ident_souvenir', 'modif_profil', 'supp_avatar', 'oubli_mail', 'oubli_user',
'obj_type', 'obj_name', 'obj_desc');
foreach ($_POST as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_POST[$name]);
	}
}
$R = array('galerie_perso', 'galerie_pass', 'galerie_vote', 'galerie_membre');
foreach ($_COOKIE as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_COOKIE[$name]);
	}
}

// Chargement de la config.
if (file_exists(dirname(__FILE__) . '/config/conf.php')) {
	require_once(dirname(__FILE__) . '/config/conf.php');
}

// La galerie est-elle installée ?
if (!defined('GALERIE_INSTALL') || !GALERIE_INSTALL) {
	header('Location: install/');
	exit;
}

// Fichier d'accès à la galerie.
$gf = basename(GALERIE_URL);
$gf = (GALERIE_URL_TYPE == 'normal' && $gf == 'index.php') ? '' : $gf;
define('GALERIE_FILE', $gf);


require_once(dirname(__FILE__) . '/includes/classes/class.mysql.php');
require_once(dirname(__FILE__) . '/includes/classes/class.cookie.php');
require_once(dirname(__FILE__) . '/includes/classes/class.outils.php');


// ================================================== <TYPE URL> ==================================================

// Type d'URL 'query_string' ou 'url_rewrite'.
if (GALERIE_URL_TYPE == 'query_string' || GALERIE_URL_TYPE == 'url_rewrite') {

	if (empty($_GET['u']) && isset($_GET['search'])) {
		$sadv = '';
		if (isset($_GET['sadv'])) {
			$sadv = '/sadv/' . $_GET['sadv'];
		}
		if (isset($_GET['img'])) {
			$sadv .= '/image/' . $_GET['img'];
		}
		if (isset($_GET['addfav'])) {
			$sadv .= '/addfav/' . $_GET['addfav'];
		}
		$qs = (GALERIE_URL_TYPE == 'query_string') ? GALERIE_FILE . '?/' : '';
		$s = preg_replace('`[\x5c#]`', '', $_GET['search']);
		if (empty($s)) {
			header('Location: ' . outils::genLink('?cat=1', '', '', 0, '&'));
		} else {
			header('Location:' . GALERIE_PATH . '/' . $qs . 'search/' . urlencode($s) . $sadv);
		}
		exit;
	}

	if (!empty($_SERVER['QUERY_STRING'])) {
		$qs = preg_replace('`(?:^|[\&\?])(..|u|mod|deconnect)=`', '/$1/', $_SERVER['QUERY_STRING']);
		convert_string_to_gets($qs);
	}


// Type d'URL 'path_info'.
} elseif (GALERIE_URL_TYPE == 'path_info') {

	if (empty($_GET['u']) && isset($_GET['search'])) {
		$sadv = '';
		if (isset($_GET['sadv'])) {
			$sadv = '/sadv/' . $_GET['sadv'];
		}
		if (isset($_GET['img'])) {
			$sadv .= '/image/' . $_GET['img'];
		}
		if (isset($_GET['addfav'])) {
			$sadv .= '/addfav/' . $_GET['addfav'];
		}
		$s = preg_replace('`[\x5c#]`', '', $_GET['search']);
		if (empty($s)) {
			header('Location: ' . outils::genLink('?cat=1', '', '', 0, '&'));
		} else {
			header('Location:' . GALERIE_PATH . '/' . GALERIE_FILE . '/search/' . urlencode($s) . $sadv);
		}
		exit;
	}

	if (!empty($_SERVER['PATH_INFO'])) {
		$qs = preg_replace('`(?:^|[\&\?])(..|u|mod|deconnect)=`', '/$1/', $_SERVER['PATH_INFO']);
		convert_string_to_gets($qs);
	}

	unset($_SERVER['QUERY_STRING']);
}

// On supprime tout paramètre GET non existant.
$R = array('alb', 'cat', 'commentaires', 'hits', 'ih', 'il', 'img',
		   'io', 'is', 'it', 'ra', 'rj', 'recentes', 'search',
		   'section', 'sa', 'sc', 'sd', 'sh', 'si', 'sn', 'sp',
		   'st', 'sv', 'sy', 'startnum', 'vl', 'vn', 'votes', 'u',
		   'mod', 'images', 'deconnect', 'sadv', 'date_creation',
		   'date_ajout', 'tag', 'comments', 'membres', 'mcom',
		   'mfav', 'mimg', 'addfav', 'profil', 'key', 'diapo');
foreach ($_GET as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_GET[$name]);
	}
}


function convert_string_to_gets($p) {
	$params = array();
	$p = preg_split('`/`', $p, -1, PREG_SPLIT_NO_EMPTY);
	for ($i = 0; $i < count($p); $i += 2) {
		if (isset($p[$i+1])) {
			$params[$p[$i]] = preg_replace('`&(?=[^/]+=).*$`', '', $p[$i+1]);
		}
	}

	$ok = false;
	foreach ($params as $type => $value) {
		switch ($type) {
			case 'images' :
			case 'recentes' :
			case 'hits' :
			case 'commentaires' :
			case 'comments' :
			case 'votes' :
				if (preg_match('`^(\d+)-.+`', $value, $m)) { $_GET[$type] = $m[1]; }
				$ok = true;
				break;
			case 'categorie' :
				if (preg_match('`^(\d+)-.+`', $value, $m)) { $_GET['cat'] = $m[1]; }
				$ok = true;
				break;
			case 'album' :
				if (preg_match('`^(\d+)-.+`', $value, $m)) { $_GET['alb'] = $m[1]; }
				$ok = true;
				break;
			case 'image' :
				if (preg_match('`^(\d+)-.+`', $value, $m)) { $_GET['img'] = $m[1]; }
				$ok = true;
				break;
			case 'startnum' :
				if (preg_match('`^(\d+)$`', $value, $m)) { $_GET['startnum'] = $m[1]; }
				break;
			case 'date_creation' :
				if (preg_match('`^\d{2}-\d{2}-\d{4}$`', $value, $m)) { $_GET['date_creation'] = $m[0]; }
				$ok = true;
				break;
			case 'date_ajout' :
				if (preg_match('`^\d{2}-\d{2}-\d{4}$`', $value, $m)) { $_GET['date_ajout'] = $m[0]; }
				$ok = true;
				break;
			case 'tag' :
			case 'search' :
			case 'section' :
			case 'membres' :
			case 'profil' :
			case 'mcom' :
			case 'mimg' :
			case 'mfav' :
			case 'addfav' :
			case 'diapo' :
				$ok = true;
			case 'sadv' :
			case 'type' :
			case 'deconnect' :
			case 'mod' :
				$_GET[$type] = urldecode($value);
				if ($type == 'tag') {
					$_GET[$type] = str_replace('_', ' ', $_GET[$type]);
				}
				break;
			default :
				unset($params[$type]);
		}
	}
}


// ================================================== </TYPE URL> ==================================================


// Filtrage des paramètres GET.
$gets = array('img', 'alb', 'cat', 'images', 'hits', 'comments',
			  'votes', 'commentaires', 'vn', 'vl', 'it', 'il',
			  'ih', 'rj', 'u', 'startnum', 'recentes', 'mod',
			  'deconnect', 'addfav');
for ($i = 0; $i < count($gets); $i++) {
	verif_gets($gets[$i], '`^\d{1,12}$`');
}

$gets = array('section', 'is', 'io', 'sa', 'sc', 'sd',
			  'sh', 'si', 'sn', 'sp', 'sv', 'sy', 'ra');
for ($i = 0; $i < count($gets); $i++) {
	verif_gets($gets[$i], '`^[a-z\d]{1,20}$`');
}

$gets = array('st', 'sadv', 'mimg', 'mcom', 'mfav', 'date_ajout',
			  'date_creation', 'membres', 'profil');
for ($i = 0; $i < count($gets); $i++) {
	verif_gets($gets[$i], '`^[a-z\d_\.-]{1,120}$`i');
}

function verif_gets($p, $re) {
	if (isset($_GET[$p]) && !preg_match($re, $_GET[$p])) {
		unset($_GET[$p]);
	}
}



// Sections : plan, aide, contact, password, historique.
if (isset($_GET['section'])) {
	if ($_GET['section'] == 'recherche') {
		require_once(dirname(__FILE__) . '/includes/classes/class.recherche.php');
	}
	$galerie = new galerie('section');
	$galerie->section();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template'] . '/section.php');

// Images.
} elseif (isset($_GET['img'])) {
	if (isset($_GET['search'])) {
		require_once(dirname(__FILE__) . '/includes/classes/class.recherche.php');
	}
	$galerie = new galerie('img');
	$galerie->image();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template'] . '/image.php');

// Tags.
} elseif (isset($_GET['tag'])) {
	$galerie = new galerie('tag');
	$galerie->page_tags();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template']  . '/album.php');

// Commentaires.
} elseif (isset($_GET['comments'])) {
	$galerie = new galerie('comments');
	$galerie->page_commentaires();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template']  . '/commentaires.php');

// Membres.
} elseif (isset($_GET['membres'])) {
	require_once(dirname(__FILE__) . '/includes/classes/class.files.php');
	$galerie = new galerie('membres');
	$galerie->membres();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template']  . '/membres.php');

// Images membres.
} elseif (isset($_GET['mimg'])) {
	$galerie = new galerie('mimg');
	$galerie->membres_images();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template']  . '/album.php');

// Favoris membres.
} elseif (isset($_GET['mfav'])) {
	$galerie = new galerie('mfav');
	$galerie->membres_favoris();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template']  . '/album.php');

// Commentaires membres.
} elseif (isset($_GET['mcom'])) {
	$galerie = new galerie('mcom');
	$galerie->membres_commentaires();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template']  . '/commentaires.php');

// Profil  membres.
} elseif (isset($_GET['profil'])) {
	$galerie = new galerie('profil');
	$galerie->membres_profil();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template']  . '/membres.php');

// Pages spéciales.
} elseif (isset($_GET['commentaires']) || 
	     isset($_GET['votes']) || 
	     isset($_GET['hits']) || 
	     isset($_GET['recentes']) || 
	     isset($_GET['images']) || 
	     isset($_GET['date_creation']) || 
	     isset($_GET['date_ajout'])) {
	if (isset($_GET['commentaires'])) {
		$type = 'commentaires';
	} elseif (isset($_GET['votes'])) {
		$type = 'votes';
	} elseif (isset($_GET['hits'])) {
		$type = 'hits';
	} elseif (isset($_GET['recentes'])) {
		$type = 'recentes';
	} elseif (isset($_GET['date_creation'])) {
		$type = 'date_creation';
	} elseif (isset($_GET['date_ajout'])) {
		$type = 'date_ajout';
	} else {
		$type = 'images';
	}
	$galerie = new galerie($type);
	$galerie->speciales();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template']  . '/album.php');

// Recherche.
} elseif (isset($_GET['search'])) {
	require_once(dirname(__FILE__) . '/includes/classes/class.recherche.php');
	$galerie = new galerie('search');
	$galerie->search();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template'] . '/album.php');

// Albums.
} elseif (isset($_GET['alb'])) {
	$galerie = new galerie('alb');
	$galerie->album();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template'] . '/album.php');

// Categories.
} else {
	$galerie = new galerie('cat');
	$galerie->categorie();
	require_once(dirname(__FILE__) . '/includes/prefs_template.php');
	define('IGAL_TEMPLATE', 'template/' . $galerie->config['galerie_template'] . '/categorie.php');

}

if (GALERIE_INTEGRATED) {
	echo "<!-- GALERIE MODE INTEGRATED -->\n";
} else {
	echo "<!-- GALERIE MODE NORMAL -->\n";
	require_once(dirname(__FILE__) . '/' . IGAL_TEMPLATE);
}



/*
 * ========== class.galerie
 */
class galerie {

	var $config;	// Configuration de la galerie.
	var $params;	// Paramètres internes.
	var $choix;		// Détermination des préférences selon choix admin et choix utilisateur.

	// Objets.
	var $prefs;		// Préférences utilisateur.
	var $passwords;	// Mots de passe.
	var $mysql;		// Base de données.
	var $membre;	// Identification membres.

	// Informations brutes à destination des fonctions de template.
	var $template;



	/*
	 *	Constructeur.
	*/
	function galerie($type) {

		global $_MYSQL;

		$this->params['objet_type'] = $type;
		$this->template['infos']['type'] = $type;

		switch ($type) {

			case 'alb' :
			case 'cat' :
			case 'img' :
			case 'hits' :
			case 'votes' :
			case 'commentaires' :
			case 'recentes' :
			case 'images' :
			case 'comments' :

				// Identifiant de l'objet.
				if (!empty($_GET[$type]) && preg_match('`^[1-9]\d{0,9}$`', $_GET[$type])) {
					$this->params['objet_id'] = $_GET[$type];
				} else {
					$this->params['objet_id'] = 1;
				}

			case 'search' :
			case 'date_creation' :
			case 'date_ajout' :
			case 'tag' :
			case 'mcom' :
			case 'mimg' :
			case 'mfav' :

				// Page à afficher.
				if (isset($_GET['startnum']) && preg_match('`^[1-9]\d{0,9}$`', $_GET['startnum'])) {
					$this->params['startnum'] = $_GET['startnum'];
				} else {
					$this->params['startnum'] = 0;
				}
				$this->template['infos']['startnum'] = $this->params['startnum'];

				break;

			case 'section' :

				switch ($_GET['section']) {
					case 'plan' :
					case 'contact' :
					case 'tags' :
					case 'recherche' :
					case 'pass' :
					case 'historique' :
						$this->params['section'] = $_GET['section'];
						$this->template['infos']['objet'] = $_GET['section'];
						break;
					default :
						header('Location: ' . outils::genLink('?cat=1'));
						exit;
					}

				break;

		}

		// Préférences utilisateur.
		$this->prefs = new cookie(31536000, 'galerie_perso', GALERIE_PATH);

		// Mots de passe.
		$this->passwords = new cookie(31536000, 'galerie_pass', GALERIE_PATH);

		// Connexion à la base de données.
		$this->mysql = new connexion(MYSQL_SERV, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);

		// Récupération des paramètres de configuration.
		$mysql_requete = 'SELECT parametre,valeur FROM ' . MYSQL_PREF . 'config';
		$this->config = $this->mysql->select($mysql_requete, 3);
		if (empty($this->config)) {
			die ('[' . __LINE__ . '] La base de données est vide.<br />' . mysql_error());
		}

		$this->template['debug']['mysql'] = $_MYSQL['mysql_requetes'];

		$this->template['infos']['add_votes'] = true;
		$this->template['infos']['add_comments'] = true;

		$this->template['infos']['h1'] = $this->config['galerie_titre'];
		$this->template['infos']['galerie_nom'] = $this->config['galerie_titre_court'];
		$this->template['infos']['footer'] = $this->config['galerie_footer'];
		$this->template['infos']['footer_message'] = $this->config['galerie_message_footer'];
		$this->template['infos']['tb_date_format'] = $this->config['galerie_tb_date_format'];
		$this->template['infos']['im_date_format'] = $this->config['galerie_im_date_format'];
		$this->template['infos']['images_window'] = $this->config['galerie_images_window'];
		$this->template['infos']['active_commentaires'] = $this->config['active_commentaires'];
		$this->template['infos']['active_votes'] = $this->config['active_votes'];
		$this->template['infos']['prefixe_vignettes'] = THUMB_PREF;
		$this->template['infos']['image_mode_resize'] = $this->config['galerie_images_resize'];
		$this->template['infos']['advsearch'] = $this->config['active_advsearch'];
		$this->template['infos']['historique'] = $this->config['active_historique'];
		$this->template['infos']['diaporama'] = $this->config['active_diaporama'];
		$this->template['infos']['rss'] = $this->config['active_rss'];
		$this->template['infos']['tags'] = $this->config['active_tags'];
		$this->template['infos']['liens'] = $this->config['active_liens'];
		$this->template['infos']['active_exif'] = $this->config['active_exif'];
		$this->template['infos']['active_iptc'] = $this->config['active_iptc'];
		$this->template['infos']['active_page_comments'] = $this->config['galerie_page_comments'];
		$this->template['infos']['users_upload_maxsize'] = $this->config['users_upload_maxsize'];
		$this->template['infos']['users_upload_maxwidth'] = $this->config['users_upload_maxwidth'];
		$this->template['infos']['users_upload_maxheight'] = $this->config['users_upload_maxheight'];

		$this->template['display']['s_hits'] = '';
		$this->template['display']['s_comments'] = '';
		$this->template['display']['s_votes'] = '';
		$this->template['display']['s_recentes'] = '';

		$this->template['contact']['text'] = $this->config['galerie_contact_text'];
		$this->template['contact']['active'] = $this->config['galerie_contact'];
		$this->template['contact']['admin_mail'] = $this->config['admin_mail'];

		$this->config['galerie_template'] = GALERIE_THEME;
		$this->config['galerie_style'] = GALERIE_STYLE;

		$this->template['galerie_liens'] = $this->config['galerie_liens'];
		$this->template['add_style'] = $this->config['galerie_add_style'];
		$this->template['infos']['parent_nom'] = '';

		// Paramètres template.
		include_once(dirname(__FILE__) . '/template/' . $this->config['galerie_template'] . '/_params.php');

		$this->template['infos']['membres_active'] = $this->config['users_membres_active'];
		$this->template['infos']['membres_avatar'] = $this->config['users_membres_avatars'];

		// Si les images ne sont pas affichées dans une page dédiée,
		// on procède à quelques changements.
		if ($this->config['galerie_images_window']) {
			$this->config['active_commentaires'] = 0;
			$this->config['active_votes'] = 0;
			$this->config['display_hits'] = 0;
			$this->config['user_hits'] = 0;
			if ($type == 'img') {
				header('Location: ' . outils::genLink('?cat=1'));
				exit;
			}
		}

		// La galerie est-elle désactivée ?
		if (!$this->config['active_galerie']) {
			die(nl2br(htmlentities($this->config['galerie_message_fermeture'])));
			exit;
		}

		$this->membres_connexion();
		$this->image_hasard();

		// Choix utilisateurs : style.
		$this->choix['style'] = $this->config['galerie_style'];
		if ($this->config['user_perso'] && $this->config['user_style']) {
			$valeur_st = $this->prefs->lire('st');
			if ($valeur_st !== FALSE) {
				$this->choix['style'] = $valeur_st;
			}
			if (!empty($_GET['st']) && preg_match('`^[a-z\d_-]{1,40}$`', $_GET['st'])) {
				$this->prefs->ajouter('st', $_GET['st']);
				$this->choix['style'] = $_GET['st'];
			}

			// Récupération des styles du template.
			$i = 0;
			$style_rep = './template/' . $this->config['galerie_template'] . '/style/';
			if ($rep = opendir($style_rep)) {
				while ($ent = readdir($rep)) {
					if (is_dir($style_rep . $ent) && !preg_match('`^\.{1,2}$`', $ent)) {
						if (preg_match('`^[-a-z0-9_]+$`', $ent)) {
							$this->template['user']['styles'][$i] = $ent;
							$i++;
						}
					}
				}
				closedir($rep);
			}
			if ($i === 1) {
				$this->config['user_style'] = 0;
			}
		}

		$this->template['infos']['style_relative'] = 'template/' . $this->config['galerie_template'] . '/style/' . $this->choix['style'] . '/';
		$this->template['infos']['style'] = GALERIE_PATH . '/template/' . $this->config['galerie_template'] . '/style/' . $this->choix['style'] . '/' . $this->choix['style'] . '.css';
		$this->template['display']['style'] = $this->choix['style'];

		// Filtrage IP.
		if ((function_exists('filter_var') && !filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP))
		 || !preg_match('`^' . outils::http_url('IP') . '$`', $_SERVER['REMOTE_ADDR'])) {
			$_SERVER['REMOTE_ADDR'] = 'inconnu';
		}
	}



	/*
	  *	Membres : connexion/deconnexion/identification.
	*/
	function membres_connexion() {

		$this->template['infos']['membres_connexion'] = true;

		if (!$this->config['users_membres_active']) {
			if (isset($_GET['membres'])) {
				unset($_GET['membres']);
			}
			return;
		}

		// Droits au minium par défaut.
		$this->template['infos']['add_votes'] = false;
		$this->template['infos']['add_comments'] = false;
		$this->template['infos']['advsearch'] = false;
		$this->config['active_advsearch'] = false;
		$this->config['user_perso'] = false;


		$this->membre = new cookie(31536000, 'galerie_membre', GALERIE_PATH);

		$session_ok = false;

		// Déconnexion.
		if (isset($_GET['membres']) && $_GET['membres'] == 'deconnect') {
			$this->membre->expire = time();
			$this->membre->ajouter('sid', -1);
			$this->membre->ecrire();
			header('Location:' . outils::genLink('?cat=1'));
			exit;
		}

		// Connexion.
		$this->template['membre_user'] = array();
		if (!empty($_POST['ident_login']) && !empty($_POST['ident_pass'])) {
			sleep(1);
			$this->template['membres']['erreur_identification'] = 1;
			if (preg_match('`^[@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)«»<>!?-]{6,250}$`i', $_POST['ident_pass'])) {
				if (preg_match('`^[-_a-z\d]{3,30}$`i', $_POST['ident_login'])) {
					$mysql_requete = 'SELECT ' . MYSQL_PREF . 'users.user_id,
											 ' . MYSQL_PREF . 'users.user_login,
											 ' . MYSQL_PREF . 'users.user_oubli,
											 ' . MYSQL_PREF . 'users.user_lieu,
											 ' . MYSQL_PREF . 'users.user_avatar,
											 ' . MYSQL_PREF . 'users.user_date_derniere_visite,
											 ' . MYSQL_PREF . 'users.user_date_dernier_upload,
											 ' . MYSQL_PREF . 'groupes.*
										FROM ' . MYSQL_PREF . 'users JOIN ' . MYSQL_PREF . 'groupes USING (groupe_id)
									   WHERE ' . MYSQL_PREF . 'users.user_pass = "' . outils::protege_mysql(md5($_POST['ident_pass']), $this->mysql->lien) . '"
										 AND ' . MYSQL_PREF . 'users.user_login = "' . outils::protege_mysql($_POST['ident_login'], $this->mysql->lien) . '"
									   LIMIT 1';
					$this->template['membre_user'] = $this->mysql->select($mysql_requete);
					if (is_array($this->template['membre_user'])) {
						$this->template['membres']['erreur_identification'] = 0;
						$session_id = outils::gen_key();
						$this->template['membre_user'][0]['user_session_id'] = $session_id;
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
											 SET user_session_id = "' . $session_id . '"
										   WHERE user_id = "' . $this->template['membre_user'][0]['user_id'] . '"';
						$this->mysql->requete($mysql_requete);

						$this->membre->expire = (isset($_POST['ident_souvenir'])) ? time() + (31536000*5) : 0;
						$this->membre->ajouter('sid', $session_id);
						$this->membre->ecrire();
						$session_ok = true;
					}
				}
			}
		}

		// Identification.
		if (!$session_ok && ($sid = $this->membre->lire('sid')) !== false) {
			if (preg_match('`^[a-z\d]{20}$`i', $sid)) {
				$mysql_requete = 'SELECT ' . MYSQL_PREF . 'users.user_id,
										 ' . MYSQL_PREF . 'users.user_login,
										 ' . MYSQL_PREF . 'users.user_mail,
										 ' . MYSQL_PREF . 'users.user_oubli,
										 ' . MYSQL_PREF . 'users.user_web,
										 ' . MYSQL_PREF . 'users.user_lieu,
										 ' . MYSQL_PREF . 'users.user_avatar,
										 ' . MYSQL_PREF . 'users.user_date_creation,
										 ' . MYSQL_PREF . 'users.user_date_derniere_visite,
										 ' . MYSQL_PREF . 'users.user_date_dernier_upload,
										 ' . MYSQL_PREF . 'users.user_session_id,
										 ' . MYSQL_PREF . 'groupes.*
									FROM ' . MYSQL_PREF . 'users JOIN ' . MYSQL_PREF . 'groupes USING (groupe_id)
								   WHERE ' . MYSQL_PREF . 'users.user_session_id = "' . outils::protege_mysql($sid, $this->mysql->lien) . '"';
				$this->template['membre_user'] = $this->mysql->select($mysql_requete);
				if (is_array($this->template['membre_user'])) {
					$session_ok = true;
				}
			}
		}

		if ($session_ok) {
			$this->template['infos']['membres_connexion'] = false;

			$update = '';

			// Date de dernière visite.
			if (date('zy') != date('zy', $this->template['membre_user'][0]['user_date_derniere_visite'])) {
				$update = ', user_date_derniere_visite = "' . time() . '",
						     user_ip_derniere_visite = "' . $_SERVER['REMOTE_ADDR'] . '"';
			}

			// Champ oubli de mot de passe.
			if (!empty($this->template['membre_user'][0]['user_oubli'])) {
				$update = ', user_oubli = NULL';
			}

			if ($update) {
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
									 SET ' . substr($update, 1) . '
								   WHERE user_id = "' . $this->template['membre_user'][0]['user_id'] . '"';
				$this->mysql->requete($mysql_requete);
			}

		} else {
			$this->template['infos']['membres_connexion'] = true;

			// Droits 'invité'.
			$mysql_requete = 'SELECT *
								FROM ' . MYSQL_PREF . 'groupes
							   WHERE groupe_id = "2"';
			$this->template['membre_user'] = $this->mysql->select($mysql_requete);

		}
		if (isset($this->template['membre_user'][0]['user_login'])) {
			$this->template['membre_user'][0]['user_nom'] = str_replace('_', ' ', $this->template['membre_user'][0]['user_login']);
		}

		// Droits.
		if (!empty($this->template['membre_user'][0]['groupe_votes'])) {
			$this->template['infos']['add_votes'] = true;
		}
		if (!empty($this->template['membre_user'][0]['groupe_commentaires'])) {
			$this->template['infos']['add_comments'] = true;
		}
		if (!empty($this->template['membre_user'][0]['groupe_recherche_avance'])) {
			$this->template['infos']['advsearch'] = true;
			$this->config['active_advsearch'] = true;
		}
		if (!empty($this->template['membre_user'][0]['groupe_perso'])) {
			$this->config['user_perso'] = true;
		}
		if (!empty($this->template['membre_user'][0]['groupe_newsletter'])) {
			$this->template['infos']['user_newsletter'] = true;
		}

		// Admin.
		if (!empty($this->template['membre_user'][0]['user_id'])
		 && $this->template['membre_user'][0]['user_id'] == 1) {
			$this->template['infos']['user_newsletter'] = false;
			$this->template['infos']['membres_noadmin'] = false;
		} else {
			$this->template['infos']['membres_noadmin'] = true;
		}
	}



	/*
	 *	Une image prise au hasard.
	*/
	function image_hasard() {
		if ($this->config['galerie_image_hasard']) {

			// On récupère les informations de l'image aléatoire à afficher.
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_id,
									 ' . MYSQL_PREF . 'images.image_nom,
									 ' . MYSQL_PREF . 'images.image_chemin,
									 ' . MYSQL_PREF . 'images.image_largeur,
									 ' . MYSQL_PREF . 'images.image_hauteur,
									 ' . MYSQL_PREF . 'categories.categorie_id AS album_id,
									 ' . MYSQL_PREF . 'categories.categorie_nom AS album_nom
								FROM ' . MYSQL_PREF . 'images,
									 ' . MYSQL_PREF . 'categories
							   WHERE ' . MYSQL_PREF . 'images.image_visible = "1" 
							     AND ' . MYSQL_PREF . 'images.categorie_parent_id = ' . MYSQL_PREF . 'categories.categorie_id'
								  	   . $this->images_protect(MYSQL_PREF . 'images.image') . '
							ORDER BY RAND()
							   LIMIT 1';
			$image_hasard = $this->mysql->select($mysql_requete);
			if (!is_array($image_hasard)) {
				return;
			}
			$file = $image_hasard[0]['image_chemin'];
			$thumb = GALERIE_PATH . '/getimg.php?img=' . $file;
			$this->template['image_hasard']['thumb'] = $thumb;
			$this->template['image_hasard']['nom'] = $image_hasard[0]['image_nom'];
			$this->template['image_hasard']['album_nom'] = $image_hasard[0]['album_nom'];
			$this->template['image_hasard']['album_id'] = $image_hasard[0]['album_id'];

			// Méthode d'affichage de l'image.
			$image_text = (IMG_TEXTE) ? 'getitext.php?i=' : GALERIE_ALBUMS . '/';
			switch ($this->config['galerie_images_window']) {
				case 0 :
					$this->template['image_hasard']['lien'] = outils::genLink('?img=' . $image_hasard[0]['image_id'], $image_hasard[0]['image_nom']);
					break;
				case 1 :
					$this->template['image_hasard']['lien'] = GALERIE_PATH . '/' . $image_text . $image_hasard[0]['image_chemin'];
					break;
				case 2 :
					$largeur = $image_hasard[0]['image_largeur'] + 40;
					$hauteur = $image_hasard[0]['image_hauteur'] + 30;
					$this->template['image_hasard']['lien'] = "javascript:window.open('" 
						. GALERIE_PATH . '/' . $image_text . $image_hasard[0]['image_chemin']
						. "','','scrollbars=yes,status=no,resizable=yes,width=" 
						. $largeur 
						. ",height=" 
						. $hauteur  
						. ",top=0,left=0');void(0);";
					break;
			}
		}
	}



	/*
	 *	Categories.
	*/
	function categorie() {

		// Nombre de vignettes par page.
		$this->choix['vn'] = $this->config['vignettes_cat_line'];
		$this->choix['vl'] = $this->config['vignettes_cat_col'];
		$this->params['limit_vignettes'] = $this->choix['vn'] * $this->choix['vl'];
		$this->template['infos']['nb_vignettes'] = $this->params['limit_vignettes'];
		$this->template['infos']['vignettes_col'] = $this->choix['vn'];
		$this->template['infos']['vignettes_line'] = $this->choix['vl'];

		$this->infos_categorie();
		$this->deconnect();

		// La catégorie est-elle protégée par un mot de passe ?
		if (!empty($this->params['objet_actuel']['categorie_pass'])) {

			$this->template['infos']['pass'] = 1;

			// Membres : droits d'accès.
			if (isset($this->template['membre_user'][0]['groupe_album_pass_mode'])) {
				$verif_pass = false;
				if ($this->template['membre_user'][0]['groupe_album_pass_mode'] != 'aucun') {
					if ($this->template['membre_user'][0]['groupe_album_pass_mode'] == 'tous') {
						$verif_pass = true;
					} else {
						$passwords = unserialize($this->template['membre_user'][0]['groupe_album_pass']);
						if (in_array($this->params['objet_actuel']['categorie_pass'], $passwords)) {
							$verif_pass = true;
						}
					}
				}
				if (!$verif_pass) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

			// L'utilisateur a-t-il entré le bon mot de passe ?
			} else {
				$pass = preg_replace('`^(\d+):.+`', '$1', $this->params['objet_actuel']['categorie_pass']);
				$categorie_pass = $this->passwords->lire($pass);
				if (!$categorie_pass || outils::decrypte($categorie_pass, $this->config['galerie_key']) !== $this->params['objet_actuel']['categorie_pass']) {
					$l = outils::genLink('?section=pass&cat=' . $this->params['objet_actuel']['categorie_id'], '', $this->params['objet_actuel']['categorie_nom'], 0, '&');
					header('Location: ' . $l);
					exit;
				}
			}
		}

		// Récupération des objets contenus dans la catégorie actuelle.
		$actuel = $this->params['objet_actuel']['categorie_chemin'];
		$actuel = ($actuel == '.') ? '' : $actuel;
		if (!empty($this->config['vignettes_cat_type'])) {
			$type = ',(' . MYSQL_PREF . 'categories.categorie_derniere_modif/' . MYSQL_PREF . 'categories.categorie_derniere_modif) AS type';
		} else {
			$type = '';
		}

		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'categories.*,
								 ' . MYSQL_PREF . 'images.image_chemin AS representant
								 ' . $type . '
			 FROM ' . MYSQL_PREF . 'categories,' . MYSQL_PREF . 'images 
			WHERE ' . MYSQL_PREF . 'categories.categorie_visible = "1" 
			  AND ' . MYSQL_PREF . 'categories.categorie_chemin REGEXP "^' . $actuel . '[^/]+/$" 
			  AND ' . MYSQL_PREF . 'categories.image_representant_id = ' . MYSQL_PREF . 'images.image_id'
					. $this->users_pass('categories.categorie_pass') . '
			ORDER BY ' . $this->config['vignettes_cat_type'] . str_replace('categorie', MYSQL_PREF . 'categories.categorie', $this->config['vignettes_cat_ordre']) . ' 
			LIMIT ' . $this->params['startnum'] . ',' . $this->params['limit_vignettes'];
		$this->params['objets'] = $this->mysql->select($mysql_requete);
		if (empty($this->params['objets'])) {
			die ('[' . __LINE__ . '] La base de données est vide.<br />' . mysql_error());
			exit;
		}

		// On récupère le nombre d'albums et de catégories totales
		// contenues dans la catégorie actuelle.
		$mysql_requete = 'SELECT categorie_derniere_modif FROM ' . MYSQL_PREF . 'categories
			WHERE categorie_visible = "1" AND 
			      categorie_chemin REGEXP "^' . $actuel . '[^/]+/$"'
				  . $this->users_pass('categories.categorie_pass');
		$objets = $this->mysql->select($mysql_requete);
		$this->template['stats']['nb_albums'] = 0;
		$this->template['stats']['nb_categories'] = 0;
		$this->params['nb_objets'] = 0;
		if (is_array($objets)) {
			$this->params['nb_objets'] = count($objets);
			for ($i = 0; $i < count($objets); $i++) {
				if (empty($objets[$i][0])) {
					$this->template['stats']['nb_categories']++;
				} else {
					$this->template['stats']['nb_albums']++;
				}
			}
		}

		// La categorie est-elle un album ?
		if ($this->params['objet_actuel']['categorie_derniere_modif'] > 0) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		$this->nouvelles_images();
		$this->stats_categorie();
		$this->hierarchie();
		$this->cat_voisines();
		$this->infos_vignettes();

		// On génère le tableau contenant toutes les infos catégories,
		// albums, images et vignettes pour le template.
		if (is_array($this->params['objets'])) {
		
			// On ne garde que le nombre de catégories en fonction du nombre de vignettes à afficher par page.
			for ($n = 0; $n < count($this->params['objets']); $n++) {

				// Catégorie ou album ?
				if (empty($this->params['objets'][$n]['categorie_derniere_modif'])) {
					$this->template['vignettes'][$n]['type'] = 'categorie';
					$this->template['vignettes'][$n]['page'] = outils::genLink('?cat=' . $this->params['objets'][$n]['categorie_id'], '', $this->params['objets'][$n]['categorie_nom']);
				} else {
					$this->template['vignettes'][$n]['type'] = 'album';
					$this->template['vignettes'][$n]['page'] = outils::genLink('?alb=' . $this->params['objets'][$n]['categorie_id'], '', $this->params['objets'][$n]['categorie_nom']);
				}

				// On détermine le nombre d'images récentes.
				if ($this->choix['recent'] 
				 && $this->params['objets'][$n]['categorie_dernier_ajout'] > $this->params['time_limit']) {
					if ($this->config['galerie_recent_nb'] && $this->choix['sn']) {
						$mysql_requete = 'SELECT COUNT(*) FROM ' . MYSQL_PREF . 'images WHERE 
								image_chemin LIKE "' . $this->params['objets'][$n]['categorie_chemin'] . '%" AND 
								image_date > ' . $this->params['time_limit'] . ' AND 
								image_visible = "1"' . $this->images_protect();
						$this->template['vignettes'][$n]['recent'] = $this->mysql->select($mysql_requete, 5);
					} else {
						$this->template['vignettes'][$n]['recent'] = -1;
					}
				}

				// Autres informations.
				$this->template['vignettes'][$n]['id'] = $this->params['objets'][$n]['categorie_id'];
				$this->template['vignettes'][$n]['chemin'] = $this->params['objets'][$n]['representant'];
				$this->template['vignettes'][$n]['nom'] = outils::html_specialchars(strip_tags($this->params['objets'][$n]['categorie_nom']));
				$this->template['vignettes'][$n]['description'] = str_replace('&', '&amp;', nl2br($this->params['objets'][$n]['categorie_description']));
				$this->template['vignettes'][$n]['poids'] = $this->params['objets'][$n]['categorie_poids'];
				$this->template['vignettes'][$n]['nb_images'] = $this->params['objets'][$n]['categorie_images'];
				$this->template['vignettes'][$n]['nb_hits'] = $this->params['objets'][$n]['categorie_hits'];
				$this->template['vignettes'][$n]['nb_commentaires'] = $this->params['objets'][$n]['categorie_commentaires'];
				$this->template['vignettes'][$n]['nb_votes'] = $this->params['objets'][$n]['categorie_votes'];
				$this->template['vignettes'][$n]['note'] = $this->params['objets'][$n]['categorie_note'];

				// Vérification mots de passe.
				$cat_pass = $this->params['objets'][$n]['categorie_pass'];
				$pass = preg_replace('`^(\d+):.+`', '$1', $cat_pass);
				$categorie_pass = $this->passwords->lire($pass);
				if (!$categorie_pass || outils::decrypte($categorie_pass, $this->config['galerie_key']) !== $cat_pass) {
					$this->template['vignettes'][$n]['pass'] = $cat_pass;

					// Droits.
					if ($cat_pass && isset($this->template['membre_user'][0]['groupe_album_pass_mode'])) {
						if ($this->template['membre_user'][0]['groupe_album_pass_mode'] != 'aucun') {
							if ($this->template['membre_user'][0]['groupe_album_pass_mode'] == 'tous') {
								$this->template['vignettes'][$n]['pass'] = '';
							} else {
								$passwords = unserialize($this->template['membre_user'][0]['groupe_album_pass']);
								if (in_array($cat_pass, $passwords)) {
									$this->template['vignettes'][$n]['pass'] = '';
								}
							}
						}
					}
				}

				// On réduit éventuellement les informations des objets.
				if (isset($this->params['categories_pass'][$this->params['objets'][$n]['categorie_chemin']])) {
					$pass_stats = $this->params['categories_pass'][$this->params['objets'][$n]['categorie_chemin']];
					$this->template['vignettes'][$n]['poids'] -= $pass_stats['poids'];
					$this->template['vignettes'][$n]['nb_images'] -= $pass_stats['images'];
					$this->template['vignettes'][$n]['nb_hits'] -= $pass_stats['hits'];
					$this->template['vignettes'][$n]['nb_commentaires'] -= $pass_stats['commentaires'];
					$this->template['vignettes'][$n]['nb_votes'] -= $pass_stats['votes'];
					if ($pass_stats['note'] > 0) {
						if ($this->template['vignettes'][$n]['nb_votes'] > 0) {
							$this->template['vignettes'][$n]['note'] = ($this->template['vignettes'][$n]['note']*2) - $pass_stats['note'];
						} else {
							$this->template['vignettes'][$n]['note'] = 0;
						}
					}
				}
			}
		}

		// Tags.
		$this->tags();

		// On n'a plus besoin de la bdd.
		$this->mysql->fermer();

		$this->liens_pages();
		$this->lien_retour();
		$this->description_categorie();
		$this->affichage_elements();

		$this->template['user']['vignettes'] = 0;
		$this->template['user']['ordre'] = 0;
		$this->user_autorisations();

		$this->template['infos']['nom'] = $this->params['objet_actuel']['categorie_nom'];

		// Message d'accueil.
		if ($this->params['objet_id'] == 1) {
			$this->template['infos']['accueil'] = $this->config['galerie_message_accueil'];
		}

		// Mode d'affichage des vignettes.
		if ($this->config['vignettes_cat_mode'] == 'etendue') {
			unset($this->template['infos']['description']);
			define('THUMB_CAT_COMPACT', FALSE);
		} else {
			define('THUMB_CAT_COMPACT', TRUE);
		}

		if (empty($this->template['infos']['pass'])) {
			$this->template['infos']['rss_objet'] = 1;
		}
	}



	/*
	 *	Albums.
	*/
	function album() {

		$this->nb_vignettes();
		$this->ordre();
		$this->infos_categorie();
		$this->deconnect();

		// L'album est-il protégé par un mot de passe ?
		if (!empty($this->params['objet_actuel']['categorie_pass'])) {

			$this->template['infos']['pass'] = 1;

			// Membres : droits d'accès.
			if (isset($this->template['membre_user'][0]['groupe_album_pass_mode'])) {
				$verif_pass = false;
				if ($this->template['membre_user'][0]['groupe_album_pass_mode'] != 'aucun') {
					if ($this->template['membre_user'][0]['groupe_album_pass_mode'] == 'tous') {
						$verif_pass = true;
					} else {
						$passwords = unserialize($this->template['membre_user'][0]['groupe_album_pass']);
						if (in_array($this->params['objet_actuel']['categorie_pass'], $passwords)) {
							$verif_pass = true;
						}
					}
				}
				if (!$verif_pass) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}
			
			// L'utilisateur a-t-il entré le bon mot de passe ?
			} else {
				$pass = preg_replace('`^(\d+):.+`', '$1', $this->params['objet_actuel']['categorie_pass']);
				$album_pass = $this->passwords->lire($pass);
				if (!$album_pass || outils::decrypte($album_pass, $this->config['galerie_key']) !== $this->params['objet_actuel']['categorie_pass']) {
					$l = outils::genLink('?section=pass&alb=' . $this->params['objet_actuel']['categorie_id'], '', $this->params['objet_actuel']['categorie_nom'], 0, '&');
					header('Location: ' . $l);
					exit;
				}
			}

		}

		// On récupère toutes les informations des images correspondant aux vignettes à afficher sur la page.
		$this->params['limit_vignettes'] = $this->choix['vn'] * $this->choix['vl'];
		$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'images 
			WHERE image_chemin LIKE "' . $this->params['objet_actuel']['categorie_chemin'] . '%" 
			AND image_visible="1" ORDER BY ' . $this->mysql_order() . '
			LIMIT ' . $this->params['startnum'] . ',' . $this->params['limit_vignettes'];
		$this->params['objets'] = $this->mysql->select($mysql_requete);
		if (empty($this->params['objets'])) {
			die ('[' . __LINE__ . '] La base de données est vide.<br />' . mysql_error());
		}

		// L'objet actuel est-il une catégorie ?
		if ($this->params['objet_actuel']['categorie_derniere_modif'] == 0) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		$this->nouvelles_images();
		$this->stats_categorie();
		$this->hierarchie();
		$this->cat_voisines();

		// Tags.
		$this->tags();

		// On n'a plus besoin de la bdd.
		$this->mysql->fermer();

		$this->vignettes_album();
		$this->liens_pages();
		$this->lien_retour();
		$this->description_categorie();
		$this->infos_vignettes();
		$this->affichage_elements();
		$this->user_autorisations();

		$this->template['infos']['nom'] = $this->params['objet_actuel']['categorie_nom'];

		// La description doit-elle être affichée ?
		if ($this->config['vignettes_cat_mode'] == 'etendue') {
			unset($this->template['infos']['description']);
		}

		if (empty($this->template['infos']['pass'])) {
			$this->template['infos']['rss_objet'] = 1;
		}
		
	}



	/*
	 *	Images.
	*/
	function image() {

		$this->nb_vignettes();
		$this->ordre();

		// Favori ?
		if (isset($_GET['addfav']) &&
		    $this->template['infos']['membres_connexion'] === false) {
			if ($_GET['addfav'] == 0) {
				$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'favoris
										WHERE image_id = "' . $this->params['objet_id'] . '"
										  AND user_id = "' . $this->template['membre_user'][0]['user_id'] . '"';
				$this->mysql->requete($mysql_requete);
			}
			if ($_GET['addfav'] == 1) {
				$mysql_requete = 'SELECT fav_id
								   FROM ' . MYSQL_PREF . 'favoris
  								  WHERE image_id = "' . $this->params['objet_id'] . '"
 								    AND user_id = "' . $this->template['membre_user'][0]['user_id'] . '"';
				$test = $this->mysql->select($mysql_requete);
				if (!is_array($test)) {
					$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'favoris (image_id, user_id)
										   VALUES ("' . $this->params['objet_id'] . '",
												   "' . $this->template['membre_user'][0]['user_id'] . '")';
					$this->mysql->requete($mysql_requete);
				}
			}
		}

		// On récupère toutes les informations de l'image à afficher.
		$user_id = (isset($this->template['membre_user'][0]['user_id'])) ? $this->template['membre_user'][0]['user_id'] : 0;
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.*,
								 ' . MYSQL_PREF . 'favoris.user_id,
								 ' . MYSQL_PREF . 'users.user_login
							FROM ' . MYSQL_PREF . 'images JOIN ' . MYSQL_PREF . 'users USING (user_id)
					   LEFT JOIN ' . MYSQL_PREF . 'favoris
					          ON ' . MYSQL_PREF . 'images.image_id = ' . MYSQL_PREF . 'favoris.image_id
							 AND ' . MYSQL_PREF . 'favoris.user_id = "' . $user_id . '"
						   WHERE ' . MYSQL_PREF . 'images.image_id = "' . $this->params['objet_id'] . '" 
							 AND ' . MYSQL_PREF . 'images.image_visible = "1"';
		$this->params['objet_actuel'] = $this->mysql->select($mysql_requete, 11);
		$this->template['image'] = $this->params['objet_actuel'];
		$this->template['image']['image_chemin'] = GALERIE_ALBUMS . '/' . $this->template['image']['image_chemin'];
		$this->template['infos']['objet'] = $this->params['objet_id'];
		if (!is_array($this->params['objet_actuel']) || !$this->params['objet_actuel']['image_visible']) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// Correction hauteur si l'option texte sur image est activée.
		if (IMG_TEXTE) {
			$this->template['image']['image_hauteur'] = $this->template['image']['image_hauteur'] + $this->config['galerie_images_text_correction'];
		} else {
			$this->config['galerie_images_text_correction'] = 0;
		}

		$this->deconnect();

		// L'image est-elle protégée par un mot de passe ?
		if (!empty($this->params['objet_actuel']['image_pass'])) {

			$this->template['infos']['pass'] = 1;

			// Membres : droits d'accès.
			if (isset($this->template['membre_user'][0]['groupe_album_pass_mode'])) {
				$verif_pass = false;
				if ($this->template['membre_user'][0]['groupe_album_pass_mode'] != 'aucun') {
					if ($this->template['membre_user'][0]['groupe_album_pass_mode'] == 'tous') {
						$verif_pass = true;
					} else {
						$passwords = unserialize($this->template['membre_user'][0]['groupe_album_pass']);
						if (in_array($this->params['objet_actuel']['image_pass'], $passwords)) {
							$verif_pass = true;
						}
					}
				}
				if (!$verif_pass) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

			// L'utilisateur a-t-il entré le bon mot de passe ?
			} else {
				$pass = preg_replace('`^(\d+):.+`', '$1', $this->params['objet_actuel']['image_pass']);
				$image_pass = $this->passwords->lire($pass);
				if (!$image_pass || outils::decrypte($image_pass, $this->config['galerie_key']) !== $this->params['objet_actuel']['image_pass']) {
					$l = outils::genLink('?section=pass&img=' . $this->params['objet_actuel']['image_id'], $this->params['objet_actuel']['image_nom'], '', 0, '&');
					header('Location: ' . $l);
					exit;
				}
			}

		}

		$this->hierarchie();
		$this->note();
		$this->new_comment();
		$this->get_comments();

		// Visites.
		$this->hits();

		// S'agit-il d'une recherche ?
		if (isset($_GET['search'])) {
			$this->recherche(1);
			$this->template['nav']['voisines'] = $this->params['objets'];
			$this->template['infos']['objet'] = $_GET['img'];
			$this->template['infos']['special'] = 'search';

		// S'agit-il d'un affichage "spécial" ?
		} elseif (isset($_GET['images']) ||
			isset($_GET['recentes']) ||
			isset($_GET['hits']) ||
			isset($_GET['commentaires']) ||
			isset($_GET['votes']) ||
			isset($_GET['tag']) ||
			isset($_GET['mimg']) ||
			isset($_GET['mfav']) ||
			isset($_GET['date_creation']) ||
			isset($_GET['date_ajout'])) {

			$cat = 1;
			if (isset($_GET['images'])) {
				$this->params['objet_type'] = 'images';
				$cat = $_GET['images'];
			} elseif (isset($_GET['recentes'])) {
				$this->nouvelles_images();
				$this->params['objet_type'] = 'recentes';
				$cat = $_GET['recentes'];
			} elseif (isset($_GET['hits'])) {
				$this->params['objet_type'] = 'hits';
				$cat = $_GET['hits'];
			} elseif (isset($_GET['commentaires'])) {
				$this->params['objet_type'] = 'commentaires';
				$cat = $_GET['commentaires'];
			} elseif (isset($_GET['votes'])) {
				$this->params['objet_type'] = 'votes';
				$cat = $_GET['votes'];
			} elseif (isset($_GET['date_creation'])) {
				$this->params['objet_type'] = 'date_creation';
				$cat = (isset($_GET['cat'])) ? $_GET['cat'] : $cat;
				$cat = (isset($_GET['alb'])) ? $_GET['alb'] : $cat;
			} elseif (isset($_GET['date_ajout'])) {
				$this->params['objet_type'] = 'date_ajout';
				$cat = (isset($_GET['cat'])) ? $_GET['cat'] : $cat;
				$cat = (isset($_GET['alb'])) ? $_GET['alb'] : $cat;
			} elseif (isset($_GET['tag'])) {
				$this->params['objet_type'] = 'tag';
				$cat = (isset($_GET['cat'])) ? $_GET['cat'] : $cat;
				$cat = (isset($_GET['alb'])) ? $_GET['alb'] : $cat;
			}
			$this->template['infos']['special'] = $this->params['objet_type'];

			// On récupère les informations basiques de l'objet.
			if (preg_match('`^\d{1,12}$`', $cat)) {
				$mysql_requete = 'SELECT categorie_id,
										 categorie_nom,
										 categorie_chemin,
										 categorie_derniere_modif  
					FROM ' . MYSQL_PREF . 'categories 
					WHERE categorie_id = "' . $cat . '"';
				$this->template['infos']['special_cat'] = $this->mysql->select($mysql_requete);
				if ($this->template['infos']['special_cat'] == 'vide') {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}
				$this->params['objet_actuel']['categorie_chemin'] = $this->template['infos']['special_cat'][0]['categorie_chemin'];
			} else {
				header('Location: ' . outils::genLink('?cat=1'));
				exit;
			}

			// Lien historique.
			$type = ($this->template['infos']['special_cat'][0]['categorie_derniere_modif']) ? 'alb' : 'cat';
			$this->template['historique']['lien'] = $type . '=' . $cat;

			// On récupère les informations de toutes les images.
			if (isset($_GET['mfav'])) {
				$this->get_membres_favoris();
			} elseif (isset($_GET['mimg'])) {
				$this->get_membres_images();
			} elseif (isset($_GET['tag'])) {
				$this->get_images_tag();
			} else {
				$this->get_speciales(1);
			}
			$this->template['nav']['voisines'] = $this->params['objets'];
			$this->template['infos']['nb_images'] = $this->params['nb_objets'];

		} else {

			// On récupère les informations de base de toutes les images de l'album.
			$mysql_requete = 'SELECT image_id,image_nom FROM ' . MYSQL_PREF . 'images 
				WHERE image_chemin LIKE "' . dirname($this->params['objet_actuel']['image_chemin']) . '/%" 
				AND image_visible="1" 
				ORDER BY ' . $this->mysql_order();
			$this->template['nav']['voisines'] = $this->mysql->select($mysql_requete);
		}

		// On n'a plus besoin de la bdd.
		$this->mysql->fermer();

		// On génère les liens pour la navigation entre les pages.
		if (is_array($this->template['nav']['voisines'])) {
			$this->template['nav']['premiere'][1] = $this->template['nav']['voisines'][0]['image_id'];
			if (isset($this->template['nav']['voisines'][0]['image_nom'])) {
				$this->template['nav']['premiere']['image_nom'] = $this->template['nav']['voisines'][0]['image_nom'];
			}
			for ($i = 0; $i < count($this->template['nav']['voisines']); $i++) {
				if ($this->template['nav']['voisines'][$i]['image_id'] == $this->template['image']['image_id']) {
					if (isset($this->template['nav']['voisines'][$i-1]['image_id'])) {
						$this->template['nav']['precedente'][1] = $this->template['nav']['voisines'][$i-1]['image_id'];
						if (isset($this->template['nav']['voisines'][$i-1]['image_nom'])) {
							$this->template['nav']['precedente']['image_nom'] = $this->template['nav']['voisines'][$i-1]['image_nom'];
						}
					}
					$this->template['infos']['objet_num'] = $i+1;
					if (isset($this->template['nav']['voisines'][$i+1]['image_id'])) {
						$this->template['nav']['suivante'][1] = $this->template['nav']['voisines'][$i+1]['image_id'];
						if (isset($this->template['nav']['voisines'][$i+1]['image_nom'])) {
							$this->template['nav']['suivante']['image_nom'] = $this->template['nav']['voisines'][$i+1]['image_nom'];
						}
					}
					break;
				}
			}
			$this->template['nav']['derniere'][1] = $this->template['nav']['voisines'][count($this->template['nav']['voisines'])-1]['image_id'];
			if (isset($this->template['nav']['voisines'][count($this->template['nav']['voisines'])-1]['image_nom'])) {
				$this->template['nav']['derniere']['image_nom'] = $this->template['nav']['voisines'][count($this->template['nav']['voisines'])-1]['image_nom'];
			}
		}

		$this->lien_retour();

		// Autorisation des choix utilisateurs.
		$this->config['user_image_ajust'] = ($this->config['galerie_images_resize'] == 2) ? 0 : $this->config['user_image_ajust'];
		$this->perso_image();
		if ($this->config['user_perso']) {
			$this->template['user']['style'] = $this->config['user_style'];
			if ($this->template['user']['image_taille'] ||
			    $this->template['user']['style']) {
				$this->template['user']['perso'] = 1;
			} else {
				$this->template['user']['perso'] = 0;
			}
		} else {
			$this->template['user']['perso'] = 0;
		}

		// Informations EXIF.
		$this->template['infos']['exif'] = 0;
		$infos_exif = 0;
		if ($this->config['active_exif'] && function_exists('read_exif_data')
		&& (strtolower(substr($this->template['image']['image_chemin'], -4)) == '.jpg' || strtolower(substr($this->template['image']['image_chemin'], -5)) == '.jpeg')) {
			$exif = @read_exif_data($this->template['image']['image_chemin'], 'ANY_TAG', true, false);
			if ($exif) {
				$this->template['infos']['exif'] = 1;
				$gep = unserialize($this->config['galerie_exif_params']);
				foreach ($gep as $section => $tags) {
					foreach ($tags as $tag => $params) {
						if ($params['active']) {
							if (isset($exif[$section][$tag])) {
								switch ($params['method']) {
									case 'simple' :
										$temp = $this->exif_simple($params, $exif[$section][$tag]);
										break;
									case 'date' :
										$temp = $this->exif_date($params, $exif[$section][$tag]);
										break;
									case 'nombre' :
										$temp = $this->exif_nombre($params, $exif[$section][$tag]);
										break;
									case 'liste' :
										$temp = $this->exif_liste($params, $exif[$section][$tag]);
										break;
									case 'version' :
										$temp = $this->exif_version($params, $exif[$section][$tag]);
										break;
								}
								if ($temp) {
									$this->template['exif']['infos'][$params['desc']] = $temp;
									$infos_exif = 1;
									$temp = '';
								}
							}
						}
					}
				}
				if (!$infos_exif) {
					$this->template['infos']['exif'] = 0;
				}
			}
		}

		// Informations IPTC
		$this->template['infos']['iptc'] = 0;
		if ($this->config['active_iptc']) {
			$size = @getimagesize($this->template['image']['image_chemin'], $info);
			if (is_array($info)) {
				$data = @iptcparse($info['APP13']);
				if (is_array($data)) {
					$iptc_champs = unserialize($this->config['galerie_iptc_params']);
					foreach ($data as $k => $v) {
						if (isset($iptc_champs[$k]) && $iptc_champs[$k]['active']) {
							$d = implode(', ', $v);
							$d = trim($d);
							if (!empty($d)) {
								$this->template['iptc']['infos'][$iptc_champs[$k]['nom']] = $d;
							}
						}
					}
				}
			}
		}

		$this->template['infos']['galerie_key'] = $this->config['galerie_key'];

		if (empty($this->template['infos']['pass'])) {
			$this->template['infos']['rss_objet'] = 1;
		}

		if (isset($_GET['img']) && isset($this->template['infos']['special_cat'])) {
			$this->template['infos']['parent_nom'] = $this->template['infos']['special_cat'][0]['categorie_nom'];
		}
	}



	/*
	 *	Formatage des informations EXIF.
	*/
	function exif_simple($params, $value) {
		if (isset($params['format'])) {
			$format = $params['format'];
		} else {
			$format = '%s';
		}
		return sprintf($format, $value);
	}
	function exif_date($params, $value) {
		if (preg_match('`(\d{4}):(\d{2}):(\d{2}) (\d{2}):(\d{2}):(\d{2})`', $value, $matches)) {
			$date = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			if (isset($params['format'])) {
				$format = $params['format'];
			} else {
				$format = '%d/%m/%Y %H:%M:%S';
			}
			if (!$date || $date == -1 || $value == '0000:00:00 00:00:00'
			 || (!$txtdate = @strftime($format, $date))) {
				return 'invalide';
			}
			return $txtdate;
		}
	}
	function exif_nombre($params, $value) {
		if (preg_match('`^[-0-9/+\*]{1,255}$`', $value)) {
			@eval("\$newval=$value;");
		} else {
			$newval = 'invalide';
		}
		return $this->exif_simple($params, $newval);
	}
	function exif_liste($params, $value) {
		$enums = $params['format'];
		if (isset($enums[$value]))  {
			return $this->exif_simple('', $enums[$value]);
		} else {
			return 'inconnu';
		}
	}
	function exif_version($params, $value) {
		if (strlen($value) < 5 && is_numeric($value)) {
			$version = sscanf($value, '%2d%2d');
			return sprintf('%d.%d', $version[0], $version[1]);
		}
	}



	
	function get_membres_favoris() {

		if (!$this->config['users_membres_active']
		 || !isset($_GET['mfav'])
		 || !preg_match('`^[-_a-z\d]{1,50}$`i', $_GET['mfav'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		$fromwhere = ' FROM ' . MYSQL_PREF . 'images,
							' . MYSQL_PREF . 'users,
						    ' . MYSQL_PREF . 'favoris
					  WHERE ' . MYSQL_PREF . 'users.user_login = "' . $_GET['mfav'] . '"
						AND ' . MYSQL_PREF . 'favoris.user_id = ' . MYSQL_PREF . 'users.user_id
						AND ' . MYSQL_PREF . 'favoris.image_id = ' . MYSQL_PREF . 'images.image_id
						AND ' . MYSQL_PREF . 'images.image_visible = "1" '
						      . $this->images_protect(MYSQL_PREF . 'images.image');
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_id,
								 ' . MYSQL_PREF . 'images.image_nom '
								   . $fromwhere . '
						ORDER BY ' . MYSQL_PREF . 'favoris.fav_id DESC';
		$this->params['objets'] = $this->mysql->select($mysql_requete);
		if (!is_array($this->params['objets'])) {
			header('Location: ' . outils::genLink('?img=' . $this->params['objet_actuel']['image_id'], $this->params['objet_actuel']['image_nom']));
			exit;
		}

		// On vérifie si l'image actuelle se trouve bien dans les favoris.
		$ok = false;
		for ($i = 0; $i < count($this->params['objets']); $i++) {
			if ($this->params['objets'][$i]['image_id'] == $this->params['objet_actuel']['image_id']) {
				$ok = true;
				break;
			}
		}
		if ($ok === false) {
			header('Location: ' . outils::genLink('?img=' . $this->params['objet_actuel']['image_id'], $this->params['objet_actuel']['image_nom']));
			exit;
		}

		// On récupère le nombre d'images correspondant aux critères du SELECT.
		$mysql_requete = 'SELECT COUNT(*) ' . $fromwhere;
		$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);
		$this->template['infos']['nb_objets'] = $this->params['nb_objets'];

	}



	
	function get_membres_images() {

		if (!$this->config['users_membres_active']
		 || !isset($_GET['mimg'])
		 || !preg_match('`^[-_a-z\d]{1,50}$`i', $_GET['mimg'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// Date d'ajout ?
		$mysql_date = '';
		if (isset($_GET['date_ajout'])) {
			$date = $_GET['date_ajout'];
			if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $date, $m)) {
				$date_debut = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
				$date_fin = mktime(23, 59, 59, $m[2], $m[1], $m[3]);
			} else {
				$date = getdate(time());
				$date_debut = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
				$date_fin = mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);
			}
			$mysql_date = ' AND ' . MYSQL_PREF . 'images.image_date >= ' . $date_debut . ' 
						   AND ' . MYSQL_PREF . 'images.image_date <= ' . $date_fin;
		}

		$fromwhere = ' FROM ' . MYSQL_PREF . 'images,
						    ' . MYSQL_PREF . 'users
					  WHERE ' . MYSQL_PREF . 'users.user_login = "' . $_GET['mimg'] . '"
						AND ' . MYSQL_PREF . 'images.user_id = ' . MYSQL_PREF . 'users.user_id
						AND ' . MYSQL_PREF . 'images.image_visible = "1" '
							  . $mysql_date
						      . $this->images_protect(MYSQL_PREF . 'images.image');
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_id,
								 ' . MYSQL_PREF . 'images.image_nom '
								   . $fromwhere . '
						ORDER BY ' . $this->mysql_order();
		$this->params['objets'] = $this->mysql->select($mysql_requete);
		if (!is_array($this->params['objets'])) {
			header('Location: ' . outils::genLink('?img=' . $this->params['objet_actuel']['image_id'], $this->params['objet_actuel']['image_nom']));
			exit;
		}

		// On vérifie si l'image actuelle se trouve bien dans les images du membre.
		$ok = false;
		for ($i = 0; $i < count($this->params['objets']); $i++) {
			if ($this->params['objets'][$i]['image_id'] == $this->params['objet_actuel']['image_id']) {
				$ok = true;
				break;
			}
		}
		if ($ok === false) {
			header('Location: ' . outils::genLink('?img=' . $this->params['objet_actuel']['image_id'], $this->params['objet_actuel']['image_nom']));
			exit;
		}

		// On récupère le nombre d'images correspondant aux critères du SELECT.
		$mysql_requete = 'SELECT COUNT(*) ' . $fromwhere;
		$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);
		$this->template['infos']['nb_objets'] = $this->params['nb_objets'];
	}



	/*
	  *	Récupération des tags pour catégories et albums.
	*/
	function tags($nolimit = 0) {

		if ($this->config['active_tags']) {

			$path = ($this->params['objet_actuel']['categorie_chemin'] == '.') 
				  ? '' : $this->params['objet_actuel']['categorie_chemin'];

			// On récupère les tags par leur ordre d'importance.
			$this->template['tags'] = '';
			$limit = ($nolimit) ? '' : ' LIMIT ' . $this->config['galerie_nb_tags'];
			$mysql_requete = 'SELECT DISTINCT ' . MYSQL_PREF . 'tags.tag_id,
							  COUNT(tag_id) AS tag_nombre
					    FROM ' . MYSQL_PREF . 'tags INNER JOIN ' . MYSQL_PREF . 'images USING(image_id)
					   WHERE ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $path . '%" 
						 AND ' . MYSQL_PREF . 'images.image_visible = "1" 
							 ' . $this->images_protect(MYSQL_PREF . 'images.image') . '
					GROUP BY ' . MYSQL_PREF . 'tags.tag_id
					ORDER BY tag_nombre DESC,tag_id ASC' 
							   . $limit;
			$tags = $this->mysql->select($mysql_requete, 4);

			// On détermine le "poids" de chaque tag, compris entre 1 et 10.
			if (is_array($tags)) {
				$plus_grand = current($tags);
				$plus_grand = $plus_grand['tag_nombre'];
				$plus_petit = end($tags);
				$plus_petit = $plus_petit['tag_nombre'];
				reset($tags);
				$difference = $plus_grand - $plus_petit;
				$increment = ($difference === 0) ? 1 : $difference / 9;
				foreach ($tags as $tag => $infos) {
					$tags[$tag]['weight'] = 
						intval(($infos['tag_nombre'] - $plus_petit) / $increment)+1;
				}

				// On trie les tags.
				$sort_tags = array();
				foreach ($tags as $t => $i) {
					$sort_tags[] = $t;
				}
				natcasesort($sort_tags);
				foreach ($sort_tags as $t) {
					$this->template['tags'][$t] = $tags[$t];
				}
			}

		}
	}



	/*
	 *	Deconnexion.
	*/
	function deconnect() {
		if (!empty($_GET['deconnect'])) {

			// On supprime le code contenu dans le cookie.		
			if ($this->params['objet_type'] == 'img') {
				$cat = preg_replace('`^(\d+):.+`', '$1', $this->params['objet_actuel']['image_pass']);
			} else {
				$cat = preg_replace('`^(\d+):.+`', '$1', $this->params['objet_actuel']['categorie_pass']);
			}
			$this->passwords->effacer($cat);
			if (!$this->passwords->valeur) {
				$this->passwords->valeur = 1;
				$this->passwords->expire = time();
			}
			$this->passwords->ecrire();

			// On renvoie vers la catégorie parente de la catégorie protégée première.
			$mysql_requete = 'SELECT categorie_chemin FROM ' . MYSQL_PREF . 'categories 
				WHERE categorie_id = "' . $cat . '"';
			$chemin = $this->mysql->select($mysql_requete, 5);
			$path = dirname($chemin);
			if ($path == '.') {
				$l = outils::genLink('?cat=1');
				header('Location: ' . $l);
				exit;
			} else {
				$mysql_requete = 'SELECT categorie_id,
										 categorie_nom,
										 categorie_derniere_modif
									FROM ' . MYSQL_PREF . 'categories 
								   WHERE categorie_chemin = "' . $path . '/"';
				$infos = $this->mysql->select($mysql_requete);
				if (!is_array($infos)) {
					$parent_id = 1;
					$type = 'cat';
					$nom = '';
				} else {
					$parent_id = $infos[0]['categorie_id'];
					$type = ($infos[0]['categorie_derniere_modif']) ? 'alb' : 'cat';
					$nom = $infos[0]['categorie_nom'];
				}
				$l = outils::genLink('?' . $type . '=' . $parent_id, '', $nom);
				header('Location: ' . $l);
				exit;
			}
		}
	}



	/*
	 *	Pages spéciales : images, hits, votes, commentaires et images récentes.
	*/
	function speciales() {

		$this->nb_vignettes();
		$this->ordre();
		if (isset($_GET['date_creation']) || isset($_GET['date_ajout'])) {

			$type = (isset($_GET['alb'])) ? 'alb' : 'cat';

			// Identifiant de l'objet.
			if (!empty($_GET[$type]) && preg_match('`^[1-9]\d{0,9}$`', $_GET[$type])) {
				$this->params['objet_id'] = $_GET[$type];
				
			} else {
				$this->params['objet_id'] = 1;
			}
		}

		$this->infos_categorie();

		// L'objet est-il protégé par un mot de passe ?
		if (!empty($this->params['objet_actuel']['categorie_pass'])) {

			$this->template['infos']['pass'] = 1;

			// Membres : droits d'accès.
			if (isset($this->template['membre_user'][0]['groupe_album_pass_mode'])) {
				$verif_pass = false;
				if ($this->template['membre_user'][0]['groupe_album_pass_mode'] != 'aucun') {
					if ($this->template['membre_user'][0]['groupe_album_pass_mode'] == 'tous') {
						$verif_pass = true;
					} else {
						$passwords = unserialize($this->template['membre_user'][0]['groupe_album_pass']);
						if (in_array($this->params['objet_actuel']['categorie_pass'], $passwords)) {
							$verif_pass = true;
						}
					}
				}
				if (!$verif_pass) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

			// L'utilisateur a-t-il entré le bon mot de passe ?
			} else {
				$pass = preg_replace('`^(\d+):.+`', '$1', $this->params['objet_actuel']['categorie_pass']);
				$categorie_pass = $this->passwords->lire($pass);
				if (!$categorie_pass || outils::decrypte($categorie_pass, $this->config['galerie_key']) !== $this->params['objet_actuel']['categorie_pass']) {
					$l = outils::genLink('?section=pass&' . $this->params['objet_type'] . '=' . $this->params['objet_actuel']['categorie_id'], '', $this->params['objet_actuel']['categorie_nom'], 0, '&');
					header('Location: ' . $l);
					exit;
				}
			}
		}

		$this->nouvelles_images();
		$this->get_speciales();
		$this->tags();

		// On n'a plus besoin de la bdd.
		$this->mysql->fermer();

		$this->vignettes_album();
		$this->liens_pages();
		$this->infos_vignettes();
		$this->affichage_elements();

		if (!empty($this->template['display']['s_hits']) ||
		    !empty($this->template['display']['s_comments']) || 
		    !empty($this->template['display']['s_votes']) || 
		    !empty($this->template['display']['s_recentes'])) {
			$this->template['display']['infos'] = 1;
		}

		$this->user_autorisations();

		if (isset($this->template['infos']['hvc']['nom'])) {
			$this->template['infos']['parent_nom'] = $this->template['infos']['hvc']['nom'];
		} elseif (isset($this->template['historique']['objet_nom'])) {
			$this->template['infos']['parent_nom'] = $this->template['historique']['objet_nom'];
		}
	}



	/*
	  *	Favoris des membres.
	*/
	function membres_favoris() {

		if (!$this->config['users_membres_active']
		 || !isset($_GET['mfav'])
		 || !preg_match('`^[-_a-z\d]{1,50}$`i', $_GET['mfav'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		$this->nb_vignettes();
		$this->ordre();
		$this->nouvelles_images();

		$fromwhere = ' FROM ' . MYSQL_PREF . 'images,
							' . MYSQL_PREF . 'users,
						    ' . MYSQL_PREF . 'favoris
					  WHERE ' . MYSQL_PREF . 'users.user_login = "' . $_GET['mfav'] . '"
						AND ' . MYSQL_PREF . 'favoris.user_id = ' . MYSQL_PREF . 'users.user_id
						AND ' . MYSQL_PREF . 'favoris.image_id = ' . MYSQL_PREF . 'images.image_id
						AND ' . MYSQL_PREF . 'images.image_visible = "1" '
						      . $this->images_protect(MYSQL_PREF . 'images.image');
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.* '
								   . $fromwhere . '
						ORDER BY ' . MYSQL_PREF . 'favoris.fav_id DESC
						   LIMIT ' . $this->params['startnum'] . ',' . $this->params['limit_vignettes'];
		$this->params['objets'] = $this->mysql->select($mysql_requete);
		if (empty($this->params['objets'])) {
			die ('[' . __LINE__ . '] La base de données est vide.<br />' . mysql_error());
		}

		// On compte le nombre de résultats.
		$mysql_requete = 'SELECT COUNT(*) ' . $fromwhere;
		$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);
		$this->template['infos']['nb_objets'] = $this->params['nb_objets'];
		if ($this->template['infos']['nb_objets'] == 0) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// On n'a plus besoin de la bdd.
		$this->mysql->fermer();

		$this->vignettes_album();
		$this->liens_pages();
		$this->infos_vignettes();
		$this->affichage_elements();
		$this->user_autorisations();

		$this->template['infos']['tags'] = 0;
		$this->template['infos']['type'] = 'mfav';
		$this->template['infos']['objet'] = $_GET['mfav'];
		$this->template['infos']['nom'] = 'favoris de ' . str_replace('_', ' ', $_GET['mfav']);
	}



	/*
	  *	Images des membres.
	*/
	function membres_images() {

		if (!$this->config['users_membres_active']
		 || !isset($_GET['mimg'])
		 || !preg_match('`^[-_a-z\d]{1,50}$`i', $_GET['mimg'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		$this->nb_vignettes();
		$this->ordre();
		$this->nouvelles_images();

		// Date d'ajout ?
		$mysql_date = '';
		if (isset($_GET['date_ajout'])) {
			$date = $_GET['date_ajout'];
			if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $date, $m)) {
				$date_debut = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
				$date_fin = mktime(23, 59, 59, $m[2], $m[1], $m[3]);
			} else {
				$date = getdate(time());
				$date_debut = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
				$date_fin = mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);
			}
			$mysql_date = ' AND ' . MYSQL_PREF . 'images.image_date >= ' . $date_debut . ' 
						   AND ' . MYSQL_PREF . 'images.image_date <= ' . $date_fin;
		}

		$fromwhere = ' FROM ' . MYSQL_PREF . 'images,
						    ' . MYSQL_PREF . 'users
					  WHERE ' . MYSQL_PREF . 'users.user_login = "' . $_GET['mimg'] . '"
						AND ' . MYSQL_PREF . 'images.user_id = ' . MYSQL_PREF . 'users.user_id
						AND ' . MYSQL_PREF . 'images.image_visible = "1" '
							  . $mysql_date
						      . $this->images_protect(MYSQL_PREF . 'images.image');
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.* '
								   . $fromwhere . '
						ORDER BY ' . $this->mysql_order() . '
						   LIMIT ' . $this->params['startnum'] . ',' . $this->params['limit_vignettes'];
		$this->params['objets'] = $this->mysql->select($mysql_requete);
		if (empty($this->params['objets'])) {
			die ('[' . __LINE__ . '] La base de données est vide.<br />' . mysql_error());
		}

		// On compte le nombre de résultats.
		$mysql_requete = 'SELECT COUNT(*) ' . $fromwhere;
		$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);
		$this->template['infos']['nb_objets'] = $this->params['nb_objets'];
		if ($this->template['infos']['nb_objets'] == 0) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// On n'a plus besoin de la bdd.
		$this->mysql->fermer();

		$this->vignettes_album();
		$this->liens_pages();
		$this->infos_vignettes();
		$this->affichage_elements();
		$this->user_autorisations();

		$this->template['infos']['tags'] = 0;
		$this->template['infos']['type'] = 'mimg';
		$this->template['infos']['objet'] = $_GET['mimg'];
		$this->template['infos']['nom'] = 'images de ' . str_replace('_', ' ', $_GET['mimg']);;
	}



	function membres_profil() {

		if (empty($_GET['profil']) || 
		   !preg_match('`^[-_a-z\d]{3,30}$`i', $_GET['profil'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// Personnalisation.
		if ($this->config['user_perso']) {
			$this->template['user']['style'] = $this->config['user_style'];
			if ($this->template['user']['style']) {
				$this->template['user']['perso'] = 1;
			} else {
				$this->template['user']['perso'] = 0;
			}
		} else {
			$this->template['user']['perso'] = 0;
		}

		$this->template['infos']['section_membres'] = 'profil';
		$this->template['infos']['title'] = $_GET['profil'];
		$this->membre_infos();

		// Nombre de commentaires.
		if ($this->config['galerie_page_comments']
		 && $this->config['active_commentaires']) {
			$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'commentaires.commentaire_id)
								FROM ' . MYSQL_PREF . 'commentaires,
									 ' . MYSQL_PREF . 'images,
									 ' . MYSQL_PREF . 'users
							   WHERE ' . MYSQL_PREF . 'commentaires.commentaire_visible = "1"
								 AND ' . MYSQL_PREF . 'commentaires.user_id = ' . MYSQL_PREF . 'users.user_id
								 AND ' . MYSQL_PREF . 'commentaires.image_id = ' . MYSQL_PREF . 'images.image_id
								 AND ' . MYSQL_PREF . 'images.image_visible = "1" '
									   . $this->images_protect(MYSQL_PREF . 'images.image') . '
								 AND ' . MYSQL_PREF . 'commentaires.user_id = "' . $this->template['membre_profil'][0]['user_id'] . '"';
			$this->template['membre_profil'][0]['nb_comments'] = $this->mysql->select($mysql_requete, 5);
		}

		// Nombre d'images envoyées
		$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'images.image_id)
							FROM ' . MYSQL_PREF . 'images JOIN ' . MYSQL_PREF . 'users USING (user_id)
						   WHERE ' . MYSQL_PREF . 'images.image_visible = "1"
							 AND ' . MYSQL_PREF . 'images.user_id = "' . $this->template['membre_profil'][0]['user_id'] . '" '
								   . $this->images_protect(MYSQL_PREF . 'images.image');
		$this->template['membre_profil'][0]['nb_images'] = $this->mysql->select($mysql_requete, 5);

		// Nombre de favoris.
		$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'favoris.image_id)
							FROM ' . MYSQL_PREF . 'favoris JOIN ' . MYSQL_PREF . 'images USING (image_id)
						   WHERE ' . MYSQL_PREF . 'images.image_visible = "1"
							 AND ' . MYSQL_PREF . 'favoris.user_id = "' . $this->template['membre_profil'][0]['user_id'] . '" '
								   . $this->images_protect(MYSQL_PREF . 'images.image');
		$this->template['membre_profil'][0]['nb_favoris'] = $this->mysql->select($mysql_requete, 5);

		$this->template['infos']['title'] = $this->template['membre_profil'][0]['user_nom'];

		if ($this->template['membre_profil'][0]['groupe_commentaires']
		 || $this->template['membre_profil'][0]['groupe_upload']) {
			$this->template['infos']['rss_objet'] = 1;
		}
		$this->template['infos']['objet'] = $_GET['profil'];
		$this->template['infos']['type'] = 'profil';

	}



	/*
	  *	Ordre de tri pour les requêtes SQL.
	*/
	function mysql_order() {
		$date_creation_lenght = ($this->params['v_ordre'] == 'date_creation') ? 'LENGTH(' . MYSQL_PREF . 'images.image_date_creation) ' . $this->params['v_sens'] . ',' : '';
		$ordre_criteres = MYSQL_PREF . 'images.image_' . $this->params['v_ordre'] . ' ' . $this->params['v_sens'];
		if ($this->params['v_ordre'] == 'votes') {
			$ordre_criteres .= ', ' . MYSQL_PREF . 'images.image_note ' . $this->params['v_sens'];
		} elseif ($this->params['v_ordre'] == 'note') {
			$ordre_criteres .= ', ' . MYSQL_PREF . 'images.image_votes ' . $this->params['v_sens'];
		}
		$order = $date_creation_lenght . $ordre_criteres;
		return $order . ',' . MYSQL_PREF . 'images.image_id ' . $this->params['v_sens'];
	}



	/*
	  *	Commentaires des membres.
	*/
	function membres_commentaires() {

		if (!$this->config['galerie_page_comments']
		 || !$this->config['active_commentaires']
		 || !$this->config['users_membres_active']
		 || !isset($_GET['mcom'])
		 || !preg_match('`^[-_a-z\d]{1,50}$`i', $_GET['mcom'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		$this->template['infos']['objet'] = $_GET['mcom'];
		$this->template['infos']['type'] = 'mcom';
		$this->params['limit_vignettes'] = $this->config['galerie_page_comments_nb'];

		$fromwhere = 'FROM ' . MYSQL_PREF . 'categories,
						    ' . MYSQL_PREF . 'images,
						    ' . MYSQL_PREF . 'commentaires
				  LEFT JOIN ' . MYSQL_PREF . 'users
					     ON ' . MYSQL_PREF . 'commentaires.user_id = ' . MYSQL_PREF . 'users.user_id
					  WHERE ' . MYSQL_PREF . 'users.user_login = "' . $_GET['mcom'] . '"
						AND ' . MYSQL_PREF . 'images.image_commentaires > 0
						AND ' . MYSQL_PREF . 'commentaires.commentaire_visible = "1" 
						AND ' . MYSQL_PREF . 'images.image_visible = "1" 
						AND ' . MYSQL_PREF . 'commentaires.image_id = ' . MYSQL_PREF . 'images.image_id
						AND ' . MYSQL_PREF . 'categories.categorie_id = ' . MYSQL_PREF . 'images.categorie_parent_id '
							  . $this->images_protect(MYSQL_PREF . 'images.image');

		// Récupération des commentaires.
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'commentaires.commentaire_id,
								 ' . MYSQL_PREF . 'commentaires.commentaire_date,
								 ' . MYSQL_PREF . 'commentaires.commentaire_auteur,
								 ' . MYSQL_PREF . 'commentaires.commentaire_web,
								 ' . MYSQL_PREF . 'commentaires.commentaire_message,
								 ' . MYSQL_PREF . 'categories.categorie_nom,
								 ' . MYSQL_PREF . 'categories.categorie_id,
								 ' . MYSQL_PREF . 'images.image_id,
								 ' . MYSQL_PREF . 'images.image_nom,
								 ' . MYSQL_PREF . 'images.image_chemin,
								 ' . MYSQL_PREF . 'images.image_largeur,
								 ' . MYSQL_PREF . 'images.image_hauteur,
								 ' . MYSQL_PREF . 'users.user_id,
								 ' . MYSQL_PREF . 'users.user_login
								 ' . $fromwhere . '
						ORDER BY ' . MYSQL_PREF . 'commentaires.commentaire_date DESC
						   LIMIT ' . $this->params['startnum'] . ',' . $this->params['limit_vignettes'];
		$this->template['commentaires'] = $this->mysql->select($mysql_requete);

		if (is_array($this->template['commentaires'])) {

			$this->template['infos']['parent_nom'] = $this->template['commentaires'][0]['categorie_nom'];

			// Récupération du nombre total de commentaires.
			$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'commentaires.commentaire_id) ' . $fromwhere;
			$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);

		} else {
			$this->params['nb_objets'] = 0;
		}

		$this->liens_pages();

		// On n'a plus besoin de la bdd.
		$this->mysql->fermer();

		$this->config['user_vignettes'] = 0;
		$this->user_autorisations();

		$this->template['infos']['nom'] = 'commentaires de ' . str_replace('_', ' ', $_GET['mcom']);

	}



	/*
	  *	Page des commentaires
	*/
	function page_commentaires() {

		if ($this->config['galerie_page_comments']) {

			$this->infos_categorie();
			$this->template['infos_objet'] = $this->params['objet_actuel'];

			$this->params['limit_vignettes'] = $this->config['galerie_page_comments_nb'];

			$path = ($this->params['objet_actuel']['categorie_id'] == 1) ? '' : $this->params['objet_actuel']['categorie_chemin'];
			$where = MYSQL_PREF . 'categories.categorie_chemin LIKE "' . $path . '%"
								 AND ' . MYSQL_PREF . 'images.image_commentaires > 0
								 AND ' . MYSQL_PREF . 'commentaires.commentaire_visible = "1" 
								 AND ' . MYSQL_PREF . 'images.image_visible = "1" 
								 AND ' . MYSQL_PREF . 'commentaires.image_id = ' . MYSQL_PREF . 'images.image_id
								 AND ' . MYSQL_PREF . 'categories.categorie_id = ' . MYSQL_PREF . 'images.categorie_parent_id '
									   . $this->images_protect(MYSQL_PREF . 'images.image');

			// Récupération des commentaires.
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'commentaires.commentaire_id,
									 ' . MYSQL_PREF . 'commentaires.commentaire_date,
									 ' . MYSQL_PREF . 'commentaires.commentaire_auteur,
									 ' . MYSQL_PREF . 'commentaires.commentaire_web,
									 ' . MYSQL_PREF . 'commentaires.commentaire_message,
									 ' . MYSQL_PREF . 'categories.categorie_nom,
									 ' . MYSQL_PREF . 'categories.categorie_id,
									 ' . MYSQL_PREF . 'images.image_id,
									 ' . MYSQL_PREF . 'images.image_nom,
									 ' . MYSQL_PREF . 'images.image_chemin,
									 ' . MYSQL_PREF . 'images.image_largeur,
									 ' . MYSQL_PREF . 'images.image_hauteur,
									 ' . MYSQL_PREF . 'users.user_id,
									 ' . MYSQL_PREF . 'users.user_login
								FROM ' . MYSQL_PREF . 'categories,
									 ' . MYSQL_PREF . 'images,
									 ' . MYSQL_PREF . 'commentaires
						   LEFT JOIN ' . MYSQL_PREF . 'users
								  ON ' . MYSQL_PREF . 'commentaires.user_id = ' . MYSQL_PREF . 'users.user_id
							   WHERE ' . $where . '
							ORDER BY ' . MYSQL_PREF . 'commentaires.commentaire_date DESC
							   LIMIT ' . $this->params['startnum'] . ',' . $this->params['limit_vignettes'];
			$this->template['commentaires'] = $this->mysql->select($mysql_requete);

			if (is_array($this->template['commentaires'])) {

				$this->template['infos']['parent_nom'] = $this->template['commentaires'][0]['categorie_nom'];

				// Récupération du nombre total de commentaires.
				$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'commentaires.commentaire_id)
									FROM ' . MYSQL_PREF . 'commentaires,
										 ' . MYSQL_PREF . 'images,
										 ' . MYSQL_PREF . 'categories
								   WHERE ' . $where;
				$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);

			} else {
				$this->params['nb_objets'] = 0;
			}

			$this->liens_pages();

			// On n'a plus besoin de la bdd.
			$this->mysql->fermer();

			$this->config['user_vignettes'] = 0;
			$this->user_autorisations();

		} else {

			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}
	}



	/*
	  *	Images taggées.
	*/
	function page_tags() {

		if ($this->config['active_tags']) {

			$this->nb_vignettes();
			$this->ordre();
			$this->nouvelles_images();

			// Récupération des informations de la catégorie / album.
			$type = (isset($_GET['alb'])) ? 'alb' : 'cat';
			$this->template['infos']['objet_type'] = $type;
			if (!empty($_GET[$type]) && preg_match('`^[1-9]\d{0,9}$`', $_GET[$type])) {
				$this->params['objet_id'] = $_GET[$type];
			} else {
				$this->params['objet_id'] = 1;
			}
			$this->infos_categorie();
			$this->template['infos_objet'] = $this->params['objet_actuel'];
			$this->template['infos']['parent_nom'] = $this->params['objet_actuel']['categorie_nom'];

			// Date d'ajout ?
			$mysql_date = '';
			if (isset($_GET['date_ajout'])) {
				$date = $_GET['date_ajout'];
				if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $date, $m)) {
					$date_debut = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
					$date_fin = mktime(23, 59, 59, $m[2], $m[1], $m[3]);
				} else {
					$date = getdate(time());
					$date_debut = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
					$date_fin = mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);
				}
				$mysql_date = ' AND ' . MYSQL_PREF . 'images.image_date >= ' . $date_debut . ' 
							   AND ' . MYSQL_PREF . 'images.image_date <= ' . $date_fin;
			}

			// On récupère les images correspondant au tag.
			$tag = htmlentities($_GET['tag']);
			$path = ($this->params['objet_actuel']['categorie_chemin'] == '.')
				? '' : $this->params['objet_actuel']['categorie_chemin'];

			$this->params['limit_vignettes'] = $this->choix['vn'] * $this->choix['vl'];
			$mysql_params = '  WHERE ' . MYSQL_PREF . 'tags.tag_id = "' . outils::protege_mysql($tag, $this->mysql->lien) . '"
								 AND ' . MYSQL_PREF . 'images.image_id = ' . MYSQL_PREF . 'tags.image_id
								 AND ' . MYSQL_PREF . 'images.image_visible = "1"
								 AND ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $path . '%"
								     ' . $this->images_protect(MYSQL_PREF . 'images.image')
									   . $mysql_date . '
							ORDER BY ' . $this->mysql_order();
			$mysql_requete = 'SELECT DISTINCT ' . MYSQL_PREF . 'images.*
								FROM ' . MYSQL_PREF . 'images,
									 ' . MYSQL_PREF . 'tags'
									   . $mysql_params . '
							   LIMIT ' . $this->params['startnum'] . ',' . $this->params['limit_vignettes'];
			$this->params['objets'] = $this->mysql->select($mysql_requete);
			if (!is_array($this->params['objets'])) {
				header('Location: ' . outils::genLink('?cat=1'));
				exit;
			}

			// Nombre d'images.
			$mysql_requete = 'SELECT COUNT(*)
								FROM ' . MYSQL_PREF . 'images,
									 ' . MYSQL_PREF . 'tags'
									   . $mysql_params;
			$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);

			// Tags.
			$this->tags();

			// On n'a plus besoin de la bdd.
			$this->mysql->fermer();

			$this->vignettes_album();
			$this->liens_pages();
			$this->infos_vignettes();
			$this->affichage_elements();
			$this->user_autorisations();

			if ($this->params['objet_actuel']['categorie_pass'] == '') {
				$this->template['infos']['rss_objet'] = 1;
			}

		} else {

			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}
	}



	/*
	 *	Recherche.
	*/
	function search() {

		$this->nb_vignettes();
		$this->ordre();
		$this->recherche();
		$this->nouvelles_images();

		// On n'a plus besoin de la bdd.
		$this->mysql->fermer();

		$this->vignettes_album();
		$this->liens_pages();
		$this->infos_vignettes();
		$this->affichage_elements();
		$this->user_autorisations();

	}



	/*
	  *	On génère une liste déroulante de tous les albums disponibles.
	*/
	function categories_list($categories, $path = '', $marge = 0, $only_cat = 0) {
		if (!is_array($categories)) {
			return;
		}
		foreach ($categories as $id => $infos) {
			if (preg_match('`^' . $path . '[^/]+/$`', $infos['categorie_chemin'])) {
				unset($categories[$id]);

				// Album.
				if ($infos['categorie_derniere_modif'] > 0 && !$only_cat) {
					$nom = str_repeat('&nbsp;', $marge*5) . '|--&nbsp;' . htmlentities($infos['categorie_nom']);
					$this->template['categories_list'] .= '<option class="alb" value="' . $infos['categorie_id'] . '">' . $nom . '</option>';

				// Catégorie.
				} else {
					$disabled = ($only_cat) ? '' : ' disabled="disabled"';
					$value = ($only_cat) ? $infos['categorie_id'] : '0';
					$class = ($only_cat) ? '' : ' class="cat"';
					$nom = str_repeat('&nbsp;', $marge*5) . htmlentities($infos['categorie_nom']);
					$this->template['categories_list'] .= '<option' . $disabled . $class . ' value="' . $value . '">' . $nom . '</option>';
					$this->categories_list($categories, $infos['categorie_chemin'], $marge+1, $only_cat);
				}
			}
		}
	}



	/*
	  *	Création d'un album ou d'une catégorie par les membres.
	*/
	function membres_create() {
		if (isset($_POST['upload_categorie'])
		 && isset($_POST['obj_type'])
		 && isset($_POST['obj_name'])) {

			// Quelques vérifications.
			if (!preg_match('`^\d{1,9}$`', $_POST['upload_categorie'])) {
				return;
			}
			if (($_POST['obj_type'] == 'cat' || $_POST['obj_type'] == 'alb') === false) {
				return;
			}

			$objet_type = ($_POST['obj_type'] == 'alb') ? 'l\'album' : 'la catégorie';

			// On vérifie si on peut créer l'objet à cet endroit.
			$mysql_requete = 'SELECT categorie_derniere_modif,
									 categorie_chemin
								FROM ' . MYSQL_PREF . 'categories 
							   WHERE categorie_id = "' . $_POST['upload_categorie'] . '"
							     AND categorie_visible != "0"';
			$infos_cat = $this->mysql->select($mysql_requete);
			if (!is_array($infos_cat) || $infos_cat[0]['categorie_derniere_modif']) {
				$this->template['erreur']['new_obj'] = 'Impossible de créer ' . $objet_type . ' à cet endroit.';
				return;
			}

			// Nom de l'objet.
			$obj_nom = strip_tags($_POST['obj_name']);
			$obj_nom = trim($obj_nom);
			if ($obj_nom == '') {
				$this->template['erreur']['new_obj'] = 'Le nom de ' . $objet_type . ' est vide.';
				return;
			}
			if (strlen($obj_nom) > 100) {
				$this->template['erreur']['new_obj'] = 'Le nom de ' . $objet_type . ' ne doit pas contenir plus de 100 caractères.';
				return;
			}
			if (!preg_match('`^[\'@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)«»!?$=-]{1,100}$`i', $obj_nom)) {
				$this->template['erreur']['new_obj'] = 'Le nom de ' . $objet_type . ' ne doit pas contenir de caractères spéciaux.';
				return;
			}

			// Filtrage de la description.
			$obj_desc = '';
			if (!empty($_POST['obj_desc'])) {
				$obj_desc = strip_tags($_POST['obj_desc']);
				$obj_desc = trim($obj_desc);
				if (strlen($obj_desc) > 1500) {
					$this->template['erreur']['new_obj'] = 'La description de ' . $objet_type . ' ne doit pas contenir plus de 1500 caractères.';
					return;
				}
				if (!preg_match('`^[\'@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)«»!?$=-]{1,1500}$`i', $obj_desc)) {
					$this->template['erreur']['new_obj'] = 'La description de ' . $objet_type . ' ne doit pas contenir de caractères spéciaux.';
					return;
				}
			}

			// On vérifie s'il n'y a pas déjà un objet du même nom.
			$path = $infos_cat[0]['categorie_chemin'];
			$path = ($path == '.') ? '': $path;
			$test1 = 'SELECT categorie_nom
						FROM ' . MYSQL_PREF . 'categories
					   WHERE categorie_id NOT IN ("' . $_POST['upload_categorie'] . '")
						 AND categorie_nom = "' . outils::protege_mysql($obj_nom, $this->mysql->lien) . '"
						 AND categorie_chemin REGEXP "^' . $path . '[^/]+/$"';
			$test = $this->mysql->select($test1);
			if (is_array($test)) {
				$this->template['erreur']['new_obj'] = 'Cette catégorie contient déjà une catégorie ou un album de même nom.';
				return;
			}

			$galerie_dir = dirname(__FILE__) . '/' . GALERIE_ALBUMS . '/';

			// On remplace les caractères spéciaux pour le nom du répertoire.
			$dir_cat = ($infos_cat[0]['categorie_chemin'] == '.') ? '' : $infos_cat[0]['categorie_chemin'];
			$post_name = strtr($obj_nom, 'éèëêàäâáåãïîìíöôòóõùûüúÿýçñ', 'eeeeaaaaaaiiiiooooouuuuyycn');
			$post_name = preg_replace('`[^-a-z0-9]`i', '_', $post_name);
			$dir = $galerie_dir . $dir_cat . $post_name;

			// Si un répertoire au même nom existe, on modifie le nom du répertoire.
			$n = 1;
			$testdir = $dir;
			while (is_dir($testdir)) {
				$testdir = $dir . $n;
				$n++;
				if ($n > 999) {
					$this->template['erreur']['new_obj'] = 'Impossible de créer ' . $objet_type . '.';
					return;
				}
			}
			$dir = $testdir . '/';

			// On crée le répertoire sur le disque.
			if (!files::createDir($dir)) {
				$this->template['erreur']['new_obj'] = 'Impossible de créer ' . $objet_type . '.';
				return;
			}

			// Si c'est un album à créer, on crée également le répertoire de vignettes.
			if ($_POST['obj_type'] == 'alb') {
				files::createDir($dir . THUMB_TDIR);
			}

			$dir = preg_replace('`^.+/' . GALERIE_ALBUMS . '/`', '', $dir);

			// On récupère le mot de passe de la catégorie parente.
			$mysql_requete = 'SELECT categorie_pass
								FROM ' . MYSQL_PREF . 'categories 
							   WHERE categorie_chemin = "' . dirname($dir) . '/"';
			$pass = $this->mysql->select($mysql_requete);
			$pass_champ = (!is_array($pass) || empty($pass[0]['categorie_pass'])) ? '' : ', categorie_pass';
			$pass_valeur = (!is_array($pass) || empty($pass[0]['categorie_pass'])) ? '' : ', "' . $pass[0]['categorie_pass'] . '"';

			// On crée l'objet.
			$derniere_modif = ($_POST['obj_type'] == 'cat') ? '0' : time();
			$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'categories (
					user_id,
					categorie_chemin,
					categorie_nom,
					categorie_description,
					categorie_visible,
					image_representant_id,
					categorie_derniere_modif,
					categorie_date'
					. $pass_champ . ') VALUES ("'
					. $this->template['membre_user'][0]['user_id'] . '","'
					. $dir . '","'
					. outils::protege_mysql($obj_nom, $this->mysql->lien) . '","'
					. outils::protege_mysql($obj_desc, $this->mysql->lien) . '","'
					. '2' . '","'
					. '0' . '","'
					. $derniere_modif . '","'
					. time() . '"' 
					. $pass_valeur . ')';
			if ($this->mysql->requete($mysql_requete)) {
				$msg = ($_POST['obj_type'] == 'cat') ? 'La catégorie a été créée.' : 'L\'album a été créé.';
				$this->template['succes']['new_obj'] = $msg;
			} else {
				$this->template['erreur']['new_obj'] = 'Impossible de créer ' . $objet_type . '.';
				files::suppFile($galerie_dir . $dir);
				if ($_POST['obj_type'] == 'alb') {
					files::suppFile($galerie_dir . $dir . THUMB_TDIR);
				}
			}
		}
	}



	/*
	  *	Membres.
	*/
	function membres() {

		if (!$this->config['users_membres_active']) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// Personnalisation.
		if ($this->config['user_perso']) {
			$this->template['user']['style'] = $this->config['user_style'];
			if ($this->template['user']['style']) {
				$this->template['user']['perso'] = 1;
			} else {
				$this->template['user']['perso'] = 0;
			}
		} else {
			$this->template['user']['perso'] = 0;
		}

		switch ($_GET['membres']) {

			case 'login' :
				$this->template['infos']['section_membres'] = $_GET['membres'];
				$this->template['infos']['title'] = 'identification';

				if (!$this->template['infos']['membres_connexion']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}
				$this->template['infos']['membres_login'] = true;

				$this->headersNoCache();
				break;

			case 'liste' :

				$this->template['infos']['section_membres'] = $_GET['membres'];
				$this->template['infos']['title'] = 'liste des membres';
				$this->template['infos']['objet'] = 'liste';
				$this->template['infos']['type'] = 'membres';

				// Startnum.
				$startnum = 0;
				if (isset($_GET['startnum']) && preg_match('`^[1-9]\d{0,9}$`', $_GET['startnum'])) {
					$startnum = $_GET['startnum'];
				}
				$this->params['startnum'] = $startnum;

				$this->params['limit_vignettes'] = 20;
				$this->template['vignettes'] = array();

				// On récupère la liste des membres à afficher.
				$mysql_requete = 'SELECT ' . MYSQL_PREF . 'users.user_login,
										 ' . MYSQL_PREF . 'users.user_avatar,
										 ' . MYSQL_PREF . 'users.user_date_creation,
										 ' . MYSQL_PREF . 'groupes.groupe_titre
									FROM ' . MYSQL_PREF . 'users JOIN ' . MYSQL_PREF . 'groupes USING (groupe_id)
								   WHERE ' . MYSQL_PREF . 'users.groupe_id != 0
								ORDER BY ' . MYSQL_PREF . 'users.user_date_creation DESC
								   LIMIT ' . $startnum . ',' . $this->params['limit_vignettes'];
				$users = $this->mysql->select($mysql_requete);

				// On récupère le nombre de membres.
				$mysql_requete = 'SELECT COUNT(user_id)
									FROM ' . MYSQL_PREF . 'users
								   WHERE groupe_id != 0';
				$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);
				$this->template['nb_objets'] = $this->params['nb_objets'];

				// On n'a plus besoin de la bdd.
				$this->mysql->fermer();

				$this->liens_pages();

				// On crée le tableau des membres.
				$avatar = '';
				if ($this->config['users_membres_avatars']) {
					$avatar = '<th class="ml_nom">Avatar</th>';
				}
				$users_table = '<table><tr>'
					. $avatar . '
					<th>Nom d\'utilisateur</th>
					<th class="ml_titre">Titre</th>
					<th class="ml_date">Date d\'inscription</th>
				</tr>';
				for ($i = 0; $i < count($users); $i++) {
					$user_url = urlencode($users[$i]['user_login']);
					$lien = outils::genLink('?profil=' . $user_url);
					$nom = str_replace('_', ' ', $users[$i]['user_login']);
					$avatar = '';
					if ($this->config['users_membres_avatars']) {
						$avatar = ($users[$i]['user_avatar']) 
								? '<td class="ml_avatar"><a href="' . $lien . '"><img alt="Avatar de ' . $nom . '" src="' . GALERIE_PATH . '/membres/avatars/avatar_' . $users[$i]['user_login'] . '_thumb.jpg" /></a></td>'
								: '<td class="ml_avatar"><a href="' . $lien . '"><img alt="pas d\'avatar" src="' . dirname($this->template['infos']['style']) . '/avatar_default.png" /></a></td>';
					}
					$pair = (is_integer($i / 2)) ? ' class="ml_pair"' : '';
					$users_table .= '<tr' . $pair . '>'
										  . $avatar . '
										 <td class="ml_nom"><a href="' . $lien . '">' . $nom . '</a></td>
										 <td class="ml_titre">' . $users[$i]['groupe_titre'] . '</td>
										 <td class="ml_date">' . outils::ladate($users[$i]['user_date_creation'], '%d/%m/%Y') . '</td>
									</tr>';
				}
				$users_table .= '</table>';
				$this->template['users'] = $users_table;

				break;

			case 'create' :
				if ($this->template['infos']['membres_connexion']
				 || !$this->template['membre_user'][0]['groupe_upload']
				 || !$this->template['membre_user'][0]['groupe_upload_create']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$this->verifSID();

				$this->template['infos']['section_membres'] = $_GET['membres'];
				$this->template['infos']['title'] = 'création d\'un nouvel album';

				$this->membres_create();

				$mysql_requete = 'SELECT categorie_id,
										 categorie_nom,
										 categorie_chemin,
										 categorie_derniere_modif
									FROM ' . MYSQL_PREF . 'categories
								   WHERE categorie_id != "1" 
									 AND categorie_visible != "0"
									 AND categorie_derniere_modif = "0" '
										. $this->images_protect('categorie') . '
								ORDER BY categorie_nom ASC';
				$categories = $this->mysql->select($mysql_requete);

				$this->template['categories_list'] = '<select id="upload_categories" name="upload_categorie">';
				$this->template['categories_list'] .= '<option value="1">galerie</option>';	
				$this->categories_list($categories, '', 1, 1);
				$this->template['categories_list'] .= '</select>';	

				break;

			case 'upload' :
				if ($this->template['infos']['membres_connexion']
				 || !$this->template['membre_user'][0]['groupe_upload']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$this->verifSID();

				$this->template['infos']['section_membres'] = $_GET['membres'];
				$this->template['infos']['title'] = 'envoi d\'images';

				$mysql_requete = 'SELECT categorie_id,
										 categorie_nom,
										 categorie_chemin,
										 categorie_derniere_modif
									FROM ' . MYSQL_PREF . 'categories
								   WHERE categorie_id != "1" 
									 AND categorie_visible != "0" '
										. $this->images_protect('categorie') . '
								ORDER BY categorie_nom ASC';
				$categories = $this->mysql->select($mysql_requete);

				$this->template['categories_list'] = '<select id="upload_categories" name="upload_categorie">';
				$this->categories_list($categories);
				$this->template['categories_list'] .= '</select>';			

				if (isset($_FILES['upload_files']) && is_array($_FILES['upload_files'])
				 && isset($_POST['upload_categorie'])
				 && isset($_POST['upload_images']) && is_array($_POST['upload_images'])) {

					$albums_dir = dirname(__FILE__) . '/' . GALERIE_ALBUMS . '/';

					// Limite du nombre de fichiers.
					$files_limit = 2;

					// Quelques vérifications.
					if (!preg_match('`^\d{1,9}$`', $_POST['upload_categorie'])) {
						return;
					}
					if (!isset($_FILES['upload_files']['name'][1])) {
						return;
					}

					// Récupération du chemin de l'album.
					$mysql_requete = 'SELECT categorie_id,
											 categorie_nom,
											 categorie_chemin
									    FROM ' . MYSQL_PREF . 'categories
									   WHERE categorie_id = "' . $_POST['upload_categorie'] . '"
									     AND categorie_visible != "0"';
					$album_infos = $this->mysql->select($mysql_requete);
					if (!is_array($album_infos)) {
						$this->template['erreur'][0] = 'Vous ne pouvez envoyer des images que dans les albums.';
						return;
					}

					// Traitement de chaque fichier.
					$this->template['erreur'] = array();
					$images_direct = array();
					$images_user = array();
					for ($i = 1; $i <= count($_FILES['upload_files']['name']) && $i <= $files_limit; $i++) {

						$name = $_FILES['upload_files']['name'][$i];
						$error = $_FILES['upload_files']['error'][$i];

						// Filtrage du nom.
						$image_nom = '';
						if (!empty($_POST['upload_images'][$i]['nom'])) {
							$image_nom = strip_tags($_POST['upload_images'][$i]['nom']);
							$image_nom = trim($image_nom);
							if (strlen($image_nom) > 100) {
								$this->template['erreur'][$i] = 'Le nom de l\'image ' . $i . ' ne doit pas contenir plus de 100 caractères.';
								continue;
							}
							if (!preg_match('`^[\'@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)«»!?$=-]{1,100}$`i', $image_nom)) {
								$this->template['erreur'][$i] = 'Le nom de l\'image ' . $i . ' ne doit pas contenir de caractères spéciaux.';
								continue;
							}
						}

						// Filtrage de la description.
						$image_desc = '';
						if (!empty($_POST['upload_images'][$i]['desc'])) {
							$image_desc = strip_tags($_POST['upload_images'][$i]['desc']);
							$image_desc = trim($image_desc);
							if (strlen($image_desc) > 1500) {
								$this->template['erreur'][$i] = 'La description de l\'image ' . $i . ' ne doit pas contenir plus de 1500 caractères.';
								continue;
							}
							if (!preg_match('`^[\'@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)«»!?$=-]{1,1500}$`i', $image_desc)) {
								$this->template['erreur'][$i] = 'La description de l\'image ' . $i . ' ne doit pas contenir de caractères spéciaux.';
								continue;
							}
						}

						// Y a-t-il une erreur ?
						if ($error) {
							switch ($error) {
								case 4 :
									break;
								case 2 :
								case 1 :
									$this->template['erreur'][$i] = 'L\'image ' . $i . ' est trop lourde.';
									break;
								default :
									$this->template['erreur'][$i] = '[' . $error . '] Impossible de récupérer l\'image ' . $i . '.';
							}
							continue;
						}

						// Le fichier est-il trop lourd ?
						if (filesize($_FILES['upload_files']['tmp_name'][$i]) > ($this->config['users_upload_maxsize']*1024)) {
							$this->template['erreur'][$i] = 'L\'image ' . $i . ' est trop lourde.';
							continue;
						}

						// Le nom de fichier est-il trop long ?
						if (strlen($name) > 250) {
							$this->template['erreur'][$i] = 'Le nom de fichier de l\'image ' . $i . ' est trop long.';
							continue;
						}

						// Le nom de fichier contient-il des caractères spéciaux ?
						if (!preg_match('`^[-_a-z0-9.]{1,250}$`i', $name)) {
							$this->template['erreur'][$i] = 'Le nom de fichier de l\'image ' . $i . ' ne doit pas contenir d\'espaces, de caractères spéciaux ou accentués.';
							continue;
						}

						// Est-ce une image au format JPEG, GIF ou PNG ?
						if (($file_infos = getimagesize($_FILES['upload_files']['tmp_name'][$i])) === false) {
							$this->template['erreur'][$i] = 'Le fichier ' . $i . ' n\'est pas une image valide.';
							continue;
						}
						if (!preg_match('`\.(jpe?g|gif|png)$`i', $name)
						 || !preg_match('`^image/(gif|p?jpeg|(x-)?png)$`i', trim($file_infos['mime']))) {
							$this->template['erreur'][$i] = 'Le fichier ' . $i . ' n\'est pas au format JPEG, GIF ou PNG.';
							continue;
						}

						// L'image est-elle trop grande ?
						if ($file_infos[0] > $this->config['users_upload_maxwidth']
						 || $file_infos[1] > $this->config['users_upload_maxheight']) {
							$this->template['erreur'][$i] = 'L\'image ' . $i . ' est trop grande (max = ' . $this->config['users_upload_maxwidth'] . ' x ' . $this->config['users_upload_maxheight'] . ' pixels).';
							continue;
						}

						// On vérifie si un fichier du même nom existe déjà.
						if (file_exists($albums_dir . $album_infos[0]['categorie_chemin'] . $name)) {
							$this->template['erreur'][$i] = 'L\'image ' . $i . ' ne peut être ajoutée à l\'album car une image de même nom de fichier existe déjà dans cet album.';
							continue;
						}

						// On vérifie si une image de même nom existe déjà.
						if ($image_nom) {
							$mysql_requete = 'SELECT image_id
												FROM ' . MYSQL_PREF . 'images
											   WHERE image_nom = "' . $image_nom . '"
												 AND categorie_parent_id = "' . $album_infos[0]['categorie_id'] . '"';
							if (is_array($this->mysql->select($mysql_requete))) {
								$this->template['erreur'][$i] = 'L\'image ' . $i . ' ne peut être ajoutée à l\'album car une image de même nom existe déjà dans cet album.';
								continue;
							}
						}


						// Mode d'upload : direct.
						if ($this->template['membre_user'][0]['groupe_upload_mode'] == 'direct') {
							$dir = $albums_dir . $album_infos[0]['categorie_chemin'];

							// On déplace le fichier envoyé vers le répertoire de l'album.
							files::chmodDir($dir);
							if (!move_uploaded_file($_FILES['upload_files']['tmp_name'][$i], $dir . $name)) {
								$this->template['erreur'][$i] = 'Impossible de récupérer le fichier « <em>' . $name . ' </em> ».';
								continue;
							}
							files::chmodFile($dir . $name);

							$images_direct[] = $name;

							$images_user[$album_infos[0]['categorie_chemin'] . $name]['user_id'] = $this->template['membre_user'][0]['user_id'];
							$images_user[$album_infos[0]['categorie_chemin'] . $name]['image_nom'] = $image_nom;
							$images_user[$album_infos[0]['categorie_chemin'] . $name]['image_desc'] = $image_desc;

						// Mode d'upload : attente.
						} else {
							$dir = dirname(__FILE__) . '/membres/images/';

							$n = 0;
							$new_name = $name;
							while (file_exists($dir . $new_name)) {
								$n++;
								$new_name = preg_replace('`^(.+)(\..{3,4})$`', '$1_' . $n . '$2', $name);
							}

							// On déplace le fichier envoyé vers le répertoire d'attente.
							files::chmodDir($dir);
							if (!move_uploaded_file($_FILES['upload_files']['tmp_name'][$i], $dir . $new_name)) {
								$this->template['erreur'][$i] = 'Impossible de récupérer l\'image ' . $i . '.';
								continue;
							}
							files::chmodFile($dir . $new_name);

							// On enregistre l'image dans la base de données.
							$type = preg_replace('`^image/(gif|p?jpeg|(?:x-)?png)$`i', '$1', trim($file_infos['mime']));
							switch ($type) {
								case 'pjpeg' :
									$type = 'jpeg';
									break;
								case 'x-png' :
									$type = 'png';
							}
							list($img_width, $img_height, $img_type) = getimagesize($dir . $new_name);
							$img_poids = 0;
							if (preg_match('`^\d{1,9}$`', $img_poids)) {
								$img_poids = $_FILES['upload_files']['size'][$i];
								$img_poids = round($img_poids/1024, 1);
							}
							$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'images_attente (
													user_id,
													categorie_id,
													img_att_nom,
													img_att_description,
													img_att_fichier,
													img_att_type,
													img_att_poids,
													img_att_hauteur,
													img_att_largeur,
													img_att_date,
													img_att_ip
													) VALUES (
													"' . $this->template['membre_user'][0]['user_id'] . '",
													"' . $album_infos[0]['categorie_id'] . '",
													"' . outils::protege_mysql($image_nom, $this->mysql->lien) . '",
													"' . outils::protege_mysql($image_desc, $this->mysql->lien) . '",
													"' . outils::protege_mysql($new_name, $this->mysql->lien) . '",
													"' . $type . '",
													"' . $img_poids . '",
													"' . $img_height . '",
													"' . $img_width . '",
													"' . time() . '",
													"' . $_SERVER['REMOTE_ADDR'] . '"
													)';
							if ($this->mysql->requete($mysql_requete)) {
								$this->template['succes'][$i] = 'L\'image ' . $i . ' a été mise en attente de validation par l\'admin.';
							} else {
								$this->template['erreur'][$i] = 'Une erreur de base de données s\'est produite avec l\'image ' . $i . '.';
								files::suppFile($dir . $new_name);
							}
					
						}

						$upload_ok = 1;
					}

					if (empty($upload_ok)) {
						return;
					}

					// Date de dernier ajout.
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET user_date_dernier_upload = "' . time() . '"
									   WHERE user_id = "' . $this->template['membre_user'][0]['user_id'] . '"';
					$this->mysql->requete($mysql_requete);

					// On prévient l'admin ?
					if ($this->config['users_upload_alert'] && !empty($this->config['admin_mail'])) {

						// On prévient l'admin uniquement si le membre n'a pas déjà envoyé des images dans l'heure précédente.
						if (!$this->template['membre_user'][0]['user_date_dernier_upload']
						  || (time()-$this->template['membre_user'][0]['user_date_dernier_upload']) > 3600) {

							$from = 'iGalerie Alerte <igalerie@' . $_SERVER['SERVER_NAME'] . '>';

							$login = $this->template['membre_user'][0]['user_login'];
							$user_nom = str_replace('_', ' ', $login);
							if ($this->template['membre_user'][0]['groupe_upload_mode'] != 'direct') {
								$objet = 'Images en attente de validation';
								$message = 'L\'utilisateur \'' . $user_nom . '\' a envoyé de nouvelles images qui ont été mises en attente de validation.' . "\n\n";
							} else {
								$objet = 'Nouvelles images dans la galerie';
								$message = 'L\'utilisateur \'' . $user_nom . '\' a ajouté de nouvelles images à la galerie.' . "\n\n";
							}

							// Message.
							$message .= 'Vous pouvez consulter le profil de ce membre sur cette page : ' . "\n";
							$message .= 'http://' . $_SERVER['HTTP_HOST'] . GALERIE_URL . '?profil=' . urlencode($login) . "\n\n";
							$message .= '-- ' . "\n";
							$message .= 'Ce courriel a été envoyé automatiquement par iGalerie.';

							// Envoi du message.
							outils::send_mail($this->config['admin_mail'], $objet, $message, $from);

						}
						
					}

					// Mode direct : enregistrement des images dans la base de données.
					if ($this->template['membre_user'][0]['groupe_upload_mode'] == 'direct') {
						require_once(dirname(__FILE__) . '/includes/classes/class.upload.php');
						$upload = new upload($this->mysql, $this->config);
						$upload->http['album'] = $album_infos[0]['categorie_chemin'];
						$upload->http['images'] = $images_direct;
						$upload->galerie_dir = './' . GALERIE_ALBUMS . '/';
						$upload->users = $images_user;
						$upload->recup_albums();

						// Rapport.
						if ($upload->rapport['erreurs']) {
							foreach ($upload->rapport['erreurs'] as $v) {
								$this->template['erreur']['upload_' . $v[0]] = 'Une erreur s\'est produite avec l\'objet « <em>' . $v[0] . '</em> » : ' . $v[1];
							}
						}
						$dir = $albums_dir . $album_infos[0]['categorie_chemin'];
						for ($i = 0; $i < count($images_direct); $i++) {
							$ok = 1;
							for ($n = 0; $n < count($upload->rapport['img_rejets']); $n++) {
								if ($upload->rapport['img_rejets'][$n][0] == $images_direct[$i]) {
									$this->template['erreur']['upload_' . $i] = 'L\'image « <em>' . $images_direct[$i] . '</em> » a été rejetée pour la raison suivante : ' . $upload->rapport['img_rejets'][$n][2];
									files::suppFile($dir . $images_direct[$i]);
									files::suppFile($dir . '~#~');
									$ok = 0;
									break;
								}
							}
							if ($ok && empty($this->template['erreur'])) {
								$this->template['succes']['upload_' . $i] = 'L\'image « <em>' . $images_direct[$i] . ' </em> » a été ajoutée à la galerie.';
							}
						}
					}

				}

				$this->headersNoCache();
				break;
		
			case 'inscription' :
				if (!$this->template['infos']['membres_connexion']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$this->template['infos']['galerie_key'] = $this->config['galerie_key'];
				$this->template['infos']['title'] = 'inscription';

				$this->template['infos']['section_membres'] = $_GET['membres'];
				if (!empty($_POST)) {

					if (!isset($_POST['molpac'])) {
						break;
					}

					// Anti-spam ?
					$ok = true;
					if (empty($_POST['molpac']) || !preg_match('`^[a-z0-9]{32}$`', $_POST['molpac'])) {
						$ok = false;
					}
					$time = time();
					$time_md5_array = array();
					for ($i = -5; $i < 1; $i++) {
						$time_md5 = md5($time + $i);
						$time_key_md5 = md5($time_md5 . $this->config['galerie_key']);
						if ($_POST['molpac'] == $time_key_md5) {
							$ok = false;
							break;
						}
					}
					if (!$ok) {
						$this->template['erreur'] = 'Prenez votre temps pour créer votre profil !';
						break;
					}

					// Tout d'abord, quelques vérifications basiques...
					if (empty($_POST['new_login'])) {
						$this->template['erreur'] = 'Nom d\'utilisateur vide.';
						break;
					}
					$_POST['new_login'] = trim($_POST['new_login']);
					if (empty($_POST['new_pass'])) {
						$this->template['erreur'] = 'Mot de passe vide.';
						break;
					}
					if (empty($_POST['new_pass_confirm'])) {
						$this->template['erreur'] = 'Confirmation du mot de passe vide.';
						break;
					}

					if (strlen($_POST['new_login']) < 3) {
						$this->template['erreur'] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
						break;
					}
					if (strlen($_POST['new_pass']) < 6) {
						$this->template['erreur'] = 'Le mot de passe doit contenir au moins 6 caractères.';
						break;
					}
					if (strlen($_POST['new_login']) > 30) {
						$this->template['erreur'] = 'Le nom d\'utilisateur doit contenir moins de 30 caractères.';
						break;
					}
					if (strlen($_POST['new_pass']) > 250) {
						$this->template['erreur'] = 'Le mot de passe doit contenir moins de 250 caractères.';
						break;
					}

					if (!preg_match('`^[-_a-z\d]{3,30}$`i', $_POST['new_login'])) {
						$this->template['erreur'] = 'Le nom d\'utilisateur ne doit pas contenir de caractères spéciaux ou accentués.<br />Pour les espaces, utilisez le caractère souligné (_), celui-ci sera remplacé par un espace lors de l\'affichage de votre nom d\'utilisateur.';
						break;
					}
					if (!preg_match('`^[@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)«»<>!?-]+$`i', $_POST['new_pass'])) {
						$this->template['erreur'] = htmlentities('Le mot de passe ne peut être constitué que des caractères suivants : abcdefghijklmnopqrstuvwxyz0123465789 éèëêàäâáåãïîìíöôòóõùûüúÿýçñ @&,;/%.:_#()«»<>!?-');
						break;
					}
					if ($_POST['new_pass'] != $_POST['new_pass_confirm']) {
						$this->template['erreur'] = 'La confirmation du mot de passe ne correspond pas.';
						break;
					}

					// On vérifie si le nom d'utilisateur n'est pas déjà pris.
					if (preg_match('`^admin.*$`i', $_POST['new_login'])
					 || $_POST['new_login'] == ADMIN_USER) {
						$this->template['erreur'] = 'Ce nom d\'utilisateur est déjà pris.';
						break;
					}
					$mysql_requete = 'SELECT user_login
										FROM ' . MYSQL_PREF . 'users
									   WHERE user_login LIKE "' . outils::protege_mysql($_POST['new_login'], $this->mysql->lien) . '"';
					$test = $this->mysql->select($mysql_requete);
					if ($test != 'vide') {
						$this->template['erreur'] = 'Ce nom d\'utilisateur est déjà pris.';
						break;
					}

					// On vérifie courriel et site Web.
					$new_mail = '';
					if (!empty($_POST['new_mail'])) {
						if (!preg_match('`^' . outils::email_address() . '$`i', $_POST['new_mail'])) {
							$this->template['erreur'] = 'Le format de l\'adresse de courriel entrée est incorrecte.';
							break;

						// On vérifie si le courriel n'est pas déjà pris.
						} else {
							$mysql_requete = 'SELECT user_mail
												FROM ' . MYSQL_PREF . 'users
											   WHERE user_mail LIKE "' . outils::protege_mysql($_POST['new_mail'], $this->mysql->lien) . '"';
							$test = $this->mysql->select($mysql_requete);
							if ($test != 'vide') {
								$this->template['erreur'] = 'Cette adresse courriel est déjà prise.';
								break;
							}
						}
						$new_mail = $_POST['new_mail'];
					}
					$new_web = '';
					if (!empty($_POST['new_web'])) {
						if (!preg_match('`^' . outils::http_url() . '$`', $_POST['new_web'])) {
							$this->template['erreur'] = 'Le format de l\'adresse du site Web entrée est incorrecte.';
							break;
						}
						$new_web = $_POST['new_web'];
					}

					// On enregistre l'utilisateur.
					$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'users (
						user_login,
						user_pass,
						user_mail,
						user_web,
						user_date_creation,
						user_date_derniere_visite,
						user_ip_creation,
						user_ip_derniere_visite) VALUES (
						"' . $_POST['new_login'] . '",
						"' . md5($_POST['new_pass']) . '",
						"' . $new_mail . '",
						"' . $new_web . '",
						"' . time() . '",
						"' . time() . '",
						"' . $_SERVER['REMOTE_ADDR'] . '",
						"' . $_SERVER['REMOTE_ADDR'] . '")';
					if ($this->mysql->requete($mysql_requete)) {
						$this->template['succes'] = 'Votre compte a bien été créé ! Vous pouvez maintenant vous identifier.';
						$this->template['infos']['inscription_ok'] = true;
					} else {
						$this->template['erreur'] = 'Impossible de créer le compte.';
					}

					// On prévient l'admin par mail.
					if ($this->config['users_membres_alert'] 
					 && !empty($this->template['infos']['inscription_ok'])
					 && !empty($this->config['admin_mail'])) {

						$objet = 'Nouvelle inscription';
						$from = 'iGalerie Alerte <igalerie@' . $_SERVER['SERVER_NAME'] . '>';

						// Message.
						$message = 'L\'utilisateur \'' . str_replace('_', ' ', $_POST['new_login']) . '\' s\'est inscrit à la galerie.' . "\n\n";
						$message .= 'Vous pouvez consulter son profil sur cette page : ' . "\n";
						$message .= 'http://' . $_SERVER['HTTP_HOST'] . GALERIE_URL . '?profil=' . urlencode($_POST['new_login']) . "\n\n";
						$message .= '-- ' . "\n";
						$message .= 'Ce courriel a été envoyé automatiquement par iGalerie.';

						// Envoi du message.
						outils::send_mail($this->config['admin_mail'], $objet, $message, $from);
					}
				}

				$this->headersNoCache();
				break;

			case 'new_pass' :
				if (!$this->template['infos']['membres_connexion']
				 || empty($_GET['key'])
				 || !preg_match('`^[a-z\d]{12}$`i', trim($_GET['key']))) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$this->verifSID();

				$this->template['infos']['title'] = 'mot de passe oublié';
				$this->template['infos']['section_membres'] = 'oubli';
				$this->template['infos']['oubli_form'] = false;

				$mysql_requete = 'SELECT user_id,
										 user_oubli
									FROM ' . MYSQL_PREF . 'users
								   WHERE user_oubli LIKE "' . trim($_GET['key']) . '.%"';
				$oubli = $this->mysql->select($mysql_requete);
				if (is_array($oubli)) {
					$infos = explode('.', $oubli[0]['user_oubli']);
					if ($infos[1] < time()) {
						$this->template['erreur'] = 'Le délai de validité du nouveau de mot de passe a expiré.';
						break;
					} else {
						$new_pass = outils::gen_key(16);
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
											 SET user_pass = "' . md5($new_pass) . '"
										   WHERE user_id = "' . $oubli[0]['user_id'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$this->template['succes'] = 'Le nouveau mot de passe que vous avez demandé a été créé :<br /><strong>' . $new_pass . '</strong>';
						} else {
							$this->template['erreur'] = 'Une erreur s\'est produite. Le nouveau mot de passe n\'a pas pu être créé.';
						}
					}
				} else {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$this->headersNoCache();
				break;

			case 'oubli' :
				if (!$this->template['infos']['membres_connexion']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}
				$this->template['infos']['title'] = 'mot de passe oublié';
				$this->template['infos']['section_membres'] = $_GET['membres'];
				$this->template['infos']['oubli_form'] = true;

				if (isset($_POST['oubli_user'])) {
					if (!preg_match('`^[-_a-z\d]{3,30}$`i', $_POST['oubli_user'])) {
						$this->template['erreur'] = 'Format du nom d\'utilisateur incorrect.';
						break;
					}
				}

				if (isset($_POST['oubli_mail'])) {
					$_POST['oubli_mail'] = trim($_POST['oubli_mail']);
					if (preg_match('`^' . outils::email_address() . '$`i', $_POST['oubli_mail'])) {
						$mysql_requete = 'SELECT user_login
											FROM ' . MYSQL_PREF . 'users
										   WHERE user_login LIKE "' . $_POST['oubli_user'] . '"
											 AND user_mail = "' . outils::protege_mysql($_POST['oubli_mail'], $this->mysql->lien) . '"
											 AND user_id != "1"';
						if (is_array($this->mysql->select($mysql_requete))) {

							$code = outils::gen_key(12);

							$titre = trim(strip_tags($this->config['galerie_titre']));
							$titre = (empty($titre)) ? 'iGalerie' : $titre;
							$from = $titre . ' <igalerie@' . $_SERVER['SERVER_NAME'] . '>';

							$galerie = 'http://' . $_SERVER['HTTP_HOST'] . GALERIE_URL;
							$message = 'Bonjour ' . str_replace('_', ' ', $_POST['oubli_user']) . ',' . "\n\n";
							$message .= 'Vous avez effectué la demande d\'un nouveau mot de passe pour la galerie ' . $galerie . '. ';
							$message .= 'Si vous n\'avez pas fait cette demande ou que vous ne souhaitez pas changer votre mot de passe, veuillez ignorer ce message.' . "\n\n";
							$message .= 'Pour activer votre nouveau mot de passe, vous devez visiter la page ' . $galerie . '?membres=new_pass&key=' . $code . '. ';
							$message .= 'Notez que ce lien d\'activation n\'est valide que pendant 48 heures.' . "\n\n";
							$message .= '-- ' . "\n";
							$message .= 'Ce courriel a été envoyé automatiquement par iGalerie.';

							// Envoi du message.
							if (outils::send_mail($_POST['oubli_mail'], 'Demande de nouveau mot de passe', $message, $from)) {
								$code = $code . '.' . (time()+(3600*48));
								$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
													 SET user_oubli = "' . $code . '"
												   WHERE user_login LIKE "' . $_POST['oubli_user'] . '"';
								if ($this->mysql->requete($mysql_requete)) {
									$this->template['infos']['oubli_form'] = false;
									$this->template['succes'] = 'Votre demande de nouveau mot de passe a été acceptée.<br />Un courriel vous a été envoyé avec les instructions à suivre.';
								} else {
									$this->template['erreur'] = 'Une erreur s\'est produite.<br />La demande de nouveau de mot de passe à échouée.';
								}
							} else {
								$this->template['erreur'] = 'Impossible d\'envoyer le courriel.';
							}
						} else {
							$this->template['erreur'] = 'Utilisateur ou adresse courriel inconnu(e).';
						}
					} else {
						$this->template['erreur'] = 'Format de l\'adresse incorrect.';
					}
				}

				$this->headersNoCache();
				break;

			case 'modif_profil' :
				if ($this->template['infos']['membres_connexion']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$this->verifSID();

				$this->template['infos']['section_membres'] = $_GET['membres'];
				$this->template['infos']['title'] = 'profil';
				$_GET['profil'] = $this->template['membre_user'][0]['user_login'];
				$this->membre_infos();

				// Modification des données.
				if (isset($_POST['modif_profil'])) {
					$update = '';
					if (isset($_POST['new_mail'])
					 && $_POST['new_mail'] != $this->template['membre_profil'][0]['user_mail']) {
						$_POST['new_mail'] = trim($_POST['new_mail']);
						if ($_POST['new_mail'] == '' || preg_match('`^' . outils::email_address() . '$`i', $_POST['new_mail'])) {

							// On vérifie si le courriel n'est pas déjà pris.
							$test = '';
							if ($_POST['new_mail'] != '') {
								$mysql_requete = 'SELECT user_mail
													FROM ' . MYSQL_PREF . 'users
												   WHERE user_mail LIKE "' . outils::protege_mysql($_POST['new_mail'], $this->mysql->lien) . '"';
								$test = $this->mysql->select($mysql_requete);
							}
							if (is_array($test)) {
								$this->template['erreur'] = 'Cette adresse courriel existe déjà.';
							} else {
								$update .= ',user_mail = "' . $_POST['new_mail'] . '"';
								$this->template['membre_profil'][0]['user_mail'] = $_POST['new_mail'];
							}
						} else {
							$this->template['erreur'] = 'L\'adresse du courriel est incorrecte.';
						}
					}
					if (isset($_POST['new_web'])
					  && $_POST['new_web'] != $this->template['membre_profil'][0]['user_web']) {
						if ($_POST['new_web'] == '' || preg_match('`^' . outils::http_url() . '$`i', $_POST['new_web'])) {
							$update .= ',user_web = "' . $_POST['new_web'] . '"';
							$this->template['membre_profil'][0]['user_web'] = $_POST['new_web'];
						} else {
							$this->template['erreur'] = 'L\'adresse du site Web est incorrecte.';
						}
					}
					if (isset($_POST['new_lieu'])
					  && $_POST['new_lieu'] != $this->template['membre_profil'][0]['user_lieu']) {
						if (strlen($_POST['new_lieu']) < 61) {
							if ($_POST['new_lieu'] == '' || preg_match('`^[@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)«»<>!?-]{1,60}$`i', $_POST['new_lieu'])) {
								$update .= ',user_lieu = "' . $_POST['new_lieu'] . '"';
								$this->template['membre_profil'][0]['user_lieu'] = $_POST['new_lieu'];
							} else {
								$this->template['erreur'] = 'Le texte de la localisation ne doit pas contenir de caractères exotiques.';
							}
						} else {
							$this->template['erreur'] = 'Le texte de la localisation ne doit pas contenir plus de 60 caractères.';
						}
					}
					if (!empty($_POST['new_mail_public']) && empty($this->template['membre_profil'][0]['user_mail_public'])) {
						$update .= ',user_mail_public = "1"';
						$this->template['membre_profil'][0]['user_mail_public'] = 1;
					} elseif (empty($_POST['new_mail_public']) && !empty($this->template['membre_profil'][0]['user_mail_public'])) {
						$update .= ',user_mail_public = "0"';
						$this->template['membre_profil'][0]['user_mail_public'] = 0;
					}

					// Newsletter.
					if ($this->template['membre_profil'][0]['groupe_newsletter']) {
						if (!empty($_POST['new_newsletter']) && empty($this->template['membre_profil'][0]['user_newsletter'])) {
							$update .= ',user_newsletter = "1"';
							$this->template['membre_profil'][0]['user_newsletter'] = 1;
						} elseif (empty($_POST['new_newsletter']) && !empty($this->template['membre_profil'][0]['user_newsletter'])) {
							$update .= ',user_newsletter = "0"';
							$this->template['membre_profil'][0]['user_newsletter'] = 0;
						}
					}
					if ($update) {
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
											 SET ' . substr($update, 1) . '
										   WHERE user_id = "' . $this->template['membre_profil'][0]['user_id'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$this->template['succes'] = 'Les informations de votre profil ont été changées.';
						}
					}
				}

				$this->headersNoCache();
				break;

			case 'modif_pass' :
				if (!$this->template['infos']['membres_noadmin']
				  || $this->template['infos']['membres_connexion']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$this->verifSID();

				$this->template['infos']['section_membres'] = $_GET['membres'];
				$this->template['infos']['title'] = 'mot de passe';
				$_GET['profil'] = $this->template['membre_user'][0]['user_login'];
				$this->membre_infos();

				if (isset($_POST['modif_profil'])) {
					if (empty($_POST['new_pass'])) {
						$this->template['erreur'] = 'Mot de passe vide.';
						break;
					}
					if (empty($_POST['new_pass_confirm'])) {
						$this->template['erreur'] = 'Confirmation du mot de passe vide.';
						break;
					}
					if (strlen($_POST['new_pass']) < 6) {
						$this->template['erreur'] = 'Le mot de passe doit contenir au moins 6 caractères.';
						break;
					}
					if (strlen($_POST['new_pass']) > 250) {
						$this->template['erreur'] = 'Le mot de passe doit contenir moins de 250 caractères.';
						break;
					}
					if (!preg_match('`^[@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)«»<>!?-]+$`i', $_POST['new_pass'])) {
						$this->template['erreur'] = htmlentities('Le mot de passe ne peut être constitué que des caractères suivants : abcdefghijklmnopqrstuvwxyz0123465789 éèëêàäâáåãïîìíöôòóõùûüúÿýçñ @&,;/%.:_#()«»<>!?-');
						break;
					}
					if ($_POST['new_pass'] != $_POST['new_pass_confirm']) {
						$this->template['erreur'] = 'La confirmation du mot de passe ne correspond pas.';
						break;
					}
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET user_pass = "' . md5($_POST['new_pass']) . '"
									   WHERE user_id = "' . $this->template['membre_profil'][0]['user_id'] . '"';
					if ($this->mysql->requete($mysql_requete)) {
						$this->template['succes'] = 'Le mot de passe a été changé.';
					} else {
						$this->template['erreur'] = 'Impossible de modifier le mot de passe.';
					}
				}

				$this->headersNoCache();
				break;

			case 'modif_avatar' :
				if ($this->template['infos']['membres_connexion']
				 || !$this->config['users_membres_avatars']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$this->verifSID();

				// Si GD n'est pas activé, on arrête tout.
				if (!function_exists('imagetypes')) {
					$this->template['erreur'] = 'GD n\'est pas présent.';
					return;
				}

				$this->template['infos']['section_membres'] = $_GET['membres'];
				$this->template['infos']['title'] = 'avatar';
				$_GET['profil'] = $this->template['membre_user'][0]['user_login'];
				$this->membre_infos();

				$img_avatar = './membres/avatars/avatar_'
					. $this->template['membre_user'][0]['user_login'] . '.jpg';
				$img_avatar_thumb = './membres/avatars/avatar_'
					. $this->template['membre_user'][0]['user_login'] . '_thumb.jpg';

				// Suppression avatar.
				if (!empty($_POST['supp_avatar'])) {
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET user_avatar = "0"
									   WHERE user_id = "' . $this->template['membre_profil'][0]['user_id'] . '"';
					if ($this->mysql->requete($mysql_requete)) {
						if (file_exists($img_avatar)) {
							files::suppFile($img_avatar);
						}
						if (file_exists($img_avatar_thumb)) {
							files::suppFile($img_avatar_thumb);
						}
						$this->template['succes'] = 'Votre avatar a été supprimé.';
						$this->template['membre_profil'][0]['user_avatar'] = 0;
						$this->template['membre_user'][0]['user_avatar'] = 0;
					} else {
						$this->template['erreur'] = 'Impossible de supprimer votre avatar.';
					}
					return;
				}

				if (!empty($_FILES['new_avatar'])) {

					$infos = $_FILES['new_avatar'];

					// Y a-t-il une erreur ?
					if ($infos['error']) {
						switch ($infos['error']) {
							case 4 :
								break;
							case 2 :
							case 1 :
								$this->template['erreur'] = 'Le fichier est trop lourd.';
								return;
							default :
								$this->template['erreur'] = 'Impossible de récupérer le fichier.<br />Code erreur : ' . __LINE__;
								return;
						}
					}

					// Le fichier est-il trop lourd ?
					if (filesize($infos['tmp_name']) > 81920) {
						$this->template['erreur'] = 'Le fichier est trop lourd.';
						return;
					}

					// Est-ce une image au format JPEG ?
					if (($file_infos = @getimagesize($infos['tmp_name'])) === false) {
						$this->template['erreur'] = 'Le fichier envoyé n\'est pas au format JPEG.';
						return;
					}
					if (!preg_match('`\.jpg$`i', $infos['name'])
					 || !preg_match('`^image/(p?jpeg)$`i', trim($file_infos['mime']))) {
						$this->template['erreur'] = 'Le fichier envoyé n\'est pas au format JPEG.';
						return;
					}

					// Dimensions minimales.
					if ($file_infos[0] < 50 || $file_infos[1] < 50) {
						$this->template['erreur'] = 'L\'image doit faire au moins 50 pixels de coté.';
						return;
					}

					// Dimensions maximales.
					if ($file_infos[0] > 1000 || $file_infos[1] > 1000) {
						$this->template['erreur'] = 'L\'image est trop grande.';
						return;
					}

					// On redimensionne l'image avec GD si nécessaire.
					$file_temp = $infos['tmp_name'];
					$width = 200;
					$height = 200;
					if ($file_infos[0] > $width || $file_infos[1] > $height) {
						$image = FALSE;
						if (imagetypes() & IMG_JPG) {
							if (($image = imagecreatefromjpeg($file_temp)) === false) {
								$this->template['erreur'] = 'Impossible de créer l\'avatar<br />Code erreur : ' . __LINE__;
								return;
							}
						} else {
							$this->template['erreur'] = 'Type de fichier non pris en charge (JPEG).';
							return;
						}
						if ($file_infos[0] < $file_infos[1]) {
							$width = ($height / $file_infos[1]) * $file_infos[0];
						} else {
							$height = ($width / $file_infos[0]) * $file_infos[1];
						}
						$image_p = imagecreatetruecolor($width, $height);
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $file_infos[0], $file_infos[1]);
						if (file_exists($img_avatar)) {
							files::suppFile($img_avatar);
						}
						if (!imagejpeg($image_p, $img_avatar, 90)) {
							if (file_exists($img_avatar)) { files::suppFile($img_avatar); }
							$this->template['erreur'] = 'Impossible de redimensionner l\'image.<br />Code erreur : ' . __LINE__;
							return;
						}
						imagedestroy($image_p);
						files::chmodFile($img_avatar);
						if (!file_exists($img_avatar)) {
							if (file_exists($img_avatar)) { files::suppFile($img_avatar); }
							$this->template['erreur'] = 'Impossible de redimensionner l\'image.<br />Code erreur : ' . __LINE__;
							return;
						}

					// Sinon on déplace directement l'image dans le répertoire des avatars.
					} else {
						if (file_exists($img_avatar)) {
							files::suppFile($img_avatar);
						}
						if (!files::copie($file_temp, $img_avatar)) {
							if (file_exists($img_avatar)) { files::suppFile($img_avatar); }
							$this->template['erreur'] = 'Impossible de récupérér l\'image.<br />Code erreur : ' . __LINE__;
							return;
						}
					}

					// On crée la vignette de l'avatar.
					$width = 50;
					$height = 50;
					$width_i = 50;
					$height_i = 50;
					$image = FALSE;
					$image = imagecreatefromjpeg($file_temp);
					$dst_x = 0;
					$dst_y = 0;
					if ($file_infos[0] < $file_infos[1]) {
						$width_i = ($height / $file_infos[1]) * $file_infos[0];
						$dst_x = ($width - $width_i) / 2;
					} else {
						$height_i = ($width / $file_infos[0]) * $file_infos[1];
						$dst_y = ($height - $height_i) / 2;
					}
					$image_p = imagecreatetruecolor($width, $height);
					$bg = imagecolorallocate($image_p, 255, 255, 255);
					imagefill($image_p, 0, 0, $bg);
					imagecopyresampled($image_p, $image, $dst_x, $dst_y, 0, 0, $width_i, $height_i, $file_infos[0], $file_infos[1]);
					if (file_exists($img_avatar_thumb)) {
						files::suppFile($img_avatar_thumb);
					}
					if (!imagejpeg($image_p, $img_avatar_thumb, 90)) {
						if (file_exists($img_avatar)) { files::suppFile($img_avatar); }
						$this->template['erreur'] = 'Impossible de créer la vignette de l\'avatar.';
						return;
					}
					imagedestroy($image_p);
					files::chmodFile($img_avatar_thumb);
					if (!file_exists($img_avatar_thumb)) {
						if (file_exists($img_avatar)) { files::suppFile($img_avatar); }
						$this->template['erreur'] = 'Impossible de créer la vignette de l\'avatar.';
						return;
					}

					// On indique dans la base de données que l'on doit utiliser l'avatar.
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET user_avatar = "1"
									   WHERE user_id = "' . $this->template['membre_profil'][0]['user_id'] . '"';
					if ($this->mysql->requete($mysql_requete)) {
						$this->template['succes'] = 'Votre avatar a été changé.';
						$this->template['membre_profil'][0]['user_avatar'] = 1;
						$this->template['membre_user'][0]['user_avatar'] = 1;
					} else {
						$this->template['erreur'] = 'Impossible de créer l\'avatar.';
						return;
					}

				}

				$this->headersNoCache();
				break;

			default :
				header('Location: ' . outils::genLink('?cat=1'));
				exit;
		}

	}
	function membre_infos() {
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'users.user_id,
								 ' . MYSQL_PREF . 'users.user_login,
								 ' . MYSQL_PREF . 'users.user_mail,
								 ' . MYSQL_PREF . 'users.user_mail_public,
								 ' . MYSQL_PREF . 'users.user_web,
								 ' . MYSQL_PREF . 'users.user_lieu,
								 ' . MYSQL_PREF . 'users.user_avatar,
								 ' . MYSQL_PREF . 'users.user_newsletter,
								 ' . MYSQL_PREF . 'users.user_date_creation,
								 ' . MYSQL_PREF . 'users.user_date_derniere_visite,
								 ' . MYSQL_PREF . 'groupes.groupe_titre,
								 ' . MYSQL_PREF . 'groupes.groupe_commentaires,
								 ' . MYSQL_PREF . 'groupes.groupe_upload,
								 ' . MYSQL_PREF . 'groupes.groupe_newsletter
							FROM ' . MYSQL_PREF . 'users JOIN ' . MYSQL_PREF . 'groupes USING(groupe_id)
						   WHERE ' . MYSQL_PREF . 'users.user_login = "' . outils::protege_mysql($_GET['profil'], $this->mysql->lien) . '"';
		$this->template['membre_profil'] = $this->mysql->select($mysql_requete);
		if (!is_array($this->template['membre_profil'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}
		$this->template['membre_profil'][0]['user_nom'] = str_replace('_', ' ', $this->template['membre_profil'][0]['user_login']);
	}
	function verifSID() {
		if (empty($_POST)) {
			return;
		}
		if (empty($_POST['sid'])
		 || $_POST['sid'] != md5($this->template['membre_user'][0]['user_session_id'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}
	}
	function headersNoCache() {
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
	}



	/*
	 *	Sections.
	*/
	function section() {
		switch ($_GET['section']) {
			case 'tags' :
				$id = '';
				if (isset($_GET['alb']) && preg_match('`^[1-9]\d{0,9}$`', $_GET['alb'])) {
					$id = $_GET['alb'];
				} elseif (isset($_GET['cat']) && preg_match('`^[1-9]\d{0,9}$`', $_GET['cat'])) {
					$id = $_GET['cat'];
				} else {
					$id = 1;
				}
				if (!$this->config['active_tags']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}
				$this->template['infos']['objet'] = 'tags';
				$this->params['objet_id'] = $id;
				$this->infos_categorie();
				$this->template['infos']['parent_nom'] = $this->params['objet_actuel']['categorie_nom'];
				$this->tags(1);
				$this->template['categorie'] = $this->params['objet_actuel'];				
				break;

			case 'historique' :

				// La fonctionnalité est-elle activée ?
				if (!$this->config['active_historique']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$this->template['infos']['objet'] = 'historique';

				// Images récentes.
				$this->nouvelles_images();
				$this->template['display']['recentes'] = $this->choix['recent'];
				$time_limit = time() - ($this->choix['recent'] * 24 * 3600);

				$cat = 1;
				$type = 'cat';
				if (isset($_GET['cat']) && preg_match('`^[1-9]\d{0,9}$`', $_GET['cat'])) {
					$cat = $_GET['cat'];
					$type = 'cat';
				}
				if (isset($_GET['alb']) && preg_match('`^[1-9]\d{0,9}$`', $_GET['alb'])) {
					$cat = $_GET['alb'];
					$type = 'alb';
				}
				$objet = $cat;
				if ($cat) {
					$mysql_requete = 'SELECT categorie_nom,
											 categorie_chemin
										FROM ' . MYSQL_PREF . 'categories
									   WHERE categorie_id = "' . $cat . '"';
					$cat_infos = $this->mysql->select($mysql_requete);
					if ($cat_infos && $cat_infos != 'vide') {
						$this->template['historique']['objet_nom'] = $cat_infos[0][0];
						$cat_infos[0][1] = ($cat_infos[0][1] == '.') ? '' : $cat_infos[0][1];
						$cat = 'AND image_chemin LIKE "' . $cat_infos[0][1] . '%" ';
					}
					$this->template['infos']['parent_nom'] = $cat_infos[0]['categorie_nom'];
				} else {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				// Lien historique.
				$this->template['historique']['lien'] = $type . '=' . $objet;

				// On récupère toutes les dates d'ajout différentes, et le nombre d'images correspondantes à chaque date.
				$mysql_requete = 'SELECT DISTINCT image_date,
										 COUNT(image_date)
									FROM ' . MYSQL_PREF . 'images
								   WHERE image_visible = "1" ' 
									   . $cat
									   . $this->images_protect() . '
							    GROUP BY image_date DESC';
				$dates_ajout = $this->mysql->select($mysql_requete);

				// On récupère toutes les dates de création différentes, et le nombre d'images correspondantes à chaque date.
				$mysql_requete = 'SELECT DISTINCT image_date_creation,
										 COUNT(image_date_creation)
									FROM ' . MYSQL_PREF . 'images
								   WHERE image_visible = "1" ' 
									   . $cat . '
									 AND image_date_creation != "0" ' 
									   . $this->images_protect() . '
								GROUP BY LENGTH(image_date_creation) DESC,
										 image_date_creation DESC';
				$dates_creation = $this->mysql->select($mysql_requete);

				// On génère le tableau des dates d'ajout.
				$annees_ajout = array();
				if ($dates_ajout && is_array($dates_ajout)) {
					for ($i = 0; $i < count($dates_ajout); $i++) {
						if (preg_match('`^\d{1,10}$`', $dates_ajout[$i][0])) {
							$annees_ajout[date('Y', $dates_ajout[$i][0])][date('d-m', $dates_ajout[$i][0])]['date'] = $dates_ajout[$i][0];
							@$annees_ajout[date('Y', $dates_ajout[$i][0])][date('d-m', $dates_ajout[$i][0])]['num'] += $dates_ajout[$i][1];
						}
					}
					$tableaux_ajout = '';
					foreach ($annees_ajout as $y => $days) {
						$tableaux_ajout .= '<div id="h_adate_' . $y . '"><p class="h_caption">' . $y . '</p>';
						$tableaux_ajout .= '<table summary="Images ajoutées en ' . $y . '" class="date_ajout">';
						$tableaux_ajout .= '<tr><th class="h_date">date</th><th>nb. d\'images</th></tr>';
						foreach ($days as $d => $v) {
							$date = explode('-', $d);
							$lien = $date[0] . '-' . $date[1] . '-' . $y . '&amp;' . $type . '=' . $objet;
							$date = mktime(0, 0, 0, $date[1], $date[0], $y);
							$recentes = ($this->choix['recent'] && ($date > $time_limit)) ? ' class="h_recentes"' : '';
							$date = strftime('%d %B', $date);
							$s = ($v['num'] > 1) ? 's' : '';
							$lien = outils::genLink('?date_ajout=' . $lien, '', $this->template['historique']['objet_nom']);
							$tableaux_ajout .= '<tr' . $recentes . '><td class="h_date">' . $date . '</td><td><a href="' . $lien . '">' . $v['num'] . ' image' . $s . '</a></td></tr>';
						}
						$tableaux_ajout .= '</table></div>';
					}
					if ($tableaux_ajout) {
						$this->template['historique']['dates_ajout'] = $tableaux_ajout;
					}
				}

				// On génère le tableau des dates de création.
				$annees_creation = array();
				if ($dates_creation && is_array($dates_creation)) {
					for ($i = 0; $i < count($dates_creation); $i++) {
						if (preg_match('`^\d{1,10}$`', $dates_creation[$i][0])) {
							$annees_creation[date('Y', $dates_creation[$i][0])][date('d-m', $dates_creation[$i][0])]['date'] = $dates_creation[$i][0];
							@$annees_creation[date('Y', $dates_creation[$i][0])][date('d-m', $dates_creation[$i][0])]['num'] += $dates_creation[$i][1];
						}
					}
					$tableaux_creation = '';
					foreach ($annees_creation as $y => $days) {
						$tableaux_creation .= '<div id="h_cdate_' . $y . '"><p class="h_caption">' . $y . '</p>';
						$tableaux_creation .= '<table summary="Images créées en ' . $y . '" class="date_creation">';
						$tableaux_creation .= '<tr><th class="h_date">date</th><th>nb. d\'images</th></tr>';
						foreach ($days as $d => $v) {
							$date = explode('-', $d);
							$lien = $date[0] . '-' . $date[1] . '-' . $y . '&amp;' . $type . '=' . $objet;
							$date = mktime(0, 0, 0, $date[1], $date[0], $y);
							$date = strftime('%d %B', $date);
							$s = ($v['num'] > 1) ? 's' : '';
							$lien = outils::genLink('?date_creation=' . $lien, '', $this->template['historique']['objet_nom']);
							$tableaux_creation .= '<tr><td class="h_date">' . $date . '</td><td><a href="' . $lien . '">' . $v['num'] . ' image' . $s . '</a></td></tr>';
						}
						$tableaux_creation .= '</table></div>';
					}
					if ($tableaux_creation) {
						$this->template['historique']['dates_creation'] = $tableaux_creation;
					}
				}
				break;
			case 'contact' :
				if ((empty($this->config['galerie_contact_text']) && 
				     empty($this->config['galerie_contact']))
				  || empty($this->config['admin_mail'])) {
						header('Location: ' . outils::genLink('?cat=1'));
						exit;
				}

				$this->template['infos']['galerie_key'] = $this->config['galerie_key'];
				$this->template['contact']['contact_form'] = 1;
				if (isset($_POST['contact_message']) &&
					isset($_POST['contact_sujet']) &&
					isset($_POST['contact_mail']) &&
					isset($_POST['contact_nom'])) {
					$ok = 1;

					// Anti-spam ?
					if (empty($_POST['molpac']) || !preg_match('`^[a-z0-9]{32}$`', $_POST['molpac'])) {
						$ok = 0;
					}
					if (empty($_POST['preview'])) {
						$time = time();
						$time_md5_array = array();
						for ($i = -5; $i < 1; $i++) {
							$time_md5 = md5($time+$i);
							$time_key_md5 = md5($time_md5 . $this->config['galerie_key']);
							array_push($time_md5_array, $time_key_md5);
						}
						if (in_array($_POST['molpac'], $time_md5_array)) {
							$this->template['contact']['erreur'] = 'Prenez votre temps pour écrire votre message !';
							$ok = 0;
						}
					}
					if ($ok) {

						$message = trim($_POST['contact_message']);
						$expediteur = trim($_POST['contact_mail']);
						$sujet = trim($_POST['contact_sujet']);
						$nom = trim($_POST['contact_nom']);

						// Un peu de nettoyage...
						$expediteur = preg_replace('`[\s\t\r\n\x5c]+`', '', $expediteur);
						$nom = preg_replace('`[\s\t\r\n\x5c]+`', '', $nom);
						$sujet = preg_replace('`[\t\r\n\x5c]+`', '', $sujet);
						$expediteur = preg_replace('`(?:to:|b?cc:|from:|content-type:)`i', '', $expediteur);
						$nom = preg_replace('`(?:to:|b?cc:|from:|content-type:)`i', '', $nom);
						$sujet = preg_replace('`(?:to:|b?cc:|from:|content-type:)`i', '', $sujet);

						// On vérifie s'il y a de la matière !
						if (!preg_match('`\w`', $message)) {
							$this->template['contact']['erreur'] = 'Votre message est vide !';
							$ok = 0;
						}
						if (!preg_match('`\w`', $sujet)) {
							$this->template['contact']['erreur'] = 'Le sujet de votre message est vide !';
							$ok = 0;
						}
						if (!preg_match('`\w`', $expediteur)) {
							$this->template['contact']['erreur'] = 'Votre adresse courriel est vide !';
							$ok = 0;
						}
						if (!preg_match('`\w`', $nom)) {
							$this->template['contact']['erreur'] = 'Votre nom est vide !';
							$ok = 0;
						}

						// On limite la longueur de chaque élément.
						if (strlen($message) > 50000) {
							$this->template['contact']['erreur'] = 'Votre message est trop long.';
							$ok = 0;
						}
						if (strlen($sujet) > 200) {
							$this->template['contact']['erreur'] = 'Le sujet de votre message est trop long.';
							$ok = 0;
						}
						if (strlen($expediteur) > 300) {
							$this->template['contact']['erreur'] = 'Votre adresse courriel est trop longue.';
							$ok = 0;
						}
						if (strlen($expediteur) > 80) {
							$this->template['contact']['erreur'] = 'Votre nom est trop long.';
							$ok = 0;
						}

						if ($ok) {
							if (!preg_match('`^' . outils::email_address() . '$`i', $expediteur)) {
								$this->template['contact']['erreur'] = 'Votre adresse courriel n\'est pas valide.';
								$ok = 0;
							}

							// Si tout est ok, on envoi le message.
							if ($ok) {

								$from = $nom . ' <' . $expediteur . '>';

								// Message.
								$message = str_replace("\x5cn", "\n", $message);
								$message = str_replace("\x5cr", "\r", $message);
								$message = str_replace("\x5ct", "\t", $message);
								$message = stripslashes($message);
								$message .= "\n\n" . '-- ' . "\n";
								$message .= 'Ce message vous a été envoyé par le formulaire de contact d\'iGalerie.' . "\n";
								$message .= 'IP expéditeur : ' . $_SERVER['REMOTE_ADDR'] . "\n";

								// Envoi du mail.
								unset($this->template['contact']['contact_form']);
								if (outils::send_mail($this->config['admin_mail'], $sujet, $message, $from)) {
									$this->template['contact']['succes'] = 'Votre message a été envoyé.';
								} else {
									$this->template['contact']['erreur'] = 'Une erreur serveur s\'est produite : votre message n\'a pas pu être envoyé.';
								}
							}
						}
					}
				}
				break;

			case 'plan' :
				$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'categories WHERE categorie_id = "1"';
				$galerie = $this->mysql->select($mysql_requete);
				settype($this->template['plan'], 'string');
				$this->params['nb_cat'] = 0;
				$this->params['nb_alb'] = 0;

				$this->nouvelles_images();
				$this->template['display']['recentes'] = $this->choix['recent'];
				$this->plan();

				$images = $galerie[0]['categorie_images'];
				$categories = $this->params['nb_cat'];
				$albums = $this->params['nb_alb'];
				$poids = $galerie[0]['categorie_poids'];

				if (isset($this->template['membre_user'])) {
					$mysql_requete = 'SELECT COUNT(image_id)
									    FROM ' . MYSQL_PREF . 'images
									   WHERE image_visible = "1" '
										   . $this->users_pass();
					$images = $this->mysql->select($mysql_requete, 5);

					$mysql_requete = 'SELECT COUNT(categorie_id)
									    FROM ' . MYSQL_PREF . 'categories
									   WHERE categorie_visible = "1" 
										 AND categorie_derniere_modif != "0"
										 AND categorie_id != "1" '
										    . $this->users_pass('categories.categorie_pass');
					$albums = $this->mysql->select($mysql_requete, 5);

					$mysql_requete = 'SELECT COUNT(categorie_id)
									    FROM ' . MYSQL_PREF . 'categories
									   WHERE categorie_visible = "1" 
										 AND categorie_derniere_modif = "0"
										 AND categorie_id != "1" '
										    . $this->users_pass('categories.categorie_pass');
					$categories = $this->mysql->select($mysql_requete, 5);

					$mysql_requete = 'SELECT SUM(categorie_poids)
									    FROM ' . MYSQL_PREF . 'categories
									   WHERE categorie_visible = "1" 
										 AND categorie_derniere_modif != "0"
										 AND categorie_id != "1" '
										    . $this->users_pass('categories.categorie_pass');
					$poids = $this->mysql->select($mysql_requete, 5);
					$poids = ($poids) ? $poids : 0;
				}
				
				$is = ($images > 1) ? 's' : '';
				$cs = ($categories > 1) ? 's' : '';
				$as = ($albums > 1) ? 's' : '';
				$g = 'La galerie contient ' . $images . ' image' . $is;
				$g .= ' dans ' . $albums . ' album' . $as;
				$g .= ' et ' . $categories . ' catégorie' . $cs . ',';
				$g .= ' pour un total de ' . outils::poids($poids) . '.<br /><br />';
				$this->template['infos']['galerie'] = $g;
				break;
			case 'pass' :
				$ibdd = 'categorie';
				if (isset($_GET['img'])) {
					$type = 'img';
					$ibdd = 'image';
				} elseif (isset($_GET['alb'])) {
					$type = 'alb';
				} elseif (isset($_GET['commentaires'])) {
					$type = 'commentaires';
				} elseif (isset($_GET['votes'])) {
					$type = 'votes';
				} elseif (isset($_GET['hits'])) {
					$type = 'hits';
				} elseif (isset($_GET['images'])) {
					$type = 'images';
				} elseif (isset($_GET['recentes'])) {
					$type = 'recentes';
				} else {
					$type = 'cat';
				}
				if (empty($_GET[$type]) 
				 || !preg_match('`^[1-9]\d{0,9}$`', $_GET[$type])
				 || isset($this->template['membre_user'][0]['groupe_album_pass_mode'])) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}
				$mysql_requete = 'SELECT ' . $ibdd . '_nom,
										 ' . $ibdd . '_pass
									FROM ' . MYSQL_PREF . $ibdd . 's 
								   WHERE ' . $ibdd . '_id = "' . $_GET[$type] . '"';
				$infos = $this->mysql->select($mysql_requete);
				if (empty($infos[0][$ibdd . '_nom'])) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				$img_name = '';
				$cat_name = '';
				if ($ibdd == 'categorie') {
					$cat_name = $infos[0][$ibdd . '_nom'];
				}
				if ($ibdd == 'image') {
					$img_name = $infos[0][$ibdd . '_nom'];
				}
				$l =  outils::genLink('?' . $type . '=' . $_GET[$type], $img_name, $cat_name);

				if (empty($infos[0][$ibdd . '_pass'])) {
					header('Location: ' . $l);
					exit;
				}

				// On vérifie le mot de passe.
				if (isset($_POST['password'])) {
					$pass_id = preg_replace('`^(\d+):.+`', '$1', $infos[0][$ibdd . '_pass']);
					$user_pass = $pass_id . ':' . $_POST['password'];
					if ($user_pass == $infos[0][$ibdd . '_pass']) {
						$this->passwords->ajouter($pass_id, outils::crypte($user_pass, $this->config['galerie_key']));
						$this->passwords->ecrire();
						header('Location: ' . $l);
						exit;
					}
				}

				$this->template['infos']['obj_nom'] = 'cet objet';
				break;
			case 'recherche':
				if (!$this->config['active_advsearch']) {
					header('Location: ' . outils::genLink('?cat=1'));
					exit;
				}

				// On convertit les variables POST en paramètres URL réduit.
				if (!empty($_POST) && isset($_POST['s_query'])) {

					$recherche = 'search=' . urlencode($_POST['s_query']) . '&sadv=';

					// Cases à cocher.
					$params = '';
					$params .= (isset($_POST['s_mode']) && $_POST['s_mode'] == 'et') ? '1' : '0';
					$params .= (isset($_POST['s_nom'])) ? '1' : '0';
					$params .= (isset($_POST['s_path'])) ? '1' : '0';
					$params .= (isset($_POST['s_desc'])) ? '1' : '0';
					$params .= (isset($_POST['s_mc'])) ? '1' : '0';
					$params .= (isset($_POST['s_com'])) ? '1' : '0';
					
					$params .= (isset($_POST['s_casse'])) ? '1' : '0';
					$params .= (isset($_POST['s_accents'])) ? '1' : '0';

					$params .= (isset($_POST['s_date'])) ? '1' : '0';
					$params .= (isset($_POST['s_date_type']) && $_POST['s_date_type'] == 'date_creation') ? '1' : '0';

					$params .= (isset($_POST['s_taille'])) ? '1' : '0';
					$params .= (isset($_POST['s_poids'])) ? '1' : '0';

					$params .= (isset($_POST['s_make'])) ? '1' : '0';
					$params .= (isset($_POST['s_model'])) ? '1' : '0';

					// Albums.
					$albums = '';
					if (isset($_POST['s_alb']) && is_array($_POST['s_alb'])) {
						for ($i = 0; $i < count($_POST['s_alb']); $i++) {
							$albums .= '-' . $_POST['s_alb'][$i];
						}
						$albums = substr($albums, 1);
						if ($albums == '1') {
							$albums = '';
						}
					}

					// Date.
					$date = '';
					if (isset($_POST['s_date'])
						&& isset($_POST['s_date_start_jour'])
						&& isset($_POST['s_date_start_mois'])
						&& isset($_POST['s_date_start_an'])
						&& isset($_POST['s_date_end_jour'])
						&& isset($_POST['s_date_end_mois'])
						&& isset($_POST['s_date_end_an'])) {
						$date .= $_POST['s_date_start_jour'] . '-';
						$date .= $_POST['s_date_start_mois'] . '-';
						$date .= $_POST['s_date_start_an'] . '-';
						$date .= $_POST['s_date_end_jour'] . '-';
						$date .= $_POST['s_date_end_mois'] . '-';
						$date .= $_POST['s_date_end_an'];
					}

					// Dimensions.
					$taille = '';
					if (isset($_POST['s_taille'])
						&& isset($_POST['s_width_start'])
						&& isset($_POST['s_width_end'])
						&& isset($_POST['s_height_start'])
						&& isset($_POST['s_height_end'])) {
						$taille .= $_POST['s_width_start'] . '-';
						$taille .= $_POST['s_width_end'] . '-';
						$taille .= $_POST['s_height_start'] . '-';
						$taille .= $_POST['s_height_end'];
					}

					// Poids.
					$poids = '';
					if (isset($_POST['s_poids'])
						&& isset($_POST['s_poids_start'])
						&& isset($_POST['s_poids_end'])) {
						$poids .= $_POST['s_poids_start'] . '-';
						$poids .= $_POST['s_poids_end'];
					}

					// On 'enchaîne' le tout.
					if ($params) { $recherche .= 'o' . $params; }
					if ($albums) { $recherche .= '.a' . $albums; }
					if ($date) { $recherche .= '.d' . $date; }
					if ($taille) { $recherche .= '.t' . $taille; }
					if ($poids) { $recherche .= '.p' . $poids; }

					// On redirige vers la recherche...
					header('Location: ' . outils::genLink('?' . $recherche, '', '', 0, '&'));
					exit;
				}

				// Récupération des albums de la galerie.
				$this->template['search_params'] = recherche::adv_search();
				$this->template['list_albums'] = $this->list_albums();
				break;
		}

		// On n'a plus besoin de la bdd.
		$this->mysql->fermer();

		// Personnalisation utilisateurs.
		$this->template['user']['perso'] = 0;
		if ($this->config['user_perso']) {
			$this->template['user']['style'] = $this->config['user_style'];
			$this->template['user']['recentes'] = $this->config['user_recentes'];
			if ($this->template['user']['recentes'] && ($_GET['section'] == 'plan' || $_GET['section'] == 'historique')) {
				$this->template['user']['montrer'] = 1;
			} else {
				$this->template['user']['montrer'] = 0;
			}
			if ($this->template['user']['montrer'] ||
			    $this->template['user']['style']) {
				$this->template['user']['perso'] = 1;
			}
		}
	}

	
	
	/*
	 *	On établie une liste de tous les albums pour
	 *	une liste à sélection multiple.
	*/
	function list_albums($parent = '', $niveau = 0) {
		static $list;
		$mysql_requete = 'SELECT categorie_id,
								 categorie_nom,
								 categorie_chemin,
								 categorie_derniere_modif,
								 categorie_pass 
						   FROM ' . MYSQL_PREF . 'categories 
						  WHERE categorie_visible = "1"
						    AND categorie_chemin REGEXP "^' . $parent . '[^/]+/$" '
							  . $this->images_protect('categorie') .
					 ' ORDER BY categorie_nom ASC';
		$cats = $this->mysql->select($mysql_requete);
		if (is_array($cats)) {
			$pos = '';
			if ($niveau) {
				$pos .= str_repeat('&nbsp;', $niveau*4) . '|--';
			} else {
				$selected = (in_array(1, $this->template['search_params']['albums'])) ? ' selected="selected"' : '';
				$list .= '<option' . $selected . ' value="1">Tous</option>';
			}
			for ($i = 0; $i < count($cats); $i++) {
				$selected = (in_array($cats[$i]['categorie_id'], $this->template['search_params']['albums'])) ? ' selected="selected"' : '';
				$list .= "\r\t\t\t\t\t\t\t\t\t\t\t" . '<option' . $selected . ' value="' . $cats[$i]['categorie_id'] . '">' . $pos . strip_tags($cats[$i]['categorie_nom']) . '</option>';
				if (!$cats[$i]['categorie_derniere_modif']) {
					$this->list_albums($cats[$i]['categorie_chemin'], $niveau+1);
				}
			}
		}
		return $list;
	}


	/*
	 *	On détermine le nombre de vignettes à afficher,
	 *	selon les choix admin et utilisateur.
	*/
	function nb_vignettes() {
		$redirect = 0;
		$this->choix['vn'] = $this->config['vignettes_col'];
		$this->choix['vl'] = $this->config['vignettes_line'];
		if ($this->config['user_perso'] && $this->config['user_vignettes']) {
			$choice[0]['nom'] = 'vn'; $choice[0]['defaut'] = $this->config['vignettes_col'];
			$choice[1]['nom'] = 'vl'; $choice[1]['defaut'] = $this->config['vignettes_line'];

			for ($i = 0; $i < count($choice); $i++) {
				if ($valeur = $this->prefs->lire($choice[$i]['nom'])) {
					if (!preg_match('`^([1-9]|[12][0-9])$`', $valeur)) {
						break;
					}
					$this->choix[$choice[$i]['nom']] = $valeur;
				} else {
					$this->choix[$choice[$i]['nom']] = $choice[$i]['defaut'];
				}
				if (isset($_GET[$choice[$i]['nom']]) && preg_match('`^([1-9]|[12][0-9])$`', $_GET[$choice[$i]['nom']])) {

					// On fait rediriger vers la première page s'il y a eu changement du nombre de vignettes.
					$valeur = $this->prefs->lire($choice[$i]['nom']);
					if ($valeur !== $_GET[$choice[$i]['nom']] || 
					    $this->choix[$choice[$i]['nom']] !== $_GET[$choice[$i]['nom']]) {
						$redirect = 1;
					}

					$this->prefs->ajouter($choice[$i]['nom'], $_GET[$choice[$i]['nom']]);
					$this->choix[$choice[$i]['nom']] = $_GET[$choice[$i]['nom']];
				}
			}
		}
		if ($redirect) {

			// On récupère les éventuels autres changements de préférences.
			$this->infos_vignettes();
			if ($this->params['objet_type'] == 'alb' || $this->params['objet_type'] == 'search') {
				$this->ordre();
			}
			$this->nouvelles_images();

			// On enregistre tout dans le cookie.
			$this->prefs->ecrire();

			// On redirige vers la première page.
			$img_name = '';
			$cat_name = '';
			if (isset($_GET['cat']) 
			 || isset($_GET['alb'])
			 || isset($_GET['images'])
			 || isset($_GET['commentaires'])
			 || isset($_GET['hits'])
			 || isset($_GET['votes'])
			 || isset($_GET['recentes'])) {
				if (isset($_GET['alb'])) {
					$obj_id = $_GET['alb'];
				} elseif (isset($_GET['cat'])) {
					$obj_id = $_GET['cat'];
				} elseif (isset($_GET['images'])) {
					$obj_id = $_GET['images'];
				} elseif (isset($_GET['commentaires'])) {
					$obj_id = $_GET['commentaires'];
				} elseif (isset($_GET['hits'])) {
					$obj_id = $_GET['hits'];
				} elseif (isset($_GET['votes'])) {
					$obj_id = $_GET['votes'];
				} elseif (isset($_GET['recentes'])) {
					$obj_id = $_GET['recentes'];
				}
				$obj_id = (preg_match('`\d{1,12}`', $obj_id)) ? $obj_id : 0;
				$mysql_requete = 'SELECT categorie_nom FROM ' . MYSQL_PREF . 'categories 
					WHERE categorie_id = "' . $obj_id . '" 
					AND categorie_visible = "1"';
				if ($i = $this->mysql->select($mysql_requete, 5)) {
					$cat_name = $i;
				}
			}
			if (isset($_GET['img']) && preg_match('`\d{1,12}`', $_GET['img'])) {
				$mysql_requete = 'SELECT image_nom FROM ' . MYSQL_PREF . 'images 
					WHERE image_id = "' . $_GET['img'] . '" 
					AND image_visible = "1"';
				if ($i = $this->mysql->select($mysql_requete, 5)) {
					$img_name = $i;
				}
			}
			$params = '';
			$params .= (isset($_GET['alb'])) ? '&alb=' . htmlentities($_GET['alb']) : '';
			$params .= (isset($_GET['cat'])) ? '&cat=' . htmlentities($_GET['cat']) : '';
			$params .= (isset($_GET['search'])) ? '&search=' . urlencode($_GET['search']) : '';
			$params .= (isset($_GET['sadv'])) ? '&sadv=' . htmlentities($_GET['sadv']) : '';
			$params .= (isset($_GET['date_ajout'])) ? '&date_ajout=' . htmlentities($_GET['date_ajout']) : '';
			$params .= (isset($_GET['date_creation'])) ? '&date_creation=' . htmlentities($_GET['date_creation']) : '';
			$params .= (isset($_GET['tag'])) ? '&tag=' . urlencode($_GET['tag']) : '';
			$params .= (isset($_GET['images'])) ? '&images=' . htmlentities($_GET['images']) : '';
			$params .= (isset($_GET['recentes'])) ? '&recentes=' . htmlentities($_GET['recentes']) : '';
			$params .= (isset($_GET['hits'])) ? '&hits=' . htmlentities($_GET['hits']) : '';
			$params .= (isset($_GET['votes'])) ? '&votes=' . htmlentities($_GET['votes']) : '';
			$params .= (isset($_GET['commentaires'])) ? '&commentaires=' . htmlentities($_GET['commentaires']) : '';
			$params .= (isset($_GET['mimg'])) ? '&mimg=' . htmlentities($_GET['mimg']) : '';
			$params .= (isset($_GET['mfav'])) ? '&mfav=' . htmlentities($_GET['mfav']) : '';

			$l = outils::genLink('?' . substr($params,1), $img_name, $cat_name, 0, '&');
			header('Location: ' . $l);
			exit;
		}
		$this->params['limit_vignettes'] = $this->choix['vn'] * $this->choix['vl'];
		$this->template['infos']['nb_vignettes'] = $this->params['limit_vignettes'];
		$this->template['infos']['vignettes_col'] = $this->choix['vn'];
		$this->template['infos']['vignettes_line'] = $this->choix['vl'];
	}



	/*
	 *	On récupère les informations de la catégorie actuelle.
	*/
	function infos_categorie() {
		$this->template['stats']['nb_images'] = 0;
		$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'categories
			WHERE categorie_id = "' . $this->params['objet_id'] . '"';
		$this->params['objet_actuel'] = $this->mysql->select($mysql_requete, 11);
		if (!is_array($this->params['objet_actuel'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// La catégorie est-elle activée ?
		if ($this->params['objet_actuel']['categorie_visible'] != 1) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// Droits : on récupère et réduit les stats des albums protégés non autorisés.
		if (isset($this->template['membre_user'][0]['groupe_album_pass_mode']) &&
			$this->template['membre_user'][0]['groupe_album_pass_mode'] != 'tous') {
			$mysql_pass = 'categorie_pass != ""';
			if ($this->template['membre_user'][0]['groupe_album_pass_mode'] == 'select') {
				$passwords = unserialize($this->template['membre_user'][0]['groupe_album_pass']);
				for ($i = 0; $i < count($passwords); $i++) {
					$mysql_pass .= ' AND categorie_pass != "' . $passwords[$i] . '" ';
				}
			}
			$path = $this->params['objet_actuel']['categorie_chemin'];
			$path = ($path == '.') ? '' : $path;
			$mysql_requete = 'SELECT categorie_chemin,
									 categorie_poids,
									 categorie_images,
									 categorie_hits,
									 categorie_commentaires,
									 categorie_note,
									 categorie_votes
								FROM ' . MYSQL_PREF . 'categories
							   WHERE categorie_derniere_modif > 0
								 AND categorie_visible = "1"
							     AND categorie_chemin LIKE "' . $path . '%"
							     AND ' . $mysql_pass;
			$stats = $this->mysql->select($mysql_requete, 1);

			if (is_array($stats)) {

				//  Stats de la catégorie actuelle.
				$this->params['objet_actuel']['categorie_poids'] -= array_sum($stats['categorie_poids']);
				$this->params['objet_actuel']['categorie_images'] -= array_sum($stats['categorie_images']);
				$this->params['objet_actuel']['categorie_hits'] -= array_sum($stats['categorie_hits']);
				$this->params['objet_actuel']['categorie_commentaires'] -= array_sum($stats['categorie_commentaires']);
				$this->params['objet_actuel']['categorie_votes'] -= array_sum($stats['categorie_votes']);

				// Stats des objets de la catégorie.
				for ($i = 0; $i < count($stats['categorie_chemin']); $i++) {
					$cat = preg_replace('`^(' . $path . '[^/]+/).*`', '$1', $stats['categorie_chemin'][$i]);
					if (!isset($this->params['categories_pass'][$cat])) {
						$this->params['categories_pass'][$cat]['poids'] = 0;
						$this->params['categories_pass'][$cat]['images'] = 0;
						$this->params['categories_pass'][$cat]['hits'] = 0;
						$this->params['categories_pass'][$cat]['commentaires'] = 0;
						$this->params['categories_pass'][$cat]['votes'] = 0;
						$this->params['categories_pass'][$cat]['note'] = 0;
					}
					$this->params['categories_pass'][$cat]['poids'] += $stats['categorie_poids'][$i];
					$this->params['categories_pass'][$cat]['images'] += $stats['categorie_images'][$i];
					$this->params['categories_pass'][$cat]['hits'] += $stats['categorie_hits'][$i];
					$this->params['categories_pass'][$cat]['commentaires'] += $stats['categorie_commentaires'][$i];
					$this->params['categories_pass'][$cat]['votes'] += $stats['categorie_votes'][$i];
					if ($stats['categorie_note'][$i] > 0) {
						if ($this->params['categories_pass'][$cat]['note'] > 0) {
							$this->params['categories_pass'][$cat]['note'] = ($this->params['categories_pass'][$cat]['note'] + $stats['categorie_note'][$i]) / 2;
						} else {
							$this->params['categories_pass'][$cat]['note'] = $stats['categorie_note'][$i];
						}
					}
				}
			}
		}

		// Lien historique.
		$type = ($this->params['objet_actuel']['categorie_derniere_modif']) ? 'alb' : 'cat';
		$this->template['historique']['lien'] = $type . '=' . $this->params['objet_actuel']['categorie_id'];
	}



	/*
	 *	Choix utilisateurs : nouvelles images.
	 *	On détermine si on doit mettre en évidence les nouvelles images,
	 *	et si oui quelle est la durée de nouveauté de ces nouvelles images.
	*/
	function nouvelles_images() {
		$this->template['infos']['recent_jours'] = $this->config['galerie_recent'];
		if ($this->config['display_recentes']) {
			$this->choix['recent'] = $this->config['galerie_recent'];
		} else {
			$this->choix['recent'] = 0;
		}
		if ($this->config['user_perso'] && $this->config['user_recentes']) {
			$valeur_ra = $this->prefs->lire('ra');
			$valeur_rj = $this->prefs->lire('rj');

			// On prend les informations envoyées par le formulaire (si envoyées)...
			if (!empty($_GET['u'])) {
				if (isset($_GET['ra']) && isset($_GET['rj']) && preg_match('`^[1-9][0-9]{0,3}$`', $_GET['rj'])) {
					$this->prefs->ajouter('ra', 1);
					$this->prefs->ajouter('rj', $_GET['rj']);
					$this->choix['recent'] = $_GET['rj'];
					$this->template['infos']['recent_jours'] = $_GET['rj'];
				} elseif (empty($_GET['ra'])) {
					$this->prefs->ajouter('ra', 0);
					$this->choix['recent'] = 0;
					$this->template['infos']['recent_jours'] = ($valeur_rj === FALSE) ? $this->config['galerie_recent'] : $valeur_rj;
				}

			// ...sinon on prend celles du cookie (si présent).
			} elseif ($valeur_ra !== FALSE) {
				$this->choix['recent'] = ($valeur_ra == 1) ? $valeur_rj : 0;
				$this->template['infos']['recent_jours'] = $valeur_rj;
			}
		}
		$this->template['infos']['recent'] = $this->choix['recent'];
	}	



	/*
	 *	On récupère les informations de stats.
	*/
	function stats_categorie() {
		$this->template['stats']['poids'] = $this->params['objet_actuel']['categorie_poids'];
		$this->template['stats']['nb_images'] = $this->params['objet_actuel']['categorie_images'];
		$this->template['stats']['nb_hits'] = $this->params['objet_actuel']['categorie_hits'];
		if (empty($this->config['active_commentaires'])) {
			$this->template['stats']['nb_commentaires'] = -1;
		} else {
			$this->template['stats']['nb_commentaires'] = $this->params['objet_actuel']['categorie_commentaires'];
		}
		if (empty($this->config['active_votes'])) {
			$this->template['stats']['nb_votes'] = -1;
		} else {
			$this->template['stats']['nb_votes'] = $this->params['objet_actuel']['categorie_votes'];
		}

		// On détermine le nombre d'images nouvelles contenues dans la catégorie actuelle.
		if (!is_array($this->params['objet_actuel'])) {
			$this->template['stats']['nb_recentes'] = 0;
		} elseif ($this->choix['recent']) {

			$this->params['time_limit'] = time() - ($this->choix['recent'] * 24 * 3600);
			$chemin = $this->params['objet_actuel']['categorie_chemin'];
			if ($chemin == '.') {
				$chemin = '';
			}
			$mysql_requete = 'SELECT COUNT(*) FROM ' . MYSQL_PREF . 'images 
				WHERE image_chemin LIKE "' . $chemin . '%" 
				AND image_date > ' . $this->params['time_limit'] . ' 
				AND image_visible = "1"' . $this->users_pass();
			$this->template['stats']['nb_recentes'] = $this->mysql->select($mysql_requete, 5);
		}
	}



	/*
	  *	Utilisateurs : droits d'accès pour les albums protégés.
	*/
	function users_pass($type = 'images.image_pass') {
		$mysql_pass = '';
		if (isset($this->template['membre_user'][0]['groupe_album_pass_mode'])
		 && $this->template['membre_user'][0]['groupe_album_pass_mode'] != 'tous') {
			$mysql_pass = ' AND (' . MYSQL_PREF . $type . ' IS NULL';
			if ($this->template['membre_user'][0]['groupe_album_pass_mode'] == 'select') {
				if ($this->template['membre_user'][0]['groupe_album_pass_mode'] == 'select') {
					$passwords = unserialize($this->template['membre_user'][0]['groupe_album_pass']);
					for ($i = 0; $i < count($passwords); $i++) {
						$mysql_pass .= ' OR ' . MYSQL_PREF . $type . ' = "' . $passwords[$i] . '"';
					}
				}
			}
			$mysql_pass .= ')';
		}
		return $mysql_pass;
	}



	/*
	 *	On récupère les informations de toute la hiérarchie parente.
	*/
	function hierarchie() {
		if ($this->params['objet_actuel'] !== 'vide') {
			if (!isset($_GET['img'])) {
				$this->template['infos']['parent_nom'] = $this->params['objet_actuel']['categorie_nom'];
			}
			$objet = ($this->template['infos']['type'] == 'img') ? 'image' : 'categorie';
			$parent = dirname($this->params['objet_actuel'][$objet . '_chemin']);
			$cat_where = '';
			while ($parent != '.') {
				$cat_where .= 'categorie_chemin = "' . $parent . '/" OR ';
				$parent = dirname($parent);
			}
			if ($cat_where) {
				$cat_where = substr($cat_where, 0, strlen($cat_where)-4);
				$mysql_requete = 'SELECT categorie_id,
										 categorie_chemin,
										 categorie_nom,
										 categorie_visible
								    FROM ' . MYSQL_PREF . 'categories 
								   WHERE ' . $cat_where . ' 
								ORDER BY LENGTH(categorie_chemin) ASC';
				$this->params['parents'] = $this->mysql->select($mysql_requete);
				$this->template['infos']['hierarchie'] = '';
				$type = 'cat';
				$mrq = ($this->params['objet_type'] == 'img') ? 1 : 0;
				if (is_array($this->params['parents'])) {
					for ($i = 0; $i < count($this->params['parents']); $i++) {
						if ($mrq && $i == count($this->params['parents'])-1) {
							$type = 'alb';
							$this->template['image']['album'] = $this->params['parents'][$i]['categorie_nom'];
						}
						if ($i == count($this->params['parents'])-1) {
							$lien = '?' . $type . '=' . $this->params['parents'][$i]['categorie_id'] . '&amp;startnum=0';
							if (isset($_GET['img'])) {
								$this->template['infos']['parent_nom'] = $this->params['parents'][$i]['categorie_nom'];
							}
						} else {
							$lien = outils::genLink('?' . $type . '=' . $this->params['parents'][$i]['categorie_id'], '', $this->params['parents'][$i]['categorie_nom']);
						}
						$this->template['infos']['hierarchie'] .= '<a href="' . $lien . '">' . 
							strip_tags($this->params['parents'][$i]['categorie_nom']) . '</a>%sep';
					}
				}
			}
		} else {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}
	}



	/*
	 *	On récupère les informations de base des catégories voisines.
	*/
	function cat_voisines() {
		$dirname = dirname($this->params['objet_actuel']['categorie_chemin']);
		$dirname = ($dirname == '.') ? '' : $dirname . '/';
		if ($this->params['objet_id'] > 1) {
			$mysql_requete = 'SELECT categorie_nom,
									 categorie_id,
									 categorie_derniere_modif
								FROM ' . MYSQL_PREF . 'categories 
							   WHERE categorie_chemin REGEXP "^' . $dirname . '[^/]+/$"
								 AND categorie_visible = "1" '
								   . $this->users_pass('categories.categorie_pass') . '
							ORDER BY ' . $this->config['vignettes_cat_ordre'];
			$this->template['nav']['voisines'] = $this->mysql->select($mysql_requete);
		}
	}



	/*
	 *	On génère les liens pour la navigation entre les pages.
	*/
	function liens_pages() {

		$this->template['infos']['page_actuelle'] = 1;

		if ($this->params['objet_type'] == 'alb') {
			$this->params['nb_objets'] = $this->params['objet_actuel']['categorie_images'];
		}

		// On détermine le nombre de pages et la page actuelle.
		$this->template['infos']['nb_pages'] = ceil(($this->params['nb_objets']) / $this->params['limit_vignettes']);
		for ($n = 0; $n < $this->template['infos']['nb_pages']; $n++) {
			$num = $n * $this->params['limit_vignettes'];
			$this->template['nav']['pages'][$n + 1]['page'] = $num;
			if ($num == $this->params['startnum']) {
				$this->template['infos']['page_actuelle'] = $n + 1;
			}
		}

		// On détermine les pages suivantes, précédentes, de début et de fin.
		$this->template['nav']['suivante'][1] = $this->params['startnum'] + $this->params['limit_vignettes'];
		$this->template['nav']['precedente'][1] = $this->params['startnum'] - $this->params['limit_vignettes'];
		$this->template['nav']['premiere'][1] = 0;
		$this->template['nav']['derniere'][1] = ($this->template['infos']['nb_pages'] * $this->params['limit_vignettes']) - $this->params['limit_vignettes'];

		// On détermine la position de la catégorie actuelle.
		if (isset($this->template['nav']['voisines']) && $this->params['objet_type'] !== 'search') {
			for ($i = 0; $i < count($this->template['nav']['voisines']); $i++) {
				if ($this->template['nav']['voisines'][$i]['categorie_id'] == $this->params['objet_actuel']['categorie_id']) {
					$this->template['infos']['objet_num'] = $i+1;
					break;
				}
			}
		}
		if (isset($this->params['objet_id'])) {
			$this->template['infos']['objet'] = $this->params['objet_id'];
		}

		if ($this->params['startnum'] == 0) {
			$this->template['nav']['premiere'][0] = 1;
		}
		if ($this->template['nav']['precedente'][1] < 0) {
			$this->template['nav']['precedente'][0] = 1;
		}
		if ($this->template['nav']['suivante'][1] >= ($this->template['infos']['nb_pages'] * $this->params['limit_vignettes']) || 
		    $this->template['nav']['suivante'][1] >= $this->params['nb_objets']) {
			$this->template['nav']['suivante'][0] = 1;
		}
		if ($this->params['startnum'] >= $this->template['nav']['derniere'][1]) {
			$this->template['nav']['derniere'][0] = 1;
		}
		$this->template['infos']['nb_objets'] = $this->params['nb_objets'];
	}



	/*
	 *	Lien retour.
	*/
	function lien_retour() {
	
		if (($this->template['infos']['type'] != 'img'
		  && $this->params['objet_id'] < 2) === FALSE
		  && isset($this->template['infos']['objet_num'])
		  && !empty($this->template['infos']['hierarchie'])) {
			$parent_id = (isset($this->params['parents'])) ? $this->params['parents'][count($this->params['parents'])-1]['categorie_id'] : 1;
			if ($this->template['infos']['type'] != 'img') {
				$nb_vignettes = $this->config['vignettes_cat_line'] * $this->config['vignettes_cat_col'];
			} else {
				$nb_vignettes = $this->params['limit_vignettes'];
			}
			$objet_num = $this->template['infos']['objet_num'];
			$prvs = '';
			$parent_page = (ceil($objet_num / $nb_vignettes) * $nb_vignettes) - $nb_vignettes;
			$this->template['infos']['parent_startnum'] = $parent_page;
			if (empty($_GET['votes']) 
			 && empty($_GET['commentaires']) 
			 && empty($_GET['hits']) 
			 && empty($_GET['recentes'])
			 && empty($_GET['images'])
			 && empty($_GET['date_ajout'])
			 && empty($_GET['date_creation'])
			 && empty($_GET['tag'])
			 && empty($_GET['mfav'])
			 && empty($_GET['search'])
				) {
				$this->template['infos']['hierarchie'] = str_replace('&amp;startnum=0', '&amp;startnum=' . $parent_page, $this->template['infos']['hierarchie']);
			}
			if (preg_match('`href="(\?.+startnum.+)"`', $this->template['infos']['hierarchie'], $m)) {
				$dernier_lien = $m[1];
				$dernier_nom = $this->params['parents'][count($this->params['parents'])-1]['categorie_nom'];
				$nouveau_dernier_lien = outils::genLink($dernier_lien, '', $dernier_nom);
				$this->template['infos']['hierarchie'] = str_replace($dernier_lien, $nouveau_dernier_lien, $this->template['infos']['hierarchie']);
			}
			$this->template['infos']['hierarchie'] = str_replace('&amp;startnum=0', '', $this->template['infos']['hierarchie']);
			$this->template['nav']['retour_id'] = $parent_id;
		}

	}



	/*
	 *	Description de la catégorie.
	 *	Que l'on affiche uniquement pour la première page.
	*/
	function description_categorie() {
		if (isset($this->template['infos']['page_actuelle']) && 
			$this->template['infos']['page_actuelle'] == 1 && 
			!empty($this->params['objet_actuel']['categorie_description'])) {
			$this->template['infos']['description'] = str_replace('&', '&amp;', nl2br($this->params['objet_actuel']['categorie_description']));
		}
	}



	/*
	 *	Choix utilisateurs : informations sous vignettes.
	*/
	function infos_vignettes() {

		// Valeurs par défaut.
		$this->choix['sa'] = $this->config['display_cat_nom'];
		$this->choix['sn'] = $this->config['display_cat_nb_images'];
		$this->choix['si'] = $this->config['display_img_nom'];
		$this->choix['sy'] = $this->config['display_img_date'];
		$this->choix['sd'] = $this->config['display_img_taille'];
		$this->choix['sp'] = $this->config['display_img_poids'];
		$this->choix['sc'] = $this->config['display_img_comments'];
		$this->choix['sv'] = $this->config['display_img_votes'];
		$this->choix['sh'] = $this->config['display_img_hits'];

		if ($this->config['user_perso']) {
			$i = -1;
			$choice = array();
			if ($this->params['objet_type'] == 'cat') {
				if ($this->config['user_nom_categories']) { $i++; $choice[$i]['nom'] = 'sa'; $choice[$i]['defaut'] = $this->config['display_cat_nom']; }
				if ($this->config['user_nb_images']) { $i++; $choice[$i]['nom'] = 'sn'; $choice[$i]['defaut'] = $this->config['display_cat_nb_images']; }
			} else {
				if ($this->config['user_nom_images']) { $i++; $choice[$i]['nom'] = 'si'; $choice[$i]['defaut'] = $this->config['display_img_nom']; }
				if ($this->config['user_date']) { $i++; $choice[$i]['nom'] = 'sy'; $choice[$i]['defaut'] = $this->config['display_img_date']; }
				if ($this->config['user_taille']) { $i++; $choice[$i]['nom'] = 'sd'; $choice[$i]['defaut'] = $this->config['display_img_taille']; }
			}
			if ($this->config['user_poids']) { $i++; $choice[$i]['nom'] = 'sp'; $choice[$i]['defaut'] = $this->config['display_img_poids']; }
			if ($this->config['user_comments']) { $i++; $choice[$i]['nom'] = 'sc'; $choice[$i]['defaut'] = $this->config['display_img_comments']; }
			if ($this->config['user_votes']) { $i++; $choice[$i]['nom'] = 'sv'; $choice[$i]['defaut'] = $this->config['display_img_votes']; }
			if ($this->config['user_hits']) { $i++; $choice[$i]['nom'] = 'sh'; $choice[$i]['defaut'] = $this->config['display_img_hits']; }
			for ($i = 0; $i < count($choice); $i++) {
				$valeur_c = $this->prefs->lire($choice[$i]['nom']);
				if ($valeur_c !== FALSE) {
					$this->choix[$choice[$i]['nom']] = $valeur_c;
				} else {
					$this->choix[$choice[$i]['nom']] = $choice[$i]['defaut'];
				}
				if (!empty($_GET['u'])) {
					$v = (isset($_GET[$choice[$i]['nom']])) ? 1 : 0;
					$this->prefs->ajouter($choice[$i]['nom'], $v);
					$this->choix[$choice[$i]['nom']] = $v;
				}
			}
		}
	}



	/*
	 *	Paramètres d'affichage de divers éléments.
	*/
	function affichage_elements() {

		// Commentaires.
		if (empty($this->config['active_commentaires'])) {
			$this->template['user']['commentaires'] = 0;
			$this->template['display']['commentaires'] = 0;
		} else {
			$this->template['user']['commentaires'] = $this->config['user_comments'];
			$this->template['display']['commentaires'] = ($this->params['objet_type'] == 'cat') ? $this->config['display_cat_comments'] : $this->choix['sc'];
		}

		// Votes.
		if (empty($this->config['active_votes'])) {
			$this->template['user']['votes'] = 0;
			$this->template['display']['votes'] = 0;
		} else {
			$this->template['user']['votes'] = $this->config['user_votes'];
			$this->template['display']['votes'] = ($this->params['objet_type'] == 'cat') ? $this->config['display_cat_votes'] : $this->choix['sv'];
		}

		$this->template['display']['recentes'] = $this->choix['recent'];

		// Paramètres d'affichage.
		if ($this->params['objet_type'] == 'cat') {
			$this->template['display']['nom'] = $this->choix['sa'];
			$this->template['display']['nb_images'] = $this->choix['sn'];
			$this->template['display']['poids'] = ($this->params['objet_type'] == 'cat') ? $this->config['display_cat_poids'] : $this->choix['sp'];
			$this->template['display']['hits'] = ($this->params['objet_type'] == 'cat') ? $this->config['display_cat_hits'] : $this->choix['sh'];
			if ($this->template['display']['nb_images'] ||
			    $this->template['display']['poids'] ||
			    $this->template['display']['nom'] ||
			    $this->template['display']['hits'] ||
			    $this->template['display']['commentaires'] ||
			    $this->template['display']['votes'] ||
				$this->template['display']['nom']) {
				$this->template['display']['infos'] = 1;
			} else {
				$this->template['display']['infos'] = 0;
			}
		} else {
			$this->template['display']['nom'] = $this->choix['si'];
			$this->template['display']['date'] = $this->choix['sy'];
			$this->template['display']['taille'] = $this->choix['sd'];
			$this->template['display']['poids'] = $this->choix['sp'];
			$this->template['display']['hits'] = $this->choix['sh'];
			if ($this->template['display']['nom'] ||
			    $this->template['display']['date'] ||
			    $this->template['display']['taille'] ||
			    $this->template['display']['poids'] ||
			    $this->template['display']['hits'] ||
			    $this->template['display']['commentaires'] ||
			    $this->template['display']['votes']) {
				$this->template['display']['infos'] = 1;
			} else {
				$this->template['display']['infos'] = 0;
			}
		}
	}



	/*
	 *	Autorisation des choix utilisateurs.
	*/
	function user_autorisations() {
		if ($this->config['user_perso']) {
			$this->template['user']['style'] = $this->config['user_style'];
			$this->template['user']['recentes'] = $this->config['user_recentes'];
			$this->template['user']['commentaires'] = $this->config['user_comments'];
			$this->template['user']['votes'] = $this->config['user_votes'];
			if ($this->params['objet_type'] == 'cat') {
				$this->template['user']['nb_images'] = $this->config['user_nb_images'];
				$this->template['user']['poids'] = $this->config['user_poids'];
				$this->template['user']['hits'] = $this->config['user_hits'];
				$this->template['user']['nom'] = $this->config['user_nom_categories'];
				if ($this->template['user']['nb_images'] ||
				    $this->template['user']['nom'] ||
				    $this->template['user']['recentes']) {
					$this->template['user']['montrer'] = 1;
				} else {
					$this->template['user']['montrer'] = 0;
				}
			} else {
				$this->template['user']['vignettes'] = $this->config['user_vignettes'];
				$this->template['user']['ordre'] = $this->config['user_ordre'];
				$this->template['user']['nom'] = $this->config['user_nom_images'];
				$this->template['user']['date'] = $this->config['user_date'];
				$this->template['user']['taille'] = $this->config['user_taille'];
				$this->template['user']['poids'] = $this->config['user_poids'];
				$this->template['user']['hits'] = $this->config['user_hits'];
				if ($this->template['user']['nom'] ||
				    $this->template['user']['taille'] ||
				    $this->template['user']['poids'] ||
				    $this->template['user']['hits'] ||
				    $this->template['user']['commentaires'] ||
				    $this->template['user']['votes'] ||
				    $this->template['user']['recentes']) {
					$this->template['user']['montrer'] = 1;
				} else {
					$this->template['user']['montrer'] = 0;
				}
			}
			if ($this->template['user']['montrer'] ||
			    $this->template['user']['vignettes'] ||
				$this->template['user']['ordre'] ||
			    $this->template['user']['style']) {
				$this->template['user']['perso'] = 1;
			} else {
				$this->template['user']['perso'] = 0;
			}
		} else {
			$this->template['user']['perso'] = 0;
		}
	}
	function user_autorisations_image() {
		if ($this->config['user_perso']) {
			$this->template['user']['style'] = $this->config['user_style'];
			if ($this->template['user']['image_taille'] ||
			    $this->template['user']['style']) {
				$this->template['user']['perso'] = 1;
			} else {
				$this->template['user']['perso'] = 0;
			}
		} else {
			$this->template['user']['perso'] = 0;
		}
	}



	/*
	 *	On détermine l'ordre et le sens dans lequel
	 *	vont être afficher les images.
	*/
	function ordre() {
		$this->template['user']['ordre'] = $this->config['user_ordre'];
		if ($this->config['user_perso'] && $this->config['user_ordre']) {
			
			$ck = ($this->params['objet_type'] == 'cat') ? 'c' : 'i';
			$choice[0]['nom'] = $ck . 'o'; $choice[0]['defaut'] = $this->config['vignettes_ordre'];
			$choice[1]['nom'] = $ck . 's'; $choice[1]['defaut'] = $this->config['vignettes_sens'];

			for ($i = 0; $i < count($choice); $i++) {
				if ($valeur = $this->prefs->lire($choice[$i]['nom'])) {
					if (!preg_match('`^[a-z0-9_]+$`', $valeur)) {
						break;
					}
					$this->choix[$choice[$i]['nom']] = $valeur;
				} else {
					$this->choix[$choice[$i]['nom']] = $choice[$i]['defaut'];
				}
				if (isset($_GET[$choice[$i]['nom']]) && preg_match('`^[a-z0-9_]{1,40}$`', $_GET[$choice[$i]['nom']])) {
					$this->prefs->ajouter($choice[$i]['nom'], $_GET[$choice[$i]['nom']]);
					$this->choix[$choice[$i]['nom']] = $_GET[$choice[$i]['nom']];
				}
			}

			switch ($this->choix[$ck . 'o']) {
				case 'n' : $this->params['v_ordre'] = 'nom'; break;
				case 'p' : $this->params['v_ordre'] = 'poids'; break;
				case 'h' : $this->params['v_ordre'] = 'hits'; break;
				case 'd' : $this->params['v_ordre'] = 'date'; break;
				case 'm' : $this->params['v_ordre'] = 'date_creation'; break;
				case 'c' : $this->params['v_ordre'] = 'commentaires'; break;
				case 'v' : $this->params['v_ordre'] = 'votes'; break;
				case 'e' : $this->params['v_ordre'] = 'note'; break;
				case 't' : $this->params['v_ordre'] = 'largeur*image_hauteur'; break;
				default  : $this->params['v_ordre'] = $this->config['vignettes_ordre'];
			}

			switch ($this->choix[$ck . 's']) {
				case 'a' : $this->params['v_sens'] = 'ASC'; break;
				case 'd' : $this->params['v_sens'] = 'DESC'; break;
				default  : $this->params['v_sens'] = $this->config['vignettes_sens'];
			}

			$this->template['infos']['vignettes_ordre'] = (strstr($this->params['v_ordre'], '*')) ? 'taille' : $this->params['v_ordre'];
			$this->template['infos']['vignettes_sens']  = $this->params['v_sens'];

		} else {
			$this->params['v_ordre'] = $this->config['vignettes_ordre'];
			$this->params['v_sens']  = $this->config['vignettes_sens'];
		}
	}



	/*
	 *	On génère le tableau contenant toutes les infos de chaque image.
	*/
	function vignettes_album() {
		if ($this->params['objets'] !== 'vide') {
			if ($this->config['galerie_images_window'] == 0) {
				$params = '';
				if (isset($_GET['search'])) {
					$params = '&amp;search=' . urlencode($_GET['search']);
					$params .= (isset($_GET['sadv'])) ? '&amp;sadv=' . urlencode($_GET['sadv']) : '';
				} elseif (isset($_GET['images'])) {
					$params = '&amp;images=' . htmlentities($_GET['images']);
				} elseif (isset($_GET['recentes'])) {
					$params = '&amp;recentes=' . htmlentities($_GET['recentes']);
				} elseif (isset($_GET['hits'])) {
					$params = '&amp;hits=' . htmlentities($_GET['hits']);
				} elseif (isset($_GET['commentaires'])) {
					$params = '&amp;commentaires=' . htmlentities($_GET['commentaires']);
				} elseif (isset($_GET['votes'])) {
					$params = '&amp;votes=' . htmlentities($_GET['votes']);
				} elseif (isset($_GET['date_creation'])) {
					$params = '&amp;date_creation=' . htmlentities($_GET['date_creation']);
					$params .= (isset($_GET['cat'])) ? '&amp;cat=' . htmlentities($_GET['cat']) : '';
					$params .= (isset($_GET['alb'])) ? '&amp;alb=' . htmlentities($_GET['alb']) : '';
				} elseif (isset($_GET['date_ajout'])) {
					$params = '&amp;date_ajout=' . htmlentities($_GET['date_ajout']);
					$params .= (isset($_GET['cat'])) ? '&amp;cat=' . htmlentities($_GET['cat']) : '';
					$params .= (isset($_GET['alb'])) ? '&amp;alb=' . htmlentities($_GET['alb']) : '';
					$params .= (isset($_GET['mimg'])) ? '&amp;mimg=' . htmlentities($_GET['mimg']) : '';
				} elseif (isset($_GET['tag'])) {
					$params = '&amp;tag=' . urlencode($_GET['tag']);
					$params .= (isset($_GET['cat'])) ? '&amp;cat=' . htmlentities($_GET['cat']) : '';
					$params .= (isset($_GET['alb'])) ? '&amp;alb=' . htmlentities($_GET['alb']) : '';
				} elseif (isset($_GET['mimg'])) {
					$params = '&amp;mimg=' . urlencode($_GET['mimg']);
				} elseif (isset($_GET['mfav'])) {
					$params = '&amp;mfav=' . urlencode($_GET['mfav']);
				}
			}
			$cat_name = '';
			if (isset($this->params['objet_actuel']['categorie_nom'])) {
				$cat_name = $this->params['objet_actuel']['categorie_nom'];
			}
			for ($n = 0; $n < count($this->params['objets']); $n++) {
			
				$img_name = $this->params['objets'][$n]['image_nom'];

				// Type d'affichage de l'image.
				$image_text = (IMG_TEXTE) ? 'getitext.php?i=' : GALERIE_ALBUMS . '/';
				switch ($this->config['galerie_images_window']) {
					case 0 :
						$this->template['vignettes'][$n]['page'] = outils::genLink('?img=' . $this->params['objets'][$n]['image_id'] . $params, $img_name, $cat_name);
						break;
					case 1 :
						$this->template['vignettes'][$n]['page'] = GALERIE_PATH . '/' . $image_text . $this->params['objets'][$n]['image_chemin'];
						break;
					case 2 :
						$largeur = $this->params['objets'][$n]['image_largeur'] + 40;
						$hauteur = $this->params['objets'][$n]['image_hauteur'] + 30;
						$this->template['vignettes'][$n]['page'] = "javascript:window.open('" 
							. GALERIE_PATH . '/' . $image_text
							. $this->params['objets'][$n]['image_chemin']
							. "','','scrollbars=yes,status=no,resizable=yes,width=" 
							. $largeur 
							. ",height=" 
							. $hauteur  
							. ",top=0,left=0');void(0);";
						break;
				}

				// On détermine si l'image est a considérer comme récente.
				$this->params['time_limit'] = time() - ($this->choix['recent'] * 24 * 3600);
				if ($this->choix['recent'] && $this->params['objets'][$n]['image_date'] > $this->params['time_limit']) {
					$this->template['vignettes'][$n]['recent'] = 1;
				}

				// Autres informations.
				$this->template['vignettes'][$n]['id'] = $this->params['objets'][$n]['image_id'];
				$this->template['vignettes'][$n]['chemin'] = $this->params['objets'][$n]['image_chemin'];
				$this->template['vignettes'][$n]['nom'] = outils::html_specialchars(strip_tags($this->params['objets'][$n]['image_nom']));
				$this->template['vignettes'][$n]['date'] = $this->params['objets'][$n]['image_date'];
				$this->template['vignettes'][$n]['poids'] = $this->params['objets'][$n]['image_poids'];
				$this->template['vignettes'][$n]['hauteur'] = $this->params['objets'][$n]['image_hauteur']+$this->config['galerie_images_text_correction'];
				$this->template['vignettes'][$n]['largeur'] = $this->params['objets'][$n]['image_largeur'];
				$this->template['vignettes'][$n]['nb_hits'] = $this->params['objets'][$n]['image_hits'];
				$this->template['vignettes'][$n]['nb_commentaires'] = $this->params['objets'][$n]['image_commentaires'];
				$this->template['vignettes'][$n]['nb_votes'] = $this->params['objets'][$n]['image_votes'];
				$this->template['vignettes'][$n]['note'] = $this->params['objets'][$n]['image_note'];
			}
		}
	}



	/*
	 *	Vote utilisateur.
	*/
	function note() {
		if ($this->config['active_votes']) {

			// On vérifie par cookie si le visiteur n'a pas déjà voté l'image, ainsi que
			// par IP si le visiteur n'a pas déjà voté l'image dans les dernières 48 heures.
			$cookie_vote = $this->prefs->lire('userid');
			if (preg_match('`^[a-z0-9]{12}$`i', $cookie_vote)) {
				$cookie_requete = 'vote_cookie = "' . $cookie_vote . '" OR ';
			} else {
				$cookie_requete = '';
			}
			$time_limit = time() - (24 * 3600 * 2);
			$mysql_requete = 'SELECT vote_note,vote_cookie FROM ' . MYSQL_PREF . 'votes 
				WHERE image_id = "' . $this->params['objet_actuel']['image_id'] . '" 
				AND (' . $cookie_requete . '(vote_date > ' . $time_limit . ' AND vote_ip = "' . $_SERVER['REMOTE_ADDR'] . '"))';
			$note = $this->mysql->select($mysql_requete);

			// Si cookie inexistant et identifiant de vote présent dans la bdd, on rajoute l'identifiant de vote au cookie.
			if ($note != 'vide' &&
			    !preg_match('`^[a-z0-9]{12}$`i', $cookie_vote) && 
			     preg_match('`^[a-z0-9]{12}$`i', $note[0]['vote_cookie'])) {
				$this->prefs->ajouter('userid', $note[0]['vote_cookie']);
			}

			if ($note != 'vide') {
				$this->params['deja_note'] = 1;
				$this->template['infos']['deja_note'] = $note[0]['vote_note'];
			}
		} else {
			$this->template['infos']['no_votes'] = 1;
		}
	}



	/*
	 * 	On rejete le commentaire si l'auteur, l'IP ou un mot-clé présent dans le message est banni.
	*/
	function comment_aut() {

		$bans = unserialize($this->config['admin_comment_ban']);

		if (isset($_POST['auteur'])) {
			if (count($bans['auteurs']) > 0) {
				$p = '';
				foreach ($bans['auteurs'] as $k => $v) {
					$k = preg_quote($k);
					$k = str_replace('\*', '.*', $k);
					$p .= '|' . outils::regexp_accents($k);
				}
				$p = '`(^)(' . substr($p,1) . ')($)`i';
				$p = preg_replace('`\s+`', '\s+', $p);
				if (preg_match($p, $_POST['auteur'])) {
					$this->template['comment']['rejet'] = 'Vous n\'êtes pas autorisé à poster un commentaire.';
					return FALSE;
				}
			}
		}

		if (count($bans['IP']) > 0) {
			$p = '';
			foreach ($bans['IP'] as $k => $v) {
				$k = preg_quote($k);
				$k = str_replace('\*', '.*', $k);
				$p .= '|' . outils::regexp_accents($k);
			}
			$p = '`(\W|^)(' . substr($p,1) . ')(\W|$)`i';
			$p = preg_replace('`\s+`', '\s+', $p);
			if (preg_match($p, $_SERVER['REMOTE_ADDR'])) {
				$this->template['comment']['rejet'] = 'Vous n\'êtes pas autorisé à poster un commentaire.';
				return FALSE;
			}
		}

		if (count($bans['mots-cles']) > 0) {
			$p = '';
			foreach ($bans['mots-cles'] as $k => $v) {
				$k = preg_quote($k);
				$k = str_replace('\*', '[^^]*', $k);
				$p .= '|' . outils::regexp_accents($k);
			}
			$p = '`(\W|^)(' . substr($p,1) . ')(\W|$)`i';
			$p = preg_replace('`[^\S]+`', '[^\S]+', $p);
			if (preg_match($p, $_POST['message'])) {
				$this->template['comment']['rejet'] = 'Vous n\'êtes pas autorisé à poster ce message.';
				return FALSE;
			}
		}

		return TRUE;
	}



	/*
	 *	On prévient l'admin par courriel qu'un nouveau message a été posté.
	*/
	function commentMailAlert($auteur, $courriel, $siteweb, $msg, $IP, $img) {

		$msg = str_replace("\x5cn", "\n", $msg);
		$msg = str_replace("\x5cr", "\r", $msg);
		$msg = str_replace("\x5ct", "\t", $msg);
		$msg = stripslashes($msg);

		$from = 'iGalerie Alerte <igalerie@' . $_SERVER['SERVER_NAME'] . '>';

		// Message.
		$auteur = ($this->template['infos']['membres_connexion'] == false) 
				? 'Le membre \'' . str_replace('_', ' ', $this->template['membre_user'][0]['user_login']) . '\''
				: '\'' . $auteur . '\'';
		$message = $auteur . ' a écrit à propos de l\'image \'' . $img['image_nom'] . '\'' . "\n";
		$message .= 'http://' . $_SERVER['HTTP_HOST'] . GALERIE_URL . '?img=' . $_GET['img'] . "\n\n";
		$message .= $msg . "\n\n";
		$message .= '-- ' . "\n";
		$message .= 'Ce courriel a été envoyé automatiquement par iGalerie.';

		// Envoi du message.
		outils::send_mail($this->config['admin_mail'], $this->config['admin_comment_objet'], $message, $from);
	}



	/*
	 *	On vérifie les entrées de commentaires.
	*/
	function verif_comment($text, $long, $type = 0, $o = 1) {
		$text = trim($text);
		if ($o && empty($text)) {
			$result['rejet'] = 'vide';
		} elseif (!empty($text) && strlen($text) > $long) {
			$result['rejet'] = 'trop long';
		} elseif (!empty($text) && !empty($type)) {

			// L'adresse e-mail est-elle valide ?
			if ($type == 'mail' && !preg_match('`^' . outils::email_address() . '$`i', $text)) {
				$result['rejet'] = 'incorrecte';
			}

			// L'adresse du site Web est-elle valide ?
			if ($type == 'site' && !preg_match('`^' . outils::http_url() . '$`i', $text)) {
				$result['rejet'] = 'incorrecte';
			}
		}
		if (empty($result['rejet'])) {
			$result['text'] = outils::protege_mysql($text, $this->mysql->lien);
		}
		return $result;
	}



	/*
	 *	On ajoute les nouveaux commentaires.
	*/
	function new_comment() {
		if ((isset($_POST['auteur']) || isset($_POST['message']) || isset($_POST['courriel']) || isset($_POST['siteweb']))
		  && $this->config['active_commentaires'] && $this->template['infos']['add_comments']) {

			if (isset($_POST['auteur'])) $this->template['comment']['auteur'] = outils::html_specialchars($_POST['auteur']);
			if (isset($_POST['message'])) $this->template['comment']['message'] = outils::html_specialchars($_POST['message']);
			if (isset($_POST['courriel'])) $this->template['comment']['courriel'] = htmlspecialchars($_POST['courriel']);
			if (isset($_POST['siteweb'])) {
				$_POST['siteweb'] = (trim($_POST['siteweb']) == 'http://') ? '' : $_POST['siteweb'];
				$this->template['comment']['siteweb'] = htmlspecialchars($_POST['siteweb']);
			}

			$IP = $_SERVER['REMOTE_ADDR'];

			// Anti-flood.
			$antifloodtime = time() - $this->config['comment_antiflood'];
			$mysql_requete = 'SELECT commentaire_date FROM ' . MYSQL_PREF . 'commentaires 
				WHERE commentaire_ip = "' . $IP . '" AND 
					commentaire_date > ' . $antifloodtime . ' 
				ORDER BY commentaire_date DESC LIMIT 1';
			$ipdate = $this->mysql->select($mysql_requete, 5);
			if ($ipdate != 'vide') {
				$temps = (time() - $antifloodtime) - (time() - $ipdate);
				$this->template['comment']['rejet'] = 'Vous devez patienter encore ' . $temps . ' secondes avant de pouvoir poster un nouveau commentaire.';
				return;
			}

			// Anti-spam ?
			if (empty($_POST['molpac']) || !preg_match('`^[a-z0-9]{32}$`', $_POST['molpac'])) {
				return;
			}
			if (empty($_POST['preview'])) {
				$time = time();
				$time_md5_array = array();
				for ($i = -5; $i < 1; $i++) {
					$time_md5 = md5($time+$i);
					$time_key_md5 = md5($time_md5 . $this->config['galerie_key']);
					array_push($time_md5_array, $time_key_md5);
				}
				if (in_array($_POST['molpac'], $time_md5_array)) {
					$this->template['comment']['rejet'] = 'Prenez au moins le temps d\'admirer cette image avant de poster un commentaire !';
					return;
				}
			}

			// Si le commentaire est autorisé...
			if ($this->comment_aut()) {

				// ...on vérifie le commentaire.
				$message = $this->verif_comment($_POST['message'], 2000);
				if ($this->template['infos']['membres_connexion']) {
					$auteur = $this->verif_comment($_POST['auteur'], 50);
					$courriel = $this->verif_comment($_POST['courriel'], 320, 'mail', $this->config['comment_courriel']);
					$siteweb = $this->verif_comment($_POST['siteweb'], 350, 'site', $this->config['comment_siteweb']);
				} else {
					$auteur = array();
					$auteur['text'] = $this->template['membre_user'][0]['user_nom'];
					$courriel = array();
					$courriel['text'] = $this->template['membre_user'][0]['user_mail'];
					$siteweb = array();
					$siteweb['text'] = $this->template['membre_user'][0]['user_web'];
				}

				// Interdire les messages contenant des adresses Internet ?
				if (!isset($message['rejet']) && $this->config['comment_nourl']) {
					$regexp = '(?:' . outils::http_url('IP') . '|(?:(ht|f)tp://' 
							. outils::http_url('domaine') . outils::http_url('TLD') . '))';
					$o = $this->config['comment_maxurl'];
					if (preg_match('`(?:[^$]*?' . $regexp . '[^$]*?){' . $o . '}`i', $message['text'])) {
						$this->template['comment']['rejet'] = 'Les adresses Internet ne sont pas autorisées.';
						return;
					}
				}

				if (isset($auteur['rejet']) || isset($message['rejet']) || isset($courriel['rejet']) || isset($siteweb['rejet'])) {
					if (isset($auteur['rejet'])) {
						$rejet = 'Le nom de l\'auteur du commentaire est ' . $auteur['rejet'] . '.';
					} elseif (isset($message['rejet'])) {
						$rejet = 'Le message que vous avez envoyé est ' . $message['rejet'] . '.';
					} elseif (isset($courriel['rejet'])) {
						$rejet = 'L\'adresse du courriel que vous avez envoyé est ' . $courriel['rejet'] . '.';
					} elseif (isset($siteweb['rejet'])) {
						$rejet = 'L\'adresse du site Web que vous avez envoyé est ' . $siteweb['rejet'] . '.';
					}
					$this->template['comment']['rejet'] = $rejet;

				// Prévisualisation.
				} elseif (!empty($_POST['preview'])) {
					$this->template['comment']['preview'] = 1;

				} else {

					// Interdire à une même IP de poster plusieurs fois un même message pour une même image ?
					if ($this->config['comment_samemsg']) {
						$mysql_requete = 'SELECT commentaire_id FROM ' . MYSQL_PREF . 'commentaires
										   WHERE commentaire_ip = "' . $IP . '"
										     AND image_id = "' . $this->params['objet_actuel']['image_id'] . '"
										     AND commentaire_message = "' . $message['text'] . '"';
						if (is_array($this->mysql->select($mysql_requete))) {
							$this->template['comment']['rejet'] = 'Vous ne pouvez reposter le même message.';
							return;
						}
					}

					// Interdire à une même IP de poster plus de X messages pour une même image ?
					if ($this->config['comment_maxmsg']) {
						$mysql_requete = 'SELECT COUNT(image_id) FROM ' . MYSQL_PREF . 'commentaires
										   WHERE commentaire_ip = "' . $IP . '"
											 AND image_id = "' . $this->params['objet_actuel']['image_id'] . '"';
						$count = $this->mysql->select($mysql_requete, 5);
						if (!is_array($count) && preg_match('`^\d+$`', $count) && $count >= $this->config['comment_maxmsg_nb']) {
							$this->template['comment']['rejet'] = 'Vous avez posté trop de messages pour cette image.';
							return;
						}
					}

					// On enregistre les informations du posteur dans le cookie.
					$this->prefs->ajouter('co_nom', $auteur['text']);
					$this->prefs->ajouter('co_mail', $courriel['text']);
					$this->prefs->ajouter('co_site', $siteweb['text']);

					// Le commentaire doit-il être modéré ?
					$visible = ($this->config['admin_comment_moderer']) ? 0 : 1;

					// Doit-on alerter l'admin par courriel ?
					if ($this->config['admin_comment_alert']) {
						$this->commentMailAlert($auteur['text'], $courriel['text'], $siteweb['text'], $message['text'], $IP, $this->params['objet_actuel']);
					}

					// Utilisateur.
					if ($this->template['infos']['membres_connexion']) {
						$user_id = 0;
					} else {
						$user_id = $this->template['membre_user'][0]['user_id'];
						$auteur = array();
						$auteur['text'] = str_replace('_', ' ', $this->template['membre_user'][0]['user_login']);
					}

					// On ajoute le commentaire à la base de données.
					$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'commentaires (
						image_id,
						user_id,
						commentaire_date,
						commentaire_auteur,
						commentaire_mail,
						commentaire_web,
						commentaire_message,
						commentaire_ip,
						commentaire_visible) VALUES (
						"' . $this->params['objet_actuel']['image_id'] . '",
						"' . $user_id . '",
						"' . time(). '",
						"' . $auteur['text'] . '",
						"' . $courriel['text'] . '",
						"' . $siteweb['text'] . '",
						"' . $message['text'] . '",
						"' . $IP . '",
						"' . $visible . '")';
					$this->mysql->requete($mysql_requete);
	
					// On update le nombre de commentaires de l'image et des catégories parentes.
					if ($visible) {
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET
							image_commentaires = image_commentaires + 1
							WHERE image_id = "' . $this->params['objet_id'] . '"';
						$this->mysql->requete($mysql_requete);
						$this->template['image']['image_commentaires']++;
						for ($i = 0; $i < count($this->params['parents']); $i++) {
							$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET
								categorie_commentaires = categorie_commentaires + 1 
								WHERE categorie_id = "' . $this->params['parents'][$i]['categorie_id'] . '"';
							$this->mysql->requete($mysql_requete);
						}
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET
							categorie_commentaires = categorie_commentaires + 1 
							WHERE categorie_id = "1"';
						$this->mysql->requete($mysql_requete);
					}

					// On redirige vers la même page pour éviter les double-posts par refresh de la page.
					$this->prefs->ecrire();
					header('Location: ' . $_SERVER['REQUEST_URI'] . '&mod=' . $this->config['admin_comment_moderer']);
					exit;
				}
			}
		}
	}



	/*
	 *	On récupère les commentaires.
	*/
	function get_comments() {

		$this->template['infos']['comment_mod_a'] = $this->config['admin_comment_moderer'];

		if ($this->config['active_commentaires']) {

			$this->template['comment']['o_courriel'] = $this->config['comment_courriel'];
			$this->template['comment']['o_siteweb'] = $this->config['comment_siteweb'];

			// On récupère, s'il y a, les informations du posteur.
			// Sauf en mode "prévisuaiser".
			if (empty($_POST['preview'])) {
				$valeur_nom = $this->prefs->lire('co_nom');
				$valeur_mail = $this->prefs->lire('co_mail');
				$valeur_site = $this->prefs->lire('co_site');
				if ($valeur_nom !== FALSE) {
					$this->template['comment']['auteur'] = outils::html_specialchars(stripslashes($valeur_nom));
				}
				if ($valeur_mail !== FALSE) {
					$this->template['comment']['courriel'] = htmlspecialchars($valeur_mail);
				}
				if ($valeur_site !== FALSE) {
					$this->template['comment']['siteweb'] = htmlspecialchars($valeur_site);
					$this->template['comment']['siteweb'] = ($this->template['comment']['siteweb']) ? $this->template['comment']['siteweb'] : 'http://';
				}
			}

			// Récupération des commentaires dans la base de données.
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'commentaires.*,
									 ' . MYSQL_PREF . 'users.user_id,
									 ' . MYSQL_PREF . 'users.user_login,
									 ' . MYSQL_PREF . 'users.user_avatar
								FROM ' . MYSQL_PREF . 'commentaires LEFT JOIN ' . MYSQL_PREF . 'users USING (user_id)
							   WHERE ' . MYSQL_PREF . 'commentaires.image_id = "' . $this->params['objet_actuel']['image_id'] . '" 
								 AND ' . MYSQL_PREF . 'commentaires.commentaire_visible = "1" 
							ORDER BY ' . MYSQL_PREF . 'commentaires.commentaire_date';
			$comments = $this->mysql->select($mysql_requete);

			// Création du tableau des commentaires à afficher.
			$i = 0;
			if (is_array($comments)) {
				while ($i < count($comments)) {
					$this->template['commentaires'][$i]['id'] = outils::html_specialchars($comments[$i]['commentaire_id']);
					if ($comments[$i]['user_id'] == 0) {
						$this->template['commentaires'][$i]['auteur'] = outils::html_specialchars($comments[$i]['commentaire_auteur']);
						$this->template['commentaires'][$i]['courriel'] = htmlspecialchars($comments[$i]['commentaire_mail']);
						$this->template['commentaires'][$i]['siteweb'] = htmlspecialchars($comments[$i]['commentaire_web']);
					} else {
						if ($this->config['users_membres_active']) {
							$login = $comments[$i]['user_login'];
							$this->template['commentaires'][$i]['auteur'] = '<a href="' . outils::genLink('?profil=' . urlencode($login)) . '">' . str_replace('_', ' ', $login) . '</a>';
						} else {
							$this->template['commentaires'][$i]['auteur'] = str_replace('_', ' ', $comments[$i]['user_login']);
						}
						$this->template['commentaires'][$i]['courriel'] = '';
						$this->template['commentaires'][$i]['siteweb'] = '';
					}
					$this->template['commentaires'][$i]['date'] = $comments[$i]['commentaire_date'];
					$this->template['commentaires'][$i]['ip'] = $comments[$i]['commentaire_ip'];
					$this->template['commentaires'][$i]['avatar'] = $comments[$i]['user_avatar'];
					$this->template['commentaires'][$i]['login'] = $comments[$i]['user_login'];
					$this->template['commentaires'][$i]['user_id'] = $comments[$i]['user_id'];
					$this->template['commentaires'][$i]['message'] = outils::comment_format($comments[$i]['commentaire_message']);
					$i++;
				}
			}
			if (isset($this->template['comment']['preview'])) {
				$this->template['commentaires'][$i]['preview'] = 1;
				$this->template['commentaires'][$i]['id'] = 0;
				if ($this->template['infos']['membres_connexion']) {
					$this->template['commentaires'][$i]['auteur'] = $this->template['comment']['auteur'];
					$this->template['commentaires'][$i]['courriel'] = $this->template['comment']['courriel'];
					$this->template['commentaires'][$i]['siteweb'] = $this->template['comment']['siteweb'];

				} else {
					$login = $this->template['membre_user'][0]['user_login'];
					$this->template['commentaires'][$i]['auteur'] = '<a href="' . outils::genLink('?profil=' . urlencode($login)) . '">' . str_replace('_', ' ', $login) . '</a>';
					$this->template['commentaires'][$i]['courriel'] = '';
					$this->template['commentaires'][$i]['siteweb'] = '';
					$this->template['commentaires'][$i]['avatar'] = $this->template['membre_user'][0]['user_avatar'];
					$this->template['commentaires'][$i]['user_id'] = $this->template['membre_user'][0]['user_id'];
					$this->template['commentaires'][$i]['login'] = $login;
				}
				$this->template['commentaires'][$i]['date'] = time();
				$this->template['commentaires'][$i]['ip'] = $_SERVER['REMOTE_ADDR'];
				$this->template['commentaires'][$i]['message'] = outils::comment_format($_POST['message']);
			}

		// Commentaires désactivés.
		} else {
			$this->template['comment']['no_comment'] = 1;
		}
	}



	/*
	 *	On additionne 1 au nombre de visites de l'image et des catégories parentes.
	*/
	function hits() {

		// L'admin veut-il que l'on ne compte pas ses visites ?
		if ($this->config['admin_no_hits']) {
			if ($this->config['admin_no_hits_mode'] == 'cookie') {
				$valeur = $this->prefs->lire('anh');
				if ($valeur == md5($this->config['galerie_key'])) {
					return;
				}
			}
			if ($this->config['admin_no_hits_mode'] == 'ip') {
				if (preg_match('`(^|,)' . $_SERVER['REMOTE_ADDR'] . '($|,)`', $this->config['admin_no_hits_ip'])) {
					return;
				}
			}
		}

		$deja = 0;

		// On vérifie par POST, par referer et par cookie si l'image n'a pas déjà été visionnée précédemment.
		if (!empty($_POST)) {
			$deja = 1;
		}
		if ((isset($_SERVER['HTTP_REFERER']) 
			&& strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME'])
			&& strstr($_SERVER['HTTP_REFERER'], 'img=' . $this->params['objet_id']))) {
			$deja = 1;
		}
		$valeur = $this->prefs->lire('last_img');
		if ($valeur == $this->params['objet_id']) {
			$deja = 1;
		}

		if ($this->params['objet_actuel'] !== 'vide' && empty($deja)) {
			$this->prefs->ajouter('last_img', $this->params['objet_id']);
			$this->template['image']['image_hits']++;

			// On fait tourner le compteur pour l'image actuelle...
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET
				image_hits = image_hits + 1 
				WHERE image_id = "' . $this->params['objet_id'] . '"';
			$this->mysql->requete($mysql_requete);

			// ...et pour toutes les catégories parentes.
			$cat_where = 'categorie_id = "1"';
			for ($i = 0; $i < count($this->params['parents']); $i++) {
				$cat_where .= ' OR categorie_id = "' . $this->params['parents'][$i]['categorie_id'] . '"';
			}
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET
				categorie_hits = categorie_hits + 1 
				WHERE ' . $cat_where;
			$this->mysql->requete($mysql_requete);
		}
	}



	/*
	 *	Choix utilisateurs : taille de l'image.
	*/
	function perso_image() {
		$this->template['user']['image_taille'] = $this->config['user_image_ajust'];
		$this->template['display']['image_taille'] = ($this->config['galerie_images_resize'] == 1) ? 2 : 1;

		// Redimensionnement GD.
		if ($this->config['galerie_images_resize'] == 2) {

			$this->template['display']['image_taille'] = 2;

			// L'image sera-t-elle redimensionnée ?
			$this->template['infos']['img_resize'] = '';
			$img_max_size = preg_split('`x`i', IMG_RESIZE_GD, -1, PREG_SPLIT_NO_EMPTY);
			$img_l = $this->params['objet_actuel']['image_largeur'];
			$img_h = $this->params['objet_actuel']['image_hauteur'];
			$ratio_l = $img_l / $img_max_size[0];
			$ratio_h = $img_h / $img_max_size[1];

			if (!empty($img_max_size[0]) && 
				($img_l > $img_max_size[0]) && 
				($ratio_l >= $ratio_h)) {
				$this->template['infos']['img_resize'] = 'width="' . $img_max_size[0] . '" height="' . (round($img_h / $ratio_l)+$this->config['galerie_images_text_correction']) . '"';
			}
			if (!empty($img_max_size[1]) && 
				($img_h > $img_max_size[1]) && 
				($ratio_h >= $ratio_l)) {
				$this->template['infos']['img_resize'] = 'width="' . round($img_l / $ratio_h) . '" height="' . ($img_max_size[1]+$this->config['galerie_images_text_correction']) . '"';
			}

		} else {

			$img_max_size = preg_split('`x`i', $this->config['galerie_images_resize_max_html'], -1, PREG_SPLIT_NO_EMPTY);

			// Si l'utilisateur est autorisé à choisir le mode de redimensionnement de l'image...
			if ($this->config['user_perso'] && $this->config['user_image_ajust']) {

				// Type de dimensionnement de l'image (valeur 'it').
				// 1 : taille originale.
				// 2 : taille maximale.
				// 3 : ajustement automatique de la largeur.
				$valeur = $this->prefs->lire('it');
				if ($valeur !== FALSE) {
					$this->template['display']['image_taille'] = $valeur;
				}
				if (!empty($_GET['u']) && isset($_GET['it']) && preg_match('`^[123]$`', $_GET['it'])) {
					$this->prefs->ajouter('it', $_GET['it']);
					$this->template['display']['image_taille'] = $_GET['it'];
				}

				// Largeur maximale des images.
				$choice[0]['nom'] = 'il'; $choice[0]['defaut'] = $img_max_size[0];
				$choice[1]['nom'] = 'ih'; $choice[1]['defaut'] = $img_max_size[1];
				for ($i = 0; $i < count($choice); $i++) {
					if ($valeur = $this->prefs->lire($choice[$i]['nom'])) {
						$this->choix[$choice[$i]['nom']] = $valeur;
					} else {
						$this->choix[$choice[$i]['nom']] = $choice[$i]['defaut'];
					}
					if (isset($_GET[$choice[$i]['nom']])) {
						if (preg_match('`^[1-9][0-9]{1,4}$`', $_GET[$choice[$i]['nom']])) {
							$this->prefs->ajouter($choice[$i]['nom'], $_GET[$choice[$i]['nom']]);
							$this->choix[$choice[$i]['nom']] = $_GET[$choice[$i]['nom']];
						} elseif ($this->template['display']['image_taille'] == 2) {
							$this->template['display']['image_taille'] = 1;
						}
					}
				}

			// ...sinon on prend les valeurs de redimmensionnent max. par défaut.
			} else {
				$this->choix['il'] = $img_max_size[0];
				$this->choix['ih'] = $img_max_size[1];
			}

			$this->template['user']['img_largeur_max'] = $this->choix['il'];
			$this->template['user']['img_hauteur_max'] = $this->choix['ih'];

			// Si redimensionnement par taille maximale,
			// on calcule le redimensionnement.
			if ($this->template['display']['image_taille'] == 2) {
				settype($this->template['infos']['img_resize'], 'string');
				$img_l = $this->params['objet_actuel']['image_largeur'];
				$img_h = $this->params['objet_actuel']['image_hauteur'] + $this->config['galerie_images_text_correction'];
				if (empty($this->choix['il'])) { $this->choix['il'] = $img_l; }
				if (empty($this->choix['ih'])) { $this->choix['ih'] = $img_h; }
				$ratio_h = $img_h / $this->choix['ih'];
				$ratio_l = $img_l / $this->choix['il'];
				if (!empty($this->choix['il']) && 
					($img_l > $this->choix['il']) && 
					($ratio_l >= $ratio_h)) {
					$this->template['infos']['img_resize'] = 'width="' . $this->choix['il'] . '" height="' . round($img_h / $ratio_l) . '"';
				}
				if (!empty($this->choix['ih']) && 
					($img_h > $this->choix['ih']) && 
					($ratio_h >= $ratio_l)) {
					$this->template['infos']['img_resize'] = 'width="' . round($img_l / $ratio_h) . '" height="' . $this->choix['ih'] . '"';
				}
			}
		}
	}



	/*
	 *	On récupère toutes les images pour les pages spéciales :
	 *	images, hits, votes, recentes et commentaires.
	*/
	function get_speciales($img = 0) {
		if (isset($this->params['objet_actuel']['categorie_nom'])) {
			$this->template['infos']['hvc']['nb_images'] = $this->params['objet_actuel']['categorie_images'];
			$this->template['infos']['hvc']['nom'] = $this->params['objet_actuel']['categorie_nom'];
			$this->template['infos']['hvc']['type'] = ($this->params['objet_actuel']['categorie_derniere_modif'] > 0) ? 'alb' : 'cat';
			$this->template['infos']['hvc']['objet'] = ($this->params['objet_actuel']['categorie_derniere_modif'] > 0) ? 'l\'album' : 'la catégorie';
		}
		$chemin = '';
		if (isset($this->params['objet_actuel']['categorie_chemin'])) {
			$chemin = $this->params['objet_actuel']['categorie_chemin'];
			$chemin = ($chemin == '.') ? '' : $chemin;
		}
		if (isset($this->params['objet_actuel']['image_chemin']) 
		&& substr($this->params['objet_actuel']['image_chemin'], 0, strlen($chemin)) != $chemin) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}
		$date_creation_lenght = ($this->params['v_ordre'] == 'date_creation') ? 'LENGTH(image_date_creation) ' . $this->params['v_sens'] . ',' : '';
		$order = 'image_';
		$ordre_criteres = 'image_' . $this->params['v_ordre'] . ' ' . $this->params['v_sens'];
		if ($this->params['v_ordre'] == 'votes') {
			$ordre_criteres .= ', image_note ' . $this->params['v_sens'];
		} elseif ($this->params['v_ordre'] == 'note') {
			$ordre_criteres .= ', image_votes ' . $this->params['v_sens'];
		}
		if (substr($this->params['objet_type'], 0, 4) == 'date') {
			unset($this->template['infos']['hvc']);
			if (isset($this->params['objet_actuel']['categorie_nom'])) {
				$this->template['historique']['objet_id'] = $this->params['objet_actuel']['categorie_id'];
				$this->template['historique']['objet_nom'] = $this->params['objet_actuel']['categorie_nom'];
			}
			if (isset($_GET[$this->params['objet_type']])) {
				$date = $_GET[$this->params['objet_type']];
				if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $date, $m)) {
					$date_debut = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
					$date_fin = mktime(23, 59, 59, $m[2], $m[1], $m[3]);
				} else {
					$date = getdate(time());
					$date_debut = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
					$date_fin = mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);
				}
			}
			$type = ($this->params['objet_type'] == 'date_creation') ? 'date_creation' : 'date';
			$mysql_params = 'WHERE image_chemin LIKE "' . $chemin . '%" 
				AND image_' . $type . ' >= ' . $date_debut . ' 
				AND image_' . $type . ' <= ' . $date_fin . ' 
				AND image_visible = "1"';
			$order .= $type . ' DESC, ' . $date_creation_lenght . $ordre_criteres;
		} elseif ($this->params['objet_type'] == 'recentes') {
			$time_limit = time() - ($this->choix['recent'] * 24 * 3600);
			$order .= 'date DESC';
			$mysql_params = 'WHERE image_chemin LIKE "' . $chemin . '%" 
				AND image_date >= ' . $time_limit . ' AND image_visible = "1"';
			$this->template['display']['s_recentes'] = ' is_hvcr';
			$order .= ',' . $date_creation_lenght . $ordre_criteres;
		} else {
			$type = ($this->params['objet_type'] == 'images') ? 'date' : $this->params['objet_type'];
			switch ($type) {
				case 'hits' : $this->template['display']['s_hits'] = ' is_hvcr'; break;
				case 'commentaires' : $this->template['display']['s_comments'] = ' is_hvcr'; break;
				case 'votes' : $this->template['display']['s_votes'] = ' is_hvcr'; break;
			}
			$mysql_params = 'WHERE image_chemin LIKE "' . $chemin . '%" 
				AND image_' . $type . ' > 0 AND image_visible = "1"';
			$order .= ($type == 'votes') ? 'note DESC,image_votes' : $type;
			$order .= ' DESC,' . $date_creation_lenght . $ordre_criteres;
			if ($this->params['objet_type'] == 'images') {
				$order = $date_creation_lenght . $ordre_criteres;
			}
		}
		$mysql_params .= $this->images_protect();

		// On récupère toutes les images dans la base de données
		// correspondant aux critères de recherche.
		$select = ($img) ? 'image_id,image_nom' : '*';
		$limit = ($img) ? '' : ' LIMIT ' . $this->params['startnum'] . ',' . $this->params['limit_vignettes'];
		$mysql_requete = 'SELECT ' . $select . ' FROM ' . MYSQL_PREF . 'images ' 
			. $mysql_params 
			. ' ORDER BY ' . $order . ', image_id ' . $this->params['v_sens']
			. $limit;
		$this->params['objets'] = $this->mysql->select($mysql_requete);
		if (empty($this->params['objets'])) {
			die ('[' . __LINE__ . '] La base de données est vide.<br />' . mysql_error());
		}

		
		// On compte le nombre de résultats.
		$mysql_requete = 'SELECT COUNT(*) FROM ' . MYSQL_PREF . 'images ' . $mysql_params;
		$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);
		$this->template['infos']['nb_objets'] = $this->params['nb_objets'];
	}



	/*
	  *	Récupération des images correspondant à un tag.
	*/
	function get_images_tag() {

		if (!$this->config['active_tags']) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// Chemin.
		$chemin = '';
		if (isset($this->params['objet_actuel']['categorie_chemin'])) {
			$chemin = $this->params['objet_actuel']['categorie_chemin'];
			$chemin = ($chemin == '.') ? '' : $chemin;
		}

		// WHERE...
		$tag = htmlentities($_GET['tag']);
		$mysql_params = 'WHERE ' . MYSQL_PREF . 'tags.tag_id = "' . outils::protege_mysql($tag, $this->mysql->lien) . '"
						   AND ' . MYSQL_PREF . 'images.image_id = ' . MYSQL_PREF . 'tags.image_id
						   AND ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $chemin . '%"
						   AND ' . MYSQL_PREF . 'images.image_visible = "1"';
		$mysql_params .= $this->images_protect();

		// ORDER BY...
		$date_creation_lenght = ($this->params['v_ordre'] == 'date_creation') ? 'LENGTH(' . MYSQL_PREF . 'images.image_date_creation) ' . $this->params['v_sens'] . ', ' : '';
		$ordre_criteres = MYSQL_PREF . 'images.image_' . $this->params['v_ordre'] . ' ' . $this->params['v_sens'];
		if ($this->params['v_ordre'] == 'votes') {
			$ordre_criteres .= ', ' . MYSQL_PREF . 'images.image_note ' . $this->params['v_sens'];
		} elseif ($this->params['v_ordre'] == 'note') {
			$ordre_criteres .= ', ' . MYSQL_PREF . 'images.image_votes ' . $this->params['v_sens'];
		}
		$order = $date_creation_lenght . $ordre_criteres;

		// Récupération des images.
		$mysql_requete = 'SELECT DISTINCT ' . MYSQL_PREF . 'images.image_id,
								 ' . MYSQL_PREF . 'images.image_nom
						    FROM ' . MYSQL_PREF . 'images,
								 ' . MYSQL_PREF . 'tags '
								   . $mysql_params .
					  ' ORDER BY ' . $order . ',
					             ' . MYSQL_PREF . 'images.image_id ' . $this->params['v_sens'];
		$this->params['objets'] = $this->mysql->select($mysql_requete);
		if (!is_array($this->params['objets'])) {
			header('Location: ' . outils::genLink('?cat=1'));
			exit;
		}

		// On récupère le nombre d'images correspondant aux critères du SELECT.
		$mysql_requete = 'SELECT COUNT(*) 
							FROM ' . MYSQL_PREF . 'images,
								 ' . MYSQL_PREF . 'tags '
								   . $mysql_params;
		$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);
		$this->template['infos']['nb_objets'] = $this->params['nb_objets'];
	}



	/*
	 *	Tient compte de la protection des objets par mot de passe
	 *	lors d'une requête de base de données.
	*/
	function images_protect($type = 'image') {
		if (isset($this->template['membre_user'][0]['groupe_album_pass_mode'])) {
			$type = (strstr($type, 'image')) ? 'images.image_pass' : 'categories.categorie_pass';
			return $this->users_pass($type);
		}
		$passwords = $this->passwords->valeur;
		$regexp = '';
		if (is_array($passwords)) {
			foreach ($passwords as $p) {
				$pass = outils::decrypte($p, $this->config['galerie_key']);
				if (preg_match('`^\d+:\w+$`', $pass)) {
					$regexp .= $pass . '|';
				}
			}
			if ($regexp) {
				$regexp = ' OR ' . $type . '_pass REGEXP "^' . preg_replace('`\|$`', '', $regexp) . '$"';
			}
		}
		return ' AND (' . $type . '_pass IS NULL' . $regexp . ') ';
	}



	/*
	 *	Moteur de recherche.
	*/
	function recherche($img = 0) {
		if (!$img) {
			$this->params['objet_actuel'] = 'search';
			$this->template['infos']['tags'] = 0;
		}
		$this->template['infos']['objet'] = outils::html_specialchars($_GET['search']);
		$this->params['nb_objets'] = 0;

		// On effectue la recherche.
		$text_correction = $this->config['galerie_images_text_correction'];
		$images_order = $this->mysql_order();
		$protect_categories = $this->images_protect('categorie');
		$protect_images = $this->images_protect();
		$search_cat = ($img) ? 'img' : 'cat';
		if (!$recherche = recherche::search($this->mysql, $this->config['active_tags'], $this->config['active_exif'], $text_correction, $images_order, $protect_images, $search_cat, $protect_categories)) {
			header('Location: ' . outils::genLink('?cat=1', '', '', 0, '&'));
			exit;
		}

		// Recherche dans les catégories.
		if (isset($recherche['categories'])) {
			$categories = $recherche['categories'];
			if ($categories !== 'vide') {
				for ($i = 0; $i < count($categories); $i++) {

					// Tableau des catégories trouvées.
					$type = ($categories[$i]['categorie_derniere_modif'] > 0) ? 'alb' : 'cat';
					$this->template['cat_result'][$type][$categories[$i]['categorie_id']] = $categories[$i]['categorie_nom'];
				}
			}
		}

		// On recherche dans la table des images.
		$this->params['limit_vignettes'] = $this->choix['vn'] * $this->choix['vl'];
		$limit = ($img) ? '' : ' LIMIT ' . $this->params['startnum'] . ',' . $this->params['limit_vignettes'];
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_id,
								 ' . MYSQL_PREF . 'images.image_chemin,
								 ' . MYSQL_PREF . 'images.image_nom,
								 ' . MYSQL_PREF . 'images.image_date,
								 ' . MYSQL_PREF . 'images.image_poids,
								 ' . MYSQL_PREF . 'images.image_hauteur,
								 ' . MYSQL_PREF . 'images.image_largeur,
								 ' . MYSQL_PREF . 'images.image_hits,
								 ' . MYSQL_PREF . 'images.image_commentaires,
								 ' . MYSQL_PREF . 'images.image_votes,
								 ' . MYSQL_PREF . 'images.image_note
							FROM ' . MYSQL_PREF . 'images '
								   . $recherche['images']
								   . $limit;
		$image_result = $this->mysql->select($mysql_requete);

		// Y a-t-il des résultats dans la table des images ?
		if (!$image_result || $image_result == 'vide') {
			$this->params['objets'] = 'vide';
		} else {
			$this->params['objets'] = $image_result;

			// Si oui, on compte le nombre de résultats trouvés.
			$mysql_requete = 'SELECT COUNT(*) FROM ' . MYSQL_PREF . 'images' . $recherche['images'];
			$this->params['nb_objets'] = $this->mysql->select($mysql_requete, 5);
		}

		$this->template['search'] = 1;
	}



	/*
	 *	On récupère toute la hiérarchie de la galerie
	 *	et on en fait une liste HTML directement affichable.
	*/
	function plan($parent = '') {
		$mysql_requete = 'SELECT categorie_id,
								 categorie_nom,
								 categorie_chemin,
								 categorie_images,
								 categorie_dernier_ajout,
								 categorie_derniere_modif,
								 categorie_pass 
						    FROM ' . MYSQL_PREF . 'categories 
						   WHERE categorie_visible = "1"
						     AND categorie_chemin REGEXP "^' . $parent . '[^/]+/$" '
							   . $this->users_pass('categories.categorie_pass') . '
						ORDER BY categorie_nom ASC';
		$cats = $this->mysql->select($mysql_requete);
		$id_premier = ($parent == '') ? ' id="plan_liste"' : '';
		$time_limit = time() - ($this->choix['recent'] * 24 * 3600);
		if ($cats && $cats !== 'vide') {
			$this->template['plan'] .= '<ul' . $id_premier . '>';
			$passwords = $this->passwords->valeur;
			$regexp = '';
			if (is_array($passwords)) {
				foreach ($passwords as $v) {
					$regexp .= '|' . preg_quote(outils::decrypte($v, $this->config['galerie_key']));
				}
			}
			$regexp = str_replace('`', '\`', $regexp);
			for ($i = 0; $i < count($cats); $i++) {
				$s = ($cats[$i]['categorie_images'] > 1) ? 's' : '';
				if ($this->config['users_membres_active']) {
					$pass = 1;
				} else {
					$pass = (preg_match('`^(?:' . $regexp . ')$`', $cats[$i]['categorie_pass'])) ? 1 : 0;
				}
				$password = '';
				if (empty($cats[$i]['categorie_derniere_modif'])) {
					if (!$this->config['users_membres_active']) {
						$password = ($pass) ? '' : ' plan_cat_pass';
					}
					$recentes = ($this->choix['recent'] && ($cats[$i]['categorie_dernier_ajout'] > $time_limit)) ? ' cat_recentes' : '';
					$this->params['nb_cat']++;
					$this->template['plan'] .= '<li class="plan_cat' . $recentes . $password . '">';
					$l = outils::genLink('?cat=' . $cats[$i]['categorie_id'], '', $cats[$i]['categorie_nom']);
					$this->template['plan'] .= '<a href="' . $l . '">' . strip_tags($cats[$i]['categorie_nom']) . '</a> <span class="plan_nb_images">[' . $cats[$i]['categorie_images'] . ' image' . $s . ']</span>';
				} else {
					if (!$this->config['users_membres_active']) {
						$password = ($pass) ? '' : ' plan_alb_pass';
					}
					$recentes = ($this->choix['recent'] && ($cats[$i]['categorie_dernier_ajout'] > $time_limit)) ? ' alb_recentes' : '';
					$this->params['nb_alb']++;
					$this->template['plan'] .= '<li class="plan_alb' . $recentes . $password . '">';
					$l = outils::genLink('?alb=' . $cats[$i]['categorie_id'], '', $cats[$i]['categorie_nom']);
					$this->template['plan'] .= '<a href="' . $l . '">' . strip_tags($cats[$i]['categorie_nom']) . '</a> <span class="plan_nb_images">[' . $cats[$i]['categorie_images'] . ' image' . $s . ']</span>';
				}
				if (empty($cats[$i]['categorie_derniere_modif']) && $pass) {
					$this->plan($cats[$i]['categorie_chemin']);
				}
				$this->template['plan'] .= '</li>';
			}
			$this->template['plan'] .= '</ul>';
		}
	}
}





/*
 * ========== class.template
*/
class template {
	
	/*  Données brutes issues du "moteur" */
	var $data;

	/* Préférences d'affichage utilisateur */
	var $display;

	/* Variable interne temporaire utilisée par certaines boucles */
	var $interne;



	/*
	 *	Constructeur.
	*/
	function template($data) {
		$this->data = $data;
	}



	function getGaleriePath() {
		echo GALERIE_PATH;
	}
	function getGalerieFile() {
		echo GALERIE_FILE;
	}
	function getCSS() {
		if (isset($this->data['user']['styles'])) {
			for ($i = 0; $i < count($this->data['user']['styles']); $i++) {
				$alternate = (basename($this->data['infos']['style']) == $this->data['user']['styles'][$i] . '.css') ? '' : 'alternate ';
				$st = dirname(dirname($this->data['infos']['style'])) . '/' . $this->data['user']['styles'][$i] . '/' . $this->data['user']['styles'][$i] . '.css';
				echo '<link rel="' . $alternate . 'stylesheet" type="text/css" media="screen" title="' . str_replace('_', ' ', htmlentities($this->data['user']['styles'][$i])) . '" href="' . $st . '" />' . "\n";
			}
		} else {
			$nom = basename($this->data['infos']['style']);
			$nom = substr($nom, 0, -4);
			$nom = str_replace('_', ' ', htmlentities($nom));
			echo '<link rel="stylesheet" type="text/css" media="screen" title="' . $nom . '" href="' . $this->data['infos']['style'] . '" />' . "\n";
		}
		if (!empty($this->data['add_style'])) {
			echo "\n";
			echo '<style type="text/css">' . "\n";
			echo $this->data['add_style'] . "\n";
			echo '</style>' . "\n";
		}
	}
	function getFormAction($o = 0) {

		if (GALERIE_URL_TYPE == 'url_rewrite' && !$o &&
		   !strstr($_SERVER['REQUEST_URI'], '.php?')) {
			$qs = preg_replace('`(?:^|[\&\?])(..|u|mod|deconnect|addfav)=`', '/!!//', $_SERVER['QUERY_STRING']);
			$qs = preg_replace('`/!!//.*$`', '', $qs);
			echo GALERIE_PATH . htmlspecialchars($qs);
		} else {
			echo htmlspecialchars($_SERVER['REQUEST_URI']);
		}
	}
	function getSearchAction() {
		if (basename(GALERIE_URL) == 'index.php') {
			echo GALERIE_PATH . '/';
		} else {
			echo GALERIE_URL;
		}
	}
	function getGalerieAccueil() {
		echo outils::genLink('?cat=1');
	}



	/*
	 *	Informations générales.
	*/
	function getInfo($i, $s = '%s', $o = ' - ') {

		// Balise <title>.
		if ($i == 'title') {
			$title = '';
			if (!empty($this->data['infos']['nom'])) {
				$title .= $this->data['infos']['nom'];
			}
			switch ($this->data['infos']['type']) {
				case 'membres' :
				case 'profil' :
					$title = $this->data['infos']['title'];
					break;
				case 'date_ajout' :
				case 'date_creation' :
					$mode = ($this->data['infos']['type'] == 'date_creation') ? 'créée' : 'ajoutée';
					$date_get = $_GET[$this->data['infos']['type']];
					if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $date_get, $m)) {
						$date = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
					} else {
						$date = getdate(time());
						$date = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
					}
					$date = strftime($this->data['infos']['im_date_format'], $date);
					$pl = ($this->data['infos']['nb_objets'] > 1) ? 's' : '';
					$title .= 'image' . $pl . ' ' . $mode . $pl . ' le ' . $date;
					break;
				case 'tag' :
					$title .= $_GET['tag'];
					break;
				case 'comments' :
					$objet = ($this->data['infos']['objet'] == 1) ? 'la galerie' : ' ' . $this->data['infos_objet']['categorie_nom'];
					$title .= 'commentaires de ' . $objet;
					break;
				case 'img' :
					$title .= $this->data['image']['image_nom'];
					break;
				case 'search' :
					$title .= 'recherche \'' . html_entity_decode($this->data['infos']['objet']) . '\'';
					break;
				case 'section' :
					switch ($this->data['infos']['objet']) {
						case 'pass' :
							$title .= 'mot de passe requis';
							break;
						case 'tags':
							$title .= 'nuage de tags';
							break;
						case 'recherche':
							$title .= 'recherche avancée';
							break;
						default :
							$title .= $this->data['infos']['objet'];
					}
					break;

				case 'images' : $hvcr = 'images';
				case 'commentaires' : $hvcr = (isset($hvcr)) ? $hvcr : 'images les plus commentées';
				case 'votes' : $hvcr = (isset($hvcr)) ? $hvcr : 'images les mieux notées';
				case 'hits' : $hvcr = (isset($hvcr)) ? $hvcr : 'images les plus visitées';
				case 'recentes' : $hvcr = (isset($hvcr)) ? $hvcr : 'images les plus récentes';
					$objet = ($this->data['infos']['objet'] == 1) ? ' de la galerie' : ' de ' .  ' ' . $this->data['infos']['hvc']['nom'];
					$title .= $hvcr . $objet;
			}

			if ($this->data['infos']['h1']) {
				$t = $this->data['infos']['h1'];
				if (preg_match('`^<img[^>]+alt="([^">]+)"[^>]+>$`', $t, $m)) {
					$t = $m[1];
				}
				$o = ($title !== '') ? $o : '';
				$title .= $o . $t;
			}

			$title = str_replace('%sep', $o, strip_tags($title));
			$title = htmlentities($title);
			printf($s, $title);

		} elseif ($i == 'style_dir') {
			echo dirname($this->data['infos']['style']);
		} elseif ($i == 'h1') {
			$lien = '<a accesskey="1" href="' . outils::genLink('?cat=1') . '">' . str_replace('&', '&amp;', $this->data['infos'][$i]) . '</a>';
			printf($s, $lien);
		} elseif ($i == 'startnum') {
			if (GALERIE_URL_TYPE == 'normal' || GALERIE_URL_TYPE == 'query_string') {
				printf($s, $this->data['infos'][$i]);
			}
		} elseif ($this->data['infos'][$i]) {
			printf($s, $this->data['infos'][$i]);
		}
	}

	function getLink($l = '', $s = '%s') {
		switch ($l) {
			case 'contact' :
				if ((!empty($this->data['contact']['text'])
				  || !empty($this->data['contact']['active']))
				  && !empty($this->data['contact']['admin_mail'])) {
					$lien = outils::genLink('?section=contact');
					printf($s, $lien);
				}
				break;
			case 'plan' : 
				printf($s, outils::genLink('?section=plan')); break;
			case 'login' :
			case 'inscription' :
			case 'modif_profil' :
			case 'oubli' :
			case 'upload' :
				printf($s, outils::genLink('?membres=' . $l)); break;
			default : printf($s, outils::genLink('?cat=1'));
		}
	}
	
	

	/*
	  *	Partie des liens.
	*/
	function getLiens() {
		if (!empty($this->data['galerie_liens'])) {
			$liens = @unserialize($this->data['galerie_liens']);
			if (is_array($liens)) {
				$liste = '<ul>';
				for ($i = 0; $i < count($liens); $i++) {
					if (preg_match('`^([^:]+):(.+)$`', $liens[$i], $m)) {
						$liste .= '<li><a href="' . strip_tags($m[2]) . '">' . htmlspecialchars($m[1]) . '</a></li>';
					}
				}
				$liste .= '</ul>';
				if (strlen($liste) > 10) {
					echo $liste;
				}
			}
		}
	}



	/*
	 *	Paramètres d'URL pour les formulaires de navigation et de personnalisation,
	 *	et pour les listes de pages des barres de navigation.
	*/
	function getUrlParameters($type) {

		if (GALERIE_URL_TYPE != 'normal' && $type == 'page') {
			$bn = $this->getBarreNav('', 0);
			$bn = preg_replace('`/$`', '', $bn);
			if (GALERIE_URL_TYPE == 'query_string') {
				echo preg_replace('`index\.php$`', 'index.php?', $bn);
			} else {
				echo $bn;
			}
			return;
		}

		if (GALERIE_URL_TYPE != 'normal' && GALERIE_URL_TYPE != 'query_string') {
			return;
		}

		if ($this->data['infos']['type'] == 'cat' &&
			$this->data['infos']['objet'] == 1) {
			$_GET['cat'] = 1;
		}
		if ($type == 'ipost') {
			$url = outils::genLink('?img=' . $this->data['infos']['objet'], $this->data['image']['image_nom']);
			echo str_replace('/?', '/index.php?', $url);
		} else {
			$params[] = 'img';
		}
		if (isset($_GET['section'])) {
			$params[] = 'section';
			$params[] = 'type';
		}
		$params[] = 'sadv';
		$params[] = 'search';
		$params[] = 'images';
		$params[] = 'recentes';
		$params[] = 'hits';
		$params[] = 'commentaires';
		$params[] = 'comments';
		$params[] = 'votes';
		$params[] = 'cat';
		$params[] = 'alb';
		$params[] = 'date_creation';
		$params[] = 'date_ajout';
		$params[] = 'tag';
		$params[] = 'membres';
		$params[] = 'profil';
		$params[] = 'mcom';
		$params[] = 'mimg';
		$params[] = 'mfav';

		for ($i = 0, $n = 0; $i < count($params); $i++) {
			if (isset($_GET[$params[$i]]) && $_GET[$params[$i]] != '') {
				if ($type == 'nav' || $type == 'page' || $type == 'ipost') {
					$amp = ($type == 'page' && $n == 0) ? '?' : '&amp;';
					echo $amp . $params[$i] . '=' . urlencode($_GET[$params[$i]]);
					$n++;
				} else {
					echo '<input type="hidden" name="' . $params[$i] . '" value="' . htmlspecialchars($_GET[$params[$i]]) . '" />';
				}
			}
		}
	}

	function getObjetType() {

		$objet = 'la galerie';

		if (isset($_GET['cat']) && $_GET['cat'] > 1) {
			$objet = 'cette catégorie';
		} elseif (isset($_GET['alb'])) {
			$objet = 'cet album';
		} elseif (isset($_GET['img'])) {
			if (isset($this->data['infos']['special_cat'])) {
				if ($this->data['infos']['special_cat'][0]['categorie_derniere_modif']) {
					$objet = 'cet album';
				} elseif (!$this->data['infos']['special_cat'][0]['categorie_derniere_modif'] &&
						   $this->data['infos']['special_cat'][0]['categorie_id'] > 1) {
					$objet = 'cette catégorie';
				}
			} else {
				$objet = 'cet album';
			}
		} elseif (isset($this->data['infos']['hvc'])) {
			if ($this->data['infos']['hvc']['type'] == 'alb') {
				$objet = 'cet album';
			} elseif ($this->data['infos']['hvc']['type'] == 'cat' && $this->data['infos']['objet'] > 1) {
				$objet = 'cette catégorie';
			}
		} elseif (isset($this->data['infos_objet']['categorie_derniere_modif'])) {
			if ($this->data['infos_objet']['categorie_derniere_modif']) {
				$objet = 'cet album';
			} elseif (!$this->data['infos_objet']['categorie_derniere_modif'] && $this->data['infos_objet']['categorie_id'] > 1) {
				$objet = 'cette catégorie';
			}
		}

		return $objet;
	}


	/*
	 * 
	 * ======================================== AFFICHAGE D'ELEMENTS ;
	 *
	*/
	function display($type) {
		switch ($type) {

			case 'add_votes' :
			case 'add_comments' :
				if ($this->data['infos'][$type]) { return TRUE; } break;
			case 'inscription_form' : if (empty($this->data['infos']['inscription_ok'])) { return TRUE; } break;
			case 'membres' : if ($this->data['infos']['membres_active']) { return TRUE; } break;
			case 'membres_connexion' : if ($this->data['infos']['membres_connexion']) { return TRUE; } break;
			case 'modif_profil_newsletter' : if (!empty($this->data['infos']['user_newsletter'])) { return TRUE; } break;
			case 'membres_avatar' : if ($this->data['infos']['membres_avatar']) { return TRUE; } break;
			case 'membres_noadmin' : if ($this->data['infos']['membres_noadmin']) { return TRUE; } break;
			case 'membres_login' : if (!empty($this->data['infos']['membres_login'])) { return TRUE; } break;
			case 'oubli_form' : if ($this->data['infos']['oubli_form']) { return TRUE; } break;
			case 'tags_section' : if ($this->data['infos']['tags']) { return TRUE; } break;
			case 'liens_section' : if ($this->data['infos']['liens']) { return TRUE; } break;
			case 'hasard_section' : if (isset($this->data['image_hasard'])) { return TRUE; } break;
			case 'perso_section' : if ($this->data['user']['perso']) { return TRUE; } break;
			case 'perso_thumbs' : if ($this->data['user']['vignettes']) { return TRUE; } break;
			case 'perso_infos' : if ($this->data['user']['montrer']) { return TRUE; } break;
			case 'perso_sort' : if (!empty($this->data['user']['ordre'])) { return TRUE; } break;
			case 'perso_style' :
				if (!empty($this->data['user']['style'])
				 && isset($this->data['user']['styles'])
				 && count($this->data['user']['styles']) > 1) { return TRUE; } break;
			case 'image_taille' : if ($this->data['user']['image_taille']) { return TRUE; }; break;
			case 'stats_section' : switch ($this->data['infos']['type']) { case 'alb' : case 'cat' : case 'img' : return TRUE; } break;
			case 'categories_voisines' : if (isset($this->data['nav']['voisines']) && count($this->data['nav']['voisines']) > 1) { return TRUE; } break;
			case 'search_result' : if ($this->data['infos']['type'] == 'search' && empty($_GET['startnum'])) { return TRUE; } break;

			// Partie centrale.
			case 'barre_nav_com' : if (isset($this->data['nav']['pages'][2])) { return TRUE; } break;
			case 'barre_nav_img' : if (count($this->data['nav']['voisines']) > 1) { return TRUE; } break;
			case 'barre_nav' : if (isset($this->data['vignettes']) && isset($this->data['nav']['pages'][2])) { return TRUE; } break;
			case 'votes' : if (empty($this->data['infos']['no_votes'])) { return TRUE; } break;
			case 'note' : if (empty($this->data['infos']['deja_note'])) { return TRUE; } break;
			case 'comments' : if (empty($this->data['comment']['no_comment'])) { return TRUE; } break;
			case 'exif' : if ($this->data['infos']['active_exif']) { return TRUE; } break;
			case 'iptc' : if ($this->data['infos']['active_iptc']) { return TRUE; } break;
			case 'rss' : if ($this->data['infos']['rss']) { return TRUE; } break;
			case 'rss_objet' : if ($this->data['infos']['rss'] && isset($this->data['infos']['rss_objet'])) { return TRUE; } break;
			case 'commentaires' : if ($this->data['infos']['active_commentaires']) { return TRUE; } break;

			// Barre de navigation pour diaporama.
			case 'barre_nav_diapo' :
				if ($this->data['infos']['diaporama'] && $this->data['infos']['type'] != 'cat') {
					if (isset($this->data['vignettes'])) {
						return TRUE;
					}
				} else {
					return $this->display('barre_nav');
				}
				break;

			// Pages membres.
			case 'inscription' :
			case 'oubli' :
			case 'profil' :
			case 'modif_profil' :
			case 'modif_pass' :
			case 'modif_avatar' :
			case 'upload' :
			case 'create' :
			case 'liste' :
				if ($this->data['infos']['section_membres'] == $type) { return TRUE; }
				break;

			// Page section.
			case 'recherche' :
			case 'plan' :
			case 'contact' :
			case 'pass' :
			case 'historique' :
			case 'tags' :
				if ($this->data['infos']['objet'] == $type) { return TRUE; }
				break;
			case 'contact_antispam' :
			case 'contact_form' :
				if (isset($this->data['contact'][$type])) { return TRUE; }
				break;

			// Vignettes.
			case 'thumbs_infos' : if ($this->data['display']['infos']) { return TRUE; } break;
			case 'thumbs' :
				if (isset($this->data['vignettes'])) {
					$thumbs = $this->data['infos']['vignettes_col'];
					$lignes = $this->data['infos']['vignettes_line'];
					$nb_vignettes = count($this->data['vignettes']);
					while ($nb_vignettes < ($thumbs * $lignes) && ($thumbs * ($lignes - 1)) >= $nb_vignettes) {
						$lignes--;
					}
					$this->interne['thumbs_nb_vignettes'] = $nb_vignettes;
					$this->interne['thumbs_lignes'] = $lignes;
					$this->interne['thumbs_thumbs'] = $thumbs;
					$this->interne['thumbs_limit'] = 1;
					$this->interne['thumbs_num'] = -1;
					$this->interne['thumbs_tr'] = 0;
					$this->interne['thumbs_td'] = 0;
					return TRUE;
				}
		}
	}



	/*
	 * 
	 * ======================================== SECTIONS ;
	 *
	*/

	/* Titre des sections. */
	function getSectionTitre($s, $section, $o = '') {
		switch ($section[0]) {
			case 'n' : $t = 'l\'aide à la navigation'; break;
			case 's' : $t = 'les statistiques'; break;
			case 'h' : $t = 'une image choisie au hasard'; break;
			case 'p' : $t = 'les options de personnalisation'; break;
			case 'e' : $t = 'les informations Exif'; break;
			case 't' : $t = 'les tags'; break;
			case 'l' : $t = 'les liens'; break;
			case 'c' : $t = 'les commentaires'; break;
			default  : $t = 'cette partie';
		}
		$valeur = 0;
		$class = 'cacher';
		$title = 'Cacher ' . $t;
		printf($s, $class, $title);
	}



	/*
	 * 
	 * ======================================== SECTION : NAVIGATION ;
	 *
	*/

	/* Indicateur de la position actuelle */
	function getPosActuel($type, $s = ' class="%s"', $o = 'pos_actuel') {
		switch ($type) {
			case 'accueil' :
				if (isset($this->data['infos']['objet']) && $this->data['infos']['objet'] < 2) {
					printf($s, $o);
				}
				break;
			case 'plan' :
			case 'aide' :
			case 'contact' :
			case 'historique' :
				if ($this->data['infos']['objet'] == $type) {
					printf($s, $o);
				}
		}
	}
	
	/* Objets voisins */
	function getObjetsVoisins($s) {
		$voisines = $this->data['nav']['voisines'];
		for ($i = 0; $i < count($voisines); $i++) {
			if (($this->data['infos']['type'] == 'cat' && $voisines[$i]['categorie_derniere_modif'] == 0) 
			 || ($this->data['infos']['type'] == 'alb' && $voisines[$i]['categorie_derniere_modif'] != 0) ) {
				$l = outils::genLink('?' . $this->data['infos']['type'] . '=' . $voisines[$i]['categorie_id'], '', $voisines[$i]['categorie_nom']);
				$selected = ($this->data['infos']['objet'] == $voisines[$i]['categorie_id']) ? ' selected="selected"' : '';
				printf($s, $l, $selected, strip_tags($voisines[$i]['categorie_nom']));
			}
		}
	}

	/* Recherche avancée */
	function getAdvSearch($avance, $simple) {
		if ($this->data['infos']['advsearch']) {
			$search = (isset($_GET['search'])) ? '&amp;search=' . urlencode($_GET['search']) : '';
			$adv_search = (isset($_GET['sadv'])) ? '&amp;sadv=' . urlencode($_GET['sadv']) : '';
			$l = outils::genLink('?section=recherche' . $search . $adv_search);
			printf($avance, $l);
		} else {
			echo $simple;
		}
	}
	function isEXIF() {
		if (function_exists('read_exif_data')) {
			return TRUE;
		}
	}
	function getAdvSearchAlbums() {
		echo $this->data['list_albums'];
	}
	function getAdvSearchDate($type, $s = '%s') {

		$time = ($type == 'start') ? $this->data['search_params']['date_start'] : $this->data['search_params']['date_end'];

		// Jour.
		$print_date = '<select name="s_date_' . $type . '_jour">';
		for ($i = 1; $i <= 31; $i++) {
			$selected = ($i == date('j', $time)) ? ' selected="selected"' : '';
			$print_date .= "\r\t\t\t\t\t\t\t\t\t\t\t\t\t" . '<option' . $selected . ' value="' . $i . '">' . str_pad($i, 2, 0, STR_PAD_LEFT)  . '</option>';
		}
		$print_date .= "\r\t\t\t\t\t\t\t\t\t\t\t\t" . '</select>';

		// Mois.
		$print_date .= "\r\t\t\t\t\t\t\t\t\t\t\t\t" . '<select name="s_date_' . $type . '_mois">';
		for ($i = 1; $i <= 12; $i++) {
			$selected = ($i == date('n', $time)) ? ' selected="selected"' : '';
			$print_date .= "\r\t\t\t\t\t\t\t\t\t\t\t\t\t" . '<option' . $selected . ' value="' . $i . '">' . strftime('%B', mktime(0, 0, 0, $i, date('j', $time), date('Y', $time)))  . '</option>';
		}
		$print_date .= "\r\t\t\t\t\t\t\t\t\t\t\t\t" . "</select>\n";

		// Année.
		$print_date .= "\r\t\t\t\t\t\t\t\t\t\t\t\t" . '<select name="s_date_' . $type . '_an">';
		for ($i = 1970; $i <= date('Y', $time); $i++) {
			$selected = ($i == date('Y', $time)) ? ' selected="selected"' : '';
			$print_date .= "\r\t\t\t\t\t\t\t\t\t\t\t\t\t" . '<option' . $selected . ' value="' . $i . '">' . $i . '</option>';
		}
		$print_date .= "\r\t\t\t\t\t\t\t\t\t\t\t\t" . '</select>';

		printf($s, $print_date);
	}
	function getAdvSearchParams($p) {
		$checked = ' checked="checked"';
		switch ($p) {

			// Requête.
			case 'text' :
				if (isset($_GET['search'])) {
					echo ' value="' . htmlspecialchars($_GET['search']) . '"';
				}
				break;

			// Dimensions et poids.
			case 'taille_width_start':
			case 'taille_width_end':
			case 'taille_height_start':
			case 'taille_height_end':
			case 'poids_start':
			case 'poids_end':
				if ($this->data['search_params'][$p]) {
					echo ' value="' . $this->data['search_params'][$p] . '"';
				}
				break;

			// Radio.
			case 'et' : if ($this->data['search_params']['AND']) { echo $checked; } break;
			case 'ou' : if (!$this->data['search_params']['AND']) { echo $checked; } break;
			case 'date_ajout' : if (!$this->data['search_params']['date_type']) { echo $checked; } break;
			case 'date_creation' : if ($this->data['search_params']['date_type']) { echo $checked; } break;

			// Checkbox.
			case 'nom' :
			case 'chemin' :
			case 'description' :
			case 'motscles' :
			case 'commentaires' :
			case 'exif_make' :
			case 'exif_model' :
			case 'respect_casse' :
			case 'respect_accents' :
			case 'date' :
			case 'taille' :
			case 'poids' :
				if ($this->data['search_params'][$p]) echo $checked;
		}
	}



	/*
	 * 
	 * ======================================== SECTION : HASARD ;
	 *
	*/

	function getHasardImg($s) {
		$size = 'width="' . $this->getThumbSize('w',1)
			  . '" height="' . $this->getThumbSize('h',1) . '"';
		$lien = $this->data['image_hasard']['lien'];
		$nom = htmlentities($this->data['image_hasard']['nom']);
		$thumb = $this->data['image_hasard']['thumb'];
		$dir = dirname($this->data['infos']['style']);
		printf($s, $lien, $size, $nom, $thumb, $dir);
	}

	function getHasardAlb($s, $limit = 1000) {
		$id = $this->data['image_hasard']['album_id'];
		$nom = wordwrap(strip_tags($this->data['image_hasard']['album_nom']), $limit, '<br />', 0);
		if ($id > 0) {
			$lien = outils::genLink('?alb=' . $id, '', $this->data['image_hasard']['album_nom']);
			printf($s, $lien, $nom);
		}
	}



	/*
	 * 
	 * ======================================== SECTION : TAGS ;
	 *
	*/

	function getTags($s = '%s') {
		if (is_array($this->data['tags'])) {
			$tags = '';
			$objet = 'cat=1';
			if (isset($_GET['alb'])) {
				$objet = 'alb=' . htmlentities($_GET['alb']);
			} elseif (isset($_GET['cat'])) {
				$objet = 'cat=' . htmlentities($_GET['cat']);
			} elseif (isset($this->data['infos']['hvc'])) {
				$objet = $this->data['infos']['hvc']['type'] . '=' . $this->data['infos']['objet'];
			}
			$cat_nom = '';
			if (isset($this->data['infos']['nom'])) {
				$cat_nom = $this->data['infos']['nom'];
			} elseif (isset($this->data['infos_objet']['categorie_nom'])) {
				$cat_nom = $this->data['infos_objet']['categorie_nom'];
			} elseif (isset($this->data['infos']['hvc'])) {
				$cat_nom = $this->data['infos']['hvc']['nom'];
			} elseif (isset($this->data['categorie']['categorie_nom'])) {
				$cat_nom = $this->data['categorie']['categorie_nom'];
			}
			foreach ($this->data['tags'] as $tag => $infos) {
				$pl = ($infos['tag_nombre'] > 1) ? 's' : '';
				$title = ' title="' . $infos['tag_nombre']  . ' image' . $pl . '"';
				$tags .= "\n\t\t\t\t\t\t\t" . '<li class="tag_weight_' . $infos['weight'] . '">';
				$tags .= '<a' . $title . ' href="' . outils::genLink('?' . $objet . '&amp;tag=' . urlencode(html_entity_decode($infos['tag_id'])), '', $cat_nom) . '">';
				$tags .= $infos['tag_id'];
				$tags .= '</a>';
				$tags .= '</li>';
			}
			printf($s, $tags);
		}
	}
	function getNullTags($s = '') {
		if (!is_array($this->data['tags'])) {
			echo $s;
		}
	}
	function getAllTags($s = '%s') {
		if (is_array($this->data['tags'])) {
			$objet = $this->getObjetType();
			$lien = '?section=tags&amp;';
			$cat = 'cat=1';
			if (isset($this->data['historique']['lien'])) {
				$cat = $this->data['historique']['lien'];
			} elseif (isset($this->data['infos']['hvc']['type']) && isset($this->data['infos']['objet'])) {
				$cat = $this->data['infos']['hvc']['type'] . '=' . $this->data['infos']['objet'];
			} elseif (isset($this->data['nav']['retour_id'])) {
				$cat = 'alb=' . $this->data['nav']['retour_id'];
			}
			$lien .= $cat;
			$lien = outils::genLink($lien, '', $this->data['infos']['parent_nom']);
			$title = 'Montrer tous les tags de ' . $objet;
			printf($s, $lien, $title);
		}
	}
	function getTagsCloudTitle($s = '%s') {
		$objet = '<a href="' . outils::genLink('?cat=1') . '">la galerie</a>';
		if ($this->data['categorie']['categorie_id'] > 1) {
			$objet = ($this->data['categorie']['categorie_derniere_modif']) ? 'l\'album' : 'la catégorie';
			$type = ($this->data['categorie']['categorie_derniere_modif']) ? 'alb' : 'cat';
			$l = outils::genLink('?' . $type . '=' . $this->data['categorie']['categorie_id'], '', $this->data['categorie']['categorie_nom']);
			$objet .= ' <a href="' . $l . '">' . $this->data['categorie']['categorie_nom'] . '</a>';
		}
		$titre = 'Nuage de tags de ' . $objet;
		printf($s, $titre);
	}




	/*
	 * 
	 * ======================================== SECTION : PERSONNALISATION ;
	 *
	*/

	/* Nombre de vignettes */
	function getPersoThumbs($type, $s = '%s %s %s', $n = 10) {
		for ($i = 1; $i <= $n; $i++) {
			$selected = ($this->data['infos']['vignettes_' . $type] == $i) ? ' selected="selected"' : '';
			printf($s, $i, $selected, $i);
		}
	}


	/* Ordre des vignettes */
	function getPersoSortOrdre($type, $s = '%s', $o = ' selected="selected"') {
		switch ($type) {
			case 'commentaires' :
				if (empty($this->data['infos']['active_commentaires'])) {
					break;
				}
			case 'votes' :
			case 'note' :
				if (empty($this->data['infos']['active_votes']) && $type != 'commentaires') {
					break;
				}
			default :
				if ($this->data['infos']['vignettes_ordre'] != $type) {
					$o = '';
				}
				printf($s, $o);
		}
	}
	
	function getPersoSortSens($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['infos']['vignettes_sens'] == $type) {
			printf($s, $o);
		}
	}


	/* Informations sous vignettes */
	function getPersoDisplay($type, $s, $o = ' checked="checked"') {
		if ($type == 'jours' && $this->data['user']['recentes']) {
			printf($s, $this->data['infos']['recent_jours']);
		} elseif (!empty($this->data['user'][$type])) {
			if (($type == 'commentaires' || $type == 'votes') && 
			    (empty($this->data['infos']['active_' . $type]) || !empty($this->data['infos']['images_window']))) {
				return;
			}
			$o = ($this->data['display'][$type]) ? $o : '' ;
			printf($s, $o);
		}
	}


	/* Styles */
	function getStyles($s) {
		for ($i = 0; $i < count($this->data['user']['styles']); $i++) {
			$selected = ($this->data['display']['style'] == $this->data['user']['styles'][$i]) ? ' selected="selected"' : '';
			printf($s, $this->data['user']['styles'][$i], $selected, str_replace('_', ' ', $this->data['user']['styles'][$i]));
		}
	}


	/* Ajustement de la taille de l'image */
	function getImageTaille($type, $s = '%s') {
		switch ($type) {
			case 'original' :
				if ($this->data['display']['image_taille'] == 1) {
					printf($s, '');
				}
				break;
			case 'fixed' :
				if ($this->data['display']['image_taille'] == 2) {
					printf($s, '');
				}
				break;
			case 'auto' :
				if ($this->data['display']['image_taille'] == 3) {
					printf($s, '');
				}
				break;
			case 'width' :
				if (!empty($this->data['user']['img_largeur_max'])) {
					printf($s, $this->data['user']['img_largeur_max']);
				}
				break;
			case 'height' :
				if (!empty($this->data['user']['img_hauteur_max'])) {
					printf($s, $this->data['user']['img_hauteur_max']);
				}
		}
	}



	/*
	 * 
	 * ======================================== SECTION : STATISTIQUES ;
	 *
	*/

	function verif_stats($n, $type = 'int') {
		switch ($type) {
			case 'int' :
				$n = (int) $n;
				break;
			case 'float' :
				$n = (float) $n;
				break;
		}
		return ($n < 0) ? 0 : $n;
	}

	/* Statistiques. */
	function getStat($type, $s = '%s', $o = '', $nodis = 0) {
		switch ($type) {
			case 'poids' :
				$o = ($o) ? $o : ',';
				$poids = $this->verif_stats($this->data['stats']['poids'], 'float');
				printf($s, outils::poids($poids, $o));
				break;
			case 'categories' :
			case 'albums' :
				$o = ($o) ? $o : 's';
				$nb = $this->verif_stats($this->data['stats']['nb_' . $type]);
				if (empty($nb)) {
					$o = '';
				} else {
					$o = ($nb > 1) ? $o : '';
				}
				printf($s, $nb, $o);
				break;
			case 'votes' :
				if (empty($this->data['infos']['active_votes'])) {
					break;
				}
			case 'comments' :
				if (empty($this->data['infos']['active_commentaires']) && $type == 'comments') {
					break;
				}
			case 'hits' :
				if ($this->data['infos']['images_window']) {
					break;
				}
			case 'recentes' :
				if (empty($this->data['infos']['recent']) && $type == 'recentes') {
					break;
				}
			case 'images' :
				$type = ($type == 'comments') ? 'commentaires' : $type;
				$o = ($o) ? $o : 's';
				if (!isset($this->data['stats']['nb_' . $type])) {
					break;
				}
				$info = $this->verif_stats($this->data['stats']['nb_' . $type]);
				if (empty($info)) {
					if ($nodis) { return; }
					$o = '';
					$lien_a = '';
					$lien_b = '';
				} else {
					$o = ($info > 1) ? $o : '';
					$lien_a = '<a href="' . 
						outils::genLink('?' . $type . '=' . $this->data['infos']['objet'], 
										'', $this->data['infos']['nom']) . '">';
					$lien_b = '</a>';
				}
				printf($s, $lien_a, $info, $o, $lien_b);
		}
	}

	function getCommentsLink($s = '%s', $o = '') {
		if ($this->data['infos']['images_window'] || empty($this->data['infos']['active_commentaires'])) {
			return;
		}
		if (empty($this->data['stats']['nb_commentaires']) || empty($this->data['infos']['active_page_comments'])) {
			echo $o;
		} else {
			$lien = outils::genLink('?comments=' . $this->data['infos']['objet'], '', $this->data['infos']['nom']);
			$title = ' la galerie';
			if ($this->data['infos']['type'] == 'cat' && $this->data['infos']['objet'] > 1) {
				$title = 'cette catégorie';
			} elseif ($this->data['infos']['type'] == 'alb') {
				$title = 'cet album';
			}				
			$title = 'Voir les commentaires de ' . $title;
			$s .= $o;
			printf($s, $title, $lien, dirname($this->data['infos']['style']));
		}
	}

	function getImageTags($s = '%s') {
		if ($this->data['infos']['tags'] && $this->data['image']['image_tags']) {
			$tags = '';
			$tags = explode(',', $this->data['image']['image_tags']);
			for ($i = 0; $i < count($tags); $i++) {
				if ($tags[$i] != '') {
					$l = outils::genLink('?tag=' . urlencode(html_entity_decode($tags[$i])));
					$tags[$i] = '<a href="' . $l . '">' . str_replace(' ', '&nbsp;', $tags[$i]) . '</a>';
				}
			}
			$tags = implode(' ', $tags);
			printf($s, $tags);
		}
	}
	
	/* Statistiques de l'image */
	function getImageStat($type, $s = '%s', $o = '', $lien = 1) {
		switch ($type) {
			case 'auteur' :
				if ($this->data['infos']['membres_active']) {
					$lien = '<a href="' . outils::genLink('?profil=' . urlencode($this->data['image']['user_login'])) . '">' . str_replace('_', ' ', $this->data['image']['user_login']) . '</a>';
					printf($s, $lien);
				}
				break;
			case 'poids' :
				$o = ($o) ? $o : ',';
				printf($s, outils::poids($this->data['image']['image_poids'], $o));
				break;
			case 'taille' :
				$o = ($o) ? $o : ' x ';
				printf($s, $this->data['image']['image_largeur'] . $o . $this->data['image']['image_hauteur']);
				break;
			case 'hits' :
				printf($s, $this->data['image']['image_hits']);
				break;
			case 'date_creation' :
				$o = ($o) ? $o : '/';
				$date_creation = $this->data['image']['image_date_creation'];
				if ($date_creation && preg_match('`^\d{1,10}$`', $date_creation)) {
					$date_creation = strftime($this->data['infos']['im_date_format'], $date_creation);
					if ($lien) {
						$l = outils::genLink('?date_creation=' . date('d-m-Y', $this->data['image']['image_date_creation']), '', '');
						$date_creation = '<a href="' . $l . '">' . $date_creation . '</a>';
					}
				} else {
					$date_creation = $o;
				}
				printf($s, $date_creation);
				break;
			case 'date' :
				$date = strftime($this->data['infos']['im_date_format'], $this->data['image']['image_date']);
				if ($lien) {
					$l = outils::genLink('?date_ajout=' . date('d-m-Y', $this->data['image']['image_date']), '', '');
					$date = '<a href="' . $l . '">' . $date . '</a>';
				}
				printf($s, $date);
				break;
			case 'comments' :
				if (empty($this->data['comment']['no_comment'])) {
					printf($s, $this->data['image']['image_commentaires']);
				}
				break;
			case 'note' :
				if (empty($this->data['infos']['no_votes'])) {
					$o = ($o) ? $o : '0.0';
					if ($this->data['image']['image_votes'] == 0) {
						printf($s, $o);
					} else {
						$note = sprintf('%1.1f', $this->data['image']['image_note']);
						printf($s, $note);
					}
				}
				break;
			case 'note_star' :
				if (empty($this->data['infos']['no_votes'])) {
					printf($s, $this->note_star($this->data['image']['image_note']));
				}
				break;
			case 'votes';
				if (empty($this->data['infos']['no_votes'])) {
					$pl = ($this->data['image']['image_votes'] > 1) ? 's'  : '';
					printf($s, $this->data['image']['image_votes'] . ' vote' . $pl);
				}
		}
	}

	/* On génère les images en étoiles représentant la note. */
	function note_star($note, $lien = 0, $mini = '') {
		$path = dirname($this->data['infos']['style']) . '/';
		$star_full = '<img alt="étoile pleine" src="' . $path . 'star_full' . $mini . '.png" />';
		$star_demi = '<img alt="étoile demi pleine" src="' . $path . 'star_demi' . $mini . '.png" />';
		$star_empty = '<img alt="étoile vide" src="' . $path . 'star_empty' . $mini . '.png" />';
		if ($lien) {
			$star_full = '<a href="javascript:void(0);">' . $star_full . '</a>';
			$star_demi = '<a href="javascript:void(0);">' . $star_demi . '</a>';
			$star_empty = '<a href="javascript:void(0);">' . $star_empty . '</a>';
		}
		if ($note > 4.75) return str_repeat($star_full, 5);
		if ($note > 4.25) return str_repeat($star_full, 4) . $star_demi;
		if ($note > 3.75) return str_repeat($star_full, 4) . $star_empty;
		if ($note > 3.25) return str_repeat($star_full, 3) . $star_demi . $star_empty;
		if ($note > 2.75) return str_repeat($star_full, 3) . str_repeat($star_empty, 2);
		if ($note > 2.25) return str_repeat($star_full, 2) . $star_demi . str_repeat($star_empty, 2);
		if ($note > 1.75) return str_repeat($star_full, 2) . str_repeat($star_empty, 3);
		if ($note > 1.25) return $star_full . $star_demi . str_repeat($star_empty, 3);
		if ($note > 0.75) return $star_full . str_repeat($star_empty, 4);
		if ($note > 0.25) return $star_demi . str_repeat($star_empty, 4);
		return str_repeat($star_empty, 5);
	}




	/*
	 * 
	 * ======================================== CADRE PRINCIPAL ;
	 *
	*/

	/* Bouton d'élargissement du cadre */
	function getEnlarge($s) {
		$valeur = 'on';
		$title = 'Cacher la sidebar';
		$image = 'enlarge_on.png';
		$alt = 'Cacher la sidebar';
		$img = $this->data['infos']['style_relative'] . '/' . $image;
		$img_html = dirname($this->data['infos']['style']) . '/' . $image;
		if (file_exists($img)) {
			$image_size = @getimagesize($img);
			$size = $image_size[3];
		} else {
			$size = '';
		}
		printf($s, $title, $img_html, $alt, $size);
	}


	/* Bouton retour */
	function getRetour($s) {
		if (!empty($this->data['nav']['retour'])) {
			$img = $this->data['infos']['style_relative'] . '/retour.png';
			$img_html = dirname($this->data['infos']['style']) . '/retour.png';
			if (file_exists($img)) {
				$image_size = @getimagesize($img);
				$size = $image_size[3];
			} else {
				$size = '';
			}
			printf($s, $this->data['nav']['retour'], $img_html, $size);
		}
	}


	/* Description Galerie */
	function getGalerieDescription($s) {
		if (!empty($this->data['infos']['accueil']) && empty($this->data['infos']['desactive']) && $this->data['infos']['page_actuelle'] == 1) {
			$accueil = str_replace('&', '&amp;', nl2br($this->data['infos']['accueil']));
			printf($s, $accueil);
		}
	}


	/* Liens de la position actuelle de l'objet */
	function getPosition($r = 1, $s = '<div id="position">%s%s %s</div>', $o1 = ' / ', $o3 = ' - ') {

		if (isset($this->data['infos']['hvc'])) {
			$this->getHVC($s);
		}

		// Favoris des membres.
		if ($this->data['infos']['type'] == 'mfav') {
			$m = 'Les %s favoris de %s';
			$u = 'Le seul favori de %2$s';
			$nb_objets = $this->data['infos']['nb_objets'];
			$msg = ($nb_objets > 1) ? $m : $u;
			$lien = outils::genLink('?profil=' . urlencode($_GET['mfav']));
			$user = '<a href="' . $lien . '">' . str_replace('_', ' ', $_GET['mfav']) . '</a>';
			$t = sprintf('<span id="hvc_result">' . $msg . '</span>', $nb_objets, $user);
			printf($s, $t, '', '', '');
			return;
		}

		// Images des membres.
		if ($this->data['infos']['type'] == 'mimg') {
			$m = 'Les %s images envoyées par %s';
			$u = 'La seule image envoyée par %2$s';
			$nb_objets = $this->data['infos']['nb_objets'];
			$msg = ($nb_objets > 1) ? $m : $u;

			// Date d'ajout ?
			if (!empty($_GET['date_ajout'])) {
				if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $_GET['date_ajout'], $m)) {
					$date = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
				} else {
					$date = getdate(time());
					$date = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
				}
				$date = strftime($this->data['infos']['im_date_format'], $date);
				$l_date = outils::genLink('?cat=1&amp;date_ajout=' . $_GET['date_ajout']);
				$msg .= ' le <a class="pos_actuel" href="' . $l_date . '"> ' . $date . '</a>';
			}

			$lien = outils::genLink('?profil=' . urlencode($_GET['mimg']));
			$user = '<a href="' . $lien . '">' . str_replace('_', ' ', $_GET['mimg']) . '</a>';
			$t = sprintf('<span id="hvc_result">' . $msg . '</span>', $nb_objets, $user);
			printf($s, $t, '', '', '');
			return;
		}

		// Commentaires des membres.
		if ($this->data['infos']['type'] == 'mcom') {
			$m = 'Les %s commentaires postés par %s';
			$u = 'Le seul commentaire posté par %2$s';
			$nb_objets = $this->data['infos']['nb_objets'];
			$msg = ($nb_objets > 1) ? $m : $u;
			$lien = outils::genLink('?profil=' . urlencode($_GET['mcom']));
			$user = '<a href="' . $lien . '">' . str_replace('_', ' ', $_GET['mcom']) . '</a>';
			$t = sprintf('<p id="comments_result">' . $msg . '</p>', $nb_objets, $user);
			printf($s, $t, '', '', '');
			return;
		}

		// Page des commentaires.
		if ($this->data['infos']['type'] == 'comments') {
			$pl = ($this->data['infos']['nb_objets'] > 1) ? 's' : '';
			if ($this->data['infos']['nb_objets'] == 0) {
				$nb_comments = 'Aucun ';
				$dispo = ' disponible';
			} else {
				$nb_comments = ($this->data['infos']['nb_objets'] > 1) 
					? 'Les ' . $this->data['infos']['nb_objets'] : 'Le seul' ;
				$dispo = '';
			}
			$objet = '<a id="retour" href="' . outils::genLink('?cat=1') . '">la galerie</a>';
			if ($this->data['infos_objet']['categorie_id'] > 1) {
				$type = ($this->data['infos_objet']['categorie_derniere_modif']) ? 'l\'album' : 'la catégorie';
				$type_lien = ($this->data['infos_objet']['categorie_derniere_modif']) ? 'alb' : 'cat';
				$objet = $type . ' <a id="retour" href="' . outils::genLink('?' . $type_lien . '=' . $this->data['infos_objet']['categorie_id'], '', $this->data['infos_objet']['categorie_nom']) . '">' . $this->data['infos_objet']['categorie_nom'] . '</a>';
			}
			$t = '<p id="comments_result">' . $nb_comments . ' commentaire' . $pl . ' de ' . $objet . $dispo . '</p>';
			printf($s, $t, '', '', '');
			return;
		}

		// Recherche.
		if (!empty($_GET['search']) && isset($this->data['infos']['nb_pages'])) {
			$oui = 'Résultats de votre recherche';
			$non = 'Aucun élément n\'a été trouvé pour :';
			if (empty($this->data['infos']['startnum'])) {
				if (empty($this->data['cat_result']) && empty($this->data['vignettes'])) {
					printf($s, '<p id="search_result_msg">' . $non . ' <span id="s_requete">' . $this->data['infos']['objet'] . '</span></p>', '', '', '');
				} else {
					printf($s, '<p id="search_result_msg">' . $oui . ' : <span id="s_requete">' . $this->data['infos']['objet'] . '</span></p>', '', '', '');
				}
			} else {
				printf($s, '<p id="search_result_msg">' . $oui . ' : <span id="s_requete">' . $this->data['infos']['objet'] . '</span></p>', '', '', '');
			}
			return;
		}

		// Tags.
		if ($this->data['infos']['type'] == 'tag') {
			$pl = ($this->data['infos']['nb_objets'] > 1) ? 's' : '';
			$nb_tags = ($this->data['infos']['nb_objets'] > 1) 
				? 'Les ' . $this->data['infos']['nb_objets'] : 'La seule' ;
			$l = outils::genLink('?tag=' . urlencode($_GET['tag']));
			$tags = '<a href="' . $l . '">' . htmlentities($_GET['tag']) . '</a>';
			$objet = '<a id="retour" href="' . outils::genLink('?cat=1') . '">la galerie</a>';
			if ($this->data['infos_objet']['categorie_id'] > 1) {
				$type = ($this->data['infos_objet']['categorie_derniere_modif']) ? 'l\'album' : 'la catégorie';
				$type_lien = ($this->data['infos_objet']['categorie_derniere_modif']) ? 'alb' : 'cat';
				$objet = $type . ' <a id="retour" href="' . outils::genLink('?' . $type_lien . '=' . $this->data['infos_objet']['categorie_id'], '', $this->data['infos_objet']['categorie_nom']) . '">' . $this->data['infos_objet']['categorie_nom'] . '</a>';
			}

			// Date d'ajout ?
			if (!empty($_GET['date_ajout'])) {
				if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $_GET['date_ajout'], $m)) {
					$date = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
				} else {
					$date = getdate(time());
					$date = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
				}
				$date = strftime($this->data['infos']['im_date_format'], $date);
				$l_date = outils::genLink('?cat=1&amp;date_ajout=' . $_GET['date_ajout']);
				$objet .= ' ajoutée' . $pl . ' le <a class="pos_actuel" href="' . $l_date . '"> ' . $date . '</a> et ';
			}

			$t = '<p id="tag_result">' . $nb_tags . ' image' . $pl . ' de ' . $objet . ' associée' . $pl . ' au tag ' . $tags . '.</p>';
			printf($s, $t, '', '', '');
			return;
		}

		// Galerie.
		$lid = ($this->data['infos']['type'] == 'img') ? 0 : 1;
		if (substr($this->data['infos']['type'], 0, 4) == 'date') {
			if (!$this->data['infos']['nb_objets']) {
				header('Location: ' . outils::genLink('?cat=1'));
				exit;
			}
			$mode = ($this->data['infos']['type'] == 'date_creation') ? 'créée' : 'ajoutée';
			$date_get = $_GET[$this->data['infos']['type']];
			if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $date_get, $m)) {
				$date = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
			} else {
				$date = getdate(time());
				$date = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
			}
			$date = strftime($this->data['infos']['im_date_format'], $date);
			$pl = ($this->data['infos']['nb_objets'] > 1) ? 's' : '';
			$nombre = ($this->data['infos']['nb_objets'] > 1) ? 'Les ' . $this->data['infos']['nb_objets'] . ' ' : 'La seule ';
			if (isset($_GET['alb'])) {
				$type = 'l\'album';
				$catalb = 'alb';
			} else {
				$type = 'la catégorie';
				$catalb = 'cat';
			}
			$p_cat = $catalb . '=' . $this->data['historique']['objet_id'];
			$objet = '<a id="retour" class="pos_actuel" href="' . outils::genLink('?' . $p_cat, '', $this->data['historique']['objet_nom'])  . '">' . $this->data['historique']['objet_nom'] . '</a>';
			if ($this->data['historique']['objet_id'] == 1) {
				$type = 'la <a id="retour" class="pos_actuel" href="' . outils::genLink('?cat=1') . '">galerie</a>';
				$objet = '';
			}
			$l = outils::genLink('?' . $this->data['infos']['type'] . '=' . $date_get . '&amp;cat=1', '', $this->data['historique']['objet_nom']);
			$text = $nombre . 'image' . $pl . ' de ' . $type . ' ' . $objet . ' ' . $mode . $pl . ' le <a class="pos_actuel" href="' . $l . '"> ' . $date . '</a>.';
			printf($s, $text, '', '');

		} elseif (
			((isset($this->data['vignettes']) || $this->data['infos']['type'] == 'img') && $this->data['infos']['type'] !== 'search' && empty($this->data['infos']['hvc'])) &&
			($this->data['infos']['objet'] > $lid || (isset($this->data['infos']['nb_pages']) && $this->data['infos']['nb_pages'] > 1))
		   ) {
			if ($this->data['infos']['type'] == 'cat' && $this->data['infos']['objet'] == 1) {
				$text = '<a class="pos_actuel" href="' . outils::genLink('?cat=1') . '">Accueil</a> ';
			} else {
				$cat_one = (isset($this->data['nav']['retour_id']) && $this->data['nav']['retour_id'] == 1) ? substr($this->data['nav']['retour'], 2) : '?cat=1';
				$text = '<a href="' . outils::genLink($cat_one) . '">Accueil</a>' . $o1;
			}
			$pos = '';
			if (!empty($this->data['infos']['hierarchie'])) {
				$pos .= $this->data['infos']['hierarchie'];
			}
			$img_name = '';
			if (isset($this->data['image'])) {
				$img_name = $this->data['image']['image_nom'];
			}
			$cat_name = '';
			if (isset($this->data['infos']['nom'])) {
				$cat_name = $this->data['infos']['nom'];
			} elseif (isset($this->data['infos_objet']['categorie_nom'])) {
				$cat_name = $this->data['infos_objet']['categorie_nom'];
			}
			$nom = ($this->data['infos']['type'] == 'img') ? $this->data['image']['image_nom'] : $this->data['infos']['nom'];
			$pos .= '<a href="' . outils::genLink('?' . $this->data['infos']['type'] . '=' . $this->data['infos']['objet'], $img_name, $cat_name) . '" class="pos_actuel">' . htmlspecialchars(strip_tags($nom)) . '</a>';
			$pos = str_replace('%sep', $o1, $pos);
			$pass = '';
			if (!$this->data['infos']['membres_active'] && !empty($this->data['infos']['pass'])) {
				$pass = $o3 . '<span id="deconnect"><a href="' . outils::genLink('?' . $this->data['infos']['type'] . '=' . $this->data['infos']['objet'], $img_name, $cat_name) . '&amp;deconnect=1">déconnecter</a></span>';
			}

			// Si l'option est activée, on va mettre le lien retour dans le lien de l'objet parent.
			if (isset($_GET['img']) && (
				isset($_GET['images']) 
			 || isset($_GET['recentes'])
			 || isset($_GET['search'])
			 || isset($_GET['hits'])
			 || isset($_GET['commentaires'])
			 || isset($_GET['votes'])
			 || isset($_GET['tag'])
			 || isset($_GET['date_creation'])
			 || isset($_GET['date_ajout']))) {
				$r = 0;
			}

			$pos .= (empty($this->data['image']['user_id'])) ? '' : '<span title="Image dans vos favoris" id="favimg">*</span>';
			printf($s, $text, $pos, $pass);

		} elseif (
			(isset($_GET['cat']) && $_GET['cat'] == 1) ||
			(empty($_GET['cat']) && empty($_GET['alb']) && empty($_GET['img']) && empty($_GET['section']) &&
			 empty($_GET['recentes']) && empty($_GET['hits']) && empty($_GET['commentaires']) && empty($_GET['votes']) && !isset($_GET['search']) && empty($_GET['images']))
		) {
			$text = '<a class="pos_actuel" href="' . outils::genLink('?cat=1') . '">Accueil</a>';
			printf($s, $text, '', '');
		}
	}


	/* Indication de position des pages spéciales */
	function getPositionSpecial($s = '<div id="pos_special">%s</div>') {
		if (empty($this->data['infos']['special'])) {
			return;
		}
		$text = '';
		$startnum = (empty($this->data['infos']['parent_startnum'])) ? '' : '&amp;startnum=' . $this->data['infos']['parent_startnum'];
		if (isset($_GET['mfav'])) {
			$m = 'Favoris de %s';
			$u = 'Favori de %s';
			$nb_objets = $this->data['infos']['nb_objets'];
			$msg = ($nb_objets > 1) ? $m : $u;
			$lien = outils::genLink('?profil=' . urlencode($_GET['mfav']));
			$user = '<a href="' . $lien . '">' . str_replace('_', ' ', $_GET['mfav']) . '</a>';
			$text = sprintf($msg, $user);

		} elseif (isset($_GET['mimg'])) {
			$m = 'Images envoyées par %s';
			$u = 'Image envoyée par %s';
			$nb_objets = $this->data['infos']['nb_objets'];
			$msg = ($nb_objets > 1) ? $m : $u;

			// Date d'ajout ?
			if (!empty($_GET['date_ajout'])) {
				if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $_GET['date_ajout'], $m)) {
					$date = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
				} else {
					$date = getdate(time());
					$date = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
				}
				$date = strftime($this->data['infos']['im_date_format'], $date);
				$l_date = outils::genLink('?cat=1&amp;date_ajout=' . $_GET['date_ajout']);
				$msg .= ' le <a class="pos_actuel" href="' . $l_date . '"> ' . $date . '</a>';
			}

			$lien = outils::genLink('?profil=' . urlencode($_GET['mimg']));
			$user = '<a href="' . $lien . '">' . str_replace('_', ' ', $_GET['mimg']) . '</a>';
			$text = sprintf($msg, $user);

		} elseif (substr($this->data['infos']['special'], 0, 4) == 'date') {
			if (!$this->data['infos']['nb_objets']) {
				header('Location: ' . outils::genLink('?cat=1'));
				exit;
			}
			$mode = ($this->data['infos']['special'] == 'date_creation') ? 'créées' : 'ajoutées';
			$date_get = $_GET[$this->data['infos']['special']];
			if (preg_match('`^(\d{2})-(\d{2})-(\d{4})$`', $date_get, $m)) {
				$date = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
			} else {
				$date = getdate(time());
				$date = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
			}
			$date = strftime($this->data['infos']['im_date_format'], $date);
			if ($this->data['infos']['special_cat'][0]['categorie_derniere_modif']) {
				$type = 'l\'album ';
				$catalb = 'alb';
			} else {
				$type = 'la catégorie ';
				$catalb = 'cat';
			}
			$p_cat = $catalb . '=' . $this->data['infos']['special_cat'][0]['categorie_id'];
			$l = outils::genLink('?' . $p_cat . $startnum, '', $this->data['infos']['special_cat'][0]['categorie_nom']);
			$objet = '<a class="pos_actuel" href="' . $l . '">' . $this->data['infos']['special_cat'][0]['categorie_nom'] . '</a> ';
			if ($this->data['infos']['special_cat'][0]['categorie_id'] == 1) {
				$type = 'la <a class="pos_actuel" href="' . outils::genLink('?cat=1') . '">galerie</a> ';
				$objet = '';
			}
			$l = outils::genLink('?' . $this->data['infos']['special'] . '=' . $date_get . '&amp;' . $p_cat . $startnum, '', $this->data['infos']['special_cat'][0]['categorie_nom']);
			$text = 'Images de ' . $type . $objet . $mode . ' <a id="retour" class="pos_actuel" href="' . $l . '">le ' . $date . '</a>.';
		} elseif ($this->data['infos']['special'] == 'search') {
			$lien = 'search=' . urlencode($_GET['search']);
			$lien .= (isset($_GET['sadv'])) ? '&amp;sadv=' . urlencode($_GET['sadv']) : '';
			$l = outils::genLink('?' . $lien . $startnum);
			$text = '<p id="search_result_msg"><a id="retour" title="Retour aux résultats de la recherche" href="' . $l . '">Résultats de votre recherche</a> : <span id="s_requete">' . htmlspecialchars($_GET['search']) . '</span></p>';
		} else {
			$l = outils::genLink('?' . $this->data['infos']['special'] . '=' . $this->data['infos']['special_cat'][0]['categorie_id'] . $startnum, '', $this->data['infos']['special_cat'][0]['categorie_nom']);
			$lien = '<a id="retour" href="' . $l . '">';
			$text = 'Images ' . $lien;
			$cat = ' la galerie';
			if ($this->data['infos']['special_cat'][0]['categorie_id'] != 1) {
				if ($this->data['infos']['special_cat'][0]['categorie_derniere_modif']) {
					$cat = ' l\'album ';
					$type = 'alb';
				} else {
					$cat = ' la catégorie ';
					$type = 'cat';
				}
				$l = outils::genLink('?' . $type . '=' . $this->data['infos']['special_cat'][0]['categorie_id'], '', $this->data['infos']['special_cat'][0]['categorie_nom']);
				$cat .= '<a href="' . $l . '">' . $this->data['infos']['special_cat'][0]['categorie_nom'] . '</a>';
			}
			switch ($this->data['infos']['special']) {
				case 'images' : $text = $lien . 'Images</a> de' . $cat; break;
				case 'recentes' : $text .= 'les plus récentes</a> de' . $cat; break;
				case 'hits' : $text .= 'les plus visitées</a> de' . $cat; break;
				case 'commentaires' : $text .= 'les plus commentées</a> de' . $cat; break;
				case 'votes' : $text .= 'les mieux notées</a> de' . $cat; break;
				case 'tag' : 
					$tags = $_GET['tag'];
					$type = (isset($type)) ? $type : 'cat';
					$cat_retour = $type . '=' . $this->data['infos']['special_cat'][0]['categorie_id'] . $startnum;
					$l = outils::genLink('?' . $cat_retour . '&amp;tag=' . urlencode($tags), '', $this->data['infos']['special_cat'][0]['categorie_nom']);
					$text = 'Images de' . $cat . ' associées au tag <a id="retour" href="' . $l . '">' . htmlentities($tags) . '</a>';
			}
		}
		printf($s, $text);
	}


	/* Indication de position de la page ou de l'image actuelle */
	function getPageActuelle($s = '%s %s|%s') {
		$type = 'page';
		if ($this->data['infos']['type'] == 'img') {
			$type = 'image';
			$total = count($this->data['nav']['voisines']);
			$actuelle = $this->data['infos']['objet_num'];
		} else {
			$total = $this->data['infos']['nb_pages'];
			$actuelle = $this->data['infos']['page_actuelle'];
		}
		printf($s, $type, $actuelle, $total);
	}


	/* Lien du diaporama */
	function getDiaporamaLien($s = '<a class="lien_js" href="javascript:diapoStart(%s,%s,%s,%s,%s,%s);">diaporama</a>') {
		if (!$this->data['infos']['diaporama'] 
		 || $this->data['infos']['type'] == 'cat'
		 || $this->data['infos']['type'] == 'comments') {
			return;
		}
		$objet1 = '';
		if (isset($_GET['alb'])) {
			$objet1 = 'alb=' . htmlentities($_GET['alb']);
		} elseif (isset($_GET['cat'])) {
			$objet1 = 'cat=' . htmlentities($_GET['cat']);
		} elseif (isset($_GET['images'])) {
			$objet1 = 'images=' . htmlentities($_GET['images']);
		} elseif (isset($_GET['hits'])) {
			$objet1 = 'hits=' . htmlentities($_GET['hits']);
		} elseif (isset($_GET['recentes'])) {
			$objet1 = 'recentes=' . htmlentities($_GET['recentes']);
		} elseif (isset($_GET['votes'])) {
			$objet1 = 'votes=' . htmlentities($_GET['votes']);
		} elseif (isset($_GET['commentaires'])) {
			$objet1 = 'commentaires=' . htmlentities($_GET['commentaires']);
		} elseif (isset($_GET['search'])) {
			$objet1 = 'search=' . urlencode($_GET['search']);
		} elseif (isset($_GET['mimg'])) {
			$objet1 = 'mimg=' . urlencode($_GET['mimg']);
		} elseif (isset($_GET['mfav'])) {
			$objet1 = 'mfav=' . urlencode($_GET['mfav']);
		}
		if (!$objet1 && isset($_GET['img']) 
		  && empty($_GET['tag'])
		  && empty($_GET['date_ajout'])
		  && empty($_GET['date_creation'])) {
			$objet1 = 'alb=' . $this->data['nav']['retour_id'];
		}
		$objet2 = '';
		if (isset($_GET['date_ajout'])) {
			$objet2 = 'date_ajout=' . htmlentities($_GET['date_ajout']);
		} elseif (isset($_GET['date_creation'])) {
			$objet2 = 'date_creation=' . htmlentities($_GET['date_creation']);
		} elseif (isset($_GET['sadv'])) {
			$objet2 = 'sadv=' . urlencode($_GET['sadv']);
		} elseif (isset($_GET['tag'])) {
			$objet2 = 'tag=' . urlencode($_GET['tag']);
		}
		$objet3 = '';
		if (isset($_GET['tag'])) {
			$objet3 = 'tag=' . urlencode($_GET['tag']);
			if ($objet2 == $objet3) {
				$objet3 = '';
			}
		}
		$startnum = 0;
		if (isset($_GET['img']) && isset($this->data['infos']['objet_num'])) {
			$startnum = $this->data['infos']['objet_num']-1;
		}
		if (isset($_GET['startnum'])) {
			$startnum = htmlentities($_GET['startnum']);
		}
		$img = 1;
		if (isset($this->data['vignettes'][0]['id'])) {
			$img = $this->data['vignettes'][0]['id'];
		}
		if (isset($this->data['image']['image_id'])) {
			$img = $this->data['image']['image_id'];
		}
		$num = 0;
		if (isset($this->data['nav']['voisines'])) {
			$num = count($this->data['nav']['voisines']);
		}
		if (isset($this->data['stats']['nb_images'])) {
			$num = $this->data['stats']['nb_images'];
		}
		if (isset($this->data['infos']['nb_objets'])) {
			$num = $this->data['infos']['nb_objets'];
		}
		printf($s, $num, $img, "'" . $objet1 . "'", "'" . $objet2 . "'", "'" . $objet3 . "'", $startnum);
	}

	/* Script JS du diaporama */
	function getDiaporamaJS($chemin) {
		if ($this->data['infos']['diaporama']) {
			if (!$chemin) {
				$chemin = dirname(dirname(dirname($this->data['infos']['style']))) . '/diaporama.js';
			}
			$script = '<script type="text/javascript" src="' . GALERIE_PATH . '/' . $chemin . '"></script>';
			echo $script;
		}
	}

	/* Barres de navigation */
	function getBarreNavPageNext($s = '%s %s %s') {
		$p = (GALERIE_URL_TYPE == 'normal') ? '&amp;' : '?';
		for ($i = 1; $i <= count($this->data['nav']['pages']); $i++) {
			if (isset($this->data['infos']['page_actuelle']) && $this->data['infos']['page_actuelle'] == $i) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$l = outils::genLink($p . 'startnum=' . $this->data['nav']['pages'][$i]['page'], '', '', 1);
			$l = ($l == '/' || $l == '&amp;startnum=0') ? '' : $l;
			printf($s, $l, $selected, $i);
		}
	}


	/* Description de la catégorie */
	function getCatDescription($s = '%s') {
		if (isset($this->data['vignettes']) && isset($this->data['infos']['description'])) {
			printf($s, $this->data['infos']['description']);
		}
	}


	/* Barre de navigation (images et vignettes) */
	function getBarreNav($s, $type, $p1 = '&lt;&lt;', $p2 = '&lt;', $p3 = '&gt;', $p4 = '&gt;&gt;') {
		static $clavier;
		$ot = $this->data['infos']['type'];
		$pi = ($ot == 'img') ? 'image' : 'page';
		switch ($type) {
			case 'first' :
				$e = 'premiere'; $k = ' id="_nav_first"'; $l = $p1; $t = 'Première ' . $pi; break;
			case 'prev' :
				$e = 'precedente'; $k = ' id="_nav_prev"'; $l = $p2; $t = ucfirst($pi) . ' précédente'; break;
			case 'next' :
				$e = 'suivante'; $k = ' id="_nav_next"';	$l = $p3; $t = ucfirst($pi) . ' suivante'; break;
			default :
				$e = 'derniere'; $k = ' id="_nav_last"'; $l = $p4; $t = 'Dernière ' . $pi;
		}
		if (empty($clavier) && $type) {
			$keys = $k;
			if ($type == 'last') {
				$clavier = 1;
			}
		} else {
			$keys = '';
		}
		$params = '';
		if ($ot == 'search' && isset($_GET['sadv'])) {
			$params = '&amp;sadv=' . urlencode($_GET['sadv']);
		}
		if ($ot == 'img') {
			if (isset($_GET['search'])) {
				$params = '&amp;search=' . urlencode($_GET['search']);
				$params .= (isset($_GET['sadv'])) ? '&amp;sadv=' . urlencode($_GET['sadv']) : '';
			} elseif (isset($_GET['images'])) {
				$params = '&amp;images=' . htmlentities($_GET['images']);
			} elseif (isset($_GET['recentes'])) {
				$params = '&amp;recentes=' . htmlentities($_GET['recentes']);
			} elseif (isset($_GET['hits'])) {
				$params = '&amp;hits=' . htmlentities($_GET['hits']);
			} elseif (isset($_GET['commentaires'])) {
				$params = '&amp;commentaires=' . htmlentities($_GET['commentaires']);
			} elseif (isset($_GET['votes'])) {
				$params = '&amp;votes=' . htmlentities($_GET['votes']);
			} elseif (isset($_GET['date_creation'])) {
				$params = '&amp;date_creation=' . htmlentities($_GET['date_creation']);
			} elseif (isset($_GET['date_ajout'])) {
				$params = '&amp;date_ajout=' . htmlentities($_GET['date_ajout']);
			} elseif (isset($_GET['tag'])) {
				$params = '&amp;tag=' . urlencode($_GET['tag']);
			} elseif (isset($_GET['mimg'])) {
				$params = '&amp;mimg=' . urlencode($_GET['mimg']);
			} elseif (isset($_GET['mfav'])) {
				$params = '&amp;mfav=' . urlencode($_GET['mfav']);
			}
			
		}
		if (isset($_GET['date_creation']) || isset($_GET['date_ajout']) || isset($_GET['tag'])) {
			$params .= (isset($_GET['cat'])) ? '&amp;cat=' . htmlentities($_GET['cat']) : '';
			$params .= (isset($_GET['alb'])) ? '&amp;alb=' . htmlentities($_GET['alb']) : '';
			$params .= (isset($_GET['mimg'])) ? '&amp;mimg=' . urlencode($_GET['mimg']) : '';
		}

		$img_name = '';
		if (isset($this->data['nav'][$e]['image_nom'])) {
			$img_name = $this->data['nav'][$e]['image_nom'];
		}
		$cat_name = '';
		if (isset($this->data['infos']['nom'])) {
			$cat_name = $this->data['infos']['nom'];
		} elseif (isset($this->data['infos']['hvc']['nom'])) {
			$cat_name = $this->data['infos']['hvc']['nom'];
		} elseif (isset($this->data['infos_objet']['categorie_nom'])) {
			$cat_name = $this->data['infos_objet']['categorie_nom'];
		} elseif (isset($this->data['infos']['special_cat'][0]['categorie_nom'])) {
			$cat_name = $this->data['infos']['special_cat'][0]['categorie_nom'];
		} elseif (isset($this->data['historique']['objet_nom'])) {
			$cat_name = $this->data['historique']['objet_nom'];
		}

		// Code retour pour fonction getUrlParameters
		if ($type === 0) {
			if (substr($ot, 0, 4) == 'date' || $ot == 'tag') {
				$a = urlencode($_GET[$ot]);
			} else {
				$a = ($ot == 'img') ? '' : urlencode(html_entity_decode($this->data['infos']['objet']));
			}
			return outils::genLink('?' . $ot . '=' . $a . $params, $img_name, $cat_name);
		}

		if (($ot == 'img' && empty($this->data['nav'][$e][1]))
		 || ($ot == 'img' && $this->data['nav'][$e][1] == $this->data['infos']['objet'])
		 || ($ot != 'img' && isset($this->data['nav'][$e][0]))) {
			$lien = $l;
			$class = ' inactive';
		} elseif (substr($ot, 0, 4) == 'date' || $ot == 'tag') {
			$a = urlencode($_GET[$ot]) . '&amp;startnum=';
			$lien = '<a' . $keys . ' href="' . outils::genLink('?' . $ot . '=' . $a . $this->data['nav'][$e][1] . $params, $img_name, $cat_name) . '" title="' . $t . '">' . $l . '</a>';
			$class = '';
		} else {
			$a = ($ot == 'img') ? '' : urlencode(html_entity_decode($this->data['infos']['objet'])) . '&amp;startnum=';
			$lien = '<a' . $keys . ' href="' . outils::genLink('?' . $ot . '=' . $a . $this->data['nav'][$e][1] . $params, $img_name, $cat_name) . '" title="' . $t . '">' . $l . '</a>';
			$class = '';
		}
		printf($s, $class, $lien);
	}


	/* Pied de page */
	function getFooter($s = '$s') {
		$tiret = (strstr($this->data['infos']['footer'], '2')) ? ' - ' : '';
		$pied = 'propulsé par <a href="http://www.igalerie.org/">iGalerie</a>' . $tiret;
		$debug = '';
		if (strstr($this->data['infos']['footer'], '2')) {
			global $_TIMESTART;
			global $_MYSQL;
			list ($m2, $t2) = explode(' ', microtime());
			$time_total = ($m2 + $t2) - ($_TIMESTART[0] + $_TIMESTART[1]);
			$queries = ($_MYSQL['debug']) ? ' avec %d requêtes SQL' : '';
			$pied .= sprintf('page générée en %.3f seconde%s' . $queries, $time_total, ($time_total >= 2) ? 's' : '', $_MYSQL['nb_requetes']);
		}
		if (strstr($this->data['infos']['footer'], '1')) {
			$pied .= '<br />' . nl2br($this->data['infos']['footer_message']);
		}
		if ($this->data['debug']['mysql']) {
			$debug = '<hr /><div style="font-size:12px;text-align:left;">';
			for ($i = 0; $i < count($this->data['debug']['mysql_requetes']); $i++) {
				$q = (strpos($this->data['debug']['mysql_requetes'][$i], '[ERREUR]'))
				   ? $this->data['debug']['mysql_requetes'][$i] 
				   : htmlentities($this->data['debug']['mysql_requetes'][$i]);
				$debug .= '[' . $i . '] ' . $q . '<br /><br />';
			}
			$debug .= '</div>';
		}
		printf($s, $pied, $debug);
	}



	/*
	 * 
	 * ======================================== CADRE PRINCIPAL:VIGNETTES ;
	 *
	*/

	function getThumbSize($type, $forced = 0) {
		if ($this->data['infos']['type'] == 'cat' && !$forced) {
			if (THUMB_ALB_MODE == 'crop') {
				$w = THUMB_ALB_CROP_WIDTH;
				$h = THUMB_ALB_CROP_HEIGHT;
			} else {
				$w = THUMB_ALB_SIZE;
				$h = THUMB_ALB_SIZE;
			}
		} else {
			if (THUMB_IMG_MODE == 'crop') {
				$w = THUMB_IMG_CROP_WIDTH;
				$h = THUMB_IMG_CROP_HEIGHT;
			} else {
				$w = THUMB_IMG_SIZE;
				$h = THUMB_IMG_SIZE;
			}
		}
		return ${$type};
	}

	function getThumbsNextLine() {
		if ($this->interne['thumbs_tr'] < $this->interne['thumbs_lignes']) {
			$this->interne['thumbs_tr']++;
			return TRUE;
		}
	}

	function getThumbsNextThumb() {
		if ($this->interne['thumbs_td'] < $this->interne['thumbs_thumbs'] && $this->interne['thumbs_limit'] <= $this->interne['thumbs_nb_vignettes']) {
			$this->interne['thumbs_limit']++;
			$this->interne['thumbs_num']++;
			$this->interne['thumbs_td']++;
			return TRUE;
		} else {
			$this->interne['thumbs_td'] = 0;
		}
	}

	function getThumb($type, $s = '%s', $o = '', $s2 = '') {
		switch ($type) {
			case 'recent' :
				if ($this->data['display']['recentes'] && 
				   !empty($this->data['vignettes'][$this->interne['thumbs_num']]['recent'])) {
					echo $s;
				}
				break;
			case 'pass' :
				if (!empty($this->data['vignettes'][$this->interne['thumbs_num']]['pass'])) {
					echo $s;
				}
				break;
			case 'lien' :
				printf($s, $this->data['vignettes'][$this->interne['thumbs_num']]['page']);
				break;
			case 'image' :
				if (empty($this->data['vignettes'][$this->interne['thumbs_num']]['pass'])) {
					$type = ($this->data['infos']['type'] == 'cat') ? 'cat' : 'img';
					$file = $this->data['vignettes'][$this->interne['thumbs_num']]['chemin'];
					$tb = GALERIE_PATH . '/getimg.php?' . $type . '=' . $file;
				} else {
					$tb = dirname($this->data['infos']['style']) . '/cadenas_vignettes.png';
				}
				$image_size = 'width="' . $this->getThumbSize('w') .'" height="' . $this->getThumbSize('h') . '"';
				printf($s, $image_size, $tb, $this->data['vignettes'][$this->interne['thumbs_num']]['nom'], dirname($this->data['infos']['style']));
				break;
			case 'nom' :
				if ($this->data['display']['nom']) {
					$nom = $this->data['vignettes'][$this->interne['thumbs_num']]['nom'];
					if ($o !== 0) {
						$limit = $this->getThumbSize('w') / 8;
						$nom = wordwrap($nom, $limit, '<br />', 0);
					}
					if ($this->data['infos']['type'] == 'cat') {
						if ($this->data['vignettes'][$this->interne['thumbs_num']]['type'] != 'album') {
							$nom = '[' . $nom . ']';
						}
					}
					printf($s, $this->data['vignettes'][$this->interne['thumbs_num']]['page'], $nom);
				}
				break;
			case 'date' :
				if ($this->data['display']['date'] || !empty($this->data['display']['s_recentes'])) {
					$format = ($o) ? $o : $this->data['infos']['tb_date_format'];
					printf($s, $this->data['display']['s_recentes'], strftime($format, $this->data['vignettes'][$this->interne['thumbs_num']]['date']));
				}
				break;
			case 'taille' :
				if ($this->data['display']['taille']) {
					$sep = ($o) ? $o : ' x ';
					$taille = $this->data['vignettes'][$this->interne['thumbs_num']]['largeur'] . $sep . $this->data['vignettes'][$this->interne['thumbs_num']]['hauteur'];
					printf($s, $taille);
				}
				break;
			case 'poids' :
				if ($this->data['display']['poids']) {
					$c = ($o) ? $o : ',';
					printf($s, outils::poids($this->data['vignettes'][$this->interne['thumbs_num']]['poids'], $c));
				}
				break;
			case 'hits' :
				if ($this->data['display']['hits'] || !empty($this->data['display']['s_hits'])) {
					$nb_hits = $this->data['vignettes'][$this->interne['thumbs_num']]['nb_hits'];
					$hits = $nb_hits . ' visite';
					$hits = ($nb_hits > 1) ? $hits . 's' : $hits;
					if ($o === 1 && $nb_hits) {
						$lien = outils::genLink('?hits=' . $this->data['vignettes'][$this->interne['thumbs_num']]['id'], '', $this->data['vignettes'][$this->interne['thumbs_num']]['nom']);
						$hits = '<a href="' . $lien . '">' . $hits . '</a>';
					}
					printf($s, $this->data['display']['s_hits'], $hits);
				}
				break;
			case 'comments' :
				if ($this->data['display']['commentaires'] || !empty($this->data['display']['s_comments'])) {
					$nb_comments = $this->data['vignettes'][$this->interne['thumbs_num']]['nb_commentaires'];
					$comments = $nb_comments . ' commentaire';
					$comments = ($nb_comments > 1) ? $comments . 's' : $comments;
					if ($o === 1 && $nb_comments) {
						$page = ($this->data['infos']['active_page_comments']) ? 'comments' : 'commentaires';
						$lien = outils::genLink('?' . $page . '=' . $this->data['vignettes'][$this->interne['thumbs_num']]['id'], '', $this->data['vignettes'][$this->interne['thumbs_num']]['nom']);
						$comments = '<a href="' . $lien . '">' . $comments . '</a>';
					}
					printf($s, $this->data['display']['s_comments'], $comments);
				}
				break;
			case 'votes' :
				if ($this->data['display']['votes'] || !empty($this->data['display']['s_votes'])) {
					$nb_votes = $this->data['vignettes'][$this->interne['thumbs_num']]['nb_votes'];
					$votes = $nb_votes . ' vote';
					$votes = ($nb_votes > 1) ? $votes . 's' : $votes;
					if ($o === 1 && $nb_votes) {
						$lien = outils::genLink('?votes=' . $this->data['vignettes'][$this->interne['thumbs_num']]['id'], '', $this->data['vignettes'][$this->interne['thumbs_num']]['nom']);
						$votes = '<a href="' . $lien . '">' . $votes . '</a>';
					}
					$note_star = $this->note_star($this->data['vignettes'][$this->interne['thumbs_num']]['note'], 0, '_mini');
					$note = sprintf('%1.1f', $this->data['vignettes'][$this->interne['thumbs_num']]['note']);
					printf($s, $this->data['display']['s_votes'], $note, $note_star, $votes);
				}
				break;
			case 'nb_images' :
				if ($this->data['display']['nb_images']) {
					$nb_images = $this->data['vignettes'][$this->interne['thumbs_num']]['nb_images'] . ' image';
					$nb_images = ($nb_images > 1) ? $nb_images . 's' : $nb_images;
					if ($o === 1) {
						$lien = outils::genLink('?images=' . $this->data['vignettes'][$this->interne['thumbs_num']]['id'], '', $this->data['vignettes'][$this->interne['thumbs_num']]['nom']);
						$nb_images = '<a href="' . $lien . '">' . $nb_images . '</a>';
					}
					if (empty($this->data['vignettes'][$this->interne['thumbs_num']]['recent'])) {
						$rcts = '';
					} elseif ($this->data['vignettes'][$this->interne['thumbs_num']]['recent'] !== -1) {
						$lien = outils::genLink('?recentes=' . $this->data['vignettes'][$this->interne['thumbs_num']]['id'], '', $this->data['vignettes'][$this->interne['thumbs_num']]['nom']);
						$rcts = $this->data['vignettes'][$this->interne['thumbs_num']]['recent'];
						$rcts = str_replace('%s', '<a title="Afficher les images récentes de \'' 
							. $this->data['vignettes'][$this->interne['thumbs_num']]['nom'] 
							. '\'" href="' . $lien . '">[' . $rcts . ']</a>', $s2);
					} else {
						$rcts = '';
					}
					printf($s, $nb_images, $rcts);
				}
				break;
			case 'description' :
				$description = $this->data['vignettes'][$this->interne['thumbs_num']]['description'];
				if ($description) {
					printf($s, $description);
				}
		}
	}



	/*
	 * 
	 * ======================================== CADRE PRINCIPAL:IMAGE ;
	 *
	*/

	/* Image */
	function getImage($s) {

		// Redimensionnement par GD.
		if ($this->data['infos']['image_mode_resize'] == 2) {
			$taille = $this->data['infos']['img_resize'];

			$img_file = preg_replace('`^' . GALERIE_ALBUMS . '/`', '', $this->data['image']['image_chemin']);
			$image_path = 'getinter.php?img=' . $img_file;
			$img_file = (IMG_TEXTE) ? 'getitext.php?i=' . $img_file : $this->data['image']['image_chemin'];

			if (preg_match('`<a[^>]+href`', $s)) {
				$s = preg_replace('`</?a[^>]*>`', '', $s);
			}
			$s = preg_replace('`(<img[^>]+>)`', '<a href="' . GALERIE_PATH . '/' . $img_file . '">$1</a>', $s);
			$s .= '<script type="text/javascript">var img_gd_resize = 1</script>';
			$s = str_replace('%20', ' ', $s);

		// Redimensionnement par HTML.
		} else {
			$img_file = preg_replace('`^' . GALERIE_ALBUMS . '/`', '', $this->data['image']['image_chemin']);
			$image_path = (IMG_TEXTE) ? 'getitext.php?i=' . $img_file : $this->data['image']['image_chemin'];
			$image_size = 'width="' . $this->data['image']['image_largeur'] . '" height="' . $this->data['image']['image_hauteur'] . '"';
			switch ($this->data['display']['image_taille']) {

				// Taille originale.
				case '1' :
					$taille = $image_size;
					$auto = '';
					$num = 2;
					break;

				// Taille fixée.
				case '2' :
					$taille = $this->data['infos']['img_resize'];
					$auto = '';
					$num = 0;
					break;

				// Taille auto.
				case '3' :
					$taille = 'style="max-width:100%;"';
					$auto = (defined('AUTO_RESIZE_NO_CORRECTION')) ? '' : 'document.getElementById("igalerie").style.height = window.innerHeight+2 + "px"; ';
					$num = 1;
			}
			$s .= '<script type="text/javascript">' . "\n";
			$s .= '//<![CDATA[' . "\n";
			$s .= 'var img_gd_resize = 0;' . "\n";
			$s .= $auto . "\n";
			$s .= 'var img_auto_resize = ' . $num . ';' . "\n";
			$s .= 'var img_width = ' . $this->data['image']['image_largeur'] . ';' . "\n";
			$s .= 'var img_height = ' . $this->data['image']['image_hauteur'] . ';' . "\n";
			$s .= 'document.getElementById("image_r_msg").innerHTML = "L\'image&nbsp;a&nbsp;été&nbsp;redimensionnée";' . "\n";
			$s .= '//]]>' . "\n";
			$s .= '</script>';
		}
		$nom = htmlspecialchars(strip_tags($this->data['image']['image_nom']));
		printf($s, $taille, GALERIE_PATH . '/' . $image_path, $nom);
	}

	/* Message indiquant qu'une image a été redimensionnée */
	function getImageResizeMsg($s) {
		if ($this->data['display']['image_taille'] == 2 && 
		    !empty($this->data['infos']['img_resize'])) {
			$display = ' style="' . preg_replace('`.*width="(\d+)".*`', 'width:$1px', $this->data['infos']['img_resize']) . '"';
			$msg = 'L\'image&nbsp;a&nbsp;été&nbsp;redimensionnée';
		} else {
			$display = ' style="display:none"';
			$msg = '';
		}
		printf($s, $display, $msg);
	}

	/* Nom du fichier */
	function getImgFile($s = '%s', $o = 1) {
	    if ($o) {
	        $img_file = preg_replace('`^' . GALERIE_ALBUMS . '/`', '', $this->data['image']['image_chemin']);
	        $img_file = (IMG_TEXTE) ? 'getitext.php?i=' . $img_file : $this->data['image']['image_chemin'];
	        $img_path = GALERIE_PATH . '/' . $img_file;
	        $lien = '<a href="' . $img_path . '">' . basename($this->data['image']['image_chemin']) . '</a>';
	        if ($o == 2) {
	            $lien = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
	        }
	    } else {
	        $lien = basename($this->data['image']['image_chemin']);
	    }
	    printf($s, $lien);
	}

	/* Description de l'image */
	function getImageDescription($s) {
		if ($this->data['image']['image_description']) {
			printf($s, nl2br($this->data['image']['image_description']));
		}
	}


	/* Vote */
	function getImgDejaNote($s = '') {
		if (!empty($this->data['infos']['deja_note'])) {
			echo $s;
		}
	}

	function getUserNote($s = '%s') {
		if (empty($this->data['infos']['no_votes'])) {
			if (isset($this->data['infos']['deja_note'])) {
				$note = $this->data['infos']['deja_note'];
			} else {
				$note = 0;
			}
			$script = '<script type="text/javascript">' . "\n";
			$script .= '//<![CDATA[' . "\n";
			$script .= 'var image_id = ' . $this->data['infos']['objet'] . ";\n";
			$script .= '//]]>' . "\n";
			$script .= '</script>' . "\n";
			printf($s, $script, $this->note_star($note, 0));
		}
	}

	/* Commentaires */
	function getNextComment() {
		if (!empty($this->data['commentaires'])) {
			if (!isset($this->interne['comment_num'])) {
				$this->interne['comment_num'] = 0;
			} else {
				$this->interne['comment_num']++;
			}
			if (isset($this->data['commentaires'][$this->interne['comment_num']])) {
				return TRUE;
			}
		}
	}
	function getCommentsNull($s = '') {
		if (empty($this->data['commentaires'])) {
			echo $s;
		}
	}
	function getCommentRejet($s) {
		if (isset($this->data['comment']['rejet'])) {
			printf($s, $this->data['comment']['rejet']);
		}
	}
	function getCommentPreview($s = '') {
		if (isset($this->data['comment']['preview'])) {
			echo $s;
		}
	}
	function getCommentMod($n, $s) {
		if ($n == 'a' && !empty($this->data['infos']['comment_mod_a'])) {
			echo $s;
		}
		if (empty($_POST) && $n == 'b' && !empty($_GET['mod'])) {
			echo $s;
		}
	}
	function getMolpac() {
		if (isset($this->data['infos']['galerie_key'])) {
			$tadd = (empty($_POST['preview'])) ? 0 : 3;
			$time_md5 = md5(time()-$tadd);
			echo md5($time_md5 . $this->data['infos']['galerie_key']);
		}
	}

	/* Elements constituants chaque commentaire */
	function getComment($type, $o = '') {
		switch ($type) {
			case 'co_avatar' :
				if ($this->data['infos']['membres_active'] && $this->data['infos']['membres_avatar']) {
					echo $o;
				}
				break;
			case 'avatar' :
				if ($this->data['infos']['membres_active'] && $this->data['infos']['membres_avatar']) {
					if (!empty($this->data['commentaires'][$this->interne['comment_num']]['user_id'])) {
						$login = $this->data['commentaires'][$this->interne['comment_num']]['login'];
						$nom = str_replace('_', ' ', $login);
						if (empty($this->data['commentaires'][$this->interne['comment_num']]['avatar'])) {
							$src = dirname($this->data['infos']['style']) . '/avatar_default.png';
						} else {
							$src = GALERIE_PATH . '/membres/avatars/avatar_' . $login . '_thumb.jpg';
						}
						$alt = 'avatar de ' . $nom;
						$lien = outils::genLink('?profil=' . urlencode($login));
						$o = '<a title="Profil de ' . $nom . '" href="' . $lien . '">' . $o . '</a>';
					} else {
						$src = dirname($this->data['infos']['style']) . '/avatar_default.png';
						$alt = 'pas d\'avatar';
					}
					printf($o, $alt, $src);
				}
				break;
			case 'id' :
				printf($o, $this->data['commentaires'][$this->interne['comment_num']]['id']);
				break;
			case 'pair' :
				if (!is_integer($this->interne['comment_num'] / 2)) {
					echo $o;
				}
				break;
			case 'preview' :
				if (!empty($this->data['commentaires'][$this->interne['comment_num']]['preview'])) {
					echo $o;
				}
				break;
			case 'num' :
				echo $this->interne['comment_num'] + 1;
				break;
			case 'date' :
				$format = ($o) ? $o : $this->data['infos']['im_date_format'] . ' à %H:%M';
				echo strftime($format, $this->data['commentaires'][$this->interne['comment_num']]['date']);
				break;
			case 'auteur' :
				echo $this->data['commentaires'][$this->interne['comment_num']]['auteur'];
				break;
			case 'ip' :
				echo $this->data['commentaires'][$this->interne['comment_num']]['ip'];
				break;
			case 'site' :
				$s = ($o) ? $o : '%s %s';
				$site = $this->data['commentaires'][$this->interne['comment_num']]['siteweb'];
				if (!empty($site)) {
					printf($s, $this->data['commentaires'][$this->interne['comment_num']]['auteur'], $site);
				}
				break;
			case 'msg' :
				$message = $this->data['commentaires'][$this->interne['comment_num']]['message'];
				echo $message;
		}
	}


	/* Nouveaux commentaires */
	function getNewComment($type, $s = '%s', $o = '') {
		switch ($type) {
			case 'auteur' :
				if (isset($this->data['comment']['auteur'])) {
					printf($s, $this->data['comment']['auteur']);
				}
				break;
			case 'mail' :
				if (isset($this->data['comment']['courriel'])) {
					printf($s, $this->data['comment']['courriel']);
				}
				break;
			case 'site' :
				if (!empty($this->data['comment']['siteweb'])) {
					printf($s, $this->data['comment']['siteweb']);
				} else {
					printf($s, 'http://');
				}
				break;
			case 'msg' :
				if (isset($this->data['comment']['preview']) || isset($this->data['comment']['rejet'])) {
					$message = str_replace('&amp;#8217', "&#039", trim($this->data['comment']['message']));
					printf($s, $message);
				}
				break;
			case 'fac_mail' :
				if (empty($this->data['comment']['o_courriel'])) {
					echo $s;
				} else {
					echo $o;
				}
				break;
			case 'fac_site' :
				if (empty($this->data['comment']['o_siteweb'])) {
					echo $s;
				} else {
					echo $o;
				}
		}
	}

	/* EXIF et IPTC */
	function getMetadata($type, $s, $o = '') {
		if (empty($this->data[$type]['infos'])) {
			echo $o;
		} else {
			foreach ($this->data[$type]['infos'] as $desc => $value) {
				printf($s, $desc, htmlentities($value));
			}
		}
	}



	/*
	 * 
	 * ======================================== CADRE PRINCIPAL:SECTIONS ;
	 *
	*/

	/* Plan */
	function getPlan($s = '%s') {
		printf($s, $this->data['plan']);
	}
	function getPlanStats($s = '%s') {
		printf($s, $this->data['infos']['galerie']);
	}

	/* Contact */
	function getContact($type, $s = '<p>%s</p>') {
		if ($type == 'text' && !empty($this->data['contact']['text'])) {
			printf($s, nl2br($this->data['contact']['text']));
		} elseif ($type == 'courriel' && !empty($this->data['contact']['courriel'])) {
			printf($s, $this->data['contact']['courriel']);
		}
	}
	function getContactRapport($s = '%s') {
		if (isset($this->data['contact']['erreur'])) {
			printf('<p id="msg_erreur"><span>%s</span></p>', $this->data['contact']['erreur']);
		} elseif (isset($this->data['contact']['succes'])) {
			printf('<p id="msg_succes"><span>%s</span></p>', $this->data['contact']['succes']);
		}
	}

	/* Historique */
	function getHistorique($type, $s = '%s') {
		if (isset($this->data['historique'][$type])) {
			printf($s, $this->data['historique'][$type]);
		}
	}
	function getHistoriqueObjet($s = '%s') {
		$nom = ($this->data['historique']['objet_nom']) ? $this->data['historique']['objet_nom'] : 'galerie';
		$obj = 'la ';
		$type = 'cat';
		$objet = 1;
		if (isset($_GET['cat'])) {
			$obj = ($nom == 'galerie') ? 'la ' : 'la catégorie ';
			$type = 'cat';
			$objet = $_GET['cat'];
		}
		if (isset($_GET['alb'])) {
			$obj = 'l\'album ';
			$type = 'alb';
			$objet = $_GET['alb'];
		}
		$l = outils::genLink('?' . $type . '=' . $objet, '', $nom);
		$lien = '<a class="pos_actuel" href="' . $l . '">' . $nom . '</a>';
		$obj = 'Historique des images de ' . $obj . $lien . '.';
		printf($s, $obj);
	}
	function getHistoriqueLien($s = '<p id="historique_lien"><a%s title="%s" href="%s">Historique</a></p>') {
		if (!$this->data['infos']['historique']) {
			return;
		}
		$pos_actuelle = (isset($_GET['section']) && $_GET['section'] == 'historique') ? ' class="pos_actuel"' : '';
		$cat = '&amp;cat=1';
		if (isset($this->data['historique']['lien'])) {
			$cat = '&amp;' . $this->data['historique']['lien'];
		} elseif (isset($this->data['infos']['hvc']['type']) && isset($this->data['infos']['objet'])) {
			$cat = '&amp;' . $this->data['infos']['hvc']['type'] . '=' . $this->data['infos']['objet'];
		} elseif (isset($this->data['nav']['retour_id'])) {
			$cat = '&amp;alb=' . $this->data['nav']['retour_id'];
		}
		$lien = outils::genLink('?section=historique' . $cat, '', $this->data['infos']['parent_nom']);
		$objet = $this->getObjetType();
		$title = 'Historique des images de ' . $objet;
		printf($s, $pos_actuelle, $title, $lien);
	}



	/*
	 * 
	 * ======================================== CADRE PRINCIPAL:RECHERCHE ;
	 *
	*/

	function getSearchResult() {
		if (empty($this->data['infos']['startnum'])) {
			if (!empty($this->data['cat_result']) || !empty($this->data['vignettes'])) {

				// Catégores et/ou albums trouvés.
				if (isset($this->data['cat_result'])) {
					if (isset($this->data['cat_result']['cat'])) {
						$s = (count($this->data['cat_result']['cat']) > 1) ? 's' : '';
						echo '<p id="search_result_cat">' . count($this->data['cat_result']['cat']) . ' catégorie' . $s . ' trouvée' . $s . ' :</p>';
						echo '<ul>';
						foreach ($this->data['cat_result']['cat'] as $k => $v) {
							$l = outils::genLink('?cat=' . $k, '', $v);
							echo '<li><a href="' . $l . '">' . strip_tags($v) . '</a></li>';
						}
						echo '</ul>';
					}
					if (isset($this->data['cat_result']['alb'])) {
						$s = (count($this->data['cat_result']['alb']) > 1) ? 's' : '';
						echo '<p id="search_result_alb">' . count($this->data['cat_result']['alb']) . ' album' . $s . ' trouvé' . $s . ' :</p>';
						echo '<ul>';
						foreach ($this->data['cat_result']['alb'] as $k => $v) {
							$l = outils::genLink('?alb=' . $k, '', $v);
							echo '<li><a href="' . $l . '">' . strip_tags($v) . '</a></li>';
						}
						echo '</ul>';
					}
				}
	
				// Images trouvées.
				if (isset($this->data['vignettes'])) {
					$s = ($this->data['infos']['nb_objets'] > 1) ? 's' : '';
					echo '<p id="search_result_img">' . $this->data['infos']['nb_objets'] . ' image' . $s . ' trouvée' . $s . ' :</p>';
				}
			}
			echo "\n";
		}
	}

	function getSearchText($s = ' value="%s"') {
		if ($this->data['infos']['type'] == 'search') {
			printf($s, $this->data['infos']['objet']);
		}
	}



	/*
	 * 
	 * ======================================== CADRE PRINCIPAL:SPECIALES ;
	 *
	*/

	function getHVC($s = '%s') {
		$g = ($this->data['infos']['hvc']['nb_images'] > 1) ? $g = 's': $g = '';
		$total = ' (' . $this->data['infos']['hvc']['nb_images'] . ' image' . $g . ')';
		$cat_name = '';
		if (!empty($this->data['infos']['hvc']['nom'])) {
			$cat_name = $this->data['infos']['hvc']['nom'];
		}
		$nom = '<a id="retour" href="' . outils::genLink('?' . $this->data['infos']['hvc']['type'] . '=' . $this->data['infos']['objet'], '', $cat_name) . '">' . strip_tags($this->data['infos']['hvc']['nom']) . '</a>';
		$type = (empty($this->data['infos']['hvc']['nom'])) ? '<a href="' . outils::genLink('?cat=1')  . '">la galerie</a>' : $this->data['infos']['hvc']['objet'] . ' ';

		// Le nombre d'objet est-il supérieur à 1?
		if ($this->data['infos']['nb_objets'] > 1) {
			$nb = $this->data['infos']['nb_objets'];
			switch ($this->data['infos']['type']) {
				case 'hits' :
					$objet = 'les plus visitées';
					break;
				case 'votes' :
					$objet = 'les mieux notées';
					break;
				case 'commentaires' :
					$objet = 'les plus commentées';
					break;
				case 'recentes' :
					$objet = 'de moins de ' . $this->data['infos']['recent_jours'] . ' jours';
					break;
				case 'images' :
					$objet = '';
			}
			if ($this->data['infos']['type'] == 'images') {
				$hvc = 'Les ' . $nb . ' images de ' . $type . $nom;
			} else {
				$l = outils::genLink('?' . $this->data['infos']['type'] . '=1');
				$hvc = 'Classement des ' . $nb . ' images <a href="' . $l . '">' . $objet . '</a> de ' . $type . $nom . $total;
			}

		} else {
			switch ($this->data['infos']['type']) {
				case 'hits' :
					$objet = 'visitée';
					break;
				case 'votes' :
					$objet = 'notée';
					break;
				case 'commentaires' :
					$objet = 'commentée';
					break;
				case 'recentes' :
					$objet = 'de moins de ' . $this->data['infos']['recent_jours'] . ' jours';
					break;
				case 'images' :
					$objet = '';
					$total = '';
			}
			if (empty($this->data['infos']['nb_objets'])) {
				$hvc = 'Aucune image ' . $objet . ' dans ' . $type . $nom . '.';
			} else {
				$hvc = 'La seule image <a href="' . htmlentities($_SERVER['REQUEST_URI']) . '">' . $objet . '</a> de ' . $type . $nom . $total . '.';
			}
		}
		printf($s, '<span id="hvc_result">' . $hvc . '</span>', '', '', '');
	}



	/*
	 * 
	 * ======================================== RSS ;
	 *
	*/

	function getRSSImages($s = '%s') {
		if (empty($this->data['infos']['type'])
		 || empty($this->data['infos']['rss'])
		 || empty($this->data['infos']['rss_objet'])) {
			return;
		}
		$title = 'la galerie"';
		if ($this->data['infos']['type'] == 'tag') {
			$lien = $this->data['infos']['objet_type'] . '=' . $this->data['infos']['objet'];
			$lien .= '&amp;tag=' . urlencode($_GET['tag']);
			$title = 'ce tag"';
		} elseif ($this->data['infos']['type'] == 'profil') {
			if ($this->data['membre_profil'][0]['user_id'] > 1 &&
			   !$this->data['membre_profil'][0]['groupe_upload']) {
				return;
			}
			$lien = 'user=' . $this->data['infos']['objet'];
			$title = 'ce membre"';
		} else {
			$lien = $this->data['infos']['type'] . '=' . $this->data['infos']['objet'];
			if ($this->data['infos']['type'] == 'cat' && $this->data['infos']['objet'] > 1) {
				$title = 'cette catégorie"';
			} elseif ($this->data['infos']['type'] == 'alb') {
				$title = 'cet album"';
			}	
		}
		printf($s, 'title="Fil RSS 2.0 des images de ' . $title, GALERIE_PATH . '/rss.php?' . $lien);
	}

	function getRSSCommentaires($s = '%s') {
		if (empty($this->data['infos']['type'])
		 || empty($this->data['infos']['rss'])
		 || empty($this->data['infos']['rss_objet'])) {
			return;
		}
		if ($this->data['infos']['active_commentaires']) {
			$title = 'la galerie"';
			if ($this->data['infos']['type'] == 'tag') {
				$lien = $this->data['infos']['objet_type'] . '=' . $this->data['infos']['objet'];
				$lien .= '&amp;tag=' . urlencode($_GET['tag']);
				$lien .= '&amp;type=com';
				$title = 'ce tag"';
			} elseif ($this->data['infos']['type'] == 'profil') {
				if (!$this->data['membre_profil'][0]['groupe_commentaires']) {
					return;
				}
				$lien = 'user=' . $this->data['infos']['objet'];
				$lien .= '&amp;type=com';
				$title = 'ce membre"';
			} else {
				$lien = $this->data['infos']['type'] . '=' . $this->data['infos']['objet'];
				$lien .= '&amp;type=com';
				if ($this->data['infos']['type'] == 'cat' && $this->data['infos']['objet'] > 1) {
					$title = 'cette catégorie"';
				} elseif ($this->data['infos']['type'] == 'alb') {
					$title = 'cet album"';
				} elseif ($this->data['infos']['type'] == 'img') {
					$title = 'cette image"';
				}
			}
			printf($s, 'title="Fil RSS 2.0 des commentaires de ' . $title, GALERIE_PATH . '/rss.php?' . $lien);
		}
	}



	/*
	 * 
	 * ======================================== PAGE DES COMMENTAIRES ;
	 *
	*/
	
	function getNextPageComment() {
		if (!empty($this->data['commentaires']) && is_array($this->data['commentaires'])) {
			if (!isset($this->interne['comment_num'])) {
				$this->interne['comment_num'] = 0;
			} else {
				$this->interne['comment_num']++;
			}
			if (isset($this->data['commentaires'][$this->interne['comment_num']])) {
				return TRUE;
			}
		}
	}

	function getPageComment($type, $s = '%s', $o = '') {

		switch ($type) {

			case 'image_lien' :
				$nom = $this->data['commentaires'][$this->interne['comment_num']]['image_nom'];
				$lien = '?img=' . $this->data['commentaires'][$this->interne['comment_num']]['image_id'];
				$lien = outils::genLink($lien, $nom);
				if ($o) {
					return $lien;
				} else {
					printf($s, $lien);
				}
				break;

			case 'image_thumb' :
				$s = ($s) ? $s : '<a title="%s" href="%s"><img width="%s" height="%s" alt="%s" src="%s" /></a>';
				$alt = htmlentities($this->data['commentaires'][$this->interne['comment_num']]['image_nom']);
				$href = $this->getPageComment('image_lien', 0, 1);
				$file = $this->data['commentaires'][$this->interne['comment_num']]['image_chemin'];
				$src = GALERIE_PATH . '/getimg.php?img=' . $file;
				$size = outils::thumb_size('img', $o, $this->data['commentaires'][$this->interne['comment_num']]['image_largeur'], $this->data['commentaires'][$this->interne['comment_num']]['image_hauteur']);
				printf($s, $alt, $href, $size[0], $size[1], $alt, $src);
				break;

			case 'album_lien' :
				$nom = $this->data['commentaires'][$this->interne['comment_num']]['categorie_nom'];
				$lien = '?alb=' . $this->data['commentaires'][$this->interne['comment_num']]['categorie_id'];
				$lien = outils::genLink($lien, '', $nom);
				printf($s, $lien);
				break;

			case 'album_nom' :
				$nom = htmlentities($this->data['commentaires'][$this->interne['comment_num']]['categorie_nom']);
				printf($s, $nom);
				break;

			case 'commentaire_date' :
				$format = $this->data['infos']['im_date_format'] . ' à %H:%M';
				$date = $this->data['commentaires'][$this->interne['comment_num']]['commentaire_date'];
				printf($s, outils::ladate($date, $format));
				break;

			case 'commentaire_auteur' :
				if ($this->data['commentaires'][$this->interne['comment_num']]['user_id'] == 0) {
					$auteur = outils::html_specialchars($this->data['commentaires'][$this->interne['comment_num']]['commentaire_auteur']);
				} else {
					if ($this->data['infos']['membres_active']) {
						$login = $this->data['commentaires'][$this->interne['comment_num']]['user_login'];
						$auteur = '<a href="' . outils::genLink('?profil=' . urlencode($login)) . '">' . str_replace('_', ' ', $login) . '</a>';
					} else {
						$auteur = str_replace('_', ' ', $this->data['commentaires'][$this->interne['comment_num']]['user_login']);
					}
				}
				printf($s, $auteur);
				break;

			case 'commentaire_web' :
				$web = $this->data['commentaires'][$this->interne['comment_num']]['commentaire_web'];
				if ($web) {
					$auteur = htmlentities($this->data['commentaires'][$this->interne['comment_num']]['commentaire_auteur']);
					$web = '<a title="Site Web de ' . $auteur . '" href="' . $web . '">site</a>';
					printf($s, $web);
				}
				break;

			case 'commentaire_message' :
				$message = $this->data['commentaires'][$this->interne['comment_num']]['commentaire_message'];
				printf($s, outils::comment_format($message));
				break;
		}

	}




	/*
	 * 
	 * ======================================== MEMBRES : identification ;
	 *
	*/

	function getMembreIdent($s = '%s', $o = 'Informations incorrectes.') {
		if (!empty($this->data['membres']['erreur_identification'])) {
			printf($s, $o);
		}
	}

	function getMembre($type, $s = '%s') {
		switch ($type) {
			case 'sid' :
				echo '<input type="hidden" name="sid" value="' . md5($this->data['membre_user'][0]['user_session_id']) . '" />';
				break;
			case 'favori' :
				if (isset($_GET['img']) && isset($this->data['image']) && empty($this->data['infos']['membres_connexion'])) {
					$addfav = (empty($this->data['image']['user_id'])) ? 1 : 0;
					$text = (empty($this->data['image']['user_id'])) ? 'ajouter aux favoris' : 'retirer des favoris';
					$get = '';
					foreach ($_GET as $p => $v) {
						if ($p != 'addfav') {
							$get .= '&amp;' . $p . '=' . urlencode($v);
						}
					}
					$get = substr($get, 5);
					$cat = 'galerie';
					if (!empty($this->data['infos']['special_cat'][0]['categorie_nom'])) {
						$cat = 'galerie';
						if ($this->data['infos']['special_cat'][0]['categorie_id'] != 1) {
							$cat = $this->data['infos']['special_cat'][0]['categorie_nom'];
						}
					} elseif (!empty($this->data['image']['album'])) {
						$cat = $this->data['image']['album'];
					}
					$lien = outils::genLink('?' . $get . '&amp;addfav=' . $addfav, $this->data['image']['image_nom'], $cat);
					printf($s, $lien, $text);
				}
				break;
			case 'nom' :
				printf($s, $this->getMembre('lien_profil'), $this->data['membre_user'][0]['user_nom'], $this->data['membre_user'][0]['groupe_titre']);
				break;
			case 'avatar' :
				if ($this->data['infos']['membres_avatar']) {
					if ($this->data['membre_user'][0]['user_avatar']) {
						$src = GALERIE_PATH . '/membres/avatars/avatar_' . $this->data['membre_user'][0]['user_login'] . '_thumb.jpg';
						$alt = 'avatar de ' . $this->data['membre_user'][0]['user_nom'];
					} else {
						$src = dirname($this->data['infos']['style']) . '/avatar_default.png';
						$alt = 'pas d\'avatar';
					}
					printf($s, $this->getMembre('lien_profil'), $alt, $src);
				}
				break;
			case 'lien_liste' :
				printf($s, outils::genLink('?membres=liste'));
				break;
			case 'lien_modif_profil' :
				printf($s, outils::genLink('?membres=modif_profil'));
				break;
			case 'lien_deconnect' :
				printf($s, outils::genLink('?membres=deconnect'));
				break;
			case 'lien_profil' :
				return outils::genLink('?profil=' . urlencode($this->data['membre_user'][0]['user_login']));
			case 'lien_upload' :
				if ($this->data['membre_user'][0]['groupe_upload']) {
					printf($s, outils::genLink('?membres=upload'));
					break;
				}
		}
	}



	/*
	 * 
	 * ======================================== PAGE DES MEMBRES  ;
	 *
	*/

	function getMembresMsg($s1 = '<p id="msg_erreur"><span>%s</span></p>',
						   $s2 = '<p id="msg_succes"><span>%s</span></p>') {
		if (isset($this->data['erreur'])) {
			if (is_array($this->data['erreur'])) {
				$erreurs = '';
				foreach ($this->data['erreur'] as $k => $v) {					
					$erreurs .= $v . '<br />';
				}
				if ($erreurs) {
					printf($s1, $erreurs);
				}
			} else {
				printf($s1, $this->data['erreur']);
			}
		}
		if (isset($this->data['succes'])) {
			if (is_array($this->data['succes'])) {
				$succes = '';
				foreach ($this->data['succes'] as $k => $v) {					
					$succes .= $v . '<br />';
				}
				if ($succes) {
					printf($s2, $succes);
				}
			} else {
				printf($s2, $this->data['succes']);
			}
		}
	}
	
	function getMembresList($o, $s = '%s') {
		switch ($o) {
			case 'list':
				echo $this->data['users'];
				break;
			case 'pos':
				$nb_objets = $this->data['nb_objets'];
				$lien = '<a href="' . outils::genLink('?cat=1') . '">galerie</a>';
				$msg = ($nb_objets > 1) ? 'Les %s membres de la ' . $lien : 'Le seul membre de la ' . $lien;
				$texte = sprintf($msg, $nb_objets);
				printf($s, $texte);
				break;
		}
	}



	/*
	 * 
	 * ======================================== PAGE DES MEMBRES : profil ;
	 *
	*/

	function getProfil($o, $s = '%s') {
		switch ($o) {
			case 'objets' :
				if (!empty($this->data['membre_profil'][0]['nb_comments'])
				 || !empty($this->data['membre_profil'][0]['nb_images'])
				 || !empty($this->data['membre_profil'][0]['nb_favoris'])) {
					$list = '<ul id="membre_objets">';
					if (!empty($this->data['membre_profil'][0]['nb_comments'])) {
						$nb_comments = $this->data['membre_profil'][0]['nb_comments'];
						$m = '%s commentaires';
						$u = '%s commentaire';
						$s = ($nb_comments > 1) ? $m : $u;
						$lien = outils::genLink('?mcom=' . urlencode($this->data['membre_profil'][0]['user_login']));
						$s = '<a href="' . $lien . '">' . $s . '</a>';
						$list .= '<li>' . sprintf($s, $nb_comments) . '</li>';
					}
					if (!empty($this->data['membre_profil'][0]['nb_images'])) {
						$nb_images = $this->data['membre_profil'][0]['nb_images'];
						$m = '%s images';
						$u = '%s image';
						$s = ($nb_images > 1) ? $m : $u;
						$lien = outils::genLink('?mimg=' . urlencode($this->data['membre_profil'][0]['user_login']));
						$s = '<a href="' . $lien . '">' . $s . '</a>';
						$list .= '<li>' . sprintf($s, $nb_images) . '</li>';
					}
					if (!empty($this->data['membre_profil'][0]['nb_favoris'])) {
						$nb_images = $this->data['membre_profil'][0]['nb_favoris'];
						$m = '%s favoris';
						$u = '%s favori';
						$s = ($nb_images > 1) ? $m : $u;
						$lien = outils::genLink('?mfav=' . urlencode($this->data['membre_profil'][0]['user_login']));
						$s = '<a href="' . $lien . '">' . $s . '</a>';
						$list .= '<li>' . sprintf($s, $nb_images) . '</li>';
					}
					$list .= '</ul>';
					echo $list;
				}
				break;
			case 'mail_public' :
				if ($this->data['membre_profil'][0]['user_mail_public']) {
					echo $s;
				}
				break;
			case 'newsletter' :
				if ($this->data['membre_profil'][0]['user_newsletter']) {
					echo $s;
				}
				break;
			case 'nom' :
				printf($s, $this->data['membre_profil'][0]['user_nom']);
				break;
			case 'avatar' :
				if ($this->data['infos']['membres_avatar']) {
					if ($this->data['membre_profil'][0]['user_avatar']) {
						$src = GALERIE_PATH . '/membres/avatars/avatar_' . $this->data['membre_profil'][0]['user_login'] . '.jpg';
						$alt = 'avatar de ' . $this->data['membre_profil'][0]['user_nom'];
					} else {
						$src = dirname($this->data['infos']['style']) . '/avatar_default.png';
						$alt = 'pas d\'avatar';
					}
					printf($s, $alt, $src);
				}
				break;
			case 'date_creation' :
				printf($s, outils::ladate($this->data['membre_profil'][0]['user_date_creation'], '%A %d %B %Y'));
				break;
			case 'date_derniere' :
				printf($s, outils::ladate($this->data['membre_profil'][0]['user_date_derniere_visite'], '%A %d %B %Y'));
				break;
			case 'mail' :
				if ($this->data['membre_profil'][0]['user_mail_public']
				 && $this->data['membre_profil'][0]['user_mail']) {
					printf($s, $this->data['membre_profil'][0]['user_mail']);
				}
				break;
			case 'mail_modif' :
				if ($this->data['membre_profil'][0]['user_mail']) {
					printf($s, $this->data['membre_profil'][0]['user_mail']);
				}
				break;
			case 'web' :
				if ($this->data['membre_profil'][0]['user_web']) {
					printf($s, $this->data['membre_profil'][0]['user_web']);
				}
				break;
			case 'lieu' :
				if ($this->data['membre_profil'][0]['user_lieu']) {
					printf($s, htmlentities($this->data['membre_profil'][0]['user_lieu']));
				}
				break;
			case 'groupe' :
				printf($s, $this->data['membre_profil'][0]['groupe_titre']);
				break;
			case 'lien_pass':
				echo outils::genLink('?membres=modif_pass');
				break;
			case 'lien_avatar':
				echo outils::genLink('?membres=modif_avatar');
				break;
		}
	}



	/*
	 * 
	 * ======================================== UPLOAD;
	 *
	*/

	function getUpload($o, $s = '%s') {
		switch ($o) {
			case 'list' :
				printf($s, $this->data['categories_list']);
				break;
			case 'mod' :
				if ($this->data['membre_user'][0]['groupe_upload_mode'] != 'direct') {
					echo $s;
				}
				break;
			case 'create' :
				if ($this->data['membre_user'][0]['groupe_upload_create']) {
					printf($s, outils::genLink('?membres=create'));
				}
				break;
			case 'format' :
				$s = 'format JPEG, GIF ou PNG uniquement; ' 
				   . $this->data['infos']['users_upload_maxsize'] . ' Ko et ' 
				   . $this->data['infos']['users_upload_maxwidth'] . ' x ' 
				   . $this->data['infos']['users_upload_maxheight'] . ' pixels maximum par fichier';
				printf($s);
				break;
			case 'MAX_FILE_SIZE' :
				echo '<input name="MAX_FILE_SIZE" value="' . ($this->data['infos']['users_upload_maxsize']*1024) . '" type="hidden" />';
				break;
		}
	}
}
?>
