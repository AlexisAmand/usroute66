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
			<h2>Votes de la galerie</h2>

<?php if ($tpl->displayVotes()) : ?>
			<p id="votes_position">
				<?php $tpl->getVotesPosition(); ?>

			</p>

			<div id="co_perso">
				<form action="index.php?section=votes<?php $tpl->getVotesParams('params'); ?>" method="post">
					<div id="co_display">
						<?php $tpl->getVID(); ?>

						<div id="co_nbco">
							Nb. par page&nbsp;:
							<select name="nb">
								<?php $tpl->getVotesNb('<option value="%s"%s>%s</option>', 100); ?>

							</select>
						</div>
						<div id="co_sort">
							Trier par&nbsp;:
							<select name="sort">
								<option<?php $tpl->getVoteSortOrdre('image_nom'); ?> value="image_nom">nom image</option>
								<option<?php $tpl->getVoteSortOrdre('categorie_nom'); ?> value="categorie_nom">nom album</option>
								<option<?php $tpl->getVoteSortOrdre('vote_date'); ?> value="vote_date">date</option>
								<option<?php $tpl->getVoteSortOrdre('vote_ip'); ?> value="vote_ip">IP</option>
								<option<?php $tpl->getVoteSortOrdre('vote_note'); ?> value="vote_note">note</option>
							</select>
							<select class="asc-desc" name="sens">
								<option<?php $tpl->getVoteSortSens('ASC'); ?> value="ASC">croissant</option>
								<option<?php $tpl->getVoteSortSens('DESC'); ?> value="DESC">décroissant</option>
							</select>
						</div>
						<input class="submit co_dis_submit" type="submit" value="OK" />
					</div>
				</form>
				<div id="co_nav">
<?php if ($tpl->display('subcats')) : ?>
					<form action="index.php" method="get">
						<div>
							<input type="hidden" name="section" value="votes" />
							<?php $tpl->getVotesParams('input'); ?>

							Afficher pour&nbsp;:
							<select class="albums_list" name="<?php $tpl->getVote('sub'); ?>">
								<?php $tpl->getVoteSubCats('<option value="%s">%s</option>'); ?>
									
							</select>
							<input class="submit co_dis_submit" type="submit" value="OK" />
						</div>
					</form>
<?php endif; ?>
				</div>
			</div>

			<?php $tpl->display('rapport'); ?>
			<?php $tpl->getRapport('<br />%s<br />'); ?>

			<div class="js_coche">
				<?php $tpl->getVoteDeleteAll('<a title="%s" class="lien_js" id="votes_delete_all" href="javascript:confirm_sup_votes(\'%s\',\'%s\');">tout supprimer</a>'); ?>

				<a class="lien_js" href="javascript:votes_all_select();">tout sélectionner</a>
				-
				<a class="lien_js" href="javascript:votes_invert_select();">inverser la sélection</a>
			</div>

<?php if ($tpl->display('barre_nav')) : ?>
			<div class="barre_nav" id="barre_nav_haut">
				<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
				<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
				<form class="js_auto" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
					<div>
						<select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '<?php $tpl->getVotesParams(); ?>';">
							<?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?>

						</select>
					</div>
				</form>
				<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
				<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
			</div>
<?php endif; ?>

			<div id="votes">
				<form action="index.php?section=votes<?php $tpl->getVotesParams(); ?>" method="post">
					<div class="vote_submit">
						<?php $tpl->getVID(); ?>

						<input name="vote_action" type="submit" value="supprimer les votes sélectionnés" />
					</div>
					<table>
						<tr><th>image</th><th>album</th><th>date</th><th>IP</th><th>note</th><th id="votes_vide"></th></tr>
<?php while ($tpl->getNextVote()) : ?>
						<tr>
							<td class="vote_image" onmouseover="this.style.cursor='pointer'" onclick="window.location='<?php $tpl->getvote('image_lien'); ?>'"><?php $tpl->getvote('image', 0, 80); ?></td>
							<td class="vote_album"><?php $tpl->getvote('album'); ?></td>
							<td class="vote_date"><?php $tpl->getvote('date'); ?></td>
							<td class="vote_IP"><?php $tpl->getvote('ip'); ?></td>
							<td class="vote_note"><?php $tpl->getvote('note'); ?></td>
							<td class="vote_selection"><input type="checkbox" name="vote_selection[<?php $tpl->getvote('id'); ?>]" /></td>
						</tr>
<?php endwhile; ?>
					</table>
					<div class="vote_submit"><input name="vote_action" type="submit" value="supprimer les votes sélectionnés" /></div>
				</form>
			</div>
			<br />

<?php if ($tpl->display('barre_nav')) : ?>
			<div class="barre_nav" id="barre_nav_bas">
				<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
				<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
				<form class="js_auto" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="get">
					<div>
						<select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '<?php $tpl->getVotesParams(); ?>';">
							<?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?>

						</select>
					</div>
				</form>
				<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
				<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
			</div>
<?php endif; ?>
<?php endif; ?>
			<?php $tpl->getNullVotes('<br /><div id="vote_null" class="rapport_msg rapport_infos"><div><span>La galerie ne contient aucun vote.</span></div></div><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />'); ?>
