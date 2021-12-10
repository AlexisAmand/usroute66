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
<!-- Easybook Reloaded <?php echo _EASYBOOK_VERSION; ?> by Kubik-Rubik.de -->
<div id="easybook">
	<?php if ($this->params->get('show_page_title', 1)) 
	{ ?>
		<h2 class="componentheading"><?php echo $this->heading ?></h2>
	<?php } ?>
	<div class="easy_entrylink">
	<strong><a class="view" href="<?php echo JRoute::_('index.php?option=com_easybookreloaded&view=easybookreloaded'); ?>"><?php echo JText::_( 'Read Guestbook'); ?><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/book.png', JText::_('Read Guestbook').":", 'height="16" border="0" width="16" class="png" style="vertical-align: middle;"'); ?></a></strong>
<br />
<br />
<script type="text/javascript">
	function x () {
    	return;
	}
 
 	function gb_smilie(thesmile) {
   		document.gbookForm.gbtext.value += " "+thesmile+" ";
    	document.gbookForm.gbtext.focus();
  	}
  	
	<?php if ($this->params->get('support_bbcode', false)) 
	{ ?>
    function DoPrompt(action) {
      	var revisedMessage;
      	var currentMessage = document.gbookForm.gbtext.value;
      	<?php if ($this->params->get('support_link', false)) 
		{ ?>
			if (action == "url") {
				var thisURL = prompt("<?php echo JTEXT::_('Enter the URL here'); ?>", "http://");
				var thisTitle = prompt("<?php echo JTEXT::_('Enter the web page title'); ?>", "<?php echo JTEXT::_('web page title'); ?>");
				var urlBBCode = "[URL="+thisURL+"]"+thisTitle+"[/URL]";
				revisedMessage = currentMessage+urlBBCode;
				document.gbookForm.gbtext.value=revisedMessage;
				document.gbookForm.gbtext.focus();
				return;
			}
    	<?php } ?>
    	<?php if ($this->params->get('support_mail', true)) 
		{ ?>
			if (action == "email") {
				var thisEmail = prompt("<?php echo JTEXT::_('Enter the e-mail address'); ?>", "");
				var emailBBCode = "[EMAIL]"+thisEmail+"[/EMAIL]";
				revisedMessage = currentMessage+emailBBCode;
				document.gbookForm.gbtext.value=revisedMessage;
				document.gbookForm.gbtext.focus();
				return;
			}
      	<?php } ?>
      	if (action == "bold") {
   			var thisBold = prompt("<?php echo JTEXT::_('Enter the text which should appear bold'); ?>", "");
		    var boldBBCode = "[B]"+thisBold+"[/B]";
		    revisedMessage = currentMessage+boldBBCode;
		    document.gbookForm.gbtext.value=revisedMessage;
		    document.gbookForm.gbtext.focus();
		    return;
   		}
  		if (action == "italic") {
		    var thisItal = prompt("<?php echo JTEXT::_('Enter the text which should appear italic'); ?>", "");
		    var italBBCode = "[I]"+thisItal+"[/I]";
		    revisedMessage = currentMessage+italBBCode;
		    document.gbookForm.gbtext.value=revisedMessage;
		    document.gbookForm.gbtext.focus();
			return;
		}
      	if (action == "underline") {
	      	var thisUndl = prompt("<?php echo JTEXT::_('Enter the text which should be underlined'); ?>", "");
	      	var undlBBCode = "[U]"+thisUndl+"[/U]";
	      	revisedMessage = currentMessage+undlBBCode;
	     	document.gbookForm.gbtext.value=revisedMessage;
	     	document.gbookForm.gbtext.focus();
	     	return;
     	}
      	if (action == "quote") {
		    var quoteBBCode = "[QUOTE]  [/QUOTE]";
		    revisedMessage = currentMessage+quoteBBCode;
		    document.gbookForm.gbtext.value=revisedMessage;
		    document.gbookForm.gbtext.focus();
		    return;
      	}
      	if (action == "code") {
			var thisLanguage = prompt("<?php echo JTEXT::_('Which language'); ?>", "");
	      	var codeBBCode = "[CODE="+thisLanguage+"]\n\n[/CODE]";
	      	revisedMessage = currentMessage+codeBBCode;
	      	document.gbookForm.gbtext.value=revisedMessage;
	      	document.gbookForm.gbtext.focus();
	      	return;
     	}
      	if (action == "listopen") {
	      	var liststartBBCode = "[LIST]";
	      	revisedMessage = currentMessage+liststartBBCode;
	      	document.gbookForm.gbtext.value=revisedMessage;
	     	document.gbookForm.gbtext.focus();
	      	return;
      	}
      	if (action == "listclose") {
	      	var listendBBCode = "[/LIST]";
	      	revisedMessage = currentMessage+listendBBCode;
	      	document.gbookForm.gbtext.value=revisedMessage;
	      	document.gbookForm.gbtext.focus();
	      	return;
      	}
      	if (action == "listitem") {
	      	var thisItem = prompt("<?php echo JTEXT::_('Enter the new list element. A group of list itmes must always be surrounded by an open-list and a close-list element'); ?>", "");
	      	var itemBBCode = "[*]"+thisItem;
	      	revisedMessage = currentMessage+itemBBCode;
	      	document.gbookForm.gbtext.value=revisedMessage;
	      	document.gbookForm.gbtext.focus();
	      	return;
      	}
      	<?php if ($this->params->get('support_pic', false)) 
		{ ?>
			if (action == "image") {
				var thisImage = prompt("<?php echo JTEXT::_('Enter the URL of the picture you want to show'); ?>", "http://");
				var imageBBCode = "[IMG]"+thisImage+"[/IMG]";
				revisedMessage = currentMessage+imageBBCode;
				if(thisImage || (typeof(thisImage) == 'string' && thisImage.length))
					document.gbookForm.gbtext.value=revisedMessage;
				document.gbookForm.gbtext.focus();
				return;
			}
			if (action == "image_link") {
				var thisImage = prompt("<?php echo JTEXT::_('Enter the URL of the picture you want to show'); ?>", "http://");
				var thisURL = prompt("<?php echo JTEXT::_('Enter the URL here'); ?>", "http://");
				var imageBBCode = "[IMGLINK="+thisURL+"]"+thisImage+"[/IMGLINK]";
				revisedMessage = currentMessage+imageBBCode;
				if(thisImage || (typeof(thisImage) == 'string' && thisImage.length))
					document.gbookForm.gbtext.value=revisedMessage;
				document.gbookForm.gbtext.focus();
				return;
			}
      	<?php } ?>
      }
 <?php } ?>
</script>
<form name='gbookForm' action='<?php JRoute::_('index.php'); ?>' target='_top' method='post'>
    <input type='hidden' name='option' value='com_easybookreloaded' />
    <input type='hidden' name='task' value='save_mail' />
	<?php if ($this->entry->id){ ?>
		<input type='hidden' name='id' value='<?php echo $this->entry->id; ?>' />
	<?php } ?>
	<input type='hidden' name='hash' value='<?php echo $option; ?>' />
	<input type='hidden' name='easycalccheck' value='<?php echo $this->session->get('spamcheckresult', null, 'easybookreloaded'); ?>' />
    <table align='center' width='90%' cellpadding='0' cellspacing='4' border='0' >
    
	<?php if ($this->params->get('enable_log', true)) 
	{ ?>
	<tr>
		<td width='130'><?php echo JTEXT::_('IP address'); ?><span class='small'>*</span></td>
		<td><input type='text' name='gbiip' style='width:245px;' class='inputbox' value='<?php echo $this->entry->ip; ?>' disabled='disabled' /></td>
	</tr>
	<?php } ?>
	
	<tr>
		<td width='130'><label for='gbname'><?php echo JTEXT::_('Name'); ?></label><span class='small'>*</span></td>
		<td><input type='text' name='gbname' id='gbname' style='width:245px;' class='inputbox' value='<?php echo $this->entry->gbname; ?>' /></td>
	</tr>
	
	<?php if ($this->params->get('show_mail', true) OR $this->params->get('require_mail', true)) 
	{ ?>
		<tr>
			<td width='130'><label for='gbmail'><?php echo JTEXT::_('E-mail'); ?></label>
			<?php if($this->params->get('require_mail', true)){
				echo "<span class='small'>*</span>";
			} ?>
			</td>
			<td><input type='text' name='gbmail' id='gbmail' style='width:245px;' class='inputbox' value='<?php echo $this->entry->gbmail; ?>' /></td>
		</tr>
		
		<?php if (!$this->entry->id)
		{ ?>
		<tr>
			<td width='130'><label for='gbmailshow'><?php echo JTEXT::_('Show e-mail in public'); ?></label></td>
			<td><input type='checkbox' name='gbmailshow' id='gbmailshow' class='inputbox' value='1' /></td>
		</tr>
		<?php } ?>
	<?php } 
	
	if ($this->params->get('show_home', true)) 
	{ ?>
	<tr>
		<td width='130'><label for='gbpage'><?php echo JTEXT::_('Homepage'); ?></label></td>
		<td><input type='text' name='gbpage' id='gbpage' style='width:245px;' class='inputbox' value='<?php echo $this->entry->gbpage; ?>' /></td>
	</tr>
	<?php }
	
	if ($this->params->get('show_loca', true)) 
	{ ?>
  	<tr>
  		<td width='130'><label for='gbloca'><?php echo JTEXT::_('Location'); ?></label></td>
  		<td><input type='text' name='gbloca' id='gbloca' style='width:245px;' class='inputbox' value='<?php echo $this->entry->gbloca; ?>' /></td>
  	</tr>
	<?php }
	
	if ($this->params->get('show_icq', true)) 
	{ ?>
    <tr>
    	<td width='130'><label for='gbicq'><?php echo JTEXT::_('ICQ number'); ?></label></td>
    	<td><input type='text' name='gbicq' id='gbicq' style='width:245px;' class='inputbox' value='<?php echo $this->entry->gbicq; ?>' /></td>
    </tr>
    <?php } 
	
	if ($this->params->get('show_aim', true)) 
	{ ?>
    <tr>
    	<td width='130'><label for='gbaim'><?php echo JTEXT::_('AIM nickname'); ?></label></td>
    	<td><input type='text' name='gbaim' id='gbaim' style='width:245px;' class='inputbox' value='<?php echo $this->entry->gbaim; ?>' /></td>
    </tr>
    <?php } 
	
	if ($this->params->get('show_msn', true)) 
	{ ?>
    <tr>
    	<td width='130'><label for='gbmsn'><?php echo JTEXT::_('MSN messenger'); ?></label></td>
    	<td><input type='text' name='gbmsn' id='gbmsn' style='width:245px;' class='inputbox' value='<?php echo $this->entry->gbmsn; ?>' /></td>
    </tr>
    <?php } 
	
	if ($this->params->get('show_yah', true)) 
	{ ?>
    <tr>
    	<td width='130'><label for='gbyah'><?php echo JTEXT::_('Yahoo messenger'); ?></label></td>
    	<td><input type='text' name='gbyah' id='gbyah' style='width:245px;' class='inputbox' value='<?php echo $this->entry->gbyah; ?>'/></td>
    </tr>
    <?php } 
	
	if ($this->params->get('show_skype', true)) 
	{ ?>
    <tr>
    	<td width='130'><label for='gbskype'><?php echo JTEXT::_('Skype nickname'); ?></label></td>
    	<td><input type='text' name='gbskype' id='gbskype' style='width:245px;' class='inputbox' value='<?php echo $this->entry->gbskype ?>' /></td>
    </tr>
	<?php } 
	
	if ($this->params->get('show_rating', true)) 
	{ ?>
	<tr>
		<td width='130'><label for='gbvote'><?php echo JTEXT::_('Website rating'); ?></label></td>
      	<td>
			<?php if ($this->params->get('show_rating_type') == 0) { ?>
      		<select style='width:130px;' class='inputbox' size='1' name='gbvote' id='gbvote'>
	 			<option value='0'><? echo JTEXT::_('Please rate'); ?></option>
	 			<?php
      			for ($i=1; $i<=$this->params->get('rating_max', 5); $i++) 
				{
        			if ((isset($this->entry->gbvote)) AND ($i == $this->entry->gbvote)) 
					{
						echo "<option selected=\"selected\">$i</option>";
					} 
					else 
					{
						echo "<option>$i</option>";
					}
     			}
     			?>
      		</select>
			<?php }
			elseif ($this->params->get('show_rating_type') == 1) 
			{
				echo "<input type='hidden' type='radio' name='gbvote' value='0' />";
				for ($i=1; $i<=$this->params->get('rating_max', 5); $i++) 
				{
					if ((isset($this->entry->gbvote)) AND ($i == $this->entry->gbvote)) 
					{
						echo $i.'<input type="radio" name="gbvote" value="'.$i.'" checked="checked">';
					} 
					else 
					{
						echo $i.'<input type="radio" name="gbvote" value="'.$i.'">';
					}
					
     			}
			} 
			if ($this->params->get('show_rating_hint', true)) 
			{ 
				echo "<br />".$this->params->get('rating_max', 5) ." - ". JTEXT::_('Best rating').", 1 - ". JTEXT::_('Worst rating');
			} ?>
		</td>
    </tr>	
    <?php } 
	else 
	{
		echo "<input type='hidden' name='gbvote' value='0' />";
    }
	// Switch for BB Code support
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
		<td width='130' valign='top'><label for='gbtext'><?php echo JTEXT::_('Guestbook entry'); ?></label><span class='small'>*</span>
		<br /><br />
		<?php // Switch for Smilie Support
		if ($this->params->get('support_smilie', true)) 
		{
			$count = 1;
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
		} ?>
		</td>
		<td valign='top'><textarea style='width:245px;' rows='8' cols='50' name='gbtext' id='gbtext' class='inputbox'><?php echo $this->entry->gbtext; ?></textarea></td>
	</tr>
    <tr>
    	<td width='130'><br /><input type='reset' value='<?php echo JTEXT::_('Reset form'); ?>' name='reset' class='button' /></td>
    	<td style='padding-left: 130px;'><br /><input type='submit' name='send' value='<?php echo JTEXT::_('Submit entry'); ?>' class='button' /></td>
    </tr>
    </table>
</form>
<p><span class='small' style='padding-left:400px;'>* <?php echo JTEXT::_('Required field'); ?></span></p>
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