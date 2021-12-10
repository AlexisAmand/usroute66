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
 * ========== class.files
*/

class files {

	function createDir($f) {

		umask(0);

		files::chmodDir(dirname($f));

		if (mkdir($f, 0775)) {
			files::chmodDir($f);
			return true;

		} else {
			return false;
		}

	}

	function suppFile($f) {

		umask(0);

		files::chmodFile($f);

		if (is_dir($f)) {
			if (@rmdir($f)) {
				return true;
			} else {
				return false;
			}
		}

		if (is_file($f)) {
			return @unlink($f);
		}

	}

	function rename($f, $new_f) {

		umask(0);

		files::chmodFile($f);

		return @rename($f, $new_f);

	}

	function copie($f, $new_f) {

		umask(0);

		files::chmodFile($f);
		files::chmodDir(dirname($new_f));

		return copy($f, $new_f);

	}

	function deplace($f, $new_f) {

		umask(0);

		files::chmodDir(dirname($new_f));

		return files::rename($f, $new_f);

	}

	function chmodDir($f, $r = 0) {

		static $b = 0; $b++;
		$b = ($r) ? $b : 1;
		$b = ($b > 99) ? 0 : $b;

		if (empty($f) || !is_dir($f)) {
			return false;
		}

		if (files::chmodThis($f)) {
			return true;

		} elseif ($b) {
			files::chmodDir(dirname($f), 1);
		}

		return files::chmodThis($f);

	}

	function chmodFile($f) {

		files::chmodDir(dirname($f));

		return files::chmodThis($f);

	}

	function chmodThis($f) {

		umask(0);

		if (is_dir($f)) {
			if (files::chmodTest($f)) {
				return true;
			} else {
				@chmod($f, 0775);
			}

			if (files::chmodTest($f)) {
				return true;
			} else {
				@chmod($f, 0777);
			}
		}

		if (is_file($f)) {
			@chmod($f, 0664);

			if (files::chmodTest($f)) {
				return true;
			} else {
				@chmod($f, 0666);
			}

			if (files::chmodTest($f)) {
				return true;
			} else {
				@chmod($f, 0777);
			}
		}

		return files::chmodTest($f);

	}

	function chmodTest($f) {

		if (@is_writable($f) 
		 && @is_readable($f)) {
			return true;
		}

		return false;

	}
}
?>