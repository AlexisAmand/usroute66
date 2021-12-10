<?php if ($tpl->display('membres')) : ?>
					<div id="membres">
						<?php $tpl->getSectionTitre('<div id="red_membres" class="partjs pan_titre %s"><div><a id="ldp_membres" href="javascript:void(0);" title="%s">Membres</a></div></div>', 'membres'); ?>
						<div id="partie_membres">
							<?php $tpl->getMembreIdent('<p id="msg_erreur">%s</p>'); ?>
<?php if ($tpl->display('membres_connexion')) : ?>
							<div id="membres_connexion">
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
								<a rel="nofollow" href="<?php $tpl->getLink('oubli'); ?>">Mot de passe oublié ?</a>
							</div>
							<ul id="membre_liens">
								<li><a rel="nofollow" href="<?php $tpl->getMembre('lien_liste'); ?>">Liste des membres</a></li>
								<li><a rel="nofollow" href="<?php $tpl->getLink('inscription'); ?>">Devenir membre</a></li>
							</ul>
<?php else : ?>
							<div id="membres_connecte">
								<?php $tpl->getMembre('nom', '<div>Connecté en tant que<span id="membre_login"><a rel="nofollow" title="Voir votre profil" href="%s">%s</a></span></div>'); ?>
								<?php $tpl->getMembre('avatar', '<a rel="nofollow" title="Voir votre profil" href="%s"><img alt="%s" src="%s" width="50" height="50" /></a>'); ?>

							</div>
							<ul id="membre_liens">
								<?php $tpl->getMembre('lien_upload', '<li><a rel="nofollow" href="%s">Envoyer des images</a></li>'); ?>
								<li><a rel="nofollow" href="<?php $tpl->getMembre('lien_liste'); ?>">Liste des membres</a></li>
								<li><a rel="nofollow" href="<?php $tpl->getMembre('lien_modif_profil'); ?>">Modifier votre profil</a></li>
								<li><a rel="nofollow" href="<?php $tpl->getMembre('lien_deconnect'); ?>">Déconnexion</a></li>
							</ul>
<?php endif; ?>
						</div>

					</div>
<?php endif; ?>