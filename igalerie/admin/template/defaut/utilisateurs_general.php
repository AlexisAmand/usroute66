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
				<form action="index.php?section=utilisateurs&amp;page=general" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getRapport('%s<br/>'); ?>
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<div id="param_general">
							<fieldset>
								<legend>Membres</legend>
								<div class="fielditems">
									<p class="field">
										<input id="u_general_membres_active" name="u_general_membres_active" type="checkbox"<?php $tpl->getUsersGeneral('membres_active'); ?> />
										<label for="u_general_membres_active"> Activer la fonctionnalité membres</label>
									</p>
									<p class="field">
										<input id="u_general_membres_alert" name="u_general_membres_alert" type="checkbox"<?php $tpl->getUsersGeneral('membres_alert'); ?> />
										<label for="u_general_membres_alert"> Être alerté par courriel de chaque nouvelle inscription</label>
									</p>
									<p class="field">
										<input id="u_general_membres_avatars" name="u_general_membres_avatars" type="checkbox"<?php $tpl->getUsersGeneral('membres_avatars'); ?> />
										<label for="u_general_membres_avatars"> Activer les avatars</label>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset>
								<legend>Envoi d'images</legend>
								<div class="fielditems">
									<p class="field">
										<input id="u_general_upload_alert" name="u_general_upload_alert" type="checkbox"<?php $tpl->getUsersGeneral('upload_alert'); ?> />
										<label for="u_general_upload_alert"> Être alerté par courriel de chaque nouvelle image envoyée</label>
									</p>
									<p class="field">
										<label for="u_general_upload_maxsize">Poids maximum d'une image&nbsp;:</label>
										<?php $tpl->getUploadMaxSize('<input type="text" class="text text4" maxlength="4" name="u_general_upload_maxsize" id="u_general_upload_maxsize" value="%s" /> Ko (poids serveur maximum : %s)'); ?>
									</p>
									<p class="field">
										Dimensions maximum d'une image&nbsp;:
										<label for="u_general_upload_maxwidth">L: </label><input type="text" class="text text5" maxlength="5" name="u_general_upload_maxwidth" id="u_general_upload_maxwidth" value="<?php $tpl->getUsersGeneralValues('maxwidth'); ?>" />
										X
										<label for="u_general_upload_maxheight">H: </label><input type="text" class="text text5" maxlength="5" name="u_general_upload_maxheight" id="u_general_upload_maxheight" value="<?php $tpl->getUsersGeneralValues('maxheight'); ?>" />
										pixels
									</p>
								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>