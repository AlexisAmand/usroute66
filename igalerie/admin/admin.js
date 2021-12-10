/*
 *	Vérification des formulaires.
*/

function search_verif(f) {
	var v = f.elements.search.value;
	if (v.search(/./gi) != -1) {
		return true;
	} else {
		alert('Vous devez entrer quelque chose à rechercher !');
		document.getElementById('co_search_itext').focus();
		return false;
	}
}




/*
 *	Section "Galerie".
*/

function gal_objet_dmc(type, o) {
	var dmc = document.getElementById('gal_objet_' + type + '_' + o);
	if (dmc.style.display == 'none') {
		gal_desactive_all();
		dmc.style.display = '';
		if (type == 'desc') {
			document.getElementById('gal_objet_desc_' + o).getElementsByTagName('textarea')[0].focus();
		} else if (type == 'tags') {
			document.getElementById('gal_objet_tags_' + o).getElementsByTagName('textarea')[0].focus();
		}
	} else {
		dmc.style.display = 'none';
	}
}
function gal_desactive_all(e, c) {
	var div = document.getElementsByTagName('div');
	for (var i = 0; i < div.length; i++) {
		var classes = (c) ? c : 'description|tags|infos|deplace|datecreation';
		if (div[i].className.search('gal_objet_(' + classes + ')') != -1) {
			div[i].style.display = (e) ? '' : 'none';
		}
	}
}
function gal_display_all(e) {
	var select = document.getElementById('display_mode');
	if (!select) {
		return;
	}
	for (var i = 0; i < 5; i++) {
		if (select.options[i].selected) {
			var objet = select.options[i].value;
			break;
		}
	}
	switch (objet) {
		case 'tout' : gal_desactive_all(e, 'description|tags|datecreation|infos'); break;
		case 'desc' : gal_desactive_all(e, 'description'); break;
		case 'tags' : gal_desactive_all(e, 'tags'); break;
		case 'date' : gal_desactive_all(e, 'datecreation'); break;
		case 'info' : gal_desactive_all(e, 'infos'); break;
	}
}
function montrer(id) {
	var div = document.getElementById(id);
	if (div.style.display == '') {
		div.style.display = 'none';
	} else {
		var gal_new_object = document.getElementById('gal_new_object');
		var gal_ajout_imgs = document.getElementById('gal_ajout_imgs');
		var gal_display_f = document.getElementById('gal_display_f');
		if (gal_new_object) { gal_new_object.style.display = 'none'; }
		if (gal_ajout_imgs) { gal_ajout_imgs.style.display = 'none'; }
		if (gal_display_f) { gal_display_f.style.display = 'none'; }
		div.style.display  = '';
	}
}
function add_upload_imgs() {
	var n = 2;
	while (document.getElementById('file_' + n)) {
		n++;
	}

	if (n < 11) {
		var gai = document.getElementById('gal_ajout_imgs_inputs');
	
		var div = document.createElement('div');
		gai.appendChild(div);

		var input_file = document.createElement('input');
		input_file.setAttribute('name', 'file_' + n);
		input_file.setAttribute('id', 'file_' + n);
		input_file.setAttribute('type', 'file');
		input_file.setAttribute('class', 'gal_files');
		input_file.setAttribute('size', '60');
		input_file.setAttribute('maxlength', '512');
		
		div.appendChild(input_file);
	}

}
function confirm_sup_obj(lien, type) {
	if (type == 'image') {
		type = 'cette image, ainsi que les commentaires et votes associés';
	} else if (type == 'album') {
		type = 'cet album, toutes ses images, commentaires et votes qu\'il contient,';
	} else {
		type = 'cette catégorie et tout ce qu\'elle contient (images, albums, commentaires et votes),';
	}
	if (confirm('Cette action supprimera définitivement ' + type + ' du disque et de la base de données.\r\nÊtes-vous sûr de vouloir continuer ?')) {
		window.location = lien;
	}
}
function gal_select_all(e) {
	var checkbox = document.getElementsByTagName('input');
	for (var i = 0; i < checkbox.length; i++) {
		if (checkbox[i].id.search(/objet_id/gi) != -1) {
			checkbox[i].checked = (e) ? true : false;
		}
	}
}
function confirm_albums_mass(o) {
	var select = document.getElementById('gal_mass_action_select');
	if (!select) {
		return false;
	}
	if (!select.options[2].selected) {
		return true;
	}
	var type = (o == 'img') ? 'images sélectionnées, ainsi que les commentaires et votes associés' : 'albums ou catégories sélectionnés et tout ce qu\'ils contiennent (images, albums, commentaires et votes)';
	
	if (confirm('Cette action supprimera définitivement toutes les ' + type + '.\r\nÊtes-vous sûr de vouloir continuer ?')) {
		return true;
	} else {
		return false;
	}
}



/*
 *	Section commentaires.
*/
function co_partied(p) {
	var partie = document.getElementById(p);
	if (!partie) {
		return;
	}
	if (partie.style.display == '') {
		partie.style.display = 'none';
	} else {
		partie.style.display = '';
		if (p == 'co_search') {
			document.getElementById('co_search_itext').focus();
		}
	}
}
function highlight_search() {
	var hl = document.getElementsByTagName('span');
	for (var i = 0; i < hl.length; i++) {
		if (hl[i].className == 's_hl') {
			hl[i].className = 's_hl search_hl';
		} else if (hl[i].className == 's_hl search_hl') {
			hl[i].className = 's_hl';
		}
	}
}
function confirm_sup_comment(lien) {
	if (confirm('Cette action supprimera définitivement ce commentaire.\r\nÊtes-vous sûr de vouloir continuer ?')) {
		window.location = lien;
	}
}
function confirm_comment_mass() {
	var select = document.getElementById('co_mass_action_select');
	if (!select) {
		return false;
	}
	if (!select.options[2].selected) {
		return true;
	}
	if (confirm('Cette action supprimera définitivement les commentaires sélectionnés.\r\nÊtes-vous sûr de vouloir continuer ?')) {
		return true;
	} else {
		return false;
	}
}
function comment_display(id) {
	var comment_msg = document.getElementById('comment_msg_' + id);
	if (!comment_msg) {
		return;
	}
	comment_msg.style.display = (comment_msg.style.display == '') ? 'none' : '';
}
function comment_display_all(e) {
	var comments_msg = document.getElementsByTagName('div');
	for (var i = 0; i < comments_msg.length; i++) {
		if (comments_msg[i].className == 'comment_msg') {
			comments_msg[i].style.display = (e) ? '' : 'none';
		}
	}
}
function comment_select_all(e) {
	var checkbox = document.getElementsByTagName('input');
	for (var i = 0; i < checkbox.length; i++) {
		if (checkbox[i].id.search(/comment_id/gi) != -1) {
			checkbox[i].checked = (e) ? true : false;
		}
	}
}




/*
 *	Section "Options".
*/
function change_theme(theme) {
	var select = document.getElementById('choix_style');

	// On détermine le thème sélectionné.
	var nom;
	for (var i = 0; i < theme.options.length; i++) {
		if (theme.options[i].selected) {
			nom = theme.options[i].value;
			break;
		}
	}
	if (!themes_styles[nom]) {
		return;
	}

	// On supprime les éléments existants de la liste.
	var items = select.length;
	for (var i = 0; i < items; i++) {
		select.removeChild(select.options[0]);
	}

	// On ajoute les styles à la liste correspondant au thème choisi.
	for (var i = 0; i < themes_styles[nom].length; i++) {
		var option = document.createElement('option');
		var option_text = document.createTextNode(themes_styles[nom][i]);
		option.setAttribute('value', themes_styles[nom][i]);
		option.appendChild(option_text);
		select.appendChild(option);
	}
}



/*
 *	Section "EXIF".
*/
function exif_display_details(id) {
	var exif_details = document.getElementById('exif_param_details_' + id);
	if (exif_details.style.display == 'none') {
		exif_display_all();
		exif_details.style.display = '';
		document.getElementById('exif_param_tag_' + id).focus();
	} else {
		exif_details.style.display = 'none';
	}
}
function exif_display_all(e) {
	var comments_msg = document.getElementsByTagName('div');
	for (var i = 0; i < comments_msg.length; i++) {
		if (comments_msg[i].className == 'exif_param_details') {
			comments_msg[i].style.display = (e) ? '' : 'none';
		}
	}
}
function exif_new_enum(id) {
	var exif_enum = document.getElementById('exif_param_liste_' + id);
	if (!exif_enum) {
		return;
	}

	/* Détermination du numéro de la liste */
	var num = 0;
	var exif_enums = document.getElementById('exif_param_valeurs_liste_' + id + '_' + num);
	while (exif_enums) {
		num++;
		exif_enums = document.getElementById('exif_param_valeurs_liste_' + id + '_' + num);
	}

	/* Création des éléments HTML */
	var div = document.createElement('div');
		div.setAttribute('class', 'exif_param_valeurs_liste');
		div.setAttribute('id', 'exif_param_valeurs_liste_' + id + '_' + num);
	var label_tag = document.createElement('label');
		label_tag.setAttribute('for', 'exif_param_liste_' + id + '_tag_' + num);
	var label_tag_text = document.createTextNode('valeur tag : ');
	var input_tag = document.createElement('input');
		input_tag.setAttribute('id', 'exif_param_liste_' + id + '_tag_' + num);
		input_tag.setAttribute('name', 'exif_param_liste[' + id + '][tag][' + num + ']');
		input_tag.setAttribute('type', 'text');
		input_tag.setAttribute('class', 'text');
		input_tag.setAttribute('maxlength', '128');
		input_tag.setAttribute('size', '5');
	var label_display = document.createElement('label');
		label_display.setAttribute('for', 'exif_param_liste_' + id + '_display_' + num);
	var label_display_text = document.createTextNode('   texte à afficher : ');
	var input_display = document.createElement('input');
		input_display.setAttribute('id', 'exif_param_liste_' + id + '_display_' + num);
		input_display.setAttribute('name', 'exif_param_liste[' + id + '][display][' + num + ']');
		input_display.setAttribute('type', 'text');
		input_display.setAttribute('class', 'text');
		input_display.setAttribute('maxlength', '128');
		input_display.setAttribute('size', '40');
	var lien = document.createElement('a');
		lien.setAttribute('class', 'lien_js');
		lien.setAttribute('href', 'javascript:exif_delete_enum(' + id + ',' + num + ');');
	var lien_text = document.createTextNode('supprimer');
	var span = document.createElement('span');
		span .setAttribute('class', 'exif_param_liste_delete');

	label_tag.appendChild(label_tag_text);
	label_display.appendChild(label_display_text);
	lien.appendChild(lien_text);
	span.appendChild(lien);

	var newline = document.createTextNode("\r\n");
	div.appendChild(label_tag);
	div.appendChild(newline);
	div.appendChild(input_tag);
	div.appendChild(newline);
	div.appendChild(label_display);
	div.appendChild(newline);
	div.appendChild(input_display);
	div.appendChild(newline);
	div.appendChild(span);

	exif_enum.appendChild(div);
	document.getElementById('exif_param_liste_' + id + '_tag_' + num).focus();
}
function exif_delete_enum(id, num) {
	var exif_enum = document.getElementById('exif_param_valeurs_liste_' + id + '_' + num);
	if (!exif_enum) {
		return;
	}
	document.getElementById('exif_param_liste_' + id).removeChild(exif_enum);
}
function exif_change_method(method, id) {

	// On détermine la méthode sélectionnée.
	var m;
	for (var i = 0; i < method.options.length; i++) {
		if (method.options[i].selected) {
			m = method.options[i].value;
			break;
		}
	}

	// On montre la partie correspondant à la méthode choisie.
	switch (m) {
		case 'simple' :
		case 'nombre' :
		case 'date' :
			document.getElementById('exif_param_format_div_' + id).style.display = '';
			document.getElementById('exif_param_liste_' + id).style.display = 'none';
			break;
		case 'liste' :
			document.getElementById('exif_param_format_div_' + id).style.display = 'none';
			document.getElementById('exif_param_liste_' + id).style.display = '';
			break;
		default :
			document.getElementById('exif_param_format_div_' + id).style.display = 'none';
			document.getElementById('exif_param_liste_' + id).style.display = 'none';			
	}
	
}
function meta_reinit_params(m, vid) {
	if (confirm('Réinitialiser les paramètres remettra les valeurs par défaut et supprimera toutes vos modifications éventuelles et tous les nouveaux paramètres éventuels que vous avez créés.\r\nÊtes-vous sûr de vouloir continuer ?')) {
		window.location = 'index.php?section=options&page=infos_' + m + '&reinit_params=1' + vid;
	}
}
function exif_confirm_delete_param(section, tag, vid) {
	if (confirm('Êtes-vous sûr de vouloir supprimer ce paramètre ?')) {
		window.location = 'index.php?section=options&page=infos_exif&delete=' + section + '.' + tag + vid;
	}
}
function meta_select_all(e, t) {
	var checkbox = document.getElementsByTagName('input');
	for (var i = 0; i < checkbox.length; i++) {
		var regex = new RegExp(t + '_param\\[\\d+\\]', 'gi');
		if (checkbox[i].name.search(regex) != -1) {
			checkbox[i].checked = (e) ? true : false;
		}
	}
}



/*
 *	Coche / décoche les cases à cocher d'un formulaire.
*/
function inputCheck(e, f) {
	var inputs = document.getElementsByTagName('input');
	for (var i = 0; i < inputs.length; i++) {
		if (inputs[i].type == 'checkbox' && inputs[i].className == f) {
			inputs[i].checked = (e) ? true : false;
		}
	}
}



/*
 *	Aide contextuelle.
*/
function affiche_aide(id) {
	var aide = document.getElementById(id);
	if (!aide) { return };
	if (aide.style.display == 'none') {
		var p = document.getElementsByTagName('p');
		for (var i = 0; i < p.length; i++) {
			if (p[i].className == 'aide_contextuelle') {
				p[i].style.display = 'none';
			}
		}
		var span = document.getElementsByTagName('span');
		for (var i = 0; i < span.length; i++) {
			if (span[i].className == 'aide_contextuelle') {
				span[i].style.display = 'none';
			}
		}
		var div = document.getElementsByTagName('div');
		for (var i = 0; i < div.length; i++) {
			if (div[i].className == 'aide_contextuelle') {
				div[i].style.display = 'none';
			}
		}
		aide.style.display = '';
	} else {
		aide.style.display = 'none';
	}
}


/*
  * Section votes.
*/
function votes_invert_select() {
	var td = document.getElementsByTagName('td');
	for (var i = 0; i < td.length; i++) {
		if (td[i].className == 'vote_selection') {
			if (td[i].getElementsByTagName('input')[0].checked == true) {
				td[i].getElementsByTagName('input')[0].checked = '';
			} else {
				td[i].getElementsByTagName('input')[0].checked = 'checked';
			}
		}
	}	
}
function votes_all_select() {
	var td = document.getElementsByTagName('td');
	for (var i = 0; i < td.length; i++) {
		if (td[i].className == 'vote_selection') {
			td[i].getElementsByTagName('input')[0].checked = 'checked';
		}
	}	
}
function confirm_sup_votes(lien,obj) {
	if (confirm('Voulez-vous vraiment supprimer tous les votes de ' + obj + ' ?')) {
		window.location = lien;
	}
}



/*
  *	Section utilisateurs : membres
 */
function membre_details(id) {
	var membre_details = document.getElementById('users_membres_details_' + id);
	if (!membre_details) {
		return;
	}
	membre_details.style.display = (membre_details.style.display == '') ? 'none' : '';
}
function membre_details_all(e) {
	var div = document.getElementsByTagName('div');
	for (var i = 0; i < div.length; i++) {
		if (div[i].className == 'users_membres_details') {
			div[i].style.display = (e) ? '' : 'none';
		}
	}
}
function membres_select_all(e) {
	var checkbox = document.getElementsByTagName('div');
	for (var i = 0; i < checkbox.length; i++) {
		if (checkbox[i].className == 'users_membres_checkbox') {
			checkbox[i].getElementsByTagName('input')[0].checked = (e) ? true : false;
		}
	}
}
function membres_invert_select() {
	var checkbox = document.getElementsByTagName('div');
	for (var i = 0; i < checkbox.length; i++) {
		if (checkbox[i].className == 'users_membres_checkbox') {
			if (checkbox[i].getElementsByTagName('input')[0].checked == true) {
				checkbox[i].getElementsByTagName('input')[0].checked = '';
			} else {
				checkbox[i].getElementsByTagName('input')[0].checked = 'checked';
			}
		}
	}	
}
function confirm_membres_delete() {
	if (confirm('Êtes-vous sûr de vouloir supprimer les membres sélectionnés ?')) {
		return true;
	} else {
		return false;
	}
}
function confirm_sup_groupe() {
	if (confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?\nEn supprimant ce groupe, tous les membres qui en font partie feront désormais partie du groupe "membres".')) {
		return true;
	} else {
		return false;
	}
}



/*
  *	Section utilisateurs : images en attente
 */
function imgatt_details(id) {
	var images_details = document.getElementById('users_images_details_' + id);
	if (!images_details) {
		return;
	}
	images_details.style.display = (images_details.style.display == '') ? 'none' : '';
}
function imgatt_details_all(e) {
	var div = document.getElementsByTagName('div');
	for (var i = 0; i < div.length; i++) {
		if (div[i].className == 'users_images_details') {
			div[i].style.display = (e) ? '' : 'none';
		}
	}
}
function imgatt_select_all(e) {
	var checkbox = document.getElementsByTagName('div');
	for (var i = 0; i < checkbox.length; i++) {
		if (checkbox[i].className == 'users_images_checkbox') {
			checkbox[i].getElementsByTagName('input')[0].checked = (e) ? true : false;
		}
	}
}
function imgatt_invert_select() {
	var checkbox = document.getElementsByTagName('div');
	for (var i = 0; i < checkbox.length; i++) {
		if (checkbox[i].className == 'users_images_checkbox') {
			if (checkbox[i].getElementsByTagName('input')[0].checked == true) {
				checkbox[i].getElementsByTagName('input')[0].checked = '';
			} else {
				checkbox[i].getElementsByTagName('input')[0].checked = 'checked';
			}
		}
	}	
}
function confirm_imgatt_delete() {
	var select = document.getElementById('users_images_action');
	if (!select) {
		return false;
	}
	if (!select.options[1].selected) {
		return true;
	}
	if (confirm('Êtes-vous sûr de vouloir supprimer toutes les images sélectionnées ?')) {
		return true;
	} else {
		return false;
	}
}