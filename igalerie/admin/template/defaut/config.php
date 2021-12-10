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
				<li<?php $tpl->getPageActuelle('conf', ' id="sm_actuel"'); ?>><a href="index.php?section=config&amp;page=conf">Configuration</a></li>
				<li<?php $tpl->getPageActuelle('infos_sys'); ?>><a href="index.php?section=config&amp;page=infos_sys">Infos système</a></li>
			</ul>
<?php
if (empty($_GET['page'])) {
	$_GET['page'] = 'config';
}
switch ($_GET['page']) {
	case 'conf' :
	case 'infos_sys' :
		include(dirname(__FILE__) . '/config_' . $_GET['page'] . '.php');
}
?>
