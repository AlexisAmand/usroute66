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
			<h2>Tags de la galerie</h2>
			<?php $tpl->display('rapport'); ?>
			<?php $tpl->getRapport('<br />%s<br />'); ?>

<?php if ($tpl->displayTags()) : ?>
			<div id="tags">
				<form action="index.php?section=tags" method="post">
					<div class="tag_submit">
						<input name="tag_action" type="submit" value="valider les changements" />
						<?php $tpl->getVID(); ?>

					</div>
					<table>
						<tr><th>nom du tag</th><th>images liées</th><th>suppression</th></tr>
<?php while ($tpl->getNextTag()) : ?>
						<tr>
							<td class="tag_name"><input maxlength="255" size="30" class="text" name="tag_name[<?php $tpl->getTag('tag_id'); ?>]" type="text" value="<?php $tpl->getTag('tag_id'); ?>" /></td>
							<td class="tag_nombre"><?php $tpl->getTag('tag_nombre'); ?></td>
							<td class="tag_supp">
								<input type="checkbox" id="tagid_<?php $tpl->getTag('id'); ?>" name="tag_supp[<?php $tpl->getTag('tag_id'); ?>]" value="<?php $tpl->getTag('tag_id'); ?>" />
								<label for="tagid_<?php $tpl->getTag('id'); ?>">supprimer</label>
							</td>
						</tr>
<?php endwhile; ?>
					</table>
					<div class="tag_submit"><input name="tag_action" type="submit" value="valider les changements" /></div>
				</form>
			</div>
<?php endif; ?>
			<?php $tpl->getNullTag('<br /><div id="tag_null" class="rapport_msg rapport_infos"><div><span>La galerie ne contient aucun tag.</span></div></div>'); ?>
			<br />
