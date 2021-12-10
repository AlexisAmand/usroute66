<?php defined('_JEXEC') or die('Restricted access'); ?>
<h1>Easybook Reloaded</h1>
<?php echo JTEXT::_('ABOUT EASYBOOK RELOADED'); ?>
<p><strong>Easybook Reloaded Version: <?php echo _EASYBOOK_VERSION; ?></strong></p>
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