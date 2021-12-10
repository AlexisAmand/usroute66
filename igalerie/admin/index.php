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
global $TIME_START;
$TIME_START = explode(' ', microtime());

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

// Chargement de la config.
if (file_exists(dirname(__FILE__) . '/../config/conf.php')) {
	require_once(dirname(__FILE__) . '/../config/conf.php');
} else {
	define('GALERIE_INSTALL', 0);
}

// La galerie est-elle installée ?
if (!defined('GALERIE_INSTALL') || !GALERIE_INSTALL) {
	header('Location:../install/');
	exit;
}

require_once(dirname(__FILE__) . '/../includes/classes/class.mysql.php');
require_once(dirname(__FILE__) . '/../includes/classes/class.cookie.php');
require_once(dirname(__FILE__) . '/../includes/classes/class.outils.php');
require_once(dirname(__FILE__) . '/../includes/classes/class.files.php');


// Filtrage des paramètres GET et REQUEST.
$gets = array('img', 'alb', 'cat', 'startnum', 'obj', 'sub_obj',
			  'str');
for ($i = 0; $i < count($gets); $i++) {
	verif_gets($gets[$i], '`^\d{1,12}$`');
}

function verif_gets($p, $re) {
	if (isset($_GET[$p]) && !preg_match($re, $_GET[$p])) {
		unset($_GET[$p]);
	}
	if (isset($_REQUEST[$p]) && !preg_match($re, $_REQUEST[$p])) {
		unset($_REQUEST[$p]);
	}
}



// Si une section parmis celle existantes est demandée, on la traite...
if (isset($_REQUEST['section'])) {

	// Création de l'objet "admin", avec initialisation de paramètres communs.
	$admin = new admin();

	switch ($_REQUEST['section']) {
		case 'ftp' :
			require_once(dirname(__FILE__) . '/../includes/classes/class.upload.php');
			if (isset($_REQUEST['action'])) {
				switch ($_REQUEST['action']) {
					case 'enregistrement' :
						$admin->ftp();
						break;
				}
			}
			break;
		case 'galerie' :
			if (!empty($_FILES)) {
				require_once(dirname(__FILE__) . '/../includes/classes/class.upload.php');
			}
			if (isset($_REQUEST['page'])) {
				switch ($_REQUEST['page']) {
					case 'gestion' :
						$admin->galerie_action();
						$admin->galerie();
						break;
					default :
						header('Location: index.php?section=galerie&page=gestion');
						exit;
				}
			} else {
				header('Location: index.php?section=galerie&page=gestion');
				exit;
			}
			break;
		case 'representant' :
			if (isset($_REQUEST['cat']) && isset($_REQUEST['str'])) {
				$admin->galerie_representant();
			} else {
				header('Location: index.php?section=galerie&page=gestion');
				exit;
			}
			break;
		case 'votes' :
			$admin->display_votes();
			break;
		case 'tags' :
			$admin->display_tags();
			break;
		case 'utilisateurs' :
			if (isset($_REQUEST['page'])) {
				switch ($_REQUEST['page']) {
					case 'general' :
						$admin->users_general();
						break;
					case 'membres' :
						$admin->users_membres_action();
						$admin->users_membres();
						break;
					case 'groupes' :
						$admin->users_groupes();
						break;
					case 'modif_groupe' :
						$admin->users_modif_groupe();
						break;
					case 'modif_user' :
						$admin->users_modif_user();
						break;
					case 'images' :
						$admin->users_images_action();
						$admin->users_images();
						break;
					default :
						header('Location: index.php?section=utilisateurs&page=general');
						exit;
				}
			} else {
				header('Location: index.php?section=utilisateurs&page=general');
				exit;
			}
			break;
		case 'commentaires' :
			if (isset($_REQUEST['page'])) {
				switch ($_REQUEST['page']) {
					case 'display' :
						$admin->comments_action();
						$admin->comments();
						break;
					case 'options' :
						$admin->comments_options();
						break;
					case 'bans' :
						$admin->comments_bans();
						break;
					default :
						header('Location: index.php?section=commentaires&page=display');
						exit;
				}
			} else {
				header('Location: index.php?section=commentaires&page=display');
				exit;
			}
			break;
		case 'options' :
			if (isset($_REQUEST['page'])) {
				switch ($_REQUEST['page']) {
					case 'general' :
						$admin->options_general();
						break;
					case 'images' :
						$admin->options_images();
						break;
					case 'itext_params' :
						$admin->options_itext();
						break;
					case 'infos_exif' :
						$admin->options_exif();
						break;
					case 'infos_iptc' :
						$admin->options_iptc();
						break;
					case 'vignettes' :
						$admin->options_vignettes();
						break;
					case 'textes' :
						$admin->options_textes();
						break;
					case 'fonctions' :
						$admin->options_fonctions();
						break;
					case 'perso' :
						$admin->options_perso();
						break;
					default :
						header('Location: index.php?section=options&page=general');
						exit;
				}
			} else {
				header('Location: index.php?section=options&page=general');
				exit;
			}
			break;
		case 'infos' :
			$admin->infos();
			break;
		case 'outils' :
			if (isset($_REQUEST['page'])) {
				switch ($_REQUEST['page']) {
					case 'images' :
						$admin->outils_images();
						break;
					default :
						header('Location: index.php?section=outils&page=images');
						exit;
				}
			} else {
				header('Location: index.php?section=outils&page=images');
				exit;
			}
			break;
			break;
		case 'config' :
			if (isset($_REQUEST['page'])) {
				switch ($_REQUEST['page']) {
					case 'conf' :
						$admin->config_conf();
						break;
					case 'infos_sys' :
						$admin->config_infosys();
						break;
					default :
						header('Location: index.php?section=config&page=conf');
						exit;
				}
			} else {
				header('Location: index.php?section=config&page=conf');
				exit;
			}
			break;
		default :
			header('Location: index.php?section=galerie&page=gestion');
			exit;
	}

	// Fermeture de la connexion.
	$admin->mysql->fermer();

	// On démmarre la classe de template.
	$tpl = new template($admin->template);

	// On appelle le template.
	require('template/' . $admin->config['admin_template']  . '/index.php');

// ...sinon on redirige vers la section 'albums'.
} else {
	header('Location: index.php?section=galerie&page=gestion');
}

/*
 * ========== class.admin
*/

class admin {

	var $config;	// Configuration générale de la galerie.
	var $template;	// Informations brutes à destination des fonctions de template.
	var $mysql;		// Connexion MySQL.
	var $galerie_dir; // Chemin relatif du répertoire des albums;


	/*
	 *	Constructeur.
	*/
	function admin() {

		// Connexion à la base de données.
		$this->mysql = new connexion(MYSQL_SERV, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);

		// Récupération des paramètres de configuration.
		$mysql_requete = 'SELECT parametre,valeur FROM ' . MYSQL_PREF . 'config';
		$this->config = $this->mysql->select($mysql_requete, 3);
		if (empty($this->config)) {
			die ('La base de données est vide.<br />' . mysql_error());
		}

		// Si l'identifiant de session du cookie est incorrect, on renvoie vers la console de connexion. 
		$session = new cookie(0, 'galerie_sessionid', GALERIE_PATH . '/' . basename(dirname(__FILE__)));
		$session_id = $session->lire('session_id');
		$this->config['session_id'] = $session_id;
		if (empty($session_id)) {
			header('Location: connexion.php?t=cookie');
			exit;		
		}
		if ($session_id != $this->config['admin_session_id']) {
			header('Location: connexion.php?t=session');
			exit;
		}

		// Si l'identifiant de session a expiré, on renvoie vers la console de connexion.
		if (time() > $this->config['admin_session_expire']) {
			header('Location: connexion.php?t=expire');
			exit;
		}

		// On génère un nouveau VID.
		$this->template['new_vid'] = outils::gen_key(32);
		$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'config
							 SET valeur = "' . $this->template['new_vid'] . '"
						   WHERE parametre = "admin_vid"';
		if (!$this->mysql->requete($mysql_requete)) {
			die('Impossible de générer le VID.');
			exit;
		}

		// Vérification du VID dans les formulaires.
		if (!empty($_POST)) {
			if (empty($this->config['admin_vid']) || empty($_POST['igalvid'])
			 || $_POST['igalvid'] != $this->config['admin_vid']) {
				$params = '';
				$params .= (isset($_REQUEST['section'])) ? '?section=' . $_REQUEST['section'] : '';
				$params .= (isset($_REQUEST['page'])) ? '&page=' . $_REQUEST['page'] : '';
				$params .= (isset($_REQUEST['cat'])) ? '&cat=' . $_REQUEST['cat'] : '';
				$params .= (isset($_REQUEST['groupe'])) ? '&groupe=' . $_REQUEST['groupe'] : '';
				$params .= (isset($_REQUEST['user'])) ? '&user=' . $_REQUEST['user'] : '';
				header('Location: index.php' . $params);
				exit;
			}
		}

		// Deconnexion.
		if (isset($_GET['igal_admin_deconnect'])) {
			$this->verifVID();
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'config SET valeur = "" 
				WHERE parametre = "admin_session_id"';
			$this->mysql->requete($mysql_requete);
			header('Location: connexion.php');
			exit;
		}

		// Activation/désactivation de la galerie.
		if (!empty($_REQUEST['gal_desactive'])) {
			$this->verifVID();
			$this->update_option('active_galerie', 0);
			header('Location:index.php?' . preg_replace('`&gal_desactive=\d`', '', $_SERVER['QUERY_STRING']));
		}
		if (!empty($_REQUEST['gal_active'])) {
			$this->verifVID();
			$this->update_option('active_galerie', 1);
			header('Location:index.php?' . preg_replace('`&gal_active=\d`', '', $_SERVER['QUERY_STRING']));
		}

		$this->config['admin_template'] = 'defaut';

		switch ($_REQUEST['section']) {
			case 'ftp' :
				$this->template['infos']['title'] = 'upload FTP';
				break;
		}

		$this->galerie_dir = dirname(dirname(__FILE__)) . '/' . GALERIE_ALBUMS . '/';

		// Configuration.
		$this->template['config'] = $this->config;
		$this->template['config']['galerie_url'] = GALERIE_URL;
		$this->config['admin_comment_ban'] = unserialize($this->config['admin_comment_ban']);
		$this->config['img_resize_gd'] = IMG_RESIZE_GD;
		$this->config['galerie_template'] = GALERIE_THEME;
		$this->config['galerie_style'] = GALERIE_STYLE;
		$this->config['galerie_url_type'] = GALERIE_URL_TYPE;
		$this->config['galerie_url'] = GALERIE_URL;
		$this->config['galerie_path'] = GALERIE_PATH;
		$this->config['galerie_integrated'] = GALERIE_INTEGRATED;
		include(dirname(__FILE__) . '/../template/' . GALERIE_THEME . '/_fonctions.php');
		$this->template['enabled'] = $f;

	}



	/*
	  *	Vérification de l'identifiant de session pour chaque action sur la galerie.
	*/
	function verifVID() {
		if (empty($this->config['admin_vid']) || empty($_GET['igalvid'])
		 || $_GET['igalvid'] != $this->config['admin_vid']) {
			$params = '';
			$params .= (isset($_REQUEST['section'])) ? '?section=' . $_REQUEST['section'] : '';
			$params .= (isset($_REQUEST['page'])) ? '&page=' . $_REQUEST['page'] : '';
			$params .= (isset($_REQUEST['cat'])) ? '&cat=' . $_REQUEST['cat'] : '';
			$params .= (isset($_REQUEST['groupe'])) ? '&groupe=' . $_REQUEST['groupe'] : '';
			$params .= (isset($_REQUEST['user'])) ? '&user=' . $_REQUEST['user'] : '';
			header('Location: index.php' . $params);
			exit;
		}
	}



	/*
	 *	UPDATE des options.
	*/
	function update_option($option, $valeur, $r = 1) {
		$valeur_mysql = outils::protege_mysql($valeur, $this->mysql->lien);
		$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'config SET valeur = "' . $valeur_mysql . '" 
			WHERE parametre = "' . $option . '"';
		if ($this->mysql->requete($mysql_requete)) {
			if ($r) {
				$this->config[$option] = $valeur;
			}
			return true;
		}
		return false;
	}



	/*
	 *	Fonction d'upload FTP.
	*/
	function ftp() {

		// Recherche et ajout à la base de données de nouvelles images et albums.
		$upload = new upload($this->mysql, $this->config);
		$upload->recup_albums();

		// Contrôle du temps d'exécution.
		if ($upload->arret) {
			$this->template['infos']['info']['ftp_trop'] = 'Il y a trop d\'images : cliquez à nouveau sur le bouton pour scanner les répertoires restants...';
		}

		// Rapport.
		$rapport_resume = '';
		$rapport_details = '';

		// Erreurs.
		if ($upload->rapport['erreurs']) {
			if (count($upload->rapport['erreurs']) > 1) {
				$upload_verbes = ' se sont';
				$s = 's';
			} else {
				$upload_verbes = ' s\'est';
				$s = '';
			}
			$rapport_resume .= '<div class="rapport_msg rapport_erreur"><div><span>' . count($upload->rapport['erreurs']) . ' erreur' . $s . $upload_verbes . ' produite' . $s . '.</span></div></div>' . "\n";
		}

		// Ajouts.
		if ((count($upload->rapport['alb_ajouts']) + $upload->rapport['img_ajouts']) > 0) {
			$e = ($upload->rapport['alb_ajouts']) ? '' : 'e';
			$ajout_verbes = ((count($upload->rapport['alb_ajouts']) + $upload->rapport['img_ajouts']) > 1) ? ' ont été ajouté' . $e . 's' : ' a été ajouté' . $e;
			$et = ($upload->rapport['alb_ajouts']) ? ' et ' : '';
			$as = (count($upload->rapport['alb_ajouts']) > 1) ? 's' : '';
			$is = ($upload->rapport['img_ajouts'] > 1) ? 's' : '';
			$albs = ($upload->rapport['alb_ajouts']) ? count($upload->rapport['alb_ajouts']) . ' album' . $as : '';
			$imgs = ($upload->rapport['img_ajouts']) ? $et . $upload->rapport['img_ajouts'] . ' image' . $is : '';
			$rapport_resume .= '<div class="rapport_msg rapport_succes"><div><span>' . $albs . $imgs . $ajout_verbes . ' à la base de données.</span></div></div>' . "\n";
		} else {
			$rapport_resume .= '<div class="rapport_msg rapport_infos"><div><span>Aucun nouvel album et aucune nouvelle image n\'a été détecté.</span></div></div>' . "\n";
		}

		// Mises à jour.
		if ($upload->rapport['alb_maj']) {
			if (count($upload->rapport['alb_maj']) > 1) {
				$upload_verbes = ' ont';
				$s = 's';
			} else {
				$upload_verbes = ' a';
				$s = '';
			}
			$rapport_resume .= '<div class="rapport_msg rapport_succes"><div><span>' . count($upload->rapport['alb_maj']) . ' album' . $s . $upload_verbes . ' été mis à jour.</span></div></div>' . "\n";
		} else {
			$rapport_resume .= '<div class="rapport_msg rapport_infos"><div><span>Aucun album n\'a été mis à jour.</span></div></div>' . "\n";
		}

		// Update Exif.
		if ($upload->images_update_exif) {
			$s = ($upload->images_update_exif > 1) ? 's' : '';
			$rapport_resume .= '<div class="rapport_msg rapport_succes"><div><span>Les meta-données Exif ont été ajoutées pour ' . $upload->images_update_exif . ' image' . $s . '.</span></div></div>' . "\n";
		}

		// Rejets.
		if ((count($upload->rapport['cat_rejets']) + count($upload->rapport['img_rejets'])) > 0) {
			$e = ($upload->rapport['cat_rejets']) ? '' : 'e';
			$ajout_verbes = ((count($upload->rapport['cat_rejets']) + count($upload->rapport['img_rejets'])) > 1) ? ' ont été rejeté' . $e . 's' : ' a été rejeté' . $e;
			$et = ($upload->rapport['cat_rejets']) ? ' et ' : '';
			$as = ($upload->rapport['cat_rejets'] > 1) ? 's' : '';
			$is = (count($upload->rapport['img_rejets']) > 1) ? 's' : '';
			$albs = ($upload->rapport['cat_rejets']) ? count($upload->rapport['cat_rejets']) . ' catégorie' . $as . ' ou album' . $as : '';
			$imgs = ($upload->rapport['img_rejets']) ? $et . count($upload->rapport['img_rejets']) . ' image' . $is : '';
			$rapport_resume .= '<div class="rapport_msg rapport_avert"><div><span>' . $albs . $imgs . $ajout_verbes . '.</span></div></div>' . "\n";
		}


		// Détails du rapport.
		if ($upload->rapport['erreurs']) {
			$rapport_details .= '<table id="ftp_details_erreur" class="ftp_details">' . "\n" . '<tr><th>Objet</th><th>Erreur</th></tr>' . "\n";
			foreach ($upload->rapport['erreurs'] as $v) {
				$rapport_details .= '<tr><td>' . wordwrap($v[0], 50, '<br />', 1) . '</td><td>' . $v[1] . '</td></tr>';
			}
			$rapport_details .= '</table><br />' . "\n";
		}
		if ($upload->rapport['alb_ajouts']) {
			$rapport_details .= '<table class="ftp_details">' . "\n" . '<tr><th>Album ajouté</th><th>Nb. d\'images</th><th>Poids</th></tr>' . "\n";
			foreach ($upload->rapport['alb_ajouts'] as $v) {
				$rapport_details .= '<tr><td>' . wordwrap($v[0], 50, '<br />', 1) . '</td><td>' . $v[1] . '</td><td>' . $v[2] . '</td></tr>';
			}
			$rapport_details .= '</table><br />' . "\n";
		}
		if ($upload->rapport['alb_maj']) {
			$rapport_details .= '<table class="ftp_details">' . "\n" . '<tr><th>Album mis à jour</th><th>Nb. d\'images supp.</th><th>Poids supp.</th></tr>' . "\n";
			foreach ($upload->rapport['alb_maj'] as $v) {
				$rapport_details .= '<tr><td>' . wordwrap($v[0], 50, '<br />', 1) . '</td><td>' . $v[1] . '</td><td>' . $v[2] . '</td></tr>';
			}
			$rapport_details .= '</table><br />' . "\n";
		}
		if ($upload->rapport['cat_rejets']) {
			$rapport_details .= '<table class="ftp_details">' . "\n" . '<tr><th>Catégorie rejetée</th><th>Cause</th></tr>' . "\n";
			foreach ($upload->rapport['cat_rejets'] as $v) {
				$rapport_details .= '<tr><td>' . wordwrap($v[0], 50, '<br />', 1) . '</td><td>' . $v[1] . '</td></tr>';
			}
			$rapport_details .= '</table><br />' . "\n";
		}
		if ($upload->rapport['img_rejets']) {
			$rapport_details .= '<table class="ftp_details">' . "\n" . '<tr><th>Image rejetée</th><th>Album</th><th>Cause</th></tr>' . "\n";
			foreach ($upload->rapport['img_rejets'] as $v) {
				$rapport_details .= '<tr><td>' . wordwrap($v[0], 50, '<br />', 1) . '</td><td>' . wordwrap($v[1], 50, '<br />', 1) . '</td><td>' . $v[2] . '</td></tr>';
			}
			$rapport_details .= '</table><br />' . "\n";
		}

		// On ajoute le rapport finalisé au tableau destiné au template.
		$this->template['rapport'] = '<div id="ftp_rapport_resume"><span class="ftp_rapport_titre">Rapport résumé</span><br /><br />' . $rapport_resume . '</div>' . "\n";
		if (!empty($rapport_details)) {
			$this->template['rapport'] .= '<p id="ftp_rapport_details"><span class="ftp_rapport_titre">Rapport détaillé</span></p>' . "\n";
			$this->template['rapport'] .= $rapport_details;
		}

		// Template.
		$this->template['config'] = $this->config;
	}



	/*
	 *	Désactivation d'un commentaire.
	*/
	function comment_action_desactive() {
		$mysql_requete = 'SELECT commentaire_visible FROM ' . MYSQL_PREF . 'commentaires 
			WHERE commentaire_id = "' . $_REQUEST['desactive'] . '"';
		$visible = $this->mysql->select($mysql_requete, 5);
		if (!empty($visible)) {
			$this->comments_update_nb('desactive', 0, '-');
			$this->template['action_maj'] = 1;
		}
	}



	/*
	 *	Désactivation d'un commentaire.
	*/
	function comment_action_active() {
		$mysql_requete = 'SELECT commentaire_visible FROM ' . MYSQL_PREF . 'commentaires 
			WHERE commentaire_id = "' . $_REQUEST['active'] . '"';
		$visible = $this->mysql->select($mysql_requete, 5);
		if (empty($visible)) {
			$this->comments_update_nb('active', 1, '+');
			$this->template['action_maj'] = 1;
		}
	}



	/*
	 *	Suppression d'un commentaire.
	*/
	function comment_action_delete($mass = 0) {

		static $ids_nb = 0;
		static $ids;
		$ids = $ids . ', ' . $_REQUEST['supprime'];
		$ids_nb++;

		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_chemin,
								 ' . MYSQL_PREF . 'commentaires.commentaire_visible 
							FROM ' . MYSQL_PREF . 'commentaires INNER JOIN ' . MYSQL_PREF . 'images USING(image_id)
						   WHERE ' . MYSQL_PREF . 'commentaires.commentaire_id = "' . $_REQUEST['supprime'] . '"';
		$i_co = $this->mysql->select($mysql_requete, 11);
		$path = $i_co['image_chemin'];

		// On vérifie si le commentaire existe.
		if ($i_co == 'vide') {
			return;
		}

		// On supprime le commentaire.
		$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'commentaires 
			WHERE commentaire_id = "' . $_REQUEST['supprime'] . '"';
		if ($this->mysql->requete($mysql_requete)) {

			// On UPDATE le nombre de commentaires de l'image et des catégories parentes,
			// mais seulement si le commentaire n'est pas désactivé !
			if ($i_co['commentaire_visible']) {
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET 
						image_commentaires = image_commentaires - 1 
					WHERE image_chemin = "' . $path . '"';
				$this->mysql->requete($mysql_requete);

				while ($path != '.') {
					$path = dirname($path);
					$path = ($path == '.') ? $path : $path . '/' ;
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET 
							categorie_commentaires = categorie_commentaires - 1 
						WHERE categorie_chemin = "' . $path . '"';
					$this->mysql->requete($mysql_requete);
				}
			}
			$comment_ids = substr($ids, 2);
			if (strstr($comment_ids, ',')) {
				$this->template['infos']['action']['co_supprime'] = 'Les commentaires ' . preg_replace('`, (\d+)$`', ' et $1', $comment_ids) . ' ont été supprimés.';
			} else {
				$this->template['infos']['action']['co_supprime'] = 'Le commentaire ' .  $_REQUEST['supprime'] . ' a été supprimé.';
			}
			$_REQUEST['startnum'] = 0;
		}
	}



	/*
	 *	Bannissement d'un auteur.
	*/
	function comments_action_ban_auteur() {
		$mysql_requete = 'SELECT commentaire_auteur FROM ' . MYSQL_PREF . 'commentaires 
			WHERE commentaire_id = "' . $_REQUEST['ban_auteur'] . '"';
		$auteur = $this->mysql->select($mysql_requete, 5);

		$this->config['admin_comment_ban']['auteurs'][$auteur] = 1;
		$this->template['action_maj'] = 1;

		$bans = $this->config['admin_comment_ban'];
		$this->update_option('admin_comment_ban', serialize($bans), 0);
	}



	/*
	 *	Unban d'un auteur.
	*/
	function comments_action_unban_auteur() {
		$mysql_requete = 'SELECT commentaire_auteur FROM ' . MYSQL_PREF . 'commentaires 
			WHERE commentaire_id = "' . $_REQUEST['unban_auteur'] . '"';
		$auteur = $this->mysql->select($mysql_requete, 5);

		if (isset($this->config['admin_comment_ban']['auteurs'][$auteur])) {
			unset($this->config['admin_comment_ban']['auteurs'][$auteur]);
		}
		$this->template['action_maj'] = 1;

		$bans = $this->config['admin_comment_ban'];
		$this->update_option('admin_comment_ban', serialize($bans), 0);
	}



	/*
	 *	Bannissement d'une IP.
	*/
	function comments_action_ban_ip() {
		$mysql_requete = 'SELECT commentaire_ip FROM ' . MYSQL_PREF . 'commentaires 
			WHERE commentaire_id = "' . $_REQUEST['ban_ip'] . '"';
		$IP = $this->mysql->select($mysql_requete, 5);

		$this->config['admin_comment_ban']['IP'][$IP] = 1;
		//$this->template['infos']['action']['co_rb_ip'] = 'L\'IP ' . $IP . ' a été bannie.';
		$this->template['action_maj'] = 1;

		$bans = $this->config['admin_comment_ban'];
		$this->update_option('admin_comment_ban', serialize($bans), 0);
	}



	/*
	 *	Unban d'une IP.
	*/
	function comments_action_unban_ip() {
		$mysql_requete = 'SELECT commentaire_ip FROM ' . MYSQL_PREF . 'commentaires 
			WHERE commentaire_id = "' . $_REQUEST['unban_ip'] . '"';
		$IP = $this->mysql->select($mysql_requete, 5);

		if (isset($this->config['admin_comment_ban']['IP'][$IP])) {
			unset($this->config['admin_comment_ban']['IP'][$IP]);
		}
		//$this->template['infos']['action']['co_ru_ip'] = 'Les commentaires en provenance de l\'IP ' . $IP . ' sont de nouveau autorisés.';
		$this->template['action_maj'] = 1;

		$bans = $this->config['admin_comment_ban'];
		$this->update_option('admin_comment_ban', serialize($bans), 0);
	}



	/*
	 *	Action sur les commentaires, les auteurs et les IP.
	*/
	function comments_action() {

		// Traitement par lot.
		if (isset($_GET['mass']) && isset($_POST['co_mass_action']) && isset($_POST['comment_id'])) {
			foreach ($_POST['comment_id'] as $id => $e) {
				switch ($_POST['co_mass_action']) {
					case 'desactiver' :
						$_REQUEST['desactive'] = $id;
						$this->comment_action_desactive();
						break;
					case 'activer' :
						$_REQUEST['active'] = $id;
						$this->comment_action_active();
						break;
					case 'supprimer' :
						$_REQUEST['supprime'] = $id;
						$this->comment_action_delete();
						break;
					case 'ban_auteurs' :
						$_REQUEST['ban_auteur'] = $id;
						$this->comments_action_ban_auteur();
						break;
					case 'aut_auteurs' :
						$_REQUEST['unban_auteur'] = $id;
						$this->comments_action_unban_auteur();
						break;
					case 'ban_ip' :
						$_REQUEST['ban_ip'] = $id;
						$this->comments_action_ban_ip();
						break;
					case 'aut_ip' :
						$_REQUEST['unban_ip'] = $id;
						$this->comments_action_unban_ip();
						break;
				}
			}
			unset($_REQUEST['desactive']);
			unset($_REQUEST['active']);
			unset($_REQUEST['supprime']);
			unset($_REQUEST['ban_auteur']);
			unset($_REQUEST['unban_auteur']);
			unset($_REQUEST['ban_ip']);
			unset($_REQUEST['unban_ip']);
		}

		// Désactivation du commentaire.
		if (isset($_REQUEST['desactive']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['desactive'])) {
			$this->verifVID();
			$this->comment_action_desactive();

		// Activation du commentaire.
		} elseif (isset($_REQUEST['active']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['active'])) {
			$this->verifVID();
			$this->comment_action_active();

		// Suppression du commentaire.
		} elseif (isset($_REQUEST['supprime']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['supprime'])) {
			$this->verifVID();
			$this->comment_action_delete();

		// Bannissement d'un auteur.
		} elseif (isset($_REQUEST['ban_auteur']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['ban_auteur'])) {
			$this->verifVID();
			$this->comments_action_ban_auteur();

		// Unban d'un auteur.
		} elseif (isset($_REQUEST['unban_auteur']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['unban_auteur'])) {
			$this->verifVID();
			$this->comments_action_unban_auteur();

		// Bannissement d'une IP.
		} elseif (isset($_REQUEST['ban_ip']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['ban_ip'])) {
			$this->verifVID();
			$this->comments_action_ban_ip();

		// Unban d'une IP.
		} elseif (isset($_REQUEST['unban_ip']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['unban_ip'])) {
			$this->verifVID();
			$this->comments_action_unban_ip();
		}
	}



	/*
	 *	UPDATE des informations sur l'activation/désactivation d'un commentaire.
	*/
	function comments_update_nb($e, $n, $i) {

		// On UPDATE l'état 'visible' du commentaire.
		$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'commentaires SET 
			commentaire_visible = "' . $n . '" 
			WHERE commentaire_id = "' . $_REQUEST[$e] . '"';
		if ($this->mysql->requete($mysql_requete)) {

			// On UPDATE le nombre de commentaires de l'image et des catégories parentes.
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_chemin
								FROM ' . MYSQL_PREF . 'commentaires INNER JOIN ' . MYSQL_PREF . 'images USING(image_id)
							   WHERE ' . MYSQL_PREF . 'commentaires.commentaire_id = "' . $_REQUEST[$e] . '"';
			$path = $this->mysql->select($mysql_requete, 5);

			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET 
					image_commentaires = image_commentaires ' . $i . ' 1 
				WHERE image_chemin = "' . $path . '"';
			$this->mysql->requete($mysql_requete);

			while ($path != '.') {
				$path = dirname($path);
				$path = ($path == '.') ? $path : $path . '/' ;
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET 
						categorie_commentaires = categorie_commentaires ' . $i . ' 1 
					WHERE categorie_chemin = "' . $path . '"';
				$this->mysql->requete($mysql_requete);
			}
			
		}
	}



	/*
	 *	Affichage des commentaires.
	*/
	function comments() {

		$this->template['infos']['title'] = 'gestion des commentaires';

		// Trie des commentaires : ordre.
		if (isset($_REQUEST['sort']) && preg_match('`^commentaire_(auteur|date|mail|ip|web)|image_chemin$`', $_REQUEST['sort']) && $_REQUEST['sort'] != $this->config['admin_comment_ordre']) {
			$this->template['infos']['comment_sort'] = $_REQUEST['sort'];
			$this->update_option('admin_comment_ordre', $_REQUEST['sort']);
		} else {
			$this->template['infos']['comment_sort'] = $this->config['admin_comment_ordre'];
		}

		// Trie des commentaires : sens.
		if (isset($_REQUEST['sens']) && preg_match('`^ASC|DESC$`', $_REQUEST['sens']) && $_REQUEST['sens'] != $this->config['admin_comment_sens']) {
			$this->template['infos']['comment_sens'] = $_REQUEST['sens'];
			$this->update_option('admin_comment_sens', $_REQUEST['sens']);
		} else {
			$this->template['infos']['comment_sens'] = $this->config['admin_comment_sens'];
		}

		// Page à afficher.
		if (isset($_REQUEST['startnum']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['startnum'])) {
			$startnum = $_REQUEST['startnum'];
		} else {
			$startnum = 0;
		}

		// Filtre.
		$filtre = $this->config['admin_comment_filtre'];
		if (isset($_REQUEST['filtre']) && preg_match('`^(tous|actif|inactif)$`', $_REQUEST['filtre']) && $filtre != $_REQUEST['filtre']) {
			$this->update_option('admin_comment_filtre', $_REQUEST['filtre']);
			$filtre = $_REQUEST['filtre'];
			$startnum = 0;
		}
		$this->template['infos']['comment_filtre'] = $filtre;
		switch ($filtre) {
			case 'actif' :
				$filtre = ' AND ' . MYSQL_PREF . 'commentaires.commentaire_visible = "1"';
				break;
			case 'inactif' :
				$filtre = ' AND ' . MYSQL_PREF . 'commentaires.commentaire_visible = "0"';
				break;
			default :
				$filtre = '';
		}

		// Nombre de commentaires par page.
		if (isset($_REQUEST['nb']) && preg_match('`^[1-9]\d{0,3}$`', $_REQUEST['nb']) && $_REQUEST['nb'] != $this->config['admin_comment_nb']) {
			$this->update_option('admin_comment_nb', $_REQUEST['nb']);
			$this->config['admin_comment_nb'] = $_REQUEST['nb'];
			$this->template['infos']['nb_comments'] = $_REQUEST['nb'];
			$startnum = 0;
		} else {
			$this->template['infos']['nb_comments'] = $this->config['admin_comment_nb'];
		}

		$this->template['infos']['startnum'] = $startnum;

		// Objet actuel.
		if (isset($_REQUEST['cat']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['cat'])) {
			$obj = $_REQUEST['cat'];
			$obj_type = 'cat';
			$obj_type_ext = 'categorie';
		} elseif (isset($_REQUEST['img']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['img'])) {
			$obj = $_REQUEST['img'];
			$obj_type = 'img';
			$obj_type_ext = 'image';
		} else {
			$obj = 1;
			$obj_type = 'cat';
			$obj_type_ext = 'categorie';
		}
		$this->template['infos']['section'] = 'section=commentaires&page=display&' . $obj_type . '=' . $obj;
		$this->template['infos']['obj'] = $obj;
		$this->template['infos']['obj_type'] = $obj_type;

		$mysql_requete = 'SELECT ' . $obj_type_ext . '_chemin,
								 ' . $obj_type_ext . '_nom 
							FROM ' . MYSQL_PREF . $obj_type_ext . 's 
						   WHERE ' . $obj_type_ext . '_id = "' . $obj . '"';
		$obj = $this->mysql->select($mysql_requete, 11);
		if (!is_array($obj)) {
			header('Location:index.php?section=commentaires&page=display');
			exit;
		}
		$obj_chemin = ($obj[$obj_type_ext . '_chemin'] == '.') ? '' : $obj[$obj_type_ext . '_chemin'];

		// Recherche.
		$this->template['infos']['recherche'] = '';
		$recherche = '';
		if (isset($_REQUEST['search'])) {

			if (isset($_REQUEST['s_accents'])) {
				$search_inputs['s_accents'] = '1';
				$this->template['infos']['recherche'] .= '&s_accents=on';
			}
			if (isset($_REQUEST['s_casse'])) {
				$search_inputs['s_casse'] = '1';
				$this->template['infos']['recherche'] .= '&s_casse=on';
			}
			if (isset($_REQUEST['s_tous'])) {
				$search_inputs['s_tous'] = '1';
				$this->template['infos']['recherche'] .= '&s_tous=on';
			}
			if (isset($_REQUEST['s_msg'])) {
				$search_inputs['s_msg'] = '1';
				$this->template['infos']['recherche'] .= '&s_msg=on';
			}
			if (isset($_REQUEST['s_auteur'])) {
				$search_inputs['s_auteur'] = '1';
				$this->template['infos']['recherche'] .= '&s_auteur=on';
			}
			if (isset($_REQUEST['s_ip'])) {
				$search_inputs['s_ip'] = '1';
				$this->template['infos']['recherche'] .= '&s_ip=on';
			}
			if (isset($_REQUEST['s_mail'])) {
				$search_inputs['s_mail'] = '1';
				$this->template['infos']['recherche'] .= '&s_mail=on';
			}
			if (isset($_REQUEST['s_web'])) {
				$search_inputs['s_web'] = '1';
				$this->template['infos']['recherche'] .= '&s_web=on';
			}

			// On affiche les messages des commentaires.
			$this->template['display']['comment_msg'] = 1;
		
			$search_inputs['search'] = outils::html_specialchars($_REQUEST['search']);
			$this->template['infos']['recherche'] .= '&search=' . urlencode($_REQUEST['search']);

			// Récupération de la requête.
			$search = trim($_REQUEST['search']);
			$search = preg_replace('`-+`', '-', $search);
			$search = str_replace('- ', '', $search);
			$search = str_replace(' *', '', $search);
			$search = preg_replace('`\s+`', ' ', $search);

			// Méthodes « AND » ou « OR ».
			$method = (isset($_REQUEST['s_tous'])) ? 'AND' : 'OR';

			// Casse.
			$binary = (isset($_REQUEST['s_casse'])) ? 'BINARY ' : '';

			// Si ni la casse ni les accents comptent, on concertit tout en minuscule.
			if (!isset($_REQUEST['s_casse']) && !isset($_REQUEST['s_accents'])) {
				$search = strtolower($search);
			}

			// Paramètres de recherche.
			$search = preg_split('`\s+(?!.*[^-\s]")`i', $search, -1, PREG_SPLIT_NO_EMPTY);
			$champs = array();
			$champs['message'] = '';
			$champs['auteur'] = '';
			$champs['ip'] = '';
			$champs['mail'] = '';
			$champs['web'] = '';
			for ($i = 0; $i < count($search); $i++) {

				// Suppression des guillemets.
				$terme = str_replace('"', '', $search[$i]);

				// Remplacement des espace par une suite de caractères non-alpha-numérique.
				$terme = preg_replace('`[^-\w\*\?\'\s]+`', '?', $terme);

				// Doit-on ne pas faire de distinction pour les lettres accentuées ?
				$terme = (isset($_REQUEST['s_accents'])) ? $terme : outils::regexp_accents($terme);

				// Doit-on inclure ou exclure le terme ?
				$terme = ($search[$i]{0} == '-') ? substr($terme, 1) : $terme;
				$not = ($search[$i]{0} == '-') ? 'NOT ' : '';

				// Si le terme est vide, inutile d'aller plus loin.
				if (trim($terme) == '') {
					continue;
				}

				// Remplacement des espace par une suite de caractères non-alpha-numérique.
				$terme = str_replace(' ', '[^[:alnum:]]', $terme);

				// Joker « * ».
				$terme = str_replace('*', '[^[:space:]]*', $terme);

				// Joker « ? ».
				$terme = str_replace('?', '.', $terme);

				// On ne recherche que des mots entiers.
				$terme = '([^[:alnum:]]|^)' . $terme . '([^[:alnum:]]|$)';

				// Champ message.
				if (isset($_REQUEST['s_msg'])) {
					$champs['message'] .= $method . ' ' . MYSQL_PREF . 'commentaires.commentaire_message ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $this->mysql->lien) . '" ';
				}

				// Champ auteur.
				if (isset($_REQUEST['s_auteur'])) {
					$champs['auteur'] .= $method . ' ' . MYSQL_PREF . 'commentaires.commentaire_auteur ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $this->mysql->lien) . '" ';
				}

				// Champ ip.
				if (isset($_REQUEST['s_ip'])) {
					$champs['ip'] .= $method . ' ' . MYSQL_PREF . 'commentaires.commentaire_ip ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $this->mysql->lien) . '" ';
				}

				// Champ mail.
				if (isset($_REQUEST['s_mail'])) {
					$champs['mail'] .= $method . ' ' . MYSQL_PREF . 'commentaires.commentaire_mail ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $this->mysql->lien) . '" ';
				}

				// Champ web.
				if (isset($_REQUEST['s_web'])) {
					$champs['web'] .= $method . ' ' . MYSQL_PREF . 'commentaires.commentaire_web ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $this->mysql->lien) . '" ';
				}
			}

			// Champs de la table dans lesquels la recherche s'effectuera.
			$commentaires_champs = '';
			foreach ($champs as $v) {
				if ($v) {
					$commentaires_champs .= 'OR (' . preg_replace('`^(?:AND|OR) `', '', $v) . ') ';
				}
			}
			if ($commentaires_champs) {
				$commentaires_champs = '(' . preg_replace('`^OR `', '', $commentaires_champs) . ')';
			}

			// Date
			$commentaires_date = '';
			if (isset($_REQUEST['s_date']) && isset($_REQUEST['s_dnpc']) && isset($_REQUEST['s_dnpd'])
			 && isset($_REQUEST['s_dnsc']) && isset($_REQUEST['s_dnsd'])) {
				$search_inputs['s_date'] = $_REQUEST['s_date'];
				$search_inputs['s_dnpc'] = $_REQUEST['s_dnpc'];
				$search_inputs['s_dnpd'] = $_REQUEST['s_dnpd'];
				$search_inputs['s_dnsc'] = $_REQUEST['s_dnsc'];
				$search_inputs['s_dnsd'] = $_REQUEST['s_dnsd'];
				$this->template['infos']['recherche'] .= '&s_date=on&s_dnpc=' . $_REQUEST['s_dnpc'];
				$this->template['infos']['recherche'] .= '&s_dnpd=' . $_REQUEST['s_dnpd'];
				$this->template['infos']['recherche'] .= '&s_dnsc=' . $_REQUEST['s_dnsc'];
				$this->template['infos']['recherche'] .= '&s_dnsd=' . $_REQUEST['s_dnsd'];
				$commentaires_date .= '' . MYSQL_PREF . 'commentaires.commentaire_date < ' . outils::time_date($_REQUEST['s_dnpc'], $_REQUEST['s_dnpd']) 
					    . ' AND ' . MYSQL_PREF . 'commentaires.commentaire_date > ' . outils::time_date($_REQUEST['s_dnsc'], $_REQUEST['s_dnsd']);
			}

			if ($commentaires_champs || $commentaires_date) {
				$and = ($commentaires_champs && $commentaires_date) ? ' AND ' : '';
				$recherche = ' AND (' . $commentaires_champs . $and . $commentaires_date . ')';
			}

			$this->template['comments']['search'] = $search_inputs;
		}

		// Sous-objets.
		$mysql_requete = 'SELECT categorie_id,categorie_nom,categorie_chemin FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_chemin REGEXP "^' . $obj_chemin . '[^/]+/$" 
			  AND categorie_id > 1
			  AND (categorie_commentaires > 0 OR categorie_commentaires_inactive > 0)';
		$sub_cat = $this->mysql->select($mysql_requete);
		if (is_array($sub_cat)) {
			for ($i = 0; $i < count($sub_cat); $i++) {
				$this->template['comments']['sub_item'][$i] = $sub_cat[$i];
				$this->template['display']['subcats'] = 1;
				$this->template['infos']['sub_objects'] = 'categorie';
				
			}
		} else {
			$mysql_requete = 'SELECT image_id,image_nom,image_chemin FROM ' . MYSQL_PREF . 'images 
				WHERE image_chemin REGEXP "^' . $obj_chemin . '[^/]+$"
				  AND image_commentaires > 0';
			$imgs = $this->mysql->select($mysql_requete);
			if (is_array($imgs)) {
				for ($i = 0; $i < count($imgs); $i++) {
					$this->template['comments']['sub_item'][$i] = $imgs[$i];
					$this->template['display']['subcats'] = 1;
					$this->template['infos']['sub_objects'] = 'image';
				}
			}
		}

		// Barre de position.
		$this->template['comments']['position'] = '';
		if ($obj_chemin) {
			$parent = dirname($obj_chemin);
			while ($parent != '.') {
				$mysql_requete = 'SELECT categorie_id,categorie_chemin,categorie_nom 
									FROM ' . MYSQL_PREF . 'categories 
								   WHERE categorie_chemin = "' . $parent . '/"';
				$p_i = $this->mysql->select($mysql_requete, 11);
				$parent = dirname($p_i['categorie_chemin']);
				$this->template['comments']['position'] = '%sep<a href="index.php?section=commentaires&amp;page=display&amp;cat=' . 
					$p_i['categorie_id'] . htmlentities($this->template['infos']['recherche']) . '">' . 
					strip_tags($p_i['categorie_nom']) . '</a>' . 
					$this->template['comments']['position'];
			}
		}
		$pos_actuel = ($this->template['infos']['obj'] > 1) ? '' : ' class="pos_actuel"' ;
		$this->template['comments']['position'] = '<a' . $pos_actuel . ' href="index.php?section=commentaires&amp;page=display' . 
				htmlentities($this->template['infos']['recherche']) . '">galerie</a>' . $this->template['comments']['position'];
		if ($this->template['infos']['obj'] > 1) {
			$this->template['comments']['position'] .= '%sep<a href="index.php?section=commentaires&amp;page=display&amp;' . $obj_type . '=' . 
					$this->template['infos']['obj'] . htmlentities($this->template['infos']['recherche']) . 
					'" class="pos_actuel">' . 
					strip_tags($obj[$obj_type_ext . '_nom']) . '</a>';
		}

		// Récupération du nombre de commentaires de l'objet actuel.
		$mysql_requete = 'SELECT COUNT(*)
							FROM ' . MYSQL_PREF . 'commentaires INNER JOIN ' . MYSQL_PREF . 'images USING(image_id)
						   WHERE ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $obj_chemin . '%"' 
								   . $recherche . $filtre;
		if ($nc = $this->mysql->select($mysql_requete, 5)) {
			$this->template['infos']['nb_objets'] = $nc;
		} else {
			$this->template['infos']['nb_objets'] = 0;
		}

		// On détermine le nombre de pages et la page actuelle.
		$this->template['infos']['nb_pages'] = ceil(($this->template['infos']['nb_objets']) / $this->config['admin_comment_nb']);
		for ($n = 0; $n < $this->template['infos']['nb_pages']; $n++) {
			$num = $n * $this->config['admin_comment_nb'];
			$this->template['nav']['pages'][$n + 1]['page'] = $num;
			if ($num == $startnum) {
				$this->template['infos']['page_actuelle'] = $n + 1;
			}
		}

		// On détermine les pages suivantes, précédentes, de début et de fin.
		$this->template['nav']['suivante'][1] = $startnum + $this->config['admin_comment_nb'];
		$this->template['nav']['precedente'][1] = $startnum - $this->config['admin_comment_nb'];
		$this->template['nav']['premiere'][1] = 0;
		$this->template['nav']['derniere'][1] = ($this->template['infos']['nb_pages'] * $this->config['admin_comment_nb']) - $this->config['admin_comment_nb'];

		// On détermine la position de l'objet actuel.
		if ($startnum == 0) {
			$this->template['nav']['premiere'][0] = 1;
		}
		if ($this->template['nav']['precedente'][1] < 0) {
			$this->template['nav']['precedente'][0] = 1;
		}
		if ($this->template['nav']['suivante'][1] >= ($this->template['infos']['nb_pages'] * $this->config['admin_comment_nb']) || 
		    $this->template['nav']['suivante'][1] >= $this->template['infos']['nb_objets']) {
			$this->template['nav']['suivante'][0] = 1;
		}
		if ($startnum >= $this->template['nav']['derniere'][1]) {
			$this->template['nav']['derniere'][0] = 1;
		}

		// Récupération des informations des commentaires.
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'commentaires.*,
								 ' . MYSQL_PREF . 'categories.categorie_id,
								 ' . MYSQL_PREF . 'categories.categorie_nom,
								 ' . MYSQL_PREF . 'images.image_id,
								 ' . MYSQL_PREF . 'images.image_chemin,
								 ' . MYSQL_PREF . 'images.image_nom,
								 ' . MYSQL_PREF . 'images.image_hauteur,
								 ' . MYSQL_PREF . 'images.image_largeur,
								 ' . MYSQL_PREF . 'users.user_id,
								 ' . MYSQL_PREF . 'users.user_login,
								 ' . MYSQL_PREF . 'users.user_web,
								 ' . MYSQL_PREF . 'users.user_mail
							FROM ' . MYSQL_PREF . 'categories,
								 ' . MYSQL_PREF . 'images,
								 ' . MYSQL_PREF . 'commentaires
					   LEFT JOIN ' . MYSQL_PREF . 'users
							  ON ' . MYSQL_PREF . 'commentaires.user_id = ' . MYSQL_PREF . 'users.user_id
						   WHERE ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $obj_chemin . '%" 
							 AND ' . MYSQL_PREF . 'commentaires.image_id = ' . MYSQL_PREF . 'images.image_id 
							 AND ' . MYSQL_PREF . 'categories.categorie_id = ' . MYSQL_PREF . 'images.categorie_parent_id ' .
						   $recherche . $filtre . ' 
						ORDER BY ' . $this->template['infos']['comment_sort'] . ' ' . $this->template['infos']['comment_sens'] . ' 
						   LIMIT ' . $startnum . ',' . $this->config['admin_comment_nb'] . '';
		$comments = $this->mysql->select($mysql_requete);
		if (!is_array($comments)) {
			$this->template['display']['co_vide'] = 1;
			$this->template['comments']['no_comments'] = 1;
			if ($this->template['infos']['startnum']) {
				header('Location:index.php?' . $this->template['infos']['section'] . $this->template['infos']['recherche']);
				exit;
			}
		} else {
			settype($this->template['comments'], 'array');
			$this->template['comments'] = array_merge($this->template['comments'], $comments);
			for ($i = 0; $i < count($comments); $i++) {
				if (isset($this->config['admin_comment_ban']['auteurs'][$comments[$i]['commentaire_auteur']])) {
					$this->template['comments'][$i]['ban_auteur'] = 1;
				}
				if (isset($this->config['admin_comment_ban']['IP'][$comments[$i]['commentaire_ip']])) {
					$this->template['comments'][$i]['ban_ip'] = 1;
				}
			}
		}

		$this->template['config']['admin_comment_ban'] = $this->config['admin_comment_ban'];

		// Affichage des message.
		if (isset($_REQUEST['o_msg_display'])) {
			if ($_REQUEST['o_msg_display'] == 'montrer' && !$this->config['admin_comment_msg_display']) {
				$this->update_option('admin_comment_msg_display', 1);
				$this->template['display']['comment_msg'] = 1;
			} elseif ($_REQUEST['o_msg_display'] == 'cacher' && $this->config['admin_comment_msg_display']) {
				$this->update_option('admin_comment_msg_display', 0);
			}
			$this->template['config'] = $this->config;
		}
		if ($this->config['admin_comment_msg_display']) {
			$this->template['display']['comment_msg'] = 1;
		}
	}



	/*
	 *	Options commentaires.
	*/
	function comments_options() {

		$this->template['infos']['title'] = 'options des commentaires';

		if (isset($_REQUEST['u'])) {

			$maj = 0;
		
			// Elements obligatoires pour poster un commentaire.
			if (isset($_REQUEST['oc_fac_mail']) && empty($this->config['comment_courriel'])) {
				$this->update_option('comment_courriel', 1);
				$maj = 1;
			
			} elseif (empty($_REQUEST['oc_fac_mail']) && $this->config['comment_courriel']) {
				$this->update_option('comment_courriel', 0);
				$maj = 1;
			}
			if (isset($_REQUEST['oc_fac_web']) && empty($this->config['comment_siteweb'])) {
				$this->update_option('comment_siteweb', 1);
				$maj = 1;
			
			} elseif (empty($_REQUEST['oc_fac_web']) && $this->config['comment_siteweb']) {
				$this->update_option('comment_siteweb', 0);
				$maj = 1;
			}

			// Contrôles.
			if (isset($_POST['oc_pro_flood']) && preg_match('`^(0|[1-9]\d{0,3})$`', $_POST['oc_pro_flood'])
			&& $_POST['oc_pro_flood'] != $this->config['comment_antiflood']) {
				$this->update_option('comment_antiflood', $_POST['oc_pro_flood']);
				$maj = 1;
			}
			if (isset($_REQUEST['oc_samemsg']) && empty($this->config['comment_samemsg'])) {
				$this->update_option('comment_samemsg', 1);
				$maj = 1;
			
			} elseif (empty($_REQUEST['oc_samemsg']) && $this->config['comment_samemsg']) {
				$this->update_option('comment_samemsg', 0);
				$maj = 1;
			}
			if (isset($_REQUEST['oc_maxmsg']) && empty($this->config['comment_maxmsg'])) {
				$this->update_option('comment_maxmsg', 1);
				$maj = 1;
			
			} elseif (empty($_REQUEST['oc_maxmsg']) && $this->config['comment_maxmsg']) {
				$this->update_option('comment_maxmsg', 0);
				$maj = 1;
			}
			if (isset($_POST['oc_maxmsg_nb']) && preg_match('`^(0|[1-9]\d{0,3})$`', $_POST['oc_maxmsg_nb'])
			&& $_POST['oc_maxmsg_nb'] != $this->config['comment_maxmsg_nb']) {
				$this->update_option('comment_maxmsg_nb', $_POST['oc_maxmsg_nb']);
				$maj = 1;
			}
			if (isset($_REQUEST['oc_nourl']) && empty($this->config['comment_nourl'])) {
				$this->update_option('comment_nourl', 1);
				$maj = 1;
			
			} elseif (empty($_REQUEST['oc_nourl']) && $this->config['comment_nourl']) {
				$this->update_option('comment_nourl', 0);
				$maj = 1;
			}
			if (isset($_POST['oc_nourl_nb']) && preg_match('`^(0|[1-9]\d{0,3})$`', $_POST['oc_nourl_nb'])
			&& $_POST['oc_nourl_nb'] != $this->config['comment_maxurl']) {
				$this->update_option('comment_maxurl', $_POST['oc_nourl_nb']);
				$maj = 1;
			}

			// Modération des commentaires.
			if (isset($_REQUEST['oc_mod']) && empty($this->config['admin_comment_moderer'])) {
				$this->update_option('admin_comment_moderer', 1);
				$maj = 1;
				
			} elseif (empty($_REQUEST['oc_mod']) && $this->config['admin_comment_moderer']) {
				$this->update_option('admin_comment_moderer', 0);
				$maj = 1;
			}

			// Alerte par courriel.
			if (!empty($_REQUEST['oc_objet']) && $_REQUEST['oc_objet'] != $this->config['admin_comment_objet']) {
				$this->update_option('admin_comment_objet', $_REQUEST['oc_objet']);
				$maj = 1;
			}
			if (isset($_REQUEST['oc_alert']) && empty($this->config['admin_comment_alert'])) {
				$this->update_option('admin_comment_alert', 1);
				$maj = 1;
				
			} elseif (empty($_REQUEST['oc_alert']) && $this->config['admin_comment_alert']) {
				$this->update_option('admin_comment_alert', 0);
				$maj = 1;
			}

			// Page des commentaires.
			if ($this->template['enabled']['co_page_comments']) {
				if (isset($_REQUEST['oc_comments']) && empty($this->config['galerie_page_comments'])) {
					$this->update_option('galerie_page_comments', 1);
					$maj = 1;
				
				} elseif (empty($_REQUEST['oc_comments']) && $this->config['galerie_page_comments']) {
					$this->update_option('galerie_page_comments', 0);
					$maj = 1;
				}
				if (isset($_REQUEST['oc_comments_nb']) && preg_match('`^[1-9]\d{0,3}$`', $_REQUEST['oc_comments_nb'])
				&& $_REQUEST['oc_comments_nb'] != $this->config['galerie_page_comments_nb']) {
					$this->update_option('galerie_page_comments_nb', $_REQUEST['oc_comments_nb']);
					$this->config['galerie_page_comments_nb'] = $_REQUEST['oc_comments_nb'];
					$maj = 1;
				}
			}

			$this->template['action_maj'] = $maj;
		}

		// Config.
		$this->template['config'] = $this->config;
	}



	/*
	 *	Commentaires : bannissements.
	*/
	function comments_bans() {
		$this->template['infos']['title'] = 'règles de bannissement des commentaires';

		// Bannissements.
		$ban_update = 0;
		if (!empty($_POST['bans_mots_ajout'])) {
			$mots = trim($_POST['bans_mots_ajout']);
			if ($mots != '') {
				$mots = preg_split('`[\r\n]+`', $mots, -1, PREG_SPLIT_NO_EMPTY);
				for ($i = 0; $i < count($mots); $i++) {
					$mots[$i] = strtolower($mots[$i]);
					if (!isset($this->config['admin_comment_ban']['mots-cles'][$mots[$i]])) {
						$this->config['admin_comment_ban']['mots-cles'][$mots[$i]] = 1;
						$ban_update = 1;
					}
				}
			}
		}
		if (!empty($_POST['bans_auteur_ajout'])) {
			$auteurs = trim($_POST['bans_auteur_ajout']);
			if ($auteurs != '') {
				$auteurs = preg_split('`[\r\n]+`', $auteurs, -1, PREG_SPLIT_NO_EMPTY);
				for ($i = 0; $i < count($auteurs); $i++) {
					$auteurs[$i] = strtolower($auteurs[$i]);
					if (!isset($this->config['admin_comment_ban']['auteurs'][$auteurs[$i]])) {
						$this->config['admin_comment_ban']['auteurs'][$auteurs[$i]] = 1;
						$ban_update = 1;
					}
				}
			}
		}
		if (!empty($_POST['bans_ip_ajout'])) {
			$ip = trim($_POST['bans_ip_ajout']);
			if ($ip != '') {
				$ip = preg_split('`[\r\n]+`', $ip, -1, PREG_SPLIT_NO_EMPTY);
				for ($i = 0; $i < count($ip); $i++) {
					$ip[$i] = strtolower($ip[$i]);
					if (!isset($this->config['admin_comment_ban']['IP'][$ip[$i]])) {
						$this->config['admin_comment_ban']['IP'][$ip[$i]] = 1;
						$ban_update = 1;
					}
				}
			}
		}

		// Ré-autorisations.
		$unban_update = 0;
		if (isset($_POST['bans_allow_select'])) {
			if (isset($_POST['bans_mots']) && is_array($_POST['bans_mots'])) {
				$mots = $_POST['bans_mots'];
				for ($i = 0; $i < count($mots); $i++) {
					if (isset($this->config['admin_comment_ban']['mots-cles'][$mots[$i]])) {
						unset($this->config['admin_comment_ban']['mots-cles'][$mots[$i]]);
						$unban_update = 1;
					}
				}
			}
			if (isset($_POST['bans_auteurs']) && is_array($_POST['bans_auteurs'])) {
				$auteurs = $_POST['bans_auteurs'];
				for ($i = 0; $i < count($auteurs); $i++) {
					if (isset($this->config['admin_comment_ban']['auteurs'][$auteurs[$i]])) {
						unset($this->config['admin_comment_ban']['auteurs'][$auteurs[$i]]);
						$unban_update = 1;
					}
				}
			}
			if (isset($_POST['bans_ip']) && is_array($_POST['bans_ip'])) {
				$ip = $_POST['bans_ip'];
				for ($i = 0; $i < count($ip); $i++) {
					if (isset($this->config['admin_comment_ban']['IP'][$ip[$i]])) {
						unset($this->config['admin_comment_ban']['IP'][$ip[$i]]);
						$unban_update = 1;
					}
				}
			}
		}

		// Enregistrement des changements..
		if ($ban_update || $unban_update) {
			$this->update_option('admin_comment_ban', serialize($this->config['admin_comment_ban']), 0);
			$this->template['action_maj'] = 1;
		}

		// Config.
		$this->template['config'] = $this->config;
	}



	/*
	 *	Vérifie si une option de type "mot" ou "nombre" doit être updatée, puis l'UPDATE.
	*/
	function verif_option_word($param, $option, $type, $plus = '', $r = 0) {
		switch ($type) {
			case 'libre' :
				$pattern = '`^[^\x5C' . $plus . ']{0,65535}$`si';
				break;
			case 'mot' :
				$pattern = '`^[-a-z0-9_' . $plus . ']{1,99}$`i';
				break;
			case 'nombre' :
				$pattern = '`^\d{1,9}$`';
				break;
		}
		if (isset($_REQUEST[$param]) && preg_match($pattern, $_REQUEST[$param]) && $this->config[$option] != $_REQUEST[$param]) {
			if ($this->update_option($option, $_REQUEST[$param])) {
				if ($r) {
					$this->template['action_maj'] = $r;
				}
				return true;
			}
		}
		return false;
	}



	/*
	 *	Vérifie si une option de type "binaire" doit être updatée, puis l'UPDATE.
	*/
	function verif_option_bin($get, $option, $r = 0) {
		if (isset($_REQUEST[$get]) && empty($this->config[$option])) {
			if ($this->update_option($option, 1)) {
				if ($r) {
					$this->template['action_maj'] = $r;
				}
				return true;
			}
		} elseif (empty($_REQUEST[$get]) && $this->config[$option]) {
			if ($this->update_option($option, 0)) {
				if ($r) {
					$this->template['action_maj'] = $r;
				}
				return true;
			}
		}
		return false;
	}



	/*
	 *	Vidage du cache.
	*/
	function vidage_cache() {
		$cache_dir = '../cache/';
		$ok = FALSE;
		if (is_dir($cache_dir)) {
			if ($dir = @opendir($cache_dir)) {
				$ok = TRUE;
				while ($ent = readdir($dir)) {
					$file = $cache_dir . $ent;
					if (is_file($file) && $ent != '.htaccess') {
						if (!files::suppFile($file)) {
							$ok = FALSE;
						}
					}
				}
				closedir($dir);
			}
		}
		return $ok;
	}



	/*
	 *	Options : général.
	*/
	function options_general() {

		$this->template['infos']['title'] = 'paramétrage de la galerie';

		if (isset($_POST['u'])) {

			$conf = $this->recup_conf();
			$change = 0;

			// Liens.
			if ($this->template['enabled']['liens']) {
				$this->verif_option_bin('g_liens_active', 'active_liens', 1);
				if (isset($_POST['g_liens'])) {
					$liens = preg_split('`[\r\n]+`', $_POST['g_liens'], -1, PREG_SPLIT_NO_EMPTY);
					$_REQUEST['g_liens'] = serialize($liens);
					if ($_REQUEST['g_liens'] != $this->config['galerie_liens']) {
						$this->verif_option_word('g_liens', 'galerie_liens', 'libre', '', 1);
					}
				}
			}

			// Format de la date.
			$formats[1] = '%d-%m-%y';
			$formats[2] = '%d/%m/%y';
			$formats[3] = '%d-%m-%Y';
			$formats[4] = '%d/%m/%Y';
			$formats[5] = '%d %b %Y';
			$formats[6] = '%d %B %Y';
			$formats[7] = '%a %d %b %Y';
			$formats[8] = '%a %d %B %Y';
			$formats[9] = '%A %d %b %Y';
			$formats[10] = '%A %d %B %Y';

			if ($this->template['enabled']['date_format_thumbs']) {
				$_REQUEST['g_ftbdate'] = $formats[$_REQUEST['g_ftbdate']];
				$this->verif_option_word('g_ftbdate', 'galerie_tb_date_format', 'libre', '', 1);
			}

			if ($this->template['enabled']['date_format_images']) {
				$_REQUEST['g_fimdate'] = $formats[$_REQUEST['g_fimdate']];
				$this->verif_option_word('g_fimdate', 'galerie_im_date_format', 'libre', '', 1);
			}

			// Template.
			if (!empty($_POST['g_theme']) && GALERIE_THEME != $_POST['g_theme']
			 && preg_match('`^[a-z0-9_-]{1,99}$`i', $_POST['g_theme'])
			 && is_dir('../template/' . $_POST['g_theme'])) {
				$change = 1;
				$conf['galerie_theme'] = $_POST['g_theme'];
				$this->config['galerie_template'] = $_POST['g_theme'];
				include(dirname(__FILE__) . '/../template/' . $_POST['g_theme'] . '/_fonctions.php');
				$this->template['enabled'] = $f;
			}

			// Style.
			if (!empty($_POST['g_style']) && GALERIE_STYLE != $_POST['g_style']
			 && preg_match('`^[a-z0-9_-]{1,99}$`i', $_POST['g_style'])
			 && is_dir('../template/' . $this->config['galerie_template'] . '/style/' . $_POST['g_style'])) {
				$change = 1;
				$conf['galerie_style'] = $_POST['g_style'];
				$this->config['galerie_style'] = $_POST['g_style'];
			}

			// Style addtionnel.
			if (trim($_POST['g_add_style']) != $this->config['galerie_add_style']) {
				$this->verif_option_word('g_add_style', 'galerie_add_style', 'libre', '', 1);
			}

			// URL de la galerie.
			if (!empty($_POST['g_url_galerie']) && $_POST['g_url_galerie'] != GALERIE_URL) {
				$change = 1;

				$_POST['g_url_galerie'] = preg_replace('`^http://[^/]+/`', '/', $_POST['g_url_galerie']);
				$_POST['g_url_galerie'] = str_replace(' ', '%20', $_POST['g_url_galerie']);

				$conf['galerie_url'] = $_POST['g_url_galerie'];
				$this->config['galerie_url'] = $_POST['g_url_galerie'];

				$galerie_path = dirname($_POST['g_url_galerie']);
				$galerie_path = (preg_match('`^[./]*$`', $galerie_path)) ? '' : $galerie_path;
				$galerie_path = preg_replace('`/+$`', '', $galerie_path);
				$galerie_path = preg_replace('[\x5c]', '', $galerie_path);

				$conf['galerie_path'] = $galerie_path;
				$this->config['galerie_path'] = $galerie_path;

			}

			// Type d'URL.
			if (!empty($_POST['g_url_type']) && GALERIE_URL_TYPE != $_POST['g_url_type']
			 && preg_match('`^(normal|query_string|path_info|url_rewrite)$`i', $_POST['g_url_type'])) {
				$change = 1;
				$conf['galerie_url_type'] = $_POST['g_url_type'];
				$this->config['galerie_url_type'] = $_POST['g_url_type'];

			}

			// Intégration ?
			if (!empty($_POST['g_integrated']) && !GALERIE_INTEGRATED) {
				$change = 1;
				$conf['galerie_integrated'] = 1;
				$this->config['galerie_integrated'] = 1;
			}
			if (empty($_POST['g_integrated']) && GALERIE_INTEGRATED) {
				$change = 1;
				$conf['galerie_integrated'] = 0;
				$this->config['galerie_integrated'] = 0;
			}

			// Non prises en compte des visites admin.
			$anh_active = false;
			$anh_cookie = false;
			if ($this->verif_option_bin('g_admin_hits', 'admin_no_hits', 1)) {
				if ($this->config['admin_no_hits']) {
					$anh_active = true;
				}
			}
			if ($this->verif_option_word('g_admin_hits_mode', 'admin_no_hits_mode', 'mot', '', 1)) {
				if ($this->config['admin_no_hits_mode'] == 'cookie') {
					$anh_cookie = true;
				}
			}
			if ($_POST['g_admin_hits_ip_addr'] != $this->config['admin_no_hits_ip']) {
				if (preg_match('`^(?:' . outils::http_url('IP') . ',?)+$`', trim($_POST['g_admin_hits_ip_addr']))) {
					$this->verif_option_word('g_admin_hits_ip_addr', 'admin_no_hits_ip', 'libre', '', 1);
				}
			}
			if ((($anh_active && $this->config['admin_no_hits_mode'] == 'cookie') || $anh_cookie)
			  && $this->config['admin_no_hits']) {
				$prefs = new cookie(31536000, 'galerie_perso', $conf['galerie_path']);
				$prefs->ajouter('anh', md5($this->config['galerie_key']));
				$prefs->ecrire();
			}

			// Mise à jour du fichier de configuration.
			if ($change) {
				if ($this->update_conf($conf, 0)) {
					$this->template['action_maj'] = 1;
				}
			}
		}

		// Récupération des thèmes.
		$i = 0;
		$template = dirname(__FILE__) . '/../template/';
		if ($rep = @opendir($template)) {
			while ($ent = readdir($rep)) {
				if (is_dir($template . $ent) && !preg_match('`^\.{1,2}$`', $ent)) {
					if (preg_match('`^[a-z0-9-_]+$`i', $ent)) {
						$this->template['themes'][$i] = $ent;
						$this->template['js_themes_styles'][$ent] = array();
						$i++;
					}
				}
			}
			closedir($rep);
		}

		// Récupération des styles.
		foreach ($this->template['js_themes_styles'] as $themes => $styles) {
			$i = 0;
			$style_rep = dirname(__FILE__) . '/../template/' . $themes . '/style/';
			if ($rep = @opendir($style_rep)) {
				while ($ent = readdir($rep)) {
					if (is_dir($style_rep . $ent) && !preg_match('`^\.{1,2}$`', $ent)) {
						if (preg_match('`^[a-z0-9-_]+$`i', $ent)) {
							$this->template['js_themes_styles'][$themes][$i] = $ent;
							if ($themes == $this->config['galerie_template']) {
								$this->template['styles'][$i] = $ent;
							}
							$i++;
						}
					}
				}
				closedir($rep);
			}
		}

		// Config.
		$this->template['config'] = $this->config;

	}



	/*
	 *	Options : vignettes.
	*/
	function options_vignettes() {

		$this->template['infos']['title'] = 'paramètres des vignettes';
		$this->config['galerie_tb_alb_size'] = THUMB_ALB_SIZE;
		$this->config['galerie_tb_img_size'] = THUMB_IMG_SIZE;
		$this->config['galerie_tb_alb_crop_width'] = THUMB_ALB_CROP_WIDTH;
		$this->config['galerie_tb_alb_crop_height'] = THUMB_ALB_CROP_HEIGHT;
		$this->config['galerie_tb_img_crop_width'] = THUMB_IMG_CROP_WIDTH;
		$this->config['galerie_tb_img_crop_height'] = THUMB_IMG_CROP_HEIGHT;
		$this->config['galerie_tb_alb_mode'] = THUMB_ALB_MODE;
		$this->config['galerie_tb_img_mode'] = THUMB_IMG_MODE;

		if (isset($_POST['u'])) {

			$conf = $this->recup_conf();

			// Nombre de vignettes.
			$this->verif_option_word('g_tb_img_vn', 'vignettes_col', 'nombre', '', 1);
			$this->verif_option_word('g_tb_img_vl', 'vignettes_line', 'nombre', '', 1);
			$this->verif_option_word('g_tb_cat_vn', 'vignettes_cat_col', 'nombre', '', 1);
			$this->verif_option_word('g_tb_cat_vl', 'vignettes_cat_line', 'nombre', '', 1);

			// Ordre et sens d'affichage des vignettes des images.
			$this->verif_option_word('g_ordre', 'vignettes_ordre', 'mot', '*', 1);
			$this->verif_option_word('g_sens', 'vignettes_sens', 'mot', '', 1);

			// Ordre et sens d'affichage des vignettes des albums.
			if (isset($_POST['g_cat_ordre']) && preg_match('`^[a-z]{2,15}$`i', $_POST['g_cat_ordre']) && 
			    isset($_POST['g_cat_sens']) && preg_match('`^[a-z]{3,4}$`i', $_POST['g_cat_sens'])) {
				$cat_trie = 'categorie_' . $_POST['g_cat_ordre'] . ' ' . $_POST['g_cat_sens'];
				if ($_POST['g_cat_ordre'] == 'nom') {
					$cat_trie = $cat_trie . ',categorie_date DESC';
				} else {
					$cat_trie = $cat_trie . ',categorie_nom ASC';
				}
				$_REQUEST['g_cat_ordre'] = $cat_trie;
				$this->verif_option_word('g_cat_ordre', 'vignettes_cat_ordre', 'libre', '', 1);
			}
			if (isset($_POST['g_tb_cat_type']) && preg_match('`^(alb|cat|sans)$`', $_POST['g_tb_cat_type'])) {
				if ($_POST['g_tb_cat_type'] == 'alb') {
					$cat_type = 'type DESC,';
				} elseif ($_POST['g_tb_cat_type'] == 'cat') {
					$cat_type = 'type ASC,';
				} else {
					$cat_type = '';
				}
				$_REQUEST['g_tb_cat_type'] = $cat_type;
				$this->verif_option_word('g_tb_cat_type', 'vignettes_cat_type', 'libre', '', 1);
			}

			// Mode d'affichage des vignettes des catégories.
			if ($this->template['enabled']['thumbs_display_mode']) {
				$this->verif_option_word('g_tb_cat_mode', 'vignettes_cat_mode', 'mot', '', 1);
			}

			// Informations sous les vignettes.
			if ($this->template['enabled']['thumbs_infos']) {
				$this->verif_option_bin('g_info_c_nom', 'display_cat_nom', 1);
				$this->verif_option_bin('g_info_c_imgs', 'display_cat_nb_images', 1);
				$this->verif_option_bin('g_info_c_poids', 'display_cat_poids', 1);
				$this->verif_option_bin('g_info_c_hits', 'display_cat_hits', 1);
				$this->verif_option_bin('g_info_c_comments', 'display_cat_comments', 1);
				$this->verif_option_bin('g_info_c_votes', 'display_cat_votes', 1);

				$this->verif_option_bin('g_info_i_nom', 'display_img_nom', 1);
				$this->verif_option_bin('g_info_i_date', 'display_img_date', 1);
				$this->verif_option_bin('g_info_i_taille', 'display_img_taille', 1);
				$this->verif_option_bin('g_info_i_poids', 'display_img_poids', 1);
				$this->verif_option_bin('g_info_i_hits', 'display_img_hits', 1);
				$this->verif_option_bin('g_info_i_comments', 'display_img_comments', 1);
				$this->verif_option_bin('g_info_i_votes', 'display_img_votes', 1);
			}

			// Dimensions des vignettes des albums.
			$del_thumbs = 0;
			$change = 0;
			$new_conf = $this->post_config($conf, THUMB_ALB_MODE, 'g_tb_alb_mode', 'galerie_tb_alb_mode', 'thumb_alb_mode', '`^(?:size|crop)$`');
			$new_conf = $this->post_config($new_conf, THUMB_ALB_SIZE, 'g_tb_alb_size', 'galerie_tb_alb_size', 'thumb_alb_size', '`^([5-9]\d|[1-2]\d\d|300)$`');
			$new_conf = $this->post_config($new_conf, THUMB_ALB_CROP_WIDTH, 'g_tb_alb_crop_width', 'galerie_tb_alb_crop_width', 'thumb_alb_crop_width', '`^([5-9]\d|[1-2]\d\d|300)$`');
			$new_conf = $this->post_config($new_conf, THUMB_ALB_CROP_HEIGHT, 'g_tb_alb_crop_height', 'galerie_tb_alb_crop_height', 'thumb_alb_crop_height', '`^([5-9]\d|[1-2]\d\d|300)$`');			
			if ($new_conf != $conf) {
				$conf = $new_conf;
				$change = 1;
				$this->del_thumbs(THUMB_TDIR, 0, 2);
			}

			// Dimensions des vignettes des images.
			$new_conf = $this->post_config($conf, THUMB_IMG_MODE, 'g_tb_img_mode', 'galerie_tb_img_mode', 'thumb_img_mode', '`^(?:size|crop)$`');
			$new_conf = $this->post_config($new_conf, THUMB_IMG_SIZE, 'g_tb_img_size', 'galerie_tb_img_size', 'thumb_img_size', '`^([5-9]\d|[1-2]\d\d|300)$`');
			$new_conf = $this->post_config($new_conf, THUMB_IMG_CROP_WIDTH, 'g_tb_img_crop_width', 'galerie_tb_img_crop_width', 'thumb_img_crop_width', '`^([5-9]\d|[1-2]\d\d|300)$`');
			$new_conf = $this->post_config($new_conf, THUMB_IMG_CROP_HEIGHT, 'g_tb_img_crop_height', 'galerie_tb_img_crop_height', 'thumb_img_crop_height', '`^([5-9]\d|[1-2]\d\d|300)$`');
			if ($new_conf != $conf) {
				$conf = $new_conf;
				$change = 1;
				$this->del_thumbs(THUMB_TDIR, 0, 1);
			}

			// Mise à jour du fichier de configuration.
			if ($change) {
				$this->update_conf($conf, 0);
			}
		}

		// Config.
		$this->template['config'] = $this->config;
	}



	/*
	  *	Traitement des options du fichier de configuration.
	*/
	function post_config($conf_file, $constante, $post, $config, $conf_file_data, $regexp) {
		if (isset($_POST[$post]) && preg_match($regexp, $_POST[$post])
		 && $constante != $_POST[$post]) {
			$conf_file[$conf_file_data] = $_POST[$post];
			$this->config[$config] = $_POST[$post];
			$this->template['action_maj'] = 1;
		}
		return $conf_file;
	}



	/*
	 *	Options : images.
	*/
	function options_images() {
	
		$this->template['infos']['title'] = 'paramètres des images';

		// Récupération des paramètres de la fonction d'ajout de texte sur les images.
		$this->config['itext'] = IMG_TEXTE;
		$itext_params = preg_split('` `', IMG_TEXTE_PARAMS, -1, PREG_SPLIT_NO_EMPTY);

		if (isset($_POST['u'])) {

			$conf = $this->recup_conf();
			$change = 0;

			// Mode d'affichage des images.
			$this->verif_option_word('g_img', 'galerie_images_window', 'nombre', '', 1);

			// Redimensionnement des images : mode.
			$this->verif_option_word('g_img_resize', 'galerie_images_resize', 'nombre', '', 1);

			// Redimensionnement des images : taille max. HTML.
			if (!empty($_POST['g_resize_max_html_largeur']) && !empty($_POST['g_resize_max_html_hauteur'])
			 && preg_match('`^\d{2,4}$`', $_POST['g_resize_max_html_largeur']) && preg_match('`^\d{2,4}$`', $_POST['g_resize_max_html_hauteur'])
			 && $_POST['g_resize_max_html_largeur'] . 'x' . $_POST['g_resize_max_html_hauteur'] != $this->config['galerie_images_resize_max_html']) {
				$_REQUEST['galerie_images_resize_max_html'] = $_POST['g_resize_max_html_largeur'] . 'x' . $_POST['g_resize_max_html_hauteur'];
				$this->verif_option_word('galerie_images_resize_max_html', 'galerie_images_resize_max_html', 'libre', '', 1);
			}

			// Redimensionnement des images : taille max. GD.
			if (!empty($_POST['g_resize_max_gd_largeur']) && !empty($_POST['g_resize_max_gd_hauteur'])
			 && preg_match('`^\d{2,4}$`', $_POST['g_resize_max_gd_largeur']) && preg_match('`^\d{2,4}$`', $_POST['g_resize_max_gd_hauteur'])
			 && $_POST['g_resize_max_gd_largeur'] . 'x' . $_POST['g_resize_max_gd_hauteur'] != $this->config['img_resize_gd']) {
				$conf['img_resize_gd'] = $_POST['g_resize_max_gd_largeur'] . 'x' . $_POST['g_resize_max_gd_hauteur'];
				$this->config['img_resize_gd'] = $conf['img_resize_gd'];
				$change = 1;
				$this->template['action_maj'] = 1;
				$this->vidage_cache();
			}

			// Meta-données EXIF.
			if ($this->template['enabled']['exif']) {
				$this->verif_option_bin('g_exif_active', 'active_exif', 1);
				$this->verif_option_bin('g_exif_ajouts', 'active_exif_ajout', 1);
			}

			// Meta-données IPTC.
			if ($this->template['enabled']['iptc']) {
				$this->verif_option_bin('g_iptc_active', 'active_iptc', 1);
				$this->verif_option_bin('g_iptc_ajouts', 'active_iptc_ajout', 1);
			}

			// Images récentes.
			if ($this->template['enabled']['recentes']) {
				$this->verif_option_bin('g_recentes', 'display_recentes', 1);
				$this->verif_option_word('g_jours', 'galerie_recent', 'nombre', '', 1);
				$this->verif_option_bin('g_recent_nb', 'galerie_recent_nb', 1);
			}

			// Texte sur chaque image : activation de la fonctionnalité.
			$htaccess_file = '../' . GALERIE_ALBUMS . '/.htaccess';
			if ((isset($_REQUEST['g_itext_active']) && !IMG_TEXTE)) {
				$change = 1;
				$conf['img_texte'] = 1;
				$this->config['itext'] = 1;

				// On supprime le fichier .htaccess interdisant l'accès aux images de l'album.
				if (file_exists($htaccess_file)) {
					files::suppFile($htaccess_file);
				}

				// On crée un fichier .htaccess interdisant l'accès aux images de l'album.
				files::chmodDir(dirname($htaccess_file));
				if ($htaccess = @fopen($htaccess_file, 'w')) {
					$instruction = "Deny from all";
					if (!@fwrite($htaccess, $instruction)) {
						$this->template['infos']['erreur']['change_config'] = 'Impossible de créer le fichier .htaccess afin de protéger les images.';
					}
					@fclose($htaccess);
					files::chmodFile($htaccess_file);
				} else {
					$this->template['infos']['erreur']['change_config'] = 'Impossible de créer le fichier .htaccess afin de protéger les images.';
				}

			} elseif ((empty($_REQUEST['g_itext_active']) && IMG_TEXTE)) {
				$change = 1;
				$conf['img_texte'] = 0;
				$this->config['itext'] = 0;

				// On supprime le fichier .htaccess interdisant l'accès aux images de l'album.
				if (file_exists($htaccess_file)) {
					files::suppFile($htaccess_file);
				}

			}

			// Texte sur chaque image : texte.
			if (isset($_REQUEST['g_itext_texte'])) {
				$_REQUEST['g_itext_texte'] = trim($_REQUEST['g_itext_texte']);
				if ($_REQUEST['g_itext_texte'] === '') {
					$_REQUEST['g_itext_texte'] = ' ';
				}
				if ($_REQUEST['g_itext_texte'] != str_replace('^', ' ', $itext_params[0])) {
					$change = 1;
					$itext_params[0] = preg_replace('`[\x5c]`', '', $_REQUEST['g_itext_texte']);
					$itext_params[0] = str_replace(' ', '^', $itext_params[0]);
				}
			}

			// Mise à jour du fichier de configuration.
			if ($change) {
				$conf['img_texte_params'] = implode(' ', $itext_params);
				$this->config['itext_params'] = $conf['img_texte_params'];
				$this->template['action_maj'] = 1;
				$this->update_conf($conf, 0);
			}

		}

		// Config.
		$this->template['config'] = $this->config;
		$this->template['config']['itext_params'] = $itext_params;
	}



	/*
	 *	Options : paramètre du texte sur chaque image.
	*/
	function options_itext() {

		$this->template['infos']['title'] = 'texte de copyright';

		$itext_params = preg_split('` `', IMG_TEXTE_PARAMS, -1, PREG_SPLIT_NO_EMPTY);

		if (isset($_POST['itext_params'])) {

			$conf = $this->recup_conf();
			$change = 0;

			// Texte sur chaque image : taille du texte.
			if ((isset($_REQUEST['g_itext_taille']) && preg_match('`^\d{1,2}$`', $_REQUEST['g_itext_taille']) && $_REQUEST['g_itext_taille'] != $itext_params[17])) {
				$change = 1;
				$itext_params[17] = $_REQUEST['g_itext_taille'];
			}
		
			// Texte sur chaque image : fonte.
			if ((isset($_REQUEST['g_itext_fonte']) && preg_match('`^[a-z0-9_.-]+$`i', $_REQUEST['g_itext_fonte']) && $_REQUEST['g_itext_fonte'] != $itext_params[4])) {
				$change = 1;
				$itext_params[4] = $_REQUEST['g_itext_fonte'];
			}

			// Texte sur chaque image : couleur du texte.
			$rgb_texte[0] = $itext_params[1];
			$rgb_texte[1] = $itext_params[2];
			$rgb_texte[2] = $itext_params[3];
			if ((isset($_REQUEST['g_itext_texte_color']) && preg_match('`^#?[0-9a-f]{6}$`i', $_REQUEST['g_itext_texte_color']))) {
				$request_texte = str_replace('#', '', $_REQUEST['g_itext_texte_color']);
				if ($request_texte != outils::convert_rgb2html($rgb_texte)) {
					$new_rgb_texte = outils::convert_html2rgb($request_texte);
					$itext_params[1] = $new_rgb_texte[0];
					$itext_params[2] = $new_rgb_texte[1];
					$itext_params[3] = $new_rgb_texte[2];
					$change = 1;
				}
			}

			// Texte sur chaque image : position.
			if ((isset($_REQUEST['g_itext_position']) && preg_match('`^(top_left|top_center|top_right|bottom_left|bottom_center|bottom_right)$`', $_REQUEST['g_itext_position']) && $_REQUEST['g_itext_position'] != $itext_params[5])) {
				$change = 1;
				$itext_params[5] = $_REQUEST['g_itext_position'];
			}

			// Texte sur chaque image : position - exterieur.
			if ((isset($_REQUEST['g_itext_exterieur']) && preg_match('`^(0|1)$`', $_REQUEST['g_itext_exterieur']) && $_REQUEST['g_itext_exterieur'] != $itext_params[21])) {
				$change = 1;
				$itext_params[21] = $_REQUEST['g_itext_exterieur'];
			}

			// Texte sur chaque image : position - bord vertical.
			if ((isset($_REQUEST['g_itext_bord_y']) && preg_match('`^-?\d{1,3}$`', $_REQUEST['g_itext_bord_y']) && $_REQUEST['g_itext_bord_y'] != $itext_params[19])) {
				$change = 1;
				$itext_params[19] = $_REQUEST['g_itext_bord_y'];
			}

			// Texte sur chaque image : position - bord horizontal.
			if ((isset($_REQUEST['g_itext_bord_x']) && preg_match('`^-?\d{1,3}$`', $_REQUEST['g_itext_bord_x']) && $_REQUEST['g_itext_bord_x'] != $itext_params[18])) {
				$change = 1;
				$itext_params[18] = $_REQUEST['g_itext_bord_x'];
			}

			// Texte sur chaque image : position - décalage vertical.
			if ((isset($_REQUEST['g_itext_decalage']) && preg_match('`^-?\d{1,3}$`', $_REQUEST['g_itext_decalage']) && $_REQUEST['g_itext_decalage'] != $itext_params[26])) {
				$change = 1;
				$itext_params[26] = $_REQUEST['g_itext_decalage'];
			}

			// Texte sur chaque image : fond.
			if ((isset($_REQUEST['g_itext_fond']) && !$itext_params[6])) {
				$change = 1;
				$itext_params[6] = 1;
			} elseif ((empty($_REQUEST['g_itext_fond']) && $itext_params[6])) {
				$change = 1;
				$itext_params[6] = 0;
			}

			// Texte sur chaque image : couleur de fond.
			$rgb_fond[0] = $itext_params[7];
			$rgb_fond[1] = $itext_params[8];
			$rgb_fond[2] = $itext_params[9];
			if ((isset($_REQUEST['g_itext_fond_color']) && preg_match('`^#?[0-9a-f]{6}$`i', $_REQUEST['g_itext_fond_color']))) {
				$request_fond = str_replace('#', '', $_REQUEST['g_itext_fond_color']);
				if ($request_fond != outils::convert_rgb2html($rgb_fond)) {
					$new_rgb_fond = outils::convert_html2rgb($request_fond);
					$itext_params[7] = $new_rgb_fond[0];
					$itext_params[8] = $new_rgb_fond[1];
					$itext_params[9] = $new_rgb_fond[2];
					$change = 1;
				}
			}

			// Texte sur chaque image : couleur de fond - transparence.
			if ((isset($_REQUEST['g_itext_fond_a']) && preg_match('`^(\d{1,2}|1[0-1]\d|12[0-7])$`', $_REQUEST['g_itext_fond_a']) && $_REQUEST['g_itext_fond_a'] != $itext_params[10])) {
				$change = 1;
				$itext_params[10] = $_REQUEST['g_itext_fond_a'];
			}

			// Texte sur chaque image : fond - prendre toute la largeur.
			if ((isset($_REQUEST['g_itext_fond_largeur']) && !$itext_params[11])) {
				$change = 1;
				$itext_params[11] = 1;
			} elseif ((empty($_REQUEST['g_itext_fond_largeur']) && $itext_params[11])) {
				$change = 1;
				$itext_params[11] = 0;
			}

			// Texte sur chaque image : padding
			if ((isset($_REQUEST['g_itext_padding']) && preg_match('`^\d{1,3}$`', $_REQUEST['g_itext_padding']) && $_REQUEST['g_itext_padding'] != $itext_params[20])) {
				$change = 1;
				$itext_params[20] = $_REQUEST['g_itext_padding'];
			}

			// Texte sur chaque image : bordure.
			if ((isset($_REQUEST['g_itext_bordure']) && !$itext_params[22])) {
				$change = 1;
				$itext_params[22] = 1;
			} elseif ((empty($_REQUEST['g_itext_bordure']) && $itext_params[22])) {
				$change = 1;
				$itext_params[22] = 0;
			}

			// Texte sur chaque image : couleur de bordure.
			$rgb_bordure[0] = $itext_params[23];
			$rgb_bordure[1] = $itext_params[24];
			$rgb_bordure[2] = $itext_params[25];
			if ((isset($_REQUEST['g_itext_bordure_color']) && preg_match('`^#?[0-9a-f]{6}$`i', $_REQUEST['g_itext_bordure_color']))) {
				$request_bordure = str_replace('#', '', $_REQUEST['g_itext_bordure_color']);
				if ($request_bordure != outils::convert_rgb2html($rgb_bordure)) {
					$new_rgb_bordure = outils::convert_html2rgb($request_bordure);
					$itext_params[23] = $new_rgb_bordure[0];
					$itext_params[24] = $new_rgb_bordure[1];
					$itext_params[25] = $new_rgb_bordure[2];
					$change = 1;
				}
			}

			// Texte sur chaque image : ombrage.
			if ((isset($_REQUEST['g_itext_ombre']) && !$itext_params[12])) {
				$change = 1;
				$itext_params[12] = 1;
			} elseif ((empty($_REQUEST['g_itext_ombre']) && $itext_params[12])) {
				$change = 1;
				$itext_params[12] = 0;
			}

			// Texte sur chaque image : couleur de l'ombrage.
			$rgb_ombre[0] = $itext_params[14];
			$rgb_ombre[1] = $itext_params[15];
			$rgb_ombre[2] = $itext_params[16];
			if ((isset($_REQUEST['g_itext_ombre_color']) && preg_match('`^#?[0-9a-f]{6}$`i', $_REQUEST['g_itext_ombre_color']))) {
				$request_ombre = str_replace('#', '', $_REQUEST['g_itext_ombre_color']);
				if ($request_ombre != outils::convert_rgb2html($rgb_ombre)) {
					$new_rgb_ombre = outils::convert_html2rgb($request_ombre);
					$itext_params[14] = $new_rgb_ombre[0];
					$itext_params[15] = $new_rgb_ombre[1];
					$itext_params[16] = $new_rgb_ombre[2];
					$change = 1;
				}
			}

			// Texte sur chaque image : épaisseur de l'ombrage.
			if ((isset($_REQUEST['g_itext_ombre_width']) && preg_match('`^\d{1,2}$`', $_REQUEST['g_itext_ombre_width']) && $_REQUEST['g_itext_ombre_width'] != $itext_params[13])) {
				$change = 1;
				$itext_params[13] = $_REQUEST['g_itext_ombre_width'];
			}

			// Texte sur chaque image : qualité de l'image.
			if ((isset($_REQUEST['g_itext_qualite']) && preg_match('`^(\d{1,2}|100)$`', $_REQUEST['g_itext_qualite']) && $_REQUEST['g_itext_qualite'] != $itext_params[27])) {
				$change = 1;
				$itext_params[27] = $_REQUEST['g_itext_qualite'];
			}

			// Mise à jour du fichier de configuration.
			if ($change) {
				$conf['img_texte_params'] = implode(' ', $itext_params);
				$this->config['itext_params'] = $conf['img_texte_params'];
				$this->template['action_maj'] = 1;
				$this->update_conf($conf, 0);

				// On mesure la taille de la correction de hauteur des images.
				$mysql_requete = 'SELECT image_chemin,image_hauteur FROM ' . MYSQL_PREF . 'images
					WHERE image_chemin REGEXP "\.jpe?g$"
					ORDER BY image_largeur*image_hauteur ASC
					LIMIT 1';
				if ($image_infos = $this->mysql->select($mysql_requete)) {
					$image_file = $this->galerie_dir . $image_infos[0]['image_chemin'];
					$image_temp = dirname(dirname(__FILE__)) . '/cache/temp_' . basename($image_infos[0]['image_chemin']);
					$img_correction = 1;
					$it_params = $itext_params;
					include(dirname(__FILE__) . '/../getitext.php');
					imagejpeg($image_texte, $image_temp, $it_params[27]);
					imagedestroy($image_texte);
					if (file_exists($image_temp)) {
						files::chmodFile($image_temp);
						@list($width, $height) = getimagesize($image_temp);
						files::suppFile($image_temp);
						if (!empty($height)) {
							$diff = $height - $image_infos[0]['image_hauteur'];
							if ($diff != $this->config['galerie_images_text_correction']) {
								$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'config 
									SET valeur = "' . $diff . '"
									WHERE parametre = "galerie_images_text_correction"';
								$this->mysql->requete($mysql_requete);
							}
						}
					}
				}
			}
		}

		// Récupération des fontes.
		if (isset($_GET['page']) && $_GET['page'] == 'itext_params') {
			$i = 0;
			$fonte_dir = '../fontes/';
			if ($rep = opendir($fonte_dir)) {
				while ($ent = readdir($rep)) {
					if (is_file($fonte_dir . $ent)) {
						if (preg_match('`^[a-z0-9_.-]+\.ttf$`i', $ent)) {
							$this->template['fontes'][$i] = $ent;
							$i++;
						}
					}
				}
				closedir($rep);
			}
		}

		// Récupération des informations d'affichage de texte sur les images.
		$this->template['config']['itext_params'] = $itext_params;
	}


	/*
	  *	Options : IPTC.
	*/
	function options_iptc() {

		$this->template['infos']['title'] = 'informations IPTC';

		// Réinitilaisation des paramètres.
		if (isset($_GET['reinit_params'])) {
			$this->update_option('galerie_iptc_params', $this->config['galerie_iptc_params_default']);
			$this->config['galerie_iptc_params'] = $this->config['galerie_iptc_params_default'];
			$this->template['infos']['action']['reinit_params'] = 'Les paramètres ont été réinitialisés.';
		}

		$iptc_champs = unserialize($this->config['galerie_iptc_params']);

		if (isset($_POST['iptc_param_description'])) {
			$change = false;
			foreach ($_POST['iptc_param_description'] as $champ => $nom) {

				$iptc_champ = '2#' . $champ;
				if (!isset($iptc_champs[$iptc_champ])) {
					continue;
				}
				$nom = trim($nom);

				// Etat.
				if (empty($_POST['iptc_param'][$champ]) && $iptc_champs[$iptc_champ]['active']) {
					$iptc_champs[$iptc_champ]['active'] = 0;
					$change = true;
				}
				if (!empty($_POST['iptc_param'][$champ]) && !$iptc_champs[$iptc_champ]['active']) {
					$iptc_champs[$iptc_champ]['active'] = 1;
					$change = true;
				}

				// Description.
				if ($nom != $iptc_champs[$iptc_champ]['nom']) {
					$iptc_champs[$iptc_champ]['nom'] = $nom;
					$change = true;
				}
			}

			if ($change) {
				$new_iptc_champs = preg_replace('`[\x5c]`', '', serialize($iptc_champs));
				$this->update_option('galerie_iptc_params', $new_iptc_champs);
				$this->template['action_maj'] = 1;
			}
		}


		$this->template['config']['infos_iptc'] = $iptc_champs;

	}



	/*
	 *	Options : Exif.
	*/
	function options_exif_sort($exif_params, $section, $reinit = 0) {
		static $i = 0;
		static $exif_sections = array();
		if ($reinit) {
			$i = 0;
			$exif_sections = array();
		}
		if (isset($exif_params[$section])) {
			ksort($exif_params[$section]);
			foreach ($exif_params[$section] as $tag => $params) {
				$exif_sections[$i] = $params;
				$exif_sections[$i]['tag'] = $tag;
				$exif_sections[$i]['section'] = $section;
				$i++;
			}
		}
		return $exif_sections;
	}
	function options_exif() {

		// Réinitilaisation des paramètres.
		if (isset($_GET['reinit_params'])) {
			$this->verifVID();
			$this->update_option('galerie_exif_params', $this->config['galerie_exif_params_default']);
			$this->config['galerie_exif_params'] = $this->config['galerie_exif_params_default'];
			$this->template['infos']['action']['reinit_params'] = 'Les paramètres ont été réinitialisés.';
		}

		$this->template['infos']['title'] = 'informations EXIF';
		$exif_params = unserialize($this->config['galerie_exif_params']);
		$masque_libre_court = '`^[éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)\'\"«»<>!?-]{1,60}$`i';
		$masque_libre_court_zero = '`^[éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)\'\"«»<>!?-]{0,60}$`i';
		$masque_libre_etendu = '`^[éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)\'\"«»<>!?-]{1,250}$`i';

		// Nouveau paramètre.
		if (isset($_GET['new_param'])) {
			$exif_params['IFD0']['0.NouveauTag']['new'] = 1;
			$exif_params['IFD0']['0.NouveauTag']['active'] = 0;
			$exif_params['IFD0']['0.NouveauTag']['desc'] = 'Nouvelle description';
			$exif_params['IFD0']['0.NouveauTag']['method'] = 'simple';
		}

		// Suppression d'un paramètre.
		if (isset($_GET['delete']) && preg_match('`^(COMPUTED|IFD0|THUMBNAIL|EXIF|INTEROP)\.[a-z\d\.:_]{2,100}$`i', $_GET['delete'])) {
			$this->verifVID();
			$delete = split('\.', $_GET['delete'], 2);
			list($section, $tag) = $delete;
			if (isset($exif_params[$section][$tag])) {
				unset($exif_params[$section][$tag]);
				$this->update_option('galerie_exif_params', serialize($exif_params));
				$this->template['infos']['action']['vidage_cache'] = 'Le tag « ' . $tag . ' » de la section « ' . $section . ' » a été supprimé.';
			}
		}

		// Trie des sections.
		$this->options_exif_sort($exif_params, 'COMPUTED');
		$this->options_exif_sort($exif_params, 'IFD0');
		$this->options_exif_sort($exif_params, 'THUMBNAIL');
		$this->options_exif_sort($exif_params, 'EXIF');
		$this->options_exif_sort($exif_params, 'INTEROP');
		$this->template['config']['infos_exif'] = $this->options_exif_sort($exif_params, 'MAKERNOTE');

		if (isset($_POST['exif_param_section'])) {
			$modif = 0;
			$change = 0;
			$nouveau = 0;
			foreach ($_POST['exif_param_section'] as $id => $section) {

				if (!isset($_POST['exif_param_format'][$id])
				 || empty($_POST['exif_param_methode'][$id])
				 || empty($_POST['exif_param_description'][$id])
				 || !preg_match('`^(simple|date|nombre|liste|version)$`', $_POST['exif_param_methode'][$id])
				 || !preg_match($masque_libre_etendu, $_POST['exif_param_description'][$id])) {
					continue;
				}

				// Est-ce que le tag existe dans la section spécifiée ?
				if (isset($exif_params[$section]) && isset($exif_params[$section][$_POST['exif_param_tag'][$id]])) {

					// Etat.
					if (isset($_POST['exif_param'][$id]) && !$exif_params[$section][$_POST['exif_param_tag'][$id]]['active']) {
						$exif_params[$section][$_POST['exif_param_tag'][$id]]['active'] = 1;
						$change = 1;
					} elseif (empty($_POST['exif_param'][$id]) && $exif_params[$section][$_POST['exif_param_tag'][$id]]['active']) {
						$exif_params[$section][$_POST['exif_param_tag'][$id]]['active'] = 0;
						$change = 1;
					}

					// Description.
					if ($_POST['exif_param_description'][$id] != $exif_params[$section][$_POST['exif_param_tag'][$id]]['desc']) {
						$exif_params[$section][$_POST['exif_param_tag'][$id]]['desc'] = $_POST['exif_param_description'][$id];
						$change = 1;
					}

					// Méthode.
					if ($_POST['exif_param_methode'][$id] != $exif_params[$section][$_POST['exif_param_tag'][$id]]['method']) {
						$exif_params[$section][$_POST['exif_param_tag'][$id]]['method'] = $_POST['exif_param_methode'][$id];
						$change = 1;
					}

					// Format : liste.
					if ($_POST['exif_param_methode'][$id] == 'liste' && isset($_POST['exif_param_liste'][$id]) 
 					 && isset($_POST['exif_param_liste'][$id]['tag']) && isset($_POST['exif_param_liste'][$id]['display'])) {
						$liste = array();
						foreach ($_POST['exif_param_liste'][$id]['tag'] as $id_list => $value) {
							if (preg_match($masque_libre_court, $value) && preg_match($masque_libre_etendu, $id_list)) {
								$liste[$value] = $_POST['exif_param_liste'][$id]['display'][$id_list];
							}
						}
						if ($liste != @$exif_params[$section][$_POST['exif_param_tag'][$id]]['format']) {
							$exif_params[$section][$_POST['exif_param_tag'][$id]]['format'] = $liste;
							$change = 1;
						}
					}

					// Format : normal.
					if (($_POST['exif_param_methode'][$id] == 'date' 
					  || $_POST['exif_param_methode'][$id] == 'simple'
					  || $_POST['exif_param_methode'][$id] == 'nombre')
 					 && isset($_POST['exif_param_format'][$id])
					 && preg_match($masque_libre_court_zero, $_POST['exif_param_format'][$id])
					 && $_POST['exif_param_format'][$id] != @$exif_params[$section][$_POST['exif_param_tag'][$id]]['format']) {
						if (empty($_POST['exif_param_format'][$id])) {
							unset($exif_params[$section][$_POST['exif_param_tag'][$id]]['format']);
						} else {
							$exif_params[$section][$_POST['exif_param_tag'][$id]]['format'] = $_POST['exif_param_format'][$id];
						}
						$change = 1;
					}

					// Format : version.
					if ($_POST['exif_param_methode'][$id] == 'version' && isset($exif_params[$section][$_POST['exif_param_tag'][$id]]['format'])) {
						unset($exif_params[$section][$_POST['exif_param_tag'][$id]]['format']);
						$change = 1;
					}

				// Nouveau tag.
				} elseif (!empty($section) && preg_match('`^(COMPUTED|IFD0|THUMBNAIL|EXIF|INTEROP)$`', $section) && !empty($_POST['exif_param_tag'][$id])) {
					$etat = (isset($_POST['exif_param'][$id])) ? 1 : 0;
					$exif_params[$section][$_POST['exif_param_tag'][$id]]['active'] = $etat;
					$exif_params[$section][$_POST['exif_param_tag'][$id]]['desc'] = $_POST['exif_param_description'][$id];
					$exif_params[$section][$_POST['exif_param_tag'][$id]]['method'] = $_POST['exif_param_methode'][$id];
					switch ($_POST['exif_param_methode'][$id]) {
						case 'simple' :
						case 'nombre' :
						case 'date' :
							if (!empty($_POST['exif_param_format'][$id]) && preg_match($masque_libre_etendu, $_POST['exif_param_format'][$id])) {
								$exif_params[$section][$_POST['exif_param_tag'][$id]]['format'] = $_POST['exif_param_format'][$id];
							}
							break;
						case 'liste' :
							if (isset($_POST['exif_param_liste'][$id]['tag']) && isset($_POST['exif_param_liste'][$id]['display'])) {
								$liste = array();
								foreach ($_POST['exif_param_liste'][$id]['tag'] as $id_list => $value) {
									if (preg_match($masque_libre_court, $value) && preg_match($masque_libre_etendu, $id_list)) {
										$liste[$value] = $_POST['exif_param_liste'][$id]['display'][$id_list];
									}
								}
								$exif_params[$section][$_POST['exif_param_tag'][$id]]['format'] = $liste;
							}
							break;
					}
					$change = 1;
					$nouveau = 1;
				}

				if ($change) {
					$modif = 1;
					$change = 0;
				}
			}

			// On supprime un ancien tag.
			if ($nouveau) {
				if (isset($_POST['exif_param_section']) && isset($_POST['exif_param_tag'])) {
					$params_post = array();
					for ($i = 0; $i < count($_POST['exif_param_section']); $i++) {
						$params_post[$_POST['exif_param_section'][$i]][$_POST['exif_param_tag'][$i]] = 1;
					}
					foreach ($exif_params as $section => $tags) {
						foreach ($tags as $tag => $params) {
							if (empty($params_post[$section][$tag])) {
								unset($exif_params[$section][$tag]);
							}
						}
					}
				}
			}

			// On met à jour les paramètres EXIF dans la base de données et dans le tableau de template.
			if ($modif) {
				$new_exif_params = preg_replace('`[\x5c]`', '', serialize($exif_params));
				$this->update_option('galerie_exif_params', $new_exif_params);
				$this->template['action_maj'] = 1;
				$this->options_exif_sort($exif_params, 'COMPUTED', 1);
				$this->options_exif_sort($exif_params, 'IFD0');
				$this->options_exif_sort($exif_params, 'THUMBNAIL');
				$this->options_exif_sort($exif_params, 'EXIF');
				$this->options_exif_sort($exif_params, 'INTEROP');
				unset($this->template['config']['infos_exif']);
				$this->template['config']['infos_exif'] = $this->options_exif_sort($exif_params, 'MAKERNOTE');
			}
		}
	}



	/*
	 *	Options : textes.
	*/
	function options_textes() {

		$this->template['infos']['title'] = 'textes de la galerie';

		if (isset($_POST['u'])) {

			// Titre.
			$this->verif_option_word('g_galtitre', 'galerie_titre', 'libre', '', 1);
			$this->verif_option_word('g_galtitre_court', 'galerie_titre_court', 'libre', '', 1);

			// Message d'accueil.
			if ($this->template['enabled']['message_accueil']) {
				$this->verif_option_word('g_accueil', 'galerie_message_accueil', 'libre', '', 1);
			}

			// Footer.
			if ($this->template['enabled']['message_accueil']) {
				$this->verif_option_word('g_msg_footer_txt', 'galerie_message_footer', 'libre', '', 1);
				$footer = '';
				if (isset($_REQUEST['g_msg_footer'])) {
					$footer .= '1';
				}
				if (isset($_REQUEST['g_cnt_footer'])) {
					$footer .= '2';
				}
				if (empty($footer)) {
					$footer = '0';
				}
				$_REQUEST['g_msg_footer'] = $footer;
				$this->verif_option_word('g_msg_footer', 'galerie_footer', 'nombre', '', 1);
			}

			// Page contact.
			if ($this->template['enabled']['contact']) {
				$this->verif_option_word('g_contact_text', 'galerie_contact_text', 'libre', '', 1);
				$this->verif_option_bin('g_contact', 'galerie_contact', 1);
			}

			// Message de fermeture de la galerie.
			$this->verif_option_word('g_close_text', 'galerie_message_fermeture', 'libre', '', 1);

		}

		// Config.
		$this->template['config'] = $this->config;
	}



	/*
	 *	Options : fonctionnalités.
	*/
	function options_fonctions() {

		$this->template['infos']['title'] = 'fonctionnalités';

		if (isset($_POST['u'])) {

			if ($this->template['enabled']['commentaires']) {
				$this->verif_option_bin('f_comment', 'active_commentaires', 1);
			}

			if ($this->template['enabled']['votes']) {
				$this->verif_option_bin('f_votes', 'active_votes', 1);
			}

			if ($this->template['enabled']['image_hasard']) {
				$this->verif_option_bin('f_imgh', 'galerie_image_hasard', 1);
			}

			if ($this->template['enabled']['perso']) {
				$this->verif_option_bin('f_perso', 'user_perso', 1);
			}

			if ($this->template['enabled']['adv_search']) {
				$this->verif_option_bin('f_adv_search', 'active_advsearch', 1);
			}

			if ($this->template['enabled']['historique']) {
				$this->verif_option_bin('f_historique', 'active_historique', 1);
			}

			if ($this->template['enabled']['diaporama']) {
				$this->verif_option_bin('f_diaporama', 'active_diaporama', 1);
				$this->verif_option_bin('f_diaporama_gd_resize', 'galerie_diaporama_resize', 1);
			}

			if ($this->template['enabled']['rss']) {
				$this->verif_option_bin('f_rss', 'active_rss', 1);
				$this->verif_option_word('f_rss_nb', 'galerie_nb_rss', 'nombre', '', 1);
			}

			if ($this->template['enabled']['tags']) {
				$this->verif_option_bin('f_tags', 'active_tags', 1);
				$this->verif_option_word('f_tags_nb', 'galerie_nb_tags', 'nombre', '', 1);
			}

			// Config.
			$this->template['config'] = $this->config;
		}
	}



	/*
	 *	Options : personnalisation.
	*/
	function options_perso() {

		$this->template['infos']['title'] = 'personnalisation';

		if (isset($_POST['u'])) {

			// Divers.

			if ($this->template['enabled']['perso_style']) {
				$this->verif_option_bin('g_perso_style', 'user_style', 1);
			}
			
			if ($this->template['enabled']['perso_recentes']) {
				$this->verif_option_bin('g_perso_recents', 'user_recentes', 1);
			}

			if ($this->template['enabled']['perso_image_size']) {
				$this->verif_option_bin('g_perso_ajust', 'user_image_ajust', 1);
			}

			if ($this->template['enabled']['perso_nb_thumbs']) {
				$this->verif_option_bin('g_perso_thumbs', 'user_vignettes', 1);
			}

			if ($this->template['enabled']['perso_sort_thumbs']) {
				$this->verif_option_bin('g_perso_ordre', 'user_ordre', 1);
			}

			// Informations sous les vignettes.
			if ($this->template['enabled']['perso_cat_nom']) {
				$this->verif_option_bin('g_perso_c_nom', 'user_nom_categories', 1);
			}

			if ($this->template['enabled']['perso_nb_images']) {
				$this->verif_option_bin('g_perso_c_imgs', 'user_nb_images', 1);
			}

			if ($this->template['enabled']['perso_img_nom']) {
				$this->verif_option_bin('g_perso_i_nom', 'user_nom_images', 1);
			}

			if ($this->template['enabled']['perso_date']) {
				$this->verif_option_bin('g_perso_i_date', 'user_date', 1);
			}

			if ($this->template['enabled']['perso_taille']) {
				$this->verif_option_bin('g_perso_i_taille', 'user_taille', 1);
			}

			if ($this->template['enabled']['perso_poids']) {
				$this->verif_option_bin('g_perso_i_poids', 'user_poids', 1);
			}

			if ($this->template['enabled']['perso_hits']) {
				$this->verif_option_bin('g_perso_i_hits', 'user_hits', 1);
			}

			if ($this->template['enabled']['perso_commentaires']) {
				$this->verif_option_bin('g_perso_i_comments', 'user_comments', 1);
			}

			if ($this->template['enabled']['perso_votes']) {
				$this->verif_option_bin('g_perso_i_votes', 'user_votes', 1);
			}

			// Config.
			$this->template['config'] = $this->config;
		}
	}



	/*
	 *	Outils : images.
	*/
	function outils_images() {
		$this->template['infos']['title'] = 'outils';
	
		if (isset($_GET['action'])) {

			$this->verifVID();

			// Suppression des vignettes.
			if ($_GET['action'] == 'supp_thumb') {
				if ($this->del_thumbs(THUMB_TDIR, 0)) {
					$this->template['infos']['action']['delete_thumbs'] = 'Toutes les vignettes ont été supprimées.';
				} else {
					$this->template['infos']['erreur']['sup_vignettes'] = 'Toutes les vignettes n\'ont pas pu être supprimées.';
				}

			// Vidage du répertoire cache.
			} elseif ($_GET['action'] == 'vide_cache') {
				if ($this->vidage_cache()) {
					$this->template['infos']['action']['vidage_cache'] = 'Le cache a été vidé.';
				} else {
					$this->template['infos']['erreur']['vidage_cache'] = 'Le cache n\'a pas pu être vidé.';
				}

			// Modification de la date de dernière modification de chaque répertoire.
			} elseif ($_GET['action'] == 'change_date') {
				$mysql_requete = 'SELECT categorie_chemin FROM ' . MYSQL_PREF . 'categories
					ORDER BY categorie_chemin';
				$categories = $this->mysql->select($mysql_requete);
				for ($i = 0; $i < count($categories); $i++) {
					@touch($this->galerie_dir . $categories[$i]['categorie_chemin'] . '~#~');
					files::suppFile($this->galerie_dir . $categories[$i]['categorie_chemin'] . '~#~');
				}
				$this->template['rapport'] = '<div class="rapport_msg rapport_succes"><div><span>La date de dernière modification de tous les répertoires a été changée.</span></div></div><br />';

			// Vérification des informations des albums.
			} elseif ($_GET['action'] == 'verif_infos') {
				$ok = TRUE;
				$bad_infos = array();

				// On récupère toutes les informations de toutes les catégories.
				$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'categories
					ORDER BY categorie_chemin';
				$categories = $this->mysql->select($mysql_requete);

				// On vérifie toutes les informations pour chaque catégorie.
				for ($i = 0; $i < count($categories); $i++) {

					$bad_infos[$i] = '';
					$select_path = ($categories[$i]['categorie_chemin'] == '.') ? '' : $categories[$i]['categorie_chemin'];

					// On sélectionne toutes les informations des images activées.
					$mysql_requete = 'SELECT COUNT(*),
											 SUM(image_poids),
											 SUM(image_hits),
											 SUM(image_commentaires),
											 SUM(image_votes),
											 SUM(image_note*image_votes)/SUM(image_votes)
						FROM ' . MYSQL_PREF . 'images
						WHERE image_chemin LIKE "' . $select_path . '%" 
						  AND image_visible = "1"';
					$infos_actives = $this->mysql->select($mysql_requete, 2);

					// On sélectionne toutes les informations des images désactivées.
					$mysql_requete = 'SELECT COUNT(*),
											 SUM(image_poids),
											 SUM(image_hits),
											 SUM(image_commentaires),
											 SUM(image_votes),
											 SUM(image_note*image_votes)/SUM(image_votes)
						FROM ' . MYSQL_PREF . 'images
						WHERE image_chemin LIKE "' . $select_path . '%" 
						  AND image_visible = "0"';
					$infos_inactives = $this->mysql->select($mysql_requete, 2);

					// Nombre d'images.
					$nb_images_actives = ($infos_actives[0][0]) ? $infos_actives[0][0] : 0;
					if ($nb_images_actives != $categories[$i]['categorie_images']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le nombre d\'images activées est erroné [<span class="rapport_erreur">' . $categories[$i]['categorie_images'] . '</span>/<span class="rapport_succes">' . $nb_images_actives . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_images = "' . $nb_images_actives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}

					// Nombre d'images désactivées.
					$nb_images_inactives = ($infos_inactives[0][0]) ? $infos_inactives[0][0] : 0;
					if ($nb_images_inactives != $categories[$i]['categorie_images_inactive']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le nombre d\'images désactivées est erroné [<span class="rapport_erreur">' . $categories[$i]['categorie_images_inactive'] . '</span>/<span class="rapport_succes">' . $nb_images_inactives . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_images_inactive = "' . $nb_images_inactives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}

					// Poids des images activées.
					$poids_images_actives = ($infos_actives[0][1]) ? $infos_actives[0][1] : '0.0';
					if ($poids_images_actives != $categories[$i]['categorie_poids']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le poids des images activées est erroné [<span class="rapport_erreur">' . outils::poids($categories[$i]['categorie_poids']) . '</span>/<span class="rapport_succes">' . outils::poids($poids_images_actives) . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_poids = "' . $poids_images_actives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}

					// Poids des images désactivées.
					$poids_images_inactives = ($infos_inactives[0][1]) ? $infos_inactives[0][1] : '0.0';
					if ($poids_images_inactives != $categories[$i]['categorie_poids_inactive']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le poids des images désactivées est erroné [<span class="rapport_erreur">' . outils::poids($categories[$i]['categorie_poids_inactive']) . '</span>/<span class="rapport_succes">' . outils::poids($poids_images_inactives) . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_poids_inactive = "' . $poids_images_inactives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}

					// Nombre de visites des images activées.
					$nb_hits_actives = ($infos_actives[0][2]) ? $infos_actives[0][2] : '0';
					if ($nb_hits_actives != $categories[$i]['categorie_hits']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le nombre de visites des images activées est erroné [<span class="rapport_erreur">' . $categories[$i]['categorie_hits'] . '</span>/<span class="rapport_succes">' . $nb_hits_actives . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_hits = "' . $nb_hits_actives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}

					// Nombre de visites des images désactivées.
					$nb_hits_inactives = ($infos_inactives[0][2]) ? $infos_inactives[0][2] : '0';
					if ($nb_hits_inactives != $categories[$i]['categorie_hits_inactive']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le nombre de visites des images désactivées est erroné [<span class="rapport_erreur">' . $categories[$i]['categorie_hits_inactive'] . '</span>/<span class="rapport_succes">' . $nb_hits_inactives . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_hits_inactive = "' . $nb_hits_inactives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}

					// Nombre de commentaires des images activées.
					$nb_comments_actives = ($infos_actives[0][3]) ? $infos_actives[0][3] : '0';
					if ($nb_comments_actives != $categories[$i]['categorie_commentaires']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le nombre de commentaires des images activées est erroné [<span class="rapport_erreur">' . $categories[$i]['categorie_commentaires'] . '</span>/<span class="rapport_succes">' . $nb_comments_actives . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_commentaires = "' . $nb_comments_actives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}

					// Nombre de commentaires des images désactivées.
					$nb_comments_inactives = ($infos_inactives[0][3]) ? $infos_inactives[0][3] : '0';
					if ($nb_comments_inactives != $categories[$i]['categorie_commentaires_inactive']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le nombre de commentaires des images désactivées est erroné [<span class="rapport_erreur">' . $categories[$i]['categorie_commentaires_inactive'] . '</span>/<span class="rapport_succes">' . $nb_comments_inactives . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_commentaires_inactive = "' . $nb_comments_inactives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}

					// Nombre de votes et note des images activées.
					$nb_votes_actives = ($infos_actives[0][4]) ? $infos_actives[0][4] : '0';
					$note_actives = ($infos_actives[0][5]) ? $infos_actives[0][5] : '0';
					if ($nb_votes_actives != $categories[$i]['categorie_votes']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le nombre de votes des images activées est erroné [<span class="rapport_erreur">' . $categories[$i]['categorie_votes'] . '</span>/<span class="rapport_succes">' . $nb_votes_actives . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_votes = "' . $nb_votes_actives . '",
								categorie_note = "' . $note_actives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}

					// Nombre de votes et note des images désactivées.
					$nb_votes_inactives = ($infos_inactives[0][4]) ? $infos_inactives[0][4] : '0';
					$note_inactives = ($infos_inactives[0][5]) ? $infos_inactives[0][5] : '0';
					if ($nb_votes_inactives != $categories[$i]['categorie_votes_inactive']) {
						$ok = FALSE;
						$bad_infos[$i] .= '<div class="verifinfos_err">Le nombre de votes des images désactivées est erroné [<span class="rapport_erreur">' . $categories[$i]['categorie_votes_inactive'] . '</span>/<span class="rapport_succes">' . $nb_votes_inactives . '</span>]</div>';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET categorie_votes_inactive = "' . $nb_votes_inactives . '",
								categorie_note_inactive = "' . $note_inactives . '"
							WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$bad_infos[$i] .= '<span class="rapport_succes">=> Information corrigée.</span>';
						} else {
							$bad_infos[$i] .= '<span class="rapport_erreur">=> La mise à jour a échouée.</span>';
						}
					}
				}
				if ($ok) {
					$this->template['rapport'] = '<div class="rapport_msg rapport_succes" id="verifinfos_ok"><div><span>Toutes les informations enregistrées semblent correctes.</span></div></div><br />';
				} else {
					$this->template['rapport'] = '<table id="verifinfos_errors">';
					$this->template['rapport'] .= "\r\t\t\t\t" . '<tr><th>album ou catégorie</th><th>problème</th></tr>';
					foreach ($bad_infos as $k => $v) {
						if ($v) {
							$cat_nom = ($categories[$k]['categorie_nom'] == '') ? '<strong>galerie</strong>' : $categories[$k]['categorie_nom'];
							$cat_chemin = ($categories[$k]['categorie_nom'] == '') ? '' : ' title="' . $categories[$k]['categorie_chemin'] . '"';
							$this->template['rapport'] .= "\r\t\t\t\t" . '<tr><td class="verifinfos_cat"><a href="index.php?section=galerie&amp;page=gestion&amp;cat=' . $categories[$k]['categorie_id'] . '"' . $cat_chemin . '>' . $cat_nom . '</a></td><td class="verifinfos_prb">' . $v . '</td></tr>';
						}
					}
					$this->template['rapport'] .= "\r\t\t\t" . '</table>';
				}

			// Vérification de l'intégrité de la table des images.
			} elseif ($_GET['action'] == 'repare_images') {

				// Vérification et réparation du champ categorie_parent_id.
				$mysql_requete = 'SELECT categorie_parent_id FROM ' . MYSQL_PREF . 'images WHERE categorie_parent_id = "0" LIMIT 1';
				if ($this->mysql->select($mysql_requete) != 'vide') {
					$mysql_requete = 'SELECT categorie_id,categorie_chemin FROM ' . MYSQL_PREF . 'categories
						WHERE categorie_derniere_modif != "0"';
					$albums = $this->mysql->select($mysql_requete);
					$ok = TRUE;
					for ($i = 0; $i < count($albums); $i++) {
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images 
											 SET categorie_parent_id = "' . $albums[$i]['categorie_id'] . '"
										   WHERE image_chemin LIKE "' . $albums[$i]['categorie_chemin'] . '%" 
										     AND categorie_parent_id = "0"';
						if (!$this->mysql->requete($mysql_requete)) {
							$ok = FALSE;
						}
					}
					if ($ok) {
						$this->template['rapport'] = '<div class="rapport_msg rapport_succes" id="verifinfos_ok"><div><span>La table des images a été réparée.</span></div></div><br />';
					} else {
						$this->template['rapport'] = '<div class="rapport_msg rapport_erreur" id="verifinfos_ok"><div><span>La réparation de la table des images à échouée.</span></div></div><br />';
					}
				} else {
					$this->template['rapport'] = '<div class="rapport_msg rapport_succes" id="verifinfos_ok"><div><span>La table des images semble intacte.</span></div></div><br />';
				}

			// Optimisation des tables.
			} elseif ($_GET['action'] == 'optimize_tables') {

				$ok = true;

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'config';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'images';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'categories';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'commentaires';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'votes';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'tags';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'membres';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'groupes';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'images_attente';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				$mysql_requete = 'OPTIMIZE TABLE ' . MYSQL_PREF . 'favoris';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				if ($ok) {
					$this->template['rapport'] = '<div class="rapport_msg rapport_succes" id="verifinfos_ok"><div><span>Toutes les tables ont été optimisées.</span></div></div><br />';
				} else {
					$this->template['rapport'] = '<div class="rapport_msg rapport_erreur" id="verifinfos_ok"><div><span>L\'optimisation des tables a échouée.</span></div></div><br />';
				}
			}
		}
	}



	/*
	 *	Informations de galerie.
	*/
	function infos() {

		$this->template['infos']['title'] = 'infos galerie';

		// Nombre d'images, de commentaires, de hits, de votes et poids.
		$mysql_requete = 'SELECT categorie_poids,
					 categorie_images,
					 categorie_hits,
					 categorie_commentaires,
					 categorie_votes FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_id = "1"';
		$nb_infos = $this->mysql->select($mysql_requete, 11);

		// Nombre d'images, de commentaires, de hits, de votes et poids des images désactivées.
		$mysql_requete = 'SELECT categorie_poids_inactive,
					 categorie_images_inactive,
					 categorie_hits_inactive,
					 categorie_commentaires_inactive,
					 categorie_votes_inactive FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_id = "1"';
		$nb_infos_inactive = $this->mysql->select($mysql_requete, 11);

		// Nombre d'albums activés.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_derniere_modif > 0
			  AND categorie_visible = "1"';
		$nb_albums = $this->mysql->select($mysql_requete, 5);

		// Nombre d'albums désactivés.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_derniere_modif > 0 
			  AND categorie_visible != "1"';
		$nb_albums_inactive = $this->mysql->select($mysql_requete, 5);

		// Nombre d'albums protégés par un mot de passe activés.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_derniere_modif > 0
			  AND categorie_pass REGEXP "^.+$"
			  AND categorie_visible = "1"';
		$nb_albums_pass = $this->mysql->select($mysql_requete, 5);

		// Nombre d'albums protégés par un mot de passe désactivés.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_derniere_modif > 0 
			  AND categorie_pass REGEXP "^.+$" 
			  AND categorie_visible != "1"';
		$nb_albums_pass_inactive = $this->mysql->select($mysql_requete, 5);

		// Nombre de catégories activées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_derniere_modif = "0" 
			  AND categorie_id NOT IN (1)
			  AND categorie_visible = "1"';
		$nb_categories = $this->mysql->select($mysql_requete, 5);

		// Nombre de catégories désactivées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_derniere_modif = "0" 
			  AND categorie_id NOT IN (1)
			  AND categorie_visible != "1"';
		$nb_categories_inactive = $this->mysql->select($mysql_requete, 5);

		// Nombre de catégories protégées par un mot de passe activées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_derniere_modif = "0" 
			  AND categorie_id NOT IN (1)
			  AND categorie_pass REGEXP "^.+$"
			  AND categorie_visible = "1"';
		$nb_categories_pass = $this->mysql->select($mysql_requete, 5);

		// Nombre de catégories protégées par un mot de passe désactivées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_derniere_modif = "0" 
			  AND categorie_id NOT IN (1)
			  AND categorie_visible != "1"
			  AND categorie_pass REGEXP "^.+$"';
		$nb_categories_pass_inactive = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images visitées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'images 
			WHERE image_hits > 0
			  AND image_visible = "1"';
		$zero_hits = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images visitées désactivées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'images 
			WHERE image_hits > 0 
			  AND image_visible = "0"';
		$zero_hits_inactive = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images commentées activées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'images 
			WHERE image_commentaires > 0
			  AND image_visible = "1"';
		$imgs_comments = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images commentées désactivées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'images 
			WHERE image_commentaires > 0 
			  AND image_visible = "0"';
		$imgs_comments_inactive = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images votées activées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'images 
			WHERE image_votes > 0
			  AND image_visible = "1"';
		$imgs_vote = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images votées désactivées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'images 
			WHERE image_votes > 0 
		        AND image_visible = "0"';
		$imgs_vote_inactive = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images protégées par un mot de passe activées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'images 
			WHERE image_pass REGEXP "^.+$"
			  AND image_visible = "1"';
		$imgs_pass = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images protégées par un mot de passe désactivées.
		$mysql_requete = 'SELECT count(*) FROM ' . MYSQL_PREF . 'images 
			WHERE image_pass REGEXP "^.+$" 
			  AND image_visible = "0"';
		$imgs_pass_inactive = $this->mysql->select($mysql_requete, 5);

		// Tags activés.
		$mysql_requete = 'SELECT count(DISTINCT ' . MYSQL_PREF . 'tags.tag_id) 
						    FROM ' . MYSQL_PREF . 'tags INNER JOIN ' . MYSQL_PREF . 'images USING(image_id)
						   WHERE ' . MYSQL_PREF . 'images.image_visible = "1"';
		$tags = $this->mysql->select($mysql_requete, 5);

		// Tags désactivés.
		$mysql_requete = 'SELECT count(DISTINCT ' . MYSQL_PREF . 'tags.tag_id) 
						    FROM ' . MYSQL_PREF . 'tags INNER JOIN ' . MYSQL_PREF . 'images USING(image_id)
						   WHERE ' . MYSQL_PREF . 'images.image_visible = "0"';
		$tags_inactive = $this->mysql->select($mysql_requete, 5);

		// Total de tags.
		$mysql_requete = 'SELECT count(DISTINCT ' . MYSQL_PREF . 'tags.tag_id) 
					FROM ' . MYSQL_PREF . 'tags INNER JOIN ' . MYSQL_PREF . 'images USING(image_id)';
		$tags_total = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images taggées activées.
		$mysql_requete = 'SELECT count(DISTINCT ' . MYSQL_PREF . 'images.image_id) 
						    FROM ' . MYSQL_PREF . 'tags INNER JOIN ' . MYSQL_PREF . 'images USING(image_id)
						   WHERE ' . MYSQL_PREF . 'images.image_visible = "1"
							 AND ' . MYSQL_PREF . 'images.image_tags != ""';
		$imgs_tags = $this->mysql->select($mysql_requete, 5);

		// Nombre d'images taggées déactivées.
		$mysql_requete = 'SELECT count(DISTINCT ' . MYSQL_PREF . 'images.image_id) 
						    FROM ' . MYSQL_PREF . 'tags INNER JOIN ' . MYSQL_PREF . 'images USING(image_id)
						   WHERE ' . MYSQL_PREF . 'images.image_visible = "0"
							 AND ' . MYSQL_PREF . 'images.image_tags != ""';
		$imgs_tags_inactive = $this->mysql->select($mysql_requete, 5);

		// Tableau de tous ces nombres.
		$g_stats = '<table class="g_infos" id="g_infos_stats">';
		$g_stats .= '<tr><th class="g_tpr"></th><th>activés</th><th>désactivés</th><th>total</th></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre d\'images</td><td>' . $nb_infos['categorie_images'] . '</td><td>' . $nb_infos_inactive['categorie_images_inactive'] . '</td><td>' . ($nb_infos['categorie_images'] + $nb_infos_inactive['categorie_images_inactive']) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Poids total des images</td><td>' . outils::poids($nb_infos['categorie_poids']) . '</td><td>' . outils::poids($nb_infos_inactive['categorie_poids_inactive']) . '</td><td>' . outils::poids(($nb_infos['categorie_poids'] + $nb_infos_inactive['categorie_poids_inactive'])) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre d\'albums</td><td>' . $nb_albums . '</td><td>' . $nb_albums_inactive . '</td><td>' . ($nb_albums + $nb_albums_inactive) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre d\'albums protégés</td><td>' . $nb_albums_pass . '</td><td>' . $nb_albums_pass_inactive . '</td><td>' . ($nb_albums_pass + $nb_albums_pass_inactive) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre de catégories</td><td>' . $nb_categories . '</td><td>' . $nb_categories_inactive . '</td><td>' . ($nb_categories + $nb_categories_inactive) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre de catégories protégées</td><td>' . $nb_categories_pass . '</td><td>' . $nb_categories_pass_inactive . '</td><td>' . ($nb_categories_pass + $nb_categories_pass_inactive) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre de visites</td><td>' . $nb_infos['categorie_hits'] . '</td><td>' . $nb_infos_inactive['categorie_hits_inactive'] . '</td><td>' . ($nb_infos['categorie_hits'] + $nb_infos_inactive['categorie_hits_inactive']) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre d\'images visitées</td><td>' . $zero_hits . '</td><td>' . $zero_hits_inactive . '</td><td>' . ($zero_hits + $zero_hits_inactive) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre de commentaires</td><td>' . $nb_infos['categorie_commentaires'] . '</td><td>' . $nb_infos_inactive['categorie_commentaires_inactive'] . '</td><td>' . ($nb_infos['categorie_commentaires'] + $nb_infos_inactive['categorie_commentaires_inactive']) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre d\'images commentées</td><td>' . $imgs_comments . '</td><td>' . $imgs_comments_inactive . '</td><td>' . ($imgs_comments + $imgs_comments_inactive) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre de votes</td><td>' . $nb_infos['categorie_votes'] . '</td><td>' . $nb_infos_inactive['categorie_votes_inactive'] . '</td><td>' . ($nb_infos['categorie_votes'] + $nb_infos_inactive['categorie_votes_inactive']) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre d\'images notées</td><td>' . $imgs_vote . '</td><td>' . $imgs_vote_inactive . '</td><td>' . ($imgs_vote + $imgs_vote_inactive) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre d\'images protégées</td><td>' . $imgs_pass . '</td><td>' . $imgs_pass_inactive . '</td><td>' . ($imgs_pass + $imgs_pass_inactive) . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre de tags distincts</td><td>' . $tags . '</td><td>' . $tags_inactive . '</td><td>' . $tags_total . '</td></tr>';
		$g_stats .= '<tr><td class="g_tpr">Nombre d\'images taggées</td><td>' . $imgs_tags . '</td><td>' . $imgs_tags_inactive . '</td><td>' . ($imgs_tags + $imgs_tags_inactive) . '</td></tr>';
		$g_stats .= '</table>';

		$this->template['infos']['g_stats'] = $g_stats;
	}



	/*
	 *	Activation d'un objet de la galerie.
	*/
	function galerie_action_active($infos) {

		// On ne peut pas activer une catégorie vide.
		if ($_REQUEST['type'] == 'categorie' && ($infos['categorie_images'] + $infos['categorie_images_inactive']) == 0) {
			return;
		}

		// On active l'objet que s'il est désactivé.
		if (empty($infos[$_REQUEST['type'] . '_visible'])) {
			if ($this->objet_update_nb('active', $infos)) {
				$this->template['action_maj'] = 1;
			} else {
				$this->template['infos']['erreur']['desactive'] = '[' . __LINE__ . '] Des erreurs se sont produites lors de cette opération.';
			}
		}

		// On vérifie le représentant de chaque catégorie parente.
		$this->verif_representant($infos[$_REQUEST['type'] . '_chemin']);
	}



	/*
	 *	Désactivation d'un objet de la galerie.
	*/
	function galerie_action_desactive($infos) {

		// On désactive l'objet que s'il est activé.
		if (!empty($infos[$_REQUEST['type'] . '_visible'])) {
			if ($this->objet_update_nb('desactive', $infos)) {
				$this->template['action_maj'] = 1;
			} else {
				$this->template['infos']['erreur']['desactive'] = '[' . __LINE__ . '] Des erreurs se sont produites lors de cette opération.';
			}
		}

		// On vérifie le représentant de chaque catégorie parente.
		$this->verif_representant($infos[$_REQUEST['type'] . '_chemin']);
	}



	/*
	 *	Suppression d'un objet de la galerie.
	*/
	function galerie_action_delete($infos, $objet = '', $fm = '') {

		static $deletes = array();

		// On UPDATE les informations des catégores parentes.
		$this->objet_update_nb('supprime', $infos, $objet);

		// On supprime les tags liés.
		if (isset($infos['categorie_nom'])) {
			$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'tags 
									USING ' . MYSQL_PREF . 'tags,
										  ' . MYSQL_PREF . 'images
									WHERE ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $infos['categorie_chemin'] . '%"
									  AND ' . MYSQL_PREF . 'tags.image_id = ' . MYSQL_PREF . 'images.image_id';
			$this->mysql->requete($mysql_requete);

		} elseif (!empty($infos['image_tags'])) {
			$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'tags
							   WHERE image_id = "' . $infos['image_id'] . '%"';
			$this->mysql->requete($mysql_requete);
		}

		// On supprime la référence de l'objet de la base de données,
		// ainsi que celles de tous ses éventuels objets enfants s'il s'agit d'une catégorie.
		if ($_REQUEST['type'] == 'image') {
			$mysql_requete = 'DELETE FROM '. MYSQL_PREF . 'images 
				WHERE image_id = "' . $infos['image_id'] . '"';
			$this->mysql->requete($mysql_requete);

		} else {
			$mysql_requete = 'DELETE FROM '. MYSQL_PREF . 'categories 
				WHERE categorie_chemin LIKE "' . $infos['categorie_chemin'] . '%"';
			$this->mysql->requete($mysql_requete);

			$mysql_requete = 'DELETE FROM '. MYSQL_PREF . 'images 
				WHERE image_chemin LIKE "' . $infos['categorie_chemin'] . '%"';
			$this->mysql->requete($mysql_requete);
		}

		// On supprime tous les commentaires de l'objet.
		$mysql_requete = 'DELETE ' . MYSQL_PREF . 'commentaires
							FROM ' . MYSQL_PREF . 'commentaires,
								 ' . MYSQL_PREF . 'images
						   WHERE ' . MYSQL_PREF . 'commentaires.image_id = ' . MYSQL_PREF . 'images.image_id
							 AND ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $infos[$_REQUEST['type'] . '_chemin'] . '%"';
		$this->mysql->requete($mysql_requete);

		// On supprime tous les votes de l'objet.
		$mysql_requete = 'DELETE ' . MYSQL_PREF . 'votes
							FROM ' . MYSQL_PREF . 'votes,
								 ' . MYSQL_PREF . 'images
						   WHERE ' . MYSQL_PREF . 'votes.image_id = ' . MYSQL_PREF . 'images.image_id
						     AND ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $infos[$_REQUEST['type'] . '_chemin'] . '%"';
		$this->mysql->requete($mysql_requete);

		// On vérifie le représentant de chaque catégorie parente.
		$this->verif_representant($infos[$_REQUEST['type'] . '_chemin']);

		// On supprime l'objet sur le disque.
		$file = $this->galerie_dir . $infos[$_REQUEST['type'] . '_chemin'];
		if ($_REQUEST['type'] == 'image') {

			// On supprime l'image.
			if (files::suppFile($file)) {
				if ($objet) {
					$this->template['infos']['action']['delete'] = $objet . ' « ' . strip_tags($infos[$_REQUEST['type'] . '_nom']) . ' » a été supprimée.';
				} else {
					$deletes[1] = 1;
				}
			} else {
				if ($objet) {
					$this->template['infos']['info']['delete'] = $objet . ' « ' . strip_tags($infos[$_REQUEST['type'] . '_nom']) . ' » n\'a pas pu être supprimée du disque.<br />Vous devrez la supprimer manuellement par FTP.';
				} else {
					$deletes[0] = 1;
				}
			}

			// On supprime la vignette de l'image, si elle existe.
			$thumb = $this->galerie_dir . 
				dirname($infos[$_REQUEST['type'] . '_chemin']) . '/' . 
				THUMB_TDIR . '/' . THUMB_PREF . basename($infos[$_REQUEST['type'] . '_chemin']);
			if (file_exists($thumb)) {
				files::suppFile($thumb);
			}
		} else {

			// On supprime le répertoire et tout ce qu'il contient correspondant à la catégorie.
			if ($this->delete_dir($file)) {

				if ($objet) {
					$this->template['infos']['action']['delete'] = $objet . ' « ' . strip_tags($infos[$_REQUEST['type'] . '_nom']) . ' » a été supprimé' . $fm . '.';
				} else {
					$deletes[1] = 1;
				}
			} else {

				if ($objet) {
					$this->template['infos']['info']['delete'] = $objet . ' « ' . strip_tags($infos[$_REQUEST['type'] . '_nom']) . ' » n\'a pas pu être supprimé du disque.<br />Vous devrez le supprimer manuellement par FTP.';
				} else {
					$deletes[0] = 1;
				}
			}
		}

		// On renvoie vers la première page.
		$_REQUEST['startnum'] = 0;

		// On renvoie le tableau des objets supprimés pour le traitement par lot.
		if (!$objet) {
			return $deletes;
		}
	}



	/*
	 *	Actions sur la galerie.
	*/
	function galerie_action() {

		// Vérification de l'ID de session dans les formulaires.
		if (isset($_GET['supprime']) || isset($_GET['active']) || isset($_GET['desactive'])) {
			$this->verifVID();
		}

		// Traitement par lot : bouton valider.
		if (isset($_POST['mass_change'])
		 && isset($_POST['nom']) && is_array($_POST['nom']) 
		 && isset($_POST['description']) && is_array($_POST['description']) 
		 && isset($_POST['f_type']) && is_array($_POST['f_type']) 
		 && (empty($_POST['reinit_hits']) 
		  || (isset($_POST['reinit_hits']) && is_array($_POST['reinit_hits'])))
		 && ((isset($_POST['file_name']) && is_array($_POST['file_name']) &&
				isset($_POST['tags']) && is_array($_POST['tags']) &&
				isset($_POST['date_creation_jour']) && is_array($_POST['date_creation_jour']) &&
				isset($_POST['date_creation_mois']) && is_array($_POST['date_creation_mois']) &&
				isset($_POST['date_creation_annee']) && is_array($_POST['date_creation_annee'])) 
		  || (isset($_POST['password']) && is_array($_POST['password'])))) {

			// S'agit-il d'images ou de catégories ?
			$table = (current($_POST['f_type']) == 'img') ? 'images' : 'categories';
			$champ = (current($_POST['f_type']) == 'img') ? 'image' : 'categorie';

			// On récupère l'identifiant de chaque objet.
			$ids = '';
			foreach ($_POST['nom'] as $id => $nom) {
				if (!is_int($id)) {
					return;
				}
				$ids .= $champ . '_id = "' . $id . '" OR ';
			}
			$ids = preg_replace('`OR $`', '', $ids);
			if (!$ids) {
				return;
			}

			// On récupère les informations de chaque objet.
			$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . $table . ' WHERE ' . $ids;
			$objets = $this->mysql->select($mysql_requete);
			if (!is_array($objets)) {
				return;
			}

			if (current($_POST['f_type']) == 'img') {
				$type = 'L\'image';
				$type2 = 'une image';
				$type3 = 'image';
				$fm = 'e';
			}

			// Initialisation des variables de remise à zéro du nombre de hits des catégories parentes.
			$update_hits = 0;
			$update_nb_hits = 0;
			$update_nb_hits_inactive = 0;

			// Les années 2000...
			for ($y2ks = '', $y = 2000; $y <= date('Y'); $y++) $y2ks .= '|' . $y;

			$insert_tags = '';
			$delete_tags = '';

			// On traite chaque objet.
			for ($i = 0; $i < count($objets); $i++) {

				$objet_id = $objets[$i][$champ . '_id'];

				// On initialise la variable qui contiendra les informations à updater
				// sous forme de bouts de requêtes MySQL.
				$sets = '';

				if (current($_POST['f_type']) != 'img') {
					$type = ($objets[$i]['categorie_derniere_modif']) ? 'L\'album' : 'La catégorie';
					$type2 = ($objets[$i]['categorie_derniere_modif']) ? 'un album' : 'une catégorie';
					$type3 = ($objets[$i]['categorie_derniere_modif']) ? 'album' : 'catégorie';
					$fm = ($objets[$i]['categorie_derniere_modif']) ? '' : 'e';
				}

				// Changement du nom.
				if (isset($_POST['nom'][$objet_id]) && $_POST['nom'][$objet_id] != $objets[$i][$champ . '_nom']) {

					if (strlen($_POST['nom'][$objet_id]) < 1
					 || strlen($_POST['nom'][$objet_id]) > 255) {
						$this->template['infos']['attention']['nom_' . $objet_id] = '[' . $type3 . ' ' . $objet_id  . '] : Le nom doit faire entre 1 et 255 caractères.';
					} else {

						// On renomme l'objet que s'il n'y a pas un objet portant déjà le même nom.
						$path = dirname($objets[$i][$champ . '_chemin']) . '/';
						$path = ($path == './') ? '' : $path;
						$mysql_requete = 'SELECT ' . $champ . '_nom FROM ' . MYSQL_PREF . $table . ' 
							WHERE ' . $champ . '_id NOT IN ("' . $objet_id . '") AND ' . 
								$champ . '_nom = "' . outils::protege_mysql($_POST['nom'][$objet_id], $this->mysql->lien) . '" AND ' .
								$champ . '_chemin LIKE "' . $path . '%"';
						if ($this->mysql->select($mysql_requete, 11) != 'vide') {
							$this->template['infos']['attention']['rename_' . $objet_id] = '[' . $type3 . ' ' . $objet_id  . '] : Il y a déjà ' . $type2 . ' portant le même nom.';
						} else {
							$sets .= $champ . '_nom = "' . outils::protege_mysql($_POST['nom'][$objet_id], $this->mysql->lien) . '", ';
						}

					}
				}

				// Changement de nom de fichier.
				if (isset($_POST['file_name'])&& $_POST['file_name'][$objet_id] != basename($objets[$i]['image_chemin'])) {
					if (preg_match('`^([-_a-z0-9.]){1,80}\.(?:jpe?g|gif|png)$`i', $_POST['file_name'][$objet_id]) ) {
						$f_ancien = $this->galerie_dir . $objets[$i]['image_chemin'];
						$f_nouveau = $this->galerie_dir . dirname($objets[$i]['image_chemin']) . '/' . $_POST['file_name'][$objet_id];
						if (file_exists($f_nouveau) && strtolower($f_ancien) != strtolower($f_nouveau)) {
							$this->template['infos']['attention']['file_rename_' . $objet_id] = '[image ' . $objet_id  . '] : Il y a déjà un fichier portant le même nom.';
						} else {
							files::chmodFile($f_ancien);
							if (files::rename($f_ancien, $f_nouveau)) {
								$f_tb_ancien = $this->galerie_dir . dirname($objets[$i]['image_chemin']) . '/' . THUMB_TDIR . '/' . THUMB_PREF . basename($objets[$i]['image_chemin']);
								$f_tb_nouveau = $this->galerie_dir . dirname($objets[$i]['image_chemin']) . '/' . THUMB_TDIR . '/' . THUMB_PREF . $_POST['file_name'][$objet_id];
								files::rename($f_tb_ancien, $f_tb_nouveau);
								$sets .= $champ . '_chemin = "' . dirname($objets[$i]['image_chemin']) . '/' . $_POST['file_name'][$objet_id] . '", ';
							} else {
								$this->template['infos']['erreur']['file_rename_' . $objet_id] = '[image ' . $objet_id  . '] : Impossible de renommer le fichier.';
							}
						}
					} else {
						$this->template['infos']['attention']['rename_' . $objet_id] = '[' . $type3 . ' ' . $objet_id  . '] : Le nom de fichier doit être constitué de 1 à 80 caractères alphanumériques, tirets (-) ou soulignés (_).';
					}
				}

				// Changement de la description.
				if (isset($_POST['description'][$objet_id]) && $_POST['description'][$objet_id] != $objets[$i][$champ . '_description']) {
					if (strlen($_POST['description'][$objet_id]) > 65535) {
						$this->template['infos']['attention']['description_' . $objet_id] = '[' . $type3 . ' ' . $objet_id  . '] : La description ne doit pas dépasser 65535 caractères.';
					} else {
						$sets .= $champ . '_description = "' . outils::protege_mysql($_POST['description'][$objet_id], $this->mysql->lien) . '", ';
					}
				}

				// Changement des tags.
				if (isset($_POST['tags'])) {
					if (strlen($_POST['tags'][$objet_id]) < 10000) {

						// Tags existants.
						$tags_actuels = array();
						if ($objets[$i]['image_tags']) {
							$tags_actuels = $objets[$i]['image_tags'];
							$tags_actuels = $tags_actuels;
							$tags_actuels = explode(',', $tags_actuels);
							sort($tags_actuels);
						}

						// Tags envoyés.
						$new_tags = $_POST['tags'][$objet_id];
						$new_tags = preg_replace('`[\r\n\x5c#]+`', '', $new_tags);
						$new_tags = preg_replace('`\s*,\s*`', ',', $new_tags);
						$new_tags = trim($new_tags);
						$new_tags = htmlentities($new_tags);
						$new_tags = preg_split('`,`', $new_tags, -1 , PREG_SPLIT_NO_EMPTY);
						sort($new_tags);

						// On détermine les nouveaux tags et les tags à supprimer.
						if ($new_tags != $tags_actuels) {
							for ($it = 0; $it < count($new_tags); $it++) {
								if ($new_tags[$it] && !in_array($new_tags[$it], $tags_actuels)) {
									$insert_tags .= ',("' . outils::protege_mysql($new_tags[$it], $this->mysql->lien) . '","' . $objet_id . '")';
								}
							}
							for ($it = 0; $it < count($tags_actuels); $it++) {
								if (!empty($tags_actuels[$it]) && !in_array($tags_actuels[$it], $new_tags)) {
									$delete_tags .= 'OR (tag_id = "' . outils::protege_mysql($tags_actuels[$it], $this->mysql->lien) . '" AND image_id = "' . $objet_id . '")';
								}
							}
							$new_tags = outils::protege_mysql(implode(',', $new_tags), $this->mysql->lien);
							$new_tags = ($new_tags == '') ? '' : ',' . $new_tags . ',';
							$sets .= 'image_tags = "' . $new_tags  . '", ';
						}
					}
				}

				// Mot de passe.
				if (isset($_POST['password'])) {
					$_POST['password'][$objet_id] = trim($_POST['password'][$objet_id]);
					$objets[$i]['categorie_pass'] = preg_replace('`^\d+:(.+)$`', '$1', $objets[$i]['categorie_pass']);
					if (isset($_POST['password'][$objet_id]) && 
						((empty($_POST['password'][$objet_id]) && !empty($objets[$i]['categorie_pass'])) || 
						 ($_POST['password'][$objet_id] != $objets[$i]['categorie_pass']))) {

						// Le mot de passe ne doit contenir que des caractères alphanumériques
						// ou soulignés et doit faire entre 4 et 50 caractères.
						$new_pass = (empty($_POST['password'][$objet_id])) ? '_pass = NULL' : '_pass = "' . $objet_id . ':' . $_POST['password'][$objet_id] . '"';
						if (preg_match('`^(?:[0-9a-z_]{4,50})?$`i', $_POST['password'][$objet_id])) {
							$ok = true;
							$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET categorie' . $new_pass . ' 
								WHERE categorie_chemin LIKE "' . $objets[$i]['categorie_chemin'] . '%"';
							if (!$this->mysql->requete($mysql_requete)) {
								$ok = false;
							}
							$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET image' . $new_pass . ' 
								WHERE image_chemin LIKE "' . $objets[$i]['categorie_chemin'] . '%"';
							if (!$this->mysql->requete($mysql_requete)) {
								$ok = false;
							}
							if ($ok) {
								$this->template['action_maj'] = 1;
							} else {
								$this->template['infos']['erreur']['new_pass_' . $objet_id] = '[' . $type3 . ' ' . $objet_id  . '] : Un erreur s\'est produite lors de l\'affectation du mot de passe aux images.';
							}
						} else {
							$this->template['infos']['attention']['new_pass_' . $objet_id] = '[' . $type3 . ' ' . $objet_id  . '] : Le mot de passe doit contenir entre 4 et 50 caractères alphanumériques.';
						}
					}
				}

				// Date de création.
				if (isset($_POST['date_creation_jour'][$objet_id]) &&
					isset($_POST['date_creation_mois'][$objet_id]) &&
					isset($_POST['date_creation_annee'][$objet_id]) &&
					preg_match('`^(?:[1-9]|[1-2]\d|3[01])?$`', $_POST['date_creation_jour'][$objet_id]) &&
					preg_match('`^(?:[1-9]|1[0-2])?$`', $_POST['date_creation_mois'][$objet_id]) &&
					preg_match('`^(?:(?:19[7-9]\d)' . $y2ks . ')?$`', $_POST['date_creation_annee'][$objet_id])
					) {
					if ($_POST['date_creation_jour'][$objet_id] == '' ||
					    $_POST['date_creation_mois'][$objet_id] == '' ||
						$_POST['date_creation_annee'][$objet_id] == '') {
						$post_timestamp = 0;
					} else {
						$dc_seconde = 0;
						$dc_minute = 0;
						$dc_heure = 0;
						$dc_jour = $_POST['date_creation_jour'][$objet_id];
						$dc_mois = $_POST['date_creation_mois'][$objet_id];
						$dc_annee = $_POST['date_creation_annee'][$objet_id];
					}
					if (!isset($post_timestamp) && $objets[$i]['image_exif_datetimeoriginal']) {
						$dc_exif = @getdate($objets[$i]['image_exif_datetimeoriginal']);
						if ($dc_exif['mday'] == $dc_jour &&
							$dc_exif['mon'] == $dc_mois &&
							$dc_exif['year'] == $dc_annee) {
							$post_timestamp = $objets[$i]['image_exif_datetimeoriginal'];
						}
					}
					if (!isset($post_timestamp)) {
						$post_timestamp = @mktime($dc_heure, $dc_minute, $dc_seconde, $dc_mois, $dc_jour, $dc_annee);
					}
					$bd_timestamp = ($objets[$i]['image_date_creation']) ? $objets[$i]['image_date_creation'] : 0;
					if (preg_match('`^(?:\d{5,10}|0)$`', $post_timestamp) &&
						$bd_timestamp != $post_timestamp) {
						$sets .= 'image_date_creation = "' . $post_timestamp . '", ';
					}
					unset($post_timestamp);
				}

				// Remise à zéro du compteur de visites.
				if (!empty($_POST['reinit_hits'][$objet_id])) {
					$update_hits = 1;

					// On UPDATE le nombre de hits pour l'objet et, s'il s'agit d'une catégorie, 
					//de toutes ses sous-catégories éventuelles.
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . $table . ' SET ' . $champ . '_hits = "0" 
						WHERE ' . $champ . '_chemin LIKE "' . $objets[$i][$champ . '_chemin'] . '%"';
					if ($this->mysql->requete($mysql_requete)) {
						$this->template['action_maj'] = 1;

						// Si l'objet est une image et qu'elle est désactivée, alors on updatera
						// le nombre de hits des objets inactifs des catégories parentes.
						if ($_POST['f_type'][$objet_id] == 'img' && !$objets[$i][$champ . '_visible']) {
							$update_nb_hits_inactive += $objets[$i][$champ . '_hits'];
						} else {
							$update_nb_hits += $objets[$i][$champ . '_hits'];
						}

						// Si l'objet est une catégorie, on UPDATE le nombre de hits
						// pour toutes les images qu'il contient et le nombre de hits
						// des catégories inactives pour toutes ses sous-catégories.
						if ($_POST['f_type'][$objet_id] == 'cat') {
							$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET categorie_hits_inactive = "0" 
								WHERE categorie_chemin LIKE "' . $objets[$i]['categorie_chemin'] . '%"';
							$this->mysql->requete($mysql_requete);
							$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET image_hits = "0" 
								WHERE image_chemin LIKE "' . $objets[$i]['categorie_chemin'] . '%"';
							$this->mysql->requete($mysql_requete);
							$update_nb_hits_inactive += $objets[$i][$champ . '_hits_inactive'];
						}
					}
				}

				// Si changement(s), mise à jour des informations de l'objet.
				if ($sets) {
					$sets = preg_replace('`, $`', '', $sets);
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . $table . 
						' SET ' . $sets . ' WHERE ' . $champ . '_id = ' . $objet_id;
					if ($this->mysql->requete($mysql_requete)) {
						$this->template['action_maj'] = 1;
					} else {
						$this->template['infos']['erreur']['update_' . $objet_id] = '[' . $type3 . ' ' . $objet_id  . '] : Impossible de mettre les informations à jour';
					}
				}
			}

			// Modification de la table des tags.
			if ($insert_tags) {
				$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'tags(tag_id,image_id) VALUES' . substr($insert_tags, 1);
				$this->mysql->requete($mysql_requete);
			}
			if ($delete_tags) {
				$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'tags WHERE ' . substr($delete_tags, 2);
				$this->mysql->requete($mysql_requete);
			}

			// On UPDATE le nombre de hits des catégories parentes.
			if ($update_hits) {
				$path = $objets[0][$champ . '_chemin'];
				while ($path != '.') {
					$path = dirname($path);
					$path = ($path == '.') ? $path : $path . '/';
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET 
							categorie_id = categorie_id,
							categorie_hits = categorie_hits - "' . $update_nb_hits . '", 
							categorie_hits_inactive = categorie_hits_inactive - "' . $update_nb_hits_inactive . '" 
						WHERE categorie_chemin = "' . $path . '"';
					$this->mysql->requete($mysql_requete);
				}
			}
			unset($_REQUEST['f_type']);
		}

		// Traitement par lot : opérations de la liste déroulante : activer, désactiver, supprimer.
		if (isset($_POST['gal_mass_action']) && isset($_POST['action']) 
		 && isset($_POST['objet_id']) && is_array($_POST['objet_id'])) {

			// On récupère l'identifiant de chaque objet.
			$ids = '';
			$champ = (current($_POST['objet_type']) != 'image') ? 'categorie' : 'image';
			$table = (current($_POST['objet_type']) == 'image') ? 'images' : 'categories';
			$_REQUEST['type'] = $champ;
			foreach ($_POST['objet_id'] as $id => $nom) {
				if (!is_int($id)) {
					return;
				}
				$ids .= $champ . '_id = "' . $id . '" OR ';
			}
			$ids = preg_replace('`OR $`', '', $ids);
			if (!$ids) {
				return;
			}

			// On récupère les informations de chaque objet.
			$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . $table . ' WHERE ' . $ids;
			$objets = $this->mysql->select($mysql_requete);
			if (!is_array($objets)) {
				return;
			}

			for ($i = 0; $i < count($objets); $i++) {
				$infos = array();
				$infos[$champ . '_id'] = $objets[$i][$champ . '_id'];
				$infos[$champ . '_nom'] = $objets[$i][$champ . '_nom'];
				$infos[$champ . '_visible'] = $objets[$i][$champ . '_visible'];
				$infos[$champ . '_chemin'] = $objets[$i][$champ . '_chemin'];
				$infos[$champ . '_commentaires'] = $objets[$i][$champ . '_commentaires'];
				$infos[$champ . '_votes'] = $objets[$i][$champ . '_votes'];
				$infos[$champ . '_hits'] = $objets[$i][$champ . '_hits'];
				$infos[$champ . '_poids'] = $objets[$i][$champ . '_poids'];
				if ($champ == 'categorie') {
					$infos['categorie_images'] = $objets[$i]['categorie_images'];
					$infos['categorie_images_inactive'] = $objets[$i]['categorie_images_inactive'];
					$infos['categorie_commentaires_inactive'] = $objets[$i]['categorie_commentaires_inactive'];
					$infos['categorie_votes_inactive'] = $objets[$i]['categorie_votes_inactive'];
					$infos['categorie_hits_inactive'] = $objets[$i]['categorie_hits_inactive'];
					$infos['categorie_poids_inactive'] = $objets[$i]['categorie_poids_inactive'];
				}
				switch ($_POST['action']) {
					case 'desactiver' :
						$_REQUEST['desactive'] = $objets[$i][$champ . '_id'];
						$this->galerie_action_desactive($infos);
						break;
					case 'activer' :
						$_REQUEST['active'] = $objets[$i][$champ . '_id'];
						$this->galerie_action_active($infos);
						break;
					case 'supprimer' :
						$_REQUEST['supprime'] = $objets[$i][$champ . '_id'];
						$deletes = $this->galerie_action_delete($infos);
						break;
				}
			}

			if (isset($deletes)) {
				if (isset($deletes[0])) {
					$objet_type = (current($_POST['objet_type']) == 'image') ? 'images' : 'catégories';
					$this->template['infos']['info']['delete'] = 'Certaines ' . $objet_type . 'n\'ont pas pu être supprimées.<br />Vous devrez les supprimer manuellement par FTP.';
				} else {
					$objet_type = (current($_POST['objet_type']) == 'image') ? 'Toutes les images sélectionnées ont été supprimées.' : 'Tous les albums ou catégories sélectionnés ont été supprimés.';
					$this->template['infos']['action']['delete'] = $objet_type;
				}
			}

			unset($_REQUEST['desactive']);
			unset($_REQUEST['active']);
			unset($_REQUEST['supprime']);
		}

		// Traitement par lot : opérations de la liste déroulante : déplacer.
		if (isset($_POST['gal_deplacer_imgs']) && isset($_POST['vers']) && preg_match('`^\d{1,9}$`', $_POST['vers']) 
		 && isset($_POST['objet_id']) && is_array($_POST['objet_id'])) {

			// On récupère l'identifiant de chaque objet.
			$ids = '';
			foreach ($_POST['objet_id'] as $id => $nom) {
				if (!is_int($id)) {
					return;
				}
				$ids .= 'image_id = "' . $id . '" OR ';
			}
			$ids = preg_replace('`OR $`', '', $ids);
			if (!$ids) {
				return;
			}

			// On récupère les informations de chaque objet.
			$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'images WHERE ' . $ids;
			$images = $this->mysql->select($mysql_requete);
			if (!is_array($images)) {
				return;
			}

			// Récupération des informations de la catégorie de destination.
			$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'categories 
				 WHERE categorie_id = "' . $_POST['vers'] . '"';
			$destination = $this->mysql->select($mysql_requete, 11);

			// On s'assure que la catégorie cible existe.
			if (is_array($destination)) {
				$_REQUEST['f_type'] = 'img';
				for ($i = 0; $i < count($images); $i++) {

					// On vérifie si un objet du même nom ou du même nom de fichier existe déjà dans la catégorie cible.
					$path = $destination['categorie_chemin'] . basename($images[$i]['image_chemin']);
					$test1 = 'SELECT image_id FROM ' . MYSQL_PREF . 'images 
						WHERE image_chemin = "' . $path . '"';
					$test2 = 'SELECT image_id FROM ' . MYSQL_PREF . 'images 
						WHERE image_chemin REGEXP "^' . dirname($path) . '/[^/]+' . '$" AND image_nom = "' . outils::protege_mysql($images[$i]['image_nom'], $this->mysql->lien) . '"';
					if ($this->mysql->select($test2, 5) != 'vide') {
						$this->template['infos']['attention']['deja_' . $images[$i]['image_id']] = '[image ' . $images[$i]['image_id']  . '] : Le répertoire cible contient déjà une image de même nom.';
						continue;
					}
					if ($this->mysql->select($test1, 5) != 'vide') {
						$this->template['infos']['attention']['deja_' . $images[$i]['image_id']] = '[image ' . $images[$i]['image_id']  . '] : Le répertoire cible contient déjà une image de même nom de fichier.';
						continue;
					}

					$img['image_visible'] = $images[$i]['image_visible'];
					$img['image_commentaires'] = $images[$i]['image_commentaires'];
					$img['image_votes'] = $images[$i]['image_votes'];
					$img['image_hits'] = $images[$i]['image_hits'];
					$img['image_poids'] = $images[$i]['image_poids'];
					$img['image_visible'] = $images[$i]['image_visible'];

					// On UPDATE les informations de la catégorie de l'objet source,
					// ainsi que de ses catégories parentes.
					$this->update_deplace(dirname($images[$i]['image_chemin']) . '/', '-', 'image', $img, '');

					// On déplace l'image sur le disque et on UPDATE le chemin de l'image.
					$images_deplace_ok = $this->deplace($images[$i]['image_chemin'], $destination['categorie_chemin'], $destination['categorie_pass']);

					// On UPDATE les informations de la catégorie cible,
					// ainsi que ses catégories parentes.
					$this->update_deplace($destination['categorie_chemin'], '+', 'image', $img);

				}

				// On attribue à la date de dernier ajout de la catégorie source et de la catégorie cible
				// la date d'ajout de l'image la plus récente qu'elles contiennent.
				$this->date_img_recente($destination['categorie_chemin']);
				$this->date_img_recente(dirname($images[0]['image_chemin']) . '/');

				// On vérifie le représentant de la catégories source et de la catégorie cible,
				// ainsi que leurs catégories parentes.
				$this->verif_representant($destination['categorie_chemin']);
				$this->verif_representant(dirname($images[0]['image_chemin']) . '/');

				// Si toutes les images ont bien été déplacées...
				if (isset($images_deplace_ok)) {
					if ($images_deplace_ok) {

						// Message de confirmation.
						if ($destination['categorie_nom']) {
							$dest_type = ($destination['categorie_derniere_modif']) ? 'l\'album' : 'la catégorie';
							$dest = 'vers ' . $dest_type . ' « ' . strip_tags($destination['categorie_nom']) . ' ».';
						} else {
							$dest = 'à la racine de la galerie.';
						}
						$img_deplace_msg = (count($images) == 1) ? 'L\'image sélectionnée a été déplacée ' : 'Les images sélectionnées ont été déplacées ';
						$this->template['infos']['action']['deplace']  = $img_deplace_msg . $dest;

					} else {
						$this->template['infos']['erreur']['deplace']  = 'Certaines images n\'ont pas pu être déplacées.';
					}
				}
				unset($_REQUEST['f_type']);

				// On renvoie vers la première page.
				$_REQUEST['startnum'] = 0;
			}
			
		}

		// Traitement individuel : déplacement d'une catégorie.
		if (isset($_POST['deplacer_cat']) && is_array($_POST['deplacer_cat']) && preg_match('`^\d{1,9}$`', key($_POST['deplacer_cat'])) 
		 && isset($_POST['vers'][key($_POST['deplacer_cat'])]) && preg_match('`^\d{1,9}$`', $_POST['vers'][key($_POST['deplacer_cat'])])) {

			// On récupère les informations de la catégorie.
			$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'categories 
				 WHERE categorie_id = ' . key($_POST['deplacer_cat']);
			$objet = $this->mysql->select($mysql_requete, 11);

			// On s'assure que la catégorie existe.
			if (is_array($objet)) {

				$type = ($objet['categorie_derniere_modif']) ? 'L\'album' : 'La catégorie';
				$type2 = ($objet['categorie_derniere_modif']) ? 'un album' : 'une catégorie';
				$fm = ($objet['categorie_derniere_modif']) ? '' : 'e';

				$_REQUEST['f_type'] = 'cat';

				// Mot de passe.
				$objet_pass = $objet['categorie_pass'];
				$objet['categorie_pass'] = preg_replace('`^\d+:(.+)$`', '$1', $objet['categorie_pass']);

				// Les catégories à déplacer ne doivent pas faire plus de 60 Mo et contenir plus de 200 images.
				if ($objet['categorie_poids'] > 60000 || $objet['categorie_images'] > 200) {
					$this->template['infos']['attention']['mc_supp'] = 'Vous ne pouvez pas déplacer ' . $type2 . ' de plus de 60 Mo et contenant plus de 200 images.';
					return;
				}

				// Récupération des informations de la catégorie de destination.
				$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'categories 
					 WHERE categorie_id = "' . $_POST['vers'][key($_POST['deplacer_cat'])] . '"';
				$destination = $this->mysql->select($mysql_requete, 11);

				// On s'assure que la catégorie cible existe.
				if (is_array($destination)) {

					// On vérifie si un objet du même nom ou du même nom de fichier existe déjà dans la catégorie cible.
					$path = $destination['categorie_chemin'] . basename($objet['categorie_chemin']) . '/';
					$test1 = 'SELECT categorie_id FROM ' . MYSQL_PREF . 'categories 
						WHERE categorie_chemin = "' . $path . '"';
					$test2 = 'SELECT categorie_id FROM ' . MYSQL_PREF . 'categories 
						WHERE categorie_chemin REGEXP "^' . dirname($path) . '/[^/]+/$" AND categorie_nom = "' . outils::protege_mysql($objet['categorie_nom'], $this->mysql->lien) . '"';
					if ($this->mysql->select($test2, 5) != 'vide') {
						$this->template['infos']['attention']['deja'] = 'Le répertoire cible contient déjà ' . $type2 . ' de même nom.';
						return;
					}
					if ($this->mysql->select($test1, 5) != 'vide') {
						$this->template['infos']['attention']['deja'] = 'Le répertoire cible contient déjà ' . $type2 . ' de même nom de fichier.';
						return;
					}

					// On UPDATE les informations de la catégorie de l'objet source,
					// ainsi que de ses catégories parentes.
					$this->update_deplace(dirname($objet['categorie_chemin']) . '/', '-', 'categorie', $objet, '');

					// On détermine le mot de passe qu'aura la catégorie déplacée.
					$pass = ($objet_pass) ? $objet_pass : $destination['categorie_pass'];

					// On déplace la catégorie sur le disque et
					// on UPDATE le chemin de la catégorie et de tous ses sous-objets.
					$this->deplace($objet['categorie_chemin'], $destination['categorie_chemin'], $pass);

					// On UPDATE les informations de la catégorie cible,
					// ainsi que ses catégories parentes.
					$this->update_deplace($destination['categorie_chemin'], '+', 'categorie', $objet);

					// On vérifie le représentant de la catégories source et de la catégorie cible,
					// ainsi que leurs catégories parentes.
					$this->verif_representant($destination['categorie_chemin']);
					$this->verif_representant(dirname($objet['categorie_chemin']) . '/');

					// On attribue à la date de dernier ajout de la catégorie source et de la catégorie cible
					// la date d'ajout de l'image la plus récente qu'elles contiennent.
					$this->date_img_recente($destination['categorie_chemin']);
					$this->date_img_recente(dirname($objet['categorie_chemin']) . '/');

					// Message de confirmation.
					if ($destination['categorie_nom']) {
						$dest_type = ($destination['categorie_derniere_modif']) ? 'l\'album' : 'la catégorie';
						$dest = 'vers ' . $dest_type . ' « ' . strip_tags($destination['categorie_nom']) . ' ».';
					} else {
						$dest = 'à la racine de la galerie.';
					}
					$this->template['infos']['action']['deplace'] = $type . ' « ' . strip_tags($objet['categorie_nom']) . ' » a été déplacé' . $fm . ' ' . $dest;

					// On renvoie vers la première page.
					$_REQUEST['startnum'] = 0;
				}
			}
		}

		// Traitement individuel : activation / désactivation / suppression d'un objet.
		if (isset($_REQUEST['type']) && preg_match('`^(album|categorie|image)$`', $_REQUEST['type'])) {

			switch ($_REQUEST['type']) {
				case 'image' : $objet = 'L\'image'; $fm  = 'e'; break;
				case 'album' : $objet = 'L\'album'; $_REQUEST['type'] = 'categorie'; $fm  = ''; break;
				case 'categorie' : $objet = 'La catégorie'; $fm  = 'e'; break;
			}

			// Désactivation d'un objet.
			if (isset($_REQUEST['desactive']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['desactive'])) {

				// On récupère des informations de l'objet.
				$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . $_REQUEST['type'] . 's 
					WHERE ' . $_REQUEST['type'] . '_id = "' . $_REQUEST['desactive'] . '"';
				$infos = $this->mysql->select($mysql_requete, 11);
				
				$this->galerie_action_desactive($infos);
			}

			// Activation d'un objet.
			if (isset($_REQUEST['active']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['active'])) {

				// On récupère les informations de l'objet.
				$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . $_REQUEST['type'] . 's 
					WHERE ' . $_REQUEST['type'] . '_id = "' . $_REQUEST['active'] . '"';
				$infos = $this->mysql->select($mysql_requete, 11);

				$this->galerie_action_active($infos);
			}

			// Suppression d'un objet.
			if (isset($_REQUEST['supprime']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['supprime'])) {

				// Récupération des informations sur l'objet.
				$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . $_REQUEST['type'] . 's 
					WHERE ' . $_REQUEST['type'] . '_id = "' . $_REQUEST['supprime'] . '"';
				$infos = $this->mysql->select($mysql_requete, 11);

				// S'il existe une référence de l'objet dans la base de données...
				if ($infos != 'vide') {
					$this->galerie_action_delete($infos, $objet, $fm);
				}

			}
		}

		// Création d'un album ou d'une catégorie.
		if (isset($_REQUEST['gal_new_obj']) 
		 && preg_match('`^alb|cat$`', $_REQUEST['gal_new_obj']) 
		 && preg_match('`\w`', $_REQUEST['gal_new_name'])) {

			$_REQUEST['gal_new_name'] = trim($_REQUEST['gal_new_name']);

			if (isset($_REQUEST['cat']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['cat'])) {
				$cat = $_REQUEST['cat'];
			} else {
				$cat = 1;
			}

			$type = ($_REQUEST['gal_new_obj'] == 'alb') ? 'cet album' : 'cette catégorie';
			$type2 = ($_REQUEST['gal_new_obj'] == 'alb') ? 'L\'album' : 'La catégorie';
			$fm = ($_REQUEST['gal_new_obj'] == 'alb') ? '' : 'e';

			// On vérifie si on peut créer l'objet à cet endroit.
			$mysql_requete = 'SELECT categorie_derniere_modif,categorie_chemin FROM ' . MYSQL_PREF . 'categories 
				WHERE categorie_id = "' .$cat  . '"';
			$infos_cat = $this->mysql->select($mysql_requete, 11);
			if (!$infos_cat['categorie_derniere_modif']) {

				$new_name = outils::protege_mysql($_REQUEST['gal_new_name'], $this->mysql->lien);

				// On vérifie s'il n'y a pas déjà un objet du même nom.
				$path = $infos_cat['categorie_chemin'];
				$path = ($path == '.') ? '': $path;
				$test1 = 'SELECT categorie_nom FROM ' . MYSQL_PREF . 'categories WHERE 
						categorie_id NOT IN ("' . $cat . '") AND 
						categorie_nom = "' . $new_name . '" AND 
						categorie_chemin REGEXP "^' . $path . '[^/]+/$"';
				$test = $this->mysql->select($test1);
				if (is_array($test)) {
					$this->template['infos']['attention']['deja'] = 'Cette catégorie contient déjà une catégorie ou un album de même nom.';
					return;
				}

				// On remplace les caractères spéciaux pour le nom du répertoire.
				$dir_cat = ($infos_cat['categorie_chemin'] == '.') ? '': $infos_cat['categorie_chemin'];
				$post_name = strtr($_REQUEST['gal_new_name'],
							'éèëêàäâáåãïîìíöôòóõùûüúÿýçñ',
							'eeeeaaaaaaiiiiooooouuuuyycn');
				$post_name = preg_replace('`[^-a-z0-9]`i', '_', $post_name);
				$dir = $this->galerie_dir . $dir_cat . $post_name;

				// Si un répertoire au même nom existe, on modifie le nom du répertoire.
				$n = 1;
				$testdir = $dir;
				while (is_dir($testdir)) {
					$testdir = $dir . $n;
					$n++;
					if ($n > 999) {
						$this->template['infos']['erreur']['impossible'] = '[' . __LINE__ . '] Impossible de créer ' . $type . '.';
						return;
					}
				}
				$dir = $testdir . '/';

				// On crée le répertoire sur le disque.
				if (!files::createDir($dir)) {
					$this->template['infos']['erreur']['impossible'] = '[' . __LINE__ . '] Impossible de créer ' . $type . '.';
					return;
				}

				// Si c'est un album à créer, on crée également le répertoire de vignettes.
				if ($_REQUEST['gal_new_obj'] == 'alb') {
					files::createDir($dir . THUMB_TDIR);
				}

				$dir = preg_replace('`^.+/' . GALERIE_ALBUMS . '/`', '', $dir);

				// On récupère le mot de passe de la catégorie parente.
				$mysql_requete = 'SELECT categorie_pass FROM ' . MYSQL_PREF . 'categories 
					WHERE categorie_chemin = "' . dirname($dir) . '/"';
				$pass = $this->mysql->select($mysql_requete);
				$pass_champ = (!is_array($pass) || empty($pass[0]['categorie_pass'])) ? '' : ', categorie_pass';
				$pass_valeur = (!is_array($pass) || empty($pass[0]['categorie_pass'])) ? '' : ', "' . $pass[0]['categorie_pass'] . '"';

				// On insére la nouvelle catégorie dans la base de données.
				$derniere_modif = ($_REQUEST['gal_new_obj'] == 'cat') ? '0' : time();
				$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'categories (
						categorie_chemin,
						categorie_nom,
						categorie_visible,
						image_representant_id,
						categorie_derniere_modif,
						categorie_date'
						. $pass_champ . ') VALUES ("'
						. $dir . '","'
						. $new_name . '","'
						. '0' . '","'
						. '0' . '","'
						. $derniere_modif . '","'
						. time() . '"' 
						. $pass_valeur . ')';
				if ($this->mysql->requete($mysql_requete)) {
					$this->template['infos']['action']['new'] = $type2 . ' « ' . $_REQUEST['gal_new_name'] . ' » a été créé' . $fm . '.';
				}
			}
		}

		// Changement du représentant d'une catégorie.
		if (isset($_REQUEST['obj']) && preg_match('`^\d{1,12}$`', $_REQUEST['obj']) 
		 && isset($_REQUEST['new_thumb']) && preg_match('`^\d{1,12}$`', $_REQUEST['new_thumb'])) {

			$this->verifVID();

			$mysql_requete = 'SELECT image_id,image_visible FROM ' . MYSQL_PREF . 'images 
				WHERE image_id = "' . $_REQUEST['new_thumb'] . '"';
			$infos_tb = $this->mysql->select($mysql_requete, 11);

			if (is_array($infos_tb)) {

				$mysql_requete = 'SELECT categorie_visible FROM ' . MYSQL_PREF . 'categories 
					WHERE categorie_id = "' . $_REQUEST['obj'] . '"';
				$infos_cat = $this->mysql->select($mysql_requete, 11);

				// On vérifie si la visibilité de l'image est en adéquation avec celle de la catégorie.
				if ($infos_cat['categorie_visible'] && !$infos_tb['image_visible']) {
					return;
				}

				// On UPDATE la catégorie.
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET 
						image_representant_id = "' . $infos_tb['image_id'] . '"
					WHERE categorie_id = "' . $_REQUEST['obj'] . '"';
				if ($this->mysql->requete($mysql_requete)) {
					$this->template['infos']['action']['representant'] = 'Le représentant a été changé.';
				} else {
					$this->template['infos']['erreur']['representant'] = 'Impossible de changer le représentant.';
				}
			}
		}

		// Upload HTTP d'images.
		if (!empty($_FILES) && isset($_REQUEST['cat']) && preg_match('`^\d{1,9}$`', $_REQUEST['cat'])) {

			$n = 0;

			// Si GD n'est pas activé, on arrête tout.
			if (!function_exists('imagetypes')) {
				$this->template['infos']['erreur']['upload_' . $n] = 'GD n\'est pas activé.';
				return;
			}

			$mysql_requete = 'SELECT categorie_chemin,categorie_derniere_modif FROM ' . MYSQL_PREF . 'categories 
				 WHERE categorie_id = ' . $_REQUEST['cat'];
			$cat_infos = $this->mysql->select($mysql_requete, 11);

			// Si l'objet n'est pas un album, on arrête tout.
			if (empty($cat_infos['categorie_derniere_modif'])) {
				return;
			}

			$dir = $this->galerie_dir . $cat_infos['categorie_chemin'];
			$images_http = array();
			foreach ($_FILES as $f_file => $infos) {
				$n++;

				// Y a-t-il une erreur ?
				if ($infos['error']) {
					switch ($infos['error']) {
						case 4 :
							break;
						case 2 :
						case 1 :
							$this->template['infos']['attention']['upload_' . $n] = 'Le fichier « <em>' . $infos['name'] . '</em> » est trop lourd.';
							break;
						default :
							$this->template['infos']['erreur']['upload_' . $n] = 'Impossible de récupérer le fichier « <em>' . $infos['name'] . ' </em> ».';
					}
					continue;
				}

				// Est-ce une image ?
				if (preg_match('`\.(jpe?g|gif|png)$`i', $infos['name'])
				 && preg_match('`^image/(gif|p?jpeg|(x-)?png)$`i', trim($infos['type']))) {

					// On vérifie si un fichier du même nom existe déjà.
					if (file_exists($dir . $infos['name'])) {
						$this->template['infos']['attention']['upload_' . $n] = 'L\'image « <em>' . $infos['name'] . '</em> » ne peut être ajoutée à l\'album car un fichier du même nom existe déjà dans cet album.';
						continue;
					}

					// On déplace le fichier envoyé vers le répertoire de l'album.
					files::chmodDir($dir);
					if (!move_uploaded_file($infos['tmp_name'], $dir . $infos['name'])) {
						$this->template['infos']['erreur']['upload_' . $n] = 'Impossible de récupérer le fichier « <em>' . $infos['name'] . ' </em> ».';
						continue;
					}
					files::chmodFile($dir . $infos['name']);

					$images_http[] = $infos['name'];

				// Est-ce une archive Zip ?
				} elseif (preg_match('`\.zip$`i', $infos['name']) && preg_match('`^application/(zip|x-zip-compressed)$`i', trim($infos['type']))) {

					if (function_exists('zip_open')) {
						if ($zip = zip_open($infos['tmp_name'])) {
							while ($zip_entry = zip_read($zip)) {
								if (zip_entry_open($zip, $zip_entry, 'r')) {
									$zip_file = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
									$image_name = zip_entry_name($zip_entry);
									if (!preg_match('`\.(jpe?g|gif|png)$`i', $image_name)) {
										continue;
									}
									$image_path = $dir . $image_name;
									if (file_exists($image_path)) {
										continue;
									}
									$image_file = fopen($image_path, 'w');
									fwrite($image_file, $zip_file);
									fclose($image_file);
									zip_entry_close($zip_entry);
									list($width, $height, $type) = getimagesize($image_path);
									if ($type != 1 && $type != 2 && $type != 3) {
										files::suppFile($image_path);
										continue;
									}
									$images_http[] = $image_name;
								}
							}
						}					
					}

				// Fichier non valide.
				} else {
					$this->template['infos']['attention']['upload_' . $n] = 'Le fichier « <em>' . $infos['name'] . '</em> » n\'est pas une image ou une archive Zip valide (type : ' . $infos['type'] . ').';
				}
				
				// On limite le nombre d'images envoyées en même temps.
				if ($n > 11) {
					break;
				}
			}

			// Enregistrement des images dans la base de données.
			$upload = new upload($this->mysql, $this->config);
			$upload->http['album'] = $cat_infos['categorie_chemin'];
			$upload->http['images'] = $images_http;
			$upload->recup_albums();

			// Rapport.
			if ($upload->rapport['erreurs']) {
				foreach ($upload->rapport['erreurs'] as $v) {
					$this->template['infos']['erreur']['upload_' . $v[0]] = 'Une erreur s\'est produite avec l\'objet « <em>' . $v[0] . '</em> » : ' . $v[1];
				}
			}
			for ($i = 0; $i < count($images_http); $i++) {
				$ok = 1;
				for ($n = 0; $n < count($upload->rapport['img_rejets']); $n++) {
					if ($upload->rapport['img_rejets'][$n][0] == $images_http[$i]) {
						$this->template['infos']['attention']['upload_' . $i] = 'L\'image « <em>' . $images_http[$i] . '</em> » a été rejetée pour la raison suivante : ' . $upload->rapport['img_rejets'][$n][2];
						files::suppFile($dir . $images_http[$i]);
						files::suppFile($dir . '~#~');
						$ok = 0;
						break;
					}
				}
				if ($ok && empty($this->template['infos']['erreur'])) {
					$this->template['infos']['action']['upload_' . $i] = 'L\'image « <em>' . $images_http[$i] . ' </em> » a été ajoutée à l\'album.';
				}
			}
		}
	}



	/*
	 *	Déplace une image ou une catégorie vers une autre catégorie.
	*/
	function deplace($source, $cible, $pass) {
		static $ok = TRUE;
		$source = preg_replace('`/$`', '', $source);
		$cible = preg_replace('`/$`', '', $cible);
		$gal_dir = $this->galerie_dir;
		if (is_dir($gal_dir . $source)) {
			if ($dir = @opendir($gal_dir . $source)) {
				$cible_dir = ($cible == '.') ? basename($source) : $cible . '/' . basename($source);
				if (!is_dir($gal_dir . $cible_dir)) {
					if (!files::createDir($gal_dir . $cible_dir)) {
						$ok = FALSE;
					}
				}
				if (basename($source) != THUMB_TDIR) {
					$categorie_pass = (!empty($pass)) ? ', categorie_pass = "' . $pass . '"' : '';
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
						  SET categorie_chemin = "' . $cible_dir . '/"'
						    . $categorie_pass . '
						WHERE categorie_chemin = "' . $source . '/"';
					$this->mysql->requete($mysql_requete);
				}
				while ($ent = readdir($dir)) {
					$source_obj = $source . '/' . $ent;
					if (is_dir($gal_dir . $source_obj) && $ent != '.' && $ent != '..') {
						if ($ent != THUMB_TDIR) {
							$this->deplace($source_obj, $cible_dir, $pass);
						}
					} elseif (is_file($gal_dir . $source_obj)) {
						$thumb_source = $gal_dir . dirname($source_obj) . '/' . 
							THUMB_TDIR . '/' . 
							THUMB_PREF . basename($source_obj);
						$thumbnails_dir = $gal_dir . $cible_dir . '/' . THUMB_TDIR;
						if (!is_dir($thumbnails_dir)) {
							if (!files::createDir($thumbnails_dir)) {
								$ok = FALSE;
							}
						}
						$thumb_cible = $thumbnails_dir . '/' . THUMB_PREF . basename($source_obj);
						if (!files::deplace($gal_dir . $source_obj, $gal_dir . $cible_dir . '/' . $ent)) {
							$ok = FALSE;
						}
						if (file_exists($thumb_source)) {
							if (!files::deplace($thumb_source, $thumb_cible)) {
								$ok = FALSE;
							}
						}
						if (basename(dirname($source_obj)) != THUMB_TDIR) {
							$image_pass = (!empty($pass)) ? ', ' . MYSQL_PREF . 'images.image_pass = "' . $pass . '"' : '';
							$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images,' . MYSQL_PREF . 'categories
								  SET ' . MYSQL_PREF . 'images.categorie_parent_id = ' . MYSQL_PREF . 'categories.categorie_id,
									  ' . MYSQL_PREF . 'images.image_chemin = "' . $cible_dir . '/' . $ent . '"
									  ' . $image_pass . '
								WHERE ' . MYSQL_PREF . 'images.image_chemin = "' . $source_obj . '"
								  AND ' . MYSQL_PREF . 'categories.categorie_chemin = "' . $cible_dir . '/"';
							if (!$this->mysql->requete($mysql_requete)) {
								$ok = FALSE;
							}
						}
					}
				}
				closedir($dir);
				if (!$this->delete_dir($gal_dir . $source)) {
					$ok = FALSE;
				}
			} else {
				$ok = FALSE;
			}
		} elseif (is_file($gal_dir . $source)) {
			$thumb_source = $gal_dir . dirname($source) . '/' . 
				THUMB_TDIR . '/' . 
				THUMB_PREF . basename($source);
			$thumbnails_dir = $gal_dir . $cible . '/' . THUMB_TDIR;
			if (!is_dir($thumbnails_dir)) {
				if (!files::createDir($thumbnails_dir)) {
					$ok = FALSE;
				}
			}
			$thumb_cible = $thumbnails_dir . '/' . THUMB_PREF . basename($source);
			if (!files::deplace($gal_dir . $source, $gal_dir . $cible . '/' . basename($source))) {
				$ok = FALSE;
			}
			if (file_exists($thumb_source)) {
				if (!files::deplace($thumb_source, $thumb_cible)) {
					$ok = FALSE;
				}
			}
			$image_pass = (!empty($pass)) ? ', ' . MYSQL_PREF . 'images.image_pass = "' . $pass . '"' : '';
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images,' . MYSQL_PREF . 'categories 
				  SET ' . MYSQL_PREF . 'images.categorie_parent_id = ' . MYSQL_PREF . 'categories.categorie_id,
					  ' . MYSQL_PREF . 'images.image_chemin = "' . $cible . '/' . basename($source) . '"
					  ' . $image_pass . '
				WHERE ' . MYSQL_PREF . 'images.image_chemin = "' . $source . '"
				  AND ' . MYSQL_PREF . 'categories.categorie_chemin = "' . $cible . '/"';
			if (!$this->mysql->requete($mysql_requete)) {
				$ok = FALSE;
			}
		} else {
			$ok = FALSE;
		}
		return $ok;
	}



	/*
	 *	UPDATE les informations des catégories parentes lors
	 *	du déplacement d'objets.
	*/
	function update_deplace($path, $pm, $champ, $objet, $active = 'categorie_visible = "1",') {

		$images = ($_REQUEST['f_type'] == 'img') ? 1 : $objet['categorie_images'];
		$updates = '';
		if (($_REQUEST['f_type'] == 'img' && $objet['image_visible']) || $_REQUEST['f_type'] == 'cat') {
			$updates .= 'categorie_commentaires = categorie_commentaires ' . $pm . '"' . $objet[$champ . '_commentaires'] . '",';
			$updates .= 'categorie_votes = categorie_votes ' . $pm . '"' . $objet[$champ . '_votes'] . '",';
			$updates .= 'categorie_hits = categorie_hits ' . $pm . '"' . $objet[$champ . '_hits'] . '",';
			$updates .= 'categorie_poids = categorie_poids ' . $pm . '"' . $objet[$champ . '_poids'] . '",';
			$updates .= 'categorie_images = categorie_images ' . $pm . '"' . $images . '",';
		}
		if (($_REQUEST['f_type'] == 'img' && !$objet['image_visible']) || $_REQUEST['f_type'] == 'cat') {
			$inactive = '';
			if ($_REQUEST['f_type'] == 'cat') {
				$inactive = '_inactive';
				$images = $objet['categorie_images_inactive'];
			}
			$updates .= 'categorie_commentaires_inactive = categorie_commentaires_inactive ' . $pm . '"' . $objet[$champ . '_commentaires' . $inactive] . '",';
			$updates .= 'categorie_votes_inactive = categorie_votes_inactive ' . $pm . '"' . $objet[$champ . '_votes' . $inactive] . '",';
			$updates .= 'categorie_hits_inactive = categorie_hits_inactive ' . $pm . '"' . $objet[$champ . '_hits' . $inactive] . '",';
			$updates .= 'categorie_poids_inactive = categorie_poids_inactive ' . $pm . '"' . $objet[$champ . '_poids' . $inactive] . '",';
			$updates .= 'categorie_images_inactive = categorie_images_inactive ' . $pm . '"' . $images . '",';
		}

		if ($path == '.') {
			$path = './';
		}
		while ($path != './') {
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET ' . 
					$updates . $active . ' categorie_id = categorie_id 
				 WHERE categorie_chemin = "' . $path . '"';
			$this->mysql->requete($mysql_requete);
			$path = dirname($path) . '/';
		}
	}



	/*
	 *	Vérifie si un objet désactivé ou supprimé supprimera ou
	 *	désactivera le représentant de ses catégories parentes,
	 *	et choisi un nouveau représentant selon les cas.
	*/
	function verif_representant($dir) {

		while ($dir != './') {

			// Si le chemin est une image, on repassera.
			if (preg_match('`/$`', $dir)) {

				// On récupère les informations utiles de la catégorie.
				$mysql_requete = 'SELECT categorie_id,
										 image_representant_id,
										 categorie_images,
										 categorie_images_inactive
					FROM ' . MYSQL_PREF . 'categories 
					WHERE categorie_chemin = "' . $dir . '"';
				$infos = $this->mysql->select($mysql_requete, 11);

				// Si la catégorie ne contient aucune image, on remplace le
				// représentant par une image indiquant que la catégorie est vide,
				// et on désactive la catégorie.
				if (($infos['categorie_images'] + $infos['categorie_images_inactive']) == 0) {
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET 
							categorie_visible = "0",
							image_representant_id = "0" 
						WHERE categorie_id = "' . $infos['categorie_id'] . '"';
					$this->mysql->requete($mysql_requete);

					$dir = dirname($dir) . '/';
					continue;
				}

				// Si la catégorie ne contient aucune image visible,
				// on la désactive.
				if ($infos['categorie_images'] == 0) {
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories 
						SET categorie_visible = "0"
						WHERE categorie_id = "' . $infos['categorie_id'] . '"';
					$this->mysql->requete($mysql_requete);
				}

				// Si le représentant est désactivé ou inexistant, on en choisi un nouveau.
				$mysql_requete = 'SELECT image_visible FROM ' . MYSQL_PREF . 'images 
					WHERE image_id = "' . $infos['image_representant_id'] . '"
					  AND image_chemin LIKE "' . $dir . '%"';
				$is_active = $this->mysql->select($mysql_requete, 5);
				$visible = 'AND image_visible = "1" ';
				if ($is_active == 'vide') {
					$is_active = 0;
					if ($infos['categorie_images'] == 0) {
						$visible = '';
					}
				}
				if (!$is_active) {
					$mysql_requete = 'SELECT image_id FROM ' . MYSQL_PREF . 'images
						WHERE image_chemin LIKE "' . $dir . '%" ' . $visible . '
						ORDER BY RAND()
						LIMIT 1';
					$nouveau_representant = $this->mysql->select($mysql_requete, 5);
					if ($nouveau_representant != 'vide') {
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
							SET image_representant_id = "' . $nouveau_representant . '"
							WHERE categorie_id = "' . $infos['categorie_id'] . '"';
						$this->mysql->requete($mysql_requete);
					}
				}
			}

			$dir = dirname($dir) . '/';
		}

	}



	/*
	  *	On attribue à la date de dernier ajout de la catégorie cible
	  *	la date d'ajout de l'image la plus récente qu'elle contient,
	  *	et de même pour les catégorie parentes.
	*/
	function date_img_recente($path) {

		while ($path != './' && $path != '.') {
			$mysql_requete = 'SELECT image_date
								FROM ' . MYSQL_PREF . 'images
							   WHERE image_chemin LIKE "' . $path . '%"
							ORDER BY image_date DESC
							   LIMIT 1';
			$date = $this->mysql->select($mysql_requete);

			if (is_array($date)) {
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
					SET categorie_dernier_ajout = "' . $date[0]['image_date'] . '"
					WHERE categorie_chemin = "' . $path . '"';
				$this->mysql->requete($mysql_requete);
			}

			$path = dirname($path) . '/';
		}

	}



	/*
	 *	Supprime le contenu d'un répertoire non-vide et éventuellement le répertoire lui-même.
	*/
	function delete_dir($dir, $supp_dir = 1) {
		if ($dir_link = @opendir($dir)) {
			while ($ent = readdir($dir_link)) {
				$sub_obj = $dir . '/' . $ent;
				if (is_dir($sub_obj) && $ent != '.' && $ent != '..') {
					$this->delete_dir($sub_obj, 1);
				} elseif (is_file($sub_obj)) {
					files::suppFile($sub_obj);
				}
			}
			closedir($dir_link);
		}
		if ($supp_dir) {
			if (files::suppFile($dir)) {
				return TRUE;
			}
		} else {
			return TRUE;
		}
	}



	/*
	 *	UPDATE des informations lors d'une
	 *	activation/désativation/suppression sur un objet.
	*/
	function objet_update_nb($e, $infos, $objet = '') {
	
		$ok = TRUE;

		$etat = ($e == 'active') ? 1 : 0;

		// On UPDATE l'état 'visible' de l'objet
		$mysql_requete = 'UPDATE ' . MYSQL_PREF . $_REQUEST['type'] . 's SET 
			' . $_REQUEST['type'] . '_visible = "' . $etat . '" 
			WHERE ' . $_REQUEST['type'] . '_id = "' . $_REQUEST[$e] . '"';

		if ($this->mysql->requete($mysql_requete)) {

			// Si l'objet est une catégorie ou un album,
			// on UPDATE l'état 'visible' de tous les objets qu'il contient.
			if ($_REQUEST['type'] == 'categorie') {

				// Images.
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET 
					image_visible = "' . $etat . '" 
					WHERE image_chemin LIKE "' . $infos[$_REQUEST['type'] . '_chemin'] . '%"';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}

				// Catégories.
				if ($e == 'active') {
					$upac = ', categorie_commentaires = categorie_commentaires + categorie_commentaires_inactive,
						categorie_commentaires_inactive = "0",
						categorie_votes = categorie_votes + categorie_votes_inactive,
						categorie_votes_inactive = "0",
						categorie_hits = categorie_hits + categorie_hits_inactive,
						categorie_hits_inactive = "0",
						categorie_poids = categorie_poids + categorie_poids_inactive,
						categorie_poids_inactive = "0",
						categorie_images = categorie_images + categorie_images_inactive,
						categorie_images_inactive = "0"';
				} else {
					$upac = '';
				}
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET 
					categorie_visible = "' . $etat . '"' . $upac . ' 
					WHERE categorie_chemin LIKE "' . $infos[$_REQUEST['type'] . '_chemin'] . '%"';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}
			}

			// Règles d'UPDATE des informations des catégories parentes.
			$images = ($_REQUEST['type'] == 'image') ? 1 : $infos[$_REQUEST['type'] . '_images'];
			$mp_p = ($e == 'active') ? '+' : '-';
			$mp_dc = ($e == 'desactive') ? '+' : '-';
			$P_Ob_P = 'categorie_commentaires = categorie_commentaires ' . $mp_p . ' ' . $infos[$_REQUEST['type'] . '_commentaires'] . ',  
				categorie_votes = categorie_votes ' . $mp_p. ' ' . $infos[$_REQUEST['type'] . '_votes'] . ',  
				categorie_hits = categorie_hits ' . $mp_p . ' ' . $infos[$_REQUEST['type'] . '_hits'] . ',  
				categorie_poids = categorie_poids ' . $mp_p. ' ' . $infos[$_REQUEST['type'] . '_poids'] . ', 
				categorie_images = categorie_images ' . $mp_p . ' ' . $images;
			$DC_Ob_P = 'categorie_commentaires_inactive = categorie_commentaires_inactive ' . $mp_dc . ' ' . $infos[$_REQUEST['type'] . '_commentaires'] . ',  
				categorie_votes_inactive = categorie_votes_inactive ' . $mp_dc. ' ' . $infos[$_REQUEST['type'] . '_votes'] . ',  
				categorie_hits_inactive = categorie_hits_inactive ' . $mp_dc . ' ' . $infos[$_REQUEST['type'] . '_hits'] . ',  
				categorie_poids_inactive = categorie_poids_inactive ' . $mp_dc. ' ' . $infos[$_REQUEST['type'] . '_poids'] . ', 
				categorie_images_inactive = categorie_images_inactive ' . $mp_dc . ' ' . $images;
			if ($_REQUEST['type'] == 'categorie') {
				$P_Ob_DC = 'categorie_commentaires = categorie_commentaires + ' . $infos['categorie_commentaires_inactive'] . ',
					categorie_votes = categorie_votes + ' . $infos['categorie_votes_inactive'] . ',
					categorie_hits = categorie_hits + ' . $infos['categorie_hits_inactive'] . ',
					categorie_poids = categorie_poids + ' . $infos['categorie_poids_inactive'] . ',
					categorie_images = categorie_images + ' . $infos['categorie_images_inactive'];
				$DC_Ob_DC = 'categorie_commentaires_inactive = categorie_commentaires_inactive - ' . $infos['categorie_commentaires_inactive'] . ',
					categorie_votes_inactive = categorie_votes_inactive - ' . $infos['categorie_votes_inactive'] . ',
					categorie_hits_inactive = categorie_hits_inactive - ' . $infos['categorie_hits_inactive'] . ',
					categorie_poids_inactive = categorie_poids_inactive - ' . $infos['categorie_poids_inactive'] . ',
					categorie_images_inactive = categorie_images_inactive - ' . $infos['categorie_images_inactive'];
			}
			if ($e == 'supprime') {
				if ($_REQUEST['type'] == 'image') {
					$updates = ($infos['image_visible']) ? $P_Ob_P : $DC_Ob_P;
				} else {
					$updates = $P_Ob_P . ', ' . $DC_Ob_DC;
				}
			} else {
				if ($e == 'active' && $_REQUEST['type'] == 'categorie') {
					$updates = $P_Ob_DC . ', ' . $DC_Ob_DC;
				} else {
					$updates = $P_Ob_P . ', ' . $DC_Ob_P;
				}
			}

			// On s'assure que toutes les catégories parentes d'un
			// objet à activer soient également activées.
			if ($e == 'active') {
				$updates .= ', categorie_visible = "1"';
			}

			// On UPDATE les informations des catégories parentes.
			$path = $infos[$_REQUEST['type'] . '_chemin'];
			$where = 'categorie_chemin = "."';
			while ($path != '.') {
				$path = dirname($path);
				if ($path != '.') {
					$where .= ' OR categorie_chemin = "' . $path . '/"';
				}

				//On update la note moyenne de la catégorie pour les images activées et désactivée.
				$path_img = ($path == '.') ? '' : $path . '/';
				$path_cat = ($path == '.') ? '.' : $path . '/';
				$mysql_requete = 'SELECT SUM(image_note*image_votes)/SUM(image_votes) FROM ' . MYSQL_PREF . 'images
					WHERE image_chemin LIKE "' . $path_img . '%" 
					  AND image_note > 0
					  AND image_visible = "1"';
				$note_active = $this->mysql->select($mysql_requete, 5);
				$note_active = ($note_active == '') ? 0 : $note_active;
				$mysql_requete = 'SELECT SUM(image_note*image_votes)/SUM(image_votes) FROM ' . MYSQL_PREF . 'images
					WHERE image_chemin LIKE "' . $path_img . '%" 
					  AND image_note > 0
					  AND image_visible = "0"';
				$note_inactive = $this->mysql->select($mysql_requete, 5);
				$note_inactive = ($note_inactive == '') ? 0 : $note_inactive;
				if (($note_active && $note_active > 0) || ($note_inactive && $note_inactive > 0)) {
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
						SET categorie_note = "' . $note_active . '",
						    categorie_note_inactive = "' . $note_inactive . '"
						WHERE categorie_chemin = "' . $path_cat . '"';
					if (!$this->mysql->requete($mysql_requete)) {
						$ok = FALSE;
					}
				}
			}

			// On update les autres informations de toutes les catégories parentes.
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET ' . $updates . ' WHERE ' . $where;
			if (!$this->mysql->requete($mysql_requete)) {
				$ok = FALSE;
			}

			// Si c'est une catégorie à désactiver, on UPDATE les informations
			// de cette catégorie et de ses sous-catégories.
			if ($e == 'desactive' && $_REQUEST['type'] == 'categorie') {
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET 
						categorie_commentaires_inactive = categorie_commentaires_inactive + categorie_commentaires,
						categorie_commentaires = "0",
						categorie_votes_inactive = categorie_votes_inactive + categorie_votes,
						categorie_votes = "0",
						categorie_hits_inactive = categorie_hits_inactive + categorie_hits,
						categorie_hits = "0",
						categorie_poids_inactive = categorie_poids_inactive + categorie_poids,
						categorie_poids = "0",
						categorie_images_inactive = categorie_images_inactive + categorie_images,
						categorie_images = "0" 
					WHERE categorie_chemin LIKE "' . $infos['categorie_chemin'] . '%"';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = FALSE;
				}
			}
		} else {
			$ok = FALSE;
		}

		return $ok;
	}



	/*
	 *	Affichage de la galerie.
	*/
	function galerie() {
	
		$this->template['infos']['title'] = 'gestion des albums';

		// Page à afficher.
		if (isset($_REQUEST['startnum']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['startnum'])) {
			$startnum = $_REQUEST['startnum'];
		} else {
			$startnum = 0;
		}

		// Catégorie actuelle.
		if (isset($_REQUEST['cat']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['cat'])) {
			$cat = $_REQUEST['cat'];
		} else {
			$cat = 1;
		}
		$this->template['infos']['cat'] = $cat;
		$this->template['infos']['section'] = 'section=galerie&page=gestion&cat=' . $cat;

		// Personnalisation: nombre d'objets maximal à afficher par page.
		if (isset($_REQUEST['nb']) && preg_match('`^[1-9]\d{0,3}$`', $_REQUEST['nb']) && $_REQUEST['nb'] != $this->config['admin_galerie_nb']) {
			$this->update_option('admin_galerie_nb', $_REQUEST['nb']);
			$this->config['admin_galerie_nb'] = $_REQUEST['nb'];
			$this->template['infos']['nb_limit'] = $_REQUEST['nb'];
			$startnum = 0;
		} else {
			$this->template['infos']['nb_limit'] = $this->config['admin_galerie_nb'];
		}
		$limit = $this->config['admin_galerie_nb'];
		$this->template['infos']['startnum'] = $startnum;

		// Personnalisation: trie des objets.
		if (isset($_REQUEST['ordre']) && preg_match('`^\w{2,20}$`', $_REQUEST['ordre']) && $_REQUEST['ordre'] != $this->config['admin_galerie_ordre']) {
			$this->update_option('admin_galerie_ordre', $_REQUEST['ordre']);
			$this->config['admin_galerie_ordre'] = $_REQUEST['ordre'];
			$this->template['infos']['objets_ordre'] = $_REQUEST['ordre'];
		} else {
			$this->template['infos']['objets_ordre'] = $this->config['admin_galerie_ordre'];
		}
		$ordre = $this->template['infos']['objets_ordre'];

		// Personnalisation: sens du trie des objets.
		if (isset($_REQUEST['sens']) && preg_match('`^(ASC|DESC)$`', $_REQUEST['sens']) && $_REQUEST['sens'] != $this->config['admin_galerie_sens']) {
			$this->update_option('admin_galerie_sens', $_REQUEST['sens']);
			$this->config['admin_galerie_sens'] = $_REQUEST['sens'];
			$this->template['infos']['objets_sens'] = $_REQUEST['sens'];
		} else {
			$this->template['infos']['objets_sens'] = $this->config['admin_galerie_sens'];
		}
		$sens = $this->template['infos']['objets_sens'];

		// Récupération des informations de la catégorie actuelle.
		$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_id = "' . $cat . '"';
		$cat_infos = $this->mysql->select($mysql_requete, 11);

		// On vérifie si la catégorie existe.
		if ($cat_infos == 'vide') {
			header('Location: index.php?section=galerie&page=gestion');
		}

		// Catégorie ou album ?
		$this->template['infos']['objet_type'] = ($cat_infos['categorie_derniere_modif']) ? 'alb' : 'cat';

		// Personnalisation: filtre.
		$filtre = $this->config['admin_galerie_filtre'];
		if (isset($_REQUEST['filtre']) && preg_match('`^(tous|actif|inactif)$`', $_REQUEST['filtre']) && $filtre != $_REQUEST['filtre']) {
			$this->update_option('admin_galerie_filtre', $_REQUEST['filtre']);
			$filtre = $_REQUEST['filtre'];
			$startnum = 0;
		}
		$this->template['infos']['galerie_filtre'] = $filtre;
		$cat_img = ($cat_infos['categorie_derniere_modif']) ? MYSQL_PREF . 'images.image' : MYSQL_PREF . 'categories.categorie';
		switch ($filtre) {
			case 'actif' :
				$filtre = ' AND ' . $cat_img . '_visible = "1"';
				break;
			case 'inactif' :
				$filtre = ' AND ' . $cat_img . '_visible != "1"';
				break;
			case 'tous' :
				$filtre = '';
				break;
		}

		// Liste des trie possibles par type d'objet.
		$this->template['infos']['liste_trie'] = array('nom' => 'Nom', 'date' => 'Date d\'ajout');

		// Récupération des objets de la catégorie actuelle.
		if ($this->template['infos']['objet_type'] == 'alb') {
			$from_where = ' FROM ' . MYSQL_PREF . 'images LEFT JOIN ' . MYSQL_PREF . 'users USING(user_id)
						   WHERE ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $cat_infos['categorie_chemin'] . '%"' . $filtre;
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.*,
									 ' . MYSQL_PREF . 'users.user_login
									 ' . $from_where . ' 
							ORDER BY ' . MYSQL_PREF . 'images.image_' . $ordre . ' ' . $sens . ',
									 ' . MYSQL_PREF . 'images.image_id ' . $sens . ' 
							   LIMIT ' . $startnum . ',' . $limit;
		} else {
			$actuel = ($cat_infos['categorie_chemin'] == '.') ? '' : $cat_infos['categorie_chemin'];
			$from_where = ' FROM ' . MYSQL_PREF . 'categories LEFT JOIN ' . MYSQL_PREF . 'users USING(user_id)
						   WHERE ' . MYSQL_PREF . 'categories.categorie_chemin REGEXP "^' . $actuel . '[^/]+/$"' . $filtre;
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'categories.*,
									 ' . MYSQL_PREF . 'users.user_login 
									 ' . $from_where . ' 
							ORDER BY ' . MYSQL_PREF . 'categories.categorie_' . $ordre . ' ' . $sens . ' 
							   LIMIT ' . $startnum . ',' . $limit;
		}
		$this->template['objets'] = $this->mysql->select($mysql_requete);

		// On récupère les dimensions des représentants de chaque objet.
		$where_paths = '';
		if (is_array($this->template['objets'])) {
			if ($this->template['infos']['objet_type'] != 'alb') {
				for ($i = 0; $i < count($this->template['objets']); $i++) {
					if ($this->template['objets'][$i]['image_representant_id']) {
						$where_paths .= 'image_id = "' . $this->template['objets'][$i]['image_representant_id'] . '" OR ';
					}
				}
				$where_paths = preg_replace('`OR $`', '', $where_paths);
				if ($where_paths) {
					$mysql_requete = 'SELECT image_id,
											 image_chemin,
											 image_largeur,
											 image_hauteur
										FROM ' . MYSQL_PREF . 'images
									   WHERE ' . $where_paths;
					$this->template['representants'] = $this->mysql->select($mysql_requete, 4);
				}
			}
		}

		// Récupération du nombre d'objets de la catégorie actuelle.
		$mysql_requete = 'SELECT COUNT(*) ' . $from_where;
		if ($nc = $this->mysql->select($mysql_requete, 5)) {
			$this->template['infos']['nb_objets'] = $nc;
		} else {
			$this->template['infos']['nb_objets'] = 0;
		}

		// On détermine le nombre de pages et la page actuelle.
		$this->template['infos']['nb_pages'] = ceil(($this->template['infos']['nb_objets']) / $this->config['admin_galerie_nb']);
		for ($n = 0; $n < $this->template['infos']['nb_pages']; $n++) {
			$num = $n * $this->config['admin_galerie_nb'];
			$this->template['nav']['pages'][$n + 1]['page'] = $num;
			if ($num == $startnum) {
				$this->template['infos']['page_actuelle'] = $n + 1;
			}
		}

		// On détermine les pages suivantes, précédentes, de début et de fin.
		$this->template['nav']['suivante'][1] = $startnum + $this->config['admin_galerie_nb'];
		$this->template['nav']['precedente'][1] = $startnum - $this->config['admin_galerie_nb'];
		$this->template['nav']['premiere'][1] = 0;
		$this->template['nav']['derniere'][1] = ($this->template['infos']['nb_pages'] * $this->config['admin_galerie_nb']) - $this->config['admin_galerie_nb'];

		// On détermine la position de l'objet actuel.
		if ($startnum == 0) {
			$this->template['nav']['premiere'][0] = 1;
		}
		if ($this->template['nav']['precedente'][1] < 0) {
			$this->template['nav']['precedente'][0] = 1;
		}
		if ($this->template['nav']['suivante'][1] >= ($this->template['infos']['nb_pages'] * $this->config['admin_galerie_nb']) || 
		    $this->template['nav']['suivante'][1] >= $this->template['infos']['nb_objets']) {
			$this->template['nav']['suivante'][0] = 1;
		}
		if ($startnum >= $this->template['nav']['derniere'][1]) {
			$this->template['nav']['derniere'][0] = 1;
		}

		// Barre de position.
		$obj_chemin = $cat_infos['categorie_chemin'];
		$this->template['galerie']['position'] = '';
		if ($obj_chemin) {
			$parent_id = 0;
			$parent = dirname($obj_chemin);
			while ($parent != '.') {
				$mysql_requete = 'SELECT categorie_id,categorie_chemin,categorie_nom FROM ' . MYSQL_PREF . 'categories 
					WHERE categorie_chemin = "' . $parent . '/"';
				$p_i = $this->mysql->select($mysql_requete, 11);
				if (empty($parent_id)) {
					$parent_id = $p_i['categorie_id'];
				}
				$parent = dirname($p_i['categorie_chemin']);
				$this->template['galerie']['position'] = '%sep<a href="index.php?section=galerie&amp;page=gestion&amp;cat=' . 
					$p_i['categorie_id'] . '">' . 
					strip_tags($p_i['categorie_nom']) . '</a>' . 
					$this->template['galerie']['position'];
			}
		}
		$pos_actuel = ($this->template['infos']['cat'] > 1) ? '' : ' class="pos_actuel"';
		$this->template['galerie']['position'] = '<a' . $pos_actuel . ' href="index.php?section=galerie&amp;page=gestion&amp;cat=1' . 
				'">galerie</a>' . 
				$this->template['galerie']['position'];
		if ($this->template['infos']['cat'] > 1) {
			$this->template['galerie']['position'] .= '%sep<a href="index.php?section=galerie&amp;page=gestion&amp;cat=' . 
					$this->template['infos']['cat'] . 
					'" class="pos_actuel">' . 
					strip_tags($cat_infos['categorie_nom']) . '</a>';
		}

		// Lien retour.
		if ($cat > 1) {
			$dir = dirname($cat_infos['categorie_chemin']);
			$dir = ($dir == '.') ? '' : $dir . '/';
			$mysql_requete = 'SELECT categorie_id FROM ' . MYSQL_PREF . 'categories 
				WHERE categorie_chemin REGEXP "^' . $dir . '[^/]+/$" 
				ORDER BY categorie_' . $ordre . ' ' . $sens;
			$voisines = $this->mysql->select($mysql_requete, 2);
			$ids = array_flip(array_keys($voisines));
			$objet_num = $ids[$cat_infos['categorie_id']] + 1;
			$parent_page = (ceil($objet_num / $limit) * $limit) - $limit;
			$prvs = (empty($parent_page)) ? '' : '&amp;startnum=' . $parent_page ;
			$parent_id = (empty($parent_id)) ? 1 : $parent_id;
			$this->template['nav']['retour'] = './?section=galerie&amp;page=gestion&amp;cat=' . $parent_id . $prvs;
		}

		// On génère une liste déroulante de la hiérarchie de la galerie,
		// ainsi que qu'une liste pour le déplacement des objets.
		$selected = ($cat == 1) ? ' selected="selected"' : '';
		$this->template['nav']['hierarchie'] = '<select class="albums_list" name="cat" onchange="if (this.options[this.selectedIndex].value) window.location.href=\'?cat=\' + this.options[this.selectedIndex].value + \'&amp;section=galerie&amp;page=gestion\';" >';
		$this->template['nav']['deplace_img'] = '<select class="albums_list" name="vers">';
		if ($this->template['infos']['objet_type'] == 'cat' && $cat > 1) {
			$this->template['nav']['deplace_img'] .= '<option class="gal_hier_cat" value="1">galerie</option>';
		}
		$this->template['nav']['hierarchie'] .= '<option' . $selected . ' class="gal_hier_cat" value="1">galerie</option>';
		$this->template['nav']['deplace_cat'] = array();
		$mysql_requete = 'SELECT categorie_id,
								 categorie_nom,
								 categorie_chemin,
								 categorie_images,
								 categorie_derniere_modif
							FROM ' . MYSQL_PREF . 'categories
						   WHERE categorie_id != 1
						ORDER BY categorie_' . $ordre . ' ' . $sens;
		$categories = $this->mysql->select($mysql_requete);
		$path = $cat_infos['categorie_chemin'];
		if ($path == '.') {
			$path = '';
		}
		$this->galerie_hierarchie($categories, $path);
		if (isset($this->template['nav']['deplace_cat'][0])) {
			$this->template['display']['cat_move'] = 1;
		}
		$this->template['nav']['hierarchie'] .= '</select>';
		$this->template['nav']['deplace_img'] .= '</select>';

		// Message a afficher si la catégorie ou l'album est vide.
		if (!$this->template['infos']['nb_objets']) {
			$this->template['display']['cat_vide'] = 1;
			switch ($this->template['infos']['galerie_filtre']) {
				case 'actif' :
					$msg_img = ' active';
					$msg_cat = ' actif';
					break;
				case 'inactif' :
					$msg_img = ' inactive';
					$msg_cat = ' inactif';
					break;
				case 'tous' :
					$msg_img = '';
					$msg_cat = '';
					break;
			}
			if ($this->template['infos']['objet_type'] == 'alb') {
				$this->template['infos']['info']['cat_vide'] = 'L\'album ne contient aucune image' . $msg_img . '.';
			} else {
				$this->template['infos']['info']['cat_vide'] = 'La catégorie ne contient aucun objet' . $msg_cat . '.';
			}
		}
	}



	/*
	 *	Génère une liste déroulante de la hierarchie de la galerie.
	*/
	function galerie_hierarchie($categories, $apath, $path = '', $level = 1) {
		if (!is_array($categories)) {
			return;
		}
		foreach ($categories as $id => $cat) {
			if (preg_match('`^' . $path . '[^/]+/$`', $categories[$id]['categorie_chemin'])) {

				$actuel = $categories[$id];
				unset($categories[$id]);

				$selected = ($actuel['categorie_chemin'] == $apath) ? ' selected="selected"' : '';

				// Catégorie.
				if ($actuel['categorie_derniere_modif'] == 0) {
					$this->template['nav']['hierarchie'] .= '<option' . $selected . ' class="gal_hier_cat" value="' . $actuel['categorie_id'] . '">';
					$this->template['nav']['hierarchie'] .= str_repeat('&nbsp;', $level*4) . outils::html_specialchars(strip_tags($actuel['categorie_nom'])) . '</option>';
					if ($this->template['infos']['objet_type'] == 'alb') { 
						if (!preg_match('`' . $apath . '.+`', $actuel['categorie_chemin'])) {
							$this->template['nav']['deplace_img'] .= '<optgroup label="' . str_repeat('&nbsp;', $level*4) . outils::html_specialchars(strip_tags($actuel['categorie_nom'])) . '">';
						}
					} else {
						array_push($this->template['nav']['deplace_cat'], 
								array(
								'id' => $actuel['categorie_id'],
								'nom' => $actuel['categorie_nom'],
								'chemin' => $actuel['categorie_chemin'],
								'space' => $level
								)
							);
					}
					$this->galerie_hierarchie($categories, $apath, $actuel['categorie_chemin'], $level+1);

				// Album.
				} else {
					$this->template['nav']['hierarchie'] .= '<option' . $selected . ' value="' . $actuel['categorie_id'] . '">';
					$this->template['nav']['hierarchie'] .= str_repeat('&nbsp;', $level*4) . '|--&nbsp;' . outils::html_specialchars(strip_tags($actuel['categorie_nom'])) . '</option>';
					if ($actuel['categorie_chemin'] != $apath && $this->template['infos']['objet_type'] == 'alb') {
						$this->template['nav']['deplace_img'] .= '<option value="' . $actuel['categorie_id'] . '">';
						$this->template['nav']['deplace_img'] .= str_repeat('&nbsp;', $level*4) . '|--&nbsp;' . outils::html_specialchars(strip_tags($actuel['categorie_nom'])) . '</option>';
					}
				}

				if ($actuel['categorie_derniere_modif'] == 0 
				   && !preg_match('`' . $apath . '.+`', $actuel['categorie_chemin']) 
				   && $this->template['infos']['objet_type'] == 'alb') {
					$this->template['nav']['deplace_img'] .= '</optgroup>';
				}
			}
		}
	}



	/*
	 *	Changement du représentant d'une catégorie ou d'un album.
	*/
	function galerie_representant() {

		$this->template['infos']['title'] = 'représentants';

		$nb_max = 48;

		if (!preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['cat']) 
		 || empty($_REQUEST['obj']) || !preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['obj'])
		 || empty($_REQUEST['sub_obj']) || !preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['sub_obj'])) {
			header('Location: index.php?section=galerie&page=gestion');
		}

		// Informations de la catégorie.
		$mysql_requete = 'SELECT categorie_nom,categorie_derniere_modif,categorie_chemin,categorie_visible FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_id = "' . $_REQUEST['sub_obj'] . '"';
		$cat_infos = $this->mysql->select($mysql_requete, 11);

		// Page à afficher.
		if (isset($_REQUEST['startnum']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['startnum'])) {
			$startnum = $_REQUEST['startnum'];
		} else {
			$startnum = 0;
		}

		// Récupération des images de la catégorie.
		$visible = ($cat_infos['categorie_visible']) ? ' AND image_visible = "1"': '';
		$from_where = 'FROM ' . MYSQL_PREF . 'images 
			WHERE image_chemin LIKE "' . $cat_infos['categorie_chemin'] . '%" ' . $visible;
		$mysql_requete = 'SELECT image_id,image_chemin,image_nom ' . $from_where . '
			ORDER BY image_' . $this->config['admin_galerie_ordre'] . ' ' . $this->config['admin_galerie_sens'] . ' 
			LIMIT ' . $startnum . ',' . $nb_max;
		$this->template['vignettes'] = $this->mysql->select($mysql_requete);

		// Récupération du nombre d'images de la catégorie.
		$mysql_requete = 'SELECT COUNT(*) ' . $from_where;
		if (!$img_nb = $this->mysql->select($mysql_requete, 5)) {
			$img_nb = 0;
		}

		// On détermine le nombre de pages et la page actuelle.
		$this->template['infos']['nb_pages'] = ceil(($img_nb) / $nb_max);
		for ($n = 0; $n < $this->template['infos']['nb_pages']; $n++) {
			$num = $n * $nb_max;
			$this->template['nav']['pages'][$n + 1]['page'] = $num;
			if ($num == $startnum) {
				$this->template['infos']['page_actuelle'] = $n + 1;
			}
		}

		// On détermine les pages suivantes, précédentes, de début et de fin.
		$this->template['nav']['suivante'][1] = $startnum + $nb_max;
		$this->template['nav']['precedente'][1] = $startnum - $nb_max;
		$this->template['nav']['premiere'][1] = 0;
		$this->template['nav']['derniere'][1] = ($this->template['infos']['nb_pages'] * $nb_max) - $nb_max;

		// On détermine la position de l'objet actuel.
		if ($startnum == 0) {
			$this->template['nav']['premiere'][0] = 1;
		}
		if ($this->template['nav']['precedente'][1] < 0) {
			$this->template['nav']['precedente'][0] = 1;
		}
		if ($this->template['nav']['suivante'][1] >= ($this->template['infos']['nb_pages'] * $nb_max) || 
		    $this->template['nav']['suivante'][1] >= $img_nb) {
			$this->template['nav']['suivante'][0] = 1;
		}
		if ($startnum >= $this->template['nav']['derniere'][1]) {
			$this->template['nav']['derniere'][0] = 1;
		}

		// Si c'est une catégorie, on génère une liste déroulante pour n'afficher
		// que les représentants de chaque sous-catégorie.
		$mysql_requete = 'SELECT categorie_id,categorie_nom,categorie_chemin,categorie_derniere_modif FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_id = "' . $_REQUEST['obj'] . '"';
		$obj = $this->mysql->select($mysql_requete, 11);
		if (!$obj['categorie_derniere_modif']) {
			$this->template['display']['rep_nav'] = 1;
			$this->template['infos']['type_nom'] = 'la catégorie « ' . $obj['categorie_nom'] . ' »';
			$selected = (empty($_REQUEST['sub_obj']) || $_REQUEST['sub_obj'] == $_REQUEST['obj']) ? ' selected="selected"' : '';
			$this->template['nav']['hierarchie'] = '<select name="sub_obj" onchange="if (this.options[this.selectedIndex].value) window.location.href=\'?sub_obj=\' + this.options[this.selectedIndex].value + \'&amp;section=representant&amp;str=' . $_REQUEST['str'] . '&amp;cat=' . $_REQUEST['cat'] . '&amp;obj=' . $_REQUEST['obj'] . '\';" >';
			$this->template['nav']['hierarchie'] .= '<option' . $selected . ' class="gal_hier_cat" value="' . $obj['categorie_id'] . '">' . $obj['categorie_nom'] . '</option>';
			$this->galerie_hierarchie_thumbs($obj['categorie_chemin']);
			$this->template['nav']['hierarchie'] .= '</select>';
		} else {
			$this->template['infos']['type_nom'] = 'l\'album ' . $obj['categorie_nom'];
		}

		$this->template['infos']['section'] = 'section=representant&cat=' . $_REQUEST['cat'] . '&str=' . $_REQUEST['str'] . '&obj=' . $_REQUEST['obj'] . '&sub_obj=' . $_REQUEST['sub_obj'];
	}



	/*
	 *	Liste déroulante pour la page représentant.
	*/
	function galerie_hierarchie_thumbs($parent, $space = '&nbsp;&nbsp;&nbsp;&nbsp;') {
		$mysql_requete = 'SELECT categorie_id,categorie_nom,categorie_chemin,categorie_visible,categorie_derniere_modif 
			FROM ' . MYSQL_PREF . 'categories 
			WHERE categorie_chemin REGEXP "^' . $parent . '[^/]+/$" 
			ORDER BY categorie_' . $this->config['admin_galerie_ordre'] . ' ' . $this->config['admin_galerie_sens'];
		$cats = $this->mysql->select($mysql_requete);
			if ($cats != 'vide') {
				for ($i = 0; $i < count($cats); $i++) {
					$obj = (isset($_REQUEST['sub_obj'])) ? $_REQUEST['sub_obj'] : $_REQUEST['obj'];
					$selected = ($cats[$i]['categorie_id'] == $obj) ? ' selected="selected"' : '';
					if ($cats[$i]['categorie_derniere_modif'] == 0) {
						if ($cats[$i]['categorie_visible']) {
							$this->template['nav']['hierarchie'] .= '<option' . $selected . ' class="gal_hier_cat" value="' . $cats[$i]['categorie_id'] . '">' . $space . htmlentities($cats[$i]['categorie_nom']) . '</option>';
						}
						$this->galerie_hierarchie_thumbs($cats[$i]['categorie_chemin'], $space . '&nbsp;&nbsp;&nbsp;&nbsp;');
					} else {
						if ($cats[$i]['categorie_visible']) {
							$this->template['nav']['hierarchie'] .= '<option' . $selected . ' value="' . $cats[$i]['categorie_id'] . '">' . $space . '|--&nbsp;' . htmlentities($cats[$i]['categorie_nom']) . '</option>';
						}
					}
				}
			}
	}



	/*
	  *	Affichage des votes.
	*/
	function display_votes() {

		if (isset($_POST['vote_action']) || isset($_GET['delete'])) {
			$this->gestion_votes();
		}

		// Page à afficher.
		$startnum = 0;
		if (isset($_GET['startnum']) && preg_match('`^[1-9]\d{0,9}$`', $_GET['startnum'])) {
			$startnum = $_GET['startnum'];
		}

		// Nombre de votes par page.
		if (isset($_REQUEST['nb']) && preg_match('`^[1-9]\d{0,3}$`', $_REQUEST['nb']) && $_REQUEST['nb'] != $this->config['admin_vote_nb']) {
			$this->update_option('admin_vote_nb', $_REQUEST['nb']);
			$this->config['admin_vote_nb'] = $_REQUEST['nb'];
			$this->template['infos']['nb_votes'] = $_REQUEST['nb'];
			$startnum = 0;
		} else {
			$this->template['infos']['nb_votes'] = $this->config['admin_vote_nb'];
		}
		$this->template['infos']['nb_votes'] = $this->config['admin_vote_nb'];

		// Trie des votes : ordre.
		if (isset($_REQUEST['sort']) && preg_match('`^vote_(date|ip|note)|image_nom|categorie_nom$`', $_REQUEST['sort']) && $_REQUEST['sort'] != $this->config['admin_vote_ordre']) {
			$this->template['infos']['vote_sort'] = $_REQUEST['sort'];
			$this->update_option('admin_vote_ordre', $_REQUEST['sort']);
		} else {
			$this->template['infos']['vote_sort'] = $this->config['admin_vote_ordre'];
		}

		// Trie des votes : sens.
		if (isset($_REQUEST['sens']) && preg_match('`^ASC|DESC$`', $_REQUEST['sens']) && $_REQUEST['sens'] != $this->config['admin_vote_sens']) {
			$this->template['infos']['vote_sens'] = $_REQUEST['sens'];
			$this->update_option('admin_vote_sens', $_REQUEST['sens']);
		} else {
			$this->template['infos']['vote_sens'] = $this->config['admin_vote_sens'];
		}



		$this->template['infos']['title'] = 'gestion des votes';
		$this->template['infos']['startnum'] = $startnum;

		$params = '';

		// Objet actuel.
		$categorie_dernier_modif = '';
		if (isset($_GET['img']) && preg_match('`^[1-9]\d{0,9}$`', $_GET['img'])) {
			$obj = $_GET['img'];
			$obj_type = 'img';
			$obj_type_ext = 'image';
			$params .= '&img=' . $_GET['img'];
		} elseif (isset($_GET['cat']) && preg_match('`^[1-9]\d{0,9}$`', $_GET['cat'])) {
			$obj = $_GET['cat'];
			$obj_type = 'cat';
			$obj_type_ext = 'categorie';
			$params .= '&cat=' . $_GET['cat'];
			$categorie_dernier_modif = ', categorie_derniere_modif';
		} else {
			$obj = 1;
			$obj_type = 'cat';
			$obj_type_ext = 'categorie';
			$categorie_dernier_modif = ', categorie_derniere_modif';
		}
		$this->template['infos']['obj'] = $obj;
		$this->template['infos']['obj_type'] = $obj_type;

		$mysql_requete = 'SELECT ' . $obj_type_ext . '_chemin,
								 ' . $obj_type_ext . '_nom
								 ' . $categorie_dernier_modif . '
							FROM ' . MYSQL_PREF . $obj_type_ext . 's 
						   WHERE ' . $obj_type_ext . '_id = "' . $obj . '"';
		$obj = $this->mysql->select($mysql_requete, 11);
		if (!is_array($obj)) {
			header('Location:index.php?section=votes');
			exit;
		}
		$obj_chemin = ($obj[$obj_type_ext . '_chemin'] == '.') ? '' : $obj[$obj_type_ext . '_chemin'];

		// Date.
		$date = '';
		if (!empty($_GET['date']) && preg_match('`^\d{1,11}$`', $_GET['date'])) {
			$ts = $_GET['date'];
			$jour = date('d', $ts);
			$mois = date('m', $ts);
			$annee = date('Y', $ts);
			$debut = mktime(0,0,0,$mois,$jour,$annee);
			$fin = mktime(23,59,59,$mois,$jour,$annee);
			$date  = ' AND ' . MYSQL_PREF . 'votes.vote_date >= "' . $debut . '"';
			$date .= ' AND ' . MYSQL_PREF . 'votes.vote_date <= "' . $fin . '"';
			$params .= '&date=' . $_GET['date'];
		}

		// IP.
		$IP = '';
		if (!empty($_GET['ip'])) {
			$IP = ' AND ' . MYSQL_PREF . 'votes.vote_ip = "' . outils::protege_mysql($_GET['ip'], $this->mysql->lien) . '"';
			$params .= '&ip=' . $_GET['ip'];
		}

		// Image.
		$img = '';
		if (isset($_GET['img']) && preg_match('`^\d{1,12}$`', $_GET['img'])) {
			$img = ' AND ' . MYSQL_PREF . 'votes.image_id = "' . $_GET['img'] . '"';
			$params .= '&img=' . $_GET['img'];
		}

		// WHERE.
		$from_where = 'FROM ' . MYSQL_PREF . 'votes,
							' . MYSQL_PREF . 'images,
							' . MYSQL_PREF . 'categories
					  WHERE ' . MYSQL_PREF . 'votes.image_id = ' . MYSQL_PREF . 'images.image_id
						AND ' . MYSQL_PREF . 'images.categorie_parent_id = ' . MYSQL_PREF . 'categories.categorie_id
						AND ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $obj_chemin . '%"'
							  . $date
							  . $IP
							  . $img;

		// On récupère les votes.
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'votes.vote_id,
								 ' . MYSQL_PREF . 'votes.vote_date AS vote_date,
								 ' . MYSQL_PREF . 'votes.vote_note AS vote_note,
								 ' . MYSQL_PREF . 'votes.vote_ip AS vote_ip,
								 ' . MYSQL_PREF . 'images.image_id,
								 ' . MYSQL_PREF . 'images.image_chemin,
								 ' . MYSQL_PREF . 'images.image_nom AS image_nom,
								 ' . MYSQL_PREF . 'images.image_largeur,
								 ' . MYSQL_PREF . 'images.image_hauteur,
								 ' . MYSQL_PREF . 'categories.categorie_id,
								 ' . MYSQL_PREF . 'categories.categorie_nom AS categorie_nom
								 ' . $from_where . '
						ORDER BY ' . $this->template['infos']['vote_sort'] . ' ' . $this->template['infos']['vote_sens'] . '
						   LIMIT ' . $startnum . ',' . $this->config['admin_vote_nb'];
		$this->template['votes'] = $this->mysql->select($mysql_requete);
		if (!is_array($this->template['votes'])) {
			$delete = '';
			if (isset($this->template['infos']['erreur'])) {
				$delete = '&s=error';
			} elseif (isset($this->template['infos']['action'])) {
				$delete = '&s=ok';
			}
			if ($delete) {
				header('Location: ?section=votes' . $delete);
				exit;
			}
		}

		if (isset($_GET['s'])) {
			if ($_GET['s'] == 'ok') {
				$this->template['infos']['action']['delete_votes'] = 'Tous les votes sélectionnés ont été supprimés.';
			} elseif ($_GET['s'] == 'errror') {
				$this->template['infos']['erreur']['delete_votes'] = '[' . __LINE__ . '] Des erreurs se sont produites lors de la suppression des votes.';
			}
		}

		// Nombre total de votes.
		$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'votes.image_id) ' . $from_where;
		$this->template['infos']['nb_objets'] = $this->mysql->select($mysql_requete, 5);


		// On détermine le nombre de pages et la page actuelle.
		$this->template['infos']['nb_pages'] = ceil(($this->template['infos']['nb_objets']) / $this->config['admin_vote_nb']);
		for ($n = 0; $n < $this->template['infos']['nb_pages']; $n++) {
			$num = $n * $this->config['admin_vote_nb'];
			$this->template['nav']['pages'][$n + 1]['page'] = $num;
			if ($num == $startnum) {
				$this->template['infos']['page_actuelle'] = $n + 1;
			}
		}

		// On détermine les pages suivantes, précédentes, de début et de fin.
		$this->template['nav']['suivante'][1] = $startnum + $this->config['admin_vote_nb'];
		$this->template['nav']['precedente'][1] = $startnum - $this->config['admin_vote_nb'];
		$this->template['nav']['premiere'][1] = 0;
		$this->template['nav']['derniere'][1] = ($this->template['infos']['nb_pages'] * $this->config['admin_vote_nb']) - $this->config['admin_vote_nb'];

		// On détermine la position de l'objet actuel.
		if ($startnum == 0) {
			$this->template['nav']['premiere'][0] = 1;
		}
		if ($this->template['nav']['precedente'][1] < 0) {
			$this->template['nav']['precedente'][0] = 1;
		}
		if ($this->template['nav']['suivante'][1] >= ($this->template['infos']['nb_pages'] * $this->config['admin_vote_nb']) || 
		    $this->template['nav']['suivante'][1] >= $this->template['infos']['nb_objets']) {
			$this->template['nav']['suivante'][0] = 1;
		}
		if ($startnum >= $this->template['nav']['derniere'][1]) {
			$this->template['nav']['derniere'][0] = 1;
		}

		// Type.
		$type = 'cat';
		if (isset($obj['categorie_derniere_modif'])) {
			if (!empty($obj['categorie_derniere_modif'])) {
				$type = 'alb';
			}
		} else {
			$type = 'img';
		}
		$this->template['infos']['obj_type'] = $type;

		// Sous-objets.
		$subcats = true;
		if (isset($obj['categorie_derniere_modif']) && ($IP || $date)) {
			$subcats = false;
		}
		if (is_array($this->template['votes']) && $subcats) {
			$sub_cat = '';
			if (empty($obj['categorie_derniere_modif'])) {
				$mysql_requete = 'SELECT categorie_id,categorie_nom,categorie_chemin FROM ' . MYSQL_PREF . 'categories 
					WHERE categorie_chemin REGEXP "^' . $obj_chemin . '[^/]+/$" 
					  AND categorie_id NOT IN (1)
					  AND (categorie_votes > 0 OR categorie_votes_inactive > 0)';
				$sub_cat = $this->mysql->select($mysql_requete);
				if (is_array($sub_cat)) {
					for ($i = 0; $i < count($sub_cat); $i++) {
						$this->template['votes']['sub_item'][$i] = $sub_cat[$i];
						$this->template['display']['subcats'] = 1;
						$this->template['infos']['sub_objects'] = 'categorie';
					}
				}
			} else {
				$mysql_requete = 'SELECT DISTINCT ' . MYSQL_PREF . 'images.image_id,
										 ' . MYSQL_PREF . 'images.image_nom,
										 ' . MYSQL_PREF . 'images.image_chemin
									FROM ' . MYSQL_PREF . 'images INNER JOIN ' . MYSQL_PREF . 'votes USING (image_id)
								   WHERE ' . MYSQL_PREF . 'images.image_chemin REGEXP "^' . $obj_chemin . '[^/]+$"
								     AND ' . MYSQL_PREF . 'images.image_note > 0 '
										   . $date
										   . $IP
										   . $img;
				$imgs = $this->mysql->select($mysql_requete);
				if (is_array($imgs)) {
					for ($i = 0; $i < count($imgs); $i++) {
						$this->template['votes']['sub_item'][$i] = $imgs[$i];
						$this->template['display']['subcats'] = 1;
						$this->template['infos']['sub_objects'] = 'image';
					}
				}
			}
		}

		// Barre de position.
		$this->template['votes']['position'] = '';
		if (is_array($this->template['votes'])) {
			if ($obj_chemin) {
				$parent = dirname($obj_chemin);
				while ($parent != '.') {
					$mysql_requete = 'SELECT categorie_id,
											 categorie_chemin,
											 categorie_nom
										FROM ' . MYSQL_PREF . 'categories 
									   WHERE categorie_chemin = "' . $parent . '/"';
					$p_i = $this->mysql->select($mysql_requete, 11);
					$parent = dirname($p_i['categorie_chemin']);
					$this->template['votes']['position'] = '%sep<a href="index.php?section=votes&amp;cat=' . 
						$p_i['categorie_id'] . '">' . 
						strip_tags($p_i['categorie_nom']) . '</a>' . 
						$this->template['votes']['position'];
				}
			}
			$pos_actuel = ($this->template['infos']['obj'] > 1) ? '' : ' class="pos_actuel"' ;
			$this->template['votes']['position'] = '<a' . $pos_actuel . ' href="index.php?section=votes">galerie</a>'
					. $this->template['votes']['position'];
			if ($this->template['infos']['obj'] > 1) {
				$this->template['votes']['position'] .= '%sep<a href="index.php?section=votes&amp;' . $obj_type . '=' . 
						$this->template['infos']['obj'] .  
						'" class="pos_actuel">' . 
						strip_tags($obj[$obj_type_ext . '_nom']) . '</a>';
			}
		}

		$this->template['infos']['section'] = 'section=votes&' . $params;

	}



	/*
	  *	Gestion des votes
	*/
	function gestion_votes() {

		if (isset($_GET['delete']) && $_GET['delete'] == 'all') {

			$this->verifVID();

			if (empty($_GET['date']) && empty($_GET['ip'])) {

				$where = ' WHERE ';
				$ok = true;

				// Suppression de tous les votes d'une image.
				if (isset($_GET['img']) && preg_match('`^\d{1,12}$`', $_GET['img'])) {

					$where .= MYSQL_PREF . 'images.image_id = "' . $_GET['img'] . '" ';

				// Suppression de tous les votes d'une catégorie.
				} elseif ($cat_path = $this->get_categorie_vote_path()) {

					$where .= MYSQL_PREF . 'images.image_chemin LIKE "' . $cat_path . '%"';

				// Suppression de tous les votes de la galerie.
				} elseif (empty($_GET['cat']) || $_GET['cat'] == 1) {

					$where = '';

				// On redirige.
				} else {

					header('Location: index.php?section=votes');
					exit;

				}

				// On supprime les votes de la table des votes.
				$where_join = ($where == '') ? '' : ' AND ' . MYSQL_PREF . 'votes.image_id = ' . MYSQL_PREF . 'images.image_id';
				$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'votes
										USING ' . MYSQL_PREF . 'votes,
											  ' . MYSQL_PREF . 'images '
												. $where 
												. $where_join;
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = false;
				}

				// On UPDATE la table des images.
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images 
									 SET image_votes = 0,
										 image_note = 0 ' . $where;
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = false;
				}

				// On UPDATE la table des catégories.
				if ($this->update_categories_vote() === false) {
					$ok = false;
				}

			} elseif (isset($_GET['date']) || isset($_GET['ip'])) {

				$ok = true;

				// On récupère les id de toutes les images correspondant aux critères d'affichage.
				$mysql_requete = 'SELECT ' . MYSQL_PREF . 'votes.vote_id,
										 ' . MYSQL_PREF . 'votes.image_id,
										 ' . MYSQL_PREF . 'votes.vote_note,
										 ' . MYSQL_PREF . 'images.image_chemin
									FROM ' . MYSQL_PREF . 'votes LEFT JOIN ' . MYSQL_PREF . 'images USING(image_id)
								   WHERE ' . $this->get_criteres_vote_mysql();
				$images_id = $this->mysql->select($mysql_requete);

				if (!$this->delete_votes_images_id($images_id)) {
					$ok = false;
				}
				
				if ($this->update_categories_vote($images_id) === false) {
					$ok = false;
				}

			}

		} elseif (isset($_POST['vote_action']) 
			   && isset($_POST['vote_selection']) && is_array($_POST['vote_selection'])) {

			$ok = true;
			$vote_ids = '';
			foreach ($_POST['vote_selection'] as $id => $e) {
				if (preg_match('`^\d{1,12}$`', $id)) {
					$vote_ids .= ',"' . $id . '"';
				}
			}

			// On récupère les id de toutes les images sélectionnées.
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'votes.vote_id,
									 ' . MYSQL_PREF . 'votes.image_id,
									 ' . MYSQL_PREF . 'votes.vote_note,
									 ' . MYSQL_PREF . 'images.image_chemin
								FROM ' . MYSQL_PREF . 'votes LEFT JOIN ' . MYSQL_PREF . 'images USING (image_id)
							   WHERE ' . MYSQL_PREF . 'votes.vote_id IN(' . substr($vote_ids,1) . ')';
			$images_id = $this->mysql->select($mysql_requete);

			if (!$this->delete_votes_images_id($images_id)) {
				$ok = false;
			}

			if (!$this->update_categories_vote($images_id)) {
				$ok = false;
			}

		}

		if (isset($ok)) {
			if ($ok) {
				$this->template['infos']['action']['delete_votes'] = 'Tous les votes sélectionnés ont été supprimés.';
			} else {
				$this->template['infos']['erreur']['delete_votes'] = '[' . __LINE__ . '] Des erreurs se sont produites lors de la suppression des votes.';
			}
		}

	}

	function delete_votes_images_id($images_id) {

		if (is_array($images_id)) {

			$ok = true;
			$vote_ids = '';

			for ($i = 0; $i < count($images_id); $i++) {

				$mysql_requete =
				'UPDATE ' . MYSQL_PREF . 'images
					SET image_note = ((image_note * image_votes) - ' . $images_id[$i]['vote_note'] . ') / (image_votes - 1),
						image_votes = image_votes - 1
				  WHERE image_id = "' . $images_id[$i]['image_id'] . '"';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = false;
				}

				$vote_ids .= ',"' . $images_id[$i]['vote_id'] . '"';

			}

			$mysql_requete = 
			'DELETE FROM ' . MYSQL_PREF . 'votes
				   WHERE vote_id IN (' . substr($vote_ids,1) . ')';
			if (!$this->mysql->requete($mysql_requete)) {
				$ok = false;
			}

			return $ok;

		} else {

			return false;
		}

	}

	function get_criteres_vote_mysql() {

		// IP ou date.
		$ipdate = '';
		if (!empty($_GET['date']) && preg_match('`^\d{1,11}$`', $_GET['date'])) {
			$ts = $_GET['date'];
			$jour = date('d', $ts);
			$mois = date('m', $ts);
			$annee = date('Y', $ts);
			$debut = mktime(0,0,0,$mois,$jour,$annee);
			$fin = mktime(23,59,59,$mois,$jour,$annee);
			$ipdate = ' AND ' . MYSQL_PREF . 'votes.vote_date >= "' . $debut . '"';
			$ipdate .= ' AND ' . MYSQL_PREF . 'votes.vote_date <= "' . $fin . '"';

		} elseif (!empty($_GET['ip'])) {
			$ipdate = ' AND ' . MYSQL_PREF . 'votes.vote_ip = "' . outils::protege_mysql($_GET['ip'], $this->mysql->lien) . '"';
		}

		// Objet.
		$obj = '';
		if (isset($_GET['img']) && preg_match('`^\d{1,12}$`', $_GET['img'])) {
			$obj = ' AND ' . MYSQL_PREF . 'votes.image_id = "' . $_GET['img'] . '"';

		} elseif ($cat_path = $this->get_categorie_vote_path()) {
			$obj = ' AND ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $cat_path . '%"';
		}

		return ' 1=1 ' . $ipdate . $obj;

	}

	function get_categorie_vote_path() {

		if (isset($_GET['cat']) && preg_match('`^\d{1,12}$`', $_GET['cat']) && $_GET['cat'] > 1) {
			$mysql_requete = 'SELECT categorie_chemin 
								FROM ' . MYSQL_PREF . 'categories
							   WHERE categorie_id = "' . $_GET['cat'] . '"';
			$path = $this->mysql->select($mysql_requete, 5);
			if ($path && $path != 'vide') {
				return $path;
			}
		}

	}

	function update_categories_vote($images_id = '') {

		$paths = '';
		if (is_array($images_id)) {
			$paths = '"."';
			for ($i = 0; $i < count($images_id); $i++) {
				$p = dirname($images_id[$i]['image_chemin']) . '/';
				while ($p != './') {
					$paths .= ',"' . $p . '"';
					$p = dirname($p) . '/';
				}
			}
			$paths = ' WHERE categorie_chemin IN (' . $paths . ') ';
		}

		// On récupère toutes les informations de toutes les catégories.
		$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'categories
										' . $paths . '
						ORDER BY categorie_chemin';
		$categories = $this->mysql->select($mysql_requete);
		if (!is_array($categories)) {
			return false;
		}

		$ok = true;

		for ($i = 0; $i < count($categories); $i++) {

			$path = ($categories[$i]['categorie_chemin'] == '.') ? '' : $categories[$i]['categorie_chemin'];

			// On sélectionne les informations de votes des images activées.
			$mysql_requete = 'SELECT SUM(image_votes) as votes,
									 SUM(image_note*image_votes)/SUM(image_votes) as note
				FROM ' . MYSQL_PREF . 'images
				WHERE image_chemin LIKE "' . $path . '%" 
				  AND image_visible = "1"';
			$infos_actives = $this->mysql->select($mysql_requete, 11);
			if (!is_array($infos_actives)) {
				$ok = false;
			}

			// On sélectionne les informations de votes des images désactivées.
			$mysql_requete = 'SELECT SUM(image_votes) as votes,
									 SUM(image_note*image_votes)/SUM(image_votes) as note
				FROM ' . MYSQL_PREF . 'images
				WHERE image_chemin LIKE "' . $path . '%" 
				  AND image_visible = "0"';
			$infos_inactives = $this->mysql->select($mysql_requete, 11);
			if (!is_array($infos_inactives)) {
				$ok = false;
			}

			// Nombre de votes et note des images activées.
			$nb_votes_actives = ($infos_actives['votes']) ? $infos_actives['votes'] : '0';
			if ($nb_votes_actives != $categories[$i]['categorie_votes']) {
				$note_actives = ($infos_actives['note']) ? $infos_actives['note'] : '0';
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
					SET categorie_votes = "' . $nb_votes_actives . '",
						categorie_note = "' . $note_actives . '"
					WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = false;
				}
			}

			// Nombre de votes et note des images désactivées.
			$nb_votes_inactives = ($infos_inactives['votes']) ? $infos_inactives['votes'] : '0';
			if ($nb_votes_inactives != $categories[$i]['categorie_votes_inactive']) {
				$note_inactives = ($infos_inactives['note']) ? $infos_inactives['note'] : '0';
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories
					SET categorie_votes_inactive = "' . $nb_votes_inactives . '",
						categorie_note_inactive = "' . $note_inactives . '"
					WHERE categorie_chemin = "' . $categories[$i]['categorie_chemin'] . '"';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = false;
				}
			}

		}

		return $ok;

	}



	/*
	  *	Affichage des tags.
	*/
	function display_tags() {

		$this->template['infos']['title'] = 'gestion des tags';

		if (isset($_POST['tag_action'])) {
			$this->gestion_tags();
		}

		// On récupère tous les tags.
		$mysql_requete = 'SELECT DISTINCT ' . MYSQL_PREF . 'tags.tag_id,
						  COUNT(tag_id) AS tag_nombre
					FROM ' . MYSQL_PREF . 'tags
				GROUP BY tag_id
				ORDER BY tag_nombre DESC,tag_id ASC';
		$tags = $this->mysql->select($mysql_requete, 4);

		// On trie les tags.
		if (is_array($tags)) {
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



	/*
	  *	Gestion des tags.
	*/
	function gestion_tags() {

		// Rennomage.
		if (isset($_POST['tag_name']) && is_array($_POST['tag_name'])) {	
			$ok = true;
			$maj = false;
			foreach ($_POST['tag_name'] as $name => $new_name) {
				$new_name = preg_replace('`[\x5c#]`', '', $new_name);
				$new_name = trim($new_name);
				if ($name != $new_name && preg_match('`\w`i', $new_name)) {
					$maj = true;
					$mysql_requete = 'UPDATE IGNORE ' . MYSQL_PREF . 'tags
										 SET tag_id = "' . htmlentities($new_name) . '"
									   WHERE tag_id = "' . htmlentities($name) . '"';
					if (!$this->mysql->requete($mysql_requete)) {
						$ok = false;
					}
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images
										 SET image_tags = REPLACE(image_tags, ",' . htmlentities($name) . ',", ",' . htmlentities($new_name) . ',")
									   WHERE image_tags LIKE "%,' . htmlentities($name) . ',%"';
					if (!$this->mysql->requete($mysql_requete)) {
						$ok = false;
					}
				}
			}
			if ($maj && $ok) {
				$this->template['infos']['action']['change_name_tags'] = 'Changements effectués avec succès.';
			}
		}

		// Suppression.
		if (isset($_POST['tag_supp']) && is_array($_POST['tag_supp'])) {
			$ok = true;
			$tags = $_POST['tag_supp'];
			$tags = array_map('htmlentities', $tags);
			$tags = implode('","', $tags);
			$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'tags
									WHERE tag_id IN ("' . $tags . '")';
			if (!$this->mysql->requete($mysql_requete)) {
				$ok = false;
			}

			$tags = $_POST['tag_supp'];
			foreach ($tags as $tag) {
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images
									 SET image_tags = REPLACE(image_tags, "' . $tag . ',", "")
								   WHERE image_tags LIKE "%,' . $tag . ',%"';
				if (!$this->mysql->requete($mysql_requete)) {
					$ok = false;
				}
			}
			if ($ok) {
				$this->template['infos']['action']['change_name_tags'] = 'Les tags sélectionnés ont été supprimés.';
			} else {
				$this->template['infos']['erreur']['change_name_tags'] = 'Tous les tags sélectionnés n\'ont pas pu être supprimés.';
			}
		}
	}


	
	/*
	  *	Utilisateurs - général.
	*/
	function users_general() {

		$this->template['infos']['title'] = 'utilisateurs';

		$up_max = strtolower(ini_get('upload_max_filesize'));
		if (substr($up_max, -1, 1) == 'g') {
			$up_max = floatval(ini_get('upload_max_filesize')) * 1024 * 1024 . ' Ko';
		} elseif (substr($up_max, -1, 1) == 'm') {
			$up_max = floatval(ini_get('upload_max_filesize')) * 1024 . ' Ko';
		} elseif (substr($up_max, -1, 1) == 'k') {
			$up_max = floatval(ini_get('upload_max_filesize')) . ' Ko';
		} else {
			$up_max = floatval(ini_get('upload_max_filesize')) / 1024 . ' Ko';
		}
		$this->config['upload_max_filesize'] = $up_max;

		if (isset($_POST['u'])) {

			$this->verif_option_bin('u_general_membres_active', 'users_membres_active', 1);
			$this->verif_option_bin('u_general_membres_alert', 'users_membres_alert', 1);
			$this->verif_option_bin('u_general_membres_avatars', 'users_membres_avatars', 1);
			$this->verif_option_bin('u_general_upload_alert', 'users_upload_alert', 1);

			$_POST['u_general_upload_maxsize'] = floatval($_POST['u_general_upload_maxsize']);
			if (!empty($_POST['u_general_upload_maxsize'])
			 && $_POST['u_general_upload_maxsize'] >= 1
			 && $_POST['u_general_upload_maxsize'] <= $up_max) {
				$this->verif_option_word('u_general_upload_maxsize', 'users_upload_maxsize', 'nombre', '', 1);
			} else {
				$this->template['infos']['attention']['change_name_tags'] = 'Le poids maximum d\'une image doit être compris entre 1 Ko et ' . $up_max . '.';
			}

			$_POST['u_general_upload_maxwidth'] = floatval($_POST['u_general_upload_maxwidth']);
			$_POST['u_general_upload_maxheight'] = floatval($_POST['u_general_upload_maxheight']);
			if (!empty($_POST['u_general_upload_maxwidth'])
			 && !empty($_POST['u_general_upload_maxheight'])
			 && $_POST['u_general_upload_maxwidth'] >= 1
			 && $_POST['u_general_upload_maxwidth'] <= 20000
			 && $_POST['u_general_upload_maxheight'] >= 1
			 && $_POST['u_general_upload_maxheight'] <= 20000) {
				$this->verif_option_word('u_general_upload_maxwidth', 'users_upload_maxwidth', 'nombre', '', 1);
				$this->verif_option_word('u_general_upload_maxheight', 'users_upload_maxheight', 'nombre', '', 1);
			} else {
				$this->template['infos']['attention']['change_name_tags'] = 'Les dimensions maximum d\'une image doivent être comprises entre 1 pixels et 20000 pixels.';
			}

		}

		// Config.
		$this->template['config'] = $this->config;
	}


	
	/*
	  *	Utilisateurs - membres.
	*/
	function users_membres_action() {

		if (empty($_POST['membres']) || !is_array($_POST['membres'])) {
			return;
		}

		if (!empty($_POST['mass_delete'])) {
			foreach ($_POST['membres'] as $id => $p) {
				if (!empty($_POST['membres'][$id]['delete'])) {
					$maj = (isset($maj)) ? $maj : 1;
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET groupe_id = "0",
											 user_pass = "",
											 user_mail = "",
											 user_web = "",
											 user_avatar = "0",
											 user_date_creation = "0",
											 user_date_derniere_visite = "0",
											 user_ip_creation = "",
											 user_ip_derniere_visite = "",
											 user_session_id = ""
									   WHERE user_id = "' . $id . '"';
					if (!$this->mysql->requete($mysql_requete)) {
						$maj = 0;
						$this->template['infos']['erreur']['delete_' . $id] = '[' . __LINE__ . '] Impossible de supprimer le membre ' . $id;
					}
				}
			}
			if (!empty($maj)) {
				$this->template['infos']['action']['delete'] = 'Les membres sélectionnés ont été supprimés.';
			}
			return;
		}

		if (isset($_POST['mass_groupes'])) {
			foreach ($_POST['membres'] as $id => $p) {
				if (!empty($_POST['membres'][$id]['groupe'])
				 && !empty($_POST['membres'][$id]['groupe_actuel'])
				 && $_POST['membres'][$id]['groupe'] != $_POST['membres'][$id]['groupe_actuel']) {
					$maj = (isset($maj)) ? $maj : 1;
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET groupe_id = "' . $_POST['membres'][$id]['groupe'] . '"
									   WHERE user_id = "' . $id . '"';
					if (!$this->mysql->requete($mysql_requete)) {
						$maj = 0;
						$this->template['infos']['erreur']['change_groupe_' . $id] = '[' . __LINE__ . '][' . $id . '] Impossible de modifier le groupe.';
					}
				}
			}
			if (!empty($maj)) {
				$this->template['action_maj'] = 1;
			}
		}

	}



	/*
	  *	Utilisateurs - membres.
	*/
	function users_membres() {

		$this->template['infos']['title'] = 'membres';
		$this->template['infos']['section'] = 'section=utilisateurs&page=membres';

		// Page à afficher.
		if (isset($_REQUEST['startnum']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['startnum'])) {
			$startnum = $_REQUEST['startnum'];
		} else {
			$startnum = 0;
		}
		$this->template['infos']['startnum'] = $startnum;

		// Trie des membres : ordre.
		if (isset($_POST['sort']) && preg_match('`^login|date_creation|date_derniere_visite$`', $_POST['sort'])
		 && $_POST['sort'] != $this->config['admin_membres_ordre']) {
			$this->update_option('admin_membres_ordre', $_POST['sort']);
		}

		// Trie des membres : sens.
		if (isset($_POST['sens']) && preg_match('`^ASC|DESC$`', $_POST['sens'])
		 && $_POST['sens'] != $this->config['admin_membres_sens']) {
			$this->update_option('admin_membres_sens', $_POST['sens']);
		}

		// Nombre de membres par page.
		if (isset($_POST['nb']) && preg_match('`^[1-9]\d{0,3}$`', $_POST['nb'])
		 && $_POST['nb'] != $this->config['admin_membres_nb']) {
			$this->update_option('admin_membres_nb', $_POST['nb']);
			$startnum = 0;
		}

		$this->template['infos']['recherche'] = '';

		// Groupe.
		$groupe_id = 0;
		if (isset($_GET['groupe']) && preg_match('`^\d{1,9}$`', $_GET['groupe'])) {
			$groupe_id = $_GET['groupe'];
		}
		$this->template['infos']['groupe_id'] = $groupe_id;
		if ($groupe_id) {
			$this->template['infos']['recherche'] = '&groupe=' . $groupe_id;;
		}

		// Recherche.
		$recherche = '';
		if (isset($_REQUEST['search'])) {

			$this->template['infos']['recherche'] .= '&search=' . htmlentities($_REQUEST['search']);
			foreach ($_REQUEST as $k => $v) {
				if (substr($k, 0, 2) == 's_') {
					$this->template['infos']['recherche'] .=  '&' . $k . '=' . $v;
				}
			}

			// Requête.
			$search = trim($_REQUEST['search']);
			if ($search == '') {
				header('Location: index.php?section=utilisateurs&page=membres');
				exit;
			}
			$search = preg_replace('`\s+`', ' ', $search);
			$search = preg_replace('`[^\w\*\?\s]+`', '?', $search);
			$search = str_replace(' ', '[^[:alnum:]]', $search);
			$search = str_replace('*', '[^[:space:]]*', $search);
			$search = str_replace('?', '.', $search);
			$search = '([^[:alnum:]]|^)' . $search . '([^[:alnum:]]|$)';;

			// Champs.
			$champs = array();
			if (isset($_REQUEST['s_nom'])) { $champs[] = 'login'; }
			if (isset($_REQUEST['s_mail'])) { $champs[] = 'mail'; }
			if (isset($_REQUEST['s_web'])) { $champs[] = 'web'; }
			if (isset($_REQUEST['s_ip_creation'])) { $champs[] = 'ip_creation'; }
			if (isset($_REQUEST['s_ip_derniere'])) { $champs[] = 'ip_derniere_visite'; }
			$membres_champs = '';
			for ($i = 0; $i < count($champs); $i++) {
				$membres_champs .= ' OR ' . MYSQL_PREF . 'users.user_' . $champs[$i] . ' REGEXP "' . outils::protege_mysql($search, $this->mysql->lien) . '" ';
			}
			$membres_champs = '(' . substr($membres_champs, 4) . ')';

			// Dates.
			$membres_date = '';
			if (isset($_REQUEST['s_date']) && isset($_REQUEST['s_dnpc']) && isset($_REQUEST['s_dnpd'])
			 && isset($_REQUEST['s_dnsc']) && isset($_REQUEST['s_dnsd']) && isset($_REQUEST['s_date_type'])) {
				$date_type = ($_REQUEST['s_date_type'] == 'creation') ? 'creation' : 'derniere_visite';
				$membres_date = MYSQL_PREF . 'users.user_date_' . $date_type . ' < ' . outils::time_date($_REQUEST['s_dnpc'], $_REQUEST['s_dnpd']) 
					. ' AND ' . MYSQL_PREF . 'users.user_date_' . $date_type . ' > ' . outils::time_date($_REQUEST['s_dnsc'], $_REQUEST['s_dnsd']);
			}
			
			if ($membres_champs || $membres_date) {
				$and = ($membres_champs && $membres_date) ? ' AND ' : '';
				$recherche = ' AND (' . $membres_champs . $and . $membres_date . ')';
			}

		}

		// FROM et WHERE.
		if ($groupe_id) {
			$from_where = 'FROM ' . MYSQL_PREF . 'users LEFT JOIN ' . MYSQL_PREF . 'groupes USING (groupe_id)
						  WHERE ' . MYSQL_PREF . 'groupes.groupe_id != "0" 
						    AND ' . MYSQL_PREF . 'groupes.groupe_id = "' . $groupe_id . '"'
								  . $recherche;
		} else {
			$from_where = 'FROM ' . MYSQL_PREF . 'users
						  WHERE groupe_id != "0"
						    AND groupe_id != "1"' . $recherche;
		}

		// Membres.
		$mysql_requete = 'SELECT * ' . $from_where . '
						ORDER BY ' . MYSQL_PREF . 'users.user_' . $this->config['admin_membres_ordre'] . ' ' . $this->config['admin_membres_sens'] . '
						   LIMIT ' . $startnum . ',' . $this->config['admin_membres_nb'];
		$this->template['membres'] = $this->mysql->select($mysql_requete);

		// Groupes.
		$mysql_requete = 'SELECT groupe_id,groupe_nom
							FROM ' . MYSQL_PREF . 'groupes
						   WHERE groupe_id != "1"
						     AND groupe_id != "2"
						ORDER BY groupe_nom ASC';
		$this->template['groupes'] = $this->mysql->select($mysql_requete, 3);

		// Nombre total de membres.
		$mysql_requete = 'SELECT COUNT(user_id) ' . $from_where;
		$this->template['infos']['nb_objets'] = $this->mysql->select($mysql_requete, 5);

		// On détermine le nombre de pages et la page actuelle.
		$this->template['infos']['nb_pages'] = ceil(($this->template['infos']['nb_objets']) / $this->config['admin_membres_nb']);
		for ($n = 0; $n < $this->template['infos']['nb_pages']; $n++) {
			$num = $n * $this->config['admin_membres_nb'];
			$this->template['nav']['pages'][$n + 1]['page'] = $num;
			if ($num == $startnum) {
				$this->template['infos']['page_actuelle'] = $n + 1;
			}
		}

		// On détermine les pages suivantes, précédentes, de début et de fin.
		$this->template['nav']['suivante'][1] = $startnum + $this->config['admin_membres_nb'];
		$this->template['nav']['precedente'][1] = $startnum - $this->config['admin_membres_nb'];
		$this->template['nav']['premiere'][1] = 0;
		$this->template['nav']['derniere'][1] = ($this->template['infos']['nb_pages'] * $this->config['admin_membres_nb']) - $this->config['admin_membres_nb'];

		// On détermine la position de l'objet actuel.
		if ($startnum == 0) {
			$this->template['nav']['premiere'][0] = 1;
		}
		if ($this->template['nav']['precedente'][1] < 0) {
			$this->template['nav']['precedente'][0] = 1;
		}
		if ($this->template['nav']['suivante'][1] >= ($this->template['infos']['nb_pages'] * $this->config['admin_membres_nb']) || 
		    $this->template['nav']['suivante'][1] >= $this->template['infos']['nb_objets']) {
			$this->template['nav']['suivante'][0] = 1;
		}
		if ($startnum >= $this->template['nav']['derniere'][1]) {
			$this->template['nav']['derniere'][0] = 1;
		}

		$this->template['config'] = $this->config;

	}


	
	/*
	  *	Utilisateurs - groupes.
	*/
	function users_groupes() {

		$this->template['infos']['title'] = 'groupes';

		// Création d'un groupe.
		if (!empty($_POST['new_group'])) {

			// Quelques vérifications...
			$_POST['new_group'] = trim($_POST['new_group']);
			if ($_POST['new_group'] == '') {
				$this->template['infos']['attention']['new_group'] = 'Le nom du groupe est vide.';
			} elseif (strlen($_POST['new_group'] > 80)) {
				$this->template['infos']['attention']['new_group'] = 'Le nom du groupe ne doit pas dépasser 80 caractères.';
			} elseif (!preg_match('`^[@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&]{1,80}$`i', $_POST['new_group'])) {
				$this->template['infos']['attention']['new_group'] = 'Le nom du groupe ne doit pas contenir de caractères spéciaux.';

			// On peut créer notre nouveau groupe !
			} else {
				$nom = outils::protege_mysql($_POST['new_group'], $this->mysql->lien);
				$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'groupes(
											groupe_nom,
											groupe_titre,
											groupe_date_creation,
											groupe_album_pass)
									   VALUES ("' . $nom . '",
											   "' . $nom . '",
											   "' . time() . '",
											   "")';
				if ($this->mysql->requete($mysql_requete)) {
					$this->template['infos']['action']['new_group'] = 'Le groupe « <em>' . $_POST['new_group'] . '</em> » a été créé.';
				} else {
					$this->template['infos']['erreur']['new_group'] = 'Impossible de créer le nouveau groupe.';
				}
			}
		}

		// Suppression de groupe.
		if (!empty($_GET['supprimer']) && preg_match('`^\d{1,9}$`', $_GET['supprimer']) && $_GET['supprimer'] > 3) {
			$this->verifVID();
			$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'groupes
							   WHERE groupe_id = "' . $_GET['supprimer'] . '"';
			if ($this->mysql->requete($mysql_requete)) {
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
									 SET groupe_id = "3"
								   WHERE groupe_id = "' . $_GET['supprimer'] . '"';
				if ($this->mysql->requete($mysql_requete)) {
					$this->template['infos']['action']['delete'] = 'Le groupe a été correctement supprimé.';
				} else {
					$this->template['infos']['erreur']['delete'] = 'Le groupe n\'a pas été correctement supprimé.';
				}
			} else {
				$this->template['infos']['erreur']['delete'] = 'Impossible de supprimer le groupe.';
			}
		}

		// Récupération des groupes.
		$mysql_requete = 'SELECT groupe_id,
								 groupe_nom,
								 groupe_titre,
								 groupe_date_creation
						    FROM ' . MYSQL_PREF . 'groupes
						ORDER BY groupe_id ASC';
		$this->template['groupes'] = $this->mysql->select($mysql_requete);
		for ($i = 0; $i < count($this->template['groupes']); $i++) {
			$mysql_requete = 'SELECT COUNT(user_id)
							    FROM ' . MYSQL_PREF . 'users
							   WHERE groupe_id = "' . $this->template['groupes'][$i]['groupe_id'] . '"';
			$this->template['groupes'][$i]['nb_users'] = $this->mysql->select($mysql_requete, 5);
		}

	}



	/*
	  *	Utilisateurs - modification du profil utilisateur.
	*/
	function users_modif_user() {

		$this->template['infos']['title'] = 'profil membre';

		if (isset($_GET['user']) && preg_match('`^\d{1,9}$`', $_GET['user'])) {

			// Récupération des informations du membre.
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'users.*,
									 ' . MYSQL_PREF . 'groupes.*
								FROM ' . MYSQL_PREF . 'users LEFT JOIN ' . MYSQL_PREF . 'groupes USING(groupe_id)
							   WHERE ' . MYSQL_PREF . 'users.user_id = "' . $_GET['user'] . '"
								 AND ' . MYSQL_PREF . 'users.user_id != "1"';
			$this->template['user'] = $this->mysql->select($mysql_requete);
			if (is_array($this->template['user'])) {

				// Nombre de commentaires.
				$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'commentaires.commentaire_id)
									FROM ' . MYSQL_PREF . 'commentaires
								   WHERE ' . MYSQL_PREF . 'commentaires.user_id = "' . $_GET['user'] . '"';
				$this->template['user'][0]['nb_comments'] = $this->mysql->select($mysql_requete, 5);

				// Nombre d'images envoyées
				$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'images.image_id)
									FROM ' . MYSQL_PREF . 'images
								   WHERE ' . MYSQL_PREF . 'images.user_id = "' . $_GET['user'] . '"';
				$this->template['user'][0]['nb_images'] = $this->mysql->select($mysql_requete, 5);

				// Nombre de favoris.
				$mysql_requete = 'SELECT COUNT(' . MYSQL_PREF . 'favoris.image_id)
									FROM ' . MYSQL_PREF . 'favoris
								   WHERE ' . MYSQL_PREF . 'favoris.user_id = "' . $_GET['user'] . '" ';
				$this->template['user'][0]['nb_favoris'] = $this->mysql->select($mysql_requete, 5);


				$img_avatar = './../membres/avatars/avatar_' . $this->template['user'][0]['user_login'] . '.jpg';
				$img_avatar_thumb = './../membres/avatars/avatar_' . $this->template['user'][0]['user_login'] . '_thumb.jpg';

				// Suppression de l'avatar.
				if (isset($_POST['supp_avatar'])) {
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET user_avatar = "0"
									   WHERE user_id = "' . $_GET['user'] . '"';
					if ($this->mysql->requete($mysql_requete)) {
						if (file_exists($img_avatar)) {
							files::suppFile($img_avatar);
						}
						if (file_exists($img_avatar_thumb)) {
							files::suppFile($img_avatar_thumb);
						}
						$this->template['infos']['action']['avatar'] = 'L\'avatar a été supprimé.';
						$this->template['user'][0]['user_avatar'] = 0;
					} else {
						$this->template['infos']['erreur']['avatar'] = 'Impossible de supprimer l\'avatar.';
					}
				}

				// Changement de l'avatar.
				if (!empty($_FILES['new_avatar'])) {

					// Si GD n'est pas activé, on arrête tout.
					if (!function_exists('imagetypes')) {
						$this->template['infos']['attention']['avatar'] = 'GD n\'est pas présent.';
						return;
					}

					$infos = $_FILES['new_avatar'];

					// Y a-t-il une erreur ?
					if ($infos['error']) {
						switch ($infos['error']) {
							case 4 :
								break;
							case 2 :
							case 1 :
								$this->template['infos']['attention']['avatar'] = 'Le fichier est trop lourd.';
								return;
							default :
								$this->template['infos']['erreur']['avatar'] = 'Impossible de récupérer le fichier.<br />Code erreur : ' . __LINE__;
								return;
						}
					}

					// Le fichier est-il trop lourd ?
					if (filesize($infos['tmp_name']) > 81920) {
						$this->template['infos']['attention']['avatar'] = 'Le fichier est trop lourd.';
						return;
					}

					// Est-ce une image au format JPEG ?
					if (($file_infos = @getimagesize($infos['tmp_name'])) === false) {
						$this->template['infos']['attention']['avatar'] = 'Le fichier n\'est pas au format JPEG.';
						return;
					}
					if (!preg_match('`\.jpg$`i', $infos['name'])
					 || !preg_match('`^image/(p?jpeg)$`i', trim($file_infos['mime']))) {
						$this->template['infos']['attention']['avatar'] = 'Le fichier n\'est pas au format JPEG.';
						return;
					}

					// Dimensions minimales.
					if ($file_infos[0] < 50 || $file_infos[1] < 50) {
						$this->template['infos']['attention']['avatar'] = 'L\'image doit faire au moins 50 pixels de coté.';
						return;
					}

					// Dimensions maximales.
					if ($file_infos[0] > 1000 || $file_infos[1] > 1000) {
						$this->template['infos']['attention']['avatar'] = 'L\'image est trop grande.';
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
								$this->template['infos']['attention']['avatar'] = 'Impossible de créer l\'avatar<br />Code erreur : ' . __LINE__;
								return;
							}
						} else {
							$this->template['infos']['attention']['avatar'] = 'Type de fichier non pris en charge (JPEG).';
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
							$this->template['infos']['erreur']['avatar'] = 'Impossible de redimensionner l\'image.<br />Code erreur : ' . __LINE__;
							return;
						}
						imagedestroy($image_p);
						files::chmodFile($img_avatar);
						if (!file_exists($img_avatar)) {
							if (file_exists($img_avatar)) { files::suppFile($img_avatar); }
							$this->template['infos']['erreur']['avatar'] = 'Impossible de redimensionner l\'image.<br />Code erreur : ' . __LINE__;
							return;
						}

					// Sinon on copie directement l'image dans le répertoire das avatars.
					} else {
						if (file_exists($img_avatar)) {
							files::suppFile($img_avatar);
						}
						if (!files::copie($file_temp, $img_avatar)) {
							if (file_exists($img_avatar)) { files::suppFile($img_avatar); }
							$this->template['infos']['erreur']['avatar'] = 'Impossible de récupérér l\'image.<br />Code erreur : ' . __LINE__;
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
						$this->template['infos']['erreur']['avatar'] = 'Impossible de créer la vignette de l\'avatar.';
						return;
					}
					imagedestroy($image_p);
					files::chmodFile($img_avatar_thumb);
					if (!file_exists($img_avatar_thumb)) {
						if (file_exists($img_avatar)) { files::suppFile($img_avatar); }
						$this->template['infos']['erreur']['avatar'] = 'Impossible de créer la vignette de l\'avatar.';
						return;
					}

					// On indique dans la base de données que l'on doit utiliser l'avatar.
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET user_avatar = "1"
									   WHERE user_id = "' . $_GET['user'] . '"';
					if ($this->mysql->requete($mysql_requete)) {
						$this->template['infos']['action']['avatar'] = 'L\'avatar a été changé.';
						$this->template['user'][0]['user_avatar'] = 1;
					} else {
						$this->template['infos']['erreur']['avatar'] = 'Impossible de créer l\'avatar.';
						return;
					}

				}

				// Modification du profil.
				if (isset($_POST['modif_profil'])) {

					$update = '';

					// Modification du courriel.
					if (isset($_POST['new_mail'])
					 && $_POST['new_mail'] != $this->template['user'][0]['user_mail']) {
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
								$this->template['infos']['attention']['mail'] = 'Cette adresse courriel existe déjà.';
							} else {
								$update .= ',user_mail = "' . $_POST['new_mail'] . '"';
								$this->template['user'][0]['user_mail'] = $_POST['new_mail'];
							}
						} else {
							$this->template['infos']['attention']['mail'] = 'L\'adresse du courriel est incorrecte.';
						}
					}

					// Modification du site Web.
					if (isset($_POST['new_web'])
					  && $_POST['new_web'] != $this->template['user'][0]['user_web']) {
						if ($_POST['new_web'] == '' || preg_match('`^' . outils::http_url() . '$`i', $_POST['new_web'])) {
							$update .= ',user_web = "' . $_POST['new_web'] . '"';
							$this->template['user'][0]['user_web'] = $_POST['new_web'];
						} else {
							$this->template['infos']['attention']['web'] = 'L\'adresse du site Web est incorrecte.';
						}
					}

					// Modification de la localisation.
					if (isset($_POST['new_lieu'])
					  && $_POST['new_lieu'] != $this->template['user'][0]['user_lieu']) {
						if (strlen($_POST['new_lieu']) < 61) {
							if ($_POST['new_lieu'] == '' || preg_match('`^[@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&,;/%.:_#\(\)«»<>!?-]{1,60}$`i', $_POST['new_lieu'])) {
								$update .= ',user_lieu = "' . $_POST['new_lieu'] . '"';
								$this->template['user'][0]['user_lieu'] = $_POST['new_lieu'];
							} else {
								$this->template['infos']['attention']['lieu'] = 'Le texte de la localisation ne doit pas contenir de caractères exotiques.';
							}
						} else {
							$this->template['infos']['attention']['lieu'] = 'Le texte de la localisation ne doit pas contenir plus de 60 caractères.';
						}
					}

					// Courriel public ?
					if (!empty($_POST['new_mail_public']) && empty($this->template['user'][0]['user_mail_public'])) {
						$update .= ',user_mail_public = "1"';
						$this->template['user'][0]['user_mail_public'] = 1;
					} elseif (empty($_POST['new_mail_public']) && !empty($this->template['user'][0]['user_mail_public'])) {
						$update .= ',user_mail_public = "0"';
						$this->template['user'][0]['user_mail_public'] = 0;
					}

					// Newsletter.
					if ($this->template['user'][0]['groupe_newsletter']) {
						if (!empty($_POST['new_newsletter']) && empty($this->template['user'][0]['user_newsletter'])) {
							$update .= ',user_newsletter = "1"';
							$this->template['user'][0]['user_newsletter'] = 1;
						} elseif (empty($_POST['new_newsletter']) && !empty($this->template['user'][0]['user_newsletter'])) {
							$update .= ',user_newsletter = "0"';
							$this->template['user'][0]['user_newsletter'] = 0;
						}
					}

					if ($update) {
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
											 SET ' . substr($update, 1) . '
										   WHERE user_id = "' . $_GET['user'] . '"';
						if ($this->mysql->requete($mysql_requete)) {
							$this->template['infos']['action']['update'] = 'Les informations du profil ont été changées.';
						} else {
							$this->template['infos']['erreur']['update'] = 'Impossible de modifier les informations du profil.';
						}
					}

				}

			} else {
				header('Location: index.php?section=utilisateurs&page=membres');
				exit;
			}

		} else {
			header('Location: index.php?section=utilisateurs&page=membres');
			exit;
		}

	}



	/*
	  *	Utilisateurs - modification de groupe.
	*/
	function users_modif_groupe() {

		$this->template['infos']['title'] = 'groupes';

		// Récupération des informations du groupe.
		if (empty($_REQUEST['groupe']) || !preg_match('`^\d{1,9}$`', $_REQUEST['groupe'])) {
			$_REQUEST['groupe'] = 1;
		}
		$mysql_requete = 'SELECT *
						    FROM ' . MYSQL_PREF . 'groupes
						   WHERE groupe_id = "' . $_REQUEST['groupe'] . '"';
		$this->template['groupe'] = $this->mysql->select($mysql_requete);

		// Groupe autre que "invité".
		if ($_REQUEST['groupe'] != 2) {
			$this->template['display']['groupe_membres'] = true;
		}

		// Groupe autre que "admin".
		if ($_REQUEST['groupe'] > 1) {
			$this->template['display']['groupe_noadmin'] = true;
		}

		// Récupération des mots de passe des albums.
		$mysql_requete = 'SELECT DISTINCT categorie_pass
							FROM ' . MYSQL_PREF . 'categories
						   WHERE categorie_pass IS NOT NULL
						ORDER BY categorie_pass';
		$this->template['passwords'] = $this->mysql->select($mysql_requete);

		// Modifications des paramètres du groupe.
		if (!empty($_POST)) {
			$mysql_update = '';

			// Textes.
			$mysql_update .= $this->users_modif_groupe_texte('groupe_nom', 'nom', 'nom', 80);
			$mysql_update .= $this->users_modif_groupe_texte('groupe_titre', 'titre', 'titre', 80);

			if (!empty($this->template['display']['groupe_noadmin'])) {

				// Checkboxes.
				$mysql_update .= $this->users_modif_groupe_checkbox('groupe_aut_comments', 'commentaires');
				$mysql_update .= $this->users_modif_groupe_checkbox('groupe_aut_votes', 'votes');
				$mysql_update .= $this->users_modif_groupe_checkbox('groupe_aut_perso', 'perso');
				$mysql_update .= $this->users_modif_groupe_checkbox('groupe_aut_search', 'recherche_avance');
				if (isset($this->template['display']['groupe_membres'])) {
					$mysql_update .= $this->users_modif_groupe_checkbox('groupe_aut_newsletter', 'newsletter');
					$mysql_update .= $this->users_modif_groupe_checkbox('groupe_aut_upload', 'upload');
					$mysql_update .= $this->users_modif_groupe_checkbox('groupe_aut_create', 'upload_create');
				}

				// Radios.
				if (isset($this->template['display']['groupe_membres'])) {
					$mysql_update .= $this->users_modif_groupe_radio('groupe_aut_upload_mode', 'upload_mode', 'direct|attente');
				}
				$mysql_update .= $this->users_modif_groupe_radio('groupe_protect', 'album_pass_mode', 'aucun|tous|select');

				// Listes de mots de passe.
				if (!empty($_POST['groupe_protect_mdp_dever'])
				 && isset($_POST['groupe_protect_mdp_ver'])
				 && is_array($_POST['groupe_protect_mdp_ver'])) {
					if (serialize($_POST['groupe_protect_mdp_ver']) != $this->template['groupe'][0]['groupe_album_pass']) {
						$album_pass = unserialize($this->template['groupe'][0]['groupe_album_pass']);
						$album_pass = (is_array($album_pass)) ? $album_pass : array();
						$album_pass = array_merge($album_pass, $_POST['groupe_protect_mdp_ver']);
						$this->template['groupe'][0]['groupe_album_pass'] = serialize($album_pass);
						$mysql_update .= ',groupe_album_pass = "' . outils::protege_mysql(serialize($album_pass), $this->mysql->lien) . '"';
					}
				}
				if (!empty($_POST['groupe_protect_mdp_rever'])
				 && isset($_POST['groupe_protect_mdp_dev'])
				 && is_array($_POST['groupe_protect_mdp_dev'])) {
					$album_pass = unserialize($this->template['groupe'][0]['groupe_album_pass']);
					$album_pass = array_flip($album_pass);
					for ($i = 0; $i < count($_POST['groupe_protect_mdp_dev']); $i++) {
						unset($album_pass[$_POST['groupe_protect_mdp_dev'][$i]]);
					}
					$album_pass = array_flip($album_pass);
					sort($album_pass);
					if ($this->template['groupe'][0]['groupe_album_pass'] != serialize($album_pass)) {
						$this->template['groupe'][0]['groupe_album_pass'] = serialize($album_pass);
						$mysql_update .= ',groupe_album_pass = "' . outils::protege_mysql(serialize($album_pass), $this->mysql->lien) . '"';
					}
				}
			}

			// On met à jour les paramètres du groupe.
			if ($mysql_update != '') {
				$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'groupes
								    SET ' . substr($mysql_update, 1)  . '
								  WHERE groupe_id = "' . $_REQUEST['groupe'] . '"';
				if ($this->mysql->requete($mysql_requete)) {
					$this->template['infos']['action']['update'] = 'Paramètres du groupe mis à jour.';
				} else {
					$this->template['infos']['erreur']['update'] = 'Impossible de mettre à jour les paramètres du groupe.';
				}
			}
		}
	}

	function users_modif_groupe_texte($post, $nom, $bdd, $limit = 80) {
		if (!empty($_POST[$post])) {
			$_POST[$post] = trim($_POST[$post]);
			if ($_POST[$post] != $this->template['groupe'][0]['groupe_' . $bdd]) {
				if ($_POST[$post] == '') {
					$this->template['infos']['attention'][$nom] = 'Le ' . $nom . ' du groupe est vide.';
				} elseif (strlen($_POST[$post] > $limit)) {
					$this->template['infos']['attention'][$nom] = 'Le ' . $nom . ' du groupe ne doit pas dépasser ' . $limit . ' caractères.';
				} elseif (!preg_match('`^[@éèëêàäâáåãïîìíöôòóõùûüúÿýçña-z\s\d&]{1,80}$`i', $_POST[$post])) {
					$this->template['infos']['attention'][$nom] = 'Le ' . $nom . ' du groupe ne doit pas contenir de caractères spéciaux.';
				} else {
					$this->template['groupe'][0]['groupe_' . $bdd] = $_POST[$post];
					return ',groupe_' . $bdd . ' = "' . outils::protege_mysql($_POST[$post], $this->mysql->lien) . '"';
				}
			}
		}
		return '';
	}

	function users_modif_groupe_checkbox($post, $bdd) {
		if (isset($_POST[$post]) && empty($this->template['groupe'][0]['groupe_' . $bdd])) {
			$this->template['groupe'][0]['groupe_' . $bdd] = 1;
			return ',groupe_' . $bdd . ' = "1"';
		}
		if (empty($_POST[$post]) && $this->template['groupe'][0]['groupe_' . $bdd]) {
			$this->template['groupe'][0]['groupe_' . $bdd] = 0;
			return ',groupe_' . $bdd . ' = "0"';
		}
		return '';
	}

	function users_modif_groupe_radio($post, $bdd, $termes) {
		if (isset($_POST[$post])
		 && preg_match('`^' . $termes . '$`', $_POST[$post])
		 && $_POST[$post] != $this->template['groupe'][0]['groupe_' . $bdd]) {
			$this->template['groupe'][0]['groupe_' . $bdd] = $_POST[$post];
			return ',groupe_' . $bdd . ' = "' . $_POST[$post] . '"';
		}
		return '';
	}


	
	/*
	  *	Utilisateurs - images.
	*/
	function users_images_action() {

		if (empty($_POST['users_images_action'])
		 || empty($_POST['images'])
		 || !is_array($_POST['images'])) {
			return;
		}

		// Suppression.
		if ($_POST['users_images_action'] == 'supprimer') {

			$delete_file = true;
			$delete_bdd = true;

			for ($i = 0; $i < count($_POST['images']); $i++) {
				if (!preg_match('`^\d{1,9}$`', $_POST['images'][$i])) {
					continue;
				}
				$mysql_requete = 'SELECT img_att_fichier
									FROM ' . MYSQL_PREF . 'images_attente
								   WHERE img_att_id = "' . $_POST['images'][$i] . '"';
				$image = $this->mysql->select($mysql_requete);
				if (!is_array($image)) {
					continue;
				}

				// Suppression de l'image et de la vignette.
				$dir = './../membres/images/';
				$image_file = $dir . $image[0]['img_att_fichier'];
				$image_thumb = $dir . 'vignettes/thumb_' . basename($image[0]['img_att_fichier']);
				if (file_exists($image_file)) {
					if (!files::suppFile($image_file)) {
						$delete_file = false;
					}
				}
				if (file_exists($image_thumb)) {
					if (!files::suppFile($image_thumb)) {
						$delete_file = false;
					}
				}

				// Suppression de la base de données.
				$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'images_attente
								   WHERE img_att_id = "' . $_POST['images'][$i] . '"';
				if (!$this->mysql->requete($mysql_requete)) {
					$delete_bdd = false;
				}

				// Messages.
				if (!$delete_file) {
					$this->template['infos']['erreur']['delete_file_' . $i] = 'Image « ' . $image[0]['img_att_fichier'] . ' » : impossible de supprimer le fichier du répertoire d\'upload.';
				}
				if (!$delete_bdd) {
					$this->template['infos']['erreur']['delete_bdd_' . $i] = 'Image « ' . $image[0]['img_att_fichier'] . ' » : impossible de supprimer la référence du fichier de la base de données.';
				}
			}

			// Messages.
			if ($delete_file && $delete_bdd) {
				$this->template['infos']['action']['delete'] = 'Les images sélectionnées ont été supprimées.';
			}
			
		}

		// Validation.
		if ($_POST['users_images_action'] == 'valider') {

			// Informations utilisateur.
			$ids = '';
			for ($i = 0; $i < count($_POST['images']); $i++) {
				if (!preg_match('`^\d{1,9}$`', $_POST['images'][$i])) {
					continue;
				}
				$ids .= ' OR ' . MYSQL_PREF . 'images_attente.img_att_id = "' . $_POST['images'][$i] . '"';
			}
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images_attente.user_id,
									 ' . MYSQL_PREF . 'images_attente.img_att_id,
									 ' . MYSQL_PREF . 'images_attente.img_att_nom,
									 ' . MYSQL_PREF . 'images_attente.img_att_description,
									 ' . MYSQL_PREF . 'images_attente.img_att_fichier,
									 ' . MYSQL_PREF . 'categories.categorie_chemin
								FROM ' . MYSQL_PREF . 'images_attente LEFT JOIN ' . MYSQL_PREF . 'categories USING (categorie_id)
							   WHERE ' . substr($ids, 4);
			$infos = $this->mysql->select($mysql_requete);
			if (!is_array($infos)) {
				return;
			}
			$images_user = array();
			$upload_dir = './../membres/images/';
			$albums_dir = './../' . GALERIE_ALBUMS . '/';
			for ($i = 0; $i < count($infos); $i++) {
				$path = $infos[$i]['categorie_chemin'] . $infos[$i]['img_att_fichier'];

				// On déplace l'image.
				if (file_exists($albums_dir . $path)) {
					$this->template['infos']['attention']['upload_' . $path] = '« ' . $infos[$i]['img_att_fichier'] . ' » : une image de même nom existe déjà dans le répertoire cible.';
					continue;
				}
				if (files::deplace($upload_dir . $infos[$i]['img_att_fichier'], $albums_dir . $path)) {

					$images_user[$path]['user_id'] = $infos[$i]['user_id'];
					$images_user[$path]['image_nom'] = $infos[$i]['img_att_nom'];
					$images_user[$path]['image_desc'] = $infos[$i]['img_att_description'];

					// On supprime l'image de la base de données.
					$mysql_requete = 'DELETE FROM ' . MYSQL_PREF . 'images_attente
									   WHERE img_att_id = "' . $infos[$i]['img_att_id'] . '"';
					$this->mysql->requete($mysql_requete);
				}

				// On supprime la vignette.
				$image_thumb = $upload_dir . 'vignettes/thumb_' . $infos[$i]['img_att_fichier'];
				if (file_exists($image_thumb)) {
					files::suppFile($image_thumb);
				}
			}

			// On ajoute les images à la base de données.
			require_once(dirname(__FILE__) . '/../includes/classes/class.upload.php');
			$upload = new upload($this->mysql, $this->config);
			$upload->users = $images_user;
			$upload->recup_albums();

			// Rapport.
			if ($upload->rapport['erreurs']) {
				foreach ($upload->rapport['erreurs'] as $v) {
					$this->template['infos']['erreur']['upload_' . $v[0]] = 'Une erreur s\'est produite avec l\'objet « <em>' . $v[0] . '</em> » : ' . $v[1];
				}
			}
			for ($n = 0; $n < count($upload->rapport['img_rejets']); $n++) {
				$this->template['infos']['erreur']['upload_' . $i] = 'L\'image « <em>' . $upload->rapport['img_rejets'][$n][0] . '</em> » a été rejetée pour la raison suivante : ' . $upload->rapport['img_rejets'][$n][2];
			}
			if (empty($upload->rapport['img_rejets']) && empty($upload->rapport['erreur'])) {
				$this->template['infos']['action']['upload_' . $i] = 'Les images sélectionnées ont été ajoutées à la galerie.';
			}

		}
	}


	
	/*
	  *	Utilisateurs - images.
	*/
	function users_images() {

		$this->template['infos']['title'] = 'images en attente';
		$this->template['infos']['section'] = 'section=utilisateurs&page=images';

		// Page à afficher.
		if (isset($_REQUEST['startnum']) && preg_match('`^[1-9]\d{0,9}$`', $_REQUEST['startnum'])) {
			$startnum = $_REQUEST['startnum'];
		} else {
			$startnum = 0;
		}

		// Trie des membres : ordre.
		if (isset($_POST['sort']) && preg_match('`^date_envoi|album|membre$`', $_POST['sort'])
		 && $_POST['sort'] != $this->config['admin_imgatt_ordre']) {
			$this->update_option('admin_imgatt_ordre', $_POST['sort']);
		}
		switch ($this->config['admin_imgatt_ordre']) {
			case 'album' :
				$sort = MYSQL_PREF . 'categories.categorie_nom';
				break;
			case 'membre' :
				$sort = MYSQL_PREF . 'users.user_login';
				break;
			default :
				$sort = MYSQL_PREF . 'images_attente.img_att_date';
		}

		// Trie des membres : sens.
		if (isset($_POST['sens']) && preg_match('`^ASC|DESC$`', $_POST['sens'])
		 && $_POST['sens'] != $this->config['admin_imgatt_sens']) {
			$this->update_option('admin_imgatt_sens', $_POST['sens']);
		}

		// Nombre de membres par page.
		if (isset($_POST['nb']) && preg_match('`^[1-9]\d{0,3}$`', $_POST['nb'])
		 && $_POST['nb'] != $this->config['admin_imgatt_nb']) {
			$this->update_option('admin_imgatt_nb', $_POST['nb']);
			$startnum = 0;
		}
		$nb_images = $this->config['admin_imgatt_nb'];

		// On récupère les images en attente.
		$from_where = ' FROM ' . MYSQL_PREF . 'images_attente 
				   LEFT JOIN ' . MYSQL_PREF . 'users 
					      ON ' . MYSQL_PREF . 'images_attente.user_id = ' . MYSQL_PREF . 'users.user_id
  				   LEFT JOIN ' . MYSQL_PREF . 'categories
					      ON ' . MYSQL_PREF . 'images_attente.categorie_id = ' . MYSQL_PREF . 'categories.categorie_id';
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images_attente.*,
								 ' . MYSQL_PREF . 'users.user_login,
								 ' . MYSQL_PREF . 'categories.categorie_id,
								 ' . MYSQL_PREF . 'categories.categorie_nom'
								   . $from_where . '
						ORDER BY ' . $sort . ' ' . $this->config['admin_imgatt_sens'] . '
						   LIMIT ' . $startnum . ',' . $nb_images;
		$this->template['images_attente'] = $this->mysql->select($mysql_requete);

		// Nombre total d'images.
		$mysql_requete = 'SELECT COUNT(img_att_id) ' . $from_where;
		$this->template['infos']['nb_objets'] = $this->mysql->select($mysql_requete, 5);

		// On détermine le nombre de pages et la page actuelle.
		$this->template['infos']['nb_pages'] = ceil(($this->template['infos']['nb_objets']) / $nb_images);
		for ($n = 0; $n < $this->template['infos']['nb_pages']; $n++) {
			$num = $n * $nb_images;
			$this->template['nav']['pages'][$n + 1]['page'] = $num;
			if ($num == $startnum) {
				$this->template['infos']['page_actuelle'] = $n + 1;
			}
		}

		// On détermine les pages suivantes, précédentes, de début et de fin.
		$this->template['nav']['suivante'][1] = $startnum + $nb_images;
		$this->template['nav']['precedente'][1] = $startnum - $nb_images;
		$this->template['nav']['premiere'][1] = 0;
		$this->template['nav']['derniere'][1] = ($this->template['infos']['nb_pages'] * $nb_images) - $nb_images;

		// On détermine la position de l'objet actuel.
		if ($startnum == 0) {
			$this->template['nav']['premiere'][0] = 1;
		}
		if ($this->template['nav']['precedente'][1] < 0) {
			$this->template['nav']['precedente'][0] = 1;
		}
		if ($this->template['nav']['suivante'][1] >= ($this->template['infos']['nb_pages'] * $nb_images) || 
		    $this->template['nav']['suivante'][1] >= $this->template['infos']['nb_objets']) {
			$this->template['nav']['suivante'][0] = 1;
		}
		if ($startnum >= $this->template['nav']['derniere'][1]) {
			$this->template['nav']['derniere'][0] = 1;
		}

		$this->template['config'] = $this->config;
	}
	
	

	/*
	 *	Mise à jour du fichier de configuration.
	*/
	function recup_conf() {
		$conf = array();
		$conf['admin_user'] = ADMIN_USER;
		$conf['admin_pass'] = ADMIN_PASS;
		$conf['mysql_serv'] = MYSQL_SERV;
		$conf['mysql_user'] = MYSQL_USER;
		$conf['mysql_pass'] = MYSQL_PASS;
		$conf['mysql_base'] = MYSQL_BASE;
		$conf['mysql_pref'] = MYSQL_PREF;
		$conf['thumb_tdir'] = THUMB_TDIR;
		$conf['thumb_pref'] = THUMB_PREF;
		$conf['thumb_alb_mode'] = THUMB_ALB_MODE;
		$conf['thumb_alb_size'] = THUMB_ALB_SIZE;
		$conf['thumb_alb_crop_width'] = THUMB_ALB_CROP_WIDTH;
		$conf['thumb_alb_crop_height'] = THUMB_ALB_CROP_HEIGHT;
		$conf['thumb_img_mode'] = THUMB_IMG_MODE;
		$conf['thumb_img_size'] = THUMB_IMG_SIZE;
		$conf['thumb_img_crop_width'] = THUMB_IMG_CROP_WIDTH;
		$conf['thumb_img_crop_height'] = THUMB_IMG_CROP_HEIGHT;
		$conf['thumb_quality'] = THUMB_QUALITY;
		$conf['img_resize_gd'] = IMG_RESIZE_GD;
		$conf['img_texte'] = IMG_TEXTE;
		$conf['img_texte_params'] = IMG_TEXTE_PARAMS;
		$conf['galerie_url'] = GALERIE_URL;
		$conf['galerie_path'] = GALERIE_PATH;
		$conf['galerie_theme'] = GALERIE_THEME;
		$conf['galerie_style'] = GALERIE_STYLE;
		$conf['galerie_url_type'] = GALERIE_URL_TYPE;		
		$conf['galerie_albums'] = GALERIE_ALBUMS;
		$conf['galerie_install'] = GALERIE_INSTALL;
		$conf['galerie_version'] = GALERIE_VERSION;
		$conf['galerie_integrated'] = GALERIE_INTEGRATED;

		return $conf;
	}
	function update_conf($conf, $msg = 1) {
		if ($conf == $this->recup_conf()) {
			return TRUE;
		}
		$file = '../config/conf.php';
		if (!file_exists($file)) {
			$this->template['infos']['erreur']['change_config'] = 'Fichier de config inexistant.';
			return FALSE;
		}
		files::chmodFile($file);
		if ($id = @fopen($file, 'w')) {
			$config = "<?php\n\n";
			$config .= "define('ADMIN_USER', '" . $conf['admin_user'] . "');\n";
			$config .= "define('ADMIN_PASS', '" . $conf['admin_pass'] . "');\n";
			$config .= "define('MYSQL_SERV', '" . $conf['mysql_serv'] . "');\n";
			$config .= "define('MYSQL_USER', '" . $conf['mysql_user'] . "');\n";
			$config .= "define('MYSQL_PASS', '" . $conf['mysql_pass'] . "');\n";
			$config .= "define('MYSQL_BASE', '" . $conf['mysql_base'] . "');\n";
			$config .= "define('MYSQL_PREF', '" . $conf['mysql_pref'] . "');\n";
			$config .= "define('THUMB_TDIR', '" . $conf['thumb_tdir'] . "');\n";
			$config .= "define('THUMB_PREF', '" . $conf['thumb_pref'] . "');\n";
			$config .= "define('THUMB_ALB_MODE', '" . $conf['thumb_alb_mode'] . "');\n";
			$config .= "define('THUMB_ALB_SIZE', " . $conf['thumb_alb_size'] . ");\n";
			$config .= "define('THUMB_ALB_CROP_WIDTH', " . $conf['thumb_alb_crop_width'] . ");\n";
			$config .= "define('THUMB_ALB_CROP_HEIGHT', " . $conf['thumb_alb_crop_height'] . ");\n";
			$config .= "define('THUMB_IMG_MODE', '" . $conf['thumb_img_mode'] . "');\n";
			$config .= "define('THUMB_IMG_SIZE', " . $conf['thumb_img_size'] . ");\n";
			$config .= "define('THUMB_IMG_CROP_WIDTH', " . $conf['thumb_img_crop_width'] . ");\n";
			$config .= "define('THUMB_IMG_CROP_HEIGHT', " . $conf['thumb_img_crop_height'] . ");\n";
			$config .= "define('THUMB_QUALITY', " . $conf['thumb_quality'] . ");\n";
			$config .= "define('IMG_RESIZE_GD', '" . $conf['img_resize_gd'] . "');\n";
			$config .= "define('IMG_TEXTE', '" . $conf['img_texte'] . "');\n";
			$config .= "define('IMG_TEXTE_PARAMS', '" . str_replace("'", "\'", $conf['img_texte_params']) . "');\n";
			$config .= "define('GALERIE_URL', '" . $conf['galerie_url'] . "');\n";
			$config .= "define('GALERIE_PATH', '" . $conf['galerie_path'] . "');\n";
			$config .= "define('GALERIE_THEME', '" . $conf['galerie_theme'] . "');\n";
			$config .= "define('GALERIE_STYLE', '" . $conf['galerie_style'] . "');\n";
			$config .= "define('GALERIE_URL_TYPE', '" . $conf['galerie_url_type'] . "');\n";
			$config .= "define('GALERIE_ALBUMS', '" . $conf['galerie_albums'] . "');\n";
			$config .= "define('GALERIE_INSTALL', " . $conf['galerie_install'] . ");\n";
			$config .= "define('GALERIE_VERSION', '" . $conf['galerie_version'] . "');\n";
			$config .= "define('GALERIE_INTEGRATED', " . $conf['galerie_integrated'] . ");\n";
			$config .= "\n?>";
			if (!@fwrite($id, $config)) {
				return FALSE;
			}
			@fclose($id);
			if ($msg) {
				$this->template['infos']['action']['change_config'] = 'Fichier de configuration mis à jour.';
			}
		} else {
			$this->template['infos']['erreur']['change_config'] = 'Impossible de modifier le fichier de config.';
			return FALSE;
		}

		return TRUE;
	}



	/*
	 *	Configuration.
	*/
	function config_conf() {
		$this->template['infos']['title'] = 'configuration de la galerie';

		$this->template['config']['galerie_albums_dir'] = GALERIE_ALBUMS;
		$this->template['config']['galerie_repertoire_vignettes'] = THUMB_TDIR;
		$this->template['config']['galerie_prefixe_vignettes'] = THUMB_PREF;
		
		if (isset($_POST['u'])) {

			// On vérifie que le mot de passe actuel a bien été entré.
			if (empty($_POST['g_pass']) || $_POST['g_pass'] !== ADMIN_PASS) {
				$this->template['infos']['attention']['config_admin_change_pass'] = 'Le mot de passe actuel que vous avez entré est incorrect.';
				return;
			}

			$change = 0;
			$conf = $this->recup_conf();

			// Identifiant.
			if (!empty($_POST['g_login'])) {
				$_POST['g_login'] = trim($_POST['g_login']);
				if (!preg_match('`^[a-z\d_]{3,30}$`i', $_POST['g_login'])) {
					$this->template['infos']['attention']['config_admin_change_login'] = 'L\'identifiant doit comporter entre 3 et 30 caractères alphanumériques (non accentués) ou souligné (_).';
				} else {
					$mysql_requete = 'SELECT user_id
										FROM ' . MYSQL_PREF . 'users
									   WHERE user_login = "' . $_POST['g_login'] . '"';
					if (is_array($this->mysql->select($mysql_requete))) {
						$this->template['infos']['attention']['config_admin_change_login'] = 'L\'identifiant choisi est déjà pris.';
					} else {
						$conf['admin_user'] = $_POST['g_login'];
						$change = 1;
						$this->template['infos']['action']['config_admin_change'] = 'Les paramètres admin ont été changés.';
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
											 SET user_login = "' . $_POST['g_login'] . '"
										   WHERE user_id = 1';
						$this->mysql->requete($mysql_requete);
					}
				}
			}

			// Mot de passe.
			if (!empty($_POST['g_pass']) && !empty($_POST['g_new_pass']) && !empty($_POST['g_new_pass_confirm'])) {
				if (!preg_match('`^[a-z\d_]{6,50}$`i', $_POST['g_new_pass'])) {
					$this->template['infos']['attention']['config_admin_change_pass'] = 'Le nouveau mot de passe doit comporter entre 6 et 50 caractères alphanumériques (non accentués) ou souligné (_).';
				} elseif ($_POST['g_new_pass'] != $_POST['g_new_pass_confirm']) {
					$this->template['infos']['attention']['config_admin_change_pass'] = 'La confirmation du nouveau mot de passe ne correspond pas.';
				} else {
					$conf['admin_pass'] = $_POST['g_new_pass'];
					$change = 1;
					$this->template['infos']['action']['config_admin_change'] = 'Les paramètres admin ont été changés.';
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET user_pass = "' . md5($_POST['g_new_pass']) . '"
									   WHERE user_id = 1';
					$this->mysql->requete($mysql_requete);
				}
			}

			// Courriel.
			if (isset($_POST['g_mail'])) {
				$_POST['g_mail'] = trim($_POST['g_mail']);
				if ($_POST['g_mail'] != $this->config['admin_mail']) {
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'users
										 SET user_mail = "' . $_POST['g_mail'] . '"
									   WHERE user_id = 1';
					$this->mysql->requete($mysql_requete);
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'config
										 SET valeur = "' . outils::protege_mysql($_POST['g_mail'], $this->mysql->lien) . '"
									   WHERE parametre = "admin_mail"';
					$this->mysql->requete($mysql_requete);
					$this->template['config']['admin_mail'] = $_POST['g_mail'];
					$this->template['infos']['action']['config_admin_change'] = 'Les paramètres admin ont été changés.';
				}
			}
		

			// Répertoire des albums.
			if (!empty($_POST['g_dir_alb']) && GALERIE_ALBUMS != $_POST['g_dir_alb']) {
				if (is_dir('../' . $_POST['g_dir_alb'] . '/')) {
					$conf['galerie_albums'] = $_POST['g_dir_alb'];
					$this->template['config']['galerie_albums_dir'] = $_POST['g_dir_alb'];
					$change = 1;
				} else {
					$this->template['infos']['attention']['config_albums_dir'] = 'Le répertoire d\'albums que vous avez spécifié n\'existe pas.';
				}
			}

			$del_thumbs = 0;
			$remakedir = 0;
			$thumb_dir = THUMB_TDIR;

			// Répertoire des vignettes.
			if (!empty($_POST['g_dir_tb']) && THUMB_TDIR != $_POST['g_dir_tb']
			 && preg_match('`^[a-z0-9_-]{1,99}$`i', $_POST['g_dir_tb'])) {
				$del_thumbs = 1;
				$remakedir = 1;
				$change = 1;
				$conf['thumb_tdir'] = $_POST['g_dir_tb'];
				$this->template['config']['galerie_repertoire_vignettes'] = $_POST['g_dir_tb'];
			}

			// Préfixe des vignettes.
			if (isset($_POST['g_pref_tb']) && THUMB_PREF != $_POST['g_pref_tb']
			 && preg_match('`^[a-z0-9_-]{0,99}$`i', $_POST['g_pref_tb'])) {
				$del_thumbs = 1;
				$change = 1;
				$conf['thumb_pref'] = $_POST['g_pref_tb'];
				$this->template['config']['galerie_prefixe_vignettes'] = $_POST['g_pref_tb'];
			}

			if ($del_thumbs) {
				if (!$this->del_thumbs($thumb_dir, $remakedir)) {
					$this->template['infos']['erreur']['sup_vignettes'] = 'Toutes les vignettes n\'ont pas pu être supprimées.';
				}
			}
			
			// Mise à jour du fichier de configuration.
			if ($change) {
				$this->update_conf($conf);
			}
		}
	}



	/*
	 *	Suppression des vignettes.
	*/
	function del_thumbs($thumb_dir, $remakedir = 0, $what = 0) {
		$ok = TRUE;
		if ($what === 0 || $what === 1) {
			if (!$this->del_thumbs_img($thumb_dir, $remakedir)) {
				$ok = FALSE;
			}
		}
		if ($what === 0 || $what === 2) {
			if (!$this->delete_dir(dirname(dirname(__FILE__)) . '/cache/cat_thumb/', 0)) {
				$ok = FALSE;
			}
		}
		return $ok;
	}
	function del_thumbs_img($thumb_dir, $remakedir = 0, $rep = '') {
		static $ok = TRUE;
		$gal_dir = substr($this->galerie_dir, 0, strlen($this->galerie_dir)-1);
		if (is_dir($gal_dir . $rep)) {
			if ($dir = @opendir($gal_dir . $rep)) {
				while ($ent = readdir($dir)) {
					$file = $gal_dir . $rep . '/' . $ent;
					if (is_dir($file) && $ent != '.' && $ent != '..') {
						$this->del_thumbs_img($thumb_dir, $remakedir, $rep . '/' . $ent);
					} elseif (is_file($file) && preg_match('`/' . $thumb_dir . '$`', $rep)) {
						if (!files::suppFile($file)) {
							$ok = FALSE;
						}
					}
				}
				closedir($dir);
				if ($remakedir) {
					if (preg_match('`/' . $thumb_dir . '$`', $rep)) {
						if (file_exists(($gal_dir . $rep))) {
							if (!files::suppFile($gal_dir . $rep)) {
								$ok = FALSE;
							}
						}
						$newdir = $gal_dir . preg_replace('`/' . $thumb_dir . '$`', '/' . $this->template['config']['galerie_repertoire_vignettes'] . '', $rep);
						if (!files::createDir($newdir)) {
							if (!file_exists(($newdir))) {
								$ok = FALSE;
							}
						}
					}
				}
			} else {
				$ok = FALSE;
			}
		}
		return $ok;
	}



	/*
	 *	Infos système.
	*/
	function config_infosys() {

		$this->template['infos']['title'] = 'infos système';

		// OS.
		$this->template['infos']['php_os'] = '<span class="action_infos">' . PHP_OS . '</span>';

		// Version de PHP.
		$this->template['infos']['php_version'] = '<span class="action_infos">' . PHP_VERSION . '</span>';

		// Version de MySQL.
		$this->template['infos']['mysql_version'] = '<span class="action_infos">' . @mysql_get_server_info() . '</span>';

		// Version GD.
		if (function_exists('gd_info')) {
			$gd_info = gd_info();
			$gd_version = (empty($gd_info['GD Version'])) ? 'inconnue' : $gd_info['GD Version'];
		} elseif (function_exists('imagetypes')) {
			$gd_version = 'inconnue';
		} else {
			$gd_version = 'GD n\'est pas activé';
		}
		$this->template['infos']['gd_version'] = '<span class="action_infos">' . $gd_version . '</span>';

		// Support JPG
		$jpg = (imagetypes() & IMG_JPG) ? 'oui' : 'non';
		$this->template['infos']['gd_jpg'] = '<span class="action_infos">' . $jpg . '</span>';

		// Support GIF
		$gif = (imagetypes() & IMG_GIF) ? 'oui' : 'non';
		$this->template['infos']['gd_gif'] = '<span class="action_infos">' . $gif . '</span>';

		// Support PNG
		$png = (imagetypes() & IMG_PNG) ? 'oui' : 'non';
		$this->template['infos']['gd_png'] = '<span class="action_infos">' . $png . '</span>';

		// Mémoire disponible.
		$this->template['infos']['memory_limit'] = (function_exists('ini_get')) ? ini_get('memory_limit') : 'inconnue';

		// Extension Exif.
		$this->template['infos']['exif'] = (function_exists('read_exif_data')) ? 'activée' : 'désactivée';

		// Extension Zip.
		$this->template['infos']['zip'] = (function_exists('zip_open')) ? 'activée' : 'désactivée';

		// Date serveur.
		$this->template['infos']['server_time'] = '<span class="action_infos">' . outils::ladate(0, '%A %d %B %Y - %H:%M:%S') . '</span>';

		// Infos serveur.
		if (isset($_SERVER['SERVER_SIGNATURE'])) {
			$this->template['infos']['server_infos'] = '<span class="action_infos">' . strip_tags($_SERVER['SERVER_SIGNATURE']) . '</span>';
		} else {
			$this->template['infos']['server_infos'] = '<span class="action_infos">non disponible</span>';
		}

		// Safe Mode.
		$this->template['infos']['safe_mode'] = (ini_get('safe_mode')) ? 'activé' : 'désactivé';

		// magic_quotes_gpc et magic_quotes_runtime
		if (get_magic_quotes_gpc()) {
			$this->template['infos']['magic_quotes_gpc'] = '<span class="action_infos">activée</span>';
		} else {
			$this->template['infos']['magic_quotes_gpc'] = '<span class="action_infos">désactivée</span>';
		}
		if (get_magic_quotes_runtime()) {
			$this->template['infos']['magic_quotes_runtime'] = '<span class="action_infos">activée</span>';
		} else {
			$this->template['infos']['magic_quotes_runtime'] = '<span class="action_infos">désactivée</span>';
		}

		// Droits d'accès en écriture.
		$f = './../config/conf.php';
		if (files::chmodTest($f))  {
			$this->template['infos']['acces_conf'] = '<span class="infosys_succes">Le fichier de configuration est accessible en écriture (' . substr(sprintf('%o', fileperms($f)), -4) . ').</span>';
		} else {
			$this->template['infos']['acces_conf'] = '<span class="infosys_erreur">Le fichier de configuration n\'est pas accessible en écriture (' . substr(sprintf('%o', fileperms($f)), -4) . ').</span>';
		}
		$f = './../' . GALERIE_ALBUMS . '/';
		if (files::chmodTest($f))  {
			$this->template['infos']['acces_albums'] = '<span class="infosys_succes">Le répertoire des albums est accessible en écriture (' . substr(sprintf('%o', fileperms($f)), -4) . ').</span>';
		} else {
			$this->template['infos']['acces_albums'] = '<span class="infosys_erreur">Le répertoire des albums n\'est pas accessible en écriture (' . substr(sprintf('%o', fileperms($f)), -4) . ').</span>';
		}
		$f = './../cache/';
		if (files::chmodTest($f))  {
			$this->template['infos']['acces_cache'] = '<span class="infosys_succes">Le répertoire cache/ est accessible en écriture (' . substr(sprintf('%o', fileperms($f)), -4) . ').</span>';
		} else {
			$this->template['infos']['acces_cache'] = '<span class="infosys_erreur">Le répertoire cache/ n\'est pas accessible en écriture (' . substr(sprintf('%o', fileperms($f)), -4) . ').</span>';
		}
		$f = './../cache/cat_thumb/';
		if (files::chmodTest($f))  {
			$this->template['infos']['acces_cat_thumb'] = '<span class="infosys_succes">Le répertoire cache/cat_thumb/ est accessible en écriture (' . substr(sprintf('%o', fileperms($f)), -4) . ').</span>';
		} else {
			$this->template['infos']['acces_cat_thumb'] = '<span class="infosys_erreur">Le répertoire cache/cat_thumb/ n\'est pas accessible en écriture (' . substr(sprintf('%o', fileperms($f)), -4) . ').</span>';
		}

	}

}



/*
 *	Fonctions de template.
*/
class template {
	
	/*  Données brutes issues du "moteur" */
	var $data;

	/* Variable interne temporaire utilisée par certaines boucles */
	var $interne;
	var $file;


	/*
	 *	Constructeur.
	*/
	function template($data) {

		$this->data = $data;
		$this->file = (basename(GALERIE_URL) != 'index.php') ? basename(GALERIE_URL) : '';

	}
	function getVID($type = 0) {
		switch ($type) {
			case 0 :
				echo '<input type="hidden" name="igalvid" value="' . $this->data['new_vid'] . '" />';
				break;
			case 1 :
				echo '&amp;igalvid=' . $this->data['new_vid'];
				break;
			case 2 :
				return '&amp;igalvid=' . $this->data['new_vid'];
				break;
		}
	}
	function getInputDisabled($f, $s = ' disabled="disabled"') {
		if (isset($this->data['enabled'][$f]) && !$this->data['enabled'][$f])
		{ echo $s; }
	}
	function getTextDisabled($f, $s = ' class="disabled"') {
		if (isset($this->data['enabled'][$f]) && !$this->data['enabled'][$f])
		{ echo $s; }
	}



	/*
	 *	Version de la galerie.
	*/
	function getGalerieVersion() {
	}


	/*
	 *	Chemin du répertoire des albums.
	*/
	function getAlbumsDir() {
		$sn = dirname(dirname($_SERVER['SCRIPT_NAME']));
		$sn = ($sn == '/' || $sn == '\\') ? '' : $sn;
		echo $sn . '/' . preg_replace('`/$`', '',  GALERIE_ALBUMS) . '/';
		
	}


	/*
	 *	Permet de répeter un même code avec un chiffre changeant.
	*/
	function boucle($s, $n = 0, $m = 100) {
		for ($i = $n; $i <= $m; $i++) {
			printf($s, $i);
		}
	}


	/*
	 *	Mise en évidence d'un terme recherché dans un texte.
	*/
	function highlight_search($text, $terme) {

		if ($terme) {

			$terme = preg_split('`\s+(?![\w\s]*[^-\s]")`i', $terme, -1, PREG_SPLIT_NO_EMPTY);
			for ($i = 0; $i < count($terme); $i++) {

				// On prépare la RegExp.
				$terme[$i] = preg_quote(trim($terme[$i]));
				$terme[$i] = preg_replace('`([lg])`', '~#µ$1µ#~', $terme[$i]);
				$terme[$i] = preg_replace('`t`', '~#µtµ#~', $terme[$i]);
				$terme[$i] = preg_replace('`a`', '~#µaµ#~', $terme[$i]);
				$terme[$i] = preg_replace('`m`', '~#µmµ#~', $terme[$i]);
				$terme[$i] = preg_replace('`p`', '~#µpµ#~', $terme[$i]);
				$terme[$i] = preg_replace('`;`', '~#µ;µ#~', $terme[$i]);
				$terme[$i] = str_replace('&', '&amp;', $terme[$i]);
				$terme[$i] = str_replace('<', '&lt;', $terme[$i]);
				$terme[$i] = str_replace('>', '&gt;', $terme[$i]);
				$terme[$i] = preg_replace('`~#µ([lg])µ#~`', '$1(?!(?<=&$1)t;)', $terme[$i]);
				$terme[$i] = str_replace('~#µtµ#~', 't(?!(?<=&[lg]t);)', $terme[$i]);
				$terme[$i] = str_replace('~#µaµ#~', 'a(?!(?<=&a)mp;)', $terme[$i]);
				$terme[$i] = str_replace('~#µmµ#~', 'm(?!(?<=&am)p;)', $terme[$i]);
				$terme[$i] = str_replace('~#µpµ#~', 'p(?!(?<=&amp);)', $terme[$i]);
				$terme[$i] = str_replace('~#µ;µ#~', '(?<!&[lg]t)(?<!&amp);', $terme[$i]);

				// Accents.
				$terme[$i] = outils::regexp_accents($terme[$i]);

				// On supprime les éventuels guillemets.
				$terme[$i] = str_replace('"', '', $terme[$i]);

				// Jokers « * » et « ? ».
				if (preg_match('`^(\x5C[*?])+$`', $terme[$i])) {
					$terme[$i] = '(?:&amp;|&lt;|&gt;|.+)';
				} else {
					$terme[$i] = str_replace('\*', '(?:[^\s>]*(?:</?a[^\s>]*>(?:[^\s<]+</a>)?)?[^\s>]*?)', $terme[$i]);
					$terme[$i] = str_replace('\?', '(&amp;|&[lg]t;|[^\W<>&])(?!(?:[^<]*>))', $terme[$i]);
				}

				// Espaces.
				$terme[$i] = preg_replace('`\s+`', '(?:\s+(?:</?a[^>]*>)?|(?:</?a[^>]*>)?\s+)', $terme[$i]);
			}

			$terme = '(?:' . implode('|', $terme) . ')';

			// On entoure les termes matchés par des balises <span>.
			$casse = (isset($_REQUEST['s_casse'])) ? '' : 'i';
			$text = preg_replace('`(?<!\w)(' . $terme . '(?![^<>]*>))(?!\w)`' . $casse, '<span class="s_hl">$1</span>', $text);

			// On remet de l'ordre dans les balises si nécessaire.
			$text = preg_replace('`(<span[^>]+>)([^<]*)(</?a[^>]*>)([^<]*)(</span>)`', '$1$2$5$3$1$4$5', $text);

		}

		return $text;
	}
	



	/* Informations générales. */
	function getInfo($i, $s = '%s') {
		if (isset($this->data['infos'][$i])) {
			$info = ($i == 'type_nom') ? strip_tags($this->data['infos'][$i]) : $this->data['infos'][$i];
			printf($s, $info);
		}
	}

	/* Section actuelle. */
	function getSectionActuel($p, $o = '') {
		static $id;
		$id = ($o) ? $o : $id;
		if (preg_match('`' . $_GET['section'] . '(\s|$)`', $p)) {
			echo $id;
		}
	}

	/* Page actuelle. */
	function getPageActuelle($p, $o = '') {
		static $id;
		$id = ($o) ? $o : $id;
		if (empty($_REQUEST['page'])) {
			echo $id;
			$id = '';
		} elseif ($_REQUEST['page'] == $p) {
			echo $id;
		}
	}

	/* On détermine quelle portion de code HTML l'on doit afficher. */
	function display($type) {
		switch ($type) {
			case 'rapport' : if (!empty($this->data[$type])) { echo $this->data[$type]; } break;
			case 'barre_nav' : if (isset($this->data['nav']['pages'][2])) { return TRUE; } break;
			default : if (isset($this->data['display'][$type])) { return TRUE; }
		}
	}

	/* Barres de navigation */
	function getBarreNavPageNext($s = '%s %s %s') {
		for ($i = 1; $i <= count($this->data['nav']['pages']); $i++) {
			if (isset($this->data['infos']['page_actuelle']) && $this->data['infos']['page_actuelle'] == $i) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			printf($s, $this->data['nav']['pages'][$i]['page'], $selected, $i);
		}
	}

	/* Barre de navigation */
	function getBarreNav($s, $type) {
		static $clavier;
		switch ($type) {
			case 'first' :
				$e = 'premiere';   $k = ' id="_nav_first"';		$l = '&lt;&lt;'; $t = 'Première page';	 break;
			case 'prev' :
				$e = 'precedente'; $k = ' id="_nav_prev"';	$l = '&lt;';	 $t = 'Page précédente'; break;
			case 'next' :
				$e = 'suivante';   $k = ' id="_nav_next"';	$l = '&gt;';	 $t = 'Page suivante';   break;
			default :
				$e = 'derniere';   $k = ' id="_nav_last"';		$l = '&gt;&gt;'; $t = 'Dernière page';
		}
		if (empty($clavier)) {
			$keys = $k;
			if ($type == 'last') {
				$clavier = 1;
			}
		} else {
			$keys = '';
		}
		if (isset($this->data['nav'][$e][0])) {
			$lien = $l;
			$class = ' inactive';
		} else {
			$recherche = (isset($this->data['infos']['recherche'])) ? $this->data['infos']['recherche'] : '';
			$lien = '<a' . $keys . ' href="?' . htmlentities($this->data['infos']['section'] . $recherche) . '&amp;startnum=' . 
				$this->data['nav'][$e][1] . '" title="' . $t . '">' . $l . '</a>';
			$class = '';
		}
		printf($s, $class, $lien);
	}

	/* Activation/désactivation de la galerie. */
	function getGalActiveLink() {
		$mot = ($this->data['config']['active_galerie']) ? 'fermer' : 'ouvrir';
		$arg = ($this->data['config']['active_galerie']) ? 'desactive': 'active';
		$query_string = '';
		foreach ($_REQUEST as $k => $v) {
			if ($k == 'startnum' || 
			    $k == 'page' ||
			    $k == 'section' ||
			    $k == 'cat' ||
			    $k == 'img') {
				$query_string .= $k . '=' . $v . '&';
			}
		}
		echo '<a href="index.php?' . htmlspecialchars($query_string) . 'gal_' . $arg . '=1' . $this->getVID(2) . '">' . $mot . ' la galerie</a>';
	}

	/* Rapport des actions effectuées par l'utilisateur. */
	function getRapport($s = '%s') {
		if (isset($this->data['infos']['action'])) {
			$rapport = '';
			foreach ($this->data['infos']['action'] as $k => $v) {
				$rapport .= '<span>' . $v . '</span>';
			}
			printf($s, '<div class="rapport_msg rapport_succes"><div>' . $rapport . '</div></div>');
		}
		if (isset($this->data['infos']['attention'])) {
			$rapport = '';
			foreach ($this->data['infos']['attention'] as $k => $v) {
				$rapport .= '<span>' . $v . '</span>';
			}
			printf($s, '<div class="rapport_msg rapport_avert"><div>' . $rapport . '</div></div>');
		}
		if (isset($this->data['infos']['erreur'])) {
			$rapport = '';
			foreach ($this->data['infos']['erreur'] as $k => $v) {
				$rapport .= '<span>' . $v . '</span>';
			}
			printf($s, '<div class="rapport_msg rapport_erreur"><div>' . $rapport . '</div></div>');
		}
		if (isset($this->data['infos']['info'])) {
			$rapport = '';
			foreach ($this->data['infos']['info'] as $k => $v) {
				$rapport .= '<span>' . $v . '</span>';
			}
			printf($s, '<div class="rapport_msg rapport_infos"><div>' . $rapport . '</div></div>');
		}
	}




	/*
	 * 
	 * ======================================== COMMENTAIRES;
	 *
	*/

	function getNextComment() {
		if (!empty($this->data['comments'])) {
			if (!isset($this->interne['comment_num'])) {
				$this->interne['comment_num'] = 0;
			} else {
				$this->interne['comment_num']++;
			}
			if (isset($this->data['comments'][$this->interne['comment_num']]['commentaire_id'])) {
				return TRUE;
			}
		}
	}

	/* Elements constituants chaque commentaire et informations commentaires */
	function getComment($type, $o = '', $o2 = '') {
		$section = 'href="index.php?section=commentaires&amp;page=display&amp;' . $this->data['infos']['obj_type'] . '=' . $this->data['infos']['obj'] . '&amp;startnum=' . $this->data['infos']['startnum'];
		switch ($type) {
			case 'date' :
				echo outils::ladate($this->data['comments'][$this->interne['comment_num']]['commentaire_date'], '%A %d %B %Y à %H:%M:%S');
				break;
			case 'auteur' :
				$search = (isset($_REQUEST['search'])) ? $_REQUEST['search'] : '';
				$texte = outils::html_specialchars($this->data['comments'][$this->interne['comment_num']]['commentaire_auteur']);
				if (isset($_REQUEST['s_auteur'])) {
					$texte = $this->highlight_search($texte, $search);
				}
				echo '<a href="?section=commentaires&amp;page=display&amp;u=1&amp;s=1&amp;cat=1&amp;search=' . urlencode('"' . $this->data['comments'][$this->interne['comment_num']]['commentaire_auteur'] . '"') . '&amp;s_auteur=on&amp;s_tous=on&amp;s_dnpc=0&amp;s_dnpd=j&amp;s_dnsc=5&amp;s_dnsd=j">' . $texte . '</a>';
				break;
			case 'courriel' :
				$s = ($o) ? $o : '%s';
				$search = (isset($_REQUEST['search'])) ? $_REQUEST['search'] : '';
				$mail = $this->data['comments'][$this->interne['comment_num']]['commentaire_mail'];
				if ($this->data['comments'][$this->interne['comment_num']]['user_id'] > 0) {
					$mail = $this->data['comments'][$this->interne['comment_num']]['user_mail'];
				}
				if (!empty($mail)) {
					if ($mail != $this->highlight_search($this->data['comments'][$this->interne['comment_num']]['commentaire_mail'], $search)
					 && isset($_REQUEST['s_mail'])) {
						$mail = '<a href="mailto:' . htmlentities($mail) . '"><span class="s_hl">courriel</span></a>';
					} else {
						$mail = '<a href="mailto:' . htmlentities($mail) . '">courriel</a>';
					}
					printf($s, $mail);
				}
				break;
			case 'site' :
				$s = ($o) ? $o : '%s';
				$search = (isset($_REQUEST['search'])) ? $_REQUEST['search'] : '';
				$site = $this->data['comments'][$this->interne['comment_num']]['commentaire_web'];
				if ($this->data['comments'][$this->interne['comment_num']]['user_id'] > 0) {
					$site = $this->data['comments'][$this->interne['comment_num']]['user_web'];
				}
				if (!empty($site)) {
					if ($site != $this->highlight_search($this->data['comments'][$this->interne['comment_num']]['commentaire_web'], $search)
					 && isset($_REQUEST['s_web'])) {
						$site = '<a class="ex" href="' . htmlentities($site) . '"><span class="s_hl">site Web</span></a>';
					} else {
						$site = '<a class="ex" href="' . htmlentities($site) . '">site Web</a>';
					}
					printf($s, $site);
				}
				break;
			case 'ip' :
				$search = (isset($_REQUEST['search'])) ? $_REQUEST['search'] : '';
				$texte = $this->data['comments'][$this->interne['comment_num']]['commentaire_ip'];
				if (isset($_REQUEST['s_ip'])) {
					$texte = $this->highlight_search($texte, $search);
				}
				echo '<a href="?section=commentaires&amp;page=display&amp;u=1&amp;s=1&amp;cat=1&amp;search=' . urlencode('"' . $this->data['comments'][$this->interne['comment_num']]['commentaire_ip'] . '"') . '&amp;s_ip=on&amp;s_tous=on&amp;s_dnpc=0&amp;s_dnpd=j&amp;s_dnsc=5&amp;s_dnsd=j">' . $texte . '</a>';
				break;
			case 'msg' :
				$search = (isset($_REQUEST['search'])) ? $_REQUEST['search'] : '';
				$texte = $this->data['comments'][$this->interne['comment_num']]['commentaire_message'];
				$texte = outils::comment_format($texte);
				if (isset($_REQUEST['s_msg'])) {
					$texte = $this->highlight_search($texte, $search);
				}
				echo $texte;
				break;
			case 'image' :
				$lien = '../' . $this->file . '?img=' . $this->data['comments'][$this->interne['comment_num']]['image_id'];
				printf($o, $lien);
				break;
			case 'album' :
				$s = ($o) ? $o : '<a href="../%s?alb=%s">%s</a>';
				printf($s, $this->file, $this->data['comments'][$this->interne['comment_num']]['categorie_id'], strip_tags($this->data['comments'][$this->interne['comment_num']]['categorie_nom']));
				break;
			case 'id' :
				echo $this->data['comments'][$this->interne['comment_num']]['commentaire_id'];
				break;
			case 'chemin' :
				echo $this->data['comments'][$this->interne['comment_num']]['image_chemin'];
				break;
			case 'visible' :
				if (empty($this->data['comments'][$this->interne['comment_num']]['commentaire_visible'])) {
					echo '<a ' . $section . '&amp;active=' . $this->data['comments'][$this->interne['comment_num']]['commentaire_id'] . htmlentities($this->data['infos']['recherche']) . $this->getVID(2) . '">' . $o . '</a>';
				} else {
					echo '<a ' . $section . '&amp;desactive=' . $this->data['comments'][$this->interne['comment_num']]['commentaire_id'] . htmlentities($this->data['infos']['recherche']) . $this->getVID(2) . '">' . $o2 . '</a>';
				}
				break;
			case 'ban_auteur' :
				if (empty($this->data['comments'][$this->interne['comment_num']]['ban_auteur'])) {
					echo '<a ' . $section . '&amp;ban_auteur=' . $this->data['comments'][$this->interne['comment_num']]['commentaire_id'] . htmlentities($this->data['infos']['recherche']) . $this->getVID(2) . '">' . $o2 . '</a>';
				} else {
					echo '<a ' . $section . '&amp;unban_auteur=' . $this->data['comments'][$this->interne['comment_num']]['commentaire_id'] . htmlentities($this->data['infos']['recherche']) . $this->getVID(2) . '">' . $o . '</a>';
				}
				break;
			case 'ban_ip' :
				if (empty($this->data['comments'][$this->interne['comment_num']]['ban_ip'])) {
					echo '<a ' . $section . '&amp;ban_ip=' . $this->data['comments'][$this->interne['comment_num']]['commentaire_id'] . htmlentities($this->data['infos']['recherche']) . $this->getVID(2) . '">' . $o2 . '</a>';
				} else {
					echo '<a ' . $section . '&amp;unban_ip=' . $this->data['comments'][$this->interne['comment_num']]['commentaire_id'] . htmlentities($this->data['infos']['recherche']) . $this->getVID(2) . '">' . $o . '</a>';
				}
				break;
			case 'supprime' :
				$lien_sup = preg_replace('`^href="`', '', $section) . '&amp;supprime=' . $this->data['comments'][$this->interne['comment_num']]['commentaire_id'] . htmlentities($this->data['infos']['recherche']) . $this->getVID(2);
				echo '<a href="javascript:confirm_sup_comment(\'' . $lien_sup . '\');" class="co_delete">' . $o . '</a>';
				break;
			case 'inactif' :
				if (empty($this->data['comments'][$this->interne['comment_num']]['commentaire_visible'])) {
					echo $o;
				}
				break;
			case 'ih_search' :
				$s = ($o2) ? $o2 : '%s';
				if (isset($this->data['comments']['search'])) {
					$inputs = '';
					if ($o == 'inputs') {
						foreach ($this->data['comments']['search'] as $k => $v) {
							$inputs .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
						}
						printf($s, $inputs);
					}
					if ($o == 'params') {
						foreach ($this->data['comments']['search'] as $k => $v) {
							$inputs .=  '&amp;' . $k . '=' . $v;
						}
						printf($s, $inputs);
					}
				}
				break;
			case 'search_hl' :
				if (isset($this->data['comments']['search']) && empty($this->data['comments']['no_comments'])) {
					echo $o;
				}
				break;
			case 'search_result' :
				if (isset($this->data['comments']['search'])) {
					if ($this->data['infos']['nb_objets']) {
						printf($o, outils::html_specialchars($_REQUEST['search']));
					} else {
						printf($o2, outils::html_specialchars($_REQUEST['search']));
					}
				}
				break;
			case 'sortir' :
				if (isset($this->data['comments']['search'])) {
					printf($o, 'index.php?' . htmlentities($this->data['infos']['section']));
				}
				break;
			case 'no_comments' :
				if (isset($this->data['comments']['no_comments'])
				 && empty($this->data['comments']['search'])
				 && $this->data['infos']['obj'] == 1) {
					$e = $this->data['infos']['comment_filtre'];
					$e = ($e == 'tous') ? '' : ' ' . $e;
					printf($o, $e);
				}
				break;
			case 'sub' :
				$objet = str_replace('image', 'img', $this->data['infos']['sub_objects']);
				$objet = str_replace('categorie', 'cat', $objet);
				echo $objet;
				break;
			case 'thumb' :
				$s = ($o2) ? $o2 : '<a title="%s" href="%s"><img %s alt="%s" src="%s" /></a>';
				$alt = htmlentities(strip_tags($this->data['comments'][$this->interne['comment_num']]['image_nom']));
				$lien = '../' . $this->file . '?img=' . $this->data['comments'][$this->interne['comment_num']]['image_id'];
				$img = $this->data['comments'][$this->interne['comment_num']]['image_chemin'];
				$img = '../getimg.php?m=1&amp;img=' . $img;
				$size = outils::thumb_size('img', $o, $this->data['comments'][$this->interne['comment_num']]['image_largeur'], $this->data['comments'][$this->interne['comment_num']]['image_hauteur']);
				$taille = 'width="' . $size[0] . '" height="' . $size[1] . '"';
				printf($s, $alt, $lien, $taille, $alt, $img);
				break;
		}
	}


	/* Affichage des commentaires : nombre par page */
	function getCommentNb($s = '%s %s %s', $n = 10) {
		for ($i = 1; $i <= $n; $i++) {
			$selected = ($this->data['infos']['nb_comments'] == $i) ? ' selected="selected"' : '';
			printf($s, $i, $selected, $i);
		}
	}


	/* Ordre des commentaires */
	function getCommentSortOrdre($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['infos']['comment_sort'] == $type) {
			printf($s, $o);
		}
	}

	function getCommentSortSens($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['infos']['comment_sens'] == $type) {
			printf($s, $o);
		}
	}

	/* Sous-éléments contenant des commentaires. */
	function getCommentSubCats($s = '%s %s') {
		$subcats = $this->data['comments']['sub_item'];
		$objet = $this->data['infos']['sub_objects'];
		for ($i = 0; $i < count($subcats); $i++) {
			printf($s, $subcats[$i][$objet . '_id'], strip_tags($subcats[$i][$objet . '_nom']));
		}
	}


	/* Liens de position. */
	function getCommentPosition($s = '%s [%s] %s', $o1 = ' / ', $o2 = '|', $o3 = ' - page ') {
		$pos = str_replace('%sep', $o1, $this->data['comments']['position']);
		if ($this->data['infos']['nb_pages'] > 1) {
			if (!isset($this->data['infos']['page_actuelle'])) {
				header('Location:' . basename($_SERVER['PHP_SELF']) . '?' . preg_replace('`startnum=\d+`', 'startnum=0', $_SERVER['QUERY_STRING']));
			}
			$page = $o3 . $this->data['infos']['page_actuelle'] . $o2 . $this->data['infos']['nb_pages'];
		} else {
			$page = '';
		}
		printf($s, $pos, $this->data['infos']['nb_objets'], $page);
	}


	/* Filtre */
	function getCommentFiltre($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['infos']['comment_filtre'] == $type) {
			printf($s, $o);
		}
	}
	
	/* Messages */
	function getCommentMsgDisplay($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['config']['admin_comment_msg_display'] == $type) {
			printf($s, $o);
		}
	}

	/* Forumulaire de recherche */
	function getCommentSearch($type, $defaut, $valeur = 0) {
		if ($type == 's_dnpd' || $type == 's_dnsd') {
			if ((isset($_GET['s']) && isset($_GET[$type]) && $_GET[$type] == $valeur)
			 || (!isset($_GET['s']) && $defaut)) {
				echo ' selected="selected"';
			}
		} else {
			if ((isset($_GET['s']) && isset($_GET[$type]))
			 || (!isset($_GET['s']) && $defaut)) {
				echo ' checked="checked"';
			}
		}
	}
	function getCommentSearchNb($s, $n = 0, $m = 100, $type, $defaut = 0) {
		for ($i = $n; $i <= $m; $i++) {
			$selected = '';
			if ((isset($_GET['s']) && isset($_GET[$type]) && $_GET[$type] == $i)
			 || (!isset($_GET['s']) && $defaut == $i)) {
				$selected = ' selected="selected"';
			}
			printf($s, $i, $selected);
		}
	}



	/*
	 * 
	 * ======================================== COMMENTAIRES : OPTIONS;
	 *
	*/

	/* Liste des auteurs et des IP bannis */
	function getCommentBan($type, $s = '%s') {
		$bans = $this->data['config']['admin_comment_ban'];
		if (isset($bans[$type]) && count($bans[$type]) > 0) {
			$b = '';
			foreach ($bans[$type] as $k => $v) {
				$b .= '<option value="' . htmlentities($k) . '">' . htmlentities($k) . '</option>';
			}
			printf($s, $b);
		}
	}

	/* Modération des commentaires */
	function getCommentMod($s) {
		if ($this->data['config']['admin_comment_moderer']) {
			echo $s;
		}
	}

	/* Alerte par courriel */
	function getCommentAlert($s) {
		if ($this->data['config']['admin_comment_alert']) {
			echo $s;
		}
	}
	function getCommentAlertMail($s = '%s') {
		if (!empty($this->data['config']['admin_comment_mail'])) {
			printf($s, $this->data['config']['admin_comment_mail']);
		}
	}
	function getCommentAlertObjet($s = '%s') {
		if (!empty($this->data['config']['admin_comment_objet'])) {
			printf($s, $this->data['config']['admin_comment_objet']);
		}
	}

	function getCommentFac($type, $s) {
		if ($this->data['config']['comment_' . $type]) {
			echo $s;
		}
	}

	/* Contrôles */
	function getCommentAntiFlood($s = '%s') {
		printf($s, $this->data['config']['comment_antiflood']);
	}
	function getCommentMaxMsg($s = '%s') {
		printf($s, $this->data['config']['comment_maxmsg_nb']);
	}
	function getCommentMaxURL($s = '%s') {
		printf($s, $this->data['config']['comment_maxurl']);
	}

	/* Page des commentaires */
	function getPageComment() {
		if ($this->data['config']['galerie_page_comments']) {
			echo ' checked="checked"';
		}
	}
	function getPageCommentNb($s = '%s %s %s', $n = 10) {
		for ($i = 1; $i <= $n; $i++) {
			$selected = ($this->data['config']['galerie_page_comments_nb'] == $i) ? ' selected="selected"' : '';
			printf($s, $i, $selected, $i);
		}
	}



	/*
	 * 
	 * ======================================== OPTIONS : GENERAL;
	 *
	*/

	/* Message de confirmation */
	function getGeneralMaj($s = '%s') {
		if (!empty($this->data['action_maj'])) {
			printf($s, '<div class="rapport_msg rapport_succes"><div><span>Modifications enregistrées.</span></div></div>');
		}
	}

	/* Themes */
	function getGalerieThemes($s = '<option value="%1$s"%2$s>%1$s</option>', $o = ' selected="selected"') {
		if (isset($this->data['themes'])) {
			for ($i = 0; $i < count($this->data['themes']); $i++) {
				$selected = ($this->data['themes'][$i] == $this->data['config']['galerie_template']) ? $o : '';
				printf($s, $this->data['themes'][$i], $selected);
			}
		}
	}

	/* Styles */
	function getGalerieStyles($s = '<option value="%1$s"%2$s>%1$s</option>', $o = ' selected="selected"') {
		if (isset($this->data['styles'])) {
			for ($i = 0; $i < count($this->data['styles']); $i++) {
				$selected = ($this->data['styles'][$i] == $this->data['config']['galerie_style']) ? $o : '';
				printf($s, $this->data['styles'][$i], $selected);
			}
		}
	}

	function getGalerieAddStyle() {
		echo $this->data['config']['galerie_add_style'];
	}

	/* Liens */
	function getActiveLiens($o = ' checked="checked"') {
		if (!empty($this->data['config']['active_liens'])) {
			echo $o;
		}
	}
	function getLiens() {
		if (!empty($this->data['config']['galerie_liens'])) {
			$liens = @unserialize($this->data['config']['galerie_liens']);
			if (is_array($liens)) {
				for ($i = 0; $i < count($liens); $i++) {
					echo $liens[$i] . "\r\n";
				}
			}
		}
	}
	
	/* Formats de date */
	function getGeneralFormatDate($type) {
		$formats[1] = '%d-%m-%y';
		$formats[2] = '%d/%m/%y';
		$formats[3] = '%d-%m-%Y';
		$formats[4] = '%d/%m/%Y';
		$formats[5] = '%d %b %Y';
		$formats[6] = '%d %B %Y';
		$formats[7] = '%a %d %b %Y';
		$formats[8] = '%a %d %B %Y';
		$formats[9] = '%A %d %b %Y';
		$formats[10] = '%A %d %B %Y';
		$disabled = '';
		if (($type == 'tb' && !$this->data['enabled']['date_format_thumbs'])
		 || ($type == 'im' && !$this->data['enabled']['date_format_images'])) {
			$disabled = ' disabled="disabled"';
		}
		echo "\t\t\t\t\t\t\t\t\t\t" . '<select' . $disabled . ' name="g_f' . $type . 'date">';
		for ($i = 1; $i <= count($formats); $i++) {
			$selected = ($this->data['config']['galerie_' . $type . '_date_format'] == $formats[$i]) ? ' selected="selected"' : '';
			echo "\n\t\t\t\t\t\t\t\t\t\t\t" . '<option' . $selected . ' value="' . $i . '">' . strftime($formats[$i]) . '</option>';
		}
		echo "\n\t\t\t\t\t\t\t\t\t\t" . '</select>';
	}

	/* Visites admin */
	function getNoHits($s = ' checked="checked"') {
		if ($this->data['config']['admin_no_hits']) {
			echo $s;
		}
	}
	function getNoHitsMode($type, $s = ' checked="checked"') {
		if ($this->data['config']['admin_no_hits_mode'] == $type) {
			echo $s;
		}
	}
	function getNoHitsIP() {
		echo $this->data['config']['admin_no_hits_ip'];
	}

	/* URL de la galerie */
	function getGalerieHOST() {
		echo 'http://' . $_SERVER['HTTP_HOST'];
	}
	function getGalerieURL() {
		echo $this->data['config']['galerie_url'];
	}

	/* Type d'URL */
	function getURLType() {
		$types['normal'] = 'normal';
		$types['query_string'] = 'QUERY_STRING';
		$types['path_info'] = 'PATH_INFO';
		$types['url_rewrite'] = 'URL rewrite';
		foreach ($types as $value => $nom) {
			$selected = ($this->data['config']['galerie_url_type'] == $value) ? ' selected="selected"' : '';
			echo "\n\t\t\t\t\t\t\t\t\t\t\t" . '<option' . $selected . ' value="' . $value . '">' . $nom . '</option>';
		}
	}

	function getIntegrated($s = ' checked="checked"') {
		if (!empty($this->data['config']['galerie_integrated'])) {
			echo $s;
		}
	}

	/* Javascript des thèmes et styles */
	function getGalerieJSStyles() {
		echo '<script type="text/javascript">' . "\n";
		echo '//<![CDATA[' . "\n";
		echo 'var themes_styles = new Array();' . "\n";
		foreach ($this->data['js_themes_styles'] as $theme => $styles) {
			echo 'themes_styles["' . $theme . '"] = new Array();' . "\n";
			for ($i = 0; $i < count($styles); $i++) {
				echo 'themes_styles["' . $theme . '"][' . $i . '] = "' . $styles[$i] . '";' . "\n";
			}
		}
		echo '//]]>' . "\n";
		echo '</script>';
	}

	/* Nombre de vignettes par page */
	function getNbThumbs($type, $n = 12, $s = '<option value="%1$s"%2$s>%1$s</option>', $o = ' selected="selected"') {
		for ($i = 1; $i <= $n; $i++) {
			$selected = ($this->data['config']['vignettes_' . $type] == $i) ? $o : '';
			printf($s, $i, $selected);
		}
	}

	/* Méthode d'affichage des images */
	function getImgDisplay($type, $s = ' checked="checked"') {
		if ($this->data['config']['galerie_images_window'] == $type) {
			echo $s;
		}
	}

	/* Redimensionnement des images */
	function getImgResize($type, $valeur, $s = ' checked="checked"') {
		switch ($type) {
			case 'mode' :
				if ($this->data['config']['galerie_images_resize'] == $valeur) {
					echo $s;
				}
				break;
			case 'html' :
				$html = preg_split('`x`i', $this->data['config']['galerie_images_resize_max_html'], -1, PREG_SPLIT_NO_EMPTY);
				echo ($valeur == 'l') ? $html[0] : $html[1];
				break;
			case 'gd' :
				$gd = preg_split('`x`i', $this->data['config']['img_resize_gd'], -1, PREG_SPLIT_NO_EMPTY);
				echo ($valeur == 'l') ? $gd[0] : $gd[1];
				break;
		}
	}

	/* Format de la date */
	function getDateFormat() {
		echo $this->data['config']['galerie_date_format'];
	}

	/* Images récentes */
	function getImgRecentes($type, $o = ' checked="checked"') {
		if ($type == 'etat') {
			if ($this->data['config']['display_recentes'] > 0) {
				echo $o;
			}
		} elseif ($type == 'nb') {
			if ($this->data['config']['galerie_recent_nb']) {
				echo $o;
			}
		} else {
			echo $this->data['config']['galerie_recent'];
		}
	}

	/* Métadonnés Exif & IPTC */
	function getmetadata($type, $o = ' checked="checked"') {
		if ($this->data['config']['active_' . $type]) {
			echo $o;
		}
	}

	/* Texte sur les images */
	function getitextcheckbox($type, $o = ' checked="checked"') {
		if (($type == 'active' && $this->data['config']['itext']) || !empty($this->data['config']['itext_params'][$type])) {
			echo $o;
		}
	}
	function getitexttext($type) {
		echo ' value="' . str_replace('^', ' ', htmlspecialchars(str_replace("\'", "'", $this->data['config']['itext_params'][$type]))) . '"';
	}
	function getitextselect($type, $v, $o = ' selected="selected"') {
		if ($this->data['config']['itext_params'][$type] == $v) {
			echo $o;
		}
	}
	function getitextfontes() {
		if (isset($this->data['fontes'])) {
			for ($i = 0; $i < count($this->data['fontes']); $i++) {
				$selected = ($this->data['config']['itext_params'][4] == $this->data['fontes'][$i]) ? ' selected="selected"' : '';
				echo '<option value="' . $this->data['fontes'][$i] . '"' . $selected . '>' . $this->data['fontes'][$i] . '</option>';
			}
		}
	}
	function getitextcolor($r, $g, $b) {
		$rgb[0] = $this->data['config']['itext_params'][$r];
		$rgb[1] = $this->data['config']['itext_params'][$g];
		$rgb[2] = $this->data['config']['itext_params'][$b];
		echo ' value="#' . outils::convert_rgb2html($rgb) . '"';
	}

	
	/* Ordre et sens d'affichage des vignettes */
	function getThumbSort($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['config']['vignettes_ordre'] != $type) {
			$o = '';
		}
		printf($s, $o);
	}
	function getThumbSens($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['config']['vignettes_sens'] == $type) {
			printf($s, $o);
		}
	}
	function getCatThumbSort($type, $s = '%s', $o = ' selected="selected"') {
		if (!preg_match('`^categorie_' . $type . '`', $this->data['config']['vignettes_cat_ordre'])) {
			$o = '';
		}
		printf($s, $o);
	}
	function getCatThumbSens($type, $s = '%s', $o = ' selected="selected"') {
		if (preg_match('`^categorie_[a-z]+\s' . $type . ',`', $this->data['config']['vignettes_cat_ordre'])) {
			printf($s, $o);
		}
	}
	function getTbCatType() {
		$type_alb = '';
		$type_cat = '';
		$type_sans = '';
		if ($this->data['config']['vignettes_cat_type'] == 'type ASC,') {
			$type_cat = ' selected="selected"';
		} elseif ($this->data['config']['vignettes_cat_type'] == 'type DESC,') {
			$type_alb = ' selected="selected"';
		} else {
			$type_sans = ' selected="selected"';
		}
		echo '<option' . $type_alb . ' value="alb">des albums avant celles des catégories</option>';
		echo '<option' . $type_cat . ' value="cat">des catégories avant celles des albums</option>';
		echo '<option' . $type_sans . ' value="sans">sans distinction entre albums et catégories</option>';
	}

	/* Informations sous les vignettes */
	function getThumbInfo($info, $o = ' checked="checked"') {
		if (!empty($this->data['config']['display_' . $info])) {
			echo $o;
		}
	}

	/* Dimensions des vignettes  */
	function getTbSizeMode($type, $mode) {
		if ($this->data['config']['galerie_tb_' . $type . '_mode'] == $mode) {
			echo ' checked="checked"';
		}
	}
	
	/* Mode d'affichage  des vignettes des catégories  */
	function getTbCatMode() {
		$selected_etendue = '';
		$selected_compact = '';
		if ($this->data['config']['vignettes_cat_mode'] == 'compact') {
			$selected_compact = ' selected="selected"';
		} else {
			$selected_etendue = ' selected="selected"';
		}
		echo '<option' . $selected_compact . ' value="compact">compacte</option>';
		echo '<option' . $selected_etendue . ' value="etendue">étendue</option>';
	}



	/*
	 * 
	 * ======================================== OPTIONS : TEXTES;
	 *
	*/
	function getActiveContact() {
		if ($this->data['config']['galerie_contact']) {
			echo ' checked="checked"';
		}
	}
	
	function getTextes($type) {
		echo outils::html_specialchars($this->data['config']['galerie_' . $type]);
	}

	function getFooter($type, $o = ' checked="checked"') {
		if ($type == 'message' && strstr($this->data['config']['galerie_footer'], '1')) {
			echo $o;
		}
		if ($type == 'counter' && strstr($this->data['config']['galerie_footer'], '2')) {
			echo $o;
		}
	}



	/*
	 * 
	 * ======================================== OPTIONS : FONCTIONS;
	 *
	*/

	function getFonction($fonction, $o = ' checked="checked"') {
		if (!empty($this->data['config'][$fonction])) {
			echo $o;
		}
	}
	
	function getFonctionOption($o) {
		if (isset($this->data['config'][$o])) {
			echo $this->data['config'][$o];
		}
	}




	/*
	 * 
	 * ======================================== OPTIONS : PERSONNALISATION;
	 *
	*/

	function getPerso($info, $o = ' checked="checked"') {
		if (!empty($this->data['config']['user_' . $info])) {
			echo $o;
		}
	}




	/*
	 * 
	 * ======================================== OPTIONS : EXIF;
	 *
	*/

	function getNextIptc() {
		if (!empty($this->data['config']['infos_iptc'])) {
			if (!isset($this->interne['iptc_num'])) {
				$this->interne['iptc_num'] = 0;
				$n = 0;
				foreach ($this->data['config']['infos_iptc'] as $k => $v) {
					$this->interne['infos_iptc'][$n] = $v;
					$this->interne['infos_iptc'][$n]['champ'] = $k;
					$n++;
				}
			} else {
				$this->interne['iptc_num']++;
			}
			if (isset($this->interne['infos_iptc'][$this->interne['iptc_num']])) {
				return TRUE;
			}
		}
	}
	function getIptc($type) {
		switch ($type) {
			case 'desc' :
				echo htmlspecialchars($this->interne['infos_iptc'][$this->interne['iptc_num']]['nom']);
				break;
			case 'id' :
				echo substr($this->interne['infos_iptc'][$this->interne['iptc_num']]['champ'], 2);
				break;
			case 'etat';
				if ($this->interne['infos_iptc'][$this->interne['iptc_num']]['active']) {
					echo ' checked="checked"';
				}
				break;
			case 'inactive';
				if (!$this->interne['infos_iptc'][$this->interne['iptc_num']]['active']) {
					echo ' iptc_inactive';
				}
				break;
		}
	}




	/*
	 * 
	 * ======================================== OPTIONS : EXIF;
	 *
	*/

	function getNextExif() {
		if (!empty($this->data['config']['infos_exif'])) {
			if (!isset($this->interne['exif_num'])) {
				$this->interne['exif_num'] = 0;
			} else {
				$this->interne['exif_num']++;
			}
			if (isset($this->data['config']['infos_exif'][$this->interne['exif_num']])) {
				return TRUE;
			}
		}
	}
	function getNextExifEnum() {
		if (isset($this->data['config']['infos_exif'][$this->interne['exif_num']]['format'])
		 && is_array($this->data['config']['infos_exif'][$this->interne['exif_num']]['format'])) {
			if (!isset($this->interne['exif_liste'])) {
				$this->interne['exif_liste'] = 0;
			} else {
				$this->interne['exif_liste']++;
			}
			if (current($this->data['config']['infos_exif'][$this->interne['exif_num']]['format'])) {
				return TRUE;
			} else {
				$this->interne['exif_liste'] = 0;
			}
		}
	}
	function getExif($type, $o = '') {
		$disabled = '';
		if (!$this->data['enabled']['exif']) {
			$disabled = ' disabled="disabled"';
		}
		switch ($type) {
			case 'etat' :
				if ($this->data['config']['infos_exif'][$this->interne['exif_num']]['active']) {
					echo ' checked="checked"';
				}
				break;
			case 'desc' :
				echo htmlspecialchars($this->data['config']['infos_exif'][$this->interne['exif_num']]['desc']);
				break;
			case 'id' :
				echo $this->interne['exif_num'];
				break;
			case 'sections' :
				$selected = ' selected="selected"';
				$section = array(0=>'',1=>'',2=>'',3=>'',4=>'',5=>'');
				switch ($this->data['config']['infos_exif'][$this->interne['exif_num']]['section']) {
					case 'COMPUTED' : $section[0] = $selected; break;
					case 'IFD0' : $section[1] = $selected; break;
					case 'THUMBNAIL' : $section[2] = $selected; break;
					case 'EXIF' : $section[3] = $selected; break;
					case 'INTEROP' : $section[4] = $selected; break;
					case 'MAKERNOTE' : $section[5] = $selected; break;
				}
				echo '<select' . $disabled . ' name="exif_param_section[' . $this->interne['exif_num'] . ']">
						<option' . $section[0] . ' value="COMPUTED">COMPUTED</option>
						<option' . $section[1] . ' value="IFD0">IFD0</option>
						<option' . $section[2] . ' value="THUMBNAIL">THUMBNAIL</option>
						<option' . $section[3] . ' value="EXIF">EXIF</option>
						<option' . $section[4] . ' value="INTEROP">INTEROP</option>
						<option' . $section[5] . ' value="MAKERNOTE">MAKERNOTE</option>
					</select>';
				break;
			case 'method' :
				$selected = ' selected="selected"';
				$method = array(0=>'',1=>'',2=>'',3=>'',4=>'');
				switch ($this->data['config']['infos_exif'][$this->interne['exif_num']]['method']) {
					case 'simple' : $method[0] = $selected; break;
					case 'date' : $method[1] = $selected; break;
					case 'nombre' : $method[2] = $selected; break;
					case 'liste' : $method[3] = $selected; break;
					case 'version' : $method[4] = $selected; break;
				}
				echo '<select' . $disabled . ' name="exif_param_methode[' . $this->interne['exif_num'] . ']" onchange="exif_change_method(this,' . $this->interne['exif_num'] . ');">
						<option' . $method[0] . ' value="simple">simple</option>
						<option' . $method[1] . ' value="date">date</option>
						<option' . $method[2] . ' value="nombre">nombre</option>
						<option' . $method[3] . ' value="liste">liste</option>
						<option' . $method[4] . ' value="version">version</option>
					</select>';
				break;
			case 'tag' :
				echo htmlspecialchars($this->data['config']['infos_exif'][$this->interne['exif_num']]['tag']);
				break;
			case 'format' :
				if (isset($this->data['config']['infos_exif'][$this->interne['exif_num']]['format'])
				 && !is_array($this->data['config']['infos_exif'][$this->interne['exif_num']]['format'])) {
					echo htmlspecialchars($this->data['config']['infos_exif'][$this->interne['exif_num']]['format']);
				}
				break;
			case 'valeur' :
				$method = $this->data['config']['infos_exif'][$this->interne['exif_num']]['method'];
				if (
					($o == '' && $method == 'version')
				   || ($o == 'format' && $method == 'liste')
				   || ($o == 'liste' && ($method == 'date' || $method == 'simple' || $method == 'nombre'))
				) {
					echo ' style="display:none"';
				}
				break;
			case 'list_num' :
				echo $this->interne['exif_liste'];
				break;
			case 'list_tag' :
				echo htmlspecialchars(key($this->data['config']['infos_exif'][$this->interne['exif_num']]['format']));
				break;
			case 'list_display' :
				echo htmlspecialchars(current($this->data['config']['infos_exif'][$this->interne['exif_num']]['format']));
				next($this->data['config']['infos_exif'][$this->interne['exif_num']]['format']);
				break;
			case 'delete' :
				$section = $this->data['config']['infos_exif'][$this->interne['exif_num']]['section'];
				$tag = $this->data['config']['infos_exif'][$this->interne['exif_num']]['tag'];
				printf($o, $section, $tag, $this->getVID(2));
				break;
			case 'inactive' :
				if (!$this->data['config']['infos_exif'][$this->interne['exif_num']]['active']) {
					if (isset($this->data['config']['infos_exif'][$this->interne['exif_num']]['new'])) {
						echo ' exif_inactive exif_new';
					} else {
						echo ' exif_inactive';
					}
				}
				break;
			case 'focus_new' :
				if (isset($this->data['config']['infos_exif'][$this->interne['exif_num']]['new'])) {
					echo 'document.getElementById("exif_param_tag_' . $this->interne['exif_num'] . '").value="NouveauTag"; document.getElementById("exif_param_description_' . $this->interne['exif_num'] . '").focus(); //';
				}
				break;
			case 'display' :
				if (!isset($this->data['config']['infos_exif'][$this->interne['exif_num']]['new'])) {
					echo 'style="display:none" ';
				}
				break;
		}
	}



	/*
	 * 
	 * ======================================== UTILISATEURS : GENERAL;
	 *
	*/
	
	function getUsersGeneral($info, $o = ' checked="checked"') {
		if (!empty($this->data['config']['users_' . $info])) {
			echo $o;
		}
	}
	function getUploadMaxSize($s = '%s %s') {
		printf($s, $this->data['config']['users_upload_maxsize'], $this->data['config']['upload_max_filesize']);
	}
	function getUsersGeneralValues($o, $s = '%s') {
		switch ($o) {
			case 'maxwidth' :
			case 'maxheight' :
				printf($s, $this->data['config']['users_upload_' . $o]);
				break;
		}
	}

	



	/*
	 * 
	 * ======================================== GALERIE;
	 *
	*/

	function getNextObjet() {
		if (!empty($this->data['objets'])) {
			if (!isset($this->interne['objet_num'])) {
				$this->interne['objet_num'] = 0;
			} else {
				$this->interne['objet_num']++;
			}
			$obj = ($this->data['infos']['objet_type'] == 'alb') ? 'image' : 'categorie' ;
			if (isset($this->data['objets'][$this->interne['objet_num']][$obj . '_id'])) {
				return TRUE;
			}
		}
	}

	function getObjetInfo($type, $s = '%s', $o = '') {
		$obj = ($this->data['infos']['objet_type'] == 'alb') ? 'image' : 'categorie';
		$section = ' href="index.php?section=galerie&amp;page=gestion&amp;cat=' . $this->data['infos']['cat'] . '&amp;startnum=' . $this->data['infos']['startnum'];
		switch ($type) {
			case 'id' :
				echo $this->data['objets'][$this->interne['objet_num']][$obj . '_id'];
				break;
			case 'tb_cat' :
				if ($obj == 'categorie') {
					$id = $this->data['objets'][$this->interne['objet_num']]['image_representant_id'];
					$lien = 'index.php?section=galerie&amp;page=gestion&amp;cat=' . $this->data['objets'][$this->interne['objet_num']]['categorie_id'];
					$nom = strip_tags($this->data['objets'][$this->interne['objet_num']]['categorie_nom']);
					$objet = ($this->data['objets'][$this->interne['objet_num']]['categorie_derniere_modif']) ? 'l\'album' : 'la catégorie' ;
					if ($id) {
						$size = outils::thumb_size('cat', $o, $this->data['representants'][$id]['image_largeur'], $this->data['representants'][$id]['image_hauteur']);
						$img = '../getimg.php?m=1&amp;cat=' . $this->data['representants'][$id]['image_chemin'];
					} else {
						$size[0] = $o;
						$size[1] = $o;
						$type = ($this->data['objets'][$this->interne['objet_num']]['categorie_derniere_modif']) ? 'alb' : 'cat';
						$img = './template/defaut/style/' . $type . '_vide.png';
					}
					$taille = 'width="' . $size[0] . '" height="' . $size[1] . '"';
					printf($s, $lien, $taille, $nom, $objet, $img);
				}
				break;
			case 'tb_img' :
				if ($obj == 'image') {
					$lien = $this->data['objets'][$this->interne['objet_num']]['image_chemin'];
					$lien = (IMG_TEXTE) ? '../getitext.php?i=' . $lien : '../' . GALERIE_ALBUMS . '/' . $lien;
					$size = outils::thumb_size('img', $o, $this->data['objets'][$this->interne['objet_num']]['image_largeur'], $this->data['objets'][$this->interne['objet_num']]['image_hauteur']);
					$taille = 'width="' . $size[0] . '" height="' . $size[1] . '"';
					$nom = strip_tags($this->data['objets'][$this->interne['objet_num']]['image_nom']);
					$img = '../getimg.php?m=1&amp;img=' . $this->data['objets'][$this->interne['objet_num']]['image_chemin'];
					printf($s, $lien, $taille, $nom, $img);
				}
				break;
			case 'tb_onclick' :
				if ($obj == 'categorie') {
					$this->getObjetInfo('tb_cat', '%1$s');
				} else {
					$this->getObjetInfo('tb_img', '%1$s');
				}
				break;
			case 'nom' :
				echo outils::html_specialchars($this->data['objets'][$this->interne['objet_num']][$obj . '_nom']);
				break;
			case 'file_name' :
				if ($this->data['infos']['objet_type'] == 'alb') {
					printf($s, $this->data['objets'][$this->interne['objet_num']][$obj . '_id'], outils::html_specialchars(basename($this->data['objets'][$this->interne['objet_num']]['image_chemin'])));
				}
				break;
			case 'f_type' :
				echo ($this->data['infos']['objet_type'] == 'alb') ? 'img' : 'cat';
				break;
			case 'type' :
				if ($this->data['infos']['objet_type'] == 'alb') {
					echo 'image';
				} else {
					echo ($this->data['objets'][$this->interne['objet_num']][$obj . '_derniere_modif']) ? 'album' : 'catégorie';
				}
				break;
			case 'description' :
				echo outils::html_specialchars($this->data['objets'][$this->interne['objet_num']][$obj . '_description']);
				break;
			case 'thumb' :
				if ($this->data['infos']['objet_type'] == 'cat' && ($this->data['objets'][$this->interne['objet_num']][$obj . '_images'] || $this->data['objets'][$this->interne['objet_num']][$obj . '_images_inactive'])) {
					$cat = (!empty($_REQUEST['cat'])) ? $_REQUEST['cat'] : 1;
					$lien = '<a href="index.php?section=representant&amp;cat=' . $cat . '&amp;str=' . $this->data['infos']['startnum'] . '&amp;obj=' . $this->data['objets'][$this->interne['objet_num']][$obj . '_id'] . '&amp;sub_obj=' . $this->data['objets'][$this->interne['objet_num']][$obj . '_id'] . '">représentant</a>';
					printf($s, $lien);
				}
				break;
			case 'infos' :
				$votes = $this->data['objets'][$this->interne['objet_num']][$obj . '_votes'];
				$votes = ($this->data['infos']['objet_type'] == 'alb') ? $votes : ($votes + $this->data['objets'][$this->interne['objet_num']][$obj . '_votes_inactive']);
				$comments = $this->data['objets'][$this->interne['objet_num']][$obj . '_commentaires'];
				$comments = ($this->data['infos']['objet_type'] == 'alb') ? $comments : ($comments + $this->data['objets'][$this->interne['objet_num']][$obj . '_commentaires_inactive']);
				$type = ($this->data['infos']['objet_type'] == 'alb') ? 'img' : 'cat';
				if ($comments) {
					$comments = ' <a title="Afficher les commentaires" href="index.php?section=commentaires&amp;page=display&amp;' . $type . '=' . $this->data['objets'][$this->interne['objet_num']][$obj . '_id'] . '">' . $comments . '</a>';
				}
				if ($votes) {
					$votes = ' <a title="Afficher les votes" href="index.php?section=votes&amp;' . $type . '=' . $this->data['objets'][$this->interne['objet_num']][$obj . '_id'] . '">' . $votes . '</a>';
				}
				echo '<table class="gal_objet_iv">';
				if ($this->data['infos']['objet_type'] == 'alb') {
					echo '<tr><td class="goivf">Poids</td><td>' . outils::poids($this->data['objets'][$this->interne['objet_num']][$obj . '_poids']) . '</td></tr>';
					echo '<tr><td class="goivf">Dimensions</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_largeur'] . ' X ' . $this->data['objets'][$this->interne['objet_num']][$obj . '_hauteur'] . ' pixels' . '</td></tr>';
					echo '<tr><td class="goivf">Visites</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_hits'] . '</td></tr>';
					echo '<tr><td class="goivf">Commentaires</td><td>' . $comments . '</td></tr>';
					echo '<tr><td class="goivf">Votes</td><td>' . $votes . '</td></tr>';
					if ($votes) {
						echo '<tr><td class="goivf">Note moyenne</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_note'] . '</td></tr>';
					}
				} else {
					echo '<tr><th></th><th class="th">activés</th><th class="th">désactivés</th><th class="th">total</th></tr>';
					echo '<tr><td class="goivf">Poids</td><td>' . outils::poids($this->data['objets'][$this->interne['objet_num']][$obj . '_poids']) . '</td><td>' . outils::poids($this->data['objets'][$this->interne['objet_num']][$obj . '_poids_inactive']) . '</td><td>' . outils::poids($this->data['objets'][$this->interne['objet_num']][$obj . '_poids'] + $this->data['objets'][$this->interne['objet_num']][$obj . '_poids_inactive']) . '</td></tr>';
					echo '<tr><td class="goivf">Images</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_images'] . '</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_images_inactive'] . '</td><td>' . ($this->data['objets'][$this->interne['objet_num']][$obj . '_images'] + $this->data['objets'][$this->interne['objet_num']][$obj . '_images_inactive']) . '</td></tr>';
					echo '<tr><td class="goivf">Visites</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_hits'] . '</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_hits_inactive'] . '</td><td>' . ($this->data['objets'][$this->interne['objet_num']][$obj . '_hits'] + $this->data['objets'][$this->interne['objet_num']][$obj . '_hits_inactive']) . '</td></tr>';
					echo '<tr><td class="goivf">Commentaires</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_commentaires'] . '</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_commentaires_inactive'] . '</td><td>' . $comments . '</td></tr>';
					echo '<tr><td class="goivf">Votes</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_votes'] . '</td><td>' . $this->data['objets'][$this->interne['objet_num']][$obj . '_votes_inactive'] . '</td><td>' . $votes . '</td></tr>';
				}
				echo '</table>';
				if ($this->data['objets'][$this->interne['objet_num']]['user_id'] > 1) {
					$nom = '<a href="?section=utilisateurs&amp;page=modif_user&amp;user=' 
						. $this->data['objets'][$this->interne['objet_num']]['user_id'] . '">' 
						. str_replace('_', ' ', $this->data['objets'][$this->interne['objet_num']]['user_login']) . '</a>';
				} else {
					$nom = str_replace('_', ' ', $this->data['objets'][$this->interne['objet_num']]['user_login']);
				}
				if ($this->data['infos']['objet_type'] == 'alb') {
					echo '<span class="gal_date_c">Ajoutée le ' . outils::ladate($this->data['objets'][$this->interne['objet_num']][$obj . '_date']) . ' par ' . $nom . '</span>';
				} else {
					$e = ($this->data['objets'][$this->interne['objet_num']][$obj . '_derniere_modif']) ? '': 'e';
					echo '<span class="gal_date_c">Créé' . $e . ' le ' . outils::ladate($this->data['objets'][$this->interne['objet_num']][$obj . '_date']) . ' par ' . $nom . '</span>';
				}
				echo '<div class="zero_hits"><input id="reinit_hits_' . $this->data['objets'][$this->interne['objet_num']][$obj . '_id'] . '" name="reinit_hits[' . $this->data['objets'][$this->interne['objet_num']][$obj . '_id'] . ']" type="checkbox" /><label for="reinit_hits_' . $this->data['objets'][$this->interne['objet_num']][$obj . '_id'] . '"> Remettre à zéro le compteur de visites.</label></div>';
				break;
			case 'image_tags' :
				if (isset($this->data['objets'][$this->interne['objet_num']][$obj. '_tags'])) {
					$tags = $this->data['objets'][$this->interne['objet_num']][$obj . '_tags'];
					$tags = preg_replace('`^,(.+),$`', '$1', $tags);
					printf($s, $tags);
				}
				break;
			case 'etat' :
				if ($obj == 'categorie' && ($this->data['objets'][$this->interne['objet_num']][$obj . '_images'] + $this->data['objets'][$this->interne['objet_num']][$obj . '_images_inactive']) == 0) {
					break;
				}
				if ($this->data['objets'][$this->interne['objet_num']][$obj . '_visible']) {
					$etat = 'desactive';
					$mot = 'désactiver';
				} else {
					$etat = 'active';
					$mot = 'activer';
				}
				if ($obj == 'categorie' && $this->data['objets'][$this->interne['objet_num']]['categorie_derniere_modif']) {
					$type = 'album';
				} else {
					$type = $obj;
				}
				$lien = '<a ' . $section . '&amp;type=' . $type . '&amp;' . $etat . '=' . $this->data['objets'][$this->interne['objet_num']][$obj . '_id'] . $this->getVID(2) . '">' . $mot . '</a>';
				printf($s, $lien);
				break;
			case 'delete' :
				if ($obj == 'categorie' && $this->data['objets'][$this->interne['objet_num']]['categorie_derniere_modif']) {
					$type = 'album';
				} else {
					$type = $obj;
				}
				$lien_sup = preg_replace('`^ href="`', '', $section) . '&amp;type=' . $type . '&amp;supprime=' . $this->data['objets'][$this->interne['objet_num']][$obj . '_id'] . $this->getVID(2);
				$lien = '<a href="javascript:confirm_sup_obj(\'' . $lien_sup . '\',\'' . $type . '\');">supprimer</a>';
				printf($s, $lien);
				break;
			case 'deplace_img' :
				if ($this->data['infos']['objet_type'] == 'alb') {
					if (strlen($this->data['nav']['deplace_img']) > 29) {
						printf($s, $this->data['nav']['deplace_img']);
					}
				}
				break;
			case 'deplace_cat_lien' :
				if ($this->data['infos']['objet_type'] == 'cat') {
					printf($s, $this->data['objets'][$this->interne['objet_num']]['categorie_id']);
				}
				break;
			case 'deplace_cat' :
				if ($this->data['infos']['objet_type'] == 'cat') {
					$list = '<select name="vers[' . $this->data['objets'][$this->interne['objet_num']]['categorie_id'] . ']">';
					if ($this->data['infos']['objet_type'] == 'cat' && $this->data['infos']['cat'] > 1) {
						$list .= '<option class="gal_hier_cat" value="1">galerie</option>';
					}
					for ($i = 0; $i < count($this->data['nav']['deplace_cat']); $i++) {
						$path_obj = $this->data['objets'][$this->interne['objet_num']][$obj . '_chemin'];
						$path_cat = $this->data['nav']['deplace_cat'][$i]['chemin'];
						$space = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $this->data['nav']['deplace_cat'][$i]['space']);
						if (!preg_match('`' . $path_obj . '.*`', $path_cat)
						  && $path_cat != dirname($path_obj) . '/') {
							$list .= '<option class="gal_hier_cat" value="' . $this->data['nav']['deplace_cat'][$i]['id'] . '">' . $space . strip_tags($this->data['nav']['deplace_cat'][$i]['nom']) . '</option>';
						}
					}
					$list .= '</select>';
					if (strlen($list) > 38) {
						printf($s, $list, $this->data['objets'][$this->interne['objet_num']]['categorie_id']);
					} else {
						echo ($this->data['objets'][$this->interne['objet_num']]['categorie_derniere_modif']) ? 'L\'album ne peut être déplacé.' : 'La catégorie ne peut être déplacée.';
					}
				}
				break;
			case 'is_inactive' :
				if ($this->data['objets'][$this->interne['objet_num']][$obj . '_visible'] != '1') {
					echo $s;
				}
				break;
			case 'password' :
				if ($obj != 'image') {
					printf($s, $this->data['objets'][$this->interne['objet_num']][$obj . '_id'], preg_replace('`^\d+:(.*)`', '$1', $this->data['objets'][$this->interne['objet_num']][$obj . '_pass']));
				}
				break;
			case 'tags' :
			case 'date_creation' :
				if ($obj == 'image') {
					printf($s, $this->data['objets'][$this->interne['objet_num']][$obj . '_id']);
				}
				break;
		}
	}

	/* Dimensions des vignettes des images */
	function getImgThumbSize($type) {
		if (THUMB_IMG_MODE == 'crop') {
			$w = THUMB_IMG_CROP_WIDTH;
			$h = THUMB_IMG_CROP_HEIGHT;
		} else {
			$w = THUMB_IMG_SIZE;
			$h = THUMB_IMG_SIZE;
		}
		return ${$type};
	}

	/* Nombre d'objets par page */
	function getObjetNb($s = '%s %s %s', $n = 10) {
		for ($i = 1; $i <= $n; $i++) {
			$selected = ($this->data['infos']['nb_limit'] == $i) ? ' selected="selected"' : '';
			printf($s, $i, $selected, $i);
		}
	}

	/* Options de trie des objets */
	function getGalerieSort($s, $o = ' selected="selected"') {
		$liste = $this->data['infos']['liste_trie'];
		foreach ($liste as $k => $v) {
			$selected = ($this->data['infos']['objets_ordre'] == $k) ? $o : '';
			printf($s, $k, $selected, $v);
		}
	}
	function getGalerieSens($s, $o = ' selected="selected"') {
		$sens = array('croissant', 'décroissant');
		$sens_value = array('ASC', 'DESC');
		for ($i = 0; $i < count($sens); $i++) {
			$selected = ($this->data['infos']['objets_sens'] == $sens_value[$i]) ? $o : '';
			printf($s, $sens_value[$i], $selected, $sens[$i]);
		}
	}


	/* Filtre */
	function getGalerieFiltre($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['infos']['galerie_filtre'] == $type) {
			printf($s, $o);
		}
	}

	/* Barre de position. */
	function getGaleriePosition($r = 1, $s = '%s [%s] %s', $o1 = ' / ', $o2 = '|', $o3 = ' - page ') {
		$pos = str_replace('%sep', $o1, $this->data['galerie']['position']);
		if ($this->data['infos']['nb_pages'] > 1) {
			$page = $o3 . $this->data['infos']['page_actuelle'] . $o2 . $this->data['infos']['nb_pages'];
		} else {
			$page = '';
		}
		printf($s, $pos, $this->data['infos']['nb_objets'], $page);
	}

	/* Lien retour */
	function getGalerieRetour($s = '<a id="retour" href="%s">retour</a>') {
		if (isset($this->data['nav']['retour'])) {
			printf($s, $this->data['nav']['retour']);
		}
	}

	/* Affiche une liste déroulante pour un accès direct à une catégorie. */
	function getGalerieHierarchie($s = '%s') {
		if (isset($this->data['nav']['hierarchie'])) {
			printf($s, $this->data['nav']['hierarchie']);
		}
	}

	function getGalerieDeplace($s = '%s') {
		printf($s, $this->data['nav']['deplace']);
	}

	// Renvoi le type de l'objet actuel : catégorie (cat) ou album (alb).
	function getObjetType() {
		return $this->data['infos']['objet_type'];
	}

	function isEXIF() {
		if (function_exists('read_exif_data')) {
			return TRUE;
		}
	}
	function isGD() {
		if (function_exists('imagetypes')) {
			return TRUE;
		}
	}

	/*  Récupère la date de création et génère les listes numériques pour la date de création */
	function getDateCreation($s = '%s') {
		if ($this->data['infos']['objet_type'] != 'alb') {
			return;
		}
		$date_creation = $this->data['objets'][$this->interne['objet_num']]['image_date_creation'];
		if (!preg_match('`^\d{5,10}$`', $date_creation)) {
			$date_creation = '';
		}
		$date = array();
		$date['jour'] = -1;
		$date['mois'] = -1;
		$date['annee'] = -1;
		if ($date_creation) {
			$date_creation = getdate($date_creation);
			$date['jour'] = $date_creation['mday'];
			$date['mois'] = $date_creation['mon'];
			$date['annee'] = $date_creation['year'];
		}

		// Jour.
		$print_date = '<select name="date_creation_jour[' . $this->data['objets'][$this->interne['objet_num']]['image_id'] . ']">';
		$print_date .= '<option value="">---</</option>';
		for ($i = 1; $i <= 31; $i++) {
			$selected = ($i == $date['jour']) ? ' selected="selected"' : '';
			$print_date .= '<option' . $selected . ' value="' . $i . '">' . str_pad($i, 2, 0, STR_PAD_LEFT)  . '</option>';
		}
		$print_date .= "</select>\n";

		// Mois.
		$print_date .= "\t\t\t\t\t\t\t\t" . '<select name="date_creation_mois[' . $this->data['objets'][$this->interne['objet_num']]['image_id'] . ']">';
		$print_date .= '<option value="">---------</</option>';
		for ($i = 1; $i <= 12; $i++) {
			$selected = ($i == $date['mois']) ? ' selected="selected"' : '';
			$print_date .= '<option' . $selected . ' value="' . $i . '">' . strftime('%B', mktime(0, 0, 0, $i, date('j'), date('Y')))  . '</option>';
		}
		$print_date .= "</select>\n";

		// Année.
		$print_date .= "\t\t\t\t\t\t\t\t" . '<select name="date_creation_annee[' . $this->data['objets'][$this->interne['objet_num']]['image_id'] . ']">';
		$print_date .= '<option value="">-----</option>';
		for ($i = 1970; $i <= date('Y'); $i++) {
			$selected = ($i == $date['annee']) ? ' selected="selected"' : '';
			$print_date .= '<option' . $selected . ' value="' . $i . '">' . $i . '</option>';
		}
		$print_date .= "</select>\n";

		$date_exif = $this->data['objets'][$this->interne['objet_num']]['image_exif_datetimeoriginal'];
		if ($date_exif) {
			$print_date .= "\n\t\t\t\t\t\t\t\t" . '<span class="date_exif">Date Exif&nbsp;: ' . outils::ladate($date_exif) . '</span>';
		}

		printf($s, $print_date);
	}



	/*
	 * 
	 * ======================================== GALERIE : REPRESENTANT;
	 *
	*/

	function getVignettes($s) {
		echo '<div id="gal_vignettes"><ul>';
		for ($i = 0; $i < count($this->data['vignettes']); $i++) {
			$thumb = '../getimg.php?m=1&amp;img=' . $this->data['vignettes'][$i]['image_chemin'];
			$lien = 'index.php?section=galerie&amp;page=gestion&amp;cat=' . $_REQUEST['cat'] 
				  . '&amp;startnum=' . $_REQUEST['str'] 
				  . '&amp;obj=' . $_REQUEST['obj'] 
				  . '&amp;new_thumb=' . $this->data['vignettes'][$i]['image_id']
				  . $this->getVID(2);
			$size = 'width="' . $this->getImgThumbSize('w') . '" height="' . $this->getImgThumbSize('h') . '"';
			$alt = $this->data['vignettes'][$i]['image_nom'];
			printf($s, $thumb, $lien, $size, $alt);
		}
		echo '</ul></div>';
	}



	/*
	 * 
	 * ======================================== TAGS;
	 *
	*/

	function getNullTag($s = '') {
		if (!isset($this->data['tags']) || !is_array($this->data['tags'])) {
			echo $s;
		}
	}

	function displayTags() {
		if (isset($this->data['tags']) && is_array($this->data['tags'])) {
			return TRUE;
		}
	}

	function getNextTag() {
		static $f = 1;
		if ($f) {
			$f = 0;
		} else {
			$t = $this->data['tags'];
			$t = end($t);
			$tag = current($this->data['tags']);
			if ($tag['tag_id'] == $t['tag_id']) {
				return FALSE;
			}
			next($this->data['tags']);
		}
		return TRUE;
	}

	function getTag($o) {
		$tag = current($this->data['tags']);
		if ($o == 'id') {
			echo md5($tag['tag_id']);
		} else {
			echo $tag[$o];
		}
	}




	/*
	 * 
	 * ======================================== UTILISATEURS : membres ;
	 *
	*/

	function getMembres($type, $o = '', $o2 = '', $o3 = '') {
		switch ($type) {
			case 'zero' :
				if ($this->data['infos']['nb_objets'] == 0) {
					echo $o;
				}
				break;
			case 'position' :
				$this->data['infos']['nb_objets'];
				$page = '';
				if ($this->data['infos']['nb_pages'] > 1) {
					$page = ' - page ' . $this->data['infos']['page_actuelle'] . '|' . $this->data['infos']['nb_pages'];
				}
				$groupe = '<strong>tous</strong> ';
				if (isset($_REQUEST['groupe'])) {
					if (isset($this->data['groupes'][$_REQUEST['groupe']])) {
						$groupe = '<strong>' . $this->data['groupes'][$_REQUEST['groupe']] . '</strong> ';
					}
				}
				echo $groupe . '[' . $this->data['infos']['nb_objets'] . ']' . $page . '';
				break;
			case 'nb_membres' :
				for ($i = 1; $i <= $o2; $i++) {
					$selected = ($this->data['config']['admin_membres_nb'] == $i) ? ' selected="selected"' : '';
					printf($o, $i, $selected, $i);
				}
				break;
			case 'ordre' :
				if ($this->data['config']['admin_membres_ordre'] == $o) {
					echo ' selected="selected"';
				}
				break;
			case 'sens' :
				if ($this->data['config']['admin_membres_sens'] == $o) {
					echo ' selected="selected"';
				}
				break;
			case 'groupe' :
				echo '<select name="groupe">';
				$selected = ($this->data['infos']['groupe_id'] == 0) ? ' selected="selected"' : '';
				echo '<option value="0"' . $selected . '>* tous</option>';
				foreach ($this->data['groupes'] as $id => $nom) {
					$selected = ($this->data['infos']['groupe_id'] == $id) ? ' selected="selected"' : '';
					echo '<option value="' . $id . '"' . $selected . '>' . $nom . '</option>';
				}
				echo '</select>';
				break;
			case 'search' :
				if ($o == 's_date_creation' || $o == 's_date_derniere') {
					if (isset($_GET['s_date_type'])) {
						if (($o == 's_date_creation' && $_GET['s_date_type'] == 'creation') ||
							($o == 's_date_derniere' && $_GET['s_date_type'] == 'derniere')) {
							echo ' checked="checked"';
						}
					} elseif ($o == 's_date_creation') {
						echo ' checked="checked"';
					}
				} elseif ($o == 's_dnpd' || $o == 's_dnsd') {
					if ((isset($_GET['s']) && isset($_GET[$o]) && $_GET[$o] == $o3)
					 || (!isset($_GET['s']) && $o2)) {
						echo ' selected="selected"';
					}
				} else {
					if ((isset($_GET['s']) && isset($_GET[$o]))
					 || (!isset($_GET['s']) && $o2)) {
						echo ' checked="checked"';
					}
				}
				break;
			case 'search_date_nb' :
				for ($i = 0; $i <= 30; $i++) {
					$selected = '';
					if ((isset($_GET['s']) && isset($_GET[$o2]) && $_GET[$o2] == $i)
					 || (!isset($_GET['s']) && $o3 == $i)) {
						$selected = ' selected="selected"';
					}
					printf($o, $i, $selected);
				}
				break;
			case 'search_result' :
				if (isset($_REQUEST['search'])) {
					if ($this->data['infos']['nb_objets']) {
						printf($o, outils::html_specialchars($_REQUEST['search']));
					} else {
						printf($o2, outils::html_specialchars($_REQUEST['search']));
					}
				}
				break;
			case 'search_params' :
				if (isset($_REQUEST['search'])) {
					$s = ($o2) ? $o2 : '%s';
					if ($o == 'inputs') {
						$params = '<input type="hidden" name="search" value="' . htmlentities($_REQUEST['search']) . '" />';
						foreach ($_REQUEST as $k => $v) {
							if (substr($k, 0, 2) == 's_') {
								$params .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
							}
						}
					}
					if ($o == 'params') {
						$params = htmlspecialchars($this->data['infos']['recherche']);
					}
					printf($s, $params);
				}
				break;
			case 'groupe_param' :
				if (isset($_REQUEST['groupe'])) {
					$s = ($o2) ? $o2 : '%s';
					if ($o == 'inputs') {
						$params = '<input type="hidden" name="groupe" value="' . htmlentities($_REQUEST['groupe']) . '" />';
					}
					if ($o == 'params') {
						$params = '&amp;groupe=' . $_REQUEST['groupe'];
					}
					printf($s, $params);
				}
				break;
		}
	}

	function getNextMember() {
		if (!empty($this->data['membres'])) {
			if (!isset($this->interne['membre_num'])) {
				$this->interne['membre_num'] = 0;
			} else {
				$this->interne['membre_num']++;
			}
			if (isset($this->data['membres'][$this->interne['membre_num']]['user_id'])) {
				return TRUE;
			}
		}
	}

	function getMembre($type, $o = '') {
		switch ($type) {
			case 'nom' :
				$nom = str_replace('_', ' ', $this->data['membres'][$this->interne['membre_num']]['user_login']);
				echo '<a title="Modifier le profil de ce membre" href="index.php?section=utilisateurs&amp;page=modif_user&amp;user=' . $this->data['membres'][$this->interne['membre_num']]['user_id'] . '">'
					. $nom . '</a>';
				break;
			case 'id' :
				echo $this->data['membres'][$this->interne['membre_num']]['user_id'];
				break;
			case 'avatar' :
				if ($this->data['membres'][$this->interne['membre_num']]['user_avatar']) {
					echo '<a title="Modifier le profil de ce membre" href="index.php?section=utilisateurs&amp;page=modif_user&amp;user=' . $this->data['membres'][$this->interne['membre_num']]['user_id'] . '">'
						. '<img src="../membres/avatars/avatar_'
						. $this->data['membres'][$this->interne['membre_num']]['user_login'] . '_thumb.jpg" '
						. 'width="50" height="50" alt="avatar de '
						. $this->data['membres'][$this->interne['membre_num']]['user_login'] . '" /></a>';
				} else {
					echo '<a title="Modifier le profil de ce membre" href="index.php?section=utilisateurs&amp;page=modif_user&amp;user=' . $this->data['membres'][$this->interne['membre_num']]['user_id'] . '">'
					   . '<img src="template/defaut/style/avatar_default.png" '
					   . 'width="50" height="50" alt="pas d\'avatar" /></a>';
				}
				break;
			case 'mail' :
				if ($this->data['membres'][$this->interne['membre_num']]['user_mail']) {
					echo '<a href="mailto:' . htmlentities($this->data['membres'][$this->interne['membre_num']]['user_mail']) . '">courriel</a>';
				}
				break;
			case 'web' :
				if ($this->data['membres'][$this->interne['membre_num']]['user_web']) {
					if ($this->data['membres'][$this->interne['membre_num']]['user_mail']) {
						echo $o;
					}
					echo '<a href="' . htmlentities($this->data['membres'][$this->interne['membre_num']]['user_web']) . '">site Web</a>';
				} else {
					echo '&nbsp;';
				}
				break;
			case 'date_creation' :
				echo outils::ladate($this->data['membres'][$this->interne['membre_num']]['user_date_creation']);
				break;
			case 'date_derniere_visite' :
				echo outils::ladate($this->data['membres'][$this->interne['membre_num']]['user_date_derniere_visite'], '%A %d %B %Y à partir de %H:%M:%S');
				break;
			case 'groupe' :
					$user_id = $this->data['membres'][$this->interne['membre_num']]['user_id'];
					$input = '';
					$groupes = '<select name="membres[' . $user_id . '][groupe]">';
					foreach ($this->data['groupes'] as $groupe_id => $nom) {
						if ($this->data['membres'][$this->interne['membre_num']]['groupe_id'] == $groupe_id) {
							$selected = ' selected="selected"';
							$input = '<input type="hidden" name="membres[' . $user_id . '][groupe_actuel]" value="' . $groupe_id . '" />';
						} else {
							$selected = '';
						}
						$groupes .= '<option value="' . $groupe_id . '"' . $selected . '>' . $nom . '</option>';
					}
					$groupes .= '</select>';
					echo $input . $groupes;
				break;
			case 'ip_creation' :
				echo $this->data['membres'][$this->interne['membre_num']]['user_ip_creation'];
				break;
			case 'ip_derniere_visite' :
				echo $this->data['membres'][$this->interne['membre_num']]['user_ip_derniere_visite'];
				break;
		}
	}




	/*
	 * 
	 * ======================================== UTILISATEURS : modification du profil ;
	 *
	*/

	function getProfil($o, $s = '%s') {
		switch ($o) {
			case 'nom' :
				$user_nom = str_replace('_', ' ', $this->data['user'][0]['user_login']);
				$user_nom = '<a href="../?profil=' . urlencode($this->data['user'][0]['user_login']) . '">' . $user_nom . '</a>';
				printf($s, $user_nom);
				break;
			case 'avatar':
				if ($this->data['user'][0]['user_avatar']) {
					$img_file = '../membres/avatars/avatar_'
							  . $this->data['user'][0]['user_login'] . '.jpg';
					$img_infos = getimagesize($img_file);
					echo '<img src="' . $img_file . '" '
						. $img_infos[3] . ' alt="avatar de '
						. $this->data['user'][0]['user_login'] . '" />';
				} else {
					echo '<img src="template/defaut/style/avatar_default.png" '
					   . 'width="50" height="50" alt="pas d\'avatar" />';
				}
				break;
			case 'courriel':
				if (!empty($this->data['user'][0]['user_mail'])) {
					echo ' value="' . $this->data['user'][0]['user_mail'] . '"';
				}
				break;
			case 'web':
				if (!empty($this->data['user'][0]['user_web'])) {
					echo ' value="' . $this->data['user'][0]['user_web'] . '"';
				}
				break;
			case 'lieu':
				if (!empty($this->data['user'][0]['user_lieu'])) {
					echo ' value="' . $this->data['user'][0]['user_lieu'] . '"';
				}
				break;
			case 'courriel_visible':
				if (!empty($this->data['user'][0]['user_mail_public'])) {
					echo ' checked="checked"';
				}
				break;
			case 'is_newsletter':
				if ($this->data['user'][0]['groupe_newsletter']) {
					return true;
				}
				break;
			case 'newsletter':
				if (!empty($this->data['user'][0]['user_newsletter'])) {
					echo ' checked="checked"';
				}
				break;
			case 'date_inscription' :
				echo outils::ladate($this->data['user'][0]['user_date_creation']);
				break;
			case 'date_derniere_visite' :
				echo outils::ladate($this->data['user'][0]['user_date_derniere_visite'], '%A %d %B %Y à partir de %H:%M:%S');
				break;
			case 'ip_inscription' :
				echo $this->data['user'][0]['user_ip_creation'];
				break;
			case 'ip_derniere_visite' :
				echo $this->data['user'][0]['user_ip_derniere_visite'];
				break;
			case 'nb_commentaires' :
				echo $this->data['user'][0]['nb_comments'];
				break;
			case 'nb_images' :
				echo $this->data['user'][0]['nb_images'];
				break;
			case 'nb_favoris' :
				echo $this->data['user'][0]['nb_favoris'];
				break;
		}
	}




	/*
	 * 
	 * ======================================== UTILISATEURS : groupes ;
	 *
	*/

	function getNextGroup() {
		if (!empty($this->data['groupes'])) {
			if (!isset($this->interne['groupe_num'])) {
				$this->interne['groupe_num'] = 0;
			} else {
				$this->interne['groupe_num']++;
			}
			if (isset($this->data['groupes'][$this->interne['groupe_num']]['groupe_id'])) {
				return TRUE;
			}
		}
	}

	function getGroupe($objet, $s = '%s') {
		switch ($objet) {
			case 'special' :
				if ($this->data['groupes'][$this->interne['groupe_num']]['groupe_id'] < 4) {
					echo $s;
				}
				break;
			case 'nom' :
				printf($s, $this->data['groupes'][$this->interne['groupe_num']]['groupe_nom']);
				break;
			case 'titre' :
				printf($s, $this->data['groupes'][$this->interne['groupe_num']]['groupe_titre']);
				break;
			case 'nb_membres' :
				$groupe_id = $this->data['groupes'][$this->interne['groupe_num']]['groupe_id'];
				if ($groupe_id < 3) {
					$nb_users = '/';
				} else {
					$nb_users = $this->data['groupes'][$this->interne['groupe_num']]['nb_users'];
					if ($nb_users > 0) {
						$pl = ($nb_users > 1) ? 's' : '';
						$nb_users = '<a href="?section=utilisateurs&amp;page=membres&amp;groupe=' . $groupe_id . '">' . $nb_users . ' membre' . $pl . '</a>';
					} else {
						$nb_users = '0 membre';
					}
				}
				printf($s, $nb_users);
				break;
			case 'actions' :
				$groupe_id = $this->data['groupes'][$this->interne['groupe_num']]['groupe_id'];
				$supprimer = '';
				if ($groupe_id > 3) {
					$supprimer = '<a onclick="return confirm_sup_groupe();" href="?section=utilisateurs&amp;page=groupes&amp;supprimer=' . $groupe_id . $this->getVID(2) . '">supprimer</a>';
				}
				$modifier = '<a href="?section=utilisateurs&amp;page=modif_groupe&amp;groupe=' . $groupe_id . '">modifier</a>';
				printf($s, $modifier, $supprimer);
				break;
			case 'date_creation' :
				if ($this->data['groupes'][$this->interne['groupe_num']]['groupe_id'] > 2) {
					$date_creation = outils::ladate($this->data['groupes'][$this->interne['groupe_num']]['groupe_date_creation'], '%d %B %Y');
				} else {
					$date_creation = '/';
				}
				printf($s, $date_creation);
				break;
		}
	}




	/*
	 * 
	 * ======================================== UTILISATEURS : modification de groupes ;
	 *
	*/
	
	function getModifGroupe($p, $s = '%s') {
		switch ($p) {
			case 'nom' :
				printf($s, $this->data['groupe'][0]['groupe_nom']);
				break;
			case 'titre' :
				printf($s, $this->data['groupe'][0]['groupe_titre']);
				break;
			case 'commentaires' :
			case 'votes' :
			case 'perso' :
			case 'recherche_avance' :
			case 'newsletter' :
			case 'upload' :
			case 'upload_create' :
				if ($this->data['groupe'][0]['groupe_' . $p]) {
					echo ' checked="checked"';
				}
				break;
			case 'direct' :
			case 'attente' :
				if ($this->data['groupe'][0]['groupe_upload_mode'] == $p) {
					echo ' checked="checked"';
				}
				break;

			case 'aucun' :
			case 'tous' :
			case 'select' :
				if ($this->data['groupe'][0]['groupe_album_pass_mode'] == $p) {
					echo ' checked="checked"';
				}
				break;
			case 'pass_ver' :
				$pass_dev = array();
				if (!empty($this->data['groupe'][0]['groupe_album_pass'])) {
					$pass_dev = unserialize($this->data['groupe'][0]['groupe_album_pass']);
				}
				if (is_array($this->data['passwords'])) {
					for ($i = 0; $i < count($this->data['passwords']); $i++) {
						if (!in_array($this->data['passwords'][$i]['categorie_pass'], $pass_dev)) {
							echo '<option value="' . $this->data['passwords'][$i]['categorie_pass'] . '">'
							. $this->data['passwords'][$i]['categorie_pass'] . '</option>';
						}
					}
				}
				break;
			case 'pass_dev' :
				if (!empty($this->data['groupe'][0]['groupe_album_pass'])) {
					$passwords = unserialize($this->data['groupe'][0]['groupe_album_pass']);
					for ($i = 0; $i < count($passwords); $i++) {
						echo '<option value="' . $passwords[$i] . '">'
						. $passwords[$i] . '</option>';
					}
				}
				break;
		}
	}



	/*
	 * 
	 * ======================================== UTILISATEURS : images en attente;
	 *
	*/

	function getImagesAtt($type, $o = '', $o2 = '') {
		switch ($type) {
			case 'position' :
				$nb_images = $this->data['infos']['nb_objets'];
				$s = ($nb_images > 1) ? '%s images en attente' : '%s image en attente';
				printf($s, $nb_images);
				break;
			case 'nb_images' :
				for ($i = 1; $i <= $o2; $i++) {
					$selected = ($this->data['config']['admin_imgatt_nb'] == $i) ? ' selected="selected"' : '';
					printf($o, $i, $selected, $i);
				}
				break;
			case 'ordre' :
				if ($this->data['config']['admin_imgatt_ordre'] == $o) {
					echo ' selected="selected"';
				}
				break;
			case 'sens' :
				if ($this->data['config']['admin_imgatt_sens'] == $o) {
					echo ' selected="selected"';
				}
				break;
			case 'noimages' :
				if ($this->data['infos']['nb_objets'] == 0) {
					return true;
				}
				break;
		}
	}
	
	function getNextImageAtt() {
		if (!empty($this->data['images_attente'])) {
			if (!isset($this->interne['imgatt_num'])) {
				$this->interne['imgatt_num'] = 0;
			} else {
				$this->interne['imgatt_num']++;
			}
			if (isset($this->data['images_attente'][$this->interne['imgatt_num']]['img_att_id'])) {
				return TRUE;
			}
		}
	}

	function getImageAtt($type, $s = '%s', $o = '') {
		switch ($type) {
			case 'lien' :
				$session_id = md5($this->data['config']['session_id']);
				$lien = 'image_att.php?sid=' . $session_id
					. '&amp;img=' . $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_fichier'];
				printf($s, $lien);
				break;
			case 'thumb' :
				$session_id = md5($this->data['config']['session_id']);
				$lien = 'image_att.php?sid=' . $session_id
					. '&amp;img=' . $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_fichier'];
				$hauteur = $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_hauteur'];
				$largeur = $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_largeur'];
				list($largeur, $hauteur) = outils::thumb_size('img', $o, $largeur, $hauteur);
				$alt = (!empty($this->data['images_attente'][$this->interne['imgatt_num']]['img_att_nom']))
					 ? $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_nom']
					 : str_replace('_', ' ', $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_fichier']);
				$src = 'image_att.php?sid=' . $session_id
					. '&amp;tb=' . $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_fichier'];
				$thumb = '<a href="' . $lien . '"><img src="' . $src . '" alt="' . $alt  . '" width="' . $largeur  . '" height="' . $hauteur  . '" /></a>';
				printf($s, $thumb);
				break;
			case 'id' :
				printf($s, $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_id']);
				break;
			case 'user_nom' :
				$login = $this->data['images_attente'][$this->interne['imgatt_num']]['user_login'];
				$lien = GALERIE_PATH . '/index.php?profil=' . urlencode($login);
				$nom = str_replace('_', ' ', $login);
				$nom = '<a href="' . $lien . '">' . $nom . '</a>';
				printf($s, $nom);
				break;
			case 'ip' :
				printf($s, $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_ip']);
				break;
			case 'album' :
				$nom = $this->data['images_attente'][$this->interne['imgatt_num']]['categorie_nom'];
				$lien = GALERIE_PATH . '/index.php?alb=' . $this->data['images_attente'][$this->interne['imgatt_num']]['categorie_id'];
				$album = '<a href="' . $lien . '">' . $nom . '</a>';
				printf($s, $album);
				break;
			case 'fichier' :
				printf($s, $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_fichier']);
				break;
			case 'date' :
				$date = outils::ladate($this->data['images_attente'][$this->interne['imgatt_num']]['img_att_date']);
				printf($s, $date);
				break;
			case 'nom' :
				$nom = htmlentities($this->data['images_attente'][$this->interne['imgatt_num']]['img_att_nom']);
				printf($s, $nom);
				break;
			case 'desc' :
				$desc = htmlentities($this->data['images_attente'][$this->interne['imgatt_num']]['img_att_description']);
				printf($s, $desc);
				break;
			case 'type' :
				printf($s, $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_type']);
				break;
			case 'poids' :
				$poids = outils::poids($this->data['images_attente'][$this->interne['imgatt_num']]['img_att_poids']);
				printf($s, $poids);
				break;
			case 'taille' :
				$hauteur = $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_hauteur'];
				$largeur = $this->data['images_attente'][$this->interne['imgatt_num']]['img_att_largeur'];
				$taille = $largeur . ' x ' . $hauteur . ' pixels';
				printf($s, $taille);
				break;
		}
	}



	/*
	 * 
	 * ======================================== VOTES;
	 *
	*/

	function getNullVotes($s = '') {
		if (!isset($this->data['votes']) || !is_array($this->data['votes'])) {
			echo $s;
		}
	}

	function displayVotes() {
		if (isset($this->data['votes']) && is_array($this->data['votes'])) {
			return TRUE;
		}
	}

	function getNextVote() {
		if (!isset($this->data['interne_id'])) {
			$this->data['interne_id'] = -1;
		}
		if (isset($this->data['votes'][$this->data['interne_id']+1])) {
			$this->data['interne_id']++;
			return TRUE;
		}
	}

	function getVotesParams($o = '') {
		if ($o == 'input') {
			foreach ($_GET as $name => $value) {
				if ($name != 'startnum' && $name != 'delete') {
					echo '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
				}
			}
		} else {
			foreach ($_GET as $name => $value) {
				if ($name != 'startnum' && $name != 'delete') {
					echo '&amp;' . $name . '=' . $value;
				}
			}
		}
	}

	function getVote($t, $s = '%s', $o = '') {

		$obj = '';
		if (isset($_GET['cat']) && $_GET['cat'] > 1) {
			$obj = '&amp;cat=' . $_GET['cat'];
		}
		if (isset($_GET['img']) && $_GET['img'] > 1) {
			$obj = '&amp;img=' . $_GET['img'];
		}

		switch ($t) {

			case 'id' :
				printf($s, $this->data['votes'][$this->data['interne_id']]['vote_id']);
				break;

			case 'image' :
				$s = ($s) ? $s : '<a title="%s" href="%s"><img width="%s" height="%s" alt="%s" src="%s" /></a>';

				$alt = htmlentities($this->data['votes'][$this->data['interne_id']]['image_nom']);
				$href = '?section=votes&amp;img=' . $this->data['votes'][$this->data['interne_id']]['image_id'] . $obj;
				$src = '../getimg.php?img=' . $this->data['votes'][$this->data['interne_id']]['image_chemin'];

				// Dimensions de la vignette.
				$size = outils::thumb_size('img', $o, $this->data['votes'][$this->data['interne_id']]['image_largeur'], $this->data['votes'][$this->data['interne_id']]['image_hauteur']);

				printf($s, $alt, $href, $size[0], $size[1], $alt, $src);
				break;

			case 'image_lien' :
				$lien = '?section=votes&amp;img=' . $this->data['votes'][$this->data['interne_id']]['image_id'] . $obj;
				printf($s, $lien);
				break;

			case 'date' :
				$ts = $this->data['votes'][$this->data['interne_id']]['vote_date'];
				$date = outils::ladate($ts, '%d %B %Y à %H:%M');
				$date = '<a href="?section=votes&amp;date=' . $ts . $obj . '">' . $date . '</a>';
				printf($s, $date);
				break;

			case 'album' :
				$album_id = $this->data['votes'][$this->data['interne_id']]['categorie_id'];
				$album_nom = $this->data['votes'][$this->data['interne_id']]['categorie_nom'];
				$album = '<a href="?section=votes&amp;cat=' . $album_id . '">' . $album_nom . '</a>';
				printf($s, $album);
				break;

			case 'ip' :
				$ip = $this->data['votes'][$this->data['interne_id']]['vote_ip'];
				$ip = '<a href="?section=votes&amp;ip=' . $ip . $obj . '">' . $ip . '</a>';
				printf($s, $ip);
				break;

			case 'note' :
				$note = sprintf('%1.0f', $this->data['votes'][$this->data['interne_id']]['vote_note']);
				printf($s, $note);
				break;

			case 'sub' :
				$objet = str_replace('image', 'img', $this->data['infos']['sub_objects']);
				$objet = str_replace('categorie', 'cat', $objet);
				echo $objet;
				break;

		}

	}

	function getVotesPosition($s = '%s%s[%s] %s', $o1 = ' / ', $o2 = '|', $o3 = ' - page ') {

		$pos = str_replace('%sep', $o1, $this->data['votes']['position']);

		if ($this->data['infos']['nb_pages'] > 1) {
			if (!isset($this->data['infos']['page_actuelle'])) {
				header('Location:' . basename($_SERVER['PHP_SELF']) . '?' . preg_replace('`startnum=\d+`', 'startnum=0', $_SERVER['QUERY_STRING']));
			}
			$page = $o3 . $this->data['infos']['page_actuelle'] . $o2 . $this->data['infos']['nb_pages'];

		} else {
			$page = '';
		}
		
		$ipdate = ' ';
		if (isset($_GET['date'])) {
			$ipdate = ' (<a href="?section=votes&amp;date=' . $_GET['date'] . '">' . strftime('%d %B %Y', $_GET['date']) . '</a>) ';
		} elseif (isset($_GET['ip'])) {
			$ipdate = ' (<a href="?section=votes&amp;ip=' . $_GET['ip'] . '">' . $_GET['ip'] . '</a>) ';
		}

		printf($s, $pos, $ipdate, $this->data['infos']['nb_objets'], $page);
	}

	function getVotesNb($s = '%s %s %s', $n = 10) {
		for ($i = 1; $i <= $n; $i++) {
			$selected = ($this->data['infos']['nb_votes'] == $i) ? ' selected="selected"' : '';
			printf($s, $i, $selected, $i);
		}
	}

	function getVoteSortOrdre($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['infos']['vote_sort'] == $type) {
			printf($s, $o);
		}
	}

	function getVoteSortSens($type, $s = '%s', $o = ' selected="selected"') {
		if ($this->data['infos']['vote_sens'] == $type) {
			printf($s, $o);
		}
	}

	function getVoteSubCats($s = '%s %s') {
		$subcats = $this->data['votes']['sub_item'];
		$objet = $this->data['infos']['sub_objects'];
		for ($i = 0; $i < count($subcats); $i++) {
			
			printf($s, $subcats[$i][$objet . '_id'], strip_tags($subcats[$i][$objet . '_nom']));
		}
	}
	
	function getVoteDeleteAll($s = '%s') {
		$title = 'Supprimer tous les votes de ';
		if (isset($_GET['img'])) {
			$obj = 'cette%20image';
			$title .= 'cette image';
		} elseif (isset($_GET['cat']) && $_GET['cat'] > 1) {
			if ($this->data['infos']['obj_type'] == 'alb') {
				$obj = 'cet%20album';
				$title .= 'cet album';
			} else {
				$obj = 'cette%20catégorie';
				$title .= 'cette catégorie';
			}
		} else {
			$obj = 'la%20galerie';
			$title .= 'la galerie';
		}
		if (isset($_GET['ip'])) {
			$obj .= '%20correspondant%20à%20cette%20IP';
			$title .= ' correspondant à cette IP';
		} elseif (isset($_GET['date'])) {
			$obj .= '%20correspondant%20à%20cette%20date';
			$title .= ' correspondant à cette date';
		}
		$lien = '?delete=all';
		foreach ($_GET as $name => $value) {
			if ($name != 'startnum' && $name != 'delete') {
				$lien .= '&amp;' . $name . '=' . $value;
			}
		}
		printf($s, $title, $lien . $this->getVID(2), $obj);
	}



	/*
	 * 
	 * ======================================== CONFIGURATION;
	 *
	*/

	function getConfig($i) {
		switch($i) {
			case 'g_mail' : echo $this->data['config']['admin_mail']; break;

			case 'g_dir_alb' : echo $this->data['config']['galerie_albums_dir']; break;
			case 'g_dir_tb' : echo $this->data['config']['galerie_repertoire_vignettes']; break;
			case 'g_pref_tb' : echo $this->data['config']['galerie_prefixe_vignettes']; break;
			case 'g_size_tb_alb' : echo $this->data['config']['galerie_tb_alb_size']; break;
			case 'g_size_tb_img' : echo $this->data['config']['galerie_tb_img_size']; break;
			
			case 'g_tb_alb_crop_width' : echo $this->data['config']['galerie_tb_alb_crop_width']; break;
			case 'g_tb_alb_crop_height' : echo $this->data['config']['galerie_tb_alb_crop_height']; break;
			case 'g_tb_img_crop_width' : echo $this->data['config']['galerie_tb_img_crop_width']; break;
			case 'g_tb_img_crop_height' : echo $this->data['config']['galerie_tb_img_crop_height']; break;
		}
	}
}
?>
