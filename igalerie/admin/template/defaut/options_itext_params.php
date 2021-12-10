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
				<form action="index.php?section=options&amp;page=itext_params" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<input type="hidden" name="itext_params" value="1" />
						<?php $tpl->getRapport('%s<br/>'); ?>
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<div class="opt_avance_retour"><a href="index.php?section=options&amp;page=images">retour</a></div>
						
						<div id="param_general">
							<fieldset>
								<legend><a href="index.php?section=options&amp;page=itext_params">Texte sur chaque image</a></legend>
								<div class="fielditems">
									<br/>
									<div id="itext_params" class="opt_avance">
										<div class="opt_avance_barre">
											<span class="opt_avance_titre">Paramètres du texte</span>
										</div>
										<p class="field">
											<label for="g_itext_taille">Taille du texte&nbsp;: </label>
											<input type="text" class="text" maxlength="2" size="2" id="g_itext_taille" name="g_itext_taille"<?php $tpl->getitexttext(17); ?> />
										</p>
										<p class="field">
											<label for="g_itext_fonte">
												Fonte :
												<select id="g_itext_fonte" name="g_itext_fonte">
													<?php $tpl->getitextfontes(); ?>
												</select>
											</label>
											<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_itext_fonte');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
										</p>
										<div id="aide_itext_fonte" class="aide_contextuelle" style="display:none">
											<div class="aide_barre">
												<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_itext_fonte');">fermer</a></span>
												<span class="aide_aide">AIDE :</span> <span class="aide_titre">fonte</span>
											</div>
											<span class="aide_texte">Vous pouvez ajouter de nouvelles fontes (TrueType uniquement, fichiers .ttf) dans le répertoire « fontes » à la racine d'iGalerie.</span>
										</div>
										<p class="field">
											<label for="g_itext_texte_color">Couleur du texte&nbsp;: </label>
											<input type="text" class="text" maxlength="7" size="7" id="g_itext_texte_color" name="g_itext_texte_color"<?php $tpl->getitextcolor(1, 2, 3); ?> />
											<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_itext_couleur');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
										</p>
										<div id="aide_itext_couleur" class="aide_contextuelle" style="display:none">
											<div class="aide_barre">
												<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_itext_couleur');">fermer</a></span>
												<span class="aide_aide">AIDE :</span> <span class="aide_titre">couleurs</span>
											</div>
											<span class="aide_texte">Les couleurs doivent être entrées au format HTML. Vous pouvez utiliser, par exemple, un logiciel de retouche photo afin de récupérer les couleurs dans ce format.</span>
										</div>
										<p class="field">
											<label for="g_itext_position">
												Position :
												<select id="g_itext_position" name="g_itext_position">
													<option value="top_left"<?php $tpl->getitextselect(5, 'top_left'); ?>>En haut à gauche</option>
													<option value="top_center"<?php $tpl->getitextselect(5, 'top_center'); ?>>En haut au centre</option>
													<option value="top_right"<?php $tpl->getitextselect(5, 'top_right'); ?>>En haut à droite</option>
													<option value="bottom_left"<?php $tpl->getitextselect(5, 'bottom_left'); ?>>En bas à gauche</option>
													<option value="bottom_center"<?php $tpl->getitextselect(5, 'bottom_center'); ?>>En bas au centre</option>
													<option value="bottom_right"<?php $tpl->getitextselect(5, 'bottom_right'); ?>>En bas à droite</option>
												</select>
												<select name="g_itext_exterieur">
													<option value="0"<?php $tpl->getitextselect(21, 0); ?>>à l'intérieur de l'image</option>
													<option value="1"<?php $tpl->getitextselect(21, 1); ?>>à l'extérieur de l'image</option>
												</select>
											</label>
										</p>
										<p class="field">
											<label for="g_itext_bord_y">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;à </label>
											<input type="text" class="text" maxlength="3" size="2" id="g_itext_bord_y" name="g_itext_bord_y"<?php $tpl->getitexttext(19); ?> /> pixels du bord (vertical)
											<label for="g_itext_bord_x">et à </label>
											<input type="text" class="text" maxlength="3" size="2" id="g_itext_bord_x" name="g_itext_bord_x"<?php $tpl->getitexttext(18); ?> /> pixels du bord (horizontal)
										</p>
										<p class="field">
											<label for="g_itext_decalage">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;avec un décalage vertical par rapport au fond de </label>
											<input type="text" class="text" maxlength="3" size="2" id="g_itext_decalage" name="g_itext_decalage"<?php $tpl->getitexttext(26); ?> /> pixels
										</p>
										
										<div class="separate"></div>

										<p class="field">
											<input type="checkbox" id="g_itext_fond" name="g_itext_fond"<?php $tpl->getitextcheckbox(6); ?> />
											<label for="g_itext_fond"> Fond&nbsp;:</label>
										</p>
										<div class="field_second">
											<p class="field">
												<label for="g_itext_fond_color">Couleur de fond&nbsp;: </label>
												<input type="text" class="text" maxlength="7" size="7" id="g_itext_fond_color" name="g_itext_fond_color"<?php $tpl->getitextcolor(7, 8, 9); ?> />
												&nbsp;
												<label for="g_itext_fond_a">Transparence&nbsp;: </label>
												<input type="text" class="text" maxlength="3" size="2" id="g_itext_fond_a" name="g_itext_fond_a"<?php $tpl->getitexttext(10); ?> />
												<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_itext_transparence');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
											</p>
											<div id="aide_itext_transparence" class="aide_contextuelle" style="display:none">
												<div class="aide_barre">
													<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_itext_transparence');">fermer</a></span>
													<span class="aide_aide">AIDE :</span> <span class="aide_titre">transparence</span>
												</div>
												<span class="aide_texte">La transparence du fond doit être un nombre entier compris entre 0 (aucune transparence) et 127 (transparence complète).</span>
											</div>
											<p class="field">
												<input type="checkbox" id="g_itext_fond_largeur" name="g_itext_fond_largeur"<?php $tpl->getitextcheckbox(11); ?> />
												<label for="g_itext_fond_largeur"> Prendre toute la largeur de l'image</label>
											</p>
											<p class="field">
												<label for="g_itext_padding">Marge interne&nbsp;: </label>
												<input type="text" class="text" maxlength="3" size="2" id="g_itext_padding" name="g_itext_padding"<?php $tpl->getitexttext(20); ?> /> pixels
											</p>
										</div>
										
										<div class="separate"></div>

										<p class="field">
											<input type="checkbox" id="g_itext_bordure" name="g_itext_bordure"<?php $tpl->getitextcheckbox(22); ?> />
											<label for="g_itext_bordure"> Bordure&nbsp;:</label>
										</p>
										<div class="field_second">
											<p class="field">
												<label for="g_itext_bordure_color">Couleur de la bordure&nbsp;: </label>
												<input type="text" class="text" maxlength="7" size="7" id="g_itext_bordure_color" name="g_itext_bordure_color"<?php $tpl->getitextcolor(23, 24, 25); ?> />
											</p>
										</div>
										
										<div class="separate"></div>

										<p class="field">
											<input type="checkbox" id="g_itext_ombre" name="g_itext_ombre"<?php $tpl->getitextcheckbox(12); ?> />
											<label for="g_itext_ombre"> Ombrage&nbsp;:</label>
										</p>
										<div class="field_second">
											<p class="field">
												<label for="g_itext_ombre_color">Couleur de l'ombrage&nbsp;: </label>
												<input type="text" class="text" maxlength="7" size="7" id="g_itext_ombre_color" name="g_itext_ombre_color"<?php $tpl->getitextcolor(14, 15, 16); ?> />
											</p>
											<p class="field">
												<label for="g_itext_ombre_width">Épaisseur de l'ombrage&nbsp;: </label>
												<input type="text" class="text" maxlength="2" size="2" id="g_itext_ombre_width" name="g_itext_ombre_width"<?php $tpl->getitexttext(13); ?> /> pixels
											</p>
										</div>

										<div class="separate"></div>

										<p class="field">
											<label for="g_itext_qualite">Qualité de l'image&nbsp;: </label>
											<input type="text" class="text" maxlength="3" size="2" id="g_itext_qualite" name="g_itext_qualite"<?php $tpl->getitexttext(27); ?> />
											<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_itext_qualite');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
										</p>
										<div id="aide_itext_qualite" class="aide_contextuelle" style="display:none">
											<div class="aide_barre">
												<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_itext_qualite');">fermer</a></span>
												<span class="aide_aide">AIDE :</span> <span class="aide_titre">qualité de l'image</span>
											</div>
											<span class="aide_texte">La qualité de l'image doit être un nombre entier compris entre 1 (très mauvaise qualité) et 100 (meilleur qualité). Plus la qualité est grande, plus le poids de l'image augmente.</span>
										</div>
									</div>
								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>