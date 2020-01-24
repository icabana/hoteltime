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

class VikBookingViewEditorder extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		$ido = $cid[0];
		if (file_exists(VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'vcm-channels.css')) {
			$document = JFactory::getDocument();
			$document->addStyleSheet(VCM_ADMIN_URI.'assets/css/vikchannelmanager.css');
			$document->addStyleSheet(VCM_ADMIN_URI.'assets/css/vcm-channels.css');
		}
		$dbo = JFactory::getDBO();
		$cpin = VikBooking::getCPinIstance();
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".$dbo->quote($ido).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() != 1) {
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
		}
		$row = $dbo->loadAssoc();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_gpayments` ORDER BY `#__vikbooking_gpayments`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$payments = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : '';
		$customer = $cpin->getCustomerFromBooking($row['id']);
		if (count($customer) && !empty($customer['country'])) {
			if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$customer['country'].'.png')) {
				$customer['country_img'] = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$customer['country'].'.png'.'" title="'.$customer['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
			}
		}
		$padminnotes = VikRequest::getString('adminnotes', '', 'request');
		$pupdadmnotes = VikRequest::getString('updadmnotes', '', 'request');
		$pinvnotes = VikRequest::getString('invnotes', '', 'request', VIKREQUEST_ALLOWHTML);
		$pupdinvnotes = VikRequest::getString('updinvnotes', '', 'request');
		$pnewpayment = VikRequest::getString('newpayment', '', 'request');
		$pnewlang = VikRequest::getString('newlang', '', 'request');
		$padmindisc = VikRequest::getString('admindisc', '', 'request');
		$ptot_taxes = VikRequest::getString('tot_taxes', '', 'request');
		$ptot_city_taxes = VikRequest::getString('tot_city_taxes', '', 'request');
		$ptot_fees = VikRequest::getString('tot_fees', '', 'request');
		$pcmms = VikRequest::getString('cmms', '', 'request');
		$pcustmail = VikRequest::getString('custmail', '', 'request');
		$pcustphone = VikRequest::getString('custphone', '', 'request');
		$pmakepay = VikRequest::getInt('makepay', '', 'request');
		if ($pmakepay > 0) {
			$q = "UPDATE `#__vikbooking_orders` SET `paymcount`=1 WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['paymcount'] = 1;
		}
		if (!empty($padminnotes) || !empty($pupdadmnotes)) {
			$q = "UPDATE `#__vikbooking_orders` SET `adminnotes`=".$dbo->quote($padminnotes)." WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['adminnotes'] = $padminnotes;
		}
		if (!empty($pinvnotes) || !empty($pupdinvnotes)) {
			$pinvnotes = strpos($pinvnotes, '<br') !== false ? $pinvnotes : nl2br($pinvnotes);
			$q = "UPDATE `#__vikbooking_orders` SET `inv_notes`=".$dbo->quote($pinvnotes)." WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['inv_notes'] = $pinvnotes;
		}
		if (!empty($pnewpayment) && is_array($payments)) {
			foreach ($payments as $npay) {
				if ((int)$npay['id'] == (int)$pnewpayment) {
					$newpayvalid = $npay['id'].'='.$npay['name'];
					$q = "UPDATE `#__vikbooking_orders` SET `idpayment`=".$dbo->quote($newpayvalid)." WHERE `id`=".$row['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					$row['idpayment'] = $newpayvalid;
					break;
				}
			}
		}
		if (!empty($pnewlang)) {
			$q = "UPDATE `#__vikbooking_orders` SET `lang`=".$dbo->quote($pnewlang)." WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['lang'] = $pnewlang;
		}
		if (strlen($padmindisc) > 0) {
			if (floatval($padmindisc) > 0.00) {
				$admincoupon = '-1;'.floatval($padmindisc).';'.JText::_('VBADMINDISCOUNT');
			} else {
				$admincoupon = '';
			}
			$q = "UPDATE `#__vikbooking_orders` SET `coupon`=".$dbo->quote($admincoupon)." WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['coupon'] = $admincoupon;
		}
		if (strlen($ptot_taxes) > 0) {
			$q = "UPDATE `#__vikbooking_orders` SET `tot_taxes`='".floatval($ptot_taxes)."' WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['tot_taxes'] = $ptot_taxes;
		}
		if (strlen($ptot_city_taxes) > 0) {
			$q = "UPDATE `#__vikbooking_orders` SET `tot_city_taxes`='".floatval($ptot_city_taxes)."' WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['tot_city_taxes'] = $ptot_city_taxes;
		}
		if (strlen($ptot_fees) > 0) {
			$q = "UPDATE `#__vikbooking_orders` SET `tot_fees`='".floatval($ptot_fees)."' WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['tot_fees'] = $ptot_fees;
		}
		if (strlen($pcmms) > 0) {
			$q = "UPDATE `#__vikbooking_orders` SET `cmms`='".floatval($pcmms)."' WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['cmms'] = $pcmms;
		}
		if (strlen($pcustmail) > 0) {
			$q = "UPDATE `#__vikbooking_orders` SET `custmail`=".$dbo->quote($pcustmail)." WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['custmail'] = $pcustmail;
		}
		if (strlen($pcustphone) > 0) {
			$q = "UPDATE `#__vikbooking_orders` SET `phone`=".$dbo->quote($pcustphone)." WHERE `id`=".$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$row['phone'] = $pcustphone;
		}
		//Rooms Specific Unit
		$proomindex = VikRequest::getVar('roomindex', array());
		if (!empty($proomindex) && is_array($proomindex) && count($proomindex)) {
			foreach ($proomindex as $or_id => $rind) {
				if (!empty($or_id)) {
					$q = "UPDATE `#__vikbooking_ordersrooms` SET `roomindex`=".(!empty($rind) ? (int)$rind : "NULL")." WHERE `id`=".(int)$or_id." AND `idorder`=".(int)$row['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		//
		//PCI DSS Checking
		if (!empty($row['idorderota']) && !empty($row['channel']) && !empty($row['paymentlog'])) {
			if (stripos($row['paymentlog'], 'card number') !== false && strpos($row['paymentlog'], '*') !== false) {
				$checkout_info = getdate($row['checkout']);
				$checkout_midnight = mktime(23, 59, 59, $checkout_info['mon'], $checkout_info['mday'], $checkout_info['year']);
				//Limit set to Check-out date at 29:59:59 + 2 extra hours
				if (time() > ($checkout_midnight + 7200)) {
					$newlogstr = JText::_('VBOCCLOGDATAREMOVEDPCIDSS');
					$q = "UPDATE `#__vikbooking_orders` SET `paymentlog`=".$dbo->quote($newlogstr)." WHERE `id`=".$row['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					$row['paymentlog'] = $newlogstr;
				}
			}
		}
		//
		$q = "SELECT `or`.*,`r`.`name`,`r`.`fromadult`,`r`.`toadult`,`r`.`params` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=".(int)$row['id']." AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$rooms = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
		$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		$busy = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
				
		$this->row = &$row;
		$this->rooms = &$rooms;
		$this->busy = &$busy;
		$this->customer = &$customer;
		$this->payments = &$payments;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINORDERTITLEEDIT'), 'vikbooking');
		JToolBarHelper::cancel( 'canceledorder', JText::_('VBBACK'));
		JToolBarHelper::spacer();
	}

}
