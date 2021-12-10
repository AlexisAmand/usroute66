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

				<div id="modif_groupe_retour"><a href="?section=utilisateurs&amp;page=groupes">retour</a></div>

				<form id="modif_groupe" action="index.php?section=utilisateurs&amp;page=modif_groupe" method="post">
					<div>
						<?php $tpl->getVID(); ?>

						<input type="hidden" name="groupe" value="<?php echo $_REQUEST['groupe']; ?>" />
						<fieldset>
							<legend>Modification du groupe</legend>
							<div class="fielditems">
								<span id="modif_groupe_titre">Paramètres du groupe</span>
								<p class="field modif_groupe_sous_titre">
									Général&nbsp;:
								</p>
								<p class="field">
									<label for="groupe_nom">Nom&nbsp;:</label>
									<input value="<?php $tpl->getModifGroupe('nom'); ?>" size="50" maxlength="255" type="text" class="text" name="groupe_nom" id="groupe_nom" />
								</p>
								<p class="field">
									<label for="groupe_titre">Titre&nbsp;:</label>
									<input value="<?php $tpl->getModifGroupe('titre'); ?>" size="50" maxlength="255" type="text" class="text" name="groupe_titre" id="groupe_titre" />
								</p>

<?php if ($tpl->display('groupe_noadmin')) : ?>
								<div class="separate"></div>

								<p class="field modif_groupe_sous_titre">
									Autorisations&nbsp;:
								</p>
								<p class="field">
									<input<?php $tpl->getModifGroupe('commentaires'); ?> type="checkbox" name="groupe_aut_comments" id="groupe_aut_comments" />
									<label for="groupe_aut_comments">Envoi de commentaires</label>
								</p>
								<p class="field">
									<input<?php $tpl->getModifGroupe('votes'); ?> type="checkbox" name="groupe_aut_votes" id="groupe_aut_votes" />
									<label for="groupe_aut_votes">Votes</label>
								</p>
								<p class="field">
									<input<?php $tpl->getModifGroupe('perso'); ?> type="checkbox" name="groupe_aut_perso" id="groupe_aut_perso" />
									<label for="groupe_aut_perso">Personnalisation</label>
								</p>
								<p class="field">
									<input<?php $tpl->getModifGroupe('recherche_avance'); ?> type="checkbox" name="groupe_aut_search" id="groupe_aut_search" />
									<label for="groupe_aut_search">Recherche avancée</label>
								</p>
<?php if ($tpl->display('groupe_membres')) : ?>
								<p class="field">
									<input<?php $tpl->getModifGroupe('newsletter'); ?> type="checkbox" name="groupe_aut_newsletter" id="groupe_aut_newsletter" />
									<label for="groupe_aut_newsletter">Newsletter</label>
								</p>
								<p class="field">
									<input<?php $tpl->getModifGroupe('upload'); ?> type="checkbox" name="groupe_aut_upload" id="groupe_aut_upload" />
									<label for="groupe_aut_upload">Envoi d'images&nbsp;:</label>
								</p>
								<div class="field_second">
									<p class="field">
										<input<?php $tpl->getModifGroupe('direct'); ?> type="radio" name="groupe_aut_upload_mode" value="direct" id="groupe_aut_upload_direct" />
										<label for="groupe_aut_upload_direct">direct</label>
									</p>
									<p class="field">
										<input<?php $tpl->getModifGroupe('attente'); ?> type="radio" name="groupe_aut_upload_mode" value="attente" id="groupe_aut_upload_attente" />
										<label for="groupe_aut_upload_attente">en attente</label>
									</p>
								</div>
								<p class="field">
									<input<?php $tpl->getModifGroupe('upload_create'); ?> type="checkbox" name="groupe_aut_create" id="groupe_aut_create" />
									<label for="groupe_aut_create">Création d'albums</label>
								</p>
<?php endif; ?>


								<div class="separate"></div>

								<p class="field modif_groupe_sous_titre">
									Déverrouiller les albums protégés pour&nbsp;:
								</p>
								<p class="field">
									<input<?php $tpl->getModifGroupe('aucun'); ?> type="radio" name="groupe_protect" value="aucun" id="groupe_protect_aucun" />
									<label for="groupe_protect_aucun">aucun album</label>
								</p>
								<p class="field">
									<input<?php $tpl->getModifGroupe('tous'); ?> type="radio" name="groupe_protect" value="tous" id="groupe_protect_tous" />
									<label for="groupe_protect_tous">tous les albums</label>
								</p>
								<p class="field">
									<input<?php $tpl->getModifGroupe('select'); ?> type="radio" name="groupe_protect" value="select" id="groupe_protect_select" />
									<label for="groupe_protect_select">les albums correspondant aux mots de passe suivant&nbsp;:</label>
								</p>
								<div class="field_second">
									<table id="groupe_protect_mdp">
										<tr>
											<td>
												<p>Mots de passe déverrouillés&nbsp;:</p>
												<select class="select_multiple" name="groupe_protect_mdp_dev[]" multiple="multiple" size="8">
													<?php $tpl->getModifGroupe('pass_dev'); ?>
												</select>
											</td>
											<td class="groupe_protect_mdp_e">
												<p>Mots de passe verrouillés&nbsp;:</p>
												<select class="select_multiple" name="groupe_protect_mdp_ver[]" multiple="multiple" size="8">
													<?php $tpl->getModifGroupe('pass_ver'); ?>
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<input type="checkbox" name="groupe_protect_mdp_rever" id="groupe_protect_mdp_rever" />
												<label class="bans_ajout_txt" for="groupe_protect_mdp_rever">Verrouiller les mots de passe sélectionnés</label>
											</td>
											<td class="groupe_protect_mdp_e">
												<input type="checkbox" name="groupe_protect_mdp_dever" id="groupe_protect_mdp_dever" />
												<label class="bans_ajout_txt" for="groupe_protect_mdp_dever">Déverrouiller les mots de passe sélectionnés</label>
											</td>
										</tr>
									</table>
								</div>
<?php endif; ?>
							</div>
							<input type="submit" class="submit" value="enregistrer" />
						</fieldset>
					</div>
				</form>
