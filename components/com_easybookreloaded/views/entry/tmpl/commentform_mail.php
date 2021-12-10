<?php
/**
 * Name:			Easybook Reloaded
 * Based on: 		Easybook by http://www.easy-joomla.org
 * License:    		GNU/GPL
 * Project Page: 	http://www.kubik-rubik.de/joomla-hilfe/komponente-easybook-reloaded-joomla
 */
 
defined( '_JEXEC' ) or die( 'Restricted access' );
$option = JRequest::getVar('hash', '', '', 'BASE64');
?>
<div id="easybook">
	<?php if ($this->params->get('show_page_title', 1)) 
	{ ?>
		<h2 class="componentheading"><?php echo $this->heading ?></h2>
	<?php } ?>
	<div class="easy_entrylink">
		<strong><a class="view" href="<?php echo JRoute::_('index.php?option=com_easybookreloaded'); ?>"><?php echo JText::_( 'Read Guestbook'); ?><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/book.png', JText::_('Read Guestbook').":", 'height="16" border="0" width="16" class="png" style="vertical-align: middle;"'); ?></a></strong>
<br /><br />
<script type="text/javascript">
	function x () {
    	return;
	}
 
 	function gb_smilie(thesmile) {
   		document.gbookForm.gbcomment.value += " "+thesmile+" ";
    	document.gbookForm.gbcomment.focus();
  	}
  	
	<?php if ($this->params->get('support_bbcode', false)) { ?>
    function DoPrompt(action) {
      	var revisedMessage;
      	var currentMessage = document.gbookForm.gbcomment.value;
      	<?php if ($this->params->get('support_link', false)) { ?>
      	if (action == "url") {
      		var thisURL = prompt("<?php echo JTEXT::_('Enter the URL here'); ?>", "http://");
      		var thisTitle = prompt("<?php echo JTEXT::_('Enter the web page title'); ?>", "<?php echo JTEXT::_('web page title'); ?>");
      		var urlBBCode = "[URL="+thisURL+"]"+thisTitle+"[/URL]";
      		revisedMessage = currentMessage+urlBBCode;
        	document.gbookForm.gbcomment.value=revisedMessage;
        	document.gbookForm.gbcomment.focus();
        	return;
    	}
    	<?php } ?>
    	<?php if ($this->params->get('support_mail', true)) { ?>
      	if (action == "email") {
      		var thisEmail = prompt("<?php echo JTEXT::_('Enter the e-mail address'); ?>", "");
      		var emailBBCode = "[EMAIL]"+thisEmail+"[/EMAIL]";
      		revisedMessage = currentMessage+emailBBCode;
      		document.gbookForm.gbcomment.value=revisedMessage;
      		document.gbookForm.gbcomment.focus();
      		return;
      	}
      	<?php } ?>
      	if (action == "bold") {
   			var thisBold = prompt("<?php echo JTEXT::_('Enter the text which should appear bold'); ?>", "");
		    var boldBBCode = "[B]"+thisBold+"[/B]";
		    revisedMessage = currentMessage+boldBBCode;
		    document.gbookForm.gbcomment.value=revisedMessage;
		    document.gbookForm.gbcomment.focus();
		    return;
   		}
  		if (action == "italic") {
		    var thisItal = prompt("<?php echo JTEXT::_('Enter the text which should appear italic'); ?>", "");
		    var italBBCode = "[I]"+thisItal+"[/I]";
		    revisedMessage = currentMessage+italBBCode;
		    document.gbookForm.gbcomment.value=revisedMessage;
		    document.gbookForm.gbcomment.focus();
			return;
		}
      	if (action == "underline") {
	      	var thisUndl = prompt("<?php echo JTEXT::_('Enter the text which should be underlined'); ?>", "");
	      	var undlBBCode = "[U]"+thisUndl+"[/U]";
	      	revisedMessage = currentMessage+undlBBCode;
	     	document.gbookForm.gbcomment.value=revisedMessage;
	     	document.gbookForm.gbcomment.focus();
	     	return;
     	}
      	if (action == "quote") {
		    var quoteBBCode = "[QUOTE]  [/QUOTE]";
		    revisedMessage = currentMessage+quoteBBCode;
		    document.gbookForm.gbcomment.value=revisedMessage;
		    document.gbookForm.gbcomment.focus();
		    return;
      	}
      	if (action == "code") {
			var thisLanguage = prompt("<?php echo JTEXT::_('Which language'); ?>", "");
	      	var codeBBCode = "[CODE="+thisLanguage+"]\n\n[/CODE]";
	      	revisedMessage = currentMessage+codeBBCode;
	      	document.gbookForm.gbcomment.value=revisedMessage;
	      	document.gbookForm.gbcomment.focus();
	      	return;
     	}
      	if (action == "listopen") {
	      	var liststartBBCode = "[LIST]";
	      	revisedMessage = currentMessage+liststartBBCode;
	      	document.gbookForm.gbcomment.value=revisedMessage;
	     	document.gbookForm.gbcomment.focus();
	      	return;
      	}
      	if (action == "listclose") {
	      	var listendBBCode = "[/LIST]";
	      	revisedMessage = currentMessage+listendBBCode;
	      	document.gbookForm.gbcomment.value=revisedMessage;
	      	document.gbookForm.gbcomment.focus();
	      	return;
      	}
      	if (action == "listitem") {
	      	var thisItem = prompt("<?php echo JTEXT::_('Enter the new list element. A group of list itmes must always be surrounded by an open-list and a close-list element'); ?>", "");
	      	var itemBBCode = "[*]"+thisItem;
	      	revisedMessage = currentMessage+itemBBCode;
	      	document.gbookForm.gbcomment.value=revisedMessage;
	      	document.gbookForm.gbcomment.focus();
	      	return;
      	}
      	<?php if ($this->params->get('support_pic', false)) { ?>
      	if (action == "image") {
	      	var thisImage = prompt("<?php echo JTEXT::_('Enter the URL of the picture you want to show'); ?>", "http://");
	      	var imageBBCode = "[IMG]"+thisImage+"[/IMG]";
	      	revisedMessage = currentMessage+imageBBCode;
	      	if(thisImage || (typeof(thisImage) == 'string' && thisImage.length))
	      		document.gbookForm.gbcomment.value=revisedMessage;
	      	document.gbookForm.gbcomment.focus();
	      	return;
      	}
		if (action == "image_link") {
	      	var thisImage = prompt("<?php echo JTEXT::_('Enter the URL of the picture you want to show'); ?>", "http://");
			var thisURL = prompt("<?php echo JTEXT::_('Enter the URL here'); ?>", "http://");
	      	var imageBBCode = "[IMGLINK="+thisURL+"]"+thisImage+"[/IMGLINK]";
	      	revisedMessage = currentMessage+imageBBCode;
	      	if(thisImage || (typeof(thisImage) == 'string' && thisImage.length))
	      		document.gbookForm.gbcomment.value=revisedMessage;
	      	document.gbookForm.gbcomment.focus();
	      	return;
      	}
      	<?php } ?>
      }
 <?php } ?>
</script>
<form name='gbookForm' action='<?php JRoute::_('index.php'); ?>' target='_top' method='post'>
	<input type='hidden' name='option' value='com_easybookreloaded' />
	<input type="hidden" name='task' value='savecomment_mail'/>
	<input type='hidden' name='id' value='<?php echo $this->entry->id; ?>' />
	<input type='hidden' name='hash' value='<?php echo $option; ?>' />

	<table align='center' width='90%' cellpadding='0' cellspacing='4' border='0' >
   	<?php // Switch for BB Code support
    if ($this->params->get('support_bbcode', false)) 
	{ ?>
		<tr>
			<td width='130'></td>
			<td>
			<?php if ($this->params->get('support_link', false)) 
			{ ?>
				<a href='javascript:%x()' onclick='DoPrompt("url");'><img src='<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/world_link.png' hspace='3' border='0' alt='' title='<?php echo JTEXT::_('Web address'); ?>' height='16' width='16' /></a>
			<?php }
      		if ($this->params->get('support_mail', true)) 
			{ ?>
				<a href='javascript:%x()' onclick='DoPrompt("email");'><img src='<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/email_link.png' hspace='3' border='0' alt='' title='<?php echo JTEXT::_('E-mail address'); ?>' height='16' width='16' /></a>
			<?php }
      		if ($this->params->get('support_pic', false)) 
			{ ?>
				<a href='javascript:%x()' onclick='DoPrompt("image_link");'><img src='<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/picture_link.png' hspace='3' border='0' alt='' title='<?php echo JTEXT::_('Shows image with a link'); ?>' height='16' width='16' /></a>
			<?php }      		
			if ($this->params->get('support_pic', false)) 
			{ ?>
				<a href='javascript:%x()' onclick='DoPrompt("image");'><img src='<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/picture.png' hspace='3' border='0' alt='' title='<?php echo JTEXT::_('Shows image from an url'); ?>' height='16' width='16' /></a>
			<?php }
			if ($this->params->get('support_code', false)) 
			{ ?>
				<a href='javascript:%x()' onclick='DoPrompt("code");'><img src='<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/code.png' hspace='3' border='0' alt='' title='<?php echo JTEXT::_('Code'); ?>' height='16' width='16' /></a>
			<?php } ?>
			<a href='javascript:%x()' onclick='DoPrompt("bold");'><img src='<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/text_bold.png' hspace='3' border='0' alt='Bold' title='<?php echo JTEXT::_('Bold'); ?>' height='16' width='16' /></a>
			<a href='javascript:%x()' onclick='DoPrompt("italic");'><img src='<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/text_italic.png' hspace='3' border='0' alt='Italic' title='<?php echo JTEXT::_('Italic'); ?>' height='16' width='16' /></a>
			<a href='javascript:%x()' onclick='DoPrompt("underline");'><img src='<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/text_underline.png' hspace='3' border='0' alt='Underline' title='<?php echo JTEXT::_('Underline'); ?>' height='16' width='16' /></a>
			</td>
		</tr>
	<?php } ?>
		<tr>
   			<td width='130' valign='top'><?php echo JTEXT::_('ADMIN COMMENT'); ?>
   			<br />
   			<br />
   			<?php
			# Switch for Smilie Support
			if ($this->params->get('support_smilie', true)) 
			{
				$count=1;
				$smiley = EasybookReloadedHelperSmilie::getSmilies();
				
				foreach ($smiley as $i=>$sm) 
				{
					if ($this->params->get('smilie_set') == 0) 
					{
						echo "<a href=\"javascript:gb_smilie('$i')\" title='$i'>". JHTML::_('image', 'components/com_easybookreloaded/images/smilies/'.$sm, $sm, 'border="0"' )."</a> ";
					}
					else
					{
						echo "<a href=\"javascript:gb_smilie('$i')\" title='$i'>". JHTML::_('image', 'components/com_easybookreloaded/images/smilies2/'.$sm, $sm, 'border="0"' )."</a> ";
					}
					if ($count%4==0) 
					{
						echo "<br />";
					}
						$count++;
				}
			}
			?>
   		 	</td>
    		<td valign='top'><textarea style='width:245px;' rows='8' cols='50' name='gbcomment' class='inputbox'><?php echo $this->entry->gbcomment; ?></textarea></td>
    	</tr>
		<?php // Buttons wie im Eingabeformular - 2.0.1 ?>
		<tr>
			<td width='130'><br /><input type='reset' value='<?php echo JTEXT::_('Reset form'); ?>' name='reset' class='button' /></td>
			<td style='padding-left: 130px;'><br /><input type='submit' name='send' value='<?php echo JTEXT::_('Submit entry'); ?>' class='button' /></td>
		</tr>
	</table>
</form>
<?php if ($this->params->get('show_logo', true) == 1) 
{ ?>
	<p id="easyfooter">
		<a href="http://www.kubik-rubik.de/" title="Easybook Reloaded - Erweiterung by Kubik-Rubik.de - Viktor Vogel" target="_blank"><img src="<?php echo JURI::base(); ?>components/com_easybookreloaded/images/logo_sm<?php if ($this->params->get('template_dark') == 1) { echo '_dark'; } ?>.png" class="png" alt="EasyBook Reloaded - Logo" title="Easybook Reloaded - Erweiterung by Kubik-Rubik.de - Viktor Vogel" border="0" width="138" height="48" /></a>
	</p>
<?php } 
elseif ($this->params->get('show_logo', true) == 2) 
{ ?>
	<p id="easyfooter">
		<a href="http://www.kubik-rubik.de/" title="Easybook Reloaded - Erweiterung by Kubik-Rubik.de - Viktor Vogel" target="_blank">Easybook Reloaded by Kubik-Rubik.de</a>
	</p>
<?php } ?>
</div>
</div>
