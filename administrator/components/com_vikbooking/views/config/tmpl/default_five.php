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

$vbo_app = new VboApplication();
$current_smsapi = VikBooking::getSMSAPIClass();
$allf = glob(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.'*.php');
$psel = "<select name=\"smsapi\" id=\"smsapifile\" onchange=\"vikLoadSMSParameters(this.value);\">\n<option value=\"\"></option>\n";
if (@count($allf) > 0) {
	$classfiles = array();
	foreach($allf as $af) {
		$classfiles[] = str_replace(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR, '', $af);
	}
	sort($classfiles);
	
	foreach($classfiles as $cf) {
		$psel .= "<option value=\"".$cf."\"".($cf == $current_smsapi ? ' selected="selected"' : '').">".$cf."</option>\n";
	}
}
$psel .= "</select>";
$sendsmsto = VikBooking::getSendSMSTo();
$sendsmswhen = VikBooking::getSendSMSWhen();
?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VBOCPARAMSMS'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSMSCLASS'); ?></b> </td>
				<td><?php echo $psel; ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSMSAUTOSEND'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('smsautosend', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::autoSendSMSEnabled() ? 1 : 0), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSMSSENDTO'); ?></b> </td>
				<td>
					<span class="vbo-spblock-inline"><input type="checkbox" name="smssendto[]" value="admin" id="smssendtoadmin"<?php echo in_array('admin', $sendsmsto) ? ' checked="checked"' : ''; ?> /> <label for="smssendtoadmin"><?php echo JText::_('VBCONFIGSMSSENDTOADMIN'); ?></label></span>
					<span class="vbo-spblock-inline"><input type="checkbox" name="smssendto[]" value="customer" id="smssendtocustomer"<?php echo in_array('customer', $sendsmsto) ? ' checked="checked"' : ''; ?> /> <label for="smssendtocustomer"><?php echo JText::_('VBCONFIGSMSSENDTOCUSTOMER'); ?></label></span>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSMSSENDWHEN'); ?></b> </td>
				<td>
					<select name="smssendwhen" onchange="displaySMSTexts(this.value);">
						<option value="1"<?php echo $sendsmswhen <= 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSMSSENDWHENCONF'); ?></option>
						<option value="2"<?php echo $sendsmswhen >= 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSMSSENDWHENCONFPEND'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSMSSADMINPHONE'); ?></b> </td>
				<td><input type="text" name="smsadminphone" size="20" value="<?php echo VikBooking::getSMSAdminPhone(); ?>" /></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGSMSPARAMETERS'); ?></b> </td>
				<td><div id="vbo-sms-params"><?php echo !empty($current_smsapi) ? VikBooking::displaySMSParameters($current_smsapi, VikBooking::getSMSParams(false)) : ''; ?></div></td>
			</tr>
	<?php
	if (!empty($current_smsapi)) {
		require_once(VBO_ADMIN_PATH.DS.'smsapi'.DS.$current_smsapi);
		if (method_exists('VikSmsApi', 'estimate')) {
			?>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGSMSREMAINBAL'); ?></b> </td>
				<td><button type="button" class="btn" onclick="vboEstimateCredit();"><i class="vboicn-coin-euro"></i><?php echo JText::_('VBCONFIGSMSESTCREDIT'); ?></button><div id="vbo-sms-balance"></div></td>
			</tr>
			<?php
		}
	}
	?>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGSMSADMTPL'); ?></b> </td>
				<td>
					<div class="btn-toolbar vbo-smstpl-toolbar">
						<div class="btn-group pull-left vbo-smstpl-bgroup">
							<button onclick="setSmsTplTag('smsadmintpl', '{customer_name}');" class="btn" type="button">{customer_name}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{booking_id}');" class="btn" type="button">{booking_id}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{checkin_date}');" class="btn" type="button">{checkin_date}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{checkout_date}');" class="btn" type="button">{checkout_date}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{num_nights}');" class="btn" type="button">{num_nights}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{rooms_booked}');" class="btn" type="button">{rooms_booked}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{rooms_names}');" class="btn" type="button">{rooms_names}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{customer_country}');" class="btn" type="button">{customer_country}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{customer_email}');" class="btn" type="button">{customer_email}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{customer_phone}');" class="btn" type="button">{customer_phone}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{tot_adults}');" class="btn" type="button">{tot_adults}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{tot_children}');" class="btn" type="button">{tot_children}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{tot_guests}');" class="btn" type="button">{tot_guests}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{total}');" class="btn" type="button">{total}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{total_paid}');" class="btn" type="button">{total_paid}</button>
							<button onclick="setSmsTplTag('smsadmintpl', '{remaining_balance}');" class="btn" type="button">{remaining_balance}</button>
						</div>
					</div>
					<div class="control vbo-smstpl-control">
						<textarea name="smsadmintpl" id="smsadmintpl" style="width: 90%; min-width: 90%; max-width: 100%; height: 100px;"><?php echo VikBooking::getSMSAdminTemplate(); ?></textarea>
					</div>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGSMSCUSTOTPL'); ?></b> </td>
				<td>
					<div class="btn-toolbar vbo-smstpl-toolbar">
						<div class="btn-group pull-left vbo-smstpl-bgroup">
							<button onclick="setSmsTplTag('smscustomertpl', '{customer_name}');" class="btn" type="button">{customer_name}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{customer_pin}');" class="btn" type="button">{customer_pin}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{booking_id}');" class="btn" type="button">{booking_id}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{checkin_date}');" class="btn" type="button">{checkin_date}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{checkout_date}');" class="btn" type="button">{checkout_date}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{num_nights}');" class="btn" type="button">{num_nights}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{rooms_booked}');" class="btn" type="button">{rooms_booked}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{rooms_names}');" class="btn" type="button">{rooms_names}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{tot_adults}');" class="btn" type="button">{tot_adults}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{tot_children}');" class="btn" type="button">{tot_children}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{tot_guests}');" class="btn" type="button">{tot_guests}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{total}');" class="btn" type="button">{total}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{total_paid}');" class="btn" type="button">{total_paid}</button>
							<button onclick="setSmsTplTag('smscustomertpl', '{remaining_balance}');" class="btn" type="button">{remaining_balance}</button>
						</div>
					</div>
					<div class="control vbo-smstpl-control">
						<textarea name="smscustomertpl" id="smscustomertpl" style="width: 90%; min-width: 90%; max-width: 100%; height: 100px;"><?php echo VikBooking::getSMSCustomerTemplate(); ?></textarea>
					</div>
				</td>
			</tr>
			<tr id="smsadmintplpend-tr" style="display: <?php echo $sendsmswhen <= 1 ? 'none' : 'table-row'; ?>;">
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGSMSADMTPLPEND'); ?></b> </td>
				<td>
					<div class="btn-toolbar vbo-smstpl-toolbar">
						<div class="btn-group pull-left vbo-smstpl-bgroup">
							<button onclick="setSmsTplTag('smsadmintplpend', '{customer_name}');" class="btn" type="button">{customer_name}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{booking_id}');" class="btn" type="button">{booking_id}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{checkin_date}');" class="btn" type="button">{checkin_date}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{checkout_date}');" class="btn" type="button">{checkout_date}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{num_nights}');" class="btn" type="button">{num_nights}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{rooms_booked}');" class="btn" type="button">{rooms_booked}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{rooms_names}');" class="btn" type="button">{rooms_names}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{customer_country}');" class="btn" type="button">{customer_country}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{customer_email}');" class="btn" type="button">{customer_email}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{customer_phone}');" class="btn" type="button">{customer_phone}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{tot_adults}');" class="btn" type="button">{tot_adults}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{tot_children}');" class="btn" type="button">{tot_children}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{tot_guests}');" class="btn" type="button">{tot_guests}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{total}');" class="btn" type="button">{total}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{total_paid}');" class="btn" type="button">{total_paid}</button>
							<button onclick="setSmsTplTag('smsadmintplpend', '{remaining_balance}');" class="btn" type="button">{remaining_balance}</button>
						</div>
					</div>
					<div class="control vbo-smstpl-control">
						<textarea name="smsadmintplpend" id="smsadmintplpend" style="width: 90%; min-width: 90%; max-width: 100%; height: 100px;"><?php echo VikBooking::getSMSAdminTemplate(null, 'standby'); ?></textarea>
					</div>
				</td>
			</tr>
			<tr id="smscustomertplpend-tr" style="display: <?php echo $sendsmswhen <= 1 ? 'none' : 'table-row'; ?>;">
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGSMSCUSTOTPLPEND'); ?></b> </td>
				<td>
					<div class="btn-toolbar vbo-smstpl-toolbar">
						<div class="btn-group pull-left vbo-smstpl-bgroup">
							<button onclick="setSmsTplTag('smscustomertplpend', '{customer_name}');" class="btn" type="button">{customer_name}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{customer_pin}');" class="btn" type="button">{customer_pin}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{booking_id}');" class="btn" type="button">{booking_id}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{checkin_date}');" class="btn" type="button">{checkin_date}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{checkout_date}');" class="btn" type="button">{checkout_date}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{num_nights}');" class="btn" type="button">{num_nights}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{rooms_booked}');" class="btn" type="button">{rooms_booked}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{rooms_names}');" class="btn" type="button">{rooms_names}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{tot_adults}');" class="btn" type="button">{tot_adults}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{tot_children}');" class="btn" type="button">{tot_children}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{tot_guests}');" class="btn" type="button">{tot_guests}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{total}');" class="btn" type="button">{total}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{total_paid}');" class="btn" type="button">{total_paid}</button>
							<button onclick="setSmsTplTag('smscustomertplpend', '{remaining_balance}');" class="btn" type="button">{remaining_balance}</button>
						</div>
					</div>
					<div class="control vbo-smstpl-control">
						<textarea name="smscustomertplpend" id="smscustomertplpend" style="width: 90%; min-width: 90%; max-width: 100%; height: 100px;"><?php echo VikBooking::getSMSCustomerTemplate(null, 'standby'); ?></textarea>
					</div>
				</td>
			</tr>
			<tr id="smsadmintplcanc-tr">
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGSMSADMTPLCANC'); ?></b> </td>
				<td>
					<div class="btn-toolbar vbo-smstpl-toolbar">
						<div class="btn-group pull-left vbo-smstpl-bgroup">
							<button onclick="setSmsTplTag('smsadmintplcanc', '{customer_name}');" class="btn" type="button">{customer_name}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{booking_id}');" class="btn" type="button">{booking_id}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{checkin_date}');" class="btn" type="button">{checkin_date}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{checkout_date}');" class="btn" type="button">{checkout_date}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{num_nights}');" class="btn" type="button">{num_nights}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{rooms_booked}');" class="btn" type="button">{rooms_booked}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{rooms_names}');" class="btn" type="button">{rooms_names}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{customer_country}');" class="btn" type="button">{customer_country}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{customer_email}');" class="btn" type="button">{customer_email}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{customer_phone}');" class="btn" type="button">{customer_phone}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{tot_adults}');" class="btn" type="button">{tot_adults}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{tot_children}');" class="btn" type="button">{tot_children}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{tot_guests}');" class="btn" type="button">{tot_guests}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{total}');" class="btn" type="button">{total}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{total_paid}');" class="btn" type="button">{total_paid}</button>
							<button onclick="setSmsTplTag('smsadmintplcanc', '{remaining_balance}');" class="btn" type="button">{remaining_balance}</button>
						</div>
					</div>
					<div class="control vbo-smstpl-control">
						<textarea name="smsadmintplcanc" id="smsadmintplcanc" style="width: 90%; min-width: 90%; max-width: 100%; height: 100px;"><?php echo VikBooking::getSMSAdminTemplate(null, 'cancelled'); ?></textarea>
					</div>
				</td>
			</tr>
			<tr id="smscustomertplcanc-tr">
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGSMSCUSTOTPLCANC'); ?></b> </td>
				<td>
					<div class="btn-toolbar vbo-smstpl-toolbar">
						<div class="btn-group pull-left vbo-smstpl-bgroup">
							<button onclick="setSmsTplTag('smscustomertplcanc', '{customer_name}');" class="btn" type="button">{customer_name}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{customer_pin}');" class="btn" type="button">{customer_pin}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{booking_id}');" class="btn" type="button">{booking_id}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{checkin_date}');" class="btn" type="button">{checkin_date}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{checkout_date}');" class="btn" type="button">{checkout_date}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{num_nights}');" class="btn" type="button">{num_nights}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{rooms_booked}');" class="btn" type="button">{rooms_booked}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{rooms_names}');" class="btn" type="button">{rooms_names}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{tot_adults}');" class="btn" type="button">{tot_adults}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{tot_children}');" class="btn" type="button">{tot_children}</button>
							<button onclick="setSmsTplTag('smscustomertplcanc', '{tot_guests}');" class="btn" type="button">{tot_guests}</button>
						</div>
					</div>
					<div class="control vbo-smstpl-control">
						<textarea name="smscustomertplcanc" id="smscustomertplcanc" style="width: 90%; min-width: 90%; max-width: 100%; height: 100px;"><?php echo VikBooking::getSMSCustomerTemplate(null, 'cancelled'); ?></textarea>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
<script type="text/javascript">
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
}
function displaySMSTexts(sval) {
	if (parseInt(sval) <= 1) {
		document.getElementById('smsadmintplpend-tr').style.display = 'none';
		document.getElementById('smscustomertplpend-tr').style.display = 'none';
	} else {
		document.getElementById('smsadmintplpend-tr').style.display = 'table-row';
		document.getElementById('smscustomertplpend-tr').style.display = 'table-row';
	}
}
function setSmsTplTag(taid, tpltag) {
	var tplobj = document.getElementById(taid);
	if (tplobj != null) {
		var start = tplobj.selectionStart;
		var end = tplobj.selectionEnd;
		tplobj.value = tplobj.value.substring(0, start) + tpltag + tplobj.value.substring(end);
		tplobj.selectionStart = tplobj.selectionEnd = start + tpltag.length;
		tplobj.focus();
	}
}
function vikLoadSMSParameters(pfile) {
	if (pfile.length > 0) {
		jQuery("#vbo-sms-params").html('<?php echo addslashes(JTEXT::_('VIKLOADING')); ?>');
		jQuery.ajax({
			type: "POST",
			url: "index.php?option=com_vikbooking&task=loadsmsparams&tmpl=component",
			data: { phpfile: pfile }
		}).done(function(res) {
			jQuery("#vbo-sms-params").html(res);
		});
	} else {
		jQuery("#vbo-sms-params").html('--------');
	}
}
function vboEstimateCredit() {
	jQuery("#vbo-sms-balance").html('<?php echo addslashes(JTEXT::_('VIKLOADING')); ?>');
	jQuery.ajax({
		type: "POST",
		url: "index.php?option=com_vikbooking&task=loadsmsbalance&tmpl=component",
		data: { vbo: '1' }
	}).done(function(res) {
		jQuery("#vbo-sms-balance").html(res);
	});
}
</script>