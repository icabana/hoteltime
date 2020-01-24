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

if (!defined('VBO_ADMIN_URI')) {
	//this library could be loaded by modules or VCM, so we need to load at least the Defines Adapter file.
	include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "defines.php");
}

if (!function_exists('showSelectVb')) {
	function showSelectVb($err, $err_code_info = array()) {
		include(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'error_form.php');
	}
}

class VikBooking {
	
	public static function addJoomlaUser($name, $username, $email, $password) {
		//new method
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_users');
		$user = new JUser;
		$data = array();
		//Get the default new user group, Registered if not specified.
		$system = $params->get('new_usertype', 2);
		$data['groups'] = array();
		$data['groups'][] = $system;
		$data['name'] = $name;
		$data['username'] = $username;
		$data['email'] = self::getVboApplication()->emailToPunycode($email);
		$data['password'] = $password;
		$data['password2'] = $password;
		$data['sendEmail'] = 0; //should the user receive system mails?
		//$data['block'] = 0;
		if (!$user->bind($data)) {
			VikError::raiseWarning('', JText::_($user->getError()));
			return false;
		}
		if (!$user->save()) {
			VikError::raiseWarning('', JText::_($user->getError()));
			return false;
		}
		return $user->id;
	}
	
	public static function userIsLogged () {
		$user = JFactory::getUser();
		if ($user->guest) {
			return false;
		} else {
			return true;
		}
	}

	public static function prepareViewContent() {
		$menu = JFactory::getApplication()->getMenu()->getActive();
		//Joomla 3.7.x - property params is now protected, before it was public
		$menuParams = null;
		if (method_exists($menu, 'getParams')) {
			$menuParams = $menu->getParams();
		} elseif (isset($menu->params)) {
			//Until Joomla 3.6.5
			$menuParams = $menu->params;
		}
		//
		if ($menuParams !== null) {
			$document = JFactory::getDocument();
			if ( intval($menuParams->get('show_page_heading')) == 1 && strlen($menuParams->get('page_heading')) ) {
				echo '<div class="page-header'.(strlen($clazz = $menuParams->get('pageclass_sfx')) ? ' '.$clazz : '' ).'"><h1>'.$menuParams->get('page_heading').'</h1></div>';
			}
			if ( strlen($menuParams->get('menu-meta_description')) ) {
				$document->setDescription($menuParams->get('menu-meta_description'));
			}
			if ( strlen($menuParams->get('menu-meta_keywords')) ) {
				$document->setMetadata('keywords', $menuParams->get('menu-meta_keywords'));
			}
			if ( strlen($menuParams->get('robots')) ) {
				$document->setMetadata('robots', $menuParams->get('robots'));
			}
		}
	}

	public static function isFontAwesomeEnabled($skipsession = false) {
		if (!$skipsession) {
			$session = JFactory::getSession();
			$s = $session->get('vbofaw', '');
			if (strlen($s)) {
				return ((int)$s == 1);
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='usefa';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			if (!$skipsession) {
				$session->set('vbofaw', $s);
			}
			return ((int)$s == 1);
		}
		$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('usefa', '1');";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$skipsession) {
			$session->set('vbofaw', '1');
		}
		return true;
	}

	public static function loadFontAwesome($force_load = false) {
		if (!self::isFontAwesomeEnabled() && !$force_load) {
			return false;
		}
		$document = JFactory::getDocument();
		$document->addStyleSheet(VBO_SITE_URI.'resources/font-awesome.min.css');

		return true;
	}

	/**
	 * Checks if modifications or cancellations via front-end are allowed.
	 * 0 = everything is Disabled.
	 * 1 = Disabled, with request message (default).
	 * 2 = Modification Enabled, Cancellation Disabled.
	 * 3 = Cancellation Enabled, Modification Disabled.
	 * 4 = everything is Enabled.
	 *
	 * @return 	int
	 */
	public static function getReservationModCanc() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='resmodcanc';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			return (int)$s;
		}
		$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('resmodcanc', '1');";
		$dbo->setQuery($q);
		$dbo->execute();
		return 1;
	}

	public static function getReservationModCancMin() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='resmodcancmin';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			return (int)$s;
		}
		$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('resmodcancmin', '1');";
		$dbo->setQuery($q);
		$dbo->execute();
		return 1;
	}

	public static function getDefaultDistinctiveFeatures() {
		$features = array();
		$features['VBODEFAULTDISTFEATUREONE'] = '';
		//Below is the default feature for 'Room Code'. One default feature is sufficient
		//$features['VBODEFAULTDISTFEATURETWO'] = '';
		return $features;
	}

	public static function getRoomUnitNumsUnavailable($order, $idroom) {
		$dbo = JFactory::getDBO();
		$unavailable_indexes = array();
		$first = $order['checkin'];
		$second = $order['checkout'];
		$secdiff = $second - $first;
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
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		$groupdays = self::getGroupDays($first, $second, $daysdiff);
		$q = "SELECT `b`.`id`,`b`.`checkin`,`b`.`checkout`,`b`.`realback`,`ob`.`idorder`,`ob`.`idbusy`,`or`.`id` AS `or_id`,`or`.`idroom`,`or`.`roomindex`,`o`.`status` ".
			"FROM `#__vikbooking_busy` AS `b` ".
			"LEFT JOIN `#__vikbooking_ordersbusy` `ob` ON `ob`.`idbusy`=`b`.`id` ".
			"LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idorder`=`ob`.`idorder` AND `or`.`idorder`!=".(int)$order['id']." ".
			"LEFT JOIN `#__vikbooking_orders` `o` ON `o`.`id`=`or`.`idorder` AND `o`.`id`=`ob`.`idorder` AND `o`.`id`!=".(int)$order['id']." ".
			"WHERE `or`.`idroom`=".(int)$idroom." AND `b`.`checkout` > ".time()." AND `o`.`status`='confirmed' AND `ob`.`idorder`!=".(int)$order['id']." AND `ob`.`idorder` > 0;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$busy = $dbo->loadAssocList();
			foreach ($groupdays as $gday) {
				foreach ($busy as $bu) {
					if (empty($bu['roomindex']) || empty($bu['idorder'])) {
						continue;
					}
					if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {;
						$unavailable_indexes[$bu['or_id']] = $bu['roomindex'];
					} elseif (count($groupdays) == 2 && $gday == $groupdays[0]) {
						if ($groupdays[0] < $bu['checkin'] && $groupdays[0] < $bu['realback'] && $groupdays[1] > $bu['checkin'] && $groupdays[1] > $bu['realback']) {
							$unavailable_indexes[$bu['or_id']] = $bu['roomindex'];
						}
					}
				}
			}
		}

		return $unavailable_indexes;
	}

	public static function getRoomUnitNumsAvailable($order, $idroom) {
		$dbo = JFactory::getDBO();
		$unavailable_indexes = array();
		$available_indexes = array();
		$first = $order['checkin'];
		$second = $order['checkout'];
		$secdiff = $second - $first;
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
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		$groupdays = self::getGroupDays($first, $second, $daysdiff);
		$q = "SELECT `b`.`id`,`b`.`checkin`,`b`.`checkout`,`b`.`realback`,`ob`.`idorder`,`ob`.`idbusy`,`or`.`id` AS `or_id`,`or`.`idroom`,`or`.`roomindex`,`o`.`status` ".
			"FROM `#__vikbooking_busy` AS `b` ".
			"LEFT JOIN `#__vikbooking_ordersbusy` `ob` ON `ob`.`idbusy`=`b`.`id` ".
			"LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idorder`=`ob`.`idorder` AND `or`.`idorder`!=".(int)$order['id']." ".
			"LEFT JOIN `#__vikbooking_orders` `o` ON `o`.`id`=`or`.`idorder` AND `o`.`id`=`ob`.`idorder` AND `o`.`id`!=".(int)$order['id']." ".
			"WHERE `or`.`idroom`=".(int)$idroom." AND `b`.`checkout` > ".time()." AND `o`.`status`='confirmed' AND `ob`.`idorder`!=".(int)$order['id']." AND `ob`.`idorder` > 0;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$busy = $dbo->loadAssocList();
			foreach ($groupdays as $gday) {
				foreach ($busy as $bu) {
					if (empty($bu['roomindex']) || empty($bu['idorder'])) {
						continue;
					}
					if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
						$unavailable_indexes[$bu['or_id']] = $bu['roomindex'];
					} elseif (count($groupdays) == 2 && $gday == $groupdays[0]) {
						if ($groupdays[0] < $bu['checkin'] && $groupdays[0] < $bu['realback'] && $groupdays[1] > $bu['checkin'] && $groupdays[1] > $bu['realback']) {
							$unavailable_indexes[$bu['or_id']] = $bu['roomindex'];
						}
					}
				}
			}
		}
		$q = "SELECT `params` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$idroom.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$room_params = $dbo->loadResult();
			$room_params_arr = json_decode($room_params, true);
			if (array_key_exists('features', $room_params_arr) && is_array($room_params_arr['features']) && count($room_params_arr['features'])) {
				foreach ($room_params_arr['features'] as $rind => $rfeatures) {
					if (in_array($rind, $unavailable_indexes)) {
						continue;
					}
					$available_indexes[] = $rind;
				}
			}
		}

		return $available_indexes;
	}
	
	public static function loadRestrictions ($filters = true, $rooms = array()) {
		$restrictions = array();
		$dbo = JFactory::getDBO();
		if (!$filters) {
			$q = "SELECT * FROM `#__vikbooking_restrictions`;";
		} else {
			if (count($rooms) == 0) {
				$q = "SELECT * FROM `#__vikbooking_restrictions` WHERE `allrooms`=1;";
			} else {
				$clause = array();
				foreach ($rooms as $idr) {
					if (empty($idr)) continue;
					$clause[] = "`idrooms` LIKE '%-".intval($idr)."-%'";
				}
				if (count($clause) > 0) {
					$q = "SELECT * FROM `#__vikbooking_restrictions` WHERE `allrooms`=1 OR (`allrooms`=0 AND (".implode(" OR ", $clause)."));";
				} else {
					$q = "SELECT * FROM `#__vikbooking_restrictions` WHERE `allrooms`=1;";
				}
			}
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allrestrictions = $dbo->loadAssocList();
			foreach ($allrestrictions as $k=>$res) {
				if (!empty($res['month'])) {
					$restrictions[$res['month']] = $res;
				} else {
					if (!isset($restrictions['range'])) {
						$restrictions['range'] = array();
					}
					$restrictions['range'][$k] = $res;
				}
			}
		}
		return $restrictions;
	}
	
	public static function globalRestrictions ($restrictions) {
		$ret = array();
		if (count($restrictions) > 0) {
			foreach($restrictions as $kr => $rr) {
				if ($kr == 'range') {
					foreach ($rr as $kd => $dr) {
						if ($dr['allrooms'] == 1) {
							$ret['range'][$kd] = $restrictions[$kr][$kd];
						}
					}
				} else {
					if ($rr['allrooms'] == 1) {
						$ret[$kr] = $restrictions[$kr];
					}
				}
			}
		}
		return $ret;
	}

	public static function parseSeasonRestrictions ($first, $second, $daysdiff, $restrictions) {
		$season_restrictions = array();
		$restrcheckin = getdate($first);
		$restrcheckout = getdate($second);
		if (array_key_exists($restrcheckin['mon'], $restrictions)) {
			//restriction found for this month, checking:
			$season_restrictions['id'] = $restrictions[$restrcheckin['mon']]['id'];
			$season_restrictions['name'] = $restrictions[$restrcheckin['mon']]['name'];
			$season_restrictions['allowed'] = true; //set to false when these nights are not allowed
			if (strlen($restrictions[$restrcheckin['mon']]['wday']) > 0) {
				//Week Day Arrival Restriction
				$rvalidwdays = array($restrictions[$restrcheckin['mon']]['wday']);
				if (strlen($restrictions[$restrcheckin['mon']]['wdaytwo']) > 0) {
					$rvalidwdays[] = $restrictions[$restrcheckin['mon']]['wdaytwo'];
				}
				$season_restrictions['wdays'] = $rvalidwdays;
			} elseif (!empty($restrictions[$restrcheckin['mon']]['ctad']) || !empty($restrictions[$restrcheckin['mon']]['ctdd'])) {
				if (!empty($restrictions[$restrcheckin['mon']]['ctad'])) {
					$season_restrictions['cta'] = explode(',', $restrictions[$restrcheckin['mon']]['ctad']);
				}
				if (!empty($restrictions[$restrcheckin['mon']]['ctdd'])) {
					$season_restrictions['ctd'] = explode(',', $restrictions[$restrcheckin['mon']]['ctdd']);
				}
			}
			if (!empty($restrictions[$restrcheckin['mon']]['maxlos']) && $restrictions[$restrcheckin['mon']]['maxlos'] > 0 && $restrictions[$restrcheckin['mon']]['maxlos'] > $restrictions[$restrcheckin['mon']]['minlos']) {
				$season_restrictions['maxlos'] = $restrictions[$restrcheckin['mon']]['maxlos'];
				if ($daysdiff > $restrictions[$restrcheckin['mon']]['maxlos']) {
					$season_restrictions['allowed'] = false;
				}
			}
			if ($daysdiff < $restrictions[$restrcheckin['mon']]['minlos']) {
				$season_restrictions['allowed'] = false;
			}
			$season_restrictions['minlos'] = $restrictions[$restrcheckin['mon']]['minlos'];
		} elseif (array_key_exists('range', $restrictions)) {
			foreach($restrictions['range'] as $restr) {
				if ($restr['dfrom'] <= $first && $restr['dto'] >= $first) {
					//restriction found for this date range, checking:
					$season_restrictions['id'] = $restr['id'];
					$season_restrictions['name'] = $restr['name'];
					$season_restrictions['allowed'] = true; //set to false when these nights are not allowed
					if (strlen($restr['wday']) > 0) {
						//Week Day Arrival Restriction
						$rvalidwdays = array($restr['wday']);
						if (strlen($restr['wdaytwo']) > 0) {
							$rvalidwdays[] = $restr['wdaytwo'];
						}
						$season_restrictions['wdays'] = $rvalidwdays;
					} elseif (!empty($restr['ctad']) || !empty($restr['ctdd'])) {
						if (!empty($restr['ctad'])) {
							$season_restrictions['cta'] = explode(',', $restr['ctad']);
						}
						if (!empty($restr['ctdd'])) {
							$season_restrictions['ctd'] = explode(',', $restr['ctdd']);
						}
					}
					if (!empty($restr['maxlos']) && $restr['maxlos'] > 0 && $restr['maxlos'] > $restr['minlos']) {
						$season_restrictions['maxlos'] = $restr['maxlos'];
						if ($daysdiff > $restr['maxlos']) {
							$season_restrictions['allowed'] = false;
						}
					}
					if ($daysdiff < $restr['minlos']) {
						$season_restrictions['allowed'] = false;
					}
					$season_restrictions['minlos'] = $restr['minlos'];
				}
			}
		}

		return $season_restrictions;
	}

	public static function compareSeasonRestrictionsNights ($restrictions) {
		$base_compare = array();
		$base_nights = 0;
		foreach ($restrictions as $nights => $restr) {
			$base_compare = $restr;
			$base_nights = $nights;
			break;
		}
		foreach ($restrictions as $nights => $restr) {
			if ($nights == $base_nights) {
				continue;
			}
			$diff = array_diff($base_compare, $restr);
			if (count($diff) > 0 && array_key_exists('id', $diff)) {
				//return differences only if the Restriction ID is different: ignore allowed, wdays, minlos, maxlos.
				//only one Restriction per time should be applied to certain Season Dates but check just in case.
				return $diff;
			}
		}

		return array();
	}
	
	public static function roomRestrictions ($roomid, $restrictions) {
		$ret = array();
		if (!empty($roomid) && count($restrictions) > 0) {
			foreach($restrictions as $kr => $rr) {
				if ($kr == 'range') {
					foreach ($rr as $kd => $dr) {
						if ($dr['allrooms'] == 0 && !empty($dr['idrooms'])) {
							$allrooms = explode(';', $dr['idrooms']);
							if (in_array('-'.$roomid.'-', $allrooms)) {
								$ret['range'][$kd] = $restrictions[$kr][$kd];
							}
						}
					}
				} else {
					if ($rr['allrooms'] == 0 && !empty($rr['idrooms'])) {
						$allrooms = explode(';', $rr['idrooms']);
						if (in_array('-'.$roomid.'-', $allrooms)) {
							$ret[$kr] = $restrictions[$kr];
						}
					}
				}
			}
		}
		return $ret;
	}
	
	public static function validateRoomRestriction ($roomrestr, $restrcheckin, $restrcheckout, $daysdiff) {
		$restrictionerrmsg = '';
		$restrictions_affcount = 0;
		if (array_key_exists($restrcheckin['mon'], $roomrestr)) {
			//restriction found for this month, checking:
			$restrictions_affcount++;
			if (strlen($roomrestr[$restrcheckin['mon']]['wday']) > 0) {
				$rvalidwdays = array($roomrestr[$restrcheckin['mon']]['wday']);
				if (strlen($roomrestr[$restrcheckin['mon']]['wdaytwo']) > 0) {
					$rvalidwdays[] = $roomrestr[$restrcheckin['mon']]['wdaytwo'];
				}
				if (!in_array($restrcheckin['wday'], $rvalidwdays)) {
					$restrictionerrmsg = JText::sprintf('VBRESTRTIPWDAYARRIVAL', self::sayMonth($restrcheckin['mon']), self::sayWeekDay($roomrestr[$restrcheckin['mon']]['wday']).(strlen($roomrestr[$restrcheckin['mon']]['wdaytwo']) > 0 ? '/'.self::sayWeekDay($roomrestr[$restrcheckin['mon']]['wdaytwo']) : ''));
				} elseif ($roomrestr[$restrcheckin['mon']]['multiplyminlos'] == 1) {
					if (($daysdiff % $roomrestr[$restrcheckin['mon']]['minlos']) != 0) {
						$restrictionerrmsg = JText::sprintf('VBRESTRTIPMULTIPLYMINLOS', self::sayMonth($restrcheckin['mon']), $roomrestr[$restrcheckin['mon']]['minlos']);
					}
				}
				$comborestr = self::parseJsDrangeWdayCombo($roomrestr[$restrcheckin['mon']]);
				if (count($comborestr) > 0) {
					if (array_key_exists($restrcheckin['wday'], $comborestr)) {
						if (!in_array($restrcheckout['wday'], $comborestr[$restrcheckin['wday']])) {
							$restrictionerrmsg = JText::sprintf('VBRESTRTIPWDAYCOMBO', self::sayMonth($restrcheckin['mon']), self::sayWeekDay($comborestr[$restrcheckin['wday']][0]).(count($comborestr[$restrcheckin['wday']]) == 2 ? '/'.self::sayWeekDay($comborestr[$restrcheckin['wday']][1]) : ''), self::sayWeekDay($restrcheckin['wday']));
						}
					}
				}
			} elseif (!empty($roomrestr[$restrcheckin['mon']]['ctad']) || !empty($roomrestr[$restrcheckin['mon']]['ctdd'])) {
				if (!empty($roomrestr[$restrcheckin['mon']]['ctad'])) {
					$ctarestrictions = explode(',', $roomrestr[$restrcheckin['mon']]['ctad']);
					if (in_array('-'.$restrcheckin['wday'].'-', $ctarestrictions)) {
						$restrictionerrmsg = JText::sprintf('VBRESTRERRWDAYCTAMONTH', self::sayWeekDay($restrcheckin['wday']), self::sayMonth($restrcheckin['mon']));
					}
				}
				if (!empty($roomrestr[$restrcheckin['mon']]['ctdd'])) {
					$ctdrestrictions = explode(',', $roomrestr[$restrcheckin['mon']]['ctdd']);
					if (in_array('-'.$restrcheckout['wday'].'-', $ctdrestrictions)) {
						$restrictionerrmsg = JText::sprintf('VBRESTRERRWDAYCTDMONTH', self::sayWeekDay($restrcheckout['wday']), self::sayMonth($restrcheckin['mon']));
					}
				}
			}
			if (!empty($roomrestr[$restrcheckin['mon']]['maxlos']) && $roomrestr[$restrcheckin['mon']]['maxlos'] > 0 && $roomrestr[$restrcheckin['mon']]['maxlos'] > $roomrestr[$restrcheckin['mon']]['minlos']) {
				if ($daysdiff > $roomrestr[$restrcheckin['mon']]['maxlos']) {
					$restrictionerrmsg = JText::sprintf('VBRESTRTIPMAXLOSEXCEEDED', self::sayMonth($restrcheckin['mon']), $roomrestr[$restrcheckin['mon']]['maxlos']);
				}
			}
			if ($daysdiff < $roomrestr[$restrcheckin['mon']]['minlos']) {
				$restrictionerrmsg = JText::sprintf('VBRESTRTIPMINLOSEXCEEDED', self::sayMonth($restrcheckin['mon']), $roomrestr[$restrcheckin['mon']]['minlos']);
			}
		} elseif (array_key_exists('range', $roomrestr)) {
			$restrictionsvalid = true;
			foreach($roomrestr['range'] as $restr) {
				if ($restr['dfrom'] <= $restrcheckin[0] && ($restr['dto'] + 82799) >= $restrcheckin[0]) {
					//restriction found for this date range, checking:
					$restrictions_affcount++;
					if (strlen($restr['wday']) > 0) {
						$rvalidwdays = array($restr['wday']);
						if (strlen($restr['wdaytwo']) > 0) {
							$rvalidwdays[] = $restr['wdaytwo'];
						}
						if (!in_array($restrcheckin['wday'], $rvalidwdays)) {
							$restrictionsvalid = false;
							$restrictionerrmsg = JText::sprintf('VBRESTRTIPWDAYARRIVALRANGE', self::sayWeekDay($restr['wday']).(strlen($restr['wdaytwo']) > 0 ? '/'.self::sayWeekDay($restr['wdaytwo']) : ''));
						} elseif ($restr['multiplyminlos'] == 1) {
							if (($daysdiff % $restr['minlos']) != 0) {
								$restrictionsvalid = false;
								$restrictionerrmsg = JText::sprintf('VBRESTRTIPMULTIPLYMINLOSRANGE', $restr['minlos']);
							}
						}
						$comborestr = self::parseJsDrangeWdayCombo($restr);
						if (count($comborestr) > 0) {
							if (array_key_exists($restrcheckin['wday'], $comborestr)) {
								if (!in_array($restrcheckout['wday'], $comborestr[$restrcheckin['wday']])) {
									$restrictionsvalid = false;
									$restrictionerrmsg = JText::sprintf('VBRESTRTIPWDAYCOMBORANGE', self::sayWeekDay($comborestr[$restrcheckin['wday']][0]).(count($comborestr[$restrcheckin['wday']]) == 2 ? '/'.self::sayWeekDay($comborestr[$restrcheckin['wday']][1]) : ''), self::sayWeekDay($restrcheckin['wday']));
								}
							}
						}
					} elseif (!empty($restr['ctad']) || !empty($restr['ctdd'])) {
						if (!empty($restr['ctad'])) {
							$ctarestrictions = explode(',', $restr['ctad']);
							if (in_array('-'.$restrcheckin['wday'].'-', $ctarestrictions)) {
								$restrictionerrmsg = JText::sprintf('VBRESTRERRWDAYCTARANGE', self::sayWeekDay($restrcheckin['wday']));
							}
						}
						if (!empty($restr['ctdd'])) {
							$ctdrestrictions = explode(',', $restr['ctdd']);
							if (in_array('-'.$restrcheckout['wday'].'-', $ctdrestrictions)) {
								$restrictionerrmsg = JText::sprintf('VBRESTRERRWDAYCTDRANGE', self::sayWeekDay($restrcheckout['wday']));
							}
						}
					}
					if (!empty($restr['maxlos']) && $restr['maxlos'] > 0 && $restr['maxlos'] > $restr['minlos']) {
						if ($daysdiff > $restr['maxlos']) {
							$restrictionsvalid = false;
							$restrictionerrmsg = JText::sprintf('VBRESTRTIPMAXLOSEXCEEDEDRANGE', $restr['maxlos']);
						}
					}
					if ($daysdiff < $restr['minlos']) {
						$restrictionsvalid = false;
						$restrictionerrmsg = JText::sprintf('VBRESTRTIPMINLOSEXCEEDEDRANGE', $restr['minlos']);
					}
					if ($restrictionsvalid == false) {
						break;
					}
				}
			}
		}
		//April 2017 - Check global restriction of Min LOS for TAC functions in VBO and VCM
		if (empty($restrictionerrmsg) && count($roomrestr) && $restrictions_affcount <= 0) {
			//Check global MinLOS (only in case there are no restrictions affecting these dates or no restrictions at all)
			$globminlos = self::getDefaultNightsCalendar();
			if ($globminlos > 1 && $daysdiff < $globminlos) {
				$restrictionerrmsg = JText::sprintf('VBRESTRERRMINLOSEXCEEDEDRANGE', $globminlos);
			}
		}
		//

		return $restrictionerrmsg;
	}
	
	public static function parseJsDrangeWdayCombo ($drestr) {
		$combo = array();
		if (strlen($drestr['wday']) > 0 && strlen($drestr['wdaytwo']) > 0 && !empty($drestr['wdaycombo'])) {
			$cparts = explode(':', $drestr['wdaycombo']);
			foreach($cparts as $kc => $cw) {
				if (!empty($cw)) {
					$nowcombo = explode('-', $cw);
					$combo[intval($nowcombo[0])][] = intval($nowcombo[1]);
				}
			}
		}
		return $combo;
	}

	public static function validateRoomPackage($pkg_id, $rooms, $numnights, $checkints, $checkoutts) {
		$dbo = JFactory::getDBO();
		$pkg = array();
		$q = "SELECT * FROM `#__vikbooking_packages` WHERE `id`='".intval($pkg_id)."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$pkg = $dbo->loadAssoc();
			$vbo_tn = self::getTranslator();
			$vbo_tn->translateContents($pkg, '#__vikbooking_packages');
		} else {
			return JText::_('VBOPKGERRNOTFOUND');
		}
		$rooms_req = array();
		foreach ($rooms as $num => $room) {
			if (!empty($room['id']) && !in_array($room['id'], $rooms_req)) {
				$rooms_req[] = $room['id'];
			}
		}
		$q = "SELECT `id` FROM `#__vikbooking_packages_rooms` WHERE `idpackage`=".$pkg['id']." AND `idroom` IN (".implode(', ', $rooms_req).");";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() != count($rooms_req)) {
			//error, not all the rooms requested are available for this package
			return JText::_('VBOPKGERRNOTROOM');
		}
		if ($numnights < $pkg['minlos'] || ($pkg['maxlos'] > 0 && $numnights > $pkg['maxlos'])) {
			return JText::_('VBOPKGERRNUMNIGHTS');
		}
		if ($checkints < $pkg['dfrom'] || $checkints > $pkg['dto']) {
			return JText::_('VBOPKGERRCHECKIND');
		}
		if ($checkoutts < $pkg['dfrom'] || ($checkoutts > $pkg['dto'] && date('Y-m-d', $pkg['dfrom']) != date('Y-m-d', $pkg['dto']))) {
			//VBO 1.10 - we allow a check-out date after the pkg validity-end-date only if the validity dates are equal (dfrom & dto)
			return JText::_('VBOPKGERRCHECKOUTD');
		}
		if (!empty($pkg['excldates'])) {
			//this would check if any stay date is excluded
			//$bookdates_ts = self::getGroupDays($checkints, $checkoutts, $numnights);
			//check just the arrival and departure dates
			$bookdates_ts = array($checkints, $checkoutts);
			$bookdates = array();
			foreach ($bookdates_ts as $bookdate_ts) {
				$info_d = getdate($bookdate_ts);
				$bookdates[] = $info_d['mon'].'-'.$info_d['mday'].'-'.$info_d['year'];
			}
			$edates = explode(';', $pkg['excldates']);
			foreach ($edates as $edate) {
				if (!empty($edate) && in_array($edate, $bookdates)) {
					return JText::sprintf('VBOPKGERREXCLUDEDATE', $edate);
				}
			}
		}
		return $pkg;
	}

	public static function getPackage($pkg_id) {
		$dbo = JFactory::getDBO();
		$pkg = array();
		$q = "SELECT * FROM `#__vikbooking_packages` WHERE `id`='".intval($pkg_id)."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$pkg = $dbo->loadAssoc();
		}
		return $pkg;
	}
	
	public static function getRoomParam ($paramname, $paramstr) {
		if (empty($paramstr)) return '';
		$paramarr = json_decode($paramstr, true);
		if (array_key_exists($paramname, $paramarr)) {
			return $paramarr[$paramname];
		}
		return '';
	}

	public static function filterNightsSeasonsCal ($arr_nights) {
		$nights = array();
		foreach ($arr_nights as $night) {
			if (intval(trim($night)) > 0) {
				$nights[] = intval(trim($night));
			}
		}
		sort($nights);
		return array_unique($nights);
	}

	public static function getSeasonRangeTs ($from, $to, $year) {
		$sfrom = 0;
		$sto = 0;
		$tsbase = mktime(0, 0, 0, 1, 1, $year);
		$curyear = $year;
		$tsbasetwo = $tsbase;
		$curyeartwo = $year;
		if ($from > $to) {
			//between two years
			$curyeartwo += 1;
			$tsbasetwo = mktime(0, 0, 0, 1, 1, $curyeartwo);
		}
		$sfrom = ($tsbase + $from);
		$sto = ($tsbasetwo + $to);
		if ($curyear % 4 == 0 && ($curyear % 100 != 0 || $curyear % 400 == 0)) {
			//leap years
			$infoseason = getdate($sfrom);
			$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
			if ($infoseason[0] >= $leapts) {
				$sfrom += 86400;
				if ($curyear == $curyeartwo) {
					$sto += 86400;
				}
			}
		} elseif ($curyeartwo % 4 == 0 && ($curyeartwo % 100 != 0 || $curyeartwo % 400 == 0)) {
			//leap years
			$infoseason = getdate($sto);
			$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
			if ($infoseason[0] >= $leapts) {
				$sto += 86400;
			}
		}
		return array($sfrom, $sto);
	}

	public static function sortSeasonsRangeTs ($all_seasons) {
		$sorted = array();
		$map = array();
		foreach ($all_seasons as $key => $season) {
			$map[$key] = $season['from_ts'];
		}
		asort($map);
		foreach ($map as $key => $s) {
			$sorted[] = $all_seasons[$key];
		}
		return $sorted;
	}

	public static function formatSeasonDates ($from_ts, $to_ts) {
		$one = getdate($from_ts);
		$two = getdate($to_ts);
		$months_map = array(
			1 => JText::_('VBSHORTMONTHONE'),
			2 => JText::_('VBSHORTMONTHTWO'),
			3 => JText::_('VBSHORTMONTHTHREE'),
			4 => JText::_('VBSHORTMONTHFOUR'),
			5 => JText::_('VBSHORTMONTHFIVE'),
			6 => JText::_('VBSHORTMONTHSIX'),
			7 => JText::_('VBSHORTMONTHSEVEN'),
			8 => JText::_('VBSHORTMONTHEIGHT'),
			9 => JText::_('VBSHORTMONTHNINE'),
			10 => JText::_('VBSHORTMONTHTEN'),
			11 => JText::_('VBSHORTMONTHELEVEN'),
			12 => JText::_('VBSHORTMONTHTWELVE')
		);
		$mday_map = array(
			1 => JText::_('VBMDAYFRIST'),
			2 => JText::_('VBMDAYSECOND'),
			3 => JText::_('VBMDAYTHIRD'),
			'generic' => JText::_('VBMDAYNUMGEN')
		);
		if ($one['year'] == $two['year']) {
			return $one['year'].' '.$months_map[(int)$one['mon']].' '.$one['mday'].'<sup>'.(array_key_exists((int)substr($one['mday'], -1), $mday_map) && ($one['mday'] < 10 || $one['mday'] > 20) ? $mday_map[(int)substr($one['mday'], -1)] : $mday_map['generic']).'</sup> - '.$months_map[(int)$two['mon']].' '.$two['mday'].'<sup>'.(array_key_exists((int)substr($two['mday'], -1), $mday_map) && ($two['mday'] < 10 || $two['mday'] > 20) ? $mday_map[(int)substr($two['mday'], -1)] : $mday_map['generic']).'</sup>';
		}
		return $months_map[(int)$one['mon']].' '.$one['mday'].'<sup>'.(array_key_exists((int)substr($one['mday'], -1), $mday_map) && ($one['mday'] < 10 || $one['mday'] > 20) ? $mday_map[(int)substr($one['mday'], -1)] : $mday_map['generic']).'</sup> '.$one['year'].' - '.$months_map[(int)$two['mon']].' '.$two['mday'].'<sup>'.(array_key_exists((int)substr($two['mday'], -1), $mday_map) && ($two['mday'] < 10 || $two['mday'] > 20) ? $mday_map[(int)substr($two['mday'], -1)] : $mday_map['generic']).'</sup> '.$two['year'];
	}

	public static function getFirstCustDataField($custdata) {
		$first_field = '';
		if (strpos($custdata, JText::_('VBDBTEXTROOMCLOSED')) !== false) {
			//Room is closed with this booking
			return '----';
		}
		$parts = explode("\n", $custdata);
		foreach ($parts as $part) {
			if (!empty($part)) {
				$field = explode(':', trim($part));
				if (!empty($field[1])) {
					return trim($field[1]);
				}
			}
		}
		return $first_field;
	}

	/**
	 * This method composes a string to be logged for the admin
	 * to keep track of what was inside the booking before the
	 * modification. Returns a string and it uses language definitions
	 * that should be available on the front-end and back-end INI files.
	 *
	 * @param 	array 		$old_booking 	the array of the booking prior to the modification
	 *
	 * @return 	string
	 */
	public static function getLogBookingModification($old_booking) {
		$vbo_df = self::getDateFormat();
		$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y-m-d');
		$wdays_map = array(
			JText::_('VBWEEKDAYZERO'),
			JText::_('VBWEEKDAYONE'),
			JText::_('VBWEEKDAYTWO'),
			JText::_('VBWEEKDAYTHREE'),
			JText::_('VBWEEKDAYFOUR'),
			JText::_('VBWEEKDAYFIVE'),
			JText::_('VBWEEKDAYSIX')
		);
		$now_info = getdate();
		$checkin_info = getdate($old_booking['checkin']);
		$checkout_info = getdate($old_booking['checkout']);

		$datemod = $wdays_map[$now_info['wday']].', '.date($df.' H:i', $now_info[0]);
		$prev_nights = $old_booking['days'].' '.($old_booking['days'] > 1 ? JText::_('VBDAYS') : JText::_('VBDAY'));
		$prev_dates = $prev_nights.' - '.$wdays_map[$checkin_info['wday']].', '.date($df, $checkin_info[0]).' - '.$wdays_map[$checkout_info['wday']].', '.date($df, $checkout_info[0]);
		$prev_rooms = '';
		if (isset($old_booking['rooms_info'])) {
			$orooms_arr = array();
			foreach ($old_booking['rooms_info'] as $oroom) {
				$orooms_arr[] = $oroom['name'].', '.JText::_('VBMAILADULTS').': '.$oroom['adults'].', '.JText::_('VBMAILCHILDREN').': '.$oroom['children'];
			}
			$prev_rooms = implode("\n", $orooms_arr);
		}
		$currencyname = self::getCurrencyName();
		$prev_total = $currencyname.' '.self::numberFormat($old_booking['total']);

		return JText::sprintf('VBOBOOKMODLOGSTR', $datemod, $prev_dates, $prev_rooms, $prev_total);
	}

	/**
	 * This method (new in VBO 1.10) invokes the class
	 * VikChannelManagerLogos (new in VCM 1.6.4) to
	 * map the name of a channel to its corresponding logo.
	 * The method can also be used to get an istance of the class.
	 *
	 * @param 	string 		$provenience
	 * @param 	boolean 	$get_istance
	 *
	 * @return 	mixed 	boolean if the Class doesn't exist or if the provenience cannot be matched - object if get_istance
	 */
	public static function getVcmChannelsLogo($provenience, $get_istance = false) {
		if (!file_exists(VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'logos.php')) {
			return false;
		}
		if (!class_exists('VikChannelManagerLogos')) {
			require_once(VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'logos.php');
		}
		$obj = new VikChannelManagerLogos($provenience);
		return $get_istance ? $obj : $obj->getLogoURL();
	}

	public static function vcmAutoUpdate() {
		if (!file_exists(VCM_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikchannelmanager.php')) {
			return -1;
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='vcmautoupd';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) > 0 ? 1 : 0);
	}

	public static function getVcmInvoker() {
		if (!class_exists('VboVcmInvoker')) {
			require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."vcm.php");
		}
		return new VboVcmInvoker();
	}

	public static function getBookingHistoryInstance() {
		if (!class_exists('VboBookingHistory')) {
			require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."history.php");
		}
		return new VboBookingHistory();
	}

	public static function vcmBcomReportingSupported() {
		if (!file_exists(VCM_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikchannelmanager.php')) {
			return false;
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`='4';";
		$dbo->setQuery($q);
		$dbo->execute();
		return ($dbo->getNumRows() > 0);
	}
	
	public static function getTheme () {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='theme';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}
	
	public static function getFooterOrdMail($vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (!is_object($vbo_tn)) {
			$vbo_tn = self::getTranslator();
		}
		$vbo_tn->translateContents($ft, '#__vikbooking_texts');
		return $ft[0]['setting'];
	}
	
	public static function requireLogin() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='requirelogin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function autoRoomUnit() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='autoroomunit';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function todayBookings() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='todaybookings';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}
	
	public static function couponsEnabled() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='enablecoupons';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function customersPinEnabled() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='enablepin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}
	
	/**
	 * Detects the type of visitor from the user agent.
	 * Known types are: computer, smartphone, tablet.
	 * 
	 * @param 	boolean  $returnua 		whether the type of visitor should be returned. If false
	 * 									boolean is returned in case of mobile device detected.
	 * @param 	boolean  $loadassets 	whether the system should load an apposite CSS file.
	 * 
	 * @return 	mixed 	 string for the type of visitor or boolean if mobile detected.
	 * 
	 * @since 	1.10 - Revision September 2018
	 */
	public static function detectUserAgent($returnua = false, $loadassets = true) {
		$session = JFactory::getSession();
		$sval = $session->get('vbuseragent', '');
		$mobiles = array('tablet', 'smartphone');
		if (!empty($sval)) {
			if ($loadassets) {
				self::userAgentStyleSheet($sval);
			}
			return $returnua ? $sval : in_array($sval, $mobiles);
		}
		if (!class_exists('MobileDetector')) {
			require_once(VBO_SITE_PATH . DS . "helpers" . DS ."mobile_detector.php");
		}
		$detector = new MobileDetector;
		$visitoris = $detector->isMobile() ? ($detector->isTablet() ? 'tablet' : 'smartphone') : 'computer';
		$session->set('vbuseragent', $visitoris);
		if ($loadassets) {
			self::userAgentStyleSheet($visitoris);
		}
		return $returnua ? $visitoris : in_array($visitoris, $mobiles);
	}
	
	public static function userAgentStyleSheet($ua) {
		$document = JFactory::getDocument();
		if ($ua == 'smartphone') {
			$document->addStyleSheet(VBO_SITE_URI.'vikbooking_smartphones.css');
		} elseif ($ua == 'tablet') {
			$document->addStyleSheet(VBO_SITE_URI.'vikbooking_tablets.css');
		}
		return true;
	}
	
	public static function loadJquery($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='loadjquery';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return intval($s[0]['setting']) == 1 ? true : false;
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbloadJquery', '');
			if (!empty($sval)) {
				return intval($sval) == 1 ? true : false;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='loadjquery';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbloadJquery', $s[0]['setting']);
				return intval($s[0]['setting']) == 1 ? true : false;
			}
		}
	}

	public static function loadBootstrap($skipsession = false) {
		$dbo = JFactory::getDBO();
		if ($skipsession) {
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='bootstrap';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return intval($s[0]['setting']) == 1 ? true : false;
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbBootstrap', '');
			if (!empty($sval)) {
				return intval($sval) == 1 ? true : false;
			} else {
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='bootstrap';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbBootstrap', $s[0]['setting']);
				return intval($s[0]['setting']) == 1 ? true : false;
			}
		}
	}

	public static function allowMultiLanguage($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='multilang';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return intval($s[0]['setting']) == 1 ? true : false;
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbMultiLang', '');
			if (!empty($sval)) {
				return intval($sval) == 1 ? true : false;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='multilang';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbMultiLang', $s[0]['setting']);
				return intval($s[0]['setting']) == 1 ? true : false;
			}
		}
	}

	public static function getTranslator() {
		if (!class_exists('VikBookingTranslator')) {
			require_once(VBO_SITE_PATH . DS . "helpers" . DS . "translator.php");
		}
		return new VikBookingTranslator();
	}

	public static function getCPinIstance() {
		if (!class_exists('VikBookingCustomersPin')) {
			require_once(VBO_SITE_PATH . DS . "helpers" . DS . "cpin.php");
		}
		return new VikBookingCustomersPin();
	}
	
	public static function getMinDaysAdvance($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='mindaysadvance';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (int)$s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbminDaysAdvance', '');
			if (!empty($sval)) {
				return (int)$sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='mindaysadvance';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbminDaysAdvance', $s[0]['setting']);
				return (int)$s[0]['setting'];
			}
		}
	}
	
	public static function getDefaultNightsCalendar($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='autodefcalnights';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (int)$s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbdefaultNightsCalendar', '');
			if (!empty($sval)) {
				return (int)$sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='autodefcalnights';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbdefaultNightsCalendar', $s[0]['setting']);
				return (int)$s[0]['setting'];
			}
		}
	}
	
	public static function getSearchNumRooms($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numrooms';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (int)$s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbsearchNumRooms', '');
			if (!empty($sval)) {
				return (int)$sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numrooms';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbsearchNumRooms', $s[0]['setting']);
				return (int)$s[0]['setting'];
			}
		}
	}
	
	public static function getSearchNumAdults($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numadults';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbsearchNumAdults', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numadults';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbsearchNumAdults', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getSearchNumChildren($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numchildren';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbsearchNumChildren', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numchildren';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbsearchNumChildren', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getSmartSearchType($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='smartsearch';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbsmartSearchType', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='smartsearch';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbsmartSearchType', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getMaxDateFuture($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='maxdate';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbmaxDateFuture', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='maxdate';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbmaxDateFuture', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function validateMaxDateBookings($checkints) {
		$datelim = self::getMaxDateFuture();
		$datelim = empty($datelim) ? '+2y' : $datelim;
		$numlim = (int)substr($datelim, 1, (strlen($datelim) - 2));
		$quantlim = substr($datelim, -1, 1);

		$now = getdate();
		if ($quantlim == 'w') {
			$until_ts = strtotime("+$numlim weeks") + 86399;
		} else {
			$until_ts = mktime(23, 59, 59, ($quantlim == 'm' ? ((int)$now['mon']+$numlim) : $now['mon']), ($quantlim == 'd' ? ((int)$now['mday']+$numlim) : $now['mday']), ($quantlim == 'y' ? ((int)$now['year']+$numlim) : $now['year']));
		}

		if ($until_ts > $now[0] && $checkints > $until_ts) {
			$vbo_df = self::getDateFormat();
			$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y-m-d');
			return date($df, $until_ts);
		}

		return '';
	}

	public static function validateMinDaysAdvance($checkints) {
		$mindadv = self::getMinDaysAdvance();
		if ($mindadv > 0) {
			$tsinfo = getdate($checkints);
			$limit_ts = mktime($tsinfo['hours'], $tsinfo['minutes'], $tsinfo['seconds'], date('n'), ((int)date('j') + $mindadv), date('Y'));
			if ($checkints < $limit_ts) {
				return $mindadv;
			}
		}

		return '';
	}
	
	public static function calendarType($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='calendar';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbcalendarType', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='calendar';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbcalendarType', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getSiteLogo() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getBackendLogo() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='backlogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		}
		return '';
	}
	
	public static function numCalendars() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numcalendars';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}
	
	public static function getFirstWeekDay($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='firstwday';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbfirstWeekDay', '');
			if (strlen($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='firstwday';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbfirstWeekDay', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function showPartlyReserved() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='showpartlyreserved';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function showStatusCheckinoutOnly() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='showcheckinoutonly';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function getDisclaimer($vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='disclaimer';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (!is_object($vbo_tn)) {
			$vbo_tn = self::getTranslator();
		}
		$vbo_tn->translateContents($ft, '#__vikbooking_texts');
		return $ft[0]['setting'];
	}

	public static function showFooter() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='showfooter';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			return false;
		}
	}

	public static function getPriceName($idp, $vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_prices` WHERE `id`=" . (int)$idp . "";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$n = $dbo->loadAssocList();
			if (is_object($vbo_tn)) {
				$vbo_tn->translateContents($n, '#__vikbooking_prices');
			}
			return $n[0]['name'];
		}
		return "";
	}

	public static function getPriceAttr($idp, $vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`attr` FROM `#__vikbooking_prices` WHERE `id`=" . (int)$idp . "";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$n = $dbo->loadAssocList();
			if (is_object($vbo_tn)) {
				$vbo_tn->translateContents($n, '#__vikbooking_prices');
			}
			return $n[0]['attr'];
		}
		return "";
	}
	
	public static function getPriceInfo($idp, $vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_prices` WHERE `id`=" . (int)$idp . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$n = $dbo->loadAssocList();
			if (is_object($vbo_tn)) {
				$vbo_tn->translateContents($n, '#__vikbooking_prices');
			}
			return $n[0];
		}
		return "";
	}
	
	public static function getAliq($idal) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `aliq` FROM `#__vikbooking_iva` WHERE `id`='" . $idal . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$n = $dbo->loadAssocList();
			return $n[0]['aliq'];
		}
		return "";
	}

	public static function getTimeOpenStore($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='timeopenstore';";
			$dbo->setQuery($q);
			$dbo->execute();
			$n = $dbo->loadAssocList();
			if (empty($n[0]['setting']) && $n[0]['setting'] != "0") {
				return false;
			} else {
				$x = explode("-", $n[0]['setting']);
				if (!empty($x[1]) && $x[1] != "0") {
					return $x;
				}
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbgetTimeOpenStore', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='timeopenstore';";
				$dbo->setQuery($q);
				$dbo->execute();
				$n = $dbo->loadAssocList();
				if (empty($n[0]['setting']) && $n[0]['setting'] != "0") {
					return false;
				} else {
					$x = explode("-", $n[0]['setting']);
					if (!empty($x[1]) && $x[1] != "0") {
						$session->set('vbgetTimeOpenStore', $x);
						return $x;
					}
				}
			}
		}
		return false;
	}

	public static function getHoursMinutes($secs) {
		if ($secs >= 3600) {
			$op = $secs / 3600;
			$hours = floor($op);
			$less = $hours * 3600;
			$newsec = $secs - $less;
			$optwo = $newsec / 60;
			$minutes = floor($optwo);
		} else {
			$hours = "0";
			$optwo = $secs / 60;
			$minutes = floor($optwo);
		}
		$x[] = $hours;
		$x[] = $minutes;
		return $x;
	}

	public static function getClosingDates() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='closingdates';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$s = $dbo->loadAssocList();
			if (!empty($s[0]['setting'])) {
				$allcd = json_decode($s[0]['setting'], true);
				$base_ts = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
				foreach ($allcd as $k => $v) {
					if ($v['to'] < $base_ts) {
						unset($allcd[$k]);
					}
				}
				$allcd = array_values($allcd);
				return $allcd;
			}
		}
		return array();
	}

	public static function parseJsClosingDates() {
		$cd = self::getClosingDates();
		if (count($cd) > 0) {
			$cdjs = array();
			foreach ($cd as $k => $v) {
				$cdjs[] = array(date('Y-m-d', $v['from']), date('Y-m-d', $v['to']));
			}
			return $cdjs;
		}
		return array();
	}

	public static function validateClosingDates($checkints, $checkoutts, $df) {
		$df = empty($df) ? 'Y-m-d' : $df;
		$cd = self::getClosingDates();
		if (count($cd) > 0) {
			foreach ($cd as $k => $v) {
				if ( ( $checkints >= $v['from'] && $checkints <= ($v['to'] + (22*60*60)) ) || ( $checkoutts >= $v['from'] && $checkoutts <= ($v['to'] + (22*60*60)) ) || ( $checkints <= $v['from'] && $checkoutts >= ($v['to'] + (22*60*60)) ) ) {
					return date($df, $v['from']) . ' - ' . date($df, $v['to']);
				}
			}
		}
		return '';
	}

	public static function showCategoriesFront($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='showcategories';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$s = $dbo->loadAssocList();
				return (intval($s[0]['setting']) == 1 ? true : false);
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbshowCategoriesFront', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='showcategories';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$s = $dbo->loadAssocList();
					$session->set('vbshowCategoriesFront', $s[0]['setting']);
					return (intval($s[0]['setting']) == 1 ? true : false);
				} else {
					return false;
				}
			}
		}
	}
	
	public static function showChildrenFront($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='showchildren';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return (intval($s[0]['setting']) == 1 ? true : false);
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbshowChildrenFront', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='showchildren';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('vbshowChildrenFront', $s[0]['setting']);
					return (intval($s[0]['setting']) == 1 ? true : false);
				} else {
					return false;
				}
			}
		}
	}

	public static function allowBooking() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='allowbooking';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			return false;
		}
	}

	public static function getDisabledBookingMsg($vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='disabledbookingmsg';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if (!is_object($vbo_tn)) {
			$vbo_tn = self::getTranslator();
		}
		$vbo_tn->translateContents($s, '#__vikbooking_texts');
		return $s[0]['setting'];
	}

	public static function getDateFormat($skipsession = false) {
		$dbo = JFactory::getDBO();
		if ($skipsession) {
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='dateformat';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbgetDateFormat', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='dateformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbgetDateFormat', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getDateSeparator($skipsession = false) {
		$dbo = JFactory::getDBO();
		if ($skipsession) {
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='datesep';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return empty($s[0]['setting']) ? "/" : $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbgetDateSep', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='datesep';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbgetDateSep', $s[0]['setting']);
				return empty($s[0]['setting']) ? "/" : $s[0]['setting'];
			}
		}
	}

	public static function getHoursMoreRb($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='hoursmorebookingback';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getHoursMoreRb', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='hoursmorebookingback';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('getHoursMoreRb', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getHoursRoomAvail() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='hoursmoreroomavail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getFrontTitle($vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (!is_object($vbo_tn)) {
			$vbo_tn = self::getTranslator();
		}
		$vbo_tn->translateContents($ft, '#__vikbooking_texts');
		return $ft[0]['setting'];
	}

	public static function getFrontTitleTag() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getFrontTitleTagClass() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getCurrencyName() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getCurrencySymb($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='currencysymb';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			return $ft[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbgetCurrencySymb', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='currencysymb';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				$session->set('vbgetCurrencySymb', $ft[0]['setting']);
				return $ft[0]['setting'];
			}
		}
	}
	
	public static function getNumberFormatData($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numberformat';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			return $ft[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getNumberFormatData', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numberformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				$session->set('getNumberFormatData', $ft[0]['setting']);
				return $ft[0]['setting'];
			}
		}
	}
	
	public static function numberFormat($num, $skipsession = false) {
		$formatvals = self::getNumberFormatData($skipsession);
		$formatparts = explode(':', $formatvals);
		return number_format((float)$num, (int)$formatparts[0], $formatparts[1], $formatparts[2]);
	}

	public static function getCurrencyCodePp() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getIntroMain($vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='intromain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (!is_object($vbo_tn)) {
			$vbo_tn = self::getTranslator();
		}
		$vbo_tn->translateContents($ft, '#__vikbooking_texts');
		return $ft[0]['setting'];
	}

	public static function getClosingMain($vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='closingmain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (!is_object($vbo_tn)) {
			$vbo_tn = self::getTranslator();
		}
		$vbo_tn->translateContents($ft, '#__vikbooking_texts');
		return $ft[0]['setting'];
	}

	public static function getFullFrontTitle($vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (!is_object($vbo_tn)) {
			$vbo_tn = self::getTranslator();
		}
		$vbo_tn->translateContents($ft, '#__vikbooking_texts');
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$fttag = $dbo->loadAssocList();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		$fttagclass = $dbo->loadAssocList();
		if (empty($ft[0]['setting'])) {
			return "";
		} else {
			if (empty($fttag[0]['setting'])) {
				return $ft[0]['setting'] . "<br/>\n";
			} else {
				$tag = str_replace("<", "", $fttag[0]['setting']);
				$tag = str_replace(">", "", $tag);
				$tag = str_replace("/", "", $tag);
				$tag = trim($tag);
				return "<" . $tag . "" . (!empty($fttagclass) ? " class=\"" . $fttagclass[0]['setting'] . "\"" : "") . ">" . $ft[0]['setting'] . "</" . $tag . ">";
			}
		}
	}

	public static function dateIsValid($date) {
		$df = self::getDateFormat();
		$datesep = self::getDateSeparator();
		if (strlen($date) != 10) {
			return false;
		}
		$cur_dsep = "/";
		if ($datesep != $cur_dsep && strpos($date, $datesep) !== false) {
			$cur_dsep = $datesep;
		}
		$x = explode($cur_dsep, $date);
		if ($df == "%d/%m/%Y") {
			if (strlen($x[0]) != 2 || $x[0] > 31 || strlen($x[1]) != 2 || $x[1] > 12 || strlen($x[2]) != 4) {
				return false;
			}
		} elseif ($df == "%m/%d/%Y") {
			if (strlen($x[1]) != 2 || $x[1] > 31 || strlen($x[0]) != 2 || $x[0] > 12 || strlen($x[2]) != 4) {
				return false;
			}
		} else {
			if (strlen($x[2]) != 2 || $x[2] > 31 || strlen($x[1]) != 2 || $x[1] > 12 || strlen($x[0]) != 4) {
				return false;
			}
		}
		return true;
	}

	public static function sayDateFormat() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if ($s[0]['setting'] == "%d/%m/%Y") {
			return JText::_('VBCONFIGONETWELVE');
		} elseif ($s[0]['setting'] == "%m/%d/%Y") {
			return JText::_('VBCONFIGONEMDY');
		} else {
			return JText::_('VBCONFIGONETENTHREE');
		}
	}

	/**
	 * Calculates the Unix timestamp from the given date and
	 * time. Avoids DST issues thanks to mktime. With prior releases,
	 * DST issues may occur due to the sum of seconds.
	 * 
	 * @param 	string 	$date 	the date string formatted with the current settings
	 * @param 	int 	$h 		hours from 0 to 23 for check-in/check-out
	 * @param 	int 	$m 		minutes from 0 to 59 for check-in/check-out
	 * @param 	int 	$s 		seconds from 0 to 59 for check-in/check-out
	 * 
	 * @return 	int 	the Unix timestamp of the date
	 * 
	 * @since 	1.10 - Revision September 27th 2018
	 */
	public static function getDateTimestamp($date, $h, $m, $s = 0) {
		$df = self::getDateFormat();
		$datesep = self::getDateSeparator();
		$cur_dsep = "/";
		if ($datesep != $cur_dsep && strpos($date, $datesep) !== false) {
			$cur_dsep = $datesep;
		}
		$x = explode($cur_dsep, $date);
		if (!(count($x) > 2)) {
			return 0;
		}
		if ($df == "%d/%m/%Y") {
			$month = (int)$x[1];
			$mday = (int)$x[0];
			$year = (int)$x[2];
		} elseif ($df == "%m/%d/%Y") {
			$month = (int)$x[0];
			$mday = (int)$x[1];
			$year = (int)$x[2];
		} else {
			$month = (int)$x[1];
			$mday = (int)$x[2];
			$year = (int)$x[0];
		}
		$h = empty($h) ? 0 : (int)$h;
		$m = empty($m) ? 0 : (int)$m;
		$s = $s > 0 && $s <= 59 ? $s : 0;
		return mktime($h, $m, $s, $month, $mday, $year);
	}

	public static function ivaInclusa($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbivaInclusa', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='ivainclusa';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbivaInclusa', $s[0]['setting']);
				return (intval($s[0]['setting']) == 1 ? true : false);
			}
		}
	}
	
	public static function showTaxOnSummaryOnly($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='taxsummary';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbshowTaxOnSummaryOnly', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='taxsummary';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbshowTaxOnSummaryOnly', $s[0]['setting']);
				return (intval($s[0]['setting']) == 1 ? true : false);
			}
		}
	}

	public static function tokenForm() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='tokenform';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function getPaypalAcc() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getAccPerCent() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='payaccpercent';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}
	
	public static function getTypeDeposit($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='typedeposit';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getTypeDeposit', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='typedeposit';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('getTypeDeposit', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function multiplePayments() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='multipay';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function getAdminMail() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		}
		return '';
	}

	public static function getSenderMail () {
		$dbo = JFactory::getDBO();
		$q="SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='senderemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return empty($s[0]['setting']) ? self::getAdminMail() : $s[0]['setting'];
	}

	public static function getPaymentName($vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if (!is_object($vbo_tn)) {
			$vbo_tn = self::getTranslator();
		}
		$vbo_tn->translateContents($s, '#__vikbooking_texts');
		return $s[0]['setting'];
	}

	public static function getTermsConditions($vbo_tn = null) {
		//VBO 1.10
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='termsconds';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadAssocList();
			if (!is_object($vbo_tn)) {
				$vbo_tn = self::getTranslator();
			}
			$vbo_tn->translateContents($s, '#__vikbooking_texts');
		} else {
			//the record has never been saved. Compose it with the default lang definition
			$timeopst = self::getTimeOpenStore(true);
			if (is_array($timeopst)) {
				$openat = self::getHoursMinutes($timeopst[0]);
				$closeat = self::getHoursMinutes($timeopst[1]);
			} else {
				$openat = array(12, 0);
				$closeat = array(10, 0);
			}
			$checkin_str = ($openat[0] < 10 ? '0'.$openat[0] : $openat[0]).':'.($openat[1] < 10 ? '0'.$openat[1] : $openat[1]);
			$checkout_str = ($closeat[0] < 10 ? '0'.$closeat[0] : $closeat[0]).':'.($closeat[1] < 10 ? '0'.$closeat[1] : $closeat[1]);
			$s = array(0 => array('setting' => nl2br(JText::sprintf('VBOTERMSCONDSDEFTEXT', $checkin_str, $checkout_str))));
		}
		
		return $s[0]['setting'];
	}

	public static function getMinutesLock($conv = false) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='minuteslock';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if ($conv) {
			$op = $s[0]['setting'] * 60;
			return (time() + $op);
		} else {
			return $s[0]['setting'];
		}
	}

	public static function roomNotLocked($idroom, $units, $first, $second) {
		$dbo = JFactory::getDBO();
		$actnow = time();
		$booked = array ();
		$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `until`<" . $dbo->quote($actnow) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		//vikbooking 1.1
		$secdiff = $second - $first;
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
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		$groupdays = self::getGroupDays($first, $second, $daysdiff);
		$check = "SELECT `id`,`checkin`,`realback` FROM `#__vikbooking_tmplock` WHERE `idroom`=" . $dbo->quote($idroom) . " AND `until`>=" . $dbo->quote($actnow) . ";";
		$dbo->setQuery($check);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$busy = $dbo->loadAssocList();
			foreach ($groupdays as $gday) {
				$bfound = 0;
				foreach ($busy as $bu) {
					if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
						$bfound++;
					}
				}
				if ($bfound >= $units) {
					return false;
				}
			}
		}
		//
		return true;
	}
	
	public static function getGroupDays($first, $second, $daysdiff) {
		$ret = array();
		$ret[] = $first;
		if ($daysdiff > 1) {
			$start = getdate($first);
			$end = getdate($second);
			$endcheck = mktime(0, 0, 0, $end['mon'], $end['mday'], $end['year']);
			for($i = 1; $i < $daysdiff; $i++) {
				$checkday = $start['mday'] + $i;
				$dayts = mktime(0, 0, 0, $start['mon'], $checkday, $start['year']);
				if ($dayts != $endcheck) {
					$ret[] = $dayts;
				}
			}
		}
		$ret[] = $second;
		return $ret;
	}

	/**
	 * Counts the hours of difference between the current
	 * server time and the selected check-in date and time.
	 *
	 * @param 	int 	$checkin_ts
	 * @param 	[int] 	$now_ts
	 *
	 * @return 	int
	 */
	public static function countHoursToArrival($checkin_ts, $now_ts = '') {
		$hoursdiff = 0;

		if (empty($now_ts)) {
			$now_ts = time();
		}

		if ($now_ts >= $checkin_ts) {
			return $hoursdiff;
		}

		$hoursdiff = floor(($checkin_ts - $now_ts) / 3600);

		return $hoursdiff;
	}
	
	public static function loadBusyRecords($roomids, $ts = 0) {
		$actnow = empty($ts) ? time() : $ts;
		$busy = array();
		if (!is_array($roomids) || !(count($roomids) > 0)) {
			return $busy;
		}
		$dbo = JFactory::getDBO();
		$check = "SELECT `id`,`idroom`,`checkin`,`checkout` FROM `#__vikbooking_busy` WHERE `idroom` IN (".implode(', ', $roomids).") AND `checkout` > ".$actnow.";";
		$dbo->setQuery($check);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allbusy = $dbo->loadAssocList();
			foreach ($allbusy as $kb => $br) {
				$busy[$br['idroom']][$kb] = $br;
			}
		}
		return $busy;
	}

	public static function loadBookingBusyIds($idorder) {
		$busy = array();
		if (empty($idorder)) {
			return $busy;
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$idorder.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allbusy = $dbo->loadAssocList();
			foreach ($allbusy as $b) {
				array_push($busy, $b['idbusy']);
			}
		}
		return $busy;
	}

	public static function loadLockedRecords($roomids, $ts = 0) {
		$actnow = empty($ts) ? time() : $ts;
		$locked = array();
		$dbo = JFactory::getDBO();
		$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `until`<" . $actnow . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!is_array($roomids) || !(count($roomids) > 0)) {
			return $locked;
		}
		$check = "SELECT `id`,`idroom`,`checkin`,`realback` FROM `#__vikbooking_tmplock` WHERE `idroom` IN (".implode(', ', $roomids).") AND `until` > ".$actnow.";";
		$dbo->setQuery($check);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all_locked = $dbo->loadAssocList();
			foreach ($all_locked as $kb => $br) {
				$locked[$br['idroom']][$kb] = $br;
			}
		}
		return $locked;
	}

	public static function getRoomBookingsFromBusyIds($idroom, $arr_bids) {
		$bookings = array();
		if (empty($idroom) || !is_array($arr_bids) || !(count($arr_bids) > 0)) {
			return $bookings;
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `ob`.`idorder`,`ob`.`idbusy` FROM `#__vikbooking_ordersbusy` AS `ob` WHERE `ob`.`idbusy` IN (".implode(',', $arr_bids).") GROUP BY `ob`.`idorder`,`ob`.`idbusy`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all_booking_ids = $dbo->loadAssocList();
			$oids = array();
			foreach ($all_booking_ids as $bid) {
				$oids[] = $bid['idorder'];
			}
			$q = "SELECT `or`.`idorder`,CONCAT_WS(' ',`or`.`t_first_name`,`or`.`t_last_name`) AS `nominative`,`or`.`roomindex`,`o`.`status`,`o`.`days`,`o`.`checkout`,`o`.`custdata`,`o`.`country`,`o`.`closure`,`o`.`checked` ".
				"FROM `#__vikbooking_ordersrooms` AS `or` ".
				"LEFT JOIN `#__vikbooking_orders` `o` ON `o`.`id`=`or`.`idorder` ".
				"WHERE `or`.`idorder` IN (".implode(',', $oids).") AND `or`.`idroom`=".(int)$idroom." AND `o`.`status`='confirmed' ".
				"ORDER BY `o`.`checkout` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$bookings = $dbo->loadAssocList();
			}
		}
		return $bookings;
	}
	
	public static function roomBookable($idroom, $units, $first, $second, $skip_busy_ids = array()) {
		$dbo = JFactory::getDBO();
		$secdiff = $second - $first;
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
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		$groupdays = self::getGroupDays($first, $second, $daysdiff);
		$check = "SELECT `id`,`checkin`,`realback` FROM `#__vikbooking_busy` WHERE `idroom`=" . $dbo->quote($idroom) . ";";
		$dbo->setQuery($check);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$busy = $dbo->loadAssocList();
			foreach ($groupdays as $gday) {
				$bfound = 0;
				foreach ($busy as $bu) {
					if (in_array($bu['id'], $skip_busy_ids)) {
						//VBO 1.10 - Booking modification
						continue;
					}
					if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
						$bfound++;
					}
				}
				if ($bfound >= $units) {
					return false;
				}
			}
		}
		
		return true;
	}

	public static function payTotal() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='paytotal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function getDepositIfDays() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='depifdaysadv';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']);
	}

	public static function depositAllowedDaysAdv($checkints) {
		$days_adv = self::getDepositIfDays();
		if (!($days_adv > 0) || !($checkints > 0)) {
			return true;
		}
		$now_info = getdate();
		$maxts = mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] + $days_adv), $now_info['year']);
		return $maxts > $checkints ? false : true;
	}

	public static function depositCustomerChoice() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='depcustchoice';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function getDepositOverrides($getjson = false) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='depoverrides';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			return $getjson ? $s : json_decode($s, true);
		}
		//count of this array will be at least 1 to store the "more" property
		$def_arr = array('more' => '');
		$def_val = json_encode($def_arr);
		$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('depoverrides', ".$dbo->quote($def_val).");";
		$dbo->setQuery($q);
		$dbo->execute();
		return $getjson ? $def_val : $def_arr;
	}

	public static function calcDepositOverride($amount_deposit, $nights) {
		$overrides = self::getDepositOverrides();
		$nights = intval($nights);
		$andmore = intval($overrides['more']);
		if (!(count($overrides) > 1)) {
			//no overrides
			return $amount_deposit;
		}
		foreach ($overrides as $k => $v) {
			if ((int)$k == $nights && strlen($v) > 0) {
				//exact override found
				return (float)$v;
			}
		}
		if ($andmore > 0 && $andmore <= $nights) {
			foreach ($overrides as $k => $v) {
				if ((int)$k == $andmore && strlen($v) > 0) {
					//"and more" nights found
					return (float)$v;
				}
			}
		}
		//nothing was found
		return $amount_deposit;
	}

	public static function noDepositForNonRefund() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='nodepnonrefund';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			return ((int)$s == 1);
		}
		$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('nodepnonrefund', '0');";
		$dbo->setQuery($q);
		$dbo->execute();
		//default to false
		return false;
	}

	/**
	 * This method returns the room-rate array with the lowest price
	 * that matches the preferred rate plan parameter (if available).
	 * The array $website_rates could not be an array, or it could be
	 * an array with the error string (response from the TACVBO Class).
	 * The method has been introduced in VBO 1.10 and it's mainly used
	 * by the module mod_vikbooking_channelrates and its ajax requests.
	 *
	 * @param 	array  		$website_rates 		the array of the website rates returned by the method fetchWebsiteRates()
	 * @param 	int  		$def_rplan 			the id of the default type of price to take for display. If empty, take the lowest rate
	 *
	 * @return 	array
	 */
	public static function getBestRoomRate($website_rates, $def_rplan) {
		if (!is_array($website_rates) || !(count($website_rates) > 0) || (is_array($website_rates) && isset($website_rates['e4j.error']))) {
			return array();
		}
		$best_room_rate = array();
		foreach ($website_rates as $rid => $tars) {
			foreach ($tars as $tar) {
				if (empty($def_rplan) || (int)$tar['idprice'] == $def_rplan) {
					//the array $website_rates is already sorted by price ASC, so we take the first useful array
					$best_room_rate = $tar;
					break 2;
				}
			}
		}
		if (!(count($best_room_rate) > 0)) {
			//the default rate plan is not available if we enter this statement, so we take the first and lowest rate
			foreach ($website_rates as $rid => $tars) {
				foreach ($tars as $tar) {
					$best_room_rate = $tar;
					break 2;
				}
			}
		}

		return $best_room_rate;
	}

	/**
	 * This method returns an array with the details
	 * of all channels in VCM that supports AV requests,
	 * and that have at least one room type mapped.
	 * The method invokes the Logos Class to return details
	 * about the name and logo URL of the channel.
	 * The method has been introduced in VBO 1.10 and it's mainly used
	 * by the module mod_vikbooking_channelrates and its ajax requests.
	 *
	 * @param 	array 	$channels 	an array of channel IDs to be mapped on the VCM relations
	 *
	 * @return 	array
	 */
	public static function getChannelsMap($channels) {
		if (!is_array($channels) || !(count($channels))) {
			return array();
		}
		$vcm_logos = self::getVcmChannelsLogo('', true);
		if (!is_object($vcm_logos)) {
			return array();
		}
		$channels_ids = array();
		foreach ($channels as $chid) {
			$ichid = (int)$chid;
			if ($ichid < 1) {
				continue;
			}
			array_push($channels_ids, $ichid);
		}
		if (!(count($channels_ids))) {
			return array();
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `idchannel`, `channel` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel` IN (".implode(', ', $channels_ids).") GROUP BY `idchannel`,`channel` ORDER BY `channel` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return array();
		}
		$channels_names = $dbo->loadAssocList();
		$channels_map = array();
		foreach ($channels_names as $ch) {
			$ota_logo_url = $vcm_logos->setProvenience($ch['channel'])->getLogoURL();
			$ota_logo_url = $ota_logo_url === false ? '' : $ota_logo_url;
			$chdata = array(
				'id' => $ch['idchannel'],
				'name' => ucwords($ch['channel']),
				'logo' => $ota_logo_url
			);
			array_push($channels_map, $chdata);
		}
		return $channels_map;
	}

	/**
	 * This method returns a string to calculate the rates
	 * for the OTAs. Data is taken from the Bulk Rates Cache
	 * of Vik Channel Manager. The string returned contains
	 * the charge/discount operator at the position 0 (+ or -),
	 * and the percentage char (%) at the last position (if percent).
	 * Between the first and last position there is the float value.
	 * The method has been introduced in VBO 1.10 and it's mainly used
	 * by the module mod_vikbooking_channelrates and its ajax requests.
	 *
	 * @param 	array  		$best_room_rate 	array containing a specific tariff returned by getBestRoomRate()
	 *
	 * @return 	string
	 */
	public static function getOtasRatesVal($best_room_rate) {
		$otas_rates_val  = '';
		if (!(count($best_room_rate)) || !file_exists(VCM_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikchannelmanager.php')) {
			return $otas_rates_val;
		}
		if (!class_exists('VikChannelManager')) {
			require_once(VCM_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikchannelmanager.php');
		}
		$bulk_rates_cache = VikChannelManager::getBulkRatesCache();
		if (count($bulk_rates_cache) && isset($best_room_rate['idprice'])) {
			if (isset($bulk_rates_cache[$best_room_rate['idroom']]) && isset($bulk_rates_cache[$best_room_rate['idroom']][$best_room_rate['idprice']])) {
				//the Bulk Rates Cache contains data for this room type and rate plan
				if ((int)$bulk_rates_cache[$best_room_rate['idroom']][$best_room_rate['idprice']]['rmod'] > 0) {
					//rates were modified for the OTAs, check how
					$rmodop = (int)$bulk_rates_cache[$best_room_rate['idroom']][$best_room_rate['idprice']]['rmodop'] > 0 ? '+' : '-';
					$rmodpcent = (int)$bulk_rates_cache[$best_room_rate['idroom']][$best_room_rate['idprice']]['rmodval'] > 0 ? '%' : '';
					$otas_rates_val = $rmodop.(float)$bulk_rates_cache[$best_room_rate['idroom']][$best_room_rate['idprice']]['rmodamount'].$rmodpcent;
				}
			}
		}

		return $otas_rates_val;
	}

	/**
	 * This method checks if some non-refundable rates were selected
	 * (`free_cancellation`=0), the only argument is an array of tariffs.
	 * The property 'idprice' must be defined on each sub-array.
	 * 
	 * @param 	$tars 		array
	 * 
	 * @return 	boolean
	 **/
	public static function findNonRefundableRates($tars) {
		$id_prices = array();
		foreach ($tars as $tar) {
			if (isset($tar['idprice'])) {
				if (!in_array($tar['idprice'], $id_prices)) {
					array_push($id_prices, (int)$tar['idprice']);
				}
				continue;
			}
			foreach ($tar as $t) {
				if (isset($t['idprice'])) {
					if (!in_array($t['idprice'], $id_prices)) {
						array_push($id_prices, (int)$t['idprice']);
					}
				}
			}
		}
		if (!(count($id_prices) > 0)) {
			//no id-prices found (probably a package)
			return false;
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_prices` WHERE `id` IN (".implode(', ', $id_prices).") AND `free_cancellation`=0;";
		$dbo->setQuery($q);
		$dbo->execute();
		return (bool)($dbo->getNumRows() > 0);
	}

	/**
	 * This method checks if the deposit is allowed depending on
	 * the selected rate plans (idprice) for the rooms reserved.
	 * If the configuration setting is enabled, and if some
	 * non-refundable rates were selected (`free_cancellation`=0),
	 * the method will return false, because the deposit is not allowed.
	 * The only argument is an array of tariffs. The property 'idprice'
	 * must be defined on each sub-array (multi-dimension supported)
	 * throgh the method findNonRefundableRates();
	 * 
	 * @param 	$tars 		array
	 * 
	 * @return 	boolean
	 **/
	public static function allowDepositFromRates($tars) {
		if (!self::noDepositForNonRefund()) {
			//deposit can be paid also if non-refundable rates
			return true;
		}
		return !self::findNonRefundableRates($tars);
	}

	public static function showSearchSuggestions() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='searchsuggestions';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			return (int)$dbo->loadResult();
		}
		$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('searchsuggestions', '1');";
		$dbo->setQuery($q);
		$dbo->execute();
		return 1;
	}
	
	public static function getCouponInfo($code) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_coupons` WHERE `code`=".$dbo->quote($code).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$c = $dbo->loadAssocList();
			return $c[0];
		} else {
			return "";
		}
	}
	
	public static function getRoomInfo($idroom) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name`,`img`,`idcat`,`idcarat`,`info`,`smalldesc` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$idroom.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadAssocList();
			return $s[0];
		}
		return array();
	}
	
	public static function loadOrdersRoomsData ($idorder) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `or`.*,`r`.`name` AS `room_name`,`r`.`params` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`or`.`idroom` WHERE `or`.`idorder`='" . $idorder . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		return $s;
	}
	
	public static function sayCategory($ids, $vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $ids);
		$say = "";
		foreach ($split as $k => $s) {
			if (strlen($s)) {
				$q = "SELECT `id`,`name` FROM `#__vikbooking_categories` WHERE `id`='" . $s . "';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() < 1) {
					continue;
				}
				$nam = $dbo->loadAssocList();
				if (is_object($vbo_tn)) {
					$vbo_tn->translateContents($nam, '#__vikbooking_categories');
				}
				$say .= $nam[0]['name'];
				$say .= (strlen($split[($k +1)]) && end($split) != $s ? ", " : "");
			}
		}
		return $say;
	}

	public static function getRoomCaratOriz($idc, $vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $idc);
		$carat = "";
		$dbo = JFactory::getDBO();
		$arr = array ();
		$where = array();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[] = $s;
			}
		}
		if (count($where) > 0) {
			$q = "SELECT * FROM `#__vikbooking_characteristics` WHERE `id` IN (".implode(",", $where).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$arr = $dbo->loadAssocList();
				if (is_object($vbo_tn)) {
					$vbo_tn->translateContents($arr, '#__vikbooking_characteristics');
				}
			}
		}
		if (count($arr) > 0) {
			$carat .= "<ul class=\"vbulcarats\">\n";
			foreach ($arr as $a) {
				if (!empty($a['textimg'])) {
					//tooltip icon text is not empty
					if (!empty($a['icon'])) {
						//an icon has been uploaded: display the image
						$carat .= "<li><span class=\"vbo-expl\" data-vbo-expl=\"".$a['textimg']."\"><img src=\"".VBO_SITE_URI."resources/uploads/".$a['icon']."\" alt=\"" . $a['name'] . "\" /></span></li>\n";
					} else {
						if (strpos($a['textimg'], '</i>') !== false) {
							//the tooltip icon text is a font-icon, we can use the name as tooltip
							$carat .= "<li><span class=\"vbo-expl\" data-vbo-expl=\"".$a['name']."\">".$a['textimg']."</span></li>\n";
						} else {
							//display just the text
							$carat .= "<li>".$a['textimg']."</li>\n";
						}
					}
				} else {
					$carat .= (!empty($a['icon']) ? "<li><img src=\"".VBO_SITE_URI."resources/uploads/" . $a['icon'] . "\" alt=\"" . $a['name'] . "\" title=\"" . $a['name'] . "\"/></li>\n" : "<li>".$a['name']."</li>\n");
				}
			}
			$carat .= "</ul>\n";
		}
		return $carat;
	}

	public static function getRoomOptionals($idopts, $vbo_tn = null) {
		$split = explode(";", $idopts);
		$dbo = JFactory::getDBO();
		$arr = array ();
		$fetch = array();
		foreach ($split as $s) {
			if (!empty($s)) {
				$fetch[] = $s;
			}
		}
		if (count($fetch) > 0) {
			$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id` IN (".implode(", ", $fetch).") ORDER BY `#__vikbooking_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$arr = $dbo->loadAssocList();
				if (is_object($vbo_tn)) {
					$vbo_tn->translateContents($arr, '#__vikbooking_optionals');
				}
				return $arr;
			}
		}
		return "";
	}

	public static function getSingleOption($idopt, $vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$opt = array();
		if (!empty($idopt)) {
			$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=" . (int)$idopt . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$opt = $dbo->loadAssoc();
				if (is_object($vbo_tn)) {
					$vbo_tn->translateContents($opt, '#__vikbooking_optionals');
				}
			}
		}
		return $opt;
	}
	
	public static function getMandatoryTaxesFees($id_rooms, $num_adults, $num_nights) {
		$dbo = JFactory::getDBO();
		$taxes = 0;
		$fees = 0;
		$options_data = array();
		$id_options = array();
		$q = "SELECT `id`,`idopt` FROM `#__vikbooking_rooms` WHERE `id` IN (".implode(", ", $id_rooms).");";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$assocs = $dbo->loadAssocList();
			foreach ($assocs as $opts) {
				if (!empty($opts['idopt'])) {
					$r_ido = explode(';', rtrim($opts['idopt']));
					foreach ($r_ido as $ido) {
						if (!empty($ido) && !in_array($ido, $id_options)) {
							$id_options[] = $ido;
						}
					}
				}
			}
		}
		if (count($id_options) > 0) {
			$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id` IN (".implode(", ", $id_options).") AND `forcesel`=1 AND `ifchildren`=0 AND (`is_citytax`=1 OR `is_fee`=1);";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$alltaxesfees = $dbo->loadAssocList();
				foreach ($alltaxesfees as $tf) {
					$realcost = (intval($tf['perday']) == 1 ? ($tf['cost'] * $num_nights) : $tf['cost']);
					if (!empty($tf['maxprice']) && $tf['maxprice'] > 0 && $realcost > $tf['maxprice']) {
						$realcost = $tf['maxprice'];
					}
					$realcost = $tf['perperson'] == 1 ? ($realcost * $num_adults) : $realcost;
					$realcost = self::sayOptionalsPlusIva($realcost, $tf['idiva']);
					if ($tf['is_citytax'] == 1) {
						$taxes += $realcost;
					} elseif ($tf['is_fee'] == 1) {
						$fees += $realcost;
					}
					$optsett = explode('-', $tf['forceval']);
					$options_data[] = $tf['id'].':'.$optsett[0];
				}
			}
		}
		return array('city_taxes' => $taxes, 'fees' => $fees, 'options' => $options_data);
	}
	
	public static function loadOptionAgeIntervals($optionals) {
		$ageintervals = '';
		foreach ($optionals as $kopt => $opt) {
			if (!empty($opt['ageintervals'])) {
				$intervals = explode(';;', $opt['ageintervals']);
				foreach($intervals as $intv) {
					if (empty($intv)) continue;
					$parts = explode('_', $intv);
					if (count($parts) >= 3) {
						$ageintervals = $optionals[$kopt];
						break 2;
					}
				}
			}
		}
		if (is_array($ageintervals)) {
			foreach ($optionals as $kopt => $opt) {
				if (!empty($opt['ageintervals']) || $opt['id'] == $ageintervals['id']) {
					unset($optionals[$kopt]);
				}
			}
			if (count($optionals) <= 0) {
				$optionals = '';
			}
		}
		return array($optionals, $ageintervals);
	}
	
	public static function getOptionIntervalsCosts($intvstr) {
		$optcosts = array();
		$intervals = explode(';;', $intvstr);
		foreach ($intervals as $kintv => $intv) {
			if (empty($intv)) continue;
			$parts = explode('_', $intv);
			if (count($parts) >= 3) {
				$optcosts[$kintv] = (float)$parts[2];
			}
		}
		return $optcosts;
	}
	
	public static function getOptionIntervalsAges($intvstr) {
		$optages = array();
		$intervals = explode(';;', $intvstr);
		foreach ($intervals as $kintv => $intv) {
			if (empty($intv)) continue;
			$parts = explode('_', $intv);
			if (count($parts) >= 3) {
				$optages[$kintv] = $parts[0].' - '.$parts[1];
			}
		}
		return $optages;
	}

	public static function getOptionIntervalsPercentage($intvstr) {
		/* returns an associative array to tell whether an interval has a percentage cost (VBO 1.8) */
		$optcostspcent = array();
		$intervals = explode(';;', $intvstr);
		foreach ($intervals as $kintv => $intv) {
			if (empty($intv)) continue;
			$parts = explode('_', $intv);
			if (count($parts) >= 3) {
				//fixed amount
				$setval = 0;
				if (array_key_exists(3, $parts) && strpos($parts[3], '%b') !== false) {
					//percentage value of the room base cost (VBO 1.10)
					$setval = 2;
				} elseif (array_key_exists(3, $parts) && strpos($parts[3], '%') !== false) {
					//percentage value of the adults tariff
					$setval = 1;
				}
				$optcostspcent[$kintv] = $setval;
			}
		}
		return $optcostspcent;
	}

	public static function dayValidTs($days, $first, $second) {
		$secdiff = $second - $first;
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
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		return ($daysdiff == $days ? true : false);
	}

	public static function sayCostPlusIva($cost, $idprice) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('vbivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('vbivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			$q = "SELECT `p`.`idiva`,`i`.`aliq` FROM `#__vikbooking_prices` AS `p` LEFT JOIN `#__vikbooking_iva` `i` ON `i`.`id`=`p`.`idiva` WHERE `p`.`id`='" . (int)$idprice . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$paliq = $dbo->loadAssocList();
				if (!empty($paliq[0]['aliq'])) {
					$subt = 100 + $paliq[0]['aliq'];
					$op = ($cost * $subt / 100);
					//VBO 1.10 - apply rounding to avoid issues with multiple tax rates when tax excluded
					$formatvals = self::getNumberFormatData();
					$formatparts = explode(':', $formatvals);
					$rounded_op = round($op, (int)$formatparts[0]);
					if ($rounded_op > $op) {
						return $rounded_op;
					}
					//
					return $op;
				}
			}
		}
		return $cost;
	}

	public static function sayCostMinusIva($cost, $idprice) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('vbivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('vbivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1) {
			$q = "SELECT `p`.`idiva`,`i`.`aliq` FROM `#__vikbooking_prices` AS `p` LEFT JOIN `#__vikbooking_iva` `i` ON `i`.`id`=`p`.`idiva` WHERE `p`.`id`='" . (int)$idprice . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$paliq = $dbo->loadAssocList();
				if (!empty($paliq[0]['aliq'])) {
					$subt = 100 + $paliq[0]['aliq'];
					$op = ($cost * 100 / $subt);
					//VBO 1.10 - apply rounding to avoid issues with multiple tax rates when tax included
					$formatvals = self::getNumberFormatData();
					$formatparts = explode(':', $formatvals);
					$rounded_op = round($op, (int)$formatparts[0]);
					if ($rounded_op < $op) {
						return $rounded_op;
					}
					//
					return $op;
				}
			}
		}
		return $cost;
	}

	public static function sayOptionalsPlusIva($cost, $idiva) {
		//this method can also be used to calculate taxes on the extra costs per room in the bookings
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('vbivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('vbivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			$q = "SELECT `aliq` FROM `#__vikbooking_iva` WHERE `id`='" . (int)$idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * $subt / 100);
				//VBO 1.10 - apply rounding to avoid issues with multiple tax rates when tax excluded
				$formatvals = self::getNumberFormatData();
				$formatparts = explode(':', $formatvals);
				$rounded_op = round($op, (int)$formatparts[0]);
				if ($rounded_op > $op) {
					return $rounded_op;
				}
				//
				return $op;
			}
		}
		return $cost;
	}

	public static function sayOptionalsMinusIva($cost, $idiva) {
		//this method can also be used to calculate taxes on the extra costs per room in the bookings
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('vbivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('vbivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1) {
			$q = "SELECT `aliq` FROM `#__vikbooking_iva` WHERE `id`='" . (int)$idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * 100 / $subt);
				//VBO 1.10 - apply rounding to avoid issues with multiple tax rates when tax included
				$formatvals = self::getNumberFormatData();
				$formatparts = explode(':', $formatvals);
				$rounded_op = round($op, (int)$formatparts[0]);
				if ($rounded_op < $op) {
					return $rounded_op;
				}
				//
				return $op;
			}
		}
		return $cost;
	}

	public static function sayPackagePlusIva($cost, $idiva) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('vbivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('vbivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			$q = "SELECT `aliq` FROM `#__vikbooking_iva` WHERE `id`='" . (int)$idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * $subt / 100);
				//VBO 1.10 - apply rounding to avoid issues with multiple tax rates when tax excluded
				$formatvals = self::getNumberFormatData();
				$formatparts = explode(':', $formatvals);
				$rounded_op = round($op, (int)$formatparts[0]);
				if ($rounded_op > $op) {
					return $rounded_op;
				}
				//
				return $op;
			}
		}
		return $cost;
	}

	public static function sayPackageMinusIva($cost, $idiva, $force_invoice_excltax = false) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('vbivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('vbivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1 || ($force_invoice_excltax === true && intval($ivainclusa) < 1)) {
			$q = "SELECT `aliq` FROM `#__vikbooking_iva` WHERE `id`='" . (int)$idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * 100 / $subt);
				//VBO 1.10 - apply rounding to avoid issues with multiple tax rates when tax included
				$formatvals = self::getNumberFormatData();
				$formatparts = explode(':', $formatvals);
				$rounded_op = round($op, (int)$formatparts[0]);
				if ($rounded_op < $op) {
					return $rounded_op;
				}
				//
				return $op;
			}
		}
		return $cost;
	}
	
	public static function getSecretLink() {
		$sid = mt_rand();
		$dbo = JFactory::getDBO();
		$q = "SELECT `sid` FROM `#__vikbooking_orders`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all = $dbo->loadAssocList();
			$arr = array();
			foreach ($all as $s) {
				$arr[] = $s['sid'];
			}
			if (in_array($sid, $arr)) {
				while (in_array($sid, $arr)) {
					$sid++;
				}
			}
		}
		return $sid;
	}
	
	public static function generateConfirmNumber($oid, $update = true) {
		$confirmnumb = date('ym');
		$confirmnumb .= (string)rand(100, 999);
		$confirmnumb .= (string)rand(10, 99);
		$confirmnumb .= (string)$oid;
		if ($update) {
			$dbo = JFactory::getDBO();
			$q="UPDATE `#__vikbooking_orders` SET `confirmnumber`='".$confirmnumb."' WHERE `id`='".$oid."';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		return $confirmnumb;
	}
	
	public static function buildCustData($arr, $sep) {
		$cdata = "";
		foreach ($arr as $k => $e) {
			if (strlen($e)) {
				$cdata .= (strlen($k) > 0 ? $k . ": " : "") . $e . $sep;
			}
		}
		return $cdata;
	}

	/**
	 * This method parses the Joomla menu object
	 * to see if a menu item of a specific type
	 * is available, to get its ID.
	 * Useful when links must be displayed in pages where
	 * there is no Itemid set (booking details pages).
	 *
	 * @param 	array  		$viewtypes 		list of accepted menu items
	 *
	 * @return 	int
	 */
	public static function findProperItemIdType($viewtypes) {
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		foreach ($menu->getMenu() as $itemid => $item) {
			if ($item->query['option'] == 'com_vikbooking' && in_array($item->query['view'], $viewtypes)) {
				return $itemid;
			}
		}
		return 0;
	}

	public static function sendAdminMail($to, $subject, $ftitle, $ts, $custdata, $rooms, $first, $second, $pricestr, $optstr, $tot, $status, $payname = "", $couponstr = "", $arrpeople = "", $confirmnumber = "") {
		$sendwhen = self::getSendEmailWhen();
		if ($sendwhen > 1 && $status == JText::_('VBINATTESA')) {
			return true;
		}
		$emailparts = explode(';_;', $to);
		$to = $emailparts[0];
		if (!is_array($to) && strpos($to, ',') !== false) {
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
		if (empty($to)) {
			//Prevent Joomla Exceptions that would stop the script execution
			VikError::raiseWarning('', 'The administrator email address is empty. Email message could not be sent.');
			return false;
		}
		$replyto = isset($emailparts[1]) ? $emailparts[1] : '';
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$datesep = self::getDateSeparator();
		$roomsnum = count($rooms);
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VBLIBONE') . " " . date(str_replace("/", $datesep, $df) . ' H:i', $ts) . "\n";
		$msg .= JText::_('VBLIBTWO') . ":\n" . $custdata . "\n";
		$msg .= JText::_('VBLIBTHREE') . ": " . $roomsnum . "\n";
		$msg .= JText::_('VBLIBFOUR') . " " . date(str_replace("/", $datesep, $df) . ' H:i', $first) . "\n";
		$msg .= JText::_('VBLIBFIVE') . " " . date(str_replace("/", $datesep, $df) . ' H:i', $second) . "\n\n";
		foreach($rooms as $num => $r) {
			$msg .= ($roomsnum > 1 ? JText::_('VBMAILROOMNUM')."".$num.": " : "").$r['name'];
			//Rooms Distinctive Features
			$distinctive_features = array();
			$rparams = json_decode($r['params'], true);
			if (array_key_exists('features', $rparams) && count($rparams['features']) > 0 && array_key_exists('roomindex', $r) && !empty($r['roomindex']) && array_key_exists($r['roomindex'], $rparams['features'])) {
				$distinctive_features = $rparams['features'][$r['roomindex']];
			}
			if (count($distinctive_features)) {
				foreach ($distinctive_features as $dfk => $dfv) {
					if (strlen($dfv)) {
						//get the first non-empty distinctive feature of the room
						$msg .= " - ".JText::_($dfk).': '.$dfv;
						break;
					}
				}
			}
			//
			$msg .= "\n";
			$msg .= JText::_('VBMAILADULTS').": ".intval($arrpeople[$num]['adults']) . "\n";
			if ($arrpeople[$num]['children'] > 0) {
				$msg .= JText::_('VBMAILCHILDREN').": ".$arrpeople[$num]['children'] . "\n";
			}
			$msg .= $pricestr[$num] . "\n";
			$allopts = "";
			if (isset($optstr[$num]) && is_array($optstr[$num]) && count($optstr[$num]) > 0) {
				foreach($optstr[$num] as $oo) {
					$expopts = explode("\n", $oo);
					foreach($expopts as $kopt => $optinfo) {
						if (!empty($optinfo)) {
							$splitopt = explode(":", $optinfo);
							$optprice = trim(str_replace($currencyname, "", $splitopt[1]));
							$optinfo = $splitopt[0].': '.self::numberFormat($optprice)." $currencyname";
							$expopts[$kopt] = $optinfo;
						}
					}
					$oo = implode("\n", $expopts);
					$allopts .= $oo;
				}
			}
			$msg .= $allopts . "\n";
		}
		//vikbooking 1.1 coupon
		if (strlen($couponstr) > 0) {
			$expcoupon = explode(";", $couponstr);
			$msg .= JText::_('VBCOUPON')." ".$expcoupon[2].": -" . $expcoupon[1] . " " . $currencyname . "\n\n";
		}
		//
		$msg .= JText::_('VBLIBSIX') . ": " . self::numberFormat($tot) . " " . $currencyname . "\n\n";
		if (!empty($payname)) {
			$msg .= JText::_('VBLIBPAYNAME') . ": " . $payname . "\n\n";
		}
		$msg .= JText::_('VBLIBSEVEN') . ": " . $status;
		
		//Confirmation Number
		if (strlen($confirmnumber) > 0) {
			$msg .= "\n\n".JText::_('VBCONFIRMNUMB') . ": " . $confirmnumber;
		}
		//
		//No deposit, chose to pay full amount (information sent only when the status is Pending)
		$pnodep = VikRequest::getString('nodep', '', 'request');
		if (intval($pnodep) > 0) {
			$msg .= "\n\n".JText::_('VBCUSTCHOICEPAYFULLADMIN');
		}
		//
		
		//$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$mailer = JFactory::getMailer();
		$adsendermail = self::getSenderMail();
		$sender = array($adsendermail, $adsendermail);
		$mailer->setSender($sender);
		$mailer->addRecipient($to);
		$mailer->addReplyTo((!empty($replyto) ? $replyto : $adsendermail));
		$mailer->setSubject($subject);
		$mailer->setBody($msg);
		$mailer->isHTML(false);
		$mailer->Encoding = 'base64';
		$mailer->Send();
		
		return true;
	}
	
	public static function loadEmailTemplate ($booking_info = array()) {
		define('_VIKBOOKINGEXEC', '1');
		ob_start();
		include VBO_SITE_PATH . DS . "helpers" . DS ."email_tmpl.php";
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	public static function parseEmailTemplate ($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $rooms, $tcheckindate, $tdropdate, $tprices, $topts, $ttot, $tlink, $tfootm, $couponstr, $arrpeople, $confirmnumber) {
		$dbo = JFactory::getDBO();
		$vbo_tn = self::getTranslator();
		//get necessary values
		$order_info = array();
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$orderid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order_info = $dbo->loadAssoc();
		}
		$tars_info = array();
		$q = "SELECT `or`.`id`,`or`.`idroom`,`or`.`idtar`,`d`.`idprice` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_dispcost` AS `d` ON `or`.`idtar`=`d`.`id` WHERE `or`.`idorder`=".(int)$orderid." AND `or`.`idtar` IS NOT NULL;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$tars_info = $dbo->loadAssocList();
		}
		//
		$parsed = $tmpl;
		$parsed = str_replace("{logo}", $tlogo, $parsed);
		$parsed = str_replace("{company_name}", $tcname, $parsed);
		$parsed = str_replace("{order_id}", $orderid, $parsed);
		$statusclass = $status == JText::_('VBCOMPLETED') ? "confirmed" : "standby";
		$statusclass = $status == JText::_('VBCANCELLED') ? "cancelled" : $statusclass;
		$parsed = str_replace("{order_status_class}", $statusclass, $parsed);
		$parsed = str_replace("{order_status}", $status, $parsed);
		$parsed = str_replace("{order_date}", $todate, $parsed);
		//PIN Code
		if ($status == JText::_('VBCOMPLETED') && self::customersPinEnabled()) {
			$cpin = self::getCPinIstance();
			$customer_pin = $cpin->getPinCodeByOrderId($orderid);
			if (!empty($customer_pin)) {
				$tcustdata .= '<h3>'.JText::_('VBYOURPIN').': '.$customer_pin.'</h3>';
			}
		}
		//
		$parsed = str_replace("{customer_info}", $tcustdata, $parsed);
		//Confirmation Number
		if (strlen($confirmnumber) > 0) {
			$parsed = str_replace("{confirmnumb}", $confirmnumber, $parsed);
		} else {
			$parsed = preg_replace('#('.preg_quote('{confirmnumb_delimiter}').')(.*)('.preg_quote('{/confirmnumb_delimiter}').')#si', '$1'.' '.'$3', $parsed);
		}
		$parsed = str_replace("{confirmnumb_delimiter}", "", $parsed);
		$parsed = str_replace("{/confirmnumb_delimiter}", "", $parsed);
		//
		$roomsnum = count($rooms);
		$parsed = str_replace("{rooms_count}", $roomsnum, $parsed);
		$roomstr = "";
		//Rooms Distinctive Features
		preg_match_all('/\{roomfeature ([a-zA-Z0-9 ]+)\}/U', $parsed, $matches);
		//
		foreach($rooms as $num => $r) {
			$roomstr .= "<strong>".$r['name']."</strong> ".$arrpeople[$num]['adults']." ".($arrpeople[$num]['adults'] > 1 ? JText::_('VBMAILADULTS') : JText::_('VBMAILADULT')).($arrpeople[$num]['children'] > 0 ? ", ".$arrpeople[$num]['children']." ".($arrpeople[$num]['children'] > 1 ? JText::_('VBMAILCHILDREN') : JText::_('VBMAILCHILD')) : "")."<br/>";
			//Rooms Distinctive Features
			if (is_array($matches[1]) && @count($matches[1]) > 0) {
				$distinctive_features = array();
				$rparams = json_decode($r['params'], true);
				if (array_key_exists('features', $rparams) && count($rparams['features']) > 0 && array_key_exists('roomindex', $r) && !empty($r['roomindex']) && array_key_exists($r['roomindex'], $rparams['features'])) {
					$distinctive_features = $rparams['features'][$r['roomindex']];
				}
				$docheck = (count($distinctive_features) > 0);
				foreach($matches[1] as $reqf) {
					$feature_found = false;
					if ($docheck) {
						foreach ($distinctive_features as $dfk => $dfv) {
							if (stripos($dfk, $reqf) !== false) {
								$feature_found = $dfk;
								if (strlen(trim($dfk)) == strlen(trim($reqf))) {
									break;
								}
							}
						}
					}
					if ($feature_found !== false && strlen($distinctive_features[$feature_found]) > 0) {
						$roomstr .= JText::_($feature_found).': '.$distinctive_features[$feature_found].'<br/>';
					}
					$parsed = str_replace("{roomfeature ".$reqf."}", "", $parsed);
				}
			}
			//
		}
		//custom fields replace
		preg_match_all('/\{customfield ([0-9]+)\}/U', $parsed, $cmatches);
		if (is_array($cmatches[1]) && @count($cmatches[1]) > 0) {
			$cfids = array();
			foreach($cmatches[1] as $cfid ){
				$cfids[] = $cfid;
			}
			$q = "SELECT * FROM `#__vikbooking_custfields` WHERE `id` IN (".implode(", ", $cfids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
			$vbo_tn->translateContents($cfields, '#__vikbooking_custfields');
			$cfmap = array();
			if (is_array($cfields)) {
				foreach($cfields as $cf) {
					$cfmap[trim(JText::_($cf['name']))] = $cf['id'];
				}
			}
			$cfmapreplace = array();
			$partsreceived = explode("\n", $tcustdata);
			if (count($partsreceived) > 0) {
				foreach($partsreceived as $pst) {
					if (!empty($pst)) {
						$tmpdata = explode(":", $pst);
						if (array_key_exists(trim($tmpdata[0]), $cfmap)) {
							$cfmapreplace[$cfmap[trim($tmpdata[0])]] = trim($tmpdata[1]);
						}
					}
				}
			}
			foreach($cmatches[1] as $cfid ){
				if (array_key_exists($cfid, $cfmapreplace)) {
					$parsed = str_replace("{customfield ".$cfid."}", $cfmapreplace[$cfid], $parsed);
				} else {
					$parsed = str_replace("{customfield ".$cfid."}", "", $parsed);
				}
			}
		}
		//end custom fields replace
		$parsed = str_replace("{rooms_info}", $roomstr, $parsed);
		$parsed = str_replace("{checkin_date}", $tcheckindate, $parsed);
		$parsed = str_replace("{checkout_date}", $tdropdate, $parsed);
		//order details
		$orderdetails = "";
		foreach ($rooms as $num => $r) {
			$expdet = explode("\n", $tprices[$num]);
			$faredets = explode(":", $expdet[0]);
			$orderdetails .= '<div class="roombooked"><strong>'.$r['name'].'</strong><br/>'.$faredets[0];
			if (!empty($expdet[1])) {
				$attrfaredets = explode(":", $expdet[1]);
				if (strlen($attrfaredets[1]) > 0) {
					$orderdetails .= ' - '.$attrfaredets[0].':'.$attrfaredets[1];
				}
			}
			$fareprice = trim(str_replace($currencyname, "", $faredets[1]));
			$orderdetails .= '<div style="float: right;"><span>'.$currencyname.' '.self::numberFormat($fareprice).'</span></div></div>';
			//options
			if (isset($topts[$num]) && is_array($topts[$num]) && count($topts[$num]) > 0) {
				foreach($topts[$num] as $oo) {
					$expopts = explode("\n", $oo);
					foreach($expopts as $optinfo) {
						if (!empty($optinfo)) {
							$splitopt = explode(":", $optinfo);
							$optprice = trim(str_replace($currencyname, "", $splitopt[1]));
							$orderdetails .= '<div class="roomoption"><span>'.$splitopt[0].'</span><div style="float: right;"><span>'.$currencyname.' '.self::numberFormat($optprice).'</span></div></div>';
						}
					}
				}
			}
			//
			if ($roomsnum > 1 && $num < $roomsnum) {
				$orderdetails .= '<br/>';
			}
		}
		//
		//coupon
		if (strlen($couponstr) > 0) {
			$expcoupon = explode(";", $couponstr);
			$orderdetails .= '<br/><div class="discount"><span>'.JText::_('VBCOUPON').' '.$expcoupon[2].'</span><div style="float: right;"><span>- '.$currencyname.' '.self::numberFormat($expcoupon[1]).'</span></div></div>';
		}
		//
		//discount payment method
		if ($status != JText::_('VBCANCELLED')) {
			$idpayment = $order_info['idpayment'];
			if (!empty($idpayment)) {
				$exppay = explode('=', $idpayment);
				$payment = self::getPayment($exppay[0], $vbo_tn);
				if (is_array($payment)) {
					if ($payment['charge'] > 0.00 && $payment['ch_disc'] != 1) {
						//Discount (not charge)
						if ($payment['val_pcent'] == 1) {
							//fixed value
							$ttot -= $payment['charge'];
							$orderdetails .= '<br/><div class="discount"><span>'.$payment['name'].'</span><div style="float: right;"><span>- '.$currencyname.' '.self::numberFormat($payment['charge']).'</span></div></div>';
						} else {
							//percent value
							$percent_disc = $ttot * $payment['charge'] / 100;
							$ttot -= $percent_disc;
							$orderdetails .= '<br/><div class="discount"><span>'.$payment['name'].'</span><div style="float: right;"><span>- '.$currencyname.' '.self::numberFormat($percent_disc).'</span></div></div>';
						}
					}
				}
			}
		}
		//
		$parsed = str_replace("{order_details}", $orderdetails, $parsed);
		//
		$parsed = str_replace("{order_total}", $currencyname.' '.self::numberFormat($ttot), $parsed);
		$parsed = str_replace("{order_link}", '<a href="'.$tlink.'">'.$tlink.'</a>', $parsed);
		$parsed = str_replace("{footer_emailtext}", $tfootm, $parsed);
		//deposit
		$deposit_str = '';
		if ($status != JText::_('VBCOMPLETED') && $status != JText::_('VBCANCELLED') && !self::payTotal() && self::allowDepositFromRates($tars_info)) {
			$percentdeposit = self::getAccPerCent();
			$percentdeposit = self::calcDepositOverride($percentdeposit, $order_info['days']);
			if ($percentdeposit > 0 && self::depositAllowedDaysAdv($order_info['checkin'])) {
				if (self::getTypeDeposit() == "fixed") {
					$deposit_amount = $percentdeposit;
				} else {
					$deposit_amount = $ttot * $percentdeposit / 100;
				}
				if ($deposit_amount > 0) {
					$deposit_str = '<div class="deposit"><span>'.JText::_('VBLEAVEDEPOSIT').'</span><div style="float: right;"><strong>'.$currencyname.' '.self::numberFormat($deposit_amount).'</strong></div></div>';
				}
			}
		}
		$parsed = str_replace("{order_deposit}", $deposit_str, $parsed);
		//
		//Amount Paid - Remaining Balance
		$totpaid_str = '';
		if ($status != JText::_('VBCANCELLED')) {
			$tot_paid = $order_info['totpaid'];
			$diff_topay = (float)$ttot - (float)$tot_paid;
			if ((float)$tot_paid > 0) {
				$totpaid_str .= '<div class="amountpaid"><span>'.JText::_('VBAMOUNTPAID').'</span><div style="float: right;"><strong>'.$currencyname.' '.self::numberFormat($tot_paid).'</strong></div></div>';
				//only in case the remaining balance is greater than 1 to avoid commissions issues
				if ($diff_topay > 1) {
					$totpaid_str .= '<div class="amountpaid"><span>'.JText::_('VBTOTALREMAINING').'</span><div style="float: right;"><strong>'.$currencyname.' '.self::numberFormat($diff_topay).'</strong></div></div>';
				}
			}
		}
		$parsed = str_replace("{order_total_paid}", $totpaid_str, $parsed);
		//
		
		return $parsed;
	}
	
	public static function sendCustMail($to, $subject, $ftitle, $ts, $custdata, $rooms, $first, $second, $pricestr, $optstr, $tot, $link, $status, $orderid = "", $strcouponeff = "", $arrpeople = "", $confirmnumber = "") {
		$sendwhen = self::getSendEmailWhen();
		if ($sendwhen > 1 && $status == JText::_('VBINATTESA')) {
			return true;
		}
		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$dbo = JFactory::getDBO();
		$vbo_tn = self::getTranslator();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$adminemail = $dbo->loadResult();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		$vbo_tn->translateContents($ft, '#__vikbooking_texts');
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sitelogo = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$datesep = self::getDateSeparator();
		$footerordmail = $ft[0]['setting'];
		$textfooterordmail = strip_tags($footerordmail);
		$roomsnum = count($rooms);
		//text part
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VBLIBEIGHT') . " " . date(str_replace("/", $datesep, $df) . ' H:i', $ts) . "\n";
		$msg .= JText::_('VBLIBNINE') . ":\n" . $custdata . "\n";
		$msg .= JText::_('VBLIBTEN') . ": " . $roomsnum . "\n";
		$msg .= JText::_('VBLIBELEVEN') . " " . date(str_replace("/", $datesep, $df) . ' H:i', $first) . "\n";
		$msg .= JText::_('VBLIBTWELVE') . " " . date(str_replace("/", $datesep, $df) . ' H:i', $second) . "\n";
		foreach($rooms as $num => $r) {
			$msg .= ($roomsnum > 1 ? JText::_('VBMAILROOMNUM')." ".$num.": " : "").$r['name']."\n";
			$msg .= JText::_('VBMAILADULTS').": ".intval($arrpeople[$num]['adults']) . "\n";
			if ($arrpeople[$num]['children'] > 0) {
				$msg .= JText::_('VBMAILCHILDREN').": ".$arrpeople[$num]['children'] . "\n";
			}
			$msg .= $pricestr[$num] . "\n";
			$allopts = "";
			if (isset($optstr[$num]) && is_array($optstr[$num]) && count($optstr[$num]) > 0) {
				foreach($optstr[$num] as $oo) {
					$allopts .= $oo;
				}
			}
			$msg .= $allopts . "\n";
		}
		$msg .= JText::_('VBLIBSIX') . " " . $tot . " " . $currencyname . "\n";
		$msg .= JText::_('VBLIBSEVEN') . ": " . $status . "\n\n";
		//Confirmation Number
		if (strlen($confirmnumber) > 0) {
			$msg .= JText::_('VBCONFIRMNUMB') . ": " . $confirmnumber . "\n\n";
		}
		//
		$msg .= JText::_('VBLIBTENTHREE') . ": \n" . $link;
		$msg .= (strlen(trim($textfooterordmail)) > 0 ? "\n" . $textfooterordmail : "");
		//
		//html part
		$from_name = $adminemail;
		$from_address = $adminemail;
		$reply_name = $from_name;
		$reply_address = $from_address;
		$reply_address = $from_address;
		$error_delivery_name = $from_name;
		$error_delivery_address = $from_address;
		$to_name = $to;
		$to_address = $to;
		//vikbooking 1.8 - set array variable to the template file
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`='".(int)$orderid."';";
		$dbo->setQuery($q);
		$dbo->execute();
		$booking_info = $dbo->loadAssoc();
		$tmpl = self::loadEmailTemplate($booking_info);
		//
		$attachlogo = false;
		if (!empty($sitelogo) && @file_exists(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources'. DIRECTORY_SEPARATOR . $sitelogo)) {
			$attachlogo = true;
		}
		$tlogo = ($attachlogo ? "<img src=\"" . VBO_ADMIN_URI . "resources/" . $sitelogo . "\" alt=\"".$ftitle." Logo\"/>\n" : "");
		//vikbooking 1.1
		$tcname = $ftitle."\n";
		$todate = date(str_replace("/", $datesep, $df) . ' H:i', $ts)."\n";
		$tcustdata = nl2br($custdata)."\n";
		$tiname = $rooms;
		$tcheckindate = date(str_replace("/", $datesep, $df) . ' H:i', $first)."\n";
		$tdropdate = date(str_replace("/", $datesep, $df) . ' H:i', $second)."\n";
		$tprices = $pricestr;
		$topts = $optstr;
		$ttot = $tot."\n";
		$tlink = $link;
		$tfootm = $footerordmail;
		$hmess = self::parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $tiname, $tcheckindate, $tdropdate, $tprices, $topts, $ttot, $tlink, $tfootm, $strcouponeff, $arrpeople, $confirmnumber);
		//
		$hmess = '<html>'."\n".'<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'."\n".'<body>'.$hmess.'</body>'."\n".'</html>';
		$mailer = JFactory::getMailer();
		$adsendermail = self::getSenderMail();
		$sender = array($adsendermail, self::getFrontTitle());
		$mailer->setSender($sender);
		$mailer->addRecipient($to);
		$mailer->addReplyTo($adsendermail);
		$mailer->setSubject($subject);
		$mailer->setBody($hmess);
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		$mailer->Send();
		//
		
		return true;
	}

	public static function sendCustMailFromBack($to, $subject, $ftitle, $ts, $custdata, $rooms, $first, $second, $pricestr, $optstr, $tot, $link, $status, $orderid = "", $strcouponeff = "", $arrpeople = "", $confirmnumber = "") {
		//this public static function is called from the administrator site
		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$dbo = JFactory::getDBO();
		$vbo_tn = self::getTranslator();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$adminemail = $dbo->loadResult();
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		$vbo_tn->translateContents($ft, '#__vikbooking_texts');
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sitelogo = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$datesep = self::getDateSeparator();
		$footerordmail = $ft[0]['setting'];
		$textfooterordmail = strip_tags($footerordmail);
		$roomsnum = count($rooms);
		//text part
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VBLIBEIGHT') . " " . date(str_replace("/", $datesep, $df) . ' H:i', $ts) . "\n";
		$msg .= JText::_('VBLIBNINE') . ":\n" . $custdata . "\n";
		$msg .= JText::_('VBLIBTEN') . ": " . $roomsnum . "\n";
		$msg .= JText::_('VBLIBELEVEN') . " " . date(str_replace("/", $datesep, $df) . ' H:i', $first) . "\n";
		$msg .= JText::_('VBLIBTWELVE') . " " . date(str_replace("/", $datesep, $df) . ' H:i', $second) . "\n";
		foreach($rooms as $num => $r) {
			$msg .= ($roomsnum > 1 ? JText::_('VBMAILROOMNUM')." ".$num.": " : "").$r['name']."\n";
			$msg .= JText::_('VBMAILADULTS').": ".intval($arrpeople[$num]['adults']) . "\n";
			if ($arrpeople[$num]['children'] > 0) {
				$msg .= JText::_('VBMAILCHILDREN').": ".$arrpeople[$num]['children'] . "\n";
			}
			$msg .= $pricestr[$num] . "\n";
			$allopts = "";
			if (count($optstr[$num]) > 0) {
				foreach($optstr[$num] as $oo) {
					$allopts .= $oo;
				}
			}
			$msg .= $allopts . "\n";
		}
		$msg .= JText::_('VBLIBSIX') . " " . $tot . " " . $currencyname . "\n";
		$msg .= JText::_('VBLIBSEVEN') . ": " . $status . "\n\n";
		//Confirmation Number
		if (strlen($confirmnumber) > 0) {
			$msg .= JText::_('VBCONFIRMNUMB') . ": " . $confirmnumber . "\n\n";
		}
		//
		$msg .= JText::_('VBLIBTENTHREE') . ": \n" . $link;
		$msg .= (strlen(trim($textfooterordmail)) > 0 ? "\n" . $textfooterordmail : "");
		//
		//html part
		$from_name = $adminemail;
		$from_address = $adminemail;
		$reply_name = $from_name;
		$reply_address = $from_address;
		$reply_address = $from_address;
		$error_delivery_name = $from_name;
		$error_delivery_address = $from_address;
		$to_name = $to;
		$to_address = $to;
		//vikbooking 1.8 - set array variable to the template file
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`='".(int)$orderid."';";
		$dbo->setQuery($q);
		$dbo->execute();
		$booking_info = $dbo->loadAssoc();
		$tmpl = self::loadEmailTemplate($booking_info);
		//
		
		$attachlogo = false;
		if (!empty($sitelogo) && @file_exists(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $sitelogo)) {
			$attachlogo = true;
		}
		$tlogo = ($attachlogo ? "<img src=\"" . VBO_ADMIN_URI . "resources/" . $sitelogo . "\" alt=\"".$ftitle." Logo\"/>\n" : "");
		//vikbooking 1.1
		$tcname = $ftitle."\n";
		$todate = date(str_replace("/", $datesep, $df) . ' H:i', $ts)."\n";
		$tcustdata = nl2br($custdata)."\n";
		$tiname = $rooms;
		$tcheckindate = date(str_replace("/", $datesep, $df) . ' H:i', $first)."\n";
		$tdropdate = date(str_replace("/", $datesep, $df) . ' H:i', $second)."\n";
		$tprices = $pricestr;
		$topts = $optstr;
		$ttot = $tot."\n";
		$tlink = $link;
		$tfootm = $footerordmail;
		$hmess = self::parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $tiname, $tcheckindate, $tdropdate, $tprices, $topts, $ttot, $tlink, $tfootm, $strcouponeff, $arrpeople, $confirmnumber);
		//
		$hmess = '<html>'."\n".'<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'."\n".'<body>'.$hmess.'</body>'."\n".'</html>';
		$mailer = JFactory::getMailer();
		$adsendermail = self::getSenderMail();
		$sender = array($adsendermail, self::getFrontTitle());
		$mailer->setSender($sender);
		$mailer->addRecipient($to);
		$mailer->addReplyTo($adsendermail);
		$mailer->setSubject($subject);
		$mailer->setBody($hmess);
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		$mailer->Send();
		//
		
		return true;
	}
	
	public static function sendCustMailByOrderId($oid) {
		//VikChannelManager should be the one calling this function
		$dbo = JFactory::getDBO();
		$q="SELECT * FROM `#__vikbooking_orders` WHERE `id`=".intval($oid).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order=$dbo->loadAssocList();
			//check if the language in use is the same as the one used during the checkout
			$lang = JFactory::getLanguage();
			$usetag = $lang->getTag();
			if (!empty($order[0]['lang'])) {
				if ($usetag != $order[0]['lang']) {
					$usetag = $order[0]['lang'];
				}
			}
			$lang->load('com_vikbooking', JPATH_SITE, $usetag, true);
			//
			$q="SELECT `or`.*,`r`.`name`,`r`.`units`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$order[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$ordersrooms=$dbo->loadAssocList();
			$currencyname = self::getCurrencyName();
			$realback=self::getHoursRoomAvail() * 3600;
			$realback+=$order[0]['checkout'];
			$rooms = array();
			$tars = array();
			$arrpeople = array();
			//send mail
			$ftitle=self::getFrontTitle();
			$nowts=time();
			$viklink=JURI::root()."index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
			foreach($ordersrooms as $kor => $or) {
				$num = $kor + 1;
				$rooms[$num] = $or;
				$arrpeople[$num]['adults'] = $or['adults'];
				$arrpeople[$num]['children'] = $or['children'];
				$q="SELECT * FROM `#__vikbooking_dispcost` WHERE `id`='".$or['idtar']."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$tar = $dbo->loadAssocList();
					$tar = self::applySeasonsRoom($tar, $order[0]['checkin'], $order[0]['checkout']);
					//different usage
					if ($or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
						$diffusageprice = self::loadAdultsDiff($or['idroom'], $or['adults']);
						//Occupancy Override
						$occ_ovr = self::occupancyOverrideExists($tar, $or['adults']);
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
				} else {
					return false;
				}
			}
			$pcheckin = $order[0]['checkin'];
			$pcheckout = $order[0]['checkout'];
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
					$maxhmore = self::getHoursMoreRb() * 3600;
					if ($maxhmore >= $newdiff) {
						$daysdiff = floor($daysdiff);
					} else {
						$daysdiff = ceil($daysdiff);
					}
				}
			}
			foreach($ordersrooms as $kor => $or) {
				$num = $kor + 1;
				if (is_array($tars[$num])) {
					$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
					$calctar = self::sayCostPlusIva($display_rate, $tars[$num]['idprice']);
					$tars[$num]['calctar'] = $calctar;
					$isdue += $calctar;
					$pricestr[$num] = self::getPriceName($tars[$num]['idprice']) . ": " . $calctar . " " . $currencyname . (!empty($tars[$num]['attrdata']) ? "\n" . self::getPriceAttr($tars[$num]['idprice']) . ": " . $tars[$num]['attrdata'] : "");
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
								$chvar = '';
								if (!empty($actopt[0]['ageintervals']) && $or['children'] > 0 && strstr($stept[1], '-') != false) {
									$optagecosts = self::getOptionIntervalsCosts($actopt[0]['ageintervals']);
									$optagenames = self::getOptionIntervalsAges($actopt[0]['ageintervals']);
									$optagepcent = self::getOptionIntervalsPercentage($actopt[0]['ageintervals']);
									$agestept = explode('-', $stept[1]);
									$stept[1] = $agestept[0];
									$chvar = $agestept[1];
									if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
										//percentage value of the adults tariff
										$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
										$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
									} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
										//VBO 1.10 - percentage value of room base cost
										$display_rate = isset($tars[$num]['room_base_cost']) ? $tars[$num]['room_base_cost'] : (!empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost']);
										$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
									}
									$actopt[0]['chageintv'] = $chvar;
									$actopt[0]['name'] .= ' ('.$optagenames[($chvar - 1)].')';
									$actopt[0]['quan'] = $stept[1];
									$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $order[0]['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
								} else {
									$actopt[0]['quan'] = $stept[1];
									$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
								}
								if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
									$realcost = $actopt[0]['maxprice'];
									if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
										$realcost = $actopt[0]['maxprice'] * $stept[1];
									}
								}
								if ($actopt[0]['perperson'] == 1) {
									$realcost = $realcost * $or['adults'];
								}
								$tmpopr = self::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
								$isdue += $tmpopr;
								$optstr[$num][] = ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": " . $tmpopr . " " . $currencyname . "\n";
							}
						}
					}
				}
				//custom extra costs
				if (!empty($or['extracosts'])) {
					$cur_extra_costs = json_decode($or['extracosts'], true);
					foreach ($cur_extra_costs as $eck => $ecv) {
						$ecplustax = !empty($ecv['idtax']) ? self::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
						$isdue += $ecplustax;
						$optstr[$num][] = $ecv['name'] . ": " . $ecplustax . " " . $currencyname."\n";
					}
				}
				//
			}
			//vikbooking 1.1 coupon
			$usedcoupon = false;
			$origisdue = $isdue;
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			//
			//ConfirmationNumber
			$confirmnumber = $order[0]['confirmnumber'];
			//end ConfirmationNumber
			
			if ($order[0]['status'] != 'confirmed' && $order[0]['status'] != 'standby') {
				return false;
			}
			
			$langstatus = $order[0]['status'] == 'confirmed' ? JText::_('VBCOMPLETED') : JText::_('VBINATTESA');
			
			self::sendCustMail($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VBORDNOL'), $ftitle, $nowts, $order[0]['custdata'], $rooms, $order[0]['checkin'], $order[0]['checkout'], $pricestr, $optstr, $isdue, $viklink, $langstatus, $order[0]['id'], $order[0]['coupon'], $arrpeople, $confirmnumber);
			
			return true;
		}
		return false;
	}

	public static function sendJutility() {
		//deprecated in VBO 1.10
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='sendjutility';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function getCategoryName($idcat, $vbo_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_categories` WHERE `id`=" . $dbo->quote($idcat) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		$p = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
		if (is_object($vbo_tn) && count($p) > 0) {
			$vbo_tn->translateContents($p, '#__vikbooking_categories');
		}
		return count($p) > 0 ? $p[0]['name'] : '';
	}
	
	public static function loadAdultsDiff($idroom, $adults) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_adultsdiff` WHERE `idroom`='" . $idroom . "' AND `adults`='".$adults."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$diff = $dbo->loadAssocList();
			return $diff[0];
		} else {
			return "";
		}
	}

	public static function loadRoomAdultsDiff($idroom) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_adultsdiff` WHERE `idroom`=" . (int)$idroom . " ORDER BY `adults` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$diff = $dbo->loadAssocList();
			$roomdiff = array();
			foreach ($diff as $v) {
				$roomdiff[$v['adults']] = $v;
			}
			return $roomdiff;
		}
		return array();
	}

	public static function occupancyOverrideExists($tar, $adults) {
		foreach ($tar as $k => $v) {
			if (is_array($v) && array_key_exists('occupancy_ovr', $v)) {
				if (array_key_exists($adults, $v['occupancy_ovr'])) {
					return $v['occupancy_ovr'][$adults];
				}
			}
		}
		return false;
	}
	
	public static function getChildrenCharges($idroom, $children, $ages, $num_nights) {
		/* charges as percentage amounts of the adults tariff not supported for third parties (only VBO 1.8) */
		$charges = array();
		if (!($children > 0) || !(count($ages) > 0)) {
			return $charges;
		}
		$dbo = JFactory::getDBO();
		$id_options = array();
		$q = "SELECT `id`,`idopt` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$idroom.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$assocs = $dbo->loadAssocList();
			foreach ($assocs as $opts) {
				if (!empty($opts['idopt'])) {
					$r_ido = explode(';', rtrim($opts['idopt']));
					foreach ($r_ido as $ido) {
						if (!empty($ido) && !in_array($ido, $id_options)) {
							$id_options[] = $ido;
						}
					}
				}
			}
		}
		if (count($id_options) > 0) {
			$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id` IN (".implode(", ", $id_options).") AND `ifchildren`=1 AND (LENGTH(`ageintervals`) > 0 OR `ageintervals` IS NOT NULL) LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ageintervals = $dbo->loadAssocList();
				$split_ages = explode(';;', $ageintervals[0]['ageintervals']);
				$age_range = array();
				foreach ($split_ages as $kg => $spage) {
					if (empty($spage)) {
						continue;
					}
					$parts = explode('_', $spage);
					if (strlen($parts[0]) > 0 && intval($parts[1]) > 0 && floatval($parts[2]) > 0) {
						$ind = count($age_range);
						$age_range[$ind]['from'] = intval($parts[0]);
						$age_range[$ind]['to'] = intval($parts[1]);
						//taxes are calculated later in VCM
						//$age_range[$ind]['cost'] = self::sayOptionalsPlusIva((floatval($parts[2]) * $num_nights), $ageintervals[0]['idiva']);
						$age_range[$ind]['cost'] = floatval($parts[2]) * $num_nights;
						$age_range[$ind]['option_str'] = $ageintervals[0]['id'].':1-'.($kg + 1);
					}
				}
				if (count($age_range) > 0) {
					$tot_charge = 0;
					$affected = array();
					$option_str = '';
					foreach ($ages as $age) {
						if (strlen($age) == 0) {
							continue;
						}
						foreach ($age_range as $range) {
							if (intval($age) >= $range['from'] && intval($age) <= $range['to']) {
								$tot_charge += $range['cost'];
								$affected[] = $age;
								$option_str .= $range['option_str'].';';
								break;
							}
						}
					}
					if ($tot_charge > 0) {
						$charges['total'] = $tot_charge;
						$charges['affected'] = $affected;
						$charges['options'] = $option_str;
					}
				}
			}
		}
		
		return $charges;
	}
	
	public static function sortRoomPrices($arr) {
		$newarr = array ();
		foreach ($arr as $k => $v) {
			$newarr[$k] = $v['cost'];
		}
		asort($newarr);
		$sorted = array ();
		foreach ($newarr as $k => $v) {
			$sorted[$k] = $arr[$k];
		}
		return $sorted;
	}
	
	public static function sortResults($arr) {
		$newarr = array ();
		foreach ($arr as $k => $v) {
			$newarr[$k] = $v[0]['cost'];
		}
		asort($newarr);
		$sorted = array ();
		foreach ($newarr as $k => $v) {
			$sorted[$k] = $arr[$k];
		}
		return $sorted;
	}
	
	public static function sortMultipleResults($arr) {
		foreach ($arr as $k => $v) {
			$newarr = array ();
			foreach ($v as $subk => $subv) {
				$newarr[$subk] = $subv[0]['cost'];
			}
			asort($newarr);
			$sorted = array ();
			foreach ($newarr as $nk => $v) {
				$sorted[$nk] = $arr[$k][$nk];
			}
			$arr[$k] = $sorted;
		}
		return $arr;
	}

	public static function applySeasonalPrices($arr, $from, $to) {
		$dbo = JFactory::getDBO();
		$vbo_tn = self::getTranslator();
		$roomschange = array();
		$one = getdate($from);
		//leap years
		if (($one['year'] % 4) == 0 && ($one['year'] % 100 != 0 || $one['year'] % 400 == 0)) {
			$isleap = true;
		} else {
			$isleap = false;
		}
		//
		$baseone = mktime(0, 0, 0, 1, 1, $one['year']);
		$tomidnightone = intval($one['hours']) * 3600;
		$tomidnightone += intval($one['minutes']) * 60;
		$sfrom = $from - $baseone - $tomidnightone;
		$fromdayts = mktime(0, 0, 0, $one['mon'], $one['mday'], $one['year']);
		$two = getdate($to);
		$basetwo = mktime(0, 0, 0, 1, 1, $two['year']);
		$tomidnighttwo = intval($two['hours']) * 3600;
		$tomidnighttwo += intval($two['minutes']) * 60;
		$sto = $to - $basetwo - $tomidnighttwo;
		//leap years, last day of the month of the season
		if ($isleap) {
			$leapts = mktime(0, 0, 0, 2, 29, $two['year']);
			if ($two[0] >= $leapts) {
				$sfrom -= 86400;
				$sto -= 86400;
			} elseif ($sto < $sfrom && $one['year'] < $two['year']) {
				//lower checkin date when in leap year but not for checkout
				$sfrom -= 86400;
			}
		}
		//
		$q = "SELECT * FROM `#__vikbooking_seasons` WHERE (" .
		 ($sto > $sfrom ? "(`from`<=" . $sfrom . " AND `to`>=" . $sto . ") " : "") .
		 ($sto > $sfrom ? "OR (`from`<=" . $sfrom . " AND `to`>=" . $sfrom . ") " : "(`from`<=" . $sfrom . " AND `to`<=" . $sfrom . " AND `from`>`to`) ") .
		 ($sto > $sfrom ? "OR (`from`<=" . $sto . " AND `to`>=" . $sto . ") " : "OR (`from`>=" . $sto . " AND `to`>=" . $sto . " AND `from`>`to`) ") .
		 ($sto > $sfrom ? "OR (`from`>=" . $sfrom . " AND `from`<=" . $sto . " AND `to`>=" . $sfrom . " AND `to`<=" . $sto . ")" : "OR (`from`>=" . $sfrom . " AND `from`>" . $sto . " AND `to`<" . $sfrom . " AND `to`<=" . $sto . " AND `from`>`to`)") .
		 ($sto > $sfrom ? " OR (`from`<=" . $sfrom . " AND `from`<=" . $sto . " AND `to`<" . $sfrom . " AND `to`<" . $sto . " AND `from`>`to`) OR (`from`>" . $sfrom . " AND `from`>" . $sto . " AND `to`>=" . $sfrom . " AND `to`>=" . $sto . " AND `from`>`to`)" : " OR (`from` <=" . $sfrom . " AND `to` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sto . " AND `from` < `to`)") .
		 ($sto > $sfrom ? " OR (`from` >=" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` <" . $sfrom . " AND `to` >=" . $sto . " AND `from` <=" . $sto . " AND `to` <" . $sfrom . " AND `from` < `to`)"). //VBO 1.6 Else part is for Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8
		 ($sto > $sfrom ? " OR (`from` >" . $sfrom . " AND `from` >" . $sto . " AND `to` >=" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sfrom . " AND `to` >" . $sto . " AND `from` < `to`) OR (`from` <" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <=" . $sto . " AND `from` < `to`)"). //VBO 1.7 Else part for seasons Dec 25 to Dec 31, Jan 2 to Jan 5 - Booking Dec 20 to Jan 7
		");";
		$dbo->setQuery($q);
		$dbo->execute();
		$totseasons = $dbo->getNumRows();
		if ($totseasons > 0) {
			$seasons = $dbo->loadAssocList();
			$vbo_tn->translateContents($seasons, '#__vikbooking_seasons');
			$applyseasons = false;
			$mem = array();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array();
			}
			foreach ($seasons as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					//VBO 1.7 - do not skip seasons tied to the year for bookings between two years
					if ($one['year'] != $s['year'] && $two['year'] != $s['year']) {
						//VBO 1.9 - tied to the year can be set for prev year (Dec 27 to Jan 3) and booking can be Jan 1 to Jan 3 - do not skip in this case
						if (($one['year'] - $s['year']) != 1 || $s['from'] < $s['to']) {
							continue;
						}
						//VBO 1.9 - tied to 2016 going through Jan 2017: dates of December 2017 should skip this speacial price
						if (($one['year'] - $s['year']) == 1 && $s['from'] > $s['to']) {
							$calc_ends = mktime(0, 0, 0, 1, 1, ($s['year'] + 1)) + $s['to'];
							if ($calc_ends < ($from - $tomidnightone)) {
								continue;
							}
						}
					} elseif ($one['year'] < $s['year'] && $two['year'] == $s['year']) {
						//VBO 1.9 - season tied to the year 2017 accross 2018 and we are parsing dates accross prev year 2016-2017
						if ($s['from'] > $s['to']) {
							continue;
						}
					} elseif ($one['year'] == $s['year'] && $two['year'] == $s['year'] && $s['from'] > $s['to']) {
						//VBO 1.9 - season tied to the year 2017 accross 2018 and we are parsing dates at the beginning of 2017 due to beginning loop in 2016 (Rates Overview)
						if (($baseone + $s['from']) > $to) {
							continue;
						}
					}
				}
				//
				$allrooms = explode(",", $s['idrooms']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				$inits = $baseone + $s['from'];
				if ($s['from'] < $s['to']) {
					$ends = $basetwo + $s['to'];
					//VikBooking 1.6 check if the inits must be set to the year after
					//ex. Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8 to charge Jan 6,7
					if ($sfrom > $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto <= $s['to'] && $s['from'] < $s['to'] && $sfrom > $sto) {
						$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
						$inits = $tmpbase + $s['from'];
					} elseif ($sfrom >= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VBO 1.7 - Season Dec 23 to Dec 29 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom <= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VBO 1.7 - Season Dec 30 to Dec 31 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom > $s['from'] && $sfrom > $s['to'] && $sto >= $s['from'] && ($sto >= $s['to'] || $sto <= $s['to']) && $sfrom > $sto) {
						//VBO 1.7 - Season Jan 1 to Jan 2 - Booking Dec 29 to Jan 5
						$inits = $basetwo + $s['from'];
					}
				} else {
					//between 2 years
					if ($baseone < $basetwo) {
						//ex. 29/12/2012 - 14/01/2013
						$ends = $basetwo + $s['to'];
					} else {
						if (($sfrom >= $s['from'] && $sto >= $s['from']) OR ($sfrom < $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto > $s['to'])) {
							//ex. 25/12 - 30/12 with init season on 20/12 OR 27/12 for counting 28,29,30/12
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
							$ends = $tmpbase + $s['to'];
						} else {
							//ex. 03/01 - 09/01
							$ends = $basetwo + $s['to'];
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] - 1));
							$inits = $tmpbase + $s['from'];
						}
					}
				}
				//leap years
				if ($isleap == true) {
					$infoseason = getdate($inits);
					$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
					//VikBooking 1.6 added below && $infoseason['year'] == $one['year']
					//for those seasons like 2015 Dec 14 to 2016 Jan 5 and booking dates like 2016 Jan 1 to Jan 6 where 2015 is not leap
					if ($infoseason[0] >= $leapts && $infoseason['year'] == $one['year']) {
						$inits += 86400;
						$ends += 86400;
					}
				}
				//
				//Promotions
				$promotion = array();
				if ($s['promo'] == 1) {
					$daysadv = (($inits - time()) / 86400);
					$daysadv = $daysadv > 0 ? (int)ceil($daysadv) : 0;
					if (!empty($s['promodaysadv']) && $s['promodaysadv'] > $daysadv) {
						continue;
					} else {
						$promotion['todaydaysadv'] = $daysadv;
						$promotion['promodaysadv'] = $s['promodaysadv'];
						$promotion['promotxt'] = $s['promotxt'];
					}
				}
				//
				//Occupancy Override
				$occupancy_ovr = !empty($s['occupancy_ovr']) ? json_decode($s['occupancy_ovr'], true) : array();
				//
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				//checkin must be after the begin of the season
				if ($s['checkinincl'] == 1) {
					$checkininclok = false;
					if ($s['from'] < $s['to']) {
						if ($sfrom >= $s['from'] && $sfrom <= $s['to']) {
							$checkininclok = true;
						}
					} else {
						if (($sfrom >= $s['from'] && $sfrom > $s['to']) || ($sfrom < $s['from'] && $sfrom <= $s['to'])) {
							$checkininclok = true;
						}
					}
				} else {
					$checkininclok = true;
				}
				//
				if ($checkininclok == true) {
					foreach ($arr as $k => $a) {
						//Applied only to some types of price
						if (count($allprices) > 0 && !empty($allprices[0])) {
							if (!in_array("-" . $a[0]['idprice'] . "-", $allprices)) {
								continue;
							}
						}
						//
						if (in_array("-" . $a[0]['idroom'] . "-", $allrooms)) {
							$affdays = 0;
							$season_fromdayts = $fromdayts;
							$is_dst = date('I', $season_fromdayts);
							for ($i = 0; $i < $a[0]['days']; $i++) {
								$todayts = $season_fromdayts + ($i * 86400);
								$is_now_dst = date('I', $todayts);
								if ($is_dst != $is_now_dst) {
									//Daylight Saving Time has changed, check how
									if ((bool)$is_dst === true) {
										$todayts += 3600;
										$season_fromdayts += 3600;
									} else {
										$todayts -= 3600;
										$season_fromdayts -= 3600;
									}
									$is_dst = $is_now_dst;
								}
								if ($todayts >= $inits && $todayts <= $ends) {
									//week days
									if ($filterwdays == true) {
										$checkwday = getdate($todayts);
										if (in_array($checkwday['wday'], $wdays)) {
											$affdays++;
										}
									} else {
										$affdays++;
									}
									//
								}
							}
							if ($affdays > 0) {
								$applyseasons = true;
								$dailyprice = $a[0]['cost'] / $a[0]['days'];
								//VikBooking 1.2 for abs or pcent and values overrides
								if (intval($s['val_pcent']) == 2) {
									//percentage value
									$pctval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strstr($ovrinfo[0], '-i') != false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a[0]['days']) {
														$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
											$pctval = $arrvaloverrides[$a[0]['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$cpercent = 100 + $pctval;
									} else {
										//discount
										$cpercent = 100 - $pctval;
									}
									$newprice = ($dailyprice * $cpercent / 100) * $affdays;
								} else {
									//absolute value
									$absval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strstr($ovrinfo[0], '-i') != false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a[0]['days']) {
														$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
											$absval = $arrvaloverrides[$a[0]['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$newprice = ($dailyprice + $absval) * $affdays;
									} else {
										//discount
										$newprice = ($dailyprice - $absval) * $affdays;
									}
								}
								//end VikBooking 1.2 for abs or pcent and values overrides
								//VikBooking 1.4
								if (!empty($s['roundmode'])) {
									$newprice = round($newprice, 0, constant($s['roundmode']));
								} else {
									//VikBooking 1.5
									$newprice = round($newprice, 2);
								}
								//
								//Promotions (only if no value overrides set the amount to 0)
								if (count($promotion) > 0 && ((isset($absval) && $absval > 0) || $pctval > 0)) {
									$mem[$k]['promotion'] = $promotion;
								}
								//
								//Occupancy Override
								if (array_key_exists($a[0]['idroom'], $occupancy_ovr) && count($occupancy_ovr[$a[0]['idroom']]) > 0) {
									$mem[$k]['occupancy_ovr'] = $occupancy_ovr[$a[0]['idroom']];
								}
								//
								$mem[$k]['sum'][] = $newprice;
								$mem[$k]['daysused'] += $affdays;
								$roomschange[] = $a[0]['idroom'];
							}
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k][0]['cost'] / $arr[$k][0]['days'];
						$restdays = $arr[$k][0]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						//Promotions
						if (array_key_exists('promotion', $v)) {
							$arr[$k][0]['promotion'] = $v['promotion'];
						}
						//
						//Occupancy Override
						if (array_key_exists('occupancy_ovr', $v)) {
							$arr[$k][0]['occupancy_ovr'] = $v['occupancy_ovr'];
						}
						//
						$arr[$k][0]['cost'] = $newprice;
						$arr[$k][0]['affdays'] = $v['daysused'];
					}
				}
			}
		}
		//week days with no season
		$roomschange = array_unique($roomschange);
		$q="SELECT * FROM `#__vikbooking_seasons` WHERE ((`from` = 0 AND `to` = 0) OR (`from` IS NULL AND `to` IS NULL));";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$specials = $dbo->loadAssocList();
			$vbo_tn->translateContents($specials, '#__vikbooking_seasons');
			$applyseasons = false;
			unset($mem);
			$mem = array();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array();
			}
			foreach($specials as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					if ($one['year'] != $s['year']) {
						continue;
					}
				}
				//
				$allrooms = explode(",", $s['idrooms']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				foreach ($arr as $k => $a) {
					//only rooms with no price modifications from seasons
					//Applied only to some types of price
					if (count($allprices) > 0 && !empty($allprices[0])) {
						if (!in_array("-" . $a[0]['idprice'] . "-", $allprices)) {
							continue;
						}
					}
					//
					if (in_array("-" . $a[0]['idroom'] . "-", $allrooms) && !in_array($a[0]['idroom'], $roomschange)) {
						$affdays = 0;
						$season_fromdayts = $fromdayts;
						$is_dst = date('I', $season_fromdayts);
						for ($i = 0; $i < $a[0]['days']; $i++) {
							$todayts = $season_fromdayts + ($i * 86400);
							$is_now_dst = date('I', $todayts);
							if ($is_dst != $is_now_dst) {
								//Daylight Saving Time has changed, check how
								if ((bool)$is_dst === true) {
									$todayts += 3600;
									$season_fromdayts += 3600;
								} else {
									$todayts -= 3600;
									$season_fromdayts -= 3600;
								}
								$is_dst = $is_now_dst;
							}
							//week days
							if ($filterwdays == true) {
								$checkwday = getdate($todayts);
								if (in_array($checkwday['wday'], $wdays)) {
									$affdays++;
								}
							}
							//
						}
						if ($affdays > 0) {
							$applyseasons = true;
							$dailyprice = $a[0]['cost'] / $a[0]['days'];
							//VikBooking 1.2 for abs or pcent and values overrides
							if (intval($s['val_pcent']) == 2) {
								//percentage value
								$pctval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strstr($ovrinfo[0], '-i') != false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a[0]['days']) {
													$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
										$pctval = $arrvaloverrides[$a[0]['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$cpercent = 100 + $pctval;
								} else {
									//discount
									$cpercent = 100 - $pctval;
								}
								$newprice = ($dailyprice * $cpercent / 100) * $affdays;
							} else {
								//absolute value
								$absval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strstr($ovrinfo[0], '-i') != false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a[0]['days']) {
													$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
										$absval = $arrvaloverrides[$a[0]['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$newprice = ($dailyprice + $absval) * $affdays;
								} else {
									//discount
									$newprice = ($dailyprice - $absval) * $affdays;
								}
							}
							//end VikBooking 1.2 for abs or pcent and values overrides
							//VikBooking 1.4
							if (!empty($s['roundmode'])) {
								$newprice = round($newprice, 0, constant($s['roundmode']));
							} else {
								//VikBooking 1.5
								$newprice = round($newprice, 2);
							}
							//
							$mem[$k]['sum'][] = $newprice;
							$mem[$k]['daysused'] += $affdays;
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k][0]['cost'] / $arr[$k][0]['days'];
						$restdays = $arr[$k][0]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						$arr[$k][0]['cost'] = $newprice;
						$arr[$k][0]['affdays'] = $v['daysused'];
					}
				}
			}
		}
		//end week days with no season
		return $arr;
	}

	/**
	 * Applies the special prices over an array of tariffs.
	 * The function is also used by VCM (>= 1.6.5) with specific arguments.
	 *
	 * @param 	array  		$arr 			array of tariffs taken from the DB
	 * @param 	int  		$from 			start timestamp
	 * @param 	int  		$to 			end timestamp
	 * @param 	array  		$parsed_season 	array of a season to parse (used to render the seasons calendars in back-end and front-end)
	 * @param 	array  		$seasons_dates 	(VBO 1.10) array of seasons with dates filter taken from the DB to avoid multiple queries (VCM)
	 * @param 	array  		$seasons_wdays 	(VBO 1.10) array of seasons with weekdays filter (only) taken from the DB to avoid multiple queries (VCM)
	 *
	 * @return 	array
	 */
	public static function applySeasonsRoom($arr, $from, $to, $parsed_season = array(), $seasons_dates = array(), $seasons_wdays = array()) {
		$dbo = JFactory::getDBO();
		$vbo_tn = self::getTranslator();
		$roomschange = array();
		$one = getdate($from);
		//leap years
		if ($one['year'] % 4 == 0 && ($one['year'] % 100 != 0 || $one['year'] % 400 == 0)) {
			$isleap = true;
		} else {
			$isleap = false;
		}
		//
		$baseone = mktime(0, 0, 0, 1, 1, $one['year']);
		$tomidnightone = intval($one['hours']) * 3600;
		$tomidnightone += intval($one['minutes']) * 60;
		$sfrom = $from - $baseone - $tomidnightone;
		$fromdayts = mktime(0, 0, 0, $one['mon'], $one['mday'], $one['year']);
		$two = getdate($to);
		$basetwo = mktime(0, 0, 0, 1, 1, $two['year']);
		$tomidnighttwo = intval($two['hours']) * 3600;
		$tomidnighttwo += intval($two['minutes']) * 60;
		$sto = $to - $basetwo - $tomidnighttwo;
		//leap years, last day of the month of the season
		if ($isleap) {
			$leapts = mktime(0, 0, 0, 2, 29, $two['year']);
			if ($two[0] >= $leapts) {
				$sfrom -= 86400;
				$sto -= 86400;
			} elseif ($sto < $sfrom && $one['year'] < $two['year']) {
				//lower checkin date when in leap year but not for checkout
				$sfrom -= 86400;
			}
		}
		//
		$totseasons = 0;
		if (count($parsed_season) == 0 && count($seasons_dates) == 0) {
			$q = "SELECT * FROM `#__vikbooking_seasons` WHERE (" .
		 	($sto > $sfrom ? "(`from`<=" . $sfrom . " AND `to`>=" . $sto . ") " : "") .
		 	($sto > $sfrom ? "OR (`from`<=" . $sfrom . " AND `to`>=" . $sfrom . ") " : "(`from`<=" . $sfrom . " AND `to`<=" . $sfrom . " AND `from`>`to`) ") .
		 	($sto > $sfrom ? "OR (`from`<=" . $sto . " AND `to`>=" . $sto . ") " : "OR (`from`>=" . $sto . " AND `to`>=" . $sto . " AND `from`>`to`) ") .
		 	($sto > $sfrom ? "OR (`from`>=" . $sfrom . " AND `from`<=" . $sto . " AND `to`>=" . $sfrom . " AND `to`<=" . $sto . ")" : "OR (`from`>=" . $sfrom . " AND `from`>" . $sto . " AND `to`<" . $sfrom . " AND `to`<=" . $sto . " AND `from`>`to`)") .
		 	($sto > $sfrom ? " OR (`from`<=" . $sfrom . " AND `from`<=" . $sto . " AND `to`<" . $sfrom . " AND `to`<" . $sto . " AND `from`>`to`) OR (`from`>" . $sfrom . " AND `from`>" . $sto . " AND `to`>=" . $sfrom . " AND `to`>=" . $sto . " AND `from`>`to`)" : " OR (`from` <=" . $sfrom . " AND `to` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sto . " AND `from` < `to`)") .
		 	($sto > $sfrom ? " OR (`from` >=" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` <" . $sfrom . " AND `to` >=" . $sto . " AND `from` <=" . $sto . " AND `to` <" . $sfrom . " AND `from` < `to`)"). //VBO 1.6 Else part is for Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8
		 	($sto > $sfrom ? " OR (`from` >" . $sfrom . " AND `from` >" . $sto . " AND `to` >=" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sfrom . " AND `to` >" . $sto . " AND `from` < `to`) OR (`from` <" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <=" . $sto . " AND `from` < `to`)"). //VBO 1.7 Else part for seasons Dec 25 to Dec 31, Jan 2 to Jan 5 - Booking Dec 20 to Jan 7
			");";
			$dbo->setQuery($q);
			$dbo->execute();
			$totseasons = $dbo->getNumRows();
		}
		if ($totseasons > 0 || count($parsed_season) > 0 || count($seasons_dates) > 0) {
			if ($totseasons > 0) {
				$seasons = $dbo->loadAssocList();
			} elseif (count($parsed_season) > 0) {
				$seasons = array($parsed_season);
			} else {
				$seasons = $seasons_dates;
			}
			$vbo_tn->translateContents($seasons, '#__vikbooking_seasons');
			$applyseasons = false;
			$mem = array ();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array();
				$mem[$k]['spids'] = array();
			}
			$affdayslistless = array();
			foreach ($seasons as $s) {
				//VBO 1.10 - double check that the 'from' and 'to' properties are not empty (dates filter), in case VCM passes an array of seasons already taken from the DB
				if (empty($s['from']) && empty($s['to']) && !empty($s['wdays'])) {
					//a season for Jan 1st to Jan 1st (1 day), with NO week-days filter is still accepted
					continue;
				}
				//
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					//VBO 1.7 - do not skip seasons tied to the year for bookings between two years
					if ($one['year'] != $s['year'] && $two['year'] != $s['year']) {
						//VBO 1.9 - tied to the year can be set for prev year (Dec 27 to Jan 3) and booking can be Jan 1 to Jan 3 - do not skip in this case
						if (($one['year'] - $s['year']) != 1 || $s['from'] < $s['to']) {
							continue;
						}
						//VBO 1.9 - tied to 2016 going through Jan 2017: dates of December 2017 should skip this speacial price
						if (($one['year'] - $s['year']) == 1 && $s['from'] > $s['to']) {
							$calc_ends = mktime(0, 0, 0, 1, 1, ($s['year'] + 1)) + $s['to'];
							if ($calc_ends < ($from - $tomidnightone)) {
								continue;
							}
						}
					} elseif ($one['year'] < $s['year'] && $two['year'] == $s['year']) {
						//VBO 1.9 - season tied to the year 2017 accross 2018 and we are parsing dates accross prev year 2016-2017
						if ($s['from'] > $s['to']) {
							continue;
						}
					} elseif ($one['year'] == $s['year'] && $two['year'] == $s['year'] && $s['from'] > $s['to']) {
						//VBO 1.9 - season tied to the year 2017 accross 2018 and we are parsing dates at the beginning of 2017 due to beginning loop in 2016 (Rates Overview)
						if (($baseone + $s['from']) > $to) {
							continue;
						}
					}
				}
				//
				$allrooms = explode(",", $s['idrooms']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				$inits = $baseone + $s['from'];
				if ($s['from'] < $s['to']) {
					$ends = $basetwo + $s['to'];
					//VikBooking 1.6 check if the inits must be set to the year after
					//ex. Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8 to charge Jan 6,7
					if ($sfrom > $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto <= $s['to'] && $s['from'] < $s['to'] && $sfrom > $sto) {
						$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
						$inits = $tmpbase + $s['from'];
					} elseif ($sfrom >= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VBO 1.7 - Season Dec 23 to Dec 29 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom <= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VBO 1.7 - Season Dec 30 to Dec 31 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom > $s['from'] && $sfrom > $s['to'] && $sto >= $s['from'] && ($sto >= $s['to'] || $sto <= $s['to']) && $sfrom > $sto) {
						//VBO 1.7 - Season Jan 1 to Jan 2 - Booking Dec 29 to Jan 5
						$inits = $basetwo + $s['from'];
					}
				} else {
					//between 2 years
					if ($baseone < $basetwo) {
						//ex. 29/12/2012 - 14/01/2013
						$ends = $basetwo + $s['to'];
					} else {
						if (($sfrom >= $s['from'] && $sto >= $s['from']) || ($sfrom < $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto > $s['to'])) {
							//ex. 25/12 - 30/12 with init season on 20/12 OR 27/12 for counting 28,29,30/12
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
							$ends = $tmpbase + $s['to'];
						} else {
							//ex. 03/01 - 09/01
							$ends = $basetwo + $s['to'];
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] - 1));
							$inits = $tmpbase + $s['from'];
						}
					}
				}
				//leap years
				if ($isleap == true) {
					$infoseason = getdate($inits);
					$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
					//VikBooking 1.6 added below && $infoseason['year'] == $one['year']
					//for those seasons like 2015 Dec 14 to 2016 Jan 5 and booking dates like 2016 Jan 1 to Jan 6 where 2015 is not leap
					if ($infoseason[0] >= $leapts && $infoseason['year'] == $one['year']) {
						$inits += 86400;
						$ends += 86400;
					}
				}
				//
				//Promotions
				$promotion = array();
				if ($s['promo'] == 1) {
					$daysadv = (($inits - time()) / 86400);
					$daysadv = $daysadv > 0 ? (int)ceil($daysadv) : 0;
					if (!empty($s['promodaysadv']) && $s['promodaysadv'] > $daysadv) {
						continue;
					} else {
						$promotion['todaydaysadv'] = $daysadv;
						$promotion['promodaysadv'] = $s['promodaysadv'];
						$promotion['promotxt'] = $s['promotxt'];
					}
				}
				//
				//Occupancy Override
				$occupancy_ovr = !empty($s['occupancy_ovr']) ? json_decode($s['occupancy_ovr'], true) : array();
				//
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				//checkin must be after the begin of the season
				if ($s['checkinincl'] == 1) {
					$checkininclok = false;
					if ($s['from'] < $s['to']) {
						if ($sfrom >= $s['from'] && $sfrom <= $s['to']) {
							$checkininclok = true;
						}
					} else {
						if (($sfrom >= $s['from'] && $sfrom > $s['to']) || ($sfrom < $s['from'] && $sfrom <= $s['to'])) {
							$checkininclok = true;
						}
					}
				} else {
					$checkininclok = true;
				}
				//
				if ($checkininclok == true) {
					foreach ($arr as $k => $a) {
						//Applied only to some types of price
						if (count($allprices) > 0 && !empty($allprices[0])) {
							//VikBooking 1.6: Price Calendar sets the idprice to -1
							if (!in_array("-" . $a['idprice'] . "-", $allprices) && $a['idprice'] > 0) {
								continue;
							}
						}
						//
						if (in_array("-" . $a['idroom'] . "-", $allrooms)) {
							$affdays = 0;
							$season_fromdayts = $fromdayts;
							$is_dst = date('I', $season_fromdayts);
							for ($i = 0; $i < $a['days']; $i++) {
								$todayts = $season_fromdayts + ($i * 86400);
								$is_now_dst = date('I', $todayts);
								if ($is_dst != $is_now_dst) {
									//Daylight Saving Time has changed, check how
									if ((bool)$is_dst === true) {
										$todayts += 3600;
										$season_fromdayts += 3600;
									} else {
										$todayts -= 3600;
										$season_fromdayts -= 3600;
									}
									$is_dst = $is_now_dst;
								}
								if ($todayts >= $inits && $todayts <= $ends) {
									$checkwday = getdate($todayts);
									//week days
									if ($filterwdays == true) {
										if (in_array($checkwday['wday'], $wdays)) {
											if (!isset($arr[$k]['affdayslist'])) {
												$arr[$k]['affdayslist'] = array();
											}
											$arr[$k]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']] = isset($arr[$k]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']]) && $arr[$k]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']] > 0 ? $arr[$k]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']] : 0;
											$arr[$k]['origdailycost'] = $a['cost'] / $a['days'];
											$affdayslistless[$s['id']][] = $checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon'];
											$affdays++;
										}
									} else {
										if (!isset($arr[$k]['affdayslist'])) {
											$arr[$k]['affdayslist'] = array();
										}
										$arr[$k]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']] = isset($arr[$k]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']]) && $arr[$k]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']] > 0 ? $arr[$k]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']] : 0;
										$arr[$k]['origdailycost'] = $a['cost'] / $a['days'];
										$affdayslistless[$s['id']][] = $checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon'];
										$affdays++;
									}
									//
								}
							}
							if ($affdays > 0) {
								$applyseasons = true;
								$dailyprice = $a['cost'] / $a['days'];
								//VikBooking 1.2 for abs or pcent and values overrides
								if (intval($s['val_pcent']) == 2) {
									//percentage value
									$pctval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strstr($ovrinfo[0], '-i') != false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a['days']) {
														$arrvaloverrides[$a['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a['days'], $arrvaloverrides)) {
											$pctval = $arrvaloverrides[$a['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$cpercent = 100 + $pctval;
									} else {
										//discount
										$cpercent = 100 - $pctval;
									}
									$dailysum = ($dailyprice * $cpercent / 100);
									$newprice = $dailysum * $affdays;
								} else {
									//absolute value
									$absval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strstr($ovrinfo[0], '-i') != false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a['days']) {
														$arrvaloverrides[$a['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a['days'], $arrvaloverrides)) {
											$absval = $arrvaloverrides[$a['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$dailysum = ($dailyprice + $absval);
										$newprice = $dailysum * $affdays;
									} else {
										//discount
										$dailysum = ($dailyprice - $absval);
										$newprice = $dailysum * $affdays;
									}
								}
								//end VikBooking 1.2 for abs or pcent and values overrides
								//VikBooking 1.4
								if (!empty($s['roundmode'])) {
									$newprice = round($newprice, 0, constant($s['roundmode']));
								} else {
									//VikBooking 1.5
									$newprice = round($newprice, 2);
								}
								//
								//Promotions (only if no value overrides set the amount to 0)
								if (count($promotion) > 0 && ((isset($absval) && $absval > 0) || $pctval > 0)) {
									$mem[$k]['promotion'] = $promotion;
								}
								//
								//Occupancy Override
								if (array_key_exists($a['idroom'], $occupancy_ovr) && count($occupancy_ovr[$a['idroom']]) > 0) {
									$mem[$k]['occupancy_ovr'] = $occupancy_ovr[$a['idroom']];
								}
								//
								foreach($arr[$k]['affdayslist'] as $affk => $affv) {
									if (in_array($affk, $affdayslistless[$s['id']])) {
										$arr[$k]['affdayslist'][$affk] = !empty($arr[$k]['affdayslist'][$affk]) && $arr[$k]['affdayslist'][$affk] > 0 ? ($arr[$k]['affdayslist'][$affk] - $arr[$k]['origdailycost'] + $dailysum) : ($affv + $dailysum);
									}
								}
								if (!in_array($s['id'], $mem[$k]['spids'])) {
									$mem[$k]['spids'][] = $s['id'];
								}
								$mem[$k]['sum'][] = $newprice;
								$mem[$k]['daysused'] += $affdays;
								$roomschange[] = $a['idroom'];
							}
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k]['cost'] / $arr[$k]['days'];
						$restdays = $arr[$k]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						//Promotions
						if (array_key_exists('promotion', $v)) {
							$arr[$k]['promotion'] = $v['promotion'];
						}
						//
						//Occupancy Override
						if (array_key_exists('occupancy_ovr', $v)) {
							$arr[$k]['occupancy_ovr'] = $v['occupancy_ovr'];
						}
						//
						$arr[$k]['cost'] = $newprice;
						$arr[$k]['affdays'] = $v['daysused'];
						if (array_key_exists('spids', $v) && count($v['spids']) > 0) {
							$arr[$k]['spids'] = $v['spids'];
						}
					}
				}
			}
		}
		//week days with no season
		$roomschange = array_unique($roomschange);
		$totspecials = 0;
		if (count($seasons_wdays) == 0) {
			$q = "SELECT * FROM `#__vikbooking_seasons` WHERE ((`from` = 0 AND `to` = 0) OR (`from` IS NULL AND `to` IS NULL));";
			$dbo->setQuery($q);
			$dbo->execute();
			$totspecials = $dbo->getNumRows();
		}
		if ($totspecials > 0 || count($seasons_wdays) > 0) {
			$specials = $totspecials > 0 ? $dbo->loadAssocList() : $seasons_wdays;
			$vbo_tn->translateContents($specials, '#__vikbooking_seasons');
			$applyseasons = false;
			unset($mem);
			$mem = array();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array();
				$mem[$k]['spids'] = array();
			}
			foreach ($specials as $s) {
				//VBO 1.10 - double check that the 'from' and 'to' properties are empty (only weekdays), in case VCM passes an array of seasons already taken from the DB
				if (!empty($s['from']) || !empty($s['to'])) {
					continue;
				}
				//
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					if ($one['year'] != $s['year']) {
						continue;
					}
				}
				//
				$allrooms = explode(",", $s['idrooms']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				foreach ($arr as $k => $a) {
					//only rooms with no price modifications from seasons
					//Applied only to some types of price
					if (count($allprices) > 0 && !empty($allprices[0])) {
						//VikBooking 1.6: Price Calendar sets the idprice to -1
						if (!in_array("-" . $a['idprice'] . "-", $allprices) && $a['idprice'] > 0) {
							continue;
						}
					}
					//
					if (in_array("-" . $a['idroom'] . "-", $allrooms) && !in_array($a['idroom'], $roomschange)) {
						$affdays = 0;
						$season_fromdayts = $fromdayts;
						$is_dst = date('I', $season_fromdayts);
						for ($i = 0; $i < $a['days']; $i++) {
							$todayts = $season_fromdayts + ($i * 86400);
							$is_now_dst = date('I', $todayts);
							if ($is_dst != $is_now_dst) {
								//Daylight Saving Time has changed, check how
								if ((bool)$is_dst === true) {
									$todayts += 3600;
									$season_fromdayts += 3600;
								} else {
									$todayts -= 3600;
									$season_fromdayts -= 3600;
								}
								$is_dst = $is_now_dst;
							}
							//week days
							if ($filterwdays == true) {
								$checkwday = getdate($todayts);
								if (in_array($checkwday['wday'], $wdays)) {
									$arr[$k]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']] = 0;
									$arr[$k]['origdailycost'] = $a['cost'] / $a['days'];
									$affdays++;
								}
							}
							//
						}
						if ($affdays > 0) {
							$applyseasons = true;
							$dailyprice = $a['cost'] / $a['days'];
							//VikBooking 1.2 for abs or pcent and values overrides
							if (intval($s['val_pcent']) == 2) {
								//percentage value
								$pctval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strstr($ovrinfo[0], '-i') != false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a['days']) {
													$arrvaloverrides[$a['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a['days'], $arrvaloverrides)) {
										$pctval = $arrvaloverrides[$a['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$cpercent = 100 + $pctval;
								} else {
									//discount
									$cpercent = 100 - $pctval;
								}
								$dailysum = ($dailyprice * $cpercent / 100);
								$newprice = $dailysum * $affdays;
							} else {
								//absolute value
								$absval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strstr($ovrinfo[0], '-i') != false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a['days']) {
													$arrvaloverrides[$a['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a['days'], $arrvaloverrides)) {
										$absval = $arrvaloverrides[$a['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$dailysum = ($dailyprice + $absval);
									$newprice = $dailysum * $affdays;
								} else {
									//discount
									$dailysum = ($dailyprice - $absval);
									$newprice = $dailysum * $affdays;
								}
							}
							//end VikBooking 1.2 for abs or pcent and values overrides
							//VikBooking 1.4
							if (!empty($s['roundmode'])) {
								$newprice = round($newprice, 0, constant($s['roundmode']));
							} else {
								//VikBooking 1.5
								$newprice = round($newprice, 2);
							}
							//
							foreach($arr[$k]['affdayslist'] as $affk => $affv) {
								$arr[$k]['affdayslist'][$affk] = $affv + $dailysum;
							}
							if (!in_array($s['id'], $mem[$k]['spids'])) {
								$mem[$k]['spids'][] = $s['id'];
							}
							$mem[$k]['sum'][] = $newprice;
							$mem[$k]['daysused'] += $affdays;
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k]['cost'] / $arr[$k]['days'];
						$restdays = $arr[$k]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						$arr[$k]['cost'] = $newprice;
						$arr[$k]['affdays'] = $v['daysused'];
						if (array_key_exists('spids', $v) && count($v['spids']) > 0) {
							$arr[$k]['spids'] = $v['spids'];
						}
					}
				}
			}
		}
		//end week days with no season
		return $arr;
	}

	public static function getRoomRplansClosingDates($idroom) {
		$dbo = JFactory::getDBO();
		$closingd = array();
		$q = "SELECT * FROM `#__vikbooking_prices` WHERE `closingd` IS NOT NULL;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$price_records = $dbo->loadAssocList();
			foreach ($price_records as $prec) {
				if (empty($prec['closingd'])) {
					continue;
				}
				$price_closing = json_decode($prec['closingd'], true);
				if (!is_array($price_closing) || !(count($price_closing) > 0) || !array_key_exists($idroom, $price_closing) || !(count($price_closing[$idroom]) > 0)) {
					continue;
				}
				//check expired dates and clean up
				$today_midnight = mktime(0, 0, 0);
				$cleaned = false;
				foreach ($price_closing[$idroom] as $k => $v) {
					if (strtotime($v) < $today_midnight) {
						$cleaned = true;
						unset($price_closing[$idroom][$k]);
					}
				}
				//
				if (!(count($price_closing[$idroom]) > 0)) {
					unset($price_closing[$idroom]);
				} elseif ($cleaned === true) {
					//reset array keys for smaller JSON size
					$price_closing[$idroom] = array_values($price_closing[$idroom]);
				}
				if ($cleaned === true) {
					$q = "UPDATE `#__vikbooking_prices` SET `closingd`=".(count($price_closing) > 0 ? $dbo->quote(json_encode($price_closing)) : "NULL")." WHERE `id`=".$prec['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				if (!(count($price_closing[$idroom]) > 0) || !(count($price_closing[$idroom]) > 0)) {
					continue;
				}
				$closingd[$prec['id']] = $price_closing[$idroom];
			}
		}
		return $closingd;
	}

	public static function getRoomRplansClosedInDates($roomids, $checkints, $numnights) {
		$dbo = JFactory::getDBO();
		$closingd = array();
		$q = "SELECT * FROM `#__vikbooking_prices` WHERE `closingd` IS NOT NULL;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0 && count($roomids) > 0) {
			$price_records = $dbo->loadAssocList();
			$info_start = getdate($checkints);
			$checkin_midnight = mktime(0, 0, 0, $info_start['mon'], $info_start['mday'], $info_start['year']);
			$all_nights = array();
			for ($i=0; $i < (int)$numnights; $i++) {
				$next_midnight = mktime(0, 0, 0, $info_start['mon'], ($info_start['mday'] + $i), $info_start['year']);
				$all_nights[] = date('Y-m-d', $next_midnight);
			}
			foreach ($price_records as $prec) {
				if (empty($prec['closingd'])) {
					continue;
				}
				$price_closing = json_decode($prec['closingd'], true);
				if (!is_array($price_closing) || !(count($price_closing) > 0)) {
					continue;
				}
				foreach ($price_closing as $idroom => $rclosedd) {
					if (!in_array($idroom, $roomids) || !is_array($rclosedd)) {
						continue;
					}
					if (!array_key_exists($idroom, $closingd)) {
						$closingd[$idroom] = array();
					}
					foreach ($all_nights as $night) {
						if (in_array($night, $rclosedd)) {
							if (array_key_exists($prec['id'], $closingd[$idroom])) {
								$closingd[$idroom][$prec['id']][] = $night;
							} else {
								$closingd[$idroom][$prec['id']] = array($night);
							}
						}
					}
				}
			}
		}

		return $closingd;
	}

	public static function areTherePayments() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id` FROM `#__vikbooking_gpayments` WHERE `published`='1';";
		$dbo->setQuery($q);
		$dbo->execute();
		return $dbo->getNumRows() > 0 ? true : false;
	}

	public static function getPayment($idp, $vbo_tn = null) {
		if (!empty($idp)) {
			if (strstr($idp, '=') !== false) {
				$parts = explode('=', $idp);
				$idp = $parts[0];
			}
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikbooking_gpayments` WHERE `id`=" . $dbo->quote($idp) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$payment = $dbo->loadAssocList();
				if (is_object($vbo_tn)) {
					$vbo_tn->translateContents($payment, '#__vikbooking_gpayments');
				}
				return $payment[0];
			} else {
				return false;
			}
		}
		return false;
	}

	public static function getCronKey() {
		$dbo = JFactory::getDBO();
		$ckey = '';
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='cronkey';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadAssocList();
			$ckey = $cval[0]['setting'];
		}
		return $ckey;
	}

	public static function getNextInvoiceNumber () {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='invoiceinum';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) + 1);
	}
	
	public static function getInvoiceNumberSuffix () {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='invoicesuffix';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}
	
	public static function getInvoiceCompanyInfo () {
		$dbo = JFactory::getDBO();
		$q="SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='invcompanyinfo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s=$dbo->loadAssocList();
		return $s[0]['setting'];
	}

	/**
	 * Gets the number for the next booking receipt generation,
	 * updates the last receipt number used for the later iterations,
	 * stores a new receipt record to keep track of the receipts issued.
	 *
	 * @param 	int 	$bid 		the Booking ID for which we are/want to generating the receipt.
	 * @param 	[int]	$newnum 	the last number used for generating the receipt.
	 *
	 * @return 	int
	 */
	public static function getNextReceiptNumber ($bid, $newnum = false) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='receiptinum';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			//check if this booking has already a receipt, and return that number
			$prev_receipt = array();
			$q = "SELECT * FROM `#__vikbooking_receipts` WHERE `idorder`=".(int)$bid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$prev_receipt = $dbo->loadAssoc();
			}
			//update value (receipt generated)
			if ($newnum !== false && $newnum > 0) {
				$s = (int)$newnum;
				if (!(count($prev_receipt) > 0)) {
					$q = "UPDATE `#__vikbooking_config` SET `setting`=".$s." WHERE `param`='receiptinum';";
					$dbo->setQuery($q);
					$dbo->execute();
					//insert the new receipt record
					$q = "INSERT INTO `#__vikbooking_receipts` (`number`,`idorder`,`created_on`) VALUES (".(int)$newnum.", ".(int)$bid.", ".time().");";
					$dbo->setQuery($q);
					$dbo->execute();
				} else {
					//update receipt record
					$q = "UPDATE `#__vikbooking_receipts` SET `number`=".(int)$newnum.",`created_on`=".time()." WHERE `idorder`=".(int)$bid.";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
			return count($prev_receipt) > 0 ? (int)$prev_receipt['number'] : ((int)$s + 1);
		}
		//first execution of the method should create the configuration record
		$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('receiptinum', '0');";
		$dbo->setQuery($q);
		$dbo->execute();
		return 1;
	}

	public static function getReceiptNotes ($newnotes = false) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='receiptnotes';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			//update value
			if ($newnotes !== false) {
				$s = $newnotes;
				$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($s)." WHERE `param`='receiptnotes';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			//
			return $s;
		}
		$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('receiptnotes', '');";
		$dbo->setQuery($q);
		$dbo->execute();
		return "";
	}

	public static function loadColorTagsRules() {
		return array(
			0 => 'VBOCOLORTAGRULECUSTOMCOLOR',
			'pend1' => 'VBWAITINGFORPAYMENT',
			'conf1' => 'VBDBTEXTROOMCLOSED',
			'conf2' => 'VBOCOLORTAGRULECONFTWO',
			'conf3' => 'VBOCOLORTAGRULECONFTHREE',
			'inv1' => 'VBOCOLORTAGRULEINVONE',
			'rcp1' => 'VBOCOLORTAGRULERCPONE',
			'conf4' => 'VBOCOLORTAGRULECONFFOUR',
			'conf5' => 'VBOCOLORTAGRULECONFFIVE',
			'inv2' => 'VBOCOLORTAGRULEINVTWO'
		);
	}

	public static function loadDefaultColorTags() {
		return array(
			array(
				'color' => '#9b9b9b',
				'name' => 'VBWAITINGFORPAYMENT',
				'rule' => 'pend1'
			),
			array(
				'color' => '#333333',
				'name' => 'VBDBTEXTROOMCLOSED',
				'rule' => 'conf1'
			),
			array(
				'color' => '#ff8606',
				'name' => 'VBOCOLORTAGRULECONFTWO',
				'rule' => 'conf2'
			),
			array(
				'color' => '#0418c9',
				'name' => 'VBOCOLORTAGRULECONFTHREE',
				'rule' => 'conf3'
			),
			array(
				'color' => '#bed953',
				'name' => 'VBOCOLORTAGRULEINVONE',
				'rule' => 'inv1'
			),
			array(
				'color' => '#67f5b5',
				'name' => 'VBOCOLORTAGRULERCPONE',
				'rule' => 'rcp1'
			),
			array(
				'color' => '#04d2c2',
				'name' => 'VBOCOLORTAGRULECONFFOUR',
				'rule' => 'conf4'
			),
			array(
				'color' => '#00b316',
				'name' => 'VBOCOLORTAGRULECONFFIVE',
				'rule' => 'conf5'
			),
			array(
				'color' => '#00f323',
				'name' => 'VBOCOLORTAGRULEINVTWO',
				'rule' => 'inv2'
			)
		);
	}

	public static function loadBookingsColorTags() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='bookingsctags';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadResult();
			if (!empty($cval)) {
				$arr_tags = json_decode($cval, true);
				if (is_array($arr_tags)) {
					return $arr_tags;
				}
			}
		}
		return self::loadDefaultColorTags();
	}

	public static function getBestColorContrast($hexcolor) {
		$hexcolor = str_replace('#', '', $hexcolor);
		if (empty($hexcolor) || strlen($hexcolor) != 6) {
			return '#000000';
		}
		$r = hexdec(substr($hexcolor, 0, 2));
		$g = hexdec(substr($hexcolor, 2, 2));
		$b = hexdec(substr($hexcolor, 4, 2));
		//Counting the perceptive luminance - human eye favors green color
		// < 0.5 bright colors
		// > 0.5 dark colors
		return (1 - ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255) < 0.5 ? '#000000' : '#ffffff';
	}

	public static function applyBookingColorTag($booking, $tags = array()) {
		if (!is_array($tags) || !(count($tags) > 0)) {
			$tags = self::loadBookingsColorTags();
		}
		if (array_key_exists('colortag', $booking) && !empty($booking['colortag'])) {
			$color_tag_arr = json_decode($booking['colortag'], true);
			if (is_array($color_tag_arr) && array_key_exists('color', $color_tag_arr)) {
				$color_tag_arr['fontcolor'] = self::getBestColorContrast($color_tag_arr['color']);
				return $color_tag_arr;
			}
		}
		$dbo = JFactory::getDBO();
		$bid = array_key_exists('idorder', $booking) ? $booking['idorder'] : $booking['id'];
		$invoice_numb = false;
		$receipt_numb = false;
		if ($booking['status'] == 'confirmed') {
			$q = "SELECT `b`.`id` AS `o_id`, `i`.`id` AS `inv_id`, `r`.`id` AS `rcp_id` FROM `#__vikbooking_orders` AS `b` LEFT JOIN `#__vikbooking_invoices` AS `i` ON `b`.`id`=`i`.`idorder` LEFT JOIN `#__vikbooking_receipts` AS `r` ON `b`.`id`=`r`.`idorder` WHERE `b`.`id`=".(int)$bid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$docs_data = $dbo->loadAssoc();
				$invoice_numb = (!empty($docs_data['inv_id']));
				$receipt_numb = (!empty($docs_data['rcp_id']));
			}
		}
		foreach ($tags as $tkey => $tval) {
			if (empty($tval['rule'])) {
				continue;
			}
			switch ($tval['rule']) {
				case 'pend1':
					//Room is waiting for the payment (locked record)
					if ($booking['status'] == 'standby') {
						$q = "SELECT `id` FROM `#__vikbooking_tmplock` WHERE `idorder`=".(int)$bid." AND `until`>=".time().";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$tval['fontcolor'] = self::getBestColorContrast($tval['color']);
							return $tval;
						}
					}
					break;
				case 'conf1':
					//Confirmed (Room Closed)
					if ($booking['status'] == 'confirmed' && $booking['custdata'] == JText::_('VBDBTEXTROOMCLOSED')) {
						$tval['fontcolor'] = self::getBestColorContrast($tval['color']);
						return $tval;
					}
					break;
				case 'conf2':
					//Confirmed (No Rate 0.00/NULL Total)
					if ($booking['status'] == 'confirmed' && (empty($booking['total']) || $booking['total'] <= 0.00 || $booking['total'] === null)) {
						$tval['fontcolor'] = self::getBestColorContrast($tval['color']);
						return $tval;
					}
					break;
				case 'conf3':
					//Confirmed (Total > 0 && Total Paid = 0 && No Invoice && No Receipt)
					if ($booking['status'] == 'confirmed' && $booking['total'] > 0 && (empty($booking['totpaid']) || $booking['totpaid'] <= 0.00 || $booking['totpaid'] === null) && $invoice_numb === false && $receipt_numb === false) {
						$tval['fontcolor'] = self::getBestColorContrast($tval['color']);
						return $tval;
					}
					break;
				case 'inv1':
					//Confirmed + Invoice (Total > 0 && Total Paid = 0 && Invoice Exists)
					if ($booking['status'] == 'confirmed' && $booking['total'] > 0 && (empty($booking['totpaid']) || $booking['totpaid'] <= 0.00 || $booking['totpaid'] === null) && $invoice_numb !== false) {
						$tval['fontcolor'] = self::getBestColorContrast($tval['color']);
						return $tval;
					}
					break;
				case 'rcp1':
					//Confirmed + Receipt Issued (Total > 0 && Total Paid = 0 && Receipt Issued)
					if ($booking['status'] == 'confirmed' && $booking['total'] > 0 && (empty($booking['totpaid']) || $booking['totpaid'] <= 0.00 || $booking['totpaid'] === null) && $receipt_numb !== false) {
						$tval['fontcolor'] = self::getBestColorContrast($tval['color']);
						return $tval;
					}
					break;
				case 'conf4':
					//Confirmed (Total > 0 && Total Paid > 0 && Total Paid < Total)
					if ($booking['status'] == 'confirmed' && $booking['total'] > 0 && $booking['totpaid'] > 0 && $booking['totpaid'] < $booking['total']) {
						$tval['fontcolor'] = self::getBestColorContrast($tval['color']);
						return $tval;
					}
					break;
				case 'conf5':
					//Confirmed (Total > 0 && Total Paid >= Total)
					if ($booking['status'] == 'confirmed' && $booking['total'] > 0 && $booking['totpaid'] > 0 && $booking['totpaid'] >= $booking['total']) {
						$tval['fontcolor'] = self::getBestColorContrast($tval['color']);
						return $tval;
					}
					break;
				case 'inv2':
					//Confirmed + Invoice + Paid (Total > 0 && Total Paid >= Total && Invoice Exists)
					if ($booking['status'] == 'confirmed' && $booking['total'] > 0 && $booking['totpaid'] > 0 && $booking['totpaid'] >= $booking['total'] && $invoice_numb !== false) {
						$tval['fontcolor'] = self::getBestColorContrast($tval['color']);
						return $tval;
					}
					break;
				default:
					break;
			}
		}
		return array();
	}

	public static function getBookingInfoFromID($bid) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$bid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking_info = $dbo->loadAssoc();
			return $booking_info;
		}
		return array();
	}

	public static function loadRoomIndexesBookings($roomid, $room_bids_pool) {
		$dbo = JFactory::getDBO();
		$room_features_bookings = array();
		if (!empty($roomid) && count($room_bids_pool) > 0) {
			$all_bids = array();
			foreach ($room_bids_pool as $day => $bids) {
				$all_bids = array_merge($all_bids, $bids);
			}
			$all_bids = array_unique($all_bids);
			$q = "SELECT `id`,`idorder`,`roomindex` FROM `#__vikbooking_ordersrooms` WHERE `idroom`=".(int)$roomid." AND `idorder` IN (".implode(', ', $all_bids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rbookings = $dbo->loadAssocList();
				foreach ($rbookings as $k => $v) {
					if (empty($v['roomindex'])) {
						continue;
					}
					if (!array_key_exists($v['roomindex'], $room_features_bookings)) {
						$room_features_bookings[$v['roomindex']] = array();
					}
					$room_features_bookings[$v['roomindex']][] = $v['idorder'];
				}
			}
		}

		return $room_features_bookings;
	}

	public static function getSendEmailWhen() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='emailsendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadAssocList();
			return intval($cval[0]['setting']) > 1 ? 2 : 1;
		} else {
			$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('emailsendwhen','1');";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		return 1;
	}

	public static function getMinutesAutoRemove() {
		$dbo = JFactory::getDBO();
		$minar = 0;
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='minautoremove';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$minar = (int)$dbo->loadResult();
		}
		return $minar;
	}

	public static function getSMSAPIClass() {
		$dbo = JFactory::getDBO();
		$cfile = '';
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='smsapi';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadAssocList();
			$cfile = $cval[0]['setting'];
		}
		return $cfile;
	}

	public static function autoSendSMSEnabled() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='smsautosend';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadAssocList();
			return intval($cval[0]['setting']) > 0 ? true : false;
		}
		return false;
	}

	public static function getSendSMSTo() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='smssendto';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadAssocList();
			if (!empty($cval[0]['setting'])) {
				$sto = json_decode($cval[0]['setting'], true);
				if (is_array($sto)) {
					return $sto;
				}
			}
		}
		return array();
	}

	public static function getSendSMSWhen() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='smssendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadAssocList();
			return intval($cval[0]['setting']) > 1 ? 2 : 1;
		}
		return 1;
	}

	public static function getSMSAdminPhone() {
		$dbo = JFactory::getDBO();
		$pnum = '';
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='smsadminphone';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadAssocList();
			$pnum = $cval[0]['setting'];
		}
		return $pnum;
	}

	public static function getSMSParams($as_array = true) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='smsparams';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadAssocList();
			if (!empty($cval[0]['setting'])) {
				if (!$as_array) {
					return $cval[0]['setting'];
				}
				$sparams = json_decode($cval[0]['setting'], true);
				if (is_array($sparams)) {
					return $sparams;
				}
			}
		}
		return array();
	}

	public static function getSMSTemplate($vbo_tn = null, $booking_status = 'confirmed', $type = 'admin') {
		$dbo = JFactory::getDBO();
		switch (strtolower($booking_status)) {
			case 'standby':
				$status = 'pend';
				break;
			case 'cancelled':
				$status = 'canc';
				break;
			default:
				$status = '';
				break;
		}
		$paramtype = 'sms'.$type.'tpl'.$status;
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='".$paramtype."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			if ($status == 'canc') {
				//Type cancelled is used by VCM since v1.6.6
				$q = "INSERT INTO `#__vikbooking_texts` (`param`,`exp`,`setting`) VALUES ('".$paramtype."','".($type == 'admin' ? 'Administrator' : 'Customer')." SMS Template (Cancelled)','');";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			return '';
		}
		$ft = $dbo->loadAssocList();
		if (is_object($vbo_tn)) {
			$vbo_tn->translateContents($ft, '#__vikbooking_texts');
		}
		return $ft[0]['setting'];
	}

	public static function getSMSAdminTemplate($vbo_tn = null, $booking_status = 'confirmed') {
		return self::getSMSTemplate($vbo_tn, $booking_status, 'admin');
	}

	public static function getSMSCustomerTemplate($vbo_tn = null, $booking_status = 'confirmed') {
		return self::getSMSTemplate($vbo_tn, $booking_status, 'customer');
	}

	public static function checkPhonePrefixCountry($phone, $country_threecode) {
		$dbo = JFactory::getDBO();
		$phone = str_replace(" ", '', trim($phone));
		$cprefix = '';
		if (!empty($country_threecode)) {
			$q = "SELECT `phone_prefix` FROM `#__vikbooking_countries` WHERE `country_3_code`=".$dbo->quote($country_threecode).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$cprefix = $dbo->loadResult();
				$cprefix = str_replace(" ", '', trim($cprefix));
			}
		}
		if (!empty($cprefix)) {
			if (substr($phone, 0, 1) != '+') {
				if (substr($phone, 0, 2) == '00') {
					$phone = '+'.substr($phone, 2);
				} else {
					$phone = $cprefix.$phone;
				}
			}
		}
		return $phone;
	}

	public static function parseAdminSMSTemplate($booking, $booking_rooms, $vbo_tn = null) {
		$tpl = self::getSMSAdminTemplate($vbo_tn, $booking['status']);
		$vbo_df = self::getDateFormat();
		$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y-m-d');
		$datesep = self::getDateSeparator();
		$tpl = str_replace('{customer_name}', $booking['customer_name'], $tpl);
		$tpl = str_replace('{booking_id}', $booking['id'], $tpl);
		$tpl = str_replace('{checkin_date}', date(str_replace("/", $datesep, $df), $booking['checkin']), $tpl);
		$tpl = str_replace('{checkout_date}', date(str_replace("/", $datesep, $df), $booking['checkout']), $tpl);
		$tpl = str_replace('{num_nights}', $booking['days'], $tpl);
		$rooms_booked = array();
		$rooms_names = array();
		$tot_adults = 0;
		$tot_children = 0;
		$tot_guests = 0;
		foreach ($booking_rooms as $broom) {
			$rooms_names[] = $broom['room_name'];
			if (array_key_exists($broom['room_name'], $rooms_booked)) {
				$rooms_booked[$broom['room_name']] += 1;
			} else {
				$rooms_booked[$broom['room_name']] = 1;
			}
			$tot_adults += (int)$broom['adults'];
			$tot_children += (int)$broom['children'];
			$tot_guests += ((int)$broom['adults'] + (int)$broom['children']);
		}
		$tpl = str_replace('{tot_adults}', $tot_adults, $tpl);
		$tpl = str_replace('{tot_children}', $tot_children, $tpl);
		$tpl = str_replace('{tot_guests}', $tot_guests, $tpl);
		$rooms_booked_quant = array();
		foreach ($rooms_booked as $rname => $quant) {
			$rooms_booked_quant[] = ($quant > 1 ? $quant.' ' : '').$rname;
		}
		$tpl = str_replace('{rooms_booked}', implode(', ', $rooms_booked_quant), $tpl);
		$tpl = str_replace('{rooms_names}', implode(', ', $rooms_names), $tpl);
		$tpl = str_replace('{customer_country}', $booking['country_name'], $tpl);
		$tpl = str_replace('{customer_email}', $booking['custmail'], $tpl);
		$tpl = str_replace('{customer_phone}', $booking['phone'], $tpl);
		$tpl = str_replace('{total}', self::numberFormat($booking['total']), $tpl);
		$tpl = str_replace('{total_paid}', self::numberFormat($booking['totpaid']), $tpl);
		$remaining_bal = $booking['total'] - $booking['totpaid'];
		$tpl = str_replace('{remaining_balance}', self::numberFormat($remaining_bal), $tpl);

		return $tpl;
	}

	public static function parseCustomerSMSTemplate($booking, $booking_rooms, $vbo_tn = null, $force_text = null) {
		$tpl = !empty($force_text) ? $force_text : self::getSMSCustomerTemplate($vbo_tn, $booking['status']);
		$vbo_df = self::getDateFormat();
		$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y-m-d');
		$datesep = self::getDateSeparator();
		$tpl = str_replace('{customer_name}', $booking['customer_name'], $tpl);
		$tpl = str_replace('{booking_id}', $booking['id'], $tpl);
		$tpl = str_replace('{checkin_date}', date(str_replace("/", $datesep, $df), $booking['checkin']), $tpl);
		$tpl = str_replace('{checkout_date}', date(str_replace("/", $datesep, $df), $booking['checkout']), $tpl);
		$tpl = str_replace('{num_nights}', $booking['days'], $tpl);
		$rooms_booked = array();
		$rooms_names = array();
		$tot_adults = 0;
		$tot_children = 0;
		$tot_guests = 0;
		foreach ($booking_rooms as $broom) {
			$rooms_names[] = $broom['room_name'];
			if (array_key_exists($broom['room_name'], $rooms_booked)) {
				$rooms_booked[$broom['room_name']] += 1;
			} else {
				$rooms_booked[$broom['room_name']] = 1;
			}
			$tot_adults += (int)$broom['adults'];
			$tot_children += (int)$broom['children'];
			$tot_guests += ((int)$broom['adults'] + (int)$broom['children']);
		}
		$tpl = str_replace('{tot_adults}', $tot_adults, $tpl);
		$tpl = str_replace('{tot_children}', $tot_children, $tpl);
		$tpl = str_replace('{tot_guests}', $tot_guests, $tpl);
		$rooms_booked_quant = array();
		foreach ($rooms_booked as $rname => $quant) {
			$rooms_booked_quant[] = ($quant > 1 ? $quant.' ' : '').$rname;
		}
		$tpl = str_replace('{rooms_booked}', implode(', ', $rooms_booked_quant), $tpl);
		$tpl = str_replace('{rooms_names}', implode(', ', $rooms_names), $tpl);
		$tpl = str_replace('{total}', self::numberFormat($booking['total']), $tpl);
		$tpl = str_replace('{total_paid}', self::numberFormat($booking['totpaid']), $tpl);
		$remaining_bal = $booking['total'] - $booking['totpaid'];
		$tpl = str_replace('{remaining_balance}', self::numberFormat($remaining_bal), $tpl);
		$tpl = str_replace('{customer_pin}', $booking['customer_pin'], $tpl);
		$book_link = JURI::root().'index.php?option=com_vikbooking&task=vieworder&sid='.$booking['sid'].'&ts='.$booking['ts'];
		$tpl = str_replace('{booking_link}', $book_link, $tpl);

		return $tpl;
	}

	public static function sendBookingSMS($oid, $skip_send_to = array(), $force_send_to = array(), $force_text = null) {
		$dbo = JFactory::getDBO();
		if (!class_exists('VboApplication')) {
			require_once(VBO_ADMIN_PATH.DS.'helpers'.DS.'jv_helper.php');
		}
		$vbo_app = new VboApplication;
		if (empty($oid)) {
			return false;
		}
		$sms_api = self::getSMSAPIClass();
		if (empty($sms_api)) {
			return false;
		}
		if (!is_file(VBO_ADMIN_PATH.DS.'smsapi'.DS.$sms_api)) {
			return false;
		}
		$sms_api_params = self::getSMSParams();
		if (!is_array($sms_api_params) || !(count($sms_api_params) > 0)) {
			return false;
		}
		if (!self::autoSendSMSEnabled() && !(count($force_send_to) > 0)) {
			return false;
		}
		$send_sms_to = self::getSendSMSTo();
		if (!(count($send_sms_to) > 0) && !(count($force_send_to) > 0)) {
			return false;
		}
		$booking = array();
		$q = "SELECT `o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idorder`=`o`.`id` AND `co`.`idorder`=".(int)$oid." LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikbooking_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `o`.`id`=".(int)$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking = $dbo->loadAssoc();
		}
		if (!(count($booking) > 0)) {
			return false;
		}
		if (strtolower($booking['status']) == 'standby' && self::getSendSMSWhen() < 2) {
			return false;
		}
		$booking_rooms = array();
		$q = "SELECT `or`.*,`r`.`name` AS `room_name` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`or`.`idroom` WHERE `or`.`idorder`=".(int)$booking['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking_rooms = $dbo->loadAssocList();
		}
		$admin_phone = self::getSMSAdminPhone();
		$admin_sendermail = self::getSenderMail();
		$admin_email = self::getAdminMail();
		$f_result = false;
		require_once(VBO_ADMIN_PATH.DS.'smsapi'.DS.$sms_api);
		if ((in_array('admin', $send_sms_to) && !empty($admin_phone) && !in_array('admin', $skip_send_to)) || in_array('admin', $force_send_to)) {
			//SMS for the administrator
			$sms_text = self::parseAdminSMSTemplate($booking, $booking_rooms);
			if (!empty($sms_text)) {
				$sms_obj = new VikSmsApi($booking, $sms_api_params);
				//administrator phone can contain multiple numbers separated by comma or semicolon
				$admin_phones = array();
				if (strpos($admin_phone, ',') !== false) {
					$all_phones = explode(',', $admin_phone);
					foreach ($all_phones as $aph) {
						if (!empty($aph)) {
							$admin_phones[] = trim($aph);
						}
					}
				} elseif (strpos($admin_phone, ';') !== false) {
					$all_phones = explode(';', $admin_phone);
					foreach ($all_phones as $aph) {
						if (!empty($aph)) {
							$admin_phones[] = trim($aph);
						}
					}
				} else {
					$admin_phones[] = $admin_phone;
				}
				foreach ($admin_phones as $admphone) {
					$response_obj = $sms_obj->sendMessage($admphone, $sms_text);
					if ( !$sms_obj->validateResponse($response_obj) ) {
						//notify the administrator via email with the error of the SMS sending
						$vbo_app->sendMail($admin_sendermail, $admin_sendermail, $admin_email, $admin_sendermail, JText::_('VBOSENDSMSERRMAILSUBJ'), JText::_('VBOSENDADMINSMSERRMAILTXT')."<br />".$sms_obj->getLog(), true);
					} else {
						$f_result = true;
					}
				}
			}
		}
		if ((in_array('customer', $send_sms_to) && !empty($booking['phone']) && !in_array('customer', $skip_send_to)) || in_array('customer', $force_send_to)) {
			//SMS for the Customer
			$vbo_tn = self::getTranslator();
			$vbo_tn->translateContents($booking_rooms, '#__vikbooking_rooms', array('id' => 'idroom', 'room_name' => 'name'));
			$sms_text = self::parseCustomerSMSTemplate($booking, $booking_rooms, $vbo_tn, $force_text);
			if (!empty($sms_text)) {
				$sms_obj = new VikSmsApi($booking, $sms_api_params);
				$response_obj = $sms_obj->sendMessage($booking['phone'], $sms_text);
				if ( !$sms_obj->validateResponse($response_obj) ) {
					//notify the administrator via email with the error of the SMS sending
					$vbo_app->sendMail($admin_sendermail, $admin_sendermail, $admin_email, $admin_sendermail, JText::_('VBOSENDSMSERRMAILSUBJ'), JText::_('VBOSENDCUSTOMERSMSERRMAILTXT')."<br />".$sms_obj->getLog(), true);
				} else {
					$f_result = true;
				}
			}
		}
		return $f_result;
	}

	public static function loadInvoiceTmpl ($booking_info = array(), $booking_rooms = array()) {
		define('_VIKBOOKINGEXEC', '1');
		ob_start();
		include VBO_SITE_PATH . DS . "helpers" . DS . "invoices" . DS ."invoice_tmpl.php";
		$content = ob_get_contents();
		ob_end_clean();
		$default_params = array(
			'show_header' => 0,
			'header_data' => array(),
			'show_footer' => 0,
			'pdf_page_orientation' => 'PDF_PAGE_ORIENTATION',
			'pdf_unit' => 'PDF_UNIT',
			'pdf_page_format' => 'PDF_PAGE_FORMAT',
			'pdf_margin_left' => 'PDF_MARGIN_LEFT',
			'pdf_margin_top' => 'PDF_MARGIN_TOP',
			'pdf_margin_right' => 'PDF_MARGIN_RIGHT',
			'pdf_margin_header' => 'PDF_MARGIN_HEADER',
			'pdf_margin_footer' => 'PDF_MARGIN_FOOTER',
			'pdf_margin_bottom' => 'PDF_MARGIN_BOTTOM',
			'pdf_image_scale_ratio' => 'PDF_IMAGE_SCALE_RATIO',
			'header_font_size' => '10',
			'body_font_size' => '10',
			'footer_font_size' => '8'
		);
		if (defined('_VIKBOOKING_INVOICE_PARAMS') && isset($invoice_params) && @count($invoice_params) > 0) {
			$default_params = array_merge($default_params, $invoice_params);
		}
		return array($content, $default_params);
	}

	public static function parseInvoiceTemplate($invoicetpl, $booking, $booking_rooms, $invoice_num, $invoice_suff, $invoice_date, $company_info, $vbo_tn = null, $is_front = false) {
		$parsed = $invoicetpl;
		$dbo = JFactory::getDBO();
		if (is_null($vbo_tn)) {
			$vbo_tn = self::getTranslator();
		}
		$nowdf = self::getDateFormat();
		if ($nowdf=="%d/%m/%Y") {
			$df='d/m/Y';
		} elseif ($nowdf=="%m/%d/%Y") {
			$df='m/d/Y';
		} else {
			$df='Y/m/d';
		}
		$datesep = self::getDateSeparator();
		$companylogo = self::getSiteLogo();
		$uselogo = '';
		if (!empty($companylogo)) {
			$uselogo = '<img src="'.($is_front ? VBO_ADMIN_URI_REL : VBO_SITE_URI_REL).'resources/'.$companylogo.'"/>';
		}
		$parsed = str_replace("{company_logo}", $uselogo, $parsed);
		$parsed = str_replace("{company_info}", $company_info, $parsed);
		$parsed = str_replace("{invoice_number}", $invoice_num, $parsed);
		$parsed = str_replace("{invoice_suffix}", $invoice_suff, $parsed);
		$parsed = str_replace("{invoice_date}", $invoice_date, $parsed);
		$parsed = str_replace("{customer_info}", nl2br(rtrim($booking['custdata'], "\n")), $parsed);
		//custom fields replace
		preg_match_all('/\{customfield ([0-9]+)\}/U', $parsed, $cmatches);
		if (is_array($cmatches[1]) && @count($cmatches[1]) > 0) {
			$cfids = array();
			foreach($cmatches[1] as $cfid ){
				$cfids[] = $cfid;
			}
			$q = "SELECT * FROM `#__vikbooking_custfields` WHERE `id` IN (".implode(", ", $cfids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
			$vbo_tn->translateContents($cfields, '#__vikbooking_custfields');
			$cfmap = array();
			if (is_array($cfields)) {
				foreach($cfields as $cf) {
					$cfmap[trim(JText::_($cf['name']))] = $cf['id'];
				}
			}
			$cfmapreplace = array();
			$partsreceived = explode("\n", $booking['custdata']);
			if (count($partsreceived) > 0) {
				foreach($partsreceived as $pst) {
					if (!empty($pst)) {
						$tmpdata = explode(":", $pst);
						if (array_key_exists(trim($tmpdata[0]), $cfmap)) {
							$cfmapreplace[$cfmap[trim($tmpdata[0])]] = trim($tmpdata[1]);
						}
					}
				}
			}
			foreach($cmatches[1] as $cfid ){
				if (array_key_exists($cfid, $cfmapreplace)) {
					$parsed = str_replace("{customfield ".$cfid."}", $cfmapreplace[$cfid], $parsed);
				} else {
					$parsed = str_replace("{customfield ".$cfid."}", "", $parsed);
				}
			}
		}
		//end custom fields replace
		//invoice price description - Start
		$rooms = array();
		$tars = array();
		$arrpeople = array();
		$is_package = !empty($booking['pkg']) ? true : false;
		$tot_adults = 0;
		$tot_children = 0;
		$tot_guests = 0;
		foreach($booking_rooms as $kor => $or) {
			$num = $kor + 1;
			$rooms[$num] = $or;
			$arrpeople[$num]['adults'] = $or['adults'];
			$arrpeople[$num]['children'] = $or['children'];
			$tot_adults += $or['adults'];
			$tot_children += $or['children'];
			$tot_guests += ($or['adults'] + $or['children']);
			if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
				//package or custom cost set from the back-end
				continue;
			}
			$q="SELECT * FROM `#__vikbooking_dispcost` WHERE `id`='".$or['idtar']."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tar = $dbo->loadAssocList();
				$tar = self::applySeasonsRoom($tar, $booking['checkin'], $booking['checkout']);
				//different usage
				if ($or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
					$diffusageprice = self::loadAdultsDiff($or['idroom'], $or['adults']);
					//Occupancy Override
					$occ_ovr = self::occupancyOverrideExists($tar, $or['adults']);
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
		$parsed = str_replace("{checkin_date}", date(str_replace("/", $datesep, $df), $booking['checkin']), $parsed);
		$parsed = str_replace("{checkout_date}", date(str_replace("/", $datesep, $df), $booking['checkout']), $parsed);
		$parsed = str_replace("{num_nights}", $booking['days'], $parsed);
		$parsed = str_replace("{tot_guests}", $tot_guests, $parsed);
		$parsed = str_replace("{tot_adults}", $tot_adults, $parsed);
		$parsed = str_replace("{tot_children}", $tot_children, $parsed);
		$isdue = 0;
		$tot_taxes = 0;
		$tot_city_taxes = 0;
		$tot_fees = 0;
		$pricestr = array();
		$optstr = array();
		foreach ($booking_rooms as $kor => $or) {
			$num = $kor + 1;
			$pricestr[$num] = array();
			if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
				//package cost or cust_cost should always be inclusive of taxes
				$calctar = $or['cust_cost'];
				$pricestr[$num]['name'] = (!empty($or['pkg_name']) ? $or['pkg_name'] : (!empty($or['otarplan']) ? ucwords($or['otarplan']) : JText::_('VBOROOMCUSTRATEPLAN')));
				$pricestr[$num]['tot'] = $calctar;
				$pricestr[$num]['tax'] = 0;
				$isdue += $calctar;
				if ($calctar == $or['cust_cost']) {
					//April 2017 - force third parameter to 'true' for prices tax excluded
					$cost_minus_tax = self::sayPackageMinusIva($or['cust_cost'], $or['cust_idiva'], true);
					//
					$tot_taxes += ($or['cust_cost'] - $cost_minus_tax);
					$pricestr[$num]['tax'] = ($or['cust_cost'] - $cost_minus_tax);
				} else {
					$tot_taxes += ($calctar - $or['cust_cost']);
				}
			} elseif (array_key_exists($num, $tars) && is_array($tars[$num])) {
				$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
				$calctar = self::sayCostPlusIva($display_rate, $tars[$num]['idprice']);
				$pricestr[$num]['name'] = self::getPriceName($tars[$num]['idprice'], $vbo_tn) . (!empty($tars[$num]['attrdata']) ? "\n" . self::getPriceAttr($tars[$num]['idprice'], $vbo_tn) . ": " . $tars[$num]['attrdata'] : "");
				$pricestr[$num]['tot'] = $calctar;
				$tars[$num]['calctar'] = $calctar;
				$isdue += $calctar;
				if ($calctar == $display_rate) {
					$cost_minus_tax = self::sayCostMinusIva($display_rate, $tars[$num]['idprice']);
					$tot_taxes += ($display_rate - $cost_minus_tax);
					$pricestr[$num]['tax'] = ($display_rate - $cost_minus_tax);
				} else {
					$tot_taxes += ($calctar - $display_rate);
					$pricestr[$num]['tax'] = ($calctar - $display_rate);
				}
			}
			$optstr[$num] = array();
			$opt_ind = 0;
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
							if (is_object($vbo_tn)) {
								$vbo_tn->translateContents($actopt, '#__vikbooking_optionals');
							}
							$optstr[$num][$opt_ind] = array();
							$chvar = '';
							if (!empty($actopt[0]['ageintervals']) && $or['children'] > 0 && strstr($stept[1], '-') != false) {
								$optagecosts = self::getOptionIntervalsCosts($actopt[0]['ageintervals']);
								$optagenames = self::getOptionIntervalsAges($actopt[0]['ageintervals']);
								$optagepcent = self::getOptionIntervalsPercentage($actopt[0]['ageintervals']);
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
								$actopt[0]['chageintv'] = $chvar;
								$actopt[0]['name'] .= ' ('.$optagenames[($chvar - 1)].')';
								$actopt[0]['quan'] = $stept[1];
								$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $booking['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
							} else {
								$actopt[0]['quan'] = $stept[1];
								$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $booking['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
							}
							if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
								$realcost = $actopt[0]['maxprice'];
								if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
									$realcost = $actopt[0]['maxprice'] * $stept[1];
								}
							}
							if ($actopt[0]['perperson'] == 1) {
								$realcost = $realcost * $or['adults'];
							}
							$tmpopr = self::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
							$optstr[$num][$opt_ind]['name'] = ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'];
							$optstr[$num][$opt_ind]['tot'] = $tmpopr;
							$optstr[$num][$opt_ind]['tax'] = 0;
							if ($actopt[0]['is_citytax'] == 1) {
								$tot_city_taxes += $tmpopr;
							} elseif ($actopt[0]['is_fee'] == 1) {
								$tot_fees += $tmpopr;
							} else {
								if ($tmpopr == $realcost) {
									$opt_minus_tax = self::sayOptionalsMinusIva($realcost, $actopt[0]['idiva']);
									$tot_taxes += ($realcost - $opt_minus_tax);
									$optstr[$num][$opt_ind]['tax'] = ($realcost - $opt_minus_tax);
								} else {
									$tot_taxes += ($tmpopr - $realcost);
									$optstr[$num][$opt_ind]['tax'] = ($tmpopr - $realcost);
								}
							}
							$opt_ind++;
							$isdue += $tmpopr;
						}
					}
				}
			}
			//custom extra costs
			if (!empty($or['extracosts'])) {
				$cur_extra_costs = json_decode($or['extracosts'], true);
				foreach ($cur_extra_costs as $eck => $ecv) {
					$ecplustax = !empty($ecv['idtax']) ? self::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
					$isdue += $ecplustax;
					$optstr[$num][$opt_ind]['name'] = $ecv['name'];
					$optstr[$num][$opt_ind]['tot'] = $ecplustax;
					$optstr[$num][$opt_ind]['tax'] = 0;
					if ($ecplustax == $ecv['cost']) {
						$ec_minus_tax = !empty($ecv['idtax']) ? self::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
						$tot_taxes += ($ecv['cost'] - $ec_minus_tax);
						$optstr[$num][$opt_ind]['tax'] = ($ecv['cost'] - $ec_minus_tax);
					} else {
						$tot_taxes += ($ecplustax - $ecv['cost']);
						$optstr[$num][$opt_ind]['tax'] = ($ecplustax - $ecv['cost']);
					}
					$opt_ind++;
				}
			}
			//
		}
		$usedcoupon = false;
		if (strlen($booking['coupon']) > 0) {
			$orig_isdue = $isdue;
			$expcoupon = explode(";", $booking['coupon']);
			$usedcoupon = $expcoupon;
			$isdue = $isdue - (float)$expcoupon[1];
			if ($isdue != $orig_isdue) {
				//lower taxes proportionally
				$tot_taxes = $isdue * $tot_taxes / $orig_isdue;
			}
		}
		$rows_written = 0;
		$inv_rows = '';
		foreach ($pricestr as $num => $price_descr) {
			$inv_rows .= '<tr>'."\n";
			$inv_rows .= '<td>'.$rooms[$num]['room_name'].'<br/>'.nl2br(rtrim($price_descr['name'], "\n")).'</td>'."\n";
			$inv_rows .= '<td>'.$booking['currencyname'].' '.self::numberformat(($price_descr['tot'] - $price_descr['tax'])).'</td>'."\n";
			$inv_rows .= '<td>'.$booking['currencyname'].' '.self::numberformat($price_descr['tax']).'</td>'."\n";
			$inv_rows .= '<td>'.$booking['currencyname'].' '.self::numberformat($price_descr['tot']).'</td>'."\n";
			$inv_rows .= '</tr>'."\n";
			$rows_written++;
			if (array_key_exists($num, $optstr) && count($optstr[$num]) > 0) {
				foreach ($optstr[$num] as $optk => $optv) {
					$inv_rows .= '<tr>'."\n";
					$inv_rows .= '<td>'.$optv['name'].'</td>'."\n";
					$inv_rows .= '<td>'.$booking['currencyname'].' '.self::numberformat(($optv['tot'] - $optv['tax'])).'</td>'."\n";
					$inv_rows .= '<td>'.$booking['currencyname'].' '.self::numberformat($optv['tax']).'</td>'."\n";
					$inv_rows .= '<td>'.$booking['currencyname'].' '.self::numberformat($optv['tot']).'</td>'."\n";
					$inv_rows .= '</tr>'."\n";
					$rows_written++;
				}
			}
		}
		//if discount print row
		if ($usedcoupon !== false) {
			$inv_rows .= '<tr>'."\n";
			$inv_rows .= '<td></td><td></td><td></td><td></td>'."\n";
			$inv_rows .= '</tr>'."\n";
			$inv_rows .= '<tr>'."\n";
			$inv_rows .= '<td>'.$usedcoupon[2].'</td>'."\n";
			$inv_rows .= '<td></td>'."\n";
			$inv_rows .= '<td></td>'."\n";
			$inv_rows .= '<td>- '.$booking['currencyname'].' '.self::numberformat($usedcoupon[1]).'</td>'."\n";
			$inv_rows .= '</tr>'."\n";
			$rows_written += 2;
		}
		//
		$min_records = 10;
		if ($rows_written < $min_records) {
			for ($i=1; $i <= ($min_records - $rows_written); $i++) { 
				$inv_rows .= '<tr>'."\n";
				$inv_rows .= '<td></td>'."\n";
				$inv_rows .= '<td></td>'."\n";
				$inv_rows .= '<td></td>'."\n";
				$inv_rows .= '</tr>'."\n";
			}
		}
		//invoice price description - End
		$parsed = str_replace("{invoice_products_descriptions}", $inv_rows, $parsed);
		$parsed = str_replace("{invoice_totalnet}", $booking['currencyname'].' '.self::numberformat(($isdue - $tot_taxes)), $parsed);
		$parsed = str_replace("{invoice_totaltax}", $booking['currencyname'].' '.self::numberformat($tot_taxes), $parsed);
		$parsed = str_replace("{invoice_grandtotal}", $booking['currencyname'].' '.self::numberformat($isdue), $parsed);
		$parsed = str_replace("{inv_notes}", $booking['inv_notes'], $parsed);

		return $parsed;
	}

	public static function generateBookingInvoice($booking, $invoice_num = 0, $invoice_suff = '', $invoice_date = '', $company_info = '', $translate = false, $is_front = false) {
		$invoice_num = empty($invoice_num) ? self::getNextInvoiceNumber() : $invoice_num;
		$invoice_suff = empty($invoice_suff) ? self::getInvoiceNumberSuffix() : $invoice_suff;
		$company_info = empty($company_info) ? self::getInvoiceCompanyInfo() : $company_info;
		if (!(count($booking) > 0)) {
			return false;
		}
		if (!($booking['total'] > 0)) {
			return false;
		}
		$dbo = JFactory::getDBO();
		$vbo_tn = self::getTranslator();
		$currencyname = self::getCurrencyName();
		$booking['currencyname'] = $currencyname;
		$nowdf = self::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$datesep = self::getDateSeparator(true);
		if (empty($invoice_date)) {
			$invoice_date = date(str_replace("/", $datesep, $df), $booking['ts']);
			$used_date = $booking['ts'];
		} else {
			/**
			 * We could be re-generating an invoice for a booking that already had a invoice.
			 * In order to modify some entries in the invoice, the whole PDF is re-generated.
			 * It is now possible to keep the same invoice date as the previous one, so check
			 * what value contains $invoice_date to see if it's different from today's date.
			 * The cron jobs may be calling this method with a $invoice_date = 1, so we need
			 * to also check the length of the string $invoice_date before using that date.
			 * 
			 * @since 	1.10 - August 2018
			 */
			$base_ts = time();
			if (date($df, $base_ts) != $invoice_date && strlen($invoice_date) >= 6) {
				$base_ts = self::getDateTimestamp($invoice_date, 0, 0);
			}
			$invoice_date = date(str_replace("/", $datesep, $df), $base_ts);
			$used_date = $base_ts;
			//
		}
		$booking_rooms = array();
		$q = "SELECT `or`.*,`r`.`name` AS `room_name`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`or`.`idroom` WHERE `or`.`idorder`=".(int)$booking['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking_rooms = $dbo->loadAssocList();
		}
		if (!(count($booking_rooms) > 0)) {
			return false;
		}
		//Translations for the invoices are disabled by default as well as the language definitions for the customer language
		if ($translate === true) {
			if (!empty($booking['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $booking['lang']) {
					$lang->load('com_vikbooking', JPATH_SITE, $booking['lang'], true);
					$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $booking['lang'], true);
					$vbo_tn::$force_tolang = $booking['lang'];
				}
				$vbo_tn->translateContents($booking_rooms, '#__vikbooking_rooms', array('id' => 'idroom', 'room_name' => 'name'), array(), $booking['lang']);
			}
		}
		//
		if (!class_exists('TCPDF')) {
			require_once(VBO_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
		}
		$usepdffont = file_exists(VBO_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php") ? 'dejavusans' : 'helvetica';
		//vikbooking 1.8 - set array variable to the template file
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`='".(int)$booking['id']."';";
		$dbo->setQuery($q);
		$dbo->execute();
		$booking_info = $dbo->loadAssoc();
		list($invoicetpl, $pdfparams) = self::loadInvoiceTmpl($booking_info, $booking_rooms);
		//
		$invoice_body = self::parseInvoiceTemplate($invoicetpl, $booking, $booking_rooms, $invoice_num, $invoice_suff, $invoice_date, $company_info, ($translate === true ? $vbo_tn : null), $is_front);
		$pdffname = $booking['id'] . '_' . $booking['sid'] . '.pdf';
		$pathpdf = VBO_SITE_PATH . DS . "helpers" . DS . "invoices" . DS . "generated" . DS . $pdffname;
		if (file_exists($pathpdf)) @unlink($pathpdf);
		$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
		$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
		$pdf->SetTitle(JText::_('VBOINVNUM').' '.$invoice_num);
		//Header for each page of the pdf
		if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
			$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
		}
		//Change some currencies to their unicode (decimal) value
		$unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
		if (array_key_exists($booking['currencyname'], $unichr_map)) {
			$invoice_body = str_replace($booking['currencyname'], $pdf->unichr($unichr_map[$booking['currencyname']]), $invoice_body);
		}
		//header and footer fonts
		$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
		$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
		//margins
		$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
		$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
		$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
		//
		$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
		$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
		$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);
		if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
			$pdf->SetPrintHeader(false);
		}
		if ($pdfparams['show_footer'] == 0) {
			$pdf->SetPrintFooter(false);
		}
		$pdf->AddPage();
		$pdf->writeHTML($invoice_body, true, false, true, false, '');
		$pdf->lastPage();
		$pdf->Output($pathpdf, 'F');
		if (!file_exists($pathpdf)) {
			return false;
		}
		//insert or update record for this invoice
		$invoice_id = 0;
		$q = "SELECT `id` FROM `#__vikbooking_invoices` WHERE `idorder`=".(int)$booking['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$invoice_data = $dbo->loadAssocList();
			$invoice_id = $invoice_data[0]['id'];
		}
		//Booking History
		self::getBookingHistoryInstance()->setBid($booking['id'])->store('BI', '#'.$invoice_num.$invoice_suff);
		//
		if ($invoice_id > 0) {
			//update
			$q = "UPDATE `#__vikbooking_invoices` SET `number`=".$dbo->quote($invoice_num.$invoice_suff).", `file_name`=".$dbo->quote($pdffname).", `created_on`=".time().", `for_date`=".(int)$used_date." WHERE `id`=".(int)$invoice_id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			return $invoice_id;
		} else {
			//insert
			$q = "INSERT INTO `#__vikbooking_invoices` (`number`,`file_name`,`idorder`,`idcustomer`,`created_on`,`for_date`) VALUES(".$dbo->quote($invoice_num.$invoice_suff).", ".$dbo->quote($pdffname).", ".(int)$booking['id'].", ".(int)$booking['idcustomer'].", ".time().", ".(int)$used_date.");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			return $lid > 0 ? $lid : false;
		}
	}

	public static function sendBookingInvoice($invoice_id, $booking, $text = '', $subject = '') {
		if (!(count($booking) > 0) || empty($invoice_id) || empty($booking['custmail'])) {
			return false;
		}
		$dbo = JFactory::getDBO();
		$invoice_data = array();
		$q = "SELECT * FROM `#__vikbooking_invoices` WHERE `id`=".(int)$invoice_id.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$invoice_data = $dbo->loadAssoc();
		}
		if (!(count($invoice_data) > 0)) {
			return false;
		}
		$mail_text = empty($text) ? JText::_('VBOEMAILINVOICEATTACHTXT') : $text;
		$mail_subject = empty($subject) ? JText::_('VBOEMAILINVOICEATTACHSUBJ') : $subject;
		$invoice_file_path = VBO_SITE_PATH . DS . "helpers" . DS . "invoices" . DS . "generated" . DS . $invoice_data['file_name'];
		if (!file_exists($invoice_file_path)) {
			return false;
		}
		if (!class_exists('VboApplication')) {
			require_once(VBO_ADMIN_PATH.DS.'helpers'.DS.'jv_helper.php');
		}
		$vbo_app = new VboApplication;
		$admin_sendermail = self::getSenderMail();
		$vbo_app->sendMail($admin_sendermail, $admin_sendermail, $booking['custmail'], $admin_sendermail, $mail_subject, $mail_text, (strpos($mail_text, '<') !== false && strpos($mail_text, '/>') !== false ? true : false), 'base64', $invoice_file_path);
		//update record
		$q = "UPDATE `#__vikbooking_invoices` SET `emailed`=1, `emailed_to`=".$dbo->quote($booking['custmail'])." WHERE `id`=".(int)$invoice_id.";";
		$dbo->setQuery($q);
		$dbo->execute();
		//
		return true;
	}

	public static function loadCheckinDocTmpl ($booking_info = array(), $booking_rooms = array(), $customer = array()) {
		define('_VIKBOOKINGEXEC', '1');
		ob_start();
		include VBO_SITE_PATH . DS . "helpers" . DS . "checkins" . DS . "checkin_tmpl.php";
		$content = ob_get_contents();
		ob_end_clean();
		$default_params = array(
			'show_header' => 0,
			'header_data' => array(),
			'show_footer' => 0,
			'pdf_page_orientation' => 'PDF_PAGE_ORIENTATION',
			'pdf_unit' => 'PDF_UNIT',
			'pdf_page_format' => 'PDF_PAGE_FORMAT',
			'pdf_margin_left' => 'PDF_MARGIN_LEFT',
			'pdf_margin_top' => 'PDF_MARGIN_TOP',
			'pdf_margin_right' => 'PDF_MARGIN_RIGHT',
			'pdf_margin_header' => 'PDF_MARGIN_HEADER',
			'pdf_margin_footer' => 'PDF_MARGIN_FOOTER',
			'pdf_margin_bottom' => 'PDF_MARGIN_BOTTOM',
			'pdf_image_scale_ratio' => 'PDF_IMAGE_SCALE_RATIO',
			'header_font_size' => '10',
			'body_font_size' => '10',
			'footer_font_size' => '8'
		);
		if (defined('_VIKBOOKING_CHECKIN_PARAMS') && isset($checkin_params) && @count($checkin_params) > 0) {
			$default_params = array_merge($default_params, $checkin_params);
		}
		return array($content, $default_params);
	}

	public static function parseCheckinDocTemplate($checkintpl, $booking, $booking_rooms, $customer) {
		$parsed = $checkintpl;
		$dbo = JFactory::getDBO();
		$app = JFactory::getApplication();
		$nowdf = self::getDateFormat();
		if ($nowdf == "%d/%m/%Y") {
			$df='d/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df='m/d/Y';
		} else {
			$df='Y/m/d';
		}
		$datesep = self::getDateSeparator();
		$companylogo = self::getSiteLogo();
		$uselogo = '';
		if (!empty($companylogo)) {
			$uselogo = '<img src="'.(!$app->isAdmin() ? VBO_ADMIN_URI_REL : VBO_SITE_URI_REL).'resources/'.$companylogo.'"/>';
		}
		$company_name = self::getFrontTitle();
		$company_info = self::getInvoiceCompanyInfo();
		$parsed = str_replace("{company_name}", $company_name, $parsed);
		$parsed = str_replace("{company_logo}", $uselogo, $parsed);
		$parsed = str_replace("{company_info}", $company_info, $parsed);
		$parsed = str_replace("{customer_info}", nl2br(rtrim($booking['custdata'], "\n")), $parsed);
		$parsed = str_replace("{checkin_date}", date(str_replace("/", $datesep, $df), $booking['checkin']), $parsed);
		$parsed = str_replace("{checkout_date}", date(str_replace("/", $datesep, $df), $booking['checkout']), $parsed);
		$parsed = str_replace("{num_nights}", $booking['days'], $parsed);
		$tot_guests = 0;
		$tot_adults = 0;
		$tot_children = 0;
		foreach($booking_rooms as $kor => $or) {
			$tot_guests += ($or['adults'] + $or['children']);
			$tot_adults += $or['adults'];
			$tot_children += $or['children'];
		}
		$parsed = str_replace("{tot_guests}", $tot_guests, $parsed);
		$parsed = str_replace("{tot_adults}", $tot_adults, $parsed);
		$parsed = str_replace("{tot_children}", $tot_children, $parsed);
		if (count($customer) && isset($customer['comments'])) {
			$parsed = str_replace("{checkin_comments}", $customer['comments'], $parsed);
		}
		$termsconds = self::getTermsConditions();
		$parsed = str_replace("{terms_and_conditions}", $termsconds, $parsed);
		//custom fields replace
		preg_match_all('/\{customfield ([0-9]+)\}/U', $parsed, $cmatches);
		if (is_array($cmatches[1]) && @count($cmatches[1]) > 0) {
			$cfids = array();
			foreach ($cmatches[1] as $cfid ){
				$cfids[] = $cfid;
			}
			$q = "SELECT * FROM `#__vikbooking_custfields` WHERE `id` IN (".implode(", ", $cfids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
			$vbo_tn->translateContents($cfields, '#__vikbooking_custfields');
			$cfmap = array();
			if (is_array($cfields)) {
				foreach ($cfields as $cf) {
					$cfmap[trim(JText::_($cf['name']))] = $cf['id'];
				}
			}
			$cfmapreplace = array();
			$partsreceived = explode("\n", $booking['custdata']);
			if (count($partsreceived) > 0) {
				foreach ($partsreceived as $pst) {
					if (!empty($pst)) {
						$tmpdata = explode(":", $pst);
						if (array_key_exists(trim($tmpdata[0]), $cfmap)) {
							$cfmapreplace[$cfmap[trim($tmpdata[0])]] = trim($tmpdata[1]);
						}
					}
				}
			}
			foreach($cmatches[1] as $cfid ){
				if (array_key_exists($cfid, $cfmapreplace)) {
					$parsed = str_replace("{customfield ".$cfid."}", $cfmapreplace[$cfid], $parsed);
				} else {
					$parsed = str_replace("{customfield ".$cfid."}", "", $parsed);
				}
			}
		}
		//end custom fields replace

		return $parsed;
	}

	/**
	 * Returns an array of key-value pairs to be used for
	 * building the Guests Details in the Check-in process.
	 * The keys will be compared to the fields of the table
	 * _customers to see if some values already exist.
	 * The values are just the translations of the fields (back-end).
	 * To be called as list(fields, attributes).
	 *
	 * @return 	array
	 */
	public static function getPaxFields() {
		return array(
			0 => array(
				'first_name' => JText::_('VBCUSTOMERFIRSTNAME'),
				'last_name' => JText::_('VBCUSTOMERLASTNAME'),
				'country' => JText::_('VBCUSTOMERCOUNTRY'),
				'docnum' => JText::_('VBCUSTOMERDOCNUM'),
				'extranotes' => JText::_('VBOGUESTEXTRANOTES')
			),
			1 => array(
				'first_name' => 'size="15"',
				'last_name' => 'size="15"',
				'country' => '',
				'docnum' => 'size="15"',
				'extranotes' => 'size="35"'
			)
		);
	}

	public static function getCountriesArray() {
		$all_countries = array();
		$dbo = JFactory::getDBO();
		$q = "SELECT `country_name`, `country_3_code` FROM `#__vikbooking_countries` ORDER BY `country_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$countries = $dbo->loadAssocList();
		foreach ($countries as $v) {
			$all_countries[$v['country_3_code']] = $v;
		}
		return $all_countries;
	}

	public static function getCountriesSelect($name, $all_countries = array(), $current_value = '', $empty_value = ' ') {
		if (!(count($all_countries) > 0)) {
			$all_countries = self::getCountriesArray();
		}
		$countries = '<select name="'.$name.'">'."\n";
		if (strlen($empty_value)) {
			$countries .= '<option value="">'.$empty_value.'</option>'."\n";
		}
		foreach ($all_countries as $v) {
			$countries .= '<option value="'.$v['country_3_code'].'"'.($v['country_3_code'] == $current_value ? ' selected="selected"' : '').'>'.$v['country_name'].'</option>'."\n";
		}
		$countries .= '</select>';

		return $countries;
	}

	public static function getThumbSize($skipsession = false) {
		if (!$skipsession) {
			$session = JFactory::getSession();
			$s = $session->get('vbothumbsize', '');
			if (strlen($s)) {
				return (int)$s;
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='thumbsize';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			if (!$skipsession) {
				$session->set('vbothumbsize', $s);
			}
			return (int)$s;
		}
		$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('thumbsize', '100');";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$skipsession) {
			$session->set('vbothumbsize', '100');
		}
		return 100;
	}

	/**
	 * Returns a string without any new-line characters
	 * to be used for JavaScript values without facing
	 * errors like 'unterminated string literal'.
	 * By passing nl2br($str) as argument, we can keep
	 * the wanted new-line HTML tags for PRE tags. 
	 * We use implode() with just one argument to 
	 * not use an empty string as glue for the string.
	 *
	 * @param 	$str 	string
	 *
	 * @return 	string 	
	 */
	public static function strTrimLiteral($str) {
		$str = str_replace(array("\r\n", "\r"), "\n", $str);
		$lines = explode("\n", $str);
		$new_lines = array();
		foreach ($lines as $i => $line) {
		    if (strlen($line)) {
				$new_lines[] = trim($line);
			}
		}
		return implode($new_lines);
	}

	public static function getVboApplication() {
		if (!class_exists('VboApplication')) {
			require_once(VBO_ADMIN_PATH.DS.'helpers'.DS.'jv_helper.php');
		}
		return new VboApplication();
	}
	
	public static function sayWeekDay($wd) {
		switch ($wd) {
			case '6' :
				$ret = JText::_('VBWEEKDAYSIX');
				break;
			case '5' :
				$ret = JText::_('VBWEEKDAYFIVE');
				break;
			case '4' :
				$ret = JText::_('VBWEEKDAYFOUR');
				break;
			case '3' :
				$ret = JText::_('VBWEEKDAYTHREE');
				break;
			case '2' :
				$ret = JText::_('VBWEEKDAYTWO');
				break;
			case '1' :
				$ret = JText::_('VBWEEKDAYONE');
				break;
			default :
				$ret = JText::_('VBWEEKDAYZERO');
				break;
		}
		return $ret;
	}
	
	public static function sayMonth($idm) {
		switch ($idm) {
			case '12' :
				$ret = JText::_('VBMONTHTWELVE');
				break;
			case '11' :
				$ret = JText::_('VBMONTHELEVEN');
				break;
			case '10' :
				$ret = JText::_('VBMONTHTEN');
				break;
			case '9' :
				$ret = JText::_('VBMONTHNINE');
				break;
			case '8' :
				$ret = JText::_('VBMONTHEIGHT');
				break;
			case '7' :
				$ret = JText::_('VBMONTHSEVEN');
				break;
			case '6' :
				$ret = JText::_('VBMONTHSIX');
				break;
			case '5' :
				$ret = JText::_('VBMONTHFIVE');
				break;
			case '4' :
				$ret = JText::_('VBMONTHFOUR');
				break;
			case '3' :
				$ret = JText::_('VBMONTHTHREE');
				break;
			case '2' :
				$ret = JText::_('VBMONTHTWO');
				break;
			default :
				$ret = JText::_('VBMONTHONE');
				break;
		}
		return $ret;
	}
	
	public static function sayDayMonth($d) {
		switch ($d) {
			case '31' :
				$ret = JText::_('VBDAYMONTHTHIRTYONE');
				break;
			case '30' :
				$ret = JText::_('VBDAYMONTHTHIRTY');
				break;
			case '29' :
				$ret = JText::_('VBDAYMONTHTWENTYNINE');
				break;
			case '28' :
				$ret = JText::_('VBDAYMONTHTWENTYEIGHT');
				break;
			case '27' :
				$ret = JText::_('VBDAYMONTHTWENTYSEVEN');
				break;
			case '26' :
				$ret = JText::_('VBDAYMONTHTWENTYSIX');
				break;
			case '25' :
				$ret = JText::_('VBDAYMONTHTWENTYFIVE');
				break;
			case '24' :
				$ret = JText::_('VBDAYMONTHTWENTYFOUR');
				break;
			case '23' :
				$ret = JText::_('VBDAYMONTHTWENTYTHREE');
				break;
			case '22' :
				$ret = JText::_('VBDAYMONTHTWENTYTWO');
				break;
			case '21' :
				$ret = JText::_('VBDAYMONTHTWENTYONE');
				break;
			case '20' :
				$ret = JText::_('VBDAYMONTHTWENTY');
				break;
			case '19' :
				$ret = JText::_('VBDAYMONTHNINETEEN');
				break;
			case '18' :
				$ret = JText::_('VBDAYMONTHEIGHTEEN');
				break;
			case '17' :
				$ret = JText::_('VBDAYMONTHSEVENTEEN');
				break;
			case '16' :
				$ret = JText::_('VBDAYMONTHSIXTEEN');
				break;
			case '15' :
				$ret = JText::_('VBDAYMONTHFIFTEEN');
				break;
			case '14' :
				$ret = JText::_('VBDAYMONTHFOURTEEN');
				break;
			case '13' :
				$ret = JText::_('VBDAYMONTHTHIRTEEN');
				break;
			case '12' :
				$ret = JText::_('VBDAYMONTHTWELVE');
				break;
			case '11' :
				$ret = JText::_('VBDAYMONTHELEVEN');
				break;
			case '10' :
				$ret = JText::_('VBDAYMONTHTEN');
				break;
			case '9' :
				$ret = JText::_('VBDAYMONTHNINE');
				break;
			case '8' :
				$ret = JText::_('VBDAYMONTHEIGHT');
				break;
			case '7' :
				$ret = JText::_('VBDAYMONTHSEVEN');
				break;
			case '6' :
				$ret = JText::_('VBDAYMONTHSIX');
				break;
			case '5' :
				$ret = JText::_('VBDAYMONTHFIVE');
				break;
			case '4' :
				$ret = JText::_('VBDAYMONTHFOUR');
				break;
			case '3' :
				$ret = JText::_('VBDAYMONTHTHREE');
				break;
			case '2' :
				$ret = JText::_('VBDAYMONTHTWO');
				break;
			default :
				$ret = JText::_('VBDAYMONTHONE');
				break;
		}
		return $ret;
	}

	public static function totElements($arr) {
		$n = 0;
		if (is_array($arr)) {
			foreach ($arr as $a) {
				if (!empty($a)) {
					$n++;
				}
			}
			return $n;
		}
		return false;
	}
	
	public static function displayPaymentParameters ($pfile, $pparams = '') {
		$html = '---------';
		$arrparams = !empty($pparams) ? json_decode($pparams, true) : array();
		if (file_exists(VBO_ADMIN_PATH.DS.'payments'.DS.$pfile) && !empty($pfile)) {
			require_once(VBO_ADMIN_PATH.DS.'payments'.DS.$pfile);
			if (method_exists('vikBookingPayment', 'getAdminParameters')) {
				$pconfig = vikBookingPayment::getAdminParameters();
				if (count($pconfig) > 0) {
					$html = '';
					foreach($pconfig as $value => $cont) {
						if (empty($value)) {
							continue;
						}
						$labelparts = explode('//', $cont['label']);
						$label = $labelparts[0];
						$labelhelp = isset($labelparts[1]) ? $labelparts[1] : '';
						$html .= '<div class="vikpaymentparam">';
						if (strlen($label) > 0) {
							$html .= '<span class="vikpaymentparamlabel">'.$label.'</span>';
						}
						switch ($cont['type']) {
							case 'custom':
								$html .= $cont['html'];
								break;
							case 'select':
								$html .= '<span class="vikpaymentparaminput">' .
										'<select name="vikpaymentparams['.$value.']">';
								foreach($cont['options'] as $poption) {
									$html .= '<option value="'.$poption.'"'.(array_key_exists($value, $arrparams) && $poption == $arrparams[$value] ? ' selected="selected"' : '').'>'.$poption.'</option>';
								}
								$html .= '</select></span>';
								break;
							default:
								$html .= '<span class="vikpaymentparaminput">' .
										'<input type="text" name="vikpaymentparams['.$value.']" value="'.(array_key_exists($value, $arrparams) ? $arrparams[$value] : '').'" size="20"/>' .
										'</span>';
								break;
						}
						if (strlen($labelhelp) > 0) {
							$html .= '<span class="vikpaymentparamlabelhelp">'.$labelhelp.'</span>';
						}
						$html .= '</div>';
					}
				}
			}
		}
		return $html;
	}

	public static function displaySMSParameters ($pfile, $pparams = '') {
		$html = '---------';
		$arrparams = !empty($pparams) ? json_decode($pparams, true) : array();
		if (file_exists(VBO_ADMIN_PATH.DS.'smsapi'.DS.$pfile) && !empty($pfile)) {
			require_once(VBO_ADMIN_PATH.DS.'smsapi'.DS.$pfile);
			if (method_exists('VikSmsApi', 'getAdminParameters')) {
				$pconfig = VikSmsApi::getAdminParameters();
				if (count($pconfig) > 0) {
					$html = '';
					foreach($pconfig as $value => $cont) {
						if (empty($value)) {
							continue;
						}
						$labelparts = explode('//', $cont['label']);
						$label = $labelparts[0];
						$labelhelp = isset($labelparts[1]) ? $labelparts[1] : '';
						$html .= '<div class="vikpaymentparam">';
						if (strlen($label) > 0) {
							$html .= '<span class="vikpaymentparamlabel">'.$label.'</span>';
						}
						switch ($cont['type']) {
							case 'custom':
								$html .= $cont['html'];
								break;
							case 'select':
								$html .= '<span class="vikpaymentparaminput">' .
										'<select name="viksmsparams['.$value.']">';
								foreach($cont['options'] as $poption) {
									$html .= '<option value="'.$poption.'"'.(array_key_exists($value, $arrparams) && $poption == $arrparams[$value] ? ' selected="selected"' : '').'>'.$poption.'</option>';
								}
								$html .= '</select></span>';
								break;
							default:
								$html .= '<span class="vikpaymentparaminput">' .
										'<input type="text" name="viksmsparams['.$value.']" value="'.(array_key_exists($value, $arrparams) ? $arrparams[$value] : '').'" size="40"/>' .
										'</span>';
								break;
						}
						if (strlen($labelhelp) > 0) {
							$html .= '<span class="vikpaymentparamlabelhelp">'.$labelhelp.'</span>';
						}
						$html .= '</div>';
					}
				}
			}
		}
		return $html;
	}

	public static function displayCronParameters ($pfile, $pparams = '') {
		$html = '---------';
		$arrparams = !empty($pparams) ? json_decode($pparams, true) : array();
		if (file_exists(VBO_ADMIN_PATH.DS.'cronjobs'.DS.$pfile) && !empty($pfile)) {
			require_once(VBO_ADMIN_PATH.DS.'cronjobs'.DS.$pfile);
			if (method_exists('VikCronJob', 'getAdminParameters')) {
				$pconfig = VikCronJob::getAdminParameters();
				if (count($pconfig) > 0) {
					$html = '';
					foreach($pconfig as $value => $cont) {
						if (empty($value)) {
							continue;
						}
						$inp_attr = '';
						if (array_key_exists('attributes', $cont)) {
							foreach ($cont['attributes'] as $inpk => $inpv) {
								$inp_attr .= $inpk.'="'.$inpv.'" ';
							}
						}
						$labelparts = explode('//', $cont['label']);
						$label = $labelparts[0];
						$labelhelp = isset($labelparts[1]) ? $labelparts[1] : '';
						$html .= '<div class="vikpaymentparam">';
						if (strlen($label) > 0) {
							$html .= '<span class="vikpaymentparamlabel vikpaymentparamlbl-'.strtolower($cont['type']).'">'.$label.'</span>';
						}
						switch ($cont['type']) {
							case 'custom':
								$html .= $cont['html'];
								break;
							case 'select':
								$html .= '<span class="vikpaymentparaminput">' .
										'<select name="vikcronparams['.$value.']"'.(array_key_exists('attributes', $cont) ? ' '.$inp_attr : '').'>';
								foreach($cont['options'] as $kopt => $poption) {
									$html .= '<option value="'.$poption.'"'.(array_key_exists($value, $arrparams) && $poption == $arrparams[$value] ? ' selected="selected"' : '').'>'.(is_numeric($kopt) ? $poption : $kopt).'</option>';
								}
								$html .= '</select></span>';
								break;
							case 'number':
								$html .= '<span class="vikpaymentparaminput">' .
										'<input type="number" name="vikcronparams['.$value.']" value="'.(array_key_exists($value, $arrparams) ? $arrparams[$value] : (array_key_exists('default', $cont) ? $cont['default'] : '')).'" '.(array_key_exists('attributes', $cont) ? $inp_attr : '').'/>' .
										'</span>';
								break;
							case 'textarea':
								$html .= '<span class="vikpaymentparaminput vikpaymentparaminput-tarea">' .
										'<textarea name="vikcronparams['.$value.']" '.(array_key_exists('attributes', $cont) ? $inp_attr : 'rows="4" cols="60"').'>'.(array_key_exists($value, $arrparams) ? htmlentities($arrparams[$value]) : (array_key_exists('default', $cont) ? htmlentities($cont['default']) : '')).'</textarea>' .
										'</span>';
								break;
							default:
								$html .= '<span class="vikpaymentparaminput">' .
										'<input type="text" name="vikcronparams['.$value.']" value="'.(array_key_exists($value, $arrparams) ? $arrparams[$value] : (array_key_exists('default', $cont) ? $cont['default'] : '')).'" '.(array_key_exists('attributes', $cont) ? $inp_attr : 'size="40"').'/>' .
										'</span>';
								break;
						}
						if (strlen($labelhelp) > 0) {
							$html .= '<span class="vikpaymentparamlabelhelp">'.$labelhelp.'</span>';
						}
						$html .= '</div>';
					}
				}
			}
		}
		return $html;
	}
	
	public static function invokeChannelManager($skiporder = true, $order = array()) {
		$task = VikRequest::getString('task', '', 'request');
		$tmpl = VikRequest::getString('tmpl', '', 'request');
		$noimpression = array('vieworder');
		if ($tmpl != 'component' && (!$skiporder || !in_array($task, $noimpression)) && file_exists(VCM_SITE_PATH.DS.'helpers'.DS.'lib.vikchannelmanager.php')) {
			//VCM Channel Impression
			if (!class_exists('VikChannelManagerConfig')) {
				require_once(VCM_SITE_PATH.DS.'helpers'.DS.'vcm_config.php');
			}
			if (!class_exists('VikChannelManager')) {
				require_once(VCM_SITE_PATH.DS.'helpers'.DS.'lib.vikchannelmanager.php');
			}
			VikChannelManager::invokeChannelImpression();
		} elseif ($tmpl != 'component' && count($order) > 0 && file_exists(VCM_SITE_PATH.DS.'helpers'.DS.'lib.vikchannelmanager.php')) {
			//VCM Channel Conversion-Impression
			if (!class_exists('VikChannelManagerConfig')) {
				require_once(VCM_SITE_PATH.DS.'helpers'.DS.'vcm_config.php');
			}
			if (!class_exists('VikChannelManager')) {
				require_once(VCM_SITE_PATH.DS.'helpers'.DS.'lib.vikchannelmanager.php');
			}
			VikChannelManager::invokeChannelConversionImpression($order);
		}
	}

	public static function validEmail($email) {
		$isValid = true;
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex) {
			$isValid = false;
		} else {
			$domain = substr($email, $atIndex +1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64) {
				// local part length exceeded
				$isValid = false;
			} else
				if ($domainLen < 1 || $domainLen > 255) {
					// domain part length exceeded
					$isValid = false;
				} else
					if ($local[0] == '.' || $local[$localLen -1] == '.') {
						// local part starts or ends with '.'
						$isValid = false;
					} else
						if (preg_match('/\\.\\./', $local)) {
							// local part has two consecutive dots
							$isValid = false;
						} else
							if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
								// character not valid in domain part
								$isValid = false;
							} else
								if (preg_match('/\\.\\./', $domain)) {
									// domain part has two consecutive dots
									$isValid = false;
								} else
									if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
										// character not valid in local part unless 
										// local part is quoted
										if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
											$isValid = false;
										}
									}
			if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
				// domain not found in DNS
				$isValid = false;
			}
		}
		return $isValid;
	}

	public static function caniWrite($path) {
		if ($path[strlen($path) - 1] == '/') {
			// ricorsivo return a temporary file path
			return self::caniWrite($path . uniqid(mt_rand()) . '.tmp');
		}
		if (is_dir($path)) {
			return self::caniWrite($path . '/' . uniqid(mt_rand()) . '.tmp');
		}
		// check tmp file for read/write capabilities
		$rm = file_exists($path);
		$f = @fopen($path, 'a');
		if ($f === false) {
			return false;
		}
		fclose($f);
		if (!$rm) {
			unlink($path);
		}
		return true;
	}

	/**
	 * Alias method of JFile::upload to unify any
	 * upload function into one.
	 * 
	 * @param   string   $src 			The name of the php (temporary) uploaded file.
	 * @param   string   $dest 			The path (including filename) to move the uploaded file to.
	 * @param   boolean  [$copy_only] 	Whether to skip the file upload and just copy the file.
	 * 
	 * @return  boolean  True on success.
	 * 
	 * @since 	1.10 - Revision April 24th 2018 for compatibility with the VikWP Framework.
	 * 			@wponly 1.0.7 added the third $copy_only argument to remove the use of copy()
	 */
	public static function uploadFile($src, $dest, $copy_only = false) {
		// always attempt to include the File class
		jimport('joomla.filesystem.file');

		// upload the file
		if (!$copy_only) {
			$result = JFile::upload($src, $dest);
		} else {
			// this is to avoid the use of the PHP function copy() and allow files mirroring in WP (triggerUploadBackup)
			$result = JFile::copy($src, $dest);
		}

		// return upload result
		return $result;
	}

}

class vikResizer {

	public function __construct() {
		//objects of this class can also be instantiated without calling the methods statically.
	}

	/**
	 * Resizes an image proportionally. For PNG files it can optionally
	 * trim the image to exclude the transparency, and add some padding to it.
	 * All PNG files keep the alpha background in the resized version.
	 *
	 * @param 	string 		$fileimg 	path to original image file
	 * @param 	string 		$dest 		path to destination image file
	 * @param 	int 		$towidth 	
	 * @param 	int 		$toheight 	
	 * @param 	bool 		$trim_png 	remove empty background from image
	 * @param 	string 		$trim_pad 	CSS-style version of padding (top right bottom left) ex: '1 2 3 4'
	 *
	 * @return 	boolean
	 */
	public static function proportionalImage($fileimg, $dest, $towidth, $toheight, $trim_png = false, $trim_pad = null) {
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty($towidth) && empty($toheight)) {
			copy($fileimg, $dest);
			return true;
		}

		list ($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid > $towidth || $ohei > $toheight) {
			$xscale = $owid / $towidth;
			$yscale = $ohei / $toheight;
			if ($yscale > $xscale) {
				$new_width = round($owid * (1 / $yscale));
				$new_height = round($ohei * (1 / $yscale));
			} else {
				$new_width = round($owid * (1 / $xscale));
				$new_height = round($ohei * (1 / $xscale));
			}

			$imageresized = imagecreatetruecolor($new_width, $new_height);

			switch ($type) {
				case '1' :
					$imagetmp = imagecreatefromgif ($fileimg);
					break;
				case '2' :
					$imagetmp = imagecreatefromjpeg($fileimg);
					break;
				default :
					//keep alpha for PNG files
					$background = imagecolorallocate($imageresized, 0, 0, 0);
					imagecolortransparent($imageresized, $background);
					imagealphablending($imageresized, false);
					imagesavealpha($imageresized, true);
					//
					$imagetmp = imagecreatefrompng($fileimg);
					break;
			}

			imagecopyresampled($imageresized, $imagetmp, 0, 0, 0, 0, $new_width, $new_height, $owid, $ohei);

			switch ($type) {
				case '1' :
					imagegif ($imageresized, $dest);
					break;
				case '2' :
					imagejpeg($imageresized, $dest);
					break;
				default :
					if ($trim_png) {
						self::imageTrim($imageresized, $background, $trim_pad);
					}
					imagepng($imageresized, $dest);
					break;
			}

			imagedestroy($imageresized);
		} else {
			copy($fileimg, $dest);
		}
		return true;
	}

	/**
	 * (BETA) Resizes an image proportionally. For PNG files it can optionally
	 * trim the image to exclude the transparency, and add some padding to it.
	 * All PNG files keep the alpha background in the resized version.
	 *
	 * @param 	resource 	$im 		Image link resource (reference)
	 * @param 	int 		$bg 		imagecolorallocate color identifier
	 * @param 	string 		$pad 		CSS-style version of padding (top right bottom left) ex: '1 2 3 4'
	 *
	 * @return 	void
	 */
	public static function imagetrim(&$im, $bg, $pad = null){
		// Calculate padding for each side.
		if (isset($pad)) {
			$pp = explode(' ', $pad);
			if (isset($pp[3])) {
				$p = array((int) $pp[0], (int) $pp[1], (int) $pp[2], (int) $pp[3]);
			} elseif (isset($pp[2])) {
				$p = array((int) $pp[0], (int) $pp[1], (int) $pp[2], (int) $pp[1]);
			} elseif (isset($pp[1])) {
				$p = array((int) $pp[0], (int) $pp[1], (int) $pp[0], (int) $pp[1]);
			} else {
				$p = array_fill(0, 4, (int) $pp[0]);
			}
		} else {
			$p = array_fill(0, 4, 0);
		}

		// Get the image width and height.
		$imw = imagesx($im);
		$imh = imagesy($im);

		// Set the X variables.
		$xmin = $imw;
		$xmax = 0;

		// Start scanning for the edges.
		for ($iy=0; $iy<$imh; $iy++) {
			$first = true;
			for ($ix=0; $ix<$imw; $ix++) {
				$ndx = imagecolorat($im, $ix, $iy);
				if ($ndx != $bg) {
					if ($xmin > $ix) {
						$xmin = $ix;
					}
					if ($xmax < $ix) {
						$xmax = $ix;
					}
					if (!isset($ymin)) {
						$ymin = $iy;
					}
					$ymax = $iy;
					if ($first) {
						$ix = $xmax;
						$first = false;
					}
				}
			}
		}

		// The new width and height of the image. (not including padding)
		$imw = 1+$xmax-$xmin; // Image width in pixels
		$imh = 1+$ymax-$ymin; // Image height in pixels

		// Make another image to place the trimmed version in.
		$im2 = imagecreatetruecolor($imw+$p[1]+$p[3], $imh+$p[0]+$p[2]);

		// Make the background of the new image the same as the background of the old one.
		$bg2 = imagecolorallocate($im2, ($bg >> 16) & 0xFF, ($bg >> 8) & 0xFF, $bg & 0xFF);
		imagefill($im2, 0, 0, $bg2);

		// Copy it over to the new image.
		imagecopy($im2, $im, $p[3], $p[0], $xmin, $ymin, $imw, $imh);

		// To finish up, we replace the old image which is referenced.
		$im = $im2;
	}

	public static function bandedImage($fileimg, $dest, $towidth, $toheight, $rgb) {
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty($towidth) && empty($toheight)) {
			copy($fileimg, $dest);
			return true;
		}

		$exp = explode(",", $rgb);
		if (count($exp) == 3) {
			$r = trim($exp[0]);
			$g = trim($exp[1]);
			$b = trim($exp[2]);
		} else {
			$r = 0;
			$g = 0;
			$b = 0;
		}

		list ($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid > $towidth || $ohei > $toheight) {
			$xscale = $owid / $towidth;
			$yscale = $ohei / $toheight;
			if ($yscale > $xscale) {
				$new_width = round($owid * (1 / $yscale));
				$new_height = round($ohei * (1 / $yscale));
				$ydest = 0;
				$diff = $towidth - $new_width;
				$xdest = ($diff > 0 ? round($diff / 2) : 0);
			} else {
				$new_width = round($owid * (1 / $xscale));
				$new_height = round($ohei * (1 / $xscale));
				$xdest = 0;
				$diff = $toheight - $new_height;
				$ydest = ($diff > 0 ? round($diff / 2) : 0);
			}

			$imageresized = imagecreatetruecolor($towidth, $toheight);

			$bgColor = imagecolorallocate($imageresized, (int) $r, (int) $g, (int) $b);
			imagefill($imageresized, 0, 0, $bgColor);

			switch ($type) {
				case '1' :
					$imagetmp = imagecreatefromgif ($fileimg);
					break;
				case '2' :
					$imagetmp = imagecreatefromjpeg($fileimg);
					break;
				default :
					$imagetmp = imagecreatefrompng($fileimg);
					break;
			}

			imagecopyresampled($imageresized, $imagetmp, $xdest, $ydest, 0, 0, $new_width, $new_height, $owid, $ohei);

			switch ($type) {
				case '1' :
					imagegif ($imageresized, $dest);
					break;
				case '2' :
					imagejpeg($imageresized, $dest);
					break;
				default :
					imagepng($imageresized, $dest);
					break;
			}

			imagedestroy($imageresized);

			return true;
		} else {
			copy($fileimg, $dest);
		}
		return true;
	}

	public static function croppedImage($fileimg, $dest, $towidth, $toheight) {
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty($towidth) && empty($toheight)) {
			copy($fileimg, $dest);
			return true;
		}

		list ($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid <= $ohei) {
			$new_width = $towidth;
			$new_height = ($towidth / $owid) * $ohei;
		} else {
			$new_height = $toheight;
			$new_width = ($new_height / $ohei) * $owid;
		}

		switch ($type) {
			case '1' :
				$img_src = imagecreatefromgif ($fileimg);
				$img_dest = imagecreate($new_width, $new_height);
				break;
			case '2' :
				$img_src = imagecreatefromjpeg($fileimg);
				$img_dest = imagecreatetruecolor($new_width, $new_height);
				break;
			default :
				$img_src = imagecreatefrompng($fileimg);
				$img_dest = imagecreatetruecolor($new_width, $new_height);
				break;
		}

		imagecopyresampled($img_dest, $img_src, 0, 0, 0, 0, $new_width, $new_height, $owid, $ohei);

		switch ($type) {
			case '1' :
				$cropped = imagecreate($towidth, $toheight);
				break;
			case '2' :
				$cropped = imagecreatetruecolor($towidth, $toheight);
				break;
			default :
				$cropped = imagecreatetruecolor($towidth, $toheight);
				break;
		}

		imagecopy($cropped, $img_dest, 0, 0, 0, 0, $owid, $ohei);

		switch ($type) {
			case '1' :
				imagegif ($cropped, $dest);
				break;
			case '2' :
				imagejpeg($cropped, $dest);
				break;
			default :
				imagepng($cropped, $dest);
				break;
		}

		imagedestroy($img_dest);
		imagedestroy($cropped);

		return true;
	}

}

function encryptCookie($str) {
	for ($i = 0; $i < 5; $i++) {
		$str = strrev(base64_encode($str));
	}
	return $str;
}

function decryptCookie($str) {
	for ($i = 0; $i < 5; $i++) {
		$str = base64_decode(strrev($str));
	}
	return $str;
}

function read($str) {
	$var = "";
	for ($i = 0; $i < strlen($str); $i += 2)
		$var .= chr(hexdec(substr($str, $i, 2)));
	return $var;
}

function checkComp($lf, $h, $n) {
	$a = $lf[0];
	$b = $lf[1];
	for ($i = 0; $i < 5; $i++) {
		$a = base64_decode(strrev($a));
		$b = base64_decode(strrev($b));
	}
	if ($a == $h || $b == $h || $a == $n || $b == $n) {
		return true;
	} else {
		$a = str_replace('www.', "", $a);
		$b = str_replace('www.', "", $b);
		if ((!empty($a) && (preg_match("/" . $a . "/i", $h) || preg_match("/" . $a . "/i", $n))) || (!empty($b) && (preg_match("/" . $b . "/i", $h) || preg_match("/" . $b . "/i", $n)))) {
			return true;
		}
	}
	return false;
}

define('CREATIVIKAPP', 'com_vikbooking');
defined('E4J_SOFTWARE_VERSION') or define('E4J_SOFTWARE_VERSION', '1.10');

class CreativikDotIt {
	function __construct() {
		$this->headers = array (
				"Referer" => "",
				"User-Agent" => "CreativikDotIt/1.0",
				"Connection" => "close"
		);
		$this->version = "1.1";
		$this->ctout = 15;
		$this->f_redha = false;
	}

	function exeqer($url) {
		$rcodes = array (
				301,
				302,
				303,
				307
		);
		$rmeth = array (
				'GET',
				'HEAD'
		);
		$rres = false;
		$this->fd_redhad = false;
		$ppred = array ();
		do {
			$rres = $this->sendout($url);
			$url = false;
			if ($this->f_redha && in_array($this->edocser, $rcodes)) {
				if (($this->edocser == 303) || in_array($this->method, $rmeth)) {
					$url = $this->resphh['Location'];
				}
			}
			if ($url && strlen($url)) {
				if (isset ($ppred[$url])) {
					$this->rore = "tceriderpool";
					$rres = false;
					break;
				}
				if (is_numeric($this->f_redha) && (count($ppred) > $this->f_redha)) {
					$this->rore = "tceriderynamoot";
					$rres = false;
					break;
				}
				$ppred[$url] = true;
			}
		} while ($url && strlen($url));
		$rep_qer_daeh = array (
				'Host',
				'Content-Length'
		);
		foreach ($rep_qer_daeh as $k => $v)
			unset ($this->headers[$v]);
		if (count($ppred) > 1)
			$this->fd_redhad = array_keys($ppred);
		return $rres;
	}

	function dliubh() {
		$daeh = "";
		foreach ($this->headers as $name => $value) {
			$value = trim($value);
			if (empty($value))
				continue;
			$daeh .= "{$name}: $value\r\n";
		}
		$daeh .= "\r\n";
		return $daeh;
	}

	function sendout($url) {
		$time_request_start = time();
		$urldata = parse_url($url);
		if (!$urldata["port"])
			$urldata["port"] = ($urldata["scheme"] == "https") ? 443 : 80;
		if (!$urldata["path"])
			$urldata["path"] = '/';
		if ($this->version > "1.0")
			$this->headers["Host"] = $urldata["host"];
		unset ($this->headers['Authorization']);
		if (!empty($urldata["query"]))
			$urldata["path"] .= "?" . $urldata["query"];
		$request = $this->method . " " . $urldata["path"] . " HTTP/" . $this->version . "\r\n";
		$request .= $this->dliubh();
		$this->tise = "";
		$hostname = $urldata['host'];
		$time_connect_start = time();
		$fp = @fsockopen($hostname, $urldata["port"], $errno, $errstr, $this->ctout);
		$connect_time = time() - $time_connect_start;
		if ($fp) {
			stream_set_timeout($fp, 3);
			fputs($fp, $request);
			$meta = stream_get_meta_data($fp);
			if ($meta['timed_out']) {
				$this->rore = "sdnoceseerhtfotuoemitetirwtekcosdedeecxe";
				return false;
			}
			$cerdaeh = false;
			$data_length = false;
			$chunked = false;
			while (!feof($fp)) {
				if ($data_length > 0) {
					$line = fread($fp, $data_length);
					$data_length -= strlen($line);
				} else {
					$line = fgets($fp, 10240);
					if ($chunked) {
						$line = trim($line);
						if (!strlen($line))
							continue;
						list ($data_length,) = explode(';', $line);
						$data_length = (int) hexdec(trim($data_length));
						if ($data_length == 0) {
							break;
						}
						continue;
					}
				}
				$this->tise .= $line;
				if ((!$cerdaeh) && (trim($line) == "")) {
					$cerdaeh = true;
					if (preg_match('/\nContent-Length: ([0-9]+)/i', $this->tise, $matches)) {
						$data_length = (int) $matches[1];
					}
					if (preg_match("/\nTransfer-Encoding: chunked/i", $this->tise, $matches)) {
						$chunked = true;
					}
				}
				$meta = stream_get_meta_data($fp);
				if ($meta['timed_out']) {
					$this->rore = "sceseerhttuoemitdaertekcos";
					return false;
				}
				if (time() - $time_request_start > 5) {
					$this->rore = "maxtransfertimefivesecs";
					return false;
					break;
				}
			}
			fclose($fp);
		} else {
			$this->rore = $urldata['scheme'] . " otdeliafnoitcennoc " . $hostname . " trop " . $urldata['port'];
			return false;
		}
		do {
			$neldaeh = strpos($this->tise, "\r\n\r\n");
			$serp_daeh = explode("\r\n", substr($this->tise, 0, $neldaeh));
			$pthats = trim(array_shift($serp_daeh));
			foreach ($serp_daeh as $line) {
				list ($k, $v) = explode(":", $line, 2);
				$this->resphh[trim($k)] = trim($v);
			}
			$this->tise = substr($this->tise, $neldaeh +4);
			if (!preg_match("/^HTTP\/([0-9\.]+) ([0-9]+) (.*?)$/", $pthats, $matches)) {
				$matches = array (
						"",
						$this->version,
						0,
						"HTTP request error"
				);
			}
			list (, $pserver, $this->edocser, $this->txet) = $matches;
		} while (($this->edocser == 100) && ($neldaeh));
		$ok = ($this->edocser == 200);
		return $ok;
	}

	function ksa($url) {
		$this->method = "GET";
		return $this->exeqer($url);
	}

}
