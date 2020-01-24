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

$payment = $this->payment;

$vbo_app = new VboApplication();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$allf = glob(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'payments'.DIRECTORY_SEPARATOR.'*.php');
$psel = "";
if (@count($allf) > 0) {
	$classfiles = array();
	foreach ($allf as $af) {
		$classfiles[] = str_replace(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'payments'.DIRECTORY_SEPARATOR, '', $af);
	}
	sort($classfiles);
	$psel = "<select name=\"payment\" onchange=\"vikLoadPaymentParameters(this.value);\">\n<option value=\"\"></option>\n";
	foreach ($classfiles as $cf) {
		$psel .= "<option value=\"".$cf."\"".(count($payment) && $cf == $payment['file'] ? " selected=\"selected\"" : "").">".$cf."</option>\n";
	}
	$psel .= "</select>";
}
$currencysymb = VikBooking::getCurrencySymb(true);
$payparams = count($payment) ? VikBooking::displayPaymentParameters($payment['file'], $payment['params']) : '';
?>
<script type="text/javascript">
function vikLoadPaymentParameters(pfile) {
	jQuery.noConflict();
	if (pfile.length > 0) {
		jQuery("#vikparameters").html('<?php echo addslashes(JTEXT::_('VIKLOADING')); ?>');
		jQuery.ajax({
			type: "POST",
			url: "index.php?option=com_vikbooking&task=loadpaymentparams&tmpl=component",
			data: { phpfile: pfile }
		}).done(function(res) {
			jQuery("#vikparameters").html(res);
		});
	} else {
		jQuery("#vikparameters").html('--------');
	}
}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPAYMENTONE'); ?></b> </td>
				<td><input type="text" name="name" value="<?php echo count($payment) ? $payment['name'] : ''; ?>" size="30"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPAYMENTTWO'); ?></b> </td>
				<td><?php echo $psel; ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBPAYMENTPARAMETERS'); ?></b> </td>
				<td id="vikparameters"><?php echo $payparams; ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPAYMENTTHREE'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('published', JText::_('VBYES'), JText::_('VBNO'), (count($payment) ? (int)$payment['published'] : 1), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPAYMENTCHARGEORDISC'); ?></b> </td>
				<td>
					<select name="ch_disc">
						<option value="1"<?php echo (count($payment) && $payment['ch_disc'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWPAYMENTCHARGEPLUS'); ?></option>
						<option value="2"<?php echo (count($payment) && $payment['ch_disc'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWPAYMENTDISCMINUS'); ?></option>
					</select> 
					<input type="number" step="any" name="charge" value="<?php echo count($payment) ? $payment['charge'] : ''; ?>" size="5"/> 
					<select name="val_pcent">
						<option value="1"<?php echo (count($payment) && $payment['val_pcent'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option>
						<option value="2"<?php echo (count($payment) && $payment['val_pcent'] == 2 ? " selected=\"selected\"" : ""); ?>>%</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBPAYMENTSHELPCONFIRMTXT'), 'content' => JText::_('VBPAYMENTSHELPCONFIRM'))); ?> <b><?php echo JText::_('VBNEWPAYMENTEIGHT'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('setconfirmed', JText::_('VBYES'), JText::_('VBNO'), (count($payment) ? (int)$payment['setconfirmed'] : 0), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBOPAYMENTHIDENONREF'), 'content' => JText::_('VBOPAYMENTHIDENONREFHELP'))); ?> <b><?php echo JText::_('VBOPAYMENTHIDENONREF'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('hidenonrefund', JText::_('VBYES'), JText::_('VBNO'), (count($payment) ? (int)$payment['hidenonrefund'] : 0), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPAYMENTNINE'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('shownotealw', JText::_('VBYES'), JText::_('VBNO'), (count($payment) ? (int)$payment['shownotealw'] : 0), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBNEWPAYMENTFIVE'); ?></b> </td>
				<td><?php echo $editor->display( "note", (count($payment) ? $payment['note'] : ""), '100%', 300, 70, 20 ); ?></td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($payment)) :
?>
	<input type="hidden" name="where" value="<?php echo $payment['id']; ?>">
<?php
endif;
?>
</form>