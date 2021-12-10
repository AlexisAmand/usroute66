/*
 *	Initialisation.
*/
if (window.addEventListener) {
  window.addEventListener('load', init, false);
} else if (window.attachEvent) {
  window.attachEvent('onload', init);
}
function init() {
	style_vignettes();

	if (typeof preview == 'number') {
		document.getElementById('comment').focus();
	}

	textfocus('adv_search_stext');
	textfocus('section_pass');
	textfocus('ident_login');
	textfocus('new_login');
	textfocus('oubli_user');

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
var stars;
var stars_defaut;
var http;
function vote() {
	if (document.getElementById('image') && document.getElementById('note_user')) {
		stars = document.getElementById('note_user').getElementsByTagName('img');
		stars_defaut = new Array();
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
	var retour = '<span class="stats_titre">note moyenne</span> : <span id="note_stars">!</span> <span>(! - !)</span>';
	http = createRequestObject();
	http.open('post', galerie_path + '/vote.php', true);
	http.onreadystatechange = handleAJAXReturn;
	http.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	http.send('note=' + note + '&img=' + image_id + '&retour=' + retour + '&styledir=' + style_dir);
}
function createRequestObject() {
    var http;
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
			if (ok == '0') {
				alert('Votre vote n\'a pas été pris en compte car une erreur s\'est produite.');
			} else if (ok != '') {
				document.getElementById('stats_note').innerHTML = http.responseText;
			}
		} else {
			alert('Votre vote n\'a pas été pris en compte car une erreur s\'est produite.');
		}
    }
}



/*
 *	Vérification des formulaires.
*/
function search_verif(f) {
	var v = f.elements.search.value;
	if (v.search(/[^\W_]/gi) != -1) {
		return true;
	} else {
		alert('Vous devez entrer quelque chose à rechercher !');
		return false;
	}
}
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
var parties = new Array();
parties['navigation'] = -1;
parties['hasard'] = -1;
parties['perso'] = -1;
parties['stats'] = -1;
parties['exif'] = -1;
parties['iptc'] = -1;
parties['comments'] = -1;
parties['enlarge'] = -1;
var titles = new Array();
titles['navigation'] = new Array();
titles['navigation']['m'] = 'Montrer l\'aide  à la navigation';
titles['navigation']['c'] = 'Cacher l\'aide  à la navigation';
titles['hasard'] = new Array();
titles['hasard']['m'] = 'Montrer une image choisie au hasard';
titles['hasard']['c'] = 'Cacher une image choisie au hasard';
titles['perso'] = new Array();
titles['perso']['m'] = 'Montrer les options de personnalisation';
titles['perso']['c'] = 'Cacher les options de personnalisation';
titles['stats'] = new Array();
titles['stats']['m'] = 'Montrer les statistiques';
titles['stats']['c'] = 'Cacher les statistiques';
titles['exif'] = new Array();
titles['exif']['m'] = 'Montrer les informations Exif';
titles['exif']['c'] = 'Cacher les informations Exif';
titles['iptc'] = new Array();
titles['iptc']['m'] = 'Montrer les informations IPTC';
titles['iptc']['c'] = 'Cacher les informations IPTC';
titles['comments'] = new Array();
titles['comments']['m'] = 'Montrer les commentaires';
titles['comments']['c'] = 'Cacher les commentaires';
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
	var c = document.getElementById('red_comments');
	if (c) { var i4 = (c.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i4 = gpj[4]; } else { var i4 = '2'; }
	var e = document.getElementById('enlarge');
	if (e && e.getElementsByTagName('img')[0]) { var i5 = (e.getElementsByTagName('img')[0].src.search(/enlarge_on.png$/gi) != -1) ? '1' : '0'; } else if (gpj) { var i5 = gpj[5]; } else { var i5 = '2'; }
	var x = document.getElementById('red_exif');
	if (x) { var i6 = (x.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i6 = gpj[6]; } else { var i6 = '2'; }
	var i = document.getElementById('red_iptc');
	if (i) { var i7 = (i.className.search(/cacher/gi) != -1) ? '1' : '0'; } else if (gpj) { var i7 = gpj[7]; } else { var i7 = '2'; }
	var path = (galerie_path == '/') ? '/' : galerie_path + '/';
	SetCookie('galerie_perso_js', i0+i1+i2+i3+i4+i5+i6+i7, date, path);
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

		affichageParties('navigation', gpj[0], titles['navigation']['m'], titles['navigation']['c']);
		affichageParties('hasard', gpj[1], titles['hasard']['m'], titles['hasard']['c']);
		affichageParties('perso', gpj[2], titles['perso']['m'], titles['perso']['c']);
		affichageParties('stats', gpj[3], titles['stats']['m'], titles['stats']['c']);
		affichageParties('comments', gpj[4], titles['comments']['m'], titles['comments']['c']);
		affichageParties('exif', gpj[6], titles['exif']['m'], titles['exif']['c']);
		affichageParties('iptc', gpj[7], titles['iptc']['m'], titles['iptc']['c']);
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



/*
 *	Montrer/cacher options et recherche.
*/
function montrer(id) {
	var div = document.getElementById(id);
	var position = document.getElementById('position');
	var lien_retour = document.getElementById('lien_retour');
	var addfav = document.getElementById('addfav');
	if (!div) { return };
	if (div.style.display == '') {
		if (position) { position.style.display = ''; }
		if (lien_retour) { lien_retour.style.display = ''; }
		if (addfav) { addfav.style.display = ''; }
		div.style.display = 'none';
	} else {
		if (position) { position.style.display = 'none'; }
		if (lien_retour) { lien_retour.style.display = 'none'; }
		if (addfav) { addfav.style.display = 'none'; }
		var search = document.getElementById('recherche');
		var options = document.getElementById('options');
		if (search) { search.style.display = 'none'; }
		if (options) { options.style.display = 'none'; }
		div.style.display  = '';
		if (id == 'recherche') {
			document.getElementById('search').focus();
		}
	}
}
