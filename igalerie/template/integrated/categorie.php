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
		<div style="display:none" id="lien_options"><a id="search_link" class="lien_js" href="javascript:montrer('recherche');">recherche</a></div>

		<?php if (!GALERIE_INTEGRATED) { $tpl->getInfo('h1', '<div id="gal_titre"><h1>%s</h1></div>'); } else { $tpl->getInfo('h1', '<div id="gal_titre"><h2>%s</h2></div>'); } ?>

		<div id="barre_position">
			<?php $tpl->getPosition(); ?>

			<div style="display:none" id="recherche">
				<form action="<?php $tpl->getSearchAction(); ?>" method="get">
					<div>
						<input type="hidden" name="test" value="1" />
						<label for="search">
							<?php $tpl->getAdvSearch('<a title="Recherche avancée" href="%s">Recherche</a>', 'Recherche'); ?>&nbsp;:
							<input<?php $tpl->getSearchText(); ?> accesskey="4" name="search" id="search" class="text" type="text" />
						</label>
						<input type="submit" class="submit" value="OK" />
					</div>
				</form>
			</div>
		</div>

		<?php $tpl->getGalerieDescription('<div id="galerie_description"><p>%s</p></div>'); ?>

<?php include(dirname(__FILE__) . '/barre_nav_haut.php'); ?>
		<?php $tpl->getCatDescription('<div id="categorie_description"><p>%s</p></div>'); ?>

<?php if ($tpl->display('thumbs')) : ?>
<?php if (THUMB_CAT_COMPACT) : ?>
		<div id="vignettes_cat">
			<ul id="vignettes">
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

<?php include(dirname(__FILE__) . '/barre_nav_bas.php'); ?>
<?php if ((empty($_GET['cat']) || $_GET['cat'] == 1) && (empty($_GET['startnum']) || $_GET['startnum'] == 0)) : ?>
		<div id="galerie_stats">
			<p>Statistiques</p>
			<ul>
				<?php $tpl->getStat('images', '<li id="stats_images">%s %s image%s%s</li>'); ?>

				<?php $tpl->getStat('recentes', '<li id="stats_nouvelles">%s %s nouvelle%3$s image%3$s%4$s</li>'); ?>

				<?php $tpl->getStat('hits', '<li id="stats_vues">%s %s visite%s%s</li>'); ?>

				<?php $tpl->getStat('comments', '<li id="stats_comments">%s %s commentaire%s%s'); ?>

				<?php $tpl->getCommentsLink('<a id="lien_comments" title="%s" href="%s"><img src="%s/comments.png" alt="Commentaires" /></a>', '</li>'); ?>

				<?php $tpl->getStat('votes', '<li id="stats_votes">%s %s vote%s%s</li>'); ?>

			</ul>
		</div>
<?php endif; ?>
<?php if ($tpl->display('tags_section')) : ?>
					<div id="tags">
						<div id="partie_tags">
							<?php $tpl->getAllTags('<ul><li id="tags_all"><a href="%s" title="%s">tous les tags</a></li>'); ?>
							<?php $tpl->getTags('%s</ul>'); ?>
							
						</div>
					</div>
<?php endif; ?>
<?php if ($tpl->display('rss_objet')) : ?>
					<ul id="rss_objet">
						<li><?php $tpl->getRSSImages('<a %s href="%s">fil des images</a>'); ?></li>
						<?php $tpl->getRSSCommentaires('<li><a %s href="%s">fil des commentaires</a></li>'); ?>

					</ul>
<?php endif; ?>
	</div>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
