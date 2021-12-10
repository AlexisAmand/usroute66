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
				<form<?php $tpl->getTextDisabled('iptc'); ?> action="index.php?section=options&amp;page=infos_iptc" method="post">
					<div>
						<?php $tpl->getRapport('%s<br/>'); ?>
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<div id="iptc_liens_haut">
							<div class="opt_avance_retour"><a href="index.php?section=options&amp;page=images">retour</a></div>
							<span id="iptc_reinit_params"><a href="javascript:meta_reinit_params('iptc', '<?php $tpl->getVID(1); ?>');">réinitialiser tous les paramètres</a></span>
						</div>

						<div id="param_general">
							<fieldset>
								<legend>
									<a href="index.php?section=options&amp;page=infos_iptc">Informations IPTC</a>
								</legend>
								<div class="fielditems">
									<div class="js_coche">
										<a class="lien_js" href="javascript:meta_select_all(1,'iptc');">tout activer</a>
										- 
										<a class="lien_js" href="javascript:meta_select_all(0,'iptc');">tout désactiver</a>
									</div>

<?php while ($tpl->getNextIptc()) : ?>
									<div class="iptc_param<?php $tpl->getIptc('inactive'); ?>" id="iptc_param_<?php $tpl->getIptc('id'); ?>">
										<div class="iptc_param_top">
											<div class="iptc_param_etat">
												<input<?php $tpl->getInputDisabled('iptc'); ?> id="iptc_<?php $tpl->getIptc('id'); ?>" name="iptc_param[<?php $tpl->getIptc('id'); ?>]" type="checkbox"<?php $tpl->getIptc('etat'); ?> />
												<label for="iptc_<?php $tpl->getIptc('id'); ?>"><?php $tpl->getIptc('id'); ?></label>
											</div>
											<div class="iptc_param_principal">
												<label for="iptc_param_description_<?php $tpl->getIptc('id'); ?>"><strong>Description</strong>&nbsp;:</label>
												<input<?php $tpl->getInputDisabled('iptc'); ?> id="iptc_param_description_<?php $tpl->getIptc('id'); ?>" name="iptc_param_description[<?php $tpl->getIptc('id'); ?>]" type="text" class="text" maxlength="200" size="50" value="<?php $tpl->getIptc('desc'); ?>" />
											</div>
										</div>
									</div>
<?php endwhile; ?>

								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input<?php $tpl->getInputDisabled('iptc'); ?> type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>