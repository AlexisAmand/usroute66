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
?>
		<?php $tpl->getFooter('<tr id="pied"><td colspan="2"><p>%s</p>%s</td></tr>'); ?>

	</table>
	
</div>

<script type="text/javascript">
//<![CDATA[
var style_dir = "<?php $tpl->getInfo('style_dir'); ?>";
var galerie_path = "<?php $tpl->getGaleriePath(); ?>";
var galerie_file = "<?php $tpl->getGalerieFile(); ?>";

// On cache l'option de redimensionnement auto pour 
// les navigateurs autres que ceux basés sur Gecko.
var img_ajust = document.getElementById('img_auto');
if (img_ajust && navigator.userAgent.search(/Gecko/) == -1) {
	img_ajust.style.display = 'none';
}

// Parties repliables.
var div_partjs = document.getElementsByTagName('div');
initDisplay();
for (var i = 0; i < div_partjs.length; i++) {
	var cl = div_partjs[i].className;
	if (cl && cl.search(/partjs/gi) != -1) {
		div_partjs[i].getElementsByTagName('a')[0].onclick = function() {
				if (this.id == 'ldp_navigation') { cacher_montrer('navigation', this); }
				if (this.id == 'ldp_hasard') { cacher_montrer('hasard', this); }
				if (this.id == 'ldp_perso') { cacher_montrer('perso', this); }
				if (this.id == 'ldp_stats') { cacher_montrer('stats', this); }
				if (this.id == 'ldp_tags') { cacher_montrer('tags', this); }
				if (this.id == 'ldp_exif') { cacher_montrer('exif', this); }
				if (this.id == 'ldp_iptc') { cacher_montrer('iptc', this); }
				if (this.id == 'ldp_liens') { cacher_montrer('liens', this); }
				if (this.id == 'ldp_membres') { cacher_montrer('membres', this); }
			};
	}
}
var comments = document.getElementById('ldp_comments');
if (comments) {
	comments.onclick = function() { cacher_montrer('comments', this); };
}
var enlarge = document.getElementById('enlarge');
if (enlarge) {
	enlarge.onclick = function() { cacher_montrer('enlarge', this); };
}
//]]>
</script>
<?php if (!GALERIE_INTEGRATED) : ?>
</body>


</html>
<?php else : ?>
<!-- </iGalerie> -->
<?php endif; ?>