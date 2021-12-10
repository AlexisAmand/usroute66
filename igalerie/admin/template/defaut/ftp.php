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
			<div id="msg_ftp" class="aide_fixe">
				<div class="aide_barre">
					<span class="aide_titre">Ajout d'images par <acronym title="File Transfer Protocol">FTP</acronym></span>
				</div>
				<span class="aide_texte">
					* Ici seront ajoutées à la base de données les images (uniquement au format <acronym title="Joint Picture Expert's Group">JPEG</acronym>, <acronym title="Graphics Interchange Format">GIF</acronym> ou <acronym title="Portable Network Graphics">PNG</acronym>)<br />que vous aurez envoyées par FTP
					dans le répertoire des albums&nbsp;:
					<span id="albums_dir"><?php $tpl->getAlbumsDir(); ?></span>
					* Le nom des images et des répertoires ne doit contenir que des chiffres, des lettres non accentuées et les caractères « - » et « _ ». Dans le cas contraire, les lettres accentuées auront leurs accents enlevés et les autres caractères seront remplacés par « _ ».<br /><br />
					<strong>=></strong> Pour en savoir plus, allez sur <a class="ex" href="http://www.igalerie.org/documentation.php#adi">cette page</a> de la documentation du site.<br /><br />
					<strong>=></strong> Notez que cette opération peut être longue si vous avez un grand nombre d'images.
				</span>
			</div>
			<form action="" method="post">
				<div>
					<?php $tpl->getVID(); ?>

					<input type="hidden" name="section" value="ftp" />
					<input type="hidden" name="action" value="enregistrement" />
				</div>
				<div id="bouton_ftp">
					<input type="submit" value="scanner le répertoire des albums" />
				</div>
			</form>
			<br />
			<?php $tpl->getRapport('%s'); ?>
			<?php $tpl->display('rapport'); ?>
			<br />