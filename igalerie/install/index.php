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
@ob_start('ob_gzhandler');

$mysqlv_require = '4.1.2';
$phpv_require = '4.3';
$config = dirname(__FILE__) . '/../config/conf.php';

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


// On désactive la fonction magic_quotes_runtime.
if (get_magic_quotes_runtime() && 
	function_exists('set_magic_quotes_runtime')) {
	set_magic_quotes_runtime(0);
}

if (empty($_REQUEST['etape'])) {
	$_REQUEST['etape'] = 0;
}

// Si la galerie est installée, on arrête tout.
if (file_exists($config)) {
	@require_once($config);
}
if (defined('GALERIE_INSTALL') && GALERIE_INSTALL == 1) {
	$_REQUEST['etape'] = -1;

// Etapes.
} else {

	require_once(dirname(__FILE__) . '/../includes/classes/class.mysql.php');
	require_once(dirname(__FILE__) . '/../includes/classes/class.cookie.php');
	require_once(dirname(__FILE__) . '/../includes/classes/class.files.php');
	require_once(dirname(__FILE__) . '/../includes/classes/class.outils.php');
	require_once(dirname(__FILE__) . '/create_tables.php');

	$mysqlv_require_test = version($mysqlv_require);
	$phpv_require_test = version($phpv_require);
	

	// On vérifie la version de PHP pour chaque étape.
	$php_version = version(phpversion());
	if ($php_version < $phpv_require_test && $_REQUEST['etape'] > 1) {
		$_REQUEST['etape'] = 1;
	}

	// On vérifie les droits en écriture pour chaque étape.
	if (!files::chmodThis('./../config/')
	 || !files::chmodThis('./../albums/')
	 || !files::chmodThis('./../cache/')
	 || !files::chmodThis('./../cache/cat_thumb/')) {
		$_REQUEST['etape'] = 1;
	}

	// Quelle étape ?
	switch ($_REQUEST['etape']) {

		// Etape 0 : Introduction
		case 0 :
			$etape_titre = 'Introduction';

			// On tente de créer un cookie.
			$cookie = new cookie(3600*24*30, 'galerie_test');
			$cookie->ajouter('otlpdcucslc', '1');
			$cookie->ecrire();

			break;

		// Etape 1 : vérification système
		case 1 :
			$etape_titre = 'Étape 1 : vérification système';

			$sys = array();

			// Version de PHP.
			$sys['php'] = array();
			$sys['php']['version'] = phpversion();
			$sys['php']['ok'] = ($php_version < $phpv_require_test) ? false : true;

			// GD.
			$sys['gd'] = array();			
			if (function_exists('gd_info')) {
				$gd_info = gd_info();
				$sys['gd']['version'] = (empty($gd_info['GD Version'])) ? 'inconnue' : $gd_info['GD Version'];
				$sys['gd']['ok'] = true;
			} elseif (function_exists('imagetypes')) {
				$sys['gd']['version'] = 'inconnue';
				$sys['gd']['ok'] = true;
			} else {
				$sys['gd']['version'] = false;
				$sys['gd']['ok'] = false;
			}

			// Exif.
			$sys['exif'] = array();
			$sys['exif']['ok'] = (function_exists('read_exif_data')) ? true : false;

			// Extension Zip.
			$sys['zip'] = array();
			$sys['zip']['ok'] = (function_exists('zip_open')) ? true : false;

			// Cookie.
			$cookie = new cookie(3600*24*14, 'galerie_test');
			if ($cookie->lire('otlpdcucslc')) {
				$sys['cookie']['ok'] = true;
			} else {
				$sys['cookie']['ok'] = false;
			}

			// Droits en écriture.
			$sys['acces'] = array();
			$sys['acces']['config'] = files::chmodThis('./../config/');
			$sys['acces']['albums'] = files::chmodThis('./../albums/');
			$sys['acces']['cache'] = files::chmodThis('./../cache/');
			$sys['acces']['cache/cat_thumb'] = files::chmodThis('./../cache/cat_thumb/');

			break;

		// Etape 2 : Informations MySQL
		case 2 :
			$etape_titre = 'Étape 2 : informations MySQL';
			$erreur_message = '';

			if (!empty($_POST)) {
				$_POST['mysql_serveur'] = trim($_POST['mysql_serveur']);
				$_POST['mysql_user'] = trim($_POST['mysql_user']);
				$_POST['mysql_pass'] = trim($_POST['mysql_pass']);
				$_POST['mysql_base'] = trim($_POST['mysql_base']);

				$mysql_err = array();

				// On vérifie s'il y a des champs vide.
				if (empty($_POST['mysql_serveur'])) {
					$mysql_err['serveur'] = 1;
				}
				if (empty($_POST['mysql_user'])) {
					$mysql_err['user'] = 1;
				}
				if (empty($_POST['mysql_base'])) {
					$mysql_err['base'] = 1;
				}
				if (!empty($mysql_err)) {
					$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>Certains champs n\'ont pas été remplis.</strong></p>' . "\n";
					break;
				}

				// On vérifie que le champs préfixe ne comporte que des caractéres alphanumériques.
				if (!preg_match('`^[a-z0-9_]{0,20}$`i', $_POST['mysql_pref'])) {
					$mysql_err['pref'] = 1;
					$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>Le préfixe des tables doit être constitué uniquement de caractères alphanumériques et de 20 caractères au plus.</strong></p>' . "\n";
					break;
				}

				// On supprime le protocole dans l'adresse.
				$_POST['mysql_serveur'] = preg_replace('`^http://`', '', $_POST['mysql_serveur']);

				// On vérifie que l'on peut bien se connecter à la base de données.
				$mysql = new connexion($_POST['mysql_serveur'],
									   $_POST['mysql_user'],
									   $_POST['mysql_pass'],
									   $_POST['mysql_base'],
									   1);
				if ($mysql->erreur) {
					if ($mysql->erreur == 1) {
						$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>Impossible de se connecter au serveur :<br />' . mysql_error() . '</strong></p>' . "\n";
					} elseif ($mysql->erreur == 2) {
						$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>Impossible de se connecter à la base de données :<br />' . mysql_error() . '</strong></p>' . "\n";
						$mysql->fermer();
						$mysql_err['base'] = 1;
					}
					break;
				}

				// On vérifie la version de MySQL.
				$sys['mysql'] = array();
				$sys['mysql']['version'] = @mysql_get_server_info();
				$mysql_version_test = version($sys['mysql']['version']);
				$mysql->fermer();
				$sys['mysql']['ok'] = ($mysql_version_test < $mysqlv_require_test) ? false : true;
				if (!$sys['mysql']['ok']) {
					break;
				}

				// On crée le fichier conf.php avec les informations de base de données.
				$sys['conf.php'] = array();
				if (gal_config($config, 2)) {
					$sys['conf.php']['ok'] = true;
				} else {
					$sys['conf.php']['ok'] = false;
					break;
				}

				// On crée les tables.
				$sys['tables'] = array();
				require_once($config);
				$result_tables = create_tables();
				if ($result_tables[1]) {
					$sys['tables']['ok'] = true;
				} else {
					$sys['tables']['ok'] = false;
					$sys['tables']['line'] = $result_tables[0];
					break;
				}

			}

			break;

		// Etape 3 : Informations galerie
		case 3 :
			if (!file_exists($config)) {
				header('Location:./');
				exit;
			}

			$etape_titre = 'Étape 3 : informations galerie';
			$erreur_message = '';

			$script_name = preg_replace('`^http://[^/]+/`', '/', $_SERVER['SCRIPT_NAME']);
			$galerie_path = dirname(dirname($script_name));
			$galerie_path = (preg_match('`^[./]*$`', $galerie_path)) ? '' : $galerie_path;
			$galerie_path = preg_replace('`/+$`', '', $galerie_path);
			$galerie_path = preg_replace('[\x5c]', '', $galerie_path);
			$galerie_url = $galerie_path . '/index.php';

			if (!empty($_POST)) {
				$_POST['gal_user'] = trim($_POST['gal_user']);
				$_POST['gal_pass'] = trim($_POST['gal_pass']);
				$_POST['gal_pass_confirm'] = trim($_POST['gal_pass_confirm']);
				$_POST['gal_mail'] = trim($_POST['gal_mail']);
				$_POST['gal_url'] = trim($_POST['gal_url']);

				$gal_err = array();

				// On vérifie s'il y a des champs vide.
				if (empty($_POST['gal_user'])) {
					$gal_err['user'] = 1;
				}
				if (empty($_POST['gal_pass'])) {
					$gal_err['pass'] = 1;
				}
				if (empty($_POST['gal_pass_confirm'])) {
					$gal_err['pass_confirm'] = 1;
				}
				if (empty($_POST['gal_url'])) {
					$gal_err['url'] = 1;
				}
				if (!empty($gal_err)) {
					$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>Certains champs n\'ont pas été remplis.</strong></p>' . "\n";
					break;
				}

				// On vérifie que chaque champs ne comporte que des caractéres alphanumériques.
				if (!preg_match('`^[a-z\d_-]{2,30}$`i', $_POST['gal_user'])) {
					$gal_err['user'] = 1;
					$erreur_message = 'L\'identifiant';
				} elseif (!preg_match('`^[a-z\d_-]{6,30}$`i', $_POST['gal_pass'])) {
					$gal_err['pass'] = 1;
					$erreur_message = 'Le mot de passe';
				} elseif (!preg_match('`^[a-z\d_-]{6,30}$`i', $_POST['gal_pass_confirm'])) {
					$gal_err['pass_confirm'] = 1;
					$erreur_message = 'Le mot de passe';
				}
				if (!empty($erreur_message)) {
					$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>' . $erreur_message . ' doit comporter entre 6 et 30 caractères alphanumériques (non accentués) ou souligné (_).</strong></p>' . "\n";
					break;
				}

				// On vérifie la longueur du mot de passe.
				if (strlen($_POST['gal_pass']) < 6) {
					$gal_err['pass'] = 1;
					$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>La longueur du mot de passe est trop courte. Elle doit faire au moins six caractères.</strong></p>' . "\n";
					break;
				}

				// On vérifie que le mot de passe et la confirmation du mot de passe sont identique.
				if ($_POST['gal_pass'] != $_POST['gal_pass_confirm']) {
					$gal_err['pass_confirm'] = 1;
					$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>La confirmation du mot de passe ne correspond pas.</strong></p>' . "\n";
					break;
				}

				// On vérifie le courriel.
				if (!empty($_POST['gal_mail']) && !preg_match('`^' . outils::email_address() . '$`i', $_POST['gal_mail'])) {
					$gal_err['mail'] = 1;
					$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>Le format de l\'adresse courriel n\'est pas correct.</strong></p>' . "\n";
					break;
				}

				// On vérifie l'URL de la galerie.
				if (!preg_match('`^http://.+/.+\..+$`i', $_POST['gal_url'])) {
					$gal_err['url'] = 1;
					$erreur_message = "\t\t" . '<br /><p class="info_attention"><strong>Le format de l\'URL de la galerie n\'est pas correct.</strong></p>' . "\n";
					break;
				}

				// On enregistre les informations et on ouvre la galerie.
				if (!gal_config($config, 3)) {
					$datasave_err = "\t\t" . '<p class="info_erreur"><strong>Impossible de modifier le fichier de config.<br />L\'installation a échouée.</strong></p>' . "\n";
					break;
				}

				// Suppression du cookie de test.
				$cookie = new cookie(3600*24*30, 'galerie_test');
				if ($cookie->lire('otlpdcucslc')) {
					$cookie->expire = time();
					$cookie->ajouter(1, 1);
					$cookie->ecrire();
				}

				// On enregistre le courriel dans la base de données,
				// et on crée par là même l'utilisateur admin.
				$courriel = (empty($_POST['gal_mail'])) ? '' : $_POST['gal_mail'];
				$mysql = new connexion(MYSQL_SERV, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);
				$mysql_requete = 'INSERT IGNORE ' . MYSQL_PREF . 'users(
									groupe_id,
									user_login,
									user_pass,
									user_mail,
									user_date_creation,
									user_date_derniere_visite
									) VALUES (
									"1",
									"' . $_POST['gal_user'] . '",
									"' . md5($_POST['gal_pass']) . '",
									"' . outils::protege_mysql($courriel, $mysql->lien) . '",
									"' . time() . '",
									"' . time() . '")';
				if (!$mysql->requete($mysql_requete)) {
					$datasave_err = "\t\t" . '<p class="info_erreur"><strong>Impossible d\'effectuer une requête SQL [' . __LINE__ . '].<br />' . mysql_error() . '<br />L\'installation a échouée.</strong></p>' . "\n";
					break;
				}
				if ($courriel) {
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'config
										 SET valeur = "' . outils::protege_mysql($courriel, $mysql->lien) . '"
									   WHERE parametre = "admin_mail"';
				}
				if (!$mysql->requete($mysql_requete)) {
					$datasave_err = "\t\t" . '<p class="info_erreur"><strong>Impossible d\'effectuer une requête SQL [' . __LINE__ . '].<br />' . mysql_error() . '<br />L\'installation a échouée.</strong></p>' . "\n";
					break;
				}
				$mysql->fermer();

				// Création du fichier .htaccess.
				$htaccess_file = './../.htaccess';
				if (!file_exists($htaccess_file)) {
					files::chmodDir(dirname($htaccess_file));
					if ($htaccess = @fopen($htaccess_file, 'w')) {
						$instructions = "Options -Indexes";
						@fwrite($htaccess, $instructions);
						@fclose($htaccess);
					}
				}

				// Si le fichier de mise à jour existe, on le supprime.
				$upgrade_file = './../upgrade.php';
				if (file_exists($upgrade_file)) {
					files::suppFile($upgrade_file);
				}

				$install_ok = true;
				$etape_titre = 'Fin de l\'installation';
			}

			break;

		default :
			header('Location:./');
			exit;
	}
	
}

function version($v) {
	$v = preg_replace('`(\d+)(?:\.(\d+))?(?:\.(\d+))?(?:\.(\d+))?`', '$1.$2$3$4', $v);
	return floatval($v);
}

function sys_accessdir($sys, $dir) {
	if ($sys['acces'][$dir]) {
		echo "\t\t" . '<p class="info_succes">Le répertoire ' . $dir . '/ est accessible en écriture</p><br />' . "\n";
	} else {
		echo "\t\t" . '<p class="info_erreur">Le répertoire ' . $dir . '/ n\'est pas accessible en écriture</p>' . "\n";
		echo "\t\t" . '<p class="explications">Ce répertoire n\'est pas accessible en écriture. Vous devez effectuer un CHMOD en 775 sur ce répertoire pour pouvoir poursuivre l\'installation.</p><br />';
	}
}

function gal_config($config, $etape) {

	if (file_exists($config)) {
		files::chmodFile($config);
	}
	if ($id = fopen($config, 'w')) {

		$conf = "<?php\n\n";

		if ($etape == 2) {
			$conf .= "define('MYSQL_SERV', '" . $_POST['mysql_serveur'] . "');\n";
			$conf .= "define('MYSQL_USER', '" . $_POST['mysql_user'] . "');\n";
			$conf .= "define('MYSQL_PASS', '" . $_POST['mysql_pass'] . "');\n";
			$conf .= "define('MYSQL_BASE', '" . $_POST['mysql_base'] . "');\n";
			$conf .= "define('MYSQL_PREF', '" . $_POST['mysql_pref'] . "');\n";
		}

		if ($etape == 3) {
			if (!defined('MYSQL_SERV')) {
				return false;
			}
			$galerie_url = preg_replace('`^http://[^/]+(/.+)$`i', '$1', $_POST['gal_url']);
			$galerie_url = str_replace(' ', '%20', $galerie_url);
			$galerie_path = dirname($galerie_url);
			$galerie_path = (preg_match('`^[./]*$`', $galerie_path)) ? '' : $galerie_path;
			$galerie_path = preg_replace('`/+$`', '', $galerie_path);
			$galerie_path = preg_replace('[\x5c]', '', $galerie_path);
			$conf .= "define('ADMIN_USER', '" . $_POST['gal_user'] . "');\n";
			$conf .= "define('ADMIN_PASS', '" . $_POST['gal_pass'] . "');\n";
			$conf .= "define('MYSQL_SERV', '" . MYSQL_SERV . "');\n";
			$conf .= "define('MYSQL_USER', '" . MYSQL_USER . "');\n";
			$conf .= "define('MYSQL_PASS', '" . MYSQL_PASS . "');\n";
			$conf .= "define('MYSQL_BASE', '" . MYSQL_BASE . "');\n";
			$conf .= "define('MYSQL_PREF', '" . MYSQL_PREF . "');\n";
			$conf .= "define('THUMB_TDIR', 'vignettes');\n";
			$conf .= "define('THUMB_PREF', 'thumb_');\n";
			$conf .= "define('THUMB_ALB_MODE', 'size');\n";
			$conf .= "define('THUMB_ALB_SIZE', 135);\n";
			$conf .= "define('THUMB_ALB_CROP_WIDTH', 200);\n";
			$conf .= "define('THUMB_ALB_CROP_HEIGHT', 150);\n";
			$conf .= "define('THUMB_IMG_MODE', 'size');\n";
			$conf .= "define('THUMB_IMG_SIZE', 135);\n";
			$conf .= "define('THUMB_IMG_CROP_WIDTH', 85);\n";
			$conf .= "define('THUMB_IMG_CROP_HEIGHT', 85);\n";
			$conf .= "define('THUMB_QUALITY', '80');\n";
			$conf .= "define('IMG_RESIZE_GD', '650x600');\n";
			$conf .= "define('IMG_TEXTE', 0);\n";
			$conf .= "define('IMG_TEXTE_PARAMS', '©copyright 0 0 0 Veranda.ttf bottom_center 1 255 255 255 50 1 0 2 149 149 149 10 5 6 1 0 0 48 75 98 0 90');\n";
			$conf .= "define('GALERIE_URL', '" . $galerie_url . "');\n";
			$conf .= "define('GALERIE_PATH', '" . $galerie_path . "');\n";
			$conf .= "define('GALERIE_THEME', 'defaut');\n";
			$conf .= "define('GALERIE_STYLE', 'defaut');\n";
			$conf .= "define('GALERIE_URL_TYPE', 'normal');\n";
			$conf .= "define('GALERIE_ALBUMS', 'albums');\n";
			$conf .= "define('GALERIE_INSTALL', 1);\n";
			$conf .= "define('GALERIE_VERSION', '20090114');\n";
			$conf .= "define('GALERIE_INTEGRATED', 0);\n";
		}

		$conf .= "\n?>";

		// Ecriture du fichier.
		if (fwrite($id, $conf)) {
			fclose($id);
			if (file_exists($config)) {
				return true;
			}
		}

	}
	return false;
	
}


define('CHARSET', 'ISO-8859-15');
@header('Content-Type: text/html; charset=' . CHARSET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">


<head>

<title>installation - iGalerie</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" media="screen" href="style.css" />

<script type="text/javascript">
//<![CDATA[
window.onload = function() {
	if (document.getElementsByTagName('input')[1]) {
		document.getElementsByTagName('input')[1].focus();
	}
	var spans = document.getElementsByTagName('span');
	for (var i = 0; i < spans.length; i++) {
		if (spans[i].className.search(/info_attention/) != -1) {
			var id = spans[i].className.replace(/info_attention /, '');
			document.getElementById(id).focus();
			break;
		}
	}
}
//]]>
</script>

</head>


<body>

<div id="global">

	<div id="titre">
		<h1>Installation</h1>
	</div>

<?php if (isset($etape_titre)) : ?>	
	<h2><span><?php echo $etape_titre; ?></span></h2>
<?php endif; ?>	

	<div id="contenu">
<?php
		switch ($_REQUEST['etape']) {
			case -1 :
?>
		<p id="deja"><em>La galerie est déjà installée.</em></p>
<?php
			break;
			case 0 :
?>
		<p id="bienvenue">
			Bienvenue dans l'installation de<br />
			<strong>iGalerie 1.0.6</strong> !<br />
		</p>
		<p><strong>Important !</strong> Avant de poursuivre, vérifiez bien <a class="ex" href="http://www.igalerie.org/index.php#download">sur cette page</a>, pour des raisons de sécurité et de performances, que vous êtes en possession de la dernière version.</p>
		<br />
		<p>L'installation va se dérouler en trois étapes :</p>
		<ul id="etapes">
			<li>Introduction</li>
			<li>Étape 1 : vérification système</li>
			<li>Étape 2 : informations MySQL</li>
			<li>Étape 3 : informations galerie</li>
			<li>Fin de l'installation</li>
		</ul>
		<br /><br />
		<p id="next"><a href="?etape=1">commencer l'installation</a> =></p>
<?php
			break;
			case 1 :
				if ($sys['php']['ok']) {
					echo "\t\t" . '<h3>Configuration serveur</h3><br />' . "\n";
					echo "\t\t" . '<p class="info_succes">Version de PHP : ' . $sys['php']['version'] . '</p><br />' . "\n";
					if ($sys['gd']['ok']) {
						echo "\t\t" . '<p class="info_succes">GD est présent ; version : ' . $sys['gd']['version'] . '</p><br />' . "\n";
					} else {
						echo "\t\t" . '<p class="info_attention">GD n\'est pas présent</p>' . "\n";
						echo "\t\t" . '<p class="explications">Il semble que GD ne soit pas présent, ce qui empêchera la galerie de générer les vignettes. Vous devrez donc créer vous-même les vignettes et les envoyer par FTP.<br /><br />
							Consultez <a href="http://www.igalerie.org/faq.php#ou-se-trouvent-les-vignettes" class="ex">la FAQ</a> pour obtenir des détails sur l\'emplacement des vignettes.</p><br />';
					}
					if ($sys['exif']['ok']) {
						echo "\t\t" . '<p class="info_succes">L\'extension Exif est présente</p><br />' . "\n";
					} else {
						echo "\t\t" . '<p class="info_attention">L\'extension Exif n\'est pas présente</p>' . "\n";
						echo "\t\t" . '<p class="explications">iGalerie ne pourra pas afficher les informations Exif de vos images.</p><br />';
					}
					if ($sys['zip']['ok']) {
						echo "\t\t" . '<p class="info_succes">L\'extension Zip est présente</p><br />' . "\n";
					} else {
						echo "\t\t" . '<p class="info_attention">L\'extension Zip n\'est pas présente</p>' . "\n";
						echo "\t\t" . '<p class="explications">Vous ne pourrez pas envoyer des archives Zip contenant des images par l\'interface d\'administration.</p><br />';
					}
					echo "\t\t" . '<br />' . "\n";
					echo "\t\t" . '<h3>Configuration navigateur</h3><br />' . "\n";
					if ($sys['cookie']['ok']) {
						echo "\t\t" . '<p class="info_succes">Les cookies sont activés</p><br />' . "\n";
					} else {
						echo "\t\t" . '<p class="info_attention">Les cookies ne sont pas activés ou acceptés</p>' . "\n";
						echo "\t\t" . '<p class="explications">Vous devez activer les cookies et accepter les cookies d\'iGalerie pour pouvoir envoyer des images et administrer la galerie.</p><br />';
					}
					echo "\t\t" . '<div id="test_jsok" style="display:none" ><p class="info_succes">JavaScript est activé</p></div>' . "\n";
					echo "\t\t" . '<p id="test_nojs" class="info_attention">JavaScript n\'est pas activé</p>' . "\n";
					echo "\t\t" . '<p id="test_nojs_ex" class="explications">Certaines parties de l\'administration ne seront pas accessible sans Javascript.</p>';
					echo "\t\t" . '<script type="text/javascript">' . "\n";
					echo "\t\t" . '//<![CDATA[' . "\n";
					echo "\t\t" . 'document.getElementById(\'test_nojs\').style.display = \'none\';' . "\n";
					echo "\t\t" . 'document.getElementById(\'test_nojs_ex\').style.display = \'none\';' . "\n";
					echo "\t\t" . 'document.getElementById(\'test_jsok\').style.display = \'block\';' . "\n";
					echo "\t\t" . '//]]>' . "\n";
					echo "\t\t" . '</script><br />' . "\n";
					echo "\t\t" . '<br />' . "\n";
					echo "\t\t" . '<h3>Droits en écriture</h3><br />' . "\n";
					sys_accessdir($sys, 'config');
					sys_accessdir($sys, 'albums');
					sys_accessdir($sys, 'cache');
					sys_accessdir($sys, 'cache/cat_thumb');
					if ($sys['acces']['config']
					 && $sys['acces']['albums']
					 && $sys['acces']['cache']
					 && $sys['acces']['cache/cat_thumb']) {
						echo "\t\t" . '<br /><br />' . "\n";
						echo "\t\t" . '<p id="next"><a href="?etape=2">étape 2</a> =></p>' . "\n";
					}
				} else {
					echo "\t\t" . '<p class="info_erreur">Version de PHP : <strong>' . $sys['php']['version'] . '</strong></p>' . "\n";
					echo "\t\t" . '<p class="explications">Désolé, iGalerie nécessite la version ' . $phpv_require . ' ou supérieure de PHP.<br />Vous ne pouvez poursuivre l\'installation.</p>';
				}
			break;
			case 2 :
			if (isset($sys['mysql'])) {
				echo "\t\t" . '<p class="info_succes">La connexion à la base de données a pu être établie.</p><br />' . "\n";
				if ($sys['mysql']['ok']) {
					echo "\t\t" . '<p class="info_succes">Version de MySQL : ' . $sys['mysql']['version'] . '</p><br />' . "\n";
					if ($sys['conf.php']['ok']) {
						echo "\t\t" . '<p class="info_succes">Le fichier de configuration a été créé.</p><br />' . "\n";
						if ($sys['tables']['ok']) {
							echo "\t\t" . '<p class="info_succes">Les tables ont été créées.</p>' . "\n";
							echo "\t\t" . '<br /><br />' . "\n";
							echo "\t\t" . '<p id="next"><a href="?etape=3">étape 3</a> =></p>' . "\n";
						} else {
							echo "\t\t" . '<p class="info_erreur">Impossible de créer les tables.</p>' . "\n";
							echo "\t\t" . '<p class="explications">Une erreur s\'est produite lors de la création des tables&nbsp;:<br />' . $sys['tables']['line'] . '<br />Vous ne pouvez poursuivre l\'installation.</p><br />';
						}
					} else {
						echo "\t\t" . '<p class="info_erreur">Impossible de créer le fichier de configuration.<br />L\'installation ne peut se poursuivre.</p>' . "\n";
					}
					break;
				} else {
					echo "\t\t" . '<p class="info_erreur">version de MySQL : <strong>' . $sys['mysql']['version'] . '</strong></p>' . "\n";
					echo "\t\t" . '<p class="explications">Désolé, iGalerie nécessite la version ' . $mysqlv_require . ' ou supérieure de MySQL.<br />Vous ne pouvez poursuivre l\'installation.</p><br />';
					break;
				}
			}
?>
		<p>Veuillez fournir les renseignements de base de données&nbsp;:</p>
		<?php echo $erreur_message; ?>
		<form action="" method="post">
			<div>
				<input type="hidden" name="etape" value="2" />
				<div class="fielditems">
					<p class="field">
<?php
	$text = 'Adresse du serveur';
	if (isset($mysql_err['serveur'])) {
		$text = '<span class="info_attention mysql_serveur">' . $text . '</span>';
	}
?>
						<label for="mysql_serveur"><?php echo $text; ?></label>
						<input type="text" class="text" maxlength="256" size="40" name="mysql_serveur" id="mysql_serveur" value="<?php if (isset($_POST['mysql_serveur'])) { echo $_POST['mysql_serveur']; } else { echo 'localhost'; } ?>" />
					</p>
					<p class="field">
<?php
	$text = 'Identifiant';
	if (isset($mysql_err['user'])) {
		$text = '<span class="info_attention mysql_user">' . $text . '</span>';
	}
?>
						<label for="mysql_user"><?php echo $text; ?></label>
						<input type="text" class="text" maxlength="256" size="40" name="mysql_user" id="mysql_user" value="<?php if (isset($_POST['mysql_user'])) { echo $_POST['mysql_user']; } else { echo 'root'; } ?>" />
					</p>
					<p class="field">
<?php
	$text = 'Mot de passe';
	if (isset($mysql_err['pass'])) {
		$text = '<span class="info_attention mysql_pass">' . $text . '</span>';
	}
?>
						<label for="mysql_pass"><?php echo $text; ?></label>
						<input type="password" class="text" maxlength="256" size="40" name="mysql_pass" id="mysql_pass" value="<?php if (isset($_POST['mysql_pass'])) { echo $_POST['mysql_pass']; } ?>" />
					</p>
					<p class="field">
<?php
	$text = 'Base de données';
	if (isset($mysql_err['base'])) {
		$text = '<span class="info_attention mysql_base">' . $text . '</span>';
	}
?>
						<label for="mysql_base"><?php echo $text; ?></label>
						<input type="text" class="text" maxlength="256" size="40" name="mysql_base" id="mysql_base" value="<?php if (isset($_POST['mysql_base'])) { echo $_POST['mysql_base']; } ?>" />
					</p>
					<p class="field">
<?php
	$text = 'Préfixe des tables';
	if (isset($mysql_err['pref'])) {
		$text = '<span class="info_attention mysql_pref">' . $text . '</span>';
	}
?>
						<label for="mysql_pref"><?php echo $text; ?></label>
						<input type="text" class="text" maxlength="256" size="40" name="mysql_pref" id="mysql_pref" value="<?php if (isset($_POST['mysql_pref'])) { echo $_POST['mysql_pref']; } else { echo 'igal_'; } ?>" />
					</p>
					<br />
					<input type="submit" class="submit" value="Valider" />
				</div>
			</div>
		</form>
<?php
			break;
			case 3 :
				if (isset($datasave_err)) {
					echo $datasave_err;
					break;
				} elseif (!empty($install_ok)) {
					echo "\t\t" . '<p class="info_succes">Fichier de configuration mis à jour.</p><br /><br />' . "\n";
					echo "\t\t" . '<p class="info_succes" id="youpi">Félicitations, l\'installation a réussie !</p>' . "\n";
					echo "\t\t" . '<p class="explications">Vous pouvez maintenant utiliser iGalerie.</p><br /><br />' . "\n";
					echo "\t\t" . '<p class="info_attention"><strong>Pour des raisons de sécurité, il est fortement recommandé de supprimer le répertoire « install » avant de mettre en place votre galerie.</strong></p><br /><br />' . "\n";
					echo "\t\t" . '<p class="info_info">N\'oubliez pas que vous trouverez <a class="ex" href="http://www.igalerie.org/documentation.php">documentation</a>, <a class="ex" href="http://www.igalerie.org/faq.php"><acronym title="Foire Aux Questions">FAQ</acronym></a> et <a class="ex" href="http://www.igalerie.org/forum/">aide</a> sur le site d\'iGalerie.</p><br /><br /><br />' . "\n";
					echo "\t\t" . '<p id="connexion"><a href="./../admin/connexion.php">connexion admin</a></p><br />' . "\n";
					break;
				}
?>
		<p>Veuillez fournir les renseignements de la galerie&nbsp;:</p>
		<?php echo $erreur_message; ?>
		<form action="" method="post">
			<div>
				<input type="hidden" name="etape" value="3" />
				<div class="fielditems">
					<p class="field">
<?php
	$text = 'Identifiant';
	if (isset($gal_err['user'])) {
		$text = '<span class="info_attention gal_user">' . $text . '</span>';
	}
?>
						<label for="gal_user"><?php echo $text; ?></label>
						<input type="text" class="text" maxlength="256" size="40" name="gal_user" id="gal_user" value="<?php if (isset($_POST['gal_user'])) { echo $_POST['gal_user']; } ?>" />
					</p>
					<p class="field">
<?php
	$text = 'Mot de passe';
	if (isset($gal_err['pass'])) {
		$text = '<span class="info_attention gal_pass">' . $text . '</span>';
	}
?>
						<label for="gal_pass"><?php echo $text; ?></label>
						<input type="password" class="text" maxlength="256" size="40" name="gal_pass" id="gal_pass" value="<?php if (isset($_POST['gal_pass'])) { echo $_POST['gal_pass']; } ?>" />
					</p>
					<p class="field">
<?php
	$text = 'Confirmation du mot de passe';
	if (isset($gal_err['pass_confirm'])) {
		$text = '<span class="info_attention gal_pass_confirm">' . $text . '</span>';
	}
?>
						<label for="gal_pass_confirm"><?php echo $text; ?></label>
						<input type="password" class="text" maxlength="256" size="40" name="gal_pass_confirm" id="gal_pass_confirm" value="<?php if (isset($_POST['gal_pass_confirm'])) { echo $_POST['gal_pass_confirm']; } ?>" />
					</p>
					<p class="field">
<?php
	$text = 'Adresse courriel';
	if (isset($gal_err['mail'])) {
		$text = '<span class="info_attention gal_mail">' . $text . '</span>';
	}
?>
						<label for="gal_mail"><?php echo $text; ?></label>
						<input type="text" class="text" maxlength="256" size="40" name="gal_mail" id="gal_mail" value="<?php if (isset($_POST['gal_mail'])) { echo $_POST['gal_mail']; } ?>" />
					</p>
					<p class="field">
<?php
	$text = 'URL de la galerie';
	if (isset($gal_err['url'])) {
		$text = '<span class="info_attention gal_url">' . $text . '</span>';
	}
?>
						<label for="gal_url"><?php echo $text; ?></label>
						<input type="text" class="text" maxlength="256" size="40" name="gal_url" id="gal_url" value="<?php if (isset($_POST['gal_url'])) { echo $_POST['gal_url']; } else { echo 'http://' . $_SERVER['HTTP_HOST'] . $galerie_url; } ?>" />
					</p>
					<br />
					<input type="submit" class="submit" value="Valider" />
				</div>
			</div>
		</form>
<?php
			break;
		}
?>
	</div>
	<div id="bas">
		<p><a title="Site Web de iGalerie" href="http://www.igalerie.org">www.igalerie.org</a></p>
	</div>

</div>

</body>

</html>
