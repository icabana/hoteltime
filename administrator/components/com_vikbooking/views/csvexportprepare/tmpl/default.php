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

$all_rooms = $this->all_rooms;
$all_channels = $this->all_channels;
$all_payments = $this->all_payments;

JHTML::_('behavior.tooltip');
$vbo_app = new VboApplication();
$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
?>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<h3><?php echo JText::_('VBCSVEXPORT'); ?></h3>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOFILTERBYDATES'); ?></b> </td>
				<td>
					<select name="datefilt" onchange="if (this.value.length){document.getElementById('vbcsvexp-dates').style.display = 'table-row';} else {document.getElementById('vbcsvexp-dates').style.display = 'none';}">
						<option value="">----------</option>
						<option value="ts"><?php echo JText::_('VBOFILTERDATEBOOK'); ?></option>
						<option value="checkin"><?php echo JText::_('VBOFILTERDATEIN'); ?></option>
						<option value="checkout"><?php echo JText::_('VBOFILTERDATEOUT'); ?></option>
					</select>
				</td>
			</tr>
			<tr id="vbcsvexp-dates" style="display: none;">
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCSVEXPDATESRANGE'); ?></b> </td>
				<td><?php echo $vbo_app->getCalendar('', 'checkindate', 'checkindate', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?> &nbsp;-&nbsp; <?php echo $vbo_app->getCalendar('', 'checkoutdate', 'checkoutdate', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBROOMFILTER'); ?></b> </td>
				<td>
					<select name="roomfilt"><option value="">----------</option>
					<?php
					foreach ($all_rooms as $room) {
						?>
						<option value="<?php echo $room['id']; ?>"><?php echo $room['name']; ?></option>
						<?php
					}
					?>
				</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCHANNELFILTER'); ?></b> </td>
				<td>
					<select name="chfilt"><option value="">----------</option>
					<?php
					foreach ($all_channels as $ch) {
						?>
						<option value="<?php echo $ch; ?>"><?php echo $ch; ?></option>
						<?php
					}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOFILTERBYPAYMENT'); ?></b> </td>
				<td>
					<select name="payfilt"><option value="">----------</option>
					<?php
					foreach ($all_payments as $pay) {
						?>
						<option value="<?php echo $pay['id']; ?>"><?php echo $pay['name']; ?></option>
						<?php
					}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCSVEXPFILTBSTATUS'); ?></b> </td>
				<td>
					<select name="status">
						<option value="">----------</option>
						<option value="confirmed"><?php echo JText::_('VBCSVSTATUSCONFIRMED'); ?></option>
						<option value="standby"><?php echo JText::_('VBCSVSTATUSSTANDBY'); ?></option>
						<option value="cancelled"><?php echo JText::_('VBCSVSTATUSCANCELLED'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td style="text-align: left;">
					<button type="button" class="btn" name="csvsubmit" onclick="document.getElementById('adminForm').submit();"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VBCSVGENERATE'); ?></button>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="task" value="csvexportlaunch" />
	<input type="hidden" name="option" value="com_vikbooking" />
</form>