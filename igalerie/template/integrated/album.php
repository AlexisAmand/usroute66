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

		<div style="display:none" id="lien_options"><?php if ($tpl->display('perso_section')) : ?><a class="lien_js" href="javascript:montrer('options');">options</a>&nbsp;&nbsp;<?php endif; ?><a id="search_link" class="lien_js" href="javascript:montrer('recherche');">recherche</a></div>
		<?php if (!GALERIE_INTEGRATED) { $tpl->getInfo('h1', '<div id="gal_titre"><h1>%s</h1></div>'); } else { $tpl->getInfo('h1', '<div id="gal_titre"><h2>%s</h2></div>'); } ?>

		<div id="barre_position">
<?php if (isset($_GET['search'])) : ?>
			<div id="lien_retour"><a href="<?php $tpl->getGalerieAccueil(); ?>">accueil</a></div>
<?php endif; ?>
			<?php $tpl->getPosition(1, '<div id="position">%s%s %3$s'); ?>
			<?php $tpl->getStat('images', '<span id="stats_images">(%2$s image%3$s)</span>', '', 1); ?>
			<?php $tpl->getPosition(1, '</div>'); ?>
		
<?php if ($tpl->display('perso_section')) : ?>
			<div style="display:none" id="options">
				<form action="<?php $tpl->getFormAction(); ?>" onsubmit="return perso_verif(this)" method="get">
					<div id="valider"><input class="submit" type="submit" value="Valider"/></div>
					<div>
						<?php $tpl->getUrlParameters('input'); ?>
						<?php $tpl->getInfo('startnum', '<input type="hidden" name="startnum" value="%s" />'); ?>

						<input type="hidden" name="u" value="1" />
<?php if ($tpl->display('perso_thumbs')) : ?>
						<div id="thumbs">
							Nombre de vignettes&nbsp;:
							<select name="vn">
								<?php $tpl->getPersoThumbs('col', '<option value="%s"%s>%s</option>', 12); ?>

							</select>
							X
							<select name="vl">
								<?php $tpl->getPersoThumbs('line', '<option value="%s"%s>%s</option>', 20); ?>

							</select>
						</div>
<?php endif; ?>
<?php if ($tpl->display('perso_sort')) : ?>
						<div id="order">
							Trier les images par&nbsp;:
							<select name="io" id="liste_trie">
								<option<?php $tpl->getPersoSortOrdre('nom'); ?>	 value="n">Nom</option>
								<option<?php $tpl->getPersoSortOrdre('poids'); ?> value="p">Poids</option>
								<option<?php $tpl->getPersoSortOrdre('taille'); ?> value="t">Dimensions</option>
								<option<?php $tpl->getPersoSortOrdre('hits'); ?> value="h">Visites</option>
								<option<?php $tpl->getPersoSortOrdre('date'); ?> value="d">Date d'ajout</option>
								<option<?php $tpl->getPersoSortOrdre('date_creation'); ?> value="m">Date création</option>
								<?php $tpl->getPersoSortOrdre('commentaires', '<option%s value="c">Commentaires</option>'); ?>

								<?php $tpl->getPersoSortOrdre('votes', '<option%s value="v">Votes</option>'); ?>

								<?php $tpl->getPersoSortOrdre('note', '<option%s value="e">Note</option>'); ?>

							</select>
							<select id="asc-desc" name="is">
								<option<?php $tpl->getPersoSortSens('ASC'); ?> value="a">croissant</option>
								<option<?php $tpl->getPersoSortSens('DESC'); ?> value="d">décroissant</option>
							</select>
						</div>
<?php endif; ?>
					</div>
				</form>
			</div>
<?php endif; ?>

			<div style="display:none" id="recherche">
				<form action="<?php $tpl->getSearchAction(); ?>" method="get">
					<div>
						<label for="search">
							<?php $tpl->getAdvSearch('<a title="Recherche avancée" href="%s">Recherche</a>', 'Recherche'); ?>&nbsp;:
							<input<?php $tpl->getSearchText(); ?> accesskey="4" name="search" id="search" class="text" type="text" />
						</label>
						<input type="submit" class="submit" value="OK" />
					</div>
				</form>
			</div>
		</div>

<?php if ($tpl->display('search_result')) : ?>
		<div id="search_result">
			<?php $tpl->getSearchResult(); ?>

		</div>
<?php endif; ?>

<?php include(dirname(__FILE__) . '/barre_nav_haut.php'); ?>
		<?php $tpl->getCatDescription('<div id="categorie_description"><p>%s</p></div>'); ?>

<?php if ($tpl->display('thumbs')) : ?>
		<div id="vignettes_alb">
			<ul id="vignettes">
				<?php while ($tpl->getThumbsNextLine()) : ?><?php while ($tpl->getThumbsNextThumb()) : ?><li class="v_thumb<?php $tpl->getThumb('recent', ' v_recent'); ?>"><span class="env1">
						<span class="env2">
							<a class="img_link<?php if ($tpl->display('thumbs_infos')) : ?> img_infos<?php endif; ?>" style="background:url(<?php $tpl->getThumb('image', '%2$s'); ?>) no-repeat center" href="<?php $tpl->getThumb('lien'); ?>">
								<?php $tpl->getThumb('image', '<img %s src="%4$s/pixtrans.png" alt="%3$s" title="%3$s" />'); ?>

							</a>
							<?php $tpl->getThumb('nom', '<a class="text_link" href="%s"><span class="vignette_nom">%s</span></a>'); ?>
							
<?php if ($tpl->display('thumbs_infos')) : ?>
							<span class="vignette_infos image_infos">
								<?php $tpl->getThumb('date', '<span class="image_date%s">%s</span>'); ?>

								<?php $tpl->getThumb('taille', '<span class="image_taille">%s</span>'); ?>

								<?php $tpl->getThumb('poids', '<span class="image_poids">%s</span>'); ?>

								<?php $tpl->getThumb('hits', '<span class="image_hits%s">%s</span>'); ?>

								<?php $tpl->getThumb('comments', '<span class="image_comments%s">%s</span>'); ?>

								<?php $tpl->getThumb('votes', '<span class="image_note%s"><span title="note moyenne: %s">%s</span><br />%s</span>'); ?>

							</span>
<?php endif; ?>
						</span>
					</span></li><?php endwhile; ?><?php endwhile; ?>

			</ul>
		</div>
<?php endif; ?>
<?php include(dirname(__FILE__) . '/barre_nav_bas.php'); ?>
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
