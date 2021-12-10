<?php // no direct access
defined('_JEXEC') or die('Restricted access');
?>
<!-- Easybook Reloaded <?php echo _EASYBOOK_VERSION; ?> by Kubik-Rubik.de -->
<div id="easybook">
	<?php if ($this->params->get('show_page_title', 1)) : ?>
		<h2 class="componentheading"><?php echo $this->heading ?></h2>
	<?php endif; ?>
<div class="easy_entrylink">
<?php
if ($this->access->canAdd) 
{
	echo "<a class=\"sign\" href='".JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=add')."'>";
	echo "<b>".JText::_( 'Sign Guestbook');
	echo JHTML::_('image', 'components/com_easybookreloaded/images/new.png', JText::_('Sign Guestbook').":", 'height="16" border="0" width="16" class="png" style="vertical-align: middle;"') ."</b></a>";
}

if ($this->params->get('show_introtext')) 
{ ?>
	<div class='easy_intro'>
		<br /><?php echo nl2br($this->params->get('introtext')); ?>
	</div>
<?php } ?>
<br />
<br />

