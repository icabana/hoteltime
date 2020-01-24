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

$coupon = $this->coupon;
$wselrooms = $this->wselrooms;

$vbo_app = new VboApplication();
$currencysymb = VikBooking::getCurrencySymb(true);
$df = VikBooking::getDateFormat(true);
$fromdate = "";
$todate = "";
if (count($coupon) && strlen($coupon['datevalid']) > 0) {
	$dateparts = explode("-", $coupon['datevalid']);
	if ($df == "%d/%m/%Y") {
		$udf = 'd/m/Y';
	} elseif ($df == "%m/%d/%Y") {
		$udf = 'm/d/Y';
	} else {
		$udf = 'Y/m/d';
	}
	$fromdate = date($udf, $dateparts[0]);
	$todate = date($udf, $dateparts[1]);
}
?>
<script type="text/javascript">
function setVehiclesList() {
	if (document.adminForm.allvehicles.checked == true) {
		document.getElementById('vbvlist').style.display='none';
	} else {
		document.getElementById('vbvlist').style.display='block';
	}
	return true;
}
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCOUPONONE'); ?></b> </td>
				<td><input type="text" name="code" value="<?php echo count($coupon) ? $coupon['code'] : ''; ?>" size="30"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCOUPONTWO'); ?></b> </td>
				<td>
					<select name="type">
						<option value="1"<?php echo (count($coupon) && $coupon['type'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCOUPONTYPEPERMANENT'); ?></option>
						<option value="2"<?php echo (count($coupon) && $coupon['type'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCOUPONTYPEGIFT'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCOUPONTHREE'); ?></b> </td>
				<td>
					<select name="percentot">
						<option value="1"<?php echo (count($coupon) && $coupon['percentot'] == 1 ? " selected=\"selected\"" : ""); ?>>%</option>
						<option value="2"<?php echo (count($coupon) && $coupon['percentot'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCOUPONFOUR'); ?></b> </td>
				<td><input type="number" step="any" min="0" name="value" value="<?php echo count($coupon) ? $coupon['value'] : ''; ?>" size="4"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBNEWCOUPONFIVE'); ?></b> </td>
				<td>
					<input type="checkbox" name="allvehicles" value="1"<?php echo ((count($coupon) && $coupon['allvehicles'] == 1) || !count($coupon) ? " checked=\"checked\"" : ""); ?> onclick="javascript: setVehiclesList();"/> <?php echo JText::_('VBNEWCOUPONEIGHT'); ?><span id="vbvlist" style="display: <?php echo ((count($coupon) && $coupon['allvehicles'] == 1) || !count($coupon) ? "none" : "block"); ?>;"><br/><?php echo $wselrooms; ?></span>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWCOUPONSIX'), 'content' => JText::_('VBNEWCOUPONNINE'))); ?> <b><?php echo JText::_('VBNEWCOUPONSIX'); ?></b> </td>
				<td><?php echo $vbo_app->getCalendar($fromdate, 'from', 'from', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?> - <?php echo $vbo_app->getCalendar($todate, 'to', 'to', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCOUPONSEVEN'); ?></b> </td>
				<td><input type="number" step="any" min="0" name="mintotord" value="<?php echo count($coupon) ? $coupon['mintotord'] : '0'; ?>" size="4"/></td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($coupon)) {
	?>
	<input type="hidden" name="where" value="<?php echo $coupon['id']; ?>">
	<?php
}
?>
</form>
<?php
if (strlen($fromdate) > 0 && strlen($todate) > 0) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#from').val('<?php echo $fromdate; ?>').attr('data-alt-value', '<?php echo $fromdate; ?>');
	jQuery('#to').val('<?php echo $todate; ?>').attr('data-alt-value', '<?php echo $todate; ?>');
});
</script>
<?php
}