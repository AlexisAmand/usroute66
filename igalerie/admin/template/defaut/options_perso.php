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
				<form action="index.php?section=options&amp;page=perso" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getRapport('%s<br/>'); ?>
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<p>Permettre à l'utilisateur d'afficher ou de changer...&nbsp;:<br/></p>
						<div class="js_coche">
							<a class="lien_js" href="javascript:inputCheck(1,'options_perso');">tout cocher</a> - <a class="lien_js" href="javascript:inputCheck(0,'options_perso');">tout décocher</a>
						</div>
						<div id="param_general">
							<fieldset class="checkbox">
								<legend>Apparence</legend>
								<div class="fielditems">
									<p class="field<?php $tpl->getTextDisabled('perso_style', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_style'); ?> class="options_perso" id="g_perso_style" name="g_perso_style" type="checkbox"<?php $tpl->getPerso('style'); ?> />
										<label for="g_perso_style"> Style</label>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset class="checkbox">
								<legend>Images</legend>
								<div class="fielditems">
									<p class="field<?php $tpl->getTextDisabled('perso_recentes', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_recentes'); ?> class="options_perso" id="g_perso_recents" name="g_perso_recents" type="checkbox"<?php $tpl->getPerso('recentes'); ?> />
										<label for="g_perso_recents"> Images récentes</label>
									</p>
									<p class="field<?php $tpl->getTextDisabled('perso_image_size', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_image_size'); ?> class="options_perso" id="g_perso_ajust" name="g_perso_ajust" type="checkbox"<?php $tpl->getPerso('image_ajust'); ?> />
										<label for="g_perso_ajust"> Ajustement de la taille des images</label>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset class="checkbox">
								<legend>Vignettes</legend>
								<div class="fielditems">
									<p class="field<?php $tpl->getTextDisabled('perso_nb_thumbs', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_nb_thumbs'); ?> class="options_perso" id="g_perso_thumbs" name="g_perso_thumbs" type="checkbox"<?php $tpl->getPerso('vignettes'); ?> />
										<label for="g_perso_thumbs"> Nombre de vignettes</label>
									</p>
									<p class="field<?php $tpl->getTextDisabled('perso_sort_thumbs', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_sort_thumbs'); ?> class="options_perso" id="g_perso_ordre" name="g_perso_ordre" type="checkbox"<?php $tpl->getPerso('ordre'); ?> />
										<label for="g_perso_ordre"> Ordre des vignettes</label>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset class="checkbox">
								<legend>Informations sous les vignettes des albums</legend>
								<div class="fielditems">
									<p class="field<?php $tpl->getTextDisabled('perso_cat_nom', ' disabled'); ?>">	
										<input<?php $tpl->getInputDisabled('perso_cat_nom'); ?> class="options_perso" id="g_perso_c_nom" name="g_perso_c_nom" type="checkbox"<?php $tpl->getPerso('nom_categories'); ?> />
										<label for="g_perso_c_nom"> Nom (catégories et albums)</label>
									</p>
									<p class="field<?php $tpl->getTextDisabled('perso_nb_images', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_nb_images'); ?> class="options_perso" id="g_perso_c_imgs" name="g_perso_c_imgs" type="checkbox"<?php $tpl->getPerso('nb_images'); ?> />
										<label for="g_perso_c_imgs"> Nombre d'images</label>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset class="checkbox">
								<legend>Informations sous les vignettes des images</legend>
								<div class="fielditems">
									<p class="field<?php $tpl->getTextDisabled('perso_img_nom', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_img_nom'); ?> class="options_perso" id="g_perso_i_nom" name="g_perso_i_nom" type="checkbox"<?php $tpl->getPerso('nom_images'); ?> />
										<label for="g_perso_i_nom"> Nom des images</label>
									</p>
									<p class="field<?php $tpl->getTextDisabled('perso_date', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_date'); ?> class="options_perso" id="g_perso_i_date" name="g_perso_i_date" type="checkbox"<?php $tpl->getPerso('date'); ?> />
										<label for="g_perso_i_date"> Date de mise en ligne</label>
									</p>
									<p class="field<?php $tpl->getTextDisabled('perso_taille', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_taille'); ?> class="options_perso" id="g_perso_i_taille" name="g_perso_i_taille" type="checkbox"<?php $tpl->getPerso('taille'); ?> />
										<label for="g_perso_i_taille"> Taille</label>
									</p>
									<p class="field<?php $tpl->getTextDisabled('perso_poids', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_poids'); ?> class="options_perso" id="g_perso_i_poids" name="g_perso_i_poids" type="checkbox"<?php $tpl->getPerso('poids'); ?> />
										<label for="g_perso_i_poids"> Poids des images</label>
									</p>
									<p class="field<?php $tpl->getTextDisabled('perso_hits', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_hits'); ?> class="options_perso" id="g_perso_i_hits" name="g_perso_i_hits" type="checkbox"<?php $tpl->getPerso('hits'); ?> />
										<label for="g_perso_i_hits"> Nombre de visites</label>
									</p>
									<p class="field<?php $tpl->getTextDisabled('perso_commentaires', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_commentaires'); ?> class="options_perso" id="g_perso_i_comments" name="g_perso_i_comments" type="checkbox"<?php $tpl->getPerso('comments'); ?> />
										<label for="g_perso_i_comments"> Nombre de commentaires</label>
									</p>
									<p class="field<?php $tpl->getTextDisabled('perso_votes', ' disabled'); ?>">
										<input<?php $tpl->getInputDisabled('perso_votes'); ?> class="options_perso" id="g_perso_i_votes" name="g_perso_i_votes" type="checkbox"<?php $tpl->getPerso('votes'); ?> />
										<label for="g_perso_i_votes"> Note moyenne</label>
									</p>
								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>