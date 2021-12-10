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

<title><?php $tpl->getInfo('title'); ?> - iGalerie</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />

<link rel="stylesheet" type="text/css" media="screen" title="style" href="template/defaut/style/style.css" />

<script type="text/javascript" src="admin.js"></script>

</head>


<body id="section_<?php if (isset($_REQUEST['section'])) echo $_REQUEST['section']; ?><?php if (isset($_REQUEST['page'])) echo '_' . $_REQUEST['page']; ?>">

<div id="global">

	<div id="haut">
		<div id="liens_haut">
			<span id="ouverture" class="top_links"><?php $tpl->getGalActiveLink(); ?></span>
			<span id="infos" class="top_links"><a href="index.php?section=infos">infos</a></span>
			<span id="galerie" class="top_links"><a href="<?php echo $tpl->getGalerieURL(); ?>">voir la galerie</a></span>
			<span id="deconnect"><a href="index.php?section=galerie&amp;igal_admin_deconnect=1<?php $tpl->getVID(1); ?>">déconnexion</a></span>
		</div>
		<h1>iGalerie :: admin</h1>
	</div>

	<ul id="menu">
		<li<?php $tpl->getSectionActuel('ftp', ' id="actuel"'); ?>><a href="index.php?section=ftp" id="premier">Ajout d'images</a></li>
		<li<?php $tpl->getSectionActuel('galerie representant'); ?>><a title="Gestion des catégories, albums et images" href="index.php?section=galerie">Albums</a></li>
		<li<?php $tpl->getSectionActuel('commentaires'); ?>><a href="index.php?section=commentaires">Commentaires</a></li>
		<li<?php $tpl->getSectionActuel('votes'); ?>><a href="index.php?section=votes">Votes</a></li>
		<li<?php $tpl->getSectionActuel('tags'); ?>><a href="index.php?section=tags">Tags</a></li>
		<li<?php $tpl->getSectionActuel('utilisateurs'); ?>><a href="index.php?section=utilisateurs">Utilisateurs</a></li>
		<li<?php $tpl->getSectionActuel('options'); ?>><a href="index.php?section=options">Options</a></li>
		<li<?php $tpl->getSectionActuel('outils'); ?>><a href="index.php?section=outils">Outils</a></li>
		<li<?php $tpl->getSectionActuel('config'); ?>><a href="index.php?section=config">Configuration</a></li>
	</ul>

	<div id="contenu">
		<div id="contenu_int">
<?php
switch ($_REQUEST['section']) {
	case 'ftp' :
	case 'galerie' :
	case 'representant' :
	case 'commentaires' :
	case 'votes' :
	case 'tags' :
	case 'utilisateurs' :
	case 'options' :
	case 'outils' :
	case 'config' :
	case 'infos' :
		include(dirname(__FILE__) . '/' . $_REQUEST['section'] . '.php');
		break;
}
?>

		</div>
	</div>

	<div id="bas">
		<p>propulsé par <strong><a class="ex" href="http://www.igalerie.org/">iGalerie</a></strong> <?php $tpl->getGalerieVersion(); ?> | site:<a class="ex" href="http://www.igalerie.org/documentation.php">documentation</a> | site:<a class="ex" href="http://www.igalerie.org/forum/">forum</a></p>
<?php
global $TIME_START;
global $_MYSQL;
list ($m2, $t2) = explode(' ', microtime());
$time_total = ($m2 + $t2) - ($TIME_START[0] + $TIME_START[1]);
$queries = ($_MYSQL['debug']) ? ' avec %d requêtes SQL' : '';
echo sprintf("\t\t" . '<p>page générée en %.3f seconde%s' . $queries . '</p>', $time_total, ($time_total >= 2) ? 's' : '', $_MYSQL['nb_requetes']);
?>

	</div>

	
</div>

<?php
if ($_MYSQL['mysql_requetes']) {
	echo '<hr /><div style="padding: 15px;font-size:12px;text-align:left;">';
	for ($i = 0; $i < count($admin->mysql->requetes); $i++) {
		$q = (strpos($admin->mysql->requetes[$i], '[ERREUR]')) ? $admin->mysql->requetes[$i] : htmlentities($admin->mysql->requetes[$i]);
		echo '[' . $i . '] ' . $q . '<br /><br />';
	}
	echo '</div>';
}
?>
<script type="text/javascript">
//<![CDATA[
// On cache les boutons submit des listes déroulantes auto.
var inputs = document.getElementsByTagName('input');
for (var i = 0; i < inputs.length; i++) {
	if (inputs[i].className.search(/js_auto/) != -1) {
		inputs[i].style.display = 'none';
	}
}
//]]>
</script>


</body>


</html>
