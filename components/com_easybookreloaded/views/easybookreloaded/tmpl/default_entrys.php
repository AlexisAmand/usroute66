<?php 
/**
 * Easybook Reloaded
 * Based on: Easybook by http://www.easy-joomla.org
 * @license    GNU/GPL
 * Project Page: http://www.kubik-rubik.de/joomla-hilfe/komponente-easybook-reloaded-joomla
 */
 
defined('_JEXEC') or die('Restricted access');

foreach ($this->entrys as $entry) 
{ ?>
	<div class="easy_frame" <?php if (!$entry->published) { ?> style="background-color: #fffefd; border: #ffb39b solid 1px;" <?php } ?>>
	<div class="easy_top" <?php if (!$entry->published) { ?> style="background-color: #FFE7D7;" <?php } ?>>
	<div class="easy_top_left">
		<strong class="easy_big" id="gbentry_<?php echo $entry->id; ?>"><?php echo $entry->gbname ?></strong>
		<strong class="easy_small">
		<?php if ($entry->published) 
		{ // Datumsformat international - 2.0.1
			if ($this->params->get('date_format') == 0)
			{
				echo JHTML::_('date', $entry->gbdate, JText::_('DATE_FORMAT_LC2'));
			}
			else
			{
				echo JHTML::_('date', $entry->gbdate, JText::_('DATE_FORMAT_LC1'));
			}
			
			if ($entry->gbloca) {
				echo ' | '.$entry->gbloca;
			}
		}
		
		if (!$entry->published) 
		{
			echo " | </strong><strong class='easy_small_red'>". JText::_( 'Entry offline'); 
		} ?>
		</strong>
	</div>
	<div class="easy_top_right">
		<?php
		//Voting
		if ($this->params->get('show_rating', true) AND $entry->gbvote !== "0") 
		{
			for ($start=1; $start<=$this->params->get('rating_max', 5); $start++) 
			{
				$ratimg = $entry->gbvote >= $start ? 'sun.png' : 'clouds.png';
				echo JHTML::_('image', 'components/com_easybookreloaded/images/'.$ratimg, JText::_('Rating'), 'border="0" class="easy_align_middle"');
			}
		}
		
		// Adminfunktionen
		// Tooltipps für Administrator - 2.0.1
		$acl = $this->access;
		
		if ($acl->canEdit || $acl->canRemove || $acl->canComment || $acl->canPublish) 
		{
			echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
		}
		
		if ($acl->canEdit)
		{
			echo "<a href='".JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=edit&cid='.(int)$entry->id)."' title='".JText::_('Edit Entry')."'>".JHTML::_('image', 'components/com_easybookreloaded/images/edit.png', JText::_('Edit Entry'), 'class="easy_align_middle" border="0"')."</a>&nbsp;&nbsp;\n";
		}
		
		if ($acl->canRemove)
		{
			echo "<a href='".JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=remove&cid='.(int)$entry->id)."' title='".JText::_('Delete Entry')."'>".JHTML::_('image', 'components/com_easybookreloaded/images/delete.png', JText::_('Delete Entry'), 'class="easy_align_middle" border="0"')."</a>&nbsp;&nbsp;\n";
		}
		
		if ($acl->canComment)
		{
			if ($entry->gbcomment != "")
			{
				echo "<a href='".JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=comment&cid='.(int)$entry->id)."' title='".JText::_('Edit Comment')."'>".JHTML::_('image', 'components/com_easybookreloaded/images/comment_edit.png', JText::_('Edit Comment'), 'class="easy_align_middle" border="0"')."</a>&nbsp;&nbsp;\n";
			} 
			else 
			{
				echo "<a href='".JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=comment&cid='.(int)$entry->id)."' title='".JText::_('Edit Comment')."'>".JHTML::_('image', 'components/com_easybookreloaded/images/comment.png', JText::_('Edit Comment'), 'class="easy_align_middle" border="0"')."</a>&nbsp;&nbsp;\n";
			}
		}
		
		if ($acl->canPublish) 
		{
			if ($entry->published == 0) 
			{
				echo "<a href='".JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=publish&cid='.(int)$entry->id)."' title='".JText::_('Publish Entry')."'>".JHTML::_('image', 'components/com_easybookreloaded/images/offline.png', JText::_('Publish Entry'), 'class="easy_align_middle" border="0"')."</a>\n";
			} 
			else 
			{
				echo "<a href='".JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=publish&cid='.(int)$entry->id)."' title='".JText::_('Unpublish Entry')."'>".JHTML::_('image', 'components/com_easybookreloaded/images/online.png', JText::_('Unpublish Entry'), 'class="easy_align_middle" border="0"')."</a>\n";
			}
		} ?>
	</div>
	<div style="clear: both;"></div>
	</div>
	<?php if (($entry->gbmail != "" AND $this->params->get('show_mail', true) AND $entry->gbmailshow) OR ($entry->gbpage != "" AND $this->params->get('show_home', true)) OR ($entry->gbicq != "" AND $this->params->get('show_icq', true)) OR ($entry->gbaim != "" AND $this->params->get('show_aim', true)) OR ($entry->gbmsn != "" AND $this->params->get('show_msn', true)) OR ($entry->gbyah != "" AND $this->params->get('show_yah', true)) OR ($entry->gbskype != "" AND $this->params->get('show_skype', true))) 
	{ ?>
		<div class='easy_contact'>		
			<?php
			//Display contact details if available
			//E-Mail
			if ($entry->gbmail != "" AND $this->params->get('show_mail', true) AND $entry->gbmailshow) 
			{
				$image = JHTML::_('image', 'components/com_easybookreloaded/images/email.png', '', 'height="16" width="16" class="png" hspace="3" border="0"');
				echo JHTML::_('email.cloak', $entry->gbmail, true, $image, false);
			}
			
			//Homepage
			if ($entry->gbpage != "" AND $this->params->get('show_home', true)) 
			{
				if (substr($entry->gbpage,0,7)!="http://") 
				{
					$entry->gbpage="http://$entry->gbpage";
				}
				echo "<a href=\"$entry->gbpage\" title=\"".JTEXT::_('Homepage')." - $entry->gbpage\" ";
				
				if ($this->params->get('nofollow_home', true)) 
				{
					echo "rel=\"nofollow\" ";
				}
				
				echo "target=\"_blank\">".JHTML::_('image', 'components/com_easybookreloaded/images/world.png', $entry->gbpage, 'height="16" width="16" class="png" hspace="3" border="0"')."</a>";
			}
			
			//ICQ
			if ($entry->gbicq != "" AND $this->params->get('show_icq', true)) 
			{
				echo "<a href=\"mailto:$entry->gbicq@pager.icq.com\">".JHTML::_('image', 'components/com_easybookreloaded/images/im-icq.png', $entry->gbicq, 'title="'.JTEXT::_('ICQ number').' - '.$entry->gbicq.'" border="0" height="16" width="16" class="png" hspace="3"')."</a>";
			}
			
			//AIM
			if ($entry->gbaim != "" AND $this->params->get('show_aim', true)) 
			{
				echo "<a href=\"aim:goim?screenname=$entry->gbaim\">".JHTML::_('image', 'components/com_easybookreloaded/images/im-aim.png', $entry->gbaim, 'title="'.JTEXT::_('AIM nickname').' - '.$entry->gbaim.'" border="0" height="16" width="16" class="png" hspace="3"')."</a>";
			}
			
			//MSN
			if ($entry->gbmsn != "" AND $this->params->get('show_msn', true)) 
			{
				echo JHTML::_('image', 'components/com_easybookreloaded/images/im-msn.png', $entry->gbmsn, 'title="'.JTEXT::_('MSN messenger').' - '.$entry->gbmsn.'" border="0" height="16" width="16" class="png" hspace="3"');
			}
			
			//Yahoo
			if ($entry->gbyah != "" AND $this->params->get('show_yah', true)) 
			{
				echo "<a href='ymsgr:sendIM?$entry->gbyah'>".JHTML::_('image', 'components/com_easybookreloaded/images/im-yahoo.png', $entry->gbyah, 'title="'.JTEXT::_('Yahoo messenger').' - '.$entry->gbyah.'" border="0" height="16" width="16" class="png" hspace="3"')."</a>";
			}
			
			//Skype
			if ($entry->gbskype != "" AND $this->params->get('show_skype', true)) 
			{
				echo "<a href='skype:" . $entry->gbskype . "?call'>".JHTML::_('image', 'components/com_easybookreloaded/images/im-skype.png', $entry->gbskype, 'title="'.JTEXT::_('Skype nickname').' - '.$entry->gbskype.'" border="0" height="16" width="16" class="png" hspace="3"')."</a>";
			} ?>
		</div>
	<?php } ?>
	<div class="easy_content">
		<?php echo EasybookReloadedHelperContent::parse($entry->gbtext) ?>
	</div>
	<?php if ($entry->gbcomment) { ?>
		<div class="easy_admincomment">
		<?php echo JHTML::_('image', 'components/com_easybookreloaded/images/admin.png', JText::_('Admin Comment:'), 'class="easy_align_middle" style="padding-bottom: 2px;"'); ?>
		<strong><?php echo JText::_( 'Admin Comment'); ?>:</strong><br />
		<?php echo EasybookReloadedHelperContent::parse($entry->gbcomment) ?>
		</div>
	<?php } ?>
	</div>
	<p class="clr"></p>
<?php } ?>
