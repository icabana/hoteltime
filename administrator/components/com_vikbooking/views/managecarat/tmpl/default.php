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

?>
<script type="text/javascript">
function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		document.getElementById('resizesel').style.display='block';
	} else {
		document.getElementById('resizesel').style.display='none';
	}
	return true;
}
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCARATONE'); ?></b> </td>
				<td><input type="text" name="caratname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCARATTWO'); ?></b> </td>
				<td>
					<?php echo (count($row) && !empty($row['icon']) && file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$row['icon']) ? "<img src=\"".VBO_SITE_URI."resources/uploads/".$row['icon']."\"/>&nbsp; " : ""); ?>
					<input type="file" name="caraticon" size="35"/><br/>
					<label for="autoresize" style="display: inline-block; vertical-align: middle;"><?php echo JText::_('VBNEWOPTNINE'); ?></label> 
					<input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> 
					<span id="resizesel" style="display: none;">&nbsp;<?php echo JText::_('VBNEWOPTTEN'); ?>: <input type="text" name="resizeto" value="50" size="3"/> px</span>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCARATTHREE'); ?></b> </td>
				<td><input type="text" name="carattextimg" value="<?php echo count($row) ? htmlspecialchars($row['textimg']) : ''; ?>" size="40"/></td>
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