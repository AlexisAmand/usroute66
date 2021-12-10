<?php defined('_JEXEC') or die; ?>

<p>
	<a href="http://www.banitech.com" target="new"
		style="margin:0 20px;padding:3px;border:1px solid;">Bookmark System Manager</a>
</p>

<?php echo $this->loadTemplate( $this->about ); ?>

<p style="margin-top:15px;padding-top:15px;border-top:1px solid #dddddd;text-align:center;">
	<?php
	$bmsmanager	= '<a href="http://www.banitech.com" target="new">';
	$bani	= '<a href="mailto:albog@banitech.com?subject=Bookmark System Manager">';
	$bmsmanagerlink	= '<a href="http://codes.mtlnews.com/bmsmanager-2" target="new">';
	echo JText::sprintf( 'BMSMANAGER_CREATED_BY', $bmsmanager, '</a>', $bani, '</a>', $bmsmanagerlink, '</a>');
	?>
</p>