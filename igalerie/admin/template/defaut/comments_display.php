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

			<p id="co_position">
				<?php $tpl->getComment('sortir', '<span><a href="%s">sortir de la recherche</a></span>'); ?>

				<?php $tpl->getCommentPosition(); ?>

			</p>

			<div id="co_perso">
				<form action="index.php?section=commentaires&amp;page=display&amp;<?php $tpl->getInfo('obj_type'); ?>=<?php $tpl->getInfo('obj'); ?>&amp;startnum=<?php $tpl->getInfo('startnum'); ?><?php $tpl->getComment('ih_search', 'params'); ?>" method="post">
					<div id="co_recherche"><a class="lien_jsd" href="javascript:co_partied('co_search');"><span>recherche</span></a></div>
					<div id="co_display">
						<?php $tpl->getVID(); ?>

						Montrer&nbsp;:
						<select name="filtre" id="co_display_montrer">
							<option<?php $tpl->getCommentFiltre('tous'); ?> value="tous">tous</option>
							<option<?php $tpl->getCommentFiltre('actif'); ?> value="actif">activés</option>
							<option<?php $tpl->getCommentFiltre('inactif'); ?> value="inactif">désactivés</option>
						</select> &nbsp;
						<div id="co_nbco">
							Nb. par page&nbsp;:
							<select name="nb">
								<?php $tpl->getCommentNb('<option value="%s"%s>%s</option>', 100); ?>

							</select>
						</div>
						<div id="co_sort">
							Trier par&nbsp;:
							<select name="sort">
								<option<?php $tpl->getCommentSortOrdre('commentaire_auteur'); ?> value="commentaire_auteur">auteur</option>
								<option<?php $tpl->getCommentSortOrdre('commentaire_mail'); ?> value="commentaire_mail">courriel</option>
								<option<?php $tpl->getCommentSortOrdre('commentaire_date'); ?> value="commentaire_date">date</option>
								<option<?php $tpl->getCommentSortOrdre('image_chemin'); ?> value="image_chemin">image</option>
								<option<?php $tpl->getCommentSortOrdre('commentaire_ip'); ?> value="commentaire_ip">IP</option>
								<option<?php $tpl->getCommentSortOrdre('commentaire_web'); ?> value="commentaire_web">site</option>
							</select>
							<select class="asc-desc" name="sens">
								<option<?php $tpl->getCommentSortSens('ASC'); ?> value="ASC">croissant</option>
								<option<?php $tpl->getCommentSortSens('DESC'); ?> value="DESC">décroissant</option>
							</select>
						</div>
						<input class="submit co_dis_submit" type="submit" value="OK" />
					</div>
				</form>
				<span id="co_options"><a class="lien_jsd" href="javascript:co_partied('co_options_float');"><span>options</span></a></span>
				<div id="co_nav">
<?php if ($tpl->display('subcats')) : ?>
					<form action="index.php" method="get">
						<div>
							<input type="hidden" name="section" value="commentaires" />
							<input type="hidden" name="page" value="display" />
							<?php $tpl->getComment('ih_search', 'inputs'); ?>

							Afficher pour&nbsp;:
							<select class="albums_list" name="<?php $tpl->getComment('sub'); ?>">
								<?php $tpl->getCommentSubCats('<option value="%s">%s</option>'); ?>
									
							</select>
							<input class="submit co_dis_submit" type="submit" value="OK" />
						</div>
					</form>
<?php endif; ?>
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
						<input type="hidden" name="section" value="commentaires" />
						<input type="hidden" name="page" value="display" />
						<input type="hidden" name="u" value="1" />
						<input type="hidden" name="s" value="1" />
						<input type="hidden" name="<?php $tpl->getInfo('obj_type'); ?>" value="<?php $tpl->getInfo('obj'); ?>" />
						<?php $tpl->getVID(); ?>

						<span>Chercher&nbsp;:</span>
						<input id="co_search_itext" name="search" maxlength="255" size="55" class="text" type="text"<?php if (!empty($_GET['search'])) echo ' value="' . htmlspecialchars($_GET['search']) . '"'; ?> />
						<input class="submit" type="submit" value="Go!" /><br />
						<span class="co_search_group">
							Dans&nbsp;&nbsp;
							<label for="s_msg">
								<input type="checkbox" id="s_msg" name="s_msg"<?php $tpl->getCommentSearch('s_msg', 1); ?> />&nbsp;Message
							</label>
							<label for="s_auteur">
								<input type="checkbox" id="s_auteur" name="s_auteur"<?php $tpl->getCommentSearch('s_auteur', 0); ?> />&nbsp;Auteur
							</label>
							<label for="s_ip">
								<input type="checkbox" id="s_ip" name="s_ip"<?php $tpl->getCommentSearch('s_ip', 0); ?> />&nbsp;IP
							</label>
							<label for="s_mail">
								<input type="checkbox" id="s_mail" name="s_mail"<?php $tpl->getCommentSearch('s_mail', 0); ?> />&nbsp;Courriel
							</label>
							<label for="s_web">
								<input type="checkbox" id="s_web" name="s_web"<?php $tpl->getCommentSearch('s_web', 0); ?> />&nbsp;Site Web
							</label>
						</span>
						<span class="co_search_group">
							<label for="s_tous">
								<input type="checkbox" id="s_tous" name="s_tous"<?php $tpl->getCommentSearch('s_tous', 1); ?> />&nbsp;Rechercher tous les termes
							</label>
							<label for="s_casse">
								<input type="checkbox" id="s_casse" name="s_casse"<?php $tpl->getCommentSearch('s_casse', 0); ?> />&nbsp;Respecter la casse
							</label>
							<label for="s_accents">
								<input type="checkbox" id="s_accents" name="s_accents"<?php $tpl->getCommentSearch('s_accents', 0); ?> />&nbsp;Respecter les accents
							</label>
						</span>
						<span class="co_search_group">
							<label for="s_date">
								<input type="checkbox" id="s_date" name="s_date"<?php $tpl->getCommentSearch('s_date', 0); ?> />&nbsp;Rechercher par date :
							</label>
							entre&nbsp;&nbsp;-
							<select name="s_dnpc">
								<?php $tpl->getCommentSearchNb('<option value="%1$s"%2$s>%1$s</option>', 0, 30, 's_dnpc'); ?>

							</select>
							<select name="s_dnpd">
								<option value="h"<?php $tpl->getCommentSearch('s_dnpd', 0, 'h'); ?>>heures</option>
								<option value="j"<?php $tpl->getCommentSearch('s_dnpd', 1, 'j'); ?>>jours</option>
								<option value="s"<?php $tpl->getCommentSearch('s_dnpd', 0, 's'); ?>>semaines</option>
								<option value="a"<?php $tpl->getCommentSearch('s_dnpd', 0, 'a'); ?>>années</option>
							</select>
							&nbsp;et&nbsp;&nbsp;-
							<select name="s_dnsc">
								<?php $tpl->getCommentSearchNb('<option value="%1$s"%2$s>%1$s</option>', 0, 30, 's_dnsc', 5); ?>

							</select>
							<select name="s_dnsd">
								<option value="h"<?php $tpl->getCommentSearch('s_dnsd', 0, 'h'); ?>>heures</option>
								<option value="j"<?php $tpl->getCommentSearch('s_dnsd', 1, 'j'); ?>>jours</option>
								<option value="s"<?php $tpl->getCommentSearch('s_dnsd', 0, 's'); ?>>semaines</option>
								<option value="a"<?php $tpl->getCommentSearch('s_dnsd', 0, 'a'); ?>>années</option>
							</select>
						</span>
					</div>
				</form>
			</div>
			</div>
			<div class="co_float_conteneur">
			<div style="display:none" class="co_float_objet" id="co_options_float">
				<form action="index.php?section=commentaires&amp;page=display&amp;u=1&amp;<?php $tpl->getInfo('obj_type'); ?>=<?php $tpl->getInfo('obj'); ?>&amp;startnum=<?php $tpl->getInfo('startnum'); ?><?php $tpl->getComment('ih_search', 'params'); ?>" method="post">
					<div class="co_float_barre">
						<span class="co_float_fermer"><a href="javascript:co_partied('co_options_float');">fermer</a></span>
						<span class="co_float_titre">options</span>
					</div>
					<div id="co_options_options">
						<?php $tpl->getVID(); ?>

						<input class="submit" type="submit" value="Valider" />
						Par défaut&nbsp;:
						<select name="o_msg_display">
							<option value="montrer"<?php $tpl->getCommentMsgDisplay(1); ?>>montrer</option>
							<option value="cacher"<?php $tpl->getCommentMsgDisplay(0); ?>>cacher</option>
						</select>
						tous les messages
					</div>
				</form>
			</div>
			</div>
<?php if ($tpl->display('barre_nav')) : ?>
			<div class="barre_nav" id="barre_nav_haut">
				<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
				<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
				<form class="js_auto" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
					<div>
						<input type="hidden" name="section" value="commentaires" /><input type="hidden" name="page" value="display" /><input type="hidden" name="<?php $tpl->getInfo('obj_type'); ?>" value="<?php $tpl->getInfo('obj'); ?>" /><?php $tpl->getComment('ih_search', 'inputs'); ?><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=commentaires&amp;page=display&amp;<?php $tpl->getInfo('obj_type'); ?>=<?php $tpl->getInfo('obj'); ?><?php $tpl->getComment('ih_search', 'params'); ?>';"><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
					</div>
				</form>
				<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
				<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
			</div>
<?php endif; ?>
			<?php $tpl->getRapport('%s<br />'); ?>
			<?php $tpl->getGeneralMaj('%s<br />'); ?>

			<?php $tpl->getComment('search_result', '<p id="search_result">Résultat de la recherche « <span>%s</span> » :</p>', '<p id="search_result">Aucun commentaire trouvé pour la recherche « <span>%s</span> ».</p>'); ?>
			<?php $tpl->getComment('search_hl', '<div id="highlight_search"><a class="lien_js" href="javascript:highlight_search()">surligner les mots trouvés</a></div>'); ?>

			<?php $tpl->getComment('no_comments', '<div class="rapport_msg rapport_infos"><div><span>La galerie ne contient aucun commentaire%s.</span></div></div>'); ?>

<?php if (!$tpl->display('co_vide')) : ?>
			<form action="index.php?section=commentaires&amp;page=display&amp;mass=1&amp;<?php $tpl->getInfo('obj_type'); ?>=<?php $tpl->getInfo('obj'); ?>" method="post" onsubmit="return confirm_comment_mass();">
				<div>
					<?php $tpl->getComment('ih_search', 'inputs', '%s'); ?>

					<?php $tpl->getVID(); ?>

				</div>

				<div class="js_coche">
					<a class="lien_js" href="javascript:comment_select_all(1);">tout sélectionner</a>
					- 
					<a class="lien_js" href="javascript:comment_select_all(0);">tout déselectionner</a>
					&nbsp;&nbsp;&nbsp;
					<a class="lien_js" href="javascript:comment_display_all(1);">tout montrer</a>
					- 
					<a class="lien_js" href="javascript:comment_display_all(0);">tout cacher</a>

				</div>

<?php while ($tpl->getNextComment()) : ?>
				<div class="comment<?php $tpl->getComment('inactif', ' comment_inactif'); ?>">
					<div class="comment_top">
						<div class="comment_thumb" onmouseover="this.style.cursor='pointer'" onclick="window.location='<?php $tpl->getComment('image', '%s'); ?>'">
							<table><tr><td><?php $tpl->getComment('thumb', 70); ?></td></tr></table>
						</div>
						<div class="comment_details">
						<div class="comment_details_bis">
							<div class="comment_img">
								<div class="comment_id">
									<label for="comment_id_<?php $tpl->getComment('id'); ?>">#<?php $tpl->getComment('id'); ?></label>
									<input name="comment_id[<?php $tpl->getComment('id'); ?>]" id="comment_id_<?php $tpl->getComment('id'); ?>" type="checkbox" />
								</div>
								<span class="comment_image"><span class="comment_ta">Album:</span> <?php $tpl->getComment('album', '<span class="comment_album"><a href="../%s?alb=%s">%s</a></span>'); ?></span>
							</div>
							<div class="comment_infos">
								<span class="comment_date">Le <?php $tpl->getComment('date'); ?></span>, par
								<span class="comment_auteur"><?php $tpl->getComment('auteur'); ?></span>
								(<span class="comment_ip"><?php $tpl->getComment('ip'); ?></span><?php $tpl->getComment('courriel', '<span class="comment_mail"> - %s</span>'); ?><?php $tpl->getComment('site', '<span class="comment_site"> - %s</span>'); ?>)
							</div>
							<div class="comment_actions">
								<span class="comment_active"><?php $tpl->getComment('visible', 'activer', 'désactiver'); ?></span>
								<span class="comment_supp"><?php $tpl->getComment('supprime', 'supprimer'); ?></span>
								<span class="comment_ban_auteur"><?php $tpl->getComment('ban_auteur', 'autoriser&nbsp;l\'auteur', 'bannir&nbsp;l\'auteur'); ?></span>
								<span class="comment_ban_ip"><?php $tpl->getComment('ban_ip', 'autoriser&nbsp;l\'IP', 'bannir&nbsp;l\'IP'); ?></span>
								<span class="comment_display"><a class="lien_jsd" href="javascript:comment_display('<?php $tpl->getComment('id'); ?>');"><span>message</span></a></span>
							</div>
						</div>
						</div>
					</div>
					<div<?php if (!$tpl->display('comment_msg')) : ?> style="display:none"<?php endif; ?> class="comment_msg" id="comment_msg_<?php $tpl->getComment('id'); ?>">
						<p><?php $tpl->getComment('msg'); ?></p>
					</div>
				</div>

<?php endwhile; ?>

				<p class="co_mass_action">
					pour la sélection&nbsp;:
					<select name="co_mass_action" id="co_mass_action_select">
						<option value="activer">activer</option>
						<option value="desactiver">désactiver</option>
						<option value="supprimer">supprimer</option>
						<option value="ban_auteurs">bannir les auteurs</option>
						<option value="ban_ip">bannir les IP</option>
						<option value="aut_auteurs">autoriser les auteurs</option>
						<option value="aut_ip">autoriser les IP</option>
					</select>
					<input class="submit co_dis_submit" type="submit" value="OK" />
				</p>

			</form>
<?php else : ?>
			<br /><br /><br /><br /><br /><br />
<?php endif; ?>


<?php if ($tpl->display('barre_nav')) : ?>
			<div class="barre_nav" id="barre_nav_bas">
				<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
				<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
				<form class="js_auto" action="./" method="get">
					<div>
						<input type="hidden" name="section" value="commentaires" /><input type="hidden" name="page" value="display" /><input type="hidden" name="<?php $tpl->getInfo('obj_type'); ?>" value="<?php $tpl->getInfo('obj'); ?>" /><?php $tpl->getComment('ih_search', 'inputs'); ?><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=commentaires&amp;page=display&amp;<?php $tpl->getInfo('obj_type'); ?>=<?php $tpl->getInfo('obj'); ?><?php $tpl->getComment('ih_search', 'params'); ?>';"><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
					</div>
				</form>
				<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
				<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
			</div>
<?php endif; ?>
