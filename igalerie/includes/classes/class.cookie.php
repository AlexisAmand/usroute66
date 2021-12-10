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
 * ========== class.cookie
*/

class cookie {

	var $nom;
	var $expire;
	var $valeur;
	var $path;


	/*
	 *	Constructeur.
	*/
	function cookie($sec = 31536000, $nom = 'galerie_perso', $path = '') {
		$this->nom = $nom;
		$this->path = ($path == '/') ? '/' : $path . '/';
		$this->expire = ($sec) ? time() + $sec : 0;
		if (isset($_COOKIE[$nom])) {
			$this->valeur = unserialize($_COOKIE[$nom]);
		}
	}



	/*
	 *	Valeurs à ajouter au cookie.
	*/
	function ajouter($c, $v) {
		$this->valeur[$c] = $v;
	}



	/*
	 *	Valeurs à effacer du cookie.
	*/
	function effacer($c) {
		unset($this->valeur[$c]);
	}



	/*
	 *	Ecriture du cookie.
	*/
	function ecrire() {
		if ($this->valeur) {
			$valeur = serialize($this->valeur);
			$valeur = ($valeur == 'i:1;') ? '' : $valeur;	// fix IE7.0/Vista
			return @setcookie($this->nom, $valeur, $this->expire, $this->path);
		}
	}



	/*
	 *	Lecture d'une valeur contenue dans le cookie.
	*/
	function lire($c) {
		if (isset($this->valeur[$c])) {
			return $this->valeur[$c];
		} else {
			return FALSE;
		}
	}
}
?>