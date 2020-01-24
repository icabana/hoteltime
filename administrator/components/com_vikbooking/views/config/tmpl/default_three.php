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
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$document = JFactory::getDocument();
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery.fancybox.css');
$document->addStyleSheet(VBO_ADMIN_URI.'resources/js_upload/colorpicker.css');
JHtml::_('script', VBO_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/colorpicker.js', false, true, false, false);
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/eye.js', false, true, false, false);
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/utils.js', false, true, false, false);

$themesel = '<select name="theme">';
$themesel .= '<option value="default">default</option>';
$themes = glob(VBO_SITE_PATH.DS.'themes'.DS.'*');
$acttheme = VikBooking::getTheme();
if (count($themes) > 0) {
	$strip = VBO_SITE_PATH.DS.'themes'.DS;
	foreach ($themes as $th) {
		if (is_dir($th)) {
			$tname = str_replace($strip, '', $th);
			if ($tname != 'default') {
				$themesel .= '<option value="'.$tname.'"'.($tname == $acttheme ? ' selected="selected"' : '').'>'.$tname.'</option>';
			}
		}
	}
}
$themesel .= '</select>';
$firstwday = VikBooking::getFirstWeekDay(true);
?>
<div style="width: 49%; float: left;">
	<fieldset class="adminform">
		<legend class="adminlegend"><?php echo JText::_('VBOCPARAMLAYOUT'); ?></legend>
		<table cellspacing="1" class="admintable table">
			<tbody>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGFIRSTWDAY'); ?></b> </td>
					<td><select name="firstwday"><option value="0"<?php echo $firstwday == '0' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBSUNDAY'); ?></option><option value="1"<?php echo $firstwday == '1' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBMONDAY'); ?></option><option value="2"<?php echo $firstwday == '2' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBTUESDAY'); ?></option><option value="3"<?php echo $firstwday == '3' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBWEDNESDAY'); ?></option><option value="4"<?php echo $firstwday == '4' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBTHURSDAY'); ?></option><option value="5"<?php echo $firstwday == '5' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBFRIDAY'); ?></option><option value="6"<?php echo $firstwday == '6' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBSATURDAY'); ?></option></select></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTHREETEN'); ?></b> </td>
					<td><input type="number" name="numcalendars" value="<?php echo VikBooking::numCalendars(); ?>" min="0" max="24"/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFIGTHUMBSIZE'); ?></b> </td>
					<td><input type="number" name="thumbsize" value="<?php echo VikBooking::getThumbSize(true); ?>" min="20" max="1000"/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTHREENINE'); ?></b> </td>
					<td><?php echo $vbo_app->printYesNoButtons('showpartlyreserved', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::showPartlyReserved() ? 'yes' : 0), 'yes', 0); ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTHREECHECKINOUTSTAT'); ?></b> </td>
					<td><?php echo $vbo_app->printYesNoButtons('showcheckinoutonly', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::showStatusCheckinoutOnly() ? 1 : 0), 1, 0); ?></td>
				</tr>

				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFIGEMAILTEMPLATE'); ?></b> </td>
					<td><button type="button" class="btn vbo-edit-tmpl" data-tmpl-path="<?php echo urlencode(VBO_SITE_PATH.DS.'helpers'.DS.'email_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VBOCONFIGEDITTMPLFILE'); ?></button></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFIGINVOICETEMPLATE'); ?></b> </td>
					<td><button type="button" class="btn vbo-edit-tmpl" data-tmpl-path="<?php echo urlencode(VBO_SITE_PATH.DS.'helpers'.DS.'invoices'.DS.'invoice_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VBOCONFIGEDITTMPLFILE'); ?></button></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFIGCHECKINTEMPLATE'); ?></b> </td>
					<td><button type="button" class="btn vbo-edit-tmpl" data-tmpl-path="<?php echo urlencode(VBO_SITE_PATH.DS.'helpers'.DS.'checkins'.DS.'checkin_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VBOCONFIGEDITTMPLFILE'); ?></button></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFIGCUSTCSSTPL'); ?></b> </td>
					<td><button type="button" class="btn vbo-edit-tmpl" data-tmpl-path="<?php echo urlencode(VBO_SITE_PATH.DS.'vikbooking_custom.css'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VBOCONFIGEDITTMPLFILE'); ?></button></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFIGCUSTBACKCSSTPL'); ?></b> </td>
					<td><button type="button" class="btn vbo-edit-tmpl" data-tmpl-path="<?php echo urlencode(VBO_ADMIN_PATH.DS.'resources'.DS.'vikbooking_backendcustom.css'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VBOCONFIGEDITTMPLFILE'); ?></button></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTHREESIX'); ?></b> </td>
					<td><?php echo $vbo_app->printYesNoButtons('showfooter', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::showFooter() ? 'yes' : 0), 'yes', 0); ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTHEME'); ?></b> </td>
					<td><?php echo $themesel; ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGTHREESEVEN'); ?></b> </td>
					<td><?php echo $editor->display( "intromain", VikBooking::getIntroMain(), '100%', 350, 70, 20 ); ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBCONFIGTHREEEIGHT'); ?></b> </td>
					<td><textarea name="closingmain" rows="5" cols="60" style="min-width: 400px;"><?php echo htmlspecialchars(VikBooking::getClosingMain()); ?></textarea></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</div>

<div style="width: 49%; float: left; margin-left: 2px;">
<?php
$colortags = VikBooking::loadBookingsColorTags();
$tagsrules = VikBooking::loadColorTagsRules();
$opt_js_rules = '';
foreach ($tagsrules as $tagk => $tagv) {
	$opt_js_rules .= '<option value=\"'.$tagk.'\">'.addslashes(JText::_($tagv)).'</option>';
}
?>
	<fieldset class="adminform">
		<legend class="adminlegend"><?php echo JText::_('VBOCPARAMBOOKTAGS'); ?></legend>
		<table cellspacing="1" class="admintable table">
			<tbody>
			<?php
			foreach ($colortags as $ctagk => $ctagv) {
				?>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_($ctagv['name']); ?></b> </td>
					<td>
						<div class="vbo-colortag-square" style="background-color: <?php echo $ctagv['color']; ?>; color: <?php echo VikBooking::getBestColorContrast($ctagv['color']); ?>;"><i class="vboicn-price-tags"></i></div>
						<input type="hidden" name="bctagname[]" class="bctagname" value="<?php echo $ctagv['name']; ?>" />
						<input type="hidden" name="bctagcolor[]" class="bctagcolor" value="<?php echo $ctagv['color']; ?>" />
						<select name="bctagrule[]" style="margin: 0; vertical-align: top;">
						<?php
						foreach ($tagsrules as $tagk => $tagv) {
							?>
							<option value="<?php echo $tagk; ?>"<?php echo !empty($tagk) && $tagk == $ctagv['rule'] ? ' selected="selected"' : ''; ?>><?php echo JText::_($tagv); ?></option>
							<?php
						}
						?>
						</select>
						<div style="float: right;"><button class="btn btn-danger vbo-colortag-rm" type="button">X</button></div>
					</td>
				</tr>
				<?php
			}
			?>
				<tr id="vbo-colortag-lasttr">
					<td width="200" class="vbo-config-param-cell"><button class="btn vbo-colortag-add" type="button"><i class="icon-new"></i> <?php echo JText::_('VBOCOLORTAGADD'); ?></button></td>
					<td> </td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"><a class="btn btn-danger" href="index.php?option=com_vikbooking&amp;task=config&amp;reset_tags=1" onclick="return confirm('<?php echo addslashes(JText::_('VBDELCONFIRM')); ?>');"><i class="icon-remove"></i> <?php echo JText::_('VBOCOLORTAGRMALL'); ?></button></td>
					<td> </td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</div>
<br clear="all" />

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
	jQuery(".vbo-edit-tmpl").click(function(){
		var vbo_tmpl_path = jQuery(this).attr("data-tmpl-path");
		jQuery.fancybox({
			"helpers": {
				"overlay": {
					"locked": false
				}
			},
			"href": "index.php?option=com_vikbooking&task=edittmplfile&path="+vbo_tmpl_path+"&tmpl=component",
			"width": "75%",
			"height": "75%",
			"autoScale": false,
			"transitionIn": "none",
			"transitionOut": "none",
			//"padding": 0,
			"type": "iframe"
		});
	});
	jQuery(".vbo-colortag-add").click(function(){
		jQuery("#vbo-colortag-lasttr").before(
			"<tr>"+
			"<td width=\"200\" class=\"vbo-config-param-cell\"> <input type=\"text\" name=\"bctagname[]\" class=\"bctagname\" value=\"\" placeholder=\"<?php echo addslashes(JText::_('VBOCOLORTAGADDPLCHLD')); ?>\" size=\"25\" /> </td>"+
			"<td>"+
			"<div class=\"vbo-colortag-square\" style=\"\"><i class=\"vboicn-price-tags\"></i></div>"+
			"<input type=\"hidden\" name=\"bctagcolor[]\" class=\"bctagcolor\" value=\"#ffffff\" />"+
			"<select name=\"bctagrule[]\" style=\"margin: 0; vertical-align: top;\"><?php echo $opt_js_rules; ?></select>"+
			"<div style=\"float: right;\"><button class=\"btn btn-danger vbo-colortag-rm\" type=\"button\">X</button></div>"+
			"</td>"+
			"</tr>"
		);
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
				jQuery(el).parent().find('.bctagcolor').val('#'+hex);
			},
			onSubmit: function(hsb, hex, rgb, el) {
				jQuery(el).css('backgroundColor', '#'+hex);
				jQuery(el).parent().find('.bctagcolor').val('#'+hex);
				jQuery(el).ColorPickerHide();
			},
		});
	});
	jQuery(document.body).on('click', '.vbo-colortag-rm', function() {
		jQuery(this).closest('tr').remove();
	});
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
			jQuery(el).parent().find('.bctagcolor').val('#'+hex);
		},
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).css('backgroundColor', '#'+hex);
			jQuery(el).parent().find('.bctagcolor').val('#'+hex);
			jQuery(el).ColorPickerHide();
		},
	});
});
</script>