<?php
// On �crit toutes les pr�f�rences dans le cookie.
$galerie->prefs->ecrire();

// On d�marre la classe de template.
$tpl = new template($galerie->template);
$tpl->data['debug']['mysql_requetes'] = $galerie->mysql->requetes;
?>
