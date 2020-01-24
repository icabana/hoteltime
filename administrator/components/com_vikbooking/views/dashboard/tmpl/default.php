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

$arrayfirst = $this->arrayfirst;
$nextreservations = $this->nextreservations;
$checkin_today = $this->checkin_today;
$checkout_today = $this->checkout_today;
$rooms_locked = $this->rooms_locked;

$vbo_app = new VboApplication();
$vbo_auth_bookings = JFactory::getUser()->authorise('core.vbo.bookings', 'com_vikbooking');
$document = JFactory::getDocument();
$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator(true);
$up_running = true;
if ($arrayfirst['totprices'] < 1 || $arrayfirst['totrooms'] < 1 || $arrayfirst['tot_rooms_units'] < 1 || $arrayfirst['totdailyfares'] < 1) {
	$up_running = false;
	?>
<div class="vbo-dashboard-firstsetup">
	<h3><?php echo JText::_('VBDASHFIRSTSETTITLE'); ?></h3>
	<h4><?php echo JText::_('VBDASHFIRSTSETSUBTITLE'); ?></h4>
	<?php
	if ($arrayfirst['totprices'] < 1) {
		?>
	<p class="vbdashparagred">
		<span><?php echo JText::_('VBDASHNOPRICES'); ?>: 0</span>
		<a href="index.php?option=com_vikbooking&task=prices" class="btn btn-secondary"><?php echo JText::_('VBCONFIGURETASK'); ?></a>
	</p>
		<?php
	}
	if ($arrayfirst['totrooms'] < 1) {
		?>
	<p class="vbdashparagred">
		<span><?php echo JText::_('VBDASHNOROOMS'); ?>: 0</span>
		<a href="index.php?option=com_vikbooking&task=rooms" class="btn btn-secondary"><?php echo JText::_('VBCONFIGURETASK'); ?></a>
	</p>
		<?php
	}
	if ($arrayfirst['totdailyfares'] < 1) {
		?>
	<p class="vbdashparagred">
		<span><?php echo JText::_('VBDASHNODAILYFARES'); ?>: 0</span>
		<a href="index.php?option=com_vikbooking&task=tariffs" class="btn btn-secondary"><?php echo JText::_('VBCONFIGURETASK'); ?></a>
	</p>
		<?php
	}
	?>
</div>
	<?php
}
if ($up_running === true) {
	//First setup complete. Show reports, check-ins and check-outs today, next bookings
	JHTML::_('behavior.keepalive');
	$document->addScript(VBO_ADMIN_URI.'resources/donutChart.js');
	$wdaysmap = array('0' => JText::_('VBSUNDAY'), '1' => JText::_('VBMONDAY'), '2' => JText::_('VBTUESDAY'), '3' => JText::_('VBWEDNESDAY'), '4' => JText::_('VBTHURSDAY'), '5' => JText::_('VBFRIDAY'), '6' => JText::_('VBSATURDAY'));
	//Prepare modal
	?>
<script type="text/javascript">
function vboJModalShowCallback() {
	//simulate STOP click
	if (vbo_t_on) {
		vbo_t_on = false;
		clearTimeout(vbo_t);
		jQuery(".vbo-dashboard-refresh-play").fadeIn();
	}
}
function vboJModalHideCallback() {
	//simulate PLAY click
	if (!vbo_t_on) {
		vboStartTimer();
		jQuery(".vbo-dashboard-refresh-play").fadeOut();
	}
}
</script>
	<?php
	echo $vbo_app->getJmodalScript('', 'vboJModalHideCallback();', 'vboJModalShowCallback();');
	echo $vbo_app->getJmodalHtml('vbo-checkin-booking', JText::_('VBOMANAGECHECKSINOUT'));
	//end Prepare modal

	//Todays Check-in
	?>
<div class="vbo-dashboard-fullcontainer">
<div class="vbo-dashboard-today-bookings">
	<div class="vbo-dashboard-today-checkin-wrapper">
		<div class="vbo-dashboard-today-checkin-head">
			<h4><i class="vboicn-enter"></i><?php echo JText::_('VBDASHTODAYCHECKIN'); ?> <span id="arrivals-tot"><?php echo count($checkin_today); ?></span></h4>
			<div class="btn-toolbar pull-right vbo-dashboard-search-checkin">
				<div class="btn-wrapper input-append pull-right">
					<input type="text" class="checkin-search form-control" placeholder="<?php echo JText::_('VBODASHSEARCHKEYS'); ?>">
					<button type="button" class="btn" onclick="jQuery('.checkin-search').val('').trigger('keyup');"><i class="icon-remove"></i></button>
				</div>
			</div>
		</div>
		<div class="vbo-dashboard-today-checkin table-responsive">
			<table class="table vbo-table-search-cin">
				<thead>
					<tr class="vbo-dashboard-today-checkin-firstrow">
						<th class="left"><?php echo JText::_('VBDASHUPRESONE'); ?></th>
						<th class="left"><?php echo JText::_('VBCUSTOMERNOMINATIVE'); ?></th>
						<th class="center"><?php echo JText::_('VBDASHUPRESSIX'); ?></th>
						<th class="center"><?php echo JText::_('VBDASHUPRESTWO'); ?></th>
						<th class="center"><?php echo JText::_('VBDASHUPRESFOUR'); ?></th>
						<th class="center"><?php echo JText::_('VBDASHUPRESFIVE'); ?></th>
						<th class="vbo-tdright"> </th>
					</tr>
					<tr class="warning no-results">
						<td colspan="7"><i class="vboicn-warning"></i> <?php echo JText::_('VBONORESULTS'); ?></td>
					</tr>
				</thead>
				<tbody>
				<?php
				if (!(count($checkin_today) > 0)) {
					?>
					<tr class="warning">
						<td colspan="7"><i class="vboicn-warning"></i> <?php echo JText::_('VBONOCHECKINSTODAY'); ?></td>
					</tr>
					<?php
				}
				foreach ($checkin_today as $ink => $intoday) {
					$totpeople_str = $intoday['tot_adults']." ".($intoday['tot_adults'] > 1 ? JText::_('VBMAILADULTS') : JText::_('VBMAILADULT')).($intoday['tot_children'] > 0 ? ", ".$intoday['tot_children']." ".($intoday['tot_children'] > 1 ? JText::_('VBMAILCHILDREN') : JText::_('VBMAILCHILD')) : "");
					$room_names = array();
					$rooms = VikBooking::loadOrdersRoomsData($intoday['id']);
					foreach ($rooms as $rr) {
						$room_names[] = $rr['room_name'];
					}
					if ($intoday['roomsnum'] == 1) {
						// parse distintive features
						$unit_index = '';
						if (strlen($rooms[0]['roomindex']) && !empty($rooms[0]['params'])) {
							$room_params = json_decode($rooms[0]['params'], true);
							if (is_array($room_params) && array_key_exists('features', $room_params) && @count($room_params['features']) > 0) {
								foreach ($room_params['features'] as $rind => $rfeatures) {
									if ($rind == $rooms[0]['roomindex']) {
										foreach ($rfeatures as $fname => $fval) {
											if (strlen($fval)) {
												$unit_index = ' #'.$fval;
												break;
											}
										}
										break;
									}
								}
							}
						}
						//
						$roomstr = '<span class="vbo-smalltext">'.$room_names[0].$unit_index.'</span>';
					} else {
						$roomstr = '<span class="hasTooltip vbo-tip-small" title="'.implode(', ', $room_names).'">'.$intoday['roomsnum'].'</span><span class="hidden-for-search">'.implode(', ', $room_names).'</span>';
					}
					$act_status = '';
					if ($intoday['status'] == 'confirmed') {
						switch ($intoday['checked']) {
							case -1:
								$ord_status = '<span style="font-weight: bold; color: red;">'.strtoupper(JText::_('VBOCHECKEDSTATUSNOS')).'</span>';
								break;
							case 1:
								$ord_status = '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VBOCHECKEDSTATUSIN')).'</span>';
								break;
							case 2:
								$ord_status = '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VBOCHECKEDSTATUSOUT')).'</span>';
								break;
							default:
								$ord_status = '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VBCONFIRMED')).'</span>';
								break;
						}
						if ($vbo_auth_bookings && $intoday['closure'] != 1) {
							$act_status = '<button type="button" class="btn btn-small btn-primary" onclick="vboOpenJModal(\'vbo-checkin-booking\', \'index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$intoday['id'].'&tmpl=component\');">'.JText::_('VBOMANAGECHECKIN').'</button>';
						}
					} elseif ($intoday['status'] == 'standby') {
						$ord_status = '<span style="font-weight: bold; color: #cc9a04;">'.strtoupper(JText::_('VBSTANDBY')).'</span>';
					} else {
						$ord_status = '<span style="font-weight: bold; color: red;">'.strtoupper(JText::_('VBCANCELLED')).'</span>';
					}
					$nominative = strlen($intoday['nominative']) > 1 ? $intoday['nominative'] : VikBooking::getFirstCustDataField($intoday['custdata']);
					$country_flag = '';
					if (file_exists(VBO_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$intoday['country'].'.png')) {
						$country_flag = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$intoday['country'].'.png'.'" title="'.$intoday['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
					}
					?>
					<tr class="vbo-dashboard-today-checkin-rows">
						<td class="searchable left"><a href="index.php?option=com_vikbooking&amp;task=editorder&amp;cid[]=<?php echo $intoday['id']; ?>"><?php echo $intoday['id']; ?></a></td>
						<td class="searchable left"><?php echo $country_flag.$nominative; ?></td>
						<td class="center"><?php echo $totpeople_str; ?></td>
						<td class="searchable center"><?php echo $roomstr; ?></td>
						<td class="searchable center"><?php echo date(str_replace("/", $datesep, $df).' H:i', $intoday['checkout']); ?></td>
						<td class="searchable center" id="status-<?php echo $intoday['id']; ?>"><?php echo $ord_status; ?></td>
						<td class="vbo-tdright"><?php echo $act_status; ?></td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
	//Todays Check-out
	?>
	<div class="vbo-dashboard-today-checkout-wrapper">
		<div class="vbo-dashboard-today-checkout-head">
			<h4><i class="vboicn-exit"></i><?php echo JText::_('VBDASHTODAYCHECKOUT'); ?> <span id="departures-tot"><?php echo count($checkout_today); ?></span></h4>
			<div class="btn-toolbar pull-right vbo-dashboard-search-checkout">
				<div class="btn-wrapper input-append pull-right">
					<input type="text" class="checkout-search form-control" placeholder="<?php echo JText::_('VBODASHSEARCHKEYS'); ?>">
					<button type="button" class="btn" onclick="jQuery('.checkout-search').val('').trigger('keyup');"><i class="icon-remove"></i></button>
				</div>
			</div>
		</div>
		<div class="vbo-dashboard-today-checkout table-responsive">
			<table class="table vbo-table-search-cout">
				<thead>
					<tr class="vbo-dashboard-today-checkout-firstrow">
						<th class="left"><?php echo JText::_('VBDASHUPRESONE'); ?></th>
						<th class="left"><?php echo JText::_('VBCUSTOMERNOMINATIVE'); ?></th>
						<th class="center"><?php echo JText::_('VBDASHUPRESSIX'); ?></th>
						<th class="center"><?php echo JText::_('VBDASHUPRESTWO'); ?></th>
						<th class="center"><?php echo JText::_('VBDASHUPRESTHREE'); ?></th>
						<th class="center"><?php echo JText::_('VBDASHUPRESFIVE'); ?></th>
						<th class="vbo-tdright"> </th>
					</tr>
					<tr class="warning no-results">
						<td colspan="7"><i class="vboicn-warning"></i> <?php echo JText::_('VBONORESULTS'); ?></td>
					</tr>
				</thead>
				<tbody>
				<?php
				if (!(count($checkout_today) > 0)) {
					?>
					<tr class="warning">
						<td colspan="7"><i class="vboicn-warning"></i> <?php echo JText::_('VBONOCHECKOUTSTODAY'); ?></td>
					</tr>
					<?php
				}
				foreach ($checkout_today as $outk => $outtoday) {
					$totpeople_str = $outtoday['tot_adults']." ".($outtoday['tot_adults'] > 1 ? JText::_('VBMAILADULTS') : JText::_('VBMAILADULT')).($outtoday['tot_children'] > 0 ? ", ".$outtoday['tot_children']." ".($outtoday['tot_children'] > 1 ? JText::_('VBMAILCHILDREN') : JText::_('VBMAILCHILD')) : "");
					$room_names = array();
					$rooms = VikBooking::loadOrdersRoomsData($outtoday['id']);
					foreach ($rooms as $rr) {
						$room_names[] = $rr['room_name'];
					}
					if ($outtoday['roomsnum'] == 1) {
						// parse distintive features
						$unit_index = '';
						if (strlen($rooms[0]['roomindex']) && !empty($rooms[0]['params'])) {
							$room_params = json_decode($rooms[0]['params'], true);
							if (is_array($room_params) && array_key_exists('features', $room_params) && @count($room_params['features']) > 0) {
								foreach ($room_params['features'] as $rind => $rfeatures) {
									if ($rind == $rooms[0]['roomindex']) {
										foreach ($rfeatures as $fname => $fval) {
											if (strlen($fval)) {
												$unit_index = ' #'.$fval;
												break;
											}
										}
										break;
									}
								}
							}
						}
						//
						$roomstr = '<span class="vbo-smalltext">'.$room_names[0].$unit_index.'</span>';
					} else {
						$roomstr = '<span class="hasTooltip vbo-tip-small" title="'.implode(', ', $room_names).'">'.$outtoday['roomsnum'].'</span><span class="hidden-for-search">'.implode(', ', $room_names).'</span>';
					}
					$act_status = '';
					if ($outtoday['status'] == 'confirmed') {
						switch ($outtoday['checked']) {
							case -1:
								$ord_status = '<span style="font-weight: bold; color: red;">'.strtoupper(JText::_('VBOCHECKEDSTATUSNOS')).'</span>';
								break;
							case 1:
								$ord_status = '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VBOCHECKEDSTATUSIN')).'</span>';
								break;
							case 2:
								$ord_status = '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VBOCHECKEDSTATUSOUT')).'</span>';
								break;
							default:
								$ord_status = '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VBCONFIRMED')).'</span>';
								break;
						}
						if ($vbo_auth_bookings && $outtoday['closure'] != 1) {
							$act_status = '<button type="button" class="btn btn-small btn-primary" onclick="vboOpenJModal(\'vbo-checkin-booking\', \'index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$outtoday['id'].'&tmpl=component\');">'.JText::_('VBOMANAGECHECKOUT').'</button>';
						}
					} elseif ($outtoday['status'] == 'standby') {
						$ord_status = '<span style="font-weight: bold; color: #cc9a04;">'.strtoupper(JText::_('VBSTANDBY')).'</span>';
					} else {
						$ord_status = '<span style="font-weight: bold; color: red;">'.strtoupper(JText::_('VBCANCELLED')).'</span>';
					}
					$nominative = strlen($outtoday['nominative']) > 1 ? $outtoday['nominative'] : VikBooking::getFirstCustDataField($outtoday['custdata']);
					$country_flag = '';
					if (file_exists(VBO_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$outtoday['country'].'.png')) {
						$country_flag = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$outtoday['country'].'.png'.'" title="'.$outtoday['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
					}
					?>
					<tr class="vbo-dashboard-today-checkout-rows">
						<td class="searchable left"><a href="index.php?option=com_vikbooking&amp;task=editorder&amp;cid[]=<?php echo $outtoday['id']; ?>"><?php echo $outtoday['id']; ?></a></td>
						<td class="searchable left"><?php echo $country_flag.$nominative; ?></td>
						<td class="center"><?php echo $totpeople_str; ?></td>
						<td class="searchable center"><?php echo $roomstr; ?></td>
						<td class="searchable center"><?php echo date(str_replace("/", $datesep, $df).' H:i', $outtoday['checkin']); ?></td>
						<td class="searchable center" id="status-<?php echo $outtoday['id']; ?>"><?php echo $ord_status; ?></td>
						<td class="vbo-tdright"><?php echo $act_status; ?></td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
</div>
	<?php
	$busy = VikBooking::loadBusyRecords(array_keys($arrayfirst['all_rooms_ids']), $arrayfirst['today_end_ts']);
	//Used for Today's Rooms Occupancy
	$today_tot_occupancy = 0;
	//
	//Chart for Rooms Sold Today and all week
	?>
<div class="vbo-dashboard-charts">
	<h4><?php echo JText::_('VBDASHWEEKGLOBAVAIL'); ?></h4>
	<div class="vbo-dashboard-charts-wrapper">
	<?php
	$is_dst = date('I', $arrayfirst['today_end_ts']);
	for($i = 0; $i < 7; $i++) {
		$today_ts = $arrayfirst['today_end_ts'] + ($i * 86400);
		$is_now_dst = date('I', $today_ts);
		if ($is_dst != $is_now_dst) {
			if ((bool)$is_dst === true) {
				$today_ts += 3600;
				$season_fromdayts += 3600;
			} else {
				$today_ts -= 3600;
			}
			$is_dst = $is_now_dst;
		}
		$today_info = getdate($today_ts);
		$tot_booked_today = 0;
		if (count($busy) > 0) {
			foreach ($busy as $idroom => $rbusy) {
				if (in_array($idroom, $arrayfirst['unpublished_rooms'])) {
					continue;
				}
				foreach ($rbusy as $b) {
					$tmpone = getdate($b['checkin']);
					$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
					$ritts = strtotime($rit);
					$tmptwo = getdate($b['checkout']);
					$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
					$conts = strtotime($con);
					if ($today_ts >= $ritts && $today_ts < $conts) {
						$tot_booked_today++;
					}
				}
			}
		}
		$percentage_booked = round((100 * $tot_booked_today / $arrayfirst['tot_rooms_units']), 2);
		$outer_color = '#2a762c'; //green
		if ($percentage_booked > 33 && $percentage_booked <= 66) {
			$outer_color = '#ffa64d'; //orange
		} elseif ($percentage_booked > 66 && $percentage_booked < 100) {
			$outer_color = '#ff4d4d'; //red
		} elseif ($percentage_booked >= 100) {
			$outer_color = '#550000'; //black-red
		}
		//Used for Today's Rooms Occupancy
		$today_tot_occupancy = $i == 0 ? $tot_booked_today : $today_tot_occupancy;
		//
		?>
		<div class="vbo-dashboard-chart-container" id="vbo-dashboard-chart-container-<?php echo ($i + 1); ?>">
			<span class="vbo-dashboard-chart-date"><?php echo $i == 0 ? JText::_('VBTODAY').', ' : ''; ?><?php echo $wdaysmap[(string)$today_info['wday']]; ?> <?php echo $today_info['mday']; ?></span>
		</div>
		<script type="text/JavaScript">
		var todaychart = new donutChart("vbo-dashboard-chart-container-<?php echo ($i + 1); ?>");
		todaychart.draw({
			start: 0,
			end: <?php echo $tot_booked_today; ?>,
			maxValue: <?php echo $arrayfirst['tot_rooms_units']; ?>,
			size: 160,
			unitText: " / <?php echo $arrayfirst['tot_rooms_units']; ?>",
			animationSpeed: 3,
			textColor: "#22485d",
			titlePosition: "outer-top", //outer-bottom, outer-top, inner-bottom, inner-top
			titleText: "",
			titleColor: '#333333',
			outerCircleColor: '<?php echo $outer_color; ?>',
			innerCircleColor: '#ffffff',
			innerCircleStroke: '#333333'
		});
		</script>
		<?php
	}
	?>
	</div>
	<div class="vbo-dashboard-refresh-container">
		<div class="vbo-dashboard-refresh-head"><span class="vbo-dashboard-refresh-label"><?php echo JText::_('VBDASHNEXTREFRESH'); ?></span> <span class="vbo-dashboard-refresh-minutes">05</span>:<span class="vbo-dashboard-refresh-seconds">00</span></div>
		<span class="vbo-dashboard-refresh-stop"> </span>
		<span class="vbo-dashboard-refresh-play" style="display: none;"> </span>
	</div>
	<script type="text/JavaScript">
	var vbo_dash_counter = 300;
	var vbo_t;
	var vbo_m = 5;
	var vbo_s = 0;
	var vbo_t_on = false;
	function vboRefreshTimer() {
		vbo_dash_counter--;
		if (vbo_dash_counter <= 0) {
			vbo_t_on = false;
			clearTimeout(vbo_t);
			location.reload();
			return true;
		}
		vbo_m = Math.floor(vbo_dash_counter / 60);
		vbo_s = Math.floor((vbo_dash_counter - (vbo_m * 60)));
		jQuery(".vbo-dashboard-refresh-minutes").text("0"+vbo_m);
		jQuery(".vbo-dashboard-refresh-seconds").text((parseInt(vbo_s) < 10 ? "0"+vbo_s : vbo_s));
		vbo_t = setTimeout(vboRefreshTimer, 1000);
	}
	function vboStartTimer() {
		vbo_t = setTimeout(vboRefreshTimer, 1000);
		vbo_t_on = true;
	}
	jQuery(document).ready(function() {
		vboStartTimer();
		jQuery(".vbo-dashboard-refresh-stop").click(function(){
			if (vbo_t_on) {
				vbo_t_on = false;
				clearTimeout(vbo_t);
				jQuery(".vbo-dashboard-refresh-play").fadeIn();
			} else {
				jQuery(this).parent().fadeOut();
			}
		});
		jQuery(".vbo-dashboard-refresh-play").click(function(){
			if (!vbo_t_on) {
				vboStartTimer();
				jQuery(this).fadeOut();
			}
		});
		/* Check-in Search */
		jQuery(".checkin-search").keyup(function () {
			var searchTerm = jQuery(this).val();
			var listItem = jQuery('.vbo-table-search-cin tbody').children('tr');
			var searchSplit = searchTerm.replace(/ /g, "'):containsi('");
			jQuery.extend(jQuery.expr[':'], {'containsi': 
				function(elem, i, match, array) {
					return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
				}
			});
			jQuery(".vbo-table-search-cin tbody tr td.searchable").not(":containsi('" + searchSplit + "')").each(function(e) {
				jQuery(this).parent('tr').attr('visible', 'false');
			});
			jQuery(".vbo-table-search-cin tbody tr td.searchable:containsi('" + searchSplit + "')").each(function(e) {
				jQuery(this).parent('tr').attr('visible', 'true');
			});
			var jobCount = parseInt(jQuery('.vbo-table-search-cin tbody tr[visible="true"]').length);
			jQuery('#arrivals-tot').text(jobCount);
			if (jobCount > 0) {
				jQuery('.vbo-table-search-cin').find('.no-results').hide();
			} else {
				jQuery('.vbo-table-search-cin').find('.no-results').show();
			}
		});
		/* Check-in Search */
		jQuery(".checkout-search").keyup(function () {
			var searchTerm = jQuery(this).val();
			var listItem = jQuery('.vbo-table-search-cout tbody').children('tr');
			var searchSplit = searchTerm.replace(/ /g, "'):containsi('");
			jQuery.extend(jQuery.expr[':'], {'containsi': 
				function(elem, i, match, array) {
					return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
				}
			});
			jQuery(".vbo-table-search-cout tbody tr td.searchable").not(":containsi('" + searchSplit + "')").each(function(e) {
				jQuery(this).parent('tr').attr('visible', 'false');
			});
			jQuery(".vbo-table-search-cout tbody tr td.searchable:containsi('" + searchSplit + "')").each(function(e) {
				jQuery(this).parent('tr').attr('visible', 'true');
			});
			var jobCount = parseInt(jQuery('.vbo-table-search-cout tbody tr[visible="true"]').length);
			jQuery('#departures-tot').text(jobCount);
			if (jobCount > 0) {
				jQuery('.vbo-table-search-cout').find('.no-results').hide();
			} else {
				jQuery('.vbo-table-search-cout').find('.no-results').show();
			}
		});
		/* Today Search */
		jQuery(".today-search").keyup(function () {
			var searchTerm = jQuery(this).val();
			var searchSplit = searchTerm.replace(/ /g, "'):containsi('");
			jQuery.extend(jQuery.expr[':'], {'containsi': 
				function(elem, i, match, array) {
					return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
				}
			});
			jQuery(".vbo-table-search-today tbody tr td.searchable").not(":containsi('" + searchSplit + "')").each(function(e) {
				jQuery(this).parent('tr').attr('visible', 'false');
			});
			jQuery(".vbo-table-search-today tbody tr td.searchable:containsi('" + searchSplit + "')").each(function(e) {
				jQuery(this).parent('tr').attr('visible', 'true');
			});
			jQuery('.vbo-table-search-today').each(function(k, v) {
				var jobCount = parseInt(jQuery(this).find('tbody tr[visible="true"]').length);
				if (jobCount > 0) {
					jQuery(this).find('.no-results').hide();
				} else {
					jQuery(this).find('.no-results').show();
				}
			});
		});
	});
	</script>
</div>
	<?php
	//Today's Rooms Occupancy
	if ($today_tot_occupancy > 0) {
		$today_rbookmap = array();
		$today_bidbookmap = array();
		foreach ($busy as $idroom => $rbusy) {
			foreach ($rbusy as $b) {
				$tmpone=getdate($b['checkin']);
				$rit=($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
				$ritts=strtotime($rit);
				$tmptwo=getdate($b['checkout']);
				$con=($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
				$conts=strtotime($con);
				if ($arrayfirst['today_end_ts'] >= $ritts && $arrayfirst['today_end_ts'] < $conts) {
					if (array_key_exists($b['idroom'], $today_rbookmap)) {
						$today_rbookmap[$b['idroom']]++;
						$today_bidbookmap[$b['idroom']][] = $b['id'];
					} else {
						$today_rbookmap[$b['idroom']] = 1;
						$today_bidbookmap[$b['idroom']] = array($b['id']);
					}
				}
			}
		}
		?>
<div class="vbo-dashboard-today-occ-block">
	<div class="vbo-dashboard-today-occ">
		<h4><?php echo JText::_('VBDASHTODROCC'); ?></h4>
		<div class="vbo-center">
			<div class="btn-wrapper input-append">
				<input type="text" class="today-search form-control" placeholder="<?php echo JText::_('VBODASHSEARCHKEYS'); ?>">
				<button type="button" class="btn" onclick="jQuery('.today-search').val('').trigger('keyup');"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="vbo-dashboard-today-occ-listcont">
		<?php
		foreach ($today_rbookmap as $idr => $rbked) {
			$room_bookings_det = VikBooking::getRoomBookingsFromBusyIds($idr, $today_bidbookmap[$idr]);
			if (count($room_bookings_det) == 1 && $room_bookings_det[0]['closure'] == 1) {
				//skip rooms with just a closure
				continue;
			}
			?>
			<div class="vbo-dashboard-today-roomocc">
				<div class="vbo-dashboard-today-roomocc-det">
					<h5><?php echo $arrayfirst['all_rooms_ids'][$idr]; ?> <span><?php echo $rbked; ?></span> / <span><?php echo $arrayfirst['all_rooms_units'][$idr]; ?></span></h5>
					<div class="vbo-dashboard-today-roomocc-customers table-responsive">
						<table class="table vbo-table-search-today">
							<thead>
								<tr class="vbo-dashboard-today-roomocc-firstrow">
									<th class="left"><?php echo JText::_('VBCUSTOMERNOMINATIVE'); ?></th>
									<th class="center">&nbsp;</th>
									<th class="center"><?php echo JText::_('VBDASHUPRESFOUR'); ?></th>
								</tr>
								<tr class="warning no-results">
									<td colspan="7"><i class="vboicn-warning"></i> <?php echo JText::_('VBONORESULTS'); ?></td>
								</tr>
							</thead>
							<tbody>
							<?php
							foreach ($room_bookings_det as $rbind => $room_booking) {
								$nominative = strlen($room_booking['nominative']) > 1 ? $room_booking['nominative'] : VikBooking::getFirstCustDataField($room_booking['custdata']);
								$country_flag = '';
								if (file_exists(VBO_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$room_booking['country'].'.png')) {
									$country_flag = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$room_booking['country'].'.png'.'" title="'.$room_booking['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
								}
								//Room specific unit
								$room_first_feature = '&nbsp;';
								if (!empty($room_booking['roomindex']) && array_key_exists($idr, $arrayfirst['all_rooms_features']) && count($arrayfirst['all_rooms_features'][$idr]) > 0) {
									foreach ($arrayfirst['all_rooms_features'][$idr] as $rind => $rfeatures) {
										if ($rind != $room_booking['roomindex']) {
											continue;
										}
										foreach ($rfeatures as $fname => $fval) {
											if (strlen($fval)) {
												$room_first_feature = '#'.$rind.' - '.JText::_($fname).': '.$fval;
												break 2;
											}
										}
									}
								}
								//
								$act_status = '';
								if ($vbo_auth_bookings && $room_booking['closure'] != 1 && $room_booking['checked'] != 0) {
									$act_status = '<button type="button" class="btn btn-small btn-primary pull-right" onclick="vboOpenJModal(\'vbo-checkin-booking\', \'index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$room_booking['idorder'].'&tmpl=component\');"><i class="vboicn-users icn-nomargin"></i></button>';
								}
								?>
								<tr class="vbo-dashboard-today-roomocc-rows">
									<td class="searchable left"><?php echo $country_flag.'<a href="index.php?option=com_vikbooking&task=editorder&cid[]='.$room_booking['idorder'].'" target="_blank">'.$nominative.'</a>'; ?></td>
									<td class="searchable center"><?php echo $room_first_feature; ?></td>
									<td class="searchable center"><?php echo date(str_replace("/", $datesep, $df).' H:i', $room_booking['checkout']).$act_status; ?></td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		</div>
	</div>
</div>
		<?php
	}
	//
	//Next Bookings
	?>
<div class="vbo-dashboard-next-bookings-block">
	<h4><i class="vboicn-stopwatch"></i> <?php echo JText::_('VBDASHUPCRES'); ?></h4>
	<div class="vbo-dashboard-next-bookings table-responsive">
		<table class="table">
			<thead>
				<tr class="vbo-dashboard-today-checkout-firstrow">
					<th class="left"><?php echo JText::_('VBDASHUPRESONE'); ?></th>
					<th class="left"><?php echo JText::_('VBCUSTOMERNOMINATIVE'); ?></th>
					<th class="left"><?php echo JText::_('VBDASHUPRESSIX'); ?></th>
					<th class="left"><?php echo JText::_('VBDASHUPRESTWO'); ?></th>
					<th class="left"><?php echo JText::_('VBDASHUPRESTHREE'); ?></th>
					<th class="left"><?php echo JText::_('VBDASHUPRESFOUR'); ?></th>
					<th class="left"><?php echo JText::_('VBDASHUPRESFIVE'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($nextreservations as $nbk => $next) {
				$totpeople_str = $next['tot_adults']." ".($next['tot_adults'] > 1 ? JText::_('VBMAILADULTS') : JText::_('VBMAILADULT')).($next['tot_children'] > 0 ? ", ".$next['tot_children']." ".($next['tot_children'] > 1 ? JText::_('VBMAILCHILDREN') : JText::_('VBMAILCHILD')) : "");
				$room_names = array();
				$rooms = VikBooking::loadOrdersRoomsData($next['id']);
				foreach ($rooms as $rr) {
					$room_names[] = $rr['room_name'];
				}
				if ($next['roomsnum'] == 1) {
					$roomstr = '<span class="vbo-smalltext">'.$room_names[0].'</span>';
				} else {
					$roomstr = '<span class="hasTooltip vbo-tip-small" title="'.implode(', ', $room_names).'">'.$next['roomsnum'].'</span>';
				}
				if ($next['status'] == 'confirmed') {
					$ord_status = '<span class="label label-success vbo-status-label">'.JText::_('VBCONFIRMED').'</span>';
				} elseif ($next['status'] == 'standby') {
					$ord_status = '<span class="label label-warning vbo-status-label">'.JText::_('VBSTANDBY').'</span>';
				} else {
					$ord_status = '<span class="label label-error vbo-status-label" style="background-color: #d9534f;">'.JText::_('VBCANCELLED').'</span>';
				}
				$nominative = strlen($next['nominative']) > 1 ? $next['nominative'] : VikBooking::getFirstCustDataField($next['custdata']);
				$country_flag = '';
				if (file_exists(VBO_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$next['country'].'.png')) {
					$country_flag = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$next['country'].'.png'.'" title="'.$next['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
				}
				?>
				<tr class="vbo-dashboard-today-checkout-rows">
					<td align="left"><a href="index.php?option=com_vikbooking&amp;task=editorder&amp;cid[]=<?php echo $next['id']; ?>"><?php echo $next['id']; ?></a></td>
					<td align="left"><?php echo $country_flag.$nominative; ?></td>
					<td align="left"><?php echo $totpeople_str; ?></td>
					<td align="left"><?php echo $roomstr; ?></td>
					<td align="left"><?php echo date(str_replace("/", $datesep, $df).' H:i', $next['checkin']); ?></td>
					<td align="left"><?php echo date(str_replace("/", $datesep, $df).' H:i', $next['checkout']); ?></td>
					<td align="left"><?php echo $ord_status; ?></td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
	</div>
</div>
	<?php
	//Rooms Locked
	if (count($rooms_locked)) {
		?>
<div class="vbo-dashboard-rooms-locked-block">
	<div class="vbo-dashboard-rooms-locked table-responsive">
		<h4 id="vbo-dashboard-rooms-locked-toggle"><i class="fa fa-lock"></i> <?php echo JText::_('VBDASHROOMSLOCKED'); ?><span>(<?php echo count($rooms_locked); ?>)</span></h4>
		<table class="table" style="display: none;">
			<tr class="vbo-dashboard-rooms-locked-firstrow">
				<td class="center"><?php echo JText::_('VBDASHROOMNAME'); ?></td>
				<td class="center"><?php echo JText::_('VBCUSTOMERNOMINATIVE'); ?></td>
				<td class="center"><?php echo JText::_('VBDASHLOCKUNTIL'); ?></td>
				<td class="center"><?php echo JText::_('VBDASHBOOKINGID'); ?></td>
				<td class="center">&nbsp;</td>
			</tr>
		<?php
		foreach ($rooms_locked as $lock) {
			?>
			<tr class="vbo-dashboard-rooms-locked-rows">
				<td class="center"><?php echo $lock['name']; ?></td>
				<td class="center"><?php echo $lock['nominative']; ?></td>
				<td class="center"><?php echo date(str_replace("/", $datesep, $df).' H:i', $lock['until']); ?></td>
				<td class="center"><a href="index.php?option=com_vikbooking&amp;task=editorder&amp;cid[]=<?php echo $lock['idorder']; ?>" target="_blank"><?php echo $lock['idorder']; ?></a></td>
				<td class="center"><button type="button" class="btn btn-danger" onclick="if (confirm('<?php echo addslashes(JText::_('VBDELCONFIRM')); ?>')) location.href='index.php?option=com_vikbooking&amp;task=unlockrecords&amp;cid[]=<?php echo $lock['id']; ?>';"><?php echo JText::_('VBDASHUNLOCK'); ?></button></td>
			</tr>
			<?php
		}
		?>
		</table>
	</div>
</div>
<script type="text/JavaScript">
jQuery(document).ready(function() {
	jQuery("#vbo-dashboard-rooms-locked-toggle").click(function(){
		jQuery(this).next("table").fadeToggle();
	});
});
</script>
		<?php
	}
	?>
</div>
	<?php
}
?>
<script type="text/JavaScript">
jQuery(document).ready(function() {
	if (jQuery.isFunction(jQuery.fn.tooltip)) {
		jQuery(".hasTooltip").tooltip();
	} else {
		jQuery.fn.tooltip = function(){};
	}
});
</script>