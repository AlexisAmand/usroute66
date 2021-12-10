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
											<input<?php $tpl->getSearchText(); ?> accesskey="4" name="search" id="search" type="text" />
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
							<form action="<?php $tpl->getFormAction(); ?>" method="get">
								<div>
									<?php $tpl->getUrlParameters('input'); ?>
									<?php $tpl->getInfo('startnum', '<input type="hidden" name="startnum" value="%s" />'); ?>

									<input type="hidden" name="u" value="1" />
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
					<?php $tpl->getEnlarge('<a href="javascript:void(0);" id="enlarge" title="%s"><img src="%s" alt="%s" %s /></a>'); ?>

					<?php $tpl->getInfo('h1', '<div><h1>%s</h1></div>'); ?>

					<?php $tpl->getPosition(); ?>

<?php if ($tpl->display('barre_nav_com')) : ?>
					<div class="barre_nav" id="barre_nav_haut">
						<div class="barre_nav_gauche"></div>
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
					<?php $tpl->getCatDescription('<div id="categorie_description"><p>%s</p></div>'); ?>
<?php endif; ?>

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

<?php if ($tpl->display('barre_nav_com')) : ?>
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
				</div>
			</td>
		</tr>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
