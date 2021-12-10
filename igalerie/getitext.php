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
@ini_set('memory_limit', '128M');

if (empty($img_correction)) {

	error_reporting(E_ALL);

	@require_once(dirname(__FILE__) . '/config/conf.php');

	// On supprime tout paramètre URL non existant.
	foreach ($_GET as $name => $value) {
		$R = array('i');
		if (!in_array($name, $R)) {
			unset($_GET[$name]);
		}
	}

	if (isset($_GET['i']) && preg_match('`^[-a-z0-9_]+/`i', $_GET['i'])) {
		@require_once(dirname(__FILE__) . '/config/conf.php');
		$image_file = GALERIE_ALBUMS . '/' . $_GET['i'];
	}
	if (!IMG_TEXTE) {
		exit;
	}

	// Paramètres du texte à afficher.
	$it_params = preg_split('` `', IMG_TEXTE_PARAMS, -1, PREG_SPLIT_NO_EMPTY);
}

$it_params[4] = dirname(__FILE__) . '/fontes/' . $it_params[4];
$angle = 0;


require_once(dirname(__FILE__) . '/includes/classes/class.files.php');
files::chmodFile($image_file);

// Création de la nouvelle image.
$ext = preg_replace('`.+\.([a-z]{2,6})$`i', '$1', $image_file);
$mimetype = 'image/jpeg';
switch (strtolower($ext)) {
	case 'png' :
		$image_texte = imagecreatefrompng($image_file);
		$mimetype = 'image/png';
		break;
	case 'gif' :
		$image_texte = imagecreatefromgif($image_file);
		$mimetype = 'image/gif';
		break;
	default :
		$image_texte = imagecreatefromjpeg($image_file);
}

// Si le texte de copyright est vide, on affiche directement l'image.
if ($it_params[0] == '^') {
	if ($fp = fopen($image_file, 'rb')) {
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

$text = str_replace('^', ' ', $it_params[0]);

// Récupération des dimensions du texte.
$box = imagettfbbox($it_params[17], $angle, $it_params[4], $text);
$hauteur = ($box[3] + $it_params[19] + $it_params[20]) - ($box[7] + $it_params[19] - $it_params[20]);

// Doit-on placer le texte à l'extérieur de l'image ?
if ($it_params[21]) {
	$image_dest = imagecreatetruecolor(imagesx($image_texte), imagesy($image_texte) + $hauteur + 1);
	if ($it_params[5] == 'bottom_left' || $it_params[5] == 'bottom_center' || $it_params[5] == 'bottom_right') {
		$plus = 0;
		$it_params[19] = $it_params[19] + 1;
	} else {
		$plus = $hauteur + 1;
	}
	imagecopyresampled($image_dest, $image_texte, 0,  $plus, 0, 0, imagesx($image_texte), imagesy($image_texte) + $plus, imagesx($image_texte), imagesy($image_texte) + $plus);
	$image_texte = $image_dest;
}

// Paramètres de positionnement des éléments.
$largeur = 0;
$bord_y = $it_params[19];
switch ($it_params[5]) {
	case 'bottom_right' :
		$it_params[18] = imagesx($image_texte) - $it_params[18];
		$it_params[19] = imagesy($image_texte) - $it_params[19];
		$largeur = ($box[6] - $box[4]);
		break;
	case 'bottom_left' :
	case 'bottom_center' :
		$it_params[19] = imagesy($image_texte) - $it_params[19];
		break;
	case 'top_right' :
		$it_params[18] = imagesx($image_texte) - $it_params[18];
		$largeur = ($box[6] - $box[4]);
		break;
}
$x1_rectangle = $it_params[18] - $it_params[20] + $largeur;
$y1_rectangle = $box[7] + $it_params[19] - $it_params[20];
$x2_rectangle = $box[2] + $it_params[18] + $it_params[20] + $largeur;
$y2_rectangle = $box[3] + $it_params[19] + $it_params[20];

// Correction de la hauteur du rectangle.
if ($it_params[5] == 'bottom_left' || $it_params[5] == 'bottom_center' || $it_params[5] == 'bottom_right') {
	$y1_rectangle = $y1_rectangle - (($box[3] + $it_params[19]) - imagesy($image_texte)) - $bord_y - $it_params[20];
	$y2_rectangle = $y2_rectangle - (($box[3] + $it_params[19]) - imagesy($image_texte)) - $bord_y - $it_params[20];
} else {
	$y1_rectangle = $it_params[19];
	$y2_rectangle = $hauteur + $it_params[19];
}

// Doit-on élargir le fond sur toute la largeur ?
if ($it_params[11]) {
	$x1_rectangle = 0;
	$x2_rectangle = imagesx($image_texte);

// Doit-on centrer le fond ?
} elseif ($it_params[5] == 'bottom_center' || $it_params[5] == 'top_center') {
	$x1_rectangle = (imagesx($image_texte) - ($box[4] - $box[6]))/2 - $it_params[20];
	$x2_rectangle = ((imagesx($image_texte) - ($box[4] - $box[6]))/2) + ($box[4] - $box[6]) + $it_params[20];
}

// On dessine le rectangle de fond du texte.
if ($it_params[6]) {
	$couleur_fond = imagecolorallocatealpha($image_texte, $it_params[7], $it_params[8], $it_params[9], $it_params[10]);
	imagefilledrectangle($image_texte, $x1_rectangle, $y1_rectangle, $x2_rectangle, $y2_rectangle, $couleur_fond);
}

// Doit-on dessiner une bordure ?
if ($it_params[22]) {
	$couleur_bordure = imagecolorallocatealpha($image_texte, $it_params[23], $it_params[24], $it_params[25], $it_params[10]);

	// Bordure du haut.
	imageline($image_texte, $x1_rectangle, $y1_rectangle, $x2_rectangle, $y1_rectangle, $couleur_bordure);

	// Bordure du bas.
	imageline($image_texte, $x1_rectangle, $y2_rectangle, $x2_rectangle, $y2_rectangle, $couleur_bordure);

	if (!$it_params[11]) {

		// Bordure gauche.
		imageline($image_texte, $x1_rectangle, $y1_rectangle, $x1_rectangle, $y2_rectangle, $couleur_bordure);

		// Bordure droite.
		imageline($image_texte, $x2_rectangle, $y1_rectangle, $x2_rectangle, $y2_rectangle, $couleur_bordure);
	}
}

// Position du texte.
$x1_texte = $it_params[18] + $largeur;
$y1_texte = $it_params[19];
if ($it_params[5] == 'bottom_center' || $it_params[5] == 'top_center') {
	$x1_texte = (imagesx($image_texte) - ($box[4] - $box[6]))/2;
}

// Correction de la hauteur du texte.
if ($it_params[5] == 'bottom_left' || $it_params[5] == 'bottom_center' || $it_params[5] == 'bottom_right') {
	$y1_texte = $y1_texte - (($box[3] + $it_params[19]) - imagesy($image_texte)) - $bord_y - $it_params[20] + $it_params[26];
} else {
	$y1_texte = $y1_rectangle + ($bord_y - ($box[7] + $bord_y - $it_params[20])) + $it_params[26];
}

// On dessine l'ombre du texte.
if ($it_params[12]) {
	$couleur_ombre = imagecolorallocate($image_texte, $it_params[14], $it_params[15], $it_params[16]);
	for ($i = 1; $i <= $it_params[13]; $i++) {
		imagettftext($image_texte, $it_params[17], $angle, $x1_texte + $i, $y1_texte + $i, $couleur_ombre, $it_params[4], $text);
	}
}

// On dessine le texte.
$couleur_texte = imagecolorallocate($image_texte, $it_params[1], $it_params[2], $it_params[3]);
imagettftext($image_texte, $it_params[17], $angle, $x1_texte, $y1_texte, $couleur_texte, $it_params[4], $text);


// On affiche l'image.
if (empty($img_correction)) {
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	#header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	header('Content-type: image/jpeg');
	header('Content-disposition: inline; filename="' . basename($image_file) . '"');
	imagejpeg($image_texte, '', $it_params[27]);
	imagedestroy($image_texte);
}
?>