<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
    <table class="adminlist">
    <thead>
        
        <tr>
            <th width="20">
    			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th width="40">
                <?php echo JText::_( 'Author' ); ?>
            </th>
            <th>
                <?php echo JText::_( 'Message' ); ?>
            </th>
            <th width="12%">
                <?php echo JText::_( 'Date' ); ?>
            </th>
            <th width="2%">
                <?php echo JText::_( 'Rating' ); ?>
            </th>
            <th width="25%">
                <?php echo JText::_( 'Comment' ); ?>
            </th>
            <th width="2%">
                <?php echo JText::_( 'Published' ); ?>
            </th>
        </tr>            
    </thead>
    <?php
    $k = 0;
    for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
        $row =& $this->items[$i];
        $checked    = JHTML::_( 'grid.id', $i, $row->id );
        $published 	= JHTML::_('grid.published', $row, $i );
        $link = JRoute::_( 'index.php?option=com_easybookreloaded&controller=entry&task=edit&cid[]='. $row->id );
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td>
  				  <?php echo $checked; ?>
			</td>
            <td>
                <?php echo $row->gbname; ?>
            </td>
            <td>
                <span class="hasTip" title="<?php echo $row->gbtext?>"><a href="<?php echo $link ?>"><?php echo substr($row->gbtext,0,70)."..."; ?></a></span>
            </td>
            <td>
                <?php echo JHTML::_('date', $row->gbdate, "%d %B %Y %H:%M") ?>
            </td>
            <td>
                <?php echo $row->gbvote; ?>
            </td>
            <td>
                <?php if($row->gbcomment){ // Kubik-Rubik.de - Reloaded
					echo '<img src="images/tick.png" class="hasTip" title="'.$row->gbcomment.'" border="0" alt="Has a comment" /> ';
					if (strlen($row->gbcomment) > 50) {
						echo substr($row->gbcomment,0,50)."..."; 
					} else {
						echo $row->gbcomment;
					}
				}?>
            </td>
            <td>
                <?php echo $published; ?>
            </td>
        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
    <tfoot>
		<tr>
			<td colspan="7">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
    </table>
</div>

<input type="hidden" name="option" value="com_easybookreloaded" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="entry" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<div class="padding">
</div>

<div style="margin: 0 auto; width: 600px; text-align: center; background-color: #F3F3F3; padding: 7px; border: 1px solid #999999;">
	<span><?php echo $this->version; ?></span>
	<p><?php echo JText::_('SUPPORT THE FURTHER DEVELOPMENT AND FREE AVAILABILITY OF THE EASYBOOK WITH A SMALL DONATION - THANK YOU!'); ?></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<div>
<input type="hidden" name="cmd" value="_donations" />
<input type="hidden" name="business" value="joomla@kubik-rubik.de" />
<input type="hidden" name="item_name" value="Joomla Erweiterung" />
<input type="hidden" name="item_number" value="Komponente Easybook Reloaded" />
<input type="hidden" name="no_shipping" value="0" />
<input type="hidden" name="no_note" value="1" />
<input type="hidden" name="currency_code" value="EUR" />
<input type="hidden" name="tax" value="0" />
<input type="hidden" name="bn" value="PP-DonationsBF" />
<input type="image" src="/administrator/components/com_easybookreloaded/images/donate.gif" name="submit" alt="PayPal Button - Spende Kubik-Rubik.de!" />
<img alt="Spende" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</div>
</form>
</div>