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

/*
 * ========== class.admin_upload
*/

class upload {

	var $mysql;
	var $config;
	var $rapport;
	var $time;
	var $time_limit;
	var $galerie_dir;
	var $thumbs_prefix;
	var $thumbs_rep;
	var $mysql_categories;
	var $img_rename;
	var $http;
	var $users;
	var $arret;
	var $exif;
	var $iptc;
	var $images_update_exif;
	var $newcat;
	var $iptc_tags;
	var $up_images;
	var $update_pass;


	/*
	 *	Constructeur.
	*/
	function upload($mysql, $config) {

		umask(0);

		// Calcule du temps maximal d'ex�cution du script.
		$this->time_limit = 8;
		if (function_exists('ini_get')) {
			$met = @intval(ini_get('max_execution_time'));
			if (is_int($met)) {
				$met = ($met > 30) ? 30 : $met;
				$this->time_limit = ($met > 1) ? ceil($met / 2) : 8;
			}
		}

		// On initialise certaines propri�t�s.
		$this->mysql = $mysql;
		$this->config = $config;
		$this->galerie_dir = '../' . GALERIE_ALBUMS . '/';
		$this->thumbs_prefix = THUMB_PREF;
		$this->thumbs_rep = THUMB_TDIR . '/';
		$this->time = time();
		$this->images_update_exif = 0;
		$this->iptc_tags = '';
		$this->users = array();
		$this->up_images = FALSE;
		$this->update_pass = array();
		$this->update_pass['cat'] = array();
		$this->update_pass['img'] = array();

		// Initilisation des valeurs du rapport.
		$this->rapport['alb_ajouts'] = array();
		$this->rapport['alb_maj'] = array();
		$this->rapport['cat_rejets'] = array();
		$this->rapport['img_rejets'] = array();
		$this->rapport['erreurs'] = array();
		$this->rapport['img_ajouts'] = 0;

		// On r�cup�re les informations utiles de toutes les 
		// cat�gories enregistr�es dans la base de donn�es.
		$mysql_requete = 'SELECT categorie_chemin,
						categorie_id,
						categorie_poids,
						image_representant_id,
						categorie_images,
						categorie_derniere_modif,
						categorie_pass 
			FROM ' . MYSQL_PREF . 'categories 
			ORDER BY categorie_nom';
		$this->mysql_categories = $this->mysql->select($mysql_requete, 4);
		if ($this->mysql_categories == 'vide') {
			$this->mysql_categories = array();
		}

		// Metadonn�es.
		$this->exif = ($this->config['active_exif_ajout'] && function_exists('read_exif_data')) ? 1 : 0;
		$this->iptc = ($this->config['active_iptc_ajout']) ? 1 : 0;
	}



	/*
	 *	On parcours l'arborescence du r�pertoire des albums � la recherche
	 *	de nouveaux albums et/ou nouvelles images.
	*/
	function recup_albums($dir = '', $nom_cat = '') {

		// On ouvre le r�pertoire.
		if (!$gad = @opendir($this->galerie_dir . $dir)) {
			if (empty($dir)) {
				$dir = 'r�pertoire racine de la galerie (' . $this->galerie_dir . $dir . '/)';
			}
			$this->rapport['erreurs'][] = array($dir, 'impossible d\'acc�der au r�pertoire');
			return FALSE;
		}

		// On initialise les compteurs de sous-r�pertoires, de nombre d'images et de poids.
		$sous_dir_n = 0;
		$cat_infos['nb_images'] = 0;
		$cat_infos['poids'] = 0;
		$cat_infos['poids_inactive'] = 0;

		// On parcours le r�pertoire.
		while (($ent = readdir($gad)) !== FALSE) {

			// Si l'�l�ment est un sous-r�pertoire...
			if (is_dir($this->galerie_dir . $dir . $ent) && $ent != '.' && $ent != '..') {

				// ...et si le nom du sous-r�pertoire est correct...
				if (empty($this->img_rename[$dir . $ent . '/']) && $sub_dir = $this->verif_nom($dir . $ent . '/')) {

					// ...et si le sous-r�pertoire contient un r�pertoire de vignettes...
					if (is_dir($this->galerie_dir . $sub_dir . $this->thumbs_rep)) {

						if (file_exists($this->galerie_dir . $sub_dir . '~#~') && !$this->http) {
							files::suppFile($this->galerie_dir . $sub_dir . '~#~');
							@touch($this->galerie_dir . $sub_dir . '~#~');
							files::suppFile($this->galerie_dir . $sub_dir . '~#~');
						}

						// On m�morise la date de derni�re modification de l'album.
						$last_modif = filemtime($this->galerie_dir . $sub_dir);

						// Traitement pour upload HTTP.
						if ($this->http && $this->http['album'] == $sub_dir) {
							while ($this->mysql_categories[$sub_dir]['categorie_derniere_modif'] == $last_modif) {
								$this->mysql_categories[$sub_dir]['categorie_derniere_modif']++;
							}
						}

						// ...et si l'album est pr�sent dans la base de donn�es...
						if (isset($this->mysql_categories[$sub_dir])) {

							// ...et si la date de derni�re modification est diff�rente...
							if ($this->mysql_categories[$sub_dir]['categorie_derniere_modif'] != $last_modif) {

								// ...alors on UPDATE les images de l'album.
								$images_infos = $this->update_images($sub_dir, $last_modif);
								$cat_infos['nb_images'] += $images_infos['nb_images'];
								$cat_infos['poids'] += $images_infos['poids'];
								$cat_infos['poids_inactive'] += $images_infos['poids_inactive'];
							}

						// ...sinon on tente de r�cup�rer les images qu'il contient.
						} elseif ($images_infos = $this->recup_images($sub_dir)) {
							if ($this->insert_categorie($sub_dir, $ent, $images_infos, $last_modif)) {
								$cat_infos['nb_images'] += $images_infos['nb_images'];
								$cat_infos['poids'] += $images_infos['poids'];
								$cat_infos['poids_inactive'] += $images_infos['poids_inactive'];
							}
						}

					// ...sinon, on scan le r�pertoire � la recherche de sous-r�pertoire(s).
					} elseif ($images_infos = $this->recup_albums($sub_dir, $ent)) {
						$cat_infos['nb_images'] += $images_infos['nb_images'];
						$cat_infos['poids'] += $images_infos['poids'];
						$cat_infos['poids_inactive'] += $images_infos['poids_inactive'];
					}

					// On incr�mente le compteur de sous-r�pertoires.
					$sous_dir_n++;
				}

				// Contr�le du temps d'ex�cution.
				if ((time() - $this->time) > $this->time_limit) {
					$this->arret = 1;
					break;
				}
			}
		}

		// On ferme le r�pertoire.
		closedir($gad);

		// Si le r�pertoire contient des images valides dans ses sous-r�pertoire,
		// soit on l'INSERT, soit on l'UPDATE selon qu'il existe ou non dans la base de donn�es.
		// Puis, on retourne les informations du r�pertoire...
		if ($cat_infos['poids'] || $cat_infos['poids_inactive']) {
			$cat = (empty($dir)) ? '.' : $dir;
			if (isset($this->mysql_categories[$cat])) {
				$this->update_categorie($cat, $cat_infos);
			} else {
				$this->insert_categorie($cat, $nom_cat, $cat_infos);
			}
			if ($dir) {
				return $cat_infos;
			}

		// ...sinon on r�cup�re et renvoie les informations des eventuelles images
		// que contient le r�pertoire, mais seulement s'il ne contient aucun sous-r�pertoire.
		} elseif ($dir && !$sous_dir_n && $images_infos = $this->recup_images($dir)) {
			if (isset($this->mysql_categories[$dir])) {
				$this->update_categorie($dir, $images_infos, filemtime($this->galerie_dir . $dir));
			} else {
				$this->insert_categorie($dir, $nom_cat, $images_infos, filemtime($this->galerie_dir . $dir));
			}
			return $images_infos;
		}

		if (!$dir) {

			// On ajoute les mots-cl�s IPTC � la table des tags.
			if ($this->iptc_tags) {
				$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'tags(tag_id,image_id) VALUES' . substr($this->iptc_tags, 1);
				$this->mysql->requete($mysql_requete);
			}

			// On prot�ge les nouveaux albums et cat�gories par mot de passe si
			// une cat�gorie parente est prot�g�e.
			if ($cat_infos['nb_images']) {
				$cat_pass = array();
				foreach ($this->mysql_categories as $k => $v) {
					if ($k != '.' && !empty($v['categorie_pass'])) {
						$cat_pass[$k] = $v['categorie_pass'];
					}
				}
				if ($cat_pass) {
					foreach ($this->update_pass['cat'] as $path => $e) {
						$p = $path;
						while ($p != './') {
							$p = dirname($p) . '/';
							if (isset($this->update_pass['cat'][$p])) {
								unset($this->update_pass['cat'][$path]);
								break;
							}
						}
					}
					foreach ($this->update_pass['cat'] as $path => $e) {
						$p = dirname($path) . '/';
						if (!empty($cat_pass[$p])) {
							$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories 
								SET categorie_pass = "' . $cat_pass[$p] . '" 
								WHERE categorie_chemin LIKE "' . $path . '%"';
							$this->mysql->requete($mysql_requete);
							$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images 
								SET image_pass = "' . $cat_pass[$p] . '" 
								WHERE image_chemin LIKE "' . $path . '%"';
							$this->mysql->requete($mysql_requete);
						}
					}
					foreach ($this->update_pass['img'] as $path => $e) {
						if (!empty($cat_pass[$path])) {
							$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images 
								SET image_pass = "' . $cat_pass[$path] . '" 
								WHERE image_chemin LIKE "' . $path . '%"';
							$this->mysql->requete($mysql_requete);
						}
					}
				}
			}

			// On ajoute l'id de l'album correspondant � toutes les images de chaque nouvel album.
			if (is_array($this->newcat)) {
				$where = '';
				for ($i = 0; $i < count($this->newcat); $i++) {
					$where .= 'OR categorie_chemin = "' . $this->newcat[$i] . '"';
				}
				$mysql_requete = 'SELECT categorie_id,
										 categorie_chemin
									FROM ' . MYSQL_PREF . 'categories
								   WHERE ' . substr($where, 3);
				$cat_id = $this->mysql->select($mysql_requete);
				for ($i = 0; $i < count($cat_id); $i++) {
					$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images
						SET categorie_parent_id = "' . $cat_id[$i]['categorie_id'] . '"
						WHERE image_chemin LIKE "' . $cat_id[$i]['categorie_chemin'] . '%"';
					if (!$this->mysql->requete($mysql_requete)) {
						$this->rapport['erreurs'][] = array($cat, 'requ�te SQL [' . __LINE__ . '] �chou�e&nbsp;:<br />' . mysql_error());
					}
				}
			}

			// Newsletter pour les membres abonn�s.
			if ($cat_infos['nb_images'] && $this->config['users_membres_active']
			&& (count($this->rapport['alb_ajouts']) + $this->rapport['img_ajouts']) > 0) {

				// On r�cup�re tous les albums prot�g�s.
				$mysql_requete = 'SELECT categorie_chemin,
										 categorie_pass
									FROM ' . MYSQL_PREF . 'categories
								   WHERE categorie_pass IS NOT NULL';
				$cat_temp = $this->mysql->select($mysql_requete, 3);
				if (!is_array($cat_temp)) {
					$cat_temp = array();
				}

				// On r�cup�re tous les groupes qui ont la fonctionnalit� newsletter activ�e.
				$mysql_requete = 'SELECT groupe_id,
										 groupe_album_pass_mode,
										 groupe_album_pass,
										 groupe_newsletter
									FROM ' . MYSQL_PREF . 'groupes
								   WHERE groupe_id > 1
								     AND groupe_newsletter = "1"';
				$groupes = $this->mysql->select($mysql_requete);
				if (is_array($groupes)) {
					for ($i = 0; $i < count($groupes); $i++) {

						// Mots de passe d�v�rouill�s du groupe.
						$group_pass = array();
						if (!empty($groupes[$i]['groupe_album_pass'])) {
							$group_pass = unserialize($groupes[$i]['groupe_album_pass']);
						}

						// On effectue une req�ete pour savoir s'il y a des abonn�s dans ce groupe.
						$mysql_requete = 'SELECT user_login,
												 user_mail
											FROM ' . MYSQL_PREF . 'users
										   WHERE groupe_id = "' . $groupes[$i]['groupe_id'] . '"
											 AND user_id > 1
											 AND user_newsletter = "1"
										     AND user_mail != ""';
						$user_mails = $this->mysql->select($mysql_requete);
						if (is_array($user_mails)) {

							// On pr�pare le message : nouveaux albums.
							$nouveaux_albums = '';
							$nb_albums = count($this->rapport['alb_ajouts']);
							for ($n = 0; $n < $nb_albums; $n++) {

								// On v�rifie les droits sur les �ventuels albums prot�g�s.
								if (isset($cat_temp[$this->rapport['alb_ajouts'][$n][0]])) {
									if ($groupes[$i]['groupe_album_pass_mode'] == 'aucun') {
										continue;
									} elseif ($groupes[$i]['groupe_album_pass_mode'] == 'select') {
										$alb_pass = $cat_temp[$this->rapport['alb_ajouts'][$n][0]];
										if (!in_array($alb_pass, $group_pass)) {
											continue;
										}
									}
								}

								$nom = preg_replace('`^.*?([^/]+)/$`', '$1', $this->rapport['alb_ajouts'][$n][0]);
								$nom = str_replace('_', ' ', $nom);
								$nb_images = $this->rapport['alb_ajouts'][$n][1];
								$s = ($nb_images > 1) ? '%s (%s images)' : '%s (%s image)';
								$nouveaux_albums .= sprintf($s, $nom, $nb_images) . "\n";
							}
							if ($nouveaux_albums) {
								$s = ($nb_albums > 1) ? '%s nouveaux albums ont �t� ajout�s :' : '%s nouvel album a �t� ajout� :';
								$nouveaux_albums = sprintf($s, $nb_albums) . "\n\n" . $nouveaux_albums;
							}

							// On pr�pare le message : albums mis � jour.
							$maj_albums = '';
							$nb_albums = count($this->rapport['alb_maj']);
							for ($n = 0; $n < $nb_albums; $n++) {

								// On v�rifie les droits sur les �ventuels albums prot�g�s.
								if (isset($cat_temp[$this->rapport['alb_maj'][$n][0]])) {
									if ($groupes[$i]['groupe_album_pass_mode'] == 'aucun') {
										continue;
									} elseif ($groupes[$i]['groupe_album_pass_mode'] == 'select') {
										$alb_pass = $cat_temp[$this->rapport['alb_maj'][$n][0]];
										if (!in_array($alb_pass, $group_pass)) {
											continue;
										}
									}
								}

								$nom = preg_replace('`^.*?([^/]+)/$`', '$1', $this->rapport['alb_maj'][$n][0]);
								$nom = str_replace('_', ' ', $nom);
								$nb_images = $this->rapport['alb_maj'][$n][1];
								$s = ($nb_images > 1) ? '%s nouvelles images dans l\'album "%s"' : '%s nouvelle image dans l\'album "%s"';
								$maj_albums .= sprintf($s, $nb_images, $nom) . "\n";
							}

							if (!$nouveaux_albums && !$maj_albums) {
								continue;
							}

							if ($maj_albums) {
								$s = ($nb_albums > 1) ? '%s albums ont �t� mis � jour :' : '%s album a �t� mis � jour :';
								$sep = ($nouveaux_albums) ? "\n\n\n" : '';
								$maj_albums = $sep . sprintf($s, $nb_albums) . "\n\n" . $maj_albums;
							}

							$date = 'Mise � jour du ' . outils::ladate() . '.' . "\n\n";
							$message = $date . $nouveaux_albums . $maj_albums . "\n\n\n";
							$message .= 'Retrouvez toutes ces images dans la galerie http://' . $_SERVER['HTTP_HOST'] . GALERIE_URL . "\n\n";
							$message .= '-- ' . "\n";
							$message .= 'Ce courriel a �t� envoy� automatiquement par iGalerie.';

							// Courriels de tous les abonn�s.
							$courriels = '';
							for ($n = 0; $n < count($user_mails); $n++) {
								if (preg_match('`^' . outils::email_address() . '$`', $user_mails[$n]['user_mail'])) {
									$courriels .= ', ' . $user_mails[$n]['user_mail'];
								}
							}

							// On envoi le mail.
							if ($courriels) {
								$titre = trim(strip_tags($this->config['galerie_titre']));
								$titre = (empty($titre)) ? 'iGalerie' : $titre;
								$from = $titre . ' <igalerie@' . $_SERVER['SERVER_NAME'] . '>';
								outils::send_mail('', '[Newsletter] Nouvelles images disponibles !', $message, $from, substr($courriels, 2));
							}
						}
					}
				}
			}
		}
	}



	/*
	 *	R�cup�re et enregistre dans la bdd les nouvelles images d'un abum,
	 *	et les informations des images modifi�es.
	*/
	function update_images($album, $last_modif) {

		// On r�cup�re les informations de toutes les images de l'album.
		$mysql_requete = 'SELECT image_chemin,
								 image_poids,
								 image_hauteur,
								 image_largeur,
								 image_exif_datetimeoriginal,
								 image_exif_make,
								 image_exif_model,
								 image_date_creation,
								 image_pass,
								 image_visible 
			FROM ' . MYSQL_PREF . 'images 
			WHERE image_chemin LIKE "' . $album . '%"';
		$images = $this->mysql->select($mysql_requete, 4);

		// Mot de passe �ventuel de l'album.
		$pass = '';
		if (is_array($images)) {
			$pass = current($images);
			$pass = $pass['image_pass'];
		}

		$album_infos['nb_images'] = 0;
		$album_infos['poids'] = 0;
		$album_infos['poids_inactive'] = 0;

		// On ouvre le r�pertoire de l'album et on le parcours.
		if (!$gad = @opendir($this->galerie_dir . $album)) {
			$this->rapport['erreurs'][] = array($album, 'impossible d\'acc�der au r�pertoire');
			return FALSE;
		}

		while (($ent = readdir($gad)) !== FALSE) {

			if (is_file($this->galerie_dir . $album . $ent)) {

				// Si l'image est d�j� pr�sente dans la base de donn�es...
				if (is_array($images) && isset($images[$album . $ent])) {

					if (!$this->up_images) {
						continue;
					}

					// ...on enregistre les propri�t�s de l'image...
					$file = $this->galerie_dir . $album . $ent;
					$img_infos = @getimagesize($file);
					$img_poids = round(filesize($file)/1024, 1);

					// ..et �ventuellement ses meta-donn�es Exif.
					$exif_update = 0;
					$exif['valeurs'] = '';
					if (!$images[$album . $ent]['image_exif_datetimeoriginal'] &&
						!$images[$album . $ent]['image_exif_make'] &&
						!$images[$album . $ent]['image_exif_model']) {
						$dc_update = ($images[$album . $ent]['image_date_creation']) ? 0 : 1;
						$exif = $this->get_exif($file, 'update', $dc_update);
						if ($exif['valeurs']) {
							$exif_update = 1;
						}
					}

					// ...on v�rifie si ses propri�t�s (Exif, taille et poids) sont diff�rentes.
					if ($exif_update
					 || $img_infos[1] != $images[$album . $ent]['image_hauteur']
					 || $img_infos[0] != $images[$album . $ent]['image_largeur']
					 || $img_poids != $images[$album . $ent]['image_poids']) {

						// Si c'est le cas, on l'UPDATE
						$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'images SET ' 
								. $exif['valeurs'] . '
								image_poids = "' . $img_poids . '",
								image_hauteur = "' . $img_infos[1] . '",
								image_largeur = "' . $img_infos[0] . '"
							WHERE image_chemin = "' . $album . $ent . '"';
						if ($this->mysql->requete($mysql_requete)) {
							if ($exif_update) {
								$this->images_update_exif++;
							}
						} else {
							$this->rapport['erreurs'][] = array($album . $ent, 'requ�te SQL [' . __LINE__ . '] �chou�e&nbsp;:<br />' . mysql_error());
						}

						// et on met � jour le poids de l'album.
						$poids_add = $img_poids - $images[$album . $ent]['image_poids'];
						if ($images[$album . $ent]['image_visible']) {
							$album_infos['poids'] += $poids_add;							
						} else {
							$album_infos['poids_inactive'] += $poids_add;
						}
					}

				// ...sinon on v�rifie que le fichier est une image valide,
				// et on l'INSERT � la base de donn�es.
				} elseif (empty($this->img_rename[$album . $ent]) && $img_poids = $this->insert_image($album, $ent, $pass)) {
					$album_infos['nb_images']++;
					$album_infos['poids'] += $img_poids;
				}
			}
		}

		// On ferme le r�pertoire.
		closedir($gad);

		// On UPDATE l'album si n�cessaire.
		$this->update_categorie($album, $album_infos, $last_modif);

		// On retourne les informations � updater pour les cat�gories parentes.
		return $album_infos;
	}



	/*
	 *	On UPDATE les informations de la cat�gorie $cat.
	*/
	function update_categorie($cat, $images_infos, $last_modif = 0) {

		// S'il n'y a rien � ajouter, on update seulement la date de derni�re modif.
		if (!$images_infos['poids'] && !$images_infos['poids_inactive']) {
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET 		
					categorie_derniere_modif = "' . $last_modif . '" 
				WHERE categorie_chemin = "' . $cat . '"';

			// Pour ne pas afficher inutilement une ligne dans le rapport.
			$last_modif = 0;

		// Sinon on update toutes les infos.
		} else {

			// Si la cat�gorie est d�sactiv�e ou vide,
			// on choisi un nouveau repr�sentant.
			if ($cat != '.' && 
					($this->mysql_categories[$cat]['categorie_images'] == 0 ||
					 $this->mysql_categories[$cat]['image_representant_id'] == 0)
				) {
				$mysql_requete = 'SELECT image_id FROM ' . MYSQL_PREF . 'images 
					WHERE image_chemin LIKE "' . $cat . '%" AND image_visible = "1" 
					ORDER BY RAND() 
					LIMIT 1';
				$nouveau_representant = $this->mysql->select($mysql_requete, 5);
				$representant = 'image_representant_id = "' . $nouveau_representant . '",';
			} else {
				$representant = '';
			}

			// S'il n'y a aucune image nouvelle, on ne touche pas au repr�sentant et � l'�tat visible.
			if (!$images_infos['nb_images']) {
				$representant = '';
				$visible = '';
			} else {
				$visible = 'categorie_visible = "1",';
			}

			$update_dajout = ($images_infos['nb_images'] > 0) ? 'categorie_dernier_ajout = "' . $this->time . '", ': '';
			$mysql_requete = 'UPDATE ' . MYSQL_PREF . 'categories SET 
					categorie_poids = categorie_poids + "' . $images_infos['poids'] . '",
					categorie_poids_inactive = categorie_poids_inactive + "' . $images_infos['poids_inactive'] . '",
					categorie_images = categorie_images + "' . $images_infos['nb_images'] . '",'
					. $update_dajout
					. $representant
					. $visible 
					. 'categorie_derniere_modif = "' . $last_modif . '"
				WHERE categorie_chemin = "' . $cat . '"';
		}
		if ($this->mysql->requete($mysql_requete)) {
			if ($last_modif) {
				$this->rapport['alb_maj'][] = array($cat, $images_infos['nb_images'], outils::poids($images_infos['poids'] + $images_infos['poids_inactive']));
			}
		} else {
			$this->rapport['erreurs'][] = array($cat, 'requ�te SQL [' . __LINE__ . '] �chou�e&nbsp;:<br />' . mysql_error());
		}
	}



	/*
	 *	Ajout de categories et d'albums � la base de donn�es.
	*/
	function insert_categorie($cat, $nom_cat, $images_infos, $last_modif = 0) {

		// On s�lectionne au hasard un repr�sentant de la categorie.
		$representant = 'SELECT image_id FROM ' . MYSQL_PREF . 'images 
			WHERE image_chemin LIKE "' . $cat . '%" 
			ORDER BY RAND() 
			LIMIT 1';

		// On enregistre la cat�gorie.
		$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'categories (
				categorie_chemin,
				categorie_nom,
				categorie_poids,
				image_representant_id,
				categorie_images,
				categorie_dernier_ajout,
				categorie_derniere_modif,
				categorie_date) VALUES ("'
				. $cat . '","'
				. str_replace('_', ' ', $nom_cat) . '","'
				. $images_infos['poids'] . '","'
				. $this->mysql->select($representant, 5) . '","'
				. $images_infos['nb_images'] . '","'
				. $this->time . '","'
				. $last_modif . '","'
				. $this->time . '")';
		if ($this->mysql->requete($mysql_requete)) {

			// Cat�gorie � updater pour mot de passe �ventuel.
			if (isset($this->update_pass['img'][$cat])) {
				unset($this->update_pass['img'][$cat]);
			}
			$this->update_pass['cat'][$cat] = 1;

			if ($last_modif) {
				$this->rapport['alb_ajouts'][] = array($cat, $images_infos['nb_images'], outils::poids($images_infos['poids']));
				$this->newcat[] = $cat;
			}
			return TRUE;
		} else {
			$this->rapport['erreurs'][] = array($cat, 'requ�te SQL [' . __LINE__ . '] �chou�e&nbsp;:<br />' . mysql_error());
			return FALSE;
		}
	}



	/*
	 *	On v�rifie si une image est valide,
	 *	puis on l'INSERT dans la base de donn�es.
	 *	En cas de succ�s, on renvoie le poids de l'image.
	 *	Si l'image n'est pas valide, on l'ajoute au rapport.
	*/
	function insert_image($album, $image, $pass = '') {

		// Upload HTTP : on ajoute ques les images envoy�es.
		if ($this->http && !in_array($image, $this->http['images'])) {
			return;
		}

		// On d�finit le fichier a tester.
		$file = $this->galerie_dir . $album . $image;

		// Si l'�l�ment est une image...
		if ($img_infos = @getimagesize($file, $gis_infos)) {

			// ...et si l'image est au format GIF, JPEG ou PNG...
			if ($img_infos[2] == 1 || $img_infos[2] == 2 || $img_infos[2] == 3) {

				// ...et si le nom de l'image est correct...
				if ($new_img = $this->verif_nom($image, 'img', $album)) {

					$image_nom = preg_replace('`(.+)\..+$`', '$1', str_replace('_', ' ', $image));
					$image_desc = '';
					$image_tags = '';

					// ...on cr�e le r�pertoire de vignettes s'il n'existe pas.
					$thumbs_dir = $this->galerie_dir . $album . '/' . $this->thumbs_rep;
					if (!is_dir($thumbs_dir)) {
						files::createDir($thumbs_dir);
					}

					// ...on r�cup�re le poids de l'image.
					$img_poids = round(filesize($this->galerie_dir . $album . $new_img)/1024, 1);

					// ...on r�cup�re certaines informations Exif.
					$exif = $this->get_exif($this->galerie_dir . $album . $new_img);

					// ...on r�cup�re certaines informations IPTC.
					$iptc = $this->get_iptc($gis_infos);
					if ($iptc['nom']) {
						$image_nom = $iptc['nom'];
					}
					if ($iptc['desc']) {
						$image_desc = $iptc['desc'];
					}
					if ($iptc['tags']) {
						for ($i = 0; $i < count($iptc['tags']); $i++) {
							$iptc['tags'][$i] = str_replace('"', '', $iptc['tags'][$i]);
							$iptc['tags'][$i] = outils::protege_mysql($iptc['tags'][$i], $this->mysql->lien);
							$iptc['tags'][$i] = htmlentities($iptc['tags'][$i]);
						}
						sort($iptc['tags']);
						$image_tags = ',' . implode(',', $iptc['tags']) . ',';
					}

					// Informations utilisateur.
					$user_id = 1;
					if (isset($this->users[$album . $image]['user_id'])) {
						$user_id = $this->users[$album . $image]['user_id'];
						if (!empty($this->users[$album . $image]['image_nom'])) {
							$image_nom = $this->users[$album . $image]['image_nom'];
						}
						if (!empty($this->users[$album . $image]['image_desc'])) {
							$image_desc = $this->users[$album . $image]['image_desc'];
						}
					}

					// ID de l'album.
					$categorie_id = 0;
					if (isset($this->mysql_categories[$album])) {
						$categorie_id = $this->mysql_categories[$album]['categorie_id'];
					}

					// ...on l'ajoute � la base de donn�es.
					$pass_champ = ($pass) ? ', image_pass': '';
					$pass_valeur = ($pass) ? '","' . $pass . '"' : '"';
					$mysql_requete = 'INSERT INTO ' . MYSQL_PREF . 'images (
						categorie_parent_id,
						user_id,
						image_chemin,
						image_nom,
						image_description,
						image_tags,
						image_date,
						image_date_creation,'
						. $exif['champs'] . '
						image_poids,
						image_hauteur,
						image_largeur'
						. $pass_champ . ') VALUES ("'
						. $categorie_id . '","'
						. $user_id . '","'
						. $album . $new_img . '","'
						. outils::protege_mysql($image_nom, $this->mysql->lien) . '","'
						. outils::protege_mysql($image_desc, $this->mysql->lien) . '","'
						. $image_tags . '","'
						. $this->time . '",'
						. $exif['date_creation'] . ',"'
						. $exif['valeurs']
						. $img_poids . '","'
						. $img_infos[1] . '","'
						. $img_infos[0]
						. $pass_valeur . ')';
					if ($this->mysql->requete($mysql_requete)) {
						$this->rapport['img_ajouts']++;
					} else {
						$this->rapport['erreurs'][] = array($album . $new_img, 'requ�te SQL [' . __LINE__ . '] �chou�e&nbsp;:<br />' . mysql_error());
					}

					// Tags pour la table des tags.
					if ($image_tags) {
						$mysql_requete = 'SELECT image_id
										    FROM ' . MYSQL_PREF . 'images
										   WHERE image_chemin = "' . $album . $new_img . '"';
						$image_id = $this->mysql->select($mysql_requete, 5);
						for ($i = 0; $i < count($iptc['tags']); $i++) {
							$this->iptc_tags .= ',("' . $iptc['tags'][$i] . '","' . $image_id . '")';
						}
					}

					// Album � updater pour mot de passe �ventuel.
					$this->update_pass['img'][$album] = 1;

					// On retourne le poids de l'image.
					return $img_poids;

				// ...sinon on modifie la date de derni�re modification du r�pertoire
				// de fa�on � ce qu'il soit v�rifi� la prochaine fois.
				} else {
					@touch($this->galerie_dir . $album . '~#~');
				}

			// ...sinon on ajoute l'image rejet�e au rapport.
			} else {
				$this->rapport['img_rejets'][] = array($image, $album, 'l\'image n\'est pas au format JPEG, GIF ou PNG.');
				@touch($this->galerie_dir . $album . '~#~');
			}
		}
	}



	/*
	 *	R�cup�ration des m�ta-donn�es IPTC.
	*/
	function get_iptc($infos) {
		$iptc_return = array();
		$iptc_return['nom'] = '';
		$iptc_return['desc'] = '';
		$iptc_return['tags'] = '';
		if ($this->iptc) {
			if (is_array($infos)) {
				$data = @iptcparse($infos['APP13']);
				if (is_array($data)) {
					if (!empty($data['2#105'][0])) {
						$iptc_return['nom'] = trim($data['2#105'][0]);
					}
					if (!empty($data['2#120'][0])) {
						$iptc_return['desc'] = trim($data['2#120'][0]);
					}
					if (!empty($data['2#025'])) {
						$iptc_return['tags'] = $data['2#025'];
					}
				}
			}
		}
		return $iptc_return;
	}



	/*
	 *	R�cup�ration des m�ta-donn�es Exif.
	*/
	function get_exif($img, $method = '', $dc_update = 1) {
		$exif_return = array();
		$exif_return['champs'] = '';
		$exif_return['valeurs'] = '';
		$exif_return['date_creation'] = 0;
		if ($this->exif && (strtolower(substr($img, -4)) == '.jpg' || strtolower(substr($img, -5)) == '.jpeg')) {
			$exif_data = @read_exif_data($img, 'ANY_TAG', true, false);
			if ($exif_data) {
				if (isset($exif_data['EXIF']['DateTimeOriginal']) || isset($exif_data['IFD0']['DateTime'])) {
					$datetime = (isset($exif_data['EXIF']['DateTimeOriginal'])) ? $exif_data['EXIF']['DateTimeOriginal'] : $exif_data['IFD0']['DateTime'];
					if (preg_match('`(\d{4}):(\d{2}):(\d{2}) (\d{2}):(\d{2}):(\d{2})`', $datetime, $matches)) {
						$date = @mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
						if ($date !== false
						 && $date !== -1
						 && $datetime != '0000:00:00 00:00:00'
						 && $txtdate = @strftime('%d/%m/%Y %H:%M:%S', $date)) {
							if (preg_match('`^\d{5,10}$`', $date)) {
								$exif_return['date_creation'] = $date;
								if ($method == 'update') {
									$exif_return['valeurs'] .= 'image_exif_datetimeoriginal = "' . $date . '",';
								} else {
									$exif_return['champs'] .= 'image_exif_datetimeoriginal,';
									$exif_return['valeurs'] .= $date . '","';
								}
							} else {
								$exif_return['date_creation'] = '';
							}
						}
					}
				}
				if (isset($exif_data['IFD0']['Make'])) {
					if ($method == 'update') {
						$exif_return['valeurs'] .= 'image_exif_make = "' . $exif_data['IFD0']['Make'] . '",';
					} else {
						$exif_return['champs'] .= 'image_exif_make,';
						$exif_return['valeurs'] .= $exif_data['IFD0']['Make'] . '","';
					}
				}
				if (isset($exif_data['IFD0']['Model'])) {
					if ($method == 'update') {
						$exif_return['valeurs'] .= 'image_exif_model = "' . $exif_data['IFD0']['Model'] . '",';
					} else {
						$exif_return['champs'] .= 'image_exif_model,';
						$exif_return['valeurs'] .= $exif_data['IFD0']['Model'] . '","';
					}
				}
			}
		}
		if ($dc_update && $method == 'update' && $exif_return['date_creation']) {
			$exif_return['valeurs'] = 'image_date_creation = "' . $exif_return['date_creation'] . '", ' . $exif_return['valeurs'];
		}
		return $exif_return;
	}


	
	/*
	 *	R�cup�re et renvoie les informations des images d'un album.
	*/
	function recup_images($dir) {

		// On ouvre le r�pertoire-album.
		if (!$gad = @opendir($this->galerie_dir . $dir)) {
			$this->rapport['erreurs'][] = array($dir, 'impossible d\'acc�der au r�pertoire');
			return FALSE;
		}

		// On r�cup�re le chemin de toutes les images
		// de la cat�gorie enregistr�es dans la base de donn�es,
		// ceci afin d'�viter d'ajouter des images d�j� existantes,
		// donc de cr�er des doublons.
		$mysql_requete = 'SELECT image_chemin
							FROM ' . MYSQL_PREF . 'images 
						   WHERE image_chemin LIKE "' . $dir  . '%" 
						ORDER BY image_chemin';
		$mysql_images_cat = $this->mysql->select($mysql_requete, 1);
		if ($mysql_images_cat == 'vide') {
			$mysql_images_cat = array();
			$mysql_images_cat['image_chemin'] = array();
		} elseif (!is_dir($this->galerie_dir . $dir . $this->thumbs_rep)) {
			files::createDir($this->galerie_dir . $dir . $this->thumbs_rep);
		}

		// On initialise les compteurs d'images et de poids.
		$image_infos['nb_images'] = 0;
		$image_infos['poids'] = 0;
		$image_infos['poids_inactive'] = 0;

		// On parcours le r�pertoire.
		while ($ent = readdir($gad)) {
			if (
			    !in_array($dir . $ent, $mysql_images_cat['image_chemin']) 
			 && is_file($this->galerie_dir . $dir . $ent) 
			 && empty($this->img_rename[$dir . $ent]) 
			 && $img_poids = $this->insert_image($dir, $ent)
			   ) {
				$image_infos['nb_images']++;
				$image_infos['poids'] += $img_poids;
			}
		}

		// On ferme le r�pertoire.
		closedir($gad);

		// Si le r�pertoire contient des images valides,
		// on renvoie les informations de ces images...
		if ($image_infos['nb_images']) {
			return $image_infos;

		// ...sinon on ajoute l'information au rapport.
		} elseif (!$mysql_images_cat['image_chemin']) {
			$this->rapport['cat_rejets'][] = array($dir, 'Le r�pertoire ne contient aucune image valide.');
		}
	}



	/*
	 *	V�rifie le nom d'un fichier ou d'un r�pertoire
	 *	(longueur et caract�res sp�ciaux).
	*/
	function verif_nom($s, $e = '', $name = '', $b = 1) {

		// On v�rifie si le nom est trop long.
		$pattern = ($e == 'img') ? '`([^-_a-z0-9.])`i' : '`([^-_a-z0-9/])`i';
		if (strlen($s) > 200) {
			$cause = 'nom trop long';
			if ($e == 'img') {
				$this->rapport['img_rejets'][] = array($s, $name, $cause);
			} else {
				$this->rapport['cat_rejets'][] = array($s, $cause);
			}
			return FALSE;

		// On v�rifie l'extension dans le cas d'une image.
		} elseif ($e == 'img' && !preg_match('`\.(jpe?g|gif|png)$`i', $s)) {
			$this->rapport['img_rejets'][] = array($s, $name, 'extension incorrecte');
			return FALSE;

		// On v�rifie s'il y a des caract�res sp�ciaux.
		} elseif (preg_match($pattern, $s)) {

			// On renomme l'�l�ment.
			$new_s = strtr($s, '����������������������ڟ��ъ����������������������������', 
							   'EEEEAAAAAAIIIIOOOOOUUUUYYCNSZeeeeaaaaaaiiiiooooouuuuyycnsz');
			$new_s = preg_replace($pattern, '_', $new_s);
			$path = $this->galerie_dir . $name;
			if (files::rename($path . $s, $path . $new_s)) {

					// On renomme la vignette si l'�l�ment est une image et qu'elle est pr�sente.
					$path = $this->galerie_dir . $name . $this->thumbs_rep . $this->thumbs_prefix;
					if (file_exists($path . $s)) {
						if (!files::rename($path . $s, $path . $new_s)) {
							$this->rapport['erreurs'][] = array($name . $this->thumbs_rep . $this->thumbs_prefix . $s, 'renommage de la vignette impossible');
							return FALSE;
						}
					}
			} else {
				$this->rapport['erreurs'][] = array($name . $s, 'renommage impossible');
				return FALSE;
			}

			$this->img_rename[$name . $new_s] = 1;
			return $new_s;

		// Tout va bien !
		} else {
			return $s;
		}
	}
}
?>