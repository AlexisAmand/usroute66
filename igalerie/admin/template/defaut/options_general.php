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
				<form action="index.php?section=options&amp;page=general" method="post">
					<div>
						<input type="hidden" name="u" value="1" />
						<?php $tpl->getRapport('%s<br/>'); ?>
						<?php $tpl->getGeneralMaj('%s<br/>'); ?>

						<?php $tpl->getVID(); ?>

						<div id="param_general">
							<fieldset>
								<legend>Apparence</legend>
								<div class="fielditems">
									<p class="field">
										Template :
										<select name="g_theme" onchange="change_theme(this);">
											<?php $tpl->getGalerieThemes(); ?>

										</select>
									</p>
									<p class="field">
										Style :
										<select name="g_style" class="space_select" id="choix_style">
											<?php $tpl->getGalerieStyles(); ?>

										</select>
									</p>
									<p class="field">
										<label for="g_add_style"><a class="lien_js" href="javascript:document.getElementById('g_add_style').style.display=(document.getElementById('g_add_style').style.display=='')?document.getElementById('g_add_style').style.display='none':document.getElementById('g_add_style').style.display='';document.getElementById('g_add_style').focus();void(0);">Style additionnel</a></label>
										<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_add_style');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
										<span id="aide_add_style" class="aide_contextuelle" style="display:none">
											<span class="aide_barre">
												<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_add_style');">fermer</a></span>
												<span class="aide_aide">AIDE :</span> <span class="aide_titre">style additionnel</span>
											</span>
											<span class="aide_texte">Le style additionnel est une feuille de style CSS qui s'ajoute à celle du style choisi. Ainsi, pour des petites modifications il peut être plus pratique d'utiliser ce moyen que de modifier la CSS du style en cours.</span>
										</span>
										<textarea id="g_add_style" name="g_add_style" cols="60" rows="20"><?php $tpl->getGalerieAddStyle(); ?></textarea>
										<script type="text/javascript">
										//<![CDATA[
											document.getElementById('g_add_style').style.display = 'none';
										//]]>
										</script>
									</p>
<?php $tpl->getGalerieJSStyles(); ?>

								</div>
							</fieldset>
							<br/>
							<fieldset>
								<legend>Date</legend>
								<div class="fielditems">
									<p class="field<?php $tpl->getTextDisabled('date_format_thumbs', ' disabled'); ?>">
										Format de la date pour les vignettes&nbsp;:
										<?php $tpl->getGeneralFormatDate('tb'); ?>

									</p>
									<p class="field<?php $tpl->getTextDisabled('date_format_images', ' disabled'); ?>">
										Format de la date pour les images et les commentaires&nbsp;:
										<?php $tpl->getGeneralFormatDate('im'); ?>

									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset<?php $tpl->getTextDisabled('liens'); ?>>
								<legend>Liens <a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_liens');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a></legend>
								<div class="fielditems">
									<div id="aide_liens" class="aide_contextuelle" style="display:none">
										<div class="aide_barre">
											<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_liens');">fermer</a></span>
											<span class="aide_aide">AIDE :</span> <span class="aide_titre">liens</span>
										</div>
										<span class="aide_texte">Pour ajouter un nouveau lien, il faut l'écrire dans le format "titre:lien", soit l'intitulé du lien et le lien séparés par les deux points, chaque lien devant occuper une ligne.<br /> Ainsi, pour obtenir un lien sur le texte "iGalerie", il suffit d'écrire "iGalerie:http://www.igalerie.org/".</span>
									</div>
									<p class="field">
										<input<?php $tpl->getInputDisabled('liens'); ?> id="g_liens_active" name="g_liens_active" type="checkbox"<?php $tpl->getActiveLiens(); ?> />
										<label for="g_liens_active"> Activer l'affichage de liens</label>
									</p>
									<p class="field">
										<label for="g_liens">Liens&nbsp;:</label>
										<textarea<?php $tpl->getInputDisabled('liens'); ?> id="g_liens" name="g_liens" cols="60" rows="5"><?php $tpl->getLiens('message_accueil'); ?></textarea>
									</p>
								</div>
							</fieldset>
							<br/>
							<fieldset>
								<legend>Visites admin <a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_admin_visites');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a></legend>
								<div id="aide_admin_visites" class="aide_contextuelle" style="display:none">
									<div class="aide_barre">
										<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_admin_visites');">fermer</a></span>
										<span class="aide_aide">AIDE :</span> <span class="aide_titre">visites admin</span>
									</div>
									<span class="aide_texte">
										En activant cette option, vos visites des images de la galerie ne seront pas pris en compte dans le nombre de visites de chaque image. Pour cela, vous disposez de deux méthodes : cookie ou IP.<br />
										La méthode par cookie va créer une chaine de caractère unique dans le cookie 'galerie_perso', permettant au script de vous identifier.<br />
										La méthode par adresse IP va permettre de vous identifier selon votre adresse IP si vous disposez d'une IP fixe. Notez que vous pouvez également entrer plusieurs IP, en les séparant par une virgule.
									</span>
								</div>
								<div class="fielditems">
									<p class="field">
										<input id="g_admin_hits" name="g_admin_hits" type="checkbox"<?php $tpl->getNoHits(); ?> />
										<label for="g_admin_hits"> Ne pas compter les visites admin&nbsp;:</label>
									</p>
									<div class="field_second">
										<br />
										<input id="g_admin_hits_cookie" name="g_admin_hits_mode" value="cookie" type="radio"<?php $tpl->getNoHitsMode('cookie'); ?> /> <label for="g_admin_hits_cookie"> par cookie</label>
										<br />
										<input id="g_admin_hits_ip" name="g_admin_hits_mode" value="ip" type="radio"<?php $tpl->getNoHitsMode('ip'); ?> /> <label for="g_admin_hits_ip"> par adresse(s) IP&nbsp;:</label>
										<input size="40" value="<?php $tpl->getNoHitsIP(); ?>" maxlength="255" type="text" class="text text_space" name="g_admin_hits_ip_addr" />
										(IP actuelle&nbsp;: <?php echo $_SERVER['REMOTE_ADDR'] ?>)
									</div>
								</div>
							</fieldset>
							<br/>
							<fieldset>
								<legend>URL</legend>
								<div class="fielditems">
									<p class="field">
										<label for="g_url_galerie">URL de la galerie&nbsp;:</label><a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_url_galerie');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
										<br />
										<?php $tpl->getGalerieHOST(); ?><input id="g_url_galerie" size="60" value="<?php $tpl->getGalerieURL(); ?>" maxlength="255" type="text" class="text text_space" name="g_url_galerie" />
									</p>
									<div id="aide_url_galerie" class="aide_contextuelle" style="display:none">
										<div class="aide_barre">
											<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_url_galerie');">fermer</a></span>
											<span class="aide_aide">AIDE :</span> <span class="aide_titre">URL de la galerie</span>
										</div>
										<div class="aide_texte">
											<strong>Attention !</strong> Si vous saisissez un mauvais URL de votre galerie, vous risquez de ne plus pouvoir vous connecter à l'admin. Un URL exact est également nécessaire au bon fonctionnement de la galerie.
										</div>
									</div>
									<p class="field">
										<input id="g_integrated" name="g_integrated" type="checkbox"<?php $tpl->getIntegrated(); ?> />
										<label for="g_integrated"> La galerie est intégrée au site</label>
										<a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_integrated');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
									</p>
									<div id="aide_integrated" class="aide_contextuelle" style="display:none">
										<div class="aide_barre">
											<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_integrated');">fermer</a></span>
											<span class="aide_aide">AIDE :</span> <span class="aide_titre">galerie intégrée</span>
										</div>
										<div class="aide_texte">
											Cochez cette case si iGalerie est destiné à être intégré dans un fichier de votre site.<br />
											Voir la <a class="ex" href="http://www.igalerie.org/documentation.php">documentation</a> pour plus de précisions.
										</div>
									</div>
									<p class="field">
										Type d'URL&nbsp;:
										<select name="g_url_type">
											<?php $tpl->getURLType(); ?>

										</select>
										 <a class="lien_aide" href="javascript:void(0);" onclick="affiche_aide('aide_url_type');"><img src="template/defaut/style/aide.png" alt="aide" title="Cliquez ici pour obtenir de l'aide sur cette fonction" /></a>
									</p>
									<div id="aide_url_type" class="aide_contextuelle" style="display:none">
										<div class="aide_barre">
											<span class="aide_fermer"><a href="javascript:void(0);" onclick="affiche_aide('aide_url_type');">fermer</a></span>
											<span class="aide_aide">AIDE :</span> <span class="aide_titre">type d'URL</span>
										</div>
										<div class="aide_texte">
											Notez que les deux derniers types d'URL ne fonctionnent pas chez tous les hébergeurs.<br />
											Le tableau suivant montre un exemple correspondant à chaque type&nbsp;:
											<table id="aide_url_type_ex">
												<tr><td class="aide_url_type_ex_gauche">normal :</td><td>http://www.igalerie.org/demo/?alb=3</td></tr>
												<tr><td class="aide_url_type_ex_gauche">QUERY_STRING :</td><td>http://www.igalerie.org/demo/index.php?/album/3-nature</td></tr>
												<tr><td class="aide_url_type_ex_gauche">PATH_INFO :</td><td>http://www.igalerie.org/demo/index.php/album/3-nature</td></tr>
												<tr><td class="aide_url_type_ex_gauche">URL rewrite :</td><td>http://www.igalerie.org/demo/album/3-nature</td></tr>
											</table>
											<br />
											Pour que l'URL rewriting fonctionne, et avant d'activer la fonctionnalité, vous devez ajouter au fichier <em>.htaccess</em> situé à la racine d'iGalerie les lignes suivantes&nbsp;:
<pre>
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.*) index.php?/$1 [QSA,L]
</pre>
											en remplaçant "index.php" par le nom du fichier d'accès à la galerie (c'est à dire le nom du fichier situé dans le champ "URL de la galerie").
										</div>
									</div>
								</div>
							</fieldset>
						</div>
						<div class="options_submit"><input type="submit" class="submit submit_options" value="enregistrer" /></div>
					</div>
				</form>