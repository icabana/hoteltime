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
JHTML::_('behavior.tooltip');
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
	<h3><?php echo JText::_('VBICSEXPORT'); ?></h3>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCSVEXPFILTDATES'); ?></b> </td>
				<td><?php echo $vbo_app->getCalendar('', 'checkindate', 'checkindate', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?> &nbsp;-&nbsp; <?php echo $vbo_app->getCalendar('', 'checkoutdate', 'checkoutdate', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
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
					<button type="button" class="btn" name="csvsubmit" onclick="document.getElementById('adminForm').submit();"><i class="vboicn-cloud-download"></i> <?php echo JText::_('VBICSGENERATE'); ?></button>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="task" value="icsexportlaunch" />
	<input type="hidden" name="option" value="com_vikbooking" />
</form>