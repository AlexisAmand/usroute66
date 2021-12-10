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
ob_start('ob_gzhandler');

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
$R = array('t');
foreach ($_GET as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_GET[$name]);
	}
}
$R = array('admin_user', 'admin_pass');
foreach ($_POST as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_POST[$name]);
	}
}
$R = array('');
foreach ($_COOKIE as $name => $value) {
	if (!in_array($name, $R)) {
		unset($_COOKIE[$name]);
	}
}

require_once(dirname(__FILE__) . '/../includes/classes/class.mysql.php');
require_once(dirname(__FILE__) . '/../includes/classes/class.outils.php');
require_once(dirname(__FILE__) . '/../includes/classes/class.cookie.php');
require_once(dirname(__FILE__) . '/../config/conf.php');

// La galerie est-elle installée ?
if (!defined('GALERIE_INSTALL') || !GALERIE_INSTALL) {
	header('Location:../install/');
	exit;
}


$error_incorrect = FALSE;
$error_invalide = FALSE;
$error_cookie = FALSE;

// Si on a entré un identifiant et un mot de passe...
if (isset($_POST['admin_user']) && isset($_POST['admin_pass'])) {

	// Temporisation.
	sleep(1);

	if (preg_match('`^[a-z\d_-]{2,30}$`i', $_POST['admin_user'])
	 && preg_match('`^[a-z\d_-]{6,30}$`i', $_POST['admin_pass'])) {

		// ... on se connecte à la base de données.
		$mysql = new connexion(MYSQL_SERV, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);

		// Si l'identifiant et le mot de passe sont corrects...
		if ($_POST['admin_user'] == ADMIN_USER
		 && $_POST['admin_pass'] == ADMIN_PASS) {

			// ...on génère un identifiant de session qui expirera dans quelques jours
			$session_id = outils::gen_key();
			$session_expire = 3600 * 24 * 14;

			// que l'on enregistre dans la base de données.
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'config SET valeur = "' . $session_id . '" 
				WHERE parametre = "admin_session_id"';
			$mysql->requete($mysql_requete);
			$expire = time() + $session_expire;
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'config SET valeur = "' . $expire . '" 
				WHERE parametre = "admin_session_expire"';
			$mysql->requete($mysql_requete);

			// et dans un cookie.
			$cookie = new cookie($session_expire, 'galerie_sessionid', GALERIE_PATH . '/' . basename(dirname(__FILE__)));
			$cookie->ajouter('session_id', $session_id);

			if ($cookie->ecrire()) {

				// On redirige vers la partie admin.
				$mysql->fermer();
				header('Location:./');
				exit;

			} else {
				$error_cookie = TRUE;
			}

		} else {
			$error_incorrect = TRUE;
		}

		$mysql->fermer();

	} else {
		$error_invalide = TRUE;
	}
}

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: text/html; charset=ISO-8859-15');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">



<head>

<title>Connexion à la galerie</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />

<style type="text/css">
html {
	font-family: Verdana, sans-serif;
	font-size: .8em;
	border: 0; 
}
body,img,form,ul,h1,h2,h3,p {
	border: 0;
	padding: 0;
	margin: 0;
}
body {
	padding: 30px 0 20px;
}
#global {
	width: 300px;
	margin: 0 auto;
	text-align: left;
	border: 1px solid #99B15F;
}
#titre {
	height: 60px;
	background: url(../admin/template/defaut/style/fond_haut.png) repeat-x 0 100%;
}
a {
	color: black;
}
a:hover {
	color: #D58B0B;
	text-decoration: none;
}
h1 {
	font-size: 2em;
	color: #5F6E38;
	letter-spacing: -.05em;
	padding: 8px;
	top: 12px;
	position: relative;
}
#contenu {
	padding: 15px;
}
form {
	border: 1px solid silver;
	border-width: 1px 3px;
	margin: 10px auto 25px;
	padding: 15px 15px 15px 30px;
}
.text {
	background: #F9F8EF;
	padding: 2px 0 2px 3px;
	border: 1px solid silver;
	margin-top: 5px;
	width: 92%;
}
.text:focus {
	border-color: #D58B0B;
}
label {
	text-align: left;
	font-size: 110%;
}
#submit {
	margin-top: 20px;
	padding: 3px 12px;
	font-weight: bold;
}
#retour {
	text-align: right;
}
.erreur {
	background: url(template/defaut/style/erreur.png) no-repeat 0 1px;
	padding: 3px 0 3px 26px;
	font-weight: bold;
	margin-bottom: 20px;
}
.attention {
	background: url(template/defaut/style/attention.png) no-repeat 0 1px;
	padding: 3px 0 3px 26px;
	font-weight: bold;
	margin-bottom: 20px;
}
</style>

</head>


<body onload="document.getElementById('login').focus();">

<div id="global">
	<div id="titre">
		<h1>Connexion</h1>
	</div>
	<div id="contenu">
		<?php
			if ($error_incorrect) { echo '<p class="attention">Renseignements incorrects.</p>'; }
			if ($error_invalide) { echo '<p class="attention">Renseignements invalides.</p>'; }
			if ($error_cookie) { echo '<p class="erreur">Création du cookie échouée.</p>'; }
			if (isset($_GET['t'])) {
				switch ($_GET['t']) {
					case 'expire' : echo '<p class="attention">Votre session a expirée.</p>'; break;
					case 'cookie' : echo '<p class="attention">Cookie de session inexistant.</p>'; break;
					case 'session' : echo '<p class="attention">Identifiant de session incorrect.</p>'; break;
				}
			}
		?>
		<form method="post" action="connexion.php">
			<div>
				<label for="login">Identifiant</label>
				<input maxlength="255" id="login" name="admin_user" class="text" type="text" />
				<br /><br />
				<label for="pass">Mot de passe</label>
				<input maxlength="255" id="pass" name="admin_pass" class="text" type="password" /><br/>
				<input type="submit" id="submit" value="OK" />
			</div>
		</form>
		<div id="retour"><a href="./../">retour galerie</a></div>
	</div>
</div>

</body>


</html>
