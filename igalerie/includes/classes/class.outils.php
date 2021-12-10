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

/*
 * ========== class.outils
*/

class outils {

	/*
	 *	Conversion d'URL en lien.
	*/
	function replace_url($text, $limit = 0) {
		$text = preg_replace('`(' . outils::http_url() . ')`i', '<a class="co_link" href="$1">$1</a>', $text);
		if ($limit) {
			$text = preg_replace('`(<a[^>]+>)(.{' . $limit . '}).*(</a>)`', '$1$2...$3', $text);
		}
		return $text;
	}



	/*
	 *	Masque pour les URL Web.
	 *	$ccTLD = 244 pays et territoires dont .eu
	*/
	function http_url($p = 'URL') {

		$protocole = 'https?://';
		$ipv4      = '(?:(?:(?:0?[0-9]?\d|1\d{2}|2[0-4]\d|25[0-5])\.){3}(?:0?[0-9]?\d|1\d{2}|2[0-4]\d|25[0-5]))';
		$ipv6      = '(?:(?:[\da-f]{0,4}:){7}[\da-f]{0,4})';
		$IP        = '(?:' . $ipv4 . '|' . $ipv6 . ')';
		$user_pass = '(?:[-\w]+:[-\w]+@)?';
		$domaine   = '(?:[a-z\d][-a-z\d]{0,62}(?<!-)\.)+';
		$gTLD      = 'aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|net|org|pro|travel';
		$ccTLD     = 'a[cdefgilmnoqrstuwz]|b[abdefghijmnorstvwyz]|c[acdfghiklmnoruvxyz]|d[ejkmoz]|e[cegrstu]|f[ijkmor]|g[adefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcikrstuvy]|m[acdghklmnopqrstuvwxyz]|n[acefgilopruz]|p[aefghklmnrstwy]|r[eouw]|s[abcdeghijklmnortuvyz]|t[cdfghjkmnoprtvwz]|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw]|qa|om';
		$TLD       = '(?:' . $gTLD . '|' . $ccTLD . ')';
		$port      = '(?::(?:6553[0-5]|655[0-2]\d|65[0-4]\d\d|6[0-4]\d{3}|[1-5]\d{4}|[1-9]\d{1,3}|\d))?';
		$chemin    = '(?:/[-@&=+?%~./,;*a-z\d_#]*)?';
		$URL       = $protocole . $user_pass . '(?:' . $IP . '|(?:' . $domaine . $TLD . ')(?<![^/@]{256}))' . $port . $chemin;

		return ${$p};
	}



	/*
	 *	Masque pour les adresses e-mail.
	*/
	function email_address($limit = 255) {
		return '(?!.{' . ($limit+1) . '})(?:[-a-z\d!#$%&\'*+/=?^_\`{|}~]+\.?)+(?<!\.)(?<!.{65})@(?!.{256})' 
			   . outils::http_url('domaine') . outils::http_url('TLD');
	}



	/*
	  *	Envoi un mail.
	*/
	function send_mail($to, $subject, $message, $from, $bcc = '') {

		$from = trim(preg_replace('`[\t\r\n\x5c]+`', '', $from));
		if (!$from || strlen($from) > 250) {
			$from = 'iGalerie';
		}
		$to = trim(preg_replace('`[\t\r\n\x5c]+`', '', $to));
		$bcc = trim(preg_replace('`[\t\r\n\x5c]+`', '', $bcc));
		$subject = trim(preg_replace('`[\t\r\n\x5c]+`', '', $subject));

		$message = wordwrap($message, 70);

		$headers = 'From: ' . $from . "\r\n";
		if ($bcc) {
			$headers .= 'Bcc: ' . $bcc . "\r\n";
		}
		$headers .= 'Date: ' . date('r') . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-transfer-encoding: 8bit' . "\r\n";
		$headers .= 'Content-type: text/plain; charset=ISO-8859-15' . "\r\n";
		$headers .= 'X-Mailer: iGalerie/' . $_SERVER['HTTP_HOST'];

		return @mail($to, $subject, $message, $headers);
	}



	/*
	 *	Permet de matcher n'importe quel caractère identique avec ou sans accent.
	*/
	function regexp_accents($text) {

		if (empty($_GET['s_accents'])) {
			$text = preg_replace('`[éèeêë]`', '[éèeêë]', $text);
			$text = preg_replace('`[aäâàáåã]`', '[aäâàáåã]', $text);
			$text = preg_replace('`[iïîìí]`', '[iïîìí]', $text);
			$text = preg_replace('`[uüûùú]`', '[uüûùú]', $text);
			$text = preg_replace('`[oöôóòõ]`', '[oöôóòõ]', $text);
			$text = preg_replace('`[cç]`', '[cç]', $text);
			$text = preg_replace('`[nñ]`', '[nñ]', $text);
			$text = preg_replace('`[yÿý]`', '[yÿý]', $text);
		}

		return $text;
	}



	/*
	 *	Renvoie le nombre de secondes à partir
	 *	d'un chiffre multiplié par une unité de temps.
	*/
	function time_date($d, $u = 'j') {

		if (!preg_match('`^\d+$`', $d) || empty($d)) {
			return time();
		}
		switch ($u) {
			case 'h' : $date = time() - (3600 * $d); break;
			case 'j' : $date = time() - (24 * 3600 * $d); break;
			case 's' : $date = time() - (7 * 24 * 3600 * $d); break;
			case 'a' : $date = time() - (365 * 24 * 3600 * $d); break;
		}

		return $date;
	}



	/*
	 *	Renvoie une date formatée.
	*/
	function ladate($timestamp = 0, $format = '%A %d %B %Y à %H:%M:%S') {

		$timestamp = ($timestamp) ? $timestamp : time();
		return strftime($format, $timestamp);
	}



	/*
	 *	Renvoi un poids formaté avec unité à partir d'un poids brut en kilo-octet.
	*/
	function poids($poids, $r = ',') {

		$mo = 1024;
		$go = 1024 * $mo;
		$to = 1024 * $go;

		if ($poids < $mo) {
			$poids = round($poids, 2) . '&nbsp;Ko';
		} elseif ($poids < $go) {
			$poids = round($poids/$mo, 2) . '&nbsp;Mo';
		} elseif ($poids < $to) {
			$poids = round($poids/$go, 2) . '&nbsp;Go';
		} else {
			$poids = round($poids/$to, 2) . '&nbsp;To';
		}

		return str_replace('.', $r, $poids);
	}



	/*
	 *	Protection MySQL.
	*/
	function protege_mysql($string, $lien) {
		if (function_exists('mysql_real_escape_string')) {
			return mysql_real_escape_string($string, $lien);
		} else {
			return mysql_escape_string($string);
		}
	}



	/*
	 *	Remplacement des cinq caractères sensibles par des entitées HTML.
	 *	Version nuancée de htmlspecialchars().
	*/
	function html_specialchars($text) {
		$text = preg_replace('`&(?!#\d+;)`', '&amp;', $text);
		$text = str_replace('"', '&quot;', $text);
		$text = str_replace("'", '&#039;', $text);
		$text = str_replace('<', '&lt;', $text);
		$text = str_replace('>', '&gt;', $text);
		return $text;
	}



	/*
	 *	Fonctions de cryptage.
	 *	http://www.info-3000.com/phpmysql/cryptagedecryptage.php
	*/
	function genKey($text, $key) {
		$key = md5($key);
		$counter = 0;
		$string = '';
		for ($n = 0; $n < strlen($text); $n++) {
			if ($counter == strlen($key)) {
				$counter = 0;
			}
			$string .= substr($text, $n, 1) ^ substr($key, $counter, 1);
			$counter++;
		}
		return $string;
	}

	function crypte($text, $key) {
		srand((double)microtime() * 1000000);
		$key_encrypt = md5(rand(0, 32000));
		$counter = 0;
		$string = '';
		for ($n = 0; $n < strlen($text); $n++) {
			if ($counter == strlen($key_encrypt)) {
				$counter = 0;
			}
			$string .= substr($key_encrypt, $counter, 1) . (substr($text, $n, 1) ^ substr($key_encrypt, $counter, 1));
			$counter++;
		}
		return base64_encode(outils::genKey($string, $key));
	}

	function decrypte($text, $key) {
		$text = outils::genKey(base64_decode($text), $key);
		$string = '';
		for ($n = 0; $n < strlen($text); $n++) {
			$md5 = substr($text, $n, 1);
			$n++;
			$string .= (substr($text, $n, 1) ^ $md5);
		}
		return $string;
	}



	/*
	 *	Convertit une couleur au format HTML vers le format RGB.
	*/
	function convert_html2rgb($html) {
		return list($rgb[0], $rgb[1], $rgb[2]) = sscanf($html, '%2x%2x%2x');
	}



	/*
	 *	Convertit une couleur au format RGB vers le format HTML.
	*/
	function convert_rgb2html($rgb) {
		return str_pad(dechex(($rgb[0] << 16) | ($rgb[1] << 8) | $rgb[2]), 6, '0', STR_PAD_LEFT);
	}



	/*
	  *	Génère un lien selon le type d'URL
	*/
	function genLink($link, $img_name = '', $cat_name = '', $path = 0, $split = '&amp;') {

		if (GALERIE_URL_TYPE == 'path_info' || 
			GALERIE_URL_TYPE == 'query_string' ||
			GALERIE_URL_TYPE == 'url_rewrite') {

			$img_name = outils::convertNameToURL($img_name);
			$cat_name = outils::convertNameToURL($cat_name);

			$link = preg_replace('`^/?\?`', '', $link);
			$params = preg_split('`' . $split . '`', $link, -1, PREG_SPLIT_NO_EMPTY);
			$new_link = '';

			for ($i = 0; $i < count($params); $i++) {
				$p = preg_split('`=`', $params[$i], -1, PREG_SPLIT_NO_EMPTY);
				if (!isset($p[1])) continue;
				if ($p[1] == 1) {
					if ($p[0] == 'cat') {
						continue;
					}
					if ($p[0] != 'img') {
						if (!$img_name) {
							$img_name = 'galerie';
						}
						if (!$cat_name) {
							$cat_name = 'galerie';
						}
					}
				}
				switch ($p[0]) {
					case 'img' :
						$new_link .= '/image/' . $p[1] . '-' . $img_name;
						break;
					case 'cat' :
						$new_link .= '/categorie/' . $p[1] . '-' . $cat_name;
						break;
					case 'alb' :
						$new_link .= '/album/' . $p[1] . '-' . $cat_name;
						break;
					case 'startnum' :
						if ($p[1]) {
							$new_link .= '/startnum/' . $p[1];
						}
						break;
					case 'tag' :
					case 'date_ajout' :
					case 'date_creation' :
					case 'section' :
					case 'membres' :
					case 'profil' :
					case 'addfav' :
					case 'user' :
					case 'mcom' :
					case 'mimg' :
					case 'mfav' :
					case 'type' :
					case 'search' :
					case 'sadv' :
						$new_link .= '/' . $p[0] . '/' . $p[1];
						break;
					case 'images' :
					case 'recentes' :
					case 'hits' :
					case 'commentaires' :
					case 'comments' :
					case 'votes' :
						if ($img_name != $cat_name) {
							$img_name = $cat_name;
						}
						$new_link .= '/' . $p[0] . '/' . $p[1] . '-' . $img_name;
						break;
				}
			}

			if (!$new_link) {
				$new_link = '/';
			}

			switch ($path) {
				case 1 : return $new_link;
				case 2 :
					switch (GALERIE_URL_TYPE) {
						case 'path_info' :
							return GALERIE_FILE . $new_link;

						case 'query_string' :
							$l = ($new_link == '/') ? '' : '?' . $new_link;
							return GALERIE_FILE . $l;

						case 'url_rewrite' :
							return $new_link;
					}
			}

			switch (GALERIE_URL_TYPE) {
				case 'path_info' :
					return GALERIE_PATH . '/' . GALERIE_FILE . $new_link;

				case 'query_string' :
					$l = ($new_link == '/') ? '' : '?' . $new_link;
					return GALERIE_PATH . '/' . GALERIE_FILE . $l;

				case 'url_rewrite' :					
					if ($new_link == '/' && basename(GALERIE_URL) != 'index.php') {
						$new_link = '/' . basename(GALERIE_URL);
					}
					return GALERIE_PATH . $new_link;
			}

		}

		if ($path) {
			return $link;
		}

		if (($link == '?cat=1' || $link == '/?cat=1')) {
			$link = '';
		}

		$galerie_file = (GALERIE_FILE == 'index.php') ? '' : GALERIE_FILE;

		return GALERIE_PATH . '/' . $galerie_file . $link;
	}

	function convertNameToURL($s) {

		if (!$s) return $s;

		$s = strip_tags($s);
		$s = str_replace(' ', '-', trim($s));
		$s = strtr($s, 'ÉÈËÊÀÄÂÁÅÃÏÎÌÍÖÔÒÓÕÙÛÜÚŸÝÇÑŠŽéèëêàäâáåãïîìíöôòóõùûüúÿýçñšž', 
					   'eeeeaaaaaaiiiiooooouuuuyycnszeeeeaaaaaaiiiiooooouuuuyycnsz');
		$s = preg_replace('`[^-a-z0-9_]`i', '-', $s);		
		$s = preg_replace('`-+`', '-', $s);

		return strtolower($s);
	}



	/*
	 *	Détermine les dimensions des vignettes à afficher.
	*/
	function thumb_size($type, $thumb_largeur_max, $img_width, $img_height) {

		$tb_mode = ($type == 'img') ? THUMB_IMG_MODE : THUMB_ALB_MODE;

		// Mode de redimensionnement 'retaillé'.
		if ($tb_mode == 'crop') {
			$tb_crop_width = ($type == 'img') ? THUMB_IMG_CROP_WIDTH : THUMB_ALB_CROP_WIDTH;
			$tb_crop_height = ($type == 'img') ? THUMB_IMG_CROP_HEIGHT : THUMB_ALB_CROP_HEIGHT;
			$width = $tb_crop_width;
			$height = $tb_crop_height;
			if ($tb_crop_width > $thumb_largeur_max || $tb_crop_height > $thumb_largeur_max) {
				$width = $thumb_largeur_max;
				$height = $thumb_largeur_max;
				if ($tb_crop_width < $tb_crop_height) {
					$width = round(($height / $tb_crop_height) * $tb_crop_width);
				} else {
					$height = round(($width / $tb_crop_width) * $tb_crop_height);
				}
			}

		// Mode de redimensionnement 'proportionnel'.
		} else {
			$width = $thumb_largeur_max;
			$height = $thumb_largeur_max;
			if ($img_width < $img_height) {
				$width = round(($height / $img_height) * $img_width);
			} else {
				$height = round(($width / $img_width) * $img_height);
			}
		}

		return array($width, $height);
	}



	/*
	 *	Formate les messages des commentaires à afficher.
	*/
	function comment_format($s, $limit = 55) {
		$s = trim($s);
		$s = outils::html_specialchars($s);
		$s = outils::replace_url($s, 50);
		if (preg_match('`\S{' . ($limit+1) . '}`', $s)) {
			$s = explode(' ', $s);
			for ($i = 0, $m = true; $i < count($s); $i++) {
				if (strpos($s[$i], '<')) {
					$m = false;
				}
				if (strpos($s[$i], '</')) {
					$m = true;
					continue;
				}
				if ($m && strlen($s[$i]) > $limit) {
					$s[$i] = wordwrap($s[$i], $limit, ' ', true);
				}
			}
			$s = implode(' ', $s);
		}
		$s = str_replace("\r\n", "<br />", $s);
		$s = str_replace("\n", "<br />", $s);

		return $s;
	}



	/*
	  *	Génère une chaine aléatoire.
	*/
	function gen_key($longueur = 20) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$chars .= '01234567890123456789';
		$chars .= 'abcdefghijklmnopqrstuvwxyz';
		$chars .= '01234567890123456789';
		for ($n = 0, $ID = ''; $n < $longueur; $n++) {
			$ID .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		return $ID;
	}
}
?>