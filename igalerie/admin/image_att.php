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

require_once(dirname(dirname(__FILE__)) . '/config/conf.php');
require_once(dirname(dirname(__FILE__)) . '/includes/classes/class.files.php');
require_once(dirname(dirname(__FILE__)) . '/includes/classes/class.cookie.php');

// On annule la fonction magic_quotes_gpc.
function strip_magic_quotes(&$valeur) {
	$valeur = stripslashes($valeur);
}
if (get_magic_quotes_gpc()) {
	array_walk($_GET, 'strip_magic_quotes');
	array_walk($_COOKIE, 'strip_magic_quotes');
}

// On supprime tout paramètre non existant.
foreach ($_GET as $name => $value) {
	$R = array('img','tb','sid');
	if (!in_array($name, $R)) {
		unset($_GET[$name]);
	}
}

// Vérification de la session.
if (empty($_GET['sid'])) {
	exit;
}
$session = new cookie(0, 'galerie_sessionid', GALERIE_PATH);
$session_id = $session->lire('session_id');
if (empty($session_id) || md5($session_id) != $_GET['sid']) {
	exit;
}

// Affiche la vignette.
function affiche($tb) {
	files::chmodFile($tb);
	if ($fp = fopen($tb, 'rb')) {
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header('Content-Type: image/jpeg');
		if (!fpassthru($fp)) {
			die('erreur ' . __LINE__ . ' : Impossible d\'ouvrir la vignette.');
		}
	} else {
		die('erreur ' . __LINE__ . ' : Impossible d\'ouvrir la vignette.');
	}
	exit;
}

$dir = './../membres/images/';

// Vignette.
if (isset($_GET['tb'])) {
	if (!preg_match('`^[-_a-z0-9.]{1,250}$`i', $_GET['tb'])) {
		exit;
	}

	$image_file = $dir . $_GET['tb'];
	files::chmodFile($image_file);
	$tb = $dir . '/' . THUMB_TDIR . '/' . THUMB_PREF . basename($image_file);

	// Si la vignette existe, on l'affiche.
	if (file_exists($tb)) {
		affiche($tb);
	}

	// On crée le répertoire de vignettes s'il n'existe pas.
	$thumb_dir = $dir . '/' . THUMB_TDIR . '/';
	if (!is_dir($thumb_dir)) {
		if (!files::createDir($thumb_dir)) {
			die('erreur ' . __LINE__ . ' : Impossible de créer le répertoire des vignettes.');
		}
	}

	// On vérifie GD.
	if (!function_exists('imagetypes')) {
		die('erreur ' . __LINE__ . ' : GD n\'est pas activée.');
	}


	// On récupère l'image et ses paramètres.
	list($img_width, $img_height, $type) = getimagesize($image_file);
	$image = FALSE;
	switch ($type) {
		case 1 :
			if (imagetypes() & IMG_GIF) {
				$image = imagecreatefromgif($image_file);
			} else {
				die('erreur ' . __LINE__ . ' : Type de fichier non pris en charge (GIF).');
			}
			break;
		case 2 :
			if (imagetypes() & IMG_JPG) {
				$image = imagecreatefromjpeg($image_file);
			} else {
				die('erreur ' . __LINE__ . ' : Type de fichier non pris en charge (JPEG).');
			}
			break;
		case 3 :
			if (imagetypes() & IMG_PNG) {
				$image = imagecreatefrompng($image_file);
			} else {
				die('erreur ' . __LINE__ . ' : Type de fichier non pris en charge (PNG).');
			}
			break;
		default :
			die('erreur ' . __LINE__ . ' : Type de fichier non reconnu : ' . $type . '.');	
	}
	if (!$image) {
		die('erreur ' . __LINE__ . ' : imagecreate avec type ' . $type . ' .');
	}


	$tb_height = THUMB_IMG_SIZE;
	$tb_width = $tb_height;

	// Mode de redimensionnement de la vignette.
	if (THUMB_IMG_MODE == 'crop') {

		// On détermine les paramètres de coupe de l'image.
		$width = THUMB_IMG_CROP_WIDTH;
		$height = THUMB_IMG_CROP_HEIGHT;
		$ratio_l = $img_width / $width;
		$ratio_h = $img_height / $height;
		if ($width > $height) {
			$ratio_thumb = $width / $height;
			$ratio_m = 1;
		} else {
			$ratio_thumb = $height / $width;
			$ratio_m = 2;
		}
		if ($ratio_l < $ratio_h) {
			$crop_width = $img_width;
			if ($ratio_m == 1) {
				$crop_height = $img_width / $ratio_thumb;
			} else {
				$crop_height = $img_width * $ratio_thumb;
			}
			$src_x = 0;
			$src_y = ($img_height-$crop_height) / 2;
		} else {
			$crop_height = $img_height;
			if ($ratio_m == 1) {
				$crop_width = $img_height * $ratio_thumb;
			} else {
				$crop_width = $img_height / $ratio_thumb;
			}
			$src_y = 0;
			$src_x = ($img_width-$crop_width) / 2;
		}

		// On redimensionne.
		$image_p = imagecreatetruecolor($width, $height);
		imagecopyresampled($image_p, $image, 0, 0, $src_x, $src_y, $width, $height, $crop_width, $crop_height);

	} else {

		// Taille maximale de la vignette.
		$width = $tb_width;
		$height = $tb_height;
		if ($img_width < $img_height) {
			$width = ($height / $img_height) * $img_width;
		} else {
			$height = ($width / $img_width) * $img_height;
		}

		// On redimensionne.
		$image_p = imagecreatetruecolor($width, $height);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $img_width, $img_height);
	}

	// On crée la vignette.
	files::chmodDir(dirname($tb));
	switch ($type) {
		case 1 :
			if (!imagegif($image_p, $tb)) {
				die('erreur ' . __LINE__ . ' : Impossible de créer la vignette.');
			}
			break;
		case 2 :
			if (!imagejpeg($image_p, $tb, THUMB_QUALITY)) {
				die('erreur ' . __LINE__ . ' : Impossible de créer la vignette.');
			}
			break;
		case 3 :
			if (!imagepng($image_p, $tb)) {
				die('erreur ' . __LINE__ . ' : Impossible de créer la vignette.');
			}
			break;
	}
	imagedestroy($image_p);
	files::chmodFile($tb);

	// On vérifie que la vignette a bien été créée.
	if (file_exists($tb)) {
		affiche($tb);
	} else {
		die('erreur ' . __LINE__ . ' : Impossible de créer la vignette.');
	}

// Image.
} elseif (isset($_GET['img'])) {
	if (!preg_match('`^[-_a-z0-9.]{1,250}$`i', $_GET['img'])) {
		exit;
	}
	$image_file = $dir . $_GET['img'];
	if (file_exists($image_file)) {
		files::chmodFile($image_file);
		if ($fp = fopen($image_file, 'rb')) {
			list($img_width, $img_height, $type) = getimagesize($image_file);
			switch ($type) {
				case 1 :
					$mimetype = 'image/gif';
					break;
				case 3 :
					$mimetype = 'image/png';
					break;
				default :
					$mimetype = 'image/jpeg';
				
			}
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
			header('Content-Type: ' . $mimetype);
			header('Content-disposition: inline; filename="' . basename($image_file));
			if (!fpassthru($fp)) {
				die('erreur ' . __LINE__ . ' : Impossible d\'ouvrir l\'image.');
			}
		} else {
			die('erreur ' . __LINE__ . ' : Impossible d\'ouvrir l\'image.');
		}
		exit;
	}
}
?>