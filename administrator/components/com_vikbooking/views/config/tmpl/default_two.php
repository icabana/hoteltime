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
$formatvals = VikBooking::getNumberFormatData(true);
$formatparts = explode(':', $formatvals);
?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VBOCPARAMCURRENCY'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTHREECURNAME'); ?></b> </td>
				<td><input type="text" name="currencyname" value="<?php echo VikBooking::getCurrencyName(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTHREECURSYMB'); ?></b> </td>
				<td><input type="text" name="currencysymb" value="<?php echo VikBooking::getCurrencySymb(true); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTHREECURCODEPP'); ?></b> </td>
				<td><input type="text" name="currencycodepp" value="<?php echo VikBooking::getCurrencyCodePp(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGNUMDECIMALS'); ?></b> </td>
				<td><input type="number" name="numdecimals" value="<?php echo $formatparts[0]; ?>" min="0" max="9"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGNUMDECSEPARATOR'); ?></b> </td>
				<td><input type="text" name="decseparator" value="<?php echo $formatparts[1]; ?>" size="2"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGNUMTHOSEPARATOR'); ?></b> </td>
				<td><input type="text" name="thoseparator" value="<?php echo $formatparts[2]; ?>" size="2"/></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VBOCPARAMTAXPAY'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTWOFIVE'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('ivainclusa', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::ivaInclusa(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTAXSUMMARY'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('taxsummary', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::showTaxOnSummaryOnly(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGMULTIPAY'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('multipay', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::multiplePayments() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTWOSIX'); ?></b> </td>
				<td><input type="text" name="paymentname" value="<?php echo VikBooking::getPaymentName(); ?>" size="25"/></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<?php
$dep_overrides = VikBooking::getDepositOverrides(true);
?>
<div class="vbo-info-overlay-block">
	<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-info-overlay-content vbo-info-overlay-content-depovr"></div>
</div>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VBOCPARAMDEPOSITPAY'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTWOTHREE'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('paytotal', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::payTotal() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTWOFOUR'); ?></b> </td>
				<td><input type="number" name="payaccpercent" value="<?php echo VikBooking::getAccPerCent(); ?>" min="0"/> <select id="typedeposit" name="typedeposit"><option value="pcent">%</option><option value="fixed"<?php echo (VikBooking::getTypeDeposit(true) == "fixed" ? ' selected="selected"' : ''); ?>><?php echo VikBooking::getCurrencySymb(); ?></option></select></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFDEPOSITOVRDS'); ?></b> </td>
				<td>
					<input type="hidden" id="depoverrides" name="depoverrides" value="<?php echo htmlspecialchars($dep_overrides); ?>"/>
					<div id="cur_depoverrides" class="cur_depoverrides"></div>
					<button type="button" class="btn" onclick="vboDisplayDepositOverrides();"><i class="vboicn-pencil2 icn-nomargin"></i></button>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFDEPONLYIFDADV'); ?></b> </td>
				<td><input type="number" style="max-width: 60px;" name="depifdaysadv" min="0" value="<?php echo VikBooking::getDepositIfDays(); ?>"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFDEPCUSTCHOICE'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('depcustchoice', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::depositCustomerChoice() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBOCONFNODEPNONREFUND'), 'content' => JText::_('VBOCONFNODEPNONREFUNDHELP'))); ?> <b><?php echo JText::_('VBOCONFNODEPNONREFUND'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('nodepnonrefund', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::noDepositForNonRefund(), 1, 0); ?></td>
			</tr>
		</tbody>
	</table>
</fieldset>
<script type="text/javascript">
var vbo_overlay_on = false;
var vbo_depovr_defs = {
	"nights": "<?php echo addslashes(JText::_('VBDAYS')); ?>",
	"more": "<?php echo addslashes(JText::_('VBOCONFDEPOSITOVRDSMORE')); ?>",
	"add": "<?php echo addslashes(JText::_('VBCONFIGCLOSINGDATEADD')); ?>",
	"apply": "<?php echo addslashes(JText::_('VBAPPLY')); ?>"
}
jQuery(document).ready(function() {
	vboPopulateDepositOverrides();
	jQuery(document).mouseup(function(e) {
		if (!vbo_overlay_on) {
			return false;
		}
		var vbo_overlay_cont = jQuery(".vbo-info-overlay-content");
		if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
			vboHideDepositOverrides();
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vbo_overlay_on) {
			vboHideDepositOverrides();
		}
	});
});
function vboDisplayDepositOverrides() {
	jQuery(".vbo-info-overlay-block").fadeIn(400, function() {
		if (jQuery(".vbo-info-overlay-block").is(":visible")) {
			vbo_overlay_on = true;
		} else {
			vbo_overlay_on = false;
		}
	});
}
function vboHideDepositOverrides() {
	jQuery(".vbo-info-overlay-block").fadeOut();
	vbo_overlay_on = false;
}
function vboPopulateDepositOverrides() {
	var vbo_dep_overrides = jQuery('#depoverrides').val();
	var vbo_dep_type = document.getElementById('typedeposit');
	var vbo_dep_oper = vbo_dep_type.options[vbo_dep_type.selectedIndex].text;
	try {
		var dep_ovr_obj = JSON.parse(vbo_dep_overrides);
		var tot_ovr = Object.keys(dep_ovr_obj).length;
		var cur_ovr_str  = '',
			cur_ovr_cont = '';
		if (tot_ovr > 1) {
			for (var prop in dep_ovr_obj) {
				if (!dep_ovr_obj.hasOwnProperty(prop) || prop == 'more') {
					continue;
				}
				cur_ovr_str += '<span>'+vbo_depovr_defs.nights+': '+prop+(dep_ovr_obj['more'] == prop ? ' '+vbo_depovr_defs.more : '')+', '+(vbo_dep_oper != '%' ? vbo_dep_oper+' ' : '')+dep_ovr_obj[prop]+(vbo_dep_oper == '%' ? vbo_dep_oper : '')+'</span>';
				cur_ovr_cont += '<div class="new_depovr_container">'+
									'<span>'+vbo_depovr_defs.nights+'</span>'+
									'<span><input type="number" min="0" class="new_depovr_nights" value="'+prop+'"/></span>'+
									(vbo_dep_oper != '%' ? '<span>'+vbo_dep_oper+'</span>' : '')+
									'<span><input type="number" min="0" step="any" class="new_depovr_amounts" value="'+dep_ovr_obj[prop]+'"/></span>'+
									(vbo_dep_oper == '%' ? '<span>'+vbo_dep_oper+'</span>' : '')+
									'<span><select class="new_depovr_more"><option value="">---</option><option value="more">'+vbo_depovr_defs.more+'</option></select></span>'+
									'<span><button type="button" class="btn btn-danger" onclick="jQuery(this).closest(\'.new_depovr_container\').remove();">&times;</button></span>'+
								'</div>';
			}
			jQuery('#cur_depoverrides').html(cur_ovr_str);
		} else {
			//no overrides defined: make the boxes empty
			jQuery('#cur_depoverrides').html('');
		}
		//always include the add and apply buttons
		cur_ovr_cont += '<div class="vbo-center"><button type="button" class="btn" onclick="vboAddDepositeOverride();"><i class="icon-new"></i> '+vbo_depovr_defs.add+'</button></div>';
		cur_ovr_cont += '<br/>';
		cur_ovr_cont += '<div class="vbo-center"><button type="button" class="btn btn-success btn-large" onclick="vboApplyDepositeOverrides();"><i class="vboicn-checkmark"></i> '+vbo_depovr_defs.apply+'</button></div>';
		jQuery('.vbo-info-overlay-content-depovr').html(cur_ovr_cont);
	} catch(e) {
		console.log('cannot parse JSON');
		console.log(e);
	}
}
function vboAddDepositeOverride() {
	var vbo_dep_type = document.getElementById('typedeposit');
	var vbo_dep_oper = vbo_dep_type.options[vbo_dep_type.selectedIndex].text;
	var add_ovr_cont = '<div class="new_depovr_container">'+
							'<span>'+vbo_depovr_defs.nights+'</span>'+
							'<span><input type="number" min="0" class="new_depovr_nights" value=""/></span>'+
							(vbo_dep_oper != '%' ? '<span>'+vbo_dep_oper+'</span>' : '')+
							'<span><input type="number" min="0" step="any" class="new_depovr_amounts" value=""/></span>'+
							(vbo_dep_oper == '%' ? '<span>'+vbo_dep_oper+'</span>' : '')+
							'<span><select class="new_depovr_more"><option value="">---</option><option value="more">'+vbo_depovr_defs.more+'</option></select></span>'+
							'<span><button type="button" class="btn btn-danger" onclick="jQuery(this).closest(\'.new_depovr_container\').remove();">&times;</button></span>'+
						'</div>';
	var cur_ovrs = jQuery('.new_depovr_container');
	if (cur_ovrs.length > 0) {
		cur_ovrs.last().after(add_ovr_cont);
	} else {
		jQuery('.vbo-info-overlay-content-depovr').prepend(add_ovr_cont);
	}
}
function vboApplyDepositeOverrides() {
	var respval = {"more": ""};
	var nights_arr = jQuery('.new_depovr_nights');
	var amounts_arr = jQuery('.new_depovr_amounts');
	var more_arr = jQuery('.new_depovr_more');
	nights_arr.each(function(k, v) {
		var use_nights = jQuery(this).val();
		var use_amounts = jQuery(amounts_arr[k]).val();
		if (isNaN(use_nights) || isNaN(use_amounts)) {
			console.log('skipping loop #'+k);
			return true;
		}
		respval[parseInt(use_nights)] = parseFloat(use_amounts);
		if (jQuery(more_arr[k]).val() == 'more') {
			respval['more'] = parseInt(use_nights);
		}
	});
	jQuery('#depoverrides').val(JSON.stringify(respval));
	vboHideDepositOverrides();
	vboPopulateDepositOverrides();
}
</script>