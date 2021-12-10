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
	$R = array('img','cat','m','r','a');
	if (!in_array($name, $R)) {
		unset($_GET[$name]);
	}
}

// Affiche un message indiquant que l'image est manquante.
function manquante() {
	$m_path = (isset($_GET['m'])) 
		? 'admin/template/defaut/style' 
		: 'template/' . GALERIE_THEME . '/style/' . GALERIE_STYLE;
	$manquante_file = $m_path . '/manquante.png';
	files::chmodFile($manquante_file);
	if ($fp = fopen($manquante_file, 'rb')) {
		header('Cache-Control: must-revalidate');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
		header('Content-Type: image/png');
		if (!fpassthru($fp)) {
			die('erreur ' . __LINE__ . ' : Impossible d\'ouvrir la vignette.');
		}
	} else {
		die('erreur ' . __LINE__ . ' : Impossible d\'ouvrir la vignette.');
	}
	exit;
}

// Affiche la vignette.
function affiche($tb, $img_file) {
	if (isset($_GET['img']) && isset($_GET['a'])) {
		$thumb = vignette($img_file, 140);
		header('Cache-Control: must-revalidate');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
		header('Content-Type: image/jpeg');
		imagejpeg($thumb['image'], '', THUMB_QUALITY);
		imagedestroy($thumb['image']);
	} else {
		files::chmodFile($tb);
		header('Cache-Control: must-revalidate');
		header('Expires: '
			. gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
		header('Content-Type: image/jpeg');
		if (!readfile($tb)) {
			header('Content-Type: text/html');
			die('erreur ' . __LINE__ . ' : ' . 'Impossible d\'ouvrir la vignette.');
		}
	}
	exit;
}

// On filtre l'adresse de la vignette.
if (isset($_GET['img'])
 && preg_match('`^[-a-z0-9_/\.]+$`i', $_GET['img'])) {
	$get_file = $_GET['img'];
} elseif (isset($_GET['cat'])
	   && preg_match('`^[-a-z0-9_/.]+$`i', $_GET['cat'])) {
	$get_file = $_GET['cat'];
} else {
	die('erreur ' . __LINE__ . ' : Nom de fichier invalide.');
}

// L'image existe-t-elle ?
$img_file = GALERIE_ALBUMS . '/' . $get_file;
if (!file_exists($img_file)) {
	manquante();
}

// On détermine le chemin de la vignette.
if (isset($_GET['img'])) {
	$tb = GALERIE_ALBUMS . '/' . dirname($get_file) . '/' . 
		  THUMB_TDIR . '/' . 
		  THUMB_PREF . basename($get_file);
} else {
	$tb = './cache/cat_thumb/' . str_replace('/', '_', $get_file);
}

// Si la vignette existe, on l'affiche.
if (file_exists($tb)) {
	affiche($tb, $img_file);

// Sinon, on génère une vignette.
} else {

	// On crée le répertoire de vignettes s'il n'existe pas.
	if (isset($_GET['img'])) {
		$thumb_dir = GALERIE_ALBUMS . '/'
				   . dirname($get_file) . '/'
				   . THUMB_TDIR . '/';
		if (!is_dir($thumb_dir)) {
			if (!files::createDir($thumb_dir)) {
				die('erreur ' . __LINE__ . ' :
					Impossible de créer le répertoire des vignettes.');
			}
		}
	}

	$thumb = vignette($img_file);

	// On crée la vignette.
	files::chmodDir(dirname($tb));
	switch ($thumb['type']) {
		case 1 :
			if (!imagegif($thumb['image'], $tb)) {
				die('erreur ' . __LINE__ . ' :
					 Impossible de créer la vignette.');
			}
			break;
		case 2 :
			if (!imagejpeg($thumb['image'], $tb, THUMB_QUALITY)) {
				die('erreur ' . __LINE__ . ' :
					 Impossible de créer la vignette.');
			}
			break;
		case 3 :
			if (!imagepng($thumb['image'], $tb)) {
				die('erreur ' . __LINE__ . ' :
					 Impossible de créer la vignette.');
			}
			break;
	}
	imagedestroy($thumb['image']);
	files::chmodFile($tb);

	// On vérifie que la vignette a bien été créée.
	if (file_exists($tb)) {
		affiche($tb, $img_file);
	} else {
		die('erreur ' . __LINE__ . ' : Impossible de créer la vignette.');
	}
}

function vignette($img_file, $limit = 0) {

	@ini_set('memory_limit', '128M');

	// On vérifie GD.
	if (!function_exists('imagetypes')) {
		die('erreur ' . __LINE__ . ' : GD n\'est pas activée.');
	}

	// On récupère l'image et ses paramètres.
	list($img_width, $img_height, $type) = getimagesize($img_file);
	$image = FALSE;
	switch ($type) {
		case 1 :
			if (imagetypes() & IMG_GIF) {
				$image = imagecreatefromgif($img_file);
			} else {
				die('erreur ' . __LINE__ . ' :
					 Type de fichier non pris en charge (GIF).');
			}
			break;
		case 2 :
			if (imagetypes() & IMG_JPG) {
				$image = imagecreatefromjpeg($img_file);
			} else {
				die('erreur ' . __LINE__ . ' :
					 Type de fichier non pris en charge (JPEG).');
			}
			break;
		case 3 :
			if (imagetypes() & IMG_PNG) {
				$image = imagecreatefrompng($img_file);
			} else {
				die('erreur ' . __LINE__ . ' :
					 Type de fichier non pris en charge (PNG).');
			}
			break;
		default :
			die('erreur ' . __LINE__ . ' :
				 Type de fichier non reconnu : ' . $type . '.');	
	}
	if (!$image) {
		die('erreur ' . __LINE__ . ' : imagecreate avec type ' . $type . ' .');
	}

	// Vignette image ou vignette catégorie ?
	$tb_mode = (isset($_GET['img'])) ?
		THUMB_IMG_MODE : THUMB_ALB_MODE;
	$tb_crop_width = (isset($_GET['img'])) ?
		THUMB_IMG_CROP_WIDTH : THUMB_ALB_CROP_WIDTH;
	$tb_crop_height = (isset($_GET['img'])) ?
		THUMB_IMG_CROP_HEIGHT : THUMB_ALB_CROP_HEIGHT;
	$tb_height = (isset($_GET['img'])) ?
		THUMB_IMG_SIZE : THUMB_ALB_SIZE;
	$tb_width = $tb_height;

	if ($limit > 0) {
		if ($tb_mode == 'crop') {
			if ($tb_crop_width > $limit
			 || $tb_crop_height > $limit) {
				if ($tb_crop_width > $tb_crop_height) {
					$r = $tb_crop_width / $tb_crop_height;
					$tb_crop_width = $limit;
					$tb_crop_height = $tb_crop_width / $r;
				} else {
					$r = $tb_crop_height / $tb_crop_width;
					$tb_crop_height = $limit;
					$tb_crop_width = $tb_crop_height / $r;
				}
			}
		} else {
			if ($tb_height > $limit) {
				$tb_height = $limit;
				$tb_width = $limit;
			}
		}
	}

	if (isset($_GET['r'])) {
		$tb_height = 500;
		$tb_width = $tb_height;
	}
	// Mode de redimensionnement de la vignette.
	if ($tb_mode == 'crop') {

		// On détermine les paramètres de coupe de l'image.
		$width = $tb_crop_width;
		$height = $tb_crop_height;
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
		if (function_exists('imagesavealpha')) {
			imagesavealpha($image_p, TRUE);
			$trans_colour = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
			imagefill($image_p, 0, 0, $trans_colour);
		}
		imagecopyresampled($image_p, $image, 0, 0, $src_x, $src_y, $width,
						   $height, $crop_width, $crop_height);

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
		if (function_exists('imagesavealpha')) {
			imagesavealpha($image_p, TRUE);
			$trans_colour = imagecolorallocatealpha($image_p, 0, 0, 0, 127);
			imagefill($image_p, 0, 0, $trans_colour);
		}
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height,
						   $img_width, $img_height);
	}

	return array('image' => $image_p, 'type' => $type);
}
?>