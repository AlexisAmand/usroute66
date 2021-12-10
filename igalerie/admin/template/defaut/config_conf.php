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
				<form action="index.php?section=config&amp;page=conf" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getVID(); ?>

						<?php $tpl->getRapport('%s<br/>'); ?>

						<p id="config_mdp">
							<label for="g_pass">Mot de passe actuel (obligatoire pour changer l'un des paramètres suivants)</label>
							<input type="password" class="text" maxlength="60" size="30" name="g_pass" id="g_pass" />
						</p>
						<div id="param_general">
							<table id="conf">
								<tr>
									<td class="group" id="gauche">
										<fieldset>
											<legend>Administration</legend>
											<div class="fielditems">
												<p class="field">
													<label for="g_login">Nouvel identifiant</label>
													<br/>
													<input type="text" class="text" maxlength="60" size="30" name="g_login" id="g_login" />
												</p>
												<p class="field">
													<label for="g_new_pass">Nouveau mot de passe</label>
													<br/>
													<input type="password" class="text" maxlength="60" size="30" name="g_new_pass" id="g_new_pass" />
												</p>
												<p class="field">
													<label for="g_new_pass_confirm">Confirmation du nouveau<br />mot de passe</label>
													<br/>
													<input type="password" class="text" maxlength="60" size="30" name="g_new_pass_confirm" id="g_new_pass_confirm" />
												</p>
												<p class="field">
													<label for="g_mail">Adresse courriel</label>
													<br/>
													<input type="text" class="text" maxlength="60" size="30" name="g_mail" id="g_mail" value="<?php $tpl->getConfig('g_mail'); ?>" />
												</p>
											</div>
										</fieldset>
									</td>
									<td class="group" id="droite">
										<fieldset>
											<legend>Galerie</legend>
											<div class="fielditems">
												<p class="field">
													<label for="g_dir_alb">Répertoire des albums</label>
													<br/>
													<input type="text" class="text" maxlength="50" size="30" name="g_dir_alb" id="g_dir_alb" value="<?php $tpl->getConfig('g_dir_alb'); ?>" />
												</p>
												<p class="field">
													<label for="g_dir_tb">Répertoire des vignettes</label>
													<br/>
													<input type="text" class="text" maxlength="50" size="30" name="g_dir_tb" id="g_dir_tb" value="<?php $tpl->getConfig('g_dir_tb'); ?>" />
												</p>
												<p class="field">
													<label for="g_pref_tb">Préfixe des vignettes</label>
													<br/>
													<input type="text" class="text" maxlength="30" size="30" name="g_pref_tb" id="g_pref_tb" value="<?php $tpl->getConfig('g_pref_tb'); ?>" />
												</p>
											</div>
										</fieldset>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
				</form>