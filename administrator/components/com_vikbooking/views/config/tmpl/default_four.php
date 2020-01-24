<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') or die('Restricted access');

$vbo_app = new VboApplication();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$sitelogo = VikBooking::getSiteLogo();
$backlogo = VikBooking::getBackendLogo();
$sendemailwhen = VikBooking::getSendEmailWhen();

?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VBOCPARAMCOMPANY'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTHREEONE'); ?></b> </td>
				<td><input type="text" name="fronttitle" value="<?php echo VikBooking::getFrontTitle(); ?>" size="30"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGFOURLOGO'); ?></b> </td>
				<td><input type="file" name="sitelogo" size="35"/> <?php echo (strlen($sitelogo) > 0 ? '<a href="'.VBO_ADMIN_URI.'resources/'.$sitelogo.'" class="vbomodal" target="_blank">'.$sitelogo.'</a>' : ''); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGLOGOBACKEND'); ?></b> </td>
				<td><input type="file" name="backlogo" size="35"/> <?php echo (strlen($backlogo) > 0 ? '<a href="'.VBO_ADMIN_URI.'resources/'.$backlogo.'" class="vbomodal" target="_blank">'.$backlogo.'</a>' : '<a href="'.VBO_ADMIN_URI.'vikbooking.png" class="vbomodal" target="_blank">vikbooking.png</a>'); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSENDEMAILWHEN'); ?></b> </td>
				<td><select name="sendemailwhen"><option value="1"><?php echo JText::_('VBCONFIGSMSSENDWHENCONFPEND'); ?></option><option value="2"<?php echo $sendemailwhen > 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSMSSENDWHENCONF'); ?></option></select></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBOTERMSCONDS'); ?></b> </td>
				<td><?php echo $editor->display( "termsconds", VikBooking::getTermsConditions(), '100%', 350, 70, 20 ); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGFOURORDMAILFOOTER'); ?></b> </td>
				<td><textarea name="footerordmail" rows="5" cols="60" style="min-height: 110px; width: 400px;"><?php echo htmlspecialchars(VikBooking::getFooterOrdMail()); ?></textarea></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGFOURFOUR'); ?></b> </td>
				<td><textarea name="disclaimer" rows="5" cols="60" style="min-height: 110px; width: 400px;"><?php echo htmlspecialchars(VikBooking::getDisclaimer()); ?></textarea></td>
			</tr>
		</tbody>
	</table>
</fieldset>