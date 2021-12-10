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
				<form action="index.php?section=options&amp;page=fonctions" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<div id="param_general">
							<fieldset class="checkbox">
								<legend>Fonctionnalités activées</legend>
								<div class="fielditems">
									<div class="separator">
										<p class="field<?php $tpl->getTextDisabled('commentaires', ' disabled'); ?>">
											<input<?php $tpl->getInputDisabled('commentaires'); ?> id="f_comment" name="f_comment" type="checkbox"<?php $tpl->getFonction('active_commentaires'); ?> />
											<label for="f_comment"> Commentaires</label>
									</p>
									</div>
									<div class="separator">
										<p class="field<?php $tpl->getTextDisabled('votes', ' disabled'); ?>">
											<input<?php $tpl->getInputDisabled('votes'); ?> id="f_votes" name="f_votes" type="checkbox"<?php $tpl->getFonction('active_votes'); ?> />
											<label for="f_votes"> Votes</label>
										</p>
									</div>
									<div class="separator">
									<p class="field<?php $tpl->getTextDisabled('image_hasard', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('image_hasard'); ?> id="f_imgh" name="f_imgh" type="checkbox"<?php $tpl->getFonction('galerie_image_hasard'); ?> />
										<label for="f_imgh"> Image au hasard</label>
									</p>
									</div>
									<div class="separator">
										<p class="field<?php $tpl->getTextDisabled('perso', ' disabled'); ?>">
											<input<?php $tpl->getInputDisabled('perso'); ?> id="f_perso" name="f_perso" type="checkbox"<?php $tpl->getFonction('user_perso'); ?> />
											<label for="f_perso"> Personnalisation</label>
										</p>
									</div>
									<div class="separator">
										<p class="field<?php $tpl->getTextDisabled('adv_search', ' disabled'); ?>">
											<input<?php $tpl->getInputDisabled('adv_search'); ?> id="f_adv_search" name="f_adv_search" type="checkbox"<?php $tpl->getFonction('active_advsearch'); ?> />
											<label for="f_adv_search"> Recherche avancée</label>
										</p>
									</div>
									<div class="separator">
										<p class="field<?php $tpl->getTextDisabled('historique', ' disabled'); ?>">
											<input<?php $tpl->getInputDisabled('historique'); ?> id="f_historique" name="f_historique" type="checkbox"<?php $tpl->getFonction('active_historique'); ?> />
											<label for="f_historique"> Historique</label>
										</p>
									</div>
									<div class="separator">
										<p class="field<?php $tpl->getTextDisabled('diaporama', ' disabled'); ?>">
											<input<?php $tpl->getInputDisabled('diaporama'); ?> id="f_diaporama" name="f_diaporama" type="checkbox"<?php $tpl->getFonction('active_diaporama'); ?> />
											<label for="f_diaporama"> Diaporama</label>
										</p>
<?php if ($tpl->isGD()) : ?>
										<div class="field_second">
											<p class="field<?php $tpl->getTextDisabled('diaporama', ' disabled'); ?>">
												<input<?php $tpl->getInputDisabled('diaporama'); ?> id="f_diaporama_gd_resize" name="f_diaporama_gd_resize" type="checkbox"<?php $tpl->getFonction('galerie_diaporama_resize'); ?> />
												<label for="f_diaporama_gd_resize"> Utiliser le</label> <a href="index.php?section=options&amp;page=images#image_resize">redimensionnement des images par GD</a>
											</p>
										</div>
<?php endif; ?>
									</div>
									<div class="separator">
										<p class="field<?php $tpl->getTextDisabled('rss', ' disabled'); ?>">
											<input<?php $tpl->getInputDisabled('rss'); ?> id="f_rss" name="f_rss" type="checkbox"<?php $tpl->getFonction('active_rss'); ?> />
											<label for="f_rss"> Flux RSS</label>
										</p>
										<div class="field_second">
											<p class="field<?php $tpl->getTextDisabled('rss', ' disabled'); ?>">
												<label for="f_rss_nb">Nombre maximal d'ajouts récents à notifier&nbsp;: </label>
												<input<?php $tpl->getInputDisabled('rss'); ?> type="text" class="text text4" id="f_rss_nb" name="f_rss_nb" maxlength="4" value="<?php $tpl->getFonctionOption('galerie_nb_rss'); ?>" />
											</p>
										</div>
									</div>
									<p class="field<?php $tpl->getTextDisabled('tags', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('tags'); ?> id="f_tags" name="f_tags" type="checkbox"<?php $tpl->getFonction('active_tags'); ?> />
										<label for="f_tags"> Tags</label>
									</p>
									<div class="field_second<?php $tpl->getTextDisabled('tags', ' disabled'); ?>">
										<p class="field">
											<label for="f_tags_nb">Nombre maximal de tags à afficher&nbsp;: </label>
											<input<?php $tpl->getInputDisabled('tags'); ?> type="text" class="text text4" id="f_tags_nb" name="f_tags_nb" maxlength="4" value="<?php $tpl->getFonctionOption('galerie_nb_tags'); ?>" />
										</p>
									</div>
								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>