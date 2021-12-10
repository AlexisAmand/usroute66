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
<?php if (isset($_GET['search'])) : ?>
			<div id="lien_retour"><a href="<?php $tpl->getGalerieAccueil(); ?>">accueil</a></div>
<?php endif; ?>
			<?php $tpl->getPosition(1, '<div id="position">%s%s %3$s'); ?>
			<?php $tpl->getStat('images', '<span id="stats_images">(%2$s image%3$s)</span>', '', 1); ?>
			<?php $tpl->getPosition(1, '</div>'); ?>

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



<?php include(dirname(__FILE__) . '/barre_nav_haut.php'); ?>

				<div id="dismsgcom_comments">
<?php while ($tpl->getNextPageComment()) : ?>
					<div class="dismsgcom">
						<div class="dismsgcom_top">
							<div title="oumaporn12r" class="dismsgcom_thumb" onmouseover="this.style.cursor='pointer'" onclick="window.location='<?php $tpl->getPageComment('image_lien'); ?>'">
								<table><tr><td><?php $tpl->getPageComment('image_thumb', 0, 80); ?></td></tr></table>
							</div>
							<div class="dismsgcom_infos">
								<p class="dismsgcom_infos_album"><span>Album:</span> <a href="<?php $tpl->getPageComment('album_lien'); ?>"><?php $tpl->getPageComment('album_nom'); ?></a></p>
								<p class="dismsgcom_infos_date">Le <?php $tpl->getPageComment('commentaire_date'); ?>,</p>
								<p class="dismsgcom_infos_auteur"><?php $tpl->getPageComment('commentaire_auteur'); ?><?php $tpl->getPageComment('commentaire_web', ' <span class="dismsgcom_infos_web">(%s)</span>'); ?> <span>a écrit :</span></p>
							</div>
						</div>
						<div class="dismsgcom_bottom">
							<div class="dismsgcom_message">
								<p><?php $tpl->getPageComment('commentaire_message'); ?></p>
							</div>
						</div>
					</div>
<?php endwhile; ?>
				</div>

<?php include(dirname(__FILE__) . '/barre_nav_bas.php'); ?>
<?php if ($tpl->display('rss_objet')) : ?>
					<ul id="rss_objet">
						<li><?php $tpl->getRSSImages('<a %s href="%s">fil des images</a>'); ?></li>
						<?php $tpl->getRSSCommentaires('<li><a %s href="%s">fil des commentaires</a></li>'); ?>

					</ul>
<?php endif; ?>
	</div>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
