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

function create_tables() {

	// Connexion à la base de données.
	$mysql = new connexion(MYSQL_SERV, MYSQL_USER, MYSQL_PASS, MYSQL_BASE);

	$create_tables = array();

	// Création des tables
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'config (
		parametre VARCHAR(40) NOT NULL,
		valeur TEXT,
		PRIMARY KEY (parametre))';
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'images (
		image_id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		categorie_parent_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		user_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 1,
		image_chemin TINYTEXT NOT NULL,
		image_nom TINYTEXT NOT NULL,
		image_description TEXT,
		image_tags TEXT,
		image_date INT(10) NOT NULL DEFAULT 0,
		image_date_creation INT(10) NOT NULL DEFAULT 0,
		image_poids DECIMAL(8,1) NOT NULL,
		image_hauteur VARCHAR(5) NOT NULL,
		image_largeur VARCHAR(5) NOT NULL,
		image_exif_datetimeoriginal INT(10),
		image_exif_make TINYTEXT,
		image_exif_model TINYTEXT,
		image_hits INT(12) UNSIGNED DEFAULT 0,
		image_commentaires MEDIUMINT UNSIGNED DEFAULT 0,
		image_votes MEDIUMINT UNSIGNED DEFAULT 0,
		image_note DECIMAL(6,4) UNSIGNED DEFAULT 0,
		image_pass VARCHAR(40),
		image_visible ENUM ("0","1") NOT NULL DEFAULT "1")';
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'categories (
		categorie_id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		user_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 1,
		categorie_chemin TINYTEXT NOT NULL,
		categorie_nom TINYTEXT NOT NULL,
		categorie_description TEXT,
		categorie_poids DECIMAL(12,1) UNSIGNED NOT NULL DEFAULT 0,
		categorie_poids_inactive DECIMAL(12,1) UNSIGNED NOT NULL DEFAULT 0,
		image_representant_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		categorie_images MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		categorie_images_inactive MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		categorie_hits INT(12) UNSIGNED NOT NULL DEFAULT 0,
		categorie_hits_inactive INT(12) UNSIGNED NOT NULL DEFAULT 0,
		categorie_commentaires MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		categorie_commentaires_inactive MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		categorie_note DECIMAL(6,4) UNSIGNED NOT NULL DEFAULT 0,
		categorie_note_inactive DECIMAL(6,4) UNSIGNED NOT NULL DEFAULT 0,
		categorie_votes MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		categorie_votes_inactive MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		categorie_dernier_ajout INT(10) NOT NULL DEFAULT 0,
		categorie_derniere_modif INT(10) NOT NULL DEFAULT 0,
		categorie_date INT(10) NOT NULL DEFAULT 0,
		categorie_pass VARCHAR(40),
		categorie_visible ENUM ("0","1","2") NOT NULL DEFAULT "1")';
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'commentaires (
		commentaire_id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		image_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		user_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		commentaire_date INT(10) NOT NULL DEFAULT 0,
		commentaire_auteur TINYTEXT NOT NULL,
		commentaire_mail TINYTEXT,
		commentaire_web TINYTEXT,
		commentaire_message TEXT NOT NULL,
		commentaire_ip VARCHAR(20),
		commentaire_visible ENUM ("0","1","2") NOT NULL DEFAULT "1")';
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'tags (
		tag_id VARBINARY(255) NOT NULL,
		image_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		PRIMARY KEY (tag_id, image_id))';
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'votes (
		vote_id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		image_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		vote_date INT(10) NOT NULL DEFAULT 0,
		vote_note TINYINT(1) NOT NULL DEFAULT 0,
		vote_cookie VARCHAR(12) NOT NULL,
		vote_ip VARCHAR(20))';
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'users (
		user_id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		groupe_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 3,
		user_login VARCHAR(50) NOT NULL,
		user_pass VARCHAR(32) NOT NULL,
		user_oubli VARCHAR(27),
		user_mail VARCHAR(255),
		user_mail_public ENUM ("0","1") NOT NULL DEFAULT "0",
		user_web TINYTEXT,
		user_lieu VARCHAR(60),
		user_avatar ENUM ("0","1") NOT NULL DEFAULT "0",
		user_newsletter ENUM ("0","1") NOT NULL DEFAULT "0",
		user_date_creation INT(10) NOT NULL DEFAULT 0,
		user_date_derniere_visite INT(10) NOT NULL DEFAULT 0,
		user_ip_creation VARCHAR(20),
		user_ip_derniere_visite VARCHAR(20),
		user_date_dernier_upload INT(10) NOT NULL DEFAULT 0,
		user_session_id VARCHAR(20),
		UNIQUE KEY (user_login))';
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'groupes (
		groupe_id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		groupe_nom VARCHAR(80) NOT NULL,
		groupe_titre VARCHAR(80) NOT NULL,
		groupe_date_creation INT(10) NOT NULL DEFAULT 0,
		groupe_commentaires ENUM ("0","1") NOT NULL DEFAULT "1",
		groupe_votes ENUM ("0","1") NOT NULL DEFAULT "1",
		groupe_recherche_avance ENUM ("0","1") NOT NULL DEFAULT "1",
		groupe_perso ENUM ("0","1") NOT NULL DEFAULT "1",
		groupe_upload ENUM ("0","1") NOT NULL DEFAULT "0",
		groupe_upload_mode ENUM ("direct","attente") NOT NULL DEFAULT "attente",
		groupe_upload_create ENUM ("0","1") NOT NULL DEFAULT "0",
		groupe_album_pass_mode ENUM ("aucun","tous","select") NOT NULL DEFAULT "aucun",
		groupe_album_pass TEXT NOT NULL,
		groupe_newsletter ENUM ("0","1") NOT NULL DEFAULT "0",
		UNIQUE KEY (groupe_nom))';
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'images_attente (
		img_att_id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		user_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		categorie_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		img_att_nom TINYTEXT,
		img_att_description TEXT,
		img_att_fichier TINYTEXT NOT NULL,
		img_att_type VARCHAR(4) NOT NULL,
		img_att_poids DECIMAL(8,1) NOT NULL,
		img_att_hauteur VARCHAR(5) NOT NULL,
		img_att_largeur VARCHAR(5) NOT NULL,
		img_att_date INT(10) NOT NULL DEFAULT 0,
		img_att_ip VARCHAR(20))';
	$create_tables[] = 'CREATE TABLE IF NOT EXISTS ' . MYSQL_PREF . 'favoris (
		fav_id MEDIUMINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		user_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
		image_id MEDIUMINT UNSIGNED NOT NULL DEFAULT 0)';
	for ($i = 0; $i < count($create_tables); $i++) {
		if (!$mysql->requete($create_tables[$i]
			. ' ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci')) {
			return array(0 => '[' . __LINE__ . '.' . $i . '] ' . mysql_error(), 1 => drop_tables($mysql));
		}
	}

	// Création des enregistrements de configuration.
	$config_inserts = '';

	// Paramètres d'administration.
	$config_inserts .= ',("admin_mail","")';
	$config_inserts .= ',("admin_vid","")';
	$config_inserts .= ',("admin_comment_nb","10")';
	$config_inserts .= ',("admin_comment_ordre","commentaire_date")';
	$config_inserts .= ',("admin_comment_sens","DESC")';
	$config_inserts .= ',("admin_comment_ban","a:3:{s:9:\"mots-cles\";a:16:{s:6:\"[email\";i:1;s:4:\"[img\";i:1;s:4:\"[url\";i:1;s:4:\"<img\";i:1;s:7:\"<script\";i:1;s:5:\"<link\";i:1;s:6:\"<frame\";i:1;s:7:\"<iframe\";i:1;s:6:\"<style\";i:1;s:7:\"<object\";i:1;s:6:\"<embed\";i:1;s:5:\"<meta\";i:1;s:5:\"href=\";i:1;s:6:\"href =\";i:1;s:4:\"src=\";i:1;s:5:\"src =\";i:1;}s:7:\"auteurs\";a:10:{s:10:\"*discount*\";i:1;s:8:\"*casino*\";i:1;s:10:\"*ringtone*\";i:1;s:6:\"*buy *\";i:1;s:8:\"*viagra*\";i:1;s:8:\"*cialis*\";i:1;s:7:\"*xanax*\";i:1;s:7:\"*poker*\";i:1;s:10:\"*download*\";i:1;s:12:\"*best price*\";i:1;}s:2:\"IP\";a:0:{}}")';
	$config_inserts .= ',("admin_comment_filtre", "tous")';
	$config_inserts .= ',("admin_comment_moderer", "0")';
	$config_inserts .= ',("admin_comment_alert", "0")';
	$config_inserts .= ',("admin_comment_objet", "Nouveau commentaire")';
	$config_inserts .= ',("admin_comment_msg_display", "1")';
	$config_inserts .= ',("admin_galerie_nb", "5")';
	$config_inserts .= ',("admin_galerie_ordre","date")';
	$config_inserts .= ',("admin_galerie_sens","DESC")';
	$config_inserts .= ',("admin_galerie_filtre", "tous")';
	$config_inserts .= ',("admin_session_id", "")';
	$config_inserts .= ',("admin_session_expire", "")';
	$config_inserts .= ',("admin_vote_nb","20")';
	$config_inserts .= ',("admin_vote_ordre","vote_date")';
	$config_inserts .= ',("admin_vote_sens","DESC")';
	$config_inserts .= ',("admin_no_hits","0")';
	$config_inserts .= ',("admin_no_hits_mode","cookie")';
	$config_inserts .= ',("admin_no_hits_ip","")';
	$config_inserts .= ',("admin_membres_nb","20")';
	$config_inserts .= ',("admin_membres_ordre","date_creation")';
	$config_inserts .= ',("admin_membres_sens","DESC")';
	$config_inserts .= ',("admin_imgatt_nb","20")';
	$config_inserts .= ',("admin_imgatt_ordre","date_envoi")';
	$config_inserts .= ',("admin_imgatt_sens","DESC")';

	// Paramètres généraux.
	$config_inserts .= ',("galerie_nom","Galerie")';
	$config_inserts .= ',("galerie_titre","Ma galerie")';
	$config_inserts .= ',("galerie_titre_court","iGalerie")';
	$config_inserts .= ',("galerie_message_accueil","Bienvenue sur ma galerie !")';
	$config_inserts .= ',("galerie_message_footer","Texte de bas de page.")';
	$config_inserts .= ',("galerie_message_fermeture","La galerie est fermée.\nVeuillez revenir plus tard.")';
	$config_inserts .= ',("galerie_recent","7")';
	$config_inserts .= ',("galerie_recent_nb","0")';
	$config_inserts .= ',("galerie_courriel","")';
	$config_inserts .= ',("galerie_footer","0")';
	$config_inserts .= ',("galerie_images_window", "0")';
	$config_inserts .= ',("galerie_tb_date_format", "%d/%m/%Y")';
	$config_inserts .= ',("galerie_im_date_format", "%A %d %B %Y")';
	$config_inserts .= ',("galerie_contact_text","")';
	$config_inserts .= ',("galerie_contact","0")';
	$config_inserts .= ',("galerie_key","' . outils::gen_key(32) . '")';
	$config_inserts .= ',("galerie_images_text_correction","0")';
	$config_inserts .= ',("galerie_images_resize","1")';
	$config_inserts .= ',("galerie_images_resize_max_html","650x600")';
	$exif = 'a:2:{s:4:\"IFD0\";a:7:{s:4:\"Make\";a:3:{s:6:\"active\";i:1;s:4:\"desc\";s:6:\"Marque\";s:6:\"method\";s:6:\"simple\";}s:5:\"Model\";a:3:{s:6:\"active\";i:1;s:4:\"desc\";s:6:\"Modèle\";s:6:\"method\";s:6:\"simple\";}s:11:\"XResolution\";a:3:{s:6:\"active\";i:0;s:4:\"desc\";s:22:\"Résolution horizontale\";s:6:\"method\";s:6:\"simple\";}s:11:\"YResolution\";a:3:{s:6:\"active\";i:0;s:4:\"desc\";s:20:\"Résolution verticale\";s:6:\"method\";s:6:\"simple\";}s:14:\"ResolutionUnit\";a:4:{s:6:\"active\";i:0;s:4:\"desc\";s:20:\"Unités de résolution\";s:6:\"method\";s:5:\"liste\";s:6:\"format\";a:3:{i:1;s:6:\"pixels\";i:2;s:6:\"pouces\";i:3;s:11:\"centimètres\";}}s:11:\"Orientation\";a:4:{s:6:\"active\";i:0;s:4:\"desc\";s:11:\"Orientation\";s:6:\"method\";s:5:\"liste\";s:6:\"format\";a:8:{i:1;s:13:\"haut - gauche\";i:2;s:13:\"haut - droite\";i:3;s:12:\"bas - droite\";i:4;s:12:\"bas - gauche\";i:5;s:13:\"gauche - haut\";i:6;s:13:\"droite - haut\";i:7;s:12:\"droite - bas\";i:8;s:12:\"gauche - bas\";}}s:8:\"Software\";a:3:{s:6:\"active\";i:0;s:4:\"desc\";s:8:\"Logiciel\";s:6:\"method\";s:6:\"simple\";}}s:4:\"EXIF\";a:11:{s:12:\"ExposureTime\";a:4:{s:6:\"active\";i:1;s:4:\"desc\";s:18:\"Temps d\'exposition\";s:6:\"method\";s:6:\"nombre\";s:6:\"format\";s:7:\"%2.4f s\";}s:16:\"DateTimeOriginal\";a:4:{s:6:\"active\";i:1;s:4:\"desc\";s:32:\"Date et heure de la prise de vue\";s:6:\"method\";s:4:\"date\";s:6:\"format\";s:19:\"%d %B %Y à %H:%M:%S\";}s:11:\"FocalLength\";a:4:{s:6:\"active\";i:1;s:4:\"desc\";s:18:\"Longueur de focale\";s:6:\"method\";s:6:\"nombre\";s:6:\"format\";s:8:\"%2.2f mm\";}s:7:\"FNumber\";a:4:{s:6:\"active\";i:1;s:4:\"desc\";s:9:\"Ouverture\";s:6:\"method\";s:6:\"nombre\";s:6:\"format\";s:4:\"f/%s\";}s:5:\"Flash\";a:4:{s:6:\"active\";i:1;s:4:\"desc\";s:5:\"Flash\";s:6:\"method\";s:5:\"liste\";s:6:\"format\";a:22:{i:0;s:19:\"Flash non déclenché\";i:1;s:15:\"Flash déclenché\";i:5;s:27:\"Retour de flash non détecté\";i:7;s:23:\"Retour de flash détecté\";i:9;s:27:\"Flash déclenché, mode forcé\";i:13;s:56:\"Flash déclenché, mode forcé, retour de flash non détecté\";i:15;s:52:\"Flash déclenché, mode forcé, retour de flash détecté\";i:16;s:31:\"Flash non déclenché, mode forcé\";i:24;s:37:\"Flash non déclenché, mode automatique\";i:25;s:33:\"Flash déclenché, mode automatique\";i:29;s:62:\"Flash déclenché, mode automatique, retour de flash non détecté\";i:31;s:58:\"Flash déclenché, mode automatique, retour de flash détecté\";i:32;s:19:\"Pas de flash activé\";i:65;s:40:\"Flash déclenché, anti yeux-rouges activé\";i:69;s:69:\"Flash déclenché, anti yeux-rouges activé, retour de flash non détecté\";i:71;s:65:\"Flash déclenché, anti yeux-rouges activé, retour de flash détecté\";i:73;s:52:\"Flash déclenché, mode forcé, anti yeux-rouges activé\";i:77;s:81:\"Flash déclenché, mode forcé, anti yeux-rouges activé, retour de flash non détecté\";i:79;s:77:\"Flash déclenché, mode forcé, anti yeux-rouges activé, retour de flash détecté\";i:93;s:87:\"Flash déclenché, mode automatique, anti yeux-rouges activé, retour de flash non détecté\";i:95;s:83:\"Flash déclenché, mode automatique, anti yeux-rouges activé, retour de flash détecté\";i:89;s:58:\"Flash déclenché, mode automatique, anti yeux-rouges activé\";}}s:11:\"ExifVersion\";a:3:{s:6:\"active\";i:0;s:4:\"desc\";s:12:\"Version Exif\";s:6:\"method\";s:7:\"version\";}s:15:\"ISOSpeedRatings\";a:3:{s:6:\"active\";i:0;s:4:\"desc\";s:3:\"ISO\";s:6:\"method\";s:6:\"simple\";}s:16:\"MaxApertureValue\";a:4:{s:6:\"active\";i:0;s:4:\"desc\";s:18:\"Ouverture maximale\";s:6:\"method\";s:6:\"nombre\";s:6:\"format\";s:8:\"%2.2f mm\";}s:12:\"WhiteBalance\";a:4:{s:6:\"active\";i:0;s:4:\"desc\";s:18:\"Balance des blancs\";s:6:\"method\";s:5:\"liste\";s:6:\"format\";a:2:{i:0;s:11:\"Automatique\";i:1;s:8:\"Manuelle\";}}s:15:\"ExposureProgram\";a:4:{s:6:\"active\";i:0;s:4:\"desc\";s:17:\"Mode d\'exposition\";s:6:\"method\";s:5:\"liste\";s:6:\"format\";a:8:{i:1;s:10:\"Non défini\";i:2;s:6:\"Manuel\";i:3;s:18:\"Priorité ouverture\";i:4;s:27:\"Priorité temps d\'exposition\";i:5;s:19:\"Programme \'creatif\'\";i:6;s:18:\"Programme \'action\'\";i:7;s:13:\"mode portrait\";i:8;s:12:\"mode paysage\";}}s:13:\"SensingMethod\";a:4:{s:6:\"active\";i:0;s:4:\"desc\";s:7:\"Capteur\";s:6:\"method\";s:5:\"liste\";s:6:\"format\";a:3:{i:1;s:10:\"Non défini\";i:2;s:13:\"Un processeur\";i:3;s:13:\"2 processeurs\";}}}}';
	$iptc = 'a:32:{s:5:\"2#005\";a:2:{s:3:\"nom\";s:14:\"Nom de l\'objet\";s:6:\"active\";i:0;}s:5:\"2#007\";a:2:{s:3:\"nom\";s:16:\"Statut éditorial\";s:6:\"active\";i:0;}s:5:\"2#010\";a:2:{s:3:\"nom\";s:8:\"Priorité\";s:6:\"active\";i:0;}s:5:\"2#015\";a:2:{s:3:\"nom\";s:9:\"Catégorie\";s:6:\"active\";i:0;}s:5:\"2#020\";a:2:{s:3:\"nom\";s:14:\"Identificateur\";s:6:\"active\";i:0;}s:5:\"2#025\";a:2:{s:3:\"nom\";s:9:\"Mots-clés\";s:6:\"active\";i:1;}s:5:\"2#026\";a:2:{s:3:\"nom\";s:32:\"Code de l\'emplacement du contenu\";s:6:\"active\";i:0;}s:5:\"2#027\";a:2:{s:3:\"nom\";s:31:\"Nom de l\'emplacement du contenu\";s:6:\"active\";i:0;}s:5:\"2#030\";a:2:{s:3:\"nom\";s:14:\"Date de sortie\";s:6:\"active\";i:0;}s:5:\"2#035\";a:2:{s:3:\"nom\";s:15:\"Heure de sortie\";s:6:\"active\";i:0;}s:5:\"2#040\";a:2:{s:3:\"nom\";s:22:\"Instructions spéciales\";s:6:\"active\";i:0;}s:5:\"2#055\";a:2:{s:3:\"nom\";s:16:\"Date de création\";s:6:\"active\";i:1;}s:5:\"2#060\";a:2:{s:3:\"nom\";s:17:\"Heure de création\";s:6:\"active\";i:0;}s:5:\"2#065\";a:2:{s:3:\"nom\";s:9:\"Programme\";s:6:\"active\";i:0;}s:5:\"2#070\";a:2:{s:3:\"nom\";s:20:\"Version du programme\";s:6:\"active\";i:0;}s:5:\"2#075\";a:2:{s:3:\"nom\";s:16:\"Cycle de l\'objet\";s:6:\"active\";i:0;}s:5:\"2#080\";a:2:{s:3:\"nom\";s:6:\"Auteur\";s:6:\"active\";i:1;}s:5:\"2#085\";a:2:{s:3:\"nom\";s:17:\"Titre de l\'auteur\";s:6:\"active\";i:0;}s:5:\"2#090\";a:2:{s:3:\"nom\";s:5:\"Ville\";s:6:\"active\";i:1;}s:5:\"2#092\";a:2:{s:3:\"nom\";s:6:\"Région\";s:6:\"active\";i:0;}s:5:\"2#095\";a:2:{s:3:\"nom\";s:13:\"Province/État\";s:6:\"active\";i:0;}s:5:\"2#100\";a:2:{s:3:\"nom\";s:9:\"Code pays\";s:6:\"active\";i:0;}s:5:\"2#101\";a:2:{s:3:\"nom\";s:4:\"Pays\";s:6:\"active\";i:0;}s:5:\"2#103\";a:2:{s:3:\"nom\";s:28:\"Référence de la transmission\";s:6:\"active\";i:0;}s:5:\"2#105\";a:2:{s:3:\"nom\";s:5:\"Titre\";s:6:\"active\";i:1;}s:5:\"2#110\";a:2:{s:3:\"nom\";s:6:\"Crédit\";s:6:\"active\";i:1;}s:5:\"2#115\";a:2:{s:3:\"nom\";s:6:\"Source\";s:6:\"active\";i:1;}s:5:\"2#116\";a:2:{s:3:\"nom\";s:9:\"Copyright\";s:6:\"active\";i:1;}s:5:\"2#118\";a:2:{s:3:\"nom\";s:7:\"Contact\";s:6:\"active\";i:1;}s:5:\"2#120\";a:2:{s:3:\"nom\";s:11:\"Description\";s:6:\"active\";i:1;}s:5:\"2#122\";a:2:{s:3:\"nom\";s:24:\"Auteur de la description\";s:6:\"active\";i:0;}s:5:\"2#130\";a:2:{s:3:\"nom\";s:15:\"Type de l\'image\";s:6:\"active\";i:0;}}';
	$config_inserts .= ',("galerie_exif_params","' . $exif . '")';
	$config_inserts .= ',("galerie_exif_params_default","' . $exif . '")';
	$config_inserts .= ',("galerie_iptc_params","' . $iptc . '")';
	$config_inserts .= ',("galerie_iptc_params_default","' . $iptc . '")';
	$config_inserts .= ',("galerie_nb_tags","20")';
	$config_inserts .= ',("galerie_nb_rss","20")';
	$config_inserts .= ',("galerie_page_comments","1")';
	$config_inserts .= ',("galerie_page_comments_nb","20")';
	$config_inserts .= ',("galerie_liens","a:1:{i:0;s:33:\"iGalerie:http://www.igalerie.org/\";}")';
	$config_inserts .= ',("galerie_diaporama_resize","0")';
	$config_inserts .= ',("galerie_add_style","")';

	// Activation de fonctionnalités (activation [1] ou non [0]).
	$config_inserts .= ',("user_perso","1")';
	$config_inserts .= ',("galerie_image_hasard","0")';
	$config_inserts .= ',("active_galerie","1")';
	$config_inserts .= ',("active_votes","0")';
	$config_inserts .= ',("active_commentaires","0")';
	$config_inserts .= ',("active_exif","0")';
	$config_inserts .= ',("active_exif_ajout","1")';
	$config_inserts .= ',("active_iptc","0")';
	$config_inserts .= ',("active_iptc_ajout","1")';
	$config_inserts .= ',("active_advsearch","0")';
	$config_inserts .= ',("active_historique","0")';
	$config_inserts .= ',("active_diaporama","0")';
	$config_inserts .= ',("active_rss","1")';
	$config_inserts .= ',("active_tags","0")';
	$config_inserts .= ',("active_liens","0")';

	// Paramètres d'affichage des vignettes des catégories.
	$config_inserts .= ',("vignettes_cat_col","4")';
	$config_inserts .= ',("vignettes_cat_line","3")';
	$config_inserts .= ',("vignettes_cat_mode","compact")';
	$config_inserts .= ',("vignettes_cat_ordre","categorie_date DESC,categorie_nom ASC")';
	$config_inserts .= ',("vignettes_cat_type","")';

	// Paramètres d'affichage des vignettes des images.
	$config_inserts .= ',("vignettes_col","4")';
	$config_inserts .= ',("vignettes_line","3")';
	$config_inserts .= ',("vignettes_ordre","date")';
	$config_inserts .= ',("vignettes_sens","DESC")';

	// Commentaires.
	$config_inserts .= ',("comment_courriel","0")';
	$config_inserts .= ',("comment_siteweb","0")';
	$config_inserts .= ',("comment_antiflood","60")';
	$config_inserts .= ',("comment_samemsg","1")';
	$config_inserts .= ',("comment_maxmsg","1")';
	$config_inserts .= ',("comment_maxmsg_nb","15")';
	$config_inserts .= ',("comment_nourl","1")';
	$config_inserts .= ',("comment_maxurl","2")';

	// Images récentes.
	$config_inserts .= ',("display_recentes","0")';

	// Affichage d'informations sous les vignettes des albums (affichage [1] ou non [0]).
	$config_inserts .= ',("display_cat_nom","1")';
	$config_inserts .= ',("display_cat_nb_images","1")';
	$config_inserts .= ',("display_cat_poids","0")';
	$config_inserts .= ',("display_cat_hits","0")';
	$config_inserts .= ',("display_cat_comments","0")';
	$config_inserts .= ',("display_cat_votes","0")';

	// Affichage d'informations sous les vignettes des images (affichage [1] ou non [0]).
	$config_inserts .= ',("display_img_nom","0")';
	$config_inserts .= ',("display_img_date","0")';
	$config_inserts .= ',("display_img_taille","0")';
	$config_inserts .= ',("display_img_poids","0")';
	$config_inserts .= ',("display_img_hits","0")';
	$config_inserts .= ',("display_img_comments","0")';
	$config_inserts .= ',("display_img_votes","0")';

	// Choix visiteurs (autorisation [1] ou non [0]).
	$config_inserts .= ',("user_nom_images","0")';
	$config_inserts .= ',("user_nom_categories","0")';
	$config_inserts .= ',("user_date","0")';
	$config_inserts .= ',("user_taille","0")';
	$config_inserts .= ',("user_poids","0")';
	$config_inserts .= ',("user_comments","0")';
	$config_inserts .= ',("user_votes","0")';
	$config_inserts .= ',("user_hits","0")';
	$config_inserts .= ',("user_recentes","0")';
	$config_inserts .= ',("user_nb_images","0")';
	$config_inserts .= ',("user_style","0")';
	$config_inserts .= ',("user_vignettes","1")';
	$config_inserts .= ',("user_image_ajust","1")';
	$config_inserts .= ',("user_ordre","0")';

	// Utilisateurs.
	$config_inserts .= ',("users_membres_active","0")';
	$config_inserts .= ',("users_membres_alert","1")';
	$config_inserts .= ',("users_membres_avatars","1")';
	$config_inserts .= ',("users_upload_alert","1")';
	$config_inserts .= ',("users_upload_maxsize","300")';
	$config_inserts .= ',("users_upload_maxwidth","1500")';
	$config_inserts .= ',("users_upload_maxheight","1500")';


	$mysql_requete = 'INSERT IGNORE ' . MYSQL_PREF . 'config(parametre,valeur) VALUES' . substr($config_inserts, 1);
	if (!$mysql->requete($mysql_requete)) {
		return array(0 => '[' . __LINE__ . '] ' . mysql_error(), 1 => drop_tables($mysql));
	}

	// Création d'un index sur la table des tags.
	$mysql_requete = 'CREATE INDEX idx_image_id on ' . MYSQL_PREF . 'tags(image_id)';
	if (!$mysql->requete($mysql_requete)) {
		return array(0 => '[' . __LINE__ . '] ' . mysql_error(), 1 => drop_tables($mysql));
	}

	// Création du premier enregistrement de la table "categories".
	$mysql_requete = 'INSERT IGNORE ' . MYSQL_PREF . 'categories(categorie_chemin) VALUES(".")';
	if (!$mysql->requete($mysql_requete)) {
		return array(0 => '[' . __LINE__ . '] ' . mysql_error(), 1 => drop_tables($mysql));
	}

	// Création des trois premiers groupes.
	$mysql_requete = 'INSERT IGNORE ' . MYSQL_PREF . 'groupes(
			groupe_nom,
			groupe_titre,
			groupe_date_creation,
			groupe_commentaires,
			groupe_votes,
			groupe_recherche_avance,
			groupe_perso,
			groupe_upload,
			groupe_upload_mode,
			groupe_album_pass_mode,
			groupe_newsletter)
			VALUES("admin", "administrateur", "' . time() . '", "1", "1", "1", "1", "0", "direct", "tous", "0"),
				  ("invités", "invité", "' . time() . '", "0", "0", "1", "1", "0", "attente", "aucun", "0"),
				  ("membres", "membre", "' . time() . '", "1", "1", "1", "1", "0", "attente", "aucun", "1")';
	if (!$mysql->requete($mysql_requete)) {
		return array(0 => '[' . __LINE__ . '] ' . mysql_error(), 1 => drop_tables($mysql));
	}

	// Fermeture de la base de données.
	$mysql->fermer();

	return array(0 => __LINE__, 1 => true);
}

function drop_tables($mysql) {
	$drop_tables = 'DROP TABLE IF EXISTS ' . 
		MYSQL_PREF . 'config,' .
		MYSQL_PREF . 'images,' .
		MYSQL_PREF . 'categories,' .
		MYSQL_PREF . 'commentaires,' .
		MYSQL_PREF . 'tags,' .
		MYSQL_PREF . 'users,' .
		MYSQL_PREF . 'groupes,' .
		MYSQL_PREF . 'images_attente,' .
		MYSQL_PREF . 'favoris,' .
		MYSQL_PREF . 'votes';
	$mysql->requete($drop_tables);
	$mysql->fermer();
	return false;
}
?>