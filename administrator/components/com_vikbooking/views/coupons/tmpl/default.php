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

if (empty($rows)) {
	?>
<p class="warn"><?php echo JText::_('VBNOCOUPONSFOUND'); ?></p>
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
			<th class="title left" width="200"><?php echo JText::_( 'VBPVIEWCOUPONSONE' ); ?></th>
			<th class="title left" width="200" align="center"><?php echo JText::_( 'VBPVIEWCOUPONSTWO' ); ?></th>
			<th class="title left" width="100" align="center"><?php echo JText::_( 'VBPVIEWCOUPONSTHREE' ); ?></th>
			<th class="title left" width="100" align="center"><?php echo JText::_( 'VBPVIEWCOUPONSFOUR' ); ?></th>
			<th class="title left" width="100" align="center"><?php echo JText::_( 'VBPVIEWCOUPONSFIVE' ); ?></th>
		</tr>
		</thead>
	<?php
	$currencysymb = VikBooking::getCurrencySymb(true);
	$nowdf = VikBooking::getDateFormat(true);
	if ($nowdf == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($nowdf == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	$datesep = VikBooking::getDateSeparator(true);
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$strtype = $row['type'] == 1 ? JText::_('VBCOUPONTYPEPERMANENT') : JText::_('VBCOUPONTYPEGIFT');
		$strtype .= ", ".$row['value']." ".($row['percentot'] == 1 ? "%" : $currencysymb);
		$strdate = JText::_('VBCOUPONALWAYSVALID');
		if (strlen($row['datevalid']) > 0) {
			$dparts = explode("-", $row['datevalid']);
			$strdate = date(str_replace("/", $datesep, $df), $dparts[0])." - ".date(str_replace("/", $datesep, $df), $dparts[1]);
		}
		$totvehicles = 0;
		if (intval($row['allvehicles']) == 0) {
			$allve = explode(";", $row['idrooms']);
			foreach($allve as $fv) {
				if (!empty($fv)) {
					$totvehicles++;
				} 
			}
		}
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editcoupon&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['code']; ?></a></td>
			<td align="center"><?php echo $strtype; ?></td>
			<td align="center"><?php echo $strdate; ?></td>
			<td align="center"><?php echo intval($row['allvehicles']) == 1 ? JText::_('VBCOUPONALLVEHICLES') : $totvehicles; ?></td>
			<td align="center"><?php echo $row['mintotord']; ?></td>
		</tr>	
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="coupons" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}