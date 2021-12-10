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
				<li<?php $tpl->getPageActuelle('display', ' id="sm_actuel"')?>><a href="index.php?section=commentaires&amp;page=display">Gestion</a></li>
				<li<?php $tpl->getPageActuelle('options')?>><a href="index.php?section=commentaires&amp;page=options">Options</a></li>
				<li<?php $tpl->getPageActuelle('bans')?>><a href="index.php?section=commentaires&amp;page=bans">Bannissements</a></li>
			</ul>
<?php
if (empty($_GET['page'])) {
	$_GET['page'] = 'display';
}
switch ($_GET['page']) {
	case 'display' :
	case 'options' :
	case 'bans' :
		include(dirname(__FILE__) . '/comments_' . $_GET['page'] . '.php');
}
?>
