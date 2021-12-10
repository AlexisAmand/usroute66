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
<?php include(dirname(__FILE__) . '/header.php'); ?>
<div id="igalerie">
	<table id="ensemble" cellspacing="15">
		<tr>
			<td id="panneau">
				<div id="div_panneau_1">
				<div id="div_panneau_2">
					<div id="galerie_nom"><p><a href="<?php $tpl->getLink(); ?>" title="Page d'accueil de la galerie"><?php $tpl->getInfo('galerie_nom'); ?></a></p></div>
					<div id="navigation">
						<?php $tpl->getSectionTitre('<div id="red_navigation" class="partjs pan_titre %s"><div><a id="ldp_navigation" href="javascript:void(0);" title="%s">Navigation</a></div></div>', 'navigation'); ?>

						<div id="partie_navigation">
							<div id="liens_site">
								<p><a href="<?php $tpl->getLink(); ?>" title="Page d'accueil de la galerie">Accueil</a>&nbsp;|&nbsp;<a accesskey="3" href="<?php $tpl->getLink('plan'); ?>" title="Afficher l'arborescence de la galerie">Plan</a><?php $tpl->getLink('contact', '&nbsp;|&nbsp;<a accesskey="7" href="%s" title="Contacter le Webmestre">Contact</a>'); ?></p>
								<?php $tpl->getHistoriqueLien(); ?>

							</div>
							<div id="recherche">
								<form action="<?php $tpl->getSearchAction(); ?>" onsubmit="return search_verif(this)" method="get">
									<div>
										<label for="search">
											<?php $tpl->getAdvSearch('<a title="Recherche avancée" href="%s">Recherche</a>', 'Recherche'); ?>&nbsp;:<br />
											<input accesskey="4" name="search" id="search" type="text" />
										</label>
									</div>
								</form>
							</div>
						</div>
					</div>
<?php if ($tpl->display('liens_section')) : ?>
					<div id="liens">
						<?php $tpl->getSectionTitre('<div id="red_liens" class="partjs pan_titre %s"><div><a id="ldp_liens" href="javascript:void(0);" title="%s">Liens</a></div></div>', 'liens'); ?>

						<div id="partie_liens">
							<?php $tpl->getLiens(); ?>

						</div>
					</div>
<?php endif; ?>
<?php if ($tpl->display('hasard_section')) : ?>
					<div id="hasard">
						<?php $tpl->getSectionTitre('<div id="red_hasard" class="partjs pan_titre %s"><div><a id="ldp_hasard" href="javascript:void(0);" title="%s">Image au hasard</a></div></div>', 'hasard'); ?>

						<div id="partie_hasard">
							<ul class="vignettes">
								<li class="v_thumb">
									<span class="env1">
									<span class="env2">
										<?php $tpl->getHasardImg('<a class="img_link" style="background:url(%4$s) no-repeat center center" href="%1$s"><img %2$s alt="%3$s" title="%3$s" src="%5$s/pixtrans.png" />' . "\n" . '</a>'); ?>
									</span>
									</span>
								</li>
							</ul>
							<?php $tpl->getHasardAlb('<span id="has_alb_name"><a href="%s">%s</a></span>'); ?>

						</div>
					</div>
<?php endif; ?>
<?php if ($tpl->display('perso_section')) : ?>
					<div id="options">
						<?php $tpl->getSectionTitre('<div id="red_perso" class="partjs pan_titre %s"><div><a id="ldp_perso" href="javascript:void(0);" title="%s">Personnalisation</a></div></div>', 'perso'); ?>

						<div id="partie_perso">
							<form action="<?php $tpl->getFormAction(); ?>" method="get">
								<div>
									<?php $tpl->getUrlParameters('input'); ?>

									<input type="hidden" name="u" value="1" />
<?php if ($tpl->display('perso_infos')) : ?>
										<div id="afficher">
											<div class="spacer1"></div>
											Montrer...&nbsp;:<br />
											<?php $tpl->getPersoDisplay('recentes', '<label for="montrer_nouvelles"><input name="ra" type="checkbox" id="montrer_nouvelles"%s />&nbsp;Nouvelles images&nbsp;:</label><br />'); ?>

											<?php $tpl->getPersoDisplay('jours', '<label for="nombre_jours"><input name="rj" type="text" value="%s" id="nombre_jours" />&nbsp;derniers jours</label>'); ?>

										</div>
<?php endif; ?>
<?php if ($tpl->display('perso_style')) : ?>
									<div id="style">
										Style&nbsp;:<br />
										<select name="st">
											<?php $tpl->getStyles('<option value="%s"%s>%s</option>'); ?>

										</select>
									</div>
<?php endif; ?>
									<div id="valider"><input class="submit" type="submit" value="Valider"/></div>
								</div>
							</form>
						</div>
					</div>
<?php endif; ?>
<?php require_once(dirname(__FILE__) . '/membres_console.php'); ?>
				&nbsp;
				</div>
				</div>
			</td>
			<td id="affichage">
				<div id="div_affichage">
				<div id="div_affichage2">
					<?php $tpl->getEnlarge('<a href="javascript:void(0);" id="enlarge" title="%s"><img src="%s" alt="%s" %s /></a>'); ?>

					<?php $tpl->getInfo('h1', '<div><h1>%s</h1></div>'); ?>

<?php if ($tpl->display('recherche')) : ?>
					<div id="section_recherche">
						<h2>Recherche avancée</h2>
						<form id="adv_search_form" action="<?php $tpl->getSearchAction(); ?>?section=recherche" method="post" onsubmit="return advsearch_verif(this)">
							<div>
								<fieldset id="adv_search_text">
									<legend>Recherche</legend>
									<div class="fielditems">
										<input id="adv_search_stext" name="s_query" maxlength="255" size="60" class="text" type="text"<?php $tpl->getAdvSearchParams('text'); ?> /><br />
										<div id="adv_search_mode">
											<input type="radio" id="adv_search_mots_et" name="s_mode" value="et"<?php $tpl->getAdvSearchParams('et'); ?> />
											<label for="adv_search_mots_et">Tous ces termes</label>
											&nbsp;
											<input type="radio" id="adv_search_mots_ou" name="s_mode" value="ou"<?php $tpl->getAdvSearchParams('ou'); ?> />
											<label for="adv_search_mots_ou">L'un de ces termes</label>
											&nbsp;
											<span id="adv_search_aidelink"><a href="javascript:void(0)" onclick="document.getElementById('adv_search_aide').style.display=(document.getElementById('adv_search_aide').style.display=='none')?'':'none';document.getElementById('adv_search_aidelink').getElementsByTagName('a')[0].innerHTML=(document.getElementById('adv_search_aide').style.display=='none')?'Aide >>':'<< Aide';">Aide >></a></span>
											<div id="adv_search_aide">
												<p><strong>Vous pouvez utiliser...</strong></p>
												<p><em>les guillemets double</em> pour chercher une expression entière&nbsp;:<br /><span class="adv_search_ex">"une expression"</span></p>
												<p><em>le signe moins</em> devant un terme pour exclure ce terme&nbsp;:<br /><span class="adv_search_ex">-terme</span></p>
												<p><em>les jokers ? et *</em> pour remplacer n'importe quel caractère ou suite de caractères&nbsp;:<br /><span class="adv_search_ex">paris* pic?1</span></p>
											</div>
										</div>
									</div>
								</fieldset>
								<br />
								<fieldset id="adv_search_filtres">
									<legend>Filtres</legend>
									<div class="fielditems">
										<div>
											<input type="checkbox" id="adv_search_nom" name="s_nom"<?php $tpl->getAdvSearchParams('nom'); ?> />
											<label for="adv_search_nom">Nom</label>
										</div>
										<div>
											<input type="checkbox" id="adv_search_path" name="s_path"<?php $tpl->getAdvSearchParams('chemin'); ?> />
											<label for="adv_search_path">Chemin complet</label>											
										</div>
										<div>
											<input type="checkbox" id="adv_search_desc" name="s_desc" <?php $tpl->getAdvSearchParams('description'); ?> />
											<label for="adv_search_desc">Description</label>											
										</div>
<?php if ($tpl->display('tags_section')) : ?>
										<div>
											<input type="checkbox" id="adv_search_mc" name="s_mc" <?php $tpl->getAdvSearchParams('motscles'); ?> />
											<label for="adv_search_mc">Tags</label>
										</div>
<?php endif; ?>
										<div>
											<input type="checkbox" id="adv_search_comments" name="s_com"<?php $tpl->getAdvSearchParams('commentaires'); ?> />
											<label for="adv_search_comments">Commentaires</label>
										</div>
<?php if ($tpl->display('exif')) : ?>
<?php if ($tpl->isExif()) : ?>
										<div id="adv_search_exif">
											<span>Exif</span><br />
											<div>
												<input type="checkbox" id="adv_search_make" name="s_make"<?php $tpl->getAdvSearchParams('exif_make'); ?> />
												<label for="adv_search_make">Marque</label>
											</div>
											<div>
												<input type="checkbox" id="adv_search_model" name="s_model"<?php $tpl->getAdvSearchParams('exif_model'); ?> />
												<label for="adv_search_model">Modèle</label>
											</div>
										</div>
<?php endif; ?>
<?php endif; ?>
									</div>
								</fieldset>
								<fieldset>
									<legend>Albums</legend>
									<div class="fielditems">
										<select id="adv_search_albums_list" size="10" name="s_alb[]" multiple="multiple">
											<?php $tpl->getAdvSearchAlbums(); ?>

										</select>
									</div>
								</fieldset>
								<span id="adv_search_pluslink"><a href="javascript:void(0)" onclick="document.getElementById('adv_search_plus').style.display=(document.getElementById('adv_search_plus').style.display=='none')?'':'none';document.getElementById('adv_search_pluslink').getElementsByTagName('a')[0].innerHTML=(document.getElementById('adv_search_plus').style.display=='none')?'Plus >>':'<< Moins';">Plus >></a></span>
								<div id="adv_search_plus">
									<br />
									<fieldset>
										<legend>Options</legend>
										<div class="fielditems">
											<label for="s_casse">
												<input type="checkbox" id="s_casse" name="s_casse"<?php $tpl->getAdvSearchParams('respect_casse'); ?> />&nbsp;Respecter la casse
											</label><br />
											<label for="s_accents">
												<input type="checkbox" id="s_accents" name="s_accents"<?php $tpl->getAdvSearchParams('respect_accents'); ?> />&nbsp;Respecter les accents
											</label>
										</div>
									</fieldset>
									<br />
									<fieldset>
										<legend>Date</legend>
										<div class="fielditems">
											<input type="checkbox" id="adv_search_date" name="s_date"<?php $tpl->getAdvSearchParams('date'); ?> />
											<label for="adv_search_date">Rechercher par date&nbsp;:</label>
											<div class="adv_search_second">
												<input type="radio" id="adv_search_date_ajout" name="s_date_type" value="date_ajout"<?php $tpl->getAdvSearchParams('date_ajout'); ?> />
												<label for="adv_search_date_ajout">Date de mise en ligne</label>
												&nbsp;
												<input type="radio" id="adv_search_date_creation" name="s_date_type" value="date_creation"<?php $tpl->getAdvSearchParams('date_creation'); ?> />
												<label for="adv_search_date_creation">Date de création</label>
											</div>
											<div class="adv_search_second">
												du&nbsp;
												<?php $tpl->getAdvSearchDate('start'); ?>

												&nbsp;&nbsp;au&nbsp;
												<?php $tpl->getAdvSearchDate('end'); ?>

											</div>
										</div>
									</fieldset>
									<br />
									<fieldset>
										<legend>Dimensions</legend>
										<div class="fielditems">
											<input type="checkbox" id="adv_search_taille" name="s_taille"<?php $tpl->getAdvSearchParams('taille'); ?> />
											<label for="adv_search_taille">Rechercher par dimensions&nbsp;:</label>
											<div class="adv_search_second">
												Largeur&nbsp;:&nbsp;
												<label for="adv_search_width_start">entre</label>
												<input id="adv_search_width_start" name="s_width_start" class="text" type="text" size="6" maxlength="5"<?php $tpl->getAdvSearchParams('taille_width_start'); ?> />
												<label for="adv_search_width_end">et</label>
												<input id="adv_search_width_end" name="s_width_end" class="text" type="text" size="6" maxlength="5"<?php $tpl->getAdvSearchParams('taille_width_end'); ?> />
												pixels
											</div>
											<div class="adv_search_second">
												Hauteur&nbsp;:&nbsp;
												<label for="adv_search_height_start">entre</label>
												<input id="adv_search_height_start" name="s_height_start" class="text" type="text" size="6" maxlength="5"<?php $tpl->getAdvSearchParams('taille_height_start'); ?> />
												<label for="adv_search_height_end">et</label>
												<input id="adv_search_height_end" name="s_height_end" class="text" type="text" size="6" maxlength="5"<?php $tpl->getAdvSearchParams('taille_height_end'); ?> />
												pixels
											</div>
										</div>
									</fieldset>
									<br />
									<fieldset>
										<legend>Poids</legend>
										<div class="fielditems">
											<input type="checkbox" id="adv_search_poids" name="s_poids"<?php $tpl->getAdvSearchParams('poids'); ?> />
											<label for="adv_search_poids">Rechercher par poids&nbsp;:</label>
											<div class="adv_search_second">
												<label for="adv_search_poids_start">entre</label>
												<input id="adv_search_poids_start" name="s_poids_start" class="text" type="text" size="6" maxlength="6"<?php $tpl->getAdvSearchParams('poids_start'); ?> />
												<label for="adv_search_poids_end">et</label>
												<input id="adv_search_poids_end" name="s_poids_end" class="text" type="text" size="6" maxlength="6"<?php $tpl->getAdvSearchParams('poids_end'); ?> />
												Ko
											</div>
										</div>
									</fieldset>
								</div>
								<script type="text/javascript">
								//<![CDATA[
									document.getElementById('adv_search_plus').style.display = 'none';
									document.getElementById('adv_search_aide').style.display = 'none';
								//]]>
								</script>
								<div id="adv_search_submit">
									<input class="submit" type="submit" value="valider" /><br/>
								</div>
							</div>
						</form>
					</div>
<?php endif; ?>
<?php if ($tpl->display('plan')) : ?>
					<div id="section_plan">
						<h2>Plan de la galerie</h2>
						<p><?php $tpl->getPlanStats(); ?></p>
						<?php $tpl->getPlan(); ?>

					</div>
<?php endif; ?>
<?php if ($tpl->display('contact')) : ?>
					<?php $tpl->getContactRapport(); ?>
<?php if ($tpl->display('contact_form')) : ?>
					<div id="section_contact">
						<h2>Contacter l'administrateur</h2>
						<form id="form_fcontact" action="" method="post">
							<div>
								<?php $tpl->getContact('text', '<p id="contact_msg">%s</p>'); ?>
								<input type="hidden" class="hidden" name="molpac" value="<?php $tpl->getMolpac(); ?>" />
								<label for="contact_nom">Votre nom&nbsp;:</label><br />
								<input <?php if (isset($_POST['contact_nom'])) echo 'value="' . $_POST['contact_nom'] . '" '; ?>maxlength="255" size="50" type="text" class="text" id="contact_nom" name="contact_nom" /><br />
								<br />
								<label for="contact_mail">Votre adresse courriel&nbsp;:</label><br />
								<input <?php if (isset($_POST['contact_mail'])) echo 'value="' . $_POST['contact_mail'] . '" '; ?>maxlength="255" size="50" type="text" class="text" id="contact_mail" name="contact_mail" /><br />
								<br />
								<label for="contact_sujet">Sujet de votre message&nbsp;:</label><br />
								<input <?php if (isset($_POST['contact_sujet'])) echo 'value="' . $_POST['contact_sujet'] . '" '; ?>maxlength="255" size="50" type="text" class="text" id="contact_sujet" name="contact_sujet" /><br />
								<br />
								<label for="contact_message">Votre message&nbsp;:</label><br />
								<textarea rows="10" cols="55" id="contact_message" name="contact_message"><?php if (isset($_POST['contact_message'])) echo $_POST['contact_message']; ?></textarea>
								<br />
								<input class="submit" value="envoyer" type="submit" />
							</div>
						</form>
					</div>
<?php endif; ?>
<?php endif; ?>
<?php if ($tpl->display('pass')) : ?>
<?php if (isset($_POST['password'])) : ?>
					<p id="msg_erreur"><span>Mauvais mot de passe !</span></p>
<?php endif;?>
					<div id="password">
						<div id="password2">
						<form action="" method="post">
							<div>
								<label for="section_pass">
									Veuillez entrer le mot de passe pour accéder à <?php $tpl->getInfo('obj_nom'); ?>&nbsp;:<br />
									<input class="text" size="40" type="password" id="section_pass" name="password" />
								</label>
								<input class="submit" type="submit" value="valider" />
							</div>
						</form>
						</div>
						<br />
						<p><a href="<?php $tpl->getGalerieAccueil(); ?>">retour à la galerie</a></p>
					</div>
<?php endif; ?>
<?php if ($tpl->display('historique')) : ?>
					<div id="historique">
						<p id="historique_titre"><?php $tpl->getHistoriqueObjet(); ?></p>

						<table id="h_tablo">
							<tr>
								<?php $tpl->getHistorique('dates_ajout', '<td id="date_ajout"><div id="date_ajout_div"><p class="h_titre">Dates de mise en ligne</p>%s</div></td>'); ?>

								<?php $tpl->getHistorique('dates_creation', '<td id="date_creation"><div id="date_creation_div"><p class="h_titre">Dates de création</p>%s</div></td>'); ?>

							</tr>
						</table>
					</div>
<?php endif; ?>
<?php if ($tpl->display('tags')) : ?>
					<div id="all_tags">
						<p><?php $tpl->getTagsCloudTitle(); ?></p>
						<div><?php $tpl->getTags('<ul>%s</ul>'); ?></div>
					</div>
<?php endif; ?>
				</div>
				</div>
			</td>
		</tr>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
