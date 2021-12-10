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
			<h2>Boîte à outils</h2>
			<br />
			<div class="aide_fixe">
				<span class="aide_texte">
					Les outils suivant peuvent résoudre certains problèmes.<br />
					Notez que ces opérations peuvent être longue si vous avez un grand nombre d'albums et d'images.
				</span>
			</div>
			<br />
			<?php $tpl->display('rapport'); ?>
			<?php $tpl->getRapport('%s<br />'); ?>

			<fieldset class="outils_action_liens">
				<legend>Base de données</legend>
				<div class="fielditems">
					<span><a href="index.php?section=outils&amp;page=images&amp;action=verif_infos<?php $tpl->getVID(1); ?>">Vérifier les informations des albums</a></span>
					<span><a href="index.php?section=outils&amp;page=images&amp;action=repare_images<?php $tpl->getVID(1); ?>">Vérifier l'intégrité de la table des images</a></span>
					<span><a href="index.php?section=outils&amp;page=images&amp;action=optimize_tables<?php $tpl->getVID(1); ?>">Optimiser les tables</a></span>
				</div>
			</fieldset>
			<br />
			<fieldset class="outils_action_liens">
				<legend>Opérations sur le disque</legend>
				<div class="fielditems">
					<span><a href="index.php?section=outils&amp;page=images&amp;action=vide_cache<?php $tpl->getVID(1); ?>">Vider le répertoire cache</a></span>
					<span><a href="index.php?section=outils&amp;page=images&amp;action=supp_thumb<?php $tpl->getVID(1); ?>">Supprimer toutes les vignettes</a></span>
					<span><a href="index.php?section=outils&amp;page=images&amp;action=change_date<?php $tpl->getVID(1); ?>">Changer la date de dernière modification de chaque répertoire</a></span>
				</div>
			</fieldset>
			<br />
			<br />

