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
								<li id="hasard_vignette" class="v_thumb">
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
							<form action="<?php $tpl->getFormAction(); ?>" onsubmit="return perso_verif(this)" method="get">
								<div>
									<?php $tpl->getUrlParameters('input'); ?>

									<input type="hidden" name="u" value="1" />
<?php if ($tpl->display('image_taille')) : ?>
									<div id="image_taille">
										Dimensions de l'image&nbsp;:<br />
										<div id="img_original">
											<label for="origine">
												<input value="1" name="it" type="radio" id="origine"<?php $tpl->getImageTaille('original', ' checked="checked"'); ?> />
												Taille originale
											</label>
										</div>
										<div id="img_fixed">
											<label for="taille">
												<input value="2" name="it" type="radio" id="taille"<?php $tpl->getImageTaille('fixed', ' checked="checked"'); ?> />
												Taille maximale&nbsp;:<br />
											</label>
											<div id="fixed_hl">
												<label for="img_largeur">
													L.&nbsp;:&nbsp;<input<?php $tpl->getImageTaille('width', ' value="%s"'); ?> maxlength="6" name="il" id="img_largeur" type="text" />
												</label>
												<label for="img_hauteur">
													&nbsp;H.&nbsp;:&nbsp;<input<?php $tpl->getImageTaille('height', ' value="%s"'); ?> maxlength="6" name="ih" id="img_hauteur" type="text" />
												</label><br />
											</div>
										</div>
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
					<div id="stats">
						<?php $tpl->getSectionTitre('<div id="red_stats" class="partjs pan_titre %s"><div><a id="ldp_stats" href="javascript:void(0);" title="%s">Statistiques</a></div></div>', 'stats'); ?>

						<div id="partie_stats">
							<ul>
								<li id="stats_poids"><?php $tpl->getImageStat('poids', 'poids : %s'); ?></li>
								<li id="stats_taille"><?php $tpl->getImageStat('taille', 'dimensions : %s'); ?></li>
								<li id="stats_vues"><?php $tpl->getImageStat('hits', 'visitée : %s fois'); ?></li>
								<?php $tpl->getImageStat('comments', '<li id="stats_comments">commentaires : %s</li>'); ?>

								<?php $tpl->getImageStat('note_star', '<li id="stats_note">note moyenne :<span id="note_stars">%s</span>'); ?>
								<?php $tpl->getImageStat('note', '<span>(%s - '); ?>
								<?php $tpl->getImageStat('votes', '%s)</span></li>'); ?>

								<li id="stats_date_creation"><?php $tpl->getImageStat('date_creation', 'créée le : %s'); ?></li>
								<li id="stats_date"><?php $tpl->getImageStat('date', 'ajoutée le : %s'); ?></li>
								<?php $tpl->getImageStat('auteur', '<li id="stats_auteur">ajoutée par : %s</li>'); ?>

							</ul>
						</div>
					</div>
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

					<?php $tpl->getPositionSpecial(); ?>

					<?php $tpl->getMembre('favori', '<a id="addfav" href="%s">%s</a>'); ?>
					<?php $tpl->getPosition(); ?>

<?php if ($tpl->display('barre_nav_img')) : ?>
					<div class="barre_nav barre_nav_img" id="barre_nav_haut">
						<div class="barre_nav_gauche"><?php $tpl->getDiaporamaLien(); ?></div>
						<div class="page_actuelle"><?php $tpl->getPageActuelle(); ?></div>

						<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>

						<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>

						<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>

						<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>

					</div>
<?php endif; ?>
					<div id="image">
						<div id="image_r">
							<?php $tpl->getImageResizeMsg('<span id="image_r_msg"%s>%s</span>'); ?>

							<?php $tpl->getImage('<img id="img" %s src="%s" alt="%s" />'); ?>

						</div>
					</div>
					<div id="image_fichier">
						<p><?php $tpl->getImgFile(); ?></p>
					</div>
					<?php $tpl->getImageTags('<div id="image_tags"><div>%s</div></div>'); ?>

					<?php $tpl->getImageDescription('<div id="image_description"><p>%s</p></div>'); ?>


<?php if ($tpl->display('exif')) : ?>
					<div class="infos_imeta">
						<div class="infos_imeta_bloc">
							<?php $tpl->getSectionTitre('<div id="red_exif" class="partjs imeta_titre %s"><div><a id="ldp_exif" href="javascript:void(0);" title="%s">Informations Exif</a></div></div>', 'exif'); ?>

							<table class="partie_imeta" id="partie_exif">
								<?php $tpl->getMetadata('exif', '<tr><td class="imeta_desc">%s</td><td>%s</td></tr>', '<tr><td colspan="2" class="imeta_null">Aucune information disponible.</td></tr>'); ?>
							</table>
						</div>
					</div>
<?php endif; ?>

<?php if ($tpl->display('iptc')) : ?>
					<div class="infos_imeta">
						<div class="infos_imeta_bloc">
							<?php $tpl->getSectionTitre('<div id="red_iptc" class="partjs imeta_titre %s"><div><a id="ldp_iptc" href="javascript:void(0);" title="%s">Informations IPTC</a></div></div>', 'iptc'); ?>

							<table class="partie_imeta" id="partie_iptc">
								<?php $tpl->getMetadata('iptc', '<tr><td class="imeta_desc">%s</td><td>%s</td></tr>', '<tr><td colspan="2" class="imeta_null">Aucune information disponible.</td></tr>'); ?>
							</table>
						</div>
					</div>
<?php endif; ?>

<?php if ($tpl->display('add_votes')) : ?>
					<?php $tpl->getUserNote('<div id="image_note">%sVotre note : <span id="note_user">%s</span></div>'); ?>
<?php endif; ?>

<?php if ($tpl->display('comments')) : ?>
					<div id="commentaires">
						<div id="commentaires_bloc">
							<?php $tpl->getSectionTitre('<div id="red_comments" class="partjs comment_titre %s"><div><a id="ldp_comments" href="javascript:void(0);" title="%s">Commentaires</a></div></div>', 'comments', '#commentaires'); ?>

							<div id="partie_comments">
<?php while ($tpl->getNextComment()) : ?>
								<div <?php $tpl->getComment('id', 'id="co%s"'); ?> class="comment<?php $tpl->getComment('co_avatar', ' comment_avatar'); ?><?php $tpl->getComment('pair', ' co_pair'); ?><?php $tpl->getComment('preview', ' co_preview'); ?>">
									<?php $tpl->getComment('avatar', '<img alt="%s" width="50" height="50" src="%s" />'); ?>
									<p class="comment_num"><a href="<?php $tpl->getComment('id', '#co%s'); ?>"><?php $tpl->getComment('num'); ?></a></p>
									<p class="comment_date">le <?php $tpl->getComment('date'); ?></p>
									<p class="comment_auteur"><span><?php $tpl->getComment('auteur'); ?></span><?php $tpl->getComment('site', ' (<a class="co_web_link" title="Site Web de %s" href="%s">site</a>)'); ?> a écrit :</p>
									<p class="comment_message">
										<?php $tpl->getComment('msg'); ?>

									</p>
								</div>
<?php endwhile; ?>

								<?php $tpl->getCommentsNull('<p id="comment_null">Aucun commentaire.</p>'); ?>

								<?php $tpl->getCommentPreview('<p id="comment_preview"><span>Aperçu de votre commentaire.</span></p>'); ?>
							</div>
						</div>

						<?php $tpl->getCommentRejet('<p id="msg_erreur"><span>Votre commentaire a été rejeté pour la raison suivante :<br />%s</span></p>'); ?>

						<?php $tpl->getCommentMod('b', '<div id="msg_succes"><p>Votre commentaire sera affiché après validation par l\'administrateur.</p></div>'); ?>

<?php if ($tpl->display('add_comments')) : ?>
						<div id="commentaires_ajout">
							<p>Ajouter un commentaire</p>
							<form id="comment_form" action="<?php $tpl->getUrlParameters('ipost'); ?>" method="post" onsubmit="return comment_verif(this)">
								<div>
									<input type="hidden" class="hidden" name="molpac" value="<?php $tpl->getMolpac(); ?>" />
<?php if ($tpl->display('membres_connexion')) : ?>
									<label for="co_auteur">
										Votre nom ou pseudo&nbsp;:
										<input maxlength="128" type="text" name="auteur" id="co_auteur"<?php $tpl->getNewComment('auteur', ' value="%s"'); ?> />
									</label>
									<label for="co_mail">
										Courriel<?php $tpl->getNewComment('fac_mail', ' (facultatif)', ' (obligatoire)'); ?> (ne sera pas affiché)&nbsp;:<br />
										<input maxlength="128" type="text" name="courriel" id="co_mail"<?php $tpl->getNewComment('mail', ' value="%s"'); ?> />
									</label>
									<label for="co_site">
										Site Web<?php $tpl->getNewComment('fac_site', ' (facultatif)', ' (obligatoire)'); ?>&nbsp;:
										<input maxlength="128" type="text" name="siteweb" id="co_site"<?php $tpl->getNewComment('site', ' value="%s"'); ?> />
									</label>
<?php endif; ?>
									<label class="js" for="comment">Message&nbsp;:</label>
									<textarea id="comment" name="message" cols="57" rows="6"><?php $tpl->getNewComment('msg'); ?></textarea>
									<?php $tpl->getCommentMod('a', '<span id="co_mod_a">Les commentaires sont modérés.</span>'); ?>

									<input class="submit" type="submit" value="Prévisualiser" name="preview" id="previsualiser" />
									<input class="submit" type="submit" value="Envoyer" />
								</div>
							</form>
							<?php $tpl->getCommentPreview('<script type="text/javascript">var preview = 1;</script>'); ?>

						</div>
<?php endif; ?>
					</div>
<?php endif; ?>
<?php if ($tpl->display('barre_nav_img')) : ?>
					<div class="barre_nav barre_nav_img" id="barre_nav_bas">
						<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>

						<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>

						<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>

						<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>

					</div>
<?php endif; ?>
				<?php $tpl->getRSSCommentaires('<ul id="rss_objet"><li><a %s href="%s">fil des commentaires</a></li></ul>'); ?>

				</div>
				</div>
			</td>
		</tr>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
