<?php
// On écrit toutes les préférences dans le cookie.
$galerie->prefs->ecrire();

// On démarre la classe de template.
$tpl = new template($galerie->template);
$tpl->data['debug']['mysql_requetes'] = $galerie->mysql->requetes;
?>
