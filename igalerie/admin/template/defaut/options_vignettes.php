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
				<form action="index.php?section=options&amp;page=vignettes" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getRapport('%s<br/>'); ?>
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<div id="param_general">
							<fieldset>
								<legend>Affichage des vignettes des catégories/albums</legend>
								<div class="fielditems">
									<p class="field">
										Nombre de vignettes par page&nbsp;:
										<select name="g_tb_cat_vn">
											<?php $tpl->getNbThumbs('cat_col'); ?>

										</select>
										&nbsp;X&nbsp;
										<select name="g_tb_cat_vl">
											<?php $tpl->getNbThumbs('cat_line', 20); ?>

										</select>
									</p>
									<p class="field">
										Trier les vignettes par&nbsp;:
										<select name="g_cat_ordre">
											<option<?php $tpl->getCatThumbSort('nom'); ?> value="nom">Nom</option>
											<option<?php $tpl->getCatThumbSort('date'); ?> value="date">Date d'ajout</option>
										</select>
										<select name="g_cat_sens">
											<option<?php $tpl->getCatThumbSens('ASC'); ?> value="ASC">croissant</option>
											<option<?php $tpl->getCatThumbSens('DESC'); ?> value="DESC">décroissant</option>
										</select>
									</p>
									<p class="field">
										Afficher les vignettes
										<select name="g_tb_cat_type">
											<?php $tpl->getTbCatType(); ?>

										</select>
									</p>
									<p class="field">Dimensions des vignettes (50 à 300 pixels)&nbsp;:</p>
									<div class="field_second">
										<p class="field">
											<input<?php $tpl->getTbSizeMode('alb', 'size'); ?> id="g_tb_alb_max" type="radio" name="g_tb_alb_mode" value="size" />
											<label for="g_tb_alb_max">Taille maximale proportionnelle&nbsp;:</label>
											<input type="text" class="text text3" maxlength="3" name="g_tb_alb_size" id="g_tb_alb_size" value="<?php $tpl->getConfig('g_size_tb_alb'); ?>" />
										</p>
										<p class="field">
											<input<?php $tpl->getTbSizeMode('alb', 'crop'); ?> id="g_tb_alb_crop" type="radio" name="g_tb_alb_mode" value="crop" />
											<label for="g_tb_alb_crop">Forcer les vignettes à toujours occuper un espace de&nbsp;:</label>
											<input type="text" class="text text3" maxlength="3" name="g_tb_alb_crop_width" id="g_tb_alb_crop_width" value="<?php $tpl->getConfig('g_tb_alb_crop_width'); ?>" />
											&nbsp;X&nbsp;
											<input type="text" class="text text3" maxlength="3" name="g_tb_alb_crop_height" id="g_tb_alb_crop_height" value="<?php $tpl->getConfig('g_tb_alb_crop_height'); ?>" />
										</p>
									</div>
									<p class="field<?php $tpl->getTextDisabled('thumbs_display_mode', ' disabled'); ?>">
										Présentation des vignettes&nbsp;:
										<select name="g_tb_cat_mode"<?php $tpl->getInputDisabled('thumbs_display_mode'); ?>>
											<?php $tpl->getTbCatMode(); ?>

										</select>
										<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_thumb_mode');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
									</p>
									<div id="aide_thumb_mode" class="aide_contextuelle" style="display:none">
										<div class="aide_barre">
											<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_thumb_mode');">fermer</a></span>
											<span class="aide_aide">AIDE :</span> <span class="aide_titre">présentation des vignettes</span>
										</div>
										<span class="aide_texte">Cette fonction permet de changer le mode d'affichage des vignettes des albums et catégories. Le mode "compact" (mode par défaut) affiche les vignettes de la même manière que les vignettes des images. Le mode "étendu" permet à chaque vignette de prendre une ligne entière, les informations s'affichant à droite et non plus en dessous de la vignette. En outre, ce mode affiche la description de l'album à droite de la vignette, et non plus sur la page de l'album.</span>
									</div>
								</div>
							</fieldset>
							<br/>
							<fieldset>
								<legend>Affichage des vignettes des images</legend>
								<div class="fielditems">
									<p class="field">
										Nombre de vignettes par page&nbsp;:
										<select name="g_tb_img_vn">
											<?php $tpl->getNbThumbs('col'); ?>

										</select>
										&nbsp;X&nbsp;
										<select name="g_tb_img_vl">
											<?php $tpl->getNbThumbs('line', 20); ?>

										</select>
									</p>
									<p class="field">
									Trier les vignettes par&nbsp;:
										<select name="g_ordre">
											<option<?php $tpl->getThumbSort('chemin'); ?> value="chemin">Nom de fichier</option>
											<option<?php $tpl->getThumbSort('nom'); ?> value="nom">Nom</option>
											<option<?php $tpl->getThumbSort('poids'); ?> value="poids">Poids</option>
											<option<?php $tpl->getThumbSort('largeur*image_hauteur'); ?> value="largeur*image_hauteur">Taille</option>
											<option<?php $tpl->getThumbSort('hits'); ?> value="hits">Visites</option>
											<option<?php $tpl->getThumbSort('date'); ?> value="date">Date d'ajout</option>
											<option<?php $tpl->getThumbSort('date_creation'); ?> value="date_creation">Date de création</option>
											<option<?php $tpl->getThumbSort('commentaires'); ?> value="comments">Commentaires</option>
											<option<?php $tpl->getThumbSort('votes'); ?> value="votes">Votes</option>
											<option<?php $tpl->getThumbSort('note'); ?> value="note">Note</option>
										</select>
										<select name="g_sens">
											<option<?php $tpl->getThumbSens('ASC'); ?> value="ASC">croissant</option>
											<option<?php $tpl->getThumbSens('DESC'); ?> value="DESC">décroissant</option>
										</select>
									</p>
									<p class="field">Dimensions des vignettes (50 à 300 pixels)&nbsp;:</p>
									<div class="field_second">
										<p class="field">
											<input<?php $tpl->getTbSizeMode('img', 'size'); ?> id="g_tb_img_max" type="radio" name="g_tb_img_mode" value="size" />
											<label for="g_tb_img_max">Taille maximale proportionnelle&nbsp;:</label>
											<input type="text" class="text text3" maxlength="3" name="g_tb_img_size" id="g_tb_img_size" value="<?php $tpl->getConfig('g_size_tb_img'); ?>" />
										</p>
										<p class="field">
											<input<?php $tpl->getTbSizeMode('img', 'crop'); ?> id="g_tb_img_crop" type="radio" name="g_tb_img_mode" value="crop" />
											<label for="g_tb_img_crop">Forcer les vignettes à toujours occuper un espace de&nbsp;:</label>
											<input type="text" class="text text3" maxlength="3" name="g_tb_img_crop_width" id="g_tb_img_crop_width" value="<?php $tpl->getConfig('g_tb_img_crop_width'); ?>" />
											&nbsp;X&nbsp;
											<input type="text" class="text text3" maxlength="3" name="g_tb_img_crop_height" id="g_tb_img_crop_height" value="<?php $tpl->getConfig('g_tb_img_crop_height'); ?>" />
										</p>
									</div>
								</div>
							</fieldset>
							<br/>
							<div class="js_coche">
								<a class="lien_js" href="javascript:inputCheck(1,'vignettes_info');">tout cocher</a> - <a class="lien_js" href="javascript:inputCheck(0,'vignettes_info');">tout décocher</a>
							</div>
							<fieldset class="checkbox<?php $tpl->getTextDisabled('thumbs_infos', ' disabled'); ?>">
								<legend>Informations sous les vignettes des catégories/albums</legend>
								<div class="fielditems">
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_c_nom" name="g_info_c_nom" type="checkbox"<?php $tpl->getThumbInfo('cat_nom'); ?> />
										<label for="g_info_c_nom"> Nom</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_c_imgs" name="g_info_c_imgs" type="checkbox"<?php $tpl->getThumbInfo('cat_nb_images'); ?> />
										<label for="g_info_c_imgs"> Nombre d'images</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_c_poids" name="g_info_c_poids" type="checkbox"<?php $tpl->getThumbInfo('cat_poids'); ?> />
										<label for="g_info_c_poids"> Poids des albums</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_c_hits" name="g_info_c_hits" type="checkbox"<?php $tpl->getThumbInfo('cat_hits'); ?> />
										<label for="g_info_c_hits"> Nombre de visites</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_c_comments" name="g_info_c_comments" type="checkbox"<?php $tpl->getThumbInfo('cat_comments'); ?> />
										<label for="g_info_c_comments"> Nombre de commentaires</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_c_votes" name="g_info_c_votes" type="checkbox"<?php $tpl->getThumbInfo('cat_votes'); ?> />
										<label for="g_info_c_votes"> Note moyenne</label>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset class="checkbox<?php $tpl->getTextDisabled('thumbs_infos', ' disabled'); ?>">
								<legend>Informations sous les vignettes des images</legend>
								<div class="fielditems">
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_i_nom" name="g_info_i_nom" type="checkbox"<?php $tpl->getThumbInfo('img_nom'); ?> />
										<label for="g_info_i_nom"> Nom de l'image</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_i_date" name="g_info_i_date" type="checkbox"<?php $tpl->getThumbInfo('img_date'); ?> />
										<label for="g_info_i_date"> Date de mise en ligne</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_i_taille" name="g_info_i_taille" type="checkbox"<?php $tpl->getThumbInfo('img_taille'); ?> />
										<label for="g_info_i_taille"> Taille</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_i_poids" name="g_info_i_poids" type="checkbox"<?php $tpl->getThumbInfo('img_poids'); ?> />
										<label for="g_info_i_poids"> Poids des images</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_i_hits" name="g_info_i_hits" type="checkbox"<?php $tpl->getThumbInfo('img_hits'); ?> />
										<label for="g_info_i_hits"> Nombre de visites</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_i_comments" name="g_info_i_comments" type="checkbox"<?php $tpl->getThumbInfo('img_comments'); ?> />
										<label for="g_info_i_comments"> Nombre de commentaires</label>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('thumbs_infos'); ?> class="vignettes_info" id="g_info_i_votes" name="g_info_i_votes" type="checkbox"<?php $tpl->getThumbInfo('img_votes'); ?> />
										<label for="g_info_i_votes"> Note moyenne</label>
									</p>
								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>