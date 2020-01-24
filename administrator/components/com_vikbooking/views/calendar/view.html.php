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

// import Joomla view library
jimport('joomla.application.component.view');

class VikBookingViewCalendar extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		$aid = $cid[0];

		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$rid = $session->get('vbCalRid', '');
		$aid = !empty($rid) && empty($aid) ? $rid : $aid;
		if (empty($aid)) {
			$q = "SELECT `id` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC LIMIT 1";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$aid = $dbo->loadResult();
			}
		}
		if (empty($aid)) {
			VikError::raiseWarning('', 'No Rooms.');
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
			exit;
		}

		$session->set('vbCalRid', $aid);
		$pvmode = VikRequest::getString('vmode', '', 'request');
		$cur_vmode = $session->get('vikbookingvmode', "");
		if (!empty($pvmode) && ctype_digit($pvmode)) {
			$session->set('vikbookingvmode', $pvmode);
		} elseif (empty($cur_vmode)) {
			$session->set('vikbookingvmode', "12");
		}
		$vmode = (int)$session->get('vikbookingvmode', "12");
		$q = "SELECT `id`,`name`,`img`,`units` FROM `#__vikbooking_rooms` WHERE `id`=".$dbo->quote($aid).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() != 1) {
			VikError::raiseWarning('', 'No Rooms.');
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
			exit;
		}
		$room = $dbo->loadAssoc();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_gpayments` ORDER BY `#__vikbooking_gpayments`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$payments = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : '';
		$msg = "";
		$actnow = time();
		$pcheckindate = VikRequest::getString('checkindate', '', 'request');
		$pcheckoutdate = VikRequest::getString('checkoutdate', '', 'request');
		$pcheckinh = VikRequest::getString('checkinh', '', 'request');
		$pcheckinm = VikRequest::getString('checkinm', '', 'request');
		$pcheckouth = VikRequest::getString('checkouth', '', 'request');
		$pcheckoutm = VikRequest::getString('checkoutm', '', 'request');
		$pcustdata = VikRequest::getString('custdata', '', 'request');
		$pcustmail = VikRequest::getString('custmail', '', 'request');
		$padults = VikRequest::getString('adults', '', 'request');
		$pchildren = VikRequest::getString('children', '', 'request');
		$psetclosed = VikRequest::getString('setclosed', '', 'request');
		$num_rooms = VikRequest::getInt('num_rooms', '', 'request');
		$num_rooms = empty($num_rooms) || $num_rooms <= 0 ? 1 : $num_rooms;
		$pordstatus = VikRequest::getString('newstatus', '', 'request');
		$pordstatus = (empty($pordstatus) || !in_array($pordstatus, array('confirmed', 'standby')) ? 'confirmed' : $pordstatus);
		$pordstatus = intval($psetclosed) > 0 ? 'confirmed' : $pordstatus;
		$pcountrycode = VikRequest::getString('countrycode', '', 'request');
		$pt_first_name = VikRequest::getString('t_first_name', '', 'request');
		$pt_last_name = VikRequest::getString('t_last_name', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcustomer_id = VikRequest::getString('customer_id', '', 'request');
		$ppaymentid = VikRequest::getString('payment', '', 'request');
		$pcust_cost = VikRequest::getFloat('cust_cost', 0, 'request');
		$ptaxid = VikRequest::getInt('taxid', '', 'request');
		$paymentmeth = '';
		if (!empty($ppaymentid) && is_array($payments)) {
			foreach ($payments as $pay) {
				if (intval($pay['id']) == intval($ppaymentid)) {
					$paymentmeth = $pay['id'].'='.$pay['name'];
					break;
				}
			}
		}
		if (!empty($pcheckindate) && !empty($pcheckoutdate)) {
			if (VikBooking::dateIsValid($pcheckindate) && VikBooking::dateIsValid($pcheckoutdate)) {
				$first = VikBooking::getDateTimestamp($pcheckindate, $pcheckinh, $pcheckinm);
				$second = VikBooking::getDateTimestamp($pcheckoutdate, $pcheckouth, $pcheckoutm);
				if ($second > $first) {
					$secdiff = $second - $first;
					$daysdiff = $secdiff / 86400;
					if (is_int($daysdiff)) {
						if ($daysdiff < 1) {
							$daysdiff = 1;
						}
					} else {
						if ($daysdiff < 1) {
							$daysdiff=1;
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
					//if the room is totally booked or locked because someone is paying, the administrator is not able to make a reservation for that room
					$check_units = $room['units'];
					if ($num_rooms > 1 && $num_rooms <= $room['units']) {
						$check_units = $room['units'] - $num_rooms + 1;
					}
					if (VikBooking::roomBookable($room['id'], $check_units, $first, $second) && VikBooking::roomNotLocked($room['id'], $room['units'], $first, $second)) {
						//Customer
						$q = "SELECT * FROM `#__vikbooking_custfields` ORDER BY `ordering` ASC;";
						$dbo->setQuery($q);
						$dbo->execute();
						$all_cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
						$customer_cfields = array();
						$customer_extrainfo = array();
						$custdata_parts = explode("\n", $pcustdata);
						foreach ($custdata_parts as $cdataline) {
							if (!(strlen(trim($cdataline)) > 0)) {
								continue;
							}
							$cdata_parts = explode(':', $cdataline);
							if (!count($cdata_parts) > 1 || !(strlen(trim($cdata_parts[0])) > 0) || !(strlen(trim($cdata_parts[1])) > 0)) {
								continue;
							}
							foreach ($all_cfields as $cf) {
								if (strpos($cdata_parts[0], JText::_($cf['name'])) !== false && !array_key_exists($cf['id'], $customer_cfields) && $cf['type'] != 'country') {
									$customer_cfields[$cf['id']] = trim($cdata_parts[1]);
									if (!empty($cf['flag'])) {
										$customer_extrainfo[$cf['flag']] = trim($cdata_parts[1]);
									}
									break;
								}
							}
						}
						$cpin = VikBooking::getCPinIstance();
						$cpin->is_admin = true;
						$cpin->setCustomerExtraInfo($customer_extrainfo);
						$cpin->saveCustomerDetails($pt_first_name, $pt_last_name, $pcustmail, $pphone, $pcountrycode, $customer_cfields);
						//
						$realback=VikBooking::getHoursRoomAvail() * 3600;
						$realback+=$second;
						$insertedbusy = array();
						$forend = 1;
						$or_forend = 1;
						$adults_map = array();
						$children_map = array();
						if (intval($psetclosed) > 0) {
							$forend = $room['units'];
						} elseif ($num_rooms > 1 && $num_rooms <= $room['units']) {
							$forend = $num_rooms;
							$or_forend = $num_rooms;
							//assign adults/children proportionally
							if ((intval($padults) + intval($pchildren)) < $num_rooms) {
								//the number of guests does not make much sense but we build the maps anyway
								for ($r = 1; $r <= $or_forend; $r++) {
									$adults_map[$r] = (int)$padults;
									$children_map[$r] = (int)$pchildren;
								}
							} else {
								$adults_per_room = floor(((int)$padults / $num_rooms));
								$adults_left = ((int)$padults % $num_rooms);
								$children_per_room = floor(((int)$pchildren / $num_rooms));
								$children_left = ((int)$pchildren % $num_rooms);
								for ($r = 1; $r <= $or_forend; $r++) {
									$adults_map[$r] = $adults_per_room;
									$children_map[$r] = $children_per_room;
									if ($r == $or_forend) {
										$adults_map[$r] += $adults_left;
										$children_map[$r] += $children_left;
									}
								}
							}
							//
						}
						//Calculate the order total if not empty cust_cost and > 0.00. Add taxes (if not empty), and consider the setting prices tax excluded to increase the total
						$set_total = 0;
						if (!empty($pcust_cost) && $pcust_cost > 0.00) {
							$set_total = $pcust_cost;
							if (!VikBooking::ivaInclusa() && $ptaxid > 0) {
								$q = "SELECT `i`.`aliq` FROM `#__vikbooking_iva` AS `i` WHERE `i`.`id`='" . (int)$ptaxid . "';";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() > 0) {
									$aliq = $dbo->loadResult();
									if (floatval($aliq) > 0.00) {
										$subt = 100 + (float)$aliq;
										$set_total = ($set_total * $subt / 100);
									}
								}
							}
						}
						//
						//Get current Joomla User ID
						$now_user = JFactory::getUser();
						$store_ujid = property_exists($now_user, 'id') && !empty($now_user->id) ? (int)$now_user->id : 0;
						//
						if ($pordstatus == 'confirmed') {
							for ($b = 1; $b <= $forend; $b++) {
								$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES('".$room['id']."','".$first."','".$second."','".$realback."');";
								$dbo->setQuery($q);
								$dbo->execute();
								$lid = $dbo->insertid();
								$insertedbusy[] = $lid;
							}
							if (count($insertedbusy) > 0) {
								$sid = VikBooking::getSecretLink();
								$q = "INSERT INTO `#__vikbooking_orders` (`custdata`,`ts`,`status`,`days`,`checkin`,`checkout`,`custmail`,`sid`,`idpayment`,`ujid`,`roomsnum`,`total`,`country`,`phone`,`closure`) VALUES(".$dbo->quote($pcustdata).",'".$actnow."','".$pordstatus."','".$daysdiff."','".$first."','".$second."',".$dbo->quote($pcustmail).",'".$sid."',".$dbo->quote($paymentmeth).",'".(int)$store_ujid."',".($num_rooms > 1 && $num_rooms <= $room['units'] ? $num_rooms : '1').",".($set_total > 0 ? $dbo->quote($set_total) : "NULL").",".$dbo->quote($pcountrycode).",".$dbo->quote($pphone).", ".(intval($psetclosed) > 0 ? '1' : '0').");";
								$dbo->setQuery($q);
								$dbo->execute();
								$newoid = $dbo->insertid();
								//ConfirmationNumber
								$confirmnumber = VikBooking::generateConfirmNumber($newoid, true);
								//end ConfirmationNumber
								//Assign room specific unit
								$set_room_indexes = VikBooking::autoRoomUnit();
								$room_indexes_usemap = array();
								//
								foreach ($insertedbusy as $lid) {
									$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES('".$newoid."','".$lid."');";
									$dbo->setQuery($q);
									$dbo->execute();
								}
								for ($r = 1; $r <= $or_forend; $r++) {
									//Assign room specific unit
									$room_indexes = $set_room_indexes === true ? VikBooking::getRoomUnitNumsAvailable(array('id' => $newoid, 'checkin' => $first, 'checkout' => $second), $room['id']) : array();
									$use_ind_key = 0;
									if (count($room_indexes)) {
										if (!array_key_exists($room['id'], $room_indexes_usemap)) {
											$room_indexes_usemap[$room['id']] = $use_ind_key;
										} else {
											$use_ind_key = $room_indexes_usemap[$room['id']];
										}
									}
									//
									$or_cust_cost = !empty($pcust_cost) && $pcust_cost > 0.00 ? $pcust_cost : 0;
									$or_cust_cost = $or_forend > 1 && $or_cust_cost > 0 ? round(($or_cust_cost / $or_forend), 2) : $or_cust_cost;
									$room_adults = isset($adults_map[$r]) ? $adults_map[$r] : (int)$padults;
									$room_children = isset($children_map[$r]) ? $children_map[$r] : (int)$pchildren;
									$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`,`t_first_name`,`t_last_name`,`roomindex`,`cust_cost`,`cust_idiva`) VALUES('".$newoid."','".$room['id']."','".$room_adults."','".$room_children."', ".$dbo->quote($pt_first_name).", ".$dbo->quote($pt_last_name).", ".(count($room_indexes) ? (int)$room_indexes[$use_ind_key] : "NULL").", ".(!empty($pcust_cost) && $pcust_cost > 0.00 ? $dbo->quote($or_cust_cost) : "NULL").", ".(!empty($pcust_cost) && $pcust_cost > 0.00 && !empty($ptaxid) ? $dbo->quote($ptaxid) : "NULL").");";
									$dbo->setQuery($q);
									$dbo->execute();
									//Assign room specific unit
									if (count($room_indexes)) {
										$room_indexes_usemap[$room['id']]++;
									}
									//
								}
								$msg = $newoid;
								//Customer Booking
								if (!(intval($cpin->getNewCustomerId()) > 0) && !empty($pcustomer_id) && !empty($pcustomer_pin)) {
									$cpin->setNewPin($pcustomer_pin);
									$cpin->setNewCustomerId($pcustomer_id);
								}
								$cpin->saveCustomerBooking($newoid);
								//end Customer Booking
								//Booking History
								VikBooking::getBookingHistoryInstance()->setBid($newoid)->store('NB');
								//
								//Invoke Channel Manager
								$vcm_autosync = VikBooking::vcmAutoUpdate();
								if ($vcm_autosync > 0) {
									$vcm_obj = VikBooking::getVcmInvoker();
									$vcm_obj->setOids(array($newoid))->setSyncType('new');
									$sync_result = $vcm_obj->doSync();
									if ($sync_result === false) {
										$vcm_err = $vcm_obj->getError();
										VikError::raiseWarning('', JText::_('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::_('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
									}
								} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php")) {
									$vcm_sync_url = 'index.php?option=com_vikbooking&task=invoke_vcm&stype=new&cid[]='.$newoid.'&returl='.urlencode('index.php?option=com_vikbooking&task=calendar&cid[]='.$aid);
									VikError::raiseNotice('', JText::_('VBCHANNELMANAGERINVOKEASK').' <button type="button" class="btn btn-primary" onclick="document.location.href=\''.$vcm_sync_url.'\';">'.JText::_('VBCHANNELMANAGERSENDRQ').'</button>');
								}
								//
							}
						} elseif ($pordstatus == 'standby') {
							$sid = VikBooking::getSecretLink();
							$q = "INSERT INTO `#__vikbooking_orders` (`custdata`,`ts`,`status`,`days`,`checkin`,`checkout`,`custmail`,`sid`,`idpayment`,`ujid`,`roomsnum`,`total`,`country`,`phone`) VALUES(".$dbo->quote($pcustdata).",'".$actnow."','".$pordstatus."','".$daysdiff."','".$first."','".$second."',".$dbo->quote($pcustmail).",'".$sid."',".$dbo->quote($paymentmeth).",'".(int)$store_ujid."',".($num_rooms > 1 && $num_rooms <= $room['units'] ? $num_rooms : '1').",".($set_total > 0 ? $dbo->quote($set_total) : "NULL").",".$dbo->quote($pcountrycode).",".$dbo->quote($pphone).");";
							$dbo->setQuery($q);
							$dbo->execute();
							$newoid = $dbo->insertid();
							//Customer Booking
							if (!(intval($cpin->getNewCustomerId()) > 0) && !empty($pcustomer_id) && !empty($pcustomer_pin)) {
								$cpin->setNewPin($pcustomer_pin);
								$cpin->setNewCustomerId($pcustomer_id);
							}
							$cpin->saveCustomerBooking($newoid);
							//
							$realback = (VikBooking::getHoursRoomAvail() * 3600) + $second;
							for ($r = 1; $r <= $or_forend; $r++) {
								$or_cust_cost = !empty($pcust_cost) && $pcust_cost > 0.00 ? $pcust_cost : 0;
								$or_cust_cost = $or_forend > 1 && $or_cust_cost > 0 ? round(($or_cust_cost / $or_forend), 2) : $or_cust_cost;
								$room_adults = isset($adults_map[$r]) ? $adults_map[$r] : (int)$padults;
								$room_children = isset($children_map[$r]) ? $children_map[$r] : (int)$pchildren;
								$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`,`t_first_name`,`t_last_name`,`cust_cost`,`cust_idiva`) VALUES('".$newoid."','".$room['id']."','".$room_adults."','".$room_children."', ".$dbo->quote($pt_first_name).", ".$dbo->quote($pt_last_name).", ".(!empty($pcust_cost) && $pcust_cost > 0.00 ? $dbo->quote($or_cust_cost) : "NULL").", ".(!empty($pcust_cost) && $pcust_cost > 0.00 && !empty($ptaxid) ? $dbo->quote($ptaxid) : "NULL").");";
								$dbo->setQuery($q);
								$dbo->execute();
								//lock room for pending status
								$q = "INSERT INTO `#__vikbooking_tmplock` (`idroom`,`checkin`,`checkout`,`until`,`realback`,`idorder`) VALUES(".$room['id'].",".$dbo->quote($first).",".$dbo->quote($second).",".$dbo->quote(VikBooking::getMinutesLock(true)).",".$realback.", ".(int)$newoid.");";
								$dbo->setQuery($q);
								$dbo->execute();
								//
							}
							//Booking History
							VikBooking::getBookingHistoryInstance()->setBid($newoid)->store('NB');
							//
							$app = JFactory::getApplication();
							$app->enqueueMessage(JText::_('VBQUICKRESWARNSTANDBY'));
							$mainframe = JFactory::getApplication();
							$mainframe->redirect("index.php?option=com_vikbooking&task=editbusy&cid[]=".$newoid);
						}
					} else {
						$msg = "0";
					}
				} else {
					VikError::raiseWarning('', 'Invalid Dates: current server time is '.date('Y-m-d H:i', $actnow).'. Reservation requested from '.date('Y-m-d H:i', $first).' to '.date('Y-m-d H:i', $second));
				}
			} else {
				VikError::raiseWarning('', 'Invalid Dates');
			}
		}
		
		$busy = "";
		$mints = mktime(0, 0, 0, date('m'), 1, date('Y'));
		$q = "SELECT `b`.*,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b` LEFT JOIN `#__vikbooking_ordersbusy` `ob` ON `ob`.`idbusy`=`b`.`id` WHERE `b`.`idroom`='".$room['id']."' AND (`b`.`checkin`>=".$mints." OR `b`.`checkout`>=".$mints.") AND `ob`.`idorder` IS NOT NULL;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$busy = $dbo->loadAssocList();
		}
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$allc = $dbo->loadAssocList();

		$this->room = &$room;
		$this->msg = &$msg;
		$this->allc = &$allc;
		$this->payments = &$payments;
		$this->busy = &$busy;
		$this->vmode = &$vmode;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINCALTITLE'), 'vikbooking');
		JToolBarHelper::cancel( 'canceledorder', JText::_('VBBACK'));
		JToolBarHelper::spacer();
	}

}
