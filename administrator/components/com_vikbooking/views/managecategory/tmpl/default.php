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

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
?>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCATONE'); ?></b> </td>
				<td><input type="text" name="catname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCATDESCR'); ?></b> </td>
				<td><?php echo $editor->display( "descr", (count($row) ? $row['descr'] : ""), '100%', 300, 70, 20 ); ?></td>
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