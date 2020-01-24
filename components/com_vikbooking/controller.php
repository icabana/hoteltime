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

jimport('joomla.application.component.controller');

class VikBookingController extends JControllerVikBooking {
	function display($cachable = false, $urlparams = array()) {
		$view = VikRequest::getVar('view', '');
		switch ($view) {
			case 'roomslist':
			case 'roomdetails':
			case 'searchdetails':
			case 'loginregister':
			case 'orderslist':
			case 'promotions':
			case 'availability':
			case 'packageslist':
			case 'packagedetails':
			case 'searchsuggestions':
				VikRequest::setVar('view', $view);
				break;
			default:
				VikRequest::setVar('view', 'vikbooking');
		}
		parent::display();
	}

	function search() {
		VikRequest::setVar('view', 'search');
		parent::display();
	}

	function showprc() {
		VikRequest::setVar('view', 'showprc');
		parent::display();
	}

	function oconfirm() {
		$requirelogin = VikBooking::requireLogin();
		if($requirelogin) {
			if(VikBooking::userIsLogged()) {
				VikRequest::setVar('view', 'oconfirm');
			} else {
				VikRequest::setVar('view', 'loginregister');
			}
		} else {
			VikRequest::setVar('view', 'oconfirm');
		}
		parent::display();
	}
	
	function register() {
		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDBO();
		//user data
		$pname = VikRequest::getString('name', '', 'request');
		$plname = VikRequest::getString('lname', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pusername = VikRequest::getString('username', '', 'request');
		$ppassword = VikRequest::getString('password', '', 'request');
		$pconfpassword = VikRequest::getString('confpassword', '', 'request');
		//
		//order data
		$pitemid = VikRequest::getString('Itemid', '', 'request');
		$proomid = VikRequest::getVar('roomid', array());
		$pdays = VikRequest::getInt('days', '', 'request');
		$pcheckin = VikRequest::getInt('checkin', '', 'request');
		$pcheckout = VikRequest::getInt('checkout', '', 'request');
		$proomsnum = VikRequest::getInt('roomsnum', '', 'request');
		$padults = VikRequest::getVar('adults', array());
		$pchildren = VikRequest::getVar('children', array());
		$rooms = array();
		$arrpeople = array();
		for($ir = 1; $ir <= $proomsnum; $ir++) {
			$ind = $ir - 1;
			if (!empty($proomid[$ind])) {
				$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`='".intval($proomid[$ind])."' AND `avail`='1';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$takeroom = $dbo->loadAssocList();
					$rooms[$ir] = $takeroom[0];
				}
			}
			if (!empty($padults[$ind])) {
				$arrpeople[$ir]['adults'] = intval($padults[$ind]);
			} else {
				$arrpeople[$ir]['adults'] = 0;
			}
			if (!empty($pchildren[$ind])) {
				$arrpeople[$ir]['children'] = intval($pchildren[$ind]);
			} else {
				$arrpeople[$ir]['children'] = 0;
			}
		}
		$prices = array();
		foreach($rooms as $num => $r) {
			$ppriceid = VikRequest::getString('priceid'.$num, '', 'request');
			if (!empty($ppriceid)) {
				$prices[$num] = intval($ppriceid);
			}
		}
		$selopt = array();
		$q = "SELECT * FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$optionals = $dbo->loadAssocList();
			foreach($rooms as $num => $r) {
				foreach ($optionals as $opt) {
					if (!empty($opt['ageintervals']) && $arrpeople[$num]['children'] > 0) {
						$tmpvar = VikRequest::getVar('optid'.$num.$opt['id'], array(0));
						if (is_array($tmpvar) && count($tmpvar) > 0 && !empty($tmpvar[0])) {
							$optagecosts = VikBooking::getOptionIntervalsCosts($opt['ageintervals']);
							$optagenames = VikBooking::getOptionIntervalsAges($opt['ageintervals']);
							$optagepcent = VikBooking::getOptionIntervalsPercentage($opt['ageintervals']);
							$optorigname = $opt['name'];
							foreach ($tmpvar as $chvar) {
								$opt['quan'] = $chvar;
								$opt['chageintv'] = $chvar;
								//ignore calculation as percetage value to reconstruct the URL
								$opt['cost'] = $optagecosts[($chvar - 1)];
								$opt['name'] = $optorigname.' ('.$optagenames[($chvar - 1)].')';
								$selopt[$num][] = $opt;
							}
						}
					} else {
						$tmpvar = VikRequest::getString('optid'.$num.$opt['id'], '', 'request');
						if (!empty($tmpvar)) {
							$opt['quan'] = $tmpvar;
							$selopt[$num][] = $opt;
						}
					}
				}
			}
		}
		$strpriceid = "";
		foreach($prices as $num => $pid) {
			$strpriceid .= ($num > 1 ? "&" : "")."priceid".$num."=".$pid;
		}
		$stroptid = "";
		for($ir = 1; $ir <= $proomsnum; $ir++) {
			if (is_array($selopt[$ir])) {
				foreach($selopt[$ir] as $opt) {
					if (array_key_exists('chageintv', $opt)) {
						$stroptid .= "&optid".$ir.$opt['id']."[]=".$opt['chageintv'];
					} else {
						$stroptid .= "&optid".$ir.$opt['id']."=".$opt['quan'];
					}
				}
			}
		}
		$strroomid = "";
		foreach ($rooms as $num => $r) {
			$strroomid .= "&roomid[]=".$r['id'];
		}
		$straduchild = "";
		foreach ($arrpeople as $indroom => $aduch) {
			$straduchild .= "&adults[]=".$aduch['adults'];
			$straduchild .= "&children[]=".$aduch['children'];
		}
		
		$qstring = $strpriceid.$stroptid.$strroomid.$straduchild."&roomsnum=".$proomsnum."&days=".$pdays."&checkin=".$pcheckin."&checkout=".$pcheckout.(!empty($pitemid) ? "&Itemid=".$pitemid : "");
		//
		if (!VikBooking::userIsLogged()) {
			if (!empty($pname) && !empty($plname) && !empty($pusername) && !empty($pemail) && $ppassword == $pconfpassword) {
				//save user
				$newuserid=VikBooking::addJoomlaUser($pname." ".$plname, $pusername, $pemail, $ppassword);
				if ($newuserid!=false && strlen($newuserid)) {
					//registration success
					$credentials = array('username' => $pusername, 'password' => $ppassword );
					//autologin
					$mainframe->login($credentials);
					$currentUser = JFactory::getUser();
					$currentUser->setLastVisit(time());
					$currentUser->set('guest', 0);
					//
					$mainframe->redirect(JRoute::_('index.php?option=com_vikbooking&task=oconfirm&'.$qstring, false));
				} else {
					//error while saving new user
					VikError::raiseWarning('', JText::_('VBREGERRSAVING'));
					$mainframe->redirect(JRoute::_('index.php?option=com_vikbooking&view=loginregister&'.$qstring, false));
				}
			} else {
				//invalid data
				VikError::raiseWarning('', JText::_('VBREGERRINSDATA'));
				$mainframe->redirect(JRoute::_('index.php?option=com_vikbooking&view=loginregister&'.$qstring, false));
			}
		} else {
			//user is already logged in, proceed
			$mainframe->redirect(JRoute::_('index.php?option=com_vikbooking&task=oconfirm&'.$qstring, false));
		}
	}
	
	function saveorder() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		$vbo_tn = VikBooking::getTranslator();
		$prooms = VikRequest::getVar('rooms', array());
		$proomsnum = VikRequest::getInt('roomsnum', '', 'request');
		$padults = VikRequest::getVar('adults', array());
		$pchildren = VikRequest::getVar('children', array());
		$pdays = VikRequest::getString('days', '', 'request');
		$pcouponcode = VikRequest::getString('couponcode', '', 'request');
		$pcheckin = VikRequest::getString('checkin', '', 'request');
		$pcheckout = VikRequest::getString('checkout', '', 'request');
		$pprtar = VikRequest::getVar('prtar', array());
		$ppriceid = VikRequest::getVar('priceid', array());
		$poptionals = VikRequest::getString('optionals', '', 'request');
		$ptotdue = VikRequest::getString('totdue', '', 'request');
		$pgpayid = VikRequest::getString('gpayid', '', 'request');
		$ppkg_id = VikRequest::getInt('pkg_id', '', 'request');
		$pnodep = VikRequest::getInt('nodep', '', 'request');
		$pitemid = VikRequest::getInt('Itemid', '', 'request');
		$validtoken = true;
		if (VikBooking::tokenForm()) {
			$validtoken = false;
			$pviktoken = VikRequest::getString('viktoken', '', 'request');
			$sessvbtkn = $session->get('vikbtoken', '');
			if (!empty($pviktoken) && $sessvbtkn == $pviktoken) {
				$session->set('vikbtoken', '');
				$validtoken = true;
			}
		}
		if ($validtoken) {
			$q = "SELECT * FROM `#__vikbooking_custfields` ORDER BY `#__vikbooking_custfields`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
			$suffdata = true;
			$useremail = "";
			$usercountry = '';
			$nominatives = array();
			$t_first_name = '';
			$t_last_name = '';
			$phone_number = '';
			$fieldflags = array();
			if (@ is_array($cfields)) {
				$vbo_tn->translateContents($cfields, '#__vikbooking_custfields');
				foreach ($cfields as $cf) {
					if (intval($cf['required']) == 1 && $cf['type'] != 'separator') {
						$tmpcfval = VikRequest::getString('vbf' . $cf['id'], '', 'request');
						if (strlen(str_replace(" ", "", trim($tmpcfval))) <= 0) {
							$suffdata = false;
							break;
						}
					}
				}
				//save user email, nominatives, phone number and create custdata array
				$arrcustdata = array();
				$arrcfields = array();
				$emailwasfound = false;
				foreach ($cfields as $cf) {
					if (intval($cf['isemail']) == 1 && $emailwasfound == false) {
						$useremail = trim(VikRequest::getString('vbf' . $cf['id'], '', 'request'));
						$emailwasfound = true;
					}
					if ($cf['isnominative'] == 1) {
						$tmpcfval = VikRequest::getString('vbf' . $cf['id'], '', 'request');
						if (strlen(str_replace(" ", "", trim($tmpcfval))) > 0) {
							$nominatives[] = $tmpcfval;
						}
					}
					if ($cf['isphone'] == 1) {
						$tmpcfval = VikRequest::getString('vbf' . $cf['id'], '', 'request');
						if (strlen(str_replace(" ", "", trim($tmpcfval))) > 0) {
							$phone_number = $tmpcfval;
						}
					}
					if (!empty($cf['flag'])) {
						$tmpcfval = VikRequest::getString('vbf' . $cf['id'], '', 'request');
						if (strlen(str_replace(" ", "", trim($tmpcfval))) > 0) {
							$fieldflags[$cf['flag']] = $tmpcfval;
						}
					}
					if($cf['type'] != 'separator' && $cf['type'] != 'country' && ( $cf['type'] != 'checkbox' || ($cf['type'] == 'checkbox' && intval($cf['required']) != 1) ) ) {
						$arrcustdata[JText::_($cf['name'])] = VikRequest::getString('vbf' . $cf['id'], '', 'request');
						$arrcfields[$cf['id']] = VikRequest::getString('vbf' . $cf['id'], '', 'request');
					} elseif ($cf['type'] == 'country') {
						$countryval = VikRequest::getString('vbf' . $cf['id'], '', 'request');
						if (!empty($countryval) && strstr($countryval, '::') !== false) {
							$countryparts = explode('::', $countryval);
							$usercountry = $countryparts[0];
							$arrcustdata[JText::_($cf['name'])] = $countryparts[1];
						} else {
							$arrcustdata[JText::_($cf['name'])] = '';
						}
					}
				}
				//
			}
			if(!empty($phone_number) && !empty($usercountry)) {
				$phone_number = VikBooking::checkPhonePrefixCountry($phone_number, $usercountry);
			}
			if ($suffdata === true) {
				if(count($nominatives) >= 2) {
					$t_last_name = array_pop($nominatives);
					$t_first_name = array_pop($nominatives);
				}
				if (VikBooking::dayValidTs($pdays, $pcheckin, $pcheckout)) {
					$currencyname = VikBooking::getCurrencyName();
					$rooms = array();
					$prices = array();
					$arrpeople = array();
					for($ir = 1; $ir <= $proomsnum; $ir++) {
						$ind = $ir - 1;
						if (!empty($prooms[$ind])) {
							$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`='".intval($prooms[$ind])."' AND `avail`='1';";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$takeroom = $dbo->loadAssocList();
								$rooms[$ir] = $takeroom[0];
							}
						}
						if (!empty($padults[$ind])) {
							$arrpeople[$ir]['adults'] = intval($padults[$ind]);
						} else {
							$arrpeople[$ir]['adults'] = 0;
						}
						if (!empty($pchildren[$ind])) {
							$arrpeople[$ir]['children'] = intval($pchildren[$ind]);
						} else {
							$arrpeople[$ir]['children'] = 0;
						}
						$prices[$ir] = intval($ppriceid[$ind]);
					}
					if (count($rooms) != $proomsnum) {
						VikError::raiseWarning('', JText::_('VBROOMNOTFND'));
						$mainframe->redirect(JRoute::_('index.php?option=com_vikbooking'));
						exit;
					}
					$vbo_tn->translateContents($rooms, '#__vikbooking_rooms');
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
					//Package
					$pkg = array();
					if (!empty($ppkg_id)) {
						$pkg = VikBooking::validateRoomPackage($ppkg_id, $rooms, $daysdiff, $pcheckin, $pcheckout);
						if (!is_array($pkg) || (is_array($pkg) && !(count($pkg) > 0)) ) {
							if (!is_array($pkg)) {
								VikError::raiseWarning('', $pkg);
							}
							$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&view=packagedetails&pkgid=".$ppkg_id.(!empty($pitemid) ? "&Itemid=".$pitemid : ""), false));
							exit;
						}
					}
					//
					$tars = array();
					$validfares = true;
					foreach ($rooms as $num => $r) {
						if (count($pkg) > 0) {
							break;
						}
						$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `idroom`='" . $r['id'] . "' AND `days`='" . $daysdiff . "' AND `idprice`='" . $prices[$num] . "';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
							$tar = VikBooking::applySeasonsRoom($tar, $pcheckin, $pcheckout);
							//different usage
							if ($r['fromadult'] <= $arrpeople[$num]['adults'] && $r['toadult'] >= $arrpeople[$num]['adults']) {
								$diffusageprice = VikBooking::loadAdultsDiff($r['id'], $arrpeople[$num]['adults']);
								//Occupancy Override
								$occ_ovr = VikBooking::occupancyOverrideExists($tar, $arrpeople[$num]['adults']);
								$diffusageprice = $occ_ovr !== false ? $occ_ovr : $diffusageprice;
								//
								if (is_array($diffusageprice)) {
									//set a charge or discount to the price(s) for the different usage of the room
									foreach($tar as $kpr => $vpr) {
										$tar[$kpr]['diffusage'] = $arrpeople[$num]['adults'];
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
						} else {
							$validfares = false;
							break;
						}
					}
					$is_package = (bool)(count($pkg) > 0);
					if ($validfares === true) {
						$isdue = 0;
						$tot_taxes = 0;
						$tot_city_taxes = 0;
						$tot_fees = 0;
						$rooms_costs_map = array();
						if($is_package === true) {
							foreach($rooms as $num => $r) {
								$pkg_cost = $pkg['pernight_total'] == 1 ? ($pkg['cost'] * $daysdiff) : $pkg['cost'];
								$pkg_cost = $pkg['perperson'] == 1 ? ($pkg_cost * ($arrpeople[$num]['adults'] > 0 ? $arrpeople[$num]['adults'] : 1)) : $pkg_cost;
								$cost_plus_tax = VikBooking::sayPackagePlusIva($pkg_cost, $pkg['idiva']);
								$isdue += $cost_plus_tax;
								if($cost_plus_tax == $pkg_cost) {
									$cost_minus_tax = VikBooking::sayPackageMinusIva($pkg_cost, $pkg['idiva']);
									$tot_taxes += ($pkg_cost - $cost_minus_tax);
								} else {
									$tot_taxes += ($cost_plus_tax - $pkg_cost);
								}
							}
						} else {
							foreach($tars as $num => $tar) {
								$cost_plus_tax = VikBooking::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice']);
								$isdue += $cost_plus_tax;
								if($cost_plus_tax == $tar[0]['cost']) {
									$cost_minus_tax = VikBooking::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice']);
									$tot_taxes += ($tar[0]['cost'] - $cost_minus_tax);
								} else {
									$tot_taxes += ($cost_plus_tax - $tar[0]['cost']);
								}
								$rooms_costs_map[$num] = $tar[0]['cost'];
							}
						}
						$selopt = array();
						$optstr = array();
						$children_age = array();
						if (!empty($poptionals)) {
							$stepo = explode(";", $poptionals);
							foreach ($stepo as $oo) {
								if (!empty($oo)) {
									$stept = explode(":", $oo);
									$rnoid = explode("_", $stept[0]);
									$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=" . $dbo->quote($rnoid[1]) . ";";
									$dbo->setQuery($q);
									$dbo->execute();
									if ($dbo->getNumRows() == 1) {
										$actopt = $dbo->loadAssocList();
										$vbo_tn->translateContents($actopt, '#__vikbooking_optionals');
										$chvar = '';
										if (!empty($actopt[0]['ageintervals']) && $arrpeople[$rnoid[0]]['children'] > 0 && strstr($stept[1], '-') != false) {
											$optagecosts = VikBooking::getOptionIntervalsCosts($actopt[0]['ageintervals']);
											$optagenames = VikBooking::getOptionIntervalsAges($actopt[0]['ageintervals']);
											$optagepcent = VikBooking::getOptionIntervalsPercentage($actopt[0]['ageintervals']);
											$agestept = explode('-', $stept[1]);
											$stept[1] = $agestept[0];
											$chvar = $agestept[1];
											if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
												//percentage value of the adults tariff
												if ($is_package === true) {
													$optagecosts[($chvar - 1)] = ($pkg['pernight_total'] == 1 ? ($pkg['cost'] * $daysdiff) : $pkg['cost']) * $optagecosts[($chvar - 1)] / 100;
												} else {
													$optagecosts[($chvar - 1)] = $tars[$rnoid[0]][0]['cost'] * $optagecosts[($chvar - 1)] / 100;
												}
											} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
												//VBO 1.10 - percentage value of room base cost
												if ($is_package === true) {
													$optagecosts[($chvar - 1)] = ($pkg['pernight_total'] == 1 ? ($pkg['cost'] * $daysdiff) : $pkg['cost']) * $optagecosts[($chvar - 1)] / 100;
												} else {
													$display_rate = isset($tars[$rnoid[0]][0]['room_base_cost']) ? $tars[$rnoid[0]][0]['room_base_cost'] : $tars[$rnoid[0]][0]['cost'];
													$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
												}
											}
											$actopt[0]['chageintv'] = $chvar;
											$actopt[0]['name'] .= ' ('.$optagenames[($chvar - 1)].')';
											$actopt[0]['quan'] = $stept[1];
											$selopt[$rnoid[0]][] = $actopt[0];
											$selopt['room'.$rnoid[0]] = $selopt['room'.$rnoid[0]].$actopt[0]['id'].":".$stept[1]."-".$chvar.";";
											$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $pdays * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
											$children_age[$rnoid[0]][] = array('ageinterval' => $optagenames[($chvar - 1)], 'age' => '', 'cost' => $realcost);
										} else {
											$actopt[0]['quan'] = $stept[1];
											$selopt[$rnoid[0]][] = $actopt[0];
											$selopt['room'.$rnoid[0]] = $selopt['room'.$rnoid[0]].$actopt[0]['id'].":".$stept[1].";";
											$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $pdays * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
										}
										if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
											$realcost = $actopt[0]['maxprice'];
											if(intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
												$realcost = $actopt[0]['maxprice'] * $stept[1];
											}
										}
										$realcost = ($actopt[0]['perperson'] == 1 ? ($realcost * $arrpeople[$rnoid[0]]['adults']) : $realcost);
										$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
										if ($actopt[0]['is_citytax'] == 1) {
											$tot_city_taxes += $tmpopr;
										} elseif ($actopt[0]['is_fee'] == 1) {
											$tot_fees += $tmpopr;
										} else {
											if ($tmpopr == $realcost) {
												$opt_minus_iva = VikBooking::sayOptionalsMinusIva($realcost, $actopt[0]['idiva']);
												$tot_taxes += ($realcost - $opt_minus_iva);
											} else {
												$tot_taxes += ($tmpopr - $realcost);
											}
										}
										$isdue += $tmpopr;
										$optstr[$rnoid[0]][] = ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": " . $tmpopr . " " . $currencyname . "\n";
									}
								}
							}
						}
						$origtotdue = $isdue;
						$usedcoupon = false;
						$strcouponeff = '';
						//coupon
						if (strlen($pcouponcode) > 0 && $is_package !== true) {
							$coupon = VikBooking::getCouponInfo($pcouponcode);
							if(is_array($coupon)) {
								$coupondateok = true;
								if(strlen($coupon['datevalid']) > 0) {
									$dateparts = explode("-", $coupon['datevalid']);
									$pickinfo = getdate($pcheckin);
									$dropinfo = getdate($pcheckout);
									$checkpick = mktime(0, 0, 0, $pickinfo['mon'], $pickinfo['mday'], $pickinfo['year']);
									$checkdrop = mktime(0, 0, 0, $dropinfo['mon'], $dropinfo['mday'], $dropinfo['year']);
									if(!($checkpick >= $dateparts[0] && $checkpick <= $dateparts[1] && $checkdrop >= $dateparts[0] && $checkdrop <= $dateparts[1])) {
										$coupondateok = false;
									}
								}
								if($coupondateok === true) {
									$couponroomok = true;
									if($coupon['allvehicles'] == 0) {
										foreach($rooms as $num => $r) {
											if(!(preg_match("/;".$r['id'].";/i", $coupon['idrooms']))) {
												$couponroomok = false;
												break;
											}
										}
									}
									if($couponroomok === true) {
										$coupontotok = true;
										if(strlen($coupon['mintotord']) > 0) {
											if($isdue < $coupon['mintotord']) {
												$coupontotok = false;
											}
										}
										if($coupontotok === true) {
											$usedcoupon = true;
											if($coupon['percentot'] == 1) {
												//percent value
												$minuscoupon = 100 - $coupon['value'];
												$coupondiscount = ($isdue - $tot_city_taxes - $tot_fees) * $coupon['value'] / 100;
												$isdue = ($isdue - $tot_taxes - $tot_city_taxes - $tot_fees) * $minuscoupon / 100;
												$tot_taxes = $tot_taxes * $minuscoupon / 100;
												$isdue += ($tot_taxes + $tot_city_taxes + $tot_fees);
											} else {
												//total value
												$coupondiscount = $coupon['value'];
												//isdue : taxes = coupon_discount : x
												$tax_prop = $tot_taxes * $coupon['value'] / $isdue;
												$tot_taxes -= $tax_prop;
												$tot_taxes = $tot_taxes < 0 ? 0 : $tot_taxes;
												$isdue -= $coupon['value'];
												$isdue = $isdue < 0 ? 0 : $isdue;
											}
											$strcouponeff = $coupon['id'].';'.$coupondiscount.';'.$coupon['code'];
										}
									}
								}
							}
						}
						//
						$strisdue = number_format($isdue, 2)."vikbooking";
						$ptotdue = number_format($ptotdue, 2)."vikbooking";
						if ($strisdue == $ptotdue) {
							//Pay full amount cookie (2 weeks)
							$nodep_set = !empty($pnodep) ? '1' : '0';
							$nodep_time_set = !empty($pnodep) ? (time() + (86400 * 14)) : (time() - (86400 * 14));
							$cookie = JFactory::getApplication()->input->cookie;
							$cookie->set( 'vboFA', $nodep_set, $nodep_time_set, '/' );
							//
							//VBO 1.10 - Modify booking
							$mod_booking = array();
							$skip_busy_ids = array();
							$cur_mod = $session->get('vboModBooking', '');
							if (is_array($cur_mod) && count($cur_mod)) {
								$mod_booking = $cur_mod;
								$skip_busy_ids = VikBooking::loadBookingBusyIds($mod_booking['id']);
							}
							//
							$nowts = time();
							$checkts = $nowts;
							$today_bookings = VikBooking::todayBookings();
							if ($today_bookings) {
								$checkts = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
							}
							if ($checkts <= $pcheckin && $checkts < $pcheckout && $pcheckin < $pcheckout) {
								$roomsavailable = true;
								foreach($rooms as $num => $r) {
									if (!VikBooking::roomNotLocked($r['id'], $r['units'], $pcheckin, $pcheckout)) {
										$roomsavailable = false;
										break;
									}
									if (!VikBooking::roomBookable($r['id'], $r['units'], $pcheckin, $pcheckout, $skip_busy_ids)) {
										$roomsavailable = false;
										break;
									}
								}
								if ($roomsavailable === true) {
									//save in session the checkin and checkout time of the reservation made
									$session->set('vikbooking_order_checkin', $pcheckin);
									$session->set('vikbooking_order_checkout', $pcheckout);
									//
									$sid = count($mod_booking) ? $mod_booking['sid'] : VikBooking::getSecretLink();
									$custdata = VikBooking::buildCustData($arrcustdata, "\r\n");
									$viklink = JURI::root() . "index.php?option=com_vikbooking&task=vieworder&sid=" . $sid . "&ts=" . $nowts . (!empty($pnodep) ? "&nodep=".$pnodep : "") . (!empty($pitemid) ? "&Itemid=" . $pitemid : "");
									$admail = VikBooking::getAdminMail();
									$ftitle = VikBooking::getFrontTitle();
									$pricestr = array();
									if ($is_package === true) {
										foreach ($rooms as $num => $r) {
											$pkg_cost = $pkg['pernight_total'] == 1 ? ($pkg['cost'] * $daysdiff) : $pkg['cost'];
											$pkg_cost = $pkg['perperson'] == 1 ? ($pkg_cost * ($arrpeople[$num]['adults'] > 0 ? $arrpeople[$num]['adults'] : 1)) : $pkg_cost;
											$cost_plus_tax = VikBooking::sayPackagePlusIva($pkg_cost, $pkg['idiva']);
											$pricestr[$num] = $pkg['name'].": ".$cost_plus_tax." ".$currencyname;
										}
									} else {
										foreach ($tars as $num => $tar) {
											$pricestr[$num] = VikBooking::getPriceName($tar[0]['idprice'], $vbo_tn) . ": " . VikBooking::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'])  . " " . $currencyname . (!empty($tar[0]['attrdata']) ? "\n" . VikBooking::getPriceAttr($tar[0]['idprice'], $vbo_tn) . ": " . $tar[0]['attrdata'] : "");
										}
									}
									$currentUser = JFactory::getUser();
									//VikBooking 1.5/1.6
									$langtag = $vbo_tn->current_lang;
									$vcmchanneldata = $session->get('vcmChannelData', '');
									$vcmchanneldata = !empty($vcmchanneldata) && is_array($vcmchanneldata) && count($vcmchanneldata) > 0 ? $vcmchanneldata : '';
									$cpin = VikBooking::getCPinIstance();
									$cpin->setCustomerExtraInfo($fieldflags);
									$cpin->saveCustomerDetails($t_first_name, $t_last_name, $useremail, $phone_number, $usercountry, $arrcfields);
									//
									$must_payment = count($mod_booking) ? false : VikBooking::areTherePayments();
									$payment = '';
									if ($must_payment) {
										$payment = VikBooking::getPayment($pgpayid);
									}
									if ($must_payment && !is_array($payment)) {
										//error, payment was not selected
										VikError::raiseWarning('', JText::_('ERRSELECTPAYMENT'));
										$strpriceid = "";
										foreach ($prices as $num => $pid) {
											$strpriceid .= "&priceid".$num."=".$pid;
										}
										$stroptid = "";
										for ($ir = 1; $ir <= $proomsnum; $ir++) {
											if (is_array($selopt[$ir])) {
												foreach ($selopt[$ir] as $opt) {
													if (array_key_exists('chageintv', $opt)) {
														$stroptid .= "&optid".$ir.$opt['id']."[]=".$opt['chageintv'];
													} else {
														$stroptid .= "&optid".$ir.$opt['id']."=".$opt['quan'];
													}
												}
											}
										}
										$strroomid = "";
										foreach ($rooms as $num => $r) {
											$strroomid .= "&roomid[]=".$r['id'];
										}
										$straduchild = "";
										foreach ($arrpeople as $indroom => $aduch) {
											$straduchild .= "&adults[]=".$aduch['adults'];
											$straduchild .= "&children[]=".$aduch['children'];
										}
										$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=oconfirm".$strpriceid.$stroptid.$strroomid.$straduchild."&roomsnum=".$proomsnum."&days=".$pdays."&checkin=".$pcheckin."&checkout=".$pcheckout.(!empty($pitemid) ? "&Itemid=".$pitemid : ""), false));
										exit;
									}
									$realback = VikBooking::getHoursRoomAvail() * 3600;
									$realback += $pcheckout;
									if (!(count($mod_booking) > 0) && ((is_array($payment) && intval($payment['setconfirmed']) == 1) || !$must_payment)) {
										//we enter this statement to set the booking to Confirmed when: no booking modification and, payment selected sets status to confirmed or no payments enabled
										$arrbusy = array();
										foreach ($rooms as $num => $r) {
											$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES('" . $r['id'] . "', " . $dbo->quote($pcheckin) . ", " . $dbo->quote($pcheckout) . ",'" . $realback . "');";
											$dbo->setQuery($q);
											$dbo->execute();
											$lid = $dbo->insertid();
											$arrbusy[$num] = $lid;
										}
										$q = "INSERT INTO `#__vikbooking_orders` (`custdata`,`ts`,`status`,`days`,`checkin`,`checkout`,`custmail`,`sid`,`idpayment`,`ujid`,`coupon`,`roomsnum`,`total`,`channel`,`lang`,`country`,`tot_taxes`,`tot_city_taxes`,`tot_fees`,`phone`,`pkg`) VALUES(" . $dbo->quote($custdata) . ",'" . $nowts . "','confirmed'," . $dbo->quote($pdays) . "," . $dbo->quote($pcheckin) . "," . $dbo->quote($pcheckout) . "," . $dbo->quote($useremail) . ",'" . $sid . "'," . (is_array($payment) ? $dbo->quote($payment['id'].'='.$payment['name']) : "NULL") . ",'".$currentUser->id."',".($usedcoupon === true ? $dbo->quote($strcouponeff) : "NULL").",'".count($rooms)."','".$isdue."',".(is_array($vcmchanneldata) ? $dbo->quote($vcmchanneldata['name']) : 'NULL')."," . $dbo->quote($langtag) . ",".(!empty($usercountry) ? $dbo->quote($usercountry) : 'NULL').",'".$tot_taxes."','".$tot_city_taxes."','".$tot_fees."', ".$dbo->quote($phone_number).", ".($is_package === true ? (int)$pkg['id'] : "NULL").");";
										$dbo->setQuery($q);
										$dbo->execute();
										$neworderid = $dbo->insertid();
										//ConfirmationNumber
										$confirmnumber = VikBooking::generateConfirmNumber($neworderid, true);
										//end ConfirmationNumber
										//Assign room specific unit
										$set_room_indexes = VikBooking::autoRoomUnit();
										$room_indexes_usemap = array();
										//
										foreach ($rooms as $num => $r) {
											$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES('".$neworderid."', '".$arrbusy[$num]."');";
											$dbo->setQuery($q);
											$dbo->execute();
											$json_ch_age = '';
											if (array_key_exists($num, $children_age)) {
												$json_ch_age = json_encode($children_age[$num]);
											}
											//Assign room specific unit
											$room_indexes = $set_room_indexes === true ? VikBooking::getRoomUnitNumsAvailable(array('id' => $neworderid, 'checkin' => $pcheckin, 'checkout' => $pcheckout), $r['id']) : array();
											$use_ind_key = 0;
											if (count($room_indexes)) {
												if (!array_key_exists($r['id'], $room_indexes_usemap)) {
													$room_indexes_usemap[$r['id']] = $use_ind_key;
												} else {
													$use_ind_key = $room_indexes_usemap[$r['id']];
												}
												$rooms[$num]['roomindex'] = (int)$room_indexes[$use_ind_key];
											}
											//
											$pkg_cost = 0;
											if ($is_package === true) {
												$pkg_cost = $pkg['pernight_total'] == 1 ? ($pkg['cost'] * $daysdiff) : $pkg['cost'];
												$pkg_cost = $pkg['perperson'] == 1 ? ($pkg_cost * ($arrpeople[$num]['adults'] > 0 ? $arrpeople[$num]['adults'] : 1)) : $pkg_cost;
												$pkg_cost = VikBooking::sayPackagePlusIva($pkg_cost, $pkg['idiva']);
											}
											$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`,`idtar`,`optionals`,`childrenage`,`t_first_name`,`t_last_name`,`roomindex`,`pkg_id`,`pkg_name`,`cust_cost`,`cust_idiva`,`room_cost`) VALUES('".$neworderid."', '".$r['id']."', '".$arrpeople[$num]['adults']."', '".$arrpeople[$num]['children']."', '".$tars[$num][0]['id']."', '".$selopt['room'.$num]."', ".(!empty($json_ch_age) ? $dbo->quote($json_ch_age) : 'NULL').", ".$dbo->quote($t_first_name).", ".$dbo->quote($t_last_name).", ".(count($room_indexes) ? (int)$room_indexes[$use_ind_key] : "NULL").", ".($is_package === true ? (int)$pkg['id'].", ".$dbo->quote($pkg['name']).", ".$dbo->quote($pkg_cost).", ".intval($pkg['idiva']) : "NULL, NULL, NULL, NULL").", ".(array_key_exists($num, $rooms_costs_map) ? $dbo->quote($rooms_costs_map[$num]) : "NULL").");";
											$dbo->setQuery($q);
											$dbo->execute();
											if (count($room_indexes)) {
												$room_indexes_usemap[$r['id']]++;
											}
											//
										}
										//Customer Booking
										$cpin->saveCustomerBooking($neworderid);
										//end Customer Booking
										if ($usedcoupon === true && $coupon['type'] == 2) {
											$q = "DELETE FROM `#__vikbooking_coupons` WHERE `id`='".$coupon['id']."';";
											$dbo->setQuery($q);
											$dbo->execute();
										}
										VikBooking::sendAdminMail($admail.';_;'.$useremail, JText::sprintf('VBNEWORDER', $neworderid), $ftitle, $nowts, $custdata, $rooms, $pcheckin, $pcheckout, $pricestr, $optstr, $isdue, JText::_('VBCOMPLETED'), $payment['name'], $strcouponeff, $arrpeople, $confirmnumber);
										VikBooking::sendCustMail($useremail, strip_tags($ftitle) . " " . JText::_('VBORDNOL'), $ftitle, $nowts, $custdata, $rooms, $pcheckin, $pcheckout, $pricestr, $optstr, $isdue, $viklink, JText::_('VBCOMPLETED'), $neworderid, $strcouponeff, $arrpeople, $confirmnumber);
										//SMS
										VikBooking::sendBookingSMS($neworderid);
										//
										//Booking History
										VikBooking::getBookingHistoryInstance()->setBid($neworderid)->store('NC', 'IP: '.VikRequest::getVar('REMOTE_ADDR', '', 'server'));
										//
										//invoke VikChannelManager
										if (file_exists(VCM_SITE_PATH . DS . "helpers" . DS . "synch.vikbooking.php")) {
											require_once(VCM_SITE_PATH . DS . "helpers" . DS . "synch.vikbooking.php");
											$vcm = new synchVikBooking($neworderid);
											$vcm->setPushType('new')->sendRequest();
										}
										//end invoke VikChannelManager
										$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=vieworder&sid=" . $sid . "&ts=" . $nowts . (!empty($pnodep) ? "&nodep=".$pnodep : "") . (!empty($pitemid) ? "&Itemid=" . $pitemid : ""), false));
									} elseif (count($mod_booking) > 0) {
										//booking modification statement
										//get current orders-busy relations
										$old_busy_ids = array();
										$q = "SELECT `idbusy` FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$mod_booking['id'].";";
										$dbo->setQuery($q);
										$dbo->execute();
										if ($dbo->getNumRows() > 0) {
											$getbusy = $dbo->loadAssocList();
											foreach ($getbusy as $gbu) {
												array_push($old_busy_ids, $gbu['idbusy']);
											}
										}
										//remove current busy records
										if (count($old_busy_ids)) {
											$q = "DELETE FROM `#__vikbooking_busy` WHERE `id` IN (".implode(', ', $old_busy_ids).");";
											$dbo->setQuery($q);
											$dbo->execute();
										}
										$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$mod_booking['id'].";";
										$dbo->setQuery($q);
										$dbo->execute();
										//get current rooms (for VCM and for composing the log)
										$q = "SELECT `or`.*,`r`.`name`,`r`.`idopt`,`r`.`units`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=".(int)$mod_booking['id']." AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
										$dbo->setQuery($q);
										$dbo->execute();
										$old_ordersrooms = $dbo->loadAssocList();
										$mod_booking['rooms_info'] = $old_ordersrooms;
										//remove current rooms
										$q = "DELETE FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".(int)$mod_booking['id'].";";
										$dbo->setQuery($q);
										$dbo->execute();
										//update the booking by creating first the new busy records
										$arrbusy = array();
										foreach ($rooms as $num => $r) {
											$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES(".(int)$r['id'].", ".$dbo->quote($pcheckin).", ".$dbo->quote($pcheckout).", ".$dbo->quote($realback).");";
											$dbo->setQuery($q);
											$dbo->execute();
											$lid = $dbo->insertid();
											$arrbusy[$num] = $lid;
										}
										//Assign room specific unit
										$set_room_indexes = VikBooking::autoRoomUnit();
										$room_indexes_usemap = array();
										//create the new rooms and orders-busy relations
										foreach ($rooms as $num => $r) {
											$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES(".(int)$mod_booking['id'].", ".(int)$arrbusy[$num].");";
											$dbo->setQuery($q);
											$dbo->execute();
											$json_ch_age = '';
											if (array_key_exists($num, $children_age)) {
												$json_ch_age = json_encode($children_age[$num]);
											}
											//Assign room specific unit
											$room_indexes = $set_room_indexes === true ? VikBooking::getRoomUnitNumsAvailable(array('id' => $mod_booking['id'], 'checkin' => $pcheckin, 'checkout' => $pcheckout), $r['id']) : array();
											$use_ind_key = 0;
											if (count($room_indexes)) {
												if(!array_key_exists($r['id'], $room_indexes_usemap)) {
													$room_indexes_usemap[$r['id']] = $use_ind_key;
												} else {
													$use_ind_key = $room_indexes_usemap[$r['id']];
												}
												$rooms[$num]['roomindex'] = (int)$room_indexes[$use_ind_key];
											}
											//
											$pkg_cost = 0;
											if ($is_package === true) {
												$pkg_cost = $pkg['pernight_total'] == 1 ? ($pkg['cost'] * $daysdiff) : $pkg['cost'];
												$pkg_cost = $pkg['perperson'] == 1 ? ($pkg_cost * ($arrpeople[$num]['adults'] > 0 ? $arrpeople[$num]['adults'] : 1)) : $pkg_cost;
												$pkg_cost = VikBooking::sayPackagePlusIva($pkg_cost, $pkg['idiva']);
											}
											$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`,`idtar`,`optionals`,`childrenage`,`t_first_name`,`t_last_name`,`roomindex`,`pkg_id`,`pkg_name`,`cust_cost`,`cust_idiva`,`room_cost`) VALUES(".(int)$mod_booking['id'].", ".(int)$r['id'].", '".$arrpeople[$num]['adults']."', '".$arrpeople[$num]['children']."', '".$tars[$num][0]['id']."', '".$selopt['room'.$num]."', ".(!empty($json_ch_age) ? $dbo->quote($json_ch_age) : 'NULL').", ".$dbo->quote($t_first_name).", ".$dbo->quote($t_last_name).", ".(count($room_indexes) ? (int)$room_indexes[$use_ind_key] : "NULL").", ".($is_package === true ? (int)$pkg['id'].", ".$dbo->quote($pkg['name']).", ".$dbo->quote($pkg_cost).", ".intval($pkg['idiva']) : "NULL, NULL, NULL, NULL").", ".(array_key_exists($num, $rooms_costs_map) ? $dbo->quote($rooms_costs_map[$num]) : "NULL").");";
											$dbo->setQuery($q);
											$dbo->execute();
											if (count($room_indexes)) {
												$room_indexes_usemap[$r['id']]++;
											}
											//
										}
										//update the booking record (do not touch information like sid, confirmnumber, payment method etc..)
										$logmod = VikBooking::getLogBookingModification($mod_booking);
										$mod_notes = $logmod.(!empty($mod_booking['adminnotes']) ? "\n\n".$mod_booking['adminnotes'] : '');
										//if old total lower than new total, increment paymcount to allow a new payment (if configuration setting enabled)
										$mod_paymcount = (int)$mod_booking['paymcount'];
										if ($mod_booking['total'] < $isdue) {
											$mod_paymcount++;
										}
										//
										$q = "UPDATE `#__vikbooking_orders` SET `custdata`=".$dbo->quote($custdata).",`ts`='".$nowts."',`days`=".$dbo->quote($pdays).",`checkin`=".$dbo->quote($pcheckin).",`checkout`=".$dbo->quote($pcheckout).",`custmail`=".$dbo->quote($useremail).",`ujid`='".$currentUser->id."',`coupon`=".($usedcoupon === true ? $dbo->quote($strcouponeff) : "NULL").",`roomsnum`='".count($rooms)."',`total`='".$isdue."',`channel`=".(is_array($vcmchanneldata) ? $dbo->quote($vcmchanneldata['name']) : (!empty($mod_booking['channel']) ? $dbo->quote($mod_booking['channel']) : 'NULL')).",`paymcount`=".$mod_paymcount.",`adminnotes`=".$dbo->quote($mod_notes).",`lang`=".$dbo->quote($langtag).",`country`=".(!empty($usercountry) ? $dbo->quote($usercountry) : 'NULL').",`tot_taxes`='".$tot_taxes."',`tot_city_taxes`='".$tot_city_taxes."',`tot_fees`='".$tot_fees."',`phone`=".$dbo->quote($phone_number).",`pkg`=".($is_package === true ? (int)$pkg['id'] : "NULL")." WHERE `id`=".(int)$mod_booking['id'].";";
										$dbo->setQuery($q);
										$dbo->execute();
										//remove the coupon used (should never been allowed for modifications)
										if ($usedcoupon == true && $coupon['type'] == 2) {
											$q = "DELETE FROM `#__vikbooking_coupons` WHERE `id`=".(int)$coupon['id'].";";
											$dbo->setQuery($q);
											$dbo->execute();
										}
										//send email messages (admin and customer) and invoke SMS send
										VikBooking::sendAdminMail($admail.';_;'.$useremail, JText::sprintf('VBOMODDEDORDER', $mod_booking['id']), $ftitle, $nowts, $custdata, $rooms, $pcheckin, $pcheckout, $pricestr, $optstr, $isdue, JText::_('VBCOMPLETED'), "", $strcouponeff, $arrpeople, $mod_booking['confirmnumber']);
										VikBooking::sendCustMail($useremail, strip_tags($ftitle)." ".JText::_('VBOMODDEDORDERC'), $ftitle, $nowts, $custdata, $rooms, $pcheckin, $pcheckout, $pricestr, $optstr, $isdue, $viklink, JText::_('VBCOMPLETED'), $mod_booking['id'], $strcouponeff, $arrpeople, $mod_booking['confirmnumber']);
										//SMS
										VikBooking::sendBookingSMS($mod_booking['id']);
										//
										//Booking History
										VikBooking::getBookingHistoryInstance()->setBid($mod_booking['id'])->store('MW', $logmod);
										//
										//invoke VikChannelManager
										if (file_exists(VCM_SITE_PATH . DS . "helpers" . DS . "synch.vikbooking.php")) {
											$vcm_obj = VikBooking::getVcmInvoker();
											$vcm_obj->setOids(array($mod_booking['id']))->setSyncType('modify')->setOriginalBooking($mod_booking);
											$vcm_obj->doSync();
										}
										//end invoke VikChannelManager
										//unset the session value
										$session->set('vboModBooking', '');
										//
										$mainframe->enqueueMessage(JText::_('VBOBOOKINGMODOK'));
										$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=vieworder&sid=" . $sid . "&ts=" . $nowts . (!empty($pnodep) ? "&nodep=".$pnodep : "") . (!empty($pitemid) ? "&Itemid=" . $pitemid : ""), false));
									} else {
										//booking must have status stand-by and proceed to the payment
										$q = "INSERT INTO `#__vikbooking_orders` (`custdata`,`ts`,`status`,`days`,`checkin`,`checkout`,`custmail`,`sid`,`totpaid`,`idpayment`,`ujid`,`coupon`,`roomsnum`,`total`,`channel`,`lang`,`country`,`tot_taxes`,`tot_city_taxes`,`tot_fees`,`phone`,`pkg`) VALUES(" . $dbo->quote($custdata) . ",'" . $nowts . "','standby'," . $dbo->quote($pdays) . "," . $dbo->quote($pcheckin) . "," . $dbo->quote($pcheckout) . "," . $dbo->quote($useremail) . ",'" . $sid . "',''," . $dbo->quote($payment['id'] . '=' . $payment['name']) . ",'".$currentUser->id."',".($usedcoupon == true ? $dbo->quote($strcouponeff) : "NULL").",'".count($rooms)."','".$isdue."',".(is_array($vcmchanneldata) ? $dbo->quote($vcmchanneldata['name']) : 'NULL')."," . $dbo->quote($langtag) . ",".(!empty($usercountry) ? $dbo->quote($usercountry) : 'NULL').",'".$tot_taxes."','".$tot_city_taxes."','".$tot_fees."', ".$dbo->quote($phone_number).", ".($is_package === true ? (int)$pkg['id'] : "NULL").");";
										$dbo->setQuery($q);
										$dbo->execute();
										$neworderid = $dbo->insertid();
										foreach ($rooms as $num => $r) {
											$json_ch_age = '';
											if (array_key_exists($num, $children_age)) {
												$json_ch_age = json_encode($children_age[$num]);
											}
											$pkg_cost = 0;
											if ($is_package === true) {
												$pkg_cost = $pkg['pernight_total'] == 1 ? ($pkg['cost'] * $daysdiff) : $pkg['cost'];
												$pkg_cost = $pkg['perperson'] == 1 ? ($pkg_cost * ($arrpeople[$num]['adults'] > 0 ? $arrpeople[$num]['adults'] : 1)) : $pkg_cost;
												$pkg_cost = VikBooking::sayPackagePlusIva($pkg_cost, $pkg['idiva']);
											}
											$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`,`idtar`,`optionals`,`childrenage`,`t_first_name`,`t_last_name`,`pkg_id`,`pkg_name`,`cust_cost`,`cust_idiva`,`room_cost`) VALUES('".$neworderid."', '".$r['id']."', '".$arrpeople[$num]['adults']."', '".$arrpeople[$num]['children']."', '".$tars[$num][0]['id']."', '".$selopt['room'.$num]."', ".(!empty($json_ch_age) ? $dbo->quote($json_ch_age) : 'NULL').", ".$dbo->quote($t_first_name).", ".$dbo->quote($t_last_name).", ".($is_package === true ? (int)$pkg['id'].", ".$dbo->quote($pkg['name']).", ".$dbo->quote($pkg_cost).", ".intval($pkg['idiva']) : "NULL, NULL, NULL, NULL").", ".(array_key_exists($num, $rooms_costs_map) ? $dbo->quote($rooms_costs_map[$num]) : "NULL").");";
											$dbo->setQuery($q);
											$dbo->execute();
										}
										if ($usedcoupon === true && $coupon['type'] == 2) {
											$q = "DELETE FROM `#__vikbooking_coupons` WHERE `id`='".$coupon['id']."';";
											$dbo->setQuery($q);
											$dbo->execute();
										}
										foreach ($rooms as $num => $r) {
											$q = "INSERT INTO `#__vikbooking_tmplock` (`idroom`,`checkin`,`checkout`,`until`,`realback`,`idorder`) VALUES('" . $r['id'] . "'," . $dbo->quote($pcheckin) . "," . $dbo->quote($pcheckout) . ",'" . VikBooking::getMinutesLock(true) . "','" . $realback . "', ".(int)$neworderid.");";
											$dbo->setQuery($q);
											$dbo->execute();
										}
										//Customer Booking
										$cpin->saveCustomerBooking($neworderid);
										//end Customer Booking
										VikBooking::sendAdminMail($admail.';_;'.$useremail, JText::sprintf('VBNEWORDER', $neworderid), $ftitle, $nowts, $custdata, $rooms, $pcheckin, $pcheckout, $pricestr, $optstr, $isdue, JText::_('VBINATTESA'), $payment['name'], $strcouponeff, $arrpeople);
										VikBooking::sendCustMail($useremail, strip_tags($ftitle) . " " . JText::_('VBORDNOL'), $ftitle, $nowts, $custdata, $rooms, $pcheckin, $pcheckout, $pricestr, $optstr, $isdue, $viklink, JText::_('VBINATTESA'), $neworderid, $strcouponeff, $arrpeople);
										//SMS
										VikBooking::sendBookingSMS($neworderid);
										//
										//Booking History
										VikBooking::getBookingHistoryInstance()->setBid($neworderid)->store('NP', 'IP: '.VikRequest::getVar('REMOTE_ADDR', '', 'server'));
										//
										$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=vieworder&sid=" . $sid . "&ts=" . $nowts . (!empty($pnodep) ? "&nodep=".$pnodep : "") . (!empty($pitemid) ? "&Itemid=" . $pitemid : ""), false));
									}
								} else {
									showSelectVb(JText::_('VBROOMBOOKEDBYOTHER'));
								}
							} else {
								showSelectVb(JText::_('VBINVALIDDATES'));
							}
						} else {
							showSelectVb(JText::_('VBINCONGRTOT'));
						}
					} else {
						showSelectVb(JText::_('VBINCONGRDATAREC'));
					}
				} else {
					showSelectVb(JText::_('VBINCONGRDATA'));
				}
			} else {
				showSelectVb(JText::_('VBINSUFDATA'));
			}
		} else {
			showSelectVb(JText::_('VBINVALIDTOKEN'));
		}
	}

	function vieworder() {
		$sid = VikRequest::getString('sid', '', 'request');
		$ts = VikRequest::getString('ts', '', 'request');
		if (!empty($sid) && !empty($ts)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `o`.*,(SELECT SUM(`or`.`adults`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `tot_adults` FROM `#__vikbooking_orders` AS `o` WHERE `o`.`sid`=" . $dbo->quote($sid) . " AND `o`.`ts`=" . $dbo->quote($ts) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$order = $dbo->loadAssocList();
				if ($order[0]['status'] == "confirmed") {
					//prepare impression data for channels
					$impressiondata = $order[0];
					$q="SELECT `or`.`idtar`,`d`.`idprice`,`p`.`idiva`,`t`.`aliq` FROM `#__vikbooking_ordersrooms` AS `or` " .
						"LEFT JOIN `#__vikbooking_dispcost` `d` ON `d`.`id`=`or`.`idtar` " . 
						"LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`d`.`idprice` " . 
						"LEFT JOIN `#__vikbooking_iva` `t` ON `t`.`id`=`p`.`idiva` " . 
						"WHERE `or`.`idorder`='".$order[0]['id']."' ORDER BY `t`.`aliq` ASC LIMIT 1;";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() == 1) {
						$taxdata = $dbo->loadAssocList();
						$taxes = 0;
						if (!empty($taxdata[0]['aliq'])) {
							$realtotal = round(($order[0]['total'] * (100 - $taxdata[0]['aliq']) / 100), 2);
							$taxes = round(($order[0]['total'] - $realtotal), 2);
							$impressiondata['total'] = $realtotal;
						}
						$impressiondata['taxes'] = $taxes;
						$impressiondata['fees'] = 0;
					}
					VikBooking::invokeChannelManager(true, $impressiondata);
					//end prepare impression data for channels
					VikRequest::setVar('view', 'confirmedorder');
					parent::display();
				} else {
					$roomavail = true;
					$q="SELECT `or`.*,`r`.`units` FROM `#__vikbooking_ordersrooms` AS `or`, `#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$order[0]['id']."' AND `or`.`idroom`=`r`.`id`;";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$orderrooms = $dbo->loadAssocList();
						foreach ($orderrooms as $or) {
							$roomavail = VikBooking::roomBookable($or['idroom'], $or['units'], $order[0]['checkin'], $order[0]['checkout']);
							if (!$roomavail) {
								break;
							}
						}
					} else {
						$roomavail = false;
					}
					$today_midnight = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
					$autoremove = false;
					if ($today_midnight > $order[0]['checkin']) {
						$roomavail = false;
					} elseif ($order[0]['status'] == "standby") {
						$minautoremove = VikBooking::getMinutesAutoRemove();
						$mins_elapsed = floor((time() - $order[0]['ts']) / 60);
						if ($minautoremove > 0 && $mins_elapsed > $minautoremove) {
							$roomavail = false;
							$autoremove = true;
						}
					}
					if ($roomavail == true || $order[0]['status'] == "cancelled") {
						//SHOW PAYMENT FORM
						if ($order[0]['status'] != "cancelled") {
							VikBooking::invokeChannelManager(false);
						}
						VikRequest::setVar('view', 'standbyorder');
						parent::display();
					} else {
						$q="UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`=".(int)$order[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . (int)$order[0]['id'] . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q="SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$order[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$ordbusy = $dbo->loadAssocList();
							foreach($ordbusy as $ob) {
								$q="DELETE FROM `#__vikbooking_busy` WHERE `id`=".(int)$ob['idbusy'].";";
								$dbo->setQuery($q);
								$dbo->execute();
							}
						}
						$q="DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$order[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($today_midnight > $order[0]['checkin']) {
							VikError::raiseWarning('', JText::_('VBOBOOKNOLONGERPAYABLE'));
						} elseif ($autoremove === true) {
							VikError::raiseWarning('', JText::_('VBOERRAUTOREMOVED'));
						} else {
							VikError::raiseWarning('', JText::_('VBERRREPSEARCH'));
						}
						//Booking History
						VikBooking::getBookingHistoryInstance()->setBid($order[0]['id'])->store('CA');
						//
						//Show the cancelled booking details page in any case
						VikRequest::setVar('view', 'standbyorder');
						parent::display();
					}
				}
			} else {
				showSelectVb(JText::_('VBORDERNOTFOUND'));
			}
		} else {
			showSelectVb(JText::_('VBINSUFDATA'));
		}
	}
	
	function cancelrequest() {
		$psid = VikRequest::getString('sid', '', 'request');
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($psid) && !empty($pidorder)) {
			$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".intval($pidorder)." AND `sid`=".$dbo->quote($psid).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$order = $dbo->loadAssocList();
				$pemail = VikRequest::getString('email', '', 'request');
				$preason = VikRequest::getString('reason', '', 'request');
				if (!empty($pemail) && !empty($preason)) {
					$to = VikBooking::getAdminMail();
					if(strpos($to, ',') !== false) {
						$all_recipients = explode(',', $to);
						foreach ($all_recipients as $k => $v) {
							if(empty($v)) {
								unset($all_recipients[$k]);
							}
						}
						if(count($all_recipients) > 0) {
							$to = $all_recipients;
						}
					}
					//Booking History
					VikBooking::getBookingHistoryInstance()->setBid($order[0]['id'])->store('CR', $pemail."\n".$preason);
					//
					$subject = JText::_('VBCANCREQUESTEMAILSUBJ');
					//$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
					$msg = JText::sprintf('VBCANCREQUESTEMAILHEAD', $order[0]['id'], JURI::root().'index.php?option=com_vikbooking&task=vieworder&sid='.$order[0]['sid'].'&ts='.$order[0]['ts'])."\n\n".$preason;
					$vbo_app = VikBooking::getVboApplication();
					$vbo_app->sendMail($adsendermail, $adsendermail, $to, $pemail, $subject, $msg, false);
					$mainframe->enqueueMessage(JText::_('VBCANCREQUESTMAILSENT'));
					$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts']."&Itemid=".VikRequest::getString('Itemid', '', 'request'), false));
				} else {
					$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'], false));
				}
			} else {
				$mainframe->redirect("index.php");
			}
		} else {
			$mainframe->redirect("index.php");
		}
	}

	function reqinfo() {
		$proomid = VikRequest::getInt('roomid', '', 'request');
		$preqinfotoken = VikRequest::getInt('reqinfotoken', '', 'request');
		$pitemid = VikRequest::getInt('Itemid', '', 'request');
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		$vbo_app = VikBooking::getVboApplication();
		if (!empty($proomid)) {
			$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$proomid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$room = $dbo->loadAssocList();
				$goto = JRoute::_('index.php?option=com_vikbooking&view=roomdetails&roomid='.$room[0]['id'].'&Itemid='.$pitemid, false);
				$preqname = VikRequest::getString('reqname', '', 'request');
				$preqemail = VikRequest::getString('reqemail', '', 'request');
				$preqmess = VikRequest::getString('reqmess', '', 'request');
				if (!empty($preqemail) && !empty($preqmess)) {
					/**
					 * captcha verification
					 * 
					 * @joomlaonly  reCaptcha is only for Joomla
					 */
					if ($vbo_app->isCaptcha() && !$vbo_app->reCaptcha('check')) {
						VikError::raiseWarning('', 'Invalid Captcha');
						$mainframe->redirect($goto);
						exit;
					}
					//
					$sesstoken = $session->get('vboreqinfo'.$room[0]['id'], '');
					if((int)$sesstoken == (int)$preqinfotoken) {
						$session->set('vboreqinfo'.$room[0]['id'], '');
						$to = VikBooking::getAdminMail();
						if(strpos($to, ',') !== false) {
							$all_recipients = explode(',', $to);
							foreach ($all_recipients as $k => $v) {
								if(empty($v)) {
									unset($all_recipients[$k]);
								}
							}
							if(count($all_recipients) > 0) {
								$to = $all_recipients;
							}
						}
						$subject = JText::sprintf('VBOROOMREQINFOSUBJ', $room[0]['name']);
						$msg = JText::_('VBOROOMREQINFONAME').": ".$preqname."\n\n".JText::_('VBOROOMREQINFOEMAIL').": ".$preqemail."\n\n".JText::_('VBOROOMREQINFOMESS').":\n\n".$preqmess;
						$vbo_app->sendMail($adsendermail, $adsendermail, $to, $preqemail, $subject, $msg, false);
						$mainframe->enqueueMessage(JText::_('VBOROOMREQINFOSENTOK'));
					} else {
						VikError::raiseWarning('', JText::_('VBOROOMREQINFOTKNERR'));
					}
					$mainframe->redirect($goto);
				} else {
					VikError::raiseWarning('', JText::_('VBOROOMREQINFOMISSFIELD'));
					$mainframe->redirect($goto);
				}
			} else {
				$mainframe->redirect("index.php");
			}
		} else {
			$mainframe->redirect("index.php");
		}
	}

	function cron_exec() {
		$dbo = JFactory::getDBO();
		$pcron_id = VikRequest::getInt('cron_id', '', 'request');
		$pcronkey = VikRequest::getString('cronkey', '', 'request');
		if(empty($pcron_id) || empty($pcronkey)) {
			die('Error[1]');
		}
		if($pcronkey != md5(VikBooking::getCronKey())) {
			die('Error[2]');
		}
		$q = "SELECT * FROM `#__vikbooking_cronjobs` WHERE `id`=".$dbo->quote($pcron_id)." AND `published`=1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() != 1) {
			die('Error[3]');
		}
		$cron_data = $dbo->loadAssoc();
		if(!file_exists(VBO_ADMIN_PATH.DS.'cronjobs'.DS.$cron_data['class_file'])) {
			die('Error[4]');
		}
		require_once(VBO_ADMIN_PATH.DS.'cronjobs'.DS.$cron_data['class_file']);
		$cron_obj = new VikCronJob($cron_data['id'], json_decode($cron_data['params'], true));
		$run_res = $cron_obj->run();
		$cron_obj->afterRun();
		echo intval($run_res);
		die;
	}
	
	function notifypayment() {
		$psid = VikRequest::getString('sid', '', 'request');
		$pts = VikRequest::getString('ts', '', 'request');
		$dbo = JFactory::getDBO();
		$nowdf = VikBooking::getDateFormat();
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		if (strlen($psid) && strlen($pts)) {
			$admail = VikBooking::getAdminMail();
			$recipient_mail = $admail;
			if(!is_array($recipient_mail) && strpos($recipient_mail, ',') !== false) {
				$all_recipients = explode(',', $recipient_mail);
				foreach ($all_recipients as $k => $v) {
					if(empty($v)) {
						unset($all_recipients[$k]);
					}
				}
				if(count($all_recipients) > 0) {
					$recipient_mail = $all_recipients;
				}
			}
			$q = "SELECT * FROM `#__vikbooking_orders` WHERE `ts`=" . $dbo->quote($pts) . " AND `sid`=" . $dbo->quote($psid) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rows = $dbo->loadAssocList();
				//check if the language in use is the same as the one used during the checkout
				if (!empty($rows[0]['lang'])) {
					$lang = JFactory::getLanguage();
					if($lang->getTag() != $rows[0]['lang']) {
						$lang->load('com_vikbooking', JPATH_SITE, $rows[0]['lang'], true);
					}
				}
				//
				$vbo_tn = VikBooking::getTranslator();
				if($rows[0]['status']!='confirmed' || (VikBooking::multiplePayments() && $rows[0]['paymcount'] > 0)) {
					$rows[0]['admin_email'] = $admail;
					$realback = VikBooking::getHoursRoomAvail() * 3600;
					$realback += $rows[0]['checkout'];
					$currencyname = VikBooking::getCurrencyName();
					$ftitle = VikBooking::getFrontTitle();
					$nowts = time();
					$viklink = JURI::root() . "index.php?option=com_vikbooking&task=vieworder&sid=" . $psid . "&ts=" . $pts;
					$rooms = array();
					$tars = array();
					$arrpeople = array();
					$is_package = !empty($rows[0]['pkg']) ? true : false;
					$q="SELECT `or`.`id` AS `or_id`,`or`.`idroom`,`or`.`adults`,`or`.`children`,`or`.`idtar`,`or`.`optionals`,`or`.`roomindex`,`or`.`pkg_id`,`or`.`pkg_name`,`or`.`cust_cost`,`or`.`cust_idiva`,`or`.`extracosts`,`or`.`otarplan`,`r`.`id` AS `r_reference_id`,`r`.`name`,`r`.`img`,`r`.`idcarat`,`r`.`fromadult`,`r`.`toadult`,`r`.`params` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$rows[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$orderrooms = $dbo->loadAssocList();
						$vbo_tn->translateContents($orderrooms, '#__vikbooking_rooms', array('id' => 'r_reference_id'));
						foreach($orderrooms as $kor => $or) {
							$num = $kor + 1;
							$rooms[$num] = $or;
							$arrpeople[$num]['adults'] = $or['adults'];
							$arrpeople[$num]['children'] = $or['children'];
							if($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
								//package or custom cost set from the back-end
								continue;
							}
							$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `id`='" . $or['idtar'] . "';";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$tar = $dbo->loadAssocList();
								$tar = VikBooking::applySeasonsRoom($tar, $rows[0]['checkin'], $rows[0]['checkout']);
								//different usage
								if ($or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
									$diffusageprice = VikBooking::loadAdultsDiff($or['idroom'], $or['adults']);
									//Occupancy Override
									$occ_ovr = VikBooking::occupancyOverrideExists($tar, $or['adults']);
									$diffusageprice = $occ_ovr !== false ? $occ_ovr : $diffusageprice;
									//
									if (is_array($diffusageprice)) {
										//set a charge or discount to the price(s) for the different usage of the room
										foreach($tar as $kpr => $vpr) {
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
								$tars[$num] = $tar[0];
							}
						}
					}
					$rows[0]['order_rooms'] = $orderrooms;
					$rows[0]['fares'] = $tars;
					$isdue = 0;
					$tot_taxes = 0;
					$tot_city_taxes = 0;
					$tot_fees = 0;
					$pricestr = array();
					$optstr = array();
					foreach ($orderrooms as $kor => $or) {
						$num = $kor + 1;
						if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
							//package cost or cust_cost should always be inclusive of taxes
							$calctar = $or['cust_cost'];
							$isdue += $calctar;
							if ($calctar == $or['cust_cost']) {
								$cost_minus_tax = VikBooking::sayPackageMinusIva($or['cust_cost'], $or['cust_idiva']);
								$tot_taxes += ($or['cust_cost'] - $cost_minus_tax);
							} else {
								$tot_taxes += ($calctar - $or['cust_cost']);
							}
							$pricestr[$num] = (!empty($or['pkg_name']) ? $or['pkg_name'] : (!empty($or['otarplan']) ? ucwords($or['otarplan']) : JText::_('VBOROOMCUSTRATEPLAN'))).": ".$calctar." ".$currencyname;
						} elseif (array_key_exists($num, $tars) && is_array($tars[$num])) {
							$calctar = VikBooking::sayCostPlusIva($tars[$num]['cost'], $tars[$num]['idprice']);
							$tars[$num]['calctar'] = $calctar;
							$isdue += $calctar;
							if($calctar == $tars[$num]['cost']) {
								$cost_minus_tax = VikBooking::sayCostMinusIva($tars[$num]['cost'], $tars[$num]['idprice']);
								$tot_taxes += ($tars[$num]['cost'] - $cost_minus_tax);
							} else {
								$tot_taxes += ($calctar - $tars[$num]['cost']);
							}
							$pricestr[$num] = VikBooking::getPriceName($tars[$num]['idprice'], $vbo_tn) . ": " . $calctar . " " . $currencyname . (!empty($tars[$num]['attrdata']) ? "\n" . VikBooking::getPriceAttr($tars[$num]['idprice'], $vbo_tn) . ": " . $tars[$num]['attrdata'] : "");
						}
						if (!empty($or['optionals'])) {
							$stepo = explode(";", $or['optionals']);
							foreach ($stepo as $oo) {
								if (!empty($oo)) {
									$stept = explode(":", $oo);
									$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
									$dbo->setQuery($q);
									$dbo->execute();
									if ($dbo->getNumRows() == 1) {
										$actopt = $dbo->loadAssocList();
										$vbo_tn->translateContents($actopt, '#__vikbooking_optionals');
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
													$optagecosts[($chvar - 1)] = $tars[$num]['cost'] * $optagecosts[($chvar - 1)] / 100;
												}
											} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
												//VBO 1.10 - percentage value of room base cost
												if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
													$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
												} else {
													$display_rate = isset($tars[$num]['room_base_cost']) ? $tars[$num]['room_base_cost'] : $tars[$num]['cost'];
													$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
												}
											}
											$actopt[0]['chageintv'] = $chvar;
											$actopt[0]['name'] .= ' ('.$optagenames[($chvar - 1)].')';
											$actopt[0]['quan'] = $stept[1];
											$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $rows[0]['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
										} else {
											$actopt[0]['quan'] = $stept[1];
											$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $rows[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
										}
										if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
											$realcost = $actopt[0]['maxprice'];
											if(intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
												$realcost = $actopt[0]['maxprice'] * $stept[1];
											}
										}
										if ($actopt[0]['perperson'] == 1) {
											$realcost = $realcost * $or['adults'];
										}
										$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
										if ($actopt[0]['is_citytax'] == 1) {
											$tot_city_taxes += $tmpopr;
										} elseif ($actopt[0]['is_fee'] == 1) {
											$tot_fees += $tmpopr;
										} else {
											if($tmpopr == $realcost) {
												$opt_minus_tax = VikBooking::sayOptionalsMinusIva($realcost, $actopt[0]['idiva']);
												$tot_taxes += ($realcost - $opt_minus_tax);
											} else {
												$tot_taxes += ($tmpopr - $realcost);
											}
										}
										$isdue += $tmpopr;
										$optstr[$num][] = ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": " . $tmpopr . " " . $currencyname . "\n";
									}
								}
							}
						}
						//custom extra costs
						if(!empty($or['extracosts'])) {
							$cur_extra_costs = json_decode($or['extracosts'], true);
							foreach ($cur_extra_costs as $eck => $ecv) {
								$ecplustax = !empty($ecv['idtax']) ? VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
								$isdue += $ecplustax;
								$optstr[$num][] = $ecv['name'] . ": " . $ecplustax . " " . $currencyname."\n";
							}
						}
						//
					}
					//vikbooking 1.1 coupon
					$usedcoupon = false;
					$origisdue = $isdue;
					if(strlen($rows[0]['coupon']) > 0) {
						$usedcoupon = true;
						$expcoupon = explode(";", $rows[0]['coupon']);
						$isdue = $isdue - $expcoupon[1];
					}
					//
					//invoke the payment method class
					$exppay = explode('=', $rows[0]['idpayment']);
					$payment = VikBooking::getPayment($exppay[0], $vbo_tn);
					require_once(VBO_ADMIN_PATH . DS . "payments" . DS . $payment['file']);
					$obj = new vikBookingPayment($rows[0], json_decode($payment['params'], true));
					$array_result = $obj->validatePayment();
					$newpaymentlog = date('c')."\n".$array_result['log']."\n----------\n".$rows[0]['paymentlog'];
					if ($array_result['verified'] == 1) {
						//valid payment
						$shouldpay = $isdue;
						if ($payment['charge'] > 0.00) {
							if($payment['ch_disc'] == 1) {
								//charge
								if($payment['val_pcent'] == 1) {
									//fixed value
									$shouldpay += $payment['charge'];
								} else {
									//percent value
									$percent_to_pay = $shouldpay * $payment['charge'] / 100;
									$shouldpay += $percent_to_pay;
								}
							} else {
								//discount
								if($payment['val_pcent'] == 1) {
									//fixed value
									$shouldpay -= $payment['charge'];
								} else {
									//percent value
									$percent_to_pay = $shouldpay * $payment['charge'] / 100;
									$shouldpay -= $percent_to_pay;
								}
							}
						}
						//deposit may be skipped by customer choice
						$shouldpay_befdep = $shouldpay;
						//
						if (!VikBooking::payTotal()) {
							$percentdeposit = VikBooking::getAccPerCent();
							if ($percentdeposit > 0) {
								if (VikBooking::getTypeDeposit() == "fixed") {
									$shouldpay = $percentdeposit;
								} else {
									$shouldpay = $shouldpay * $percentdeposit / 100;
								}
							}
						}
						//check if the total amount paid is the same as the order total
						if (array_key_exists('tot_paid', $array_result)) {
							$shouldpay = round($shouldpay, 2);
							$shouldpay_befdep = round($shouldpay_befdep, 2);
							$totreceived = round($array_result['tot_paid'], 2);
							if ($shouldpay != $totreceived && $shouldpay_befdep != $totreceived && $rows[0]['paymcount'] == 0) {
								//the amount paid is different than the order total
								//fares might have changed or the deposit might be different
								//Sending just an email to the admin that will check
								$vbo_app = VikBooking::getVboApplication();
								$vbo_app->sendMail($adsendermail, $adsendermail, $recipient_mail, $adsendermail, JText::_('VBTOTPAYMENTINVALID'), JText::sprintf('VBTOTPAYMENTINVALIDTXT', $rows[0]['id'], $totreceived." (".$array_result['tot_paid'].")", $shouldpay), false);
							}
						}
						//
						if ($rows[0]['paymcount'] == 0) {
							foreach ($orderrooms as $indnum => $r) {
								$num = $indnum + 1;
								$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES('" . $r['idroom'] . "', '" . $rows[0]['checkin'] . "', '" . $rows[0]['checkout'] . "','" . $realback . "');";
								$dbo->setQuery($q);
								$dbo->execute();
								$lid = $dbo->insertid();
								$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES('".$rows[0]['id']."', '".$lid."');";
								$dbo->setQuery($q);
								$dbo->execute();
							}
						}
						//ConfirmationNumber
						if ($rows[0]['paymcount'] == 0) {
							$confirmnumber = VikBooking::generateConfirmNumber($rows[0]['id'], true);
						}
						//end ConfirmationNumber
						$q = "UPDATE `#__vikbooking_orders` SET `status`='confirmed'" . (isset($array_result['tot_paid']) && $array_result['tot_paid'] ? ", `totpaid`='" . ($array_result['tot_paid'] + $rows[0]['totpaid']) . "', `paymcount`=".($rows[0]['paymcount'] + 1) : "") . (!empty($array_result['log']) ? ", `paymentlog`=".$dbo->quote($newpaymentlog) : "") . " WHERE `id`='" . $rows[0]['id'] . "';";
						$dbo->setQuery($q);
						$dbo->execute();
						//Assign room specific unit
						$set_room_indexes = VikBooking::autoRoomUnit();
						$room_indexes_usemap = array();
						if ($set_room_indexes === true) {
							$q = "SELECT `id`,`idroom` FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".(int)$rows[0]['id'].";";
							$dbo->setQuery($q);
							$dbo->execute();
							$orooms = $dbo->loadAssocList();
							foreach ($orooms as $oroom) {
								//Assign room specific unit
								$room_indexes = VikBooking::getRoomUnitNumsAvailable($rows[0], $oroom['idroom']);
								$use_ind_key = 0;
								if (count($room_indexes)) {
									if (!array_key_exists($oroom['idroom'], $room_indexes_usemap)) {
										$room_indexes_usemap[$oroom['idroom']] = $use_ind_key;
									} else {
										$use_ind_key = $room_indexes_usemap[$oroom['idroom']];
									}
									$q = "UPDATE `#__vikbooking_ordersrooms` SET `roomindex`=".(int)$room_indexes[$use_ind_key]." WHERE `id`=".(int)$oroom['id'].";";
									$dbo->setQuery($q);
									$dbo->execute();
									//update rooms references for the customer email sending function
									foreach ($rooms as $rnum => $rr) {
										if ($rr['or_id'] == $oroom['id']) {
											$rooms[$rnum]['roomindex'] = (int)$room_indexes[$use_ind_key];
											break;
										}
									}
									//
									$room_indexes_usemap[$oroom['idroom']]++;
								}
								//
							}
						}
						//
						//VikBooking 1.6 : unlock room(s) for other imminent bookings
						$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						//
						//Customer Booking
						$q = "SELECT `idcustomer` FROM `#__vikbooking_customers_orders` WHERE `idorder`=".(int)$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						if($dbo->getNumRows() > 0) {
							$customer_id = $dbo->loadResult();
							$cpin = VikBooking::getCPinIstance();
							$cpin->updateBookingCommissions($rows[0]['id'], $customer_id);
						}
						//end Customer Booking
						//send mails
						VikBooking::sendAdminMail($admail.';_;'.$rows[0]['custmail'], JText::sprintf('VBORDERPAYMENT', $rows[0]['id']), $ftitle, $nowts, $rows[0]['custdata'], $rooms, $rows[0]['checkin'], $rows[0]['checkout'], $pricestr, $optstr, $isdue, JText::_('VBCOMPLETED'), $payment['name'], $rows[0]['coupon'], $arrpeople, $confirmnumber);
						VikBooking::sendCustMail($rows[0]['custmail'], strip_tags($ftitle) . " " . JText::_('VBRENTALORD'), $ftitle, $nowts, $rows[0]['custdata'], $rooms, $rows[0]['checkin'], $rows[0]['checkout'], $pricestr, $optstr, $isdue, $viklink, JText::_('VBCOMPLETED'), $rows[0]['id'], $rows[0]['coupon'], $arrpeople, $confirmnumber);
						//SMS
						VikBooking::sendBookingSMS($rows[0]['id']);
						//
						//Booking History
						VikBooking::getBookingHistoryInstance()->setBid($rows[0]['id'])->store('P'.($rows[0]['paymcount'] > 0 ? 'N' : '0'), $payment['name']);
						//
						//invoke VikChannelManager
						if (file_exists(VCM_SITE_PATH . DS . "helpers" . DS . "synch.vikbooking.php")) {
							require_once(VCM_SITE_PATH . DS . "helpers" . DS . "synch.vikbooking.php");
							$vcm = new synchVikBooking($rows[0]['id']);
							$vcm->setPushType('new')->sendRequest();
						}
						$session = JFactory::getSession();
						$vcmchanneldata = $session->get('vcmChannelData', '');
						if (!empty($vcmchanneldata)) {
							$session->set('vcmChannelData', '');
						}
						//end invoke VikChannelManager
						if(method_exists($obj, 'afterValidation')) {
							$obj->afterValidation(1);
						}
					} else {
						if(!array_key_exists('skip_email', $array_result) || $array_result['skip_email'] != 1) {
							$vbo_app = VikBooking::getVboApplication();
							$vbo_app->sendMail($adsendermail, $adsendermail, $recipient_mail, $adsendermail, JText::_('VBPAYMENTNOTVER'), JText::_('VBSERVRESP') . ":\n\n" . $array_result['log'], false);
						}
						if (!empty($array_result['log'])) {
							$q = "UPDATE `#__vikbooking_orders` SET `paymentlog`=".$dbo->quote($newpaymentlog)." WHERE `id`='" . $rows[0]['id'] . "';";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						if(method_exists($obj, 'afterValidation')) {
							$obj->afterValidation(0);
						}
					}
				}
			}
		}
		return true;
	}
	
	function currencyconverter() {
		$session = JFactory::getSession();
		$pprices = VikRequest::getVar('prices', array(0));
		$pfromsymbol = VikRequest::getString('fromsymbol', '', 'request');
		$ptocurrency = VikRequest::getString('tocurrency', '', 'request');
		$pfromcurrency = VikRequest::getString('fromcurrency', '', 'request');
		$default_cur = !empty($pfromcurrency) ? $pfromcurrency : VikBooking::getCurrencyName();
		$response = array();
		if (!empty($default_cur) && !empty($pprices) && count($pprices) > 0 && !empty($ptocurrency)) {
			require_once(VBO_SITE_PATH . DS . "helpers" . DS ."currencyconverter.php");
			if ($default_cur != $ptocurrency) {
				$format = VikBooking::getNumberFormatData();
				$converter = new vboCurrencyConverter($default_cur, $ptocurrency, $pprices, explode(':', $format));
				$exchanged = $converter->convert();
				if (count($exchanged) > 0) {
					$response = $exchanged;
					$session->set('vboLastCurrency', $ptocurrency);
				} else {
					$response['error'] = JText::_('VBERRCURCONVINVALIDDATA');
				}
			} else {
				$session->set('vboLastCurrency', $ptocurrency);
				foreach ($pprices as $i => $price) {
					$response[$i]['symbol'] = $pfromsymbol;
					$response[$i]['price'] = $price;
				}
			}
		} else {
			$response['error'] = JText::_('VBERRCURCONVNODATA');
		}
		if(array_key_exists('error', $response)) {
			$session->set('vboLastCurrency', $ptocurrency);
		}
		echo json_encode($response);
		exit;
	}

	function signature() {
		VikRequest::setVar('view', 'signature');
		parent::display();
	}

	function storesignature() {
		$sid = VikRequest::getString('sid', '', 'request');
		$ts = VikRequest::getString('ts', '', 'request');
		$psignature = VikRequest::getString('signature', '', 'request', VIKREQUEST_ALLOWRAW);
		$ppad_width = VikRequest::getInt('pad_width', '', 'request');
		$ppad_ratio = VikRequest::getInt('pad_ratio', '', 'request');
		$pitemid = VikRequest::getInt('Itemid', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `ts`=" . $dbo->quote($ts) . " AND `sid`=" . $dbo->quote($sid) . " AND `status`='confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			VikError::raiseWarning('', 'Booking not found');
			$mainframe->redirect('index.php');
			exit;
		}
		$row = $dbo->loadAssoc();
		$tonight = mktime(23, 59, 59, date('n'), date('j'), date('Y'));
		if ($tonight > $row['checkout']) {
			VikError::raiseWarning('', 'Check-out date is in the past');
			$mainframe->redirect('index.php');
			exit;
		}
		$customer = array();
		$q = "SELECT `c`.*,`co`.`idorder`,`co`.`signature`,`co`.`pax_data`,`co`.`comments` FROM `#__vikbooking_customers` AS `c` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=".(int)$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$customer = $dbo->loadAssoc();
		}
		if (!(count($customer) > 0)) {
			VikError::raiseWarning('', 'Customer not found');
			$mainframe->redirect('index.php');
			exit;
		}
		//check if the signature has been submitted
		$signature_data = '';
		$cont_type = '';
		if (!empty($psignature)) {
			//check whether the format is accepted
			if (strpos($psignature, 'image/png') !== false || strpos($psignature, 'image/jpeg') !== false || strpos($psignature, 'image/svg') !== false) {
				$parts = explode(';base64,', $psignature);
				$cont_type_parts = explode('image/', $parts[0]);
				$cont_type = $cont_type_parts[1];
				if (!empty($parts[1])) {
					$signature_data = base64_decode($parts[1]);
				}
			}
		}
		$ret_link = JRoute::_('index.php?option=com_vikbooking&task=signature&sid='.$row['sid'].'&ts='.$row['ts'].(!empty($pitemid) ? '&Itemid='.$pitemid : '').($ptmpl == 'component' ? '&tmpl=component' : ''), false);
		if (empty($signature_data)) {
			VikError::raiseWarning('', JText::_('VBOSIGNATUREISEMPTY'));
			$mainframe->redirect($ret_link);
			exit;
		}
		//write file
		$sign_fname = $row['id'].'_'.$row['sid'].'_'.$customer['id'].'.'.$cont_type;
		$filepath = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'idscans' . DIRECTORY_SEPARATOR . $sign_fname;
		$fp = fopen($filepath, 'w+');
		$bytes = fwrite($fp, $signature_data);
		fclose($fp);
		if ($bytes !== false && $bytes > 0) {
			//update the signature in the DB
			$q = "UPDATE `#__vikbooking_customers_orders` SET `signature`=".$dbo->quote($sign_fname)." WHERE `idorder`=".(int)$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VBOSIGNATURETHANKS'));
			//resize image for screens with high resolution
			if ($ppad_ratio > 1) {
				$new_width = floor(($ppad_width / 2));
				$creativik = new vikResizer();
				$creativik->proportionalImage($filepath, $filepath, $new_width, $new_width);
			}
			//
		} else {
			VikError::raiseWarning('', JText::_('VBOERRSTORESIGNFILE'));
		}
		$mainframe->redirect($ret_link);
		exit;
	}

	function validatepin() {
		$ppin = VikRequest::getString('pin', '', 'request');
		$cpin = VikBooking::getCPinIstance();
		$response = array();
		$customer = $cpin->getCustomerByPin($ppin);
		if(count($customer) > 0) {
			$response = $customer;
			$response['success'] = 1;
		}
		echo json_encode($response);
		exit;
	}

	function docancelbooking() {
		$psid = VikRequest::getString('sid', '', 'request');
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($psid) && !empty($pidorder)) {
			$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".intval($pidorder)." AND `sid`=".$dbo->quote($psid)." AND `status`='confirmed';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$order = $dbo->loadAssocList();
				$pemail = VikRequest::getString('email', '', 'request');
				$preason = VikRequest::getString('reason', '', 'request');
				if (!empty($pemail) && !empty($preason)) {
					$to = VikBooking::getAdminMail();
					if (strpos($to, ',') !== false) {
						$all_recipients = explode(',', $to);
						foreach ($all_recipients as $k => $v) {
							if (empty($v)) {
								unset($all_recipients[$k]);
							}
						}
						if (count($all_recipients) > 0) {
							$to = $all_recipients;
						}
					}
					//check if the booking can be cancelled
					$days_to_arrival = 0;
					$is_refundable = 0;
					$daysadv_refund_arr = array();
					$daysadv_refund = 0;
					$now_info = getdate();
					$checkin_info = getdate($order[0]['checkin']);
					if ($now_info[0] < $checkin_info[0]) {
						while ($now_info[0] < $checkin_info[0]) {
							if (!($now_info['mday'] != $checkin_info['mday'] || $now_info['mon'] != $checkin_info['mon'] || $now_info['year'] != $checkin_info['year'])) {
								break;
							}
							$days_to_arrival++;
							$now_info = getdate(mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] + 1), $now_info['year']));
						}
					}
					$tars = array();
					$is_package = !empty($order[0]['pkg']) ? true : false;
					$orderrooms = array();
					$q = "SELECT `or`.`idroom`,`or`.`adults`,`or`.`children`,`or`.`idtar`,`or`.`optionals`,`or`.`roomindex`,`or`.`pkg_id`,`or`.`pkg_name`,`or`.`cust_cost`,`or`.`cust_idiva`,`or`.`extracosts`,`or`.`room_cost`,`or`.`otarplan`,`r`.`id` AS `r_reference_id`,`r`.`name`,`r`.`img`,`r`.`idcarat`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$order[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$orderrooms = $dbo->loadAssocList();
						foreach($orderrooms as $kor => $or) {
							$num = $kor + 1;
							if($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
								//package or custom cost set from the back-end
								continue;
							}
							$q = "SELECT `t`.*,`p`.`name`,`p`.`free_cancellation`,`p`.`canc_deadline`,`p`.`canc_policy` FROM `#__vikbooking_dispcost` AS `t` LEFT JOIN `#__vikbooking_prices` AS `p` ON `t`.`idprice`=`p`.`id` WHERE `t`.`id`='" . $or['idtar'] . "';";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$tar = $dbo->loadAssocList();
								$tars[$num] = $tar[0];
							}
						}
					}
					foreach ($tars as $num => $tar) {
						if ($tar['free_cancellation'] < 1) {
							//if at least one rate plan is non-refundable, the whole reservation cannot be cancelled
							$is_refundable = 0;
							$daysadv_refund_arr = array();
							break;
						}
						$is_refundable = 1;
						$daysadv_refund_arr[] = $tar['canc_deadline'];
					}
					//get the rate plan with the lowest cancellation deadline
					$daysadv_refund = count($daysadv_refund_arr) > 0 ? min($daysadv_refund_arr) : $daysadv_refund;
					$resmodcanc = VikBooking::getReservationModCanc();
					$resmodcanc = $days_to_arrival < 1 ? 0 : $resmodcanc;
					$resmodcancmin = VikBooking::getReservationModCancMin();
					$canc_allowed = ($resmodcanc > 1 && $resmodcanc != 2 && $is_refundable > 0 && $daysadv_refund <= $days_to_arrival && $days_to_arrival >= $resmodcancmin);
					if (!$canc_allowed) {
						VikError::raiseWarning('', JText::_('VBOERRCANNOTCANCBOOK'));
						$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts']."&Itemid=".VikRequest::getString('Itemid', '', 'request'), false));
						exit;
					}
					//make the cancellation in the db and update the administrator notes with the reason specified by the customer
					$new_adminotes = JText::_('VBOBOOKCANCELLEDEMAILSUBJ').' ('.$pemail.")\n".$preason."\n\n".$order[0]['adminnotes'];
					$q = "UPDATE `#__vikbooking_orders` SET `status`='cancelled',`adminnotes`=".$dbo->quote($new_adminotes)." WHERE `id`=".(int)$order[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$order[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$ordbusy = $dbo->loadAssocList();
						foreach ($ordbusy as $ob) {
							$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`=".(int)$ob['idbusy'].";";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
					$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$order[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					//
					//Booking History
					VikBooking::getBookingHistoryInstance()->setBid($order[0]['id'])->store('CW', $preason);
					//
					//invoke VikChannelManager
					if (file_exists(VCM_SITE_PATH . DS . "helpers" . DS . "synch.vikbooking.php")) {
						$vcm_obj = VikBooking::getVcmInvoker();
						$vcm_obj->setOids(array($order[0]['id']))->setSyncType('cancel');
						$vcm_obj->doSync();
					}
					//end invoke VikChannelManager
					//send email to the administrator
					$subject = JText::_('VBOBOOKCANCELLEDEMAILSUBJ');
					$msg = JText::sprintf('VBOBOOKCANCELLEDEMAILHEAD', $order[0]['id'], JURI::root().'index.php?option=com_vikbooking&task=vieworder&sid='.$order[0]['sid'].'&ts='.$order[0]['ts'])."\n\n".$preason;
					$vbo_app = VikBooking::getVboApplication();
					$vbo_app->sendMail($adsendermail, $adsendermail, $to, $pemail, $subject, $msg, false);
					//go back to the booking details page to show the new status
					$mainframe->enqueueMessage(JText::_('VBOBOOKCANCELLEDRESP'));
					$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts']."&Itemid=".VikRequest::getString('Itemid', '', 'request'), false));
				} else {
					VikError::raiseWarning('', JText::_('VBOERRMISSDATA'));
					$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts']."&Itemid=".VikRequest::getString('Itemid', '', 'request'), false));
				}
			} else {
				$mainframe->redirect("index.php");
			}
		} else {
			$mainframe->redirect("index.php");
		}
	}

	function cancelmodification() {
		$psid = VikRequest::getString('sid', '', 'request');
		$pidorder = VikRequest::getString('id', '', 'request');
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		if (!empty($psid) && !empty($pidorder)) {
			$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".intval($pidorder)." AND `sid`=".$dbo->quote($psid)." AND `status`='confirmed';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$order = $dbo->loadAssocList();
				//unset the session value and redirect
				$session->set('vboModBooking', '');
				$mainframe->redirect(JRoute::_("index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'], false));
			} else {
				$mainframe->redirect("index.php");
			}
		} else {
			$mainframe->redirect("index.php");
		}
	}
	
	function tac_av_l() {
		require_once(VBO_SITE_PATH . DS . "helpers" . DS ."tac.vikbooking.php");
		
		//Channel Rates Module
		$pvbomodule = VikRequest::getInt('vbomodule', '', 'request');
		$pshow_tax = VikRequest::getInt('show_tax', '', 'request');
		$pdef_rplan = VikRequest::getInt('def_rplan', '', 'request');
		$pchannels_sel = VikRequest::getVar('channels_sel', array());
		$pcheckin = VikRequest::getString('checkin', '', 'request');
		$pcheckout = VikRequest::getString('checkout', '', 'request');
		if ($pvbomodule > 0 && !empty($pcheckin) && !empty($pcheckout)) {
			//this is an ajax request, probably made by the module Vik Booking Channel Rates
			//we need to prepare some variables before calling the method.
			$start_date = date('Y-m-d', VikBooking::getDateTimestamp($pcheckin, 12, 0));
			$end_date = date('Y-m-d', VikBooking::getDateTimestamp($pcheckout, 10, 0));
			//set (only some) request variables (the rest is sent via Ajax)
			VikRequest::setVar('e4jauth', md5('vbo.e4j.vbo'));
			VikRequest::setVar('req_type', 'hotel_availability');
			VikRequest::setVar('start_date', $start_date);
			VikRequest::setVar('end_date', $end_date);
			//make call to get the result
			TACVBO::$getArray = true;
			$website_rates = TACVBO::tac_av_l();
			//validate response
			if (!is_array($website_rates)) {
				//error returned
				echo json_encode(array('e4j.error' => $website_rates));
				exit;
			}
			if (is_array($website_rates) && isset($website_rates['e4j.error'])) {
				//another type of error returned
				echo json_encode($website_rates);
				exit;
			}
			if (is_array($website_rates) && !(count($website_rates) > 0)) {
				//empty response
				echo json_encode(array('e4j.error' => 'empty response'));
				exit;
			}
			//get the list of channels connected, filtered by ID
			$channels_map = VikBooking::getChannelsMap($pchannels_sel);
			//get the array with the lowest and preferred room rate
			$best_room_rate = VikBooking::getBestRoomRate($website_rates, $pdef_rplan);
			//get the charge/discount value for the OTAs rates from the Bulk Rates Cache of VCM
			$otas_rates_val = VikBooking::getOtasRatesVal($best_room_rate);
			$otas_rmod = '';
			$otas_rmodpcent = 0;
			$otas_rmodval = 0;
			if (!empty($otas_rates_val)) {
				$otas_rmod = substr($otas_rates_val, 0, 1); //+ or - (charge or discount)
				$otas_rmodpcent = substr($otas_rates_val, -1) == '%' ? 1 : 0;
				$otas_rmodval = (float)($otas_rmodpcent > 0 ? substr($otas_rates_val, 1, (strlen($otas_rates_val) - 2)) : substr($otas_rates_val, 1, (strlen($otas_rates_val) - 1)));
			}
			if (!(count($best_room_rate) > 0)) {
				//nothing to parse
				echo json_encode(array('e4j.error' => 'no rates'));
				exit;
			}
			//build the response
			$final_cost = $pshow_tax > 0 ? ($best_room_rate['cost'] + $best_room_rate['taxes']) : $best_room_rate['cost'];
			$rates_resp = array(
				'website' => VikBooking::numberFormat($final_cost)
			);
			if (count($channels_map)) {
				$rates_resp['channels'] = array();
			}
			foreach ($channels_map as $ch) {
				$ch_final_cost = $final_cost;
				if (!empty($otas_rmod)) {
					if ($otas_rmod == '+') {
						//charge
						if ($otas_rmodpcent > 0) {
							//percentage
							$ch_final_cost = $ch_final_cost * (100 + $otas_rmodval) / 100;
						} else {
							//absolute
							$ch_final_cost += $otas_rmodval;
						}
					} else {
						//discount (must be a fool)
						if ($otas_rmodpcent > 0) {
							//percentage
							$ch_final_cost = $ch_final_cost / (($otas_rmodval / 100) + 1);
						} else {
							//absolute
							$ch_final_cost -= $otas_rmodval;
						}
					}
				}
				$rates_resp['channels'][$ch['id']] = VikBooking::numberFormat($ch_final_cost);
			}
			//output the response
			echo json_encode($rates_resp);
			exit;
		}
		//

		//proceed with the standard request (that will end with an exit;)
		TACVBO::tac_av_l();
	}
	
}
