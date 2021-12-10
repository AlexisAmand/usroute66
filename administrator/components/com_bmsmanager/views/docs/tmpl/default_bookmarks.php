<?php defined('_JEXEC') or die; ?>

<div class="bmsmanagerc">
	<h2 style="text-align:center;">
		<?php echo JText::_( 'Bookmarking' ); ?>
	</h2>
	
	<h3><?php echo JText::_( 'Configuration' ); ?></h3></dev>
	<div class="bmsmanagerc">
<?php
echo	JText::_( 'DOCS_BOOKMARKING_CONFIG_TEXT_1' ) .' '.
		JText::_( 'DOCS_BOOKMARKING_CONFIG_TEXT_2' );
?>
	</div>
	<h3><?php echo JText::_( 'HTML Anchors' ); ?></h3>
	<div class="bmsmanagerc">
	
<?php

echo
	JText::_( 'List of existing anchors' ) .
	'<br/><br/>'.
	JText::sprintf( 'ANCHOR_DESC_URL', '<b>[&#117rl]</b>' ).'<br/>'.
	JText::sprintf( 'ANCHOR_DESC_TARGET', '<b>[twin]</b>' ) .'<br/>'.
	JText::sprintf( 'ANCHOR_DESC_TEXT', '<b>[text]</b>' ) .'<br/>'.
	JText::sprintf( 'ANCHOR_DESC_TITLE', '<b>[title]</b>' ) .'<br/>'.
	JText::sprintf( 'ANCHOR_DESC_DESC', '<b>[desc]</b>' ) .'<br/>'.'<br/>'.'<br/>';
	
?>
	</div>
	<h3><?php echo JText::_( 'Customizing Bookmarks' ); ?></h3>
	<div class="bmsmanagerc">
<?php
echo 
	JText::_( '' ) .
	'<br/><br/>'.
	'Example 1 {bmsmanager:bookmarks;size:small;text:yr;separator:-;badges:7,2,1,14,18,6,13,5,16,4,3,19,15,12}'.'<br/>'.
	'Example 2 {bmsmanager:bookmarks;size:text;text:nn;badges:*}'.'<br/>'.
	'Example 3 {bmsmanager:bookmarks;size:button;text:yr;badges:*}'.'<br/>'.
	'Example 4 {bmsmanager:bookmarks;size:custom;text:nn;badges:*}'.'<br/><br/>'.
	'badges - * (All badges), 1, 2, 3... (id of badges)'.'<br/>'.
	'size - text, small, large, button, custom.'.'<br/>'.
	'text - nn (nothing), yr (on the right side), yl (on the left side)'.'<br/>'.
	'separator - Separator between badges' .'<br/>'.
	'bmsmanager:bookmarks - for compatibiliti with Linkr component.'.'<br/>';
?>
	</div>
	
	<h3><?php echo JText::_( 'Customizing' ); ?></h3>
	<div class="bmsmanagerc">
		<?php echo JText::_( 'MORE_OPTIONS_IN_PLUGIN' ); ?>
	</div>
	
	<h3><?php echo JText::_( 'Size examples' ); ?></h3>
	<div class="bmsmanagerc">
<?php
		$align	= array( 'align' => 'absmiddle' );
		$small	= JHTML::image( bmsmanagerAssetsPath .'egsb.gif', JText::_( 'SIZE_SMALL' ), $align );
		$large	= JHTML::image( bmsmanagerAssetsPath .'eglb.gif', JText::_( 'SIZE_LARGE' ), $align );
		$btn	= JHTML::image( bmsmanagerAssetsPath .'egbutton.gif', JText::_( 'SIZE_BTN' ), $align );
		$cust	= JHTML::image( bmsmanagerAssetsPath .'egcustom.gif', JText::_( 'SIZE_CSTM' ), $align );
		
echo	JText::_( 'SIZE_TEXT' ) .'&nbsp;<b>Digg This!</b>&nbsp;|&nbsp;'.
		JText::_( 'SIZE_SMALL' ) .'&nbsp;'. $small .'&nbsp;|&nbsp;'.
		JText::_( 'SIZE_LARGE' ) .'&nbsp;'. $large .'&nbsp;|&nbsp;'.
		JText::_( 'SIZE_BTN' ) .'&nbsp;'. $btn .'&nbsp;|&nbsp;'.
		JText::_( 'SIZE_CSTM' ) .'&nbsp;'. $cust;
?>
	</div>
	
	<h3><?php echo JText::_( 'HTML example' ); ?></h3>
	<code class="bmsmanagerc"><br/>
<?php
echo	sprintf( lDiv, 'bmsmanager-bm' ) .
		lTab . JText::_( 'example' ) .'<br/>'.
		lTab . JText::_( 'example' ) .'<br/>'.
		lTab . JText::_( 'example' ) .'<br/>'.
		'&lt;/div&gt;';
?>
	</code>
</div>