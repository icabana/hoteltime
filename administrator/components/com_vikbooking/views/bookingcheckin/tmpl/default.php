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

$order = $this->row;
$rooms = $this->rooms;
$customer = $this->customer;

$vbo_app = new VboApplication();
$dbo = JFactory::getDbo();
$currencysymb = VikBooking::getCurrencySymb();
$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator(true);
$pchanged = VikRequest::getInt('changed', '', 'request');
$tmpl = VikRequest::getVar('tmpl');
$set_parent_status = '';
$now_info = getdate();
$today_midnight = mktime(0, 0, 0, $now_info['mon'], $now_info['mday'], $now_info['year']);
$colortags = VikBooking::loadBookingsColorTags();
$otachannel = '';
$otachannel_name = '';
$otachannel_bid = '';
$otacurrency = '';
if (!empty($order['channel'])) {
	$channelparts = explode('_', $order['channel']);
	$otachannel = array_key_exists(1, $channelparts) && strlen($channelparts[1]) > 0 ? $channelparts[1] : ucwords($channelparts[0]);
	$otachannel_name = $otachannel;
	$otachannel_bid = $order['idorderota'];
	if (strstr($otachannel, '.') !== false) {
		$otaccparts = explode('.', $otachannel);
		$otachannel = $otaccparts[0];
	}
	$otacurrency = strlen($order['chcurrency']) > 0 ? $order['chcurrency'] : '';
}
$sensible_k = array('first_name', 'last_name', 'country', 'gender', 'bdate', 'pbirth');
$missing_customer_det = false;
foreach ($customer as $ck => $cv) {
	if ((!isset($customer[$ck]) || empty($customer[$ck])) && in_array($ck, $sensible_k)) {
		$missing_customer_det = true;
	}
}
?>
<div class="vbo-info-overlay-block">
	<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-info-overlay-content">
		<h3 id="vbo-overlay-title"></h3>
		<div class="vbo-overlay-checkin-body"></div>
	</div>
</div>

<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<div class="vbo-bookdet-container">
		<div class="vbo-bookdet-wrap">
			<div class="vbo-bookdet-head">
				<span>ID</span>
			<?php
			if (!empty($order['adminnotes'])) {
				?>
				<i class="vboicn-info icn-bigger icn-nomargin icn-float-left icn-clickable" onclick="vboUpdateModal('<?php echo addslashes(JText::_('VBADMINNOTESTOGGLE')); ?>', '.adminnotes', true);"></i>
				<?php
			}
			?>
			</div>
			<div class="vbo-bookdet-foot">
				<span><a href="index.php?option=com_vikbooking&task=editorder&cid[]=<?php echo $order['id']; ?>" target="_blank"><?php echo $order['id']; ?></a></span>
			</div>
		</div>
		<?php
		if (!empty($order['channel'])) {
			?>
		<div class="vbo-bookdet-wrap">
			<div class="vbo-bookdet-head">
				<?php echo $otachannel; ?>
			</div>
			<div class="vbo-bookdet-foot">
				<span>ID <?php echo $otachannel_bid; ?></span>
			</div>
		</div>
			<?php
		}
		?>
		<div class="vbo-bookdet-wrap">
			<div class="vbo-bookdet-head">
				<span><?php echo JText::_('VBEDITORDERONE'); ?></span>
			</div>
			<div class="vbo-bookdet-foot">
				<?php echo date(str_replace("/", $datesep, $df).' H:i', $order['ts']); ?>
			</div>
		</div>
		<?php
		if (count($customer)) {
		?>
		<div class="vbo-bookdet-wrap">
			<div class="vbo-bookdet-head">
				<span><?php echo JText::_('VBCUSTOMERNOMINATIVE'); ?></span>
			<?php
			if ($missing_customer_det) {
				echo $vbo_app->createPopover(array('title' => JText::_('VBCUSTOMERMISSIMPDET'), 'content' => JText::_('VBCUSTOMERMISSIMPDETHELP'), 'icon_class' => 'fa fa-exclamation-triangle', 'placement' => 'bottom'));
			} elseif (!empty($customer['notes'])) {
				?>
				<i class="vboicn-info icn-bigger icn-nomargin icn-float-left icn-clickable" onclick="vboUpdateModal('<?php echo addslashes(JText::_('VBCUSTOMERNOTES')); ?>', '.customer_notes', true);"></i>
				<?php
			}
			?>
			</div>
			<div class="vbo-bookdet-foot">
				<?php echo (isset($customer['country_img']) ? $customer['country_img'].' ' : '').'<a href="javascript: void(0);" onclick="vboUpdateModal(\''.addslashes(JText::_('VBCUSTINFO')).'\', \'.customer_info\', true);">'.ltrim($customer['first_name'].' '.$customer['last_name']).'</a>'; ?>
			</div>
		</div>
		<?php
		}
		?>
		<div class="vbo-bookdet-wrap">
			<div class="vbo-bookdet-head">
				<span><?php echo JText::_('VBEDITORDERROOMSNUM'); ?></span>
			</div>
			<div class="vbo-bookdet-foot">
				<?php echo $order['roomsnum']; ?>
			</div>
		</div>
		<div class="vbo-bookdet-wrap">
			<div class="vbo-bookdet-head">
				<span><?php echo JText::_('VBEDITORDERFOUR'); ?></span>
			</div>
			<div class="vbo-bookdet-foot">
				<?php echo $order['days']; ?>
			</div>
		</div>
		<div class="vbo-bookdet-wrap">
			<div class="vbo-bookdet-head">
				<span><?php echo JText::_('VBEDITORDERFIVE'); ?></span>
			</div>
			<div class="vbo-bookdet-foot">
			<?php
			$checkin_info = getdate($order['checkin']);
			$short_wday = JText::_('VB'.strtoupper(substr($checkin_info['weekday'], 0, 3)));
			?>
				<?php echo $short_wday.', '.date(str_replace("/", $datesep, $df).' H:i', $order['checkin']); ?>
			</div>
		</div>
		<div class="vbo-bookdet-wrap">
			<div class="vbo-bookdet-head">
				<span><?php echo JText::_('VBEDITORDERSIX'); ?></span>
			</div>
			<div class="vbo-bookdet-foot">
			<?php
			$checkout_info = getdate($order['checkout']);
			$short_wday = JText::_('VB'.strtoupper(substr($checkout_info['weekday'], 0, 3)));
			?>
				<?php echo $short_wday.', '.date(str_replace("/", $datesep, $df).' H:i', $order['checkout']); ?>
			</div>
		</div>
		<div class="vbo-bookdet-wrap vbo-bookdet-wrap-special">
			<div class="vbo-bookdet-head">
				<span><?php echo JText::_('VBOCHECKEDSTATUS'); ?></span>
			</div>
			<div class="vbo-bookdet-foot">
			<?php
			if ($order['checked'] < 0) {
				//no show
				$checked_status = '<span class="label label-error" style="background-color: #d9534f;">'.JText::_('VBOCHECKEDSTATUSNOS').'</span>';
				$set_parent_status = $pchanged > 0 ? '<span style="font-weight: bold; color: red;">'.strtoupper(JText::_('VBOCHECKEDSTATUSNOS')).'</span>' : $set_parent_status;
			} elseif ($order['checked'] == 1) {
				//checked in
				$checked_status = '<span class="label label-success">'.JText::_('VBOCHECKEDSTATUSIN').'</span>';
				$set_parent_status = $pchanged > 0 ? '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VBOCHECKEDSTATUSIN')).'</span>' : $set_parent_status;
			} elseif ($order['checked'] == 2) {
				//checked out
				$checked_status = '<span class="label label-info">'.JText::_('VBOCHECKEDSTATUSOUT').'</span>';
				$set_parent_status = $pchanged > 0 ? '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VBOCHECKEDSTATUSOUT')).'</span>' : $set_parent_status;
			} else {
				//none (0)
				$checked_status = '<span class="label">'.JText::_('VBOCHECKEDSTATUSZERO').'</span>';
				$set_parent_status = $pchanged > 0 ? '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VBCONFIRMED')).'</span>' : $set_parent_status;
			}
			?>
				<?php echo $checked_status; ?>
			</div>
		</div>
	</div>
		<?php
		//rooms details and total information
		?>
	<div class="vbo-checkin-main-block">
		<div class="vbo-roomsdet-container">
			<?php
			$tars = array();
			$arrpeople = array();
			$is_package = (!empty($order['pkg']));
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
				$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `id`=".(int)$or['idtar'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$tar = $dbo->loadAssocList();
				$tars[$num] = $tar;
			}
			//compose the calculation for pax_data
			$pax_data = array();
			$count_pax_data = 0;
			if (count($customer) > 0) {
				$count_pax_data = 1;
				if (!empty($customer['pax_data'])) {
					$pax_data = json_decode($customer['pax_data'], true);
				}
			}
			$all_countries = VikBooking::getCountriesArray();
			//
			foreach ($rooms as $ind => $or) {
				$num = $ind + 1;
				//total guests details available
				$count_pax_data = $num < 2 ? $count_pax_data : 0;
				if (isset($pax_data[$ind])) {
					$count_pax_data = count($pax_data[$ind]);
				}
				//
				//Room Specific Unit
				$spec_unit = '';
				if (!empty($or['params'])) {
					$room_params = json_decode($or['params'], true);
					$arr_features = array();
					if (is_array($room_params) && array_key_exists('features', $room_params) && @count($room_params['features']) > 0) {
						foreach ($room_params['features'] as $rind => $rfeatures) {
							foreach ($rfeatures as $fname => $fval) {
								if (strlen($fval)) {
									$arr_features[$rind] = '#'.$rind.' - '.JText::_($fname).': '.$fval;
									break;
								}
							}
						}
					}
					if (isset($arr_features[$or['roomindex']])) {
						$spec_unit = $arr_features[$or['roomindex']];
					}
				}
				//
				?>
			<div class="vbo-roomdet-wrapper">
				<div class="vbo-roomdet-wrap">
					<div class="vbo-roomdet-entry">
						<div class="vbo-roomdet-head">
							<span><?php echo $or['room_name']; ?></span>
						</div>
						<div class="vbo-roomdet-foot">
						<?php
						if (!empty($spec_unit)) {
							?>
							<span><?php echo $spec_unit; ?></span>
							<?php
						}
						?>
							<div class="vbo-roomdet-guests-toggle-cont">
								<span tabindex="0" class="vbo-roomdet-guests-toggle <?php echo $count_pax_data >= $arrpeople[$num]['adults'] ? 'vbo-guestscount-complete' : 'vbo-guestscount-incomplete'; ?>" data-roomind="<?php echo $ind; ?>">
									<i class="vboicn-user-plus"></i> 
									<span class="vbo-roomdet-guests-toggleword"><?php echo JText::_('VBOGUESTSDETAILS'); ?> (<span id="vbo-guestscount-<?php echo $ind; ?>"><?php echo $count_pax_data; ?></span>/<?php echo $arrpeople[$num]['adults']; ?>)</span>
								</span>
							</div>
						</div>
					</div>
					<div class="vbo-roomdet-entry">
						<div class="vbo-roomdet-head">
							<span><?php echo JText::_('VBEDITORDERADULTS'); ?></span>
						</div>
						<div class="vbo-roomdet-foot">
							<span><?php echo $arrpeople[$num]['adults']; ?></span>
						</div>
					</div>
				<?php
				$age_str = '';
				if ($arrpeople[$num]['children'] > 0) {
					if (!empty($arrpeople[$num]['children_age'])) {
						$json_child = json_decode($arrpeople[$num]['children_age'], true);
						if (is_array($json_child['age']) && count($json_child['age']) > 0) {
							$age_str = ' '.JText::sprintf('VBORDERCHILDAGES', implode(', ', $json_child['age']));
						}
					}
				}
				?>
					<div class="vbo-roomdet-entry">
						<div class="vbo-roomdet-head">
							<span><?php echo JText::_('VBEDITORDERCHILDREN'); ?></span>
						</div>
						<div class="vbo-roomdet-foot">
							<span><?php echo $arrpeople[$num]['children'].$age_str; ?></span>
						</div>
					</div>
					<div class="vbo-roomdet-entry">
						<div class="vbo-roomdet-head">
							<span><?php echo JText::_('VBEDITORDERSEVEN'); ?></span>
						</div>
						<div class="vbo-roomdet-foot">
						<?php
						if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
							?>
							<span>
							<?php
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
							?>
								<?php echo $currencysymb; ?> <?php echo VikBooking::numberFormat($or['cust_cost']); ?>
							</span>
							<?php
						} elseif (array_key_exists($num, $tars) && !empty($tars[$num][0]['idprice'])) {
							$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num][0]['cost'];
							?>
							<span>
								<?php echo VikBooking::getPriceName($tars[$num][0]['idprice']); ?> 
							<?php
							if (!empty($or['room_cost'])) {
								echo $currencysymb.' '.VikBooking::numberFormat(VikBooking::sayCostPlusIva($display_rate, $tars[$num][0]['idprice']));
							}
							?>
							</span>
							<?php
						}  elseif (!empty($or['otarplan'])) {
							?>
							<span><?php echo ucwords($or['otarplan']); ?></span>
							<?php
						} elseif ($row['closure'] < 1) {
							?>
							<span><?php echo JText::_('VBOROOMNORATE'); ?></span>
							<?php
						} else {
							?>
							<span>-----</span>
							<?php
						}
						?>
						</div>
					</div>
					<div class="vbo-roomdet-entry">
						<div class="vbo-roomdet-head">
							<span><?php echo JText::_('VBEDITORDEREIGHT'); ?></span>
						</div>
						<div class="vbo-roomdet-foot">
					<?php
					if (!empty($or['optionals'])) {
						$stepo = explode(";", $or['optionals']);
						foreach ($stepo as $oo) {
							if (empty($oo)) {
								continue;
							}
							$hide_price = false;
							$stept = explode(":", $oo);
							$actopt = VikBooking::getSingleOption($stept[0]);
							if (!(count($actopt) > 0)) {
								continue;
							}
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
										$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num][0]['cost'];
										$hide_price = empty($or['room_cost']) ? true : $hide_price;
										$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
									}
								} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
									//VBO 1.10 - percentage value of room base cost
									if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
										$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
									} else {
										$display_rate = isset($tars[$num][0]['room_base_cost']) ? $tars[$num][0]['room_base_cost'] : (!empty($or['room_cost']) ? $or['room_cost'] : $tars[$num][0]['cost']);
										$hide_price = empty($or['room_cost']) ? true : $hide_price;
										$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
									}
								}
								$actopt['chageintv'] = $chvar;
								$actopt['name'] .= ' ('.$optagenames[($chvar - 1)].')';
								$realcost = (intval($actopt['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $order['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
							} else {
								$realcost = (intval($actopt['perday']) == 1 ? ($actopt['cost'] * $order['days'] * $stept[1]) : ($actopt['cost'] * $stept[1]));
							}
							if ($actopt['maxprice'] > 0 && $realcost > $actopt['maxprice']) {
								$realcost = $actopt['maxprice'];
								if (intval($actopt['hmany']) == 1 && intval($stept[1]) > 1) {
									$realcost = $actopt['maxprice'] * $stept[1];
								}
							}
							$realcost = $actopt['perperson'] == 1 ? ($realcost * $arrpeople[$num]['adults']) : $realcost;
							$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt['idiva']);
							?>
							<div class="vbo-roomdet-foot-options">
								<?php echo ($stept[1] > 1 ? $stept[1]." " : "").$actopt['name'].(!$hide_price ? " ".$currencysymb." ".VikBooking::numberFormat($tmpopr) : ''); ?>
							</div>
							<?php
						}
					}
					?>
						</div>
					</div>
					<div class="vbo-roomdet-entry">
						<div class="vbo-roomdet-head">
							<span><?php echo JText::_('VBPEDITBUSYEXTRACOSTS'); ?></span>
						</div>
						<div class="vbo-roomdet-foot">
					<?php
					if (!empty($or['extracosts'])) {
						$cur_extra_costs = json_decode($or['extracosts'], true);
						foreach ($cur_extra_costs as $eck => $ecv) {
							?>
							<div class="vbo-roomdet-foot-extras">
								<?php echo $ecv['name']." ".$currencysymb." ".VikBooking::numberFormat(VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'])); ?>
							</div>
							<?php
						}
					}
					?>
						</div>
					</div>
				</div>
				<?php
				if ($arrpeople[$num]['adults'] > 0) {
					?>
				<div class="vbo-roomdet-guests-details" id="vbo-roomdet-guests-details-<?php echo $ind; ?>">
					<?php
					list($pax_fields, $pax_fields_attributes) = VikBooking::getPaxFields();
					for ($g = 1; $g <= $arrpeople[$num]['adults']; $g++) {
						$current_guest = array();
						if (count($pax_data) && isset($pax_data[$ind]) && isset($pax_data[$ind][$g])) {
							$current_guest = $pax_data[$ind][$g];
						} elseif ($ind < 1 && $g == 1 && count($customer)) {
							$current_guest = $customer;
						}
						?>
					<div class="vbo-roomdet-guest-details" data-roomind="<?php echo $ind; ?>" data-totguests="<?php echo $arrpeople[$num]['adults']; ?>">
						<div class="vbo-roomdet-guest-detail vbo-roomdet-guest-detail-num">
							<span><?php echo JText::sprintf('VBOGUESTNUM', $g); ?></span>
						</div>
						<?php
						$pax_field_ind = 1;
						foreach ($pax_fields as $paxk => $paxv) {
							$pax_field_class = 'vbo-paxfield';
							if ($pax_field_ind < 3) {
								//use the first two fields to check via JS whether the Guests details are empty
								$pax_field_class .= ' vbo-paxfield-'.$ind;
							}
							?>
						<div class="vbo-roomdet-guest-detail">
							<?php
							if ($paxk == 'country') {
								echo VikBooking::getCountriesSelect('guests['.$ind.']['.$g.']['.$paxk.']', $all_countries, (isset($current_guest[$paxk]) ? $current_guest[$paxk] : ''), $paxv);
							} else {
								?>
							<input type="text" autocomplete="off" data-gind="<?php echo $g; ?>" class="<?php echo $pax_field_class; ?>" name="guests[<?php echo $ind; ?>][<?php echo $g; ?>][<?php echo $paxk; ?>]" value="<?php echo (isset($current_guest[$paxk]) ? $current_guest[$paxk] : ''); ?>" placeholder="<?php echo $paxv; ?>" <?php echo $pax_fields_attributes[$paxk]; ?> />
								<?php
							}
							?>
						</div>
							<?php
							$pax_field_ind++;
						}
						?>
					</div>
						<?php
					}
					?>
				</div>
			</div>
					<?php
				}
			}
			?>
		</div>

		<div class="vbo-checkin-payment-container">
			<div class="vbo-checkin-payment-detail">
			<?php
			$bcolortag = VikBooking::applyBookingColorTag($order, $colortags);
			$usectag = '';
			if (count($bcolortag) > 0) {
				$bcolortag['name'] = JText::_($bcolortag['name']);
				$usectag = '<span class="vbo-colortag-circle hasTooltip" style="background-color: '.$bcolortag['color'].';" title="'.htmlspecialchars($bcolortag['name']).'"></span> ';
			}
			?>
				<span class="vbo-checkin-payment-detail-lbl"><?php echo $usectag; ?><strong><?php echo JText::_('VBEDITORDERNINE'); ?></strong></span>
				<span class="vbo-checkin-payment-detail-v"><?php echo (strlen($otacurrency) > 0 ? '('.$otacurrency.') '.$currencysymb : $currencysymb); ?> <?php echo VikBooking::numberFormat($order['total']); ?></span>
			</div>
		<?php
		if (!empty($order['totpaid']) && $order['totpaid'] > 0) {
			$diff_to_pay = $order['total'] - $order['totpaid'];
			?>
			<div class="vbo-checkin-payment-detail">
				<span class="vbo-checkin-payment-detail-lbl"><?php echo JText::_('VBAMOUNTPAID'); ?></span>
				<span class="vbo-checkin-payment-detail-v"><?php echo $currencysymb; ?> <?php echo VikBooking::numberFormat($order['totpaid']); ?></span>
			</div>
			<?php
			if ($diff_to_pay > 1) {
				?>
			<div class="vbo-checkin-payment-detail">
				<span class="vbo-checkin-payment-detail-lbl"><?php echo JText::_('VBTOTALREMAINING'); ?></span>
				<span class="vbo-checkin-payment-detail-v"><?php echo $currencysymb; ?> <span id="vbo-checkin-remaining"><?php echo VikBooking::numberFormat($diff_to_pay); ?></span></span>
			</div>
			<div class="vbo-checkin-payment-detail">
				<span class="vbo-checkin-payment-detail-lbl"><?php echo JText::_('VBONEWAMOUNTPAID'); ?></span>
				<span class="vbo-checkin-payment-detail-v"><?php echo $currencysymb; ?> <input name="newtotpaid" id="newtotpaid" value="" min="0" type="number" step="any" style="margin: 0;"></span>
			</div>
				<?php
			}
		}
		$payment = VikBooking::getPayment($order['idpayment']);
		if (is_array($payment)) {
			?>
			<div class="vbo-checkin-payment-detail">
				<span class="vbo-checkin-payment-detail-lbl"><?php echo JText::_('VBPAYMENTMETHOD'); ?></span>
				<span class="vbo-checkin-payment-detail-v"><?php echo $payment['name']; ?></span>
			</div>
			<?php
		}
		if (!empty($order['paymentlog'])) {
			?>
			<div class="vbo-checkin-payment-detail">
				<span class="vbo-checkin-payment-detail-lbl vbo-checkin-payment-detail-click" onclick="vboUpdateModal('<?php echo addslashes(JText::_('VBPAYMENTLOGTOGGLE')); ?>', '.paymentlog', true);">
					<i class="vboicn-credit-card"></i> <?php echo JText::_('VBPAYMENTLOGTOGGLE'); ?>
				</span>
			</div>
			<?php
		}
		?>
		</div>
	</div>

	<div class="vbo-checkin-notes-wrap">
		<div class="vbo-checkin-notes-inner">
			<div class="vbo-checkin-notes-trig">
				<span onclick="vboToggleCheckinNotes();"><i class="<?php echo count($customer) && !empty($customer['comments']) ? 'vboicn-bubbles2' : 'vboicn-bubble2'; ?>"></i> <?php echo JText::_('VBOTOGGLECHECKINNOTES'); ?></span>
			</div>
			<div class="vbo-checkin-notes-cont">
				<textarea name="comments"><?php echo count($customer) && isset($customer['comments']) ? htmlspecialchars($customer['comments']) : ''; ?></textarea>
			</div>
		</div>
	</div>

	<div class="vbo-checkin-update-wrap">
		<div class="vbo-checkin-update-inner">
			<div>
				<button type="button" class="btn btn-large btn-primary" onclick="jQuery('#adminForm').submit();"><i class="vboicn-checkmark"></i> <?php echo JText::_('VBOCHECKINUPDATEBTN'); ?></button>
			</div>
		</div>
	</div>

	<div class="vbo-checkin-commands-wrap">
		<div class="vbo-checkin-commands-inner">
		<?php
		if ($today_midnight < $order['checkout']) {
			$allowed_btns = array();
			if ($order['checked'] <= 0) {
				//check-in btn only for no-status or no-show
				$allowed_btns[] = 1;
			}
			if ($order['checked'] < 0 && !empty($order['channel']) && strpos($order['channel'], 'booking.com') !== false && VikBooking::vcmBcomReportingSupported()) {
				//report no show to Booking.com btn only for no-status
				$allowed_btns[] = -11;
			}
			if ($order['checked'] == 0) {
				//no-show button only if no-status
				$allowed_btns[] = -1;
			}
			if ($order['checked'] == 1 && (count($customer) && empty($customer['checkindoc']))) {
				//generate check-in doc button only for checked-in and no check-in doc
				$allowed_btns[] = 11;
			} elseif ($order['checked'] > 0 && (count($customer) && !empty($customer['checkindoc']))) {
				//download check-in doc button only for checked-in||out and check-in doc
				$allowed_btns[] = 12;
				//add also the possibility of re-creating the document
				$allowed_btns[] = 11;
			}
			if ($order['checked'] == 1 && $today_midnight > $order['checkin']) {
				//check-out button only for checked-in and after the check-in day
				$allowed_btns[] = 2;
			}
			if ($order['checked'] != 0) {
				//cancel button only if not no-status
				$allowed_btns[] = 0;
			}
			//print buttons
			foreach ($allowed_btns as $chstatus) {
				switch ($chstatus) {
					case -1:
						?>
			<div class="vbo-checkin-commands-btn">
				<button type="button" onclick="vboSetCheckinAction(-1);" class="btn btn-large btn-danger"><i class="vboicn-blocked"></i> <?php echo JText::_('VBOSETCHECKEDSTATUSNOS'); ?></button>
			</div>
						<?php
						break;
					case 0:
						?>
			<div class="vbo-checkin-commands-btn">
				<button type="button" onclick="vboSetCheckinAction(0);" class="btn btn-large"><i class="vboicn-cancel-circle"></i> <?php echo JText::_('VBOSETCHECKEDSTATUSZERO'); ?></button>
			</div>
						<?php
						break;
					case 1:
						?>
			<div class="vbo-checkin-commands-btn">
				<button type="button" onclick="vboSetCheckinAction(1);" class="btn btn-large btn-success"><i class="vboicn-checkmark"></i> <?php echo JText::_('VBOSETCHECKEDSTATUSIN'); ?></button>
			</div>
						<?php
						break;
					case 2:
						?>
			<div class="vbo-checkin-commands-btn">
				<button type="button" onclick="vboSetCheckinAction(2);" class="btn btn-large btn-success"><i class="vboicn-exit"></i> <?php echo JText::_('VBOSETCHECKEDSTATUSOUT'); ?></button>
			</div>
						<?php
						break;
					case 11:
						?>
			<div class="vbo-checkin-commands-btn">
				<a href="index.php?option=com_vikbooking&task=gencheckindoc&cid[]=<?php echo $order['id'].($tmpl == 'component' ? '&tmpl=component' : ''); ?>" class="btn btn-large btn-success"><i class="vboicn-profile"></i> <?php echo JText::_('VBOGENCHECKINDOC'); ?></a>
			</div>
						<?php
						break;
					case 12:
						?>
			<div class="vbo-checkin-commands-btn">
				<button type="button" onclick="window.open('<?php echo VBO_SITE_URI.'helpers/checkins/generated/'.$customer['checkindoc']; ?>', '_blank');" class="btn btn-large btn-success"><i class="vboicn-download"></i> <?php echo JText::_('VBODWNLCHECKINDOC'); ?></button>
			</div>
						<?php
						break;
					case -11:
						?>
			<div class="vbo-checkin-commands-btn">
				<button type="button" onclick="if(confirm('<?php echo addslashes(JText::_('VBOBCOMREPORTNOSHOWCONF')); ?>')){window.open('<?php echo JURI::root().'administrator/index.php?option=com_vikchannelmanager&task=breporting.noShow&otaid='.$order['idorderota']; ?>', '_blank');}" class="btn btn-large btn-danger"><i class="fa fa-ban"></i> <?php echo JText::_('VBOBCOMREPORTNOSHOW'); ?></button>
			</div>
						<?php
						break;
					default:
						break;
				}
			}
		} else {
			?>
			<p class="warn"><?php echo JText::_('VBOCHECKINTIMEOVER'); ?></p>
			<?php
			//if the document exists, print the download button even if the check-out date is in the past
			if ($order['checked'] != 0 && (count($customer) && !empty($customer['checkindoc']))) {
				//download check-in doc button only for checked-in||out and check-in doc
				?>
			<div class="vbo-checkin-commands-btn">
				<button type="button" onclick="window.open('<?php echo VBO_SITE_URI.'helpers/checkins/generated/'.$customer['checkindoc']; ?>', '_blank');" class="btn btn-large btn-success"><i class="vboicn-download"></i> <?php echo JText::_('VBODWNLCHECKINDOC'); ?></button>
			</div>
				<?php
			}
		}
		?>
		</div>
	</div>
	<input type="hidden" name="cid[]" value="<?php echo $order['id']; ?>">
	<input type="hidden" name="checkin_action" id="vbo-checkin-action" value="<?php echo $order['checked']; ?>">
	<input type="hidden" name="task" value="updatebookingcheckin" />
	<input type="hidden" name="option" value="com_vikbooking" />
		<?php
		if ($tmpl == 'component') {
		?>
	<input type="hidden" name="tmpl" value="component" />
		<?php
		}
		?>
</form>
		<?php
		//
		?>
	<script type="text/javascript">
	var vbo_overlay_data = {};
	<?php
	if (!empty($order['adminnotes'])) {
		$order['adminnotes'] = VikBooking::strTrimLiteral(nl2br(htmlspecialchars($order['adminnotes'])));
		?>
	vbo_overlay_data['adminnotes'] = '<pre><?php echo addslashes($order['adminnotes']); ?></pre>';
		<?php
	}
	if (!empty($order['paymentlog'])) {
		$plain_log = htmlspecialchars($order['paymentlog']);
		$order['paymentlog'] = VikBooking::strTrimLiteral(nl2br(htmlspecialchars($order['paymentlog'])));
		?>
	vbo_overlay_data['paymentlog'] = '<pre><?php echo addslashes($order['paymentlog']); ?></pre>';
		<?php
		//PCI Data Retrieval
		if (!empty($order['idorderota']) && !empty($order['channel'])) {
			$channel_source = $order['channel'];
			if (strpos($order['channel'], '_') !== false) {
				$channelparts = explode('_', $order['channel']);
				$channel_source = $channelparts[0];
			}
			//Limit set to Check-out date at 29:59:59
			$checkout_info = getdate($order['checkout']);
			$checkout_midnight = mktime(23, 59, 59, $checkout_info['mon'], $checkout_info['mday'], $checkout_info['year']);
			if (time() < $checkout_midnight) {
				if (stripos($plain_log, 'card number') !== false && strpos($plain_log, '****') !== false) {
					//log contains credit card details
					?>
	var pci_vcm_frame = '<iframe src="index.php?option=com_vikchannelmanager&task=execpcid&channel_source=<?php echo $channel_source; ?>&otaid=<?php echo $order['idorderota']; ?>&tmpl=component"></iframe>';
	vbo_overlay_data['paymentlog'] = '<div class="vcm-notif-pcidrq-container">'+
			'<a class="vcm-pcid-launch" href="javascript: void(0);" onclick="vboUpdateModal(\'<?php echo addslashes(JText::_('VBPAYMENTLOGTOGGLE')); ?>\', pci_vcm_frame, false);">'+
				'<?php echo addslashes(JText::_('GETFULLCARDDETAILS')); ?>'+
			'</a>'+
		'</div>'+
		vbo_overlay_data['paymentlog'];
					<?php
				}
			}
		}
		//
	}
	if (count($customer) && !empty($customer['notes'])) {
		$customer['notes'] = VikBooking::strTrimLiteral(nl2br(htmlspecialchars($customer['notes'])));
		?>
	vbo_overlay_data['customer_notes'] = '<pre><?php echo addslashes($customer['notes']); ?></pre>';
		<?php
	}
	if (count($customer)) {
		$displayable_fields = array(
			'first_name' => JText::_('VBCUSTOMERFIRSTNAME'),
			'last_name' => JText::_('VBCUSTOMERLASTNAME'),
			'company' => JText::_('VBCUSTOMERCOMPANY'),
			'vat' => JText::_('VBCUSTOMERCOMPANYVAT'),
			'email' => JText::_('VBCUSTOMEREMAIL'),
			'phone' => JText::_('VBCUSTOMERPHONE'),
			'address' => JText::_('VBCUSTOMERADDRESS'),
			'city' => JText::_('VBCUSTOMERCITY'),
			'zip' => JText::_('VBCUSTOMERZIP'),
			'country' => JText::_('VBCUSTOMERCOUNTRY'),
			'gender' => JText::_('VBCUSTOMERGENDER'),
			'bdate' => JText::_('VBCUSTOMERBDATE'),
			'pbirth' => JText::_('VBCUSTOMERPBIRTH'),
			'doctype' => JText::_('VBCUSTOMERDOCTYPE'),
			'docnum' => JText::_('VBCUSTOMERDOCNUM')
		);
		?>
	vbo_overlay_data['customer_info'] = '<button type="button" class="btn btn-primary pull-right" onclick="document.location.href = \'index.php?option=com_vikbooking&task=editcustomer&cid[]=<?php echo $customer['id'].'&checkin=1&bid='.$order['id'].($tmpl == 'component' ? '&tmpl=component' : ''); ?>\';"><i class="vboicn-profile"></i> <?php echo addslashes(JText::_('VBMAINCUSTOMEREDIT')); ?></button>'+
	'<div class="vbo-checkin-custdet-cont">'+
		<?php
		foreach ($displayable_fields as $k => $v) {
			$customer_val = isset($customer[$k]) && !empty($customer[$k]) ? addslashes(VikBooking::strTrimLiteral(nl2br(htmlspecialchars($customer[$k])))) : '---';
		?>
		'<div class="vbo-checkin-custdet-entry">'+
			'<span class="vbo-checkin-custdet-key<?php echo (!isset($customer[$k]) || empty($customer[$k])) && in_array($k, $sensible_k) ? ' vbo-checkin-custdet-key-warn' : ''; ?>"><?php echo addslashes(JText::_($v)); ?></span>'+
			'<span class="vbo-checkin-custdet-value"><?php echo $customer_val; ?></span>'+
		'</div>'+
		<?php
		}
		?>
	'</div>';
		<?php
	}
	if (!empty($set_parent_status)) {
		?>
	if (jQuery('#status-<?php echo $order['id']; ?>', window.parent.document).length) {
		jQuery('#status-<?php echo $order['id']; ?>', window.parent.document).html('<?php echo str_replace("'", "", $set_parent_status); ?>');
	}
		<?php
	}
	?>
	</script>
	<script type="text/javascript">
	/* Global Variables and Functions */
	var vbo_overlay_on = false;
	var booking_total = <?php echo (float)$order['total']; ?>;
	var tot_rooms = <?php echo (int)$order['roomsnum']; ?>;
	var current_checked = <?php echo (int)$order['checked']; ?>;
	var something_changed = false;
	function vboOpenModal() {
		jQuery(".vbo-info-overlay-block").fadeIn(400, function() {
			if (jQuery(".vbo-info-overlay-block").is(":visible")) {
				vbo_overlay_on = true;
			} else {
				vbo_overlay_on = false;
				jQuery('.vbo-overlay-checkin-body').html('');
			}
		});
	}
	function vboUpdateModal(title, body, call_toggle) {
		jQuery('#vbo-overlay-title').text(title);
		if (body.substr(0, 1) == '.') {
			//look for this value inside the global array
			body = body.substr(1, (body.length - 1));
			if (vbo_overlay_data.hasOwnProperty(body)) {
				body = vbo_overlay_data[body];
			}
		}
		jQuery('.vbo-overlay-checkin-body').html(body);
		if (call_toggle) {
			vboOpenModal();
		}
	}
	function vboToggleCheckinNotes() {
		jQuery('.vbo-checkin-notes-cont').toggle();
	}
	function vboSetCheckinAction(action) {
		jQuery('#vbo-checkin-action').val(action);
		if (action > 0) {
			//check if guests details were filled in for check-in/out actions
			var guests_filled = true;
			for (var i = 0; i < tot_rooms; i++) {
				var elem = jQuery(".vbo-roomdet-guests-toggle[data-roomind='"+i+"']");
				if (elem.length && elem.hasClass('vbo-guestscount-incomplete')) {
					guests_filled = false;
					break;
				}
			}
			if (!guests_filled) {
				if (confirm('<?php echo addslashes(JText::_('VBOCHECKINACTCONFGUESTS')) ?>')) {
					jQuery('#adminForm').submit();
				}
				return true;
			}
		}
		jQuery('#adminForm').submit();
	}
	if (jQuery.isFunction(jQuery.fn.tooltip)) {
		jQuery(".hasTooltip").tooltip();
	} else {
		jQuery.fn.tooltip = function(){};
	}
	/* ---------------- */
	jQuery(document).ready(function() {
		/* Guests Details events - Start */
		jQuery('.vbo-roomdet-guests-toggle').focus(function(e) {
			e.stopPropagation();
			e.preventDefault();
			var roomind = jQuery(this).attr('data-roomind');
			var elem = jQuery('#vbo-roomdet-guests-details-'+roomind);
			if (elem.length) {
				elem.slideToggle();
			}
		});
		jQuery('.vbo-roomdet-guests-toggle').dblclick(function() {
			var roomind = jQuery(this).attr('data-roomind');
			var elem = jQuery('#vbo-roomdet-guests-details-'+roomind);
			if (elem.length && elem.is(':hidden')) {
				elem.slideToggle();
			}
		});
		jQuery('.vbo-paxfield').keyup(function() {
			var cur_gind = jQuery(this).attr('data-gind');
			var roomind = jQuery(this).closest('.vbo-roomdet-guest-details').attr('data-roomind');
			var tot_room_guests = parseInt(jQuery(this).closest('.vbo-roomdet-guest-details').attr('data-totguests'));
			if (!cur_gind.length || isNaN(tot_room_guests) || !jQuery(this).hasClass('vbo-paxfield-'+roomind)) {
				return true;
			}
			var cur_val = jQuery(this).val();
			var tot_full_guests = 0;
			for (var i = 1; i <= tot_room_guests; i++) {
				var fullfilled = true;
				jQuery(".vbo-paxfield-"+roomind+"[data-gind='"+i+"']").each(function(k, v) {
					if (!jQuery(this).val().length) {
						fullfilled = false;
					}
				});
				if (fullfilled) {
					tot_full_guests++;
				}
			}
			if (tot_full_guests >= tot_room_guests) {
				var add_class = 'vbo-guestscount-complete';
				var rm_class = 'vbo-guestscount-incomplete';
				var ico_add_class = 'vboicn-user-check';
				var ico_rm_class = 'vboicn-user-plus';
			} else {
				var add_class = 'vbo-guestscount-incomplete';
				var rm_class = 'vbo-guestscount-complete';
				var ico_add_class = 'vboicn-user-plus';
				var ico_rm_class = 'vboicn-user-check';
			}
			jQuery('#vbo-guestscount-'+roomind).text(tot_full_guests).closest('.vbo-roomdet-guests-toggle').addClass(add_class).removeClass(rm_class).find('i').addClass(ico_add_class).removeClass(ico_rm_class);
		});
		/* Guests Details events - End */
		/* Overlay for Customer Notes, Booking Notes, Payment Logs - Start */
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
		/* Overlay for Customer Notes, Booking Notes, Payment Logs - End */
		/* Update amount paid and remaining balance */
		jQuery('#newtotpaid').change(function() {
			if (!jQuery('#vbo-checkin-remaining').length) {
				return true;
			}
			var cur_val = parseFloat(jQuery(this).val());
			if (!(cur_val > 0)) {
				return true;
			}
			var new_val = booking_total - cur_val;
			jQuery('#vbo-checkin-remaining').text(new_val.toFixed(2));
		});
		/* Listener for the change event of any input field to display the Update Information button */
		jQuery('input, textarea, select').keyup(function() {
			var dontupdateonly = <?php echo $today_midnight > $order['checkout'] ? 'false' : 'true'; ?>;
			if (current_checked == 0 && dontupdateonly) {
				return true;
			}
			if (!something_changed) {
				something_changed = true;
				jQuery('.vbo-checkin-update-wrap').show();
			}
		});
	});
	</script>