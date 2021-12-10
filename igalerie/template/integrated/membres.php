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
<?php if ($tpl->display('liste')) : ?>
			<?php $tpl->getMembresList('pos', '<div id="position"><span id="hvc_result">%s</span></div>'); ?>
<?php else : ?>
			<p id="position"><a href="<?php $tpl->getGalerieAccueil(); ?>">Accueil</a></p>
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

<?php if ($tpl->display('liste')) : ?>
<?php if ($tpl->display('barre_nav')) : ?>
			<div class="barre_nav" id="barre_nav_haut">
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
			<div id="membres_liste"><?php $tpl->getMembresList('list'); ?></div>
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
<?php endif; ?>

<?php if ($tpl->display('oubli')) : ?>
			<?php $tpl->getMembresMsg(); ?>

<?php if ($tpl->display('oubli_form')) : ?>
			<div id="membre_oubli">
				<h3>Mot de passe oublié</h3>
				<form method="post" action="">
					<div id="membre_oubli_form">
						<p>Pour générer aléatoirement un nouveau mot de passe, veuillez entrer les informations suivantes.</p>
						<br />
						<label for="oubli_user">Nom d'utilisateur&nbsp;:</label>
						<input id="oubli_user" name="oubli_user" type="text" class="text" maxlength="512" size="50" />
						<label for="oubli_mail">Courriel&nbsp;:</label>
						<input id="oubli_mail" name="oubli_mail" type="text" class="text" maxlength="512" size="50" />
						<div>
							<input type="submit" class="submit" value="valider" />
						</div>
					</div>
				</form>
			</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($tpl->display('inscription')) : ?>
			<?php $tpl->getMembresMsg(); ?>

<?php if ($tpl->display('inscription_form')) : ?>
			<div id="membres_inscription">
				<h3>Enregistrement</h3>
				<form action="" method="post">
					<div>
						<input type="hidden" class="hidden" name="molpac" value="<?php $tpl->getMolpac(); ?>" />
						<label for="new_login">Nom d'utilisateur&nbsp;:</label>
						<input<?php if (isset($_POST['new_login'])) { echo ' value="' . htmlentities($_POST['new_login']) . '"'; } ?> id="new_login" name="new_login" class="text" type="text" maxlength="255" />
						<label for="new_pass">Mot de passe&nbsp;:</label>
						<input<?php if (isset($_POST['new_pass'])) { echo ' value="' . htmlentities($_POST['new_pass']) . '"'; } ?> id="new_pass" name="new_pass" class="text" type="password" maxlength="255" />
						<label for="new_pass_confirm">Confirmation du mot de passe&nbsp;:</label>
						<input<?php if (isset($_POST['new_pass_confirm'])) { echo ' value="' . htmlentities($_POST['new_pass_confirm']) . '"'; } ?> id="new_pass_confirm" name="new_pass_confirm" class="text" type="password" maxlength="255" />
						<label for="new_mail">Courriel (facultatif)&nbsp;:</label>
						<input<?php if (isset($_POST['new_mail'])) { echo ' value="' . htmlentities($_POST['new_mail']) . '"'; } ?> id="new_mail" name="new_mail" class="text" type="text" maxlength="255" />
						<label for="new_web">Site Web (facultatif)&nbsp;:</label>
						<input<?php if (isset($_POST['new_web'])) { echo ' value="' . htmlentities($_POST['new_web']) . '"'; } ?> id="new_web" name="new_web" class="text" type="text" maxlength="255" />
						<input type="submit" class="submit" value="enregistrer" />
					</div>
				</form>
			</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($tpl->display('membres_login')) : ?>
			<?php $tpl->getMembreIdent('<p id="msg_erreur">%s</p>'); ?>

			<div id="membres_connexion">
				<h3>Connexion</h3>
				<form action="" method="post">
					<div>
						<label for="ident_login">Nom d'utilisateur&nbsp;:</label>
						<input class="text" maxlength="255" id="ident_login" name="ident_login" type="text" />
					</div>
					<div>
						<label for="ident_pass">Mot de passe&nbsp;:</label>
						<input class="text" maxlength="255" id="ident_pass" name="ident_pass" type="password" />								
					</div>
					<div>
						<input checked="checked" id="ident_souvenir" name="ident_souvenir" type="checkbox" />
						<label for="ident_souvenir">Se souvenir de moi ?</label>
					</div>
					<div>
						<input class="submit" type="submit" value="OK" />
					</div>
				</form>
				<div id="membres_connexion_oubli">
					<a rel="nofollow" href="<?php $tpl->getLink('oubli'); ?>">Mot de passe oublié ?</a>
				</div>
			</div>
<?php endif; ?>

<?php if ($tpl->display('profil')) : ?>
			<div id="membre_profil">
				<table>
					<tr><td id="membre_nom" colspan="2"><h3><?php $tpl->getProfil('nom'); ?><?php $tpl->getProfil('groupe', ' <span>(%s)</span>'); ?></h3></td></tr>
					<tr>
						<?php $tpl->getProfil('avatar', '<td id="membre_avatar"><img alt="%s" src="%s" /></td>'); ?>

						<td id="membre_infos">
							<?php $tpl->getProfil('date_creation', '<p><span>Date d\'inscription&nbsp;:</span> %s</p><br />'); ?>

							<?php $tpl->getProfil('lieu', '<p><span>Localisation&nbsp;:</span> %s</p>'); ?>

							<?php $tpl->getProfil('mail', '<p><span>Courriel&nbsp;:</span> <a href="mailto:%1$s">%1$s</a></p>'); ?>

							<?php $tpl->getProfil('web', '<p><span>Site Web&nbsp;:</span> <a href="%1$s">%1$s</a></p>'); ?>

							<?php $tpl->getProfil('objets'); ?>

						</td>
					</tr>
				</table>
			</div>
<?php if ($tpl->display('rss_objet')) : ?>
			<ul id="rss_objet">
				<li><?php $tpl->getRSSImages('<a %s href="%s">fil des images</a>'); ?></li>
				<?php $tpl->getRSSCommentaires('<li><a %s href="%s">fil des commentaires</a></li>'); ?>

			</ul>
<?php endif; ?>
<?php endif; ?>


<?php if ($tpl->display('modif_pass')) : ?>
		
		<?php $tpl->getMembresMsg(); ?>
		<div id="membre_profil">
			<table>
				<tr><td id="membre_nom" colspan="2"><h3><?php $tpl->getProfil('nom'); ?><?php $tpl->getProfil('groupe', ' <span>(%s)</span>'); ?></h3></td></tr>
				<tr>
					<td id="membre_infos" colspan="2">
						<form method="post" action="">
							<div id="membre_infos_text">
								<?php $tpl->getMembre('sid'); ?>

								<input type="hidden" name="modif_profil" value="1" />
								<label for="new_pass">Nouveau mot de passe&nbsp;:</label>
								<input id="new_pass" name="new_pass" class="text" type="password" />
								<label for="new_pass_confirm">Confirmation du mot de passe&nbsp;:</label>
								<input id="new_pass_confirm" name="new_pass_confirm" class="text" type="password" />
							</div>
							<div>
								<input type="submit" class="submit" value="enregistrer" />
							</div>
						</form>
						<div id="membres_retour"><a href="<?php $tpl->getLink('modif_profil'); ?>">retour</a></div>
					</td>
				</tr>
			</table>
		</div>
<?php endif; ?>

<?php if ($tpl->display('modif_avatar')) : ?>
		
		<?php $tpl->getMembresMsg(); ?>
		<div id="membre_profil">
			<table>
				<tr><td id="membre_nom" colspan="2"><h3><?php $tpl->getProfil('nom'); ?><?php $tpl->getProfil('groupe', ' <span>(%s)</span>'); ?></h3></td></tr>
				<tr>
					<td id="membre_avatar_upload" colspan="2">
						<form enctype="multipart/form-data" method="post" action="">
							<div>
								<?php $tpl->getMembre('sid'); ?>

								<input name="MAX_FILE_SIZE" value="81920" type="hidden" />
								<label for="new_avatar">Nouvel avatar (image JPEG avec extension .jpg,<br />de 80 Ko et 200 pixels de coté maximum)&nbsp;:</label>
								<input class="text" id="new_avatar" name="new_avatar" size="50" maxlength="2048" type="file" />&nbsp;&nbsp;
								<input value="envoyer" class="submit" type="submit" />
							</div>
						</form>
						<form id="membre_supp_avatar" method="post" action="">
							<div>
								<?php $tpl->getMembre('sid'); ?>

								<input type="hidden" name="modif_profil" value="1" />
								<input type="hidden" name="supp_avatar" value="1" />
							</div>
							<div>
								<input onclick="if (confirm('Êtes-vous sûr de vouloir supprimer votre avatar ?')) { return true;} else { return false; }" type="submit" class="submit" value="supprimer votre avatar" />
							</div>
						</form>
						<div id="membres_retour"><a href="<?php $tpl->getLink('modif_profil'); ?>">retour</a></div>
					</td>
				</tr>
			</table>
		</div>
<?php endif; ?>

<?php if ($tpl->display('modif_profil')) : ?>
		
		<?php $tpl->getMembresMsg(); ?>
		<div id="membre_profil">
			<table>
				<tr><td id="membre_nom" colspan="2"><h3><?php $tpl->getProfil('nom'); ?><?php $tpl->getProfil('groupe', ' <span>(%s)</span>'); ?></h3></td></tr>
				<tr>
					<td id="membre_avatar">
						<?php $tpl->getProfil('avatar', '<img alt="%s" src="%s" />'); ?>
<?php if ($tpl->display('membres_noadmin')) : ?>
						<p id="membre_lien_mpd"><a href="<?php $tpl->getProfil('lien_pass'); ?>">Changer de mot de passe</a></p>
<?php else : ?>
						<br /><br />
<?php endif; ?>
<?php if ($tpl->display('membres_avatar')) : ?>
						<p><a href="<?php $tpl->getProfil('lien_avatar'); ?>">Changer d'avatar</a></p>
<?php endif; ?>	
					</td>
					<td id="membre_infos">
						<form method="post" action="">
							<div>
							<?php $tpl->getMembre('sid'); ?>

							<input type="hidden" name="modif_profil" value="1" />
							<div id="membre_infos_text">
<?php if ($tpl->display('membres_noadmin')) : ?>
								<label for="new_mail">Courriel&nbsp;:</label>
								<input id="new_mail" name="new_mail" class="text" type="text"<?php $tpl->getProfil('mail_modif', ' value="%s"'); ?> />
<?php endif; ?>	
								<label for="new_web">Site Web&nbsp;:</label>
								<input id="new_web" name="new_web" class="text" type="text"<?php $tpl->getProfil('web', ' value="%s"'); ?> />
								<label for="new_lieu">Localisation&nbsp;:</label>
								<input id="new_lieu" name="new_lieu" class="text" type="text"<?php $tpl->getProfil('lieu', ' value="%s"'); ?> />
							</div>
							<br />
							<div>
								<p>Options&nbsp;:</p>
								<input<?php $tpl->getProfil('mail_public', ' checked="checked"'); ?> id="new_mail_public" name="new_mail_public" type="checkbox" />
								<label for="new_mail_public">Afficher votre courriel pour tous</label>
<?php if ($tpl->display('modif_profil_newsletter')) : ?>
								<br />
								<input<?php $tpl->getProfil('newsletter', ' checked="checked"'); ?> id="new_newsletter" name="new_newsletter" type="checkbox" />
								<label for="new_newsletter">S'abonner à la newsletter</label>
<?php endif; ?>
							</div>
							<input type="submit" class="submit" value="enregistrer" />
							</div>
						</form>

					</td>
				</tr>
			</table>
		</div>
<?php endif; ?>

<?php if ($tpl->display('create')) : ?>
		<?php $tpl->getMembresMsg(); ?>
		<div id="upload_membres">
			<div id="upload_membres_titre"><h3>Création d'un nouvel album</h3></div>
			<form action="" method="post">
				<div>
					<?php $tpl->getMembre('sid'); ?>
					<p>Choisissez la catégorie dans laquelle vous souhaitez créer un album ou une catégorie&nbsp;:</p>
					<?php $tpl->getUpload('list'); ?>

					<p id="upload_obj_type">
						Créer&nbsp;:
						&nbsp;
						<label for="new_alb">
							<input checked="checked" name="obj_type" value="alb" id="new_alb" type="radio" /> Album
						</label>
						&nbsp;
						<label for="new_cat">
							<input name="obj_type" value="cat" id="new_cat" type="radio" /> Catégorie
						</label>
					</p>
					<p id="upload_obj_name">
						<label for="new_name">Nom&nbsp;:</label>
						<input id="new_name" name="obj_name" type="text" class="text" maxlength="255" />
					</p>
					<p id="upload_obj_desc">
						<label for="obj_desc"><a class="lien_js" href="javascript:display_upload('obj_desc')">Description</a></label>
						<textarea cols="45" rows="3" id="obj_desc" name="obj_desc"></textarea>
					</p>
					<input value="valider" class="submit" type="submit" />
				</div>
				<script type="text/javascript">
				//<![CDATA[
					document.getElementById('obj_desc').style.display = 'none';
				//]]>
				</script>
				<div id="membres_retour"><a href="<?php $tpl->getLink('upload'); ?>">retour</a></div>
			</form>
		</div>
<?php endif; ?>

<?php if ($tpl->display('upload')) : ?>
		
		<?php $tpl->getMembresMsg(); ?>
		<div id="upload_membres">
			<div id="upload_membres_titre"><h3>Envoi d'images</h3></div>
			<form enctype="multipart/form-data" action="" method="post">
				<div>
					<?php $tpl->getMembre('sid'); ?>
					<p>Choisissez l'album dans lequel vous souhaitez envoyer des images<?php $tpl->getUpload('create', ' (ou <a href="%s">créez un nouvel album</a>)'); ?>&nbsp;:</p>
					<?php $tpl->getUpload('list'); ?>
					<p>Images à envoyer (<?php $tpl->getUpload('format'); ?>)&nbsp;:</p>
					<div id="upload_images">
						<?php $tpl->getUpload('MAX_FILE_SIZE'); ?>

<?php for ($i = 1; $i <= 2; $i++) : ?>
						<fieldset>
							<legend>Image <?php echo $i; ?></legend>
							<input class="text" name="upload_files[<?php echo $i; ?>]" size="40" maxlength="2048" type="file" />
							<label for="image_nom_<?php echo $i; ?>"><a class="lien_js" href="javascript:display_upload('image_nom_<?php echo $i; ?>')">Nom de l'image</a></label>
							<input size="50" maxlength="512" id="image_nom_<?php echo $i; ?>" name="upload_images[<?php echo $i; ?>][nom]" type="text" class="text image_nom" />
							<label for="image_desc_<?php echo $i; ?>"><a class="lien_js" href="javascript:display_upload('image_desc_<?php echo $i; ?>')">Description</a></label>
							<textarea cols="45" rows="3" id="image_desc_<?php echo $i; ?>" name="upload_images[<?php echo $i; ?>][desc]"></textarea>
						</fieldset>
<?php endfor; ?>
					</div>
					<script type="text/javascript">
					//<![CDATA[
						for (var i = 1; i <= 2; i++) {
							document.getElementById('image_nom_' + i).style.display = 'none';
							document.getElementById('image_desc_' + i).style.display = 'none';
						}
					//]]>
					</script>
					<?php $tpl->getUpload('mod', '<p id="upload_images_mod">Vos images n\'apparaîtront dans la galerie qu\'après validation par l\'admin.</p>'); ?>
					<input value="envoyer" class="submit" type="submit" />
				</div>
			</form>
		</div>
<?php endif; ?>

	</div>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
