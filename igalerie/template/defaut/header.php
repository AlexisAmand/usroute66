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

if (!GALERIE_INTEGRATED) :

define('CHARSET', 'ISO-8859-15');
header('Content-Type: text/html; charset=' . CHARSET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>

<title><?php $tpl->getInfo('title'); ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />

<?php $tpl->getCSS(); ?>

<?php if ($tpl->display('rss')) : ?>
<link rel="alternate" type="application/rss+xml" title="Flux RSS 2.0 des images de la galerie" href="<?php $tpl->getGaleriePath(); ?>/rss.php" />
<?php if ($tpl->display('commentaires')) : ?>
<link rel="alternate" type="application/rss+xml" title="Flux RSS 2.0 des commentaires de la galerie" href="<?php $tpl->getGaleriePath(); ?>/rss.php?type=com" />
<?php endif; ?>
<?php endif; ?>

<script type="text/javascript" src="<?php $tpl->getInfo('style_dir'); ?>/galerie_style.js"></script>
<script type="text/javascript" src="<?php $tpl->getGaleriePath(); ?>/template/defaut/galerie.js"></script>
<?php $tpl->getDiaporamaJS('diaporama.js'); ?>


</head>


<body>
<?php else : ?>

<!-- <iGalerie> -->
<?php endif; ?>
