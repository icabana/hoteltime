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

$customer = $this->customer;
$wselcountries = $this->wselcountries;

//JHtmlList::users(string $name, string $active, integer $nouser, string $javascript = null, string $order = 'name')
if (!class_exists('JHtmlList')) {
	jimport( 'joomla.html.html.list' );
}
$df = VikBooking::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$vbo_app = new VboApplication();
$document = JFactory::getDocument();
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery.fancybox.css');
$document->addStyleSheet(VBO_ADMIN_URI.'resources/js_upload/colorpicker.css');
JHtml::_('script', VBO_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/colorpicker.js', false, true, false, false);
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/eye.js', false, true, false, false);
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/utils.js', false, true, false, false);
$ptmpl = VikRequest::getString('tmpl', '', 'request');
$pcheckin = VikRequest::getInt('checkin', '', 'request');
$pbid = VikRequest::getInt('bid', '', 'request');
?>
<script type="text/javascript" src="<?php echo VBO_ADMIN_URI.'resources/js_upload/webcam.js'; ?>"></script>
<script type="text/Javascript">
var vbo_swf_url = '<?php echo VBO_ADMIN_URI.'resources/js_upload/webcam.swf'; ?>';
var vbo_shutter_url = '<?php echo VBO_ADMIN_URI.'resources/js_upload/shutter.mp3'; ?>';
var vbo_scan_base = '<?php echo VBO_ADMIN_URI.'resources/idscans/'; ?>';
var vbo_messages = {
	"camNotAllowed" : "<?php echo addslashes(JText::_('VBOSNAPCAMNOTALLOWED')); ?>",
	"takeSnapLoading" : "<?php echo addslashes(JText::_('VBOTAKESNAPCAMLOADING')); ?>",
	"takeSnapDefText" : "<?php echo addslashes(JText::_('VBOTAKESNAPCAMTAKEIT')); ?>"
};
</script>
<script type="text/Javascript">
function getRandomPin(min, max) {
	return Math.floor(Math.random() * (max - min)) + min;
}
function generatePin() {
	var pin = getRandomPin(10999, 99999);
	document.getElementById('pin').value = pin;
}
var vbo_overlay_on = false;
var vbo_snap_allowed = false;
var def_snap_width = 1024;
var def_snap_height = 768;
if (window.innerWidth > 1920) {
	def_snap_width = 1920;
	def_snap_height = 1440;
}
webcam.set_api_url('index.php?option=com_vikbooking&task=uploadsnapshot');
webcam.set_swf_url(vbo_swf_url);
webcam.set_quality(100);
webcam.set_shutter_sound(true, vbo_shutter_url);
webcam.set_hook('onLoad', 'snapshotLoad');
webcam.set_hook('onAllow', 'snapshotAllow');
webcam.set_hook('onComplete', 'snapshotComplete');
function snapshotLoad(allowStatus) {
	vbo_snap_allowed = allowStatus;
}
function snapshotAllow() {
	vbo_snap_allowed = true;
}
function takeSnapshot() {
	if (vbo_snap_allowed) {
		jQuery("#vbo-takesnbtn").prop("disabled", true).html(vbo_messages.takeSnapLoading);
		webcam.snap();
	} else {
		alert(vbo_messages.camNotAllowed);
	}
}
function snapshotComplete(res) {
	jQuery("#vbo-takesnbtn").prop("disabled", false).html(vbo_messages.takeSnapDefText);
	webcam.reset();
	if (res.indexOf('e4j.error') >= 0 ) {
		console.log(res);
		alert(res.replace("e4j.error.", ""));
	} else {
		jQuery("#docimg").hide();
		jQuery("#scandocimg").val(res);
		if (jQuery(".vbo-cur-idscan").length) {
			jQuery(".vbo-cur-idscan").find("a").replaceWith("<a href=\""+vbo_scan_base+res+"\" target=\"_blank\">"+res+"</a>");
		} else {
			jQuery("#scandocimg").after("<div class=\"vbo-cur-idscan\"><i class=\"vboicn-eye\"></i><a href=\""+vbo_scan_base+res+"\" target=\"_blank\">"+res+"</a></div>");
		}
		
		jQuery(".vbo-info-overlay-block").fadeOut();
		vbo_overlay_on = false;
	}
}
jQuery(document).ready(function() {
	jQuery(document).mouseup(function(e) {
		if (!vbo_overlay_on) {
			return false;
		}
		var vbo_overlay_cont = jQuery(".vbo-info-overlay-snapshot");
		if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
			jQuery(".vbo-info-overlay-block").fadeOut();
			vbo_overlay_on = false;
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vbo_overlay_on) {
			jQuery(".vbo-info-overlay-block").fadeOut();
			vbo_overlay_on = false;
		}
	});
	jQuery("#vbo-camstart").click(function() {
		jQuery(".vbo-info-overlay-block").fadeIn();
		vbo_overlay_on = true;
		jQuery(".vbo-info-overlay-snapshot-movie").css({
			"width": def_snap_width,
			"height": def_snap_height
		}).html(webcam.get_html(def_snap_width, def_snap_height));
	});
	jQuery(document.body).on("click", ".vbo-cur-idscan a", function(e) {
		e.preventDefault();
		var imgsrc = jQuery(this).attr("href");
		console.log(imgsrc);
		jQuery.fancybox({
			"helpers": {
				"overlay": {
					"locked": false
				}
			},
			"href": imgsrc,
			"autoScale": false,
			"transitionIn": "none",
			"transitionOut": "none",
			"padding": 0,
			"type": "image"
		});
	});
<?php
if (count($customer) && !empty($customer['bdate'])) {
	?>
	jQuery("#bdate").val("<?php echo $customer['bdate']; ?>").attr('data-alt-value', "<?php echo $customer['bdate']; ?>");
	<?php
}
?>
});
</script>
<?php
if (!empty($pcheckin) && !empty($pbid)) {
	?>
<div class="vbo-center">
	<button type="button" class="btn btn-success btn-large" onclick="document.getElementById('adminForm').submit();"><i class="vboicn-checkmark"></i> <?php echo JText::_('VBSAVE'); ?></button>
</div>
	<?php
}
?>
<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div style="width: 49%; float: left;">
		<fieldset class="adminform">
			<legend class="adminlegend"><?php echo JText::_('VBOCUSTOMERDETAILS'); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERFIRSTNAME'); ?> <sup>*</sup></b> </td>
						<td><input type="text" name="first_name" value="<?php echo count($customer) ? $customer['first_name'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERLASTNAME'); ?> <sup>*</sup></b> </td>
						<td><input type="text" name="last_name" value="<?php echo count($customer) ? $customer['last_name'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERCOMPANY'); ?></b> </td>
						<td><input type="text" name="company" value="<?php echo count($customer) ? $customer['company'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERCOMPANYVAT'); ?></b> </td>
						<td><input type="text" name="vat" value="<?php echo count($customer) ? $customer['vat'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMEREMAIL'); ?> <sup>*</sup></b> </td>
						<td><input type="text" name="email" value="<?php echo count($customer) ? $customer['email'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERPHONE'); ?> <sup>*</sup></b> </td>
						<td><input type="text" name="phone" value="<?php echo count($customer) ? $customer['phone'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERADDRESS'); ?></b> </td>
						<td><input type="text" name="address" value="<?php echo count($customer) ? $customer['address'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERCITY'); ?></b> </td>
						<td><input type="text" name="city" value="<?php echo count($customer) ? $customer['city'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERZIP'); ?></b> </td>
						<td><input type="text" name="zip" value="<?php echo count($customer) ? $customer['zip'] : ''; ?>" size="6"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERCOUNTRY'); ?> <sup>*</sup></b> </td>
						<td><?php echo $wselcountries; ?></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell<?php echo !empty($pcheckin) && !empty($pbid) && empty($customer['gender']) ? ' vbo-config-param-cell-warn' : ''; ?>"> <b><?php echo JText::_('VBCUSTOMERGENDER'); ?></b> </td>
						<td>
							<select name="gender">
								<option value=""></option>
								<option value="M"<?php echo count($customer) && $customer['gender'] == 'M' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCUSTOMERGENDERM'); ?></option>
								<option value="F"<?php echo count($customer) && $customer['gender'] == 'F' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCUSTOMERGENDERF'); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell<?php echo count($customer) && !empty($pcheckin) && !empty($pbid) && empty($customer['bdate']) ? ' vbo-config-param-cell-warn' : ''; ?>"> <b><?php echo JText::_('VBCUSTOMERBDATE'); ?></b> </td>
						<td><?php echo $vbo_app->getCalendar('', 'bdate', 'bdate', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell<?php echo count($customer) && !empty($pcheckin) && !empty($pbid) && empty($customer['pbirth']) ? ' vbo-config-param-cell-warn' : ''; ?>"> <b><?php echo JText::_('VBCUSTOMERPBIRTH'); ?></b> </td>
						<td><input type="text" name="pbirth" value="<?php echo count($customer) ? $customer['pbirth'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERDOCTYPE'); ?></b> </td>
						<td><input type="text" name="doctype" value="<?php echo count($customer) ? $customer['doctype'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERDOCNUM'); ?></b> </td>
						<td><input type="text" name="docnum" value="<?php echo count($customer) ? $customer['docnum'] : ''; ?>" size="15"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERDOCIMG'); ?></b> <br/><button type="button" class="btn" id="vbo-camstart"><i class="vboicn-camera"></i><?php echo JText::_('VBOTAKESNAPCAM'); ?></button></td>
						<td>
							<input type="file" name="docimg" id="docimg" size="30" />
							<input type="hidden" name="scandocimg" id="scandocimg" value="" />
							<div class="vbo-cur-idscan">
						<?php
						if (count($customer) && !empty($customer['docimg'])) {
							?>
							<i class="vboicn-eye"></i><a href="<?php echo VBO_ADMIN_URI.'resources/idscans/'.$customer['docimg']; ?>" target="_blank"><?php echo $customer['docimg']; ?></a>
							<?php
						}
						?>
							</div>
						</td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCUSTOMERPIN'); ?></b> </td>
						<td><input type="text" name="pin" id="pin" value="<?php echo count($customer) ? $customer['pin'] : ''; ?>" size="6" placeholder="54321" /> &nbsp;&nbsp; <button type="button" class="btn" onclick="generatePin();" style="vertical-align: top;"><?php echo JText::_('VBCUSTOMERGENERATEPIN'); ?></button></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b>Joomla User</b> </td>
						<td><?php echo JHtmlList::users('ujid', (count($customer) ? $customer['ujid'] : ''), 1); ?></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell" style="vertical-align: top;"> <b><?php echo JText::_('VBCUSTOMERNOTES'); ?></b> </td>
						<td><textarea cols="80" rows="5" name="notes" style="width: 400px; height: 130px;"><?php echo count($customer) ? htmlspecialchars($customer['notes']) : ''; ?></textarea></td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	<div style="width: 49%; float: right;">
	<?php
	$customerch_params = array();
	if (count($customer) && !empty($customer['chdata'])) {
		$customerch_params = json_decode($customer['chdata'], true);
		$customerch_params = is_array($customerch_params) ? $customerch_params : array();
	}
	?>
		<fieldset class="adminform">
			<legend class="adminlegend"><?php echo JText::_('VBOCUSTOMERSALESCHANNEL'); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCUSTOMERISCHANNEL'); ?></b> </td>
						<td><?php echo $vbo_app->printYesNoButtons('ischannel', JText::_('VBYES'), JText::_('VBNO'), (count($customer) && intval($customer['ischannel']) == 1 ? 1 : 0), 1, 0); ?></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCUSTOMERCOMMISSION'); ?> <sup>*</sup></b> </td>
						<td><input type="number" name="commission" step="any" value="<?php echo array_key_exists('commission', $customerch_params) ? $customerch_params['commission'] : ''; ?>" placeholder="0.00" style="width: 60px !important;" /> %</td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCUSTOMERCMMSON'); ?></b> </td>
						<td><select name="calccmmon"><option value="0"<?php echo array_key_exists('calccmmon', $customerch_params) && intval($customerch_params['calccmmon']) < 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCUSTOMERCMMSONTOTAL'); ?></option><option value="1"<?php echo array_key_exists('calccmmon', $customerch_params) && intval($customerch_params['calccmmon']) > 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCUSTOMERCMMSONRRATES'); ?></option></select></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCUSTOMERCMMSONTAX'); ?></b> </td>
						<td><select name="applycmmon"><option value="0"<?php echo array_key_exists('applycmmon', $customerch_params) && intval($customerch_params['applycmmon']) < 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCUSTOMERCMMSONTAXINCL'); ?></option><option value="1"<?php echo array_key_exists('applycmmon', $customerch_params) && intval($customerch_params['applycmmon']) > 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCUSTOMERCMMSONTAXEXCL'); ?></option></select></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCUSTOMERCMMSNAME'); ?> <sup>*</sup></b> </td>
						<td><input type="text" name="chname" value="<?php echo array_key_exists('chname', $customerch_params) && !empty($customerch_params['chname']) ? $customerch_params['chname'] : (count($customer) && intval($customer['ischannel']) > 0 ? str_replace(' ', '-', trim($customer['first_name'].' '.$customer['last_name'])) : ''); ?>" size="30" /></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCUSTOMERCMMSCOLOR'); ?> <sup>*</sup></b> </td>
						<td><div class="vbo-colortag-square" style="background-color: <?php echo array_key_exists('chcolor', $customerch_params) && !empty($customerch_params['chcolor']) ? $customerch_params['chcolor'] : (count($customer) && intval($customer['ischannel']) > 0 ? '#000000' : '#ffffff'); ?>"></div><input type="hidden" name="chcolor" class="chcolor" value="<?php echo array_key_exists('chcolor', $customerch_params) && !empty($customerch_params['chcolor']) ? $customerch_params['chcolor'] : (count($customer) && intval($customer['ischannel']) > 0 ? '#000000' : '#ffffff'); ?>" /></td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	<div class="vbo-info-overlay-block">
		<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
		<div class="vbo-info-overlay-snapshot">
			<h3 style="text-align: center;"><i class="vboicn-camera"></i><?php echo JText::_('VBOTAKESNAPCAM'); ?></h3>
			<div class="vbo-info-overlay-snapshot-controls">
				<button type="button" class="btn btn-primary" onclick="webcam.configure();"><i class="vboicn-cogs"></i><?php echo JText::_('VBOTAKESNAPCAMCONFIGURE'); ?></button>
				<button type="button" class="btn btn-success" onclick="takeSnapshot();" id="vbo-takesnbtn"><i class="vboicn-camera"></i><?php echo JText::_('VBOTAKESNAPCAMTAKEIT'); ?></button>
			</div>
			<div class="vbo-info-overlay-snapshot-movie"></div>
		</div>
	</div>
	<?php
if ($ptmpl == 'component') {
	?>
	<input type="hidden" name="tmpl" value="<?php echo $ptmpl; ?>">
	<?php
}
if (!empty($pcheckin) && !empty($pbid)) {
	?>
	<input type="hidden" name="checkin" value="<?php echo $pcheckin; ?>">
	<input type="hidden" name="bid" value="<?php echo $pbid; ?>">
	<?php
}
if (count($customer)) {
	?>
	<input type="hidden" name="where" value="<?php echo $customer['id']; ?>">
	<?php
}
?>
	<input type="hidden" name="task" value="<?php echo count($customer) ? 'updatecustomer' : 'savecustomer'; ?>">
	<input type="hidden" name="option" value="com_vikbooking">
</form>
<script type="text/javascript">
var vboHexDigits = new Array ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
function vboRgb2Hex(rgb) {
	rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	return "#" + vboHex(rgb[1]) + vboHex(rgb[2]) + vboHex(rgb[3]);
}
function vboHex(x) {
	return isNaN(x) ? "00" : vboHexDigits[(x - x % 16) / 16] + vboHexDigits[x % 16];
}
jQuery(document).ready(function() {
	jQuery('.vbo-colortag-square').ColorPicker({
		color: '#ffffff',
		onShow: function (colpkr, el) {
			var cur_color = jQuery(el).css('backgroundColor');
			jQuery(el).ColorPickerSetColor(vboRgb2Hex(cur_color));
			jQuery(colpkr).show();
			return false;
		},
		onChange: function (hsb, hex, rgb, el) {
			jQuery(el).css('backgroundColor', '#'+hex);
			jQuery(el).parent().find('.chcolor').val('#'+hex);
		},
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).css('backgroundColor', '#'+hex);
			jQuery(el).parent().find('.chcolor').val('#'+hex);
			jQuery(el).ColorPickerHide();
		},
	});
});
</script>