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

require_once(dirname(__FILE__) . '/config/conf.php');
require_once(dirname(__FILE__) . '/includes/classes/class.files.php');

// On annule la fonction magic_quotes_gpc.
function strip_magic_quotes(&$valeur) {
	$valeur = stripslashes($valeur);
}
if (get_magic_quotes_gpc()) {
	array_walk($_GET, 'strip_magic_quotes');
}

// On supprime tout paramètre non existant.
foreach ($_GET as $name => $value) {
	$R = array('img');
	if (!in_array($name, $R)) {
		unset($_GET[$name]);
	}
}

// Affiche l'image.
function affiche($i) {
	files::chmodFile($i);
	if (IMG_TEXTE) {
		$image_file = $i;
		require_once(dirname(__FILE__) . '/getitext.php');
	} elseif ($fp = @fopen($i, 'rb')) {
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header('Content-Type: image/jpeg');
		header('Content-disposition: inline; filename="' . preg_replace('`\.\w{3,4}$`', '', basename($i)) . '_resize"');
		if (!fpassthru($fp)) {
			die('erreur ' . __LINE__ . ' : Impossible d\'ouvrir la vignette.');
		}
	} else {
		die('erreur ' . __LINE__ . ' : Impossible d\'ouvrir le fichier.');
	}
	exit;
}

// On filtre l'adresse de l'image.
if (empty($_GET['img']) || !preg_match('`^[-a-z0-9_]+/`i', $_GET['img'])) {
	die('erreur ' . __LINE__ . ' : Nom de fichier invalide.');
}

// L'image existe-t-elle ?
$img_file = $_GET['img'];
if (!file_exists(GALERIE_ALBUMS . '/' . $img_file)) {
	die('erreur ' . __LINE__ . ' : Le fichier demandé n\'existe pas.');
}

// Chemin de l'image intermédiaire.
$image_inter = str_replace('/', '_', $img_file);

// Si l'image intermédiaire existe, on l'affiche.
if (file_exists('cache/' . $image_inter)) {
	affiche('cache/' . $image_inter);

// Sinon, on la génère.
} else {

	@ini_set('memory_limit', '128M');

	// On vérifie GD.
	if (!function_exists('imagetypes')) {
		die('erreur ' . __LINE__ . ' : GD n\'est pas activée.');
	}

	// Taille maximale.
	$img_max_size = preg_split('`x`i', IMG_RESIZE_GD, -1, PREG_SPLIT_NO_EMPTY);
	list($img_l, $img_h, $type) = getimagesize(GALERIE_ALBUMS . '/' . $img_file);
	$ratio_l = $img_l / $img_max_size[0];
	$ratio_h = $img_h / $img_max_size[1];
	if (!empty($img_max_size[0]) && 
		($img_l > $img_max_size[0]) && 
		($ratio_l >= $ratio_h)) {
		$new_size[0] = $img_max_size[0];
		$new_size[1] = round($img_h / $ratio_l);
	}
	if (!empty($img_max_size[1]) && 
		($img_h > $img_max_size[1]) && 
		($ratio_h >= $ratio_l)) {
		$new_size[0] = round($img_l / $ratio_h);
		$new_size[1] = $img_max_size[1];
	}

	// Si c'est nécessaire...
	if (isset($new_size)) {

		// ...on crée l'image intermédiaire.
		$image = 0;
		switch ($type) {
			case 1 :
				if (imagetypes() & IMG_GIF) {
					$image = imagecreatefromgif(GALERIE_ALBUMS . '/' . $img_file);
				}
				break;
			case 2 :
				if (imagetypes() & IMG_JPG) {
					$image = imagecreatefromjpeg(GALERIE_ALBUMS . '/' . $img_file);
				}
				break;
			case 3 :
				if (imagetypes() & IMG_PNG) {
					$image = imagecreatefrompng(GALERIE_ALBUMS . '/' . $img_file);
				}
				break;
			default :
				die('erreur ' . __LINE__ . ' : Type de fichier non reconnu : ' . $type . '.');	
		}
		if (!$image) {
			die('erreur ' . __LINE__ . ' : imagecreate avec type ' . $type . ' .');
		}
		$image_p = imagecreatetruecolor($new_size[0], $new_size[1]);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_size[0], $new_size[1], $img_l, $img_h);

		// On crée l'image.
		files::chmodDir('cache/');
		switch ($type) {
			case 1 : imagegif($image_p, 'cache/' . $image_inter); break;
			case 2 : imagejpeg($image_p, 'cache/' . $image_inter, 80); break;
			case 3 : imagepng($image_p, 'cache/' . $image_inter); break;
		}
		imagedestroy($image_p);
		files::chmodFile('cache/' . $image_inter);

		// On vérifie que l'image a bien été créée.
		if (file_exists('cache/' . $image_inter)) {
			affiche('cache/' . $image_inter);
		} else {
			die('erreur ' . __LINE__ . ' : Impossible de créer la vignette.');
		}

	} else {
		// ...sinon on affiche directement l'image originale.
		affiche(GALERIE_ALBUMS . '/' . $img_file);
	}

}
?>