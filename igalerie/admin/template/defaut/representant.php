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
			<p id="gal_rep_position">
				<a id="gal_retour" href="index.php?section=galerie&amp;page=gestion&amp;cat=<?php echo $_GET['cat']; ?>&amp;startnum=<?php echo $_GET['str']; ?>">retour</a>
				Cliquez sur une vignette pour la choisir comme nouveau représentant de <?php $tpl->getInfo('type_nom'); ?>.
			</p>
<?php if ($tpl->display('rep_nav')) : ?>
			<div id="gal_rep_nav">
				<form class="js_auto" action="index.php" method="get" id="gal_access_direct">
					<div>
						<input type="hidden" name="section" value="representant" />
						<input type="hidden" name="str" value="<?php echo $_GET['str']; ?>" />
						<input type="hidden" name="cat" value="<?php echo $_GET['cat']; ?>" />
						<input type="hidden" name="obj" value="<?php echo $_GET['obj']; ?>" />
							<?php $tpl->getGalerieHierarchie(); ?>

						<input class="submit gal_dis_submit" type="submit" value="OK" />
					</div>
				</form>
			</div>
<?php endif; ?>
<?php if ($tpl->display('barre_nav')) : ?>
				<div class="barre_nav" id="barre_nav_haut">
					<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
					<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
					<form action="./" method="get">
						<div>
							<input type="hidden" name="section" value="representant" /><input type="hidden" name="str" value="<?php echo $_GET['str']; ?>" /><input type="hidden" name="cat" value="<?php echo $_GET['cat']; ?>" /><input type="hidden" name="obj" value="<?php echo $_GET['obj']; ?>" /><input type="hidden" name="sub_obj" value="<?php echo $_GET['sub_obj']; ?>" /><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=representant&amp;str=<?php echo $_GET['str']; ?>&amp;cat=<?php echo $_GET['cat']; ?>&amp;obj=<?php echo $_GET['obj']; ?>&amp;sub_obj=<?php echo $_GET['sub_obj']; ?>';" ><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
						</div>
					</form>
					<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
					<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
				</div>
<?php endif; ?>

				<?php $tpl->getVignettes('<li><span class="env1">
							<span class="env2">
								<a style="background:url(%s) no-repeat center center" href="%s">
									<img %s src="template/defaut/style/pixtrans.png" alt="%s" />
								</a>
							</span>
						</span></li>' . "\n\t\t\t\t\t");?>

<?php if ($tpl->display('barre_nav')) : ?>
				<div class="barre_nav" id="barre_nav_bas">
					<?php $tpl->getBarreNav('<span class="premiere%s">%s</span>', 'first'); ?>
					<?php $tpl->getBarreNav('<span class="precedente%s">%s</span>', 'prev'); ?>
					<form action="./" method="get">
						<div>
							<input type="hidden" name="section" value="representant" /><input type="hidden" name="str" value="<?php echo $_GET['str']; ?>" /><input type="hidden" name="cat" value="<?php echo $_GET['cat']; ?>" /><input type="hidden" name="obj" value="<?php echo $_GET['obj']; ?>" /><input type="hidden" name="sub_obj" value="<?php echo $_GET['sub_obj']; ?>" /><select name="startnum" onchange="if (this.options[this.selectedIndex].value) window.location.href='?startnum=' + this.options[this.selectedIndex].value + '&amp;section=representant&amp;str=<?php echo $_GET['str']; ?>&amp;cat=<?php echo $_GET['cat']; ?>&amp;obj=<?php echo $_GET['obj']; ?>&amp;sub_obj=<?php echo $_GET['sub_obj']; ?>';" ><?php $tpl->getBarreNavPageNext('<option value="%s"%s>%s</option>'); ?></select>
						</div>
					</form>
					<?php $tpl->getBarreNav('<span class="suivante%s">%s</span>', 'next'); ?>
					<?php $tpl->getBarreNav('<span class="derniere%s">%s</span>', 'last'); ?>
				</div>
<?php endif; ?>