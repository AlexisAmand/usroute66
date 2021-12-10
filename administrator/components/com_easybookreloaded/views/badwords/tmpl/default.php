<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
    <table class="adminlist">
    <thead>
        <tr>
            <th width="20">
    			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th width="10">
                <?php echo JText::_( 'Word Number' ); ?>
            </th>
            <th>
                <?php echo JText::_( 'Word' ); ?>
            </th>
        </tr>            
    </thead>
    <?php
    $k = 0;
    for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
        $row =& $this->items[$i];
        $checked    = JHTML::_( 'grid.id', $i, $row->id );
        $link = JRoute::_( 'index.php?option=com_easybookreloaded&controller=badwords&task=edit&cid[]='. $row->id );
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td>
  				  <?php echo $checked; ?>
			</td>
            <td>
                <?php echo $row->id; ?>
            </td>
            <td>
                <a href="<?php echo $link ?>"><?php echo $row->word; ?></a>
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
<input type="hidden" name="controller" value="badwords" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>