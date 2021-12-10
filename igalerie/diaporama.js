// ===================================================================================== PREFERENCES  =//

// Couleur de fond par défaut.
var diapo_background = '#101010';

// Durée d'affichage d'une image en mode automatique par défaut, en secondes.
var diapo_auto_time = 5.0;

// Niveau de profondeur de préchargement d'images (entre 2 et 5).
var diapo_img_preload = 2;

// Répertoire des images du diaporama par rapport au répertoire CSS.
var diapo_style_dir = 'diaporama/';



// ============================================================================= VARIABLES GLOBALES = //

var diapo_open = false;

// Informations générales.
var diapo_deja,diapo_nb_images,diapo_auto,diapo_auto_change_img,diapo_img_resize,diapo_lecture,
    diapo_overflow_body,diapo_overflow_html;

// Informations et composants Ajax.
var diapo_http,diapo_xml,diapo_requete,diapo_startnum,diapo_params,diapo_ready,diapo_q;

// Informations utiles des images.
var diapo_image = [];
	diapo_image.actuelle = [];
	diapo_image.suivante = [];
	diapo_image.precedente = [];
	diapo_image.derniere = [];
	diapo_image.premiere = [];



// ========================================================== DEMARRAGE / FERMETURE DU DIAPORAMA = //

/*
  *	Initialisation.
*/
function diapoStart(nb_images, img, arg1, arg2, arg3, startnum) {

	// On supprime les barres de navigation du navigateur.
	diapo_overflow_html = document.getElementsByTagName('html')[0].style.overflow;
	diapo_overflow_body = document.getElementsByTagName('body')[0].style.overflow;
	document.getElementsByTagName('html')[0].style.overflow = 'hidden';
	document.getElementsByTagName('body')[0].style.overflow = 'hidden';

	// On "ouvre" le diaporama.
	diapo_open = true;
	document.getElementById('igalerie').style.display = 'none';
	if (diapo_deja) {
		document.getElementById('igal_diapo').style.display = '';
		diapoResize();
	} else {
		diapo_style_dir = style_dir + '/' + diapo_style_dir;
		diapoPreloadImages();

		diapo_nb_images = nb_images;
		diapo_startnum = startnum;

		diapoCreateBase();

		// On récupère les données des images.
		var preload = (diapo_img_preload < 2) ? 2 : diapo_img_preload;
		arg1 = encodeURLParam(arg1);
		arg2 = encodeURLParam(arg2);
		arg2 = (arg2) ? arg2 + '&' : '';
		arg3 = encodeURLParam(arg3);
		arg3 = (arg3) ? arg3 + '&' : '';
		diapo_params = arg3 + arg2 + arg1 + '&preload=' + preload;
		diapo_http = diapoCreateRequestObject();
		diapo_requete = galerie_path + '/diaporama.php?' + diapo_params + '&actuelle=' + img 
					  + '&startnum=' + startnum + '&firstlast=1&galerie_file=' + galerie_file + '&nb_images=' + nb_images;
		//window.location = diapo_requete;
		diapo_http.open('get', diapo_requete, true);
		diapo_http.onreadystatechange = diapoAjaxHandleReturn;
		diapo_http.send(null);
	}

	// On réajuste les dimensions de l'image si la fenêtre du navigateur est redimensionnée.
	window.onresize = diapoResize;

	// Navigation au clavier.
	document.onkeyup = diapoClavier;
}
function encodeURLParam(param) {
		if (!param) { return; }
		var a = param.split('=');
		a[1] = escape(a[1]);
		var p = '';
		for (var i = 0; i < a.length; i++) {
			p += (a[i+1]) ? a[i] + '=' : a[i];
		}
		return p;
}

/*
  *	Préchargement des images.
*/
function diapoPreloadImages(e) {
	var preload_png = ['arret_off', 'arret_on', 'lecture_off', 'lecture_on',
						'auto_moins', 'auto_moins_hover', 'auto_plus', 'auto_plus_hover',
						'noresize', 'noresize_inactive', 'resize', 'resize_inactive',
						'derniere', 'derniere_hover', 'derniere_inactive',
						'suivante', 'suivante_hover', 'suivante_inactive',
						'premiere', 'premiere_hover', 'premiere_inactive',
						'precedente', 'precedente_hover', 'precedente_inactive',
						'separateur'];
	document.preload_images = [];
	for (var i = 0, lpng = preload_png.length; i < lpng; i++) {
		document.preload_images[i] = new Image();
		document.preload_images[i].src = diapo_style_dir + 'diapo_' + preload_png[i] + '.png';
	}
	var preload_gif = ['navigation_fond', 'position_fond'];
	for (var n = 0, lgif = preload_gif.length; n < lgif; n++, i++) {
		document.preload_images[i] = new Image();
		document.preload_images[i].src = diapo_style_dir + 'diapo_' + preload_gif[i] + '.gif';
	}
}

/*
  *	Fermeture du diaporama.
*/
function diapoFermer() {

	// On arrête le mode auto s'il est activé.
	if (diapo_auto) {
		diapoStopAuto();
	}

	// On redimensionne l'image si elle est en taille réelle.
	if (!diapo_img_resize) {
		diapoSwitchImageSize();
	}

	// On désactive le diaporama et on réactive la galerie.
	document.getElementById('diapo_infos').style.display = 'none';
	document.getElementById('diapo_infos_titre').style.display = 'none';
	document.getElementById('igal_diapo').style.display = 'none';
	document.getElementById('igalerie').style.display = '';
	diapo_start = false;

	// On remet les barres de navigation du navigateur.
	document.getElementsByTagName('html')[0].style.overflow = (navigator.userAgent.search(/Opera/) != -1) ? 'auto' : diapo_overflow_html;
	document.getElementsByTagName('body')[0].style.overflow = (navigator.userAgent.search(/Opera/) != -1) ? 'auto' : diapo_overflow_body;

	// On rend le on resize à la fonction de redimensionnement auto de l'image.
	var image_r = document.getElementById('image_r');
	if (!image_r || (navigator.userAgent.search(/Gecko/) == -1 && img_auto_resize == 2) || img_gd_resize) {
	} else if (typeof(autoAjust) == 'function') {
		window.onresize = autoAjust;
	}
}



// ============================================ INTERACTIVITE : FONCTIONS DE BARRE DE NAVIGATION = //

/*
  *	Changement de la couleur de fond.
*/
function diapoChangeFond(e) {
	var pos = 0;
	if (e && typeof(e.layerX) == 'number') {
		pos = e.layerX - document.getElementById('diapo_change_fond').getElementsByTagName('img')[0].offsetLeft - 1;
	} else if (window.event && typeof(window.event.offsetX) == 'number') {
		pos = window.event.offsetX;
	}
	if (pos) {
		var color;
		if (pos < 11) { color = '#000000'; }
		else if (pos < 22) { color = '#101010'; }
		else if (pos < 33) { color = '#202020'; }
		else if (pos < 44) { color = '#303030'; }
		else if (pos < 55) { color = '#404040'; }
		else if (pos < 66) { color = '#505050'; }
		else if (pos < 77) { color = '#606060'; }
		else if (pos < 88) { color = '#707070'; }
		else if (pos < 99) { color = '#808080'; }
		else if (pos < 110) { color = '#909090'; }
		else if (pos < 121) { color = '#A0A0A0'; }
		else if (pos < 132) { color = '#B0B0B0'; }
		else if (pos < 143) { color = '#C0C0C0'; }
		else if (pos < 154) { color = '#D0D0D0'; }
		else if (pos < 165) { color = '#E0E0E0'; }
		else if (pos < 176) { color = '#F0F0F0'; }
		else { color = '#FFFFFF'; }
		diapo_background = color;
		document.getElementById('igal_diapo').style.backgroundColor = color;
	}
}

/*
  *	Switch redimensionnement / taille réelle de l'image.
*/
function diapoSwitchImageSize() {
	if (diapo_img_resize && 
	    (document.getElementById('diapo_image_actuelle').offsetWidth == diapo_image.actuelle.largeur)) {
		return;
	}
	var bouton = document.getElementById('diapo_img_resize').getElementsByTagName('img')[0];
	if (diapo_img_resize) {
		bouton.src = diapo_style_dir + 'diapo_resize.png';
		diapo_img_resize = false;
		diapoChangeImgSize('actuelle',false,true);
		diapoDragFonction(true);
	} else {
		bouton.src = diapo_style_dir + 'diapo_noresize.png';
		diapo_img_resize = true;
		diapoChangeImgSize('actuelle',false,false);
		diapoDragFonction(false);
	}
}

/*
  *	Fonction de drag and drop basée sur le code posté par Lasse Reichstein Nielsen
  *	dans le groupe comp.lang.javascript en janvier 2004.
*/
function diapoDragImage(img,evt) {
	if (diapo_img_resize) {
		return;
	}
	var root = document.documentElement || document.body;
	var x = img.xPos || img.offsetLeft;
	var y = img.yPos || img.offsetTop;
	var mx = evt.pageX || evt.clientX + root.scrollLeft;
	var my = evt.pageY || evt.clientY + root.scrollTop;
	document.onmousemove = function (evt) {
		evt = evt || window.event;
		var newmx = evt.pageX || evt.clientX + root.scrollLeft;
		var newmy = evt.pageY || evt.clientY + root.scrollTop;
		x += newmx - mx;
		y += newmy - my;
		mx = newmx;
		my = newmy;
		if (x <= 0 && x >= (diapoNavSize('largeur')-parseInt(diapo_image.actuelle.largeur))) {
			img.style.left = x + 'px';
		}
		if (y <= document.getElementById('diapo_position').offsetHeight &&
		   (y+parseInt(diapo_image.actuelle.hauteur)) >= (diapoNavSize('hauteur')-parseInt(document.getElementById('diapo_navigation').offsetHeight))) {
			img.style.top = y + 'px';
		}
		return false;
	};
	document.onmouseup = function () {
		document.onmousemove = document.onmouseup = null;
		return false;
	};
	return false;
}

/*
  *	Changement d'image.
*/
function diapoChangeImg(nav,auto) {
	if (!diapo_ready) {
		return;
	}
	if ((nav == 'suivante' || nav == 'precedente') &&
	    !diapo_xml.getElementsByTagName(nav)[0]) {
		return;
	}
	diapo_ready = false;

	// Réinitalise le temps de transition du mode auto si utilisation d'une flèche de navigation - part 1.
	diapo_auto_change_img = auto;
	if (diapo_auto && !auto) {
		clearTimeout(diapo_lecture);
	}

	// On change les informations de l'image actuelle.
	if (nav == 'suivante' || nav == 'precedente') {
		
		diapo_image.actuelle.id = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('id')[0].firstChild.data;
		diapo_image.actuelle.lien = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('lien')[0].firstChild.data;
		diapo_image.actuelle.nom = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('nom')[0].firstChild.data;
		diapo_image.actuelle.description = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('description')[0].firstChild.data;
		diapo_image.actuelle.exif = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('exif')[0].firstChild.data;
		diapo_image.actuelle.iptc = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('iptc')[0].firstChild.data;
		diapo_image.actuelle.infos = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('infos')[0].firstChild.data;
		diapo_image.actuelle.chemin = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('chemin')[0].firstChild.data;
		diapo_image.actuelle.hauteur = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('hauteur')[0].firstChild.data;
		diapo_image.actuelle.largeur = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('largeur')[0].firstChild.data;
		diapo_image.actuelle.position_chemin = diapo_xml.getElementsByTagName(nav)[0].getElementsByTagName('position_chemin')[0].firstChild.data;

	} else if (nav == 'premiere' || nav == 'derniere')  {
		diapo_image.actuelle.id = diapo_image[nav].id;
		diapo_image.actuelle.lien = diapo_image[nav].lien;
		diapo_image.actuelle.nom = diapo_image[nav].nom;
		diapo_image.actuelle.description = diapo_image[nav].description;
		diapo_image.actuelle.exif = diapo_image[nav].exif;
		diapo_image.actuelle.iptc = diapo_image[nav].iptc;
		diapo_image.actuelle.infos = diapo_image[nav].infos;
		diapo_image.actuelle.chemin = diapo_image[nav].chemin;
		diapo_image.actuelle.hauteur = diapo_image[nav].hauteur;
		diapo_image.actuelle.largeur = diapo_image[nav].largeur;
		diapo_image.actuelle.position_chemin = diapo_image[nav].position_chemin;
	}
	if (diapo_image.actuelle.description == 0) {
		diapo_image.actuelle.description = '';
	}
	if (diapo_image.actuelle.exif == 0) {
		diapo_image.actuelle.exif = '';
	}
	if (diapo_image.actuelle.iptc == 0) {
		diapo_image.actuelle.iptc = '';
	}

	// On change d'image.
	var img_actuelle = document.getElementById('diapo_image_actuelle');
	var img_change = document.getElementById('diapo_image_' + nav);
	img_actuelle.style.display = 'none';
	img_actuelle.setAttribute('id', 'diapo_image_temp');
	img_change.setAttribute('id', 'diapo_image_actuelle');
	img_change.style.display = '';

	// On recrée les première et dernière images.
	if (nav == 'premiere' || nav == 'derniere') {
		diapoCreateImg(nav);
		diapoChangeImgSize(nav, false, false);
	}

	// On change le chemin de potiion de l'image.
	document.getElementById('diapo_image_path').innerHTML = diapo_image.actuelle.position_chemin + '<a id="diapo_pos_actuel" href="' + diapo_image.actuelle.lien + '">' + diapo_image.actuelle.nom + '</a>';

	// Changement de l'image.
	diapoChangeImgSize('actuelle', false, false);
	if (!diapo_img_resize) {
		diapoSwitchImageSize();
	}
	diapoChangeBoutonRealSize();

	// On supprime les autres images.
	diapoSuppImg('temp');
	diapoSuppImg('suivante');
	diapoSuppImg('precedente');

	// Paramètres d'URL et changements des liens de navigation.
	var pos_num;
	if (nav == 'suivante') {
		diapo_startnum = diapo_startnum+1;
		pos_num = diapo_startnum+1;
		diapoChangeNavLiens(0,1,false,false);
	} else if (nav == 'precedente') {
		diapo_startnum = diapo_startnum-1;
		pos_num = diapo_startnum+1;
		diapoChangeNavLiens(1,0,false,false);
	} else if (nav == 'premiere') {
		diapo_startnum = 0;
		pos_num = 1;
		diapoChangeNavLiens(1,0,true,false);
	} else if (nav == 'derniere') {
		diapo_startnum = diapo_nb_images-1;
		pos_num = diapo_nb_images;
		diapoChangeNavLiens(0,1,false,true);
	}

	// On change le numéro de position de l'image.
	document.getElementById('diapo_image_pos_num').innerHTML = pos_num;

	// On récupère les nouvelles informations.
	diapo_requete = galerie_path + '/diaporama.php?' + diapo_params + '&actuelle=' + diapo_image.actuelle.id + '&firstlast=0' 
				  + '&startnum=' + diapo_startnum + '&galerie_file=' + galerie_file + '&nb_images=' + diapo_nb_images;
	diapo_http.open('get', diapo_requete, true);
	diapo_http.onreadystatechange = diapoAjaxHandleReturn;
	diapo_http.send(null);
}

/*
  *	Changement de la durée de transition entre deux images.
*/
var mousedown = true;
function diapoAutoTime(c,t) {
	if (!mousedown) {
		return;
	}
	var time = document.getElementById('diapo_auto_temps');
	
	// Accélération.
	if (!t) { t = 160; } else if (t > 20) { t = t-5; }

	var valeur = time.firstChild.nodeValue.replace(/^(\d+\.?\d*).+$/, '$1');
	if (parseFloat(valeur)+c > 2 && parseFloat(valeur)+c < 61) {
		diapo_auto_time = parseFloat(diapo_auto_time)+(c/2);
		var duree = parseFloat(valeur)+(c/2);
		duree = duree.toString();
		if (duree.search(/\./) == -1) {
			duree = duree + '.0';
		}
		time.firstChild.nodeValue = duree + ' secondes';
		if (mousedown) {
			setTimeout('diapoAutoTime(' + c + ',' + t + ');', t);
		}
	}
}

/*
  *	Démarre le mode automatique.
*/
function diapoStartAuto(start) {
	if (start && !diapo_xml.getElementsByTagName('suivante')[0]) {
		return;
	}

	if (start) {
		document.getElementById('diapostart').src = diapo_style_dir + 'diapo_lecture_on.png';
		document.getElementById('diapostop').src = diapo_style_dir + 'diapo_arret_off.png';
	}

	if (diapo_auto || start) {
		diapo_lecture = setTimeout('diapoChangeImg("suivante",true);diapoStartAuto()', diapo_auto_time*1000);
		diapo_auto = true;
	}
}

/*
  *	Arrête le mode automatique.
*/
function diapoStopAuto() {
	diapo_auto = false;
	clearTimeout(diapo_lecture);

	document.getElementById('diapostart').src = diapo_style_dir + 'diapo_lecture_off.png';
	document.getElementById('diapostop').src = diapo_style_dir + 'diapo_arret_on.png';
}

/*
  *	Navigation au clavier.
*/
function diapoClavier(key) {
	var touche = (document.all) ? event.keyCode : key.which;
	switch (touche) {

		// Flèche gauche : page précédente.
		case 37:
			diapoChangeImg('precedente'); break;

		// Flèche droite ou barre d'espacement.
		case 39:
			diapoChangeImg('suivante'); break;

		// Echap.
		case 27:
			diapoFermer(); break;

		// Informations.
		case 73:
			diapoInfos(); break;
	}
}



// =================================================== CHANGEMENT DES COMPOSANTS DU DIAPORAMA = //

/*
  *	Informations de l'image.
*/
function diapoInfos() {
	var igal_infos = document.getElementById('diapo_infos');
	var igal_infos_titre = document.getElementById('diapo_infos_titre');
	var e = (igal_infos.style.display == '') ? 'none' : '';
	igal_infos.style.display = e;
	igal_infos_titre.style.display = e;
	if (e == '') {
		diapoChangeInfos();
	}
}

/*
  *	Change les dimensions et la position de l'image.
*/
function diapoChangeImgSize(image,visible,realsize) {
	if (!diapo_image[image].largeur) {
		return;
	}
	var nav_largeur = diapoNavSize('largeur');
	var nav_hauteur = diapoHauteurDispo();

	var img = document.getElementById('diapo_image_' + image);

	// Dimensions de l'image.
	var ratio_l = diapo_image[image].largeur / nav_largeur;
	var ratio_h = diapo_image[image].hauteur / nav_hauteur;
	var img_resize_largeur = diapo_image[image].largeur;
	var img_resize_hauteur = diapo_image[image].hauteur;
	if (!realsize) {
		if ((diapo_image[image].largeur > nav_largeur) && (ratio_l >= ratio_h)) {
			img_resize_largeur = nav_largeur;
			img_resize_hauteur = Math.round(diapo_image[image].hauteur / ratio_l);
		}
		if ((diapo_image[image].hauteur > nav_hauteur) && (ratio_h >= ratio_l)) {
			img_resize_largeur = Math.round(diapo_image[image].largeur / ratio_h);
			img_resize_hauteur = nav_hauteur;
		}
	}

	// Position de l'image.
	var left = (nav_largeur-img_resize_largeur)/2;
	var top = (nav_hauteur-img_resize_hauteur)/2 + document.getElementById('diapo_position').offsetHeight;

	// On attribue les valeurs de dimensions et de position à l'image.
	img.setAttribute('width', img_resize_largeur);
	img.setAttribute('height', img_resize_hauteur);
	document.getElementById('diapo_image_' + image).style.left = left + 'px';
	document.getElementById('diapo_image_' + image).style.top = top + 'px';

	// Visibilité de l'image.
	if (visible) {
		document.getElementById('diapo_image_' + image).style.display = '';
	}
}

/*
  *	Divs des informations.
*/
function diapoChangeInfos() {
	var diapo_l = diapoNavSize('largeur');
	var diapo_h = diapoNavSize('hauteur');
	var igal_infos = document.getElementById('diapo_infos');
	var igal_infos_titre = document.getElementById('diapo_infos_titre');
	var diapo_navigation = document.getElementById('diapo_navigation');
	var diapo_position = document.getElementById('diapo_position');
	var l = igal_infos.offsetWidth;
	var h = diapo_h-(2*diapo_navigation.offsetHeight)-(2*diapo_position.offsetHeight)-igal_infos_titre.offsetHeight;
	var left = (diapo_l-l)/2;
	var top = diapo_position.offsetHeight*2;
	igal_infos.style.height = h + 'px';
	igal_infos.style.left = left + 'px';
	igal_infos.style.top = (top+igal_infos_titre.offsetHeight) + 'px';
	igal_infos_titre.style.left = left + 'px';
	igal_infos_titre.style.top = top + 'px';
}

/*
  *	Change les liens de navigation.
*/
function diapoChangeNavLiens(np,ns,nf,nl) {
	if ((ns || diapo_xml.getElementsByTagName('precedente')[np]) && !nf) {
		diapoAddNavLien('premiere');
		diapoAddNavLien('precedente');
	} else {
		diapoRemoveNavLien('premiere');
		diapoRemoveNavLien('precedente');
	}
	if ((np || diapo_xml.getElementsByTagName('suivante')[ns]) && !nl) {
		diapoAddNavLien('suivante');
		diapoAddNavLien('derniere');
	} else {
		diapoRemoveNavLien('suivante');
		diapoRemoveNavLien('derniere');
	}
}

/*
  *	Bouton du switch taille redimensionnée / taille réelle de l'image.
*/
function diapoChangeBoutonRealSize() {
	var img = document.getElementById('diapo_img_resize').getElementsByTagName('img')[0];
	var bouton = (diapo_img_resize) ? 'noresize' : 'resize';
	if (diapo_image.actuelle.largeur > diapoNavSize('largeur') ||
	    diapo_image.actuelle.hauteur > diapoHauteurDispo()) {
		img.onclick = function() {diapoSwitchImageSize(); };
		img.onmouseover = function() { this.style.cursor = 'pointer'; };
		img.setAttribute('src', diapo_style_dir + 'diapo_' + bouton + '.png');
		if (!diapo_img_resize) {
			diapoDragFonction(true);
		}
	} else {
		img.onclick = null;
		img.onmouseover = function() { this.style.cursor = 'default'; };
		img.setAttribute('src', diapo_style_dir + 'diapo_' + bouton + '_inactive.png');
		diapoDragFonction(false);
	}
}

/*
  *	Ajoute un lien de navigation.
*/
function diapoAddNavLien(nav) {
	var img = document.getElementById('diapo_' + nav).getElementsByTagName('img')[0];
	img.onclick = function() { diapoChangeImg(nav); };
	img.setAttribute('src', diapo_style_dir + 'diapo_' + nav + '.png');
	img.onmouseover = function() { this.src=diapo_style_dir + 'diapo_' + nav + '_hover.png';this.style.cursor='pointer'; };
	img.onmouseout = function() { this.src=diapo_style_dir + 'diapo_' + nav + '.png';this.style.cursor='pointer'; };
}

/*
  *	Supprime un lien de navigation.
*/
function diapoRemoveNavLien(nav) {
	var img = document.getElementById('diapo_' + nav).getElementsByTagName('img')[0];
	img.onclick = null;
	img.onmouseover = null;
	img.onmouseover = function() { this.style.cursor = 'default'; };
	img.onmouseout = null;
	img.setAttribute('src', diapo_style_dir + 'diapo_' + nav + '_inactive.png');
	if (diapo_auto && nav == 'suivante') {
		diapoStopAuto();
	}
}

/*
  *	Div du diaporama.
*/
function diapoChangeDiv() {
	var igal_diapo = document.getElementById('igal_diapo');
	igal_diapo.style.backgroundColor = diapo_background;
	igal_diapo.style.width = diapoNavSize('largeur') + 'px';
	igal_diapo.style.height = diapoNavSize('hauteur') + 'px';
}

/*
  *	Traitement après redimensionnement du navigateur.
*/
function diapoResize() {
	diapoChangeDiv();
	diapoChangeInfos();
	if (diapo_img_resize) {
		diapoChangeImgSize('actuelle');
	} else {
		var img = document.getElementById('diapo_image_actuelle');
		var img_pos_left = img.style.left.replace(/px/, '');
		var img_pos_top = img.style.top.replace(/px/, '');
		var diff_largeur = diapoNavSize('largeur')-diapo_image.actuelle.largeur;
		var diff_hauteur = diapoHauteurDispo()-diapo_image.actuelle.hauteur+
			document.getElementById('diapo_position').offsetHeight;
		if (diapo_image.actuelle.largeur > diapoNavSize('largeur')) {
			if (diff_largeur > img_pos_left) {
				img.style.left = diff_largeur + 'px';
			} else if (img_pos_left > 0) {
				img.style.left = (diapoNavSize('largeur')-diapo_image.actuelle.largeur)/2 + 'px';
			}
		} else {
			img.style.left = (diapoNavSize('largeur')-diapo_image.actuelle.largeur)/2 + 'px';
		}
		if (diapo_image.actuelle.hauteur > diapoHauteurDispo()) {
			if  (diff_hauteur > img_pos_top) {
				img.style.top = diff_hauteur + 'px';
			} else if (img_pos_top > document.getElementById('diapo_position').offsetHeight) {
				img.style.top = document.getElementById('diapo_position').offsetHeight+
					((diapoHauteurDispo()-diapo_image.actuelle.hauteur)/2) + 'px';
			}
		} else {
			img.style.top = document.getElementById('diapo_position').offsetHeight+
				((diapoHauteurDispo()-diapo_image.actuelle.hauteur)/2) + 'px';
		}
	}
	diapoChangeBoutonRealSize();
}

/*
  *	Ajoute / supprime la fonction de drag and drop sur l'image.
*/
function diapoDragFonction(e) {
	var image = document.getElementById('diapo_image_actuelle');
	if (e) {
		if (document.all) {
			image.onmousedown = function() { return diapoDragImage(this,event); };
		} else {
			image.setAttribute('onmousedown', 'return diapoDragImage(this,event)');
		}
		image.style.cursor = 'move';
	} else {
		image.setAttribute('onmousedown', '');
		image.style.cursor = 'default';
	}
}

/*
  *	Suppression d'une image.
*/
function diapoSuppImg(img) {
	var igal_diapo = document.getElementById('igal_diapo');
	var img_supp = document.getElementById('diapo_image_' + img);
	if (img_supp) {
		efface = igal_diapo.removeChild(img_supp);
	}
}

/*
  *	Effectue les changements nécessaire après la requête Ajax.
*/
function diapoChangeAfterAjax() {

	diapoRecupImages('precedente');
	diapoRecupImages('suivante');

	// On recrée les images suivante et précédente.
	diapoCreateImg('precedente');
	diapoCreateImg('suivante');

	// On détermine la taille des images, on les positionne et on les affiche.
	diapoChangeImgSize('suivante', false, false);
	diapoChangeImgSize('precedente', false, false);

	diapo_img_resize = true;

	// Chemin de positionnement.
	document.getElementById('diapo_image_path').innerHTML = diapo_image.actuelle.position_chemin + '<a id="diapo_pos_actuel" href="' + diapo_image.actuelle.lien + '">' + diapo_image.actuelle.nom + '</a>';

	// Réinitalise le temps de transition du mode auto si utilisation d'une flèche de navigation - part 2.
	if (diapo_auto === true && !diapo_auto_change_img) {
		diapoStartAuto();
	}

	// On est de nouveau près pour changer d'image.
	diapo_ready = true;

	diapoPreloadVoisines('suivante');
	diapoPreloadVoisines('precedente');

	document.getElementById('diapo_infos_int').innerHTML = diapo_image.actuelle.description + diapo_image.actuelle.infos + diapo_image.actuelle.exif + diapo_image.actuelle.iptc;
}

/*
  *	Précharrgement des images voisines.
*/
function diapoPreloadVoisines(img) {
	document.preload_voisines = [];
	document.preload_voisines[img] = [];
	for (var i = 0; i < diapo_xml.getElementsByTagName(img).length; i++) {
		document.preload_voisines[img][i] = new Image();
		document.preload_voisines[img][i].src = galerie_path + '/' + diapo_xml.getElementsByTagName(img)[i].getElementsByTagName('chemin')[0].firstChild.data;
	}
}



// ======================================================================= INFORMATIONS GENERALES = //

/*
  *	On détermine l'espace du navigateur disponible.
*/
function diapoNavSize(what) {
	var w = 0;
	var h = 0;
	if (window.innerWidth && typeof(window.innerWidth) == 'number') {
		w = window.innerWidth;
		h = window.innerHeight;
	} else if (document.documentElement && document.documentElement.clientWidth && typeof(document.documentElement.clientWidth) == 'number') {
		w = document.documentElement.clientWidth;
		h = document.documentElement.clientHeight;
	} else if (document.body && document.body.clientWidth && typeof(document.body.clientWidth) == 'number') {
		w = document.body.clientWidth;
		h = document.body.clientHeight;
	}
	if (what) {
		return (what == 'hauteur') ? h : w;
	} else {
		return { largeur:w, hauteur:h };
	}
}

/*
  *	Corrige la hauteur disponible en fonction de la hauteur des barres.
*/
function diapoHauteurDispo() {
	return diapoNavSize('hauteur')-document.getElementById('diapo_position').offsetHeight-document.getElementById('diapo_navigation').offsetHeight;
}



// =================================================================== CONSTRUCTION DU DIAPORAMA = //

/*
  *	On génère les composants du diaporama.
*/
function diapoCreateBase() {

	diapo_img_resize = true;

	// On génère le div qui contiendra les composants du diaporama.
	var igal_diapo = document.createElement('div');
		igal_diapo.setAttribute('id', 'igal_diapo');
	document.getElementsByTagName('body')[0].appendChild(igal_diapo);
	diapoChangeDiv();

	// On génère les barres de position et de navigation.
	var barres = '';
	barres += diapoCreatePosition();
	barres += diapoCreateNavigation();

	// On génére le div qui contiendra les informations de l'image.
	var infos = '';
	infos += '<div style="z-index:-1" id="diapo_infos_titre"><p><a id="diapo_infos_fermer" href="javascript:diapoInfos()">fermer</a>informations</p></div>\n';
	infos += '<div style="z-index:-1" id="diapo_infos">\n';
	infos += '<div id="diapo_infos_int"></div>\n';
	infos += '</div>\n';	

	document.getElementById('igal_diapo').innerHTML = barres + infos;

	diapoInfos();
	document.getElementById('diapo_infos_titre').style.zIndex = 5;
	document.getElementById('diapo_infos').style.zIndex = 5;
}

/*
  *	Création des images.
*/
function diapoCreateImg(i) {
	if (!diapo_image[i]) {
		return;
	}
	var img = document.createElement('img');
	img.setAttribute('id', 'diapo_image_' + i);
	document.getElementById('igal_diapo').appendChild(img);
	document.getElementById('diapo_image_' + i).style.display = 'none';
	document.getElementById('diapo_image_' + i).src = galerie_path + '/' + diapo_image[i].chemin;
}

/*
  *	Barre de position.
*/
function diapoCreatePosition() {
	var barre_position = '';
	barre_position += '<div id="diapo_position">\n';
	barre_position += '<span id="diapo_image_pos"><img title="informations sur l\'image" onclick="diapoInfos()" onmouseover="this.style.cursor=\'pointer\'" id="diapo_image_infos_link" src="' + diapo_style_dir + 'diapo_info.png" /> image <span id="diapo_image_pos_num">' + (diapo_startnum+1) + '</span>|' + diapo_nb_images + ' - <a id="diapo_sortir" href="javascript:diapoFermer();">fermer</a></span>\n';
	barre_position += '<span id="diapo_image_path"></span>\n';
	barre_position += '</div>\n';
	return barre_position;
}

/*
  *	Barre de navigation.
*/
function diapoCreateNavigation() {
	var barre_navigation = '';
	barre_navigation += '<div id="diapo_navigation">\n';

	barre_navigation += '<div id="diapo_change_fond"><img onmousemove="diapoChangeFond(event);" src="' + diapo_style_dir + 'diapo_barre_fond.png" alt="Change la couleur de fond" /></div>\n';

	barre_navigation += '<div id="diapo_mode_auto">\n';
	barre_navigation += '<span id="diapo_img_resize"><img src="' + diapo_style_dir + 'diapo_noresize_inactive.png" ></span>\n';
	barre_navigation += '<span class="diapo_separateur"><img src="' + diapo_style_dir + 'diapo_separateur.png" /></span>\n';
	barre_navigation += '<img onmouseover="this.style.cursor=\'pointer\'" onclick="diapoStartAuto(1)" id="diapostart" src="' + diapo_style_dir + 'diapo_lecture_off.png" />\n';
	barre_navigation += '<img onmouseover="this.style.cursor=\'pointer\'" onclick="diapoStopAuto()" id="diapostop" src="' + diapo_style_dir + 'diapo_arret_on.png" />\n';
	barre_navigation += '<span id="diapo_auto_temps">' + diapo_auto_time + '.0 secondes</span>\n';
	barre_navigation += '<img onmouseup="mousedown=false;setTimeout(\'mousedown=true;\',170);" onmousedown="diapoAutoTime(1);" src="' + diapo_style_dir + 'diapo_auto_plus.png" onmouseover="this.src=\'' + diapo_style_dir + 'diapo_auto_plus_hover.png\';this.style.cursor=\'pointer\'" onmouseout="this.src=\'' + diapo_style_dir + 'diapo_auto_plus.png\'" />\n';
	barre_navigation += '<img onmouseup="mousedown=false;setTimeout(\'mousedown=true;\',170);" onmousedown="diapoAutoTime(-1);" src="' + diapo_style_dir + 'diapo_auto_moins.png" onmouseover="this.src=\'' + diapo_style_dir + 'diapo_auto_moins_hover.png\';this.style.cursor=\'pointer\'" onmouseout="this.src=\'' + diapo_style_dir + 'diapo_auto_moins.png\'" />\n';
	barre_navigation += '</div>\n';

	barre_navigation += '<div id="diapo_boutons_nav">\n';
	barre_navigation += '<span id="diapo_premiere"><img src="' + diapo_style_dir + 'diapo_premiere_inactive.png" alt="" /></span>\n';
	barre_navigation += '<span id="diapo_precedente"><img src="' + diapo_style_dir + 'diapo_precedente_inactive.png" alt="" /></span>\n';
	barre_navigation += '<span id="diapo_suivante"><img src="' + diapo_style_dir + 'diapo_suivante_inactive.png" alt="" /></span>\n';
	barre_navigation += '<span id="diapo_derniere"><img src="' + diapo_style_dir + 'diapo_derniere_inactive.png" alt="" /></span>\n';
	barre_navigation += '</div>\n';

	barre_navigation += '</div>\n';

	return barre_navigation;
}

/*
  *	Initialise les informations de démarrage du diaporama.
*/
function diapoCreateAfterAjax() {

	diapoRecupImages('actuelle');
	diapoRecupImages('premiere');
	diapoRecupImages('derniere');
	diapoRecupImages('suivante');
	diapoRecupImages('precedente');

	// On crée les images.
	diapoCreateImg('actuelle');
	diapoCreateImg('precedente');
	diapoCreateImg('suivante');
	diapoCreateImg('premiere');
	diapoCreateImg('derniere');

	diapoChangeBoutonRealSize();

	// On change les liens de navigation.
	diapoChangeNavLiens(0, 0, false, false);

	// On détermine la taille des images, on les positionne et on les affiche.
	diapoChangeImgSize('actuelle', true, false);
	diapoChangeImgSize('suivante', false, false);
	diapoChangeImgSize('precedente', false, false);
	diapoChangeImgSize('premiere', false, false);
	diapoChangeImgSize('derniere', false, false);

	// Chemin de positionnement.
	document.getElementById('diapo_image_path').innerHTML = diapo_image.actuelle.position_chemin + '<a id="diapo_pos_actuel" href="' + diapo_image.actuelle.lien + '">' + diapo_image.actuelle.nom + '</a>';

	diapo_ready = true;

	diapoPreloadVoisines('suivante');
	diapoPreloadVoisines('precedente');

	document.getElementById('diapo_infos_int').innerHTML = diapo_image.actuelle.description + diapo_image.actuelle.infos + diapo_image.actuelle.exif + diapo_image.actuelle.iptc;
}



// ================================================================================= FONCTIONS AJAX = //

function diapoCreateRequestObject() {
    if (window.XMLHttpRequest) {
        diapo_http = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        diapo_http = new ActiveXObject('Microsoft.XMLHTTP');
    }
    return diapo_http;
}

function diapoAjaxHandleReturn() {
	clearTimeout(diapo_q);
    if (diapo_http.readyState == 4) {
		if (!diapo_http) {
			diapo_q = setTimeout('diapoAjaxRequete()', 500);
			return;
		}
		if (diapo_http.status == 200) {
			diapo_xml = diapo_http.responseXML;
			if (diapo_xml) {
				if (diapo_deja) {
					diapoChangeAfterAjax();
				} else {
					diapoCreateAfterAjax();
					diapo_deja = true;
				}
			} else {
				if (diapo_http.responseText) {
					if (confirm('Une erreur PHP s\'est produite :\n\n' + diapo_http.responseText + '.\n\nRéessayer ?')) {
						diapo_q = setTimeout('diapoAjaxRequete()', 250);
					}
				} else {
					diapo_q = setTimeout('diapoAjaxRequete()', 100);
				}
			}
		} else {
			if (diapo_http.status == 12152) {
				diapo_q = setTimeout('diapoAjaxRequete()', 100);
			} else {
				if (confirm('Une erreur HTTP s\'est produite :\n\n' + diapo_http.status + ' : ' + diapo_http.statusText + '\n\nRéessayer ?')) {
					diapo_q = setTimeout('diapoAjaxRequete()', 250);
				}
			}
		}
    }
}

/*
  *	On retente la requête.
*/
function diapoAjaxRequete() {
	diapo_http.open('get', diapo_requete, true);
	diapo_http.onreadystatechange = diapoAjaxHandleReturn;
	diapo_http.send(null);
}

function diapoRecupImages(img) {
	if (diapo_xml.getElementsByTagName(img)[0]) {
		diapo_image[img].id = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('id')[0].firstChild.data;
		diapo_image[img].lien = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('lien')[0].firstChild.data;
		diapo_image[img].nom = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('nom')[0].firstChild.data;
		diapo_image[img].description = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('description')[0].firstChild.data;
		diapo_image[img].infos = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('infos')[0].firstChild.data;
		diapo_image[img].exif = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('exif')[0].firstChild.data;
		diapo_image[img].iptc = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('iptc')[0].firstChild.data;
		diapo_image[img].chemin = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('chemin')[0].firstChild.data;
		diapo_image[img].hauteur = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('hauteur')[0].firstChild.data;
		diapo_image[img].largeur = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('largeur')[0].firstChild.data;
		diapo_image[img].position_chemin = diapo_xml.getElementsByTagName(img)[0].getElementsByTagName('position_chemin')[0].firstChild.data;
		if (diapo_image[img].description == 0) {
			diapo_image[img].description = '';
		}
		if (diapo_image[img].exif == 0) {
			diapo_image[img].exif = '';
		}
		if (diapo_image[img].iptc == 0) {
			diapo_image[img].iptc = '';
		}
	} else {
		diapo_image[img] = [];
	}
}
