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
				<form action="index.php?section=commentaires&amp;page=bans" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getRapport('%s<br />'); ?>
						<?php $tpl->getGeneralMaj('%s<br />'); ?>

						<?php $tpl->getVID(); ?>

						<div id="comment_bans">
							<fieldset>
								<legend>Règles de bannissement <a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_bans');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a></legend>
								<div id="aide_bans" class="aide_contextuelle" style="display:none">
									<div class="aide_barre">
										<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_bans');">fermer</a></span>
										<span class="aide_aide">AIDE :</span> <span class="aide_titre">bannissements</span>
									</div>
									<div class="aide_texte">
										Chaque terme ajouté est insensible à la casse, et les accents ne comptent pas. Par exemple le mot "présent" va bannir les mots "présent", "Présent", "PRESENT", etc.<br />
										Vous pouvez également utilisez le caractère * comme joker, qui trouvera n'importe quelle suite de caractères.
									</div>
								</div>
								<div class="fielditems">
									<div class="bans_partie" id="bans_messages">
										<p>Mots bannis</p>
										<select class="select_multiple" name="bans_mots[]" multiple="multiple" size="10">
											<?php $tpl->getCommentBan('mots-cles'); ?>
										</select>
										<label class="bans_ajout_txt" for="bans_mots_ajout">Bannir des mots<br />(séparés par des retours de ligne)&nbsp;:</label>
										<textarea class="bans_ajout" id="bans_mots_ajout" name="bans_mots_ajout" rows="3" cols="30"></textarea>
									</div>
									<div class="bans_partie" id="bans_auteurs">
										<p>Auteurs bannis</p>
										<select class="select_multiple" name="bans_auteurs[]" multiple="multiple" size="10">
											<?php $tpl->getCommentBan('auteurs'); ?>
										</select>
										<label class="bans_ajout_txt" for="bans_auteur_ajout">Bannir des auteurs<br />(séparés par des retours de ligne)&nbsp;:</label>
										<textarea class="bans_ajout" id="bans_auteur_ajout" name="bans_auteur_ajout" rows="3" cols="30"></textarea>
									</div>
									<div class="bans_partie" id="bans_ip">
										<p>Adresses IP bannies</p>
										<select class="select_multiple" name="bans_ip[]" multiple="multiple" size="10">
											<?php $tpl->getCommentBan('IP'); ?>
										</select>
										<label class="bans_ajout_txt" for="bans_ip_ajout">Bannir des IP<br />(séparées par des retours de ligne)&nbsp;:</label>
										<textarea class="bans_ajout" id="bans_ip_ajout" name="bans_ip_ajout" rows="3" cols="30"></textarea>
									</div>
								</div>
							</fieldset>
						</div>
						<br />
						<input type="checkbox" id="bans_allow_select" name="bans_allow_select" />
						<label for="bans_allow_select">Autoriser les éléments sélectionnés dans les listes</label>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>