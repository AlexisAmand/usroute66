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
			<p id="co_position"><?php $tpl->getMembres('position'); ?></p>

			<div id="co_perso">
				<form action="index.php?section=utilisateurs&amp;page=membres&amp;startnum=<?php $tpl->getInfo('startnum'); ?><?php $tpl->getMembres('search_params', 'params', '%s'); ?><?php $tpl->getMembres('groupe_param', 'params', '%s'); ?>" method="post">
					<div id="co_recherche"><a class="lien_jsd" href="javascript:co_partied('co_search');"><span>recherche</span></a></div>
					<div id="co_display">
						<?php $tpl->getVID(); ?>

						<div id="co_nbco">
							Nb. par page&nbsp;:
							<select name="nb">
								<?php $tpl->getMembres('nb_membres', '<option value="%s"%s>%s</option>', 100); ?>

							</select>
						</div>
						<div id="co_sort">
							Trier par&nbsp;:
							<select name="sort">
								<option<?php $tpl->getMembres('ordre', 'login'); ?> value="login">nom</option>
								<option<?php $tpl->getMembres('ordre', 'date_creation'); ?> value="date_creation">date d'inscription</option>
								<option<?php $tpl->getMembres('ordre', 'date_derniere_visite'); ?> value="date_derniere_visite">date de dernière visite</option>
							</select>
							<select class="asc-desc" name="sens">
								<option<?php $tpl->getMembres('sens', 'ASC'); ?> value="ASC">croissant</option>
								<option<?php $tpl->getMembres('sens', 'DESC'); ?> value="DESC">décroissant</option>
							</select>
						</div>
						<input class="submit co_dis_submit" type="submit" value="OK" />
					</div>
				</form>
				<div id="co_nav">
					<form action="index.php" method="get">
						<div>
							<input type="hidden" name="section" value="utilisateurs" />
							<input type="hidden" name="page" value="membres" />
							<?php $tpl->getMembres('search_params', 'inputs', '%s'); ?>
							<?php $tpl->getMembres('groupe_param', 'inputs', '%s'); ?>

							groupe&nbsp;:
							<?php $tpl->getMembres('groupe', '<option value="%s">%s</option>'); ?>

							<input class="submit co_dis_submit" type="submit" value="OK" />
						</div>
					</form>
				</div>
			</div>
			<div class="co_float_conteneur">
			<div style="display:none" class="co_float_objet" id="co_search">
				<form action="index.php" method="get" onsubmit="return search_verif(this)">
					<div class="co_float_barre">
						<span class="co_float_fermer"><a href="javascript:co_partied('co_search');">fermer</a></span>
						<span class="co_float_titre">recherche</span>
					</div>
					<div id="co_search_options">
						<input type="hidden" name="section" value="utilisateurs" />
						<input type="hidden" name="page" value="membres" />
						<input type="hidden" name="u" value="1" />
						<input type="hidden" name="s" value="1" />
						<span>Chercher&nbsp;:</span>
						<input id="co_search_itext" name="search" maxlength="255" size="55" class="text" type="text"<?php if (!empty($_GET['search'])) echo ' value="' . htmlspecialchars($_GET['search']) . '"'; ?> />
						<input class="submit" type="submit" value="Go!" /><br />
						<span class="co_search_group">
							Dans&nbsp;&nbsp;
							<label for="s_nom">
								<input type="checkbox" id="s_nom" name="s_nom"<?php $tpl->getMembres('search', 's_nom', 1); ?> />&nbsp;Nom
							</label>
							<label for="s_mail">
								<input type="checkbox" id="s_mail" name="s_mail"<?php $tpl->getMembres('search', 's_mail', 0); ?> />&nbsp;Courriel
							</label>
							<label for="s_web">
								<input type="checkbox" id="s_web" name="s_web"<?php $tpl->getMembres('search', 's_web', 0); ?> />&nbsp;Site Web
							</label>
							<label for="s_ip_creation">
								<input type="checkbox" id="s_ip_creation" name="s_ip_creation"<?php $tpl->getMembres('search', 's_ip_creation', 0); ?> />&nbsp;IP inscription
							</label>
							<label for="s_ip_derniere">
								<input type="checkbox" id="s_ip_derniere" name="s_ip_derniere"<?php $tpl->getMembres('search', 's_ip_derniere', 0); ?> />&nbsp;IP dernière visite
							</label>
						</span>
						<span class="co_search_group">
							<label for="s_date">
								<input type="checkbox" id="s_date" name="s_date"<?php $tpl->getMembres('search', 's_date', 0); ?> />&nbsp;Rechercher par date :
							</label>
							<label for="s_date_creation">
								<input type="radio" id="s_date_creation" value="creation" name="s_date_type"<?php $tpl->getMembres('search', 's_date_creation', 0); ?> />&nbsp;d'inscription
							</label>
							<label for="s_date_derniere">
								<input type="radio" id="s_date_derniere" value="derniere" name="s_date_type"<?php $tpl->getMembres('search', 's_date_derniere', 0); ?> />&nbsp;de dernière visite
							</label>
						</span>
						<span class="co_search_group">
							entre&nbsp;&nbsp;-
							<select name="s_dnpc">
								<?php $tpl->getMembres('search_date_nb', '<option value="%1$s"%2$s>%1$s</option>', 's_dnpc'); ?>

							</select>
							<select name="s_dnpd">
								<option value="h"<?php $tpl->getMembres('search', 's_dnpd', 0, 'h'); ?>>heures</option>
								<option value="j"<?php $tpl->getMembres('search', 's_dnpd', 1, 'j'); ?>>jours</option>
								<option value="s"<?php $tpl->getMembres('search', 's_dnpd', 0, 's'); ?>>semaines</option>
								<option value="a"<?php $tpl->getMembres('search', 's_dnpd', 0, 'a'); ?>>années</option>
							</select>
							&nbsp;et&nbsp;&nbsp;-
							<select name="s_dnsc">
								<?php $tpl->getMembres('search_date_nb', '<option value="%1$s"%2$s>%1$s</option>', 's_dnsc', 5); ?>

							</select>
							<select name="s_dnsd">
								<option value="h"<?php $tpl->getMembres('search', 's_dnsd', 0, 'h'); ?>>heures</option>
								<option value="j"<?php $tpl->getMembres('search', 's_dnsd', 1, 'j'); ?>>jours</option>
								<option value="s"<?php $tpl->getMembres('search', 's_dnsd', 0, 's'); ?>>semaines</option>
								<option value="a"<?php $tpl->getMembres('search', 's_dnsd', 0, 'a'); ?>>années</option>
							</select>
						</span>
					</div>
				</form>
			</div>
			</div>

			<?php $tpl->getRapport('%s<br/>'); ?>
			<?php $tpl->getGeneralMaj('%s<br/>'); ?>

			<?php $tpl->getMembres('search_result', '<p id="search_result">Résultat de la recherche « <span>%s</span> » :</p>', '<p id="search_result">Aucun membre trouvé pour la recherche « <span>%s</span> ».</p><br /><br /><br /><br /><br /><br /><br />'); ?>

<?php if ($tpl->display('barre_nav')) : ?>
			<div class="barre_nav" id="barre_nav_haut">
				<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
				<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
				<form class="js_auto" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
					<div>
						<?php $tpl->getMembres('search_params', 'inputs', '%s'); ?><?php $tpl->getMembres('groupe_param', 'inputs', '%s'); ?><input type="hidden" name="section" value="utilisateurs" /><input type="hidden" name="page" value="membres" /><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=utilisateurs&amp;page=membres<?php $tpl->getMembres('search_params', 'params'); ?><?php $tpl->getMembres('groupe_param', 'params'); ?>';"><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
					</div>
				</form>
				<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
				<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
			</div>
<?php endif; ?>

				<div class="js_coche">
					<a class="lien_js" href="javascript:membres_select_all(1);">tout sélectionner</a>
					- 
					<a class="lien_js" href="javascript:membres_invert_select();">inverser la sélection</a>
					&nbsp;&nbsp;&nbsp;
					<a class="lien_js" href="javascript:membre_details_all(1);">tout montrer</a>
					- 
					<a class="lien_js" href="javascript:membre_details_all(0);">tout cacher</a>

				</div>

				<form action="index.php?section=utilisateurs&amp;page=membres" method="post">
					<div>
					<?php $tpl->getMembres('search_params', 'inputs', '%s'); ?>
					<?php $tpl->getMembres('groupe_param', 'inputs', '%s'); ?>

					<?php $tpl->getVID(); ?>

					<?php $tpl->getMembres('zero', '<br /><br /><br />'); ?>

<?php while ($tpl->getNextMember()) : ?>
						<div class="users_membres">
							<div class="users_membres_haut">
								<div class="users_membres_avatar"><?php $tpl->getMembre('avatar'); ?></div>
								<div class="users_membres_infos">
								<div class="users_membres_infos_bis">
								<div class="users_membres_checkbox"><input name="membres[<?php $tpl->getMembre('id'); ?>][delete]" type="checkbox" /></div>
									<div class="users_membres_groupe">
										<?php $tpl->getMembre('groupe'); ?>
										<div class="users_membres_display_details"><a class="lien_jsd" href="javascript:membre_details(<?php $tpl->getMembre('id'); ?>);"><span>détails</span></a></div>
									</div>
									<span class="users_membres_nom"><?php $tpl->getMembre('nom'); ?></span>
									<div class="users_membres_web"><?php $tpl->getMembre('mail'); ?><?php $tpl->getMembre('web', ' - '); ?></div>
								</div>
								</div>
							</div>
							<div style="display:none" class="users_membres_details" id="users_membres_details_<?php $tpl->getMembre('id'); ?>">
								<table>
									<tr><td>date d'inscription</td><td><?php $tpl->getMembre('date_creation'); ?></td></tr>
									<tr><td>IP d'inscription</td><td><?php $tpl->getMembre('ip_creation'); ?></td></tr>
									<tr><td>dernière visite</td><td><?php $tpl->getMembre('date_derniere_visite'); ?></td></tr>
									<tr><td>IP de dernière visite</td><td><?php $tpl->getMembre('ip_derniere_visite'); ?></td></tr>
								</table>
							</div>
						</div>
<?php endwhile; ?>

					</div>
					<div id="users_membres_mass">
						<div class="submit_changes users_membres_mass"><input name="mass_groupes" type="submit" class="submit" value="valider les changements" /></div>
						<div class="submit_changes users_membres_mass"><input onclick="return confirm_membres_delete();" name="mass_delete" type="submit" class="submit" value="supprimer les membres sélectionnés" /></div>
					</div>
				</form>

<?php if ($tpl->display('barre_nav')) : ?>
			<div class="barre_nav" id="barre_nav_bas">
				<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
				<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
				<form class="js_auto" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
					<div>
						<?php $tpl->getMembres('search_params', 'inputs', '%s'); ?><?php $tpl->getMembres('groupe_param', 'inputs', '%s'); ?><input type="hidden" name="section" value="utilisateurs" /><input type="hidden" name="page" value="membres" /><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=utilisateurs&amp;page=membres<?php $tpl->getMembres('search_params', 'params'); ?><?php $tpl->getMembres('groupe_param', 'params'); ?>';"><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
					</div>
				</form>
				<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
				<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
			</div>
<?php endif; ?>
