<?php defined('_JEXEC') or die('Restricted access');?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
    <fieldset class="adminform">
        <legend><?php echo JText::_( 'Details' ); ?></legend>
        <table class="admintable">
        <tr>
            <td width="100" align="right" class="key">
                <label for="word">
                    <?php echo JText::_( 'Word' ); ?>:
                </label>
            </td>
            <td>
                <input class="text_area" type="text" name="word" id="word" size="32" value="<?php echo $this->badword->word;?>" />
            </td>
        </tr>
        </table>
    </fieldset>
</div>

<div class="clr"></div>

<input type="hidden" name="option" value="com_easybookreloaded" />
<input type="hidden" name="id" value="<?php echo $this->badword->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="badwords" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>