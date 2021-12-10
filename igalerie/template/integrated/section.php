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
	<div id="ensemble">
		<?php if (!GALERIE_INTEGRATED) { $tpl->getInfo('h1', '<div id="gal_titre"><h1>%s</h1></div>'); } else { $tpl->getInfo('h1', '<div id="gal_titre"><h2>%s</h2></div>'); } ?>

<?php if ($tpl->display('pass')) : ?>
<?php if (isset($_POST['password'])) : ?>
		<p id="msg_erreur"><span>Mauvais mot de passe !</span></p>
<?php endif;?>
		<div id="password">
			<div id="password2">
			<form action="" method="post">
				<div>
					<label for="section_pass">
						Veuillez entrer le mot de passe pour accéder à <?php $tpl->getInfo('obj_nom'); ?>&nbsp;:<br/>
						<input class="text" size="40" type="password" id="section_pass" name="password" />
					</label>
					<input class="submit" type="submit" value="valider" />
				</div>
			</form>
			</div>
			<br/>
			<p><a href="<?php $tpl->getGalerieAccueil(); ?>">retour à la galerie</a></p>
		</div>
<?php endif; ?>
<?php if ($tpl->display('tags')) : ?>
					<div id="all_tags">
						<p><?php $tpl->getTagsCloudTitle(); ?></p>
						<div><?php $tpl->getTags('<ul>%s</ul>'); ?></div>
					</div>
<?php endif; ?>
<?php if ($tpl->display('recherche')) : ?>
		<div id="section_recherche">
			<p id="position"><a href="<?php $tpl->getGalerieAccueil(); ?>">Accueil</a></p>
			<h3>Recherche avancée</h3>
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
									<input class="text" id="adv_search_width_start" name="s_width_start" type="text" size="6" maxlength="5"<?php $tpl->getAdvSearchParams('taille_width_start'); ?> />
									<label for="adv_search_width_end">et</label>
									<input class="text" id="adv_search_width_end" name="s_width_end" type="text" size="6" maxlength="5"<?php $tpl->getAdvSearchParams('taille_width_end'); ?> />
									pixels
								</div>
								<div class="adv_search_second">
									Hauteur&nbsp;:&nbsp;
									<label for="adv_search_height_start">entre</label>
									<input class="text" id="adv_search_height_start" name="s_height_start" type="text" size="6" maxlength="5"<?php $tpl->getAdvSearchParams('taille_height_start'); ?> />
									<label for="adv_search_height_end">et</label>
									<input class="text" id="adv_search_height_end" name="s_height_end" type="text" size="6" maxlength="5"<?php $tpl->getAdvSearchParams('taille_height_end'); ?> />
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
									<input class="text" id="adv_search_poids_start" name="s_poids_start" type="text" size="6" maxlength="6"<?php $tpl->getAdvSearchParams('poids_start'); ?> />
									<label for="adv_search_poids_end">et</label>
									<input class="text" id="adv_search_poids_end" name="s_poids_end" type="text" size="6" maxlength="6"<?php $tpl->getAdvSearchParams('poids_end'); ?> />
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
	</div>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
