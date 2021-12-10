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

// On supprime tout paramètre gpc non existant.
$R = array('note', 'img', 'retour', 'styledir');
foreach ($_POST as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_POST[$name]);
	}
}
$R = array();
foreach ($_GET as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_GET[$name]);
	}
}
$R = array('galerie_perso', 'galerie_membre');
foreach ($_COOKIE as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_COOKIE[$name]);
	}
}

if (isset($_POST['note']) && preg_match('`^[1-5]$`', $_POST['note']) &&
    isset($_POST['img']) && preg_match('`^\d{1,12}$`', $_POST['img']) &&
    isset($_POST['retour']) && isset($_POST['styledir'])) {

	$_POST['retour'] = str_replace('!', '%s', $_POST['retour']);

	// Chargement de la config.
	if (file_exists('config/conf.php')) {
		@require_once(dirname(__FILE__) . '/config/conf.php');
	}

	// Connexion MySQL.
	require_once(dirname(__FILE__) . '/includes/classes/class.mysql.php');
	$mysql = new connexion(MYSQL_SERV, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);

	// Récupération des paramètres de configuration.
	$mysql_requete = 'SELECT parametre,valeur FROM ' . MYSQL_PREF . 'config';
	$config = $mysql->select($mysql_requete, 3);
	if (!$config['active_votes']) {
		exit;
	}

	// Identification.
	if ($config['users_membres_active']) {
		require_once(dirname(__FILE__) . '/includes/classes/class.cookie.php');
		$membre = new cookie(31536000, 'galerie_membre', GALERIE_PATH);
		if (($sid = $membre->lire('sid')) !== false) {
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'groupes.groupe_votes
								FROM ' . MYSQL_PREF . 'users JOIN ' . MYSQL_PREF . 'groupes USING (groupe_id)
							   WHERE ' . MYSQL_PREF . 'users.user_session_id = "' . $sid . '"';
			$aut_vote = $mysql->select($mysql_requete);
			if (!is_array($aut_vote)) {
				exit;
			}

		// Droits 'invité'.
		} else {
			$mysql_requete = 'SELECT groupe_votes
								FROM ' . MYSQL_PREF . 'groupes
							   WHERE groupe_id = "2"';
			$aut_vote = $mysql->select($mysql_requete);
			if (!is_array($aut_vote) || $aut_vote[0]['groupe_votes'] != 1) {
				exit;
			}
		}
	}

	$mysql_ok = 1;
	$IP = $_SERVER['REMOTE_ADDR'];

	// On récupère les informations utiles de l'image.
	$mysql_requete = 'SELECT image_id,image_chemin,image_note,image_votes FROM ' . MYSQL_PREF . 'images
		WHERE image_id = "' . $_POST['img'] . '"';
	$image_infos = $mysql->select($mysql_requete);

	// Récupération des valeurs du cookie.
	require_once(dirname(__FILE__) . '/includes/classes/class.cookie.php');
	$cookie = new cookie();

	// On vérifie par cookie si le visiteur n'a pas déjà voté l'image, ainsi que
	// par IP si le visiteur n'a pas déjà voté l'image dans les dernières 48 heures.
	$cookie_vote = $cookie->lire('userid');
	if (preg_match('`^[a-z0-9]{12}$`i', $cookie_vote)) {
		$cookie_requete = 'vote_cookie = "' . $cookie_vote . '" OR ';
	} else {
		$cookie_requete = '';
		$cookie_vote = 0;
	}
	$time_limit = time() - (24 * 3600 * 2);
	$mysql_requete = 'SELECT * FROM ' . MYSQL_PREF . 'votes 
		WHERE image_id = "' . $image_infos[0]['image_id'] . '" 
		AND (' . $cookie_requete . '(vote_date > ' . $time_limit . ' AND vote_ip = "' . $IP . '"))';
	$note = $mysql->select($mysql_requete);

	// On update la note de l'utilisateur...
	if (is_array($note)) {

		// ...si différente !
		if ($note[0]['vote_note'] == $_POST['note']) {
			exit;
		}

		// On update la table des votes.
		$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'votes SET
			vote_note = ' . $_POST['note'] . '
			WHERE vote_id = ' . $note[0]['vote_id'];
		if (!$mysql->requete($mysql_requete)) {
			$mysql_ok = 0;
		}

		// On update la note de l'image.
		if (($image_infos[0]['image_votes']-1) == 0) {
			$ancienne_note = (empty($image_infos[0]['image_votes'])) ? $_POST['note'] : 0;
		} else {
			$ancienne_note = (empty($image_infos[0]['image_votes'])) ? $_POST['note'] : (($image_infos[0]['image_note'] * $image_infos[0]['image_votes']) - $note[0]['vote_note']) / ($image_infos[0]['image_votes']-1);
		}
		$ancien_votes = $image_infos[0]['image_votes']-1;
		$nouvelle_note = (empty($ancien_votes)) ? $_POST['note'] : (($ancienne_note * $ancien_votes) + $_POST['note']) / ($ancien_votes+1);
		$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET
			image_note = ' . $nouvelle_note . '
			WHERE image_id = "' . $_POST['img'] . '"';
		if (!$mysql->requete($mysql_requete)) {
			$mysql_ok = 0;
		}
		$image_nouvelle_note = $nouvelle_note;

		// On update la note moyenne pour les catégories parentes.
		$parent = dirname($image_infos[0]['image_chemin']);
		$cat_where = 'categorie_id = "1"';
		while ($parent != '.') {
			$cat_where .= ' OR categorie_chemin = "' . $parent . '/"';
			$parent = dirname($parent);
		}
		$mysql_requete = 'SELECT categorie_id,categorie_note,categorie_votes FROM ' . MYSQL_PREF . 'categories
			WHERE ' . $cat_where;
		$cat_infos = $mysql->select($mysql_requete);
		for ($i = 0; $i < count($cat_infos); $i++) {
			if (($cat_infos[$i]['categorie_votes']-1) == 0) {
				$ancienne_note = (empty($cat_infos[$i]['categorie_votes'])) ? $_POST['note'] : 0;
			} else {
				$ancienne_note = (empty($cat_infos[$i]['categorie_votes'])) ? $_POST['note'] : (($cat_infos[$i]['categorie_note'] * $cat_infos[$i]['categorie_votes']) - $note[0]['vote_note']) / ($cat_infos[$i]['categorie_votes']-1);
			}
			$ancien_votes = $cat_infos[$i]['categorie_votes']-1;
			$nouvelle_note = (empty($ancien_votes)) ? $_POST['note'] : (($ancienne_note * $ancien_votes) + $_POST['note']) / ($ancien_votes+1);
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET
				categorie_note = ' . $nouvelle_note . '
				WHERE categorie_id = "' . $cat_infos[$i]['categorie_id'] . '"';
			if (!$mysql->requete($mysql_requete)) {
				$mysql_ok = 0;
			}
		}

		// On retourne le résultat.
		if ($mysql_ok) {
			$note_arrondie = sprintf('%1.1f', $image_nouvelle_note);
			$pl = ($image_infos[0]['image_votes'] > 1) ? 's'  : '';
			printf($_POST['retour'], note_star($image_nouvelle_note), $note_arrondie, $image_infos[0]['image_votes'] . ' vote' . $pl);
		} else {
			echo $mysql_ok;
		}

	// On ajoute la note de l'utilisateur.
	} else {

		// S'il n'y a pas d'identifiant dans le cookie, on en génère un nouveau.
		if (!$cookie_vote) {
			$cookie_vote = genKey();
			$cookie->ajouter('userid', $cookie_vote);
			$cookie->ecrire();
		}

		// On insert le nouveau vote dans la table des votes.
		$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'votes (
			image_id,
			vote_date,
			vote_note,
			vote_ip,
			vote_cookie
			) VALUES (
			"' . $image_infos[0]['image_id'] . '",
			"' . time() . '",
			"' . $_POST['note'] . '",
			"' . $IP . '",
			"' . $cookie_vote . '")';
		if (!$mysql->requete($mysql_requete)) {
			$mysql_ok = 0;
		}

		// On update la table des images du nombre de votes
		// et de la moyenne de la note.
		$note = (empty($image_infos[0]['image_votes'])) ? $_POST['note'] : (($image_infos[0]['image_note'] * $image_infos[0]['image_votes']) + $_POST['note']) / ($image_infos[0]['image_votes']+1);
		$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET
			image_note = ' . $note . ',
			image_votes = image_votes + 1
			WHERE image_id = "' . $_POST['img'] . '"';
		if (!$mysql->requete($mysql_requete)) {
			$mysql_ok = 0;
		}

		// On update les catégories parentes du nombre de votes.
		$parent = dirname($image_infos[0]['image_chemin']);
		$cat_where = 'categorie_id = "1"';
		while ($parent != '.') {
			$cat_where .= ' OR categorie_chemin = "' . $parent . '/"';
			$parent = dirname($parent);
		}
		$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET
			categorie_note = ((categorie_note*categorie_votes)+' . $_POST['note'] . ')/(categorie_votes+1),
			categorie_votes = categorie_votes + 1
			WHERE ' . $cat_where;
		if (!$mysql->requete($mysql_requete)) {
			$mysql_ok = 0;
		}

		// On retourne le résultat.
		if ($mysql_ok) {
			$note_arrondie = sprintf('%1.1f', $note);
			$pl = ($image_infos[0]['image_votes'] > 0) ? 's'  : '';
			printf($_POST['retour'], note_star($note), $note_arrondie, $image_infos[0]['image_votes']+1 . ' vote' . $pl);
		} else {
			echo $mysql_ok;
		}
	}

	$mysql->fermer();

} else {
	echo -1;
}



/*
  *	On génère les images en étoiles représentant la note.
*/
function note_star($note) {
	$path = $_POST['styledir'] . '/';
	$star_full = '<img alt="" src="' . $path . 'star_full.png" />';
	$star_demi = '<img alt="" src="' . $path . 'star_demi.png" />';
	$star_empty = '<img alt="" src="' . $path . 'star_empty.png" />';
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
  *	Génère une chaîne aléatoire.
*/
function genKey($longueur = 12) {
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$chars .= '012345678901234567890123456789';
	$chars .= 'abcdefghijklmnopqrstuvwxyz';
	$chars .= '012345678901234567890123456789';
	for ($n = 0, $key = ''; $n < $longueur; $n++) {
		$key .= $chars[mt_rand(0, strlen($chars) - 1)];
	}
	return $key;
}
?>