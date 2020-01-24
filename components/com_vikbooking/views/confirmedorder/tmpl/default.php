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
$ptmpl = VikRequest::getString('tmpl', '', 'request');
$pitemid = VikRequest::getInt('Itemid', '', 'request');

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
$isdue_orig = 0;
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
		$isdue_orig += $calctar;
		$pricenames[$num] = (!empty($or['pkg_name']) ? $or['pkg_name'] : (!empty($or['otarplan']) ? $or['otarplan'] : JText::_('VBOROOMCUSTRATEPLAN')));
	} elseif (array_key_exists($num, $tars) && is_array($tars[$num])) {
		$calctar = VikBooking::sayCostPlusIva($tars[$num]['cost'], $tars[$num]['idprice']);
		$tars[$num]['calctar'] = $calctar;
		$isdue += $calctar;
		$isdue_orig += array_key_exists('room_cost', $or) ? $or['room_cost'] : 0;
		$pricenames[$num] = VikBooking::getPriceName($tars[$num]['idprice'], $vbo_tn);
	}
	if (!empty ($or['optionals'])) {
		$stepo = explode(";", $or['optionals']);
		foreach ($stepo as $one) {
			if (!empty($one)) {
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
					$isdue_orig += $tmpopr;
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
			$isdue_orig += $ecplustax;
			$extraservices[$num] .= "<div><span class=\"vbo-booking-pricename\">".$ecv['name']."</span> <span class=\"vbo_currency\">" . $currencysymb . "</span> <span class=\"vbo_price\">" . VikBooking::numberFormat($ecplustax) . "</span></div>";
		}
	}
	//
}

$usedcoupon = false;
$origisdue = $isdue;
if(strlen($ord['coupon']) > 0) {
	$usedcoupon = true;
	$expcoupon = explode(";", $ord['coupon']);
	$isdue = $isdue - $expcoupon[1];
	$isdue_orig = $isdue_orig - $expcoupon[1];
}

//Check whether the booking total amount has changed due to rates modifications for these dates, made after this booking
$rooms_total_changed = number_format($isdue, 2) != number_format($ord['total'], 2) && number_format($origisdue, 2) != number_format($ord['total'], 2) ? true : false;
$only_roomsrates_changed = $rooms_total_changed === true && number_format($isdue_orig, 2) == number_format($ord['total'], 2) ? true : false;

//booking modification, cancellation and request
$resmodcanc = VikBooking::getReservationModCanc();
$resmodcanc = $this->days_to_arrival < 1 ? 0 : $resmodcanc;
$resmodcancmin = VikBooking::getReservationModCancMin();
$mod_allowed = ($resmodcanc > 1 && $resmodcanc != 3 && $this->days_to_arrival >= $resmodcancmin);
$canc_allowed = ($resmodcanc > 1 && $resmodcanc != 2 && $this->is_refundable > 0 && $this->daysadv_refund <= $this->days_to_arrival && $this->days_to_arrival >= $resmodcancmin);
//

$ts_info = getdate($ord['ts']);
$checkin_info = getdate($ord['checkin']);
$checkout_info = getdate($ord['checkout']);

//print button
if ($ptmpl != 'component') {
	?>
<div class="vbo-booking-print">
	<a class="vbo-booking-print-link" href="<?php echo JRoute::_('index.php?option=com_vikbooking&task=vieworder&sid='.$ord['sid'].'&ts='.$ord['ts'].'&tmpl=component'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" target="_blank" title="<?php echo JText::_('VBOPRINT'); ?>"><i class="fa fa-print"></i></a>
</div>
	<?php
}
//
?>

<h3 class="vbo-booking-details-intro"><?php echo JText::sprintf('VBOYOURBOOKCONFAT', VikBooking::getFrontTitle()); ?></h3>

<div class="vbo-booking-details-topcontainer">

	<div class="vbo-booking-details-head vbo-booking-details-head-confirmed">
		<h4><?php echo JText::_('VBOYOURBOOKISCONF'); ?></h4>
	</div>

	<div class="vbo-booking-details-midcontainer">

		<div class="vbo-booking-details-bookinfos">
			<span class="vbvordudatatitle"><?php echo JText::_('VBORDERDETAILS'); ?></span>
			<div class="vbo-booking-details-bookinfo">
				<span class="vbo-booking-details-bookinfo-lbl"><?php echo JText::_('VBORDEREDON'); ?></span>
				<span class="vbo-booking-details-bookinfo-val"><?php echo $wdays_map[$ts_info['wday']].', '.date(str_replace("/", $datesep, $df).' H:i', $ord['ts']); ?></span>
			</div>
			<div class="vbo-booking-details-bookinfo">
				<span class="vbo-booking-details-bookinfo-lbl"><?php echo JText::_('VBCONFIRMNUMB'); ?></span>
				<span class="vbo-booking-details-bookinfo-val"><?php echo $ord['confirmnumber']; ?></span>
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
	<?php
	//booking modification and/or cancellation or modification request
	if ($mod_allowed || $canc_allowed || ($resmodcanc === 1 && $this->days_to_arrival >= $resmodcancmin)) {
	?>
		<div class="vbo-booking-details-actions">
			<div class="vbo-booking-details-actions-inner">
			<?php
			if ($mod_allowed) {
				$start_itemid = VikBooking::findProperItemIdType(array('vikbooking', 'roomslist'));
				?>
				<div class="vbo-booking-mod-container">
					<div class="vbo-booking-mod-inner">
						<div class="vbo-booking-mod-cmd">
							<a onclick="return confirm('<?php echo addslashes(JText::_('VBOMODYOURBOOKINGCONF')); ?>');" href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=vikbooking&modify_sid='.$ord['sid'].'&modify_id='.$ord['id'].(!empty($pitemid) ? '&Itemid='.$pitemid : (!empty($start_itemid) ? '&Itemid='.$start_itemid : ''))); ?>"><i class="fa fa-edit"></i> <?php echo JText::_('VBOMODYOURBOOKING'); ?></a>
						</div>
					</div>
				</div>
				<?php
			}
			if ($canc_allowed) {
				?>
				<div class="vbo-booking-canc-container">
					<div class="vbo-booking-canc-inner">
						<div class="vbo-booking-canc-cmd">
							<span onclick="document.getElementById('vbo-booking-cancform-container').style.display='block';location.hash='bcancf';"><i class="fa fa-times-circle"></i> <?php echo JText::_('VBOCANCYOURBOOKING'); ?></span>
						</div>
					</div>
				</div>
				<?php
			}
			if ($resmodcanc === 1 && $this->days_to_arrival >= $resmodcancmin) {
				?>
				<div class="vbo-booking-mod-container">
					<div class="vbo-booking-mod-inner">
						<div class="vbo-booking-mod-cmd">
							<a onclick="vbOpenCancOrdForm();" href="javascript: void(0);"><i class="fa fa-envelope"></i> <?php echo JText::_('VBREQUESTCANCMOD'); ?></a>
						</div>
					</div>
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
					<span class="vbo_price"<?php echo $or['room_cost'] > 0 ? ' data-vborigprice="'.VikBooking::numberFormat($or['room_cost']).'"' : ''; ?>><?php echo VikBooking::numberFormat($tars[$num]['calctar']); ?></span>
				</span>
			</div>
			<?php
		}
		?>
		</div>
		
	<?php
	if ((array_key_exists($num, $optbought) && strlen($optbought[$num]) > 0) || (array_key_exists($num, $extraservices) && strlen($extraservices[$num]) > 0)) {
	?>
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
	<?php
	}
	?>
		
	</div>
	<?php
}
?>
</div>
	
	<div class="vbvordcosts">
		<?php if ($usedcoupon === true) { ?>
		<p class="vbvordcostsdiscount"><span class="vbvordcoststitle"><?php echo JText::_('VBCOUPON').' '.$expcoupon[2]; ?>:</span> - <span class="vbo_currency vbo_keepcost"><?php echo $currencysymb; ?></span> <span class="vbo_price vbo_keepcost"><?php echo VikBooking::numberFormat($expcoupon[1]); ?></span></p>
		<?php } ?>
		<p class="vbvordcoststot"><span class="vbvordcoststitle"><?php echo JText::_('VBTOTAL'); ?>:</span> <span class="vbo_currency vbo_keepcost"><?php echo $currencysymb; ?></span> <span class="vbo_price vbo_keepcost"><?php echo VikBooking::numberFormat($ord['total']); ?></span></p>
	</div>

	<?php
	if (is_array($payment) && intval($payment['shownotealw']) == 1) {
		if (strlen($payment['note']) > 0) {
			?>
			<div class="vbvordpaynote"><?php echo $payment['note']; ?></div>
			<?php
		}
	}
	if ($rooms_total_changed === true) {
		?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery(".vbo_price").not(".vbo_keepcost").each(function(k, v) {
			var origp = jQuery(this).attr('data-vborigprice');
			if(origp !== undefined) {
				jQuery(this).addClass("vbo_keepcost").text(origp).parent().find(".vbo_currency").addClass("vbo_keepcost");
			}else {
				<?php
				//if only the room rates changed but not the options, keep printing the prices
				echo !$only_roomsrates_changed ? 'jQuery(this).text("").parent().find(".vbo_currency").text("");' : 'jQuery(this).addClass("vbo_keepcost").parent().find(".vbo_currency").addClass("vbo_keepcost");';
				?>
			}
		});
		jQuery(".vbo_currency").not(".vbo_keepcost").each(function(){
			var cur_txt = jQuery(this).parent("span").html();
			if(cur_txt) {
				jQuery(this).parent("span").html(cur_txt.replace(":", ""));
			}else {
				var cur_txt = jQuery(this).parent("div").html();
				if(cur_txt) {
					jQuery(this).parent("div").html(cur_txt.replace(":", ""));
				}
			}
		});
	});
	</script>
		<?php
	}
	
	if (is_array($payment) && VikBooking::multiplePayments() && $ord['total'] > 0 && $ord['totpaid'] > 0.00 && $ord['totpaid'] < $ord['total'] && $ord['paymcount'] > 0) {
		//write again the payment form because the order was not fully paid
		require_once(VBO_ADMIN_PATH . DS . "payments" . DS . $payment['file']);
		$return_url = JURI::root() . "index.php?option=com_vikbooking&task=vieworder&sid=" . $ord['sid'] . "&ts=" . $ord['ts'];
		$error_url = JURI::root() . "index.php?option=com_vikbooking&task=vieworder&sid=" . $ord['sid'] . "&ts=" . $ord['ts'];
		$notify_url = JURI::root() . "index.php?option=com_vikbooking&task=notifypayment&sid=" . $ord['sid'] . "&ts=" . $ord['ts']."&tmpl=component";
		$transaction_name = VikBooking::getPaymentName();
		$remainingamount = $ord['total'] - $ord['totpaid'];
		$leave_deposit = 0;
		$percentdeposit = "";
		$array_order = array();
		$array_order['details'] = $ord;
		$array_order['customer_email'] = $ord['custmail'];
		$array_order['account_name'] = VikBooking::getPaypalAcc();
		$array_order['transaction_currency'] = VikBooking::getCurrencyCodePp();
		$array_order['rooms_name'] = implode(", ", $roomsnames);
		$array_order['transaction_name'] = !empty ($transaction_name) ? $transaction_name : implode(", ", $roomsnames);
		$array_order['order_total'] = $remainingamount;
		$array_order['currency_symb'] = $currencysymb;
		$array_order['net_price'] = $remainingamount;
		$array_order['tax'] = 0;
		$array_order['return_url'] = $return_url;
		$array_order['error_url'] = $error_url;
		$array_order['notify_url'] = $notify_url;
		$array_order['total_to_pay'] = $remainingamount;
		$array_order['total_net_price'] = $remainingamount;
		$array_order['total_tax'] = 0;
		$array_order['leave_deposit'] = $leave_deposit;
		$array_order['percentdeposit'] = $percentdeposit;
		$array_order['payment_info'] = $payment;
		
		?>
		<div class="vbvordcosts vbo-amount-paid-block">
			<p class="vbvordcoststot"><span class="vbvordcoststitle"><?php echo JText::_('VBAMOUNTPAID'); ?>:</span> <span class="vbo_currency vbo_keepcost"><?php echo $currencysymb; ?></span> <span class="vbo_price vbo_keepcost"><?php echo VikBooking::numberFormat($ord['totpaid']); ?></span></p>
		</div>
		<div class="vbvordcosts vbo-remaining-balance-block">
			<p class="vbvordcoststot"><span class="vbvordcoststitle"><?php echo JText::_('VBTOTALREMAINING'); ?>:</span> <span class="vbo_currency vbo_keepcost"><?php echo $currencysymb; ?></span> <span class="vbo_price vbo_keepcost"><?php echo VikBooking::numberFormat($remainingamount); ?></span></p>
		</div>
		<div class="vbvordpaybutton">
		<?php
		$obj = new vikBookingPayment($array_order, json_decode($payment['params'], true));
		$obj->showPayment();
		?>
		</div>
		<?php
	} else {
		if ($ptmpl != 'component') {
			if ($ord['total'] > 0 && $ord['totpaid'] > 0.00 && $ord['totpaid'] < $ord['total']) {
				$remainingamount = $ord['total'] - $ord['totpaid'];
			?>
		<div class="vbvordcosts vbo-amount-paid-block">
			<p class="vbvordcoststot"><span class="vbvordcoststitle"><?php echo JText::_('VBAMOUNTPAID'); ?>:</span> <span class="vbo_currency vbo_keepcost"><?php echo $currencysymb; ?></span> <span class="vbo_price vbo_keepcost"><?php echo VikBooking::numberFormat($ord['totpaid']); ?></span></p>
		</div>
		<div class="vbvordcosts vbo-remaining-balance-block">
			<p class="vbvordcoststot"><span class="vbvordcoststitle"><?php echo JText::_('VBTOTALREMAINING'); ?>:</span> <span class="vbo_currency vbo_keepcost"><?php echo $currencysymb; ?></span> <span class="vbo_price vbo_keepcost"><?php echo VikBooking::numberFormat($remainingamount); ?></span></p>
		</div>
			<?php
			}
		}
		if ($ptmpl == 'component') {
			?>
		<script type="text/javascript">
		window.print();
		</script>
			<?php
		}
	}
	//booking modification/cancellation request or cancellation form
	if ($resmodcanc > 0 && $this->days_to_arrival >= $resmodcancmin) {
		?>
		<script type="text/javascript">
		function vbOpenCancOrdForm() {
			location.hash = 'bmodreqf';
			document.getElementById('vbordcancformbox').style.display = 'block';
		}
		function vbValidateCancForm() {
			if (!document.getElementById('vbcancemail').value.match(/\S/)) {
				document.getElementById('vbformcancemail').style.color='#ff0000';
				return false;
			} else {
				document.getElementById('vbformcancemail').style.color='';
			}
			if (!document.getElementById('vbcancreason').value.match(/\S/)) {
				document.getElementById('vbformcancreason').style.color='#ff0000';
				return false;
			} else {
				document.getElementById('vbformcancreason').style.color='';
			}
			return true;
		}
		</script>
		<?php
	}
	if ($resmodcanc === 1 && $this->days_to_arrival >= $resmodcancmin) {
		?>
		<a name="bmodreqf"></a>
		<div class="vbordcancformbox" id="vbordcancformbox">
			<div class="vbo-booking-cancform-inner">
				<h4><?php echo JText::_('VBREQUESTCANCMOD'); ?></h4>
				<form action="<?php echo JRoute::_('index.php?option=com_vikbooking'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" name="vbcanc" method="post" onsubmit="javascript: return vbValidateCancForm();">
					<div class="vbordcancform-inner">
						<div class="vbordcancform-entry">
							<div class="vbordcancform-entry-label">
								<label for="vbcancemail" id="vbformcancemail"><?php echo JText::_('VBREQUESTCANCMODEMAIL'); ?></label>
							</div>
							<div class="vbordcancform-entry-inp">
								<input type="text" class="vbinput" name="email" id="vbcancemail" value="<?php echo $ord['custmail']; ?>"/>
							</div>
						</div>
						<div class="vbordcancform-entry">
							<div class="vbordcancform-entry-label">
								<label for="vbcancreason" id="vbformcancreason"><?php echo JText::_('VBREQUESTCANCMODREASON'); ?></label>
							</div>
							<div class="vbordcancform-entry-inp">
								<textarea name="reason" id="vbcancreason" rows="7" cols="30" class="vbtextarea"></textarea>
							</div>
						</div>
						<div class="vbordcancform-entry-submit">
							<input type="submit" name="sendrequest" value="<?php echo JText::_('VBREQUESTCANCMODSUBMIT'); ?>" class="btn"/>
						</div>
					</div>
				<?php
				if (!empty($pitemid)) {
					?>
					<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
					<?php
				}
				?>
					<input type="hidden" name="sid" value="<?php echo $ord['sid']; ?>"/>
					<input type="hidden" name="idorder" value="<?php echo $ord['id']; ?>"/>
					<input type="hidden" name="option" value="com_vikbooking"/>
					<input type="hidden" name="task" value="cancelrequest"/>
				</form>
			</div>
		</div>
		<?php
	}
	//booking cancellation
	if ($canc_allowed) {
		?>
		<a name="bcancf"></a>
		<div class="vbo-booking-cancform-container" id="vbo-booking-cancform-container" style="display: none;">
			<div class="vbo-booking-cancform-inner">
				<h4><?php echo JText::_('VBOCANCYOURBOOKING'); ?></h4>
				<div class="vbo-booking-cancform-details">
					<div class="vbo-booking-canc-details-policy">
						<?php echo $this->canc_policy; ?>
					</div>
					<form action="<?php echo JRoute::_('index.php?option=com_vikbooking'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" name="vbcanc" method="post" onsubmit="javascript: return vbValidateCancForm();">
						<div class="vbordcancform-inner vbo-booking-canc">
							<div class="vbordcancform-entry">
								<div class="vbordcancform-entry-label">
									<label for="vbcancemail" id="vbformcancemail"><?php echo JText::_('VBREQUESTCANCMODEMAIL'); ?></label>
								</div>
								<div class="vbordcancform-entry-inp">
									<input type="text" class="vbinput" name="email" id="vbcancemail" value="<?php echo $ord['custmail']; ?>"/>
								</div>
							</div>
							<div class="vbordcancform-entry">
								<div class="vbordcancform-entry-label">
									<label for="vbcancreason" id="vbformcancreason"><?php echo JText::_('VBOCANCBOOKINGREASON'); ?></label>
								</div>
								<div class="vbordcancform-entry-inp">
									<textarea name="reason" id="vbcancreason" rows="7" cols="30" class="vbtextarea"></textarea>
								</div>
							</div>
							<div class="vbo-booking-canc-submit">
								<input type="submit" name="sendrequest" value="<?php echo JText::_('VBOCANCYOURBOOKING'); ?>" class="vbo-btn-cancelbooking"/>
							</div>
						</div>
					<?php
					if (!empty($pitemid)) {
						?>
						<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
						<?php
					}
					?>
						<input type="hidden" name="sid" value="<?php echo $ord['sid']; ?>"/>
						<input type="hidden" name="idorder" value="<?php echo $ord['id']; ?>"/>
						<input type="hidden" name="option" value="com_vikbooking"/>
						<input type="hidden" name="task" value="docancelbooking"/>
					</form>
				</div>
			</div>
		</div>
		<?php
	}