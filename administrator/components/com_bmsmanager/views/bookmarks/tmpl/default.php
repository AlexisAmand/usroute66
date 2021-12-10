<?php
defined('_JEXEC') or die;

// Bookmark count
$n	= count( $this->bookmarks );

// Popular icon
$yes	= JHTML::image( 'images/M_images/rating_star.png', '+' );
$no		= JHTML::image( 'images/M_images/rating_star_blank.png', '-' );
?>

<form action="index.php?option=com_bmsmanager&view=bookmarks" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="40" align="center">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->bookmarks ); ?>);"/>
			</th>
			<th width="70" align="center">
				<?php echo JHTML::_( 'grid.sort', 'Bookmark ID', 'id', $this->order['order_Dir'], $this->order['order'] ); ?>
			</th>
			<!--<th width="40" align="center">
				<?php echo JHTML::_( 'grid.sort', 'Visible', 'popular', $this->order['order_Dir'], $this->order['order'] ); ?>
			</th>-->
			<th width="75px">
				<?php echo JHTML::_( 'grid.sort', 'Order', 'ordering', $this->order['order_Dir'], $this->order['order'] ); ?>
				<?php echo JHTML::_( 'grid.order', $this->bookmarks, 'filesave.png', 'bookmark.saveorder' ); ?>
			</th>
			<th align="center">
				<?php echo JHTML::_( 'grid.sort', 'Bookmarks', 'name', $this->order['order_Dir'], $this->order['order'] ); ?>
			</th>
			
		</tr>
	</thead>
	<tbody>
<?php
for ($i = 0; $i < $n; $i++)
{
	$b			= & $this->bookmarks[$i];
	//ID
	$b_id 	= $b->id;
	// Checkbox
	$checked 	= JHTML::_( 'grid.id', $i, $b->id, false, 'bid' );
	
	// Popular icon
	$popular	= ($b->popular) ? 'unpop' : 'makepop';
	$popular	= array( 'onclick' =>
					'return listItemTask(\'cb'. $i .'\',\''. $popular .'\')' );
	$popular	= JHTML::link( '#', ($b->popular) ? $yes : $no, $popular );
	
	// Ordering
	$up	= $this->page->orderUpIcon( $i, true, 'bookmark.orderup' );
	$down	= $this->page->orderDownIcon( $i, $n, true, 'bookmark.orderdown' );
	
	// Edit link
	$link 		= JRoute::_( index .'&controller=bookmark&task=edit&bid[]='. $b->id );
	$link		= JHTML::link( $link, $b->name .' - '. $b->text );
?>
		<tr>
			
			<td align="center">
				<?php echo $checked; ?>
			</td>
			<td align="center">
				<?php echo '<b>'.$b_id.'</b>'; ?>
			</td>
			<!--<td align="center">
				<?php echo $popular; ?>
			</td>-->
			<td style="padding-left:15px;">
				<div style="float:left;margin-top:5px;">
					<input type="text" name="order[]" size="5"
						value="<?php echo $b->ordering; ?>"
						class="inputbox" style="text-align:center;" />
				</div>
				<div style="float:left;">
					<span><?php echo $up; ?></span><br/>
					<span><?php echo $down; ?></span>
				</div>
			</td>
			<td>
				&nbsp;&nbsp;<?php echo $link; ?>
			</td>
			
		</tr>
<?php
}
?>
	</tbody>
	</table>
</div>

	<input type="hidden" name="option" value="com_bmsmanager"/>
	<input type="hidden" name="controller" value="bookmark"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $this->order['order']; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->order['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
