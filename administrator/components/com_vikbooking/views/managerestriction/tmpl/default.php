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

$data = $this->data;
$rooms = $this->rooms;

$vbo_app = new VboApplication();
$df = VikBooking::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$cdf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$cdf = 'm/d/Y';
} else {
	$cdf = 'Y/m/d';
}
$roomsel = '';
if (is_array($rooms) && count($rooms) > 0) {
	$nowrooms = count($data) && !empty($data['idrooms']) && $data['allrooms'] == 0 ? explode(';', $data['idrooms']) : array();
	$roomsel = '<select name="idrooms[]" multiple="multiple">'."\n";
	foreach ($rooms as $r) {
		$roomsel .= '<option value="'.$r['id'].'"'.(in_array('-'.$r['id'].'-', $nowrooms) ? ' selected="selected"' : '').'>'.$r['name'].'</option>'."\n";
	}
	$roomsel .= '</select>';
}
//CTA and CTD
$cur_setcta = count($data) && !empty($data['ctad']) ? explode(',', $data['ctad']) : array();
$cur_setctd = count($data) && !empty($data['ctdd']) ? explode(',', $data['ctdd']) : array();
$wdaysmap = array('0' => JText::_('VBSUNDAY'), '1' => JText::_('VBMONDAY'), '2' => JText::_('VBTUESDAY'), '3' => JText::_('VBWEDNESDAY'), '4' => JText::_('VBTHURSDAY'), '5' => JText::_('VBFRIDAY'), '6' => JText::_('VBSATURDAY'));
$ctasel = '<select name="ctad[]" multiple="multiple" size="7">'."\n";
foreach ($wdaysmap as $wdk => $wdv) {
	$ctasel .= '<option value="'.$wdk.'"'.(in_array('-'.$wdk.'-', $cur_setcta) ? ' selected="selected"' : '').'>'.$wdv.'</option>'."\n";
}
$ctasel .= '</select>';
$ctdsel = '<select name="ctdd[]" multiple="multiple" size="7">'."\n";
foreach ($wdaysmap as $wdk => $wdv) {
	$ctdsel .= '<option value="'.$wdk.'"'.(in_array('-'.$wdk.'-', $cur_setctd) ? ' selected="selected"' : '').'>'.$wdv.'</option>'."\n";
}
$ctdsel .= '</select>';
//
$dfromval = count($data) && !empty($data['dfrom']) ? date($cdf, $data['dfrom']) : '';
$dtoval = count($data) && !empty($data['dto']) ? date($cdf, $data['dto']) : '';
$vbra1 = '';
$vbra2 = '';
$vbrb1 = '';
$vbrb2 = '';
$vbrc1 = '';
$vbrc2 = '';
$vbrd1 = '';
$vbrd2 = '';
if (count($data) && strlen($data['wdaycombo']) > 0) {
	$vbcomboparts = explode(':', $data['wdaycombo']);
	foreach($vbcomboparts as $kc => $cb) {
		if (!empty($cb)) {
			$nowcombo = explode('-', $cb);
			if ($kc == 0) {
				$vbra1 = $nowcombo[0];
				$vbra2 = $nowcombo[1];
			} elseif ($kc == 1) {
				$vbrb1 = $nowcombo[0];
				$vbrb2 = $nowcombo[1];
			} elseif ($kc == 2) {
				$vbrc1 = $nowcombo[0];
				$vbrc2 = $nowcombo[1];
			} elseif ($kc == 3) {
				$vbrd1 = $nowcombo[0];
				$vbrd2 = $nowcombo[1];
			}
		}
	}
}
$arrwdays = array(1 => JText::_('VBMONDAY'),
		2 => JText::_('VBTUESDAY'),
		3 => JText::_('VBWEDNESDAY'),
		4 => JText::_('VBTHURSDAY'),
		5 => JText::_('VBFRIDAY'),
		6 => JText::_('VBSATURDAY'),
		0 => JText::_('VBSUNDAY')
);
?>
<script type="text/javascript">
function vbSecondArrWDay() {
	var wdayone = document.adminForm.wday.value;
	if (wdayone != "") {
		document.getElementById("vbwdaytwodivid").style.display = "inline-block";
		document.adminForm.cta.checked = false;
		document.adminForm.ctd.checked = false;
		vbToggleCta();
		vbToggleCtd();
	} else {
		document.getElementById("vbwdaytwodivid").style.display = "none";
	}
	vbComboArrWDay();
}
function vbComboArrWDay() {
	var wdayone = document.adminForm.wday;
	var wdaytwo = document.adminForm.wdaytwo;
	if (wdayone.value != "" && wdaytwo.value != "" && wdayone.value != wdaytwo.value) {
		var comboa = wdayone.options[wdayone.selectedIndex].text;
		var combob = wdaytwo.options[wdaytwo.selectedIndex].text;
		document.getElementById("vbrcomboa1").innerHTML = comboa;
		document.getElementById("vbrcomboa2").innerHTML = combob;
		document.getElementById("vbrcomboa").value = wdayone.value+"-"+wdaytwo.value;
		document.getElementById("vbrcombob1").innerHTML = combob;
		document.getElementById("vbrcombob2").innerHTML = comboa;
		document.getElementById("vbrcombob").value = wdaytwo.value+"-"+wdayone.value;
		document.getElementById("vbrcomboc1").innerHTML = comboa;
		document.getElementById("vbrcomboc2").innerHTML = comboa;
		document.getElementById("vbrcomboc").value = wdayone.value+"-"+wdayone.value;
		document.getElementById("vbrcombod1").innerHTML = combob;
		document.getElementById("vbrcombod2").innerHTML = combob;
		document.getElementById("vbrcombod").value = wdaytwo.value+"-"+wdaytwo.value;
		document.getElementById("vbwdaycombodivid").style.display = "block";
	} else {
		document.getElementById("vbwdaycombodivid").style.display = "none";
	}
}
function vbToggleRooms() {
	if (document.adminForm.allrooms.checked == true) {
		document.getElementById("vbrestrroomsdiv").style.display = "none";
	} else {
		document.getElementById("vbrestrroomsdiv").style.display = "block";
	}
}
function vbToggleCta() {
	if (document.adminForm.cta.checked != true) {
		document.getElementById("vbrestrctadiv").style.display = "none";
	} else {
		document.getElementById("vbrestrctadiv").style.display = "block";
		document.adminForm.wday.value = "";
		document.adminForm.wdaytwo.value = "";
		vbSecondArrWDay();

	}
}
function vbToggleCtd() {
	if (document.adminForm.ctd.checked != true) {
		document.getElementById("vbrestrctddiv").style.display = "none";
	} else {
		document.getElementById("vbrestrctddiv").style.display = "block";
		document.adminForm.wday.value = "";
		document.adminForm.wdaytwo.value = "";
		vbSecondArrWDay();
	}
}
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<fieldset class="adminform">
		<table cellspacing="1" class="admintable table">
			<tbody>
				<tr>
					<td width="200" class="vbo-config-param-cell"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBRESTRICTIONSHELPTITLE'), 'content' => JText::_('VBRESTRICTIONSSHELP'))); ?> <b><?php echo JText::_('VBNEWRESTRICTIONNAME'); ?>*</b></td>
					<td><input type="text" name="name" value="<?php echo count($data) ? $data['name'] : ''; ?>" size="40"/></td>
				</tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWRESTRICTIONONE'); ?>*</b></td>
					<td><select name="month"><option value="0">----</option><option value="1"<?php echo (count($data) && $data['month'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHONE'); ?></option><option value="2"<?php echo (count($data) && $data['month'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHTWO'); ?></option><option value="3"<?php echo (count($data) && $data['month'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHTHREE'); ?></option><option value="4"<?php echo (count($data) && $data['month'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHFOUR'); ?></option><option value="5"<?php echo (count($data) && $data['month'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHFIVE'); ?></option><option value="6"<?php echo (count($data) && $data['month'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHSIX'); ?></option><option value="7"<?php echo (count($data) && $data['month'] == 7 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHSEVEN'); ?></option><option value="8"<?php echo (count($data) && $data['month'] == 8 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHEIGHT'); ?></option><option value="9"<?php echo (count($data) && $data['month'] == 9 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHNINE'); ?></option><option value="10"<?php echo (count($data) && $data['month'] == 10 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHTEN'); ?></option><option value="11"<?php echo (count($data) && $data['month'] == 11 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHELEVEN'); ?></option><option value="12"<?php echo (count($data) && $data['month'] == 12 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONTHTWELVE'); ?></option></select></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWRESTRICTIONOR'); ?>*</b></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWRESTRICTIONDATERANGE'); ?>*</b></td>
					<td><div style="display: block; margin-bottom: 3px;"><?php echo '<span class="vbrestrdrangesp">'.JText::_('VBNEWRESTRICTIONDFROMRANGE').'</span>'.$vbo_app->getCalendar($dfromval, 'dfrom', 'dfrom', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></div><div style="display: block; margin-bottom: 3px;"><?php echo '<span class="vbrestrdrangesp">'.JText::_('VBNEWRESTRICTIONDTORANGE').'</span>'.$vbo_app->getCalendar($dtoval, 'dto', 'dto', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></div></td>
				</tr>
				<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWRESTRICTIONWDAY'); ?></b></td>
					<td>
						<select name="wday" onchange="vbSecondArrWDay();"><option value=""></option><option value="0"<?php echo (count($data) && strlen($data['wday']) > 0 && $data['wday'] == 0 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBSUNDAY'); ?></option><option value="1"<?php echo (count($data) && $data['wday'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONDAY'); ?></option><option value="2"<?php echo (count($data) && $data['wday'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBTUESDAY'); ?></option><option value="3"<?php echo (count($data) && $data['wday'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBWEDNESDAY'); ?></option><option value="4"<?php echo (count($data) && $data['wday'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBTHURSDAY'); ?></option><option value="5"<?php echo (count($data) && $data['wday'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBFRIDAY'); ?></option><option value="6"<?php echo (count($data) && $data['wday'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBSATURDAY'); ?></option></select>
						<div class="vbwdaytwodiv" id="vbwdaytwodivid" style="display: <?php echo (count($data) && strlen($data['wday']) > 0 ? 'inline-block' : 'none'); ?>;"><span><?php echo JText::_('VBNEWRESTRICTIONOR'); ?></span> 
						<select name="wdaytwo" onchange="vbComboArrWDay();"><option value=""></option><option value="0"<?php echo (count($data) && strlen($data['wdaytwo']) > 0 && $data['wdaytwo'] == 0 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBSUNDAY'); ?></option><option value="1"<?php echo (count($data) && $data['wdaytwo'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBMONDAY'); ?></option><option value="2"<?php echo (count($data) && $data['wdaytwo'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBTUESDAY'); ?></option><option value="3"<?php echo (count($data) && $data['wdaytwo'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBWEDNESDAY'); ?></option><option value="4"<?php echo (count($data) && $data['wdaytwo'] == 4 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBTHURSDAY'); ?></option><option value="5"<?php echo (count($data) && $data['wdaytwo'] == 5 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBFRIDAY'); ?></option><option value="6"<?php echo (count($data) && $data['wdaytwo'] == 6 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBSATURDAY'); ?></option></select></div>
						<div class="vbwdaycombodiv" id="vbwdaycombodivid" style="display: <?php echo (count($data) && !empty($data['wdaycombo']) && strlen($data['wdaycombo']) > 3 ? 'block' : 'none'); ?>;"><span class="vbwdaycombosp"><?php echo JText::_('VBNEWRESTRICTIONALLCOMBO'); ?></span><span class="vbwdaycombohelp"><?php echo JText::_('VBNEWRESTRICTIONALLCOMBOHELP'); ?></span>
						<p class="vbwdaycombop"><label for="vbrcomboa" style="display: inline-block; vertical-align: top;"><span id="vbrcomboa1"><?php echo strlen($vbra1) ? $arrwdays[intval($vbra1)] : ''; ?></span> - <span id="vbrcomboa2"><?php echo strlen($vbra2) ? $arrwdays[intval($vbra2)] : ''; ?></span></label> <input type="checkbox" name="comboa" id="vbrcomboa" value="<?php echo strlen($vbra1) ? $vbra1.'-'.$vbra2 : ''; ?>"<?php echo (strlen($vbra1) && $vbcomboparts[0] == $vbra1.'-'.$vbra2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
						<p class="vbwdaycombop"><label for="vbrcombob" style="display: inline-block; vertical-align: top;"><span id="vbrcombob1"><?php echo strlen($vbrb1) ? $arrwdays[intval($vbrb1)] : ''; ?></span> - <span id="vbrcombob2"><?php echo strlen($vbrb2) ? $arrwdays[intval($vbrb2)] : ''; ?></span></label> <input type="checkbox" name="combob" id="vbrcombob" value="<?php echo strlen($vbrb1) ? $vbrb1.'-'.$vbrb2 : ''; ?>"<?php echo (strlen($vbrb1) && $vbcomboparts[1] == $vbrb1.'-'.$vbrb2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
						<p class="vbwdaycombop"><label for="vbrcomboc" style="display: inline-block; vertical-align: top;"><span id="vbrcomboc1"><?php echo strlen($vbrc1) ? $arrwdays[intval($vbrc1)] : ''; ?></span> - <span id="vbrcomboc2"><?php echo strlen($vbrc2) ? $arrwdays[intval($vbrc2)] : ''; ?></span></label> <input type="checkbox" name="comboc" id="vbrcomboc" value="<?php echo strlen($vbrc1) ? $vbrc1.'-'.$vbrc2 : ''; ?>"<?php echo (strlen($vbrc1) && $vbcomboparts[2] == $vbrc1.'-'.$vbrc2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
						<p class="vbwdaycombop"><label for="vbrcombod" style="display: inline-block; vertical-align: top;"><span id="vbrcombod1"><?php echo strlen($vbrd1) ? $arrwdays[intval($vbrd1)] : ''; ?></span> - <span id="vbrcombod2"><?php echo strlen($vbrd2) ? $arrwdays[intval($vbrd2)] : ''; ?></span></label> <input type="checkbox" name="combod" id="vbrcombod" value="<?php echo strlen($vbrd1) ? $vbrd1.'-'.$vbrd2 : ''; ?>"<?php echo (strlen($vbrd1) && $vbcomboparts[3] == $vbrd1.'-'.$vbrd2 ? ' checked="checked"' : ''); ?> style="display: inline-block; vertical-align: top;"/></p>
						</div>
					</td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWRESTRICTIONMINLOS'); ?>*</b></td>
					<td><input type="number" name="minlos" value="<?php echo count($data) ? $data['minlos'] : '1'; ?>" min="1" size="3" style="width: 60px !important;" /></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWRESTRICTIONMULTIPLYMINLOS'), 'content' => JText::_('VBNEWRESTRICTIONMULTIPLYMINLOSHELP'))); ?> <b><?php echo JText::_('VBNEWRESTRICTIONMULTIPLYMINLOS'); ?></b></td>
					<td><input type="checkbox" name="multiplyminlos" value="1"<?php echo (count($data) && $data['multiplyminlos'] == 1 ? ' checked="checked"' : ''); ?>/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWRESTRICTIONMAXLOS'); ?></b></td>
					<td><input type="number" name="maxlos" value="<?php echo count($data) ? $data['maxlos'] : '0'; ?>" min="0" size="3" style="width: 60px !important;" /></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWRESTRICTIONSETCTA'); ?></b></td>
					<td><input type="checkbox" name="cta" value="1" onclick="vbToggleCta();"<?php echo count($cur_setcta) > 0 ? ' checked="checked"' : ''; ?>/><div id="vbrestrctadiv" style="display: <?php echo count($cur_setcta) > 0 ? ' block' : 'none'; ?>;"><span class="vbrestrroomssp"><?php echo JText::_('VBNEWRESTRICTIONWDAYSCTA'); ?></span><?php echo $ctasel; ?></div></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWRESTRICTIONSETCTD'); ?></b></td>
					<td><input type="checkbox" name="ctd" value="1" onclick="vbToggleCtd();"<?php echo count($cur_setctd) > 0 ? ' checked="checked"' : ''; ?>/><div id="vbrestrctddiv" style="display: <?php echo count($cur_setctd) > 0 ? ' block' : 'none'; ?>;"><span class="vbrestrroomssp"><?php echo JText::_('VBNEWRESTRICTIONWDAYSCTD'); ?></span><?php echo $ctdsel; ?></div></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWRESTRICTIONALLROOMS'); ?></b></td>
					<td><input type="checkbox" name="allrooms" value="1" onclick="vbToggleRooms();"<?php echo ((count($data) && $data['allrooms'] == 1) || !count($data) ? ' checked="checked"' : ''); ?>/><div id="vbrestrroomsdiv" style="display: <?php echo ((count($data) && $data['allrooms'] == 1) || !count($data) ? 'none' : 'block'); ?>;"><span class="vbrestrroomssp"><?php echo JText::_('VBNEWRESTRICTIONROOMSAFF'); ?></span><?php echo $roomsel; ?></div></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
<?php
if (count($data)) :
?>
	<input type="hidden" name="where" value="<?php echo $data['id']; ?>">
<?php
endif;
?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#dfrom').val('<?php echo $dfromval; ?>').attr('data-alt-value', '<?php echo $dfromval; ?>');
	jQuery('#dto').val('<?php echo $dtoval; ?>').attr('data-alt-value', '<?php echo $dtoval; ?>');
});
<?php
if (count($data) && strlen($data['wday']) > 0 && strlen($data['wdaytwo']) > 0) {
	?>
vbComboArrWDay();
	<?php
}
?>
</script>