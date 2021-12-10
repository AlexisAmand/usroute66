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
				<form action="index.php?section=options&amp;page=textes" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<div id="param_general">
							<fieldset>
								<legend>Titre et page d'accueil</legend>
								<div class="fielditems">
									<p class="field">
										<label for="g_galtitre">Titre de la galerie <span title="Vous pouvez utiliser du code HTML dans ce texte" class="text_html">(HTML)</span>&nbsp;: </label>
										<input type="text" class="text" id="g_galtitre" name="g_galtitre" maxlength="255" size="50" value="<?php $tpl->getTextes('titre'); ?>" />
									</p>
									<p class="field">
										<label<?php $tpl->getTextDisabled('titre_court'); ?> for="g_galtitre_court">Titre court ou logo <span title="Vous pouvez utiliser du code HTML dans ce texte" class="text_html">(HTML)</span>&nbsp;: </label>
										<input<?php $tpl->getInputDisabled('titre_court'); ?> type="text" class="text" id="g_galtitre_court" name="g_galtitre_court" maxlength="255" size="50" value="<?php $tpl->getTextes('titre_court'); ?>" />
									</p>
									<p class="field<?php $tpl->getTextDisabled('message_accueil', ' disabled'); ?>">
										<label for="g_accueil">Message de page d'accueil <span title="Vous pouvez utiliser du code HTML dans ce texte" class="text_html">(HTML)</span>&nbsp;:</label>
										<textarea<?php $tpl->getInputDisabled('message_accueil'); ?> id="g_accueil" name="g_accueil" cols="60" rows="5"><?php $tpl->getTextes('message_accueil'); ?></textarea>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset<?php $tpl->getTextDisabled('pied'); ?>>
								<legend>Pied de page</legend>
								<div class="fielditems">
									<p class="field">
										<input<?php $tpl->getInputDisabled('pied'); ?>  id="g_msg_footer" name="g_msg_footer" type="checkbox"<?php $tpl->getFooter('message'); ?> />
										<label for="g_msg_footer">Message <span title="Vous pouvez utiliser du code HTML dans ce texte" class="text_html">(HTML)</span>&nbsp;:</label>
										<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_pied_msg');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
										<span id="aide_pied_msg" class="aide_contextuelle" style="display:none">
											<span class="aide_barre">
												<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_pied_msg');">fermer</a></span>
												<span class="aide_aide">AIDE :</span> <span class="aide_titre">message de pied de page</span>
											</span>
											<span class="aide_texte">Pour insérer du HTML ou du JavaScript, vous devez mettre tout le code sur une seule ligne, car tous les sauts de lignes sont remplacés par des &lt;br /&gt;. Si vous souhaitez conserver les sauts de lignes, vous devrez insérer votre code directement dans le template, dans le fichier footer.php.</span>
										</span>
										<textarea<?php $tpl->getInputDisabled('pied'); ?> id="g_msg_footer_txt" name="g_msg_footer_txt" cols="60" rows="5"><?php $tpl->getTextes('message_footer'); ?></textarea>
									</p>
									<p class="field">
										<input<?php $tpl->getInputDisabled('pied'); ?>  id="g_cnt_footer" name="g_cnt_footer" type="checkbox"<?php $tpl->getFooter('counter'); ?> />
										<label for="g_cnt_footer"> Temps d'exécution</label>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset<?php $tpl->getTextDisabled('contact'); ?>>
								<legend>Page contact</legend>
								<div class="fielditems">
									<p class="field">
										<input<?php $tpl->getInputDisabled('contact'); ?>  id="g_contact" name="g_contact" type="checkbox"<?php $tpl->getActiveContact(); ?> />
										<label for="g_contact">Activer la page contact</label>
									</p>
									<p class="field">
										<label for="g_contact_text">Message <span title="Vous pouvez utiliser du code HTML dans ce texte" class="text_html">(HTML)</span>&nbsp;:</label>
										<textarea<?php $tpl->getInputDisabled('contact'); ?> id="g_contact_text" name="g_contact_text" cols="60" rows="5"><?php $tpl->getTextes('contact_text'); ?></textarea>
									</p>
								</div>
							</fieldset>
							<br />
							<fieldset>
								<legend>Fermeture de la galerie</legend>
								<div class="fielditems">
									<p class="field">
										<label for="g_close_text">Message&nbsp;:</label>
										<textarea id="g_close_text" name="g_close_text" cols="60" rows="5"><?php $tpl->getTextes('message_fermeture'); ?></textarea>
									</p>
								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>