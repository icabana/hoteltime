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

$field = $this->field;

$choose = "";
if (count($field) && $field['type'] == "select") {
	$x = explode(";;__;;", $field['choose']);
	if (@count($x) > 0) {
		foreach ($x as $y) {
			if (!empty($y)) {
				$choose .= '<input type="text" name="choose[]" value="'.$y.'" size="40"/><br/>'."\n";
			}
		}
	}
}
?>
<script type="text/javascript">
function setCustomfChoose (val) {
	if (val == "text") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vbnominative').style.display = 'table-row';
		document.getElementById('vbphone').style.display = 'table-row';
		document.getElementById('vbemail').style.display = 'table-row';
		document.getElementById('vbaddress').style.display = 'table-row';
		document.getElementById('vbcity').style.display = 'table-row';
		document.getElementById('vbzip').style.display = 'table-row';
		document.getElementById('vbcompany').style.display = 'table-row';
		document.getElementById('vbvat').style.display = 'table-row';
	}
	if (val == "textarea") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vbnominative').style.display = 'none';
		document.getElementById('vbphone').style.display = 'none';
		document.getElementById('vbemail').style.display = 'none';
		document.getElementById('vbaddress').style.display = 'none';
		document.getElementById('vbcity').style.display = 'none';
		document.getElementById('vbzip').style.display = 'none';
		document.getElementById('vbcompany').style.display = 'none';
		document.getElementById('vbvat').style.display = 'none';
	}
	if (val == "checkbox") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vbnominative').style.display = 'none';
		document.getElementById('vbphone').style.display = 'none';
		document.getElementById('vbemail').style.display = 'none';
		document.getElementById('vbaddress').style.display = 'none';
		document.getElementById('vbcity').style.display = 'none';
		document.getElementById('vbzip').style.display = 'none';
		document.getElementById('vbcompany').style.display = 'none';
		document.getElementById('vbvat').style.display = 'none';
	}
	if (val == "date") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vbnominative').style.display = 'none';
		document.getElementById('vbphone').style.display = 'none';
		document.getElementById('vbemail').style.display = 'none';
		document.getElementById('vbaddress').style.display = 'none';
		document.getElementById('vbcity').style.display = 'none';
		document.getElementById('vbzip').style.display = 'none';
		document.getElementById('vbcompany').style.display = 'none';
		document.getElementById('vbvat').style.display = 'none';
	}
	if (val == "select") {
		document.getElementById('customfchoose').style.display = 'block';
		document.getElementById('vbnominative').style.display = 'none';
		document.getElementById('vbphone').style.display = 'none';
		document.getElementById('vbemail').style.display = 'none';
		document.getElementById('vbaddress').style.display = 'none';
		document.getElementById('vbcity').style.display = 'none';
		document.getElementById('vbzip').style.display = 'none';
		document.getElementById('vbcompany').style.display = 'none';
		document.getElementById('vbvat').style.display = 'none';
	}
	if (val == "country") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vbnominative').style.display = 'none';
		document.getElementById('vbphone').style.display = 'none';
		document.getElementById('vbemail').style.display = 'none';
		document.getElementById('vbaddress').style.display = 'none';
		document.getElementById('vbcity').style.display = 'none';
		document.getElementById('vbzip').style.display = 'none';
		document.getElementById('vbcompany').style.display = 'none';
		document.getElementById('vbvat').style.display = 'none';
	}
	if (val == "separator") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vbnominative').style.display = 'none';
		document.getElementById('vbphone').style.display = 'none';
		document.getElementById('vbemail').style.display = 'none';
		document.getElementById('vbaddress').style.display = 'none';
		document.getElementById('vbcity').style.display = 'none';
		document.getElementById('vbzip').style.display = 'none';
		document.getElementById('vbcompany').style.display = 'none';
		document.getElementById('vbvat').style.display = 'none';
	}
	return true;
}
function addElement() {
	var ni = document.getElementById('customfchooseadd');
	var numi = document.getElementById('theValue');
	var num = (document.getElementById('theValue').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<input type=\'text\' name=\'choose[]\' value=\'\' size=\'40\'/><br/>';
	ni.appendChild(newdiv);
}
</script>
<input type="hidden" value="0" id="theValue" />

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCUSTOMFONE'); ?></b> </td>
				<td><input type="text" name="name" value="<?php echo count($field) ? $field['name'] : ''; ?>" size="40"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBNEWCUSTOMFTWO'); ?></b> </td>
				<td>
					<select id="stype" name="type" onchange="setCustomfChoose(this.value);">
						<option value="text"<?php echo (count($field) && $field['type'] == "text" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFTHREE'); ?></option>
						<option value="textarea"<?php echo (count($field) && $field['type'] == "textarea" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFTEN'); ?></option>
						<option value="select"<?php echo (count($field) && $field['type'] == "select" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFFOUR'); ?></option>
						<option value="checkbox"<?php echo (count($field) && $field['type'] == "checkbox" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFFIVE'); ?></option>
						<option value="date"<?php echo (count($field) && $field['type'] == "date" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFDATETYPE'); ?></option>
						<option value="country"<?php echo (count($field) && $field['type'] == "country" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFCOUNTRY'); ?></option>
						<option value="separator"<?php echo (count($field) && $field['type'] == "separator" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWCUSTOMFSEPARATOR'); ?></option>
					</select>
					<div id="customfchoose" style="display: <?php echo (count($field) && $field['type'] == "select" ? "block" : "none"); ?>;">
						<?php
						if ((count($field) && $field['type'] != "select") || !count($field)) {
						?>
						<br/><input type="text" name="choose[]" value="" size="40"/>
						<?php
						} else {
							echo '<br/>'.$choose;
						}
						?>
						<div id="customfchooseadd" style="display: block;"></div>
						<span><b><a href="javascript: void(0);" onclick="javascript: addElement();"><?php echo JText::_('VBNEWCUSTOMFNINE'); ?></a></b></span>
					</div>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCUSTOMFSIX'); ?></b> </td>
				<td><input type="checkbox" name="required" value="1"<?php echo (count($field) && intval($field['required']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vbemail"<?php echo (count($field) && $field['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCUSTOMFSEVEN'); ?></b> </td>
				<td><input type="checkbox" name="isemail" value="1"<?php echo (count($field) && intval($field['isemail']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vbnominative"<?php echo (count($field) && $field['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBISNOMINATIVE'); ?></b> </td>
				<td><input type="checkbox" name="isnominative" value="1"<?php echo (count($field) && intval($field['isnominative']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vbphone"<?php echo (count($field) && $field['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBISPHONENUMBER'); ?></b> </td>
				<td><input type="checkbox" name="isphone" value="1"<?php echo (count($field) && intval($field['isphone']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vbaddress"<?php echo (count($field) && $field['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBISADDRESS'); ?></b> </td>
				<td><input type="checkbox" name="isaddress" value="1"<?php echo (count($field) && stripos($field['flag'], 'address') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vbcity"<?php echo (count($field) && $field['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBISCITY'); ?></b> </td>
				<td><input type="checkbox" name="iscity" value="1"<?php echo (count($field) && stripos($field['flag'], 'city') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vbzip"<?php echo (count($field) && $field['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBISZIP'); ?></b> </td>
				<td><input type="checkbox" name="iszip" value="1"<?php echo (count($field) && stripos($field['flag'], 'zip') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vbcompany"<?php echo (count($field) && $field['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBISCOMPANY'); ?></b> </td>
				<td><input type="checkbox" name="iscompany" value="1"<?php echo (count($field) && stripos($field['flag'], 'company') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vbvat"<?php echo (count($field) && $field['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBISVAT'); ?></b> </td>
				<td><input type="checkbox" name="isvat" value="1"<?php echo (count($field) && stripos($field['flag'], 'vat') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWCUSTOMFEIGHT'); ?></b> </td>
				<td>
					<input type="text" name="poplink" value="<?php echo count($field) ? $field['poplink'] : ''; ?>" size="40"/>
					<br/>
					<small>Ex. <i>index.php?option=com_content&amp;view=article&amp;id=#JoomlaArticleID#&amp;tmpl=component</i></small>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($field)) :
?>
	<input type="hidden" name="where" value="<?php echo $field['id']; ?>">
<?php
endif;
?>
</form>