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
				<?php $tpl->getRapport('%s<br/>'); ?>
				<?php $tpl->getGeneralMaj('%s<br/>'); ?>

				<div id="users_groupe_nouveau">
					<form action="index.php?section=utilisateurs&amp;page=groupes" method="post">
						<div>
							<?php $tpl->getVID(); ?>

							<label for="new_group">créer un nouveau groupe&nbsp;:</label>
							<input size="30" type="text" class="text" maxlength="255" id="new_group" name="new_group" />
							<input type="submit" class="submit" value="valider" />
						</div>
					</form>
					<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_groupes');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
				</div>

				<div id="aide_groupes" class="aide_contextuelle" style="display:none">
					<span class="aide_barre">
						<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_groupes');">fermer</a></span>
						<span class="aide_aide">AIDE :</span> <span class="aide_titre">groupes</span>
					</span>
					<span class="aide_texte">
						iGalerie comporte trois groupes de base qu'on ne peut pas supprimer. Le groupe "admin" ne contient et ne peut contenir qu'un seul utilisateur : l'administrateur d'iGalerie, c'est à dire vous. Il n'est pas possible de modifier les droits de ce groupe pour la simple raison que l'administrateur dispose par définition de tous les droits.<br /><br />
						Tous les utilisateurs non enregistrés font partie du groupe "invités". Ce groupe ne dispose pas des fonctionnalités "newsletter" et "envoi d'images".<br /><br />
						Les utilisateurs qui s'enregistreront feront automatiquement partie du groupe "membres". Vous devez donc définir les droits qui s'appliqueront par défaut pour tout nouveau membre.<br /><br />
						Vous pouvez par la suite créer de nouveaux groupes et définir des droits différents pour chacun d'eux. Le changement de groupe d'un membre s'effectue sur la page des membres, via la liste déroulante lui correspondant.
					</span>
				</div>

				<div id="users_groupes">

					<table>
						<tr>
							<th>Nom du groupe</th>
							<th>Titre</th>
							<th>Nombre de membres</th>
							<th>Actions</th>
						</tr>
<?php while ($tpl->getNextGroup()) : ?>
						<tr<?php $tpl->getGroupe('special', ' class="users_groupes_special"'); ?>>
							<td class="users_groupes_nom"><?php $tpl->getGroupe('nom'); ?></td>
							<td class="users_groupes_titre"><?php $tpl->getGroupe('titre'); ?></td>
							<td class="users_groupes_nombre"><?php $tpl->getGroupe('nb_membres'); ?></td>
							<td class="users_groupes_actions"><?php $tpl->getGroupe('actions', '%s %s'); ?></td>
						</tr>
<?php endwhile; ?>
					</table>

				</div>
