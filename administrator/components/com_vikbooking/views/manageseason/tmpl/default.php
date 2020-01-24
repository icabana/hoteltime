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

$sdata = $this->sdata;
$wsel = $this->wsel;
$wpricesel = $this->wpricesel;
$adults_diff = $this->adults_diff;

if (strlen($wsel) > 0) {
	$vbo_app = new VboApplication();
	$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
	$caldf = VikBooking::getDateFormat(true);
	$currencysymb = VikBooking::getCurrencySymb(true);
	if ($caldf == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($caldf == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	if (count($sdata) && ($sdata['from'] > 0 || $sdata['to'] > 0)) {
		$nowyear = !empty($sdata['year']) ? $sdata['year'] : date('Y');
		$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
		$fromdate = date($df, ($frombase + $sdata['from']));
		if ($sdata['to'] < $sdata['from']) {
			$nowyear = $nowyear + 1;
			$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
		}
		$todate = date($df, ($frombase + $sdata['to']));
		//leap years
		$checkly = !empty($sdata['year']) ? $sdata['year'] : date('Y');
		if ($checkly % 4 == 0 && ($checkly % 100 != 0 || $checkly % 400 == 0)) {
			$frombase = mktime(0, 0, 0, 1, 1, $checkly);
			$infoseason = getdate($frombase + $sdata['from']);
			$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
			if ($infoseason[0] >= $leapts) {
				$fromdate = date($df, ($frombase + $sdata['from'] + 86400));
				$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
				$todate = date($df, ($frombase + $sdata['to'] + 86400));
			}
		}
		//
	} else {
		$fromdate = '';
		$todate = '';
	}
	$actweekdays = count($sdata) ? explode(";", $sdata['wdays']) : array();
	
	$actvalueoverrides = '';
	if (count($sdata) && strlen($sdata['losoverride']) > 0) {
		$losoverrides = explode('_', $sdata['losoverride']);
		foreach($losoverrides as $loso) {
			if (!empty($loso)) {
				$losoparts = explode(':', $loso);
				$losoparts[2] = strstr($losoparts[0], '-i') != false ? 1 : 0;
				$losoparts[0] = str_replace('-i', '', $losoparts[0]);
				$actvalueoverrides .= '<p>'.JText::_('VBNEWSEASONNIGHTSOVR').' <input type="number" min="1" name="nightsoverrides[]" value="'.$losoparts[0].'" /> <select name="andmoreoverride[]"><option value="0">-------</option><option value="1"'.($losoparts[2] == 1 ? ' selected="selected"' : '').'>'.JText::_('VBNEWSEASONVALUESOVREMORE').'</option></select> - '.JText::_('VBNEWSEASONVALUESOVR').' <input type="number" step="any" name="valuesoverrides[]" value="'.$losoparts[1].'" style="min-width: 60px !important;"/> '.(intval($sdata['val_pcent']) == 2 ? '%' : $currencysymb).'</p>';
			}
		}
	}
	
	?>
	<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if ( task == 'removeseasons') {
			if (confirm('<?php echo addslashes(JText::_('VBDELCONFIRM')); ?>')) {
				Joomla.submitform(task, document.adminForm);
			} else {
				return false;
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}
	function addMoreOverrides() {
		var sel = document.getElementById('val_pcent');
		var curpcent = sel.options[sel.selectedIndex].text;
		var ni = document.getElementById('myDiv');
		var numi = document.getElementById('morevalueoverrides');
		var num = (document.getElementById('morevalueoverrides').value -1)+ 2;
		numi.value = num;
		var newdiv = document.createElement('div');
		var divIdName = 'my'+num+'Div';
		newdiv.setAttribute('id',divIdName);
		newdiv.innerHTML = '<p><?php echo addslashes(JText::_('VBNEWSEASONNIGHTSOVR')); ?> <input type=\'number\' min=\'1\' name=\'nightsoverrides[]\' value=\'\' /> <select name=\'andmoreoverride[]\'><option value=\'0\'>-------</option><option value=\'1\'><?php echo addslashes(JText::_('VBNEWSEASONVALUESOVREMORE')); ?></option></select> - <?php echo addslashes(JText::_('VBNEWSEASONVALUESOVR')); ?> <input type=\'number\' step=\'any\' name=\'valuesoverrides[]\' value=\'\' style=\'min-width: 60px !important;\'/> '+curpcent+'</p>';
		ni.appendChild(newdiv);
	}
	jQuery.noConflict();
	var rooms_sel_ids = [];
	var rooms_names_map = [];
	var rooms_adults_pricing = <?php echo json_encode($adults_diff); ?>;
	jQuery(document).ready(function() {
		var rseltag = document.getElementById("idrooms");
		for(var i=0; i < rseltag.length; i++) {
			rooms_names_map[rseltag.options[i].value] = rseltag.options[i].text;
		}
		jQuery(".vbo-select-all").click(function(){
			jQuery(this).next("select").find("option").prop('selected', true);
		});
		jQuery("#idrooms").change(function(){
			if (jQuery(this).val() !== null) {
				rooms_sel_ids = jQuery(this).val();
			} else {
				rooms_sel_ids = [];
			}
			updateOccupancyPricing();
		});
		jQuery(document.body).on('click', ".occupancy-room-name", function() {
			jQuery(this).next(".occupancy-room-data").fadeToggle();
		});
		//edit mode must trigger the change event when the document is ready
		jQuery("#idrooms").trigger("change");
	});
	function isFullObject(obj) {
		var jk;
		for(jk in obj) {
			return obj.hasOwnProperty(jk);
		}
	}
	function updateOccupancyPricing() {
		var occupancy_cont = jQuery("#vbo-occupancy-container");
		var usage_lbl = '<?php echo addslashes(JText::_('VBADULTSDIFFNUM')); ?>';
		if (rooms_sel_ids.length > 0) {
			jQuery("#vbo-occupancy-pricing-fieldset").fadeIn();
			jQuery(rooms_sel_ids).each(function(k, v){
				if (!rooms_adults_pricing.hasOwnProperty(v)) {
					return true;
				}
				if (jQuery("#occupancy-r"+v).length) {
					return true;
				}
				if (isFullObject(rooms_adults_pricing[v])) {
					//Occupancy supported
					var is_ovr = false;
					var occ_data = "<div id=\"occupancy-r"+v+"\" class=\"occupancy-room\"  data-roomid=\""+v+"\">"+
						"<div class=\"occupancy-room-name\">"+rooms_names_map[v]+"</div>"+
						"<div class=\"occupancy-room-data\">";
					for(var occ in rooms_adults_pricing[v]) {
						if (rooms_adults_pricing[v].hasOwnProperty(occ)) {
							occ_data += "<div class=\"occupancy-adults-data\">"+
								"<span class=\"occupancy-adults-lbl\">"+usage_lbl.replace("%s", occ)+"</span>"+
								"<div class=\"occupancy-adults-ovr\">"+
									"<select name=\"adultsdiffchdisc["+v+"]["+occ+"]\"><option value=\"1\""+(rooms_adults_pricing[v][occ].hasOwnProperty('chdisc') && rooms_adults_pricing[v][occ]['chdisc'] == 1 ? " selected=\"selected\"" : "")+"><?php echo addslashes(JText::_('VBADULTSDIFFCHDISCONE')); ?></option><option value=\"2\""+(rooms_adults_pricing[v][occ].hasOwnProperty('chdisc') && rooms_adults_pricing[v][occ]['chdisc'] == 2 ? " selected=\"selected\"" : "")+"><?php echo addslashes(JText::_('VBADULTSDIFFCHDISCTWO')); ?></option></select>"+
									"<input type=\"number\" step=\"any\" name=\"adultsdiffval["+v+"]["+occ+"]\" value=\""+(rooms_adults_pricing[v][occ].hasOwnProperty('override') && rooms_adults_pricing[v][occ].hasOwnProperty('value') ? rooms_adults_pricing[v][occ]['value'] : "")+"\" placeholder=\""+(rooms_adults_pricing[v][occ].hasOwnProperty('value') ? rooms_adults_pricing[v][occ]['value'] : "0.00")+"\" style=\"min-width: 60px !important;\"/>"+
									"<select name=\"adultsdiffvalpcent["+v+"]["+occ+"]\"><option value=\"1\""+(rooms_adults_pricing[v][occ].hasOwnProperty('valpcent') && rooms_adults_pricing[v][occ]['valpcent'] == 1 ? " selected=\"selected\"" : "")+"><?php echo $currencysymb; ?></option><option value=\"2\""+(rooms_adults_pricing[v][occ].hasOwnProperty('valpcent') && rooms_adults_pricing[v][occ]['valpcent'] == 2 ? " selected=\"selected\"" : "")+">%</option></select>"+
									"<select name=\"adultsdiffpernight["+v+"]["+occ+"]\"><option value=\"0\""+(rooms_adults_pricing[v][occ].hasOwnProperty('pernight') && rooms_adults_pricing[v][occ]['pernight'] <= 0 ? " selected=\"selected\"" : "")+"><?php echo addslashes(JText::_('VBADULTSDIFFONTOTAL')); ?></option><option value=\"1\""+(rooms_adults_pricing[v][occ].hasOwnProperty('pernight') && rooms_adults_pricing[v][occ]['pernight'] >= 1 ? " selected=\"selected\"" : "")+"><?php echo addslashes(JText::_('VBADULTSDIFFONPERNIGHT')); ?></option></select>"+
								"</div>"+
								"</div>";
							is_ovr = rooms_adults_pricing[v][occ].hasOwnProperty('override') ? true : is_ovr;
						}
					}
					occ_data += "</div>"+
						"</div>";
					occupancy_cont.append(occ_data);
					if (is_ovr === true) {
						jQuery("#occupancy-r"+v).find(".occupancy-room-name").trigger("click");
					}
				} else {
					//Occupancy not supported (same fromadult and toadult)
					occupancy_cont.append("<div id=\"occupancy-r"+v+"\" class=\"occupancy-room\" data-roomid=\""+v+"\">"+
						"<div class=\"occupancy-room-name\">"+rooms_names_map[v]+"</div>"+
						"<div class=\"occupancy-room-data\"><p><?php echo addslashes(JText::_('VBOROOMOCCUPANCYPRNOTSUPP')); ?></p></div>"+
						"</div>");
				}
			});
		} else {
			jQuery("#vbo-occupancy-pricing-fieldset").fadeOut();
		}
		//hide the un-selected rooms
		jQuery(".occupancy-room").each(function() {
			var rid = jQuery(this).attr("data-roomid");
			if (jQuery.inArray(rid, rooms_sel_ids) == -1) {
				jQuery(this).remove();
			}
		});
		//
	}
	function togglePromotion() {
		var promo_on = document.getElementById('promo').checked;
		if (promo_on === true) {
			jQuery('.promotr').fadeIn();
			var cur_startd = jQuery('#from').val();
			jQuery('#promovalidity span').text('');
			if (cur_startd.length) {
				jQuery('#promovalidity span').text(' ('+cur_startd+')');
			}
		} else {
			jQuery('.promotr').fadeOut();
		}
	}
	</script>
	<input type="hidden" value="0" id="morevalueoverrides" />
	
	<form name="adminForm" id="adminForm" action="index.php" method="post">

		<fieldset class="adminform fieldset-left">
			<legend class="adminlegend"><?php echo JText::_('VBSEASON'); ?> &nbsp;&nbsp;<?php echo $vbo_app->createPopover(array('title' => JText::_('VBSPRICESHELPTITLE'), 'content' => JText::_('VBSPRICESHELP'))); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWSEASONONE'); ?></b> </td>
						<td><?php echo $vbo_app->getCalendar($fromdate, 'from', 'from', $caldf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWSEASONTWO'); ?></b> </td>
						<td><?php echo $vbo_app->getCalendar($todate, 'to', 'to', $caldf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBSPONLYPICKINCL'); ?></b> </td>
						<td><?php echo $vbo_app->printYesNoButtons('checkinincl', JText::_('VBYES'), JText::_('VBNO'), (count($sdata) ? (int)$sdata['checkinincl'] : 0), 1, 0); ?></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBSPYEARTIED'); ?></b> </td>
						<td><?php echo $vbo_app->printYesNoButtons('yeartied', JText::_('VBYES'), JText::_('VBNO'), (count($sdata) && !empty($sdata['year']) ? 1 : 0), 1, 0); ?></td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<fieldset class="adminform fieldset-left">
			<legend class="adminlegend"><?php echo JText::_('VBWEEKDAYS'); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vbo-config-param-cell" style="vertical-align: top;"> <b><?php echo JText::_('VBSEASONDAYS'); ?></b> </td>
						<td><select multiple="multiple" size="7" name="wdays[]"><option value="0"<?php echo (in_array("0", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBSUNDAY'); ?></option><option value="1"<?php echo (in_array("1", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBMONDAY'); ?></option><option value="2"<?php echo (in_array("2", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBTUESDAY'); ?></option><option value="3"<?php echo (in_array("3", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBWEDNESDAY'); ?></option><option value="4"<?php echo (in_array("4", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBTHURSDAY'); ?></option><option value="5"<?php echo (in_array("5", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBFRIDAY'); ?></option><option value="6"<?php echo (in_array("6", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBSATURDAY'); ?></option></select></td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<br clear="all" />

		<fieldset class="adminform fieldset-half">
			<legend class="adminlegend"><?php echo JText::_('VBSPMAINSETTINGS'); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBSPNAME'); ?></b> </td>
						<td><input type="text" name="spname" value="<?php echo count($sdata) ? $sdata['spname'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWSEASONTHREE'); ?></b> </td>
						<td><select name="type"><option value="1"<?php echo (count($sdata) && intval($sdata['type']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWSEASONSIX'); ?></option><option value="2"<?php echo (count($sdata) && intval($sdata['type']) == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBNEWSEASONSEVEN'); ?></option></select></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWSEASONFOUR'); ?></b> </td>
						<td><input type="number" step="any" name="diffcost" value="<?php echo count($sdata) ? $sdata['diffcost'] : ''; ?>" style="min-width: 60px !important;"/> <select name="val_pcent" id="val_pcent"><option value="2"<?php echo (count($sdata) && intval($sdata['val_pcent']) == 2 ? " selected=\"selected\"" : ""); ?>>%</option><option value="1"<?php echo (count($sdata) && intval($sdata['val_pcent']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option></select> &nbsp;<?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWSEASONFOUR'), 'content' => JText::_('VBSPECIALPRICEVALHELP'))); ?></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWSEASONVALUEOVERRIDE'); ?></b> <?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWSEASONVALUEOVERRIDE'), 'content' => JText::_('VBNEWSEASONVALUEOVERRIDEHELP'))); ?></td>
						<td><div id="myDiv" style="display: block;"><?php echo $actvalueoverrides; ?></div><a href="javascript: void(0);" onclick="addMoreOverrides();"><?php echo JText::_('VBNEWSEASONADDOVERRIDE'); ?></a></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWSEASONROUNDCOST'); ?></b> </td>
						<td><select name="roundmode"><option value=""><?php echo JText::_('VBNEWSEASONROUNDCOSTNO'); ?></option><option value="PHP_ROUND_HALF_UP"<?php echo (count($sdata) && $sdata['roundmode'] == 'PHP_ROUND_HALF_UP' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWSEASONROUNDCOSTUP'); ?></option><option value="PHP_ROUND_HALF_DOWN"<?php echo (count($sdata) && $sdata['roundmode'] == 'PHP_ROUND_HALF_DOWN' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWSEASONROUNDCOSTDOWN'); ?></option></select></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWSEASONFIVE'); ?></b> </td>
						<td><span class="vbo-select-all"><?php echo JText::_('VBOSELECTALL'); ?></span><?php echo $wsel; ?></td>
					</tr>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOSPTYPESPRICE'); ?></b> </td>
						<td><span class="vbo-select-all"><?php echo JText::_('VBOSELECTALL'); ?></span><?php echo $wpricesel; ?></td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<fieldset class="adminform fieldset-half" id="vbo-occupancy-pricing-fieldset" style="display: none;">
			<legend class="adminlegend"><?php echo JText::_('VBSEASONOCCUPANCYPR'); ?></legend>
			<div id="vbo-occupancy-container"></div>
		</fieldset>

		<br clear="all" />

		<fieldset class="adminform">
			<legend class="adminlegend"><?php echo JText::_('VBSPPROMOTIONLABEL'); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOISPROMOTION'); ?></b> </td>
						<td><input type="checkbox" id="promo" name="promo" value="1" onclick="togglePromotion();" <?php echo count($sdata) && $sdata['promo'] == 1 ? "checked=\"checked\"" : ""; ?>/></td>
					</tr>
					<tr class="promotr">
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOPROMOVALIDITY'); ?></b> </td>
						<td><input type="number" name="promodaysadv" value="<?php echo !count($sdata) || (count($sdata) && empty($sdata['promodaysadv'])) ? '0' : $sdata['promodaysadv']; ?>" size="5"/><span id="promovalidity"><?php echo JText::_('VBOPROMOVALIDITYDAYSADV'); ?><span></span></span></td>
					</tr>
					<tr class="promotr">
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBPROMOFORCEMINLOS'); ?></b> </td>
						<td><input type="number" name="promominlos" value="<?php echo !count($sdata) || (count($sdata) && empty($sdata['promominlos'])) ? '0' : $sdata['promominlos']; ?>" size="5"/></td>
					</tr>
					<tr class="promotr">
						<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOPROMOTEXT'); ?></b> </td>
						<td><?php echo $editor->display( "promotxt", (count($sdata) ? $sdata['promotxt'] : ""), '100%', 300, 70, 20 ); ?></td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikbooking">
	<?php
	if (count($sdata)) :
	?>
		<input type="hidden" name="where" value="<?php echo $sdata['id']; ?>">
	<?php
	endif;
	?>
	</form>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#from').val('<?php echo $fromdate; ?>').attr('data-alt-value', '<?php echo $fromdate; ?>');
		jQuery('#to').val('<?php echo $todate; ?>').attr('data-alt-value', '<?php echo $todate; ?>');
	});
	togglePromotion();
	</script>
	<?php
} else {
	?>
	<p class="warn"><a href="index.php?option=com_vikbooking&amp;task=newroom"><?php echo JText::_('VBNOROOMSFOUNDSEASONS'); ?></a></p>
	<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikbooking" />
	</form>
	<?php
}