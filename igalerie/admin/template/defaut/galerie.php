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
			<p id="gal_position">
				<?php $tpl->getGaleriePosition(); ?>
				
			</p>

			<div id="gal_actions">
				<form class="gal_action" action="index.php" method="get" id="gal_access_direct">
					<div>
						<input type="hidden" name="section" value="galerie" />
						<input type="hidden" name="page" value="gestion" />
							Accès direct&nbsp;: 
							<?php $tpl->getGalerieHierarchie(); ?>

					</div>
				</form>
				<div id="gal_action_links">
<?php if ($tpl->getObjetType() == 'cat') : ?>
					<a class="lien_jsd" href="javascript:montrer('gal_new_object');"><span>créer une catégorie ou un album</span></a> &nbsp; 
<?php elseif ($tpl->isGD()) : ?>
					<a class="lien_jsd" href="javascript:montrer('gal_ajout_imgs');"><span>ajouter des images</span></a> &nbsp; 
<?php endif; ?>
					<a class="lien_jsd" href="javascript:montrer('gal_display_f');"><span>affichage</span></a>
				</div>
				<form class="gal_action" action="index.php?section=galerie&amp;page=gestion&amp;cat=<?php $tpl->getInfo('cat'); ?>&amp;startnum=<?php $tpl->getInfo('startnum'); ?>" method="post" id="gal_display_f">
					<div id="gal_display">
						<?php $tpl->getVID(); ?>

						Montrer&nbsp;: 
						<select name="filtre">
							<option<?php $tpl->getGalerieFiltre('tous'); ?> value="tous">tous</option>
							<option<?php $tpl->getGalerieFiltre('actif'); ?> value="actif">activés</option>
							<option<?php $tpl->getGalerieFiltre('inactif'); ?> value="inactif">désactivés</option>

						</select> &nbsp;
						<div id="gal_nbco">
							Nb. par page&nbsp;:
							<select name="nb">
								<?php $tpl->getObjetNb('<option value="%s"%s>%s</option>', 50); ?>

							</select>
						</div>
						<div id="gal_sort">
							Trier par&nbsp;:
							<select name="ordre">
								<?php $tpl->getGalerieSort('<option value="%s"%s>%s</option>'); ?>

							</select>
							<select class="asc-desc" name="sens">
								<?php $tpl->getGalerieSens('<option value="%s"%s>%s</option>'); ?>

							</select>
						</div>
						<input class="submit gal_dis_submit" type="submit" value="OK" />
					</div>
				</form>
<?php if ($tpl->getObjetType() == 'cat') : ?>
				<div class="gal_action" id="gal_new_object">
					<form action="index.php?section=galerie&amp;page=gestion&amp;cat=<?php $tpl->getInfo('cat'); ?>" method="post">
						<div>
							<?php $tpl->getVID(); ?>

							Créer&nbsp;:
							&nbsp;
							<label for="gal_new_alb">
								<input name="gal_new_obj" value="alb" id="gal_new_alb" type="radio" /> Album
							</label>
							&nbsp;
							<label for="gal_new_cat">
								<input name="gal_new_obj" value="cat" id="gal_new_cat" type="radio" /> Catégorie
							</label>
							&nbsp;&nbsp;
							<label for="gal_new_name">
								nom&nbsp;: <input name="gal_new_name" id="gal_new_name" class="text" type="text" maxlength="128" size="30" />
							</label>
							<input class="submit gal_dis_submit" type="submit" value="OK" />
						</div>
					</form>
				</div>
<?php elseif ($tpl->isGD()) : ?>
				<div class="gal_action" id="gal_ajout_imgs">
					<a href="javascript:add_upload_imgs();">plus d'images</a>
					<form enctype="multipart/form-data" action="index.php?section=galerie&amp;page=gestion&amp;cat=<?php $tpl->getInfo('cat'); ?>" method="post">
						<div>
							<?php $tpl->getVID(); ?>

							<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
							<div id="gal_ajout_imgs_inputs">
								<div><input type="file" id="file_1" name="file_1" class="gal_files" size="60" maxlength="2048" /></div>
								<div><input type="file" id="file_2" name="file_2" class="gal_files" size="60" maxlength="2048" /></div>
								<div><input type="file" id="file_3" name="file_3" class="gal_files" size="60" maxlength="2048" /></div>
							</div>
							<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_upload_http');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
							<input type="submit" value="envoyer" class="submit" />
							<div id="aide_upload_http" class="aide_contextuelle" style="display:none">
								<div class="aide_barre">
									<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_upload_http');">fermer</a></span>
									<span class="aide_aide">AIDE :</span> <span class="aide_titre">Envoi d'images</span>
								</div>
								<span class="aide_texte">Vous pouvez envoyer deux types de fichiers : images (au format <acronym title="Joint Picture Expert's Group">JPEG</acronym>, <acronym title="Graphics Interchange Format">GIF</acronym> ou <acronym title="Portable Network Graphics">PNG</acronym>) ou archives Zip contenant des images. Chaque fichier ne doit pas faire plus de 2 Mo.<br />Pour savoir si le serveur supporte la gestion des archives Zip, allez dans la section <a href="index.php?section=config&amp;page=infos_sys">Config / Infos système</a> à la ligne « Extension Zip ».</span>
							</div>
						</div>
					</form>
				</div>
<?php endif; ?>
				<script type="text/javascript">
					//<![CDATA[
						var gal_new_object = document.getElementById('gal_new_object');
						var gal_ajout_imgs = document.getElementById('gal_ajout_imgs');
						var gal_display_f = document.getElementById('gal_display_f');
						if (gal_new_object) { gal_new_object.style.display = 'none'; }
						if (gal_ajout_imgs) { gal_ajout_imgs.style.display = 'none'; }
						if (gal_display_f) { gal_display_f.style.display = 'none'; }
					//]]>
				</script>
			</div>

			<?php $tpl->getRapport(); ?>
			<?php $tpl->getGeneralMaj(); ?>

<?php if ($tpl->display('barre_nav')) : ?>
				<div class="barre_nav" id="barre_nav_haut">
					<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
					<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
					<form action="./" method="get">
						<div>
							<input type="hidden" name="section" value="galerie" /><input type="hidden" name="page" value="gestion" /><input type="hidden" name="cat" value="<?php $tpl->getInfo('cat'); ?>" /><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=galerie&amp;page=gestion&amp;cat=<?php $tpl->getInfo('cat'); ?>';"><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
						</div>
					</form>
					<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
					<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
				</div>
<?php endif; ?>

<?php if (!$tpl->display('cat_vide')) : ?>
			<div class="js_coche">
				<a class="lien_js" href="javascript:gal_select_all(1);">tout sélectionner</a>
				- 
				<a class="lien_js" href="javascript:gal_select_all(0);">tout déselectionner</a>
				&nbsp;&nbsp;&nbsp;
				<a class="lien_js" href="javascript:gal_display_all(1);">tout montrer</a>
				- 
				<a class="lien_js" href="javascript:gal_display_all(0);">tout cacher</a>
				&nbsp;pour&nbsp; 
				<select id="display_mode">
					<option value="tout">tout</option>
					<option value="desc">description</option>
<?php if ($tpl->getObjetType() == 'alb') : ?>
					<option value="tags">tags</option>
					<option value="date">date de création</option>
<?php endif; ?>
					<option value="info">informations</option>
				</select>
			</div>

			<form action="index.php?section=galerie&amp;page=gestion&amp;cat=<?php $tpl->getInfo('cat'); ?>&amp;startnum=<?php $tpl->getInfo('startnum'); ?>" method="post" onsubmit="return confirm_albums_mass('<?php $tpl->getObjetInfo('f_type'); ?>');">
				<div>
					<?php $tpl->getVID(); ?>

					<input type="hidden" name="section" value="galerie" />
					<input type="hidden" name="page" value="gestion" />
					<input type="hidden" name="cat" value="<?php $tpl->getInfo('cat'); ?>" />
					<input type="hidden" name="startnum" value="<?php $tpl->getInfo('startnum'); ?>" />
				</div>
				<div class="submit_changes" id="submit_changes_haut"><input name="mass_change" type="submit" class="submit" value="valider les changements" /></div>
			
<?php while ($tpl->getNextObjet()) : ?>
				<div>
					<input type="hidden" name="f_type[<?php $tpl->getObjetInfo('id'); ?>]" value="<?php $tpl->getObjetInfo('f_type'); ?>" />
					<input type="hidden" name="objet_type[<?php $tpl->getObjetInfo('id'); ?>]" value="<?php $tpl->getObjetInfo('type'); ?>" />
				</div>
				<div class="gal_objet gal_categorie<?php $tpl->getObjetInfo('is_inactive', ' gal_objet_inactif'); ?>">
						<div class="gal_objet_top">
							<div class="gal_objet_thumb" onmouseover="this.style.cursor='pointer'" onclick="window.location='<?php $tpl->getObjetInfo('tb_onclick'); ?>'">
								<table><tr><td><?php $tpl->getObjetInfo('tb_cat', '<a href="%1$s"><img %2$s alt="%3$s" title="Gérer %4$s \'%3$s\'" src="%5$s" /></a>', 100); ?><?php $tpl->getObjetInfo('tb_img', '<a href="%1$s"><img %2$s alt="%3$s" title="Ouvrir l\'image \'%3$s\'" src="%4$s" /></a>', 100); ?></td></tr></table>
							</div>
							<div class="gal_objet_details">
							<div class="gal_objet_details_bis">
								<div class="gal_objet_nom">
									<div class="gal_objet_type">
										<?php $tpl->getObjetInfo('type'); ?>
										<label for="objet_id_<?php $tpl->getObjetInfo('id'); ?>"><?php $tpl->getObjetInfo('id'); ?></label>
										<input name="objet_id[<?php $tpl->getObjetInfo('id'); ?>]" id="objet_id_<?php $tpl->getObjetInfo('id'); ?>" type="checkbox" />
									</div>
									<label for="nom_<?php $tpl->getObjetInfo('id'); ?>"><strong>Nom</strong>&nbsp;: </label>
									<input class="text" maxlength="255" size="60" name="nom[<?php $tpl->getObjetInfo('id'); ?>]" id="nom_<?php $tpl->getObjetInfo('id'); ?>" type="text" value="<?php $tpl->getObjetInfo('nom'); ?>" />
								</div>
								<div class="gal_objet_de">
									<?php $tpl->getObjetInfo('delete', '<span class="gal_odel">%s</span>'); ?>

									<?php $tpl->getObjetInfo('etat', '<span>%s</span>'); ?>
								</div>
								<div class="gal_objet_milieu">

									<?php $tpl->getObjetInfo('password', '<div class="gal_objet_password"><label for="password_%1$s">Mot de passe&nbsp;:</label> <input class="text" id="password_%1$s" name="password[%1$s]" type="text" maxlength="255" size="40"  value="%2$s" /></div>'); ?>

									<?php $tpl->getObjetInfo('file_name', 'Nom de fichier&nbsp;: <input class="text" maxlength="255" size="60" name="file_name[%1$d]" id="file_name_%1$d" type="text" value="%2$s" />'); ?>

								</div>
								<div class="gal_objet_actions">
									<?php $tpl->getObjetInfo('thumb', '<span class="gal_objet_lien_tb">%s</span>'); ?>
									<?php $tpl->getObjetInfo('deplace_cat_lien', '<span class="gal_objet_actions_lien"><a class="lien_jsd" href="javascript:gal_objet_dmc(\'move\',%s)"><span>déplacer</span></a></span>'); ?>
									<span class="gal_objet_actions_lien"><a class="lien_jsd" href="javascript:gal_objet_dmc('desc',<?php $tpl->getObjetInfo('id'); ?>)"><span>description</span></a></span>
									<?php $tpl->getObjetInfo('tags', '<span class="gal_objet_actions_lien"><a class="lien_jsd" href="javascript:gal_objet_dmc(\'tags\',%s)"><span>tags</span></a></span>'); ?>
									<?php $tpl->getObjetInfo('date_creation', '<span class="gal_objet_actions_lien"><a class="lien_jsd" href="javascript:gal_objet_dmc(\'date\',%s)"><span>date de création</span></a></span>'); ?>
									<span class="gal_objet_actions_lien"><a class="lien_jsd" href="javascript:gal_objet_dmc('info',<?php $tpl->getObjetInfo('id'); ?>)"><span>informations</span></a></span>
								</div>
							</div>
							</div>
						</div>
<?php if ($tpl->getObjetType() == 'cat') : ?>
						<div style="display:none" class="gal_objet_depli gal_objet_deplace" id="gal_objet_move_<?php $tpl->getObjetInfo('id'); ?>">
							<span class="gal_objet_it">Déplacer vers&nbsp;:</span>
							<div class="gal_objet_move">
								<?php $tpl->getObjetInfo('deplace_cat', '%s <input name="deplacer_cat[%s]" type="submit" value="OK" />'); ?>

							</div>
						</div>
<?php endif; ?>
						<div style="display:none" class="gal_objet_depli gal_objet_description" id="gal_objet_desc_<?php $tpl->getObjetInfo('id'); ?>">
							<span class="gal_objet_it">Description <span title="Vous pouvez utiliser du code HTML dans ce texte" class="text_html">(HTML)</span>&nbsp;:</span>
							<textarea name="description[<?php $tpl->getObjetInfo('id'); ?>]" rows="6" cols="50"><?php $tpl->getObjetInfo('description'); ?></textarea>
						</div>
<?php if ($tpl->getObjetType() == 'alb') : ?>
						<div style="display:none" class="gal_objet_depli gal_objet_tags" id="gal_objet_tags_<?php $tpl->getObjetInfo('id'); ?>">
							<span class="gal_objet_it">Tags (séparés par une virgule)&nbsp;:</span>
							<textarea name="tags[<?php $tpl->getObjetInfo('id'); ?>]" rows="3" cols="50"><?php $tpl->getObjetInfo('image_tags'); ?></textarea>
						</div>
						<div style="display:none" class="gal_objet_depli gal_objet_datecreation" id="gal_objet_date_<?php $tpl->getObjetInfo('id'); ?>">
							<span class="gal_objet_it">Date de création (les trois champs sont requis)&nbsp;:</span>
							<?php $tpl->getDateCreation(); ?>

						</div>
<?php endif; ?>
						<div style="display:none" class="gal_objet_depli gal_objet_infos" id="gal_objet_info_<?php $tpl->getObjetInfo('id'); ?>">
							<span class="gal_objet_it">Informations&nbsp;:</span>
							<?php $tpl->getObjetInfo('infos'); ?>

						</div>
				</div>
<?php endwhile; ?>
				<div class="submit_changes" id="submit_changes_bas"><input name="mass_change" type="submit" class="submit" value="valider les changements" /></div>

				<p class="gal_mass_action">
					pour la sélection&nbsp;:
					<select id="gal_mass_action_select" name="action">
						<option value="activer">activer</option>
						<option value="desactiver">désactiver</option>
						<option value="supprimer">supprimer</option>
					</select>
					<input name="gal_mass_action" class="submit" type="submit" value="OK" />
				</p>
				<?php $tpl->getObjetInfo('deplace_img', '<p class="gal_mass_action gal_images_deplace">déplacer la sélection vers&nbsp;: %s <input name="gal_deplacer_imgs" class="submit" type="submit" value="OK" /></p>'); ?>

			</form>
<?php else : ?>
			<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
<?php endif; ?>

<?php if ($tpl->display('barre_nav')) : ?>
				<div class="barre_nav" id="barre_nav_bas">
					<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
					<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
					<form action="./" method="get">
						<div>
							<input type="hidden" name="section" value="galerie" /><input type="hidden" name="page" value="gestion" /><input type="hidden" name="cat" value="<?php $tpl->getInfo('cat'); ?>" /><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=galerie&amp;page=gestion&amp;cat=<?php $tpl->getInfo('cat'); ?>';"><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
						</div>
					</form>
					<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
					<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
				</div>
<?php endif; ?>
