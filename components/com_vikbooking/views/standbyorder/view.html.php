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


jimport('joomla.application.component.view');

class VikbookingViewStandbyorder extends JViewVikBooking {
	function display($tpl = null) {
		//set noindex instruction for robots
		$document = JFactory::getDocument();
		$document->setMetaData('robots', 'noindex,follow');
		//
		$sid = VikRequest::getString('sid', '', 'request');
		$ts = VikRequest::getString('ts', '', 'request');
		$rooms = array();
		$tars = array();
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$cookie = $mainframe->input->cookie;
		$vbo_tn = VikBooking::getTranslator();
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `sid`=" . $dbo->quote($sid) . " AND `ts`=" . $dbo->quote($ts) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			$mainframe->redirect("index.php");
			exit;
		}
		$order = $dbo->loadAssocList();
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
				$maxhmore = VikBooking::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		$is_package = !empty($order[0]['pkg']) ? true : false;
		$orderrooms = array();
		$q="SELECT `or`.`idroom`,`or`.`adults`,`or`.`children`,`or`.`idtar`,`or`.`optionals`,`or`.`roomindex`,`or`.`pkg_id`,`or`.`pkg_name`,`or`.`cust_cost`,`or`.`cust_idiva`,`or`.`extracosts`,`or`.`room_cost`,`or`.`otarplan`,`r`.`id` AS `r_reference_id`,`r`.`name`,`r`.`img`,`r`.`idcarat`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$order[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$orderrooms = $dbo->loadAssocList();
			$vbo_tn->translateContents($orderrooms, '#__vikbooking_rooms', array('id' => 'r_reference_id'));
			foreach ($orderrooms as $kor => $or) {
				$num = $kor + 1;
				if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
					//package or custom cost set from the back-end
					continue;
				}
				$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `id`='" . $or['idtar'] . "';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$tar = $dbo->loadAssocList();
					$tar = VikBooking::applySeasonsRoom($tar, $order[0]['checkin'], $order[0]['checkout']);
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
					$tars[$num] = $tar[0];
				}
			}
		}
		$payment = "";
		if (!empty ($order[0]['idpayment'])) {
			$exppay = explode('=', $order[0]['idpayment']);
			$payment = VikBooking::getPayment($exppay[0], $vbo_tn);
		}
		$pnodep = VikRequest::getString('nodep', '', 'request');
		$cnodep = $cookie->get('vboFA', '', 'string');
		$nodep = intval($pnodep) > 0 || intval($cnodep) > 0 ? 1 : 0;
		$this->ord = &$order[0];
		$this->orderrooms = &$orderrooms;
		$this->tars = &$tars;
		$this->payment = &$payment;
		$this->nodep = &$nodep;
		$this->vbo_tn = &$vbo_tn;
		//theme
		$theme = VikBooking::getTheme();
		if ($theme != 'default') {
			$thdir = VBO_SITE_PATH.DS.'themes'.DS.$theme.DS.'standbyorder';
			if (is_dir($thdir)) {
				$this->_setPath('template', $thdir.DS);
			}
		}
		//
		parent::display($tpl);
	}
}
?>