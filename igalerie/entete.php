<!-- <header iGalerie> -->
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />

<?php $tpl->getCSS(); ?>

<?php if ($tpl->display('rss')) : ?>
<link rel="alternate" type="application/rss+xml" title="Flux RSS 2.0 des images de la galerie" href="<?php $tpl->getGaleriePath(); ?>/rss.php" />
<?php if ($tpl->display('commentaires')) : ?>
<link rel="alternate" type="application/rss+xml" title="Flux RSS 2.0 des commentaires de la galerie" href="<?php $tpl->getGaleriePath(); ?>/rss.php?com=1" />
<?php endif; ?>
<?php endif; ?>

<script type="text/javascript" src="<?php $tpl->getGaleriePath(); ?>/template/<?php echo GALERIE_THEME; ?>/style/<?php echo GALERIE_STYLE; ?>/galerie_style.js"></script>
<script type="text/javascript" src="<?php $tpl->getGaleriePath(); ?>/template/<?php echo GALERIE_THEME; ?>/galerie.js"></script>
<?php $tpl->getDiaporamaJS('diaporama.js'); ?>

<!-- </header iGalerie> -->
