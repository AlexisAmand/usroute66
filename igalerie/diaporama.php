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

// On supprime tout paramètre gpc non existant.
$R = array('actuelle', 'preload', 'firstlast', 'alb', 'cat', 'images', 'hits', 'recentes', 'hits', 'tag', 'votes',
		   'commentaires', 'search', 'sadv', 'date_ajout', 'date_creation', 'startnum', 'galerie_file', 'nb_images',
		   'mimg', 'mfav');
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
$R = array('galerie_perso', 'galerie_pass', 'galerie_membre');
foreach ($_COOKIE as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_COOKIE[$name]);
	}
}

define('GALERIE_FILE', $_GET['galerie_file']);

// Chargement de la config.
if (file_exists('config/conf.php')) {
	@require_once(dirname(__FILE__) . '/config/conf.php');
} else {
	die('erreur ' . __LINE__);
}

require_once(dirname(__FILE__) . '/includes/classes/class.mysql.php');
require_once(dirname(__FILE__) . '/includes/classes/class.cookie.php');
require_once(dirname(__FILE__) . '/includes/classes/class.outils.php');


// Tags.
if (isset($_GET['tag'])) {
	$_GET['tag'] = urldecode($_GET['tag']);
	$diapo = new diapo('tag');
	$diapo->tags();

// Images des membres.
} elseif (isset($_GET['mimg'])) {
	$diapo = new diapo('mimg');
	$diapo->membres_images();

// Favoris des membres.
} elseif (isset($_GET['mfav'])) {
	$diapo = new diapo('mfav');
	$diapo->membres_favoris();

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
	$diapo = new diapo($type);
	$diapo->speciales();

// Recherche.
} elseif (isset($_GET['search'])) {
	$_GET['search'] = urldecode($_GET['search']);
	if (isset($_GET['sadv'])) {
		$_GET['sadv'] = urldecode($_GET['sadv']);
	}
	require_once(dirname(__FILE__) . '/includes/classes/class.recherche.php');
	$diapo = new diapo('search');
	$diapo->search();

// Albums.
} elseif (isset($_GET['alb'])) {
	$diapo = new diapo('alb');
	$diapo->album();

} else {
	die('erreur ' . __LINE__);
}



/*
 * ========== class.diapo
 */
class diapo {

	var $config;	// Configuration de la galerie.
	var $params;	// Paramètres internes.

	// Objets.
	var $prefs;		// Préférences utilisateur.
	var $passwords;	// Mots de passe.
	var $mysql;		// Base de données.


	/*
	 *	Constructeur.
	*/
	function diapo($type) {

		$this->params['objet_type'] = $type;

		if (!isset($_GET['actuelle']) || !preg_match('`^[0-9]{1,12}$`', $_GET['actuelle'])
		 || !isset($_GET['preload']) || !preg_match('`^[2-5]$`', $_GET['preload']) 
		 || !isset($_GET['nb_images']) || !preg_match('`^[0-9]{1,9}$`', $_GET['nb_images']) 
		 || !isset($_GET['firstlast']) || !preg_match('`^[01]$`', $_GET['firstlast']) ) {
			die('erreur ' . __LINE__);
		}

		switch ($type) {

			case 'alb' :
			case 'img' :
			case 'hits' :
			case 'votes' :
			case 'commentaires' :
			case 'recentes' :
			case 'images' :

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
			case 'mimg' :
			case 'mfav' :

				// Page à afficher.
				if (!isset($_GET['startnum']) || !preg_match('`^[1-9]\d{0,9}$`', $_GET['startnum'])) {
					$_GET['startnum'] = 0;
				}
				break;

			default :
				die('erreur ' . __LINE__);
		}

		// Préférences utilisateur.
		$this->prefs = new cookie();

		// Mots de passe.
		$this->passwords = new cookie(31536000, 'galerie_pass');

		// Connexion à la base de données.
		$this->mysql = new connexion(MYSQL_SERV, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);

		// Récupération des paramètres de configuration.
		$mysql_requete = 'SELECT parametre,valeur FROM ' . MYSQL_PREF . 'config';
		$this->config = $this->mysql->select($mysql_requete, 3);
		if (!is_array($this->config)) {
			die('erreur ' . __LINE__);
		}

		$this->membres_connexion();
	}



	/*
	  *	Favoris des membres.
	*/
	function membres_favoris() {

		if (!$this->config['users_membres_active']
		 || !preg_match('`^[-_a-z\d]{1,50}$`i', $_GET['mfav'])) {
			die('erreur ' . __LINE__);
			exit;
		}

		$this->ordre();

		$from = MYSQL_PREF . 'images,'
			  . MYSQL_PREF . 'users,'
			  . MYSQL_PREF . 'favoris';
		$where = MYSQL_PREF . 'users.user_login = "' . $_GET['mfav'] . '"
		 AND ' . MYSQL_PREF . 'favoris.user_id = ' . MYSQL_PREF . 'users.user_id
		 AND ' . MYSQL_PREF . 'favoris.image_id = ' . MYSQL_PREF . 'images.image_id';
		$order = MYSQL_PREF . 'favoris.fav_id DESC';
		$this->recup_images($from, $where, $order);

		// Hierarchie des images actuelle et voisines.
		if (!isset($this->params['images']['actuelle'][0])) {
			die('erreur ' . __LINE__);
		}
		$hierarchie = $this->hierarchie($this->params['images']['actuelle'][0]);
		$this->params['images']['actuelle'][0]['galerie_chemin'] = $hierarchie;
		if ($_GET['preload']) {
			if (isset($this->params['images']['suivantes'])) {
				$this->params['images']['suivantes'][0]['galerie_chemin'] = $this->hierarchie($this->params['images']['suivantes'][0]);
			}
			if (isset($this->params['images']['precedentes'])) {
				$this->params['images']['precedentes'][0]['galerie_chemin'] = $this->hierarchie($this->params['images']['precedentes'][0]);
			}
		}

		// Première et dernière images.
		if ($_GET['firstlast'] == 1) {
			$this->recup_extreme_images($from, $where, $order, 'premiere');
			$this->recup_extreme_images($from, $where, $order, 'derniere');
			$this->params['images']['premiere'][0]['galerie_chemin'] = $hierarchie;
			$this->params['images']['derniere'][0]['galerie_chemin'] = $hierarchie;
		}

		$this->mysql->fermer();
		$this->printXML();
	}



	/*
	  *	Images des membres.
	*/
	function membres_images() {

		if (!$this->config['users_membres_active']
		 || !preg_match('`^[-_a-z\d]{1,50}$`i', $_GET['mimg'])) {
			die('erreur ' . __LINE__);
			exit;
		}

		$this->ordre();

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

		$from = MYSQL_PREF . 'images,'
			  . MYSQL_PREF . 'users';
		$where = MYSQL_PREF . 'users.user_login = "' . $_GET['mimg'] . '"
		 AND ' . MYSQL_PREF . 'images.user_id = ' . MYSQL_PREF . 'users.user_id'
			   . $mysql_date;
		$order = $this->mysql_order();
		$this->recup_images($from, $where, $order);

		// Hierarchie des images actuelle et voisines.
		if (!isset($this->params['images']['actuelle'][0])) {
			die('erreur ' . __LINE__);
		}
		$hierarchie = $this->hierarchie($this->params['images']['actuelle'][0]);
		$this->params['images']['actuelle'][0]['galerie_chemin'] = $hierarchie;
		if ($_GET['preload']) {
			if (isset($this->params['images']['suivantes'])) {
				$this->params['images']['suivantes'][0]['galerie_chemin'] = $this->hierarchie($this->params['images']['suivantes'][0]);
			}
			if (isset($this->params['images']['precedentes'])) {
				$this->params['images']['precedentes'][0]['galerie_chemin'] = $this->hierarchie($this->params['images']['precedentes'][0]);
			}
		}

		// Première et dernière images.
		if ($_GET['firstlast'] == 1) {
			$this->recup_extreme_images($from, $where, $order, 'premiere');
			$this->recup_extreme_images($from, $where, $order, 'derniere');
			$this->params['images']['premiere'][0]['galerie_chemin'] = $hierarchie;
			$this->params['images']['derniere'][0]['galerie_chemin'] = $hierarchie;
		}

		$this->mysql->fermer();
		$this->printXML();
	}



	/*
	  *	Membres : identification.
	*/
	function membres_connexion() {
		if (!$this->config['users_membres_active']) {
			return;
		}
		$session_ok = false;
		$this->membre = new cookie(31536000, 'galerie_membre', GALERIE_PATH);
		if (($sid = $this->membre->lire('sid')) !== false) {
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'groupes.*
								FROM ' . MYSQL_PREF . 'users JOIN ' . MYSQL_PREF . 'groupes USING (groupe_id)
							   WHERE ' . MYSQL_PREF . 'users.user_session_id = "' . $sid . '"';
			$this->template['membre_user'] = $this->mysql->select($mysql_requete);
			if (is_array($this->template['membre_user'])) {
				$session_ok = true;
			}
		}
		if (!$session_ok) {
			$mysql_requete = 'SELECT *
								FROM ' . MYSQL_PREF . 'groupes
							   WHERE groupe_id = "2"';
			$this->template['membre_user'] = $this->mysql->select($mysql_requete);
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
	 *	Recherche.
	*/
	function search() {

		$this->ordre();

		// Image actuelle et voisines.
		$debut = $_GET['startnum'] - $_GET['preload'];
		$nombre = ($_GET['preload']*2) + 1;
		if ($debut < 0) {
			$nombre = $_GET['preload'] + $_GET['startnum'] + 1;
			$debut = 0;
		}
		$limit = $debut . ',' . $nombre;
		$order = $this->mysql_order();
		$this->recupsearch($order, $limit);

		// Hierarchie des images actuelle et voisines.
		if (!isset($this->params['images']['actuelle'][0])) {
			die('erreur ' . __LINE__);
		}
		$this->params['images']['actuelle'][0]['galerie_chemin'] = $this->hierarchie($this->params['images']['actuelle'][0]);
		if ($_GET['preload']) {
			if (isset($this->params['images']['suivantes'])) {
				$this->params['images']['suivantes'][0]['galerie_chemin'] = $this->hierarchie($this->params['images']['suivantes'][0]);
			}
			if (isset($this->params['images']['precedentes'])) {
				$this->params['images']['precedentes'][0]['galerie_chemin'] = $this->hierarchie($this->params['images']['precedentes'][0]);
			}
		}

		// Première et dernière images.
		if ($_GET['firstlast'] == 1) {
			$this->recupsearch($order, '0,1', 'premiere');
			$order = ($this->params['v_sens'] == 'DESC') ? str_replace('DESC', 'ASC', $order) : str_replace('ASC', 'DESC', $order);
			$this->recupsearch($order, '0,1', 'derniere');
			$this->params['images']['premiere'][0]['galerie_chemin'] = $this->hierarchie($this->params['images']['premiere'][0]);
			$this->params['images']['derniere'][0]['galerie_chemin'] = $this->hierarchie($this->params['images']['derniere'][0]);
		}

		$this->mysql->fermer();
		$this->printXML();
	}



	/*
	  *	Effectue la recherche.
	*/
	function recupsearch($order, $limit, $nom = '') {
		$text_correction = $this->config['galerie_images_text_correction'];
		$protect_images = $this->images_protect();
		$recherche = recherche::search($this->mysql, $this->config['active_tags'], $this->config['active_exif'], $text_correction, $order, $this->images_protect());

		// On récupère les informations utiles des images.
		$mysql_requete = 'SELECT image_id,
								 image_chemin,
								 image_nom,
								 image_description,
								 image_date,
								 image_date_creation,
								 image_poids,
								 image_hauteur,
								 image_largeur
							FROM ' . MYSQL_PREF . 'images ' . $recherche['images'] . '
						   LIMIT ' . $limit;
		$images = $this->mysql->select($mysql_requete);
		if (!is_array($images)) {
			die('erreur ' . __LINE__ . "\n\n" . $mysql_requete);
		}

		if ($nom) {
			$this->params['images'][$nom] = $images;
		} else {
			$this->imagesFormat($images);
		}
	}



	/*
	  *	Page des tags.
	*/
	function tags() {

		$this->ordre();

		$type = (isset($_GET['alb'])) ? 'alb' : 'cat';

		// Identifiant de l'objet.
		if (!empty($_GET[$type]) && preg_match('`^[1-9]\d{0,9}$`', $_GET[$type])) {
			$this->params['objet_id'] = $_GET[$type];

		} else {
			$this->params['objet_id'] = 1;
		}

		$this->infos_categorie();

		$tag = htmlentities($_GET['tag']);
		$chemin = $this->params['categorie'][0]['categorie_chemin'];
		$chemin = ($chemin == '.') ? '' : $chemin;
		$from = MYSQL_PREF . 'images,
		    ' . MYSQL_PREF . 'tags';
		$where = MYSQL_PREF . 'tags.tag_id = "' . outils::protege_mysql($tag, $this->mysql->lien) . '"
				 AND ' . MYSQL_PREF . 'images.image_chemin LIKE "' . $chemin . '%" 
				 AND ' . MYSQL_PREF . 'images.image_id = ' . MYSQL_PREF . 'tags.image_id
				 AND ' . MYSQL_PREF . 'images.image_visible="1"' 
					   . $this->images_protect(MYSQL_PREF . 'images.');
		$order = $this->mysql_order();
		$this->recup_images($from, $where, $order);

		// Hierarchie des images actuelle et voisines.
		if (!isset($this->params['images']['actuelle'][0])) {
			die('erreur ' . __LINE__);
		}
		$hierarchie = $this->hierarchie($this->params['images']['actuelle'][0]);
		$this->params['images']['actuelle'][0]['galerie_chemin'] = $hierarchie;
		if ($_GET['preload']) {
			if (isset($this->params['images']['suivantes'])) {
				$hierarchie = ($this->params['categorie'][0]['categorie_derniere_modif'] > 0) ? $hierarchie : $this->hierarchie($this->params['images']['suivantes'][0]);
				$this->params['images']['suivantes'][0]['galerie_chemin'] = $hierarchie;
			}
			if (isset($this->params['images']['precedentes'])) {
				$hierarchie = ($this->params['categorie'][0]['categorie_derniere_modif'] > 0) ? $hierarchie : $this->hierarchie($this->params['images']['precedentes'][0]);
				$this->params['images']['precedentes'][0]['galerie_chemin'] = $hierarchie;
			}
		}

		// Première et dernière images.
		if ($_GET['firstlast'] == 1) {
			$this->recup_extreme_images($from, $where, $order, 'premiere');
			$this->recup_extreme_images($from, $where, $order, 'derniere');
			$hierarchie = ($this->params['categorie'][0]['categorie_derniere_modif'] > 0) ? $hierarchie : $this->hierarchie($this->params['images']['premiere'][0]);
			$this->params['images']['premiere'][0]['galerie_chemin'] = $hierarchie;
			$hierarchie = ($this->params['categorie'][0]['categorie_derniere_modif'] > 0) ? $hierarchie : $this->hierarchie($this->params['images']['derniere'][0]);
			$this->params['images']['derniere'][0]['galerie_chemin'] = $hierarchie;
		}
		
		$this->mysql->fermer();
		$this->printXML();

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
	 *	Pages spéciales : images, hits, votes, commentaires, images récentes...
	*/
	function speciales() {

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

		// On détermine le WHERE et le ORDER BY des requêtes SQL.
		$chemin = $this->params['categorie'][0]['categorie_chemin'];
		$chemin = ($chemin == '.') ? '' : $chemin;
		$date_creation_lenght = ($this->params['v_ordre'] == 'date_creation') ? 'LENGTH(' . MYSQL_PREF . 'images.image_date_creation) ' . $this->params['v_sens'] . ',' : '';
		$order = MYSQL_PREF . 'images.image_';
		$ordre_criteres = MYSQL_PREF . 'images.image_' . $this->params['v_ordre'] . ' ' . $this->params['v_sens'];
		if ($this->params['v_ordre'] == 'votes') {
			$ordre_criteres .= ', ' . MYSQL_PREF . 'images.image_note ' . $this->params['v_sens'];
		} elseif ($this->params['v_ordre'] == 'note') {
			$ordre_criteres .= ', ' . MYSQL_PREF . 'images.image_votes ' . $this->params['v_sens'];
		}
		if (substr($this->params['objet_type'], 0, 4) == 'date') {
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
			$where = MYSQL_PREF . 'images.image_chemin LIKE "' . $chemin . '%" 
				AND ' . MYSQL_PREF . 'images.image_' . $type . ' >= ' . $date_debut . ' 
				AND ' . MYSQL_PREF . 'images.image_' . $type . ' <= ' . $date_fin;
			$order .= $type . ' DESC, ' . $date_creation_lenght . $ordre_criteres;
		} elseif ($this->params['objet_type'] == 'recentes') {
			if ($this->config['display_recentes']) {
				$this->choix['recent'] = $this->config['galerie_recent'];
			} else {
				$this->choix['recent'] = 0;
			}
			if ($this->config['user_perso'] && $this->config['user_recentes']) {
				$valeur_ra = $this->prefs->lire('ra');
				$valeur_rj = $this->prefs->lire('rj');

				if ($valeur_ra !== FALSE) {
					$this->choix['recent'] = ($valeur_ra == 1) ? $valeur_rj : 0;
				}
			}
			$time_limit = time() - ($this->choix['recent'] * 24 * 3600);
			$order .= 'date DESC';
			$where = MYSQL_PREF . 'images.image_chemin LIKE "' . $chemin . '%" 
				AND ' . MYSQL_PREF . 'images.image_date >= ' . $time_limit;
			$order .= ',' . $date_creation_lenght . $ordre_criteres;
		} else {
			$type = ($this->params['objet_type'] == 'images') ? 'date' : $this->params['objet_type'];
			$where = MYSQL_PREF . 'images.image_chemin LIKE "' . $chemin . '%" 
				AND ' . MYSQL_PREF . 'images.image_' . $type . ' > 0';
			$order .= ($type == 'votes') ? 'note DESC, ' . MYSQL_PREF . 'images.image_votes' : $type;
			$order .= ' DESC,' . $date_creation_lenght . $ordre_criteres;
			if ($this->params['objet_type'] == 'images') {
				$order = $date_creation_lenght . $ordre_criteres;
			}
		}
		$order = $order . ', ' . MYSQL_PREF . 'images.image_id ' . $this->params['v_sens'];

		$this->recup_images(MYSQL_PREF . 'images', $where, $order);

		// Hierarchie des images actuelle et voisines.
		if (!isset($this->params['images']['actuelle'][0])) {
			die('erreur ' . __LINE__);
		}
		$hierarchie = $this->hierarchie($this->params['images']['actuelle'][0]);
		$this->params['images']['actuelle'][0]['galerie_chemin'] = $hierarchie;
		if ($_GET['preload']) {
			if (isset($this->params['images']['suivantes'])) {
				$hierarchie = ($this->params['categorie'][0]['categorie_derniere_modif'] > 0) ? $hierarchie : $this->hierarchie($this->params['images']['suivantes'][0]);
				$this->params['images']['suivantes'][0]['galerie_chemin'] = $hierarchie;
			}
			if (isset($this->params['images']['precedentes'])) {
				$hierarchie = ($this->params['categorie'][0]['categorie_derniere_modif'] > 0) ? $hierarchie : $this->hierarchie($this->params['images']['precedentes'][0]);
				$this->params['images']['precedentes'][0]['galerie_chemin'] = $hierarchie;
			}
		}

		// Première et dernière images.
		if ($_GET['firstlast'] == 1) {
			$this->recup_extreme_images(MYSQL_PREF . 'images', $where, $order, 'premiere');
			$this->recup_extreme_images(MYSQL_PREF . 'images', $where, $order, 'derniere');
			$hierarchie = ($this->params['categorie'][0]['categorie_derniere_modif'] > 0) ? $hierarchie : $this->hierarchie($this->params['images']['premiere'][0]);
			$this->params['images']['premiere'][0]['galerie_chemin'] = $hierarchie;
			$hierarchie = ($this->params['categorie'][0]['categorie_derniere_modif'] > 0) ? $hierarchie : $this->hierarchie($this->params['images']['derniere'][0]);
			$this->params['images']['derniere'][0]['galerie_chemin'] = $hierarchie;
		}

		$this->mysql->fermer();
		$this->printXML();
	}



	/*
	  *	Albums.
	*/
	function album() {

		$this->ordre();
		$this->infos_categorie();

		$where = MYSQL_PREF . 'images.image_chemin LIKE "' . $this->params['categorie'][0]['categorie_chemin'] . '%" ';
		$order = $this->mysql_order();
		$this->recup_images(MYSQL_PREF . 'images', $where, $order);

		// Hierarchie des images actuelle et voisines.
		if (!isset($this->params['images']['actuelle'][0])) {
			die('erreur ' . __LINE__);
		}
		$hierarchie = $this->hierarchie($this->params['images']['actuelle'][0]);
		$this->params['images']['actuelle'][0]['galerie_chemin'] = $hierarchie;
		if ($_GET['preload']) {
			if (isset($this->params['images']['suivantes'])) {
				$this->params['images']['suivantes'][0]['galerie_chemin'] = $hierarchie;
			}
			if (isset($this->params['images']['precedentes'])) {
				$this->params['images']['precedentes'][0]['galerie_chemin'] = $hierarchie;
			}
		}

		// Première et dernière images.
		if ($_GET['firstlast'] == 1) {
			$this->recup_extreme_images(MYSQL_PREF . 'images', $where, $order, 'premiere');
			$this->recup_extreme_images(MYSQL_PREF . 'images', $where, $order, 'derniere');
			$this->params['images']['premiere'][0]['galerie_chemin'] = $hierarchie;
			$this->params['images']['derniere'][0]['galerie_chemin'] = $hierarchie;
		}

		$this->mysql->fermer();
		$this->printXML();
	}



	/*
	 *	On récupère les informations de toute la hiérarchie parente.
	*/
	function hierarchie($image) {
		$parent = dirname($image['image_chemin']);
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
			$parents = $this->mysql->select($mysql_requete);
			$hierarchie = '';
			$type = 'cat';
			for ($i = 0; $i < count($parents); $i++) {
				if ($i == count($parents)-1) {
					$type = 'alb';
				}
				$l = outils::genLink('?' . $type . '=' . $parents[$i]['categorie_id'], '', $parents[$i]['categorie_nom']);
				$hierarchie .= '<a href="' . $l . '">' 
							. strip_tags($parents[$i]['categorie_nom']) . '</a> / ';
			}
		}
		return $hierarchie;
	}



	/*
	  *	Récupération des informations des première et dernière images.
	*/
	function recup_extreme_images($from, $where, $order, $nom) {
		$limit = ($nom == 'premiere') ? 0 : $_GET['nb_images']-1;
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_id,
								 ' . MYSQL_PREF . 'images.image_chemin,
								 ' . MYSQL_PREF . 'images.image_nom,
								 ' . MYSQL_PREF . 'images.image_description,
								 ' . MYSQL_PREF . 'images.image_date,
								 ' . MYSQL_PREF . 'images.image_date_creation,
								 ' . MYSQL_PREF . 'images.image_poids,
								 ' . MYSQL_PREF . 'images.image_hauteur,
								 ' . MYSQL_PREF . 'images.image_largeur
							FROM ' . $from . ' 
						   WHERE ' . $where . '
							 AND ' . MYSQL_PREF . 'images.image_visible="1"' 
								   . $this->images_protect(MYSQL_PREF . 'images.') . ' 
						ORDER BY ' . $order . '
						   LIMIT ' . $limit . ',1';
		$image = $this->mysql->select($mysql_requete);
		if (!is_array($image)) {
			die('erreur ' . __LINE__ . "\n\n" . mysql_error());
		}
		$this->params['images'][$nom] = $image;
	}



	/*
	  *	Récupère les informations de l'image actuelle et de ses images voisines.
	*/
	function recup_images($from, $where, $order) {
		$debut = $_GET['startnum'] - $_GET['preload'];
		$nombre = ($_GET['preload']*2) + 1;
		if ($debut < 0) {
			$nombre = $_GET['preload'] + $_GET['startnum'] + 1;
			$debut = 0;
		}
		$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_id,
								 ' . MYSQL_PREF . 'images.image_chemin,
								 ' . MYSQL_PREF . 'images.image_nom,
								 ' . MYSQL_PREF . 'images.image_description,
								 ' . MYSQL_PREF . 'images.image_date,
								 ' . MYSQL_PREF . 'images.image_date_creation,
								 ' . MYSQL_PREF . 'images.image_poids,
								 ' . MYSQL_PREF . 'images.image_hauteur,
								 ' . MYSQL_PREF . 'images.image_largeur
							FROM ' . $from . '
						   WHERE ' . $where . '
							 AND ' . MYSQL_PREF . 'images.image_visible="1"' 
								   . $this->images_protect(MYSQL_PREF . 'images.') . ' 
						ORDER BY ' . $order . ' 
						   LIMIT ' . $debut . ',' . $nombre;
		$images = $this->mysql->select($mysql_requete);
		if (!is_array($images)) {
			die('erreur ' . __LINE__ . "\n\n" . mysql_error());
		}
		$this->imagesFormat($images);
	}



	/*
	  *	On genère un tableau approprié pour les images.
	*/
	function imagesFormat($images) {
		$this->params['images'] = array();
		for ($i = 0, $n = 0, $actuelle = 0; $i < count($images); $i++) {
			if ($images[$i]['image_id'] == $_GET['actuelle']) {
				$this->params['images']['actuelle'][0] = $images[$i];
				$this->params['images']['actuelle'][0]['position'] = $_GET['startnum']+1;
				$actuelle = 1;
				$n = 0;
			} elseif ($actuelle) {
				$this->params['images']['suivantes'][$n] = $images[$i];
				$this->params['images']['suivantes'][$n]['position'] = $_GET['startnum']+$n+2;
				$n++;
			} else {
				$this->params['images']['precedentes'][$n] = $images[$i];
				$n++;
			}
		}
		if (isset($this->params['images']['precedentes'])) {
			$this->params['images']['precedentes'] = array_reverse($this->params['images']['precedentes']);
			for ($i = 0; $i < count($this->params['images']['precedentes']); $i++) {
				$this->params['images']['precedentes'][$i]['position'] = $_GET['startnum']-$i;
			}
		}
	}



	/*
	 *	Tient compte de la protection des images par mot de passe
	 *	lors d'une requête de base de données.
	*/
	function images_protect($plus = '') {
		if (isset($this->template['membre_user'][0]['groupe_album_pass_mode'])) {
			return $this->users_pass();
		}
		$passwords = $this->passwords->valeur;
		$regexp = '';
		if (is_array($passwords)) {
			foreach ($passwords as $k => $v) {
				$pass = outils::decrypte($v, $this->config['galerie_key']);
				if (preg_match('`^\d+:\w+$`', $pass)) {
					$regexp .= $pass . '|';
				}
			}
			if ($regexp) {
				$regexp = ' OR ' . $plus . 'image_pass REGEXP "^' . preg_replace('`\|$`', '', $regexp) . '$"';
			}
		}
		return ' AND (' . $plus . 'image_pass IS NULL' . $regexp . ') ';
	}



	/*
	 *	On récupère les informations de la catégorie actuelle.
	*/
	function infos_categorie() {
		$mysql_requete = 'SELECT categorie_chemin,
								 categorie_derniere_modif,
								 categorie_visible
						   FROM ' . MYSQL_PREF . 'categories
						  WHERE categorie_id = "' . $this->params['objet_id'] . '"
						    AND categorie_visible = "1"';
		$this->params['categorie'] = $this->mysql->select($mysql_requete);
		if (!is_array($this->params['categorie'])) {
			die('erreur ' . __LINE__);
		}
	}



	/*
	 *	On détermine l'ordre et le sens dans lequel
	 *	vont être afficher les images.
	*/
	function ordre() {
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
		} else {
			$this->params['v_ordre'] = $this->config['vignettes_ordre'];
			$this->params['v_sens']  = $this->config['vignettes_sens'];
		}
	}



	/*
	  *	Redimmensionnement des images.
	*/
	function imageSize($i, $w) {

		if ($this->config['galerie_diaporama_resize']) {

			$img_max_size = preg_split('`x`i', IMG_RESIZE_GD, -1, PREG_SPLIT_NO_EMPTY);
			$img_l = $i['image_largeur'];
			$img_h = $i['image_hauteur'];
			$ratio_l = $img_l / $img_max_size[0];
			$ratio_h = $img_h / $img_max_size[1];

			if (!empty($img_max_size[0]) && 
				($img_l > $img_max_size[0]) && 
				($ratio_l >= $ratio_h)) {
				return ($w == 'image_largeur') ? $img_max_size[0] 
					 : (round($img_h / $ratio_l)+$this->config['galerie_images_text_correction']);
			}

			if (!empty($img_max_size[1]) && 
				($img_h > $img_max_size[1]) && 
				($ratio_h >= $ratio_l)) {
				return ($w == 'image_largeur') ? round($img_l / $ratio_h) 
					 : ($img_max_size[1]+$this->config['galerie_images_text_correction']);
			}
		}

		return $i[$w];
	}



	/*
	  *	Envoi les données au format XML.
	*/
	function printXML() {

		$accueil = '<a href="' . outils::genLink('?cat=1') . '">Accueil</a> / ';
		if ($this->config['galerie_diaporama_resize']) {
			$image_text = 'getinter.php?img=';
		} else {
			$image_text = (IMG_TEXTE) ? 'getitext.php?i=' : GALERIE_ALBUMS . '/';
		}

		header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="ISO-8859-15"?>' . "\n";
		echo '<images>' . "\n";
		if (isset($this->params['images']['premiere'])) {
			$this->printXMLImage('premiere', 'premiere', 0, $accueil, $image_text);
		}
		if (isset($this->params['images']['precedentes'])) {
			for ($i = 0; $i < count($this->params['images']['precedentes']); $i++) {
				$this->printXMLImage('precedente', 'precedentes', $i, $accueil, $image_text);
			}
		}
		if (isset($this->params['images']['actuelle'])) {
			$this->printXMLImage('actuelle', 'actuelle', 0, $accueil, $image_text);
		}
		if (isset($this->params['images']['suivantes'])) {
			for ($i = 0; $i < count($this->params['images']['suivantes']); $i++) {
				$this->printXMLImage('suivante', 'suivantes', $i, $accueil, $image_text);
			}
		}
		if (isset($this->params['images']['derniere'])) {
			$this->printXMLImage('derniere', 'derniere', 0, $accueil, $image_text);
		}
		echo '</images>';
	}
	function printXMLImage($s, $o, $i, $accueil, $image_text) {

		// Description.
		$image_description = '<span>Description</span><p id="diapo_desc">%s</p><br />';
		$image_description = (!empty($this->params['images'][$o][$i]['image_description']))
			? htmlspecialchars(sprintf($image_description, nl2br($this->params['images'][$o][$i]['image_description'])))
			: 0;

		// Informations image.
		$image_infos = '<span>Image</span>';
		$image_infos .= '<table>
			<tr><td>nom</td><td>%s</td></tr>
			<tr><td>fichier</td><td><a href="%s">%s</a></td></tr>
			<tr><td>poids</td><td>%s</td></tr>
			<tr><td>dimensions</td><td>%s x %s pixels</td></tr>
			<tr><td>ajoutée le</td><td>%s</td></tr>
			<tr><td>créée le</td><td>%s</td></tr>
		</table>';
		$nom = htmlspecialchars(strip_tags($this->params['images'][$o][$i]['image_nom']));
		$path = GALERIE_PATH . '/' . $image_text . $this->params['images'][$o][$i]['image_chemin'];
		$file = wordwrap(basename($this->params['images'][$o][$i]['image_chemin']), 40, ' ', 1);
		$poids = outils::poids($this->params['images'][$o][$i]['image_poids']);
		$largeur = $this->params['images'][$o][$i]['image_largeur'];
		$hauteur = $this->params['images'][$o][$i]['image_hauteur'];
		$date = outils::ladate($this->params['images'][$o][$i]['image_date'], $this->config['galerie_im_date_format']);
		$date_creation = ($this->params['images'][$o][$i]['image_date_creation']) ? outils::ladate($this->params['images'][$o][$i]['image_date_creation'], $this->config['galerie_im_date_format']) : '/';

		$meta_path = GALERIE_ALBUMS . '/' . $this->params['images'][$o][$i]['image_chemin'];

		// Informations EXIF.
		$infos_exif = 0;
		$image_exif = '<br /><span>Exif</span><table>';
		if ($this->config['active_exif'] && function_exists('read_exif_data')) {
			$active_exif = true;
			if (strtolower(substr($meta_path, -4)) == '.jpg' || strtolower(substr($meta_path, -5)) == '.jpeg') {
				$exif = @read_exif_data($meta_path, 'ANY_TAG', true, false);
				if ($exif) {
					foreach (unserialize($this->config['galerie_exif_params']) as $section => $tags) {
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
										$image_exif .= '<tr><td>' . $params['desc'] . '</td><td>' . htmlspecialchars($temp) . '</td></tr>';
										$infos_exif = 1;
										$temp = '';
									}
								}
							}
						}
					}
				}
			}
		} else {
			$active_exif = false;
		}
		$image_exif .= '</table>';
		if ($infos_exif === 0) {
			$image_exif = ($active_exif) ? '<br /><span>Exif</span><p>Aucune information disponible.</p>' : 0;
		}

		// Informations IPTC.
		$infos_iptc = 0;
		$image_iptc = '<br /><span>IPTC</span><table>';
		$iptc_champs = unserialize($this->config['galerie_iptc_params']);
		if ($this->config['active_iptc']) {
			$active_iptc = true;
			$size = @getimagesize($meta_path, $info);
			if (is_array($info)) {
				$data = @iptcparse($info['APP13']);
				if (is_array($data)) {
					foreach ($data as $k => $v) {
						if (isset($iptc_champs[$k]) && $iptc_champs[$k]['active']) {
							$d = implode(', ', $v);
							$d = trim($d);
							if (!empty($d)) {
								$image_iptc .= '<tr><td>' . $iptc_champs[$k]['nom'] . '</td><td>' . htmlspecialchars($d) . '</td></tr>';
								$infos_iptc = 1;
							}
						}
					}
				}
			}
		} else {
			$active_iptc = false;
		}
		$image_iptc .= '</table>';
		if ($infos_iptc === 0) {
			$image_iptc = ($active_iptc) ? '<br /><span>IPTC</span><p>Aucune information disponible.</p>' : 0;
		}

		// XML.
		echo '<' . $s . '>' . "\n";
		echo '<id>' . $this->params['images'][$o][$i]['image_id'] . '</id>' . "\n";
		echo '<lien>' . outils::genLink('?img=' . $this->params['images'][$o][$i]['image_id'], $this->params['images'][$o][$i]['image_nom']) . '</lien>' . "\n";
		echo '<nom>' . htmlspecialchars($this->params['images'][$o][$i]['image_nom']) . '</nom>' . "\n";
		echo '<description>' . $image_description  . '</description>' . "\n";
		echo '<infos>' . htmlspecialchars(sprintf($image_infos, $nom, $path, $file, $poids, $largeur, $hauteur, $date, $date_creation)) . '</infos>' . "\n";
		echo '<exif>' . htmlspecialchars($image_exif) . '</exif>' . "\n";
		echo '<iptc>' . htmlspecialchars($image_iptc) . '</iptc>' . "\n";
		echo '<chemin>' . $image_text . $this->params['images'][$o][$i]['image_chemin'] . '</chemin>' . "\n";
		echo '<largeur>' . $this->imageSize($this->params['images'][$o][$i], 'image_largeur') . '</largeur>' . "\n";
		echo '<hauteur>' . $this->imageSize($this->params['images'][$o][$i], 'image_hauteur') . '</hauteur>' . "\n";
		echo '<position_chemin>' . htmlspecialchars($accueil . $this->params['images'][$o][$i]['galerie_chemin']) . '</position_chemin>' . "\n";
		echo '</' . $s . '>' . "\n";
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
		if (preg_match('`^[-0-9/+\*]{1,30}$`', $value)) {
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
}
