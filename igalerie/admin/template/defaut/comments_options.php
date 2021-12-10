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
				<form action="index.php?section=commentaires&amp;page=options" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getRapport('%s<br />'); ?>
						<?php $tpl->getGeneralMaj('%s<br />'); ?>

						<?php $tpl->getVID(); ?>

						<div id="co_opt_ban">
							<fieldset class="checkbox">
								<legend>Renseignements obligatoires pour poster un commentaire</legend>
								<div class="fielditems">
									<p class="field">
										<input type="checkbox" id="oc_fac_mail" name="oc_fac_mail"<?php $tpl->getCommentFac('courriel', ' checked="checked"'); ?> />
										<label for="oc_fac_mail">Courriel</label>
									</p>
									<p class="field">
										<input type="checkbox" id="oc_fac_web" name="oc_fac_web"<?php $tpl->getCommentFac('siteweb', ' checked="checked"'); ?> />
										<label for="oc_fac_web">Site Web</label>
									</p>
								</div>
							</fieldset>
							<br />
							<fieldset class="checkbox">
								<legend>Contrôle</legend>
								<div class="fielditems">
									<p class="field">
										<label for="oc_pro_flood">Anti-flood</label> 
										:&nbsp;
										<input id="oc_pro_flood" name="oc_pro_flood" type="text" class="text" maxlength="4" size="4" value="<?php $tpl->getCommentAntiFlood(); ?>" /> &nbsp;secondes
										<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_anti_flood');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
									</p>
									<div id="aide_anti_flood" class="aide_contextuelle" style="display:none">
										<div class="aide_barre">
											<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_anti_flood');">fermer</a></span>
											<span class="aide_aide">AIDE :</span> <span class="aide_titre">anti-flood</span>
										</div>
										<div class="aide_texte">
											L'anti-flood permet de régler, après l'envoi d'un commentaire, la durée pendant laquelle on ne peut pas poster un autre commentaire.
										</div>
									</div>
									<p class="field">
										<input type="checkbox" id="oc_samemsg" name="oc_samemsg"<?php $tpl->getCommentFac('samemsg', ' checked="checked"'); ?> />
										<label for="oc_samemsg">Interdire à une même IP de poster plusieurs fois un même message par image</label>
									</p>
									<p class="field">
										<input type="checkbox" id="oc_maxmsg" name="oc_maxmsg"<?php $tpl->getCommentFac('maxmsg', ' checked="checked"'); ?> />
										<label for="oc_maxmsg">Interdire à une même IP de poster plus de</label>
										<input id="oc_maxmsg_nb" name="oc_maxmsg_nb" type="text" class="text" maxlength="4" size="4" value="<?php $tpl->getCommentMaxMsg(); ?>" />
										<label for="oc_maxmsg">messages par image</label>
									</p>
									<p class="field">
										<input type="checkbox" id="oc_nourl" name="oc_nourl"<?php $tpl->getCommentFac('nourl', ' checked="checked"'); ?> />
										<label for="oc_nourl">Interdire les commentaires contenant au moins</label>
										<input id="oc_nourl_nb" name="oc_nourl_nb" type="text" class="text" maxlength="2" size="2" value="<?php $tpl->getCommentMaxURL(); ?>" />
										<label for="oc_nourl">adresses Internet</label>
									</p>
								</div>
							</fieldset>
							<br />
							<fieldset>
								<legend>Traitement des commentaires</legend>
								<div class="fielditems">
									<p class="field">
										<input type="checkbox" id="oc_mod" name="oc_mod"<?php $tpl->getCommentMod(' checked="checked"'); ?> />
										<label for="oc_mod">Modérer les commentaires</label>
										<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_moderation');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
									</p>
									<div id="aide_moderation" class="aide_contextuelle" style="display:none">
										<div class="aide_barre">
											<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_moderation');">fermer</a></span>
											<span class="aide_aide">AIDE :</span> <span class="aide_titre">modération des commentaires</span>
										</div>
										<div class="aide_texte">
											En cochant cette option, les commentaires ne seront pas visibles sur la galerie tant que vous ne les aurez pas validés. Les commentaires modérés sont désactivés et vous devrez les activer dans la partie <a href="index.php?section=commentaires&amp;page=display">gestion</a> des commentaires.
										</div>
									</div>
									<p class="field">
										<input type="checkbox" id="oc_alert" name="oc_alert"<?php $tpl->getCommentAlert(' checked="checked"'); ?> />
										<label for="oc_alert">Être alerté par courriel de chaque nouveau commentaire&nbsp;:</label>
									</p>
									<div class="field_second">
										<p class="field">
											<label for="oc_objet" class="oc_labc">objet du courriel&nbsp;: </label>
											<input class="text" size="50" maxlength="255" id="oc_objet" name="oc_objet"<?php $tpl->getCommentAlertObjet(' value="%s"'); ?> type="text" />
										</p>
									</div>
								</div>
							</fieldset>
							<br />
							<fieldset<?php $tpl->getTextDisabled('co_page_comments'); ?>>
								<legend>Page des commentaires</legend>
								<div class="fielditems">
									<p class="field">
										<input<?php $tpl->getInputDisabled('co_page_comments'); ?> type="checkbox" id="oc_comments" name="oc_comments"<?php $tpl->getPageComment(); ?> />
										<label for="oc_comments">Activer la page des commentaires</label>
									</p>
									<p class="field">
										Nombre de commentaires par page&nbsp;:
										<select<?php $tpl->getInputDisabled('co_page_comments'); ?> name="oc_comments_nb">
											<?php $tpl->getPageCommentNb('<option value="%s"%s>%s</option>', 100); ?>

										</select>
									</p>
								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>