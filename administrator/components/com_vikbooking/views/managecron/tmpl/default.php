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
$allf = $this->allf;

$vbo_app = new VboApplication();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$psel = "<select name=\"class_file\" id=\"cronfile\" onchange=\"vikLoadCronParameters(this.value);\">\n<option value=\"\"></option>\n";
$classfiles = array();
foreach ($allf as $af) {
	$classfiles[] = str_replace(VBO_ADMIN_PATH.DS.'cronjobs'.DS, '', $af);
}
sort($classfiles);

foreach ($classfiles as $cf) {
	$psel .= "<option value=\"".$cf."\"".(count($row) && $cf == $row['class_file'] ? ' selected="selected"' : '').">".$cf."</option>\n";
}
$psel .= "</select>";
?>
<script type="text/javascript">
jQuery.noConflict();
function vikLoadCronParameters(pfile) {
	if (pfile.length > 0) {
		jQuery("#vbo-cron-params").html('<?php echo addslashes(JTEXT::_('VIKLOADING')); ?>');
		jQuery.ajax({
			type: "POST",
			url: "index.php?option=com_vikbooking&task=loadcronparams&tmpl=component",
			data: { phpfile: pfile }
		}).done(function(res) {
			jQuery("#vbo-cron-params").html(res);
		});
	} else {
		jQuery("#vbo-cron-params").html('--------');
	}
}
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<fieldset class="adminform">
		<table cellspacing="1" class="admintable table">
			<tbody>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCRONNAME'); ?></b> </td>
					<td><input type="text" name="cron_name" value="<?php echo count($row) ? $row['cron_name'] : ''; ?>" size="50"/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCRONCLASS'); ?></b> </td>
					<td><?php echo $psel; ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCRONPARAMS'); ?></b> </td>
					<td><div id="vbo-cron-params"><?php echo count($row) ? VikBooking::displayCronParameters($row['class_file'], $row['params']) : ''; ?></div></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCRONPUBLISHED'); ?></b> </td>
					<td><?php echo $vbo_app->printYesNoButtons('published', JText::_('VBYES'), JText::_('VBNO'), (count($row) ? (int)$row['published'] : 1), 1, 0); ?></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($row)) :
?>
	<input type="hidden" name="where" value="<?php echo $row['id']; ?>">
<?php
endif;
?>
</form>