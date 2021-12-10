<?php
// <HTML>
$image = "\n\t" . '<a href="%s"><img alt="%s" src="%s" /></a><br />' . "\n";
$album = "\t" . '<a href="%s">%s</a>' . "\n";
// </HTML>

require_once(dirname(__FILE__) . '/includes/classes/class.outils.php');
require_once(dirname(__FILE__) . '/includes/classes/class.mysql.php');
require_once(dirname(__FILE__) . '/config/conf.php');

$gf = basename(GALERIE_URL);
$gf = (GALERIE_URL_TYPE == 'normal' && $gf == 'index.php') ? '' : $gf;
define('GALERIE_FILE', $gf);

$mysql = new connexion(MYSQL_SERV, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);
$mysql_requete = 'SELECT img.image_id,
                         img.image_nom,
                         img.image_chemin,
                         img.image_largeur,
                         img.image_hauteur,
                         cat.categorie_id AS album_id,
                         cat.categorie_nom AS album_nom
                    FROM ' . MYSQL_PREF . 'images AS img,
                         ' . MYSQL_PREF . 'categories AS cat
                   WHERE img.image_visible = "1" 
                     AND img.image_pass IS NULL
                     AND img.categorie_parent_id = cat.categorie_id
                ORDER BY RAND()
                   LIMIT 1';
$image_hasard = $mysql->select($mysql_requete);
if (!is_array($image_hasard)) {
    die('erreur ' . __LINE__ . '<br />' . mysql_error());
}

$image_nom = $image_hasard[0]['image_nom'];
$image_lien = outils::genLink('?img=' . $image_hasard[0]['image_id'], $image_nom);
$image_thumb = GALERIE_PATH . '/getimg.php?img=' . $image_hasard[0]['image_chemin'] . '&amp;r&amp;a';
$album_nom = $image_hasard[0]['album_nom'];
$album_lien = outils::genLink('?alb=' . $image_hasard[0]['album_id'], '', $album_nom);

printf($image, $image_lien, $image_nom, $image_thumb);
printf($album, $album_lien, $album_nom);
?>
