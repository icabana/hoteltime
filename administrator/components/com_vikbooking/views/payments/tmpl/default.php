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

$rows = $this->rows;
$lim0 = $this->lim0;
$navbut = $this->navbut;

$currencysymb=VikBooking::getCurrencySymb(true);
if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VBNOPAYMENTS'); ?></p>
	<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikbooking" />
	</form>
	<?php
} else {
	?>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-list-table">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
			<th class="title left" width="150"><?php echo JText::_( 'VBPSHOWPAYMENTSONE' ); ?></th>
			<th class="title left" width="150"><?php echo JText::_( 'VBPSHOWPAYMENTSTWO' ); ?></th>
			<th class="title center" width="150" align="center"><?php echo JText::_( 'VBPSHOWPAYMENTSTHREE' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VBPSHOWPAYMENTSCHARGEORDISC' ); ?></th>
			<th class="title center" width="50" align="center"><?php echo JText::_( 'VBPSHOWPAYMENTSFIVE' ); ?></th>
			<th class="title center" width="50" align="center"><?php echo JText::_( 'VBORDERING' ); ?></th>
		</tr>
		</thead>
	<?php
	
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$saycharge = "";
		if (strlen($row['charge']) > 0 && $row['charge'] > 0.00) {
			$saycharge .= $row['ch_disc'] == 1 ? "+ " : "- ";
			$saycharge .= $row['charge']." ";
			$saycharge .= $row['val_pcent'] == 1 ? $currencysymb : "%";
		}
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editpayment&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php echo $row['file']; ?></td>
			<td class="center"><?php echo strip_tags($row['note']); ?></td>
			<td class="center"><?php echo $saycharge; ?></td>
			<td class="center"><a href="index.php?option=com_vikbooking&amp;task=modavailpayment&amp;cid[]=<?php echo $row['id']; ?>"><?php echo intval($row['published']) == 1 ? "<i class=\"fa fa-check vbo-icn-img\" style=\"color: #099909;\"></i>" : "<i class=\"fa fa-times-circle vbo-icn-img\" style=\"color: #ff0000;\"></i>"; ?></a></td>
			<td class="center"><a href="index.php?option=com_vikbooking&amp;task=sortpayment&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=up" title="(<?php echo $row['ordering']; ?>)"><i class="fa fa-arrow-up vbo-icn-img"></i></a> <a href="index.php?option=com_vikbooking&amp;task=sortpayment&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=down" title="(<?php echo $row['ordering']; ?>)"><i class="fa fa-arrow-down vbo-icn-img"></i></a></td>
		</tr>  
		  <?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="payments" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}