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
/*
 * ========== class.recherche
 */
class recherche {

	function search($mysql, $tags, $exif, $text_correction, $images_order, $protect_images, $search_cat = 'img', $protect_categories = '') {

		// Extension Exif activée ?
		if ($exif) {
			$exif = (function_exists('read_exif_data')) ? TRUE : FALSE;
		}
		

		// Options de recherche.
		$s_options = recherche::adv_search();
		$s_options['categories'] = ($search_cat) ? $search_cat : $s_options['categories'];

		// Récupération de la requête.
		$search = trim($_GET['search']);
		$search = preg_replace('`-+`', '-', $search);
		$search = str_replace('- ', '', $search);
		$search = str_replace(' *', '', $search);
		$search = preg_replace('`\s+`', ' ', $search);

		// On vérifie s'il y a de la matière.
		if ($search == '') {
			return FALSE;
		}

		// Méthodes « AND » ou « OR ».
		$method = ($s_options['AND']) ? 'AND' : 'OR';

		// Casse.
		$binary = ($s_options['respect_casse']) ? 'BINARY ' : '';

		// Si ni la casse ni les accents comptent, on concertit tout en minuscule.
		if (!$s_options['respect_casse'] && !$s_options['respect_accents']) {
			$search = strtolower($search);
		}

		// Paramètres de recherche dans les tables image et categorie.
		$search = preg_split('`\s+(?![\w\s]*[^-\s]")`i', $search, -1, PREG_SPLIT_NO_EMPTY);
		$champs = array();
		$champs['image']['nom'] = '';
		$champs['image']['chemin'] = '';
		$champs['image']['description'] = '';
		$champs['image']['mots_cles'] = '';
		$champs['image']['exif_make'] = '';
		$champs['image']['exif_model'] = '';
		$champs['categorie']['nom'] = '';
		$champs['categorie']['description'] = '';
		$champs['commentaire']['message'] = '';
		for ($i = 0; $i < count($search); $i++) {

			// Suppression des guillemets double.
			$terme = str_replace('"', '', $search[$i]);

			// Remplacement des espace par une suite de caractères non-alpha-numérique.
			$terme = preg_replace('`[^-\w\*\?\']+`', ' ', $terme);

			// Doit-on ne pas faire de distinction pour les lettres accentuées ?
			$terme = ($s_options['respect_accents']) ? $terme : outils::regexp_accents($terme);

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

			// Champ image : nom.
			if ($s_options['nom']) {
				$champs['image']['nom'] .= $method . ' ' . MYSQL_PREF . 'images.image_nom ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $mysql->lien) . '" ';
			}

			// Champ image : chemin.
			if ($s_options['chemin']) {
				$champs['image']['chemin'] .= $method . ' ' . MYSQL_PREF . 'images.image_chemin ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $mysql->lien) . '" ';
			}

			// Champ image : description.
			if ($s_options['description']) {
				$champs['image']['description'] .= $method . ' ' . MYSQL_PREF . 'images.image_description ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $mysql->lien) . '" ';
			}

			// Champ image : tags.
			if ($s_options['motscles'] && $tags) {
				$champs['image']['mots_cles'] .= $method . ' ' . MYSQL_PREF . 'images.image_tags ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql(recherche::convert_accents_entites($terme, $s_options['respect_accents']), $mysql->lien) . '" ';
			}

			// Champ categorie : nom.
			if ($s_options['nom']) {
				$champs['categorie']['nom'] .= $method . ' ' . MYSQL_PREF . 'categories.categorie_nom ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $mysql->lien) . '" ';
			}

			// Champ categorie : description.
			if ($s_options['description']) {
				$champs['categorie']['description'] .= $method . ' ' . MYSQL_PREF . 'categories.categorie_description ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $mysql->lien) . '" ';
			}

			// Champ commentaire : message.
			if ($s_options['commentaires']) {
				$champs['commentaire']['message'] .= $method . ' ' . MYSQL_PREF . 'commentaires.commentaire_message ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $mysql->lien) . '" ';
			}

			// Champs Exif.
			if ($exif) {

				// Champ image : exif_make.
				if ($s_options['exif_make']) {
					$champs['image']['exif_make'] .= $method . ' ' . MYSQL_PREF . 'images.image_exif_make ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $mysql->lien) . '" ';
				}

				// Champ image : exif_model.
				if ($s_options['exif_model']) {
					$champs['image']['exif_model'] .= $method . ' ' . MYSQL_PREF . 'images.image_exif_model ' . $not . 'REGEXP ' . $binary . '"' . outils::protege_mysql($terme, $mysql->lien) . '" ';
				}
			}
		}

		// Champs de la table image dans lesquels la recherche s'effectuera.
		$image_champs = '';
		foreach ($champs['image'] as $v) {
			if ($v) {
				$image_champs .= 'OR (' . preg_replace('`^(?:AND|OR) `', '', $v) . ') ';
			}
		}
		$image_champs = preg_replace('`^OR `', '', $image_champs);

		// Champs de la table categorie dans lesquels la recherche s'effectuera.
		$categorie_champs = '';
		foreach ($champs['categorie'] as $v) {
			if ($v) {
				$categorie_champs .= 'OR (' . preg_replace('`^(?:AND|OR) `', '', $v) . ') ';
			}
		}
		$categorie_champs = preg_replace('`^OR `', '', $categorie_champs);

		// Champs de la table commentaire dans lesquels la recherche s'effectuera.
		$commentaire_champ = preg_replace('`^(?:AND|OR) `', '', $champs['commentaire']['message']);

		// Limitations de poids des images et catégories.
		$image_poids = '';
		$categorie_poids = '';
		if ($s_options['poids']) {
			$image_poids .= ' AND ' . MYSQL_PREF . 'images.image_poids >= ' . $s_options['poids_start'] 
						  . ' AND ' . MYSQL_PREF . 'images.image_poids <= ' . $s_options['poids_end'];
			$categorie_poids .= ' AND ' . MYSQL_PREF . 'categories.categorie_poids >= ' . $s_options['poids_start'] 
							  . ' AND ' . MYSQL_PREF . 'categories.categorie_poids <= ' . $s_options['poids_end'];
		}

		// Limitation des dimensions des images.
		$image_taille = '';
		if ($s_options['taille']) {
			if ($s_options['taille_width_start'] || $s_options['taille_width_end']) {
				$image_taille .= ' AND ' . MYSQL_PREF . 'images.image_largeur >= ' . $s_options['taille_width_start'] 
							   . ' AND ' . MYSQL_PREF . 'images.image_largeur <= ' . $s_options['taille_width_end'];
				$s_options['categories'] = 'img';
			}
			if ($s_options['taille_height_start'] || $s_options['taille_height_end']) {
				$image_taille .= ' AND ' . MYSQL_PREF . 'images.image_hauteur+' . $text_correction . ' >= ' . $s_options['taille_height_start'] 
							   . ' AND ' . MYSQL_PREF . 'images.image_hauteur+' . $text_correction . ' <= ' . $s_options['taille_height_end'];
				$s_options['categories'] = 'img';
			}
		}

		// Limitation de date des images et catégories.
		$image_date = '';
		$categorie_date = '';
		if ($s_options['date']) {
			$image_date .= ' AND ' . MYSQL_PREF . 'images.image_date' . $s_options['date_type'] . ' >= ' . $s_options['date_start'] 
						 . ' AND ' . MYSQL_PREF . 'images.image_date' . $s_options['date_type'] . ' <= ' . $s_options['date_end'];
			$categorie_date .= ' AND ' . MYSQL_PREF . 'categories.categorie_date >= ' . $s_options['date_start'] 
							 . ' AND ' . MYSQL_PREF . 'categories.categorie_date <= ' . $s_options['date_end'];
		}

		// Albums.
		$image_albums = '';
		$categorie_albums = '';
		if ($s_options['albums'][0] !== 1) {
			$albums = '';
			for ($i = 0; $i < count($s_options['albums']); $i++) {
				$albums .= ' OR ' . MYSQL_PREF . 'categories.categorie_id = "' . $s_options['albums'][$i] . '"';
			}
			$albums = substr($albums, 4);
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'categories.categorie_chemin
								FROM ' . MYSQL_PREF . 'categories 
							   WHERE ' . MYSQL_PREF . 'categories.categorie_visible="1"
								 AND (' . $albums . ')
								 AND ' . MYSQL_PREF . 'categories.categorie_id != "1"';
			$categories_chemin = $this->mysql->select($mysql_requete);
			$image_albums .= ' AND (';
			$categorie_albums .= ' AND (';
			for ($i = 0; $i < count($categories_chemin); $i++) {
				$image_albums .= MYSQL_PREF . 'images.image_chemin LIKE "' . $categories_chemin[$i]['categorie_chemin'] . '%" OR ';
				$categorie_albums .= MYSQL_PREF . 'categories.categorie_chemin LIKE "' . $categories_chemin[$i]['categorie_chemin'] . '_%" OR ';
			}
			$image_albums = preg_replace('` OR $`', '', $image_albums);
			$categorie_albums = preg_replace('` OR $`', '', $categorie_albums);
			$image_albums .= ') ';
			$categorie_albums .= ') ';
		}

		// Recherche dans les commentaires.
		$image_comments = '';
		if ($s_options['commentaires']) {
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'images.image_chemin
								FROM ' . MYSQL_PREF . 'commentaires JOIN ' . MYSQL_PREF . 'images USING (image_id)
							   WHERE ' . MYSQL_PREF . 'commentaires.commentaire_visible = "1"
							     AND ' . $commentaire_champ . $image_albums;
			$comments = $mysql->select($mysql_requete);
			if (is_array($comments)) {
				for ($i = 0; $i < count($comments); $i++) {
					$image_comments .= ' OR image_chemin = "' . $comments[$i]['image_chemin'] . '"';
				}
			}
		}

		// On recherche dans la table des catégories.
		if ($s_options['categories'] == 'cat' && $categorie_champs) {
			$mysql_requete = 'SELECT ' . MYSQL_PREF . 'categories.categorie_id,
									 ' . MYSQL_PREF . 'categories.categorie_chemin,
									 ' . MYSQL_PREF . 'categories.categorie_nom,
									 ' . MYSQL_PREF . 'categories.categorie_derniere_modif
								FROM ' . MYSQL_PREF . 'categories 
							   WHERE (' . $categorie_champs . ') '
									   . $categorie_date
									   . $categorie_poids
									   . $categorie_albums
							 . ' AND ' . MYSQL_PREF . 'categories.categorie_visible="1"
								 AND ' . MYSQL_PREF . 'categories.categorie_id != "1" ' 
									   . $protect_categories
						. 'ORDER BY ' . MYSQL_PREF . 'categories.categorie_derniere_modif DESC';
			$return['categories'] = $mysql->select($mysql_requete);
		}

		// Si la recherche ne porte que sur les commentaires, on adapte la requête.
		$image_champs = ($image_comments && !$image_champs) ? MYSQL_PREF . 'images.image_id = "-1"' : $image_champs;

		// Paramètres de recherche pour la table des images.
		$return['images'] = ' WHERE (' . $image_champs . $image_comments . ') '
				. $protect_images
				. $image_date
				. $image_poids
				. $image_taille
				. $image_albums
				. ' AND ' . MYSQL_PREF . 'images.image_visible="1" 
				ORDER BY ' . $images_order;

		return $return;
	}



	/*
	 *	Paramètres de recherche avancée.
	*/
	function adv_search() {

		// Initialisation des paramètres.
		$s_options = array();
		$s_options['AND'] = 1;
		$s_options['nom'] = 1;
		$s_options['chemin'] = 0;
		$s_options['description'] = 1;
		$s_options['motscles'] = 1;
		$s_options['commentaires'] = 0;
		$s_options['exif_make'] = 0;
		$s_options['exif_model'] = 0;
		$s_options['respect_casse'] = 0;
		$s_options['respect_accents'] = 0;
		$s_options['date'] = 0;
		$s_options['date_type'] = '';
		$s_options['date_start'] = time()-604800;
		$s_options['date_end'] = time();
		$s_options['taille'] = 0;
		$s_options['taille_width_start'] = 0;
		$s_options['taille_width_end'] = 0;
		$s_options['taille_height_start'] = 0;
		$s_options['taille_height_end'] = 0;
		$s_options['poids'] = 0;
		$s_options['poids_start'] = 0;
		$s_options['poids_end'] = 0;
		$s_options['albums'][] = 1;
		$s_options['categories'] = 'cat';

		// Paramètres de la recherche avancée.
		if (!empty($_GET['sadv'])) {
			$s_adv = preg_split('`\.(?=[a-z])`', $_GET['sadv']);
			for ($i = 0; $i < count($s_adv); $i++) {
				switch ($s_adv[$i]{0}) {
					case 'o' :
						if (preg_match('`^o[01]{14}$`', $s_adv[$i])) {
							$s_options['AND'] = $s_adv[$i]{1};
							$s_options['nom'] = $s_adv[$i]{2};
							$s_options['chemin'] = $s_adv[$i]{3};
							$s_options['description'] = $s_adv[$i]{4};
							$s_options['motscles'] = $s_adv[$i]{5};
							$s_options['commentaires'] = $s_adv[$i]{6};
							$s_options['respect_casse'] = $s_adv[$i]{7};
							$s_options['respect_accents'] = $s_adv[$i]{8};
							$s_options['date'] = $s_adv[$i]{9};
							$s_options['date_type'] = ($s_adv[$i]{10}) ? '_creation' : '';
							$s_options['taille'] = $s_adv[$i]{11};
							$s_options['poids'] = $s_adv[$i]{12};
							$s_options['exif_make'] = $s_adv[$i]{13};
							$s_options['exif_model'] = $s_adv[$i]{14};
						}
						break;
					case 'a' :
						if (preg_match('`^a(\d{1,8}-)*\d{1,8}$`', $s_adv[$i])) {
							$s_options['albums'] = preg_split('`-`', substr($s_adv[$i], 1), -1, PREG_SPLIT_NO_EMPTY);
						}
						break;
					case 'd' :
						if (preg_match('`^d\d{1,2}-\d{1,2}-\d{4}-\d{1,2}-\d{1,2}-\d{4}$`', $s_adv[$i])) {
							$s_date = preg_split('`-`', substr($s_adv[$i], 1), -1, PREG_SPLIT_NO_EMPTY);
							if ($s_date_start = @mktime(0, 0, 0, $s_date[1], $s_date[0], $s_date[2])) {
								$s_options['date_start'] = $s_date_start;
							}
							if ($s_date_end = @mktime(23, 59, 59, $s_date[4], $s_date[3], $s_date[5])) {
								$s_options['date_end'] = $s_date_end;
							}
						}
						break;
					case 't' :
						if (preg_match('`^t(\d{0,6}-){3}\d{0,6}$`', $s_adv[$i])) {
							$s_taille = preg_split('`-`', substr($s_adv[$i], 1), -1, PREG_SPLIT_NO_EMPTY);
							if (isset($s_taille[0]) && isset($s_taille[1])) {
								$s_options['taille_width_start'] = $s_taille[0];
								$s_options['taille_width_end'] = $s_taille[1];
							}
							if (isset($s_taille[2]) && isset($s_taille[3])) {
								$s_options['taille_height_start'] = $s_taille[2];
								$s_options['taille_height_end'] = $s_taille[3];
							}
						}
						break;
					case 'p' :
						if (preg_match('`^p\d{1,12}(?:\.\d{1,4})?-\d{1,12}(?:\.\d{1,4})?$`', $s_adv[$i])) {
							$s_poids = preg_split('`-`', substr($s_adv[$i], 1), -1, PREG_SPLIT_NO_EMPTY);
							$s_options['poids_start'] = $s_poids[0];
							$s_options['poids_end'] = $s_poids[1];
						}
						break;
				}
			}
		}
		return $s_options;
	}



	function convert_accents_entites($s, $o) {

		if (!$o) {
			$s = str_replace('[éèeêë]', '(&eacute;|&egrave;|e|&ecirc;|&euml;)', $s);
			$s = str_replace('[aäâàáåã]', '(a|&auml;|&acirc;|&agrave;|&aacute;|&aring;|&atilde;)', $s);
			$s = str_replace('[iïîìí]', '(i|&iuml;|&icirc;|&igrave;|&iacute;)', $s);
			$s = str_replace('[uüûùú]', '(u|&uuml;|&ucirc;|&ugrave;|&uacute;)', $s);
			$s = str_replace('[oöôóòõ]', '(o|&ouml;|&ocirc;|&oacute;|&ograve;|&otilde;)', $s);
			$s = str_replace('[cç]', '(c|&ccedil;)', $s);
			$s = str_replace('[nñ]', '(n|&ntilde;)', $s);
			$s = str_replace('[yÿý]', '(y|&yuml;|&yacute;)', $s);
		}

		return $s;
	}
}

?>