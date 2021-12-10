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
				<?php $tpl->getRapport('%s<br/>'); ?>
				<?php $tpl->getGeneralMaj('%s<br/>'); ?>

				<div id="modif_user">
					<p id="modif_user_retour"><a href="?section=utilisateurs&amp;page=membres">retour</a></p>
					<p id="modif_user_profil"><?php $tpl->getProfil('nom', 'Profil de %s'); ?></p>

					<fieldset>
						<legend>Avatar</legend>
						<p class="field" id="avatar">
							<?php $tpl->getProfil('avatar'); ?>

						</p>

						<form enctype="multipart/form-data" method="post" action="">
							<div>
								<?php $tpl->getVID(); ?>

								<input name="MAX_FILE_SIZE" value="81920" type="hidden" />
								<p class="field">
									<label for="new_avatar">Nouvel avatar (image JPEG avec extension .jpg, de 80 Ko et 200 pixels de coté maximum)&nbsp;:</label>
									<input class="text" id="new_avatar" name="new_avatar" size="50" maxlength="2048" type="file" />
									<input value="envoyer" class="submit" type="submit" />
								</p>
							</div>
						</form>

						<form id="modif_user_supp_avatar" method="post" action="">
							<div>
								<input type="hidden" name="supp_avatar" value="1" />
								<?php $tpl->getVID(); ?>

								<p class="field">
									<input onclick="if (confirm('Êtes-vous sûr de vouloir supprimer cet avatar ?')) { return true;} else { return false; }" type="submit" class="submit" value="supprimer l'avatar" />
								</p>
							</div>
						</form>
					</fieldset>

					<fieldset>
						<legend>Informations et options</legend>
						<form action="" method="post">
							<div id="modif_user_infos">
								<input type="hidden" name="modif_profil" value="1" />
								<?php $tpl->getVID(); ?>

								<p class="field">
									<label class="labeltext" for="new_mail">Courriel :</label>
									<input<?php $tpl->getProfil('courriel'); ?> id="new_mail" name="new_mail" type="text" class="text" />
								</p>
								<p class="field">
									<label class="labeltext" for="new_web">Site Web :</label>
									<input<?php $tpl->getProfil('web'); ?> id="new_web" name="new_web" type="text" class="text" />
								</p>
								<p class="field">
									<label class="labeltext" for="new_lieu">Localisation :</label>
									<input<?php $tpl->getProfil('lieu'); ?> id="new_lieu" name="new_lieu" type="text" class="text" />
								</p>
								<br />
								<p class="field">
									<input<?php $tpl->getProfil('courriel_visible'); ?> id="new_mail_public" name="new_mail_public" type="checkbox" />
									<label for="new_mail_public">Courriel visible pour tous les utilisateurs</label>
								</p>
<?php if ($tpl->getProfil('is_newsletter')) : ?>
								<p class="field">
									<input<?php $tpl->getProfil('newsletter'); ?> id="new_newsletter" name="new_newsletter" type="checkbox" />
									<label for="new_newsletter">Abonnement à la newsletter</label>
								</p>
<?php endif; ?>
								<input type="submit" class="submit" value="enregistrer" />
							</div>
						</form>
					</fieldset>

					<fieldset>
						<legend>Statistiques</legend>
							<p class="field">
								Date d'inscription&nbsp;: <?php $tpl->getProfil('date_inscription'); ?>
							</p>
							<p class="field">
								IP d'inscription&nbsp;: <?php $tpl->getProfil('ip_inscription'); ?>
							</p>
							<p class="field">
								Dernière visite&nbsp;: <?php $tpl->getProfil('date_derniere_visite'); ?>
							</p>
							<p class="field">
								IP de dernière visite&nbsp;: <?php $tpl->getProfil('ip_derniere_visite'); ?>
							</p>
							<p class="field">
								Nombre de commentaires&nbsp;: <?php $tpl->getProfil('nb_commentaires'); ?>
							</p>
							<p class="field">
								Nombre d'images envoyées&nbsp;: <?php $tpl->getProfil('nb_images'); ?>
							</p>
							<p class="field">
								Nombre de favoris&nbsp;: <?php $tpl->getProfil('nb_favoris'); ?>
							</p>
					</fieldset>	
				</div>
