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
				<div id="config_infosys">
					<p>Infos serveur&nbsp;: <strong><?php $tpl->getInfo('server_infos'); ?></strong></p>
					<p>Temps serveur&nbsp;: <strong><?php $tpl->getInfo('server_time'); ?></strong></p>
					<br />
					<p>Système d'exploitation&nbsp;: <strong><?php $tpl->getInfo('php_os'); ?></strong></p>
					<p>Version de PHP&nbsp;: <strong><?php $tpl->getInfo('php_version'); ?></strong></p>
					<p>Version de MySQL&nbsp;: <strong><?php $tpl->getInfo('mysql_version'); ?></strong></p>
					<p>Version de GD&nbsp;: <strong><?php $tpl->getInfo('gd_version'); ?></strong></p>
					<p>Support JPG&nbsp;: <strong><?php $tpl->getInfo('gd_jpg'); ?></strong></p>
					<p>Support GIF&nbsp;: <strong><?php $tpl->getInfo('gd_gif'); ?></strong></p>
					<p>Support PNG&nbsp;: <strong><?php $tpl->getInfo('gd_png'); ?></strong></p>
					<p>Extension Exif&nbsp;: <strong><?php $tpl->getInfo('exif'); ?></strong></p>
					<p>Extension Zip&nbsp;: <strong><?php $tpl->getInfo('zip'); ?></strong></p>
					<p>Mémoire disponible&nbsp;: <strong><?php $tpl->getInfo('memory_limit'); ?></strong></p>
					<p>Safe mode&nbsp;: <strong><?php $tpl->getInfo('safe_mode'); ?></strong></p>
					<p>Option magic_quotes_gpc&nbsp;: <strong><?php $tpl->getInfo('magic_quotes_gpc'); ?></strong></p>
					<p>Option magic_quotes_runtime&nbsp;: <strong><?php $tpl->getInfo('magic_quotes_runtime'); ?></strong></p>
					<br />
					<p><?php $tpl->getInfo('acces_conf'); ?></p>
					<p><?php $tpl->getInfo('acces_albums'); ?></p>
					<p><?php $tpl->getInfo('acces_cache'); ?></p>
					<p><?php $tpl->getInfo('acces_cat_thumb'); ?></p>
					<br />
				</div>
