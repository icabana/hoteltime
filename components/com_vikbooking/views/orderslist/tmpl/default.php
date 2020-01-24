<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

$userorders = $this->userorders;
$customer_details = $this->customer_details;
$navig = $this->navig;

$vbdateformat = VikBooking::getDateFormat();
if ($vbdateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vbdateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator();
$pitemid = VikRequest::getString('Itemid', '', 'request');
?>

<form action="<?php echo JRoute::_('index.php?option=com_vikbooking&view=orderslist'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post">
	<div class="vbsearchorderdiv">
		<div class="vbsearchorderinner">
			<span class="vbsearchordertitle"><?php echo JText::_('VBSEARCHCONFIRMNUMB'); ?></span>
		</div>
		<p><?php echo JText::_('VBCONFIRMNUMBORPIN'); ?>: <input type="text" name="confirmnumber" value="<?php echo is_array($customer_details) && array_key_exists('pin', $customer_details) ? $customer_details['pin'] : ''; ?>" size="12"/> <input type="submit" class="vbsearchordersubmit" name="vbsearchorder" value="<?php echo JText::_('VBSEARCHCONFIRMNUMBBTN'); ?>"/></p>
	</div>
</form>

<?php

if (is_array($userorders) && count($userorders) > 0) {
	?>
<br clear="all"/>
<div class="table-responsive">
	<table class="table vborderslisttable">
		<thead>
			<tr><td class="vborderslisttdhead vborderslisttdhead-first">&nbsp;</td><td class="vborderslisttdhead"><?php echo JText::_('VBCONFIRMNUMB'); ?></td><td class="vborderslisttdhead"><?php echo JText::_('VBBOOKINGDATE'); ?></td><td class="vborderslisttdhead"><?php echo JText::_('VBPICKUP'); ?></td><td class="vborderslisttdhead"><?php echo JText::_('VBRETURN'); ?></td><td class="vborderslisttdhead"><?php echo JText::_('VBDAYS'); ?></td></tr>
		</thead>
		<tbody>
	<?php
	foreach($userorders as $ord) {
		$bstatus = 'confirmed';
		if($ord['status'] == 'standby') {
			$bstatus = 'standby';
		}elseif($ord['status'] != 'confirmed') {
			$bstatus = 'cancelled';
		}
		?>
		<tr><td class="vborder-status-cell vborder-status-cell-<?php echo $bstatus; ?>"></td><td><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&task=vieworder&sid='.$ord['sid'].'&ts='.$ord['ts'].(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo (!empty($ord['confirmnumber']) ? $ord['confirmnumber'] : ($ord['status'] == 'standby' ? JText::_('VBINATTESA') : '--------')); ?></a></td><td><?php echo date(str_replace("/", $datesep, $df).' H:i', $ord['ts']); ?></td><td><?php echo date(str_replace("/", $datesep, $df), $ord['checkin']); ?></td><td><?php echo date(str_replace("/", $datesep, $df), $ord['checkout']); ?></td><td><?php echo $ord['days']; ?></td></tr>
		<?php
	}
	?>
		</tbody>
	</table>
</div>
	<?php
}

//pagination
if(strlen($navig) > 0) {
	?>
	<div class="pagination"><?php echo $navig; ?></div>
	<?php
}

?>