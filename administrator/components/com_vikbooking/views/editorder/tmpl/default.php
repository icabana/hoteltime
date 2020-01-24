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

$row = $this->row;
$rooms = $this->rooms;
$busy = $this->busy;
$customer = $this->customer;
$payments = $this->payments;

$vbo_auth_bookings = JFactory::getUser()->authorise('core.vbo.bookings', 'com_vikbooking');
$currencyname = VikBooking::getCurrencyName();
$dbo = JFactory::getDBO();
$vbo_app = new VboApplication();
$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator(true);
$vbo_modals_html = array();
$payment = VikBooking::getPayment($row['idpayment']);
$pactive_tab = VikRequest::getString('vbo_active_tab', 'vbo-tab-details', 'request');
$printreceipt = VikRequest::getInt('print', 0, 'request');
$printreceipt = ($printreceipt > 0);
if ($printreceipt) {
	//we set a different page title when printing the receipt for the "PDF Printer" to give the file a good name.
	JFactory::getDocument()->setTitle(VikBooking::getFrontTitle()." - ".JText::_('VBOFISCRECEIPT')." #".$row['id']);
}
$tars = array();
$arrpeople = array();
$is_package = !empty($row['pkg']) ? true : false;
$is_cust_cost = false;
foreach ($rooms as $ind => $or) {
	$num = $ind + 1;
	$arrpeople[$num]['adults'] = $or['adults'];
	$arrpeople[$num]['children'] = $or['children'];
	$arrpeople[$num]['children_age'] = $or['childrenage'];
	$arrpeople[$num]['t_first_name'] = $or['t_first_name'];
	$arrpeople[$num]['t_last_name'] = $or['t_last_name'];
	if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
		//package or custom cost set from the back-end
		$is_cust_cost = true;
		continue;
	}
	$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `id`='".$or['idtar']."';";
	$dbo->setQuery($q);
	$dbo->execute();
	$tar=$dbo->loadAssocList();
	$tar = VikBooking::applySeasonsRoom($tar, $row['checkin'], $row['checkout']);
	//different usage
	if ($or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
		$diffusageprice = VikBooking::loadAdultsDiff($or['idroom'], $or['adults']);
		//Occupancy Override
		$occ_ovr = VikBooking::occupancyOverrideExists($tar, $or['adults']);
		$diffusageprice = $occ_ovr !== false ? $occ_ovr : $diffusageprice;
		//
		if (is_array($diffusageprice)) {
			//set a charge or discount to the price(s) for the different usage of the room
			foreach ($tar as $kpr => $vpr) {
				$tar[$kpr]['diffusage'] = $or['adults'];
				if ($diffusageprice['chdisc'] == 1) {
					//charge
					if ($diffusageprice['valpcent'] == 1) {
						//fixed value
						$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
						$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
						$tar[$kpr]['diffusagecost'] = "+".$aduseval;
						$tar[$kpr]['room_base_cost'] = $vpr['cost'];
						$tar[$kpr]['cost'] = $vpr['cost'] + $aduseval;
					} else {
						//percentage value
						$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
						$aduseval = $diffusageprice['pernight'] == 1 ? round(($vpr['cost'] * $diffusageprice['value'] / 100) * $tar[$kpr]['days'] + $vpr['cost'], 2) : round(($vpr['cost'] * (100 + $diffusageprice['value']) / 100), 2);
						$tar[$kpr]['diffusagecost'] = "+".$diffusageprice['value']."%";
						$tar[$kpr]['room_base_cost'] = $vpr['cost'];
						$tar[$kpr]['cost'] = $aduseval;
					}
				} else {
					//discount
					if ($diffusageprice['valpcent'] == 1) {
						//fixed value
						$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
						$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
						$tar[$kpr]['diffusagecost'] = "-".$aduseval;
						$tar[$kpr]['room_base_cost'] = $vpr['cost'];
						$tar[$kpr]['cost'] = $vpr['cost'] - $aduseval;
					} else {
						//percentage value
						$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
						$aduseval = $diffusageprice['pernight'] == 1 ? round($vpr['cost'] - ((($vpr['cost'] / $tar[$kpr]['days']) * $diffusageprice['value'] / 100) * $tar[$kpr]['days']), 2) : round(($vpr['cost'] * (100 - $diffusageprice['value']) / 100), 2);
						$tar[$kpr]['diffusagecost'] = "-".$diffusageprice['value']."%";
						$tar[$kpr]['room_base_cost'] = $vpr['cost'];
						$tar[$kpr]['cost'] = $aduseval;
					}
				}
			}
		}
	}
	//
	$tars[$num] = $tar;
}
$pcheckin = $row['checkin'];
$pcheckout = $row['checkout'];
$secdiff = $pcheckout - $pcheckin;
$daysdiff = $secdiff / 86400;
if (is_int($daysdiff)) {
	if ($daysdiff < 1) {
		$daysdiff = 1;
	}
} else {
	if ($daysdiff < 1) {
		$daysdiff = 1;
	} else {
		$sum = floor($daysdiff) * 86400;
		$newdiff = $secdiff - $sum;
		$maxhmore = VikBooking::getHoursMoreRb() * 3600;
		if ($maxhmore >= $newdiff) {
			$daysdiff = floor($daysdiff);
		} else {
			$daysdiff = ceil($daysdiff);
		}
	}
}
$otachannel = '';
$otachannel_name = '';
$otachannel_bid = '';
$otacurrency = '';
if (!empty($row['channel'])) {
	$channelparts = explode('_', $row['channel']);
	$otachannel = array_key_exists(1, $channelparts) && strlen($channelparts[1]) > 0 ? $channelparts[1] : ucwords($channelparts[0]);
	$otachannel_name = $otachannel;
	$otachannel_bid = $otachannel.(!empty($row['idorderota']) ? ' - Booking ID: '.$row['idorderota'] : '');
	if (strstr($otachannel, '.') !== false) {
		$otaccparts = explode('.', $otachannel);
		$otachannel = $otaccparts[0];
	}
	$otacurrency = strlen($row['chcurrency']) > 0 ? $row['chcurrency'] : '';
}
if ($row['status'] == "confirmed") {
	$saystaus = '<span class="label label-success">'.JText::_('VBCONFIRMED').'</span>';
} elseif ($row['status']=="standby") {
	$saystaus = '<span class="label label-warning">'.JText::_('VBSTANDBY').'</span>';
} else {
	$saystaus = '<span class="label label-error" style="background-color: #d9534f;">'.JText::_('VBCANCELLED').'</span>';
}
//Prepare modal (used for the Registration and for reconstructing the credit card details through the channel manager)
echo $vbo_app->getJmodalScript();
//end Prepare modal
?>
<script type="text/javascript">
function vbToggleLog(elem) {
	var logdiv = document.getElementById('vbpaymentlogdiv').style.display;
	if (logdiv == 'block') {
		document.getElementById('vbpaymentlogdiv').style.display = 'none';
		jQuery(elem).parent(".vbo-bookingdet-noteslogs-btn").removeClass("vbo-bookingdet-noteslogs-btn-active");
	} else {
		jQuery(".vbo-bookingdet-noteslogs-btn-active").removeClass("vbo-bookingdet-noteslogs-btn-active");
		if (document.getElementById('vbhistorydiv')) {
			document.getElementById('vbhistorydiv').style.display = 'none';
		}
		document.getElementById('vbadminnotesdiv').style.display = 'none';
		document.getElementById('vbinvnotesdiv').style.display = 'none';
		document.getElementById('vbpaymentlogdiv').style.display = 'block';
		jQuery(elem).parent(".vbo-bookingdet-noteslogs-btn").addClass("vbo-bookingdet-noteslogs-btn-active");
	}
}
function changePayment() {
	var newpayment = document.getElementById('newpayment').value;
	if (newpayment != '') {
		var paymentname = document.getElementById('newpayment').options[document.getElementById('newpayment').selectedIndex].text;
		if (confirm('<?php echo addslashes(JText::_('VBCHANGEPAYCONFIRM')); ?>' + paymentname + '?')) {
			document.adminForm.submit();
		} else {
			document.getElementById('newpayment').selectedIndex = 0;
		}
	}
}
function vbToggleNotes(elem) {
	var notesdiv = document.getElementById('vbadminnotesdiv').style.display;
	if (notesdiv == 'block') {
		document.getElementById('vbadminnotesdiv').style.display = 'none';
		jQuery(elem).parent(".vbo-bookingdet-noteslogs-btn").removeClass("vbo-bookingdet-noteslogs-btn-active");
	} else {
		jQuery(".vbo-bookingdet-noteslogs-btn-active").removeClass("vbo-bookingdet-noteslogs-btn-active");
		if (document.getElementById('vbpaymentlogdiv')) {
			document.getElementById('vbpaymentlogdiv').style.display = 'none';
		}
		if (document.getElementById('vbhistorydiv')) {
			document.getElementById('vbhistorydiv').style.display = 'none';
		}
		document.getElementById('vbinvnotesdiv').style.display = 'none';
		document.getElementById('vbadminnotesdiv').style.display = 'block';
		jQuery(elem).parent(".vbo-bookingdet-noteslogs-btn").addClass("vbo-bookingdet-noteslogs-btn-active");
	}
}
function vbToggleHistory(elem) {
	var historydiv = document.getElementById('vbhistorydiv').style.display;
	if (historydiv == 'block') {
		document.getElementById('vbhistorydiv').style.display = 'none';
		jQuery(elem).parent(".vbo-bookingdet-noteslogs-btn").removeClass("vbo-bookingdet-noteslogs-btn-active");
	} else {
		jQuery(".vbo-bookingdet-noteslogs-btn-active").removeClass("vbo-bookingdet-noteslogs-btn-active");
		if (document.getElementById('vbpaymentlogdiv')) {
			document.getElementById('vbpaymentlogdiv').style.display = 'none';
		}
		document.getElementById('vbinvnotesdiv').style.display = 'none';
		document.getElementById('vbadminnotesdiv').style.display = 'none';
		document.getElementById('vbhistorydiv').style.display = 'block';
		jQuery(elem).parent(".vbo-bookingdet-noteslogs-btn").addClass("vbo-bookingdet-noteslogs-btn-active");
	}
}
function vbToggleInvNotes(elem) {
	var invnotesdiv = document.getElementById('vbinvnotesdiv').style.display;
	if (invnotesdiv == 'block') {
		document.getElementById('vbinvnotesdiv').style.display = 'none';
		jQuery(elem).parent(".vbo-bookingdet-noteslogs-btn").removeClass("vbo-bookingdet-noteslogs-btn-active");
	} else {
		jQuery(".vbo-bookingdet-noteslogs-btn-active").removeClass("vbo-bookingdet-noteslogs-btn-active");
		if (document.getElementById('vbpaymentlogdiv')) {
			document.getElementById('vbpaymentlogdiv').style.display = 'none';
		}
		if (document.getElementById('vbhistorydiv')) {
			document.getElementById('vbhistorydiv').style.display = 'none';
		}
		document.getElementById('vbadminnotesdiv').style.display = 'none';
		document.getElementById('vbinvnotesdiv').style.display = 'block';
		jQuery(elem).parent(".vbo-bookingdet-noteslogs-btn").addClass("vbo-bookingdet-noteslogs-btn-active");
	}
}
function toggleDiscount(elem) {
	var discsp = document.getElementById('vbdiscenter').style.display;
	if (discsp == 'block') {
		document.getElementById('vbdiscenter').style.display = 'none';
		jQuery(elem).find('i').removeClass("fa-chevron-up").addClass("fa-chevron-down");
	} else {
		document.getElementById('vbdiscenter').style.display = 'block';
		jQuery(elem).find('i').removeClass("fa-chevron-down").addClass("fa-chevron-up");
	}
}
</script>

<div class="vbo-bookingdet-topcontainer">
	<form name="adminForm" id="adminForm" action="index.php" method="post">
	<?php
	if ($printreceipt) {
		//print the company details
		$companylogo = VikBooking::getSiteLogo();
		$next_receipt = VikBooking::getNextReceiptNumber($row['id']);
		?>
		<div class="vbo-receipt-company-block-outer">
			<div class="vbo-receipt-company-block">
			<?php
			if (!empty($companylogo)) {
				?>
				<div class="vbo-receipt-company-logo"><img src="<?php echo VBO_ADMIN_URI.'resources/'.$companylogo; ?>" /></div>
				<?php
			}
			?>
				<div class="vbo-receipt-company-info"><?php echo VikBooking::getInvoiceCompanyInfo(); ?></div>
			</div>
			<div class="vbo-receipt-numdate-block">
				<div class="vbo-receipt-numdate-inner">
					<div class="vbo-receipt-numdate-title">
						<span><?php echo JText::_('VBOFISCRECEIPT'); ?></span>
						<span style="float: right; cursor: pointer; color: #ff0000;" onclick="vboMakePrintOnly();"><i class="fa fa-times-circle"></i></span>
					</div>
					<div class="vbo-receipt-numdate-num">
						<span class="vbo-receipt-numdate-num-lbl"><?php echo JText::_('VBOFISCRECEIPTNUM'); ?></span>
						<span class="vbo-receipt-numdate-num-val">
							<span class="vbo-showin-print" id="vbo-receipt-num"><?php echo $next_receipt; ?></span>
							<input class="vbo-hidein-print" id="vbo-receipt-num-inp" type="number" min="0" value="<?php echo $next_receipt; ?>" onchange="document.getElementById('vbo-receipt-num').innerText = this.value;" />
						</span>
					</div>
					<div class="vbo-receipt-numdate-date">
						<span class="vbo-receipt-numdate-date-lbl"><?php echo JText::_('VBOFISCRECEIPTDATE'); ?></span>
						<span class="vbo-receipt-numdate-date-val"><?php echo date(str_replace("/", $datesep, $df)); ?></span>
					</div>
				</div>
			</div>
			<div class="vbo-receipt-print-confirm vbo-hidein-print">
				<div class="vbo-receipt-print-btn">
					<span onclick="vboLaunchPrintReceipt();">
						<i class="fa fa-print"></i> 
						<span id="vbo-receipt-print-btn-name"><?php echo JText::_('VBOPRINTRECEIPT'); ?></span>
					</span>
				</div>
			</div>
		</div>
		<?php
	}
	?>
		
		<div class="vbo-bookdet-container">
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span>ID</span>
				</div>
				<div class="vbo-bookdet-foot">
					<span><?php echo $row['id']; ?></span>
				</div>
			</div>
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span><?php echo JText::_('VBEDITORDERONE'); ?></span>
				</div>
				<div class="vbo-bookdet-foot">
					<span><?php echo date(str_replace("/", $datesep, $df).' H:i', $row['ts']); ?></span>
				</div>
			</div>
		<?php
		if (!$printreceipt && count($customer)) {
		?>
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span><?php echo JText::_('VBCUSTOMERNOMINATIVE'); ?></span>
				</div>
				<div class="vbo-bookdet-foot">
					<?php echo (isset($customer['country_img']) ? $customer['country_img'].' ' : '').'<a href="index.php?option=com_vikbooking&task=editcustomer&cid[]='.$customer['id'].'" target="_blank">'.ltrim($customer['first_name'].' '.$customer['last_name']).'</a>'; ?>
				</div>
			</div>
		<?php
		}
		if (!$printreceipt) {
		?>
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span><?php echo JText::_('VBEDITORDERROOMSNUM'); ?></span>
				</div>
				<div class="vbo-bookdet-foot">
					<?php echo $row['roomsnum']; ?>
				</div>
			</div>
			<?php
		}
		?>
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span><?php echo JText::_('VBEDITORDERFOUR'); ?></span>
				</div>
				<div class="vbo-bookdet-foot">
					<?php echo $row['days']; ?>
				</div>
			</div>
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span><?php echo JText::_('VBEDITORDERFIVE'); ?></span>
				</div>
				<div class="vbo-bookdet-foot">
				<?php
				$checkin_info = getdate($row['checkin']);
				$short_wday = JText::_('VB'.strtoupper(substr($checkin_info['weekday'], 0, 3)));
				?>
					<?php echo $short_wday.', '.date(str_replace("/", $datesep, $df).' H:i', $row['checkin']); ?>
				</div>
			</div>
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span><?php echo JText::_('VBEDITORDERSIX'); ?></span>
				</div>
				<div class="vbo-bookdet-foot">
				<?php
				$checkout_info = getdate($row['checkout']);
				$short_wday = JText::_('VB'.strtoupper(substr($checkout_info['weekday'], 0, 3)));
				?>
					<?php echo $short_wday.', '.date(str_replace("/", $datesep, $df).' H:i', $row['checkout']); ?>
				</div>
			</div>
		<?php
		if (!$printreceipt && $vbo_auth_bookings && $row['closure'] != 1 && $row['status'] == 'confirmed') {
			//we don't need to check in the IF above "&& $row['checked'] != 0" because the registration is useful for all bookings.
			switch ($row['checked']) {
				case -1:
					$checked_status = JText::_('VBOCHECKEDSTATUSNOS');
					break;
				case 1:
					$checked_status = JText::_('VBOCHECKEDSTATUSIN');
					break;
				case 2:
					$checked_status = JText::_('VBOCHECKEDSTATUSOUT');
					break;
				default:
					$checked_status = JText::_('VBOCHECKEDSTATUSZERO');
					break;
			}
			?>
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span><?php echo JText::_('VBOCHECKEDSTATUS'); ?></span>
				</div>
				<div class="vbo-bookdet-foot">
					<button type="button" class="btn btn-small <?php echo $row['checked'] == -1 ? 'btn-danger' : 'btn-primary'; ?>" onclick="vboOpenJModal('vbo-checkin-booking', 'index.php?option=com_vikbooking&task=bookingcheckin&cid[]=<?php echo $row['id']; ?>&tmpl=component');">
						<?php echo $checked_status; ?>
					</button>
				</div>
			</div>
		<?php
			//Prepare modal (Registration)
			array_push($vbo_modals_html, $vbo_app->getJmodalHtml('vbo-checkin-booking', JText::_('VBOMANAGECHECKSINOUT')));
			//end Prepare modal
		}
		if (!$printreceipt && !empty($row['channel'])) {
			$ota_logo_img = VikBooking::getVcmChannelsLogo($otachannel_name);
			if ($ota_logo_img === false) {
				$ota_logo_img = $otachannel_name;
			} else {
				$ota_logo_img = '<img src="'.$ota_logo_img.'" class="vbo-channelimg-medium"/>';
			}
			?>
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span><?php echo JText::_('VBPVIEWORDERCHANNEL'); ?></span>
				</div>
				<div class="vbo-bookdet-foot">
					<span><?php echo $ota_logo_img; ?></span>
				</div>
			</div>
			<?php
		}
		if (!$printreceipt) {
		?>
			<div class="vbo-bookdet-wrap">
				<div class="vbo-bookdet-head">
					<span><?php echo JText::_('VBSTATUS'); ?></span>
				</div>
				<div class="vbo-bookdet-foot">
					<span><?php echo $saystaus; ?></span>
				</div>
			</div>
			<?php
		}
		?>
		</div>

		<div class="vbo-bookingdet-innertop">
			<div class="vbo-bookingdet-commands">
			<?php
			if (is_array($busy) || $row['status']=="standby") {
				?>
				<div class="vbo-bookingdet-command">
					<button onclick="document.location.href='index.php?option=com_vikbooking&task=editbusy&cid[]=<?php echo $row['id']; ?>';" class="btn btn-secondary" type="button"><i class="icon-pencil"></i> <?php echo JText::_('VBMODRES'); ?></button>
				</div>
				<?php
			}
			if ((array_key_exists(1, $tars) && count($tars[1]) > 0) || ($is_package || $is_cust_cost)) {
				?>
				<div class="vbo-bookingdet-command">
					<button onclick="window.open('<?php echo JURI::root(); ?>index.php?option=com_vikbooking&task=vieworder&sid=<?php echo $row['sid']; ?>&ts=<?php echo $row['ts']; ?>', '_blank');" type="button" class="btn btn-secondary"><i class="icon-eye"></i> <?php echo JText::_('VBVIEWORDFRONT'); ?></button>
				</div>
				<?php
			}
			if (($row['status'] == "confirmed" || ($row['status'] == "standby" && !empty($row['custmail']))) && ((array_key_exists(1, $tars) && count($tars[1]) > 0) || ($is_package || $is_cust_cost))) {
				?>
				<div class="vbo-bookingdet-command">
					<button class="btn btn-success" type="button" onclick="document.location.href='index.php?option=com_vikbooking&task=resendordemail&cid[]=<?php echo $row['id']; ?>';"><i class="icon-mail"></i> <?php echo JText::_('VBRESENDORDEMAIL'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] == "standby") {
				?>
				<div class="vbo-bookingdet-command">
					<button class="btn btn-success" type="button" onclick="if (confirm('<?php echo addslashes(JText::_('VBSETORDCONFIRMED')); ?> ?')) {document.location.href='index.php?option=com_vikbooking&task=setordconfirmed&cid[]=<?php echo $row['id']; ?>';}"><i class="vboicn-checkmark"></i> <?php echo JText::_('VBSETORDCONFIRMED'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] == "cancelled" && !empty($row['custmail'])) {
				?>
				<div class="vbo-bookingdet-command">
					<button class="btn btn-primary" type="button" onclick="document.location.href='index.php?option=com_vikbooking&task=sendcancordemail&cid[]=<?php echo $row['id']; ?>';"><i class="icon-mail"></i> <?php echo JText::_('VBSENDCANCORDEMAIL'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] == 'confirmed' && $row['closure'] < 1) {
				?>
				<div class="vbo-bookingdet-command">
					<button type="button" class="btn btn-secondary" onclick="document.getElementById('invnotes-hid').value=document.getElementById('invnotes').value;document.getElementById('vbo-gen-invoice').submit();"><i class="fa fa-file-text-o"></i> <?php echo JText::_('VBOGENBOOKINGINVOICE'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] == 'confirmed' && $row['closure'] < 1) {
				?>
				<div class="vbo-bookingdet-command">
					<button type="button" class="btn btn-secondary" onclick="window.open('index.php?option=com_vikbooking&task=editorder&cid[]=<?php echo $row['id']; ?>&print=1&tmpl=component', '_blank');"><i class="fa fa-print"></i> <?php echo JText::_('VBOPRINTRECEIPT'); ?></button>
				</div>
				<?php
			}
			if ($row['status'] != 'confirmed' || $row['closure'] > 0) {
				?>
				<div class="vbo-bookingdet-command">
					<button type="button" class="btn btn-danger" onclick="if (confirm('<?php echo addslashes(JText::_('VBDELCONFIRM')); ?>')){document.location.href='index.php?option=com_vikbooking&task=removeorders&cid[]=<?php echo $row['id']; ?>';}"><i class="vboicn-bin"></i> <?php echo JText::_('VBMAINEBUSYDEL'); ?></button>
				</div>
				<?php
			}
			?>
			</div>

			<div class="vbo-bookingdet-tabs">
				<div class="vbo-bookingdet-tab vbo-bookingdet-tab-active" data-vbotab="vbo-tab-details"><?php echo JText::_('VBOBOOKDETTABDETAILS'); ?></div>
				<div class="vbo-bookingdet-tab" data-vbotab="vbo-tab-admin"><?php echo JText::_('VBOBOOKDETTABADMIN'); ?></div>
			</div>
		</div>

		<div class="vbo-bookingdet-tab-cont" id="vbo-tab-details" style="display: block;">
			<div class="vbo-bookingdet-innercontainer">
				<div class="vbo-bookingdet-customer">
					<div class="vbo-bookingdet-detcont<?php echo $row['closure'] > 0 ? ' vbo-bookingdet-closure' : ''; ?>">
					<?php
					$custdata_parts = explode("\n", $row['custdata']);
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
						if ($row['closure'] > 0) {
							?>
						<div class="vbo-bookingdet-userdetail">
							<span class="vbo-bookingdet-userdetail-val"><?php echo nl2br($row['custdata']); ?></span>
						</div>
							<?php
						} else {
							echo nl2br($row['custdata']);
							?>
						<div class="vbo-bookingdet-userdetail">
							<span class="vbo-bookingdet-userdetail-val">&nbsp;</span>
						</div>
							<?php
						}
					}
					if (!empty($row['ujid'])) {
						$orig_user = JFactory::getUser($row['ujid']);
						$author_name = is_object($orig_user) && property_exists($orig_user, 'name') && !empty($orig_user->name) ? $orig_user->name : '';
						?>
						<div class="vbo-bookingdet-userdetail">
							<span class="vbo-bookingdet-userdetail-val"><?php echo JText::sprintf('VBOBOOKINGCREATEDBY', $row['ujid'].(!empty($author_name) ? ' ('.$author_name.')' : '')); ?></span>
						</div>
						<?php
					}
					?>
					</div>
				<?php
				$invoiced = file_exists(VBO_SITE_PATH.DS.'helpers'.DS.'invoices'.DS.'generated'.DS.$row['id'].'_'.$row['sid'].'.pdf');
				if (!$printreceipt && ((!empty($row['channel']) && !empty($row['idorderota'])) || strlen($row['confirmnumber']) > 0 || $invoiced)) {
					?>
					<div class="vbo-bookingdet-detcont vbo-hidein-print">
					<?php
					if (!empty($row['channel']) && !empty($row['idorderota'])) {
						?>
						<div>
							<span class="label label-info"><?php echo $otachannel_name.' ID'; ?> <span class="badge"><?php echo $row['idorderota']; ?></span></span>
						</div>
						<?php
					}
					if (strlen($row['confirmnumber']) > 0) {
						?>
						<div>
							<span class="label label-success"><?php echo JText::_('VBCONFIRMNUMB'); ?> <span class="badge"><?php echo $row['confirmnumber']; ?></span></span>
						</div>
						<?php
					}
					if ($invoiced) {
						?>
						<div>
							<span class="label label-success"><?php echo JText::_('VBOCOLORTAGRULEINVONE'); ?> <span class="badge"><a href="<?php echo VBO_SITE_URI; ?>helpers/invoices/generated/<?php echo $row['id'].'_'.$row['sid']; ?>.pdf" target="_blank"><i class="vboicn-file-text2"></i><?php echo JText::_('VBOINVDOWNLOAD'); ?></a></span></span>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				}
				if (!$printreceipt && $row['closure'] < 1) {
				?>
					<div class="vbo-bookingdet-detcont vbo-hidein-print">
						<label for="custmail"><?php echo JText::_('VBCUSTEMAIL'); ?></label>
						<input type="text" name="custmail" id="custmail" value="<?php echo $row['custmail']; ?>" size="25"/>
						<?php if (!empty($row['custmail'])) : ?> <button type="button" class="btn btn-secondary" onclick="vboToggleSendEmail();" style="vertical-align: top;"><i class="vboicn-envelop"></i><?php echo JText::_('VBSENDEMAILACTION'); ?></button><?php endif; ?>
					</div>
					<div class="vbo-bookingdet-detcont vbo-hidein-print">
						<label for="custphone"><?php echo JText::_('VBCUSTOMERPHONE'); ?></label>
						<input type="text" name="custphone" id="custphone" value="<?php echo $row['phone']; ?>" size="25"/>
						<?php if (!empty($row['phone'])) : ?> <button type="button" class="btn btn-secondary" onclick="vboToggleSendSMS();" style="vertical-align: top;"><i class="vboicn-bubble"></i><?php echo JText::_('VBSENDSMSACTION'); ?></button><?php endif; ?>
					</div>
				<?php
				}
				?>
				</div>

				<?php
				$isdue = 0;
				$all_id_prices = array();
				$used_indexes_map = array();
				?>
				<div class="vbo-bookingdet-summary">
					<div class="table-responsive">
						<table class="table">
						<?php
						foreach ($rooms as $ind => $or) {
							$num = $ind + 1;
							if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
								//package cost or cust_cost should always be inclusive of taxes
								$isdue += $or['cust_cost'];
							} else {
								$isdue += isset($tars[$num]) && isset($tars[$num][0]) ? VikBooking::sayCostPlusIva($tars[$num][0]['cost'], $tars[$num][0]['idprice']) : 0;
							}
							?>
							<tr class="vbo-bookingdet-summary-room">
								<td class="vbo-bookingdet-summary-room-firstcell">
									<div class="vbo-bookingdet-summary-roomnum"><i class="fa fa-bed"></i> <?php echo JText::_('VBEDITORDERTHREE').' '.$num; ?></div>
								<?php
								//Room Specific Unit
								if ($row['status'] == "confirmed" && !empty($or['params'])) {
									$room_params = json_decode($or['params'], true);
									$arr_features = array();
									$unavailable_indexes = VikBooking::getRoomUnitNumsUnavailable($row, $or['idroom']);
									if (is_array($room_params) && array_key_exists('features', $room_params) && @count($room_params['features']) > 0) {
										foreach ($room_params['features'] as $rind => $rfeatures) {
											if (in_array($rind, $unavailable_indexes) || (isset($used_indexes_map[$or['idroom']]) && in_array($rind, $used_indexes_map[$or['idroom']]))) {
												continue;
											}
											foreach ($rfeatures as $fname => $fval) {
												if (strlen($fval)) {
													$arr_features[$rind] = '#'.$rind.' - '.JText::_($fname).': '.$fval;
													break;
												}
											}
										}
									}
									if (count($arr_features) > 0) {
										//$or['id'] equals to the ID of each matching record in _ordersrooms
										//echo $vbo_app->getDropDown($arr_features, $or['roomindex'], JText::_('VBOFEATASSIGNUNITEMPTY'), JText::_('VBOFEATASSIGNUNIT'), 'roomindex['.$or['id'].']', $or['id']).'<br/>';
										?>
									<div class="vbo-bookingdet-summary-roomnum-chunit">
										<?php echo !$printreceipt ? $vbo_app->getNiceSelect($arr_features, $or['roomindex'], 'roomindex['.$or['id'].']', JText::_('VBOFEATASSIGNUNIT'), JText::_('VBOFEATASSIGNUNITEMPTY'), '', 'document.adminForm.submit();', $or['id']) : (!empty($or['roomindex']) && isset($arr_features[$or['roomindex']]) ? $arr_features[$or['roomindex']] : ''); ?>
									</div>
										<?php
										if (!empty($or['idroom']) && !empty($or['roomindex'])) {
											if (!array_key_exists($or['idroom'], $used_indexes_map)) {
												$used_indexes_map[$or['idroom']] = array();
											}
											$used_indexes_map[$or['idroom']][] = $or['roomindex'];
										}
									}
								}
								//
								?>
									<div class="vbo-bookingdet-summary-roomguests">
										<i class="fa fa-male"></i>
										<div class="vbo-bookingdet-summary-roomadults">
											<span><?php echo JText::_('VBEDITORDERADULTS'); ?>:</span> <?php echo $arrpeople[$num]['adults']; ?>
										</div>
									<?php
									if ($arrpeople[$num]['children'] > 0) {
										$age_str = '';
										if (!empty($arrpeople[$num]['children_age'])) {
											$json_child = json_decode($arrpeople[$num]['children_age'], true);
											if (@is_array($json_child['age']) && @count($json_child['age']) > 0) {
												$age_str = ' '.JText::sprintf('VBORDERCHILDAGES', implode(', ', $json_child['age']));
											}
										}
										?>
										<div class="vbo-bookingdet-summary-roomchildren">
											<span><?php echo JText::_('VBEDITORDERCHILDREN'); ?>:</span> <?php echo $arrpeople[$num]['children'].$age_str; ?>
										</div>
										<?php
									}
									?>
									</div>
									<?php
									if (!empty($arrpeople[$num]['t_first_name'])) {
									?>
									<div class="vbo-bookingdet-summary-guestname">
										<span><?php echo $arrpeople[$num]['t_first_name'].' '.$arrpeople[$num]['t_last_name']; ?></span>
									</div>
									<?php
									}
									?>
								</td>
								<td>
									<div class="vbo-bookingdet-summary-roomname"><?php echo $or['name']; ?></div>
									<div class="vbo-bookingdet-summary-roomrate">
									<?php
									if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
										if (!empty($or['pkg_name'])) {
											//package
											echo $or['pkg_name'];
										} else {
											//custom cost can have an OTA Rate Plan name
											if (!empty($or['otarplan'])) {
												echo ucwords($or['otarplan']);
											} else {
												echo JText::_('VBOROOMCUSTRATEPLAN');
											}
										}
									} elseif (array_key_exists($num, $tars) && !empty($tars[$num][0]['idprice'])) {
										$all_id_prices[] = $tars[$num][0]['idprice'];
										echo VikBooking::getPriceName($tars[$num][0]['idprice']);
										if (!empty($tars[$num][0]['attrdata'])) {
											?>
										<div>
											<?php echo VikBooking::getPriceAttr($tars[$num][0]['idprice']).": ".$tars[$num][0]['attrdata']; ?>
										</div>
											<?php
										}
									} elseif (!empty($or['otarplan'])) {
										echo ucwords($or['otarplan']);
									} elseif ($row['closure'] < 1) {
										echo JText::_('VBOROOMNORATE');
									}
									?>
									</div>
								</td>
								<td>
									<div class="vbo-bookingdet-summary-price">
									<?php
									if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
										echo $currencyname.' '.VikBooking::numberFormat($or['cust_cost']);
									} elseif (array_key_exists($num, $tars) && !empty($tars[$num][0]['idprice'])) {
										$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num][0]['cost'];
										echo $currencyname.' '.VikBooking::numberFormat(VikBooking::sayCostPlusIva($display_rate, $tars[$num][0]['idprice']));
									}
									?>
									</div>
								</td>
							</tr>
							<?php
							//Options
							if (!empty($or['optionals'])) {
								$stepo = explode(";", $or['optionals']);
								$counter = 0;
								foreach ($stepo as $oo) {
									if (empty($oo)) {
										continue;
									}
									$stept = explode(":", $oo);
									$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=".(int)$stept[0].";";
									$dbo->setQuery($q);
									$dbo->execute();
									if ($dbo->getNumRows() != 1) {
										continue;
									}
									$counter++;
									$actopt = $dbo->loadAssocList();
									$chvar = '';
									if (!empty($actopt[0]['ageintervals']) && $or['children'] > 0 && strstr($stept[1], '-') != false) {
										$optagecosts = VikBooking::getOptionIntervalsCosts($actopt[0]['ageintervals']);
										$optagenames = VikBooking::getOptionIntervalsAges($actopt[0]['ageintervals']);
										$optagepcent = VikBooking::getOptionIntervalsPercentage($actopt[0]['ageintervals']);
										$agestept = explode('-', $stept[1]);
										$stept[1] = $agestept[0];
										$chvar = $agestept[1];
										if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
											//percentage value of the adults tariff
											if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
												$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
											} else {
												$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num][0]['cost'];
												$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
											}
										} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
											//VBO 1.10 - percentage value of room base cost
											if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
												$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
											} else {
												$display_rate = isset($tars[$num][0]['room_base_cost']) ? $tars[$num][0]['room_base_cost'] : (!empty($or['room_cost']) ? $or['room_cost'] : $tars[$num][0]['cost']);
												$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
											}
										}
										$actopt[0]['chageintv'] = $chvar;
										$actopt[0]['name'] .= ' ('.$optagenames[($chvar - 1)].')';
										$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $row['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
									} else {
										$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $row['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
									}
									if ($actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
										$realcost = $actopt[0]['maxprice'];
										if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
											$realcost = $actopt[0]['maxprice'] * $stept[1];
										}
									}
									$realcost = $actopt[0]['perperson'] == 1 ? ($realcost * $arrpeople[$num]['adults']) : $realcost;
									$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
									$isdue += $tmpopr;
									?>
							<tr class="vbo-bookingdet-summary-options">
								<td class="vbo-bookingdet-summary-options-title"><?php echo $counter == 1 ? JText::_('VBEDITORDEREIGHT') : '&nbsp;'; ?></td>
								<td>
									<span class="vbo-bookingdet-summary-lbl"><?php echo ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name']; ?></span>
								</td>
								<td>
									<span class="vbo-bookingdet-summary-cost"><?php echo $currencyname." ".VikBooking::numberFormat($tmpopr); ?></span>
								</td>
							</tr>
								<?php
								}
							}
							//Custom extra costs
							if (!empty($or['extracosts'])) {
								$counter = 0;
								$cur_extra_costs = json_decode($or['extracosts'], true);
								foreach ($cur_extra_costs as $eck => $ecv) {
									$counter++;
									?>
							<tr class="vbo-bookingdet-summary-custcosts">
								<td class="vbo-bookingdet-summary-custcosts-title"><?php echo $counter == 1 ? JText::_('VBPEDITBUSYEXTRACOSTS') : '&nbsp;'; ?></td>
								<td>
									<span class="vbo-bookingdet-summary-lbl"><?php echo $ecv['name']; ?></span>
								</td>
								<td>
									<span class="vbo-bookingdet-summary-cost"><?php echo $currencyname." ".VikBooking::numberFormat(VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'])); ?></span>
								</td>
							</tr>
									<?php
								}
							}
						}
						//vikbooking 1.1 coupon
						$usedcoupon = false;
						$origisdue = $isdue;
						if (strlen($row['coupon']) > 0) {
							$usedcoupon = true;
							$expcoupon = explode(";", $row['coupon']);
							$isdue = $isdue - $expcoupon[1];
							?>
							<tr class="vbo-bookingdet-summary-coupon">
								<td><?php echo JText::_('VBCOUPON'); ?></td>
								<td>
									<span class="vbo-bookingdet-summary-lbl"><?php echo $expcoupon[2]; ?></span>
								</td>
								<td>
									<span class="vbo-bookingdet-summary-cost">- <?php echo $currencyname; ?> <?php echo VikBooking::numberFormat($expcoupon[1]); ?></span>
								</td>
							</tr>
							<?php
						}
						//Reservation Total
						//Taxes Breakdown (only if tot_taxes is greater than 0)
						$tax_breakdown = array();
						$base_aliq = 0;
						if (count($all_id_prices) > 0 && $row['tot_taxes'] > 0) {
							//only last type of price assuming that the tax breakdown is equivalent in case of different rates
							$q = "SELECT `p`.`id`,`p`.`name`,`p`.`idiva`,`t`.`aliq`,`t`.`breakdown` FROM `#__vikbooking_prices` AS `p` LEFT JOIN `#__vikbooking_iva` `t` ON `p`.`idiva`=`t`.`id` WHERE `p`.`id`=".intval(array_pop($all_id_prices))." LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$breakdown_info = $dbo->loadAssoc();
								if (!empty($breakdown_info['breakdown']) && !empty($breakdown_info['aliq'])) {
									$tax_breakdown = json_decode($breakdown_info['breakdown'], true);
									$tax_breakdown = is_array($tax_breakdown) && count($tax_breakdown) > 0 ? $tax_breakdown : array();
									$base_aliq = $breakdown_info['aliq'];
								}
							}
						}
						//
						?>
							<tr class="vbo-bookingdet-summary-total">
								<td>
								<?php
								if (!$printreceipt) {
									?>
									<span class="vbapplydiscsp" onclick="toggleDiscount(this);">
										<i class="fa fa-chevron-down" title="<?php echo JText::_('VBAPPLYDISCOUNT'); ?>"></i>
									</span>
									<?php
								}
								?>
								</td>
								<td>
									<span class="vbo-bookingdet-summary-lbl"><?php echo JText::_('VBEDITORDERNINE'); ?></span>

									<div class="vbdiscenter" id="vbdiscenter" style="display: none;">
										<div class="vbdiscenter-entry">
											<span class="vbdiscenter-label"><?php echo JText::_('VBTOTALVAT'); ?>:</span><span class="vbdiscenter-value"><?php echo $currencyname; ?> <input type="text" name="tot_taxes" value="<?php echo $row['tot_taxes']; ?>" size="4" placeholder="0.00"/></span>
										</div>
									<?php
									if (count($tax_breakdown)) {
										foreach ($tax_breakdown as $tbkk => $tbkv) {
											$tax_break_cost = $row['tot_taxes'] * floatval($tbkv['aliq']) / $base_aliq;
											?>
										<div class="vbdiscenter-entry vbdiscenter-entry-breakdown">
											<span class="vbdiscenter-label"><?php echo $tbkv['name']; ?>:</span><span class="vbdiscenter-value"><?php echo $currencyname; ?> <?php echo VikBooking::numberFormat($tax_break_cost); ?></span>
										</div>
											<?php
										}
									}
									?>
										<div class="vbdiscenter-entry">
											<span class="vbdiscenter-label"><?php echo JText::_('VBTOTALCITYTAX'); ?>:</span><span class="vbdiscenter-value"><?php echo $currencyname; ?> <input type="number" step="any" name="tot_city_taxes" value="<?php echo $row['tot_city_taxes']; ?>" size="4"/></span>
										</div>
										<div class="vbdiscenter-entry">
											<span class="vbdiscenter-label"><?php echo JText::_('VBTOTALFEES'); ?>:</span><span class="vbdiscenter-value"><?php echo $currencyname; ?> <input type="number" step="any" name="tot_fees" value="<?php echo $row['tot_fees']; ?>" size="4"/></span>
										</div>
										<div class="vbdiscenter-entry">
											<span class="vbdiscenter-label hasTooltip"<?php echo !empty($otachannel_name) ? ' title="'.$otachannel_name.'"' : ''; ?>><?php echo JText::_('VBTOTALCOMMISSIONS'); ?>:</span><span class="vbdiscenter-value"><?php echo $currencyname; ?> <input type="number" step="any" name="cmms" value="<?php echo $row['cmms']; ?>" size="4"/></span>
										</div>
										<div class="vbdiscenter-entry">
											<span class="vbdiscenter-label"><?php echo JText::_('VBAPPLYDISCOUNT'); ?>:</span><span class="vbdiscenter-value"><?php echo $currencyname; ?> <input type="number" step="any" name="admindisc" value="" size="4"/></span>
										</div>
										<div class="vbdiscenter-entrycentered">
											<button type="submit" class="btn btn-success"><?php echo JText::_('VBAPPLYDISCOUNTSAVE'); ?></button>
										</div>
									</div>
								</td>
								<td>
									<span class="vbo-bookingdet-summary-cost"><?php echo (strlen($otacurrency) > 0 ? '('.$otacurrency.') '.$currencyname : $currencyname); ?> <?php echo VikBooking::numberFormat($row['total']); ?></span>
								</td>
							</tr>
						<?php
						if (!empty($row['totpaid']) && $row['totpaid'] > 0) {
							$diff_to_pay = $row['total'] - $row['totpaid'];
							?>
							<tr class="vbo-bookingdet-summary-totpaid">
								<td>&nbsp;</td>
								<td><?php echo JText::_('VBAMOUNTPAID'); ?></td>
								<td><?php echo $currencyname.' '.VikBooking::numberFormat($row['totpaid']); ?></td>
							</tr>
							<?php
							if ($diff_to_pay > 1) {
							?>
							<tr class="vbo-bookingdet-summary-totpaid vbo-bookingdet-summary-totremaining">
								<td>&nbsp;</td>
								<td>
									<div><?php echo JText::_('VBTOTALREMAINING'); ?></div>
									<?php
									//enable second payment
									if (!$printreceipt && $row['status'] == 'confirmed' && !($row['paymcount'] > 0) && VikBooking::multiplePayments() && is_array($payment) && !empty($payment['id'])) {
										?>
										<div style="margin-top: 5px;">
											<a href="index.php?option=com_vikbooking&amp;task=editorder&amp;makepay=1&amp;cid[]=<?php echo $row['id']; ?>" class="vbo-makepayable-link"><i class="vboicn-credit-card"></i><?php echo JText::_('VBMAKEORDERPAYABLE'); ?></a>
										</div>
										<?php
									}
									//
									?>
								</td>
								<td><?php echo $currencyname.' '.VikBooking::numberFormat($diff_to_pay); ?></td>
							</tr>
							<?php
							}
						}
						?>
						</table>
					</div>
				</div>
			</div>
		<?php
		if ($printreceipt) {
			$receipt_notes = VikBooking::getReceiptNotes();
			?>
			<div class="vbo-receipt-notes-container">
				<div class="vbo-receipt-notes-inner">
					<div class="vbo-receipt-notes-val vbo-showin-print" id="vbo-receipt-notes-val"><?php echo $receipt_notes; ?></div>
					<div class="vbo-receipt-notes-tarea vbo-hidein-print">
						<textarea id="vbo-receipt-notes" placeholder="<?php echo JText::_('VBORECEIPTNOTESDEF'); ?>"><?php echo htmlspecialchars($receipt_notes); ?></textarea>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		</div>

		<div class="vbo-bookingdet-tab-cont" id="vbo-tab-admin" style="display: none;">
			<div class="vbo-bookingdet-innercontainer">
				<div class="vbo-bookingdet-admindata">
					<div class="vbo-bookingdet-admin-entry">
						<label for="newpayment"><?php echo JText::_('VBPAYMENTMETHOD'); ?></label>
					<?php
					if (is_array($payment)) {
						?>
						<span><?php echo $payment['name']; ?></span>
						<?php
					}
					$chpayment = '';
					if (is_array($payments)) {
						$chpayment = '<div><select name="newpayment" id="newpayment" onchange="changePayment();"><option value="">'.JText::_('VBCHANGEPAYLABEL').'</option>';
						foreach($payments as $pay) {
							$chpayment .= '<option value="'.$pay['id'].'">'.(is_array($payment) && $payment['id'] == $pay['id'] ? ' ::' : '').$pay['name'].'</option>';
						}
						$chpayment .= '</select></div>';
					}
					echo $chpayment;
					?>
					</div>
				<?php
				$tn = VikBooking::getTranslator();
				$all_langs = $tn->getLanguagesList();
				if (count($all_langs) > 1) {
				?>
					<div class="vbo-bookingdet-admin-entry">
						<label for="newlang"><?php echo JText::_('VBOBOOKINGLANG'); ?></label>
						<select name="newlang" id="newlang" onchange="document.adminForm.submit();">
						<?php
						foreach ($all_langs as $lk => $lv) {
							?>
							<option value="<?php echo $lk; ?>"<?php echo $row['lang'] == $lk ? ' selected="selected"' : ''; ?>><?php echo isset($lv['nativeName']) ? $lv['nativeName'] : $lv['name']; ?></option>
							<?php
						}
						?>
						</select>
					</div>
				<?php
				}
				?>
				</div>
				<div class="vbo-bookingdet-noteslogs">
					<?php
					$history_obj = VikBooking::getBookingHistoryInstance();
					$history_obj->setBid($row['id']);
					$history = $history_obj->loadHistory();
					?>
					<div class="vbo-bookingdet-noteslogs-btns">
						<div class="vbo-bookingdet-noteslogs-btn vbo-bookingdet-noteslogs-btn-active">
							<a href="javascript: void(0);" onclick="javascript: vbToggleNotes(this);"><?php echo JText::_('VBADMINNOTESTOGGLE'); ?></a>
						</div>
					<?php
					if (count($history)) {
						?>
						<div class="vbo-bookingdet-noteslogs-btn">
							<a href="javascript: void(0);" onclick="javascript: vbToggleHistory(this);"><?php echo JText::_('VBOBOOKHISTORYTAB'); ?></a>
						</div>
						<?php
					}
					?>
						<div class="vbo-bookingdet-noteslogs-btn">
							<a href="javascript: void(0);" class="hasTooltip" onclick="javascript: vbToggleInvNotes(this);" title="<?php echo addslashes(JText::_('VBBOOKINGINVNOTESHELP')); ?>"><?php echo JText::_('VBBOOKINGINVNOTES'); ?></a>
						</div>
					<?php
					if (!empty($row['paymentlog'])) {
						?>
						<div class="vbo-bookingdet-noteslogs-btn">
							<a href="javascript: void(0);" id="vbo-trig-paylogs" onclick="javascript: vbToggleLog(this);"><?php echo JText::_('VBPAYMENTLOGTOGGLE'); ?></a>
							<a name="paymentlog" href="javascript: void(0);"></a>
						</div>
						<?php
					}
					?>
					</div>
					<div class="vbo-bookingdet-noteslogs-cont">
						<div id="vbadminnotesdiv" style="display: block;">
							<textarea name="adminnotes" class="vbadminnotestarea"><?php echo strip_tags($row['adminnotes']); ?></textarea>
							<br clear="all"/>
							<input type="submit" name="updadmnotes" value="<?php echo JText::_('VBADMINNOTESUPD'); ?>" class="btn btn-secondary" />
						</div>
					<?php
					if (count($history)) {
						?>
						<div id="vbhistorydiv" style="display: none;">
							<div class="vbo-booking-history-container table-responsive">
								<table class="table">
									<thead>
										<tr class="vbo-booking-history-firstrow">
											<td class="vbo-booking-history-td-type"><?php echo JText::_('VBOBOOKHISTORYLBLTYPE'); ?></td>
											<td class="vbo-booking-history-td-date"><?php echo JText::_('VBOBOOKHISTORYLBLDATE'); ?></td>
											<td class="vbo-booking-history-td-descr"><?php echo JText::_('VBOBOOKHISTORYLBLDESC'); ?></td>
											<td class="vbo-booking-history-td-totpaid"><?php echo JText::_('VBOBOOKHISTORYLBLTPAID'); ?></td>
											<td class="vbo-booking-history-td-tot"><?php echo JText::_('VBOBOOKHISTORYLBLTOT'); ?></td>
										</tr>
									</thead>
									<tbody>
									<?php
									foreach ($history as $hist) {
										$hdescr = strpos($hist['descr'], '<') !== false ? $hist['descr'] : nl2br($hist['descr']);
										?>
										<tr class="vbo-booking-history-row">
											<td><?php echo $history_obj->validType($hist['type'], true); ?></td>
											<td><?php echo $hist['dt']; ?></td>
											<td><?php echo $hdescr; ?></td>
											<td><?php echo $currencyname.' '.VikBooking::numberFormat($hist['totpaid']); ?></td>
											<td><?php echo $currencyname.' '.VikBooking::numberFormat($hist['total']); ?></td>
										</tr>
										<?php
									}
									?>
									</tbody>
								</table>
							</div>
						</div>
						<?php
					}
					?>
						<div id="vbinvnotesdiv" style="display: none;">
							<textarea name="invnotes" id="invnotes" class="vbadminnotestarea"><?php echo $row['inv_notes']; ?></textarea>
							<br clear="all"/>
							<input type="submit" name="updinvnotes" value="<?php echo JText::_('VBADMINNOTESUPD'); ?>" class="btn btn-secondary" />
							<button type="button" class="btn btn-secondary pull-right" onclick="document.getElementById('invnotes-hid').value=document.getElementById('invnotes').value;document.getElementById('vbo-gen-invoice').submit();"><i class="vboicn-file-text2"></i> <?php echo JText::_('VBOGENBOOKINGINVOICE'); ?></button>
						</div>
					<?php
					if (!empty($row['paymentlog'])) {
						?>
						<div id="vbpaymentlogdiv" style="display: none;">
						<?php
						//PCI Data Retrieval
						if (!empty($row['idorderota']) && !empty($row['channel'])) {
							$channel_source = $row['channel'];
							if (strpos($row['channel'], '_') !== false) {
								$channelparts = explode('_', $row['channel']);
								$channel_source = $channelparts[0];
							}
							//Limit set to Check-out date at 29:59:59
							$checkout_info = getdate($row['checkout']);
							$checkout_midnight = mktime(23, 59, 59, $checkout_info['mon'], $checkout_info['mday'], $checkout_info['year']);
							if (time() < $checkout_midnight) {
								$plain_log = htmlspecialchars($row['paymentlog']);
								if (stripos($plain_log, 'card number') !== false && strpos($plain_log, '****') !== false) {
									//log contains credit card details
									//Prepare modal (Credit Card Details)
									array_push($vbo_modals_html, $vbo_app->getJmodalHtml('vbo-vcm-pcid', JText::_('GETFULLCARDDETAILS'), '', 'width: 80%; height: 60%; margin-left: -40%; top: 20% !important;'));
									//end Prepare modal
									?>
							<div class="vcm-notif-pcidrq-container">
								<a class="vcm-pcid-launch" onclick="vboOpenJModal('vbo-vcm-pcid', 'index.php?option=com_vikchannelmanager&task=execpcid&channel_source=<?php echo $channel_source; ?>&otaid=<?php echo $row['idorderota']; ?>&tmpl=component');" href="javascript: void(0);"><?php echo JText::_('GETFULLCARDDETAILS'); ?></a>
							</div>
									<?php
								}
							}
						}
						//
						?>
							<pre style="min-height: 100%;"><?php echo htmlspecialchars($row['paymentlog']); ?></pre>
						</div>
						<script type="text/javascript">
						if (window.location.hash == '#paymentlog') {
							vbToggleLog(document.getElementById('vbo-trig-paylogs'));
							jQuery(".vbo-bookingdet-tab[data-vbotab='vbo-tab-admin']").trigger('click');
						}
						</script>
						<?php
					}
					?>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="task" value="editorder">
		<input type="hidden" name="vbo_active_tab" id="vbo_active_tab" value="">
		<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
		<input type="hidden" name="cid[]" value="<?php echo $row['id']; ?>">
		<input type="hidden" name="option" value="com_vikbooking">
		<?php
		$tmpl = VikRequest::getVar('tmpl');
		if ($tmpl == 'component') {
			echo '<input type="hidden" name="tmpl" value="component">';
		}
		$pgoto = VikRequest::getString('goto', '', 'request');
		if ($pgoto == 'overv') {
			echo '<input type="hidden" name="goto" value="overv">';
		}
		?>
	</form>
</div>
<?php
foreach ($vbo_modals_html as $modalhtml) {
	echo $modalhtml;
}
?>
<form action="index.php?option=com_vikbooking&amp;task=orders" method="post" id="vbo-gen-invoice">
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="orders" />
	<input type="hidden" name="cid[]" value="<?php echo $row['id']; ?>" />
	<input type="hidden" name="invnotes" id="invnotes-hid" value="" />
</form>
<div class="vbo-info-overlay-block">
	<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-info-overlay-content vbo-info-overlay-content-sendsms">
		<div id="vbo-overlay-sms-cont" style="display: none;">
			<h4><?php echo JText::_('VBSENDSMSACTION'); ?>: <span id="smstophone-lbl"><?php echo $row['phone']; ?></span></h4>
			<form action="index.php?option=com_vikbooking" method="post">
				<div class="vbo-calendar-cfield-entry">
					<label for="smscont"><?php echo JText::_('VBSENDSMSCUSTCONT'); ?></label>
					<span><textarea name="smscont" id="smscont" style="width: 99%; min-width: 99%;max-width: 99%; height: 35%;"></textarea></span>
				</div>
				<div class="vbo-calendar-cfields-bottom">
					<button type="submit" class="btn btn-secondary"><i class="vboicn-bubbles"></i><?php echo JText::_('VBSENDSMSACTION'); ?></button>
				</div>
				<input type="hidden" name="phone" id="smstophone" value="<?php echo $row['phone']; ?>" />
				<input type="hidden" name="goto" value="<?php echo urlencode('index.php?option=com_vikbooking&task=editorder&cid[]='.$row['id']); ?>" />
				<input type="hidden" name="task" value="sendcustomsms" />
			</form>
		</div>
		<div id="vbo-overlay-email-cont" style="display: none;">
			<h4><?php echo JText::_('VBSENDEMAILACTION'); ?>: <span id="emailto-lbl"><?php echo $row['custmail']; ?></span></h4>
			<form action="index.php?option=com_vikbooking" method="post" enctype="multipart/form-data">
				<input type="hidden" name="bid" value="<?php echo $row['id']; ?>" />
			<?php
			$cur_emtpl = array();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$cur_emtpl = $dbo->loadResult();
				$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
				$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
			}
			if (count($cur_emtpl) > 0) {
				?>
				<div style="float: right;">
					<select id="emtpl-customemail" onchange="vboLoadEmailTpl(this.value);">
						<option value=""><?php echo JText::_('VBEMAILCUSTFROMTPL'); ?></option>
					<?php
					foreach ($cur_emtpl as $emk => $emv) {
						?>
						<optgroup label="<?php echo $emv['emailsubj']; ?>">
							<option value="<?php echo $emk; ?>"><?php echo JText::_('VBEMAILCUSTFROMTPLUSE'); ?></option>
							<option value="rm<?php echo $emk; ?>"><?php echo JText::_('VBEMAILCUSTFROMTPLRM'); ?></option>
						</optgroup>
						<?php
					}
					?>
					</select>
				</div>
				<?php
			}
			?>
				<div class="vbo-calendar-cfield-entry">
					<label for="emailsubj"><?php echo JText::_('VBSENDEMAILCUSTSUBJ'); ?></label>
					<span><input type="text" name="emailsubj" id="emailsubj" value="" size="30" /></span>
				</div>
				<div class="vbo-calendar-cfield-entry">
					<label for="emailcont"><?php echo JText::_('VBSENDEMAILCUSTCONT'); ?></label>
					<textarea name="emailcont" id="emailcont" style="width: 99%; min-width: 99%; max-width: 99%; height: 120px; margin-bottom: 1px;"></textarea>
					<div class="btn-group pull-left vbo-smstpl-bgroup vbo-custmail-bgroup">
						<button onclick="setSpecialTplTag('emailcont', '{customer_name}');" class="btn btn-secondary btn-small" type="button">{customer_name}</button>
						<button onclick="setSpecialTplTag('emailcont', '{checkin_date}');" class="btn btn-secondary btn-small" type="button">{checkin_date}</button>
						<button onclick="setSpecialTplTag('emailcont', '{checkout_date}');" class="btn btn-secondary btn-small" type="button">{checkout_date}</button>
						<button onclick="setSpecialTplTag('emailcont', '{num_nights}');" class="btn btn-secondary btn-small" type="button">{num_nights}</button>
						<button onclick="setSpecialTplTag('emailcont', '{rooms_booked}');" class="btn btn-secondary btn-small" type="button">{rooms_booked}</button>
						<button onclick="setSpecialTplTag('emailcont', '{rooms_names}');" class="btn btn-secondary btn-small" type="button">{rooms_names}</button>
						<button onclick="setSpecialTplTag('emailcont', '{tot_adults}');" class="btn btn-secondary btn-small" type="button">{tot_adults}</button>
						<button onclick="setSpecialTplTag('emailcont', '{tot_children}');" class="btn btn-secondary btn-small" type="button">{tot_children}</button>
						<button onclick="setSpecialTplTag('emailcont', '{tot_guests}');" class="btn btn-secondary btn-small" type="button">{tot_guests}</button>
						<button onclick="setSpecialTplTag('emailcont', '{total}');" class="btn btn-secondary btn-small" type="button">{total}</button>
						<button onclick="setSpecialTplTag('emailcont', '{total_paid}');" class="btn btn-secondary btn-small" type="button">{total_paid}</button>
						<button onclick="setSpecialTplTag('emailcont', '{remaining_balance}');" class="btn btn-secondary btn-small" type="button">{remaining_balance}</button>
						<button onclick="setSpecialTplTag('emailcont', '{booking_id}');" class="btn btn-secondary btn-small" type="button">{booking_id}</button>
					</div>
				</div>
				<div class="vbo-calendar-cfield-entry">
					<label for="emailattch"><?php echo JText::_('VBSENDEMAILCUSTATTCH'); ?></label>
					<span><input type="file" name="emailattch" id="emailattch" /></span>
				</div>
				<div class="vbo-calendar-cfield-entry">
					<label for="emailfrom"><?php echo JText::_('VBSENDEMAILCUSTFROM'); ?></label>
					<span><input type="text" name="emailfrom" id="emailfrom" value="<?php echo VikBooking::getSenderMail(); ?>" size="30" /></span>
				</div>
				<br clear="all" />
				<div class="vbo-calendar-cfields-bottom">
					<button type="submit" class="btn"><i class="vboicn-envelop"></i><?php echo JText::_('VBSENDEMAILACTION'); ?></button>
				</div>
				<input type="hidden" name="email" id="emailto" value="<?php echo $row['custmail']; ?>" />
				<input type="hidden" name="goto" value="<?php echo urlencode('index.php?option=com_vikbooking&task=editorder&cid[]='.$row['id']); ?>" />
				<input type="hidden" name="task" value="sendcustomemail" />
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
var vbo_overlay_on = false;
var vbo_print_only = false;
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
}
function vboToggleSendSMS() {
	var cur_phone = jQuery("#smstophone").val();
	var phone_set = jQuery("#custphone").val();
	if (phone_set.length && phone_set != cur_phone) {
		jQuery("#smstophone").val(phone_set);
		jQuery("#smstophone-lbl").text(phone_set);
	}
	jQuery("#vbo-overlay-email-cont").hide();
	jQuery("#vbo-overlay-sms-cont").show();
	jQuery(".vbo-info-overlay-block").fadeToggle(400, function() {
		if (jQuery(".vbo-info-overlay-block").is(":visible")) {
			vbo_overlay_on = true;
		} else {
			vbo_overlay_on = false;
		}
	});
}
function vboToggleSendEmail() {
	var cur_email = jQuery("#emailto").val();
	var email_set = jQuery("#custmail").val();
	if (email_set.length && email_set != cur_email) {
		jQuery("#emailto").val(email_set);
		jQuery("#emailto-lbl").text(email_set);
	}
	jQuery("#vbo-overlay-sms-cont").hide();
	jQuery("#vbo-overlay-email-cont").show();
	jQuery(".vbo-info-overlay-block").fadeToggle(400, function() {
		if (jQuery(".vbo-info-overlay-block").is(":visible")) {
			vbo_overlay_on = true;
		} else {
			vbo_overlay_on = false;
		}
	});
}
function setSpecialTplTag(taid, tpltag) {
	var tplobj = document.getElementById(taid);
	if (tplobj != null) {
		var start = tplobj.selectionStart;
		var end = tplobj.selectionEnd;
		tplobj.value = tplobj.value.substring(0, start) + tpltag + tplobj.value.substring(end);
		tplobj.selectionStart = tplobj.selectionEnd = start + tpltag.length;
		tplobj.focus();
	}
}
jQuery(document).ready(function(){
	jQuery(document).mouseup(function(e) {
		if (!vbo_overlay_on) {
			return false;
		}
		var vbo_overlay_cont = jQuery(".vbo-info-overlay-content");
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
	jQuery(".vbo-bookingdet-tab").click(function() {
		var newtabrel = jQuery(this).attr('data-vbotab');
		var oldtabrel = jQuery(".vbo-bookingdet-tab-active").attr('data-vbotab');
		if (newtabrel == oldtabrel) {
			return;
		}
		jQuery(".vbo-bookingdet-tab").removeClass("vbo-bookingdet-tab-active");
		jQuery(this).addClass("vbo-bookingdet-tab-active");
		jQuery("#"+oldtabrel).hide();
		jQuery("#"+newtabrel).fadeIn();
		jQuery("#vbo_active_tab").val(newtabrel);
	});
	jQuery(".vbo-bookingdet-tab[data-vbotab='<?php echo $pactive_tab; ?>']").trigger('click');
});
var cur_emtpl = <?php echo json_encode($cur_emtpl); ?>;
function vboLoadEmailTpl(tplind) {
	if (!(tplind.length > 0)) {
		jQuery('#emailsubj').val('');
		jQuery('#emailcont').val('');
		return true;
	}
	if (tplind.substr(0, 2) == 'rm') {
		if (confirm('<?php echo addslashes(JText::_('VBDELCONFIRM')); ?>')) {
			document.location.href = 'index.php?option=com_vikbooking&task=rmcustomemailtpl&cid[]=<?php echo $row['id']; ?>&tplind='+tplind.substr(2);
		}
		return false;
	}
	if (!cur_emtpl.hasOwnProperty(tplind)) {
		jQuery('#emailsubj').val('');
		jQuery('#emailcont').val('');
		return true;
	}
	jQuery('#emailsubj').val(cur_emtpl[tplind]['emailsubj']);
	jQuery('#emailcont').val(cur_emtpl[tplind]['emailcont']);
	jQuery('#emailfrom').val(cur_emtpl[tplind]['emailfrom']);
	return true;
}
<?php
$pcustomemail = VikRequest::getInt('customemail', '', 'request');
if ($pcustomemail > 0) {
	?>
	vboToggleSendEmail();
	<?php
}
if ($printreceipt) {
	?>
jQuery(document).ready(function() {
	jQuery('button, .vbo-bookingdet-innertop').hide();
	jQuery('body').find('a').each(function(k, v) {
		jQuery(this).replaceWith(jQuery(this).html());
	});
	jQuery('body').find("input[type='text']").each(function(k, v) {
		jQuery(this).replaceWith(jQuery(this).val());
	});
});
function vboMakePrintOnly() {
	vbo_print_only = true;
	jQuery(".vbo-receipt-numdate-block").remove();
	jQuery("#vbo-receipt-print-btn-name").text("<?php echo addslashes(JText::_('VBOPRINT')); ?>");
}
function vboLaunchPrintReceipt() {
	var rcnotes = jQuery('#vbo-receipt-notes').val();
	var rnewnum = jQuery('#vbo-receipt-num-inp').val();
	if (rcnotes.length) {
		if (rcnotes.indexOf('<') < 0) {
			rcnotes = rcnotes.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
		}
		jQuery('#vbo-receipt-notes-val').html(rcnotes);
	} else {
		jQuery('.vbo-receipt-notes-container').remove();
	}
	if (vbo_print_only === true) {
		window.print();
		return;
	}
	jQuery.ajax({
		type: "POST",
		url: "index.php?option=com_vikbooking&task=updatereceiptnum&tmpl=component",
		data: { newnum: rnewnum, newnotes: rcnotes, oid: "<?php echo $row['id']; ?>" }
	}).done(function(res) {
		window.print();
	}).fail(function() {
		alert('Could not update the next receipt number.')
		window.print();
	});
}
	<?php
}
?>
</script>