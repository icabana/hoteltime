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

$row = $this->row;

$vbo_app = new VboApplication();
$dbo = JFactory::getDBO();
$q = "SELECT * FROM `#__vikbooking_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"praliq\">\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $iv['id'] == $row['idiva'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikbooking&task=iva\">".JText::_('NESSUNAIVA')."</a>";
}
?>
<script type="text/javascript">
function toggleFreeCancellation() {
	if (document.getElementById('free_cancellation').checked == true) {
		document.getElementById('canc_deadline').style.display='table-row';
		document.getElementById('canc_policy').style.display='table-row';
	} else {
		document.getElementById('canc_deadline').style.display='none';
		document.getElementById('canc_policy').style.display='none';
	}
	return true;
}
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPRICEONE'); ?>*</b> </td>
				<td><input type="text" name="price" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWPRICETWO'), 'content' => JText::_('VBOPRICEATTRHELP'))); ?> <b><?php echo JText::_('VBNEWPRICETWO'); ?></b> </td>
				<td><input type="text" name="attr" value="<?php echo count($row) ? htmlspecialchars($row['attr']) : ''; ?>" size="40"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPRICETHREE'); ?></b> </td>
				<td><?php echo $wiva; ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBOPRICETYPEMINLOS'), 'content' => JText::_('VBOPRICETYPEMINLOSHELP'))); ?> <b><?php echo JText::_('VBOPRICETYPEMINLOS'); ?></b> </td>
				<td><input type="number" name="minlos" min="1" value="<?php echo count($row) ? $row['minlos'] : '1'; ?>" /></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBOPRICETYPEMINHADV'), 'content' => JText::_('VBOPRICETYPEMINHADVHELP'))); ?> <b><?php echo JText::_('VBOPRICETYPEMINHADV'); ?></b> </td>
				<td><input type="number" name="minhadv" min="0" value="<?php echo count($row) ? $row['minhadv'] : '0'; ?>" /></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPRICEBREAKFAST'); ?></b> </td>
				<td><input type="checkbox" name="breakfast_included" value="1" <?php echo count($row) && $row['breakfast_included'] == 1 ? 'checked="checked"' : ''; ?>/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPRICEFREECANC'); ?></b> </td>
				<td><input type="checkbox" id="free_cancellation" name="free_cancellation" value="1" onclick="toggleFreeCancellation();" <?php echo count($row) && $row['free_cancellation'] == 1 ? 'checked="checked"' : ''; ?>/></td>
			</tr>
			<tr id="canc_deadline" style="display: <?php echo count($row) && $row['free_cancellation'] == 1 ? 'table-row' : 'none'; ?>;">
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPRICEFREECANCDLINE'); ?></b> </td>
				<td><input type="number" min="0" name="canc_deadline" value="<?php echo count($row) ? $row['canc_deadline'] : '7'; ?>" size="5"/></td>
			</tr>
			<tr id="canc_policy" style="display: <?php echo count($row) && $row['free_cancellation'] == 1 ? 'table-row' : 'none'; ?>;">
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWPRICECANCPOLICY'), 'content' => JText::_('VBNEWPRICECANCPOLICYHELP'))); ?> <b><?php echo JText::_('VBNEWPRICECANCPOLICY'); ?></b> </td>
				<td><textarea name="canc_policy" rows="5" cols="200" style="width: 350px; height: 130px;"><?php echo count($row) ? htmlspecialchars($row['canc_policy']) : ''; ?></textarea></td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
?>
	<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
<?php
}
?>
	<input type="hidden" name="option" value="com_vikbooking">
</form>
