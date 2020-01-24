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

class VikBookingViewRatesoverv extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		$roomid = $cid[0];

		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		if (empty($roomid)) {
			$q = "SELECT `id` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC LIMIT 1";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$roomid = $dbo->loadResult();
			}
		}
		if (empty($roomid)) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
			exit;
		}
		$roomrows = array();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$all_rooms = $dbo->loadAssocList();
		$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`=".intval($roomid).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$roomrows = $dbo->loadAssoc();
		}
		if (!(count($roomrows) > 0)) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
			exit;
		}
		$pnights_cal = VikRequest::getVar('nights_cal', array());
		$pnights_cal = VikBooking::filterNightsSeasonsCal($pnights_cal);
		$room_nights_cal = explode(',', VikBooking::getRoomParam('seasoncal_nights', $roomrows['params']));
		$room_nights_cal = VikBooking::filterNightsSeasonsCal($room_nights_cal);
		$seasons_cal = array();
		$seasons_cal_nights = array();
		if (count($pnights_cal) > 0) {
			$seasons_cal_nights = $pnights_cal;
		} elseif (count($room_nights_cal) > 0) {
			$seasons_cal_nights = $room_nights_cal;
		} else {
			$q = "SELECT `days` FROM `#__vikbooking_dispcost` WHERE `idroom`=".intval($roomid)." ORDER BY `#__vikbooking_dispcost`.`days` ASC LIMIT 7;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$nights_vals = $dbo->loadAssocList();
				$nights_got = array();
				foreach ($nights_vals as $night) {
					$nights_got[] = $night['days'];
				}
				$seasons_cal_nights = VikBooking::filterNightsSeasonsCal($nights_got);
			}
		}
		if (count($seasons_cal_nights) > 0) {
			$q = "SELECT `p`.*,`tp`.`name`,`tp`.`attr`,`tp`.`idiva`,`tp`.`breakfast_included`,`tp`.`free_cancellation`,`tp`.`canc_deadline` FROM `#__vikbooking_dispcost` AS `p` LEFT JOIN `#__vikbooking_prices` `tp` ON `p`.`idprice`=`tp`.`id` WHERE `p`.`days` IN (".implode(',', $seasons_cal_nights).") AND `p`.`idroom`=".$roomid." ORDER BY `p`.`days` ASC, `p`.`cost` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				$arrtar = array();
				foreach ($tars as $tar) {
					$arrtar[$tar['days']][] = $tar;
				}
				$seasons_cal['nights'] = $seasons_cal_nights;
				$seasons_cal['offseason'] = $arrtar;
				$q = "SELECT * FROM `#__vikbooking_seasons` WHERE `idrooms` LIKE '%-".$roomid."-%';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$seasons = $dbo->loadAssocList();
					//Restrictions
					$all_restrictions = VikBooking::loadRestrictions(true, array($roomid));
					$all_seasons = array();
					$curtime = time();
					foreach ($seasons as $sk => $s) {
						if (empty($s['from']) && empty($s['to'])) {
							continue;
						}
						$now_year = !empty($s['year']) ? $s['year'] : date('Y');
						list($sfrom, $sto) = VikBooking::getSeasonRangeTs($s['from'], $s['to'], $now_year);
						if ($sto < $curtime && empty($s['year'])) {
							$now_year += 1;
							list($sfrom, $sto) = VikBooking::getSeasonRangeTs($s['from'], $s['to'], $now_year);
						}
						if ($sto >= $curtime) {
							$s['from_ts'] = $sfrom;
							$s['to_ts'] = $sto;
							$all_seasons[] = $s;
						}
					}
					if (count($all_seasons) > 0) {
						$vbo_df = VikBooking::getDateFormat();
						$vbo_df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
						$hcheckin = 0;
						$mcheckin = 0;
						$hcheckout = 0;
						$mcheckout = 0;
						$timeopst = VikBooking::getTimeOpenStore();
						if (is_array($timeopst)) {
							$opent = VikBooking::getHoursMinutes($timeopst[0]);
							$closet = VikBooking::getHoursMinutes($timeopst[1]);
							$hcheckin = $opent[0];
							$mcheckin = $opent[1];
							$hcheckout = $closet[0];
							$mcheckout = $closet[1];
						}
						$all_seasons = VikBooking::sortSeasonsRangeTs($all_seasons);
						$seasons_cal['seasons'] = $all_seasons;
						$seasons_cal['season_prices'] = array();
						$seasons_cal['restrictions'] = array();
						//calc price changes for each season and for each num-night
						foreach ($all_seasons as $sk => $s) {
							$checkin_base_ts = $s['from_ts'];
							$is_dst = date('I', $checkin_base_ts);
							foreach ($arrtar as $numnights => $tar) {
								$checkout_base_ts = $s['to_ts'];
								for($i = 1; $i <= $numnights; $i++) {
									$checkout_base_ts += 86400;
									$is_now_dst = date('I', $checkout_base_ts);
									if ($is_dst != $is_now_dst) {
										if ((int)$is_dst == 1) {
											$checkout_base_ts += 3600;
										} else {
											$checkout_base_ts -= 3600;
										}
										$is_dst = $is_now_dst;
									}
								}
								//calc check-in and check-out ts for the two dates
								$first = VikBooking::getDateTimestamp(date($vbo_df, $checkin_base_ts), $hcheckin, $mcheckin);
								$second = VikBooking::getDateTimestamp(date($vbo_df, $checkout_base_ts), $hcheckout, $mcheckout);
								$tar = VikBooking::applySeasonsRoom($tar, $first, $second, $s);
								$seasons_cal['season_prices'][$sk][$numnights] = $tar;
								//Restrictions
								if (count($all_restrictions) > 0) {
									$season_restr = VikBooking::parseSeasonRestrictions($first, $second, $numnights, $all_restrictions);
									if (count($season_restr) > 0) {
										$seasons_cal['restrictions'][$sk][$numnights] = $season_restr;
									}
								}
							}
						}
					}
				}
			}
		}
		//calendar rates
		$todayd = getdate();
		$tsstart = mktime(0, 0, 0, $todayd['mon'], $todayd['mday'], $todayd['year']);
		$startdate = VikRequest::getString('startdate', '', 'request');
		if (!empty($startdate)) {
			$startts = VikBooking::getDateTimestamp($startdate, 0, 0);
			if (!empty($startts)) {
				$session->set('vbRatesOviewTs', $startts);
				$tsstart = $startts;
			}
		} else {
			$prevts = $session->get('vbRatesOviewTs', '');
			if (!empty($prevts)) {
				$tsstart = $prevts;
			}
		}
		$roomrates = array();
		//read the rates for the lowest number of nights
		//the old query below used to cause an error #1055 when sql_mode=only_full_group_by
		//$q = "SELECT `r`.*,`p`.`name` FROM `#__vikbooking_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikbooking_dispcost` WHERE `idroom`=".(int)$roomid." GROUP BY `idroom`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` WHERE `r`.`idroom`=".(int)$roomid." GROUP BY `r`.`idprice` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		/**
		 * Some types of price may not have a cost for 1 or 2 nights,
		 * so joining by MIN(`days`) may exclude certain types of price.
		 * We need to manually get via PHP all types of price.
		 * Old query below is no longer in use, even though it was
		 * compatible with the SQL strict mode (only_full_group_by).
		 * $q = "SELECT `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` FROM `#__vikbooking_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikbooking_dispcost` WHERE `idroom`=".(int)$roomid." GROUP BY `idroom`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` WHERE `r`.`idroom`=".(int)$roomid." GROUP BY `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		 * 
		 * @since 	1.10 - Revision October 1st 2018
		 */
		$q = "SELECT `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` FROM `#__vikbooking_dispcost` AS `r` LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` WHERE `r`.`idroom`=".(int)$roomid." ORDER BY `r`.`days` ASC, `r`.`cost` ASC LIMIT 50;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$roomrates = $dbo->loadAssocList();
			$parsed_room_prices = array();
			foreach ($roomrates as $rrk => $rrv) {
				if (isset($parsed_room_prices[$rrv['idprice']])) {
					unset($roomrates[$rrk]);
					continue;
				}
				$roomrates[$rrk]['cost'] = round(($rrv['cost'] / $rrv['days']), 2);
				$roomrates[$rrk]['days'] = 1;
				$parsed_room_prices[$rrv['idprice']] = 1;
			}
		}
		$roomrates = array_values($roomrates);
		//

		//Read all the bookings between these dates
		$booked_dates = array();
		$MAX_DAYS = 60;
		$info_start = getdate($tsstart);
		$endts = mktime(0, 0, 0, $info_start['mon'], ($info_start['mday'] + $MAX_DAYS), $info_start['year']);
		$q = "SELECT `b`.*,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`='".(int)$roomid."' AND `b`.`id`=`ob`.`idbusy` AND (`b`.`checkin`>=".$tsstart." OR `b`.`checkout`>=".$tsstart.") AND (`b`.`checkin`<=".$endts." OR `b`.`checkout`<=".$tsstart.");";
		$dbo->setQuery($q);
		$dbo->execute();
		$rbusy = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		$booked_dates[(int)$roomid] = $rbusy;
		
		$this->all_rooms = &$all_rooms;
		$this->roomrows = &$roomrows;
		$this->seasons_cal_nights = &$seasons_cal_nights;
		$this->seasons_cal = &$seasons_cal;
		$this->tsstart = &$tsstart;
		$this->roomrates = &$roomrates;
		$this->booked_dates = &$booked_dates;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINRATESOVERVIEWTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
			JToolBarHelper::addNew('newseason', JText::_('VBMAINSEASONSNEW'));
			JToolBarHelper::spacer();
			JToolBarHelper::addNew('newrestriction', JText::_('VBMAINRESTRICTIONNEW'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::cancel( 'cancel', JText::_('VBBACK'));
		JToolBarHelper::spacer();
	}

}
