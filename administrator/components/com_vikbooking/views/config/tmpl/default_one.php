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
$timeopst = VikBooking::getTimeOpenStore(true);
if (is_array($timeopst)) {
	$openat = VikBooking::getHoursMinutes($timeopst[0]);
	$closeat = VikBooking::getHoursMinutes($timeopst[1]);
} else {
	$openat = array(0, 0);
	$closeat = array(0, 0);
}
$wcheckintime = "<select name=\"timeopenstorefh\">\n";
for ($i = 0; $i <= 23; $i++) {
	if ($i < 10) {
		$in = "0".$i;
	} else {
		$in = $i;
	}
	$stat = $openat[0] == $i ? " selected=\"selected\"" : "";
	$wcheckintime .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$wcheckintime .= "</select> <select name=\"timeopenstorefm\">\n";
for ($i = 0; $i <= 59; $i++) {
	if ($i < 10) {
		$in = "0".$i;
	} else {
		$in = $i;
	}
	$stat = $openat[1] == $i ? " selected=\"selected\"" : "";
	$wcheckintime .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$wcheckintime .= "</select>\n";
$wcheckouttime = "<select name=\"timeopenstoreth\">\n";
for ($i = 0; $i <= 23; $i++) {
	if ($i < 10) {
		$in = "0".$i;
	} else {
		$in = $i;
	}
	$stat = $closeat[0]==$i ? " selected=\"selected\"" : "";
	$wcheckouttime .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$wcheckouttime .= "</select> <select name=\"timeopenstoretm\">\n";
for ($i = 0; $i <= 59; $i++) {
	if ($i < 10) {
		$in = "0".$i;
	} else {
		$in = $i;
	}
	$stat = $closeat[1] == $i ? " selected=\"selected\"" : "";
	$wcheckouttime .= "<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$wcheckouttime .= "</select>\n";

$calendartype = VikBooking::calendarType(true);

$globnumadults = VikBooking::getSearchNumAdults(true);
$adultsparts = explode('-', $globnumadults);
$globnumchildren = VikBooking::getSearchNumChildren(true);
$childrenparts = explode('-', $globnumchildren);

$maxdatefuture = VikBooking::getMaxDateFuture(true);
$maxdate_val = intval(substr($maxdatefuture, 1, (strlen($maxdatefuture) - 1)));
$maxdate_interval = substr($maxdatefuture, -1, 1);

$smartseach_type = VikBooking::getSmartSearchType(true);

$vbosef = file_exists(VBO_SITE_PATH.DS.'router.php');

$vcm_autoupd = (int)VikBooking::vcmAutoUpdate();

$nowdf = VikBooking::getDateFormat(true);
if ($nowdf=="%d/%m/%Y") {
	$usedf='d/m/Y';
} elseif ($nowdf=="%m/%d/%Y") {
	$usedf='m/d/Y';
} else {
	$usedf='Y/m/d';
}
$datesep = VikBooking::getDateSeparator(true);
?>
<script type="text/javascript">
function vboRemoveElement(el) {
	return (elem=document.getElementById(el)).parentNode.removeChild(elem);
}
function vboAddClosingDate() {
	var cdfrom = document.getElementById('cdfrom').value;
	var cdto = document.getElementById('cdto').value;
	if (cdfrom.length && cdto.length) {
		var cdcounter = document.getElementsByClassName('vbo-closed-date-entry').length + 1;
		var cdstring = "<div class=\"vbo-closed-date-entry\" id=\"vbo-closed-date-entry"+cdcounter+"\"><span>"+cdfrom+"</span> - <span>"+cdto+"</span> <span class=\"vbo-closed-date-rm\" onclick=\"vboRemoveElement('vbo-closed-date-entry"+cdcounter+"');\"><i class=\"vboicn-cross\"></i> </span><input type=\"hidden\" name=\"cdsfrom[]\" value=\""+cdfrom+"\" /><input type=\"hidden\" name=\"cdsto[]\" value=\""+cdto+"\" /></div>";
		document.getElementById('vbo-config-closed-dates').innerHTML += cdstring;
		document.getElementById('cdfrom').value = '';
		document.getElementById('cdto').value = '';
	}
}
</script>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VBOCPARAMBOOKING'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell">
					<?php echo $vbo_app->createPopover(array('title' => JText::_('VBCONFIGVCMAUTOUPD'), 'content' => JText::_('VBCONFIGVCMAUTOUPDHELP'), 'icon_class' => 'vboicn-lifebuoy')); ?>
					<b><?php echo JText::_('VBCONFIGVCMAUTOUPD'); ?></b>
				</td>
				<td><?php echo $vbo_app->printYesNoButtons('vcmautoupd', JText::_('VBYES'), JText::_('VBNO'), ($vcm_autoupd < 0 ? 0 : $vcm_autoupd), 1, 0).($vcm_autoupd < 0 ? '<span class="vbo-config-warn">'.JText::_('VBCONFIGVCMAUTOUPDMISS').'</span>' : ''); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONEFIVE'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('allowbooking', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::allowBooking(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONESIX'); ?></b> </td>
				<td><textarea name="disabledbookingmsg" rows="5" cols="50"><?php echo VikBooking::getDisabledBookingMsg(); ?></textarea></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONETENSIX'); ?></b> </td>
				<td><input type="text" name="adminemail" value="<?php echo VikBooking::getAdminMail(); ?>" size="35"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBSENDEREMAIL'); ?></b> </td>
				<td><input type="text" name="senderemail" value="<?php echo VikBooking::getSenderMail(); ?>" size="35"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONESEVEN'); ?></b> </td>
				<td><?php echo $wcheckintime; ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONETHREE'); ?></b> </td>
				<td><?php echo $wcheckouttime; ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONEELEVEN'); ?></b> </td>
				<td>
					<select name="dateformat">
						<option value="%d/%m/%Y"<?php echo ($nowdf=="%d/%m/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCONFIGONETWELVE'); ?></option>
						<option value="%m/%d/%Y"<?php echo ($nowdf=="%m/%d/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCONFIGONEMDY'); ?></option>
						<option value="%Y/%m/%d"<?php echo ($nowdf=="%Y/%m/%d" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VBCONFIGONETENTHREE'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGDATESEP'); ?></b> </td>
				<td><input type="text" name="datesep" value="<?php echo $datesep; ?>" size="3"/></td>
			</tr>
			<?php
			$resmodcanc = VikBooking::getReservationModCanc();
			$resmodcancmin = VikBooking::getReservationModCancMin();
			?>
			<tr>
				<td width="200" class="vbo-config-param-cell">
					<?php echo $vbo_app->createPopover(array('title' => JText::_('VBOCONFIGALLOWMODCANC'), 'content' => JText::_('VBOCONFIGALLOWMODCANCHELP'))); ?>
					<b><?php echo JText::_('VBOCONFIGALLOWMODCANC'); ?></b>
				</td>
				<td>
					<script type="text/javascript">
					function vboChangeResModCanc(mode) {
						mode = parseInt(mode);
						document.getElementById('vbo-resmodcanc-lim').style.display = (mode > 0 ? 'inline-block' : 'none');
					}
					</script>
					<div class="vbo-resmodcanc-block">
						<select name="resmodcanc" onchange="vboChangeResModCanc(this.value);">
							<option value="0"<?php echo $resmodcanc == 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC0'); ?></option>
							<option value="1"<?php echo $resmodcanc == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC1'); ?></option>
							<option value="2"<?php echo $resmodcanc == 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC2'); ?></option>
							<option value="3"<?php echo $resmodcanc == 3 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC3'); ?></option>
							<option value="4"<?php echo $resmodcanc == 4 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOCONFIGMODCANC4'); ?></option>
						</select>
						<div class="vbo-resmodcanc-lim" id="vbo-resmodcanc-lim" style="display: <?php echo $resmodcanc > 0 ? 'inline-block' : 'none'; ?>;">
							<label for="resmodcancmin"><?php echo JText::_('VBOCONFIGMODCANCMINDAYS'); ?></label>
							<input type="number" min="0" name="resmodcancmin" id="resmodcancmin" value="<?php echo $resmodcancmin; ?>" />
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGTODAYBOOKINGS'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('todaybookings', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::todayBookings(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONECOUPONS'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('enablecoupons', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::couponsEnabled(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGENABLECUSTOMERPIN'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('enablepin', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::customersPinEnabled(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONETENFIVE'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('tokenform', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::tokenForm() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGREQUIRELOGIN'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('requirelogin', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::requireLogin(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"><?php echo $vbo_app->createPopover(array('title' => JText::_('VBCONFIGAUTODISTFEATURE'), 'content' => JText::_('VBCONFIGAUTODISTFEATUREHELP'))); ?> <b><?php echo JText::_('VBCONFIGAUTODISTFEATURE'); ?></b></td>
				<td><?php echo $vbo_app->printYesNoButtons('autoroomunit', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::autoRoomUnit() ? 1 : 0), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONETENSEVEN'); ?></b> </td>
				<td><input type="number" name="minuteslock" value="<?php echo VikBooking::getMinutesLock(); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGMINAUTOREMOVE'); ?></b> </td>
				<td><input type="number" name="minautoremove" value="<?php echo VikBooking::getMinutesAutoRemove(); ?>" min="0"/></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VBCONFIGSEARCHPARAMS'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGMINDAYSADVANCE'); ?></b> </td>
				<td><input type="number" name="mindaysadvance" value="<?php echo VikBooking::getMinDaysAdvance(true); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSEARCHDEFNIGHTS'); ?></b> </td>
				<td><input type="number" name="autodefcalnights" value="<?php echo VikBooking::getDefaultNightsCalendar(true); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSEARCHPNUMROOM'); ?></b> </td>
				<td><input type="number" name="numrooms" value="<?php echo VikBooking::getSearchNumRooms(true); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSEARCHPNUMADULTS'); ?></b> </td>
				<td><?php echo JText::_('VBCONFIGSEARCHPFROM'); ?> <input type="number" name="numadultsfrom" value="<?php echo $adultsparts[0]; ?>" min="0"/> &nbsp;&nbsp; <?php echo JText::_('VBCONFIGSEARCHPTO'); ?> <input type="number" name="numadultsto" value="<?php echo $adultsparts[1]; ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSEARCHPNUMCHILDREN'); ?></b> </td>
				<td><?php echo JText::_('VBCONFIGSEARCHPFROM'); ?> <input type="number" name="numchildrenfrom" value="<?php echo $childrenparts[0]; ?>" min="0"/> &nbsp;&nbsp; <?php echo JText::_('VBCONFIGSEARCHPTO'); ?> <input type="number" name="numchildrento" value="<?php echo $childrenparts[1]; ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSEARCHPMAXDATEFUT'); ?></b> </td>
				<td><input type="number" name="maxdate" value="<?php echo $maxdate_val; ?>" min="0"/> <select name="maxdateinterval"><option value="d"<?php echo $maxdate_interval == 'd' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPMAXDATEDAYS'); ?></option><option value="w"<?php echo $maxdate_interval == 'w' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPMAXDATEWEEKS'); ?></option><option value="m"<?php echo $maxdate_interval == 'm' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPMAXDATEMONTHS'); ?></option><option value="y"<?php echo $maxdate_interval == 'y' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPMAXDATEYEARS'); ?></option></select></td>
			</tr>

			<tr>
				<td width="200" class="vbo-config-param-cell">
					<?php echo $vbo_app->createPopover(array('title' => JText::_('VBCONFIGCLOSINGDATES'), 'content' => JText::_('VBCONFIGCLOSINGDATESHELP'))); ?>
					<b><?php echo JText::_('VBCONFIGCLOSINGDATES'); ?></b>
				</td>
				<td>
					<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
						<div class="btn-group pull-left">
							<?php echo $vbo_app->getCalendar('', 'cdfrom', 'cdfrom', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VBCONFIGCLOSINGDATEFROM'))); ?>
						</div>
						<div class="btn-group pull-left">
							<?php echo $vbo_app->getCalendar('', 'cdto', 'cdto', $nowdf, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder' => JText::_('VBCONFIGCLOSINGDATETO'))); ?>
						</div>
						<div class="btn-group pull-left">
							<button type="button" class="btn" onclick="vboAddClosingDate();"><i class="icon-new"></i><?php echo JText::_('VBCONFIGCLOSINGDATEADD'); ?></button>
						</div>
					</div>
					<div id="vbo-config-closed-dates" style="display: block;">
				<?php
				$cur_closed_dates = VikBooking::getClosingDates();
				if (is_array($cur_closed_dates) && count($cur_closed_dates)) {
					foreach ($cur_closed_dates as $kcd => $vcd) {
						echo "<div class=\"vbo-closed-date-entry\" id=\"vbo-closed-date-entry".$kcd."\"><span>".date(str_replace("/", $datesep, $usedf), $vcd['from'])."</span> - <span>".date(str_replace("/", $datesep, $usedf), $vcd['to'])."</span> <span class=\"vbo-closed-date-rm\" onclick=\"vboRemoveElement('vbo-closed-date-entry".$kcd."');\"><i class=\"vboicn-cross\"></i> </span><input type=\"hidden\" name=\"cdsfrom[]\" value=\"".date($usedf, $vcd['from'])."\" /><input type=\"hidden\" name=\"cdsto[]\" value=\"".date($usedf, $vcd['to'])."\" /></div>"."\n";
					}
				}
				?>
					</div>
				</td>
			</tr>

			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSEARCHPSMARTSEARCH'); ?></b> </td>
				<td><select name="smartsearch"><option value="dynamic"<?php echo $smartseach_type == 'dynamic' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPSMARTSEARCHDYN'); ?></option><option value="automatic"<?php echo $smartseach_type == 'automatic' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBCONFIGSEARCHPSMARTSEARCHAUTO'); ?></option></select></td>
			</tr>
			<?php
			$searchsugg = (int)VikBooking::showSearchSuggestions();
			?>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOCONFIGSHOWSEARCHSUGG'); ?></b> </td>
				<td>
					<select name="searchsuggestions">
						<option value="1"<?php echo $searchsugg == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOYESWITHAVAILABILITY'); ?></option>
						<option value="2"<?php echo $searchsugg == 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOYESNOAVAILABILITY'); ?></option>
						<option value="0"<?php echo $searchsugg == 0 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBNO'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONETENFOUR'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('showcategories', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::showCategoriesFront(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGSHOWCHILDREN'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('showchildren', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::showChildrenFront(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VBOCPARAMSYSTEM'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGCRONKEY'); ?></b> </td>
				<td><input type="text" name="cronkey" value="<?php echo VikBooking::getCronKey(); ?>" size="6" /></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGMULTILANG'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('multilang', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::allowMultiLanguage(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGROUTER'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('vbosef', JText::_('VBYES'), JText::_('VBNO'), (int)$vbosef, 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBLOADBOOTSTRAP'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('loadbootstrap', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::loadBootstrap(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBOLOADFA'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('usefa', JText::_('VBYES'), JText::_('VBNO'), (int)VikBooking::isFontAwesomeEnabled(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONEJQUERY'); ?></b> </td>
				<td><?php echo $vbo_app->printYesNoButtons('loadjquery', JText::_('VBYES'), JText::_('VBNO'), (VikBooking::loadJquery(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBCONFIGONECALENDAR'); ?></b> </td>
				<td><select name="calendar"><option value="jqueryui"<?php echo ($calendartype == "jqueryui" ? " selected=\"selected\"" : ""); ?>>jQuery UI</option><option value="joomla"<?php echo ($calendartype == "joomla" ? " selected=\"selected\"" : ""); ?>>Joomla</option></select></td>
			</tr>
		</tbody>
	</table>
</fieldset>