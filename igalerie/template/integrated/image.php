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

		<?php $tpl->getPositionSpecial(); ?>
		<div id="barre_position">
			<?php $tpl->getMembre('favori', '<a id="addfav" href="%s">%s</a>'); ?>
			<?php $tpl->getPosition(); ?>

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
<?php if ($tpl->display('image_taille')) : ?>
			<div style="display:none" id="options">
				<form action="<?php $tpl->getFormAction(); ?>" onsubmit="return perso_verif(this)" method="get">
					<div class="valider_image" id="valider"><input class="submit" type="submit" value="Valider"/></div>
					<div>
						<?php $tpl->getUrlParameters('input'); ?>

						<input type="hidden" name="u" value="1" />
						<div id="image_taille">
							<div id="img_original">
								<label for="origine">
									<input value="1" name="it" type="radio" id="origine"<?php $tpl->getImageTaille('original', ' checked="checked"'); ?> />
									Taille originale
								</label>
							</div>
							&nbsp;
							<div id="img_fixed">
								<label for="taille">
									<input value="2" name="it" type="radio" id="taille"<?php $tpl->getImageTaille('fixed', ' checked="checked"'); ?> />
									Taille maximale&nbsp;:
								</label>
								<div id="fixed_hl">
									<label for="img_largeur">
										L.&nbsp;:&nbsp;<input<?php $tpl->getImageTaille('width', ' value="%s"'); ?> maxlength="6" name="il" id="img_largeur" class="text" type="text" />
									</label>
									<label for="img_hauteur">
										&nbsp;H.&nbsp;:&nbsp;<input<?php $tpl->getImageTaille('height', ' value="%s"'); ?> maxlength="6" name="ih" id="img_hauteur" class="text" type="text" />
									</label>
								</div>
							</div>
							&nbsp;
							<div id="img_auto">
								<label for="ajust">
									<input value="3" name="it" type="radio" id="ajust"<?php $tpl->getImageTaille('auto', ' checked="checked"'); ?> />
									Ajustement auto.
								</label>
							</div>
						</div>
					</div>
				</form>
			</div>
<?php endif; ?>
		</div>

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
				<script type="text/javascript">
				//<![CDATA[
					var img_espace = document.getElementById('image_r').offsetWidth;
				//]]>
				</script>
				<?php $tpl->getImageResizeMsg('<span id="image_r_msg"%s>%s</span>'); ?>

				<?php $tpl->getImage('<div id="image_cadre"><a href="%2$s"><img id="img" %s src="%s" alt="%s" /></a></div>'); ?>

				<script type="text/javascript">
				//<![CDATA[
					if (typeof img_auto_resize == 'number' && img_auto_resize == 1) {
						var img = document.getElementById('image_r').getElementsByTagName('img')[0];
						if (img.offsetWidth > img_espace || img_width > img_espace) {
							document.getElementById('image_r_msg').style.display = '';
							img.width = img_espace;
						}
					}
				//]]>
				</script>
			</div>
		</div>

		<?php $tpl->getImageTags('<div id="image_tags"><div>%s</div></div>'); ?>

		<?php $tpl->getImageDescription('<div id="image_description"><p>%s</p></div>'); ?>

		<div class="p_repli">
			<div id="stats">
				<?php $tpl->getSectionTitre('<div id="red_stats" class="partjs pan_titre %s"><a class="lien_js" id="ldp_stats" href="javascript:void(0);" title="%s">Statistiques</a></div>', 'stats'); ?>

				<div id="partie_stats">
					<ul>
						<li id="stats_fichier"><span class="stats_titre">fichier</span> : <?php $tpl->getImgFile('%s', 0); ?></li>
						<li id="stats_poids"><?php $tpl->getImageStat('poids', '<span class="stats_titre">poids</span> : %s'); ?></li>
						<li id="stats_taille"><?php $tpl->getImageStat('taille', '<span class="stats_titre">dimensions</span> : %s pixels'); ?></li>
						<li id="stats_vues"><?php $tpl->getImageStat('hits', '<span class="stats_titre">visitée</span> : %s fois'); ?></li>
						<?php $tpl->getImageStat('comments', '<li id="stats_comments"><span class="stats_titre">commentaires</span> : %s</li>'); ?>

						<?php $tpl->getImageStat('note_star', '<li id="stats_note"><span class="stats_titre">note moyenne</span> : <span id="note_stars">%s</span>'); ?>
						<?php $tpl->getImageStat('note', '<span>(%s - '); ?>
						<?php $tpl->getImageStat('votes', '%s)</span></li>'); ?>

						<li id="stats_date_creation"><?php $tpl->getImageStat('date_creation', '<span class="stats_titre">créée le</span> : %s'); ?></li>
						<li id="stats_date"><?php $tpl->getImageStat('date', '<span class="stats_titre">ajoutée le</span> : %s'); ?></li>
						<?php $tpl->getImageStat('auteur', '<li id="stats_auteur"><span class="stats_titre">ajouté par</span> : %s</li>'); ?>

					</ul>
				</div>
			</div>
		</div>

<?php if ($tpl->display('exif')) : ?>
		<div class="p_repli">
			<div class="imeta">
				<?php $tpl->getSectionTitre('<div id="red_exif" class="partjs pan_titre %s"><a class="lien_js" id="ldp_exif" href="javascript:void(0);" title="%s">Informations Exif</a></div>', 'exif'); ?>

				<div class="partie_imeta" id="partie_exif">
					<?php $tpl->getMetadata('exif', '<p><span>%s</span> : %s</p>', '<p>Aucune information disponible.</p>'); ?>
				</div>
			</div>
		</div>
<?php endif; ?>

<?php if ($tpl->display('iptc')) : ?>
		<div class="p_repli">
			<div class="imeta">
				<?php $tpl->getSectionTitre('<div id="red_iptc" class="partjs pan_titre %s"><a class="lien_js" id="ldp_iptc" href="javascript:void(0);" title="%s">Informations IPTC</a></div>', 'iptc'); ?>

				<div class="partie_imeta" id="partie_iptc">
					<?php $tpl->getMetadata('iptc', '<p><span>%s</span> : %s</p>', '<p>Aucune information disponible.</p>'); ?>
				</div>
			</div>
		</div>
<?php endif; ?>

<?php if ($tpl->display('add_votes')) : ?>
		<?php $tpl->getUserNote('<div id="image_note">%sVotre note : <span id="note_user">%s</span></div>'); ?>
<?php endif; ?>

<?php if ($tpl->display('comments')) : ?>
		<div id="commentaires">
			<div id="commentaires_bloc">
				<?php $tpl->getSectionTitre('<p id="red_comments" class="partjs comment_titre %s"><a class="lien_js" id="ldp_comments" href="javascript:void(0);" title="%s">Commentaires</a></p>', 'comments', '#commentaires'); ?>

				<div id="partie_comments">
<?php while ($tpl->getNextComment()) : ?>
					<div <?php $tpl->getComment('id', 'id="co%s"'); ?> class="comment<?php $tpl->getComment('co_avatar', ' comment_avatar'); ?><?php $tpl->getComment('pair', ' co_pair'); ?><?php $tpl->getComment('preview', ' co_preview'); ?>">
						<?php $tpl->getComment('avatar', '<img alt="%s" width="50" height="50" src="%s" />'); ?>
						<p class="comment_num"><a href="<?php $tpl->getComment('id', '#co%s'); ?>"><?php $tpl->getComment('num'); ?></a></p>
						<p class="comment_date">le <?php $tpl->getComment('date'); ?></p>
						<p class="comment_auteur"><span class="comment_aut"><?php $tpl->getComment('auteur'); ?></span><?php $tpl->getComment('site', ' (<a class="co_web_link" title="Site Web de %s" href="%s">site</a>)'); ?> a écrit :</p>
						<p class="comment_message">
							<?php $tpl->getComment('msg'); ?>

						</p>
					</div>
<?php endwhile; ?>

					<?php $tpl->getCommentsNull('<p id="comment_null">Aucun commentaire.</p>'); ?>

					<?php $tpl->getCommentPreview('<p id="comment_preview"><span>Aperçu de votre commentaire.</span></p>'); ?>
				</div>
			</div>

			<?php $tpl->getCommentRejet('<div id="msg_erreur"><p>Votre commentaire a été rejeté pour la raison suivante :<br/>%s</p></div>'); ?>

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
							Courriel<?php $tpl->getNewComment('fac_mail', ' (facultatif)', ' (obligatoire)'); ?> (ne sera pas affiché)&nbsp;:<br/>
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
<?php include(dirname(__FILE__) . '/footer.php'); ?>
