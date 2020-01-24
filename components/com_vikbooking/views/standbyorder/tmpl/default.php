<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

$ord = $this->ord;
$orderrooms = $this->orderrooms;
$tars = $this->tars;
$payment = $this->payment;
$vbo_tn = $this->vbo_tn;

$currencysymb = VikBooking::getCurrencySymb();
$nowdf = VikBooking::getDateFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator();
$pnodep = VikRequest::getInt('nodep', '', 'request');
$pitemid = VikRequest::getInt('Itemid', '', 'request');
$now_info = getdate();

$wdays_map = array(
	JText::_('VBWEEKDAYZERO'),
	JText::_('VBWEEKDAYONE'),
	JText::_('VBWEEKDAYTWO'),
	JText::_('VBWEEKDAYTHREE'),
	JText::_('VBWEEKDAYFOUR'),
	JText::_('VBWEEKDAYFIVE'),
	JText::_('VBWEEKDAYSIX')
);

$isdue = 0;
$imp = 0;
$pricenames = array();
$optbought = array();
$extraservices = array();
$roomsnames = array();
$is_package = !empty($ord['pkg']) ? true : false;
foreach ($orderrooms as $kor => $or) {
	$num = $kor + 1;
	$roomsnames[] = $or['name'];
	if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
		//package cost or cust_cost should always be inclusive of taxes
		$calctar = $or['cust_cost'];
		$isdue += $calctar;
		$imp += VikBooking::sayPackageMinusIva($or['cust_cost'], $or['cust_idiva']);
		$pricenames[$num] = (!empty($or['pkg_name']) ? $or['pkg_name'] : (!empty($or['otarplan']) ? $or['otarplan'] : JText::_('VBOROOMCUSTRATEPLAN')));
	} elseif (array_key_exists($num, $tars) && is_array($tars[$num])) {
		$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
		$calctar = VikBooking::sayCostPlusIva($display_rate, $tars[$num]['idprice']);
		$tars[$num]['calctar'] = $calctar;
		$isdue += $calctar;
		$imp += VikBooking::sayCostMinusIva($display_rate, $tars[$num]['idprice']);
		$pricenames[$num] = VikBooking::getPriceName($tars[$num]['idprice'], $vbo_tn);
	}
	if (!empty ($or['optionals'])) {
		$stepo = explode(";", $or['optionals']);
		foreach ($stepo as $one) {
			if (!empty ($one)) {
				$stept = explode(":", $one);
				$actopt = VikBooking::getSingleOption($stept[0], $vbo_tn);
				if (count($actopt) > 0) {
					$chvar = '';
					if (!empty($actopt['ageintervals']) && $or['children'] > 0 && strstr($stept[1], '-') != false) {
						$optagecosts = VikBooking::getOptionIntervalsCosts($actopt['ageintervals']);
						$optagenames = VikBooking::getOptionIntervalsAges($actopt['ageintervals']);
						$optagepcent = VikBooking::getOptionIntervalsPercentage($actopt['ageintervals']);
						$agestept = explode('-', $stept[1]);
						$stept[1] = $agestept[0];
						$chvar = $agestept[1];
						if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
							//percentage value of the adults tariff
							if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
								$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
							} else {
								$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
								$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
							}
						} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
							//VBO 1.10 - percentage value of room base cost
							if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
								$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
							} else {
								$display_rate = isset($tars[$num]['room_base_cost']) ? $tars[$num]['room_base_cost'] : (!empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost']);
								$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
							}
						}
						$actopt['chageintv'] = $chvar;
						$actopt['name'] .= ' ('.$optagenames[($chvar - 1)].')';
						$realcost = (intval($actopt['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $ord['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
					} else {
						$realcost = (intval($actopt['perday']) == 1 ? ($actopt['cost'] * $ord['days'] * $stept[1]) : ($actopt['cost'] * $stept[1]));
					}
					if (!empty ($actopt['maxprice']) && $actopt['maxprice'] > 0 && $realcost > $actopt['maxprice']) {
						$realcost = $actopt['maxprice'];
						if(intval($actopt['hmany']) == 1 && intval($stept[1]) > 1) {
							$realcost = $actopt['maxprice'] * $stept[1];
						}
					}
					if ($actopt['perperson'] == 1) {
						$realcost = $realcost * $or['adults'];
					}
					$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt['idiva']);
					$isdue += $tmpopr;
					$imp += VikBooking::sayOptionalsMinusIva($realcost, $actopt['idiva']);
					if (!isset($optbought[$num])) {
						$optbought[$num] = '';
					}
					$optbought[$num] .= "<div><span class=\"vbo-booking-pricename\">".($stept[1] > 1 ? $stept[1] . " " : "") . $actopt['name'] . "</span> <span class=\"vbo_currency\">" . $currencysymb . "</span> <span class=\"vbo_price\">" . VikBooking::numberFormat($tmpopr) . "</span></div>";
				}
			}
		}
	}
	//custom extra costs
	if (!empty($or['extracosts'])) {
		$extraservices[$num] = '';
		$cur_extra_costs = json_decode($or['extracosts'], true);
		foreach ($cur_extra_costs as $eck => $ecv) {
			$ecplustax = !empty($ecv['idtax']) ? VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
			$isdue += $ecplustax;
			$imp += !empty($ecv['idtax']) ? VikBooking::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
			$extraservices[$num] .= "<div><span class=\"vbo-booking-pricename\">".$ecv['name']."</span> <span class=\"vbo_currency\">" . $currencysymb . "</span> <span class=\"vbo_price\">" . VikBooking::numberFormat($ecplustax) . "</span></div>";
		}
	}
	//
}

$tax = $isdue - $imp;

//vikbooking 1.1 coupon
$usedcoupon = false;
$origisdue = $isdue;
if (strlen($ord['coupon']) > 0) {
	$usedcoupon = true;
	$expcoupon = explode(";", $ord['coupon']);
	$isdue = $isdue - $expcoupon[1];
}
//

$ts_info = getdate($ord['ts']);
$checkin_info = getdate($ord['checkin']);
$checkout_info = getdate($ord['checkout']);

?>

<div class="vbo-booking-details-topcontainer">

	<div class="vbo-booking-details-head <?php echo $ord['status'] != 'cancelled' ? 'vbo-booking-details-head-pending' : 'vbo-booking-details-head-cancelled'; ?>">
	<?php
	if ($ord['status'] != 'cancelled') {
		?>
		<h4><?php echo JText::_('VBOYOURBOOKISPEND'); ?></h4>
		<?php
	} else {
		?>
		<h4><?php echo JText::_('VBOYOURBOOKISCANC'); ?></h4>
		<?php
	}
	?>
	</div>

	<div class="vbo-booking-details-midcontainer">

		<div class="vbo-booking-details-bookinfos">
			<span class="vbvordudatatitle"><?php echo JText::_('VBORDERDETAILS'); ?></span>
			<div class="vbo-booking-details-bookinfo">
				<span class="vbo-booking-details-bookinfo-lbl"><?php echo JText::_('VBORDEREDON'); ?></span>
				<span class="vbo-booking-details-bookinfo-val"><?php echo $wdays_map[$ts_info['wday']].', '.date(str_replace("/", $datesep, $df).' H:i', $ord['ts']); ?></span>
			</div>
			<div class="vbo-booking-details-bookinfo">
				<span class="vbo-booking-details-bookinfo-lbl"><?php echo JText::_('VBDAL'); ?></span>
				<span class="vbo-booking-details-bookinfo-val"><?php echo $wdays_map[$checkin_info['wday']].', '.date(str_replace("/", $datesep, $df).' H:i', $ord['checkin']); ?></span>
			</div>
			<div class="vbo-booking-details-bookinfo">
				<span class="vbo-booking-details-bookinfo-lbl"><?php echo JText::_('VBAL'); ?></span>
				<span class="vbo-booking-details-bookinfo-val"><?php echo $wdays_map[$checkout_info['wday']].', '.date(str_replace("/", $datesep, $df).' H:i', $ord['checkout']); ?></span>
			</div>
			<div class="vbo-booking-details-bookinfo">
				<span class="vbo-booking-details-bookinfo-lbl"><?php echo JText::_('VBDAYS'); ?></span>
				<span class="vbo-booking-details-bookinfo-val"><?php echo $ord['days']; ?></span>
			</div>
		</div>

		<div class="vbo-booking-details-udets">
			<span class="vbvordudatatitle"><?php echo JText::_('VBPERSDETS'); ?></span>
			<div class="vbo-bookingdet-custdata">
			<?php
			$custdata_parts = explode("\n", $ord['custdata']);
			if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
				//attempt to format labels and values
				foreach ($custdata_parts as $custdet) {
					if (strlen($custdet) < 1) {
						continue;
					}
					$custdet_parts = explode(':', $custdet);
					$custd_lbl = '';
					$custd_val = '';
					if (count($custdet_parts) < 2) {
						$custd_val = $custdet;
					} else {
						$custd_lbl = $custdet_parts[0];
						unset($custdet_parts[0]);
						$custd_val = trim(implode(':', $custdet_parts));
					}
					?>
				<div class="vbo-bookingdet-userdetail">
					<?php
					if (strlen($custd_lbl)) {
						?>
					<span class="vbo-bookingdet-userdetail-lbl"><?php echo $custd_lbl; ?></span>
						<?php
					}
					if (strlen($custd_val)) {
						?>
					<span class="vbo-bookingdet-userdetail-val"><?php echo $custd_val; ?></span>
						<?php
					}
					?>
				</div>
					<?php
				}
			} else {
				echo nl2br($ord['custdata']);
			}
			?>
			</div>
		</div>

	</div>

</div>

<div class="vbo-booking-rooms-wrapper">
<?php
foreach ($orderrooms as $kor => $or) {
	$num = $kor + 1;
	?>
	<div class="vbvordroominfo<?php echo count($orderrooms) > 1 ? ' vbvordroominfo-multi' : ''; ?>">
		<?php
		if (strlen($or['img']) > 0) {
			?>
		<div class="vbo-booking-roomphoto">
			<img src="<?php echo VBO_SITE_URI; ?>resources/uploads/<?php echo $or['img']; ?>"/>
		</div>
			<?php
		}
		?>
		<div class="vbordroomdet">
			<span class="vbvordroominfotitle"><?php echo $or['name']; ?></span>
			<div class="vbordroomdetpeople">
				<span class="vbo-booking-numadults"><?php echo $or['adults']; ?> <?php echo ($or['adults'] == 1 ? JText::_('VBSEARCHRESADULT') : JText::_('VBSEARCHRESADULTS')); ?></span>
			<?php
			if ($or['children'] > 0) {
				?>
				<span class="vbo-booking-numchildren"><?php echo $or['children']." ".($or['children'] == 1 ? JText::_('VBSEARCHRESCHILD') : JText::_('VBSEARCHRESCHILDREN')); ?></span>
				<?php
			}
			?>
			</div>
		<?php
		if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
			?>
			<div class="vbo-booking-roomrate">
				<span class="vbvordcoststitlemain">
					<span class="vbo-booking-pricename"><?php echo $pricenames[$num]; ?></span>
					<span class="vbo_currency"><?php echo $currencysymb; ?></span> 
					<span class="vbo_price"><?php echo VikBooking::numberFormat($or['cust_cost']); ?></span>
				</span>
			</div>
			<?php
		} elseif (array_key_exists($num, $tars) && is_array($tars[$num])) {
			?>
			<div class="vbo-booking-roomrate">
				<span class="vbvordcoststitlemain">
					<span class="vbo-booking-pricename"><?php echo $pricenames[$num]; ?></span>
					<span class="vbo_currency"><?php echo $currencysymb; ?></span> 
					<span class="vbo_price"><?php echo VikBooking::numberFormat($tars[$num]['calctar']); ?></span>
				</span>
			</div>
			<?php
		}
		?>
		</div>
		
		<div class="vbvordcosts">
		<?php
		if (array_key_exists($num, $optbought) && strlen($optbought[$num]) > 0) {
			?>
			<div>
				<span class="vbvordcoststitle"><?php echo JText::_('VBOPTS'); ?>:</span>
				<div class="vbvordcostsoptionals"><?php echo $optbought[$num]; ?></div>
			</div>
			<?php
		}
		if (array_key_exists($num, $extraservices) && strlen($extraservices[$num]) > 0) {
			?>
			<div>
				<span class="vbvordcoststitle"><?php echo JText::_('VBOEXTRASERVICES'); ?>:</span>
				<div class="vbvordextraservices"><?php echo $extraservices[$num]; ?></div>
			</div>
			<?php
		}
		?>
		</div>
		
	</div>
	<?php
}
?>
</div>
	
	<div class="vbvordcosts">
		<?php
		if($usedcoupon == true) {
		?>
		<p class="vbvordcostsdiscount"><span class="vbvordcoststitle"><?php echo JText::_('VBCOUPON').' '.$expcoupon[2]; ?>:</span> - <span class="vbo_currency"><?php echo $currencysymb; ?></span> <span class="vbo_price"><?php echo VikBooking::numberFormat($expcoupon[1]); ?></span></p>
		<?php
		}
		?>
		<p class="vbvordcoststot"><span class="vbvordcoststitle"><?php echo JText::_('VBTOTAL'); ?>:</span> <span class="vbo_currency"><?php echo $currencysymb; ?></span> <span class="vbo_price"><?php echo VikBooking::numberFormat($isdue); ?></span></p>
	</div>
		
		<?php

if (is_array($payment) && $ord['status'] != 'cancelled') {
	require_once(VBO_ADMIN_PATH . DS . "payments" . DS . $payment['file']);
	$lang = JFactory::getLanguage();
	$langtag = substr($lang->getTag(), 0, 2);
	$return_url = JURI::root() . "index.php?option=com_vikbooking&task=vieworder&sid=" . $ord['sid'] . "&ts=" . $ord['ts']."&lang=".$langtag;
	$error_url = JURI::root() . "index.php?option=com_vikbooking&task=vieworder&sid=" . $ord['sid'] . "&ts=" . $ord['ts']."&lang=".$langtag;
	$notify_url = JURI::root() . "index.php?option=com_vikbooking&task=notifypayment&sid=" . $ord['sid'] . "&ts=" . $ord['ts']."&lang=".$langtag."&tmpl=component";
	$transaction_name = VikBooking::getPaymentName();
	$leave_deposit = 0;
	$percentdeposit = "";
	$array_order = array ();
	$array_order['details'] = $ord;
	$array_order['customer_email'] = $ord['custmail'];
	$array_order['account_name'] = VikBooking::getPaypalAcc();
	$array_order['transaction_currency'] = VikBooking::getCurrencyCodePp();
	$array_order['rooms_name'] = implode(", ", $roomsnames);
	$array_order['transaction_name'] = !empty ($transaction_name) ? $transaction_name : implode(", ", $roomsnames);
	$array_order['order_total'] = $isdue;
	$array_order['currency_symb'] = $currencysymb;
	$array_order['net_price'] = $imp;
	$array_order['tax'] = $tax;
	$array_order['return_url'] = $return_url;
	$array_order['error_url'] = $error_url;
	$array_order['notify_url'] = $notify_url;
	$array_order['total_to_pay'] = $isdue;
	$array_order['total_net_price'] = $imp;
	$array_order['total_tax'] = $tax;
	$totalchanged = false;
	if ($payment['charge'] > 0.00) {
		$totalchanged = true;
		if($payment['ch_disc'] == 1) {
			//charge
			if($payment['val_pcent'] == 1) {
				//fixed value
				$array_order['total_net_price'] += $payment['charge'];
				$array_order['total_tax'] += $payment['charge'];
				$array_order['total_to_pay'] += $payment['charge'];
				$newtotaltopay = $array_order['total_to_pay'];
			}else {
				//percent value
				$percent_net = $array_order['total_net_price'] * $payment['charge'] / 100;
				$percent_tax = $array_order['total_tax'] * $payment['charge'] / 100;
				$percent_to_pay = $array_order['total_to_pay'] * $payment['charge'] / 100;
				$array_order['total_net_price'] += $percent_net;
				$array_order['total_tax'] += $percent_tax;
				$array_order['total_to_pay'] += $percent_to_pay;
				$newtotaltopay = $array_order['total_to_pay'];
			}
		}else {
			//discount
			if($payment['val_pcent'] == 1) {
				//fixed value
				$array_order['total_net_price'] -= $payment['charge'];
				$array_order['total_tax'] -= $payment['charge'];
				$array_order['total_to_pay'] -= $payment['charge'];
				$newtotaltopay = $array_order['total_to_pay'];
			}else {
				//percent value
				$percent_net = $array_order['total_net_price'] * $payment['charge'] / 100;
				$percent_tax = $array_order['total_tax'] * $payment['charge'] / 100;
				$percent_to_pay = $array_order['total_to_pay'] * $payment['charge'] / 100;
				$array_order['total_net_price'] -= $percent_net;
				$array_order['total_tax'] -= $percent_tax;
				$array_order['total_to_pay'] -= $percent_to_pay;
				$newtotaltopay = $array_order['total_to_pay'];
			}
		}
	}
	$percentdeposit = false;
	if (!VikBooking::payTotal() && $this->nodep != 1 && VikBooking::allowDepositFromRates($tars)) {
		$percentdeposit = VikBooking::getAccPerCent();
		$percentdeposit = VikBooking::calcDepositOverride($percentdeposit, $ord['days']);
		if ($percentdeposit > 0 && VikBooking::depositAllowedDaysAdv($ord['checkin'])) {
			$leave_deposit = 1;
			if(VikBooking::getTypeDeposit() == "fixed") {
				$array_order['total_to_pay'] = $percentdeposit;
				$array_order['total_net_price'] = $percentdeposit;
				$array_order['total_tax'] = ($array_order['total_to_pay'] - $array_order['total_net_price']);
			}else {
				$array_order['total_to_pay'] = $array_order['total_to_pay'] * $percentdeposit / 100;
				$array_order['total_net_price'] = $array_order['total_net_price'] * $percentdeposit / 100;
				$array_order['total_tax'] = ($array_order['total_to_pay'] - $array_order['total_net_price']);
			}
		}
	}
	$array_order['leave_deposit'] = $leave_deposit;
	$array_order['percentdeposit'] = $percentdeposit;
	$array_order['payment_info'] = $payment;

	//Auto Removal Minutes
	$minautoremove = VikBooking::getMinutesAutoRemove();
	$mins_elapsed = floor(($now_info[0] - $ord['ts']) / 60);
	if ($minautoremove > 0) {
		$booktime_info = getdate($ord['ts']);
		$booktime_offset = date('Z', $ord['ts']) * 60;
		$remainmin = $minautoremove - $mins_elapsed;
		$remainmin = $remainmin < 1 ? 1 : $remainmin;
		$remainmilsec = intval($remainmin * 60 * 1000) + 100;
		$remainmilsec = $remainmilsec < 100 ? 100 : $remainmilsec;
		//calculate the values for the timer
		$hours_left = $remainmin > 59 ? floor($remainmin / 60) : 0;
		$minutes_left = $remainmin - ($hours_left * 60);
		$lbl_hour = JText::_('VBHOUR');
		$lbl_hours = JText::_('VBHOURS');
		$lbl_minute = JText::_('VBMINUTE');
		$lbl_minutes = JText::_('VBMINUTES');
		$timer_str = $hours_left > 0 ? '<span id="vbo-timer-hours">'.$hours_left.' '.($hours_left == 1 ? $lbl_hour : $lbl_hours).'</span> ' : '';
		$timer_str .= '<span id="vbo-timer-minutes">'.$minutes_left.' '.($minutes_left == 1 ? $lbl_minute : $lbl_minutes).'</span>';
		?>
	<script type="text/javascript">
	var vboPayTimerLbl = {
		"hour": "<?php echo addslashes($lbl_hour); ?>",
		"hours": "<?php echo addslashes($lbl_hours); ?>",
		"minute": "<?php echo addslashes($lbl_minute); ?>",
		"minutes": "<?php echo addslashes($lbl_minutes); ?>"
	}
	var vboPayTimeout = setTimeout(function() {
		document.location.href = '<?php echo JRoute::_('index.php?option=com_vikbooking&task=vieworder&sid='.$ord['sid'].'&ts='.$ord['ts'].(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>';
	}, <?php echo $remainmilsec; ?>);
	var vboPayInterval = setInterval("vboRefreshPayTimer()", 60000);
	var vboBookInfo = new Date(<?php echo $booktime_info['year']; ?>, <?php echo ($booktime_info['mon'] - 1); ?>, <?php echo $booktime_info['mday']; ?>, <?php echo $booktime_info['hours']; ?>, <?php echo $booktime_info['minutes']; ?>, <?php echo $booktime_info['seconds']; ?>, 0);
	var vboPayTimerOffsetSet = false;
	function vboPauseTimeout () {
		clearTimeout(vboPayTimeout);
	}
	function vboRefreshPayTimer () {
		var vboNow = new Date();
		if (!vboPayTimerOffsetSet) {
			var tzoffset = <?php echo $booktime_offset; ?> - vboNow.getTimezoneOffset();
			vboBookInfo.setMinutes(vboBookInfo.getMinutes() + tzoffset);
			vboPayTimerOffsetSet = true;
		}
		var mins_elapsed = Math.floor((vboNow - vboBookInfo) / 1000 / 60);
		var remainmin = <?php echo $minautoremove; ?> - mins_elapsed;
		var hours_left = remainmin > 59 ? Math.floor(remainmin / 60) : 0;
		var minutes_left = remainmin - (hours_left * 60);
		if (hours_left < 1 && minutes_left < 1) {
			clearInterval(vboPayInterval);
			if (document.getElementById('vbo-timer-payment')) {
				document.getElementById('vbo-timer-payment').style.display = 'none';
			}
			return false;
		}
		if (document.getElementById('vbo-timer-hours')) {
			if (hours_left < 1) {
				document.getElementById('vbo-timer-hours').style.display = 'none';
			} else {
				document.getElementById('vbo-timer-hours').innerText = hours_left+' '+(hours_left == 1 ? vboPayTimerLbl['hour'] : vboPayTimerLbl['hours']);
			}
		}
		document.getElementById('vbo-timer-minutes').innerText = minutes_left+' '+(minutes_left == 1 ? vboPayTimerLbl['minute'] : vboPayTimerLbl['minutes']);
	}
	</script>
	<div class="vbo-timer-payment" id="vbo-timer-payment">
		<span class="vbo-timer-payment-str">
			<?php echo JText::sprintf('VBOTIMERPAYMENTSTR', $timer_str); ?>
		</span>
	</div>
		<?php
	}
	//
	
	?>
	<div class="vbvordpaybutton">
	<?php	
	if ($totalchanged) {
		$chdecimals = $payment['charge'] - (int)$payment['charge'];
		?>
		<p class="vbpaymentchangetot">
			<span class="vbpaymentnamediff">
				<span><?php echo $payment['name']; ?></span>
				(<?php echo ($payment['ch_disc'] == 1 ? "+" : "-").($payment['val_pcent'] == 1 ? '<span class="vbo_currency">'.$currencysymb.'</span> ' : '').'<span class="vbo_price">'.VikBooking::numberFormat($payment['charge']).'</span>'.($payment['val_pcent'] == 1 ? '' : " %"); ?>) 
			</span>
			<span class="vborddiffpayment"><span class="vbo_currency"><?php echo $currencysymb; ?></span> <span class="vbo_price"><?php echo VikBooking::numberFormat($newtotaltopay); ?></span></span>
		</p>
		<?php
	}
	$obj = new vikBookingPayment($array_order, json_decode($payment['params'], true));
	$obj->showPayment();
	?>
	</div>
	<?php
}
?>