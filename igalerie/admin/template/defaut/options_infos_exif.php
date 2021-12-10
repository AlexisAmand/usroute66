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
				<form<?php $tpl->getTextDisabled('exif'); ?> action="index.php?section=options&amp;page=infos_exif" method="post">
					<div>
						<?php $tpl->getRapport('%s<br/>'); ?>
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<div id="exif_liens_haut">
							<div class="opt_avance_retour"><a href="index.php?section=options&amp;page=images">retour</a></div>
							<span id="exif_reinit_params"><a href="javascript:meta_reinit_params('exif', '<?php $tpl->getVID(1); ?>');">réinitialiser tous les paramètres</a></span>
						</div>
						<div class="aide_fixe">
							<span class="aide_texte">
								<strong>Attention!</strong> Ne modifiez les détails des paramètres que si<br/>vous savez exactement à quoi ils correspondent.
							</span>
						</div>

						<div id="param_general">
							<fieldset>
								<legend>
									<a href="index.php?section=options&amp;page=infos_exif">Éditeur Exif</a>
									<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_exif');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
								</legend>
								<div class="fielditems">
									<div id="exif_new_param"><a href="index.php?section=options&amp;page=infos_exif&amp;new_param=1">créer un nouveau paramètre</a></div>
									<div class="js_coche">
										<a class="lien_js" href="javascript:meta_select_all(1,'exif');">tout activer</a>
										- 
										<a class="lien_js" href="javascript:meta_select_all(0,'exif');">tout désactiver</a>
										&nbsp;&nbsp;&nbsp;
										<a class="lien_js" href="javascript:exif_display_all(1);">tout montrer</a>
										- 
										<a class="lien_js" href="javascript:exif_display_all(0);">tout cacher</a>

									</div>
									
									<div id="aide_exif" class="aide_contextuelle" style="display:none">
										<div class="aide_barre">
											<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_exif');">fermer</a></span>
											<span class="aide_aide">AIDE :</span> <span class="aide_titre">éditeur Exif</span>
										</div>
										<span class="aide_texte">
											Sur cette page vous avez la possibilité d'éditer les paramètres des informations Exif ainsi que d'en créer de nouveaux. L'édition des détails des paramètres permet de formater la valeur à afficher. La "méthode" correspond à la méthode de formatage de la valeur contenu dans le tag. Lorsque celle-ci ne peut être qu'une valeur d'un ensemble de valeurs prédéfinies, choisissez la méthode "liste" afin de faire correspondre les différentes valeurs possibles au texte que vous souhaitez afficher. Si la valeur est un numéro de version (en quatre chiffres), choisissez la méthode "version". Si la valeur est une date Exif, choisissez la méthode "date". Dans ce cas, un champ "format" permet de spécifier le format de la date à l'aide des paramètres de la fonction PHP <a href="http://www.php.net/manual/fr/function.strftime.php" class="ex">strftime()</a>. Si la valeur est composé de deux nombres à diviser (comme « 35/10 »), choisissez la méthode nombre. Le champ "format" permet alors de formater la valeur à afficher à l'aide des spécificateurs de type de la fonction PHP <a href="http://fr.php.net/manual/fr/function.sprintf.php" class="ex">sprintf()</a> Pour un nombre à virgule flottante, on utilisera le spécificateur de type f (ainsi, le format %2.4f appliqué à la valeur 1/160 donnera 0.0063).  Enfin, si vous souhaitez formater toute autre valeur différente de la précédente, choisissez la méthode "simple", qui formatera la valeur avec la même fonction sprintf(). Si vous souhaitez par exemple afficher une valeur brute, sans formatage, il suffira, avec cette méthode, de laissez le champ "format" vide.
										</span>
									</div>

<?php while ($tpl->getNextExif()) : ?>
									<div class="exif_param<?php $tpl->getExif('inactive'); ?>" id="exif_param_<?php $tpl->getExif('id'); ?>">
										<div class="exif_param_top">
											<div class="exif_param_etat">
												<input<?php $tpl->getInputDisabled('exif'); ?> name="exif_param[<?php $tpl->getExif('id'); ?>]" type="checkbox"<?php $tpl->getExif('etat'); ?> />
											</div>
											<div class="exif_param_principal">
												<span class="exif_param_display_details"><a class="lien_jsd" href="javascript:exif_display_details(<?php $tpl->getExif('id'); ?>);"><span>details</span></a></span>
												<?php $tpl->getExif('delete', '<span class="exif_param_delete"><a href="javascript:exif_confirm_delete_param(\'%s\',\'%s\',\'%s\');">supprimer</a></span>'); ?>
												<label for="exif_param_description_<?php $tpl->getExif('id'); ?>"><strong>Description</strong>&nbsp;:</label>
												<input<?php $tpl->getInputDisabled('exif'); ?> id="exif_param_description_<?php $tpl->getExif('id'); ?>" name="exif_param_description[<?php $tpl->getExif('id'); ?>]" type="text" class="text" maxlength="200" size="50" value="<?php $tpl->getExif('desc'); ?>" />
											</div>
										</div>
										<div <?php $tpl->getExif('display'); ?>class="exif_param_details" id="exif_param_details_<?php $tpl->getExif('id'); ?>">
											Section&nbsp;:
											<?php $tpl->getExif('sections'); ?>
											&nbsp;
											Tag&nbsp;:
											<input<?php $tpl->getInputDisabled('exif'); ?> value="<?php $tpl->getExif('tag'); ?>" id="exif_param_tag_<?php $tpl->getExif('id'); ?>" name="exif_param_tag[<?php $tpl->getExif('id'); ?>]" type="text" class="text" maxlength="64" size="30" />
											&nbsp;
											Méthode&nbsp;:
											<?php $tpl->getExif('method'); ?>
											<div class="exif_param_valeurs">
												<div class="exif_param_format" id="exif_param_format_div_<?php $tpl->getExif('id'); ?>"<?php $tpl->getExif('valeur', 'format'); ?><?php $tpl->getExif('valeur', ''); ?>>
													<label for="exif_param_format_<?php $tpl->getExif('id'); ?>">Format&nbsp;:</label>
													<input<?php $tpl->getInputDisabled('exif'); ?> value="<?php $tpl->getExif('format'); ?>" id="exif_param_format_<?php $tpl->getExif('id'); ?>" name="exif_param_format[<?php $tpl->getExif('id'); ?>]" type="text" class="text" maxlength="100" size="60" />
												</div>
												<div class="exif_param_liste" id="exif_param_liste_<?php $tpl->getExif('id'); ?>"<?php $tpl->getExif('valeur', 'liste'); ?><?php $tpl->getExif('valeur', ''); ?>>
													<span class="exif_param_valeurs_new_enum"><a class="lien_js" href="javascript:exif_new_enum(<?php $tpl->getExif('id'); ?>);">nouvelle correspondance</a></span>
													<span class="exif_param_valeurs_titre">Correspondances&nbsp;:</span>
<?php while ($tpl->getNextExifEnum()) : ?>
													<div class="exif_param_valeurs_liste" id="exif_param_valeurs_liste_<?php $tpl->getExif('id'); ?>_<?php $tpl->getExif('list_num'); ?>">
														<label for="exif_param_liste_<?php $tpl->getExif('id'); ?>_tag_<?php $tpl->getExif('list_num'); ?>">valeur tag&nbsp;:</label> <input<?php $tpl->getInputDisabled('exif'); ?> value="<?php $tpl->getExif('list_tag'); ?>" id="exif_param_liste_<?php $tpl->getExif('id'); ?>_tag_<?php $tpl->getExif('list_num'); ?>" name="exif_param_liste[<?php $tpl->getExif('id'); ?>][tag][<?php $tpl->getExif('list_num'); ?>]" type="text" class="text" maxlength="128" size="5" />
														&nbsp;
														<label for="exif_param_liste_<?php $tpl->getExif('id'); ?>_display_<?php $tpl->getExif('list_num'); ?>">texte à afficher&nbsp;:</label> <input<?php $tpl->getInputDisabled('exif'); ?> value="<?php $tpl->getExif('list_display'); ?>" id="exif_param_liste_<?php $tpl->getExif('id'); ?>_display_<?php $tpl->getExif('list_num'); ?>" name="exif_param_liste[<?php $tpl->getExif('id'); ?>][display][<?php $tpl->getExif('list_num'); ?>]" type="text" class="text" maxlength="128" size="40" />
														<span class="exif_param_liste_delete"><a class="lien_js" href="javascript:exif_delete_enum(<?php $tpl->getExif('id'); ?>,<?php $tpl->getExif('list_num'); ?>);">supprimer</a></span>
													</div>
<?php endwhile; ?>
												</div>
											</div>
										</div>
									</div>
									<script type="text/javascript">
									//<![CDATA[
									<?php $tpl->getExif('focus_new'); ?>
									//]]>
									</script>
<?php endwhile; ?>

								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input<?php $tpl->getInputDisabled('exif'); ?> type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>