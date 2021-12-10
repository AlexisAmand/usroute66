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

<?php if ($tpl->display('categories_voisines')) : ?>
							<div id="categories">
								<form action="<?php $tpl->getFormAction(); ?>" method="get">
									<div>
										Catégories voisines&nbsp;:<br />
										<select name="cat" onchange="if (this.options[this.selectedIndex].value) window.location.href=this.options[this.selectedIndex].value;">
											<?php $tpl->getObjetsVoisins('<option value="%s"%s>%s</option>'); ?>

										</select>
									</div>
								</form>
							</div>
<?php endif; ?>
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
										<?php $tpl->getHasardImg('<a class="img_link" style="background:url(%4$s) no-repeat center" href="%1$s"><img %2$s alt="%3$s" title="%3$s" src="%5$s/pixtrans.png" />' . "\n" . '</a>'); ?>
									</span>
									</span>
								</li>
							</ul>
							<?php $tpl->getHasardAlb('<span id="has_alb_name"><a href="%s">%s</a></span>'); ?>

						</div>
					</div>
<?php endif; ?>
<?php if ($tpl->display('tags_section')) : ?>
					<div id="tags">
						<?php $tpl->getSectionTitre('<div id="red_tags" class="partjs pan_titre %s"><div><a id="ldp_tags" href="javascript:void(0);" title="%s">Tags</a></div></div>', 'tags'); ?>

						<div id="partie_tags">
							<?php $tpl->getTags('<ul>%s</ul>'); ?>
							<?php $tpl->getNullTags('<span id="tags_null">Aucun tag.</span>'); ?>
							<?php $tpl->getAllTags('<div id="tags_all"><a href="%s" title="%s">Tous les tags</a></div>'); ?>

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

									<?php $tpl->getInfo('startnum', '<input type="hidden" name="startnum" value="%s"/>'); ?>

									<input type="hidden" name="u" value="1" />
<?php if ($tpl->display('perso_infos')) : ?>
									<div id="afficher">
										Montrer...&nbsp;:<br />
										<?php $tpl->getPersoDisplay('nom', '<label for="montrer_nom"><input name="sa" type="checkbox" id="montrer_nom"%s />&nbsp;Nom des catégories</label><br />'); ?>

										<?php $tpl->getPersoDisplay('nb_images', '<label for="montrer_images"><input name="sn" type="checkbox" id="montrer_images"%s />&nbsp;Nombre d\'images</label><br />'); ?>

										<?php $tpl->getPersoDisplay('recentes', '<label for="montrer_nouvelles"><input name="ra" type="checkbox" id="montrer_nouvelles"%s />&nbsp;Nouvelles images&nbsp;:</label><br />'); ?>

										<?php $tpl->getPersoDisplay('jours', '<label for="nombre_jours"><input name="rj" class="text" type="text" value="%s" id="nombre_jours" />&nbsp;derniers jours</label>'); ?>

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
								<?php $tpl->getStat('images', '<li id="stats_images">%s %s image%s%s</li>'); ?>

								<li id="stats_cat"><?php $tpl->getStat('categories', '%d catégorie%s'); ?></li>
								<li id="stats_alb"><?php $tpl->getStat('albums', '%d album%s'); ?></li>
								<li id="stats_poids"><?php $tpl->getStat('poids', '%s'); ?></li>
								<?php $tpl->getStat('recentes', '<li id="stats_nouvelles">%s %s nouvelle%3$s image%3$s%4$s</li>'); ?>

								<?php $tpl->getStat('hits', '<li id="stats_vues">%s %s visite%s%s</li>'); ?>

								<?php $tpl->getStat('comments', '<li id="stats_comments">%s %s commentaire%s%s'); ?>

								<?php $tpl->getCommentsLink('<a id="lien_comments" title="%s" href="%s"><img src="%s/comments.png" alt="Commentaires" /></a>', '</li>'); ?>

								<?php $tpl->getStat('votes', '<li id="stats_votes">%s %s vote%s%s</li>'); ?>

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

					<?php $tpl->getPosition(); ?>

					<?php $tpl->getGalerieDescription('<div id="galerie_description"><p>%s</p></div>'); ?>

<?php if ($tpl->display('barre_nav')) : ?>
					<div class="barre_nav" id="barre_nav_haut">
						<div class="barre_nav_gauche">&nbsp;</div>
						<div class="page_actuelle"><?php $tpl->getPageActuelle(); ?></div>

						<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>

						<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>

						<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
							<div>
								<select name="startnum" onchange="window.location.href='<?php $tpl->getUrlParameters('page'); ?>' + this.options[this.selectedIndex].value;">
									<?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?>

								</select>
							</div>
						</form>
						<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>

						<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>

					</div>
<?php endif; ?>
					<?php $tpl->getCatDescription('<div id="categorie_description"><p>%s</p></div>'); ?>

<?php if ($tpl->display('thumbs')) : ?>
<?php if (THUMB_CAT_COMPACT) : ?>
					<div id="vignettes_cat">
						<ul id="vignettes" class="vignettes">
							<?php while ($tpl->getThumbsNextLine()) : ?><?php while ($tpl->getThumbsNextThumb()) : ?><li class="v_thumb<?php $tpl->getThumb('recent', ' v_recent'); ?><?php $tpl->getThumb('pass', ' v_pass'); ?>"><span class="env1">
									<span class="env2">
										<a class="img_link<?php if ($tpl->display('thumbs_infos')) : ?> img_infos<?php endif; ?>" style="background:url(<?php $tpl->getThumb('image', '%2$s'); ?>) no-repeat center" href="<?php $tpl->getThumb('lien'); ?>">
											<?php $tpl->getThumb('image', '<img %s src="%4$s/pixtrans.png" alt="%3$s" title="%3$s" />'); ?>

										</a>
										<?php $tpl->getThumb('nom', '<a class="text_link" href="%s"><span class="vignette_nom">%s</span></a>'); ?>

<?php if ($tpl->display('thumbs_infos')) : ?>
										<span class="vignette_infos categorie_infos">
											<?php $tpl->getThumb('nb_images', '<span class="categorie_images">%s%s</span>', 0, '&nbsp;<span class="img_recentes">%s</span>'); ?>

											<?php $tpl->getThumb('poids', '<span class="categorie_poids">%s</span>'); ?>

											<?php $tpl->getThumb('hits', '<span class="categorie_hits%s">%s</span>'); ?>

											<?php $tpl->getThumb('comments', '<span class="categorie_comments%s">%s</span>'); ?>

											<?php $tpl->getThumb('votes', '<span class="image_note%s"><span title="note moyenne: %s">%s</span><br />%s</span>'); ?>

										</span>
<?php endif; ?>
									</span>
								</span></li><?php endwhile; ?><?php endwhile; ?>

						</ul>
					</div>
<?php else : ?>
					<div id="vignettes_cat_extended">
						<div id="vex_vignettes">
							<?php while ($tpl->getThumbsNextLine()) : ?><?php while ($tpl->getThumbsNextThumb()) : ?>
							<div class="vex_vignette<?php $tpl->getThumb('recent', ' vex_recent'); ?><?php $tpl->getThumb('pass', ' vex_pass'); ?>">
								<table><tr>
									<td class="vex_thumb">
										<a class="vex_link" style="background:url(<?php $tpl->getThumb('image', '%2$s'); ?>) no-repeat center" href="<?php $tpl->getThumb('lien'); ?>">
											<?php $tpl->getThumb('image', '<img %s src="%4$s/pixtrans.png" alt="%3$s" title="%3$s" />'); ?>

										</a>
									</td>
									<td class="vex_infos">
										<?php $tpl->getThumb('nom', '<p class="vex_nom"><a href="%s">%s</a></p>', 0); ?><?php $tpl->getThumb('nb_images', '<p class="vex_images">- %s%s</p>', 1, '&nbsp;<span class="img_recentes">%s</span>'); ?>

										<div class="vex_vchp">
										<?php $tpl->getThumb('votes', '<p class="vex_votes%s"><span title="note moyenne: %s">%s</span> (%s)</p>', 1); ?>

										<?php $tpl->getThumb('comments', '<p class="vex_comments%s">%s</p>', 1); ?>

										<?php $tpl->getThumb('hits', '<p class="vex_hits%s">%s</p>', 1); ?>

										<?php $tpl->getThumb('poids', '<p class="vex_poids">%s</p>'); ?>

										</div>

										<?php $tpl->getThumb('description', '<p class="vex_desc">%s</p>'); ?>

									</td>
								</tr></table>
							</div>
							<?php endwhile; ?><?php endwhile; ?>
						</div>
					</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($tpl->display('barre_nav')) : ?>
					<div class="barre_nav" id="barre_nav_bas">
						<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>

						<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>

						<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
							<div>
								<select name="startnum" onchange="window.location.href='<?php $tpl->getUrlParameters('page'); ?>' + this.options[this.selectedIndex].value;">
									<?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?>

								</select>
							</div>
						</form>
						<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>

						<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>

					</div>
<?php endif; ?>
<?php if ($tpl->display('rss_objet')) : ?>
					<ul id="rss_objet">
						<li><?php $tpl->getRSSImages('<a %s href="%s">fil des images</a>'); ?></li>
						<?php $tpl->getRSSCommentaires('<li><a %s href="%s">fil des commentaires</a></li>'); ?>

					</ul>
<?php endif; ?>
				</div>
				</div>
			</td>
		</tr>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
