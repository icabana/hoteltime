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

class VikBookingViewDashboard extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		/**
		 * @joomlaonly 	Extra fields for Joomla XML Updates
		 */
		//VBO 1.9 - Joomla Updates (>= 3.2.0) - Extra Fields Handler
		$jvobj = new JVersion;
		$jv = $jvobj->getShortVersion();
		if (version_compare($jv, '3.2.0', '>=')) {
			//With this method we populate the extra fields for this extension. We need to store the domain name encoded in base64 for the download of commercial updates.
			//Without the record stored this way, our Update Servers will reject the download request.
			require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'urihandler.php');
			$update = new UriUpdateHandler('com_vikbooking');
			$domain = JFactory::getApplication()->input->server->getString('HTTP_HOST');
			$update->addExtraField('domain', base64_encode($domain));
			$ord_num = JFactory::getApplication()->input->getString('order_number');
			if (!empty($ord_num)) {
				$update->addExtraField('order_number', $ord_num);
			}
			$update->checkSchema(E4J_SOFTWARE_VERSION);
			$update->register();
			//
		}
		//
		$dbo = JFactory::getDBO();
		$q = "SELECT COUNT(*) FROM `#__vikbooking_prices`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totprices = $dbo->loadResult();
		$q = "SELECT COUNT(*) FROM `#__vikbooking_rooms`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totrooms = $dbo->loadResult();
		$q = "SELECT COUNT(*) FROM `#__vikbooking_dispcost`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totdailyfares = $dbo->loadResult();
		$arrayfirst = array('totprices' => $totprices, 'totrooms' => $totrooms, 'totdailyfares' => $totdailyfares);
		$nextreservations = array();
		$totnextresconf = 0;
		$totnextrespend = 0;
		$tot_rooms_units = 0;
		$all_rooms_ids = array();
		$all_rooms_units = array();
		$all_rooms_features = array();
		$unpublished_rooms = array();
		$today_start_ts = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
		$today_end_ts = mktime(23, 59, 59, date("n"), date("j"), date("Y"));
		$checkin_today = array();
		$checkout_today = array();
		$rooms_locked = array();
		if ($totprices > 0 && $totrooms > 0) {
			$q = "SELECT `o`.`id`,`o`.`custdata`,`o`.`status`,`o`.`checkin`,`o`.`checkout`,`o`.`roomsnum`,`o`.`country`,(SELECT CONCAT_WS(' ',`or`.`t_first_name`,`or`.`t_last_name`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id` LIMIT 1) AS `nominative`,(SELECT SUM(`or`.`adults`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `tot_adults`,(SELECT SUM(`or`.`children`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `tot_children` FROM `#__vikbooking_orders` AS `o` WHERE `o`.`status`!='cancelled' AND `o`.`checkin`>".$today_end_ts." ORDER BY `o`.`checkin` ASC LIMIT 10;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$nextreservations = $dbo->loadAssocList();
			}
			$q = "SELECT COUNT(*) FROM `#__vikbooking_orders` WHERE `checkin`>".time()." AND `status`='confirmed';";
			$dbo->setQuery($q);
			$dbo->execute();
			$totnextresconf = $dbo->loadResult();
			$q = "SELECT COUNT(*) FROM `#__vikbooking_orders` WHERE `checkin`>".time()." AND `status`='standby';";
			$dbo->setQuery($q);
			$dbo->execute();
			$totnextrespend = $dbo->loadResult();
			$q = "SELECT SUM(`units`) FROM `#__vikbooking_rooms` WHERE `avail`=1;";
			$dbo->setQuery($q);
			$dbo->execute();
			$tot_rooms_units = $dbo->loadResult();
			$q = "SELECT `id`,`name`,`units`,`params`,`avail` FROM `#__vikbooking_rooms`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$all_rooms = $dbo->loadAssocList();
				foreach ($all_rooms as $k => $r) {
					if ($r['avail'] < 1) {
						$unpublished_rooms[] = $r['id'];
					}
					$all_rooms_ids[$r['id']] = $r['name'];
					$all_rooms_units[$r['id']] = $r['units'];
					$rparams = json_decode($r['params'], true);
					$all_rooms_features[$r['id']] = array_key_exists('features', $rparams) && is_array($rparams['features']) ? $rparams['features'] : array();
				}
			}
		}
		$arrayfirst['totnextresconf'] = $totnextresconf;
		$arrayfirst['totnextrespend'] = $totnextrespend;
		$arrayfirst['tot_rooms_units'] = (int)$tot_rooms_units;
		$arrayfirst['all_rooms_ids'] = $all_rooms_ids;
		$arrayfirst['all_rooms_units'] = $all_rooms_units;
		$arrayfirst['all_rooms_features'] = $all_rooms_features;
		$arrayfirst['today_start_ts'] = $today_start_ts;
		$arrayfirst['today_end_ts'] = $today_end_ts;
		$arrayfirst['unpublished_rooms'] = $unpublished_rooms;
		$q = "SELECT `o`.`id`,`o`.`custdata`,`o`.`status`,`o`.`checkin`,`o`.`checkout`,`o`.`roomsnum`,`o`.`country`,`o`.`closure`,`o`.`checked`,(SELECT CONCAT_WS(' ',`or`.`t_first_name`,`or`.`t_last_name`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id` LIMIT 1) AS `nominative`,(SELECT SUM(`or`.`adults`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `tot_adults`,(SELECT SUM(`or`.`children`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `tot_children` FROM `#__vikbooking_orders` AS `o` WHERE `o`.`checkin`>=".$today_start_ts." AND `o`.`checkin`<=".$today_end_ts." ORDER BY `o`.`checkin` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$checkin_today = $dbo->loadAssocList();
		}
		$q = "SELECT `o`.`id`,`o`.`custdata`,`o`.`status`,`o`.`checkin`,`o`.`checkout`,`o`.`roomsnum`,`o`.`country`,`o`.`closure`,`o`.`checked`,(SELECT CONCAT_WS(' ',`or`.`t_first_name`,`or`.`t_last_name`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id` LIMIT 1) AS `nominative`,(SELECT SUM(`or`.`adults`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `tot_adults`,(SELECT SUM(`or`.`children`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`o`.`id`) AS `tot_children` FROM `#__vikbooking_orders` AS `o` WHERE `o`.`closure`=0 AND `o`.`status`='confirmed' AND `o`.`checkout`>=".$today_start_ts." AND `o`.`checkout`<=".$today_end_ts." ORDER BY `o`.`checkout` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$checkout_today = $dbo->loadAssocList();
		}
		$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `until`<" . time() . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "SELECT `lock`.*,`r`.`name`,(SELECT CONCAT_WS(' ',`or`.`t_first_name`,`or`.`t_last_name`) FROM `#__vikbooking_ordersrooms` AS `or` WHERE `lock`.`idorder`=`or`.`idorder` LIMIT 1) AS `nominative` FROM `#__vikbooking_tmplock` AS `lock` LEFT JOIN `#__vikbooking_rooms` `r` ON `lock`.`idroom`=`r`.`id` WHERE `lock`.`until`>".time()." ORDER BY `lock`.`id` DESC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rooms_locked = $dbo->loadAssocList();
		}
		
		$this->arrayfirst = &$arrayfirst;
		$this->nextreservations = &$nextreservations;
		$this->checkin_today = &$checkin_today;
		$this->checkout_today = &$checkout_today;
		$this->rooms_locked = &$rooms_locked;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINDASHBOARDTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.admin', 'com_vikbooking')) {
			JToolBarHelper::preferences('com_vikbooking');
		}		
	}

}
