<?php 
 /**
 * Easybook Reloaded
 * Based on: Easybook by http://www.easy-joomla.org
 * @license    GNU/GPL
 * Project Page: http://www.kubik-rubik.de/joomla-hilfe/komponente-easybook-reloaded-joomla
 */

defined('_JEXEC') or die('Restricted access');
//Load Header
echo $this->loadTemplate('header');
//Load Entrys
echo $this->loadTemplate('entrys'); ?>
<div>
	<br /><strong class='easy_pagination'><?php echo $this->count ?><br />
	<?php if ($this->count == 1) 
	{
		echo JText::_('Entry in the Guestbook');
	} 
	else 
	{
		echo JText::_('Entrys in the Guestbook');
	} ?>
	</strong>
</div>
<?php
//Load Pagenavigation
if ($this->pagination->total > $this->pagination->limit) 
{
	echo '<div class="easy_pagination">';
	echo $this->pagination->getPagesLinks();
	echo '</div>';
}
// Footer laden
echo $this->loadTemplate('footer');
?>
</div>
</div>