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
 * ========== class.connexion
*/

class connexion {

	var $lien;
	var $con;
	var $erreur;
	var $test;
	var $requetes;



	/*
	 *	Constructeur.
	 *	Connexion au serveur et sélection de la base de données.
	*/
	function connexion($serv, $user, $pass, $base, $test = 0) {
		$this->test = $test;
		$this->lien = @mysql_connect($serv, $user, $pass);
		if ($this->lien) {
			if (@mysql_select_db($base, $this->lien)) {
				$this->con = 1;
			} else {
				$this->erreur(2);
			}
		} else {
			$this->erreur(1);
		}
		$this->requetes = array();
	}



	/*
	 *	Requetes SELECT.
	 *
	 *	$r=0 : renvoi un tableau indexé sur chaque ligne.
	 *	$r=1 : renvoi un tableau indexé sur chaque champs.
	 *	$r=11: renvoi un tableau indexé sur chaque champs,
		       mais pour une seule ligne.
	 *	$r=2 : renvoi un tableau indexé sur chaque valeur
	 *	       (uniquement s'il n'y a qu'un champ).
	 *	$r=3 : renvoi un tableau indexé sur chaque première valeur
	 *	       (uniquement s'il n'y a que deux champ).
	 *	$r=4 : renvoi un tableau indexé sur chaque première valeur,
	 *	       contenant un tableau de toutes les valeurs.
	 *	       (exige au minimum deux champs)
	 *	$r=5 : renvoi une valeur recherchée.
	*/
	function select($query, $r = 0) {
		global $_MYSQL;
		$this->requetes[] = $query;

		$select = @mysql_query($query, $this->lien);
		if (isset($_MYSQL['nb_requetes'])) {
			$_MYSQL['nb_requetes']++;
		}

		// Renvoi FALSE si une erreur est survenue.
		if (!$select) {
			$this->requetes[] = '<strong style="color:red">[ERREUR]</strong> ' . mysql_error();
			return FALSE;
		}

		// On place tout dans un tableau.
		$result = array();
		while ($ligne = mysql_fetch_array($select)) {
			$result[] = $ligne;
		}

		// Renvoi 'vide' si aucun résultat.
		if (empty($result)) {
			return 'vide';
		}

		// Renvoi un format au choix.
		switch ($r) {
			case 1 :
				foreach ($result[0] as $k => $v) {
					if (is_string($k)) {
						$champs[$k] = array();
					}
				}
				for ($i = 0; $i < count($result); $i++) {
					foreach ($champs as $k => $v) {
						array_push($champs[$k], $result[$i][$k]);
					}
				}
				return $champs;
			case 11 :
				foreach ($result[0] as $k => $v) {
					if (is_string($k)) {
						$champs[$k] = array();
					}
				}
				for ($i = 0; $i < count($result); $i++) {
					foreach ($champs as $k => $v) {
						$champs[$k] = $result[$i][$k];
					}
				}
				return $champs;
			case 2 :
				if (isset($result[0][1])) {
					return $result;
				} else {
					for ($i = 0; $i < count($result); $i++) {
						$valeurs[$result[$i][0]] = 1;
					}
					return $valeurs;
				}
			case 3 :
				if (isset($result[0][2]) || !isset($result[0][1])) {
					return $result;
				} else {
					for ($i = 0; $i < count($result); $i++) {
						$valeurs[$result[$i][0]] = $result[$i][1];
					}
					return $valeurs;
				}
			case 4 :
				if (!isset($result[0][1])) {
					return $result;
				} else {
					for ($i = 0; $i < count($result); $i++) {
						$valeurs[$result[$i][0]] = $result[$i];
					}
					return $valeurs;
				}
			case 5 :
				return $result[0][0];
			default :
				return $result;
		}
	}



	/*
	 *	Effectue des requêtes autres que SELECT.
	*/
	function requete($query) {
		global $_MYSQL;
		$this->requetes[] = $query;

		// Envoie de la requête.
		$resp = @mysql_query($query, $this->lien);
		if (isset($_MYSQL['nb_requetes'])) {
			$_MYSQL['nb_requetes']++;
		}

		if (!$resp) {
			$this->requetes[] = '<strong style="color:red">[ERREUR]</strong> ' . mysql_error();
			return FALSE;
		} else {
			return TRUE;
		}
	}



	/*
	 *	Fermeture de la connexion.
	*/
	function fermer() {
		mysql_close($this->lien);
	}



	/*
	 *	Affichage d'un message d'erreur.
	*/
	function erreur($code) {
		if ($this->test) {
			$this->erreur = $code;
		} else {
			if ($code == 1) {
				die('mysql: impossible de se connecter au serveur');
			}
			if ($code == 2) {
				die('mysql: base de données introuvable');
			}
			exit;
		}
	}

}
?>