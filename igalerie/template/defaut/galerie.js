/*
 *	Initialisation.
*/
if (window.addEventListener) {
  window.addEventListener('load', init, false);
} else if (window.attachEvent) {
  window.attachEvent('onload', init);
}
function init() {
	document.getElementById('igalerie').style.height = '';

	style_vignettes();

	autoResize();

	if (typeof preview == 'number') {
		document.getElementById('comment').focus();
	}

	textfocus('adv_search_stext');
	textfocus('section_pass');

	// Préchargement de certaines images.
	var preload = new Array('enlarge_off','enlarge_on',
							'montrer','cacher',
							'star_demi','star_empty','star_full');
	document.preload_images = [];
	for (var i = 0; i < preload.length; i++) {
		document.preload_images[i] = new Image();
		document.preload_images[i].src = style_dir + '/' + preload[i] + '.png';
	}

	// Note.
	vote();
}
function textfocus(id) {
	var e = document.getElementById(id);
	if (e) {
		e.focus();
	}
}



/*
  *	Utilisateurs : upload.
*/
function display_upload(n) {
	e = document.getElementById(n);
	if (!e) {
		return;
	}
	if (e.style.display == 'block') {
		e.style.display = 'none';
	} else {
		e.style.display = 'block';
		e.focus();
	}
}



/*
 *	Vote utilisateur.
*/
var stars, stars_defaut, http;
function vote() {
	if (document.getElementById('image') && document.getElementById('note_user')) {
		stars = document.getElementById('note_user').getElementsByTagName('img');
		stars_defaut = [];
		for (var i = 0; i < stars.length; i++) {
			stars_defaut[i] = stars[i].src;
			stars[i].setAttribute('cssText', '');
			stars[i].setAttribute('id', 'star_' + (i+1));
			stars[i].onmouseover = function() { starOver(this); };
			stars[i].onmouseout = function() { starOut(this); };
			stars[i].onclick = function() { starClick(this); };
		}
	}
}
function starOver(star) {
	for (var i = 0; i < 5; i++) {
		stars[i].src = stars[i].src.replace(/full/, 'empty');
	}
	var id = star.id.replace(/star_/, '');
	for (var i = 0; i < id; i++) {
		stars[i].src = stars[i].src.replace(/empty/, 'full');
	}
	star.style.cursor = 'pointer';
}
function starOut(star) {
	var id = star.id.replace(/star_/, '');
	for (var i = 0; i < 5; i++) {
		stars[i].src = stars_defaut[i];
	}
}
function starClick(star) {
	for (var i = 0; i < 5; i++) {
		stars_defaut[i] = stars[i].src;
	}
	var note = star.id.replace(/star_/, '');
	var retour = 'note moyenne :<span id="note_stars">!</span><span>(! - !)</span>';
	http = createRequestObject();
	http.open('post', galerie_path + '/vote.php', true);
	http.onreadystatechange = handleAJAXReturn;
	http.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	http.send('note=' + note + '&img=' + image_id + '&retour=' + retour + '&styledir=' + style_dir);
}
function createRequestObject() {
    if (window.XMLHttpRequest) {
        http = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        http = new ActiveXObject('Microsoft.XMLHTTP');
    }
    return http;
}
function handleAJAXReturn() {
    if (http.readyState == 4) {
		if (http.status == 200) {
			var ok = http.responseText;
			if (ok == '-1') {
				alert('Votre vote n\'a pas été pris en compte car une erreur Ajax s\'est produite.');
			} else if (ok == '0') {
				alert('Votre vote n\'a pas été pris en compte car une erreur SQL s\'est produite.');
			} else if (ok != '') {
				document.getElementById('stats_note').innerHTML = http.responseText;
			}
		} else {
			alert('Votre vote n\'a pas été pris en compte car une erreur HTTP s\'est produite: ' + http.status);
		}
    }
}



/*
 *	Redimensionnement de l'image.
*/
var auto_resize,img_auto_resize,img_width,img_height,img_space,
    img_ratio,fixed_width,fixed_height,ilar,pointer;

function autoAjust() {
	if (auto_resize) {
		var img = document.getElementById('img');
		if (img) {
			autoResizeImage(img);
			autoResizeImage(img);
		}
		imageResizeMessage(img);
	}
}

function imageResize(img) {
	autoResizeValues();
	var body_width = document.body.offsetWidth;
	var largeur = img.width + img_space;
	if (largeur > body_width) {
		img.width = img.width - (largeur - body_width);
	}
	if (img.width > img_width) {
		img_auto_resize = 0;
		autoResizeImage(img);
		img_auto_resize = 1;
	}
	img.height = img.width / img_ratio;
}

function autoResizeValues() {
	var img = document.getElementById('img');
	var img_pos_left = getPageOffsetLeft(img);
	var page_width = document.getElementById('ensemble').offsetWidth;
	var img_pos_right = page_width - img_pos_left - img.width;
	img_space = img_pos_left + img_pos_right;
}

function getPageOffsetLeft(el) {
	return el.offsetLeft + (el.offsetParent ? getPageOffsetLeft(el.offsetParent) : 0);
}

function autoResize() {
	var image_r = document.getElementById('image_r');
	if (!image_r || (navigator.userAgent.search(/Gecko/) == -1 && img_auto_resize == 2) || img_gd_resize) {
		return;
	}
	var img = image_r.getElementsByTagName('img')[0];
	if (img) {
		if (!ilar) {
			ilar = (img_auto_resize) ? 1 : 2;
		}
		img_ratio = img_width / img_height;
		if (img_auto_resize) {
			auto_resize = 1;
			imageResizeMessage(img);
			autoResizeValues();
			img_auto_resize = (img_auto_resize == 2) ? 1 : 0;
		} else {
			auto_resize = 0;
			fixed_width = img.width;
			fixed_height = img.height;
			imageResizeMessage(img);
		}
		img.onclick = function () { autoResizeImage(this); };
	}
	window.onresize = autoAjust;
}

function imageResizeMessage(img) {
	var msg = document.getElementById('image_r_msg');
	var e = document.getElementById('ensemble');
	if (img.width == img_width) {
		msg.style.display = 'none';
		img.setAttribute('title', '');
		e.style.width = '';
	} else {
		msg.style.width = img.width + 'px';
		msg.style.display = '';
		img.setAttribute('title', 'Cliquez pour voir l\'image en taille réelle.');
		img.style.cursor = 'pointer';
		pointer = 1;
		if (ilar < 2) {
			e.style.width = document.body.offsetWidth + 'px';
		}
	}
}

function autoResizeImage(img) {
	if (auto_resize) {
		if (img_auto_resize) {
			imageResize(img);
			img_auto_resize = 0;
		} else {
			img.width = img_width;
			img.height = img_height;
			img.setAttribute('style', '');
			img_auto_resize = 1;
		}
		imageResizeMessage(img);
	} else {
		if (img_auto_resize) {
			img_auto_resize = 0;
			img.width = fixed_width;
			img.height = fixed_height;
			imageResizeMessage(img);
		} else {
			img_auto_resize = 1;
			img.width = img_width;
			img.height = img_height;
			imageResizeMessage(img);
		}
	}
	if (pointer) {
		img.style.cursor = 'pointer';
	}
}




/*
 *	Vérification des formulaires.
*/
function comment_verif(f) {
	var aut = f.elements.auteur.value;
	var msg = f.elements.message.value;
	if (aut.search(/^\W*$/gi) == -1) {
		if (msg.search(/^\s*$/gi) == -1) {
			return true;
		} else {
			alert('Votre message est vide !');
			f.elements.message.focus();
			return false;
		}
	} else {
		alert('Vous devez entrer votre nom ou un pseudo.');
		f.elements.auteur.focus();
		return false;
	}
}
function advsearch_verif(f) {
	if (document.getElementById('adv_search_taille') &&
		document.getElementById('adv_search_poids') &&
		document.getElementById('adv_search_width_start') &&
	    document.getElementById('adv_search_width_end') &&
		document.getElementById('adv_search_height_start') &&
		document.getElementById('adv_search_height_end') &&
		document.getElementById('adv_search_poids_start') &&
		document.getElementById('adv_search_poids_end')) {
		if (document.getElementById('adv_search_taille').checked == true) {
			if (document.getElementById('adv_search_width_start').value.search(/^\d{0,6}$/) == -1 ||
			    document.getElementById('adv_search_width_end').value.search(/^\d{0,6}$/) == -1 ||
				document.getElementById('adv_search_height_start').value.search(/^\d{0,6}$/) == -1 ||
				document.getElementById('adv_search_height_end').value.search(/^\d{0,6}$/) == -1) {
				alert('Vous devez entrer un nombre entier pour les limites de dimensions.');
				return false;
			}
		}
		if (document.getElementById('adv_search_poids').checked == true) {
			if (document.getElementById('adv_search_poids_start').value.search(/^\d{1,12}(\.\d{1,6})?$/) == -1 ||
				document.getElementById('adv_search_poids_end').value.search(/^\d{1,12}(\.\d{1,6})?$/) == -1) {
				alert('Vous devez entrer un nombre pour les limites de poids.');
				return false;
			}
		}
		return true;
	}
}
function perso_verif(f) {
	if (f.elements.it && f.elements.it[1].checked == true) {
		var il = f.elements.il.value;
		var ih = f.elements.ih.value;
		if (il.search(/^[\d]{2,}$/gi) != -1) {
			if (ih.search(/^[\d]{2,}$/gi) != -1) {
				return true;
			} else {
				alert('Vous devez entrer un entier supérieur à 9 comme hauteur maximale.');
				f.elements.ih.focus();
				return false;
			}
		} else {
			alert('Vous devez entrer un entier supérieur à 9 comme largeur maximale.');
			f.elements.il.focus();
			return false;
		}
	}
	if (f.elements.ra && f.elements.ra.checked == true) {
		var rj = f.elements.rj.value;
		if (rj.search(/^[\d]+$/gi) != -1) {
			return true;
		} else {
			alert('Vous devez entrer un entier pour le nombre de jours.');
			f.elements.rj.focus();
			return false;
		}
	}
}



/*
 *	Parties repliables.
*/
var parties = [];
parties['navigation'] = -1;
parties['hasard'] = -1;
parties['perso'] = -1;
parties['stats'] = -1;
parties['tags'] = -1;
parties['liens'] = -1;
parties['exif'] = -1;
parties['iptc'] = -1;
parties['comments'] = -1;
parties['enlarge'] = -1;
parties['membres'] = -1;
var titles = [];
titles['navigation'] = [];
titles['navigation']['m'] = 'Montrer l\'aide  à la navigation';
titles['navigation']['c'] = 'Cacher l\'aide  à la navigation';
titles['hasard'] = [];
titles['hasard']['m'] = 'Montrer une image choisie au hasard';
titles['hasard']['c'] = 'Cacher une image choisie au hasard';
titles['perso'] = [];
titles['perso']['m'] = 'Montrer les options de personnalisation';
titles['perso']['c'] = 'Cacher les options de personnalisation';
titles['stats'] = [];
titles['stats']['m'] = 'Montrer les statistiques';
titles['stats']['c'] = 'Cacher les statistiques';
titles['membres'] = [];
titles['membres']['m'] = 'Montrer la partie membre';
titles['membres']['c'] = 'Cacher la partie membre';
titles['tags'] = [];
titles['tags']['m'] = 'Montrer les tags';
titles['tags']['c'] = 'Cacher les tags';
titles['liens'] = [];
titles['liens']['m'] = 'Montrer les liens';
titles['liens']['c'] = 'Cacher les liens';
titles['exif'] = [];
titles['exif']['m'] = 'Montrer les informations Exif';
titles['exif']['c'] = 'Cacher les informations Exif';
titles['iptc'] = [];
titles['iptc']['m'] = 'Montrer les informations IPTC';
titles['iptc']['c'] = 'Cacher les informations IPTC';
titles['comments'] = [];
titles['comments']['m'] = 'Montrer les commentaires';
titles['comments']['c'] = 'Cacher les commentaires';
titles['enlarge'] = [];
titles['enlarge']['m'] = 'Montrer la sidebar';
titles['enlarge']['c'] = 'Cacher la sidebar';
var gpj = GetCookie('galerie_perso_js');
if (gpj) { gpj = gpj.split(''); }

function cacher_montrer(p, obj) {
	var part = document.getElementById('partie_' + p);
	var titre = document.getElementById('red_' + p);
	var c = document.getElementById('commentaires_ajout');
	if (part) {
		if (parties[p] == -1) {
			parties[p] = (titre.className.search(/cacher/gi) != -1) ? 1 : 0;
			obj.href = 'javascript:void(0);';
		}
		if (parties[p]) {
			part.style.display = 'none';
			if (c && p == 'comments') { c.style.display = 'none'; }
			titre.className = titre.className.replace(/cacher/, 'montrer');
			obj.style.backgroundImage = obj.style.backgroundImage.replace(/cacher/, 'montrer');
			obj.title = titles[p]['m'];
			parties[p] = 0;
		} else {
			part.style.display = '';
			if (c && p == 'comments') { c.style.display = ''; }
			titre.className = titre.className.replace(/montrer/, 'cacher');
			obj.style.backgroundImage = obj.style.backgroundImage.replace(/montrer/, 'cacher');
			obj.title = titles[p]['c'];
			parties[p] = 1;
		}
	}
	if (p == 'enlarge') {
		var img = obj.getElementsByTagName('img')[0];
		var sb = document.getElementById('panneau');
		var h1 = document.getElementsByTagName('h1')[0];
		if (parties[p] == -1) {
			parties[p] = (img.src.search(/enlarge_on.png$/gi) != -1) ? 1 : 0;
			obj.href = 'javascript:void(0);';
		}
		if (parties[p]) {
			sb.style.display = 'none';
			h1.style.display = 'none';
			img.src = img.src.replace(/enlarge_on.png$/, 'enlarge_off.png');
			img.alt = titles[p]['m'];
			obj.title = titles[p]['m'];
			parties[p] = 0;
			
		} else {
			sb.style.display = '';
			h1.style.display = '';
			img.src = img.src.replace(/enlarge_off.png$/, 'enlarge_on.png');
			img.alt = titles[p]['c'];
			obj.title = titles[p]['c'];
			parties[p] = 1;
		}
		autoAjust();
	}
	saveDisplay();
}

function saveDisplay() {
	var date = new Date;
	date.setFullYear(date.getFullYear()+10);
	var n = document.getElementById('red_navigation');
	if (n) { var i0 = (n.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i0 = gpj[0]; } else { var i0 = '2'; }
	var h = document.getElementById('red_hasard');
	if (h) { var i1 = (h.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i1 = gpj[1]; } else { var i1 = '2'; }
	var p = document.getElementById('red_perso');
	if (p) { var i2 = (p.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i2 = gpj[2]; } else { var i2 = '2'; }
	var s = document.getElementById('red_stats');
	if (s) { var i3 = (s.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i3 = gpj[3]; } else { var i3 = '2'; }
	var t = document.getElementById('red_tags');
	if (t) { var i4 = (t.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i4 = gpj[4]; } else { var i4 = '2'; }
	var c = document.getElementById('red_comments');
	if (c) { var i5 = (c.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i5 = gpj[5]; } else { var i5 = '2'; }
	var e = document.getElementById('enlarge');
	if (e && e.getElementsByTagName('img')[0]) { var i6 = (e.getElementsByTagName('img')[0].src.search(/enlarge_on.png$/gi) != -1) ? '1' : '0'; } else if (gpj) { var i6 = gpj[6]; } else { var i6 = '2'; }
	var x = document.getElementById('red_exif');
	if (x) { var i7 = (x.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i7 = gpj[7]; } else { var i7 = '2'; }
	var l = document.getElementById('red_liens');
	if (l) { var i8 = (l.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i8 = gpj[8]; } else { var i8 = '2'; }
	var i = document.getElementById('red_iptc');
	if (i) { var i9 = (i.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i9 = gpj[9]; } else { var i9 = '2'; }
	var m = document.getElementById('red_membres');
	if (m) { var i10 = (m.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i10 = gpj[10]; } else { var i10 = '2'; }
	var path = (galerie_path == '/') ? '/' : galerie_path + '/';
	SetCookie('galerie_perso_js', i0+i1+i2+i3+i4+i5+i6+i7+i8+i9+i10, date, path);
}

function initDisplay() {
	if (gpj) {
		gpj[0] = (gpj[0]) ? gpj[0] : '2';
		gpj[1] = (gpj[1]) ? gpj[1] : '2';
		gpj[2] = (gpj[2]) ? gpj[2] : '2';
		gpj[3] = (gpj[3]) ? gpj[3] : '2';
		gpj[4] = (gpj[4]) ? gpj[4] : '2';
		gpj[5] = (gpj[5]) ? gpj[5] : '2';
		gpj[6] = (gpj[6]) ? gpj[6] : '2';
		gpj[7] = (gpj[7]) ? gpj[7] : '2';
		gpj[8] = (gpj[8]) ? gpj[8] : '2';
		gpj[9] = (gpj[9]) ? gpj[9] : '2';
		gpj[10] = (gpj[10]) ? gpj[10] : '2';

		affichageParties('navigation', gpj[0], titles['navigation']['m'], titles['navigation']['c']);
		affichageParties('hasard', gpj[1], titles['hasard']['m'], titles['hasard']['c']);
		affichageParties('perso', gpj[2], titles['perso']['m'], titles['perso']['c']);
		affichageParties('stats', gpj[3], titles['stats']['m'], titles['stats']['c']);
		affichageParties('tags', gpj[4], titles['tags']['m'], titles['tags']['c']);
		affichageParties('comments', gpj[5], titles['comments']['m'], titles['comments']['c']);
		affichageParties('exif', gpj[7], titles['exif']['m'], titles['exif']['c']);
		affichageParties('liens', gpj[8], titles['liens']['m'], titles['liens']['c']);
		affichageParties('iptc', gpj[9], titles['iptc']['m'], titles['iptc']['c']);
		affichageParties('membres', gpj[10], titles['membres']['m'], titles['membres']['c']);

		var e = document.getElementById('enlarge');
		if (e) {
			var img = e.getElementsByTagName('img')[0];
			var sb = document.getElementById('panneau');
			var h1 = document.getElementsByTagName('h1')[0];
			if (gpj[6] == '0') {
				sb.style.display = 'none';
				h1.style.display = 'none';
				img.src = img.src.replace(/enlarge_on.png$/, 'enlarge_off.png');
				e.title = titles['enlarge']['m'];
			} else if (gpj[6] == '1') {
				sb.style.display = '';
				h1.style.display = '';
				img.src = img.src.replace(/enlarge_off.png$/, 'enlarge_on.png');
				e.title = titles['enlarge']['c'];
			}
		}
	}
}

function affichageParties(part, e, title_m, title_c) {
	var p = document.getElementById('partie_' + part);
	var t = document.getElementById('red_' + part);
	var l = document.getElementById('ldp_' + part);
	var c = document.getElementById('commentaires_ajout');
	if (p && t && l) {
		if (e == '0') {
			p.style.display = 'none';
			if (c && part == 'comments') { c.style.display = 'none'; }
			t.className = t.className.replace(/cacher/, 'montrer');
			l.style.backgroundImage = l.style.backgroundImage.replace(/cacher/, 'montrer');
			l.title = title_m;
		} else if (e == '1') {
			p.style.display = '';
			if (c && part == 'comments') { c.style.display = ''; }
			t.className = t.className.replace(/montrer/, 'cacher');
			l.style.backgroundImage = l.style.backgroundImage.replace(/montrer/, 'cacher');
			l.title = title_c;
		}
	}
}



/*
 *	Fonctions de cookies.
*/
//  Cookie Functions -- "Night of the Living Cookie" Version (25-Jul-96)
//  Written by:  Bill Dortch, hIdaho Design <bdortch@hidaho.com>
function getCookieVal (offset) {
  var endstr = document.cookie.indexOf (";", offset);
  if (endstr == -1)
    endstr = document.cookie.length;
  return unescape(document.cookie.substring(offset, endstr));
}

function GetCookie (name) {
  var arg = name + "=";
  var alen = arg.length;
  var clen = document.cookie.length;
  var i = 0;
  while (i < clen) {
    var j = i + alen;
    if (document.cookie.substring(i, j) == arg)
      return getCookieVal (j);
	i = document.cookie.indexOf(" ", i) + 1;
    if (i == 0) break; 
  }
  return null;
}

function SetCookie (name,value,expires,path,domain,secure) {
  document.cookie = name + "=" + escape (value) +
    ((expires) ? "; expires=" + expires.toGMTString() : "") +
    ((path) ? "; path=" + path : "") +
    ((domain) ? "; domain=" + domain : "") +
    ((secure) ? "; secure" : "");
}
