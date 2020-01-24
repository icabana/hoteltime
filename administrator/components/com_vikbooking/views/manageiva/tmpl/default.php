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

$breakdown = array();
if (count($row) && !empty($row['breakdown'])) {
	$get_breakdown = json_decode($row['breakdown'], true);
	if (is_array($get_breakdown) && count($get_breakdown) > 0) {
		$breakdown = $get_breakdown;
	}
}
$breakdown_str = '';
if (count($breakdown) > 0) {
	foreach ($breakdown as $bkey => $subtax) {
		$breakdown_str .= '<div class="add-tax-breakdown-cont">'."\n";
		$breakdown_str .= '<div class="add-tax-breakdown-remove"><i class="fa fa-minus-circle"></i></div>'."\n";
		$breakdown_str .= '<br clear="all"/>'."\n";
		$breakdown_str .= '<div class="add-tax-breakdown-name">'."\n";
		$breakdown_str .= '<span>'.JText::_('VBOTAXNAMEBKDWN').'</span>'."\n";
		$breakdown_str .= '<input type="text" name="breakdown_name[]" value="'.$subtax['name'].'" size="30" placeholder="'.JText::_('VBOTAXNAMEBKDWNEX').'"/>'."\n";
		$breakdown_str .= '</div>'."\n";
		$breakdown_str .= '<div class="add-tax-breakdown-rate">'."\n";
		$breakdown_str .= '<span>'.JText::_('VBOTAXRATEBKDWN').'</span>'."\n";
		$breakdown_str .= '<input type="number" step="any" min="0" name="breakdown_rate[]" value="'.$subtax['aliq'].'" size="6" placeholder="0.00"/>'."\n";
		$breakdown_str .= '</div>'."\n";
		$breakdown_str .= '</div>'."\n";
	}
}
?>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWIVAONE'); ?></b> </td>
				<td><input type="text" name="aliqname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="30"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWIVATWO'); ?></b> </td>
				<td><input type="number" step="any" min="0" name="aliqperc" value="<?php echo count($row) ? $row['aliq'] : ''; ?>" size="10"/> %</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <a href="javascript: void(0);" class="vbo-link-add"><i class="fa fa-plus-circle"></i> <?php echo JText::_('VBOADDTAXBKDWN'); ?></a> </td>
				<td><div id="breakdown-cont"><?php echo $breakdown_str; ?></div></td>
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
<div style="display: none;" id="add-breakdown">
	<div class="add-tax-breakdown-cont">
		<div class="add-tax-breakdown-remove"><i class="fa fa-minus-circle"></i></div>
		<br clear="all"/>
		<div class="add-tax-breakdown-name">
			<span><?php echo JText::_('VBOTAXNAMEBKDWN'); ?></span>
			<input type="text" name="breakdown_name[]" value="" size="30" placeholder="<?php echo JText::_('VBOTAXNAMEBKDWNEX'); ?>"/>
		</div>
		<div class="add-tax-breakdown-rate">
			<span><?php echo JText::_('VBOTAXRATEBKDWN'); ?></span>
			<input type="number" step="any" min="0" name="breakdown_rate[]" value="" size="6" placeholder="0.00"/>
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery(".vbo-link-add").click(function(){
		jQuery("#breakdown-cont").append(jQuery("#add-breakdown").html());
	});
	jQuery("body").on("click", ".add-tax-breakdown-remove", function() {
		jQuery(this).parent().remove();
	});
});
</script>