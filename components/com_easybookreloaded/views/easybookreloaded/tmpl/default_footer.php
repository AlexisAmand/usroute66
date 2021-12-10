<?php
defined('_JEXEC') or die('Restricted access');
// Title Tag für Logo - 2.0.1
//Load Footer
if ($this->params->get('show_logo', true) == 1) 
{ ?>
	<p id="easyfooter">
		<a href="http://www.kubik-rubik.de/" title="Easybook Reloaded - Erweiterung by Kubik-Rubik.de - Viktor Vogel" target="_blank">
			<img src="<?php echo JURI::base(); ?>components/com_easybookreloaded/images/logo_sm<?php if ($this->params->get('template_dark') == 1) { echo '_dark'; } ?>.png" class="png" alt="EasyBook Reloaded - Logo" title="Easybook Reloaded - Erweiterung by Kubik-Rubik.de - Viktor Vogel" border="0" width="138" height="48" />
		</a>
	</p>
<?php } 
elseif ($this->params->get('show_logo', true) == 2) 
{ ?>
	<p id="easyfooter">
		<a href="http://www.kubik-rubik.de/" title="Easybook Reloaded - Erweiterung by Kubik-Rubik.de - Viktor Vogel" target="_blank">Easybook Reloaded by Kubik-Rubik.de</a>
	</p>
<?php } ?>