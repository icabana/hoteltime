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

$all_rooms = $this->all_rooms;
$roomrows = $this->roomrows;
$seasoncal_nights = $this->seasons_cal_nights;
$seasons_cal = $this->seasons_cal;
$tsstart = $this->tsstart;
$roomrates = $this->roomrates;
$booked_dates = $this->booked_dates;

$vbo_app = new VboApplication();
$pdebug = VikRequest::getint('e4j_debug', '', 'request');
$document = JFactory::getDocument();
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery-ui.min.css');
JHtml::_('jquery.framework', true, true);
JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
$currencysymb = VikBooking::getCurrencySymb();
$vbo_df = VikBooking::getDateFormat();
$datesep = VikBooking::getDateSeparator();
$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
$juidf = $vbo_df == "%d/%m/%Y" ? 'dd/mm/yy' : ($vbo_df == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');
$ldecl = '
jQuery(function($){'."\n".'
	$.datepicker.regional["vikbooking"] = {'."\n".'
		closeText: "'.JText::_('VBJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VBJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VBJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VBJQCALTODAY').'",'."\n".'
		monthNames: ["'.JText::_('VBMONTHONE').'","'.JText::_('VBMONTHTWO').'","'.JText::_('VBMONTHTHREE').'","'.JText::_('VBMONTHFOUR').'","'.JText::_('VBMONTHFIVE').'","'.JText::_('VBMONTHSIX').'","'.JText::_('VBMONTHSEVEN').'","'.JText::_('VBMONTHEIGHT').'","'.JText::_('VBMONTHNINE').'","'.JText::_('VBMONTHTEN').'","'.JText::_('VBMONTHELEVEN').'","'.JText::_('VBMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(JText::_('VBMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.JText::_('VBSUNDAY').'", "'.JText::_('VBMONDAY').'", "'.JText::_('VBTUESDAY').'", "'.JText::_('VBWEDNESDAY').'", "'.JText::_('VBTHURSDAY').'", "'.JText::_('VBFRIDAY').'", "'.JText::_('VBSATURDAY').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(JText::_('VBSUNDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBMONDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBTUESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBWEDNESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBTHURSDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBFRIDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBSATURDAY'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(JText::_('VBSUNDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBMONDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBTUESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBWEDNESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBTHURSDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBFRIDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBSATURDAY'), 0, 2, 'UTF-8').'"],'."\n".'
		weekHeader: "'.JText::_('VBJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikBooking::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikbooking"]);'."\n".'
});
var vboMapWdays = ["'.mb_substr(JText::_('VBSUNDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBMONDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBTUESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBWEDNESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBTHURSDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBFRIDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBSATURDAY'), 0, 3, 'UTF-8').'"];
var vboMapMons = ["'.mb_substr(JText::_('VBMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWELVE'), 0, 3, 'UTF-8').'"];';
$document->addScriptDeclaration($ldecl);
$price_types_show = true;
$los_show = true;
$cookie = JFactory::getApplication()->input->cookie;
$cookie_tab = $cookie->get('vboRovwRab', 'cal', 'string');
//Prepare modal
echo $vbo_app->getJmodalScript();
echo $vbo_app->getJmodalHtml('vbo-vcm-rates-res', JText::_('VBOVCMRATESRES'), '', 'width: 90%; height: 80%; margin-left: -45%; top: 10% !important;');
//end Prepare modal
?>
<div class="vbo-ratesoverview-roomsel-block">
	<form method="get" action="index.php?option=com_vikbooking" name="vboratesovwform">
	<input type="hidden" name="option" value="com_vikbooking" />
		<input type="hidden" name="task" value="ratesoverv" />
		<div class="vbo-ratesoverview-roomsel-entry">
			<label for="roomsel"><?php echo JText::_('VBRATESOVWROOM'); ?></label>
			<select name="cid[]" onchange="document.vboratesovwform.submit();" id="roomsel">
	<?php
	foreach ($all_rooms as $room) {
		?>
			<option value="<?php echo $room['id']; ?>"<?php echo $room['id'] == $roomrows['id'] ? ' selected="selected"' : ''; ?>><?php echo $room['name']; ?></option>
		<?php
	}
	?>
			</select>
			<button type="button" class="btn" style="vertical-align: top;" onclick="document.vboratesovwform.submit();"><i class="vboicn-loop2"></i></button>
		</div>
		<div class="vbo-ratesoverview-roomsel-entry vbo-ratesoverview-roomsel-entry-los"<?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? ' style="display: none;"' : ''); ?>>
			<label><?php echo JText::_('VBRATESOVWNUMNIGHTSACT'); ?></label>
	<?php
	foreach ($seasoncal_nights as $numnights) {
		?>
			<span class="vbo-ratesoverview-numnight" id="numnights<?php echo $numnights; ?>"><?php echo $numnights; ?></span>
			<input type="hidden" name="nights_cal[]" id="inpnumnights<?php echo $numnights; ?>" value="<?php echo $numnights; ?>" />
		<?php
	}
	?>
			<input type="number" id="vbo-addnumnight" value="<?php echo ($numnights + 1); ?>" min="1"/>
			<span id="vbo-addnumnight-act"><i class="fa fa-plus-square"></i></span>
			<button type="button" class="btn vbo-apply-los-btn" onclick="document.vboratesovwform.submit();"><?php echo JText::_('VBRATESOVWAPPLYLOS'); ?></button>
		</div>
		<div class="vbo-ratesoverview-roomsel-entry">
			<label for="roomselcalc"><?php echo JText::_('VBRATESOVWRATESCALCULATOR'); ?></label>
			<span class="vbo-ratesoverview-entryinline"><?php echo $vbo_app->getCalendar('', 'checkindate', 'checkindate', '%Y-%m-%d', array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true', 'placeholder'=>JText::_('VBPICKUPAT'))); ?></span>
			<span class="vbo-ratesoverview-entryinline"><span><?php echo JText::_('VBRATESOVWRATESCALCNUMNIGHTS'); ?></span> <input type="number" id="vbo-numnights" value="1" min="1" max="999" step="1" /></span>
			<span class="vbo-ratesoverview-entryinline"><span><?php echo JText::_('VBRATESOVWRATESCALCNUMADULTS'); ?></span> <input type="number" id="vbo-numadults" value="<?php echo $roomrows['fromadult']; ?>" min="<?php echo $roomrows['fromadult']; ?>" max="<?php echo $roomrows['toadult']; ?>" step="1"/></span>
			<span class="vbo-ratesoverview-entryinline"><span><?php echo JText::_('VBRATESOVWRATESCALCNUMCHILDREN'); ?></span> <input type="number" id="vbo-numchildren" value="<?php echo $roomrows['fromchild']; ?>" min="<?php echo $roomrows['fromchild']; ?>" max="<?php echo $roomrows['tochild']; ?>" step="1"/></span>
			<span class="vbo-ratesoverview-entryinline"><button type="button" class="btn" id="vbo-ratesoverview-calculate"><?php echo JText::_('VBRATESOVWRATESCALCULATORCALC'); ?></button></span>
		</div>
	</form>
</div>
<div class="vbo-ratesoverview-right-block">
	<div class="vbo-ratesoverview-right-inner"></div>
</div>
<br clear="all" />
<div class="vbo-ratesoverview-calculation-response"></div>
<div class="vbo-ratesoverview-roomdetails">
	<h3><i class="fa fa-bed"></i> <?php echo $roomrows['name']; ?></h3>
</div>

<div class="vbo-ratesoverview-tabscont">
	<div class="vbo-ratesoverview-tab-cal <?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? 'vbo-ratesoverview-tab-active' : 'vbo-ratesoverview-tab-unactive'); ?>"><i class="vboicn-calendar"></i> <?php echo JText::_('VBRATESOVWTABCALENDAR'); ?></div>
	<div class="vbo-ratesoverview-tab-los <?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? 'vbo-ratesoverview-tab-unactive' : 'vbo-ratesoverview-tab-active'); ?>"><i class="vboicn-clock"></i> <?php echo JText::_('VBRATESOVWTABLOS'); ?></div>
</div>
<br clear="all" />

<div class="vbo-ratesoverview-caltab-cont" style="display: <?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? 'block' : 'none'); ?>;">
	<div class="vbo-ratesoverview-caltab-wrapper">
		<div class="vbo-table-responsive">
			<table class="vboverviewtable vbratesoverviewtable vbo-table">
				<tbody>
					<tr class="vbo-roverviewrowone">
						<td class="bluedays">
							<form name="vbratesoverview" method="post" action="index.php?option=com_vikbooking&amp;task=ratesoverv">
								<div class="vbo-roverview-datecmd-top">
									<div class="vbo-roverview-datecmd-date">
										<span>
											<i class="fa fa-calendar"></i>
											<input type="text" autocomplete="off" value="<?php echo date($df, $tsstart); ?>" class="vbodatepicker" id="vbodatepicker" name="startdate" />
										</span>
									</div>
								</div>
								<div class="vbo-roverview-datecmd">
									<a class="vboosprevweek" onclick="prevWeek();" href="javascript: void(0);"><i class="fa fa-angle-double-left"></i></a>
									<a class="vboosprevday" onclick="prevDay();" href="javascript: void(0);"><i class="fa fa-angle-left"></i></a>
									<a class="vboosnextday" onclick="nextDay();" href="javascript: void(0);"><i class="fa fa-angle-right"></i></a>
									<a class="vboosnextweek" onclick="nextWeek();" href="javascript: void(0);"><i class="fa fa-angle-double-right"></i></a>
									<input type="hidden" name="cid[]" value="<?php echo $roomrows['id']; ?>" />
								</div>
							</form>
						</td>
					<?php
					$nowts = getdate($tsstart);
					$days_labels = array(
						JText::_('VBSUN'),
						JText::_('VBMON'),
						JText::_('VBTUE'),
						JText::_('VBWED'),
						JText::_('VBTHU'),
						JText::_('VBFRI'),
						JText::_('VBSAT')
					);
					$months_labels = array(
						JText::_('VBMONTHONE'),
						JText::_('VBMONTHTWO'),
						JText::_('VBMONTHTHREE'),
						JText::_('VBMONTHFOUR'),
						JText::_('VBMONTHFIVE'),
						JText::_('VBMONTHSIX'),
						JText::_('VBMONTHSEVEN'),
						JText::_('VBMONTHEIGHT'),
						JText::_('VBMONTHNINE'),
						JText::_('VBMONTHTEN'),
						JText::_('VBMONTHELEVEN'),
						JText::_('VBMONTHTWELVE')
					);
					foreach( $months_labels as $i => $v ) {
						$months_labels[$i] = mb_substr($v, 0, 3, 'UTF-8');
					}
					$cell_count = 0;
					$MAX_DAYS = 60;
					$MAX_TO_DISPLAY = 14;
					$pcheckinh = 0;
					$pcheckinm = 0;
					$pcheckouth = 0;
					$pcheckoutm = 0;
					$timeopst = VikBooking::getTimeOpenStore();
					if (is_array($timeopst)) {
						$opent = VikBooking::getHoursMinutes($timeopst[0]);
						$closet = VikBooking::getHoursMinutes($timeopst[1]);
						$pcheckinh = $opent[0];
						$pcheckinm = $opent[1];
						$pcheckouth = $closet[0];
						$pcheckoutm = $closet[1];
					}
					$start_day_id = 'cell-'.$nowts['mday'].'-'.$nowts['mon'];
					$end_day_id = '';
					$weekend_arr = array(0, 6);
					while ($cell_count < $MAX_DAYS) {
						$style = '';
						if ( $cell_count >= $MAX_TO_DISPLAY ) {
							$style = 'style="display: none;"';
						} else {
							$end_day_id = 'cell-'.$nowts['mday'].'-'.$nowts['mon'];
						}
						?>
						<td class="bluedays <?php echo 'cell-'.$nowts['mday'].'-'.$nowts['mon']; ?><?php echo in_array((int)$nowts['wday'], $weekend_arr) ? ' vbo-roverw-tablewday-wend' : ''; ?>" <?php echo $style; ?>>
							<span class="vbo-roverw-tablewday"><?php echo $days_labels[$nowts['wday']]; ?></span>
							<span class="vbo-roverw-tablemday"><?php echo $nowts['mday']; ?></span>
							<span class="vbo-roverw-tablemonth"><?php echo $months_labels[$nowts['mon']-1]; ?></span>
						</td>
						<?php
						$next = $nowts['mday'] + 1;
						$dayts = mktime(0, 0, 0, $nowts['mon'], $next, $nowts['year']);
						$nowts = getdate($dayts);
						$cell_count++;
					}
					?>
					</tr>
				<?php
				$closed_roomrateplans = VikBooking::getRoomRplansClosingDates($roomrows['id']);
				foreach ($roomrates as $roomrate) {
					$nowts = getdate($tsstart);
					$cell_count = 0;
					?>
					<tr class="vbo-roverviewtablerow" id="vbo-roverw-<?php echo $roomrate['id']; ?>">
						<td data-defrate="<?php echo $roomrate['cost']; ?>"><span class="vbo-rplan-name"><?php echo $roomrate['name']; ?></span></td>
					<?php
					while( $cell_count < $MAX_DAYS ) {
						$style = '';
						if ( $cell_count >= $MAX_TO_DISPLAY ) {
							$style = ' style="display: none;"';
						}
						$dclass = "vbo-roverw-rplan-on";
						if (count($closed_roomrateplans) > 0 && array_key_exists($roomrate['idprice'], $closed_roomrateplans) && in_array(date('Y-m-d', $nowts[0]), $closed_roomrateplans[$roomrate['idprice']])) {
							$dclass = "vbo-roverw-rplan-off";
						}
						$id_block = "cell-".$nowts['mday'].'-'.$nowts['mon']."-".$nowts['year']."-".$roomrate['idprice'];
						$dclass .= ' day-block';

						$today_tsin = mktime($pcheckinh, $pcheckinm, 0, $nowts['mon'], $nowts['mday'], $nowts['year']);
						$today_tsout = mktime($pcheckouth, $pcheckoutm, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']);

						$tars = VikBooking::applySeasonsRoom(array($roomrate), $today_tsin, $today_tsout);

						?>
						<td align="center" class="<?php echo $dclass.' cell-'.$nowts['mday'].'-'.$nowts['mon']; ?>" id="<?php echo $id_block; ?>" data-vboprice="<?php echo $tars[0]['cost']; ?>" data-vbodate="<?php echo date('Y-m-d', $nowts[0]); ?>" data-vbodateread="<?php echo $months_labels[$nowts['mon']-1].' '.$nowts['mday'].' '.$days_labels[$nowts['wday']]; ?>" data-vbospids="<?php echo (array_key_exists('spids', $tars[0]) && count($tars[0]['spids']) > 0 ? implode('-', $tars[0]['spids']) : ''); ?>"<?php echo $style; ?>>
							<span class="vbo-rplan-currency"><?php echo $currencysymb; ?></span>
							<span class="vbo-rplan-price"><?php echo $tars[0]['cost']; ?></span>
						</td>
						<?php

						$next = $nowts['mday'] + 1;
						$dayts = mktime(0, 0, 0, $nowts['mon'], $next, $nowts['year']);
						$nowts = getdate($dayts);
						
						$cell_count++;
					}
					?>
					</tr>
					<?php
				}
				?>
					<tr class="vbo-roverviewtableavrow">
						<td><span class="vbo-roverview-roomunits"><?php echo $roomrows['units']; ?></span><span class="vbo-roverview-uleftlbl"><?php echo JText::_('VBPCHOOSEBUSYCAVAIL'); ?></span></td>
					<?php
					$nowts = getdate($tsstart);
					$cell_count = 0;
					while( $cell_count < $MAX_DAYS ) {
						$style = '';
						if ( $cell_count >= $MAX_TO_DISPLAY ) {
							$style = ' style="display: none;"';
						}
						$dclass = "vbo-roverw-daynotbusy";
						$id_block = "cell-".$nowts['mday'].'-'.$nowts['mon']."-".$nowts['year']."-avail";

						$totfound = 0;
						$last_bid = 0;
						if (array_key_exists($roomrows['id'], $booked_dates) && is_array($booked_dates[$roomrows['id']])) {
							foreach($booked_dates[$roomrows['id']] as $b) {
								$tmpone = getdate($b['checkin']);
								$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
								$ritts = strtotime($rit);
								$tmptwo = getdate($b['checkout']);
								$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
								$conts = strtotime($con);
								if ($nowts[0]>=$ritts && $nowts[0]<$conts) {
									$dclass = "vbo-roverw-daybusy";
									$last_bid = $b['idorder'];
									$totfound++;
								}
							}
						}
						$units_remaining = $roomrows['units'] - $totfound;
						if ($units_remaining > 0 && $units_remaining < $roomrows['units'] && $roomrows['units'] > 1) {
							$dclass .= " vbo-roverw-daybusypartially";
						} elseif ($units_remaining <= 0 && $roomrows['units'] <= 1 && !empty($last_bid)) {
							//Booking color tag
							$btag_style = '';
							$binfo = VikBooking::getBookingInfoFromID($last_bid);
							if (count($binfo) > 0) {
								$bcolortag = VikBooking::applyBookingColorTag($binfo);
								if (count($bcolortag) > 0) {
									$bcolortag['name'] = JText::_($bcolortag['name']);
									$btag_style = "background-color: ".$bcolortag['color']."; color: ".(array_key_exists('fontcolor', $bcolortag) ? $bcolortag['fontcolor'] : '#ffffff').";";
									$dclass .= ' vbo-roverw-hascolortag';
								}
							}
							if (!empty($btag_style)) {
								$style = !empty($style) ? ' style="display: none; '.$btag_style.'"' : ' style="'.$btag_style.'"';
								$style .= ' title="'.addslashes($bcolortag['name']).'"';
							}
						}

						?>
						<td align="center" class="<?php echo $dclass.' cell-'.$nowts['mday'].'-'.$nowts['mon']; ?>" id="<?php echo $id_block; ?>" data-vbodateread="<?php echo $months_labels[$nowts['mon']-1].' '.$nowts['mday'].' '.$days_labels[$nowts['wday']]; ?>"<?php echo $style; ?>>
							<span class="vbo-roverw-curunits"><?php echo $units_remaining; ?></span>
						</td>
						<?php

						$next = $nowts['mday'] + 1;
						$dayts = mktime(0, 0, 0, ($nowts['mon'] < 10 ? "0".$nowts['mon'] : $nowts['mon']), ($next < 10 ? "0".$next : $next), $nowts['year']);
						$nowts = getdate($dayts);
						
						$cell_count++;
					}
					?>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="vbo-ratesoverview-period-container">
			<div class="vbo-ratesoverview-period-inner">
				<div class="vbo-ratesoverview-period-lbl">
					<span><?php echo JText::_('VBOROVWSELPERIOD'); ?></span>
				</div>
				<div class="vbo-ratesoverview-period-boxes">
					<div class="vbo-ratesoverview-period-boxes-inner">
						<div class="vbo-ratesoverview-period-box-left">
							<div class="vbo-ratesoverview-period-box-lbl">
								<span><?php echo JText::_('VBOROVWSELPERIODFROM'); ?></span>
							</div>
							<div class="vbo-ratesoverview-period-box-val">
								<div id="vbo-ratesoverview-period-from">
									<span class="vbo-ratesoverview-period-wday"></span>
									<span class="vbo-ratesoverview-period-mday"></span>
									<span class="vbo-ratesoverview-period-month"></span>
								</div>
								<span id="vbo-ratesoverview-period-from-icon"><i class="fa fa-calendar"></i></span>
							</div>
						</div>
						<div class="vbo-ratesoverview-period-box-right">
							<div class="vbo-ratesoverview-period-box-lbl">
								<span><?php echo JText::_('VBOROVWSELPERIODTO'); ?></span>
							</div>
							<div class="vbo-ratesoverview-period-box-val">
								<div id="vbo-ratesoverview-period-to">
									<span class="vbo-ratesoverview-period-wday"></span>
									<span class="vbo-ratesoverview-period-mday"></span>
									<span class="vbo-ratesoverview-period-month"></span>
								</div>
								<span id="vbo-ratesoverview-period-to-icon"><i class="fa fa-calendar"></i></span>
							</div>
						</div>
					</div>
					<div class="vbo-ratesoverview-period-box-cals" style="display: none;">
						<div class="vbo-ratesoverview-period-box-cals-inner">
							<div class="vbo-ratesoverview-period-cal-left">
								<h4><?php echo JText::_('VBOROVWSELPERIODFROM'); ?></h4>
								<div id="vbo-period-from"></div>
								<input type="hidden" id="vbo-period-from-val" value="" />
							</div>
							<div class="vbo-ratesoverview-period-cal-right">
								<h4><?php echo JText::_('VBOROVWSELPERIODTO'); ?></h4>
								<div id="vbo-period-to"></div>
								<input type="hidden" id="vbo-period-to-val" value="" />
							</div>
							<div class="vbo-ratesoverview-period-cal-cmd">
								<h4><?php echo JText::_('VBOROVWSELRPLAN'); ?></h4>
								<div class="vbo-ratesoverview-period-cal-cmd-inner">
									<select id="vbo-selperiod-rplanid" onchange="vboUpdateRplan();">
									<?php
									foreach ($roomrates as $krr => $roomrate) {
										?>
										<option value="<?php echo $roomrate['idprice']; ?>" data-defrate="<?php echo $roomrate['cost']; ?>"<?php echo $krr < 1 ? ' selected="selected"' : ''; ?>><?php echo $roomrate['name']; ?></option>
										<?php
									}
									?>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="vbo-ratesoverview-lostab-cont"<?php echo (!empty($cookie_tab) && $cookie_tab == 'cal' ? ' style="display: none;"' : ''); ?>>
	<?php
	if (count($seasons_cal) > 0) {
		//Special Prices Timeline
		if (isset($seasons_cal['seasons']) && count($seasons_cal['seasons'])) {
			?>
	<div class="vbo-timeline-container">
		<ul id="vbo-timeline">
			<?php
			foreach ($seasons_cal['seasons'] as $ks => $timeseason) {
				$s_val_diff = '';
				if ($timeseason['val_pcent'] == 2) {
					//percentage
					$s_val_diff = (($timeseason['diffcost'] - abs($timeseason['diffcost'])) > 0.00 ? VikBooking::numberFormat($timeseason['diffcost']) : intval($timeseason['diffcost']))." %";
				} else {
					//absolute
					$s_val_diff = $currencysymb.''.VikBooking::numberFormat($timeseason['diffcost']);
				}
				$s_explanation = array();
				if (empty($timeseason['year'])) {
					$s_explanation[] = JText::_('VBSEASONANYYEARS');
				}
				if (!empty($timeseason['losoverride'])) {
					$s_explanation[] = JText::_('VBSEASONBASEDLOS');
				}
				?>
			<li data-fromts="<?php echo $timeseason['from_ts']; ?>" data-tots="<?php echo $timeseason['to_ts']; ?>">
				<input type="radio" name="timeline" class="vbo-timeline-radio" id="vbo-timeline-dot<?php echo $ks; ?>" <?php echo $ks === 0 ? 'checked="checked"' : ''; ?>/>
				<div class="vbo-timeline-relative">
					<label class="vbo-timeline-label" for="vbo-timeline-dot<?php echo $ks; ?>"><?php echo $timeseason['spname']; ?></label>
					<span class="vbo-timeline-date"><?php echo VikBooking::formatSeasonDates($timeseason['from_ts'], $timeseason['to_ts']); ?></span>
					<span class="vbo-timeline-circle" onclick="Javascript: jQuery('#vbo-timeline-dot<?php echo $ks; ?>').trigger('click');"></span>
				</div>
				<div class="vbo-timeline-content">
					<p>
						<span class="vbo-seasons-calendar-slabel vbo-seasons-calendar-season-<?php echo $timeseason['type'] == 2 ? 'discount' : 'charge'; ?>"><?php echo $timeseason['type'] == 2 ? '-' : '+'; ?> <?php echo $s_val_diff; ?> <?php echo JText::_('VBSEASONPERNIGHT'); ?></span>
						<br/>
						<?php
						if (count($s_explanation) > 0) {
							echo implode(' - ', $s_explanation);
						}
						?>
					</p>
				</div>
			</li>
				<?php
			}
			?>
		</ul>
	</div>
	<script>
	jQuery(document).ready(function(){
		jQuery('.vbo-timeline-container').css('min-height', (jQuery('.vbo-timeline-container').outerHeight() + 20));
	});
	</script>
			<?php
		}
		//
		//Begin Seasons Calendar
		?>
	<table class="vbo-seasons-calendar-table">
		<tr class="vbo-seasons-calendar-nightsrow">
			<td>&nbsp;</td>
		<?php
		foreach ($seasons_cal['offseason'] as $numnights => $ntars) {
			?>
			<td><span><?php echo JText::sprintf(($numnights > 1 ? 'VBOSEASONCALNUMNIGHTS' : 'VBOSEASONCALNUMNIGHT'), $numnights); ?></span></td>
			<?php
		}
		?>
		</tr>
		<tr class="vbo-seasons-calendar-offseasonrow">
			<td>
				<span class="vbo-seasons-calendar-offseasonname"><?php echo JText::_('VBOSEASONSCALOFFSEASONPRICES'); ?></span>
			</td>
		<?php
		foreach ($seasons_cal['offseason'] as $numnights => $tars) {
			?>
			<td>
				<div class="vbo-seasons-calendar-offseasoncosts">
					<?php
					foreach ($tars as $tar) {
						?>
					<div class="vbo-seasons-calendar-offseasoncost">
						<?php
						if ($price_types_show) {
						?>
						<span class="vbo-seasons-calendar-pricename"><?php echo $tar['name']; ?></span>
						<?php
						}
						?>
						<span class="vbo-seasons-calendar-pricecost">
							<span class="vbo_currency"><?php echo $currencysymb; ?></span><span class="vbo_price"><?php echo VikBooking::numberFormat($tar['cost']); ?></span>
						</span>
					</div>
						<?php
						if (!$price_types_show) {
							break;
						}
					}
					?>
				</div>
			</td>
			<?php
		}
		?>
		</tr>
		<?php
		if (!isset($seasons_cal['seasons'])) {
			$seasons_cal['seasons'] = array();
		}
		foreach ($seasons_cal['seasons'] as $s_id => $s) {
			$restr_diff_nights = array();
			if ($los_show && array_key_exists($s_id, $seasons_cal['restrictions'])) {
				$restr_diff_nights = VikBooking::compareSeasonRestrictionsNights($seasons_cal['restrictions'][$s_id]);
			}
			$s_val_diff = '';
			if ($s['val_pcent'] == 2) {
				//percentage
				$s_val_diff = (($s['diffcost'] - abs($s['diffcost'])) > 0.00 ? VikBooking::numberFormat($s['diffcost']) : intval($s['diffcost']))." %";
			} else {
				//absolute
				$s_val_diff = $currencysymb.''.VikBooking::numberFormat($s['diffcost']);
			}
			?>
		<tr class="vbo-seasons-calendar-seasonrow">
			<td>
				<div class="vbo-seasons-calendar-seasondates">
					<span class="vbo-seasons-calendar-seasonfrom"><?php echo date(str_replace("/", $datesep, $df), $s['from_ts']); ?></span>
					<span class="vbo-seasons-calendar-seasondates-separe">-</span>
					<span class="vbo-seasons-calendar-seasonto"><?php echo date(str_replace("/", $datesep, $df), $s['to_ts']); ?></span>
				</div>
				<div class="vbo-seasons-calendar-seasonchargedisc">
					<span class="vbo-seasons-calendar-slabel vbo-seasons-calendar-season-<?php echo $s['type'] == 2 ? 'discount' : 'charge'; ?>"><span class="vbo-seasons-calendar-operator"><?php echo $s['type'] == 2 ? '-' : '+'; ?></span><?php echo $s_val_diff; ?></span>
				</div>
				<span class="vbo-seasons-calendar-seasonname"><a href="index.php?option=com_vikbooking&amp;task=editseason&amp;cid[]=<?php echo $s['id']; ?>" target="_blank"><?php echo $s['spname']; ?></a></span>
			<?php
			if ($los_show && array_key_exists($s_id, $seasons_cal['restrictions']) && count($restr_diff_nights) == 0) {
				//Season Restrictions
				$season_restrictions = array();
				foreach ($seasons_cal['restrictions'][$s_id] as $restr) {
					$season_restrictions = $restr;
					break;
				}
				?>
				<div class="vbo-seasons-calendar-restrictions">
				<?php
				if ($season_restrictions['minlos'] > 1) {
					?>
					<span class="vbo-seasons-calendar-restriction-minlos"><?php echo JText::_('VBORESTRMINLOS'); ?><span class="vbo-seasons-calendar-restriction-minlos-badge"><?php echo $season_restrictions['minlos']; ?></span></span>
					<?php
				}
				if (array_key_exists('maxlos', $season_restrictions) && $season_restrictions['maxlos'] > 1) {
					?>
					<span class="vbo-seasons-calendar-restriction-maxlos"><?php echo JText::_('VBORESTRMAXLOS'); ?><span class="vbo-seasons-calendar-restriction-maxlos-badge"><?php echo $season_restrictions['maxlos']; ?></span></span>
					<?php
				}
				if (array_key_exists('wdays', $season_restrictions) && count($season_restrictions['wdays']) > 0) {
					?>
					<div class="vbo-seasons-calendar-restriction-wdays">
						<label><?php echo JText::_((count($season_restrictions['wdays']) > 1 ? 'VBORESTRARRIVWDAYS' : 'VBORESTRARRIVWDAY')); ?></label>
					<?php
					foreach ($season_restrictions['wdays'] as $wday) {
						?>
						<span class="vbo-seasons-calendar-restriction-wday"><?php echo VikBooking::sayWeekDay($wday); ?></span>
						<?php
					}
					?>
					</div>
					<?php
				} elseif ((array_key_exists('cta', $season_restrictions) && count($season_restrictions['cta']) > 0) || (array_key_exists('ctd', $season_restrictions) && count($season_restrictions['ctd']) > 0)) {
					if (array_key_exists('cta', $season_restrictions) && count($season_restrictions['cta']) > 0) {
						?>
					<div class="vbo-seasons-calendar-restriction-wdays vbo-seasons-calendar-restriction-cta">
						<label><?php echo JText::_('VBORESTRWDAYSCTA'); ?></label>
						<?php
						foreach ($season_restrictions['cta'] as $wday) {
							?>
						<span class="vbo-seasons-calendar-restriction-wday"><?php echo VikBooking::sayWeekDay(str_replace('-', '', $wday)); ?></span>
							<?php
						}
						?>
					</div>
						<?php
					}
					if (array_key_exists('ctd', $season_restrictions) && count($season_restrictions['ctd']) > 0) {
						?>
					<div class="vbo-seasons-calendar-restriction-wdays vbo-seasons-calendar-restriction-ctd">
						<label><?php echo JText::_('VBORESTRWDAYSCTD'); ?></label>
						<?php
						foreach ($season_restrictions['ctd'] as $wday) {
							?>
						<span class="vbo-seasons-calendar-restriction-wday"><?php echo VikBooking::sayWeekDay(str_replace('-', '', $wday)); ?></span>
							<?php
						}
						?>
					</div>
						<?php
					}
				}
				?>
				</div>
				<?php
			}
			?>
			</td>
			<?php
			if (array_key_exists($s_id, $seasons_cal['season_prices']) && count($seasons_cal['season_prices'][$s_id]) > 0) {
				foreach ($seasons_cal['season_prices'][$s_id] as $numnights => $tars) {
					$show_day_cost = true;
					if ($los_show && array_key_exists($s_id, $seasons_cal['restrictions']) && array_key_exists($numnights, $seasons_cal['restrictions'][$s_id])) {
						if ($seasons_cal['restrictions'][$s_id][$numnights]['allowed'] === false) {
							$show_day_cost = false;
						}
					}
					?>
			<td>
				<?php
				if ($show_day_cost) {
				?>
				<div class="vbo-seasons-calendar-seasoncosts">
					<?php
					foreach ($tars as $tar) {
						//print the types of price that are not being modified by this special price with opacity
						$not_affected = (!array_key_exists('origdailycost', $tar));
						//
						?>
					<div class="vbo-seasons-calendar-seasoncost<?php echo ($not_affected ? ' vbo-seasons-calendar-seasoncost-notaffected' : ''); ?>">
						<?php
						if ($price_types_show) {
						?>
						<span class="vbo-seasons-calendar-pricename"><?php echo $tar['name']; ?></span>
						<?php
						}
						?>
						<span class="vbo-seasons-calendar-pricecost">
							<span class="vbo_currency"><?php echo $currencysymb; ?></span><span class="vbo_price"><?php echo VikBooking::numberFormat($tar['cost']); ?></span>
						</span>
					</div>
						<?php
						if (!$price_types_show) {
							break;
						}
					}
					?>
				</div>
				<?php
				} else {
					?>
					<div class="vbo-seasons-calendar-seasoncosts-disabled"></div>
					<?php
				}
				?>
			</td>
					<?php
				}
			}
			?>
		</tr>
			<?php
		}
		?>
	</table>
		<?php
		//End Seasons Calendar
	} else {
		?>
	<p class="vbo-warning"><?php echo JText::_('VBOWARNNORATESROOM'); ?></p>
		<?php
	}
	?>
</div>
	<?php
	$vcm_enabled = VikBooking::vcmAutoUpdate();
	?>
<div class="vbo-info-overlay-block">
	<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-info-overlay-content vbo-info-overlay-content-rovervw">
		<div class="vbo-roverw-infoblock">
			<span id="rovervw-roomname"></span>
			<span id="rovervw-rplan"></span>
			<span id="rovervw-fromdate"></span> - <span id="rovervw-todate"></span>
		</div>
		<div class="vbo-roverw-alldays">
			<div class="vbo-roverw-alldays-inner"></div>
		</div>
		<div class="vbo-roverw-setnewrate">
			<h4><i class="vboicn-calculator"></i><?php echo JText::_('VBRATESOVWSETNEWRATE'); ?></h4>
			<div class="vbo-roverw-setnewrate-inner">
				<span class="vbo-roverw-setnewrate-currency"><?php echo $currencysymb; ?></span> <input type="number" step="any" min="0" id="roverw-newrate" value="" placeholder="" size="7" />
				<div class="vbo-roverw-setnewrate-vcm">
					<div class="vbo-roverw-setnewrate-vcm-head">
						<span class="<?php echo $vcm_enabled < 0 ? 'vbo-vcm-notinstalled' : 'vbo-vcm-installed'; ?>">
							<?php echo $vbo_app->createPopover(array('title' => JText::_('VBOUPDRATESONCHANNELS'), 'content' => ($vcm_enabled < 0 ? JText::_('VBCONFIGVCMAUTOUPDMISS') : JText::_('VBOUPDRATESONCHANNELSHELP')), 'icon_class' => 'fa fa-globe')); ?>
							<?php echo JText::_('VBOUPDRATESONCHANNELS'); ?>
						</span>
					</div>
					<div class="vbo-roverw-setnewrate-vcm-body">
						<label class="vbo-switch">
							<input type="checkbox" id="roverw-newrate-vcm" value="1" <?php echo $vcm_enabled < 0 ? 'disabled="disabled"' : ($vcm_enabled > 0 ? 'checked="checked"' : ''); ?>/>
							<span class="vbo-slider<?php echo $vcm_enabled < 0 ? ' vbo-slider-disabled' : ''; ?> vbo-round"></span>
						</label>
					</div>
				</div>
				<div class="vbo-roverw-setnewrate-btns">
					<button type="button" class="btn btn-danger" onclick="hideVboDialog();"><?php echo JText::_('VBANNULLA'); ?></button>
					<button type="button" class="btn btn-success" onclick="setNewRates();"><i class="vboicn-checkmark"></i><?php echo JText::_('VBAPPLY'); ?></button>
				</div>
			</div>
		</div>
		<div class="vbo-roverw-closeopenrp">
			<h4><i class="vboicn-switch"></i><?php echo JText::_('VBRATESOVWCLOSEOPENRRP'); ?> <span id="rovervw-closeopen-rplan"></span></h4>
			<div class="vbo-roverw-closeopenrp-btns">
				<button type="button" class="btn btn-danger" onclick="modRoomRatePlan('close');"><i class="vboicn-exit"></i><?php echo JText::_('VBRATESOVWCLOSERRP'); ?></button>
				<button type="button" class="btn btn-success" onclick="modRoomRatePlan('open');"><i class="vboicn-enter"></i><?php echo JText::_('VBRATESOVWOPENRRP'); ?></button>
				<br clear="all" /><br />
				<button type="button" class="btn btn-danger" onclick="hideVboDialog();"><?php echo JText::_('VBANNULLA'); ?></button>
			</div>
		</div>
	</div>
	<div class="vbo-info-overlay-loading">
		<div><?php echo JText::_('VIKLOADING'); ?></div>
	</div>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
</form>
<script type="text/Javascript">
function vboFormatCalDate(idc) {
	var vb_period = document.getElementById('vbo-'+idc+'-val').value;
	if (!vb_period || !vb_period.length) {
		return false;
	}
	var vb_period_parts = vb_period.split("/");
	if ('%d/%m/%Y' == '<?php echo $vbo_df; ?>') {
		var period_date = new Date(vb_period_parts[2], (parseInt(vb_period_parts[1]) - 1), parseInt(vb_period_parts[0], 10), 0, 0, 0, 0);
		var data = [parseInt(vb_period_parts[0], 10), parseInt(vb_period_parts[1]), vb_period_parts[2]];
	} else if ('%m/%d/%Y' == '<?php echo $vbo_df; ?>') {
		var period_date = new Date(vb_period_parts[2], (parseInt(vb_period_parts[0]) - 1), parseInt(vb_period_parts[1], 10), 0, 0, 0, 0);
		var data = [parseInt(vb_period_parts[1], 10), parseInt(vb_period_parts[0]), vb_period_parts[2]];
	} else {
		var period_date = new Date(vb_period_parts[0], (parseInt(vb_period_parts[1]) - 1), parseInt(vb_period_parts[2], 10), 0, 0, 0, 0);
		var data = [parseInt(vb_period_parts[2], 10), parseInt(vb_period_parts[1]), vb_period_parts[0]];
	}
	var elcont = jQuery('#vbo-ratesoverview-'+idc);
	elcont.find('.vbo-ratesoverview-period-wday').text(vboMapWdays[period_date.getDay()]);
	elcont.find('.vbo-ratesoverview-period-mday').text(period_date.getDate());
	elcont.find('.vbo-ratesoverview-period-month').text(vboMapMons[period_date.getMonth()]);
	jQuery('#vbo-ratesoverview-'+idc+'-icon').hide();
	data.push(jQuery('#vbo-selperiod-rplanid').val());
	data.push(jQuery('#vbo-selperiod-rplanid option:selected').text());
	data.push(jQuery('#vbo-selperiod-rplanid option:selected').attr('data-defrate'));
	var struct = getPeriodStructure(data);
	if (idc.indexOf('from') >= 0) {
		//period from date selected
		if (!vbolistener.pickFirst(struct)) {
			//first already picked: update it
			vbolistener.first = struct;
		}
	}
	if (idc.indexOf('to') >= 0) {
		//period to date selected
		if (!vbolistener.pickFirst(struct)) {
			//first already picked
			if ((vbolistener.first.isBeforeThan(struct) || vbolistener.first.isSameDay(struct)) && vbolistener.first.isSameRplan(struct)) {
				//last > first: pick last
				if (vbolistener.pickLast(struct)) {
					showVboDialogPeriod();
				}
			}
		}
	}
}
jQuery(document).ready(function() {
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '' ] );
	jQuery('#vbodatepicker').datepicker({
		showOn: 'focus',
		dateFormat: '<?php echo $juidf; ?>',
		minDate: '0d',
		numberOfMonths: 2,
		onSelect: function( selectedDate ) {
			document.vbratesoverview.submit();
		}
	});
	jQuery('#vbo-period-from').datepicker({
		dateFormat: '<?php echo $juidf; ?>',
		minDate: '0d',
		altField: '#vbo-period-from-val',
		onSelect: function( selectedDate ) {
			jQuery('#vbo-period-to').datepicker("option", "minDate", selectedDate);
			vboFormatCalDate('period-from');
		}
	});
	jQuery('#vbo-period-to').datepicker({
		dateFormat: '<?php echo $juidf; ?>',
		minDate: '0d',
		altField: '#vbo-period-to-val',
		onSelect: function( selectedDate ) {
			jQuery('#vbo-period-from').datepicker("option", "maxDate", selectedDate);
			vboFormatCalDate('period-to');
		}
	});
	jQuery('.vbo-ratesoverview-period-box-left, .vbo-ratesoverview-period-box-right').click(function() {
		jQuery('.vbo-ratesoverview-period-box-cals').fadeToggle();
	});
});
var _START_DAY_ = '<?php echo $start_day_id; ?>';
var _END_DAY_ = '<?php echo $end_day_id; ?>';
<?php
if ($df == "Y/m/d") {
	?>
Date.prototype.format = "yy/mm/dd";
	<?php
} elseif ($df == "m/d/Y") {
	?>
Date.prototype.format = "mm/dd/yy";
	<?php
} else {
	?>
Date.prototype.format = "dd/mm/yy";
	<?php
}
?>
Date.prototype.datesep = "<?php echo addslashes(VikBooking::getDateSeparator()); ?>";
var droprooms = document.getElementById("roomsel");
var roomid = droprooms.value;
var roomname = droprooms.options[droprooms.selectedIndex].text;
var currencysymb = '<?php echo $currencysymb; ?>';
var debug_mode = '<?php echo $pdebug; ?>';
var vcm_exists = <?php echo VikBooking::vcmAutoUpdate(); ?>;
var roverw_messages = {
	"setNewRatesMissing": "<?php echo addslashes(JText::_('VBRATESOVWERRNEWRATE')); ?>",
	"modRplansMissing": "<?php echo addslashes(JText::_('VBRATESOVWERRMODRPLANS')); ?>",
	"openSpLink": "<?php echo addslashes(JText::_('VBRATESOVWOPENSPL')); ?>",
	"vcmRatesChanged": "<?php echo addslashes(JText::_('VBRATESOVWVCMRCHANGED')); ?>",
	"vcmRatesChangedOpen": "<?php echo addslashes(JText::_('VBRATESOVWVCMRCHANGEDOPEN')); ?>"
};
</script>
<script type="text/Javascript">
/* Dates navigation - Start */
function prevDay() {
	if ( canPrev(_START_DAY_) ) {
		jQuery('.'+_START_DAY_).prev().show();
		jQuery('.'+_END_DAY_).hide();
		
		if ( canPrev(_START_DAY_) ) {
			var start = jQuery('.'+_START_DAY_).first();
			var end = jQuery('.'+_END_DAY_).first();
			
			_START_DAY_ = start.prev().prop('class').split(' ')[1];
			_END_DAY_ = end.prev().prop('class').split(' ')[1];
			
			return true;
		} 
	}
	
	return false;
}

function nextDay() {
	if ( canNext(_END_DAY_) ) {
		jQuery('.'+_START_DAY_).hide();
		jQuery('.'+_END_DAY_).next().show();
		
		if ( canNext(_END_DAY_) ) {
			var start = jQuery('.'+_START_DAY_).first();
			var end = jQuery('.'+_END_DAY_).first();
			
			_START_DAY_ = start.next().prop('class').split(' ')[1];
			_END_DAY_ = end.next().prop('class').split(' ')[1];
			
			return true;
		} 
	}
	
	return false;
}

function prevWeek() {
	var i = 0;
	while( i++ < 7 && prevDay() );
}

function nextWeek() {
	var i = 0;
	while( i++ < 7 && nextDay() );
}

function canPrev(start) {
	return ( jQuery('.'+start).first().prev().prop('class').split(' ').length > 1 );
}

function canNext(end) {
	return ( jQuery('.'+end).first().next().length > 0 );
}
/* Dates navigation - End */
/* Dates selection - Start */
var vbolistener = null;
var vbodialog_on = false;
jQuery(document).ready(function() {
	vbolistener = new CalendarListener();
	jQuery('.day-block').click(function() {
		pickBlock( jQuery(this).attr('id') );
	});
	jQuery('.day-block').hover(
		function() {
			if ( vbolistener.isFirstPicked() && !vbolistener.isLastPicked() ) {
				var struct = initBlockStructure(jQuery(this).attr('id'));
				var all_blocks = getAllBlocksBetween(vbolistener.first, struct, false);
				if ( all_blocks !== false ) {
					jQuery.each(all_blocks, function(k, v){
						if ( !v.hasClass('block-picked-middle') ) {
							v.addClass('block-picked-middle');
						}
					});
					jQuery(this).addClass('block-picked-end');
				}
			}
		},
		function() {
			if ( !vbolistener.isLastPicked() ) {
				jQuery('.day-block').removeClass('block-picked-middle block-picked-end');
			}
		}
	);
	jQuery(document).keydown(function(e) {
		if ( e.keyCode == 27 ) {
			hideVboDialog();
		}
	});
	jQuery(document).mouseup(function(e) {
		if (!vbodialog_on) {
			return false;
		}
		var vbo_overlay_cont = jQuery(".vbo-info-overlay-content");
		if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
			hideVboDialog();
		}
	});
	jQuery("body").on("click", ".vbo-roverw-daymod-infospids", function() {
		var helem = jQuery(this).next('.vbo-roverw-daymod-infospids-outcont');
		if (helem.length && helem.is(":visible")) {
			jQuery(this).removeClass("vbo-roverw-daymod-infospids-on");
			helem.hide();
		} else {
			jQuery(".vbo-roverw-daymod-infospids-on").removeClass("vbo-roverw-daymod-infospids-on");
			jQuery(".vbo-roverw-daymod-infospids-outcont").hide();
			jQuery(this).addClass("vbo-roverw-daymod-infospids-on");
			helem.show();
		}
	});
	jQuery('.vbo-roverw-closeopenrp h4').click(function() {
		jQuery('.vbo-roverw-closeopenrp-btns').fadeToggle();
	});
});

function checkInvokeVcm() {
	if (!vbolistener || !vbolistener.first || !vbolistener.first.rplan) {
		return;
	}
	var rplanid = vbolistener.first.rplan;
	var curval = document.getElementById('roverw-newrate-vcm').value;
	if (parseInt(curval) < 0) {
		return;
	}
	var buiscuits = document.cookie;
	if (!buiscuits.length) {
		return;
	}
	var vcmmatch = "vboVcmRov"+roomid+rplanid+"=";
	if (buiscuits.indexOf(vcmmatch) >= 0) {
		// last cookie does not terminate with ; so just use 0 to compare
		vcmmatch += "0";
		if (buiscuits.indexOf(vcmmatch) >= 0) {
			jQuery('#roverw-newrate-vcm').prop('checked', false);
		} else {
			jQuery('#roverw-newrate-vcm').prop('checked', true);
		}
	}
}

function showVboDialog() {
	var format = new Date().format;
	format = format.replace(new RegExp("/", 'g'), new Date().datesep);
	jQuery("#rovervw-roomname").html(roomname);
	jQuery("#rovervw-rplan").html(vbolistener.first.rplanName);
	jQuery("#rovervw-closeopen-rplan").html('"'+vbolistener.first.rplanName+'"');
	jQuery("#rovervw-fromdate").html(vbolistener.first.toDate(format));
	jQuery("#rovervw-todate").html(vbolistener.last.toDate(format));
	jQuery(".vbo-roverw-alldays-inner").html("");
	var all_blocks = getAllBlocksBetween(vbolistener.first, vbolistener.last, true);
	if ( all_blocks !== false ) {
		var newdayscont = '';
		jQuery.each(all_blocks, function(k, v) {
			var spids = jQuery(v).attr("data-vbospids").split("-");
			var spids_det = '';
			if (jQuery(v).attr("data-vbospids").length > 0 && spids.length > 0) {
				spids_det += "<div class=\"vbo-roverw-daymod-infospids\"><span><i class=\"fa fa-info\"></i></span></div>";
				spids_det += "<div class=\"vbo-roverw-daymod-infospids-outcont\">";
				spids_det += "<div class=\"vbo-roverw-daymod-infospids-incont\"><ul>";
				for(var x = 0; x < spids.length; x++) {
					spids_det += "<li><a target=\"_blank\" href=\"index.php?option=com_vikbooking&task=editseason&cid[]="+spids[x]+"\">"+roverw_messages.openSpLink.replace("%d", spids[x])+"</a></li>";
				}
				spids_det += "</ul></div></div>";
			}
			newdayscont += "<div class=\"vbo-roverw-daymod\"><div class=\"vbo-roverw-daymod-inner\"><div class=\"vbo-roverw-daymod-innercell\"><span class=\"vbo-roverw-daydate\">"+jQuery(v).attr("data-vbodateread")+"</span><span class=\"vbo-roverw-dayprice\">"+v.html()+"</span>"+spids_det+"</div></div></div>";
		});
		jQuery(".vbo-roverw-alldays-inner").html(newdayscont);
		//jQuery("#roverw-newrate").attr("placeholder", vbolistener.first.defRate);
		jQuery("#roverw-newrate").val(vbolistener.first.defRate);
	}
	checkInvokeVcm();

	jQuery(".vbo-info-overlay-block").fadeIn();
	vbodialog_on = true;
}

function showVboDialogPeriod() {
	var format = new Date().format;
	format = format.replace(new RegExp("/", 'g'), new Date().datesep);
	jQuery('.vbo-ratesoverview-period-box-cals').fadeOut();
	jQuery("#rovervw-roomname").html(roomname);
	jQuery("#rovervw-rplan").html(vbolistener.first.rplanName);
	jQuery("#rovervw-closeopen-rplan").html('"'+vbolistener.first.rplanName+'"');
	jQuery("#rovervw-fromdate").html(vbolistener.first.toDate(format));
	jQuery("#rovervw-todate").html(vbolistener.last.toDate(format));
	jQuery(".vbo-roverw-alldays-inner").html("");
	checkInvokeVcm();

	jQuery(".vbo-info-overlay-block").fadeIn();
	vbodialog_on = true;
}

function hideVboDialog() {
	vbolistener.clear();
	jQuery('.day-block').removeClass('block-picked-start block-picked-middle block-picked-end');
	if (vbodialog_on === true) {
		jQuery(".vbo-info-overlay-block").fadeOut(400, function () {
			jQuery(".vbo-info-overlay-content").show();
		});
		//reset period selection
		jQuery('#vbo-ratesoverview-period-from').find('span').text('');
		jQuery('#vbo-ratesoverview-period-from-icon').show();
		jQuery('#vbo-ratesoverview-period-to').find('span').text('');
		jQuery('#vbo-ratesoverview-period-to-icon').show();
		//
		vbodialog_on = false;
	}
}

function vboCheckVcmRatesChanges() {
	if (vcm_exists < 1) {
		return false;
	}
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { option: "com_vikbooking", task: "checkvcmrateschanges", tmpl: "component", e4j_debug: debug_mode }
	}).done(function(res) {
		if (res.indexOf('e4j.error') >= 0 ) {
			console.log(res);
			alert(res.replace("e4j.error.", ""));
			jQuery('.vbo-ratesoverview-right-inner').hide();
		} else {
			//display the VCM link for updating the rates on the OTAs
			var obj_res = JSON.parse(res);
			var esitcont = "";
			if (obj_res.changesCount > 0 && obj_res.hasOwnProperty('changesData') && obj_res.changesData.hasOwnProperty('dfrom')) {
				esitcont += "<span class=\"vbo-ratesoverview-vcmwarn-close\"> <i class=\"vboicn-cancel-circle\"></i></span>";
				esitcont += "<span class=\"vbo-ratesoverview-vcmwarn-count\"><i class=\"vboicn-notification\"></i> <span>"+roverw_messages.vcmRatesChanged.replace("%d", obj_res.changesCount)+"</span></span>";
				esitcont += "<span class=\"vbo-ratesoverview-vcmwarn-open\"><a href=\"index.php?option=com_vikchannelmanager&amp;task=ratespush&amp;vbosess=1\" class=\"btn btn-primary\">"+roverw_messages.vcmRatesChangedOpen+"</a></span>";
				jQuery('.vbo-ratesoverview-right-inner').html(esitcont).fadeIn();
			} else {
				jQuery('.vbo-ratesoverview-right-inner').hide().html('');
			}
		}
	}).fail(function() {
		console.log("vboCheckVcmRatesChanges Request Failed");
		jQuery('.vbo-ratesoverview-right-inner').hide();
	});
}

jQuery(document.body).on('click', '.vbo-ratesoverview-vcmwarn-close', function() {
	vcm_exists = 0;
	jQuery('.vbo-ratesoverview-right-inner').hide().html('');
});

/* Delay and launch the check VCM rates modification function, when the page loads */
setTimeout(function() {
	vboCheckVcmRatesChanges();
}, 1000);
/* - */

function renderChannelManagerResult(obj) {
	console.log(obj);
	//compose modal body
	var htmlres = '<div class="vbo-vcm-rates-res-container">';
	if (obj.hasOwnProperty('channels_success')) {
		htmlres += '<div class="vbo-vcm-rates-res-success">';
		for (var ch_id in obj['channels_success']) {
			htmlres += '<div class="vbo-vcm-rates-res-channel">';
			htmlres += '	<div class="vbo-vcm-rates-res-channel-esit">';
			htmlres += '		<i class="fa fa-check"></i>';
			htmlres += '	</div>';
			htmlres += '	<div class="vbo-vcm-rates-res-channel-logo">';
			if (obj['channels_updated'].hasOwnProperty(ch_id) && obj['channels_updated'][ch_id]['logo'].length) {
				htmlres += '<img src="'+obj['channels_updated'][ch_id]['logo']+'" />';
			} else {
				htmlres += '<span>'+obj['channels_success'][ch_id]+'</span>';
			}
			htmlres += '	</div>';
			htmlres += '</div>';
		}
		if (obj.hasOwnProperty('channels_bkdown')) {
			htmlres += '<div class="vbo-vcm-rates-res-bkdown">';
			htmlres += '	<div><pre>'+obj['channels_bkdown']+'</pre></div>';
			htmlres += '</div>';
		}
		htmlres += '</div>';
	}
	if (obj.hasOwnProperty('channels_warnings')) {
		htmlres += '<div class="vbo-vcm-rates-res-warning">';
		for (var ch_id in obj['channels_warnings']) {
			htmlres += '<div class="vbo-vcm-rates-res-channel">';
			htmlres += '	<div class="vbo-vcm-rates-res-channel-esit">';
			htmlres += '		<i class="fa fa-exclamation-triangle"></i>';
			htmlres += '	</div>';
			htmlres += '	<div class="vbo-vcm-rates-res-channel-logo">';
			if (obj['channels_updated'].hasOwnProperty(ch_id) && obj['channels_updated'][ch_id]['logo'].length) {
				htmlres += '<img src="'+obj['channels_updated'][ch_id]['logo']+'" />';
			} else if (obj['channels_updated'].hasOwnProperty(ch_id)) {
				htmlres += '<span>'+obj['channels_updated'][ch_id]['name']+'</span>';
			}
			htmlres += '	</div>';
			htmlres += '	<div class="vbo-vcm-rates-res-channel-det">';
			htmlres += '		<pre>'+obj['channels_warnings'][ch_id]+'</pre>';
			htmlres += '	</div>';
			htmlres += '</div>';
		}
		htmlres += '</div>';
	}
	if (obj.hasOwnProperty('channels_errors')) {
		htmlres += '<div class="vbo-vcm-rates-res-error">';
		for (var ch_id in obj['channels_errors']) {
			htmlres += '<div class="vbo-vcm-rates-res-channel">';
			htmlres += '	<div class="vbo-vcm-rates-res-channel-esit">';
			htmlres += '		<i class="fa fa-times"></i>';
			htmlres += '	</div>';
			htmlres += '	<div class="vbo-vcm-rates-res-channel-logo">';
			if (obj['channels_updated'].hasOwnProperty(ch_id) && obj['channels_updated'][ch_id]['logo'].length) {
				htmlres += '	<img src="'+obj['channels_updated'][ch_id]['logo']+'" />';
			} else if (obj['channels_updated'].hasOwnProperty(ch_id)) {
				htmlres += '	<span>'+obj['channels_updated'][ch_id]['name']+'</span>';
			}
			htmlres += '	</div>';
			htmlres += '	<div class="vbo-vcm-rates-res-channel-det">';
			htmlres += '		<pre>'+obj['channels_errors'][ch_id]+'</pre>';
			htmlres += '	</div>';
			htmlres += '</div>';
		}
		htmlres += '</div>';
	}
	htmlres += '</div>';
	//update modal body
	if (!jQuery('#jmodal-vbo-vcm-rates-res').find('.modal-body').length) {
		/**
		 * The class modal-body is appended (in WP) by the function vboOpenJModal,
		 * so it may not be available at this point of the code.
		 */
		jQuery('#jmodal-vbo-vcm-rates-res').find('.modal-body-wrapper').html('<div class="modal-body"></div>');
	}
	jQuery('#jmodal-vbo-vcm-rates-res').find('.modal-body').html(htmlres);
	//display modal with the results
	vboOpenJModal('vbo-vcm-rates-res');
}

function setNewRates() {
	var all_blocks = getAllBlocksBetween(vbolistener.first, vbolistener.last, true);
	var toval = jQuery("#roverw-newrate").val();
	var tovalint = parseFloat(toval);
	var invoke_vcm = jQuery("#roverw-newrate-vcm").is(":checked") ? 1 : 0;
	if (all_blocks !== false && toval.length > 0 && !isNaN(tovalint) && tovalint > 0.00) {
		// set cookie to remember the action to invoke VCM for this combination of room-rateplan
		var nd = new Date();
		nd.setTime(nd.getTime() + (365*24*60*60*1000));
		document.cookie = "vboVcmRov"+roomid+vbolistener.first.rplan+"="+invoke_vcm+"; expires=" + nd.toUTCString() + "; path=/";
		//
		jQuery(".vbo-info-overlay-content").hide();
		jQuery(".vbo-info-overlay-loading").prepend('<i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>').fadeIn();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikbooking", task: "setnewrates", tmpl: "component", e4j_debug: debug_mode, id_room: roomid, id_price: vbolistener.first.rplan, rate: toval, vcm: invoke_vcm, fromdate: vbolistener.first.toDate("yy-mm-dd"), todate: vbolistener.last.toDate("yy-mm-dd") }
		}).done(function(res) {
			if (res.indexOf('e4j.error') >= 0) {
				console.log(res);
				alert(res.replace("e4j.error.", ""));
				jQuery(".vbo-info-overlay-content").show();
				jQuery(".vbo-info-overlay-loading").hide().find("i").remove();
			} else {
				//display new rates in all_blocks IDs
				var obj_res = JSON.parse(res);
				jQuery.each(obj_res, function(k, v) {
					if (k == 'vcm') {
						return true;
					}
					var elem = jQuery("#cell-"+k);
					if (elem.length) {
						elem.find(".vbo-rplan-price").html(v.cost);
						var spids = '';
						if (v.hasOwnProperty('spids')) {
							jQuery.each(v.spids, function(spk, spv) {
								spids += spv+'-';
							});
							//right trim dash
							spids = spids.replace(/-+$/, '');
						}
						elem.attr('data-vbospids', spids);
					}
				});
				jQuery(".vbo-info-overlay-loading").hide().find("i").remove();
				hideVboDialog();
				if (obj_res.hasOwnProperty('vcm')) {
					renderChannelManagerResult(obj_res['vcm']);
				} else {
					setTimeout(function() {
						vboCheckVcmRatesChanges();
					}, 500);
				}
			}
		}).fail(function() { 
			alert("Request Failed");
			jQuery(".vbo-info-overlay-content").show();
			jQuery(".vbo-info-overlay-loading").hide().find("i").remove();
		});
	} else {
		alert(roverw_messages.setNewRatesMissing);
		return false;
	}
}

function modRoomRatePlan(mode) {
	var all_blocks = getAllBlocksBetween(vbolistener.first, vbolistener.last, true);
	if ( all_blocks !== false && mode.length > 0 ) {
		jQuery(".vbo-info-overlay-content").hide();
		jQuery(".vbo-info-overlay-loading").prepend('<i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>').fadeIn();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikbooking", task: "modroomrateplans", tmpl: "component", e4j_debug: debug_mode, id_room: roomid, id_price: vbolistener.first.rplan, type: mode, fromdate: vbolistener.first.toDate("yy-mm-dd"), todate: vbolistener.last.toDate("yy-mm-dd") }
		}).done(function(res) {
			if (res.indexOf('e4j.error') >= 0 ) {
				console.log(res);
				alert(res.replace("e4j.error.", ""));
				jQuery(".vbo-info-overlay-content").show();
				jQuery(".vbo-info-overlay-loading").hide().find("i").remove();
			} else {
				//apply new classes in all_blocks IDs
				var obj_res = JSON.parse(res);
				jQuery.each(obj_res, function(k, v) {
					var elem = jQuery("#cell-"+k);
					if (elem.length) {
						elem.removeClass(v.oldcls).addClass(v.newcls);
					}
				});
				jQuery(".vbo-info-overlay-loading").hide().find("i").remove();
				hideVboDialog();
				setTimeout(function() {
					vboCheckVcmRatesChanges();
				}, 500);
			}
		}).fail(function() { 
			alert("Request Failed");
			jQuery(".vbo-info-overlay-content").show();
			jQuery(".vbo-info-overlay-loading").hide().find("i").remove();
		});
	} else {
		alert(roverw_messages.modRplansMissing);
		return false;
	}
}

function vboUpdateRplan() {
	if (vbolistener === null || vbolistener.first === null) {
		return true;
	}
	vbolistener.first.rplan = jQuery('#vbo-selperiod-rplanid').val();
	vbolistener.first.rplanName = jQuery('#vbo-selperiod-rplanid option:selected').text();
	vbolistener.first.defRate = jQuery('#vbo-selperiod-rplanid option:selected').attr('data-defrate');
}

function pickBlock(id) {
	var struct = initBlockStructure(id);
	
	if ( !vbolistener.pickFirst(struct) ) {
		// first already picked
		if ( ( vbolistener.first.isBeforeThan(struct) || vbolistener.first.isSameDay(struct) ) && vbolistener.first.isSameRplan(struct) ) {
			// last > first : pick last
			if ( vbolistener.pickLast(struct) ) {
				var all_blocks = getAllBlocksBetween(vbolistener.first, vbolistener.last, false);
				if ( all_blocks !== false ) {
					jQuery.each(all_blocks, function(k, v){
						if ( !v.hasClass('block-picked-middle') ) {
							v.addClass('block-picked-middle');
						}
					});
					jQuery('#'+vbolistener.last.id).addClass('block-picked-end');
					showVboDialog();
				}
			}
		} else {
			// last < first : clear selection
			vbolistener.clear();
			jQuery('.day-block').removeClass('block-picked-start block-picked-middle block-picked-end');
		}
	} else {
		// first picked
		jQuery('#'+vbolistener.first.id).addClass('block-picked-start');
	}
}

function getAllBlocksBetween(start, end, outers_included) {
	if ( !start.isSameRplan(end) ) {
		return false;
	}
	
	if ( start.isAfterThan(end) ) {
		return false;
	}
	
	var queue = new Array();
	
	if ( outers_included ) {
		queue.push(jQuery('#'+start.id));
	}
	
	if ( start.isSameDay(end) ) {
		return queue;
	}

	var node = jQuery('#'+start.id).next();
	var end_id = jQuery('#'+end.id).attr('id');
	while( node.length > 0 && node.attr('id') != end_id ) {
		queue.push(node);
		node = node.next();
	}
	
	if ( outers_included ) {
		queue.push(jQuery('#'+end.id));
	}
	
	return queue;
}

function getPeriodStructure(data) {
	return {
		"day": parseInt(data[0]),
		"month": parseInt(data[1]),
		"year": parseInt(data[2]),
		"rplan": data[3],
		"rplanName": data[4],
		"defRate": data[5],
		"id": "cell-"+parseInt(data[0])+"-"+parseInt(data[1])+"-"+parseInt(data[2])+"-"+data[3],
		"isSameDay" : function(block) {
			return ( this.month == block.month && this.day == block.day && this.year == block.year );
		},
		"isBeforeThan" : function(block) {
			return ( 
				( this.year < block.year ) || 
				( this.year == block.year && this.month < block.month ) || 
				( this.year == block.year &&  this.month == block.month && this.day < block.day ) );
		},
		"isAfterThan" : function(block) {
			return ( 
				( this.year > block.year ) || 
				( this.year == block.year && this.month > block.month ) || 
				( this.year == block.year && this.month == block.month && this.day > block.day ) );
		},
		"isSameRplan" : function(block) {
			return ( this.rplan == block.rplan );
		},
		"toDate" : function(format) {
			return format.replace(
				'dd', ( this.day < 10 ? '0' : '' )+this.day
			).replace(
				'mm', ( this.month < 10 ? '0' : '' )+this.month
			).replace(
				'yy', this.year
			);
		}
	};
}

function initBlockStructure(id) {
	var s = id.split("-");
	if ( s.length != 5 ) {
		return {};
	}
	var elem = jQuery("#"+id);
	return {
		"day":parseInt(s[1]),
		"month":parseInt(s[2]),
		"year":parseInt(s[3]),
		"rplan":s[4],
		"rplanName": elem.parent("tr").find("td").first().text(),
		"defRate": elem.parent("tr").find("td").first().attr("data-defrate"),
		"id":id,
		"isSameDay" : function(block) {
			return ( this.month == block.month && this.day == block.day && this.year == block.year );
		},
		"isBeforeThan" : function(block) {
			return ( 
				( this.year < block.year ) || 
				( this.year == block.year && this.month < block.month ) || 
				( this.year == block.year &&  this.month == block.month && this.day < block.day ) );
		},
		"isAfterThan" : function(block) {
			return ( 
				( this.year > block.year ) || 
				( this.year == block.year && this.month > block.month ) || 
				( this.year == block.year && this.month == block.month && this.day > block.day ) );
		},
		"isSameRplan" : function(block) {
			return ( this.rplan == block.rplan );
		},
		"toDate" : function(format) {
			return format.replace(
				'dd', ( this.day < 10 ? '0' : '' )+this.day
			).replace(
				'mm', ( this.month < 10 ? '0' : '' )+this.month
			).replace(
				'yy', this.year
			);
		}
	};
}

function CalendarListener() {
	this.first = null;
	this.last = null;
}

CalendarListener.prototype.pickFirst = function(struct) {
	if ( !this.isFirstPicked() ) {
		this.first = struct;
		return true;
	}
	return false;
}

CalendarListener.prototype.pickLast = function(struct) {
	if ( !this.isLastPicked() && this.isFirstPicked() ) {
		this.last = struct;
		return true;
	}
	return false;
}

CalendarListener.prototype.clear = function() {
	this.first = null;
	this.last = null;
}

CalendarListener.prototype.isFirstPicked = function() {
	return this.first != null;
}

CalendarListener.prototype.isLastPicked = function() {
	return this.last != null;
}

/* Dates selection - End */
var timeline_height_set = false;
jQuery(document).ready(function() {
	jQuery(".vbo-ratesoverview-tab-los").click(function() {
		var nd = new Date();
		nd.setTime(nd.getTime() + (365*24*60*60*1000));
		document.cookie = "vboRovwRab=los; expires=" + nd.toUTCString() + "; path=/";
		jQuery(this).removeClass("vbo-ratesoverview-tab-unactive").addClass("vbo-ratesoverview-tab-active");
		jQuery(".vbo-ratesoverview-tab-cal").removeClass("vbo-ratesoverview-tab-active").addClass("vbo-ratesoverview-tab-unactive");
		jQuery(".vbo-ratesoverview-roomsel-entry-los").show();
		jQuery(".vbo-ratesoverview-caltab-cont").hide();
		jQuery(".vbo-ratesoverview-lostab-cont").fadeIn();
		if (!timeline_height_set) {
			jQuery('.vbo-timeline-container').css('min-height', (jQuery('.vbo-timeline-container').outerHeight() + 20));
			timeline_height_set = true;
		}
	});
	jQuery(".vbo-ratesoverview-tab-cal").click(function() {
		var nd = new Date();
		nd.setTime(nd.getTime() + (365*24*60*60*1000));
		document.cookie = "vboRovwRab=cal; expires=" + nd.toUTCString() + "; path=/";
		jQuery(this).removeClass("vbo-ratesoverview-tab-unactive").addClass("vbo-ratesoverview-tab-active");
		jQuery(".vbo-ratesoverview-tab-los").removeClass("vbo-ratesoverview-tab-active").addClass("vbo-ratesoverview-tab-unactive");
		jQuery(".vbo-ratesoverview-roomsel-entry-los").hide();
		jQuery(".vbo-ratesoverview-lostab-cont").hide();
		jQuery(".vbo-ratesoverview-caltab-cont").fadeIn();
	});
	if (window.location.hash == '#tabcal') {
		jQuery(".vbo-ratesoverview-tab-cal").trigger("click");
	}
	jQuery("body").on("click", ".vbo-ratesoverview-numnight", function() {
		var inpnight = jQuery(this).attr('id');
		if (jQuery('.vbo-ratesoverview-numnight').length > 1) {
			jQuery('#inp'+inpnight).remove();
			jQuery(this).remove();
		}
	});
	jQuery("body").on("dblclick", ".vbo-calcrates-rateblock", function() {
		jQuery(this).remove();
	});
	jQuery('#vbo-addnumnight-act').click(function() {
		var setnights = jQuery('#vbo-addnumnight').val();
		if (parseInt(setnights) > 0) {
			var los_exists = false;
			jQuery('.vbo-ratesoverview-numnight').each(function() {
				if (parseInt(jQuery(this).text()) == parseInt(setnights)) {
					los_exists = true;
				}
			});
			if (!los_exists) {
				jQuery('.vbo-ratesoverview-numnight').last().after("<span class=\"vbo-ratesoverview-numnight\" id=\"numnights"+setnights+"\">"+setnights+"</span><input type=\"hidden\" name=\"nights_cal[]\" id=\"inpnumnights"+setnights+"\" value=\""+setnights+"\" />");
			} else {
				jQuery('#vbo-addnumnight').val((parseInt(setnights) + 1));
			}
		}
	});
	jQuery('#vbo-ratesoverview-calculate').click(function() {
		jQuery(this).text('<?php echo addslashes(JText::_('VBRATESOVWRATESCALCULATORCALCING')); ?>').prop('disabled', true);
		jQuery('.vbo-ratesoverview-calculation-response').html('');
		var checkindate = jQuery("#checkindate").val();
		if (!(checkindate.length > 0)) {
			checkindate = '<?php echo date('Y-m-d') ?>';
			jQuery("#checkindate").val(checkindate);
		}
		var nights = jQuery("#vbo-numnights").val();
		var adults = jQuery("#vbo-numadults").val();
		var children = jQuery("#vbo-numchildren").val();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikbooking", task: "calc_rates", tmpl: "component", id_room: "<?php echo $roomrows['id']; ?>", checkin: checkindate, num_nights: nights, num_adults: adults, num_children: children }
		}).done(function(res) {
			res = JSON.parse(res);
			res = res[0];
			if (res.indexOf('e4j.error') >= 0 ) {
				jQuery(".vbo-ratesoverview-calculation-response").html("<p class='vbo-warning'>" + res.replace("e4j.error.", "") + "</p>").fadeIn();
			} else {
				jQuery(".vbo-ratesoverview-calculation-response").html(res).fadeIn();
			}
			jQuery('#vbo-ratesoverview-calculate').text('<?php echo addslashes(JText::_('VBRATESOVWRATESCALCULATORCALC')); ?>').prop('disabled', false);
		}).fail(function() { 
			jQuery(".vbo-ratesoverview-calculation-response").fadeOut();
			jQuery('#vbo-ratesoverview-calculate').text('<?php echo addslashes(JText::_('VBRATESOVWRATESCALCULATORCALC')); ?>').prop('disabled', false);
			alert("Error Performing Ajax Request"); 
		});
	});
});
</script>
