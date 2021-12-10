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
			<p id="users_images_position"><?php $tpl->getImagesAtt('position'); ?></p>

			<div id="co_perso">
				<form action="index.php?section=utilisateurs&amp;page=images&amp;startnum=<?php $tpl->getInfo('startnum'); ?>" method="post">
					<div id="co_display">
						<?php $tpl->getVID(); ?>

						<div id="co_nbco">
							Nb. par page&nbsp;:
							<select name="nb">
								<?php $tpl->getImagesAtt('nb_images', '<option value="%s"%s>%s</option>', 100); ?>

							</select>
						</div>
						<div id="co_sort">
							Trier par&nbsp;:
							<select name="sort">
								<option<?php $tpl->getImagesAtt('ordre', 'date_envoi'); ?> value="date_envoi">date d'envoi</option>
								<option<?php $tpl->getImagesAtt('ordre', 'album'); ?> value="album">album</option>
								<option<?php $tpl->getImagesAtt('ordre', 'membre'); ?> value="membre">membre</option>
							</select>
							<select class="asc-desc" name="sens">
								<option<?php $tpl->getImagesAtt('sens', 'ASC'); ?> value="ASC">croissant</option>
								<option<?php $tpl->getImagesAtt('sens', 'DESC'); ?> value="DESC">décroissant</option>
							</select>
						</div>
						<input class="submit co_dis_submit" type="submit" value="OK" />
					</div>
				</form>
			</div>

			<div class="js_coche">
				<a class="lien_js" href="javascript:imgatt_select_all(1);">tout sélectionner</a>
				- 
				<a class="lien_js" href="javascript:imgatt_invert_select();">inverser la sélection</a>
				&nbsp;&nbsp;&nbsp;
				<a class="lien_js" href="javascript:imgatt_details_all(1);">tout montrer</a>
				- 
				<a class="lien_js" href="javascript:imgatt_details_all(0);">tout cacher</a>

			</div>

<?php if ($tpl->display('barre_nav')) : ?>
			<div class="barre_nav" id="barre_nav_haut">
				<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
				<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
				<form class="js_auto" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
					<div>
						<input type="hidden" name="section" value="utilisateurs" /><input type="hidden" name="page" value="images" /><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=utilisateurs&amp;page=images';"><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
					</div>
				</form>
				<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
				<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
			</div>
<?php endif; ?>

				<?php $tpl->getRapport('%s<br/>'); ?>
				<?php $tpl->getGeneralMaj('%s<br/>'); ?>

				<form action="index.php?section=utilisateurs&amp;page=images" method="post" onsubmit="return confirm_imgatt_delete();">
					<div>
						<?php $tpl->getVID(); ?>

<?php while ($tpl->getNextImageAtt()) : ?>
						<div class="users_images">
							<div class="users_images_haut">
								<div class="users_images_thumb" onmouseover="this.style.cursor='pointer'" onclick="window.location='<?php $tpl->getImageAtt('lien'); ?>'">
									<table><tr><td><?php $tpl->getImageAtt('thumb', '%s', 50); ?></td></tr></table>
								</div>
								<div class="users_images_infos">
								<div class="users_images_infos_bis">
									<div class="users_images_checkbox"><input name="images[]" value="<?php $tpl->getImageAtt('id'); ?>" type="checkbox" /></div>
									<div class="users_images_album"><?php $tpl->getImageAtt('album', 'album : %s'); ?></div>
									<div class="users_images_display_details"><a class="lien_jsd" href="javascript:imgatt_details(<?php $tpl->getImageAtt('id'); ?>);"><span>détails</span></a></div>
									<span class="users_images_nom"><?php $tpl->getImageAtt('user_nom', 'envoyé par %s'); ?></span> <span class="users_images_ip">(<?php $tpl->getImageAtt('ip'); ?>)</span>
									
								</div>
								</div>
							</div>
							<div style="display:none" class="users_images_details" id="users_images_details_<?php $tpl->getImageAtt('id'); ?>">
								<div>
								<table>
									<tr><td>fichier</td><td><?php $tpl->getImageAtt('fichier'); ?></td></tr>
									<tr><td>date&nbsp;d'envoi</td><td><?php $tpl->getImageAtt('date'); ?></td></tr>
									<tr><td>nom</td><td><?php $tpl->getImageAtt('nom'); ?></td></tr>
									<tr><td>description</td><td><?php $tpl->getImageAtt('desc'); ?></td></tr>
									<tr><td>type</td><td><?php $tpl->getImageAtt('type'); ?></td></tr>
									<tr><td>poids</td><td><?php $tpl->getImageAtt('poids'); ?></td></tr>
									<tr><td>dimensions</td><td><?php $tpl->getImageAtt('taille'); ?></td></tr>
								</table>
								</div>
							</div>
						</div>
<?php endwhile; ?>
<?php if ($tpl->getImagesAtt('noimages')) : ?>
					<div class="rapport_msg rapport_infos"><div><span>Aucune image en attente.</span></div></div>
					<br /><br /><br />
<?php endif; ?>
					</div>
					<div id="users_images_mass">
						<label for="users_images_action">pour la sélection&nbsp;:</label>
						<select id="users_images_action" name="users_images_action">
							<option value="valider">valider</option>
							<option value="supprimer">supprimer</option>
						</select>
						<input name="mass_action" type="submit" class="submit" value="OK" />
					</div>
				</form>

<?php if ($tpl->display('barre_nav')) : ?>
			<div class="barre_nav" id="barre_nav_bas">
				<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
				<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
				<form class="js_auto" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
					<div>
						<input type="hidden" name="section" value="utilisateurs" /><input type="hidden" name="page" value="images" /><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=utilisateurs&amp;page=images';"><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
					</div>
				</form>
				<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
				<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
			</div>
<?php endif; ?>