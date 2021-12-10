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
				<form action="index.php?section=options&amp;page=images" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getRapport('%s<br/>'); ?>
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<div id="param_general">
							<fieldset class="checkbox">
								<legend>Mode d'affichage des images</legend>
								<div class="fielditems">
									<p class="field">
										<input id="g_img_direct" name="g_img" value="1" type="radio"<?php $tpl->getImgDisplay(1); ?> />
										<label for="g_img_direct"> direct</label>
									</p>
									<p class="field">
										<input id="g_img_normal" name="g_img" value="0" type="radio"<?php $tpl->getImgDisplay(0); ?> />
										<label for="g_img_normal"> dans une page dédiée</label>
									</p>
									<p class="field">
										<input id="g_img_window" name="g_img" value="2" type="radio"<?php $tpl->getImgDisplay(2); ?> />
										<label for="g_img_window"> dans une nouvelle fenêtre</label>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset id="image_resize">
								<legend>Redimensionnement des images</legend>
								<div class="fielditems">
									<p class="field">
										<input id="g_resize_reel" name="g_img_resize" value="0" type="radio"<?php $tpl->getImgResize('mode', 0); ?> />
										<label for="g_resize_reel"> taille réelle</label>
									</p>
									<p class="field">
										<input id="g_resize_max_html" name="g_img_resize" value="1" type="radio"<?php $tpl->getImgResize('mode', 1); ?> />
										<label for="g_resize_max_html"> taille maximale (HTML)&nbsp;:</label>
										<label for="g_resize_max_html_largeur">L. </label>
										<input type="text" class="text text4 i_resize" id="g_resize_max_html_largeur" maxlength="4" name="g_resize_max_html_largeur" value="<?php $tpl->getImgResize('html', 'l'); ?>" />
										&nbsp;X&nbsp;
										<label for="g_resize_max_html_hauteur">H. </label>
										<input type="text" class="text text4 i_resize" id="g_resize_max_html_hauteur" maxlength="4" name="g_resize_max_html_hauteur" value="<?php $tpl->getImgResize('html', 'h'); ?>" />
										&nbsp;pixels
									</p>
<?php if ($tpl->isGD()) : ?>
									<p class="field">
										<label for="g_resize_max_gd">
											<input id="g_resize_max_gd" name="g_img_resize" value="2" type="radio"<?php $tpl->getImgResize('mode', 2); ?> /> taille maximale (GD)&nbsp;:
										</label>
										<label for="g_resize_max_gd_largeur">
											L. <input type="text" class="text text4 i_resize" id="g_resize_max_gd_largeur" maxlength="4" name="g_resize_max_gd_largeur" value="<?php $tpl->getImgResize('gd', 'l'); ?>" />
										</label>
										&nbsp;X&nbsp;
										<label for="g_resize_max_gd_hauteur">
											H. <input type="text" class="text text4 i_resize" id="g_resize_max_gd_hauteur" maxlength="4" name="g_resize_max_gd_hauteur" value="<?php $tpl->getImgResize('gd', 'h'); ?>" />
										</label>
										&nbsp;pixels
										<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_resize_gd');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
									</p>
									<div id="aide_resize_gd" class="aide_contextuelle" style="display:none">
										<div class="aide_barre">
											<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_resize_gd');">fermer</a></span>
											<span class="aide_aide">AIDE :</span> <span class="aide_titre">taille maximale (GD)</span>
										</div>
										<span class="aide_texte">Cette option va créer, à l'aide de la librairie GD, des images de taille intermédiaire si les images dépassent la taille fixée ci-dessus, et qui se situeront dans le répertoire « cache ». Si vous choisissez cette fonction, les visiteurs ne pourront pas choisir le mode de redimensionnement des images. La différence par rapport au redimensionnement par HTML c'est que les images seront d'une meilleur qualité. En contrepartie, cela consommera plus de ressources et d'espace disque.</span>
									</div>
<?php endif; ?>
								</div>
							</fieldset>
							<br/>
							<fieldset<?php $tpl->getTextDisabled('recentes'); ?>>
								<legend>Images récentes</legend>
								<div class="fielditems">
									<p class="field">
										<input<?php $tpl->getInputDisabled('recentes'); ?> type="checkbox" id="g_recentes" name="g_recentes"<?php $tpl->getImgRecentes('etat'); ?> />
										<label for="g_recentes"> Mettre en évidence les images récentes&nbsp;:</label>
									</p>
									<div class="field_second">
										<p class="field">
											<label for="g_jours">Durée de nouveauté (en jours)&nbsp;: </label>
											<input<?php $tpl->getInputDisabled('recentes'); ?> type="text" class="text text4" id="g_jours" name="g_jours" maxlength="4" value="<?php $tpl->getImgRecentes('jours'); ?>" />
										</p>
										<p class="field">
											<input<?php $tpl->getInputDisabled('recentes'); ?> type="checkbox" id="g_recent_nb" name="g_recent_nb"<?php $tpl->getImgRecentes('nb'); ?> />
											<label for="g_recent_nb">Afficher le nombre d'images récentes</label>
										</p>
									</div>
								</div>
							</fieldset>
<?php if ($tpl->isEXIF()) : ?>
							<br/>
							<fieldset<?php $tpl->getTextDisabled('exif'); ?>>
								<legend>Méta-données Exif</legend>
								<div class="fielditems">
									<p class="field">
										<input<?php $tpl->getInputDisabled('exif'); ?> type="checkbox" id="g_exif_active" name="g_exif_active"<?php $tpl->getmetadata('exif'); ?> />
										<label for="g_exif_active">Afficher les informations Exif disponibles</label>
									</p>
									<br/>
									<div class="field_second">
										<p><a href="index.php?section=options&amp;page=infos_exif">choix et formatage des informations à afficher</a></p>
									</div>
									<p class="field">
										<input<?php $tpl->getInputDisabled('exif'); ?> type="checkbox" id="g_exif_ajouts" name="g_exif_ajouts"<?php $tpl->getmetadata('exif_ajout'); ?> />
										<label for="g_exif_ajouts">Récupérer et associer aux images ces informations lors de l'ajout de nouvelles images&nbsp;:<br /><em>date de création, modèle et marque de l'appareil</em></label>
									</p>
								</div>
							</fieldset>
<?php endif; ?>
							<br/>
							<fieldset<?php $tpl->getTextDisabled('iptc'); ?>>
								<legend>Méta-données IPTC</legend>
								<div class="fielditems">
									<p class="field">
										<input<?php $tpl->getInputDisabled('iptc'); ?> type="checkbox" id="g_iptc_active" name="g_iptc_active"<?php $tpl->getmetadata('iptc'); ?> />
										<label for="g_iptc_active">Afficher les informations IPTC disponibles</label>
									</p>
									<br/>
									<div class="field_second">
										<p><a href="index.php?section=options&amp;page=infos_iptc">choix des informations à afficher</a></p>
									</div>
									<p class="field">
										<input<?php $tpl->getInputDisabled('iptc'); ?> type="checkbox" id="g_iptc_ajouts" name="g_iptc_ajouts"<?php $tpl->getmetadata('iptc_ajout'); ?> />
										<label for="g_iptc_ajouts">Récupérer et associer aux images ces informations lors de l'ajout de nouvelles images&nbsp;:<br /><em>titre, description et mots-clés</em></label>
									</p>
								</div>
							</fieldset>
<?php if ($tpl->isGD()) : ?>
							<br/>
							<fieldset>
								<legend>Texte sur chaque image</legend>
								<div class="fielditems">
									<p class="field">
										<input type="checkbox" id="g_itext_active" name="g_itext_active"<?php $tpl->getitextcheckbox('active'); ?> />
										<label for="g_itext_active">Afficher un texte sur chaque image</label>
									</p>
									<div class="field_second">
										<p class="field">
											<label for="g_itext_texte">Texte&nbsp;: </label>
											<input type="text" class="text" id="g_itext_texte" maxlength="255" size="50" name="g_itext_texte"<?php $tpl->getitexttext(0); ?> />
										</p>
										<div id="itext_parametres"></div>
										<br/>
										<p><a href="index.php?section=options&amp;page=itext_params">couleur et position du texte</a></p>
									</div>
								</div>
							</fieldset>
<?php endif; ?>
						</div>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>