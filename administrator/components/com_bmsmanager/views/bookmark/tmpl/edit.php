<?php
defined('_JEXEC') or die;

$popular	= '';
if ($this->bookmark->popular == 1) {
	$popular	.= 'checked="checked"';
}

$egsb	= JHTML::image( bmsmanagerAssetsPath .'egsb.gif', JText::_( 'SIZE_SMALL' ) );
$eglb	= JHTML::image( bmsmanagerAssetsPath .'eglb.gif', JText::_( 'SIZE_LARGE' ) );
$egb	= JHTML::image( bmsmanagerAssetsPath .'egbutton.gif', JText::_( 'SIZE_BTN' ) );
$egc	= JHTML::image( bmsmanagerAssetsPath .'egcustom.gif', JText::_( 'SIZE_CSTM' ) );
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">

<fieldset class="adminform">
	<legend>
		<?php echo JText::_( 'Bookmark Details' ); ?>
	</legend>
	<table class="admintable">
	<tr>
		<td width="100" align="right" class="key">
			<label for="name">
				<?php echo JText::_( 'Name' ); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="name" id="name" size="94" maxlength="20" value="<?php echo $this->bookmark->name;?>"/>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label for="text">
				<?php echo JText::_( 'Text' ); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="text" id="text" size="94" maxlength="50" value="<?php echo $this->bookmark->text;?>"/>
		</td>
	</tr>
	<!--<tr>
		<td width="100" align="right" class="key">
			<label for="popular">
				<?php echo JText::_( 'Visible' ); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="hidden" name="popular" id="popular" <?php echo $popular;?>/>
		</td>
	</tr>-->
	<tr>
		<td width="100" align="right" class="key">
			<label for="size">
				<?php echo JText::_( 'Default size' ); ?>
			</label>
		</td>
		<td>
			<?php echo $this->lists['size']; ?>
		</td>
	</tr>
	<!--<tr>
		<td width="100" align="right" class="key">
			<label for="icon">
				<?php echo JText::_( 'Icon Path' ); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="hidden" name="icon" id="icon" size="94" maxlength="250" value="<?php echo $this->bookmark->icon;?>"/>
		</td>
	</tr>-->
	</table>
</fieldset>

<fieldset class="adminform">
	<legend>
		<?php echo JText::_( 'HTML Code' ); ?>
	</legend>
	<table class="adminlist">
	<thead>
		<tr>
			<th colspan="2">
				<?php echo JText::_( 'ANCHOR_NOTICE' ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr><td colspan="2">&nbsp;</td></tr>
	</tfoot>
	<tbody>
		<tr>
			<td width="100" align="right" class="item">
				<label for="htmltext">
					<?php echo JText::_( 'SIZE_TEXT' ); ?><br/>
				</label>
			</td>
			<td>
				<textarea class="text_area" cols="99" rows="4" name="htmltext" id="htmltext"><?php echo $this->bookmark->htmltext; ?></textarea>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="item">
				<label for="htmlsmall">
					<?php echo JText::_( 'SIZE_SMALL' ); ?><br/>
				</label>
			</td>
			<td>
				<textarea class="text_area" cols="99" rows="4" name="htmlsmall" id="htmlsmall"><?php echo $this->bookmark->htmlsmall; ?></textarea>
			</td>
		</tr>
	</tbody>
	</table>
</fieldset>
</div>
<div class="clr"></div>

	<input type="hidden" name="option" value="com_bmsmanager"/>
	<input type="hidden" name="id" value="<?php echo $this->bookmark->id; ?>"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="controller" value="bookmark"/>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
