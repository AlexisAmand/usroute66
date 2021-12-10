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
			<ul id="sous_menu">
				<li<?php $tpl->getPageActuelle('general', ' id="sm_actuel"'); ?>><a href="index.php?section=options&amp;page=general">Général</a></li>
				<li<?php $tpl->getPageActuelle('vignettes'); ?>><a href="index.php?section=options&amp;page=vignettes">Vignettes</a></li>
				<li<?php $tpl->getPageActuelle('images'); ?><?php $tpl->getPageActuelle('infos_exif'); ?><?php $tpl->getPageActuelle('infos_iptc'); ?><?php $tpl->getPageActuelle('itext_params'); ?>><a href="index.php?section=options&amp;page=images">Images</a></li>
				<li<?php $tpl->getPageActuelle('textes'); ?>><a href="index.php?section=options&amp;page=textes">Textes</a></li>
				<li<?php $tpl->getPageActuelle('fonctions'); ?>><a href="index.php?section=options&amp;page=fonctions">Fonctionnalités</a></li>
				<li<?php $tpl->getPageActuelle('perso'); ?>><a href="index.php?section=options&amp;page=perso">Personnalisation</a></li>
			</ul>
<?php
if (empty($_GET['page'])) {
	$_GET['page'] = 'general';
}
switch ($_GET['page']) {
	case 'fonctions' :
	case 'general' :
	case 'images' :
	case 'infos_exif' :
	case 'infos_iptc' :
	case 'itext_params' :
	case 'perso' :
	case 'textes' :
	case 'vignettes' :
		include(dirname(__FILE__) . '/options_' . $_GET['page'] . '.php');
		break;
}
?>
