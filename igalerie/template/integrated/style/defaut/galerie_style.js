/*
 *	Style additionnel sur vignettes pour IE <= 7.0.
*/
function style_vignettes() {
	if (navigator.userAgent.search(/MSIE [1-7]/) != -1) {
		var tb = document.getElementById('vignettes');
		var tb_ex = document.getElementById('vex_vignettes');

		// Vignettes en mode 'compact'.
		if (tb) {
			var lis = tb.getElementsByTagName('li');
			var bordercolor = (document.getElementById('vignettes_cat')) ? '#494949' : '#C0C0C0';
			for (var i = 0; i < lis.length; i++) {
				var cl = lis[i].className;
				if (cl && cl.search(/v_thumb/gi) != -1) {

					// La souris survole la vignette.
					lis[i].onmouseover = function() {
						this.getElementsByTagName('span')[1].style.borderColor = '#D58B0B';
						this.getElementsByTagName('span')[1].style.backgroundColor = '#FCF0DA';
					};

					// La souris ne survole plus la vignette.
					lis[i].onmouseout = function() {
						bc = (this.className.search(/v_recent/gi) != -1) ? '#BDCF7F' : bordercolor;
						bc = (this.className.search(/v_pass/gi) != -1) ? '#E9CB20' : bc;
						this.getElementsByTagName('span')[1].style.borderColor = bc;
						this.getElementsByTagName('span')[1].style.backgroundColor = '';
					};
				}
			}

		// Vignettes en mode 'étendu'.
		} else if (tb_ex) {
			var divs = tb_ex.getElementsByTagName('div');
			for (var i = 0; i < divs.length; i++) {
				var cl = divs[i].className;
				if (cl && cl.search(/vex_vignette/gi) != -1) {

					// La souris survole la vignette.
					divs[i].getElementsByTagName('table')[0].onmouseover = function() {
						this.getElementsByTagName('tr')[0].getElementsByTagName('td')[0].style.borderColor = '#D58B0B';
						this.getElementsByTagName('tr')[0].getElementsByTagName('td')[1].style.borderColor = '#D58B0B';
						this.getElementsByTagName('tr')[0].getElementsByTagName('td')[0].style.backgroundColor = '#FCF0DA';
						this.getElementsByTagName('tr')[0].getElementsByTagName('td')[1].style.backgroundColor = '#FCF0DA';
					};

					// La souris ne survole plus la vignette.
					divs[i].getElementsByTagName('table')[0].onmouseout = function() {
						bc_1 = (this.parentNode.className.search(/vex_recent/gi) != -1) ? '#BDCF7F' : '#656565';
						bc_1 = (this.parentNode.className.search(/vex_pass/gi) != -1) ? '#E9CB20' : bc_1;
						bc_2 = (this.parentNode.className.search(/vex_recent/gi) != -1) ? '#BDCF7F' : '#C0C0C0';
						bc_2 = (this.parentNode.className.search(/vex_pass/gi) != -1) ? '#E9CB20' : bc_2;
						this.getElementsByTagName('tr')[0].getElementsByTagName('td')[0].style.borderColor = bc_1;
						this.getElementsByTagName('tr')[0].getElementsByTagName('td')[1].style.borderColor = bc_2;
						this.getElementsByTagName('tr')[0].getElementsByTagName('td')[0].style.backgroundColor = '';
						this.getElementsByTagName('tr')[0].getElementsByTagName('td')[1].style.backgroundColor = '';
					};
				}
			}
		}
	}
}
