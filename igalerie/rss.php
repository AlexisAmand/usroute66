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
?>
<?php
error_reporting(E_ALL);

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
	$valeur = stripslashes($valeur);
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

// On supprime tout paramètre gpc non existant.
$R = array('cat','alb','img','tag','type','user');
foreach ($_GET as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_GET[$name]);
	}
}
$R = array();
foreach ($_POST as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_POST[$name]);
	}
}
$R = array();
foreach ($_COOKIE as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_COOKIE[$name]);
	}
}

// Chargement de la config.
if (file_exists('config/conf.php')) {
	@require_once(dirname(__FILE__) . '/config/conf.php');
} else {
	die('erreur ' . __LINE__);
	exit;
}

$gf = basename(GALERIE_URL);
$gf = (GALERIE_URL_TYPE == 'normal' && $gf == 'index.php') ? '' : $gf;
define('GALERIE_FILE', $gf);

require_once(dirname(__FILE__) . '/includes/classes/class.mysql.php');
require_once(dirname(__FILE__) . '/includes/classes/class.outils.php');


if (isset($_GET['alb']) && preg_match('`^\d{1,12}$`', $_GET['alb'])) {
	$rss = new rss('alb');
} elseif (isset($_GET['cat']) && preg_match('`^\d{1,12}$`', $_GET['cat'])) {
	$rss = new rss('cat');
} elseif (isset($_GET['img']) && preg_match('`^\d{1,12}$`', $_GET['img'])) {
	$rss = new rss('img');
} else {
	$_GET['cat'] = 1;
	$rss = new rss('cat');
}



/*
 * ========== class.rss
 */
class rss {

	var $config;	// Configuration de la galerie.
	var $params;	// Paramètres internes.

	// Objets.
	var $mysql;		// Base de données.


	/*
	 *	Constructeur.
	*/
	function rss($type) {

		$this->params['objet_type'] = $type;

		// Connexion à la base de données.
		$this->mysql = new connexion(MYSQL_SERV, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);

		// Récupération des paramètres de configuration.
		$mysql_requete = 'SELECT parametre,valeur FROM ' . MYSQL_PREF . 'config';
		$this->config = $this->mysql->select($mysql_requete, 3);
		if (!is_array($this->config)) {
			die('erreur ' . __LINE__);
		}

		// La fonctionnalité RSS est-elle activée ?
		if (!$this->config['active_rss']) {
			die('Le flux RSS n\'est pas activé.');
		}

		// Images ou commentaires ?
		if (isset($_GET['type']) && $_GET['type'] == 'com') {
			if ($this->config['active_commentaires']) {
				$this->getLastComments();
				$this->printCommentsRSS();
			} else {
				die('Le flux RSS n\'est pas activé pour les commentaires.');
			}
		} else {
			$this->getLastImages();
			$this->printImagesRSS();
		}
	}



	/*
	  *	Récupération des informations - commentaires.
	*/
	function getLastComments() {

		$this->objetInfos();

		// Tag ?
		$table_user = '';
		$table_tag = '';
		$where_user = '';
		$where_tag = '';
		$tag = '';
		if (isset($_GET['tag'])) {
			$tag = htmlentities($_GET['tag']);
			$table_tag = ',' . MYSQL_PREF . 'tags';
			$where_tag = 'AND ' . MYSQL_PREF . 'tags.tag_id = "' . outils::protege_mysql($tag, $this->mysql->lien) . '" 
						  AND ' . MYSQL_PREF . 'tags.image_id = ' . MYSQL_PREF . 'images.image_id ';
		} elseif (isset($_GET['user'])) {
			$where_user = 'AND ' . MYSQL_PREF . 'users.user_login = "' . $_GET['user'] . '"';
			$table_user = ' JOIN ' . MYSQL_PREF . 'users USING (user_id)';
		}

		// On récupère les derniers commentaires de l'objet.
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'commentaires.commentaire_id AS co_id,
								 ' . MYSQL_PREF . 'commentaires.commentaire_date AS date,
								 ' . MYSQL_PREF . 'commentaires.commentaire_auteur AS auteur,
								 ' . MYSQL_PREF . 'commentaires.commentaire_message AS message,
								 ' . MYSQL_PREF . 'images.image_nom AS nom,
								 ' . MYSQL_PREF . 'images.image_id AS img_id
						    FROM ' . MYSQL_PREF . 'images,
							     ' . MYSQL_PREF . 'commentaires'
								   . $table_user
								   . $table_tag . '
						   WHERE ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $this->params['objet_infos']['chemin'][0] . '%"
						     AND ' . MYSQL_PREF . 'commentaires.commentaire_visible = "1"
						     AND ' . MYSQL_PREF . 'images.image_visible = "1"
						     AND ' . MYSQL_PREF . 'images.image_pass IS NULL
						     AND ' . MYSQL_PREF . 'commentaires.image_id = ' . MYSQL_PREF . 'images.image_id '
								   . $where_user
								   . $where_tag . '
						ORDER BY ' . MYSQL_PREF . 'commentaires.commentaire_date DESC
						 LIMIT 0,' . $this->config['galerie_nb_rss'];
		$this->params['comments'] = $this->mysql->select($mysql_requete, 1);
	}



	/*
	  *	Génère le flux RSS pour les commentaires.
	*/
	function printCommentsRSS() {

		$RFC822 = 'D, d M Y H:i:s O';
		$url = 'http://' . $_SERVER['HTTP_HOST'] . GALERIE_URL;
		$site_path = dirname($url) . '/';

		// On définit l'URL d'accès.
		$site_file = $site_path;
		if (GALERIE_URL_TYPE == 'url_rewrite') {			// Type d'URL 'url_rewrite'.
			$site_file = dirname($url);		
		} elseif (GALERIE_URL_TYPE != 'normal') {			// Type d'URL autre que 'normal'.
			$site_file = $site_path;
		} elseif (basename(GALERIE_URL) != 'index.php') {	// On est en type d'URL 'normal', mais le fichier d'accès n'est pas 'index.php'.
			$site_file = $url;
		}

		$objet_desc = '';
		if (isset($_GET['alb'])) {
			$objet_desc = ' - album « ' . $this->params['objet_infos']['nom'][0] . ' »';
		} elseif (isset($_GET['cat']) && $_GET['cat'] != 1) {
			$objet_desc = ' - catégorie « ' . $this->params['objet_infos']['nom'][0] . ' »';
		} elseif (isset($_GET['img'])) {
			$objet_desc = ' - image « ' . $this->params['objet_infos']['nom'][0] . ' »';
		}

		// Tag ?
		if (isset($_GET['tag'])) {
			if (!$this->config['active_tags']) {
				die('Le flux RSS n\'est pas activé pour les tags.');
			}
			$objet_desc .= ' - tag « ' . htmlspecialchars($_GET['tag']) . ' »';

		// User ?
		} elseif (isset($_GET['user'])) {
			$objet_desc .= ' - ' . str_replace('_', ' ', htmlspecialchars($_GET['user']));
		}

		// Objet.
		if (isset($_GET['img'])) {
			$champ_type = '';
		} else {
			$champ_type = 'image_';
		}
		$objet_nom = ($this->params['objet_infos']['nom'][0]) ? $this->params['objet_infos']['nom'][0] : 'galerie';
		if (isset($_GET['cat'])) {
			$objet = '?cat=' . $_GET['cat'];
		} elseif (isset($_GET['alb'])) {
			$objet = '?alb=' . $_GET['alb'];
		} elseif (isset($_GET['img'])) {
			$objet = '?img=' . $_GET['img'];
		}
		if (!empty($_GET['tag'])) {
			$objet .= '&tag=' . urlencode($_GET['tag']);
		} elseif (!empty($_GET['user'])) {
			$objet = '?mimg=' . urlencode($_GET['user']);
		}
		$objet = outils::genLink($objet, $objet_nom, $objet_nom, 2, '&(?!amp;)');

		$image_link = (GALERIE_URL_TYPE == 'url_rewrite' && basename(GALERIE_URL) != 'index.php' && $objet == '/') ? $url : $site_file . $objet;

		$flux = '<?xml version="1.0" encoding="ISO-8859-15"?>' . "\n";
		$flux .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
		$flux .= '<channel>' . "\n";
		$flux .= '<title>' . htmlspecialchars(strip_tags($this->config['galerie_titre'])) . $objet_desc . '</title>' . "\n";
		$flux .= '<link>' . htmlspecialchars($image_link) . '</link>' . "\n";
		$flux .= '<description>Flux RSS des derniers commentaires</description>' . "\n";
		$flux .= '<language>fr</language> ';
		$flux .= '<generator>http://www.igalerie.org/</generator>' . "\n";
		$flux .= '<lastBuildDate>' . date($RFC822) . '</lastBuildDate>' . "\n";

		// Vignette.
		if (isset($this->params['objet_infos'][$champ_type . 'chemin'])) {
			$thumb_type = 'img';
			if (((isset($_GET['cat']) && $_GET['cat'] > 1) || (isset($_GET['alb']))) && !isset($_GET['tag'])) {
				$thumb_type = 'cat';
			}
			$path = $this->params['objet_infos'][$champ_type . 'chemin'][0];
			$flux .= '<image><url>' . $site_path . 'getimg.php?' . $thumb_type . '=' . $path . '</url><title><![CDATA[' . $objet_nom . ']]></title><link>' . htmlspecialchars($image_link) . '</link></image>' . "\n";
		} elseif (isset($_GET['user']) && $this->config['users_membres_avatars']) {
			$avatar = ($this->params['objet_infos']['user_avatar'][0]) 
					? 'membres/avatars/avatar_' . $_GET['user'] . '_thumb.jpg'
					: 'admin/template/defaut/style/avatar_default.png';
			$flux .= '<image><url>' . $site_path . $avatar . '</url><title><![CDATA[' . str_replace('_', ' ', $_GET['user']) . ']]></title><link>' . htmlspecialchars(str_replace('mimg', 'profil', $image_link)) . '</link></image>' . "\n";
		}

		// Items.
		if (!empty($this->params['comments']) && is_array($this->params['comments'])) {
			for ($i = 0; $i < count($this->params['comments']['date']); $i++) {
				$l = outils::genLink('?img=' . $this->params['comments']['img_id'][$i], $this->params['comments']['nom'][$i], '', 2);
				$urlco = $site_file . $l . '#co' . $this->params['comments']['co_id'][$i];
				$auteur = htmlspecialchars(strip_tags($this->params['comments']['auteur'][$i]));
				$flux .= '<item>' . "\n";
				$flux .= '<title>' . $this->params['comments']['nom'][$i] . ' - ' . $auteur . '</title>' . "\n";
				$flux .= '<link>' . $urlco . '</link>' . "\n";
				$flux .= '<pubDate>' . date($RFC822, $this->params['comments']['date'][$i]) . '</pubDate>' . "\n";
				$flux .= '<dc:creator>' . $auteur . '</dc:creator>' . "\n\n";
				$flux .= '<description><![CDATA[' . nl2br(htmlspecialchars(strip_tags($this->params['comments']['message'][$i]))) . ']]></description>' . "\n";
				$flux .= '<guid isPermaLink="false">' . $urlco . '</guid>' . "\n";
				$flux .= '</item>' . "\n";
			}
		}

		$flux .= '</channel>' . "\n";
		$flux .= '</rss>';

		header('Content-Type: text/xml; charset=iso-8859-15');
		echo $flux;
	}



	/*
	  *	On récupère le nom, le chemin et le type de l'objet.
	*/
	function objetInfos() {

		if (isset($_GET['user']) && preg_match('`^[a-z\d_-]{1,50}$`i', $_GET['user'])) {
			if (!$this->config['users_membres_active']) {
				die(__LINE__ . ' : Flux non disponible.');
				exit;
			}
			$mysql_requete = 'SELECT user_avatar
							    FROM ' . MYSQL_PREF . 'users
							   WHERE user_login = "' . $_GET['user'] . '"';
			$this->params['objet_infos'] = $this->mysql->select($mysql_requete, 1);
			if (!is_array($this->params['objet_infos'])) {
				die(__LINE__ . ' : Objet non existant.');
				exit;
			}
			$this->params['objet_infos']['chemin'][0] = '';
			$this->params['objet_infos']['nom'][0] = '';
			$this->params['objet_infos']['album'][0] = '';
			return;

		}elseif (isset($_GET['cat'])) {
			$cat = $_GET['cat'];
			unset($_GET['alb']);
			unset($_GET['img']);
		} elseif (isset($_GET['alb'])) {
			$cat = $_GET['alb'];
			unset($_GET['cat']);
			unset($_GET['img']);
		} elseif (isset($_GET['img']) && isset($_GET['type']) && $_GET['type'] == 'com') {
			unset($_GET['alb']);
			unset($_GET['cat']);
			$mysql_requete = 'SELECT image_nom AS nom,
									 image_chemin AS chemin,
									 image_pass AS pass
			    FROM ' . MYSQL_PREF . 'images
				WHERE image_id = "' . $_GET['img'] . '"';
			$img_infos = $this->mysql->select($mysql_requete, 1);
			if (!is_array($img_infos) || $img_infos['pass'][0] != '') {
				die(__LINE__ . ' : Objet non existant.');
				exit;
			}
			$this->params['objet_infos'] = $img_infos;
			return ;
		} else {
			die(__LINE__ . ' : Objet non existant.');
			exit;
		}

		// Infos catégorie.
		$mysql_requete = 'SELECT categorie_nom AS nom,
								 categorie_chemin AS chemin,
								 categorie_derniere_modif AS album,
								 categorie_pass AS pass
							FROM ' . MYSQL_PREF . 'categories
						   WHERE categorie_id = "' . $cat . '"
						     AND categorie_visible = "1"
							 AND categorie_pass IS NULL';
		$cat_infos = $this->mysql->select($mysql_requete, 1);
		if (!is_array($cat_infos)) {
			die(__LINE__ . ' : Objet non existant.');
			exit;
		}
		$cat_infos['chemin'][0] = ($cat_infos['chemin'][0] == '.') ? '' : $cat_infos['chemin'][0];

		// Vignette.
		$table_tag = '';
		$mysql_requete_thumb = '';
		if (!empty($_GET['tag'])) {
			$tag = htmlentities($_GET['tag']);
			$table_tag = ', ' . MYSQL_PREF . 'tags';
			$mysql_requete_thumb = ' AND ' . MYSQL_PREF . 'tags.tag_id = "' . outils::protege_mysql($tag, $this->mysql->lien) . '" 
									 AND ' . MYSQL_PREF . 'tags.image_id = ' . MYSQL_PREF . 'images.image_id
									 AND STRCMP(' . MYSQL_PREF . 'images.image_chemin, ' . MYSQL_PREF . 'categories.categorie_chemin)=1
								ORDER BY ' . MYSQL_PREF . 'images.image_date DESC LIMIT 1';
		} elseif ($cat == 1) {
			$mysql_requete_thumb = ' ORDER BY ' . MYSQL_PREF . 'images.image_date DESC LIMIT 1';
		} else {
			$mysql_requete_thumb = ' AND ' . MYSQL_PREF . 'images.image_id = ' . MYSQL_PREF . 'categories.image_representant_id ';
		}
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_chemin AS image_chemin
							FROM ' . MYSQL_PREF . 'categories,
								 ' . MYSQL_PREF . 'images' 
								   . $table_tag . '
						   WHERE ' . MYSQL_PREF . 'categories.categorie_id = "' . $cat . '"
						     AND ' . MYSQL_PREF . 'images.image_visible = "1"
							 AND ' . MYSQL_PREF . 'images.image_pass IS NULL '
								   . $mysql_requete_thumb;
		$thumb = $this->mysql->select($mysql_requete, 1);

		if (is_array($thumb)) {
			$this->params['objet_infos'] = array_merge($cat_infos, $thumb);
		} else {
			$this->params['objet_infos'] = $cat_infos;
		}
	}


	
	/*
	  *	Récupération des informations - images.
	*/
	function getLastImages() {

		$this->objetInfos();

		// Récupération des informations de toutes les catégories.
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'categories.categorie_id,
								 ' . MYSQL_PREF . 'categories.categorie_date,
								 ' . MYSQL_PREF . 'categories.categorie_nom,
								 ' . MYSQL_PREF . 'categories.categorie_chemin,
								 ' . MYSQL_PREF . 'categories.categorie_pass,
								 ' . MYSQL_PREF . 'images.image_chemin AS representant
							FROM ' . MYSQL_PREF . 'categories,' . MYSQL_PREF . 'images
						   WHERE ' . MYSQL_PREF . 'categories.categorie_visible = "1" 
							 AND ' . MYSQL_PREF . 'categories.categorie_chemin LIKE "' . $this->params['objet_infos']['chemin'][0] . '%" 
							 AND ' . MYSQL_PREF . 'categories.categorie_derniere_modif = "0"
							 AND ' . MYSQL_PREF . 'categories.categorie_id != "1"
							 AND ' . MYSQL_PREF . 'categories.image_representant_id = ' . MYSQL_PREF . 'images.image_id
						ORDER BY ' . MYSQL_PREF . 'categories.categorie_date DESC,
								 ' . MYSQL_PREF . 'categories.categorie_id DESC';
		$ajout_categories = $this->mysql->select($mysql_requete, 1);
		if (is_array($ajout_categories)) {
			$ajout_categories['id_date'] = array_flip($ajout_categories['categorie_id']);
			foreach ($ajout_categories['id_date'] as $k => $v) {
				$ajout_categories['id_date'][$k] = $ajout_categories['categorie_date'][$v];
			}
			$ajout_categories['chemin_date'] = array_flip($ajout_categories['categorie_chemin']);
			foreach ($ajout_categories['chemin_date'] as $k => $v) {
				$ajout_categories['chemin_date'][$k] = $ajout_categories['categorie_date'][$v];
			}
			$ajout_categories['chemin_nom'] = array_flip($ajout_categories['categorie_chemin']);
			foreach ($ajout_categories['chemin_nom'] as $k => $v) {
				$ajout_categories['chemin_nom'][$k] = $ajout_categories['categorie_nom'][$v];
			}
			$ajout_categories['chemin_id'] = array_flip($ajout_categories['categorie_chemin']);
			foreach ($ajout_categories['chemin_id'] as $k => $v) {
				$ajout_categories['chemin_id'][$k] = $ajout_categories['categorie_id'][$v];
			}
			$ajout_categories['id_representant'] = array_flip($ajout_categories['categorie_id']);
			foreach ($ajout_categories['id_representant'] as $k => $v) {
				$ajout_categories['id_representant'][$k] = $ajout_categories['representant'][$v];
			}
			$ajout_categories['chemin_pass'] = array_flip($ajout_categories['categorie_chemin']);
			foreach ($ajout_categories['chemin_pass'] as $k => $v) {
				$ajout_categories['chemin_pass'][$k] = $ajout_categories['categorie_pass'][$v];
			}
		}

		// Récupération des informations de tous les albums.
		$membres_pass = ($this->config['users_membres_active']) ? ' AND ' . MYSQL_PREF . 'categories.categorie_pass IS NULL ' : '';
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'categories.categorie_id,
								 ' . MYSQL_PREF . 'categories.categorie_date,
								 ' . MYSQL_PREF . 'categories.categorie_nom,
								 ' . MYSQL_PREF . 'categories.categorie_chemin,
								 ' . MYSQL_PREF . 'categories.categorie_pass,
								 ' . MYSQL_PREF . 'images.image_chemin AS representant
							FROM ' . MYSQL_PREF . 'categories,' . MYSQL_PREF . 'images
						   WHERE ' . MYSQL_PREF . 'categories.categorie_visible = "1" 
							 AND ' . MYSQL_PREF . 'categories.categorie_chemin LIKE "' . $this->params['objet_infos']['chemin'][0] . '%" 
							 AND ' . MYSQL_PREF . 'categories.categorie_derniere_modif != "0"
							 AND ' . MYSQL_PREF . 'categories.image_representant_id = ' . MYSQL_PREF . 'images.image_id'
								   . $membres_pass . '
						ORDER BY ' . MYSQL_PREF . 'categories.categorie_date DESC,
								 ' . MYSQL_PREF . 'categories.categorie_id DESC';
		$ajout_albums = $this->mysql->select($mysql_requete, 1);
		if (!is_array($ajout_albums)) {
			return;
		}
		$ajout_albums['id_date'] = array_flip($ajout_albums['categorie_id']);
		foreach ($ajout_albums['id_date'] as $k => $v) {
			$ajout_albums['id_date'][$k] = $ajout_albums['categorie_date'][$v];
		}
		$ajout_albums['id_chemin'] = array_flip($ajout_albums['categorie_id']);
		foreach ($ajout_albums['id_chemin'] as $k => $v) {
			$ajout_albums['id_chemin'][$k] = $ajout_albums['categorie_chemin'][$v];
		}
		$ajout_albums['chemin_pass'] = array_flip($ajout_albums['categorie_chemin']);
		foreach ($ajout_albums['chemin_pass'] as $k => $v) {
			$ajout_albums['chemin_pass'][$k] = $ajout_albums['categorie_pass'][$v];
		}

		// Tag / User ?
		$table_tag = '';
		$table_user = '';
		$where_tag = '';
		$where_user = '';
		$tag = '';
		if (isset($_GET['tag'])) {
			if (!$this->config['active_tags']) {
				die('Le flux RSS n\'est pas activé pour les tags.');
			}
			$tag = htmlentities($_GET['tag']);
			$table_tag = ',' . MYSQL_PREF . 'tags';
			$where_tag = 'AND ' . MYSQL_PREF . 'tags.tag_id = "' . outils::protege_mysql($tag, $this->mysql->lien) . '" 
						  AND ' . MYSQL_PREF . 'tags.image_id = ' . MYSQL_PREF . 'images.image_id ';
		} elseif (isset($_GET['user'])) {
			$where_user = 'AND ' . MYSQL_PREF . 'users.user_login = "' . $_GET['user'] . '"';
			$table_user = ' JOIN ' . MYSQL_PREF . 'users USING (user_id)';
		}

		// On récupère les dates des derniers ajouts d'images.
		$membres_pass = ($this->config['users_membres_active']) ? ' AND ' . MYSQL_PREF . 'images.image_pass IS NULL ' : '';
		$mysql_requete = 'SELECT DISTINCT ' . MYSQL_PREF . 'images.image_date 
							FROM ' . MYSQL_PREF . 'images' 
								   . $table_tag
								   . $table_user . '
						   WHERE ' . MYSQL_PREF . 'images.image_visible = "1" 
						     AND ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $this->params['objet_infos']['chemin'][0] . '%"'
								   . $where_user
								   . $where_tag
								   . $membres_pass . '
						ORDER BY ' . MYSQL_PREF . 'images.image_date DESC 
						   LIMIT 0,' . $this->config['galerie_nb_rss'];
		$ajout_images = $this->mysql->select($mysql_requete, 1);

		// On analyse chaque date.
		if (is_array($ajout_images)) {
			for ($i = 0; $i < count($ajout_images['image_date']); $i++) {

				$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'images.categorie_parent_id) AS nb_images,
										 ' . MYSQL_PREF . 'images.categorie_parent_id,
										 ' . MYSQL_PREF . 'images.image_chemin,
										 ' . MYSQL_PREF . 'images.image_pass,
										 ' . MYSQL_PREF . 'categories.categorie_nom
									FROM ' . MYSQL_PREF . 'images' . $table_user . ','
										   . MYSQL_PREF . 'categories'
										   . $table_tag . '
								   WHERE ' . MYSQL_PREF . 'images.image_visible = "1" 
								     AND ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $this->params['objet_infos']['chemin'][0] . '%" 
									 AND ' . MYSQL_PREF . 'images.image_date = "' . $ajout_images['image_date'][$i] . '" 
									 AND ' . MYSQL_PREF . 'images.categorie_parent_id = ' . MYSQL_PREF . 'categories.categorie_id '
										   . $where_user
										   . $where_tag
										   . $membres_pass . '
								GROUP BY ' . MYSQL_PREF . 'images.categorie_parent_id';
				$images = $this->mysql->select($mysql_requete);
				if (!is_array($images)) {
					continue;
				}

				$this->params['ajouts'][$ajout_images['image_date'][$i]] = array();
				for ($n = 0; $n < count($images); $n++) {
					$type = '';
					$objet = '';

					if ($images[$n]['image_pass'] == '') {
						if (isset($ajout_albums['id_date'][$images[$n]['categorie_parent_id']]) &&
						    $ajout_albums['id_date'][$images[$n]['categorie_parent_id']] == '0'.$ajout_images['image_date'][$i] && !$tag) {
							$cat_path = dirname($ajout_albums['id_chemin'][$images[$n]['categorie_parent_id']]) . '/';
							if (isset($ajout_categories['chemin_date'][$cat_path]) &&
								$ajout_categories['chemin_date'][$cat_path] == '1'.$ajout_images['image_date'][$i]) {
								$cat_path_b = $cat_path;
								while ($cat_path != './') {
									$cat_path_b = dirname($cat_path_b) . '/';
									if (isset($ajout_categories['chemin_date'][$cat_path_b]) &&
										$ajout_categories['chemin_date'][$cat_path_b] == '1'.$ajout_images['image_date'][$i]) {
										$cat_path = $cat_path_b;
									} else {
										break;
									}
								}
								$type = 'cat';
								$nom = $ajout_categories['chemin_nom'][$cat_path];
								$id = $ajout_categories['chemin_id'][$cat_path];
								$thumb = $ajout_categories['id_representant'][$id];
							} else {
								$type = 'alb';
								$nom = $images[$n]['categorie_nom'];
								$id = $images[$n]['categorie_parent_id'];
								$thumb = $ajout_albums['representant'][array_search($id, $ajout_albums['categorie_id'])];
							}
						} else {
							$type = 'img';
							$nom = $images[$n]['categorie_nom'];
							$id = $images[$n]['categorie_parent_id'];
							$thumb = '';
						}

					// Images protégées.
					} else {
						$type = 'img';
						$nom = $images[$n]['categorie_nom'];
						$id = $images[$n]['categorie_parent_id'];
						$thumb = '';
						$objet = 'alb';

						// On vérifie si les catégories parentes sont protégées.
						$path = dirname($images[$n]['image_chemin']) . '/';
						if ($path != './') {
							$path = dirname($path) . '/';
							while ($path  != './') {
								if ($ajout_categories['chemin_pass'][$path] != '') {
									$objet = 'cat';
									$nom = $ajout_categories['chemin_nom'][$path];
									$id = $ajout_categories['chemin_id'][$path];
									$path = dirname($path) . '/';
								} else {
									break;
								}
							}
						}
					}
					if (isset($this->params['ajouts'][$ajout_images['image_date'][$i]][$type][$id])) {
						$this->params['ajouts'][$ajout_images['image_date'][$i]][$type][$id]['nb_images'] += $images[$n]['nb_images'];
					} else {
						$this->params['ajouts'][$ajout_images['image_date'][$i]][$type][$id] = array(
							'nom' => $nom,
							'nb_images' => $images[$n]['nb_images'],
							'thumb' => $thumb,
							'objet' => $objet
							);
					}
				}
			}
		}

		// On ferme la connexion à la base de données.
		$this->mysql->fermer();
	}



	/*
	  *	Génère le flux RSS pour les images.
	*/
	function printImagesRSS() {

		$RFC822 = 'D, d M Y H:i:s O';
		$url = 'http://' . $_SERVER['HTTP_HOST'] . GALERIE_URL;
		$site_path = dirname($url) . '/';

		// On définit l'URL d'accès.
		$site_file = $site_path;
		if (GALERIE_URL_TYPE == 'url_rewrite') {			// Type d'URL 'url_rewrite'.
			$site_file = dirname($url);		
		} elseif (GALERIE_URL_TYPE != 'normal') {			// Type d'URL autre que 'normal'.
			$site_file = $site_path;
		} elseif (basename(GALERIE_URL) != 'index.php') {	// On est en type d'URL 'normal', mais le fichier d'accès n'est pas 'index.php'.
			$site_file = $url;
		}

		$objet_desc = '';
		if ($this->params['objet_infos']['album'][0] > 0) {
			$objet_desc = ' - album « ' . $this->params['objet_infos']['nom'][0] . ' »';
		} elseif ($this->params['objet_infos']['nom'][0] != '') {
			$objet_desc = ' - catégorie « ' . $this->params['objet_infos']['nom'][0] . ' »';
		}

		// Tag ?
		if (isset($_GET['tag'])) {
			$objet_desc .= ' - tag « ' . htmlspecialchars($_GET['tag']) . ' »';
		
		// User ?
		} elseif (isset($_GET['user'])) {
			$objet_desc .= ' - ' . str_replace('_', ' ', htmlspecialchars($_GET['user']));
		}

		// Objet.
		$objet_nom = ($this->params['objet_infos']['nom'][0]) ? $this->params['objet_infos']['nom'][0] : 'galerie';
		$objet_normal = (isset($_GET['cat'])) ? '?cat=' . $_GET['cat'] : '?alb=' . $_GET['alb'];
		if (!empty($_GET['tag'])) {
			$objet_normal .= '&tag=' . urlencode($_GET['tag']);
		} elseif (!empty($_GET['user'])) {
			$objet_normal = '?mimg=' . urlencode($_GET['user']);
		}
		$objet = outils::genLink($objet_normal, '', $objet_nom, 2, '&(?!amp;)');
		$image_link = (GALERIE_URL_TYPE == 'url_rewrite' && basename(GALERIE_URL) != 'index.php' && $objet == '/') ? $url : $site_file . $objet;

		$flux = '<?xml version="1.0" encoding="ISO-8859-15"?>' . "\n";
		$flux .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
		$flux .= '<channel>' . "\n";
		$flux .= '<title>' . htmlspecialchars(strip_tags($this->config['galerie_titre'])) . $objet_desc . '</title>' . "\n";
		$flux .= '<link>' . htmlspecialchars($image_link) . '</link>' . "\n";
		$flux .= '<description>Flux RSS des dernières images</description>' . "\n";
		$flux .= '<language>fr</language>' . "\n";
		$flux .= '<generator>http://www.igalerie.org/</generator>' . "\n";
		$flux .= '<lastBuildDate>' . date($RFC822) . '</lastBuildDate>' . "\n";

		// Vignette.
		if (isset($this->params['objet_infos']['image_chemin'])) {
			$thumb_type = 'img';
			if (((isset($_GET['cat']) && $_GET['cat'] > 1) || (isset($_GET['alb']))) && !isset($_GET['tag'])) {
				$thumb_type = 'cat';
			}
			$path = $this->params['objet_infos']['image_chemin'][0];
			$flux .= '<image><url>' . $site_path . 'getimg.php?' . $thumb_type . '=' . $path . '</url><title><![CDATA[' . $objet_nom . ']]></title><link>' . htmlspecialchars($image_link) . '</link></image>' . "\n";
		} elseif (isset($_GET['user']) && $this->config['users_membres_avatars']) {
			$avatar = ($this->params['objet_infos']['user_avatar'][0]) 
					? 'membres/avatars/avatar_' . $_GET['user'] . '_thumb.jpg'
					: 'admin/template/defaut/style/avatar_default.png';
			$flux .= '<image><url>' . $site_path . $avatar . '</url><title><![CDATA[' . str_replace('_', ' ', $_GET['user']) . ']]></title><link>' . htmlspecialchars(str_replace('mimg', 'profil', $image_link)) . '</link></image>' . "\n";
		}

		// Items.
		if (isset($this->params['ajouts']) && is_array($this->params['ajouts'])) {
			foreach ($this->params['ajouts'] as $ts => $types) {
				$flux .= '<item>' . "\n";
				$flux .= '<title>' . outils::ladate($ts) . '</title>' . "\n";
				$flux .= '<link>' . $site_file . htmlspecialchars(outils::genLink($objet_normal . '&date_ajout=' . date('d-m-Y', $ts), '', $objet_nom, 2, '&(?!amp;)')) . '</link>' . "\n";
				$flux .= '<pubDate>' . date($RFC822, $ts) . '</pubDate>' . "\n";
				$flux .= '<description><![CDATA[';

				// Derniers ajouts.
				$adds = '<ul>';
				if (isset($types['cat'])) {
					$nb_cat = count($types['cat']);
					$s = ($nb_cat > 1) ? 's' : '';
					$adds .= '<li>';
					$adds .= $nb_cat . ' nouvelle' . $s . ' categorie' . $s . ':<ul>';
					foreach ($types['cat'] as $id => $infos) {
						$s = ($infos['nb_images'] > 1) ? 's' : '';
						$thumb = $site_path . 'getimg.php?cat=' . $infos['thumb'];
						$l = $site_file . outils::genLink('?cat=' . $id, '', $infos['nom'], 2);
						$lien = '<a href="' . $l . '">';
						$adds .= '<li>' . $lien . $infos['nom'] . '</a> - ' . $infos['nb_images'] . ' image' . $s;
						$adds .= '<p>' . $lien . '<img src="' . $thumb . '" alt="' . $infos['nom'] . '" /></a></p><br /></li>';
					}
					$adds .= '</ul></li>';
				}
				if (isset($types['alb'])) {
					$nb_alb = count($types['alb']);
					$s = ($nb_alb > 1) ? 's' : '';
					$new = ($nb_alb > 1) ? 'nouveaux' : 'nouvel';
					$adds .= '<li>';
					$adds .= $nb_alb . ' ' . $new . ' album' . $s . ':<ul>';
					foreach ($types['alb'] as $id => $infos) {
						$s = ($infos['nb_images'] > 1) ? 's' : '';
						$thumb = $site_path . 'getimg.php?cat=' . $infos['thumb'];
						$l = $site_file . outils::genLink('?alb=' . $id, '', $infos['nom'], 2);
						$lien = '<a href="' . $l . '">';
						$adds .= '<li>' . $lien . $infos['nom'] . '</a> - ' . $infos['nb_images'] . ' image' . $s;
						$adds .= '<p>' . $lien . '<img src="' . $thumb . '" alt="' . $infos['nom'] . '" /></a></p><br /></li>';
					}
					$adds .= '</ul></li>';
				}
				if (isset($types['img'])) {
					foreach ($types['img'] as $id => $infos) {
						$s = ($infos['nb_images'] > 1) ? 's' : '';
						if ($infos['objet'] == 'cat') {
							$objet_type = 'la catégorie';
							$l = $site_file . outils::genLink('?cat=' . $id, '', $infos['nom'], 2);
						} else {
							$objet_type = 'l\'album';
							$l = $site_file . outils::genLink('?alb=' . $id, '', $infos['nom'], 2);
						}
						$adds .= '<li>' . $infos['nb_images'] . ' nouvelle' . $s . ' image' . $s . ' dans ' . $objet_type . ' <a href="' . $l . '">' . $infos['nom'] . '</a></li>';
					}
				}
				$adds .= '</ul>';

				$flux .= $adds;
				$flux .= ']]></description>' . "\n";
				$flux .= '<guid isPermaLink="false">md5:' . md5($site_path . $objet . $ts) . '</guid>' . "\n";
				$flux .= '</item>' . "\n";
			}
		}

		$flux .= '</channel>' . "\n";
		$flux .= '</rss>';

		header('Content-Type: text/xml; charset=iso-8859-15');
		echo $flux;
	}
}
?>